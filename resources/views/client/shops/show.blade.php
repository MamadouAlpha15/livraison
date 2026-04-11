{{--
    resources/views/client/shops/show.blade.php
    Route     : GET /client/shops/{shop} → Client\ShopController@show
    Variables :
      $shop       → Shop (avec image, name, type, description, address, ville, currency)
      $products   → LengthAwarePaginator<Product>
      $categories → array (catégories distinctes des produits)
      $devise     → string
--}}
@extends('layouts.app')
@section('title', $shop->name . ' — Produits')
@php $bodyClass = 'shop-front'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --ink:      #0a0f0d;
    --ink-2:    #1e2b25;
    --ink-3:    #3d4f46;
    --muted:    #7a9088;
    --fog:      #e8efec;
    --fog-lt:   #f4f8f6;
    --surface:  #ffffff;
    --border:   rgba(0,0,0,.07);

    --teal:     #0ea472;
    --teal-dk:  #0d7a55;
    --teal-lt:  #d1f5e8;
    --teal-mlt: #edfdf5;
    --amber:    #f5a623;
    --amber-lt: #fef3d7;
    --rose:     #f24f60;
    --rose-lt:  #ffe0e3;
    --lime:     #b5f23c;
    --blue:     #3b82f6;
    --blue-lt:  #dbeafe;
    --violet:   #8b5cf6;
    --violet-lt:#ede9fe;

    --font:     'DM Sans', sans-serif;
    --display:  'Syne', sans-serif;
    --r:        14px;
    --r-sm:     9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
    --shadow:    0 4px 20px rgba(0,0,0,.08);
    --shadow-lg: 0 12px 40px rgba(0,0,0,.14);
    --nav-h:     60px;
}

html { font-family: var(--font); scroll-behavior: smooth; }
body { background: var(--fog-lt); margin: 0; color: var(--ink); -webkit-font-smoothing: antialiased; }

/* ══════════════════════════════════════════
   TOPBAR STICKY
══════════════════════════════════════════ */
.sf-topbar {
    position: sticky; top: 0; z-index: 100;
    height: var(--nav-h);
    background: rgba(255,255,255,.9);
    backdrop-filter: blur(16px) saturate(160%);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 20px; gap: 14px;
}
.sf-back {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 7px 14px; border-radius: 50px;
    font-size: 12.5px; font-weight: 600; font-family: var(--font);
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--ink-3); text-decoration: none;
    transition: all .15s; flex-shrink: 0;
}
.sf-back:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-mlt); }
.sf-topbar-title {
    flex: 1; font-family: var(--display); font-weight: 700;
    font-size: 15px; color: var(--ink);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sf-search-mini {
    display: flex; align-items: center; gap: 8px;
    background: var(--fog-lt); border: 1.5px solid var(--border);
    border-radius: 50px; padding: 7px 14px;
    flex: 0 0 220px;
    transition: all .2s;
}
.sf-search-mini:focus-within {
    border-color: var(--teal); background: var(--surface);
    box-shadow: 0 0 0 3px rgba(14,164,114,.1);
}
.sf-search-mini input {
    border: none; outline: none; background: transparent;
    font-size: 13px; font-family: var(--font); color: var(--ink);
    width: 100%;
}
.sf-cart-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 50px;
    font-size: 12.5px; font-weight: 700; font-family: var(--font);
    background: var(--ink); color: #fff; border: none;
    cursor: pointer; text-decoration: none; flex-shrink: 0;
    transition: background .15s;
    position: relative;
}
.sf-cart-btn:hover { background: var(--teal-dk); }
.sf-cart-count {
    position: absolute; top: -6px; right: -6px;
    width: 18px; height: 18px; border-radius: 50%;
    background: var(--rose); color: #fff;
    font-size: 9px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #fff;
    display: none;
}
.sf-cart-count.visible { display: flex; }

