{{--
    resources/views/client/orders/create_from_product.blade.php
    Route     : GET  /client/orders/create-from-product/{product}
                     → Client\OrderController@createFromProduct
    Variables :
      $product  → Product (avec shop, image, gallery, price, description…)
      $messages → Collection<ShopMessage> (messages du chat)
      $devise   → string
--}}
@extends('layouts.app')
@section('title', 'Commander · ' . $product->name)
@php $bodyClass = 'order-page'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
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
    --border:   rgba(0,0,0,.08);

    --teal:     #0ea472;
    --teal-dk:  #0d7a55;
    --teal-lt:  #d1f5e8;
    --teal-mlt: #edfdf5;
    --amber:    #f5a623;
    --amber-lt: #fef3d7;
    --rose:     #f24f60;
    --rose-lt:  #ffe0e3;
    --blue:     #3b82f6;
    --blue-lt:  #dbeafe;

    --font:     'DM Sans', sans-serif;
    --display:  'Syne', sans-serif;
    --r:        14px;
    --r-sm:     9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
    --shadow:    0 4px 20px rgba(0,0,0,.08);
    --shadow-lg: 0 12px 40px rgba(0,0,0,.14);
    --nav-h:     58px;
}

html { font-family: var(--font); scroll-behavior: smooth; }
body { background: var(--fog-lt); margin: 0; color: var(--ink); -webkit-font-smoothing: antialiased; }

