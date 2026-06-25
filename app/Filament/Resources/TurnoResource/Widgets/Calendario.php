<?php

namespace App\Filament\Resources\TurnoResource\Widgets;

use App\Enums\EstadosTurno;
use App\Enums\Roles;
use App\Filament\Resources\HistoriaClinicaResource;
use App\Filament\Resources\TurnoResource;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions\{EditAction, DeleteAction, CreateAction};

class Calendario extends FullCalendarWidget
{
    public Model | string | null $model = Turno::class;

    /**
     * FullCalendar will call this function whenever it needs new event data.
     * This is triggered when the user clicks prev/next or switches views on the calendar.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        $turnos = Turno::query()
            ->where('fecha', '>=', $fetchInfo['start'])
            ->where('fecha', '<=', $fetchInfo['end'])
            ->get()
            ->map(fn (Turno $turno) => [
                    'id' => $turno->id,
                    'title' => $turno->paciente->nombre . ' ' . $turno->paciente->apellido,
                    'start' => Carbon::parse($turno->fecha . ' ' . $turno->hora),
                    'end' => Carbon::parse($turno->fecha . ' ' . $turno->hora)->addMinutes(20),
                    'backgroundColor' => $turno->estado->getHexColor(),
                    'shouldOpenInNewTab' => true,
                    'extendedProps' => [
                        'notas' => $turno->notas
                    ],
                    'display' => 'block',
                ])
            ->all();
        $diasNoDisponibles = user()->diasNoDisponibles($fetchInfo['start'], $fetchInfo['end']);
        $disableDays = [];
        foreach ($diasNoDisponibles as $dia) {
        $disableDays[] = ['start' => $dia, 'end' => $dia, 'display' => 'background', 'backgroundColor' => '#ff5858', 'allDay' => true, 'disableClick' => true];
        }
        
        // $daysOfWeek = user()->diasDeSemanaDisponibles();
        // $disableDays = [['daysOfWeek' => $daysOfWeek, 'display' => 'inverse-background', 'backgroundColor' => '#ff5858']];
        return array_merge($turnos, $disableDays);
    }

    public function config(): array
    {
        return [
            'height' => '800px',
            'dayMaxEventRows' => true,
            'eventTimeFormat' => [
                'hour' => 'numeric',
                'minute' => '2-digit',
                'hour12' => false,
            ],
            'views' => [
                'dayGrid' => [
                ],
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
                ->hidden(function(Turno $turno) {
                    if (user()->rol == Roles::Secretario) {
                        return true;
                    } else {
                        return !in_array($turno->estado, [EstadosTurno::Pendiente, EstadosTurno::Confirmado]);
                    }
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
                            'estado' => EstadosTurno::Pendiente,
                            'tipo' => 'turno',
                            'medico_id' => Auth::user()->medico_id,
                        ]);
                    }
                )
        ];
    }

    // public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    // {
    //     if(user()->horariosDisponibles($start)){
    //         parent::onDateSelect($start, $end, $allDay, $view, $resource);
    //     }
    // }
}
