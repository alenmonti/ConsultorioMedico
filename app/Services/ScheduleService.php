<?php

namespace App\Services;

use App\Models\Horario;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;

class ScheduleService
{
    public function horariosDisponibles(User $medico, string $fecha, string $tipo = 'turno'): array
    {
        $diaSemana = $this->dayOfWeekToString(Carbon::parse($fecha)->dayOfWeek);

        $configHorarios = Horario::where('medico_id', $medico->medico_id)
            ->where('dia', $diaSemana)
            ->get();

        if ($configHorarios->isEmpty()) {
            return [];
        }

        $slots = [];
        foreach ($configHorarios as $horario) {
            $desde = Carbon::parse($horario->desde);
            $hasta = Carbon::parse($horario->hasta);
            $intervalo = (int) Carbon::parse($horario->intervalo)->format('i');
            while ($desde <= $hasta) {
                $slots[] = $desde->format('H:i');
                $desde->addMinutes($intervalo);
            }
        }

        $horariosOcupados = Turno::where('medico_id', $medico->medico_id)
            ->where('fecha', $fecha)
            ->pluck('hora')
            ->toArray();

        $result = $tipo !== 'turno'
            ? array_intersect($slots, $horariosOcupados)
            : array_diff($slots, $horariosOcupados);

        return array_combine($result, $result);
    }

    public function diasNoDisponibles(User $medico, string $desde, string $hasta): array
    {
        $fechaDesde = Carbon::parse($desde);
        $fechaHasta = Carbon::parse($hasta);

        $horariosPorDia = Horario::where('medico_id', $medico->medico_id)
            ->get()
            ->groupBy(fn($h) => $h->dia instanceof \BackedEnum ? $h->dia->value : $h->dia);

        $turnosPorFecha = Turno::where('medico_id', $medico->medico_id)
            ->whereBetween('fecha', [$fechaDesde->format('Y-m-d'), $fechaHasta->format('Y-m-d')])
            ->get()
            ->groupBy('fecha');

        $diasNoDisponibles = [];
        $cursor = $fechaDesde->copy();

        while ($cursor <= $fechaHasta) {
            $fechaStr = $cursor->format('Y-m-d');
            $diaSemana = $this->dayOfWeekToString($cursor->dayOfWeek);

            $configHorarios = $horariosPorDia->get($diaSemana, collect());

            if ($configHorarios->isEmpty()) {
                $diasNoDisponibles[] = $fechaStr;
                $cursor->addDay();
                continue;
            }

            $slots = [];
            foreach ($configHorarios as $horario) {
                $time = Carbon::parse($horario->desde);
                $fin = Carbon::parse($horario->hasta);
                $intervalo = (int) Carbon::parse($horario->intervalo)->format('i');
                while ($time <= $fin) {
                    $slots[] = $time->format('H:i');
                    $time->addMinutes($intervalo);
                }
            }

            $horasOcupadas = $turnosPorFecha->get($fechaStr, collect())
                ->pluck('hora')
                ->toArray();

            if (empty(array_diff($slots, $horasOcupadas))) {
                $diasNoDisponibles[] = $fechaStr;
            }

            $cursor->addDay();
        }

        return $diasNoDisponibles;
    }

    private function dayOfWeekToString(int $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            0 => 'domingo',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
        };
    }
}
