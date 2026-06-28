<?php

namespace Tests\Unit;

use App\Filament\Resources\RecordatoriosResource\Pages\ListRecordatorios;
use Carbon\Carbon;
use Tests\TestCase;

class NextBusinessDaysTest extends TestCase
{
    public function test_desde_lunes_devuelve_martes_y_miercoles(): void
    {
        Carbon::setTestNow('2026-06-29'); // lunes

        $dates = ListRecordatorios::nextBusinessDays(2);

        $this->assertEquals(['2026-06-30', '2026-07-01'], $dates);

        Carbon::setTestNow();
    }

    public function test_desde_jueves_devuelve_viernes_y_lunes(): void
    {
        Carbon::setTestNow('2026-07-02'); // jueves

        $dates = ListRecordatorios::nextBusinessDays(2);

        $this->assertEquals(['2026-07-03', '2026-07-06'], $dates);

        Carbon::setTestNow();
    }

    public function test_desde_viernes_devuelve_lunes_y_martes(): void
    {
        Carbon::setTestNow('2026-07-03'); // viernes

        $dates = ListRecordatorios::nextBusinessDays(2);

        $this->assertEquals(['2026-07-06', '2026-07-07'], $dates);

        Carbon::setTestNow();
    }

    public function test_desde_sabado_devuelve_lunes_y_martes(): void
    {
        Carbon::setTestNow('2026-07-04'); // sábado

        $dates = ListRecordatorios::nextBusinessDays(2);

        $this->assertEquals(['2026-07-06', '2026-07-07'], $dates);

        Carbon::setTestNow();
    }

    public function test_desde_domingo_devuelve_lunes_y_martes(): void
    {
        Carbon::setTestNow('2026-07-05'); // domingo

        $dates = ListRecordatorios::nextBusinessDays(2);

        $this->assertEquals(['2026-07-06', '2026-07-07'], $dates);

        Carbon::setTestNow();
    }

    public function test_retorna_un_solo_dia_habil(): void
    {
        Carbon::setTestNow('2026-06-29'); // lunes

        $dates = ListRecordatorios::nextBusinessDays(1);

        $this->assertEquals(['2026-06-30'], $dates);

        Carbon::setTestNow();
    }

    public function test_retorna_lista_vacia_para_cero(): void
    {
        $dates = ListRecordatorios::nextBusinessDays(0);

        $this->assertEmpty($dates);
    }

    // --- businessDaysSince ---

    public function test_business_days_since_ayer_es_1(): void
    {
        Carbon::setTestNow('2026-06-30'); // martes

        $desde = Carbon::parse('2026-06-29'); // lunes
        $this->assertEquals(1, ListRecordatorios::businessDaysSince($desde));

        Carbon::setTestNow();
    }

    public function test_business_days_since_hace_2_dias_habiles(): void
    {
        Carbon::setTestNow('2026-07-01'); // miércoles

        $desde = Carbon::parse('2026-06-29'); // lunes
        $this->assertEquals(2, ListRecordatorios::businessDaysSince($desde));

        Carbon::setTestNow();
    }

    public function test_business_days_since_no_cuenta_fin_de_semana(): void
    {
        Carbon::setTestNow('2026-07-06'); // lunes

        $desde = Carbon::parse('2026-07-03'); // viernes → sábado y domingo no cuentan
        $this->assertEquals(1, ListRecordatorios::businessDaysSince($desde));

        Carbon::setTestNow();
    }

    public function test_business_days_since_mismo_dia_es_cero(): void
    {
        Carbon::setTestNow('2026-06-29');

        $desde = Carbon::parse('2026-06-29');
        $this->assertEquals(0, ListRecordatorios::businessDaysSince($desde));

        Carbon::setTestNow();
    }

    public function test_business_days_since_viernes_a_lunes_es_1(): void
    {
        Carbon::setTestNow('2026-07-06'); // lunes

        $desde = Carbon::parse('2026-07-03'); // viernes
        $this->assertEquals(1, ListRecordatorios::businessDaysSince($desde));

        Carbon::setTestNow();
    }

    public function test_business_days_since_viernes_a_martes_es_2(): void
    {
        Carbon::setTestNow('2026-07-07'); // martes

        $desde = Carbon::parse('2026-07-03'); // viernes
        $this->assertEquals(2, ListRecordatorios::businessDaysSince($desde));

        Carbon::setTestNow();
    }
}
