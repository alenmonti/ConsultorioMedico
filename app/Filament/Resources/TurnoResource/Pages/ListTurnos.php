<?php

namespace App\Filament\Resources\TurnoResource\Pages;

use App\Filament\Resources\TurnoResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTurnos extends ListRecords
{
    protected static string $resource = TurnoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make(),
            'hoy' => Tab::make()
                ->query(fn ($query) => $query->whereDate('fecha', Carbon::today())),
            'manana' => Tab::make()
                ->query(fn ($query) => $query->whereDate('fecha', Carbon::tomorrow())),
            'esta_semana' => Tab::make()
                ->query(fn ($query) => $query->whereBetween('fecha', [Carbon::today(), Carbon::today()->addWeek()])),
            'este_mes' => Tab::make()
                ->query(fn ($query) => $query->whereBetween('fecha', [Carbon::today(), Carbon::today()->addMonth()])),
        ];
    }
}
