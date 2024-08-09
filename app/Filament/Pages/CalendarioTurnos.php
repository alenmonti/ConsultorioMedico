<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TurnoResource\Widgets\Calendario;
use App\Filament\Resources\TurnoResource\Widgets\ColorGuide;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CalendarioTurnos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Calendario';

    public function getTitle(): string | Htmlable
{
    return __('');
}

    protected static string $view = 'filament.pages.calendario-turnos';

    public function getHeaderWidgets(): array
    {
        return [
            ColorGuide::class,
            Calendario::make(),
        ];
    }
}
