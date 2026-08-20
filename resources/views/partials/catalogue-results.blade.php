{{--
=====================================================
PARTIALS/CATALOGUE-RESULTS.BLADE.PHP
=====================================================
Ventes flash + Recommandés + Meilleures ventes + bannière promo +
"Populaire par catégorie" + Catalogue/Résultats de recherche + pagination.
Rendu à l'intérieur de #resultsRoot dans welcome.blade.php ET renvoyé seul
(fragment HTML) par WelcomeController@index lors d'une requête AJAX, pour
que la recherche mette à jour la liste de produits sans recharger la page.
Variables attendues (fournies par WelcomeController@catalogueData, aussi
bien pour la page complète que pour le fragment AJAX) :
  $flashProducts, $recommendedProducts, $bestSellers, $products, $categories,
  $categoryGroups, $favoritedIds, $shopRatings
Les icônes viennent de \App\Support\IconLibrary (classe statique, pas de
closure Blade) : un appel de méthode fonctionne à l'identique que ce
fragment soit inclus dans welcome.blade.php ou rendu seul.
=====================================================
--}}

{{-- ══ VENTES FLASH (masqué pendant une recherche, comme Jumia) ══ --}}
@if($flashProducts->isNotEmpty() && !request('s'))
<div class="flash-section reveal" id="ventes-flash" style="scroll-margin-top:calc(var(--nav-h) + 12px)">
    <div class="flash-section-hd">
        <div class="flash-section-title">{!! \App\Support\IconLibrary::svg('zap', 'bolt', 20) !!} Ventes flash</div>
        <div class="flash-countdown" id="flashCountdown" data-seconds="{{ $flashProducts->min('flash_seconds_remaining') }}">
            {!! \App\Support\IconLibrary::svg('clock', '', 14) !!} Se termine dans <strong id="flashCountdownVal">--:--:--</strong>
        </div>
    </div>
    <div class="flash-row-outer">
        <div class="flash-row">
            @foreach($flashProducts as $product)
            <a href="{{ route('client.orders.createFromProduct', $product) }}" class="flash-card">
                <div class="flash-card-img">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy" width="168" height="112">
                    @else
                        <div class="flash-card-ph">{!! \App\Support\IconLibrary::svg('tag', '', 28) !!}</div>
                    @endif
                    <span class="flash-card-badge">-{{ $product->flash_discount_percent }}%</span>
                </div>
                <div class="flash-card-body">
                    @if($product->shop)<div class="flash-card-shop">{{ Str::limit($product->shop->name, 20) }}</div>@endif
                    <div class="flash-card-name">{{ $product->name }}</div>
                    <div class="flash-card-price-row">
                        <span class="flash-card-price">{{ number_format($product->current_price, 0, ',', ' ') }} GNF</span>
                        <span class="flash-card-orig">{{ number_format($product->price, 0, ',', ' ') }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ══ RECOMMANDÉS (masqué pendant une recherche, comme Jumia) ══ --}}
@if($recommendedProducts->isNotEmpty() && !request('s'))
<div class="sec-hd reveal">
    <div class="sec-title">{!! \App\Support\IconLibrary::svg('star') !!} Recommandés <strong>pour vous</strong></div>
    <span style="font-size:12px;color:var(--muted)">Sélectionnés par nos boutiques</span>
</div>
<div class="reco-row-outer">
    <div class="reco-row">
        @foreach($recommendedProducts as $product)
        @php $hasPromo = $product->original_price && $product->original_price > $product->price; @endphp
        <a href="{{ route('client.orders.createFromProduct', $product) }}" class="reco-card">
            <div class="reco-card-img">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy" width="190" height="130">
                @else
                    <div class="reco-card-ph">{!! \App\Support\IconLibrary::svg('tag', '', 32) !!}</div>
                @endif
                <span class="reco-card-badge">{!! \App\Support\IconLibrary::svg('star', '', 11) !!} Vedette</span>
            </div>
            <div class="reco-card-body">
                @if($product->shop)<div class="reco-card-shop">{{ Str::limit($product->shop->name, 22) }}</div>@endif
                <div class="reco-card-name">{{ $product->name }}</div>
                <div class="reco-card-price-row">
                    <span class="reco-card-price">{{ number_format($product->current_price, 0, ',', ' ') }} GNF</span>
                    @if($hasPromo)<span class="reco-card-orig">{{ number_format($product->original_price, 0, ',', ' ') }}</span>@endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- ══ MEILLEURES VENTES (quantités réellement vendues, commandes livrées) ══ --}}
@if($bestSellers->isNotEmpty())
<div class="sec-hd reveal">
    <div class="sec-title">{!! \App\Support\IconLibrary::svg('trophy') !!} Meilleures <strong>ventes</strong></div>
    <span style="font-size:12px;color:var(--muted)">Les plus achetés par nos clients</span>
</div>
<div class="reco-row-outer">
    <div class="cat-group-row">
        @foreach($bestSellers as $product)
            @include('partials.product-card', ['product' => $product, 'favoritedIds' => $favoritedIds, 'shopRatings' => $shopRatings, 'cardClass' => 'prod-card--row', 'soldCount' => $product->total_sold])
        @endforeach
    </div>
</div>
@endif

