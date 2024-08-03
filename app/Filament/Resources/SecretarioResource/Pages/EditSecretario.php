<?php

namespace App\Filament\Resources\SecretarioResource\Pages;

use App\Filament\Resources\SecretarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSecretario extends EditRecord
{
    protected static string $resource = SecretarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
