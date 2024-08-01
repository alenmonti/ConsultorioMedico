<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\{Paciente, User};
use App\Observers\{PacienteObserver, UserObserver};
use Filament\Actions\CreateAction;
use Filament\Forms\Components\RichEditor;
use Filament\Infolists\Components\Fieldset;
use Filament\Tables\Actions\{DeleteAction, EditAction, ViewAction};
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

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

        CreateAction::configureUsing(function (CreateAction $action): void {
            $action->createAnother(false);
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

        RichEditor::configureUsing(function (RichEditor $richEditor): void {
            $richEditor->toolbarButtons(['bulletList', 'orderedList', 'bold', 'italic', 'redo', 'undo']);
        });

        User::observe(UserObserver::class);
        Paciente::observe(PacienteObserver::class);
    }
}
