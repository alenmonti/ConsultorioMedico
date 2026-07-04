<?php

namespace App\Filament\Resources\HorarioResource\Pages;

use App\Filament\Resources\HorarioResource;
use App\Filament\Widgets\HorarioEspecialesWidget;
use App\Models\AperturaMensual;
use App\Models\Horario;
use App\Services\HorarioMesService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHorarios extends ListRecords
{
    protected static string $resource = HorarioResource::class;
    public static ?string $title = 'Disponibilidad Horaria';

    public function updatedTableFilters(): void
    {
        parent::updatedTableFilters();

        $anio = (int) ($this->tableFilters['anio']['value'] ?? now()->year);
        $mes = (int) ($this->tableFilters['mes']['value'] ?? now()->month);

        $this->dispatch('mes-horario-cambiado', anio: $anio, mes: $mes);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['anio'] = $this->tableFilters['anio']['value'] ?? now()->year;
                    $data['mes'] = $this->tableFilters['mes']['value'] ?? now()->month;

                    return $data;
                }),
            Actions\Action::make('importar_horarios_base')
                ->label('Importar horarios base')
                ->color('gray')
                ->icon('heroicon-o-document-duplicate')
                ->visible(fn (): bool => ! $this->mesFiltradoTieneHorarios())
                ->action(function (): void {
                    $anio = (int) ($this->tableFilters['anio']['value'] ?? now()->year);
                    $mes = (int) ($this->tableFilters['mes']['value'] ?? now()->month);

                    app(HorarioMesService::class)->asegurarMesConfigurado(auth()->user(), $anio, $mes);
                }),
            Actions\Action::make('abrir_mes')
                ->label(fn () => $this->mesFiltradoAbierto() ? 'Cerrar mes' : 'Abrir mes')
                ->color(fn () => $this->mesFiltradoAbierto() ? 'danger' : 'success')
                ->icon(fn () => $this->mesFiltradoAbierto() ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                ->visible(function (): bool {
                    $anio = (int) ($this->tableFilters['anio']['value'] ?? now()->year);
                    $mes = (int) ($this->tableFilters['mes']['value'] ?? now()->month);

                    return $anio > now()->year || ($anio === now()->year && $mes > now()->month);
                })
                ->action(function (): void {
                    $anio = (int) ($this->tableFilters['anio']['value'] ?? now()->year);
                    $mes = (int) ($this->tableFilters['mes']['value'] ?? now()->month);

                    AperturaMensual::updateOrCreate(
                        ['medico_id' => auth()->user()->medico_id, 'anio' => $anio, 'mes' => $mes],
                        ['abierto' => ! $this->mesFiltradoAbierto()],
                    );
                }),
        ];
    }

    private function mesFiltradoAbierto(): bool
    {
        $anio = (int) ($this->tableFilters['anio']['value'] ?? now()->year);
        $mes = (int) ($this->tableFilters['mes']['value'] ?? now()->month);

        $apertura = AperturaMensual::where('medico_id', auth()->user()->medico_id)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->first();

        return $apertura?->abierto ?? false;
    }

    private function mesFiltradoTieneHorarios(): bool
    {
        $anio = (int) ($this->tableFilters['anio']['value'] ?? now()->year);
        $mes = (int) ($this->tableFilters['mes']['value'] ?? now()->month);

        return Horario::where('medico_id', auth()->user()->medico_id)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->exists();
    }

    protected function getFooterWidgets(): array
    {
        return [
            HorarioEspecialesWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            ...parent::getWidgetData(),
            'anio' => now()->year,
            'mes' => now()->month,
        ];
    }
}
