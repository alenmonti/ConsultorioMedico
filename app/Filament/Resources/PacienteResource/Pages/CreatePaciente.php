<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use App\Filament\Resources\PacienteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\{RedirectAfterSubmit, SimpleSave};

class CreatePaciente extends CreateRecord
{
    use RedirectAfterSubmit, SimpleSave;
    
    protected static string $resource = PacienteResource::class;
}
