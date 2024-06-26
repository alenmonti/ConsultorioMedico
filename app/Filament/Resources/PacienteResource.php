<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Filament\Resources\PacienteResource\RelationManagers;
use App\Models\Paciente;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required(),
                Forms\Components\TextInput::make('apellido')
                    ->label('Apellido')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email(),
                Forms\Components\TextInput::make('telefono')
                    ->label('Teléfono'),
                Forms\Components\TextInput::make('dni')
                    ->label('DNI')
                    ->required(),
                Forms\Components\TextInput::make('afiliado')
                    ->label('Nro Afiliado')
                    ->required(),
                Forms\Components\Datepicker::make('fecha_nacimiento')
                    ->label('Fecha de Nacimiento')
                    ->required(),
                Forms\Components\Hidden::make('medico_id')
                    ->default(Auth::user()->id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('apellido')->searchable(),
                TextColumn::make('nombre')->searchable(),
                TextColumn::make('email'),
                TextColumn::make('telefono')->label('Teléfono'),
                TextColumn::make('dni')->label('DNI')->copyable()->badge()->color('primary'),
                TextColumn::make('afiliado')->copyable()->badge()->color('primary'),
                TextColumn::make('fecha_nacimiento')->label('Nacimiento')->date('d/m/Y'),
                TextColumn::make('fecha_nacimiento')->label('Edad')
                    ->formatStateUsing(function ($record) {
                        return \Carbon\Carbon::parse($record->fecha_nacimiento)->age . ' años';
                    }),
                TextColumn::make('medico.name')->label('Médico')
                    ->formatStateUsing(function ($record) {
                        return ucfirst($record->medico->name);
                    }),
            ])
            ->filters([
                Filter::make('nombre')
                    ->form([
                        Forms\Components\TextInput::make('nombre')
                    ])
                    ->query(function (Builder $query, $data) {
                        return $query->where('nombre', 'like', '%' . $data['nombre'] . '%');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->iconButton(),
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
            'index' => Pages\ListPacientes::route('/'),
            'create' => Pages\CreatePaciente::route('/create'),
            'edit' => Pages\EditPaciente::route('/{record}/edit'),
        ];
    }
}
