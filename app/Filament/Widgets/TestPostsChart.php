<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class TestPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    protected static ?int $sort = 3;
    protected array|string|int $columnSpan = 6;
    public ?string $filter = 'year';

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        return [
            'datasets' => [
                [
                    'label' => 'Blog posts created',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
                [
                    'label' => 'Blog posts deleted',
                    'data' => [0, 15, 5, 2, 31, 32, 55, 74, 65, 55, 77, 99],
                    'backgroundColor' => '#FF6384',
                    'borderColor' => '#FF9AA2',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
