<?php

namespace App\Http\Controllers\Portal;

use App\Enums\EstadosTurno;
use App\Enums\TipoTurno;
use App\Http\Controllers\Controller;
use App\Models\Practica;
use App\Models\Turno;
use App\Models\User;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PortalTurnosController extends Controller
{
    public function index()
    {
        return view('portal.turnos');
    }

    public function medicos()
    {
        $medicos = User::where('portal_turnos_activo', true)
            ->select('id', 'name', 'surname', 'especialidad', 'descripcion', 'foto_portal', 'whatsapp')
            ->get()
            ->map(fn ($u) => [
                'id'          => $u->id,
                'nombre'      => $u->name . ' ' . $u->surname,
                'especialidad'=> $u->especialidad,
                'descripcion' => $u->descripcion,
                'foto'        => $u->foto_portal ? asset('storage/' . $u->foto_portal) : null,
                'whatsapp'    => $u->whatsapp,
            ]);

        return response()->json($medicos);
    }

    public function semana(Request $request, ScheduleService $schedule)
    {
        $request->validate([
            'medico_id' => 'required|integer|exists:users,id',
            'desde'     => 'required|date',
        ]);

        $medico = User::findOrFail($request->medico_id);
        $desde  = Carbon::parse($request->desde)->startOfWeek(Carbon::MONDAY);
        $hasta  = $desde->copy()->endOfWeek(Carbon::SUNDAY);

        $diasNoDisponibles = $schedule->diasNoDisponibles($medico, $desde->format('Y-m-d'), $hasta->format('Y-m-d'));

        $dias = [];
        $cursor = $desde->copy();
        $hoy = Carbon::today();

        while ($cursor <= $hasta) {
            $fechaStr = $cursor->format('Y-m-d');
            $pasado = $cursor->lt($hoy);

            if ($pasado) {
                $estado = 'pasado';
                $slots = 0;
            } elseif (in_array($fechaStr, $diasNoDisponibles)) {
                $horarioDelDia = $medico->horarios()
                    ->where('dia', $this->diaSemana($cursor->dayOfWeek))
                    ->exists();
                $estado = $horarioDelDia ? 'lleno' : 'cerrado';
                $slots = 0;
            } else {
                $disponibles = $schedule->horariosDisponibles($medico, $fechaStr);
                $slots = count($disponibles);
                $estado = $slots <= 3 ? 'pocos' : 'libre';
            }

            $dias[] = [
                'fecha'  => $fechaStr,
                'nombre' => $this->nombreDia($cursor->dayOfWeek),
                'numero' => $cursor->day,
                'estado' => $estado,
                'slots'  => $slots,
            ];

            $cursor->addDay();
        }

        return response()->json([
            'semana_label' => $desde->isoFormat('D MMM') . '–' . $hasta->isoFormat('D MMM'),
            'desde'        => $desde->format('Y-m-d'),
            'hasta'        => $hasta->format('Y-m-d'),
            'dias'         => $dias,
        ]);
    }

    public function horarios(Request $request, ScheduleService $schedule)
    {
        $request->validate([
            'medico_id' => 'required|integer|exists:users,id',
            'fecha'     => 'required|date',
        ]);

        $medico = User::findOrFail($request->medico_id);
        $slots  = $schedule->horariosDisponibles($medico, $request->fecha);

        $manana = [];
        $tarde  = [];

        foreach (array_keys($slots) as $hora) {
            $h = (int) explode(':', $hora)[0];
            if ($h < 13) {
                $manana[] = $hora;
            } else {
                $tarde[] = $hora;
            }
        }

        $label = Carbon::parse($request->fecha)->isoFormat('dddd D [de] MMMM');

        return response()->json([
            'fecha_label' => mb_strtoupper($label),
            'manana'      => $manana,
            'tarde'       => $tarde,
        ]);
    }

    public function reservar(Request $request)
    {
        $data = $request->validate([
            'medico_id' => 'required|integer|exists:users,id',
            'fecha'     => 'required|date|after_or_equal:today',
            'hora'      => 'required|date_format:H:i',
            'nombre'    => 'required|string|max:120',
            'whatsapp'  => 'required|string|max:30',
        ]);

        $medico = User::findOrFail($data['medico_id']);

        // Verify slot is still available
        $disponibles = app(ScheduleService::class)->horariosDisponibles($medico, $data['fecha']);
        if (!isset($disponibles[$data['hora']])) {
            return response()->json(['message' => 'El horario ya no está disponible.'], 422);
        }

        $notas = "Reserva web — Nombre: {$data['nombre']} | WhatsApp: {$data['whatsapp']}";

        $consulta = Practica::withoutGlobalScopes()
            ->where('medico_id', $medico->id)
            ->whereRaw('LOWER(nombre) = ?', ['consulta'])
            ->first();

        Turno::withoutGlobalScopes()->create([
            'medico_id'   => $medico->id,
            'paciente_id' => null,
            'practica_id' => $consulta?->id,
            'fecha'       => $data['fecha'],
            'hora'        => $data['hora'],
            'estado'      => EstadosTurno::Pendiente,
            'tipo'        => TipoTurno::Turno,
            'notas'       => $notas,
            'origen'      => 'web',
        ]);

        return response()->json(['ok' => true]);
    }

    private function diaSemana(int $dayOfWeek): string
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

    private function nombreDia(int $dayOfWeek): string
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
}
