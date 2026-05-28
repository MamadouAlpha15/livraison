@extends('layouts.app')

@section('title', 'Tous les produits' . ($countryName ? ' — ' . $countryName : ''))
@php $bodyClass = 'is-dashboard'; @endphp

<style>
*, *::before, *::after { box-sizing: border-box; }

body { background: #0f0f1a; color: #e2e8f0; }

/* ── HEADER ── */
.catalog-header {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    padding: 36px 24px 28px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.catalog-header h1 {
    font-size: clamp(22px, 4vw, 34px);
    font-weight: 800;
    margin: 0 0 6px;
    background: linear-gradient(135deg, #fff 0%, #a78bfa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.catalog-header p {
    margin: 0;
    font-size: 14px;
    color: rgba(255,255,255,.55);
}
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 16px;
    font-size: 13px;
    color: rgba(255,255,255,.55);
    text-decoration: none;
    transition: color .15s;
}
.back-link:hover { color: #fff; }

/* ── TOOLBAR ── */
.catalog-toolbar {
    max-width: 1200px;
    margin: 24px auto 0;
    padding: 0 20px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}
.search-bar {
    flex: 1;
    min-width: 200px;
    display: flex;
    background: #1e1e30;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 12px;
    overflow: hidden;
}
.search-bar input {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    color: #e2e8f0;
    font-size: 14px;
    padding: 11px 16px;
}
.search-bar input::placeholder { color: rgba(255,255,255,.3); }
.search-bar button {
    background: linear-gradient(135deg, #ff6a00, #ee0979);
    border: none;
    color: #fff;
    padding: 0 18px;
    font-size: 15px;
    cursor: pointer;
    transition: opacity .15s;
}
.search-bar button:hover { opacity: .85; }

/* ── CATS ── */
.cat-pills {
    max-width: 1200px;
    margin: 14px auto 0;
    padding: 0 20px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.cat-pill {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    color: rgba(255,255,255,.65);
    transition: .15s;
}
.cat-pill:hover, .cat-pill.active {
    background: linear-gradient(135deg, #ff6a00, #ee0979);
    border-color: transparent;
    color: #fff;
}

/* ── GRID ── */
.catalog-grid {
    max-width: 1200px;
    margin: 24px auto 0;
    padding: 0 20px 48px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 18px;
}

/* ── CARD ── */
.p-card {
    background: #1a1a2e;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform .18s, box-shadow .18s;
    text-decoration: none;
    color: inherit;
}
.p-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(0,0,0,.35);
    border-color: rgba(255,106,0,.35);
}
.p-card-img {
    position: relative;
    height: 170px;
    background: #13131f;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.p-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .3s;
}
.p-card:hover .p-card-img img { transform: scale(1.04); }
.p-card-ph {
    font-size: 40px;
    opacity: .25;
}
.p-card-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: linear-gradient(135deg, #ff6a00, #ee0979);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 20px;
}
.p-card-body {
    padding: 13px 14px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.p-card-name {
    font-size: 13.5px;
    font-weight: 700;
    color: #e2e8f0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.p-card-shop {
    font-size: 11.5px;
    color: rgba(255,255,255,.45);
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}
.p-card-cat {
    font-size: 10.5px;
    color: #a78bfa;
    font-weight: 600;
}
.p-card-footer {
    padding: 10px 14px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid rgba(255,255,255,.06);
    margin-top: auto;
}
.p-card-price {
    font-size: 14px;
    font-weight: 800;
    color: #ff6a00;
}
.p-card-orig {
    font-size: 11px;
    color: rgba(255,255,255,.3);
    text-decoration: line-through;
    margin-left: 4px;
}
.p-card-link {
    display: block;
    text-decoration: none;
    color: inherit;
    flex: 1;
}
.p-card-cta {
    font-size: 11.5px;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #ff6a00, #ee0979);
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    cursor: pointer;
    text-decoration: none;
    transition: opacity .15s;
    white-space: nowrap;
}
.p-card-cta:hover { opacity: .85; }

/* ── EMPTY ── */
.catalog-empty {
    max-width: 1200px;
    margin: 60px auto;
    padding: 0 20px;
    text-align: center;
    color: rgba(255,255,255,.4);
}
.catalog-empty-ico { font-size: 52px; margin-bottom: 12px; }
.catalog-empty-title { font-size: 18px; font-weight: 700; color: rgba(255,255,255,.6); }
.catalog-empty-sub { font-size: 13px; margin-top: 6px; }

/* ── PAGINATION ── */
.catalog-pagination {
    max-width: 1200px;
    margin: 0 auto 48px;
    padding: 0 20px;
    display: flex;
    justify-content: center;
}
.catalog-pagination nav { display: flex; gap: 6px; flex-wrap: wrap; }

/* ── RESULT COUNT ── */
.catalog-count {
    max-width: 1200px;
    margin: 16px auto 0;
    padding: 0 20px;
    font-size: 13px;
    color: rgba(255,255,255,.4);
}

@media (max-width: 600px) {
    .catalog-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .catalog-toolbar { gap: 8px; }
}
</style>

@section('content')

<div class="catalog-header">
    <a href="{{ route('client.dashboard') }}" class="back-link">
        ← Retour à l'accueil
    </a>
    <h1>Tous les produits{{ $countryName ? ' en ' . $countryName : '' }}</h1>
    <p>{{ number_format($products->total()) }} produit{{ $products->total() > 1 ? 's' : '' }} disponible{{ $products->total() > 1 ? 's' : '' }}</p>
</div>

{{-- Toolbar --}}
<form method="GET" action="{{ route('client.products.index') }}">
    @if(request('cat'))
        <input type="hidden" name="cat" value="{{ request('cat') }}">
    @endif
    <div class="catalog-toolbar">
        <div class="search-bar">
            <input type="text" name="s" value="{{ request('s') }}" placeholder="Rechercher un produit…">
            <button type="submit">&#x1F50D;</button>
        </div>
        @if(request('s') || request('cat'))
            <a href="{{ route('client.products.index') }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;border-radius:12px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.65);font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap">
                Réinitialiser
            </a>
        @endif
    </div>
</form>

{{-- Category pills --}}
@if($categories->isNotEmpty())
<div class="cat-pills">
    <a href="{{ route('client.products.index', array_filter(['s' => request('s')])) }}"
       class="cat-pill {{ !request('cat') ? 'active' : '' }}">
        Toutes
    </a>
    @foreach($categories as $cat)
    <a href="{{ route('client.products.index', array_filter(['cat' => $cat, 's' => request('s')])) }}"
       class="cat-pill {{ request('cat') === $cat ? 'active' : '' }}">
        {{ $cat }}
    </a>
    @endforeach
</div>
@endif

<div class="catalog-count">
    @if(request('s') || request('cat'))
        Résultats pour
        @if(request('s'))"<strong>{{ request('s') }}</strong>"@endif
        @if(request('cat')) dans <strong>{{ request('cat') }}</strong>@endif
        — {{ number_format($products->total()) }} produit{{ $products->total() > 1 ? 's' : '' }}
    @endif
</div>

{{-- Grid --}}
@if($products->isEmpty())
<div class="catalog-empty">
    <div class="catalog-empty-ico">📦</div>
    <div class="catalog-empty-title">Aucun produit trouvé</div>
    <p class="catalog-empty-sub">Essayez une autre recherche ou catégorie.</p>
</div>
@else
<div class="catalog-grid">
    @foreach($products as $product)
    @php
        $hasPromo = $product->original_price && $product->original_price > $product->price;
    @endphp
    <div class="p-card">
        <a href="{{ route('client.products.show', $product) }}" class="p-card-link">
            <div class="p-card-img">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy">
                @else
                    <div class="p-card-ph">🛍</div>
                @endif
                @if($hasPromo)
                    <span class="p-card-badge">Promo</span>
                @endif
            </div>
            <div class="p-card-body">
                <div class="p-card-name">{{ $product->name }}</div>
                @if($product->category)
                    <div class="p-card-cat">{{ $product->category }}</div>
                @endif
                @if($product->shop)
                    <div class="p-card-shop">
                        <span style="font-size:11px">🏪</span>
                        {{ Str::limit($product->shop->name, 22) }}
                    </div>
                @endif
            </div>
        </a>
        <div class="p-card-footer">
            <div>
                <span class="p-card-price">{{ number_format($product->price, 0, ',', ' ') }} GNF</span>
                @if($hasPromo)
                    <span class="p-card-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>
                @endif
            </div>
            <a href="{{ route('client.orders.createFromProduct', $product) }}" class="p-card-cta">Commander</a>
        </div>
    </div>
    @endforeach
</div>

<div class="catalog-pagination">
    {{ $products->links() }}
</div>
@endif

@endsection
