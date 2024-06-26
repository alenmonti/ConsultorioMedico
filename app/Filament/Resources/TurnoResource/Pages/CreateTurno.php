<?php

namespace App\Filament\Resources\TurnoResource\Pages;

use App\Filament\Resources\TurnoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\RedirectAfterSubmit;

class CreateTurno extends CreateRecord
{
    use RedirectAfterSubmit;
    
    protected static string $resource = TurnoResource::class;
}
