{{--
    En-tête « couverture » de la vitrine, réglé dans Faire grandir ma vitrine → Couverture et logo.
    Une à quatre diapos (photo importée, photo d'un produit, dégradé), voile sombre, logo qui chevauche
    le bas de la couverture, nom et slogan sur la photo ou dessous, puis les preuves de confiance réelles.
    « Boutique vérifiée », le statut et la note (à partir de 3 avis) ne sont pas masquables par le vendeur.
    Variables : $shop, $sf, $coverSlides, $proofs, $reviewAvg, $reviewCount, $deliveredCount, $sfStatus, $products
--}}
@php
    $cv = $sf['cover'];
    $lg = $sf['logo'];
    $cvNameOn = $cv['name_on'] || $lg['pos'] === 'over';
    $cvLogo = $lg['src'] === 'current' && $shop->image ? (\App\Services\ImageOptimizer::url($shop->image, 'thumb') ?? asset('storage/' . $shop->image)) : null;
    $cvIco = fn ($n, $s = 13) => \App\Support\IconLibrary::svg($n, '', $s);
    $cvResponse = $proofs['response'] ?? false;
@endphp
<header class="sf-cover sf-cover-h-{{ $cv['height'] }} sf-logo-{{ $lg['pos'] }} sf-logo-{{ $lg['shape'] }}" data-sf-block="__banner"
        style="--lg0: {{ $lg['size'] }}px; --cv-y: {{ $cv['y'] }}%; --cv-veil: {{ round($cv['veil'] / 100, 2) }}">
    <div class="sf-cover-media" @if(count($coverSlides) > 1) data-cover-carousel @endif>
        @foreach($coverSlides as $i => $s)
        <div class="sf-cover-slide{{ $i === 0 ? ' is-on' : '' }}" @isset($s['css']) style="background: {{ $s['css'] }}" @endisset>
            @isset($s['img'])<img src="{{ $s['img'] }}" alt="" @if($i === 0) fetchpriority="high" @else loading="lazy" @endif>@endisset
        </div>
        @endforeach
        <div class="sf-cover-veil" aria-hidden="true"></div>
        @if($cvNameOn)
        <div class="sf-cover-text">
            <div class="sf-cover-name">{{ $shop->name }}</div>
            @if($cv['slogan'] !== '')<p class="sf-cover-slogan">{{ $cv['slogan'] }}</p>@endif
        </div>
        @endif
        @if(count($coverSlides) > 1)
        <div class="sf-cover-dots">
            @foreach($coverSlides as $i => $s)<button type="button" data-cover-dot="{{ $i }}" aria-label="Afficher la diapo {{ $i + 1 }}" @if($i === 0) aria-current="true" @endif></button>@endforeach
        </div>
        @endif
    </div>
    <div class="sf-cover-logo">
        @if($cvLogo)<img src="{{ $cvLogo }}" alt="Logo de {{ $shop->name }}" width="140" height="140">
        @else<span class="sf-cover-initials" aria-label="{{ $shop->name }}">{{ \App\Support\Growth::initials($shop->name) }}</span>@endif
    </div>
    <div class="sf-cover-bar">
        @unless($cvNameOn)
        <div class="sf-cover-title">
            <div class="sf-cover-name">{{ $shop->name }}</div>
            @if($shop->is_approved)<span class="sf-cv-chip is-verified">{!! $cvIco('check', 12) !!} Boutique vérifiée</span>@endif
        </div>
        @if($cv['slogan'] !== '')<p class="sf-cover-sub">{{ $cv['slogan'] }}</p>@endif
        @endunless
        <div class="sf-cover-meta">
            @if($cvNameOn && $shop->is_approved)<span class="sf-cv-chip is-verified">{!! $cvIco('check', 12) !!} Boutique vérifiée</span>@endif
            @if($sfStatus)<span class="sf-status is-{{ $sfStatus[1] }}">{{ $sfStatus[0] }}</span>@endif
            @if($proofs['rating'] && $reviewAvg)<span class="sf-cv-chip is-proof">{!! $cvIco('star', 12) !!} {{ number_format($reviewAvg, 1, ',', ' ') }} · {{ $reviewCount }} avis</span>@endif
            @if($proofs['delivered'])<span class="sf-cv-chip is-proof">{!! $cvIco('check', 12) !!} {{ $deliveredCount }} commandes livrées</span>@endif
            @if($cvResponse)<span class="sf-cv-chip is-proof">{!! $cvIco('message', 12) !!} Répond en moins d’1 h</span>@endif
            <span class="sf-cv-chip">{!! $cvIco('truck', 12) !!} Paiement à la livraison</span>
            @if($shop->address)<span class="sf-cv-chip">{!! $cvIco('map-pin', 12) !!} {{ \Illuminate\Support\Str::limit($shop->address, 28) }}</span>@endif
            <span class="sf-cv-chip">{!! $cvIco('package', 12) !!} {{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}</span>
        </div>
    </div>
</header>
