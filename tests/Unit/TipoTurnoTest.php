<?php

namespace Tests\Unit;

use App\Enums\TipoTurno;
use PHPUnit\Framework\TestCase;

class TipoTurnoTest extends TestCase
{
    public function test_turno_has_correct_backing_value(): void
    {
        $this->assertSame('turno', TipoTurno::Turno->value);
    }

    public function test_sobre_turno_has_correct_backing_value(): void
    {
        $this->assertSame('sobre_turno', TipoTurno::SobreTurno->value);
    }

    public function test_turno_label(): void
    {
        $this->assertSame('Turno', TipoTurno::Turno->getLabel());
    }

    public function test_sobre_turno_label(): void
    {
        $this->assertSame('Sobre Turno', TipoTurno::SobreTurno->getLabel());
    }

    public function test_can_be_created_from_string_value(): void
    {
        $this->assertSame(TipoTurno::Turno, TipoTurno::from('turno'));
        $this->assertSame(TipoTurno::SobreTurno, TipoTurno::from('sobre_turno'));
    }
}
