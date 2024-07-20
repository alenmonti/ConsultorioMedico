<?php

namespace App\Enums;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum EstadosTurno: string implements HasLabel, HasColor
{
    case Pendiente = 'pendiente';
    case Confirmado = 'confirmado';
    case Cancelado = 'cancelado';
    case Ausente = 'ausente';
    case Atendido = 'atendido';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Confirmado => 'Confirmado',
            self::Cancelado => 'Cancelado',
            self::Ausente => 'Ausente',
            self::Atendido => 'Atendido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'primary',
            self::Confirmado => 'success',
            self::Cancelado => 'danger',
            self::Ausente => 'warning',
            self::Atendido => 'info',
        };
    }

    public function getHexColor(): string
    {
        return match ($this) {
            self::Pendiente => '#3490dc',
            self::Confirmado => '#38c172',
            self::Cancelado => '#ec4d4c',
            self::Ausente => '#f6993f',
            self::Atendido => '#4a5568',
        };
    }

    public static function values(): array
    {
        return [
            'pendiente',
            'confirmado',
            'cancelado',
            'ausente',
            'atendido',
        ];
    }
}
