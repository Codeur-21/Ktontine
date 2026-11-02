<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

class UserProfile extends Page
{
    use WithFileUploads;

    protected static ?string $slug = 'mon-profil';

    protected static ?string $title = 'Mon profil';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Mon profil';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.user-profile';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $currentPassword = '';

    public string $password = '';

    public string $password_confirmation = '';

    public mixed $avatar = null;

    public function mount(): void
    {
        Gate::authorize('view-platform-administration');
        $user = auth()->user();
        $this->fill(['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? '']);
    }

    public function saveProfile(): void
    {
        Gate::authorize('view-platform-administration');
        $user = auth()->user();
        $this->name = trim($this->name);
        $this->email = trim($this->email);
        $this->phone = trim($this->phone);
        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'digits:9', Rule::unique('users', 'phone')->ignore($user->id)],
        ], [
            'name.required' => 'Indiquez votre nom complet.',
            'email.required' => 'Indiquez votre adresse e-mail.',
            'email.email' => 'Indiquez une adresse e-mail valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'phone.digits' => 'Le téléphone doit contenir 9 chiffres.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
        ]);
        $user->fill([...$data, 'phone' => $data['phone'] ?: null]);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();
        Notification::make()->title('Profil enregistré')->success()->send();
        $this->redirect(static::getUrl());
    }

    public function saveAvatar(): void
    {
        Gate::authorize('view-platform-administration');
        $this->validate(['avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']], [
            'avatar.required' => 'Choisissez une photo.',
            'avatar.image' => 'Choisissez une image valide.',
            'avatar.mimes' => 'Utilisez une image PNG, JPG ou WebP.',
            'avatar.max' => 'La photo ne doit pas dépasser 2 Mo.',
        ]);
        $path = $this->avatar->storePublicly('avatars/'.auth()->id(), 'public');
        if (! $path) {
            throw ValidationException::withMessages(['avatar' => 'Impossible d’enregistrer la photo. Réessayez.']);
        }
        auth()->user()->forceFill(['avatar_path' => $path])->save();
        $this->reset('avatar');
        Notification::make()->title('Photo de profil enregistrée')->success()->send();
        $this->redirect(static::getUrl());
    }

    public function removeAvatar(): void
    {
        Gate::authorize('view-platform-administration');
        auth()->user()->forceFill(['avatar_path' => null])->save();
        $this->reset('avatar');
        Notification::make()->title('Avatar par défaut restauré')->success()->send();
        $this->redirect(static::getUrl());
    }

    public function cancelProfile(): void
    {
        $this->mount();
        $this->resetValidation(['name', 'email', 'phone']);
    }

    public function cancelPassword(): void
    {
        Gate::authorize('view-platform-administration');
        $this->reset('currentPassword', 'password', 'password_confirmation');
        $this->resetValidation(['currentPassword', 'password', 'password_confirmation']);
    }

    public function changePassword(): void
    {
        Gate::authorize('view-platform-administration');
        $key = 'workspace-password:'.auth()->id().'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->reset('currentPassword', 'password', 'password_confirmation');
            throw ValidationException::withMessages(['currentPassword' => 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($key).' secondes.']);
        }
        try {
            $data = $this->validate([
                'currentPassword' => ['required', 'current_password'],
                'password' => ['required', 'string', 'confirmed', 'different:currentPassword', Password::min(8)->mixedCase()->numbers()->symbols()],
            ], [
                'currentPassword.required' => 'Indiquez votre mot de passe actuel.',
                'currentPassword.current_password' => 'Le mot de passe actuel est incorrect.',
                'password.required' => 'Indiquez un nouveau mot de passe.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
                'password.different' => 'Choisissez un mot de passe différent de l’actuel.',
            ]);
        } catch (ValidationException $exception) {
            if (array_key_exists('currentPassword', $exception->errors())) {
                RateLimiter::hit($key, 60);
            }
            $this->reset('currentPassword', 'password', 'password_confirmation');
            throw $exception;
        }
        $user = auth()->user();
        DB::transaction(function () use ($user, $data): void {
            $user->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();
            DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', session()->getId())->delete();
        });
        RateLimiter::clear($key);
        session()->regenerate();
        session()->put('password_hash_web', $user->getAuthPassword());
        $this->cancelPassword();
        Notification::make()->title('Mot de passe modifié')->body('Vos autres sessions ont été déconnectées.')->success()->send();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isPlatformAdmin() ?? false;
    }
}
