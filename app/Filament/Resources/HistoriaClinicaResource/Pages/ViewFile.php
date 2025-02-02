<?php

namespace App\Filament\Resources\HistoriaClinicaResource\Pages;

use App\Filament\Resources\HistoriaClinicaResource;
use App\Filament\Resources\PacienteResource;
use App\Models\HistoriaClinica;
use App\Models\Paciente;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Actions\Action as FilamentAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Njxqlus\Filament\Components\Infolists\LightboxImageEntry;

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

    public function evolucionForm()
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
            TextInput::make('motivo')
                ->placeholder('Motivo de la consulta')
                ->columnSpan(2),
            Textarea::make('examen_fisico')
                ->label('Examen Físico')
                ->placeholder('Resultados del examen fisico')
                ->autosize()
                ->columnSpan(2),
            Textarea::make('evolucion')
                ->label('Evolución')
                ->placeholder('Evolución del paciente')
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
            FileUpload::make('imagenes')
                ->label('Imagenes de estudios')
                ->directory('imagenes')
                ->image()
                ->multiple()
                ->panelLayout('grid')
                ->imageEditor()
                ->columnSpan(2),
        ]),
    ];
    }

    public function PrimeraConsultaForm()
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
                Textarea::make('antecedentes')
                    ->placeholder('Antecedentes Personales')
                    ->autosize()
                    ->columnSpan(2),
                Textarea::make('toxicos')
                    ->label('Tóxicos')
                    ->placeholder('Ingrese tóxicos del paciente')
                    ->autosize()
                    ->columnSpan(2),
                Textarea::make('quirurgicos')
                    ->label('Quirúrgicos')
                    ->placeholder('Ingrese quirurgicos del paciente')
                    ->autosize()
                    ->columnSpan(2),
                Textarea::make('alergias')
                    ->placeholder('Ingrese alergias del paciente')
                    ->autosize()
                    ->columnSpan(2),
                RichEditor::make('vacunacion')
                    ->label('Vacunación')
                    ->placeholder('Ingrese vacunación del paciente'),
                RichEditor::make('medicacion')
                    ->label('Medicación')
                    ->placeholder('Ingrese medicación del paciente'),
            ]),
        ];
    }

    public function allForm()
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
                Textarea::make('antecedentes')
                    ->placeholder('Antecedentes Personales')
                    ->autosize(),
                Textarea::make('toxicos')
                    ->label('Tóxicos')
                    ->placeholder('Ingrese tóxicos del paciente')
                    ->autosize(),
                Textarea::make('quirurgicos')
                    ->label('Quirúrgicos')
                    ->placeholder('Ingrese quirurgicos del paciente')
                    ->autosize(),
                Textarea::make('alergias')
                    ->placeholder('Ingrese alergias del paciente')
                    ->autosize(),
                Textarea::make('motivo')
                    ->placeholder('Motivo de la consulta')
                    ->autosize(),
                Textarea::make('examen_fisico')
                    ->label('Examen Físico')
                    ->placeholder('Resultados del examen fisico')
                    ->autosize(),
                Textarea::make('evolucion')
                    ->label('Evolución')
                    ->placeholder('Evolución del paciente')
                    ->autosize(),
                Textarea::make('diagnostico')
                    ->placeholder('Diagnóstico del paciente')
                    ->autosize(),
                RichEditor::make('vacunacion')
                    ->label('Vacunación')
                    ->placeholder('Ingrese vacunación del paciente'),
                RichEditor::make('medicacion')
                    ->label('Medicación')
                    ->placeholder('Ingrese medicación del paciente'),
                RichEditor::make('estudios')
                    ->placeholder('Estudios realizados')
                    ->columnSpan(1),
                RichEditor::make('tratamiento')
                    ->placeholder('Tratamiento del paciente')
                    ->columnSpan(1),
                FileUpload::make('imagenes')
                    ->label('Imagenes de estudios')
                    ->directory('imagenes')
                    ->image()
                    ->multiple()
                    ->panelLayout('grid')
                    ->imageEditor()
                    ->columnSpan(2),
            ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('evolucion')
                ->model(HistoriaClinica::class)
                ->mutateFormDataUsing(function (array $data): array {
                    $data['paciente_id'] = $this->paciente->id;
                    return $data;
                })
                ->label('Nueva Evolución')
                ->createAnother(false)
                ->form($this->evolucionForm()),
                
            CreateAction::make('primeraConsulta')
                ->model(HistoriaClinica::class)
                ->mutateFormDataUsing(function (array $data): array {
                    $data['paciente_id'] = $this->paciente->id;
                    return $data;
                })
                ->label('Primera Consulta')
                ->color('info')
                ->createAnother(false)
                ->form($this->PrimeraConsultaForm()),

            EditAction::make('edit')
                ->icon('heroicon-o-user')
                ->iconButton()
                ->tooltip('Editar Paciente')
                ->color('info')
                ->form(PacienteResource::getForm())
                ->record($this->paciente),

            FilamentAction::make('addDocument')
                ->icon('heroicon-o-document-plus')
                ->iconButton()
                ->tooltip('Subir Historia Clínica antigua')
                ->color('info')
                ->form([FileUpload::make('documento')->label('Documento')->required()->directory('documents')->maxSize(10024)->acceptedFileTypes(['application/pdf'])])
                ->action(function ($data) {
                    $this->paciente->documento = $data['documento'];
                    $this->paciente->save();
                }),
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
                                ->form($this->allForm())
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
                            Fieldset::make('Tóxicos')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->toxicos)
                                ->schema([TextEntry::make('toxicos')->label('')->columnSpan(2)]),
                            Fieldset::make('Quirúrgicos')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->quirurgicos)
                                ->schema([TextEntry::make('quirurgicos')->label('')->columnSpan(2)]),
                            Fieldset::make('Alergias')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->alergias)
                                ->schema([TextEntry::make('alergias')->label('')->columnSpan(2)]),
                            Fieldset::make('Vacunación')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->vacunacion)
                                ->schema([TextEntry::make('vacunacion')->label('')->columnSpan(2)->html()]),
                            Fieldset::make('Medicación')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->medicacion)
                                ->schema([TextEntry::make('medicacion')->label('')->columnSpan(2)->html()]),
                            Fieldset::make('Diagnóstico')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->diagnostico)
                                ->schema([TextEntry::make('diagnostico')->label('')->columnSpan(2)]),
                            Fieldset::make('Motivo')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->motivo)
                                ->schema([TextEntry::make('motivo')->label('')->columnSpan(2)]),
                            Fieldset::make('Examen Físico')
                                ->columnSpan(2)
                                ->hidden(fn($record) => !$record->examen_fisico)
                                ->schema([TextEntry::make('examen_fisico')->label('')->columnSpan(2)]),
                            Fieldset::make('Evolución')
                                ->columnSpan(2)
                                ->hidden(fn($record) => !$record->evolucion)
                                ->schema([TextEntry::make('evolucion')->label('')->columnSpan(2)]),
                            Fieldset::make('Resultados')
                                ->columnSpan(2)
                                ->hidden(fn($record) => !$record->resultados)
                                ->schema([TextEntry::make('resultados')->label('')->columnSpan(2)]),
                            Fieldset::make('Estudios')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->estudios)
                                ->schema([TextEntry::make('estudios')->label('')->columnSpan(2)->html()]),
                            Fieldset::make('Tratamiento')
                                ->columnSpan(1)
                                ->hidden(fn($record) => !$record->tratamiento)
                                ->schema([TextEntry::make('tratamiento')->label('')->columnSpan(2)->html()]),
                            Fieldset::make('Imagenes')
                                ->label('Estudios')
                                ->columnSpan(2)
                                ->columns(8)
                                ->hidden(fn($record) => !$record->imagenes)
                                ->schema(
                                    fn($record) => collect($record->imagenes)->map(function($imagen) use ($record){
                                        return LightboxImageEntry::make('')
                                            ->label('')
                                            ->columnSpan(1)
                                            ->height(100)
                                            ->square()
                                            ->href(fn($record) => Storage::url($imagen))
                                            ->image(fn($record) => Storage::url($imagen))
                                            ->slideGallery('gallery'.$record->id)
                                            ->slideWidth('100%')
                                            ->slideHeight('100%');
                                    })->toArray()
                                ),
                        ]),
                    ])
            ]);
    }

}
