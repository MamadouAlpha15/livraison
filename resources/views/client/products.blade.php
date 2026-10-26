@extends('layouts.app')

@section('title', 'Tous les produits' . ($countryName ? ' — ' . $countryName : ''))
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }

body { background: #F6F9FF; color: #16162A; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; }

/* ── En-tête bleu nuit avec halo bleu ── */
.catalog-header {
    background: radial-gradient(110% 160% at 100% 0%, rgba(31,124,246,.42), transparent 60%), linear-gradient(135deg, #0B1E3D, #132B52);
    padding: 34px 24px 30px;
    text-align: center;
}
.catalog-header h1 { font-size: clamp(24px, 4vw, 36px); font-weight: 800; letter-spacing: -.025em; margin: 0 0 6px; color: #fff; text-wrap: balance; }
.catalog-header p { margin: 0; font-size: 14.5px; color: #C9D6EA; }
.back-link { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 14px; min-height: 36px; padding: 0 14px; border-radius: 999px; background: rgba(255,255,255,.1); font-size: 13px; font-weight: 700; color: #fff; text-decoration: none; transition: background-color .18s; }
.back-link:hover { background: rgba(255,255,255,.18); color: #fff; }

/* ── Recherche ── */
.catalog-toolbar { max-width: 1200px; margin: 22px auto 0; padding: 0 20px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
.search-bar { flex: 1; min-width: 200px; display: flex; gap: 6px; padding: 5px; background: #fff; border-radius: 999px; box-shadow: inset 0 0 0 1.5px #E3E8F2; transition: box-shadow .18s; }
.search-bar:focus-within { box-shadow: inset 0 0 0 2px #1F7CF6, 0 0 0 4px rgba(31,124,246,.12); }
.search-bar input { flex: 1; min-width: 0; background: none; border: none; outline: none; color: #16162A; font-family: inherit; font-size: 15px; font-weight: 500; padding: 9px 14px; }
.search-bar input::placeholder { color: #9AA3B8; }
.search-bar button { min-height: 44px; min-width: 52px; border: none; border-radius: 999px; background: #1668D8; color: #fff; padding: 0 18px; font-size: 15px; cursor: pointer; transition: background-color .18s; }
.search-bar button:hover { background: #1260C9; }

/* ── Catégories en pastilles ── */
.cat-pills { max-width: 1200px; margin: 14px auto 0; padding: 0 20px; display: flex; gap: 8px; flex-wrap: wrap; }
.cat-pill { display: inline-flex; align-items: center; min-height: 38px; padding: 0 16px; border-radius: 999px; font-size: 13.5px; font-weight: 700; text-decoration: none; background: rgba(22,22,42,.07); color: #45455E; transition: background-color .18s, color .18s; }
.cat-pill:hover { background: rgba(22,22,42,.12); color: #16162A; }
.cat-pill.active { background: #1668D8; color: #fff; }

/* ── Grille ── */
.catalog-grid { max-width: 1200px; margin: 22px auto 0; padding: 0 20px 48px; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 18px; }

/* ── Carte produit ── */
.p-card { background: #fff; border-radius: 22px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 0 0 1px #E3E8F2; transition: transform .22s cubic-bezier(.22,1,.36,1), box-shadow .22s; text-decoration: none; color: inherit; }
.p-card:hover { transform: translateY(-3px); box-shadow: 0 0 0 1px #D6E6FF, 0 16px 32px -18px rgba(11,30,61,.35); }
.p-card-img { position: relative; aspect-ratio: 1; background: #EAF2FF; overflow: hidden; display: flex; align-items: center; justify-content: center; color: #7DB4FF; }
.p-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s cubic-bezier(.22,1,.36,1); }
.p-card:hover .p-card-img img { transform: scale(1.05); }
.p-card-ph { font-size: 40px; display: inline-flex; }
.p-card-badge { position: absolute; top: 10px; left: 10px; display: inline-flex; align-items: center; gap: 4px; background: #1668D8; color: #fff; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 999px; }
.p-card-body { padding: 12px 14px 6px; flex: 1; display: flex; flex-direction: column; gap: 3px; }
.p-card-name { font-size: 14px; font-weight: 700; color: #16162A; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.p-card-shop { font-size: 12px; color: #62627C; display: flex; align-items: center; gap: 4px; margin-top: 2px; }
.p-card-cat { font-size: 11px; color: #1260C9; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
.p-card-footer { padding: 8px 14px 14px; display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: auto; }
.p-card-price { font-size: 15px; font-weight: 800; color: #1260C9; font-variant-numeric: tabular-nums; }
.p-card-orig { font-size: 11.5px; color: #9AA3B8; text-decoration: line-through; margin-left: 4px; }
.p-card-link { display: block; text-decoration: none; color: inherit; flex: 1; }
.p-card-cta { font-size: 12.5px; font-weight: 800; color: #fff; background: #1668D8; border: none; border-radius: 999px; padding: 8px 14px; cursor: pointer; text-decoration: none; transition: background-color .18s; white-space: nowrap; }
.p-card-cta:hover { background: #1260C9; color: #fff; }

/* ── Aucun résultat ── */
.catalog-empty { max-width: 1200px; margin: 60px auto; padding: 0 20px; text-align: center; color: #45455E; }
.catalog-empty-ico { font-size: 52px; margin-bottom: 12px; color: #9AA3B8; display: inline-flex; }
.catalog-empty-title { font-size: 18px; font-weight: 800; color: #16162A; }
.catalog-empty-sub { font-size: 14px; margin-top: 6px; }

/* ── Pagination ── */
.catalog-pagination { max-width: 1200px; margin: 0 auto 48px; padding: 0 20px; display: flex; justify-content: center; }
.catalog-pagination nav { display: flex; gap: 6px; flex-wrap: wrap; }

/* ── Nombre de résultats ── */
.catalog-count { max-width: 1200px; margin: 16px auto 0; padding: 0 20px; font-size: 13.5px; color: #62627C; }

/* ══ RESPONSIVE ══ */

/* ── Tablette (≤ 768px) ── */
@media (max-width: 768px) {
    .catalog-header { padding: 24px 16px 20px; }
    .catalog-toolbar { padding: 0 14px; gap: 8px; }
    .cat-pills { padding: 0 14px; }
    .catalog-grid { padding: 0 14px 40px; gap: 14px; }
    .catalog-count { padding: 0 14px; }
    .catalog-pagination { padding: 0 14px; }
}

/* ── Mobile (≤ 600px) ── */
@media (max-width: 600px) {
    .catalog-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        padding: 0 10px 40px;
    }
    .catalog-toolbar { padding: 0 10px; flex-wrap: nowrap; }
    .search-bar { min-width: 0; }
    .cat-pills { padding: 0 10px; gap: 6px; overflow-x: auto; flex-wrap: nowrap; scrollbar-width: none; padding-bottom: 6px; }
    .cat-pills::-webkit-scrollbar { display: none; }
    .cat-pill { flex-shrink: 0; font-size: 11.5px; padding: 5px 12px; }

    .p-card-img { height: 130px; }
    .p-card-body { padding: 9px 10px; gap: 3px; }
    .p-card-name { font-size: 12px; }
    .p-card-shop { font-size: 10px; }
    .p-card-cat { font-size: 9.5px; }

    /* Footer : prix en haut bien lisible, bouton en dessous */
    .p-card-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: 8px 10px 10px;
    }
    .p-card-price {
        font-size: 13px;
        font-weight: 900;
        color: #1668D8;
        line-height: 1.2;
        white-space: nowrap;
    }
    .p-card-orig { font-size: 10px; display: block; margin-left: 0; margin-top: 1px; }
    .p-card-cta {
        width: 100%;
        text-align: center;
        padding: 7px 10px;
        font-size: 11px;
        border-radius: 8px;
    }
}

/* ── Petits mobiles (≤ 390px) ── */
@media (max-width: 390px) {
    .catalog-grid { gap: 8px; padding: 0 8px 36px; }
    .p-card-img { height: 110px; }
    .p-card-body { padding: 7px 8px; }
    .p-card-name { font-size: 11.5px; }
    .p-card-footer { padding: 7px 8px 9px; gap: 6px; }
    .p-card-price { font-size: 12px; }
    .p-card-cta { font-size: 10.5px; padding: 6px 8px; }
    .catalog-header h1 { font-size: 18px !important; }
}

/* ── Très petits mobiles (≤ 360px) ── */
@media (max-width: 360px) {
    .catalog-grid { gap: 6px; padding: 0 6px 32px; }
    .p-card-img { height: 95px; }
    .p-card-name { font-size: 11px; -webkit-line-clamp: 2; }
    .p-card-price { font-size: 11.5px; }
    .p-card-cta { font-size: 10px; padding: 5px 7px; }
    .p-card-shop, .p-card-cat { display: none; }
}

/* ── 320px ── */
@media (max-width: 320px) {
    .catalog-grid { grid-template-columns: 1fr; gap: 10px; padding: 0 10px 32px; }
    .p-card-img { height: 160px; }
    .p-card-name { font-size: 13px; }
    .p-card-price { font-size: 15px; font-weight: 900; }
    .p-card-cta { font-size: 12px; padding: 8px; }
    .p-card-shop { display: flex; }
}
</style>
@endpush

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
            <button type="submit" aria-label="Rechercher">{!! \App\Support\IconLibrary::svg('search', '', 18) !!}</button>
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
    <div class="catalog-empty-ico">{!! \App\Support\IconLibrary::svg('package', '', 44) !!}</div>
    <div class="catalog-empty-title">Aucun produit trouvé</div>
    <p class="catalog-empty-sub">Essayez une autre recherche ou catégorie.</p>
</div>
@else
<div class="catalog-grid">
    @foreach($products as $product)
    @php
        $hasPromo = $product->original_price && $product->original_price > $product->price;
        $showUrl  = \App\Support\Campaigns\SponsoredPlacements::url(route('client.products.show', $product), $product);
    @endphp
    <div class="p-card">
        <a href="{{ $showUrl }}" class="p-card-link">
            <div class="p-card-img">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy" decoding="async" width="400" height="170">
                @else
                    <div class="p-card-ph">{!! \App\Support\IconLibrary::svg('bag', '', 26) !!}</div>
                @endif
                @if($product->is_flash_active)
                    <span class="p-card-badge" style="background:#0B1E3D">{!! \App\Support\IconLibrary::svg('zap', '', 16) !!} Flash −{{ $product->flash_discount_percent }}%</span>
                @elseif($hasPromo)
                    <span class="p-card-badge">Promo</span>
                @endif
            </div>
            <div class="p-card-body">
                @if($product->sponsored_campaign_id)@include('partials.sponsored-tag', ['style' => 'align-self:flex-start;margin-bottom:2px'])@endif
                <div class="p-card-name">{{ $product->name }}</div>
                @if($product->category)
                    <div class="p-card-cat">{{ $product->category }}</div>
                @endif
                @if($product->shop)
                    <div class="p-card-shop">
                        <span style="font-size:11px">{!! \App\Support\IconLibrary::svg('store', '', 12) !!}</span>
                        {{ Str::limit($product->shop->name, 22) }}
                    </div>
                @endif
            </div>
        </a>
        <div class="p-card-footer">
            <div>
                <span class="p-card-price">{{ number_format($product->current_price, 0, ',', ' ') }} GNF</span>
                @if($product->is_flash_active)
                    <span class="p-card-orig">{{ number_format($product->price, 0, ',', ' ') }}</span>
                @elseif($hasPromo)
                    <span class="p-card-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>
                @endif
            </div>
            <a href="{{ \App\Support\Campaigns\SponsoredPlacements::url(route('client.orders.createFromProduct', $product), $product) }}" class="p-card-cta">Commander</a>
        </div>
    </div>
    @endforeach
</div>

<div class="catalog-pagination">
    {{ $products->links() }}
</div>
@endif

@endsection
