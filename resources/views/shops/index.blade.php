@extends('layouts.app')


@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* Annuler le container Bootstrap de layouts.app */
main.app-main {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
}

/* ═══════════════════════════════════════════
   VARIABLES
═══════════════════════════════════════════ */
:root {
    --gr:      #6366f1;
    --gr-dk:   #4f46e5;
    --gr-lt:   #e0e7ff;
    --gr-mlt:  #eef2ff;
    --dark:    #0a0a1e;
    --dark2:   #0e0e16;
    --txt:     #0f172a;
    --txt2:    #475569;
    --muted:   #94a3b8;
    --surf:    #ffffff;
    --bg:      #f8fafc;
    --border:  #e2e8f0;
    --r:       14px;
    --font:    'Plus Jakarta Sans', sans-serif;
}
*, *::before, *::after { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body { font-family: var(--font); background: var(--bg); color: var(--txt); margin: 0; -webkit-font-smoothing: antialiased; }

/* ── Hide default navbar ── */
.navbar { display: none !important; }

/* ════════════════════════════════════════════
   TOP NAV (identique welcome)
════════════════════════════════════════════ */
.top-nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    padding: 0 40px; height: 64px;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(10,10,30,.96);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.nav-brand {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none; color: #fff;
    font-size: 17px; font-weight: 700;
}
.nav-brand-icon {
    width: 34px; height: 34px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    border-radius: 9px; display: flex; align-items: center;
    justify-content: center; font-size: 16px;
    box-shadow: 0 2px 10px rgba(99,102,241,.4);
}
.nav-links { display: flex; align-items: center; gap: 8px; }
.nav-link-item {
    padding: 7px 14px; border-radius: 8px;
    font-size: 13px; font-weight: 600; text-decoration: none;
    color: rgba(255,255,255,.7); transition: all .15s;
}
.nav-link-item:hover { background: rgba(255,255,255,.08); color: #fff; }
.nav-btn {
    padding: 7px 18px; border-radius: 8px;
    font-size: 13px; font-weight: 700; text-decoration: none;
    background: var(--gr); color: #fff; transition: all .15s;
}
.nav-btn:hover { background: var(--gr-dk); color: #fff; }
.nav-btn-outline {
    background: transparent; border: 1px solid rgba(255,255,255,.2);
    color: rgba(255,255,255,.8);
}
.nav-btn-outline:hover { border-color: rgba(255,255,255,.5); color: #fff; background: rgba(255,255,255,.06); }

/* ════════════════════════════════════════════
   HERO MINI
════════════════════════════════════════════ */
.shops-hero {
    background: var(--dark);
    padding: 100px 24px 60px;
    text-align: center;
    position: relative; overflow: hidden;
}
.shops-hero::before {
    content: '';
    position: absolute; inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.05) 1px, transparent 0);
    background-size: 28px 28px;
    pointer-events: none;
}
.shops-hero-glow {
    position: absolute;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(99,102,241,.18) 0%, rgba(139,92,246,.08) 60%, transparent 70%);
    top: 50%; left: 50%; transform: translate(-50%, -60%);
    pointer-events: none;
}
.shops-hero-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(99,102,241,.12); border: 1px solid rgba(99,102,241,.25);
    color: #a5b4fc; font-size: 11px; font-weight: 700;
    padding: 5px 13px; border-radius: 20px;
    margin-bottom: 18px;
}
.shops-hero-badge-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #a5b4fc; box-shadow: 0 0 6px #a5b4fc;
    animation: blink 2s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
.shops-hero h1 {
    font-size: clamp(28px, 5vw, 52px);
    font-weight: 800; color: #fff;
    letter-spacing: -1.5px; margin: 0 0 12px;
    line-height: 1.1;
}
.shops-hero h1 span {
    background: linear-gradient(135deg, #a5b4fc, #6366f1, #8b5cf6);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.shops-hero p {
    font-size: 15px; color: rgba(255,255,255,.5);
    margin: 0 auto 32px; max-width: 460px; line-height: 1.65;
}

/* ── Search bar ── */
.search-wrap {
    position: relative; max-width: 560px; margin: 0 auto;
}
.search-input {
    width: 100%;
    padding: 14px 56px 14px 20px;
    border-radius: 50px;
    border: 2px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.07);
    color: #fff; font-size: 14px; font-family: var(--font);
    outline: none; transition: border-color .2s, background .2s;
}
.search-input::placeholder { color: rgba(255,255,255,.35); }
.search-input:focus {
    border-color: var(--gr);
    background: rgba(255,255,255,.1);
}
.search-btn {
    position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
    background: var(--gr); border: none; border-radius: 50px;
    width: 42px; height: 42px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; cursor: pointer;
    transition: background .15s, transform .1s;
}
.search-btn:hover { background: var(--gr-dk); }

