{{--
    resources/views/vendeur/products/index.blade.php
    Route     : GET /products → Vendeur\ProductController@index → name('products.index')
    Design    : maquette « Espace vendeur » (Main.dc.html), version épurée : icônes à trait fin (App\Support\IconLibrary),
                filtres avec leurs nombres (Tous, Actifs, Inactifs, Rupture), recherche, grille de cartes produit
                (photo, nom, prix, catégorie · stock, interrupteur, Modifier, menu ⋯).
    Données   : toutes calculées (aucun chiffre inventé). Le menu ⋯ : Page de vente, Voir, Booster, Dupliquer, Supprimer.
    Variables : $products, $categories, $customCats, $devise, $totalProducts, $activeProducts, $outOfStock,
                $lowStock, $perPage, $isPro, $processedCount, $shop
--}}
@extends('layouts.app')

@section('title', 'Produits · ' . $shop->name)

@php
    use App\Support\IconLibrary as Ico;
    $bodyClass = 'is-dashboard';
    $freeMax   = 5; // Plan Gratuit : 5 produits
    $limitHit  = !$isPro && $totalProducts >= $freeMax;
    $status    = request('status');
    $stockF    = request('stock');
    $filtered  = request()->hasAny(['search', 'category', 'status', 'stock']);
    $inactive  = $totalProducts - $activeProducts;
    // Lien de la page en gardant les filtres en cours (null retire un filtre)
    $url = fn (array $over) => route('products.index', array_filter(array_merge([
        'search' => request('search'), 'category' => request('category'), 'status' => $status, 'stock' => $stockF,
        'par_page' => $perPage !== 12 ? $perPage : null,
    ], $over), fn ($v) => $v !== null && $v !== ''));
    $chips = [
        ['Tous', $totalProducts, $url(['status' => null, 'stock' => null]), !$status && !$stockF, null],
        ['Actifs', $activeProducts, $url(['status' => 'active', 'stock' => null]), $status === 'active' && !$stockF, 'kpiActive'],
        ['Inactifs', $inactive, $url(['status' => 'inactive', 'stock' => null]), $status === 'inactive' && !$stockF, 'kpiInactive'],
        ['Rupture', $outOfStock, $url(['stock' => 'out', 'status' => null]), $stockF === 'out', null],
    ];
    if ($lowStock) $chips[] = ['Stock faible', $lowStock, $url(['stock' => 'low', 'status' => null]), $stockF === 'low', null];
    $shareUrl  = \App\Support\Storefront::publicUrl($shop);
    $img       = fn (?string $path, string $size = 'medium') => $path ? (\App\Services\ImageOptimizer::url($path, $size) ?? asset('storage/' . $path)) : null;
    $gnf       = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' ' . $devise;
@endphp

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.num { font-variant-numeric: tabular-nums; }
.cp svg, .cp-menu svg, .cp-box svg { flex-shrink: 0; }

/* En-tête */
.cp-top { position: sticky; top: 0; z-index: 30; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 14px 28px !important; }
.cp-top h1 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
.cp-top-sub { font-size: 13px; color: var(--muted); }
.cp { padding: 22px 28px 48px; display: flex; flex-direction: column; gap: 18px; max-width: 1440px; width: 100%; min-width: 0; }

