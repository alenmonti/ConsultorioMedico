<?php

namespace App\Filament\Resources;

use App\Enums\EstadosTurno;
use App\Filament\Resources\TurnoResource\Pages;
use App\Filament\Resources\TurnoResource\RelationManagers;
use App\Forms\Components\TextInfo;
use App\Models\Paciente;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\{DatePicker, Grid, Hidden, Select, Textarea};
use Filament\Forms\{Form, Get, Set};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use HusamTariq\FilamentTimePicker\Forms\Components\TimePickerField;
use Illuminate\Support\Facades\Auth;

class TurnoResource extends Resource
{
    protected static ?string $model = Turno::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('tipo')
                    ->required()
                    ->searchable()
                    ->label('Tipo de turno')
                    ->options([
                        'turno' => 'Turno',
                        'sobre_turno' => 'Sobre Turno',
                    ])
                    ->default('turno')
                    ->columnSpan(2)
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('hora', null)),
                TextInfo::make('info')
                    ->hidden(fn(Get $get) => $get('tipo') == 'turno')
                    ->columnSpan(2),
                Grid::make('')
                    ->columns(2)
                    ->schema([
                DatePicker::make('fecha')
                    ->required()
                    ->placeholder('Seleccione una fecha')
                    ->live()
                    ->native(false)
                    ->afterStateUpdated(fn (Set $set) => $set('hora', null)),
                Select::make('hora')
                    ->required()
                    ->placeholder('Seleccione un horario')
                    ->searchable()
                    ->options( function (Get $get) {
                        $fecha = Carbon::parse($get('fecha'))->format('Y-m-d');
                        $tipo = $get('tipo') ?? 'turno';
                        return Auth::user()->horariosDisponibles($fecha, $tipo);
                    })
                ]),
                Grid::make('')
                    ->columns(2)
                    ->schema([
                Select::make('paciente_id')
                    ->label('Paciente')
                    ->options(Paciente::selectOptions())    
                    ->searchable()
                    ->required(),
                Select::make('estado')
                    ->required()
                    ->searchable()
                    ->options(EstadosTurno::class)
                    ->default(EstadosTurno::Pendiente),
                    ]),
                Textarea::make('notas')
                    ->label('Notas')
                    ->placeholder('Notas adicionales')
                    ->rows(3)
                    ->columnSpan(2)
                    ->autosize(),
                Hidden::make('medico_id')
                    ->default(Auth::user()->medico_id),
            ]);
            
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modificado')
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
            ->filters([
                SelectFilter::make('paciente_id')
                ->label('Paciente')
                ->options(Paciente::selectOptions())    
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
                Tables\Actions\ViewAction::make(),
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
            // 'create' => Pages\CreateTurno::route('/create'),
            // 'edit' => Pages\EditTurno::route('/{record}/edit'),
        ];
    }
}
