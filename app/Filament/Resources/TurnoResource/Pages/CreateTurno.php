<?php

namespace App\Filament\Resources\TurnoResource\Pages;

use App\Filament\Resources\TurnoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\{RedirectAfterSubmit, SimpleSave};

class CreateTurno extends CreateRecord
{
    use RedirectAfterSubmit, SimpleSave;
    
    protected static string $resource = TurnoResource::class;
}
