<?php

namespace App\Filament\Resources;

use App\Enums\Roles;
use App\Filament\Resources\HistoriaClinicaResource\Pages\ViewHistoriaClinica;
use App\Filament\Resources\PacienteResource\Pages;
use App\Filament\Resources\PacienteResource\RelationManagers;
use App\Models\Paciente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Split::make([
                    Forms\Components\TextInput::make('nombre')
                        ->placeholder('Nombre del paciente')
                        ->required(),
                    Forms\Components\TextInput::make('apellido')
                        ->placeholder('Apellido del paciente')
                        ->required(),
                    Forms\Components\TextInput::make('dni')
                        ->placeholder('DNI sin puntos ni guiones')
                        ->label('DNI')
                        ->required()
                ])
                ->columnSpan('full')
                ->columns(3),
                Forms\Components\Select::make('obra_social')
                    ->options(config('paciente.obras_sociales'))
                    // Get the search results from config, if the search is not found, return the search itself
                    ->getSearchResultsUsing(function ($search) {
                        $default = collect(config('paciente.obras_sociales'))
                            ->filter(function ($obra_social, $key) use ($search) {
                                return str_contains($key, $search) || str_contains($obra_social, $search);
                            })
                            ->mapWithKeys(function ($obra_social, $key) {
                                return [$key => $obra_social];
                            });
                        if($default->isEmpty()) {
                            return [$search => ucfirst($search)];
                        } else {
                            return $default;
                        }
                    })
                    ->searchable(),
                Forms\Components\TextInput::make('afiliado')
                    ->placeholder('Nro de Afiliado')
                    ->label('Nro Afiliado'),
                Forms\Components\TextInput::make('email')
                    ->placeholder('Correo Electrónico')
                    ->email(),
                Forms\Components\TextInput::make('telefono')
                    ->placeholder('Teléfono de contacto')
                    ->label('Teléfono'),
                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->label('Fecha de Nacimiento')
                    ->native(false)
                    ->placeholder('01/01/1990'),
                Forms\Components\TextInput::make('direccion')
                    ->placeholder('Dirección del paciente')
                    ->label('Dirección'),
                Forms\Components\Hidden::make('medico_id')
                    ->default(Auth::user()->medico_id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(['nombre', 'apellido'])
                    ->state(function ($record) {
                        return ucfirst($record->apellido).' '.ucfirst($record->nombre);
                    }),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),
                TextColumn::make('dni')
                    ->label('DNI')
                    ->searchable()
                    ->copyable()
                    ->color('info'),
                TextColumn::make('afiliado')
                    ->copyable()
                    ->searchable()
                    ->color('warning'),
                TextColumn::make('obra_social')
                        ->searchable()
                        ->badge()
                        ->state(fn($record) => config('paciente.obras_sociales')[$record->obra_social] ?? ucfirst($record->obra_social))
                        ->color(fn($record) => config('paciente.obras_sociales_colores')[$record->obra_social] ?? 'gray'),
                TextColumn::make('fecha_nacimiento')->label('Nacimiento')
                    ->state(function ($record) {
                        if (!$record->fecha_nacimiento) return null;
                        $fecha = \Carbon\Carbon::parse($record->fecha_nacimiento);
                        return $fecha->format('d/m/Y').', '.$fecha->age.' años';
                    })
                    ->searchable(),
                TextColumn::make('medico.name')->label('Médico')
                    ->state(function ($record) {
                        return ucfirst($record->medico->name.' '.$record->medico->surname);
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Historias Clínicas')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('info')
                    ->iconButton()
                    ->url(fn (Paciente $record) => HistoriaClinicaResource::getUrl('viewFile', ['paciente_id' => $record->id]))
                    ->hidden(fn () => role(Roles::Secretario)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->hidden(fn () => role(Roles::Secretario)),
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HistoriasclinicasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPacientes::route('/'),
            // 'create' => Pages\CreatePaciente::route('/create'),
            // 'edit' => Pages\EditPaciente::route('/{record}/edit'),
            // 'view' => Pages\ViewPaciente::route('/{record}'),
        ];
    }
}
