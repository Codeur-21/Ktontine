<?php

namespace App\Livewire;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Connexion extends Component
{
    public $logType = 'email';

    public $email = '';

    public $password = '';

    public $phone = '';

    public bool $remember = false;

    public function save()
    {
        $defaulType = $this->logType === 'phone' ? 'phone' : 'email';

        $data = $this->validate([

            'logType' => ['required', Rule::in(['email', 'phone'])],

            'email' => [Rule::requiredIf($this->logType === 'email'), 'nullable', 'email'],
            'phone' => [Rule::requiredIf($this->logType === 'phone'), 'nullable', 'digits:9'],

            'password' => ['required', Password::min('9')->mixedCase()->symbols()->numbers()],

        ],
            [
                'email.required' => 'Veuillez renseigner votre adresse e-mail.',
                'email.email' => 'Veuillez saisir une adresse e-mail valide.',

                'phone.required' => 'Veuillez renseigner votre numéro de téléphone.',
                'phone.digits' => 'Le numéro doit contenir exactement 9 chiffres.',

                'password.required' => 'Veuillez renseigner votre mot de passe.',
                'password.string' => 'Le mot de passe doit être un texte valide.',
            ]);
        $mentientLogin = $this->remember;

        dd($data);

    }

    public function clearErrors()
    {
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.connexion')->layout('components.layouts.test');
    }
}
