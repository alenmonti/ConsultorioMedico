<?php
 
namespace App\Filament\Widgets;

use App\Enums\EstadosTurno;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
 
class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $pendientes = Turno::today()->where('estado', EstadosTurno::Pendiente)->count();
        $confirmados = Turno::today()->where('estado', EstadosTurno::Confirmado)->count();
        $cancelados = Turno::today()->where('estado', EstadosTurno::Cancelado)->count();

        return [
        Stat::make('', $pendientes)
            ->description('Con turno sin confirmar')
            ->descriptionIcon('heroicon-c-user')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('warning'),
        Stat::make('', $confirmados)
            ->description('Confirmaron el turno')
            ->descriptionIcon('heroicon-c-user-plus')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),
        Stat::make('', $cancelados)
            ->description('Cancelaron el turno')
            ->descriptionIcon('heroicon-c-user-minus')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('danger'),
        ];
    }
}