{{--
    Modèle de page « Prestige » : nuit et or. Fond #070F1F, or #D4A017, crème #F5EFE1, titres Playfair Display,
    angles presque droits. Carrousel 3D en anneau, bande défilante, collection à cadres dorés, pièce du moment
    inclinée selon la souris, médaillon flottant, avis en grande citation. Données : PageModels::data().
--}}
@php
    $s = $pm['shop'];
    $heroTitle = $s['slogan'] ?: $s['name'];
@endphp
<!doctype html>
<html lang="fr">
<head>
@include('public.shops.models._head')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --gold: #D4A017; --gold-2: #F1D27A; --night: #070F1F; --night-2: #0E1A30; --cream: #F5EFE1; --cream-2: #CFC6B2;
    --m-bg: var(--night); --m-surface: #0E1A30; --m-ink: var(--cream); --m-ink2: #BFB6A2; --m-line: rgba(212,160,23,.28); --m-soft: #16243D;
    --m-accent: var(--gold); --m-accent-ink: #1A1305; --m-accent-text: var(--gold-2); --m-radius: 4px; --m-btn-radius: 2px;
    --m-title: 'Playfair Display', Georgia, serif; --m-body: 'Plus Jakarta Sans', system-ui, sans-serif;
    --out: cubic-bezier(.22,1,.36,1);
}
body { background: radial-gradient(90% 60% at 80% -10%, rgba(212,160,23,.12), transparent 60%), var(--night); }
a { color: inherit; }
.pv-wrap { max-width: 1280px; margin: 0 auto; padding: 0 max(20px, env(safe-area-inset-left, 0px)); }
.pv-band { padding: 8px 16px; text-align: center; background: #050B17; color: var(--gold-2); font-size: 12.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; border-bottom: 1px solid var(--m-line); }
.pv-band span { margin: 0 10px; color: var(--gold); }
/* En-tête fixe flouté, menu souligné d'or au survol */
.pv-head { position: sticky; top: 0; z-index: 50; background: rgba(7,15,31,.72); backdrop-filter: blur(14px) saturate(1.3); -webkit-backdrop-filter: blur(14px) saturate(1.3); border-bottom: 1px solid var(--m-line); }
.pv-head .pv-wrap { display: flex; align-items: center; gap: 18px; min-height: 72px; }
.pv-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; min-width: 0; }
.pv-brand i { width: 42px; height: 42px; flex-shrink: 0; border-radius: 50%; border: 1.5px solid var(--gold); overflow: hidden; display: flex; align-items: center; justify-content: center; font: 700 15px var(--m-title); font-style: normal; color: var(--gold); }
.pv-brand i img { width: 100%; height: 100%; object-fit: cover; }
.pv-brand b { font: 700 21px var(--m-title); letter-spacing: .01em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pv-nav { display: flex; gap: 26px; margin-left: auto; }
.pv-nav a { position: relative; padding: 8px 0; text-decoration: none; font-size: 14px; font-weight: 600; letter-spacing: .04em; color: var(--cream-2); }
.pv-nav a::after { content: ''; position: absolute; left: 0; right: 0; bottom: 2px; height: 1.5px; background: var(--gold); transform: scaleX(0); transform-origin: left; transition: transform 300ms var(--out); }
.pv-nav a:hover { color: var(--cream); } .pv-nav a:hover::after { transform: scaleX(1); }
.pv-cart { position: relative; width: 48px; height: 48px; border: 1px solid var(--m-line); border-radius: 2px; background: transparent; color: var(--gold-2); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
.pv-cart span { position: absolute; top: -7px; right: -7px; min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px; background: var(--gold); color: #1A1305; font-size: 12px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
/* Accueil : titre en 96 px, carrousel 3D en anneau */
.pv-hero { position: relative; padding: 64px 0 40px; text-align: center; overflow: hidden; }
.pv-kicker { margin: 0 0 14px; color: var(--gold); font-size: 13px; font-weight: 800; letter-spacing: .3em; text-transform: uppercase; }
.pv-hero h1 { margin: 0 auto; max-width: 15ch; font: 700 clamp(46px, 8vw, 96px)/1 var(--m-title); letter-spacing: -.02em; text-wrap: balance; color: var(--cream); }
.pv-hero h1 em { color: var(--gold); font-style: italic; }
.pv-stage { position: relative; height: 430px; margin-top: 30px; perspective: 1300px; }
.pv-ring { position: absolute; left: 50%; top: 20px; width: 250px; height: 330px; margin-left: -125px; transform-style: preserve-3d; transition: transform 1100ms var(--out); }
.pv-item { position: absolute; inset: 0; border: 0; padding: 0; background: #0B1528; border-radius: 3px; overflow: hidden; cursor: pointer; box-shadow: 0 0 0 1px var(--m-line), 0 40px 70px -30px rgba(0,0,0,.8); }
.pv-item::before { content: ''; position: absolute; inset: 0; z-index: 1; background: rgba(5,10,22,.62); transition: opacity 900ms var(--out); pointer-events: none; }
.pv-item.is-front::before { opacity: 0; }
.pv-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pv-item span { z-index: 2; position: absolute; inset: auto 0 0; padding: 30px 14px 14px; background: linear-gradient(transparent, rgba(5,10,22,.9)); color: var(--cream); font: 600 15px var(--m-title); text-align: left; }
.pv-front { margin-top: 6px; min-height: 70px; }
.pv-front b { display: block; font: 600 26px var(--m-title); }
.pv-front span { color: var(--gold-2); font-size: 18px; font-weight: 800; font-variant-numeric: tabular-nums; }
.pv-arrows { position: absolute; left: 0; right: 0; top: 180px; display: flex; justify-content: space-between; pointer-events: none; padding: 0 max(4px, 3vw); }
.pv-arrows button { pointer-events: auto; width: 52px; height: 52px; border: 1px solid var(--m-line); border-radius: 50%; background: rgba(7,15,31,.7); color: var(--gold-2); font-size: 22px; cursor: pointer; transition: background-color 200ms; }
.pv-arrows button:hover { background: rgba(212,160,23,.18); }
.pv-btn { display: inline-flex; align-items: center; gap: 10px; min-height: 50px; padding: 0 26px; border: 1px solid var(--gold); background: transparent; color: var(--gold-2); font: 700 13.5px var(--m-body); letter-spacing: .16em; text-transform: uppercase; cursor: pointer; text-decoration: none; transition: background-color 250ms, color 250ms; }
.pv-btn:hover { background: var(--gold); color: #1A1305; }
/* Bande défilante */
.pv-marquee { overflow: hidden; border-block: 1px solid var(--m-line); padding: 18px 0; background: #050B17; }
.pv-track { display: flex; gap: 48px; width: max-content; animation: pvMarq 38s linear infinite; }
.pv-track span { font: italic 600 26px var(--m-title); color: var(--gold); white-space: nowrap; }
.pv-track span::after { content: '✦'; margin-left: 48px; font-style: normal; font-size: 14px; color: var(--cream-2); }
@keyframes pvMarq { to { transform: translateX(-50%); } }
/* La collection */
.pv-sec { padding: 80px 0; }
.pv-h2 { margin: 0; font: 700 clamp(34px, 5vw, 54px)/1.05 var(--m-title); letter-spacing: -.01em; }
.pv-sub { margin: 8px 0 0; color: var(--m-ink2); }
.pv-tools { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; flex-wrap: wrap; margin-bottom: 26px; }
.pv-tabs { display: flex; gap: 22px; flex-wrap: wrap; margin-top: 22px; }
.pv-tabs button { border: 0; background: none; padding: 6px 0; color: var(--cream-2); font: 600 15px var(--m-body); cursor: pointer; border-bottom: 1.5px solid transparent; }
.pv-tabs button[aria-pressed="true"] { color: var(--gold-2); border-color: var(--gold); }
.pv-tabs small { margin-left: 4px; color: var(--m-ink2); font-variant-numeric: tabular-nums; }
.pv-sort { min-height: 44px; padding: 0 14px; border: 1px solid var(--m-line); border-radius: 2px; background: var(--night-2); color: var(--cream); font: 600 14px var(--m-body); }
.pv-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 26px 20px; }
.pv-card { position: relative; }
.pv-card-media { position: relative; overflow: hidden; border-radius: 2px; }
.pv-card-img { position: relative; display: block; width: 100%; aspect-ratio: 1; padding: 0; border: 0; background: #0B1528; overflow: hidden; cursor: pointer; border-radius: 2px; }
.pv-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 900ms var(--out); }
.pv-card-img::after { content: ''; position: absolute; inset: 10px; border: 1px solid var(--gold); opacity: 0; transform: scale(1.04); transition: opacity 400ms var(--out), transform 400ms var(--out); pointer-events: none; }
.pv-card:hover .pv-card-img::after, .pv-card:focus-within .pv-card-img::after { opacity: 1; transform: none; }
.pv-card:hover .pv-card-img img { transform: scale(1.05); }
.pv-card-img .pv-noimg { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 16px; font: italic 600 20px var(--m-title); color: var(--gold); text-align: center; }
.pv-card-add { position: absolute; left: 10px; right: 10px; bottom: 10px; min-height: 44px; border: 0; background: var(--gold); color: #1A1305; font: 800 12.5px var(--m-body); letter-spacing: .12em; text-transform: uppercase; cursor: pointer; transform: translateY(130%); transition: transform 380ms var(--out); }
.pv-card:hover .pv-card-add, .pv-card:focus-within .pv-card-add { transform: none; }
.pv-card-add:disabled { background: #2A3550; color: var(--cream-2); cursor: not-allowed; }
.pv-card-fav { position: absolute; top: 10px; right: 10px; z-index: 2; width: 40px; height: 40px; border: 0; border-radius: 50%; background: rgba(7,15,31,.65); color: var(--cream); cursor: pointer; display: flex; align-items: center; justify-content: center; }
.pv-card-fav[aria-pressed="true"] { color: #F87171; } .pv-card-fav[aria-pressed="true"] svg { fill: currentColor; }
.pv-card h3 { margin: 14px 0 4px; font: 600 19px/1.25 var(--m-title); }
.pv-card p { margin: 0; color: var(--gold-2); font-weight: 800; font-variant-numeric: tabular-nums; }
.pv-card p s { margin-left: 8px; color: var(--m-ink2); font-weight: 500; }
.pv-card small { color: var(--m-ink2); font-size: 12px; letter-spacing: .1em; text-transform: uppercase; }
.pv-empty { padding: 50px 20px; text-align: center; color: var(--m-ink2); border: 1px dashed var(--m-line); }
/* Pièce du moment */
.pv-moment { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 60px; align-items: center; }
.pv-tilt { position: relative; perspective: 1000px; }
.pv-tilt-in { position: relative; aspect-ratio: 4 / 5; background: #0B1528; border-radius: 3px; overflow: hidden; transform-style: preserve-3d; transition: transform 300ms var(--out); box-shadow: 0 50px 90px -40px rgba(0,0,0,.9), 0 0 0 1px var(--m-line); }
.pv-tilt-in img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pv-tag { position: absolute; right: -16px; bottom: 34px; padding: 12px 18px; background: var(--gold); color: #1A1305; font: 800 18px var(--m-body); font-variant-numeric: tabular-nums; transform: translateZ(60px) rotate(-4deg); box-shadow: 0 20px 40px -14px rgba(0,0,0,.7); }
.pv-moment h2 em { color: var(--gold); }
.pv-moment p.pv-desc { color: var(--cream-2); line-height: 1.7; max-width: 52ch; }
.pv-engage { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1px; margin-top: 70px; background: var(--m-line); border: 1px solid var(--m-line); }
.pv-engage div { padding: 24px 20px; background: var(--night); }
.pv-engage b { display: block; font: 600 19px var(--m-title); color: var(--gold-2); margin-bottom: 6px; }
.pv-engage span { color: var(--m-ink2); font-size: 14px; }
.pv-medal { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 40px; align-items: center; margin-top: 80px; }
.pv-medal-img { width: 260px; height: 260px; border-radius: 50%; overflow: hidden; border: 1.5px solid var(--gold); padding: 10px; animation: pvFloat 6s ease-in-out infinite; }
.pv-medal-img > * { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block; background: #0B1528; }
@keyframes pvFloat { 50% { transform: translateY(-14px) rotate(2deg); } }
/* Avis en grande citation */
.pv-quote { text-align: center; max-width: 900px; margin: 0 auto; }
.pv-quote blockquote { margin: 0; font: italic 600 clamp(26px, 4vw, 42px)/1.3 var(--m-title); }
.pv-quote blockquote::before { content: '“'; display: block; font-size: 90px; line-height: .6; color: var(--gold); }
.pv-quote cite { display: block; margin-top: 18px; font-style: normal; color: var(--gold-2); font-weight: 700; letter-spacing: .12em; text-transform: uppercase; font-size: 13px; }
.pv-strip { display: grid; grid-auto-flow: column; grid-auto-columns: 160px; gap: 14px; overflow-x: auto; padding-bottom: 8px; scrollbar-color: var(--gold) transparent; }
.pv-strip button { border: 0; background: none; padding: 0; color: var(--cream); text-align: left; cursor: pointer; font: 600 14px var(--m-title); }
.pv-strip i { display: block; aspect-ratio: 1; background: #0B1528 center / cover; margin-bottom: 8px; box-shadow: 0 0 0 1px var(--m-line); }
.pv-foot { padding: 50px 0 calc(40px + env(safe-area-inset-bottom, 0px)); border-top: 1px solid var(--m-line); color: var(--m-ink2); font-size: 14px; text-align: center; }
.pv-foot b { display: block; font: 700 26px var(--m-title); color: var(--cream); margin-bottom: 8px; }
.pv-reveal { animation: pvRise 900ms var(--out) both; }
@keyframes pvRise { from { opacity: 0; transform: translateY(24px); } }
@media (max-width: 980px) {
    .pv-nav { display: none; }
    .pv-head .pv-cart { margin-left: auto; }
    .pv-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px 12px; }
    .pv-moment, .pv-medal { grid-template-columns: minmax(0, 1fr); gap: 34px; }
    .pv-engage { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .pv-medal-img { width: 200px; height: 200px; margin: 0 auto; }
    .pv-card-add { transform: none; position: static; width: 100%; }
    .pv-stage { height: 380px; } .pv-ring { width: 200px; height: 270px; margin-left: -100px; }
    .pv-arrows { top: 150px; }
    .pv-tag { right: 10px; }
}
</style>
</head>
<body>
@include('public.shops.models._top')
<div class="pv-band">@if($s['verified'])Boutique vérifiée<span>✦</span>@endif Paiement à la livraison<span>✦</span>{{ $s['address'] !== '' ? \Illuminate\Support\Str::limit($s['address'], 40) : 'Commande en ligne' }}</div>
<header class="pv-head">
    <div class="pv-wrap">
        <a class="pv-brand" href="{{ $s['url'] }}"><i>@if($s['logo'])<img src="{{ $s['logo'] }}" alt="">@else{{ $s['initials'] }}@endif</i><b>{{ $s['name'] }}</b></a>
        <nav class="pv-nav" aria-label="Sections"><a href="#produits">La collection</a><a href="#piece">Pièce du moment</a>@if(count($pm['reviews']))<a href="#avis">Avis</a>@endif<a href="#favoris">Favoris</a></nav>
        <button type="button" class="pv-cart" data-sm-cart aria-label="Ouvrir le panier"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7zM9 7a3 3 0 0 1 6 0"/></svg><span data-sm-count></span></button>
    </div>
</header>

<main>
    <section class="pv-hero" aria-labelledby="pvTitle">
        <div class="pv-wrap">
            <p class="pv-kicker">{{ $s['name'] }}</p>
            <h1 id="pvTitle" class="pv-reveal">{{ $heroTitle }}</h1>
        </div>
        <div class="pv-stage" id="pvStage" aria-roledescription="carrousel" aria-label="Pièces de la boutique">
            <div class="pv-ring" id="pvRing"></div>
            <div class="pv-arrows"><button type="button" id="pvPrev" aria-label="Pièce précédente">‹</button><button type="button" id="pvNext" aria-label="Pièce suivante">›</button></div>
        </div>
        <div class="pv-wrap pv-front" id="pvFront" aria-live="polite"></div>
    </section>

    @if(count($pm['categories']))
    <div class="pv-marquee" aria-hidden="true"><div class="pv-track" id="pvTrack"></div></div>
    @endif

    <section class="pv-sec" id="produits" aria-labelledby="pvColH">
        <div class="pv-wrap">
            <div class="pv-tools">
                <div><h2 class="pv-h2" id="pvColH">La collection</h2><p class="pv-sub" id="pvCount"></p>
                    <div class="pv-tabs" role="group" aria-label="Catégories" id="pvTabs"></div></div>
                <label><span class="sm-sr">Trier</span><select class="pv-sort" id="pvSort"><option value="">Mis en avant</option><option value="asc">Prix croissant</option><option value="desc">Prix décroissant</option><option value="new">Nouveautés</option></select></label>
            </div>
            <div class="pv-grid" id="pvGrid"></div>
        </div>
    </section>

    <section class="pv-sec" id="piece" aria-labelledby="pvMomentH" style="background:linear-gradient(180deg,transparent,rgba(212,160,23,.05))">
        <div class="pv-wrap" id="pvMoment"></div>
    </section>

    @if(count($pm['reviews']))
    <section class="pv-sec" id="avis" aria-label="Avis de clients">
        <div class="pv-wrap pv-quote">
            @php $r = $pm['reviews'][0]; @endphp
            <blockquote>{{ $r['text'] }}</blockquote>
            <cite>{{ $r['name'] }} · {{ str_repeat('★', max(1, min(5, $r['rating']))) }}@if($pm['rating']) · note {{ number_format($pm['rating']['avg'], 1, ',', ' ') }} sur {{ $pm['rating']['count'] }} avis @endif</cite>
        </div>
    </section>
    @endif

    <section class="pv-sec" id="favoris" aria-labelledby="pvFavH" style="padding-top:20px">
        <div class="pv-wrap">
            <h2 class="pv-h2" id="pvFavH" style="font-size:34px">Vos favoris</h2>
            <div id="pvFavs" style="margin-top:18px"></div>
            <h2 class="pv-h2" style="font-size:28px;margin-top:40px">Vus récemment</h2>
            <div id="pvRecent" style="margin-top:18px"></div>
        </div>
    </section>
</main>
<footer class="pv-foot"><div class="pv-wrap"><b>{{ $s['name'] }}</b>Paiement à la livraison, en espèces, au livreur · Boutique sur Shopio</div></footer>

@include('public.shops.models._kit')
<script>
(function () {
    'use strict';
    const S = window.SM, D = S.data, esc = S.esc, $ = id => document.getElementById(id);
    const heart = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 7.9 3.6 4.5 7 4.5c2 0 3.5 1.1 5 3 1.5-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"/></svg>';
    const withPhoto = S.products.filter(p => p.photo);

    /* ── Carrousel 3D en anneau : tourne seul toutes les 3,4 s, s'arrête au survol ── */
    const ringItems = (withPhoto.length >= 3 ? withPhoto : S.products).slice(0, 7);
    const n = ringItems.length, R = Math.round(260 / Math.tan(Math.PI / Math.max(3, n)) * .62) + 60;
    let at = 0, paused = false;
    $('pvRing').innerHTML = ringItems.map((p, i) => '<button type="button" class="pv-item" data-sm-quick="' + p.id + '" style="transform:rotateY(' + (i * 360 / n) + 'deg) translateZ(' + R + 'px)" aria-label="' + esc(p.name) + '">' + (p.photo ? '<img src="' + esc(p.photo) + '" alt="">' : '') + '<span>' + esc(p.name) + '</span></button>').join('');
    function turn(i) {
        at = (i + n) % n;
        $('pvRing').style.transform = 'translateZ(-' + R + 'px) rotateY(' + (-at * 360 / n) + 'deg)';
        $('pvRing').querySelectorAll('.pv-item').forEach((el, k) => el.classList.toggle('is-front', k === at));
        const p = ringItems[at];
        $('pvFront').innerHTML = p ? '<b>' + esc(p.name) + '</b><span>' + esc(S.money(p.price)) + '</span><div style="margin-top:16px"><button type="button" class="pv-btn" data-sm-quick="' + p.id + '">Découvrir la pièce</button></div>' : '';
    }
    if (n) {
        turn(0);
        $('pvPrev').addEventListener('click', () => turn(at - 1));
        $('pvNext').addEventListener('click', () => turn(at + 1));
        $('pvStage').addEventListener('mouseenter', () => { paused = true; });
        $('pvStage').addEventListener('mouseleave', () => { paused = false; });
        $('pvStage').addEventListener('focusin', () => { paused = true; });
        if (!S.reduce && n > 1) setInterval(() => { if (!paused && !document.hidden) turn(at + 1); }, 3400);
    } else { $('pvStage').hidden = true; }

    /* ── Bande défilante : les catégories de la boutique ── */
    if ($('pvTrack')) { const words = D.categories.map(c => c.name); let line = []; while (line.length < 10) line = line.concat(words); $('pvTrack').innerHTML = line.concat(line).map(w => '<span>' + esc(w) + '</span>').join(''); }

    /* ── La collection : onglets de catégories, tri, grille ── */
    let cat = @json($activeCategory['name'] ?? ''), sort = '';
    function renderTabs() {
        const tabs = [['', 'Toutes', S.products.length]].concat(D.categories.map(c => [c.name, c.name, c.count]));
        $('pvTabs').innerHTML = tabs.map(([v, l, c]) => '<button type="button" data-cat="' + esc(v) + '" aria-pressed="' + (cat === v) + '">' + esc(l) + '<small>' + c + '</small></button>').join('');
    }
    $('pvTabs').addEventListener('click', e => { const b = e.target.closest('[data-cat]'); if (b) { cat = b.dataset.cat; renderTabs(); renderGrid(); } });
    $('pvSort').addEventListener('change', e => { sort = e.target.value; renderGrid(); });
    const card = p => '<article class="pv-card"><div class="pv-card-media"><button type="button" class="pv-card-fav" data-sm-fav="' + p.id + '" aria-pressed="' + S.fav(p.id) + '" aria-label="Ajouter aux favoris : ' + esc(p.name) + '">' + heart + '</button>' +
        '<button type="button" class="pv-card-img" data-sm-quick="' + p.id + '" aria-label="Aperçu rapide : ' + esc(p.name) + '">' + (p.photo ? '<img src="' + esc(p.photo) + '" alt="" loading="lazy">' : '<span class="pv-noimg">' + esc(p.name) + '</span>') + '</button>' +
        '<button type="button" class="pv-card-add" data-sm-add="' + p.id + '"' + (S.canBuy(p) ? '' : ' disabled') + '>' + esc(S.buyLabel(p)) + '</button></div>' +
        (p.cat ? '<small>' + esc(p.cat) + '</small>' : '') + '<h3>' + esc(p.name) + '</h3><p>' + esc(S.money(p.price)) + (p.old ? '<s>' + esc(S.money(p.old)) + '</s>' : '') + '</p></article>';
    function renderGrid() {
        let list = S.products.filter(p => !cat || p.cat === cat);
        if (sort === 'asc') list = list.slice().sort((a, b) => a.price - b.price);
        if (sort === 'desc') list = list.slice().sort((a, b) => b.price - a.price);
        if (sort === 'new') list = list.slice().sort((a, b) => (b.new ? 1 : 0) - (a.new ? 1 : 0));
        $('pvCount').textContent = list.length + ' pièce' + (list.length > 1 ? 's' : '');
        $('pvGrid').innerHTML = list.length ? list.map(card).join('') : '<div class="pv-empty" style="grid-column:1/-1">Aucun produit dans cette catégorie pour l’instant.</div>';
    }
    renderTabs(); renderGrid();

    /* ── Pièce du moment (inclinée selon la souris) et médaillon flottant ── */
    const moment = S.products.find(p => p.featured && p.photo) || withPhoto[0] || S.products[0];
    const medal = moment ? (S.products.find(p => p.photo && p.cat !== moment.cat) || withPhoto.find(p => p.id !== moment.id)) : null;
    if (moment) {
        $('pvMoment').innerHTML = '<div class="pv-moment"><div class="pv-tilt"><div class="pv-tilt-in" id="pvTilt">' + (moment.photo ? '<img src="' + esc(moment.photo) + '" alt="' + esc(moment.name) + '">' : '') + '<span class="pv-tag">' + esc(S.money(moment.price)) + '</span></div></div>' +
            '<div><p class="pv-kicker" style="text-align:left">Pièce du moment</p><h2 class="pv-h2" id="pvMomentH">' + esc(moment.name) + '</h2>' + (moment.desc ? '<p class="pv-desc">' + esc(moment.desc) + '</p>' : '<p class="pv-desc">' + esc(moment.cat || D.shop.name) + '</p>') +
            '<div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px"><button type="button" class="pv-btn" data-sm-add="' + moment.id + '"' + (S.canBuy(moment) ? '' : ' disabled') + '>' + esc(S.buyLabel(moment)) + '</button><button type="button" class="pv-btn" style="border-color:var(--m-line)" data-sm-quick="' + moment.id + '">Voir le détail</button></div></div></div>' +
            '<div class="pv-engage">' + (D.shop.verified ? '<div><b>Boutique vérifiée</b><span>Contrôlée par Shopio avant d’ouvrir.</span></div>' : '<div><b>Boutique Shopio</b><span>Votre commande passe par Shopio.</span></div>') +
            '<div><b>Paiement à la livraison</b><span>En espèces, au livreur. Rien à payer avant.</span></div><div><b>Confirmation par téléphone</b><span>La boutique vous appelle avant de livrer.</span></div><div><b>Suivi de commande</b><span>Vous suivez votre commande sur Shopio.</span></div></div>' +
            (medal ? '<div class="pv-medal"><div class="pv-medal-img"><img src="' + esc(medal.photo) + '" alt=""></div><div><p class="pv-kicker" style="text-align:left">' + esc(medal.cat || 'À découvrir') + '</p><h2 class="pv-h2" style="font-size:40px">' + esc(medal.name) + '</h2><p class="pv-desc" style="color:var(--gold-2);font-weight:800;font-size:20px">' + esc(S.money(medal.price)) + '</p><button type="button" class="pv-btn" data-sm-quick="' + medal.id + '">Découvrir</button></div></div>' : '');
        const tilt = $('pvTilt'), wrap = tilt.parentElement;
        if (!S.reduce && window.matchMedia('(hover: hover)').matches) {
            wrap.addEventListener('mousemove', e => { const r = wrap.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5; tilt.style.transform = 'rotateY(' + (x * 16) + 'deg) rotateX(' + (-y * 12) + 'deg)'; });
            wrap.addEventListener('mouseleave', () => { tilt.style.transform = ''; });
        }
    } else { $('piece').hidden = true; }

    /* ── Favoris et vus récemment ── */
    const strip = list => '<div class="pv-strip">' + list.map(p => '<button type="button" data-sm-quick="' + p.id + '"><i' + (p.thumb ? ' style="background-image:url(\'' + esc(p.thumb) + '\')"' : '') + '></i>' + esc(p.name) + '</button>').join('') + '</div>';
    function renderMine() {
        const f = S.favs().map(S.byId).filter(Boolean), r = S.recent();
        $('pvFavs').innerHTML = f.length ? strip(f) : '<p class="pv-sub">Touchez le cœur d’une pièce pour la retrouver ici.</p>';
        $('pvRecent').innerHTML = r.length ? strip(r) : '<p class="pv-sub">Les pièces que vous regardez apparaîtront ici.</p>';
        $('pvGrid').querySelectorAll('[data-sm-fav]').forEach(b => b.setAttribute('aria-pressed', S.fav(b.dataset.smFav)));
    }
    S.subscribe(renderMine); renderMine();
})();
</script>
</body>
</html>
