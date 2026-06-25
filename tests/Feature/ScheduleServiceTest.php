<?php

namespace Tests\Feature;

use App\Enums\EstadosTurno;
use App\Enums\Roles;
use App\Enums\TipoTurno;
use App\Models\Horario;
use App\Models\Paciente;
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

    public function test_uses_at_most_two_queries_for_date_range(): void
    {
        $this->createHorario('lunes', '09:00', '09:00', '00:20');

        $queryCount = 0;
        \DB::listen(fn () => $queryCount++);

        $this->service->diasNoDisponibles($this->medico, '2024-01-01', '2024-01-07');

        $this->assertLessThanOrEqual(2, $queryCount);
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

    private function createTurno(string $fecha, string $hora): Turno
    {
        return Turno::create([
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'fecha' => $fecha,
            'hora' => $hora,
            'estado' => EstadosTurno::Pendiente,
            'tipo' => TipoTurno::Turno,
        ]);
    }
}
