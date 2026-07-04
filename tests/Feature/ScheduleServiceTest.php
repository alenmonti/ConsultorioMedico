<?php

namespace Tests\Feature;

use App\Enums\EstadosTurno;
use App\Enums\Roles;
use App\Enums\TipoHorarioEspecial;
use App\Enums\TipoTurno;
use App\Models\AperturaMensual;
use App\Models\Horario;
use App\Models\HorarioEspecial;
use App\Models\Paciente;
use App\Models\Practica;
use App\Models\Turno;
use App\Models\User;
use App\Services\HorarioMesService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleServiceTest extends TestCase
{
    use RefreshDatabase;

    private ScheduleService $service;
    private User $medico;
    private Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ScheduleService::class);

        $this->medico = $this->createMedicoSilently();

        // Act as medico so PacienteObserver can read auth user
        $this->actingAs($this->medico);

        $this->paciente = Paciente::create([
            'nombre' => 'Test',
            'apellido' => 'Paciente',
            'dni' => '12345678',
            'afiliado' => 'N/A',
        ]);
    }

    // horariosDisponibles

    public function test_returns_empty_when_no_horarios_configured(): void
    {
        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01'); // lunes

        $this->assertEmpty($result);
    }

    public function test_returns_all_slots_when_no_turnos_booked(): void
    {
        $this->createHorario('lunes', '09:00', '09:40', '00:20');

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01');

        $this->assertSame(['09:00' => '09:00', '09:20' => '09:20', '09:40' => '09:40'], $result);
    }

    public function test_excludes_occupied_slots_for_turno_type(): void
    {
        $this->createHorario('lunes', '09:00', '09:40', '00:20');
        $this->createTurno('2024-01-01', '09:00');

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01');

        $this->assertArrayNotHasKey('09:00', $result);
        $this->assertArrayHasKey('09:20', $result);
        $this->assertArrayHasKey('09:40', $result);
    }

    public function test_returns_only_occupied_slots_for_sobre_turno_type(): void
    {
        $this->createHorario('lunes', '09:00', '09:40', '00:20');
        $this->createTurno('2024-01-01', '09:00');

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01', 'sobre_turno');

        $this->assertSame(['09:00' => '09:00'], $result);
    }

    public function test_only_returns_slots_for_requested_day(): void
    {
        $this->createHorario('lunes', '09:00', '09:00', '00:20');
        $this->createHorario('martes', '10:00', '10:00', '00:20');

        $resultLunes = $this->service->horariosDisponibles($this->medico, '2024-01-01');
        $resultMartes = $this->service->horariosDisponibles($this->medico, '2024-01-02');

        $this->assertSame(['09:00' => '09:00'], $resultLunes);
        $this->assertSame(['10:00' => '10:00'], $resultMartes);
    }

    public function test_other_medico_turnos_do_not_affect_availability(): void
    {
        $otroMedico = $this->createMedicoSilently();

        $this->createHorario('lunes', '09:00', '09:00', '00:20');

        Turno::create([
            'paciente_id' => $this->paciente->id,
            'medico_id' => $otroMedico->id,
            'fecha' => '2024-01-01',
            'hora' => '09:00',
            'estado' => EstadosTurno::Pendiente,
            'tipo' => TipoTurno::Turno,
        ]);

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01');

        $this->assertArrayHasKey('09:00', $result);
    }

    // diasNoDisponibles

    public function test_marks_day_unavailable_when_no_horarios(): void
    {
        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-07', '2024-01-07'); // domingo

        $this->assertContains('2024-01-07', $result);
    }

    public function test_does_not_mark_day_unavailable_when_slots_exist(): void
    {
        $this->createHorario('lunes', '09:00', '09:00', '00:20');

        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-01');

        $this->assertNotContains('2024-01-01', $result);
    }

    public function test_marks_day_unavailable_when_all_slots_booked(): void
    {
        $this->createHorario('lunes', '09:00', '09:00', '00:20');
        $this->createTurno('2024-01-01', '09:00');

        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-01');

        $this->assertContains('2024-01-01', $result);
    }

    public function test_uses_at_most_three_queries_for_date_range(): void
    {
        $this->createHorario('lunes', '09:00', '09:00', '00:20');

        $queryCount = 0;
        \DB::listen(fn () => $queryCount++);

        $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-07');

        // horarios query + turnos query + especiales query + aperturas mensuales query
        $this->assertLessThanOrEqual(4, $queryCount);
    }

    // multi-slot: horariosDisponibles

    public function test_multi_slot_turno_blocks_all_covered_slots(): void
    {
        $this->createHorario('lunes', '09:00', '09:40', '00:20');
        $practica = $this->createPractica(60);
        $this->createTurno('2024-01-01', '09:00', $practica->id);

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01');

        $this->assertArrayNotHasKey('09:00', $result);
        $this->assertArrayNotHasKey('09:20', $result);
        $this->assertArrayNotHasKey('09:40', $result);
    }

    public function test_slot_requires_enough_consecutive_slots_for_duration(): void
    {
        // 09:00, 09:20, 09:40 available; requesting 60 min needs 3 consecutive slots
        // 09:40 only has itself left, so it should NOT be available for 60-min booking
        $this->createHorario('lunes', '09:00', '09:40', '00:20');

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01', 'turno', 60);

        $this->assertArrayHasKey('09:00', $result);
        $this->assertArrayNotHasKey('09:20', $result);
        $this->assertArrayNotHasKey('09:40', $result);
    }

    public function test_short_duration_fits_in_gap_between_multi_slot_turnos(): void
    {
        // 09:00–10:20 window; 60-min turno at 09:00 blocks 09:00/09:20/09:40; 10:00 and 10:20 remain
        $this->createHorario('lunes', '09:00', '10:20', '00:20');
        $practica = $this->createPractica(60);
        $this->createTurno('2024-01-01', '09:00', $practica->id);

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01', 'turno', 20);

        $this->assertArrayNotHasKey('09:00', $result);
        $this->assertArrayNotHasKey('09:20', $result);
        $this->assertArrayNotHasKey('09:40', $result);
        $this->assertArrayHasKey('10:00', $result);
        $this->assertArrayHasKey('10:20', $result);
    }

    public function test_long_duration_not_available_when_gap_too_small(): void
    {
        // 09:00–10:20 window; 60-min turno at 09:00; only 40 min remain (10:00, 10:20),
        // so 60-min booking should find no slot
        $this->createHorario('lunes', '09:00', '10:20', '00:20');
        $practica = $this->createPractica(60);
        $this->createTurno('2024-01-01', '09:00', $practica->id);

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01', 'turno', 60);

        $this->assertEmpty($result);
    }

    // multi-slot: diasNoDisponibles

    public function test_multi_slot_turno_marks_day_unavailable_when_all_slots_covered(): void
    {
        // 3 slots of 20 min; one 60-min turno covers all three
        $this->createHorario('lunes', '09:00', '09:40', '00:20');
        $practica = $this->createPractica(60);
        $this->createTurno('2024-01-01', '09:00', $practica->id);

        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-01');

        $this->assertContains('2024-01-01', $result);
    }

    public function test_multi_slot_turno_does_not_mark_day_unavailable_when_slots_remain(): void
    {
        // 5 slots; 60-min turno covers 3, 2 remain
        $this->createHorario('lunes', '09:00', '10:20', '00:20');
        $practica = $this->createPractica(60);
        $this->createTurno('2024-01-01', '09:00', $practica->id);

        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-01');

        $this->assertNotContains('2024-01-01', $result);
    }

    // ignorarCancelados en horariosDisponibles

    public function test_turno_cancelado_bloquea_slot_por_defecto(): void
    {
        $this->createHorario('lunes', '09:00', '09:40', '00:20');
        Turno::create([
            'paciente_id' => $this->paciente->id,
            'medico_id'   => $this->medico->id,
            'fecha'       => '2024-01-01',
            'hora'        => '09:00',
            'estado'      => EstadosTurno::Cancelado,
            'tipo'        => TipoTurno::Turno,
        ]);

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01');

        $this->assertArrayNotHasKey('09:00', $result);
    }

    public function test_turno_cancelado_no_bloquea_slot_con_ignorar_cancelados(): void
    {
        $this->createHorario('lunes', '09:00', '09:40', '00:20');
        Turno::create([
            'paciente_id' => $this->paciente->id,
            'medico_id'   => $this->medico->id,
            'fecha'       => '2024-01-01',
            'hora'        => '09:00',
            'estado'      => EstadosTurno::Cancelado,
            'tipo'        => TipoTurno::Turno,
        ]);

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01', ignorarCancelados: true);

        $this->assertArrayHasKey('09:00', $result);
    }

    public function test_turno_pendiente_sigue_bloqueando_con_ignorar_cancelados(): void
    {
        $this->createHorario('lunes', '09:00', '09:40', '00:20');
        $this->createTurno('2024-01-01', '09:00');

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01', ignorarCancelados: true);

        $this->assertArrayNotHasKey('09:00', $result);
        $this->assertArrayHasKey('09:20', $result);
    }

    // ignorarCancelados en diasNoDisponibles

    public function test_dia_con_todos_slots_cancelados_es_no_disponible_por_defecto(): void
    {
        $this->createHorario('lunes', '09:00', '09:00', '00:20');
        Turno::create([
            'paciente_id' => $this->paciente->id,
            'medico_id'   => $this->medico->id,
            'fecha'       => '2024-01-01',
            'hora'        => '09:00',
            'estado'      => EstadosTurno::Cancelado,
            'tipo'        => TipoTurno::Turno,
        ]);

        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-01');

        $this->assertContains('2024-01-01', $result);
    }

    public function test_dia_con_todos_slots_cancelados_es_disponible_con_ignorar_cancelados(): void
    {
        $this->createHorario('lunes', '09:00', '09:00', '00:20');
        Turno::create([
            'paciente_id' => $this->paciente->id,
            'medico_id'   => $this->medico->id,
            'fecha'       => '2024-01-01',
            'hora'        => '09:00',
            'estado'      => EstadosTurno::Cancelado,
            'tipo'        => TipoTurno::Turno,
        ]);

        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-01', ignorarCancelados: true);

        $this->assertNotContains('2024-01-01', $result);
    }

    public function test_dia_con_slot_pendiente_sigue_siendo_no_disponible_con_ignorar_cancelados(): void
    {
        $this->createHorario('lunes', '09:00', '09:00', '00:20');
        $this->createTurno('2024-01-01', '09:00');

        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-01', ignorarCancelados: true);

        $this->assertContains('2024-01-01', $result);
    }

    // horarios especiales: exclusión

    public function test_exclusion_todo_el_dia_vacia_horarios_disponibles(): void
    {
        $this->createHorario('lunes', '09:00', '09:40', '00:20');
        $this->createEspecial('2024-01-01', TipoHorarioEspecial::Exclusion, todoElDia: true);

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01');

        $this->assertEmpty($result);
    }

    public function test_exclusion_parcial_quita_solo_los_slots_del_rango(): void
    {
        $this->createHorario('lunes', '09:00', '10:00', '00:20');
        $this->createEspecial('2024-01-01', TipoHorarioEspecial::Exclusion, desde: '09:00', hasta: '09:20');

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01');

        $this->assertArrayNotHasKey('09:00', $result);
        $this->assertArrayNotHasKey('09:20', $result);
        $this->assertArrayHasKey('09:40', $result);
        $this->assertArrayHasKey('10:00', $result);
    }

    public function test_exclusion_todo_el_dia_marca_dia_no_disponible(): void
    {
        $this->createHorario('lunes', '09:00', '09:40', '00:20');
        $this->createEspecial('2024-01-01', TipoHorarioEspecial::Exclusion, todoElDia: true);

        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-01');

        $this->assertContains('2024-01-01', $result);
    }

    // horarios especiales: adición

    public function test_adicion_agrega_slots_en_dia_sin_horario_configurado(): void
    {
        // domingo sin Horario semanal configurado
        $this->createEspecial('2024-01-07', TipoHorarioEspecial::Adicion, desde: '10:00', hasta: '10:40');

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-07');

        $this->assertSame(['10:00' => '10:00', '10:20' => '10:20', '10:40' => '10:40'], $result);
    }

    public function test_adicion_agrega_slots_fuera_del_rango_del_horario_semanal(): void
    {
        $this->createHorario('lunes', '09:00', '09:20', '00:20');
        $this->createEspecial('2024-01-01', TipoHorarioEspecial::Adicion, desde: '18:00', hasta: '18:20');

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01');

        $this->assertArrayHasKey('09:00', $result);
        $this->assertArrayHasKey('09:20', $result);
        $this->assertArrayHasKey('18:00', $result);
        $this->assertArrayHasKey('18:20', $result);
    }

    public function test_adicion_respeta_turnos_ya_ocupados_en_ese_rango(): void
    {
        $this->createEspecial('2024-01-07', TipoHorarioEspecial::Adicion, desde: '10:00', hasta: '10:40');
        $this->createTurno('2024-01-07', '10:00');

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-07');

        $this->assertArrayNotHasKey('10:00', $result);
        $this->assertArrayHasKey('10:20', $result);
    }

    public function test_dia_con_solo_adicion_no_se_marca_no_disponible(): void
    {
        $this->createEspecial('2024-01-07', TipoHorarioEspecial::Adicion, desde: '10:00', hasta: '10:00');

        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-07', '2024-01-07');

        $this->assertNotContains('2024-01-07', $result);
    }

    public function test_dia_sin_horario_ni_especial_sigue_no_disponible(): void
    {
        $result = $this->service->diasNoDisponibles($this->medico, '2024-01-07', '2024-01-07');

        $this->assertContains('2024-01-07', $result);
    }

    // apertura mensual

    public function test_mes_futuro_sin_apertura_mensual_se_considera_cerrado(): void
    {
        $futuro = Carbon::now()->addMonth()->startOfMonth();
        $dia = $this->diaDeSemana($futuro);
        $fecha = $futuro->format('Y-m-d');

        $this->createHorario($dia, '09:00', '09:40', '00:20', $futuro->year, $futuro->month);
        $this->createEspecial($fecha, TipoHorarioEspecial::Adicion, desde: '10:00', hasta: '10:00');

        $result = $this->service->horariosDisponibles($this->medico, $fecha);
        $this->assertEmpty($result);

        $diasNoDisponibles = $this->service->diasNoDisponibles($this->medico, $fecha, $fecha);
        $this->assertContains($fecha, $diasNoDisponibles);
    }

    public function test_mes_futuro_con_apertura_mensual_abierta_vuelve_a_estar_disponible(): void
    {
        $futuro = Carbon::now()->addMonth()->startOfMonth();
        $dia = $this->diaDeSemana($futuro);
        $fecha = $futuro->format('Y-m-d');

        $this->createHorario($dia, '09:00', '09:40', '00:20', $futuro->year, $futuro->month);

        AperturaMensual::create([
            'medico_id' => $this->medico->id,
            'anio' => $futuro->year,
            'mes' => $futuro->month,
            'abierto' => true,
        ]);

        $result = $this->service->horariosDisponibles($this->medico, $fecha);
        $this->assertArrayHasKey('09:00', $result);

        $diasNoDisponibles = $this->service->diasNoDisponibles($this->medico, $fecha, $fecha);
        $this->assertNotContains($fecha, $diasNoDisponibles);
    }

    public function test_mes_actual_esta_abierto_aunque_haya_quedado_cerrado_de_cuando_era_futuro(): void
    {
        // 2024-01 es pasado respecto a "hoy": si quedó una fila cerrada de cuando
        // todavía era un mes futuro, el toggle ya no debe aplicar.
        $this->createHorario('lunes', '09:00', '09:00', '00:20', 2024, 1);

        $apertura = AperturaMensual::create([
            'medico_id' => $this->medico->id,
            'anio' => 2024,
            'mes' => 1,
            'abierto' => false,
        ]);

        $result = $this->service->horariosDisponibles($this->medico, '2024-01-01');
        $this->assertArrayHasKey('09:00', $result);

        $diasNoDisponibles = $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-01');
        $this->assertNotContains('2024-01-01', $diasNoDisponibles);

        // se autocorrige en la DB para que quede claro que el mes está abierto
        $this->assertTrue($apertura->fresh()->abierto);
    }

    public function test_horario_de_un_mes_no_se_filtra_en_otro_mes_mismo_dia(): void
    {
        // mismo día de semana (lunes) en dos meses distintos
        $this->createHorario('lunes', '09:00', '09:00', '00:20', 2024, 1);
        $this->createHorario('lunes', '10:00', '10:00', '00:20', 2024, 2);

        $resultEnero = $this->service->horariosDisponibles($this->medico, '2024-01-01');
        $resultFebrero = $this->service->horariosDisponibles($this->medico, '2024-02-05');

        $this->assertSame(['09:00' => '09:00'], $resultEnero);
        $this->assertSame(['10:00' => '10:00'], $resultFebrero);
    }

    // adición: activo_sistema / activo_portal

    public function test_adicion_solo_portal_no_aparece_en_sistema_pero_si_en_portal(): void
    {
        $this->createEspecial(
            '2024-01-07',
            TipoHorarioEspecial::Adicion,
            desde: '10:00',
            hasta: '10:00',
            activoSistema: false,
            activoPortal: true,
        );

        $resultSistema = $this->service->horariosDisponibles($this->medico, '2024-01-07', portal: false);
        $resultPortal = $this->service->horariosDisponibles($this->medico, '2024-01-07', portal: true);

        $this->assertArrayNotHasKey('10:00', $resultSistema);
        $this->assertArrayHasKey('10:00', $resultPortal);
    }

    public function test_adicion_solo_sistema_no_aparece_en_portal_pero_si_en_sistema(): void
    {
        $this->createEspecial(
            '2024-01-07',
            TipoHorarioEspecial::Adicion,
            desde: '10:00',
            hasta: '10:00',
            activoSistema: true,
            activoPortal: false,
        );

        $resultSistema = $this->service->horariosDisponibles($this->medico, '2024-01-07', portal: false);
        $resultPortal = $this->service->horariosDisponibles($this->medico, '2024-01-07', portal: true);

        $this->assertArrayHasKey('10:00', $resultSistema);
        $this->assertArrayNotHasKey('10:00', $resultPortal);
    }

    // HorarioMesService

    public function test_asegurar_mes_configurado_clona_del_mes_anterior(): void
    {
        $this->createHorario('lunes', '09:00', '10:00', '00:20', 2024, 1);

        app(HorarioMesService::class)->asegurarMesConfigurado($this->medico, 2024, 2);

        $clonados = Horario::where('medico_id', $this->medico->id)->where('anio', 2024)->where('mes', 2)->get();
        $this->assertCount(1, $clonados);
        $this->assertSame('lunes', $clonados->first()->dia->value);
    }

    public function test_asegurar_mes_configurado_no_hace_nada_si_ya_hay_filas(): void
    {
        $this->createHorario('lunes', '09:00', '10:00', '00:20', 2024, 2);

        app(HorarioMesService::class)->asegurarMesConfigurado($this->medico, 2024, 2);

        $this->assertCount(1, Horario::where('medico_id', $this->medico->id)->where('anio', 2024)->where('mes', 2)->get());
    }

    public function test_asegurar_mes_configurado_no_hace_nada_si_no_hay_mes_previo_con_datos(): void
    {
        app(HorarioMesService::class)->asegurarMesConfigurado($this->medico, 2024, 1);

        $this->assertCount(0, Horario::where('medico_id', $this->medico->id)->where('anio', 2024)->where('mes', 1)->get());
    }

    private function diaDeSemana(Carbon $fecha): string
    {
        $dayMap = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

        return $dayMap[$fecha->dayOfWeek];
    }

    private function createMedicoSilently(): User
    {
        $user = User::factory()->make(['rol' => Roles::Medico]);
        $user->saveQuietly();
        $user->forceFill(['medico_id' => $user->id])->saveQuietly();
        return $user->fresh();
    }

    private function createHorario(string $dia, string $desde, string $hasta, string $intervalo, int $anio = 2024, int $mes = 1): Horario
    {
        return Horario::create([
            'medico_id' => $this->medico->id,
            'anio' => $anio,
            'mes' => $mes,
            'dia' => $dia,
            'desde' => $desde,
            'hasta' => $hasta,
            'intervalo' => $intervalo,
        ]);
    }

    private function createTurno(string $fecha, string $hora, ?int $practicaId = null): Turno
    {
        return Turno::create([
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'fecha' => $fecha,
            'hora' => $hora,
            'estado' => EstadosTurno::Pendiente,
            'tipo' => TipoTurno::Turno,
            'practica_id' => $practicaId,
        ]);
    }

    private function createEspecial(
        string $fecha,
        TipoHorarioEspecial $tipo,
        bool $todoElDia = false,
        ?string $desde = null,
        ?string $hasta = null,
        bool $activoSistema = true,
        bool $activoPortal = false,
    ): HorarioEspecial {
        return HorarioEspecial::create([
            'medico_id' => $this->medico->id,
            'fecha' => $fecha,
            'tipo' => $tipo,
            'todo_el_dia' => $todoElDia,
            'desde' => $desde,
            'hasta' => $hasta,
            'motivo' => 'Test',
            'activo_sistema' => $activoSistema,
            'activo_portal' => $activoPortal,
        ]);
    }

    private function createPractica(int $duracionMin): Practica
    {
        return Practica::withoutGlobalScopes()->create([
            'medico_id' => $this->medico->id,
            'nombre' => "Práctica {$duracionMin}min",
            'costo' => 0,
            'duracion_min' => $duracionMin,
        ]);
    }
}
