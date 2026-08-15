{{--
    resources/views/client/orders/create_from_product.blade.php
    Route     : GET /client/orders/create-from-product/{product}
    Variables : $product, $devise
--}}
@extends('layouts.app')
@section('title', 'Commander · ' . $product->name)
@php $bodyClass = 'is-dashboard'; @endphp  

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --brand:     #6366f1;
    --brand-dk:  #4f46e5;
    --brand-lt:  #e0e7ff;
    --brand-mlt: #eef2ff;
    --navy:      #0a0a1e;
    --navy-2:    #151538;
    --green:     #067d62;
    --green-lt:  #e8f5e9;
    --red:       #dc2626;
    --blue:      #4f46e5;
    --grey:      #f4f6fb;
    --grey-2:    #e8ecf5;
    --border:    #e2e8f0;
    --text:      #0f172a;
    --text-2:    #333;
    --muted:     #64748b;
    --surface:   #fff;
    --amber-lt:  #fff8e1;
    --rose-lt:   #fee2e2;
    --teal:      #0ea472;
    --teal-lt:   #d1f5e8;
    --font:      'Open Sans', sans-serif;
    --display:   'Nunito', sans-serif;
    --r:         10px;
    --r-sm:      6px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
    --shadow:    0 4px 16px rgba(0,0,0,.1);
    --nav-h:     56px;
}
html { font-family: var(--font); scroll-behavior: smooth; }
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }
.ico { display: inline-block; flex-shrink: 0; vertical-align: -3px; }

