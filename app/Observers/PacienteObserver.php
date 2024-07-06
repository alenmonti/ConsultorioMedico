<?php

namespace App\Observers;

use App\Models\Paciente;

class PacienteObserver
{
    public function creating(Paciente $paciente)
    {
        $paciente->medico_id = auth()->id();
    }
}
