<?php

namespace App\Filament\Resources;

use App\Enums\Dias;
use App\Filament\Resources\HorarioResource\Pages;
use App\Filament\Resources\HorarioResource\RelationManagers;
use App\Models\Horario;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use HusamTariq\FilamentTimePicker\Forms\Components\TimePickerField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ramsey\Uuid\Type\Time;

class HorarioResource extends Resource
{
    protected static ?string $model = Horario::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $title = 'Disponibilidad Horaria';
    protected static ?string $navigationLabel = 'Horarios';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('dia')
                    ->options(Dias::class)
                    ->required(),
                Forms\Components\Select::make('intervalo')
                    ->options([
                        '00:05' => '5 minutos',
                        '00:10' => '10 minutos',
                        '00:15' => '15 minutos',
                        '00:20' => '20 minutos',
                        '00:30' => '30 minutos',
                        '00:45' => '45 minutos',
                        '01:00' => '1 hora',
                    ])
                    ->required(),
                TimePickerField::make('desde')
                    ->default('09:00')
                    ->required(),
                TimePickerField::make('hasta')
                    ->default('18:00')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->color('info'),
            ])
            ->filters([
                //
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
            // 'create' => Pages\CreateHorario::route('/create'),
            // 'edit' => Pages\EditHorario::route('/{record}/edit'),
        ];
    }
}
