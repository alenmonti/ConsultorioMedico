<?php

namespace App\Http\Controllers;

use App\Enums\EstadosTurno;
use App\Models\Turno;
use Illuminate\Http\Request;

class TurnoPublicController extends Controller
{
    public function confirmar(Request $request, int $turnoId)
    {
        $turno = Turno::withoutGlobalScopes()->with('medico')->find($turnoId);

        if (! $turno || ! $turno->turno_token || $turno->turno_token !== $request->query('token')) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Link inválido',
                'mensaje' => 'Este link de confirmación no es válido o ya fue utilizado.',
            ]);
        }

        if ($turno->estado === EstadosTurno::Cancelado) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Turno cancelado',
                'mensaje' => 'Este turno ya fue cancelado.',
            ]);
        }

        $turno->update([
            'estado'      => EstadosTurno::Confirmado,
            'turno_token' => null,
        ]);

        return view('turno.respuesta', [
            'exito'   => true,
            'titulo'  => 'Turno confirmado',
            'mensaje' => 'Su turno ha sido confirmado exitosamente. ¡Muchas gracias!',
            'turno'   => $turno,
        ]);
    }

    public function cancelar(Request $request, int $turnoId)
    {
        $turno = Turno::withoutGlobalScopes()->with('medico')->find($turnoId);

        if (! $turno || ! $turno->turno_token || $turno->turno_token !== $request->query('token')) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Link inválido',
                'mensaje' => 'Este link de cancelación no es válido o ya fue utilizado.',
            ]);
        }

        if ($turno->estado === EstadosTurno::Cancelado) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Turno ya cancelado',
                'mensaje' => 'Este turno ya fue cancelado previamente.',
            ]);
        }

        $turno->update([
            'estado'      => EstadosTurno::Cancelado,
            'turno_token' => null,
        ]);

        return view('turno.respuesta', [
            'exito'   => true,
            'titulo'  => 'Turno cancelado',
            'mensaje' => 'Su turno ha sido cancelado. Si desea reagendarlo, comuníquese con el consultorio.',
            'turno'   => $turno,
        ]);
    }
}
