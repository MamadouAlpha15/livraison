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
    $shopImg  = ($shareImage ?? null) ?: ($shop->image ? (\App\Services\ImageOptimizer::url($shop->image, 'medium') ?? asset('storage/'.$shop->image)) : asset('images/shopio-logo-192.png'));
    $activeCollection = $activeCollection ?? null;
    $previewLink = $previewLink ?? null;
    $proofs = $proofs ?? ['rating' => (bool) $reviewAvg, 'delivered' => $deliveredCount > 0, 'response' => false];
    // Page d'une catégorie (lien partagé) : titre et aperçu de partage à son nom
    $activeCategory = $activeCategory ?? null;
    $pageTitle = $activeCategory ? $activeCategory['name'] . ' · ' . $shop->name : ($activeCollection ? $activeCollection['name'] . ' · ' . $shop->name : $shop->name . ' — Boutique en ligne');
    if ($activeCategory) {
        $shopDesc = $activeCategory['count'] . ' produit' . ($activeCategory['count'] > 1 ? 's' : '') . ' « ' . $activeCategory['name'] . ' » chez ' . $shop->name . '. Commandez en ligne, paiement à la livraison.';
    }
@endphp

@section('title', ($activeCategory || $activeCollection) ? $pageTitle : $shop->name . ' — Produits')
@section('description', \Illuminate\Support\Str::limit($shopDesc, 160))
@php $bodyClass = 'is-dashboard'; @endphp

@push('meta')
{{-- Aperçu riche quand ce lien est collé sur WhatsApp / Facebook / Messenger : c'est
     précisément ce que le vendeur partage pour attirer des clients, donc la carte de
     prévisualisation doit montrer la boutique (photo + nom + description), pas le site. --}}
@if($previewLink)<meta name="robots" content="noindex, nofollow">@endif
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit($shopDesc, 200) }}">
<meta property="og:image" content="{{ $shopImg }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="Shopio">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
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

