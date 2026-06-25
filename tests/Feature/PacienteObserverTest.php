<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PacienteObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_paciente_gets_medico_id_from_authenticated_user(): void
    {
        $medico = $this->createMedicoSilently();

        $this->actingAs($medico);

        $paciente = Paciente::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'dni' => '12345678',
            'afiliado' => 'N/A',
        ]);

        $this->assertEquals($medico->medico_id, $paciente->medico_id);
    }

    public function test_secretario_assigns_medico_id_of_their_doctor(): void
    {
        $medico = $this->createMedicoSilently();

        $secretario = User::factory()->make(['rol' => Roles::Secretario]);
        $secretario->saveQuietly();
        $secretario->forceFill(['medico_id' => $medico->id])->saveQuietly();

        $this->actingAs($secretario->fresh());

        $paciente = Paciente::create([
            'nombre' => 'Ana',
            'apellido' => 'García',
            'dni' => '87654321',
            'afiliado' => 'N/A',
        ]);

        $this->assertEquals($medico->id, $paciente->medico_id);
    }

    public function test_explicit_medico_id_is_kept_when_no_auth(): void
    {
        $medico = $this->createMedicoSilently();

        $paciente = Paciente::create([
            'nombre' => 'Test',
            'apellido' => 'Paciente',
            'dni' => '11111111',
            'afiliado' => 'N/A',
            'medico_id' => $medico->id,
        ]);

        $this->assertEquals($medico->id, $paciente->medico_id);
    }

    private function createMedicoSilently(): User
    {
        $user = User::factory()->make(['rol' => Roles::Medico]);
        $user->saveQuietly();
        $user->forceFill(['medico_id' => $user->id])->saveQuietly();
        return $user->fresh();
    }
}
