@section('auth-title')
    Mot de passe oublié — Ktontine
@endsection

@section('auth-brand-message')
    Retrouvez votre accès en quelques étapes.
@endsection

<x-layouts.auth-layout>
    <div class="card reset-card">
        <style>
            .reset-card .steps {
                display: flex;
                align-items: center;
                gap: 0.65rem;
                margin: 0 0 1.1rem;
            }

            .reset-card .step {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                color: #7a8795;
                font-size: 0.72rem;
                font-weight: 700;
            }

            .reset-card .step-number {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1.55rem;
                height: 1.55rem;
                border: 1px solid #d7dee5;
                border-radius: 50%;
                background: #fff;
            }

            .reset-card .step.is-active {
                color: #9a3c06;
            }

            .reset-card .step.is-active .step-number {
                border-color: #d66016;
                background: #fff0e0;
            }

            .reset-card .step-line {
                width: 2.2rem;
                height: 1px;
                background: #dce2e8;
            }

            .reset-card .input-control {
                position: relative;
            }

            .reset-card .input-control input {
                padding-left: 2.8rem;
            }

            .reset-card .input-icon {
                position: absolute;
                top: 50%;
                left: 0.9rem;
                color: #6a7785;
                pointer-events: none;
                transform: translateY(-50%);
            }

            .reset-card .input-control:focus-within .input-icon {
                color: #9a3c06;
            }

            .reset-card .code-grid {
                display: grid;
                grid-template-columns: repeat(6, minmax(0, 1fr));
                gap: 0.55rem;
            }

            .reset-card .code-grid input {
                height: 3.35rem;
                padding: 0;
                text-align: center;
                font-size: 1.25rem;
                font-weight: 800;
            }

            .reset-card .code-help {
                margin: 0.7rem 0 0;
                color: #6a7785;
                font-size: 0.75rem;
                text-align: center;
            }

            .reset-card .mail-preview {
                display: flex;
                align-items: center;
                gap: 0.7rem;
                margin-bottom: 1rem;
                padding: 0.75rem;
                border: 1px solid #ead3b9;
                border-radius: 0.75rem;
                background: #fff4e8;
                color: #80410c;
                font-size: 0.78rem;
            }

            .reset-card .text-button {
                display: block;
                margin: 0.8rem auto 0;
                padding: 0.35rem;
                border: 0;
                background: transparent;
                color: #9a3c06;
                font-size: 0.78rem;
                font-weight: 700;
                cursor: pointer;
            }

            @media (max-width: 380px) {
                .reset-card .code-grid {
                    gap: 0.35rem;
                }

                .reset-card .code-grid input {
                    height: 3rem;
                    font-size: 1.05rem;
                }
            }
        </style>

        <a class="back" href="{{ route('login') }}">← Retour à la connexion</a>

        <div class="steps" aria-label="Étapes de récupération">
            <span class="step is-active">
                <span class="step-number">1</span>
                E-mail
            </span>
            <span class="step-line" aria-hidden="true"></span>
            <span class="step">
                <span class="step-number">2</span>
                Code
            </span>
        </div>

        <section data-reset-step="email">
            <div class="eyebrow">Récupération du compte</div>
            <h2>Mot de passe oublié ?</h2>
            <p class="subtitle">
                Indiquez l’adresse e-mail liée à votre compte. Vous recevrez ensuite un code de vérification.
            </p>

            <form>
                <div class="field">
                    <label for="reset-email">
                        Adresse e-mail <span class="required" aria-hidden="true">*</span>
                    </label>
                    <div class="input-control">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="20" height="16" x="2" y="4" rx="2" />
                            <path d="m22 7-8.97 5.7a1.9 1.9 0 0 1-2.06 0L2 7" />
                        </svg>
                        <input id="reset-email" name="email" type="email" autocomplete="email"
                            placeholder="vous@exemple.com">
                    </div>
                </div>

                <button type="button" class="submit">Recevoir le code →</button>
            </form>

            <p class="other">
                Vous vous souvenez du mot de passe ?
                <a class="accent" href="{{ route('login') }}">Se connecter</a>
            </p>
        </section>

        {{-- Template de la seconde étape. Le futur comportement retirera l’attribut hidden. --}}
        <section data-reset-step="code" hidden>
            <div class="eyebrow">Vérification par e-mail</div>
            <h2>Entrez votre code</h2>
            <p class="subtitle">
                Saisissez les six chiffres envoyés à votre adresse e-mail.
            </p>

            <div class="mail-preview">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <rect width="20" height="16" x="2" y="4" rx="2" />
                    <path d="m22 7-8.97 5.7a1.9 1.9 0 0 1-2.06 0L2 7" />
                </svg>
                <span>Code envoyé à votre adresse e-mail</span>
            </div>

            <form>
                <div class="field">
                    <label for="reset-code-1">
                        Code de vérification <span class="required" aria-hidden="true">*</span>
                    </label>
                    <div class="code-grid" aria-label="Code de vérification à six chiffres">
                        <input id="reset-code-1" name="code[]" type="text" inputmode="numeric" maxlength="1"
                            autocomplete="one-time-code" aria-label="Premier chiffre">
                        <input name="code[]" type="text" inputmode="numeric" maxlength="1"
                            aria-label="Deuxième chiffre">
                        <input name="code[]" type="text" inputmode="numeric" maxlength="1"
                            aria-label="Troisième chiffre">
                        <input name="code[]" type="text" inputmode="numeric" maxlength="1"
                            aria-label="Quatrième chiffre">
                        <input name="code[]" type="text" inputmode="numeric" maxlength="1"
                            aria-label="Cinquième chiffre">
                        <input name="code[]" type="text" inputmode="numeric" maxlength="1"
                            aria-label="Sixième chiffre">
                    </div>
                    <p class="code-help">Le code contient six chiffres.</p>
                </div>

                <button type="button" class="submit">Vérifier le code →</button>
                <button type="button" class="text-button">Renvoyer le code</button>
            </form>
        </section>

        <div class="note">Ktontine · Récupération sécurisée de votre accès</div>
    </div>
</x-layouts.auth-layout>
