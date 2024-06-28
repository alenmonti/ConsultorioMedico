<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class BienvenidoWidget extends Widget
{
    protected static ?int $sort = 1;
    protected array|string|int $columnSpan = 12;
    protected static bool $isLazy = false;
    protected static ?string $pollingInterval = null;

    /**
     * @var view-string
     */
    protected static string $view = 'filament-panels::widgets.account-widget';
}
