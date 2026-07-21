{{--
=====================================================
WELCOME2.BLADE.PHP — Page d'accueil "catalogue" (mêmes couleurs que le dashboard client)
=====================================================
Affiche directement les produits au lieu de la page marketing.
Variables injectées depuis WelcomeController@catalogue :
  $flashProducts        → Collection<Product>  (ventes flash actives)
  $recommendedProducts  → Collection<Product>  (produits vedette)
  $shops                → Collection<Shop>     (boutiques à la une)
  $products             → LengthAwarePaginator<Product> (catalogue complet)
  $categories           → Collection<string>
=====================================================
--}}

@extends('layouts.app')

@section('title', 'Accueil — Produits')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --orange:     #f06a0f;
    --orange-dk:  #d45a08;
    --orange-lt:  #fff3ec;
    --navy:       #2c3e50;
    --navy-2:     #34495e;
    --grey:       #f4f6f8;
    --grey-2:     #e8ecf0;
    --border:     #dde3ea;
    --text:       #2c3e50;
    --text-2:     #5a6a7a;
    --muted:      #8a9bb0;
    --surface:    #ffffff;
    --font:       'Open Sans', sans-serif;
    --display:    'Nunito', sans-serif;
    --r:          10px;
    --r-sm:       7px;
    --shadow-sm:  0 1px 4px rgba(0,0,0,.07);
    --shadow:     0 4px 16px rgba(0,0,0,.1);
    --shadow-lg:  0 8px 32px rgba(0,0,0,.13);
    --nav-h:      60px;
}

html { font-family: var(--font); scroll-behavior: smooth; overflow-x: hidden; }
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; overflow-x: hidden; }

