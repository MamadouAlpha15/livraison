{{--
    Modèle de page « Fil » : façon réseaux sociaux. Fond bleu nuit #061226 avec halo bleu, 3 colonnes :
    la boutique à gauche (suivre, catégories, favoris), le fil au centre (un produit par écran vertical,
    défilement qui s'arrête sur chaque produit, zoom lent, double-clic = favori), le panier en direct à droite.
--}}
@php $s = $pm['shop']; @endphp
<!doctype html>
<html lang="fr">
<head>
@include('public.shops.models._head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --night: #061226; --night-2: #0D1F3C; --night-3: #132B52; --blue: #1F7CF6; --sky: #7DB4FF;
    --m-bg: var(--night); --m-surface: #0D1F3C; --m-ink: #EEF3FB; --m-ink2: #A9B8D2; --m-line: #22355A; --m-soft: #16294A;
    --m-accent: var(--blue); --m-accent-ink: #fff; --m-accent-text: #fff; --m-radius: 22px; --m-btn-radius: 999px;
    --m-title: 'Plus Jakarta Sans', system-ui, sans-serif; --m-body: 'Plus Jakarta Sans', system-ui, sans-serif;
    --out: cubic-bezier(.22,1,.36,1); --pop: cubic-bezier(.34,1.56,.64,1);
}
body { min-height: 100vh; background: radial-gradient(60% 50% at 50% 30%, rgba(31,124,246,.28), transparent 70%), radial-gradient(40% 40% at 90% 90%, rgba(125,180,255,.12), transparent 70%), var(--night); }
.fl { max-width: 1320px; margin: 0 auto; padding: 22px max(16px, env(safe-area-inset-left, 0px)) 30px; display: grid; grid-template-columns: 290px minmax(0, 1fr) 320px; gap: 26px; align-items: start; }
.fl-col { position: sticky; top: 22px; display: flex; flex-direction: column; gap: 14px; }
.fl-card { background: rgba(13,31,60,.75); backdrop-filter: blur(10px); border-radius: 24px; box-shadow: inset 0 0 0 1px var(--m-line); padding: 18px; }
.fl-card h2 { margin: 0 0 10px; font-size: 15px; font-weight: 800; }
/* Gauche : la boutique */
.fl-shop { text-align: center; }
.fl-avatar { width: 86px; height: 86px; margin: 0 auto 10px; border-radius: 50%; padding: 3px; background: conic-gradient(var(--blue), var(--sky), var(--blue)); }
.fl-avatar span { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; border-radius: 50%; border: 3px solid var(--night); background: var(--night-3); overflow: hidden; font-weight: 800; font-size: 24px; }
.fl-avatar img { width: 100%; height: 100%; object-fit: cover; }
.fl-shop b { display: block; font-size: 19px; }
.fl-handle { color: var(--m-ink2); font-size: 13.5px; }
.fl-handle em { font-style: normal; color: var(--sky); font-weight: 800; }
.fl-stats { display: flex; justify-content: center; gap: 22px; margin: 14px 0; }
.fl-stats div b { display: block; font-size: 18px; font-variant-numeric: tabular-nums; }
.fl-stats div span { font-size: 12px; color: var(--m-ink2); }
.fl-follow { width: 100%; }
.fl-cats { display: flex; flex-direction: column; gap: 4px; }
.fl-cats button { display: flex; justify-content: space-between; align-items: center; min-height: 42px; padding: 0 12px; border: 0; border-radius: 14px; background: transparent; color: var(--m-ink); font: 600 14px var(--m-body); cursor: pointer; text-align: left; }
.fl-cats button:hover { background: var(--m-soft); }
.fl-cats button[aria-pressed="true"] { background: var(--blue); }
.fl-cats small { color: inherit; opacity: .7; font-variant-numeric: tabular-nums; }
/* Centre : le fil dans un téléphone */
.fl-center { display: flex; flex-direction: column; align-items: center; gap: 14px; }
.fl-phone { position: relative; width: 440px; max-width: 100%; height: 820px; max-height: calc(100vh - 60px); min-height: 560px; border: 10px solid #020812; border-radius: 48px; overflow: hidden; background: #000; box-shadow: 0 50px 100px -40px rgba(0,0,0,.9), 0 0 0 1px var(--m-line); }
.fl-feed { height: 100%; overflow-y: auto; scroll-snap-type: y mandatory; scrollbar-width: none; overscroll-behavior: contain; }
.fl-feed::-webkit-scrollbar { display: none; }
.fl-post { position: relative; height: 100%; scroll-snap-align: start; scroll-snap-stop: always; overflow: hidden; background: var(--night-2); }
.fl-post-img { position: absolute; inset: 0; }
.fl-post-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.fl-post.is-on .fl-post-img img { animation: flZoom 12s ease-out forwards; }
@keyframes flZoom { to { transform: scale(1.12); } }
.fl-post-img .fl-noimg { display: flex; align-items: center; justify-content: center; height: 100%; padding: 30px; font-size: 26px; font-weight: 800; text-align: center; background: linear-gradient(160deg, #132B52, #061226); }
.fl-post::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,.35) 0%, transparent 18%, transparent 52%, rgba(0,0,0,.82) 100%); pointer-events: none; }
.fl-cap { position: absolute; left: 16px; right: 84px; bottom: 22px; z-index: 2; }
.fl-cap h3 { margin: 0 0 4px; font-size: 21px; font-weight: 800; line-height: 1.2; }
.fl-cap p { margin: 0 0 6px; font-size: 18px; font-weight: 800; color: #FCD34D; font-variant-numeric: tabular-nums; }
.fl-tags { margin: 0 0 12px; color: var(--sky); font-size: 13.5px; font-weight: 700; }
.fl-acts { position: absolute; right: 12px; bottom: 26px; z-index: 2; display: flex; flex-direction: column; gap: 14px; }
.fl-act { display: flex; flex-direction: column; align-items: center; gap: 4px; border: 0; background: none; color: #fff; font: 700 11.5px var(--m-body); cursor: pointer; text-decoration: none; }
.fl-act i { width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,.16); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; font-style: normal; transition: transform 260ms var(--pop), background-color 180ms; }
.fl-act:hover i { background: rgba(255,255,255,.28); }
.fl-act[aria-pressed="true"] i { color: #F43F5E; background: #fff; }
.fl-act[aria-pressed="true"] svg { fill: currentColor; }
.fl-act.is-pop i { transform: scale(1.3); }
.fl-big { position: absolute; left: 50%; top: 45%; z-index: 3; width: 130px; height: 130px; margin: -65px 0 0 -65px; color: #F43F5E; pointer-events: none; animation: flHeart 900ms var(--pop) forwards; }
@keyframes flHeart { 0% { transform: scale(.2); opacity: 0; } 30% { transform: scale(1.15); opacity: 1; } 70% { transform: scale(1); opacity: 1; } 100% { transform: scale(1.3) translateY(-30px); opacity: 0; } }
.fl-top { position: absolute; top: 16px; left: 16px; right: 16px; z-index: 4; display: flex; justify-content: space-between; align-items: center; pointer-events: none; }
.fl-ind { padding: 6px 12px; border-radius: 999px; background: rgba(0,0,0,.45); font-weight: 800; font-size: 13px; font-variant-numeric: tabular-nums; }
.fl-nav { display: flex; gap: 8px; }
.fl-nav button { width: 48px; height: 48px; border: 0; border-radius: 50%; background: var(--m-soft); color: var(--m-ink); font-size: 20px; cursor: pointer; box-shadow: inset 0 0 0 1px var(--m-line); }
.fl-nav button:hover { background: var(--blue); }
.fl-hint { color: var(--m-ink2); font-size: 13px; text-align: center; }
/* Droite : panier en direct */
.fl-cart-list { display: flex; flex-direction: column; gap: 10px; }
.fl-cart-line { display: grid; grid-template-columns: 46px minmax(0, 1fr) auto; gap: 10px; align-items: center; font-size: 13.5px; }
.fl-cart-line i { width: 46px; height: 46px; border-radius: 12px; background: var(--m-soft) center / cover; }
.fl-cart-line b { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fl-cart-line small { color: var(--m-ink2); }
.fl-cart-line span { font-weight: 800; font-variant-numeric: tabular-nums; }
.fl-total { display: flex; justify-content: space-between; margin: 12px 0; padding-top: 12px; border-top: 1px solid var(--m-line); font-weight: 800; }
.fl-total b { font-size: 18px; font-variant-numeric: tabular-nums; }
.fl-recent { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
.fl-recent button { aspect-ratio: 1; border: 0; padding: 0; border-radius: 14px; background: var(--m-soft) center / cover; cursor: pointer; }
.fl-cod { background: linear-gradient(160deg, #0F3D24, #0B2A1A); box-shadow: inset 0 0 0 1px #1E5B39; }
.fl-cod h2 { color: #86EFAC; }
.fl-cod p { margin: 0; color: #D1FAE5; font-size: 14px; line-height: 1.55; }
.fl-cod blockquote { margin: 14px 0 0; padding-top: 12px; border-top: 1px solid #1E5B39; font-style: italic; color: #ECFDF5; }
.fl-cod cite { display: block; margin-top: 6px; font-style: normal; font-size: 12.5px; color: #86EFAC; font-weight: 700; }
.fl-empty { color: var(--m-ink2); font-size: 13.5px; margin: 0; }
@media (max-width: 1180px) {
    .fl { grid-template-columns: minmax(0, 1fr) 300px; }
    .fl-left { grid-column: 1 / -1; position: static; flex-direction: row; flex-wrap: wrap; }
    .fl-left .fl-card { flex: 1 1 260px; }
}
@media (max-width: 820px) {
    .fl { grid-template-columns: minmax(0, 1fr); padding-top: 12px; }
    .fl-col { position: static; }
    .fl-phone { height: calc(100vh - 150px); min-height: 520px; border-width: 6px; border-radius: 30px; }
    .fl-nav { display: none; }
    /* Téléphone : carte boutique compacte et catégories en puces, pour que le fil arrive vite */
    .fl-left .fl-card { flex-basis: 100%; padding: 14px; }
    .fl-shop { display: grid; grid-template-columns: 60px minmax(0, 1fr); gap: 2px 12px; align-items: center; text-align: left; }
    .fl-avatar { grid-row: span 2; width: 60px; height: 60px; margin: 0; }
    .fl-avatar span { font-size: 18px; }
    .fl-stats { grid-column: 1 / -1; justify-content: flex-start; margin: 8px 0; }
    .fl-shop form, .fl-shop > a.fl-follow { grid-column: 1 / -1; }
    .fl-left .fl-card h2 { display: none; }
    .fl-cats { flex-direction: row; overflow-x: auto; scrollbar-width: none; gap: 6px; }
    .fl-cats button { flex-shrink: 0; gap: 8px; background: var(--m-soft); }
}
</style>
</head>
<body>
@include('public.shops.models._top')
<div class="fl">
    <aside class="fl-col fl-left" aria-label="La boutique">
        <div class="fl-card fl-shop">
            <div class="fl-avatar"><span>@if($s['logo'])<img src="{{ $s['logo'] }}" alt="">@else{{ $s['initials'] }}@endif</span></div>
            <b>{{ $s['name'] }}</b>
            <span class="fl-handle">{{ '@' . $s['handle'] }}@if($s['verified']) · <em>✓ vérifiée</em>@endif</span>
            <div class="fl-stats">
                <div><b>{{ count($pm['products']) }}</b><span>produits</span></div>
                @if($pm['rating'])<div><b>{{ number_format($pm['rating']['avg'], 1, ',', ' ') }}</b><span>{{ $pm['rating']['count'] }} avis</span></div>@endif
                @if($pm['delivered'])<div><b>{{ $pm['delivered'] }}</b><span>livrées</span></div>@endif
            </div>
            @if($pm['urls']['followIsForm'])
            <form method="POST" action="{{ $pm['urls']['follow'] }}">@csrf<button type="submit" class="sm-btn sm-btn-main fl-follow">Suivre la boutique</button></form>
            @else
            <a class="sm-btn sm-btn-main fl-follow" href="{{ $pm['urls']['follow'] }}">Suivre la boutique</a>
            @endif
        </div>
        <div class="fl-card"><h2>Catégories</h2><div class="fl-cats" id="flCats" role="group" aria-label="Catégories"></div></div>
    </aside>

    <section class="fl-center" aria-label="Le fil des produits">
        <div class="fl-phone">
            <div class="fl-top"><span class="fl-ind" id="flInd" aria-live="polite"></span></div>
            <div class="fl-feed" id="flFeed" tabindex="0" aria-label="Produits, un par écran"></div>
        </div>
        <div class="fl-nav"><button type="button" id="flUp" aria-label="Produit précédent">↑</button><button type="button" id="flDown" aria-label="Produit suivant">↓</button></div>
        <p class="fl-hint">Faites défiler · double-cliquez sur une photo pour l’ajouter aux favoris</p>
    </section>

    <aside class="fl-col" aria-label="Panier et favoris">
        <div class="fl-card"><h2>Panier</h2><div class="fl-cart-list" id="flCart"></div><div class="fl-total"><span>Total</span><b data-sm-total></b></div><button type="button" class="sm-btn sm-btn-main" style="width:100%" data-sm-cart>Voir le panier <span data-sm-count></span></button></div>
        <div class="fl-card"><h2>Vus récemment</h2><div id="flRecent"></div></div>
        <div class="fl-card fl-cod">
            <h2>Paiement à la livraison</h2>
            <p>Vous payez en espèces, au livreur, quand vous recevez votre commande. {{ $s['name'] }} vous appelle d’abord pour confirmer.</p>
            @if(count($pm['reviews']))<blockquote>« {{ $pm['reviews'][0]['text'] }} »<cite>{{ $pm['reviews'][0]['name'] }} · {{ str_repeat('★', max(1, min(5, $pm['reviews'][0]['rating']))) }}</cite></blockquote>@endif
        </div>
    </aside>
</div>

@include('public.shops.models._kit')
<script>
(function () {
    'use strict';
    const S = window.SM, D = S.data, esc = S.esc, $ = id => document.getElementById(id);
    const heart = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 7.9 3.6 4.5 7 4.5c2 0 3.5 1.1 5 3 1.5-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"/></svg>';
    const ico = d => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>';
    const tags = p => (p.cat ? p.cat.split(/[\s&,]+/).filter(w => w.length > 2).slice(0, 3).map(w => '#' + w.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()).join(' ') : '');
    let cat = @json($activeCategory['name'] ?? ''), favOnly = false, list = [], at = 0;

    function renderCats() {
        const rows = [['', 'Tout le fil', S.products.length]].concat(D.categories.map(c => [c.name, c.name, c.count]));
        $('flCats').innerHTML = rows.map(([v, l, n]) => '<button type="button" data-cat="' + esc(v) + '" aria-pressed="' + (!favOnly && cat === v) + '"><span>' + esc(l) + '</span><small>' + n + '</small></button>').join('') +
            '<button type="button" data-favs aria-pressed="' + favOnly + '"><span>Mes favoris</span><small>' + S.favs().length + '</small></button>';
    }
    $('flCats').addEventListener('click', e => {
        const c = e.target.closest('[data-cat]'), f = e.target.closest('[data-favs]');
        if (c) { cat = c.dataset.cat; favOnly = false; } if (f) { favOnly = true; cat = ''; }
        renderCats(); renderFeed();
    });
    function post(p, i) {
        return '<article class="fl-post" data-i="' + i + '" data-id="' + p.id + '" aria-label="' + esc(p.name) + '"><div class="fl-post-img">' + (p.photo ? '<img src="' + esc(p.photo) + '" alt="" loading="' + (i < 2 ? 'eager' : 'lazy') + '">' : '<span class="fl-noimg">' + esc(p.name) + '</span>') + '</div>' +
            '<div class="fl-cap"><h3>' + esc(p.name) + '</h3><p>' + esc(S.money(p.price)) + '</p><div class="fl-tags">' + esc(tags(p)) + '</div><button type="button" class="sm-btn sm-btn-main" data-sm-add="' + p.id + '"' + (S.canBuy(p) ? '' : ' disabled') + '>' + (S.canBuy(p) ? (p.variants ? 'Choisir les options' : 'Commander') : esc(S.buyLabel(p))) + '</button></div>' +
            '<div class="fl-acts"><button type="button" class="fl-act" data-sm-fav="' + p.id + '" aria-pressed="' + S.fav(p.id) + '" aria-label="Ajouter aux favoris : ' + esc(p.name) + '"><i>' + heart + '</i>Favori</button>' +
            (D.settings.nego ? '<a class="fl-act" href="' + esc(p.nego) + '"><i>' + ico('M4 5h16v11H9l-5 4zM8 9h8M8 12h5') + '</i>Négocier</a>' : '') +
            '<button type="button" class="fl-act" data-share="' + p.id + '"><i>' + ico('M18 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6M6 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6M18 22a3 3 0 1 0 0-6 3 3 0 0 0 0 6M8.6 13.5l6.8 4M15.4 6.5l-6.8 4') + '</i>Partager</button>' +
            '<button type="button" class="fl-act" data-sm-quick="' + p.id + '"><i>' + ico('M12 8v.01M11 12h1v5h1M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20') + '</i>Détails</button></div></article>';
    }
    let io = null;
    function renderFeed() {
        list = S.products.filter(p => (!cat || p.cat === cat) && (!favOnly || S.fav(p.id)));
        $('flFeed').innerHTML = list.length ? list.map(post).join('') : '<article class="fl-post"><div class="fl-post-img"><span class="fl-noimg" style="font-size:18px">' + (favOnly ? 'Aucun favori pour l’instant : double-cliquez sur une photo.' : 'Aucun produit dans cette catégorie pour l’instant.') + '</span></div></article>';
        $('flFeed').scrollTop = 0; at = 0; ind();
        if (io) io.disconnect();
        io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting && e.intersectionRatio > .6) { $('flFeed').querySelectorAll('.fl-post.is-on').forEach(x => x.classList.remove('is-on')); e.target.classList.add('is-on'); at = +e.target.dataset.i || 0; ind(); } }), { root: $('flFeed'), threshold: [.6] });
        $('flFeed').querySelectorAll('.fl-post').forEach(el => io.observe(el));
    }
    const ind = () => { $('flInd').textContent = list.length ? (at + 1) + ' / ' + list.length : '0 / 0'; };
    const go = i => { const el = $('flFeed').children[Math.max(0, Math.min(list.length - 1, i))]; if (el) el.scrollIntoView({ behavior: S.reduce ? 'auto' : 'smooth', block: 'start' }); };
    $('flUp').addEventListener('click', () => go(at - 1));
    $('flDown').addEventListener('click', () => go(at + 1));
    $('flFeed').addEventListener('keydown', e => { if (e.key === 'ArrowDown' || e.key === 'PageDown') { e.preventDefault(); go(at + 1); } if (e.key === 'ArrowUp' || e.key === 'PageUp') { e.preventDefault(); go(at - 1); } });
    /* Double-clic sur la photo = favori, avec un grand cœur animé */
    $('flFeed').addEventListener('dblclick', e => {
        const postEl = e.target.closest('.fl-post[data-id]');
        if (!postEl || e.target.closest('button, a')) return;
        const id = postEl.dataset.id;
        if (!S.fav(id)) S.toggleFav(id);
        const h = document.createElement('div'); h.className = 'fl-big'; h.innerHTML = '<svg viewBox="0 0 24 24" width="130" height="130" fill="currentColor" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 7.9 3.6 4.5 7 4.5c2 0 3.5 1.1 5 3 1.5-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"/></svg>';
        postEl.appendChild(h); setTimeout(() => h.remove(), 950);
        S.toast('Ajouté à vos favoris');
    });
    /* Partager : copie le lien de la fiche */
    $('flFeed').addEventListener('click', async e => {
        const b = e.target.closest('[data-share]');
        if (!b) return;
        const p = S.byId(b.dataset.share);
        try { await navigator.clipboard.writeText(p.url); S.toast('Lien copié'); } catch (x) { window.prompt('Copiez ce lien :', p.url); }
    });
    function side() {
        const lines = S.cartLines();
        $('flCart').innerHTML = lines.length ? lines.map(l => '<div class="fl-cart-line"><i' + (l.p.thumb ? ' style="background-image:url(\'' + esc(l.p.thumb) + '\')"' : '') + '></i><div style="min-width:0"><b>' + esc(l.p.name) + '</b><small>× ' + l.qty + '</small></div><span>' + esc(S.money(l.p.price * l.qty)) + '</span></div>').join('') : '<p class="fl-empty">Votre panier est vide.</p>';
        const r = S.recent();
        $('flRecent').innerHTML = r.length ? '<div class="fl-recent">' + r.map(p => '<button type="button" data-sm-quick="' + p.id + '" aria-label="' + esc(p.name) + '"' + (p.thumb ? ' style="background-image:url(\'' + esc(p.thumb) + '\')"' : '') + '></button>').join('') + '</div>' : '<p class="fl-empty">Les produits que vous regardez apparaîtront ici.</p>';
        $('flFeed').querySelectorAll('[data-sm-fav]').forEach(b => b.setAttribute('aria-pressed', S.fav(b.dataset.smFav)));
        const favCount = $('flCats').querySelector('[data-favs] small'); if (favCount) favCount.textContent = S.favs().length;
        if (favOnly && list.length !== S.products.filter(p => S.fav(p.id)).length) renderFeed();
    }
    renderCats(); renderFeed(); S.subscribe(side); side();
})();
</script>
</body>
</html>