/* ══ TOPBAR ══ */
.op-topbar {
    position: sticky; top: 0; z-index: 100;
    height: var(--nav-h);
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 20px; gap: 12px;
}
.op-back {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 7px 14px; border-radius: 50px;
    font-size: 12.5px; font-weight: 600; font-family: var(--font);
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--ink-3); text-decoration: none; transition: all .15s;
}
.op-back:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-mlt); }
.op-topbar-info { flex: 1; min-width: 0; }
.op-topbar-title {
    font-family: var(--display); font-size: 14px; font-weight: 700;
    color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.op-topbar-sub { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* ══ LAYOUT ══ */
.op-wrap {
    max-width: 1060px; margin: 0 auto;
    padding: 28px 20px 80px;
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    align-items: start;
}

/* ══ FLASH ══ */
.op-flash {
    grid-column: 1/-1;
    padding: 12px 16px; border-radius: var(--r-sm); border: 1px solid;
    font-size: 13px; font-weight: 500;
    display: flex; align-items: center; gap: 8px;
}
.op-flash-success { background: var(--teal-lt); border-color: #6ee7b7; color: #065f46; }
.op-flash-danger  { background: var(--rose-lt); border-color: #fca5a5; color: #991b1b; }

/* ══ CARD ══ */
.op-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 18px;
}
.op-card-hd {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    background: var(--fog-lt);
    display: flex; align-items: center; gap: 10px;
}
.op-card-hd-ico {
    width: 28px; height: 28px; border-radius: 8px;
    background: var(--teal-lt); border: 1px solid #6ee7b7;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; flex-shrink: 0;
}
.op-card-title { font-family: var(--display); font-size: 14px; font-weight: 700; color: var(--ink); }
.op-card-body { padding: 18px; }

/* ══ PRODUIT ══ */
.op-prod {
    display: flex; gap: 18px; align-items: flex-start;
}
.op-prod-imgs { flex-shrink: 0; }
.op-prod-main-img {
    width: 180px; height: 180px;
    border-radius: var(--r); overflow: hidden;
    background: var(--fog-lt); border: 1px solid var(--border);
    cursor: zoom-in; position: relative;
}
.op-prod-main-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s;
}
.op-prod-main-img:hover img { transform: scale(1.06); }
.op-prod-main-img-ph {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 40px; opacity: .3;
}
.op-prod-gallery {
    display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;
}
.op-prod-thumb {
    width: 44px; height: 44px; border-radius: var(--r-sm);
    object-fit: cover; border: 2px solid transparent;
    cursor: pointer; transition: all .15s; opacity: .7;
}
.op-prod-thumb:hover, .op-prod-thumb.active {
    border-color: var(--teal); opacity: 1;
    transform: scale(1.08);
}

.op-prod-info { flex: 1; min-width: 0; }
.op-prod-cat {
    font-size: 10.5px; font-weight: 700; color: var(--teal);
    text-transform: uppercase; letter-spacing: .7px; margin-bottom: 6px;
}
.op-prod-name {
    font-family: var(--display); font-size: clamp(16px, 3vw, 22px);
    font-weight: 800; color: var(--ink); line-height: 1.2;
    margin-bottom: 10px; letter-spacing: -.3px;
}
.op-prod-price-row {
    display: flex; align-items: baseline; gap: 10px; margin-bottom: 12px;
}
.op-prod-price {
    font-size: 26px; font-weight: 800; font-family: monospace;
    color: var(--teal); letter-spacing: -.8px;
}
.op-prod-currency { font-size: 12px; color: var(--muted); font-weight: 600; }
.op-prod-price-orig {
    font-size: 14px; font-family: monospace;
    color: var(--muted); text-decoration: line-through;
}
.op-prod-remise {
    font-size: 11px; font-weight: 800;
    background: var(--rose-lt); color: var(--rose);
    padding: 3px 8px; border-radius: 20px;
}
.op-prod-desc {
    font-size: 13px; color: var(--muted); line-height: 1.65;
    margin-bottom: 14px;
}
.op-prod-chips { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
.op-prod-chip {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 600; color: var(--ink-3);
    background: var(--fog-lt); border: 1px solid var(--border);
    padding: 3px 9px; border-radius: var(--r-sm);
}
.op-prod-chip.ok     { color: #065f46; background: var(--teal-lt); border-color: #6ee7b7; }
.op-prod-chip.amber  { color: #92400e; background: var(--amber-lt); border-color: #fde68a; }
.op-prod-chip.danger { color: #991b1b; background: var(--rose-lt); border-color: #fca5a5; }

/* ══ BOUTIQUE INFO ══ */
.op-shop-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px;
    background: var(--fog-lt); border-radius: var(--r-sm);
    border: 1px solid var(--border);
}
.op-shop-logo {
    width: 38px; height: 38px; border-radius: 10px;
    background: var(--surface); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; overflow: hidden; flex-shrink: 0;
}
.op-shop-logo img { width: 100%; height: 100%; object-fit: cover; }
.op-shop-name { font-weight: 700; font-size: 13px; color: var(--ink); }
.op-shop-type { font-size: 11px; color: var(--muted); margin-top: 1px; }
.op-shop-open {
    margin-left: auto; display: inline-flex; align-items: center; gap: 5px;
    background: var(--teal); color: #fff;
    font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
}

/* ══ FORMULAIRE COMMANDE ══ */
.op-qty-row { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
.op-qty-lbl { font-size: 12.5px; font-weight: 700; color: var(--ink-3); text-transform: uppercase; letter-spacing: .4px; }
.op-qty-ctrl {
    display: flex; align-items: center;
    border: 1.5px solid var(--border); border-radius: var(--r-sm);
    overflow: hidden; background: var(--surface);
}
.op-qty-btn {
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    background: var(--fog-lt); border: none; cursor: pointer;
    font-size: 16px; font-weight: 700; color: var(--ink-3);
    transition: background .12s;
    user-select: none;
}
.op-qty-btn:hover { background: var(--teal-lt); color: var(--teal); }
.op-qty-input {
    width: 52px; height: 36px;
    border: none; outline: none; text-align: center;
    font-size: 14px; font-weight: 700; font-family: monospace;
    color: var(--ink); background: var(--surface);
}
/* Total dynamique */
.op-total-preview {
    background: linear-gradient(135deg, #0a2018, #0d4a32);
    border-radius: var(--r-sm); padding: 14px 16px;
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px;
}
.op-total-lbl { font-size: 12px; font-weight: 600; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .4px; }
.op-total-val {
    font-family: monospace; font-size: 22px; font-weight: 800;
    color: #fff; letter-spacing: -.5px;
}
.op-total-devise { font-size: 11px; color: rgba(255,255,255,.4); font-weight: 500; }

.op-cash-notice {
    display: flex; align-items: center; gap: 10px;
    background: var(--amber-lt); border: 1px solid #fde68a;
    border-radius: var(--r-sm); padding: 10px 14px;
    font-size: 12.5px; color: #92400e; font-weight: 500;
    margin-bottom: 16px;
}

/* Bouton commander */
.op-submit-btn {
    width: 100%; padding: 14px;
    border-radius: var(--r-sm); border: none;
    font-size: 15px; font-weight: 800; font-family: var(--font);
    background: var(--teal); color: #fff; cursor: pointer;
    transition: all .15s;
    box-shadow: 0 4px 16px rgba(14,164,114,.35);
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.op-submit-btn:hover { background: var(--teal-dk); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14,164,114,.45); }
.op-submit-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* ══ CHAT ══ */
.op-chat-wrap {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    display: flex; flex-direction: column;
    height: 520px;
    position: sticky; top: calc(var(--nav-h) + 16px);
}
.op-chat-hd {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    background: var(--ink);
    display: flex; align-items: center; gap: 10px;
    flex-shrink: 0;
}
.op-chat-hd-av {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--teal), #0d7a55);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800; color: #fff;
    flex-shrink: 0; overflow: hidden;
}
.op-chat-hd-av img { width: 100%; height: 100%; object-fit: cover; }
.op-chat-hd-name { font-size: 13px; font-weight: 700; color: #fff; }
.op-chat-hd-status {
    font-size: 10.5px; color: rgba(255,255,255,.45);
    display: flex; align-items: center; gap: 5px; margin-top: 1px;
}
.op-chat-hd-status::before {
    content: ''; width: 5px; height: 5px; border-radius: 50%;
    background: var(--teal);
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

/* Produit résumé dans le chat */
.op-chat-prod-bar {
    padding: 10px 14px;
    background: var(--fog-lt);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px;
    flex-shrink: 0;
}
.op-chat-prod-img {
    width: 40px; height: 40px; border-radius: var(--r-sm);
    object-fit: cover; flex-shrink: 0;
    border: 1px solid var(--border);
}
.op-chat-prod-name { font-size: 12px; font-weight: 700; color: var(--ink); }
.op-chat-prod-price { font-size: 11px; font-weight: 800; color: var(--teal); font-family: monospace; }

/* Historique messages */
.op-chat-msgs {
    flex: 1; overflow-y: auto; padding: 14px;
    display: flex; flex-direction: column; gap: 10px;
    scroll-behavior: smooth;
}
.op-chat-msgs::-webkit-scrollbar { width: 4px; }
.op-chat-msgs::-webkit-scrollbar-thumb { background: var(--fog); border-radius: 4px; }

/* Bulle message */
.op-msg {
    display: flex; gap: 8px; max-width: 90%;
    animation: msgIn .25s ease;
}
@keyframes msgIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
.op-msg.mine { margin-left: auto; flex-direction: row-reverse; }
.op-msg-av {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 800; color: #fff; flex-shrink: 0;
    align-self: flex-end;
}
.op-msg.mine .op-msg-av { background: linear-gradient(135deg, var(--teal), #0d7a55); }
.op-msg:not(.mine) .op-msg-av { background: linear-gradient(135deg, var(--ink-2), #3d4f46); }
.op-msg-bubble {
    padding: 9px 13px; border-radius: 16px;
    font-size: 13px; line-height: 1.5; color: var(--ink);
    max-width: 100%;
}
.op-msg.mine .op-msg-bubble {
    background: var(--teal); color: #fff;
    border-bottom-right-radius: 4px;
}
.op-msg:not(.mine) .op-msg-bubble {
    background: var(--fog-lt); border: 1px solid var(--border);
    border-bottom-left-radius: 4px;
}
.op-msg-time {
    font-size: 9.5px; color: var(--muted); margin-top: 3px;
    text-align: right;
}
.op-msg.mine .op-msg-time { text-align: right; }
.op-msg:not(.mine) .op-msg-time { text-align: left; }
.op-msg-read { font-size: 9px; color: rgba(255,255,255,.6); }

/* Chat vide */
.op-chat-empty {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 24px; text-align: center; gap: 8px;
}
.op-chat-empty-ico { font-size: 36px; opacity: .3; }
.op-chat-empty-title { font-size: 13px; font-weight: 700; color: var(--ink-3); }
.op-chat-empty-sub { font-size: 12px; color: var(--muted); line-height: 1.5; }

/* Zone de saisie */
.op-chat-input-wrap {
    padding: 10px 12px;
    border-top: 1px solid var(--border);
    background: var(--surface);
    flex-shrink: 0;
}
.op-chat-form {
    display: flex; gap: 8px; align-items: flex-end;
}
.op-chat-textarea {
    flex: 1;
    padding: 10px 13px; border: 1.5px solid var(--border);
    border-radius: 20px; font-size: 13px; font-family: var(--font);
    color: var(--ink); background: var(--fog-lt); outline: none;
    resize: none; min-height: 40px; max-height: 100px;
    line-height: 1.4; transition: border-color .15s;
    overflow-y: auto;
}
.op-chat-textarea:focus {
    border-color: var(--teal); background: var(--surface);
    box-shadow: 0 0 0 3px rgba(14,164,114,.1);
}
.op-chat-send {
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--teal); color: #fff; border: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
    transition: all .15s; box-shadow: 0 2px 8px rgba(14,164,114,.3);
}
.op-chat-send:hover { background: var(--teal-dk); transform: scale(1.08); }
.op-chat-send:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* ══ LIGHTBOX ══ */
.op-lb {
    display: none; position: fixed; inset: 0; z-index: 9000;
    background: rgba(0,0,0,.95); align-items: center; justify-content: center;
}
.op-lb.open { display: flex; }
.op-lb img { max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: 8px; }
.op-lb-close {
    position: absolute; top: 16px; right: 16px;
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
    color: #fff; font-size: 18px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .op-wrap { grid-template-columns: 1fr; gap: 18px; padding: 18px 14px 60px; }
    .op-chat-wrap { height: 420px; position: static; }
}
@media (max-width: 580px) {
    .op-prod { flex-direction: column; gap: 14px; }
    .op-prod-main-img { width: 100%; height: 220px; }
    .op-prod-price { font-size: 22px; }
    .op-topbar { padding: 0 12px; }
    .op-chat-wrap { height: 380px; }
}
</style>
@endpush

@section('content')

@php
    $devise     = $product->shop->currency ?? 'GNF';
    $shop       = $product->shop;
    $vendeur    = $shop->user;
    $hasPromo   = $product->original_price && $product->original_price > $product->price;
    $remise     = $hasPromo ? round((1 - $product->price / $product->original_price) * 100) : 0;
    $gallery    = json_decode($product->gallery ?? '[]', true) ?: [];
    $allPhotos  = array_filter(array_merge(
        $product->image ? [asset('storage/'.$product->image)] : [],
        array_map(fn($g) => asset('storage/'.$g), $gallery)
    ));
    $allPhotos  = array_values($allPhotos);
    $stockVal   = $product->stock ?? null;
    $stockOut   = $stockVal !== null && $stockVal <= 0;
    $stockLow   = $stockVal !== null && $stockVal > 0 && $stockVal <= 5;
    $client     = auth()->user();

    /* Initiales du vendeur */
    $vParts  = explode(' ', $vendeur->name ?? 'V');
    $vInit   = strtoupper(substr($vParts[0],0,1)) . strtoupper(substr($vParts[1] ?? 'X',0,1));
    $cParts  = explode(' ', $client->name);
    $cInit   = strtoupper(substr($cParts[0],0,1)) . strtoupper(substr($cParts[1] ?? 'X',0,1));
@endphp

{{-- Lightbox --}}
<div class="op-lb" id="opLb" onclick="this.classList.remove('open')">
    <button class="op-lb-close" onclick="document.getElementById('opLb').classList.remove('open')">✕</button>
    <img id="lbImg" src="" alt="">
</div>

{{-- TOPBAR --}}
<nav class="op-topbar">
    <a href="{{ route('client.shops.show', $shop) }}" class="op-back">← {{ Str::limit($shop->name, 20) }}</a>
    <div class="op-topbar-info">
        <div class="op-topbar-title">{{ $product->name }}</div>
        <div class="op-topbar-sub">Commander ce produit</div>
    </div>
</nav>

<div class="op-wrap">

    {{-- Flash --}}
    @if(session('success') || session('chat_sent'))
    <div class="op-flash op-flash-success">
        <span>✓</span>
        {{ session('success') ?? 'Message envoyé au vendeur !' }}
    </div>
    @endif
    @if($errors->any())
    <div class="op-flash op-flash-danger">
        <span>⚠</span>
        {{ $errors->first() }}
    </div>
    @endif

    {{-- ══ COLONNE GAUCHE : Produit + Commande ══ --}}
    <div>

        {{-- ── Fiche produit ── --}}
        <div class="op-card">
            <div class="op-card-hd">
                <div class="op-card-hd-ico">🏷️</div>
                <span class="op-card-title">Détail du produit</span>
            </div>
            <div class="op-card-body">
                <div class="op-prod">

                    {{-- Images --}}
                    <div class="op-prod-imgs">
                        <div class="op-prod-main-img" id="mainImgWrap"
                             onclick="openLb('{{ $product->image ? asset('storage/'.$product->image) : '' }}')">
                            @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" id="mainImg">
                            @else
                                <div class="op-prod-main-img-ph">🏷️</div>
                            @endif
                        </div>
                        {{-- Galerie photos supplémentaires --}}
                        @if(count($allPhotos) > 1)
                        <div class="op-prod-gallery">
                            @foreach($allPhotos as $i => $photo)
                            <img src="{{ $photo }}" class="op-prod-thumb {{ $i === 0 ? 'active' : '' }}"
                                 onclick="switchPhoto('{{ $photo }}', this)"
                                 alt="Photo {{ $i+1 }}">
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- Infos --}}
                    <div class="op-prod-info">
                        @if($product->category)
                        <div class="op-prod-cat">{{ $product->category }}</div>
                        @endif

                        <h1 class="op-prod-name">{{ $product->name }}</h1>

                        <div class="op-prod-price-row">
                            <span class="op-prod-price">{{ number_format($product->price, 0, ',', ' ') }}</span>
                            <span class="op-prod-currency">{{ $devise }}</span>
                            @if($hasPromo)
                            <span class="op-prod-price-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>
                            <span class="op-prod-remise">-{{ $remise }}%</span>
                            @endif
                        </div>

                        @if($product->description)
                        <p class="op-prod-desc">{{ $product->description }}</p>
                        @endif

                        <div class="op-prod-chips">
                            @if($stockVal !== null)
                                @if($stockOut)
                                    <span class="op-prod-chip danger">❌ Rupture de stock</span>
                                @elseif($stockLow)
                                    <span class="op-prod-chip amber">⚠️ Plus que {{ $stockVal }} en stock</span>
                                @else
                                    <span class="op-prod-chip ok">✓ {{ $stockVal }} en stock</span>
                                @endif
                            @endif
                            @if($product->unit)
                                <span class="op-prod-chip">📦 {{ $product->unit }}</span>
                            @endif
                            @if($product->preparation_time)
                                <span class="op-prod-chip">⏱ {{ $product->preparation_time }} min</span>
                            @endif
                            @if($product->allergens)
                                <span class="op-prod-chip" title="{{ $product->allergens }}" style="cursor:help">⚠ Allergènes</span>
                            @endif
                        </div>

                        {{-- Boutique --}}
                        <div class="op-shop-row">
                            <div class="op-shop-logo">
                                @if($shop->image)
                                    <img src="{{ asset('storage/'.$shop->image) }}" alt="">
                                @else
                                    🛍️
                                @endif
                            </div>
                            <div>
                                <div class="op-shop-name">{{ $shop->name }}</div>
                                @if($shop->type)
                                <div class="op-shop-type">{{ $shop->type }}</div>
                                @endif
                            </div>
                            <span class="op-shop-open">● Ouvert</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Formulaire commande ── --}}
        <div class="op-card">
            <div class="op-card-hd">
                <div class="op-card-hd-ico">🛒</div>
                <span class="op-card-title">Passer la commande</span>
            </div>
            <div class="op-card-body">

                @if($stockOut)
                {{-- Produit en rupture → impossible de commander --}}
                <div style="text-align:center;padding:24px 0;color:var(--muted)">
                    <div style="font-size:36px;margin-bottom:10px">😞</div>
                    <div style="font-size:14px;font-weight:700;color:var(--ink);margin-bottom:6px">Produit indisponible</div>
                    <div style="font-size:13px">Ce produit est en rupture de stock.<br>Contactez le vendeur via le chat →</div>
                </div>
                @else

                <form method="POST" action="{{ route('client.orders.storeProduct') }}" id="orderForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    {{-- Quantité --}}
                    <div class="op-qty-row">
                        <span class="op-qty-lbl">Quantité</span>
                        <div class="op-qty-ctrl">
                            <button type="button" class="op-qty-btn" onclick="changeQty(-1)">−</button>
                            <input type="number" name="quantity" id="qty"
                                   class="op-qty-input" value="1" min="1"
                                   max="{{ $stockVal ?? 999 }}"
                                   oninput="updateTotal()">
                            <button type="button" class="op-qty-btn" onclick="changeQty(1)">+</button>
                        </div>
                        @if($product->unit)
                        <span style="font-size:12px;color:var(--muted)">× {{ $product->unit }}</span>
                        @endif
                    </div>

                    {{-- Total dynamique --}}
                    <div class="op-total-preview">
                        <div>
                            <div class="op-total-lbl">Total à payer</div>
                            <div class="op-total-devise">Cash à la livraison</div>
                        </div>
                        <div class="op-total-val" id="totalDisplay">
                            {{ number_format($product->price, 0, ',', ' ') }}
                            <span style="font-size:12px;opacity:.5">{{ $devise }}</span>
                        </div>
                    </div>

                    {{-- Notice paiement --}}
                    <div class="op-cash-notice">
                        💵 <span>Paiement <strong>cash à la livraison</strong> — aucune carte requise.</span>
                    </div>

                    <button type="submit" class="op-submit-btn" id="submitBtn">
                        ✅ Valider ma commande
                    </button>
                </form>

                @endif
            </div>
        </div>

    </div>

    {{-- ══ COLONNE DROITE : Chat ══ --}}
    <div class="op-chat-wrap">

        {{-- Header chat --}}
        <div class="op-chat-hd">
            <div class="op-chat-hd-av">
                @if($vendeur && $shop->image)
                    <img src="{{ asset('storage/'.$shop->image) }}" alt="">
                @else
                    {{ $vInit }}
                @endif
            </div>
            <div>
                <div class="op-chat-hd-name">{{ $vendeur->name ?? $shop->name }}</div>
                <div class="op-chat-hd-status">En ligne · répond rapidement</div>
            </div>
        </div>

        {{-- Résumé produit dans le chat --}}
        <div class="op-chat-prod-bar">
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" class="op-chat-prod-img" alt="">
            @endif
            <div>
                <div class="op-chat-prod-name">{{ Str::limit($product->name, 30) }}</div>
                <div class="op-chat-prod-price">{{ number_format($product->price, 0, ',', ' ') }} {{ $devise }}</div>
            </div>
            <span style="margin-left:auto;font-size:10px;color:var(--muted);font-weight:600">Produit concerné</span>
        </div>

        {{-- Historique messages --}}
        <div class="op-chat-msgs" id="chatMsgs">

            @if($messages->isEmpty())
            <div class="op-chat-empty" id="chatEmpty">
                <div class="op-chat-empty-ico">💬</div>
                <div class="op-chat-empty-title">Discutez avec le vendeur</div>
                <div class="op-chat-empty-sub">
                    Posez vos questions avant de commander.<br>
                    Le vendeur répond en général rapidement.
                </div>
            </div>
            @endif

            @foreach($messages as $msg)
            @php
                $isMine = $msg->sender_id === $client->id;
                $initials = $isMine ? $cInit : $vInit;
            @endphp
            <div class="op-msg {{ $isMine ? 'mine' : '' }}" id="msg-{{ $msg->id }}">
                <div class="op-msg-av">{{ $initials }}</div>
                <div>
                    <div class="op-msg-bubble">{{ $msg->body }}</div>
                    <div class="op-msg-time">
                        {{ $msg->created_at->format('H:i') }}
                        @if($isMine && $msg->read_at)
                            <span class="op-msg-read">✓✓ Lu</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach

        </div>

        {{-- Zone de saisie --}}
        <div class="op-chat-input-wrap">
            <form method="POST"
                  action="{{ route('client.messages.store', $product) }}"
                  id="chatForm"
                  class="op-chat-form">
                @csrf
                <textarea name="body"
                          id="chatInput"
                          class="op-chat-textarea"
                          placeholder="Écrivez un message au vendeur…"
                          rows="1"
                          required
                          onkeydown="handleChatKey(event)"></textarea>
                <button type="submit" class="op-chat-send" id="chatSend" title="Envoyer">
                    ➤
                </button>
            </form>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
const PRICE   = {{ (float) $product->price }};
const DEVISE  = '{{ $devise }}';
const STOCK   = {{ $stockVal ?? 9999 }};

/* ── Quantité & Total ── */
function changeQty(d) {
    const inp = document.getElementById('qty');
    let v = parseInt(inp.value || 1) + d;
    v = Math.max(1, Math.min(STOCK, v));
    inp.value = v;
    updateTotal();
}

function updateTotal() {
    const qty = parseInt(document.getElementById('qty')?.value || 1);
    const total = Math.round(PRICE * qty);
    const el = document.getElementById('totalDisplay');
    if (el) el.innerHTML = total.toLocaleString('fr-FR') + ' <span style="font-size:12px;opacity:.5">' + DEVISE + '</span>';
}

/* ── Galerie photos ── */
function switchPhoto(url, thumb) {
    const main = document.getElementById('mainImg');
    if (main) { main.src = url; }
    document.querySelectorAll('.op-prod-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
    /* Mettre à jour le lien lightbox --*/
    document.getElementById('mainImgWrap').onclick = () => openLb(url);
}

/* ── Lightbox ── */
function openLb(url) {
    if (!url) return;
    document.getElementById('lbImg').src = url;
    document.getElementById('opLb').classList.add('open');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('opLb').classList.remove('open');
});

/* ── Submit loader ── */
document.getElementById('orderForm')?.addEventListener('submit', () => {
    const btn = document.getElementById('submitBtn');
    if (btn) { btn.disabled = true; btn.innerHTML = '⏳ Envoi en cours…'; }
});

/* ── Chat : auto-resize textarea ── */
const chatInput = document.getElementById('chatInput');
if (chatInput) {
    chatInput.addEventListener('input', () => {
        chatInput.style.height = 'auto';
        chatInput.style.height = Math.min(chatInput.scrollHeight, 100) + 'px';
    });
}

/* ── Envoi par Entrée (Shift+Entrée = retour à la ligne) ── */
function handleChatKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chatForm')?.submit();
    }
}

/* ── Scroll auto en bas du chat ── */
(function scrollChat() {
    const msgs = document.getElementById('chatMsgs');
    if (msgs) msgs.scrollTop = msgs.scrollHeight;
})();

/* ── Polling toutes les 8s pour les nouveaux messages ── */
@if(isset($product))
let lastId = {{ $messages->last()->id ?? 0 }};

async function pollMessages() {
    try {
        const res = await fetch('{{ route('client.messages.index', $product) }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });
        if (!res.ok) return;
        const data = await res.json();

        const msgs = document.getElementById('chatMsgs');
        const empty = document.getElementById('chatEmpty');

        data.forEach(msg => {
            if (msg.id > lastId) {
                lastId = msg.id;
                if (empty) empty.style.display = 'none';

                const div = document.createElement('div');
                div.className = 'op-msg' + (msg.mine ? ' mine' : '');
                div.id = 'msg-' + msg.id;
                const initials = msg.mine ? '{{ $cInit }}' : '{{ $vInit }}';
                div.innerHTML = `
                    <div class="op-msg-av">${initials}</div>
                    <div>
                        <div class="op-msg-bubble">${escHtml(msg.body)}</div>
                        <div class="op-msg-time">${msg.time}${msg.mine && msg.read ? ' <span class="op-msg-read">✓✓ Lu</span>' : ''}</div>
                    </div>`;
                msgs.appendChild(div);
                msgs.scrollTop = msgs.scrollHeight;
            }
        });
    } catch (e) { /* réseau indisponible */ }
}

function escHtml(t) {
    return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

setInterval(pollMessages, 8000);
@endif
</script>
@endpush