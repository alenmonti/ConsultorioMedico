<?php

namespace App\Filament\Resources\HistoriaClinicaResource\Pages;

use App\Filament\Resources\HistoriaClinicaResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAllInOne extends ViewRecord
{
    protected static string $resource = HistoriaClinicaResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('paciente_id'),
                TextEntry::make('fecha'),
                TextEntry::make('diagnostico'),
            ]);
    }
}
