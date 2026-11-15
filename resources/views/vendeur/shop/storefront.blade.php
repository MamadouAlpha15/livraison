{{--
    resources/views/vendeur/shop/storefront.blade.php
    Route     : GET /boutique/vitrine → Vendeur\StorefrontController@edit → name('boutique.storefront.edit')
    Rôle      : « Ma vitrine » — le thème (Express ou Signature, à essayer sur la vraie vitrine sans rien
                publier), la couleur, l'adresse courte, l'affichage des fiches produit et le mode vacances.
                « Enregistrer » publie tout de suite (StorefrontController@update).
    Variables : $shop, $config, $accent, $colors, $models, $publicUrl, $slugBase, $qrSvg
--}}
@extends('layouts.app')
@section('title', 'Ma vitrine · ' . $shop->name)
@php
    $bodyClass = 'is-dashboard';
    $set = $config['settings'];
    $model = old('page_model', $config['page_model']);
    $color = strtoupper(old('accent', $accent));
    $host = preg_replace('#^https?://#', '', $publicUrl);
    // Aperçu de chaque thème : bandeau, carte produit et bouton aux couleurs du thème
    $looks = [
        'express'   => ['bg' => '#FFFFFF', 'band' => 'var(--mv-acc)', 'card' => '#EEF3E8', 'ink' => '#111111', 'btn' => 'var(--mv-acc)', 'font' => "'Poppins', 'Plus Jakarta Sans', sans-serif",
                        'points' => ['Packs à prix réduit (2, 3 pièces…)', 'Commande en 1 minute sur la page produit', 'Page produit longue, rédigée par Shopio IA'], 'tag' => 'Pour vendre vite'],
        'signature' => ['bg' => '#F6F2EA', 'band' => '#1F3B2D', 'card' => '#EDE6D8', 'ink' => '#1A1A17', 'btn' => '#1F3B2D', 'font' => "'Instrument Serif', Georgia, serif",
                        'points' => ['Grandes photos, look magazine', 'Ivoire, encre et vert profond', 'Idéal pour les belles pièces'], 'tag' => 'Pour une image premium'],
    ];
@endphp

