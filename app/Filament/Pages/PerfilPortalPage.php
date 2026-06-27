<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class PerfilPortalPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = null;
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.perfil-portal';
    protected static ?string $slug = 'configuracion';

    public function getTitle(): string
    {
        return 'Configuración';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'especialidad'        => $user->especialidad,
            'descripcion'         => $user->descripcion,
            'foto_portal'         => $user->foto_portal,
            'whatsapp'            => $user->whatsapp,
            'portal_turnos_activo' => (bool) $user->portal_turnos_activo,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Perfil público')
                    ->description('Estos datos se muestran a los pacientes cuando buscan turno en el portal web.')
                    ->schema([
                        Toggle::make('portal_turnos_activo')
                            ->label('Activar portal de turnos')
                            ->helperText('Cuando está activo, los pacientes pueden encontrarte en el portal y reservar turnos.'),

                        TextInput::make('especialidad')
                            ->label('Especialidad')
                            ->placeholder('Ej: Cardiología, Pediatría…')
                            ->maxLength(120),

                        TextInput::make('whatsapp')
                            ->label('WhatsApp de contacto')
                            ->placeholder('Ej: 5491112345678')
                            ->helperText('Número completo con código de país, sin +, sin espacios.')
                            ->maxLength(30),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Una breve presentación que verán los pacientes…')
                            ->rows(4)
                            ->maxLength(500),

                        FileUpload::make('foto_portal')
                            ->label('Foto de perfil')
                            ->image()
                            ->imageEditor()
                            ->directory('portal/fotos')
                            ->maxSize(2048)
                            ->helperText('Imagen cuadrada recomendada. Máximo 2 MB.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $user = auth()->user();

        $user->fill([
            'especialidad' => $state['especialidad'],
            'descripcion'  => $state['descripcion'],
            'foto_portal'  => $state['foto_portal'],
            'whatsapp'     => $state['whatsapp'],
        ]);
        $user->save();

        // portal_turnos_activo is not in $fillable — update via query builder
        DB::table('users')
            ->where('id', $user->id)
            ->update(['portal_turnos_activo' => $state['portal_turnos_activo'] ? 1 : 0]);

        Notification::make()
            ->success()
            ->title('Perfil actualizado')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->submit('save'),
        ];
    }
}
