<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Roles: string implements HasLabel, HasColor
{
    case Admin = 'admin';
    case Medico = 'medico';
    case Paciente = 'paciente';
    case Secretario = 'secretario';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Medico => 'Médico',
            self::Paciente => 'Paciente',
            self::Secretario => 'Secretario',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'primary',
            self::Medico => 'success',
            self::Paciente => 'info',
            self::Secretario => 'warning',
        };
    }
}
