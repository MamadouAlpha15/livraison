{{--
    resources/views/dashboards/client.blade.php
    Route     : GET /client/dashboard → Client\DashboardController@index
    Variables :
      $shops  → LengthAwarePaginator<Shop>
      Auth::user() → client connecté
--}}
@extends('layouts.app')
@section('title', 'Mes Boutiques — Marketplace')
@php $bodyClass = 'client-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --ink:       #0a0f0d;
    --ink-2:     #1e2b25;
    --ink-3:     #3d4f46;
    --muted:     #7a9088;
    --fog:       #e8efec;
    --fog-lt:    #f4f8f6;
    --surface:   #ffffff;
    --border:    rgba(0,0,0,.07);

    --lime:      #b5f23c;
    --lime-dk:   #8dc42a;
    --lime-lt:   #edfab8;
    --teal:      #0ea472;
    --teal-lt:   #d1f5e8;
    --amber:     #f5a623;
    --amber-lt:  #fef3d7;
    --rose:      #f24f60;
    --rose-lt:   #ffe0e3;
    --blue:      #3b82f6;
    --blue-lt:   #dbeafe;

    --font:      'DM Sans', sans-serif;
    --display:   'Syne', sans-serif;
    --r:         16px;
    --r-sm:      10px;
    --r-xs:      6px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow:    0 4px 20px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
    --shadow-lg: 0 12px 40px rgba(0,0,0,.12);
    --nav-h:     64px;
}

html { font-family: var(--font); scroll-behavior: smooth; }
body { background: var(--fog-lt); margin: 0; color: var(--ink); -webkit-font-smoothing: antialiased; }

/* ══════════════════════════════════════════
   NAVBAR
══════════════════════════════════════════ */
.c-nav {
    position: sticky; top: 0; z-index: 100;
    height: var(--nav-h);
    background: rgba(255,255,255,.88);
    backdrop-filter: blur(16px) saturate(160%);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 24px; gap: 16px;
}
.c-nav-logo {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none; color: var(--ink);
    font-family: var(--display); font-weight: 800;
    font-size: 18px; letter-spacing: -.4px; flex-shrink: 0;
}
.c-nav-logo-dot {
    width: 32px; height: 32px; border-radius: 9px;
    background: var(--ink); color: var(--lime);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}

/* Barre de recherche centrale */
.c-nav-search {
    flex: 1; max-width: 480px; margin: 0 auto;
    position: relative;
}
.c-nav-search-input {
    width: 100%; padding: 10px 16px 10px 42px;
    border: 1.5px solid var(--border);
    border-radius: 50px;
    font-size: 13.5px; font-family: var(--font);
    background: var(--fog-lt); color: var(--ink);
    outline: none; transition: all .2s;
}
.c-nav-search-input:focus {
    border-color: var(--teal);
    background: var(--surface);
    box-shadow: 0 0 0 3px rgba(14,164,114,.1);
}
.c-nav-search-ico {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    font-size: 15px; pointer-events: none; color: var(--muted);
}

/* Actions nav */
.c-nav-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.c-nav-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px; border-radius: 50px;
    font-size: 12.5px; font-weight: 600; font-family: var(--font);
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--ink-3); cursor: pointer; text-decoration: none;
    transition: all .15s; white-space: nowrap;
}
.c-nav-btn:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-lt); }
.c-nav-btn.primary { background: var(--ink); color: #fff; border-color: var(--ink); }
.c-nav-btn.primary:hover { background: var(--ink-2); border-color: var(--ink-2); color: #fff; }

/* Avatar client */
.c-nav-av {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--teal), #0d7a55);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800; color: #fff;
    font-family: var(--display);
    cursor: pointer; flex-shrink: 0;
    border: 2px solid var(--surface);
    box-shadow: 0 0 0 2px var(--teal);
    position: relative;
}
.c-nav-av-menu {
    position: absolute; top: calc(100% + 10px); right: 0;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); box-shadow: var(--shadow-lg);
    min-width: 200px; padding: 8px;
    display: none; z-index: 200;
}
.c-nav-av-menu.open { display: block; animation: dropIn .18s ease; }
@keyframes dropIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
.c-nav-av-menu a, .c-nav-av-menu button {
    display: flex; align-items: center; gap: 9px;
    padding: 9px 12px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 500; color: var(--ink-3);
    text-decoration: none; background: none; border: none;
    width: 100%; cursor: pointer; font-family: var(--font);
    transition: background .12s;
}
.c-nav-av-menu a:hover, .c-nav-av-menu button:hover { background: var(--fog-lt); color: var(--ink); }
.c-nav-av-menu .sep { height: 1px; background: var(--border); margin: 4px 0; }
.c-nav-av-menu .logout { color: var(--rose); }
.c-nav-av-menu .logout:hover { background: var(--rose-lt); }

