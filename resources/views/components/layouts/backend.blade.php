<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @PwaHead
    <link rel="icon" href="{{ $platformLogoUrl }}">
    <title>Ktontine — Mon espace</title>
    @livewireStyles
    <style>

        *{box-sizing:border-box}body{margin:0;background:#fffaf5;color:#29231f;font-family:Arial,Helvetica,sans-serif}a{color:inherit;text-decoration:none}button{font:inherit;cursor:pointer}[x-cloak]{display:none!important}.shell{display:flex;height:100vh;height:100dvh;overflow:hidden}.sidebar{width:260px;flex-shrink:0;display:flex;flex-direction:column;background:#151311;color:#dce5ee;padding:24px 16px;border-right:1px solid #ffffff18;overflow-y:auto}.brand{display:flex;align-items:center;gap:12px;padding:0 8px 28px;font-weight:700}.brand img{height:48px;width:48px;border-radius:50%}.brand small{display:block;color:#ffad68;letter-spacing:2px;font-size:10px;margin-top:5px}.section-label{padding:20px 14px 10px;color:#9caebf;letter-spacing:2px;font-size:10px}.nav-link{display:flex;align-items:center;gap:12px;padding:13px 14px;border-radius:12px;margin:4px 0;font-size:14px}.nav-link:hover{background:#ffffff0d}.nav-link.active{background:#f9731620;color:#ffbd88;border:1px solid #f9731638}.icon{width:20px;text-align:center}.trial{margin-top:auto;border:1px solid #ffffff20;border-radius:16px;padding:16px;background:#ffffff08}.trial strong{font-size:13px}.trial p{font-size:12px;color:#b6c3d2;line-height:1.6}.trial a{display:block;background:#c64b08;color:#fff;text-align:center;padding:10px;border-radius:9px;font-size:12px;font-weight:bold}.workspace{flex:1;min-width:0;display:flex;flex-direction:column}.topbar{height:78px;flex-shrink:0;display:flex;align-items:center;justify-content:space-between;padding:0 32px;border-bottom:1px solid #29231f15;background:#ffffffbe;backdrop-filter:blur(20px)}.topbar small{display:block;font-size:11px;color:#786c62;margin-bottom:6px}.topbar strong{font-size:15px}.tools{display:flex;align-items:center;gap:14px}.tool{border:1px solid #29231f20;border-radius:10px;background:#fff;padding:9px 12px;color:#29231f}.account{position:relative}.avatar{border:0;background:#ffeddc;color:#8d3605;border-radius:50%;height:38px;width:38px;font-size:12px;font-weight:bold}.dropdown{position:absolute;right:0;top:48px;z-index:30;width:200px;border:1px solid #29231f20;background:#fff;border-radius:14px;padding:8px;box-shadow:0 16px 40px #15131115}.dropdown a{display:block;padding:12px;font-size:13px;border-radius:8px}.dropdown a:hover{background:#fff4e8}.dropdown .logout-button{display:flex;align-items:center;gap:9px;width:100%;margin-top:5px;padding:11px 12px;border:0;border-top:1px solid #29231f12;border-radius:8px;background:transparent;color:#b42318;font-size:13px;text-align:left}.dropdown .logout-button:hover{background:#fff1f0}.dropdown .logout-button svg{flex-shrink:0}.content{flex:1;min-height:0;overflow-y:auto;padding:32px}.mobile-toggle{display:none}.overlay{position:fixed;inset:0;background:#15131180;z-index:40;border:0}.close-sidebar{display:none}.footer{font-size:11px;color:#786c62;padding:14px 32px;border-top:1px solid #29231f12;background:#fff}.demo-card{background:#ffffffc9;border:1px solid #29231f15;border-radius:20px;padding:28px;box-shadow:0 12px 40px #62350b08}.demo-card h1{font-size:28px;margin:0 0 12px}.demo-card p{color:#786c62;line-height:1.7}.badge{display:inline-block;padding:6px 10px;background:#ffeddc;border-radius:8px;color:#9a3c06;font-size:12px}a:focus-visible,button:focus-visible{outline:3px solid #fb923c;outline-offset:3px}@media(max-width:900px){.sidebar{position:fixed;inset:0 auto 0 0;z-index:50;transform:translateX(-100%);transition:transform .2s;width:260px}.sidebar.is-open{transform:translateX(0)}.mobile-toggle,.close-sidebar{display:inline-block}.close-sidebar{margin-left:auto;border:0;background:transparent;color:#fff;font-size:22px}.topbar{padding:0 18px;gap:12px}.content{padding:20px}.footer{padding:12px 20px}.desktop-label{display:none}}@media(prefers-reduced-motion:reduce){.sidebar{transition:none}}
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const installButton = document.getElementById('install-button');

            if (installButton) {
                installButton.title = 'Installer Ktontine';
                installButton.setAttribute('aria-label', 'Installer l’application Ktontine');
            }
        });
    </script>
    <link rel="stylesheet" href="{{ asset('css/ktontine-brand.css') }}">
