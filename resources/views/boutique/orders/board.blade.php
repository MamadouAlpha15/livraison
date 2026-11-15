{{--
    resources/views/boutique/orders/board.blade.php
    Route     : GET /boutique/orders → Boutique\OrderBoardController@index → name('boutique.orders.index')
    Rôle      : Commandes → « Toutes les commandes » (façon Sellio) : étapes avec compteurs, recherche, filtres
                (source, produit), dates, commandes groupées par jour, fiche en fenêtre (articles, client,
                Appeler / WhatsApp / SMS, copier, actions, notes), nouvelle commande, réglages du message WhatsApp.
                Données en direct : la liste et les compteurs se mettent à jour toutes les 30 s.
    Variables : $shop, $groups, $page, $counts, $stage, $period, $search, $source, $productId, $settings, $devise,
                $sources, $products, $communes, $cartsWaiting
--}}
@extends('layouts.app')
@section('title', 'Commandes · ' . $shop->name)
@php
    use App\Support\OrderBoard as OB;
    use App\Http\Controllers\Boutique\OrderBoardController as OBC;
    $bodyClass = 'is-dashboard';
    $qs = fn (array $over) => route('boutique.orders.index', array_filter(array_merge(['etape' => $stage !== 'tous' ? $stage : null, 'periode' => $period !== 'all' ? $period : null, 'q' => $search ?: null, 'source' => $source ?: null, 'produit' => $productId ?: null], $over), fn ($v) => $v !== null && $v !== ''));
    $obData = [
        'stages' => collect(OB::STAGES)->map(fn ($s) => ['label' => $s[0], 'color' => $s[3]]),
        'actions' => OB::ACTIONS, 'products' => $products, 'communes' => $communes, 'currency' => $devise,
        'next' => $page->hasMorePages() ? $page->nextPageUrl() . '&partial=1' : null,
        'live' => request()->fullUrlWithQuery(['partial' => 1, 'page' => 1]),
        'wa' => $settings['orders']['wa'],
        'urls' => ['store' => route('boutique.orders.store'), 'settings' => route('boutique.orders.settings')],
    ];
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.ob { padding: 18px 28px 80px; max-width: 1240px; width: 100%; min-width: 0; }
.ob > * { min-width: 0; }
.ob-top { display: flex; align-items: center; gap: 10px; padding: 12px 28px !important; position: sticky; top: 0; z-index: 30; }
.ob-tabs { display: flex; gap: 22px; border-bottom: 1px solid var(--border); margin-bottom: 18px; }
.ob-tabs a { padding: 10px 2px; font-weight: 700; font-size: 14px; color: var(--text-2); text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; display: inline-flex; align-items: center; gap: 6px; }
.ob-tabs a[aria-current="page"] { color: var(--text); border-bottom-color: var(--text); }
.ob-tabs small { min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: #F59E0B; color: #16162A; font-size: 11.5px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
.ob-head { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
.ob-head h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.02em; }
.ob-live { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--text-2); font-weight: 600; }
.ob-live i { width: 8px; height: 8px; border-radius: 50%; background: #22C55E; box-shadow: 0 0 0 0 rgba(34,197,94,.6); animation: obPulse 2s infinite; }
@keyframes obPulse { 70% { box-shadow: 0 0 0 7px rgba(34,197,94,0); } 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); } }
.ob-head-actions { margin-left: auto; display: flex; gap: 8px; flex-wrap: wrap; }
.ob-btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 14px; border: 0; border-radius: 10px; font: 700 13.5px var(--font); cursor: pointer; text-decoration: none; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); transition: transform 150ms, box-shadow 150ms, background-color 150ms; }
.ob-btn:hover { box-shadow: inset 0 0 0 1px #9DB4D6; }
.ob-btn:active { transform: scale(.98); }
.ob-btn:disabled { opacity: .55; cursor: not-allowed; }
.ob-btn-main { background: var(--sv-btn); color: #fff; box-shadow: none; }
.ob-btn-main:hover { background: var(--sv-btn-h); box-shadow: none; color: #fff; }
.ob-chips { display: flex; gap: 4px; padding: 4px; border-radius: 12px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); overflow-x: auto; scrollbar-width: none; margin-bottom: 12px; }
.ob-chips::-webkit-scrollbar { display: none; }
.ob-chips a { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px; min-height: 34px; padding: 0 12px; border-radius: 9px; font-size: 13px; font-weight: 700; color: var(--text-2); text-decoration: none; white-space: nowrap; }
.ob-chips a small { font-size: 11.5px; color: var(--muted, #8A93A6); font-weight: 700; }
.ob-chips a[aria-current="page"] { background: var(--bg); color: var(--text); box-shadow: 0 1px 2px rgba(0,0,0,.08), inset 0 0 0 1px var(--border); }
.ob-tools { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; position: relative; }
.ob-search { flex: 1 1 280px; display: flex; align-items: center; gap: 8px; min-height: 42px; padding: 0 12px; border-radius: 10px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); }
.ob-search input { flex: 1; min-width: 0; border: 0; background: none; color: var(--text); font: 500 14px var(--font); }
.ob-search input:focus { outline: none; }
.ob-search:focus-within { box-shadow: inset 0 0 0 2px var(--sv-blue); }
.ob-select { min-height: 42px; padding: 0 12px; border: 0; border-radius: 10px; background: var(--surface); color: var(--text); font: 600 13.5px var(--font); box-shadow: inset 0 0 0 1px var(--border); }
.ob-filters { position: absolute; right: 0; top: 48px; z-index: 40; width: min(320px, 100%); display: grid; gap: 10px; padding: 14px; border-radius: 14px; background: var(--surface); box-shadow: 0 20px 40px -12px rgba(11,30,61,.3), inset 0 0 0 1px var(--border); }
.ob-filters label { display: grid; gap: 5px; font-size: 12.5px; font-weight: 700; color: var(--text-2); }
.ob-day { margin: 18px 0 8px; font-size: 11.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--text-2); }
.ob-card { display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; margin-bottom: 8px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); cursor: pointer; transition: box-shadow 160ms, transform 160ms; }
.ob-card:hover { box-shadow: inset 0 0 0 1px #9DB4D6, 0 6px 18px -10px rgba(11,30,61,.25); }
.ob-card:focus-visible { outline: 3px solid var(--sv-sky, #7DB4FF); outline-offset: 2px; }
.ob-card.is-new { animation: obNew 2.2s ease; }
@keyframes obNew { 0% { box-shadow: inset 0 0 0 2px #22C55E; } 100% { box-shadow: inset 0 0 0 1px var(--border); } }
.ob-card-main { flex: 1; min-width: 0; }
.ob-name { display: block; font-size: 15px; margin-bottom: 8px; }
.ob-meta { display: flex; gap: 6px 28px; flex-wrap: wrap; margin: 0; }
.ob-meta div { display: grid; gap: 2px; }
.ob-meta dt { font-size: 11.5px; color: var(--text-2); font-weight: 600; }
.ob-meta dd { margin: 0; font-size: 13.5px; font-weight: 700; }
.ob-amount { color: #16A34A; }
.ob-ref { display: block; margin-top: 8px; font-size: 12px; color: var(--text-2); }
.ob-card-side { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
.ob-pill { display: inline-flex; align-items: center; gap: 6px; min-height: 30px; padding: 0 10px; border: 0; border-radius: 999px; font: 800 12px var(--font); cursor: pointer; }
.is-amber { background: #FEF3C7; color: #92400E; } .is-blue { background: #DBEAFE; color: #1D4ED8; } .is-green { background: #DCFCE7; color: #15803D; }
.is-red { background: #FEE2E2; color: #B91C1C; } .is-grey { background: #EEF0F4; color: #475467; } .is-violet { background: #EDE9FE; color: #6D28D9; }
.is-sky { background: #E0F2FE; color: #0369A1; } .is-brown { background: #F5EBDD; color: #7C4A1E; }
.ob-kebab { width: 34px; height: 34px; border: 0; border-radius: 9px; background: none; color: var(--text-2); cursor: pointer; display: grid; place-items: center; }
.ob-kebab:hover { background: var(--bg); }
.ob-menu { position: fixed; z-index: 1200; min-width: 190px; padding: 6px; border-radius: 12px; background: var(--surface); box-shadow: 0 20px 40px -12px rgba(11,30,61,.35), inset 0 0 0 1px var(--border); }
.ob-menu button, .ob-menu a { display: flex; align-items: center; gap: 8px; width: 100%; min-height: 36px; padding: 0 10px; border: 0; border-radius: 8px; background: none; color: var(--text); font: 600 13.5px var(--font); text-align: left; cursor: pointer; text-decoration: none; }
.ob-menu button:hover, .ob-menu a:hover { background: var(--bg); }
.ob-menu i { width: 9px; height: 9px; border-radius: 50%; }
.ob-more { display: flex; justify-content: center; margin-top: 14px; }
.ob-empty { display: grid; place-items: center; gap: 6px; text-align: center; padding: 60px 20px; border-radius: 16px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); color: var(--text-2); }
.ob-empty b { color: var(--text); }
/* Fenêtres */
.ob-modal { position: fixed; inset: 0; z-index: 1100; display: grid; place-items: center; padding: 16px; background: rgba(11,18,32,.55); }
.ob-modal[hidden] { display: none; }
.ob-sheet { position: relative; width: min(460px, 100%); max-height: calc(100vh - 32px); overflow-y: auto; border-radius: 18px; background: var(--surface); padding: 20px; box-shadow: 0 30px 60px -20px rgba(0,0,0,.5); animation: obIn 220ms cubic-bezier(.22,1,.36,1); }
.ob-sheet.is-wide { width: min(560px, 100%); }
@keyframes obIn { from { transform: translateY(10px) scale(.98); opacity: 0; } }
.ob-x { position: absolute; right: 12px; top: 12px; width: 34px; height: 34px; border: 0; border-radius: 9px; background: none; color: var(--text-2); font-size: 20px; cursor: pointer; }
.ob-x:hover { background: var(--bg); }
.ob-sheet h2 { margin: 0; font-size: 20px; }
.ob-sub { margin: 2px 0 0; color: var(--text-2); font-size: 12.5px; }
.ob-sec { margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border); display: grid; gap: 10px; }
.ob-sec h3 { margin: 0; font-size: 13.5px; }
.ob-item { display: flex; align-items: center; gap: 10px; }
.ob-item i { width: 40px; height: 40px; border-radius: 9px; background: var(--bg) center / cover; flex-shrink: 0; box-shadow: inset 0 0 0 1px var(--border); }
.ob-item div { flex: 1; min-width: 0; }
.ob-item b { display: block; font-size: 13.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ob-item small { color: var(--text-2); font-size: 12px; }
.ob-item > span { font-weight: 800; font-size: 13.5px; white-space: nowrap; }
.ob-total { display: flex; justify-content: space-between; font-weight: 800; font-size: 15px; }
.ob-client { display: grid; gap: 10px; padding: 12px; border-radius: 12px; background: var(--bg); }
.ob-client-hd { display: flex; align-items: center; gap: 10px; }
.ob-client-hd i { width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; background: var(--surface); font-style: normal; font-weight: 800; box-shadow: inset 0 0 0 1px var(--border); }
.ob-client-hd a { color: var(--sv-btn); font-size: 13px; font-weight: 700; text-decoration: none; }
.ob-kv small { display: block; font-size: 11.5px; color: var(--text-2); font-weight: 600; }
.ob-kv span { font-size: 13.5px; font-weight: 600; }
.ob-contact { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.ob-contact .ob-call { grid-column: 1 / -1; background: var(--sv-btn); color: #fff; box-shadow: none; }
.ob-contact .ob-wa { background: #25D366; color: #fff; box-shadow: none; }
.ob-copy { grid-column: 1 / -1; }
.ob-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.ob-actions button { min-height: 38px; border: 0; border-radius: 10px; font: 700 13px var(--font); cursor: pointer; box-shadow: inset 0 0 0 1px currentColor; background: transparent; }
.ob-actions button[aria-pressed="true"] { box-shadow: none; }
.ob-actions .is-amber { color: #B45309; } .ob-actions .is-blue { color: #1D4ED8; } .ob-actions .is-green { color: #15803D; } .ob-actions .is-red { color: #B91C1C; }
.ob-actions .is-grey { color: #475467; } .ob-actions .is-violet { color: #6D28D9; } .ob-actions .is-sky { color: #0369A1; } .ob-actions .is-brown { color: #7C4A1E; }
.ob-actions button[aria-pressed="true"].is-blue { background: #DBEAFE; } .ob-actions button[aria-pressed="true"].is-green { background: #DCFCE7; }
.ob-actions button[aria-pressed="true"].is-red { background: #FEE2E2; } .ob-actions button[aria-pressed="true"].is-grey { background: #EEF0F4; }
.ob-actions button[aria-pressed="true"].is-violet { background: #EDE9FE; } .ob-actions button[aria-pressed="true"].is-sky { background: #E0F2FE; }
.ob-sched { display: flex; gap: 8px; align-items: end; flex-wrap: wrap; padding: 10px; border-radius: 10px; background: #F5F3FF; }
.ob-input, .ob-area { width: 100%; min-height: 42px; padding: 9px 12px; border: 0; border-radius: 10px; background: var(--bg); color: var(--text); font: 500 14px var(--font); box-shadow: inset 0 0 0 1px var(--border); }
.ob-area { min-height: 84px; resize: vertical; line-height: 1.5; }
.ob-input:focus, .ob-area:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue); }
.ob-lbl { display: block; font-size: 12.5px; font-weight: 700; margin-bottom: 5px; color: var(--text-2); }
.ob-msg { font-size: 12.5px; font-weight: 700; color: var(--text-2); min-height: 1em; }
.ob-msg.is-err { color: #B91C1C; } .ob-msg.is-ok { color: #15803D; }
.ob-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.ob-line { display: grid; grid-template-columns: minmax(0, 1fr) 80px auto; gap: 8px; align-items: center; }
.ob-toast { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%); z-index: 1300; padding: 10px 16px; border-radius: 10px; background: #0B1E3D; color: #fff; font-weight: 700; font-size: 13.5px; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
@media (max-width: 900px) { .ob { padding: 14px 14px 80px; } .ob-top { padding: 10px 14px !important; } }
@media (max-width: 560px) {
    .ob-head h1 { font-size: 22px; }
    .ob-head-actions { width: 100%; margin-left: 0; }
    .ob-head-actions .ob-btn { flex: 1; }
    .ob-card { flex-direction: column; }
    .ob-card-side { width: 100%; justify-content: space-between; }
    .ob-grid2 { grid-template-columns: 1fr; }
    .ob-sheet { padding: 16px; }
}
@media (prefers-reduced-motion: reduce) { .ob-live i, .ob-card.is-new, .ob-sheet { animation: none; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'orders'])
    <main class="main vs-main">
        <div class="topbar ob-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
            <span style="font-weight:800">Commandes</span>
        </div>
        <div class="ob">
            <nav class="ob-tabs" aria-label="Commandes">
                <a href="{{ route('boutique.orders.index') }}" aria-current="page">Toutes les commandes</a>
                <a href="{{ route('boutique.carts.index') }}">Paniers abandonnés @if($cartsWaiting)<small id="obCartsBadge">{{ $cartsWaiting }}</small>@endif</a>
            </nav>

            <div class="ob-head">
                <h1>Commandes</h1>
                <span class="ob-live" title="La liste se met à jour toute seule"><i aria-hidden="true"></i>Données en direct</span>
                <div class="ob-head-actions">
                    <a class="ob-btn" href="{{ route('boutique.orders.livraisons') }}" title="Assigner un livreur ou une entreprise de livraison">🛵 Livraisons</a>
                    <button type="button" class="ob-btn" id="obSettingsBtn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1A1.7 1.7 0 0 0 9 19.4a1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>Réglages</button>
                    <button type="button" class="ob-btn ob-btn-main" id="obNewBtn">+ Nouvelle commande</button>
                </div>
            </div>

            <nav class="ob-chips" aria-label="Étapes" id="obChips">
                <a href="{{ $qs(['etape' => null]) }}" @if($stage === 'tous') aria-current="page" @endif>Tous <small data-count="tous">{{ $counts['tous'] }}</small></a>
                @foreach(OB::STAGES as $k => [$label])
                <a href="{{ $qs(['etape' => $k]) }}" @if($stage === $k) aria-current="page" @endif>{{ $label }} <small data-count="{{ $k }}">{{ $counts[$k] }}</small></a>
                @endforeach
            </nav>

            <form class="ob-tools" method="GET" action="{{ route('boutique.orders.index') }}" id="obTools">
                @if($stage !== 'tous')<input type="hidden" name="etape" value="{{ $stage }}">@endif
                <label class="ob-search"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><span class="sm-sr" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Rechercher</span><input type="search" name="q" value="{{ $search }}" placeholder="Rechercher une commande, un client ou un produit…" id="obQ"></label>
                <button type="button" class="ob-btn" id="obFilterBtn" aria-expanded="false" aria-controls="obFilters"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18l-7 8v6l-4-2v-4z"/></svg>Filtres @if($source || $productId)<span class="ob-pill is-blue" style="min-height:20px;padding:0 7px">{{ ($source ? 1 : 0) + ($productId ? 1 : 0) }}</span>@endif</button>
                <label><span class="sm-sr" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Période</span><select name="periode" class="ob-select" onchange="this.form.submit()">@foreach(OBC::PERIODS as $k => $l)<option value="{{ $k }}" @selected($period === $k)>📅 {{ $l }}</option>@endforeach</select></label>
                <div class="ob-filters" id="obFilters" hidden>
                    <label>Source<select name="source" class="ob-select"><option value="">Toutes les sources</option>@foreach($sources as $s)<option value="{{ $s['value'] }}" @selected($source === $s['value'])>{{ $s['label'] }}</option>@endforeach</select></label>
                    <label>Produit<select name="produit" class="ob-select"><option value="">Tous les produits</option>@foreach($products as $p)<option value="{{ $p['id'] }}" @selected($productId === $p['id'])>{{ $p['name'] }}</option>@endforeach</select></label>
                    <div style="display:flex;gap:8px"><a class="ob-btn" style="flex:1" href="{{ $qs(['source' => null, 'produit' => null]) }}">Effacer</a><button type="submit" class="ob-btn ob-btn-main" style="flex:1">Appliquer</button></div>
                </div>
            </form>

            <div id="obList">@include('boutique.orders._cards')</div>
            <div class="ob-more"><button type="button" class="ob-btn" id="obMore" @if(!$page->hasMorePages()) hidden @endif>Voir plus de commandes</button></div>
        </div>
    </main>
</div>

{{-- Fiche d'une commande --}}
<div class="ob-modal" id="obOrder" hidden role="dialog" aria-modal="true" aria-labelledby="obOrderH"><div class="ob-sheet" id="obOrderSheet"></div></div>

{{-- Nouvelle commande --}}
<div class="ob-modal" id="obNew" hidden role="dialog" aria-modal="true" aria-labelledby="obNewH">
    <form class="ob-sheet is-wide" id="obNewForm" novalidate>
        <button type="button" class="ob-x" data-close aria-label="Fermer">×</button>
        <h2 id="obNewH">Nouvelle commande</h2>
        <p class="ob-sub">Une commande reçue par téléphone, WhatsApp ou en boutique. Payée à la livraison.</p>
        <div class="ob-sec">
            <div class="ob-grid2">
                <div><label class="ob-lbl" for="nnName">Nom du client</label><input class="ob-input" id="nnName" maxlength="120" required></div>
                <div><label class="ob-lbl" for="nnPhone">Téléphone</label><input class="ob-input" id="nnPhone" inputmode="tel" maxlength="16" placeholder="622 12 34 56" required></div>
                <div><label class="ob-lbl" for="nnCommune">Commune</label><select class="ob-input" id="nnCommune" required><option value="">Choisir…</option>@foreach($communes as $c)<option>{{ $c }}</option>@endforeach</select></div>
                <div><label class="ob-lbl" for="nnLandmark">Adresse</label><input class="ob-input" id="nnLandmark" maxlength="120" placeholder="Quartier, rue, repère"></div>
            </div>
        </div>
        <div class="ob-sec">
            <h3>Produits</h3>
            <div id="nnLines" style="display:grid;gap:8px"></div>
            <button type="button" class="ob-btn" id="nnAdd" style="justify-self:start">+ Ajouter un produit</button>
            <div class="ob-total"><span>Total estimé</span><span id="nnTotal">0 {{ $devise }}</span></div>
            <p class="ob-sub" style="margin:0">Les packs du produit s’appliquent automatiquement.</p>
        </div>
        <p class="ob-msg" id="nnMsg" role="alert"></p>
        <button type="submit" class="ob-btn ob-btn-main" style="width:100%" id="nnSend">Créer la commande</button>
    </form>
</div>

{{-- Réglages : message WhatsApp de confirmation --}}
<div class="ob-modal" id="obSettings" hidden role="dialog" aria-modal="true" aria-labelledby="obSetH">
    <form class="ob-sheet" id="obSetForm" novalidate>
        <button type="button" class="ob-x" data-close aria-label="Fermer">×</button>
        <h2 id="obSetH">Réglages des commandes</h2>
        <p class="ob-sub">Le message pré-rempli quand vous touchez « WhatsApp » ou « SMS » sur une commande.</p>
        <div class="ob-sec">
            <label class="ob-lbl" for="obWa">Message de confirmation</label>
            <textarea class="ob-area" id="obWa" maxlength="600" style="min-height:150px">{{ $settings['orders']['wa'] }}</textarea>
            <p class="ob-sub">Remplacés automatiquement : <code>{prenom}</code> le prénom du client, <code>{numero}</code> le numéro de commande, <code>{boutique}</code> votre boutique, <code>{produits}</code> les produits, <code>{total}</code> le total.</p>
            <p class="ob-sub">Les paniers abandonnés ont leur propre message : <a href="{{ route('boutique.carts.settings') }}">Réglages des paniers abandonnés</a>.</p>
        </div>
        <p class="ob-msg" id="obSetMsg" role="alert"></p>
        <button type="submit" class="ob-btn ob-btn-main" style="width:100%">Enregistrer</button>
    </form>
</div>
<div class="ob-toast" id="obToast" hidden role="status"></div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const G = @json($obData);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    // Fenêtres et messages au niveau de la page (pas dans un conteneur animé) : fond sombre sur tout l'écran
    document.querySelectorAll('.ob-modal, #obToast').forEach(el => document.body.appendChild(el));
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const money = n => Math.round(Number(n) || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + G.currency;
    const post = async (url, body, method) => {
        const r = await fetch(url, { method: method || 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body || {}) });
        const j = await r.json().catch(() => ({}));
        return { ok: r.ok, status: r.status, j };
    };
    let toastT;
    const toast = t => { const el = $('obToast'); el.textContent = t; el.hidden = false; clearTimeout(toastT); toastT = setTimeout(() => { el.hidden = true; }, 2400); };
    const cardData = el => JSON.parse(el.closest('[data-order]').dataset.order);

    /* ───────── Fenêtres ───────── */
    let lastFocus = null;
    function openModal(id) { lastFocus = document.activeElement; $(id).hidden = false; document.body.style.overflow = 'hidden'; setTimeout(() => { const f = $(id).querySelector('input,textarea,select,button:not(.ob-x)'); if (f) f.focus(); }, 30); }
    function closeModal(id) { $(id).hidden = true; document.body.style.overflow = ''; if (lastFocus) lastFocus.focus(); }
    document.querySelectorAll('.ob-modal').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m || e.target.closest('[data-close]')) closeModal(m.id); });
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeMenu(); const m = [...document.querySelectorAll('.ob-modal')].find(x => !x.hidden); if (m) closeModal(m.id); } });

    /* ───────── Fiche d'une commande ───────── */
    let cur = null;
    function renderOrder(o) {
        cur = o;
        const st = G.stages[o.stage];
        $('obOrderSheet').innerHTML =
            '<button type="button" class="ob-x" data-close aria-label="Fermer">×</button>' +
            '<div style="display:flex;align-items:center;gap:10px;padding-right:40px"><h2 id="obOrderH">#' + o.id + '</h2><span class="ob-pill is-' + st.color + '">' + esc(st.label) + '</span></div>' +
            '<p class="ob-sub">' + esc(o.date) + (o.source ? ' · ' + esc(o.source) : '') + '</p>' +
            '<div class="ob-sec">' + o.items.map(i => '<div class="ob-item"><i' + (i.thumb ? ' style="background-image:url(\'' + esc(i.thumb) + '\')"' : '') + '></i><div><b>' + esc(i.name) + '</b><small>' + i.qty + ' × ' + esc(i.price) + '</small></div><span>' + esc(i.line) + '</span></div>').join('') +
                (o.fee ? '<div class="ob-total" style="font-weight:600;font-size:13.5px"><span>Livraison</span><span>' + esc(o.fee) + '</span></div>' : '') +
                '<div class="ob-total"><span>Total</span><span>' + esc(o.total) + '</span></div></div>' +
            '<div class="ob-sec"><div class="ob-client"><div class="ob-client-hd"><i aria-hidden="true">' + esc(o.name.charAt(0).toUpperCase()) + '</i><div><b>' + esc(o.name) + '</b>' + (o.phone ? '<br><a href="' + esc(o.tel) + '">' + esc(o.phoneShow) + '</a>' : '') + '</div></div>' +
                (o.address ? '<div class="ob-kv"><small>Adresse</small><span>' + esc(o.address) + '</span></div>' : '') +
                (o.livreur ? '<div class="ob-kv"><small>Livraison</small><span>' + esc(o.livreur) + '</span></div>' : '') +
                (o.followupLabel && o.stage === 'programmee' ? '<div class="ob-kv"><small>Prévue le</small><span>' + esc(o.followupLabel) + '</span></div>' : '') + '</div>' +
                (o.tel ? '<div class="ob-contact"><a class="ob-btn ob-call" href="' + esc(o.tel) + '">📞 Appeler</a><a class="ob-btn ob-wa" href="' + esc(o.wa) + '" target="_blank" rel="noopener">WhatsApp</a><a class="ob-btn" href="' + esc(o.sms) + '">💬 SMS</a><button type="button" class="ob-btn ob-copy" data-copy-order>📋 Copier la commande</button></div>' : '<button type="button" class="ob-btn ob-copy" data-copy-order>📋 Copier la commande</button>') + '</div>' +
            '<div class="ob-sec"><h3>Actions</h3><div class="ob-actions">' + G.actions.map(k => '<button type="button" class="is-' + G.stages[k].color + '" data-stage="' + k + '" aria-pressed="' + (o.stage === k) + '">' + esc(G.stages[k].label) + '</button>').join('') +
                '<button type="button" class="is-' + G.stages.retournee.color + '" data-stage="retournee" aria-pressed="' + (o.stage === 'retournee') + '">' + esc(G.stages.retournee.label) + '</button><button type="button" class="is-' + G.stages.nouvelle.color + '" data-stage="nouvelle" aria-pressed="' + (o.stage === 'nouvelle') + '">Remettre « Non traitée »</button></div>' +
                '<div class="ob-sched" id="obSched" hidden><div style="flex:1;min-width:180px"><label class="ob-lbl" for="obSchedAt">Date et heure prévues</label><input class="ob-input" type="datetime-local" id="obSchedAt" value="' + esc(o.followupAt || '') + '"></div><button type="button" class="ob-btn ob-btn-main" id="obSchedOk">Programmer</button></div>' +
                '<p class="ob-msg" id="obStageMsg" role="alert"></p>' +
                '<a class="ob-btn" href="' + esc(o.urls.logistics) + '" style="justify-self:start">🛵 Assigner un livreur</a></div>' +
            '<div class="ob-sec"><h3>Notes</h3><textarea class="ob-area" id="obNote" maxlength="2000" placeholder="Ajouter une note… (visible par la boutique seulement)">' + esc(o.note) + '</textarea><p class="ob-msg" id="obNoteMsg" aria-live="polite"></p></div>';
    }
    function openOrder(o) { renderOrder(o); openModal('obOrder'); }
    $('obList').addEventListener('click', e => {
        if (e.target.closest('[data-stage-menu]')) { openStageMenu(e.target.closest('[data-stage-menu]')); return; }
        if (e.target.closest('[data-kebab]')) { openKebab(e.target.closest('[data-kebab]')); return; }
        const card = e.target.closest('[data-order]'); if (card) openOrder(cardData(card));
    });
    $('obList').addEventListener('keydown', e => { if ((e.key === 'Enter' || e.key === ' ') && e.target.matches('[data-order]')) { e.preventDefault(); openOrder(cardData(e.target)); } });

    async function setStage(o, stage, at) {
        const r = await post(o.urls.stage, { stage: stage, at: at || null });
        if (!r.ok) return { error: (r.j.errors && Object.values(r.j.errors)[0][0]) || r.j.message || 'Le statut n’a pas pu être changé.' };
        const n = r.j.order;
        const card = document.querySelector('.ob-card[data-id="' + n.id + '"]');
        if (card) { card.dataset.order = JSON.stringify(n); const pill = card.querySelector('[data-stage-menu]'); pill.className = 'ob-pill is-' + G.stages[n.stage].color; pill.querySelector('span').textContent = G.stages[n.stage].label; }
        toast('Commande #' + n.id + ' : ' + G.stages[n.stage].label);
        refresh();
        return { order: n };
    }
    $('obOrderSheet').addEventListener('click', async e => {
        const b = e.target.closest('[data-stage]');
        if (b && cur) {
            if (b.dataset.stage === 'programmee') { $('obSched').hidden = false; $('obSchedAt').focus(); return; }
            b.disabled = true;
            const r = await setStage(cur, b.dataset.stage);
            b.disabled = false;
            if (r.error) { $('obStageMsg').textContent = r.error; $('obStageMsg').className = 'ob-msg is-err'; } else renderOrder(r.order);
            return;
        }
        if (e.target.id === 'obSchedOk' && cur) {
            const at = $('obSchedAt').value;
            const r = await setStage(cur, 'programmee', at);
            if (r.error) { $('obStageMsg').textContent = r.error; $('obStageMsg').className = 'ob-msg is-err'; } else renderOrder(r.order);
            return;
        }
        if (e.target.closest('[data-copy-order]') && cur) copy(cur.copy);
    });
    async function copy(t) { try { await navigator.clipboard.writeText(t); toast('Commande copiée'); } catch (x) { window.prompt('Copiez la commande :', t); } }
    // Notes : enregistrées toutes seules
    let noteT;
    $('obOrderSheet').addEventListener('input', e => {
        if (e.target.id !== 'obNote' || !cur) return;
        const o = cur, v = e.target.value; $('obNoteMsg').textContent = '…';
        clearTimeout(noteT);
        noteT = setTimeout(async () => {
            const r = await post(o.urls.note, { note: v });
            const m = $('obNoteMsg'); if (!m) return;
            m.textContent = r.ok ? 'Note enregistrée ✓' : ((r.j.errors && r.j.errors.note && r.j.errors.note[0]) || 'Note non enregistrée.');
            m.className = 'ob-msg ' + (r.ok ? 'is-ok' : 'is-err');
            if (r.ok) { o.note = v; const card = document.querySelector('.ob-card[data-id="' + o.id + '"]'); if (card) card.dataset.order = JSON.stringify(o); }
        }, 700);
    });

    /* ───────── Menus (étape, plus d'actions) ───────── */
    let menu = null;
    function closeMenu() { if (menu) { menu.remove(); menu = null; } }
    function place(m, btn) { document.body.appendChild(m); const r = btn.getBoundingClientRect(); const w = m.offsetWidth, h = m.offsetHeight; m.style.left = Math.max(8, Math.min(window.innerWidth - w - 8, r.right - w)) + 'px'; m.style.top = (r.bottom + h + 8 > window.innerHeight ? r.top - h - 6 : r.bottom + 6) + 'px'; menu = m; m.querySelector('button,a')?.focus(); }
    function openStageMenu(btn) {
        closeMenu();
        const o = cardData(btn), m = document.createElement('div');
        m.className = 'ob-menu'; m.setAttribute('role', 'menu');
        m.innerHTML = Object.entries(G.stages).map(([k, s]) => '<button type="button" role="menuitem" data-pick="' + k + '"' + (k === o.stage ? ' aria-current="true" style="font-weight:800"' : '') + '><i class="is-' + s.color + '" style="background:currentColor"></i>' + esc(s.label) + '</button>').join('');
        m.addEventListener('click', async e => {
            const p = e.target.closest('[data-pick]'); if (!p) return;
            closeMenu();
            if (p.dataset.pick === 'programmee') { openOrder(o); $('obSched').hidden = false; return; }
            const r = await setStage(o, p.dataset.pick);
            if (r.error) toast(r.error);
        });
        place(m, btn);
    }
    function openKebab(btn) {
        closeMenu();
        const o = cardData(btn), m = document.createElement('div');
        m.className = 'ob-menu'; m.setAttribute('role', 'menu');
        m.innerHTML = '<button type="button" role="menuitem" data-k="open">Ouvrir la commande</button>' + (o.tel ? '<a role="menuitem" href="' + esc(o.tel) + '">📞 Appeler</a><a role="menuitem" href="' + esc(o.wa) + '" target="_blank" rel="noopener">WhatsApp</a>' : '') + '<button type="button" role="menuitem" data-k="copy">📋 Copier la commande</button><a role="menuitem" href="' + esc(o.urls.logistics) + '">🛵 Assigner un livreur</a>';
        m.addEventListener('click', e => { const k = e.target.closest('[data-k]'); if (!k) { closeMenu(); return; } closeMenu(); if (k.dataset.k === 'open') openOrder(o); if (k.dataset.k === 'copy') copy(o.copy); });
        place(m, btn);
    }
    document.addEventListener('click', e => { if (menu && !menu.contains(e.target) && !e.target.closest('[data-stage-menu],[data-kebab]')) closeMenu(); });
    window.addEventListener('scroll', closeMenu, { passive: true });

    /* ───────── Filtres, recherche ───────── */
    $('obFilterBtn').addEventListener('click', () => { const f = $('obFilters'); f.hidden = !f.hidden; $('obFilterBtn').setAttribute('aria-expanded', String(!f.hidden)); });
    let qT; $('obQ').addEventListener('input', () => { clearTimeout(qT); qT = setTimeout(() => $('obTools').submit(), 600); });

    /* ───────── Voir plus ───────── */
    let next = G.next;
    $('obMore').addEventListener('click', async () => {
        if (!next) return;
        const b = $('obMore'); b.disabled = true;
        const r = await fetch(next, { headers: { 'Accept': 'application/json' } }).then(x => x.json()).catch(() => null);
        b.disabled = false;
        if (!r) return;
        const tmp = document.createElement('div'); tmp.innerHTML = r.html;
        tmp.querySelectorAll('.ob-empty').forEach(x => x.remove());
        // Le jour déjà affiché n'est pas répété
        const lastDay = [...$('obList').querySelectorAll('.ob-day')].pop();
        const firstDay = tmp.querySelector('.ob-day');
        if (lastDay && firstDay && lastDay.textContent === firstDay.textContent) firstDay.remove();
        $('obList').append(...tmp.childNodes);
        next = r.next; b.hidden = !next;
    });

    /* ───────── Données en direct (toutes les 30 s) ───────── */
    async function refresh() {
        try {
            const r = await fetch(G.live, { headers: { 'Accept': 'application/json' } }).then(x => x.json());
            Object.entries(r.counts).forEach(([k, v]) => { const el = document.querySelector('[data-count="' + k + '"]'); if (el) el.textContent = v; });
            const badge = $('obCartsBadge'); if (badge) badge.textContent = r.cartsWaiting;
            // La liste n'est remplacée que si rien n'est ouvert et que l'on est en haut de la première page
            const busy = [...document.querySelectorAll('.ob-modal')].some(m => !m.hidden) || menu || document.activeElement === $('obQ') || $('obList').querySelectorAll('.ob-card').length > 30;
            if (busy) return;
            const known = new Set([...document.querySelectorAll('.ob-card')].map(c => c.dataset.id));
            $('obList').innerHTML = r.html;
            document.querySelectorAll('.ob-card').forEach(c => { if (!known.has(c.dataset.id) && known.size) c.classList.add('is-new'); });
            next = r.next; $('obMore').hidden = !next;
        } catch (e) {}
    }
    setInterval(() => { if (!document.hidden) refresh(); }, 30000);

    /* ───────── Nouvelle commande ───────── */
    const lines = [];
    function lineHtml(i) {
        const l = lines[i];
        return '<div class="ob-line"><select class="ob-input" data-l="' + i + '" data-f="id" aria-label="Produit ' + (i + 1) + '"><option value="">Choisir un produit…</option>' + G.products.filter(p => !p.variants).map(p => '<option value="' + p.id + '"' + (+l.id === p.id ? ' selected' : '') + '>' + esc(p.name) + ' · ' + esc(money(p.price)) + (p.stock !== null ? ' · stock ' + p.stock : '') + (p.active ? '' : ' (brouillon)') + '</option>').join('') + '</select>' +
            '<input class="ob-input" type="number" min="1" max="99" value="' + l.qty + '" data-l="' + i + '" data-f="qty" aria-label="Quantité ' + (i + 1) + '"><button type="button" class="ob-kebab" data-ldel="' + i + '" aria-label="Retirer">×</button></div>';
    }
    function renderLines() {
        $('nnLines').innerHTML = lines.map((_, i) => lineHtml(i)).join('');
        const t = lines.reduce((s, l) => { const p = G.products.find(x => x.id === +l.id); return s + (p ? p.price * (+l.qty || 0) : 0); }, 0);
        $('nnTotal').textContent = money(t) + (lines.length ? ' (avant packs)' : '');
    }
    $('obNewBtn').addEventListener('click', () => { if (!lines.length) lines.push({ id: '', qty: 1 }); renderLines(); $('nnMsg').textContent = ''; openModal('obNew'); });
    $('nnAdd').addEventListener('click', () => { if (lines.length < 20) { lines.push({ id: '', qty: 1 }); renderLines(); } });
    $('nnLines').addEventListener('change', e => { const el = e.target; if (el.dataset.l === undefined) return; lines[+el.dataset.l][el.dataset.f] = el.dataset.f === 'qty' ? Math.max(1, Math.min(99, +el.value || 1)) : el.value; renderLines(); });
    $('nnLines').addEventListener('click', e => { const d = e.target.closest('[data-ldel]'); if (d) { lines.splice(+d.dataset.ldel, 1); renderLines(); } });
    $('obNewForm').addEventListener('submit', async e => {
        e.preventDefault();
        const msg = $('nnMsg'); msg.className = 'ob-msg is-err';
        const items = lines.filter(l => l.id).map(l => ({ id: +l.id, qty: +l.qty || 1 }));
        if ($('nnName').value.trim().length < 2) { msg.textContent = 'Indiquez le nom du client.'; $('nnName').focus(); return; }
        if (!/^6\d{8}$/.test($('nnPhone').value.replace(/\D/g, '').replace(/^224/, ''))) { msg.textContent = 'Le numéro doit avoir 9 chiffres et commencer par 6.'; $('nnPhone').focus(); return; }
        if (!$('nnCommune').value) { msg.textContent = 'Choisissez la commune.'; $('nnCommune').focus(); return; }
        if (!items.length) { msg.textContent = 'Ajoutez au moins un produit.'; return; }
        const b = $('nnSend'); b.disabled = true; b.textContent = 'Création…';
        const r = await post(G.urls.store, { name: $('nnName').value.trim(), phone: $('nnPhone').value, commune: $('nnCommune').value, landmark: $('nnLandmark').value.trim(), items: items });
        b.disabled = false; b.textContent = 'Créer la commande';
        if (!r.ok) { msg.textContent = (r.j.errors && Object.values(r.j.errors)[0][0]) || r.j.message || 'La commande n’a pas pu être créée.'; return; }
        closeModal('obNew'); lines.length = 0; $('obNewForm').reset();
        toast('Commande #' + r.j.id + ' créée'); refresh();
    });

    /* ───────── Réglages ───────── */
    $('obSettingsBtn').addEventListener('click', () => { $('obSetMsg').textContent = ''; openModal('obSettings'); });
    $('obSetForm').addEventListener('submit', async e => {
        e.preventDefault();
        const r = await post(G.urls.settings, { wa: $('obWa').value }, 'PUT');
        const m = $('obSetMsg');
        m.textContent = r.ok ? 'Enregistré ✓ (les commandes affichées l’utiliseront après actualisation)' : ((r.j.errors && Object.values(r.j.errors)[0][0]) || 'Non enregistré.');
        m.className = 'ob-msg ' + (r.ok ? 'is-ok' : 'is-err');
    });
})();
</script>
@endpush