/* ══════════════════════════════════════════
   HERO BOUTIQUE
══════════════════════════════════════════ */
.sf-hero {
    background: var(--ink);
    position: relative; overflow: hidden;
}
.sf-hero-banner {
    height: 220px; position: relative;
    overflow: hidden;
}
.sf-hero-banner img {
    width: 100%; height: 100%; object-fit: cover; opacity: .45;
}
.sf-hero-banner-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, #1a2e26 0%, #0d4a32 50%, #1a2e26 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 64px; opacity: .25;
}
.sf-hero-banner::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(10,15,13,.3) 0%, rgba(10,15,13,.85) 100%);
}
.sf-hero-content {
    position: absolute; bottom: 0; left: 0; right: 0;
    padding: 20px 28px 28px;
    z-index: 2; display: flex; align-items: flex-end; gap: 20px;
}
.sf-hero-logo {
    width: 72px; height: 72px; border-radius: 18px;
    background: var(--surface); border: 3px solid rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 32px; flex-shrink: 0; overflow: hidden;
    box-shadow: var(--shadow-lg);
}
.sf-hero-logo img { width: 100%; height: 100%; object-fit: cover; }
.sf-hero-info { flex: 1; min-width: 0; }
.sf-hero-type {
    font-size: 11px; font-weight: 700; letter-spacing: 1.2px;
    text-transform: uppercase; color: var(--teal);
    margin-bottom: 5px;
}
.sf-hero-name {
    font-family: var(--display); font-size: clamp(20px, 4vw, 30px);
    font-weight: 800; color: #fff; letter-spacing: -.4px;
    margin-bottom: 6px; line-height: 1.1;
}
.sf-hero-meta {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
}
.sf-hero-chip {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11.5px; color: rgba(255,255,255,.6); font-weight: 500;
}
.sf-hero-open {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(14,164,114,.85); color: #fff;
    font-size: 10.5px; font-weight: 700; padding: 4px 10px;
    border-radius: 20px;
}
.sf-hero-open::before {
    content: ''; width: 5px; height: 5px; border-radius: 50%;
    background: #fff; animation: pulse 1.8s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

/* ══════════════════════════════════════════
   BARRE DE FILTRE
══════════════════════════════════════════ */
.sf-filters {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 14px 24px;
    display: flex; align-items: center; gap: 10px;
    flex-wrap: wrap; position: sticky;
    top: var(--nav-h); z-index: 90;
    box-shadow: var(--shadow-sm);
}
.sf-filter-cats {
    display: flex; gap: 6px; overflow-x: auto;
    scrollbar-width: none; flex: 1;
}
.sf-filter-cats::-webkit-scrollbar { display: none; }
.sf-filter-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; border-radius: 50px; white-space: nowrap;
    font-size: 12px; font-weight: 600; font-family: var(--font);
    border: 1.5px solid var(--border); background: var(--fog-lt);
    color: var(--ink-3); cursor: pointer;
    transition: all .15s; flex-shrink: 0;
}
.sf-filter-pill:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-mlt); }
.sf-filter-pill.active { background: var(--ink); color: #fff; border-color: var(--ink); }
.sf-filter-sep { width: 1px; height: 28px; background: var(--border); flex-shrink: 0; }
.sf-sort-select {
    padding: 7px 12px; border-radius: var(--r-sm);
    border: 1.5px solid var(--border); background: var(--fog-lt);
    font-size: 12px; font-weight: 600; font-family: var(--font);
    color: var(--ink-3); outline: none; cursor: pointer;
    transition: border-color .15s;
}
.sf-sort-select:focus { border-color: var(--teal); }
.sf-filter-count {
    font-size: 11.5px; color: var(--muted); font-weight: 500;
    white-space: nowrap; flex-shrink: 0;
}

/* ══════════════════════════════════════════
   PRODUITS EN VEDETTE (carousel horizontal)
══════════════════════════════════════════ */
.sf-featured {
    padding: 24px 24px 0;
    max-width: 1200px; margin: 0 auto;
}
.sf-featured-title {
    font-family: var(--display); font-size: 15px; font-weight: 700;
    color: var(--ink); margin-bottom: 14px;
    display: flex; align-items: center; gap: 8px;
}
.sf-featured-title::before {
    content: '';
    display: inline-block; width: 4px; height: 18px;
    background: var(--amber); border-radius: 2px;
}
.sf-featured-scroll {
    display: flex; gap: 14px; overflow-x: auto;
    padding-bottom: 4px; scrollbar-width: none;
}
.sf-featured-scroll::-webkit-scrollbar { display: none; }

.sf-feat-card {
    flex-shrink: 0; width: 200px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden; box-shadow: var(--shadow-sm);
    transition: box-shadow .2s, transform .2s;
    text-decoration: none; color: inherit;
    display: flex; flex-direction: column;
    position: relative;
}
.sf-feat-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-3px); }
.sf-feat-img {
    height: 130px; overflow: hidden; position: relative;
    background: var(--fog-lt); flex-shrink: 0;
}
.sf-feat-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
.sf-feat-card:hover .sf-feat-img img { transform: scale(1.07); }
.sf-feat-img-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 32px; opacity: .3; }
.sf-feat-star {
    position: absolute; top: 8px; right: 8px;
    background: var(--amber); color: #fff;
    font-size: 9px; font-weight: 800; padding: 3px 7px; border-radius: 20px;
}
.sf-feat-body { padding: 10px 12px 12px; flex: 1; }
.sf-feat-cat  { font-size: 9.5px; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 3px; }
.sf-feat-name { font-size: 12.5px; font-weight: 700; color: var(--ink); margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sf-feat-price { font-size: 14px; font-weight: 800; color: var(--teal); font-family: monospace; }
.sf-feat-orig  { font-size: 10.5px; color: var(--muted); text-decoration: line-through; font-family: monospace; }

/* ══════════════════════════════════════════
   GRILLE PRODUITS PRINCIPALE
══════════════════════════════════════════ */
.sf-main {
    max-width: 1200px; margin: 0 auto;
    padding: 24px 24px 80px;
}

.sf-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 18px;
}

