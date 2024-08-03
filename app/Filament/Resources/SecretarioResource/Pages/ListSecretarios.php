<?php

namespace App\Filament\Resources\SecretarioResource\Pages;

use App\Filament\Resources\SecretarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListSecretarios extends ListRecords
{
    protected static string $resource = SecretarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