/* Résultat recherche */
.search-result-info {
    margin-top: 12px; font-size: 13px; color: rgba(255,255,255,.4);
}
.search-result-info strong { color: rgba(255,255,255,.7); }

/* ════════════════════════════════════════════
   MAIN CONTENT
════════════════════════════════════════════ */
.shops-main {
    max-width: 1200px; margin: 0 auto;
    padding: 48px 24px 80px;
}

/* ── Filtres par type ── */
.type-filters {
    display: flex; gap: .5rem; flex-wrap: wrap;
    margin-bottom: 32px;
}
.type-chip {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .42rem 1rem; border-radius: 50px;
    font-size: .8rem; font-weight: 700;
    text-decoration: none;
    border: 1.5px solid var(--border);
    color: var(--txt2); background: var(--surf);
    transition: all .15s;
    white-space: nowrap;
}
.type-chip:hover { border-color: var(--gr-lt); color: var(--gr-dk); background: var(--gr-mlt); }
.type-chip.active { background: var(--gr); border-color: var(--gr); color: #fff; }

/* ── Stats barre ── */
.shops-stats-bar {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .75rem;
    margin-bottom: 28px;
}
.shops-count {
    font-size: .88rem; color: var(--txt2);
    font-weight: 600;
}
.shops-count strong { color: var(--txt); font-size: 1rem; }

/* Tri / vue (déco) */
.shops-view-opts {
    display: flex; gap: .4rem;
}
.view-opt {
    width: 36px; height: 36px; border-radius: 8px;
    border: 1.5px solid var(--border);
    background: var(--surf); display: flex; align-items: center;
    justify-content: center; font-size: 14px;
    cursor: pointer; transition: all .15s; color: var(--muted);
}
.view-opt.active, .view-opt:hover {
    border-color: var(--gr-lt); color: var(--gr-dk); background: var(--gr-mlt);
}

/* ════════════════════════════════════════════
   GRILLE DES BOUTIQUES
════════════════════════════════════════════ */
.shops-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 48px;
}

.shop-card {
    scroll-margin-top: calc(var(--top-nav-h, 64px) + 12px);
    background: var(--surf);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    text-decoration: none;
    display: flex; flex-direction: column;
    transition: box-shadow .25s, border-color .25s, transform .25s;
    animation: fadeUp .35s ease both;
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
}
.shop-card:hover {
    box-shadow: 0 20px 60px rgba(0,0,0,.12);
    border-color: var(--gr-lt);
    transform: translateY(-7px);
}
@keyframes fadeUp {
    from { opacity:0; transform:translateY(18px); }
    to   { opacity:1; transform:translateY(0); }
}

/* Image */
.sc-img {
    height: 190px; overflow: hidden;
    background: linear-gradient(135deg, var(--gr-mlt), var(--gr-lt));
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; position: relative;
}
.sc-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .5s ease;
}
.shop-card:hover .sc-img img { transform: scale(1.08); }
.sc-img-ph { font-size: 52px; opacity: .5; }

/* Overlay au hover */
.sc-img::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.25) 0%, transparent 60%);
    opacity: 0; transition: opacity .25s;
}
.shop-card:hover .sc-img::after { opacity: 1; }

/* Badge "Actif" flottant */
.sc-badge {
    position: absolute; top: 12px; right: 12px;
    background: rgba(99,102,241,.92); color: #fff;
    font-size: .68rem; font-weight: 800;
    padding: 4px 11px; border-radius: 20px;
    backdrop-filter: blur(6px);
    z-index: 1;
    letter-spacing: .3px;
}

