{{--
    resources/views/boutique/live/index.blade.php
    Route     : GET /boutique/en-direct → Boutique\LiveController@index → name('boutique.live')
                Données mises à jour toutes les 10 s : GET /boutique/en-direct/donnees → name('boutique.live.data')
    Rôle      : vue en direct de la boutique. Chiffres réels (App\Support\LiveStats) : visiteurs en direct, ventes,
                visiteurs et commandes du jour, comportement des clients, fil « En ce moment », carte de l'Afrique
                (pays des visiteurs déduit de l'IP sans l'enregistrer ; commandes du jour dans le pays de la boutique).
    Variables : $shop, $stats (LiveStats::for), $maps (['africa' => …, 'world' => …], App\Support\LiveMaps)
    Cartes    : onglets Afrique / Monde (choix gardé dans le navigateur) ; dessin dans boutique.live._map
--}}
@extends('layouts.app')

@section('title', 'En direct · ' . $shop->name)

@php
    use App\Support\IconLibrary as Ico;
    $bodyClass = 'is-dashboard';
    $icons = collect(['browse' => 'eye', 'product' => 'tag', 'cart' => 'cart', 'form' => 'edit', 'order' => 'bag'])
        ->map(fn ($n) => Ico::svg($n, '', 16));
    $shopIso = strtoupper((string) $shop->country);
    // Repères au centre de chaque pays (le plus grand tracé quand il y en a plusieurs : Somalie + Somaliland)
    $centers = collect($maps)->map(fn ($m) => collect($m['countries'])->sortByDesc('area')->unique('iso')->keyBy('iso')->map(fn ($c) => $c['c']));
