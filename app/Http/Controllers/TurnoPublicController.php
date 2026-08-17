<?php

namespace App\Http\Controllers;

use App\Enums\EstadosTurno;
use App\Models\Turno;
use App\Notifications\TurnoCanceladoNotification;
use App\Notifications\TurnoConfirmadoNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TurnoPublicController extends Controller
{
    public function confirmar(Request $request, int $turnoId)
    {
        $turno = Turno::withoutGlobalScopes()->with('medico')->find($turnoId);

        if (! $this->tokenValido($turno, $request)) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Link inválido',
                'mensaje' => 'Este link de confirmación no es válido.',
            ]);
        }

        if ($turno->estado === EstadosTurno::Cancelado) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Turno cancelado',
                'mensaje' => 'Este turno ya fue cancelado y no puede confirmarse.',
                'turno'   => $turno,
            ]);
        }

        if ($turno->estado === EstadosTurno::Confirmado) {
            return view('turno.respuesta', [
                'exito'   => true,
                'titulo'  => 'Turno confirmado',
                'mensaje' => 'Su turno ya se encontraba confirmado. ¡Muchas gracias!',
                'turno'   => $turno,
            ]);
        }

        if ($this->turnoVencido($turno)) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Turno vencido',
                'mensaje' => 'La fecha de este turno ya pasó y no puede confirmarse.',
                'turno'   => $turno,
            ]);
        }

        return view('turno.confirmar', [
            'turno' => $turno,
            'token' => $request->query('token'),
        ]);
    }

    public function confirmarStore(Request $request, int $turnoId)
    {
        $turno = Turno::withoutGlobalScopes()->with('medico')->find($turnoId);

        if (! $this->tokenValido($turno, $request)) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Link inválido',
                'mensaje' => 'Este link de confirmación no es válido.',
            ]);
        }

        if ($turno->estado === EstadosTurno::Cancelado) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Turno cancelado',
                'mensaje' => 'Este turno ya fue cancelado y no puede confirmarse.',
                'turno'   => $turno,
            ]);
        }

        if ($turno->estado !== EstadosTurno::Confirmado && $this->turnoVencido($turno)) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Turno vencido',
                'mensaje' => 'La fecha de este turno ya pasó y no puede confirmarse.',
                'turno'   => $turno,
            ]);
        }

        $turno->update(['estado' => EstadosTurno::Confirmado]);

        if ($turno->medico) {
            $turno->medico->notify(new TurnoConfirmadoNotification($turno));
        }

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

        if (! $this->tokenValido($turno, $request)) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Link inválido',
                'mensaje' => 'Este link de cancelación no es válido.',
            ]);
        }

        if ($turno->estado === EstadosTurno::Cancelado) {
            return view('turno.respuesta', [
                'exito'   => true,
                'titulo'  => 'Turno cancelado',
                'mensaje' => 'Este turno ya se encontraba cancelado.',
                'turno'   => $turno,
            ]);
        }

        if ($this->turnoVencido($turno)) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Turno vencido',
                'mensaje' => 'La fecha de este turno ya pasó y no puede cancelarse.',
                'turno'   => $turno,
            ]);
        }

        return view('turno.cancelar', [
            'turno' => $turno,
            'token' => $request->query('token'),
        ]);
    }

    public function cancelarStore(Request $request, int $turnoId)
    {
        $turno = Turno::withoutGlobalScopes()->with('medico')->find($turnoId);

        if (! $this->tokenValido($turno, $request)) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Link inválido',
                'mensaje' => 'Este link de cancelación no es válido.',
            ]);
        }

        if ($turno->estado !== EstadosTurno::Cancelado && $this->turnoVencido($turno)) {
            return view('turno.respuesta', [
                'exito'   => false,
                'titulo'  => 'Turno vencido',
                'mensaje' => 'La fecha de este turno ya pasó y no puede cancelarse.',
                'turno'   => $turno,
            ]);
        }

        $turno->update(['estado' => EstadosTurno::Cancelado]);

        if ($turno->medico) {
            $turno->medico->notify(new TurnoCanceladoNotification($turno));
        }

        return view('turno.respuesta', [
            'exito'   => true,
            'titulo'  => 'Turno cancelado',
            'mensaje' => 'Su turno ha sido cancelado. Si desea reagendarlo, comuníquese con el consultorio.',
            'turno'   => $turno,
        ]);
    }

    private function tokenValido(?Turno $turno, Request $request): bool
    {
        return $turno && $turno->turno_token && $turno->turno_token === $request->input('token');
    }

    private function turnoVencido(Turno $turno): bool
    {
        return Carbon::parse("{$turno->fecha} {$turno->hora}")->isPast();
    }
}