/* Body de la carte */
.sc-body {
    padding: 20px 22px 22px;
    display: flex; flex-direction: column; flex: 1;
}
.sc-name {
    font-size: 16px; font-weight: 800; color: var(--txt);
    margin-bottom: 4px; line-height: 1.25;
}
.sc-type {
    display: inline-flex; align-items: center;
    font-size: 11px; color: var(--gr-dk);
    text-transform: uppercase; letter-spacing: .06em;
    font-weight: 700; margin-bottom: 12px;
    background: var(--gr-mlt); border: 1px solid var(--gr-lt);
    padding: 2px 9px; border-radius: 20px;
    width: fit-content;
}
.sc-meta {
    display: flex; align-items: center; gap: 14px;
    font-size: 12px; color: var(--txt2); margin-bottom: 16px;
    flex-wrap: wrap;
}
.sc-meta-item {
    display: flex; align-items: center; gap: 4px;
    background: var(--bg); border: 1px solid var(--border);
    padding: 3px 9px; border-radius: 20px;
    font-weight: 600;
}

/* Rating étoiles déco */
.sc-stars { color: #f59e0b; font-size: 11px; letter-spacing: 1px; margin-bottom: 14px; }

/* Bouton CTA */
.sc-btn {
    margin-top: auto;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: .65rem 1rem;
    border-radius: 10px;
    font-size: .83rem; font-weight: 700;
    background: var(--gr-mlt); color: var(--gr-dk);
    border: 1.5px solid var(--gr-lt);
    text-decoration: none; transition: all .18s;
}
.shop-card:hover .sc-btn {
    background: var(--gr); color: #fff; border-color: var(--gr-dk);
    box-shadow: 0 4px 14px rgba(99,102,241,.35);
}

/* ── Empty state ── */
.shops-empty {
    text-align: center; padding: 5rem 1rem;
    background: var(--surf); border-radius: var(--r);
    border: 1px dashed var(--border);
}
.shops-empty-ico { font-size: 3.5rem; display: block; margin-bottom: 1rem; }
.shops-empty h3 { font-size: 1.2rem; font-weight: 700; color: var(--txt); margin: 0 0 .5rem; }
.shops-empty p  { color: var(--txt2); font-size: .9rem; margin: 0 0 1.5rem; }
.shops-empty a  {
    display: inline-flex; align-items: center; gap: .4rem;
    background: var(--gr); color: #fff; font-weight: 700;
    font-size: .88rem; padding: .6rem 1.5rem;
    border-radius: 50px; text-decoration: none;
    transition: background .2s;
}
.shops-empty a:hover { background: var(--gr-dk); color: #fff; }

/* ── Pagination ── */
.shops-pag { display: flex; justify-content: center; }
.shops-pag .pagination { gap: .3rem; }
.shops-pag .page-link {
    border-radius: 8px !important;
    border: 1.5px solid var(--border) !important;
    color: var(--gr) !important; font-weight: 600;
    padding: .45rem .85rem;
}
.shops-pag .page-item.active .page-link {
    background: var(--gr) !important; border-color: var(--gr) !important; color: #fff !important;
}
.shops-pag .page-link:hover { background: var(--gr-mlt) !important; }

/* ── Badge "produit trouvé" ── */
.sc-prod-match {
    display: none;
    align-items: center; gap: 5px;
    font-size: 11px; font-weight: 700;
    color: var(--gr-dk); background: var(--gr-mlt);
    border: 1px solid var(--gr-lt);
    padding: 3px 9px; border-radius: 20px;
    width: fit-content; margin-bottom: 8px;
}
.shop-card.prod-match .sc-prod-match { display: inline-flex; }

/* ── Recherche live ── */
#liveSearchInfo {
    margin-top: 10px; font-size: 13px;
    color: rgba(255,255,255,.55); display: none;
    animation: fadeIn .2s ease;
}
#liveSearchInfo strong { color: rgba(255,255,255,.85); }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
.sc-name mark {
    background: rgba(99,102,241,.22); color: var(--gr-dk);
    border-radius: 3px; padding: 0 2px;
    font-style: normal;
}
#liveEmpty {
    display: none; text-align: center; padding: 3rem 1rem;
    background: var(--surf); border-radius: var(--r);
    border: 1px dashed var(--border); margin-bottom: 24px;
}
#liveEmpty .ico { font-size: 3rem; display: block; margin-bottom: .75rem; }
#liveEmpty h3  { font-size: 1.1rem; font-weight: 700; color: var(--txt); margin: 0 0 .4rem; }
#liveEmpty p   { color: var(--txt2); font-size: .88rem; margin: 0; }

