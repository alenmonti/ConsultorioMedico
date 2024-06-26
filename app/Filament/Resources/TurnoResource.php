<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TurnoResource\Pages;
use App\Filament\Resources\TurnoResource\RelationManagers;
use App\Models\Turno;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TurnoResource extends Resource
{
    protected static ?string $model = Turno::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
