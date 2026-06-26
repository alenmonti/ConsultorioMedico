<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PracticaResource\Pages;
use App\Models\Practica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PracticaResource extends Resource
{
    protected static ?string $model = Practica::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Práctica';
    protected static ?string $pluralModelLabel = 'Prácticas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tipo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('costo')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0),
                Forms\Components\Select::make('duracion_min')
                    ->required()
                    ->label('Duración (min)')
                    ->options([
                        20 => '20 minutos',
                        40 => '40 minutos',
                        60 => '60 minutos',
                    ]),
                Forms\Components\TextInput::make('codigo_osde')
                    ->label('Código OSDE')
                    ->maxLength(255),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull()
                    ->autosize(),
                Forms\Components\Hidden::make('medico_id')
                    ->default(fn () => Auth::user()->medico_id),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Práctica')
                    ->searchable(['nombre', 'tipo'])
                    ->sortable('nombre'),
                Tables\Columns\TextColumn::make('tipo')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('costo')
                    ->money('ARS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duracion_min')
                    ->label('Duración')
                    ->suffix(' min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo_osde')
                    ->label('Cód. OSDE')
                    ->color('gray'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPracticas::route('/'),
        ];
    }
}
