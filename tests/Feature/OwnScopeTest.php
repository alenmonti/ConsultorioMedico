<?php

namespace Tests\Feature;

use App\Enums\EstadosTurno;
use App\Enums\Roles;
use App\Enums\TipoTurno;
use App\Models\Paciente;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnScopeTest extends TestCase
{
    use RefreshDatabase;

    private User $medicoA;
    private User $medicoB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->medicoA = $this->createMedicoSilently();
        $this->medicoB = $this->createMedicoSilently();
    }

    public function test_medico_sees_only_own_turnos(): void
    {
        $pacienteA = $this->createPaciente($this->medicoA);
        $pacienteB = $this->createPaciente($this->medicoB);

        Turno::create($this->turnoData($pacienteA, $this->medicoA, '09:00'));
        Turno::create($this->turnoData($pacienteA, $this->medicoA, '09:20'));
        Turno::create($this->turnoData($pacienteB, $this->medicoB, '09:00'));

        $this->actingAs($this->medicoA);

        $this->assertCount(2, Turno::all());
    }

    public function test_admin_sees_all_turnos(): void
    {
        $pacienteA = $this->createPaciente($this->medicoA);
        $pacienteB = $this->createPaciente($this->medicoB);

        Turno::create($this->turnoData($pacienteA, $this->medicoA, '09:00'));
        Turno::create($this->turnoData($pacienteB, $this->medicoB, '09:00'));

        $admin = $this->createMedicoSilently();
        $admin->forceFill(['rol' => Roles::Admin])->saveQuietly();

        $this->actingAs($admin->fresh());

        $this->assertCount(2, Turno::all());
    }

    public function test_secretario_sees_own_medico_turnos(): void
    {
        $pacienteA = $this->createPaciente($this->medicoA);
        $pacienteB = $this->createPaciente($this->medicoB);

        Turno::create($this->turnoData($pacienteA, $this->medicoA, '09:00'));
        Turno::create($this->turnoData($pacienteB, $this->medicoB, '09:00'));

        $secretario = User::factory()->make(['rol' => Roles::Secretario]);
        $secretario->saveQuietly();
        $secretario->forceFill(['medico_id' => $this->medicoA->id])->saveQuietly();

        $this->actingAs($secretario->fresh());

        $turnos = Turno::all();
        $this->assertCount(1, $turnos);
        $this->assertEquals($this->medicoA->id, $turnos->first()->medico_id);
    }

    public function test_medico_sees_only_own_pacientes(): void
    {
        $this->createPaciente($this->medicoA);
        $this->createPaciente($this->medicoB);

        $this->actingAs($this->medicoA);

        $this->assertCount(1, Paciente::all());
        $this->assertEquals($this->medicoA->id, Paciente::first()->medico_id);
    }

    public function test_own_scope_not_applied_when_unauthenticated(): void
    {
        $pacienteA = $this->createPaciente($this->medicoA);
        $pacienteB = $this->createPaciente($this->medicoB);

        Turno::create($this->turnoData($pacienteA, $this->medicoA, '09:00'));
        Turno::create($this->turnoData($pacienteB, $this->medicoB, '09:00'));

        $this->assertCount(2, Turno::all());
    }

    private function createMedicoSilently(): User
    {
        $user = User::factory()->make(['rol' => Roles::Medico]);
        $user->saveQuietly();
        $user->forceFill(['medico_id' => $user->id])->saveQuietly();
        return $user->fresh();
    }

    private function createPaciente(User $medico): Paciente
    {
        return Paciente::create([
            'nombre' => 'Test',
            'apellido' => 'Paciente',
            'dni' => fake()->unique()->numerify('########'),
            'afiliado' => 'N/A',
            'medico_id' => $medico->id,
        ]);
    }

    private function turnoData(Paciente $paciente, User $medico, string $hora): array
    {
        return [
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'fecha' => '2024-01-01',
            'hora' => $hora,
            'estado' => EstadosTurno::Pendiente,
            'tipo' => TipoTurno::Turno,
        ];
    }
}
