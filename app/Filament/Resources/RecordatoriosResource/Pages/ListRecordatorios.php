<?php

namespace App\Filament\Resources\RecordatoriosResource\Pages;

use App\Enums\EstadosTurno;
use App\Filament\Resources\PacienteResource;
use App\Filament\Resources\RecordatoriosResource;
use App\Jobs\EnviarRecordatorioWhatsAppJob;
use App\Models\Turno;
use App\Notifications\RecordatorioTurnoWhatsApp;
use Carbon\Carbon;
use Filament\Actions\Action as HeaderAction;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ListRecordatorios extends ListRecords
{
    protected static string $resource = RecordatoriosResource::class;

    // No usar emojis en los mensajes de WhatsApp: el redirect de wa.me/api.whatsapp.com
    // corrompe ciertos emojis (los reemplaza por U+FFFD) antes de que el chat los reciba,
    // y no depende de nuestro código (probado con urlencode, rawurlencode y sin pasar por wa.me).
    private const DIRECCION_CONSULTORIO = 'Conesa 849, timbre 4F, Muñiz.';
    private const INSTRUCCION_TIMBRE = 'Al llegar, toque el timbre para que puedan bajar a abrirle.';
    private const FIRMA_CONSULTORIO = '*Consultorio Monti.*';

    private static function medicoNombreCompleto(?\App\Models\User $medico): string
    {
        if (! $medico) {
            return '';
        }

        $nombre = trim("{$medico->name} {$medico->surname}");
        $especialidad = $medico->especialidad ? " ({$medico->especialidad})" : '';

        return "Dra. {$nombre}{$especialidad}";
    }

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::make('enviar_todos_recordatorios')
                ->label('Enviar todos los recordatorios')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                // ->visible(fn () => $this->activeTab === 'recordatorio')
                ->visible(false)
                ->modalHeading('Enviar todos los recordatorios pendientes')
                ->modalSubmitActionLabel('Enviar todos')
                ->modalContent(function () {
                    $pendientes = $this->getPendientesRecordatorio()->with('paciente')->get();

                    if ($pendientes->isEmpty()) {
                        return new HtmlString('<p class="text-sm text-gray-500 dark:text-gray-400">No hay recordatorios pendientes para enviar.</p>');
                    }

                    $totalSegundos = ($pendientes->count() - 1) * 10;
                    $minutos = intdiv($totalSegundos, 60);
                    $segundos = $totalSegundos % 60;
                    $tiempoEstimado = $minutos > 0 ? "{$minutos} min {$segundos} seg" : "{$segundos} seg";

                    $filas = $pendientes->map(function (Turno $t) {
                        $nombre = $t->paciente
                            ? "{$t->paciente->apellido}, {$t->paciente->nombre}"
                            : '(sin paciente)';
                        $telefono = $t->paciente?->telefono ?? '—';
                        $fecha = Carbon::parse($t->fecha)->format('d/m/Y');
                        $sinTelefono = ! $t->paciente?->telefono;

                        $clases = $sinTelefono
                            ? 'text-danger-600 dark:text-danger-400'
                            : 'text-gray-700 dark:text-gray-200';

                        $icono = $sinTelefono
                            ? '<span title="Sin teléfono — se omitirá">⚠️</span>'
                            : '✅';

                        return "<tr class=\"border-b border-gray-100 dark:border-white/10\">
                            <td class=\"py-2 pr-4 text-sm {$clases}\">{$nombre}</td>
                            <td class=\"py-2 pr-4 text-sm {$clases}\">{$telefono}</td>
                            <td class=\"py-2 text-sm {$clases}\">{$fecha}</td>
                            <td class=\"py-2 pl-2 text-sm\">{$icono}</td>
                        </tr>";
                    })->implode('');

                    $conTelefono = $pendientes->filter(fn ($t) => $t->paciente?->telefono)->count();

                    return new HtmlString("
                        <div class=\"space-y-3\">
                            <p class=\"text-sm text-gray-600 dark:text-gray-300\">
                                Se enviarán <strong>{$conTelefono}</strong> de {$pendientes->count()} recordatorios (uno cada 10 segundos, tiempo estimado: {$tiempoEstimado}).
                            </p>
                            <div class=\"overflow-auto max-h-72 rounded-lg border border-gray-200 dark:border-white/10\">
                                <table class=\"w-full\">
                                    <thead>
                                        <tr class=\"border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5\">
                                            <th class=\"py-2 pr-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase\">Paciente</th>
                                            <th class=\"py-2 pr-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase\">Teléfono</th>
                                            <th class=\"py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase\">Fecha turno</th>
                                            <th class=\"py-2\"></th>
                                        </tr>
                                    </thead>
                                    <tbody>{$filas}</tbody>
                                </table>
                            </div>
                        </div>
                    ");
                })
                ->action(function () {
                    $pendientes = $this->getPendientesRecordatorio()->get();

                    $delay = 0;
                    foreach ($pendientes as $turno) {
                        EnviarRecordatorioWhatsAppJob::dispatch($turno->id)
                            ->delay(now()->addSeconds($delay));
                        $delay += 10;
                    }

                    Notification::make()
                        ->success()
                        ->title('Recordatorios encolados')
                        ->body("{$pendientes->count()} mensajes se enviarán en los próximos " . round($delay / 60, 1) . ' minutos.')
                        ->send();
                }),
        ];
    }

    private function getPendientesRecordatorio(): \Illuminate\Database\Eloquent\Builder
    {
        $estadosActivos = [EstadosTurno::Pendiente->value];
        $businessDates = static::nextBusinessDays(2);

        return Turno::query()
            ->whereNull('recordatorio_enviado_at')
            ->whereIn('estado', $estadosActivos)
            ->where(function ($q) use ($businessDates) {
                foreach ($businessDates as $date) {
                    $q->orWhereDate('fecha', $date);
                }
            });
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

                TextColumn::make('aviso_asignacion_enviado_at')
                    ->label('Aviso enviado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->visible(fn () => $this->activeTab === 'aviso_asignacion')
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
                // Tab 0: enviar aviso de turno asignado
                TableAction::make('enviar_aviso_asignacion')
                    ->tooltip('Enviar aviso de turno asignado')
                    ->icon('heroicon-o-check-badge')
                    ->iconButton()
                    ->color('success')
                    ->visible(fn (Turno $record) => ($this->activeTab === 'aviso_asignacion' || $this->activeTab === null) && $record->paciente_id !== null)
                    ->requiresConfirmation()
                    ->modalHeading('Enviar aviso de turno asignado')
                    ->modalDescription(fn (Turno $record) => "Se enviará un aviso a {$record->paciente->nombre} {$record->paciente->apellido} confirmando que su turno fue asignado correctamente y se marcará como enviado.")
                    ->modalSubmitActionLabel('Marcar como enviado y abrir WhatsApp')
                    ->action(function (Turno $record) {
                        $record->update(['aviso_asignacion_enviado_at' => now()]);

                        $paciente = $record->paciente;
                        $medicoNombre = static::medicoNombreCompleto($record->medico);
                        $fecha = Carbon::parse($record->fecha)->format('d/m/Y');

                        $mensaje = rawurlencode(
                            "Hola, ¡buenos días!\n\n" .
                            "Le confirmamos que su turno con la *{$medicoNombre}* quedó asignado correctamente.\n\n" .
                            "*{$fecha} a las {$record->hora} hs*\n" .
                            "*" . self::DIRECCION_CONSULTORIO . "*\n" .
                            self::INSTRUCCION_TIMBRE . "\n" .
                            "Si por algún motivo no puede asistir, le pedimos que nos lo informe con anticipación.\n\n" .
                            self::FIRMA_CONSULTORIO
                        );

                        $url = "https://api.whatsapp.com/send?phone=549{$paciente->telefono}&text={$mensaje}";

                        $this->js("window.open(" . json_encode($url) . ", '_blank')");
                    }),

                // Tab 1: informar seña (link directo a WhatsApp, sin modal)
                TableAction::make('informar_senia')
                    ->tooltip('Informar seña')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->iconButton()
                    ->color('success')
                    ->visible(fn (Turno $record) => $this->activeTab === 'sin_informar' && $record->paciente_id !== null)
                    ->action(function (Turno $record) {
                        $record->update(['senia_informada_at' => now()]);

                        $paciente = $record->paciente;
                        $fecha = Carbon::parse($record->fecha)->format('d/m/Y');
                        $monto = auth()->user()->monto_senia;
                        $alias = auth()->user()->alias_pago;

                        $montoTexto = $monto ? " de *\${$monto}*" : '';
                        $aliasTexto = $alias ? "Alias: *{$alias}*\n" : '';
                        $costoPractica = $record->practica?->costo !== null
                            ? number_format($record->practica->costo, 2, ',', '.')
                            : '0';

                        $mensaje = rawurlencode(
                            "Hola, ¡buenos días!\n\n" .
                            "Tiene un turno el *{$fecha} a las {$record->hora} hs*, el valor del mismo es de *\${$costoPractica}*.\n" .
                            "Para confirmar su turno deberá abonar una seña{$montoTexto} que luego será descontada del valor total.\n\n" .
                            "Información bancaria para abonar la seña:\n" .
                            "{$aliasTexto}" .
                            "CBU: *0140029803505567741050*\n" .
                            "Titular de la cuenta: *Mailin Monti*\n\n" .
                            "Por favor, envíe el comprobante por este chat dentro de las *48 horas hábiles* para mantener la reserva de su turno.\n\n" .
                            self::FIRMA_CONSULTORIO
                        );

                        $url = "https://api.whatsapp.com/send?phone=549{$paciente->telefono}&text={$mensaje}";

                        $this->js("window.open(" . json_encode($url) . ", '_blank')");
                    }),

                // Tab 2: ir a WhatsApp
                TableAction::make('ver_whatsapp_senia')
                    ->tooltip('Ir a WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->iconButton()
                    ->color('success')
                    ->visible(fn (Turno $record) => $this->activeTab === 'informados' && $record->paciente_id !== null)
                    ->url(fn (Turno $record) => "https://api.whatsapp.com/send?phone=549{$record->paciente->telefono}")
                    ->openUrlInNewTab(),

                // Tab 2: marcar seña como pagada
                TableAction::make('marcar_pagada')
                    ->tooltip('Marcar como pagada')
                    ->icon('heroicon-o-check-circle')
                    ->iconButton()
                    ->color('primary')
                    ->visible(fn (Turno $record) => $this->activeTab === 'informados' && $record->paciente_id !== null)
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalIconColor('success')
                    ->modalHeading('Confirmar pago de seña')
                    ->modalDescription(fn (Turno $record) => "¿Confirmar que {$record->paciente->nombre} {$record->paciente->apellido} abonó la seña?")
                    ->action(function (Turno $record) {
                        $record->update([
                            'senia_informada_at' => $record->senia_informada_at ?? now(),
                            'senia_pagada_at' => now(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Seña registrada como pagada')
                            ->send();
                    }),

                // Tab 2: cancelar por falta de pago
                TableAction::make('cancelar_falta_pago')
                    ->tooltip('Cancelar por falta de pago de seña')
                    ->icon('heroicon-o-x-circle')
                    ->iconButton()
                    ->color('danger')
                    ->visible(fn (Turno $record) => $this->activeTab === 'informados'
                        && $record->paciente_id !== null
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

                        $mensaje = rawurlencode(
                            "Hola, ¡buenos días!\n\n" .
                            "Le informamos que su turno del *{$fecha} a las {$record->hora} hs* fue cancelado debido a que no recibimos el pago de la seña dentro del plazo establecido.\n\n" .
                            "Si desea reservar un nuevo turno, puede hacerlo desde nuestro portal:\n{$portalUrl}\n\n" .
                            "También puede comunicarse con nosotros por este mismo chat para asignarle un nuevo turno.\n\n" .
                            self::FIRMA_CONSULTORIO
                        );

                        $url = "https://api.whatsapp.com/send?phone=549{$paciente->telefono}&text={$mensaje}";

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
                    ->visible(fn (Turno $record) => $this->activeTab === 'recordatorio' && $record->paciente_id !== null)
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
                        $fecha = Carbon::parse($record->fecha)->format('d/m/Y');
                        $appUrl = config('app.url');

                        $confirmUrl = "{$appUrl}/turno/confirmar/{$record->id}?token={$token}";
                        $cancelUrl = "{$appUrl}/turno/cancelar/{$record->id}?token={$token}";

                        $medicoNombre = static::medicoNombreCompleto($record->medico);

                        $mensaje = rawurlencode(
                            "Hola, ¡buenos días!\n\n" .
                            "Le recordamos que tiene un turno con la *{$medicoNombre}*.\n\n" .
                            "*{$fecha} a las {$record->hora} hs*\n" .
                            "*" . self::DIRECCION_CONSULTORIO . "*\n" .
                            self::INSTRUCCION_TIMBRE . "\n\n" .
                            "*Confirmar turno:*\n{$confirmUrl}\n\n" .
                            "*Cancelar turno:*\n{$cancelUrl}\n\n" .
                            self::FIRMA_CONSULTORIO
                        );

                        $url = "https://api.whatsapp.com/send?phone=549{$paciente->telefono}&text={$mensaje}";

                        $this->js("window.open(" . json_encode($url) . ", '_blank')");
                    }),

                // Tab 3: enviar recordatorio via API de WhatsApp
                TableAction::make('enviar_recordatorio_api')
                    ->tooltip('Enviar recordatorio por WhatsApp (API)')
                    ->icon('heroicon-o-paper-airplane')
                    ->iconButton()
                    ->color('primary')
                    // ->visible(fn (Turno $record) => $this->activeTab === 'recordatorio'
                    //     && $record->paciente_id !== null
                    //     && $record->paciente?->telefono
                    // )
                    ->visible(false)
                    ->requiresConfirmation()
                    ->modalHeading('Enviar recordatorio por WhatsApp')
                    ->modalDescription(fn (Turno $record) => "Se enviará el recordatorio con links de confirmación/cancelación a {$record->paciente->nombre} {$record->paciente->apellido} ({$record->paciente->telefono}) directamente por WhatsApp.")
                    ->modalSubmitActionLabel('Enviar')
                    ->action(function (Turno $record) {
                        if (! $record->turno_token) {
                            $token = Str::random(40);
                            while (Turno::withoutGlobalScopes()->where('turno_token', $token)->exists()) {
                                $token = Str::random(40);
                            }
                            $record->update(['turno_token' => $token]);
                            $record->refresh();
                        }

                        try {
                            $record->paciente->notify(new RecordatorioTurnoWhatsApp($record));
                            $record->update(['recordatorio_enviado_at' => now()]);

                            Notification::make()
                                ->success()
                                ->title('Recordatorio enviado')
                                ->body("Mensaje enviado a {$record->paciente->nombre} {$record->paciente->apellido}")
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->danger()
                                ->title('Error al enviar por WhatsApp')
                                ->body("No se pudo enviar el mensaje a {$record->paciente->telefono}. Verificá que el número sea correcto.")
                                ->send();
                        }
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
        $estadosActivos = [EstadosTurno::Pendiente->value];
        $businessDates = static::nextBusinessDays(2);

        return [
            'aviso_asignacion' => Tab::make('Turno asignado')
                ->icon('heroicon-o-calendar-days')
                ->badge(
                    Turno::query()
                        ->whereNull('aviso_asignacion_enviado_at')
                        ->whereIn('estado', $estadosActivos)
                        ->whereDate('fecha', '>=', today())
                        ->count()
                )
                ->modifyQueryUsing(fn ($query) => $query
                    ->whereNull('aviso_asignacion_enviado_at')
                    ->whereIn('estado', $estadosActivos)
                    ->whereDate('fecha', '>=', today())
                ),

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
                        ->whereDate('fecha', '>=', today())
                        ->count()
                )
                ->modifyQueryUsing(fn ($query) => $query
                    ->whereNotNull('senia_informada_at')
                    ->whereNull('senia_pagada_at')
                    ->whereIn('estado', $estadosActivos)
                    ->whereDate('fecha', '>=', today())
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
