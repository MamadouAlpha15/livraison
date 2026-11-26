{{--
    resources/views/vendeur/shop/sections.blade.php
    Route     : GET /boutique/vitrine/sections → Vendeur\StorefrontController@sections → name('boutique.storefront.sections')
    Rôle      : « Boutique → Sections » — les sections de la page d'accueil, pour chaque thème : modifier les textes,
                masquer, déplacer (glisser ou flèches), ajouter (texte, image et texte, bannière, catégorie, questions),
                supprimer. Aperçu en direct de la vraie vitrine (?modele=…&apercu=1) rechargé à chaque enregistrement.
                « Enregistrer et publier » → StorefrontController@saveSections (JSON).
    Variables : $shop, $theme, $published, $home, $models, $defaults, $images, $categories, $publicUrl
--}}
@extends('layouts.app')
@section('title', 'Sections des pages · ' . $shop->name)
@php
    use App\Support\HomeSections as HS;
    $bodyClass = 'is-dashboard';
    $isProduct = $theme === 'product';
    $sxData = [
        'theme' => $theme, 'published' => $published, 'home' => $home, 'models' => $models, 'isProduct' => $isProduct, 'products' => $previewProducts,
        'types' => HS::TYPES, 'fields' => HS::FIELDS, 'builtin' => HS::BUILTIN, 'addable' => HS::ADDABLE,
        'defaults' => $defaults, 'tones' => HS::TONES, 'styles' => HS::STYLES, 'formats' => HS::FORMATS, 'images' => (object) $images, 'categories' => $categories,
        'library' => HS::CATEGORIES, 'icons' => HS::ICONS, 'testimonial' => HS::TESTIMONIAL_TYPES,
        'limits' => ['custom' => HS::CUSTOM_MAX, 'list' => HS::LIST_MAX],
        // Shopio IA : textes réécrits pour les sections qui en ont (jamais les témoignages ni les chiffres)
        'ai' => $ai + ['writable' => collect(array_keys(HS::TYPES))->filter(fn ($t) => \App\Services\SectionsAi::writable($t))->values()],
        'urls' => [
            'save' => route('boutique.storefront.sections.update'), 'upload' => route('boutique.grow.cover-upload'),
            'ai' => route('boutique.storefront.sections.ai'), 'upgrade' => route('boutique.subscription.upgrade'),
            'preview' => $publicUrl, 'self' => route('boutique.storefront.sections'),
        ],
    ];
@endphp

@push('styles')
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
@include('vendeur.shop._sx-style')
@endpush

