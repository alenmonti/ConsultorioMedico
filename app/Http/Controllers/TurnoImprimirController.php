<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TurnoImprimirController extends Controller
{
    public function __invoke(Request $request)
    {
        $fecha = Carbon::parse($request->query('fecha', today()->format('Y-m-d')));
        $rango = $request->query('rango', 'dia') === 'semana' ? 'semana' : 'dia';

        if ($rango === 'semana') {
            $desde = $fecha->copy()->startOfWeek(Carbon::MONDAY);
            $hasta = $desde->copy()->addDays(5);
        } else {
            $desde = $fecha->copy();
            $hasta = $fecha->copy();
        }

        $turnos = Turno::query()
            ->with(['paciente', 'practica'])
            ->whereBetween('fecha', [$desde->format('Y-m-d'), $hasta->format('Y-m-d')])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->groupBy(fn (Turno $turno) => Carbon::parse($turno->fecha)->format('Y-m-d'));

        return view('turno.imprimir', [
            'desde' => $desde,
            'hasta' => $hasta,
            'rango' => $rango,
            'turnos' => $turnos,
            'medico' => user(),
        ]);
    }
}
