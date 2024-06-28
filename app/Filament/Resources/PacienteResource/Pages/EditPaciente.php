<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use App\Filament\Resources\PacienteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Traits\RedirectAfterSubmit;

class EditPaciente extends EditRecord
{
    use RedirectAfterSubmit;
    
    protected static string $resource = PacienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
