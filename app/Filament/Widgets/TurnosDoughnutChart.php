<?php

namespace App\Filament\Widgets;

use App\Enums\EstadosTurno;
use Filament\Widgets\ChartWidget;

class TurnosDoughnutChart extends ChartWidget
{
    protected static ?string $heading = 'Estados';
    protected static ?int $sort = 5;
    protected array|string|int $columnSpan = 6;
    protected static ?string $pollingInterval = null;
    protected static ?string $maxHeight = '274px';
    public ?string $filter = 'hoy';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'data' => [4, 2, 2, 5],
                    'backgroundColor' => ['#6495ED', '#F4D03F', '#FF7F50', '#40E0D0'],
                ],
            ],
            'labels' => ['Pendientes', 'Confirmados', 'Cancelados', 'Atendidos'],
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'hoy' => 'Hoy',
            'mes' => 'Este mes',
            'semana' => 'Esta semana',
            'anio' => 'Este año',
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
