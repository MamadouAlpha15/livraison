{{--
    resources/views/public/shops/products.blade.php
    Route     : GET /shops/{shop}/products → Public\ShopController@products
    Variables :
      $shop           → Shop
      $products       → LengthAwarePaginator<Product>
      $categories     → array
      $reviewAvg      → float|null   note moyenne réelle du vendeur (null si aucun avis)
      $reviewCount    → int          nombre d'avis réels
      $deliveredCount → int          nombre de commandes livrées par la boutique
      $devise         → string
--}}
@extends('layouts.app')

@php
    $shopDesc = trim(strip_tags($shop->description ?? '')) ?: ($shop->name . ' — découvrez tous nos produits et commandez directement en ligne sur Shopio.');
    $shopImg  = $shop->image ? (\App\Services\ImageOptimizer::url($shop->image, 'medium') ?? asset('storage/'.$shop->image)) : asset('images/shopio-logo-192.png');
@endphp

@section('title', $shop->name . ' — Produits')
@section('description', \Illuminate\Support\Str::limit($shopDesc, 160))
@php $bodyClass = 'is-dashboard'; @endphp

@push('meta')
{{-- Aperçu riche quand ce lien est collé sur WhatsApp / Facebook / Messenger : c'est
     précisément ce que le vendeur partage pour attirer des clients, donc la carte de
     prévisualisation doit montrer la boutique (photo + nom + description), pas le site. --}}
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $shop->name }} — Boutique en ligne">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit($shopDesc, 200) }}">
<meta property="og:image" content="{{ $shopImg }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="Shopio">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $shop->name }} — Boutique en ligne">
<meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit($shopDesc, 200) }}">
<meta name="twitter:image" content="{{ $shopImg }}">
@endpush

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --brand:     #1668D8;
    --brand-dk:  #1260C9;
    --brand-lt:  #D6E6FF;
    --brand-mlt: #EAF2FF;
    --violet:    #1F7CF6;
    --dark:      #0B1E3D;
    --red:       #ef4444;
    --red-lt:    #fef2f2;
    --green:     #10b981;
    --green-lt:  #ecfdf5;
    --amber:     #f59e0b;
    --grey:      #f8fafc;
    --grey-2:    #f1f5f9;
    --border:    #e2e8f0;
    --text:      #0f172a;
    --text-2:    #475569;
    --muted:     #94a3b8;
    --surface:   #ffffff;
    --font:      'Plus Jakarta Sans', sans-serif;
    --mono:      'JetBrains Mono', monospace;
    --r:         14px;
    --r-sm:      9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
    --shadow:    0 4px 20px rgba(0,0,0,.09);
    --nav-h:     64px;
}

html { font-family: var(--font); scroll-behavior: smooth; }
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

