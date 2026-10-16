{{--
    resources/views/boutique/shop/edit.blade.php
    Route : PUT /shop/{shop} → ShopController@update → name('shop.update')
    Variables : $shop → App\Models\Shop
--}}

@extends('layouts.app')

@section('title', 'Modifier la boutique · ' . $shop->name)

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=block" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --brand:     #1668D8;
    --brand-dk:  #1260C9;
    --brand-lt:  #D6E6FF;
    --brand-mlt: #EAF2FF;
    --bg:        #f8fafc;
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

/* ══ MODE SOMBRE ══ */
[data-theme="dark"] {
    --brand-lt: rgba(22,104,216,.16);
    --brand-mlt: rgba(22,104,216,.09);
    --bg: #0b0f1a;
    --surface: #141925;
    --border: #262d3d;
    --border-dk: #343d52;
    --text: #e6e9f0;
    --text-2: #b0b8c9;
    --muted: #7b869c;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.35), 0 1px 2px rgba(0,0,0,.25);
    --shadow: 0 4px 20px rgba(0,0,0,.4), 0 1px 4px rgba(0,0,0,.25);
    --shadow-lg: 0 10px 40px rgba(0,0,0,.5);
}
[data-theme="dark"] input, [data-theme="dark"] select, [data-theme="dark"] textarea {
    background: var(--surface, var(--card)); color: var(--text); border-color: var(--border-dk, var(--border, var(--bd)));
}
[data-theme="dark"] input::placeholder, [data-theme="dark"] textarea::placeholder { color: var(--muted); }
[data-theme="dark"] #notifSettingsCard { background: var(--surface) !important; border-color: var(--border) !important; }


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
    background: radial-gradient(120% 60% at 100% 0%, rgba(31,124,246,.28), transparent 60%), #0B1E3D; /* bleu nuit Shopio */
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
    background: linear-gradient(135deg, var(--brand), #1260C9);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
    box-shadow: 0 2px 10px rgba(22,104,216,.4);
}

