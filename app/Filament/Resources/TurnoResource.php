<?php

namespace App\Filament\Resources;

use App\Enums\EstadosTurno;
use App\Filament\Resources\TurnoResource\Pages;
use App\Filament\Resources\TurnoResource\RelationManagers;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use HusamTariq\FilamentTimePicker\Forms\Components\TimePickerField;

class TurnoResource extends Resource
{
    protected static ?string $model = Turno::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\DatePicker::make('fecha')
                ->required()
                ->native(false)
                ->default(Carbon::now()),
            TimePickerField::make('hora')
                ->required()
                ->okLabel('Aceptar')
                ->cancelLabel('Cancelar')
                ->default(Carbon::now()->format('H:i')),
                Forms\Components\Select::make('paciente_id')
                ->label('Paciente')
                ->options(function () {
                    $pacientes = \App\Models\Paciente::select('id', 'nombre', 'apellido', 'dni')->get();
                    $options = [];
                    foreach ($pacientes as $paciente) {
                        $options[$paciente->id] = $paciente->nombre.' '.$paciente->apellido.', '.$paciente->dni;
                    }
                    return $options;
                })    
                ->searchable()
                ->required(),
            Forms\Components\Select::make('estado')
                ->default('pendiente')
                ->required()
                ->options(EstadosTurno::class),
            Forms\Components\Textarea::make('notas')
                ->label('Notas')
                ->placeholder('Notas adicionales')
                ->rows(3)
                ->columnSpan(2)
                ->autosize(),
            Forms\Components\Hidden::make('medico_id')
                ->default(auth()->user()->id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->sortable()
                    ->since()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('paciente.nombre')
                    ->state(fn ($record) => $record->paciente->apellido.' '.$record->paciente->nombre),
                Tables\Columns\TextColumn::make('fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hora')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('medico.name')
                    ->label('Médico'),
                Tables\Columns\TextColumn::make('notas')
                    ->wrap()
                    ->limit(50)
                    ->width('20%'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('paciente_id')
                ->label('Paciente')
                ->options(function () {
                    $pacientes = \App\Models\Paciente::select('id', 'nombre', 'apellido', 'dni')->get();
                    $options = [];
                    foreach ($pacientes as $paciente) {
                        $options[$paciente->id] = $paciente->nombre.' '.$paciente->apellido.', '.$paciente->dni;
                    }
                    return $options;
                })    
                ->searchable(),
                SelectFilter::make('estado')
                    ->options(EstadosTurno::class),
                Filter::make('fecha2')
                    ->form([
                        DatePicker::make('desde')
                            ->native(false),
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
                        DatePicker::make('hasta')
                            ->native(false),
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