/* ══ NAVBAR ══ */
.nav {
    height: var(--nav-h);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 32px; gap: 20px;
    position: sticky; top: 0; z-index: 100;
    box-shadow: var(--shadow-sm);
}
.nav-logo {
    font-family: var(--display); font-weight: 900; font-size: 22px;
    text-decoration: none; flex-shrink: 0;
    display: flex; align-items: center; gap: 8px;
}
.nav-logo img { height: 38px; width: auto; object-fit: contain; border-radius: 8px; }
.nav-logo span:first-child { color: var(--navy); }
.nav-logo span:last-child  { color: var(--orange); }
.nav-links { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
.nav-link {
    padding: 8px 14px; border-radius: var(--r-sm);
    font-size: 13.5px; font-weight: 600; color: var(--text-2);
    text-decoration: none; transition: all .15s;
    display: flex; align-items: center; gap: 6px; white-space: nowrap;
}
.nav-link:hover { background: var(--grey); color: var(--text); }
.nav-link.active { color: var(--orange); }
.nav-search {
    flex: 1; max-width: 420px;
    display: flex; align-items: center;
    border: 1.5px solid var(--border);
    border-radius: 50px; overflow: hidden;
    background: var(--grey);
    transition: border-color .2s, box-shadow .2s;
}
.nav-search:focus-within { border-color: var(--orange); box-shadow: 0 0 0 3px rgba(240,106,15,.1); background: var(--surface); }
.nav-search input {
    flex: 1; border: none; outline: none; background: transparent;
    padding: 9px 16px; font-size: 13px; font-family: var(--font); color: var(--text);
}
.nav-search input::placeholder { color: var(--muted); }
.nav-search-btn { padding: 9px 16px; background: var(--orange); border: none; cursor: pointer; color: #fff; font-size: 14px; transition: background .15s; }
.nav-search-btn:hover { background: var(--orange-dk); }
.nav-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; margin-left: auto; }
.nav-orders-btn {
    display: flex; align-items: center; gap: 7px;
    padding: 8px 16px; border-radius: 50px;
    font-size: 12.5px; font-weight: 700; font-family: var(--font);
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--text); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap;
}
.nav-orders-btn:hover { border-color: var(--orange); color: var(--orange); background: var(--orange-lt); }
.nav-btn-primary {
    padding: 9px 18px; border-radius: 50px; font-size: 12.5px; font-weight: 700; font-family: var(--font);
    background: var(--orange); color: #fff; border: none; cursor: pointer; text-decoration: none;
    transition: all .15s; white-space: nowrap;
}
.nav-btn-primary:hover { background: var(--orange-dk); color: #fff; }

/* ══ BARRE MOBILE ══ */
.mobile-bar { display: none; padding: 10px 14px; gap: 8px; background: var(--surface); border-bottom: 1px solid var(--border); position: sticky; top: var(--nav-h); z-index: 90; }
.mobile-bar .nav-search { max-width: 100%; }

/* ══ BANDEAU RETOUR ACCUEIL CLASSIQUE ══ */
.switch-banner { padding: 10px 32px 0; }
.switch-inner {
    max-width: 1300px; margin: 0 auto;
    background: var(--orange-lt); border: 1px solid #ffdcc4; border-radius: var(--r-sm);
    padding: 10px 16px; font-size: 12.5px; color: var(--orange-dk); display: flex; align-items: center; gap: 8px;
}
.switch-inner a { color: var(--orange-dk); font-weight: 700; text-decoration: underline; }

/* ══ HERO ══ */
.hero {
    background: linear-gradient(135deg, var(--navy) 0%, #3d5a73 100%);
    border-radius: var(--r);
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    overflow: hidden; position: relative;
    margin: 20px 32px; padding: 32px 36px;
}
.hero::before { content: ''; position: absolute; right: -60px; top: -60px; width: 280px; height: 280px; border-radius: 50%; background: rgba(255,255,255,.04); pointer-events: none; }
.hero::after { content: ''; position: absolute; right: 100px; bottom: -80px; width: 200px; height: 200px; border-radius: 50%; background: rgba(240,106,15,.12); pointer-events: none; }
.hero-text { flex: 1; position: relative; z-index: 1; }
.hero-title { font-family: var(--display); font-weight: 900; font-size: clamp(22px, 3.5vw, 34px); color: #fff; line-height: 1.15; margin-bottom: 10px; letter-spacing: -.5px; }
.hero-sub { font-size: 14px; color: rgba(255,255,255,.7); margin-bottom: 18px; max-width: 460px; }
.hero-btns { display: flex; gap: 12px; flex-wrap: wrap; }
.hero-btn-primary {
    padding: 12px 24px; border-radius: 50px; font-size: 13.5px; font-weight: 700; font-family: var(--font);
    background: var(--orange); color: #fff; border: none; cursor: pointer; text-decoration: none;
    transition: all .15s; display: inline-flex; align-items: center; gap: 6px;
}
.hero-btn-primary:hover { background: var(--orange-dk); transform: scale(1.03); color: #fff; }
.hero-btn-secondary {
    padding: 12px 24px; border-radius: 50px; font-size: 13.5px; font-weight: 700; font-family: var(--font);
    background: rgba(255,255,255,.12); color: #fff; border: 1.5px solid rgba(255,255,255,.25);
    cursor: pointer; text-decoration: none; transition: all .15s; display: inline-flex; align-items: center; gap: 6px;
    backdrop-filter: blur(8px);
}
.hero-btn-secondary:hover { background: rgba(255,255,255,.2); color: #fff; }
.hero-right { display: flex; align-items: center; gap: 16px; flex-shrink: 0; position: relative; z-index: 1; }
.hero-icon-box {
    width: 76px; height: 76px; border-radius: 20px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
    display: flex; align-items: center; justify-content: center; font-size: 30px; flex-shrink: 0;
    animation: floatBox 3.5s ease-in-out infinite;
}
.hero-icon-box:nth-child(2) { animation-delay: -1.5s; width: 64px; height: 64px; font-size: 26px; }
.hero-icon-box:nth-child(3) { animation-delay: -3s; width: 70px; height: 70px; font-size: 28px; }
@keyframes floatBox { 0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)} }

/* ══ MAIN CONTENT ══ */
.c-main { padding: 0 32px 60px; }
.sec-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 12px; }
.sec-title { font-family: var(--display); font-size: 19px; font-weight: 800; color: var(--text); letter-spacing: -.3px; display: flex; align-items: center; gap: 8px; }
.sec-title strong { color: var(--orange); }
.sec-link { font-size: 12.5px; font-weight: 700; color: var(--orange); text-decoration: none; white-space: nowrap; }
.sec-link:hover { text-decoration: underline; }

/* ══ FILTRES CATÉGORIES ══ */
.cats { display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 6px; scrollbar-width: none; -ms-overflow-style: none; }
.cats::-webkit-scrollbar { display: none; }
.cat-pill {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 50px; flex-shrink: 0;
    font-size: 13px; font-weight: 600; font-family: var(--font); border: 1.5px solid var(--border); background: var(--surface);
    color: var(--text-2); cursor: pointer; white-space: nowrap; text-decoration: none; transition: all .18s;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.cat-pill:hover { border-color: var(--orange); color: var(--orange); background: var(--orange-lt); transform: translateY(-1px); box-shadow: 0 3px 10px rgba(240,106,15,.18); }
.cat-pill.active { background: linear-gradient(135deg, var(--orange), var(--orange-dk)); color: #fff; border-color: var(--orange-dk); box-shadow: 0 4px 14px rgba(240,106,15,.35); transform: translateY(-1px); }

/* ══ GRILLE BOUTIQUES ══ */
.shops-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 18px; margin-bottom: 28px; }
.shop-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm); transition: box-shadow .2s, transform .2s, border-color .2s;
    text-decoration: none; color: inherit; display: flex; flex-direction: column;
}
.shop-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: var(--orange); }
.shop-card-img { height: 140px; overflow: hidden; position: relative; flex-shrink: 0; background: linear-gradient(135deg, #f3f4f6, #e5e7eb); display: flex; align-items: center; justify-content: center; }
.shop-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
.shop-card:hover .shop-card-img img { transform: scale(1.07); }
.shop-card-ph { font-size: 40px; opacity: .4; }
.shop-card-badge {
    position: absolute; top: 10px; left: 10px; display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-weight: 700; padding: 4px 9px; border-radius: 20px; backdrop-filter: blur(6px);
    background: rgba(16,185,129,.9); color: #fff;
}
.shop-card-badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #fff; animation: pulse 1.8s ease-in-out infinite; display: inline-block; }
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.3} }
.shop-card-body { padding: 14px 16px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
.shop-card-name { font-family: var(--display); font-size: 14.5px; font-weight: 800; color: var(--text); line-height: 1.3; }
.shop-card-count { font-size: 12px; color: var(--muted); }
.shop-card-footer { padding: 12px 16px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: flex-end; }
.shop-card-cta {
    display: inline-flex; align-items: center; gap: 5px; padding: 8px 16px; border-radius: 50px;
    font-size: 12.5px; font-weight: 700; font-family: var(--font); background: var(--orange); color: #fff;
    border: none; cursor: pointer; text-decoration: none; transition: all .15s;
}
.shop-card-cta:hover { background: var(--orange-dk); transform: scale(1.04); }

/* ══ RECOMMANDÉ POUR VOUS ══ */
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
.reco-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: var(--orange); }
.reco-card-img { height: 130px; position: relative; overflow: hidden; flex-shrink: 0; background: var(--grey); display: flex; align-items: center; justify-content: center; }
.reco-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
.reco-card:hover .reco-card-img img { transform: scale(1.07); }
.reco-card-ph { font-size: 32px; opacity: .3; }
.reco-card-badge { position: absolute; top: 8px; left: 8px; background: var(--orange); color: #fff; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 20px; }
.reco-card-body { padding: 11px 13px; display: flex; flex-direction: column; gap: 4px; flex: 1; }
.reco-card-shop { font-size: 10.5px; color: var(--muted); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.reco-card-name { font-size: 12.5px; font-weight: 700; color: var(--text); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; }
.reco-card-price-row { display: flex; align-items: baseline; gap: 6px; margin-top: auto; flex-wrap: wrap; }
.reco-card-price { font-size: 14px; font-weight: 800; color: var(--orange); font-family: monospace; }
.reco-card-orig { font-size: 10.5px; color: var(--muted); text-decoration: line-through; font-family: monospace; }
@media (max-width: 480px) { .reco-card { flex-basis: 152px; width: 152px; } .reco-card-img { height: 105px; } }

/* ══ VENTES FLASH ══ */
.flash-section {
    background: linear-gradient(135deg, #7c2d12 0%, #dc2626 55%, #f97316 100%);
    border-radius: var(--r); padding: 20px 20px 22px; position: relative; overflow: hidden; margin-bottom: 28px;
    box-shadow: 0 8px 28px rgba(220,38,38,.25);
}
.flash-section::before { content: ''; position: absolute; right: -40px; top: -40px; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,.08); pointer-events: none; }
.flash-section-hd { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; position: relative; z-index: 1; flex-wrap: wrap; }
.flash-section-title { font-family: var(--display); font-size: 18px; font-weight: 900; color: #fff; display: flex; align-items: center; gap: 8px; }
.flash-section-title .bolt { display: inline-block; animation: boltPulse 1.4s ease-in-out infinite; }
@keyframes boltPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.25); } }
.flash-section-sub { font-size: 12px; color: rgba(255,255,255,.85); font-weight: 600; }
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
.flash-card-badge { position: absolute; top: 7px; left: 7px; background: #111; color: #fde047; font-size: 10.5px; font-weight: 900; padding: 3px 8px; border-radius: 20px; }
.flash-card-body { padding: 9px 11px 11px; display: flex; flex-direction: column; gap: 3px; }
.flash-card-shop { font-size: 10px; color: var(--muted); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.flash-card-name { font-size: 12px; font-weight: 700; color: var(--text); line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.4em; }
.flash-card-price-row { display: flex; align-items: baseline; gap: 5px; flex-wrap: wrap; }
.flash-card-price { font-size: 14px; font-weight: 900; color: #dc2626; font-family: monospace; }
.flash-card-orig { font-size: 10px; color: var(--muted); text-decoration: line-through; font-family: monospace; }
@media (max-width: 480px) { .flash-card { flex-basis: 138px; width: 138px; } .flash-card-img { height: 92px; } }

/* ══ GRILLE PRODUITS (catalogue complet) ══ */
.prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 16px; }
.prod-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm); transition: box-shadow .2s, transform .2s, border-color .2s;
    text-decoration: none; color: inherit; display: flex; flex-direction: column;
}
.prod-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: var(--orange); }
.prod-card-img { height: 150px; position: relative; overflow: hidden; flex-shrink: 0; background: var(--grey); display: flex; align-items: center; justify-content: center; }
.prod-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
.prod-card:hover .prod-card-img img { transform: scale(1.06); }
.prod-card-ph { font-size: 36px; opacity: .3; }
.prod-card-badge { position: absolute; top: 8px; left: 8px; font-size: 10px; font-weight: 800; color: #fff; padding: 3px 9px; border-radius: 20px; }
.prod-card-body { padding: 11px 13px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
.prod-card-name { font-size: 13px; font-weight: 700; color: var(--text); line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.prod-card-shop { font-size: 11px; color: var(--muted); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.prod-card-cat { font-size: 10.5px; color: var(--orange); font-weight: 700; }
.prod-card-footer { padding: 10px 13px 13px; display: flex; align-items: center; justify-content: space-between; gap: 8px; border-top: 1px solid var(--grey-2); margin-top: auto; }
.prod-card-price { font-size: 14.5px; font-weight: 800; color: var(--orange); font-family: monospace; }
.prod-card-orig { font-size: 10.5px; color: var(--muted); text-decoration: line-through; margin-left: 3px; font-family: monospace; }
.prod-card-cta {
    font-size: 11.5px; font-weight: 700; color: #fff; background: var(--orange); border: none; border-radius: 50px;
    padding: 7px 14px; cursor: pointer; text-decoration: none; white-space: nowrap; transition: background .15s;
}
.prod-card-cta:hover { background: var(--orange-dk); color: #fff; }
.prod-card-cta.out { background: var(--grey-2); color: var(--muted); cursor: not-allowed; }

.c-empty { grid-column: 1/-1; padding: 72px 20px; text-align: center; background: var(--surface); border-radius: var(--r); border: 1px dashed var(--border); }
.c-empty-ico { font-size: 52px; display: block; opacity: .3; margin-bottom: 14px; }
.c-empty-title { font-family: var(--display); font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 6px; }
.c-empty-sub { font-size: 13.5px; color: var(--muted); }
.c-pagination { display: flex; justify-content: center; padding: 20px 0 8px; }
.count-line { font-size: 13px; color: var(--muted); margin-bottom: 14px; }

/* ══ FOOTER ══ */
.w2-footer { background: var(--navy); padding: 40px 32px 24px; color: rgba(255,255,255,.5); margin-top: 20px; }
.w2-footer-inner { max-width: 1300px; margin: 0 auto; display: flex; justify-content: space-between; align-items: flex-start; gap: 28px; flex-wrap: wrap; margin-bottom: 26px; }
.w2-footer-brand { max-width: 260px; }
.w2-footer-logo { display: flex; align-items: center; gap: 9px; font-family: var(--display); font-size: 16px; font-weight: 900; color: #fff; text-decoration: none; margin-bottom: 8px; }
.w2-footer-desc { font-size: 12.5px; line-height: 1.6; }
.w2-footer-col h4 { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,.7); margin-bottom: 12px; }
.w2-footer-col a { display: block; font-size: 12.5px; color: rgba(255,255,255,.5); text-decoration: none; margin-bottom: 7px; transition: color .15s; }
.w2-footer-col a:hover { color: var(--orange); }
.w2-footer-bottom { max-width: 1300px; margin: 0 auto; padding-top: 18px; border-top: 1px solid rgba(255,255,255,.1); display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; flex-wrap: wrap; gap: 8px; }

/* ══ RESPONSIVE ══ */
@media (max-width: 960px) {
    .nav-links { display: none; }
    .mobile-bar { display: flex; }
    .nav-search { display: none; }
    .shops-grid { grid-template-columns: repeat(2, 1fr); }
    .hero { margin: 14px 16px; padding: 24px 22px; }
    .hero-right { display: none; }
}
@media (max-width: 640px) {
    .nav { padding: 0 14px; gap: 10px; height: 56px; }
    :root { --nav-h: 56px; }
    .nav-logo img { height: 30px; }
    .nav-logo { font-size: 17px; }
    .nav-orders-btn span { display: none; }
    .switch-banner { padding: 10px 14px 0; }
    .c-main { padding: 0 14px 48px; }
    .sec-title { font-size: 16px; }
    .hero-title { font-size: 20px; }
    .hero-sub { font-size: 12.5px; }
    .shops-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .cats { margin-bottom: 14px; }
    .prod-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .prod-card-img { height: 120px; }
    .prod-card-footer { flex-direction: column; align-items: flex-start; gap: 8px; }
    .prod-card-cta { width: 100%; text-align: center; }
    .w2-footer { padding: 32px 18px 20px; }
    .w2-footer-bottom { flex-direction: column; text-align: center; }
}
@media (max-width: 380px) {
    .shops-grid { grid-template-columns: 1fr; }
    .prod-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
}
</style>
@endpush

@section('content')
@php
    $roleMap = ['superadmin'=>'admin.dashboard','admin'=>'boutique.dashboard','vendeur'=>'vendeur.dashboard','client'=>'client.dashboard','company'=>'company.dashboard','livreur'=>'livreur.dashboard'];
@endphp

{{-- ══ NAVBAR ══ --}}
<nav class="nav">
    <a href="{{ route('welcome.produits') }}" class="nav-logo">
        <img src="/images/shopio-logo-192.png" alt="{{ config('app.name', 'Shopio') }}">
        <span>Shop</span><span>io</span>
    </a>
    <div class="nav-links">
        <a href="{{ route('welcome.produits') }}" class="nav-link active">🏠 Accueil</a>
        <a href="{{ route('shops.index') }}" class="nav-link">🏪 Boutiques</a>
        <a href="{{ route('welcome') }}" class="nav-link">🎨 Accueil classique</a>
    </div>
    <form method="GET" action="{{ route('welcome.produits') }}" class="nav-search">
        <input type="text" name="s" value="{{ request('s') }}" placeholder="Que recherchez-vous ?" autocomplete="off">
        <button class="nav-search-btn" type="submit">🔍</button>
    </form>
    <div class="nav-actions">
        @guest
            <a href="{{ route('login') }}" class="nav-orders-btn"><span>Connexion</span></a>
            <a href="{{ route('register') }}" class="nav-btn-primary">S'inscrire</a>
        @else
            @if(Auth::user()->role === 'client')
                <a href="{{ route('client.orders.index') }}" class="nav-orders-btn">📦 <span>Mes commandes</span></a>
            @endif
            @if(isset($roleMap[Auth::user()->role]))
                <a href="{{ route($roleMap[Auth::user()->role]) }}" class="nav-btn-primary">Mon dashboard →</a>
            @endif
        @endguest
    </div>
</nav>

{{-- ══ BARRE MOBILE ══ --}}
<div class="mobile-bar">
    <form method="GET" action="{{ route('welcome.produits') }}" class="nav-search">
        <input type="text" name="s" value="{{ request('s') }}" placeholder="Rechercher un produit…" autocomplete="off">
        <button class="nav-search-btn" type="submit">🔍</button>
    </form>
</div>

{{-- ══ RETOUR ACCUEIL CLASSIQUE ══ --}}
<div class="switch-banner">
    <div class="switch-inner">
        🧪 Nouvelle page d'accueil (test) — <a href="{{ route('welcome') }}">revenir à l'ancienne page</a>
    </div>
</div>

{{-- ══ HERO ══ --}}
<section class="hero">
    <div class="hero-text">
        <div class="hero-title">Bienvenue sur {{ config('app.name', 'Shopio') }} 👋</div>
        <p class="hero-sub">Découvrez les meilleures boutiques et commandez vos produits préférés, livrés directement chez vous.</p>
        <div class="hero-btns">
            <a href="#catalogue" class="hero-btn-primary">🛒 Voir les produits</a>
            <a href="{{ route('shops.index') }}" class="hero-btn-secondary">🏪 Parcourir les boutiques</a>
        </div>
    </div>
    <div class="hero-right">
        <div class="hero-icon-box">📦</div>
        <div class="hero-icon-box">🛍️</div>
        <div class="hero-icon-box">🚚</div>
    </div>
</section>

<div class="c-main">

{{-- ══ VENTES FLASH ══ --}}
@if($flashProducts->isNotEmpty())
<div class="flash-section">
    <div class="flash-section-hd">
        <div class="flash-section-title"><span class="bolt">⚡</span> Ventes flash</div>
        <span class="flash-section-sub">Offres à durée limitée</span>
    </div>
    <div class="flash-row-outer">
        <div class="flash-row">
            @foreach($flashProducts as $product)
            <a href="{{ route('client.products.show', $product) }}" class="flash-card">
                <div class="flash-card-img">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy" width="168" height="112">
                    @else
                        <div class="flash-card-ph">🏷️</div>
                    @endif
                    <span class="flash-card-badge">-{{ $product->flash_discount_percent }}%</span>
                </div>
                <div class="flash-card-body">
                    @if($product->shop)<div class="flash-card-shop">{{ Str::limit($product->shop->name, 20) }}</div>@endif
                    <div class="flash-card-name">{{ $product->name }}</div>
                    <div class="flash-card-price-row">
                        <span class="flash-card-price">{{ number_format($product->current_price, 0, ',', ' ') }} GNF</span>
                        <span class="flash-card-orig">{{ number_format($product->price, 0, ',', ' ') }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ══ RECOMMANDÉS ══ --}}
@if($recommendedProducts->isNotEmpty())
<div class="sec-hd">
    <div class="sec-title">⭐ Recommandés <strong>pour vous</strong></div>
    <span style="font-size:12px;color:var(--muted)">Sélectionnés par nos boutiques</span>
</div>
<div class="reco-row-outer">
    <div class="reco-row">
        @foreach($recommendedProducts as $product)
        @php $hasPromo = $product->original_price && $product->original_price > $product->price; @endphp
        <a href="{{ route('client.products.show', $product) }}" class="reco-card">
            <div class="reco-card-img">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy" width="190" height="130">
                @else
                    <div class="reco-card-ph">🏷️</div>
                @endif
                <span class="reco-card-badge">⭐ Vedette</span>
            </div>
            <div class="reco-card-body">
                @if($product->shop)<div class="reco-card-shop">{{ Str::limit($product->shop->name, 22) }}</div>@endif
                <div class="reco-card-name">{{ $product->name }}</div>
                <div class="reco-card-price-row">
                    <span class="reco-card-price">{{ number_format($product->current_price, 0, ',', ' ') }} GNF</span>
                    @if($hasPromo)<span class="reco-card-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>@endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- ══ BOUTIQUES À LA UNE ══ --}}
@if($shops->isNotEmpty())
<div class="sec-hd">
    <div class="sec-title">🏪 Boutiques <strong>à la une</strong></div>
    <a href="{{ route('shops.index') }}" class="sec-link">Voir toutes les boutiques →</a>
</div>
<div class="shops-grid">
    @foreach($shops as $shop)
    <a href="{{ route('public.shops.products', $shop) }}" class="shop-card">
        <div class="shop-card-img">
            @if($shop->image)
                <img src="{{ asset('storage/' . $shop->image) }}" alt="{{ $shop->name }}" width="220" height="140">
            @else
                <div class="shop-card-ph">🛍️</div>
            @endif
            <span class="shop-card-badge">Ouvert</span>
        </div>
        <div class="shop-card-body">
            <div class="shop-card-name">{{ $shop->name }}</div>
            <div class="shop-card-count">{{ $shop->products_count }} produit{{ $shop->products_count > 1 ? 's' : '' }}</div>
        </div>
        <div class="shop-card-footer">
            <span class="shop-card-cta">Visiter →</span>
        </div>
    </a>
    @endforeach
</div>
@endif

{{-- ══ CATALOGUE COMPLET ══ --}}
<div class="sec-hd" id="catalogue" style="scroll-margin-top:calc(var(--nav-h) + 12px)">
    <div class="sec-title">🛒 Tous les <strong>produits</strong></div>
</div>
<div class="count-line">{{ number_format($products->total()) }} produit{{ $products->total() > 1 ? 's' : '' }} disponible{{ $products->total() > 1 ? 's' : '' }}</div>

@if($categories->isNotEmpty())
<div class="cats">
    <a href="{{ route('welcome.produits', array_filter(['s' => request('s')])) }}" class="cat-pill {{ !request('cat') ? 'active' : '' }}">Toutes</a>
    @foreach($categories as $cat)
    <a href="{{ route('welcome.produits', array_filter(['cat' => $cat, 's' => request('s')])) }}" class="cat-pill {{ request('cat') === $cat ? 'active' : '' }}">{{ $cat }}</a>
    @endforeach
</div>
@endif

@if(request('s') || request('cat'))
<div class="count-line">
    Résultats pour
    @if(request('s'))"<strong>{{ request('s') }}</strong>"@endif
    @if(request('cat')) dans <strong>{{ request('cat') }}</strong>@endif
    — <a href="{{ route('welcome.produits') }}" style="color:var(--orange);font-weight:600">réinitialiser</a>
</div>
@endif

@if($products->isEmpty())
<div class="prod-grid">
    <div class="c-empty">
        <span class="c-empty-ico">📦</span>
        <div class="c-empty-title">Aucun produit trouvé</div>
        <div class="c-empty-sub">Essayez une autre recherche ou catégorie.</div>
    </div>
</div>
@else
<div class="prod-grid">
    @foreach($products as $product)
    @php
        $hasPromo = $product->original_price && $product->original_price > $product->price;
        $stockOut = $product->stock !== null && $product->stock <= 0;
    @endphp
    <div class="prod-card">
        <a href="{{ route('client.products.show', $product) }}" style="text-decoration:none;color:inherit">
            <div class="prod-card-img">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy" decoding="async" width="190" height="150">
                @else
                    <div class="prod-card-ph">🛍</div>
                @endif
                @if($product->is_flash_active)
                    <span class="prod-card-badge" style="background:#dc2626">⚡ -{{ $product->flash_discount_percent }}%</span>
                @elseif($hasPromo)
                    <span class="prod-card-badge" style="background:#e53e3e">Promo</span>
                @elseif($product->is_featured)
                    <span class="prod-card-badge" style="background:var(--orange)">⭐ Vedette</span>
                @endif
            </div>
            <div class="prod-card-body">
                <div class="prod-card-name">{{ $product->name }}</div>
                @if($product->category)<div class="prod-card-cat">{{ $product->category }}</div>@endif
                @if($product->shop)<div class="prod-card-shop">🏪 {{ Str::limit($product->shop->name, 22) }}</div>@endif
            </div>
        </a>
        <div class="prod-card-footer">
            <div>
                <span class="prod-card-price">{{ number_format($product->current_price, 0, ',', ' ') }} GNF</span>
                @if($product->is_flash_active)
                    <span class="prod-card-orig">{{ number_format($product->price, 0, ',', ' ') }}</span>
                @elseif($hasPromo)
                    <span class="prod-card-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>
                @endif
            </div>
            @if(!$stockOut)
                <a href="{{ route('client.orders.createFromProduct', $product) }}" class="prod-card-cta">Commander</a>
            @else
                <span class="prod-card-cta out">Indisponible</span>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div class="c-pagination">{{ $products->links() }}</div>
@endif

</div>{{-- /.c-main --}}

{{-- ══ FOOTER ══ --}}
<footer class="w2-footer">
    <div class="w2-footer-inner">
        <div class="w2-footer-brand">
            <a href="{{ route('welcome.produits') }}" class="w2-footer-logo">
                <img src="/images/shopio-logo-192.png" alt="{{ config('app.name', 'Shopio') }}" style="width:32px;height:32px;object-fit:cover;border-radius:8px">
                {{ config('app.name', 'Shopio') }}
            </a>
            <p class="w2-footer-desc">La plateforme tout-en-un pour gérer votre boutique, vos livraisons et vos clients en Guinée.</p>
        </div>
        <div class="w2-footer-col">
            <h4>Plateforme</h4>
            <a href="{{ route('welcome') }}">Accueil classique</a>
            <a href="{{ route('shops.index') }}">Boutiques</a>
            <a href="{{ route('delivery.companies.index') }}">Entreprises livraison</a>
        </div>
        <div class="w2-footer-col">
            <h4>Compte</h4>
            <a href="{{ route('login') }}">Connexion</a>
            <a href="{{ route('register') }}">Inscription</a>
            @auth<a href="{{ route('profile.edit') }}">Mon profil</a>@endauth
        </div>
        <div class="w2-footer-col">
            <h4>Support</h4>
            <a href="{{ route('support.index') }}">Centre d'aide</a>
            <a href="{{ route('support.create') }}">Ouvrir un ticket</a>
        </div>
    </div>
    <div class="w2-footer-bottom">
        <span>&copy; {{ date('Y') }} {{ config('app.name', 'ShopManager') }} — Tous droits réservés</span>
        <span>Fait avec ❤️ en Guinée 🇬🇳</span>
    </div>
</footer>
@endsection