/* ══════════════════════════════
   TOPBAR
══════════════════════════════ */
.amz-nav {
    background: rgba(10,10,30,.97);
    backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
    height: var(--nav-h);
    display: flex; align-items: center;
    padding: 0 20px; gap: 12px;
    position: sticky; top: 0; z-index: 200;
    border-bottom: 1px solid rgba(22,104,216,.18);
}
.nav-logo-wrap {
    display: flex; align-items: center; gap: 9px;
    text-decoration: none; flex-shrink: 0;
}
.nav-logo-img {
    width: 36px; height: 36px; object-fit: cover; border-radius: 9px;
    border: 2px solid rgba(196,181,253,.5);
    box-shadow: 0 0 0 3px rgba(41,29,149,.3), 0 4px 12px rgba(170,40,217,.3);
}
.nav-brand-name {
    font-size: 16px; font-weight: 800; letter-spacing: -.3px;
    background: linear-gradient(90deg, #c4b5fd, #e879f9);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.amz-back-home {
    display: flex; align-items: center; gap: 6px;
    color: #c4b5fd; font-size: 13px; font-weight: 700;
    text-decoration: none; padding: 7px 14px;
    border: 1.5px solid rgba(196,181,253,.35); border-radius: var(--r-sm);
    background: rgba(22,104,216,.14); transition: all .18s;
    white-space: nowrap; flex-shrink: 0;
}
.amz-back-home:hover { background: rgba(22,104,216,.26); border-color: rgba(196,181,253,.6); color: #e9d5ff; }
.amz-back {
    display: flex; align-items: center; gap: 5px;
    color: rgba(255,255,255,.7); font-size: 13px; font-weight: 600;
    text-decoration: none; padding: 7px 12px;
    border: 1px solid rgba(255,255,255,.12); border-radius: var(--r-sm);
    background: rgba(255,255,255,.05); transition: all .15s;
    white-space: nowrap; flex-shrink: 0;
}
.amz-back:hover { border-color: rgba(255,255,255,.3); color: #fff; background: rgba(255,255,255,.09); }

/* Recherche navbar */
.amz-nav-search {
    flex: 1; display: flex; border-radius: var(--r-sm); overflow: hidden;
    border: 2px solid rgba(22,104,216,.35); max-width: 560px; transition: border-color .2s;
}
.amz-nav-search:focus-within { border-color: #1668D8; box-shadow: 0 0 0 3px rgba(22,104,216,.15); }
.amz-nav-search input {
    flex: 1; border: none; outline: none; padding: 9px 14px;
    font-size: 14px; font-family: var(--font); background: #fff; color: var(--text);
}
.amz-nav-search-btn {
    background: linear-gradient(135deg, #1668D8, #1F7CF6); border: none;
    padding: 0 16px; cursor: pointer; font-size: 16px; transition: opacity .15s;
    color: #fff; display: flex; align-items: center; justify-content: center;
}
.amz-nav-search-btn:hover { opacity: .85; }
.amz-nav-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.amz-nav-link {
    color: rgba(255,255,255,.8); font-size: 12px; text-decoration: none;
    padding: 6px 10px; border: 1px solid transparent; border-radius: var(--r-sm);
    transition: all .15s; white-space: nowrap;
}
.amz-nav-link:hover { border-color: rgba(255,255,255,.25); color: #fff; background: rgba(255,255,255,.06); }
.amz-nav-link strong { display: block; font-size: 13px; color: #fff; }

/* ══════════════════════════════
   BARRE MOBILE (sous nav, mobile only)
══════════════════════════════ */
.mobile-bar {
    display: none;
    padding: 10px 14px; gap: 8px;
    background: var(--surface); border-bottom: 1px solid var(--border);
    position: sticky; top: var(--nav-h); z-index: 150;
}
.mobile-search-wrap {
    flex: 1; display: flex; border-radius: var(--r-sm); overflow: hidden;
    border: 1.5px solid var(--border); transition: border-color .2s;
}
.mobile-search-wrap:focus-within { border-color: var(--brand); }
.mobile-search-wrap input {
    flex: 1; border: none; outline: none; padding: 9px 13px;
    font-size: 14px; font-family: var(--font); color: var(--text); background: #fff;
}
.mobile-search-wrap button {
    background: var(--brand); border: none; padding: 0 14px;
    font-size: 15px; cursor: pointer;
    color: #fff; display: flex; align-items: center; justify-content: center;
}
.btn-filter-open {
    display: flex; align-items: center; gap: 6px;
    padding: 9px 14px; border-radius: var(--r-sm);
    border: 1.5px solid var(--border); background: var(--surface);
    font-size: 13px; font-weight: 700; color: var(--text);
    cursor: pointer; white-space: nowrap; transition: all .15s;
    font-family: var(--font);
}
.btn-filter-open:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-mlt); }
.filter-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 18px; height: 18px; border-radius: 50%;
    background: var(--brand); color: #fff; font-size: 10px; font-weight: 800;
    display: none;
}
.filter-badge.visible { display: inline-flex; }

/* ══════════════════════════════
   DRAWER FILTRES MOBILE
══════════════════════════════ */
.drawer-overlay {
    display: none; position: fixed; inset: 0; z-index: 400;
    background: rgba(0,0,0,.5); backdrop-filter: blur(2px);
}
.drawer-overlay.open { display: block; }
.drawer-panel {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 500;
    background: var(--surface); border-radius: 20px 20px 0 0;
    padding: 0 0 32px;
    max-height: 88vh; overflow-y: auto;
    transform: translateY(100%);
    transition: transform .32s cubic-bezier(.32,1,.58,1);
}
.drawer-panel.open { transform: translateY(0); }
.drawer-handle {
    width: 40px; height: 4px; border-radius: 4px;
    background: var(--border); margin: 12px auto 0;
}
.drawer-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px 12px;
    border-bottom: 1px solid var(--border);
}
.drawer-title { font-size: 16px; font-weight: 800; color: var(--text); }
.drawer-close {
    width: 32px; height: 32px; border-radius: 50%;
    border: 1.5px solid var(--border); background: var(--grey);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; cursor: pointer; color: var(--muted);
    transition: all .15s;
}
.drawer-close:hover { background: var(--red-lt); border-color: var(--red); color: var(--red); }
.drawer-body { padding: 16px 20px; }
.drawer-reset {
    width: 100%; padding: 11px; margin-top: 4px;
    border-radius: var(--r-sm); border: 1.5px solid var(--border);
    background: var(--grey); color: var(--text-2); font-size: 13px;
    font-weight: 600; font-family: var(--font); cursor: pointer; transition: all .15s;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.drawer-reset:hover { border-color: var(--red); color: var(--red); background: var(--red-lt); }
.drawer-apply {
    width: 100%; padding: 13px; margin-top: 10px;
    border-radius: var(--r-sm); border: none;
    background: linear-gradient(135deg, #1668D8, #1F7CF6);
    color: #fff; font-size: 14px; font-weight: 700;
    font-family: var(--font); cursor: pointer;
    box-shadow: 0 4px 14px rgba(22,104,216,.3);
    display: flex; align-items: center; justify-content: center; gap: 6px;
}

/* ══════════════════════════════
   BANNIÈRE BOUTIQUE
══════════════════════════════ */
.shop-banner {
    background: linear-gradient(135deg, #291d95 0%, #3b20aa 50%, #aa28d9 100%);
    padding: 26px 28px;
    display: flex; align-items: center; gap: 20px;
    position: relative; overflow: hidden;
}
.shop-banner::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.06) 1px, transparent 0);
    background-size: 26px 26px; pointer-events: none;
}
.shop-banner::after {
    content: ''; position: absolute; right: -80px; top: -80px;
    width: 300px; height: 300px; border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
    pointer-events: none;
}
.shop-banner-logo {
    width: 72px; height: 72px; border-radius: var(--r);
    background: rgba(255,255,255,.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; flex-shrink: 0; overflow: hidden;
    border: 2px solid rgba(255,255,255,.28);
    box-shadow: 0 0 0 4px rgba(196,181,253,.2), 0 8px 24px rgba(0,0,0,.3);
    position: relative; z-index: 1;
}
.shop-banner-logo img { width: 100%; height: 100%; object-fit: cover; }
.shop-banner-info { flex: 1; min-width: 0; position: relative; z-index: 1; }
.shop-banner-name { font-size: 24px; font-weight: 800; color: #fff; margin-bottom: 8px; letter-spacing: -.4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.shop-banner-verified {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 11px; font-weight: 700; color: #6ee7b7;
    background: rgba(16,185,129,.18); border: 1px solid rgba(110,231,183,.35);
    padding: 3px 9px; border-radius: 20px; vertical-align: middle;
}
.shop-banner-desc {
    font-size: 13px; color: rgba(255,255,255,.78); line-height: 1.5;
    max-width: 640px; margin-bottom: 12px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.shop-banner-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.shop-banner-rating {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 12px; font-weight: 700; color: #fde68a;
    background: rgba(245,158,11,.16); border: 1px solid rgba(253,230,138,.3);
    padding: 3px 10px; border-radius: 20px;
}
.shop-banner-chip {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 12px; color: rgba(255,255,255,.8);
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
    padding: 3px 10px; border-radius: 20px;
}
.shop-banner-open {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(16,185,129,.2); color: #6ee7b7;
    border: 1px solid rgba(110,231,183,.3);
    font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 20px;
}
.shop-banner-open::before {
    content: ''; width: 6px; height: 6px; border-radius: 50%;
    background: #34d399; box-shadow: 0 0 6px #34d399;
    animation: pulse 1.8s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.3} }

/* ══════════════════════════════
   LAYOUT
══════════════════════════════ */
.amz-layout {
    max-width: 1500px; margin: 0 auto;
    display: flex; padding: 20px 20px 64px;
}

/* ══════════════════════════════
   SIDEBAR FILTRES (desktop)
══════════════════════════════ */
.amz-sidebar {
    width: 230px; flex-shrink: 0;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 18px;
    height: fit-content; position: sticky; top: 80px;
    box-shadow: var(--shadow-sm); margin-right: 20px;
}
.amz-sidebar-title {
    font-size: 15px; font-weight: 800; color: var(--text);
    border-bottom: 2px solid var(--brand-lt); padding-bottom: 10px; margin-bottom: 16px;
}
.amz-sidebar-section { margin-bottom: 20px; }
.amz-sidebar-section-title {
    font-size: 10.5px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: .6px;
    margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid var(--grey-2);
}
.amz-filter-item {
    display: flex; align-items: center; gap: 8px;
    padding: 5px 2px; cursor: pointer; border-radius: 6px; transition: background .12s;
}
.amz-filter-item:hover { background: var(--brand-mlt); }
.amz-filter-item input[type=radio] { accent-color: var(--brand); cursor: pointer; flex-shrink: 0; }
.amz-filter-item label { font-size: 13px; color: var(--text-2); cursor: pointer; transition: color .12s; }
.amz-filter-item:hover label { color: var(--brand); }
.amz-filter-item.active label { color: var(--brand-dk); font-weight: 700; }

.amz-price-range { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
.amz-price-input {
    flex: 1; min-width: 0; width: 0; padding: 7px 9px; border: 1.5px solid var(--border);
    border-radius: var(--r-sm); font-size: 13px; font-family: var(--font);
    color: var(--text); outline: none; transition: border-color .15s;
}
.amz-price-input:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(22,104,216,.1); }
.amz-price-btn {
    width: 100%; padding: 8px; background: var(--brand-mlt); border: 1.5px solid var(--brand-lt);
    border-radius: var(--r-sm); font-size: 13px; font-family: var(--font);
    cursor: pointer; transition: all .15s; color: var(--brand-dk); font-weight: 600;
}
.amz-price-btn:hover { background: var(--brand); border-color: var(--brand-dk); color: #fff; }

/* ══════════════════════════════
   CONTENU
══════════════════════════════ */
.amz-content { flex: 1; min-width: 0; }

.amz-results-bar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 11px 16px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 10px; margin-bottom: 14px; box-shadow: var(--shadow-sm); flex-wrap: wrap;
}
.amz-results-text { font-size: 13px; color: var(--muted); }
.amz-results-text strong { color: var(--text); font-weight: 700; }
.amz-sort-wrap { display: flex; align-items: center; gap: 8px; }
.amz-sort-label { font-size: 13px; color: var(--muted); white-space: nowrap; }
.amz-sort-select {
    padding: 7px 10px; border: 1.5px solid var(--border);
    border-radius: var(--r-sm); font-size: 13px; font-family: var(--font);
    color: var(--text); background: var(--surface); outline: none; cursor: pointer;
}
.amz-sort-select:focus { border-color: var(--brand); }
.amz-view-btns { display: flex; gap: 4px; }
.amz-view-btn {
    padding: 7px 10px; border: 1.5px solid var(--border);
    background: var(--surface); border-radius: var(--r-sm);
    cursor: pointer; font-size: 14px; transition: all .15s; color: var(--muted);
    display: flex; align-items: center; justify-content: center;
}
.amz-view-btn.active { background: var(--brand-mlt); border-color: var(--brand-lt); color: var(--brand-dk); }

/* ══════════════════════════════
   GRILLE PRODUITS
══════════════════════════════ */
.amz-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(235px, 1fr));
    gap: 14px;
}
.amz-grid.list-view { grid-template-columns: 1fr; }

/* ══════════════════════════════
   CARD PRODUIT
══════════════════════════════ */
.amz-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm);
    transition: box-shadow .25s, border-color .25s, transform .25s;
    display: flex; flex-direction: column; position: relative;
}
.amz-card:hover { box-shadow: 0 8px 32px rgba(22,104,216,.13); border-color: var(--brand-lt); transform: translateY(-3px); }

