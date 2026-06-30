<?php

namespace App\Filament\Widgets;

use App\Models\HorarioExclusion;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use HusamTariq\FilamentTimePicker\Forms\Components\TimePickerField;
use Illuminate\Support\Facades\Http;

class HorarioExclusionesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $form = [
            DatePicker::make('fecha')
                ->label('Fecha')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y'),
            Toggle::make('todo_el_dia')
                ->label('Todo el día')
                ->default(true)
                ->live(),
            TimePickerField::make('desde')
                ->label('Desde')
                ->visible(fn ($get) => ! $get('todo_el_dia'))
                ->required(fn ($get) => ! $get('todo_el_dia')),
            TimePickerField::make('hasta')
                ->label('Hasta')
                ->visible(fn ($get) => ! $get('todo_el_dia'))
                ->required(fn ($get) => ! $get('todo_el_dia')),
            TextInput::make('motivo')
                ->label('Motivo')
                ->required()
                ->maxLength(255),
        ];

        return $table
            ->query(HorarioExclusion::query())
            ->defaultSort('fecha', 'asc')
            ->heading('Días Excluidos')
            ->description('Días o rangos horarios en los que no se generarán turnos.')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agregar exclusión')
                    ->modalHeading('Nueva Exclusión')
                    ->form($form)
                    ->using(function (array $data) {
                        if ($data['todo_el_dia']) {
                            $data['desde'] = null;
                            $data['hasta'] = null;
                        }
                        return HorarioExclusion::create($data);
                    }),
                Action::make('cargar_feriados')
                    ->label('Cargar feriados ' . now()->year)
                    ->color('gray')
                    ->icon('heroicon-o-calendar-days')
                    ->requiresConfirmation()
                    ->modalHeading('Cargar feriados ' . now()->year)
                    ->modalDescription(function () {
                        $year = now()->year;
                        $response = Http::timeout(10)->get("https://api.argentinadatos.com/v1/feriados/{$year}");
                        if (! $response->ok()) {
                            return 'No se pudo obtener la lista de feriados. Intente nuevamente.';
                        }
                        $feriados = collect($response->json());
                        $existentes = HorarioExclusion::whereYear('fecha', $year)->pluck('fecha')->map(fn ($f) => $f->toDateString());
                        $nuevos = $feriados->filter(fn ($f) => ! $existentes->contains($f['fecha']));
                        if ($nuevos->isEmpty()) {
                            return 'Todos los feriados de ' . $year . ' ya están cargados.';
                        }
                        $lista = $nuevos->map(fn ($f) => '• ' . \Carbon\Carbon::parse($f['fecha'])->format('d/m') . ' — ' . $f['nombre'])->join("\n");
                        return "Se agregarán " . $nuevos->count() . " feriados:\n\n" . $lista;
                    })
                    ->modalSubmitActionLabel('Confirmar')
                    ->action(function () {
                        $year = now()->year;
                        $response = Http::timeout(10)->get("https://api.argentinadatos.com/v1/feriados/{$year}");
                        if (! $response->ok()) {
                            Notification::make()->title('Error al obtener feriados')->danger()->send();
                            return;
                        }
                        $feriados = collect($response->json());
                        $existentes = HorarioExclusion::whereYear('fecha', $year)->pluck('fecha')->map(fn ($f) => $f->toDateString());
                        $nuevos = $feriados->filter(fn ($f) => ! $existentes->contains($f['fecha']));
                        foreach ($nuevos as $feriado) {
                            HorarioExclusion::create([
                                'fecha'      => $feriado['fecha'],
                                'todo_el_dia' => true,
                                'desde'      => null,
                                'hasta'      => null,
                                'motivo'     => $feriado['nombre'],
                            ]);
                        }
                        Notification::make()
                            ->title($nuevos->count() . ' feriados cargados')
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
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
                Tables\Columns\TextColumn::make('motivo')
                    ->label('Motivo')
                    ->limit(40),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($form)
                    ->using(function (HorarioExclusion $record, array $data) {
                        if ($data['todo_el_dia']) {
                            $data['desde'] = null;
                            $data['hasta'] = null;
                        }
                        $record->update($data);
                        return $record;
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
