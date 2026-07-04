<?php

namespace Tests\Feature;

use App\Enums\EstadosTurno;
use App\Enums\Roles;
use App\Enums\TipoTurno;
use App\Models\Horario;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalReservarCanceladoTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-06-10 12:00:00'); // lunes

        $this->medico = $this->createMedico();

        Horario::create([
            'medico_id'     => $this->medico->id,
            'dia'           => 'lunes',
            'desde'         => '09:00',
            'hasta'         => '10:00',
            'intervalo'     => '00:20',
            'activo_portal' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_slot_con_turno_cancelado_aparece_disponible_en_horarios(): void
    {
        Turno::withoutGlobalScopes()->create([
            'medico_id'   => $this->medico->id,
            'paciente_id' => null,
            'fecha'       => '2024-06-10',
            'hora'        => '09:00',
            'estado'      => EstadosTurno::Cancelado,
            'tipo'        => TipoTurno::Turno,
        ]);

        $res = $this->getJson("/portal-turnos/horarios?medico_id={$this->medico->id}&fecha=2024-06-10");

        $res->assertOk();
        $this->assertContains('09:00', $res->json('manana'));
    }

    public function test_dia_con_todos_slots_cancelados_aparece_disponible_en_semana(): void
    {
        // Ocupa todos los slots con turnos cancelados
        foreach (['09:00', '09:20', '09:40', '10:00'] as $hora) {
            Turno::withoutGlobalScopes()->create([
                'medico_id'   => $this->medico->id,
                'paciente_id' => null,
                'fecha'       => '2024-06-10',
                'hora'        => $hora,
                'estado'      => EstadosTurno::Cancelado,
                'tipo'        => TipoTurno::Turno,
            ]);
        }

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-06-10");

        $res->assertOk();
        $lunes = collect($res->json('dias'))->firstWhere('fecha', '2024-06-10');
        $this->assertNotEquals('lleno', $lunes['estado']);
        $this->assertGreaterThan(0, $lunes['slots']);
    }

    public function test_reservar_elimina_turno_cancelado_en_mismo_slot(): void
    {
        $cancelado = Turno::withoutGlobalScopes()->create([
            'medico_id'   => $this->medico->id,
            'paciente_id' => null,
            'fecha'       => '2024-06-10',
            'hora'        => '09:00',
            'estado'      => EstadosTurno::Cancelado,
            'tipo'        => TipoTurno::Turno,
        ]);

        $this->postJson('/portal-turnos/reservar', [
            'medico_id' => $this->medico->id,
            'fecha'     => '2024-06-10',
            'hora'      => '09:00',
            'nombre'    => 'Juan Pérez',
            'whatsapp'  => '1123456789',
        ])->assertOk();

        $this->assertNull(Turno::withoutGlobalScopes()->find($cancelado->id));
    }

    public function test_reservar_crea_turno_nuevo_en_slot_liberado(): void
    {
        Turno::withoutGlobalScopes()->create([
            'medico_id'   => $this->medico->id,
            'paciente_id' => null,
            'fecha'       => '2024-06-10',
            'hora'        => '09:00',
            'estado'      => EstadosTurno::Cancelado,
            'tipo'        => TipoTurno::Turno,
        ]);

        $this->postJson('/portal-turnos/reservar', [
            'medico_id' => $this->medico->id,
            'fecha'     => '2024-06-10',
            'hora'      => '09:00',
            'nombre'    => 'Ana López',
            'whatsapp'  => '1187654321',
        ])->assertOk();

        $nuevo = Turno::withoutGlobalScopes()
            ->where('medico_id', $this->medico->id)
            ->where('fecha', '2024-06-10')
            ->where('hora', '09:00')
            ->first();

        $this->assertNotNull($nuevo);
        $this->assertEquals(EstadosTurno::Pendiente, $nuevo->estado);
        $this->assertEquals('web', $nuevo->origen);
    }

    public function test_reservar_no_elimina_turno_cancelado_de_otro_slot(): void
    {
        $otroSlot = Turno::withoutGlobalScopes()->create([
            'medico_id'   => $this->medico->id,
            'paciente_id' => null,
            'fecha'       => '2024-06-10',
            'hora'        => '09:20',
            'estado'      => EstadosTurno::Cancelado,
            'tipo'        => TipoTurno::Turno,
        ]);

        $this->postJson('/portal-turnos/reservar', [
            'medico_id' => $this->medico->id,
            'fecha'     => '2024-06-10',
            'hora'      => '09:00',
            'nombre'    => 'Carlos Ruiz',
            'whatsapp'  => '1112345678',
        ])->assertOk();

        $this->assertNotNull(Turno::withoutGlobalScopes()->find($otroSlot->id));
    }

    private function createMedico(): User
    {
        $user = User::factory()->make(['rol' => Roles::Medico]);
        $user->saveQuietly();
        $user->forceFill([
            'medico_id' => $user->id,
        ])->saveQuietly();
        return $user->fresh();
    }
}