/* ══ NAVBAR ══ */
.nav { background: linear-gradient(120deg, var(--navy), var(--navy-2)); height: var(--nav-h); display: flex; align-items: center; padding: 0 16px; gap: 10px; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 12px rgba(0,0,0,.15); }
.nav-logo { display: flex; align-items: center; gap: 8px; font-family: var(--display); font-size: 17px; font-weight: 900; color: #fff; text-decoration: none; flex-shrink: 0; }
.nav-logo img { width: 28px; height: 28px; border-radius: 7px; object-fit: cover; flex-shrink: 0; }
.nav-logo span { color: var(--brand-lt); }
.nav-back { display: inline-flex; align-items: center; gap: 5px; color: rgba(255,255,255,.8); font-size: 12.5px; font-weight: 600; text-decoration: none; padding: 5px 10px; border: 1px solid transparent; border-radius: var(--r-sm); transition: all .15s; white-space: nowrap; flex-shrink: 0; }
.nav-back:hover { border-color: rgba(255,255,255,.4); color: #fff; }
.nav-title { flex: 1; min-width: 0; font-size: 13px; font-weight: 700; color: rgba(255,255,255,.8); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ══ PAGE ══ */
.page-wrap { max-width: 860px; margin: 0 auto; padding: 24px 16px 80px; }

/* ══ FLASH ══ */
.flash { padding: 12px 16px; border-radius: var(--r); border: 1px solid; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.flash-success { background: var(--teal-lt); border-color: #6ee7b7; color: #065f46; }
.flash-danger  { background: var(--rose-lt); border-color: #fca5a5; color: var(--red); }

/* ══ CARD ══ */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 16px; animation: cardFadeUp .5s ease both; }
.card:nth-of-type(2) { animation-delay: .08s; }
@keyframes cardFadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
@media (prefers-reduced-motion: reduce) { .card { animation: none; } }
.card-hd { padding: 12px 18px; border-bottom: 1px solid var(--border); background: var(--grey); display: flex; align-items: center; gap: 8px; }
.card-hd-ico { width: 26px; height: 26px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 13px; background: var(--brand-lt); color: var(--brand-dk); border: 1px solid #c7d2fe; flex-shrink: 0; }
.card-title { font-family: var(--display); font-size: 14px; font-weight: 800; color: var(--text); }
.card-body { padding: 18px; }

/* ══ PRODUIT ══ */
.prod-layout { display: flex; gap: 20px; align-items: flex-start; }
.prod-gallery-col { flex-shrink: 0; }

.prod-gallery-col { flex-shrink: 0; width: 240px; }
.prod-main-img { width: 100%; height: 240px; border-radius: var(--r); overflow: hidden; background: var(--grey); border: 1px solid var(--border); cursor: zoom-in; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; transition: box-shadow .2s; position: relative; }
.prod-main-img:hover { box-shadow: var(--shadow); }
.prod-main-img img { width: 100%; height: 100%; object-fit: cover; transition: opacity .18s ease, transform .3s; }
.prod-main-img:hover img { transform: scale(1.04); }
.prod-main-img-ph { font-size: 52px; opacity: .22; }

/* Flèches navigation galerie */
.prod-nav-btn {
    position: absolute; top: 50%; transform: translateY(-50%); z-index: 3;
    width: 34px; height: 34px; border-radius: 50%;
    background: rgba(0,0,0,.48); backdrop-filter: blur(4px);
    border: none; color: #fff; font-size: 20px; line-height: 1;
    cursor: pointer; display: none; align-items: center; justify-content: center;
    transition: background .15s; padding: 0; font-family: sans-serif;
}
.prod-nav-btn:active { background: rgba(0,0,0,.78); }
.prod-nav-prev { left: 8px; }
.prod-nav-next { right: 8px; }

/* Bande miniatures — défilement horizontal */
.prod-thumbs-wrap { position: relative; }
.prod-thumbs {
    display: flex; gap: 7px; overflow-x: auto; flex-wrap: nowrap;
    padding-bottom: 4px; scroll-snap-type: x mandatory;
    scrollbar-width: thin; scrollbar-color: var(--brand) var(--grey-2);
}
.prod-thumbs::-webkit-scrollbar { height: 4px; }
.prod-thumbs::-webkit-scrollbar-thumb { background: var(--brand); border-radius: 4px; }
.prod-thumbs::-webkit-scrollbar-track { background: var(--grey-2); border-radius: 4px; }
.prod-thumb { flex-shrink: 0; width: 52px; height: 52px; border-radius: var(--r-sm); object-fit: cover; border: 2.5px solid transparent; cursor: pointer; transition: all .15s; opacity: .6; scroll-snap-align: start; }
.prod-thumb:hover, .prod-thumb.active { border-color: var(--brand); opacity: 1; transform: scale(1.06); }

.prod-info { flex: 1; min-width: 0; }
.prod-cat { font-size: 10.5px; font-weight: 700; color: var(--blue); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 5px; }
.prod-name { font-family: var(--display); font-size: clamp(17px, 3vw, 24px); font-weight: 900; color: var(--text); line-height: 1.25; margin-bottom: 12px; }
.prod-price-row { display: flex; align-items: baseline; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
.prod-price { font-size: 28px; font-weight: 800; color: var(--brand-dk); font-family: monospace; letter-spacing: -.5px; }
.prod-devise { font-size: 12px; color: var(--muted); font-weight: 600; }
.prod-orig { font-size: 13px; color: var(--muted); text-decoration: line-through; font-family: monospace; }
.prod-remise { font-size: 11px; font-weight: 800; background: #fce4e4; color: var(--red); padding: 2px 8px; border-radius: 20px; }
.flash-badge-order { display: inline-flex; align-items: center; gap: 5px; background: linear-gradient(135deg,#dc2626,#f97316); color: #fff; font-size: 11px; font-weight: 800; letter-spacing: .3px; padding: 4px 12px; border-radius: 20px; margin-bottom: 8px; animation: flashPulseOrder 1.6s ease-in-out infinite; }
@keyframes flashPulseOrder { 0%,100% { opacity: 1; } 50% { opacity: .75; } }
.flash-countdown-order { display: inline-flex; align-items: center; gap: 6px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 12px; font-weight: 700; padding: 5px 11px; border-radius: 9px; margin-bottom: 12px; font-family: ui-monospace, monospace; }
.promo-banner-order { display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--teal-lt); border: 1.5px dashed #34d399; color: #065f46; font-size: 12.5px; font-weight: 700; padding: 9px 12px; border-radius: 10px; margin-bottom: 14px; cursor: pointer; transition: background .15s; }
.promo-banner-order:hover { background: #c8f4e2; }
.promo-banner-order strong { font-family: monospace; letter-spacing: .3px; }
.promo-banner-order-btn { flex-shrink: 0; background: var(--teal); color: #fff; font-size: 11px; font-weight: 800; padding: 5px 11px; border-radius: 20px; white-space: nowrap; }
.prod-desc { font-size: 13px; color: var(--muted); line-height: 1.65; margin-bottom: 12px; }

.prod-chips { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
.prod-chip { display: inline-flex; align-items: center; gap: 4px; font-size: 11.5px; font-weight: 600; color: var(--text-2); background: var(--grey); border: 1px solid var(--border); padding: 4px 10px; border-radius: var(--r-sm); }
.prod-chip.ok     { color: #065f46; background: var(--teal-lt); border-color: #6ee7b7; }
.prod-chip.amber  { color: #92400e; background: var(--amber-lt); border-color: #fde68a; }
.prod-chip.danger { color: var(--red); background: var(--rose-lt); border-color: #fca5a5; }

.variant-pill-order { padding: 8px 15px; border-radius: 20px; border: 1.5px solid var(--border); background: var(--surface); font-size: 12.5px; font-weight: 600; color: var(--text-2); cursor: pointer; transition: all .15s; font-family: var(--font); }
.variant-pill-order:hover:not(.disabled) { border-color: var(--brand); }
.variant-pill-order.selected { background: var(--brand); color: #fff; border-color: var(--brand-dk); }
.variant-pill-order.disabled { opacity: .45; text-decoration: line-through; cursor: not-allowed; }

/* Boutique mini */
.shop-mini { display: flex; align-items: center; gap: 10px; background: var(--grey); border: 1px solid var(--border); border-radius: var(--r-sm); padding: 10px 12px; }
.shop-mini-logo { width: 38px; height: 38px; border-radius: 9px; background: var(--surface); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 18px; overflow: hidden; flex-shrink: 0; }
.shop-mini-logo img { width: 100%; height: 100%; object-fit: cover; }
.shop-mini-name { font-size: 13px; font-weight: 700; color: var(--text); }
.shop-mini-type { font-size: 11px; color: var(--muted); }
.shop-mini-open { margin-left: auto; display: inline-flex; align-items: center; gap: 4px; background: var(--green); color: #fff; font-size: 10.5px; font-weight: 700; padding: 4px 10px; border-radius: 20px; white-space: nowrap; flex-shrink: 0; }
.shop-mini-open::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #a8f0d4; animation: pulse 1.8s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.3} }

/* ══ BANDEAU DE CONFIANCE (mini) ══ */
.trust-mini-row { display: flex; flex-wrap: wrap; gap: 8px 16px; margin-top: 12px; }
.trust-mini-item { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; color: var(--muted); }
.trust-mini-item .ico { color: var(--brand); }

/* ══ FORMULAIRE COMMANDE ══ */
.order-grid { display: grid; grid-template-columns: 1fr 280px; gap: 16px; align-items: start; }

.qty-row { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
.qty-label { font-size: 12px; font-weight: 700; color: var(--text-2); text-transform: uppercase; letter-spacing: .4px; }
.qty-ctrl { display: flex; align-items: center; border: 1.5px solid var(--border); border-radius: var(--r-sm); overflow: hidden; background: var(--surface); }
.qty-btn { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: var(--grey-2); border: none; cursor: pointer; font-size: 20px; font-weight: 700; color: var(--text); transition: background .12s; user-select: none; }
.qty-btn:hover { background: var(--brand-lt); color: var(--brand-dk); }
.qty-input { width: 56px; height: 40px; border: none; outline: none; text-align: center; font-size: 15px; font-weight: 800; font-family: monospace; color: var(--text); background: var(--surface); }

/* Box résumé */
.order-summary {
    background: var(--navy);
    border-radius: var(--r); padding: 18px;
    display: flex; flex-direction: column; gap: 12px;
    position: sticky; top: calc(var(--nav-h) + 16px);
}
.order-summary-title { font-family: var(--display); font-size: 14px; font-weight: 800; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .5px; }
.order-summary-prod { display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,.06); border-radius: var(--r-sm); padding: 10px; }
.order-summary-prod-img { width: 40px; height: 40px; border-radius: var(--r-sm); object-fit: cover; flex-shrink: 0; }
.order-summary-prod-ph { width: 40px; height: 40px; border-radius: var(--r-sm); background: rgba(255,255,255,.08); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.order-summary-prod-name { font-size: 12px; font-weight: 700; color: rgba(255,255,255,.8); }
.order-summary-sep { height: 1px; background: rgba(255,255,255,.1); }
.order-summary-row { display: flex; align-items: center; justify-content: space-between; font-size: 13px; }
.order-summary-row-lbl { color: rgba(255,255,255,.5); }
.order-summary-row-val { color: #fff; font-weight: 600; font-family: monospace; }
.order-total-row { display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,.07); border-radius: var(--r-sm); padding: 12px; }
.order-total-lbl { font-size: 12px; color: rgba(255,255,255,.5); font-weight: 700; text-transform: uppercase; letter-spacing: .4px; }
.order-total-val { font-size: 22px; font-weight: 900; color: var(--brand); font-family: monospace; letter-spacing: -.5px; }
.order-total-devise { font-size: 11px; color: rgba(255,255,255,.35); text-align: right; margin-top: 2px; }

.cash-notice { display: flex; align-items: center; gap: 8px; background: rgba(245,166,35,.12); border: 1px solid rgba(245,166,35,.25); border-radius: var(--r-sm); padding: 10px 12px; font-size: 12px; color: #fde68a; }

.submit-btn { width: 100%; padding: 13px; border-radius: 50px; border: none; font-size: 14px; font-weight: 800; font-family: var(--font); background: var(--brand); color: #fff; cursor: pointer; transition: all .15s; box-shadow: 0 4px 14px rgba(99,102,241,.4); display: flex; align-items: center; justify-content: center; gap: 7px; }
.submit-btn:hover { background: var(--brand-dk); color: #fff; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.5); }
.submit-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
.chat-btn { width: 100%; padding: 11px; border-radius: 50px; border: 1px solid rgba(255,255,255,.2); font-size: 13px; font-weight: 700; font-family: var(--font); background: transparent; color: rgba(255,255,255,.75); cursor: pointer; transition: all .15s; display: flex; align-items: center; justify-content: center; gap: 7px; text-decoration: none; }
.chat-btn:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.4); color: #fff; }

/* ══ LIGHTBOX ══ */
.lb-overlay { display: none; position: fixed; inset: 0; z-index: 9000; background: rgba(0,0,0,.96); align-items: center; justify-content: center; }
.lb-overlay.open { display: flex; }
.lb-overlay img { max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: var(--r); transition: opacity .18s ease; }
.lb-close { position: absolute; top: 16px; right: 16px; width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.lb-close:hover { background: rgba(255,255,255,.2); }
.lb-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 46px; height: 46px; border-radius: 50%; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .15s; font-family: sans-serif; }
.lb-nav:hover { background: rgba(255,255,255,.22); }
.lb-prev { left: 14px; }
.lb-next { right: 14px; }
.lb-counter { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,.5); color: rgba(255,255,255,.8); font-size: 12px; padding: 4px 14px; border-radius: 20px; font-family: monospace; }

/* ══ CHAMPS ADRESSE / TÉLÉPHONE ══ */
.field-group { margin-bottom: 14px; }
.field-label { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--text-2); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
.field-input { width: 100%; padding: 10px 12px; border: 1.5px solid var(--border); border-radius: var(--r-sm); font-size: 13.5px; font-family: var(--font); color: var(--text); background: var(--surface); outline: none; transition: border-color .15s; }
.field-input:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
.field-input.is-invalid { border-color: #fca5a5; }
.field-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(220,38,38,.12); }
.field-input.is-valid { border-color: #6ee7b7; }
.field-hint { display: none; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 600; margin-top: 5px; }
.field-hint.show { display: flex; }
.field-hint.error { color: var(--red); }
.field-hint.ok { color: #067d62; }

/* ══ RESPONSIVE ══ */
@media (max-width: 700px) {
    .order-grid { grid-template-columns: 1fr; }
    .order-summary { position: static; }
}
@media (max-width: 580px) {
    .prod-layout { flex-direction: column; }
    .prod-gallery-col { width: 100%; }
    .prod-main-img { height: 280px; }
    .prod-nav-btn { display: flex !important; }
    .prod-name { font-size: 18px; }
    .prod-price { font-size: 22px; }
    .nav-title { display: none; }
    .page-wrap { padding: 14px 12px 60px; }
    /* Anti-zoom iOS */
    .field-input, .qty-input { font-size: 16px !important; }
}
@media (max-width: 380px) {
    .prod-main-img { height: 210px; }
    .card-body { padding: 12px; }
    .prod-price { font-size: 20px; }
    .submit-btn { font-size: 13px; padding: 11px; }
    .qty-btn { width: 36px; height: 36px; }
    .qty-input { width: 48px; height: 36px; }
}
</style>
@endpush

@section('content')
@php
    $devise    = $product->shop->currency ?? 'GNF';
    $shop      = $product->shop;
    $hasPromo  = $product->original_price && $product->original_price > $product->price;
    $remise    = $hasPromo ? round((1 - $product->price / $product->original_price) * 100) : 0;
    $gallery   = json_decode($product->gallery ?? '[]', true) ?: [];
    $allPhotos = array_values(array_filter(array_merge(
        $product->image ? [asset('storage/'.$product->image)] : [],
        array_map(fn($g) => asset('storage/'.$g), $gallery)
    )));
    $stockVal  = $product->stock ?? null;
    // Si le produit gère des variantes, c'est leur stock qui fait foi, pas le stock global du produit
    $stockOut  = $variants->isEmpty() && $stockVal !== null && $stockVal <= 0;
    $stockLow  = $variants->isEmpty() && $stockVal !== null && $stockVal > 0 && $stockVal <= 5;
@endphp

{{-- LIGHTBOX --}}
<div class="lb-overlay" id="lbOverlay" onclick="if(event.target===this)closeLb()">
    <button class="lb-close" onclick="closeLb()">{!! \App\Support\IconLibrary::svg('x', '', 16) !!}</button>
    <button class="lb-nav lb-prev" id="lbPrev" onclick="lbNav(-1)">&#8249;</button>
    <img id="lbImg" src="" alt="">
    <button class="lb-nav lb-next" id="lbNext" onclick="lbNav(1)">&#8250;</button>
    <div class="lb-counter" id="lbCounter"></div>
</div>

{{-- NAVBAR --}}
<nav class="nav">
    <a href="{{ auth()->check() ? route('client.dashboard') : url('/') }}" class="nav-logo">
        <img src="/images/shopio-logo-192.png" alt="{{ config('app.name', 'Shopio') }}">
        {{ config('app.name', 'Shopio') }}
    </a>
    <a href="{{ auth()->check() ? route('client.shops.show', $shop) : route('public.shops.products', $shop) }}" class="nav-back">
        {!! \App\Support\IconLibrary::svg('arrow-left', '', 13) !!} {{ Str::limit($shop->name, 18) }}
    </a>
    <div class="nav-title">{{ Str::limit($product->name, 40) }}</div>
</nav>

<div class="page-wrap">

    {{-- FLASH --}}
    @if(session('success'))
    <div class="flash flash-success">{!! \App\Support\IconLibrary::svg('check', '', 15) !!}{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="flash flash-danger">{!! \App\Support\IconLibrary::svg('alert', '', 15) !!}{{ $errors->first() }}</div>
    @endif

    {{-- ══ FICHE PRODUIT ══ --}}
    <div class="card">
        <div class="card-hd">
            <div class="card-hd-ico">{!! \App\Support\IconLibrary::svg('tag', '', 14) !!}</div>
            <span class="card-title">Détail du produit</span>
        </div>
        <div class="card-body">
            <div class="prod-layout">

                {{-- Galerie --}}
                <div class="prod-gallery-col">
                    <div class="prod-main-img" id="mainImgWrap" onclick="openLb()">
                        @if($product->image)
                            <img src="{{ \App\Services\ImageOptimizer::url($product->image, 'medium') ?? asset('storage/'.$product->image) }}"
                                 srcset="{{ \App\Services\ImageOptimizer::url($product->image, 'medium') }} 800w,
                                         {{ \App\Services\ImageOptimizer::url($product->image, 'large') }} 1600w"
                                 sizes="(max-width:600px) 100vw, 400px"
                                 id="mainImg" alt="{{ $product->name }}">
                        @else
                            <div class="prod-main-img-ph">{!! \App\Support\IconLibrary::svg('tag', '', 46) !!}</div>
                        @endif
                        @if(count($allPhotos) > 1)
                        <button class="prod-nav-btn prod-nav-prev" id="photoPrev" onclick="event.stopPropagation();navPhoto(-1)">&#8249;</button>
                        <button class="prod-nav-btn prod-nav-next" id="photoNext" onclick="event.stopPropagation();navPhoto(1)">&#8250;</button>
                        @endif
                    </div>
                    @if(count($allPhotos) > 1)
                    <div class="prod-thumbs-wrap">
                        <div class="prod-thumbs" id="thumbsStrip">
                            @foreach($allPhotos as $i => $photo)
                            <img src="{{ $photo }}" class="prod-thumb {{ $i === 0 ? 'active' : '' }}"
                                 onclick="setPhoto({{ $i }})"
                                 alt="Photo {{ $i+1 }}" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Infos --}}
                <div class="prod-info">
                    @if($product->category)<div class="prod-cat">{{ $product->category }}</div>@endif
                    <h1 class="prod-name">{{ $product->name }}</h1>

                    @if($product->is_flash_active)
                    <div class="flash-badge-order">{!! \App\Support\IconLibrary::svg('zap', '', 12) !!} VENTE FLASH −{{ $product->flash_discount_percent }}%</div>
                    @endif
                    <div class="prod-price-row">
                        <span class="prod-price">{{ number_format($product->current_price, 0, ',', ' ') }}</span>
                        <span class="prod-devise">{{ $devise }}</span>
                        @if($product->is_flash_active)
                        <span class="prod-orig">{{ number_format($product->price, 0, ',', ' ') }}</span>
                        @elseif($hasPromo)
                        <span class="prod-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>
                        <span class="prod-remise">-{{ $remise }}%</span>
                        @endif
                    </div>
                    @if($product->is_flash_active)
                    <div class="flash-countdown-order" id="flashCountdownOrder" data-ends="{{ $product->flash_ends_at->timestamp }}">
                        {!! \App\Support\IconLibrary::svg('clock', '', 13) !!} Se termine dans <span id="flashTimerOrder">--:--:--</span>
                    </div>
                    @endif

                    @if($activePromo)
                    <div class="promo-banner-order" onclick="quickUsePromo()">
                        <span>{!! \App\Support\IconLibrary::svg('gift', '', 14) !!} Code <strong>{{ $activePromo->code }}</strong> :
                            {{ $activePromo->type === 'percent' ? '-' . $activePromo->value . '%' : '-' . number_format($activePromo->value, 0, ',', ' ') . ' ' . $devise }}
                            @if($activePromo->min_purchase_amount) dès {{ number_format($activePromo->min_purchase_amount, 0, ',', ' ') }} {{ $devise }} d'achat @endif
                        </span>
                        <span class="promo-banner-order-btn">Utiliser</span>
                    </div>
                    @endif

                    @if($variants->isNotEmpty())
                    <div id="variantPickerOrder" style="margin-bottom:14px">
                        <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px">Choisissez une option</div>
                        <div style="display:flex;flex-wrap:wrap;gap:8px">
                            <button type="button" class="variant-pill-order" id="defaultPhotoPill" onclick="showDefaultPhoto(this)">
                                {{ $product->name }}
                            </button>
                            @foreach($variants as $v)
                            <button type="button" class="variant-pill-order {{ (($selectedVariantId ?? null) == $v->id) ? 'selected' : '' }} {{ $v->out_of_stock ? 'disabled' : '' }}"
                                    data-id="{{ $v->id }}" data-name="{{ $v->name }}" data-price="{{ $v->effective_price }}" data-stock="{{ $v->stock }}" data-image="{{ $v->image_url }}"
                                    onclick="selectOrderVariant(this)" {{ $v->out_of_stock ? 'disabled' : '' }}>
                                {{ $v->name }}{{ $v->out_of_stock ? ' (épuisé)' : '' }}
                            </button>
                            @endforeach
                        </div>
                        <div id="variantOrderError" style="display:none;margin-top:8px;color:#b12704;font-size:12.5px;font-weight:600">Veuillez choisir une option ci-dessus.</div>
                    </div>
                    @endif

                    @if($product->description)
                    <p class="prod-desc">{{ $product->description }}</p>
                    @endif

                    <div class="prod-chips" id="stockChipsWrap">
                        @if($variants->isEmpty() && $stockVal !== null)
                            @if($stockOut)<span class="prod-chip danger">{!! \App\Support\IconLibrary::svg('x', '', 11) !!} Rupture de stock</span>
                            @elseif($stockLow)<span class="prod-chip amber">{!! \App\Support\IconLibrary::svg('alert', '', 11) !!} {{ $stockVal }} restants</span>
                            @else<span class="prod-chip ok">{!! \App\Support\IconLibrary::svg('check', '', 11) !!} En stock</span>@endif
                        @endif
                        @if($product->unit)<span class="prod-chip">{!! \App\Support\IconLibrary::svg('package', '', 11) !!} {{ $product->unit }}</span>@endif
                        @if($product->preparation_time)<span class="prod-chip">{!! \App\Support\IconLibrary::svg('clock', '', 11) !!} {{ $product->preparation_time }}min</span>@endif
                    </div>

                    <div class="shop-mini">
                        <div class="shop-mini-logo">
                            @if($shop->image)<img src="{{ asset('storage/'.$shop->image) }}" alt="">
                            @else{!! \App\Support\IconLibrary::svg('store', '', 18) !!}@endif
                        </div>
                        <div>
                            <div class="shop-mini-name">{{ $shop->name }}</div>
                            @if($shop->type)<div class="shop-mini-type">{{ $shop->type }}</div>@endif
                        </div>
                        <span class="shop-mini-open">Ouvert</span>
                    </div>

                    <div class="trust-mini-row">
                        <span class="trust-mini-item">{!! \App\Support\IconLibrary::svg('shield', '', 13) !!} Boutique vérifiée</span>
                        <span class="trust-mini-item">{!! \App\Support\IconLibrary::svg('truck', '', 13) !!} Livraison suivie</span>
                        <span class="trust-mini-item">{!! \App\Support\IconLibrary::svg('rotate', '', 13) !!} Retour facile</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ COMMANDE ══ --}}
    <div class="card">
        <div class="card-hd">
            <div class="card-hd-ico">{!! \App\Support\IconLibrary::svg('cart', '', 14) !!}</div>
            <span class="card-title">Passer la commande</span>
        </div>
        <div class="card-body">

            @if($stockOut)
            <div style="text-align:center;padding:28px 0">
                <div style="width:56px;height:56px;border-radius:50%;background:var(--rose-lt);color:var(--red);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">{!! \App\Support\IconLibrary::svg('package', '', 26) !!}</div>
                <div style="font-size:15px;font-weight:700;margin-bottom:6px">Produit indisponible</div>
                <div style="font-size:13px;color:var(--muted)">Ce produit est en rupture de stock.</div>
            </div>
            @else

            <form method="POST" action="{{ route('client.orders.storeProduct') }}" id="orderForm" data-ajax>
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variant_id" id="variantIdInput" value="{{ $selectedVariantId ?? '' }}">

               

                <div class="order-grid">
                    {{-- Gauche : quantité + adresse + téléphone --}}
                    <div>
                        <div class="qty-row">
                            <span class="qty-label">Quantité</span>
                            <div class="qty-ctrl">
                                <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                                <input type="number" name="quantity" id="qty" class="qty-input" value="1" min="1" max="{{ $variants->isNotEmpty() ? 999 : ($stockVal ?? 999) }}" oninput="updateTotal()">
                                <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                            </div>
                            @if($product->unit)
                            <span style="font-size:12px;color:var(--muted)">× {{ $product->unit }}</span>
                            @endif
                        </div>

                        @guest
                        <div class="field-group">
                            <label class="field-label" for="client_name">{!! \App\Support\IconLibrary::svg('user', '', 11) !!} Nom complet</label>
                            <input type="text" name="client_name" id="client_name" required
                                   class="field-input" placeholder="Ex : Mamadou Diallo"
                                   value="{{ old('client_name') }}"
                                   oninput="liveValidate(this, validateFullName)">
                            <div class="field-hint" id="client_name_hint"></div>
                        </div>
                        @endguest

                        <div class="field-group">
                            <label class="field-label" for="delivery_destination">{!! \App\Support\IconLibrary::svg('map-pin', '', 11) !!} Adresse de livraison</label>
                            <input type="text" name="delivery_destination" id="delivery_destination" @guest required @endguest
                                   class="field-input" placeholder="Ex : Quartier Almamya, en face du marché…"
                                   value="{{ old('delivery_destination', auth()->user()->address ?? '') }}">
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="client_phone">{!! \App\Support\IconLibrary::svg('phone', '', 11) !!} Téléphone</label>
                            <input type="tel" name="client_phone" id="client_phone" @guest required @endguest
                                   class="field-input" placeholder="Ex : 622 00 00 00"
                                   value="{{ old('client_phone', auth()->user()->phone ?? '') }}"
                                   oninput="liveValidate(this, validateGuineaPhone)">
                            <div class="field-hint" id="client_phone_hint"></div>
                        </div>

                        @if($stockLow)
                        <div style="display:flex;align-items:center;gap:6px;font-size:12.5px;color:#92400e;background:var(--amber-lt);border:1px solid #fde68a;border-radius:var(--r-sm);padding:8px 12px;margin-bottom:14px">
                            {!! \App\Support\IconLibrary::svg('alert', '', 13) !!} Plus que <strong>{{ $stockVal }}</strong> unité{{ $stockVal > 1 ? 's' : '' }} disponible{{ $stockVal > 1 ? 's' : '' }} — commandez vite !
                        </div>
                        @endif

                        <div style="font-size:13px;color:var(--green);display:flex;align-items:center;gap:6px;margin-bottom:14px">
                            {!! \App\Support\IconLibrary::svg('check', '', 14) !!} Livraison disponible — paiement à la réception
                        </div>
                    </div>

                    {{-- Droite : résumé + bouton --}}
                    <div class="order-summary">
                        <div class="order-summary-title">Votre commande</div>

                        <div class="order-summary-prod">
                            @if($product->image)
                                <img src="{{ \App\Services\ImageOptimizer::url($product->image, 'thumb') ?? asset('storage/'.$product->image) }}" class="order-summary-prod-img" alt="" loading="lazy">
                            @else
                                <div class="order-summary-prod-ph">{!! \App\Support\IconLibrary::svg('tag', '', 16) !!}</div>
                            @endif
                            <div style="min-width:0;overflow:hidden">
                                <div class="order-summary-prod-name">{{ Str::limit($product->name, 28) }}</div>
                                @if($variants->isNotEmpty())
                                <div id="summaryVariant" style="font-size:11px;font-weight:700;color:var(--brand);margin-top:3px">
                                    @if($selectedVariantId ?? null)
                                        {{ $variants->firstWhere('id', $selectedVariantId)?->name }}
                                    @else
                                        Choisissez une option ↑
                                    @endif
                                </div>
                                @endif
                                <div style="font-size:10px;color:rgba(255,255,255,.35);margin-top:2px">{{ $shop->name }}</div>
                            </div>
                        </div>

                        <div class="order-summary-sep"></div>

                        <div class="order-summary-row">
                            <span class="order-summary-row-lbl">Prix unitaire</span>
                            <span class="order-summary-row-val">{{ number_format($product->price, 0, ',', ' ') }} {{ $devise }}</span>
                        </div>
                        <div class="order-summary-row">
                            <span class="order-summary-row-lbl">Quantité</span>
                            <span class="order-summary-row-val" id="summaryQty">1</span>
                        </div>

                        @if($hasPromo)
                        <div class="order-summary-row">
                            <span class="order-summary-row-lbl" style="color:#fca5a5">Économie</span>
                            <span class="order-summary-row-val" style="color:#fca5a5">-{{ $remise }}%</span>
                        </div>
                        @endif

                        <div class="order-summary-sep"></div>

                        {{-- Code promo --}}
                        <div style="display:flex;gap:6px">
                            <input type="text" id="promoInput" placeholder="Code promo"
                                   style="flex:1;min-width:0;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.08);color:#fff;font-family:monospace;font-size:12.5px;text-transform:uppercase"
                                   oninput="this.value = this.value.toUpperCase()">
                            <button type="button" onclick="applyPromoCode()" style="padding:8px 14px;border-radius:6px;border:1px solid rgba(255,255,255,.2);background:transparent;color:var(--brand);font-size:11.5px;font-weight:700;cursor:pointer;white-space:nowrap">Appliquer</button>
                        </div>
                        <div id="promoMsg" style="display:none;font-size:11.5px;font-weight:600;margin-top:-4px"></div>
                        <input type="hidden" name="promo_code" id="promoCodeInput" value="">
                        <div class="order-summary-row" id="promoDiscountRow" style="display:none">
                            <span class="order-summary-row-lbl" style="color:#6ee7b7">Code promo</span>
                            <span class="order-summary-row-val" id="promoDiscountVal" style="color:#6ee7b7">-0</span>
                        </div>

                        @auth
                        @if(($loyaltyBalance ?? 0) > 0)
                        <div class="order-summary-sep"></div>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12.5px;color:rgba(255,255,255,.85);font-weight:600">
                            <input type="checkbox" id="usePointsChk" onchange="togglePoints()" style="width:15px;height:15px;accent-color:var(--brand)">
                            {!! \App\Support\IconLibrary::svg('gift', '', 14) !!} Utiliser mes points (solde : {{ number_format($loyaltyBalance, 0, ',', ' ') }})
                        </label>
                        <div id="pointsRow" style="display:none;align-items:center;gap:8px;margin-top:8px">
                            <input type="number" id="pointsInput" name="points_to_use" value="0" min="0"
                                   style="width:90px;padding:7px 9px;border-radius:6px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.08);color:#fff;font-family:monospace;font-size:13px"
                                   oninput="onPointsInput()">
                            <button type="button" onclick="usePointsMax()" style="padding:7px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.2);background:transparent;color:var(--brand);font-size:11.5px;font-weight:700;cursor:pointer">MAX</button>
                        </div>
                        <div class="order-summary-row" id="pointsDiscountRow" style="display:none">
                            <span class="order-summary-row-lbl" style="color:#6ee7b7">Réduction points</span>
                            <span class="order-summary-row-val" id="pointsDiscountVal" style="color:#6ee7b7">-0</span>
                        </div>
                        @endif
                        @endauth

                        <div class="order-summary-sep"></div>

                        <div class="order-total-row">
                            <div>
                                <div class="order-total-lbl">Total</div>
                                <div class="order-total-devise">{{ $devise }}</div>
                            </div>
                            <div style="text-align:right">
                                <div class="order-total-val" id="totalDisplay">{{ number_format($product->price, 0, ',', ' ') }}</div>
                            </div>
                        </div>

                        <div class="cash-notice">
                            {!! \App\Support\IconLibrary::svg('wallet', '', 15) !!} <span>Cash à la livraison — aucune carte requise</span>
                        </div>

                        <button type="submit" class="submit-btn" id="submitBtn">
                            {!! \App\Support\IconLibrary::svg('cart', '', 16) !!} Valider ma commande
                        </button>

                        @auth
                        <a href="{{ route('client.messages.index', $product) }}" class="chat-btn">
                            {!! \App\Support\IconLibrary::svg('message', '', 15) !!} Poser une question au vendeur
                        </a>
                        @else
                        @php $msgUrl = route('client.messages.index', $product); @endphp
                        <a href="{{ route('register', ['redirect' => $msgUrl, 'role' => 'client']) }}" class="chat-btn">
                            {!! \App\Support\IconLibrary::svg('message', '', 15) !!} Poser une question au vendeur
                        </a>
                        @endauth
                    </div>
                </div>
            </form>
            @endif

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const HAS_VARIANTS = {{ $variants->isNotEmpty() ? 'true' : 'false' }};
const BASE_PRICE = {{ (float) $product->current_price }};
const BASE_STOCK = {{ $variants->isNotEmpty() ? 999 : ($stockVal ?? 999) }};
let PRICE = BASE_PRICE;
let STOCK = BASE_STOCK;
const LOYALTY_BALANCE = {{ (int) ($loyaltyBalance ?? 0) }};
const MAX_REDEEM_RATIO = 0.5;
const PROMO_CHECK_URL = @json(route('client.orders.promo.check'));
const PROMO_CSRF = @json(csrf_token());
const PROMO_SHOP_ID = {{ (int) $product->shop->id }};
const ACTIVE_PROMO_CODE = @json($activePromo->code ?? null);
let promoDiscount = 0;

function quickUsePromo() {
    const input = document.getElementById('promoInput');
    if (!ACTIVE_PROMO_CODE || !input) return;
    input.value = ACTIVE_PROMO_CODE;
    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    applyPromoCode();
}

/* ══ COMPTE À REBOURS VENTE FLASH ══ */
(function(){
    const el = document.getElementById('flashCountdownOrder');
    if(!el) return;
    const endsAt = parseInt(el.dataset.ends, 10) * 1000;
    const timerEl = document.getElementById('flashTimerOrder');

    function tick(){
        const diff = endsAt - Date.now();
        if(diff <= 0){
            timerEl.textContent = 'Terminée';
            clearInterval(_flashOrderInterval);
            return;
        }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        const pad = n => String(n).padStart(2,'0');
        timerEl.textContent = (d > 0 ? d+'j ' : '') + pad(h)+':'+pad(m)+':'+pad(s);
    }
    tick();
    var _flashOrderInterval = setInterval(tick, 1000);
})();

/* ══ VARIANTES ══ */
function showDefaultPhoto(btn) {
    document.querySelectorAll('.variant-pill-order').forEach(p => p.classList.remove('selected'));
    btn.classList.add('selected');

    // Ce n'est pas une vraie option : on vide la sélection (le client devra choisir une couleur/taille pour commander)
    document.getElementById('variantIdInput').value = '';
    PRICE = BASE_PRICE;
    STOCK = BASE_STOCK;

    const qtyInput = document.getElementById('qty');
    if (qtyInput) qtyInput.max = STOCK;

    const mainImg = document.getElementById('mainImg');
    if (mainImg && typeof PHOTOS !== 'undefined' && PHOTOS[0]) {
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.removeAttribute('srcset');
            mainImg.removeAttribute('sizes');
            mainImg.src = PHOTOS[0];
            mainImg.style.opacity = '1';
        }, 150);
    }

    const priceEl = document.querySelector('.prod-price');
    if (priceEl) priceEl.textContent = Math.round(PRICE).toLocaleString('fr-FR');

    const summaryVariant = document.getElementById('summaryVariant');
    if (summaryVariant) summaryVariant.textContent = 'Choisissez une option ↑';

    updateTotal();
}

function selectOrderVariant(btn) {
    if (btn.disabled) return;
    document.querySelectorAll('.variant-pill-order').forEach(p => p.classList.remove('selected'));
    btn.classList.add('selected');

    document.getElementById('variantIdInput').value = btn.dataset.id;
    PRICE = parseFloat(btn.dataset.price);
    STOCK = parseInt(btn.dataset.stock);

    const qtyInput = document.getElementById('qty');
    if (qtyInput) {
        qtyInput.max = STOCK;
        if (parseInt(qtyInput.value || 1) > STOCK) qtyInput.value = Math.max(1, STOCK);
    }

    const priceEl = document.querySelector('.prod-price');
    if (priceEl) priceEl.textContent = Math.round(PRICE).toLocaleString('fr-FR');

    // Met à jour l'option affichée dans le résumé "Votre commande"
    const summaryVariant = document.getElementById('summaryVariant');
    if (summaryVariant) summaryVariant.textContent = btn.dataset.name;

    // Si la variante a sa propre photo, on l'affiche ; sinon on revient à la photo par défaut du produit
    const mainImg = document.getElementById('mainImg');
    if (mainImg) {
        const nextSrc = btn.dataset.image || (typeof PHOTOS !== 'undefined' && PHOTOS[0]) || mainImg.src;
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.removeAttribute('srcset');
            mainImg.removeAttribute('sizes');
            mainImg.src = nextSrc;
            mainImg.style.opacity = '1';
        }, 150);

        // Affiche le bouton "Revoir la photo du produit" seulement si la variante a sa propre photo
        const resetBtn = document.getElementById('resetPhotoBtn');
        if (resetBtn) resetBtn.style.display = btn.dataset.image ? 'block' : 'none';
    }

    const err = document.getElementById('variantOrderError');
    if (err) err.style.display = 'none';

    updateTotal();
}

@if($variants->isNotEmpty() && ($selectedVariantId ?? null))
document.addEventListener('DOMContentLoaded', function () {
    const preselected = document.querySelector('.variant-pill-order.selected');
    if (preselected) selectOrderVariant(preselected);
});
@endif

function changeQty(d) {
    const inp = document.getElementById('qty');
    if (!inp) return;
    let v = parseInt(inp.value || 1) + d;
    v = Math.max(1, Math.min(STOCK, v));
    inp.value = v;
    updateTotal();
}

function currentMaxPoints(subtotal) {
    return Math.max(0, Math.min(LOYALTY_BALANCE, Math.floor(subtotal * MAX_REDEEM_RATIO)));
}

function showPromoMsg(text, ok) {
    const el = document.getElementById('promoMsg');
    el.textContent = text;
    el.style.color = ok ? '#6ee7b7' : '#fca5a5';
    el.style.display = 'block';
}

function applyPromoCode() {
    const code = document.getElementById('promoInput').value.trim();
    if (!code) return;

    const qty = parseInt(document.getElementById('qty')?.value || 1);
    const subtotal = Math.round(PRICE * qty);

    fetch(PROMO_CHECK_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': PROMO_CSRF },
        body: JSON.stringify({ code, shop_id: PROMO_SHOP_ID, subtotal }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.valid) {
            promoDiscount = data.discount;
            document.getElementById('promoCodeInput').value = code;
            showPromoMsg('✓ Code appliqué', true);
        } else {
            promoDiscount = 0;
            document.getElementById('promoCodeInput').value = '';
            showPromoMsg(data.message || 'Code invalide', false);
        }
        updateTotal();
    })
    .catch(() => showPromoMsg('Erreur de vérification, réessayez.', false));
}

function togglePoints() {
    const chk = document.getElementById('usePointsChk');
    const row = document.getElementById('pointsRow');
    if (!chk || !row) return;
    row.style.display = chk.checked ? 'flex' : 'none';
    if (!chk.checked) document.getElementById('pointsInput').value = 0;
    updateTotal();
}

function onPointsInput() {
    updateTotal();
}

function usePointsMax() {
    const qty = parseInt(document.getElementById('qty')?.value || 1);
    const afterPromo = Math.max(0, Math.round(PRICE * qty) - promoDiscount);
    document.getElementById('pointsInput').value = currentMaxPoints(afterPromo);
    updateTotal();
}

function updateTotal() {
    const qty = parseInt(document.getElementById('qty')?.value || 1);
    const subtotal = Math.round(PRICE * qty);

    // Le code promo se réévalue seulement au clic "Appliquer" (montant figé jusqu'à la prochaine vérification)
    if (promoDiscount > 0) {
        document.getElementById('promoDiscountRow').style.display = 'flex';
        document.getElementById('promoDiscountVal').textContent = '-' + Math.round(promoDiscount).toLocaleString('fr-FR');
    } else {
        document.getElementById('promoDiscountRow').style.display = 'none';
    }

    const afterPromo = Math.max(0, subtotal - promoDiscount);

    let pointsUsed = 0;
    const chk = document.getElementById('usePointsChk');
    if (chk && chk.checked) {
        const input = document.getElementById('pointsInput');
        const max = currentMaxPoints(afterPromo);
        pointsUsed = Math.max(0, Math.min(parseInt(input.value || 0), max));
        input.value = pointsUsed;
        document.getElementById('pointsDiscountRow').style.display = 'flex';
        document.getElementById('pointsDiscountVal').textContent = '-' + pointsUsed.toLocaleString('fr-FR');
    }

    const total = Math.max(0, afterPromo - pointsUsed);
    const el = document.getElementById('totalDisplay');
    if (el) el.textContent = total.toLocaleString('fr-FR');
    const sq = document.getElementById('summaryQty');
    if (sq) sq.textContent = qty;
}

/* ══ GALERIE ══ */
const PHOTOS = @json($allPhotos);
let photoIdx = 0, lbIdx = 0;

/* Précharge toutes les photos dès l'arrivée sur la page pour un défilement instantané */
PHOTOS.forEach(src => { (new Image()).src = src; });

function setPhoto(idx) {
    photoIdx = Math.max(0, Math.min(PHOTOS.length - 1, idx));
    const img = document.getElementById('mainImg');
    if (img) {
        img.style.opacity = '0';
        const preload = new Image();
        preload.onload = () => {
            img.removeAttribute('srcset');
            img.removeAttribute('sizes');
            img.src = PHOTOS[photoIdx];
            img.style.opacity = '1';
        };
        preload.src = PHOTOS[photoIdx];
    }
    document.querySelectorAll('.prod-thumb').forEach((t, i) => {
        t.classList.toggle('active', i === photoIdx);
        if (i === photoIdx) t.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    });
    const prev = document.getElementById('photoPrev');
    const next = document.getElementById('photoNext');
    if (prev) prev.style.display = PHOTOS.length > 1 ? 'flex' : 'none';
    if (next) next.style.display = PHOTOS.length > 1 ? 'flex' : 'none';
}

function navPhoto(dir) { setPhoto(photoIdx + dir); }

/* ══ LIGHTBOX ══ */
function openLb() {
    if (!PHOTOS.length) return;
    lbIdx = photoIdx;
    _lbRender();
    document.getElementById('lbOverlay').classList.add('open');
}
function closeLb() { document.getElementById('lbOverlay').classList.remove('open'); }
function lbNav(dir) {
    lbIdx = Math.max(0, Math.min(PHOTOS.length - 1, lbIdx + dir));
    _lbRender();
}
function _lbRender() {
    const img = document.getElementById('lbImg');
    img.style.opacity = '0';
    const preload = new Image();
    preload.onload = () => { img.src = PHOTOS[lbIdx]; img.style.opacity = '1'; };
    preload.src = PHOTOS[lbIdx];
    const prev = document.getElementById('lbPrev');
    const next = document.getElementById('lbNext');
    if (prev) prev.style.display = lbIdx === 0 ? 'none' : 'flex';
    if (next) next.style.display = lbIdx >= PHOTOS.length - 1 ? 'none' : 'flex';
    const counter = document.getElementById('lbCounter');
    if (counter) counter.textContent = (lbIdx + 1) + ' / ' + PHOTOS.length;
    if (PHOTOS.length <= 1 && counter) counter.style.display = 'none';
}

document.addEventListener('keydown', e => {
    const lb = document.getElementById('lbOverlay');
    if (!lb?.classList.contains('open')) return;
    if (e.key === 'Escape') closeLb();
    if (e.key === 'ArrowLeft')  lbNav(-1);
    if (e.key === 'ArrowRight') lbNav(1);
});

/* ══ SWIPE TACTILE ══ */
let tsX = 0, tsY = 0;
const mw = document.getElementById('mainImgWrap');
if (mw) {
    mw.addEventListener('touchstart', e => { tsX = e.touches[0].clientX; tsY = e.touches[0].clientY; }, {passive:true});
    mw.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - tsX;
        const dy = e.changedTouches[0].clientY - tsY;
        if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) && PHOTOS.length > 1) navPhoto(dx < 0 ? 1 : -1);
    }, {passive:true});
}

/* Init flèches */
if (PHOTOS.length > 1) {
    const prev = document.getElementById('photoPrev');
    const next = document.getElementById('photoNext');
    if (prev) prev.style.display = 'flex';
    if (next) next.style.display = 'flex';
}

/* ══ VALIDATION EN DIRECT (nom / téléphone) ══
 * Reflète côté client les mêmes règles que le serveur (RealisticGuineaPhone /
 * RealisticFullName), pour prévenir tout de suite un client qui tape n'importe
 * quoi — au lieu de le découvrir seulement après l'envoi du formulaire. Le
 * serveur reste la vraie barrière (cette vérification JS est juste un confort). */
function validateGuineaPhone(value) {
    const digits = (value || '').replace(/\D+/g, '').replace(/^224(\d{9})$/, '$1');
    if (!/^6\d{8}$/.test(digits)) {
        return "Numéro guinéen invalide (9 chiffres commençant par 6). Ex : 622 00 00 00.";
    }
    if (new Set(digits.split('')).size <= 2) {
        return "Ce numéro ne semble pas valide.";
    }
    const diffs = [...digits].slice(1).map((d, i) => parseInt(d, 10) - parseInt(digits[i], 10));
    if (new Set(diffs).size === 1 && (diffs[0] === 1 || diffs[0] === -1)) {
        return "Ce numéro ne semble pas valide.";
    }
    return null;
}

function validateFullName(value) {
    const name = (value || '').trim();
    const words = name.split(/\s+/).filter(Boolean);
    if (words.length < 2) return "Indiquez votre prénom et votre nom.";
    if (words.some(w => w.length < 2)) return "Prénom et nom complets requis.";
    if (!/[aeiouyàâäéèêëîïôöùûü]/i.test(name)) return "Ce nom ne semble pas valide.";
    if (/(.)\1{3,}/i.test(name)) return "Ce nom ne semble pas valide.";
    return null;
}

function liveValidate(input, validator) {
    const hint = document.getElementById(input.id + '_hint');
    const value = input.value.trim();
    input.classList.remove('is-valid', 'is-invalid');
    if (!hint) return;
    if (!value) { hint.className = 'field-hint'; return; }

    const error = validator(value);
    if (error) {
        input.classList.add('is-invalid');
        hint.textContent = error;
        hint.className = 'field-hint show error';
    } else {
        input.classList.add('is-valid');
        hint.textContent = '✓ OK';
        hint.className = 'field-hint show ok';
    }
}

/* Submit */
document.getElementById('orderForm')?.addEventListener('submit', (e) => {
    // Il faut avoir fait un choix : soit une vraie option (couleur/taille), soit "aucune préférence" (pastille nom du produit)
    const hasVariant  = !!document.getElementById('variantIdInput').value;
    const noPreference = document.getElementById('defaultPhotoPill')?.classList.contains('selected');
    if (HAS_VARIANTS && !hasVariant && !noPreference) {
        e.preventDefault();
        const err = document.getElementById('variantOrderError');
        if (err) err.style.display = 'block';
        document.getElementById('variantPickerOrder')?.scrollIntoView({behavior:'smooth', block:'center'});
        return;
    }

    // Bloque l'envoi si le nom ou le téléphone est manifestement invalide
    // (le serveur revérifie de toute façon — ceci évite juste un aller-retour inutile)
    const nameInput  = document.getElementById('client_name');
    const phoneInput = document.getElementById('client_phone');
    let firstInvalid = null;

    if (nameInput && nameInput.value.trim() && validateFullName(nameInput.value)) {
        liveValidate(nameInput, validateFullName);
        firstInvalid = firstInvalid || nameInput;
    }
    if (phoneInput && phoneInput.value.trim() && validateGuineaPhone(phoneInput.value)) {
        liveValidate(phoneInput, validateGuineaPhone);
        firstInvalid = firstInvalid || phoneInput;
    }
    if (firstInvalid) {
        e.preventDefault();
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus();
        return;
    }

    const btn = document.getElementById('submitBtn');
    if (btn) { btn.disabled = true; btn.innerHTML = '{!! \App\Support\IconLibrary::svg("clock", "", 15) !!} Envoi en cours…'; }
});
</script>
@endpush