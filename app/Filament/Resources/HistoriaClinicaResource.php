<?php

namespace App\Filament\Resources;

use App\Models\HistoriaClinica;
use App\Filament\Resources\HistoriaClinicaResource\Pages;
use App\Filament\Resources\HistoriaClinicaResource\RelationManagers;
use DateTime;
use Filament\Forms;
use Filament\Forms\Components\{DatePicker, Select, Textarea};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HistoriaClinicaResource extends Resource
{
    protected static ?string $model = HistoriaClinica::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $title = 'Historias Clinicas';
    protected static ?string $navigationLabel = 'Historias Clinicas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('paciente_id')
                    ->relationship('paciente', 'nombre')
                    ->required(),
                DatePicker::make('fecha')
                    ->default(now())
                    ->required(),
                Textarea::make('diagnostico'),
                Textarea::make('motivo'),
                Textarea::make('estudios'),
                Textarea::make('tratamiento'),
                Textarea::make('medicamentos'),
                Textarea::make('examen_fisico'),
                Textarea::make('resultados'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paciente.nombre'),
                TextColumn::make('diagnostico'),
                TextColumn::make('motivo'),
                TextColumn::make('estudios'),
                TextColumn::make('tratamiento'),
                TextColumn::make('medicamentos'),
                TextColumn::make('examen_fisico'),
                TextColumn::make('resultados'),
            ])
            ->filters([
                
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListHistoriaClinicas::route('/'),
            'create' => Pages\CreateHistoriaClinica::route('/create'),
            'edit' => Pages\EditHistoriaClinica::route('/{record}/edit'),
            'view' => Pages\ViewHistoriaClinica::route('/{record}'),
        ];
    }
}