/* ══════════════════════════════════════════
   HERO SECTION
══════════════════════════════════════════ */
.c-hero {
    background: var(--ink);
    padding: 52px 24px 0;
    position: relative;
    overflow: hidden;
}
.c-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 80% 60% at 60% 0%, rgba(14,164,114,.25) 0%, transparent 65%),
                radial-gradient(ellipse 50% 80% at 95% 50%, rgba(181,242,60,.12) 0%, transparent 60%);
    pointer-events: none;
}
.c-hero-inner {
    max-width: 1200px; margin: 0 auto;
    display: flex; align-items: flex-end; gap: 40px;
    position: relative; z-index: 1;
}
.c-hero-text { flex: 1; padding-bottom: 36px; }
.c-hero-greeting {
    font-size: 12px; font-weight: 600; letter-spacing: 1.5px;
    text-transform: uppercase; color: var(--teal);
    margin-bottom: 12px; display: flex; align-items: center; gap: 8px;
}
.c-hero-greeting::before {
    content: '';
    display: inline-block; width: 24px; height: 2px;
    background: var(--teal); border-radius: 2px;
}
.c-hero-title {
    font-family: var(--display); font-weight: 800;
    font-size: clamp(32px, 5vw, 52px); color: #fff;
    letter-spacing: -.8px; line-height: 1.05; margin-bottom: 16px;
}
.c-hero-title .accent {
    color: var(--lime);
}
.c-hero-sub {
    font-size: 15px; color: rgba(255,255,255,.5);
    line-height: 1.7; max-width: 480px; margin-bottom: 28px;
}
.c-hero-stats {
    display: flex; gap: 28px; flex-wrap: wrap;
}
.c-hero-stat {}
.c-hero-stat-val {
    font-family: var(--display); font-size: 28px; font-weight: 800;
    color: #fff; letter-spacing: -1px; line-height: 1;
}
.c-hero-stat-lbl {
    font-size: 11px; color: rgba(255,255,255,.35);
    font-weight: 500; margin-top: 3px;
    text-transform: uppercase; letter-spacing: .5px;
}
/* Illustration droite */
.c-hero-right {
    flex-shrink: 0; width: 280px;
    display: flex; align-items: flex-end; gap: 12px;
    padding-bottom: 0;
}
.c-hero-card-preview {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: var(--r);
    overflow: hidden; flex: 1;
    transform: translateY(0);
    animation: floatCard 4s ease-in-out infinite;
}
.c-hero-card-preview:nth-child(2) {
    animation-delay: -2s;
    transform: translateY(-12px);
}
@keyframes floatCard {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-8px); }
}
.c-hero-card-img {
    height: 90px; background: linear-gradient(135deg, rgba(14,164,114,.3), rgba(181,242,60,.1));
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
}
.c-hero-card-body { padding: 10px 12px; }
.c-hero-card-name { font-size: 11px; font-weight: 700; color: #fff; margin-bottom: 3px; }
.c-hero-card-meta { font-size: 10px; color: rgba(255,255,255,.35); }
.c-hero-card-badge {
    display: inline-flex; align-items: center; gap: 3px;
    background: var(--lime); color: var(--ink);
    font-size: 9px; font-weight: 800; padding: 2px 7px;
    border-radius: 20px; margin-top: 6px;
}

/* ══════════════════════════════════════════
   MAIN CONTENT
══════════════════════════════════════════ */
.c-main {
    max-width: 1200px; margin: 0 auto;
    padding: 32px 24px 80px;
}

/* Section titre */
.c-sec-hd {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 20px; gap: 12px; flex-wrap: wrap;
}
.c-sec-title {
    font-family: var(--display); font-size: 18px; font-weight: 700;
    color: var(--ink); letter-spacing: -.3px;
    display: flex; align-items: center; gap: 10px;
}
.c-sec-title-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--teal); flex-shrink: 0;
}
.c-sec-link {
    font-size: 12.5px; font-weight: 600; color: var(--teal);
    text-decoration: none; display: flex; align-items: center; gap: 4px;
    transition: gap .15s;
}
.c-sec-link:hover { gap: 8px; }

