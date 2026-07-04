<?php

namespace App\Filament\Widgets;

use App\Enums\TipoHorarioEspecial;
use App\Models\HorarioEspecial;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;

class HorarioEspecialesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    public ?int $anio = null;
    public ?int $mes = null;

    public function mount(): void
    {
        $this->anio = $this->anio ?? now()->year;
        $this->mes = $this->mes ?? now()->month;
    }

    #[On('mes-horario-cambiado')]
    public function onMesHorarioCambiado(int $anio, int $mes): void
    {
        $this->anio = $anio;
        $this->mes = $mes;
        $this->resetTable();
    }

    private function adicionForm(): array
    {
        return [
            DatePicker::make('fecha')
                ->label('Fecha')
                ->required()
                ->minDate(fn () => \Carbon\Carbon::create($this->anio, $this->mes, 1)->startOfMonth())
                ->maxDate(fn () => \Carbon\Carbon::create($this->anio, $this->mes, 1)->endOfMonth()),
            Grid::make(2)
                ->schema([
                    TimePicker::make('desde')
                        ->label('Desde')
                        ->seconds(false)
                        ->step(60)
                        ->default('09:00')
                        ->required(),
                    TimePicker::make('hasta')
                        ->label('Hasta')
                        ->seconds(false)
                        ->step(60)
                        ->default('18:00')
                        ->required(),
                ]),
            Grid::make(2)
                ->schema([
                    Toggle::make('activo_sistema')
                        ->label('Activo en sistema')
                        ->default(true)
                        ->helperText('Si está desactivado, esta adición no genera turnos en el sistema.'),
                    Toggle::make('activo_portal')
                        ->label('Activo en portal')
                        ->default(false)
                        ->helperText('Si está activado, esta adición es visible para reservas desde el portal web.'),
                ]),
            TextInput::make('motivo')
                ->label('Motivo')
                ->required()
                ->maxLength(255),
        ];
    }

    private function exclusionForm(): array
    {
        return [
            DatePicker::make('fecha')
                ->label('Fecha')
                ->required(),
            Toggle::make('todo_el_dia')
                ->label('Todo el día')
                ->default(true)
                ->live(),
            Grid::make(2)
                ->schema([
                    TimePicker::make('desde')
                        ->label('Desde')
                        ->seconds(false)
                        ->step(60)
                        ->default('09:00')
                        ->visible(fn ($get) => ! $get('todo_el_dia'))
                        ->required(fn ($get) => ! $get('todo_el_dia')),
                    TimePicker::make('hasta')
                        ->label('Hasta')
                        ->seconds(false)
                        ->step(60)
                        ->default('18:00')
                        ->visible(fn ($get) => ! $get('todo_el_dia'))
                        ->required(fn ($get) => ! $get('todo_el_dia')),
                ]),
            TextInput::make('motivo')
                ->label('Motivo')
                ->required()
                ->maxLength(255),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HorarioEspecial::query()
                    ->whereYear('fecha', $this->anio)
                    ->whereMonth('fecha', $this->mes)
            )
            ->defaultSort('fecha', 'asc')
            ->heading('Horarios Especiales')
            ->description('Días o rangos horarios que se excluyen o se agregan al horario general.')
            ->headerActions([
                Tables\Actions\CreateAction::make('agregar_adicion')
                    ->label('Agregar adición')
                    ->modalHeading('Nueva Adición')
                    ->form($this->adicionForm())
                    ->using(function (array $data) {
                        $data['tipo'] = TipoHorarioEspecial::Adicion;
                        $data['todo_el_dia'] = false;

                        return HorarioEspecial::create($data);
                    }),
                Tables\Actions\CreateAction::make('agregar_exclusion')
                    ->label('Agregar exclusión')
                    ->modalHeading('Nueva Exclusión')
                    ->form($this->exclusionForm())
                    ->using(function (array $data) {
                        $data['tipo'] = TipoHorarioEspecial::Exclusion;

                        return HorarioEspecial::create($this->prepararDatosExclusion($data));
                    }),
                Action::make('cargar_feriados')
                    ->label(fn () => 'Cargar feriados ' . $this->anio)
                    ->color('gray')
                    ->icon('heroicon-o-calendar-days')
                    ->requiresConfirmation()
                    ->modalHeading(fn () => 'Cargar feriados ' . $this->anio)
                    ->modalDescription(function () {
                        $year = $this->anio;
                        $response = Http::timeout(10)->get("https://api.argentinadatos.com/v1/feriados/{$year}");
                        if (! $response->ok()) {
                            return 'No se pudo obtener la lista de feriados. Intente nuevamente.';
                        }
                        $feriados = collect($response->json());
                        $existentes = HorarioEspecial::whereYear('fecha', $year)->pluck('fecha')->map(fn ($f) => $f->toDateString());
                        $nuevos = $feriados->filter(fn ($f) => ! $existentes->contains($f['fecha']));
                        if ($nuevos->isEmpty()) {
                            return 'Todos los feriados de ' . $year . ' ya están cargados.';
                        }
                        $lista = $nuevos->map(fn ($f) => '• ' . \Carbon\Carbon::parse($f['fecha'])->format('d/m') . ' — ' . $f['nombre'])->join("\n");
                        return "Se agregarán " . $nuevos->count() . " feriados:\n\n" . $lista;
                    })
                    ->modalSubmitActionLabel('Confirmar')
                    ->action(function () {
                        $year = $this->anio;
                        $response = Http::timeout(10)->get("https://api.argentinadatos.com/v1/feriados/{$year}");
                        if (! $response->ok()) {
                            Notification::make()->title('Error al obtener feriados')->danger()->send();
                            return;
                        }
                        $feriados = collect($response->json());
                        $existentes = HorarioEspecial::whereYear('fecha', $year)->pluck('fecha')->map(fn ($f) => $f->toDateString());
                        $nuevos = $feriados->filter(fn ($f) => ! $existentes->contains($f['fecha']));
                        foreach ($nuevos as $feriado) {
                            HorarioEspecial::create([
                                'fecha'       => $feriado['fecha'],
                                'tipo'        => TipoHorarioEspecial::Exclusion,
                                'todo_el_dia' => true,
                                'desde'       => null,
                                'hasta'       => null,
                                'motivo'      => $feriado['nombre'],
                            ]);
                        }
                        Notification::make()
                            ->title($nuevos->count() . ' feriados cargados')
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),
                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('todo_el_dia')
                    ->label('Todo el día')
                    ->boolean(),
                Tables\Columns\TextColumn::make('desde')
                    ->label('Desde')
                    ->placeholder('—')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('hasta')
                    ->label('Hasta')
                    ->placeholder('—')
                    ->time('H:i'),
                Tables\Columns\IconColumn::make('activo_sistema')
                    ->label('Sistema')
                    ->boolean(),
                Tables\Columns\IconColumn::make('activo_portal')
                    ->label('Portal')
                    ->boolean(),
                Tables\Columns\TextColumn::make('motivo')
                    ->label('Motivo')
                    ->limit(40),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form(fn (HorarioEspecial $record) => $record->tipo === TipoHorarioEspecial::Adicion
                        ? $this->adicionForm()
                        : $this->exclusionForm())
                    ->using(function (HorarioEspecial $record, array $data) {
                        if ($record->tipo === TipoHorarioEspecial::Exclusion) {
                            $data = $this->prepararDatosExclusion($data);
                        }
                        $record->update($data);
                        return $record;
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    private function prepararDatosExclusion(array $data): array
    {
        if ($data['todo_el_dia']) {
            $data['desde'] = null;
            $data['hasta'] = null;
        }

        return $data;
    }
}
