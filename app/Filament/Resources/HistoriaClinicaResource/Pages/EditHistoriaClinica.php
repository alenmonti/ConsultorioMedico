<?php

namespace App\Filament\Resources\HistoriaClinicaResource\Pages;

use App\Filament\Resources\HistoriaClinicaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHistoriaClinica extends EditRecord
{
    protected static string $resource = HistoriaClinicaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
