@if(isset($categoryGroups) && $categoryGroups->isNotEmpty())
@foreach($categoryGroups as $group)
<div>
    <div class="sec-hd">
        <div class="sec-title">{!! \App\Support\IconLibrary::categorySvg($group['name'], '', 18) !!} Populaire en <strong>{{ $group['name'] }}</strong></div>
        <a href="{{ route('client.dashboard') }}?{{ http_build_query(array_filter(array_merge($baseParams, ['cat' => $group['name']]))) }}#catalogue" class="sec-link">Voir tout →</a>
    </div>
    <div class="reco-row-outer">
        <div class="reco-row">
            @foreach($group['products'] as $gp)
            @php $gpHasPromo = $gp->original_price && $gp->original_price > $gp->price; @endphp
            <a href="{{ route('client.orders.createFromProduct', $gp) }}" class="reco-card">
                <div class="reco-card-img">
                    @if($gp->image)
                        <img src="{{ \App\Services\ImageOptimizer::url($gp->image, 'medium') }}" alt="{{ $gp->name }}" loading="lazy">
                    @else
                        <div class="reco-card-ph">{!! $si('bag', 30) !!}</div>
                    @endif
                    @if($gp->is_flash_active)
                        <span class="reco-card-badge">{!! \App\Support\IconLibrary::svg('zap', '', 11) !!} -{{ $gp->flash_discount_percent }}%</span>
                    @elseif(isset($gp->total_sold) && $gp->total_sold > 0)
                        <span class="reco-card-badge">{!! \App\Support\IconLibrary::svg('trophy', '', 11) !!} {{ $gp->total_sold }} vendus</span>
                    @elseif($gpHasPromo)
                        <span class="reco-card-badge">-{{ $gp->discount_percent }}%</span>
                    @endif
                    <button class="reco-card-fav {{ in_array($gp->id, $favoriteProductIds ?? []) ? 'favorited' : '' }}"
                            data-product-id="{{ $gp->id }}"
                            onclick="event.preventDefault();event.stopPropagation();toggleProductFavorite({{ $gp->id }}, this)"
                            title="{{ in_array($gp->id, $favoriteProductIds ?? []) ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                        {!! $si('heart', 13) !!}
                    </button>
                </div>
                <div class="reco-card-body">
                    <div class="reco-card-shop">{{ $gp->shop->name ?? '' }}</div>
                    <div class="reco-card-name">{{ $gp->name }}</div>
                    @php $srGp = $shopRatings[$gp->shop_id] ?? null; @endphp
                    @if($srGp && $srGp->cnt > 0)
                    <div class="reco-card-rating">{!! \App\Support\IconLibrary::svg('star', '', 12) !!} <strong>{{ number_format($srGp->avg_rating, 1) }}</strong> ({{ $srGp->cnt }})</div>
                    @endif
                    <div class="reco-card-price-row">
                        <span class="reco-card-price">{{ number_format($gp->current_price, 0, ',', ' ') }}</span>
                        @if($gp->is_flash_active)
                            <span class="reco-card-orig">{{ number_format($gp->price, 0, ',', ' ') }}</span>
                        @elseif($gpHasPromo)
                            <span class="reco-card-orig">{{ number_format($gp->original_price, 0, ',', ' ') }}</span>
                        @endif
                        <span style="font-size:10px;color:var(--muted)">{{ $gp->shop->currency ?? 'GNF' }}</span>
                    </div>
                    <button type="button" class="reco-card-addcart" onclick="event.preventDefault();event.stopPropagation();addToCart({{ $gp->id }}, this)">
                        {!! $si('cart', 13) !!} Ajouter
                    </button>
                    <div class="reco-card-actions-row">
                        <button type="button" class="reco-card-order" title="Commander" onclick="event.preventDefault();event.stopPropagation();window.location.href='{{ route('client.orders.createFromProduct', $gp) }}'">{!! $si('bag', 12) !!} Commander</button>
                        <button type="button" class="reco-card-contact" title="Contacter le vendeur" onclick="event.preventDefault();event.stopPropagation();window.location.href='{{ route('client.messages.index', $gp) }}'">{!! $si('chat', 12) !!} Contacter</button>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endforeach
@endif
