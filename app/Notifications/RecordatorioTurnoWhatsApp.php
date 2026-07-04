<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Channels\WhatsAppMessage;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;

class RecordatorioTurnoWhatsApp extends Notification
{
    public function __construct(private Turno $turno) {}

    public function via(mixed $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp(mixed $notifiable): WhatsAppMessage
    {
        $turno = $this->turno;
        $paciente = $turno->paciente;
        $medico = $turno->medico;
        $fecha = Carbon::parse($turno->fecha)->translatedFormat('l d \d\e F');
        $hora = Carbon::parse($turno->hora)->format('H:i');

        $linkConfirmar = route('turno.confirmar', $turno->id) . '?token=' . $turno->turno_token;
        $linkCancelar = route('turno.cancelar', $turno->id) . '?token=' . $turno->turno_token;

        $texto = "Hola {$paciente->nombre} 👋\n\n"
            . "Te recordamos que tenés un turno programado con el Dr./Dra. *{$medico->name}*:\n\n"
            . "📅 *Fecha:* {$fecha}\n"
            . "🕐 *Hora:* {$hora} hs\n\n"
            . "Por favor, confirmá o cancelá tu turno usando los siguientes links:\n\n"
            . "✅ Confirmar: \n"
            . "{$linkConfirmar}\n\n"
            . "❌ Cancelar: \n"
            . "{$linkCancelar}";

        return (new WhatsAppMessage($texto))->to($paciente->telefono);
    }
}
