{{--
    resources/views/boutique/live/index.blade.php
    Route     : GET /boutique/en-direct → Boutique\LiveController@index → name('boutique.live')
                Données mises à jour toutes les 10 s : GET /boutique/en-direct/donnees → name('boutique.live.data')
    Rôle      : vue en direct de la boutique. Chiffres réels (App\Support\LiveStats) : visiteurs en direct, ventes,
                visiteurs et commandes du jour, comportement des clients, fil « En ce moment », globe terrestre
                (pays des visiteurs déduit de l'IP sans l'enregistrer ; commandes du jour dans le pays de la boutique).
    Variables : $shop, $stats (LiveStats::for), $globe (App\Support\LiveMaps::globe : pays en longitude / latitude)
    Globe     : projection orthographique dessinée en SVG (sans bibliothèque) : glisser pour le faire tourner (avec élan),
                molette ou deux doigts pour zoomer, double-clic pour s'approcher d'un endroit ; « Afrique » et « Monde »
                sont deux vues du même globe (choix gardé dans le navigateur) ; « Monde » tourne tout seul doucement.
--}}
@extends('layouts.app')

@section('title', 'En direct · ' . $shop->name)

@php
    use App\Support\IconLibrary as Ico;
    $bodyClass = 'is-dashboard';
    $icons = collect(['browse' => 'eye', 'product' => 'tag', 'cart' => 'cart', 'form' => 'edit', 'order' => 'bag'])
        ->map(fn ($n) => Ico::svg($n, '', 16));
    $shopIso = strtoupper((string) $shop->country);
    $countries = $globe['countries'];
    // Pour le JavaScript : contours et centres seulement (les noms sont déjà dans la page)
    $globeJs = array_map(fn ($c) => ['iso' => $c['iso'], 'c' => $c['c'], 'a' => $c['area'], 'p' => $c['p']], $countries);
    $ci = fn ($d) => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>';
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

