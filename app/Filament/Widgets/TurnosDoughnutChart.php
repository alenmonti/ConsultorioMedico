<?php

namespace App\Filament\Widgets;

use App\Enums\EstadosTurno;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class TurnosDoughnutChart extends ChartWidget
{
    protected static ?string $heading = 'Estados hoy';
    protected static ?int $sort = 5;
    protected array|string|int $columnSpan = ['sm' => 1, 'md' => 6];
    protected static ?string $pollingInterval = null;
    protected static ?string $maxHeight = '274px';
    // public ?string $filter = 'hoy';

    protected function getData(): array
    {
        $turnos = Turno::query()
            ->whereDate('fecha', Carbon::today())
            ->selectRaw('COUNT(*) cantidad, estado')
            ->groupByRaw('estado')
            ->orderBy('estado')
            ->get()
            ->toArray();

        $data = [];
        foreach (EstadosTurno::values() as $estado) {
            $cantidad = 0;
            foreach ($turnos as $turno) {
                if ($turno['estado'] === $estado) {
                    $cantidad = $turno['cantidad'];
                    break;
                }
            }
            $data[] = $cantidad;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => ['#6495ED', '#F4D03F', '#FF7F50', '#40E0D0'],
                ],
            ],
            'labels' => ['Pendientes', 'Confirmados', 'Cancelados', 'Atendidos'],
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            // 'hoy' => 'Hoy',
            // 'mes' => 'Este mes',
            // 'semana' => 'Esta semana',
            // 'anio' => 'Este año',
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
