{{--
=====================================================
WELCOME.BLADE.PHP — Page d'accueil, style "Jumia"
=====================================================
Affiche directement les produits et catégories dès l'arrivée du visiteur,
au lieu d'une page marketing. Variables injectées depuis WelcomeController@index :
  $flashProducts        → Collection<Product>  (ventes flash actives)
  $recommendedProducts  → Collection<Product>  (produits vedette)
  $shops                → Collection<Shop>     (boutiques à la une)
  $products             → LengthAwarePaginator<Product> (catalogue complet)
  $categories           → Collection<string>
  $stats                → array { total_shops, total_products, total_orders }
=====================================================
--}}

@extends('layouts.app')

@section('title', config('app.name', 'Shopio') . ' — Achetez malin, faites-vous livrer')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap">
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
</noscript>

<style>
*, *::before, *::after { box-sizing: border-box; }

/* ════════════════════════════════════════════════════════════════
   VARIABLES & BASE — thème bleu de la marque
════════════════════════════════════════════════════════════════ */
:root {
    --brand:      #6366f1;
    --brand-dk:   #4f46e5;
    --brand-lt:   #e0e7ff;
    --brand-mlt:  #eef2ff;
    --navy:       #0a0a1e;
    --navy-2:     #151538;
    --grey:       #f4f6fb;
    --grey-2:     #e8ecf5;
    --border:     #e2e8f0;
    --text:       #0f172a;
    --text-2:     #475569;
    --muted:      #94a3b8;
    --surface:    #ffffff;
    --font:       'Plus Jakarta Sans', sans-serif;
    --display:    'Clash Display', 'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r:          14px;
    --r-sm:       9px;
    --shadow-sm:  0 1px 4px rgba(15,23,42,.06);
    --shadow:     0 4px 18px rgba(15,23,42,.08);
    --shadow-lg:  0 10px 36px rgba(15,23,42,.12);
    --nav-h:      64px;
}

html { font-family: var(--font); scroll-behavior: smooth; overflow-x: hidden; }
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; overflow-x: hidden; }
main.app-main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; width: 100% !important; }
img { max-width: 100%; }
footer.app-footer { display: none !important; }

/* ── Icônes SVG (remplacent les émojis) ── */
.ico { display: inline-block; flex-shrink: 0; vertical-align: -3px; }

/* ════════════════════════════════════════════════════════════════
   NAVBAR
════════════════════════════════════════════════════════════════ */
.navbar { display: none !important; } /* la nav custom ci-dessous remplace celle du layout */

