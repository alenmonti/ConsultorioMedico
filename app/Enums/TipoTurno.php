<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoTurno: string implements HasLabel
{
    case Turno = 'turno';
    case SobreTurno = 'sobre_turno';

    public function getLabel(): string
    {
        return match ($this) {
            self::Turno => 'Turno',
            self::SobreTurno => 'Sobre Turno',
        };
    }
}
