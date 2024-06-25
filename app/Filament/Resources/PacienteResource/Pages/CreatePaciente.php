<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use App\Filament\Resources\PacienteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\RedirectAfterSubmit;

class CreatePaciente extends CreateRecord
{
    use RedirectAfterSubmit;
    
    protected static string $resource = PacienteResource::class;

}
