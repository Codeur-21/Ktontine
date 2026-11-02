<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public function mount(): void
    {
        $user = $this->currentUser();
        $this->fill(['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? '']);
    }

    public function cancelProfile(): void
    {
        $this->mount();
        $this->resetValidation(['name', 'email', 'phone']);
    }

    public function saveProfile(): void
    {
        $user = $this->currentUser();
        $this->name = trim($this->name);
        $this->email = trim($this->email);
        $this->phone = trim($this->phone);
        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'digits:9', Rule::unique('users', 'phone')->ignore($user->id)],
        ], [
            'name.required' => 'Indiquez votre nom complet.',
            'name.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'email.required' => 'Indiquez votre adresse e-mail.',
            'email.email' => 'Indiquez une adresse e-mail valide.',
            'email.max' => 'L’adresse e-mail ne doit pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'phone.required' => 'Indiquez votre numéro de téléphone.',
            'phone.digits' => 'Le téléphone doit contenir 9 chiffres.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
        ]);
        $user->fill($data);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();
        session()->flash('profileSuccess', 'Votre profil a été enregistré avec succès.');
        $this->redirectRoute('profile');
    }

    private function currentUser(): User
    {
        abort_unless(auth()->check(), 403);
        $user = auth()->user()->fresh();
        abort_unless($user !== null, 403);
        $user->activeTenantId();

        return $user;
    }

    public function render(): View
    {
        return view('livewire.profile')->layout('components.layouts.backend');
    }
}
