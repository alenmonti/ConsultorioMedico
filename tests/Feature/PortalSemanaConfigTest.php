<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\AperturaMensual;
use App\Models\Horario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalSemanaConfigTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;

    protected function setUp(): void
    {
        parent::setUp();

        // Fijar fecha: lunes 2024-01-01
        Carbon::setTestNow('2024-01-01 12:00:00');

        $this->medico = $this->createMedico();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ── meses cerrados (apertura mensual) ─────────────────────────────────────

    public function test_semana_de_mes_futuro_sin_abrir_aparece_cerrada(): void
    {
        // hoy = 2024-01-01 → febrero es un mes futuro sin AperturaMensual
        $this->createHorario('jueves', anio: 2024, mes: 2);

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-02-01");

        $res->assertOk();
        $jueves = $this->diaEnRespuesta($res, '2024-02-01');
        $this->assertEquals('cerrado', $jueves['estado']);
    }

    public function test_semana_de_mes_futuro_abierto_muestra_disponibilidad(): void
    {
        $this->createHorario('jueves', anio: 2024, mes: 2);

        AperturaMensual::create([
            'medico_id' => $this->medico->id,
            'anio' => 2024,
            'mes' => 2,
            'abierto' => true,
        ]);

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-02-01");

        $res->assertOk();
        $jueves = $this->diaEnRespuesta($res, '2024-02-01');
        $this->assertNotEquals('cerrado', $jueves['estado']);
    }

    // ── activo_portal en Horario ──────────────────────────────────────────────

    public function test_horario_con_activo_portal_false_aparece_como_cerrado(): void
    {
        $this->createHorario('martes', activo_portal: false);

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-01");

        $res->assertOk();
        $martes = $this->diaEnRespuesta($res, '2024-01-02');
        $this->assertEquals('cerrado', $martes['estado']);
    }

    public function test_horario_con_activo_portal_true_no_es_cerrado(): void
    {
        $this->createHorario('miercoles', activo_portal: true);

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-01");

        $res->assertOk();
        $miercoles = $this->diaEnRespuesta($res, '2024-01-03');
        $this->assertNotEquals('cerrado', $miercoles['estado']);
    }

    public function test_activo_portal_false_no_afecta_a_otros_dias(): void
    {
        $this->createHorario('lunes', activo_portal: false);
        $this->createHorario('miercoles', activo_portal: true);

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-01");

        $res->assertOk();
        $this->assertEquals('cerrado', $this->diaEnRespuesta($res, '2024-01-01')['estado']);
        $this->assertNotEquals('cerrado', $this->diaEnRespuesta($res, '2024-01-03')['estado']);
    }

    public function test_multiples_horarios_con_activo_portal_false(): void
    {
        $this->createHorario('lunes', activo_portal: false);
        $this->createHorario('miercoles', activo_portal: false);
        $this->createHorario('viernes', activo_portal: false);
        $this->createHorario('martes', activo_portal: true);

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-01");

        $res->assertOk();
        $this->assertEquals('cerrado', $this->diaEnRespuesta($res, '2024-01-01')['estado']); // lunes
        $this->assertEquals('cerrado', $this->diaEnRespuesta($res, '2024-01-03')['estado']); // miercoles
        $this->assertEquals('cerrado', $this->diaEnRespuesta($res, '2024-01-05')['estado']); // viernes
        $this->assertNotEquals('cerrado', $this->diaEnRespuesta($res, '2024-01-02')['estado']); // martes
    }

    public function test_sin_horarios_con_activo_portal_no_cierra_dias_con_activo_true(): void
    {
        $this->createHorario('lunes', activo_portal: true);
        $this->createHorario('martes', activo_portal: true);

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-01");

        $res->assertOk();
        $this->assertNotEquals('cerrado', $this->diaEnRespuesta($res, '2024-01-01')['estado']);
        $this->assertNotEquals('cerrado', $this->diaEnRespuesta($res, '2024-01-02')['estado']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function createMedico(): User
    {
        $user = User::factory()->make(['rol' => Roles::Medico]);
        $user->saveQuietly();
        $user->forceFill(['medico_id' => $user->id])->saveQuietly();
        return $user->fresh();
    }

    private function createHorario(string $dia, bool $activo_portal = true, int $anio = 2024, int $mes = 1): Horario
    {
        return Horario::create([
            'medico_id'     => $this->medico->id,
            'anio'          => $anio,
            'mes'           => $mes,
            'dia'           => $dia,
            'desde'         => '09:00',
            'hasta'         => '09:00',
            'intervalo'     => '00:20',
            'activo_sistema' => true,
            'activo_portal'  => $activo_portal,
        ]);
    }

    private function diaEnRespuesta($response, string $fecha): array
    {
        $dias = $response->json('dias');
        foreach ($dias as $dia) {
            if ($dia['fecha'] === $fecha) {
                return $dia;
            }
        }
        $this->fail("Fecha {$fecha} no encontrada en la respuesta del portal.");
    }
}
