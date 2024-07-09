<?php

namespace App\Enums;

use Filament\Support\Contracts\{HasColor, HasLabel};

enum ColoresDefault: string implements HasLabel, HasColor
{
    case Primary = 'primary';
    case Success = 'success';
    case Info = 'Info';
    case Warning = 'Warning';
    case Danger = 'Danger';
    case Gray = 'Gray';

    public function getLabel(): string
    {
        return match ($this) {
            self::Primary => 'Primary',
            self::Success => 'Success',
            self::Info => 'Info',
            self::Warning => 'Warning',
            self::Danger => 'Danger',
            self::Gray => 'Gray',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Primary => 'primary',
            self::Success => 'success',
            self::Info => 'info',
            self::Warning => 'warning',
            self::Danger => 'danger',
            self::Gray => 'gray',
        };
    }
}
