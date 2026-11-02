<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Page introuvable — Ktontine</title>
    <link rel="icon" href="{{ asset('ktontine-logo.png') }}" type="image/png">
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;min-height:100dvh;display:grid;place-items:center;padding:24px;background:#f3f4f6;color:#251d17;font-family:Arial,Helvetica,sans-serif}main{width:100%;max-width:1050px;overflow:hidden;border:1px solid #e9dfd6;border-radius:28px;background:#fff;box-shadow:0 24px 80px #24180c0a}.brand{display:inline-flex;align-items:center;gap:10px;color:#29201a;font-size:21px;font-weight:800;text-decoration:none;letter-spacing:-.8px}.brand img{width:40px;height:40px;border-radius:50%;object-fit:contain}.brand span{color:#c64b08}.layout{display:grid;grid-template-columns:1.15fr 1fr}.copy{padding:clamp(26px,5vw,55px)}.eyebrow{margin:38px 0 14px;font-size:11px;font-weight:700;letter-spacing:2px;color:#b7470b}h1{margin:0 0 20px;font-size:clamp(30px,4vw,46px);line-height:1.15;letter-spacing:-1.5px}.description{margin:0;color:#806b5b;font-size:15px;line-height:1.9;max-width:390px}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:28px}.button{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:14px 18px;border:1px solid #c64b08;border-radius:10px;background:#c64b08;color:white;font-size:13px;font-weight:700;text-decoration:none;line-height:1.5}.button:hover{background:#a73e07}.button.secondary{border-color:#e6d8cd;background:#fffaf6;color:#6f4931}.button.secondary:hover{background:#fff0e2}a:focus-visible{outline:3px solid #fb923c;outline-offset:4px}.hint{margin:25px 0 0;padding-top:20px;border-top:1px solid #f0e6de;color:#9a8574;font-size:12px;line-height:1.8}.scene{position:relative;isolation:isolate;min-height:480px;display:flex;align-items:flex-end;justify-content:center;overflow:hidden;background:radial-gradient(ellipse at 50% 75%,#b8572638,transparent 65%),#1c1713}.scene::before{content:"";position:absolute;width:360px;height:360px;border:1px dashed #f4945140;border-radius:50%;left:50%;top:50%;transform:translate(-50%,-40%);z-index:-1}.code{position:absolute;top:26px;left:0;right:0;text-align:center;color:#ffb780;font-size:clamp(100px,13vw,160px);line-height:1;font-weight:800;letter-spacing:-8px;opacity:.2;z-index:-1}.mascot{display:block;width:280px;height:420px;object-fit:contain;object-position:bottom;filter:drop-shadow(0 15px 22px #0005);animation:float 5s ease-in-out infinite}.bubble{position:absolute;top:37px;left:22px;right:22px;max-width:250px;margin:auto;padding:14px 18px;border:1px solid #ffb78040;border-radius:16px;background:#fff8f0;color:#59351e;box-shadow:0 10px 30px #0001;font-size:13px;font-weight:700;line-height:1.6;text-align:center}.bubble span{display:block;font-size:11px;font-weight:400;color:#94725b;margin-top:3px}footer{padding:17px 26px;border-top:1px solid #eee5dd;background:#fffcf9;color:#9a8574;font-size:11px;text-align:center}@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-9px)}}@media(max-width:700px){body{padding:16px}.layout{grid-template-columns:1fr}.scene{min-height:340px;order:-1}.mascot{width:210px;height:310px}.bubble{top:18px;left:auto;right:18px;max-width:165px;padding:11px;font-size:11px}.bubble span{font-size:10px}.code{top:40px;font-size:130px;text-align:left;padding-left:18px}.eyebrow{margin-top:26px}.copy{padding:27px}h1{letter-spacing:-1px}.description{font-size:14px}.actions a{flex:1}footer{padding:16px}}@media(prefers-reduced-motion:reduce){.mascot{animation:none}}
    </style>
</head>
<body>
    <main aria-labelledby="error-title">
        <div class="layout">
            <section class="copy">
                <a class="brand" href="{{ url('/') }}"><img src="{{ asset('ktontine-logo.png') }}" alt="" width="40" height="40">K<span>tontine.</span></a>
                <p class="eyebrow">404 · PAGE INTROUVABLE</p>
                <h1 id="error-title">Un petit détour.<br>On retrouve le chemin ?</h1>
                <p class="description">Cette page n’existe pas ou son adresse a changé. La mascotte vous accompagne pour reprendre votre visite.</p>
                <nav class="actions" aria-label="Retrouver mon chemin">
                    <a class="button" href="{{ url('/') }}">Retour à l’accueil <span aria-hidden="true">↗</span></a>
                    <a class="button secondary" href="{{ route('login') }}">Me connecter</a>
                </nav>
                <p class="hint">Vérifiez l’adresse saisie ou repartez de l’accueil pour retrouver ce que vous cherchez.</p>
            </section>
            <div class="scene">
                <span class="code" aria-hidden="true">404</span>
                <img class="mascot" src="{{ asset('ktontine-mascotte-v1.png') }}" alt="La mascotte Ktontine vous guide vers l’accueil" width="280" height="420">
                <p class="bubble">Je vous remets sur la bonne voie.<span>Votre activité vous attend.</span></p>
            </div>
        </div>
        <footer>Ktontine · Ensemble aujourd’hui, plus forts demain.</footer>
    </main>
</body>
</html>
