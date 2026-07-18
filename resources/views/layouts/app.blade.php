<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- ── PWA — EN PREMIER pour que iOS le détecte avant tout autre contenu ── --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Shopio">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="{{ asset('images/shopio-logo-192.png') }}?v=2">
    <link rel="apple-touch-startup-image" href="{{ asset('images/shopio-logo-192.png') }}?v=2">
    <link rel="icon" type="image/png" href="{{ asset('images/shopio-logo-32.png') }}?v=2">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/shopio-logo-32.png') }}?v=2">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth<meta name="vapid-public-key" content="{{ config('app.vapid_public_key') }}">@endauth
    <meta name="theme-color" content="#059669">
    <title>{{ $title ?? config('app.name', 'Shopio') }}</title>

    {{-- ══ Loader plein écran — inline pour s'afficher avant tout CSS ══ --}}
    <style>
        #pg-loader {
            position:fixed;inset:0;z-index:999999;
            background:#fff;
            display:flex;flex-direction:column;
            align-items:center;justify-content:center;gap:20px;
            transition:opacity .35s ease;
        }
        #pg-loader.done { opacity:0;pointer-events:none; }
        #pg-loader-logo {
            width:80px;height:80px;border-radius:20px;
            object-fit:cover;
            box-shadow:0 8px 28px rgba(5,150,105,.25);
            animation:pgLogoPulse 1.5s ease-in-out infinite;
        }
        #pg-loader-spin {
            width:40px;height:40px;
            border:3.5px solid #e5e7eb;
            border-top-color:#059669;
            border-radius:50%;
            animation:pgSpin .75s linear infinite;
        }
        #pg-loader-txt {
            font-family:system-ui,sans-serif;
            font-size:13px;font-weight:600;
            color:#6b7280;letter-spacing:.3px;
        }
        @keyframes pgSpin     { to { transform:rotate(360deg); } }
        @keyframes pgLogoPulse{
            0%,100% { transform:scale(1);    box-shadow:0 8px 28px rgba(5,150,105,.25); }
            50%      { transform:scale(1.06); box-shadow:0 12px 36px rgba(5,150,105,.4); }
        }
    </style>


    {{-- ══ Ressource hints (connexions anticipées) ══ --}}
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- ══ NProgress — chargé après Bootstrap pour ne pas bloquer ══ --}}
    <style>
        #nprogress .bar { position:fixed;top:0;left:0;width:100%;height:3px;background:#059669;z-index:9999; }
        #nprogress .peg { box-shadow:0 0 10px #059669,0 0 5px #059669;opacity:1;width:100px;height:100%;position:absolute;right:0; }
    </style>

    {{-- ══ Bootstrap CSS — chargement normal (bloquant voulu : évite le flash de page non stylée) ══ --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brand1: #059669;
            --brand2: #16a34a;
            --text:   #1f2937;
            --muted:  #6b7280;
            --card:   #ffffff;
            --bg:     #f8fafc;
        }

        @media (prefers-color-scheme: dark) {
            :root { --text:#e5e7eb; --muted:#9aa3b2; --card:#0f172a; --bg:#0b1220; }
            .dropdown-menu  { background: var(--card); color: var(--text); }
            .dropdown-item  { color: var(--text); }
            .dropdown-item:hover { background: rgba(255,255,255,.06); }
            footer { background: var(--card); }
        }

        html, body { height: 100%; margin: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            overscroll-behavior: contain;
        }
        /* Taps instantanés sur tous les éléments interactifs */
        a, button, [role="button"], input, select, textarea, label {
            touch-action: manipulation;
        }

        /* ── Navbar (masquée sur les pages avec sidebar) ── */
        .navbar {
            background: linear-gradient(90deg, var(--brand1), var(--brand2));  /*couleurs de la navbar*/
            box-shadow: 0 8px 24px rgba(22,163,74,.20);
        }
        .navbar-brand { font-weight: 700; letter-spacing: .2px; }
        .nav-link { font-weight: 500; }
        .nav-link:hover { opacity: .95; }
        .lang-btn { font-size: 11px; font-weight: 700; color: rgba(255,255,255,.65); text-decoration: none; padding: 2px 7px; border-radius: 5px; transition: all .15s; letter-spacing: .5px; }
        .lang-btn:hover { color: #fff; background: rgba(255,255,255,.15); }
        .lang-btn.active { color: #fff; background: rgba(255,255,255,.22); }

        /* ── Sur les pages "dashboard" : on cache navbar + footer + on neutralise le main ── */
        body.is-dashboard .app-navbar  { display: none !important; }
        body.is-dashboard .app-footer  { display: none !important; }
        body.is-dashboard .app-flash   { display: none !important; }
        body.is-dashboard > main.app-main {
            padding: 0 !important;
            margin:  0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        /* ── Pages normales ── */
        .page-header { padding: 28px 0 6px; }
        .page-header h1 {
            font-size: clamp(1.25rem, 2.2vw, 1.8rem);
            font-weight: 700; margin: 0;
        }
        .card { background: var(--card); border: none; box-shadow: 0 8px 30px rgba(0,0,0,.06); }
        .card-header { background: transparent; border-bottom: 1px solid rgba(0,0,0,.06); }

        footer {
            background: #ffffff;
            border-top: 1px solid rgba(0,0,0,.06);
            color: var(--muted);
        }

        .btn-ghost {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            color: #fff;
        }
        .btn-ghost:hover { background: rgba(255,255,255,.25); color: #fff; }

        /* ── Pagination Bootstrap 5 — style global ── */
        .pagination { gap: 3px; flex-wrap: wrap; }
        .page-link {
            border-radius: 8px !important;
            border: 1px solid #dee2e6;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 13px;
            transition: all .15s;
            background: #fff;
        }
        .page-link:hover {
            background: #ecfdf5;
            border-color: #6ee7b7;
            color: #059669;
        }
        .page-item.active .page-link {
            background: #10b981;
            border-color: #059669;
            color: #fff;
            box-shadow: 0 2px 8px rgba(16,185,129,.35);
        }
        .page-item.disabled .page-link {
            opacity: .45;
            background: #f9fafb;
        }
        /* Conteneur centré */
        .pagination-wrap {
            display: flex;
            justify-content: center;
            padding: 16px 0 4px;
        }

        /* Empêche le zoom iOS sur les champs de saisie */
        @media (max-width: 768px) {
            input, select, textarea { font-size: 16px !important; }
        }

        /* ══ Transition de page — fade-in doux (opacity only, pas de transform) ══ */
        .app-main { animation: pgFadeIn .2s ease both; }
        @keyframes pgFadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* ══ Bouton en cours de chargement ══ */
        .btn-loading {
            opacity: .7 !important;
            pointer-events: none !important;
            position: relative;
        }
        .btn-loading::after {
            content: '';
            display: inline-block;
            width: 12px; height: 12px;
            border: 2px solid currentColor;
            border-top-color: transparent;
            border-radius: 50%;
            animation: btnSpin .6s linear infinite;
            margin-left: 8px;
            vertical-align: middle;
        }
        @keyframes btnSpin { to { transform: rotate(360deg); } }

        /* ══ Ripple effect au tap ══ */
        .ripple-host { position: relative; overflow: hidden; }
        .ripple-wave {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,.35);
            transform: scale(0); animation: rippleAnim .5s linear;
            pointer-events: none;
        }
        @keyframes rippleAnim { to { transform: scale(4); opacity: 0; } }

        /* ══ Skeleton Shimmer — images en cours de chargement ══ */
        .sk-active {
            position: relative !important;
            overflow: hidden;
        }
        .sk-active::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg,
                #f0f0f0 0%,
                #e0e0e0 40%,
                #f0f0f0 80%
            );
            background-size: 200% 100%;
            animation: sk-shimmer 1.4s ease-in-out infinite;
            z-index: 5;
            border-radius: inherit;
            pointer-events: none;
        }
        @keyframes sk-shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        @media (prefers-color-scheme: dark) {
            .sk-active::after {
                background: linear-gradient(90deg, #1e293b 0%, #334155 40%, #1e293b 80%);
                background-size: 200% 100%;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="{{ $bodyClass ?? '' }}">

{{-- ══ Loader plein écran ══ --}}
<div id="pg-loader">
    <img id="pg-loader-logo" src="{{ asset('images/shopio-logo-192.png') }}" alt="Shopio" data-no-skeleton>
    <div id="pg-loader-spin"></div>
    <span id="pg-loader-txt">Chargement…</span>
</div>

    {{-- ═══ NAVBAR (cachée sur dashboard via body.is-dashboard) ═══ --}}
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top app-navbar">
        <div class="container-xxl">
             <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <img src="{{ asset('images/shopio-logo-192.png') }}" alt="Shopio" style="height:32px;width:auto;object-fit:contain">
                {{ config('app.name', 'Shopio') }}
            </a>
           

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#topnav" aria-controls="topnav"
                    aria-expanded="false" aria-label="Ouvrir la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="topnav" class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto"></ul>
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    {{-- Switcher FR / EN --}}
                    <li class="nav-item d-flex align-items-center me-2" style="gap:4px">
                        <a href="{{ route('language.switch', 'fr') }}"
                           class="lang-btn {{ app()->getLocale() === 'fr' ? 'active' : '' }}"
                           title="Français">FR</a>
                        <span style="color:rgba(255,255,255,.4);font-size:11px">|</span>
                        <a href="{{ route('language.switch', 'en') }}"
                           class="lang-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                           title="English">EN</a>
                    </li>
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Connexion
                            </a>
                        </li>
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                            <a class="btn btn-light text-success fw-semibold" href="{{ route('register') }}">
                                <i class="bi bi-pencil-square me-1"></i> Inscription
                            </a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                               id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <span class="d-none d-sm-inline">{{ Str::limit(auth()->user()->name, 22) }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    {{-- ═══ TITRE OPTIONNEL (pages normales seulement) ═══ --}}
    @isset($title)
        @if(!isset($bodyClass) || !str_contains($bodyClass, 'is-dashboard'))
        <header class="page-header">
            <div class="container-xxl">
                <h1>{{ $title }}</h1>
            </div>
        </header>
        @endif
    @endisset

    {{-- ═══ FLASH (pages normales seulement) ═══ --}}
    <div class="container-xxl mt-3 app-flash">
        @foreach (['success','info','warning','danger'] as $type)
            @if(session($type))
                <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                    {{ session($type) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif
        @endforeach
    </div>

    {{-- ═══ CONTENU ═══ --}}
    <main class="app-main container-xxl py-4">
        @yield('content')
    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.js" defer></script>
    @stack('scripts')

{{-- ══ Célébration : objectif journalier du livreur atteint (gamification) ══ --}}
@auth
@if(session('daily_bonus_unlocked'))
<div id="bonusCelebration" style="position:fixed;inset:0;z-index:100000;background:rgba(15,23,42,.75);display:flex;align-items:center;justify-content:center;padding:20px;animation:bonusFadeIn .25s ease;">
    <div style="background:linear-gradient(135deg,#f59e0b,#fbbf24);border-radius:24px;padding:36px 28px;max-width:340px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.4);animation:bonusPop .4s cubic-bezier(.23,1,.32,1);">
        <div style="font-size:56px;line-height:1;margin-bottom:10px;">🎉</div>
        <div style="font-size:20px;font-weight:900;color:#78350f;margin-bottom:6px;">Objectif du jour atteint !</div>
        <div style="font-size:14px;color:#92400e;line-height:1.5;margin-bottom:20px;">
            Vous avez livré {{ \App\Services\GamificationService::DAILY_GOAL }} commandes aujourd'hui.<br>
            Prime débloquée : <strong>{{ number_format(\App\Services\GamificationService::DAILY_BONUS, 0, ',', ' ') }}</strong> !
        </div>
        <button onclick="document.getElementById('bonusCelebration').remove()" style="background:#78350f;color:#fff;border:none;border-radius:12px;padding:12px 28px;font-size:14px;font-weight:700;cursor:pointer;">
            Continuer 🚀
        </button>
    </div>
</div>
<style>
@keyframes bonusFadeIn { from{opacity:0} to{opacity:1} }
@keyframes bonusPop { from{opacity:0;transform:scale(.85)} to{opacity:1;transform:scale(1)} }
</style>
@endif
@endauth

{{-- ══ Bandeau activation notifications push ══ --}}
@auth
@if(in_array(auth()->user()->role ?? '', ['admin', 'company', 'client', 'vendeur', 'employe', 'livreur']))
@if(!session('push_banner_dismissed'))
<div id="pushNotifBanner" style="display:none;position:fixed;bottom:80px;left:50%;transform:translateX(-50%);z-index:9999;width:calc(100% - 32px);max-width:420px;background:#1e293b;color:#fff;border-radius:16px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,.35);align-items:center;gap:12px;">
    <span style="font-size:22px">🔔</span>
    <div style="flex:1;font-size:13px;line-height:1.4">
        <strong>Activez les notifications</strong><br>
        <span style="opacity:.8">Recevez vos commandes en temps réel</span>
    </div>
    <button onclick="document.getElementById('pushNotifBanner').remove();enablePushNotifications();" style="background:#059669;color:#fff;border:none;border-radius:10px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">Activer</button>
    @if(!in_array(auth()->user()->role ?? '', ['livreur', 'company']))
    <button onclick="document.getElementById('pushNotifBanner').remove();sessionStorage.setItem('push_banner_hidden','1')" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer;padding:0 4px;">✕</button>
    @endif
</div>
<script>
(function() {
    if (localStorage.getItem('push_subscribed')) return;

    // Depuis iPadOS 13, Safari sur iPad se présente comme un Mac de bureau dans le user-agent
    // (pas de "iPad" dedans) — on le détecte via le support tactile multi-points, propre aux iPad.
    var isIos = /iphone|ipad|ipod/i.test(navigator.userAgent)
             || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var isStandalone = window.navigator.standalone === true
                    || window.matchMedia('(display-mode: standalone)').matches;
    var role = '{{ auth()->user()->role ?? "" }}';
    var isDeliveryRole = (role === 'livreur' || role === 'company');

    // Sur iOS navigateur : PushManager absent → guider vers l'installation de l'app
    if (isIos && !isStandalone) {
        var banner = document.getElementById('pushNotifBanner');
        if (!banner) return;
        var textEl = banner.querySelector('div[style*="flex:1"]');
        var btnActiver = banner.querySelector('button:first-of-type');
        if (textEl) {
            textEl.innerHTML = '<strong>Installez l\'application</strong><br><span style="opacity:.8">Pour recevoir vos commandes en temps réel</span>';
        }
        if (btnActiver) {
            btnActiver.textContent = 'Installer';
            btnActiver.onclick = function() {
                banner.remove();
                localStorage.removeItem('pwa_dismissed');
                // Afficher les instructions iOS dans le modal
                var hint = document.getElementById('pwaIosHint');
                var installBtn = document.getElementById('pwaInstallBtn');
                if (hint) hint.style.display = 'block';
                if (installBtn) installBtn.style.display = 'none';
                if (typeof pwaShowModal === 'function') pwaShowModal();
            };
        }
        setTimeout(function() { banner.style.display = 'flex'; }, 2000);
        return;
    }

    if (!('PushManager' in window)) return;
    if (Notification.permission === 'denied') return;

    // Livreur / entreprise : s'abonner automatiquement si permission déjà accordée
    if (isDeliveryRole && Notification.permission === 'granted') {
        navigator.serviceWorker.ready.then(function(reg) {
            reg.pushManager.getSubscription().then(function(sub) {
                if (!sub) {
                    reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: _b64ToUint8(window._vapidKey) })
                        .then(function(newSub) { _savePushSub(newSub); })
                        .catch(function() {});
                } else {
                    _savePushSub(sub);
                }
            });
        });
        return;
    }

    // Pour livreur/company : banner non-fermable, re-affiché à chaque session
    if (isDeliveryRole || !sessionStorage.getItem('push_banner_hidden')) {
        setTimeout(function() {
            var b = document.getElementById('pushNotifBanner');
            if (b) b.style.display = 'flex';
        }, 2000);
    }
})();
</script>
@endif
@endif
@endauth

{{-- ══ PWA : Modal d'installation ══ --}}
<div id="pwaModal" style="
    display:none;position:fixed;inset:0;z-index:99999;
    background:rgba(0,0,0,.55);backdrop-filter:blur(4px);
    align-items:flex-end;justify-content:center;padding:0 0 0 0;
">
    <div style="
        background:#fff;border-radius:24px 24px 0 0;
        padding:28px 24px 36px;width:100%;max-width:480px;
        box-shadow:0 -8px 40px rgba(0,0,0,.18);
        animation:pwaSlideUp .35s cubic-bezier(.23,1,.32,1);
    ">
        <div style="width:48px;height:5px;background:#e5e7eb;border-radius:99px;margin:0 auto 22px;"></div>
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:18px">
            <img src="{{ asset('images/shopio-logo-192.png') }}"
                 style="width:64px;height:64px;border-radius:16px;object-fit:cover;box-shadow:0 4px 14px rgba(5,150,105,.25)"
                 alt="Shopio">
            <div>
                <div style="font-size:19px;font-weight:800;color:#0f172a">Shopio</div>
                <div style="font-size:13px;color:#6b7280;margin-top:2px">Marketplace Africaine</div>
            </div>
        </div>
        <p style="font-size:14px;color:#374151;line-height:1.6;margin-bottom:22px">
            Installez <strong>Shopio</strong> sur votre téléphone pour un accès rapide, des notifications en temps réel et une expérience native.
        </p>

        {{-- Instructions iOS --}}
        <div id="pwaIosHint" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 14px;margin-bottom:18px;font-size:13px;color:#166534;line-height:1.6">
            Sur iPhone : appuyez sur <strong>⬆ Partager</strong> puis <strong>"Sur l'écran d'accueil"</strong>
        </div>

        <button id="pwaInstallBtn" onclick="pwaInstall()" style="
            width:100%;padding:14px;border:none;border-radius:14px;
            background:linear-gradient(135deg,#059669,#16a34a);
            color:#fff;font-size:16px;font-weight:700;cursor:pointer;
            box-shadow:0 4px 16px rgba(5,150,105,.35);margin-bottom:12px;
            display:flex;align-items:center;justify-content:center;gap:8px;
        ">
            📲 Installer l'application
        </button>
        <button onclick="pwaDismiss()" style="
            width:100%;padding:12px;border:1.5px solid #e5e7eb;border-radius:14px;
            background:#fff;color:#6b7280;font-size:14px;font-weight:600;cursor:pointer;
        ">
            Peut-être plus tard
        </button>
    </div>
</div>

<style>
@keyframes pwaSlideUp {
    from { transform: translateY(100%); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
</style>

<script>
/* ══ Page Loader — masquer après DOM + polices prêtes ══ */
(function () {
    var loader = document.getElementById('pg-loader');
    if (!loader) return;
    var done = false;

    function hide() {
        if (done) return;
        done = true;
        loader.classList.add('done');
        setTimeout(function () { loader.remove(); }, 350);
    }

    /* Attendre DOM + polices pour éviter le flash de texte non stylé */
    var domReady = document.readyState !== 'loading'
        ? Promise.resolve()
        : new Promise(function (r) { document.addEventListener('DOMContentLoaded', r); });

    var fontsReady = (document.fonts && document.fonts.ready)
        ? document.fonts.ready
        : Promise.resolve();

    Promise.all([domReady, fontsReady]).then(function () {
        /* 2 frames pour laisser le navigateur finaliser le rendu des fonts */
        requestAnimationFrame(function () {
            requestAnimationFrame(hide);
        });
    });

    /* Sécurité absolue : 4s max */
    setTimeout(hide, 4000);
})();

/* ══ NProgress — barre de progression instantanée ══ */
var _np = function(fn) { if (typeof NProgress !== 'undefined') fn(); };

document.addEventListener('click', function (e) {
    const link = e.target.closest('a[href]');
    if (!link) return;
    const href = link.getAttribute('href') || '';
    if (!href || href.startsWith('#') || href.startsWith('javascript') ||
        href.startsWith('mailto:') || href.startsWith('tel:') ||
        link.target === '_blank' || link.dataset.noprogress !== undefined) return;
    _np(() => NProgress.start());
});

document.addEventListener('submit', function (e) {
    if (e.target.dataset.ajax !== undefined) return;
    const btn = e.target.querySelector('[type="submit"]:not([data-no-loading])');
    if (btn) btn.classList.add('btn-loading');
    _np(() => NProgress.start());
});

window.addEventListener('pageshow', function () { _np(() => NProgress.done()); });
window.addEventListener('load', function () {
    if (typeof NProgress !== 'undefined') {
        NProgress.configure({ showSpinner: false, trickleSpeed: 150, minimum: 0.08 });
        NProgress.done();
    }
});

/* ══ Ripple effect sur boutons et liens ══ */
document.addEventListener('click', function (e) {
    const el = e.target.closest('a, button, .btn, [role="button"]');
    if (!el) return;

    el.classList.add('ripple-host');
    const r    = Math.max(el.offsetWidth, el.offsetHeight);
    const rect = el.getBoundingClientRect();
    const wave = document.createElement('span');
    wave.className = 'ripple-wave';
    wave.style.cssText = `
        width:${r}px; height:${r}px;
        left:${e.clientX - rect.left - r/2}px;
        top:${e.clientY  - rect.top  - r/2}px;
    `;
    el.appendChild(wave);
    setTimeout(() => wave.remove(), 550);
});

/* ══ Skeleton Shimmer — toutes les images du site ══ */
(function () {
    function applyToImg(img) {
        if (!img.src || img.src.startsWith('data:')) return;
        if (img.src.endsWith('.svg')) return;
        if (img.complete && img.naturalHeight > 0) return; // déjà en cache
        if (img.dataset.noSkeleton !== undefined) return;  // exclusion manuelle

        const parent = img.parentElement;
        if (!parent) return;

        parent.classList.add('sk-active');
        img.style.opacity  = '0';
        img.style.transition = 'opacity 0.35s ease';

        const done = () => {
            parent.classList.remove('sk-active');
            img.style.opacity = '1';
        };

        img.addEventListener('load',  done, { once: true });
        img.addEventListener('error', done, { once: true });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('img').forEach(applyToImg);

        /* Images ajoutées dynamiquement (polling, chat, etc.) */
        new MutationObserver(function (muts) {
            muts.forEach(function (m) {
                m.addedNodes.forEach(function (n) {
                    if (n.nodeName === 'IMG') applyToImg(n);
                    else if (n.querySelectorAll) n.querySelectorAll('img').forEach(applyToImg);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    });
})();

/* ══ PWA — Service Worker + Install Prompt + Badge ══ */
let _pwaPrompt = null;

/* ── Enregistrement du Service Worker ── */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => {
                console.log('[SW] Enregistré');
                /* Vérifier les mises à jour */
                reg.addEventListener('updatefound', () => {
                    const newSW = reg.installing;
                    newSW?.addEventListener('statechange', () => {
                        if (newSW.state === 'installed' && navigator.serviceWorker.controller) {
                            newSW.postMessage({ type: 'SKIP_WAITING' });
                        }
                    });
                });
            })
            .catch(e => console.warn('[SW] Erreur:', e));
    });
}

/* ── Détecter si déjà installée (standalone) ── */
const isStandalone = window.navigator.standalone === true
                  || window.matchMedia('(display-mode: standalone)').matches;

@if(session('google_just_authed'))
/* Vient de Google OAuth → reset le rejet PWA pour que le modal s'affiche */
if (!isStandalone) { localStorage.removeItem('pwa_dismissed'); }
@endif

/* ── Intercepter le prompt d'installation natif (Android/Chrome) ── */
window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    if (isStandalone) return; // déjà dans l'app installée
    _pwaPrompt = e;

    const dismissed = localStorage.getItem('pwa_dismissed');
    const tooSoon   = dismissed && (Date.now() - parseInt(dismissed)) < 1 * 24 * 60 * 60 * 1000;
    if (!tooSoon) {
        setTimeout(pwaShowModal, 3000);
    }
});

/* ── iOS : détecter et afficher les instructions ──
   Depuis iPadOS 13, Safari sur iPad se présente comme un Mac de bureau dans le user-agent
   (pas de "iPad" dedans) — on le détecte via le support tactile multi-points, propre aux iPad. */
const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent)
           || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

if (isIos && !isStandalone) {
    const dismissed = localStorage.getItem('pwa_dismissed');
    const tooSoon   = dismissed && (Date.now() - parseInt(dismissed)) < 1 * 24 * 60 * 60 * 1000;
    if (!tooSoon) {
        setTimeout(() => {
            document.getElementById('pwaIosHint').style.display = 'block';
            document.getElementById('pwaInstallBtn').style.display = 'none';
            pwaShowModal();
        }, 3000);
    }
}

function pwaShowModal() {
    const modal = document.getElementById('pwaModal');
    if (modal) modal.style.display = 'flex';
}

function pwaInstall() {
    if (!_pwaPrompt) return;
    _pwaPrompt.prompt();
    _pwaPrompt.userChoice.then(result => {
        if (result.outcome === 'accepted') {
            pwaDismiss();
        }
        _pwaPrompt = null;
    });
}

function pwaDismiss() {
    const modal = document.getElementById('pwaModal');
    if (modal) modal.style.display = 'none';
    localStorage.setItem('pwa_dismissed', Date.now());
}

/* Fermer en cliquant sur le fond */
document.getElementById('pwaModal')?.addEventListener('click', function(e) {
    if (e.target === this) pwaDismiss();
});

/* ── Push Notifications ── */
/* Vide le flag local d'abonnement à chaque déconnexion, pour que le prochain
   utilisateur connecté sur ce même appareil resynchronise proprement son
   propre abonnement push (sinon le flag reste "activé" pour l'ancien compte). */
document.addEventListener('submit', function(e) {
    if (e.target && e.target.action && e.target.action.indexOf('/logout') !== -1) {
        localStorage.removeItem('push_subscribed');
    }
});

@auth
window._vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;

function _b64ToUint8(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
}

async function _savePushSub(sub) {
    const key  = sub.getKey('p256dh');
    const auth = sub.getKey('auth');
    await fetch('/push/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            endpoint:   sub.endpoint,
            public_key: key  ? btoa(String.fromCharCode(...new Uint8Array(key)))  : null,
            auth_token: auth ? btoa(String.fromCharCode(...new Uint8Array(auth))) : null,
        }),
    });
    localStorage.setItem('push_subscribed', '1');
    document.getElementById('pushNotifBanner')?.remove();
}

/* Appelé quand l'utilisateur clique sur "Activer" */
window.enablePushNotifications = async function() {
    if (!window._vapidKey || !('PushManager' in window) || !('serviceWorker' in navigator)) return;
    try {
        const perm = Notification.permission === 'granted'
            ? 'granted'
            : await Notification.requestPermission();
        if (perm !== 'granted') return;
        const reg = await navigator.serviceWorker.ready;
        let sub = await reg.pushManager.getSubscription();
        if (!sub) {
            sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: _b64ToUint8(window._vapidKey),
            });
        }
        await _savePushSub(sub);
    } catch(e) {
        console.warn('[Push] Échec:', e);
    }
};

/* Si déjà accordé mais pas encore sauvegardé en base → s'abonner silencieusement */
if ('PushManager' in window && 'serviceWorker' in navigator && !localStorage.getItem('push_subscribed')) {
    navigator.serviceWorker.ready.then(async reg => {
        if (Notification.permission === 'granted') {
            try {
                let sub = await reg.pushManager.getSubscription();
                if (!sub) {
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: _b64ToUint8(window._vapidKey),
                    });
                }
                await _savePushSub(sub);
            } catch(e) {}
        }
    });
}

@if(in_array(auth()->user()->role ?? '', ['company', 'livreur']))
/* Toujours rafraîchir la subscription en base à chaque page (anti-expiration) */
if ('PushManager' in window && 'serviceWorker' in navigator && Notification.permission === 'granted') {
    navigator.serviceWorker.ready.then(async function(reg) {
        try {
            let sub = await reg.pushManager.getSubscription();
            if (!sub) {
                sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: _b64ToUint8(window._vapidKey),
                });
            }
            if (sub) await _savePushSub(sub);
        } catch(e) {}
    });
}
@endif
@endauth

/* ── Badge API : met à jour le badge de l'icône ── */
window.updatePwaBadge = function(count) {
    count = parseInt(count) || 0;
    /* Via Service Worker */
    if (navigator.serviceWorker?.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'SET_BADGE', count });
    }
    /* Direct (Chrome 81+) */
    if ('setAppBadge' in navigator) {
        count > 0
            ? navigator.setAppBadge(count).catch(() => {})
            : navigator.clearAppBadge().catch(() => {});
    }
};

