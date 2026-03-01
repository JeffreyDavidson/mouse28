<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public function getHeading(): string
    {
        return 'Welcome Back';
    }

    public function getSubHeading(): ?string
    {
        return 'Sign in to continue telling your story ✨';
    }
}
