<?php

namespace App\Services;

use App\Enums\EstadosTurno;
use App\Enums\TipoHorarioEspecial;
use App\Models\AperturaMensual;
use App\Models\Horario;
use App\Models\HorarioEspecial;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;

class ScheduleService
{
    public function horariosDisponibles(User $medico, string $fecha, string $tipo = 'turno', int $duracion = 20, bool $ignorarCancelados = false, bool $portal = false, ?int $turnoIdExcluir = null): array
    {
        $fechaCarbon = Carbon::parse($fecha);

        if (empty($this->mesesAbiertos($medico, $fecha, $fecha))) {
            return [];
        }

        $diaSemana = $this->dayOfWeekToString($fechaCarbon->dayOfWeek);

        $configHorarios = Horario::where('medico_id', $medico->medico_id)
            ->where('anio', $fechaCarbon->year)
            ->where('mes', $fechaCarbon->month)
            ->where('dia', $diaSemana)
            ->where('activo_sistema', true)
            ->when($portal, fn ($q) => $q->where('activo_portal', true))
            ->get();

        $especiales = HorarioEspecial::where('medico_id', $medico->medico_id)
            ->whereDate('fecha', $fecha)
            ->get();

        if ($configHorarios->isEmpty() && $especiales->isEmpty()) {
            return [];
        }

        if ($especiales->contains(fn ($e) => $e->tipo === TipoHorarioEspecial::Exclusion && $e->todo_el_dia)) {
            return [];
        }

        $intervalo = 20;
        $slots = [];
        foreach ($configHorarios as $horario) {
            $desde = Carbon::parse($horario->desde);
            $hasta = Carbon::parse($horario->hasta);
            $intervalo = (int) Carbon::parse($horario->intervalo)->format('i');
            while ($desde->copy()->addMinutes($intervalo) <= $hasta) {
                $slots[] = $desde->format('H:i');
                $desde->addMinutes($intervalo);
            }
        }

        foreach ($especiales as $especial) {
            if ($especial->tipo !== TipoHorarioEspecial::Adicion) {
                continue;
            }
            if ($portal ? ! $especial->activo_portal : ! $especial->activo_sistema) {
                continue;
            }
            $desde = Carbon::parse($especial->desde);
            $hasta = Carbon::parse($especial->hasta);
            while ($desde->copy()->addMinutes($intervalo) <= $hasta) {
                $slots[] = $desde->format('H:i');
                $desde->addMinutes($intervalo);
            }
        }
        $slots = array_values(array_unique($slots));
        sort($slots);

        foreach ($especiales as $especial) {
            if ($especial->tipo !== TipoHorarioEspecial::Exclusion || $especial->todo_el_dia) {
                continue;
            }
            $exDesde = Carbon::parse($especial->desde);
            $exHasta = Carbon::parse($especial->hasta);
            $slots = array_values(array_filter(
                $slots,
                fn ($s) => ! (Carbon::parse($s)->between($exDesde, $exHasta, true))
            ));
        }

        $turnosDelDia = Turno::where('medico_id', $medico->medico_id)
            ->where('fecha', $fecha)
            ->when($ignorarCancelados, fn ($q) => $q->where('estado', '!=', EstadosTurno::Cancelado))
            ->when($turnoIdExcluir, fn ($q) => $q->where('id', '!=', $turnoIdExcluir))
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

        [$mesesAbiertos, $horariosPorDia, $turnosPorFecha, $especialesPorFecha] =
            $this->cargarDatosRango($medico, $fechaDesde, $fechaHasta, $ignorarCancelados, $portal);

        $diasNoDisponibles = [];
        $cursor = $fechaDesde->copy();

        while ($cursor <= $fechaHasta) {
            $fechaStr = $cursor->format('Y-m-d');

            $dia = $this->calcularSlotsDia($cursor, $mesesAbiertos, $horariosPorDia, $especialesPorFecha, $turnosPorFecha, $portal);

            if (empty($dia['slots']) || empty(array_diff($dia['slots'], $dia['ocupados']))) {
                $diasNoDisponibles[] = $fechaStr;
            }

            $cursor->addDay();
        }

        return $diasNoDisponibles;
    }

