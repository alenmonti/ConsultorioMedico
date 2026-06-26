<?php

namespace App\Filament\Resources\PracticaResource\Pages;

use App\Filament\Resources\PracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPracticas extends ListRecords
{
    protected static string $resource = PracticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
