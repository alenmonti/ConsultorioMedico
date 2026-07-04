<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Mes: int implements HasLabel
{
    case Enero = 1;
    case Febrero = 2;
    case Marzo = 3;
    case Abril = 4;
    case Mayo = 5;
    case Junio = 6;
    case Julio = 7;
    case Agosto = 8;
    case Septiembre = 9;
    case Octubre = 10;
    case Noviembre = 11;
    case Diciembre = 12;

    public function getLabel(): string
    {
        return match ($this) {
            self::Enero => 'Enero',
            self::Febrero => 'Febrero',
            self::Marzo => 'Marzo',
            self::Abril => 'Abril',
            self::Mayo => 'Mayo',
            self::Junio => 'Junio',
            self::Julio => 'Julio',
            self::Agosto => 'Agosto',
            self::Septiembre => 'Septiembre',
            self::Octubre => 'Octubre',
            self::Noviembre => 'Noviembre',
            self::Diciembre => 'Diciembre',
        };
    }
}
