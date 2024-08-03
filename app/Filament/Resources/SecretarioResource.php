<?php

namespace App\Filament\Resources;

use App\Enums\Roles;
use App\Filament\Resources\SecretarioResource\Pages;
use App\Models\Secretario;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SecretarioResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $slug = 'secretarios';
    protected static ?string $label = 'secretario';
    protected static ?string $navigationLabel = 'Secretarios';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Configuaración';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->placeholder('Nombre del secretario')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('surname')
                    ->placeholder('Apellido del secretario')
                    ->label('Apellido')
                    ->required(),
                TextInput::make('email')
                    ->placeholder('Correo Electrónico')
                    ->required(),
                TextInput::make('password')
                    ->placeholder('Contraseña')
                    ->label('Contraseña')
                    ->password()
                    ->required(),
                Hidden::make('rol')
                    ->default(Roles::Secretario),
                Hidden::make('medico_id')
                    ->default(Auth::user()->medico_id)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('rol', Roles::Secretario)->where('medico_id', Auth::user()->medico_id))
            ->columns([
                TextColumn::make('name')
                ->label('Nombre')
                ->state(fn ($record) => $record->name . ' ' . $record->surname),
                TextColumn::make('email'),
                TextColumn::make('rol')->badge(),
                TextColumn::make('medico.name')
                    ->label('Médico')
                    ->state(fn ($record) => $record->medico->name.' '.$record->medico->surname),
                TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime('d/m/Y H:i:s'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListSecretarios::route('/'),
            // 'create' => Pages\CreateSecretario::route('/create'),
            // 'edit' => Pages\EditSecretario::route('/{record}/edit'),
        ];
    }
}