/* ── Back to top ── */
.back-top {
    display: inline-flex; align-items: center; gap: .4rem;
    text-decoration: none; color: var(--txt2);
    font-size: .82rem; font-weight: 600;
    padding: .4rem .9rem; border-radius: 50px;
    border: 1.5px solid var(--border); background: var(--surf);
    transition: all .15s; margin-bottom: 28px;
}
.back-top:hover { color: var(--gr-dk); border-color: var(--gr-lt); background: var(--gr-mlt); }

/* ════════════════════════════════════════════
   FOOTER MINI
════════════════════════════════════════════ */
.mini-footer {
    background: var(--dark); padding: 28px 40px;
    text-align: center; color: rgba(255,255,255,.35);
    font-size: 12.5px;
}
.mini-footer a { color: rgba(255,255,255,.5); text-decoration: none; }
.mini-footer a:hover { color: #fff; }

/* ════════════════════════════════════════════
   HAMBURGER MOBILE
════════════════════════════════════════════ */
.nav-hamburger {
    display: none;
    flex-direction: column; justify-content: center; align-items: center;
    gap: 5px; width: 40px; height: 40px;
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px; cursor: pointer; padding: 0;
    flex-shrink: 0;
}
.nav-hamburger span {
    display: block; width: 18px; height: 2px;
    background: #fff; border-radius: 2px;
    transition: transform .25s, opacity .25s;
}
.nav-hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.nav-hamburger.open span:nth-child(2) { opacity: 0; }
.nav-hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

.nav-mobile-menu {
    display: none;
    position: fixed; top: 64px; left: 0; right: 0; z-index: 99;
    background: rgba(10,10,30,.98);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(255,255,255,.08);
    padding: 12px 16px 16px;
    flex-direction: column; gap: 4px;
}
.nav-mobile-menu.open { display: flex; }
.nav-mobile-link {
    display: block; padding: 12px 16px; border-radius: 10px;
    font-size: 14px; font-weight: 600; text-decoration: none;
    color: rgba(255,255,255,.75); transition: all .15s;
}
.nav-mobile-link:hover { background: rgba(255,255,255,.08); color: #fff; }
.nav-mobile-divider { height: 1px; background: rgba(255,255,255,.08); margin: 6px 0; }
.nav-mobile-btn {
    display: block; padding: 13px 16px; border-radius: 10px;
    font-size: 14px; font-weight: 700; text-decoration: none;
    text-align: center; margin-top: 4px; transition: all .15s;
}
.nav-mobile-btn-outline {
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.15);
    color: rgba(255,255,255,.8);
}
.nav-mobile-btn-indigo {
    background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff;
    box-shadow: 0 4px 14px rgba(99,102,241,.35);
}

/* ════════════════════════════════════════════
   RESPONSIVE
════════════════════════════════════════════ */
@media (max-width: 1100px) {
    .shops-grid { grid-template-columns: repeat(3, 1fr); gap: 18px; }
}
@media (max-width: 768px) {
    .shops-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .top-nav { padding: 0 16px; }
    .nav-links { display: none; }
    .nav-hamburger { display: flex; }
    .shops-hero { padding: 88px 16px 48px; }
    .shops-main { padding: 32px 16px 60px; }
    .sc-img { height: 150px; }
    .type-filters {
        flex-wrap: nowrap; overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 4px;
        scrollbar-width: none;
    }
    .type-filters::-webkit-scrollbar { display: none; }
    .mini-footer { padding: 24px 16px; }
    .search-input { font-size: 16px !important; }
}
@media (max-width: 480px) {
    .shops-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .sc-img { height: 120px; }
    .sc-name { font-size: 13.5px; }
    .sc-body { padding: 12px 13px 14px; }
    .sc-btn { font-size: .75rem; padding: .5rem .6rem; }
    .sc-meta { gap: 6px; margin-bottom: 10px; }
    .sc-meta-item { font-size: 11px; padding: 2px 7px; }
    .sc-stars { font-size: 10px; margin-bottom: 8px; }
    .sc-type { font-size: 10px; }
    .shops-hero h1 { letter-spacing: -1px; }
    .back-top { font-size: .78rem; }
}
@media (max-width: 380px) {
    .shops-grid { grid-template-columns: 1fr; gap: 12px; }
    .sc-img { height: 180px; }
    .sc-name { font-size: 15px; }
    .sc-body { padding: 14px 16px 16px; }
    .sc-btn { font-size: .83rem; padding: .65rem 1rem; }
}
</style>
@endpush

@section('content')

{{-- ══════════ NAVBAR ══════════ --}}
<nav class="top-nav">
    <a href="{{ url('/') }}" class="nav-brand">
        <div class="sb-logo-icon"><img src="/images/shopio3.jpeg" alt="Shopio" style="width:40px;height:40px;object-fit:cover;border-radius:9px"></div>
        {{ config('app.name', 'ShopManager') }}
    </a>
    <div class="nav-links">
        <a href="{{ url('/') }}" class="nav-link-item">← Accueil</a>
        @guest
        <a href="{{ route('login') }}"    class="nav-link-item nav-btn nav-btn-outline" style="padding:7px 14px">Connexion</a>
        <a href="{{ route('register') }}" class="nav-btn">S'inscrire</a>
        @else
        @php
            $role = Auth::user()->role;
            $map  = ['superadmin'=>'admin.dashboard','admin'=>'boutique.dashboard','vendeur'=>'vendeur.dashboard','client'=>'client.dashboard','company'=>'company.dashboard','livreur'=>'livreur.dashboard'];
        @endphp
        @if(isset($map[$role]))
        <a href="{{ route($map[$role]) }}" class="nav-btn">Mon dashboard →</a>
        @endif
        @endguest
    </div>
    <button class="nav-hamburger" id="shopsHamburger" aria-label="Menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</nav>

{{-- Menu mobile --}}
<div class="nav-mobile-menu" id="shopsMobileMenu">
    <a href="{{ url('/') }}" class="nav-mobile-link">← Accueil</a>
    <div class="nav-mobile-divider"></div>
    @guest
    <a href="{{ route('login') }}"    class="nav-mobile-btn nav-mobile-btn-outline">Se connecter</a>
    <a href="{{ route('register') }}" class="nav-mobile-btn nav-mobile-btn-indigo">🚀 S'inscrire — Gratuit</a>
    @else
    @php
        $role3 = Auth::user()->role;
        $map3  = ['superadmin'=>'admin.dashboard','admin'=>'boutique.dashboard','vendeur'=>'vendeur.dashboard','client'=>'client.dashboard','company'=>'company.dashboard','livreur'=>'livreur.dashboard'];
    @endphp
    @if(isset($map3[$role3]))
    <a href="{{ route($map3[$role3]) }}" class="nav-mobile-btn nav-mobile-btn-indigo">Mon dashboard →</a>
    @endif
    @endguest
</div>

{{-- ══════════ HERO ══════════ --}}
<div class="shops-hero">
    <div class="shops-hero-glow"></div>

    <div class="shops-hero-badge">
        <span class="shops-hero-badge-dot"></span>
        {{ $totalShops }} boutique{{ $totalShops > 1 ? 's' : '' }} disponible{{ $totalShops > 1 ? 's' : '' }}
    </div>

    <h1>Toutes les <span>boutiques</span></h1>
    <p>Explorez notre marketplace et commandez directement auprès de vendeurs de confiance.</p>

    {{-- Formulaire de recherche --}}
    <form action="{{ route('shops.index') }}" method="GET" class="search-wrap">
        @if($type)
            <input type="hidden" name="type" value="{{ $type }}">
        @endif
        <input type="text"
               id="shopsSearch"
               name="q"
               class="search-input"
               placeholder="Rechercher une boutique, un type..."
               value="{{ $q }}"
               autocomplete="off">
        <button type="submit" class="search-btn">🔍</button>
    </form>

    <div id="liveSearchInfo"></div>

    @if($q)
        <div class="search-result-info">
            Résultats pour <strong>"{{ $q }}"</strong> —
            <a href="{{ route('shops.index', $type ? ['type' => $type] : []) }}"
               style="color:rgba(255,255,255,.5);text-decoration:underline">Effacer</a>
        </div>
    @endif
</div>

{{-- ══════════ CONTENU PRINCIPAL ══════════ --}}
<div class="shops-main">

    {{-- Retour --}}
    <a href="{{ url('/') }}" class="back-top">← Retour à l'accueil</a>

    {{-- Filtres par type --}}
    @if($types->count() > 0)
    <div class="type-filters">
        <a href="{{ route('shops.index', $q ? ['q' => $q] : []) }}"
           class="type-chip {{ !$type ? 'active' : '' }}">
            Toutes
        </a>
        @foreach($types as $t)
        <a href="{{ route('shops.index', array_filter(['q' => $q, 'type' => $t])) }}"
           class="type-chip {{ $type === $t ? 'active' : '' }}">
            {{ $t }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Barre de stats ── --}}
    <div class="shops-stats-bar">
        <div class="shops-count">
            @if($shops->total() > 0)
                <strong>{{ $shops->total() }}</strong> boutique{{ $shops->total() > 1 ? 's' : '' }}
                @if($type) · <span style="color:var(--gr-dk)">{{ $type }}</span> @endif
                @if($q) · <span style="color:var(--gr-dk)">"{{ $q }}"</span> @endif
            @else
                Aucune boutique trouvée
            @endif
        </div>

        <div class="shops-view-opts">
            <div class="view-opt active" title="Grille">⊞</div>
        </div>
    </div>

    {{-- Grille boutiques --}}
    @if($shops->count() > 0)
    <div class="shops-grid" id="shopsGrid">
        @foreach($shops as $i => $shop)
        @php
            $prodKeywords = $shop->products
                ->map(function($p){ return strtolower($p->name.' '.($p->category ?? '')); })
                ->implode(' ');
        @endphp
        <a href="{{ route('public.shops.products', $shop) }}"
           class="shop-card"
           data-name="{{ strtolower($shop->name) }}"
           data-type="{{ strtolower($shop->type ?? '') }}"
           data-products="{{ $prodKeywords }}"
           style="animation-delay: {{ ($i % 12) * 55 }}ms">

            {{-- Image --}}
            <div class="sc-img">
                @if(!empty($shop->image))
                    <img src="{{ \App\Services\ImageOptimizer::url($shop->image, 'thumb') }}"
                         alt="{{ $shop->name }}" loading="lazy">
                @else
                    <span class="sc-img-ph">🛍️</span>
                @endif
                <span class="sc-badge">✓ Actif</span>
            </div>

            {{-- Corps --}}
            <div class="sc-body">
                <div class="sc-name">{{ $shop->name }}</div>

                @if($shop->type)
                    <div class="sc-type">{{ $shop->type }}</div>
                @endif

                <div class="sc-stars">★★★★★</div>

                <div class="sc-meta">
                    <span class="sc-meta-item">
                        📦 {{ $shop->products_count ?? 0 }} produit{{ ($shop->products_count ?? 0) > 1 ? 's' : '' }}
                    </span>
                    @if($shop->country)
                    <span class="sc-meta-item">🌍 {{ $shop->country }}</span>
                    @elseif($shop->address)
                    <span class="sc-meta-item">📍 {{ Str::limit($shop->address, 16) }}</span>
                    @endif
                </div>

                <span class="sc-prod-match">📦 A ce produit</span>

                <span class="sc-btn">
                    Visiter la boutique
                    <span style="font-size:15px">→</span>
                </span>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Empty live search --}}
    <div id="liveEmpty">
        <span class="ico">🔍</span>
        <h3>Aucune boutique trouvée</h3>
        <p id="liveEmptyMsg">Aucun résultat pour votre recherche.</p>
    </div>

    {{-- Pagination --}}
    @if($shops->hasPages())
    <div class="shops-pag">{{ $shops->links() }}</div>
    @endif

    @else
    {{-- Empty state --}}
    <div class="shops-empty">
        <span class="shops-empty-ico">🏪</span>
        <h3>Aucune boutique trouvée</h3>
        <p>
            @if($q)
                Aucun résultat pour "{{ $q }}". Essayez un autre terme.
            @elseif($type)
                Aucune boutique dans la catégorie "{{ $type }}".
            @else
                Aucune boutique disponible pour le moment.
            @endif
        </p>
        <a href="{{ route('shops.index') }}">🔄 Voir toutes les boutiques</a>
    </div>
    @endif

