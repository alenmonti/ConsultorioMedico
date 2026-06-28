<?php

namespace App\Filament\Resources\RecordatoriosResource\Pages;

use App\Enums\EstadosTurno;
use App\Filament\Resources\PacienteResource;
use App\Filament\Resources\RecordatoriosResource;
use App\Models\Turno;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ListRecordatorios extends ListRecords
{
    protected static string $resource = RecordatoriosResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Recordatorios de turnos';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Turno::query())
            ->defaultSort('fecha', 'asc')
            ->columns([
                TextColumn::make('paciente.apellido')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => $record->paciente
                        ? $record->paciente->apellido . ', ' . $record->paciente->nombre
                        : '—'
                    )
                    ->searchable(query: fn ($query, $search) => $query->whereHas('paciente', fn ($q) => $q
                        ->where('apellido', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                    ))
                    ->sortable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('hora')
                    ->label('Hora')
                    ->visibleFrom('sm'),

                TextColumn::make('practica.nombre')
                    ->label('Práctica')
                    ->default('-')
                    ->visibleFrom('sm'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->visibleFrom('sm'),

                TextColumn::make('senia_informada_at')
                    ->label('Seña informada')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->visible(fn () => $this->activeTab === 'informados')
                    ->visibleFrom('sm'),

                TextColumn::make('paciente.telefono')
                    ->label('Teléfono')
                    ->placeholder('Sin teléfono')
                    ->visibleFrom('sm'),
            ])
            ->actions([
                // Tab 1: informar seña (link directo a WhatsApp, sin modal)
                TableAction::make('informar_senia')
                    ->tooltip('Informar seña')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->iconButton()
                    ->color('success')
                    ->visible(fn () => $this->activeTab === 'sin_informar' || $this->activeTab === null)
                    ->action(function (Turno $record) {
                        $record->update(['senia_informada_at' => now()]);

                        $paciente = $record->paciente;
                        $fecha = Carbon::parse($record->fecha)->format('d/m/Y');
                        $monto = auth()->user()->monto_senia;
                        $alias = auth()->user()->alias_pago;

                        $textoAlias = $alias
                            ? "enviando el monto al alias *{$alias}*"
                            : 'coordinando el pago con nosotros';

                        $montoTexto = $monto ? " de *\${$monto}*" : '';

                        $mensaje = urlencode(
                            "Hola {$paciente->nombre}! Le informamos que para confirmar su turno del {$fecha} a las {$record->hora} deberá abonar una seña{$montoTexto} {$textoAlias}.\n\n" .
                            "Por favor compartí el comprobante por este chat dentro de las *48 hs hábiles* para mantener el turno.\n\n" .
                            "¡Muchas gracias!"
                        );

                        $url = "https://wa.me/549{$paciente->telefono}?text={$mensaje}";

                        $this->js("window.open(" . json_encode($url) . ", '_blank')");
                    }),

                // Tab 2: ir a WhatsApp
                TableAction::make('ver_whatsapp_senia')
                    ->tooltip('Ir a WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->iconButton()
                    ->color('success')
                    ->visible(fn () => $this->activeTab === 'informados')
                    ->url(fn (Turno $record) => "https://wa.me/549{$record->paciente->telefono}")
                    ->openUrlInNewTab(),

                // Tab 2: marcar seña como pagada
                TableAction::make('marcar_pagada')
                    ->tooltip('Marcar como pagada')
                    ->icon('heroicon-o-check-circle')
                    ->iconButton()
                    ->color('primary')
                    ->visible(fn () => $this->activeTab === 'informados')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalIconColor('success')
                    ->modalHeading('Confirmar pago de seña')
                    ->modalDescription(fn (Turno $record) => "¿Confirmar que {$record->paciente->nombre} {$record->paciente->apellido} abonó la seña?")
                    ->action(function (Turno $record) {
                        $record->update([
                            'senia_informada_at' => $record->senia_informada_at ?? now(),
                            'senia_pagada_at' => now(),
                            'estado' => EstadosTurno::Confirmado,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Seña registrada como pagada')
                            ->body('El turno quedó confirmado.')
                            ->send();
                    }),

                // Tab 2: cancelar por falta de pago
                TableAction::make('cancelar_falta_pago')
                    ->tooltip('Cancelar por falta de pago de seña')
                    ->icon('heroicon-o-x-circle')
                    ->iconButton()
                    ->color('danger')
                    ->visible(fn (Turno $record) => $this->activeTab === 'informados'
                        && $record->senia_informada_at
                        && $record->senia_informada_at->diffInDays(now()) >= 2
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar turno por falta de pago de seña')
                    ->modalDescription(fn (Turno $record) => "Se cancelará el turno de {$record->paciente->nombre} {$record->paciente->apellido} y se abrirá WhatsApp para avisarle.")
                    ->modalSubmitActionLabel('Cancelar turno y abrir WhatsApp')
                    ->action(function (Turno $record) {
                        $record->update(['estado' => EstadosTurno::Cancelado]);

                        $paciente = $record->paciente;
                        $fecha = Carbon::parse($record->fecha)->format('d/m/Y');
                        $portalUrl = config('app.url') . '/portal-turnos';

                        $mensaje = urlencode(
                            "Hola {$paciente->nombre}! Le informamos que su turno del {$fecha} a las {$record->hora} fue *cancelado* debido a que no recibimos el pago de la seña dentro del plazo establecido.\n\n" .
                            "Si desea reservar un nuevo turno puede hacerlo desde nuestro portal: {$portalUrl}\n\n"
                        );

                        $url = "https://wa.me/549{$paciente->telefono}?text={$mensaje}";

                        Notification::make()
                            ->warning()
                            ->title('Turno cancelado')
                            ->body("{$paciente->nombre} {$paciente->apellido}")
                            ->send();

                        $this->js("window.open(" . json_encode($url) . ", '_blank')");
                    }),

                // Tab 3: enviar recordatorio con links de confirmación/cancelación
                TableAction::make('enviar_recordatorio')
                    ->tooltip('Enviar recordatorio')
                    ->icon('heroicon-o-bell-alert')
                    ->iconButton()
                    ->color('warning')
                    ->visible(fn () => $this->activeTab === 'recordatorio')
                    ->requiresConfirmation()
                    ->modalHeading('Enviar recordatorio de turno')
                    ->modalDescription(fn (Turno $record) => "Se generarán links de confirmación/cancelación para {$record->paciente->nombre} {$record->paciente->apellido} y se marcará el recordatorio como enviado.")
                    ->modalSubmitActionLabel('Marcar como enviado y abrir WhatsApp')
                    ->action(function (Turno $record) {
                        $token = Str::random(40);

                        while (Turno::withoutGlobalScopes()->where('turno_token', $token)->exists()) {
                            $token = Str::random(40);
                        }

                        $record->update([
                            'recordatorio_enviado_at' => now(),
                            'turno_token' => $token,
                        ]);

                        $paciente = $record->paciente;
                        $medico = $record->medico;
                        $fecha = Carbon::parse($record->fecha)
                            ->locale('es')
                            ->isoFormat('dddd D [de] MMMM');
                        $fecha = ucfirst($fecha);
                        $appUrl = config('app.url');

                        $confirmUrl = "{$appUrl}/turno/confirmar/{$record->id}?token={$token}";
                        $cancelUrl = "{$appUrl}/turno/cancelar/{$record->id}?token={$token}";

                        $medicoNombre = $medico ? $medico->name : '';

                        $mensaje = urlencode(
                            "Hola {$paciente->nombre}, te recordamos que el día *{$fecha}* a las *{$record->hora} hs* tenés turno" .
                            ($medicoNombre ? " con {$medicoNombre}" : '') . ".\n\n" .
                            "Confirmar turno:\n{$confirmUrl}\n\n" .
                            "Cancelar turno:\n{$cancelUrl}\n\n" .
                            "¡Muchas gracias!"
                        );

                        $url = "https://wa.me/549{$paciente->telefono}?text={$mensaje}";

                        $this->js("window.open(" . json_encode($url) . ", '_blank')");
                    }),

                // Todos los tabs: editar turno
                EditAction::make()
                    ->tooltip('Editar turno')
                    ->slideOver(),

                // Todos los tabs: editar paciente (cuando tiene uno asignado)
                TableAction::make('edit_paciente')
                    ->tooltip('Editar paciente')
                    ->icon('heroicon-o-user')
                    ->iconButton()
                    ->color('gray')
                    ->visible(fn (Turno $record) => $record->paciente_id !== null)
                    ->slideOver()
                    ->form(PacienteResource::getForm())
                    ->fillForm(fn (Turno $record) => $record->paciente->attributesToArray())
                    ->action(function (Turno $record, array $data) {
                        $record->paciente->update($data);

                        Notification::make()
                            ->success()
                            ->title('Paciente actualizado')
                            ->send();
                    }),
            ])
            ->paginated([10, 25, 50]);
    }

    public function getTabs(): array
    {
        $estadosActivos = [EstadosTurno::Pendiente->value, EstadosTurno::Confirmado->value];
        $businessDates = static::nextBusinessDays(2);

        return [
            'sin_informar' => Tab::make('Sin informar seña')
                ->icon('heroicon-o-bell-slash')
                ->badge(
                    Turno::query()
                        ->whereNull('senia_informada_at')
                        ->whereNull('senia_pagada_at')
                        ->whereIn('estado', $estadosActivos)
                        ->whereDate('fecha', '>=', today())
                        ->count()
                )
                ->modifyQueryUsing(fn ($query) => $query
                    ->whereNull('senia_informada_at')
                    ->whereNull('senia_pagada_at')
                    ->whereIn('estado', $estadosActivos)
                    ->whereDate('fecha', '>=', today())
                ),

            'informados' => Tab::make('Seña informada, sin pagar')
                ->icon('heroicon-o-clock')
                ->badge(
                    Turno::query()
                        ->whereNotNull('senia_informada_at')
                        ->whereNull('senia_pagada_at')
                        ->whereIn('estado', $estadosActivos)
                        ->count()
                )
                ->modifyQueryUsing(fn ($query) => $query
                    ->whereNotNull('senia_informada_at')
                    ->whereNull('senia_pagada_at')
                    ->whereIn('estado', $estadosActivos)
                ),

            'recordatorio' => Tab::make('Recordatorio pendiente')
                ->icon('heroicon-o-bell-alert')
                ->badge(
                    Turno::query()
                        ->whereNull('recordatorio_enviado_at')
                        ->whereIn('estado', $estadosActivos)
                        ->where(function ($q) use ($businessDates) {
                            foreach ($businessDates as $date) {
                                $q->orWhereDate('fecha', $date);
                            }
                        })
                        ->count()
                )
                ->modifyQueryUsing(fn ($query) => $query
                    ->whereNull('recordatorio_enviado_at')
                    ->whereIn('estado', $estadosActivos)
                    ->where(function ($q) use ($businessDates) {
                        foreach ($businessDates as $date) {
                            $q->orWhereDate('fecha', $date);
                        }
                    })
                ),
        ];
    }

    public static function nextBusinessDays(int $count): array
    {
        $dates = [];
        $current = Carbon::today();
        while (count($dates) < $count) {
            $current = $current->copy()->addDay();
            if (! $current->isWeekend()) {
                $dates[] = $current->format('Y-m-d');
            }
        }
        return $dates;
    }

    public static function businessDaysSince(Carbon $from): int
    {
        $count = 0;
        $current = $from->copy()->startOfDay()->addDay();
        $today = Carbon::today();
        while ($current->lte($today)) {
            if (! $current->isWeekend()) {
                $count++;
            }
            $current->addDay();
        }
        return $count;
    }
}
