{{--
    Modèle de page « Express » : la boutique qui vend, sur le schéma des boutiques à paiement à la livraison.
      Accueil   : sections dans l'ordre choisi par le vendeur (Boutique → Sections, App\Support\HomeSections) :
                  accueil (badges, titre, engagements, bouton, note réelle, produit sur socle), 3 engagements
                  illustrés, produits phares en carrousel, résultats réels en cercles, avis réels, questions
                  fréquentes, et les sections ajoutées (texte, image et texte, bannière, catégorie, questions).
      Catalogue : ?vue=produits, une catégorie (/s/{slug}/{categorie}) ou une recherche (?q=) — fil d'Ariane,
                  catégories, tri, grille « Ajouter au panier ».
    Chaque produit ouvre sa page longue (public.shops.express.product). Données : PageModels::data().
--}}
@php
    use App\Support\HomeSections as HS;
    $s = $pm['shop'];
    $q = trim((string) request('q', ''));
    $isCatalog = request('vue') === 'produits' || $activeCategory || $q !== '';
    $hero = collect($pm['products'])->first(fn ($p) => $p['featured'] && $p['photo']) ?? collect($pm['products'])->first(fn ($p) => $p['photo']);
    $ratingPct = $pm['rating'] ? (int) round($pm['rating']['avg'] * 20) : null;
    // Sections de l'accueil, dans l'ordre choisi par le vendeur (Boutique → Sections)
    $home = $pm['home']['express'] ?? HS::defaults('express');