/* ── Catégories pills ── */
.c-cats {
    display: flex; gap: 8px; margin-bottom: 28px;
    overflow-x: auto; padding-bottom: 4px;
    scrollbar-width: none;
}
.c-cats::-webkit-scrollbar { display: none; }
.c-cat-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 50px;
    font-size: 12.5px; font-weight: 600; font-family: var(--font);
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--ink-3); cursor: pointer; white-space: nowrap;
    transition: all .15s; text-decoration: none;
    flex-shrink: 0;
}
.c-cat-pill:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-lt); }
.c-cat-pill.active { background: var(--ink); color: #fff; border-color: var(--ink); }
.c-cat-pill .pill-ico { font-size: 14px; }

/* ── Grille boutiques ── */
.shops-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

/* ── Card boutique ── */
.shop-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .25s, transform .25s, border-color .25s;
    cursor: pointer;
    text-decoration: none; color: inherit;
    display: flex; flex-direction: column;
    position: relative;
}
.shop-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
    border-color: rgba(14,164,114,.25);
}

/* Bannière */
.shop-card-banner {
    height: 120px; position: relative; overflow: hidden;
    flex-shrink: 0;
}
.shop-card-banner-img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s ease;
}
.shop-card:hover .shop-card-banner-img { transform: scale(1.06); }
.shop-card-banner-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 36px;
}
/* Gradient de couleur selon type */
.bg-food     { background: linear-gradient(135deg, #1a2e1e, #2d5235); }
.bg-fashion  { background: linear-gradient(135deg, #2a1a2e, #4a2d5a); }
.bg-tech     { background: linear-gradient(135deg, #1a1e2e, #2d3552); }
.bg-beauty   { background: linear-gradient(135deg, #2e1a1e, #521a2d); }
.bg-default  { background: linear-gradient(135deg, #1a2e26, #2d4a3e); }

/* Badge ouvert/fermé */
.shop-status {
    position: absolute; top: 10px; left: 10px;
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 10px; font-weight: 700;
    padding: 4px 9px; border-radius: 20px;
    backdrop-filter: blur(8px);
}
.shop-status.open  { background: rgba(14,164,114,.85); color: #fff; }
.shop-status.open::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #fff; animation: pulse 1.8s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }
.shop-status.new   { background: rgba(181,242,60,.9); color: var(--ink); }

/* Logo boutique */
.shop-card-logo {
    position: absolute; bottom: -20px; left: 16px;
    width: 44px; height: 44px; border-radius: 12px;
    background: var(--surface); border: 2.5px solid var(--surface);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; box-shadow: var(--shadow-sm);
    overflow: hidden; flex-shrink: 0;
}
.shop-card-logo img { width: 100%; height: 100%; object-fit: cover; }

/* Corps */
.shop-card-body {
    padding: 28px 16px 14px;
    flex: 1; display: flex; flex-direction: column; gap: 8px;
}
.shop-card-type {
    font-size: 10px; font-weight: 700; color: var(--teal);
    text-transform: uppercase; letter-spacing: .8px;
}
.shop-card-name {
    font-family: var(--display); font-size: 15px; font-weight: 700;
    color: var(--ink); line-height: 1.3;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.shop-card-desc {
    font-size: 12px; color: var(--muted); line-height: 1.55;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.shop-card-meta {
    display: flex; align-items: center; gap: 10px;
    flex-wrap: wrap; margin-top: 4px;
}
.shop-card-chip {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 600; color: var(--ink-3);
    background: var(--fog-lt); border: 1px solid var(--border);
    padding: 3px 8px; border-radius: var(--r-xs);
}

/* Footer */
.shop-card-footer {
    padding: 12px 16px;
    border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    gap: 8px;
}
.shop-card-rating {
    display: flex; align-items: center; gap: 4px;
    font-size: 12px; font-weight: 700; color: var(--amber);
}
.shop-card-rating small { color: var(--muted); font-weight: 400; }
.shop-card-cta {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 50px;
    font-size: 12px; font-weight: 700; font-family: var(--font);
    background: var(--ink); color: #fff; border: none;
    cursor: pointer; text-decoration: none;
    transition: all .15s;
}
.shop-card-cta:hover {
    background: var(--teal); transform: scale(1.04);
}

/* ── Section Mes commandes récentes ── */
.orders-strip {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 32px;
}
.orders-strip-hd {
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--fog-lt);
}
.order-row {
    display: flex; align-items: center; gap: 14px;
    padding: 13px 20px;
    border-bottom: 1px solid #f3f6f4;
    transition: background .12s;
}
.order-row:last-child { border-bottom: none; }
.order-row:hover { background: var(--fog-lt); }
.order-ico {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.order-info { flex: 1; min-width: 0; }
.order-ref  { font-size: 12px; font-weight: 700; color: var(--ink); font-family: monospace; }
.order-shop { font-size: 11.5px; color: var(--muted); margin-top: 1px; }
.order-amount { font-family: monospace; font-weight: 700; font-size: 13px; color: var(--ink); white-space: nowrap; }
.order-pill {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px;
}
.pill-livree   { background: var(--teal-lt); color: #065f46; }
.pill-pending  { background: var(--amber-lt); color: #92400e; }
.pill-livraison { background: var(--blue-lt); color: #1e40af; }
.pill-cancelled { background: var(--rose-lt); color: #991b1b; }

/* ── Bannière promo ── */
.promo-banner {
    background: var(--ink);
    border-radius: var(--r);
    padding: 28px 32px;
    display: flex; align-items: center; gap: 24px;
    margin-bottom: 32px; overflow: hidden; position: relative;
}
.promo-banner::before {
    content: '';
    position: absolute; right: -40px; top: -40px;
    width: 200px; height: 200px; border-radius: 50%;
    background: rgba(181,242,60,.08);
}
.promo-banner::after {
    content: '';
    position: absolute; right: 60px; bottom: -60px;
    width: 150px; height: 150px; border-radius: 50%;
    background: rgba(14,164,114,.1);
}
.promo-ico { font-size: 40px; flex-shrink: 0; position: relative; z-index: 1; }
.promo-text { flex: 1; min-width: 0; position: relative; z-index: 1; }
.promo-title {
    font-family: var(--display); font-size: 18px; font-weight: 800;
    color: #fff; margin-bottom: 5px; letter-spacing: -.3px;
}
.promo-title .hl { color: var(--lime); }
.promo-sub { font-size: 13px; color: rgba(255,255,255,.5); }
.promo-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 11px 22px; border-radius: 50px;
    font-size: 13px; font-weight: 700; font-family: var(--font);
    background: var(--lime); color: var(--ink); border: none;
    cursor: pointer; text-decoration: none; flex-shrink: 0;
    position: relative; z-index: 1;
    transition: all .15s;
}
.promo-btn:hover { background: #c8f54e; transform: scale(1.04); }

/* ── Flash messages ── */
.c-flash {
    padding: 12px 16px; border-radius: var(--r-sm);
    border: 1px solid; font-size: 13px; font-weight: 500;
    margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
}
.c-flash-success { background: var(--teal-lt); border-color: #6ee7b7; color: #065f46; }
.c-flash-danger  { background: var(--rose-lt); border-color: #fca5a5; color: #991b1b; }

/* ── Pagination ── */
.c-pagination { display: flex; justify-content: center; padding: 8px 0; }

/* ── État vide ── */
.c-empty {
    grid-column: 1/-1; padding: 80px 20px;
    text-align: center;
}
.c-empty-ico { font-size: 56px; display: block; opacity: .3; margin-bottom: 14px; }
.c-empty-title { font-family: var(--display); font-size: 20px; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
.c-empty-sub { font-size: 14px; color: var(--muted); }

/* ══════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════ */
@media (max-width: 900px) {
    .c-hero-right { display: none; }
    .c-hero { padding: 36px 20px 0; }
    .c-main { padding: 24px 16px 60px; }
    .shops-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 14px; }
}
@media (max-width: 640px) {
    .c-nav { padding: 0 14px; }
    .c-nav-search { display: none; }
    .c-hero-title { font-size: 28px; }
    .c-hero { padding: 28px 14px 0; }
    .c-main { padding: 18px 12px 50px; }
    .shops-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .shop-card-body { padding: 22px 12px 10px; }
    .shop-card-footer { padding: 10px 12px; }
    .promo-banner { flex-direction: column; padding: 22px 20px; text-align: center; }
    .order-row { padding: 11px 14px; gap: 10px; }
    .c-hero-stats { gap: 20px; }
}
@media (max-width: 400px) {
    .shops-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

@php
    $user     = auth()->user();
    $parts    = explode(' ', $user->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $firstName = $parts[0];

    /* Heure de la journée */
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');

    /* $recentOrders vient du contrôleur DashboardController */

    /* Statut → label + couleur */
    $statusMap = [
        'livrée'       => ['pill-livree',    '✓ Livrée'],
        'pending'      => ['pill-pending',   '⏳ En attente'],
        'en attente'   => ['pill-pending',   '⏳ En attente'],
        'en_attente'   => ['pill-pending',   '⏳ En attente'],
        'confirmée'    => ['pill-livraison', '✓ Confirmée'],
        'en_livraison' => ['pill-livraison', '🚴 En livraison'],
        'annulée'      => ['pill-cancelled', '✕ Annulée'],
        'cancelled'    => ['pill-cancelled', '✕ Annulée'],
    ];

    /* Icônes par type boutique */
    $typeIco = [
        'Alimentation' => ['🥩', 'bg-food'],   'Restaurant' => ['🍽️', 'bg-food'],
        'Épicerie' => ['🛒', 'bg-food'],        'Boulangerie' => ['🥖', 'bg-food'],
        'Vêtements' => ['👗', 'bg-fashion'],    'Bijouterie' => ['💎', 'bg-fashion'],
        'Électronique' => ['📱', 'bg-tech'],    'Informatique' => ['💻', 'bg-tech'],
        'Téléphonie' => ['📞', 'bg-tech'],      'Beauté & Cosmétiques' => ['💄', 'bg-beauty'],
        'Pharmacie' => ['💊', 'bg-beauty'],
    ];
    $categories = [
        'Toutes'   => '🏪',
        'Alimentation' => '🍽️',
        'Mode'     => '👗',
        'Tech'     => '📱',
        'Beauté'   => '💄',
        'Épicerie' => '🛒',
        'Autre'    => '🔖',
    ];
@endphp

{{-- ══════ NAVBAR ══════ --}}
<nav class="c-nav">
    <a href="{{ route('client.dashboard') }}" class="c-nav-logo">
        <div class="c-nav-logo-dot">🛍</div>
        ShopHub
    </a>

    {{-- Recherche --}}
    <div class="c-nav-search">
        <span class="c-nav-search-ico">🔍</span>
        <input type="text" class="c-nav-search-input"
               id="globalSearch"
               placeholder="Rechercher une boutique, un produit…"
               autocomplete="off">
    </div>

    <div class="c-nav-actions">
        {{-- Mes commandes --}}
        <a href="{{ route('client.orders.index') }}" class="c-nav-btn">
            📦 Mes commandes
        </a>

        {{-- Avatar + menu --}}
        <div style="position:relative">
            <div class="c-nav-av" id="navAvatar" onclick="toggleAvatarMenu()">
                {{ $initials }}
            </div>
            <div class="c-nav-av-menu" id="avatarMenu">
                <div style="padding:10px 12px 8px;border-bottom:1px solid var(--border);margin-bottom:4px">
                    <div style="font-size:13px;font-weight:700;color:var(--ink)">{{ $user->name }}</div>
                    <div style="font-size:11px;color:var(--muted)">{{ $user->email }}</div>
                </div>
                <a href="{{ route('profile.edit') }}">👤 Mon profil</a>
                <a href="{{ route('client.orders.index') }}">📦 Mes commandes</a>
                <div class="sep"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout">⎋ Se déconnecter</button>
                </form>
            </div>
        </div>
    </div>
</nav>

{{-- ══════ HERO ══════ --}}
<section class="c-hero">
    <div class="c-hero-inner">
        <div class="c-hero-text">
            <div class="c-hero-greeting">{{ $greeting }}, {{ $firstName }}</div>
            <h1 class="c-hero-title">
                Commandez auprès<br>des <span class="accent">meilleures</span><br>boutiques
            </h1>
            <p class="c-hero-sub">
                Découvrez {{ $shops->total() }} boutiques locales. Passez votre commande
                en quelques secondes, livraison directement chez vous.
            </p>
            <div class="c-hero-stats">
                <div class="c-hero-stat">
                    <div class="c-hero-stat-val">{{ $shops->total() }}</div>
                    <div class="c-hero-stat-lbl">Boutiques actives</div>
                </div>
                <div class="c-hero-stat">
                    <div class="c-hero-stat-val">{{ $recentOrders->count() }}</div>
                    <div class="c-hero-stat-lbl">Mes commandes</div>
                </div>
                <div class="c-hero-stat">
                    <div class="c-hero-stat-val">🚀</div>
                    <div class="c-hero-stat-lbl">Livraison rapide</div>
                </div>
            </div>
        </div>

        {{-- Cartes flottantes illustration --}}
        <div class="c-hero-right">
            @foreach($shops->take(2) as $previewShop)
            @php [$ico, $bg] = $typeIco[$previewShop->type ?? ''] ?? ['🛍️', 'bg-default']; @endphp
            <div class="c-hero-card-preview">
                <div class="c-hero-card-img {{ $bg }}">
                    @if($previewShop->image)
                        <img src="{{ asset('storage/'.$previewShop->image) }}" style="width:100%;height:100%;object-fit:cover">
                    @else
                        {{ $ico }}
                    @endif
                </div>
                <div class="c-hero-card-body">
                    <div class="c-hero-card-name">{{ Str::limit($previewShop->name, 18) }}</div>
                    <div class="c-hero-card-meta">{{ $previewShop->type ?? 'Boutique' }}</div>
                    <div class="c-hero-card-badge">✓ Ouvert</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════ MAIN CONTENT ══════ --}}
<main class="c-main">

    {{-- Flash --}}
    @foreach(['success','danger'] as $t)
        @if(session($t))
        <div class="c-flash c-flash-{{ $t }}">
            <span>{{ $t === 'success' ? '✓' : '✕' }}</span>
            {{ session($t) }}
        </div>
        @endif
    @endforeach

    {{-- ── Commandes récentes (si client a commandé) ── --}}
    @if($recentOrders->isNotEmpty())
    <div style="margin-bottom:10px">
        <div class="c-sec-hd">
            <div class="c-sec-title">
                <span class="c-sec-title-dot"></span>
                Mes commandes récentes
            </div>
            <a href="{{ route('client.orders.index') }}" class="c-sec-link">
                Voir tout →
            </a>
        </div>
        <div class="orders-strip">
            <div class="orders-strip-hd">
                <span style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">
                    Historique
                </span>
                <span style="font-size:11px;color:var(--muted)">{{ $recentOrders->count() }} commande(s)</span>
            </div>
            @foreach($recentOrders as $order)
            @php
                $st = $statusMap[$order->status] ?? ['pill-pending', ucfirst($order->status)];
                $oIco = match($order->status) {
                    'livrée' => ['🎉', 'background:#d1fae5'],
                    'en_livraison', 'en livraison' => ['🚴', 'background:#dbeafe'],
                    'annulée', 'cancelled' => ['✕', 'background:#fee2e2'],
                    default  => ['📦', 'background:#fef3c7'],
                };
            @endphp
            <a href="{{ route('client.orders.index') }}"
               class="order-row" style="text-decoration:none">
                <div class="order-ico" style="{{ $oIco[1] }}">{{ $oIco[0] }}</div>
                <div class="order-info">
                    <div class="order-ref">#{{ $order->id }}</div>
                    <div class="order-shop">{{ $order->shop?->name ?? 'Boutique' }}</div>
                </div>
                <span class="order-pill {{ $st[0] }}">{{ $st[1] }}</span>
                <div class="order-amount">
                    {{ number_format($order->total, 0, ',', ' ') }}
                    <span style="font-size:10px;font-weight:400;color:var(--muted)"> GNF</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Bannière promo ── --}}
    <div class="promo-banner">
        <span class="promo-ico">🎁</span>
        <div class="promo-text">
            <div class="promo-title">Paiement <span class="hl">cash à la livraison</span> sur toutes les boutiques</div>
            <div class="promo-sub">Commandez maintenant, payez à la réception — aucune carte requise.</div>
        </div>
        <a href="#boutiques" class="promo-btn">Explorer les boutiques →</a>
    </div>

    {{-- ── Filtres catégories ── --}}
    <div class="c-cats" id="catFilter">
        @foreach($categories as $cat => $ico)
        <a href="?type={{ $cat === 'Toutes' ? '' : $cat }}"
           class="c-cat-pill {{ request('type', '') === ($cat === 'Toutes' ? '' : $cat) ? 'active' : '' }}">
            <span class="pill-ico">{{ $ico }}</span>
            {{ $cat }}
        </a>
        @endforeach
    </div>

    {{-- ── Section boutiques ── --}}
    <div id="boutiques">
        <div class="c-sec-hd">
            <div class="c-sec-title">
                <span class="c-sec-title-dot"></span>
                Boutiques disponibles
                <span style="font-size:13px;font-weight:500;color:var(--muted);font-family:var(--font)">
                    ({{ $shops->total() }})
                </span>
            </div>
        </div>

        <div class="shops-grid" id="shopsGrid">
            @forelse($shops as $shop)
            @php
                [$ico, $bgClass] = $typeIco[$shop->type ?? ''] ?? ['🛍️', 'bg-default'];
                $isNew = $shop->created_at->diffInDays(now()) <= 7;
            @endphp
            <a href="{{ route('client.shops.show', $shop) }}"
               class="shop-card" data-name="{{ strtolower($shop->name) }}" data-type="{{ strtolower($shop->type ?? '') }}">

                {{-- Bannière --}}
                <div class="shop-card-banner">
                    @if($shop->image)
                        <img src="{{ asset('storage/'.$shop->image) }}"
                             class="shop-card-banner-img" alt="{{ $shop->name }}">
                    @else
                        <div class="shop-card-banner-placeholder {{ $bgClass }}">{{ $ico }}</div>
                    @endif

                    {{-- Badge statut --}}
                    @if($isNew)
                        <span class="shop-status new">✨ Nouveau</span>
                    @else
                        <span class="shop-status open">Ouvert</span>
                    @endif

                    {{-- Logo --}}
                    <div class="shop-card-logo">
                        @if($shop->image)
                            <img src="{{ asset('storage/'.$shop->image) }}" alt="">
                        @else
                            {{ $ico }}
                        @endif
                    </div>
                </div>

                {{-- Corps --}}
                <div class="shop-card-body">
                    @if($shop->type)
                    <div class="shop-card-type">{{ $shop->type }}</div>
                    @endif
                    <div class="shop-card-name">{{ $shop->name }}</div>
                    @if($shop->description)
                    <p class="shop-card-desc">{{ $shop->description }}</p>
                    @endif
                    <div class="shop-card-meta">
                        @if($shop->ville ?? $shop->address)
                        <span class="shop-card-chip">
                            📍 {{ $shop->ville ?? Str::limit($shop->address, 20) }}
                        </span>
                        @endif
                        @if(($shop->products_count ?? 0) > 0)
                        <span class="shop-card-chip">🏷️ {{ $shop->products_count }} produit{{ $shop->products_count > 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="shop-card-footer">
                    <div class="shop-card-rating">
                        ⭐
                        {{ $shop->avg_rating ? number_format($shop->avg_rating, 1) : '—' }}
                        <small>({{ $shop->reviews_count ?? 0 }} avis)</small>
                    </div>
                    <span class="shop-card-cta">
                        Commander →
                    </span>
                </div>

            </a>
            @empty
            <div class="c-empty">
                <span class="c-empty-ico">🏪</span>
                <div class="c-empty-title">Aucune boutique disponible</div>
                <p class="c-empty-sub">Revenez bientôt, de nouvelles boutiques arrivent.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="c-pagination">
            {{ $shops->links() }}
        </div>
    </div>

</main>

@endsection

@push('scripts')
<script>
/* ── Avatar menu ── */
function toggleAvatarMenu() {
    document.getElementById('avatarMenu').classList.toggle('open');
}
document.addEventListener('click', e => {
    const av  = document.getElementById('navAvatar');
    const men = document.getElementById('avatarMenu');
    if (av && !av.contains(e.target) && men && !men.contains(e.target)) {
        men.classList.remove('open');
    }
});

/* ── Recherche live boutiques ── */
const searchInput = document.getElementById('globalSearch');
if (searchInput) {
    searchInput.addEventListener('input', e => {
        const q = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.shop-card').forEach(card => {
            const name = card.dataset.name || '';
            const type = card.dataset.type || '';
            const match = !q || name.includes(q) || type.includes(q);
            card.style.display = match ? '' : 'none';
        });
    });
}

/* ── Animations d'entrée des cards ── */
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.shop-card');
    cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity .4s ease, transform .4s ease';
            card.style.opacity    = '1';
            card.style.transform  = 'translateY(0)';
        }, 60 + i * 40);
    });
});
</script>
@endpush