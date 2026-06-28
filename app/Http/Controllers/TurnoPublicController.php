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
                'mensaje' => 'Este link de confirmación no es válido.',
            ]);
        }

        if ($turno->estado !== EstadosTurno::Cancelado) {
            $turno->update(['estado' => EstadosTurno::Confirmado]);
        }

        return view('turno.respuesta', [
            'exito'   => $turno->estado !== EstadosTurno::Cancelado,
            'titulo'  => $turno->estado === EstadosTurno::Cancelado ? 'Turno cancelado' : 'Turno confirmado',
            'mensaje' => $turno->estado === EstadosTurno::Cancelado
                ? 'Este turno ya fue cancelado y no puede confirmarse.'
                : 'Su turno ha sido confirmado exitosamente. ¡Muchas gracias!',
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
                'mensaje' => 'Este link de cancelación no es válido.',
            ]);
        }

        $turno->update(['estado' => EstadosTurno::Cancelado]);

        return view('turno.respuesta', [
            'exito'   => true,
            'titulo'  => 'Turno cancelado',
            'mensaje' => 'Su turno ha sido cancelado. Si desea reagendarlo, comuníquese con el consultorio.',
            'turno'   => $turno,
        ]);
    }
}
