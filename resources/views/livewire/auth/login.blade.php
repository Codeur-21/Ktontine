@section('auth-title')
    Connexion — Ktontine
@endsection

@section('auth-brand-message')
    Reprenez votre journée en toute clarté.
@endsection

<div class="card login-card">

    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes toast-countdown {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        .toast-progress {
            animation: toast-countdown 5s linear forwards;
        }

        .login-card .field-error {
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

        .login-card .field-error::before {
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

        .login-card input[aria-invalid="true"] {
            border-color: #ef4444 !important;
            background-color: #fff7f7 !important;
            box-shadow: 0 0 0 3px rgb(239 68 68 / 10%);
        }

        .login-card input[aria-invalid="true"]:focus {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgb(239 68 68 / 18%);
        }

        .login-card .validation-summary {
            display: flex;
            gap: 0.75rem;
            margin: 0 0 1rem;
            padding: 0.9rem 1rem;
            border: 1px solid #fecaca;
            border-radius: 12px;
            background: #fff1f2;
            color: #991b1b;
        }

        .login-card .validation-summary-icon {
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

        .login-card .validation-summary strong {
            display: block;
            margin-bottom: 0.2rem;
            font-size: 0.85rem;
        }

        .login-card .validation-summary p {
            margin: 0;
            color: #b91c1c;
            font-size: 0.78rem;
            line-height: 1.45;
        }

        .login-card .password-control {
            position: relative;
        }

        .login-card .password-control input {
            padding-right: 3rem;
        }

        .login-card .password-eye {
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

        .login-card .password-eye:hover {
            background: #fff4e8;
            color: #9a3c06;
        }

        .login-card .login-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.35rem;
            margin: 0 0 1rem;
            padding: 0.3rem;
            border: 1px solid #e2d6ca;
            border-radius: 0.85rem;
            background: #fff4e8;
        }

        .login-card .login-method {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-width: 0;
            padding: 0.7rem 0.65rem;
            border: 0;
            border-radius: 0.65rem;
            background: transparent;
            color: #6a7785;
            font-size: 0.76rem;
            font-weight: 700;
            line-height: 1.3;
            cursor: pointer;
        }

        .login-card .login-method.is-active {
            background: #ffffff;
            color: #9a3c06;
            box-shadow: 0 4px 14px rgb(98 53 11 / 10%);
        }

        .login-card .login-method:hover {
            color: #9a3c06;
        }

        .login-card .input-control {
            position: relative;
        }

        .login-card .input-control input {
            padding-left: 2.8rem;
        }

        .login-card .input-icon {
            position: absolute;
            top: 50%;
            left: 0.9rem;
            z-index: 1;
            color: #6a7785;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .login-card .input-control:focus-within .input-icon {
            color: #9a3c06;
        }
.inactive-account-dialog{box-sizing:border-box;width:min(440px,calc(100vw - 32px));max-height:calc(100dvh - 32px);overflow:auto;margin:auto;padding:30px;border:1px solid #f0d7c3;border-radius:22px;background:#fffdfb;color:#302318;box-shadow:0 24px 80px #24130930;text-align:center}.inactive-account-dialog::backdrop{background:#191512aa;backdrop-filter:blur(5px)}.inactive-account-dialog .inactive-account-icon{display:grid;place-items:center;width:60px;height:60px;margin:0 auto 20px;border-radius:18px;background:#fff0e2;color:#c64b08}.inactive-account-dialog .inactive-account-label{font-size:10px;letter-spacing:1.6px;font-weight:700;color:#a7460b;margin:0 0 12px}.inactive-account-dialog h2{font-size:23px;line-height:1.3;color:#302318;margin:0 0 16px}.inactive-account-dialog #inactive-account-description{font-size:14px;line-height:1.8;color:#806b5b}.inactive-account-dialog form{margin:24px 0 0}.inactive-account-dialog button{width:100%;padding:13px 18px;border:0;border-radius:10px;background:#c64b08;color:white;font:inherit;font-weight:700;cursor:pointer}.inactive-account-dialog button:focus-visible{outline:3px solid #fb923c;outline-offset:3px}
    </style><a class="back" href="/">← Retour à l’accueil</a>

    <div class="eyebrow">Votre espace tontinier</div>

    <h2>Heureux de vous retrouver</h2>

    <p class="subtitle">Connectez-vous pour retrouver vos clients et le suivi de vos tontines.</p>

    @if (session()->has('success'))
        <div x-data="{ show: true, timer: null }" x-init="timer = setTimeout(() => show = false, 5000)" x-show="show" x-cloak
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-8"
            class="fixed right-4 top-4 z-[9999] w-[calc(100%-2rem)] max-w-sm overflow-hidden rounded-2xl border border-orange-200 bg-white shadow-2xl sm:right-6 sm:top-6"
            role="status" aria-live="polite">

            <div class="flex items-start gap-3 p-4">

                {{-- Icône orange --}}

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600">

                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">

                        <circle cx="12" cy="12" r="10" />

                        <path d="m8 12 3 3 5-6" />

                    </svg>

                </div>

                {{-- Contenu --}}

                <div class="min-w-0 flex-1">

                    <h3 class="text-sm font-bold text-neutral-950">

                        Opération réussie !

                    </h3>

                    <p class="mt-1 text-sm leading-relaxed text-neutral-600">

                        {{ session('success') }}

                    </p>

                    <span class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-orange-600">

                        <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>

                        Ktontine

                    </span>

                </div>

                {{-- Fermeture --}}

                <button type="button" x-on:click="clearTimeout(timer); show = false"
                    class="rounded-lg p-1.5 text-neutral-400 transition-colors duration-200 hover:bg-orange-50 hover:text-orange-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500"
                    aria-label="Fermer la notification">

                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">

                        <path d="M18 6 6 18M6 6l12 12" />

                    </svg>

                </button>

            </div>

            {{-- Barre de progression --}}

            <div class="h-1 w-full bg-orange-100">

                <div class="toast-progress h-full bg-orange-500"></div>

            </div>

        </div>
    @endif

    @if ($errors->any())
        <div class="validation-summary" role="alert" aria-live="assertive">

            <span class="validation-summary-icon" aria-hidden="true">!</span>

            <div>

                <strong>Impossible de vous connecter.</strong>

                <p>Vérifiez les champs signalés ci-dessous, puis essayez à nouveau.</p>

            </div>

        </div>
    @endif

    <form wire:submit="login" novalidate>

        <div x-data="{ loginType: $wire.entangle('loginType') }">

            <div class="login-methods" role="group" aria-label="Méthode de connexion">

                <button @click="loginType = 'email'" :class="{ 'is-active': loginType === 'email' }"
                    class="login-method" type="button" aria-label="Connexion par e-mail"
                    :aria-pressed="loginType === 'email'" data-login-mode="email">

                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">

                        <rect width="20" height="16" x="2" y="4" rx="2" />

                        <path d="m22 7-8.97 5.7a1.9 1.9 0 0 1-2.06 0L2 7" />

                    </svg>

                </button>

                <button @click="loginType = 'phone'" :class="{ 'is-active': loginType === 'phone' }"
                    class="login-method" type="button" aria-label="Connexion par téléphone"
                    :aria-pressed="loginType === 'phone'" data-login-mode="phone">

                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">

                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92Z" />

                    </svg>

                </button>

            </div>

            <div class="field" x-show="loginType === 'email'" x-cloak>

                <label for="email">Adresse e-mail <span class="required" aria-hidden="true">*</span></label>

                <div class="input-control">

                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">

                        <rect width="20" height="16" x="2" y="4" rx="2" />

                        <path d="m22 7-8.97 5.7a1.9 1.9 0 0 1-2.06 0L2 7" />

                    </svg>

                    <input id="email" wire:model.blur="email" type="email" placeholder="vous@exemple.com"
                        autocomplete="username" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        @if ($errors->has('email')) aria-describedby="email-error" @endif>

                </div>

                @error('email')
                    <p id="email-error" class="field-error" role="alert">{{ $message }}</p>
                @enderror

            </div>

            {{-- Téléphone login --}}

            <div class="field" x-show="loginType === 'phone'" x-cloak>

                <label for="phone">Téléphone <span class="required" aria-hidden="true">*</span></label>

                <div class="input-control">

                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">

                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92Z" />

                    </svg>

                    <input id="phone" wire:model.blur="phone" type="tel" placeholder="Ex. 620000000"
                        autocomplete="tel-national" inputmode="numeric"
                        aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                        @if ($errors->has('phone')) aria-describedby="phone-error" @endif>

                </div>

                @error('phone')
                    <p id="phone-error" class="field-error" role="alert">{{ $message }}</p>
                @enderror

            </div>

        </div>

        {{-- password login --}}

        <div class="field">

            <label for="password">Mot de passe <span class="required" aria-hidden="true">*</span>

            </label>

            <div x-data="{ showPwd: false }" class="password-control">

                <input id="password" wire:model="password" type="password" :type="showPwd ? 'text' : 'password'"
                    placeholder="Votre mot de passe" autocomplete="current-password"
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    @if ($errors->has('password')) aria-describedby="password-error" @endif>

                <button class="password-eye" @click="showPwd=!showPwd" type="button"
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

            @error('password')
                <p id="password-error" class="field-error" role="alert">{{ $message }}</p>
            @enderror

        </div>

        <div class="options">

            <label class="checkbox"><input type="checkbox" wire:model="remember"><span>Se souvenir de

                    moi</span></label>

        </div>

        <button class="submit flex items-center justify-center gap-2 disabled:cursor-not-allowed disabled:opacity-60"
            type="submit" wire:loading.attr="disabled" wire:target="login">

            <span wire:loading.remove wire:target="login">Se connecter →</span>

            <span wire:loading.flex wire:target="login" class="items-center justify-center gap-2">

                <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" aria-hidden="true">

                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4" />

                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z" />

                </svg>

                Connexion en cours...

            </span>

        </button>

    </form>

    <p class="other">Vous découvrez Ktontine ? <a class="accent" href="{{ route('register') }}">Créer un compte</a>

    </p>

    <div class="note">Ensemble aujourd’hui, plus forts demain.</div>

    <dialog class="inactive-account-dialog" wire:ignore.self x-data @account-inactive.window="$el.showModal()" aria-labelledby="inactive-account-title" aria-describedby="inactive-account-description">
        <div class="inactive-account-icon" aria-hidden="true"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg></div>
        <p class="inactive-account-label">ACCÈS À VOTRE ESPACE</p>
        <h2 id="inactive-account-title">Votre accès est désactivé</h2>
        <p id="inactive-account-description">Votre compte ou votre espace tontinier est actuellement inactif. Contactez le support Ktontine pour connaître la raison et les démarches de réactivation.</p>
        <form method="dialog"><button type="submit" autofocus>J’ai compris</button></form>
    </dialog>
</div>