@section('content')
<div class="sx" id="sx">
    <header class="sx-top"><div class="sx-top-in">
        <div>
            <h1>{{ $isProduct ? 'Sections de la page produit' : 'Sections de la page d’accueil' }}</h1>
            <p>{{ $isProduct ? 'Une seule mise en page pour tous vos produits : déplacez, masquez ou ajoutez des sections. Les textes de chaque produit se règlent dans sa « Page de vente ».' : 'Modifiez les textes, masquez, déplacez ou ajoutez des sections. L’aperçu montre votre vraie vitrine.' }}</p>
        </div>
        <nav class="sx-links" aria-label="Autres pages">
            <a class="sx-btn sx-btn-sm" href="{{ $publicUrl }}" target="_blank" rel="noopener">Voir ma vitrine ↗</a>
            <a class="sx-btn sx-btn-sm" href="{{ route('boutique.storefront.edit') }}">Ma vitrine</a>
            <a class="sx-btn sx-btn-sm" href="{{ route('boutique.grow') }}">Boutique</a>
        </nav>
    </div></header>

    <div class="sx-layout">
        <div class="sx-panel">
            <nav class="sx-themes" aria-label="Page à modifier">
                @foreach($pages as $k => [$label])
                <a href="{{ route('boutique.storefront.sections', ['theme' => $k]) }}" @if($k === $theme) aria-current="page" @endif data-theme-link>{{ $label }}@if($k === $published)<small>en ligne</small>@endif</a>
                @endforeach
            </nav>
            @if($isProduct)
            <p class="sx-hint" style="padding:10px 12px;border-radius:12px;background:#EAF2FF;color:#0B1E3D">Le haut de la page (photos, prix, packs, commande) reste toujours en premier. Les sections « remplies dans la page de vente » n’apparaissent que sur les produits où elles sont écrites.</p>
            @elseif($theme !== $published)
            <p class="sx-hint" style="padding:10px 12px;border-radius:12px;background:#FEF3E0;color:#92400E">Vous modifiez le thème {{ $models[$theme][0] }}, qui n’est pas en ligne. Vos clients verront ces sections quand vous choisirez ce thème dans <a href="{{ route('boutique.storefront.edit') }}" style="color:inherit">Ma vitrine</a>.</p>
            @endif
            @unless($isProduct)
            <div class="sx-ai" id="sxAi">
                <div class="sx-ai-hd"><b><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/></svg> Shopio IA</b>@unless($ai['pro'])<a href="{{ route('boutique.subscription.upgrade') }}">Plan Pro</a>@endunless</div>
                <p class="sx-hint">Réécrit les textes de vos sections : plus clairs, plus vendeurs, toujours vrais. Vous relisez avant de publier.</p>
                <input class="sx-input" id="sxAiHint" maxlength="300" placeholder="Ton souhaité (facultatif) : chic, chaleureux, jeune…" aria-label="Ton souhaité pour Shopio IA" @unless($ai['ready']) disabled @endunless>
                <button type="button" class="sx-btn sx-btn-sm sx-btn-ai" id="sxAiAll" @unless($ai['ready']) disabled @endunless>✦ Améliorer toute la page</button>
                <p class="sx-msg" id="sxAiMsg" aria-live="polite">@if(!$ai['pro'])Réservé au plan Pro.@elseif(!$ai['ready'])Shopio IA n’est pas configuré sur ce serveur.@endif</p>
            </div>
            @endunless
            <ol class="sx-list" id="sxList" aria-label="Sections, dans l’ordre de la page"></ol>
            <div class="sx-add" id="sxAdd">
                <b style="font-size:14px">Ajouter une section</b>
                <input class="sx-input" type="search" id="sxSearch" placeholder="Rechercher une section…" aria-label="Rechercher une section">
                <div class="sx-tabs" role="group" aria-label="Catégories de sections" id="sxTabs"></div>
                <div class="sx-add-grid" id="sxAddGrid"></div>
            </div>
        </div>

        <section class="sx-preview" aria-label="Aperçu">
            <div class="sx-preview-bar">
                <div class="sx-seg" role="group" aria-label="Appareil"><button type="button" data-dev="phone" aria-pressed="true">Mobile</button><button type="button" data-dev="desk" aria-pressed="false">Ordinateur</button></div>
                @if($isProduct && count($previewProducts))
                <label style="display:flex;align-items:center;gap:6px;flex:1;min-width:0;justify-content:flex-end"><span class="sx-hint" style="white-space:nowrap">Produit</span><select class="sx-select" id="sxProduct" style="min-height:36px;max-width:260px;padding:6px 10px">@foreach($previewProducts as $i => $pp)<option value="{{ $i }}">{{ $pp['name'] }}</option>@endforeach</select></label>
                @endif
                <button type="button" class="sx-btn sx-btn-sm" id="sxReload">Recharger l’aperçu</button>
            </div>
            <div class="sx-stage" id="sxStage">
                <div class="sx-frame is-phone is-loading" id="sxFrame"><iframe id="sxIframe" title="Aperçu de la page"></iframe><div class="sx-loading">Chargement…</div></div>
            </div>
        </section>
    </div>

    <div class="sx-bar"><div class="sx-bar-in">
        <p id="sxState" aria-live="polite">Tout est enregistré</p>
        <button type="button" class="sx-btn sx-btn-main" id="sxSave">Enregistrer et publier</button>
    </div></div>
    <input type="file" id="sxFile" accept="image/jpeg,image/png,image/webp" class="sx-sr" tabindex="-1">
</div>
@endsection

@push('scripts')
@include('vendeur.shop._sections-script')
@endpush
