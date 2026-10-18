{{--
    Modèle de page « Pop » : énergique. Blanc, bleu #1668D8, ambre #F59E0B, Nunito très grasse, très arrondi (28 px),
    animations à rebond. Recherche instantanée, stories (lecteur plein écran), sélection du jour (compte à rebours réel
    jusqu'à minuit, sans remise inventée), tuiles, puces fixées en haut, cartes pastel, pilule panier fixée en bas.
--}}
@php $s = $pm['shop']; @endphp
<!doctype html>
<html lang="fr">
<head>
@include('public.shops.models._head')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
    --blue: #1668D8; --blue-2: #1F7CF6; --amber: #F59E0B; --ink: #16162A;
    --m-bg: #FFFFFF; --m-surface: #FFFFFF; --m-ink: var(--ink); --m-ink2: #55556E; --m-line: #E6E9F2; --m-soft: #F2F5FC;
    --m-accent: var(--blue); --m-accent-ink: #fff; --m-accent-text: var(--blue); --m-radius: 28px; --m-btn-radius: 999px;
    --m-title: 'Nunito', system-ui, sans-serif; --m-body: 'Nunito', system-ui, sans-serif;
    --pop: cubic-bezier(.34,1.56,.64,1); --out: cubic-bezier(.22,1,.36,1);
}
body { font-weight: 700; }
.pp-wrap { max-width: 1240px; margin: 0 auto; padding: 0 max(16px, env(safe-area-inset-left, 0px)); }
.pp-head { position: sticky; top: 0; z-index: 60; background: rgba(255,255,255,.9); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
.pp-head .pp-wrap { display: flex; align-items: center; gap: 14px; min-height: 74px; }
.pp-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; min-width: 0; }
.pp-brand i { width: 46px; height: 46px; border-radius: 16px; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--amber); color: var(--ink); font-style: normal; font-weight: 900; transform: rotate(-6deg); }
.pp-brand i img { width: 100%; height: 100%; object-fit: cover; }
.pp-brand b { font-size: 21px; font-weight: 900; letter-spacing: -.02em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pp-search { flex: 1; max-width: 520px; margin-left: auto; position: relative; }
.pp-search input { width: 100%; min-height: 50px; padding: 0 18px 0 46px; border: 0; border-radius: 999px; background: var(--m-soft); font: 800 15px var(--m-body); color: var(--ink); outline: 0; box-shadow: inset 0 0 0 2px transparent; transition: box-shadow 180ms; }
.pp-search input:focus { box-shadow: inset 0 0 0 2.5px var(--blue); background: #fff; }
.pp-search svg { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--m-ink2); }
.pp-cartbtn { position: relative; width: 52px; height: 52px; border: 0; border-radius: 18px; background: var(--blue); color: #fff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
.pp-cartbtn span { position: absolute; top: -6px; right: -6px; min-width: 24px; height: 24px; padding: 0 6px; border-radius: 999px; background: var(--amber); color: var(--ink); font-size: 12.5px; font-weight: 900; display: flex; align-items: center; justify-content: center; border: 2.5px solid #fff; }
/* Stories */
.pp-stories { display: flex; gap: 16px; overflow-x: auto; padding: 18px 2px 10px; scrollbar-width: none; }
.pp-stories::-webkit-scrollbar { display: none; }
.pp-story { flex-shrink: 0; width: 84px; border: 0; background: none; padding: 0; cursor: pointer; color: var(--ink); font: 800 12.5px var(--m-body); text-align: center; transition: transform 260ms var(--pop); }
.pp-story:hover { transform: translateY(-3px) scale(1.04); }
.pp-story i { display: block; width: 78px; height: 78px; margin: 0 auto 6px; padding: 3px; border-radius: 50%; background: conic-gradient(from 200deg, var(--amber), #EF4444, #A855F7, var(--blue), var(--amber)); }
.pp-story i span { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; border-radius: 50%; border: 3px solid #fff; background: var(--m-soft) center / cover; font-size: 22px; color: var(--blue); overflow: hidden; }
.pp-story em { display: block; font-style: normal; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
/* Lecteur de stories plein écran */
.pp-player { position: fixed; inset: 0; z-index: 960; background: #000; display: flex; align-items: center; justify-content: center; animation: smFade 240ms var(--out); }
.pp-player-in { position: relative; width: min(440px, 100%); height: min(100%, 860px); background: #111 center / cover; overflow: hidden; }
.pp-player-in img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.pp-player-in::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,.45) 0%, transparent 22%, transparent 55%, rgba(0,0,0,.75) 100%); pointer-events: none; }
.pp-bars { position: absolute; top: max(12px, env(safe-area-inset-top, 0px)); left: 12px; right: 12px; z-index: 3; display: flex; gap: 4px; }
.pp-bars i { flex: 1; height: 3.5px; border-radius: 4px; background: rgba(255,255,255,.35); overflow: hidden; }
.pp-bars i b { display: block; height: 100%; width: 0; background: #fff; }
.pp-bars i.is-done b { width: 100%; }
.pp-bars i.is-on b { animation: ppBar 4s linear forwards; }
@keyframes ppBar { to { width: 100%; } }
.pp-ptop { position: absolute; top: calc(max(12px, env(safe-area-inset-top, 0px)) + 14px); left: 14px; right: 14px; z-index: 3; display: flex; align-items: center; justify-content: space-between; color: #fff; font-weight: 900; }
.pp-ptop button { width: 44px; height: 44px; border: 0; border-radius: 50%; background: rgba(255,255,255,.18); color: #fff; font-size: 24px; cursor: pointer; }
.pp-tap { position: absolute; top: 80px; bottom: 150px; z-index: 2; width: 45%; border: 0; background: transparent; cursor: pointer; }
.pp-tap.is-l { left: 0; } .pp-tap.is-r { right: 0; }
.pp-pinfo { position: absolute; left: 16px; right: 16px; bottom: max(18px, env(safe-area-inset-bottom, 0px)); z-index: 3; color: #fff; }
.pp-pinfo h3 { margin: 0 0 4px; font-size: 24px; font-weight: 900; line-height: 1.15; }
.pp-pinfo p { margin: 0 0 12px; font-size: 19px; font-weight: 900; color: #FCD34D; font-variant-numeric: tabular-nums; }
.pp-pinfo div { display: flex; gap: 8px; }
.pp-pinfo .sm-btn { flex: 1; }
.pp-pinfo .sm-btn-ghost { color: #fff; box-shadow: inset 0 0 0 2px rgba(255,255,255,.7); }
/* Sélection du jour */
.pp-day { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr); gap: 26px; align-items: center; margin-top: 16px; padding: 22px; border-radius: 34px; background: linear-gradient(135deg, #1668D8, #1F7CF6 55%, #60A5FA); color: #fff; overflow: hidden; position: relative; }
.pp-day::before { content: ''; position: absolute; width: 380px; height: 380px; right: -120px; top: -160px; border-radius: 50%; background: rgba(255,255,255,.12); }
.pp-day-img { position: relative; aspect-ratio: 1; border-radius: 26px; overflow: hidden; background: rgba(255,255,255,.15); transform: rotate(-3deg); box-shadow: 0 30px 50px -20px rgba(0,0,0,.45); transition: transform 400ms var(--pop); }
.pp-day:hover .pp-day-img { transform: rotate(0) scale(1.02); }
.pp-day-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pp-day-tag { display: inline-flex; padding: 6px 14px; border-radius: 999px; background: var(--amber); color: var(--ink); font-weight: 900; font-size: 13px; letter-spacing: .04em; }
.pp-day h2 { margin: 12px 0 6px; font-size: clamp(28px, 4vw, 44px); font-weight: 900; line-height: 1.05; letter-spacing: -.02em; position: relative; }
.pp-day .pp-price { font-size: 26px; font-weight: 900; font-variant-numeric: tabular-nums; }
.pp-count { display: flex; gap: 8px; margin: 16px 0 18px; }
.pp-count div { min-width: 70px; padding: 10px 6px; border-radius: 18px; background: rgba(255,255,255,.18); text-align: center; }
.pp-count b { display: block; font-size: 28px; font-weight: 900; font-variant-numeric: tabular-nums; line-height: 1.05; }
.pp-count small { font-size: 11.5px; font-weight: 800; opacity: .85; text-transform: uppercase; letter-spacing: .06em; }
.pp-day .sm-btn-main { background: #fff; color: var(--blue); }
/* Tuiles */
.pp-tiles { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-top: 16px; }
.pp-tile { position: relative; min-height: 150px; padding: 20px; border: 0; border-radius: 30px; text-align: left; cursor: pointer; color: var(--ink); font-family: var(--m-body); overflow: hidden; transition: transform 300ms var(--pop); }
.pp-tile:hover { transform: translateY(-4px) rotate(-.6deg); }
.pp-tile b { display: block; font-size: 26px; font-weight: 900; letter-spacing: -.02em; }
.pp-tile span { font-size: 14px; font-weight: 800; opacity: .8; }
.pp-tile i { position: absolute; right: -10px; bottom: -14px; width: 120px; height: 120px; border-radius: 28px; background: center / cover; transform: rotate(12deg); box-shadow: 0 16px 30px -12px rgba(0,0,0,.35); }
/* Puces fixées en haut */
.pp-chips { position: sticky; top: 74px; z-index: 40; background: rgba(255,255,255,.94); backdrop-filter: blur(10px); padding: 12px 0; margin-top: 24px; }
.pp-chiprow { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; }
.pp-chiprow::-webkit-scrollbar { display: none; }
.pp-chip { flex-shrink: 0; min-height: 42px; padding: 0 18px; border: 0; border-radius: 999px; background: var(--m-soft); color: var(--ink); font: 800 14px var(--m-body); cursor: pointer; transition: transform 240ms var(--pop), background-color 180ms; }
.pp-chip[aria-pressed="true"] { background: var(--ink); color: #fff; transform: scale(1.04); }
.pp-chip.is-sort[aria-pressed="true"] { background: var(--amber); color: var(--ink); }
.pp-sep { width: 2px; flex-shrink: 0; margin: 6px 4px; background: var(--m-line); border-radius: 2px; }
/* Cartes pastel */
.pp-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; padding: 10px 0 120px; }
.pp-card { position: relative; border-radius: 28px; padding: 10px 10px 14px; transition: transform 300ms var(--pop); animation: ppIn 500ms var(--pop) backwards; }
.pp-card:hover { transform: translateY(-5px) rotate(-.4deg); }
@keyframes ppIn { from { opacity: 0; transform: translateY(18px) scale(.95); } }
.pp-card-img { display: block; width: 100%; aspect-ratio: 1; border: 0; padding: 0; border-radius: 22px; overflow: hidden; cursor: pointer; background: rgba(255,255,255,.6); }
.pp-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pp-card-img span { display: flex; align-items: center; justify-content: center; height: 100%; padding: 10px; font-size: 18px; font-weight: 900; text-align: center; }
.pp-card h3 { margin: 10px 6px 2px; font-size: 15.5px; font-weight: 900; line-height: 1.25; }
.pp-card p { margin: 0 6px; font-size: 16px; font-weight: 900; color: var(--blue); font-variant-numeric: tabular-nums; }
.pp-card p s { margin-left: 6px; font-size: 13px; color: var(--m-ink2); font-weight: 700; }
.pp-heart { position: absolute; top: 18px; right: 18px; width: 42px; height: 42px; border: 0; border-radius: 50%; background: #fff; color: var(--m-ink2); cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 14px rgba(0,0,0,.12); transition: transform 260ms var(--pop), color 180ms; }
.pp-heart[aria-pressed="true"] { color: #EF4444; transform: scale(1.12); } .pp-heart[aria-pressed="true"] svg { fill: currentColor; }
.pp-heart.is-pop { transform: scale(1.45); }
.pp-plus { position: absolute; right: 16px; bottom: 16px; width: 46px; height: 46px; border: 0; border-radius: 50%; background: var(--ink); color: #fff; font-size: 26px; font-weight: 900; line-height: 1; cursor: pointer; transition: transform 420ms var(--pop), background-color 180ms; }
.pp-plus:hover { background: var(--blue); }
.pp-plus.sm-added { transform: rotate(360deg) scale(1.1); background: #15803D; }
.pp-plus:disabled { background: #C7CBD8; cursor: not-allowed; font-size: 12px; }
.pp-none { grid-column: 1 / -1; padding: 50px 20px; border-radius: 28px; background: var(--m-soft); text-align: center; font-size: 17px; }
.pp-h2 { margin: 28px 0 0; font-size: 26px; font-weight: 900; letter-spacing: -.02em; }
.pp-mini { display: grid; grid-auto-flow: column; grid-auto-columns: 130px; gap: 12px; overflow-x: auto; padding: 12px 0; }
.pp-mini button { border: 0; background: none; padding: 0; text-align: left; cursor: pointer; font: 800 13px var(--m-body); color: var(--ink); }
.pp-mini i { display: block; aspect-ratio: 1; border-radius: 22px; background: var(--m-soft) center / cover; margin-bottom: 6px; }
/* Pilule panier fixée en bas */
.pp-pill { position: fixed; z-index: 70; left: 50%; bottom: max(16px, env(safe-area-inset-bottom, 0px)); transform: translateX(-50%); display: flex; align-items: center; gap: 14px; min-height: 58px; padding: 0 10px 0 22px; border: 0; border-radius: 999px; background: var(--ink); color: #fff; font: 900 15px var(--m-body); cursor: pointer; box-shadow: 0 20px 40px -12px rgba(22,22,42,.55); animation: ppPill 520ms var(--pop); }
@keyframes ppPill { from { transform: translate(-50%, 90px); } }
.pp-pill b { padding: 10px 16px; border-radius: 999px; background: var(--amber); color: var(--ink); font-variant-numeric: tabular-nums; }
.pp-foot { padding: 30px 0 110px; text-align: center; color: var(--m-ink2); font-size: 14px; }
@media (max-width: 900px) {
    .pp-head .pp-wrap { flex-wrap: wrap; padding-bottom: 10px; }
    .pp-search { order: 3; flex-basis: 100%; max-width: none; }
    .pp-cartbtn { margin-left: auto; }
    .pp-chips { top: 134px; }
    .pp-day { grid-template-columns: minmax(0, 1fr); }
    .pp-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .pp-tile { min-height: 120px; } .pp-tile b { font-size: 21px; } .pp-tile i { width: 90px; height: 90px; }
}
</style>
</head>
<body>
@include('public.shops.models._top')
<header class="pp-head">
    <div class="pp-wrap">
        <a class="pp-brand" href="{{ $s['url'] }}"><i>@if($s['logo'])<img src="{{ $s['logo'] }}" alt="">@else{{ $s['initials'] }}@endif</i><b>{{ $s['name'] }}</b></a>
        <label class="pp-search"><span class="sm-sr">Rechercher un produit</span><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM20 20l-4-4"/></svg><input type="search" id="ppQ" placeholder="Rechercher chez {{ $s['name'] }}…" autocomplete="off"></label>
        <button type="button" class="pp-cartbtn" data-sm-cart aria-label="Ouvrir le panier"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 4h2l2.4 11h11L21 7H6.2M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg><span data-sm-count></span></button>
    </div>
</header>
<main class="pp-wrap">
    <nav class="pp-stories" id="ppStories" aria-label="Stories"></nav>
    <section class="pp-day" id="ppDay" aria-labelledby="ppDayH"></section>
    <div class="pp-tiles" id="ppTiles"></div>
    <div class="pp-chips"><div class="pp-chiprow" id="ppChips" role="group" aria-label="Filtrer et trier"></div></div>
    <p class="sm-sr" id="ppLive" aria-live="polite"></p>
    <div class="pp-grid" id="produits"></div>
    <section id="ppMine"></section>
</main>
<footer class="pp-foot">{{ $s['name'] }} · paiement à la livraison · boutique sur Shopio</footer>
<button type="button" class="pp-pill" id="ppPill" data-sm-cart hidden><span><span data-sm-count></span> article(s)</span><b data-sm-total></b></button>

<div class="pp-player" id="ppPlayer" role="dialog" aria-modal="true" aria-label="Story" hidden>
    <div class="pp-player-in" id="ppPlayerIn">
        <div class="pp-bars" id="ppBars"></div>
        <div class="pp-ptop"><span id="ppStoryName"></span><button type="button" id="ppClose" aria-label="Fermer la story">&times;</button></div>
        <button type="button" class="pp-tap is-l" id="ppPrev" aria-label="Produit précédent"></button>
        <button type="button" class="pp-tap is-r" id="ppNext" aria-label="Produit suivant"></button>
        <div class="pp-pinfo" id="ppInfo"></div>
    </div>
</div>

@include('public.shops.models._kit')
<script>
(function () {
    'use strict';
    const S = window.SM, D = S.data, esc = S.esc, $ = id => document.getElementById(id);
    const heart = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 7.9 3.6 4.5 7 4.5c2 0 3.5 1.1 5 3 1.5-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"/></svg>';
    const PASTELS = ['#FFE9D6', '#E0F2FE', '#FCE7F3', '#DCFCE7', '#EDE9FE', '#FEF9C3'];
    const P = S.products;
    // Petit budget : le tiers le moins cher de la boutique (prix réels)
    const sorted = P.map(p => p.price).sort((a, b) => a - b);
    const budget = sorted.length ? sorted[Math.max(0, Math.ceil(sorted.length / 3) - 1)] : 0;
    const round = n => n >= 1e6 ? (Math.round(n / 1e5) / 10).toString().replace('.', ',') + ' M' : n >= 1e3 ? Math.round(n / 1e3) + ' k' : n;

    /* ── Stories : nouveautés, catégories, petit budget, favoris ── */
    function stories() {
        const list = [];
        const fresh = P.filter(p => p.new);
        if (fresh.length) list.push({ key: 'new', name: 'Nouveautés', items: fresh, icon: 'N' });
        D.categories.slice(0, 6).forEach(c => list.push({ key: 'c:' + c.name, name: c.name, items: P.filter(p => p.cat === c.name) }));
        if (P.length > 3) list.push({ key: 'budget', name: '≤ ' + round(budget), items: P.filter(p => p.price <= budget), icon: '≤' });
        const f = S.favs().map(S.byId).filter(Boolean);
        if (f.length) list.push({ key: 'fav', name: 'Mes favoris', items: f, icon: '♥' });
        return list.filter(s => s.items.length);
    }
    function renderStories() {
        const st = stories();
        $('ppStories').innerHTML = st.map((s, i) => { const ph = s.items.find(p => p.thumb); return '<button type="button" class="pp-story" data-story="' + i + '"><i><span' + (ph ? ' style="background-image:url(\'' + esc(ph.thumb) + '\')"' : '') + '>' + (ph ? '' : (s.icon || esc(s.name.slice(0, 1)))) + '</span></i><em>' + esc(s.name) + '</em></button>'; }).join('');
        $('ppStories').hidden = !st.length;
        return st;
    }
    let current = [], storyAt = 0, storyItems = [], timer = null;
    $('ppStories').addEventListener('click', e => { const b = e.target.closest('[data-story]'); if (b) openStory(current[+b.dataset.story]); });
    let storyReturn = null;
    function openStory(s) {
        storyReturn = document.activeElement;
        storyItems = s.items.slice(0, 10); storyAt = 0;
        $('ppStoryName').textContent = s.name;
        $('ppBars').innerHTML = storyItems.map(() => '<i><b></b></i>').join('');
        $('ppPlayer').hidden = false; document.documentElement.style.overflow = 'hidden';
        showStory(0); $('ppClose').focus();
    }
    function showStory(i) {
        if (i >= storyItems.length) return closeStory();
        storyAt = Math.max(0, i);
        const p = storyItems[storyAt];
        $('ppBars').querySelectorAll('i').forEach((b, k) => { b.className = k < storyAt ? 'is-done' : ''; });
        const bar = $('ppBars').children[storyAt]; void bar.offsetWidth; bar.className = 'is-on';
        $('ppPlayerIn').querySelectorAll('img').forEach(x => x.remove());
        if (p.photo) $('ppPlayerIn').insertAdjacentHTML('afterbegin', '<img src="' + esc(p.photo) + '" alt="">');
        $('ppInfo').innerHTML = '<h3>' + esc(p.name) + '</h3><p>' + esc(S.money(p.price)) + '</p><div><button type="button" class="sm-btn sm-btn-main" data-sm-add="' + p.id + '"' + (S.canBuy(p) ? '' : ' disabled') + '>' + esc(S.buyLabel(p)) + '</button><button type="button" class="sm-btn sm-btn-ghost" id="ppDetails">Détails</button></div>';
        $('ppDetails').addEventListener('click', () => { const id = p.id; closeStory(); S.open(id); });
        clearTimeout(timer);
        timer = setTimeout(() => showStory(storyAt + 1), S.reduce ? 8000 : 4000);
    }
    function closeStory() { clearTimeout(timer); $('ppPlayer').hidden = true; document.documentElement.style.overflow = ''; if (storyReturn) storyReturn.focus(); }
    $('ppNext').addEventListener('click', () => showStory(storyAt + 1));
    $('ppPrev').addEventListener('click', () => showStory(storyAt - 1));
    $('ppClose').addEventListener('click', closeStory);
    document.addEventListener('keydown', e => { if ($('ppPlayer').hidden) return; if (e.key === 'Escape') closeStory(); if (e.key === 'ArrowRight') showStory(storyAt + 1); if (e.key === 'ArrowLeft') showStory(storyAt - 1); });

    /* ── La sélection du jour : un produit différent chaque jour, compte à rebours jusqu'à minuit (heure de Conakry) ── */
    const pool = P.filter(p => p.photo).length ? P.filter(p => p.photo) : P;
    if (pool.length) {
        const dayNum = Math.floor(Date.parse(D.today + 'T00:00:00Z') / 864e5);
        const p = pool[dayNum % pool.length];
        $('ppDay').innerHTML = '<div class="pp-day-img">' + (p.photo ? '<img src="' + esc(p.photo) + '" alt="' + esc(p.name) + '">' : '') + '</div><div style="position:relative"><span class="pp-day-tag">La sélection du jour</span><h2 id="ppDayH">' + esc(p.name) + '</h2><div class="pp-price">' + esc(S.money(p.price)) + '</div>' +
            '<div class="pp-count" role="timer" aria-label="Temps avant la prochaine sélection"><div><b id="ppH">00</b><small>heures</small></div><div><b id="ppM">00</b><small>min</small></div><div><b id="ppS">00</b><small>sec</small></div></div>' +
            '<p style="margin:0 0 16px;opacity:.9">Une nouvelle sélection chaque jour à minuit.</p><div style="display:flex;gap:10px;flex-wrap:wrap"><button type="button" class="sm-btn sm-btn-main" data-sm-add="' + p.id + '"' + (S.canBuy(p) ? '' : ' disabled') + '>' + esc(S.buyLabel(p)) + '</button><button type="button" class="sm-btn" style="background:rgba(255,255,255,.2);color:#fff" data-sm-quick="' + p.id + '">Voir</button></div></div>';
        const pad = n => String(n).padStart(2, '0');
        (function tick() {
            const now = new Date(), left = Math.max(0, Math.floor((Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate() + 1) - now.getTime()) / 1000));
            $('ppH').textContent = pad(Math.floor(left / 3600)); $('ppM').textContent = pad(Math.floor(left % 3600 / 60)); $('ppS').textContent = pad(left % 60);
            setTimeout(tick, 1000);
        })();
    } else { $('ppDay').hidden = true; }

    /* ── Tuiles : la catégorie la plus fournie et le petit budget ── */
    const topCat = D.categories.slice().sort((a, b) => b.count - a.count)[0];
    const tiles = [];
    if (topCat) { const ph = P.find(p => p.cat === topCat.name && p.thumb); tiles.push('<button type="button" class="pp-tile" data-tile-cat="' + esc(topCat.name) + '" style="background:#FFE9D6"><b>' + esc(topCat.name) + '</b><span>' + topCat.count + ' produit' + (topCat.count > 1 ? 's' : '') + '</span>' + (ph ? '<i style="background-image:url(\'' + esc(ph.thumb) + '\')"></i>' : '') + '</button>'); }
    if (P.length > 3) { const ph = P.find(p => p.price <= budget && p.thumb); tiles.push('<button type="button" class="pp-tile" data-tile-budget style="background:#DCFCE7"><b>Petit budget</b><span>Jusqu’à ' + esc(S.money(budget)) + '</span>' + (ph ? '<i style="background-image:url(\'' + esc(ph.thumb) + '\')"></i>' : '') + '</button>'); }
    $('ppTiles').innerHTML = tiles.join(''); $('ppTiles').hidden = !tiles.length;

    /* ── Puces fixées (catégories et tri), recherche instantanée, grille ── */
    let cat = @json($activeCategory['name'] ?? ''), sort = '', q = '', onlyBudget = false;
    function renderChips() {
        $('ppChips').innerHTML = [['', 'Tout']].concat(D.categories.map(c => [c.name, c.name])).map(([v, l]) => '<button type="button" class="pp-chip" data-cat="' + esc(v) + '" aria-pressed="' + (!onlyBudget && cat === v) + '">' + esc(l) + '</button>').join('') +
            (P.length > 3 ? '<button type="button" class="pp-chip" data-budget aria-pressed="' + onlyBudget + '">Petit budget</button>' : '') + '<span class="pp-sep" aria-hidden="true"></span>' +
            [['', 'Populaires'], ['asc', 'Prix ↑'], ['desc', 'Prix ↓'], ['new', 'Nouveautés']].map(([v, l]) => '<button type="button" class="pp-chip is-sort" data-sort="' + v + '" aria-pressed="' + (sort === v) + '">' + l + '</button>').join('');
    }
    $('ppChips').addEventListener('click', e => {
        const c = e.target.closest('[data-cat]'), so = e.target.closest('[data-sort]'), b = e.target.closest('[data-budget]');
        if (c) { cat = c.dataset.cat; onlyBudget = false; }
        if (b) { onlyBudget = !onlyBudget; cat = ''; }
        if (so) sort = so.dataset.sort;
        renderChips(); renderGrid();
    });
    $('ppTiles').addEventListener('click', e => {
        const c = e.target.closest('[data-tile-cat]'), b = e.target.closest('[data-tile-budget]');
        if (c) { cat = c.dataset.tileCat; onlyBudget = false; } if (b) { onlyBudget = true; cat = ''; }
        renderChips(); renderGrid(); $('produits').scrollIntoView({ behavior: S.reduce ? 'auto' : 'smooth', block: 'start' });
    });
    $('ppQ').addEventListener('input', e => { q = e.target.value.trim(); renderGrid(); });
    const norm = v => v.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    const card = (p, i) => '<article class="pp-card" style="background:' + PASTELS[i % PASTELS.length] + ';animation-delay:' + Math.min(i, 12) * 35 + 'ms"><button type="button" class="pp-card-img" data-sm-quick="' + p.id + '" aria-label="Aperçu rapide : ' + esc(p.name) + '">' + (p.photo ? '<img src="' + esc(p.photo) + '" alt="" loading="lazy">' : '<span>' + esc(p.name) + '</span>') + '</button>' +
        '<button type="button" class="pp-heart" data-sm-fav="' + p.id + '" aria-pressed="' + S.fav(p.id) + '" aria-label="Ajouter aux favoris : ' + esc(p.name) + '">' + heart + '</button>' +
        '<h3>' + esc(p.name) + '</h3><p>' + esc(S.money(p.price)) + (p.old ? '<s>' + esc(S.money(p.old)) + '</s>' : '') + '</p>' +
        '<button type="button" class="pp-plus" data-sm-add="' + p.id + '" aria-label="' + esc(S.buyLabel(p)) + ' : ' + esc(p.name) + '"' + (S.canBuy(p) ? '' : ' disabled') + '>' + (S.canBuy(p) ? '+' : '×') + '</button></article>';
    function renderGrid() {
        let list = P.filter(p => (!cat || p.cat === cat) && (!onlyBudget || p.price <= budget) && (!q || norm(p.name + ' ' + p.cat).includes(norm(q))));
        if (sort === 'asc') list = list.slice().sort((a, b) => a.price - b.price);
        if (sort === 'desc') list = list.slice().sort((a, b) => b.price - a.price);
        if (sort === 'new') list = list.slice().sort((a, b) => (b.new ? 1 : 0) - (a.new ? 1 : 0));
        $('produits').innerHTML = list.length ? list.map(card).join('') : '<div class="pp-none">' + (q ? 'Rien trouvé pour « ' + esc(q) + ' ». Essayez un autre mot.' : 'Aucun produit dans cette catégorie pour l’instant.') + '</div>';
        $('ppLive').textContent = list.length + ' produit' + (list.length > 1 ? 's' : '');
    }
    function renderMine() {
        const f = S.favs().map(S.byId).filter(Boolean), r = S.recent();
        const mini = l => '<div class="pp-mini">' + l.map(p => '<button type="button" data-sm-quick="' + p.id + '"><i' + (p.thumb ? ' style="background-image:url(\'' + esc(p.thumb) + '\')"' : '') + '></i>' + esc(p.name) + '</button>').join('') + '</div>';
        $('ppMine').innerHTML = (r.length ? '<h2 class="pp-h2">Vus récemment</h2>' + mini(r) : '') + (f.length ? '<h2 class="pp-h2">Mes favoris</h2>' + mini(f) : '');
        $('ppPill').hidden = !S.cartCount();
        $('produits').querySelectorAll('[data-sm-fav]').forEach(b => b.setAttribute('aria-pressed', S.fav(b.dataset.smFav)));
        current = renderStories();
    }
    renderChips(); renderGrid(); S.subscribe(renderMine); renderMine();
})();
</script>
</body>
</html>
