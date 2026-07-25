<?php

namespace App\Notifications;

use App\Filament\Pages\CalendarioTurnos;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class TurnoCanceladoNotification extends Notification
{
    public function __construct(private Turno $turno) {}

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(mixed $notifiable): array
    {
        $turno = $this->turno;
        $paciente = $turno->paciente;
        $fecha = Carbon::parse($turno->fecha)->format('d/m/Y');

        return FilamentNotification::make()
            ->title('Turno cancelado')
            ->body(($paciente ? "{$paciente->nombre} {$paciente->apellido}" : 'El paciente') . " canceló su turno del {$fecha} a las {$turno->hora} hs.")
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->actions([
                \Filament\Notifications\Actions\Action::make('ver')
                    ->label('Ver turnos')
                    ->url(CalendarioTurnos::getUrl())
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
