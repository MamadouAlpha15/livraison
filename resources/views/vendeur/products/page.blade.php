{{--
    resources/views/vendeur/products/page.blade.php
    Route     : GET /products/{product}/page-de-vente → Vendeur\ProductPageController@edit → name('products.page.edit')
    Rôle      : éditeur produit, onglets Infos · Sections · Couleurs · Formulaire · Offres, avec l'aperçu en direct
                de la vraie page produit (?apercu=1, brouillon compris). Sections : bloc d'achat (ordre, affichage,
                espacement, textes) et sections de la page (contenu du produit, sections ajoutées de la bibliothèque).
                « Sauvegarder » envoie tout en JSON → ProductPageController@update. Contenu dans products.landing,
                réglages dans products.landing['design'] (App\Support\ProductDesign), formulaire de la boutique
                dans shops.storefront['form'].
    Variables : $shop, $product, $l, $design, $ownPage, $ownForm, $unit, $devise, $photos, $products, $images,
                $categories, $rating, $publicUrl
--}}
@extends('layouts.app')
@section('title', 'Éditeur produit · ' . $product->name)
@php
    use App\Support\HomeSections as HS;
    use App\Support\ProductDesign as PD;
    $bodyClass = 'is-dashboard';
    $blk = fn ($k, array $keys) => array_merge(array_fill_keys($keys, ''), array_intersect_key(is_array($l[$k] ?? null) ? $l[$k] : [], array_flip($keys)));
    $content = [
        'hook' => (string) ($l['hook'] ?? ''), 'benefits' => array_values($l['benefits'] ?? []), 'promise' => (string) ($l['promise'] ?? ''),
        'packs' => array_values($l['packs'] ?? []),
        'usage' => (string) ($l['usage'] ?? ''), 'details' => (string) ($l['details'] ?? ''), 'shipping' => (string) ($l['shipping'] ?? ''),
        'highlight' => $blk('highlight', ['title', 'text', 'cta']), 'story' => $blk('story', ['title', 'kicker', 'text']), 'audience' => $blk('audience', ['title', 'kicker', 'text']),
        'love' => $blk('love', ['title', 'kicker']) + ['items' => array_values($l['love']['items'] ?? [])],
        'compare' => $blk('compare', ['title']) + ['rows' => array_values($l['compare']['rows'] ?? [])],
        'faq' => array_values($l['faq'] ?? []),
    ];
    $peData = [
        'product' => ['id' => $product->id, 'name' => $product->name, 'price' => $unit, 'old' => $product->original_price ? (int) $product->original_price : null,
            'stock' => $product->stock, 'category' => (string) $product->category, 'variants' => (bool) $product->has_variants, 'active' => (bool) $product->is_active],
        'content' => $content, 'design' => $design, 'ownPage' => $ownPage, 'ownForm' => $ownForm,
        'photos' => $photos, 'products' => $products, 'rating' => $rating, 'currency' => $devise, 'unit' => $unit,
        'buy' => PD::BUY, 'fonts' => PD::FONTS, 'anims' => PD::ANIMS, 'intensities' => PD::INTENSITIES, 'spaceMax' => PD::SPACE_MAX,
        // Pour les champs des sections ajoutées (même rendu que Boutique → Sections)
        'theme' => 'product', 'types' => HS::TYPES, 'fields' => HS::FIELDS, 'builtin' => ['product' => HS::BUILTIN['product']], 'addable' => HS::ADDABLE,
        'library' => HS::CATEGORIES, 'defaults' => HS::DEFAULTS, 'tones' => HS::TONES, 'styles' => HS::STYLES, 'formats' => HS::FORMATS,
        'icons' => HS::ICONS, 'testimonial' => HS::TESTIMONIAL_TYPES, 'images' => (object) $images, 'categories' => $categories,
        'limits' => ['custom' => HS::CUSTOM_MAX, 'list' => HS::LIST_MAX, 'packs' => \App\Support\ProductPage::PACKS_MAX],
        'isNew' => request()->boolean('nouveau'),
        // Shopio IA : sortes de sections dont l'IA sait écrire les textes (App\Services\SectionAi), plan Pro
        'aiTypes' => \App\Services\SectionAi::writableTypes(), 'aiPro' => $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture(),
        // Photos Shopio IA : sections avec photo, produits à mettre en scène (ce produit en premier)
        'aiImageTypes' => array_keys(\App\Services\SectionImageAi::TYPES), 'aiShots' => \App\Services\SectionImageAi::choices($shop, $product),
        'urls' => [
            'save' => route('products.page.update', $product), 'ai' => route('products.page.ai', $product), 'sectionsAi' => route('products.page.sections-ai', $product), 'aiImage' => route('products.page.section-image', $product),
            'photoAi' => route('products.photos.ai', $product), 'photoAttach' => route('products.photos.attach', $product), 'toggle' => route('products.toggle', $product),
            'upload' => route('boutique.storefront.cover-upload'), 'preview' => $publicUrl, 'edit' => route('products.edit', $product),
        ],
    ];