@endphp

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.num { font-variant-numeric: tabular-nums; }
.lv-top { position: sticky; top: 0; z-index: 30; display: flex; align-items: center; gap: 12px; padding: 14px 28px !important; }
.lv { padding: 22px 28px 40px; display: grid; grid-template-columns: minmax(300px, 380px) minmax(0, 1fr); gap: 18px; align-items: start; max-width: 1600px; width: 100%; }
.lv-head { grid-column: 1 / -1; display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.lv-head h1 { margin: 0; font-size: 34px; font-weight: 800; letter-spacing: -.03em; display: flex; align-items: center; gap: 12px; }
.lv-dot { width: 10px; height: 10px; border-radius: 50%; background: #22C55E; box-shadow: 0 0 0 0 rgba(34,197,94,.6); animation: lvPulse 2s infinite; flex-shrink: 0; }
.lv-dot.is-off { background: #F59E0B; animation: none; }
@keyframes lvPulse { 70% { box-shadow: 0 0 0 8px rgba(34,197,94,0); } 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); } }
.lv-ago { margin-top: 4px; font-size: 14px; color: var(--muted); }
.lv-col { display: flex; flex-direction: column; gap: 14px; min-width: 0; }
.card { background: var(--surface); border-radius: 20px; box-shadow: 0 0 0 1px var(--border); min-width: 0; }
.lv-kpis { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.lv-kpi { padding: 18px 20px; display: flex; flex-direction: column; gap: 14px; min-height: 118px; }
.lv-kpi small { display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 600; color: var(--text-2); }
.lv-kpi small i { width: 8px; height: 8px; border-radius: 50%; background: #22C55E; }
.lv-kpi b { font-size: 26px; font-weight: 700; letter-spacing: -.02em; line-height: 1.1; transition: color 300ms; }
.lv-kpi b.is-bump { color: var(--sv-btn); }
.lv-card { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
.lv-card h2 { margin: 0; font-size: 17px; font-weight: 800; letter-spacing: -.01em; }
.lv-card h2 span { font-weight: 500; color: var(--text-2); }
.lv-row { display: grid; grid-template-columns: 1fr auto; gap: 8px 12px; align-items: center; font-size: 14px; }
.lv-row b { font-weight: 700; }
.lv-bar { grid-column: 1 / -1; height: 6px; border-radius: 999px; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); overflow: hidden; }
.lv-bar i { display: block; height: 100%; width: 0; border-radius: inherit; background: var(--sv-blue); transition: width 600ms var(--sv-e-out); }
.lv-bar.is-green i { background: #22C55E; } .lv-bar.is-amber i { background: #F59E0B; }
.lv-events { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
.lv-ev { display: flex; align-items: center; gap: 12px; padding: 8px; margin: 0 -8px; border-radius: 12px; animation: lvIn 400ms var(--sv-e-out) backwards; }
.lv-ev:hover { background: var(--bg); }
.lv-ev-ico { width: 34px; height: 34px; border-radius: 11px; display: grid; place-items: center; flex-shrink: 0; background: var(--sv-b50); color: var(--sv-btn); }
.lv-ev-ico.is-form { background: rgba(245,158,11,.14); color: #B45309; } .lv-ev-ico.is-cart { background: rgba(34,197,94,.14); color: #15803D; }
.lv-ev-ico.is-order { background: #0B1E3D; color: #7DB4FF; }
.lv-ev div { min-width: 0; }
.lv-ev b { display: block; font-size: 13.5px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lv-ev small { font-size: 12px; color: var(--muted); }
.lv-empty { margin: 0; font-size: 14px; color: var(--text-2); }
@keyframes lvIn { from { opacity: 0; transform: translateY(6px); } }

/* Carte */
.lv-map { position: relative; overflow: hidden; border-radius: 22px; min-height: 640px; height: calc(100vh - 190px); max-height: 980px;
    background: radial-gradient(60% 55% at 50% 52%, rgba(31,124,246,.20), transparent 70%), radial-gradient(48% 46% at 50% 50%, #0F1E3D 0%, #070D1C 62%, #04070F 100%); box-shadow: 0 0 0 1px #1C2C4D; }
.lv-map::before { content: ''; position: absolute; left: 50%; top: 50%; width: min(86%, 900px); aspect-ratio: 1; transform: translate(-50%, -50%); border-radius: 50%; box-shadow: 0 0 120px 30px rgba(31,124,246,.16), inset 0 0 80px rgba(31,124,246,.12); pointer-events: none; }
.lv-map .lv-svg { position: absolute; inset: 62px 24px 34px; width: calc(100% - 48px); height: calc(100% - 96px); }
.lv-map .lv-svg[hidden] { display: none; }
.lv-map .lv-svg[data-map="world"] { inset: 62px 12px 34px; width: calc(100% - 24px); }
.lv-tabs { position: absolute; top: 14px; left: 14px; z-index: 2; display: flex; gap: 4px; padding: 4px; border-radius: 999px; background: rgba(255,255,255,.06); box-shadow: inset 0 0 0 1px rgba(255,255,255,.10); }
.lv-tabs button { min-height: 32px; padding: 0 14px; border: 0; border-radius: 999px; background: none; color: #9FB3D3; font: 700 12.5px var(--font); cursor: pointer; transition: background-color 160ms, color 160ms; }
.lv-tabs button:hover { color: #fff; }
.lv-tabs button[aria-selected="true"] { background: #fff; color: #0B1E3D; }
.lv-tabs button:focus-visible { outline: 2px solid #7DB4FF; outline-offset: 2px; }
.lv-legend { position: absolute; top: 22px; right: 18px; z-index: 2; display: flex; gap: 16px; flex-wrap: wrap; font-size: 12px; color: #C9D6EA; }
.lv-legend span { display: inline-flex; align-items: center; gap: 6px; }
.lv-legend i { width: 8px; height: 8px; border-radius: 50%; }
.lv-credit { position: absolute; left: 16px; bottom: 10px; z-index: 2; font-size: 10.5px; color: #6B7FA6; }
.lv-unplaced { position: absolute; right: 18px; bottom: 10px; z-index: 2; font-size: 11.5px; color: #9FB3D3; }
.lv-c { fill: url(#lvHex); stroke: rgba(255,255,255,.62); stroke-width: .8; stroke-linejoin: round; transition: fill 400ms; cursor: default; }
.lv-c:hover { stroke: #fff; stroke-width: 1.4; }
.lv-c.is-live { fill: url(#lvHexLive); filter: url(#lvGlow); }
.lv-c.is-shop { stroke: #7DB4FF; stroke-width: 1.3; }
.lv-svg[data-map="world"] .lv-c { stroke-width: .5; }
.lv-isle { fill: #2F57A8; stroke: rgba(255,255,255,.7); stroke-width: .8; }
.lv-svg[data-map="world"] .lv-isle { stroke-width: .5; }
.lv-isle.is-live { fill: #22C55E; }
.lv-isle.is-shop { stroke: #7DB4FF; }
.lv-lbl { font-family: var(--font); font-weight: 700; letter-spacing: .02em; fill: rgba(255,255,255,.86); text-anchor: middle; pointer-events: none; paint-order: stroke; stroke: rgba(4,7,15,.75); stroke-width: .27em; text-transform: uppercase; }
.lv-pin-ring { fill: none; stroke-width: 2; transform-box: fill-box; transform-origin: center; animation: lvRing 2.2s var(--sv-e-out) infinite; }
@keyframes lvRing { from { transform: scale(.6); opacity: .9; } to { transform: scale(2.6); opacity: 0; } }
.lv-pin-n { font-family: var(--font); font-weight: 800; fill: #fff; text-anchor: middle; dominant-baseline: central; pointer-events: none; }
.lv-tip { position: absolute; z-index: 3; pointer-events: none; padding: 8px 12px; border-radius: 12px; background: rgba(255,255,255,.96); color: #16162A; font-size: 12.5px; box-shadow: 0 10px 24px rgba(0,0,0,.35); white-space: nowrap; transform: translate(-50%, calc(-100% - 12px)); }
.lv-tip b { display: block; font-size: 13px; }
.lv-tip[hidden] { display: none; }

@media (max-width: 1100px) { .lv { grid-template-columns: minmax(0, 1fr); } .lv-map { height: 70vh; min-height: 460px; } }
@media (max-width: 900px) { .lv, .lv-top { padding-left: 16px !important; padding-right: 16px !important; } .lv-head h1 { font-size: 26px; } }
@media (max-width: 420px) { .lv-kpi b { font-size: 21px; } .lv-kpi { padding: 14px; min-height: 100px; } }
@media (prefers-reduced-motion: reduce) { .lv-dot, .lv-pin-ring, .lv-ev { animation: none !important; } .lv-bar i { transition: none; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'live'])

    <main class="main vs-main">
        <header class="topbar lv-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
            <span style="font-weight:800">En direct</span>
            <span style="color:var(--text-2);font-size:13px">· {{ $shop->name }}</span>
        </header>

        <div class="lv">
            <div class="lv-head">
                <div>
                    <h1>Vue en direct <span class="lv-dot" id="lvDot" aria-hidden="true"></span></h1>
                    <div class="lv-ago" id="lvAgo" aria-live="polite">Mis à jour à l’instant</div>
                </div>
            </div>

            <div class="lv-col">
                <section class="lv-kpis" aria-label="Chiffres en direct">
                    <div class="card lv-kpi"><small><i aria-hidden="true"></i>Visiteurs en direct</small><b class="num" data-k="live">{{ $stats['live'] }}</b></div>
                    <div class="card lv-kpi"><small>Ventes du jour</small><b class="num" data-k="todaySales">{{ $stats['todaySales'] }}</b></div>
                    <div class="card lv-kpi"><small>Visiteurs du jour</small><b class="num" data-k="todayVisitors">{{ $stats['todayVisitors'] }}</b></div>
                    <div class="card lv-kpi"><small>Commandes du jour</small><b class="num" data-k="todayOrders">{{ $stats['todayOrders'] }}</b></div>
                </section>

                <section class="card lv-card" aria-labelledby="lvBehH">
                    <h2 id="lvBehH">Comportement des clients <span>· en direct</span></h2>
                    <div class="lv-row"><span>Paniers actifs</span><b class="num" data-b="carts">{{ $stats['behavior']['carts'] }}</b><span class="lv-bar is-green" aria-hidden="true"><i data-bar="carts"></i></span></div>
                    <div class="lv-row"><span>Au formulaire de commande</span><b class="num" data-b="form">{{ $stats['behavior']['form'] }}</b><span class="lv-bar is-amber" aria-hidden="true"><i data-bar="form"></i></span></div>
                    <div class="lv-row"><span>Commandes (10 dernières min)</span><b class="num" data-b="orders">{{ $stats['behavior']['orders'] }}</b><span class="lv-bar" aria-hidden="true"><i data-bar="orders"></i></span></div>
                </section>

                <section class="card lv-card" aria-labelledby="lvNowH">
                    <h2 id="lvNowH">En ce moment</h2>
                    <ul class="lv-events" id="lvEvents" aria-live="polite"></ul>
                    <p class="lv-empty" id="lvEmpty" hidden>Aucun visiteur en ce moment.</p>
                </section>
            </div>

            <section class="lv-map" aria-label="Carte : visiteurs en direct et commandes du jour">
                <div class="lv-tabs" role="tablist" aria-label="Carte">
                    <button type="button" role="tab" data-tab="africa" aria-selected="true">Afrique</button>
                    <button type="button" role="tab" data-tab="world" aria-selected="false">Monde</button>
                </div>
                <div class="lv-legend"><span><i style="background:#3B82F6"></i>Commandes du jour</span><span><i style="background:#22C55E"></i>Visiteurs en direct</span></div>
                {{-- Motifs communs aux deux cartes : nid d'abeille bleu au repos, vert pour les pays avec des visiteurs --}}
                <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
                    <defs>
                        <pattern id="lvHex" width="6.93" height="12" patternUnits="userSpaceOnUse">
                            <rect width="6.93" height="12" fill="#0C1A36"/>
                            <path d="M3.46 .6 6.2 2.2v3.2L3.46 7 .73 5.4V2.2Z M0 6.6l2.73 1.6v3.2L0 13l-2.73-1.6V8.2Z M6.93 6.6l2.73 1.6v3.2L6.93 13 4.2 11.4V8.2Z" fill="#2C56A6"/>
                        </pattern>
                        <pattern id="lvHexLive" width="6.93" height="12" patternUnits="userSpaceOnUse">
                            <rect width="6.93" height="12" fill="#0A2A1A"/>
                            <path d="M3.46 .6 6.2 2.2v3.2L3.46 7 .73 5.4V2.2Z M0 6.6l2.73 1.6v3.2L0 13l-2.73-1.6V8.2Z M6.93 6.6l2.73 1.6v3.2L6.93 13 4.2 11.4V8.2Z" fill="#22C55E"/>
                        </pattern>
                        <filter id="lvGlow" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="4" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
                    </defs>
                </svg>
                @include('boutique.live._map', ['key' => 'africa', 'map' => $maps['africa'], 'shopIso' => $shopIso, 'labelMin' => 900, 'font' => 9, 'hidden' => false])
                @include('boutique.live._map', ['key' => 'world', 'map' => $maps['world'], 'shopIso' => $shopIso, 'labelMin' => 450, 'font' => 6, 'hidden' => true])
                <div class="lv-tip" id="lvTip" hidden></div>
                <div class="lv-unplaced" id="lvUnplaced" hidden></div>
                <div class="lv-credit">Carte : Natural Earth · Pays des visiteurs : DB-IP Lite (CC BY 4.0) · aucune adresse IP enregistrée</div>
            </section>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const URL_DATA = @json(route('boutique.live.data'));
    const ICONS = @json($icons);
    const CENTERS = @json($centers);
    const PIN = { africa: 11, world: 6 }; // taille des repères selon l'échelle de la carte
    const $ = id => document.getElementById(id);
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let data = @json($stats), lastAt = Date.now(), failed = false;

    function render(d, first) {
        // Chiffres (petit éclat de couleur quand une valeur change)
        ['live', 'todaySales', 'todayVisitors', 'todayOrders'].forEach(k => {
            const el = document.querySelector('[data-k="' + k + '"]'); if (!el) return;
            const v = String(d[k]);
            if (!first && el.textContent !== v) { el.classList.add('is-bump'); setTimeout(() => el.classList.remove('is-bump'), 1200); }
            el.textContent = v;
        });
        // Comportement : barres à l'échelle des visiteurs en direct
        const base = Math.max(d.live, d.behavior.carts, d.behavior.form, 1), ob = Math.max(d.behavior.orders, d.todayOrders ? 5 : 1);
        ['carts', 'form', 'orders'].forEach(k => {
            document.querySelector('[data-b="' + k + '"]').textContent = d.behavior[k];
            document.querySelector('[data-bar="' + k + '"]').style.width = Math.min(100, Math.round(d.behavior[k] / (k === 'orders' ? ob : base) * 100)) + '%';
        });
        // En ce moment
        $('lvEvents').innerHTML = d.events.map(e => '<li class="lv-ev"><span class="lv-ev-ico is-' + esc(e.stage) + '">' + (ICONS[e.stage] || ICONS.browse) + '</span><div><b>' + esc(e.text) + '</b><small>' + esc(e.place) + ' · ' + esc(e.since) + '</small></div></li>').join('');
        $('lvEmpty').hidden = d.events.length > 0;
        // Cartes : pays avec visiteurs en vert, repères (visiteurs verts, commandes bleues) au centre des pays
        document.querySelectorAll('.lv-countries [data-iso]').forEach(p => p.classList.toggle('is-live', !!d.map.visitors[p.dataset.iso]));
        document.querySelectorAll('.lv-svg').forEach(svg => {
            const key = svg.dataset.map, centers = CENTERS[key] || {}, r = PIN[key], gap = r * 1.2;
            const pin = (iso, n, color, dx) => {
                const c = centers[iso]; if (!c || !n) return '';
                const x = c[0] + dx, y = c[1] - r * 1.4;
                return '<g><circle class="lv-pin-ring" cx="' + x + '" cy="' + y + '" r="' + r + '" stroke="' + color + '"/><circle cx="' + x + '" cy="' + y + '" r="' + r + '" fill="' + color + '" stroke="#04070F" stroke-width="' + (r / 5.5) + '"/><text class="lv-pin-n" x="' + x + '" y="' + y + '" style="font-size:' + r + 'px">' + n + '</text></g>';
            };
            let pins = '';
            Object.entries(d.map.orders).forEach(([iso, n]) => { pins += pin(iso, n, '#3B82F6', d.map.visitors[iso] ? -gap : 0); });
            Object.entries(d.map.visitors).forEach(([iso, n]) => { pins += pin(iso, n, '#22C55E', d.map.orders[iso] ? gap : 0); });
            svg.querySelector('.lv-pins').innerHTML = pins;
        });
        // Visiteurs sans pays connu, et (sur la carte de l'Afrique) ceux venus d'ailleurs
        const africa = CENTERS.africa || {};
        const outside = Object.entries(d.map.visitors).reduce((a, [iso, n]) => a + (africa[iso] ? 0 : n), 0);
        const off = d.map.unplaced + (current === 'africa' ? outside : 0);
        $('lvUnplaced').hidden = !off;
        $('lvUnplaced').textContent = !off ? '' : (current === 'africa' && outside
            ? off + ' visiteur' + (off > 1 ? 's' : '') + ' hors de la carte · voir « Monde »'
            : off + ' visiteur' + (off > 1 ? 's' : '') + ' sans lieu connu');
    }

    // Onglets Afrique / Monde (choix gardé dans ce navigateur)
    let current = 'africa';
    try { if (localStorage.getItem('shopio_live_map') === 'world') current = 'world'; } catch (e) {}
    function showMap(key) {
        current = key;
        // Attribut « hidden » posé directement : sur un <svg>, la propriété .hidden n'existe pas (seulement sur le HTML)
        document.querySelectorAll('.lv-svg').forEach(s => s.toggleAttribute('hidden', s.dataset.map !== key));
        document.querySelectorAll('.lv-tabs [data-tab]').forEach(b => b.setAttribute('aria-selected', String(b.dataset.tab === key)));
        try { localStorage.setItem('shopio_live_map', key); } catch (e) {}
        tip.hidden = true;
        render(data, true);
    }
    document.querySelector('.lv-tabs').addEventListener('click', e => { const b = e.target.closest('[data-tab]'); if (b) showMap(b.dataset.tab); });
    document.querySelector('.lv-tabs').addEventListener('keydown', e => {
        if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
        const next = current === 'africa' ? 'world' : 'africa';
        showMap(next); document.querySelector('.lv-tabs [data-tab="' + next + '"]').focus();
    });

    // Info-bulle au survol d'un pays
    const tip = $('lvTip'), mapBox = document.querySelector('.lv-map');
    mapBox.addEventListener('mousemove', e => {
        const p = e.target.closest('.lv-countries [data-iso]'); if (!p) { tip.hidden = true; return; }
        const v = data.map.visitors[p.dataset.iso] || 0, o = data.map.orders[p.dataset.iso] || 0;
        tip.innerHTML = '<b>' + esc(p.dataset.name) + '</b>' + v + ' visiteur' + (v > 1 ? 's' : '') + ' en direct · ' + o + ' commande' + (o > 1 ? 's' : '') + ' aujourd’hui';
        const r = mapBox.getBoundingClientRect();
        tip.style.left = (e.clientX - r.left) + 'px'; tip.style.top = (e.clientY - r.top) + 'px'; tip.hidden = false;
    });
    mapBox.addEventListener('mouseleave', () => { tip.hidden = true; });

    // « Mis à jour il y a … »
    setInterval(() => {
        const s = Math.round((Date.now() - lastAt) / 1000);
        $('lvAgo').textContent = failed ? 'Connexion perdue, nouvel essai dans quelques secondes…' : (s < 3 ? 'Mis à jour à l’instant' : 'Mis à jour il y a ' + s + ' s');
    }, 1000);

    async function refresh() {
        if (document.hidden) return;
        try {
            const r = await fetch(URL_DATA, { headers: { 'Accept': 'application/json' } });
            if (!r.ok) throw new Error('HTTP ' + r.status);
            data = await r.json(); lastAt = Date.now(); failed = false;
            render(data, false);
        } catch (e) { failed = true; }
        $('lvDot').classList.toggle('is-off', failed);
    }
    showMap(current);
    setInterval(refresh, 10000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
})();
</script>
@endpush
