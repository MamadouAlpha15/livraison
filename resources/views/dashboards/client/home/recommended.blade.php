@if(isset($recommendedProducts) && $recommendedProducts->isNotEmpty())
@php
    // Boucle infinie fluide : on duplique une fois la liste (assez d'articles pour que ce soit joli),
    // l'animation CSS glisse de 0 à -50% donc revient pile sur la copie identique = pas de saut visible.
    $recoLoop = $recommendedProducts->count() >= 4;
    $recoPasses = $recoLoop ? 2 : 1;
@endphp
<div>
    <div class="sec-hd">
        <div class="sec-title"><strong>Recommandé</strong> pour vous</div>
    </div>
    <div class="reco-row-outer {{ $recoLoop ? 'reco-loop' : '' }}" id="recoRowOuter">
        <div class="reco-row {{ $recoLoop ? 'reco-auto' : '' }}" id="recoRow">
            @for ($pass = 0; $pass < $recoPasses; $pass++)
                @foreach($recommendedProducts as $rp)
                <a href="{{ route('client.orders.createFromProduct', $rp) }}" class="reco-card" @if($pass > 0) aria-hidden="true" tabindex="-1" @endif>
                    <div class="reco-card-img">
                        @if($rp->image)
                            <img src="{{ \App\Services\ImageOptimizer::url($rp->image, 'medium') }}" alt="{{ $rp->name }}" loading="lazy">
                        @else
                            <div class="reco-card-ph">{!! $si('bag', 30) !!}</div>
                        @endif
                        @if($rp->is_flash_active)
                            <span class="reco-card-badge">{!! \App\Support\IconLibrary::svg('zap', '', 11) !!} -{{ $rp->flash_discount_percent }}%</span>
                        @elseif($rp->has_promo)
                            <span class="reco-card-badge">-{{ $rp->discount_percent }}%</span>
                        @endif
                        <button class="reco-card-fav {{ in_array($rp->id, $favoriteProductIds ?? []) ? 'favorited' : '' }}"
                                data-product-id="{{ $rp->id }}"
                                onclick="event.preventDefault();event.stopPropagation();toggleProductFavorite({{ $rp->id }}, this)"
                                title="{{ in_array($rp->id, $favoriteProductIds ?? []) ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                            {!! $si('heart', 13) !!}
                        </button>
                    </div>
                    <div class="reco-card-body">
                        <div class="reco-card-shop">{{ $rp->shop->name ?? '' }}</div>
                        <div class="reco-card-name">{{ $rp->name }}</div>
                        @php $srRp = $shopRatings[$rp->shop_id] ?? null; @endphp
                        @if($srRp && $srRp->cnt > 0)
                        <div class="reco-card-rating">{!! \App\Support\IconLibrary::svg('star', '', 12) !!} <strong>{{ number_format($srRp->avg_rating, 1) }}</strong> ({{ $srRp->cnt }})</div>
                        @endif
                        <div class="reco-card-price-row">
                            <span class="reco-card-price">{{ number_format($rp->current_price, 0, ',', ' ') }}</span>
                            @if($rp->is_flash_active)
                                <span class="reco-card-orig">{{ number_format($rp->price, 0, ',', ' ') }}</span>
                            @elseif($rp->has_promo)
                                <span class="reco-card-orig">{{ number_format($rp->original_price, 0, ',', ' ') }}</span>
                            @endif
                            <span style="font-size:10px;color:var(--muted)">{{ $rp->shop->currency ?? 'GNF' }}</span>
                        </div>
                        <button type="button" class="reco-card-addcart" onclick="event.preventDefault();event.stopPropagation();addToCart({{ $rp->id }}, this)">
                            {!! $si('cart', 13) !!} Ajouter
                        </button>
                        <div class="reco-card-actions-row">
                            <button type="button" class="reco-card-order" title="Commander" onclick="event.preventDefault();event.stopPropagation();window.location.href='{{ route('client.orders.createFromProduct', $rp) }}'">{!! $si('bag', 12) !!} Commander</button>
                            <button type="button" class="reco-card-contact" title="Contacter le vendeur" onclick="event.preventDefault();event.stopPropagation();window.location.href='{{ route('client.messages.index', $rp) }}'">{!! $si('chat', 12) !!} Contacter</button>
                        </div>
                    </div>
                </a>
                @endforeach
            @endfor
        </div>
    </div>
</div>
@endif
