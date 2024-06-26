<?php

namespace App\Providers;

use Filament\Tables\Actions\EditAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        EditAction::configureUsing(function (EditAction $action): void {
            $action->iconButton();
        });

        Table::configureUsing(function (Table $table): void {
            $table->filtersTriggerAction(
                fn ($action) => $action->button()->label('Filtrar'),
            );
            $table->filtersLayout(FiltersLayout::Modal);
        });
    }
}