</div>

{{-- ══════════ FOOTER MINI ══════════ --}}
<footer class="mini-footer">
    <span>&copy; {{ date('Y') }} {{ config('app.name') }} ·
    <a href="{{ url('/') }}">Accueil</a> ·
    <a href="{{ route('login') }}">Connexion</a> ·
    <a href="{{ route('register') }}">S'inscrire</a></span>
</footer>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Reveal au scroll ── */
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.opacity   = '1';
                e.target.style.transform = 'translateY(0)';
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });
    document.querySelectorAll('.shop-card').forEach(el => obs.observe(el));

    /* ── Hamburger ── */
    const ham  = document.getElementById('shopsHamburger');
    const menu = document.getElementById('shopsMobileMenu');
    if (ham && menu) {
        ham.addEventListener('click', () => {
            const open = menu.classList.toggle('open');
            ham.classList.toggle('open', open);
            ham.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', e => {
            if (!ham.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('open');
                ham.classList.remove('open');
                ham.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ════════════════════════════════
       RECHERCHE LIVE
    ════════════════════════════════ */
    const searchInput = document.getElementById('shopsSearch');
    const liveInfo    = document.getElementById('liveSearchInfo');
    const liveEmpty   = document.getElementById('liveEmpty');
    const liveMsg     = document.getElementById('liveEmptyMsg');
    const navH        = () => (document.querySelector('.top-nav')?.offsetHeight || 64) + 16;

    if (!searchInput) return;

    /* Échappe les caractères spéciaux regex */
    function escRe(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

    /* Surligne le texte qui correspond à la recherche */
    function hlText(text, q) {
        if (!q) return text;
        return text.replace(new RegExp('(' + escRe(q) + ')', 'gi'), '<mark>$1</mark>');
    }

    /* Scroll vers un élément en tenant compte de la navbar fixe */
    function scrollTo(el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    let timer;
    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => liveSearch(this.value.trim()), 180);
    });

    /* Empêche le rechargement de page si l'utilisateur appuie Entrée (la recherche est déjà live) */
    searchInput.closest('form')?.addEventListener('submit', e => {
        const q = searchInput.value.trim();
        if (q) {
            /* Laisse le formulaire soumettre normalement pour que la pagination
               et les filtres de type fonctionnent aussi côté serveur */
        }
    });

    function liveSearch(q) {
        const cards = Array.from(document.querySelectorAll('.shop-card'));
        const ql    = q.toLowerCase();
        let visible = 0;
        let firstCard = null;

        cards.forEach(card => {
            const name     = card.dataset.name     || '';
            const type     = card.dataset.type     || '';
            const products = card.dataset.products || '';

            const matchShop = !ql || name.includes(ql) || type.includes(ql);
            const matchProd = !matchShop && ql && products.includes(ql);
            const match     = matchShop || matchProd;

            card.style.display = match ? '' : 'none';

            /* Badge "A ce produit" — visible seulement si trouvé via produit */
            card.classList.toggle('prod-match', matchProd);

            /* Surlignage dans le nom de boutique */
            const nameEl = card.querySelector('.sc-name');
            if (nameEl) {
                if (!card.dataset.origName) card.dataset.origName = nameEl.textContent.trim();
                nameEl.innerHTML = ql ? hlText(card.dataset.origName, q) : card.dataset.origName;
            }

            if (match) { visible++; if (!firstCard) firstCard = card; }
        });

        /* Info live sous la barre de recherche */
        if (ql) {
            liveInfo.innerHTML = visible > 0
                ? `${visible} boutique${visible > 1 ? 's' : ''} pour <strong>"${q}"</strong>`
                : `Aucun résultat pour <strong>"${q}"</strong>`;
            liveInfo.style.display = 'block';
        } else {
            liveInfo.style.display = 'none';
            /* Retirer tous les badges prod-match */
            cards.forEach(c => c.classList.remove('prod-match'));
        }

        /* Empty state live */
        if (liveEmpty) {
            liveEmpty.style.display = (visible === 0 && ql) ? 'block' : 'none';
            if (liveMsg) liveMsg.textContent = `Aucun résultat pour "${q}". Essayez un autre terme.`;
        }

        /* Scroll vers la première carte trouvée */
        if (ql.length >= 1 && firstCard) {
            setTimeout(() => scrollTo(firstCard), 50);
        } else if (!ql) {
            const grid = document.getElementById('shopsGrid');
            if (grid) setTimeout(() => scrollTo(grid), 50);
        }
    }

    /* Si la page est chargée avec un ?q= déjà défini, lancer la recherche live */
    if (searchInput.value.trim()) {
        liveSearch(searchInput.value.trim());
    }
});
</script>
@endpush
