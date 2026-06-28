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

class TurnoPublicControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;
    private Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->medico = $this->createMedico();
        $this->paciente = Paciente::create([
            'nombre'   => 'Juan',
            'apellido' => 'Perez',
            'dni'      => '12345678',
            'afiliado' => 'N/A',
            'medico_id' => $this->medico->id,
        ]);
    }

    // --- confirmar ---

    public function test_confirmar_con_token_valido_actualiza_estado(): void
    {
        $turno = $this->crearTurno(['turno_token' => 'token-valido-123']);

        $this->get("/turno/confirmar/{$turno->id}?token=token-valido-123")
            ->assertOk()
            ->assertViewIs('turno.respuesta')
            ->assertViewHas('exito', true);

        $this->assertEquals(EstadosTurno::Confirmado, $turno->fresh()->estado);
        $this->assertNotNull($turno->fresh()->turno_token);
    }

    public function test_confirmar_con_mismo_token_dos_veces_sigue_exitoso(): void
    {
        $turno = $this->crearTurno(['turno_token' => 'token-one-time']);

        $this->get("/turno/confirmar/{$turno->id}?token=token-one-time")->assertOk();

        // El link es reutilizable, el segundo intento también debe ser exitoso
        $this->get("/turno/confirmar/{$turno->id}?token=token-one-time")
            ->assertOk()
            ->assertViewHas('exito', true);
    }

    public function test_confirmar_con_token_incorrecto_muestra_error(): void
    {
        $turno = $this->crearTurno(['turno_token' => 'token-correcto']);

        $this->get("/turno/confirmar/{$turno->id}?token=token-incorrecto")
            ->assertOk()
            ->assertViewHas('exito', false);

        $this->assertEquals(EstadosTurno::Pendiente, $turno->fresh()->estado);
    }

    public function test_confirmar_sin_token_muestra_error(): void
    {
        $turno = $this->crearTurno(['turno_token' => 'algo']);

        $this->get("/turno/confirmar/{$turno->id}")
            ->assertOk()
            ->assertViewHas('exito', false);
    }

    public function test_confirmar_turno_inexistente_muestra_error(): void
    {
        $this->get('/turno/confirmar/99999?token=cualquiera')
            ->assertOk()
            ->assertViewHas('exito', false);
    }

    public function test_confirmar_turno_ya_cancelado_muestra_error(): void
    {
        $turno = $this->crearTurno([
            'estado'      => EstadosTurno::Cancelado,
            'turno_token' => 'token-cancelado',
        ]);

        $this->get("/turno/confirmar/{$turno->id}?token=token-cancelado")
            ->assertOk()
            ->assertViewHas('exito', false);
    }

    // --- cancelar ---

    public function test_cancelar_con_token_valido_actualiza_estado(): void
    {
        $turno = $this->crearTurno(['turno_token' => 'token-cancel-123']);

        $this->get("/turno/cancelar/{$turno->id}?token=token-cancel-123")
            ->assertOk()
            ->assertViewIs('turno.respuesta')
            ->assertViewHas('exito', true);

        $this->assertEquals(EstadosTurno::Cancelado, $turno->fresh()->estado);
        $this->assertNotNull($turno->fresh()->turno_token);
    }

    public function test_cancelar_con_mismo_token_dos_veces_sigue_exitoso(): void
    {
        $turno = $this->crearTurno(['turno_token' => 'token-cancel-one']);

        $this->get("/turno/cancelar/{$turno->id}?token=token-cancel-one")->assertOk();

        // El link es reutilizable, el segundo intento también debe ser exitoso
        $this->get("/turno/cancelar/{$turno->id}?token=token-cancel-one")
            ->assertOk()
            ->assertViewHas('exito', true);
    }

    public function test_cancelar_con_token_incorrecto_muestra_error(): void
    {
        $turno = $this->crearTurno(['turno_token' => 'token-real']);

        $this->get("/turno/cancelar/{$turno->id}?token=token-falso")
            ->assertOk()
            ->assertViewHas('exito', false);

        $this->assertEquals(EstadosTurno::Pendiente, $turno->fresh()->estado);
    }

    public function test_cancelar_sin_token_muestra_error(): void
    {
        $turno = $this->crearTurno(['turno_token' => 'algo']);

        $this->get("/turno/cancelar/{$turno->id}")
            ->assertOk()
            ->assertViewHas('exito', false);
    }

    public function test_cancelar_turno_inexistente_muestra_error(): void
    {
        $this->get('/turno/cancelar/99999?token=cualquiera')
            ->assertOk()
            ->assertViewHas('exito', false);
    }

    public function test_cancelar_turno_ya_cancelado_devuelve_exito(): void
    {
        $turno = $this->crearTurno([
            'estado'      => EstadosTurno::Cancelado,
            'turno_token' => 'token-ya-cancelado',
        ]);

        $this->get("/turno/cancelar/{$turno->id}?token=token-ya-cancelado")
            ->assertOk()
            ->assertViewHas('exito', true);
    }

    // --- helpers ---

    private function createMedico(): User
    {
        $user = User::factory()->make(['rol' => Roles::Medico]);
        $user->saveQuietly();
        $user->forceFill(['medico_id' => $user->id])->saveQuietly();
        return $user->fresh();
    }

    private function crearTurno(array $overrides = []): Turno
    {
        $defaults = [
            'paciente_id' => $this->paciente->id,
            'medico_id'   => $this->medico->id,
            'fecha'       => now()->addDays(3)->toDateString(),
            'hora'        => '10:00',
            'estado'      => EstadosTurno::Pendiente,
            'tipo'        => TipoTurno::Turno,
        ];

        return Turno::create(array_merge($defaults, $overrides));
    }
}
