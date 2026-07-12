<?php

namespace App\Filament\Resources;

use App\Enums\EstadosTurno;
use App\Enums\TipoTurno;
use App\Filament\Resources\TurnoResource\Pages;
use App\Forms\Components\TextInfo;
use App\Models\Paciente;
use App\Models\Practica;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TurnoResource extends Resource
{
    protected static ?string $model = Turno::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?int $navigationSort = 1;

    public static function getFormSchema(): array
    {
        return [
            Select::make('tipo')
                ->required()
                ->searchable()
                ->label('Tipo de turno')
                ->options(TipoTurno::class)
                ->default(TipoTurno::Turno)
                ->columnSpan(2)
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('hora', null)),
            TextInfo::make('info')
                ->hidden(fn (Get $get) => $get('tipo') == 'turno')
                ->columnSpan(2),
            Select::make('practica_id')
                ->label('Práctica')
                ->options(Practica::selectOptions())
                ->searchable()
                ->default(fn () => (string) Practica::whereRaw('lower(nombre) = ?', ['consulta'])->value('id'))
                ->columnSpan(2)
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $hora = $get('hora');
                    if ($hora === null || $get('fecha') === null) {
                        return;
                    }

                    $fecha = Carbon::parse($get('fecha'))->format('Y-m-d');
                    $tipo = $get('tipo') ?? 'turno';
                    $practicaId = $get('practica_id');
                    $duracion = $practicaId
                        ? (Practica::find($practicaId)?->duracion_min ?? 20)
                        : 20;

                    if (! array_key_exists($hora, Auth::user()->horariosDisponibles($fecha, $tipo, $duracion))) {
                        $set('hora', null);
                    }
                }),
            Grid::make('')
                ->columns(2)
                ->schema([
                    DatePicker::make('fecha')
                        ->required()
                        ->placeholder('Seleccione una fecha')
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('hora', null)),
                    Select::make('hora')
                        ->required()
                        ->placeholder('Seleccione un horario')
                        ->searchable()
                        ->options(function (Get $get) {
                            $fecha = Carbon::parse($get('fecha'))->format('Y-m-d');
                            $tipo = $get('tipo') ?? 'turno';
                            $practicaId = $get('practica_id');
                            $duracion = $practicaId
                                ? (Practica::find($practicaId)?->duracion_min ?? 20)
                                : 20;

                            return Auth::user()->horariosDisponibles($fecha, $tipo, $duracion);
                        }),
                ]),
            Grid::make('')
                ->columns(2)
                ->schema([
                    Select::make('paciente_id')
                        ->label('Paciente')
                        ->options(Paciente::selectOptions())
                        ->searchable()
                        ->required()
                        ->createOptionForm(PacienteResource::getForm())
                        ->createOptionUsing(function (array $data): int {
                            $paciente = Paciente::create([
                                ...$data,
                                'medico_id' => Auth::user()->medico_id,
                            ]);

                            return $paciente->id;
                        })
                        ->createOptionAction(fn ($action) => $action->label('Crear paciente')),
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
            Hidden::make('senia_informada_at'),
            Hidden::make('senia_pagada_at'),
            Hidden::make('recordatorio_enviado_at'),
            Grid::make(3)
                ->columnSpan(2)
                ->schema([
                    Forms\Components\Toggle::make('_senia_informada')
                        ->label('Seña avisada')
                        ->dehydrated(false)
                        ->afterStateHydrated(fn ($component, $record) => $component->state($record?->senia_informada_at !== null))
                        ->live()
                        ->hint(fn (Get $get) => $get('senia_informada_at') ? Carbon::parse($get('senia_informada_at'))->format('d/m/Y H:i') : null)
                        ->afterStateUpdated(fn ($state, Set $set) => $set('senia_informada_at', $state ? now()->toDateTimeString() : null)),
                    Forms\Components\Toggle::make('_senia_pagada')
                        ->label('Seña pagada')
                        ->dehydrated(false)
                        ->afterStateHydrated(fn ($component, $record) => $component->state($record?->senia_pagada_at !== null))
                        ->live()
                        ->hint(fn (Get $get) => $get('senia_pagada_at') ? Carbon::parse($get('senia_pagada_at'))->format('d/m/Y H:i') : null)
                        ->afterStateUpdated(fn ($state, Set $set) => $set('senia_pagada_at', $state ? now()->toDateTimeString() : null)),
                    Forms\Components\Toggle::make('_recordatorio_enviado')
                        ->label('Recordatorio enviado')
                        ->dehydrated(false)
                        ->afterStateHydrated(fn ($component, $record) => $component->state($record?->recordatorio_enviado_at !== null))
                        ->live()
                        ->hint(fn (Get $get) => $get('recordatorio_enviado_at') ? Carbon::parse($get('recordatorio_enviado_at'))->format('d/m/Y H:i') : null)
                        ->afterStateUpdated(fn ($state, Set $set) => $set('recordatorio_enviado_at', $state ? now()->toDateTimeString() : null)),
                ]),
            Hidden::make('medico_id')
                ->default(Auth::user()->medico_id),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::getFormSchema());
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['paciente', 'medico', 'practica']);
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
                    }),

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
