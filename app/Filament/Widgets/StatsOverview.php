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
        $pendientes = Turno::whereDate('fecha', Carbon::today())->where('estado', EstadosTurno::Pendiente)->count();
        $confirmados = Turno::whereDate('fecha', Carbon::today())->where('estado', EstadosTurno::Confirmado)->count();
        $cancelados = Turno::whereDate('fecha', Carbon::today())->where('estado', EstadosTurno::Cancelado)->count();

        return [
        Stat::make('Pendientes', $pendientes)
            ->description('32k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('warning'),
        Stat::make('Confirmados', $confirmados)
            ->description('32k increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),
        Stat::make('Cancelados', $cancelados)
            ->description('3% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('danger'),
        ];
    }
}