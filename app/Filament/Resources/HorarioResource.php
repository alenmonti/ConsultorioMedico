<?php

namespace App\Filament\Resources;

use App\Enums\Dias;
use App\Enums\Mes;
use App\Filament\Resources\HorarioResource\Pages;
use App\Models\Horario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HorarioResource extends Resource
{
    protected static ?string $model = Horario::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $title = 'Disponibilidad Horaria';
    protected static ?string $navigationLabel = 'Horarios';
    protected static ?string $navigationGroup = 'Configuaración';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('dia')
                    ->options(Dias::class)
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('intervalo')
                    ->options([
                        '00:20' => '20 minutos',
                        // '00:40' => '40 minutos',
                        // '01:00' => '1 hora',
                    ])
                    ->default('00:20')
                    ->required(),
                Forms\Components\TimePicker::make('desde')
                    ->seconds(false)
                    ->step(60)
                    ->default('09:00')
                    ->required(),
                Forms\Components\TimePicker::make('hasta')
                    ->seconds(false)
                    ->step(60)
                    ->default('18:00')
                    ->required(),
                Forms\Components\Toggle::make('activo_sistema')
                    ->label('Activo en sistema')
                    ->default(true)
                    ->helperText('Si está desactivado, este horario no genera turnos en el sistema.'),
                Forms\Components\Toggle::make('activo_portal')
                    ->label('Activo en portal')
                    ->default(false)
                    ->helperText('Si está activado, este horario es visible para reservas desde el portal web.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->defaultSort('dia', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('dia')
                    ->badge(),
                Tables\Columns\TextColumn::make('desde')
                    ->time('H:i a'),
                Tables\Columns\TextColumn::make('hasta')
                    ->time('H:i a'),
                Tables\Columns\TextColumn::make('intervalo')
                    ->time('i \m\i\n')
                    ->badge()
                    ->color('info')
                    ->visibleFrom('sm'),
                Tables\Columns\IconColumn::make('activo_sistema')
                    ->label('Sistema')
                    ->boolean()
                    ->visibleFrom('sm'),
                Tables\Columns\IconColumn::make('activo_portal')
                    ->label('Portal')
                    ->boolean()
                    ->visibleFrom('sm'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('anio')
                    ->label('Año')
                    ->options(array_combine(config('horario.anios_disponibles'), config('horario.anios_disponibles')))
                    ->default(now()->year),
                Tables\Filters\SelectFilter::make('mes')
                    ->label('Mes')
                    ->options(Mes::class)
                    ->default(now()->month),
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
            'index' => Pages\ListHorarios::route('/'),
        ];
    }
}
