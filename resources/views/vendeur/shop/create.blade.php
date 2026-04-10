{{--
    resources/views/vendeur/shop/create.blade.php
    Route : POST /shop → ShopController@store → name('shop.store')
--}}

@extends('layouts.app')

@section('title', 'Créer ma boutique')

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --brand:     #10b981;
    --brand-dk:  #059669;
    --brand-lt:  #d1fae5;
    --brand-mlt: #ecfdf5;
    --bg:        #f6f8f7;
    --surface:   #ffffff;
    --border:    #e2e8f0;
    --border-dk: #cbd5e1;
    --text:      #0f172a;
    --text-2:    #475569;
    --muted:     #94a3b8;
    --danger:    #ef4444;
    --danger-lt: #fef2f2;
    --font:      'Plus Jakarta Sans', sans-serif;
    --mono:      'JetBrains Mono', monospace;
    --r:         14px;
    --r-sm:      9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
    --shadow:    0 8px 32px rgba(0,0,0,.08);
    --shadow-lg: 0 20px 60px rgba(0,0,0,.12);
}

html, body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--text);
    margin: 0;
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
}

/* ── Page layout ── */
.form-page {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 0;
}

/* ── Panneau gauche (info + preview) ── */
.form-sidebar {
    background: linear-gradient(160deg, #0d1f18 0%, #1a3328 60%, #0d2218 100%);
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}
.form-sidebar::-webkit-scrollbar { width: 4px; }
.form-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }

.sidebar-logo {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none; color: #fff; margin-bottom: 40px;
    font-size: 15px; font-weight: 700;
}
.sidebar-logo-ico {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--brand), #34d399);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
    box-shadow: 0 2px 10px rgba(16,185,129,.4);
}