@endphp
<!doctype html>
<html lang="fr">
<head>
@include('public.shops.models._head')
@include('public.shops.express._style')
<style>
/* Accueil */
.ex-hero { position: relative; overflow: hidden; background: linear-gradient(100deg, #fff 0%, #fff 38%, #ECECEC 70%, #DADADA 100%); }
.ex-hero .ex-wrap { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr); gap: 30px; align-items: center; padding-top: clamp(30px, 5vw, 56px); padding-bottom: clamp(70px, 9vw, 120px); }
.ex-pills { display: flex; gap: 8px; flex-wrap: wrap; }
.ex-pills span { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; background: #111; color: #fff; font: 700 11.5px var(--m-title); letter-spacing: .06em; text-transform: uppercase; }
.ex-hero h1 { margin: 18px 0 0; font: 800 clamp(30px, 4.6vw, 52px)/1.08 var(--m-title); letter-spacing: -.03em; text-wrap: balance; }
.ex-hero h1 .ex-acc { color: var(--acc); }
.ex-hero .ex-lead { margin: 16px 0 0; max-width: 54ch; color: var(--ink-2); font-size: 16.5px; line-height: 1.6; }
.ex-points { display: grid; gap: 10px; margin: 20px 0 0; padding: 0; list-style: none; }
.ex-points li { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 15px; }
.ex-points i { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #fff; color: var(--ink); box-shadow: inset 0 0 0 1px rgba(0,0,0,.1); font-style: normal; }
.ex-pills svg { flex-shrink: 0; }
.ex-hero .ex-btn { margin-top: 26px; }
.ex-proof { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 16px; font-size: 13px; color: var(--ink-2); }
.ex-proof b { color: var(--ink); }
.ex-stage { position: relative; display: flex; justify-content: center; align-items: flex-end; min-height: 380px; }
.ex-stage::before { content: ''; position: absolute; left: 50%; top: 46%; width: 360px; height: 360px; margin: -180px 0 0 -180px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,.95), rgba(255,255,255,0) 68%); }
.ex-stage-item { position: relative; z-index: 2; width: min(300px, 70%); aspect-ratio: 1; margin-bottom: 46px; border-radius: 24px; overflow: hidden; background: #fff; box-shadow: 0 34px 60px -30px rgba(0,0,0,.55); animation: exFloat 6s ease-in-out infinite; }
.ex-stage-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ex-stage-item .ex-noimg { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font: 800 54px var(--m-title); color: var(--acc); }
.ex-podium { position: absolute; z-index: 1; bottom: 0; left: 50%; width: min(330px, 80%); height: 70px; transform: translateX(-50%); border-radius: 50%; background: linear-gradient(#fff, #E6E6E6); box-shadow: 0 30px 40px -20px rgba(0,0,0,.35), inset 0 -10px 18px rgba(0,0,0,.06); }
.ex-podium::before { content: ''; position: absolute; left: 6%; right: 6%; top: -22px; height: 46px; border-radius: 50%; background: linear-gradient(#FDFDFD, #EFEFEF); box-shadow: 0 6px 12px -6px rgba(0,0,0,.25); }
.ex-spark { position: absolute; z-index: 3; color: #fff; filter: drop-shadow(0 0 6px rgba(255,255,255,.9)); animation: exTwinkle 3s ease-in-out infinite; }
@keyframes exFloat { 50% { transform: translateY(-12px); } }
@keyframes exTwinkle { 50% { opacity: .2; transform: scale(.6); } }
.ex-wave { position: absolute; left: 0; right: 0; bottom: -1px; width: 100%; height: clamp(50px, 7vw, 90px); display: block; }
.ex-feats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; max-width: 760px; margin: 0 auto; text-align: center; }
/* Engagements : même tuile pour les trois, icône fine dans la couleur de la boutique */
.ex-feat i { width: 64px; height: 64px; margin: 0 auto 12px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-style: normal; background: #F4F4F4; background: color-mix(in srgb, var(--acc) 9%, #fff); color: var(--acc); box-shadow: inset 0 0 0 1px rgba(0,0,0,.06); transition: transform 300ms var(--out); }
.ex-feat:hover i { transform: translateY(-4px) rotate(-4deg); }
.ex-feat b { display: block; font: 700 15px var(--m-title); }
.ex-feat span { display: block; margin-top: 3px; color: var(--ink-3); font-size: 12.5px; }
.ex-row { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(220px, 260px); gap: 18px; overflow-x: auto; padding: 8px 4px 18px; scroll-snap-type: x mandatory; scrollbar-width: none; }
.ex-row::-webkit-scrollbar { display: none; }
.ex-row .ex-card { scroll-snap-align: start; }
.ex-row-nav { display: flex; gap: 8px; }
.ex-row-nav button { width: 42px; height: 42px; border: 0; border-radius: 50%; background: #fff; color: var(--ink); cursor: pointer; box-shadow: 0 0 0 1px var(--line); font-size: 18px; }
.ex-row-nav button:hover { background: var(--soft); }
.ex-rings { display: flex; justify-content: center; gap: clamp(26px, 6vw, 70px); flex-wrap: wrap; margin-top: 30px; }
.ex-ring { display: flex; align-items: center; gap: 16px; text-align: left; }
.ex-ring svg { width: 96px; height: 96px; transform: rotate(-90deg); flex-shrink: 0; }
.ex-ring circle { fill: none; stroke-width: 9; }
.ex-ring .bg { stroke: var(--line); }
.ex-ring .fg { stroke: var(--acc); stroke-linecap: round; stroke-dasharray: 264; stroke-dashoffset: 264; transition: stroke-dashoffset 1600ms var(--out); }
.ex-ring.is-in .fg { stroke-dashoffset: var(--off); }
.ex-ring div { position: relative; }
.ex-ring div b { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font: 800 20px var(--m-title); color: var(--acc-dark); }
.ex-ring p { margin: 0; max-width: 18ch; font-weight: 600; font-size: 14px; color: var(--ink-2); }
/* Catalogue */
.ex-crumb { display: flex; gap: 8px; flex-wrap: wrap; padding: 20px 0 0; font-size: 13px; color: var(--ink-3); }
.ex-crumb a { color: var(--acc-dark); text-decoration: none; }
.ex-cat-bar { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; margin: 18px 0 24px; }
.ex-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.ex-chips a { min-height: 36px; padding: 0 16px; border-radius: 999px; display: inline-flex; align-items: center; text-decoration: none; font-size: 13.5px; font-weight: 600; color: var(--ink-2); box-shadow: inset 0 0 0 1px var(--line); }
.ex-chips a:hover { color: var(--ink); box-shadow: inset 0 0 0 1px var(--ink-3); }
.ex-chips a[aria-current="page"] { background: #111; color: #fff; box-shadow: none; }
.ex-sort { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--ink-3); }
.ex-sort select { min-height: 40px; padding: 0 12px; border: 0; border-radius: 10px; background: #fff; color: var(--ink); font: 600 13.5px var(--m-body); box-shadow: inset 0 0 0 1px var(--line); }
.ex-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }
.ex-empty { grid-column: 1 / -1; padding: 50px 20px; text-align: center; color: var(--ink-2); border-radius: 14px; background: var(--soft); }
@media (max-width: 960px) { .ex-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 760px) {
    .ex-hero .ex-wrap { grid-template-columns: minmax(0, 1fr); }
    .ex-hero { background: linear-gradient(180deg, #fff 0%, #fff 50%, #E4E4E4 100%); }
    .ex-stage { min-height: 300px; }
    .ex-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .ex-card-body { padding: 10px 10px 12px; }
    .ex-card .ex-btn { padding: 0 6px; font-size: 12.5px; white-space: nowrap; }
    .ex-feats { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .ex-feat i { width: 54px; height: 54px; }
    .ex-feat b { font-size: 13px; }
    .ex-ring svg { width: 80px; height: 80px; }
}
</style>
@include('public.shops.models._colors', ['theme' => 'express'])
</head>
<body>
@include('public.shops.models._top')
@include('public.shops.express._header', ['current' => $isCatalog ? 'products' : 'home'])

<main>
@if(!$isCatalog)
@foreach($home as $sec)
@continue(!$sec['on'] && empty($editFrame))
@if(!empty($editFrame))<div class="sxe-sec" data-sx="{{ $sec['id'] }}" data-sx-type="{{ $sec['type'] }}" data-sx-on="{{ $sec['on'] ? 1 : 0 }}" data-sx-name="{{ HS::TYPES[$sec['type']][0] ?? $sec['type'] }}" data-sx-builtin="{{ in_array($sec['type'], HS::BUILTIN['express'], true) ? 1 : 0 }}">@endif
@if($sec['on'])
@switch($sec['type'])
    @case('hero')
    @php $heroImg = HS::imgUrl($sec['d']['img']) ?? ($hero['photo'] ?? null); @endphp
    <section class="ex-hero" aria-labelledby="exTitle">
        <div class="ex-wrap">
            <div>
                @php $exI = fn ($k, $sz = 16) => '<svg width="' . $sz . '" height="' . $sz . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . HS::ICONS[$k][1] . '"/></svg>'; @endphp
                <div class="ex-pills" data-rv><span>{!! $exI('cash', 14) !!} Paiement à la livraison</span><span>{!! $exI('clock', 14) !!} Commande en 1 minute</span></div>
                <h1 id="exTitle" data-rv style="--d:80ms">{{ $sec['d']['title'] !== '' ? $sec['d']['title'] : ($s['slogan'] !== '' ? $s['slogan'] : HS::txt('express', $sec, 'title')) }}</h1>
                <p class="ex-lead" data-rv style="--d:140ms">{{ $sec['d']['text'] !== '' ? $sec['d']['text'] : 'Découvrez ' . $s['name'] . ' : ' . lcfirst(HS::txt('express', $sec, 'text')) }}</p>
                <ul class="ex-points" data-rv style="--d:200ms">
                    <li><i aria-hidden="true">{!! $exI('cash') !!}</i>Paiement à la livraison</li>
                    <li><i aria-hidden="true">{!! $exI('phone') !!}</i>Appel de confirmation avant l’envoi</li>
                    <li><i aria-hidden="true">{!! $exI($s['verified'] ? 'shield' : 'truck') !!}</i>{{ $s['verified'] ? 'Boutique vérifiée par Shopio' : 'Suivi de commande sur Shopio' }}</li>
                </ul>
                <a class="ex-btn" href="{{ $s['url'] }}?vue=produits" data-rv style="--d:260ms">{{ HS::txt('express', $sec, 'button') }} <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                @if($pm['rating'] || $pm['delivered'])
                <p class="ex-proof" data-rv style="--d:320ms">@if($pm['rating'])<span class="ex-stars" aria-hidden="true">★★★★★</span><b>{{ number_format($pm['rating']['avg'], 1, ',', ' ') }}/5</b><span>sur {{ $pm['rating']['count'] }} avis clients</span>@endif @if($pm['delivered'])<span>· Déjà <b>{{ number_format($pm['delivered'], 0, ',', ' ') }} commandes</b> livrées</span>@endif</p>
                @endif
            </div>
            <div class="ex-stage" data-rv style="--d:120ms" aria-hidden="true">
                <svg class="ex-spark" style="left:16%;top:12%" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/></svg>
                <svg class="ex-spark" style="right:18%;top:24%;animation-delay:1s" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/></svg>
                <svg class="ex-spark" style="left:26%;bottom:34%;animation-delay:2s" width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/></svg>
                <div class="ex-stage-item">@if($heroImg)<img src="{{ $heroImg }}" alt="" fetchpriority="high">@else<span class="ex-noimg">{{ $s['initials'] }}</span>@endif</div>
                <div class="ex-podium"></div>
            </div>
        </div>
        <svg class="ex-wave" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true"><path d="M0 40 C 320 90, 620 0, 900 34 S 1300 70, 1440 20 V90 H0z" fill="#C9C9C9" opacity=".45"/><path d="M0 60 C 360 100, 700 20, 1000 50 S 1340 80, 1440 46 V90 H0z" fill="#fff"/></svg>
    </section>
    @break

    @case('promise')
    @php $pIcons = [
        ['linear-gradient(135deg,#F97316,#DC2626)', '<path d="M1 6h13v10H1zM14 9h4l4 4v3h-8z"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>'],
        ['linear-gradient(135deg,#22C55E,#15803D)', '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/>'],
        ['linear-gradient(135deg,#38BDF8,#2563EB)', '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/>'],
    ]; @endphp
    <section style="padding:34px 0 10px" aria-label="Nos engagements">
        <div class="ex-wrap">
            <div class="ex-feats">
                @foreach($pIcons as $i => [$bg, $path])
                @php $it = HS::item('express', $sec, $i); @endphp
                <div class="ex-feat" data-rv style="--d:{{ $i * 80 }}ms"><i><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $path !!}</svg></i><b>{{ $it['title'] }}</b><span>{{ $it['text'] }}</span></div>
                @endforeach
            </div>
        </div>
    </section>
    @break

    @case('products')
    <section class="ex-sec" aria-labelledby="exFeatH">
        <div class="ex-wrap">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap" data-rv>
                <div><h2 class="ex-h2" id="exFeatH">{{ HS::txt('express', $sec, 'title') }}</h2>@if(HS::txt('express', $sec, 'text') !== '')<p class="ex-sub">{{ HS::txt('express', $sec, 'text') }}</p>@endif</div>
                <div class="ex-row-nav"><button type="button" data-row="-1" aria-label="Produits précédents">‹</button><button type="button" data-row="1" aria-label="Produits suivants">›</button></div>
            </div>
            <div class="ex-row" id="exRow" style="margin-top:20px"></div>
        </div>
    </section>
    @break

    @case('results')
    @if($pm['rating'] || $pm['delivered'])
    <section class="ex-sec" aria-labelledby="exResH" style="padding-top:0;text-align:center">
        <div class="ex-wrap">
            <h2 class="ex-h2" id="exResH" data-rv>@if($sec['d']['title'] !== ''){{ $sec['d']['title'] }}@else Des résultats qui <span class="ex-acc">parlent</span>@endif</h2>
            <div class="ex-rings">
                @if($pm['rating'])<div class="ex-ring" data-ring style="--off:{{ 264 - round(264 * $ratingPct / 100) }}"><div><svg viewBox="0 0 100 100" aria-hidden="true"><circle class="bg" cx="50" cy="50" r="42"/><circle class="fg" cx="50" cy="50" r="42"/></svg><b>{{ number_format($pm['rating']['avg'], 1, ',', ' ') }}</b></div><p>Note moyenne sur 5, d’après {{ $pm['rating']['count'] }} avis</p></div>@endif
                @if($pm['delivered'])<div class="ex-ring" data-ring style="--off:0"><div><svg viewBox="0 0 100 100" aria-hidden="true"><circle class="bg" cx="50" cy="50" r="42"/><circle class="fg" cx="50" cy="50" r="42"/></svg><b style="font-size:{{ strlen((string) $pm['delivered']) > 4 ? 15 : 20 }}px">{{ number_format($pm['delivered'], 0, ',', ' ') }}</b></div><p>Commandes livrées à nos clients</p></div>@endif
            </div>
            <p class="ex-sub" style="font-size:12.5px">Calculé sur les vrais avis et les vraies commandes de la boutique.</p>
            <a class="ex-btn is-pill" href="{{ $s['url'] }}?vue=produits" style="margin-top:22px;text-transform:none;letter-spacing:0">Découvrir nos produits</a>
        </div>
    </section>
    @endif
    @break

    @case('reviews')
    @if(count($pm['reviews']))
    <section class="ex-sec" aria-labelledby="exRevH" style="padding-top:0">
        <div class="ex-wrap">
            <h2 class="ex-h2" id="exRevH" style="text-align:center" data-rv>{{ HS::txt('express', $sec, 'title') }}</h2>
            <div class="ex-reviews">
                @foreach($pm['reviews']->take(3) as $i => $rv)
                <article class="ex-review" data-rv style="--d:{{ $i * 80 }}ms">
                    <span class="ex-stars" role="img" aria-label="{{ max(1, min(5, $rv['rating'])) }} sur 5">{{ str_repeat('★', max(1, min(5, $rv['rating']))) }}<span style="opacity:.25">{{ str_repeat('★', 5 - max(1, min(5, $rv['rating']))) }}</span></span>
                    <p>{{ $rv['text'] }}</p>
                    <footer><i aria-hidden="true">{{ mb_strtoupper(mb_substr($rv['name'], 0, 1)) }}</i><span>{{ $rv['name'] }}<small>Client vérifié</small></span></footer>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('faq')
    @include('public.shops.express._faq', ['faq' => $sec['d']['items'], 'faqTitle' => $sec['d']['title']])
    @break

    @default
    @include('public.shops.models._section', ['sec' => $sec, 'theme' => 'express'])
@endswitch
@endif
@if(!empty($editFrame))</div>@endif
@endforeach
@else
    <div class="ex-wrap" style="padding-bottom:clamp(48px,7vw,84px)">
        <nav class="ex-crumb" aria-label="Fil d’Ariane"><a href="{{ $s['url'] }}">Accueil</a><span aria-hidden="true">›</span>@if($activeCategory)<a href="{{ $s['url'] }}?vue=produits">Produits</a><span aria-hidden="true">›</span><span>{{ $activeCategory['name'] }}</span>@else<span>Produits</span>@endif</nav>
        <h1 class="ex-h2" style="margin-top:14px">@if($q !== '')Résultats pour « {{ $q }} »@elseif($activeCategory){{ $activeCategory['name'] }}@else Tous nos produits @endif</h1>
        <div class="ex-cat-bar">
            <nav class="ex-chips" aria-label="Catégories">
                <a href="{{ $s['url'] }}?vue=produits" @if(!$activeCategory && $q === '') aria-current="page" @endif>Tout</a>
                @foreach($pm['categories'] as $cat)<a href="{{ $cat['url'] }}" @if($activeCategory && $activeCategory['name'] === $cat['name']) aria-current="page" @endif>{{ $cat['name'] }}</a>@endforeach
            </nav>
            <label class="ex-sort">Trier par <select id="exSort"><option value="">Mis en avant</option><option value="new">Nouveautés</option><option value="asc">Prix croissant</option><option value="desc">Prix décroissant</option></select></label>
        </div>
        <p class="ex-sub" id="exCount" aria-live="polite" style="margin:0 0 16px"></p>
        <div class="ex-grid" id="exGrid"></div>
    </div>
@endif
</main>

@include('public.shops.express._footer', ['faq' => [], 'noFaq' => !$isCatalog])

@include('public.shops.models._kit')
@if(!empty($editFrame))@include('public.shops.models._edit-frame')@endif
<script>
(function () {
    'use strict';
    const S = window.SM, D = S.data, esc = S.esc, $ = id => document.getElementById(id);
    const off = p => p.old ? Math.round((1 - p.price / p.old) * 100) : 0;
    const tag = p => off(p) >= 5 ? '<span class="ex-tag is-sale">−' + off(p) + ' %</span>' : p.new ? '<span class="ex-tag">Nouveau</span>' : '';
    const media = p => '<a class="ex-card-img" href="' + esc(p.page) + '" aria-label="' + esc(p.name) + '">' + (p.photo ? '<img src="' + esc(p.photo) + '" alt="" loading="lazy">' : '<span class="ex-noimg">' + esc(p.name) + '</span>') + tag(p) + '</a>';
    const price = p => '<p class="ex-price">' + esc(S.money(p.price)) + (p.old ? '<s>' + esc(S.money(p.old)) + '</s>' : '') + '</p>';

    /* ── Accueil : produits phares en carrousel ── */
    if ($('exRow')) {
        const list = S.products.filter(p => p.featured).concat(S.products.filter(p => !p.featured)).slice(0, 10);
        $('exRow').innerHTML = list.map(p => '<article class="ex-card">' + media(p) + '<div class="ex-card-body"><h3><a href="' + esc(p.page) + '">' + esc(p.name) + '</a></h3>' + price(p) + '<a class="ex-btn" href="' + esc(p.page) + '">Commander</a></div></article>').join('') || '<p class="ex-sub">Les produits arrivent bientôt.</p>';
        document.querySelectorAll('[data-row]').forEach(b => b.addEventListener('click', () => $('exRow').scrollBy({ left: +b.dataset.row * Math.min(560, $('exRow').clientWidth * .8), behavior: S.reduce ? 'auto' : 'smooth' })));
    }

    /* ── Catalogue : filtre (catégorie, recherche), tri, « Ajouter au panier » ── */
    if ($('exGrid')) {
        const cat = @json($activeCategory['name'] ?? ''), q = @json(mb_strtolower($q));
        const norm = t => String(t).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
        let sort = '';
        function render() {
            let l = S.products.filter(p => (!cat || p.cat === cat) && (!q || norm(p.name + ' ' + p.cat + ' ' + p.desc).includes(norm(q))));
            if (sort === 'asc') l = l.slice().sort((a, b) => a.price - b.price);
            if (sort === 'desc') l = l.slice().sort((a, b) => b.price - a.price);
            if (sort === 'new') l = l.slice().sort((a, b) => (b.new ? 1 : 0) - (a.new ? 1 : 0));
            $('exCount').textContent = l.length + ' produit' + (l.length > 1 ? 's' : '');
            $('exGrid').innerHTML = l.length ? l.map(p => '<article class="ex-card">' + media(p) + '<div class="ex-card-body">' + (p.cat ? '<small style="color:var(--ink-3);font-size:12px">' + esc(p.cat) + '</small>' : '') + '<h3><a href="' + esc(p.page) + '">' + esc(p.name) + '</a></h3>' + price(p) +
                (p.variants ? '<a class="ex-btn" href="' + esc(p.page) + '">Choisir les options</a>' : '<button type="button" class="ex-btn" data-sm-add="' + p.id + '"' + (S.canBuy(p) ? '' : ' disabled') + '>' + esc(S.buyLabel(p)) + '</button>') + '</div></article>').join('')
                : '<div class="ex-empty">Aucun produit ne correspond' + (q ? ' à « ' + esc(q) + ' »' : '') + '. <a href="' + esc(D.shop.url) + '?vue=produits">Voir tous les produits</a></div>';
        }
        $('exSort').addEventListener('change', e => { sort = e.target.value; render(); });
        render();
    }

    /* ── Apparitions au défilement, cercles qui se remplissent ── */
    const io = 'IntersectionObserver' in window && !S.reduce ? new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } }), { rootMargin: '0px 0px -8% 0px' }) : null;
    document.querySelectorAll('[data-rv], [data-ring]').forEach(el => io ? io.observe(el) : el.classList.add('is-in'));
})();
</script>
</body>
</html>
