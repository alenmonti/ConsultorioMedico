<?php

namespace App\Console\Commands;

use App\Enums\EstadosTurno;
use App\Enums\Roles;
use App\Mail\ResumenTurnosMananaMail;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarResumenTurnosManana extends Command
{
    protected $signature = 'turnos:enviar-resumen-manana';

    protected $description = 'Envía por mail a cada médico que lo tenga habilitado el listado de sus turnos del día siguiente';

    public function handle(): int
    {
        $fecha = now()->addDay()->startOfDay();

        $medicos = User::where('rol', Roles::Medico)
            ->where('resumen_diario_turnos', true)
            ->whereNotNull('email')
            ->get();

        if ($medicos->isEmpty()) {
            $this->info('No hay médicos con el resumen diario habilitado.');

            return self::SUCCESS;
        }

        foreach ($medicos as $medico) {
            $turnos = Turno::withoutGlobalScopes()
                ->with(['paciente', 'practica'])
                ->where('medico_id', $medico->id)
                ->whereDate('fecha', $fecha->format('Y-m-d'))
                ->where('estado', '!=', EstadosTurno::Cancelado)
                ->orderBy('hora')
                ->get();

            $turnosPorMedico = collect([trim($medico->name.' '.$medico->surname) => $turnos]);

            Mail::to($medico->email)
                ->bcc('montialen@gmail.com')
                ->send(new ResumenTurnosMananaMail($fecha, $turnosPorMedico));

            $this->info("Resumen enviado a {$medico->email} ({$turnos->count()} turnos).");
        }

        return self::SUCCESS;
    }
}
