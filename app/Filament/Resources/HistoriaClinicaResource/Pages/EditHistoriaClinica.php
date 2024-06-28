<?php

namespace App\Filament\Resources\HistoriaClinicaResource\Pages;

use App\Filament\Resources\HistoriaClinicaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use App\Traits\RedirectAfterSubmit;

class EditHistoriaClinica extends EditRecord
{
    use RedirectAfterSubmit;

    protected static string $resource = HistoriaClinicaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
