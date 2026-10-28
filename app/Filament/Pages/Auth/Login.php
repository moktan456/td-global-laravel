<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

/** Admin sign-in with the same username (or email) and password as the staff area. No "remember me". */
class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Username')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getRememberFormComponent(): Component
    {
        return Hidden::make('remember')->default(false);
    }

    /** @param  array<string, mixed>  $data */
    protected function getCredentialsFromFormData(#[\SensitiveParameter] array $data): array
    {
        $login = mb_strtolower(trim((string) ($data['email'] ?? '')));

        return [
            str_contains($login, '@') ? 'email' : 'username' => $login,
            'password' => (string) ($data['password'] ?? ''),
            'active' => true,
        ];
    }
}
