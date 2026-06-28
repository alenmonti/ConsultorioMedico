<?php

namespace Tests\Feature;

use App\Enums\EstadosTurno;
use App\Enums\Roles;
use App\Enums\TipoTurno;
use App\Models\Horario;
use App\Models\Paciente;
use App\Models\Practica;
use App\Models\Turno;
use App\Models\User;
use App\Services\ScheduleService;
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

        // horarios query + turnos query + practica eager load (skipped when no turnos)
        $this->assertLessThanOrEqual(3, $queryCount);
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

    private function createMedicoSilently(): User
    {
        $user = User::factory()->make(['rol' => Roles::Medico]);
        $user->saveQuietly();
        $user->forceFill(['medico_id' => $user->id])->saveQuietly();
        return $user->fresh();
    }

    private function createHorario(string $dia, string $desde, string $hasta, string $intervalo): Horario
    {
        return Horario::create([
            'medico_id' => $this->medico->id,
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