.nav {
    height: var(--nav-h);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 28px; gap: 18px;
    position: sticky; top: 0; z-index: 200;
    box-shadow: var(--shadow-sm);
}
.nav-logo {
    font-family: var(--display); font-weight: 700; font-size: 20px;
    text-decoration: none; flex-shrink: 0;
    display: flex; align-items: center; gap: 9px; color: var(--text);
}
.nav-logo img { height: 36px; width: 36px; object-fit: cover; border-radius: 9px; flex-shrink: 0; }
.nav-logo .brand-part { color: var(--navy); }
.nav-logo .brand-accent { color: var(--brand); }
.nav-links { display: flex; align-items: center; gap: 2px; flex-shrink: 0; }
.nav-link {
    padding: 9px 14px; border-radius: var(--r-sm);
    font-size: 13.5px; font-weight: 600; color: var(--text-2);
    text-decoration: none; transition: all .15s;
    display: flex; align-items: center; gap: 6px; white-space: nowrap;
}
.nav-link:hover { background: var(--grey); color: var(--text); }
.nav-link.active { color: var(--brand-dk); background: var(--brand-mlt); }
.nav-search {
    flex: 1; max-width: 460px;
    display: flex; align-items: center;
    border: 1.5px solid var(--border);
    border-radius: 50px; overflow: hidden;
    background: var(--grey);
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.nav-search:focus-within { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(99,102,241,.12); background: var(--surface); }
.nav-search input {
    flex: 1; border: none; outline: none; background: transparent;
    padding: 10px 18px; font-size: 13.5px; font-family: var(--font); color: var(--text);
}
.nav-search input::placeholder { color: var(--muted); }
.nav-search-btn {
    padding: 10px 18px; background: var(--brand); border: none; cursor: pointer;
    color: #fff; font-size: 15px; transition: background .15s; display: flex; align-items: center;
}
.nav-search-btn:hover { background: var(--brand-dk); }
.nav-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; margin-left: auto; }
.nav-orders-btn {
    display: flex; align-items: center; gap: 7px;
    padding: 9px 16px; border-radius: 50px;
    font-size: 12.5px; font-weight: 700; font-family: var(--font);
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--text); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap;
}
.nav-orders-btn:hover { border-color: var(--brand); color: var(--brand-dk); background: var(--brand-mlt); }
.nav-btn-primary {
    padding: 10px 20px; border-radius: 50px; font-size: 12.5px; font-weight: 700; font-family: var(--font);
    background: var(--brand); color: #fff; border: none; cursor: pointer; text-decoration: none;
    transition: all .15s; white-space: nowrap; box-shadow: 0 3px 12px rgba(99,102,241,.35);
}
.nav-btn-primary:hover { background: var(--brand-dk); color: #fff; }

/* ── Barre mobile (recherche) ── */
.mobile-bar { display: none; padding: 10px 14px; gap: 8px; background: var(--surface); border-bottom: 1px solid var(--border); position: sticky; top: var(--nav-h); z-index: 190; }
.mobile-bar .nav-search { max-width: 100%; }

/* ── Hamburger ── */
.nav-hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 6px; border: none; background: none; flex-shrink: 0; }
.nav-hamburger span { display: block; width: 21px; height: 2px; background: var(--text); border-radius: 2px; transition: transform .25s, opacity .25s; }
.nav-hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.nav-hamburger.open span:nth-child(2) { opacity: 0; }
.nav-hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
.nav-mobile-menu {
    display: none; position: fixed; top: var(--nav-h); left: 0; right: 0; z-index: 199;
    background: var(--surface); border-bottom: 1px solid var(--border);
    box-shadow: var(--shadow-lg);
    padding: 10px 14px 18px; flex-direction: column; gap: 4px;
    max-height: calc(100vh - var(--nav-h)); overflow-y: auto;
}
.nav-mobile-menu.open { display: flex; }
.nav-mobile-link { display: block; padding: 12px 14px; border-radius: var(--r-sm); font-size: 14.5px; font-weight: 600; color: var(--text); text-decoration: none; transition: background .15s; }
.nav-mobile-link:hover { background: var(--grey); }
.nav-mobile-divider { height: 1px; background: var(--border); margin: 8px 4px; }
.nav-mobile-btn { display: block; padding: 13px; border-radius: var(--r-sm); font-size: 14.5px; font-weight: 700; text-align: center; text-decoration: none; margin-top: 4px; background: var(--brand); color: #fff; }
.nav-mobile-cats { display: flex; flex-direction: column; gap: 2px; }

/* ════════════════════════════════════════════════════════════════
   SIDEBAR CATÉGORIES + HERO
════════════════════════════════════════════════════════════════ */
.home-top { display: flex; gap: 20px; margin: 20px 28px; align-items: stretch; }
.cat-sidebar {
    flex: 0 0 250px; background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); box-shadow: var(--shadow-sm); overflow: hidden;
    display: flex; flex-direction: column;
}
.cat-sidebar-hd {
    padding: 15px 18px; font-family: var(--display); font-weight: 700; font-size: 13.5px;
    color: #fff; background: linear-gradient(135deg, var(--navy), var(--navy-2));
    letter-spacing: .3px; display: flex; align-items: center; gap: 8px; flex-shrink: 0;
}
.cat-sidebar-list { flex: 1; overflow-y: auto; padding: 6px 0; }
.cat-sidebar-list::-webkit-scrollbar { width: 5px; }
.cat-sidebar-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.cat-sidebar-item {
    display: flex; align-items: center; gap: 11px; padding: 10px 18px;
    font-size: 13.5px; font-weight: 600; color: var(--text-2); text-decoration: none;
    transition: all .15s; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.cat-sidebar-item:hover { background: var(--grey); color: var(--text); }
.cat-sidebar-item.active { background: var(--brand-mlt); color: var(--brand-dk); font-weight: 800; box-shadow: inset 3px 0 0 var(--brand); }
.csi-ico { font-size: 15px; flex-shrink: 0; width: 18px; text-align: center; }

/* ── "Autres catégories" repliable (façon Jumia) ── */
.cat-sidebar-more { border-top: 1px solid var(--border); margin-top: 4px; }
.cat-sidebar-more summary {
    display: flex; align-items: center; gap: 11px; padding: 10px 18px;
    font-size: 13.5px; font-weight: 700; color: var(--brand-dk); cursor: pointer;
    list-style: none; user-select: none; transition: background .15s;
}
.cat-sidebar-more summary::-webkit-details-marker { display: none; }
.cat-sidebar-more summary::after { content: '▾'; margin-left: auto; font-size: 11px; color: var(--muted); transition: transform .2s; }
.cat-sidebar-more[open] summary::after { transform: rotate(180deg); }
.cat-sidebar-more summary:hover { background: var(--grey); }
.csi-count {
    margin-left: auto; background: var(--brand-mlt); color: var(--brand-dk);
    font-size: 10.5px; font-weight: 800; padding: 1px 7px; border-radius: 20px; flex-shrink: 0;
}

/* ── Carrousel du hero : plusieurs diapositives qui défilent automatiquement,
      façon Jumia (points cliquables, pause au survol) ── */
.hero-carousel { position: relative; flex: 1; min-width: 0; border-radius: var(--r); min-height: 336px; overflow: hidden; }
.hero-slide {
    position: absolute; inset: 0;
    opacity: 0; visibility: hidden; pointer-events: none;
    transform: scale(1.02);
    transition: opacity .7s ease, transform .7s ease, visibility 0s linear .7s;
}
.hero-slide.is-active {
    opacity: 1; visibility: visible; pointer-events: auto; transform: scale(1); z-index: 1;
    transition: opacity .7s ease, transform .7s ease, visibility 0s linear 0s;
}
.hero-dots {
    position: absolute; left: 50%; bottom: 14px; transform: translateX(-50%); z-index: 3;
    display: flex; align-items: center; gap: 7px;
}
.hero-dot {
    width: 7px; height: 7px; border-radius: 50%; background: rgba(255,255,255,.4);
    border: none; padding: 0; cursor: pointer; transition: all .25s; flex-shrink: 0;
}
.hero-dot:hover { background: rgba(255,255,255,.7); }
.hero-dot.is-active { background: #fff; width: 22px; border-radius: 5px; }

.hero {
    background: linear-gradient(120deg, var(--navy) 0%, #241a6b 50%, var(--brand) 100%);
    border-radius: var(--r);
    display: flex; align-items: center; justify-content: space-between; gap: 24px;
    overflow: hidden;
    padding: 40px 42px; height: 100%;
}
/* Photo de fond de la diapositive (vraie image, chargée en <img> pour un
   vrai lazy-loading / priorité réseau — pas en CSS background-image) */
.hero-bg-img {
    position: absolute; inset: 0; width: 100%; height: 100%;
    object-fit: cover; object-position: center; z-index: 0;
}
.hero-scrim {
    position: absolute; inset: 0; z-index: 0;
    background: linear-gradient(100deg,
        rgba(8,8,26,.94) 0%, rgba(10,10,32,.86) 32%,
        rgba(10,10,32,.45) 60%, rgba(10,10,32,.18) 100%);
}
.hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.06) 1px, transparent 0);
    background-size: 28px 28px; pointer-events: none;
}
.hero::after {
    content: ''; position: absolute; right: -80px; bottom: -100px; width: 320px; height: 320px;
    border-radius: 50%; background: radial-gradient(circle, rgba(165,180,252,.28) 0%, transparent 70%);
    pointer-events: none;
}
/* Balayage lumineux qui traverse la bannière (effet "vivant") */
.hero-shine {
    position: absolute; top: 0; left: -35%; width: 30%; height: 100%; z-index: 1;
    background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.14) 50%, transparent 100%);
    transform: skewX(-18deg);
    animation: heroShineSweep 5.5s ease-in-out infinite;
    pointer-events: none;
}
@keyframes heroShineSweep { 0% { left: -35%; } 55% { left: 130%; } 100% { left: 130%; } }
.hero-text { flex: 1; position: relative; z-index: 2; }
.hero-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
    color: #e0e7ff; font-size: 11.5px; font-weight: 700;
    padding: 5px 13px; border-radius: 20px; margin-bottom: 16px;
    animation: heroFadeUp .6s ease both;
}
.hero-badge-dot { width: 6px; height: 6px; border-radius: 50%; background: #a5b4fc; box-shadow: 0 0 6px #a5b4fc; animation: blink 2s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
@keyframes heroFadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
.hero-title { font-family: var(--display); font-weight: 700; font-size: clamp(24px, 3.4vw, 38px); color: #fff; line-height: 1.15; margin-bottom: 12px; letter-spacing: -.6px; animation: heroFadeUp .65s .08s ease both; }
.hero-sub { font-size: 14.5px; color: rgba(255,255,255,.72); margin-bottom: 22px; max-width: 460px; line-height: 1.6; animation: heroFadeUp .65s .16s ease both; }
.hero-btns { display: flex; gap: 12px; flex-wrap: wrap; animation: heroFadeUp .65s .24s ease both; }
.hero-btn-primary {
    padding: 13px 26px; border-radius: 50px; font-size: 13.5px; font-weight: 700; font-family: var(--font);
    background: #fff; color: var(--brand-dk); border: none; cursor: pointer; text-decoration: none;
    transition: all .15s; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 6px 20px rgba(0,0,0,.2);
}
.hero-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(0,0,0,.28); color: var(--brand-dk); }
.hero-btn-secondary {
    padding: 13px 26px; border-radius: 50px; font-size: 13.5px; font-weight: 700; font-family: var(--font);
    background: rgba(255,255,255,.1); color: #fff; border: 1.5px solid rgba(255,255,255,.28);
    cursor: pointer; text-decoration: none; transition: all .15s; display: inline-flex; align-items: center; gap: 7px;
    backdrop-filter: blur(8px);
}
.hero-btn-secondary:hover { background: rgba(255,255,255,.2); color: #fff; transform: translateY(-2px); }
@media (prefers-reduced-motion: reduce) {
    .hero-badge-dot, .hero-shine,
    .hero-badge, .hero-title, .hero-sub, .hero-btns,
    .flash-section, .flash-card-badge, .reveal { animation: none !important; }
    .reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
    .hero-slide { transition: opacity .3s ease !important; transform: none !important; }
}

/* ── Cartes latérales (aide / vendre / boutiques), façon colonne droite Jumia ── */
.side-cards { flex: 0 0 220px; display: flex; flex-direction: column; gap: 12px; }
.side-card {
    display: flex; align-items: center; gap: 12px;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--r);
    padding: 14px 15px; text-decoration: none; color: inherit;
    box-shadow: var(--shadow-sm); transition: box-shadow .2s, transform .2s, border-color .2s;
}
.side-card:hover { border-color: var(--brand-lt); box-shadow: var(--shadow-lg); transform: translateY(-2px); }
.side-card-ico {
    width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
    background: var(--brand-mlt); color: var(--brand-dk);
    display: flex; align-items: center; justify-content: center;
}
.side-card-body { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.side-card-title { font-size: 12.5px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.side-card-sub { font-size: 11px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Reveal au scroll (fondu + glissement doux à l'apparition) ──
   Utilise @keyframes (animation) plutôt que `transition` : la card garde
   ainsi sa propre transition de survol (hover) sans qu'elle soit écrasée. */
.reveal { opacity: 0; transform: translateY(26px); }
.reveal.is-visible {
    animation: revealIn .6s cubic-bezier(.2,.7,.3,1) both;
    animation-delay: var(--rd, 0ms);
}
@keyframes revealIn {
    from { opacity: 0; transform: translateY(26px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ════════════════════════════════════════════════════════════════
   MAIN CONTENT
════════════════════════════════════════════════════════════════ */
.c-main { padding: 0 28px 60px; }
#resultsRoot.is-loading { pointer-events: none; }
.sec-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 12px; }
.sec-title { font-family: var(--display); font-size: 19px; font-weight: 700; color: var(--text); letter-spacing: -.3px; display: flex; align-items: center; gap: 8px; }
.sec-title strong { color: var(--brand-dk); }
.sec-link { font-size: 12.5px; font-weight: 700; color: var(--brand-dk); text-decoration: none; white-space: nowrap; }
.sec-link:hover { text-decoration: underline; }

/* ── Filtres catégories (mobile) ── */
.cats { display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 6px; scrollbar-width: none; -ms-overflow-style: none; }
.cats::-webkit-scrollbar { display: none; }
.cat-pill {
    display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 50px; flex-shrink: 0;
    font-size: 13px; font-weight: 600; font-family: var(--font); border: 1.5px solid var(--border); background: var(--surface);
    color: var(--text-2); cursor: pointer; white-space: nowrap; text-decoration: none; transition: all .18s;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.cat-pill:hover { border-color: var(--brand); color: var(--brand-dk); background: var(--brand-mlt); transform: translateY(-1px); }
.cat-pill.active { background: linear-gradient(135deg, var(--brand), var(--brand-dk)); color: #fff; border-color: var(--brand-dk); box-shadow: 0 4px 14px rgba(99,102,241,.35); transform: translateY(-1px); }

/* ── Recommandés (scroll horizontal) ── */
.reco-row-outer {
    position: relative; padding: 2px 0 16px; overflow-x: auto; overflow-y: hidden;
    -webkit-overflow-scrolling: touch; scroll-snap-type: x proximity;
    scrollbar-width: thin; scrollbar-color: var(--border) transparent;
}
.reco-row-outer::-webkit-scrollbar { height: 6px; }
.reco-row-outer::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.reco-row { display: flex; gap: 16px; width: max-content; }
.reco-card {
    flex: 0 0 190px; width: 190px; scroll-snap-align: start;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm); transition: box-shadow .2s, transform .2s, border-color .2s;
    text-decoration: none; color: inherit; display: flex; flex-direction: column;
}
.reco-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: var(--brand-lt); }
.reco-card-img { height: 130px; position: relative; overflow: hidden; flex-shrink: 0; background: var(--grey); display: flex; align-items: center; justify-content: center; }
.reco-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
.reco-card:hover .reco-card-img img { transform: scale(1.07); }
.reco-card-ph { font-size: 32px; opacity: .3; }
.reco-card-badge { position: absolute; top: 8px; left: 8px; background: var(--brand); color: #fff; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 20px; display: inline-flex; align-items: center; gap: 3px; }
.reco-card-body { padding: 11px 13px; display: flex; flex-direction: column; gap: 4px; flex: 1; }
.reco-card-shop { font-size: 10.5px; color: var(--muted); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.reco-card-name { font-size: 12.5px; font-weight: 700; color: var(--text); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; }
.reco-card-price-row { display: flex; align-items: baseline; gap: 6px; margin-top: auto; flex-wrap: wrap; }
.reco-card-price { font-size: 14px; font-weight: 800; color: var(--brand-dk); font-family: var(--mono); }
.reco-card-orig { font-size: 10.5px; color: var(--muted); text-decoration: line-through; font-family: var(--mono); }
@media (max-width: 480px) { .reco-card { flex-basis: 152px; width: 152px; } .reco-card-img { height: 105px; } }

/* ── Ventes flash ── */
.flash-section {
    background: linear-gradient(135deg, var(--navy) 0%, var(--brand-dk) 55%, var(--brand) 100%);
    border-radius: var(--r); padding: 20px 20px 22px; position: relative; overflow: hidden; margin-bottom: 32px;
    animation: flashGlow 2.8s ease-in-out infinite;
}
@keyframes flashGlow {
    0%, 100% { box-shadow: 0 8px 28px rgba(79,70,229,.28); }
    50%      { box-shadow: 0 8px 40px rgba(79,70,229,.5), 0 0 0 4px rgba(99,102,241,.14); }
}
.flash-section::before { content: ''; position: absolute; right: -40px; top: -40px; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,.08); pointer-events: none; }
.flash-section-hd { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; position: relative; z-index: 1; flex-wrap: wrap; }
.flash-section-title { font-family: var(--display); font-size: 18px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px; }
.flash-section-title .bolt { display: inline-block; animation: boltPulse 1.4s ease-in-out infinite; }
@keyframes boltPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.25); } }
.flash-section-sub { font-size: 12px; color: rgba(255,255,255,.85); font-weight: 600; }
.flash-countdown {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(0,0,0,.28); border: 1px solid rgba(255,255,255,.2);
    color: #fff; font-size: 12px; font-weight: 600; padding: 6px 13px; border-radius: 20px;
}
.flash-countdown strong { font-family: var(--mono); font-weight: 800; letter-spacing: .5px; }
.flash-row-outer {
    position: relative; overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch;
    scroll-snap-type: x proximity; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.4) transparent;
}
.flash-row-outer::-webkit-scrollbar { height: 6px; }
.flash-row-outer::-webkit-scrollbar-thumb { background: rgba(255,255,255,.4); border-radius: 4px; }
.flash-row { display: flex; gap: 14px; width: max-content; }
.flash-card {
    flex: 0 0 168px; width: 168px; scroll-snap-align: start;
    background: var(--surface); border-radius: 14px; overflow: hidden; text-decoration: none; color: inherit;
    display: flex; flex-direction: column; box-shadow: 0 4px 14px rgba(0,0,0,.18); transition: transform .2s;
}
.flash-card:hover { transform: translateY(-4px); }
.flash-card-img { height: 112px; position: relative; background: var(--grey); overflow: hidden; display: flex; align-items: center; justify-content: center; }
.flash-card-img img { width: 100%; height: 100%; object-fit: cover; }
.flash-card-ph { font-size: 28px; opacity: .3; }
.flash-card-badge { position: absolute; top: 7px; left: 7px; background: var(--navy); color: #a5b4fc; font-size: 10.5px; font-weight: 800; padding: 3px 8px; border-radius: 20px; animation: badgePulse 1.9s ease-in-out infinite; }
@keyframes badgePulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.1); } }
.flash-card-body { padding: 9px 11px 11px; display: flex; flex-direction: column; gap: 3px; }
.flash-card-shop { font-size: 10px; color: var(--muted); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.flash-card-name { font-size: 12px; font-weight: 700; color: var(--text); line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.4em; }
.flash-card-price-row { display: flex; align-items: baseline; gap: 5px; flex-wrap: wrap; }
.flash-card-price { font-size: 14px; font-weight: 800; color: var(--brand-dk); font-family: var(--mono); }
.flash-card-orig { font-size: 10px; color: var(--muted); text-decoration: line-through; font-family: var(--mono); }
@media (max-width: 480px) { .flash-card { flex-basis: 138px; width: 138px; } .flash-card-img { height: 92px; } }

/* ── Grille produits (catalogue complet) ── */
.prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 16px; }
.prod-card {
    position: relative;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm); transition: box-shadow .2s, transform .2s, border-color .2s;
    text-decoration: none; color: inherit; display: flex; flex-direction: column;
}
.prod-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: var(--brand-lt); }
.prod-card-img { height: 150px; position: relative; overflow: hidden; flex-shrink: 0; background: var(--grey); display: flex; align-items: center; justify-content: center; }
.prod-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
.prod-card:hover .prod-card-img img { transform: scale(1.06); }
.prod-card-ph { font-size: 36px; opacity: .3; }
.prod-card-badge { position: absolute; top: 8px; left: 8px; font-size: 10px; font-weight: 800; color: #fff; padding: 3px 9px; border-radius: 20px; display: inline-flex; align-items: center; gap: 3px; }
.prod-card-body { padding: 11px 13px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
.prod-card-name { font-size: 13px; font-weight: 700; color: var(--text); line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.prod-card-shop { font-size: 11px; color: var(--muted); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.prod-card-cat { font-size: 10.5px; color: var(--brand-dk); font-weight: 700; }
.prod-card-footer { padding: 10px 13px 13px; display: flex; align-items: center; justify-content: space-between; gap: 8px; border-top: 1px solid var(--grey-2); margin-top: auto; }
.prod-card-price { font-size: 14.5px; font-weight: 800; color: var(--brand-dk); font-family: var(--mono); }
.prod-card-orig { font-size: 10.5px; color: var(--muted); text-decoration: line-through; margin-left: 3px; font-family: var(--mono); }
.prod-card-cta {
    font-size: 11.5px; font-weight: 700; color: #fff; background: var(--brand); border: none; border-radius: 50px;
    padding: 7px 14px; cursor: pointer; text-decoration: none; white-space: nowrap; transition: background .15s;
}
.prod-card-cta:hover { background: var(--brand-dk); color: #fff; }
.prod-card-cta.out { background: var(--grey-2); color: var(--muted); cursor: not-allowed; }

/* ── Bouton favoris (♥) sur la carte produit ── */
.prod-card-fav {
    position: absolute; top: 8px; right: 8px; z-index: 2;
    width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
    background: rgba(255,255,255,.92); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--muted); cursor: pointer; text-decoration: none;
    transition: all .15s; backdrop-filter: blur(4px);
}
.prod-card-fav:hover { color: #e11d48; border-color: #fecdd3; transform: scale(1.08); }
.prod-card-fav.is-fav { color: #e11d48; border-color: #fecdd3; background: #fff1f2; }
.prod-card-fav.is-fav svg { fill: currentColor; }
.prod-card-fav.is-busy { opacity: .55; pointer-events: none; }

/* ── Note moyenne de la boutique (étoiles, à partir des avis réels) ── */
.prod-card-rating { display: flex; align-items: center; gap: 4px; }
.prod-card-rating span { font-size: 10.5px; font-weight: 700; color: var(--text-2); }
.prod-card-sold { display: flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 700; color: #b45309; }
.star-ico.is-filled { color: #f59e0b; }
.star-ico.is-empty { color: #d9dee6; }

/* ── Rangées "Populaire en ..." (cartes produit en scroll horizontal) ── */
.cat-group-row { display: flex; gap: 16px; width: max-content; }
.prod-card--row { flex: 0 0 190px; width: 190px; }
@media (max-width: 480px) { .prod-card--row { flex-basis: 158px; width: 158px; } }

/* ── Bannière promo (boutiques vérifiées), façon bloc pub Jumia ── */
.promo-banner {
    display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    background: linear-gradient(120deg, var(--brand-mlt) 0%, #f5f3ff 100%);
    border: 1px solid var(--brand-lt); border-radius: var(--r);
    padding: 20px 24px; margin-bottom: 32px; text-decoration: none; color: inherit;
    transition: box-shadow .2s, transform .2s, border-color .2s;
}
.promo-banner:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); border-color: var(--brand); }
.promo-banner-ico {
    width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
    background: #fff; color: var(--brand-dk);
    display: flex; align-items: center; justify-content: center;
    box-shadow: var(--shadow-sm);
}
.promo-banner-body { display: flex; flex-direction: column; gap: 3px; flex: 1; min-width: 200px; }
.promo-banner-title { font-family: var(--display); font-size: 15.5px; font-weight: 700; color: var(--text); }
.promo-banner-sub { font-size: 12.5px; color: var(--text-2); line-height: 1.5; }
.promo-banner-cta {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    background: var(--brand); color: #fff; font-size: 12.5px; font-weight: 700;
    padding: 9px 16px; border-radius: 50px; white-space: nowrap;
}

.c-empty { grid-column: 1/-1; padding: 72px 20px; text-align: center; background: var(--surface); border-radius: var(--r); border: 1px dashed var(--border); }
.c-empty-ico { font-size: 52px; display: block; opacity: .3; margin-bottom: 14px; }
.c-empty-title { font-family: var(--display); font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
.c-empty-sub { font-size: 13.5px; color: var(--muted); }
.c-pagination { display: flex; justify-content: center; padding: 24px 0 8px; }
.c-pagination .pagination { gap: 4px; }
.c-pagination .page-link { color: var(--text-2); border-color: var(--border); border-radius: 8px; font-size: 13px; }
.c-pagination .page-item.active .page-link { background: var(--brand); border-color: var(--brand-dk); color: #fff; }
.c-pagination .page-link:hover { color: var(--brand-dk); border-color: var(--brand-lt); background: var(--brand-mlt); }
.count-line { font-size: 13px; color: var(--muted); margin-bottom: 14px; }
.reset-link { display: inline-flex; align-items: center; gap: 3px; color: var(--brand-dk); font-weight: 700; text-decoration: none; }
.reset-link:hover { text-decoration: underline; }

/* ── Bandeau "résultats de recherche" (façon Jumia) ── */
.search-banner {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    background: var(--brand-mlt); border: 1px solid var(--brand-lt); color: var(--brand-dk);
    padding: 13px 18px; border-radius: var(--r-sm); margin-bottom: 20px;
    font-size: 13.5px; font-weight: 600;
}
.search-banner strong { color: var(--brand-dk); }
.search-banner-clear {
    margin-left: auto; display: inline-flex; align-items: center; gap: 4px;
    background: #fff; border: 1px solid var(--brand-lt); color: var(--brand-dk);
    padding: 6px 13px; border-radius: 20px; font-size: 12px; font-weight: 700;
    text-decoration: none; transition: all .15s; flex-shrink: 0;
}
.search-banner-clear:hover { background: var(--brand); color: #fff; border-color: var(--brand-dk); }

/* ════════════════════════════════════════════════════════════════
   BANDEAU DE CONFIANCE
════════════════════════════════════════════════════════════════ */
.trust-strip {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
    margin: 0 28px 24px;
}
.trust-item {
    display: flex; align-items: center; gap: 10px;
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-sm);
    padding: 12px 14px; box-shadow: var(--shadow-sm);
}
.trust-ico {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    background: var(--brand-mlt); color: var(--brand-dk);
    display: flex; align-items: center; justify-content: center;
}
.trust-txt { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.trust-txt strong { font-size: 12px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.trust-txt span { font-size: 10.5px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ════════════════════════════════════════════════════════════════
   TÉMOIGNAGES CLIENTS
════════════════════════════════════════════════════════════════ */
.testi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; margin-top: 8px; }
.testi-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--r);
    padding: 20px 22px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; gap: 12px;
}
.testi-stars { display: flex; gap: 2px; }
.testi-text { font-size: 13px; color: var(--text-2); line-height: 1.6; font-style: italic; margin: 0; flex: 1; }
.testi-author { display: flex; align-items: center; gap: 10px; }
.testi-avatar {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--brand), var(--brand-dk)); color: #fff;
    font-size: 13px; font-weight: 800; display: flex; align-items: center; justify-content: center;
}
.testi-author-body { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.testi-author-name { font-size: 12.5px; font-weight: 700; color: var(--text); }
.testi-author-sub { font-size: 11px; color: var(--muted); }

/* ════════════════════════════════════════════════════════════════
   RETOUR EN HAUT
════════════════════════════════════════════════════════════════ */
.back-to-top {
    position: fixed; right: 20px; bottom: 20px; z-index: 150;
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--brand); color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 20px rgba(99,102,241,.4);
    opacity: 0; visibility: hidden; transform: translateY(10px);
    transition: opacity .25s, transform .25s, visibility 0s linear .25s, background .15s;
}
.back-to-top.is-visible { opacity: 1; visibility: visible; transform: translateY(0); transition: opacity .25s, transform .25s, background .15s; }
.back-to-top:hover { background: var(--brand-dk); }

/* ════════════════════════════════════════════════════════════════
   SQUELETTE DE CHARGEMENT (recherche en direct)
════════════════════════════════════════════════════════════════ */
.skeleton-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 16px; }
.skeleton-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; }
.skeleton-block {
    background: linear-gradient(90deg, var(--grey-2) 25%, #f3f4f8 37%, var(--grey-2) 63%);
    background-size: 400% 100%; animation: skeletonShimmer 1.4s ease infinite;
}
.skeleton-card .skeleton-block.img { height: 150px; }
.skeleton-card .skeleton-body { padding: 11px 13px; display: flex; flex-direction: column; gap: 8px; }
.skeleton-card .skeleton-block.line { height: 11px; border-radius: 4px; }
.skeleton-card .skeleton-block.line.w-70 { width: 70%; }
.skeleton-card .skeleton-block.line.w-40 { width: 40%; }
@keyframes skeletonShimmer { 0% { background-position: 100% 0; } 100% { background-position: 0 0; } }
@media (prefers-reduced-motion: reduce) { .skeleton-block { animation: none; } }

/* ════════════════════════════════════════════════════════════════
   FOOTER
════════════════════════════════════════════════════════════════ */
.w-footer { background: var(--navy); padding: 48px 28px 24px; color: rgba(255,255,255,.5); margin-top: 24px; }
.w-footer-inner { max-width: 1320px; margin: 0 auto; display: flex; justify-content: space-between; align-items: flex-start; gap: 28px; flex-wrap: wrap; margin-bottom: 30px; }
.w-footer-brand { max-width: 280px; }
.w-footer-logo { display: flex; align-items: center; gap: 9px; font-family: var(--display); font-size: 17px; font-weight: 700; color: #fff; text-decoration: none; margin-bottom: 10px; }
.w-footer-logo img { width: 32px; height: 32px; object-fit: cover; border-radius: 8px; }
.w-footer-desc { font-size: 12.5px; line-height: 1.65; }
.w-footer-col h4 { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,.7); margin: 0 0 14px; }
.w-footer-col a { display: block; font-size: 13px; color: rgba(255,255,255,.5); text-decoration: none; margin-bottom: 8px; transition: color .15s; }
.w-footer-col a:hover { color: #fff; }
.w-footer-bottom { max-width: 1320px; margin: 0 auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,.08); display: flex; align-items: center; justify-content: space-between; font-size: 12px; flex-wrap: wrap; gap: 8px; }

/* ════════════════════════════════════════════════════════════════
   RESPONSIVE — ultra adaptatif, du petit téléphone au très grand écran
════════════════════════════════════════════════════════════════ */

/* ── Très grands écrans : on centre le contenu pour éviter les lignes
      trop étirées (bannière géante, cartes trop larges) ── */
@media (min-width: 1680px) {
    .home-top, .c-main { max-width: 1680px; margin-left: auto; margin-right: auto; }
}
@media (min-width: 1400px) {
    .prod-grid { grid-template-columns: repeat(6, 1fr); }
}
@media (min-width: 1100px) and (max-width: 1399px) {
    .prod-grid { grid-template-columns: repeat(5, 1fr); }
}
@media (min-width: 961px) and (max-width: 1099px) {
    .prod-grid { grid-template-columns: repeat(3, 1fr); }
}

/* ── Cartes latérales (aide/vendre/boutiques) : dès que la place manque pour
      les garder en colonne à droite, elles passent en bandeau horizontal
      sous le carrousel au lieu de disparaître — restent visibles partout,
      y compris sur mobile. ── */
@media (max-width: 1200px) {
    .home-top { flex-wrap: wrap; }
    .side-cards { flex: 1 1 100%; flex-direction: row; }
    .side-card { flex: 1; min-width: 0; }
}

/* ── Petit desktop / grande tablette : sidebar plus étroite ── */
@media (max-width: 1080px) {
    .cat-sidebar { flex-basis: 200px; }
}

/* ── Tablette : nav condensée, sidebar catégories remplacée par les
      pills + le menu mobile (assez de place pour un confort tactile) ── */
@media (max-width: 960px) {
    .nav-links { display: none; }
    .nav-hamburger { display: flex; padding: 9px; }
    .mobile-bar { display: flex; }
    .nav > .nav-search { display: none; } /* seule la barre de la nav desktop est cachée — pas celle de .mobile-bar (même classe) */
    .cat-sidebar { display: none; }
    .home-top { margin: 14px 16px; }
    .hero { padding: 26px 24px; }
    .hero-carousel { min-height: 280px; }
    .prod-grid { grid-template-columns: repeat(3, 1fr); }
    .trust-strip { grid-template-columns: repeat(2, 1fr); margin: 0 16px 20px; }
}

/* ── Tablette portrait / phablette ── */
@media (max-width: 760px) {
    .prod-grid { grid-template-columns: repeat(3, 1fr); gap: 12px; }
}

/* ── Mobile ── */
@media (max-width: 640px) {
    .nav { padding: 0 12px; gap: 8px; height: 56px; }
    :root { --nav-h: 56px; }
    .nav-logo img { height: 30px; width: 30px; }
    .nav-logo { font-size: 16.5px; }
    .nav-orders-btn svg + span { display: none; } /* cache le libellé texte seulement quand il y a une icône avant (ex: "Mes commandes") — pas "Connexion" qui n'a pas d'icône */
    .nav-orders-btn, .nav-btn-primary { padding: 8px 12px; font-size: 12px; }
    .nav-hamburger { min-width: 40px; min-height: 40px; justify-content: center; align-items: center; }

    .mobile-bar { padding: 8px 12px; }
    .home-top { margin: 12px 12px; }
    .trust-strip { grid-template-columns: 1fr 1fr; gap: 10px; margin: 0 12px 18px; }
    .trust-item { padding: 10px 11px; }
    .back-to-top { right: 14px; bottom: 14px; width: 40px; height: 40px; }
    .side-cards { flex-direction: column; gap: 10px; }
    .side-card { padding: 12px 14px; }
    .hero-carousel { min-height: 336px; border-radius: 12px; }
    .hero-dots { bottom: 10px; }
    .hero { padding: 22px 18px; border-radius: 12px; }
    .hero-badge { font-size: 10.5px; padding: 4px 11px; }
    .sec-title { font-size: 16px; }
    .hero-title { font-size: 21px; }
    .hero-sub { font-size: 12.5px; margin-bottom: 18px; }
    .hero-btns { gap: 8px; flex-direction: column; align-items: stretch; }
    .hero-btn-primary, .hero-btn-secondary { padding: 12px 18px; font-size: 13px; justify-content: center; }

    .c-main { padding: 0 12px 40px; }
    .flash-section { padding: 16px 14px 18px; }
    .flash-countdown { font-size: 11px; padding: 5px 10px; }
    .cats { margin-bottom: 14px; }

    .search-banner { flex-direction: column; align-items: flex-start; gap: 8px; padding: 14px 16px; }
    .search-banner-clear { margin-left: 0; align-self: flex-start; }

    .prod-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .prod-card-img { height: 120px; }
    .prod-card-body { padding: 10px 11px; }
    .prod-card-footer { flex-direction: column; align-items: stretch; gap: 8px; padding: 9px 11px 11px; }
    .prod-card-price { font-size: 13.5px; }
    .prod-card-cta { width: 100%; text-align: center; padding: 9px 14px; }

    .w-footer { padding: 32px 18px 20px; }
    .w-footer-inner { flex-direction: column; gap: 26px; }
    .w-footer-brand { max-width: 100%; }
    .w-footer-bottom { flex-direction: column; text-align: center; }
}

/* ── Très petit mobile (iPhone SE, vieux Android ≤ 380px) ── */
@media (max-width: 380px) {
    .nav { padding: 0 10px; }
    .nav-actions { gap: 6px; }
    .prod-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
    .hero-title { font-size: 19px; }
    .hero-carousel { min-height: 366px; }
    .hero { padding: 18px 15px; }
    .flash-card, .reco-card { flex-basis: 128px; width: 128px; }
}

/* ── Confort tactile : cibles ≥ 40px sur tout écran à pointeur grossier ── */
@media (pointer: coarse) {
    .nav-hamburger { min-width: 40px; min-height: 40px; }
    .nav-search-btn { min-width: 40px; }
    .cat-pill, .prod-card-cta, .hero-btn-primary, .hero-btn-secondary,
    .nav-orders-btn, .nav-btn-primary, .search-banner-clear { min-height: 40px; }
}
</style>
@endpush

@section('content')
@php
    $roleMap = ['superadmin'=>'admin.dashboard','admin'=>'boutique.dashboard','vendeur'=>'vendeur.dashboard','client'=>'client.dashboard','company'=>'company.dashboard','livreur'=>'livreur.dashboard'];
@endphp

{{-- ══ NAVBAR ══ --}}
<nav class="nav">
    <a href="{{ url('/') }}" class="nav-logo">
        <img src="/images/shopio-logo-192.png" alt="{{ config('app.name', 'Shopio') }}">
        <span><span class="brand-part">Shop</span><span class="brand-accent">io</span></span>
    </a>
    <div class="nav-links">
        <a href="{{ url('/') }}" class="nav-link active">{!! \App\Support\IconLibrary::svg('home') !!} Accueil</a>
        <a href="{{ route('shops.index') }}" class="nav-link">{!! \App\Support\IconLibrary::svg('store') !!} Boutiques</a>
    </div>
    <form method="GET" action="{{ url('/') }}" class="nav-search" data-ajax>
        <input type="text" name="s" value="{{ request('s') }}" placeholder="Que recherchez-vous ?" autocomplete="off">
        <button class="nav-search-btn" type="submit" aria-label="Rechercher">{!! \App\Support\IconLibrary::svg('search', '', 16) !!}</button>
    </form>
    <div class="nav-actions">
        @guest
            <a href="{{ route('login') }}" class="nav-orders-btn"><span>Connexion</span></a>
            <a href="{{ route('register') }}" class="nav-btn-primary">S'inscrire</a>
        @else
            @if(Auth::user()->role === 'client')
                <a href="{{ route('client.orders.index') }}" class="nav-orders-btn">{!! \App\Support\IconLibrary::svg('package', '', 15) !!} <span>Mes commandes</span></a>
            @endif
            @if(isset($roleMap[Auth::user()->role]))
                <a href="{{ route($roleMap[Auth::user()->role]) }}" class="nav-btn-primary">Mon dashboard →</a>
            @endif
        @endguest
    </div>
    <button class="nav-hamburger" id="navHamburger" aria-label="Menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</nav>

{{-- ══ BARRE MOBILE (recherche) ══ --}}
<div class="mobile-bar">
    <form method="GET" action="{{ url('/') }}" class="nav-search" data-ajax>
        <input type="text" name="s" value="{{ request('s') }}" placeholder="Rechercher un produit…" autocomplete="off">
        <button class="nav-search-btn" type="submit" aria-label="Rechercher">{!! \App\Support\IconLibrary::svg('search', '', 16) !!}</button>
    </form>
</div>

{{-- ══ MENU MOBILE (liens + catégories) ══ --}}
<div class="nav-mobile-menu" id="navMobileMenu">
    <a href="{{ url('/') }}" class="nav-mobile-link">{!! \App\Support\IconLibrary::svg('home') !!} Accueil</a>
    <a href="{{ route('shops.index') }}" class="nav-mobile-link">{!! \App\Support\IconLibrary::svg('store') !!} Boutiques</a>
    <div class="nav-mobile-divider"></div>
    @guest
        <a href="{{ route('login') }}" class="nav-mobile-link">Connexion</a>
        <a href="{{ route('register') }}" class="nav-mobile-btn">S'inscrire</a>
    @else
        @if(Auth::user()->role === 'client')
            <a href="{{ route('client.orders.index') }}" class="nav-mobile-link">{!! \App\Support\IconLibrary::svg('package') !!} Mes commandes</a>
        @endif
        @if(isset($roleMap[Auth::user()->role]))
            <a href="{{ route($roleMap[Auth::user()->role]) }}" class="nav-mobile-btn">Mon dashboard →</a>
        @endif
    @endguest
    @if($categories->isNotEmpty())
    <div class="nav-mobile-divider"></div>
    <div class="nav-mobile-cats">
        @foreach($categories as $cat)
        <a href="{{ url('/') }}?{{ http_build_query(array_filter(['cat' => $cat, 's' => request('s')])) }}#catalogue" class="nav-mobile-link">{!! \App\Support\IconLibrary::categorySvg($cat) !!} {{ $cat }}</a>
        @endforeach
    </div>
    @endif
</div>

{{-- ══ SIDEBAR CATÉGORIES + HERO ══ --}}
@php
    $catsVisibleCount = 11;
    $catsVisible = $categories->take($catsVisibleCount);
    $catsMore = $categories->slice($catsVisibleCount)->values();
    $activeInMore = request('cat') && $catsMore->contains(request('cat'));
@endphp
<div class="home-top">
    @if($categories->isNotEmpty())
    <aside class="cat-sidebar reveal">
        <div class="cat-sidebar-hd">{!! \App\Support\IconLibrary::svg('grid', 'csi-ico') !!} Toutes les catégories</div>
        <nav class="cat-sidebar-list">
            <a href="{{ url('/') }}?{{ http_build_query(array_filter(['s' => request('s')])) }}#catalogue" class="cat-sidebar-item {{ !request('cat') ? 'active' : '' }}">
                {!! \App\Support\IconLibrary::svg('eye', 'csi-ico') !!} Tout voir
            </a>
            @foreach($catsVisible as $cat)
            <a href="{{ url('/') }}?{{ http_build_query(array_filter(['cat' => $cat, 's' => request('s')])) }}#catalogue" class="cat-sidebar-item {{ request('cat') === $cat ? 'active' : '' }}">
                {!! \App\Support\IconLibrary::categorySvg($cat, 'csi-ico') !!} {{ $cat }}
            </a>
            @endforeach

            @if($catsMore->isNotEmpty())
            <details class="cat-sidebar-more" {{ $activeInMore ? 'open' : '' }}>
                <summary>
                    {!! \App\Support\IconLibrary::svg('plus', 'csi-ico') !!} Autres catégories
                    <span class="csi-count">{{ $catsMore->count() }}</span>
                </summary>
                @foreach($catsMore as $cat)
                <a href="{{ url('/') }}?{{ http_build_query(array_filter(['cat' => $cat, 's' => request('s')])) }}#catalogue" class="cat-sidebar-item {{ request('cat') === $cat ? 'active' : '' }}">
                    {!! \App\Support\IconLibrary::categorySvg($cat, 'csi-ico') !!} {{ $cat }}
                </a>
                @endforeach
            </details>
            @endif
        </nav>
    </aside>
    @endif

    <div class="hero-carousel reveal" id="heroCarousel">
        @php
            /* Photos libres de droits (licence Unsplash, gratuites, usage commercial
               autorisé sans attribution) — servies via le CDN Unsplash avec redimensionnement
               et compression à la volée (auto=format sert du WebP/AVIF aux navigateurs
               compatibles) pour rester légères malgré la haute qualité. */
            $heroPhoto = fn (string $id, int $w) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=60";
            $slide1Id = 'photo-1758525223453-06095a7459ce'; // clientes souriantes, sacs de courses
            $slide2Id = 'photo-1483985988355-763728e1935b'; // sacs de shopping en papier
            $slide3Id = 'photo-1760001868397-e5995a577e26'; // entrée de boutique
        @endphp
        <section class="hero hero-slide is-active">
            <img class="hero-bg-img" alt=""
                 src="{{ $heroPhoto($slide1Id, 1400) }}"
                 srcset="{{ $heroPhoto($slide1Id, 700) }} 700w, {{ $heroPhoto($slide1Id, 1100) }} 1100w, {{ $heroPhoto($slide1Id, 1600) }} 1600w"
                 sizes="100vw" width="1400" height="560"
                 loading="eager" fetchpriority="high" decoding="async">
            <div class="hero-scrim"></div>
            <div class="hero-shine"></div>
            <div class="hero-text">
                <div class="hero-badge"><span class="hero-badge-dot"></span> <span id="heroProductCount" data-count="{{ $stats['total_products'] ?? 0 }}">0</span> produits disponibles</div>
                <div class="hero-title">Bienvenue sur {{ config('app.name', 'Shopio') }}</div>
                <p class="hero-sub">Découvrez des milliers de produits dans toutes les catégories, chez les meilleures boutiques, livrés directement chez vous.</p>
                <div class="hero-btns">
                    <a href="#catalogue" class="hero-btn-primary">{!! \App\Support\IconLibrary::svg('cart', '', 16) !!} Voir les produits</a>
                    <a href="{{ route('shops.index') }}" class="hero-btn-secondary">{!! \App\Support\IconLibrary::svg('store', '', 16) !!} Parcourir les boutiques</a>
                </div>
            </div>
        </section>

        <section class="hero hero-slide">
            <img class="hero-bg-img" alt=""
                 src="{{ $heroPhoto($slide2Id, 1400) }}"
                 srcset="{{ $heroPhoto($slide2Id, 700) }} 700w, {{ $heroPhoto($slide2Id, 1100) }} 1100w, {{ $heroPhoto($slide2Id, 1600) }} 1600w"
                 sizes="100vw" width="1400" height="560"
                 loading="lazy" decoding="async">
            <div class="hero-scrim"></div>
            <div class="hero-shine"></div>
            <div class="hero-text">
                <div class="hero-badge"><span class="hero-badge-dot"></span> Tous les jours</div>
                <div class="hero-title">Ventes flash quotidiennes</div>
                <p class="hero-sub">Jusqu'à -50% sur une sélection de produits chaque jour, en quantités limitées. Ne les manquez pas !</p>
                <div class="hero-btns">
                    <a href="#catalogue" class="hero-btn-primary">{!! \App\Support\IconLibrary::svg('zap', '', 16) !!} Voir les ventes flash</a>
                    <a href="{{ route('shops.index') }}" class="hero-btn-secondary">{!! \App\Support\IconLibrary::svg('store', '', 16) !!} Parcourir les boutiques</a>
                </div>
            </div>
        </section>

        <section class="hero hero-slide">
            <img class="hero-bg-img" alt=""
                 src="{{ $heroPhoto($slide3Id, 1400) }}"
                 srcset="{{ $heroPhoto($slide3Id, 700) }} 700w, {{ $heroPhoto($slide3Id, 1100) }} 1100w, {{ $heroPhoto($slide3Id, 1600) }} 1600w"
                 sizes="100vw" width="1400" height="560"
                 loading="lazy" decoding="async">
            <div class="hero-scrim"></div>
            <div class="hero-shine"></div>
            <div class="hero-text">
                <div class="hero-badge"><span class="hero-badge-dot"></span> Devenez partenaire</div>
                <div class="hero-title">Vendez sur {{ config('app.name', 'Shopio') }}</div>
                <p class="hero-sub">Créez votre boutique en ligne en quelques minutes et touchez des milliers de clients partout en Guinée.</p>
                <div class="hero-btns">
                    <a href="{{ route('register') }}" class="hero-btn-primary">{!! \App\Support\IconLibrary::svg('store', '', 16) !!} Ouvrir ma boutique</a>
                    <a href="{{ route('shops.index') }}" class="hero-btn-secondary">{!! \App\Support\IconLibrary::svg('eye', '', 16) !!} Voir les boutiques</a>
                </div>
            </div>
        </section>

        <div class="hero-dots">
            <button class="hero-dot is-active" type="button" data-slide-to="0" aria-label="Diapositive 1"></button>
            <button class="hero-dot" type="button" data-slide-to="1" aria-label="Diapositive 2"></button>
            <button class="hero-dot" type="button" data-slide-to="2" aria-label="Diapositive 3"></button>
        </div>
    </div>

    <aside class="side-cards reveal">
        @auth
            <a href="{{ route('support.index') }}" class="side-card">
        @else
            <a href="{{ route('login') }}" class="side-card">
        @endauth
            <span class="side-card-ico">{!! \App\Support\IconLibrary::svg('search', '', 17) !!}</span>
            <span class="side-card-body">
                <span class="side-card-title">Centre d'aide</span>
                <span class="side-card-sub">Besoin d'assistance ?</span>
            </span>
        </a>
        <a href="{{ route('register') }}" class="side-card">
            <span class="side-card-ico">{!! \App\Support\IconLibrary::svg('store', '', 17) !!}</span>
            <span class="side-card-body">
                <span class="side-card-title">Vendez sur {{ config('app.name', 'Shopio') }}</span>
                <span class="side-card-sub">Ouvrez votre boutique</span>
            </span>
        </a>
        <a href="{{ route('shops.index') }}" class="side-card">
            <span class="side-card-ico">{!! \App\Support\IconLibrary::svg('bag', '', 17) !!}</span>
            <span class="side-card-body">
                <span class="side-card-title">Toutes les boutiques</span>
                <span class="side-card-sub">Parcourir le catalogue</span>
            </span>
        </a>
    </aside>
</div>

{{-- ══ BANDEAU DE CONFIANCE ══ --}}
<div class="trust-strip reveal">
    <div class="trust-item">
        <span class="trust-ico">{!! \App\Support\IconLibrary::svg('truck', '', 20) !!}</span>
        <span class="trust-txt"><strong>Livraison rapide</strong><span>Partout en Guinée</span></span>
    </div>
    <div class="trust-item">
        <span class="trust-ico">{!! \App\Support\IconLibrary::svg('wallet', '', 20) !!}</span>
        <span class="trust-txt"><strong>Paiement à la livraison</strong><span>Payez en toute sécurité</span></span>
    </div>
    <div class="trust-item">
        <span class="trust-ico">{!! \App\Support\IconLibrary::svg('rotate', '', 20) !!}</span>
        <span class="trust-txt"><strong>Retours faciles</strong><span>Assistance dédiée</span></span>
    </div>
    <div class="trust-item">
        <span class="trust-ico">{!! \App\Support\IconLibrary::svg('shield', '', 20) !!}</span>
        <span class="trust-txt"><strong>Boutiques vérifiées</strong><span>Vendeurs approuvés</span></span>
    </div>
</div>

<div class="c-main">

{{-- ══ VU RÉCEMMENT (rempli en JS depuis l'historique local du navigateur) ══ --}}
<div class="sec-hd reveal" id="recentlyViewedHd" hidden>
    <div class="sec-title">{!! \App\Support\IconLibrary::svg('clock') !!} Vu <strong>récemment</strong></div>
    <button type="button" class="sec-link" id="recentlyViewedClear" style="background:none;border:none;cursor:pointer">Effacer l'historique</button>
</div>
<div class="reco-row-outer" id="recentlyViewedRow" hidden>
    <div class="reco-row" id="recentlyViewedList"></div>
</div>

<div id="resultsRoot">
@include('partials.catalogue-results')
</div>

{{-- ══ TÉMOIGNAGES CLIENTS (avis réels, hors recherche) ══ --}}
@if($testimonials->isNotEmpty())
<div class="sec-hd reveal" style="margin-top:8px">
    <div class="sec-title">{!! \App\Support\IconLibrary::svg('sparkles') !!} Ce que disent <strong>nos clients</strong></div>
</div>
<div class="testi-grid">
    @foreach($testimonials as $review)
    <div class="testi-card reveal" style="--rd: {{ $loop->index * 70 }}ms">
        <div class="testi-stars">{!! \App\Support\IconLibrary::stars((float) $review->rating, 13) !!}</div>
        <p class="testi-text">&laquo;&nbsp;{{ Str::limit($review->comment, 160) }}&nbsp;&raquo;</p>
        <div class="testi-author">
            <span class="testi-avatar">{{ mb_strtoupper(mb_substr($review->client->name ?? '?', 0, 1)) }}</span>
            <span class="testi-author-body">
                <span class="testi-author-name">{{ $review->client->name ?? 'Client Shopio' }}</span>
                @if($review->vendeur)<span class="testi-author-sub">Achat chez {{ $review->vendeur->name }}</span>@endif
            </span>
        </div>
    </div>
    @endforeach
</div>
@endif

</div>{{-- /.c-main --}}

{{-- ══ FOOTER ══ --}}
<footer class="w-footer">
    <div class="w-footer-inner">
        <div class="w-footer-brand">
            <a href="{{ url('/') }}" class="w-footer-logo">
                <img src="/images/shopio-logo-192.png" alt="{{ config('app.name', 'Shopio') }}">
                {{ config('app.name', 'Shopio') }}
            </a>
            <p class="w-footer-desc">La marketplace tout-en-un : boutiques, produits et livraison en Guinée.</p>
        </div>
        <div class="w-footer-col">
            <h4>Plateforme</h4>
            <a href="{{ url('/') }}">Accueil</a>
            <a href="{{ route('shops.index') }}">Boutiques</a>
            <a href="{{ route('delivery.companies.index') }}">Entreprises livraison</a>
        </div>
        <div class="w-footer-col">
            <h4>Compte</h4>
            <a href="{{ route('login') }}">Connexion</a>
            <a href="{{ route('register') }}">Inscription</a>
            @auth<a href="{{ route('profile.edit') }}">Mon profil</a>@endauth
        </div>
        <div class="w-footer-col">
            <h4>Support</h4>
            @auth
                <a href="{{ route('support.index') }}">Centre d'aide</a>
                <a href="{{ route('support.create') }}">Ouvrir un ticket</a>
            @else
                <a href="{{ route('login') }}">Se connecter pour l'assistance</a>
            @endauth
            <a href="{{ route('legal.terms') }}">Conditions d'utilisation</a>
        </div>
    </div>
    <div class="w-footer-bottom">
        <span>&copy; {{ date('Y') }} {{ config('app.name', 'Shopio') }} — Tous droits réservés</span>
        <span>Fait avec {!! \App\Support\IconLibrary::svg('heart', '', 12) !!} en Guinée</span>
    </div>
</footer>

{{-- ══ RETOUR EN HAUT ══ --}}
<button type="button" id="backToTop" class="back-to-top" aria-label="Retour en haut de page">
    {!! \App\Support\IconLibrary::svg('chevron-up', '', 20) !!}
</button>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    /* ── Compte à rebours des ventes flash (ré-exécutable après une recherche en direct) ── */
    let flashTimer = null;
    function initFlashCountdown() {
        if (flashTimer) { clearInterval(flashTimer); flashTimer = null; }
        const flashEl = document.getElementById('flashCountdown');
        if (!flashEl) return;
        let seconds = parseInt(flashEl.dataset.seconds, 10) || 0;
        const valEl = document.getElementById('flashCountdownVal');
        const tick = () => {
            if (seconds <= 0) { valEl.textContent = '00:00:00'; return; }
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            valEl.textContent = [h, m, s].map(n => String(n).padStart(2, '0')).join(':');
            seconds--;
        };
        tick();
        flashTimer = setInterval(tick, 1000);
    }
    initFlashCountdown();

    /* ── Compteur animé "X produits disponibles" (0 → valeur réelle) ── */
    const countEl = document.getElementById('heroProductCount');
    if (countEl) {
        const target = parseInt(countEl.dataset.count, 10) || 0;
        if (target > 0) {
            const duration = 1300, step = 16;
            const increment = target / (duration / step);
            let current = 0;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    countEl.textContent = target.toLocaleString('fr-FR');
                    clearInterval(timer);
                } else {
                    countEl.textContent = Math.floor(current).toLocaleString('fr-FR');
                }
            }, step);
        }
    }

    const hamburger = document.getElementById('navHamburger');
    const mobileMenu = document.getElementById('navMobileMenu');
    if (hamburger && mobileMenu) {
        hamburger.addEventListener('click', () => {
            const open = mobileMenu.classList.toggle('open');
            hamburger.classList.toggle('open', open);
            hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', (e) => {
            if (!mobileMenu.classList.contains('open')) return;
            if (mobileMenu.contains(e.target) || hamburger.contains(e.target)) return;
            mobileMenu.classList.remove('open');
            hamburger.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
        });
    }

    /* ── Reveal au scroll : titres de section, sidebar, bandeau flash,
     *    et chaque carte produit apparaît en fondu/glissement, en cascade.
     *    Ré-exécutable sur une portion précise du DOM (après une recherche
     *    en direct, pour animer seulement les nouveaux résultats). ── */
    let revealObserver = null;
    function initReveal(root) {
        root = root || document;
        if (revealObserver) revealObserver.disconnect();
        root.querySelectorAll('.flash-row, .reco-row, .prod-grid').forEach(container => {
            Array.from(container.children).forEach((el, i) => {
                el.classList.add('reveal');
                el.style.setProperty('--rd', Math.min(i, 10) * 55 + 'ms');
            });
        });
        if ('IntersectionObserver' in window) {
            revealObserver = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
            root.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
        } else {
            root.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
        }
    }
    initReveal();

    /* ── Carrousel du hero : diapositives qui défilent automatiquement,
     *    points cliquables, pause au survol/tactile et hors écran. ── */
    (function initHeroCarousel() {
        const root = document.getElementById('heroCarousel');
        if (!root) return;
        const slides = Array.from(root.querySelectorAll('.hero-slide'));
        const dots = Array.from(root.querySelectorAll('.hero-dot'));
        if (slides.length < 2) return;

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let current = slides.findIndex(s => s.classList.contains('is-active'));
        if (current < 0) current = 0;
        let timer = null;

        function goTo(index) {
            slides[current].classList.remove('is-active');
            dots[current] && dots[current].classList.remove('is-active');
            current = (index + slides.length) % slides.length;
            slides[current].classList.add('is-active');
            dots[current] && dots[current].classList.add('is-active');
        }
        function next() { goTo(current + 1); }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }
        function start() {
            stop();
            if (reduceMotion) return; // pas de défilement auto pour les visiteurs qui limitent les animations
            timer = setInterval(next, 5500);
        }

        dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i); start(); }));
        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);
        root.addEventListener('touchstart', stop, { passive: true });
        document.addEventListener('visibilitychange', () => { document.hidden ? stop() : start(); });

        start();
    })();

    /* ── Favoris (♥) : bascule en AJAX sur chaque carte produit, sans recharger la page. ── */
    (function initFavorites() {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) return;
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-fav-toggle]');
            if (!btn || btn.classList.contains('is-busy')) return;
            e.preventDefault();
            btn.classList.add('is-busy');
            fetch(btn.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfMeta.content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => {
                    btn.classList.toggle('is-fav', !!data.favorited);
                    btn.setAttribute('aria-pressed', data.favorited ? 'true' : 'false');
                })
                .catch(() => {})
                .finally(() => btn.classList.remove('is-busy'));
        });
    })();

    /* ── "Vu récemment" : mémorisé côté navigateur (localStorage), rien à envoyer
     *    au serveur. Chaque clic sur une carte produit enregistre l'article ;
     *    au chargement, on affiche les derniers produits consultés. ── */
    (function initRecentlyViewed() {
        // v2 : les cartes produit pointent maintenant vers le formulaire de commande
        // (avant, vers la fiche produit) — la clé change pour purger automatiquement
        // les anciennes URLs mémorisées avant ce changement, chez tous les visiteurs.
        const STORAGE_KEY = 'shopio_recently_viewed_v2';
        localStorage.removeItem('shopio_recently_viewed');
        const MAX_ITEMS = 10;

        function readList() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; } catch (e) { return []; }
        }
        function writeList(list) {
            try { localStorage.setItem(STORAGE_KEY, JSON.stringify(list.slice(0, MAX_ITEMS))); } catch (e) {}
        }

        // Enregistre un produit dès qu'on clique sur sa carte (avant la navigation).
        document.addEventListener('click', (e) => {
            const card = e.target.closest('.prod-card, .flash-card, .reco-card');
            if (!card) return;
            const link = card.matches('a') ? card : card.querySelector('a.prod-card-link');
            const nameEl = card.querySelector('.prod-card-name, .flash-card-name, .reco-card-name');
            const priceEl = card.querySelector('.prod-card-price, .flash-card-price, .reco-card-price');
            const imgEl = card.querySelector('img');
            if (!link || !nameEl) return;

            const item = {
                url: link.href,
                name: nameEl.textContent.trim(),
                price: priceEl ? priceEl.textContent.trim() : '',
                img: imgEl ? imgEl.src : '',
            };
            let list = readList().filter(p => p.url !== item.url);
            list.unshift(item);
            writeList(list);
        });

        // Affiche la rangée si on a déjà un historique.
        const hd = document.getElementById('recentlyViewedHd');
        const rowWrap = document.getElementById('recentlyViewedRow');
        const list = document.getElementById('recentlyViewedList');
        const clearBtn = document.getElementById('recentlyViewedClear');
        if (!hd || !rowWrap || !list) return;

        function render() {
            const items = readList();
            if (!items.length) { hd.hidden = true; rowWrap.hidden = true; return; }
            list.innerHTML = items.map(item => `
                <a href="${item.url}" class="reco-card">
                    <div class="reco-card-img">${item.img ? `<img src="${item.img}" alt="${item.name}" loading="lazy" width="190" height="130">` : ''}</div>
                    <div class="reco-card-body">
                        <div class="reco-card-name">${item.name}</div>
                        <div class="reco-card-price-row"><span class="reco-card-price">${item.price}</span></div>
                    </div>
                </a>
            `).join('');
            hd.hidden = false;
            rowWrap.hidden = false;
        }
        render();

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                localStorage.removeItem(STORAGE_KEY);
                render();
            });
        }
    })();

    /* ── Bouton "retour en haut" ── */
    (function initBackToTop() {
        const btn = document.getElementById('backToTop');
        if (!btn) return;
        window.addEventListener('scroll', () => {
            btn.classList.toggle('is-visible', window.scrollY > 500);
        }, { passive: true });
        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();

    /* ── Recherche en direct : dès que le champ change (y compris quand on
     *    l'efface complètement), la liste de produits se met à jour toute
     *    seule, sans recharger la page — comme sur Jumia. ── */
    const resultsRoot = document.getElementById('resultsRoot');
    const searchInputs = Array.from(document.querySelectorAll('.nav-search input[name="s"]'));

    if (resultsRoot && searchInputs.length) {
        let searchDebounce = null;
        let searchAbortCtrl = null;

        const buildSearchUrl = (term) => {
            const url = new URL(window.location.origin + '/');
            const currentCat = new URLSearchParams(window.location.search).get('cat');
            if (currentCat) url.searchParams.set('cat', currentCat);
            if (term) url.searchParams.set('s', term);
            return url;
        };

        const skeletonHtml = (n) => {
            let cards = '';
            for (let i = 0; i < n; i++) {
                cards += '<div class="skeleton-card"><div class="skeleton-block img"></div>'
                    + '<div class="skeleton-body">'
                    + '<div class="skeleton-block line w-70"></div>'
                    + '<div class="skeleton-block line w-40"></div>'
                    + '<div class="skeleton-block line w-70"></div>'
                    + '</div></div>';
            }
            return '<div class="skeleton-grid">' + cards + '</div>';
        };

        const loadResults = async (url, { pushHistory = true, scrollToCatalogue = false } = {}) => {
            if (searchAbortCtrl) searchAbortCtrl.abort();
            searchAbortCtrl = new AbortController();
            resultsRoot.classList.add('is-loading');
            resultsRoot.innerHTML = skeletonHtml(8);
            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: searchAbortCtrl.signal,
                });
                if (!res.ok) return;
                const html = await res.text();
                resultsRoot.innerHTML = html;
                if (pushHistory) {
                    window.history.pushState({}, '', url.pathname + url.search);
                }
                initFlashCountdown();
                initReveal(resultsRoot);
                // Pagination : on atterrit directement sur les produits, pas besoin de redescendre.
                if (scrollToCatalogue) {
                    document.getElementById('catalogue')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } catch (err) {
                /* AbortError = une recherche plus récente a pris le relais, on ignore */
            } finally {
                resultsRoot.classList.remove('is-loading');
                // Sécurité : s'assure que la barre de progression globale (NProgress) et le
                // spinner de bouton ne restent jamais bloqués après une recherche en direct.
                if (typeof NProgress !== 'undefined') NProgress.done();
                document.querySelectorAll('.btn-loading').forEach(el => el.classList.remove('btn-loading'));
            }
        };

        searchInputs.forEach(input => {
            input.addEventListener('input', () => {
                const val = input.value;
                searchInputs.forEach(other => { if (other !== input) other.value = val; });
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(() => loadResults(buildSearchUrl(val.trim())), 380);
            });
        });

        // Soumission classique du formulaire (bouton loupe / touche Entrée) : instantané, sans
        // attendre le debounce, et on descend directement vers les résultats — sinon sur
        // l'accueil (au-dessus de la ligne de flottaison) on ne les voit pas sans scroller soi-même.
        document.querySelectorAll('.nav-search').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                clearTimeout(searchDebounce);
                const input = form.querySelector('input[name="s"]');
                loadResults(buildSearchUrl((input?.value || '').trim()), { scrollToCatalogue: true });
            });
        });

        // Liens "Effacer" / "Réinitialiser" à l'intérieur des résultats : idem, sans rechargement
        document.addEventListener('click', (e) => {
            const clearLink = e.target.closest('[data-live-clear]');
            if (!clearLink) return;
            e.preventDefault();
            clearTimeout(searchDebounce);
            searchInputs.forEach(input => { input.value = ''; });
            loadResults(new URL(clearLink.href));
        });

        // Pagination (page suivante/précédente) : chargée en direct puis on atterrit
        // directement sur les produits, au lieu de tout en haut de la page.
        document.addEventListener('click', (e) => {
            const pageLink = e.target.closest('#resultsRoot .c-pagination a');
            if (!pageLink) return;
            e.preventDefault();
            clearTimeout(searchDebounce);
            loadResults(new URL(pageLink.href), { scrollToCatalogue: true });
        });

        // Bouton précédent/suivant du navigateur : recharge les résultats en direct aussi
        window.addEventListener('popstate', () => {
            const params = new URLSearchParams(window.location.search);
            searchInputs.forEach(input => { input.value = params.get('s') || ''; });
            loadResults(new URL(window.location.href), { pushHistory: false });
        });
    }
});
</script>
@endpush
