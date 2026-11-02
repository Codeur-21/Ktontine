<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ktontine, l’espace des tontiniers en Guinée. Organisez vos clients, cotisations et calendriers. un mois d’essai gratuit.">
    <meta name="theme-color" content="#fffaf5">
    <title>Ktontine — Ensemble, chaque jour compte.</title>
    <link rel="icon" href="{{ $platformLogoUrl }}">
    <style>
        :root{--paper:#f8f7ef;--ink:#173e34;--muted:#64736a;--line:#dce2d6;--green:#235b43;--lime:#d9ed96;--orange:#e6a35c}*{box-sizing:border-box}html{scroll-behavior:smooth;scroll-padding-top:100px}body{margin:0;background:var(--paper);color:var(--ink);font-family:Inter,"Segoe UI",Arial,sans-serif;-webkit-font-smoothing:antialiased}a{color:inherit;text-decoration:none}button{font:inherit;cursor:pointer}a,button,summary{-webkit-tap-highlight-color:transparent}a:focus-visible,button:focus-visible,summary:focus-visible{outline:3px solid #ae702c;outline-offset:5px}::selection{background:var(--lime)}.wrap{width:min(1180px,calc(100% - 48px));margin:auto}.nav{position:sticky;top:0;z-index:20;background:#f8f7eff2;border-bottom:1px solid var(--line);backdrop-filter:blur(16px)}.nav-inner{min-height:86px;display:flex;align-items:center;justify-content:space-between;gap:24px}.brand{display:flex;align-items:center;gap:10px;font-size:23px;font-weight:800;letter-spacing:-1px}.brand img{width:42px;height:42px;object-fit:contain;border-radius:50%}.brand span{color:#a86a2c}.links,.actions{display:flex;align-items:center;gap:28px;font-size:13px;font-weight:600}.links a:hover,.text-link:hover{color:#9a6128}.button{display:inline-flex;justify-content:center;align-items:center;gap:22px;background:var(--green);color:white;border:1px solid var(--green);padding:16px 22px;border-radius:9px;font-size:13px;font-weight:650;transition:transform .25s,box-shadow .25s,background .25s}.button:hover{transform:translateY(-3px);box-shadow:0 10px 25px #173e3420;background:#173e34}.button.light{background:var(--lime);color:var(--ink);border-color:var(--lime)}.button.outline{background:transparent;color:var(--ink);border-color:#b7c3b3}.menu{display:none;border:1px solid var(--line);background:transparent;border-radius:8px;padding:9px 12px}.hero{display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:64px;padding:88px 0 82px}.eyebrow{display:flex;align-items:center;gap:10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.7px}.dot{width:7px;height:7px;background:#6d9445;border-radius:50%;box-shadow:0 0 0 5px #6d944514}h1{font-size:clamp(46px,5.5vw,74px);line-height:1.06;letter-spacing:-3.8px;font-weight:650;margin:27px 0}h1 em{font-family:Georgia,serif;font-weight:400;color:#77924c}p{color:var(--muted);font-size:15px;line-height:1.85}.intro{max-width:440px;font-size:16px}.hero-actions{display:flex;align-items:center;flex-wrap:wrap;gap:22px;margin-top:30px}.text-link{font-size:13px;font-weight:650}.small{font-size:11px}.trust{display:flex;flex-wrap:wrap;gap:20px;margin-top:28px;font-size:11px;color:var(--muted)}.trust span:before{content:'✓';margin-right:7px;color:var(--green)}.stage{position:relative;min-width:0;padding:30px 10px 36px}.orbit{position:absolute;inset:-10px -20px;border:1px dashed #bac9a9;border-radius:50%;transform:rotate(-15deg);pointer-events:none}.orbit:after{content:'✦';position:absolute;top:32px;right:64px;font-size:42px;color:#97ad65}.dashboard{position:relative;background:#fff;border:1px solid #dde4d8;border-radius:16px;box-shadow:0 24px 65px #284a2812;transform:rotate(-2deg);overflow:hidden;transition:transform .6s}.stage:hover .dashboard{transform:rotate(0)}.dash-top{padding:18px 22px;border-bottom:1px solid #edf0e8;display:flex;align-items:center;justify-content:space-between;font-size:12px;font-weight:700}.dash-top small{font-weight:400;color:var(--muted)}.dash-body{padding:24px}.dash-greeting{display:flex;align-items:center;justify-content:space-between;gap:10px}.dash-greeting h2{font-size:20px;margin:6px 0}.dash-greeting p{font-size:11px;margin:0}.avatar{width:35px;height:35px;background:#f2e8d9;border-radius:50%;display:grid;place-items:center;font-size:11px}.balance{background:var(--green);color:#fff;border-radius:11px;padding:20px;margin:20px 0;position:relative;overflow:hidden}.balance:after{content:'';position:absolute;width:150px;height:150px;border:22px solid #ffffff09;border-radius:50%;right:-40px;top:-50px}.balance small{color:#cfdfc7;font-size:11px}.balance strong{display:block;font-size:30px;letter-spacing:-1px;margin:9px 0}.balance strong span{font-size:12px;letter-spacing:0;font-weight:400}.balance .small{color:#d9ed96}.chart-head{display:flex;justify-content:space-between;font-size:11px}.bars{height:85px;display:flex;align-items:end;gap:15px;margin:18px 0 26px}.bar{height:var(--height);flex:1;background:#dfeac8;border-radius:4px 4px 0 0;transform-origin:bottom;animation:grow 1.4s both}.bar:nth-child(2n){background:#a7c376}.bar:nth-child(6){background:var(--green)}.payments{border-top:1px solid #edf0e8}.payment{display:flex;align-items:center;gap:10px;padding:13px 0;border-bottom:1px solid #edf0e8;font-size:11px}.payment:last-child{border:0}.payment .name{flex:1}.payment b{font-size:11px}.pill{background:#edf4e7;color:#42663b;border-radius:5px;padding:5px 7px;font-size:10px}.pill.pending{background:#fcf0dd;color:#956622}.float-note{position:absolute;background:#fff;border:1px solid #e0e5d9;border-radius:11px;box-shadow:0 10px 35px #234a2810;display:flex;align-items:center;gap:12px;padding:14px 18px;font-size:11px;animation:float 6s ease-in-out infinite}.float-note strong{display:block;margin-bottom:5px;font-size:12px}.note-top{right:-18px;top:5px}.note-bottom{left:-26px;bottom:5px;animation-delay:-3s}.note-icon{display:grid;place-items:center;width:34px;height:34px;border-radius:50%;background:#edf4df;font-size:17px}.ribbon{border-block:1px solid var(--line);padding:23px 0}.ribbon .wrap{display:flex;justify-content:space-between;flex-wrap:wrap;gap:20px;font-size:12px;color:#65735b}.ribbon b{font-weight:500}.ribbon i{font-style:normal;color:#9bb26a}.section{padding:95px 0}.section-heading{display:flex;justify-content:space-between;align-items:end;gap:40px;margin-bottom:36px}.section-heading p{max-width:340px;margin:0}h2{font-size:clamp(30px,3.8vw,46px);line-height:1.15;letter-spacing:-1.7px;font-weight:600;margin:16px 0}h2 em{font-family:Georgia,serif;font-weight:400;color:#7d9555}.features{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.feature{border:1px solid var(--line);border-radius:13px;padding:29px;background:#ffffff65;transition:transform .3s,background .3s,box-shadow .3s}.feature:hover{transform:translateY(-6px);background:#fff;box-shadow:0 14px 30px #173e3408}.feature-number{display:flex;justify-content:space-between;align-items:center;color:#7d915f;font-family:Georgia,serif;font-size:26px}.feature-number span{font-family:Arial,sans-serif;font-size:18px}.feature h3{font-size:16px;margin:28px 0 10px;letter-spacing:-.3px}.feature p{font-size:13px;margin:0}.journey{background:#eaf0de;border-radius:20px;padding:46px}.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:38px;margin-top:40px}.step{border-top:1px solid #b6c79e;padding-top:22px}.step span{font-family:Georgia,serif;color:#708f48;font-size:32px}.step h3{font-size:17px;margin:16px 0 10px}.step p{font-size:13px}.pricing{display:grid;grid-template-columns:1fr 1fr;gap:100px;align-items:center}.pricing-copy p{max-width:410px}.funds{display:flex;gap:13px;border-top:1px solid var(--line);padding-top:20px;margin-top:26px;font-size:12px;line-height:1.8;color:var(--muted)}.funds b{color:var(--ink)}.price-card{background:#fff;border:1px solid #cdd8bf;border-radius:17px;padding:34px;box-shadow:0 18px 40px #173e3408}.price-top{display:flex;justify-content:space-between;gap:15px;align-items:center;font-size:14px}.price-top span{background:var(--lime);border-radius:20px;padding:7px 12px;font-size:10px}.price{font-size:46px;letter-spacing:-2px;margin:27px 0 0;font-weight:650}.price small{font-size:12px;letter-spacing:0;font-weight:400;color:var(--muted)}.price-card p{font-size:12px;margin-top:5px}.price-card ul{list-style:none;padding:20px 0;display:grid;gap:16px;font-size:13px}.price-card li:before{content:'✓';color:#709044;margin-right:12px}.price-card .button{display:flex}.faq{max-width:760px;margin:auto;padding-bottom:90px}.faq h2{text-align:center;margin-bottom:32px}.faq details{border-bottom:1px solid var(--line);padding:21px 0}.faq summary{cursor:pointer;font-size:14px;font-weight:600;display:flex;justify-content:space-between;gap:20px;list-style:none}.faq summary::-webkit-details-marker{display:none}.faq summary:after{content:'+';font-size:20px;color:#7c9653}.faq details[open] summary:after{content:'−'}.faq details p{font-size:13px;margin-bottom:0}.closing{background:var(--green);border-radius:20px;text-align:center;padding:58px 24px;color:#fff;position:relative;overflow:hidden}.closing:before,.closing:after{content:'';position:absolute;width:260px;height:260px;border:1px solid #ffffff15;border-radius:50%;pointer-events:none}.closing:before{left:-110px;top:-120px}.closing:after{right:-100px;bottom:-140px}.closing .eyebrow{justify-content:center;color:#d9ed96}.closing h2{margin:20px auto 28px;max-width:600px}.closing h2 em{color:#d9ed96}.footer{display:flex;justify-content:space-between;align-items:center;gap:24px;padding:45px 0 30px}.footer p{font-size:11px;margin:6px 0}.footer-links{display:flex;gap:23px;flex-wrap:wrap;font-size:11px;color:var(--muted)}.skip{position:absolute;top:-100px;left:16px;z-index:30;background:var(--green);color:#fff;padding:12px}.skip:focus{top:10px}.reveal{transition:opacity .75s,transform .75s}.motion-ready .reveal:not(.visible){opacity:0;transform:translateY(24px)}@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}@keyframes grow{from{transform:scaleY(.05)}to{transform:scaleY(1)}}@media(min-width:900px){.features .feature:nth-child(3n+2){transition-delay:.08s}.features .feature:nth-child(3n+3){transition-delay:.16s}}@media(max-width:1000px){.hero{gap:30px}.links{gap:16px}.actions{gap:16px}.pricing{gap:45px}.note-top{right:-5px}.note-bottom{left:-5px}.hero h1{font-size:58px}}@media(max-width:760px){.wrap{width:calc(100% - 36px)}.nav-inner{min-height:74px;flex-wrap:wrap;gap:0;padding:14px 0}.brand{font-size:21px}.brand img{width:35px;height:35px}.menu{display:block}.links{display:none;order:3;flex-basis:100%;flex-direction:column;align-items:stretch;padding:20px 0 4px;gap:20px}.links.open{display:flex}.actions{margin-left:auto;margin-right:12px}.actions .text-link{display:none}.actions .button{padding:11px 13px;font-size:11px}.hero{grid-template-columns:1fr;padding:48px 0 55px;gap:38px}.hero h1{font-size:clamp(45px,10vw,66px);letter-spacing:-2.6px}.hero-copy{max-width:540px}.intro{font-size:15px}.stage{max-width:500px;width:100%;margin:auto;padding:22px 12px 35px}.orbit{inset:0}.note-top{right:0;top:0}.note-bottom{left:0;bottom:6px}.float-note{padding:11px;font-size:10px}.float-note strong{font-size:11px}.ribbon .wrap{justify-content:center;font-size:11px;gap:15px}.section{padding:62px 0}.section-heading{display:block}.section-heading p{max-width:480px;margin-top:18px}.features{grid-template-columns:repeat(2,1fr);gap:12px}.feature{padding:22px}.feature h3{margin-top:20px}.journey{padding:28px}.steps{grid-template-columns:1fr;gap:18px;margin-top:28px}.step h3{margin-top:9px}.step p{margin-bottom:0}.pricing{grid-template-columns:1fr;gap:28px}.price-card{padding:27px}.faq{padding-bottom:60px}.footer{align-items:start;flex-direction:column}.closing{padding:42px 22px}.closing h2{font-size:34px}}@media(max-width:400px){.features{grid-template-columns:1fr}.hero-actions{gap:18px}.price{font-size:38px}.payment{gap:7px}.payment .avatar{width:28px;height:28px}.dashboard .dash-body{padding:18px}.trust{gap:12px}.ribbon i{display:none}}@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*:before,*:after{animation:none!important;transition:none!important}.motion-ready .reveal:not(.visible){opacity:1;transform:none}}
    :root{--paper:#fffaf5;--ink:#201914;--muted:#786c62;--line:#e9dfd5;--green:#c64b08;--lime:#ffb477} .nav{background:#fffaf5f2}.dot{background:#ea580c;box-shadow:0 0 0 5px #ea580c14}h1 em,h2 em{color:#c64b08;font-family:inherit;font-style:normal;font-weight:600}.orbit{border-color:#edc6a6}.orbit:after{color:#ed8d44}.dashboard{border-color:#eadfd5;box-shadow:0 24px 65px #43240c12}.dash-top{background:#1a1512;color:#fff;border-color:#453326}.dash-top small{color:#d1bba8}.balance{background:linear-gradient(120deg,#24180f,#a83c09)}.balance small,.balance .small{color:#ffdbbf}.bar{background:#fbd8ba}.bar:nth-child(2n){background:#f5a367}.bar:nth-child(6){background:#c64b08}.pill{background:#f8e6d6;color:#97420d}.note-icon{background:#ffeddd}.float-note{border-color:#ebded1}.ribbon .wrap{color:#806a59}.ribbon i,.feature-number{color:#cc7537}.feature:hover{box-shadow:0 14px 30px #43240c08}.journey{background:#f9eddf}.step{border-color:#e3c5aa}.step span,.faq summary:after{color:#c64b08}.price-card{border-color:#e5c9b1}.price-card li:before{color:#c64b08}.closing{background:#171310}.closing h2 em,.closing .eyebrow{color:#ffad75}.closing .button.light{background:#f97316;border-color:#f97316;color:#21130b}.button.outline{border-color:#d6c0ad}.trust span:before{color:#c64b08}</style>
    <link rel="stylesheet" href="{{ asset('css/ktontine-brand.css') }}">

  @cookieconsentscripts

<style>.kt-cookie-manage{margin:0}.kt-cookie-manage button{border:0;background:transparent;padding:8px 0;color:#8b7564;font:inherit;font-size:11px;cursor:pointer;text-decoration:underline;text-underline-offset:4px}.kt-cookie-manage button:hover{color:#b64a0a}.kt-cookie-manage button:focus-visible{outline:2px solid #c64b08;outline-offset:4px}</style>
</head>
<body class="kt-landing">
<a class="skip" href="#contenu">Aller au contenu</a>
<header class="nav landing-nav">
    <div class="wrap nav-inner">
        <a class="brand" href="#contenu" aria-label="Ktontine, accueil"><img src="{{ $platformLogoUrl }}" alt="" width="56" height="56" fetchpriority="high"><span class="nav-wordmark">Ktontine<span>.</span><small>LA TONTINE, SIMPLEMENT.</small></span></a>
        <nav class="links" id="navigation" aria-label="Navigation principale">
            <a href="#fonctionnalites" data-section="fonctionnalites">Fonctionnalités</a>
            <a href="#cercle" data-section="cercle">Le cercle</a>
            <a href="#comment" data-section="comment">Comment ça marche</a>
            <a href="#tarifs" data-section="tarifs">Tarifs</a>
            <button type="button" class="kt-contact-nav" onclick="document.getElementById('support-contact-dialog').showModal()" aria-haspopup="dialog" aria-controls="support-contact-dialog">Nous contacter</button>
            <div class="nav-mobile-account"><a href="{{ route('login') }}">Se connecter <span aria-hidden="true">↗</span></a><a class="button" href="{{ route('register') }}">Créer mon espace</a></div>
        </nav>
        <div class="actions"><a class="text-link nav-login" href="{{ route('login') }}">Se connecter</a><a class="button nav-register" href="{{ route('register') }}">Commencer <span aria-hidden="true">↗</span></a></div>
        <button class="menu" type="button" aria-controls="navigation" aria-expanded="false" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
    </div>
</header>
<main id="contenu">
    <section class="wrap hero">
        <div class="hero-copy reveal">
            <div class="eyebrow"><span class="dot" aria-hidden="true"></span>Pensé pour les tontiniers de Guinée</div>
            <h1>Ensemble,<br>chaque jour<br><em>compte.</em></h1>
            <p class="intro">Vos clients, vos cotisations, vos projets. Retrouvez toute votre activité dans un espace simple, et avancez l’esprit tranquille.</p>
            <div class="hero-actions"><a class="button" href="{{ route('register') }}">Commencer gratuitement <span aria-hidden="true">↗</span></a><a class="text-link" href="#comment">Découvrir Ktontine <span aria-hidden="true">↓</span></a></div>
            <p class="small">Un mois d’essai · Puis 200 000 GNF / an</p>
            <div class="trust"><span>Mobile et ordinateur</span><span>Votre espace personnel</span></div>
        </div>
        <div class="stage reveal mascot-scene" aria-label="Ktontine : vos clients, cotisations et reçus réunis">
            <div class="mascot-halo" aria-hidden="true"></div>
            <div class="mascot-ring" aria-hidden="true"></div>
            <img class="kt-mascot" src="{{ asset('ktontine-mascotte-v1.png') }}" alt="La mascotte Ktontine, souriante, avec sa tablette de gestion" width="1024" height="1536" fetchpriority="high">
            <div class="mascot-card mascot-clients"><span class="mascot-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="8" r="3"/><path d="M3 20v-3a6 6 0 0 1 12 0v3M17 6v6m-3-3h6"/></svg></span><div><strong>Vos clients réunis</strong><small>Un cercle de confiance</small></div></div>
            <div class="mascot-card mascot-payment"><span class="mascot-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="5" width="18" height="14" rx="3"/><circle cx="12" cy="12" r="3"/><path d="M6 12h.01M18 12h.01"/></svg></span><div><strong>Cotisation enregistrée</strong><small>+ 50 000 GNF <span class="mascot-example">· Exemple</span></small></div></div>
            <div class="mascot-card mascot-confirm"><span class="mascot-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg></span><div><strong>Reçu disponible</strong><small>Chaque paiement a sa trace</small></div></div>
            <span class="mascot-spark spark-one" aria-hidden="true">✦</span><span class="mascot-spark spark-two" aria-hidden="true">✧</span>
            <button class="mascot-pause" type="button" aria-pressed="false" aria-label="Mettre les animations de la mascotte en pause">Ⅱ Pause</button>
        </div>
    </section>
    <div class="ribbon"><div class="wrap"><b>Clients organisés</b><i aria-hidden="true">✦</i><b>Cotisations traçables</b><i aria-hidden="true">✦</i><b>Calendriers lisibles</b><i aria-hidden="true">✦</i><b>Retards identifiés</b></div></div>
    <section class="circle-section" id="cercle" aria-labelledby="circle-title">
        <div class="wrap circle-layout">
            <div class="circle-copy reveal">
                <div class="eyebrow"><span class="dot" aria-hidden="true"></span>Le cercle Ktontine</div>
                <h2 id="circle-title">Une tontine.<br><em>Tout le monde avance.</em></h2>
                <p>Au centre, votre tontine. Autour, les personnes et les outils qui vous aident à garder le fil, jour après jour.</p>
                <ol class="circle-explanations">
                    <li><span class="circle-number">1</span><div><h3>Les personnes</h3><p>Vous organisez votre activité. Vos clients ont leur historique et vos agents vous accompagnent dans le suivi.</p></div></li>
                    <li><span class="circle-number">2</span><div><h3>Le quotidien</h3><p>Chaque cotisation retrouve sa place. Le calendrier vous aide à repérer les paiements et les retards.</p></div></li>
                    <li><span class="circle-number">3</span><div><h3>La confiance</h3><p>Des reçus pour les opérations et des rapports pour votre activité. Vous gardez une trace et une vue d’ensemble.</p></div></li>
                </ol>
                <a class="text-link" href="#comment">Découvrir le fonctionnement <span aria-hidden="true">↗</span></a>
            </div>
            <div class="circle-visual reveal">
                <svg class="circle-icons" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="0" height="0"><defs>
                    <symbol id="circle-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-3a6 6 0 0 1 12 0v3m2-15a3 3 0 0 1 0 6m1 3a5 5 0 0 1 3 5v1"/></symbol>
                    <symbol id="circle-wallet" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="15" rx="3"/><path d="M3 8V5a2 2 0 0 1 2-2h12m4 8h-6v5h6"/><path d="M17 13.5h.01"/></symbol>
                    <symbol id="circle-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18m-13 5 2 2 5-5"/></symbol>
                    <symbol id="circle-receipt" viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Zm3 5h6m-6 4h6m-6 4h3"/></symbol>
                    <symbol id="circle-chart" viewBox="0 0 24 24"><path d="M4 3v18h17M9 16v-5m5 5V7m5 9V4"/></symbol>
                    <symbol id="circle-shield" viewBox="0 0 24 24"><path d="M12 3 3 7v5c0 5 9 9 9 9s9-4 9-9V7l-9-4Zm-4 9 3 3 5-6"/></symbol>
                </defs></svg>
                <div class="circle-diagram" aria-label="Votre tontine au centre des clients, agents, cotisations, calendrier, reçus et rapports">
                    <div class="circle-orbit orbit-outer" aria-hidden="true"></div>
                    <div class="circle-orbit orbit-middle" aria-hidden="true"></div>
                    <div class="circle-orbit orbit-inner" aria-hidden="true"></div>
                    <span class="circle-ring-label label-people" aria-hidden="true">Les personnes</span>
                    <span class="circle-ring-label label-daily" aria-hidden="true">Le quotidien</span>
                    <span class="circle-ring-label label-trust" aria-hidden="true">La confiance</span>
                    <div class="circle-center"><svg aria-hidden="true"><use href="#circle-wallet"/></svg><strong>Votre<br>tontine</strong></div>
                    <div class="circle-node node-clients"><span class="circle-node-icon"><svg aria-hidden="true"><use href="#circle-users"/></svg></span><div><strong>Clients</strong><small>adhésions · historique</small></div></div>
                    <div class="circle-node node-agents"><span class="circle-node-icon"><svg aria-hidden="true"><use href="#circle-users"/></svg></span><div><strong>Vos agents</strong><small>équipe · suivi</small></div></div>
                    <div class="circle-node node-payments"><span class="circle-node-icon"><svg aria-hidden="true"><use href="#circle-wallet"/></svg></span><div><strong>Cotisations</strong><small>montants · versements</small></div></div>
                    <div class="circle-node node-calendar"><span class="circle-node-icon"><svg aria-hidden="true"><use href="#circle-calendar"/></svg></span><div><strong>Calendrier</strong><small>échéances · retards</small></div></div>
                    <div class="circle-node node-receipts"><span class="circle-node-icon"><svg aria-hidden="true"><use href="#circle-receipt"/></svg></span><div><strong>Reçus</strong><small>une opération, une trace</small></div></div>
                    <div class="circle-node node-reports"><span class="circle-node-icon"><svg aria-hidden="true"><use href="#circle-chart"/></svg></span><div><strong>Rapports</strong><small>activité · vue d’ensemble</small></div></div>
                </div>
                <button class="circle-pause" type="button" aria-pressed="false">Ⅱ Mettre l’animation en pause</button>
            </div>
        </div>
    </section>
    <section class="wrap section" id="fonctionnalites">
        <div class="section-heading reveal"><div><div class="eyebrow">Votre quotidien, simplifié</div><h2>Tout votre suivi.<br><em>Un seul espace.</em></h2></div><p>Du premier client à la dernière cotisation, gardez une vue claire sur ce qui compte vraiment.</p></div>
        <div class="features">
            <article class="feature reveal"><div class="feature-number">01 <span aria-hidden="true">♧</span></div><h3>Chaque client a sa place</h3><p>Coordonnées, tontines et historique : retrouvez les informations de vos clients sans feuilleter un cahier.</p></article>
            <article class="feature reveal"><div class="feature-number">02 <span aria-hidden="true">↗</span></div><h3>Chaque versement compte</h3><p>Enregistrez les cotisations avec leur date et leur montant. Une trace claire pour chaque opération.</p></article>
            <article class="feature reveal"><div class="feature-number">03 <span aria-hidden="true">▦</span></div><h3>Vos journées, en un regard</h3><p>Repérez les jours payés, les journées manquées et les échéances dans un calendrier lisible.</p></article>
            <article class="feature reveal"><div class="feature-number">04 <span aria-hidden="true">◷</span></div><h3>Gardez le fil des retards</h3><p>Identifiez les cotisations en attente pour préparer vos relances et accompagner vos clients.</p></article>
            <article class="feature reveal"><div class="feature-number">05 <span aria-hidden="true">≡</span></div><h3>Une activité qui se lit</h3><p>Consultez vos historiques, vos reçus et vos rapports pour comprendre votre activité par période.</p></article>
            <article class="feature reveal"><div class="feature-number">06 <span aria-hidden="true">◎</span></div><h3>Avancez avec votre équipe</h3><p>Organisez le travail de vos agents grâce aux rôles et permissions prévus pour votre espace.</p></article>
        </div>
    </section>
    <section class="together-section" id="ensemble" aria-labelledby="together-title">
        <div class="wrap">
            <div class="together-heading reveal">
                <div class="eyebrow"><span class="dot" aria-hidden="true"></span>Pour les tontiniers et leurs équipes</div>
                <h2 id="together-title">Tout le monde avance <em>ensemble.</em></h2>
            </div>
            <div class="together-grid reveal">
                <a class="together-card together-owner" href="#tarifs">
                    <div class="together-card-heading"><span class="together-icon"><svg aria-hidden="true"><use href="#circle-wallet"/></svg></span><h3>Votre espace tontinier</h3></div>
                    <p>Clients, tontines et cotisations réunis. Une vue claire pour organiser votre activité au quotidien.</p>
                    <div class="together-card-footer"><span><strong>Un mois gratuit</strong><small>Puis 200 000 GNF / an</small></span><span class="together-arrow" aria-hidden="true">↗</span></div>
                </a>
                <a class="together-card together-clients" href="#fonctionnalites">
                    <div class="together-card-heading"><span class="together-icon"><svg aria-hidden="true"><use href="#circle-users"/></svg></span><h3>Chaque client, bien suivi</h3></div>
                    <p>Ses coordonnées, ses versements et son historique. Retrouvez la bonne information au bon moment.</p>
                    <div class="together-card-footer"><strong>Un suivi personnalisé</strong><span class="together-arrow" aria-hidden="true">↗</span></div>
                </a>
                <a class="together-card together-team" href="#comment">
                    <div class="together-card-heading"><span class="together-icon"><svg aria-hidden="true"><use href="#circle-users"/></svg></span><h3>Une équipe organisée</h3></div>
                    <p>Des rôles et des permissions pour répartir les tâches de vos agents dans votre espace de travail.</p>
                    <div class="together-card-footer"><strong>Chacun trouve sa place</strong><span class="together-arrow" aria-hidden="true">↗</span></div>
                </a>
                <a class="together-card together-records" href="#cercle">
                    <div class="together-card-heading"><span class="together-icon"><svg aria-hidden="true"><use href="#circle-receipt"/></svg></span><h3>La confiance, en clair</h3></div>
                    <p>Reçus, calendriers et rapports : gardez une trace de vos opérations et une vue d’ensemble de votre activité.</p>
                    <div class="together-card-footer"><strong>Chaque opération compte</strong><span class="together-arrow" aria-hidden="true">↗</span></div>
                </a>
                <div class="together-center" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 3-5 4 1 3-1 5 1 3 5-3 7-4 3-4-1-5-5-2-5Z"/><path d="m6 5 5 3-3 3 2 2 4-3 5 5m-8-7 3 2m-8 4 5 5m-2-7 6 6"/></svg><strong>ENSEMBLE</strong></div>
            </div>
        </div>
    </section>
    <section class="wrap" id="comment"><div class="journey reveal"><div class="eyebrow">Simple dès le premier jour</div><h2>Du cahier au numérique.<br><em>À votre rythme.</em></h2><div class="steps"><div class="step"><span>01</span><h3>Créez votre espace</h3><p>Inscrivez-vous et renseignez le profil de votre activité. Votre organisation commence ici.</p></div><div class="step"><span>02</span><h3>Ajoutez vos clients</h3><p>Définissez vos tontines, les montants quotidiens et les périodes de cotisation.</p></div><div class="step"><span>03</span><h3>Suivez chaque journée</h3><p>Enregistrez les versements et retrouvez les paiements, les retards et les historiques.</p></div></div></div></section>
    <section class="wrap section pricing" id="tarifs"><div class="pricing-copy reveal"><div class="eyebrow">Une offre, en toute clarté</div><h2>La confiance commence<br>par <em>la simplicité.</em></h2><p>Prenez un mois pour découvrir Ktontine et l’intégrer à votre quotidien. Poursuivez ensuite avec un abonnement annuel.</p><div class="funds"><span aria-hidden="true">◎</span><div><b>Vous gardez la gestion de vos fonds.</b><br>Ktontine organise vos opérations et ne conserve pas l’argent de vos clients.</div></div></div><div class="price-card reveal"><div class="price-top"><b>L’espace tontinier</b><span>Un mois gratuit</span></div><div class="price">200 000 <small>GNF / an</small></div><p>Après votre période d’essai d’un mois.</p><ul><li>Gestion des clients et des tontines</li><li>Enregistrement des cotisations</li><li>Calendrier et suivi des retards</li><li>Tableau de bord et historiques</li><li>Un espace dédié à votre activité</li></ul><a class="button" href="{{ route('register') }}">Créer mon espace gratuit <span aria-hidden="true">↗</span></a></div></section>
    <section class="wrap"><div class="faq reveal"><h2>On vous répond.</h2><details><summary>À qui s’adresse Ktontine ?</summary><p>Aux tontiniers en Guinée qui souhaitent numériser leurs cartes, leurs cahiers et leur suivi quotidien des cotisations.</p></details><details><summary>Ktontine garde-t-il l’argent de mes clients ?</summary><p>Non. Ktontine sert à enregistrer et organiser les opérations que vous déclarez. Vous restez responsable de la collecte et de la remise des fonds.</p></details><details><summary>Comment fonctionne l’essai gratuit ?</summary><p>Vous disposez d’un mois pour découvrir la plateforme. L’abonnement prévu est ensuite de 200 000 GNF par an.</p></details><details><summary>Puis-je travailler avec mes agents ?</summary><p>Le projet prévoit des comptes employés avec des rôles et des permissions pour répartir les tâches dans votre espace.</p></details><details><summary>Comment me connecter à mon espace ?</summary><p>Utilisez vos identifiants sur la <a class="text-link" href="{{ route('login') }}">page de connexion →</a>.</p></details></div></section>
    <section class="wrap"><div class="closing reveal"><div class="eyebrow">Ensemble aujourd’hui, plus forts demain</div><h2>Votre prochaine journée,<br><em>l’esprit tranquille.</em></h2><a class="button light" href="{{ route('register') }}">Je crée mon espace <span aria-hidden="true">↗</span></a></div></section>
</main>
<footer class="wrap footer"><div><a class="brand" href="#contenu" aria-label="Ktontine, accueil"><img src="{{ $platformLogoUrl }}" alt="" width="56" height="56" loading="lazy"><span>Ktontine<span>.</span></span></a><p>La tontine avance. Vous aussi.</p></div><div class="footer-links"><a href="#fonctionnalites">Fonctionnalités</a><a href="#tarifs">Tarifs</a><a href="{{ route('login') }}">Se connecter</a><span>© 2026 Ktontine</span><div class="developer-signature"><span class="developer-code-icon" aria-label="Développeur">&lt;&gt;</span><div class="developer-contact"><span class="developer-caption">LE DÉVELOPPEUR</span><strong>Abdoulaye Sylla</strong><a href="mailto:abdoulayesylla25@gmail.com"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/></svg>abdoulayesylla25@gmail.com</a><a href="tel:+224626427442"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m5 3 4 4-2 3c2 3 4 5 7 6l3-2 4 4-2 3C11 22 2 13 2 6Z"/></svg>626 427 442</a></div></div></div><div class="kt-cookie-manage-wrap">@cookieconsentbutton(action: 'reset', label: 'Gérer mes cookies', attributes: ['class' => 'kt-cookie-manage cookiereset'])</div></footer>
<script>
    const menuButton = document.querySelector('.menu');
    const navigation = document.querySelector('#navigation');
    const navHeader = document.querySelector('.landing-nav');
    const mobileNav = window.matchMedia('(max-width: 1050px)');
    function setMenuOpen(open, returnFocus = false) {
        navigation.classList.toggle('open', open);
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
        if (returnFocus) { menuButton.focus(); }
    }
    menuButton.addEventListener('click', () => setMenuOpen(menuButton.getAttribute('aria-expanded') !== 'true'));
    navigation.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setMenuOpen(false)));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && navigation.classList.contains('open')) { setMenuOpen(false, true); }
    });
    document.addEventListener('click', event => {
        if (!navHeader.contains(event.target)) { setMenuOpen(false); }
    });
    mobileNav.addEventListener('change', () => setMenuOpen(false));
    if ('IntersectionObserver' in window) {
        const sectionLinks = navigation.querySelectorAll('[data-section]');
        const sectionObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) { return; }
                sectionLinks.forEach(link => {
                    if (link.dataset.section === entry.target.id) { link.setAttribute('aria-current', 'location'); }
                    else { link.removeAttribute('aria-current'); }
                });
            });
        }, { rootMargin: '-15% 0px -55% 0px', threshold: 0 });
        sectionLinks.forEach(link => {
            const section = document.getElementById(link.dataset.section);
            if (section) { sectionObserver.observe(section); }
        });
    }
    if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        document.querySelectorAll('.reveal').forEach(element => observer.observe(element));
        document.documentElement.classList.add('motion-ready');
    }
    const mascotScene = document.querySelector('.mascot-scene');
    const mascotPause = mascotScene.querySelector('.mascot-pause');
    const mascotMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    function setMascotPaused(paused) {
        mascotScene.classList.toggle('is-paused', paused);
        mascotPause.setAttribute('aria-pressed', String(paused));
        mascotPause.setAttribute('aria-label', paused ? 'Reprendre les animations de la mascotte' : 'Mettre les animations de la mascotte en pause');
        mascotPause.textContent = paused ? '▶ Reprendre' : 'Ⅱ Pause';
    }
    mascotPause.addEventListener('click', () => setMascotPaused(!mascotScene.classList.contains('is-paused')));
    mascotMotion.addEventListener('change', event => setMascotPaused(event.matches));
    setMascotPaused(mascotMotion.matches);
    const circleSection = document.querySelector('.circle-section');
    const circlePause = circleSection.querySelector('.circle-pause');
    circlePause.addEventListener('click', () => {
        const paused = circleSection.classList.toggle('is-paused');
        circlePause.setAttribute('aria-pressed', String(paused));
        circlePause.textContent = paused ? '▶ Reprendre l’animation' : 'Ⅱ Mettre l’animation en pause';
    });
