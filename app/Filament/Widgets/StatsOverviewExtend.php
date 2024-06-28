<?php
 
namespace App\Filament\Widgets;

use App\Enums\EstadosTurno;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
 
class StatsOverviewExtend extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        $cancelados = Turno::whereDate('fecha', Carbon::today())->where('estado', EstadosTurno::Cancelado)->count();
        $atendidos = Turno::whereDate('fecha', Carbon::today())->where('estado', EstadosTurno::Atendido)->count();
        $restantes = Turno::whereDate('fecha', Carbon::today())->count() - $cancelados - $atendidos;

        return [
        Stat::make('Atendidos', $atendidos)
            ->description('7% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('primary'),
        Stat::make('Restantes', $restantes)
            ->description('3% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('info'),
        ];
    }
}