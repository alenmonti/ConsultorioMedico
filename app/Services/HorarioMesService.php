<?php

namespace App\Services;

use App\Models\Horario;
use App\Models\User;
use Carbon\Carbon;

class HorarioMesService
{
    public function asegurarMesConfigurado(User $medico, int $anio, int $mes): void
    {
        $yaConfigurado = Horario::where('medico_id', $medico->medico_id)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->exists();

        if ($yaConfigurado) {
            return;
        }

        $cursor = Carbon::create($anio, $mes, 1)->subMonth();

        for ($i = 0; $i < 24; $i++) {
            $horariosMesAnterior = Horario::where('medico_id', $medico->medico_id)
                ->where('anio', $cursor->year)
                ->where('mes', $cursor->month)
                ->get();

            if ($horariosMesAnterior->isNotEmpty()) {
                foreach ($horariosMesAnterior as $horario) {
                    Horario::create([
                        'medico_id' => $medico->medico_id,
                        'anio' => $anio,
                        'mes' => $mes,
                        'dia' => $horario->dia,
                        'desde' => $horario->desde,
                        'hasta' => $horario->hasta,
                        'intervalo' => $horario->intervalo,
                        'activo_sistema' => $horario->activo_sistema,
                        'activo_portal' => $horario->activo_portal,
                    ]);
                }

                return;
            }

            $cursor->subMonth();
        }
    }
}