/* Globe */
.lv-map { position: relative; overflow: hidden; border-radius: 22px; min-height: 640px; height: calc(100vh - 190px); max-height: 980px;
    background: radial-gradient(48% 46% at 50% 50%, #0F1E3D 0%, #070D1C 62%, #04070F 100%); box-shadow: 0 0 0 1px #1C2C4D; }
.lv-svg { position: absolute; inset: 0; width: 100%; height: 100%; display: block; cursor: grab; touch-action: pan-x pan-y; user-select: none; -webkit-user-select: none; }
.lv-svg.is-drag { cursor: grabbing; }
.lv-svg:focus-visible { outline: none; }
.lv-map:focus-within .lv-ctrl { box-shadow: inset 0 0 0 1px rgba(125,180,255,.5); }
.lv-halo { fill: none; stroke: rgba(31,124,246,.35); stroke-width: 18; filter: url(#lvBlur); }
.lv-ocean { fill: url(#lvOcean); stroke: rgba(125,180,255,.35); stroke-width: 1.2; }
.lv-grat { fill: none; stroke: rgba(125,180,255,.10); stroke-width: .8; }
.lv-shade { fill: url(#lvShade); pointer-events: none; }
.lv-tabs { position: absolute; top: 14px; left: 14px; z-index: 2; display: flex; gap: 4px; padding: 4px; border-radius: 999px; background: rgba(12,26,54,.82); box-shadow: inset 0 0 0 1px rgba(255,255,255,.10); }
.lv-tabs button { min-height: 32px; padding: 0 14px; border: 0; border-radius: 999px; background: none; color: #9FB3D3; font: 700 12.5px var(--font); cursor: pointer; transition: background-color 160ms, color 160ms; }
.lv-tabs button:hover { color: #fff; }
.lv-tabs button[aria-selected="true"] { background: #fff; color: #0B1E3D; }
.lv-tabs button:focus-visible { outline: 2px solid #7DB4FF; outline-offset: 2px; }
.lv-legend { position: absolute; top: 16px; right: 14px; z-index: 2; display: flex; gap: 14px; flex-wrap: wrap; padding: 7px 12px; border-radius: 999px; background: rgba(12,26,54,.82); box-shadow: inset 0 0 0 1px rgba(255,255,255,.10); font-size: 12px; color: #C9D6EA; }
.lv-legend span { display: inline-flex; align-items: center; gap: 6px; }
.lv-legend i { width: 8px; height: 8px; border-radius: 50%; }
.lv-credit { position: absolute; left: 16px; bottom: 10px; z-index: 2; font-size: 10.5px; color: #6B7FA6; }
.lv-unplaced { position: absolute; right: 18px; bottom: 10px; z-index: 2; font-size: 11.5px; color: #9FB3D3; }
.lv-c { fill: url(#lvHex); stroke: rgba(255,255,255,.55); stroke-width: .7; stroke-linejoin: round; transition: fill 400ms; }
.lv-c:hover { stroke: #fff; stroke-width: 1.3; }
.lv-c.is-live { fill: url(#lvHexLive); filter: url(#lvGlow); }
.lv-c.is-shop { stroke: #7DB4FF; stroke-width: 1.2; }
.lv-isle { fill: #2F57A8; stroke: rgba(255,255,255,.7); stroke-width: .7; }
.lv-isle.is-live { fill: #22C55E; }
.lv-isle.is-shop { stroke: #7DB4FF; }
.lv-lbl { font-family: var(--font); font-weight: 700; letter-spacing: .02em; fill: rgba(255,255,255,.86); text-anchor: middle; pointer-events: none; paint-order: stroke; stroke: rgba(4,7,15,.75); stroke-width: .27em; text-transform: uppercase; }
.lv-pin-ring { fill: none; stroke-width: 2; transform-box: fill-box; transform-origin: center; animation: lvRing 2.2s var(--sv-e-out) infinite; }
@keyframes lvRing { from { transform: scale(.6); opacity: .9; } to { transform: scale(2.6); opacity: 0; } }
.lv-pin-n { font-family: var(--font); font-weight: 800; fill: #fff; text-anchor: middle; dominant-baseline: central; pointer-events: none; }
.lv-tip { position: absolute; z-index: 3; pointer-events: none; padding: 8px 12px; border-radius: 12px; background: rgba(255,255,255,.96); color: #16162A; font-size: 12.5px; box-shadow: 0 10px 24px rgba(0,0,0,.35); white-space: nowrap; transform: translate(-50%, calc(-100% - 12px)); }
.lv-tip b { display: block; font-size: 13px; }
.lv-tip[hidden] { display: none; }
.lv-ctrl { position: absolute; right: 14px; bottom: 34px; z-index: 2; display: flex; flex-direction: column; gap: 2px; padding: 4px; border-radius: 14px; background: rgba(12,26,54,.82); box-shadow: inset 0 0 0 1px rgba(255,255,255,.12); backdrop-filter: blur(6px); }
.lv-ctrl button { width: 38px; height: 38px; border: 0; border-radius: 10px; background: none; color: #DCE6F5; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background-color 160ms, color 160ms; }
.lv-ctrl button:hover { background: rgba(255,255,255,.1); color: #fff; }
.lv-ctrl button[aria-pressed="true"] { color: #7DB4FF; }
.lv-ctrl button:focus-visible { outline: 2px solid #7DB4FF; outline-offset: -2px; }
.lv-ctrl button:disabled { opacity: .35; cursor: default; }
.lv-ctrl hr { width: 22px; margin: 2px auto; border: 0; border-top: 1px solid rgba(255,255,255,.14); }
.lv-zoom { position: absolute; right: 62px; bottom: 40px; z-index: 2; padding: 4px 8px; border-radius: 8px; background: rgba(12,26,54,.82); color: #C9D6EA; font-size: 11.5px; font-weight: 700; pointer-events: none; }
.lv-zoom[hidden] { display: none; }
.lv-hint { position: absolute; left: 16px; bottom: 26px; z-index: 2; font-size: 11px; color: #8FA3C6; pointer-events: none; }
.lv-hint .is-touch { display: none; }
@media (hover: none) { .lv-hint .is-mouse { display: none; } .lv-hint .is-touch { display: inline; } }

@media (max-width: 1100px) { .lv { grid-template-columns: minmax(0, 1fr); } .lv-map { height: 70vh; min-height: 460px; } }
@media (max-width: 900px) { .lv, .lv-top { padding-left: 16px !important; padding-right: 16px !important; } .lv-head h1 { font-size: 26px; } }
@media (max-width: 420px) { .lv-kpi b { font-size: 21px; } .lv-kpi { padding: 14px; min-height: 100px; } }
@media (max-width: 640px) {
    .lv-ctrl { bottom: 54px; } .lv-ctrl button { width: 40px; height: 40px; }
    .lv-zoom { bottom: 60px; }
    .lv-legend { top: 60px; left: 14px; right: auto; gap: 10px; font-size: 11.5px; }
    .lv-hint { bottom: 46px; right: 70px; }
}
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

            <section class="lv-map" aria-label="Globe : visiteurs en direct et commandes du jour">
                <div class="lv-tabs" role="tablist" aria-label="Vue du globe">
                    <button type="button" role="tab" data-tab="africa" aria-selected="true">Afrique</button>
                    <button type="button" role="tab" data-tab="world" aria-selected="false">Monde</button>
                </div>
                <div class="lv-legend"><span><i style="background:#3B82F6"></i>Commandes du jour</span><span><i style="background:#22C55E"></i>Visiteurs en direct</span></div>

                {{-- Le globe : dessiné par le JavaScript de la page (projection orthographique) --}}
                <svg class="lv-svg" id="lvGlobe" viewBox="0 0 1000 1000" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Globe terrestre" tabindex="0">
                    <defs>
                        {{-- Nid d'abeille bleu au repos, vert pour les pays avec des visiteurs --}}
                        <pattern id="lvHex" width="6.93" height="12" patternUnits="userSpaceOnUse">
                            <rect width="6.93" height="12" fill="#0C1A36"/>
                            <path d="M3.46 .6 6.2 2.2v3.2L3.46 7 .73 5.4V2.2Z M0 6.6l2.73 1.6v3.2L0 13l-2.73-1.6V8.2Z M6.93 6.6l2.73 1.6v3.2L6.93 13 4.2 11.4V8.2Z" fill="#2C56A6"/>
                        </pattern>
                        <pattern id="lvHexLive" width="6.93" height="12" patternUnits="userSpaceOnUse">
                            <rect width="6.93" height="12" fill="#0A2A1A"/>
                            <path d="M3.46 .6 6.2 2.2v3.2L3.46 7 .73 5.4V2.2Z M0 6.6l2.73 1.6v3.2L0 13l-2.73-1.6V8.2Z M6.93 6.6l2.73 1.6v3.2L6.93 13 4.2 11.4V8.2Z" fill="#22C55E"/>
                        </pattern>
                        <radialGradient id="lvOcean" cx="42%" cy="38%" r="70%"><stop offset="0" stop-color="#14306A"/><stop offset=".65" stop-color="#0A1838"/><stop offset="1" stop-color="#050C20"/></radialGradient>
                        <radialGradient id="lvShade" cx="38%" cy="34%" r="72%"><stop offset=".55" stop-color="#000" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity=".45"/></radialGradient>
                        <filter id="lvGlow" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="4" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
                        <filter id="lvBlur" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="14"/></filter>
                    </defs>
                    <circle class="lv-halo" id="lvHalo" cx="500" cy="500" r="440"/>
                    <circle class="lv-ocean" id="lvOcean2" cx="500" cy="500" r="440"/>
                    <path class="lv-grat" id="lvGrat" d=""/>
                    <g class="lv-countries">
                        @foreach($countries as $c)
                            @if(count($c['p']))
                                <path class="lv-c {{ $c['iso'] === $shopIso ? 'is-shop' : '' }}" data-iso="{{ $c['iso'] }}" data-name="{{ $c['name'] }}" d=""/>
                            @else
                                <circle class="lv-isle {{ $c['iso'] === $shopIso ? 'is-shop' : '' }}" data-iso="{{ $c['iso'] }}" data-name="{{ $c['name'] }}" r="3.5" cx="-99" cy="-99"/>
                            @endif
                        @endforeach
                    </g>
                    <circle class="lv-shade" id="lvShadeC" cx="500" cy="500" r="440"/>
                    <g aria-hidden="true" id="lvLbls">
                        @foreach($countries as $c)
                            @if($c['area'] >= 4 || $c['iso'] === $shopIso)
                                <text class="lv-lbl" data-lbl="{{ $c['iso'] }}" x="-99" y="-99" style="display:none">{{ $c['name'] }}</text>
                            @endif
                        @endforeach
                    </g>
                    <g class="lv-pins" aria-hidden="true"></g>
                </svg>

                <div class="lv-ctrl" role="group" aria-label="Manipuler le globe">
                    <button type="button" data-zoom="in" title="Zoomer" aria-label="Zoomer">{!! $ci('M12 5v14M5 12h14') !!}</button>
                    <button type="button" data-zoom="out" title="Dézoomer" aria-label="Dézoomer">{!! $ci('M5 12h14') !!}</button>
                    <hr>
                    <button type="button" data-turn="-30" title="Tourner vers l’ouest" aria-label="Faire tourner le globe vers la gauche">{!! $ci('M15 6l-6 6 6 6') !!}</button>
                    <button type="button" data-turn="30" title="Tourner vers l’est" aria-label="Faire tourner le globe vers la droite">{!! $ci('M9 6l6 6-6 6') !!}</button>
                    <hr>
                    <button type="button" id="lvSpin" aria-pressed="false" title="Rotation automatique" aria-label="Rotation automatique du globe"><span id="lvSpinIco">{!! $ci('M8 5v14l11-7z') !!}</span></button>
                    <button type="button" data-reset title="Revenir à la vue de départ" aria-label="Revenir à la vue de départ">{!! $ci('M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3') !!}</button>
                </div>
                <div class="lv-zoom" id="lvZoom" hidden></div>
                <div class="lv-hint"><span class="is-mouse">Glisser : faire tourner · molette : zoom · double-clic : s’approcher</span><span class="is-touch">Un doigt sur le globe : tourner · deux doigts : zoom</span></div>
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
    const GLOBE = @json($globeJs);
    const SHOP = @json($shopIso);
    const $ = id => document.getElementById(id);
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let data = @json($stats), lastAt = Date.now(), failed = false;

    /* ───────── Globe : projection orthographique ─────────
       Chaque point est préparé une fois (cos·sin de la longitude, sin de la latitude) : faire tourner le globe ne coûte
       ensuite que quelques multiplications par point. Les points de la face cachée sont posés sur le bord du disque. */
    const D = Math.PI / 180, C = 500, R0 = 440, K_MIN = .8, K_MAX = 6;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const svg = $('lvGlobe');
    const prep = (lon, lat) => { const L = lon * D, P = lat * D, cp = Math.cos(P); return [cp * Math.sin(L), cp * Math.cos(L), Math.sin(P)]; };
    const byIso = {};
    const COUNTRIES = GLOBE.map(c => {
        const o = { iso: c.iso, a: c.a, c: prep(c.c[0], c.c[1]),
            rings: c.p.map(r => { const A = new Float64Array(r.length * 3); r.forEach(([lo, la], i) => { const q = prep(lo, la); A[i * 3] = q[0]; A[i * 3 + 1] = q[1]; A[i * 3 + 2] = q[2]; }); return A; }),
            el: svg.querySelector('.lv-countries [data-iso="' + c.iso + '"]'), lbl: svg.querySelector('[data-lbl="' + c.iso + '"]') };
        byIso[c.iso] = o;
        return o;
    });
    // Méridiens et parallèles tous les 30°
    const GRAT = [];
    for (let lon = -180; lon < 180; lon += 30) { const l = []; for (let lat = -80; lat <= 80; lat += 4) l.push(prep(lon, lat)); GRAT.push(l); }
    for (let lat = -60; lat <= 60; lat += 30) { const l = []; for (let lon = -180; lon <= 180; lon += 4) l.push(prep(lon, lat)); GRAT.push(l); }

    const PRESETS = { africa: { lon: 17, lat: 6, k: 1.12 }, world: { lon: SHOP && byIso[SHOP] ? Math.atan2(byIso[SHOP].c[0], byIso[SHOP].c[1]) / D : 0, lat: 18, k: 1 } };
    let current = 'africa';
    try { if (localStorage.getItem('shopio_live_map') === 'world') current = 'world'; } catch (e) {}
    const cam = Object.assign({}, PRESETS[current]);

    let cl, sl, cp0, sp0, R;
    function frame() { cl = Math.cos(cam.lon * D); sl = Math.sin(cam.lon * D); cp0 = Math.cos(cam.lat * D); sp0 = Math.sin(cam.lat * D); R = R0 * cam.k; }
    // [x écran, y écran, profondeur] (profondeur < 0 : face cachée)
    function project(a, b, s, out) {
        const x = a * cl - b * sl, w = b * cl + a * sl, y = cp0 * s - sp0 * w, z = sp0 * s + cp0 * w;
        out[0] = x; out[1] = y; out[2] = z; return out;
    }
    const P = [0, 0, 0];
    let PX = new Float64Array(0), PY = PX, PZ = PX;
    const sx = x => (C + R * x).toFixed(1), sy = y => (C - R * y).toFixed(1);
    /* Contour coupé à l'horizon : la partie cachée est remplacée par l'arc du bord du globe, entre le point où le
       contour passe derrière et celui où il revient (le plus court des deux arcs). */
    function ringPath(A) {
        const n = A.length / 3;
        if (PX.length < n) { PX = new Float64Array(n); PY = new Float64Array(n); PZ = new Float64Array(n); }
        let s = -1;
        for (let i = 0; i < n; i++) { project(A[i * 3], A[i * 3 + 1], A[i * 3 + 2], P); PX[i] = P[0]; PY[i] = P[1]; PZ[i] = P[2]; if (s < 0 && P[2] >= 0) s = i; }
        if (s < 0) return '';
        const edge = (i, j) => { const t = PZ[i] / (PZ[i] - PZ[j]), x = PX[i] + t * (PX[j] - PX[i]), y = PY[i] + t * (PY[j] - PY[i]), m = Math.hypot(x, y) || 1; return [x / m, y / m]; };
        let d = 'M' + sx(PX[s]) + ' ' + sy(PY[s]), out = null;
        for (let k = 0; k < n; k++) {
            const i = (s + k) % n, j = (s + k + 1) % n, fi = PZ[i] >= 0, fj = PZ[j] >= 0;
            if (fi && fj) { if (j !== s) d += 'L' + sx(PX[j]) + ' ' + sy(PY[j]); }
            else if (fi && !fj) { out = edge(i, j); d += 'L' + sx(out[0]) + ' ' + sy(out[1]); }
            else if (!fi && fj) {
                const back = edge(i, j);
                if (out) { let da = Math.atan2(-back[1], back[0]) - Math.atan2(-out[1], out[0]); da = ((da % (2 * Math.PI)) + 3 * Math.PI) % (2 * Math.PI) - Math.PI; d += 'A' + R.toFixed(1) + ' ' + R.toFixed(1) + ' 0 0 ' + (da > 0 ? 1 : 0) + ' ' + sx(back[0]) + ' ' + sy(back[1]); }
                d += 'L' + sx(back[0]) + ' ' + sy(back[1]);
                if (j !== s) d += 'L' + sx(PX[j]) + ' ' + sy(PY[j]);
                out = null;
            }
        }
        return d + 'Z';
    }
    // Taille d'un pixel de l'écran en unités du dessin : noms et repères gardent la même taille sur tous les écrans
    let UNIT = 1.4, PIN_R = 11;
    function measure() { const m = svg.getScreenCTM(); if (m && m.a) UNIT = 1 / m.a; PIN_R = 8 * UNIT; $('lvLbls').style.fontSize = (6.5 * UNIT).toFixed(2) + 'px'; }
    // Recalculé quand le globe change de taille (écran tourné, menu, ou fin de l'écran de chargement du site)
    if ('ResizeObserver' in window) new ResizeObserver(() => { const u = UNIT; measure(); if (Math.abs(u - UNIT) > .01) render(data, true); }).observe(svg);
    else window.addEventListener('resize', () => { measure(); render(data, true); });
    function draw() {
        frame();
        ['lvHalo', 'lvOcean2', 'lvShadeC'].forEach(id => $(id).setAttribute('r', R.toFixed(1)));
        // Méridiens et parallèles (face visible seulement)
        let g = '';
        GRAT.forEach(line => { let pen = false; line.forEach(q => { project(q[0], q[1], q[2], P); if (P[2] < 0) { pen = false; return; } g += (pen ? 'L' : 'M') + (C + R * P[0]).toFixed(1) + ' ' + (C - R * P[1]).toFixed(1); pen = true; }); });
        $('lvGrat').setAttribute('d', g);
        // Pays, petits pays (points) et étiquettes
        const lblMin = 40 / (cam.k * cam.k) * Math.max(1, Math.pow(UNIT / 1.45, 2));
        COUNTRIES.forEach(c => {
            if (c.el) {
                if (c.rings.length) c.el.setAttribute('d', c.rings.map(ringPath).join(''));
                else { project(c.c[0], c.c[1], c.c[2], P); const on = P[2] > 0; c.el.setAttribute('cx', on ? (C + R * P[0]).toFixed(1) : -99); c.el.setAttribute('cy', on ? (C - R * P[1]).toFixed(1) : -99); }
            }
            if (c.lbl) {
                project(c.c[0], c.c[1], c.c[2], P);
                const on = P[2] > .2 && (c.a >= lblMin || c.iso === SHOP);
                c.lbl.style.display = on ? '' : 'none';
                if (on) { c.lbl.setAttribute('x', (C + R * P[0]).toFixed(1)); c.lbl.setAttribute('y', (C - R * P[1] + 3).toFixed(1)); }
            }
        });
        // Repères (visiteurs, commandes) au centre des pays visibles
        svg.querySelectorAll('.lv-pin').forEach(p => {
            const c = byIso[p.dataset.iso]; if (!c) return;
            project(c.c[0], c.c[1], c.c[2], P);
            p.style.display = P[2] > .05 ? '' : 'none';
            p.setAttribute('transform', 'translate(' + (C + R * P[0] + Number(p.dataset.dx)).toFixed(1) + ' ' + (C - R * P[1] - PIN_R * 1.4).toFixed(1) + ')');
        });
        $('lvZoom').hidden = Math.abs(cam.k - PRESETS[current].k) < .05;
        $('lvZoom').textContent = '× ' + cam.k.toFixed(1).replace('.', ',');
        document.querySelector('[data-zoom="in"]').disabled = cam.k >= K_MAX - .01;
        document.querySelector('[data-zoom="out"]').disabled = cam.k <= K_MIN + .01;
    }

    /* ───────── Mouvement : une seule boucle (animation des boutons, élan après un glissé, rotation automatique) ───────── */
    let dirty = true, loop = 0, tween = null, vel = null, lastT = 0, idleUntil = 0;
    let spin = current === 'world' && !reduce;
    function kick() { if (!loop) { lastT = performance.now(); loop = requestAnimationFrame(tick); } }
    function changed() { dirty = true; kick(); }
    function tick(now) {
        const dt = Math.min(64, now - lastT); lastT = now; loop = 0;
        let busy = false;
        if (tween) {
            const p = reduce ? 1 : Math.min(1, (now - tween.t0) / tween.d), e = 1 - Math.pow(1 - p, 3);
            cam.lon = tween.from.lon + tween.dl * e; cam.lat = tween.from.lat + (tween.to.lat - tween.from.lat) * e; cam.k = tween.from.k + (tween.to.k - tween.from.k) * e;
            dirty = true; if (p >= 1) tween = null; else busy = true;
        } else if (vel) {
            cam.lon -= vel.x * dt; cam.lat = Math.max(-80, Math.min(80, cam.lat + vel.y * dt));
            vel.x *= Math.pow(.94, dt / 16); vel.y *= Math.pow(.94, dt / 16);
            dirty = true; if (Math.hypot(vel.x, vel.y) < .002) vel = null; else busy = true;
        } else if (spin && now > idleUntil && !drag) {
            cam.lon += dt * .006; dirty = true; busy = true; // 6° par seconde
        } else if (spin) busy = true;
        if (dirty) { dirty = false; draw(); }
        if (busy) loop = requestAnimationFrame(tick);
    }
    function goTo(to, d) {
        let dl = ((to.lon - cam.lon) % 360 + 540) % 360 - 180;
        tween = { from: { lon: cam.lon, lat: cam.lat, k: cam.k }, to: { lat: Math.max(-80, Math.min(80, to.lat)), k: Math.max(K_MIN, Math.min(K_MAX, to.k)) }, dl, t0: performance.now(), d: d || 600 };
        vel = null; kick();
    }
    function touched() { idleUntil = performance.now() + 8000; tween = null; }
    function setSpin(on) {
        spin = on && !reduce;
        $('lvSpin').setAttribute('aria-pressed', String(spin));
        $('lvSpinIco').innerHTML = spin ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M9 5v14M15 5v14"/></svg>'
            : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>';
        $('lvSpin').title = spin ? 'Arrêter la rotation automatique' : 'Rotation automatique';
        idleUntil = 0; kick();
    }
    $('lvSpin').addEventListener('click', () => setSpin(!spin));
    $('lvSpin').disabled = reduce;

    // Écran → coordonnées du dessin ; et point du globe sous l'écran (null hors du disque)
    function toSvg(x, y) { const m = svg.getScreenCTM(); if (!m) return [0, 0]; const p = new DOMPoint(x, y).matrixTransform(m.inverse()); return [p.x, p.y]; }
    function lonLatAt(q) {
        frame();
        const x = (q[0] - C) / R, y = -(q[1] - C) / R, rr = x * x + y * y; if (rr > 1) return null;
        const z = Math.sqrt(1 - rr);
        return { lat: Math.asin(y * cp0 + z * sp0) / D, lon: cam.lon + Math.atan2(x, z * cp0 - y * sp0) / D };
    }

    // Boutons
    document.querySelector('.lv-ctrl').addEventListener('click', e => {
        const b = e.target.closest('button'); if (!b || b.id === 'lvSpin') return;
        touched();
        if (b.dataset.zoom) goTo({ lon: cam.lon, lat: cam.lat, k: cam.k * (b.dataset.zoom === 'in' ? 1.6 : 1 / 1.6) }, 350);
        else if (b.dataset.turn) goTo({ lon: cam.lon + Number(b.dataset.turn), lat: cam.lat, k: cam.k }, 450);
        else if (b.dataset.reset !== undefined) { idleUntil = 0; goTo(PRESETS[current]); }
    });
    // Molette : zoom ; double-clic : s'approcher de cet endroit
    svg.addEventListener('wheel', e => {
        e.preventDefault(); touched(); vel = null;
        cam.k = Math.max(K_MIN, Math.min(K_MAX, cam.k * Math.exp(-e.deltaY * (e.ctrlKey ? .01 : .0015))));
        tip.hidden = true; changed();
    }, { passive: false });
    svg.addEventListener('dblclick', e => {
        const p = lonLatAt(toSvg(e.clientX, e.clientY)); if (!p) return;
        touched(); goTo({ lon: p.lon, lat: p.lat, k: cam.k * 1.6 });
    });

    // Glisser (souris, ou un doigt sur le globe) : faire tourner, avec de l'élan au lâcher
    let drag = null;
    function dragStart(x, y) { touched(); vel = null; drag = { q: toSvg(x, y), lon: cam.lon, lat: cam.lat, hist: [[performance.now(), cam.lon, cam.lat]] }; svg.classList.add('is-drag'); tip.hidden = true; }
    function dragMove(x, y) {
        if (!drag) return;
        const q = toSvg(x, y), f = 1 / (R0 * cam.k * D);
        cam.lon = drag.lon - (q[0] - drag.q[0]) * f;
        cam.lat = Math.max(-80, Math.min(80, drag.lat + (q[1] - drag.q[1]) * f));
        const now = performance.now(); drag.hist.push([now, cam.lon, cam.lat]); while (drag.hist.length > 6) drag.hist.shift();
        changed();
    }
    function dragEnd() {
        if (!drag) return;
        const h = drag.hist, a = h[0], b = h[h.length - 1], dt = b[0] - a[0];
        if (!reduce && dt > 0 && performance.now() - b[0] < 80) vel = { x: -(b[1] - a[1]) / dt, y: (b[2] - a[2]) / dt };
        drag = null; svg.classList.remove('is-drag'); touched(); kick();
    }
    svg.addEventListener('pointerdown', e => { if (e.pointerType !== 'mouse' || e.button !== 0) return; svg.setPointerCapture(e.pointerId); dragStart(e.clientX, e.clientY); });
    svg.addEventListener('pointermove', e => { if (e.pointerType === 'mouse') dragMove(e.clientX, e.clientY); });
    svg.addEventListener('pointerup', e => { if (e.pointerType === 'mouse') dragEnd(); });
    svg.addEventListener('pointercancel', e => { if (e.pointerType === 'mouse') dragEnd(); });

    // Doigts : un doigt posé sur le globe le fait tourner (ailleurs, la page défile) ; deux doigts zooment
    let pinch = null;
    const dist = T => Math.hypot(T[1].clientX - T[0].clientX, T[1].clientY - T[0].clientY);
    svg.addEventListener('touchstart', e => {
        const T = e.touches;
        if (T.length >= 2) {
            e.preventDefault(); dragEnd();
            const mx = (T[0].clientX + T[1].clientX) / 2, my = (T[0].clientY + T[1].clientY) / 2;
            pinch = { d: dist(T), k: cam.k }; dragStart(mx, my);
        } else if (T.length === 1) {
            const q = toSvg(T[0].clientX, T[0].clientY); frame();
            if (Math.hypot(q[0] - C, q[1] - C) <= R) { e.preventDefault(); dragStart(T[0].clientX, T[0].clientY); }
        }
    }, { passive: false });
    svg.addEventListener('touchmove', e => {
        const T = e.touches;
        if (pinch && T.length >= 2) {
            e.preventDefault();
            cam.k = Math.max(K_MIN, Math.min(K_MAX, pinch.k * dist(T) / pinch.d));
            dragMove((T[0].clientX + T[1].clientX) / 2, (T[0].clientY + T[1].clientY) / 2);
        } else if (drag && T.length === 1) { e.preventDefault(); dragMove(T[0].clientX, T[0].clientY); }
    }, { passive: false });
    svg.addEventListener('touchend', e => {
        if (e.touches.length < 2) pinch = null;
        if (e.touches.length === 0) dragEnd();
        else if (e.touches.length === 1 && drag) { drag = null; dragStart(e.touches[0].clientX, e.touches[0].clientY); }
    }, { passive: true });

    // Clavier (globe sélectionné) : flèches pour tourner, + − pour zoomer, 0 pour revenir au départ
    svg.addEventListener('keydown', e => {
        const k = { ArrowLeft: [-15, 0, 1], ArrowRight: [15, 0, 1], ArrowUp: [0, 10, 1], ArrowDown: [0, -10, 1], '+': [0, 0, 1.6], '=': [0, 0, 1.6], '-': [0, 0, 1 / 1.6] }[e.key];
        if (e.key === '0') { e.preventDefault(); touched(); goTo(PRESETS[current]); return; }
        if (!k) return;
        e.preventDefault(); touched(); goTo({ lon: cam.lon + k[0], lat: cam.lat + k[1], k: cam.k * k[2] }, 300);
    });

    /* ───────── Données en direct ───────── */
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
        // Globe : pays avec visiteurs en vert, repères (visiteurs verts, commandes bleues) au centre des pays
        svg.querySelectorAll('.lv-countries [data-iso]').forEach(p => p.classList.toggle('is-live', !!d.map.visitors[p.dataset.iso]));
        const r = PIN_R, g = (r * 1.2).toFixed(1);
        const pin = (iso, n, color, dx) => !byIso[iso] || !n ? '' : '<g class="lv-pin" data-iso="' + esc(iso) + '" data-dx="' + dx + '"><circle class="lv-pin-ring" r="' + r + '" stroke="' + color + '"/><circle r="' + r + '" fill="' + color + '" stroke="#04070F" stroke-width="' + (r / 5.5).toFixed(1) + '"/><text class="lv-pin-n" style="font-size:' + r + 'px">' + n + '</text></g>';
        let pins = '';
        Object.entries(d.map.orders).forEach(([iso, n]) => { pins += pin(iso, n, '#3B82F6', d.map.visitors[iso] ? -g : 0); });
        Object.entries(d.map.visitors).forEach(([iso, n]) => { pins += pin(iso, n, '#22C55E', d.map.orders[iso] ? g : 0); });
        svg.querySelector('.lv-pins').innerHTML = pins;
        // Visiteurs sans pays connu
        const off = d.map.unplaced + Object.entries(d.map.visitors).reduce((a, [iso, n]) => a + (byIso[iso] ? 0 : n), 0);
        $('lvUnplaced').hidden = !off;
        $('lvUnplaced').textContent = off ? off + ' visiteur' + (off > 1 ? 's' : '') + ' sans lieu connu' : '';
        changed();
    }

    // Vues Afrique / Monde (choix gardé dans ce navigateur) ; « Monde » tourne tout seul
    function showView(key, animate) {
        current = key;
        document.querySelectorAll('.lv-tabs [data-tab]').forEach(b => b.setAttribute('aria-selected', String(b.dataset.tab === key)));
        try { localStorage.setItem('shopio_live_map', key); } catch (e) {}
        tip.hidden = true; idleUntil = 0;
        if (animate) goTo(PRESETS[key], 900); else { Object.assign(cam, PRESETS[key]); changed(); }
        setSpin(key === 'world');
    }
    document.querySelector('.lv-tabs').addEventListener('click', e => { const b = e.target.closest('[data-tab]'); if (b && b.dataset.tab !== current) showView(b.dataset.tab, true); });
    document.querySelector('.lv-tabs').addEventListener('keydown', e => {
        if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
        const next = current === 'africa' ? 'world' : 'africa';
        showView(next, true); document.querySelector('.lv-tabs [data-tab="' + next + '"]').focus();
    });

    // Info-bulle au survol d'un pays
    const tip = $('lvTip'), mapBox = document.querySelector('.lv-map');
    mapBox.addEventListener('mousemove', e => {
        const p = e.target.closest('.lv-countries [data-iso]'); if (!p || drag) { tip.hidden = true; return; }
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
    measure();
    render(data, true);
    showView(current, false);
    setInterval(refresh, 10000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
})();
</script>
@endpush
