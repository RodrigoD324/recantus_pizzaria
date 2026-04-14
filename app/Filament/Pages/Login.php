<?php

namespace App\Filament\Pages\Auth;

use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Login extends BaseLogin
{

    protected static string $view = 'filament.pages.login';
    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('cpf') 
                    ->label('CPF')
                    ->placeholder('000.000.000-00')
                    ->mask('999.999.999-99')
                    ->required()
                    ->autocomplete()
                    ->autofocus(),
                TextInput::make('password')
                    ->label('Senha')
                    ->placeholder('********')
                    ->password()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        $cpfLimpo = preg_replace('/\D/', '', $data['cpf']);

        $user = User::whereHas('pessoa', function ($q) use ($cpfLimpo) {
            $q->where('cpf', $cpfLimpo);
        })->first();

        if (!$user || !Hash::check($data['password'], $user->password) || $user->id_usuario_tipo == 2) {
            $this->throwFailureValidationException();
        }

        Auth::login($user, remember: true);
        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'data.cpf' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}