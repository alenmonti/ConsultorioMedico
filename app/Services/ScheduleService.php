<?php

namespace App\Services;

use App\Enums\EstadosTurno;
use App\Models\Horario;
use App\Models\HorarioExclusion;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;

class ScheduleService
{
    public function horariosDisponibles(User $medico, string $fecha, string $tipo = 'turno', int $duracion = 20, bool $ignorarCancelados = false, bool $portal = false): array
    {
        $diaSemana = $this->dayOfWeekToString(Carbon::parse($fecha)->dayOfWeek);

        $configHorarios = Horario::where('medico_id', $medico->medico_id)
            ->where('dia', $diaSemana)
            ->where('activo_sistema', true)
            ->when($portal, fn ($q) => $q->where('activo_portal', true))
            ->get();

        if ($configHorarios->isEmpty()) {
            return [];
        }

        $exclusion = HorarioExclusion::where('medico_id', $medico->medico_id)
            ->where('fecha', $fecha)
            ->first();

        if ($exclusion && $exclusion->todo_el_dia) {
            return [];
        }

        $intervalo = 20;
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

        if ($exclusion) {
            $exDesde = Carbon::parse($exclusion->desde);
            $exHasta = Carbon::parse($exclusion->hasta);
            $slots = array_values(array_filter(
                $slots,
                fn ($s) => ! (Carbon::parse($s)->between($exDesde, $exHasta, true))
            ));
        }

        $turnosDelDia = Turno::where('medico_id', $medico->medico_id)
            ->where('fecha', $fecha)
            ->when($ignorarCancelados, fn ($q) => $q->where('estado', '!=', EstadosTurno::Cancelado))
            ->with('practica')
            ->get();

        $slotsOcupados = $this->expandirSlotsOcupados($turnosDelDia, $intervalo);

        if ($tipo !== 'turno') {
            $horasConTurno = $turnosDelDia->pluck('hora')->toArray();
            $result = array_intersect($slots, $horasConTurno);
            return array_combine($result, $result);
        }

        $bloquesSolicitados = max(1, (int) ceil($duracion / $intervalo));
        $slotsSet = array_flip($slots);

        $result = [];
        foreach ($slots as $slot) {
            $libre = true;
            $inicio = Carbon::parse($slot);
            for ($i = 0; $i < $bloquesSolicitados; $i++) {
                $check = $inicio->copy()->addMinutes($i * $intervalo)->format('H:i');
                if (in_array($check, $slotsOcupados) || ! isset($slotsSet[$check])) {
                    $libre = false;
                    break;
                }
            }
            if ($libre) {
                $result[$slot] = $slot;
            }
        }

        return $result;
    }

    public function diasNoDisponibles(User $medico, string $desde, string $hasta, bool $ignorarCancelados = false, bool $portal = false): array
    {
        $fechaDesde = Carbon::parse($desde);
        $fechaHasta = Carbon::parse($hasta);

        $horariosPorDia = Horario::where('medico_id', $medico->medico_id)
            ->where('activo_sistema', true)
            ->when($portal, fn ($q) => $q->where('activo_portal', true))
            ->get()
            ->groupBy(fn ($h) => $h->dia instanceof \BackedEnum ? $h->dia->value : $h->dia);

        $turnosPorFecha = Turno::where('medico_id', $medico->medico_id)
            ->whereBetween('fecha', [$fechaDesde->format('Y-m-d'), $fechaHasta->format('Y-m-d')])
            ->when($ignorarCancelados, fn ($q) => $q->where('estado', '!=', EstadosTurno::Cancelado))
            ->with('practica')
            ->get()
            ->groupBy('fecha');

        $exclusionesPorFecha = HorarioExclusion::where('medico_id', $medico->medico_id)
            ->whereBetween('fecha', [$fechaDesde->format('Y-m-d'), $fechaHasta->format('Y-m-d')])
            ->get()
            ->keyBy(fn ($e) => $e->fecha->format('Y-m-d'));

        $diasNoDisponibles = [];
        $cursor = $fechaDesde->copy();

        while ($cursor <= $fechaHasta) {
            $fechaStr = $cursor->format('Y-m-d');
            $diaSemana = $this->dayOfWeekToString($cursor->dayOfWeek);

            $configHorarios = $horariosPorDia->get($diaSemana, collect());
            $exclusion = $exclusionesPorFecha->get($fechaStr);

            if ($configHorarios->isEmpty() || ($exclusion && $exclusion->todo_el_dia)) {
                $diasNoDisponibles[] = $fechaStr;
                $cursor->addDay();
                continue;
            }

            $intervalo = (int) Carbon::parse($configHorarios->first()->intervalo)->format('i');
            $slots = [];
            foreach ($configHorarios as $horario) {
                $time = Carbon::parse($horario->desde);
                $fin = Carbon::parse($horario->hasta);
                $iv = (int) Carbon::parse($horario->intervalo)->format('i');
                while ($time <= $fin) {
                    $slots[] = $time->format('H:i');
                    $time->addMinutes($iv);
                }
            }

            if ($exclusion) {
                $exDesde = Carbon::parse($exclusion->desde);
                $exHasta = Carbon::parse($exclusion->hasta);
                $slots = array_values(array_filter(
                    $slots,
                    fn ($s) => ! (Carbon::parse($s)->between($exDesde, $exHasta, true))
                ));
            }

            $slotsOcupados = $this->expandirSlotsOcupados(
                $turnosPorFecha->get($fechaStr, collect()),
                $intervalo
            );

            if (empty($slots) || empty(array_diff($slots, $slotsOcupados))) {
                $diasNoDisponibles[] = $fechaStr;
            }

            $cursor->addDay();
        }

        return $diasNoDisponibles;
    }

    private function expandirSlotsOcupados($turnos, int $intervalo): array
    {
        $ocupados = [];
        foreach ($turnos as $turno) {
            $duracionTurno = $turno->practica?->duracion_min ?? $intervalo;
            $bloques = max(1, (int) ceil($duracionTurno / $intervalo));
            $inicio = Carbon::parse($turno->hora);
            for ($i = 0; $i < $bloques; $i++) {
                $ocupados[] = $inicio->copy()->addMinutes($i * $intervalo)->format('H:i');
            }
        }

        return array_unique($ocupados);
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