</head>
<body  class="kt-workspace">
{{-- Alpine gère uniquement les états visuels de ce layout. --}}

<div class="shell" x-data="{ sidebarOpen: false, accountOpen: false }" @keydown.escape.window="sidebarOpen = false; accountOpen = false">
    <button x-cloak x-show="sidebarOpen" class="overlay" @click="sidebarOpen = false" aria-label="Fermer le menu"></button>
    <aside id="sidebar" class="sidebar" :class="{ 'is-open': sidebarOpen }">
        <a class="brand" href="{{ route('dashboard') }}"><img src="{{ $platformLogoUrl }}" alt="Ktontine"><span>Ktontine<small>TONTINE</small></span></a>
        <button class="close-sidebar" @click="sidebarOpen = false" aria-label="Fermer le menu">×</button>
        <div class="section-label">MON ACTIVITÉ</div>
        <nav aria-label="Navigation principale">
            <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}"><span class="icon" aria-hidden="true">◫</span>Tableau de bord</a>
            <a href="/clients" class="nav-link {{ request()->is('clients*') ? 'active' : '' }}"><span class="icon" aria-hidden="true">♧</span>Clients</a>
            <a href="/tontines" class="nav-link {{ request()->is('tontines*') ? 'active' : '' }}"><span class="icon" aria-hidden="true">◎</span>Tontines</a>
            <a href="/cotisations" class="nav-link {{ request()->is('cotisations*') ? 'active' : '' }}"><span class="icon" aria-hidden="true">↗</span>Cotisations</a>
            <a href="{{ route('tickets.sheets') }}" class="nav-link {{ request()->routeIs('tickets.sheets*') ? 'active' : '' }}"><span class="icon" aria-hidden="true"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M4 9h16M4 15h16M12 3v18"/></svg></span>Planches de tickets</a>
            <a href="/calendrier" class="nav-link {{ request()->is('calendrier*') ? 'active' : '' }}"><span class="icon" aria-hidden="true">▦</span>Calendrier</a>
            <a href="/retards" class="nav-link {{ request()->is('retards*') ? 'active' : '' }}"><span class="icon" aria-hidden="true">◷</span>Retards</a>
            <a href="/rapports" class="nav-link {{ request()->is('rapports*') ? 'active' : '' }}"><span class="icon" aria-hidden="true">▥</span>Rapports</a>
        </nav>
        <div class="section-label">MON ESPACE</div>
        <nav aria-label="Gestion de l’espace">
            <a href="/equipe" class="nav-link {{ request()->is('equipe*') ? 'active' : '' }}"><span class="icon" aria-hidden="true">♧</span>Équipe</a>
            <a href="{{ route('abonnement') }}" class="nav-link {{ request()->routeIs('abonnement') ? 'active' : '' }}"><span class="icon" aria-hidden="true">◇</span>Paiement</a>
            <a href="/parametres" class="nav-link {{ request()->is('parametres*') ? 'active' : '' }}"><span class="icon" aria-hidden="true">⚙</span>Paramètres</a>
            <button type="button" class="nav-link kt-contact-sidebar" onclick="document.getElementById('support-contact-dialog').showModal()" aria-haspopup="dialog" aria-controls="support-contact-dialog"><span class="icon" aria-hidden="true">✉</span>Contacter le support</button>
        </nav>
    </aside>
    <div class="workspace">
        <header   class="topbar">
            <div style="display:flex;align-items:center;gap:14px"><button class="tool mobile-toggle" @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen" aria-controls="sidebar" aria-label="Ouvrir le menu">☰</button><div><small>MON ESPACE TONTINIER</small><strong>Ktontine</strong></div></div>
            <div class="tools">
                <span class="badge desktop-label">Guinée · GNF</span>

                <livewire:notification-center />

                <div class="account" @click.outside="accountOpen = false">
                    <button class="avatar" @click="accountOpen = !accountOpen" :aria-expanded="accountOpen" aria-controls="account-menu" aria-label="Ouvrir le menu du compte">KT</button>
                    <nav id="account-menu" class="dropdown" x-cloak x-show="accountOpen" x-transition aria-label="Compte"><a href="{{ route('profile') }}">Mon profil</a>
                        <a href="/parametres">Paramètres</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M10 17l5-5-5-5"/>
                            <path d="M15 12H3"/>
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/></svg>
                            <span>Se déconnecter</span>
                        </button>
                    </form>
                        </nav>
                    </div>
                </div>
        </header>
        {{-- Modale hors de la barre supérieure pour éviter ses contraintes de positionnement. --}}

        {{-- Chaque page Livewire sera insérée à cet endroit. --}}
        <main class="content" id="contenu">{{ $slot }}</main>
        <footer class="footer">Ktontine · Ensemble aujourd’hui, plus forts demain.</footer>
    </div>
