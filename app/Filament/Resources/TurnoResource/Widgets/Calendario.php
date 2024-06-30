<?php

namespace App\Filament\Resources\TurnoResource\Widgets;

use App\Enums\EstadosTurno;
use App\Filament\Resources\TurnoResource;
use App\Models\Turno;
use Carbon\Carbon;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class Calendario extends FullCalendarWidget
{
    /**
     * FullCalendar will call this function whenever it needs new event data.
     * This is triggered when the user clicks prev/next or switches views on the calendar.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        return Turno::query()
            ->where('fecha', '>=', $fetchInfo['start'])
            ->where('fecha', '<=', $fetchInfo['end'])
            ->get()
            ->map(fn (Turno $turno) => [
                    'id' => $turno->id,
                    'title' => $turno->paciente->nombre . ' ' . $turno->paciente->apellido,
                    'start' => Carbon::parse($turno->fecha . ' ' . $turno->hora),
                    'end' => Carbon::parse($turno->fecha . ' ' . $turno->hora)->addMinutes(30),
                    'url' => TurnoResource::getUrl(name: 'edit', parameters: ['record' => $turno->id]),
                    'backgroundColor' => $turno->estado->getHexColor(),
                    'shouldOpenInNewTab' => true,
                    'extendedProps' => [
                        'notas' => $turno->notas
                    ],

                ])
            ->all();
    }
}
