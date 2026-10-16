{{--
=====================================================
PARTIALS/HOME-3D.BLADE.PHP — Accueil Shopio « 3D et motion design »
=====================================================
Reprend la maquette de référence (Main.dc.html) avec la palette bleue Shopio
et les VRAIES données du site (produits, catégories, avis). Inclus par
welcome.blade.php. Tout est scopé sous .sp3 pour ne rien casser ailleurs.
Variables : $flashProducts, $recommendedProducts, $bestSellers, $categoryCounts,
$testimonials, $favoritedIds, $shopRatings, $stats
Règles motion : uniquement transform/opacity, contenu visible sans JS,
prefers-reduced-motion = scènes fixes et simples fondus.
=====================================================
--}}
@php
    $gnf = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' GNF';
    $I = fn ($n, $s = 24) => \App\Support\IconLibrary::svg($n, '', $s);

    /* Produits réels pour la scène du hero (avec photo uniquement) */
    $showcase = collect($flashProducts ?? [])->merge($recommendedProducts ?? [])->merge($bestSellers ?? [])
        ->filter(fn ($p) => $p->image)->unique('id')->values();
    $phoneSlides = $showcase->take(3);
    $laptopProds = $showcase->take(4);

    /* Catégories : les 7 plus fournies (comptes réels) + « Toutes » */
    $topCats = collect($categoryCounts ?? [])->take(7);
    $tints = ['#EAF2FF', '#FFF4DB', '#F1F4FA', '#EAF2FF', '#FFF4DB', '#F1F4FA', '#EAF2FF'];

    $flashEnds = ($flashProducts ?? collect())->isNotEmpty() ? (int) $flashProducts->min('flash_seconds_remaining') : 0;
    $hasBest = ($bestSellers ?? collect())->isNotEmpty();
    $catUrl = fn ($c) => url('/') . '?' . http_build_query(['cat' => $c]) . '#catalogue';

    /* Immeubles de la carte de livraison : x, y, largeur, profondeur, hauteur */
    $blds = [[30,20,60,50,40],[175,10,40,40,56],[250,20,50,32,34],[20,110,60,50,48],[380,110,50,40,44],[340,210,60,56,64],[180,250,50,46,30],[420,240,46,50,38],[60,200,40,36,26]];
@endphp

<div class="sp3" id="sp3">

