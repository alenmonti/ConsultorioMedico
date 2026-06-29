<?php

namespace Tests\Feature;

use App\Enums\Roles;
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

    // ── portal_dias_anticipacion ──────────────────────────────────────────────

    public function test_dias_dentro_del_limite_no_son_cerrados_por_anticipacion(): void
    {
        $this->medico->forceFill(['portal_dias_anticipacion' => 30])->save();
        $this->createHorario('lunes');

        // Semana actual (hoy = lunes 2024-01-01), dentro de los 30 días
        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-01");

        $res->assertOk();
        $lunes = $this->diaEnRespuesta($res, '2024-01-01');
        $this->assertNotEquals('cerrado', $lunes['estado']);
    }

    public function test_dias_fuera_del_limite_aparecen_como_cerrados(): void
    {
        // hoy = 2024-01-01, anticipacion = 7 → límite = 2024-01-08
        // 2024-01-09 está estrictamente más allá del límite
        $this->medico->forceFill(['portal_dias_anticipacion' => 7])->save();
        $this->createHorario('martes');

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-08");

        $res->assertOk();
        $martes = $this->diaEnRespuesta($res, '2024-01-09');
        $this->assertEquals('cerrado', $martes['estado']);
    }

    public function test_el_dia_exacto_del_limite_no_es_cerrado(): void
    {
        $this->medico->forceFill(['portal_dias_anticipacion' => 6])->save();
        // hoy = 2024-01-01, límite = 2024-01-07 (domingo), día del límite = 2024-01-07
        $this->createHorario('domingo');

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-01");

        $res->assertOk();
        $domingo = $this->diaEnRespuesta($res, '2024-01-07');
        // El domingo está en el límite (<=), no debe ser cerrado por anticipación
        $this->assertNotEquals('cerrado', $domingo['estado']);
    }

    public function test_respuesta_incluye_limite_portal(): void
    {
        $this->medico->forceFill(['portal_dias_anticipacion' => 14])->save();

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-01");

        $res->assertOk()->assertJsonPath('limite_portal', '2024-01-15');
    }

    public function test_limite_por_defecto_es_30_dias(): void
    {
        // medico sin portal_dias_anticipacion configurado explícitamente (null en DB)
        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-01");

        $res->assertOk()->assertJsonPath('limite_portal', '2024-01-31');
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

    // ── Interacción entre ambas configs ──────────────────────────────────────

    public function test_horario_activo_portal_false_dentro_del_limite_sigue_siendo_cerrado(): void
    {
        $this->medico->forceFill(['portal_dias_anticipacion' => 30])->save();
        $this->createHorario('martes', activo_portal: false);

        $res = $this->getJson("/portal-turnos/semana?medico_id={$this->medico->id}&desde=2024-01-01");

        $res->assertOk();
        $this->assertEquals('cerrado', $this->diaEnRespuesta($res, '2024-01-02')['estado']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function createMedico(): User
    {
        $user = User::factory()->make(['rol' => Roles::Medico]);
        $user->saveQuietly();
        $user->forceFill(['medico_id' => $user->id, 'portal_dias_anticipacion' => 30])->saveQuietly();
        return $user->fresh();
    }

    private function createHorario(string $dia, bool $activo_portal = true): Horario
    {
        return Horario::create([
            'medico_id'     => $this->medico->id,
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
