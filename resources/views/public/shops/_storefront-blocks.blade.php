{{--
    Blocs de la vitrine publique, dans l'ordre choisi dans l'éditeur de boutique.
    Inclus dans public/shops/products.blade.php, à l'intérieur de .sf-flow (flex en colonne) :
    chaque bloc reçoit son rang via style="order:…" ; le bloc « Tous les produits » (#sfProducts)
    est rendu par la page elle-même.
    Un bloc sans contenu réel n'est pas affiché ; en aperçu, le vendeur voit une indication à la place.
    data-sf-block = identifiant du bloc (clic dans l'aperçu → sélection dans l'éditeur).
    Variables : $shop, $sf, $sfSet, $sfCats (catégories + liens), $sfHome, $activeCategory, $categories, $featuredProducts, $latestReviews, $reviewAvg,
                $reviewCount, $devise, $isPreview, $selections
--}}
@php
    use App\Support\Storefront as SF;
    use App\Services\ImageOptimizer as Img;
    $sfIco = fn ($name, $size = 18) => \App\Support\IconLibrary::svg($name, '', $size);
    $sfHint = fn ($text) => '<div class="sf-hint">' . e($text) . '</div>';
    $sfImg = fn ($path, $size = 'medium') => $path ? Img::url($path, $size) : null;
    $sfMoney = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' ' . $devise;
    $sfBuy = function ($p) use ($sfSet) {
        $out = $p->stock !== null && $p->stock <= 0;
        if ($sfSet['vacation'] || $out) return '<span class="sf-btn is-off">' . e($sfSet['vacation'] ? 'Indisponible' : 'Rupture') . '</span>';
        return '<a href="' . e(route('client.orders.createFromProduct', $p)) . '" class="sf-btn">' . e($sfSet['btn_text']) . '</a>';
    };
@endphp

@foreach($sf['blocks'] as $i => $blk)
@continue(!$blk['on'] || $blk['type'] === 'products')
{{-- Page d'une catégorie : seulement le bandeau d'annonce et les catégories au-dessus des produits --}}
@continue(!empty($activeCategory) && !in_array($blk['type'], ['announcement', 'categories'], true))
@php $d = $blk['d']; $attrs = 'data-sf-block="' . e($blk['id']) . '" style="order: ' . $i . '"'; @endphp

@switch($blk['type'])

{{-- ══ Bandeau d'annonce ══ --}}
@case('announcement')
    @if($d['text'] !== '')
    <div class="sf-wrap" {!! $attrs !!}>
        <div class="sf-announce" role="note">
            <span class="sf-announce-ico" aria-hidden="true">{!! $sfIco('zap', 16) !!}</span>
            <span>{{ $d['text'] }}</span>
        </div>
    </div>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint("Bandeau d'annonce : écrivez votre phrase dans le panneau de droite (ex. « Livraison offerte ce week-end »).") !!}</div>
    @endif
    @break

{{-- ══ Catégories ══ --}}
@case('categories')
    @if(count($sfCats) > 1)
    <section class="sf-wrap" {!! $attrs !!} aria-labelledby="sfCatTitle">
        @unless(!empty($activeCategory))<h2 class="sf-h2 sf-cat-h" id="sfCatTitle">Catégories</h2>@endunless
        <nav class="sf-chips" aria-label="Catégories de la boutique">
            <a class="sf-chip {{ empty($activeCategory) ? 'is-on' : '' }}" href="{{ $sfHome }}" @if(empty($activeCategory)) aria-current="page" @endif>Tout</a>
            @foreach($sfCats as $c)
            @php $on = ($activeCategory['slug'] ?? '') === $c['slug']; @endphp
            <a class="sf-chip {{ $on ? 'is-on' : '' }}" href="{{ $c['url'] }}" @if($on) aria-current="page" @endif>{{ $c['name'] }}</a>
            @endforeach
        </nav>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Catégories : apparaît dès que vos produits ont au moins deux catégories différentes.') !!}</div>
    @endif
    @break

{{-- ══ Produit(s) à la une ══ --}}
@case('featured')
    @if($featuredProducts->isNotEmpty())
    <section class="sf-wrap" {!! $attrs !!} aria-label="{{ $featuredProducts->count() > 1 ? 'Produits à la une' : 'Produit à la une' }}">
        <div class="sf-featured-row {{ $featuredProducts->count() > 1 ? 'is-multi' : '' }}">
        @foreach($featuredProducts as $fp)
        @php $fpOld = $sfSet['show_strike'] ? ($fp->is_flash_active ? $fp->price : (($fp->original_price && $fp->original_price > $fp->price) ? $fp->original_price : null)) : null; @endphp
        <div class="sf-featured">
            <a href="{{ route('client.orders.createFromProduct', $fp) }}" class="sf-featured-img" tabindex="-1" aria-hidden="true">
                @if($fp->image)
                    <img src="{{ Img::url($fp->image, 'medium') ?? asset('storage/' . $fp->image) }}" alt="" width="800" height="800" loading="lazy">
                @else
                    <span class="sf-featured-ph">{!! \App\Support\IconLibrary::categorySvg($fp->category, '', 56) !!}</span>
                @endif
            </a>
            <div class="sf-featured-txt">
                <span class="sf-kicker">Produit à la une</span>
                <h2 class="sf-featured-name">{{ $fp->name }}</h2>
                <div class="sf-featured-price">
                    <strong>{{ $sfMoney($fp->current_price) }}</strong>
                    @if($fpOld)<s>{{ number_format($fpOld, 0, ',', ' ') }}</s>@endif
                </div>
                @if($fp->description)<p class="sf-featured-desc">{{ Str::limit(strip_tags($fp->description), 140) }}</p>@endif
                <div class="sf-row">{!! $sfBuy($fp) !!}
                    @if($sfSet['show_nego'] && !$sfSet['vacation'])<a class="sf-nego" href="{{ route('client.messages.index', $fp) }}?offre=1">Négocier le prix</a>@endif
                </div>
            </div>
        </div>
        @endforeach
        </div>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Produit à la une : choisissez jusqu’à 3 produits dans le panneau de droite.') !!}</div>
    @endif
    @break

{{-- ══ Avis clients (vrais avis uniquement) ══ --}}
@case('reviews')
    @if($latestReviews->isNotEmpty())
    <section class="sf-wrap" {!! $attrs !!} aria-labelledby="sfRevTitle">
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
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Avis clients : apparaît dès qu’un client livré aura laissé un avis écrit. Les avis viennent uniquement de vrais clients.') !!}</div>
    @endif
    @break

{{-- ══ Paiement et livraison ══ --}}
@case('delivery')
    <section class="sf-wrap" {!! $attrs !!} aria-labelledby="sfDelTitle">
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
    @break

{{-- ══ Bloc contact ══ --}}
@case('contact')
    @if($shop->phone || $shop->address || $shop->email)
    <section class="sf-wrap" {!! $attrs !!} aria-labelledby="sfContactTitle">
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
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Bloc contact : ajoutez un téléphone ou une adresse dans Ma boutique pour l’afficher.') !!}</div>
    @endif
    @break

{{-- ══ Texte libre ══ --}}
@case('text')
    @if($d['title'] !== '' || $d['body'] !== '')
    <section class="sf-wrap" {!! $attrs !!}>
        <div class="sf-text">
            @if($d['title'] !== '')<h2 class="sf-h2b">{{ $d['title'] }}</h2>@endif
            @if($d['body'] !== '')<p>{{ $d['body'] }}</p>@endif
        </div>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Texte libre : écrivez un titre et un message dans le panneau de droite.') !!}</div>
    @endif
    @break

{{-- ══ Image et texte ══ --}}
@case('imgtext')
    @if($d['img'] || $d['title'] !== '' || $d['body'] !== '')
    <section class="sf-wrap" {!! $attrs !!}>
        <div class="sf-imgtext {{ $d['side'] === 'right' ? 'is-right' : '' }}">
            @if($d['img'])
                <img class="sf-imgtext-img" src="{{ $sfImg($d['img']) }}" alt="" loading="lazy" width="800" height="600">
            @elseif($isPreview)
                <div class="sf-imgtext-img sf-imgtext-ph">{!! $sfIco('image', 40) !!}<span>Ajoutez une photo</span></div>
            @endif
            <div class="sf-imgtext-txt">
                @if($d['title'] !== '')<h2 class="sf-h2b">{{ $d['title'] }}</h2>@endif
                @if($d['body'] !== '')<p>{{ $d['body'] }}</p>@endif
                <a href="#sfProducts" class="sf-btn sf-btn-alt">Voir les produits</a>
            </div>
        </div>
    </section>
    @endif
    @break

{{-- ══ Vidéo (YouTube intégré au clic, sinon lien vers la plateforme) ══ --}}
@case('video')
    @php $vid = SF::video($d['url']); @endphp
    @if($vid)
    <section class="sf-wrap" {!! $attrs !!}>
        @if($d['title'] !== '')<h2 class="sf-h2b">{{ $d['title'] }}</h2>@endif
        @if($vid['kind'] === 'youtube')
        <button type="button" class="sf-video" data-yt="{{ $vid['id'] }}" aria-label="Lire la vidéo {{ $d['title'] }}"
                style="background-image:url('https://i.ytimg.com/vi/{{ $vid['id'] }}/hqdefault.jpg')">
            <span class="sf-video-play" aria-hidden="true"><svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
        </button>
        @else
        <a class="sf-video sf-video-link" href="{{ $vid['url'] }}" target="_blank" rel="noopener">
            <span class="sf-video-play" aria-hidden="true"><svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
            <span class="sf-video-cap">Voir la vidéo sur {{ $vid['site'] }}</span>
        </a>
        @endif
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Vidéo : collez un lien YouTube ou TikTok dans le panneau de droite.') !!}</div>
    @endif
    @break

{{-- ══ Galerie photos ══ --}}
@case('gallery')
    @if(count($d['imgs']))
    <section class="sf-wrap" {!! $attrs !!}>
        @if($d['title'] !== '')<h2 class="sf-h2b">{{ $d['title'] }}</h2>@endif
        <div class="sf-gallery n{{ min(count($d['imgs']), 5) }}">
            @foreach($d['imgs'] as $g)
            <button type="button" class="sf-gallery-cell" onclick="openLb('{{ $sfImg($g) }}', '{{ addslashes($d['title']) }}')" aria-label="Agrandir la photo {{ $loop->iteration }}">
                <img src="{{ $sfImg($g, $loop->first ? 'medium' : 'thumb') }}" alt="" loading="lazy">
            </button>
            @endforeach
        </div>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Galerie : ajoutez jusqu’à 8 photos dans le panneau de droite.') !!}</div>
    @endif
    @break

{{-- ══ Compte à rebours (masqué une fois la promotion terminée) ══ --}}
@case('countdown')
    @php $cdEnd = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $d['end']); @endphp
    @if($cdEnd->isFuture())
    <section class="sf-wrap" {!! $attrs !!}>
        <div class="sf-countdown" data-end="{{ $cdEnd->toIso8601String() }}">
            <div>
                <h2 class="sf-countdown-title">{{ $d['title'] !== '' ? $d['title'] : 'Promotion' }}</h2>
                @if($d['sub'] !== '')<p>{{ $d['sub'] }}</p>@endif
            </div>
            <div class="sf-cd" role="timer" aria-label="Temps restant">
                @foreach(['j' => 'jours', 'h' => 'heures', 'm' => 'min'] as $k => $l)
                <div class="sf-cd-cell"><b data-cd="{{ $k }}">--</b><span>{{ $l }}</span></div>
                @endforeach
            </div>
        </div>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Compte à rebours : la date de fin est passée, les clients ne le voient plus. Choisissez une nouvelle date.') !!}</div>
    @endif
    @break

{{-- ══ Questions fréquentes ══ --}}
@case('faq')
    @php $faq = array_values(array_filter($d['items'], fn ($it) => $it['q'] !== '' && $it['a'] !== '')); @endphp
    @if(count($faq))
    <section class="sf-wrap" {!! $attrs !!}>
        <h2 class="sf-h2b">{{ $d['title'] !== '' ? $d['title'] : 'Questions fréquentes' }}</h2>
        <div class="sf-faq">
            @foreach($faq as $f)
            <details @if($loop->first) open @endif><summary>{{ $f['q'] }}</summary><p>{{ $f['a'] }}</p></details>
            @endforeach
        </div>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Questions fréquentes : chaque question a besoin d’une réponse pour s’afficher.') !!}</div>
    @endif
    @break

{{-- ══ Notre histoire ══ --}}
@case('story')
    @if($d['body'] !== '')
    <section class="sf-wrap" {!! $attrs !!}>
        <div class="sf-story">
            @if($shop->image)<img src="{{ Img::url($shop->image, 'thumb') ?? asset('storage/' . $shop->image) }}" alt="" width="120" height="120" loading="lazy">@endif
            <div>
                <h2 class="sf-h2b">{{ $d['title'] !== '' ? $d['title'] : 'Notre histoire' }}</h2>
                <p>{{ $d['body'] }}</p>
            </div>
        </div>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Notre histoire : racontez comment la boutique est née et ce qui vous distingue.') !!}</div>
    @endif
    @break

{{-- ══ Adresse et horaires ══ --}}
@case('hours')
    @php $addr = $d['addr'] !== '' ? $d['addr'] : (string) $shop->address; $open = SF::isOpenNow($d); @endphp
    @if($addr !== '')
    <section class="sf-wrap" {!! $attrs !!}>
        <div class="sf-hours">
            <div class="sf-iso" aria-hidden="true"><div class="sf-iso-pl"></div><svg class="sf-pin" width="34" height="44" viewBox="0 0 24 32"><path d="M12 0a12 12 0 0 0-12 12c0 9 12 20 12 20s12-11 12-20A12 12 0 0 0 12 0z" fill="var(--sf-accent)"/><circle cx="12" cy="12" r="4.5" fill="#fff"/></svg></div>
            <div class="sf-hours-txt">
                <h2 class="sf-h2b">Nous trouver</h2>
                <strong>{{ $addr }}</strong>
                @if($d['days'] !== '')<span>{{ $d['days'] }} · {{ $d['open'] }} – {{ $d['close'] }}</span>@endif
                <span class="sf-open {{ $open && !$sfSet['vacation'] ? 'is-open' : '' }}">{{ $sfSet['vacation'] ? 'En pause' : ($open ? 'Ouvert maintenant' : 'Fermé · ouvre à ' . $d['open']) }}</span>
                <a class="sf-btn sf-btn-alt" href="https://www.google.com/maps/search/?api=1&amp;query={{ rawurlencode($addr) }}" target="_blank" rel="noopener">Itinéraire</a>
            </div>
        </div>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Adresse et horaires : indiquez votre adresse dans le panneau de droite.') !!}</div>
    @endif
    @break

{{-- ══ Réseaux sociaux (seuls les réseaux renseignés) ══ --}}
@case('social')
    @php $links = SF::socialLinks($d); @endphp
    @if(count($links))
    <section class="sf-wrap" {!! $attrs !!}>
        <div class="sf-social">
            <h2 class="sf-h2b">Suivez-nous</h2>
            <div class="sf-social-row">
                @foreach($links as [$label, $url, $color])
                <a href="{{ $url }}" target="_blank" rel="noopener" class="sf-social-btn" style="--c: {{ $color }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Réseaux sociaux : ajoutez au moins un lien (WhatsApp, Facebook, TikTok ou Instagram).') !!}</div>
    @endif
    @break

{{-- ══ Sélection de produits ══ --}}
@case('selection')
    @php $sel = $selections[$blk['id']] ?? collect(); @endphp
    @if($sel->isNotEmpty())
    <section class="sf-wrap" {!! $attrs !!}>
        <h2 class="sf-h2b">{{ $d['title'] !== '' ? $d['title'] : ($d['category'] !== '' ? $d['category'] : 'Notre sélection') }}</h2>
        <div class="sf-sel">
            @foreach($sel as $p)
            @php $pOld = $sfSet['show_strike'] && $p->original_price && $p->original_price > $p->current_price ? $p->original_price : null; @endphp
            <article class="sf-sel-card">
                <a href="{{ route('client.orders.createFromProduct', $p) }}" class="sf-sel-img" tabindex="-1" aria-hidden="true">
                    @if($p->image)<img src="{{ Img::url($p->image, 'thumb') ?? asset('storage/' . $p->image) }}" alt="" loading="lazy">@else{!! \App\Support\IconLibrary::categorySvg($p->category, '', 40) !!}@endif
                </a>
                <a href="{{ route('client.orders.createFromProduct', $p) }}" class="sf-sel-name">{{ $p->name }}</a>
                <div class="sf-sel-price"><strong>{{ $sfMoney($p->current_price) }}</strong>@if($pOld)<s>{{ number_format($pOld, 0, ',', ' ') }}</s>@endif</div>
                {!! $sfBuy($p) !!}
            </article>
            @endforeach
        </div>
        @php $selCat = $d['category'] !== '' ? collect($sfCats)->firstWhere('name', $d['category']) : null; @endphp
        @if($selCat && $selCat['count'] > $sel->count())<a class="sf-sel-more" href="{{ $selCat['url'] }}">Voir les {{ $selCat['count'] }} produits « {{ $selCat['name'] }} » →</a>@endif
    </section>
    @elseif($isPreview)
    <div class="sf-wrap" {!! $attrs !!}>{!! $sfHint('Sélection de produits : aucun produit en ligne dans cette catégorie.') !!}</div>
    @endif
    @break

@endswitch
@endforeach