{{-- ═════════════ 01 · HERO ═════════════ --}}
<section class="h3" id="hero3d" aria-labelledby="h3Title">
    <span class="tab3 tab3-b hide-m3" aria-hidden="true">01</span>
    <div class="w3 h3-in">
        <div class="h3-g">
            <div class="h3-copy">
                <span class="b3 b3-g in3" style="height:34px;padding:0 14px;font-size:14px">{!! $I('shield', 16) !!} Boutiques vérifiées · Paiement à la livraison</span>
                <h1 class="h3-title in3-mask d3-1" id="h3Title">Achetez en confiance. <span class="ext3">Payez à la livraison.</span></h1>
                <p class="h3-sub in3 d3-2">La marketplace tout-en-un : boutiques, produits et livraison partout en Guinée. Vous ne payez qu'à la réception.</p>
                <form class="in3 d3-3" role="search" action="{{ url('/') }}" method="GET" style="max-width:540px">
                    <label for="q3" class="sr3">Rechercher un produit</label>
                    <div class="search3">
                        {!! $I('search', 22) !!}
                        <input id="q3" name="s" type="search" placeholder="Rechercher un produit, une boutique…" autocomplete="off">
                        <button class="btn3 btn3-p" type="submit" style="min-height:44px;padding:0 20px">Rechercher</button>
                    </div>
                    @if($topCats->isNotEmpty())
                    <div class="chips3">
                        @foreach($topCats->keys()->take(4) as $c)<a class="chip3" href="{{ $catUrl($c) }}">{{ $c }}</a>@endforeach
                    </div>
                    @endif
                </form>
                <div class="in3 d3-4 h3-cta">
                    <a class="btn3 btn3-p btn3-lg" href="#catalogue">Voir les produits <span aria-hidden="true">→</span></a>
                    <a class="btn3 btn3-s btn3-lg" href="{{ route('register') }}">Ouvrir ma boutique</a>
                </div>
                <ul class="feats3">
                    <li class="feat3 in3 d3-2"><span class="feat3-ic">{!! $I('store') !!}</span>Marketplace multi-vendeurs</li>
                    <li class="feat3 in3 d3-3"><span class="feat3-ic">{!! $I('wallet') !!}</span>Paiement à la livraison</li>
                    <li class="feat3 in3 d3-4"><span class="feat3-ic">{!! $I('truck') !!}</span>Livraison suivie en temps réel</li>
                    <li class="feat3 in3 d3-5"><span class="feat3-ic">{!! $I('rotate') !!}</span>Retours faciles</li>
                </ul>
            </div>

            {{-- Scène 3D : pivote selon la souris / le doigt (±10° / ±20°) --}}
            <div class="scene3-wrap par3" aria-hidden="true">
                <div class="scene3-fit">
                    <div class="scene3">
                        <div class="stage3" id="stage3">
                            <div class="p3d" style="right:-160px;top:10px;width:600px;height:600px;transform:translateZ(-180px)"><div class="sphere3 sphere3-b blob3"></div></div>
                            <div class="p3d" style="right:330px;top:20px;width:120px;height:120px;transform:translateZ(-60px)"><div class="sphere3 sphere3-y blob3" style="animation-delay:200ms"></div></div>

                            <div class="p3d lap3" style="left:0;top:60px;width:540px">
                                <div class="lid3">
                                    <div class="lid3-screen">
                                        <div class="mini3-nav"><span class="mini3-logo"><img src="/images/shopio-logo-192.png" alt=""> <span>Shop<b>io</b></span></span><i></i><i></i><em></em></div>
                                        <div class="mini3-hero"><strong>Tout ce que vous cherchez,<br>au même endroit</strong><span></span></div>
                                        <div class="mini3-cats">
                                            @forelse($topCats->keys()->take(6) as $c)
                                                <div><span>{!! \App\Support\IconLibrary::categorySvg($c, '', 16) !!}</span><small>{{ Str::limit($c, 11, '') }}</small></div>
                                            @empty
                                                @for($i = 0; $i < 6; $i++)<div><span></span><small></small></div>@endfor
                                            @endforelse
                                        </div>
                                        <div class="mini3-sec">Meilleures ventes</div>
                                        <div class="mini3-prods">
                                            @for($i = 0; $i < 4; $i++)
                                                @php $mp = $laptopProds[$i] ?? null; @endphp
                                                <div>@if($mp)<img src="{{ \App\Services\ImageOptimizer::url($mp->image, 'thumb') ?? asset('storage/'.$mp->image) }}" alt=""><b class="num">{{ $gnf($mp->current_price) }}</b>@else<i></i>@endif</div>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <div class="deck3"></div>
                            </div>

                            <div class="p3d" style="right:10px;top:150px;width:200px;transform:translateZ(150px)">
                                <div class="phn3"><div class="float3 phone3">
                                    <div class="phone3-scr">
                                        <div class="mini3-logo" style="font-size:12px"><img src="/images/shopio-logo-192.png" alt=""> <span>Shop<b>io</b></span></div>
                                        <div class="phone3-hero">Tout ce que vous cherchez, au même endroit</div>
                                        <div class="phone3-slides">
                                            @forelse($phoneSlides as $k => $sp)
                                                <div class="phone3-slide {{ $k === 0 ? 'on' : '' }}">
                                                    <div class="phone3-img"><img src="{{ \App\Services\ImageOptimizer::url($sp->image, 'thumb') ?? asset('storage/'.$sp->image) }}" alt="">
                                                        @if($sp->is_flash_active)<span class="b3 b3-y" style="height:20px;font-size:10px">-{{ $sp->flash_discount_percent }} %</span>@endif</div>
                                                    <div class="phone3-name">{{ Str::limit($sp->name, 26) }}</div>
                                                    <div class="phone3-price num">{{ $gnf($sp->current_price) }}</div>
                                                    <div class="phone3-btn">Commander</div>
                                                </div>
                                            @empty
                                                <div class="phone3-slide on"><div class="phone3-img"></div><div class="phone3-name">&nbsp;</div><div class="phone3-btn">Commander</div></div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div></div>
                            </div>

                            <div class="p3d" style="left:360px;top:30px;transform:translateZ(230px)"><div class="float3 pill3">{!! $I('truck', 15) !!} Livraison suivie</div></div>
                            <div class="p3d" style="left:20px;top:440px;transform:translateZ(200px)"><div class="float3 pill3 pill3-d" style="animation-delay:1.2s">{!! $I('shield', 16) !!} Boutique vérifiée</div></div>
                            <div class="p3d" style="left:300px;top:470px;transform:translateZ(260px)"><div class="float3 coin3" style="animation-delay:.6s">GNF</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═════════════ VENTES FLASH ═════════════ --}}
@if(($flashProducts ?? collect())->isNotEmpty())
<section id="ventes-flash" class="w3 sec3" style="padding-top:56px" aria-labelledby="flash3Title">
    <div class="sec3-hd">
        <div class="rv3" style="max-width:560px">
            <span class="b3 b3-y" style="height:30px;font-size:13px">{!! $I('zap', 15) !!} Tous les jours</span>
            <h2 class="h2-3" id="flash3Title" style="margin:12px 0 8px">Ventes flash</h2>
            <p class="p3">Jusqu'à -50 % sur une sélection de produits, en quantités limitées.</p>
        </div>
        <div class="rv3 rv3-2 count3" role="timer" id="flash3Count" data-seconds="{{ $flashEnds }}" aria-live="off">
            <span class="count3-lbl">Se termine dans</span>
            <div class="num count3-d" aria-hidden="true"><span data-h>--</span>:<span data-m>--</span>:<span class="count3-s"><b data-s>--</b></span></div>
            <span class="sr3" data-sr>Compte à rebours</span>
        </div>
    </div>
    <div class="rail3" id="flashRail3">
        @foreach($flashProducts as $p)
            @include('partials.home-3d-card', ['p' => $p, 'showStock' => true])
        @endforeach
    </div>
