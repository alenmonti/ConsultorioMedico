<?php

namespace App\Filament\Resources\HistoriaClinicaResource\Pages;

use App\Filament\Resources\HistoriaClinicaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use App\Traits\{RedirectAfterSubmit, SimpleSave};

class CreateHistoriaClinica extends CreateRecord
{
    use RedirectAfterSubmit, SimpleSave;
    
    protected static string $resource = HistoriaClinicaResource::class;
}
