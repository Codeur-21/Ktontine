<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Auth\Authenticatable;

class Login extends BaseLogin
{
    protected string $view = 'filament.auth.login';

    protected Width|string|null $maxWidth = Width::Full;

    public function hasLogo(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Connexion à Ktontine';
    }

    protected function isUserAllowedToAccessPanel(Authenticatable $user): bool
    {
        if ($user instanceof User && $user->tenant_id === null && ! $user->is_active) {
            Notification::make()
                ->title('Votre compte est désactivé')
                ->body('Votre accès à l’administration est suspendu. Contactez un administrateur pour demander sa réactivation.')
                ->danger()
                ->duration(8000)
                ->send();

            return false;
        }

        return parent::isUserAllowedToAccessPanel($user);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Adresse e-mail')
            ->placeholder('vous@exemple.com')
            ->prefixIcon('heroicon-o-envelope')
            ->email()
            ->required()
            ->autocomplete('email')
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Mot de passe')
            ->placeholder('Votre mot de passe')
            ->prefixIcon('heroicon-o-lock-closed')
            ->password()
            ->revealable()
            ->autocomplete('current-password')
            ->required();
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Rester connecté');
    }
}
