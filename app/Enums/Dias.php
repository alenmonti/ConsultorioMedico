<?php

namespace App\Enums;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum Dias: string implements HasLabel, HasColor
{
    case Lunes = 'lunes';
    case Martes = 'martes';
    case Miercoles = 'miercoles';
    case Jueves = 'jueves';
    case Viernes = 'viernes';
    case Sabado = 'sabado';
    case Domingo = 'domingo';

    public function getLabel(): string
    {
        return match ($this) {
            self::Lunes => 'Lunes',
            self::Martes => 'Martes',
            self::Miercoles => 'Miercoles',
            self::Jueves => 'Jueves',
            self::Viernes => 'Viernes',
            self::Sabado => 'Sabado',
            self::Domingo => 'Domingo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Lunes => 'primary',
            self::Martes => 'warning',
            self::Miercoles => 'info',
            self::Jueves => 'warning',
            self::Viernes => 'success',
            self::Sabado => 'gray',
            self::Domingo => 'primary',
        };
    }
}