.sidebar-title {
    font-size: 28px; font-weight: 800;
    color: #fff; letter-spacing: -.6px;
    line-height: 1.2; margin-bottom: 12px;
}
.sidebar-title span {
    background: linear-gradient(135deg, #7DB4FF, #1668D8);
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
    background: linear-gradient(135deg, rgba(22,104,216,.15), rgba(79,70,229,.08));
    display: flex; align-items: center; justify-content: center;
    position: relative; overflow: hidden;
}
.preview-img-wrap img {
    width: 100%; height: 100%; object-fit: cover;
}
.preview-img-wrap .preview-placeholder {
    font-size: 42px; opacity: .35;
    position: absolute;
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
    background: rgba(22,104,216,.2); color: #7DB4FF;
    border: 1px solid rgba(22,104,216,.3);
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
    color: #fff; box-shadow: 0 0 12px rgba(22,104,216,.4);
}
.step-row.done .step-dot {
    background: rgba(22,104,216,.2); border-color: rgba(22,104,216,.4);
    color: #7DB4FF;
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
    box-shadow: 0 0 0 3px rgba(22,104,216,.12);
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
    box-shadow: 0 0 0 3px rgba(22,104,216,.12);
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

/* Image actuelle (spécifique à l'édition) */
.current-img-box {
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    overflow: hidden;
    margin-bottom: 14px;
    position: relative;
}
.current-img-box img {
    width: 100%; max-height: 140px; object-fit: cover; display: block;
}
.current-img-label {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: rgba(0,0,0,.5);
    font-size: 10.5px; font-weight: 600; color: rgba(255,255,255,.8);
    padding: 5px 10px; text-align: center;
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
    box-shadow: 0 4px 14px rgba(22,104,216,.3);
}
.btn-next:hover { background: var(--brand-dk); box-shadow: 0 6px 20px rgba(22,104,216,.4); transform: translateY(-1px); }
.btn-next:disabled { opacity: .6; cursor: not-allowed; transform: none; }

.btn-submit {
    padding: 13px 32px; border-radius: var(--r-sm);
    font-size: 14px; font-weight: 700; font-family: var(--font);
    background: var(--brand); color: #fff;
    border: 1.5px solid var(--brand-dk);
    cursor: pointer; transition: all .15s;
    display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 4px 14px rgba(22,104,216,.3);
}
.btn-submit:hover { background: var(--brand-dk); transform: translateY(-1px); }

/* ── Flash messages ── */
.flash { padding: 11px 14px; border-radius: var(--r-sm); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 20px; display: flex; gap: 8px; align-items: flex-start; }
.flash-danger  { background: var(--danger-lt); border-color: #fca5a5; color: #991b1b; }
.flash-success { background: #EAF2FF; border-color: #7DB4FF; color: #0B1E3D; }
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

/* ── Sélecteur type boutique (pills) ── */
.type-pills {
    display: flex; flex-wrap: wrap; gap: 7px; margin-top: 4px;
}
.type-pill {
    padding: 7px 14px; border-radius: 50px;
    border: 1.5px solid var(--border);
    background: var(--surface);
    font-size: 12.5px; font-weight: 600;
    color: var(--text-2); cursor: pointer;
    transition: all .15s; font-family: var(--font);
    display: inline-flex; align-items: center; gap: 5px;
    white-space: nowrap;
}
.type-pill:hover { border-color: var(--brand); color: var(--brand-dk); background: var(--brand-mlt); }
.type-pill.selected {
    border-color: var(--brand-dk); background: var(--brand);
    color: #fff; box-shadow: 0 2px 8px rgba(22,104,216,.3);
}
.type-pill-add {
    border-style: dashed; border-color: var(--brand);
    color: var(--brand); background: transparent; font-weight: 700;
}
.type-pill-add:hover { background: var(--brand-mlt); }
.type-custom-wrap {
    display: flex; align-items: center; gap: 8px; margin-top: 10px;
}
.type-custom-wrap .field-input { flex: 1; margin: 0; }
.type-custom-ok {
    padding: 9px 14px; border-radius: var(--r-sm);
    background: var(--brand); color: #fff;
    border: none; font-weight: 700; cursor: pointer;
    font-family: var(--font); font-size: 13px;
    transition: background .15s; flex-shrink: 0;
}
.type-custom-ok:hover { background: var(--brand-dk); }
.type-custom-cancel {
    padding: 9px 12px; border-radius: var(--r-sm);
    background: var(--bg); color: var(--muted);
    border: 1.5px solid var(--border); font-size: 13px;
    cursor: pointer; transition: all .15s; flex-shrink: 0;
}
.type-custom-cancel:hover { border-color: var(--danger); color: var(--danger); }

/* ── Bandeau étapes (mobile uniquement) ── */
.mobile-steps-bar {
    display: none;
    align-items: center; gap: 10px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 12px 16px;
    margin-bottom: 18px; box-shadow: var(--shadow-sm);
}
.mobile-step-indicator {
    background: var(--brand); color: #fff;
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800; flex-shrink: 0;
}
.mobile-step-txt { font-size: 13px; font-weight: 700; color: var(--text); flex: 1; }
.mobile-step-sub { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .form-page { grid-template-columns: 1fr; }
    .form-sidebar { display: none; }
    .form-main { padding: 28px 24px; }
    .mobile-steps-bar { display: flex; }

    /* Anti-zoom iOS — font-size < 16px déclenche le zoom automatique */
    .field-input,
    textarea.field-input,
    select.field-input,
    .currency-search-box,
    .currency-search-input,
    #typeCustomInput { font-size: 16px !important; }
}
@media (max-width: 640px) {
    .form-main { padding: 20px 16px; }
    .step-tab { font-size: 11px; padding: 10px 4px; gap: 3px; }
    .step-tab .tab-num { width: 16px; height: 16px; font-size: 9px; }
    .section-hd h2 { font-size: 16px; }
    .form-nav { flex-direction: column-reverse; gap: 10px; }
    .form-nav .btn-back,
    .form-nav .btn-next,
    .form-nav .btn-submit { width: 100%; justify-content: center; text-align: center; }
    .type-pills { gap: 6px; }
    .type-pill { padding: 6px 11px; font-size: 12px; }
}
@media (max-width: 480px) {
    .form-main { padding: 16px 12px; }
    .field-row { grid-template-columns: 1fr; }
    .form-steps-tabs { border-radius: var(--r-sm); }
    .step-tab span:not(.tab-num) { display: none; }
    .step-tab { padding: 11px 6px; }
    .type-custom-wrap { flex-direction: column; align-items: stretch; }
    .type-custom-ok, .type-custom-cancel { width: 100%; }
}
@media (max-width: 360px) {
    .form-main { padding: 12px 10px; }
    .section-hd h2 { font-size: 15px; }
    .btn-next, .btn-submit { padding: 11px 16px; font-size: 13px; }
}

/* ══════════════════════════════════════════════════════════════════
   « MA BOUTIQUE » — maquette Main.dc.html (écran Réglages)
   Menu vendeur à gauche, colonne bleu nuit (aperçu + étapes), formulaire à droite.
══════════════════════════════════════════════════════════════════ */
:root { --se-r: 24px; --se-e-out: cubic-bezier(.22,1,.36,1); --se-e-io: cubic-bezier(.65,0,.35,1); --se-e-pop: cubic-bezier(.34,1.56,.64,1); }
.se-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.se-top { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; position: sticky; top: 0; z-index: 30; padding: 14px 28px !important; }
.se-top .tb-info { flex: 1 1 240px; min-width: 0; }
.se-title { display: flex; align-items: center; gap: 10px; margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
.se-title-ico { color: var(--sv-blue, #1F7CF6); display: flex; }
.se-top-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.se-content { padding: 24px 28px 48px; }

/* Boutons en pilule */
.se-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 999px; border: 0; font: 700 14px/1 var(--font); text-decoration: none; cursor: pointer; white-space: nowrap; transition: transform 180ms var(--se-e-out), background-color 180ms var(--se-e-out), box-shadow 180ms var(--se-e-out), color 180ms var(--se-e-out); }
.se-btn:active:not(:disabled) { transform: scale(.97); }
.se-btn:disabled { opacity: .5; cursor: default; }
.se-btn-sm { min-height: 40px; padding: 0 14px; }
.se-btn-blue { background: #1668D8; color: #fff; }
.se-btn-blue:hover:not(:disabled) { background: #1260C9; color: #fff; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(22,104,216,.28); }
.se-btn-line { background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); }
.se-btn-line:hover:not(:disabled) { box-shadow: inset 0 0 0 1.5px var(--sv-blue, #1F7CF6); color: #1668D8; }
.se-btn:focus-visible, .step-row:focus-visible, .type-pill:focus-visible, .se-swatch input:focus-visible + .se-swatch-dot { outline: 3px solid #1F7CF6; outline-offset: 3px; }

.se-flash { border-radius: 18px; padding: 14px 18px; margin-bottom: 18px; font-size: 14px; }
.se-flash ul { margin: 6px 0 0; padding-left: 18px; }
.se-flash-ok { background: var(--sv-ok50, #E7F6EC); color: var(--sv-ok, #15803D); font-weight: 700; }
.se-flash-err { background: var(--sv-err50, #FDECEC); color: var(--sv-err, #B91C1C); }
.se-flash-info { background: var(--sv-b50, #EAF2FF); color: var(--text); }

/* Grille principale */
.se-grid { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr); gap: 20px; align-items: start; }
.se-hero { position: sticky; top: 96px; display: flex; flex-direction: column; gap: 18px; padding: 24px; border-radius: var(--se-r); background: #0B1E3D; color: #fff; overflow: hidden; }
.se-hero-title { margin: 0; font-size: 30px; line-height: 1.1; font-weight: 800; letter-spacing: -.02em; color: #fff; text-wrap: balance; }
.se-hero-title span { color: #7DB4FF; }
.se-hero-sub { margin: 8px 0 0; color: #C9D6EA; font-size: 15px; }

/* Aperçu de la boutique */
.se-preview { border-radius: 20px; overflow: hidden; background: #132B52; transition: transform 260ms var(--se-e-out), box-shadow 260ms var(--se-e-out); }
.se-preview:hover { transform: perspective(900px) rotateX(5deg) rotateY(-6deg) translateY(-3px); box-shadow: 12px 20px 36px rgba(0,0,0,.3); }
.se-preview-banner { position: relative; height: 150px; background: radial-gradient(120% 120% at 80% 0%, #1F7CF6 0%, #132B52 70%); }
.se-preview-banner img { width: 100%; height: 100%; object-fit: cover; display: block; }
.se-preview-ph { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #7DB4FF; }
.se-preview-ph svg { width: 44px; height: 44px; }
.se-preview-stripe { position: absolute; left: 0; right: 0; bottom: 0; height: 6px; transition: background-color 300ms var(--se-e-out); }
.se-preview-body { display: flex; align-items: center; gap: 12px; padding: 14px 16px; }
.se-preview-logo { width: 52px; height: 52px; border-radius: 14px; margin-top: -38px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 17px; box-shadow: 0 0 0 4px #132B52, inset -5px -6px 10px rgba(0,0,0,.18), inset 4px 4px 8px rgba(255,255,255,.25); transition: background-color 300ms var(--se-e-out), color 300ms; }
.se-preview-name { font-size: 17px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.se-preview-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; font-size: 13px; color: #C9D6EA; }
.se-preview-badge { display: inline-flex; align-items: center; height: 22px; padding: 0 9px; border-radius: 999px; font-size: 12px; font-weight: 800; transition: background-color 300ms var(--se-e-out), color 300ms; }

/* Étapes (colonne bleu nuit) */
.steps-list { display: flex; flex-direction: column; gap: 4px; }
.step-row { display: flex; align-items: center; gap: 12px; width: 100%; min-height: 48px; padding: 0 12px; border: 0; border-radius: 14px; background: transparent; color: #9FB3D3; font: 700 15px var(--font); text-align: left; cursor: pointer; opacity: 1; transition: background-color 180ms var(--se-e-out), color 180ms; }
.step-row:hover { background: rgba(19,43,82,.6); color: #fff; }
.step-row.active { background: #132B52; color: #fff; }
.step-dot { width: 30px; height: 30px; border-radius: 999px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; box-shadow: inset 0 0 0 2px #2B4A7E; background: transparent; color: inherit; flex-shrink: 0; transition: background-color 300ms var(--se-e-out), transform 300ms var(--se-e-pop); }
.step-row.active .step-dot { background: #1F7CF6; box-shadow: none; color: #fff; transform: scale(1.06); }
.step-row.done .step-dot { background: #1B3563; box-shadow: none; color: #7DB4FF; }
.step-txt { color: inherit; font-size: 15px; font-weight: 700; }

/* Colonne formulaire */
.se-col { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
.se-card { background: var(--surface); border-radius: var(--se-r); box-shadow: 0 0 0 1px var(--border); }
.se-notif { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; padding: 16px 18px; }
.se-notif strong { color: var(--text); }
.se-notif-sub { font-size: 13px; color: var(--text-2); margin-top: 2px; }
.se-tile { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: inset -5px -6px 10px rgba(0,0,0,.18), inset 4px 4px 8px rgba(255,255,255,.3), 0 8px 16px rgba(11,30,61,.12); }
.se-tile-amber { background: #F59E0B; color: #16162A; }
.se-form-card { padding: 22px; display: flex; flex-direction: column; gap: 10px; }
.progress-bar-wrap { height: 6px; border-radius: 6px; background: var(--sv-b50, #EAF2FF); overflow: hidden; }
.progress-bar-fill { height: 100%; width: 100% !important; background: #1F7CF6; border-radius: 6px; transform-origin: left center; transform: scaleX(var(--p, .25)); transition: transform 500ms var(--se-e-io); }
.se-step-count { font-size: 13px; font-weight: 700; color: var(--muted); }

.form-section { display: none; }
.form-section.active { display: block; }
.form-section.is-in { animation: seTurn 450ms var(--se-e-out) backwards; transform-origin: left center; }
@keyframes seTurn { from { opacity: 0; transform: perspective(900px) rotateY(-12deg) translateX(24px); } }
.se-sec-title { margin: 6px 0 18px; font-size: 20px; font-weight: 800; letter-spacing: -.02em; color: var(--text); outline: none; }
.se-sub-title { margin: 8px 0 12px; font-size: 16px; font-weight: 800; color: var(--text); }

/* Champs */
.field-group { margin-bottom: 16px; }
.field-row { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 14px; }
.field-label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 700; color: var(--text); text-transform: none; letter-spacing: 0; }
.field-label span { color: var(--sv-err, #B91C1C); }
.field-input { width: 100%; min-height: 46px; padding: 0 14px; border: 0; border-radius: 14px; background: var(--surface); color: var(--text); font: 500 15px var(--font); box-shadow: inset 0 0 0 1.5px var(--border); outline: 0; transition: box-shadow 180ms var(--se-e-out); }
textarea.field-input { min-height: 104px; padding: 12px 14px; resize: vertical; line-height: 1.5; }
select.field-input { appearance: auto; }
.field-input:focus { box-shadow: inset 0 0 0 2px #1F7CF6 !important; border-color: transparent !important; }
.field-input.error { box-shadow: inset 0 0 0 2px var(--sv-err, #B91C1C); }
.field-hint { font-size: 13px; color: var(--muted); margin-top: 6px; }
.field-error { font-size: 13px; font-weight: 600; color: var(--sv-err, #B91C1C); margin-top: 6px; }
.se-fieldset { border: 0; padding: 0; margin: 0 0 16px; min-width: 0; }

/* Type de boutique : pastilles (chips) */
.type-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
.type-pill { display: inline-flex; align-items: center; gap: 6px; min-height: 38px; padding: 0 14px; border-radius: 999px; border: 0; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); color: var(--text-2); font: 700 13px var(--font); cursor: pointer; transition: background-color 180ms var(--se-e-out), color 180ms, box-shadow 180ms; }
.type-pill:hover { box-shadow: inset 0 0 0 1.5px #1F7CF6; }
.type-pill.selected { background: #1668D8; color: #fff; box-shadow: none; }
.type-pill-add { box-shadow: inset 0 0 0 1.5px #7DB4FF; color: #1668D8; border-style: none; }
.type-custom-wrap { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
.type-custom-wrap .field-input { flex: 1 1 200px; }

/* Couleur d'accent : pastilles en relief */
.se-swatches { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 4px; }
.se-swatch { position: relative; cursor: pointer; }
.se-swatch input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.se-swatch-dot { display: block; width: 48px; height: 48px; border-radius: 16px; background: var(--sw); box-shadow: inset -5px -6px 10px rgba(0,0,0,.22), inset 4px 4px 8px rgba(255,255,255,.25); transition: transform 220ms var(--se-e-pop); }
.se-swatch:hover .se-swatch-dot { transform: perspective(400px) rotateX(12deg) rotateY(-12deg) scale(1.06); }
.se-swatch input:checked + .se-swatch-dot { outline: 3px solid var(--text); outline-offset: 3px; }

/* Devise : menu déroulant */
.currency-wrap { position: relative; }
.currency-search-box { cursor: pointer; }
.currency-dropdown { display: none; position: absolute; left: 0; right: 0; top: calc(100% + 6px); z-index: 20; background: var(--surface); border-radius: 16px; box-shadow: 0 0 0 1px var(--border), 0 16px 32px rgba(11,30,61,.14); padding: 8px; }
.currency-dropdown.open { display: block; }
.currency-search-input { width: 100%; min-height: 42px; border: 0; border-radius: 12px; padding: 0 12px; background: var(--bg); color: var(--text); font: 500 15px var(--font); margin-bottom: 6px; outline: 0; }
.currency-list { max-height: 220px; overflow-y: auto; }
.currency-opt { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 10px; cursor: pointer; font-size: 14px; }
.currency-opt:hover, .currency-opt.selected { background: var(--sv-b50, #EAF2FF); }
.currency-code { font-weight: 800; color: var(--text); }
.currency-name { flex: 1; min-width: 0; color: var(--text-2); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.currency-country { font-size: 12px; color: var(--muted); }

/* Images */
.se-asset { display: flex; align-items: center; gap: 14px; padding: 14px; border-radius: 18px; background: var(--bg); margin-bottom: 16px; }
.se-asset img { width: 56px; height: 56px; border-radius: 14px; object-fit: cover; flex-shrink: 0; }
.se-asset strong { color: var(--text); }
.upload-zone { position: relative; border-radius: 18px; padding: 26px 18px; text-align: center; background: var(--bg); box-shadow: inset 0 0 0 2px var(--border); cursor: pointer; transition: box-shadow 180ms var(--se-e-out), background-color 180ms; }
.upload-zone:hover, .upload-zone.dragover { box-shadow: inset 0 0 0 2px #1F7CF6; background: var(--sv-b50, #EAF2FF); }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.upload-txt { font-size: 15px; font-weight: 700; color: var(--text); }
.upload-hint { font-size: 13px; color: var(--muted); margin-top: 4px; }
.upload-preview { display: none; position: relative; }
.upload-preview img { max-height: 180px; max-width: 100%; border-radius: 14px; }
.upload-remove { position: absolute; top: 6px; right: 6px; width: 36px; height: 36px; border-radius: 999px; border: 0; background: rgba(11,30,61,.75); color: #fff; font-size: 20px; cursor: pointer; }

/* Récapitulatif */
.se-recap { border-radius: 18px; background: var(--bg); padding: 16px 18px; }
.se-recap-title { font-size: 12px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; }
.se-recap dl { margin: 0; display: grid; gap: 8px; }
.se-recap dl > div { display: flex; justify-content: space-between; gap: 12px; font-size: 14px; }
.se-recap dt { color: var(--text-2); }
.se-recap dd { margin: 0; font-weight: 700; color: var(--text); text-align: right; overflow-wrap: anywhere; }
.se-recap-dot { display: inline-block; width: 12px; height: 12px; border-radius: 999px; vertical-align: -1px; }

/* Navigation des étapes */
.se-nav { display: flex !important; flex-direction: row !important; justify-content: space-between; gap: 10px; padding-top: 14px; margin-top: 6px; border-top: 1px solid var(--border); }

/* Mode sombre */
[data-theme="dark"] .se-hero { background: #071428; }
[data-theme="dark"] .se-swatch input:checked + .se-swatch-dot { outline-color: #EAF0FA; }

/* Responsive */
@media (max-width: 1100px) {
    .se-grid { grid-template-columns: minmax(0, 1fr); }
    .se-hero { position: static; }
    .steps-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .se-top { padding: 10px 16px !important; }
    .se-title { font-size: 19px; }
    .se-hide-m { display: none; }
    .se-content { padding: 16px; }
    .se-hero { padding: 18px; }
    .se-hero-title { font-size: 24px; }
    .steps-list { grid-template-columns: minmax(0, 1fr); }
    .se-form-card { padding: 18px; }
    .field-row { grid-template-columns: minmax(0, 1fr); gap: 0; }
    .se-nav .se-btn { flex: 1; }
}
@media (prefers-reduced-motion: reduce) {
    .form-section.is-in { animation: none; }
    .se-preview:hover, .se-swatch:hover .se-swatch-dot { transform: none; }
    .progress-bar-fill, .step-dot { transition: none; }
}
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
    $currentAccent = old('accent_color', $shop->accent_color ?: '#1F7CF6');
    if (!isset($accentChoices[$currentAccent])) $currentAccent = '#1F7CF6';
    $shopIni = collect(preg_split('/\s+/', trim($shop->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $stepLabels = ['Informations générales', 'Contact et localisation', 'Concevoir', 'Images et finalisation'];
    $svgStore = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9l1.5-5h13L20 9M4 9v11h16V9M4 9c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3M10 20v-5h4v5"/></svg>';
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
                <h1 class="se-title"><span class="se-title-ico">{!! $svgStore !!}</span> Ma boutique</h1>
                <div class="tb-sub">{{ $shop->name }} · informations et apparence</div>
            </div>
            <div class="se-top-actions">
                <a href="{{ route('public.shops.products', $shop) }}" class="se-btn se-btn-line" target="_blank" rel="noopener">Voir ma boutique</a>
                <a href="{{ route('boutique.dashboard') }}" class="se-btn se-btn-line se-hide-m">Tableau de bord</a>
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
            @if(session('info'))<div class="se-flash se-flash-info" role="status">{{ session('info') }}</div>@endif

            <div class="se-grid">
                {{-- ══ Colonne bleu nuit : titre, aperçu, étapes ══ --}}
                <section class="se-hero" aria-label="Aperçu de la boutique">
                    <div>
                        <h2 class="se-hero-title">Modifier votre <span>boutique</span></h2>
                        <p class="se-hero-sub">Les modifications sont visibles par vos clients dès l'enregistrement.</p>
                    </div>

                    <div class="se-preview">
                        <div class="se-preview-banner">
                            <img id="sidebarPreviewImg"
                                 src="{{ $shop->image ? (\App\Services\ImageOptimizer::url($shop->image, 'medium') ?? asset('storage/' . $shop->image)) : '' }}"
                                 alt="" @unless($shop->image) style="display:none" @endunless>
                            <span class="se-preview-ph" id="previewPlaceholder" @if($shop->image) style="display:none" @endif aria-hidden="true">{!! $svgStore !!}</span>
                            <span class="se-preview-stripe" id="previewStripe" style="background:{{ $currentAccent }}"></span>
                        </div>
                        <div class="se-preview-body">
                            <span class="se-preview-logo" id="previewLogo" style="background:{{ $currentAccent }};color:{{ $accentChoices[$currentAccent][1] }}">{{ $shopIni }}</span>
                            <div style="min-width:0">
                                <div class="se-preview-name" id="previewName">{{ $shop->name }}</div>
                                <div class="se-preview-meta">
                                    <span id="previewType" @unless($shop->type) style="display:none" @endunless>{{ $shop->type }}</span>
                                    <span class="se-preview-badge" id="previewBadge" style="background:{{ $currentAccent }};color:{{ $accentChoices[$currentAccent][1] }}">{{ $shop->is_approved ? 'Vérifiée' : 'En validation' }}</span>
                                    <span id="previewCurrency">{{ $shop->currency ?? 'GNF' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <nav class="steps-list" aria-label="Étapes">
                        @foreach($stepLabels as $i => $label)
                        <button type="button" class="step-row {{ $i === 0 ? 'active' : '' }}" data-step="{{ $i + 1 }}" @if($i === 0) aria-current="step" @endif>
                            <span class="step-dot">{{ $i + 1 }}</span>
                            <span class="step-txt">{{ $label }}</span>
                        </button>
                        @endforeach
                    </nav>
                </section>

                {{-- ══ Colonne formulaire ══ --}}
                <section class="se-col">
                    {{-- Notifications push — activation manuelle si la bannière automatique n'est pas apparue --}}
                    <div id="notifSettingsCard" class="se-card se-notif">
                        <span class="se-tile se-tile-amber" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15zM10 20a2 2 0 0 0 4 0"/></svg>
                        </span>
                        <div style="flex:1 1 180px;min-width:0">
                            <strong>Notifications push</strong>
                            <div id="notifSettingsSub" class="se-notif-sub">Recevez vos commandes et messages en temps réel, même application fermée.</div>
                        </div>
                        <button type="button" id="notifSettingsBtn" class="se-btn se-btn-blue">Activer</button>
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
                                btn.classList.replace('se-btn-blue', 'se-btn-line');
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

                    <div class="se-card se-form-card">
                        <div class="progress-bar-wrap" aria-hidden="true"><div class="progress-bar-fill" id="progressBar"></div></div>
                        <div class="se-step-count" id="mobileStepSub" aria-live="polite">Étape 1 sur 4</div>

                        {{-- Formulaire unique — route PUT --}}
                        <form action="{{ route('shop.update', $shop->id) }}" method="POST" enctype="multipart/form-data" id="shopForm">
                            @csrf
                            @method('PUT')

                            {{-- ══ ÉTAPE 1 : Informations générales ══ --}}
                            <div class="form-section active" id="section-1">
                                <h2 class="se-sec-title" tabindex="-1">Informations générales</h2>

                                <div class="field-group">
                                    <label class="field-label" for="name">Nom de la boutique <span>*</span></label>
                                    <input type="text" name="name" id="name" class="field-input {{ $errors->has('name') ? 'error' : '' }}"
                                           value="{{ old('name', $shop->name) }}" placeholder="Ex. Boutique Aminata" required maxlength="255">
                                    @error('name')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
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
                                        <button type="button" class="se-btn se-btn-blue se-btn-sm" onclick="confirmCustomType()">Valider</button>
                                        <button type="button" class="se-btn se-btn-line se-btn-sm" onclick="hideCustomType()">Annuler</button>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label class="field-label" for="email">E-mail de la boutique <span>*</span></label>
                                    <input type="email" name="email" id="email" class="field-input {{ $errors->has('email') ? 'error' : '' }}"
                                           value="{{ old('email', $shop->email) }}" placeholder="boutique@email.com" required>
                                    @error('email')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- ══ ÉTAPE 2 : Contact et localisation ══ --}}
                            <div class="form-section" id="section-2">
                                <h2 class="se-sec-title" tabindex="-1">Contact et localisation</h2>

                                <div class="field-group">
                                    <label class="field-label" for="phone">Téléphone de la boutique</label>
                                    <input type="tel" name="phone" id="phone" class="field-input {{ $errors->has('phone') ? 'error' : '' }}"
                                           value="{{ old('phone', $shop->phone) }}" placeholder="+224 6XX XX XX XX" maxlength="20" inputmode="tel">
                                    @error('phone')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
                                </div>

                                <div class="field-group">
                                    <label class="field-label" for="address">Adresse et point de repère</label>
                                    <input type="text" name="address" id="address" class="field-input {{ $errors->has('address') ? 'error' : '' }}"
                                           value="{{ old('address', $shop->address) }}" placeholder="Ex. Kaloum, en face de la Banque centrale" maxlength="255">
                                    @error('address')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
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
                                $flatCountries = [];
                                foreach ($shopCountries as $grp) { $flatCountries += $grp; }
                                $currentCountry = old('country', $shop->country ?? '');
                                @endphp
                                <div class="field-row">
                                    <div class="field-group">
                                        <label class="field-label" for="country">Pays de la boutique</label>
                                        <select name="country" id="country" class="field-input" onchange="onShopCountryChange(this)">
                                            <option value="">Sélectionner un pays…</option>
                                            @foreach($shopCountries as $region => $pays)
                                                <optgroup label="{{ $region }}">
                                                    @foreach($pays as $code => [$flag, $cname])
                                                    <option value="{{ $code }}" {{ $currentCountry === $code ? 'selected' : '' }}>{{ $flag }} {{ $cname }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        <span id="shopCountryFlag" hidden></span>
                                        <div class="field-hint">Les clients de ce pays voient votre boutique dans leur espace.</div>
                                        @error('country')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
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
                                        @error('payout_wallet_type')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="field-group">
                                        <label class="field-label" for="payout_wallet_number">Numéro Mobile Money</label>
                                        <input type="text" name="payout_wallet_number" id="payout_wallet_number" class="field-input" inputmode="tel"
                                               placeholder="Ex. 622 00 00 00" value="{{ old('payout_wallet_number', $shop->payout_wallet_number ?? '') }}">
                                        @error('payout_wallet_number')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="field-hint" style="margin-top:-6px">C'est sur ce numéro que Shopio vous verse l'argent des ventes payées en ligne.</div>
                            </div>

                            {{-- ══ ÉTAPE 3 : Concevoir ══ --}}
                            <div class="form-section" id="section-3">
                                <h2 class="se-sec-title" tabindex="-1">Concevoir</h2>

                                <fieldset class="field-group se-fieldset">
                                    <legend class="field-label">Couleur d'accent</legend>
                                    <div class="se-swatches">
                                        @foreach($accentChoices as $hex => [$aLabel, $aInk])
                                        <label class="se-swatch" style="--sw:{{ $hex }}">
                                            <input type="radio" name="accent_color" value="{{ $hex }}" data-ink="{{ $aInk }}" {{ $currentAccent === $hex ? 'checked' : '' }}>
                                            <span class="se-swatch-dot" aria-hidden="true"></span>
                                            <span class="se-sr">{{ $aLabel }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                    <div class="field-hint">Utilisée pour le liseré du bandeau de votre boutique, visible par vos clients.</div>
                                    @error('accent_color')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
                                </fieldset>

                                <div class="field-group">
                                    <label class="field-label" for="description">Description courte</label>
                                    <textarea name="description" id="description" class="field-input" rows="4" maxlength="500"
                                              placeholder="Ex. Montres et parfums authentiques, livrés partout à Conakry.">{{ old('description', $shop->description) }}</textarea>
                                    <div class="field-hint">500 caractères maximum. Dites ce que vous vendez et où vous livrez.</div>
                                    @error('description')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
                                </div>

                                {{-- Pixel Meta (Facebook) : mesure des pubs du vendeur sur ses propres pages --}}
                                <div class="field-group">
                                    <label class="field-label" for="meta_pixel_id">Pixel Meta (Facebook) <span style="color:var(--muted);font-weight:500">· facultatif</span></label>
                                    <input type="text" name="meta_pixel_id" id="meta_pixel_id" class="field-input {{ $errors->has('meta_pixel_id') ? 'error' : '' }}"
                                           value="{{ old('meta_pixel_id', $shop->meta_pixel_id) }}" inputmode="numeric" autocomplete="off" maxlength="30"
                                           placeholder="Ex. 123456789012345" aria-describedby="metaPixelHint">
                                    <div class="field-hint" id="metaPixelHint">
                                        L'identifiant à 15 ou 16 chiffres de votre pixel (Gestionnaire d'événements Meta → Sources de données).
                                        Il mesure les visites de votre vitrine, les produits vus, les ajouts au panier et les commandes,
                                        uniquement sur les pages de votre boutique. Laissez vide pour le désactiver.
                                    </div>
                                    @error('meta_pixel_id')<div class="field-error" style="display:block">{{ $message }}</div>@enderror
                                </div>

                                {{-- Réglages avancés de la page publique (sections, affichage, bannière) --}}
                                <div class="se-asset" style="flex-wrap:wrap">
                                    <div style="flex:1 1 200px;min-width:0">
                                        <strong>Vitrine de la boutique</strong>
                                        <div class="field-hint" style="margin:2px 0 0">Ordre des sections, affichage des produits, bannière, avec un aperçu en direct. Enregistrez d'abord cette page si vous l'avez modifiée.</div>
                                    </div>
                                    <a href="{{ route('boutique.storefront.edit') }}" class="se-btn se-btn-blue">Personnaliser la vitrine en détail</a>
                                </div>
                            </div>

                            {{-- ══ ÉTAPE 4 : Images et finalisation ══ --}}
                            <div class="form-section" id="section-4">
                                <h2 class="se-sec-title" tabindex="-1">Images et finalisation</h2>

                                @if($shop->image)
                                <div class="se-asset">
                                    <img src="{{ \App\Services\ImageOptimizer::url($shop->image, 'thumb') ?? asset('storage/' . $shop->image) }}" alt="Image actuelle de {{ $shop->name }}" width="56" height="56">
                                    <div style="flex:1;min-width:0"><strong>Image actuelle</strong><div class="field-hint" style="margin:0">Choisissez une nouvelle image ci-dessous pour la remplacer.</div></div>
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
                                    @error('image')<div class="field-error" style="display:block;margin-top:6px">{{ $message }}</div>@enderror
                                </div>

                                <div class="se-recap">
                                    <div class="se-recap-title">Récapitulatif</div>
                                    <dl>
                                        <div><dt>Nom</dt><dd id="recapName">{{ $shop->name }}</dd></div>
                                        <div><dt>Type</dt><dd id="recapType">{{ $shop->type ?? '—' }}</dd></div>
                                        <div><dt>E-mail</dt><dd id="recapEmail">{{ $shop->email }}</dd></div>
                                        <div><dt>Téléphone</dt><dd id="recapPhone">{{ $shop->phone ?? '—' }}</dd></div>
                                        <div><dt>Pays</dt><dd id="recapCountry">{{ $currentCountry && isset($flatCountries[$currentCountry]) ? $flatCountries[$currentCountry][0].' '.$flatCountries[$currentCountry][1] : '—' }}</dd></div>
                                        <div><dt>Devise</dt><dd id="recapCurrency">{{ $shop->currency ?? 'GNF' }}</dd></div>
                                        <div><dt>Couleur</dt><dd><span class="se-recap-dot" id="recapAccentDot" style="background:{{ $currentAccent }}"></span> <span id="recapAccent">{{ $accentChoices[$currentAccent][0] }}</span></dd></div>
                                    </dl>
                                </div>
                            </div>

                            <div class="form-nav se-nav">
                                <button type="button" class="se-btn se-btn-line" id="stepPrev">Précédent</button>
                                <button type="button" class="se-btn se-btn-blue" id="stepNext">Suivant</button>
                                <button type="submit" class="se-btn se-btn-blue" id="submitBtn" hidden>Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
/* ════════════════════════════════════════════════════════════════
   DONNÉES DEVISES — identique à la vue création
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

/* ════════════════════════════════════════════════════════════════
   GESTION DES ÉTAPES — identique à la création
════════════════════════════════════════════════════════════════ */
let currentStep = 1;
const totalSteps = 4;
const stepNames  = ['Informations générales', 'Contact et localisation', 'Concevoir', 'Images et finalisation'];
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function goToStep(step, opts = {}) {
    document.getElementById(`section-${currentStep}`).classList.remove('active', 'is-in');

    document.querySelectorAll('.step-row').forEach(r => {
        const n = parseInt(r.dataset.step);
        r.classList.toggle('active', n === step);
        r.classList.toggle('done', n < step);
        if (n === step) r.setAttribute('aria-current', 'step'); else r.removeAttribute('aria-current');
    });

    currentStep = step;
    const section = document.getElementById(`section-${step}`);
    section.classList.add('active');
    if (!reduceMotion && !opts.silent) { void section.offsetWidth; section.classList.add('is-in'); }
    document.getElementById('progressBar').style.setProperty('--p', step / totalSteps);

    const counter = document.getElementById('mobileStepSub');
    if (counter) counter.textContent = `Étape ${step} sur ${totalSteps} · ${stepNames[step - 1]}`;

    /* Boutons : Précédent désactivé à l'étape 1, « Enregistrer » à la dernière */
    document.getElementById('stepPrev').disabled = step === 1;
    document.getElementById('stepNext').hidden = step === totalSteps;
    document.getElementById('submitBtn').hidden = step !== totalSteps;

    if (step === 4) updateRecap();
    if (!opts.silent) {
        const card = document.querySelector('.se-form-card');
        if (card && card.getBoundingClientRect().top < 0) card.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
        section.querySelector('.se-sec-title')?.focus({ preventScroll: true });
    }
}

document.querySelectorAll('.step-row').forEach(r => r.addEventListener('click', () => goToStep(parseInt(r.dataset.step))));
document.getElementById('stepPrev').addEventListener('click', () => { if (currentStep > 1) goToStep(currentStep - 1); });
document.getElementById('stepNext').addEventListener('click', () => { if (currentStep < totalSteps) goToStep(currentStep + 1); });
goToStep(1, { silent: true });
/* Lien direct vers une étape (ex. ?etape=2 depuis l'indicateur de qualité de la vitrine) */
(function () {
    const s = parseInt(new URLSearchParams(location.search).get('etape'), 10);
    if (s >= 2 && s <= totalSteps) goToStep(s, { silent: true });
})();

/* ════════════════════════════════════════════════════════════════
   COULEUR D'ACCENT — aperçu en direct (liseré, logo, badge, récap)
════════════════════════════════════════════════════════════════ */
document.querySelectorAll('input[name="accent_color"]').forEach(r => r.addEventListener('change', () => {
    const c = r.value, ink = r.dataset.ink;
    document.getElementById('previewStripe').style.background = c;
    ['previewLogo', 'previewBadge'].forEach(id => { const el = document.getElementById(id); el.style.background = c; el.style.color = ink; });
    document.getElementById('recapAccentDot').style.background = c;
    document.getElementById('recapAccent').textContent = r.parentElement.querySelector('.se-sr').textContent;
}));

/* ════════════════════════════════════════════════════════════════
   DEVISE — identique à la création + pré-sélection boutique
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
        });
    });
}

function selectCurrency(code, name, flag) {
    document.getElementById('currency_hidden').value  = code;
    document.getElementById('currencyDisplay').value  = `${flag}  ${code} — ${name}`;
    document.getElementById('currencyDropdown').classList.remove('open');
    document.getElementById('previewCurrency').textContent = code;
    document.getElementById('recapCurrency').textContent   = code;
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

/* Init — pré-sélectionner la devise actuelle de la boutique */
renderCurrencies();
const shopCurrency = '{{ old('currency', $shop->currency ?? 'GNF') }}';
const found = CURRENCIES.find(c => c.code === shopCurrency) || CURRENCIES.find(c => c.code === 'GNF');
if (found) selectCurrency(found.code, found.name, found.flag);


/* ════════════════════════════════════════════════════════════════
   UPLOAD IMAGE — identique à la création
════════════════════════════════════════════════════════════════ */
const imageInput        = document.getElementById('imageInput');
const uploadPreview     = document.getElementById('uploadPreview');
const previewImg        = document.getElementById('previewImg');
const uploadPlaceholder = document.getElementById('uploadPlaceholder');
const removeImg         = document.getElementById('removeImg');
const uploadZone        = document.getElementById('uploadZone');

imageInput.addEventListener('change', e => {
    if (e.target.files[0]) showPreview(e.target.files[0]);
});

function showPreview(file) {
    if (file.size > 2 * 1024 * 1024) {
        alert('Image trop lourde (max 2 Mo).');
        imageInput.value = '';
        return;
    }
    const url = URL.createObjectURL(file);
    previewImg.src = url;
    uploadPreview.style.display = 'block';
    uploadPlaceholder.style.display = 'none';

    /* Mettre à jour la preview dans la sidebar */
    const sidebarImg = document.getElementById('sidebarPreviewImg');
    sidebarImg.src = url;
    sidebarImg.style.display = 'block';
    const placeholder = document.getElementById('previewPlaceholder');
    if (placeholder) placeholder.style.display = 'none';
}

removeImg.addEventListener('click', () => {
    imageInput.value = '';
    previewImg.src   = '';
    uploadPreview.style.display     = 'none';
    uploadPlaceholder.style.display = 'block';
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
    }
});

/* ════════════════════════════════════════════════════════════════
   PREVIEW EN TEMPS RÉEL — identique à la création
════════════════════════════════════════════════════════════════ */
document.getElementById('name').addEventListener('input', e => {
    const v = e.target.value || 'Nom de votre boutique';
    document.getElementById('previewName').textContent = v;
    document.getElementById('recapName').textContent   = e.target.value || '—';
});

/* ════════════════════════════════════════════════════════════════
   TYPE DE BOUTIQUE — sélecteur pills
════════════════════════════════════════════════════════════════ */
function selectType(value) {
    document.getElementById('type').value = value;
    document.querySelectorAll('#typePills .type-pill:not(.type-pill-add)').forEach(p => {
        p.classList.toggle('selected', p.dataset.val === value);
    });
    const el = document.getElementById('previewType');
    el.textContent   = value;
    el.style.display = value ? 'inline' : 'none';
    document.getElementById('recapType').textContent = value || '—';
}

function showCustomType() {
    document.getElementById('typeCustomWrap').style.display = 'flex';
    document.getElementById('typeCustomInput').focus();
}

function hideCustomType() {
    document.getElementById('typeCustomWrap').style.display = 'none';
    document.getElementById('typeCustomInput').value = '';
}

function confirmCustomType() {
    const val = document.getElementById('typeCustomInput').value.trim();
    if (!val) return;
    /* Remove any previous custom pill */
    const prev = document.querySelector('#typePills .type-pill[data-custom]');
    if (prev) prev.remove();
    /* Create a custom pill */
    const pill = document.createElement('button');
    pill.type = 'button';
    pill.className = 'type-pill selected';
    pill.dataset.val = val;
    pill.dataset.custom = '1';
    pill.textContent = '✏️ ' + val;
    pill.onclick = () => selectType(val);
    document.querySelector('.type-pill-add').insertAdjacentElement('beforebegin', pill);
    selectType(val);
    hideCustomType();
}

document.getElementById('typeCustomInput').addEventListener('keydown', e => {
    if (e.key === 'Enter')  { e.preventDefault(); confirmCustomType(); }
    if (e.key === 'Escape') hideCustomType();
});

/* Pré-sélectionner le type actuel de la boutique */
(function () {
    const currentType = document.getElementById('type').value;
    if (!currentType) return;
    const knownPill = document.querySelector(`#typePills .type-pill[data-val="${CSS.escape(currentType)}"]`);
    if (knownPill) {
        knownPill.classList.add('selected');
        /* Sync preview */
        const el = document.getElementById('previewType');
        if (el) { el.textContent = currentType; el.style.display = 'inline'; }
    } else {
        /* Type personnalisé — ajouter une pill custom */
        const pill = document.createElement('button');
        pill.type = 'button';
        pill.className = 'type-pill selected';
        pill.dataset.val = currentType;
        pill.dataset.custom = '1';
        pill.textContent = '✏️ ' + currentType;
        pill.onclick = () => selectType(currentType);
        document.querySelector('.type-pill-add').insertAdjacentElement('beforebegin', pill);
        const el = document.getElementById('previewType');
        if (el) { el.textContent = currentType; el.style.display = 'inline'; }
    }
})();

document.getElementById('email').addEventListener('input', e => {
    document.getElementById('recapEmail').textContent = e.target.value || '—';
});

document.getElementById('phone').addEventListener('input', e => {
    document.getElementById('recapPhone').textContent = e.target.value || '—';
});

/* ════════════════════════════════════════════════════════════════
   PAYS DE LA BOUTIQUE — mise à jour du drapeau + récap
════════════════════════════════════════════════════════════════ */
function onShopCountryChange(select) {
    const opt  = select.options[select.selectedIndex];
    const text = opt.text; // ex: "🇲🇦 Maroc"
    /* Met à jour l'icône dans le field-wrap */
    const flagSpan = document.getElementById('shopCountryFlag');
    if (flagSpan) {
        const parts = text.split(' ');
        flagSpan.textContent = parts[0] || '🌍';
    }
    /* Récap étape 4 */
    const recapEl = document.getElementById('recapCountry');
    if (recapEl) recapEl.textContent = select.value ? text : '—';
}

/* ════════════════════════════════════════════════════════════════
   RÉCAPITULATIF étape 4
════════════════════════════════════════════════════════════════ */
function updateRecap() {
    document.getElementById('recapName').textContent       = document.getElementById('name').value || '—';
    document.getElementById('recapType').textContent       = document.getElementById('type').value || '—';
    document.getElementById('recapEmail').textContent      = document.getElementById('email').value || '—';
    document.getElementById('recapPhone').textContent      = document.getElementById('phone').value || '—';
    document.getElementById('recapCurrency').textContent   = document.getElementById('currency_hidden').value || 'GNF';
    /* Pays */
    const countryEl = document.getElementById('country');
    if (countryEl && document.getElementById('recapCountry')) {
        const opt = countryEl.options[countryEl.selectedIndex];
        document.getElementById('recapCountry').textContent = countryEl.value ? opt.text : '—';
    }
}

/* ════════════════════════════════════════════════════════════════
   SUBMIT — loader sur le bouton
════════════════════════════════════════════════════════════════ */
document.getElementById('shopForm').addEventListener('submit', e => {
    /* Les champs obligatoires sont sur l'étape 1 : s'ils sont vides ou invalides alors que
       l'étape est masquée, le navigateur bloquerait l'envoi sans rien montrer → on y retourne. */
    const bad = ['name', 'email'].map(id => document.getElementById(id)).find(f => !f.checkValidity());
    if (bad) {
        e.preventDefault();
        goToStep(1);
        bad.reportValidity();
        return;
    }
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Enregistrement…';
});

/* Si Laravel renvoie des erreurs → aller directement à l'étape concernée */
@if($errors->has('name') || $errors->has('email') || $errors->has('type'))
    goToStep(1);
@elseif($errors->has('phone') || $errors->has('address') || $errors->has('country') || $errors->has('currency') || $errors->has('payout_wallet_type') || $errors->has('payout_wallet_number'))
    goToStep(2);
@elseif($errors->has('description') || $errors->has('accent_color') || $errors->has('meta_pixel_id'))
    goToStep(3);
@elseif($errors->has('image'))
    goToStep(4);
@endif
</script>
@endpush
{{-- Thème bleu « espace vendeur » (maquette Main.dc.html), chargé après les styles de la page --}}
@push('styles')
@include('boutique._partials.sv-theme')
@endpush