/* ══════════════════════════════════════════════════════════════════
   VITRINE PERSONNALISABLE (« Personnaliser ma vitrine » côté vendeur)
   Variables posées sur .sf-flow : --sf-accent, --sf-ink, --sf-cols
══════════════════════════════════════════════════════════════════ */
.shop-banner { background: radial-gradient(120% 140% at 100% 0%, rgba(31,124,246,.35), transparent 55%), linear-gradient(135deg, #0B1E3D, #132B52) !important; border-bottom: 6px solid var(--sf-accent, #1F7CF6); }
.sf-banner-compact { padding: 14px 22px !important; gap: 14px !important; }
.sf-banner-compact .shop-banner-desc { display: none; }
.sf-banner-compact .shop-banner-logo { width: 52px; height: 52px; }
.sf-banner-compact .shop-banner-name { font-size: 19px; margin-bottom: 4px; }
.sf-slim-head { max-width: 1500px; margin: 0 auto; padding: 18px 20px 0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.sf-slim-name { font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
.sf-slim-badge { display: inline-flex; align-items: center; gap: 4px; height: 24px; padding: 0 10px; border-radius: 999px; background: #E7F6EC; color: #15803D; font-size: 12px; font-weight: 800; }
/* Statut Ouvert / Fermé / En pause (horaires du bloc « Adresse et horaires », heure de Conakry) */
.sf-status { display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 11px; border-radius: 999px; font-size: 12.5px; font-weight: 800; white-space: nowrap; }
.sf-status::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.shop-banner .sf-status.is-open { background: rgba(34,197,94,.18); color: #86EFAC; }
.shop-banner .sf-status.is-closed { background: rgba(255,255,255,.12); color: #E3E8F2; }
.sf-status.is-pause { background: #F59E0B; color: #16162A; }
.sf-slim-head .sf-status.is-open { background: #E7F6EC; color: #15803D; }
.sf-slim-head .sf-status.is-closed { background: var(--grey-2); color: var(--text-2); }
.sf-slim-meta { display: inline-flex; align-items: center; gap: 4px; font-size: 13px; color: var(--muted); }
.sf-slim-meta .ico { color: #F59E0B; }

.sf-flow { display: flex; flex-direction: column; }
/* Dans une colonne flex, les marges auto (centrage) empêchent l'étirement : sans largeur
   explicite, la zone produits prenait la largeur de tout le carrousel et débordait. */
.sf-flow > .amz-layout { width: 100%; }
.sf-wrap { width: 100%; max-width: 1500px; margin: 0 auto; padding: 18px 20px 0; }
.sf-h2 { margin: 0 0 12px; font-size: 20px; font-weight: 800; letter-spacing: -.02em; color: var(--text); display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
.sf-h2 small { font-size: 14px; font-weight: 600; color: var(--muted); }
.sf-hint { border: 1.5px dashed var(--border); border-radius: 16px; padding: 14px 16px; color: var(--muted); font-size: 14px; background: var(--surface); }

.sf-announce { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 16px; background: #0B1E3D; color: #fff; font-weight: 700; font-size: 14.5px; }
.sf-announce-ico { width: 30px; height: 30px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--sf-accent); color: var(--sf-ink); }

/* Pastilles de catégories (maquette) : fond gris doux sans bordure, la catégorie active à la couleur principale */
.sf-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.sf-chip { min-height: 38px; padding: 0 18px; border-radius: 999px; border: 0; background: var(--grey-2); background: color-mix(in srgb, var(--text) 7%, transparent); box-shadow: none; color: var(--text-2); font: 700 13.5px var(--font); white-space: nowrap; cursor: pointer; transition: background-color .18s, color .18s, transform .18s; }
.sf-chip:hover { background: color-mix(in srgb, var(--text) 12%, transparent); color: var(--text); }
.sf-chip.is-on, .sf-chip.is-on:hover { background: var(--sf-accent); color: var(--sf-ink); }
.sf-chip:active { transform: scale(.97); }
.sf-h2.sf-cat-h { font-size: calc(17px * var(--sf-scale, 1)); margin-bottom: 10px; color: var(--text); }
.sf-chip:focus-visible, .sf-btn:focus-visible { outline: 3px solid #1F7CF6; outline-offset: 2px; }

/* 2 ou 3 produits à la une : cartes côte à côte, image au-dessus du texte */
.sf-featured-row { display: grid; gap: 14px; }
.sf-featured-row.is-multi { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
.sf-featured-row.is-multi .sf-featured { grid-template-columns: minmax(0, 1fr); align-content: start; gap: 14px; }
.sf-featured-row.is-multi .sf-featured-name { font-size: 20px; }
.sf-featured-row.is-multi .sf-featured-desc { display: none; }
.sf-featured { display: grid; grid-template-columns: minmax(0, 340px) minmax(0, 1fr); gap: 24px; align-items: center; padding: 18px; border-radius: 24px; background: var(--brand-mlt); }
.sf-featured-img { display: block; aspect-ratio: 1 / 1; border-radius: 18px; overflow: hidden; background: var(--surface); }
.sf-featured-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .5s cubic-bezier(.22,1,.36,1); }
.sf-featured:hover .sf-featured-img img { transform: scale(1.04); }
.sf-featured-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--brand); }
.sf-kicker { font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--brand-dk); }
.sf-featured-name { margin: 6px 0 8px; font-size: 26px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
.sf-featured-price { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; }
.sf-featured-price strong { font-size: 22px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; white-space: nowrap; }
.sf-featured-price s { color: var(--muted); white-space: nowrap; }
.sf-featured-desc { margin: 10px 0 16px; color: var(--muted); font-size: 14.5px; max-width: 60ch; }
.sf-btn { display: inline-flex; align-items: center; gap: 8px; min-height: 46px; padding: 0 20px; border-radius: 999px; background: var(--sf-accent); color: var(--sf-ink); font-weight: 800; text-decoration: none; transition: transform .18s, box-shadow .18s; }
.sf-btn:hover { color: var(--sf-ink); transform: translateY(-1px); box-shadow: 0 8px 20px rgba(11,30,61,.2); }

.sf-reviews { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
.sf-review { background: var(--surface); border-radius: 20px; box-shadow: 0 0 0 1px var(--border); padding: 16px 18px; }
.sf-review p { margin: 8px 0 10px; color: var(--text); font-size: 14.5px; overflow-wrap: anywhere; }
.sf-review-meta { font-size: 13px; color: var(--muted); }
.sf-stars .star-ico.is-filled { color: #F59E0B; }
.sf-stars .star-ico.is-empty { color: var(--border); }

.sf-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
.sf-info { display: flex; gap: 12px; align-items: flex-start; padding: 14px 16px; border-radius: 18px; background: var(--surface); box-shadow: 0 0 0 1px var(--border); }
.sf-info strong { display: block; color: var(--text); font-size: 14.5px; }
.sf-info span:not(.sf-info-ico) { font-size: 13.5px; color: var(--muted); }
.sf-info-ico { width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--brand-mlt); color: var(--brand-dk); }

.sf-contact { display: flex; justify-content: space-between; align-items: center; gap: 18px 32px; flex-wrap: wrap; padding: 22px 24px; border-radius: 24px; background: #0B1E3D; color: #C9D6EA; border-bottom: 6px solid var(--sf-accent); }
.sf-contact p { margin: 0; max-width: 48ch; }
.sf-contact-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
.sf-contact-list li { display: flex; align-items: center; gap: 10px; color: #fff; font-weight: 600; flex-wrap: wrap; }
.sf-contact-link { color: #7DB4FF; font-weight: 800; text-underline-offset: 3px; }

/* Produits : colonnes, carrousel, forme des cartes */
@media (min-width: 1101px) {
    .sf-grid:not(.list-view):not(.sf-carousel) { grid-template-columns: repeat(var(--sf-cols, 4), minmax(0, 1fr)); }
}
.sf-grid.sf-carousel { display: flex !important; overflow-x: auto; scroll-snap-type: x mandatory; gap: 14px; padding: 4px 2px 12px; scrollbar-width: thin; }
.sf-grid.sf-carousel .amz-card { flex: 0 0 260px; scroll-snap-align: start; }
.sf-grid.sf-carousel .amz-filtered-empty { flex: 0 0 100%; }
.sf-cards-square .amz-card, .sf-cards-square .sf-featured, .sf-cards-square .sf-featured-img,
.sf-cards-square .sf-review, .sf-cards-square .sf-info { border-radius: 2px !important; }

@media (max-width: 960px) {
    .sf-wrap { padding: 14px 14px 0; }
    .sf-slim-head { padding: 14px 14px 0; }
}
@media (max-width: 760px) {
    .sf-featured { grid-template-columns: minmax(0, 1fr); }
    .sf-featured-name { font-size: 22px; }
    .sf-reviews { grid-template-columns: minmax(0, 1fr); }
    .sf-grid.sf-carousel .amz-card { flex-basis: 72%; }
}
/* ══ Éditeur de boutique : style choisi (variables posées sur :root par la page) ══ */
.sf-h2, .sf-h2b { color: var(--sf-title, var(--text)); font-family: var(--font); }
.sf-h2b { margin: 0 0 12px; font-size: calc(22px * var(--sf-scale, 1)); font-weight: 800; letter-spacing: -.02em; text-wrap: balance; }
.sf-h2 { font-size: calc(20px * var(--sf-scale, 1)); }
.amz-card-name { font-size: calc(14px * var(--sf-scale, 1)) !important; }
.amz-price-main { font-size: calc(17px * var(--sf-scale, 1)) !important; }
.sf-btn, .amz-btn-order:not(.out) { background: var(--sf-btn-bg) !important; color: var(--sf-btn-fg) !important; box-shadow: var(--sf-btn-sh) !important; border-radius: var(--sf-btn-r, 999px) !important; border: 0 !important; }
.sf-btn:hover, .amz-btn-order:not(.out):hover { filter: brightness(1.06); transform: translateY(-1px); }
.sf-btn.is-off { background: var(--grey-2) !important; color: var(--muted) !important; box-shadow: none !important; cursor: default; transform: none; }
.amz-btn-order.out { border-radius: var(--sf-btn-r, 999px) !important; }
.sf-btn-alt { margin-top: 14px; }
.sf-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.sf-nego, .amz-nego { font-weight: 800; font-size: 13.5px; color: var(--sf-title); text-decoration: none; }
.amz-nego { display: block; text-align: center; margin-top: 8px; }
.sf-nego:hover, .amz-nego:hover { text-decoration: underline; }
.sf-vacation { display: flex; align-items: center; gap: 12px; padding: 12px max(16px, env(safe-area-inset-left, 0px)); background: #F59E0B; color: #16162A; font-size: 14px; font-weight: 600; justify-content: center; text-align: left; }
.sf-vacation svg { flex-shrink: 0; }

/* Page d'une catégorie */
.sf-cathead { display: flex; align-items: flex-end; justify-content: space-between; gap: 14px 24px; flex-wrap: wrap; padding: 18px 20px; border-radius: 22px; background: var(--surface); box-shadow: 0 0 0 1px var(--border); }
.sf-crumb { display: flex; gap: 6px; align-items: center; font-size: 13px; color: var(--muted); }
.sf-crumb a { color: var(--sf-title); font-weight: 700; text-decoration: none; }
.sf-crumb a:hover { text-decoration: underline; }
.sf-cat-title { margin: 4px 0 2px; font-size: calc(28px * var(--sf-scale, 1)); font-weight: 800; letter-spacing: -.02em; color: var(--text); text-wrap: balance; }
.sf-cat-count { font-size: 14px; color: var(--muted); font-variant-numeric: tabular-nums; }
.sf-cat-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.sf-cat-btn { display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 0 14px; border-radius: var(--sf-btn-r, 999px); border: 0; background: var(--grey-2); color: var(--text); font: 700 13px var(--font); text-decoration: none; cursor: pointer; }
.sf-cat-btn:hover { box-shadow: inset 0 0 0 1.5px var(--sf-accent); color: var(--text); }
.sf-cat-btn.is-wa { background: #15803D; color: #fff; }
.sf-cat-btn.is-wa:hover { color: #fff; box-shadow: none; filter: brightness(1.08); }
.sf-cat-n { font-size: 11.5px; color: var(--muted); font-variant-numeric: tabular-nums; margin-left: 4px; }
a.amz-filter-item { text-decoration: none; }
a.sf-chip { display: inline-flex; align-items: center; text-decoration: none; }
.sf-sel-more { display: inline-flex; margin-top: 12px; font-weight: 800; color: var(--sf-title); text-decoration: none; }
.sf-sel-more:hover { text-decoration: underline; }

/* Texte libre, image et texte, histoire */
.sf-text { max-width: 720px; margin: 0 auto; text-align: center; padding: 10px 0; }
.sf-text p, .sf-imgtext p, .sf-story p { margin: 0; color: var(--text-2); font-size: calc(15.5px * var(--sf-scale, 1)); line-height: 1.65; white-space: pre-line; overflow-wrap: anywhere; }
.sf-imgtext { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 28px; align-items: center; }
.sf-imgtext.is-right .sf-imgtext-img { order: 2; }
.sf-imgtext-img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 22px; box-shadow: 0 18px 36px rgba(11,30,61,.16); transform: perspective(900px) rotateY(6deg); transition: transform .5s cubic-bezier(.22,1,.36,1); }
.sf-imgtext.is-right .sf-imgtext-img { transform: perspective(900px) rotateY(-6deg); }
.sf-imgtext:hover .sf-imgtext-img { transform: none; }
.sf-imgtext-ph { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; background: var(--grey-2); color: var(--muted); font-weight: 700; box-shadow: none; border: 2px dashed var(--border); }
.sf-story { display: grid; grid-template-columns: 120px minmax(0, 1fr); gap: 22px; align-items: center; padding: 22px; border-radius: 24px; background: var(--grey-2); }
.sf-story img { width: 120px; height: 120px; border-radius: 26px; object-fit: cover; transform: perspective(500px) rotateY(14deg) rotateX(5deg); box-shadow: 12px 16px 26px rgba(11,30,61,.2); }

/* Vidéo */
.sf-video { position: relative; display: flex; align-items: center; justify-content: center; width: 100%; max-width: 900px; margin: 0 auto; aspect-ratio: 16 / 9; border: 0; padding: 0; border-radius: 22px; overflow: hidden; background: #0B1E3D center / cover no-repeat; cursor: pointer; text-decoration: none; }
.sf-video::before { content: ''; position: absolute; inset: 0; background: rgba(11,30,61,.35); }
.sf-video-play { position: relative; width: 76px; height: 76px; border-radius: 999px; background: var(--sf-accent); color: var(--sf-ink); display: flex; align-items: center; justify-content: center; box-shadow: 0 12px 30px rgba(0,0,0,.35); transition: transform .25s cubic-bezier(.34,1.56,.64,1); }
.sf-video:hover .sf-video-play { transform: scale(1.08); }
.sf-video-cap { position: absolute; left: 16px; bottom: 14px; color: #fff; font-weight: 800; font-size: 14px; }
.sf-video-link { background: linear-gradient(135deg, #0B1E3D, #132B52); }
iframe.sf-video { cursor: auto; }

/* Galerie */
.sf-gallery { display: grid; gap: 8px; grid-template-columns: 2fr 1fr 1fr; grid-auto-rows: 140px; }
.sf-gallery-cell { border: 0; padding: 0; border-radius: 16px; overflow: hidden; cursor: zoom-in; background: var(--grey-2); }
.sf-gallery-cell img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .4s cubic-bezier(.22,1,.36,1); }
.sf-gallery-cell:hover img { transform: scale(1.05); }
.sf-gallery .sf-gallery-cell:first-child { grid-row: span 2; }
.sf-gallery.n1 { grid-template-columns: minmax(0, 1fr); grid-auto-rows: 320px; }
.sf-gallery.n1 .sf-gallery-cell:first-child, .sf-gallery.n2 .sf-gallery-cell:first-child { grid-row: auto; }
.sf-gallery.n2 { grid-template-columns: 1fr 1fr; grid-auto-rows: 260px; }

/* Compte à rebours */
.sf-countdown { display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap; padding: 22px 24px; border-radius: 24px; background: var(--sf-accent); color: var(--sf-ink); }
.sf-countdown-title { margin: 0; font-size: calc(24px * var(--sf-scale, 1)); font-weight: 800; letter-spacing: -.02em; color: inherit; }
.sf-countdown p { margin: 4px 0 0; opacity: .9; }
.sf-cd { display: flex; gap: 10px; }
.sf-cd-cell { min-width: 76px; padding: 10px 8px; border-radius: 16px; display: flex; flex-direction: column; align-items: center; background: rgba(0,0,0,.2); transform: perspective(500px) rotateX(10deg); box-shadow: 0 10px 0 rgba(0,0,0,.16); }
.sf-cd-cell b { font-size: 30px; font-weight: 800; line-height: 1.1; font-variant-numeric: tabular-nums; }
.sf-cd-cell span { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; opacity: .85; }

/* Questions fréquentes */
.sf-faq { display: flex; flex-direction: column; gap: 8px; max-width: 860px; }
.sf-faq details { border-radius: 16px; background: var(--surface); box-shadow: 0 0 0 1px var(--border); padding: 14px 16px; }
.sf-faq summary { font-weight: 800; cursor: pointer; color: var(--text); list-style-position: inside; }
.sf-faq p { margin: 8px 0 0; color: var(--text-2); white-space: pre-line; }

/* Adresse et horaires */
.sf-hours { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 22px; align-items: center; }
.sf-iso { position: relative; height: 200px; border-radius: 22px; overflow: hidden; background: var(--brand-mlt, #EAF2FF); perspective: 700px; }
.sf-iso-pl { position: absolute; left: 50%; top: 50%; width: 360px; height: 260px; margin: -130px 0 0 -180px; transform: rotateX(55deg) rotateZ(-28deg); background: repeating-linear-gradient(0deg, transparent 0 38px, rgba(255,255,255,.9) 38px 44px), repeating-linear-gradient(90deg, rgba(31,124,246,.18) 0 52px, rgba(255,255,255,.9) 52px 58px); }
.sf-pin { position: absolute; left: 50%; top: 46%; transform: translate(-50%, -100%); animation: sfBounce 1.6s cubic-bezier(.22,1,.36,1) infinite; }
@keyframes sfBounce { 0%, 100% { transform: translate(-50%, -100%); } 50% { transform: translate(-50%, -125%); } }
.sf-hours-txt { display: flex; flex-direction: column; gap: 6px; align-items: flex-start; }
.sf-hours-txt strong { color: var(--text); font-size: 15.5px; }
.sf-hours-txt span { color: var(--text-2); }
.sf-open { display: inline-flex; padding: 4px 12px; border-radius: 999px; font-size: 12.5px; font-weight: 800; background: var(--grey-2); color: var(--text-2) !important; }
.sf-open.is-open { background: #E7F6EC; color: #15803D !important; }

/* Réseaux sociaux */
.sf-social { text-align: center; }
.sf-social-row { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; }
.sf-social-btn { min-height: 44px; display: inline-flex; align-items: center; padding: 0 20px; border-radius: var(--sf-btn-r, 999px); background: var(--c); color: #fff; font-weight: 800; text-decoration: none; transition: transform .2s; }
.sf-social-btn:hover { color: #fff; transform: translateY(-2px); }

/* Sélection de produits */
.sf-sel { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.sf-sel-card { display: flex; flex-direction: column; gap: 6px; padding: 10px 10px 14px; border-radius: 20px; background: var(--surface); box-shadow: 0 0 0 1px var(--border); min-width: 0; }
.sf-sel-img { display: flex; align-items: center; justify-content: center; aspect-ratio: 1; border-radius: 14px; overflow: hidden; background: var(--grey-2); color: var(--muted); }
.sf-sel-img img { width: 100%; height: 100%; object-fit: cover; }
.sf-sel-name { font-weight: 700; color: var(--text); text-decoration: none; font-size: calc(14px * var(--sf-scale, 1)); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sf-sel-price { display: flex; gap: 8px; align-items: baseline; flex-wrap: wrap; }
.sf-sel-price strong { color: var(--text); font-variant-numeric: tabular-nums; }
.sf-sel-price s { color: var(--muted); font-size: 13px; }
.sf-sel-card .sf-btn { justify-content: center; min-height: 40px; font-size: 13.5px; margin-top: auto; }
.sf-cards-square .sf-sel-card, .sf-cards-square .sf-faq details, .sf-cards-square .sf-story { border-radius: 2px !important; }

/* Message de bienvenue */
.sf-welcome { position: fixed; inset: 0; z-index: 9000; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(11,30,61,.5); animation: sfFade .25s both; }
.sf-welcome[hidden] { display: none; }
@keyframes sfFade { from { opacity: 0; } }
.sf-welcome-box { position: relative; width: min(380px, 100%); padding: 26px 24px 22px; border-radius: 26px; background: #fff; color: #16162A; text-align: center; font-family: var(--font); box-shadow: 0 30px 60px rgba(0,0,0,.3); animation: sfPop .38s cubic-bezier(.34,1.56,.64,1) both; }
@keyframes sfPop { from { opacity: 0; transform: scale(.92) translateY(8px); } }
.sf-welcome-box img { width: 64px; height: 64px; border-radius: 18px; object-fit: cover; }
.sf-welcome-box h2 { margin: 10px 0 6px; font-size: 22px; font-weight: 800; }
.sf-welcome-box p { margin: 0; color: #45455E; }
.sf-welcome-code { margin: 14px auto 6px; padding: 12px; border-radius: 14px; border: 2px dashed var(--sf-accent); font-size: 22px; font-weight: 800; letter-spacing: .08em; font-variant-numeric: tabular-nums; }
.sf-welcome-off { font-size: 13.5px; font-weight: 700; margin-bottom: 14px !important; }
.sf-welcome-box .sf-btn { width: 100%; justify-content: center; margin-top: 12px; }
.sf-welcome-x { position: absolute; top: 12px; right: 12px; width: 38px; height: 38px; border-radius: 999px; border: 0; background: #F1F5FB; color: #16162A; cursor: pointer; display: flex; align-items: center; justify-content: center; }

/* En-tête « couverture » (Faire grandir ma vitrine → Couverture et logo) */
@include('public.shops._cover-css')
/* Mode WhatsApp (Faire grandir ma vitrine → Conversion) : la commande part aussi sur WhatsApp */
.sf-btn.is-wa, .amz-btn-order.is-wa:not(.out) { background: #15803D !important; color: #fff !important; box-shadow: none !important; }
/* Lien d'aperçu (48 h) : bandeau en haut, avis en bas */
.sf-plink { display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap; padding: 10px max(16px, env(safe-area-inset-left, 0px)); background: #0B1E3D; color: #fff; font-size: 14px; font-weight: 600; text-align: center; }
.sf-plink b { color: #FCD34D; }
.sf-fb { max-width: 640px; margin: 28px auto 8px; padding: 22px; border-radius: 24px; background: var(--surface); box-shadow: 0 0 0 1px var(--border); display: flex; flex-direction: column; gap: 10px; }
.sf-fb h2 { margin: 0; font-size: 19px; font-weight: 800; color: var(--text); }
.sf-fb p { margin: 0; color: var(--text-2); font-size: 14px; }
.sf-fb input, .sf-fb textarea { width: 100%; border: 0; border-radius: 14px; padding: 12px 14px; background: var(--grey-2, #EEF2F8); color: var(--text); font: 500 15px var(--font); box-shadow: inset 0 0 0 1.5px var(--border); }
.sf-fb textarea { min-height: 96px; resize: vertical; }
.sf-fb input:focus, .sf-fb textarea:focus { outline: 0; box-shadow: inset 0 0 0 2px #1F7CF6; }
.sf-fb-err { color: #B91C1C; font-size: 13px; font-weight: 700; }
.sf-fb-ok { padding: 12px 14px; border-radius: 14px; background: #E7F6EC; color: #15803D; font-weight: 700; }
.sf-fb .sf-btn { align-self: flex-start; border: 0; cursor: pointer; font-family: var(--font); }
.sf-sr { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
/* Collections (une rangée de produits par collection) */
.sf-col + .sf-col { margin-top: 22px; }
.sf-col-hd { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.sf-col-hd .sf-sel-more { margin: 0; }
.sf-stock-note { font-size: 12.5px; font-weight: 800; color: #92400E; }
/* Aperçu de l'éditeur : bloc survolé / sélectionné */
.sf-preview [data-sf-block] { cursor: pointer; outline: 2px solid transparent; outline-offset: 2px; transition: outline-color .16s; border-radius: 8px; }
.sf-preview [data-sf-block]:hover { outline-color: rgba(31,124,246,.45); }
.sf-preview [data-sf-block].sf-is-selected { outline: 3px solid #1F7CF6; }
.sf-preview [data-sf-block]:focus-visible { outline: 3px solid #1F7CF6; outline-offset: 2px; }
.sf-preview [data-sf-block].sf-is-dragging { opacity: .4; }
/* Étiquette du bloc (en haut à gauche) et barre d'outils (en haut à droite) ; --ed-ui compense le zoom de l'aperçu */
.sf-ed-tag, .sf-ed-bar { position: absolute; z-index: 8000; transform-origin: 0 0; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
.sf-ed-tag { display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 11px; border-radius: 999px; background: #1F7CF6; color: #fff; font-size: 12px; font-weight: 800; white-space: nowrap; pointer-events: none; box-shadow: 0 6px 14px rgba(11,30,61,.25); transform: scale(var(--ed-ui, 1)); }
.sf-ed-bar { display: flex; gap: 2px; padding: 4px; border-radius: 999px; background: #0B1E3D; box-shadow: 0 10px 24px rgba(11,30,61,.35); transform: scale(var(--ed-ui, 1)) translateX(-100%); }
.sf-ed-tag[hidden], .sf-ed-bar[hidden], .sf-ed-line[hidden] { display: none; }
.sf-ed-bar button, .sf-ed-grip { width: 34px; height: 34px; border: 0; border-radius: 999px; background: transparent; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background-color .18s; }
.sf-ed-grip { cursor: grab; }
.sf-ed-bar button:hover, .sf-ed-grip:hover { background: #132B52; }
.sf-ed-bar button[data-ed-act="del"]:hover { background: #B91C1C; }
.sf-ed-bar button:disabled { opacity: .35; cursor: default; background: transparent; }
.sf-ed-bar button:focus-visible { outline: 3px solid #7DB4FF; outline-offset: 1px; }
/* Point d'insertion pendant un glisser-déposer */
.sf-ed-line { position: absolute; z-index: 8001; height: 4px; border-radius: 4px; background: #F59E0B; box-shadow: 0 0 0 3px rgba(245,158,11,.25); pointer-events: none; }
.sf-ed-end { order: 9999; margin-top: 18px; padding: 22px 16px; border: 2px dashed var(--border, #C3CBDA); border-radius: 18px; text-align: center; color: var(--muted, #62627C); font: 700 14px var(--font, sans-serif); transition: background-color .18s, border-color .18s, color .18s; }
.sf-ed-end.is-over { background: #FEF3E0; border-color: #F59E0B; color: #92400E; }

@media (max-width: 760px) {
    .sf-imgtext, .sf-hours { grid-template-columns: minmax(0, 1fr); }
    .sf-imgtext.is-right .sf-imgtext-img { order: 0; }
    .sf-imgtext-img, .sf-imgtext.is-right .sf-imgtext-img { transform: none; }
    .sf-story { grid-template-columns: 76px minmax(0, 1fr); gap: 14px; padding: 16px; }
    .sf-story img { width: 76px; height: 76px; border-radius: 18px; }
    .sf-gallery { grid-template-columns: 1fr 1fr; grid-auto-rows: 120px; }
    .sf-sel { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sf-cd-cell { min-width: 64px; }
    .sf-cd-cell b { font-size: 24px; }
}
@media (prefers-reduced-motion: reduce) {
    .sf-featured:hover .sf-featured-img img, .sf-btn:hover { transform: none; }
    .sf-pin, .sf-welcome, .sf-welcome-box { animation: none; }
    .sf-imgtext-img, .sf-imgtext.is-right .sf-imgtext-img, .sf-story img, .sf-cd-cell { transform: none; }
}
</style>
@endpush

@section('content')
@php
    $devise = $shop->currency ?? 'GNF';
    // Réglages de la vitrine (voir App\Support\Storefront et PublicShopController@products)
    $sfProdIndex = collect($sf['blocks'])->search(fn ($b) => $b['type'] === 'products');
    $sfProdOn    = $sf['blocks'][$sfProdIndex]['on'];
    $sfInk   = \App\Support\Storefront::inkFor($sfAccent);
    $sfSet   = $sf['settings'];
    $sfCats  = \App\Support\Storefront::categoryLinks($shop);
    $sfHome  = \App\Support\Storefront::publicUrl($shop);
    $sfStatus = \App\Support\Storefront::openStatus($sf); // « Ouvert maintenant » / « Fermé · ouvre à … » / « En pause »
    $sfStyle = $sf['style'];
    $sfBg    = \App\Support\Storefront::background($sfStyle['bg']);
    $sfFont  = \App\Support\Storefront::FONTS[$sfStyle['font']];
    $sfSolid = $sfStyle['fill'] === 'solid';
    // Bouton « contour » : couleur principale si lisible sur le fond, sinon l'encre du fond
    $sfOutlineInk = \App\Support\Storefront::contrast($sfAccent, $sfBg[0]) >= 3 ? $sfAccent : $sfBg[1];
@endphp
@if($sfFont[2])
@push('styles')
<link href="https://fonts.googleapis.com/css2?family={{ $sfFont[2] }}&display=swap" rel="stylesheet">
@endpush
@endif
{{-- Style choisi dans l'éditeur : appliqué à toute la page de la boutique --}}
<style>
:root {
    --sf-accent: {{ $sfAccent }}; --sf-ink: {{ $sfInk }};
    --sf-title: {{ \App\Support\Storefront::titleColor($sfAccent, $sfStyle['bg']) }};
    --sf-scale: {{ ['s' => '.93', 'm' => '1', 'l' => '1.12'][$sfStyle['size']] }};
    --sf-btn-r: {{ $sfStyle['btn'] === 'pill' ? '999px' : '8px' }};
    --sf-btn-bg: {{ $sfSolid ? $sfAccent : 'transparent' }};
    --sf-btn-fg: {{ $sfSolid ? $sfInk : $sfOutlineInk }};
    --sf-btn-sh: {{ $sfSolid ? 'none' : 'inset 0 0 0 2px ' . $sfOutlineInk }};
    --grey: {{ $sfBg[0] }}; --text: {{ $sfBg[1] }}; --text-2: {{ $sfBg[2] }}; --muted: {{ $sfBg[2] }};
    --border: {{ $sfBg[3] }}; --surface: {{ $sfBg[4] }}; --grey-2: {{ $sfBg[5] }}; --brand-mlt: {{ $sfBg[5] }};
    --font: {!! $sfFont[1] !!};
}
body { background: var(--grey){{ $sfStyle['bg'] === 'texture' ? ' radial-gradient(rgba(11,30,61,.07) 1.2px, transparent 1.2px) 0 0 / 16px 16px' : '' }}; font-family: var(--font); }
</style>
@if(\App\Support\MetaPixel::activeFor($shop, $isPreview))
@push('scripts')
{{-- Pixel Meta (Facebook) du vendeur : visite de sa vitrine --}}
@include('partials.meta-pixel', ['pixelId' => $shop->meta_pixel_id])
@endpush
@endif
@if($isPreview)
{{-- Aperçu dans l'éditeur de vitrine : on masque les fenêtres du site qui gêneraient la lecture --}}
<style>#pwaModal, #pushNotifBanner { display: none !important; }</style>
<script>document.documentElement.classList.add('sf-preview');</script>
@endif

{{-- ══ Modèles de boutique Shopio : barre visible du seul vendeur ══ --}}
@if($isOwnerBar ?? false)
@include('public.shops.models._owner-bar', ['current' => 'standard'])
@endif

{{-- ══ NAVBAR ══ --}}
<nav class="amz-nav">
    <a href="{{ url('/') }}" class="nav-logo-wrap">
        <img src="/images/shopio-logo-192.png" alt="Shopio" class="nav-logo-img" width="36" height="36">
        <span class="nav-brand-name">Shopio</span>
    </a>
    {{-- Client connecté : retour vers son espace (comme l'ancienne page boutique client) --}}
    @if(auth()->check() && auth()->user()->role === 'client')
    <a href="{{ route('client.dashboard') }}" class="amz-back-home">{!! \App\Support\IconLibrary::svg('home', '', 14) !!} <span class="txt">Mon espace</span></a>
    @else
    <a href="{{ url('/') }}" class="amz-back-home">{!! \App\Support\IconLibrary::svg('home', '', 14) !!} <span class="txt">Accueil</span></a>
    @endif
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
            <a class="amz-filter-item {{ $activeCategory ? '' : 'active' }}" href="{{ $sfHome }}">
                <input type="radio" name="cat_m" id="mcat-all" {{ $activeCategory ? '' : 'checked' }} tabindex="-1">
                <label for="mcat-all">Tous les produits</label>
            </a>
            @foreach($sfCats as $c)
            <a class="amz-filter-item {{ ($activeCategory['slug'] ?? '') === $c['slug'] ? 'active' : '' }}" href="{{ $c['url'] }}">
                <input type="radio" name="cat_m" id="mcat-{{ $c['slug'] }}" {{ ($activeCategory['slug'] ?? '') === $c['slug'] ? 'checked' : '' }} tabindex="-1">
                <label for="mcat-{{ $c['slug'] }}">{{ $c['name'] }} <span class="sf-cat-n">{{ $c['count'] }}</span></label>
            </a>
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

{{-- ══ MODE VACANCES : boutique visible, commandes en pause ══ --}}
@if($sfSet['vacation'])
<div class="sf-vacation" role="status">
    {!! \App\Support\IconLibrary::svg('clock', '', 18) !!}
    <span><strong>Boutique en pause</strong>@if($sfSet['vacation_msg'] !== '') · {{ $sfSet['vacation_msg'] }}@endif. Vous pouvez parcourir les produits ; les commandes reprendront au retour du vendeur.</span>
</div>
@endif

{{-- ══ LIEN D'APERÇU (48 h) ══ --}}
@if($previewLink)
<div class="sf-plink" role="note"><span><b>Aperçu</b> de la vitrine de {{ $shop->name }} · pas encore visible des clients · lien valable jusqu’au {{ $previewLink['expires']->timezone('Africa/Conakry')->translatedFormat('j F à H:i') }}</span></div>
@endif

{{-- ══ BANNIÈRE BOUTIQUE ══ --}}
{{-- Couverture réglée dans « Faire grandir ma vitrine », sinon l'en-tête d'origine (grande, compacte, sans) --}}
@if($sf['cover']['on'] && $coverSlides)
@include('public.shops._storefront-cover')
@elseif($sf['banner'] === 'none')
<div class="sf-slim-head" data-sf-block="__banner">
    <span class="sf-slim-name">{{ $shop->name }}</span>
    @if($shop->is_approved)<span class="sf-slim-badge">{!! \App\Support\IconLibrary::svg('check', '', 11) !!} Boutique vérifiée</span>@endif
    @if($sfStatus)<span class="sf-status is-{{ $sfStatus[1] }}">{{ $sfStatus[0] }}</span>@endif
    @if($proofs['rating'] && $reviewAvg)<span class="sf-slim-meta">{!! \App\Support\IconLibrary::svg('star', '', 12) !!} {{ number_format($reviewAvg, 1, ',', ' ') }} ({{ $reviewCount }} avis)</span>@endif
</div>
@else
<div class="shop-banner sf-banner-{{ $sf['banner'] }}" data-sf-block="__banner">
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
            @if($sfStatus)<span class="sf-status is-{{ $sfStatus[1] }}">{{ $sfStatus[0] }}</span>@endif
            @if($proofs['rating'] && $reviewAvg)<span class="shop-banner-rating">{!! \App\Support\IconLibrary::svg('star', '', 12) !!} {{ number_format($reviewAvg, 1) }} <span style="opacity:.8">({{ $reviewCount }} avis)</span></span>@endif
            @if($proofs['response'])<span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('message', '', 12) !!} Répond en moins d’1 h</span>@endif
            @if($proofs['delivered'])<span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('check', '', 12) !!} {{ $deliveredCount }} commande{{ $deliveredCount > 1 ? 's' : '' }} livrée{{ $deliveredCount > 1 ? 's' : '' }}</span>@endif
            @if($shop->type)<span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('tag', '', 12) !!} {{ $shop->type }}</span>@endif
            @if($shop->address)<span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('map-pin', '', 12) !!} {{ Str::limit($shop->address, 28) }}</span>@endif
            <span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('package', '', 12) !!} {{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}</span>
            @if($shop->phone)<span class="shop-banner-chip">{!! \App\Support\IconLibrary::svg('phone', '', 12) !!} {{ $shop->phone }}</span>@endif
        </div>
    </div>
</div>
@endif

{{-- ══ PAGE D'UNE CATÉGORIE : fil d'Ariane, nombre de produits, lien à partager ══ --}}
@if($activeCategory)
@php
    $catShare = \App\Support\TrafficSource::tag($activeCategory['url'], 'whatsapp');
    $catWa = 'Découvrez « ' . $activeCategory['name'] . ' » chez ' . $shop->name . ' sur Shopio : ' . $catShare;
@endphp
<div class="sf-wrap">
    <div class="sf-cathead">
        <div style="min-width:0">
            <nav class="sf-crumb" aria-label="Fil d'Ariane"><a href="{{ $sfHome }}">{{ $shop->name }}</a><span aria-hidden="true">/</span><span>Catégorie</span></nav>
            <h1 class="sf-cat-title">{{ $activeCategory['name'] }}</h1>
            <span class="sf-cat-count">{{ $activeCategory['count'] }} produit{{ $activeCategory['count'] > 1 ? 's' : '' }}</span>
        </div>
        <div class="sf-cat-actions">
            <button type="button" class="sf-cat-btn" data-copy-link="{{ $activeCategory['url'] }}">{!! \App\Support\IconLibrary::svg('globe', '', 15) !!} <span>Copier le lien</span></button>
            <a class="sf-cat-btn is-wa" href="https://wa.me/?text={{ rawurlencode($catWa) }}" target="_blank" rel="noopener">{!! \App\Support\IconLibrary::svg('message', '', 15) !!} WhatsApp</a>
            <a class="sf-cat-btn" href="{{ $sfHome }}">Toute la boutique</a>
        </div>
    </div>
</div>
@endif

{{-- ══ PAGE D'UNE COLLECTION (Faire grandir ma vitrine → Personnalisation) ══ --}}
@if($activeCollection)
@php $colWa = 'Découvrez « ' . $activeCollection['name'] . ' » chez ' . $shop->name . ' sur Shopio : ' . \App\Support\TrafficSource::tag($activeCollection['url'], 'whatsapp'); @endphp
<div class="sf-wrap">
    <div class="sf-cathead">
        <div style="min-width:0">
            <nav class="sf-crumb" aria-label="Fil d'Ariane"><a href="{{ $sfHome }}">{{ $shop->name }}</a><span aria-hidden="true">/</span><span>Collection</span></nav>
            <h1 class="sf-cat-title">{{ $activeCollection['name'] }}</h1>
            <span class="sf-cat-count">{{ $activeCollection['count'] }} produit{{ $activeCollection['count'] > 1 ? 's' : '' }}</span>
        </div>
        <div class="sf-cat-actions">
            <button type="button" class="sf-cat-btn" data-copy-link="{{ $activeCollection['url'] }}">{!! \App\Support\IconLibrary::svg('globe', '', 15) !!} <span>Copier le lien</span></button>
            <a class="sf-cat-btn is-wa" href="https://wa.me/?text={{ rawurlencode($colWa) }}" target="_blank" rel="noopener">{!! \App\Support\IconLibrary::svg('message', '', 15) !!} WhatsApp</a>
            <a class="sf-cat-btn" href="{{ $sfHome }}">Toute la boutique</a>
        </div>
    </div>
</div>
@endif

{{-- ══ BLOCS DE LA VITRINE (ordre et visibilité réglés dans l'éditeur de boutique) ══ --}}
<div class="sf-flow sf-cards-{{ $sf['cards'] }} sf-size-{{ $sfStyle['size'] }}" style="--sf-cols: {{ $sf['columns'] }}">
@include('public.shops._storefront-blocks')

{{-- ══ LAYOUT (section « Tous les produits ») ══ --}}
<div class="amz-layout" id="sfProducts" data-sf-block="products" style="order: {{ $sfProdIndex }};{{ $sfProdOn ? '' : ' display: none;' }}">

    {{-- Sidebar desktop --}}
    <aside class="amz-sidebar">
        <div class="amz-sidebar-title">Affiner les résultats</div>

        @if(!empty($categories))
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Catégorie</div>
            <a class="amz-filter-item {{ $activeCategory ? '' : 'active' }}" href="{{ $sfHome }}">
                <input type="radio" name="cat_d" id="dcat-all" {{ $activeCategory ? '' : 'checked' }} tabindex="-1">
                <label for="dcat-all">Tous les produits</label>
            </a>
            @foreach($sfCats as $c)
            <a class="amz-filter-item {{ ($activeCategory['slug'] ?? '') === $c['slug'] ? 'active' : '' }}" href="{{ $c['url'] }}">
                <input type="radio" name="cat_d" id="dcat-{{ $c['slug'] }}" {{ ($activeCategory['slug'] ?? '') === $c['slug'] ? 'checked' : '' }} tabindex="-1">
                <label for="dcat-{{ $c['slug'] }}">{{ $c['name'] }} <span class="sf-cat-n">{{ $c['count'] }}</span></label>
            </a>
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
                    <button class="amz-view-btn {{ $sf['layout'] !== 'list' ? 'active' : '' }}" id="btnGrid" onclick="setView('grid')" title="Grille">{!! \App\Support\IconLibrary::svg('grid', '', 14) !!}</button>
                    <button class="amz-view-btn {{ $sf['layout'] === 'list' ? 'active' : '' }}" id="btnList" onclick="setView('list')" title="Liste">{!! \App\Support\IconLibrary::svg('list', '', 14) !!}</button>
                </div>
            </div>
        </div>

        @if($products->hasPages())
        <div class="amz-page-hint" id="pageHint" role="status">
            {!! \App\Support\IconLibrary::svg('alert', '', 14) !!}
            Filtres appliqués aux {{ $products->count() }} produits de cette page (page {{ $products->currentPage() }} sur {{ $products->lastPage() }}).
        </div>
        @endif

        <div class="amz-grid sf-grid {{ $sf['layout'] === 'list' ? 'list-view' : '' }} {{ $sf['layout'] === 'carousel' ? 'sf-carousel' : '' }}" id="prodGrid">
            @forelse($products as $product)
            @php
                // Prix barré, stock restant, texte du bouton, négociation : réglages de l'éditeur de boutique
                $hasPromo = $sfSet['show_strike'] && $product->original_price && $product->original_price > $product->price;
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
                            @if($product->is_flash_active && $sfSet['show_strike'])
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
                        @elseif(!$sfSet['show_stock'])
                        @elseif($stockLow)<div class="amz-stock-low">{!! \App\Support\IconLibrary::svg('alert', '', 12) !!} Plus que {{ $stockVal }} en stock</div>
                        @else<div class="amz-stock-ok">{!! \App\Support\IconLibrary::svg('check', '', 12) !!} En stock</div>
                        @endif
                    @endif
                </div>

                <div class="amz-card-footer">
                    @auth
                        @if(Auth::user()->role === 'client')
                            @if(!$stockOut && !$sfSet['vacation'])
                            <a href="{{ route('client.orders.createFromProduct', $product) }}" class="amz-btn-order{{ $sfSet['wa_mode'] ? ' is-wa' : '' }}">{!! \App\Support\IconLibrary::svg($sfSet['wa_mode'] ? 'message' : 'cart', '', 14) !!} {{ $sfSet['wa_mode'] ? 'Commander sur WhatsApp' : $sfSet['btn_text'] }}</a>
                            @else
                            <div class="amz-btn-order out">Indisponible</div>
                            @endif
                            <a href="{{ route('client.messages.index', $product) }}" class="amz-btn-msg">{!! \App\Support\IconLibrary::svg('message', '', 13) !!} Poser une question</a>
                        @endif
                    @else
                        @if(!$stockOut && !$sfSet['vacation'])
                        <a href="{{ route('client.orders.createFromProduct', $product) }}" class="amz-btn-order{{ $sfSet['wa_mode'] ? ' is-wa' : '' }}">{!! \App\Support\IconLibrary::svg($sfSet['wa_mode'] ? 'message' : 'cart', '', 14) !!} {{ $sfSet['wa_mode'] ? 'Commander sur WhatsApp' : $sfSet['btn_text'] }}</a>
                        @else
                        <div class="amz-btn-order out">Indisponible</div>
                        @endif
                        @php $msgUrl = route('client.messages.index', $product); @endphp
                        <a href="{{ route('register', ['redirect' => $msgUrl, 'role' => 'client']) }}" class="amz-btn-msg">
                            {!! \App\Support\IconLibrary::svg('message', '', 13) !!} Poser une question
                        </a>
                    @endauth
                    @if($sfSet['show_nego'] && !$sfSet['vacation'] && !$stockOut)
                    @php $negoUrl = route('client.messages.index', $product) . '?offre=1'; @endphp
                    <a href="{{ auth()->check() ? $negoUrl : route('register', ['redirect' => $negoUrl, 'role' => 'client']) }}" class="amz-nego">Négocier le prix</a>
                    @endif
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
</div>{{-- /sf-flow --}}

@if($previewLink)
<section class="sf-wrap" aria-labelledby="sfFbTitle">
    <form class="sf-fb" method="POST" action="{{ route('storefront.preview-feedback', $previewLink['token']) }}">
        @csrf
        <h2 id="sfFbTitle">Votre avis sur cette vitrine</h2>
        <p>{{ $shop->name }} prépare sa boutique et aimerait savoir ce que vous en pensez avant de la publier.</p>
        @if(session('feedback_sent'))<div class="sf-fb-ok" role="status">Merci, votre avis a été envoyé à la boutique.</div>@endif
        <label class="sf-sr" for="sfFbName">Votre prénom (facultatif)</label>
        <input id="sfFbName" name="name" maxlength="40" placeholder="Votre prénom (facultatif)" value="{{ old('name') }}" autocomplete="given-name">
        <label class="sf-sr" for="sfFbMsg">Votre avis</label>
        <textarea id="sfFbMsg" name="message" maxlength="500" required placeholder="Ce qui vous plaît, ce qui manque, ce qui n’est pas clair…" aria-describedby="sfFbErr">{{ old('message') }}</textarea>
        @error('message')<span class="sf-fb-err" id="sfFbErr" role="alert">{{ $message }}</span>@enderror
        <button type="submit" class="sf-btn">Envoyer mon avis</button>
    </form>
</section>
@endif

{{-- ══ Message de bienvenue : une seule fois par visiteur, avec un vrai code promo de la boutique ══ --}}
@if($welcomePromo)
<div class="sf-welcome" id="sfWelcome" hidden>
    <div class="sf-welcome-box" role="dialog" aria-modal="true" aria-labelledby="sfWelcomeTitle">
        <button type="button" class="sf-welcome-x" data-welcome-close aria-label="Fermer">{!! \App\Support\IconLibrary::svg('x', '', 18) !!}</button>
        @if($shop->image)<img src="{{ \App\Services\ImageOptimizer::url($shop->image, 'thumb') ?? asset('storage/' . $shop->image) }}" alt="" width="64" height="64">@endif
        <h2 id="sfWelcomeTitle">Bienvenue chez {{ $shop->name }} !</h2>
        <p>Pour votre première commande, utilisez ce code :</p>
        <div class="sf-welcome-code">{{ $welcomePromo->code }}</div>
        <p class="sf-welcome-off">{{ $welcomePromo->type === 'percent' ? '−' . $welcomePromo->value . ' %' : '−' . number_format($welcomePromo->value, 0, ',', ' ') . ' ' . $devise }}@if($welcomePromo->min_purchase_amount) dès {{ number_format($welcomePromo->min_purchase_amount, 0, ',', ' ') }} {{ $devise }} d'achat @endif</p>
        <button type="button" class="sf-btn" data-welcome-close>Voir les produits</button>
    </div>
</div>
@endif

@push('scripts')
<script>
(function () {
    'use strict';
    /* Copier le lien d'une catégorie */
    document.querySelectorAll('[data-copy-link]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var label = btn.querySelector('span'), done = function () { if (label) { label.textContent = 'Lien copié'; setTimeout(function () { label.textContent = 'Copier le lien'; }, 2000); } };
            if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(btn.dataset.copyLink).then(done, function () { window.prompt('Copiez ce lien :', btn.dataset.copyLink); });
            else { window.prompt('Copiez ce lien :', btn.dataset.copyLink); }
        });
    });

    /* Couverture en diapos : une toutes les 3,8 s, pause au survol, points de navigation */
    document.querySelectorAll('[data-cover-carousel]').forEach(function (media) {
        var slides = media.querySelectorAll('.sf-cover-slide'), dots = media.querySelectorAll('[data-cover-dot]'), at = 0, paused = false;
        var show = function (i) {
            at = (i + slides.length) % slides.length;
            slides.forEach(function (s, k) { s.classList.toggle('is-on', k === at); });
            dots.forEach(function (d, k) { if (k === at) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current'); });
        };
        dots.forEach(function (d) { d.addEventListener('click', function (e) { e.stopPropagation(); show(+d.dataset.coverDot); }); });
        media.addEventListener('mouseenter', function () { paused = true; });
        media.addEventListener('mouseleave', function () { paused = false; });
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) setInterval(function () { if (!paused && !document.hidden) show(at + 1); }, 3800);
    });

    /* Clics par section (statistiques du vendeur) : pas dans l'aperçu ni sur le lien d'aperçu */
    @if(!$isPreview && !$previewLink)
    (function () {
        var url = @json(route('public.shops.click', $shop)), token = document.querySelector('meta[name="csrf-token"]');
        document.addEventListener('click', function (e) {
            var hit = e.target.closest('a, button');
            var blk = hit && hit.closest('[data-sf-block]');
            if (!blk || !navigator.sendBeacon) return;
            var fd = new FormData();
            fd.append('block', blk.dataset.sfBlock);
            if (token) fd.append('_token', token.content);
            navigator.sendBeacon(url, fd);
        });
    })();
    @endif

    /* Compte à rebours : jours, heures, minutes ; le bloc disparaît à la fin */
    document.querySelectorAll('.sf-countdown[data-end]').forEach(function (box) {
        var end = new Date(box.dataset.end).getTime();
        var cells = { j: box.querySelector('[data-cd="j"]'), h: box.querySelector('[data-cd="h"]'), m: box.querySelector('[data-cd="m"]') };
        var pad = function (n) { return String(n).padStart(2, '0'); };
        (function tick() {
            var left = Math.floor((end - Date.now()) / 60000);
            if (left < 0) { box.closest('.sf-wrap').hidden = true; return; }
            cells.j.textContent = pad(Math.floor(left / 1440));
            cells.h.textContent = pad(Math.floor(left % 1440 / 60));
            cells.m.textContent = pad(left % 60);
            setTimeout(tick, 30000);
        })();
    });

    /* Vidéo YouTube : le lecteur ne se charge qu'au clic (page plus légère) */
    document.querySelectorAll('.sf-video[data-yt]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var f = document.createElement('iframe');
            f.src = 'https://www.youtube-nocookie.com/embed/' + btn.dataset.yt + '?autoplay=1&rel=0';
            f.allow = 'autoplay; encrypted-media; picture-in-picture';
            f.allowFullscreen = true;
            f.title = btn.getAttribute('aria-label') || 'Vidéo';
            f.className = 'sf-video';
            btn.replaceWith(f);
        });
    });

    /* Message de bienvenue : une seule fois par boutique et par appareil */
    var welcome = document.getElementById('sfWelcome');
    var IS_PREVIEW = @json((bool) $isPreview);
    function openWelcome() { if (welcome) { welcome.hidden = false; welcome.querySelector('[data-welcome-close]').focus(); } }
    if (welcome) {
        welcome.addEventListener('click', function (e) { if (e.target === welcome || e.target.closest('[data-welcome-close]')) welcome.hidden = true; });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') welcome.hidden = true; });
        var key = 'shopio_welcome_{{ $shop->id }}';
        if (!IS_PREVIEW) {
            try { if (!localStorage.getItem(key)) { localStorage.setItem(key, '1'); setTimeout(openWelcome, 900); } } catch (e) {}
        }
    }

    /* Aperçu dans l'éditeur : la page se modifie directement.
       Survol → contour léger ; clic (ou Entrée) → bloc sélectionné, avec son étiquette et une barre d'outils
       (glisser, monter, descendre, dupliquer, supprimer) ; glisser un bloc sur un autre → trait ambre au point
       d'insertion ; zone de dépôt en bas de page ; raccourcis clavier transmis à l'éditeur. Les liens sont neutralisés. */
    if (IS_PREVIEW && window.parent !== window) {
        var ORIGIN = location.origin;
        var send = function (msg) { window.parent.postMessage(msg, ORIGIN); };
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var meta = {}, selected = null, selectedId = null, dragId = null, outer = null, drop = null;
        var ICO = {
            up: 'm6 15 6-6 6 6', down: 'm6 9 6 6 6-6', dup: 'M8 8h12v12H8zM4 16V4h12',
            del: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3', lock: 'M5 11h14v10H5zM8 11V7a4 4 0 0 1 8 0v4',
            hide: 'M3 3l18 18M10.6 6.1A9.7 9.7 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-3 3.6M6.6 6.6A17 17 0 0 0 2.5 12S6 18.5 12 18.5a9 9 0 0 0 4.4-1.1',
            drag: 'M9 5h.01M9 12h.01M9 19h.01M15 5h.01M15 12h.01M15 19h.01',
        };
        var ico = function (d, s) { return '<svg width="' + (s || 16) + '" height="' + (s || 16) + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>'; };
        var btn = function (act, label, d, off) { return '<button type="button" data-ed-act="' + act + '" aria-label="' + label + '" title="' + label + '"' + (off ? ' disabled' : '') + '>' + ico(d) + '</button>'; };
        var escH = function (v) { return String(v).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
        var byId = function (id) { return id ? document.querySelector('[data-sf-block="' + (window.CSS && CSS.escape ? CSS.escape(id) : id) + '"]') : null; };
        var make = function (cls) { var el = document.createElement('div'); el.className = cls; el.hidden = true; document.body.appendChild(el); return el; };
        var tag = make('sf-ed-tag'), bar = make('sf-ed-bar'), line = make('sf-ed-line');
        bar.setAttribute('role', 'toolbar'); bar.setAttribute('aria-label', 'Actions du bloc');
        var flow = document.querySelector('.sf-flow'), end = null;
        if (flow) {
            end = document.createElement('div'); end.className = 'sf-ed-end';
            end.textContent = 'Déposez un bloc ici pour l’ajouter en bas de la page';
            flow.appendChild(end);
        }

        /* Étiquette et barre d'outils suivent le bloc sélectionné (positions dans la page) */
        var place = function () {
            if (!selected || !selected.isConnected) { tag.hidden = true; bar.hidden = true; return; }
            var r = selected.getBoundingClientRect(), y = r.top + window.scrollY + 8, x = r.left + window.scrollX;
            tag.style.top = y + 'px'; tag.style.left = (x + 8) + 'px';
            bar.style.top = y + 'px'; bar.style.left = (x + r.width - 8) + 'px';
        };
        var mark = function (id, scroll) {
            if (selected) selected.classList.remove('sf-is-selected');
            selectedId = id; selected = byId(id);
            if (!selected) { place(); return; }
            selected.classList.add('sf-is-selected');
            var m = meta[id] || {};
            tag.innerHTML = (m.trust ? ico(ICO.lock, 13) : '') + escH(m.label || 'Bloc');
            tag.hidden = false;
            if (id === '__banner') { bar.innerHTML = ''; bar.hidden = true; }
            else {
                bar.innerHTML = '<span class="sf-ed-grip" draggable="true" title="Glisser pour déplacer" aria-hidden="true">' + ico(ICO.drag) + '</span>' +
                    btn('up', 'Monter', ICO.up, m.i === 0) + btn('down', 'Descendre', ICO.down, m.i === m.n - 1) +
                    (m.single ? (m.trust ? '' : btn('hide', 'Masquer ce bloc', ICO.hide)) : btn('dup', 'Dupliquer', ICO.dup) + btn('del', 'Supprimer', ICO.del));
                bar.hidden = false;
            }
            place();
            if (scroll) selected.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
        };
        var pick = function (blk) { mark(blk.dataset.sfBlock, false); send({ type: 'sf-select', id: blk.dataset.sfBlock }); };
        var labelBlocks = function () {
            Array.prototype.forEach.call(document.querySelectorAll('[data-sf-block]'), function (b) {
                var m = meta[b.dataset.sfBlock];
                b.tabIndex = 0;
                if (m) b.setAttribute('aria-label', 'Bloc ' + m.label + ' · Entrée pour le modifier');
            });
        };

        document.addEventListener('click', function (e) {
            var act = e.target.closest('[data-ed-act]');
            if (act) { e.preventDefault(); e.stopPropagation(); if (!act.disabled) send({ type: 'sf-act', id: selectedId, act: act.dataset.edAct }); return; }
            if (e.target.closest('.sf-ed-bar')) { e.preventDefault(); return; }
            var blk = e.target.closest('[data-sf-block]');
            if (e.target.closest('a, form, .sf-video')) e.preventDefault();
            if (blk) { e.preventDefault(); pick(blk); }
        }, true);

        /* Glisser-déposer : un bloc de la page (poignée), ou un bloc venu de l'éditeur (bibliothèque, structure) */
        var clearDrop = function () { line.hidden = true; if (end) end.classList.remove('is-over'); drop = null; };
        var stopDrag = function () { if (selected) selected.classList.remove('sf-is-dragging'); dragId = null; clearDrop(); };
        document.addEventListener('dragstart', function (e) {
            var el = e.target.nodeType === 1 ? e.target : e.target.parentElement;
            if (!el || !el.closest('.sf-ed-grip') || !selected) return;
            dragId = selectedId;
            selected.classList.add('sf-is-dragging');
            try { e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', dragId); e.dataTransfer.setDragImage(selected, 24, 24); } catch (x) {}
        });
        document.addEventListener('dragover', function (e) {
            if (!dragId && !outer) return;
            var el = e.target.nodeType === 1 ? e.target : e.target.parentElement;
            var t = el ? el.closest('[data-sf-block], .sf-ed-end') : null;
            if (!t) { clearDrop(); return; }
            e.preventDefault();
            try { e.dataTransfer.dropEffect = dragId ? 'move' : 'copy'; } catch (x) {}
            if (t === end) { line.hidden = true; end.classList.add('is-over'); drop = { end: true }; return; }
            if (end) end.classList.remove('is-over');
            var id = t.dataset.sfBlock;
            if (id === dragId) { line.hidden = true; drop = null; return; }
            var r = t.getBoundingClientRect();
            var after = id === '__banner' || e.clientY > r.top + r.height / 2; // l'en-tête reste en haut : on dépose dessous
            line.style.top = ((after ? r.bottom : r.top) + window.scrollY - 2) + 'px';
            line.style.left = (r.left + window.scrollX) + 'px';
            line.style.width = r.width + 'px';
            line.hidden = false;
            drop = { targetId: id, after: after };
        });
        document.addEventListener('dragleave', function (e) { if (!e.relatedTarget) clearDrop(); });
        document.addEventListener('drop', function (e) {
            if (!dragId && !outer) return;
            e.preventDefault();
            if (drop) send({ type: 'sf-drop', id: dragId, outer: outer, targetId: drop.targetId || null, after: !!drop.after, end: !!drop.end });
            stopDrag();
        });
        document.addEventListener('dragend', stopDrag);

        /* Clavier : Entrée sélectionne le bloc ; annuler, rétablir, déplacer, supprimer → éditeur */
        document.addEventListener('keydown', function (e) {
            var el = e.target, tn = el.tagName;
            if (tn === 'INPUT' || tn === 'TEXTAREA' || tn === 'SELECT') return;
            if (e.key === 'Enter' && el.hasAttribute && el.hasAttribute('data-sf-block')) { e.preventDefault(); pick(el); return; }
            var k = e.key.toLowerCase(), ctrl = e.ctrlKey || e.metaKey;
            if ((ctrl && (k === 'z' || k === 'y')) || (e.altKey && (e.key === 'ArrowUp' || e.key === 'ArrowDown')) || e.key === 'Delete') {
                e.preventDefault();
                send({ type: 'sf-key', key: e.key, ctrl: ctrl, alt: e.altKey, shift: e.shiftKey });
            }
        });

        window.addEventListener('message', function (e) {
            if (e.origin !== ORIGIN || !e.data) return;
            var d = e.data;
            if (d.type === 'sf-highlight') {
                if (d.meta) { meta = d.meta; labelBlocks(); }
                if (d.ui) document.documentElement.style.setProperty('--ed-ui', Math.min(2.6, Math.max(1, d.ui)).toFixed(3));
                mark(d.id, d.scroll !== false);
            }
            if (d.type === 'sf-drag') { outer = d.drag || null; if (!outer) clearDrop(); }
            if (d.type === 'sf-welcome') openWelcome();
        });
        window.addEventListener('resize', place);
        if (window.ResizeObserver) new ResizeObserver(place).observe(document.body);
        send({ type: 'sf-ready' });
    }
})();
</script>
@endpush

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
    /* Minuscules côté JS : strtolower() en PHP ne touche pas aux lettres accentuées
       (« Électronique » restait « Électronique ») alors que les cartes sont comparées en
       toLowerCase() → aucune carte ne correspondait aux catégories qui commencent par un accent. */
    var raw = cat || '';
    state.cat = raw.toLowerCase();
    _syncRadios('filterCat', raw);
    document.querySelectorAll('.sf-chip').forEach(function (c) {
        var on = (c.dataset.sfCat || '').toLowerCase() === state.cat;
        c.classList.toggle('is-on', on);
        c.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
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
    grid.classList.remove('sf-carousel'); // le visiteur reprend la main sur l'affichage choisi par le vendeur
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

/* ── Pastilles « Catégories » de la vitrine : filtrent la liste puis y descendent ── */
document.addEventListener('click', function (e) {
    var chip = e.target.closest('.sf-chip');
    if (!chip || chip.tagName === 'A') return; // pastille-lien : page de la catégorie
    filterCat(chip.dataset.sfCat || '');
    var target = document.getElementById('sfProducts');
    if (target && target.style.display !== 'none') target.scrollIntoView({ behavior: REDUCE_MOTION ? 'auto' : 'smooth', block: 'start' });
});

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
