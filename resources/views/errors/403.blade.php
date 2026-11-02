<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Accès réservé — Ktontine</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 28px 18px; background: #f3f4f6; color: #221b16; font-family: Arial, Helvetica, sans-serif; }
        main { width: 100%; max-width: 680px; padding: clamp(28px, 6vw, 56px); border: 1px solid #eadfd6; border-radius: 28px; background: #fff; box-shadow: 0 24px 70px #24180c0d; }
        .brand { font-size: 24px; font-weight: 800; letter-spacing: -1px; text-decoration: none; color: #221b16; }
        .brand span { color: #c2410c; }
        .icon { display: grid; place-items: center; width: 72px; height: 72px; margin-top: 36px; border-radius: 22px; background: #fff0e3; color: #c2410c; }
        .label { margin: 24px 0 12px; font-size: 12px; font-weight: bold; letter-spacing: 2px; color: #c2410c; }
        h1 { margin: 0 0 18px; font-size: clamp(28px, 5vw, 40px); line-height: 1.15; letter-spacing: -1px; }
        p { margin: 0 0 16px; color: #6b5d52; font-size: 16px; line-height: 1.75; }
        .info { padding: 16px 20px; border: 1px solid #fed7aa; border-radius: 14px; background: #fff7ed; font-size: 14px; color: #854322; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
        .button { display: inline-block; padding: 14px 20px; border: 1px solid #c2410c; border-radius: 12px; background: #c2410c; color: #fff; font-size: 14px; font-weight: bold; text-decoration: none; line-height: 1.5; }
        .secondary { border-color: #e5ddd5; background: #fff; color: #44362b; }
        .button:hover { background: #9a3412; color: #fff; }
        a:focus-visible { outline: 3px solid #f97316; outline-offset: 4px; }
        footer { margin-top: 28px; font-size: 12px; color: #8c7a6b; }
    </style>
</head>
<body>
    <main aria-labelledby="error-title">
        <a class="brand" href="{{ url('/') }}">K<span>tontine</span></a>
        <div class="icon" aria-hidden="true"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg></div>
        <p class="label">403 · ACCÈS RÉSERVÉ</p>
        @if (request()->is('workspace', 'workspace/*'))
            <h1 id="error-title">Cet espace est réservé à l’administration.</h1>
            <p>Vous êtes dans l’espace de gestion de la plateforme Ktontine. Il est accessible uniquement aux administrateurs de la plateforme.</p>
            <p class="info">Pour gérer vos clients, vos tontines et vos cotisations, retrouvez votre tableau de bord habituel.</p>
            <div class="actions">
                @auth
                    @if (auth()->user()->is_active && auth()->user()->tenant_id !== null)
                        <a class="button" href="{{ route('dashboard') }}">Revenir à mon tableau de bord</a>
                    @else
                        <a class="button" href="{{ route('login') }}">Aller à la connexion</a>
                    @endif
                @else
                    <a class="button" href="{{ route('login') }}">Me connecter</a>
                @endauth
                <a class="button secondary" href="{{ url('/') }}">Retour à l’accueil</a>
            </div>
        @else
            <h1 id="error-title">Cette page ne vous est pas accessible.</h1>
            <p>Votre compte ne dispose pas des autorisations nécessaires, ou l’accès à votre espace a été désactivé. Contactez le responsable de votre espace si vous pensez qu’il s’agit d’une erreur.</p>
            <div class="actions"><a class="button" href="{{ url('/') }}">Retour à l’accueil</a></div>
        @endif
        <footer>Ktontine · Votre activité, en toute simplicité.</footer>
    </main>
</body>
</html>
