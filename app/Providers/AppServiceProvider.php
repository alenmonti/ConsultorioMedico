<?php

namespace App\Providers;

use Filament\Infolists\Components\Fieldset;
use Filament\Tables\Actions\{DeleteAction, EditAction, ViewAction};
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

        DeleteAction::configureUsing(function (DeleteAction $action): void {
            $action->iconButton();
        });

        ViewAction::configureUsing(function (ViewAction $action): void {
            $action->iconButton();
        });

        Table::configureUsing(function (Table $table): void {
            $table->filtersTriggerAction(
                fn ($action) => $action->button()->label('Filtrar'),
            );
            $table->filtersLayout(FiltersLayout::Modal);
        });

        Fieldset::configureUsing(function (Fieldset $fieldset): void {
            $fieldset->extraAttributes(['style' => 'height: 100%;'], true);
        });
    }
}
