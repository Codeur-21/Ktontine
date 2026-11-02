<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#151311">
    <title>@yield('auth-title', 'Ktontine')</title>
    <link rel="icon" href="{{ $platformLogoUrl }}">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/ktontine-brand.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ktontine-auth.css') }}">
</head>
<body class="kt-auth {{ request()->routeIs('login', 'register') ? 'auth-fixed' : '' }}" x-data="{ guide: 'Je vous accompagne. Commencez par remplir le premier champ.' }" x-on:auth-guide.window="guide = $event.detail">
    <div class="auth-shell">
        <aside class="auth-story" aria-label="Bienvenue sur Ktontine">
            <a class="auth-identity" href="/" aria-label="Ktontine, accueil">
                <img src="{{ $platformLogoUrl }}" alt="" width="48" height="48">
                <span><b>Ktontine<span>.</span></b><small>Ensemble, chaque jour compte.</small></span>
            </a>
            <div class="auth-mascot-guide">
                <div class="auth-guide-bubble"><span class="auth-kicker">VOTRE GUIDE KTONTINE</span><p x-text="guide" aria-live="polite">Je vous accompagne. Commencez par remplir le premier champ.</p></div>
                <div class="auth-mascot-scene">
                    <img class="auth-guide-mascot" src="{{ asset('ktontine-mascotte-v1.png') }}" alt="La mascotte Ktontine vous accompagne" width="1024" height="1536">
                    <div class="auth-side-note auth-note-left">
                        <span class="auth-note-number" aria-hidden="true">01</span>
                        <strong>{{ request()->routeIs('register') ? 'Présentez votre activité' : 'Vos identifiants' }}</strong>
                        <p>{{ request()->routeIs('register') ? 'Votre nom, votre activité, puis vos coordonnées.' : 'Votre e-mail ou votre téléphone, puis votre mot de passe.' }}</p>
                    </div>
                    <div class="auth-side-note auth-note-right">
                        <span class="auth-note-number" aria-hidden="true">02</span>
                        <strong>{{ request()->routeIs('register') ? 'Suivez les 3 étapes' : 'Retrouvez votre espace' }}</strong>
                        <p>{{ request()->routeIs('register') ? 'Utilisez « Continuer ». Vous pouvez revenir en arrière.' : 'Cliquez sur « Se connecter » pour reprendre votre suivi.' }}</p>
                    </div>
                </div>
            </div>
            <p class="auth-story-footer">@yield('auth-brand-message', 'Pensé pour les tontiniers de Guinée.')<br>Mobile et ordinateur, au même rythme.</p>
        </aside>
        <main class="main" id="contenu">
            <a class="auth-identity auth-mobile-brand" href="/" aria-label="Ktontine, accueil"><img src="{{ $platformLogoUrl }}" alt="" width="42" height="42"><span><b>Ktontine<span>.</span></b><small>Ensemble, chaque jour compte.</small></span></a>
            <div class="auth-mobile-guide"><img src="{{ asset('ktontine-mascotte-v1.png') }}" alt="" width="44" height="52"><p x-text="guide">Je vous accompagne. Remplissez le premier champ.</p></div>
            {{ $slot }}
        </main>
    </div>
    <script>
        document.addEventListener('focusin', event => {
            const field = event.target;
            if (!(field instanceof HTMLInputElement)) { return; }
            const messages = {
                name: 'Indiquez votre prénom et votre nom pour personnaliser votre espace.',
                business_name: 'Donnez un nom à votre activité de tontine.',
                email: 'Saisissez votre adresse e-mail, par exemple vous@exemple.com.',
                phone: 'Entrez votre numéro guinéen : 9 chiffres, sans le +224.',
                password: document.querySelector('.register-card') ? 'Créez un mot de passe : 8 caractères minimum, avec majuscule, minuscule, chiffre et symbole.' : 'Saisissez votre mot de passe, puis cliquez sur Se connecter.',
                password_confirmation: 'Retapez le même mot de passe pour le confirmer.',
            };
            const message = messages[field.id] || (field.type === 'checkbox' && field.getAttribute('wire:model') === 'terms' ? 'Lisez les conditions, puis cochez la case si vous les acceptez.' : null);
            if (message) { window.dispatchEvent(new CustomEvent('auth-guide', { detail: message })); }
        });
    </script>
    @livewireScripts
</body>
</html>