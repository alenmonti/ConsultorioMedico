<?php

namespace App\Filament\Resources\TurnoResource\Widgets;

use App\Enums\EstadosTurno;
use App\Enums\Roles;
use App\Enums\TipoHorarioEspecial;
use App\Filament\Resources\HistoriaClinicaResource;
use App\Filament\Resources\TurnoResource;
use App\Models\Horario;
use App\Models\HorarioEspecial;
use App\Models\Practica;
use App\Models\Turno;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class Calendario extends FullCalendarWidget
{
    public Model|string|null $model = Turno::class;

    public string $initialView = 'timeGridWeek';

    public function mount(): void
    {
        $ua = strtolower(request()->userAgent() ?? '');
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android')) {
            $this->initialView = 'timeGridDay';
        }
    }

    /**
     * FullCalendar will call this function whenever it needs new event data.
     * This is triggered when the user clicks prev/next or switches views on the calendar.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        $turnos = Turno::query()
            ->where('fecha', '>=', $fetchInfo['start'])
            ->where('fecha', '<=', $fetchInfo['end'])
            ->with(['practica', 'paciente'])
            ->get()
            ->map(fn (Turno $turno) => [
                'id' => $turno->id,
                'title' => ($turno->paciente
                    ? $turno->paciente->nombre.' '.$turno->paciente->apellido.' ('.(config('paciente.obras_sociales')[$turno->paciente->obra_social] ?? $turno->paciente->obra_social).')'
                    : 'Sin Paciente')
                    .($turno->practica ? ' | '.$turno->practica->nombre.($turno->practica->costo !== null ? ' - $'.number_format($turno->practica->costo, 2, ',', '.') : '') : '')
                    .($turno->notas ? ' | '.$turno->notas : ''),
                'start' => Carbon::parse($turno->fecha.' '.$turno->hora),
                'end' => Carbon::parse($turno->fecha.' '.$turno->hora)->addMinutes($turno->practica?->duracion_min ?? 20),
                'backgroundColor' => $turno->estado->getHexColor(),
                'shouldOpenInNewTab' => true,
                'extendedProps' => [
                    'notas' => $turno->notas,
                ],
                'display' => 'block',
            ])
            ->all();

        $slotEvents = $this->getAvailableSlotEvents($fetchInfo['start'], $fetchInfo['end']);

        $diasNoDisponibles = user()->diasNoDisponibles($fetchInfo['start'], $fetchInfo['end']);
        $disableDays = [];
        foreach ($diasNoDisponibles as $dia) {
            $disableDays[] = ['start' => $dia, 'end' => $dia, 'display' => 'background', 'backgroundColor' => '#ff5858', 'allDay' => true, 'disableClick' => true];
        }

        return array_merge($turnos, $slotEvents, $disableDays);
    }

    private function getAvailableSlotEvents(string $desde, string $hasta): array
    {
        $medico = user();
        $fechaDesde = Carbon::parse($desde);
        $fechaHasta = Carbon::parse($hasta);

        $mesesAbiertos = app(ScheduleService::class)->mesesAbiertos($medico, $desde, $hasta);

        $horariosPorDia = Horario::where('medico_id', $medico->medico_id)
            ->where('activo_sistema', true)
            ->get()
            ->groupBy(fn ($h) => "{$h->anio}-{$h->mes}-".($h->dia instanceof \BackedEnum ? $h->dia->value : $h->dia));

        $turnosPorFecha = Turno::where('medico_id', $medico->medico_id)
            ->whereBetween('fecha', [$fechaDesde->format('Y-m-d'), $fechaHasta->format('Y-m-d')])
            ->with('practica')
            ->get()
            ->groupBy('fecha');

        $especialesPorFecha = HorarioEspecial::where('medico_id', $medico->medico_id)
            ->whereDate('fecha', '>=', $fechaDesde->format('Y-m-d'))
            ->whereDate('fecha', '<=', $fechaHasta->format('Y-m-d'))
            ->get()
            ->groupBy(fn ($e) => $e->fecha->format('Y-m-d'));

        $dayMap = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $slotEvents = [];
        $cursor = $fechaDesde->copy();

        while ($cursor <= $fechaHasta) {
            $fechaStr = $cursor->format('Y-m-d');
            $diaSemana = $dayMap[$cursor->dayOfWeek];

            if (! in_array("{$cursor->year}-{$cursor->month}", $mesesAbiertos, true)) {
                $cursor->addDay();

                continue;
            }

            $configHorarios = $horariosPorDia->get("{$cursor->year}-{$cursor->month}-{$diaSemana}", collect());
            $especiales = $especialesPorFecha->get($fechaStr, collect());
            $diaExcluido = $especiales->contains(fn ($e) => $e->tipo === TipoHorarioEspecial::Exclusion && $e->todo_el_dia);
            $adiciones = $especiales->filter(fn ($e) => $e->tipo === TipoHorarioEspecial::Adicion);

            if (($configHorarios->isNotEmpty() || $adiciones->isNotEmpty()) && ! $diaExcluido) {
                $intervalo = $configHorarios->isNotEmpty()
                    ? (int) Carbon::parse($configHorarios->first()->intervalo)->format('i')
                    : 20;

                $turnosDelDia = $turnosPorFecha->get($fechaStr, collect());
                $horasOcupadas = [];
                foreach ($turnosDelDia as $turno) {
                    $duracionTurno = $turno->practica?->duracion_min ?? $intervalo;
                    $bloques = max(1, (int) ceil($duracionTurno / $intervalo));
                    $inicio = Carbon::parse($turno->hora);
                    for ($i = 0; $i < $bloques; $i++) {
                        $horasOcupadas[] = $inicio->copy()->addMinutes($i * $intervalo)->format('H:i');
                    }
                }
                $horasOcupadas = array_unique($horasOcupadas);

                $exclusionesParciales = $especiales->filter(fn ($e) => $e->tipo === TipoHorarioEspecial::Exclusion && ! $e->todo_el_dia);

                $rangos = $configHorarios
                    ->map(fn ($h) => [
                        'desde' => Carbon::parse($h->desde),
                        'hasta' => Carbon::parse($h->hasta),
                        'intervalo' => (int) Carbon::parse($h->intervalo)->format('i'),
                    ])
                    ->concat($adiciones->map(fn ($e) => [
                        'desde' => Carbon::parse($e->desde),
                        'hasta' => Carbon::parse($e->hasta),
                        'intervalo' => $intervalo,
                    ]));

                $emitidos = [];
                foreach ($rangos as $rango) {
                    $time = $rango['desde']->copy();
                    $fin = $rango['hasta'];
                    $iv = $rango['intervalo'];

                    while ($time->copy()->addMinutes($iv) <= $fin) {
                        $horaStr = $time->format('H:i');

                        if (! isset($emitidos[$horaStr])) {
                            $excluido = $exclusionesParciales->contains(
                                fn ($e) => $time->between(Carbon::parse($e->desde), Carbon::parse($e->hasta), true)
                            );

                            if (! in_array($horaStr, $horasOcupadas) && ! $excluido) {
                                $start = Carbon::parse($fechaStr.' '.$horaStr);
                                $slotEvents[] = [
                                    'id' => 'slot_'.$fechaStr.'_'.$horaStr,
                                    'title' => 'DISPONIBLE',
                                    'start' => $start->toIso8601String(),
                                    'end' => $start->copy()->addMinutes($iv)->toIso8601String(),
                                    'backgroundColor' => '#7dd3fc',
                                    'borderColor' => '#0284c7',
                                    'textColor' => '#0c4a6e',
                                    'display' => 'block',
                                    'extendedProps' => [
                                        'isAvailable' => true,
                                        'fecha' => $fechaStr,
                                        'hora' => $horaStr,
                                    ],
                                ];
                            }
                            $emitidos[$horaStr] = true;
                        }
                        $time->addMinutes($iv);
                    }
                }
            }

            $cursor->addDay();
        }

        return $slotEvents;
    }

    public function onEventClick(array $event): void
    {
        if (! empty($event['extendedProps']['isAvailable'])) {
            $this->mountAction('create', [
                'type' => 'select',
                'start' => $event['extendedProps']['fecha'],
                'hora' => $event['extendedProps']['hora'],
            ]);

            return;
        }
        parent::onEventClick($event);
    }

    private function getSlotRange(): array
    {
        $horarios = Horario::where('medico_id', user()->medico_id)
            ->where('anio', now()->year)
            ->where('mes', now()->month)
            ->where('activo_sistema', true)
            ->get();

        if ($horarios->isEmpty()) {
            return ['slotMinTime' => '06:00:00', 'slotMaxTime' => '20:00:00'];
        }

        $min = $horarios->min('desde');
        $max = $horarios->max('hasta');

        return [
            'slotMinTime' => Carbon::parse($min)->format('H:i:s'),
            'slotMaxTime' => Carbon::parse($max)->format('H:i:s'),
        ];
    }

    private function getHiddenDays(): array
    {
        // Siempre se muestra de lunes a sábado; un horario especial puede caer
        // en sábado aunque el horario regular no lo incluya.
        return [0];
    }

    public function config(): array
    {
        return [
            'initialView' => $this->initialView,
            'hiddenDays' => $this->getHiddenDays(),
            ...$this->getSlotRange(),
            'height' => '800px',
            'dayMaxEventRows' => true,
            'eventTimeFormat' => [
                'hour' => 'numeric',
                'minute' => '2-digit',
                'hour12' => false,
            ],
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
        ];
    }

    public function getFormSchema(): array
    {
        return TurnoResource::getFormSchema();
    }

    protected function modalActions(): array
    {
        return [
            Action::make('Atender')
                ->label('Atender')
                ->action(function (Turno $turno) {
                    $turno->update(['estado' => EstadosTurno::Atendido]);

                    return redirect(HistoriaClinicaResource::getUrl('viewFile', ['paciente_id' => $turno->paciente_id]));
                })
                ->icon('heroicon-o-clipboard-document-list')
                ->hidden(function (Turno $turno) {
                    if (user()->rol == Roles::Secretario || ! $turno->paciente_id) {
                        return true;
                    }

                    return ! in_array($turno->estado, [EstadosTurno::Pendiente, EstadosTurno::Confirmado]);
                }),
            EditAction::make()
                ->extraAttributes(['class' => 'attend-button'])
                ->color('info'),
            DeleteAction::make(),
        ];
    }

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->mountUsing(
                    function (Form $form, array $arguments) {
                        $form->fill([
                            'fecha' => $arguments['start'] ?? null,
                            'hora' => $arguments['hora'] ?? null,
                            'estado' => EstadosTurno::Pendiente,
                            'tipo' => 'turno',
                            'medico_id' => Auth::user()->medico_id,
                            'practica_id' => Practica::whereRaw('lower(nombre) = ?', ['consulta'])->value('id'),
                        ]);
                    }
                ),
        ];
    }

    // public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    // {
    //     if(user()->horariosDisponibles($start)){
    //         parent::onDateSelect($start, $end, $allDay, $view, $resource);
    //     }
    // }
}
