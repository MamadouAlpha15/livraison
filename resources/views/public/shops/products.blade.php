{{--
    resources/views/client/shops/show.blade.php
    Route     : GET /client/shops/{shop} → Client\ShopController@show
    Variables :
      $shop       → Shop
      $products   → LengthAwarePaginator<Product>
      $categories → array
      $devise     → string
--}}
@extends('layouts.app')
@section('title', $shop->name . ' — Produits')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Amazon+Ember:wght@400;700&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --amazon:     #f90;
    --amazon-dk:  #e47911;
    --amazon-lt:  #fff8e7;
    --navy:       #131921;
    --navy-2:     #232f3e;
    --blue:       #007185;
    --blue-lt:    #e8f4f8;
    --green:      #067d62;
    --green-lt:   #e8f5e9;
    --red:        #b12704;
    --grey:       #f3f3f3;
    --grey-2:     #eaeded;
    --border:     #ddd;
    --text:       #0f1111;
    --text-2:     #333;
    --muted:      #565959;
    --surface:    #fff;
    --font:       'Noto Sans', sans-serif;
    --r:          8px;
    --r-sm:       4px;
    --shadow-sm:  0 1px 2px rgba(0,0,0,.12);
    --shadow:     0 2px 8px rgba(0,0,0,.12);
    --nav-h:      60px;
}
html { font-family: var(--font); scroll-behavior: smooth; }
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

