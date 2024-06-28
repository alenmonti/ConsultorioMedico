<?php
 
namespace App\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends \Filament\Pages\Dashboard
{
    public function getColumns(): int|string|array
    {
        return [
            'sm' => 1,
            'md' => 12,
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Turnos del día';
    }
}