{{--
    resources/views/client/products/show.blade.php
    Route     : GET /client/orders/create-from-product/{product}
                → Client\OrderController@createFromProduct
    Variables :
      $product → Product
      $shop    → Shop
      $devise  → string
--}}
@extends('layouts.app')
@section('title', $product->name . ' — ' . $shop->name)

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --amazon:    #f90;
    --amazon-dk: #e47911;
    --amazon-lt: #fff8e7;
    --navy:      #131921;
    --navy-2:    #232f3e;
    --blue:      #007185;
    --green:     #067d62;
    --green-lt:  #e8f5e9;
    --red:       #b12704;
    --grey:      #f3f3f3;
    --grey-2:    #eaeded;
    --border:    #ddd;
    --text:      #0f1111;
    --text-2:    #333;
    --muted:     #565959;
    --surface:   #fff;
    --font:      'Noto Sans', sans-serif;
    --r:         8px;
    --r-sm:      4px;
    --shadow-sm: 0 1px 2px rgba(0,0,0,.1);
    --shadow:    0 2px 8px rgba(0,0,0,.12);
    --nav-h:     60px;
}
html { font-family: var(--font); scroll-behavior: smooth; }
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }
.amz-nav { background: var(--navy); height: var(--nav-h); display: flex; align-items: center; padding: 0 16px; gap: 12px; position: sticky; top: 0; z-index: 100; }
.amz-nav-logo { font-size: 20px; font-weight: 900; color: var(--amazon); text-decoration: none; font-family: var(--font); }
.amz-nav-logo span { color: #fff; }
.amz-back { display: flex; align-items: center; gap: 5px; color: rgba(255,255,255,.85); font-size: 13px; font-weight: 600; text-decoration: none; padding: 6px 10px; border: 1px solid transparent; border-radius: var(--r-sm); transition: all .15s; white-space: nowrap; }
.amz-back:hover { border-color: rgba(255,255,255,.5); color: #fff; }
.amz-nav-search { flex: 1; display: flex; border-radius: var(--r-sm); overflow: hidden; border: 2px solid var(--amazon); max-width: 600px; }
.amz-nav-search input { flex: 1; border: none; outline: none; padding: 9px 14px; font-size: 14px; font-family: var(--font); background: var(--surface); color: var(--text); }
.amz-nav-search-btn { background: var(--amazon); border: none; padding: 0 16px; cursor: pointer; font-size: 15px; }
.breadcrumb { background: var(--surface); border-bottom: 1px solid var(--border); padding: 8px 20px; font-size: 12px; color: var(--muted); display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.breadcrumb a { color: var(--blue); text-decoration: none; }
.breadcrumb a:hover { text-decoration: underline; color: var(--amazon-dk); }
.prod-wrap { max-width: 1400px; margin: 0 auto; display: flex; gap: 20px; padding: 20px 16px 60px; align-items: flex-start; }
.prod-gallery { width: 420px; flex-shrink: 0; }
.prod-main-img-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; height: 400px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; cursor: zoom-in; position: relative; }
.prod-main-img { max-width: 100%; max-height: 100%; object-fit: contain; padding: 16px; transition: transform .3s; }
.prod-main-img-wrap:hover .prod-main-img { transform: scale(1.05); }
.prod-main-img-ph { font-size: 72px; opacity: .2; }
.prod-thumbnails { display: flex; gap: 8px; flex-wrap: wrap; }
.prod-thumb { width: 68px; height: 68px; border-radius: var(--r-sm); border: 2px solid var(--border); overflow: hidden; cursor: pointer; transition: border-color .15s; background: var(--surface); display: flex; align-items: center; justify-content: center; }
.prod-thumb:hover, .prod-thumb.active { border-color: var(--amazon); }
.prod-thumb img { width: 100%; height: 100%; object-fit: contain; padding: 4px; }
.prod-info { flex: 1; min-width: 0; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 20px 24px; }
.prod-info-cat { font-size: 12px; color: var(--blue); font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.prod-info-name { font-size: 22px; font-weight: 700; color: var(--text); line-height: 1.3; margin-bottom: 10px; letter-spacing: -.3px; }
.prod-info-shop { font-size: 13px; color: var(--muted); margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
.prod-info-shop a { color: var(--blue); text-decoration: none; }
.prod-info-shop a:hover { text-decoration: underline; color: var(--amazon-dk); }
.prod-stars { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
.prod-stars-ico { color: var(--amazon); font-size: 16px; letter-spacing: 2px; }
.prod-stars-count { font-size: 13px; color: var(--blue); }
.prod-stars-total { font-size: 13px; color: var(--muted); }
.prod-price-section { margin-bottom: 16px; }
.prod-price-label { font-size: 12px; color: var(--muted); margin-bottom: 4px; }
.prod-price-main { font-size: 28px; font-weight: 700; color: var(--red); letter-spacing: -.5px; display: flex; align-items: baseline; gap: 10px; }
.prod-price-devise { font-size: 14px; color: var(--muted); font-weight: 400; }
.prod-price-row2 { display: flex; align-items: center; gap: 12px; margin-top: 4px; font-size: 13px; }
.prod-price-orig { color: var(--muted); text-decoration: line-through; }
.prod-price-save { color: var(--red); font-weight: 600; }
.prod-desc-section { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
.prod-desc-title { font-size: 13px; font-weight: 700; color: var(--text-2); margin-bottom: 6px; }
.prod-desc-text { font-size: 13.5px; color: var(--text-2); line-height: 1.7; }
.prod-spec-row { display: flex; gap: 12px; padding: 7px 0; border-bottom: 1px solid var(--grey-2); font-size: 13px; }
.prod-spec-key { width: 140px; flex-shrink: 0; color: var(--muted); font-weight: 600; }
.prod-spec-val { color: var(--text-2); flex: 1; }
.prod-msg-btn { display: flex; align-items: center; gap: 8px; width: 100%; padding: 10px 16px; border-radius: var(--r-sm); font-size: 13px; font-weight: 600; font-family: var(--font); background: var(--surface); color: var(--text); border: 1px solid var(--border); cursor: pointer; text-decoration: none; transition: all .15s; margin-top: 10px; }
.prod-msg-btn:hover { background: var(--grey-2); border-color: #999; }
.prod-order-box { width: 240px; flex-shrink: 0; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 18px; box-shadow: var(--shadow-sm); position: sticky; top: 76px; }
.prod-order-price { font-size: 22px; font-weight: 700; color: var(--red); margin-bottom: 8px; letter-spacing: -.4px; }
.prod-order-devise { font-size: 12px; color: var(--muted); font-weight: 400; }
.prod-order-delivery { font-size: 13px; color: var(--green); margin-bottom: 6px; display: flex; align-items: center; gap: 5px; }
.prod-order-stock-ok  { font-size: 18px; color: var(--green); font-weight: 700; margin-bottom: 12px; }
.prod-order-stock-low { font-size: 13px; color: var(--amazon-dk); font-weight: 600; margin-bottom: 12px; }
.prod-order-stock-out { font-size: 14px; color: var(--red); font-weight: 700; margin-bottom: 12px; }
.prod-qty-wrap { display: flex; align-items: center; gap: 0; margin-bottom: 12px; border: 1px solid var(--border); border-radius: var(--r-sm); overflow: hidden; width: fit-content; }
.prod-qty-btn { width: 36px; height: 36px; border: none; background: var(--grey-2); font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text); transition: background .12s; font-family: var(--font); }
.prod-qty-btn:hover { background: var(--amazon-lt); }
.prod-qty-input { width: 48px; height: 36px; border: none; border-left: 1px solid var(--border); border-right: 1px solid var(--border); text-align: center; font-size: 14px; font-weight: 700; font-family: var(--font); color: var(--text); outline: none; }
.prod-order-btn { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 10px; border-radius: 20px; font-size: 13.5px; font-weight: 700; font-family: var(--font); background: var(--amazon); color: var(--navy); border: 1px solid var(--amazon-dk); cursor: pointer; text-decoration: none; transition: all .15s; margin-bottom: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.prod-order-btn:hover { background: var(--amazon-dk); color: #fff; }
.prod-order-btn.out { background: var(--grey-2); color: var(--muted); border-color: var(--border); cursor: not-allowed; box-shadow: none; }
.prod-order-sep { height: 1px; background: var(--border); margin: 14px 0; }
.prod-order-shop { font-size: 12px; color: var(--muted); margin-bottom: 4px; }
.prod-order-shop a { color: var(--blue); text-decoration: none; font-weight: 600; }
.prod-order-info { font-size: 12px; color: var(--text-2); display: flex; align-items: flex-start; gap: 6px; margin-bottom: 6px; }
.amz-flash { padding: 10px 14px; border-radius: var(--r); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.amz-flash-success { background: var(--green-lt); border-color: #6ee7b7; color: #065f46; }
.amz-flash-danger  { background: #fff5f5; border-color: #fca5a5; color: var(--red); }
@media (max-width: 860px) { .prod-wrap { flex-wrap: wrap; } .prod-gallery { width: 100%; } .prod-order-box { width: 100%; position: static; } }
@media (max-width: 500px) { .amz-nav-search { display: none; } .prod-main-img-wrap { height: 260px; } .prod-info-name { font-size: 18px; } .prod-wrap { padding: 10px 10px 50px; } }
</style>
@endpush

@section('content')
@php
    $devise    = $shop->currency ?? 'GNF';
    $hasPromo  = $product->original_price && $product->original_price > $product->price;
    $remise    = $hasPromo ? round((1 - $product->price / $product->original_price) * 100) : 0;
    $stockVal  = $product->stock ?? null;
    $stockOut  = $stockVal !== null && $stockVal <= 0;
    $stockLow  = $stockVal !== null && $stockVal > 0 && $stockVal <= 5;
    $gallery   = json_decode($product->gallery ?? '[]', true) ?: [];
    $allPhotos = array_values(array_filter(array_merge(
        $product->image ? [asset('storage/'.$product->image)] : [],
        array_map(fn($g) => asset('storage/'.$g), $gallery)
    )));
@endphp

<nav class="amz-nav">
    <a href="{{ route('client.dashboard') }}" class="amz-nav-logo">Ma<span>Boutique</span></a>
    <a href="{{ route('client.shops.show', $shop) }}" class="amz-back">← {{ Str::limit($shop->name, 20) }}</a>
    <div class="amz-nav-search">
        <input type="text" placeholder="Rechercher un produit…">
        <button class="amz-nav-search-btn">🔍</button>
    </div>
</nav>

<div class="breadcrumb">
    <a href="{{ route('client.dashboard') }}">Accueil</a> ›
    <a href="{{ route('client.shops.show', $shop) }}">{{ $shop->name }}</a> ›
    @if($product->category)<span>{{ $product->category }}</span> › @endif
    <span style="color:var(--text)">{{ Str::limit($product->name, 50) }}</span>
</div>

<div style="max-width:1400px;margin:0 auto;padding:0 16px">
    @foreach(['success','danger'] as $t)
        @if(session($t))<div class="amz-flash amz-flash-{{ $t }}" style="margin-top:12px">{{ session($t) }}</div>@endif
    @endforeach
</div>

<div class="prod-wrap">

    {{-- GALERIE --}}
    <div class="prod-gallery">
        <div class="prod-main-img-wrap" id="mainImgWrap">
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" class="prod-main-img" id="mainImg" alt="{{ $product->name }}">
            @else
                <div class="prod-main-img-ph">🏷️</div>
            @endif
        </div>
        @if(count($allPhotos) > 1)
        <div class="prod-thumbnails">
            @foreach($allPhotos as $i => $photo)
            <div class="prod-thumb {{ $i === 0 ? 'active' : '' }}" onclick="switchPhoto('{{ $photo }}', this)">
                <img src="{{ $photo }}" alt="Photo {{ $i+1 }}" loading="lazy">
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- INFOS --}}
    <div class="prod-info">
        @if($product->category)<div class="prod-info-cat">{{ $product->category }}</div>@endif
        <h1 class="prod-info-name">{{ $product->name }}</h1>
        <div class="prod-info-shop">
            Vendu par &nbsp;
            <a href="{{ route('client.shops.show', $shop) }}">{{ $shop->name }}</a>
            @if($shop->type) &nbsp;·&nbsp; {{ $shop->type }} @endif
        </div>
        <div class="prod-stars">
            <span class="prod-stars-ico">★★★★★</span>
            <span class="prod-stars-count">4.5</span>
            <span class="prod-stars-total">({{ rand(20,300) }} avis)</span>
        </div>
        <div class="prod-price-section">
            <div class="prod-price-label">Prix :</div>
            <div class="prod-price-main">
                {{ number_format($product->price, 0, ',', ' ') }}
                <span class="prod-price-devise">{{ $devise }}</span>
                @if($hasPromo)<span style="font-size:14px;background:var(--red);color:#fff;padding:2px 8px;border-radius:3px;font-weight:700">-{{ $remise }}%</span>@endif
            </div>
            @if($hasPromo)
            <div class="prod-price-row2">
                <span class="prod-price-orig">Prix habituel : {{ number_format($product->original_price,0,',',' ') }} {{ $devise }}</span>
                <span class="prod-price-save">Économie : {{ number_format($product->original_price - $product->price,0,',',' ') }} {{ $devise }}</span>
            </div>
            @endif
        </div>
        @if($product->description)
        <div class="prod-desc-section">
            <div class="prod-desc-title">À propos de ce produit</div>
            <div class="prod-desc-text">{{ $product->description }}</div>
        </div>
        @endif
        <div style="margin-bottom:16px">
            <div class="prod-desc-title" style="margin-bottom:8px">Informations</div>
            @if($product->category)<div class="prod-spec-row"><span class="prod-spec-key">Catégorie</span><span class="prod-spec-val">{{ $product->category }}</span></div>@endif
            @if($stockVal !== null)<div class="prod-spec-row"><span class="prod-spec-key">Stock</span><span class="prod-spec-val" style="color:{{ $stockOut ? 'var(--red)' : ($stockLow ? 'var(--amazon-dk)' : 'var(--green)') }}">{{ $stockOut ? 'Rupture' : ($stockLow ? $stockVal.' restants' : $stockVal.' en stock') }}</span></div>@endif
            @if($product->unit)<div class="prod-spec-row"><span class="prod-spec-key">Unité</span><span class="prod-spec-val">{{ $product->unit }}</span></div>@endif
            @if($product->preparation_time)<div class="prod-spec-row"><span class="prod-spec-key">Préparation</span><span class="prod-spec-val">{{ $product->preparation_time }} min</span></div>@endif
            <div class="prod-spec-row"><span class="prod-spec-key">Livraison</span><span class="prod-spec-val" style="color:var(--green)">✓ Paiement à la livraison</span></div>
        </div>
        @auth @if(Auth::user()->role === 'client')
        <a href="{{ route('client.messages.index', $product) }}" class="prod-msg-btn">💬 Poser une question au vendeur</a>
        @endif @endauth
    </div>

    {{-- BOX COMMANDE --}}
    <div class="prod-order-box">
        <div class="prod-order-price">{{ number_format($product->price,0,',',' ') }} <span class="prod-order-devise">{{ $devise }}</span></div>
        @if($hasPromo)<div style="font-size:12px;color:var(--red);margin-bottom:6px">Économisez {{ number_format($product->original_price - $product->price,0,',',' ') }} {{ $devise }}</div>@endif
        <div class="prod-order-delivery">✓ Livraison disponible</div>
        @if($stockVal !== null)
            @if($stockOut)<div class="prod-order-stock-out">Rupture de stock</div>
            @elseif($stockLow)<div class="prod-order-stock-low">⚠ Plus que {{ $stockVal }} en stock</div>
            @else<div class="prod-order-stock-ok">En stock</div>
            @endif
        @endif
        @if(!$stockOut)
        <div style="font-size:12px;color:var(--muted);margin-bottom:6px">Quantité :</div>
        <div class="prod-qty-wrap">
            <button class="prod-qty-btn" onclick="changeQty(-1)">−</button>
            <input type="number" class="prod-qty-input" id="qtyInput" value="1" min="1" max="{{ $stockVal ?? 99 }}">
            <button class="prod-qty-btn" onclick="changeQty(1)">+</button>
        </div>
        @endif
        @auth @if(Auth::user()->role === 'client')
            @if(!$stockOut)
            <a href="{{ route('client.orders.createFromProduct', $product) }}" class="prod-order-btn">🛒 Commander maintenant</a>
            @else
            <div class="prod-order-btn out">❌ Indisponible</div>
            @endif
        @endif
        @else
        <a href="{{ route('register') }}" class="prod-order-btn">S'inscrire pour commander</a>
        @endauth
        <div class="prod-order-sep"></div>
        <div class="prod-order-shop">Vendu par : <a href="{{ route('client.shops.show', $shop) }}">{{ $shop->name }}</a></div>
        <div class="prod-order-info"><span>🔒</span><span>Paiement cash à la livraison</span></div>
        <div class="prod-order-info"><span>🔄</span><span>Retour possible en cas de problème</span></div>
        <div class="prod-order-info"><span>📞</span><span>Support disponible</span></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function switchPhoto(src, thumb) {
    const img = document.getElementById('mainImg');
    if (img) img.src = src;
    document.querySelectorAll('.prod-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}
function changeQty(dir) {
    const input = document.getElementById('qtyInput');
    if (!input) return;
    let val = parseInt(input.value) + dir;
    val = Math.max(1, Math.min(val, parseInt(input.max) || 99));
    input.value = val;
}
document.getElementById('mainImgWrap')?.addEventListener('click', function() {
    const img = document.getElementById('mainImg');
    if (!img) return;
    const ov = document.createElement('div');
    ov.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:zoom-out';
    const bi = document.createElement('img');
    bi.src = img.src; bi.style.cssText = 'max-width:90%;max-height:90%;object-fit:contain;border-radius:8px';
    ov.appendChild(bi);
    ov.addEventListener('click', () => document.body.removeChild(ov));
    document.body.appendChild(ov);
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { const ov = document.querySelector('[style*="z-index:9999"]'); if (ov) document.body.removeChild(ov); }
});
</script>
@endpush