    /**
     * Disponibilidad completa de un médico para un rango de fechas, pensada para
     * traer de una sola vez todo lo que necesita el portal de pacientes (evita
     * repetir requests semana a semana / día a día).
     */
    public function disponibilidadCompleta(User $medico, string $desde, string $hasta, bool $ignorarCancelados = true, bool $portal = true): array
    {
        $fechaDesde = Carbon::parse($desde);
        $fechaHasta = Carbon::parse($hasta);
        $hoy = Carbon::today();

        [$mesesAbiertos, $horariosPorDia, $turnosPorFecha, $especialesPorFecha] =
            $this->cargarDatosRango($medico, $fechaDesde, $fechaHasta, $ignorarCancelados, $portal);

        $dias = [];
        $cursor = $fechaDesde->copy();

        while ($cursor <= $fechaHasta) {
            $fechaStr = $cursor->format('Y-m-d');
            $pasado = $cursor->lt($hoy);

            if ($pasado) {
                $estado = 'pasado';
                $libres = [];
            } else {
                $dia = $this->calcularSlotsDia($cursor, $mesesAbiertos, $horariosPorDia, $especialesPorFecha, $turnosPorFecha, $portal);
                $libres = array_values(array_diff($dia['slots'], $dia['ocupados']));
                sort($libres);

                if (empty($dia['slots'])) {
                    $mesAbierto = in_array("{$cursor->year}-{$cursor->month}", $mesesAbiertos, true);
                    $estado = $mesAbierto && $dia['tieneConfig'] ? 'lleno' : 'cerrado';
                } elseif (empty($libres)) {
                    $estado = 'lleno';
                } else {
                    $estado = count($libres) <= 3 ? 'pocos' : 'libre';
                }
            }

            $manana = [];
            $tarde = [];
            foreach ($libres as $hora) {
                $h = (int) explode(':', $hora)[0];
                if ($h < 13) {
                    $manana[] = $hora;
                } else {
                    $tarde[] = $hora;
                }
            }

            $dias[$fechaStr] = [
                'nombre' => $this->nombreDiaCorto($cursor->dayOfWeek),
                'numero' => $cursor->day,
                'estado' => $estado,
                'slots' => count($libres),
                'manana' => $manana,
                'tarde' => $tarde,
            ];

            $cursor->addDay();
        }

        return [
            'desde' => $fechaDesde->format('Y-m-d'),
            'hasta' => $fechaHasta->format('Y-m-d'),
            'dias' => $dias,
        ];
    }

    /**
     * Carga en bulk (una consulta por tipo, no por día) todos los datos necesarios
     * para calcular disponibilidad en un rango: meses abiertos, horarios configurados,
     * turnos existentes y horarios especiales.
     */
    private function cargarDatosRango(User $medico, Carbon $fechaDesde, Carbon $fechaHasta, bool $ignorarCancelados, bool $portal): array
    {
        $mesesAbiertos = $this->mesesAbiertos($medico, $fechaDesde->format('Y-m-d'), $fechaHasta->format('Y-m-d'));

        $horariosPorDia = Horario::where('medico_id', $medico->medico_id)
            ->where('activo_sistema', true)
            ->when($portal, fn ($q) => $q->where('activo_portal', true))
            ->get()
            ->groupBy(fn ($h) => "{$h->anio}-{$h->mes}-".($h->dia instanceof \BackedEnum ? $h->dia->value : $h->dia));

        $turnosPorFecha = Turno::where('medico_id', $medico->medico_id)
            ->whereBetween('fecha', [$fechaDesde->format('Y-m-d'), $fechaHasta->format('Y-m-d')])
            ->when($ignorarCancelados, fn ($q) => $q->where('estado', '!=', EstadosTurno::Cancelado))
            ->with('practica')
            ->get()
            ->groupBy('fecha');

        $especialesPorFecha = HorarioEspecial::where('medico_id', $medico->medico_id)
            ->whereDate('fecha', '>=', $fechaDesde->format('Y-m-d'))
            ->whereDate('fecha', '<=', $fechaHasta->format('Y-m-d'))
            ->get()
            ->groupBy(fn ($e) => $e->fecha->format('Y-m-d'));

        return [$mesesAbiertos, $horariosPorDia, $turnosPorFecha, $especialesPorFecha];
    }

