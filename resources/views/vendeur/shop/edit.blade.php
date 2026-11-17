{{--
    resources/views/vendeur/shop/edit.blade.php — « Ma boutique » (réglages)
    Route : PUT /shop/{shop} → ShopController@update → name('shop.update')
    Variables : $shop → App\Models\Shop
    Une seule page : 4 parties l'une sous l'autre, un petit menu pour y aller, une barre « Enregistrer » toujours visible.
    Blanc et gris, une seule couleur d'accent (le bleu Shopio). ?etape=N mène à la partie N.
--}}

@extends('layouts.app')

@section('title', 'Ma boutique · ' . $shop->name)

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { margin: 0; min-height: 100vh; background: var(--bg); color: var(--text); font-family: var(--font); -webkit-font-smoothing: antialiased; }
.se-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

/* ── Barre du haut ── */
.se-top { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 14px 28px !important; }
.se-top .tb-info { flex: 1 1 240px; min-width: 0; }
.se-title { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
.se-sub { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 2px; font-size: 13px; color: var(--muted); }
.se-status { display: inline-flex; align-items: center; height: 22px; padding: 0 9px; border-radius: 999px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); font-size: 12px; font-weight: 700; color: var(--text-2); }
.se-top-actions { display: flex; gap: 8px; flex-wrap: wrap; }

