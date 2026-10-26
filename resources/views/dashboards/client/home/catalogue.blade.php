<div id="catalogue" style="scroll-margin-top:80px">
    <div class="sec-hd">
        <div class="sec-title">
            @if(request('cat'))
                <strong>{{ request('cat') }}</strong>
            @else
                <strong>Tous</strong> les produits
            @endif
            <span style="font-size:14px;font-weight:500;color:var(--muted);font-family:var(--font)">
                (<span id="productCount">{{ $products->total() }}</span>)
            </span>
        </div>
        @if(request('cat'))
        <a href="{{ route('client.dashboard') }}#catalogue" class="sec-link">Réinitialiser ✕</a>
        @endif
    </div>

    {{-- ══ TRI + FILTRE PRIX (même principe que l'accueil) ══ --}}
    @php
        $sortLabels = ['newest' => 'Plus récents', 'price_asc' => 'Prix croissant', 'price_desc' => 'Prix décroissant', 'popular' => 'Plus vendus'];
        $sortUrl = fn ($v) => route('client.dashboard') . '?' . http_build_query(array_filter(array_merge($baseParams, ['sort' => $v !== 'newest' ? $v : null]))) . '#catalogue';
    @endphp
    <div class="filter-row">
        <div class="sort-row">
            {!! \App\Support\IconLibrary::svg('sort', '', 14) !!}
            <select class="sort-select" onchange="location.href=this.value" aria-label="Trier les produits">
                @foreach($sortLabels as $val => $label)
                <option value="{{ $sortUrl($val) }}" {{ $sort === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <form method="GET" action="{{ route('client.dashboard') }}#catalogue" class="price-filter-form">
            <input type="hidden" name="s" value="{{ request('s') }}">
            <input type="hidden" name="cat" value="{{ request('cat') }}">
            @if($sort !== 'newest')<input type="hidden" name="sort" value="{{ $sort }}">@endif
            {!! \App\Support\IconLibrary::svg('wallet', '', 14) !!}
            <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="Prix min" min="0" step="1000" inputmode="numeric" class="price-input">
            <span class="price-sep">—</span>
            <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="Prix max" min="0" step="1000" inputmode="numeric" class="price-input">
            <button type="submit" class="price-filter-btn">Filtrer</button>
            @if($minPrice !== null || $maxPrice !== null)
            <a href="{{ route('client.dashboard') }}?{{ http_build_query(array_filter(array_merge($baseParams, ['min_price' => null, 'max_price' => null]))) }}#catalogue" class="price-filter-clear" aria-label="Effacer le filtre prix">{!! \App\Support\IconLibrary::svg('x', '', 12) !!}</a>
            @endif
        </form>
    </div>

    @if($products->isEmpty())
    <div class="c-empty">
        <span class="c-empty-ico">{!! $si('bag',48) !!}</span>
        <div class="c-empty-title">Aucun produit disponible</div>
        <p class="c-empty-sub">Revenez bientôt.</p>
    </div>
    @else
    <div class="prod-grid" id="productsGrid">
        @foreach($products as $p)
        @php
            $pHasPromo = $p->original_price && $p->original_price > $p->price;
            $pUrl = \App\Support\Campaigns\SponsoredPlacements::url(route('client.orders.createFromProduct', $p), $p);
        @endphp
        <a href="{{ $pUrl }}" class="reco-card">
            <div class="reco-card-img">
                @if($p->image)
                    <img src="{{ \App\Services\ImageOptimizer::url($p->image, 'medium') }}" alt="{{ $p->name }}" loading="lazy">
                @else
                    <div class="reco-card-ph">{!! $si('bag', 30) !!}</div>
                @endif
                @if($p->is_flash_active)
                    <span class="reco-card-badge">{!! \App\Support\IconLibrary::svg('zap', '', 11) !!} -{{ $p->flash_discount_percent }}%</span>
                @elseif($pHasPromo)
                    <span class="reco-card-badge">-{{ $p->discount_percent }}%</span>
                @endif
                <button class="reco-card-fav {{ in_array($p->id, $favoriteProductIds ?? []) ? 'favorited' : '' }}"
                        data-product-id="{{ $p->id }}"
                        onclick="event.preventDefault();event.stopPropagation();toggleProductFavorite({{ $p->id }}, this)"
                        title="{{ in_array($p->id, $favoriteProductIds ?? []) ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                    {!! $si('heart', 13) !!}
                </button>
            </div>
            <div class="reco-card-body">
                @if($p->sponsored_campaign_id)@include('partials.sponsored-tag', ['style' => 'align-self:flex-start'])@endif
                <div class="reco-card-shop">{{ $p->shop->name ?? '' }}</div>
                <div class="reco-card-name">{{ $p->name }}</div>
                @php $srP = $shopRatings[$p->shop_id] ?? null; @endphp
                @if($srP && $srP->cnt > 0)
                <div class="reco-card-rating">{!! \App\Support\IconLibrary::svg('star', '', 12) !!} <strong>{{ number_format($srP->avg_rating, 1) }}</strong> ({{ $srP->cnt }})</div>
                @endif
                <div class="reco-card-price-row">
                    <span class="reco-card-price">{{ number_format($p->current_price, 0, ',', ' ') }}</span>
                    @if($p->is_flash_active)
                        <span class="reco-card-orig">{{ number_format($p->price, 0, ',', ' ') }}</span>
                    @elseif($pHasPromo)
                        <span class="reco-card-orig">{{ number_format($p->original_price, 0, ',', ' ') }}</span>
                    @endif
                    <span style="font-size:10px;color:var(--muted)">{{ $p->shop->currency ?? 'GNF' }}</span>
                </div>
                <button type="button" class="reco-card-addcart" onclick="event.preventDefault();event.stopPropagation();addToCart({{ $p->id }}, this)">
                    {!! $si('cart', 13) !!} Ajouter
                </button>
                <div class="reco-card-actions-row">
                    <button type="button" class="reco-card-order" title="Commander" onclick="event.preventDefault();event.stopPropagation();window.location.href='{{ $pUrl }}'">{!! $si('bag', 12) !!} Commander</button>
                    <button type="button" class="reco-card-contact" title="Contacter le vendeur" onclick="event.preventDefault();event.stopPropagation();window.location.href='{{ route('client.messages.index', $p) }}'">{!! $si('chat', 12) !!} Contacter</button>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <div class="c-pagination">{{ $products->links() }}</div>
    @endif
