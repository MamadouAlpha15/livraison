<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B1E3D">
    <title>{{ config('app.name', 'Shopio') }}</title>
    <link rel="icon" type="image/png" href="/images/shopio-logo-32.png?v=2">
    <link rel="shortcut icon" type="image/png" href="/images/shopio-logo-32.png?v=2">
    <link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Pages de compte (connexion, inscription, mot de passe…) aux couleurs de Shopio :
           panneau bleu nuit à gauche, formulaire dans une carte blanche à droite. */
        :root {
            --g-blue: #1F7CF6; --g-btn: #1668D8; --g-btn-h: #1260C9; --g-b50: #EAF2FF; --g-b100: #D6E6FF; --g-sky: #7DB4FF;
            --g-night: #0B1E3D; --g-night2: #132B52; --g-amber: #F59E0B;
            --g-bg: #F6F9FF; --g-surf: #FFFFFF; --g-line: #E3E8F2;
            --g-ink: #16162A; --g-ink2: #45455E; --g-ink3: #62627C; --g-ok: #15803D; --g-ok50: #E7F6EC; --g-err: #B91C1C; --g-err50: #FDECEC;
            --g-font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
            --g-out: cubic-bezier(.22,1,.36,1); --g-pop: cubic-bezier(.34,1.56,.64,1);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--g-font); min-height: 100vh; display: flex; background: var(--g-bg); color: var(--g-ink); -webkit-font-smoothing: antialiased; }
        :focus-visible { outline: 3px solid var(--g-blue); outline-offset: 2px; }

        /* ── Panneau gauche : bleu nuit, halo bleu ── */
        .auth-left {
            width: 44%; min-height: 100vh; position: relative; overflow: hidden;
            background: radial-gradient(110% 80% at 100% 0%, rgba(31,124,246,.42), transparent 60%), radial-gradient(70% 60% at 0% 100%, rgba(125,180,255,.14), transparent 70%), linear-gradient(150deg, #0B1E3D 0%, #132B52 100%);
            display: flex; flex-direction: column; justify-content: space-between; gap: 40px;
            padding: 48px 52px; color: #fff;
        }
        .auth-left::before { content: ''; position: absolute; right: -160px; top: 30%; width: 460px; height: 460px; border-radius: 50%; border: 1px solid rgba(125,180,255,.18); }
        .auth-left::after { content: ''; position: absolute; right: -80px; top: calc(30% + 80px); width: 300px; height: 300px; border-radius: 50%; border: 1px solid rgba(125,180,255,.12); }
        .al-logo { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; text-decoration: none; }
        .al-logo img { height: 46px; width: 46px; object-fit: cover; border-radius: 14px; box-shadow: 0 8px 22px rgba(0,0,0,.35); }
        .al-logo-name { font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: #fff; }
        .al-body { position: relative; z-index: 1; animation: gIn 700ms var(--g-out) backwards; }
        @keyframes gIn { from { opacity: 0; transform: translateY(14px); } }
        .al-kicker { display: inline-flex; align-items: center; gap: 8px; height: 30px; padding: 0 12px; border-radius: 999px; background: rgba(255,255,255,.1); color: #C9D6EA; font-size: 12.5px; font-weight: 700; margin-bottom: 18px; }
        .al-kicker i { width: 7px; height: 7px; border-radius: 50%; background: #4ADE80; }
        .al-title { font-size: 38px; font-weight: 800; line-height: 1.1; letter-spacing: -.03em; margin-bottom: 14px; text-wrap: balance; }
        .al-title span { color: var(--g-sky); }
        .al-sub { font-size: 15px; color: #C9D6EA; line-height: 1.65; margin-bottom: 32px; max-width: 42ch; }
        .al-features { display: grid; gap: 12px; }
        .al-feature { display: flex; align-items: center; gap: 14px; padding: 12px 14px; border-radius: 18px; background: rgba(255,255,255,.06); box-shadow: inset 0 0 0 1px rgba(255,255,255,.08); }
        .al-ico { width: 42px; height: 42px; border-radius: 14px; background: rgba(31,124,246,.28); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff; }
        .al-ico svg { width: 20px; height: 20px; }
        .al-feature:nth-child(1) .al-ico { background: var(--g-amber); color: #16162A; }
        .al-ftxt strong { display: block; font-size: 14px; font-weight: 800; color: #fff; margin-bottom: 1px; }
        .al-ftxt span { font-size: 12.5px; color: #A9B8D2; }
        .al-footer { position: relative; z-index: 1; font-size: 12px; color: rgba(255,255,255,.45); }

        /* ── Panneau droit : le formulaire dans une carte ── */
        .auth-right { flex: 1; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 48px 32px; overflow-y: auto; }
        .auth-box { width: 100%; max-width: 460px; background: var(--g-surf); border-radius: 28px; padding: 38px 36px 32px; box-shadow: 0 0 0 1px var(--g-line), 0 30px 60px -36px rgba(11,30,61,.35); animation: gCard 520ms var(--g-out) backwards; }
        @keyframes gCard { from { opacity: 0; transform: perspective(1200px) rotateX(-6deg) translateY(16px); } }

        /* Formulaires (connexion, inscription, mot de passe…) */
        .ab-title { font-size: 27px; font-weight: 800; color: var(--g-ink); margin-bottom: 6px; letter-spacing: -.025em; }
        .ab-sub { font-size: 14.5px; color: var(--g-ink2); margin-bottom: 28px; line-height: 1.55; }
        .f-group { margin-bottom: 18px; }
        .f-label { display: block; font-size: 13px; font-weight: 700; color: var(--g-ink); margin-bottom: 7px; }
        .f-input {
            width: 100%; min-height: 50px; padding: 12px 15px; border: 0; border-radius: 14px;
            font-size: 15px; color: var(--g-ink); background: var(--g-surf); outline: none;
            box-shadow: inset 0 0 0 1.5px var(--g-line);
            transition: box-shadow 180ms var(--g-out), background-color 180ms; font-family: inherit;
        }
        .f-input:hover { box-shadow: inset 0 0 0 1.5px #C8D4E8; }
        .f-input:focus { box-shadow: inset 0 0 0 2px var(--g-blue), 0 0 0 4px rgba(31,124,246,.12); }
        .f-input::placeholder { color: #9AA3B8; }
        .f-input-err, .f-input-err:focus { box-shadow: inset 0 0 0 2px var(--g-err) !important; }
        .f-icon-wrap { position: relative; }
        .f-icon-wrap .f-input { padding-left: 46px; }
        .f-icon-wrap .f-ico { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 17px; pointer-events: none; display: inline-flex; color: var(--g-ink3); }
        .f-icon-wrap .f-ico svg { width: 1em; height: 1em; }
        .f-icon-wrap:focus-within .f-ico { color: var(--g-blue); }
        .f-pw-wrap { position: relative; }
        .f-pw-wrap .f-input { padding-left: 46px; padding-right: 52px; }
        .f-pw-eye { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; border: none; border-radius: 999px; background: none; cursor: pointer; font-size: 17px; color: var(--g-ink3); display: inline-flex; align-items: center; justify-content: center; }
        .f-pw-eye svg { width: 1em; height: 1em; }
        .f-pw-eye:hover { color: var(--g-btn); background: var(--g-b50); }
        .f-select {
            width: 100%; min-height: 50px; padding: 12px 40px 12px 15px; border: 0; border-radius: 14px;
            font-size: 15px; color: var(--g-ink); background-color: var(--g-surf); appearance: none; -webkit-appearance: none;
            box-shadow: inset 0 0 0 1.5px var(--g-line);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2362627C'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center; background-size: 16px;
            transition: box-shadow 180ms var(--g-out); outline: none; cursor: pointer; font-family: inherit;
        }
        .f-select:focus { box-shadow: inset 0 0 0 2px var(--g-blue), 0 0 0 4px rgba(31,124,246,.12); }
        .f-flag-wrap { position: relative; }
        .f-flag-wrap .f-select { padding-left: 46px; }
        .f-flag-preview { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 20px; pointer-events: none; line-height: 1; z-index: 1; }
        .f-error { font-size: 12.5px; font-weight: 700; color: var(--g-err); margin-top: 6px; }
        .f-hint { font-size: 12px; color: var(--g-ink3); margin-top: 5px; }
        .btn-auth {
            width: 100%; min-height: 52px; padding: 0 22px; background: var(--g-btn);
            color: #fff; font-size: 15.5px; font-weight: 800; border: none; border-radius: 999px;
            cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            transition: background-color 180ms var(--g-out), transform 180ms var(--g-out), box-shadow 180ms var(--g-out);
        }
        .btn-auth:hover { background: var(--g-btn-h); transform: translateY(-1px); box-shadow: 0 12px 24px -10px rgba(22,104,216,.6); }
        .btn-auth:active { transform: scale(.98); }
        .btn-auth:disabled { opacity: .7; cursor: not-allowed; transform: none; box-shadow: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-spinner { display: inline-block; width: 16px; height: 16px; border: 2.5px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; flex-shrink: 0; }
        /* Variante sombre : pour les boutons à fond clair (ex: "Continuer avec Google") */
        .btn-spinner-dark { display: inline-block; width: 16px; height: 16px; border: 2.5px solid var(--g-line); border-top-color: var(--g-blue); border-radius: 50%; animation: spin .7s linear infinite; flex-shrink: 0; }
        .btn-google { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; min-height: 52px; padding: 0 18px; border-radius: 999px; background: var(--g-surf); color: var(--g-ink); font-size: 15px; font-weight: 700; text-decoration: none; box-shadow: inset 0 0 0 1.5px var(--g-line); transition: box-shadow 180ms var(--g-out), transform 180ms var(--g-out); }
        .btn-google:hover { box-shadow: inset 0 0 0 1.5px var(--g-blue), 0 10px 22px -14px rgba(22,104,216,.6); color: var(--g-ink); transform: translateY(-1px); }
        .g-sep { display: flex; align-items: center; gap: 12px; margin: 22px 0; color: var(--g-ink3); font-size: 12px; font-weight: 800; letter-spacing: .08em; }
        .g-sep::before, .g-sep::after { content: ''; flex: 1; height: 1px; background: var(--g-line); }
        .role-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-top: 2px; }
        .role-card {
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            padding: 13px 8px; border: 0; border-radius: 16px; background: var(--g-surf);
            box-shadow: inset 0 0 0 1.5px var(--g-line);
            cursor: pointer; transition: box-shadow 180ms var(--g-out), background-color 180ms, transform 220ms var(--g-pop);
            font-size: 12.5px; font-weight: 700; color: var(--g-ink2); user-select: none; text-align: center;
        }
        .role-card:hover { box-shadow: inset 0 0 0 1.5px var(--g-blue); background: var(--g-b50); transform: translateY(-1px); }
        .role-card.active { box-shadow: inset 0 0 0 2px var(--g-blue); background: var(--g-b50); color: var(--g-btn); }
        .role-card .rc-ico { font-size: 22px; display: inline-flex; color: var(--g-btn); }
        /* Icônes SVG (IconLibrary) : taille pilotée par le font-size ci-dessus via em */
        .role-card .rc-ico svg { width: 1em; height: 1em; }
        .role-input-hidden { display: none; }
        .f-check { display: flex; align-items: center; gap: 9px; cursor: pointer; min-height: 32px; }
        .f-check input[type=checkbox] { width: 18px; height: 18px; accent-color: var(--g-btn); cursor: pointer; flex-shrink: 0; }
        .f-check span { font-size: 13.5px; color: var(--g-ink2); }
        .auth-link { color: var(--g-btn); font-weight: 700; text-decoration: none; }
        .auth-link:hover { text-decoration: underline; text-underline-offset: 3px; }
        .auth-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; margin-top: 24px; }
        .auth-row-end { display: flex; justify-content: flex-end; gap: 12px; align-items: center; margin-top: 24px; }
        .f-alert { padding: 12px 14px; border-radius: 14px; font-size: 13.5px; font-weight: 600; margin-bottom: 18px; line-height: 1.5; }
        .f-alert-success { background: var(--g-b50); color: var(--g-btn-h); }
        .f-alert-err { background: var(--g-err50); color: var(--g-err); }

        /* ── En-tête mobile (quand le panneau gauche est caché) ── */
        .mobile-header {
            display: none; align-items: center; gap: 10px; width: 100%;
            padding: max(14px, env(safe-area-inset-top, 0px)) max(18px, env(safe-area-inset-right, 0px)) 14px max(18px, env(safe-area-inset-left, 0px));
            background: radial-gradient(90% 140% at 100% 0%, rgba(31,124,246,.45), transparent 60%), var(--g-night);
            position: sticky; top: 0; z-index: 10;
        }
        .mobile-header img { width: 40px; height: 40px; border-radius: 12px; object-fit: cover; }
        .mobile-header-name { font-size: 17px; font-weight: 800; letter-spacing: -.02em; color: #fff; }
        .mobile-header-sub { font-size: 11.5px; color: #A9B8D2; margin-top: 1px; }

        @media (max-width: 1100px) {
            .auth-left { width: 40%; padding: 40px; }
            .al-title { font-size: 31px; }
        }
        @media (max-width: 900px) {
            .auth-left { width: 38%; padding: 36px 28px; }
            .al-title { font-size: 26px; }
            .al-feature { padding: 10px 12px; }
            .al-ico { width: 36px; height: 36px; }
        }
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .auth-left { display: none; }
            .mobile-header { display: flex; }
            .auth-right { min-height: calc(100vh - 68px); align-items: flex-start; padding: 22px max(16px, env(safe-area-inset-left, 0px)) 40px; }
            .auth-box { max-width: 520px; margin: 0 auto; padding: 28px 22px 24px; border-radius: 24px; }
            .ab-title { font-size: 23px; }
            .ab-sub { font-size: 14px; margin-bottom: 22px; }
            .role-card { padding: 11px 6px; font-size: 12px; }
            .role-card .rc-ico { font-size: 20px; }
        }
        @media (max-width: 480px) {
            .auth-right { padding: 14px 12px 36px; }
            .auth-box { padding: 24px 16px 20px; border-radius: 22px; box-shadow: 0 0 0 1px var(--g-line); }
            .f-input, .f-select { font-size: 16px; }
            .role-card { padding: 10px 4px; font-size: 11.5px; }
        }
        @media (max-width: 360px) {
            .f-group { margin-bottom: 14px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 1ms !important; animation-iteration-count: 1 !important; transition-duration: 1ms !important; }
        }
    </style>
</head>
<body>
    {{-- En-tête visible uniquement sur mobile (quand le panneau gauche est caché) --}}
    <div class="mobile-header">
        <img src="/images/shopio-logo-192.png" alt="" width="40" height="40">
        <div>
            <div class="mobile-header-name">Shopio</div>
            <div class="mobile-header-sub">Achetez en confiance, payez à la livraison</div>
        </div>
    </div>

    <aside class="auth-left" aria-label="Shopio">
        <a class="al-logo" href="{{ url('/') }}">
            <img src="/images/shopio-logo-192.png" alt="" width="46" height="46">
            <span class="al-logo-name">Shopio</span>
        </a>
        <div class="al-body">
            <span class="al-kicker"><i aria-hidden="true"></i>La marketplace de Guinée</span>
            <div class="al-title">Achetez en confiance. <span>Payez à la livraison.</span></div>
            <p class="al-sub">Boutiques, produits et livraison au même endroit. Vous ne payez qu’à la réception de votre commande.</p>
            <div class="al-features">
                <div class="al-feature"><div class="al-ico">{!! \App\Support\IconLibrary::svg('truck') !!}</div><div class="al-ftxt"><strong>Paiement à la livraison</strong><span>En espèces, au livreur. Rien à payer avant.</span></div></div>
                <div class="al-feature"><div class="al-ico">{!! \App\Support\IconLibrary::svg('check') !!}</div><div class="al-ftxt"><strong>Boutiques vérifiées</strong><span>Contrôlées par Shopio avant d’ouvrir.</span></div></div>
                <div class="al-feature"><div class="al-ico">{!! \App\Support\IconLibrary::svg('package') !!}</div><div class="al-ftxt"><strong>Suivi de commande</strong><span>Vous suivez votre commande jusqu’à chez vous.</span></div></div>
                <div class="al-feature"><div class="al-ico">{!! \App\Support\IconLibrary::svg('store') !!}</div><div class="al-ftxt"><strong>Ouvrez votre boutique</strong><span>Vitrine, commandes et livreurs au même endroit.</span></div></div>
            </div>
        </div>
        <div class="al-footer">© {{ date('Y') }} Shopio · Tous droits réservés</div>
    </aside>

    <main class="auth-right">
        <div class="auth-box">
            {{ $slot }}
        </div>
    </main>
</body>
</html>