@endphp

@push('styles')
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
@include('vendeur.shop._sx-style')
<style>
.pe { font-family: var(--sx-font); color: var(--sx-ink); font-size: 14.5px; }
.pe *, .pe *::before, .pe *::after { box-sizing: border-box; }
.pe :focus-visible { outline: 3px solid #7DB4FF; outline-offset: 2px; }
/* En-tête */
.pe-top { position: sticky; top: 0; z-index: 50; background: var(--sx-surf); border-bottom: 1px solid var(--sx-line); }
.pe-top-in { max-width: 1560px; margin: 0 auto; padding: 10px 16px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.pe-back { display: inline-flex; align-items: center; gap: 6px; color: var(--sx-ink2); font-weight: 700; text-decoration: none; font-size: 13.5px; }
.pe-switch { display: flex; align-items: center; gap: 8px; flex: 1; justify-content: center; min-width: 0; }
.pe-switch img, .pe-switch i { width: 30px; height: 30px; border-radius: 8px; object-fit: cover; background: var(--sx-soft); flex-shrink: 0; }
.pe-switch select { flex: 0 1 280px; min-width: 0; max-width: 280px; min-height: 38px; border: 1px solid var(--sx-line); border-radius: 10px; padding: 0 10px; background: var(--sx-surf); color: var(--sx-ink); font: 700 13.5px var(--sx-font); }
.pe-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; background: #FEF3C7; color: #78350F; }
.pe-pill::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.pe-pill.is-on { background: var(--sx-ok50); color: var(--sx-ok); }
.pe-state { font-size: 12.5px; font-weight: 700; color: var(--sx-ink3); }
.pe-state.is-dirty { color: var(--sx-ambi); } .pe-state.is-err { color: var(--sx-err); } .pe-state.is-ok { color: var(--sx-ok); }
.pe-btn-pub { background: #F59E0B; border-color: #F59E0B; color: #16162A; }
.pe-btn-pub.is-off { background: var(--sx-surf); border-color: var(--sx-line); color: var(--sx-ink); }
/* Corps */
.pe-layout { max-width: 1560px; margin: 0 auto; padding: 14px 16px 60px; display: grid; grid-template-columns: minmax(0, 470px) minmax(0, 1fr); gap: 18px; align-items: start; }
.pe-layout > * { min-width: 0; }
.pe-tabs { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 4px; padding: 4px; border-radius: 14px; background: var(--sx-surf); border: 1px solid var(--sx-line); position: sticky; top: 64px; z-index: 5; }
.pe-tabs button { min-height: 40px; border: 0; border-radius: 10px; background: none; color: var(--sx-ink2); font: 700 13px var(--sx-font); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.pe-tabs button[aria-selected="true"] { background: var(--sx-night); color: #fff; }
.pe-tabs small { font-size: 10.5px; padding: 1px 6px; border-radius: 999px; background: var(--sx-b50); color: var(--sx-blue); }
.pe-tabs button[aria-selected="true"] small { background: rgba(255,255,255,.18); color: #fff; }
.pe-pane { display: grid; gap: 12px; margin-top: 12px; }
.pe-pane[hidden] { display: none; }
.pe-card { background: var(--sx-surf); border: 1px solid var(--sx-line); border-radius: 16px; padding: 14px; display: grid; gap: 12px; }
.pe-card h3 { margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--sx-ink3); }
.pe-card h3 + p { margin-top: -6px; }
.pe-sub { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 4px; padding: 4px; border-radius: 12px; background: var(--sx-soft); }
.pe-sub button { min-height: 34px; border: 0; border-radius: 9px; background: none; font: 700 12.5px var(--sx-font); color: var(--sx-ink2); cursor: pointer; }
.pe-sub button[aria-pressed="true"] { background: var(--sx-surf); color: var(--sx-ink); box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.pe-gal { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
.pe-gal i { aspect-ratio: 1; border-radius: 10px; background: var(--sx-soft) center / cover; border: 1px solid var(--sx-line); position: relative; }
.pe-gal i:first-child::after { content: 'Principale'; position: absolute; left: 4px; top: 4px; padding: 1px 6px; border-radius: 6px; background: var(--sx-night); color: #fff; font-size: 10px; font-weight: 800; font-style: normal; }
.pe-kv { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 6px 14px; font-size: 13.5px; }
.pe-kv dt { color: var(--sx-ink3); font-weight: 600; } .pe-kv dd { margin: 0; font-weight: 700; }
.pe-sw { position: relative; flex: 0 0 auto; width: 44px; height: 26px; }
.pe-sw input { position: absolute; inset: 0; opacity: 0; margin: 0; cursor: pointer; z-index: 1; }
.pe-sw input:disabled { cursor: not-allowed; }
.pe-sw span { position: absolute; inset: 0; border-radius: 999px; background: #CBD5E1; transition: background-color 180ms; }
.pe-sw span::after { content: ''; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform 200ms var(--sx-out); }
.pe-sw input:checked + span { background: var(--sx-blue); }
.pe-sw input:checked + span::after { transform: translateX(18px); }
.pe-sw input:disabled + span { opacity: .45; }
.pe-sw input:focus-visible + span { outline: 3px solid #7DB4FF; outline-offset: 2px; }
.pe-line { display: flex; align-items: center; gap: 12px; }
.pe-line > div { flex: 1; min-width: 0; }
.pe-line b { display: block; font-size: 14px; }
.pe-line small { display: block; color: var(--sx-ink3); font-size: 12px; margin-top: 1px; }
.pe-tag { font-size: 10.5px; font-weight: 700; color: var(--sx-ink3); padding: 1px 6px; border-radius: 6px; background: var(--sx-soft); margin-left: 4px; }
.pe-range { display: grid; grid-template-columns: 48px minmax(0, 1fr) 46px; align-items: center; gap: 8px; font-size: 12.5px; color: var(--sx-ink2); font-weight: 600; }
.pe-range input { width: 100%; accent-color: var(--sx-blue); }
.pe-range output { text-align: right; font-variant-numeric: tabular-nums; }
.pe-space { display: grid; gap: 6px; padding: 10px; border-radius: 12px; background: var(--sx-soft); }
.pe-color { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.pe-color input[type=color] { width: 38px; height: 34px; border: 1px solid var(--sx-line); border-radius: 9px; padding: 2px; background: var(--sx-surf); cursor: pointer; }
.pe-color code { font-size: 12.5px; color: var(--sx-ink2); }
.pe-grid2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
.pe-choice { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
.pe-choice button { min-height: 40px; border: 1px solid var(--sx-line); border-radius: 10px; background: var(--sx-surf); color: var(--sx-ink); font: 700 13px var(--sx-font); cursor: pointer; }
.pe-choice button[aria-pressed="true"] { background: var(--sx-blue); border-color: var(--sx-blue); color: #fff; }
.pe-tier { display: grid; grid-template-columns: 118px minmax(0, 1fr) minmax(0, 1fr) auto; gap: 8px; align-items: end; padding: 10px; border-radius: 12px; background: var(--sx-soft); }
.pe-tier output { grid-column: 1 / -1; font-size: 12.5px; font-weight: 700; color: var(--sx-ok); }
.pe-tier output.is-bad { color: var(--sx-err); }
.pe-pre { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: var(--sx-ink2); }
.pe-ai { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 12px; border-radius: 14px; background: linear-gradient(135deg, #EAF2FF, #F3E8FF); }
.pe-ai p { flex: 1 1 200px; margin: 0; font-size: 12.5px; color: var(--sx-ink2); line-height: 1.45; }
.pe-ai .sx-btn { background: linear-gradient(135deg, #1668D8, #7C3AED); border: 0; color: #fff; }
.pe-ai p.is-err { color: var(--sx-err); font-weight: 700; } .pe-ai p.is-ok { color: var(--sx-ok); font-weight: 700; }
.pe-welcome { padding: 12px 14px; border-radius: 14px; background: var(--sx-ok50); font-size: 13.5px; line-height: 1.5; }
/* Aperçu */
.pe-preview { position: sticky; top: 64px; display: grid; gap: 10px; }
.pe-preview-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.pe-stage { border-radius: 18px; background: var(--sx-stage); padding: 14px; display: flex; justify-content: center; height: calc(100vh - 140px); min-height: 520px; overflow: hidden; min-width: 0; }
.pe-frame { background: #fff; border-radius: 22px; overflow: hidden; position: relative; height: 100%; box-shadow: 0 0 0 7px #0F172A, 0 30px 60px -30px rgba(15,23,42,.6); }
.pe-frame.is-phone { width: min(390px, 100%); }
.pe-frame.is-desk { width: 100%; border-radius: 12px; box-shadow: 0 20px 40px -24px rgba(11,30,61,.5); }
.pe-frame iframe { border: 0; display: block; width: 100%; height: 100%; background: #fff; }
.pe-frame.is-desk iframe { width: 1280px; transform-origin: 0 0; }
.pe-loading { position: absolute; inset: 0; display: grid; place-items: center; background: rgba(255,255,255,.7); font-weight: 700; color: var(--sx-ink2); opacity: 0; pointer-events: none; transition: opacity 200ms; }
.pe-frame.is-loading .pe-loading { opacity: 1; }
/* Intégré dans « Modifier la page » : la barre de l'éditeur de la vitrine remplace l'en-tête */
.pe.is-embedded .pe-top { display: none; }
.pe.is-embedded .pe-layout { max-width: none; padding: 12px; grid-template-columns: minmax(0, 420px) minmax(0, 1fr); }
.pe.is-embedded .pe-tabs { top: 0; }
.pe.is-embedded .pe-preview { top: 12px; }
.pe.is-embedded .pe-stage { height: calc(100vh - 82px); }
@media (max-width: 1100px) {
    .pe-layout, .pe.is-embedded .pe-layout { grid-template-columns: minmax(0, 1fr); }
    .pe-preview { position: static; }
    .pe-stage { height: 680px; }
}
@media (max-width: 560px) {
    .pe-tabs { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .pe-switch { order: 5; flex-basis: 100%; justify-content: flex-start; }
    .pe-switch select { flex: 1 1 auto; max-width: none; }
    .pe-grid2, .pe-tier { grid-template-columns: minmax(0, 1fr); }
    .pe-stage { padding: 8px; height: 600px; }
}
</style>
@endpush

@section('content')
<div class="pe{{ !empty($embedded) ? ' is-embedded' : '' }}" id="pe">
    <header class="pe-top"><div class="pe-top-in">
        <a class="pe-back" href="{{ route('products.index') }}">← Produits</a>
        <div class="pe-switch">
            @if(count($photos))<img src="{{ $photos[0] }}" alt="">@else<i></i>@endif
            <label class="sx-sr" for="peProduct">Produit à modifier</label>
            <select id="peProduct">@foreach($products as $pp)<option value="{{ $pp['url'] }}" @selected($pp['id'] === $product->id)>{{ \Illuminate\Support\Str::limit($pp['name'], 40) }}{{ $pp['active'] ? '' : ' (brouillon)' }}</option>@endforeach</select>
            <span class="pe-pill {{ $product->is_active ? 'is-on' : '' }}" id="peStatus">{{ $product->is_active ? 'Publié' : 'Brouillon' }}</span>
        </div>
        <span class="pe-state" id="peState" aria-live="polite">Tout est enregistré</span>
        @if($publicUrl)<a class="sx-btn sx-btn-sm" href="{{ $publicUrl }}" target="_blank" rel="noopener">Voir ↗</a>@endif
        <button type="button" class="sx-btn sx-btn-sm" id="peSave">Sauvegarder</button>
        <button type="button" class="sx-btn sx-btn-sm pe-btn-pub {{ $product->is_active ? 'is-off' : '' }}" id="pePublish">{{ $product->is_active ? 'Dépublier' : 'Publier' }}</button>
    </div></header>

    <div class="pe-layout">
        <div>
            <nav class="pe-tabs" role="tablist" aria-label="Réglages du produit">
                <button type="button" role="tab" data-tab="infos" aria-selected="false">Infos</button>
                <button type="button" role="tab" data-tab="sections" aria-selected="true">Sections <small id="peCount"></small></button>
                <button type="button" role="tab" data-tab="couleurs" aria-selected="false">Couleurs</button>
                <button type="button" role="tab" data-tab="formulaire" aria-selected="false">Formulaire</button>
                <button type="button" role="tab" data-tab="offres" aria-selected="false">Offres</button>
            </nav>
            @if(request()->boolean('nouveau'))
            <div class="pe-welcome" role="status" style="margin-top:12px"><b>🎉 « {{ $product->name }} » est créé, en brouillon.</b> Relisez les textes et les packs, puis touchez « Publier ». <span id="peWarn" hidden style="display:block;color:#92400E;font-weight:700"></span></div>
            @endif
            <section class="pe-pane" data-pane="infos" hidden></section>
            <section class="pe-pane" data-pane="sections"></section>
            <section class="pe-pane" data-pane="couleurs" hidden></section>
            <section class="pe-pane" data-pane="formulaire" hidden></section>
            <section class="pe-pane" data-pane="offres" hidden></section>
        </div>

        <aside class="pe-preview" aria-label="Aperçu">
            <div class="pe-preview-bar">
                <div class="sx-seg" role="group" aria-label="Appareil"><button type="button" data-dev="phone" aria-pressed="true">Mobile</button><button type="button" data-dev="desk" aria-pressed="false">Ordinateur</button></div>
                <span class="sx-hint">L’aperçu montre la dernière version sauvegardée.</span>
                <button type="button" class="sx-btn sx-btn-sm" id="peReload">Recharger</button>
            </div>
            <div class="pe-stage">
                <div class="pe-frame is-phone is-loading" id="peFrame"><iframe id="peIframe" title="Aperçu de la page produit"></iframe><div class="pe-loading">Chargement…</div></div>
            </div>
        </aside>
    </div>
    <input type="file" id="peFile" accept="image/jpeg,image/png,image/webp" class="sx-sr" tabindex="-1">
</div>
@endsection

@push('scripts')
@include('vendeur.shop._section-fields')
@include('vendeur.products._page-script')
@endpush
