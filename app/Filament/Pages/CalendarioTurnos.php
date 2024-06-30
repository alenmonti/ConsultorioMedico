<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TurnoResource\Widgets\Calendario;
use Filament\Pages\Page;

class CalendarioTurnos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $title = 'Calendario';

    protected static string $view = 'filament.pages.calendario-turnos';

    public function getHeaderWidgets(): array
    {
        return [
            Calendario::make([
                'height' => 650,
            ])
        ];
    }
}
