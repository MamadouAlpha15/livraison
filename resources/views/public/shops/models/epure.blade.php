{{--
    Modèle de page « Épuré » : minimal. Blanc, encre #111118, bleu seulement sur les actions, Plus Jakarta Sans,
    beaucoup d'espace. Titre géant qui monte ligne par ligne, photo dévoilée de bas en haut, barre d'outils fixe
    (catégories avec compteur, favoris, résultats, grille 2/3/4, tri), photos 4:5 à zoom lent, « Récemment vus »,
    « Comment ça marche » en 3 étapes.
--}}
@php
    $s = $pm['shop'];
    $title = $s['slogan'] ?: $s['name'];
    $words = preg_split('/\s+/u', trim($title));
    $lines = []; $cur = '';
    foreach ($words as $w) { if ($cur !== '' && mb_strlen($cur . ' ' . $w) > 18) { $lines[] = $cur; $cur = $w; } else { $cur = trim($cur . ' ' . $w); } }
    if ($cur !== '') $lines[] = $cur;
@endphp
<!doctype html>
<html lang="fr">
<head>
@include('public.shops.models._head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --ink: #111118; --grey: #F3F3F5; --blue: #1668D8;
    --m-bg: #FFFFFF; --m-surface: #FFFFFF; --m-ink: var(--ink); --m-ink2: #5B5B6B; --m-line: #E7E7EC; --m-soft: #F3F3F5;
    --m-accent: var(--blue); --m-accent-ink: #fff; --m-radius: 14px; --m-btn-radius: 999px;
    --m-title: 'Plus Jakarta Sans', system-ui, sans-serif; --m-body: 'Plus Jakarta Sans', system-ui, sans-serif;
    --out: cubic-bezier(.22,1,.36,1);
}
.ep-wrap { max-width: 1320px; margin: 0 auto; padding: 0 max(20px, env(safe-area-inset-left, 0px)); }
.ep-head .ep-wrap { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-height: 76px; }
.ep-brand { display: flex; align-items: center; gap: 12px; color: var(--ink); text-decoration: none; font-weight: 800; font-size: 17px; letter-spacing: -.01em; min-width: 0; }
.ep-brand i { width: 36px; height: 36px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: var(--grey); display: flex; align-items: center; justify-content: center; font-style: normal; font-size: 13px; }
.ep-brand i img { width: 100%; height: 100%; object-fit: cover; }
.ep-brand span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ep-cart { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 16px; border: 0; border-radius: 999px; background: none; color: var(--ink); font: 700 14px var(--m-body); cursor: pointer; box-shadow: inset 0 0 0 1px var(--m-line); }
.ep-cart span { min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px; background: var(--blue); color: #fff; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; }
/* Titre géant qui monte ligne par ligne, photo dévoilée de bas en haut */
.ep-hero { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 60px; align-items: end; padding: 70px 0 90px; }
.ep-hero h1 { margin: 0; font-size: clamp(44px, 7.4vw, 104px); font-weight: 800; line-height: .98; letter-spacing: -.045em; }
.ep-line { display: block; overflow: hidden; padding-bottom: .04em; }
.ep-line span { display: block; animation: epUp 1s var(--out) backwards; }
@keyframes epUp { from { transform: translateY(105%); } }
.ep-hero p { margin: 30px 0 0; max-width: 42ch; color: var(--m-ink2); font-size: 17px; line-height: 1.6; }
.ep-hero-img { position: relative; aspect-ratio: 4 / 5; background: var(--grey); overflow: hidden; border-radius: 4px; animation: epReveal 1.4s .3s var(--out) backwards; }
@keyframes epReveal { from { clip-path: inset(100% 0 0 0); } to { clip-path: inset(0 0 0 0); } }
.ep-hero-img img { width: 100%; height: 100%; object-fit: cover; display: block; animation: epZoomIn 2s .3s var(--out) backwards; }
@keyframes epZoomIn { from { transform: scale(1.15); } }
.ep-hero-img button { position: absolute; left: 16px; bottom: 16px; }
.ep-link { border: 0; background: #fff; color: var(--ink); min-height: 44px; padding: 0 18px; border-radius: 999px; font: 700 14px var(--m-body); cursor: pointer; }
/* Barre d'outils fixe */
.ep-bar { position: sticky; top: 0; z-index: 40; background: rgba(255,255,255,.93); backdrop-filter: blur(10px); border-block: 1px solid var(--m-line); }
.ep-bar .ep-wrap { display: flex; align-items: center; gap: 18px; min-height: 62px; }
.ep-cats { display: flex; gap: 22px; overflow-x: auto; scrollbar-width: none; flex: 1; }
.ep-cats::-webkit-scrollbar { display: none; }
.ep-cats button { flex-shrink: 0; border: 0; background: none; padding: 8px 0; color: var(--m-ink2); font: 600 14.5px var(--m-body); cursor: pointer; white-space: nowrap; border-bottom: 1.5px solid transparent; }
.ep-cats button sup { font-size: 10.5px; margin-left: 2px; font-variant-numeric: tabular-nums; }
.ep-cats button[aria-pressed="true"] { color: var(--ink); border-color: var(--ink); }
.ep-tools { display: flex; align-items: center; gap: 14px; flex-shrink: 0; }
.ep-count { color: var(--m-ink2); font-size: 13.5px; font-variant-numeric: tabular-nums; white-space: nowrap; }
.ep-favf { display: inline-flex; align-items: center; gap: 6px; border: 0; background: none; color: var(--m-ink2); font: 600 14px var(--m-body); cursor: pointer; min-height: 40px; }
.ep-favf[aria-pressed="true"] { color: var(--blue); }
.ep-favf[aria-pressed="true"] svg { fill: currentColor; }
.ep-cols { display: flex; gap: 2px; padding: 3px; border-radius: 999px; background: var(--grey); }
.ep-cols button { width: 38px; height: 34px; border: 0; border-radius: 999px; background: none; color: var(--m-ink2); font: 700 13px var(--m-body); cursor: pointer; }
.ep-cols button[aria-pressed="true"] { background: #fff; color: var(--ink); box-shadow: 0 1px 4px rgba(0,0,0,.1); }
.ep-sort { min-height: 40px; border: 0; background: none; font: 600 14px var(--m-body); color: var(--ink); cursor: pointer; }
/* Grille : photos 4:5 sur gris clair, zoom lent, barre « Aperçu rapide | Ajouter » */
.ep-grid { --cols: 3; display: grid; grid-template-columns: repeat(var(--cols), minmax(0, 1fr)); gap: 56px 26px; padding: 50px 0 90px; }
.ep-card-media { position: relative; aspect-ratio: 4 / 5; background: var(--grey); overflow: hidden; }
.ep-card-media > button:first-child { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; padding: 0; background: none; cursor: pointer; }
.ep-card-media img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 2.4s var(--out); }
.ep-card:hover .ep-card-media img { transform: scale(1.07); }
.ep-card-media .ep-noimg { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 20px; font-size: 18px; font-weight: 700; color: var(--m-ink2); text-align: center; }
.ep-qbar { position: absolute; left: 0; right: 0; bottom: 0; display: grid; grid-template-columns: 1fr 1fr; background: rgba(255,255,255,.96); transform: translateY(101%); transition: transform 420ms var(--out); }
.ep-card:hover .ep-qbar, .ep-card:focus-within .ep-qbar { transform: none; }
.ep-qbar button { min-height: 50px; border: 0; background: none; font: 700 13.5px var(--m-body); color: var(--ink); cursor: pointer; }
.ep-qbar button + button { border-left: 1px solid var(--m-line); color: var(--blue); }
.ep-qbar button:disabled { color: var(--m-ink2); cursor: not-allowed; }
.ep-fav { position: absolute; top: 12px; right: 12px; z-index: 2; width: 38px; height: 38px; border: 0; border-radius: 50%; background: rgba(255,255,255,.85); color: var(--ink); cursor: pointer; display: flex; align-items: center; justify-content: center; }
.ep-fav[aria-pressed="true"] svg { fill: currentColor; }
.ep-card-txt { display: flex; justify-content: space-between; gap: 14px; margin-top: 14px; font-size: 15px; }
.ep-card-txt span { font-weight: 600; }
.ep-card-txt b { font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
.ep-card-txt s { display: block; font-weight: 500; color: var(--m-ink2); font-size: 13px; text-align: right; }
.ep-card small { color: var(--m-ink2); font-size: 12.5px; }
.ep-none { grid-column: 1 / -1; padding: 80px 20px; text-align: center; color: var(--m-ink2); font-size: 16px; }
/* Récemment vus, comment ça marche */
.ep-sec { padding: 70px 0; border-top: 1px solid var(--m-line); }
.ep-h2 { margin: 0 0 26px; font-size: clamp(26px, 3.4vw, 40px); font-weight: 800; letter-spacing: -.035em; }
.ep-recent { display: grid; grid-auto-flow: column; grid-auto-columns: 180px; gap: 18px; overflow-x: auto; padding-bottom: 6px; }
.ep-recent button { border: 0; background: none; padding: 0; text-align: left; cursor: pointer; font: 600 14px var(--m-body); color: var(--ink); }
.ep-recent i { display: block; aspect-ratio: 4 / 5; background: var(--grey) center / cover; margin-bottom: 10px; }
.ep-steps { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 40px; counter-reset: st; }
.ep-step { padding-top: 22px; border-top: 1.5px solid var(--ink); }
.ep-step::before { counter-increment: st; content: '0' counter(st); display: block; font-size: 44px; font-weight: 800; letter-spacing: -.04em; margin-bottom: 14px; font-variant-numeric: tabular-nums; }
.ep-step b { display: block; font-size: 19px; margin-bottom: 6px; }
.ep-step span { color: var(--m-ink2); line-height: 1.6; }
.ep-foot { padding: 40px 0 calc(40px + env(safe-area-inset-bottom, 0px)); border-top: 1px solid var(--m-line); color: var(--m-ink2); font-size: 14px; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
@media (max-width: 900px) {
    .ep-hero { grid-template-columns: minmax(0, 1fr); gap: 34px; padding: 40px 0 50px; }
    .ep-bar .ep-wrap { flex-wrap: wrap; padding-block: 8px; min-height: 0; }
    .ep-cats { flex-basis: 100%; }
    .ep-tools { width: 100%; justify-content: space-between; }
    .ep-cols { display: none; }
    .ep-grid { --cols: 2 !important; gap: 34px 12px; }
    .ep-qbar { transform: none; position: static; grid-template-columns: 1fr 1fr; background: var(--grey); }
    .ep-steps { grid-template-columns: minmax(0, 1fr); gap: 26px; }
}
</style>
</head>
<body>
@include('public.shops.models._top')
<header class="ep-head">
    <div class="ep-wrap">
        <a class="ep-brand" href="{{ $s['url'] }}"><i>@if($s['logo'])<img src="{{ $s['logo'] }}" alt="">@else{{ $s['initials'] }}@endif</i><span>{{ $s['name'] }}</span></a>
        <button type="button" class="ep-cart" data-sm-cart aria-label="Ouvrir le panier">Panier <span data-sm-count></span></button>
    </div>
</header>
<main>
    <section class="ep-wrap ep-hero" aria-labelledby="epTitle">
        <div>
            <h1 id="epTitle">@foreach($lines as $i => $l)<span class="ep-line"><span style="animation-delay: {{ $i * 130 }}ms">{{ $l }}</span></span>@endforeach</h1>
            <p>{{ $s['name'] }}{{ $s['address'] !== '' ? ' · ' . $s['address'] : '' }}. Paiement à la livraison, en espèces, au livreur.</p>
        </div>
        <div class="ep-hero-img" id="epHeroImg"></div>
    </section>

    <div class="ep-bar">
        <div class="ep-wrap">
            <div class="ep-cats" id="epCats" role="group" aria-label="Catégories"></div>
            <div class="ep-tools">
                <button type="button" class="ep-favf" id="epFavF" aria-pressed="false"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 7.9 3.6 4.5 7 4.5c2 0 3.5 1.1 5 3 1.5-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"/></svg>Favoris</button>
                <span class="ep-count" id="epCount" aria-live="polite"></span>
                <div class="ep-cols" role="group" aria-label="Colonnes"><button type="button" data-cols="2" aria-pressed="false">2</button><button type="button" data-cols="3" aria-pressed="true">3</button><button type="button" data-cols="4" aria-pressed="false">4</button></div>
                <label><span class="sm-sr">Trier</span><select class="ep-sort" id="epSort"><option value="">Sélection</option><option value="asc">Prix croissant</option><option value="desc">Prix décroissant</option><option value="new">Nouveautés</option></select></label>
            </div>
        </div>
    </div>
    <div class="ep-wrap"><div class="ep-grid" id="produits"></div></div>

    <section class="ep-sec" id="epRecentSec" aria-labelledby="epRecentH" hidden><div class="ep-wrap"><h2 class="ep-h2" id="epRecentH">Récemment vus</h2><div class="ep-recent" id="epRecent"></div></div></section>
    <section class="ep-sec" aria-labelledby="epHowH">
        <div class="ep-wrap">
            <h2 class="ep-h2" id="epHowH">Comment ça marche</h2>
            <div class="ep-steps">
                <div class="ep-step"><b>Choisissez</b><span>Ajoutez vos produits au panier. Vous pouvez aussi négocier le prix avec la boutique.</span></div>
                <div class="ep-step"><b>Commandez</b><span>Votre nom, votre téléphone et votre commune suffisent. {{ $s['name'] }} vous appelle pour confirmer.</span></div>
                <div class="ep-step"><b>Payez à la livraison</b><span>En espèces, au livreur, quand vous recevez la commande. Rien à payer avant.</span></div>
            </div>
        </div>
    </section>
</main>
<footer class="ep-wrap ep-foot"><span>{{ $s['name'] }}</span><span>Boutique sur Shopio · paiement à la livraison</span></footer>

@include('public.shops.models._kit')
<script>
(function () {
    'use strict';
    const S = window.SM, D = S.data, esc = S.esc, $ = id => document.getElementById(id);
    const heart = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 7.9 3.6 4.5 7 4.5c2 0 3.5 1.1 5 3 1.5-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"/></svg>';
    const P = S.products;
    const hero = P.find(p => p.featured && p.photo) || P.find(p => p.photo);
    if (hero) $('epHeroImg').innerHTML = '<img src="' + esc(hero.photo) + '" alt="' + esc(hero.name) + '"><button type="button" class="ep-link" data-sm-quick="' + hero.id + '">' + esc(hero.name) + ' →</button>';
    else $('epHeroImg').hidden = true;

    let cat = @json($activeCategory['name'] ?? ''), sort = '', favOnly = false;
    function renderCats() {
        $('epCats').innerHTML = [['', 'Tout', P.length]].concat(D.categories.map(c => [c.name, c.name, c.count])).map(([v, l, n]) => '<button type="button" data-cat="' + esc(v) + '" aria-pressed="' + (cat === v) + '">' + esc(l) + '<sup>' + n + '</sup></button>').join('');
    }
    $('epCats').addEventListener('click', e => { const b = e.target.closest('[data-cat]'); if (b) { cat = b.dataset.cat; renderCats(); render(); } });
    $('epFavF').addEventListener('click', () => { favOnly = !favOnly; $('epFavF').setAttribute('aria-pressed', favOnly); render(); });
    $('epSort').addEventListener('change', e => { sort = e.target.value; render(); });
    document.querySelectorAll('[data-cols]').forEach(b => b.addEventListener('click', () => {
        document.querySelectorAll('[data-cols]').forEach(x => x.setAttribute('aria-pressed', x === b));
        $('produits').style.setProperty('--cols', b.dataset.cols);
        try { localStorage.setItem('shopio_pm_cols', b.dataset.cols); } catch (e) {}
    }));
    try { const c = localStorage.getItem('shopio_pm_cols'); if (c) { const b = document.querySelector('[data-cols="' + c + '"]'); if (b) b.click(); } } catch (e) {}

    const card = p => '<article class="ep-card"><div class="ep-card-media"><button type="button" data-sm-quick="' + p.id + '" aria-label="Aperçu rapide : ' + esc(p.name) + '">' + (p.photo ? '<img src="' + esc(p.photo) + '" alt="" loading="lazy">' : '<span class="ep-noimg">' + esc(p.name) + '</span>') + '</button>' +
        '<button type="button" class="ep-fav" data-sm-fav="' + p.id + '" aria-pressed="' + S.fav(p.id) + '" aria-label="Ajouter aux favoris : ' + esc(p.name) + '">' + heart + '</button>' +
        '<div class="ep-qbar"><button type="button" data-sm-quick="' + p.id + '">Aperçu rapide</button><button type="button" data-sm-add="' + p.id + '"' + (S.canBuy(p) ? '' : ' disabled') + '>' + (S.canBuy(p) ? (p.variants ? 'Options' : 'Ajouter') : esc(S.buyLabel(p))) + '</button></div></div>' +
        '<div class="ep-card-txt"><span>' + esc(p.name) + '</span><b>' + esc(S.money(p.price)) + (p.old ? '<s>' + esc(S.money(p.old)) + '</s>' : '') + '</b></div>' + (p.cat ? '<small>' + esc(p.cat) + '</small>' : '') + '</article>';
    function render() {
        let list = P.filter(p => (!cat || p.cat === cat) && (!favOnly || S.fav(p.id)));
        if (sort === 'asc') list = list.slice().sort((a, b) => a.price - b.price);
        if (sort === 'desc') list = list.slice().sort((a, b) => b.price - a.price);
        if (sort === 'new') list = list.slice().sort((a, b) => (b.new ? 1 : 0) - (a.new ? 1 : 0));
        $('epCount').textContent = list.length + ' résultat' + (list.length > 1 ? 's' : '');
        $('produits').innerHTML = list.length ? list.map(card).join('') : '<p class="ep-none">' + (favOnly ? 'Aucun favori pour l’instant : touchez le cœur d’un produit.' : 'Aucun produit dans cette catégorie pour l’instant.') + '</p>';
    }
    function renderRecent() {
        const r = S.recent();
        $('epRecentSec').hidden = !r.length;
        $('epRecent').innerHTML = r.map(p => '<button type="button" data-sm-quick="' + p.id + '"><i' + (p.thumb ? ' style="background-image:url(\'' + esc(p.photo || p.thumb) + '\')"' : '') + '></i>' + esc(p.name) + '</button>').join('');
        if (favOnly) render(); else $('produits').querySelectorAll('[data-sm-fav]').forEach(b => b.setAttribute('aria-pressed', S.fav(b.dataset.smFav)));
    }
    renderCats(); render(); S.subscribe(renderRecent); renderRecent();
})();
</script>
</body>
</html>
