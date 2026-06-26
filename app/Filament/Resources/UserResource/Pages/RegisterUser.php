<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register;

class RegisterUser extends Register
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                TextInput::make('surname')
                    ->required()
                    ->label('Apellido'),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                TextInput::make('registration_code')
                    ->label('Código de registro')
                    ->required()
                    ->password()
                    ->dehydrated(false)
                    ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                        if ($value !== config('app.registration_code')) {
                            $fail('El código de registro es incorrecto.');
                        }
                    }),
            ]);
    }
}