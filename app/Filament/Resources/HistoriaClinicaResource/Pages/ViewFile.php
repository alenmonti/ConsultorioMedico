<?php

namespace App\Filament\Resources\HistoriaClinicaResource\Pages;

use App\Filament\Resources\HistoriaClinicaResource;
use App\Models\HistoriaClinica;
use App\Models\Paciente;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;

class ViewFile extends Page implements HasForms, HasInfolists
{
    use InteractsWithInfolists, InteractsWithForms;

    protected static string $resource = HistoriaClinicaResource::class;
    protected static string $view = 'filament.resources.historia-clinica-resource.pages.view-file';

    protected static ?string $title = 'Historia Clínica';

    public $paciente;
    
    public function mount()
    {
        $this->paciente = Paciente::find(request()->paciente_id);
    }

    public function historiaForm()
    {
        return [
        Grid::make('')
        ->label('')
        ->columns(2)
        ->schema([
            Hidden::make('paciente_id'),
            DatePicker::make('fecha')
                ->native(false)
                ->default(now())
                ->required()
                ->columnSpan(2),
            TextInput::make('antecedentes')
                ->placeholder('Antecedentes del paciente'),
            TextInput::make('motivo')
                ->placeholder('Motivo de la consulta'),
            Textarea::make('examen_fisico')
                ->placeholder('Resultados del examen fisico')
                ->autosize()
                ->columnSpan(2),
            Textarea::make('evolucion')
                ->placeholder('Evolución del paciente')
                ->autosize()
                ->columnSpan(2),
            Textarea::make('resultados')
                ->placeholder('Resultados de los estudios')
                ->autosize()
                ->columnSpan(2),
            Textarea::make('diagnostico')
                ->placeholder('Diagnóstico del paciente')
                ->autosize()
                ->columnSpan(2),
            RichEditor::make('estudios')
                ->placeholder('Estudios realizados')
                ->columnSpan(1),
            RichEditor::make('tratamiento')
                ->placeholder('Tratamiento del paciente')
                ->columnSpan(1),
        ]),
    ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->model(HistoriaClinica::class)
                ->mutateFormDataUsing(function (array $data): array {
                    $data['paciente_id'] = $this->paciente->id;
                    return $data;
                })
                ->label('Nueva Evolución')
                ->createAnother(false)
                ->form($this->historiaForm()),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->paciente)
            ->schema([
                Section::make('Informacion del paciente')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('paciente.nombre')
                            ->state(function () {return $this->paciente->nombre.' '.$this->paciente->apellido;})
                            ->label('Nombre'),
                        TextEntry::make('fecha_nacimiento')
                            ->state(function () {
                                if(!$this->paciente->fecha_nacimiento) return 'N/A';
                                return Carbon::parse($this->paciente->fecha_nacimiento)->format('d/m/Y');
                            })
                            ->label('Fecha de nacimiento'),
                        TextEntry::make('fecha_nacimiento')
                            ->state(function () {
                                if(!$this->paciente->fecha_nacimiento) return 'N/A';
                                return Carbon::parse($this->paciente->fecha_nacimiento)->age.' años';
                            })
                            ->label('Edad'),
                        TextEntry::make('obra_social')
                            ->label('Obra social')
                            ->default('N/A'),
                        TextEntry::make('dni')
                            ->label('DNI'),
                        TextEntry::make('telefono')
                            ->label('Teléfono')
                            ->default('N/A'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->default('N/A'),
                        TextEntry::make('afiliado')
                            ->label('Afiliado')
                            ->default('N/A'),
                        TextEntry::make('direccion')
                            ->label('Dirección')
                            ->default('N/A'),
                    ]),
                RepeatableEntry::make('historiasClinicas')
                    ->label('')
                    ->contained(false)
                    ->schema([
                        Section::make('Evolución Clínica')
                        ->headerActions([
                            Action::make('edit')
                                ->icon('heroicon-o-pencil')
                                ->iconButton()
                                ->form($this->historiaForm())
                                ->fillForm(fn($record) => $record->toArray())
                                ->action(fn($record, $data) => $record->update($data)),
                            Action::make('delete')
                                ->requiresConfirmation(true)
                                ->icon('heroicon-o-trash')
                                ->iconButton()
                                ->action(fn($record) => $record->delete()),
                        ])
                                
                        ->heading(function($record){return 'Evolución '.$record->fecha;})
                        ->icon('heroicon-o-clipboard-document-list')
                        ->columns(2)
                        ->schema([
                            Fieldset::make('Antecedentes')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->antecedentes)
                                ->schema([TextEntry::make('antecedentes')->label('')->columnSpan(2)]),
                            Fieldset::make('Motivo')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->motivo)
                                ->schema([TextEntry::make('motivo')->label('')->columnSpan(2)]),
                            Fieldset::make('Examen_fisico')
                                ->columnSpan(2)
                                ->hidden(fn($record) => !$record->examen_fisico)
                                ->schema([TextEntry::make('examen_fisico')->label('')->columnSpan(2)]),
                            Fieldset::make('Evolucion')
                                ->columnSpan(2)
                                ->hidden(fn($record) => !$record->evolucion)
                                ->schema([TextEntry::make('evolucion')->label('')->columnSpan(2)]),
                            Fieldset::make('Resultados')
                                ->columnSpan(2)
                                ->hidden(fn($record) => !$record->resultados)
                                ->schema([TextEntry::make('resultados')->label('')->columnSpan(2)]),
                            Fieldset::make('Diagnostico')
                                ->columnSpan(2)
                                ->hidden(fn($record) => !$record->diagnostico)
                                ->schema([TextEntry::make('diagnostico')->label('')->columnSpan(2)]),
                            Fieldset::make('Estudios')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->estudios)
                                ->schema([TextEntry::make('estudios')->label('')->columnSpan(2)->html()]),
                            Fieldset::make('Tratamiento')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->tratamiento)
                                ->schema([TextEntry::make('tratamiento')->label('')->columnSpan(2)->html()]),
                        ]),
                    ]),
            ]);
    }

}