/* Boutons et champs (valeurs de la maquette) */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 42px; padding: 0 16px; border-radius: 999px; font: 700 14px/1 var(--font); text-decoration: none; border: 0; cursor: pointer; white-space: nowrap; transition: transform 180ms var(--sv-e-out), background-color 180ms var(--sv-e-out), box-shadow 180ms var(--sv-e-out), color 180ms var(--sv-e-out); }
.btn:active { transform: scale(.97); }
.btn-p { background: var(--sv-btn); color: #fff; }
.btn-p:hover { background: var(--sv-btn-h); color: #fff; box-shadow: 0 8px 20px rgba(22,104,216,.28); }
.btn-s { background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); }
.btn-s:hover { box-shadow: inset 0 0 0 1.5px var(--sv-blue); color: var(--sv-btn); }
.btn-g { background: transparent; color: var(--text-2); }
.btn-g:hover { background: var(--surface); color: var(--sv-btn); box-shadow: inset 0 0 0 1.5px var(--border); }
.btn-danger { background: #DC2626; color: #fff; }
.btn-danger:hover { background: #B91C1C; color: #fff; }
.btn.ico { width: 42px; padding: 0; }
.btn[aria-disabled="true"] { opacity: .45; pointer-events: none; }
.inp { height: 44px; width: 100%; padding: 0 16px 0 42px; border-radius: 999px !important; border: 0 !important; background: var(--surface); color: var(--text); font: 500 14.5px var(--font); box-shadow: inset 0 0 0 1.5px var(--border) !important; outline: 0; }
.inp:focus { box-shadow: inset 0 0 0 2px var(--sv-blue) !important; }
.sel { height: 44px; padding: 0 14px; border-radius: 999px !important; border: 0 !important; background: var(--surface); color: var(--text); font: 600 14px var(--font); box-shadow: inset 0 0 0 1.5px var(--border) !important; max-width: 240px; }
.cp a:focus-visible, .cp button:focus-visible, .cp input:focus-visible, .cp select:focus-visible { outline: 3px solid var(--sv-blue); outline-offset: 2px; }

/* Plan Gratuit : une ligne */
.cp-plan { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; font-size: 13.5px; color: var(--text-2); }
.cp-plan b { color: var(--text); }
.cp-meter { width: 120px; height: 6px; border-radius: 999px; background: var(--border); overflow: hidden; }
.cp-meter i { display: block; height: 100%; border-radius: inherit; background: var(--sv-blue); }
.cp-plan.is-full .cp-meter i { background: var(--sv-amber); }
.cp-plan a { color: var(--sv-btn); font-weight: 700; text-decoration: none; }
.cp-flash { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 16px; font-size: 14px; font-weight: 700; }
.cp-flash.is-ok { background: var(--sv-ok50); color: var(--sv-ok); }
.cp-flash.is-err { background: var(--sv-err50); color: var(--sv-err); }

/* Filtres avec nombres (maquette .chip) */
.cp-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.cp-chips { display: flex; gap: 6px; flex-wrap: wrap; }
.chip { display: inline-flex; align-items: center; gap: 7px; min-height: 38px; padding: 0 14px; border-radius: 999px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); color: var(--text-2); font: 700 13px var(--font); text-decoration: none; transition: background-color 180ms var(--sv-e-out), color 180ms var(--sv-e-out), box-shadow 180ms var(--sv-e-out); }
.chip span { opacity: .7; }
.chip:hover { color: var(--sv-btn); box-shadow: inset 0 0 0 1px var(--sv-blue); }
.chip[aria-current="page"] { background: var(--sv-btn); color: #fff; box-shadow: none; }
.chip[aria-current="page"] span { opacity: .85; }
.cp-search { position: relative; flex: 1 1 240px; }
.cp-search .ico { position: absolute; left: 15px; top: 13px; color: var(--muted); }

/* Cartes produit */
.g4 { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
.pcard { position: relative; background: var(--surface); border-radius: 20px; overflow: hidden; box-shadow: 0 0 0 1px var(--border); display: flex; flex-direction: column; min-width: 0; transition: transform 260ms var(--sv-e-out), box-shadow 260ms var(--sv-e-out); }
.pcard:hover { transform: translateY(-3px); box-shadow: 0 0 0 1px var(--border), 0 16px 32px -16px rgba(11,30,61,.28); }
.pcard-media { position: relative; display: block; aspect-ratio: 4 / 3; overflow: hidden; background: var(--sv-b50); }
.pcard-media .ph { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 500ms var(--sv-e-out), filter 260ms; }
.pcard:hover .ph { transform: scale(1.05); }
.pcard.is-off .ph { filter: grayscale(1); opacity: .7; }
.pcard-empty { width: 100%; height: 100%; display: grid; place-content: center; justify-items: center; gap: 6px; color: var(--sv-btn); font-size: 12.5px; font-weight: 700; text-decoration: none; opacity: .8; }
.pcard-tag { position: absolute; top: 10px; left: 10px; display: inline-flex; align-items: center; gap: 6px; height: 24px; padding: 0 9px; border-radius: 999px; background: rgba(255,255,255,.92); color: #45455E; font-size: 11.5px; font-weight: 800; backdrop-filter: blur(6px); }
.pcard-tag i { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.pcard-tag[hidden] { display: none; }
.pcard-photos { position: absolute; right: 10px; bottom: 10px; display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 9px; border: 0; border-radius: 999px; background: rgba(11,30,61,.66); color: #fff; font: 700 12px var(--font); cursor: zoom-in; backdrop-filter: blur(6px); }
.pcard-body { padding: 14px; display: flex; flex-direction: column; gap: 4px; flex: 1; }
.pcard-name { font-size: 15px; font-weight: 700; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: var(--text); text-decoration: none; }
.pcard-name:hover { color: var(--sv-btn); }
.pcard-price { font-size: 17px; font-weight: 800; letter-spacing: -.01em; white-space: nowrap; }
.pcard-price s { font-size: 12.5px; font-weight: 600; color: var(--muted); margin-left: 6px; }
.pcard-meta { font-size: 12.5px; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pcard-meta .is-low { color: var(--sv-warn); font-weight: 700; }
.pcard-meta .is-out { color: var(--sv-err); font-weight: 700; }
.pcard-foot { display: flex; align-items: center; gap: 6px; margin-top: auto; padding-top: 12px; }
.pcard-foot .btn { min-height: 36px; }
.pcard-foot .btn.ico { width: 36px; }
.sw { position: relative; flex-shrink: 0; width: 44px; height: 26px; margin-right: auto; border-radius: 999px; border: 0; cursor: pointer; padding: 0; transition: background-color 200ms var(--sv-e-out); background: #C3CBDA; }
.sw[aria-checked="true"] { background: var(--sv-btn); }
.sw span { position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 999px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,.25); transition: transform 260ms var(--sv-e-pop); }
.sw[aria-checked="true"] span { transform: translateX(18px); }
.sw:disabled { opacity: .6; cursor: wait; }

/* Vide, pages */
.cp-empty { grid-column: 1 / -1; padding: 56px 24px; display: flex; flex-direction: column; align-items: center; gap: 12px; text-align: center; border-radius: 24px; background: var(--surface); box-shadow: 0 0 0 1px var(--border); }
.cp-empty-ico { width: 64px; height: 64px; border-radius: 20px; display: grid; place-items: center; background: var(--sv-b50); color: var(--sv-btn); }
.cp-empty h2 { margin: 0; font-size: 19px; font-weight: 800; }
.cp-empty p { margin: 0; color: var(--text-2); max-width: 44ch; font-size: 14px; }
.cp-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; font-size: 13.5px; color: var(--muted); }

/* Menu ⋯, fenêtres, galerie */
.cp-menu { position: fixed; z-index: 1200; min-width: 220px; padding: 6px; border-radius: 16px; background: var(--surface); box-shadow: 0 22px 44px -14px rgba(11,30,61,.4), 0 0 0 1px var(--border); animation: cpPop 260ms var(--sv-e-out) both; }
.cp-menu a, .cp-menu button { display: flex; align-items: center; gap: 11px; width: 100%; min-height: 40px; padding: 0 12px; border: 0; border-radius: 11px; background: none; color: var(--text); font: 600 14px var(--font); text-align: left; text-decoration: none; cursor: pointer; }
.cp-menu a .ico, .cp-menu button .ico { color: var(--muted); }
.cp-menu a:hover, .cp-menu button:hover, .cp-menu a:focus-visible, .cp-menu button:focus-visible { background: var(--sv-b50); outline: none; }
.cp-menu a:hover .ico, .cp-menu button:hover .ico { color: var(--sv-btn); }
.cp-menu .is-del, .cp-menu .is-del .ico { color: var(--sv-err); }
.cp-menu hr { border: 0; border-top: 1px solid var(--border); margin: 5px 4px; }
.cp-scrim { position: fixed; inset: 0; z-index: 1100; display: none; background: rgba(11,30,61,.4); border: 0; }
.cp-scrim.open { display: block; animation: cpFade 240ms var(--sv-e-out) both; }
.cp-modal { position: fixed; inset: 0; z-index: 1101; display: none; place-items: center; padding: 16px; pointer-events: none; }
.cp-modal.open { display: grid; }
.cp-box { pointer-events: auto; width: min(420px, 100%); padding: 22px; display: flex; flex-direction: column; gap: 12px; border-radius: 24px; background: var(--surface); box-shadow: 0 30px 60px -20px rgba(0,0,0,.45); animation: cpPop 300ms var(--sv-e-out) both; }
.cp-box h2 { margin: 0; font-size: 19px; font-weight: 800; }
.cp-box p { margin: 0; color: var(--text-2); font-size: 14px; line-height: 1.5; }
.cp-box-ico { width: 44px; height: 44px; border-radius: 14px; display: grid; place-items: center; }
.cp-link { display: flex; align-items: center; gap: 10px; padding: 6px 6px 6px 14px; border-radius: 999px; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); }
.cp-link code { flex: 1; min-width: 0; font-size: 12.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cp-nets { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.cp-net { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 12px 4px; border-radius: 16px; color: var(--text-2); font-size: 12px; font-weight: 700; text-decoration: none; transition: background-color 160ms var(--sv-e-out), color 160ms; }
.cp-net:hover { background: var(--sv-b50); color: var(--sv-btn); }
.cp-net span { width: 42px; height: 42px; border-radius: 14px; display: grid; place-items: center; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); color: var(--text); }
.cp-toast { position: fixed; left: 50%; bottom: 24px; z-index: 2000; transform: translateX(-50%); display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 999px; background: #0B1E3D; color: #fff; font-size: 14px; font-weight: 700; box-shadow: 0 16px 32px rgba(11,30,61,.35); animation: cpToast 280ms var(--sv-e-out) both; }
.cp-toast[hidden] { display: none; }
.lb { position: fixed; inset: 0; z-index: 1300; display: none; flex-direction: column; background: rgba(5,10,20,.94); }
.lb.open { display: flex; }
.lb-hd { display: flex; align-items: center; gap: 12px; padding: 14px 18px; color: #fff; }
.lb-hd b { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lb-hd span { font-size: 13px; opacity: .75; }
.lb-x, .lb-nav { border: 0; border-radius: 999px; background: rgba(255,255,255,.12); color: #fff; cursor: pointer; display: grid; place-items: center; }
.lb-x { width: 40px; height: 40px; }
.lb-main { position: relative; flex: 1; min-height: 0; display: grid; place-items: center; padding: 0 64px; }
.lb-main img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 12px; }
.lb-nav { position: absolute; top: 50%; width: 46px; height: 46px; margin-top: -23px; }
.lb-prev { left: 12px; } .lb-next { right: 12px; }
.lb-nav[hidden] { display: none; }
.lb-thumbs { display: flex; gap: 8px; justify-content: center; padding: 14px; overflow-x: auto; }
.lb-thumbs img { width: 56px; height: 56px; flex-shrink: 0; object-fit: cover; border-radius: 10px; opacity: .5; cursor: pointer; }
.lb-thumbs img.active { opacity: 1; outline: 2px solid #fff; }

@keyframes cpRise { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
@keyframes cpPop { from { opacity: 0; transform: translateY(6px) scale(.98); } to { opacity: 1; transform: none; } }
@keyframes cpFade { from { opacity: 0; } to { opacity: 1; } }
@keyframes cpToast { from { opacity: 0; transform: translate(-50%, 12px); } to { opacity: 1; transform: translate(-50%, 0); } }
.in { animation: cpRise 500ms var(--sv-e-out) backwards; }

@media (max-width: 1200px) { .g4 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 900px) {
    .cp, .cp-top { padding-left: 16px !important; padding-right: 16px !important; }
    .g4 { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .hide-m { display: none !important; }
    .cp-chips { flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none; width: 100%; }
    .chip { flex-shrink: 0; }
}
@media (max-width: 560px) {
    .pcard-body { padding: 10px; } .pcard-name { font-size: 13.5px; } .pcard-price { font-size: 15px; }
    .cp-bar > .cp-search, .cp-bar > label { flex: 1 1 100%; } .sel { max-width: none; width: 100%; }
    .lb-main { padding: 0 8px; } .lb-nav { display: none; }
}
@media (prefers-reduced-motion: reduce) { .cp *, .cp-menu, .cp-box, .cp-toast { animation: none !important; transition-duration: 1ms !important; } .pcard:hover { transform: none; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'products'])

    <main class="main vs-main">
        <header class="topbar cp-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
            <div style="flex:1 1 200px;min-width:0">
                <h1>Produits</h1>
                <div class="cp-top-sub num">{{ $totalProducts }} produit{{ $totalProducts > 1 ? 's' : '' }} · {{ $shop->name }}</div>
            </div>
            <button type="button" class="btn btn-g ico" onclick="openShare()" title="Partager ma boutique" aria-label="Partager ma boutique">{!! Ico::svg('share', '', 18) !!}</button>
            <a class="btn btn-g ico" href="{{ $shareUrl }}" target="_blank" rel="noopener" title="Voir ma boutique" aria-label="Voir ma boutique">{!! Ico::svg('eye', '', 18) !!}</a>
            @if($limitHit)
                <a class="btn btn-p" href="{{ route('payment.checkout', ['type' => 'shop', 'id' => $shop->id]) }}">{!! Ico::svg('zap', '', 16) !!}Passer au Plan Pro</a>
            @else
                <a class="btn btn-s hide-m" href="{{ route('products.import') }}">{!! Ico::svg('link', '', 16) !!}Importer un lien</a>
                <a class="btn btn-s hide-m" href="{{ route('products.wizard') }}">{!! Ico::svg('sparkles', '', 16) !!}Créer avec l’IA</a>
                <a class="btn btn-p" href="{{ route('products.create') }}">{!! Ico::svg('plus', '', 16) !!}Ajouter</a>
            @endif
        </header>

        <div class="cp">
            @if(!$isPro)
            <div class="cp-plan {{ $limitHit ? 'is-full' : '' }}">
                <span>Plan Gratuit : <b class="num">{{ min($totalProducts, $freeMax) }} / {{ $freeMax }}</b> produits</span>
                <span class="cp-meter" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $freeMax }}" aria-valuenow="{{ min($totalProducts, $freeMax) }}" aria-label="Produits utilisés"><i style="width:{{ min(100, round($totalProducts / $freeMax * 100)) }}%"></i></span>
                <a href="{{ route('payment.checkout', ['type' => 'shop', 'id' => $shop->id]) }}">{{ $limitHit ? 'Limite atteinte : passer au Plan Pro' : 'Plan Pro : sans limite' }}</a>
            </div>
            @endif

            @foreach(['success' => 'is-ok', 'danger' => 'is-err', 'error' => 'is-err'] as $t => $cls)
                @if(session($t))<div class="cp-flash {{ $cls }}" role="status">{!! Ico::svg($cls === 'is-ok' ? 'check' : 'alert', '', 18) !!} {{ session($t) }}</div>@endif
            @endforeach

            {{-- Filtres avec leurs nombres (calculés), recherche, catégorie --}}
            <div class="cp-bar">
                <nav class="cp-chips" aria-label="Filtrer les produits">
                    @foreach($chips as [$label, $n, $href, $on, $id])
                    <a class="chip" href="{{ $href }}" @if($on) aria-current="page" @endif>{{ $label }} <span class="num" @if($id) id="{{ $id }}" @endif>{{ $n }}</span></a>
                    @endforeach
                </nav>
                <form class="cp-search" method="GET" action="{{ route('products.index') }}" id="filterForm" role="search">
                    @foreach(['status' => $status, 'stock' => $stockF, 'category' => request('category'), 'par_page' => $perPage !== 12 ? $perPage : null] as $k => $v)
                        @if($v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                    @endforeach
                    {!! Ico::svg('search', '', 17) !!}
                    <label for="searchInput" class="sr">Rechercher un produit</label>
                    <input id="searchInput" class="inp" type="search" name="search" placeholder="Rechercher" value="{{ request('search', '') }}" autocomplete="off">
                </form>
                <label><span class="sr">Catégorie</span>
                    <select class="sel" onchange="location.href=this.value">
                        <option value="{{ $url(['category' => null]) }}">Toutes les catégories</option>
                        @if(!empty($customCats))<optgroup label="Mes catégories">@foreach($customCats as $cat)<option value="{{ $url(['category' => $cat]) }}" @selected(request('category') === $cat)>{{ $cat }}</option>@endforeach</optgroup>@endif
                        <optgroup label="Catégories Shopio">@foreach($categories as $cat)<option value="{{ $url(['category' => $cat]) }}" @selected(request('category') === $cat)>{{ $cat }}</option>@endforeach</optgroup>
                    </select>
                </label>
            </div>

            {{-- Grille des produits --}}
            <div class="g4">
                @forelse($products as $i => $product)
                @php
                    $product->setRelation('shop', $shop);
                    $gallery  = is_array($product->gallery) ? $product->gallery : ($product->gallery ? (json_decode($product->gallery, true) ?: []) : []);
                    $hasPromo = $product->original_price && $product->original_price > $product->price;
                    $stockVal = $product->stock;
                    $photos   = array_values(array_filter(array_merge([$product->image ? asset('storage/' . $product->image) : null], array_map(fn ($g) => asset('storage/' . $g), $gallery))));
                    // Photo de la carte : la principale, sinon la première de la galerie
                    $cover    = $img($product->image) ?? $img($gallery[0] ?? null);
                    $menu     = [
                        'page' => route('products.page.edit', $product), 'view' => $product->publicUrl(),
                        'boost' => auth()->user()->role === 'admin' && $product->is_active && $product->stock > 0 ? route('boutique.campaigns.create', ['produit' => $product->id]) : null,
                        'dupe' => route('products.duplicate', $product), 'del' => route('products.destroy', $product), 'name' => $product->name,
                    ];
                @endphp
                <article class="pcard in {{ $product->is_active ? '' : 'is-off' }}" id="card-{{ $product->id }}" style="animation-delay:{{ min($i, 11) * 35 }}ms">
                    <div class="pcard-media">
                        @if($cover)
                            <img class="ph" src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy">
                        @else
                            <a class="pcard-empty" href="{{ route('products.edit', $product) }}">{!! Ico::svg('camera', '', 26) !!}Ajouter une photo</a>
                        @endif
                        <span class="pcard-tag" data-status-badge @if($product->is_active) hidden @endif><i></i>Inactif</span>
                        @if(count($photos) > 1)
                        <button type="button" class="pcard-photos" onclick='openGallery(@json($photos), @json($product->name))' aria-label="Voir les {{ count($photos) }} photos de {{ $product->name }}">{!! Ico::svg('image', '', 14) !!}<span class="num">{{ count($photos) }}</span></button>
                        @endif
                    </div>
                    <div class="pcard-body">
                        <a class="pcard-name" href="{{ route('products.edit', $product) }}">{{ $product->name }}</a>
                        <div class="num pcard-price">{{ $gnf($product->price) }}@if($hasPromo)<s>{{ $gnf($product->original_price) }}</s>@endif</div>
                        <div class="pcard-meta">
                            {{ $product->category ?: 'Sans catégorie' }}@if($stockVal !== null) ·
                                @if($stockVal <= 0)<span class="is-out">Rupture de stock</span>
                                @elseif($stockVal <= 5)<span class="is-low">Plus que {{ $stockVal }}</span>
                                @else<span class="num">{{ $stockVal }} en stock</span>@endif
                            @endif
                        </div>
                        <div class="pcard-foot">
                            <button type="button" class="sw" role="switch" aria-checked="{{ $product->is_active ? 'true' : 'false' }}" aria-label="Produit actif : {{ $product->name }}" title="Visible en boutique" data-url="{{ route('products.toggle', $product) }}"><span></span></button>
                            <a class="btn btn-s ico" href="{{ route('products.edit', $product) }}" title="Modifier" aria-label="Modifier {{ $product->name }}">{!! Ico::svg('edit', '', 17) !!}</a>
                            <button type="button" class="btn btn-s ico" data-menu='@json($menu)' aria-haspopup="menu" aria-expanded="false" title="Plus d’actions" aria-label="Plus d’actions pour {{ $product->name }}">{!! Ico::svg('more', '', 18) !!}</button>
                        </div>
                    </div>
                </article>
                @empty
                <div class="cp-empty">
                    @if($filtered)
                        <span class="cp-empty-ico">{!! Ico::svg('search', '', 28) !!}</span>
                        <h2>Aucun produit ne correspond</h2>
                        <p>Essayez un autre mot ou retirez les filtres.</p>
                        <a class="btn btn-s" href="{{ route('products.index') }}">Effacer les filtres</a>
                    @else
                        <span class="cp-empty-ico">{!! Ico::svg('camera', '', 28) !!}</span>
                        <h2>Votre premier produit, en une photo</h2>
                        <p>Ajoutez une photo et votre prix : Shopio IA écrit le nom, la description et toute la page de vente. Vous relisez, puis vous publiez.</p>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center">
                            <a class="btn btn-p" href="{{ route('products.wizard') }}">{!! Ico::svg('sparkles', '', 16) !!}Créer avec Shopio IA</a>
                            <a class="btn btn-s" href="{{ route('products.create') }}">Ajouter un produit</a>
                            <a class="btn btn-s" href="{{ route('products.quick-add') }}">{!! Ico::svg('zap', '', 16) !!}Ajout rapide</a>
                        </div>
                    @endif
                </div>
                @endforelse
            </div>

            {{-- Pages --}}
            @if($products->total() > 0)
            <div class="cp-foot">
                <form method="GET" action="{{ route('products.index') }}" style="display:flex;align-items:center;gap:10px">
                    @foreach(['search' => request('search'), 'category' => request('category'), 'status' => $status, 'stock' => $stockF] as $k => $v)
                        @if($v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                    @endforeach
                    <span class="num">{{ $products->firstItem() }}–{{ $products->lastItem() }} sur {{ $products->total() }}</span>
                    <label for="perPage" class="sr">Produits par page</label>
                    <select id="perPage" class="sel" name="par_page" onchange="this.form.submit()" style="height:36px;font-size:13px">
                        @foreach([12, 24, 48] as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }} par page</option>@endforeach
                    </select>
                </form>
                @if($products->hasPages())
                <nav aria-label="Pages" style="display:flex;align-items:center;gap:6px">
                    @if($products->onFirstPage())<span class="btn btn-s ico" aria-disabled="true" aria-label="Page précédente"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></span>@else<a class="btn btn-s ico" href="{{ $products->previousPageUrl() }}" aria-label="Page précédente"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></a>@endif
                    <span class="num" style="padding:0 8px;font-weight:700;color:var(--text-2)">{{ $products->currentPage() }} / {{ $products->lastPage() }}</span>
                    @if($products->hasMorePages())<a class="btn btn-s ico" href="{{ $products->nextPageUrl() }}" aria-label="Page suivante">{!! Ico::svg('chevron-right', '', 18) !!}</a>@else<span class="btn btn-s ico" aria-disabled="true" aria-label="Page suivante">{!! Ico::svg('chevron-right', '', 18) !!}</span>@endif
                </nav>
                @endif
            </div>
            @endif
        </div>
    </main>
</div>

<button type="button" class="cp-scrim" id="cpScrim" aria-label="Fermer" tabindex="-1"></button>

{{-- Supprimer un produit --}}
<div class="cp-modal" id="deleteModal" role="dialog" aria-modal="true" aria-labelledby="delTitle">
    <div class="cp-box">
        <span class="cp-box-ico" style="background:var(--sv-err50);color:var(--sv-err)">{!! Ico::svg('trash', '', 22) !!}</span>
        <h2 id="delTitle">Supprimer ce produit ?</h2>
        <p><strong id="modalProductName"></strong><br>Le produit et ses photos seront supprimés. Cette action est définitive.</p>
        <div style="display:flex;gap:10px">
            <button type="button" class="btn btn-s" style="flex:1" onclick="closeAll()">Annuler</button>
            <form id="deleteForm" method="POST" style="flex:1;display:flex;margin:0">@csrf @method('DELETE')<button type="submit" class="btn btn-danger" style="flex:1">Supprimer</button></form>
        </div>
    </div>
</div>

{{-- Partager ma boutique --}}
<div class="cp-modal" id="shareModal" role="dialog" aria-modal="true" aria-labelledby="shareTitle">
    <div class="cp-box">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px">
            <h2 id="shareTitle">Partager ma boutique</h2>
            <button type="button" class="btn btn-g ico" onclick="closeAll()" aria-label="Fermer">{!! Ico::svg('x', '', 18) !!}</button>
        </div>
        <p>Vos clients voient tous vos produits et commandent en 1 minute.</p>
        <div class="cp-link"><code>{{ $shareUrl }}</code><button type="button" class="btn btn-p" id="copyBtn" onclick="copyShareUrl()" style="min-height:36px">{!! Ico::svg('link', '', 15) !!}<span>Copier</span></button></div>
        {{-- Partage natif du téléphone : seul moyen fiable vers l'appli Facebook sur mobile (elle ignore le lien de sharer.php) --}}
        <button type="button" class="btn btn-s" id="btnNativeShare" onclick="nativeShareShop()" style="display:none;width:100%">{!! Ico::svg('smartphone', '', 16) !!}Partager avec mon téléphone</button>
        <div class="cp-nets">
            <a class="cp-net" href="https://wa.me/?text={{ urlencode('Découvrez ma boutique ' . $shop->name . ' sur Shopio : ' . $shareUrl) }}" target="_blank" rel="noopener"><span>{!! Ico::svg('message', '', 18) !!}</span>WhatsApp</a>
            <a class="cp-net" id="btnShareFacebook" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"><span>{!! Ico::svg('users', '', 18) !!}</span>Facebook</a>
            <a class="cp-net" href="https://telegram.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode('Découvrez ' . $shop->name . ' : tous mes produits !') }}" target="_blank" rel="noopener"><span>{!! Ico::svg('send', '', 18) !!}</span>Telegram</a>
            <a class="cp-net" href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode('Découvrez tous les produits de ' . $shop->name . ' !') }}" target="_blank" rel="noopener"><span>{!! Ico::svg('x', '', 18) !!}</span>X</a>
        </div>
    </div>
</div>

<form id="dupeForm" method="POST" hidden>@csrf</form>
<div class="cp-toast" id="cpToast" hidden role="status"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4ADE80" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg><span id="cpToastMsg"></span></div>

{{-- Galerie photos : flèches, miniatures, clavier (← → Échap), glisser sur mobile --}}
<div class="lb" id="lbOverlay" role="dialog" aria-modal="true" aria-labelledby="lbProductName">
    <div class="lb-hd"><b id="lbProductName"></b><span id="lbPhotoCount"></span><button type="button" class="lb-x" onclick="closeLightbox()" aria-label="Fermer la galerie">{!! Ico::svg('x', '', 18) !!}</button></div>
    <div class="lb-main">
        <button type="button" class="lb-nav lb-prev" id="lbPrev" onclick="lbNavigate(-1)" aria-label="Photo précédente"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></button>
        <img id="lbMainImg" src="" alt="">
        <button type="button" class="lb-nav lb-next" id="lbNext" onclick="lbNavigate(1)" aria-label="Photo suivante">{!! Ico::svg('chevron-right', '', 22) !!}</button>
    </div>
    <div class="lb-thumbs" id="lbThumbs"></div>
</div>
@endsection

@push('scripts')
<script>
['cpScrim', 'deleteModal', 'shareModal', 'dupeForm', 'cpToast', 'lbOverlay'].forEach(id => { const el = document.getElementById(id); if (el) document.body.appendChild(el); });
const SHARE_URL = @json($shareUrl);
const SHOP_NAME = @json($shop->name);
@php $menuIcons = collect(['layout', 'external', 'zap', 'copy', 'trash'])->mapWithKeys(fn ($n) => [$n => Ico::svg($n, '', 17)]); @endphp
const ICO = @json($menuIcons);
const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
let toastT;
function say(msg) { document.getElementById('cpToastMsg').textContent = msg; const t = document.getElementById('cpToast'); t.hidden = true; void t.offsetWidth; t.hidden = false; clearTimeout(toastT); toastT = setTimeout(() => { t.hidden = true; }, 2400); }

/* ── Fenêtres ── */
let lastFocus = null;
function openModal(id) { closeMenu(); lastFocus = document.activeElement; document.getElementById('cpScrim').classList.add('open'); document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; setTimeout(() => document.getElementById(id).querySelector('button, a')?.focus(), 30); }
function closeAll() { const open = document.querySelector('.cp-modal.open'); ['deleteModal', 'shareModal'].forEach(id => document.getElementById(id).classList.remove('open')); document.getElementById('cpScrim').classList.remove('open'); document.body.style.overflow = ''; if (open && lastFocus) lastFocus.focus(); }
document.getElementById('cpScrim').addEventListener('click', closeAll);
function openDeleteModal(url, name) { document.getElementById('deleteForm').action = url; document.getElementById('modalProductName').textContent = name; openModal('deleteModal'); }
function openShare() { if (navigator.share) document.getElementById('btnNativeShare').style.display = 'flex'; openModal('shareModal'); }
function nativeShareShop() { if (navigator.share) navigator.share({ title: SHOP_NAME, text: 'Découvrez ma boutique ' + SHOP_NAME + ' sur Shopio :', url: SHARE_URL }).catch(() => {}); }
(function () { // Sur téléphone, Facebook passe par le partage natif (voir plus haut)
    const fb = document.getElementById('btnShareFacebook');
    if (fb && navigator.share && /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent)) fb.addEventListener('click', e => { e.preventDefault(); nativeShareShop(); });
})();
function copyShareUrl() {
    navigator.clipboard.writeText(SHARE_URL).then(() => { const s = document.querySelector('#copyBtn span'); s.textContent = 'Copié'; setTimeout(() => { s.textContent = 'Copier'; }, 2200); say('Lien de la boutique copié'); })
        .catch(() => window.prompt('Copiez le lien :', SHARE_URL));
}

/* ── Menu ⋯ d'un produit ── */
let menu = null, menuBtn = null;
function closeMenu() { if (menu) { menu.remove(); menu = null; } if (menuBtn) { menuBtn.setAttribute('aria-expanded', 'false'); menuBtn = null; } }
document.addEventListener('click', e => {
    const b = e.target.closest('[data-menu]');
    if (!b) { if (menu && !menu.contains(e.target)) closeMenu(); return; }
    const same = menuBtn === b; closeMenu(); if (same) return;
    const d = JSON.parse(b.dataset.menu), m = document.createElement('div');
    m.className = 'cp-menu'; m.setAttribute('role', 'menu');
    m.innerHTML = '<a role="menuitem" href="' + esc(d.page) + '">' + ICO.layout + 'Page de vente</a>' +
        '<a role="menuitem" href="' + esc(d.view) + '" target="_blank" rel="noopener">' + ICO.external + 'Voir comme un client</a>' +
        (d.boost ? '<a role="menuitem" href="' + esc(d.boost) + '">' + ICO.zap + 'Booster sur Shopio</a>' : '') +
        '<button type="button" role="menuitem" data-act="dupe">' + ICO.copy + 'Dupliquer</button>' +
        '<hr><button type="button" role="menuitem" class="is-del" data-act="del">' + ICO.trash + 'Supprimer</button>';
    m.addEventListener('click', ev => {
        const a = ev.target.closest('[data-act]'); if (!a) return;
        closeMenu();
        if (a.dataset.act === 'dupe') { const f = document.getElementById('dupeForm'); f.action = d.dupe; f.submit(); }
        if (a.dataset.act === 'del') openDeleteModal(d.del, d.name);
    });
    document.body.appendChild(m);
    const r = b.getBoundingClientRect(), w = m.offsetWidth, h = m.offsetHeight;
    m.style.left = Math.max(8, Math.min(window.innerWidth - w - 8, r.right - w)) + 'px';
    m.style.top = (r.bottom + h + 8 > window.innerHeight ? Math.max(8, r.top - h - 6) : r.bottom + 6) + 'px';
    b.setAttribute('aria-expanded', 'true'); menu = m; menuBtn = b;
    m.querySelector('a, button').focus();
});
window.addEventListener('scroll', closeMenu, { passive: true });
window.addEventListener('resize', closeMenu);

/* ── Activer / désactiver (interrupteur) ── */
const kpiActive = document.getElementById('kpiActive'), kpiInactive = document.getElementById('kpiInactive');
document.querySelectorAll('.sw[data-url]').forEach(btn => {
    btn.addEventListener('click', async () => {
        const was = btn.getAttribute('aria-checked') === 'true';
        const card = btn.closest('.pcard'), name = card.querySelector('.pcard-name').textContent;
        btn.disabled = true;
        try {
            const res = await fetch(btn.dataset.url, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const on = !!(await res.json()).active;
            btn.setAttribute('aria-checked', String(on));
            card.classList.toggle('is-off', !on);
            card.querySelector('[data-status-badge]').hidden = on;
            if (on !== was) {
                if (kpiActive) kpiActive.textContent = +kpiActive.textContent + (on ? 1 : -1);
                if (kpiInactive) kpiInactive.textContent = +kpiInactive.textContent + (on ? -1 : 1);
            }
            say(name + (on ? ' est visible en boutique' : ' est masqué'));
        } catch (err) {
            say('La mise à jour n’a pas marché. Réessayez.');
        } finally { btn.disabled = false; }
    });
});

/* ── Recherche : envoyée 500 ms après la frappe ── */
(function () { const input = document.getElementById('searchInput'); let t = null; if (input) input.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => input.form.submit(), 500); }); })();

/* ── Galerie photos ── */
let lbPhotos = [], lbCurrent = 0, lbName = '';
function openGallery(photos, name) {
    lbPhotos = photos; lbName = name;
    document.getElementById('lbProductName').textContent = name;
    document.getElementById('lbThumbs').innerHTML = photos.map((u, i) => '<img src="' + esc(u) + '" alt="Photo ' + (i + 1) + '" loading="lazy" data-i="' + i + '">').join('');
    document.getElementById('lbOverlay').classList.add('open'); document.body.style.overflow = 'hidden';
    showLbPhoto(0);
}
function showLbPhoto(i) {
    if (i < 0 || i >= lbPhotos.length) return;
    lbCurrent = i;
    const img = document.getElementById('lbMainImg'); img.src = lbPhotos[i]; img.alt = lbName + ' : photo ' + (i + 1);
    document.getElementById('lbPhotoCount').textContent = (i + 1) + ' / ' + lbPhotos.length;
    document.getElementById('lbPrev').hidden = i === 0; document.getElementById('lbNext').hidden = i === lbPhotos.length - 1;
    document.querySelectorAll('#lbThumbs img').forEach((t, k) => t.classList.toggle('active', k === i));
}
function lbNavigate(d) { showLbPhoto(lbCurrent + d); }
function closeLightbox() { document.getElementById('lbOverlay').classList.remove('open'); document.body.style.overflow = ''; lbPhotos = []; }
document.getElementById('lbThumbs').addEventListener('click', e => { const t = e.target.closest('[data-i]'); if (t) showLbPhoto(+t.dataset.i); });
document.getElementById('lbOverlay').addEventListener('click', function (e) { if (e.target === this || e.target.classList.contains('lb-main')) closeLightbox(); });
let lbX = 0;
document.getElementById('lbOverlay').addEventListener('touchstart', e => { lbX = e.touches[0].clientX; }, { passive: true });
document.getElementById('lbOverlay').addEventListener('touchend', e => { const dx = e.changedTouches[0].clientX - lbX; if (Math.abs(dx) > 50) lbNavigate(dx < 0 ? 1 : -1); }, { passive: true });

/* ── Clavier : Échap ferme, ← → dans la galerie ── */
document.addEventListener('keydown', e => {
    const lb = document.getElementById('lbOverlay').classList.contains('open');
    if (e.key === 'Escape') { closeMenu(); if (lb) closeLightbox(); else closeAll(); }
    if (lb && e.key === 'ArrowLeft') lbNavigate(-1);
    if (lb && e.key === 'ArrowRight') lbNavigate(1);
});
</script>
@endpush
