<?php

namespace App\Notifications;

use App\Filament\Pages\CalendarioTurnos;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class TurnoConfirmadoNotification extends Notification
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
            ->title('Turno confirmado')
            ->body(($paciente ? "{$paciente->nombre} {$paciente->apellido}" : 'El paciente') . " confirmó su turno del {$fecha} a las {$turno->hora} hs.")
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->actions([
                \Filament\Notifications\Actions\Action::make('ver')
                    ->label('Ver turnos')
                    ->url(CalendarioTurnos::getUrl())
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
