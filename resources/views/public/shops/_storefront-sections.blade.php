{{--
    Sections de la vitrine publique, réglables par le vendeur (« Personnaliser ma vitrine »).
    Inclus dans public/shops/products.blade.php, à l'intérieur de .sf-flow (flex en colonne) :
    chaque bloc reçoit son rang via style="order:…", la liste des produits (#sfProducts) aussi.
    Une section sans contenu réel n'est pas affichée ; en aperçu, le vendeur voit une indication à la place.
    Variables : $shop, $sf, $sfOrder, $sfOn, $categories, $featuredProduct, $latestReviews,
                $reviewAvg, $reviewCount, $devise, $isPreview
--}}
@php
    $sfIco = fn ($name, $size = 18) => \App\Support\IconLibrary::svg($name, '', $size);
    $sfHint = fn ($text) => '<div class="sf-hint">' . e($text) . '</div>';
@endphp

{{-- ══ Bandeau d'annonce ══ --}}
@if($sfOn['announcement'])
    @if($sf['announcement'] !== '')
    <div class="sf-wrap" style="order: {{ $sfOrder['announcement'] }}">
        <div class="sf-announce" role="note">
            <span class="sf-announce-ico" aria-hidden="true">{!! $sfIco('zap', 16) !!}</span>
            <span>{{ $sf['announcement'] }}</span>
        </div>
    </div>
    @elseif($isPreview)
    <div class="sf-wrap" style="order: {{ $sfOrder['announcement'] }}">{!! $sfHint("Bandeau d'annonce : écrivez votre texte dans le panneau de gauche (ex. « Livraison offerte ce week-end »).") !!}</div>
    @endif
@endif

{{-- ══ Catégories ══ --}}
@if($sfOn['categories'] && count($categories) > 1)
<section class="sf-wrap" style="order: {{ $sfOrder['categories'] }}" aria-labelledby="sfCatTitle">
    <h2 class="sf-h2" id="sfCatTitle">Catégories</h2>
    <div class="sf-chips" role="group" aria-label="Filtrer par catégorie">
        <button type="button" class="sf-chip is-on" data-sf-cat="">Tout</button>
        @foreach($categories as $cat)
        <button type="button" class="sf-chip" data-sf-cat="{{ strtolower($cat) }}">{{ $cat }}</button>
        @endforeach
    </div>
</section>
@endif

{{-- ══ Produit à la une ══ --}}
@if($sfOn['featured'])
    @if($featuredProducts->isNotEmpty())
    <section class="sf-wrap" style="order: {{ $sfOrder['featured'] }}" aria-label="{{ $featuredProducts->count() > 1 ? 'Produits à la une' : 'Produit à la une' }}">
        <div class="sf-featured-row {{ $featuredProducts->count() > 1 ? 'is-multi' : '' }}">
        @foreach($featuredProducts as $fp)
        @php $fpOld = $fp->is_flash_active ? $fp->price : (($fp->original_price && $fp->original_price > $fp->price) ? $fp->original_price : null); @endphp
        <div class="sf-featured">
            <a href="{{ route('client.orders.createFromProduct', $fp) }}" class="sf-featured-img" tabindex="-1" aria-hidden="true">
                @if($fp->image)
                    <img src="{{ \App\Services\ImageOptimizer::url($fp->image, 'medium') ?? asset('storage/' . $fp->image) }}" alt="" width="800" height="800" loading="lazy">
                @else
                    <span class="sf-featured-ph">{!! \App\Support\IconLibrary::categorySvg($fp->category, '', 56) !!}</span>
                @endif
            </a>
            <div class="sf-featured-txt">
                <span class="sf-kicker">Produit à la une</span>
                <h2 class="sf-featured-name">{{ $fp->name }}</h2>
                <div class="sf-featured-price">
                    <strong>{{ number_format($fp->current_price, 0, ',', ' ') }} {{ $devise }}</strong>
                    @if($fpOld)<s>{{ number_format($fpOld, 0, ',', ' ') }}</s>@endif
                </div>
                @if($fp->description)<p class="sf-featured-desc">{{ Str::limit(strip_tags($fp->description), 140) }}</p>@endif
                <a href="{{ route('client.orders.createFromProduct', $fp) }}" class="sf-btn">{!! $sfIco('cart', 16) !!} Commander</a>
            </div>
        </div>
        @endforeach
        </div>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" style="order: {{ $sfOrder['featured'] }}">{!! $sfHint('Produit à la une : choisissez jusqu’à 3 produits dans le panneau « Produit à la une » à gauche.') !!}</div>
    @endif
@endif

{{-- ══ Avis clients (vrais avis uniquement) ══ --}}
@if($sfOn['reviews'])
    @if($latestReviews->isNotEmpty())
    <section class="sf-wrap" style="order: {{ $sfOrder['reviews'] }}" aria-labelledby="sfRevTitle">
        <h2 class="sf-h2" id="sfRevTitle">Avis clients
            @if($reviewAvg)<small>{{ number_format($reviewAvg, 1, ',', ' ') }} sur 5 · {{ $reviewCount }} avis</small>@endif
        </h2>
        <div class="sf-reviews">
            @foreach($latestReviews as $rv)
            @php $rvParts = preg_split('/\s+/', trim($rv->client->name ?? 'Client')); @endphp
            <article class="sf-review">
                <div class="sf-stars">{!! \App\Support\IconLibrary::stars((float) $rv->rating, 14) !!}</div>
                <p>{{ Str::limit($rv->comment, 220) }}</p>
                <span class="sf-review-meta">{{ $rvParts[0] }}{{ isset($rvParts[1]) ? ' ' . mb_strtoupper(mb_substr($rvParts[1], 0, 1)) . '.' : '' }} · {{ $rv->created_at->translatedFormat('j F Y') }}</span>
            </article>
            @endforeach
        </div>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" style="order: {{ $sfOrder['reviews'] }}">{!! $sfHint('Avis clients : cette section apparaîtra dès qu’un client aura laissé un avis écrit.') !!}</div>
    @endif
@endif

{{-- ══ Paiement et livraison ══ --}}
@if($sfOn['delivery'])
<section class="sf-wrap" style="order: {{ $sfOrder['delivery'] }}" aria-labelledby="sfDelTitle">
    <h2 class="sf-h2" id="sfDelTitle">Paiement et livraison</h2>
    <div class="sf-info-grid">
        <div class="sf-info"><span class="sf-info-ico">{!! $sfIco('wallet') !!}</span><div><strong>Paiement à la livraison</strong><span>En espèces, quand vous recevez votre commande.</span></div></div>
        @if($shop->payout_wallet_type && $shop->payout_wallet_number)
        <div class="sf-info"><span class="sf-info-ico">{!! $sfIco('smartphone') !!}</span><div><strong>Paiement en ligne</strong><span>Par Mobile Money, au moment de la commande.</span></div></div>
        @endif
        <div class="sf-info"><span class="sf-info-ico">{!! $sfIco('truck') !!}</span><div><strong>Livraison suivie</strong><span>{{ $shop->address ? 'Expédiée depuis ' . Str::limit($shop->address, 60) . '.' : 'Suivez votre commande jusqu’à la livraison.' }}</span></div></div>
        <div class="sf-info"><span class="sf-info-ico">{!! $sfIco('message') !!}</span><div><strong>Vendeur joignable</strong><span>Posez vos questions ou proposez un prix dans le chat.</span></div></div>
    </div>
</section>
@endif

{{-- ══ Bloc contact ══ --}}
@if($sfOn['contact'] && ($shop->phone || $shop->address || $shop->email))
<section class="sf-wrap" style="order: {{ $sfOrder['contact'] }}" aria-labelledby="sfContactTitle">
    <div class="sf-contact">
        <div>
            <h2 class="sf-h2" id="sfContactTitle" style="color:#fff;margin-bottom:6px">Contacter {{ $shop->name }}</h2>
            <p>Une question sur un produit, une livraison ou un prix ? La boutique vous répond.</p>
        </div>
        <ul class="sf-contact-list">
            @if($shop->phone)
            @php $waNumber = preg_replace('/\D+/', '', $shop->phone); @endphp
            <li>{!! $sfIco('phone', 16) !!} <span>{{ $shop->phone }}</span> <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="sf-contact-link">WhatsApp</a></li>
            @endif
            @if($shop->address)<li>{!! $sfIco('map-pin', 16) !!} <span>{{ $shop->address }}</span></li>@endif
            @if($shop->email)<li>{!! $sfIco('mail', 16) !!} <span>{{ $shop->email }}</span></li>@endif
        </ul>
    </div>
</section>
@elseif($sfOn['contact'] && $isPreview)
<div class="sf-wrap" style="order: {{ $sfOrder['contact'] }}">{!! $sfHint('Bloc contact : ajoutez un téléphone ou une adresse dans Ma boutique pour l’afficher.') !!}</div>
@endif
