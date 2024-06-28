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
        $query = Turno::today();
        $cancelados = Turno::today()->where('estado', EstadosTurno::Cancelado)->count();
        $atendidos = Turno::today()->where('estado', EstadosTurno::Atendido)->count();
        $restantes = Turno::today()->count() - $cancelados - $atendidos;

        return [
        Stat::make('', $atendidos)
            ->description('Pacientes atendidos')
            ->descriptionIcon('heroicon-o-check-circle')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('primary'),
        Stat::make('', $restantes)
            ->description('Faltan atender')
            ->descriptionIcon('heroicon-s-no-symbol')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('info'),
        ];
    }
}