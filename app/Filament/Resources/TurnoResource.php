<?php

namespace App\Filament\Resources;

use App\Enums\EstadosTurno;
use App\Filament\Resources\TurnoResource\Pages;
use App\Filament\Resources\TurnoResource\RelationManagers;
use App\Models\Turno;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Attributes\Layout;

class TurnoResource extends Resource
{
    protected static ?string $model = Turno::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Select::make('paciente_id')
                ->label('Paciente')
                ->relationship('paciente', 'nombre')
                //options(\App\Models\Paciente::all()->pluck('nombre', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\DateTimePicker::make('fecha')
                ->required()
                ->default(now()),
            Forms\Components\Select::make('estado')
                ->default('pendiente')
                ->options(EstadosTurno::class),
            Forms\Components\Hidden::make('medico_id')
                ->default(auth()->user()->id),
            Forms\Components\TextInput::make('medico_name')
                ->label('Médico')
                ->default(auth()->user()->name)
                ->readOnly(),
            Forms\Components\Textarea::make('notas')
                ->label('Notas')
                ->placeholder('Notas adicionales')
                ->rows(3)
                ->columnSpan(2)
                ->autosize(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paciente.nombre'),
                Tables\Columns\TextColumn::make('fecha')
                    ->date('d/m/Y H:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('medico.name')
                    ->label('Médico'),
                Tables\Columns\TextColumn::make('notas'),
            ])
            ->filters([
                SelectFilter::make('paciente_id')
                    ->relationship('paciente', 'nombre')
                    ->searchable()
                    ->label('Paciente'),
                SelectFilter::make('estado')
                    ->options(EstadosTurno::class),
                Filter::make('fecha2')
                    ->form([
                        DatePicker::make('desde'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha', '>=', $date),
                            );
                    }),
                Filter::make('fecha')
                    ->form([
                        DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha', '<=', $date),
                            );
                    })
                
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTurnos::route('/'),
            'create' => Pages\CreateTurno::route('/create'),
            'edit' => Pages\EditTurno::route('/{record}/edit'),
        ];
    }
}
