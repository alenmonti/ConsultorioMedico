<?php

namespace App\Jobs;

use App\Models\Turno;
use App\Notifications\RecordatorioTurnoWhatsApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnviarRecordatorioWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $turnoId) {}

    public function handle(): void
    {
        $turno = Turno::withoutGlobalScopes()
            ->with(['paciente', 'medico'])
            ->find($this->turnoId);

        if (! $turno || ! $turno->paciente || ! $turno->paciente->telefono) {
            Log::warning("EnviarRecordatorioWhatsAppJob: turno {$this->turnoId} sin paciente o teléfono, se omite.");
            return;
        }

        if (! $turno->turno_token) {
            $token = Str::random(40);
            while (Turno::withoutGlobalScopes()->where('turno_token', $token)->exists()) {
                $token = Str::random(40);
            }
            $turno->turno_token = $token;
            $turno->save();
        }

        // Enviar primero; si lanza excepción no se marca como enviado
        $turno->paciente->notify(new RecordatorioTurnoWhatsApp($turno));

        $turno->recordatorio_enviado_at = now();
        $turno->save();
    }
}
