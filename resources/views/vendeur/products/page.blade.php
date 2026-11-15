{{--
    Produits → Modifier → « Page de vente » : contenu de la page produit longue (modèle Express).
    Accroche, avantages, promesse de délai, packs, volets, sections de vente, comparatif, questions.
    Tout est facultatif : une section vide n'apparaît pas sur la page. Enregistré dans products.landing.
    Variables : $shop, $product, $l (contenu actuel), $unit (prix unitaire), $devise, $publicUrl
--}}
@extends('layouts.app')
@section('title', 'Page de vente · ' . $product->name)
@php
    $bodyClass = 'is-dashboard';
    $v = fn (string $path, $default = '') => (string) old($path, data_get($l, $path, $default));
    $packs = array_values($l['packs'] ?? []);
    $faq = array_values($l['faq'] ?? []);
    $faqRows = max(6, min(10, count($faq) + 1));
    $loveItems = collect($l['love']['items'] ?? [])->map(fn ($i) => trim(($i['title'] ?? '') . (($i['text'] ?? '') !== '' ? ' : ' . $i['text'] : '')))->implode("\n");
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --bg: #f8fafc; --surface: #fff; --border: #e2e8f0; --text: #0f172a; --text-2: #475569; --muted: #64748b;
    --font: 'Plus Jakarta Sans', sans-serif;
}
[data-theme="dark"] { --bg: #0b0f1a; --surface: #141925; --border: #262d3d; --text: #e6e9f0; --text-2: #b0b8c9; --muted: #8b95aa; }
.pg-wrap *, .pg-wrap *::before, .pg-wrap *::after, .pg-bar, .pg-bar * { box-sizing: border-box; }
.pg-top { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; position: sticky; top: 0; z-index: 30; padding: 14px 28px !important; }
.pg-top .tb-info { flex: 1 1 240px; min-width: 0; }
.pg-crumb { display: flex; gap: 6px; font-size: 13px; color: var(--muted); }
.pg-crumb a { color: var(--sv-btn, #1668D8); font-weight: 700; text-decoration: none; }
.pg-title { margin: 2px 0 0; font: 800 22px var(--font); letter-spacing: -.02em; color: var(--text); }
.pg-pill { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 18px; border: 0; border-radius: 999px; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); font: 700 14px var(--font); text-decoration: none; cursor: pointer; }
.pg-pill:hover { box-shadow: inset 0 0 0 1.5px var(--sv-blue, #1F7CF6); color: var(--sv-btn, #1668D8); }
.pg-pill.is-main { background: var(--sv-btn, #1668D8); color: #fff; box-shadow: none; }
.pg-pill.is-main:hover { background: var(--sv-btn-h, #1260C9); color: #fff; }
.pg-wrap { max-width: 920px; padding: 24px 28px 120px; font-family: var(--font); color: var(--text); }
.pg-ai { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; margin: 0 0 18px; }
.pg-ai .pg-help { flex: 1 1 260px; margin: 0; color: var(--muted); font-size: 13px; line-height: 1.45; }
.pg-ai .pg-help.is-ok { color: var(--sv-ok, #15803D); font-weight: 700; }
.pg-ai .pg-help.is-err { color: var(--sv-err, #DC2626); font-weight: 700; }
.pg-pill.is-ai { background: linear-gradient(135deg, #1668D8, #7C3AED); color: #fff; box-shadow: none; }
.pg-pill.is-ai:hover { color: #fff; filter: brightness(1.08); }
.pg-pill.is-ai[aria-busy="true"] { opacity: .7; cursor: progress; }
.pg-intro { margin: 0 0 18px; padding: 14px 18px; border-radius: 18px; background: var(--sv-b50, #EAF2FF); color: var(--text); font-size: 14px; line-height: 1.55; }
.pg-flash { margin: 0 0 18px; padding: 12px 16px; border-radius: 14px; font-weight: 700; font-size: 14px; }
.pg-flash.is-ok { background: var(--sv-ok50, #E7F6EC); color: var(--sv-ok, #15803D); }
.pg-flash.is-err { background: var(--sv-err50, #FDECEC); color: var(--sv-err, #B91C1C); }
.pg-card { margin-bottom: 18px; border-radius: 24px; background: var(--surface); box-shadow: 0 0 0 1px var(--border); }
.pg-card > summary { display: flex; align-items: center; gap: 12px; padding: 18px 22px; cursor: pointer; list-style: none; }
.pg-card > summary::-webkit-details-marker { display: none; }
.pg-card > summary::after { content: '+'; margin-left: auto; font-size: 22px; color: var(--muted); transition: transform 250ms; }
.pg-card[open] > summary::after { transform: rotate(45deg); }
.pg-card > summary b { display: block; font-size: 16px; font-weight: 800; }
.pg-card > summary small { display: block; margin-top: 2px; color: var(--muted); font-size: 13px; font-weight: 500; }
.pg-card > summary i { width: 38px; height: 38px; flex-shrink: 0; border-radius: 12px; background: var(--sv-b50, #EAF2FF); display: flex; align-items: center; justify-content: center; font-style: normal; font-size: 18px; }
.pg-body { display: grid; gap: 16px; padding: 0 22px 22px; }
.pg-field { display: flex; flex-direction: column; gap: 6px; }
.pg-field label { font-size: 13px; font-weight: 800; }
.pg-field .pg-help { margin: 0; color: var(--muted); font-size: 12.5px; line-height: 1.45; }
.pg-field input, .pg-field textarea, .pg-field select { width: 100%; min-height: 46px; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 14px; background: var(--bg); color: var(--text); font: 500 15px var(--font); }
.pg-field textarea { min-height: 96px; resize: vertical; line-height: 1.5; }
.pg-field input:focus, .pg-field textarea:focus, .pg-field select:focus { outline: 0; border-color: var(--sv-blue, #1F7CF6); box-shadow: 0 0 0 3px var(--sv-b50, #EAF2FF); }
.pg-field [aria-invalid="true"] { border-color: var(--sv-err, #DC2626); }
.pg-err { margin: 0; color: var(--sv-err, #DC2626); font-size: 13px; font-weight: 700; }
.pg-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
.pg-pack { display: grid; grid-template-columns: 120px minmax(0, 1fr) minmax(0, 1fr); gap: 10px; align-items: end; padding: 14px; border-radius: 16px; background: var(--bg); }
.pg-pack output { grid-column: 1 / -1; font-size: 13px; font-weight: 700; color: var(--sv-ok, #15803D); }
.pg-pack output.is-bad { color: var(--sv-err, #DC2626); }
.pg-faq { display: grid; grid-template-columns: minmax(0, .8fr) minmax(0, 1.2fr); gap: 10px; }
.pg-bar { position: fixed; z-index: 40; left: 0; right: 0; bottom: 0; display: flex; justify-content: flex-end; gap: 10px; padding: 12px max(20px, env(safe-area-inset-right, 0px)) calc(12px + env(safe-area-inset-bottom, 0px)); background: color-mix(in srgb, var(--surface) 92%, transparent); backdrop-filter: blur(10px); box-shadow: 0 -1px 0 var(--border); }
@media (min-width: 901px) { .pg-bar { left: 264px; } }
.pg-status { display: inline-flex; align-items: center; gap: 6px; min-height: 32px; padding: 0 12px; border-radius: 999px; background: var(--sv-amb50, #FEF3C7); color: var(--sv-amb-ink, #78350F); font: 800 12.5px var(--font); }
.pg-status::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
.pg-status.is-on { background: var(--sv-ok50, #E7F6EC); color: var(--sv-ok, #15803D); }
.pg-welcome { display: flex; flex-direction: column; gap: 6px; margin: 0 0 18px; padding: 16px 18px; border-radius: 18px; background: var(--sv-ok50, #E7F6EC); color: var(--text); font-size: 14px; line-height: 1.5; }
.pg-welcome a { color: var(--sv-btn, #1668D8); font-weight: 800; }
.pg-welcome-warn { color: var(--sv-amb-ink, #78350F); font-weight: 700; }
.pg-layout { display: block; }
.pg-preview { display: none; }
@media (min-width: 1280px) {
    .pg-layout { display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 8px; align-items: start; max-width: 1400px; }
    .pg-layout .pg-wrap { max-width: none; }
    .pg-preview { display: flex; flex-direction: column; gap: 10px; position: sticky; top: 88px; padding: 24px 28px 120px 0; font-family: var(--font); color: var(--text); }
}
.pg-preview-bar { display: flex; align-items: center; gap: 8px; }
.pg-preview-bar b { margin-right: auto; font-size: 14px; }
.pg-preview-bar button { min-height: 34px; padding: 0 12px; border: 0; border-radius: 999px; background: var(--surface); color: var(--text-2); box-shadow: inset 0 0 0 1px var(--border); font: 700 12.5px var(--font); cursor: pointer; }
.pg-preview-bar button[aria-pressed="true"] { background: var(--sv-btn, #1668D8); color: #fff; box-shadow: none; }
.pg-frame { position: relative; height: calc(100vh - 230px); min-height: 520px; border-radius: 28px; overflow: hidden; background: #fff; box-shadow: 0 0 0 8px #0F172A, 0 30px 60px -30px rgba(15,23,42,.6); }
.pg-frame iframe { width: 100%; height: 100%; border: 0; display: block; }
/* Ordinateur : la page en 1280 px de large, réduite pour tenir dans le cadre */
.pg-frame.is-desktop { border-radius: 14px; box-shadow: 0 0 0 1px var(--border), 0 30px 60px -30px rgba(15,23,42,.45); }
.pg-frame.is-desktop iframe { position: absolute; left: 0; top: 0; width: 1280px; transform-origin: 0 0; }
@media (max-width: 900px) { .pg-top { padding: 12px 16px !important; } .pg-wrap { padding: 18px 16px 120px; } }
@media (max-width: 600px) { .pg-pack, .pg-faq { grid-template-columns: minmax(0, 1fr); } .pg-top .pg-pill:not(.is-main) { display: none; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'products'])
    <main class="main vs-main">
        <div class="topbar pg-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
            <div class="tb-info">
                <nav class="pg-crumb" aria-label="Fil d'Ariane"><a href="{{ route('products.index') }}">Produits</a><span aria-hidden="true">/</span><a href="{{ route('products.edit', $product) }}">{{ \Illuminate\Support\Str::limit($product->name, 34) }}</a><span aria-hidden="true">/</span><span>Page de vente</span></nav>
                <h1 class="pg-title">Page de vente</h1>
            </div>
            <span class="pg-status {{ $product->is_active ? 'is-on' : '' }}" id="pgStatus" role="status">{{ $product->is_active ? 'Publié' : 'Brouillon' }}</span>
            <button type="button" class="pg-pill {{ $product->is_active ? '' : 'is-main' }}" id="pgPublish" data-active="{{ $product->is_active ? 1 : 0 }}">{{ $product->is_active ? 'Repasser en brouillon' : 'Publier le produit' }}</button>
            @if($publicUrl)<a class="pg-pill" href="{{ $publicUrl }}" target="_blank" rel="noopener">Voir la page ↗</a>@endif
        </div>

        <div class="pg-layout">
        <form class="pg-wrap" method="POST" action="{{ route('products.page.update', $product) }}" id="pgForm" novalidate>
            @csrf @method('PUT')
            @if(request()->boolean('nouveau'))
            <div class="pg-welcome" role="status">
                <b>🎉 « {{ $product->name }} » est créé, en brouillon.</b>
                <span>Relisez le nom, les textes et les packs (l’aperçu se met à jour à chaque enregistrement), puis touchez « Publier le produit ». <a href="{{ route('products.edit', $product) }}">Modifier le nom, le prix, le stock ou les photos</a></span>
                <span class="pg-welcome-warn" id="pgWarn" hidden></span>
            </div>
            @endif
            @if(session('success'))<p class="pg-flash is-ok" role="status">{{ session('success') }}</p>@endif
            @if($errors->any())<p class="pg-flash is-err" role="alert">Certains champs sont à corriger : ils sont signalés en rouge plus bas.</p>@endif
            <p class="pg-intro">Ces textes remplissent la page produit longue de « {{ $product->name }} » : accroche, packs, « Pour qui ? », comparatif, questions… <b>Tout est facultatif</b> : une section vide n’apparaît pas. Shopio IA peut aussi les remplir pour vous. Les avis et les chiffres de la page viennent toujours de vos vraies commandes.</p>
            <div class="pg-ai">
                <button type="button" class="pg-pill is-ai" id="pgAi"><span aria-hidden="true">✨</span> Remplir avec Shopio IA</button>
                <p class="pg-help" id="pgAiMsg" role="status" aria-live="polite">Shopio IA rédige toute la page à partir du nom, de la description et de la photo du produit. Vous relisez, corrigez, puis enregistrez.</p>
            </div>

            <details class="pg-card" open>
                <summary><i aria-hidden="true">✍️</i><span><b>Haut de la page</b><small>Accroche, avantages et délai, à côté des photos</small></span></summary>
                <div class="pg-body">
                    <div class="pg-field"><label for="pgHook">Accroche</label><textarea id="pgHook" name="hook" maxlength="220" rows="2" placeholder="Ex. Un sac solide et imperméable pour la ville et le voyage.">{{ $v('hook') }}</textarea><p class="pg-help">Une ou deux phrases sous le nom du produit. Vide : la première phrase de la description.</p>@error('hook')<p class="pg-err">{{ $message }}</p>@enderror</div>
                    <div class="pg-field"><label for="pgBen">Avantages</label><textarea id="pgBen" name="benefits" rows="5" placeholder="Un avantage par ligne (8 au plus)&#10;Tissu qui résiste à la pluie&#10;Poche pour ordinateur 15 pouces">{{ old('benefits', implode("\n", $l['benefits'] ?? [])) }}</textarea><p class="pg-help">Un avantage par ligne, court et concret. Chacun s’affiche avec une coche.</p></div>
                    <div class="pg-field"><label for="pgPromise">Promesse de délai</label><input id="pgPromise" name="promise" maxlength="90" value="{{ $v('promise') }}" placeholder="Ex. Commandez avant 18 h, livré demain à Conakry"><p class="pg-help">Seulement si vous la tenez vraiment. Vide : rien n’est promis, les 3 étapes restent affichées.</p>@error('promise')<p class="pg-err">{{ $message }}</p>@enderror</div>
                </div>
            </details>

            <details class="pg-card" open>
                <summary><i aria-hidden="true">🎁</i><span><b>Packs « Plus vous achetez, plus vous économisez »</b><small>Prix actuel à l’unité : {{ number_format($unit, 0, ',', ' ') }} {{ $devise }}</small></span></summary>
                <div class="pg-body">
                    <p class="pg-help" style="margin:0">Le prix d’un pack est le prix <b>total</b> pour toutes les pièces. Il doit être plus bas que l’achat à l’unité. Il s’applique aussi dans le panier et à la commande.</p>
                    @for($i = 0; $i < \App\Support\ProductPage::PACKS_MAX; $i++)
                    @php $k = $packs[$i] ?? []; @endphp
                    <div class="pg-pack" data-pack>
                        <div class="pg-field"><label for="pgPq{{ $i }}">Quantité</label><select id="pgPq{{ $i }}" name="packs[{{ $i }}][qty]" data-qty @error("packs.$i.qty") aria-invalid="true" @enderror><option value="">—</option>@for($q = 2; $q <= 10; $q++)<option value="{{ $q }}" @selected((int) old("packs.$i.qty", $k['qty'] ?? 0) === $q)>{{ $q }} produits</option>@endfor</select></div>
                        <div class="pg-field"><label for="pgPp{{ $i }}">Prix total du pack ({{ $devise }})</label><input id="pgPp{{ $i }}" name="packs[{{ $i }}][price]" inputmode="numeric" data-price value="{{ old("packs.$i.price", $k['price'] ?? '') }}" placeholder="Ex. {{ number_format($unit * 2 * .9, 0, '', '') }}" @error("packs.$i.price") aria-invalid="true" @enderror></div>
                        <div class="pg-field"><label for="pgPl{{ $i }}">Étiquette</label><input id="pgPl{{ $i }}" name="packs[{{ $i }}][label]" maxlength="20" value="{{ old("packs.$i.label", $k['label'] ?? '') }}" placeholder="{{ ['Populaire', 'Meilleure offre', ''][$i] }}"></div>
                        <output aria-live="polite"></output>
                        @error("packs.$i.qty")<p class="pg-err" style="grid-column:1/-1">{{ $message }}</p>@enderror
                        @error("packs.$i.price")<p class="pg-err" style="grid-column:1/-1">{{ $message }}</p>@enderror
                    </div>
                    @endfor
                </div>
            </details>

            <details class="pg-card" @if($v('usage') !== '' || $v('details') !== '' || $v('shipping') !== '') open @endif>
                <summary><i aria-hidden="true">📄</i><span><b>Volets d’information</b><small>« Comment l’utiliser ? », « Description détaillée », « Livraison et garantie »</small></span></summary>
                <div class="pg-body">
                    <div class="pg-field"><label for="pgUsage">Comment l’utiliser ?</label><textarea id="pgUsage" name="usage" maxlength="1500">{{ $v('usage') }}</textarea></div>
                    <div class="pg-field"><label for="pgDetails">Description détaillée</label><textarea id="pgDetails" name="details" maxlength="3000" placeholder="Vide : la description du produit">{{ $v('details') }}</textarea></div>
                    <div class="pg-field"><label for="pgShip">Livraison et garantie</label><textarea id="pgShip" name="shipping" maxlength="1500" placeholder="Vide : texte standard (appel de confirmation, paiement à la livraison, suivi)">{{ $v('shipping') }}</textarea></div>
                </div>
            </details>

            @foreach([
                ['highlight', '⭐', 'Bloc mis en avant', 'Grand titre, texte et bouton « Je le veux », avec vos photos inclinées', ['title' => ['Titre', 70, 'Ex. Qualité tactique professionnelle'], 'text' => ['Texte', 600, ''], 'cta' => ['Texte du bouton', 24, 'Je le veux']]],
                ['story', '🔁', 'Photo et texte (ex. « Avant / Après »)', 'Une photo du produit à côté d’un texte', ['title' => ['Titre', 80, 'Ex. Avant / Après : tout est rangé'], 'kicker' => ['Sous-titre', 140, ''], 'text' => ['Texte', 1200, '']]],
                ['audience', '🎯', '« C’est pour qui ? »', 'À qui le produit s’adresse', ['title' => ['Titre', 60, 'C’est pour qui ?'], 'kicker' => ['Sous-titre', 80, 'Ex. Votre équipement de tous les jours'], 'text' => ['Texte', 1200, '']]],
            ] as [$key, $ico, $title, $sub, $fields])
            <details class="pg-card" @if($v("$key.title") !== '') open @endif>
                <summary><i aria-hidden="true">{{ $ico }}</i><span><b>{{ $title }}</b><small>{{ $sub }} · s’affiche si le titre est rempli</small></span></summary>
                <div class="pg-body">
                    @foreach($fields as $f => [$label, $max, $ph])
                    <div class="pg-field"><label for="pg_{{ $key }}_{{ $f }}">{{ $label }}</label>
                        @if($f === 'text')<textarea id="pg_{{ $key }}_{{ $f }}" name="{{ $key }}[{{ $f }}]" maxlength="{{ $max }}">{{ old("$key.$f", data_get($l, "$key.$f", '')) }}</textarea>
                        @else<input id="pg_{{ $key }}_{{ $f }}" name="{{ $key }}[{{ $f }}]" maxlength="{{ $max }}" value="{{ old("$key.$f", data_get($l, "$key.$f", '')) }}" placeholder="{{ $ph }}">@endif
                        @error("$key.$f")<p class="pg-err">{{ $message }}</p>@enderror
                    </div>
                    @endforeach
                </div>
            </details>
            @endforeach

            <details class="pg-card" @if($loveItems !== '') open @endif>
                <summary><i aria-hidden="true">💚</i><span><b>« Pourquoi vous allez l’adorer »</b><small>Une liste de raisons, avec une photo · s’affiche si la liste est remplie</small></span></summary>
                <div class="pg-body">
                    <div class="pg-row">
                        <div class="pg-field"><label for="pgLoveT">Titre</label><input id="pgLoveT" name="love[title]" maxlength="80" value="{{ old('love.title', data_get($l, 'love.title', '')) }}" placeholder="Pourquoi vous allez l’adorer"></div>
                        <div class="pg-field"><label for="pgLoveK">Sous-titre</label><input id="pgLoveK" name="love[kicker]" maxlength="120" value="{{ old('love.kicker', data_get($l, 'love.kicker', '')) }}" placeholder="Ex. Confort et solidité au quotidien"></div>
                    </div>
                    <div class="pg-field"><label for="pgLoveI">Raisons</label><textarea id="pgLoveI" name="love_items" rows="5" placeholder="Une raison par ligne : « Titre : explication » (6 au plus)&#10;Confort toute la journée : dos rembourré et bretelles larges">{{ old('love_items', $loveItems) }}</textarea><p class="pg-help">Une ligne par raison. Mettez « : » entre le titre et l’explication.</p></div>
                </div>
            </details>

            <details class="pg-card" @if(count($l['compare']['rows'] ?? [])) open @endif>
                <summary><i aria-hidden="true">✅</i><span><b>Tableau « Notre produit / Autres »</b><small>Ce que vous garantissez · s’affiche si la liste est remplie</small></span></summary>
                <div class="pg-body">
                    <div class="pg-field"><label for="pgCmpT">Titre</label><input id="pgCmpT" name="compare[title]" maxlength="80" value="{{ old('compare.title', data_get($l, 'compare.title', '')) }}" placeholder="Pourquoi nous choisir ?"></div>
                    <div class="pg-field"><label for="pgCmpR">Points garantis</label><textarea id="pgCmpR" name="compare_rows" rows="5" placeholder="Un point par ligne (8 au plus)&#10;Qualité contrôlée avant l’envoi&#10;Paiement à la livraison">{{ old('compare_rows', implode("\n", $l['compare']['rows'] ?? [])) }}</textarea><p class="pg-help">Seulement ce que vous garantissez vraiment à chaque commande.</p></div>
                </div>
            </details>

            <details class="pg-card" @if(count($faq)) open @endif>
                <summary><i aria-hidden="true">❓</i><span><b>Questions sur ce produit</b><small>Vide : les questions habituelles (commande, paiement, livraison)</small></span></summary>
                <div class="pg-body">
                    @for($i = 0; $i < $faqRows; $i++)
                    <div class="pg-faq">
                        <div class="pg-field"><label for="pgFq{{ $i }}">Question {{ $i + 1 }}</label><input id="pgFq{{ $i }}" name="faq[{{ $i }}][q]" maxlength="140" value="{{ old("faq.$i.q", $faq[$i]['q'] ?? '') }}"></div>
                        <div class="pg-field"><label for="pgFa{{ $i }}">Réponse</label><textarea id="pgFa{{ $i }}" name="faq[{{ $i }}][a]" maxlength="600" rows="2">{{ old("faq.$i.a", $faq[$i]['a'] ?? '') }}</textarea></div>
                    </div>
                    @endfor
                </div>
            </details>

            <div class="pg-bar">
                <a class="pg-pill" href="{{ route('products.edit', $product) }}">Retour au produit</a>
                <button type="submit" class="pg-pill is-main">Enregistrer la page</button>
            </div>
        </form>
        @if($publicUrl)
        {{-- Aperçu en direct de la page produit (brouillon compris, visible du seul vendeur) --}}
        <aside class="pg-preview" aria-label="Aperçu de la page produit">
            <div class="pg-preview-bar">
                <b>Aperçu en direct</b>
                <div role="group" aria-label="Format de l’aperçu"><button type="button" data-size="mobile" aria-pressed="true">Mobile</button><button type="button" data-size="desktop" aria-pressed="false">Ordinateur</button></div>
                <button type="button" id="pgReload" aria-label="Recharger l’aperçu">↻</button>
            </div>
            <div class="pg-frame" id="pgFrame"><iframe id="pgIframe" data-src="{{ $publicUrl }}?apercu=1" title="Aperçu de la page produit"></iframe></div>
            <p class="pg-help">L’aperçu montre la dernière version enregistrée.</p>
        </aside>
        @endif
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
(function () {
    /* Packs : économie calculée pendant la saisie, comme sur la page publique */
    const unit = @json($unit), cur = @json($devise);
    const fmt = n => Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + cur;
    document.querySelectorAll('[data-pack]').forEach(row => {
        const q = row.querySelector('[data-qty]'), p = row.querySelector('[data-price]'), out = row.querySelector('output');
        function upd() {
            const n = +q.value, price = +String(p.value).replace(/\D/g, '');
            out.classList.remove('is-bad');
            if (!n || !price) { out.textContent = n ? 'À l’unité : ' + fmt(n * unit) + '. Indiquez le prix du pack.' : ''; return; }
            const full = n * unit, save = full - price;
            if (save <= 0) { out.classList.add('is-bad'); out.textContent = 'Trop cher : ' + n + ' à l’unité coûtent déjà ' + fmt(full) + '.'; return; }
            out.textContent = 'Le client économise ' + fmt(save) + ' (−' + Math.round(save / full * 100) + ' %), soit ' + fmt(price / n) + ' par produit.';
        }
        q.addEventListener('change', upd); p.addEventListener('input', upd); upd();
    });

    /* Aperçu en direct : mobile ou ordinateur (page en 1280 px réduite), rechargement à la demande */
    const frame = document.getElementById('pgFrame'), iframe = document.getElementById('pgIframe');
    if (frame) {
        // Chargé après la page : l'écran s'affiche sans attendre l'aperçu (seulement s'il est visible à l'écran)
        const start = () => { if (!iframe.getAttribute('src') && window.matchMedia('(min-width: 1280px)').matches) iframe.src = iframe.dataset.src; };
        if (document.readyState === 'complete') setTimeout(start, 50); else window.addEventListener('load', () => setTimeout(start, 50));
        window.addEventListener('resize', start);
        const fit = () => {
            if (!frame.classList.contains('is-desktop')) { iframe.style.transform = ''; iframe.style.height = ''; return; }
            const k = frame.clientWidth / 1280;
            iframe.style.transform = 'scale(' + k + ')'; iframe.style.height = (frame.clientHeight / k) + 'px';
        };
        document.querySelectorAll('[data-size]').forEach(b => b.addEventListener('click', () => {
            document.querySelectorAll('[data-size]').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
            frame.classList.toggle('is-desktop', b.dataset.size === 'desktop'); fit();
        }));
        window.addEventListener('resize', fit);
        document.getElementById('pgReload').addEventListener('click', () => { iframe.src = iframe.dataset.src + '&t=' + Date.now(); });
    }

    /* Publier / repasser en brouillon (même action que la liste des produits) */
    const pub = document.getElementById('pgPublish'), status = document.getElementById('pgStatus');
    pub.addEventListener('click', async () => {
        pub.disabled = true;
        try {
            const r = await fetch(@json(route('products.toggle', $product)), { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
            const j = await r.json();
            if (!r.ok) throw new Error();
            status.textContent = j.active ? 'Publié' : 'Brouillon'; status.classList.toggle('is-on', j.active);
            pub.textContent = j.active ? 'Repasser en brouillon' : 'Publier le produit'; pub.classList.toggle('is-main', !j.active);
            if (iframe && iframe.src) iframe.src = iframe.dataset.src + '&t=' + Date.now();
        } catch (e) { alert('Le statut n’a pas pu être changé. Réessayez.'); }
        pub.disabled = false;
    });

    /* Après « Créer avec Shopio IA » : avertissement éventuel (IA indisponible…) */
    try { const w = sessionStorage.getItem('wzWarning'); if (w && document.getElementById('pgWarn')) { const el = document.getElementById('pgWarn'); el.textContent = w; el.hidden = false; } sessionStorage.removeItem('wzWarning'); } catch (e) {}

    /* « Remplir avec Shopio IA » : remplit le formulaire, rien n'est enregistré avant « Enregistrer la page » */
    const form = document.getElementById('pgForm'), btn = document.getElementById('pgAi'), msg = document.getElementById('pgAiMsg');
    const field = name => form.elements.namedItem(name);
    const set = (name, value) => { const el = field(name); if (el) { el.value = value == null ? '' : value; el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true })); } };
    const say = (text, cls) => { msg.textContent = text; msg.className = 'pg-help' + (cls ? ' ' + cls : ''); };
    const filled = () => ['hook', 'benefits', 'usage', 'highlight[title]', 'story[title]', 'audience[title]', 'love_items', 'compare_rows', 'faq[0][q]'].some(n => field(n) && field(n).value.trim() !== '');
    btn.addEventListener('click', async () => {
        if (filled() && !confirm('Shopio IA va remplacer les textes déjà saisis (les packs déjà remplis sont gardés). Continuer ?')) return;
        btn.disabled = true; btn.setAttribute('aria-busy', 'true');
        say('Shopio IA rédige votre page… (environ 30 secondes)');
        try {
            const r = await fetch(@json(route('products.page.ai', $product)), { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
            const j = await r.json().catch(() => ({}));
            if (!r.ok || !j.landing) { say(j.error || (r.status === 429 ? 'Trop d’essais : patientez une minute.' : 'Shopio IA n’a pas pu rédiger la page. Réessayez.'), 'is-err'); return; }
            const L = j.landing;
            set('hook', L.hook); set('benefits', (L.benefits || []).join('\n'));
            set('usage', L.usage); set('details', L.details);
            ['highlight', 'story', 'audience'].forEach(b => Object.entries(L[b] || {}).forEach(([k, v]) => set(b + '[' + k + ']', v)));
            set('love[title]', L.love.title); set('love[kicker]', L.love.kicker);
            set('love_items', (L.love.items || []).map(i => i.title + (i.text ? ' : ' + i.text : '')).join('\n'));
            set('compare[title]', L.compare.title); set('compare_rows', (L.compare.rows || []).join('\n'));
            let i = 0;
            for (; field('faq[' + i + '][q]'); i++) { const f = (L.faq || [])[i] || { q: '', a: '' }; set('faq[' + i + '][q]', f.q); set('faq[' + i + '][a]', f.a); }
            // Packs : suggestion seulement si aucun pack n'est encore rempli — le vendeur garde la main sur ses prix
            const packsEmpty = [0, 1, 2].every(k => !field('packs[' + k + '][qty]') || !field('packs[' + k + '][qty]').value);
            if (packsEmpty) (j.packs || []).forEach((k, n) => { set('packs[' + n + '][qty]', k.qty); set('packs[' + n + '][price]', k.price); set('packs[' + n + '][label]', k.label); });
            form.querySelectorAll('details.pg-card').forEach(d => { d.open = true; });
            say('Page rédigée ' + (packsEmpty && (j.packs || []).length ? '(avec des packs suggérés à −10 % et −20 %) ' : '') + ': relisez, corrigez si besoin, puis touchez « Enregistrer la page ».', 'is-ok');
        } catch (e) {
            say('Connexion impossible : vérifiez votre internet puis réessayez.', 'is-err');
        } finally { btn.disabled = false; btn.removeAttribute('aria-busy'); }
    });
})();
</script>
@endpush