</div>




{{-- Livewire charge également Alpine : ne pas ajouter un second script Alpine. --}}
@livewireScripts
 @RegisterServiceWorkerScript
 <template id="pwa-mascot-template">
    <div class="pwa-mascot-content">
        <img class="pwa-mascot-image" src="{{ asset('ktontine-mascotte-v1.png') }}" alt="" width="34" height="45">
        <button class="pwa-mascot-close" type="button" aria-label="Fermer l’invitation à installer Ktontine">×</button>
    </div>
 </template>
 <script>
    (() => {
        const prompt = document.getElementById('install-prompt');
        const button = document.getElementById('install-button');
        const template = document.getElementById('pwa-mascot-template');
        if (!prompt || !button || !template || prompt.querySelector('.pwa-mascot-content')) { return; }
        prompt.prepend(template.content.cloneNode(true));
        prompt.setAttribute('aria-label', 'Installer Ktontine sur cet appareil');
        button.textContent = 'Installer l’app';
        button.setAttribute('aria-label', 'Installer l’application Ktontine');
        button.title = 'Installer Ktontine';
        const iosTip = prompt.querySelector('.ios-tip');
        if (iosTip) { iosTip.textContent = 'Sur iPhone ou iPad : touchez Partager dans Safari, puis « Sur l’écran d’accueil ».'; }
        prompt.querySelector('.pwa-mascot-close').addEventListener('click', () => {
            prompt.style.display = 'none';
        });
        function updateInstallInstructions() {
            const ios = prompt.getAttribute('data-mode') === 'ios';
            button.textContent = ios ? 'Voir les étapes' : 'Installer l’app';
            button.setAttribute('aria-label', ios ? 'Afficher les étapes d’installation sur iPhone ou iPad' : 'Installer l’application Ktontine');
        }
        new MutationObserver(updateInstallInstructions).observe(prompt, { attributes: true, attributeFilter: ['data-mode'] });
        updateInstallInstructions();
    })();
 </script>
<x-support-contact :contact="$supportContact" />
</body>
</html>
