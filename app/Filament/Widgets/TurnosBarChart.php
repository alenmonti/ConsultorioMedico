<?php

namespace App\Filament\Widgets;

use App\Models\Turno;
use Filament\Widgets\ChartWidget;

class TurnosBarChart extends ChartWidget
{
    protected static ?string $heading = 'Resumen anual de turnos';
    protected static ?int $sort = 4;
    protected array|string|int $columnSpan = ['sm' => 1, 'md' => 6];
    protected static ?string $pollingInterval = null;
    public ?string $filter = 'anio';

    protected function getData(): array
    {
        $turnos = Turno::query()
            ->whereYear('fecha', now()->year)
            ->selectRaw('MONTH(fecha) as mes, COUNT(*) as cantidad')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes')
            ->toArray();
    
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            // Verificar si existe el mes en $turnos y asignar la cantidad correspondiente
            $cantidad = isset($turnos[$i]) ? $turnos[$i]['cantidad'] : 0;
            $data[] = $cantidad;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de turnos',
                    'data' => $data,
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