.sidebar-title {
    font-size: 28px; font-weight: 800;
    color: #fff; letter-spacing: -.6px;
    line-height: 1.2; margin-bottom: 12px;
}
.sidebar-title span {
    background: linear-gradient(135deg, #34d399, #10b981);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.sidebar-sub {
    font-size: 14px; color: rgba(255,255,255,.5);
    line-height: 1.7; margin-bottom: 36px;
}

/* Preview carte boutique */
.shop-preview {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: var(--r);
    overflow: hidden;
    margin-bottom: 32px;
    transition: all .3s;
}
.preview-img-wrap {
    height: 140px;
    background: linear-gradient(135deg, rgba(16,185,129,.15), rgba(52,211,153,.08));
    display: flex; align-items: center; justify-content: center;
    position: relative; overflow: hidden;
}
.preview-img-wrap img {
    width: 100%; height: 100%; object-fit: cover;
    display: none;
}
.preview-img-wrap .preview-placeholder {
    font-size: 42px; opacity: .35;
}
.preview-body { padding: 16px 18px; }
.preview-name {
    font-size: 15px; font-weight: 700; color: #fff;
    margin-bottom: 4px;
}
.preview-meta {
    font-size: 12px; color: rgba(255,255,255,.4);
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.preview-badge {
    font-size: 10px; font-weight: 700;
    background: rgba(16,185,129,.2); color: #34d399;
    border: 1px solid rgba(16,185,129,.3);
    padding: 2px 8px; border-radius: 20px;
}
.preview-currency {
    font-family: var(--mono); font-size: 11px;
    color: rgba(255,255,255,.3);
}

/* Steps progress */
.steps-list { display: flex; flex-direction: column; gap: 12px; }
.step-row {
    display: flex; align-items: center; gap: 12px;
    opacity: .4; transition: opacity .3s;
}
.step-row.active { opacity: 1; }
.step-dot {
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: rgba(255,255,255,.4);
    flex-shrink: 0; transition: all .3s;
}
.step-row.active .step-dot {
    background: var(--brand); border-color: var(--brand-dk);
    color: #fff; box-shadow: 0 0 12px rgba(16,185,129,.4);
}
.step-row.done .step-dot {
    background: rgba(16,185,129,.2); border-color: rgba(16,185,129,.4);
    color: #34d399;
}
.step-txt { font-size: 13px; color: rgba(255,255,255,.6); font-weight: 500; }
.step-row.active .step-txt { color: #fff; font-weight: 600; }

/* ── Panneau droit (formulaire) ── */
.form-main {
    background: var(--bg);
    padding: 48px 40px;
    overflow-y: auto;
}

/* Stepper tabs */
.form-steps-tabs {
    display: flex; gap: 0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    margin-bottom: 28px;
    box-shadow: var(--shadow-sm);
}
.step-tab {
    flex: 1; padding: 12px 8px;
    text-align: center; font-size: 12px; font-weight: 600;
    color: var(--muted); cursor: pointer;
    border-right: 1px solid var(--border);
    transition: all .15s; user-select: none;
    display: flex; align-items: center; justify-content: center; gap: 5px;
}
.step-tab:last-child { border-right: none; }
.step-tab:hover { background: var(--bg); color: var(--text-2); }
.step-tab.active { background: var(--brand-mlt); color: var(--brand-dk); }
.step-tab .tab-num {
    width: 18px; height: 18px; border-radius: 50%;
    background: var(--border); color: var(--muted);
    font-size: 10px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.step-tab.active .tab-num { background: var(--brand); color: #fff; }
.step-tab.done .tab-num { background: var(--brand-lt); color: var(--brand-dk); }

/* Sections du formulaire */
.form-section { display: none; }
.form-section.active { display: block; animation: fadeIn .25s ease; }
@keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

.section-hd { margin-bottom: 22px; }
.section-hd h2 { font-size: 18px; font-weight: 700; color: var(--text); margin: 0 0 4px; }
.section-hd p  { font-size: 13px; color: var(--muted); margin: 0; }

/* ── Champs de formulaire ── */
.field-group { margin-bottom: 18px; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.field-label {
    display: block;
    font-size: 12px; font-weight: 700; color: var(--text-2);
    text-transform: uppercase; letter-spacing: .4px;
    margin-bottom: 6px;
}
.field-label span { color: var(--danger); margin-left: 2px; }

.field-input {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    font-size: 13.5px; font-family: var(--font);
    color: var(--text); background: var(--surface);
    outline: none; transition: border-color .15s, box-shadow .15s;
    appearance: none;
}
.field-input:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(16,185,129,.12);
}
.field-input:focus + .field-icon { color: var(--brand); }
.field-input.error { border-color: var(--danger); }
.field-input.error:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.1); }

.field-wrap { position: relative; }
.field-icon {
    position: absolute; left: 12px; top: 50%;
    transform: translateY(-50%);
    font-size: 15px; color: var(--muted);
    pointer-events: none; transition: color .15s;
}
.field-wrap .field-input { padding-left: 38px; }

.field-hint { font-size: 11.5px; color: var(--muted); margin-top: 5px; }
.field-error { font-size: 11.5px; color: var(--danger); margin-top: 5px; display: none; }

textarea.field-input { resize: vertical; min-height: 90px; }

/* ── Select devise spécial ── */
.currency-wrap { position: relative; }
.currency-search-box {
    width: 100%;
    padding: 11px 14px 11px 38px;
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    font-size: 13.5px; font-family: var(--font);
    color: var(--text); background: var(--surface);
    outline: none; transition: border-color .15s, box-shadow .15s;
    cursor: pointer;
}
.currency-search-box:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(16,185,129,.12);
}
.currency-dropdown {
    position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    box-shadow: var(--shadow);
    z-index: 100; overflow: hidden;
    display: none; max-height: 280px;
}
.currency-dropdown.open { display: block; }
.currency-search-input {
    width: 100%; padding: 10px 14px;
    border: none; border-bottom: 1px solid var(--border);
    font-size: 13px; font-family: var(--font); outline: none;
    color: var(--text); background: var(--bg);
}
.currency-list { overflow-y: auto; max-height: 220px; }
.currency-list::-webkit-scrollbar { width: 4px; }
.currency-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.currency-opt {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 14px; cursor: pointer;
    transition: background .12s; font-size: 13px;
    border-bottom: 1px solid #f8fafc;
}
.currency-opt:hover { background: var(--brand-mlt); }
.currency-opt.selected { background: var(--brand-mlt); }
.currency-flag { font-size: 18px; flex-shrink: 0; }
.currency-code {
    font-family: var(--mono); font-size: 12px; font-weight: 700;
    color: var(--brand-dk); background: var(--brand-lt);
    padding: 2px 7px; border-radius: 5px; flex-shrink: 0;
}
.currency-name { color: var(--text-2); font-size: 12.5px; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.currency-country { font-size: 11px; color: var(--muted); flex-shrink: 0; }

/* Input hidden pour la devise */
#currency_hidden { display: none; }

/* ── Upload image ── */
.upload-zone {
    border: 2px dashed var(--border);
    border-radius: var(--r);
    padding: 28px 20px;
    text-align: center;
    cursor: pointer;
    transition: all .2s; position: relative;
    background: var(--surface);
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: var(--brand);
    background: var(--brand-mlt);
}
.upload-zone input[type=file] {
    position: absolute; inset: 0;
    opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.upload-icon { font-size: 32px; margin-bottom: 8px; opacity: .5; }
.upload-txt  { font-size: 13px; font-weight: 600; color: var(--text-2); }
.upload-hint { font-size: 11.5px; color: var(--muted); margin-top: 4px; }
.upload-preview {
    display: none; position: relative;
    border-radius: var(--r-sm); overflow: hidden;
    margin-top: 14px;
}
.upload-preview img {
    width: 100%; max-height: 160px; object-fit: cover; border-radius: var(--r-sm);
    display: block;
}
.upload-remove {
    position: absolute; top: 8px; right: 8px;
    background: rgba(0,0,0,.6); color: #fff;
    border: none; border-radius: 50%;
    width: 28px; height: 28px; cursor: pointer;
    font-size: 14px; display: flex; align-items: center; justify-content: center;
}

/* ── Commission slider ── */
.commission-wrap {
    background: var(--bg);
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    padding: 16px 18px;
}
.commission-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 12px;
}
.commission-val {
    font-size: 24px; font-weight: 800; font-family: var(--mono);
    color: var(--brand); letter-spacing: -1px;
}
.commission-val span { font-size: 14px; }
input[type=range] {
    width: 100%; height: 6px; border-radius: 3px;
    background: linear-gradient(to right, var(--brand) 0%, var(--brand) 20%, var(--border) 20%, var(--border) 100%);
    outline: none; appearance: none; cursor: pointer;
}
input[type=range]::-webkit-slider-thumb {
    appearance: none; width: 20px; height: 20px;
    border-radius: 50%; background: var(--brand);
    border: 3px solid #fff; box-shadow: 0 1px 6px rgba(16,185,129,.4);
    cursor: pointer;
}
.commission-labels {
    display: flex; justify-content: space-between;
    font-size: 10px; color: var(--muted); margin-top: 6px;
    font-family: var(--mono);
}

/* ── Boutons navigation ── */
.form-nav {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 28px; padding-top: 20px;
    border-top: 1px solid var(--border);
    gap: 12px;
}
.btn-back {
    padding: 11px 20px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 600; font-family: var(--font);
    border: 1.5px solid var(--border-dk); background: var(--surface);
    color: var(--text-2); cursor: pointer; transition: all .15s;
    text-decoration: none;
}
.btn-back:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-next {
    padding: 11px 24px; border-radius: var(--r-sm);
    font-size: 13.5px; font-weight: 700; font-family: var(--font);
    background: var(--brand); color: #fff;
    border: 1.5px solid var(--brand-dk);
    cursor: pointer; transition: all .15s;
    display: flex; align-items: center; gap: 7px;
    box-shadow: 0 4px 14px rgba(16,185,129,.3);
}
.btn-next:hover { background: var(--brand-dk); box-shadow: 0 6px 20px rgba(16,185,129,.4); transform: translateY(-1px); }
.btn-next:disabled { opacity: .6; cursor: not-allowed; transform: none; }

.btn-submit {
    padding: 13px 32px; border-radius: var(--r-sm);
    font-size: 14px; font-weight: 700; font-family: var(--font);
    background: var(--brand); color: #fff;
    border: 1.5px solid var(--brand-dk);
    cursor: pointer; transition: all .15s;
    display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 4px 14px rgba(16,185,129,.3);
}
.btn-submit:hover { background: var(--brand-dk); transform: translateY(-1px); }

/* ── Flash messages ── */
.flash { padding: 11px 14px; border-radius: var(--r-sm); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 20px; display: flex; gap: 8px; align-items: flex-start; }
.flash-danger  { background: var(--danger-lt); border-color: #fca5a5; color: #991b1b; }
.flash-success { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; }
.flash-info    { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
.flash ul { margin: 0; padding-left: 16px; }
.flash ul li { margin-top: 3px; font-size: 12.5px; }

/* ── Progress bar ── */
.progress-bar-wrap {
    height: 3px; background: var(--border);
    border-radius: 3px; overflow: hidden; margin-bottom: 24px;
}
.progress-bar-fill {
    height: 100%; background: var(--brand);
    border-radius: 3px; transition: width .4s cubic-bezier(.23,1,.32,1);
}

/* ── Responsive ── */
@media (max-width: 900px) {
    .form-page { grid-template-columns: 1fr; }
    .form-sidebar { display: none; }
    .form-main { padding: 28px 20px; }
}
@media (max-width: 480px) {
    .field-row { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="form-page">

    {{-- ══════ SIDEBAR GAUCHE ══════ --}}
    <aside class="form-sidebar">

        <a href="{{ route('boutique.dashboard') }}" class="sidebar-logo">
            <div class="sidebar-logo-ico">🛍️</div>
            {{ config('app.name', 'ShopManager') }}
        </a>

        <h1 class="sidebar-title">
            Créez votre<br><span>boutique</span>
        </h1>
        <p class="sidebar-sub">
            Configurez votre espace de vente en quelques étapes simples.
            Commencez à vendre en moins de 5 minutes.
        </p>

        {{-- Aperçu live de la boutique --}}
        <div class="shop-preview">
            <div class="preview-img-wrap">
                <img id="sidebarPreviewImg" src="" alt="">
                <span class="preview-placeholder">🛍️</span>
            </div>
            <div class="preview-body">
                <div class="preview-name" id="previewName">Nom de votre boutique</div>
                <div class="preview-meta">
                    <span id="previewType" style="display:none"></span>
                    <span class="preview-badge">✓ Boutique</span>
                    <span class="preview-currency" id="previewCurrency">GNF</span>
                </div>
            </div>
        </div>

        {{-- Étapes --}}
        <div class="steps-list">
            <div class="step-row active" data-step="1">
                <div class="step-dot">1</div>
                <span class="step-txt">Informations générales</span>
            </div>
            <div class="step-row" data-step="2">
                <div class="step-dot">2</div>
                <span class="step-txt">Contact & localisation</span>
            </div>
            <div class="step-row" data-step="3">
                <div class="step-dot">3</div>
                <span class="step-txt">Devise & commission</span>
            </div>
            <div class="step-row" data-step="4">
                <div class="step-dot">4</div>
                <span class="step-txt">Image & finalisation</span>
            </div>
        </div>

    </aside>

    {{-- ══════ FORMULAIRE PRINCIPAL ══════ --}}
    <main class="form-main">

        {{-- Messages flash --}}
        @if($errors->any())
        <div class="flash flash-danger">
            <span>⚠</span>
            <div>
                <strong>Veuillez corriger les erreurs suivantes :</strong>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        </div>
        @endif
        @if(session('success'))
        <div class="flash flash-success"><span>✓</span> {{ session('success') }}</div>
        @endif
        @if(session('info'))
        <div class="flash flash-info"><span>ℹ</span> {{ session('info') }}</div>
        @endif

        {{-- Barre de progression --}}
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" id="progressBar" style="width:25%"></div>
        </div>

        {{-- Tabs étapes --}}
        <div class="form-steps-tabs">
            <div class="step-tab active" data-tab="1">
                <span class="tab-num">1</span> Général
            </div>
            <div class="step-tab" data-tab="2">
                <span class="tab-num">2</span> Contact
            </div>
            <div class="step-tab" data-tab="3">
                <span class="tab-num">3</span> Devise
            </div>
            <div class="step-tab" data-tab="4">
                <span class="tab-num">4</span> Image
            </div>
        </div>

        {{-- Formulaire unique --}}
        <form action="{{ route('shop.store') }}" method="POST" enctype="multipart/form-data" id="shopForm">
            @csrf

            {{-- ══ ÉTAPE 1 : Informations générales ══ --}}
            <div class="form-section active" id="section-1">
                <div class="section-hd">
                    <h2>🏪 Informations générales</h2>
                    <p>Les informations principales de votre boutique.</p>
                </div>

                <div class="field-group">
                    <label class="field-label" for="name">Nom de la boutique <span>*</span></label>
                    <div class="field-wrap">
                        <span class="field-icon">🏪</span>
                        <input type="text" name="name" id="name"
                               class="field-input {{ $errors->has('name') ? 'error' : '' }}"
                               value="{{ old('name') }}"
                               placeholder="Ex : Boutique Aminata"
                               required>
                    </div>
                    @error('name')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
                </div>

                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label" for="type">Type de boutique</label>
                        <div class="field-wrap">
                            <span class="field-icon">🏷️</span>
                            <select name="type" id="type" class="field-input">
                                <option value="">Sélectionner…</option>
                                @foreach([
                                    'Alimentation','Restaurant','Pharmacie','Vêtements','Électronique',
                                    'Beauté & Cosmétiques','Meubles & Décoration','Sport & Loisirs',
                                    'Librairie','Boulangerie','Épicerie','Bijouterie','Auto & Moto',
                                    'Informatique','Téléphonie','Jouets','Fleurs','Autre'
                                ] as $t)
                                <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="email">Email boutique <span>*</span></label>
                        <div class="field-wrap">
                            <span class="field-icon">✉️</span>
                            <input type="email" name="email" id="email"
                                   class="field-input {{ $errors->has('email') ? 'error' : '' }}"
                                   value="{{ old('email') }}"
                                   placeholder="boutique@email.com"
                                   required>
                        </div>
                        @error('email')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="description">Description</label>
                    <textarea name="description" id="description"
                              class="field-input"
                              placeholder="Décrivez votre boutique en quelques mots…"
                              rows="3">{{ old('description') }}</textarea>
                    <div class="field-hint">Max 500 caractères. Une bonne description attire plus de clients.</div>
                </div>

                <div class="form-nav">
                    <a href="{{ route('boutique.dashboard') }}" class="btn-back">← Retour</a>
                    <button type="button" class="btn-next" onclick="goToStep(2)">
                        Contact & localisation <span>→</span>
                    </button>
                </div>
            </div>

            {{-- ══ ÉTAPE 2 : Contact & localisation ══ --}}
            <div class="form-section" id="section-2">
                <div class="section-hd">
                    <h2>📍 Contact & localisation</h2>
                    <p>Comment vos clients peuvent vous joindre et vous trouver.</p>
                </div>

                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label" for="phone">Téléphone</label>
                        <div class="field-wrap">
                            <span class="field-icon">📞</span>
                            <input type="tel" name="phone" id="phone"
                                   class="field-input"
                                   value="{{ old('phone') }}"
                                   placeholder="+224 6XX XXX XXX">
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="whatsapp">WhatsApp</label>
                        <div class="field-wrap">
                            <span class="field-icon">💬</span>
                            <input type="tel" name="whatsapp" id="whatsapp"
                                   class="field-input"
                                   value="{{ old('whatsapp') }}"
                                   placeholder="+224 6XX XXX XXX">
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="address">Adresse complète</label>
                    <div class="field-wrap">
                        <span class="field-icon">📍</span>
                        <input type="text" name="address" id="address"
                               class="field-input"
                               value="{{ old('address') }}"
                               placeholder="Ex : Kaloum, Conakry, Guinée">
                    </div>
                </div>

                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label" for="ville">Ville</label>
                        <div class="field-wrap">
                            <span class="field-icon">🏙️</span>
                            <input type="text" name="ville" id="ville"
                                   class="field-input"
                                   value="{{ old('ville') }}"
                                   placeholder="Ex : Conakry">
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="pays">Pays</label>
                        <div class="field-wrap">
                            <span class="field-icon">🌍</span>
                            <select name="pays" id="pays" class="field-input">
                                <option value="">Sélectionner…</option>
                                @foreach(['Guinée','Sénégal','Mali','Côte d\'Ivoire','Burkina Faso','Niger','Cameroun','Maroc','Algérie','Tunisie','France','Belgique','Canada','Autre'] as $p)
                                <option value="{{ $p }}" {{ old('pays', 'Guinée') === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-back" onclick="goToStep(1)">← Retour</button>
                    <button type="button" class="btn-next" onclick="goToStep(3)">
                        Devise & commission <span>→</span>
                    </button>
                </div>
            </div>

            {{-- ══ ÉTAPE 3 : Devise & Commission ══ --}}
            <div class="form-section" id="section-3">
                <div class="section-hd">
                    <h2>💱 Devise & Commission</h2>
                    <p>Choisissez votre devise et définissez le taux de commission livreurs.</p>
                </div>

                {{-- Sélecteur de devise personnalisé --}}
                <div class="field-group">
                    <label class="field-label">Devise principale <span>*</span></label>
                    <div class="currency-wrap" id="currencyWrap">
                        <div class="field-wrap">
                            <span class="field-icon">💱</span>
                            <input type="text" class="currency-search-box" id="currencyDisplay"
                                   placeholder="Sélectionner une devise…"
                                   readonly>
                        </div>
                        <input type="hidden" name="currency" id="currency_hidden" value="{{ old('currency', 'GNF') }}">

                        <div class="currency-dropdown" id="currencyDropdown">
                            <input type="text" class="currency-search-input"
                                   id="currencySearch"
                                   placeholder="🔍  Rechercher une devise ou un pays…"
                                   autocomplete="off">
                            <div class="currency-list" id="currencyList">
                                {{-- Rempli par JS --}}
                            </div>
                        </div>
                    </div>
                    <div class="field-hint">La devise sera affichée sur toutes vos commandes et factures.</div>
                </div>

                {{-- Commission slider --}}
                <div class="field-group">
                    <label class="field-label">Taux de commission livreurs</label>
                    <div class="commission-wrap">
                        <div class="commission-header">
                            <div>
                                <div style="font-size:12px;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px">Commission par livraison</div>
                                <div style="font-size:11.5px;color:var(--muted)">Appliqué automatiquement à chaque commande livrée</div>
                            </div>
                            <div class="commission-val">
                                <span id="commissionDisplay">20</span>%
                            </div>
                        </div>
                        <input type="range" name="commission_rate" id="commission_rate"
                               min="0" max="50" step="0.5"
                               value="{{ old('commission_rate', 20) }}"
                               oninput="updateCommission(this.value)">
                        <div class="commission-labels">
                            <span>0%</span><span>10%</span><span>20%</span><span>30%</span><span>40%</span><span>50%</span>
                        </div>
                    </div>
                    <div class="field-hint">
                        Recommandé : entre 10% et 25%. Ce taux est déduit du montant de chaque commande livrée.
                    </div>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-back" onclick="goToStep(2)">← Retour</button>
                    <button type="button" class="btn-next" onclick="goToStep(4)">
                        Image & finalisation <span>→</span>
                    </button>
                </div>
            </div>

            {{-- ══ ÉTAPE 4 : Image & Finalisation ══ --}}
            <div class="form-section" id="section-4">
                <div class="section-hd">
                    <h2>🖼️ Image & Finalisation</h2>
                    <p>Ajoutez un logo ou une photo pour votre boutique.</p>
                </div>

                <div class="field-group">
                    <label class="field-label">Logo / Image de la boutique</label>
                    <div class="upload-zone" id="uploadZone">
                        <input type="file" name="image" id="imageInput" accept="image/*">
                        <div id="uploadPlaceholder">
                            <div class="upload-icon">🖼️</div>
                            <div class="upload-txt">Cliquez ou glissez votre image ici</div>
                            <div class="upload-hint">JPG, PNG, WEBP — Max 4 Mo</div>
                        </div>
                        <div class="upload-preview" id="uploadPreview">
                            <img id="previewImg" src="" alt="Aperçu">
                            <button type="button" class="upload-remove" id="removeImg">✕</button>
                        </div>
                    </div>
                </div>

                {{-- Récapitulatif --}}
                <div style="background:var(--bg);border:1.5px solid var(--border);border-radius:var(--r);padding:18px 20px;margin-bottom:8px">
                    <div style="font-size:12px;font-weight:700;color:var(--text-2);text-transform:uppercase;letter-spacing:.4px;margin-bottom:12px">
                        ✅ Récapitulatif
                    </div>
                    <div style="display:flex;flex-direction:column;gap:7px;font-size:13px;color:var(--text-2)">
                        <div><strong style="color:var(--text)">Nom :</strong> <span id="recapName">—</span></div>
                        <div><strong style="color:var(--text)">Type :</strong> <span id="recapType">—</span></div>
                        <div><strong style="color:var(--text)">Email :</strong> <span id="recapEmail">—</span></div>
                        <div><strong style="color:var(--text)">Téléphone :</strong> <span id="recapPhone">—</span></div>
                        <div><strong style="color:var(--text)">Devise :</strong> <span id="recapCurrency">GNF</span></div>
                        <div><strong style="color:var(--text)">Commission :</strong> <span id="recapCommission">20%</span></div>
                    </div>
                </div>

                <div class="form-nav">
                    <button type="button" class="btn-back" onclick="goToStep(3)">← Retour</button>
                    <button type="submit" class="btn-submit" id="submitBtn">
                        🚀 Créer ma boutique
                    </button>
                </div>
            </div>

        </form>
    </main>
</div>
@endsection

@push('scripts')
<script>
/* ════════════════════════════════════════════════════════════════
   DONNÉES : toutes les devises du monde avec drapeau, code, pays
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
    { code: 'VND', name: 'Dong vietnamien',        flag: '🇻🇳', country: 'Vietnam' },
    { code: 'PKR', name: 'Roupie pakistanaise',    flag: '🇵🇰', country: 'Pakistan' },
    { code: 'BDT', name: 'Taka bangladais',        flag: '🇧🇩', country: 'Bangladesh' },
    { code: 'LBP', name: 'Livre libanaise',        flag: '🇱🇧', country: 'Liban' },
    { code: 'JOD', name: 'Dinar jordanien',        flag: '🇯🇴', country: 'Jordanie' },
    { code: 'DKK', name: 'Couronne danoise',       flag: '🇩🇰', country: 'Danemark' },
    { code: 'SEK', name: 'Couronne suédoise',      flag: '🇸🇪', country: 'Suède' },
    { code: 'NOK', name: 'Couronne norvégienne',   flag: '🇳🇴', country: 'Norvège' },
    { code: 'PLN', name: 'Zloty polonais',         flag: '🇵🇱', country: 'Pologne' },
    { code: 'CZK', name: 'Couronne tchèque',       flag: '🇨🇿', country: 'Tchéquie' },
    { code: 'HUF', name: 'Forint hongrois',        flag: '🇭🇺', country: 'Hongrie' },
    { code: 'RON', name: 'Leu roumain',            flag: '🇷🇴', country: 'Roumanie' },
    { code: 'UAH', name: 'Hryvnia ukrainienne',    flag: '🇺🇦', country: 'Ukraine' },
    { code: 'CLP', name: 'Peso chilien',           flag: '🇨🇱', country: 'Chili' },
    { code: 'COP', name: 'Peso colombien',         flag: '🇨🇴', country: 'Colombie' },
    { code: 'ARS', name: 'Peso argentin',          flag: '🇦🇷', country: 'Argentine' },
    { code: 'PEN', name: 'Sol péruvien',           flag: '🇵🇪', country: 'Pérou' },
    { code: 'NZD', name: 'Dollar néo-zélandais',   flag: '🇳🇿', country: 'Nouvelle-Zélande' },
];

/* ════════════════════════════════════════════════════════════════
   GESTION DES ÉTAPES
════════════════════════════════════════════════════════════════ */
let currentStep = 1;
const totalSteps = 4;

function goToStep(step) {
    /* Cacher la section actuelle */
    document.getElementById(`section-${currentStep}`).classList.remove('active');

    /* Mettre à jour les tabs */
    document.querySelectorAll('.step-tab').forEach(t => {
        const n = parseInt(t.dataset.tab);
        t.classList.remove('active');
        if (n === step) t.classList.add('active');
    });

    /* Mettre à jour les steps sidebar */
    document.querySelectorAll('.step-row').forEach(r => {
        const n = parseInt(r.dataset.step);
        r.classList.remove('active', 'done');
        if (n === step) r.classList.add('active');
        else if (n < step) r.classList.add('done');
    });

    currentStep = step;
    document.getElementById(`section-${step}`).classList.add('active');

    /* Mettre à jour la barre de progression */
    document.getElementById('progressBar').style.width = (step / totalSteps * 100) + '%';

    /* Mettre à jour le récapitulatif à l'étape 4 */
    if (step === 4) updateRecap();

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ════════════════════════════════════════════════════════════════
   DEVISE — Rendre la liste et gérer la sélection
════════════════════════════════════════════════════════════════ */
function renderCurrencies(filter = '') {
    const list = document.getElementById('currencyList');
    const q    = filter.toLowerCase();
    const filtered = CURRENCIES.filter(c =>
        c.code.toLowerCase().includes(q) ||
        c.name.toLowerCase().includes(q) ||
        c.country.toLowerCase().includes(q)
    );

    const current = document.getElementById('currency_hidden').value;

    list.innerHTML = filtered.map(c => `
        <div class="currency-opt ${c.code === current ? 'selected' : ''}"
             data-code="${c.code}" data-name="${c.name}" data-flag="${c.flag}">
            <span class="currency-flag">${c.flag}</span>
            <span class="currency-code">${c.code}</span>
            <span class="currency-name">${c.name}</span>
            <span class="currency-country">${c.country}</span>
        </div>
    `).join('');

    /* Clic sur une option */
    list.querySelectorAll('.currency-opt').forEach(opt => {
        opt.addEventListener('click', () => {
            selectCurrency(opt.dataset.code, opt.dataset.name, opt.dataset.flag);
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

/* Ouvrir/fermer le dropdown */
document.getElementById('currencyDisplay').addEventListener('click', () => {
    const dd = document.getElementById('currencyDropdown');
    dd.classList.toggle('open');
    if (dd.classList.contains('open')) {
        document.getElementById('currencySearch').focus();
    }
});

/* Fermer si clic à l'extérieur */
document.addEventListener('click', e => {
    if (!document.getElementById('currencyWrap').contains(e.target)) {
        document.getElementById('currencyDropdown').classList.remove('open');
    }
});

/* Filtrage en temps réel */
document.getElementById('currencySearch').addEventListener('input', e => {
    renderCurrencies(e.target.value);
});

/* Init : sélectionner GNF par défaut */
renderCurrencies();
const defaultCurrency = CURRENCIES.find(c => c.code === 'GNF');
if (defaultCurrency) {
    selectCurrency(defaultCurrency.code, defaultCurrency.name, defaultCurrency.flag);
}

/* ════════════════════════════════════════════════════════════════
   COMMISSION SLIDER
════════════════════════════════════════════════════════════════ */
function updateCommission(val) {
    document.getElementById('commissionDisplay').textContent = parseFloat(val).toFixed(val % 1 === 0 ? 0 : 1);
    /* Mettre à jour le dégradé du slider */
    const pct = (val / 50) * 100;
    document.getElementById('commission_rate').style.background =
        `linear-gradient(to right, var(--brand) 0%, var(--brand) ${pct}%, var(--border) ${pct}%, var(--border) 100%)`;
    document.getElementById('recapCommission').textContent = parseFloat(val).toFixed(1) + '%';
}
updateCommission(document.getElementById('commission_rate').value);

/* ════════════════════════════════════════════════════════════════
   UPLOAD IMAGE
════════════════════════════════════════════════════════════════ */
const imageInput   = document.getElementById('imageInput');
const uploadPreview = document.getElementById('uploadPreview');
const previewImg   = document.getElementById('previewImg');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const removeImg    = document.getElementById('removeImg');
const uploadZone   = document.getElementById('uploadZone');

imageInput.addEventListener('change', e => {
    if (e.target.files[0]) showPreview(e.target.files[0]);
});

function showPreview(file) {
    const url = URL.createObjectURL(file);
    previewImg.src = url;
    uploadPreview.style.display = 'block';
    uploadPlaceholder.style.display = 'none';

    /* Mettre à jour la preview dans la sidebar */
    const sidebarImg = document.getElementById('sidebarPreviewImg');
    sidebarImg.src = url;
    sidebarImg.style.display = 'block';
    sidebarImg.previousElementSibling?.style && (document.querySelector('.preview-placeholder').style.display = 'none');
}

removeImg.addEventListener('click', () => {
    imageInput.value = '';
    previewImg.src = '';
    uploadPreview.style.display = 'none';
    uploadPlaceholder.style.display = 'block';
});

/* Drag & drop */
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
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
    }
});

/* ════════════════════════════════════════════════════════════════
   PREVIEW EN TEMPS RÉEL (sidebar)
════════════════════════════════════════════════════════════════ */
document.getElementById('name').addEventListener('input', e => {
    const v = e.target.value || 'Nom de votre boutique';
    document.getElementById('previewName').textContent = v;
    document.getElementById('recapName').textContent   = v;
});

document.getElementById('type').addEventListener('change', e => {
    const v = e.target.value;
    const el = document.getElementById('previewType');
    el.textContent   = v;
    el.style.display = v ? 'inline' : 'none';
    document.getElementById('recapType').textContent = v || '—';
});

document.getElementById('email').addEventListener('input', e => {
    document.getElementById('recapEmail').textContent = e.target.value || '—';
});

document.getElementById('phone').addEventListener('input', e => {
    document.getElementById('recapPhone').textContent = e.target.value || '—';
});

/* ════════════════════════════════════════════════════════════════
   RÉCAPITULATIF étape 4
════════════════════════════════════════════════════════════════ */
function updateRecap() {
    document.getElementById('recapName').textContent       = document.getElementById('name').value || '—';
    document.getElementById('recapType').textContent       = document.getElementById('type').value || '—';
    document.getElementById('recapEmail').textContent      = document.getElementById('email').value || '—';
    document.getElementById('recapPhone').textContent      = document.getElementById('phone').value || '—';
    document.getElementById('recapCurrency').textContent   = document.getElementById('currency_hidden').value || 'GNF';
    document.getElementById('recapCommission').textContent = parseFloat(document.getElementById('commission_rate').value).toFixed(1) + '%';
}

/* ════════════════════════════════════════════════════════════════
   SUBMIT — loader sur le bouton
════════════════════════════════════════════════════════════════ */
document.getElementById('shopForm').addEventListener('submit', () => {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Création en cours…';
});
</script>
@endpush