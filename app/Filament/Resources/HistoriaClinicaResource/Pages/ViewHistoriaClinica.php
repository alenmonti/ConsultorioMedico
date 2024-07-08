<?php

namespace App\Filament\Resources\HistoriaClinicaResource\Pages;

use App\Filament\Resources\HistoriaClinicaResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewHistoriaClinica extends ViewRecord
{
    protected static string $resource = HistoriaClinicaResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informacion del paciente')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('paciente.nombre')
                            ->state(function ($record) {
                                return $record->paciente->nombre.' '.$record->paciente->apellido;
                            })
                            ->label('Nombre'),
                        TextEntry::make('paciente.fecha_nacimiento')
                            ->date('d/m/Y')
                            ->label('Fecha de nacimiento'),
                        TextEntry::make('paciente.fecha_nacimiento')
                            ->state(function ($record) {
                                return Carbon::parse($record->paciente->fecha_nacimiento)->age.' años';
                            })
                            ->label('Edad'),
                        TextEntry::make('paciente.dni')
                            ->label('DNI'),
                        TextEntry::make('paciente.telefono')
                            ->label('Telefono'),
                        TextEntry::make('paciente.email')
                            ->label('Email'),
                        TextEntry::make('paciente.medico.name')
                            ->label('Médico de cabecera'),
                        TextEntry::make('paciente.afiliado')
                            ->label('Afiliado'),
                        TextEntry::make('paciente.obra_social')
                            ->label('Obra social'),
                    ]),
                Section::make('Historia Clinica'.' - '.Carbon::parse($infolist->record->fecha)->format('d/m/Y'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(2)
                    ->schema([
                        Fieldset::make('Fecha')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('fecha')->label('')->date('d/m/Y')->columnSpan(2)]),
                        Fieldset::make('Motivo')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('motivo')->label('')->columnSpan(2)]),
                        Fieldset::make('Diagnostico')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('diagnostico')->label('')->columnSpan(2)]),
                        Fieldset::make('Estudios')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('estudios')->label('')->columnSpan(2)]),
                        Fieldset::make('Tratamiento')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('tratamiento')->label('')->columnSpan(2)]),
                        Fieldset::make('Medicamentos')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('medicamentos')->label('')->columnSpan(2)]),
                        Fieldset::make('Examen_fisico')
                            ->columnSpan(1)
                            ->schema([TextEntry::make('examen_fisico')->label('')->columnSpan(2)]),
                        Fieldset::make('Resultados')
                            ->columnSpan(1)
                ]),
            ]);
    }
}
