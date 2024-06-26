<?php

namespace App\Enums;

use Filament\Support\Contracts\{HasColor, HasLabel};

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
            self::Admin => 'info',
            self::Medico => 'primary',
            self::Paciente => 'gray',
            self::Secretario => 'warning',
        };
    }
}
