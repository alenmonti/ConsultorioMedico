<?php

namespace App\Filament\Resources;

use App\Enums\Roles;
use App\Models\HistoriaClinica;
use App\Filament\Resources\HistoriaClinicaResource\Pages;
use App\Filament\Resources\HistoriaClinicaResource\RelationManagers;
use App\Models\Paciente;
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
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HistoriaClinicaResource extends Resource
{
    protected static ?string $model = HistoriaClinica::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $title = 'Historias Clinicas';
    protected static ?string $navigationLabel = 'Historias Clínicas';
    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->rol === Roles::Admin;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('paciente_id')
                    ->options(Paciente::selectOptions())
                    ->label('Paciente')
                    ->searchable()
                    ->required(),
                DatePicker::make('fecha')
                    ->default(now())
                    ->native(false)
                    ->required(),
                Textarea::make('diagnostico')
                    ->placeholder('Diagnóstico del paciente')
                    ->autosize(),
                Textarea::make('motivo')
                    ->placeholder('Motivo de la consulta')
                    ->autosize(),
                Textarea::make('estudios')
                    ->placeholder('Estudios realizados')
                    ->autosize(),
                Textarea::make('tratamiento')
                    ->placeholder('Tratamiento del paciente')
                    ->autosize(),
                Textarea::make('medicamentos')
                    ->placeholder('Medicamentos recetados')
                    ->autosize(),
                Textarea::make('examen_fisico')
                    ->placeholder('Resultados del examen fisico')
                    ->autosize(),
                Textarea::make('resultados')
                    ->placeholder('Resultados de los estudios')
                    ->autosize(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paciente.nombre')
                    ->state(function ($record) {
                        return $record->paciente?->apellido.' '.$record->paciente?->nombre.', '.$record->paciente?->dni;
                    }),
                TextColumn::make('fecha')
                    ->date()
                    ->sortable()
                    ->color('warning')
                    ->badge(),
                TextColumn::make('diagnostico')
                    ->label('Diagnóstico')
                    ->limit(50),
                TextColumn::make('motivo')
                    ->limit(50),
            ])
            ->filters([
                SelectFilter::make('paciente_id')
                    ->label('Paciente')
                    ->options(Paciente::selectOptions())    
                    ->searchable(),
                ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
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
            'viewFile' => Pages\ViewFIle::route('paciente/{paciente_id}'),
        ];
    }
}
