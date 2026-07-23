<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ResumenTurnosMananaMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Carbon $fecha,
        public Collection $turnosPorMedico,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Turnos de mañana '.$this->fecha->format('d/m/Y'))
            ->view('emails.turnos-manana');
    }
}
