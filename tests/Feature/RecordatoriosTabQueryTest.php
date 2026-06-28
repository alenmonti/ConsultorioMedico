<?php

namespace Tests\Feature;

use App\Enums\EstadosTurno;
use App\Enums\Roles;
use App\Enums\TipoTurno;
use App\Models\Paciente;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordatoriosTabQueryTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;
    private Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->medico = $this->createMedico();
        $this->paciente = Paciente::create([
            'nombre'    => 'Test',
            'apellido'  => 'Paciente',
            'dni'       => '99887766',
            'afiliado'  => 'N/A',
            'medico_id' => $this->medico->id,
        ]);

        $this->actingAs($this->medico);
    }

    // --- Tab 1: sin seña informada ---

    public function test_tab1_incluye_turnos_sin_senia_informada(): void
    {
        $turno = $this->crearTurno([
            'fecha'              => now()->addDays(5)->toDateString(),
            'senia_informada_at' => null,
            'senia_pagada_at'    => null,
            'estado'             => EstadosTurno::Pendiente,
        ]);

        $results = $this->queryTab1()->get();

        $this->assertTrue($results->contains($turno));
    }

    public function test_tab1_excluye_turnos_con_senia_ya_informada(): void
    {
        $this->crearTurno([
            'fecha'              => now()->addDays(5)->toDateString(),
            'senia_informada_at' => now(),
            'senia_pagada_at'    => null,
        ]);

        $this->assertCount(0, $this->queryTab1()->get());
    }

    public function test_tab1_excluye_turnos_con_senia_pagada(): void
    {
        $this->crearTurno([
            'fecha'              => now()->addDays(5)->toDateString(),
            'senia_informada_at' => now()->subDay(),
            'senia_pagada_at'    => now(),
        ]);

        $this->assertCount(0, $this->queryTab1()->get());
    }

    public function test_tab1_excluye_turnos_pasados(): void
    {
        $this->crearTurno([
            'fecha'              => now()->subDay()->toDateString(),
            'senia_informada_at' => null,
            'senia_pagada_at'    => null,
        ]);

        $this->assertCount(0, $this->queryTab1()->get());
    }

    public function test_tab1_excluye_turnos_cancelados_o_atendidos(): void
    {
        foreach ([EstadosTurno::Cancelado, EstadosTurno::Atendido, EstadosTurno::Ausente] as $estado) {
            $this->crearTurno([
                'fecha'              => now()->addDays(5)->toDateString(),
                'senia_informada_at' => null,
                'senia_pagada_at'    => null,
                'estado'             => $estado,
            ]);
        }

        $this->assertCount(0, $this->queryTab1()->get());
    }

    // --- Tab 2: seña informada, sin pagar ---

    public function test_tab2_incluye_turnos_con_senia_informada_y_sin_pagar(): void
    {
        $turno = $this->crearTurno([
            'fecha'              => now()->addDays(5)->toDateString(),
            'senia_informada_at' => now()->subHour(),
            'senia_pagada_at'    => null,
            'estado'             => EstadosTurno::Pendiente,
        ]);

        $results = $this->queryTab2()->get();

        $this->assertTrue($results->contains($turno));
    }

    public function test_tab2_excluye_turnos_sin_senia_informada(): void
    {
        $this->crearTurno([
            'fecha'              => now()->addDays(5)->toDateString(),
            'senia_informada_at' => null,
            'senia_pagada_at'    => null,
        ]);

        $this->assertCount(0, $this->queryTab2()->get());
    }

    public function test_tab2_excluye_turnos_con_senia_pagada(): void
    {
        $this->crearTurno([
            'fecha'              => now()->addDays(5)->toDateString(),
            'senia_informada_at' => now()->subHour(),
            'senia_pagada_at'    => now(),
        ]);

        $this->assertCount(0, $this->queryTab2()->get());
    }

    public function test_tab2_excluye_estados_finales(): void
    {
        foreach ([EstadosTurno::Cancelado, EstadosTurno::Atendido] as $estado) {
            $this->crearTurno([
                'fecha'              => now()->addDays(5)->toDateString(),
                'senia_informada_at' => now()->subHour(),
                'senia_pagada_at'    => null,
                'estado'             => $estado,
            ]);
        }

        $this->assertCount(0, $this->queryTab2()->get());
    }

    // --- Tab 3: recordatorio pendiente (1-2 días hábiles) ---

    public function test_tab3_incluye_turnos_a_1_dia_habil(): void
    {
        Carbon::setTestNow('2026-06-29'); // lunes

        $turno = $this->crearTurno([
            'fecha'                   => '2026-06-30', // martes = 1 día hábil
            'recordatorio_enviado_at' => null,
            'estado'                  => EstadosTurno::Pendiente,
        ]);

        $dates = ['2026-06-30', '2026-07-01'];
        $this->assertCount(1, $this->queryTab3($dates)->get());

        Carbon::setTestNow();
    }

    public function test_tab3_incluye_turnos_a_2_dias_habiles(): void
    {
        Carbon::setTestNow('2026-06-29'); // lunes

        $this->crearTurno([
            'fecha'                   => '2026-07-01', // miércoles = 2 días hábiles
            'recordatorio_enviado_at' => null,
            'estado'                  => EstadosTurno::Pendiente,
        ]);

        $dates = ['2026-06-30', '2026-07-01'];
        $this->assertCount(1, $this->queryTab3($dates)->get());

        Carbon::setTestNow();
    }

    public function test_tab3_excluye_turnos_mas_alla_de_2_dias_habiles(): void
    {
        Carbon::setTestNow('2026-06-29'); // lunes

        $this->crearTurno([
            'fecha'                   => '2026-07-02', // jueves = 3 días hábiles
            'recordatorio_enviado_at' => null,
            'estado'                  => EstadosTurno::Pendiente,
        ]);

        $dates = ['2026-06-30', '2026-07-01'];
        $this->assertCount(0, $this->queryTab3($dates)->get());

        Carbon::setTestNow();
    }

    public function test_tab3_excluye_turnos_con_recordatorio_ya_enviado(): void
    {
        Carbon::setTestNow('2026-06-29');

        $this->crearTurno([
            'fecha'                   => '2026-06-30',
            'recordatorio_enviado_at' => now()->subHour(),
            'estado'                  => EstadosTurno::Pendiente,
        ]);

        $dates = ['2026-06-30', '2026-07-01'];
        $this->assertCount(0, $this->queryTab3($dates)->get());

        Carbon::setTestNow();
    }

    public function test_tab3_excluye_turnos_cancelados(): void
    {
        Carbon::setTestNow('2026-06-29');

        $this->crearTurno([
            'fecha'                   => '2026-06-30',
            'recordatorio_enviado_at' => null,
            'estado'                  => EstadosTurno::Cancelado,
        ]);

        $dates = ['2026-06-30', '2026-07-01'];
        $this->assertCount(0, $this->queryTab3($dates)->get());

        Carbon::setTestNow();
    }

    public function test_tab3_salta_fines_de_semana_correctamente(): void
    {
        Carbon::setTestNow('2026-07-03'); // viernes

        // 1 día hábil desde viernes = lunes
        // 2 días hábiles desde viernes = martes
        $dates = ['2026-07-06', '2026-07-07'];

        $turnoLunes = $this->crearTurno([
            'fecha'                   => '2026-07-06',
            'recordatorio_enviado_at' => null,
            'estado'                  => EstadosTurno::Pendiente,
        ]);

        $turnoSabado = $this->crearTurno([
            'fecha'                   => '2026-07-04',
            'recordatorio_enviado_at' => null,
            'estado'                  => EstadosTurno::Pendiente,
        ]);

        $results = $this->queryTab3($dates)->get();
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($turnoLunes));

        Carbon::setTestNow();
    }

    // --- helpers de query (replica la lógica del ListRecordatorios) ---

    private function queryTab1()
    {
        $estadosActivos = [EstadosTurno::Pendiente->value, EstadosTurno::Confirmado->value];

        return Turno::query()
            ->whereNull('senia_informada_at')
            ->whereNull('senia_pagada_at')
            ->whereIn('estado', $estadosActivos)
            ->whereDate('fecha', '>=', today());
    }

    private function queryTab2()
    {
        $estadosActivos = [EstadosTurno::Pendiente->value, EstadosTurno::Confirmado->value];

        return Turno::query()
            ->whereNotNull('senia_informada_at')
            ->whereNull('senia_pagada_at')
            ->whereIn('estado', $estadosActivos);
    }

    private function queryTab3(array $dates)
    {
        $estadosActivos = [EstadosTurno::Pendiente->value, EstadosTurno::Confirmado->value];

        return Turno::query()
            ->whereNull('recordatorio_enviado_at')
            ->whereIn('estado', $estadosActivos)
            ->where(function ($q) use ($dates) {
                foreach ($dates as $date) {
                    $q->orWhereDate('fecha', $date);
                }
            });
    }

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
            'hora'        => '10:00',
            'estado'      => EstadosTurno::Pendiente,
            'tipo'        => TipoTurno::Turno,
        ];

        return Turno::create(array_merge($defaults, $overrides));
    }
}