{{-- ══ BANNIÈRE PROMO (masquée pendant une recherche) ══ --}}
@if(!request('s'))
<a href="{{ route('shops.index') }}" class="promo-banner reveal">
    <span class="promo-banner-ico">{!! \App\Support\IconLibrary::svg('shield', '', 30) !!}</span>
    <span class="promo-banner-body">
        <span class="promo-banner-title">Boutiques vérifiées, livraison suivie</span>
        <span class="promo-banner-sub">Achetez en toute confiance : chaque boutique est approuvée avant de vendre sur {{ config('app.name', 'Shopio') }}.</span>
    </span>
    <span class="promo-banner-cta">Découvrir les boutiques {!! \App\Support\IconLibrary::svg('eye', '', 14) !!}</span>
</a>
@endif

{{-- ══ POPULAIRE PAR CATÉGORIE (masqué pendant une recherche/filtre) ══ --}}
@if($categoryGroups->isNotEmpty())
@foreach($categoryGroups as $group)
<div class="sec-hd reveal">
    <div class="sec-title">{!! \App\Support\IconLibrary::categorySvg($group['name']) !!} Populaire en <strong>{{ $group['name'] }}</strong></div>
    <a href="{{ url('/') }}?cat={{ urlencode($group['name']) }}#catalogue" class="sec-link">Tout voir →</a>
</div>
<div class="reco-row-outer">
    <div class="cat-group-row">
        @foreach($group['products'] as $product)
            @include('partials.product-card', ['product' => $product, 'favoritedIds' => $favoritedIds, 'shopRatings' => $shopRatings, 'cardClass' => 'prod-card--row'])
        @endforeach
    </div>
</div>
@endforeach
@endif

{{-- ══ CATALOGUE COMPLET / RÉSULTATS DE RECHERCHE ══ --}}
@php
    $isSearching = (bool) request('s');
    $isFiltering = (bool) request('cat');
@endphp

@if($isSearching)
{{-- Bandeau "résultats de recherche" façon Jumia : seuls les produits trouvés s'affichent --}}
<div class="search-banner reveal" id="catalogue" style="scroll-margin-top:calc(var(--nav-h) + 12px)">
    {!! \App\Support\IconLibrary::svg('search', '', 17) !!}
    <span>Résultats pour <strong>&laquo;&nbsp;{{ request('s') }}&nbsp;&raquo;</strong> — {{ number_format($products->total()) }} produit{{ $products->total() > 1 ? 's' : '' }} trouvé{{ $products->total() > 1 ? 's' : '' }}</span>
    <a href="{{ url('/') }}" class="search-banner-clear" data-live-clear data-noprogress>{!! \App\Support\IconLibrary::svg('x', '', 12) !!} Effacer</a>
</div>
@else
<div class="sec-hd reveal" id="catalogue" style="scroll-margin-top:calc(var(--nav-h) + 12px)">
    <div class="sec-title">
        @if($isFiltering)
            {!! \App\Support\IconLibrary::categorySvg(request('cat'), '', 20) !!} <strong>{{ request('cat') }}</strong>
        @else
            {!! \App\Support\IconLibrary::svg('cart') !!} Tous les <strong>produits</strong>
        @endif
    </div>
</div>
<div class="count-line">
    {{ number_format($products->total()) }} produit{{ $products->total() > 1 ? 's' : '' }} {{ $isFiltering ? 'trouvé' : 'disponible' }}{{ $products->total() > 1 ? 's' : '' }}
    @if($isFiltering)
        — <a href="{{ url('/') }}" class="reset-link" data-live-clear data-noprogress>{!! \App\Support\IconLibrary::svg('x', '', 11) !!} Réinitialiser</a>
    @endif
</div>
@endif

@if($categories->isNotEmpty())
<div class="cats">
    <a href="{{ url('/') }}?{{ http_build_query(array_filter(['s' => request('s')])) }}" class="cat-pill {{ !request('cat') ? 'active' : '' }}">Toutes</a>
    @foreach($categories as $cat)
    <a href="{{ url('/') }}?{{ http_build_query(array_filter(['cat' => $cat, 's' => request('s')])) }}" class="cat-pill {{ request('cat') === $cat ? 'active' : '' }}">{!! \App\Support\IconLibrary::categorySvg($cat, '', 14) !!} {{ $cat }}</a>
    @endforeach
</div>
@endif

@if($products->isEmpty())
<div class="prod-grid">
    <div class="c-empty">
        <span class="c-empty-ico">{!! \App\Support\IconLibrary::svg($isSearching ? 'search' : 'package', '', 48) !!}</span>
        <div class="c-empty-title">
            @if($isSearching)
                Aucun résultat pour «&nbsp;{{ request('s') }}&nbsp;»
            @else
                Aucun produit trouvé
            @endif
        </div>
        <div class="c-empty-sub">Essayez une autre recherche ou catégorie.</div>
    </div>
</div>
@else
<div class="prod-grid">
    @foreach($products as $product)
        @include('partials.product-card', ['product' => $product, 'favoritedIds' => $favoritedIds, 'shopRatings' => $shopRatings])
    @endforeach
</div>

<div class="c-pagination">{{ $products->links() }}</div>
@endif
