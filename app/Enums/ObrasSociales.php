<?php

namespace App\Enums;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum ObrasSociales: string implements HasLabel, HasColor
{
    case Particular = 'particular';
    case PAMI = 'pami';
    case IOMA = 'ioma';
    case OSDE = 'osde';
    case Galeno = 'galeno';
    case Omint = 'omint';
    case SwissMedical = 'swiss-medical';
    case SancorSalud = 'sancor-salud';
    case Otra = 'otra';

    public function getLabel(): string
    {
        return match ($this) {
            self::Particular => 'Particular',
            self::PAMI => 'PAMI',
            self::IOMA => 'IOMA',
            self::OSDE => 'OSDE',
            self::Galeno => 'Galeno',
            self::Omint => 'Omint',
            self::SwissMedical => 'Swiss Medical',
            self::SancorSalud => 'Sancor Salud',
            self::Otra => 'Otra',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Particular => 'primary',
            self::PAMI => 'warning',
            self::IOMA => 'info',
            self::OSDE => 'warning',
            self::Galeno => 'success',
            self::Omint => 'gray',
            self::SwissMedical => 'primary',
            self::SancorSalud => 'success',
            self::Otra => 'info',
        };
    }
}