{{--
    resources/views/client/wishlist/index.blade.php
    Route : GET /client/wishlist → Client\ProductFavoriteController@index
--}}
@extends('layouts.app')
@section('title', 'Ma liste de souhaits')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --orange:    #f90;
    --orange-dk: #e47911;
    --navy:      #131921;
    --navy-2:    #232f3e;
    --red:       #b12704;
    --grey:      #f3f3f3;
    --grey-2:    #eaeded;
    --border:    #ddd;
    --text:      #0f1111;
    --text-2:    #333;
    --muted:     #565959;
    --surface:   #fff;
    --font:      'Open Sans', sans-serif;
    --display:   'Nunito', sans-serif;
    --r:         10px;
    --r-sm:      6px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
    --nav-h:     56px;
}
html { font-family: var(--font); }
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

.nav { background: var(--navy); height: var(--nav-h); display: flex; align-items: center; padding: 0 16px; gap: 10px; position: sticky; top: 0; z-index: 100; }
.nav-logo { font-family: var(--display); font-size: 18px; font-weight: 900; color: var(--orange); text-decoration: none; flex-shrink: 0; }
.nav-logo span { color: #fff; }
.nav-back { color: rgba(255,255,255,.8); font-size: 12.5px; font-weight: 600; text-decoration: none; padding: 5px 10px; border: 1px solid transparent; border-radius: var(--r-sm); transition: all .15s; white-space: nowrap; }
.nav-back:hover { border-color: rgba(255,255,255,.4); color: #fff; }

.page-wrap { max-width: 1080px; margin: 0 auto; padding: 24px 16px 80px; }
.page-title { font-family: var(--display); font-size: 22px; font-weight: 900; color: var(--text); margin-bottom: 4px; display: flex; align-items: center; gap: 10px; }
.page-sub { font-size: 12.5px; color: var(--muted); margin-bottom: 20px; }

.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
.pcard { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); position: relative; transition: box-shadow .15s, transform .15s; }
.pcard:hover { box-shadow: 0 6px 20px rgba(0,0,0,.1); transform: translateY(-2px); }
.pcard-img-wrap { position: relative; aspect-ratio: 1/1; background: var(--grey); overflow: hidden; }
.pcard-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pcard-img-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 40px; opacity: .3; }
.pcard-rm { position: absolute; top: 8px; right: 8px; width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,.92); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; color: var(--red); box-shadow: 0 2px 8px rgba(0,0,0,.18); transition: transform .15s; }
.pcard-rm:hover { transform: scale(1.1); background: #fff; }
.pcard-body { padding: 12px 14px 14px; }
.pcard-shop { font-size: 10.5px; color: var(--muted); margin-bottom: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pcard-name { font-size: 13px; font-weight: 700; color: var(--text); line-height: 1.35; margin-bottom: 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 35px; }
.pcard-price { font-size: 15px; font-weight: 800; color: var(--red); font-family: monospace; }
.pcard-link { display: block; text-decoration: none; color: inherit; }
.pcard-out { position: absolute; top: 8px; left: 8px; background: rgba(15,17,17,.82); color: #fff; font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }

.empty-wrap { text-align: center; padding: 60px 20px; }
.empty-ico { font-size: 52px; opacity: .3; margin-bottom: 14px; }
.empty-txt { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
.empty-sub { font-size: 13px; color: var(--muted); margin-bottom: 18px; }
.btn-browse { display: inline-flex; align-items: center; gap: 7px; background: var(--orange); color: var(--navy); font-weight: 800; font-size: 13.5px; padding: 11px 22px; border-radius: 50px; text-decoration: none; transition: background .15s; }
.btn-browse:hover { background: var(--orange-dk); }

.pag-wrap { margin-top: 20px; }

@media (max-width: 600px) {
    .page-wrap { padding: 16px 12px 60px; }
    .grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .pcard-name { font-size: 12px; min-height: 32px; }
}
</style>
@endpush

@section('content')

<nav class="nav">
    <a href="{{ route('client.dashboard') }}" class="nav-logo">Ma<span>Boutique</span></a>
    <a href="{{ route('client.dashboard') }}" class="nav-back">← Retour à l'accueil</a>
</nav>

<div class="page-wrap">
    <div class="page-title">❤️ Ma liste de souhaits</div>
    <div class="page-sub">{{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }} sauvegardé{{ $products->total() > 1 ? 's' : '' }}</div>

    @if($products->isEmpty())
        <div class="empty-wrap">
            <div class="empty-ico">💔</div>
            <div class="empty-txt">Votre liste de souhaits est vide</div>
            <div class="empty-sub">Cliquez sur le cœur d'un produit pour le sauvegarder ici.</div>
            <a href="{{ route('client.products.index') }}" class="btn-browse">🛍️ Découvrir des produits</a>
        </div>
    @else
        <div class="grid" id="wishGrid">
            @foreach($products as $product)
            <div class="pcard" data-product-id="{{ $product->id }}">
                <a href="{{ route('client.products.show', $product) }}" class="pcard-link">
                    <div class="pcard-img-wrap">
                        @if($product->out_of_stock)
                            <span class="pcard-out">Rupture de stock</span>
                        @endif
                        @if($product->image)
                            <img src="{{ \App\Services\ImageOptimizer::url($product->image, 'medium') ?? asset('storage/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy">
                        @else
                            <div class="pcard-img-ph">🏷️</div>
                        @endif
                    </div>
                </a>
                <button class="pcard-rm" onclick="removeWish({{ $product->id }}, this)" title="Retirer de la liste">✕</button>
                <div class="pcard-body">
                    <div class="pcard-shop">{{ $product->shop->name ?? '' }}</div>
                    <a href="{{ route('client.products.show', $product) }}" class="pcard-link">
                        <div class="pcard-name">{{ $product->name }}</div>
                    </a>
                    <div class="pcard-price">{{ number_format($product->price, 0, ',', ' ') }} {{ $product->shop->currency ?? 'GNF' }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @if($products->hasPages())
        <div class="pag-wrap">{{ $products->links() }}</div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script>
async function removeWish(productId, btn) {
    btn.disabled = true;
    try {
        const res = await fetch('/client/products/' + productId + '/favorite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        if (!data.favorited) {
            const card = document.querySelector('.pcard[data-product-id="' + productId + '"]');
            card?.remove();
        }
    } catch (e) {
        btn.disabled = false;
    }
}
</script>
@endpush
