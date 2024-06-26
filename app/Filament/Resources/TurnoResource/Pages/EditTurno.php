<?php

namespace App\Filament\Resources\TurnoResource\Pages;

use App\Filament\Resources\TurnoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Traits\RedirectAfterSubmit;

class EditTurno extends EditRecord
{
    use RedirectAfterSubmit;
    
    protected static string $resource = TurnoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