</section>
@endif

{{-- ═════════════ COMMENT ÇA MARCHE ═════════════ --}}
<section class="band3-light" aria-labelledby="how3Title">
    <div class="w3 sec3">
        <div class="rv3" style="text-align:center;max-width:640px;margin:0 auto 56px">
            <h2 class="h2-3" id="how3Title" style="margin:0 0 12px">Comment ça marche</h2>
            <p class="p3">Trois étapes, zéro risque : vous payez seulement quand vous recevez votre colis.</p>
        </div>
        <div class="how3">
            <div class="how3-line hide-m3" aria-hidden="true"></div>
            <div class="how3-line how3-fill rv3-line hide-m3" aria-hidden="true"></div>
            <ol class="g3-3">
                <li class="rv3"><div class="flip3d step3-ic" style="background:#EAF2FF;color:#1668D8">{!! $I('search', 34) !!}</div><span class="num step3-n">Étape 1</span><h3>Je choisis</h3><p>Parcourez les boutiques vérifiées, comparez les prix et lisez les avis.</p></li>
                <li class="rv3 rv3-2"><div class="flip3d step3-ic" style="background:#FFF4DB;color:#8A5300">{!! $I('cart', 34) !!}</div><span class="num step3-n">Étape 2</span><h3>Je commande</h3><p>Commandez en quelques secondes, ou négociez le prix avec le vendeur dans le chat.</p></li>
                <li class="rv3 rv3-3"><div class="flip3d step3-ic" style="background:#1668D8;color:#fff">{!! $I('truck', 34) !!}</div><span class="num step3-n">Étape 3</span><h3>Je suis livré et je paie</h3><p>Le livreur vous apporte votre colis. Vous payez à la réception.</p></li>
            </ol>
        </div>
    </div>
</section>

{{-- ═════════════ CATÉGORIES ═════════════ --}}
@if($topCats->isNotEmpty())
<section id="categories3" class="w3 sec3" aria-labelledby="cat3Title">
    <div class="sec3-hd rv3"><h2 class="h2-3" id="cat3Title" style="margin:0">Achetez par catégorie</h2><a class="btn3 btn3-s" href="#catalogue">Toutes les catégories</a></div>
    <div class="g4-3">
        @foreach($topCats as $c => $n)
        <a class="cat3 rv3 {{ ['', 'rv3-2', 'rv3-3', 'rv3-4'][$loop->index % 4] }}" href="{{ $catUrl($c) }}" style="background:{{ $tints[$loop->index % 7] }}">
            <span class="cat3-name">{{ $c }}</span>
            <span class="cat3-n num">{{ number_format($n, 0, ',', ' ') }} produit{{ $n > 1 ? 's' : '' }} →</span>
            <span class="cat3-ic" aria-hidden="true">{!! \App\Support\IconLibrary::categorySvg($c, '', 76) !!}</span>
        </a>
        @endforeach
        <a class="cat3 rv3 rv3-4" href="#catalogue" style="background:#D6E6FF">
            <span class="cat3-name">Toutes les catégories</span>
            <span class="cat3-n num">{{ number_format($stats['total_products'] ?? 0, 0, ',', ' ') }} produits →</span>
            <span class="cat3-ic" aria-hidden="true">{!! $I('grid', 76) !!}</span>
        </a>
    </div>
</section>
@endif

{{-- ═════════════ NOTRE SÉLECTION (onglets) ═════════════ --}}
@if(($recommendedProducts ?? collect())->isNotEmpty() || $hasBest)
<section class="w3" style="padding-bottom:72px" aria-labelledby="sel3Title">
    <div class="sec3-hd rv3">
        <h2 class="h2-3" id="sel3Title" style="margin:0">Notre sélection</h2>
        @if(($recommendedProducts ?? collect())->isNotEmpty() && $hasBest)
        <div class="tabs3" role="tablist" aria-label="Notre sélection">
            <span class="tabs3-ind" aria-hidden="true"></span>
            <button class="tabbtn3" role="tab" id="t3-reco" aria-selected="true" aria-controls="p3-reco">Recommandés</button>
            <button class="tabbtn3" role="tab" id="t3-best" aria-selected="false" aria-controls="p3-best" tabindex="-1">Meilleures ventes</button>
        </div>
        @endif
    </div>
    @if(($recommendedProducts ?? collect())->isNotEmpty())
    <div class="g4-3 tabpanel3" role="tabpanel" id="p3-reco" aria-labelledby="t3-reco">
        @foreach($recommendedProducts->take(8) as $p) @include('partials.home-3d-card', ['p' => $p]) @endforeach
    </div>
    @endif
    @if($hasBest)
    <div class="g4-3 tabpanel3" role="tabpanel" id="p3-best" aria-labelledby="t3-best" @if(($recommendedProducts ?? collect())->isNotEmpty()) hidden @endif>
        @foreach($bestSellers->take(8) as $p) @include('partials.home-3d-card', ['p' => $p]) @endforeach
    </div>
    @endif
</section>
@endif

</div>{{-- /.sp3 (partie haute) --}}
