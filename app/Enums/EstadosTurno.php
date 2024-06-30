<?php

namespace App\Enums;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum EstadosTurno: string implements HasLabel, HasColor
{
    case Pendiente = 'pendiente';
    case Confirmado = 'confirmado';
    case Cancelado = 'cancelado';
    case Atendido = 'atendido';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Confirmado => 'Confirmado',
            self::Cancelado => 'Cancelado',
            self::Atendido => 'Atendido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'primary',
            self::Confirmado => 'success',
            self::Cancelado => 'danger',
            self::Atendido => 'info',
        };
    }

    public function getHexColor(): string
    {
        return match ($this) {
            self::Pendiente => '#3490dc',
            self::Confirmado => '#38c172',
            self::Cancelado => '#e3342f',
            self::Atendido => '#4a5568',
        };
    }

    public static function values(): array
    {
        return [
            'pendiente',
            'confirmado',
            'cancelado',
            'atendido',
        ];
    }
}
