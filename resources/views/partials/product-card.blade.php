{{--
=====================================================
PARTIALS/PRODUCT-CARD.BLADE.PHP
=====================================================
Carte produit réutilisée dans le catalogue complet et les rangées
"Populaire en ..." / "Meilleures ventes". Variables attendues :
  $product       → Product (avec relation shop chargée)
  $favoritedIds  → array<int> ids produits favoris du client connecté (optionnel)
  $shopRatings   → array<int, {avg, count}> note moyenne boutique par user_id vendeur (optionnel)
  $cardClass     → classes CSS supplémentaires, ex. 'prod-card--row' dans une rangée horizontale (optionnel)
  $soldCount     → int nombre d'unités vendues, affiché en badge "🏆 X vendus" (optionnel)
=====================================================
--}}
@php
    $hasPromo = $product->original_price && $product->original_price > $product->price;
    $stockOut = $product->stock !== null && $product->stock <= 0;
    $isFavorited = in_array($product->id, $favoritedIds ?? [], true);
    $isNew = $product->created_at && $product->created_at->gt(now()->subDays(10))
        && !$product->is_flash_active && !$hasPromo && !$product->is_featured;
    $vendeurId = $product->shop->user_id ?? null;
    $rating = $vendeurId && isset(($shopRatings ?? [])[$vendeurId]) ? $shopRatings[$vendeurId] : null;
@endphp
<div class="prod-card {{ $cardClass ?? '' }}">
    @auth
        @if(auth()->user()->role === 'client')
        <button type="button" class="prod-card-fav {{ $isFavorited ? 'is-fav' : '' }}"
                data-fav-toggle data-product-id="{{ $product->id }}"
                data-url="{{ route('client.products.favorite.toggle', $product) }}"
                aria-label="Ajouter aux favoris" aria-pressed="{{ $isFavorited ? 'true' : 'false' }}">
            {!! \App\Support\IconLibrary::svg('heart', '', 15) !!}
        </button>
        @endif
    @else
        <a href="{{ route('login') }}" class="prod-card-fav" aria-label="Se connecter pour ajouter aux favoris">
            {!! \App\Support\IconLibrary::svg('heart', '', 15) !!}
        </a>
    @endauth

    <a href="{{ route('client.orders.createFromProduct', $product) }}" class="prod-card-link" style="text-decoration:none;color:inherit">
        <div class="prod-card-img">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy" decoding="async" width="190" height="150">
            @else
                <div class="prod-card-ph">{!! \App\Support\IconLibrary::svg('bag', '', 34) !!}</div>
            @endif
            @if($product->is_flash_active)
                <span class="prod-card-badge" style="background:var(--navy)">{!! \App\Support\IconLibrary::svg('zap', '', 10) !!} -{{ $product->flash_discount_percent }}%</span>
            @elseif($hasPromo)
                <span class="prod-card-badge" style="background:#e53e3e">Promo</span>
            @elseif($product->is_featured)
                <span class="prod-card-badge" style="background:var(--brand)">{!! \App\Support\IconLibrary::svg('star', '', 10) !!} Vedette</span>
            @elseif($isNew)
                <span class="prod-card-badge" style="background:#059669">Nouveau</span>
            @endif
        </div>
        <div class="prod-card-body">
            <div class="prod-card-name">{{ $product->name }}</div>
            @if(isset($soldCount) && $soldCount > 0)
                <div class="prod-card-sold">{!! \App\Support\IconLibrary::svg('trophy', '', 11) !!} {{ number_format($soldCount) }} vendu{{ $soldCount > 1 ? 's' : '' }}</div>
            @endif
            @if($rating)
                <div class="prod-card-rating">{!! \App\Support\IconLibrary::stars($rating['avg'], 11) !!} <span>{{ number_format($rating['avg'], 1) }} ({{ $rating['count'] }})</span></div>
            @elseif($product->category)
                <div class="prod-card-cat">{{ $product->category }}</div>
            @endif
            @if($product->shop)<div class="prod-card-shop">{!! \App\Support\IconLibrary::svg('store', '', 12) !!} {{ Str::limit($product->shop->name, 22) }}</div>@endif
        </div>
    </a>
    <div class="prod-card-footer">
        <div>
            <span class="prod-card-price">{{ number_format($product->current_price, 0, ',', ' ') }} GNF</span>
            @if($product->is_flash_active)
                <span class="prod-card-orig">{{ number_format($product->price, 0, ',', ' ') }}</span>
            @elseif($hasPromo)
                <span class="prod-card-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>
            @endif
        </div>
        @if(!$stockOut)
            <a href="{{ route('client.orders.createFromProduct', $product) }}" class="prod-card-cta">Commander</a>
        @else
            <span class="prod-card-cta out">Indisponible</span>
        @endif
    </div>
</div>
