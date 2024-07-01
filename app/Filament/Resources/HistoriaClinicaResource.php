<?php

namespace App\Filament\Resources;

use App\Models\HistoriaClinica;
use App\Filament\Resources\HistoriaClinicaResource\Pages;
use App\Filament\Resources\HistoriaClinicaResource\RelationManagers;
use Carbon\Carbon;
use DateTime;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Components\{DatePicker, Select, Textarea};
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
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
                    ->state(function ($record) {
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informacion del paciente')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('paciente.nombre')
                            ->state(function ($record) {
                                return $record->paciente->nombre.' '.$record->paciente->apellido;
                            })
                            ->label('Nombre'),
                        TextEntry::make('paciente.fecha_nacimiento')
                            ->date('d/m/Y')
                            ->label('Fecha de nacimiento'),
                        TextEntry::make('paciente.fecha_nacimiento')
                            ->state(function ($record) {
                                return Carbon::parse($record->paciente->fecha_nacimiento)->age.' años';
                            })
                            ->label('Edad'),
                        TextEntry::make('paciente.dni')
                            ->label('DNI'),
                        TextEntry::make('paciente.telefono')
                            ->label('Telefono'),
                        TextEntry::make('paciente.email')
                            ->label('Email'),
                        TextEntry::make('paciente.medico.name')
                            ->label('Medico de cabecera'),
                        TextEntry::make('paciente.afiliado')
                            ->label('Obra social'),
                    ]),
                Section::make('Historia Clinica'.' - '.Carbon::parse($infolist->record->fecha)->format('d/m/Y'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(2)
                    ->schema([
                        FieldSet::make('Fecha')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('fecha')->label('')->date('d/m/Y')->columnSpan(2)]),
                        Fieldset::make('Motivo')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('motivo')->label('')->columnSpan(2)]),
                        Fieldset::make('Diagnostico')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('diagnostico')->label('')->columnSpan(2)]),
                        Fieldset::make('Estudios')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('estudios')->label('')->columnSpan(2)]),
                        Fieldset::make('Tratamiento')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('tratamiento')->label('')->columnSpan(2)]),
                        Fieldset::make('Medicamentos')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('medicamentos')->label('')->columnSpan(2)]),
                        Fieldset::make('Examen_fisico')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('examen_fisico')->label('')->columnSpan(2)]),
                        Fieldset::make('Resultados')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('resultados')->label('')->columnSpan(2)]),
                        // TextEntry::make('fecha')->date('d/m/Y'),
                        // TextEntry::make('motivo'),
                        // TextEntry::make('diagnostico'),
                        // TextEntry::make('estudios'),
                        // TextEntry::make('tratamiento'),
                        // TextEntry::make('medicamentos'),
                        // TextEntry::make('examen_fisico'),
                        // TextEntry::make('resultados'),
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
            'index' => Pages\ListHistoriaClinicas::route('/'),
            'create' => Pages\CreateHistoriaClinica::route('/create'),
            'edit' => Pages\EditHistoriaClinica::route('/{record}/edit'),
            'view' => Pages\ViewHistoriaClinica::route('/{record}'),
        ];
    }
}