</script>

<style>
.developer-signature{position:relative;display:inline-flex;align-items:center;font-size:11px}.developer-code-icon{display:inline-flex;align-items:center;justify-content:center;width:20px;height:22px;color:#a99b8e;font-family:monospace;font-size:11px;font-weight:400;cursor:help;opacity:.65}.developer-signature:hover .developer-code-icon{color:#94745c;opacity:1}.developer-signature .developer-contact{opacity:0;visibility:hidden;pointer-events:none;transition:opacity .15s ease,visibility .15s ease}.developer-signature:hover .developer-contact{opacity:1;visibility:visible;pointer-events:auto}.developer-contact{position:absolute;bottom:100%;right:0;width:max-content;max-width:calc(100vw - 48px);background:#241b15;color:#fff;padding:20px;border:1px solid #62412b;border-radius:14px;box-shadow:0 12px 35px #241b1526;z-index:25;display:flex;flex-direction:column;gap:13px;text-align:left}.developer-contact .developer-caption{font-size:8px;letter-spacing:1.8px;color:#d99c6c}.developer-contact>strong{font-size:15px;color:#fff}.developer-contact a{display:flex;align-items:center;gap:9px;color:#f4d3ba;font-size:12px;font-weight:400;overflow-wrap:anywhere}.developer-contact a:hover{color:#fff}.developer-contact svg{width:16px;height:16px;flex-shrink:0;color:#fb923c}.developer-contact a:focus-visible{outline:2px solid #fb923c;outline-offset:4px}@media(max-width:600px){.footer .footer-links{position:relative;width:100%;flex-wrap:wrap}.developer-signature{position:static}.developer-contact{right:0;left:auto;bottom:calc(100% + 10px);width:min(320px,100%);max-width:100%;padding:18px;box-sizing:border-box}.developer-contact::after{content:"";position:absolute;left:0;right:0;bottom:-12px;height:12px}.developer-contact a{font-size:12px;line-height:1.7;min-width:0}.developer-contact>strong{font-size:16px}}
</style>


  <x-support-contact :contact="$supportContact" />
  @cookieconsentview
</body>
</html>