/* ══ TOPBAR STYLE AMAZON ══ */
.amz-nav {
    background: var(--navy);
    height: var(--nav-h);
    display: flex; align-items: center;
    padding: 0 16px; gap: 12px;
    position: sticky; top: 0; z-index: 100;
}
.amz-nav-logo {
    font-size: 20px; font-weight: 900; color: var(--amazon);
    text-decoration: none; letter-spacing: -.5px; flex-shrink: 0;
    font-family: var(--font);
}
.amz-nav-logo span { color: #fff; }
.amz-back {
    display: flex; align-items: center; gap: 5px;
    color: rgba(255,255,255,.85); font-size: 13px; font-weight: 600;
    text-decoration: none; padding: 6px 10px;
    border: 1px solid transparent; border-radius: var(--r-sm);
    transition: all .15s; white-space: nowrap; flex-shrink: 0;
}
.amz-back:hover { border-color: rgba(255,255,255,.5); color: #fff; }
.amz-nav-search {
    flex: 1; display: flex; border-radius: var(--r-sm); overflow: hidden;
    border: 2px solid var(--amazon); max-width: 600px;
}
.amz-nav-search input {
    flex: 1; border: none; outline: none; padding: 9px 14px;
    font-size: 14px; font-family: var(--font); background: var(--surface);
    color: var(--text);
}
.amz-nav-search-btn {
    background: var(--amazon); border: none; padding: 0 16px;
    cursor: pointer; font-size: 16px; transition: background .15s;
}
.amz-nav-search-btn:hover { background: var(--amazon-dk); }
.amz-nav-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.amz-nav-link {
    color: rgba(255,255,255,.85); font-size: 12px; text-decoration: none;
    padding: 6px 8px; border: 1px solid transparent; border-radius: var(--r-sm);
    transition: all .15s; white-space: nowrap;
}
.amz-nav-link:hover { border-color: rgba(255,255,255,.5); color: #fff; }
.amz-nav-link strong { display: block; font-size: 13px; color: #fff; }

/* ══ BANNIÈRE BOUTIQUE ══ */
.shop-banner {
    background: var(--navy-2);
    padding: 16px 20px;
    display: flex; align-items: center; gap: 16px;
    border-bottom: 3px solid var(--amazon);
}
.shop-banner-logo {
    width: 64px; height: 64px; border-radius: var(--r);
    background: var(--surface); display: flex; align-items: center;
    justify-content: center; font-size: 28px; flex-shrink: 0;
    overflow: hidden; border: 2px solid rgba(255,255,255,.15);
    box-shadow: var(--shadow);
}
.shop-banner-logo img { width: 100%; height: 100%; object-fit: cover; }
.shop-banner-info { flex: 1; min-width: 0; }
.shop-banner-name {
    font-size: 22px; font-weight: 700; color: #fff;
    margin-bottom: 4px; letter-spacing: -.3px;
}
.shop-banner-meta {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
}
.shop-banner-chip {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 12px; color: rgba(255,255,255,.65);
}
.shop-banner-open {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--green); color: #fff;
    font-size: 11px; font-weight: 700; padding: 3px 10px;
    border-radius: 20px;
}
.shop-banner-open::before {
    content: ''; width: 5px; height: 5px; border-radius: 50%;
    background: #a8f0d4; animation: pulse 1.8s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.3} }

/* ══ LAYOUT PRINCIPAL ══ */
.amz-layout {
    max-width: 1500px; margin: 0 auto;
    display: flex; gap: 0; padding: 16px 16px 60px;
}

/* ══ SIDEBAR FILTRES ══ */
.amz-sidebar {
    width: 220px; flex-shrink: 0;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 16px;
    height: fit-content; position: sticky; top: 76px;
    box-shadow: var(--shadow-sm);
    margin-right: 16px;
}
.amz-sidebar-title {
    font-size: 18px; font-weight: 700; color: var(--text);
    border-bottom: 1px solid var(--border); padding-bottom: 10px;
    margin-bottom: 14px;
}
.amz-sidebar-section { margin-bottom: 18px; }
.amz-sidebar-section-title {
    font-size: 13px; font-weight: 700; color: var(--text-2);
    text-transform: uppercase; letter-spacing: .5px;
    margin-bottom: 10px; padding-bottom: 6px;
    border-bottom: 1px solid var(--grey-2);
}
.amz-filter-item {
    display: flex; align-items: center; gap: 8px;
    padding: 5px 0; cursor: pointer; transition: color .12s;
}
.amz-filter-item input[type=radio] { accent-color: var(--amazon); cursor: pointer; }
.amz-filter-item label {
    font-size: 13px; color: var(--blue); cursor: pointer;
    transition: color .12s;
}
.amz-filter-item:hover label { color: var(--amazon-dk); text-decoration: underline; }
.amz-filter-item.active label { color: var(--text); font-weight: 700; }
.amz-price-range { display: flex; gap: 8px; align-items: center; }
.amz-price-input {
    flex: 1; padding: 6px 8px; border: 1px solid var(--border);
    border-radius: var(--r-sm); font-size: 12px; font-family: var(--font);
    color: var(--text); outline: none;
}
.amz-price-input:focus { border-color: var(--amazon); }
.amz-price-btn {
    padding: 6px 12px; background: var(--grey-2); border: 1px solid var(--border);
    border-radius: var(--r-sm); font-size: 12px; font-family: var(--font);
    cursor: pointer; transition: background .15s; color: var(--text);
}
.amz-price-btn:hover { background: var(--amazon-lt); border-color: var(--amazon); }

/* ══ CONTENU PRINCIPAL ══ */
.amz-content { flex: 1; min-width: 0; }

/* Barre résultats */
.amz-results-bar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 10px 16px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; margin-bottom: 14px; box-shadow: var(--shadow-sm);
    flex-wrap: wrap;
}
.amz-results-text { font-size: 13px; color: var(--muted); }
.amz-results-text strong { color: var(--text); font-weight: 700; }
.amz-sort-wrap { display: flex; align-items: center; gap: 8px; }
.amz-sort-label { font-size: 13px; color: var(--muted); white-space: nowrap; }
.amz-sort-select {
    padding: 6px 10px; border: 1px solid var(--border);
    border-radius: var(--r-sm); font-size: 13px; font-family: var(--font);
    color: var(--text); background: var(--surface); outline: none;
    cursor: pointer; transition: border-color .15s;
}
.amz-sort-select:focus { border-color: var(--amazon); }
.amz-view-btns { display: flex; gap: 4px; }
.amz-view-btn {
    padding: 6px 10px; border: 1px solid var(--border);
    background: var(--surface); border-radius: var(--r-sm);
    cursor: pointer; font-size: 14px; transition: all .15s; color: var(--muted);
}
.amz-view-btn.active { background: var(--amazon-lt); border-color: var(--amazon); color: var(--amazon-dk); }

/* ══ GRILLE PRODUITS ══ */
.amz-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}
.amz-grid.list-view { grid-template-columns: 1fr; }

/* ══ CARD PRODUIT ══ */
.amz-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .2s, border-color .2s;
    display: flex; flex-direction: column;
    position: relative;
}
.amz-card:hover { box-shadow: var(--shadow); border-color: #999; }

/* Mode liste */
.amz-grid.list-view .amz-card {
    flex-direction: row; height: 180px;
}
.amz-grid.list-view .amz-card-img { width: 180px; height: 100%; flex-shrink: 0; }
.amz-grid.list-view .amz-card-body { flex: 1; padding: 16px 20px; }
.amz-grid.list-view .amz-card-footer { border-top: none; border-left: 1px solid var(--border); width: 180px; flex-shrink: 0; padding: 16px; }

.amz-card-img {
    height: 200px; overflow: hidden; position: relative;
    background: #f9f9f9; flex-shrink: 0; cursor: zoom-in;
    display: flex; align-items: center; justify-content: center;
}
.amz-card-img img {
    max-width: 100%; max-height: 100%; object-fit: contain;
    transition: transform .3s; padding: 8px;
}
.amz-card:hover .amz-card-img img { transform: scale(1.04); }
.amz-card-img-ph {
    font-size: 48px; opacity: .25;
}

/* Badges */
.amz-card-badge {
    position: absolute; top: 8px; left: 8px;
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10px; font-weight: 700; padding: 3px 7px;
    border-radius: 3px; white-space: nowrap;
}
.badge-promo    { background: #cc0c39; color: #fff; }
.badge-nouveau  { background: var(--amazon); color: var(--navy); }
.badge-vedette  { background: var(--navy-2); color: var(--amazon); }
.badge-rupture  { background: #888; color: #fff; }

.amz-card-body {
    padding: 10px 12px 6px;
    flex: 1; display: flex; flex-direction: column; gap: 5px;
}
.amz-card-cat {
    font-size: 11px; color: var(--blue); font-weight: 600;
    text-transform: uppercase; letter-spacing: .4px;
}
.amz-card-name {
    font-size: 13.5px; color: var(--text); line-height: 1.4;
    display: -webkit-box; -webkit-line-clamp: 3;
    -webkit-box-orient: vertical; overflow: hidden;
    font-weight: 500;
}
.amz-card-name:hover { color: var(--amazon-dk); cursor: pointer; }

/* Étoiles */
.amz-stars { display: flex; align-items: center; gap: 4px; }
.amz-stars-ico { color: var(--amazon); font-size: 13px; letter-spacing: 1px; }
.amz-stars-count { font-size: 12px; color: var(--blue); }

/* Prix */
.amz-price-wrap { margin-top: 4px; }
.amz-price-main {
    font-size: 20px; font-weight: 700; color: var(--red);
    font-family: var(--font); letter-spacing: -.5px;
}
.amz-price-main sup { font-size: 12px; vertical-align: super; }
.amz-price-devise { font-size: 12px; color: var(--muted); font-weight: 400; }
.amz-price-orig {
    font-size: 12px; color: var(--muted); text-decoration: line-through;
    font-family: var(--font);
}
.amz-price-remise { font-size: 12px; color: var(--red); font-weight: 600; }
.amz-delivery {
    font-size: 12px; color: var(--green);
    display: flex; align-items: center; gap: 4px;
}
.amz-stock-ok   { font-size: 12px; color: var(--green); font-weight: 600; }
.amz-stock-low  { font-size: 12px; color: var(--amazon-dk); font-weight: 600; }
.amz-stock-out  { font-size: 12px; color: var(--red); font-weight: 600; }

.amz-card-footer { padding: 10px 12px; border-top: 1px solid var(--grey-2); }
.amz-btn-order {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; padding: 9px 12px; border-radius: 20px;
    font-size: 13px; font-weight: 700; font-family: var(--font);
    background: var(--amazon); color: var(--navy);
    border: 1px solid var(--amazon-dk);
    cursor: pointer; text-decoration: none;
    transition: all .15s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.amz-btn-order:hover { background: var(--amazon-dk); color: #fff; }
.amz-btn-order.out {
    background: var(--grey-2); color: var(--muted);
    border-color: var(--border); cursor: not-allowed;
    box-shadow: none;
}
.amz-btn-msg {
    display: flex; align-items: center; justify-content: center; gap: 5px;
    width: 100%; padding: 7px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600; font-family: var(--font);
    background: var(--surface); color: var(--text);
    border: 1px solid var(--border);
    cursor: pointer; text-decoration: none; margin-top: 6px;
    transition: all .15s;
}
.amz-btn-msg:hover { background: var(--grey-2); border-color: #999; }

/* ══ FLASH ══ */
.amz-flash {
    padding: 10px 14px; border-radius: var(--r); border: 1px solid;
    font-size: 13px; font-weight: 500; margin-bottom: 14px;
    display: flex; align-items: center; gap: 8px;
}
.amz-flash-success { background: var(--green-lt); border-color: #6ee7b7; color: #065f46; }
.amz-flash-danger  { background: #fff5f5; border-color: #fca5a5; color: var(--red); }

/* ══ VIDE ══ */
.amz-empty {
    grid-column: 1/-1; padding: 64px 20px; text-align: center;
    background: var(--surface); border-radius: var(--r); border: 1px solid var(--border);
}

/* ══ PAGINATION ══ */
.amz-pagination { display: flex; justify-content: center; padding: 20px 0 8px; }

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .amz-sidebar { display: none; }
    .amz-layout  { padding: 12px 12px 50px; }
}
@media (max-width: 600px) {
    .amz-nav { padding: 0 10px; gap: 8px; }
    .amz-nav-right { display: none; }
    .amz-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .amz-card-img { height: 150px; }
    .amz-card-name { font-size: 12px; -webkit-line-clamp: 2; }
    .amz-price-main { font-size: 16px; }
    .shop-banner { padding: 12px; }
    .shop-banner-logo { width: 48px; height: 48px; font-size: 20px; }
    .shop-banner-name { font-size: 16px; }
}
</style>
@endpush

@section('content')
@php
    $devise = $shop->currency ?? 'GNF';
@endphp

{{-- ══ NAVBAR ══ --}}
<nav class="amz-nav">
    <a href="{{ route('client.dashboard') }}" class="amz-nav-logo">Ma<span>Boutique</span></a>
    <a href="{{ route('client.dashboard') }}" class="amz-back">← Retour</a>
    <div class="amz-nav-search">
        <input type="text" id="prodSearch" placeholder="Rechercher dans {{ $shop->name }}…">
        <button class="amz-nav-search-btn">🔍</button>
    </div>
    <div class="amz-nav-right">
        <a href="{{ route('client.orders.index') }}" class="amz-nav-link">
            <span>Retours</span>
            <strong>& Commandes</strong>
        </a>
        <a href="{{ route('client.orders.index') }}" class="amz-nav-link" style="font-size:22px;color:var(--amazon)">📦</a>
    </div>
</nav>

{{-- ══ BANNIÈRE BOUTIQUE ══ --}}
<div class="shop-banner">
    <div class="shop-banner-logo">
        @if($shop->image)
            <img src="{{ asset('storage/'.$shop->image) }}" alt="{{ $shop->name }}">
        @else
            🛍️
        @endif
    </div>
    <div class="shop-banner-info">
        <div class="shop-banner-name">{{ $shop->name }}</div>
        <div class="shop-banner-meta">
            <span class="shop-banner-open">Ouvert</span>
            @if($shop->type)<span class="shop-banner-chip">🏷️ {{ $shop->type }}</span>@endif
            @if($shop->address)<span class="shop-banner-chip">📍 {{ Str::limit($shop->address, 30) }}</span>@endif
            <span class="shop-banner-chip">📦 {{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}</span>
            @if($shop->phone)<span class="shop-banner-chip">📞 {{ $shop->phone }}</span>@endif
        </div>
    </div>
</div>

{{-- ══ LAYOUT ══ --}}
<div class="amz-layout">

    {{-- Sidebar filtres --}}
    <aside class="amz-sidebar">
        <div class="amz-sidebar-title">Affiner les résultats</div>

        {{-- Catégories --}}
        @if(!empty($categories))
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Catégorie</div>
            <div class="amz-filter-item active" onclick="filterCat('')">
                <input type="radio" name="cat" id="cat-all" checked>
                <label for="cat-all">Tous les produits</label>
            </div>
            @foreach($categories as $cat)
            <div class="amz-filter-item" onclick="filterCat('{{ strtolower($cat) }}')">
                <input type="radio" name="cat" id="cat-{{ Str::slug($cat) }}">
                <label for="cat-{{ Str::slug($cat) }}">{{ $cat }}</label>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Disponibilité --}}
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Disponibilité</div>
            <div class="amz-filter-item" onclick="filterStock(false)">
                <input type="radio" name="stock" id="stock-all" checked>
                <label for="stock-all">Tous les articles</label>
            </div>
            <div class="amz-filter-item" onclick="filterStock(true)">
                <input type="radio" name="stock" id="stock-avail">
                <label for="stock-avail">En stock uniquement</label>
            </div>
        </div>

        {{-- Prix --}}
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Fourchette de prix</div>
            <div class="amz-price-range">
                <input type="number" class="amz-price-input" id="priceMin" placeholder="Min">
                <span style="color:var(--muted);font-size:12px">—</span>
                <input type="number" class="amz-price-input" id="priceMax" placeholder="Max">
            </div>
            <button class="amz-price-btn" style="width:100%;margin-top:8px" onclick="filterPrice()">Appliquer</button>
        </div>

        {{-- Tri --}}
        <div class="amz-sidebar-section">
            <div class="amz-sidebar-section-title">Trier par</div>
            <div class="amz-filter-item" onclick="setSort('default')">
                <input type="radio" name="sort" id="sort-def" checked>
                <label for="sort-def">Pertinence</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('price_asc')">
                <input type="radio" name="sort" id="sort-pa">
                <label for="sort-pa">Prix : croissant</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('price_desc')">
                <input type="radio" name="sort" id="sort-pd">
                <label for="sort-pd">Prix : décroissant</label>
            </div>
            <div class="amz-filter-item" onclick="setSort('featured')">
                <input type="radio" name="sort" id="sort-feat">
                <label for="sort-feat">Meilleures ventes</label>
            </div>
        </div>
    </aside>

    {{-- Contenu --}}
    <div class="amz-content">

        @foreach(['success','danger'] as $t)
            @if(session($t))<div class="amz-flash amz-flash-{{ $t }}">{{ session($t) }}</div>@endif
        @endforeach

        {{-- Barre résultats --}}
        <div class="amz-results-bar">
            <div class="amz-results-text">
                <strong id="resultCount">{{ $products->total() }}</strong> résultat{{ $products->total() > 1 ? 's' : '' }} dans <strong>{{ $shop->name }}</strong>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                <div class="amz-sort-wrap">
                    <span class="amz-sort-label">Trier par :</span>
                    <select class="amz-sort-select" id="sortSelectTop" onchange="setSort(this.value)">
                        <option value="default">Pertinence</option>
                        <option value="price_asc">Prix croissant</option>
                        <option value="price_desc">Prix décroissant</option>
                        <option value="name">Nom A→Z</option>
                        <option value="featured">Vedettes</option>
                    </select>
                </div>
                <div class="amz-view-btns">
                    <button class="amz-view-btn active" id="btnGrid" onclick="setView('grid')" title="Grille">⊞</button>
                    <button class="amz-view-btn" id="btnList" onclick="setView('list')" title="Liste">≡</button>
                </div>
            </div>
        </div>

        {{-- Grille produits --}}
        <div class="amz-grid" id="prodGrid">
            @forelse($products as $product)
            @php
                $hasPromo  = $product->original_price && $product->original_price > $product->price;
                $remise    = $hasPromo ? round((1 - $product->price / $product->original_price) * 100) : 0;
                $stockVal  = $product->stock ?? null;
                $stockOut  = $stockVal !== null && $stockVal <= 0;
                $stockLow  = $stockVal !== null && $stockVal > 0 && $stockVal <= 5;
                $isNew     = $product->created_at->diffInDays(now()) <= 7;
            @endphp
            <div class="amz-card"
                 data-name="{{ strtolower($product->name) }}"
                 data-cat="{{ strtolower($product->category ?? '') }}"
                 data-price="{{ $product->price }}"
                 data-featured="{{ $product->is_featured ? '1' : '0' }}"
                 data-stock="{{ $stockOut ? 'out' : 'in' }}">

                <div class="amz-card-img" onclick="goToProduct('{{ route('client.orders.createFromProduct', $product) }}')">
                    @if($product->image)
                        <img src="{{ \App\Services\ImageOptimizer::url($product->image, 'thumb') ?? asset('storage/'.$product->image) }}"
                             srcset="{{ \App\Services\ImageOptimizer::url($product->image, 'thumb') }} 300w,
                                     {{ \App\Services\ImageOptimizer::url($product->image, 'medium') }} 800w"
                             sizes="(max-width:600px) 150px, 300px"
                             alt="{{ $product->name }}" loading="lazy">
                    @else
                        <div class="amz-card-img-ph">🏷️</div>
                    @endif
                    @if($hasPromo)
                        <span class="amz-card-badge badge-promo">-{{ $remise }}%</span>
                    @elseif($isNew)
                        <span class="amz-card-badge badge-nouveau">Nouveau</span>
                    @elseif($product->is_featured)
                        <span class="amz-card-badge badge-vedette">⭐ Vedette</span>
                    @elseif($stockOut)
                        <span class="amz-card-badge badge-rupture">Rupture</span>
                    @endif
                </div>

                <div class="amz-card-body">
                    @if($product->category)
                    <div class="amz-card-cat">{{ $product->category }}</div>
                    @endif
                    <div class="amz-card-name" onclick="goToProduct('{{ route('client.orders.createFromProduct', $product) }}')">
                        {{ $product->name }}
                    </div>
                    <div class="amz-stars">
                        <span class="amz-stars-ico">★★★★★</span>
                        <span class="amz-stars-count">({{ rand(10, 200) }})</span>
                    </div>
                    <div class="amz-price-wrap">
                        <div style="display:flex;align-items:baseline;gap:8px;flex-wrap:wrap">
                            <span class="amz-price-main">
                                {{ number_format($product->price, 0, ',', ' ') }}
                                <span class="amz-price-devise">{{ $devise }}</span>
                            </span>
                            @if($hasPromo)
                            <span class="amz-price-orig">{{ number_format($product->original_price, 0, ',', ' ') }} {{ $devise }}</span>
                            <span class="amz-price-remise">-{{ $remise }}%</span>
                            @endif
                        </div>
                        <div class="amz-delivery">✓ Livraison disponible</div>
                    </div>
                    @if($stockVal !== null)
                        @if($stockOut)
                            <div class="amz-stock-out">Rupture de stock</div>
                        @elseif($stockLow)
                            <div class="amz-stock-low">Plus que {{ $stockVal }} en stock — commandez vite</div>
                        @else
                            <div class="amz-stock-ok">En stock</div>
                        @endif
                    @endif
                </div>

                <div class="amz-card-footer">
                    @auth
                        @if(Auth::user()->role === 'client')
                            @if(!$stockOut)
                            <a href="{{ route('client.orders.createFromProduct', $product) }}" class="amz-btn-order">
                                🛒 Commander maintenant
                            </a>
                            @else
                            <div class="amz-btn-order out">❌ Indisponible</div>
                            @endif
                            <a href="{{ route('client.messages.index', $product) }}" class="amz-btn-msg">
                                💬 Poser une question
                            </a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="amz-btn-order">S'inscrire pour commander</a>
                    @endauth
                </div>
            </div>
            @empty
            <div class="amz-empty">
                <div style="font-size:48px;opacity:.3;margin-bottom:12px">📭</div>
                <div style="font-size:18px;font-weight:700;margin-bottom:6px">Aucun produit trouvé</div>
                <p style="color:var(--muted);font-size:14px">Cette boutique n'a pas encore de produits.</p>
            </div>
            @endforelse
        </div>

        <div class="amz-pagination">{{ $products->links() }}</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let activeCat   = '';
let activeSort  = 'default';
let stockOnly   = false;
let priceMin    = 0;
let priceMax    = Infinity;
let searchQ     = '';

function goToProduct(url) { window.location.href = url; }

function filterCat(cat) {
    activeCat = cat;
    document.querySelectorAll('.amz-filter-item').forEach(el => {
        if (el.getAttribute('onclick')?.includes('filterCat')) el.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
    applyFilters();
}
function filterStock(onlyIn) { stockOnly = onlyIn; applyFilters(); }
function filterPrice() {
    priceMin = parseFloat(document.getElementById('priceMin').value) || 0;
    priceMax = parseFloat(document.getElementById('priceMax').value) || Infinity;
    applyFilters();
}
function setSort(val) {
    activeSort = val;
    document.getElementById('sortSelectTop').value = val;
    applyFilters();
}
function setView(type) {
    const grid = document.getElementById('prodGrid');
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

function applyFilters() {
    const cards = Array.from(document.querySelectorAll('.amz-card'));
    let visible = cards.filter(c => {
        const name  = c.dataset.name   || '';
        const cat   = c.dataset.cat    || '';
        const price = parseFloat(c.dataset.price) || 0;
        const stock = c.dataset.stock;
        const catOk   = !activeCat || cat === activeCat;
        const nameOk  = !searchQ   || name.includes(searchQ);
        const stockOk = !stockOnly || stock === 'in';
        const priceOk = price >= priceMin && price <= priceMax;
        return catOk && nameOk && stockOk && priceOk;
    });
    cards.forEach(c => c.style.display = 'none');
    visible.sort((a, b) => {
        if (activeSort === 'price_asc')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
        if (activeSort === 'price_desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
        if (activeSort === 'featured')   return parseInt(b.dataset.featured) - parseInt(a.dataset.featured);
        return 0;
    });
    const grid = document.getElementById('prodGrid');
    visible.forEach(c => { c.style.display = ''; grid.appendChild(c); });
    document.getElementById('resultCount').textContent = visible.length;
}

/* Recherche */
let st;
document.getElementById('prodSearch').addEventListener('input', e => {
    clearTimeout(st);
    st = setTimeout(() => { searchQ = e.target.value.toLowerCase().trim(); applyFilters(); }, 250);
});

/* Animations */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.amz-card').forEach((c, i) => {
        c.style.opacity = '0';
        setTimeout(() => {
            c.style.transition = 'opacity .3s ease';
            c.style.opacity = '1';
        }, 30 + i * 25);
    });
});
</script>
@endpush