    /**
     * Calcula, para un día puntual, los slots configurados (ya con adiciones/exclusiones
     * especiales aplicadas) y los slots ocupados por turnos existentes, a partir de las
     * colecciones ya agrupadas por `cargarDatosRango`.
     */
    private function calcularSlotsDia(Carbon $cursor, array $mesesAbiertos, $horariosPorDia, $especialesPorFecha, $turnosPorFecha, bool $portal): array
    {
        $fechaStr = $cursor->format('Y-m-d');
        $diaSemana = $this->dayOfWeekToString($cursor->dayOfWeek);

        if (! in_array("{$cursor->year}-{$cursor->month}", $mesesAbiertos, true)) {
            return ['slots' => [], 'ocupados' => [], 'tieneConfig' => false];
        }

        $configHorarios = $horariosPorDia->get("{$cursor->year}-{$cursor->month}-{$diaSemana}", collect());
        $especiales = $especialesPorFecha->get($fechaStr, collect());
        $tieneConfig = $configHorarios->isNotEmpty();

        if ($configHorarios->isEmpty() && $especiales->isEmpty()) {
            return ['slots' => [], 'ocupados' => [], 'tieneConfig' => $tieneConfig];
        }

        if ($especiales->contains(fn ($e) => $e->tipo === TipoHorarioEspecial::Exclusion && $e->todo_el_dia)) {
            return ['slots' => [], 'ocupados' => [], 'tieneConfig' => $tieneConfig];
        }

        $intervalo = $configHorarios->isNotEmpty()
            ? (int) Carbon::parse($configHorarios->first()->intervalo)->format('i')
            : 20;

        $slots = [];
        foreach ($configHorarios as $horario) {
            $time = Carbon::parse($horario->desde);
            $fin = Carbon::parse($horario->hasta);
            $iv = (int) Carbon::parse($horario->intervalo)->format('i');
            while ($time->copy()->addMinutes($iv) <= $fin) {
                $slots[] = $time->format('H:i');
                $time->addMinutes($iv);
            }
        }

        foreach ($especiales as $especial) {
            if ($especial->tipo !== TipoHorarioEspecial::Adicion) {
                continue;
            }
            if ($portal ? ! $especial->activo_portal : ! $especial->activo_sistema) {
                continue;
            }
            $time = Carbon::parse($especial->desde);
            $fin = Carbon::parse($especial->hasta);
            while ($time->copy()->addMinutes($intervalo) <= $fin) {
                $slots[] = $time->format('H:i');
                $time->addMinutes($intervalo);
            }
        }
        $slots = array_values(array_unique($slots));

        foreach ($especiales as $especial) {
            if ($especial->tipo !== TipoHorarioEspecial::Exclusion || $especial->todo_el_dia) {
                continue;
            }
            $exDesde = Carbon::parse($especial->desde);
            $exHasta = Carbon::parse($especial->hasta);
            $slots = array_values(array_filter(
                $slots,
                fn ($s) => ! (Carbon::parse($s)->between($exDesde, $exHasta, true))
            ));
        }

        $ocupados = $this->expandirSlotsOcupados(
            $turnosPorFecha->get($fechaStr, collect()),
            $intervalo
        );

        return ['slots' => $slots, 'ocupados' => $ocupados, 'tieneConfig' => $tieneConfig];
    }

    private function nombreDiaCorto(int $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            0 => 'DOM',
            1 => 'LUN',
            2 => 'MAR',
            3 => 'MIÉ',
            4 => 'JUE',
            5 => 'VIE',
            6 => 'SÁB',
        };
    }

    public function mesesAbiertos(User $medico, string $desde, string $hasta): array
    {
        $fechaDesde = Carbon::parse($desde);
        $fechaHasta = Carbon::parse($hasta);

        $aperturas = AperturaMensual::where('medico_id', $medico->medico_id)
            ->get()
            ->keyBy(fn ($a) => "{$a->anio}-{$a->mes}");

        $hoy = Carbon::now();

        $abiertos = [];
        $cursor = $fechaDesde->copy()->startOfMonth();
        $fin = $fechaHasta->copy()->startOfMonth();

        while ($cursor <= $fin) {
            $clave = "{$cursor->year}-{$cursor->month}";
            $esPasadoOActual = $cursor->year < $hoy->year || ($cursor->year === $hoy->year && $cursor->month <= $hoy->month);
            $apertura = $aperturas->get($clave);

            // Un mes ya iniciado siempre está abierto: el toggle de apertura solo rige meses futuros.
            // Si quedó una fila cerrada de cuando todavía era futuro, se corrige en la DB para que quede claro.
            if ($esPasadoOActual && $apertura && ! $apertura->abierto) {
                $apertura->update(['abierto' => true]);
            }

            $abierto = $esPasadoOActual || ($apertura?->abierto ?? false);

            if ($abierto) {
                $abiertos[] = $clave;
            }

            $cursor->addMonth();
        }

        return $abiertos;
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