/* ── Boutons : un seul bouton bleu sur la page (Enregistrer) ── */
.se-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 999px; border: 0; font: 700 14px/1 var(--font); text-decoration: none; cursor: pointer; white-space: nowrap; transition: background-color 160ms, box-shadow 160ms, color 160ms; }
.se-btn:disabled { opacity: .55; cursor: default; }
.se-btn-sm { min-height: 40px; padding: 0 14px; font-size: 13px; }
.se-btn-blue { background: #1668D8; color: #fff; }
.se-btn-blue:hover:not(:disabled) { background: #1260C9; color: #fff; }
.se-btn-line { background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); }
.se-btn-line:hover:not(:disabled) { box-shadow: inset 0 0 0 1px #1F7CF6; color: #1668D8; }
.se-btn:focus-visible, .type-pill:focus-visible, .se-nav a:focus-visible, .se-swatch input:focus-visible + .se-swatch-dot { outline: 3px solid #1F7CF6; outline-offset: 2px; }

/* ── Messages ── */
.se-flash { border-radius: 14px; padding: 12px 16px; margin-bottom: 16px; font-size: 14px; box-shadow: inset 0 0 0 1px var(--border); background: var(--surface); color: var(--text); }
.se-flash ul { margin: 6px 0 0; padding-left: 18px; }
.se-flash-ok { color: var(--sv-ok, #15803D); font-weight: 700; }
.se-flash-err { color: var(--sv-err, #B91C1C); box-shadow: inset 0 0 0 1px currentColor; }

/* ── Mise en page : menu à gauche, parties à droite ── */
.se-content { padding: 24px 28px 0; }
.se-layout { display: grid; grid-template-columns: 220px minmax(0, 760px); gap: 28px; align-items: start; }
.se-aside { position: sticky; top: 16px; display: flex; flex-direction: column; gap: 14px; }
.se-ident { display: flex; align-items: center; gap: 10px; padding: 12px; border-radius: 16px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); min-width: 0; }
.se-avatar { position: relative; width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0; overflow: hidden; }
.se-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.se-avatar-ini { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; }
.se-ident-name { font-weight: 800; font-size: 14px; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.se-ident-meta { font-size: 12px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.se-nav { display: flex; flex-direction: column; gap: 2px; }
.se-nav a { display: flex; align-items: center; gap: 10px; min-height: 42px; padding: 0 12px; border-radius: 12px; color: var(--text-2); font-size: 14px; font-weight: 600; text-decoration: none; transition: background-color 160ms, color 160ms; }
.se-nav a:hover { background: var(--surface); color: var(--text); }
.se-nav a.is-active { background: var(--surface); color: #1668D8; box-shadow: inset 3px 0 0 #1F7CF6; }
.se-nav-n { width: 22px; height: 22px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: var(--muted); box-shadow: inset 0 0 0 1px var(--border); flex-shrink: 0; }
.se-nav a.is-active .se-nav-n { color: #1668D8; box-shadow: inset 0 0 0 1px #1F7CF6; }

/* ── Parties ── */
.se-main { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.se-card { background: var(--surface); border-radius: 18px; box-shadow: inset 0 0 0 1px var(--border); }
.se-sec { padding: 22px 24px 8px; scroll-margin-top: 16px; }
.se-sec-hd { margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
.se-sec-title { margin: 0; font-size: 18px; font-weight: 800; letter-spacing: -.01em; color: var(--text); outline: none; }
.se-sec-desc { margin: 4px 0 0; font-size: 13.5px; color: var(--muted); }
.se-sub-title { margin: 8px 0 12px; font-size: 15px; font-weight: 800; color: var(--text); }

/* Notifications push */
.se-notif { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; padding: 14px 18px; }
.se-notif-ico { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: var(--bg); color: var(--text-2); }
.se-notif strong { color: var(--text); font-size: 14px; }
.se-notif-sub { font-size: 13px; color: var(--muted); margin-top: 2px; }

/* ── Champs ── */
.field-group { margin-bottom: 18px; }
.field-row { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 14px; }
.field-label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 700; color: var(--text); }
.field-label span { color: var(--sv-err, #B91C1C); }
.field-label .se-opt { color: var(--muted); font-weight: 500; }
.field-input { width: 100%; min-height: 46px; padding: 0 14px; border: 1px solid var(--border); border-radius: 12px; background: var(--surface); color: var(--text); font: 500 15px var(--font); outline: 0; transition: border-color 160ms, box-shadow 160ms; }
textarea.field-input { min-height: 104px; padding: 12px 14px; resize: vertical; line-height: 1.5; }
select.field-input { appearance: auto; }
.field-input.error { border-color: var(--sv-err, #B91C1C); }
.field-hint { font-size: 13px; color: var(--muted); margin-top: 6px; line-height: 1.45; }
.field-error { font-size: 13px; font-weight: 600; color: var(--sv-err, #B91C1C); margin-top: 6px; }
.se-fieldset { border: 0; padding: 0; margin: 0 0 18px; min-width: 0; }

/* Type de boutique */
.type-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 2px; }
.type-pill { display: inline-flex; align-items: center; gap: 6px; min-height: 38px; padding: 0 14px; border-radius: 999px; border: 0; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); color: var(--text-2); font: 600 13px var(--font); cursor: pointer; transition: box-shadow 160ms, color 160ms, background-color 160ms; }
.type-pill:hover { box-shadow: inset 0 0 0 1px var(--muted); color: var(--text); }
.type-pill.selected { background: var(--sv-b50, #EAF2FF); color: #1668D8; box-shadow: inset 0 0 0 1.5px #1F7CF6; }
.type-pill-add { color: var(--text-2); box-shadow: inset 0 0 0 1px var(--border); border: 1px dashed var(--border); }
.type-custom-wrap { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
.type-custom-wrap .field-input { flex: 1 1 200px; }

/* Couleur d'accent */
.se-swatches { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 2px; }
.se-swatch { position: relative; cursor: pointer; }
.se-swatch input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.se-swatch-dot { display: block; width: 36px; height: 36px; border-radius: 999px; background: var(--sw); box-shadow: inset 0 0 0 1px rgba(0,0,0,.08); }
.se-swatch input:checked + .se-swatch-dot { outline: 2px solid var(--text); outline-offset: 3px; }

/* Devise */
.currency-wrap { position: relative; }
.currency-search-box { cursor: pointer; }
.currency-dropdown { display: none; position: absolute; left: 0; right: 0; top: calc(100% + 6px); z-index: 20; background: var(--surface); border-radius: 14px; box-shadow: 0 0 0 1px var(--border), 0 12px 28px rgba(11,30,61,.12); padding: 8px; }
.currency-dropdown.open { display: block; }
.currency-search-input { width: 100%; min-height: 42px; border: 1px solid var(--border); border-radius: 10px; padding: 0 12px; background: var(--bg); color: var(--text); font: 500 15px var(--font); margin-bottom: 6px; outline: 0; }
.currency-list { max-height: 220px; overflow-y: auto; }
.currency-opt { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 10px; cursor: pointer; font-size: 14px; }
.currency-opt:hover, .currency-opt.selected { background: var(--bg); }
.currency-code { font-weight: 800; color: var(--text); }
.currency-name { flex: 1; min-width: 0; color: var(--text-2); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.currency-country { font-size: 12px; color: var(--muted); }
#currency_hidden { display: none; }

/* Liens vers la vitrine, image actuelle */
.se-asset { display: flex; align-items: center; flex-wrap: wrap; gap: 12px 14px; padding: 14px; border-radius: 14px; background: var(--bg); margin-bottom: 18px; }
.se-asset img { width: 56px; height: 56px; border-radius: 12px; object-fit: cover; flex-shrink: 0; }
.se-asset strong { color: var(--text); font-size: 14px; }
.se-asset-txt { flex: 1 1 200px; min-width: 0; }
.se-asset-btns { display: flex; gap: 8px; flex-wrap: wrap; }

/* Image */
.upload-zone { position: relative; border-radius: 14px; padding: 24px 18px; text-align: center; background: var(--bg); border: 1.5px dashed var(--border); cursor: pointer; transition: border-color 160ms; }
.upload-zone:hover, .upload-zone.dragover { border-color: #1F7CF6; }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.upload-txt { font-size: 15px; font-weight: 700; color: var(--text); }
.upload-hint { font-size: 13px; color: var(--muted); margin-top: 4px; }
.upload-preview { display: none; position: relative; width: max-content; max-width: 100%; margin: 0 auto; }
.upload-preview img { display: block; max-height: 180px; max-width: 100%; border-radius: 12px; }
.upload-remove { position: absolute; top: 6px; right: 6px; width: 34px; height: 34px; border-radius: 999px; border: 0; background: rgba(22,22,42,.7); color: #fff; font-size: 20px; cursor: pointer; z-index: 1; }

/* ── Barre « Enregistrer » toujours visible ── */
.se-save { position: sticky; bottom: 0; z-index: 25; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin: 4px 0 0; padding: 12px 0 calc(12px + env(safe-area-inset-bottom, 0px)); background: var(--bg); border-top: 1px solid var(--border); }
.se-save-txt { flex: 1 1 220px; min-width: 0; font-size: 13px; color: var(--muted); }
.se-save-txt.is-dirty { color: var(--text); font-weight: 700; }
.se-save .se-btn { min-width: 160px; }

/* Mode sombre */
[data-theme="dark"] .se-swatch input:checked + .se-swatch-dot { outline-color: #EAF0FA; }

/* ── Tablette et téléphone : le menu devient une barre de pastilles en haut ── */
@media (max-width: 1100px) {
    .se-layout { grid-template-columns: minmax(0, 1fr); gap: 14px; }
    .se-aside { top: 0; z-index: 26; margin: 0 -28px; padding: 10px 28px; background: var(--bg); border-bottom: 1px solid var(--border); }
    .se-ident { display: none; }
    .se-nav { flex-direction: row; gap: 6px; overflow-x: auto; scrollbar-width: none; }
    .se-nav::-webkit-scrollbar { display: none; }
    .se-nav a { flex-shrink: 0; min-height: 38px; border-radius: 999px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); font-size: 13px; }
    .se-nav a.is-active { box-shadow: inset 0 0 0 1.5px #1F7CF6; }
    .se-nav-n { display: none; }
    .se-sec { scroll-margin-top: 72px; }
}
@media (max-width: 640px) {
    .se-top { padding: 10px 16px !important; }
    .se-title { font-size: 19px; }
    .se-hide-m { display: none; }
    .se-content { padding: 14px 16px 0; }
    .se-aside { margin: 0 -16px; padding: 8px 16px; }
    .se-sec { padding: 18px 16px 4px; }
    .field-row { grid-template-columns: minmax(0, 1fr); gap: 0; }
    .se-save .se-btn { flex: 1 1 100%; }
    /* Anti-zoom iOS */
    .field-input, .currency-search-input { font-size: 16px; }
}
@media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto !important; } }
</style>
@endpush

@section('content')
@php
    $accentChoices = [
        '#1F7CF6' => ['Bleu Shopio', '#FFFFFF'],
        '#0B1E3D' => ['Bleu nuit', '#7DB4FF'],
        '#F59E0B' => ['Ambre', '#16162A'],
        '#15803D' => ['Vert', '#FFFFFF'],
    ];
    $currentAccent = strtoupper(old('accent_color', $shop->accent_color ?: '#1F7CF6'));
    // Couleur libre choisie dans l'éditeur de boutique : proposée telle quelle (sinon elle serait perdue à l'enregistrement)
    if (!isset($accentChoices[$currentAccent])) {
        $currentAccent = \App\Support\Storefront::isHex($currentAccent) ? $currentAccent : '#1F7CF6';
        if (!isset($accentChoices[$currentAccent])) $accentChoices[$currentAccent] = ['Votre couleur', \App\Support\Storefront::inkFor($currentAccent)];
    }
    $shopIni = collect(preg_split('/\s+/', trim($shop->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $parts = [
        1 => 'Informations générales',
        2 => 'Contact et paiement',
        3 => 'Apparence',
        4 => 'Image de la boutique',
    ];
    // Partie à ouvrir quand Laravel renvoie une erreur
    $errorPart = match (true) {
        $errors->hasAny(['name', 'email', 'type']) => 1,
        $errors->hasAny(['phone', 'address', 'country', 'currency', 'payout_wallet_type', 'payout_wallet_number']) => 2,
        $errors->hasAny(['description', 'accent_color', 'meta_pixel_id']) => 3,
        $errors->has('image') => 4,
        default => null,
    };
@endphp
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'settings'])

    <main class="main vs-main">
        {{-- ══ Barre du haut ══ --}}
        <div class="topbar se-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="tb-info">
                <h1 class="se-title">Ma boutique</h1>
                <div class="se-sub">
                    <span>Informations, contact et apparence</span>
                    <span class="se-status">{{ $shop->is_approved ? 'Vérifiée' : 'En validation' }}</span>
                </div>
            </div>
            <div class="se-top-actions">
                <a href="{{ \App\Support\Storefront::publicUrl($shop) }}" class="se-btn se-btn-line" target="_blank" rel="noopener">Voir ma boutique</a>
                <a href="{{ route('boutique.storefront.edit') }}" class="se-btn se-btn-line">Ma vitrine</a>
                <a href="{{ route('boutique.marketing', ['onglet' => 'partage']) }}" class="se-btn se-btn-line se-hide-m">Partage et qualité</a>
            </div>
        </div>

        <div class="se-content">
            {{-- Messages --}}
            @if($errors->any())
            <div class="se-flash se-flash-err" role="alert">
                <strong>Veuillez corriger les points suivants :</strong>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif
            @if(session('success'))<div class="se-flash se-flash-ok" role="status">{{ session('success') }}</div>@endif
            @if(session('info'))<div class="se-flash" role="status">{{ session('info') }}</div>@endif

            <div class="se-layout">
                {{-- ══ Menu : la boutique en bref + les 4 parties ══ --}}
                <aside class="se-aside" aria-label="Parties de la page">
                    <div class="se-ident">
                        <span class="se-avatar">
                            <img id="sidebarPreviewImg"
                                 src="{{ $shop->image ? (\App\Services\ImageOptimizer::url($shop->image, 'thumb') ?? asset('storage/' . $shop->image)) : '' }}"
                                 alt="" @unless($shop->image) style="display:none" @endunless>
                            <span class="se-avatar-ini" id="previewLogo"
                                  style="background:{{ $currentAccent }};color:{{ $accentChoices[$currentAccent][1] }}{{ $shop->image ? ';display:none' : '' }}">{{ $shopIni }}</span>
                        </span>
                        <div style="min-width:0">
                            <div class="se-ident-name" id="previewName">{{ $shop->name }}</div>
                            <div class="se-ident-meta"><span id="previewType">{{ $shop->type }}</span>@if($shop->type) · @endif<span id="previewCurrency">{{ $shop->currency ?? 'GNF' }}</span></div>
                        </div>
                    </div>
                    <nav class="se-nav" id="seNav">
                        @foreach($parts as $n => $label)
                        <a href="#section-{{ $n }}" data-part="{{ $n }}" class="{{ $n === 1 ? 'is-active' : '' }}"><span class="se-nav-n">{{ $n }}</span>{{ $label }}</a>
                        @endforeach
                    </nav>
                </aside>

                <div class="se-main">
                    {{-- Notifications push — activation manuelle si la bannière automatique n'est pas apparue --}}
                    <div id="notifSettingsCard" class="se-card se-notif">
                        <span class="se-notif-ico" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15zM10 20a2 2 0 0 0 4 0"/></svg>
                        </span>
                        <div style="flex:1 1 180px;min-width:0">
                            <strong>Notifications push</strong>
                            <div id="notifSettingsSub" class="se-notif-sub">Recevez vos commandes et messages en temps réel, même application fermée.</div>
                        </div>
                        <button type="button" id="notifSettingsBtn" class="se-btn se-btn-line se-btn-sm">Activer</button>
                    </div>
                    <script>
                    (function() {
                        var card = document.getElementById('notifSettingsCard');
                        var btn  = document.getElementById('notifSettingsBtn');
                        var sub  = document.getElementById('notifSettingsSub');
                        if (!card) return;
                        if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) { card.style.display = 'none'; return; }
                        function refresh() {
                            if (Notification.permission === 'granted' && localStorage.getItem('push_subscribed')) {
                                sub.textContent = 'Notifications activées sur cet appareil.';
                                btn.textContent = 'Activées';
                                btn.disabled = true;
                            } else if (Notification.permission === 'denied') {
                                sub.textContent = 'Bloquées dans les réglages de votre navigateur. Autorisez les notifications pour ce site pour les activer.';
                                btn.style.display = 'none';
                            }
                        }
                        btn.addEventListener('click', function() {
                            btn.disabled = true;
                            btn.textContent = 'Activation…';
                            Promise.resolve(window.enablePushNotifications ? window.enablePushNotifications() : null)
                                .finally(function() { btn.disabled = false; btn.textContent = 'Activer'; refresh(); });
                        });
                        refresh();
                    })();
                    </script>

                    {{-- Formulaire unique — route PUT --}}
                    <form action="{{ route('shop.update', $shop->id) }}" method="POST" enctype="multipart/form-data" id="shopForm" class="se-main">
                        @csrf
                        @method('PUT')

                        {{-- ══ 1. Informations générales ══ --}}
                        <section class="se-card se-sec" id="section-1" aria-labelledby="t-1">
                            <div class="se-sec-hd">
                                <h2 class="se-sec-title" id="t-1" tabindex="-1">Informations générales</h2>
                                <p class="se-sec-desc">Le nom et le type de votre boutique, tels que vos clients les voient.</p>
                            </div>

                            <div class="field-group">
                                <label class="field-label" for="name">Nom de la boutique <span>*</span></label>
                                <input type="text" name="name" id="name" class="field-input {{ $errors->has('name') ? 'error' : '' }}"
                                       value="{{ old('name', $shop->name) }}" placeholder="Ex. Boutique Aminata" required maxlength="255">
                                @error('name')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="field-group">
                                <span class="field-label" id="typeLbl">Type de boutique</span>
                                <input type="hidden" name="type" id="type" value="{{ old('type', $shop->type) }}">
                                <div class="type-pills" id="typePills" role="group" aria-labelledby="typeLbl">
                                    @foreach(['Alimentation','Restaurant','Pharmacie','Vêtements','Électronique','Beauté & Cosmétiques','Meubles & Décoration','Sport & Loisirs','Librairie','Boulangerie','Épicerie','Bijouterie','Auto & Moto','Informatique','Téléphonie','Jouets','Fleurs','Autre'] as $t)
                                    <button type="button" class="type-pill" data-val="{{ $t }}" onclick="selectType(@js($t))">{{ $t }}</button>
                                    @endforeach
                                    <button type="button" class="type-pill type-pill-add" onclick="showCustomType()">+ Autre catégorie</button>
                                </div>
                                <div class="type-custom-wrap" id="typeCustomWrap" style="display:none">
                                    <label for="typeCustomInput" class="se-sr">Nom de la catégorie</label>
                                    <input type="text" id="typeCustomInput" class="field-input" placeholder="Ex. Quincaillerie" maxlength="60">
                                    <button type="button" class="se-btn se-btn-line se-btn-sm" onclick="confirmCustomType()">Valider</button>
                                    <button type="button" class="se-btn se-btn-line se-btn-sm" onclick="hideCustomType()">Annuler</button>
                                </div>
                                @error('type')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="field-group">
                                <label class="field-label" for="email">E-mail de la boutique <span>*</span></label>
                                <input type="email" name="email" id="email" class="field-input {{ $errors->has('email') ? 'error' : '' }}"
                                       value="{{ old('email', $shop->email) }}" placeholder="boutique@email.com" required>
                                @error('email')<div class="field-error">{{ $message }}</div>@enderror
                            </div>
                        </section>

                        {{-- ══ 2. Contact et paiement ══ --}}
                        <section class="se-card se-sec" id="section-2" aria-labelledby="t-2">
                            <div class="se-sec-hd">
                                <h2 class="se-sec-title" id="t-2" tabindex="-1">Contact et paiement</h2>
                                <p class="se-sec-desc">Où vous trouver, et sur quel numéro recevoir l'argent des ventes payées en ligne.</p>
                            </div>

                            <div class="field-group">
                                <label class="field-label" for="phone">Téléphone de la boutique</label>
                                <input type="tel" name="phone" id="phone" class="field-input {{ $errors->has('phone') ? 'error' : '' }}"
                                       value="{{ old('phone', $shop->phone) }}" placeholder="+224 6XX XX XX XX" maxlength="20" inputmode="tel">
                                @error('phone')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="field-group">
                                <label class="field-label" for="address">Adresse et point de repère</label>
                                <input type="text" name="address" id="address" class="field-input {{ $errors->has('address') ? 'error' : '' }}"
                                       value="{{ old('address', $shop->address) }}" placeholder="Ex. Kaloum, en face de la Banque centrale" maxlength="255">
                                @error('address')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            @php
                            $shopCountries = [
                                'Afrique de l\'Ouest' => ['BJ'=>['🇧🇯','Bénin'],'BF'=>['🇧🇫','Burkina Faso'],'CV'=>['🇨🇻','Cap-Vert'],'CI'=>['🇨🇮',"Côte d'Ivoire"],'GM'=>['🇬🇲','Gambie'],'GH'=>['🇬🇭','Ghana'],'GN'=>['🇬🇳','Guinée'],'GW'=>['🇬🇼','Guinée-Bissau'],'LR'=>['🇱🇷','Libéria'],'ML'=>['🇲🇱','Mali'],'MR'=>['🇲🇷','Mauritanie'],'NE'=>['🇳🇪','Niger'],'NG'=>['🇳🇬','Nigéria'],'SN'=>['🇸🇳','Sénégal'],'SL'=>['🇸🇱','Sierra Leone'],'TG'=>['🇹🇬','Togo']],
                                'Afrique centrale' => ['CM'=>['🇨🇲','Cameroun'],'CF'=>['🇨🇫','Centrafrique'],'TD'=>['🇹🇩','Tchad'],'CG'=>['🇨🇬','Congo'],'CD'=>['🇨🇩','RD Congo'],'GA'=>['🇬🇦','Gabon'],'GQ'=>['🇬🇶','Guinée équatoriale'],'RW'=>['🇷🇼','Rwanda'],'BI'=>['🇧🇮','Burundi']],
                                'Afrique de l\'Est' => ['DJ'=>['🇩🇯','Djibouti'],'ET'=>['🇪🇹','Éthiopie'],'KE'=>['🇰🇪','Kenya'],'MG'=>['🇲🇬','Madagascar'],'MU'=>['🇲🇺','Maurice'],'MZ'=>['🇲🇿','Mozambique'],'SO'=>['🇸🇴','Somalie'],'SD'=>['🇸🇩','Soudan'],'TZ'=>['🇹🇿','Tanzanie'],'UG'=>['🇺🇬','Ouganda'],'ZM'=>['🇿🇲','Zambie'],'ZW'=>['🇿🇼','Zimbabwe']],
                                'Afrique du Nord' => ['DZ'=>['🇩🇿','Algérie'],'EG'=>['🇪🇬','Égypte'],'LY'=>['🇱🇾','Libye'],'MA'=>['🇲🇦','Maroc'],'TN'=>['🇹🇳','Tunisie']],
                                'Afrique australe' => ['AO'=>['🇦🇴','Angola'],'BW'=>['🇧🇼','Botswana'],'NA'=>['🇳🇦','Namibie'],'ZA'=>['🇿🇦','Afrique du Sud'],'LS'=>['🇱🇸','Lesotho']],
                                'Europe' => ['DE'=>['🇩🇪','Allemagne'],'AT'=>['🇦🇹','Autriche'],'BE'=>['🇧🇪','Belgique'],'DK'=>['🇩🇰','Danemark'],'ES'=>['🇪🇸','Espagne'],'FI'=>['🇫🇮','Finlande'],'FR'=>['🇫🇷','France'],'GR'=>['🇬🇷','Grèce'],'IE'=>['🇮🇪','Irlande'],'IT'=>['🇮🇹','Italie'],'LU'=>['🇱🇺','Luxembourg'],'NO'=>['🇳🇴','Norvège'],'NL'=>['🇳🇱','Pays-Bas'],'PL'=>['🇵🇱','Pologne'],'PT'=>['🇵🇹','Portugal'],'GB'=>['🇬🇧','Royaume-Uni'],'RU'=>['🇷🇺','Russie'],'SE'=>['🇸🇪','Suède'],'CH'=>['🇨🇭','Suisse'],'UA'=>['🇺🇦','Ukraine']],
                                'Amériques' => ['AR'=>['🇦🇷','Argentine'],'BR'=>['🇧🇷','Brésil'],'CA'=>['🇨🇦','Canada'],'CL'=>['🇨🇱','Chili'],'CO'=>['🇨🇴','Colombie'],'US'=>['🇺🇸','États-Unis'],'MX'=>['🇲🇽','Mexique'],'PE'=>['🇵🇪','Pérou'],'VE'=>['🇻🇪','Venezuela']],
                                'Asie et Moyen-Orient' => ['SA'=>['🇸🇦','Arabie saoudite'],'AE'=>['🇦🇪','Émirats arabes unis'],'CN'=>['🇨🇳','Chine'],'IN'=>['🇮🇳','Inde'],'ID'=>['🇮🇩','Indonésie'],'JP'=>['🇯🇵','Japon'],'KW'=>['🇰🇼','Koweït'],'LB'=>['🇱🇧','Liban'],'MY'=>['🇲🇾','Malaisie'],'PK'=>['🇵🇰','Pakistan'],'QA'=>['🇶🇦','Qatar'],'SG'=>['🇸🇬','Singapour'],'KR'=>['🇰🇷','Corée du Sud'],'TH'=>['🇹🇭','Thaïlande'],'TR'=>['🇹🇷','Turquie'],'VN'=>['🇻🇳','Viêt Nam']],
                                'Océanie' => ['AU'=>['🇦🇺','Australie'],'NZ'=>['🇳🇿','Nouvelle-Zélande']],
                            ];
                            $currentCountry = old('country', $shop->country ?? '');
                            @endphp
                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label" for="country">Pays de la boutique</label>
                                    <select name="country" id="country" class="field-input">
                                        <option value="">Sélectionner un pays…</option>
                                        @foreach($shopCountries as $region => $pays)
                                            <optgroup label="{{ $region }}">
                                                @foreach($pays as $code => [$flag, $cname])
                                                <option value="{{ $code }}" {{ $currentCountry === $code ? 'selected' : '' }}>{{ $flag }} {{ $cname }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <div class="field-hint">Les clients de ce pays voient votre boutique dans leur espace.</div>
                                    @error('country')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="field-group">
                                    <label class="field-label" for="currencyDisplay">Devise</label>
                                    <div class="currency-wrap" id="currencyWrap">
                                        <input type="text" class="currency-search-box field-input" id="currencyDisplay" placeholder="Sélectionner une devise…" readonly>
                                        <input type="hidden" name="currency" id="currency_hidden" value="{{ old('currency', $shop->currency ?? 'GNF') }}">
                                        <div class="currency-dropdown" id="currencyDropdown">
                                            <label for="currencySearch" class="se-sr">Rechercher une devise</label>
                                            <input type="text" class="currency-search-input" id="currencySearch" placeholder="Rechercher une devise ou un pays…" autocomplete="off">
                                            <div class="currency-list" id="currencyList"></div>
                                        </div>
                                    </div>
                                    <div class="field-hint">Quand un pays est choisi, c'est la devise de ce pays qui est enregistrée.</div>
                                    @error('currency')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <h3 class="se-sub-title">Paiements en ligne</h3>
                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label" for="payout_wallet_type">Mobile Money</label>
                                    <select name="payout_wallet_type" id="payout_wallet_type" class="field-input">
                                        <option value="">Non renseigné</option>
                                        @foreach(['orange_money' => 'Orange Money', 'mtn_momo' => 'MTN MoMo', 'paycard' => 'PayCard', 'kulu' => 'Kulu', 'soutra_money' => 'Soutra Money', 'akiba' => 'Akiba'] as $wv => $wl)
                                        <option value="{{ $wv }}" {{ old('payout_wallet_type', $shop->payout_wallet_type ?? '') === $wv ? 'selected' : '' }}>{{ $wl }}</option>
                                        @endforeach
                                    </select>
                                    @error('payout_wallet_type')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="field-group">
                                    <label class="field-label" for="payout_wallet_number">Numéro Mobile Money</label>
                                    <input type="text" name="payout_wallet_number" id="payout_wallet_number" class="field-input" inputmode="tel"
                                           placeholder="Ex. 622 00 00 00" value="{{ old('payout_wallet_number', $shop->payout_wallet_number ?? '') }}">
                                    @error('payout_wallet_number')<div class="field-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="field-hint" style="margin:-8px 0 18px">C'est sur ce numéro que Shopio vous verse l'argent des ventes payées en ligne.</div>
                        </section>

                        {{-- ══ 3. Apparence ══ --}}
                        <section class="se-card se-sec" id="section-3" aria-labelledby="t-3">
                            <div class="se-sec-hd">
                                <h2 class="se-sec-title" id="t-3" tabindex="-1">Apparence</h2>
                                <p class="se-sec-desc">La couleur et la description de votre boutique.</p>
                            </div>

                            <fieldset class="se-fieldset">
                                <legend class="field-label">Couleur d'accent</legend>
                                <div class="se-swatches">
                                    @foreach($accentChoices as $hex => [$aLabel, $aInk])
                                    <label class="se-swatch" style="--sw:{{ $hex }}" title="{{ $aLabel }}">
                                        <input type="radio" name="accent_color" value="{{ $hex }}" data-ink="{{ $aInk }}" {{ $currentAccent === $hex ? 'checked' : '' }}>
                                        <span class="se-swatch-dot" aria-hidden="true"></span>
                                        <span class="se-sr">{{ $aLabel }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <div class="field-hint">Utilisée pour le liseré du bandeau de votre boutique, visible par vos clients.</div>
                                @error('accent_color')<div class="field-error">{{ $message }}</div>@enderror
                            </fieldset>

                            <div class="field-group">
                                <label class="field-label" for="description">Description courte</label>
                                <textarea name="description" id="description" class="field-input {{ $errors->has('description') ? 'error' : '' }}" rows="4" maxlength="500"
                                          placeholder="Ex. Montres et parfums authentiques, livrés partout à Conakry.">{{ old('description', $shop->description) }}</textarea>
                                <div class="field-hint">500 caractères maximum. Dites ce que vous vendez et où vous livrez.</div>
                                @error('description')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            {{-- Pixel Meta (Facebook) : mesure des pubs du vendeur sur ses propres pages --}}
                            <div class="field-group">
                                <label class="field-label" for="meta_pixel_id">Pixel Meta (Facebook) <span class="se-opt">· facultatif</span></label>
                                <input type="text" name="meta_pixel_id" id="meta_pixel_id" class="field-input {{ $errors->has('meta_pixel_id') ? 'error' : '' }}"
                                       value="{{ old('meta_pixel_id', $shop->meta_pixel_id) }}" inputmode="numeric" autocomplete="off" maxlength="30"
                                       placeholder="Ex. 123456789012345" aria-describedby="metaPixelHint">
                                <div class="field-hint" id="metaPixelHint">
                                    L'identifiant à 15 ou 16 chiffres de votre pixel (Gestionnaire d'événements Meta → Sources de données).
                                    Il mesure les visites de votre vitrine, les produits vus, les ajouts au panier et les commandes,
                                    uniquement sur les pages de votre boutique. Laissez vide pour le désactiver.
                                </div>
                                @error('meta_pixel_id')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            {{-- Thème de la page publique (Express ou Signature), couverture et logo --}}
                            <div class="se-asset">
                                <div class="se-asset-txt">
                                    <strong>Vitrine de la boutique</strong>
                                    <div class="field-hint" style="margin:2px 0 0">Thème, logo, couverture et sections se modifient directement sur votre vitrine. Enregistrez d'abord cette page si vous l'avez modifiée.</div>
                                </div>
                                <div class="se-asset-btns">
                                    <a href="{{ route('boutique.storefront.live', ['ouvrir' => 'entete']) }}" class="se-btn se-btn-line se-btn-sm">Changer le logo et la couverture</a>
                                    <a href="{{ route('boutique.storefront.edit') }}" class="se-btn se-btn-line se-btn-sm">Choisir le thème</a>
                                </div>
                            </div>
                        </section>

                        {{-- ══ 4. Image de la boutique ══ --}}
                        <section class="se-card se-sec" id="section-4" aria-labelledby="t-4">
                            <div class="se-sec-hd">
                                <h2 class="se-sec-title" id="t-4" tabindex="-1">Image de la boutique</h2>
                                <p class="se-sec-desc">Votre logo ou une photo carrée, affichée sur votre fiche boutique.</p>
                            </div>

                            @if($shop->image)
                            <div class="se-asset">
                                <img src="{{ \App\Services\ImageOptimizer::url($shop->image, 'thumb') ?? asset('storage/' . $shop->image) }}" alt="Image actuelle de {{ $shop->name }}" width="56" height="56">
                                <div class="se-asset-txt"><strong>Image actuelle</strong><div class="field-hint" style="margin:0">Choisissez une nouvelle image ci-dessous pour la remplacer.</div></div>
                            </div>
                            @endif

                            <div class="field-group">
                                <span class="field-label">{{ $shop->image ? 'Nouvelle image (facultatif)' : 'Logo ou image de la boutique' }}</span>
                                <div class="upload-zone" id="uploadZone">
                                    <input type="file" name="image" id="imageInput" accept="image/*" aria-label="Choisir une image">
                                    <div id="uploadPlaceholder">
                                        <div class="upload-txt">Cliquez ou glissez votre image ici</div>
                                        <div class="upload-hint">JPG, PNG ou WEBP · 2 Mo maximum · carré de 512 px conseillé</div>
                                    </div>
                                    <div class="upload-preview" id="uploadPreview">
                                        <img id="previewImg" src="" alt="Aperçu de la nouvelle image">
                                        <button type="button" class="upload-remove" id="removeImg" aria-label="Retirer l'image">&times;</button>
                                    </div>
                                </div>
                                @error('image')<div class="field-error">{{ $message }}</div>@enderror
                            </div>
                        </section>

                        {{-- ══ Barre « Enregistrer », toujours visible en bas ══ --}}
                        <div class="se-save">
                            <div class="se-save-txt" id="saveState" aria-live="polite">Vos clients voient les changements dès l'enregistrement.</div>
                            <button type="submit" class="se-btn se-btn-blue" id="submitBtn">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
/* ════════════════════════════════════════════════════════════════
   DEVISES — identiques à la création de boutique
════════════════════════════════════════════════════════════════ */
const CURRENCIES = [
    { code: 'GNF', name: 'Franc guinéen',        flag: '🇬🇳', country: 'Guinée' },
    { code: 'XOF', name: 'Franc CFA (UEMOA)',     flag: '🌍', country: 'Afrique Ouest' },
    { code: 'XAF', name: 'Franc CFA (CEMAC)',     flag: '🌍', country: 'Afrique Centrale' },
    { code: 'MAD', name: 'Dirham marocain',        flag: '🇲🇦', country: 'Maroc' },
    { code: 'DZD', name: 'Dinar algérien',         flag: '🇩🇿', country: 'Algérie' },
    { code: 'TND', name: 'Dinar tunisien',         flag: '🇹🇳', country: 'Tunisie' },
    { code: 'EGP', name: 'Livre égyptienne',       flag: '🇪🇬', country: 'Égypte' },
    { code: 'NGN', name: 'Naira nigérian',         flag: '🇳🇬', country: 'Nigéria' },
    { code: 'GHS', name: 'Cedi ghanéen',           flag: '🇬🇭', country: 'Ghana' },
    { code: 'KES', name: 'Shilling kenyan',        flag: '🇰🇪', country: 'Kenya' },
    { code: 'ETB', name: 'Birr éthiopien',         flag: '🇪🇹', country: 'Éthiopie' },
    { code: 'TZS', name: 'Shilling tanzanien',     flag: '🇹🇿', country: 'Tanzanie' },
    { code: 'ZAR', name: 'Rand sud-africain',      flag: '🇿🇦', country: 'Afrique du Sud' },
    { code: 'USD', name: 'Dollar américain',       flag: '🇺🇸', country: 'États-Unis' },
    { code: 'EUR', name: 'Euro',                   flag: '🇪🇺', country: 'Zone Euro' },
    { code: 'GBP', name: 'Livre sterling',         flag: '🇬🇧', country: 'Royaume-Uni' },
    { code: 'CHF', name: 'Franc suisse',           flag: '🇨🇭', country: 'Suisse' },
    { code: 'CAD', name: 'Dollar canadien',        flag: '🇨🇦', country: 'Canada' },
    { code: 'AUD', name: 'Dollar australien',      flag: '🇦🇺', country: 'Australie' },
    { code: 'JPY', name: 'Yen japonais',           flag: '🇯🇵', country: 'Japon' },
    { code: 'CNY', name: 'Yuan chinois',           flag: '🇨🇳', country: 'Chine' },
    { code: 'INR', name: 'Roupie indienne',        flag: '🇮🇳', country: 'Inde' },
    { code: 'BRL', name: 'Real brésilien',         flag: '🇧🇷', country: 'Brésil' },
    { code: 'MXN', name: 'Peso mexicain',          flag: '🇲🇽', country: 'Mexique' },
    { code: 'RUB', name: 'Rouble russe',           flag: '🇷🇺', country: 'Russie' },
    { code: 'TRY', name: 'Livre turque',           flag: '🇹🇷', country: 'Turquie' },
    { code: 'SAR', name: 'Riyal saoudien',         flag: '🇸🇦', country: 'Arabie Saoudite' },
    { code: 'AED', name: 'Dirham émirati',         flag: '🇦🇪', country: 'Émirats Arabes' },
    { code: 'QAR', name: 'Riyal qatari',           flag: '🇶🇦', country: 'Qatar' },
    { code: 'KWD', name: 'Dinar koweïtien',        flag: '🇰🇼', country: 'Koweït' },
    { code: 'SGD', name: 'Dollar singapourien',    flag: '🇸🇬', country: 'Singapour' },
    { code: 'HKD', name: 'Dollar hongkongais',     flag: '🇭🇰', country: 'Hong Kong' },
    { code: 'KRW', name: 'Won sud-coréen',         flag: '🇰🇷', country: 'Corée du Sud' },
    { code: 'IDR', name: 'Roupiah indonésienne',   flag: '🇮🇩', country: 'Indonésie' },
    { code: 'MYR', name: 'Ringgit malaisien',      flag: '🇲🇾', country: 'Malaisie' },
    { code: 'THB', name: 'Baht thaïlandais',       flag: '🇹🇭', country: 'Thaïlande' },
    { code: 'PKR', name: 'Roupie pakistanaise',    flag: '🇵🇰', country: 'Pakistan' },
    { code: 'NZD', name: 'Dollar néo-zélandais',   flag: '🇳🇿', country: 'Nouvelle-Zélande' },
    { code: 'CLP', name: 'Peso chilien',           flag: '🇨🇱', country: 'Chili' },
    { code: 'COP', name: 'Peso colombien',         flag: '🇨🇴', country: 'Colombie' },
    { code: 'ARS', name: 'Peso argentin',          flag: '🇦🇷', country: 'Argentine' },
    { code: 'DKK', name: 'Couronne danoise',       flag: '🇩🇰', country: 'Danemark' },
    { code: 'SEK', name: 'Couronne suédoise',      flag: '🇸🇪', country: 'Suède' },
    { code: 'NOK', name: 'Couronne norvégienne',   flag: '🇳🇴', country: 'Norvège' },
    { code: 'PLN', name: 'Zloty polonais',         flag: '🇵🇱', country: 'Pologne' },
];
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ════════════════════════════════════════════════════════════════
   MENU DES PARTIES — aller à une partie, et suivre celle qu'on lit
════════════════════════════════════════════════════════════════ */
const navLinks = Array.from(document.querySelectorAll('#seNav a'));
function markPart(n) {
    lastPart = n;
    navLinks.forEach(a => {
        const on = parseInt(a.dataset.part, 10) === n;
        a.classList.toggle('is-active', on);
        if (on) { a.setAttribute('aria-current', 'true'); a.scrollIntoView({ block: 'nearest', inline: 'nearest' }); }
        else a.removeAttribute('aria-current');
    });
}
function goToPart(n, smooth = true) {
    const sec = document.getElementById('section-' + n);
    if (!sec) return;
    sec.scrollIntoView({ behavior: smooth && !reduceMotion ? 'smooth' : 'auto', block: 'start' });
    sec.querySelector('.se-sec-title')?.focus({ preventScroll: true });
    markPart(n);
}
navLinks.forEach(a => a.addEventListener('click', e => {
    e.preventDefault();
    goToPart(parseInt(a.dataset.part, 10));
    history.replaceState(null, '', a.getAttribute('href'));
}));
/* Partie lue = la dernière dont le haut a passé le tiers de l'écran ; tout en bas de la page = la dernière partie */
let lastPart = 1, ticking = false;
function trackPart() {
    ticking = false;
    const root = document.scrollingElement || document.documentElement;
    let n = 1;
    [1, 2, 3, 4].forEach(i => { if (document.getElementById('section-' + i).getBoundingClientRect().top <= window.innerHeight / 3) n = i; });
    if (window.innerHeight + root.scrollTop >= root.scrollHeight - 4) n = 4;
    if (n !== lastPart) { lastPart = n; markPart(n); }
}
window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(trackPart); } }, { passive: true });

/* ════════════════════════════════════════════════════════════════
   COULEUR D'ACCENT — pastille de la boutique dans le menu
════════════════════════════════════════════════════════════════ */
document.querySelectorAll('input[name="accent_color"]').forEach(r => r.addEventListener('change', () => {
    const logo = document.getElementById('previewLogo');
    logo.style.background = r.value;
    logo.style.color = r.dataset.ink;
}));

/* ════════════════════════════════════════════════════════════════
   DEVISE — liste avec recherche, devise actuelle pré-sélectionnée
════════════════════════════════════════════════════════════════ */
function renderCurrencies(filter = '') {
    const list = document.getElementById('currencyList');
    const q    = filter.toLowerCase();
    const current = document.getElementById('currency_hidden').value;
    const filtered = CURRENCIES.filter(c =>
        c.code.toLowerCase().includes(q) ||
        c.name.toLowerCase().includes(q) ||
        c.country.toLowerCase().includes(q)
    );

    list.innerHTML = filtered.map(c => `
        <div class="currency-opt ${c.code === current ? 'selected' : ''}"
             data-code="${c.code}" data-name="${c.name}" data-flag="${c.flag}">
            <span class="currency-flag">${c.flag}</span>
            <span class="currency-code">${c.code}</span>
            <span class="currency-name">${c.name}</span>
            <span class="currency-country">${c.country}</span>
        </div>
    `).join('');

    list.querySelectorAll('.currency-opt').forEach(opt => {
        opt.addEventListener('click', () => {
            selectCurrency(opt.dataset.code, opt.dataset.name, opt.dataset.flag);
            markDirty();
        });
    });
}

function selectCurrency(code, name, flag) {
    document.getElementById('currency_hidden').value  = code;
    document.getElementById('currencyDisplay').value  = `${flag}  ${code} — ${name}`;
    document.getElementById('currencyDropdown').classList.remove('open');
    document.getElementById('previewCurrency').textContent = code;
    renderCurrencies();
}

document.getElementById('currencyDisplay').addEventListener('click', () => {
    const dd = document.getElementById('currencyDropdown');
    dd.classList.toggle('open');
    if (dd.classList.contains('open')) document.getElementById('currencySearch').focus();
});

document.addEventListener('click', e => {
    if (!document.getElementById('currencyWrap').contains(e.target))
        document.getElementById('currencyDropdown').classList.remove('open');
});

document.getElementById('currencySearch').addEventListener('input', e => {
    renderCurrencies(e.target.value);
});

renderCurrencies();
const shopCurrency = @json(old('currency', $shop->currency ?? 'GNF'));
const found = CURRENCIES.find(c => c.code === shopCurrency) || CURRENCIES.find(c => c.code === 'GNF');
if (found) selectCurrency(found.code, found.name, found.flag);

/* ════════════════════════════════════════════════════════════════
   IMAGE — aperçu avant envoi (clic ou glisser-déposer)
════════════════════════════════════════════════════════════════ */
const imageInput        = document.getElementById('imageInput');
const uploadPreview     = document.getElementById('uploadPreview');
const previewImg        = document.getElementById('previewImg');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const removeImg         = document.getElementById('removeImg');
const uploadZone        = document.getElementById('uploadZone');
const sidebarImg        = document.getElementById('sidebarPreviewImg');
const sidebarImgSrc     = sidebarImg.getAttribute('src');

imageInput.addEventListener('change', e => {
    if (e.target.files[0]) showPreview(e.target.files[0]);
});

function showPreview(file) {
    if (file.size > 2 * 1024 * 1024) {
        alert('Image trop lourde (2 Mo maximum).');
        imageInput.value = '';
        return;
    }
    const url = URL.createObjectURL(file);
    previewImg.src = url;
    uploadPreview.style.display = 'block';
    uploadPlaceholder.style.display = 'none';
    sidebarImg.src = url;
    sidebarImg.style.display = 'block';
    document.getElementById('previewLogo').style.display = 'none';
}

removeImg.addEventListener('click', e => {
    e.preventDefault();
    e.stopPropagation();
    imageInput.value = '';
    previewImg.src   = '';
    uploadPreview.style.display     = 'none';
    uploadPlaceholder.style.display = 'block';
    /* La pastille du menu revient à l'image actuelle (ou aux initiales) */
    if (sidebarImgSrc) { sidebarImg.src = sidebarImgSrc; }
    else { sidebarImg.style.display = 'none'; document.getElementById('previewLogo').style.display = 'flex'; }
});

uploadZone.addEventListener('dragover',  e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        imageInput.files = dt.files;
        showPreview(file);
        markDirty();
    }
});

/* ════════════════════════════════════════════════════════════════
   NOM — repris dans le menu en direct
════════════════════════════════════════════════════════════════ */
document.getElementById('name').addEventListener('input', e => {
    document.getElementById('previewName').textContent = e.target.value || 'Nom de votre boutique';
});

/* ════════════════════════════════════════════════════════════════
   TYPE DE BOUTIQUE — pastilles
════════════════════════════════════════════════════════════════ */
function selectType(value) {
    document.getElementById('type').value = value;
    document.querySelectorAll('#typePills .type-pill:not(.type-pill-add)').forEach(p => {
        p.classList.toggle('selected', p.dataset.val === value);
    });
    document.getElementById('previewType').textContent = value;
    markDirty();
}

function showCustomType() {
    document.getElementById('typeCustomWrap').style.display = 'flex';
    document.getElementById('typeCustomInput').focus();
}

function hideCustomType() {
    document.getElementById('typeCustomWrap').style.display = 'none';
    document.getElementById('typeCustomInput').value = '';
}

function addCustomPill(val) {
    const prev = document.querySelector('#typePills .type-pill[data-custom]');
    if (prev) prev.remove();
    const pill = document.createElement('button');
    pill.type = 'button';
    pill.className = 'type-pill selected';
    pill.dataset.val = val;
    pill.dataset.custom = '1';
    pill.textContent = val;
    pill.onclick = () => selectType(val);
    document.querySelector('.type-pill-add').insertAdjacentElement('beforebegin', pill);
}

function confirmCustomType() {
    const val = document.getElementById('typeCustomInput').value.trim();
    if (!val) return;
    addCustomPill(val);
    selectType(val);
    hideCustomType();
}

document.getElementById('typeCustomInput').addEventListener('keydown', e => {
    if (e.key === 'Enter')  { e.preventDefault(); confirmCustomType(); }
    if (e.key === 'Escape') hideCustomType();
});

/* Type actuel de la boutique pré-sélectionné */
(function () {
    const currentType = document.getElementById('type').value;
    if (!currentType) return;
    const knownPill = document.querySelector(`#typePills .type-pill[data-val="${CSS.escape(currentType)}"]`);
    if (knownPill) knownPill.classList.add('selected');
    else addCustomPill(currentType);
})();

/* ════════════════════════════════════════════════════════════════
   ENREGISTRER — « modifications non enregistrées », puis loader
════════════════════════════════════════════════════════════════ */
const form = document.getElementById('shopForm');
let dirty = false, sending = false;
function markDirty() {
    if (dirty) return;
    dirty = true;
    const s = document.getElementById('saveState');
    s.textContent = 'Modifications non enregistrées';
    s.classList.add('is-dirty');
}
form.addEventListener('input', e => { if (e.target.id !== 'currencySearch' && e.target.id !== 'typeCustomInput') markDirty(); });
form.addEventListener('change', e => { if (e.target.id !== 'currencySearch' && e.target.id !== 'typeCustomInput') markDirty(); });
window.addEventListener('beforeunload', e => { if (dirty && !sending) { e.preventDefault(); e.returnValue = ''; } });

form.addEventListener('submit', () => {
    sending = true;
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Enregistrement…';
});

/* À l'ouverture : partie avec une erreur, sinon ?etape=N (ex. depuis Marketing), sinon #section-N */
(function () {
    const fromError = @json($errorPart);
    const fromQuery = parseInt(new URLSearchParams(location.search).get('etape'), 10);
    const fromHash  = parseInt((location.hash.match(/^#section-(\d)$/) || [])[1], 10);
    const n = fromError || (fromQuery >= 1 && fromQuery <= 4 ? fromQuery : null) || (fromHash >= 1 && fromHash <= 4 ? fromHash : null);
    if (!n) return;
    const open = () => requestAnimationFrame(() => { if (n > 1) goToPart(n, false); else if (fromError) document.getElementById('name').focus(); });
    /* Le contenu reste caché tant que l'écran de chargement du site est affiché (body.pg-loading) : on attend qu'il parte */
    if (!document.body.classList.contains('pg-loading')) return open();
    const mo = new MutationObserver(() => { if (!document.body.classList.contains('pg-loading')) { mo.disconnect(); open(); } });
    mo.observe(document.body, { attributes: true, attributeFilter: ['class'] });
})();
</script>
@endpush
{{-- Thème bleu « espace vendeur », chargé après les styles de la page --}}
@push('styles')
@include('boutique._partials.sv-theme')
@endpush
