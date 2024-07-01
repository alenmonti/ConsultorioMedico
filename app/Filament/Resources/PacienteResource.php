<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Filament\Resources\PacienteResource\RelationManagers;
use App\Models\Paciente;
use Carbon\Carbon;
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
use Illuminate\Http\Client\Request;
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
                Forms\Components\TextInput::make('nombre')
                    ->placeholder('Nombre del paciente')
                    ->required(),
                Forms\Components\TextInput::make('apellido')
                    ->placeholder('Apellido del paciente')
                    ->required(),
                Forms\Components\TextInput::make('dni')
                    ->placeholder('DNI sin puntos ni guiones')
                    ->label('DNI')
                    ->required(),
                Forms\Components\TextInput::make('afiliado')
                    ->placeholder('Nro de Afiliado')
                    ->label('Nro Afiliado')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->placeholder('Correo Electrónico')
                    ->email(),
                Forms\Components\TextInput::make('telefono')
                    ->placeholder('Teléfono de contacto')
                    ->label('Teléfono'),
                Forms\Components\Datepicker::make('fecha_nacimiento')
                    ->label('Fecha de Nacimiento')
                    ->native(false)
                    ->default('1990-01-01')
                    ->required(),
                Forms\Components\Hidden::make('medico_id')
                    ->default(Auth::user()->id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->state(function ($record) {
                        return ucfirst($record->apellido).' '.ucfirst($record->nombre);
                    }),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('dni')
                    ->label('DNI')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('afiliado')
                    ->copyable()
                    ->searchable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('fecha_nacimiento')->label('Nacimiento')
                    ->state(function ($record) {
                        $fecha = \Carbon\Carbon::parse($record->fecha_nacimiento);
                        return $fecha->format('d/m/Y').', '.$fecha->age.' años';
                    })
                    ->searchable(),
                TextColumn::make('medico.name')->label('Médico')
                    ->state(function ($record) {
                        return ucfirst($record->medico->name);
                    }),
            ])
            ->filters([
                Filter::make('nombre')
                    ->form([Forms\Components\TextInput::make('nombre')->label('Nombre o Apellido')])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->where('nombre', 'like', '%'.$data['nombre'].'%')
                            ->orWhere('apellido', 'like', '%'.$data['nombre'].'%');
                    }),
                Filter::make('dni')
                    ->form([Forms\Components\TextInput::make('dni')->label('DNI')])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->where('dni', 'like', '%'.$data['dni'].'%');
                    }),
                Filter::make('email')
                    ->form([Forms\Components\TextInput::make('email')])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->where('email', 'like', '%'.$data['email'].'%');
                    }),
                Filter::make('telefono')
                    ->form([Forms\Components\TextInput::make('telefono')])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->where('telefono', 'like', '%'.$data['telefono'].'%');
                    }),
                Filter::make('afiliado')
                    ->form([Forms\Components\TextInput::make('afiliado')])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->where('afiliado', 'like', '%'.$data['afiliado'].'%');
                    }),
                
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
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
