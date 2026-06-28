<?php

namespace App\Filament\Resources;

use App\Enums\Roles;
use App\Filament\Resources\RecordatoriosResource\Pages;
use App\Models\Turno;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

class RecordatoriosResource extends Resource
{
    protected static ?string $model = Turno::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Recordatorios';
    protected static ?string $modelLabel = 'Recordatorio';
    protected static ?string $pluralModelLabel = 'Recordatorios';
    protected static ?string $slug = 'recordatorios';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return TurnoResource::form($form);
    }

    public static function canViewAny(): bool
    {
        $rol = auth()->user()?->rol;
        return in_array($rol, [Roles::Admin, Roles::Medico, Roles::Secretario]);
    }

    public static function canEdit(Model $record): bool
    {
        $rol = auth()->user()?->rol;
        return in_array($rol, [Roles::Admin, Roles::Medico, Roles::Secretario]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecordatorios::route('/'),
        ];
    }
}
