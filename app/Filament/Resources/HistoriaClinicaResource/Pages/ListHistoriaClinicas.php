<?php

namespace App\Filament\Resources\HistoriaClinicaResource\Pages;

use App\Filament\Resources\HistoriaClinicaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHistoriaClinicas extends ListRecords
{
    protected static string $resource = HistoriaClinicaResource::class;

    public static ?string $title = 'Historias Clínicas';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
