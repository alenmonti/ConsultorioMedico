<?php

namespace App\Enums;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum TipoHorarioEspecial: string implements HasLabel, HasColor
{
    case Exclusion = 'exclusion';
    case Adicion = 'adicion';

    public function getLabel(): string
    {
        return match ($this) {
            self::Exclusion => 'Exclusión',
            self::Adicion => 'Adición',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Exclusion => 'danger',
            self::Adicion => 'success',
        };
    }
}