/* ── Badge : mis à jour depuis le polling existant ── */
@auth
@if(auth()->user()->role === 'client')
async function pollGlobalBadge() {
    try {
        const res = await fetch('/client/notifications/poll?_t=' + Date.now(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data  = await res.json();
        const total = (data.messages_unread || 0) + (data.order_updates_unseen || 0);
        updatePwaBadge(total);
    } catch(e) {}
}
setInterval(pollGlobalBadge, 30000);
pollGlobalBadge();
@endif

@if(in_array(auth()->user()->role ?? '', ['vendeur', 'employe']))
async function pollGlobalBadgeVendeur() {
    try {
        const res = await fetch('/boutique/notifications/poll', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json',
                       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        if (!res.ok) return;
        const d = await res.json();
        updatePwaBadge((d.messages_unread || 0) + (d.orders_pending || 0));
    } catch(e) {}
}
setInterval(pollGlobalBadgeVendeur, 30000);
pollGlobalBadgeVendeur();
@endif


@if(auth()->user()->role === 'company')
async function pollGlobalBadgeCompany() {
    try {
        const res = await fetch('/company/orders/notifications', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const d = await res.json();
        if (d.ok) updatePwaBadge(d.total_pending || 0);
    } catch(e) {}
}
setInterval(pollGlobalBadgeCompany, 30000);
pollGlobalBadgeCompany();
@endif

@if(in_array(auth()->user()->role ?? '', ['admin', 'client', 'vendeur', 'employe', 'livreur']))
/* Effacer le badge dès que l'utilisateur ouvre l'app */
updatePwaBadge(0);
@endif
@endauth
</script>

</body>
</html>