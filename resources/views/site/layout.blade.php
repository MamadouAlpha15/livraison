{{--
    resources/views/site/layout.blade.php
    Gabarit des pages publiques de Shopio hors page d'accueil (Académie…) : même police (Geist), même menu et
    même pied de page que la page d'accueil (landing.blade.php), une seule couleur d'accent.
    Sections : title, description, content ; pile « styles ».
--}}
@php
    $logo = \App\Support\Assets::v('images/shopio-logo-192.png');
    $siteCta = auth()->check() && auth()->user()->role === 'admin'
        ? [auth()->user()->shop_id ? route('boutique.dashboard') : route('boutique.shops.create'), auth()->user()->shop_id ? 'Mon tableau de bord' : 'Créer ma boutique']
        : [route('register', ['role' => 'admin']), 'Créer ma boutique'];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>@yield('title') · Shopio</title>
<meta name="description" content="@yield('description')">
<link rel="canonical" href="{{ url()->current() }}">
<link rel="icon" href="{{ $logo }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root { --ink: #0F172A; --ink-2: #475569; --ink-3: #94A3B8; --line: #E2E8F0; --soft: #F8FAFC; --soft-2: #F1F5F9; --accent: #1F7CF6; --accent-dk: #1667D6; --accent-50: #EFF6FF; --ok: #16A34A; --font: 'Geist', 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; }
*, *::before, *::after { box-sizing: border-box; }
body { margin: 0; background: #fff; color: var(--ink); font-family: var(--font); -webkit-font-smoothing: antialiased; line-height: 1.6; }
a { color: inherit; }
.wrap { width: 100%; max-width: 1080px; margin: 0 auto; padding: 0 20px; }
:focus-visible { outline: 3px solid #93C5FD; outline-offset: 2px; border-radius: 6px; }
.s-nav { position: sticky; top: 0; z-index: 50; background: rgba(255,255,255,.92); backdrop-filter: saturate(1.4) blur(12px); -webkit-backdrop-filter: saturate(1.4) blur(12px); border-bottom: 1px solid var(--line); }
.s-nav .wrap { display: flex; align-items: center; gap: 16px; height: 64px; }
.s-logo { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 18px; text-decoration: none; letter-spacing: -.02em; }
.s-logo img { width: 32px; height: 32px; border-radius: 9px; }
.s-links { display: flex; gap: 4px; margin-left: 10px; }
.s-links a { padding: 8px 12px; border-radius: 9px; color: var(--ink-2); font-weight: 600; font-size: 14.5px; text-decoration: none; }
.s-links a:hover, .s-links a[aria-current] { background: var(--soft-2); color: var(--ink); }
.s-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 42px; padding: 0 18px; border: 0; border-radius: 11px; font: 700 14.5px var(--font); text-decoration: none; cursor: pointer; white-space: nowrap; }
.s-btn-main { background: var(--accent); color: #fff; } .s-btn-main:hover { background: var(--accent-dk); }
.s-btn-line { background: #fff; color: var(--ink); box-shadow: inset 0 0 0 1.5px var(--line); }
.s-nav .s-btn { margin-left: auto; }
@media (max-width: 720px) { .s-links { display: none; } .s-nav .s-btn { padding: 0 14px; font-size: 13.5px; } }
.s-foot { margin-top: 70px; padding: 30px 0 calc(30px + env(safe-area-inset-bottom, 0px)); border-top: 1px solid var(--soft-2); color: var(--ink-2); font-size: 14px; }
.s-foot .wrap { display: flex; flex-wrap: wrap; gap: 12px 24px; align-items: center; }
.s-foot nav { display: flex; flex-wrap: wrap; gap: 6px 18px; margin-left: auto; }
.s-foot a { text-decoration: none; font-weight: 600; } .s-foot a:hover { color: var(--ink); }
</style>
@stack('styles')
</head>
<body>
<header class="s-nav">
    <div class="wrap">
        <a class="s-logo" href="{{ route('landing') }}"><img src="{{ $logo }}" alt="" width="32" height="32">Shopio</a>
        <nav class="s-links" aria-label="Menu">
            <a href="{{ route('landing') }}">Accueil</a>
            <a href="{{ route('academy.index') }}" @if(request()->routeIs('academy.*')) aria-current="page" @endif>Académie</a>
            <a href="{{ route('landing') }}#tarifs">Tarifs</a>
            <a href="{{ route('welcome') }}">Marché</a>
        </nav>
        <a class="s-btn s-btn-main" href="{{ $siteCta[0] }}">{{ $siteCta[1] }}</a>
    </div>
</header>
<main>@yield('content')</main>
<footer class="s-foot">
    <div class="wrap">
        <a class="s-logo" href="{{ route('landing') }}"><img src="{{ $logo }}" alt="" width="26" height="26">Shopio</a>
        <span>© {{ now()->year }} Shopio</span>
        <nav aria-label="Liens">
            <a href="{{ route('academy.index') }}">Académie</a>
            <a href="{{ route('welcome') }}">Marché</a>
            <a href="{{ route('legal.terms') }}">Conditions d’utilisation</a>
        </nav>
    </div>
</footer>
</body>
</html>
