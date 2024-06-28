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
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('paciente_id')
                    ->relationship('paciente', 'nombre')
                    ->required(),
                DatePicker::make('fecha')
                    ->default(now())
                    ->native(false)
                    ->required(),
                Textarea::make('diagnostico')
                    ->placeholder('Diagnostico del paciente'),
                Textarea::make('motivo')
                    ->placeholder('Motivo de la consulta'),
                Textarea::make('estudios')
                    ->placeholder('Estudios realizados'),
                Textarea::make('tratamiento')
                    ->placeholder('Tratamiento del paciente'),
                Textarea::make('medicamentos')
                    ->placeholder('Medicamentos recetados'),
                Textarea::make('examen_fisico')
                    ->placeholder('Resultados del examen fisico'),
                Textarea::make('resultados')
                    ->placeholder('Resultados de los estudios'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paciente.nombre')
                    ->formatStateUsing(function ($record) {
                        return $record->paciente->nombre.' '.$record->paciente->apellido.', '.$record->paciente->dni;
                    }),
                TextColumn::make('fecha')
                    ->date(),
                TextColumn::make('diagnostico'),
                TextColumn::make('motivo'),
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
