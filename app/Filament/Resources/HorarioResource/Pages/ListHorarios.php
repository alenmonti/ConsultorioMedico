<?php

namespace App\Filament\Resources\HorarioResource\Pages;

use App\Filament\Resources\HorarioResource;
use App\Filament\Widgets\HorarioExclusionesWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHorarios extends ListRecords
{
    protected static string $resource = HorarioResource::class;
    public static ?string $title = 'Disponibilidad Horaria';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            HorarioExclusionesWidget::class,
        ];
    }
}
