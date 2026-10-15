{{--
    Carte produit 3D de l'accueil (inclinaison qui suit la souris + reflet).
    Variables : $p (Product), $favoritedIds, $shopRatings, $showStock (bool, ventes flash)
--}}
@php
    $img      = $p->image ? (\App\Services\ImageOptimizer::url($p->image, 'thumb') ?? asset('storage/' . $p->image)) : null;
    $isFlash  = $p->is_flash_active;
    $hasPromo = !$isFlash && $p->original_price && $p->original_price > $p->price;
    $old      = $isFlash ? $p->price : ($hasPromo ? $p->original_price : null);
    $pct      = ($old && $old > 0) ? (int) round((1 - $p->current_price / $old) * 100) : null;
    $stock    = $p->stock;
    $out      = $stock !== null && $stock <= 0;
    $fav      = in_array($p->id, $favoritedIds ?? [], true);
    $url      = route('client.orders.createFromProduct', $p);
    $rating   = ($p->shop && isset(($shopRatings ?? [])[$p->shop->user_id])) ? $shopRatings[$p->shop->user_id] : null;
    $gnf      = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' GNF';
@endphp
<article class="c3 tilt3d">
    <div class="c3-media">
        <a href="{{ $url }}" class="c3-img" aria-label="{{ $p->name }}">
            @if($img)
                <img src="{{ $img }}" alt="{{ $p->name }}" loading="lazy" decoding="async" width="320" height="320">
            @else
                <span class="c3-ph">{!! \App\Support\IconLibrary::categorySvg($p->category ?? '', '', 72) !!}</span>
            @endif
        </a>
        @if($pct)
            <span class="b3 b3-y num">-{{ $pct }} %</span>
        @elseif($p->is_featured)
            <span class="b3 b3-w">Vedette</span>
        @endif
        @auth
            @if(auth()->user()->role === 'client')
            <button type="button" class="c3-fav {{ $fav ? 'on' : '' }}" data-fav-toggle data-url="{{ route('client.products.favorite.toggle', $p) }}"
                    aria-pressed="{{ $fav ? 'true' : 'false' }}" aria-label="{{ $fav ? 'Retirer des favoris' : 'Ajouter aux favoris' }} : {{ $p->name }}">
                {!! \App\Support\IconLibrary::svg('heart', '', 20) !!}
            </button>
            @endif
        @else
            <a href="{{ route('login') }}" class="c3-fav" aria-label="Se connecter pour ajouter aux favoris">{!! \App\Support\IconLibrary::svg('heart', '', 20) !!}</a>
        @endauth
    </div>
    <div class="c3-body">
        @if($p->shop)
        <div class="c3-shop">{!! \App\Support\IconLibrary::svg('shield', '', 15) !!}<span>{{ $p->shop->name }}</span></div>
        @endif
        <a class="c3-name" href="{{ $url }}">{{ $p->name }}</a>
        @if($rating)
            <div class="c3-rate num">{!! \App\Support\IconLibrary::svg('star', '', 15) !!}<strong>{{ number_format($rating['avg'], 1, ',', ' ') }}</strong> ({{ $rating['count'] }} avis)</div>
        @elseif(isset($p->total_sold) && $p->total_sold > 0)
            <div class="c3-rate num">{{ number_format($p->total_sold, 0, ',', ' ') }} vendu{{ $p->total_sold > 1 ? 's' : '' }}</div>
        @endif
        <div class="c3-price num"><span>{{ $gnf($p->current_price) }}</span>@if($old)<s>{{ $gnf($old) }}</s>@endif</div>
        @if(($showStock ?? false) && $stock !== null && $stock > 0 && $stock <= 20)
            <div class="c3-stock"><i style="--s: {{ max(0.08, min(1, $stock / 20)) }}"></i></div>
            <div class="c3-stock-txt">Plus que {{ $stock }} en stock</div>
        @endif
        @if($out)
            <span class="btn3 btn3-off" aria-disabled="true">Indisponible</span>
        @else
            <a class="btn3 btn3-p c3-cta" href="{{ $url }}">Commander</a>
        @endif
    </div>
    <span class="c3-glare" aria-hidden="true"></span>
</article>
