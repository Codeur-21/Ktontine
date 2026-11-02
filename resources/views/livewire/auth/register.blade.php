@section('auth-title')
    Inscription — Ktontine
@endsection

@section('auth-brand-message')
    un mois pour découvrir votre nouvel espace.
@endsection

@section('auth-register', '1')

<div class="card register-card" wire:key="register-{{ implode('-', $errors->keys()) }}"
    x-data="{ step: {{ $errors->has('name') || $errors->has('business_name') ? 1 : ($errors->has('phone') || $errors->has('email') ? 2 : ($errors->any() ? 3 : 1)) }} }"
    x-effect="$dispatch('auth-guide', ['Présentez-vous : votre nom et celui de votre activité.', 'Ajoutez votre téléphone et votre adresse e-mail.', 'Sécurisez votre espace, puis créez votre compte.'][step - 1])">
    <style>
        .main {
            align-items: flex-start;
            overflow-y: auto;
        }

        .register-card {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            min-height: 0;
            margin-block: auto;
            padding-bottom: 1.5rem;
        }

        .register-card .register-form {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }

        .register-card .field,
        .register-card .row {
            height: auto !important;
            min-height: 0;
        }

        .register-card .row>.field {
            min-width: 0;
        }

        .register-card .submit {
            width: 100%;
            position: relative;
            flex-shrink: 0;
            margin-top: 1rem;
        }

        .register-card .field-error {
            display: flex;
            align-items: flex-start;
            gap: 0.45rem;
            margin: 0.45rem 0 0;
            padding: 0.55rem 0.7rem;
            border-left: 3px solid #dc2626;
            border-radius: 0 8px 8px 0;
            background: #fff1f2;
            color: #b91c1c;
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .register-card .field-error::before {
            content: '!';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 0.9rem;
            width: 0.9rem;
            height: 0.9rem;
            margin-top: 0.09rem;
            border: 1.5px solid currentColor;
            border-radius: 50%;
            font-size: 0.65rem;
            font-weight: 700;
        }

        .register-card input[aria-invalid="true"] {
            border-color: #ef4444 !important;
            background-color: #fff7f7 !important;
            box-shadow: 0 0 0 3px rgb(239 68 68 / 10%);
        }

        .register-card input[aria-invalid="true"]:focus {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgb(239 68 68 / 18%);
        }

        .register-card .validation-summary {
            display: flex;
            gap: 0.75rem;
            margin: 0 0 1rem;
            padding: 0.9rem 1rem;
            border: 1px solid #fecaca;
            border-radius: 12px;
            background: #fff1f2;
            color: #991b1b;
        }

        .register-card .validation-summary-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 1.5rem;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            background: #dc2626;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .register-card .validation-summary strong {
            display: block;
            margin-bottom: 0.2rem;
            font-size: 0.85rem;
        }

        .register-card .validation-summary p {
            margin: 0;
            color: #b91c1c;
            font-size: 0.78rem;
            line-height: 1.45;
        }

        @media (max-width: 640px) {
            .register-card .row {
                flex-wrap: wrap;
            }

            .register-card .row>.field {
                flex-basis: 100%;
            }
        }

        .register-card .password-control {
            position: relative;
        }

        .register-card .password-control input {
            padding-right: 3rem;
        }

        .register-card .password-eye {
            position: absolute;
            top: 50%;
            right: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            padding: 0;
            border: 0;
            border-radius: 0.5rem;
            background: transparent;
            color: #6a7785;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .register-card .password-eye:hover {
            background: #fff4e8;
            color: #9a3c06;
        }
    </style>
    <a class="back" href="/">← Retour à l’accueil</a>

    <div class="eyebrow">Votre activité commence ici</div>

    <h2>Créer votre espace</h2>
    <p class="subtitle">
        Inscrivez-vous et découvrez Ktontine pendant un mois gratuit.
    </p>

    @if ($errors->any())
        <div class="validation-summary" role="alert" aria-live="assertive">
            <span class="validation-summary-icon" aria-hidden="true">!</span>
            <div>
                <strong>Quelques informations sont à corriger.</strong>
                <p>
                    Vérifiez les champs signalés ci-dessous, puis essayez à nouveau.
                </p>
            </div>
        </div>
    @endif

    <ol class="auth-form-steps" aria-label="Étapes de l’inscription">
        <li :class="{ active: step === 1, complete: step > 1 }" :aria-current="step === 1 ? 'step' : null">1 · Activité</li>
        <li :class="{ active: step === 2, complete: step > 2 }" :aria-current="step === 2 ? 'step' : null">2 · Coordonnées</li>
        <li :class="{ active: step === 3 }" :aria-current="step === 3 ? 'step' : null">3 · Sécurité</li>
    </ol>
    <form class="register-form" wire:submit="register" novalidate
        @submit.capture="if (step < 3) { $event.preventDefault(); $event.stopImmediatePropagation(); step++; }">
        <fieldset x-show="step === 1" class="auth-step"><legend class="sr-only">Votre activité</legend>
        {{-- Nom complet --}}
        <div class="field">
            <label for="name">Nom complet <span class="required" aria-hidden="true">*</span></label>
            <input id="name" type="text" wire:model.blur="name" placeholder="Votre prénom et votre nom"
                autocomplete="name" maxlength="25" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                @if ($errors->has('name')) aria-describedby="name-error" @endif>
            @error('name')
                <p id="name-error" class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nom de l'activité --}}
        <div class="field">
            <label for="business_name">Nom de votre activité <span class="required" aria-hidden="true">*</span></label>
            <input id="business_name" type="text" wire:model.blur="business_name"
                placeholder="Ex. Tontine de Mamadou" autocomplete="organization" maxlength="25"
                aria-invalid="{{ $errors->has('business_name') ? 'true' : 'false' }}"
                @if ($errors->has('business_name')) aria-describedby="business_name-error" @endif>
            @error('business_name')
                <p id="business_name-error" class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        </fieldset>
        <fieldset x-show="step === 2" x-cloak class="auth-step"><legend class="sr-only">Vos coordonnées</legend>
        {{-- Téléphone et adresse e-mail --}}
        <div class="row">
            <div class="field">
                <label for="phone">Téléphone <span class="required" aria-hidden="true">*</span></label>
                <input id="phone" type="tel" wire:model.blur="phone" placeholder="Ex. 620000000"
                    autocomplete="tel-national" inputmode="numeric" maxlength="9"
                    aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                    @if ($errors->has('phone')) aria-describedby="phone-error" @endif>
                @error('phone')
                    <p id="phone-error" class="field-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="email">Adresse e-mail <span class="required" aria-hidden="true">*</span></label>
                <input id="email" type="email" wire:model.blur="email" placeholder="vous@exemple.com"
                    autocomplete="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    @if ($errors->has('email')) aria-describedby="email-error" @endif>
                @error('email')
                    <p id="email-error" class="field-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>

        </fieldset>
        <fieldset x-show="step === 3" x-cloak class="auth-step"><legend class="sr-only">Sécuriser votre compte</legend>
        {{-- Mot de passe --}}
        <div class="field">
            <label for="password">Mot de passe <span class="required" aria-hidden="true">*</span></label>

            <div x-data="{ showPwd: false }" class="password-control">

                <input id="password" type="password" :type="showPwd ? 'text' : 'password'" wire:model="password"
                    placeholder="Choisissez un mot de passe sécurisé" autocomplete="new-password"
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" @if ($errors->has('password'))
                aria-describedby="password-hint password-error"
            @else
                aria-describedby="password-hint"
                @endif>

                <button @click="showPwd=!showPwd" class="password-eye" type="button"
                    aria-label="Afficher le mot de passe" aria-controls="password">

                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path
                            d="M2.06 12.35a1 1 0 0 1 0-.7C3.48 8.14 7.1 5.5 12 5.5s8.52 2.64 9.94 6.15a1 1 0 0 1 0 .7C20.52 15.86 16.9 18.5 12 18.5s-8.52-2.64-9.94-6.15Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </button>
            </div>
            <p id="password-hint" class="mt-1 text-xs text-gray-500">
                8 caractères minimum, majuscule, minuscule, chiffre et caractère spécial.
            </p>
            @error('password')
                <p id="password-error" class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirmation --}}
        <div x-data="{ showPwd: false }" class="field">

            <label for="password_confirmation">Confirmer le mot de passe <span class="required"
                    aria-hidden="true">*</span></label>
            <div class="password-control">

                <input id="password_confirmation" type="password" :type="showPwd ? 'text' : 'password'"
                    wire:model="password_confirmation" placeholder="Saisissez à nouveau le mot de passe"
                    autocomplete="new-password"
                    aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}" @if ($errors->has('password_confirmation'))
                aria-describedby="password_confirmation-error"
                @endif>
                <button @click="showPwd=!showPwd" class="password-eye" type="button"
                    aria-label="Afficher le mot de passe" aria-controls="password_confirmation">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path
                            d="M2.06 12.35a1 1 0 0 1 0-.7C3.48 8.14 7.1 5.5 12 5.5s8.52 2.64 9.94 6.15a1 1 0 0 1 0 .7C20.52 15.86 16.9 18.5 12 18.5s-8.52-2.64-9.94-6.15Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </button>
            </div>
            @error('password_confirmation')
                <p id="password_confirmation-error" class="field-error" role="alert">{{ $message }}
                </p>
            @enderror
        </div>

        {{-- Conditions d'utilisation --}}
        <div>
            <label class="checkbox">
                <input type="checkbox" wire:model="terms"
                    aria-invalid="{{ $errors->has('terms') ? 'true' : 'false' }}"
                    @if ($errors->has('terms')) aria-describedby="terms-error" @endif>
                <span>
                    J’accepte les <a href="#" class="accent">conditions d’utilisation</a>
                    et la <a href="#" class="accent">politique de confidentialité</a>.
                </span>
            </label>
            @error('terms')
                <p id="terms-error" class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        </fieldset>
        <div class="auth-step-actions">
            <button type="button" class="auth-previous" x-show="step > 1" x-cloak @click="step--">← Retour</button>
            <button type="button" class="submit auth-next" x-show="step < 3" @click="step++">Continuer →</button>
        <button x-show="step === 3" x-cloak type="submit" wire:loading.attr="disabled" wire:target="register"
            class="submit flex items-center justify-center gap-2
               disabled:cursor-not-allowed disabled:opacity-60">
            <span wire:loading.remove wire:target="register">
                Créer mon compte →
            </span>

            <span wire:loading.flex wire:target="register" class="items-center justify-center gap-2">

                <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>

                Création de votre espace...
            </span>
        </button>

        </div>
    </form>

    <p class="other">Vous avez déjà un compte ? <a class="accent" href="{{ route('login') }}">Se connecter</a>
    </p>
    <div class="note">Un mois gratuit · Puis 200 000 GNF / an</div>
</div>