@push('styles')
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Instrument+Serif&display=swap" rel="stylesheet">
<style>
:root {
    --mv-blue: #1668D8; --mv-blue-h: #1260C9; --mv-b50: #EAF2FF; --mv-night: #0B1E3D;
    --mv-bg: #F6F9FF; --mv-surf: #FFFFFF; --mv-line: #E3E8F2; --mv-soft: #F1F5FB;
    --mv-ink: #16162A; --mv-ink2: #45455E; --mv-ink3: #62627C; --mv-ok: #15803D; --mv-ok50: #E7F6EC; --mv-err: #B91C1C;
    --mv-amb: #F59E0B; --mv-amb50: #FEF3E0; --mv-ambi: #92400E;
    --mv-font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    --mv-out: cubic-bezier(.22,1,.36,1);
}
[data-theme="dark"] {
    --mv-b50: #13284B; --mv-bg: #0A1426; --mv-surf: #10203B; --mv-line: #22355A; --mv-soft: #0D1A31;
    --mv-ink: #EAF0FA; --mv-ink2: #C3CEE0; --mv-ink3: #9DAAC2; --mv-ok: #4ADE80; --mv-ok50: #0F2A1C; --mv-err: #F87171; --mv-amb50: #2E2208; --mv-ambi: #FBBF24;
}
#pwaModal, #pushNotifBanner { display: none !important; }
html, body { background: var(--mv-bg); }
.mv { --mv-acc: {{ $color }}; font-family: var(--mv-font); color: var(--mv-ink); padding-bottom: 120px; }
.mv-in { max-width: 1180px; margin: 0 auto; padding: 22px 16px 0; }
.mv *, .mv *::before, .mv *::after { box-sizing: border-box; }
.mv h1 { margin: 0; font-size: clamp(24px, 3vw, 30px); font-weight: 800; letter-spacing: -.02em; }
.mv h2 { margin: 0; font-size: 17px; font-weight: 800; }
.mv-top { background: var(--mv-night); color: #fff; }
.mv-top-in { max-width: 1180px; margin: 0 auto; padding: 22px 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
.mv-top p { margin: 6px 0 0; color: #C9D6EA; font-size: 14.5px; }
.mv-top .mv-btn { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18); color: #fff; }
.mv-top .mv-btn:hover { background: rgba(255,255,255,.16); }
.mv-links { display: flex; gap: 8px; flex-wrap: wrap; }
.mv-btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 42px; padding: 0 16px; border-radius: 12px; border: 1px solid var(--mv-line); background: var(--mv-surf); color: var(--mv-ink); font: 700 14px var(--mv-font); text-decoration: none; cursor: pointer; transition: background-color 160ms, border-color 160ms, transform 160ms var(--mv-out); }
.mv-btn:hover { border-color: #B9C7DD; }
.mv-btn:active { transform: scale(.98); }
.mv-btn-main { background: var(--mv-blue); border-color: var(--mv-blue); color: #fff; }
.mv-btn-main:hover { background: var(--mv-blue-h); border-color: var(--mv-blue-h); }
.mv-btn-sm { min-height: 36px; padding: 0 12px; font-size: 13px; border-radius: 10px; }
.mv :focus-visible { outline: 3px solid #7DB4FF; outline-offset: 2px; }
.mv-flash { margin-bottom: 14px; padding: 12px 16px; border-radius: 12px; font-weight: 700; font-size: 14px; }
.mv-flash-ok { background: var(--mv-ok50); color: var(--mv-ok); }
.mv-flash-err { background: #FDECEC; color: var(--mv-err); }
.mv-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 18px; align-items: start; }
.mv-stack { display: grid; gap: 18px; }
.mv-card { background: var(--mv-surf); border: 1px solid var(--mv-line); border-radius: 20px; padding: 20px; display: grid; gap: 14px; }
.mv-card-hd { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.mv-hint { margin: 0; color: var(--mv-ink3); font-size: 13.5px; line-height: 1.5; }

/* Thèmes */
.mv-themes { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
.mv-theme { position: relative; display: grid; gap: 12px; padding: 12px 12px 16px; border: 2px solid var(--mv-line); border-radius: 18px; background: var(--mv-surf); cursor: pointer; transition: border-color 180ms, box-shadow 180ms, transform 220ms var(--mv-out); }
.mv-theme:hover { transform: translateY(-2px); }
.mv-theme input { position: absolute; opacity: 0; pointer-events: none; }
.mv-theme:has(input:checked) { border-color: var(--mv-blue); box-shadow: 0 0 0 4px var(--mv-b50); }
.mv-theme:has(input:focus-visible) { outline: 3px solid #7DB4FF; outline-offset: 2px; }
.mv-check { position: absolute; top: 20px; right: 20px; z-index: 2; width: 28px; height: 28px; border-radius: 50%; background: var(--mv-blue); color: #fff; display: none; place-items: center; box-shadow: 0 4px 12px rgba(22,104,216,.35); }
.mv-theme:has(input:checked) .mv-check { display: grid; }
.mv-shot { border-radius: 12px; overflow: hidden; border: 1px solid rgba(0,0,0,.06); aspect-ratio: 16 / 10; display: grid; grid-template-rows: 18% 1fr; }
.mv-shot-band { display: flex; align-items: center; padding: 0 10px; gap: 6px; color: #fff; font-size: 11px; font-weight: 700; }
.mv-shot-band i { width: 14px; height: 14px; border-radius: 50%; background: rgba(255,255,255,.85); }
.mv-shot-body { padding: 10px; display: grid; grid-template-columns: 1.1fr 1fr; gap: 10px; align-items: center; }
.mv-shot-title { font-size: clamp(15px, 2vw, 20px); line-height: 1.1; }
.mv-shot-lines { display: grid; gap: 5px; margin-top: 8px; }
.mv-shot-lines span { height: 6px; border-radius: 4px; background: currentColor; opacity: .14; }
.mv-shot-lines span:nth-child(2) { width: 70%; }
.mv-shot-btn { margin-top: 10px; width: 64%; height: 18px; border-radius: 6px; }
.mv-shot-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.mv-shot-cards i { aspect-ratio: 4 / 5; border-radius: 8px; }
.mv-theme-name { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.mv-theme-name b { font-size: 17px; }
.mv-tag { padding: 3px 9px; border-radius: 999px; background: var(--mv-soft); color: var(--mv-ink2); font-size: 11.5px; font-weight: 800; }
.mv-tag-live { background: var(--mv-ok50); color: var(--mv-ok); }
.mv-theme ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 5px; font-size: 13.5px; color: var(--mv-ink2); }
.mv-theme li { display: flex; gap: 7px; }
.mv-theme li::before { content: '✓'; color: var(--mv-ok); font-weight: 800; }
.mv-try { justify-self: start; color: var(--mv-blue); font-weight: 700; font-size: 13.5px; text-decoration: none; }
.mv-try:hover { text-decoration: underline; }

/* Couleur */
.mv-colors { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.mv-swatch { width: 40px; height: 40px; border-radius: 50%; border: 3px solid var(--mv-surf); box-shadow: 0 0 0 1px var(--mv-line); cursor: pointer; padding: 0; transition: transform 160ms var(--mv-out), box-shadow 160ms; }
.mv-swatch:hover { transform: scale(1.08); }
.mv-swatch[aria-checked="true"] { box-shadow: 0 0 0 3px var(--mv-ink); }
.mv-custom { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 12px 0 6px; border: 1px dashed #B9C7DD; border-radius: 999px; font-size: 13px; font-weight: 700; color: var(--mv-ink2); cursor: pointer; }
.mv-custom input { width: 30px; height: 30px; border: 0; padding: 0; background: none; cursor: pointer; }
.mv-custom input::-webkit-color-swatch-wrapper { padding: 0; }
.mv-custom input::-webkit-color-swatch { border: 0; border-radius: 50%; }
.mv-color-demo { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 12px; border-radius: 14px; background: var(--mv-soft); }
.mv-color-demo b { font-size: 18px; color: var(--mv-acc); font-variant-numeric: tabular-nums; }
.mv-color-demo span { padding: 9px 16px; border-radius: 10px; background: var(--mv-acc); color: var(--mv-acc-ink, #fff); font-weight: 700; font-size: 13.5px; }

/* Champs et interrupteurs */
.mv-lbl { display: block; font-weight: 700; font-size: 13.5px; margin-bottom: 6px; }
.mv-input { width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid var(--mv-line); border-radius: 12px; background: var(--mv-surf); color: var(--mv-ink); font: 500 15px var(--mv-font); }
.mv-input:focus { outline: none; border-color: var(--mv-blue); box-shadow: 0 0 0 3px var(--mv-b50); }
.mv-slug { display: flex; align-items: center; border: 1px solid var(--mv-line); border-radius: 12px; overflow: hidden; background: var(--mv-surf); }
.mv-slug:focus-within { border-color: var(--mv-blue); box-shadow: 0 0 0 3px var(--mv-b50); }
.mv-slug span { padding: 0 4px 0 12px; color: var(--mv-ink3); font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 55%; }
.mv-slug input { flex: 1; min-width: 0; min-height: 44px; border: 0; padding: 10px 12px 10px 0; background: none; color: var(--mv-ink); font: 700 15px var(--mv-font); }
.mv-slug input:focus { outline: none; }
.mv-msg { font-size: 13px; font-weight: 600; color: var(--mv-ink3); }
.mv-msg.is-ok { color: var(--mv-ok); } .mv-msg.is-err { color: var(--mv-err); }
.mv-row { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-top: 1px solid var(--mv-line); }
.mv-row:first-of-type { border-top: 0; padding-top: 0; }
.mv-row-txt { flex: 1; min-width: 0; }
.mv-row-txt b { display: block; font-size: 14.5px; }
.mv-row-txt span { display: block; color: var(--mv-ink3); font-size: 13px; margin-top: 2px; }
.mv-sw { position: relative; flex: 0 0 auto; width: 50px; height: 30px; }
.mv-sw input { position: absolute; inset: 0; opacity: 0; margin: 0; cursor: pointer; z-index: 1; }
.mv-sw span { position: absolute; inset: 0; border-radius: 999px; background: #CBD5E1; transition: background-color 180ms; }
.mv-sw span::after { content: ''; position: absolute; top: 3px; left: 3px; width: 24px; height: 24px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform 220ms var(--mv-out); }
.mv-sw input:checked + span { background: var(--mv-blue); }
.mv-sw.is-amber input:checked + span { background: var(--mv-amb); }
.mv-sw input:checked + span::after { transform: translateX(20px); }
.mv-sw input:focus-visible + span { outline: 3px solid #7DB4FF; outline-offset: 2px; }
.mv-vac { padding: 12px; border-radius: 14px; background: var(--mv-amb50); color: var(--mv-ambi); font-size: 13.5px; font-weight: 600; }

/* Partage */
.mv-share { position: sticky; top: 84px; }
.mv-qr { display: grid; place-items: center; padding: 12px; border-radius: 16px; background: #fff; border: 1px solid var(--mv-line); }
.mv-qr svg { width: 100%; max-width: 200px; height: auto; display: block; }
.mv-link { display: flex; gap: 6px; align-items: center; padding: 4px 4px 4px 12px; border: 1px solid var(--mv-line); border-radius: 12px; }
.mv-link input { flex: 1; min-width: 0; border: 0; background: none; color: var(--mv-ink); font: 600 13.5px var(--mv-font); }
.mv-link input:focus { outline: none; }

/* Barre d'enregistrement */
.mv-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 900; display: flex; justify-content: center; padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px)); background: rgba(255,255,255,.92); backdrop-filter: blur(8px); border-top: 1px solid var(--mv-line); }
[data-theme="dark"] .mv-bar { background: rgba(10,20,38,.92); }
.mv-bar-in { width: 100%; max-width: 1180px; display: flex; align-items: center; gap: 12px; }
.mv-bar-in p { flex: 1; margin: 0; font-size: 13.5px; color: var(--mv-ink3); font-weight: 600; }
.mv-bar-in p.is-dirty { color: var(--mv-ambi); }

@media (max-width: 980px) {
    .mv-grid { grid-template-columns: minmax(0, 1fr); }
    .mv-share { position: static; }
}
@media (max-width: 620px) {
    .mv-themes { grid-template-columns: minmax(0, 1fr); }
    .mv-card { padding: 16px; border-radius: 16px; }
    .mv-bar-in p { display: none; }
    .mv-bar-in .mv-btn { flex: 1; }
}
@media (prefers-reduced-motion: reduce) { .mv *, .mv *::after { transition-duration: 1ms !important; } }
</style>
@endpush

@section('content')
<div class="mv" id="mv">
    <header class="mv-top"><div class="mv-top-in">
        <div>
            <h1>Ma vitrine</h1>
            <p>Choisissez le thème de votre boutique et réglez ce que voient vos clients.</p>
        </div>
        <nav class="mv-links" aria-label="Autres pages">
            <a class="mv-btn mv-btn-sm" href="{{ route('boutique.storefront.sections') }}" style="background:#F59E0B;border-color:#F59E0B;color:#16162A">Modifier les sections</a>
            <a class="mv-btn mv-btn-sm" href="{{ $publicUrl }}" target="_blank" rel="noopener">Voir ma vitrine ↗</a>
            <a class="mv-btn mv-btn-sm" href="{{ route('boutique.grow') }}">Boutique</a>
        </nav>
    </div></header>

    <div class="mv-in">

    @if(session('success'))<div class="mv-flash mv-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mv-flash mv-flash-err" role="alert">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('boutique.storefront.update') }}" id="mvForm" class="mv-grid" novalidate>
        @csrf @method('PUT')
        <div class="mv-stack">
            {{-- ═══ Thème ═══ --}}
            <section class="mv-card" aria-labelledby="mvThemeH">
                <div class="mv-card-hd">
                    <h2 id="mvThemeH">Thème de la boutique</h2>
                    <span class="mv-hint">Même commande payée à la livraison, deux styles</span>
                </div>
                <div class="mv-themes" role="radiogroup" aria-labelledby="mvThemeH">
                    @foreach($models as $k => [$label, $desc])
                    @php $l = $looks[$k]; @endphp
                    <label class="mv-theme">
                        <input type="radio" name="page_model" value="{{ $k }}" @checked($model === $k)>
                        <span class="mv-check" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg></span>
                        <span class="mv-shot" aria-hidden="true" style="background:{{ $l['bg'] }};color:{{ $l['ink'] }}">
                            <span class="mv-shot-band" style="background:{{ $l['band'] }}"><i></i>{{ \Illuminate\Support\Str::limit($shop->name, 18) }}</span>
                            <span class="mv-shot-body">
                                <span>
                                    <span class="mv-shot-title" style="display:block;font-family:{{ $l['font'] }}">{{ $k === 'express' ? 'Commandez en 1 minute' : 'L’élégance au quotidien' }}</span>
                                    <span class="mv-shot-lines"><span></span><span></span></span>
                                    <span class="mv-shot-btn" style="display:block;background:{{ $l['btn'] }}"></span>
                                </span>
                                <span class="mv-shot-cards"><i style="background:{{ $l['card'] }}"></i><i style="background:{{ $l['card'] }}"></i></span>
                            </span>
                        </span>
                        <span class="mv-theme-name"><b>{{ $label }}</b><span class="mv-tag">{{ $l['tag'] }}</span>@if($config['page_model'] === $k)<span class="mv-tag mv-tag-live">En ligne</span>@endif</span>
                        <ul>@foreach($l['points'] as $pt)<li>{{ $pt }}</li>@endforeach</ul>
                        <a class="mv-try" href="{{ $publicUrl }}?modele={{ $k }}" target="_blank" rel="noopener">Essayer sur ma vitrine ↗</a>
                    </label>
                    @endforeach
                </div>
            </section>

            {{-- ═══ Couleur ═══ --}}
            <section class="mv-card" aria-labelledby="mvColorH">
                <div class="mv-card-hd">
                    <h2 id="mvColorH">Couleur principale</h2>
                    <span class="mv-hint">Prix et boutons du thème Express · Signature garde son vert profond</span>
                </div>
                <input type="hidden" name="accent" id="mvAccent" value="{{ $color }}">
                <div class="mv-colors" role="radiogroup" aria-labelledby="mvColorH" id="mvColors">
                    @foreach($colors as $hex => $name)
                    <button type="button" class="mv-swatch" role="radio" aria-checked="{{ $color === $hex ? 'true' : 'false' }}" aria-label="{{ $name }}" title="{{ $name }}" data-color="{{ $hex }}" style="background:{{ $hex }}"></button>
                    @endforeach
                    <label class="mv-custom" title="Choisir ma couleur"><input type="color" id="mvPicker" value="{{ $color }}" aria-label="Choisir ma propre couleur"> Ma couleur</label>
                </div>
                <div class="mv-color-demo" aria-hidden="true"><b>150 000 {{ $shop->currency ?? 'GNF' }}</b><span id="mvDemoBtn">Commander</span></div>
            </section>

            {{-- ═══ Adresse ═══ --}}
            <section class="mv-card" aria-labelledby="mvSlugH">
                <h2 id="mvSlugH">Adresse de la boutique</h2>
                <div class="mv-slug">
                    <span>{{ preg_replace('#^https?://#', '', $slugBase) }}</span>
                    <input id="mvSlug" name="slug" value="{{ old('slug', $shop->slug) }}" maxlength="40" placeholder="nom-de-ma-boutique" autocomplete="off" aria-label="Adresse de la boutique" aria-describedby="mvSlugMsg">
                </div>
                <span class="mv-msg" id="mvSlugMsg" aria-live="polite">@error('slug'){{ $message }}@else Facultatif · une adresse courte, facile à partager @enderror</span>
            </section>

            {{-- ═══ Fiches produit ═══ --}}
            <section class="mv-card" aria-labelledby="mvCardsH">
                <h2 id="mvCardsH">Fiches produit</h2>
                <div>
                    @foreach(['show_stock' => ['Stock restant', 'Affiche « plus que N en stock » quand le stock est bas'], 'show_strike' => ['Prix barré', 'Seulement si vous indiquez un ancien prix'], 'show_nego' => ['Bouton « Négocier le prix »', 'Le client propose un prix dans le chat']] as $k => [$label, $dsc])
                    <div class="mv-row">
                        <div class="mv-row-txt"><b id="mvL{{ $k }}">{{ $label }}</b><span>{{ $dsc }}</span></div>
                        <span class="mv-sw"><input type="hidden" name="{{ $k }}" value="0"><input type="checkbox" role="switch" name="{{ $k }}" value="1" aria-labelledby="mvL{{ $k }}" @checked(old($k, $set[$k]))><span></span></span>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- ═══ Mode vacances ═══ --}}
            <section class="mv-card" aria-labelledby="mvVacH">
                <div class="mv-row" style="padding:0">
                    <div class="mv-row-txt"><h2 id="mvVacH">Mode vacances</h2><span>La boutique reste visible, mais les commandes sont en pause. Vos produits ne sont pas perdus.</span></div>
                    <span class="mv-sw is-amber"><input type="hidden" name="vacation" value="0"><input type="checkbox" role="switch" name="vacation" value="1" id="mvVac" aria-labelledby="mvVacH" @checked(old('vacation', $set['vacation']))><span></span></span>
                </div>
                <div id="mvVacBox" @unless(old('vacation', $set['vacation'])) hidden @endunless>
                    <label class="mv-lbl" for="mvVacMsg">Message affiché aux clients</label>
                    <input class="mv-input" id="mvVacMsg" name="vacation_msg" maxlength="120" value="{{ old('vacation_msg', $set['vacation_msg']) }}" placeholder="Ex. De retour le 5 novembre">
                    <p class="mv-vac" style="margin:10px 0 0">Les clients ne peuvent plus commander tant que le mode vacances est actif.</p>
                </div>
            </section>
        </div>

        {{-- ═══ Partage ═══ --}}
        <aside class="mv-card mv-share" aria-labelledby="mvShareH">
            <h2 id="mvShareH">Partager ma vitrine</h2>
            <div class="mv-link"><input id="mvUrl" value="{{ $publicUrl }}" readonly aria-label="Lien de la vitrine"><button type="button" class="mv-btn mv-btn-main mv-btn-sm" id="mvCopy">Copier</button></div>
            <a class="mv-btn" style="background:#25D366;border-color:#25D366;color:#fff" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode('Découvrez ma boutique ' . $shop->name . ' : ' . $publicUrl) }}">Partager sur WhatsApp</a>
            @if($qrSvg)
            <div class="mv-qr" role="img" aria-label="QR code de la vitrine">{!! $qrSvg !!}</div>
            <a class="mv-btn mv-btn-sm" href="{{ route('boutique.storefront.qr') }}">Télécharger le QR code</a>
            @endif
            <p class="mv-hint">Couverture, logo, liens suivis et statistiques : <a href="{{ route('boutique.grow') }}">Boutique</a>.</p>
        </aside>

        <div class="mv-bar">
            <div class="mv-bar-in">
                <p id="mvState">Tout est enregistré · {{ $host }}</p>
                <button type="submit" class="mv-btn mv-btn-main" id="mvSave">Enregistrer et publier</button>
            </div>
        </div>
    </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const $ = id => document.getElementById(id);
    const root = $('mv'), form = $('mvForm');

    /* Couleur : pastilles, couleur libre, encre lisible sur la couleur choisie */
    const lum = h => [1, 3, 5].map(i => parseInt(h.substr(i, 2), 16) / 255).map(v => v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4)).reduce((a, v, i) => a + v * [0.2126, 0.7152, 0.0722][i], 0);
    function setColor(hex) {
        hex = hex.toUpperCase();
        $('mvAccent').value = hex; $('mvPicker').value = hex;
        root.style.setProperty('--mv-acc', hex);
        root.style.setProperty('--mv-acc-ink', (1.05 / (lum(hex) + 0.05)) >= 3 ? '#FFFFFF' : '#16162A');
        document.querySelectorAll('[data-color]').forEach(b => b.setAttribute('aria-checked', b.dataset.color === hex ? 'true' : 'false'));
        dirty();
    }
    $('mvColors').addEventListener('click', e => { const b = e.target.closest('[data-color]'); if (b) setColor(b.dataset.color); });
    $('mvPicker').addEventListener('input', e => setColor(e.target.value));

    /* Mode vacances : message seulement quand il est actif */
    $('mvVac').addEventListener('change', e => { $('mvVacBox').hidden = !e.target.checked; });

    /* Adresse : disponibilité vérifiée pendant la saisie */
    let slugT = null;
    $('mvSlug').addEventListener('input', e => {
        const msg = $('mvSlugMsg'), v = e.target.value.trim();
        clearTimeout(slugT);
        if (!v) { msg.className = 'mv-msg'; msg.textContent = 'Facultatif · une adresse courte, facile à partager'; return; }
        msg.className = 'mv-msg'; msg.textContent = 'Vérification…';
        slugT = setTimeout(async () => {
            try {
                const r = await fetch(@json(route('boutique.storefront.slug')) + '?slug=' + encodeURIComponent(v), { headers: { 'Accept': 'application/json' } });
                const j = await r.json();
                msg.className = 'mv-msg ' + (j.ok ? 'is-ok' : 'is-err');
                msg.textContent = j.ok ? 'Disponible : ' + j.url.replace(/^https?:\/\//, '') : j.message;
            } catch (x) { msg.className = 'mv-msg'; msg.textContent = 'Vérification impossible pour le moment.'; }
        }, 350);
    });

    /* Copier le lien */
    $('mvCopy').addEventListener('click', async () => {
        try { await navigator.clipboard.writeText($('mvUrl').value); } catch (x) { $('mvUrl').select(); document.execCommand && document.execCommand('copy'); }
        $('mvCopy').textContent = 'Copié'; setTimeout(() => { $('mvCopy').textContent = 'Copier'; }, 1800);
    });

    /* Modifications non enregistrées */
    let changed = false;
    function dirty() { if (changed) return; changed = true; const s = $('mvState'); s.textContent = 'Modifications non enregistrées'; s.classList.add('is-dirty'); }
    form.addEventListener('input', e => { if (e.target.id !== 'mvUrl') dirty(); });
    form.addEventListener('change', dirty);
    form.addEventListener('submit', () => { changed = false; $('mvSave').disabled = true; $('mvSave').textContent = 'Enregistrement…'; });
    window.addEventListener('beforeunload', e => { if (changed) { e.preventDefault(); e.returnValue = ''; } });
    setColor($('mvAccent').value); changed = false; $('mvState').classList.remove('is-dirty'); $('mvState').textContent = @json('Tout est enregistré · ' . $host);
})();
</script>
@endpush