/* ── Card produit ── */
.sf-prod-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .25s, transform .25s, border-color .2s;
    display: flex; flex-direction: column;
    position: relative;
}
.sf-prod-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
    border-color: rgba(14,164,114,.2);
}

/* Zone image */
.sf-prod-img {
    position: relative; overflow: hidden;
    height: 200px; background: var(--fog-lt); flex-shrink: 0;
    cursor: pointer;
}
.sf-prod-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s ease;
}
.sf-prod-card:hover .sf-prod-img img { transform: scale(1.06); }
.sf-prod-img-ph {
    width: 100%; height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 6px;
    background: linear-gradient(135deg, #f3f8f5, #eaf2ee);
    color: var(--muted);
}
.sf-prod-img-ph span { font-size: 40px; opacity: .35; }

/* Badges */
.sf-prod-badges {
    position: absolute; top: 10px; left: 10px;
    display: flex; flex-direction: column; gap: 4px;
    pointer-events: none;
}
.sf-badge {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10px; font-weight: 700;
    padding: 3px 8px; border-radius: 20px;
    white-space: nowrap;
}
.sf-badge-star    { background: var(--amber); color: #fff; }
.sf-badge-promo   { background: var(--rose); color: #fff; }
.sf-badge-rupture { background: rgba(0,0,0,.65); color: #fff; }
.sf-badge-stock   { background: rgba(14,164,114,.85); color: #fff; }

/* Bouton galerie (compteur photos) */
.sf-gallery-btn {
    position: absolute; bottom: 8px; right: 8px;
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(0,0,0,.6); color: #fff;
    font-size: 10px; font-weight: 700;
    padding: 4px 9px; border-radius: 20px;
    cursor: pointer; border: none; backdrop-filter: blur(4px);
    transition: background .15s;
}
.sf-gallery-btn:hover { background: rgba(0,0,0,.85); }

/* Corps */
.sf-prod-body {
    padding: 14px 14px 10px;
    flex: 1; display: flex; flex-direction: column; gap: 7px;
}
.sf-prod-cat {
    font-size: 10px; font-weight: 700; color: var(--teal);
    text-transform: uppercase; letter-spacing: .6px;
}
.sf-prod-name {
    font-family: var(--display); font-size: 14px; font-weight: 700;
    color: var(--ink); line-height: 1.3; margin: 0;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.sf-prod-desc {
    font-size: 12px; color: var(--muted); line-height: 1.5; margin: 0;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
/* Prix */
.sf-prod-price-row {
    display: flex; align-items: baseline; gap: 8px; margin-top: 2px;
}
.sf-prod-price {
    font-size: 18px; font-weight: 800; font-family: monospace;
    color: var(--teal); letter-spacing: -.5px;
}
.sf-prod-price-orig {
    font-size: 12px; font-family: monospace;
    color: var(--muted); text-decoration: line-through;
}
.sf-prod-price-unit { font-size: 10.5px; color: var(--muted); font-weight: 600; }
.sf-prod-remise {
    font-size: 10px; font-weight: 800; background: var(--rose-lt);
    color: var(--rose); padding: 2px 6px; border-radius: 5px;
}
/* Meta chips */
.sf-prod-meta {
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    margin-top: 2px;
}
.sf-prod-chip {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10.5px; font-weight: 600; color: var(--ink-3);
    background: var(--fog-lt); border: 1px solid var(--border);
    padding: 2px 7px; border-radius: var(--r-sm);
}
.sf-prod-chip.danger { color: var(--rose); background: var(--rose-lt); border-color: #fca5a5; }
.sf-prod-chip.amber  { color: #92400e; background: var(--amber-lt); border-color: #fde68a; }
.sf-prod-chip.ok     { color: #065f46; background: var(--teal-lt); border-color: #6ee7b7; }

/* Footer bouton */
.sf-prod-footer { padding: 10px 14px; border-top: 1px solid var(--border); }
.sf-order-btn {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; padding: 10px 14px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 700; font-family: var(--font);
    background: var(--teal); color: #fff; border: none;
    cursor: pointer; text-decoration: none;
    transition: all .15s;
    box-shadow: 0 4px 12px rgba(14,164,114,.25);
}
.sf-order-btn:hover { background: var(--teal-dk); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(14,164,114,.35); }
.sf-order-btn.disabled {
    background: var(--fog); color: var(--muted);
    cursor: not-allowed; box-shadow: none; transform: none;
    border: 1.5px solid var(--border);
}

/* ══════════════════════════════════════════
   LIGHTBOX GALERIE
══════════════════════════════════════════ */
.sf-lb {
    display: none; position: fixed; inset: 0; z-index: 9000;
    background: rgba(0,0,0,.95);
    align-items: center; justify-content: center;
    flex-direction: column;
}
.sf-lb.open { display: flex; }
.sf-lb-hd {
    position: absolute; top: 0; left: 0; right: 0;
    padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between;
    background: linear-gradient(to bottom, rgba(0,0,0,.7), transparent);
    z-index: 2;
}
.sf-lb-name { font-size: 14px; font-weight: 700; color: #fff; }
.sf-lb-close {
    width: 36px; height: 36px; border-radius: 50%;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
    color: #fff; font-size: 16px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s;
}
.sf-lb-close:hover { background: rgba(255,255,255,.25); }
.sf-lb-main {
    flex: 1; width: 100%; position: relative;
    display: flex; align-items: center; justify-content: center;
    padding: 56px 56px 10px;
    overflow: hidden;
}
.sf-lb-img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; display: block; }
.sf-lb-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 44px; height: 44px; border-radius: 50%;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
    color: #fff; font-size: 20px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s; z-index: 2;
}
.sf-lb-nav:hover { background: rgba(255,255,255,.25); }
.sf-lb-prev { left: 10px; }
.sf-lb-next { right: 10px; }
.sf-lb-nav.gone { display: none; }
.sf-lb-thumbs {
    display: flex; gap: 8px; padding: 10px 16px 16px;
    overflow-x: auto; justify-content: center; flex-shrink: 0;
    scrollbar-width: thin;
}
.sf-lb-thumb {
    width: 52px; height: 52px; border-radius: 8px;
    object-fit: cover; cursor: pointer; flex-shrink: 0;
    border: 2.5px solid transparent; opacity: .5;
    transition: all .2s;
}
.sf-lb-thumb:hover { opacity: .85; }
.sf-lb-thumb.on { opacity: 1; border-color: var(--teal); transform: scale(1.1); }
.sf-lb-count {
    position: absolute; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,.5); color: rgba(255,255,255,.8);
    font-size: 11px; font-family: monospace; font-weight: 600;
    padding: 4px 12px; border-radius: 20px;
}

/* ══════════════════════════════════════════
   VIDE / PAGINATION
══════════════════════════════════════════ */
.sf-empty {
    grid-column: 1/-1; padding: 72px 20px; text-align: center;
}
.sf-empty-ico { font-size: 52px; display: block; opacity: .3; margin-bottom: 12px; }
.sf-empty-title { font-family: var(--display); font-size: 18px; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
.sf-empty-sub { font-size: 13.5px; color: var(--muted); }
.sf-pagination { display: flex; justify-content: center; padding: 24px 0 8px; }

/* ══════════════════════════════════════════
   FLASH
══════════════════════════════════════════ */
.sf-flash {
    padding: 11px 16px; border-radius: var(--r-sm); border: 1px solid;
    font-size: 13px; font-weight: 500; margin-bottom: 18px;
    display: flex; align-items: center; gap: 8px;
}
.sf-flash-success { background: var(--teal-lt); border-color: #6ee7b7; color: #065f46; }
.sf-flash-danger  { background: var(--rose-lt); border-color: #fca5a5; color: #991b1b; }

/* ══════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════ */
@media (max-width: 900px) {
    .sf-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
    .sf-main { padding: 18px 16px 60px; }
    .sf-featured { padding: 18px 16px 0; }
    .sf-filters  { padding: 12px 16px; }
}
@media (max-width: 640px) {
    .sf-topbar { padding: 0 12px; }
    .sf-search-mini { display: none; }
    .sf-hero-content { padding: 14px 16px 20px; }
    .sf-hero-logo { width: 56px; height: 56px; font-size: 24px; border-radius: 14px; }
    .sf-hero-banner { height: 170px; }
    .sf-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .sf-prod-img { height: 150px; }
    .sf-prod-body { padding: 10px 10px 8px; gap: 5px; }
    .sf-prod-name { font-size: 13px; }
    .sf-prod-desc { display: none; }
    .sf-prod-price { font-size: 15px; }
    .sf-prod-footer { padding: 8px 10px; }
    .sf-order-btn { font-size: 12px; padding: 9px 10px; }
    .sf-lb-main { padding: 56px 8px 8px; }
    .sf-lb-nav { width: 36px; height: 36px; font-size: 16px; }
    .sf-lb-prev { left: 4px; }
    .sf-lb-next { right: 4px; }
}
@media (max-width: 380px) {
    .sf-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

@php
    $devise         = $shop->currency ?? 'GNF';
    $featuredProds  = $products->getCollection()->where('is_featured', true);
    $hasImages      = fn($p) => !empty(json_decode($p->gallery ?? '[]', true));
    $allPhotos      = fn($p) => array_filter(
        array_merge(
            $p->image ? [asset('storage/'.$p->image)] : [],
            array_map(fn($g) => asset('storage/'.$g), json_decode($p->gallery ?? '[]', true))
        )
    );
@endphp

{{-- ══════ TOPBAR ══════ --}}
<nav class="sf-topbar">
    <a href="{{ route('client.dashboard') }}" class="sf-back">← Boutiques</a>
    <div class="sf-topbar-title">{{ $shop->name }}</div>

    {{-- Recherche produit --}}
    <div class="sf-search-mini">
        <span style="font-size:13px;color:var(--muted)">🔍</span>
        <input type="text" id="prodSearch" placeholder="Rechercher un produit…">
    </div>

    {{-- Bouton commander (ancre) --}}
    <a href="#produits" class="sf-cart-btn">
        🛒 Commander
    </a>
</nav>

{{-- ══════ HERO BOUTIQUE ══════ --}}
<section class="sf-hero">
    <div class="sf-hero-banner">
        @if($shop->image)
            <img src="{{ asset('storage/'.$shop->image) }}" alt="{{ $shop->name }}">
        @else
            <div class="sf-hero-banner-placeholder">🛍️</div>
        @endif
    </div>
    <div class="sf-hero-content">
        <div class="sf-hero-logo">
            @if($shop->image)
                <img src="{{ asset('storage/'.$shop->image) }}" alt="">
            @else
                🛍️
            @endif
        </div>
        <div class="sf-hero-info">
            @if($shop->type)
            <div class="sf-hero-type">{{ $shop->type }}</div>
            @endif
            <h1 class="sf-hero-name">{{ $shop->name }}</h1>
            <div class="sf-hero-meta">
                <span class="sf-hero-open">Ouvert</span>
                @if($shop->ville ?? $shop->address)
                <span class="sf-hero-chip">📍 {{ $shop->ville ?? Str::limit($shop->address, 25) }}</span>
                @endif
                <span class="sf-hero-chip">🏷️ {{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}</span>
                @if($shop->phone)
                <span class="sf-hero-chip">📞 {{ $shop->phone }}</span>
                @endif
            </div>
            @if($shop->description)
            <p style="font-size:13px;color:rgba(255,255,255,.45);margin:8px 0 0;line-height:1.5;max-width:500px">
                {{ Str::limit($shop->description, 120) }}
            </p>
            @endif
        </div>
    </div>
</section>

{{-- ══════ FILTRE CATÉGORIES ══════ --}}
<div class="sf-filters" id="sfFilters">
    <div class="sf-filter-cats" id="filterCats">
        <button class="sf-filter-pill active" data-cat="">🏪 Tous</button>
        @foreach($categories as $cat)
        <button class="sf-filter-pill" data-cat="{{ $cat }}">{{ $cat }}</button>
        @endforeach
    </div>
    <div class="sf-filter-sep"></div>
    <select class="sf-sort-select" id="sortSelect">
        <option value="default">Trier : Par défaut</option>
        <option value="price_asc">Prix : croissant</option>
        <option value="price_desc">Prix : décroissant</option>
        <option value="name">Nom A→Z</option>
        <option value="featured">Vedettes d'abord</option>
    </select>
    <span class="sf-filter-count" id="filterCount">
        {{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}
    </span>
</div>

{{-- ══════ PRODUITS EN VEDETTE ══════ --}}
@if($featuredProds->isNotEmpty())
<section class="sf-featured">
    <div class="sf-featured-title">⭐ Produits en vedette</div>
    <div class="sf-featured-scroll">
        @foreach($featuredProds as $fp)
        @php
            $fpHasPromo = $fp->original_price && $fp->original_price > $fp->price;
            $fpPhotos   = array_values($allPhotos($fp));
        @endphp
        <div class="sf-feat-card"
             onclick="@auth @if(Auth::user()->role === 'client') window.location='{{ route('client.orders.createFromProduct', $fp) }}' @endif @endauth">
            <div class="sf-feat-img">
                @if($fp->image)
                    <img src="{{ asset('storage/'.$fp->image) }}" alt="{{ $fp->name }}">
                @else
                    <div class="sf-feat-img-ph">🏷️</div>
                @endif
                <span class="sf-feat-star">⭐ Vedette</span>
            </div>
            <div class="sf-feat-body">
                @if($fp->category)<div class="sf-feat-cat">{{ $fp->category }}</div>@endif
                <div class="sf-feat-name" title="{{ $fp->name }}">{{ Str::limit($fp->name, 22) }}</div>
                <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px">
                    <div class="sf-feat-price">{{ number_format($fp->price, 0, ',', ' ') }} <span style="font-size:9px;font-weight:500;color:var(--muted)">{{ $devise }}</span></div>
                    @if($fpHasPromo)<div class="sf-feat-orig">{{ number_format($fp->original_price, 0, ',', ' ') }}</div>@endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ══════ GRILLE PRODUITS ══════ --}}
<main class="sf-main" id="produits">

    {{-- Flash --}}
    @foreach(['success','danger'] as $t)
        @if(session($t))
        <div class="sf-flash sf-flash-{{ $t }}">
            <span>{{ $t === 'success' ? '✓' : '✕' }}</span>
            {{ session($t) }}
        </div>
        @endif
    @endforeach

    <div class="sf-grid" id="prodGrid">
        @forelse($products as $product)
        @php
            $hasPromo   = $product->original_price && $product->original_price > $product->price;
            $remise     = $hasPromo ? round((1 - $product->price / $product->original_price) * 100) : 0;
            $stockVal   = $product->stock ?? null;
            $stockOut   = $stockVal !== null && $stockVal <= 0;
            $stockLow   = $stockVal !== null && $stockVal > 0 && $stockVal <= 5;
            $gallery    = json_decode($product->gallery ?? '[]', true) ?: [];
            $photos     = array_values($allPhotos($product));
            $totalPics  = count($photos);
            $canOrder   = !$stockOut;
        @endphp
        <div class="sf-prod-card"
             data-name="{{ strtolower($product->name) }}"
             data-cat="{{ strtolower($product->category ?? '') }}"
             data-price="{{ $product->price }}"
             data-featured="{{ $product->is_featured ? '1' : '0' }}"
             data-sort-name="{{ $product->name }}">

            {{-- ── Zone image ── --}}
            <div class="sf-prod-img"
                 onclick="{{ $totalPics > 0 ? 'openGallery('.json_encode($photos).', '.json_encode($product->name).')' : '' }}"
                 style="{{ $totalPics > 0 ? 'cursor:zoom-in' : 'cursor:default' }}">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                @else
                    <div class="sf-prod-img-ph">
                        <span>🏷️</span>
                    </div>
                @endif

                {{-- Badges --}}
                <div class="sf-prod-badges">
                    @if($product->is_featured)
                        <span class="sf-badge sf-badge-star">⭐ Vedette</span>
                    @endif
                    @if($hasPromo)
                        <span class="sf-badge sf-badge-promo">-{{ $remise }}%</span>
                    @endif
                    @if($stockOut)
                        <span class="sf-badge sf-badge-rupture">Rupture</span>
                    @endif
                </div>

                {{-- Compteur photos galerie --}}
                @if($totalPics > 1)
                <button class="sf-gallery-btn"
                        onclick="event.stopPropagation();openGallery({{ json_encode($photos) }}, {{ json_encode($product->name) }})">
                    🖼 {{ $totalPics }} photo{{ $totalPics > 1 ? 's' : '' }}
                </button>
                @endif
            </div>

            {{-- ── Corps ── --}}
            <div class="sf-prod-body">
                @if($product->category)
                <div class="sf-prod-cat">{{ $product->category }}</div>
                @endif

                <h3 class="sf-prod-name" title="{{ $product->name }}">{{ $product->name }}</h3>

                @if($product->description)
                <p class="sf-prod-desc">{{ $product->description }}</p>
                @endif

                {{-- Prix --}}
                <div class="sf-prod-price-row">
                    <span class="sf-prod-price">{{ number_format($product->price, 0, ',', ' ') }}</span>
                    <span style="font-size:10px;color:var(--muted);font-weight:600">{{ $devise }}</span>
                    @if($hasPromo)
                    <span class="sf-prod-price-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>
                    <span class="sf-prod-remise">-{{ $remise }}%</span>
                    @endif
                    @if($product->unit)
                    <span class="sf-prod-price-unit">/ {{ $product->unit }}</span>
                    @endif
                </div>

                {{-- Meta chips --}}
                <div class="sf-prod-meta">
                    @if($stockVal !== null)
                        @if($stockOut)
                            <span class="sf-prod-chip danger">❌ Rupture de stock</span>
                        @elseif($stockLow)
                            <span class="sf-prod-chip amber">⚠️ Plus que {{ $stockVal }} en stock</span>
                        @else
                            <span class="sf-prod-chip ok">✓ {{ $stockVal }} en stock</span>
                        @endif
                    @endif
                    @if($product->preparation_time)
                        <span class="sf-prod-chip">⏱ {{ $product->preparation_time }}min</span>
                    @endif
                    @if($product->allergens)
                        <span class="sf-prod-chip" title="Allergènes : {{ $product->allergens }}" style="cursor:help">⚠ Allergènes</span>
                    @endif
                </div>
            </div>

            {{-- ── Footer commande ── --}}
            <div class="sf-prod-footer">
                @auth
                    @if(Auth::user()->role === 'client')
                        @if($canOrder)
                        <a href="{{ route('client.orders.createFromProduct', $product) }}"
                           class="sf-order-btn">
                            🛒 Commander
                        </a>
                        @else
                        <div class="sf-order-btn disabled">
                            ❌ Indisponible
                        </div>
                        @endif
                    @else
                        <a href="{{ route('client.dashboard') }}" class="sf-order-btn" style="background:var(--fog);color:var(--muted);box-shadow:none">
                            Accès client requis
                        </a>
                    @endif
                @else
                    <a href="{{ route('register') }}" class="sf-order-btn" style="background:var(--ink)">
                        S'inscrire pour commander
                    </a>
                @endauth
            </div>

        </div>
        @empty
        <div class="sf-empty">
            <span class="sf-empty-ico">📭</span>
            <div class="sf-empty-title">Aucun produit disponible</div>
            <p class="sf-empty-sub">Cette boutique n'a pas encore ajouté de produits.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="sf-pagination">
        {{ $products->links() }}
    </div>

</main>

{{-- ══════ LIGHTBOX GALERIE ══════ --}}
<div class="sf-lb" id="sfLb" role="dialog">
    <div class="sf-lb-hd">
        <span class="sf-lb-name" id="lbName"></span>
        <button class="sf-lb-close" onclick="closeLb()">✕</button>
    </div>
    <div class="sf-lb-main">
        <button class="sf-lb-nav sf-lb-prev" id="lbPrev" onclick="lbNav(-1)">‹</button>
        <img class="sf-lb-img" id="lbImg" src="" alt="">
        <button class="sf-lb-nav sf-lb-next" id="lbNext" onclick="lbNav(1)">›</button>
        <div class="sf-lb-count" id="lbCount"></div>
    </div>
    <div class="sf-lb-thumbs" id="lbThumbs"></div>
</div>

@endsection

@push('scripts')
<script>
/* ══ LIGHTBOX ══ */
let lbPhotos = [], lbIdx = 0, lbName = '';

function openGallery(photos, name) {
    lbPhotos = photos; lbIdx = 0; lbName = name;
    document.getElementById('lbName').textContent = name;
    document.getElementById('sfLb').classList.add('open');
    document.body.style.overflow = 'hidden';
    buildThumbs(); showPhoto(0);
}

function showPhoto(i) {
    if (i < 0 || i >= lbPhotos.length) return;
    lbIdx = i;
    const img = document.getElementById('lbImg');
    img.src = lbPhotos[i];
    document.getElementById('lbCount').textContent = (i+1) + ' / ' + lbPhotos.length;
    document.getElementById('lbPrev').classList.toggle('gone', i === 0);
    document.getElementById('lbNext').classList.toggle('gone', i === lbPhotos.length - 1);
    document.querySelectorAll('.sf-lb-thumb').forEach((t, j) => t.classList.toggle('on', j === i));
    document.querySelector('.sf-lb-thumb.on')?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
}

function buildThumbs() {
    document.getElementById('lbThumbs').innerHTML = lbPhotos.map((u, i) =>
        `<img class="sf-lb-thumb ${i === 0 ? 'on' : ''}" src="${u}" onclick="showPhoto(${i})" loading="lazy">`
    ).join('');
}

function lbNav(d) { showPhoto(lbIdx + d); }

function closeLb() {
    document.getElementById('sfLb').classList.remove('open');
    document.body.style.overflow = '';
}

document.getElementById('sfLb').addEventListener('click', e => {
    if (e.target === document.getElementById('sfLb')) closeLb();
});
document.addEventListener('keydown', e => {
    if (!document.getElementById('sfLb').classList.contains('open')) return;
    if (e.key === 'Escape') closeLb();
    if (e.key === 'ArrowLeft') lbNav(-1);
    if (e.key === 'ArrowRight') lbNav(1);
});
/* Swipe tactile */
let lbTx = 0;
document.getElementById('sfLb').addEventListener('touchstart', e => { lbTx = e.touches[0].clientX; }, { passive: true });
document.getElementById('sfLb').addEventListener('touchend', e => {
    const dx = e.changedTouches[0].clientX - lbTx;
    if (Math.abs(dx) > 50) lbNav(dx < 0 ? 1 : -1);
}, { passive: true });

/* ══ FILTRE PAR CATÉGORIE ══ */
const pills = document.querySelectorAll('.sf-filter-pill');
let activeCat  = '';
let activeSort = 'default';
let searchQ    = '';

pills.forEach(btn => {
    btn.addEventListener('click', () => {
        pills.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeCat = btn.dataset.cat;
        applyFilters();
    });
});

document.getElementById('sortSelect').addEventListener('change', e => {
    activeSort = e.target.value;
    applyFilters();
});

const searchInput = document.getElementById('prodSearch');
if (searchInput) {
    let st;
    searchInput.addEventListener('input', e => {
        clearTimeout(st);
        st = setTimeout(() => { searchQ = e.target.value.toLowerCase().trim(); applyFilters(); }, 250);
    });
}

function applyFilters() {
    const cards = Array.from(document.querySelectorAll('.sf-prod-card'));

    /* Filtrer */
    let visible = cards.filter(c => {
        const cat  = c.dataset.cat || '';
        const name = c.dataset.name || '';
        const catOk  = !activeCat || cat === activeCat.toLowerCase();
        const nameOk = !searchQ || name.includes(searchQ);
        return catOk && nameOk;
    });
    const hidden = cards.filter(c => !visible.includes(c));

    hidden.forEach(c => { c.style.display = 'none'; });

    /* Trier les visibles */
    visible.sort((a, b) => {
        if (activeSort === 'price_asc')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
        if (activeSort === 'price_desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
        if (activeSort === 'name')       return (a.dataset.sortName||'').localeCompare(b.dataset.sortName||'');
        if (activeSort === 'featured')   return (b.dataset.featured||'0') - (a.dataset.featured||'0');
        return 0;
    });

    const grid = document.getElementById('prodGrid');
    visible.forEach(c => { c.style.display = ''; grid.appendChild(c); });

    /* Compteur */
    const n = visible.length;
    document.getElementById('filterCount').textContent = n + ' produit' + (n > 1 ? 's' : '');
}

/* ══ ANIMATION D'ENTRÉE ══ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.sf-prod-card').forEach((c, i) => {
        c.style.opacity = '0';
        c.style.transform = 'translateY(16px)';
        setTimeout(() => {
            c.style.transition = 'opacity .35s ease, transform .35s ease';
            c.style.opacity    = '1';
            c.style.transform  = 'translateY(0)';
        }, 50 + i * 35);
    });
});
</script>
@endpush