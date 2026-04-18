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
    --orange:    #f90;
    --orange-dk: #e47911;
    --orange-lt: #fff8e7;
    --navy:      #131921;
    --navy-2:    #232f3e;
    --green:     #067d62;
    --green-lt:  #e8f5e9;
    --red:       #b12704;
    --blue:      #007185;
    --grey:      #f3f3f3;
    --grey-2:    #eaeded;
    --border:    #ddd;
    --text:      #0f1111;
    --text-2:    #333;
    --muted:     #565959;
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

/* ══ NAVBAR ══ */
.nav { background: var(--navy); height: var(--nav-h); display: flex; align-items: center; padding: 0 16px; gap: 10px; position: sticky; top: 0; z-index: 100; }
.nav-logo { font-family: var(--display); font-size: 18px; font-weight: 900; color: var(--orange); text-decoration: none; flex-shrink: 0; }
.nav-logo span { color: #fff; }
.nav-back { color: rgba(255,255,255,.8); font-size: 12.5px; font-weight: 600; text-decoration: none; padding: 5px 10px; border: 1px solid transparent; border-radius: var(--r-sm); transition: all .15s; white-space: nowrap; flex-shrink: 0; }
.nav-back:hover { border-color: rgba(255,255,255,.4); color: #fff; }
.nav-title { flex: 1; min-width: 0; font-size: 13px; font-weight: 700; color: rgba(255,255,255,.8); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ══ PAGE ══ */
.page-wrap { max-width: 860px; margin: 0 auto; padding: 24px 16px 80px; }

/* ══ FLASH ══ */
.flash { padding: 12px 16px; border-radius: var(--r); border: 1px solid; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.flash-success { background: var(--teal-lt); border-color: #6ee7b7; color: #065f46; }
.flash-danger  { background: var(--rose-lt); border-color: #fca5a5; color: var(--red); }

/* ══ CARD ══ */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 16px; }
.card-hd { padding: 12px 18px; border-bottom: 1px solid var(--border); background: var(--grey); display: flex; align-items: center; gap: 8px; }
.card-hd-ico { width: 26px; height: 26px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 13px; background: var(--orange-lt); border: 1px solid #fde68a; flex-shrink: 0; }
.card-title { font-family: var(--display); font-size: 14px; font-weight: 800; color: var(--text); }
.card-body { padding: 18px; }

/* ══ PRODUIT ══ */
.prod-layout { display: flex; gap: 20px; align-items: flex-start; }
.prod-gallery-col { flex-shrink: 0; }

.prod-main-img { width: 220px; height: 220px; border-radius: var(--r); overflow: hidden; background: var(--grey); border: 1px solid var(--border); cursor: zoom-in; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; transition: box-shadow .2s; }
.prod-main-img:hover { box-shadow: var(--shadow); }
.prod-main-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
.prod-main-img:hover img { transform: scale(1.05); }
.prod-main-img-ph { font-size: 52px; opacity: .22; }

.prod-thumbs { display: flex; gap: 6px; flex-wrap: wrap; }
.prod-thumb { width: 48px; height: 48px; border-radius: var(--r-sm); object-fit: cover; border: 2px solid transparent; cursor: pointer; transition: all .15s; opacity: .65; }
.prod-thumb:hover, .prod-thumb.active { border-color: var(--orange); opacity: 1; transform: scale(1.08); }

.prod-info { flex: 1; min-width: 0; }
.prod-cat { font-size: 10.5px; font-weight: 700; color: var(--blue); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 5px; }
.prod-name { font-family: var(--display); font-size: clamp(17px, 3vw, 24px); font-weight: 900; color: var(--text); line-height: 1.25; margin-bottom: 12px; }
.prod-price-row { display: flex; align-items: baseline; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
.prod-price { font-size: 28px; font-weight: 800; color: var(--red); font-family: monospace; letter-spacing: -.5px; }
.prod-devise { font-size: 12px; color: var(--muted); font-weight: 600; }
.prod-orig { font-size: 13px; color: var(--muted); text-decoration: line-through; font-family: monospace; }
.prod-remise { font-size: 11px; font-weight: 800; background: #fce4e4; color: var(--red); padding: 2px 8px; border-radius: 20px; }
.prod-desc { font-size: 13px; color: var(--muted); line-height: 1.65; margin-bottom: 12px; }

.prod-chips { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
.prod-chip { display: inline-flex; align-items: center; gap: 4px; font-size: 11.5px; font-weight: 600; color: var(--text-2); background: var(--grey); border: 1px solid var(--border); padding: 4px 10px; border-radius: var(--r-sm); }
.prod-chip.ok     { color: #065f46; background: var(--teal-lt); border-color: #6ee7b7; }
.prod-chip.amber  { color: #92400e; background: var(--amber-lt); border-color: #fde68a; }
.prod-chip.danger { color: var(--red); background: var(--rose-lt); border-color: #fca5a5; }

/* Boutique mini */
.shop-mini { display: flex; align-items: center; gap: 10px; background: var(--grey); border: 1px solid var(--border); border-radius: var(--r-sm); padding: 10px 12px; }
.shop-mini-logo { width: 38px; height: 38px; border-radius: 9px; background: var(--surface); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 18px; overflow: hidden; flex-shrink: 0; }
.shop-mini-logo img { width: 100%; height: 100%; object-fit: cover; }
.shop-mini-name { font-size: 13px; font-weight: 700; color: var(--text); }
.shop-mini-type { font-size: 11px; color: var(--muted); }
.shop-mini-open { margin-left: auto; display: inline-flex; align-items: center; gap: 4px; background: var(--green); color: #fff; font-size: 10.5px; font-weight: 700; padding: 4px 10px; border-radius: 20px; white-space: nowrap; flex-shrink: 0; }
.shop-mini-open::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #a8f0d4; animation: pulse 1.8s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.3} }

/* ══ FORMULAIRE COMMANDE ══ */
.order-grid { display: grid; grid-template-columns: 1fr 280px; gap: 16px; align-items: start; }

.qty-row { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
.qty-label { font-size: 12px; font-weight: 700; color: var(--text-2); text-transform: uppercase; letter-spacing: .4px; }
.qty-ctrl { display: flex; align-items: center; border: 1.5px solid var(--border); border-radius: var(--r-sm); overflow: hidden; background: var(--surface); }
.qty-btn { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: var(--grey-2); border: none; cursor: pointer; font-size: 20px; font-weight: 700; color: var(--text); transition: background .12s; user-select: none; }
.qty-btn:hover { background: var(--orange-lt); color: var(--orange-dk); }
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
.order-total-val { font-size: 22px; font-weight: 900; color: var(--orange); font-family: monospace; letter-spacing: -.5px; }
.order-total-devise { font-size: 11px; color: rgba(255,255,255,.35); text-align: right; margin-top: 2px; }

.cash-notice { display: flex; align-items: center; gap: 8px; background: rgba(245,166,35,.12); border: 1px solid rgba(245,166,35,.25); border-radius: var(--r-sm); padding: 10px 12px; font-size: 12px; color: #fde68a; }

.submit-btn { width: 100%; padding: 13px; border-radius: 50px; border: none; font-size: 14px; font-weight: 800; font-family: var(--font); background: var(--orange); color: var(--navy); cursor: pointer; transition: all .15s; box-shadow: 0 4px 14px rgba(255,153,0,.4); display: flex; align-items: center; justify-content: center; gap: 7px; }
.submit-btn:hover { background: var(--orange-dk); color: #fff; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(255,153,0,.5); }
.submit-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* ══ LIGHTBOX ══ */
.lb-overlay { display: none; position: fixed; inset: 0; z-index: 9000; background: rgba(0,0,0,.95); align-items: center; justify-content: center; }
.lb-overlay.open { display: flex; }
.lb-overlay img { max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: var(--r); }
.lb-close { position: absolute; top: 16px; right: 16px; width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.lb-close:hover { background: rgba(255,255,255,.2); }

/* ══ RESPONSIVE ══ */
@media (max-width: 700px) {
    .order-grid { grid-template-columns: 1fr; }
    .order-summary { position: static; }
}
@media (max-width: 580px) {
    .prod-layout { flex-direction: column; }
    .prod-gallery-col { width: 100%; }
    .prod-main-img { width: 100%; height: 260px; }
    .prod-thumbs { justify-content: center; }
    .prod-name { font-size: 18px; }
    .prod-price { font-size: 22px; }
    .nav-title { display: none; }
    .page-wrap { padding: 14px 12px 60px; }
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
    $stockOut  = $stockVal !== null && $stockVal <= 0;
    $stockLow  = $stockVal !== null && $stockVal > 0 && $stockVal <= 5;
@endphp

{{-- LIGHTBOX --}}
<div class="lb-overlay" id="lbOverlay" onclick="this.classList.remove('open')">
    <button class="lb-close" onclick="event.stopPropagation();document.getElementById('lbOverlay').classList.remove('open')">✕</button>
    <img id="lbImg" src="" alt="">
</div>

{{-- NAVBAR --}}
<nav class="nav">
    <a href="{{ route('client.dashboard') }}" class="nav-logo">Ma<span>Boutique</span></a>
    <a href="{{ route('client.shops.show', $shop) }}" class="nav-back">← {{ Str::limit($shop->name, 18) }}</a>
    <div class="nav-title">{{ Str::limit($product->name, 40) }}</div>
</nav>

<div class="page-wrap">

    {{-- FLASH --}}
    @if(session('success'))
    <div class="flash flash-success"><span>✓</span>{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="flash flash-danger"><span>⚠</span>{{ $errors->first() }}</div>
    @endif

    {{-- ══ FICHE PRODUIT ══ --}}
    <div class="card">
        <div class="card-hd">
            <div class="card-hd-ico">🏷️</div>
            <span class="card-title">Détail du produit</span>
        </div>
        <div class="card-body">
            <div class="prod-layout">

                {{-- Galerie --}}
                <div class="prod-gallery-col">
                    <div class="prod-main-img" id="mainImgWrap">
                        @if($product->image)
                            <img src="{{ \App\Services\ImageOptimizer::url($product->image, 'medium') ?? asset('storage/'.$product->image) }}"
                                 srcset="{{ \App\Services\ImageOptimizer::url($product->image, 'medium') }} 800w,
                                         {{ \App\Services\ImageOptimizer::url($product->image, 'large') }} 1600w"
                                 sizes="(max-width:600px) 100vw, 400px"
                                 id="mainImg" alt="{{ $product->name }}">
                        @else
                            <div class="prod-main-img-ph">🏷️</div>
                        @endif
                    </div>
                    @if(count($allPhotos) > 1)
                    <div class="prod-thumbs">
                        @foreach($allPhotos as $i => $photo)
                        <img src="{{ $photo }}" class="prod-thumb {{ $i === 0 ? 'active' : '' }}"
                             onclick="switchPhoto('{{ $photo }}', this)"
                             alt="Photo {{ $i+1 }}" loading="lazy">
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Infos --}}
                <div class="prod-info">
                    @if($product->category)<div class="prod-cat">{{ $product->category }}</div>@endif
                    <h1 class="prod-name">{{ $product->name }}</h1>

                    <div class="prod-price-row">
                        <span class="prod-price">{{ number_format($product->price, 0, ',', ' ') }}</span>
                        <span class="prod-devise">{{ $devise }}</span>
                        @if($hasPromo)
                        <span class="prod-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>
                        <span class="prod-remise">-{{ $remise }}%</span>
                        @endif
                    </div>

                    @if($product->description)
                    <p class="prod-desc">{{ $product->description }}</p>
                    @endif

                    <div class="prod-chips">
                        @if($stockVal !== null)
                            @if($stockOut)<span class="prod-chip danger">❌ Rupture de stock</span>
                            @elseif($stockLow)<span class="prod-chip amber">⚠ {{ $stockVal }} restants</span>
                            @else<span class="prod-chip ok">✓ En stock</span>@endif
                        @endif
                        @if($product->unit)<span class="prod-chip">📦 {{ $product->unit }}</span>@endif
                        @if($product->preparation_time)<span class="prod-chip">⏱ {{ $product->preparation_time }}min</span>@endif
                    </div>

                    <div class="shop-mini">
                        <div class="shop-mini-logo">
                            @if($shop->image)<img src="{{ asset('storage/'.$shop->image) }}" alt="">
                            @else🛍️@endif
                        </div>
                        <div>
                            <div class="shop-mini-name">{{ $shop->name }}</div>
                            @if($shop->type)<div class="shop-mini-type">{{ $shop->type }}</div>@endif
                        </div>
                        <span class="shop-mini-open">Ouvert</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ COMMANDE ══ --}}
    <div class="card">
        <div class="card-hd">
            <div class="card-hd-ico">🛒</div>
            <span class="card-title">Passer la commande</span>
        </div>
        <div class="card-body">

            @if($stockOut)
            <div style="text-align:center;padding:28px 0">
                <div style="font-size:42px;margin-bottom:10px">😞</div>
                <div style="font-size:15px;font-weight:700;margin-bottom:6px">Produit indisponible</div>
                <div style="font-size:13px;color:var(--muted)">Ce produit est en rupture de stock.</div>
            </div>
            @else

            <form method="POST" action="{{ route('client.orders.storeProduct') }}" id="orderForm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="order-grid">
                    {{-- Gauche : quantité --}}
                    <div>
                        <div class="qty-row">
                            <span class="qty-label">Quantité</span>
                            <div class="qty-ctrl">
                                <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                                <input type="number" name="quantity" id="qty" class="qty-input" value="1" min="1" max="{{ $stockVal ?? 999 }}" oninput="updateTotal()">
                                <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                            </div>
                            @if($product->unit)
                            <span style="font-size:12px;color:var(--muted)">× {{ $product->unit }}</span>
                            @endif
                        </div>

                        @if($stockLow)
                        <div style="font-size:12.5px;color:#92400e;background:var(--amber-lt);border:1px solid #fde68a;border-radius:var(--r-sm);padding:8px 12px;margin-bottom:14px">
                            ⚠ Plus que <strong>{{ $stockVal }}</strong> unité{{ $stockVal > 1 ? 's' : '' }} disponible{{ $stockVal > 1 ? 's' : '' }} — commandez vite !
                        </div>
                        @endif

                        <div style="font-size:13px;color:var(--green);display:flex;align-items:center;gap:6px;margin-bottom:14px">
                            ✓ Livraison disponible — paiement à la réception
                        </div>

                        <div style="font-size:13px;color:var(--muted);line-height:1.6">
                            📞 Si vous avez des questions, contactez le vendeur via la page boutique.
                        </div>
                    </div>

                    {{-- Droite : résumé + bouton --}}
                    <div class="order-summary">
                        <div class="order-summary-title">Votre commande</div>

                        <div class="order-summary-prod">
                            @if($product->image)
                                <img src="{{ \App\Services\ImageOptimizer::url($product->image, 'thumb') ?? asset('storage/'.$product->image) }}" class="order-summary-prod-img" alt="" loading="lazy">
                            @else
                                <div class="order-summary-prod-ph">🏷️</div>
                            @endif
                            <div style="min-width:0;overflow:hidden">
                                <div class="order-summary-prod-name">{{ Str::limit($product->name, 28) }}</div>
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
                            💵 <span>Cash à la livraison — aucune carte requise</span>
                        </div>

                        <button type="submit" class="submit-btn" id="submitBtn">
                            🛒 Valider ma commande
                        </button>
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
const PRICE = {{ (float) $product->price }};
const STOCK = {{ $stockVal ?? 9999 }};

function changeQty(d) {
    const inp = document.getElementById('qty');
    if (!inp) return;
    let v = parseInt(inp.value || 1) + d;
    v = Math.max(1, Math.min(STOCK, v));
    inp.value = v;
    updateTotal();
}

function updateTotal() {
    const qty = parseInt(document.getElementById('qty')?.value || 1);
    const total = Math.round(PRICE * qty);
    const el = document.getElementById('totalDisplay');
    if (el) el.textContent = total.toLocaleString('fr-FR');
    const sq = document.getElementById('summaryQty');
    if (sq) sq.textContent = qty;
}

/* Galerie */
function switchPhoto(url, thumb) {
    const img = document.getElementById('mainImg');
    if (img) img.src = url;
    document.querySelectorAll('.prod-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
    document.getElementById('mainImgWrap').onclick = () => openLb(url);
}

/* Lightbox */
function openLb(url) {
    if (!url) return;
    document.getElementById('lbImg').src = url;
    document.getElementById('lbOverlay').classList.add('open');
}
document.getElementById('mainImgWrap')?.addEventListener('click', function() {
    const img = document.getElementById('mainImg');
    if (img) openLb(img.src);
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('lbOverlay')?.classList.remove('open');
});

/* Submit */
document.getElementById('orderForm')?.addEventListener('submit', () => {
    const btn = document.getElementById('submitBtn');
    if (btn) { btn.disabled = true; btn.textContent = '⏳ Envoi en cours…'; }
});
</script>
@endpush