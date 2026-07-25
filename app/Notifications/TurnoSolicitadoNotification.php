<?php

namespace App\Notifications;

use App\Filament\Pages\CalendarioTurnos;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class TurnoSolicitadoNotification extends Notification
{
    public function __construct(private Turno $turno) {}

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(mixed $notifiable): array
    {
        $turno = $this->turno;
        $fecha = Carbon::parse($turno->fecha)->format('d/m/Y');

        return FilamentNotification::make()
            ->title('Nueva solicitud de turno')
            ->body("Se pidió un turno desde el portal para el {$fecha} a las {$turno->hora} hs.")
            ->icon('heroicon-o-calendar-days')
            ->iconColor('warning')
            ->actions([
                \Filament\Notifications\Actions\Action::make('ver')
                    ->label('Ver turnos')
                    ->url(CalendarioTurnos::getUrl())
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
