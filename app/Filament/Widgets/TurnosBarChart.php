<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class TurnosBarChart extends ChartWidget
{
    protected static ?string $heading = 'Resumen';
    protected static ?int $sort = 4;
    protected array|string|int $columnSpan = 6;
    protected static ?string $pollingInterval = null;
    public ?string $filter = 'anio';

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de turnos',
                    'data' => [1, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
            ],
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
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
        return 'bar';
    }
}