.amz-grid.list-view .amz-card { flex-direction: row; min-height: 180px; }
.amz-grid.list-view .amz-card-img { width: 210px; height: auto; min-height: 180px; flex-shrink: 0; }
.amz-grid.list-view .amz-card-body { flex: 1; padding: 16px 18px; }
.amz-grid.list-view .amz-card-footer { border-top: none; border-left: 1px solid var(--border); width: 180px; flex-shrink: 0; padding: 16px; display: flex; flex-direction: column; justify-content: center; }

.amz-card-img {
    height: 235px; overflow: hidden; position: relative;
    background: var(--grey-2); flex-shrink: 0; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}
.amz-card-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .35s;
}
.amz-card:hover .amz-card-img img { transform: scale(1.07); }
.amz-card-img-ph { font-size: 48px; opacity: .2; }
.amz-card-img::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(22,104,216,.07) 0%, transparent 60%);
    opacity: 0; transition: opacity .25s;
}
.amz-card:hover .amz-card-img::after { opacity: 1; }

.amz-card-badge {
    position: absolute; top: 9px; left: 9px; z-index: 1;
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 6px; white-space: nowrap;
}
.badge-promo   { background: #ef4444; color: #fff; box-shadow: 0 2px 6px rgba(239,68,68,.35); }
.badge-nouveau { background: linear-gradient(135deg, #1668D8, #1F7CF6); color: #fff; box-shadow: 0 2px 6px rgba(22,104,216,.35); }
.badge-vedette { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 2px 6px rgba(245,158,11,.35); }
.badge-rupture { background: #94a3b8; color: #fff; }

.amz-card-body { padding: 12px 14px 8px; flex: 1; display: flex; flex-direction: column; gap: 5px; }
.amz-card-cat {
    font-size: 10.5px; color: var(--brand); font-weight: 700;
    text-transform: uppercase; letter-spacing: .5px;
    background: var(--brand-mlt); padding: 2px 8px; border-radius: 5px; width: fit-content;
}
.amz-card-name {
    font-size: 13.5px; color: var(--text); line-height: 1.45;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
    font-weight: 600; cursor: pointer;
}
.amz-card-name:hover { color: var(--brand-dk); }
.amz-stars { display: flex; align-items: center; gap: 4px; }
.amz-stars-ico { color: #f59e0b; font-size: 13px; letter-spacing: 1px; }
.amz-stars-count { font-size: 11.5px; color: var(--muted); }
.amz-price-wrap { margin-top: 4px; }
.amz-price-main { font-size: 20px; font-weight: 800; color: var(--brand-dk); font-family: var(--mono); letter-spacing: -.5px; }
.amz-price-devise { font-size: 12px; color: var(--muted); font-weight: 400; font-family: var(--font); }
.amz-price-orig { font-size: 12px; color: var(--muted); text-decoration: line-through; }
.amz-price-remise { font-size: 11px; color: var(--red); font-weight: 700; background: var(--red-lt); padding: 1px 6px; border-radius: 4px; }
.amz-delivery { font-size: 12px; color: var(--green); display: flex; align-items: center; gap: 4px; font-weight: 500; }
.amz-stock-ok  { font-size: 12px; color: var(--green); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.amz-stock-low { font-size: 12px; color: var(--amber); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.amz-stock-out { font-size: 12px; color: var(--red); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }

.amz-card-footer { padding: 10px 14px 14px; }
.amz-btn-order {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; padding: 10px 12px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 700; font-family: var(--font);
    background: linear-gradient(135deg, #1668D8, #1F7CF6); color: #fff;
    border: none; cursor: pointer; text-decoration: none; transition: all .2s;
    box-shadow: 0 4px 12px rgba(22,104,216,.28);
}
.amz-btn-order:hover { background: linear-gradient(135deg, #1260C9, #7c3aed); box-shadow: 0 6px 20px rgba(22,104,216,.42); transform: translateY(-1px); color: #fff; }
.amz-btn-order.out { background: var(--grey-2); color: var(--muted); border: 1px solid var(--border); cursor: not-allowed; box-shadow: none; transform: none; }
.amz-btn-msg {
    display: flex; align-items: center; justify-content: center; gap: 5px;
    width: 100%; padding: 8px 12px; border-radius: var(--r-sm);
    font-size: 12px; font-weight: 600; font-family: var(--font);
    background: var(--brand-mlt); color: var(--brand-dk);
    border: 1.5px solid var(--brand-lt);
    cursor: pointer; text-decoration: none; margin-top: 7px; transition: all .15s;
}
.amz-btn-msg:hover { background: var(--brand); color: #fff; border-color: var(--brand-dk); }

/* Vide + flash + pagination */
.amz-flash { padding: 12px 16px; border-radius: var(--r); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.amz-flash-success { background: var(--green-lt); border-color: #a7f3d0; color: #065f46; }
.amz-flash-danger  { background: var(--red-lt); border-color: #fca5a5; color: #991b1b; }
.amz-empty { grid-column: 1/-1; padding: 80px 20px; text-align: center; background: var(--surface); border-radius: var(--r); border: 1px dashed var(--border); }
.amz-pagination { display: flex; justify-content: center; padding: 28px 0 8px; }

/* ══════════════════════════════
   RESPONSIVE
══════════════════════════════ */
@media (max-width: 960px) {
    .amz-sidebar  { display: none; }
    .mobile-bar   { display: flex; }
    .amz-layout   { padding: 14px 14px 50px; }
    .amz-nav-right{ display: none; }
    /* anti-zoom iOS sur recherche desktop visible ≤960 */
    .amz-nav-search input { font-size: 16px !important; }
    /* forcer grille sur mobile (vue liste trop large) */
    .amz-grid.list-view {
        grid-template-columns: repeat(2, 1fr);
    }
    .amz-grid.list-view .amz-card { flex-direction: column; min-height: unset; }
    .amz-grid.list-view .amz-card-img { width: 100%; min-height: unset; height: 195px; }
    .amz-grid.list-view .amz-card-footer { border-left: none; border-top: 1px solid var(--border); width: 100%; flex-direction: row; gap: 8px; padding: 10px 12px; }
}
@media (max-width: 640px) {
    .amz-nav { padding: 0 12px; gap: 8px; height: 56px; }
    :root { --nav-h: 56px; }
    .nav-logo-img   { width: 32px; height: 32px; }
    .nav-brand-name { font-size: 14px; }
    .amz-back-home  { padding: 6px 10px; font-size: 12px; }
    .amz-back       { display: none; }
    .amz-nav-search { display: none; }
    /* anti-zoom iOS sur tous les inputs */
    .mobile-search-wrap input { font-size: 16px !important; }
    .amz-price-input           { font-size: 16px !important; }
    .amz-sort-select           { font-size: 16px !important; }
    .amz-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .amz-grid.list-view { grid-template-columns: repeat(2, 1fr); }
    .amz-card-img  { height: 172px; }
    .amz-card-body { padding: 9px 10px 6px; }
    .amz-card-name { font-size: 12px; -webkit-line-clamp: 2; }
    .amz-price-main { font-size: 15px; }
    .amz-btn-msg { display: none; } /* trop encombrant sur mobile */
    .amz-btn-login { display: flex; } /* toujours visible sur mobile */
    .shop-banner { padding: 16px 14px; gap: 12px; }
    .shop-banner-logo { width: 52px; height: 52px; font-size: 22px; }
    .shop-banner-name { font-size: 17px; margin-bottom: 6px; }
    .amz-results-bar { padding: 9px 12px; }
    .amz-sort-label  { display: none; }
    .amz-view-btns   { display: none; } /* cache grille/liste sur mobile */
    .amz-layout { padding: 10px 10px 48px; }
}
@media (max-width: 400px) {
    .amz-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .amz-back-home .txt { display: none; }
    .amz-card-footer { padding: 7px 9px 11px; }
    .amz-btn-order   { font-size: 11.5px; padding: 8px 6px; }
    .shop-banner-chip { display: none; }
    .shop-banner-chip:first-of-type { display: inline-flex; }
    .amz-price-main { font-size: 14px; }
}
/* ══════════════════════════════
   LIGHTBOX
══════════════════════════════ */
.lb-overlay {
    display: none; position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.88); backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
    padding: 20px;
}
.lb-overlay.open { display: flex; }
.lb-img {
    max-width: 92vw; max-height: 88vh; border-radius: 12px;
    object-fit: contain; box-shadow: 0 24px 80px rgba(0,0,0,.6);
    animation: lbIn .22s ease;
}
@keyframes lbIn { from { opacity:0; transform:scale(.92); } to { opacity:1; transform:scale(1); } }
.lb-close {
    position: fixed; top: 16px; right: 16px;
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.3);
    color: #fff; font-size: 20px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s;
}
.lb-close:hover { background: rgba(255,255,255,.3); }
.amz-card-img { cursor: zoom-in; }

@media (max-width: 360px) {
    .amz-card-img { height: 220px; }
    .amz-card-name { font-size: 13px; -webkit-line-clamp: 3; }
    .amz-price-main { font-size: 17px; }
    .amz-btn-order  { font-size: 13px; padding: 11px; }
    .amz-btn-msg    { display: flex; } /* réafficher sur 1-col */
    .shop-banner-name { font-size: 15px; }
    .amz-layout { padding: 8px 8px 40px; }
}

/* ══ MOTION TOKENS (mêmes valeurs que l'accueil) ══ */
:root {
    --ease-out:    cubic-bezier(.22, 1, .36, 1);
    --ease-io:     cubic-bezier(.65, 0, .35, 1);
    --t-micro:     200ms;
    --t-enter:     500ms;
}
.amz-card { transition: box-shadow var(--t-micro) var(--ease-out), border-color var(--t-micro), transform var(--t-micro) var(--ease-out); }
.amz-card-img img { transition: transform .4s var(--ease-out); }
.amz-card:hover .amz-card-img img { transform: scale(1.05); }
.amz-price-main, .amz-price-orig, #resultCount { font-variant-numeric: tabular-nums; white-space: nowrap; }

/* Apparition en cascade (ajoutée en JS ; sans JS les cartes restent simplement visibles) */
.amz-card.is-entering { animation: cardIn var(--t-enter) var(--ease-out) backwards; animation-delay: var(--d, 0ms); }
@keyframes cardIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

/* Nom du produit = vrai lien (clavier, clic molette, lecteurs d'écran) */
a.amz-card-name { text-decoration: none; }
.amz-card-name:focus-visible, .amz-btn-order:focus-visible, .amz-filter-item:focus-within,
.amz-view-btn:focus-visible, .btn-filter-open:focus-visible, .amz-filtered-reset:focus-visible {
    outline: 3px solid var(--brand); outline-offset: 2px; border-radius: 6px;
}

/* Filtres appliqués à une seule page de résultats : on le dit clairement */
.amz-page-hint {
    display: none; align-items: center; gap: 7px; margin: -4px 0 14px;
    font-size: 12.5px; color: var(--text-2); background: var(--brand-mlt);
    border: 1px solid var(--brand-lt); border-radius: var(--r-sm); padding: 9px 12px;
}
.amz-page-hint.show { display: flex; }

/* Aucun produit ne correspond aux filtres */
.amz-filtered-empty { display: none; grid-column: 1/-1; padding: 56px 20px; text-align: center; background: var(--surface); border-radius: var(--r); border: 1px dashed var(--border); }
.amz-filtered-empty.show { display: block; animation: cardIn var(--t-enter) var(--ease-out) both; }
.amz-filtered-empty strong { display: block; font-size: 17px; color: var(--text); margin-bottom: 6px; }
.amz-filtered-empty p { margin: 0 0 18px; font-size: 14px; color: var(--muted); }
.amz-filtered-reset {
    display: inline-flex; align-items: center; gap: 7px; min-height: 44px; padding: 0 22px; border-radius: 50px;
    border: none; background: var(--brand); color: #fff; font-size: 13.5px; font-weight: 700; font-family: var(--font); cursor: pointer;
    box-shadow: 0 6px 18px rgba(22,104,216,.3); transition: background var(--t-micro), transform var(--t-micro) var(--ease-out);
}
.amz-filtered-reset:hover { background: var(--brand-dk); transform: translateY(-2px); }

@media (prefers-reduced-motion: reduce) {
    .amz-card, .amz-card-img img { transition: none !important; }
    .amz-card.is-entering, .amz-filtered-empty.show, .shop-banner-open::before { animation: none !important; }
}
</style>
@endpush

@section('content')
@php
    $devise = $shop->currency ?? 'GNF';
@endphp

{{-- ══ NAVBAR ══ --}}
<nav class="amz-nav">
    <a href="{{ url('/') }}" class="nav-logo-wrap">
        <img src="/images/shopio-logo-192.png" alt="Shopio" class="nav-logo-img" width="36" height="36">
        <span class="nav-brand-name">Shopio</span>
    </a>
    <a href="{{ url('/') }}" class="amz-back-home">{!! \App\Support\IconLibrary::svg('home', '', 14) !!} <span class="txt">Accueil</span></a>
    <a href="{{ route('shops.index') }}" class="amz-back">{!! \App\Support\IconLibrary::svg('arrow-left', '', 13) !!} Boutiques</a>
    <div class="amz-nav-search">
        <input type="text" id="prodSearch" placeholder="Rechercher dans {{ $shop->name }}…" autocomplete="off">
        <button class="amz-nav-search-btn" type="button">{!! \App\Support\IconLibrary::svg('search', '', 16) !!}</button>
    </div>
    <div class="amz-nav-right">
        @auth
            @if(Auth::user()->role === 'client')
            <a href="{{ route('client.orders.index') }}" class="amz-nav-link">
                <span>Retours</span><strong>& Commandes</strong>
            </a>
            @endif
        @endauth
    </div>
</nav>

{{-- ══ BARRE MOBILE (recherche + bouton filtres) ══ --}}
<div class="mobile-bar">
    <div class="mobile-search-wrap">
        <input type="text" id="prodSearchMobile" placeholder="Rechercher un produit…" autocomplete="off">
        <button type="button">{!! \App\Support\IconLibrary::svg('search', '', 15) !!}</button>
    </div>
    <button class="btn-filter-open" type="button" onclick="openDrawer()">
        {!! \App\Support\IconLibrary::svg('sliders', '', 15) !!} Filtres
        <span class="filter-badge" id="filterBadge">0</span>
    </button>
</div>

{{-- ══ DRAWER FILTRES MOBILE ══ --}}
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
<div class="drawer-panel" id="drawerPanel">
    <div class="drawer-handle"></div>
    <div class="drawer-header">
        <span class="drawer-title" style="display:flex;align-items:center;gap:7px">{!! \App\Support\IconLibrary::svg('sliders', '', 16) !!} Affiner les résultats</span>
        <button class="drawer-close" onclick="closeDrawer()">{!! \App\Support\IconLibrary::svg('x', '', 15) !!}</button>
    </div>
    <div class="drawer-body">

        {{-- Catégories --}}
        @if(!empty($categories))
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Catégorie</div>
            <div class="amz-filter-item active" onclick="filterCat('', this)">
                <input type="radio" name="cat_m" id="mcat-all" checked>
                <label for="mcat-all">Tous les produits</label>
            </div>
            @foreach($categories as $cat)
            <div class="amz-filter-item" onclick="filterCat('{{ strtolower($cat) }}', this)">
                <input type="radio" name="cat_m" id="mcat-{{ Str::slug($cat) }}">
                <label for="mcat-{{ Str::slug($cat) }}">{{ $cat }}</label>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Disponibilité --}}
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Disponibilité</div>
            <div class="amz-filter-item active" onclick="filterStock(false, this)">
                <input type="radio" name="stock_m" id="mstock-all" checked>
                <label for="mstock-all">Tous les articles</label>
            </div>
            <div class="amz-filter-item" onclick="filterStock(true, this)">
                <input type="radio" name="stock_m" id="mstock-avail">
                <label for="mstock-avail">En stock uniquement</label>
            </div>
        </div>

        {{-- Fourchette de prix --}}
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Fourchette de prix</div>
            <div class="amz-price-range">
                <input type="number" class="amz-price-input" id="mPriceMin" placeholder="Min">
                <span style="color:var(--muted);font-size:12px">—</span>
                <input type="number" class="amz-price-input" id="mPriceMax" placeholder="Max">
            </div>
        </div>

        {{-- Tri --}}
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Trier par</div>
            <div class="amz-filter-item active" onclick="setSort('default', this, true)">
                <input type="radio" name="sort_m" id="msort-def" checked>
                <label for="msort-def">Pertinence</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('price_asc', this, true)">
                <input type="radio" name="sort_m" id="msort-pa">
                <label for="msort-pa">Prix croissant</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('price_desc', this, true)">
                <input type="radio" name="sort_m" id="msort-pd">
                <label for="msort-pd">Prix décroissant</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('name', this, true)">
                <input type="radio" name="sort_m" id="msort-name">
                <label for="msort-name">Nom A → Z</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('featured', this, true)">
                <input type="radio" name="sort_m" id="msort-feat">
                <label for="msort-feat" style="display:inline-flex;align-items:center;gap:5px">{!! \App\Support\IconLibrary::svg('star', '', 13) !!} Vedettes en premier</label>
            </div>
        </div>

        <button class="drawer-reset" onclick="resetFilters()">{!! \App\Support\IconLibrary::svg('rotate', '', 14) !!} Réinitialiser les filtres</button>
        <button class="drawer-apply" onclick="applyAndClose()">{!! \App\Support\IconLibrary::svg('check', '', 14) !!} Appliquer</button>
    </div>
</div>

{{-- ══ LIGHTBOX ══ --}}
<div class="lb-overlay" id="lbOverlay" onclick="closeLb(event)">
    <button class="lb-close" onclick="closeLb()">{!! \App\Support\IconLibrary::svg('x', '', 18) !!}</button>
    <img class="lb-img" id="lbImg" src="" alt="">
</div>

{{-- ══ BANNIÈRE BOUTIQUE ══ --}}
<div class="shop-banner">
    <div class="shop-banner-logo">
        @if($shop->image)
            <img src="{{ \App\Services\ImageOptimizer::url($shop->image, 'thumb') ?? asset('storage/'.$shop->image) }}"
                 alt="{{ $shop->name }}"
                 width="72" height="72"
                 fetchpriority="high">
        @else
            <span style="display:flex;color:#fff">{!! \App\Support\IconLibrary::svg('store', '', 32) !!}</span>
        @endif
    </div>
    <div class="shop-banner-info">
        <div class="shop-banner-name">
            {{ $shop->name }}
            @if($shop->is_approved)<span class="shop-banner-verified">{!! \App\Support\IconLibrary::svg('check', '', 11) !!} Boutique vérifiée</span>@endif
        </div>
        @if($shop->description)
        <div class="shop-banner-desc">{{ Str::limit(strip_tags($shop->description), 140) }}</div>
        @endif
        <div class="shop-banner-meta">
            @if($reviewAvg)<span class="shop-banner-rating">{!! \App\Support\IconLibrary::svg('star', '', 12) !!} {{ number_format($reviewAvg, 1) }} <span style="opacity:.8">({{ $reviewCount }} avis)</span></span>@endif
            @if($deliveredCount > 0)<span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('check', '', 12) !!} {{ $deliveredCount }} commande{{ $deliveredCount > 1 ? 's' : '' }} livrée{{ $deliveredCount > 1 ? 's' : '' }}</span>@endif
            @if($shop->type)<span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('tag', '', 12) !!} {{ $shop->type }}</span>@endif
            @if($shop->address)<span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('map-pin', '', 12) !!} {{ Str::limit($shop->address, 28) }}</span>@endif
            <span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('package', '', 12) !!} {{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}</span>
            @if($shop->phone)<span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('phone', '', 12) !!} {{ $shop->phone }}</span>@endif
        </div>
    </div>
</div>

{{-- ══ LAYOUT ══ --}}
<div class="amz-layout">

    {{-- Sidebar desktop --}}
    <aside class="amz-sidebar">
        <div class="amz-sidebar-title">Affiner les résultats</div>

        @if(!empty($categories))
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Catégorie</div>
            <div class="amz-filter-item active" onclick="filterCat('', this)">
                <input type="radio" name="cat_d" id="dcat-all" checked>
                <label for="dcat-all">Tous les produits</label>
            </div>
            @foreach($categories as $cat)
            <div class="amz-filter-item" onclick="filterCat('{{ strtolower($cat) }}', this)">
                <input type="radio" name="cat_d" id="dcat-{{ Str::slug($cat) }}">
                <label for="dcat-{{ Str::slug($cat) }}">{{ $cat }}</label>
            </div>
            @endforeach
        </div>
        @endif

        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Disponibilité</div>
            <div class="amz-filter-item active" onclick="filterStock(false, this)">
                <input type="radio" name="stock_d" id="dstock-all" checked>
                <label for="dstock-all">Tous les articles</label>
            </div>
            <div class="amz-filter-item" onclick="filterStock(true, this)">
                <input type="radio" name="stock_d" id="dstock-avail">
                <label for="dstock-avail">En stock uniquement</label>
            </div>
        </div>

        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Fourchette de prix</div>
            <div class="amz-price-range">
                <input type="number" class="amz-price-input" id="dPriceMin" placeholder="Min">
                <span style="color:var(--muted);font-size:12px">—</span>
                <input type="number" class="amz-price-input" id="dPriceMax" placeholder="Max">
            </div>
            <button class="amz-price-btn" onclick="filterPrice()">Appliquer le prix</button>
        </div>

        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Trier par</div>
            <div class="amz-filter-item active" onclick="setSort('default', this, false)">
                <input type="radio" name="sort_d" id="dsort-def" checked>
                <label for="dsort-def">Pertinence</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('price_asc', this, false)">
                <input type="radio" name="sort_d" id="dsort-pa">
                <label for="dsort-pa">Prix croissant</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('price_desc', this, false)">
                <input type="radio" name="sort_d" id="dsort-pd">
                <label for="dsort-pd">Prix décroissant</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('name', this, false)">
                <input type="radio" name="sort_d" id="dsort-name">
                <label for="dsort-name">Nom A → Z</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('featured', this, false)">
                <input type="radio" name="sort_d" id="dsort-feat">
                <label for="dsort-feat" style="display:inline-flex;align-items:center;gap:5px">{!! \App\Support\IconLibrary::svg('star', '', 13) !!} Vedettes en premier</label>
            </div>
        </div>
    </aside>

    {{-- Contenu --}}
    <div class="amz-content">

        @foreach(['success','danger'] as $t)
            @if(session($t))<div class="amz-flash amz-flash-{{ $t }}">{{ session($t) }}</div>@endif
        @endforeach

        <div class="amz-results-bar">
            <div class="amz-results-text">
                <strong id="resultCount">{{ $products->total() }}</strong>
                résultat{{ $products->total() > 1 ? 's' : '' }} — <strong>{{ $shop->name }}</strong>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <div class="amz-sort-wrap">
                    <span class="amz-sort-label">Trier :</span>
                    <select class="amz-sort-select" id="sortSelectTop" onchange="setSort(this.value, null, false)">
                        <option value="default">Pertinence</option>
                        <option value="price_asc">Prix croissant</option>
                        <option value="price_desc">Prix décroissant</option>
                        <option value="name">Nom A → Z</option>
                        <option value="featured">Vedettes en premier</option>
                    </select>
                </div>
                <div class="amz-view-btns">
                    <button class="amz-view-btn active" id="btnGrid" onclick="setView('grid')" title="Grille">{!! \App\Support\IconLibrary::svg('grid', '', 14) !!}</button>
                    <button class="amz-view-btn" id="btnList" onclick="setView('list')" title="Liste">{!! \App\Support\IconLibrary::svg('list', '', 14) !!}</button>
                </div>
            </div>
        </div>

        @if($products->hasPages())
        <div class="amz-page-hint" id="pageHint" role="status">
            {!! \App\Support\IconLibrary::svg('alert', '', 14) !!}
            Filtres appliqués aux {{ $products->count() }} produits de cette page (page {{ $products->currentPage() }} sur {{ $products->lastPage() }}).
        </div>
        @endif

        <div class="amz-grid" id="prodGrid">
            @forelse($products as $product)
            @php
                $hasPromo = $product->original_price && $product->original_price > $product->price;
                $remise   = $hasPromo ? round((1 - $product->price / $product->original_price) * 100) : 0;
                $stockVal = $product->stock ?? null;
                $stockOut = $stockVal !== null && $stockVal <= 0;
                $stockLow = $stockVal !== null && $stockVal > 0 && $stockVal <= 5;
                $isNew    = $product->created_at->diffInDays(now()) <= 7;
            @endphp
            <div class="amz-card"
                 data-name="{{ strtolower($product->name) }}"
                 data-cat="{{ strtolower($product->category ?? '') }}"
                 data-price="{{ $product->current_price }}"
                 data-featured="{{ $product->is_featured ? '1' : '0' }}"
                 data-stock="{{ $stockOut ? 'out' : 'in' }}">

                <div class="amz-card-img" onclick="openLb('{{ \App\Services\ImageOptimizer::url($product->image, 'medium') ?? asset('storage/'.($product->image ?? '')) }}', '{{ addslashes($product->name) }}')">
                    @if($product->image)
                        <img src="{{ \App\Services\ImageOptimizer::url($product->image, 'thumb') ?? asset('storage/'.$product->image) }}"
                             srcset="{{ \App\Services\ImageOptimizer::url($product->image, 'thumb') }} 300w,
                                     {{ \App\Services\ImageOptimizer::url($product->image, 'medium') }} 800w"
                             sizes="(max-width:640px) 150px, 300px"
                             width="300" height="200"
                             alt="{{ $product->name }}"
                             loading="{{ $loop->index < 2 ? 'eager' : 'lazy' }}"
                             {{ $loop->index < 2 ? 'fetchpriority=high' : '' }}>
                    @else
                        <div class="amz-card-img-ph">{!! \App\Support\IconLibrary::svg('image', '', 44) !!}</div>
                    @endif
                    @if($product->is_flash_active)<span class="amz-card-badge" style="background:linear-gradient(135deg,#dc2626,#f97316)">{!! \App\Support\IconLibrary::svg('zap', '', 11) !!} Flash −{{ $product->flash_discount_percent }}%</span>
                    @elseif($hasPromo)<span class="amz-card-badge badge-promo">-{{ $remise }}%</span>
                    @elseif($isNew)<span class="amz-card-badge badge-nouveau">{!! \App\Support\IconLibrary::svg('sparkles', '', 11) !!} Nouveau</span>
                    @elseif($product->is_featured)<span class="amz-card-badge badge-vedette">{!! \App\Support\IconLibrary::svg('star', '', 11) !!} Vedette</span>
                    @elseif($stockOut)<span class="amz-card-badge badge-rupture">Rupture</span>
                    @endif
                </div>

                <div class="amz-card-body">
                    @if($product->category)
                    <div class="amz-card-cat">{{ $product->category }}</div>
                    @endif
                    <a class="amz-card-name" href="{{ route('client.orders.createFromProduct', $product) }}">
                        {{ $product->name }}
                    </a>
                    <div class="amz-price-wrap">
                        <div style="display:flex;align-items:baseline;gap:8px;flex-wrap:wrap">
                            <span class="amz-price-main">{{ number_format($product->current_price, 0, ',', ' ') }}<span class="amz-price-devise"> {{ $devise }}</span></span>
                            @if($product->is_flash_active)
                            <span class="amz-price-orig">{{ number_format($product->price, 0, ',', ' ') }}</span>
                            <span class="amz-price-remise">-{{ $product->flash_discount_percent }}%</span>
                            @elseif($hasPromo)
                            <span class="amz-price-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>
                            <span class="amz-price-remise">-{{ $remise }}%</span>
                            @endif
                        </div>
                        <div class="amz-delivery">{!! \App\Support\IconLibrary::svg('check', '', 12) !!} Livraison disponible</div>
                    </div>
                    @if($stockVal !== null)
                        @if($stockOut)<div class="amz-stock-out">{!! \App\Support\IconLibrary::svg('x', '', 12) !!} Rupture de stock</div>
                        @elseif($stockLow)<div class="amz-stock-low">{!! \App\Support\IconLibrary::svg('alert', '', 12) !!} Plus que {{ $stockVal }} en stock</div>
                        @else<div class="amz-stock-ok">{!! \App\Support\IconLibrary::svg('check', '', 12) !!} En stock</div>
                        @endif
                    @endif
                </div>

                <div class="amz-card-footer">
                    @auth
                        @if(Auth::user()->role === 'client')
                            @if(!$stockOut)
                            <a href="{{ route('client.orders.createFromProduct', $product) }}" class="amz-btn-order">{!! \App\Support\IconLibrary::svg('cart', '', 14) !!} Commander</a>
                            @else
                            <div class="amz-btn-order out">Indisponible</div>
                            @endif
                            <a href="{{ route('client.messages.index', $product) }}" class="amz-btn-msg">{!! \App\Support\IconLibrary::svg('message', '', 13) !!} Poser une question</a>
                        @endif
                    @else
                        @if(!$stockOut)
                        <a href="{{ route('client.orders.createFromProduct', $product) }}" class="amz-btn-order">{!! \App\Support\IconLibrary::svg('cart', '', 14) !!} Commander</a>
                        @else
                        <div class="amz-btn-order out">Indisponible</div>
                        @endif
                        @php $msgUrl = route('client.messages.index', $product); @endphp
                        <a href="{{ route('register', ['redirect' => $msgUrl, 'role' => 'client']) }}" class="amz-btn-msg">
                            {!! \App\Support\IconLibrary::svg('message', '', 13) !!} Poser une question
                        </a>
                    @endauth
                </div>
            </div>
            @empty
            <div class="amz-empty">
                <div style="display:flex;justify-content:center;opacity:.2;margin-bottom:14px">{!! \App\Support\IconLibrary::svg('package', '', 52) !!}</div>
                <div style="font-size:18px;font-weight:800;margin-bottom:8px;color:var(--text)">Aucun produit trouvé</div>
                <p style="color:var(--muted);font-size:14px;margin:0">Cette boutique n'a pas encore de produits.</p>
            </div>
            @endforelse
            @if($products->isNotEmpty())
            <div class="amz-filtered-empty" id="filteredEmpty" role="status">
                <strong>Aucun produit ne correspond à vos filtres</strong>
                <p>Essayez une autre catégorie, une autre fourchette de prix ou un autre mot-clé.</p>
                <button type="button" class="amz-filtered-reset" onclick="resetFilters(true)">{!! \App\Support\IconLibrary::svg('rotate', '', 15) !!} Réinitialiser les filtres</button>
            </div>
            @endif
        </div>

        <div class="amz-pagination">{{ $products->links() }}</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ── État global des filtres ── */
var state = { cat: '', sort: 'default', stockOnly: false, priceMin: 0, priceMax: Infinity, search: '' };

/* ── Navigation produit ── */
function goToProduct(url) { window.location.href = url; }

/* ── Lightbox ── */
function openLb(src, alt) {
    if (!src) return;
    var img = document.getElementById('lbImg');
    img.src = src;
    img.alt = alt || '';
    document.getElementById('lbOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLb(e) {
    if (e && e.target === document.getElementById('lbImg')) return;
    document.getElementById('lbOverlay').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLb();
});

/*
 * ── Sync visuelle GLOBALE (sidebar + drawer) ──
 * Parcourt tous les .amz-filter-item et active ceux dont le 1er argument
 * de onclick correspond à la valeur attendue.
 * Corrige le bug : sélection dans sidebar ne mettait pas à jour le drawer, et vice versa.
 */
function _syncRadios(fnName, activeVal) {
    var re = new RegExp(fnName.replace('.', '\\.') + "\\(([^,)]+)");
    document.querySelectorAll('.amz-filter-item').forEach(function(item) {
        var oc = item.getAttribute('onclick') || '';
        if (!oc.includes(fnName)) return;
        var m = oc.match(re);
        if (!m) return;
        var raw   = m[1].trim().replace(/'/g, '');  // retire les guillemets simples
        var match = (raw === String(activeVal));
        item.classList.toggle('active', match);
        var r = item.querySelector('input[type=radio]');
        if (r) r.checked = match;
    });
}

/* ── Catégorie ── */
function filterCat(cat) {
    state.cat = cat;
    _syncRadios('filterCat', cat);
    updateBadge();
    applyFilters();
}

/* ── Stock ── */
function filterStock(onlyIn) {
    state.stockOnly = onlyIn;
    _syncRadios('filterStock', onlyIn);
    updateBadge();
    applyFilters();
}

/* ── Prix desktop (sidebar) ── */
function filterPrice() {
    var minEl = document.getElementById('dPriceMin');
    var maxEl = document.getElementById('dPriceMax');
    /* Bug fix : 0 est falsy donc on utilise une vérification explicite */
    state.priceMin = minEl.value !== '' ? parseFloat(minEl.value) : 0;
    state.priceMax = maxEl.value !== '' ? parseFloat(maxEl.value) : Infinity;
    /* Sync vers les inputs mobiles du drawer */
    var mMin = document.getElementById('mPriceMin');
    var mMax = document.getElementById('mPriceMax');
    if (mMin) mMin.value = minEl.value;
    if (mMax) mMax.value = maxEl.value;
    updateBadge();
    applyFilters();
}

/* ── Tri ── */
function setSort(val, el, fromDrawer) {
    state.sort = val;
    /* Sync le select du haut */
    var sel = document.getElementById('sortSelectTop');
    if (sel) sel.value = val;
    /* Sync les radios dans les deux panneaux */
    _syncRadios('setSort', val);
    updateBadge();
    applyFilters();
}

/* ── Vue grille / liste ── */
function setView(type) {
    var grid = document.getElementById('prodGrid');
    if (type === 'list') {
        grid.classList.add('list-view');
        document.getElementById('btnList').classList.add('active');
        document.getElementById('btnGrid').classList.remove('active');
    } else {
        grid.classList.remove('list-view');
        document.getElementById('btnGrid').classList.add('active');
        document.getElementById('btnList').classList.remove('active');
    }
}

/* ── Application des filtres ──
 * Note : filtre uniquement les produits de la page courante.
 * Les produits sur d'autres pages (pagination) ne sont pas accessibles côté client. */
var REDUCE_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function applyFilters() {
    var cards = Array.from(document.querySelectorAll('.amz-card'));
    /* FLIP : on mémorise la position de chaque carte visible avant le tri/filtre,
       pour la faire glisser en douceur vers sa nouvelle place ensuite. */
    var before = new Map();
    if (!REDUCE_MOTION) {
        cards.forEach(function(c) { if (c.style.display !== 'none') before.set(c, c.getBoundingClientRect()); });
    }
    var visible = cards.filter(function(c) {
        var name  = (c.dataset.name  || '').toLowerCase();
        var cat   = (c.dataset.cat   || '').toLowerCase();
        var price = parseFloat(c.dataset.price) || 0;
        var stock = c.dataset.stock;
        return (!state.cat       || cat === state.cat)
            && (!state.search    || name.includes(state.search))
            && (!state.stockOnly || stock === 'in')
            && price >= state.priceMin
            && price <= state.priceMax;
    });
    cards.forEach(function(c) { c.style.display = 'none'; });
    visible.sort(function(a, b) {
        if (state.sort === 'price_asc')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
        if (state.sort === 'price_desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
        if (state.sort === 'featured')   return parseInt(b.dataset.featured) - parseInt(a.dataset.featured);
        if (state.sort === 'name')       return (a.dataset.name||'').localeCompare(b.dataset.name||'');
        return 0;
    });
    var grid = document.getElementById('prodGrid');
    visible.forEach(function(c) { c.style.display = ''; grid.appendChild(c); });
    var el = document.getElementById('resultCount');
    if (el) el.textContent = visible.length;

    var filtering = !!(state.cat || state.search || state.stockOnly || state.priceMin > 0 || state.priceMax < Infinity);
    var empty = document.getElementById('filteredEmpty');
    if (empty) empty.classList.toggle('show', visible.length === 0);
    var hint = document.getElementById('pageHint');
    if (hint) hint.classList.toggle('show', filtering);

    if (REDUCE_MOTION) return;
    visible.forEach(function(c, i) {
        var first = before.get(c);
        if (!first) { // carte qui réapparaît : simple fondu
            c.classList.remove('is-entering'); void c.offsetWidth;
            c.style.setProperty('--d', Math.min(i, 10) * 40 + 'ms');
            c.classList.add('is-entering');
            return;
        }
        var last = c.getBoundingClientRect();
        var dx = first.left - last.left, dy = first.top - last.top;
        if (!dx && !dy) return;
        c.animate([{ transform: 'translate(' + dx + 'px,' + dy + 'px)' }, { transform: 'none' }],
                  { duration: 450, easing: 'cubic-bezier(.65,0,.35,1)' });
    });
}

/* ── Badge compteur de filtres actifs ── */
function updateBadge() {
    var count = 0;
    if (state.cat)       count++;
    if (state.stockOnly) count++;
    if (state.priceMin > 0 || state.priceMax < Infinity) count++;
    if (state.sort !== 'default') count++;
    var badge = document.getElementById('filterBadge');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('visible', count > 0);
    }
}

/* ── Réinitialiser les filtres ── */
function resetFilters(clearSearch) {
    state = { cat: '', sort: 'default', stockOnly: false, priceMin: 0, priceMax: Infinity, search: clearSearch ? '' : state.search };
    if (clearSearch) {
        ['prodSearch', 'prodSearchMobile'].forEach(function(id) { var el = document.getElementById(id); if (el) el.value = ''; });
    }
    ['mPriceMin','mPriceMax','dPriceMin','dPriceMax'].forEach(function(id) {
        var el = document.getElementById(id); if (el) el.value = '';
    });
    _syncRadios('filterCat',   '');
    _syncRadios('filterStock', 'false');
    _syncRadios('setSort',     'default');
    var sel = document.getElementById('sortSelectTop');
    if (sel) sel.value = 'default';
    updateBadge();
    applyFilters();
}

/* ── Drawer mobile ── */
function openDrawer() {
    document.getElementById('drawerOverlay').classList.add('open');
    document.getElementById('drawerPanel').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeDrawer() {
    document.getElementById('drawerOverlay').classList.remove('open');
    document.getElementById('drawerPanel').classList.remove('open');
    document.body.style.overflow = '';
}
function applyAndClose() {
    var minEl = document.getElementById('mPriceMin');
    var maxEl = document.getElementById('mPriceMax');
    /* Bug fix : n'écraser le prix que si l'utilisateur a saisi quelque chose dans le drawer */
    if (minEl.value !== '' || maxEl.value !== '') {
        state.priceMin = minEl.value !== '' ? parseFloat(minEl.value) : 0;
        state.priceMax = maxEl.value !== '' ? parseFloat(maxEl.value) : Infinity;
        /* Sync vers les inputs desktop */
        var dMin = document.getElementById('dPriceMin');
        var dMax = document.getElementById('dPriceMax');
        if (dMin) dMin.value = minEl.value;
        if (dMax) dMax.value = maxEl.value;
    }
    updateBadge();
    applyFilters();
    closeDrawer();
}

/* ── Recherche (desktop + mobile synchro) ── */
function handleSearch(val) {
    state.search = val.toLowerCase().trim();
    var d = document.getElementById('prodSearch');
    var m = document.getElementById('prodSearchMobile');
    if (d && d.value.toLowerCase().trim() !== state.search) d.value = val;
    if (m && m.value.toLowerCase().trim() !== state.search) m.value = val;
    applyFilters();
}

var searchTimer;
function onSearchInput(val) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() { handleSearch(val); }, 220);
}

document.addEventListener('DOMContentLoaded', function() {
    var d = document.getElementById('prodSearch');
    var m = document.getElementById('prodSearchMobile');
    if (d) d.addEventListener('input', function() { onSearchInput(this.value); });
    if (m) m.addEventListener('input', function() { onSearchInput(this.value); });

    /* Apparition en cascade des cartes (plafonnée à ~500 ms au total) */
    if (!REDUCE_MOTION) {
        document.querySelectorAll('.amz-card').forEach(function(c, i) {
            c.style.setProperty('--d', Math.min(i, 12) * 40 + 'ms');
            c.classList.add('is-entering');
        });
    }
});
</script>
@endpush
