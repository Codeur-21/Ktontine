<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $loginType = 'email';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        // Déterminer le champ utilisé pour la connexion.
        $field = $this->loginType === 'phone' ? 'phone' : 'email';

        // Adapter les règles selon le type de connexion.
        $data = $this->validate([
            'loginType' => ['required', Rule::in(['email', 'phone'])],

            'email' => [Rule::requiredIf($this->loginType === 'email'), 'nullable', 'email'],
            'phone' => [Rule::requiredIf($this->loginType === 'phone'), 'nullable', 'digits:9'],

            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ], [
            'email.required' => 'Veuillez renseigner votre adresse e-mail.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',

            'phone.required' => 'Veuillez renseigner votre numéro de téléphone.',
            'phone.digits' => 'Le numéro doit contenir exactement 9 chiffres.',

            'password.required' => 'Veuillez renseigner votre mot de passe.',
            'password.string' => 'Le mot de passe doit être un texte valide.',
        ]);

        // Identifier la valeur sélectionnée.
        $identifier = $data[$field];
        // Construire une clé de limitation des connexions.
        $key = 'login:'.hash('sha256', $field.'|'.Str::lower($identifier).'|'.request()->ip()
        );

        // Bloquer temporairement les tentatives excessives.
        if (RateLimiter::tooManyAttempts($key, 5)) {
            // le de seoncde atteendues avant ressayage
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                $field => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        // Vérifier les identifiants et gérer Remember Me.
        if (Auth::attempt([
            $field => $identifier,
            'password' => $data['password'],
            'is_active' => true,
            fn (Builder $query): Builder => $query->whereHas('tenant', fn (Builder $tenant): Builder => $tenant->where('is_active', true)),
        ], $data['remember'])) {

            // Effacer le compteur après une connexion réussie.
            RateLimiter::clear($key);

            // Régénérer la session pour renforcer la sécurité.
            session()->regenerate();

            // Rediriger vers le tableau de bord.
            return $this->redirectRoute('dashboard', navigate: true);
        }

        // Comptabiliser une tentative échouée.
        RateLimiter::hit($key, 60);

        $user = User::query()->with('tenant')->where($field, $identifier)->first();
        if ($user !== null && $user->tenant_id !== null
            && (! $user->is_active || ! $user->tenant?->is_active)
            && Auth::getProvider()->validateCredentials($user, ['password' => $data['password']])) {
            $this->reset('password');
            $this->dispatch('account-inactive');
            $this->addError($field, 'Votre compte ou votre espace est actuellement désactivé. Contactez le support.');

            return;
        }

        // Afficher un message générique de sécurité.
        $this->addError(
            $field,
            'Informations de connexion incorrectes.'
        );
    }

    public function render(): mixed
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.auth-layout');
    }
}
