<?php

namespace App\Filament\Resources\TurnoResource\Widgets;

use App\Enums\EstadosTurno;
use App\Forms\Components\TextInfo;
use App\Models\Paciente;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Forms\Components\{DatePicker, Grid, Hidden, Select, Textarea};
use Filament\Forms\Form;
use Filament\Forms\Get;
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
        return [
            Select::make('tipo')
                ->required()
                ->searchable()
                ->label('Tipo de turno')
                ->options([
                    'turno' => 'Turno',
                    'sobre_turno' => 'Sobre Turno',
                ])
                ->default('turno')
                ->live(),
            TextInfo::make('info')
                ->hidden(fn(Get $get) => $get('tipo') == 'turno')
                ->columnSpan(2),
            Grid::make('')
                ->columns(2)
                ->schema([
            DatePicker::make('fecha')
                ->required()
                ->placeholder('Seleccione una fecha')
                ->live()
                ->native(false),
            Select::make('hora')
                ->required()
                ->placeholder('Seleccione un horario')
                ->searchable()
                ->options( function (Get $get) {
                    $fecha = Carbon::parse($get('fecha'))->format('Y-m-d');
                    $tipo = $get('tipo') ?? 'turno';
                    return Auth::user()->horariosDisponibles($fecha, $tipo);
                })
            ]),
            Grid::make('')
                ->columns(2)
                ->schema([
            Select::make('paciente_id')
                ->label('Paciente')
                ->options(Paciente::selectOptions())    
                ->searchable()
                ->required(),
            Select::make('estado')
                ->required()
                ->searchable()
                ->options(EstadosTurno::class)
                ->default(EstadosTurno::Pendiente),
                ]),
            Textarea::make('notas')
                ->label('Notas')
                ->placeholder('Notas adicionales')
                ->rows(3)
                ->columnSpan(2)
                ->autosize(),
            Hidden::make('medico_id')
                ->default(Auth::user()->medico_id),
        ];
    }

    protected function modalActions(): array
    {
        return [
            EditAction::make(),
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
