<?php

namespace App\Filament\Resources;

use App\Enums\Roles;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Split;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $slug = 'users';
    protected static ?string $label = 'User';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return user()->rol === Roles::Admin;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Forms\Components\TextInput::make('name')
                        ->placeholder('Nombre del Usuario')
                        ->label('Nombre')
                        ->required(),
                    Forms\Components\TextInput::make('surname')
                        ->placeholder('Apellido del Usuario')
                        ->label('Apellido')
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->placeholder('Correo Electrónico')
                        ->required(),
                ])
                ->columnSpanFull()
                ->columns(3),
                Forms\Components\Select::make('rol')
                    ->options(Roles::class)
                    ->required()
                    ->label('Rol')
                    ->searchable(),
                Forms\Components\TextInput::make('password')
                    ->placeholder('Contraseña')
                    ->label('Contraseña')
                    ->password()
                    ->required(),
                Forms\Components\Hidden::make('medico_id')
                    ->default(Auth::user()->medico_id)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->state(function ($record) {
                        return $record->name . ' ' . $record->surname;
                    })
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('rol')
                    ->badge(),
                TextColumn::make('medico.name')
                    ->label('Médico')
                    ->state(function ($record) {
                        return ucfirst($record->medico->name.' '.$record->medico->surname);
                    }),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i:s'),
                TextColumn::make('updated_at')
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
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
