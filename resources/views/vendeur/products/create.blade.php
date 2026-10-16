{{--
    resources/views/vendeur/products/create.blade.php
    (La vue edit.blade.php est identique — changer action et ajouter @method('PUT'))
    Variables : $categories, $devise
    En mode edit, ajouter : $product
--}}
@extends('layouts.app')
@section('title', isset($product) ? 'Modifier · ' . $product->name : 'Ajouter un produit')
@php $bodyClass = 'is-dashboard'; $isEdit = isset($product); @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=block" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --brand:     #1F7CF6; --brand-dk: #1260C9;
    --brand-lt:  #D6E6FF; --brand-mlt: #EAF2FF;
    --bg:        #f8fafc; --surface:  #ffffff;
    --border:    #e2e8f0; --border-dk: #cbd5e1;
    --text:      #0f172a; --text-2:   #475569; --muted: #94a3b8;
    --danger:    #ef4444; --danger-lt: #fef2f2;
    --font:      'Plus Jakarta Sans', sans-serif;
    --mono:      'JetBrains Mono', monospace;
    --r:         20px; --r-sm: 14px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
    --shadow:    0 8px 32px rgba(0,0,0,.08);
}

/* ══ MODE SOMBRE ══ */
[data-theme="dark"] {
    --brand-lt: rgba(31,124,246,.16);
    --brand-mlt: rgba(31,124,246,.09);
    --bg: #0b0f1a;
    --surface: #141925;
    --border: #262d3d;
    --border-dk: #343d52;
    --text: #e6e9f0;
    --text-2: #b0b8c9;
    --muted: #7b869c;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.35), 0 1px 2px rgba(0,0,0,.25);
    --shadow: 0 4px 20px rgba(0,0,0,.4), 0 1px 4px rgba(0,0,0,.25);
}
[data-theme="dark"] input, [data-theme="dark"] select, [data-theme="dark"] textarea {
    background: var(--surface, var(--card)); color: var(--text); border-color: var(--border-dk, var(--border, var(--bd)));
}
[data-theme="dark"] input::placeholder, [data-theme="dark"] textarea::placeholder { color: var(--muted); }

html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; overflow-x: hidden; }

/* ── Layout 2 colonnes ── */
.product-form-wrap {
    max-width: 1100px; margin: 0 auto;
    padding: 28px 20px 60px;
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    align-items: start;
    overflow-x: hidden;
}
.main-col { min-width: 0; }

/* ── Cards ── */
.form-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm); margin-bottom: 16px;
}
.form-card-hd {
    padding: 14px 20px; border-bottom: 1px solid var(--border);
    background: var(--bg); display: flex; align-items: center; gap: 10px;
}
.form-card-hd-ico {
    width: 28px; height: 28px; border-radius: 7px;
    background: var(--brand-mlt); border: 1px solid var(--brand-lt);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
}
.form-card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.form-card-sub   { font-size: 11.5px; color: var(--muted); margin-left: auto; }
.form-card-body  { padding: 20px; }

/* ── Champs ── */
.field { margin-bottom: 16px; }
.field:last-child { margin-bottom: 0; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.field-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }

.field-lbl {
    display: block; font-size: 11.5px; font-weight: 700;
    color: var(--text-2); text-transform: uppercase;
    letter-spacing: .4px; margin-bottom: 6px;
}
.field-lbl span { color: var(--danger); }
.field-hint { font-size: 11px; color: var(--muted); margin-top: 5px; }

.field-input, .field-select, .field-textarea {
    width: 100%; padding: 10px 13px;
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    font-size: 13.5px; font-family: var(--font);
    color: var(--text); background: var(--surface);
    outline: none; transition: border-color .15s, box-shadow .15s;
    appearance: none;
}
.field-input:focus, .field-select:focus, .field-textarea:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(31,124,246,.12);
}
.field-input.error  { border-color: var(--danger); }
.field-error { font-size: 11.5px; color: var(--danger); margin-top: 4px; }
.field-textarea { resize: vertical; min-height: 90px; }

/* Prix avec préfixe devise */
.price-wrap { position: relative; }
.price-wrap .price-suffix {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    font-size: 11px; font-weight: 700; color: var(--muted);
    font-family: var(--mono); pointer-events: none;
}
.price-wrap .field-input { padding-right: 52px; }

/* ── Upload image principale ── */
.upload-main {
    border: 2px dashed var(--border);
    border-radius: var(--r);
    overflow: hidden;
    position: relative;
    transition: border-color .2s, background .2s;
    background: var(--bg);
    aspect-ratio: 4/3;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    cursor: pointer;
}
.upload-main:hover, .upload-main.over { border-color: var(--brand); background: var(--brand-mlt); }

/* ── L'input file est caché — déclenché par JS onclick ── */
.upload-main-input { display: none; }

.upload-main-preview {
    width: 100%; height: 100%; object-fit: cover;
    position: absolute; inset: 0; display: none;
    border-radius: calc(var(--r) - 2px);
}
.upload-main-preview.visible { display: block; }
.upload-main-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.5); display: none;
    align-items: center; justify-content: center;
    font-size: 13px; font-weight: 600; color: #fff; gap: 6px;
    border-radius: calc(var(--r) - 2px);
    pointer-events: none; /* ne bloque plus les clics */
}
.upload-main:hover .upload-main-overlay { display: flex; }
.upload-placeholder { text-align: center; padding: 20px; }
.upload-placeholder-ico { font-size: 36px; opacity: .3; display: block; margin-bottom: 8px; }
.upload-placeholder-txt { font-size: 13px; font-weight: 600; color: var(--text-2); }
.upload-placeholder-hint { font-size: 11px; color: var(--muted); margin-top: 4px; }

/* Remove image button */
.img-remove {
    position: absolute; top: 8px; right: 8px; z-index: 5;
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(0,0,0,.65); color: #fff;
    border: none; cursor: pointer; font-size: 13px;
    display: none; align-items: center; justify-content: center;
}
.img-remove.visible { display: flex; }

/* ── Upload galerie ── */
.upload-gallery-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
}
.gallery-slot {
    aspect-ratio: 1; border-radius: var(--r-sm);
    border: 1.5px dashed var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; opacity: .4; cursor: pointer;
    overflow: hidden; position: relative;
    background: var(--bg);
    transition: border-color .15s, opacity .15s;
}
.gallery-slot:hover { border-color: var(--brand); opacity: .7; }
.gallery-slot img { width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; }
.gallery-slot.has-image {
    opacity: 1; border-style: solid; border-color: var(--border-dk); cursor: default;
}
.gallery-slot.has-image:hover { border-color: var(--brand); }
.gallery-slot-remove {
    position: absolute; top: 5px; right: 5px; z-index: 10;
    width: 24px; height: 24px; border-radius: 50%;
    background: rgba(0,0,0,.72); color: #fff;
    border: 1.5px solid rgba(255,255,255,.3);
    cursor: pointer; font-size: 12px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    line-height: 1; padding: 0; transition: background .15s;
}
.gallery-slot-remove:hover { background: var(--danger); border-color: var(--danger); }

/* ── Toggles (checkbox stylisés) ── */
.toggle-field { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f3f6f4; }
.toggle-field:last-child { border-bottom: none; }
.toggle-field-info {}
.toggle-field-lbl { font-size: 13px; font-weight: 600; color: var(--text); }
.toggle-field-hint { font-size: 11px; color: var(--muted); margin-top: 2px; }
.toggle-ctrl { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
.toggle-ctrl input { opacity: 0; width: 0; height: 0; position: absolute; }
.toggle-ctrl-track {
    position: absolute; inset: 0; border-radius: 24px;
    background: #d1d5db; cursor: pointer; transition: background .2s;
}
.toggle-ctrl input:checked + .toggle-ctrl-track { background: var(--brand); }
.toggle-ctrl-track::after {
    content: ''; position: absolute; top: 3px; left: 3px;
    width: 18px; height: 18px; border-radius: 50%;
    background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.2);
    transition: transform .2s;
}
.toggle-ctrl input:checked + .toggle-ctrl-track::after { transform: translateX(20px); }

/* ── Variantes (taille/couleur/quantité) ── */
.variant-hd-row, .variant-row { display: grid; grid-template-columns: 44px 1.3fr .8fr .6fr .8fr 34px; gap: 8px; align-items: center; }
.variant-hd-row { font-size: 10.5px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
.variant-row { margin-bottom: 8px; }
.variant-row .field-input { padding: 9px 10px; font-size: 13px; }
.variant-thumb { width: 44px; height: 44px; border-radius: 8px; border: 1.5px dashed var(--border); background: var(--bg); display: flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; overflow: hidden; flex-shrink: 0; transition: border-color .15s; }
.variant-thumb:hover { border-color: var(--brand); }
.variant-thumb img { width: 100%; height: 100%; object-fit: cover; }
.variant-rm { width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid var(--border); background: var(--surface); color: var(--danger); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 15px; transition: all .15s; flex-shrink: 0; }
.variant-rm:hover { background: var(--danger-lt); border-color: var(--danger); }
.variant-add-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 9px; border: 1.5px dashed var(--brand); background: var(--brand-lt); color: var(--brand-dk); font-size: 12.5px; font-weight: 700; cursor: pointer; transition: all .15s; font-family: var(--font); }
.variant-add-btn:hover { background: var(--brand); color: #fff; }

/* ── Allergens & tags ── */
.tag-input-wrap { display: flex; flex-wrap: wrap; gap: 6px; padding: 8px 10px; border: 1.5px solid var(--border); border-radius: var(--r-sm); min-height: 42px; cursor: text; transition: border-color .15s; }
.tag-input-wrap:focus-within { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(31,124,246,.12); }
.tag-chip { display: inline-flex; align-items: center; gap: 4px; background: var(--brand-lt); color: var(--brand-dk); font-size: 11.5px; font-weight: 600; padding: 3px 9px; border-radius: 20px; }
.tag-chip-remove { background: none; border: none; cursor: pointer; color: var(--brand-dk); font-size: 13px; padding: 0; line-height: 1; }
.tag-real-input { border: none; outline: none; font-size: 13px; font-family: var(--font); flex: 1; min-width: 80px; background: transparent; }

/* ── Boutons actions ── */
.form-actions {
    display: flex; gap: 10px; align-items: center;
    padding-top: 16px;
}
.btn-submit {
    padding: 12px 28px; border-radius: var(--r-sm);
    font-size: 14px; font-weight: 700; font-family: var(--font);
    background: var(--brand); color: #fff;
    border: 1.5px solid var(--brand-dk);
    cursor: pointer; transition: all .15s;
    display: inline-flex; align-items: center; gap: 7px;
    box-shadow: 0 4px 14px rgba(31,124,246,.3);
}
.btn-submit:hover { background: var(--brand-dk); transform: translateY(-1px); }
.btn-submit:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.btn-cancel {
    padding: 12px 20px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 600; font-family: var(--font);
    border: 1.5px solid var(--border-dk); background: var(--surface);
    color: var(--text-2); cursor: pointer; text-decoration: none;
    transition: all .15s; display: inline-flex; align-items: center; gap: 6px;
}
.btn-cancel:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }

/* ── Preview sidebar ── */
.preview-card-product {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.preview-card-img {
    height: 160px; background: linear-gradient(135deg, #f3f6f4, #eef1f0);
    display: flex; align-items: center; justify-content: center;
    font-size: 40px; opacity: .3; overflow: hidden;
}
.preview-card-img img { width: 100%; height: 100%; object-fit: cover; display: none; }
.preview-card-body { padding: 14px 16px; }
.preview-cat  { font-size: 10px; font-weight: 700; color: var(--brand); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.preview-name { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 6px; line-height: 1.3; }
.preview-desc { font-size: 12px; color: var(--muted); margin-bottom: 10px; line-height: 1.5; }
.preview-price { font-size: 20px; font-weight: 800; font-family: var(--mono); color: var(--brand); }
.preview-price-orig { font-size: 12px; color: var(--muted); text-decoration: line-through; font-family: var(--mono); }
.preview-meta { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
.preview-chip {
    font-size: 10.5px; font-weight: 600; color: var(--text-2);
    background: var(--bg); border: 1px solid var(--border);
    padding: 2px 8px; border-radius: 6px;
}

/* Flash */
.flash { padding: 11px 16px; border-radius: var(--r-sm); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 18px; display: flex; gap: 8px; }
.flash-danger  { background: var(--danger-lt); border-color: #fca5a5; color: #991b1b; }
.flash ul { margin: 0; padding-left: 16px; }
.flash ul li { margin-top: 3px; font-size: 12.5px; }

/* ── Spinner upload ── */
@keyframes spin   { to { transform: rotate(360deg); } }
@keyframes iaSpin { to { transform: rotate(360deg); } }
.upload-spin { display: inline-block; animation: spin .8s linear infinite; font-size: 28px; }
.btn-spinner {
    display: inline-block; width: 15px; height: 15px;
    border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
    border-radius: 50%; animation: spin .7s linear infinite;
    flex-shrink: 0;
}
.upload-uploading {
    position: absolute; inset: 0; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: var(--brand-mlt); gap: 8px; z-index: 2;
}
.upload-uploading-txt { font-size: 12px; font-weight: 700; color: var(--brand-dk); }
/* Mini spinner pendant upload (preview déjà visible) */
.upload-mini-spin {
    position: absolute; bottom: 8px; right: 8px; z-index: 4;
    width: 30px; height: 30px; border-radius: 50%;
    background: rgba(0,0,0,.55);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; pointer-events: none;
}

/* ── Aperçu produit mobile (visible uniquement ≤ 900px) ── */
.mobile-product-preview {
    display: none;
    align-items: center; gap: 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 14px 16px;
    margin-bottom: 18px;
    box-shadow: var(--shadow-sm);
    position: relative; overflow: hidden;
}
.mobile-product-preview::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--brand), #7DB4FF);
}
.mpv-img {
    width: 60px; height: 60px; border-radius: 10px; flex-shrink: 0;
    background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; overflow: hidden; position: relative;
    border: 1.5px solid var(--border);
}
.mpv-img img {
    position: absolute; inset: 0;
    width: 100%; height: 100%; object-fit: cover; display: none;
}
.mpv-info { flex: 1; min-width: 0; }
.mpv-cat {
    font-size: 10px; font-weight: 700; color: var(--brand);
    text-transform: uppercase; letter-spacing: .5px;
    margin-bottom: 3px;
}
.mpv-name {
    font-size: 14px; font-weight: 800; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 4px;
}
.mpv-price-row { display: flex; align-items: baseline; gap: 7px; }
.mpv-price {
    font-size: 16px; font-weight: 800; color: var(--brand);
    font-family: var(--mono);
}
.mpv-orig {
    font-size: 11px; color: var(--muted);
    text-decoration: line-through; font-family: var(--mono);
    display: none;
}
.mpv-devise { font-size: 10px; color: var(--muted); font-weight: 600; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .product-form-wrap { grid-template-columns: 1fr; }
    .sidebar-col { display: none; }
    .field-row-3 { grid-template-columns: 1fr 1fr; }
    .mobile-product-preview { display: flex; }
}
@media (max-width: 640px) {
    .product-form-wrap { padding: 16px 14px 48px; gap: 14px; }
    .form-card-hd { padding: 12px 16px; }
    .form-card-body { padding: 16px; }
    .form-card-sub { display: none; }
    .form-actions { flex-direction: column-reverse; gap: 8px; }
    .btn-submit, .btn-cancel { width: 100%; justify-content: center; text-align: center; }
    /* iOS anti-zoom : font-size < 16px déclenche un zoom automatique au focus */
    .field-input, .field-select, .field-textarea, .tag-real-input { font-size: 16px !important; }
}
@media (max-width: 480px) {
    .product-form-wrap { padding: 12px 10px 40px; }
    .field-row, .field-row-3 { grid-template-columns: 1fr; }
    .form-card-hd-ico { width: 24px; height: 24px; font-size: 12px; border-radius: 6px; }
    .form-card-title { font-size: 12.5px; }
    .upload-gallery-grid { grid-template-columns: repeat(3, 1fr); gap: 6px; }
    .toggle-field { padding: 10px 0; }
    .toggle-field-lbl { font-size: 12.5px; }
}
@media (max-width: 360px) {
    .product-form-wrap { padding: 10px 8px 36px; }
    .form-card-hd { padding: 10px 12px; gap: 7px; }
    .form-card-body { padding: 12px; }
    .field-lbl { font-size: 11px; }
    .field-input, .field-select, .field-textarea { font-size: 16px !important; padding: 9px 11px; }
    .upload-gallery-grid { grid-template-columns: repeat(2, 1fr); }
    .btn-submit { padding: 12px 16px; font-size: 13.5px; }
}
/* ══ Design espace vendeur (bleu Shopio, pilules, menu latéral) ══ */
.pf-top { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; position: sticky; top: 0; z-index: 30; padding: 14px 28px !important; }
.pf-top .tb-info { flex: 1 1 240px; min-width: 0; }
.pf-crumb { display: flex; gap: 6px; align-items: center; font-size: 13px; color: var(--muted); min-width: 0; }
.pf-crumb a { color: var(--sv-btn, #1668D8); font-weight: 700; text-decoration: none; }
.pf-crumb a:hover { text-decoration: underline; }
.pf-crumb span:last-child { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pf-title { margin: 2px 0 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
.pf-back { display: inline-flex; align-items: center; min-height: 44px; padding: 0 18px; border-radius: 999px; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); font-weight: 700; font-size: 14px; text-decoration: none; }
.pf-back:hover { box-shadow: inset 0 0 0 1.5px var(--sv-blue, #1F7CF6); color: var(--sv-btn, #1668D8); }
.product-form-wrap { max-width: 1180px; margin: 0; padding: 24px 28px 60px; grid-template-columns: minmax(0, 1fr) 320px; overflow: visible; }
.form-card { border: 0; box-shadow: 0 0 0 1px var(--border); border-radius: 24px; }
.form-card-hd { background: transparent; padding: 16px 22px; gap: 12px; }
.form-card-hd-ico { width: 38px; height: 38px; border-radius: 12px; background: var(--sv-b50, #EAF2FF); border: 0; color: var(--sv-btn, #1668D8); }
.form-card-title { font-size: 16px; font-weight: 800; letter-spacing: -.01em; }
.form-card-body { padding: 20px 22px 22px; }
.field-lbl { font-size: 12px; letter-spacing: .06em; }
.field-input, .field-select, .field-textarea { border-radius: 14px; min-height: 46px; font-size: 14px; }
.field-textarea { min-height: 110px; }
.field-input:focus, .field-select:focus, .field-textarea:focus { border-color: var(--sv-blue, #1F7CF6); box-shadow: 0 0 0 3px var(--sv-b50, #EAF2FF); }
/* Photo principale : cadre carré, comme le format conseillé */
.upload-main { aspect-ratio: 1 / 1; border: 2px dashed var(--sv-b100, #D6E6FF); background: var(--sv-b50, #EAF2FF); border-radius: 20px; }
.upload-main:hover, .upload-main.over { border-color: var(--sv-blue, #1F7CF6); background: var(--sv-b50, #EAF2FF); }
.upload-placeholder-ico { opacity: 1; color: var(--sv-btn, #1668D8); display: flex; justify-content: center; }
.upload-placeholder-txt { color: var(--text); font-weight: 700; }
/* « Changer la photo » seulement quand une photo est en place */
.upload-main:hover .upload-main-overlay { display: none; }
.upload-main:hover .upload-main-preview.visible ~ .upload-main-overlay { display: flex; background: rgba(11,30,61,.55); }
.gallery-slot { opacity: 1; border-radius: 14px; border-color: var(--sv-b100, #D6E6FF); background: var(--sv-b50, #EAF2FF); color: var(--sv-btn, #1668D8); }
/* Boutons en pilule */
.btn-submit { border-radius: 999px; border: 0; background: var(--sv-btn, #1668D8); box-shadow: none; min-height: 48px; padding: 0 28px; }
.btn-submit:hover { background: var(--sv-btn-h, #1260C9); box-shadow: 0 8px 20px rgba(22,104,216,.28); }
.btn-cancel { border-radius: 999px; border: 0; box-shadow: inset 0 0 0 1.5px var(--border); min-height: 48px; padding: 0 22px; color: var(--text); }
.btn-cancel:hover { background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--sv-blue, #1F7CF6); color: var(--sv-btn, #1668D8); }
.variant-add-btn { border-radius: 999px; }
.btn-submit:focus-visible, .btn-cancel:focus-visible, .variant-add-btn:focus-visible, .pf-back:focus-visible { outline: 3px solid var(--sv-blue, #1F7CF6); outline-offset: 2px; }
/* Aperçu et conseils */
.preview-card-product { border: 0; box-shadow: none; border-radius: 0; }
.preview-card-img { background: var(--sv-b50, #EAF2FF); opacity: 1; color: var(--sv-blue, #1F7CF6); aspect-ratio: 3 / 2; height: auto; }
.preview-price { color: var(--sv-btn, #1668D8); }
.pf-tip-ico { width: 34px; height: 34px; border-radius: 10px; background: var(--sv-b50, #EAF2FF); color: var(--sv-btn, #1668D8); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mobile-product-preview { border: 0; box-shadow: 0 0 0 1px var(--border); border-radius: 20px; }
.mobile-product-preview::before { display: none; }
.mpv-img { background: var(--sv-b50, #EAF2FF); color: var(--sv-blue, #1F7CF6); border: 0; }
@media (max-width: 900px) {
    .pf-top { padding: 12px 16px !important; }
    .product-form-wrap { padding: 18px 16px 48px; grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 560px) { .pf-back { display: none; } }
@media (prefers-reduced-motion: reduce) { .btn-submit:hover { transform: none; } }
</style>
@endpush

@section('content')
@php
    $pfIco = collect(['camera' => 'M4 8h3.5L9 5.5h6L16.5 8H20v11H4zM12 16.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z', 'pencil' => 'M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4', 'price' => 'M12 3v18M16.5 7.5c0-1.7-2-3-4.5-3s-4.5 1.3-4.5 3 2 2.6 4.5 3 4.5 1.3 4.5 3-2 3-4.5 3-4.5-1.3-4.5-3', 'star' => 'M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9z'])
        ->map(fn ($d) => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>')->all();
@endphp
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'products'])

    <main class="main vs-main">
        <div class="topbar pf-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="tb-info">
                <nav class="pf-crumb" aria-label="Fil d'Ariane"><a href="{{ route('products.index') }}">Produits</a><span aria-hidden="true">/</span><span>{{ $isEdit ? Str::limit($product->name, 40) : 'Nouveau produit' }}</span></nav>
                <h1 class="pf-title">{{ $isEdit ? 'Modifier le produit' : 'Ajouter un produit' }}</h1>
            </div>
            <a href="{{ route('products.index') }}" class="pf-back">Retour aux produits</a>
        </div>

<div class="product-form-wrap">

    {{-- ═══ COLONNE PRINCIPALE ═══ --}}
    <div class="main-col">

        {{-- Flash --}}
        @if($errors->any())
        <div class="flash flash-danger">
            <span>⚠</span>
            <div>
                <strong>Veuillez corriger les erreurs :</strong>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        </div>
        @endif

        {{-- Aperçu produit mobile (caché sur desktop) --}}
        <div class="mobile-product-preview">
            <div class="mpv-img" id="mpvImgWrap">
                <span id="mpvEmoji"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16v14H4zM4 15l4.5-4.5 4 4 2.5-2.5L20 17M15.5 9.5h.01"/></svg></span>
                <img id="mpvImg" src="" alt="">
            </div>
            <div class="mpv-info">
                <div class="mpv-cat"  id="mpvCat">{{ $isEdit ? ($product->category ?? '') : 'Catégorie' }}</div>
                <div class="mpv-name" id="mpvName">{{ $isEdit ? $product->name : 'Nom du produit' }}</div>
                <div class="mpv-price-row">
                    <span class="mpv-price"  id="mpvPrice">{{ $isEdit ? number_format($product->price, 0, ',', ' ') : '0' }}</span>
                    <span class="mpv-devise">{{ $devise }}</span>
                    <span class="mpv-orig"   id="mpvOrig">{{ $isEdit && ($product->original_price ?? false) ? number_format($product->original_price, 0, ',', ' ') : '' }}</span>
                </div>
            </div>
        </div>

        <form action="{{ $isEdit ? route('products.update', $product) : route('products.store') }}"
              method="POST"
              enctype="multipart/form-data"
              id="productForm">
            @csrf
            @if($isEdit) @method('PUT') @endif

            {{-- ── CARD 1 : Photos du produit ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16v14H4zM4 15l4.5-4.5 4 4 2.5-2.5L20 17M15.5 9.5h.01"/></svg></div>
                    <span class="form-card-title">Photos du produit</span>
                    <span class="form-card-sub">Max 20 Mo par image</span>
                </div>
                <div class="form-card-body">

                    <div class="field-row" style="align-items:start">
                        {{-- Image principale --}}
                        <div class="field">
                            <label class="field-lbl">Photo principale</label>
                            <input type="file" id="mainImageInput"
                                   class="upload-main-input" accept="image/*">
                            <input type="hidden" id="mainImageUploaded" name="image_uploaded" value="">
                            <div class="upload-main" id="mainUploadZone"
                                 onclick="document.getElementById('mainImageInput').click()">
                                <img id="mainPreview" class="upload-main-preview" src="" alt="Aperçu" data-no-skeleton>
                                <div class="upload-main-overlay"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8h3.5L9 5.5h6L16.5 8H20v11H4zM12 16.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/></svg> Changer la photo</div>
                                <div class="upload-placeholder" id="mainPlaceholder">
                                    <span class="upload-placeholder-ico"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8h3.5L9 5.5h6L16.5 8H20v11H4zM12 16.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/></svg></span>
                                    <div class="upload-placeholder-txt">Ajouter la photo principale</div>
                                    <div class="upload-placeholder-hint">Carré conseillé · JPG · PNG · WEBP — Max 20 Mo</div>
                                </div>
                                <button type="button" class="img-remove"
                                        id="mainRemoveBtn"
                                        onclick="event.stopPropagation();removeMainImage()">✕</button>
                            </div>
                            @error('image_uploaded')<div class="field-error" style="margin-top:6px">{{ $message }}</div>@enderror
                        </div>

                        {{-- Galerie secondaire --}}
                        <div class="field">
                            <label class="field-lbl">Photos supplémentaires</label>
                            <div class="upload-gallery-grid" id="galleryGrid">
                                <div class="gallery-slot" id="galleryAddBtn"
                                     onclick="document.getElementById('galleryInput').click()"
                                     title="Ajouter une photo">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                </div>
                            </div>
                            <input type="file" id="galleryInput" accept="image/*" multiple style="display:none">
                            <div class="field-hint" style="margin-top:8px">
                                + pour ajouter, croix pour retirer · 20 photos maximum.
                            </div>
                        </div>
                    </div>


                    {{-- Format conseillé + aperçu accueil / vitrine / fiche, et ajustement automatique --}}
                    @include('vendeur.products._image-guide')
                </div>
            </div>

            {{-- ── CARD 2 : Informations principales ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12 12 3h8v8l-9 9zM15.5 8.5h.01"/></svg></div>
                    <span class="form-card-title">Informations principales</span>
                </div>
                <div class="form-card-body">

                    <div class="field">
                        <label class="field-lbl" for="name">Nom du produit <span>*</span></label>
                        <input type="text" name="name" id="name"
                               class="field-input {{ $errors->has('name') ? 'error' : '' }}"
                               value="{{ old('name', $isEdit ? $product->name : '') }}"
                               placeholder="Ex : Poulet braisé, Robe Batik, iPhone 14…"
                               required>
                        @error('name')<div class="field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                            <label class="field-lbl" for="description" style="margin-bottom:0">Description</label>
                            @if($isPro)
                            <button type="button" id="btnShopioIA" onclick="generateAiDescription()"
                                style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;border:1.5px solid #1F7CF6;background:linear-gradient(135deg,#EAF2FF,#D6E6FF);color:#1260C9;font-size:12px;font-weight:700;cursor:pointer;transition:all .2s;font-family:var(--font);white-space:nowrap">
                                ✨ Shopio IA
                            </button>
                            @else
                            <a href="{{ route('boutique.subscription.upgrade') }}"
                                style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;border:1.5px solid #d1d5db;background:#f9fafb;color:#9ca3af;font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap;cursor:pointer"
                                title="Fonctionnalité réservée au plan Pro">
                                🔒 Shopio IA <span style="font-size:10px;background:#f59e0b;color:#fff;padding:1px 6px;border-radius:10px;font-weight:800">PRO</span>
                            </a>
                            @endif
                        </div>
                        <div id="shopioIaStatus" style="display:none;align-items:center;gap:8px;padding:10px 14px;background:#EAF2FF;border:1px solid #D6E6FF;border-radius:9px;margin-bottom:8px;font-size:12.5px;color:#1260C9;font-weight:600">
                            <span style="display:inline-block;width:14px;height:14px;border:2px solid #D6E6FF;border-top-color:#1F7CF6;border-radius:50%;animation:iaSpin .7s linear infinite;flex-shrink:0"></span>
                            Shopio IA génère la description…
                        </div>
                        <textarea name="description" id="description"
                                  class="field-textarea"
                                  rows="3"
                                  placeholder="Décrivez votre produit ou cliquez ✨ Shopio IA pour générer automatiquement…">{{ old('description', $isEdit ? $product->description : '') }}</textarea>
                        <div class="field-hint">Max 2000 caractères · <span style="color:#1F7CF6;font-weight:600">✨ Shopio IA</span> génère la description depuis la photo, le nom et le prix.</div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label class="field-lbl" for="category">Catégorie</label>
                            @php
                                $currentCat = old('category', $isEdit ? $product->category : '');
                                $isCustomCat = $currentCat && !in_array($currentCat, $categories);
                            @endphp
                            <input type="hidden" name="category" id="categoryHidden" value="{{ $currentCat }}">
                            <div style="display:flex;gap:8px;align-items:center" id="categorySelectWrap">
                                <select id="categorySelect" class="field-select" style="flex:1"
                                        onchange="onCategorySelect(this.value)">
                                    <option value="">Sélectionner…</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat }}"
                                        {{ (!$isCustomCat && $currentCat === $cat) ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                    @endforeach
                                    @foreach($customCats ?? [] as $cat)
                                    @if($cat !== $currentCat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endif
                                    @endforeach
                                    @if($isCustomCat)
                                    <option value="{{ $currentCat }}" selected>{{ $currentCat }}</option>
                                    @endif
                                    <option value="__custom__">➕ Saisir une catégorie…</option>
                                </select>
                            </div>
                            <div id="categoryCustomWrap" style="display:{{ $isCustomCat ? 'flex' : 'none' }};gap:8px;align-items:center;margin-top:8px">
                                <input type="text" id="categoryCustomInput"
                                       class="field-input" style="flex:1"
                                       value="{{ $isCustomCat ? $currentCat : '' }}"
                                       placeholder="Ex : Produits locaux, Artisanat…"
                                       oninput="onCustomCategoryInput(this.value)">
                                <button type="button"
                                        onclick="cancelCustomCategory()"
                                        style="padding:9px 12px;border-radius:var(--r-sm);border:1.5px solid var(--border-dk);background:var(--surface);color:var(--muted);font-size:12px;cursor:pointer;white-space:nowrap;font-family:var(--font)">
                                    ✕ Annuler
                                </button>
                            </div>
                            <div class="field-hint">Catégorie introuvable ? Cliquez sur <strong>➕ Saisir une catégorie…</strong></div>
                        </div>
                        <div class="field">
                            <label class="field-lbl" for="tags">Mots-clés / Tags</label>
                            <input type="text" name="tags" id="tags"
                                   class="field-input"
                                   value="{{ old('tags', $isEdit ? $product->tags : '') }}"
                                   placeholder="Ex : épicé, sans gluten, promo…">
                            <div class="field-hint">Séparés par des virgules.</div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── CARD 3 : Prix & Stock ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18M16.5 7.5c0-1.7-2-3-4.5-3s-4.5 1.3-4.5 3 2 2.6 4.5 3 4.5 1.3 4.5 3-2 3-4.5 3-4.5-1.3-4.5-3"/></svg></div>
                    <span class="form-card-title">Prix & Stock</span>
                </div>
                <div class="form-card-body">

                    <div class="field-row">
                        <div class="field">
                            <label class="field-lbl" for="price">Prix de vente <span>*</span></label>
                            <div class="price-wrap">
                                <input type="number" name="price" id="price"
                                       class="field-input {{ $errors->has('price') ? 'error' : '' }}"
                                       value="{{ old('price', $isEdit ? $product->price : '') }}"
                                       step="1" min="0"
                                       placeholder="0"
                                       required>
                                <span class="price-suffix">{{ $devise }}</span>
                            </div>
                            @error('price')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label class="field-lbl" for="original_price">Prix original (barré)</label>
                            <div class="price-wrap">
                                <input type="number" name="original_price" id="original_price"
                                       class="field-input"
                                       value="{{ old('original_price', $isEdit ? ($product->original_price ?? '') : '') }}"
                                       step="1" min="0"
                                       placeholder="0">
                                <span class="price-suffix">{{ $devise }}</span>
                            </div>
                            <div class="field-hint">Laissez vide si pas de promo.</div>
                        </div>
                    </div>

                    <div class="field-row-3">
                        <div class="field">
                            <label class="field-lbl" for="stock">Stock</label>
                            <input type="number" name="stock" id="stock"
                                   class="field-input"
                                   value="{{ old('stock', $isEdit ? ($product->stock ?? 0) : 0) }}"
                                   min="0" step="1" placeholder="0">
                        </div>
                        <div class="field">
                            <label class="field-lbl" for="unit">Unité</label>
                            <select name="unit" id="unit" class="field-select">
                                @foreach(['pièce','kg','g','litre','cl','ml','portion','boîte','sachet','pack','lot','mètre','paire'] as $u)
                                <option value="{{ $u }}"
                                    {{ old('unit', $isEdit ? ($product->unit ?? 'pièce') : 'pièce') === $u ? 'selected' : '' }}>
                                    {{ $u }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-lbl" for="preparation_time">Préparation (min)</label>
                            <input type="number" name="preparation_time" id="preparation_time"
                                   class="field-input"
                                   value="{{ old('preparation_time', $isEdit ? ($product->preparation_time ?? '') : '') }}"
                                   min="0" step="1" placeholder="—">
                            <div class="field-hint">Restaurant uniquement.</div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── CARD : Vente flash ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 3 5 13.5h6L10 21l9-11h-6z"/></svg></div>
                    <span class="form-card-title">Vente flash</span>
                    <span class="form-card-sub">Optionnel</span>
                </div>
                <div class="form-card-body">
                    <div class="toggle-field">
                        <div class="toggle-field-info">
                            <div class="toggle-field-lbl">Activer une vente flash</div>
                            <div class="toggle-field-hint">Prix réduit pendant une durée limitée, avec compte à rebours visible par le client</div>
                        </div>
                        <label class="toggle-ctrl">
                            <input type="checkbox" id="flashToggle" onchange="toggleFlashFields()"
                                   {{ old('flash_price', $isEdit ? $product->flash_price : null) ? 'checked' : '' }}>
                            <div class="toggle-ctrl-track"></div>
                        </label>
                    </div>

                    <div id="flashFields" style="display:none;margin-top:14px">
                        <div class="field-row-3">
                            <div class="field">
                                <label class="field-lbl" for="flash_price">Prix flash <span>*</span></label>
                                <div class="price-wrap">
                                    <input type="number" name="flash_price" id="flash_price"
                                           class="field-input {{ $errors->has('flash_price') ? 'error' : '' }}"
                                           value="{{ old('flash_price', $isEdit ? ($product->flash_price ?? '') : '') }}"
                                           step="1" min="0" placeholder="0">
                                    <span class="price-suffix">{{ $devise }}</span>
                                </div>
                                @error('flash_price')<div class="field-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="field" style="grid-column: span 2">
                                <label class="field-lbl" for="flash_ends_at">Fin de la vente flash <span>*</span></label>
                                <input type="datetime-local" name="flash_ends_at" id="flash_ends_at"
                                       class="field-input {{ $errors->has('flash_ends_at') ? 'error' : '' }}"
                                       value="{{ old('flash_ends_at', $isEdit && $product->flash_ends_at ? $product->flash_ends_at->format('Y-m-d\TH:i') : '') }}">
                                @error('flash_ends_at')<div class="field-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px">
                            <button type="button" class="variant-add-btn" onclick="setFlashDuration(24)">+24h (FLASH24H)</button>
                            <button type="button" class="variant-add-btn" onclick="setFlashDuration(48)">+48h</button>
                            <button type="button" class="variant-add-btn" onclick="setFlashDuration(72)">+72h</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── CARD : Variantes (taille/couleur/quantité) ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 3 7.5l9 4.5 9-4.5zM3 12l9 4.5 9-4.5M3 16.5 12 21l9-4.5"/></svg></div>
                    <span class="form-card-title">Variantes</span>
                    <span class="form-card-sub">Optionnel</span>
                </div>
                <div class="form-card-body">
                    <div class="field-hint" style="margin-bottom:12px">
                        Si ce produit existe en plusieurs couleurs/tailles, ajoutez-les ici avec leur propre stock (et prix si différent). Sinon, le stock global ci-dessus suffit.
                    </div>
                    <div style="overflow-x:auto">
                        <div style="min-width:560px">
                            <div class="variant-hd-row">
                                <span>Photo</span><span>Nom (ex : Rouge - M)</span><span>Prix ({{ $devise }})</span><span>Stock</span><span>SKU</span><span></span>
                            </div>
                            <div id="variantsList"></div>
                        </div>
                    </div>
                    <button type="button" class="variant-add-btn" onclick="addVariantRow()">+ Ajouter une variante</button>
                </div>
            </div>

            {{-- ── CARD 4 : Options avancées ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h4M12 17h8M16 5v4M10 15v4"/></svg></div>
                    <span class="form-card-title">Options & disponibilité</span>
                </div>
                <div class="form-card-body" style="padding:8px 20px">

                    <div class="toggle-field">
                        <div class="toggle-field-info">
                            <div class="toggle-field-lbl">Produit actif</div>
                            <div class="toggle-field-hint">Visible par les clients dans la boutique</div>
                        </div>
                        <label class="toggle-ctrl">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $isEdit ? $product->is_active : true) ? 'checked' : '' }}>
                            <div class="toggle-ctrl-track"></div>
                        </label>
                    </div>

                    <div class="toggle-field">
                        <div class="toggle-field-info">
                            <div class="toggle-field-lbl">⭐ Produit en vedette</div>
                            <div class="toggle-field-hint">Mis en avant dans le catalogue</div>
                        </div>
                        <label class="toggle-ctrl">
                            <input type="checkbox" name="is_featured" value="1"
                                   {{ old('is_featured', $isEdit ? ($product->is_featured ?? false) : false) ? 'checked' : '' }}>
                            <div class="toggle-ctrl-track"></div>
                        </label>
                    </div>

                    <div class="toggle-field">
                        <div class="toggle-field-info">
                            <div class="toggle-field-lbl">🟢 Disponible aujourd'hui</div>
                            <div class="toggle-field-hint">Utile pour les restaurants (plat du jour)</div>
                        </div>
                        <label class="toggle-ctrl">
                            <input type="checkbox" name="is_available" value="1"
                                   {{ old('is_available', $isEdit ? ($product->is_available ?? true) : true) ? 'checked' : '' }}>
                            <div class="toggle-ctrl-track"></div>
                        </label>
                    </div>

                </div>
            </div>

            {{-- ── CARD 5 : Allergènes ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4 2.5 20h19zM12 10v4.5M12 17.5h.01"/></svg></div>
                    <span class="form-card-title">Allergènes & infos santé</span>
                    <span class="form-card-sub">Recommandé pour les restaurants</span>
                </div>
                <div class="form-card-body">
                    <div class="field">
                        <label class="field-lbl" for="allergens">Allergènes</label>
                        <input type="text" name="allergens" id="allergens"
                               class="field-input"
                               value="{{ old('allergens', $isEdit ? ($product->allergens ?? '') : '') }}"
                               placeholder="Ex : gluten, lactose, arachides, fruits de mer…">
                        <div class="field-hint">Séparez par des virgules. Obligatoire pour la restauration.</div>
                    </div>
                </div>
            </div>

            {{-- ── Boutons ── --}}
            <div class="form-actions">
                <a href="{{ route('products.index') }}" class="btn-cancel">Annuler</a>
                <button type="submit" class="btn-submit" id="submitBtn">
                    {{ $isEdit ? 'Enregistrer les modifications' : 'Ajouter le produit' }}
                </button>
            </div>

        </form>
    </div>

    {{-- ═══ SIDEBAR (preview live) ═══ --}}
    <div class="sidebar-col">
        <div style="position:sticky;top:24px">

            {{-- Preview card --}}
            <div class="form-card" style="margin-bottom:16px">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg></div>
                    <span class="form-card-title">Aperçu client</span>
                </div>
                <div class="preview-card-product">
                    <div class="preview-card-img" id="previewImgWrap">
                        <img id="previewImgEl" src="" alt="">
                        <span id="previewImgPlaceholder"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16v14H4zM4 15l4.5-4.5 4 4 2.5-2.5L20 17M15.5 9.5h.01"/></svg></span>
                    </div>
                    <div class="preview-card-body">
                        <div class="preview-cat" id="previewCat">{{ $isEdit ? ($product->category ?? '') : '' }}</div>
                        <div class="preview-name" id="previewName">{{ $isEdit ? $product->name : 'Nom du produit' }}</div>
                        <div class="preview-desc" id="previewDesc">{{ $isEdit ? Str::limit($product->description ?? '', 80) : 'Description du produit…' }}</div>
                        <div style="display:flex;align-items:baseline;gap:8px">
                            <div class="preview-price" id="previewPrice">
                                {{ $isEdit ? number_format($product->price, 0, ',', ' ') : '0' }}
                                <span style="font-size:11px;font-weight:600;color:var(--muted)">{{ $devise }}</span>
                            </div>
                            <div class="preview-price-orig" id="previewOrig" style="{{ (!$isEdit || !$product->original_price) ? 'display:none' : '' }}">
                                {{ $isEdit && $product->original_price ? number_format($product->original_price, 0, ',', ' ') : '' }}
                            </div>
                        </div>
                        <div class="preview-meta" id="previewMeta">
                            @if($isEdit)
                                @if($product->stock ?? false)<span class="preview-chip">📦 {{ $product->stock }}</span>@endif
                                @if($product->preparation_time ?? false)<span class="preview-chip">⏱ {{ $product->preparation_time }}min</span>@endif
                                @if($product->unit ?? false)<span class="preview-chip">/ {{ $product->unit }}</span>@endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Conseils --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.4 1 1.1 1 1.9V16h5v-.2c0-.8.4-1.5 1-1.9A6 6 0 0 0 12 3z"/></svg></div>
                    <span class="form-card-title">Conseils de vente</span>
                </div>
                <div class="form-card-body" style="padding:16px 18px;display:flex;flex-direction:column;gap:12px">
                    @foreach([
                        ['camera', 'Bonne photo', 'Une photo nette augmente les ventes de 3×.'],
                        ['pencil', 'Description claire', 'Mentionnez les ingrédients, taille, matière.'],
                        ['price', 'Prix juste', 'Comparez avec le marché local.'],
                        ['star', 'Mettez en vedette', 'Les produits vedettes sont vus en premier.'],
                    ] as [$ico, $t, $d])
                    <div style="display:flex;gap:10px;align-items:flex-start">
                        <span class="pf-tip-ico">{!! $pfIco[$ico] !!}</span>
                        <div>
                            <div style="font-size:12px;font-weight:700;color:var(--text)">{{ $t }}</div>
                            <div style="font-size:11.5px;color:var(--muted);line-height:1.5">{{ $d }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</div>
    </main>
</div>
@endsection

@push('scripts')
<script>
/* ══════════════════════════════════════════════
   UPLOAD IMAGE PRINCIPALE — 100% AJAX
   Aucun fichier soumis dans le formulaire.
   ══════════════════════════════════════════════ */
const mainInput             = document.getElementById('mainImageInput');
const mainPreview           = document.getElementById('mainPreview');
const mainPlaceholder       = document.getElementById('mainPlaceholder');
const mainRemoveBtn         = document.getElementById('mainRemoveBtn');
const mainImageUploaded     = document.getElementById('mainImageUploaded');
const previewImgEl          = document.getElementById('previewImgEl');
const previewImgPlaceholder = document.getElementById('previewImgPlaceholder');
const mainUploadZone        = document.getElementById('mainUploadZone');

let mainUploading = false;

mainInput.addEventListener('change', function () {
    const file = this.files[0];
    this.value = '';
    if (!file) return;
    if (file.size > 20 * 1024 * 1024) { alert('Image trop lourde — maximum 20 Mo.'); return; }
    uploadMainImage(file);
});

async function uploadMainImage(file) {
    if (mainUploading) return;
    mainUploading = true;

    /* Afficher la preview locale IMMÉDIATEMENT — sans attendre le serveur */
    const localUrl = URL.createObjectURL(file);
    showMainPreview(localUrl);
    mainRemoveBtn.classList.remove('visible'); // pas de suppression pendant l'upload
    mainUploadZone.style.pointerEvents = 'none';

    /* Petit spinner discret en bas à droite (image déjà visible) */
    const miniSpin = document.createElement('div');
    miniSpin.className = 'upload-mini-spin';
    miniSpin.innerHTML = `<span class="upload-spin" style="font-size:14px">⏳</span>`;
    mainUploadZone.appendChild(miniSpin);

    try {
        const fd = new FormData();
        fd.append('file',   file);
        fd.append('folder', 'products');
        fd.append('_token', CSRF);

        const res = await fetch(UPLOAD_URL, { method: 'POST', body: fd });
        let json;
        try { json = await res.json(); } catch { throw new Error('Réponse serveur invalide'); }
        if (!res.ok || !json.path) throw new Error(json.message || `Erreur ${res.status}`);

        mainImageUploaded.value = json.path;
        /* Remplacer le blob local par l'URL serveur */
        mainPreview.src = json.url;
        if (previewImgEl) previewImgEl.src = json.url;
        const mi = document.getElementById('mpvImg');
        if (mi) mi.src = json.url;
        URL.revokeObjectURL(localUrl);
        mainRemoveBtn.classList.add('visible');

    } catch (err) {
        mainPreview.classList.remove('visible');
        mainRemoveBtn.classList.remove('visible');
        mainPlaceholder.style.display = '';
        URL.revokeObjectURL(localUrl);
        alert(`Erreur upload image : ${err.message}`);
    } finally {
        miniSpin.remove();
        mainUploadZone.style.pointerEvents = '';
        mainUploading = false;
    }
}

function showMainPreview(url) {
    mainPreview.src = url;
    window.pigCheck?.(url); // guide du format de la photo
    mainPreview.classList.add('visible');
    mainPlaceholder.style.display = 'none';
    mainRemoveBtn.classList.add('visible');
    if (previewImgEl) {
        previewImgEl.src = url;
        previewImgEl.style.display = 'block';
        if (previewImgPlaceholder) previewImgPlaceholder.style.display = 'none';
    }
    /* Mobile preview image */
    const mi = document.getElementById('mpvImg');
    const me = document.getElementById('mpvEmoji');
    if (mi) { mi.src = url; mi.style.display = 'block'; }
    if (me) me.style.display = 'none';
}

function removeMainImage() {
    window.pigReset?.();
    mainInput.value = '';
    mainImageUploaded.value = '';
    mainPreview.src = '';
    mainPreview.classList.remove('visible');
    mainPlaceholder.style.display = '';
    mainRemoveBtn.classList.remove('visible');
    if (previewImgEl) { previewImgEl.src = ''; previewImgEl.style.display = 'none'; }
    if (previewImgPlaceholder) previewImgPlaceholder.style.display = '';
}

const zone = document.getElementById('mainUploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); e.stopPropagation(); zone.classList.add('over'); });
zone.addEventListener('dragleave', e => { e.stopPropagation(); zone.classList.remove('over'); });
zone.addEventListener('drop', e => {
    e.preventDefault(); e.stopPropagation();
    zone.classList.remove('over');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        if (file.size > 20 * 1024 * 1024) { alert('Image trop lourde — maximum 20 Mo.'); return; }
        uploadMainImage(file);
    }
});

/* ══════════════════════════════════════════════
   GALERIE — upload AJAX image par image
   (évite "POST data is too large")
   ══════════════════════════════════════════════ */

const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
const UPLOAD_URL = '{{ route("products.upload.image") }}';

function updateGalleryAddBtn() {
    const total  = document.querySelectorAll('#galleryGrid .gallery-slot.has-image').length;
    const addBtn = document.getElementById('galleryAddBtn');
    if (addBtn) addBtn.style.display = total >= 20 ? 'none' : '';
}

/* Supprimer un slot galerie (nouveau — chemin AJAX) */
function removeNewGallerySlot(btn) {
    const slot = btn.closest('.gallery-slot');
    if (slot._hiddenPath) slot._hiddenPath.remove();
    slot.remove();
    updateGalleryAddBtn();
}

/* Upload une image via AJAX, crée le slot + input caché path */
async function uploadGalleryFile(file) {
    const total = document.querySelectorAll('#galleryGrid .gallery-slot.has-image').length;
    if (total >= 20) { alert('Maximum 20 photos de galerie atteint.'); return; }
    if (file.size > 20 * 1024 * 1024) { alert(`"${file.name}" dépasse 20 Mo.`); return; }

    /* Slot "en cours d'upload" */
    const addBtn = document.getElementById('galleryAddBtn');
    const slot = document.createElement('div');
    slot.className = 'gallery-slot has-image';
    slot.innerHTML = `
        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;
                    justify-content:center;background:var(--brand-mlt);gap:6px">
            <div style="font-size:20px">⏳</div>
            <div style="font-size:10px;color:var(--brand-dk);font-weight:700">Upload…</div>
        </div>`;
    addBtn.parentNode.insertBefore(slot, addBtn);
    updateGalleryAddBtn();

    try {
        const fd = new FormData();
        fd.append('file',   file);
        fd.append('folder', 'products/gallery');
        fd.append('_token', CSRF);

        const res  = await fetch(UPLOAD_URL, { method: 'POST', body: fd });
        let json;
        try { json = await res.json(); } catch { throw new Error('Réponse serveur invalide (HTML ?)'); }

        if (!res.ok || !json.path) throw new Error(json.message || `Erreur ${res.status}`);

        /* Slot final avec aperçu + input caché (path, pas le fichier) */
        const hiddenPath = document.createElement('input');
        hiddenPath.type  = 'hidden';
        hiddenPath.name  = 'gallery_uploaded[]';
        hiddenPath.value = json.path;
        document.getElementById('productForm').appendChild(hiddenPath);

        slot.innerHTML = `
            <img src="${json.url}" alt=""
                 style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0">
            <button type="button" class="gallery-slot-remove"
                    onclick="removeNewGallerySlot(this)" title="Supprimer">✕</button>`;
        slot._hiddenPath = hiddenPath;
        slot.querySelector('.gallery-slot-remove').addEventListener('click', () => {
            hiddenPath.remove();
        });

    } catch (err) {
        slot.remove();
        updateGalleryAddBtn();
        alert(`Erreur : ${err.message}`);
    }
}

document.getElementById('galleryInput').addEventListener('change', function () {
    Array.from(this.files).forEach(file => uploadGalleryFile(file));
    this.value = '';
});

/* ── Preview live ── */
function previewFmt(n) {
    const v = parseFloat(n) || 0;
    return v.toLocaleString('fr-FR', { maximumFractionDigits: 0 });
}

document.getElementById('name').addEventListener('input', e => {
    const v = e.target.value || 'Nom du produit';
    document.getElementById('previewName').textContent = v;
    const mn = document.getElementById('mpvName'); if (mn) mn.textContent = v;
});
document.getElementById('description').addEventListener('input', e => {
    const txt = e.target.value.substring(0, 80) + (e.target.value.length > 80 ? '…' : '');
    document.getElementById('previewDesc').textContent = txt || 'Description du produit…';
});
/* ── Catégorie : liste + saisie libre ── */
function onCategorySelect(val) {
    const hidden      = document.getElementById('categoryHidden');
    const customWrap  = document.getElementById('categoryCustomWrap');
    const customInput = document.getElementById('categoryCustomInput');

    if (val === '__custom__') {
        /* Afficher le champ texte libre */
        customWrap.style.display = 'flex';
        customInput.focus();
        hidden.value = '';
        document.getElementById('previewCat').textContent = '';
        /* Remettre le select sur "Sélectionner…" */
        document.getElementById('categorySelect').value = '';
    } else {
        /* Catégorie normale choisie dans la liste */
        customWrap.style.display = 'none';
        customInput.value = '';
        hidden.value = val;
        document.getElementById('previewCat').textContent = val;
        const mc = document.getElementById('mpvCat'); if (mc) mc.textContent = val;
    }
}

function onCustomCategoryInput(val) {
    document.getElementById('categoryHidden').value = val;
    document.getElementById('previewCat').textContent = val;
    const mc = document.getElementById('mpvCat'); if (mc) mc.textContent = val;
}

function cancelCustomCategory() {
    document.getElementById('categoryCustomWrap').style.display = 'none';
    document.getElementById('categoryCustomInput').value = '';
    document.getElementById('categoryHidden').value = '';
    document.getElementById('categorySelect').value = '';
    document.getElementById('previewCat').textContent = '';
    const mc = document.getElementById('mpvCat'); if (mc) mc.textContent = '';
}
document.getElementById('price').addEventListener('input', e => {
    const fmt = previewFmt(e.target.value);
    document.getElementById('previewPrice').innerHTML =
        fmt + ' <span style="font-size:11px;font-weight:600;color:var(--muted)">{{ $devise }}</span>';
    const mp = document.getElementById('mpvPrice'); if (mp) mp.textContent = fmt || '0';
});
document.getElementById('original_price').addEventListener('input', e => {
    const el = document.getElementById('previewOrig');
    const mo = document.getElementById('mpvOrig');
    if (e.target.value) {
        const fmt = previewFmt(e.target.value);
        el.textContent = fmt; el.style.display = '';
        if (mo) { mo.textContent = fmt; mo.style.display = ''; }
    } else {
        el.style.display = 'none';
        if (mo) mo.style.display = 'none';
    }
});
document.getElementById('stock').addEventListener('input', e => {
    updatePreviewMeta();
});
document.getElementById('preparation_time').addEventListener('input', () => updatePreviewMeta());
document.getElementById('unit').addEventListener('change', () => updatePreviewMeta());

function updatePreviewMeta() {
    const stock = document.getElementById('stock').value;
    const prep  = document.getElementById('preparation_time').value;
    const unit  = document.getElementById('unit').value;
    let html = '';
    if (stock) html += `<span class="preview-chip">📦 ${stock}</span>`;
    if (prep)  html += `<span class="preview-chip">⏱ ${prep}min</span>`;
    if (unit)  html += `<span class="preview-chip">/ ${unit}</span>`;
    document.getElementById('previewMeta').innerHTML = html;
}

/* ── Shopio IA ── */
async function generateAiDescription() {
    const name  = document.getElementById('name').value.trim();

    const price   = document.getElementById('price')?.value || '';
    const btn     = document.getElementById('btnShopioIA');
    const status  = document.getElementById('shopioIaStatus');
    const textarea = document.getElementById('description');

    btn.disabled = true;
    btn.innerHTML = '⏳ Génération…';
    status.style.display = 'flex';

    // Récupérer l'image principale en base64 si disponible
    let imageBase64 = null;
    const mainImg = document.getElementById('mainPreview');
    if (mainImg && mainImg.classList.contains('visible') && mainImg.src) {
        try {
            const imgRes = await fetch(mainImg.src);
            const blob   = await imgRes.blob();
            imageBase64  = await new Promise(resolve => {
                const reader = new FileReader();
                reader.onload = e => resolve(e.target.result);
                reader.readAsDataURL(blob);
            });
        } catch(e) { imageBase64 = null; }
    }

    if (!name && !imageBase64) {
        alert('Ajoutez au moins une photo ou un nom de produit.');
        return;
    }

    try {
        const res = await fetch('{{ route("products.ai.description") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name, price: price || null, image: imageBase64 }),
        });

        const data = await res.json();

        if (data.description) {
            textarea.value = data.description;
            textarea.dispatchEvent(new Event('input'));
            textarea.style.borderColor = '#1F7CF6';
            textarea.style.boxShadow = '0 0 0 3px rgba(31,124,246,.15)';
            setTimeout(() => { textarea.style.borderColor = ''; textarea.style.boxShadow = ''; }, 2000);
        } else {
            alert(data.error || 'Erreur Shopio IA. Réessayez.');
        }
    } catch(e) {
        alert('Erreur réseau. Vérifiez votre connexion.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '✨ Shopio IA';
        status.style.display = 'none';
    }
}

/* ── Submit ── */
document.getElementById('productForm').addEventListener('submit', function (e) {
    if (mainUploading) {
        e.preventDefault();
        alert('Attendez la fin de l\'upload de l\'image principale.');
        return;
    }
    if (document.querySelector('#galleryGrid [style*="Upload"]')) {
        e.preventDefault();
        alert('Attendez la fin des uploads de galerie.');
        return;
    }
    document.getElementById('galleryInput').disabled = true;
    document.getElementById('mainImageInput').disabled = true;

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="btn-spinner"></span> Enregistrement…';
});

/* ══════════════════════════════════════════════
   VARIANTES (taille/couleur/quantité)
   ══════════════════════════════════════════════ */
/* ══════════════════════════════════════════════
   VENTE FLASH
   ══════════════════════════════════════════════ */
function toggleFlashFields() {
    const on = document.getElementById('flashToggle').checked;
    document.getElementById('flashFields').style.display = on ? 'block' : 'none';
    if (!on) {
        document.getElementById('flash_price').value = '';
        document.getElementById('flash_ends_at').value = '';
    }
}

function setFlashDuration(hours) {
    const d = new Date(Date.now() + hours * 3600 * 1000);
    d.setSeconds(0, 0);
    const pad = (n) => String(n).padStart(2, '0');
    const local = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    document.getElementById('flash_ends_at').value = local;
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('flashToggle')?.checked) {
        document.getElementById('flashFields').style.display = 'block';
    }
});

let variantIdx = 0;

function addVariantRow(data = {}) {
    const list = document.getElementById('variantsList');
    const idx  = variantIdx++;
    const row  = document.createElement('div');
    row.className = 'variant-row';

    if (data.id) {
        const hid = document.createElement('input');
        hid.type = 'hidden';
        hid.name = `variants[${idx}][id]`;
        hid.value = data.id;
        row.appendChild(hid);
    }

    // Vignette photo (optionnelle) — clic pour uploader
    const thumb = document.createElement('div');
    thumb.className = 'variant-thumb';
    thumb.title = 'Photo de cette variante (optionnel)';
    if (data.image_url) {
        const img = document.createElement('img');
        img.src = data.image_url;
        thumb.appendChild(img);
    } else {
        thumb.textContent = '📷';
    }

    const imageInput = document.createElement('input');
    imageInput.type = 'hidden';
    imageInput.name = `variants[${idx}][image]`;
    imageInput.value = data.image ?? '';

    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/jpeg,image/png,image/webp,image/gif';
    fileInput.style.display = 'none';
    fileInput.addEventListener('change', () => uploadVariantImage(fileInput, thumb, imageInput));

    thumb.addEventListener('click', () => fileInput.click());

    row.appendChild(thumb);
    row.appendChild(imageInput);
    row.appendChild(fileInput);

    const mk = (name, type, placeholder, value) => {
        const inp = document.createElement('input');
        inp.type = type;
        inp.className = 'field-input';
        inp.name = `variants[${idx}][${name}]`;
        inp.placeholder = placeholder;
        inp.value = value ?? '';
        if (type === 'number') { inp.min = '0'; inp.step = '1'; }
        return inp;
    };

    row.appendChild(mk('name', 'text', 'Rouge - M', data.name));
    row.appendChild(mk('price', 'number', '= prix produit', data.price));
    row.appendChild(mk('stock', 'number', '0', data.stock ?? 0));
    row.appendChild(mk('sku', 'text', '—', data.sku));

    const rm = document.createElement('button');
    rm.type = 'button';
    rm.className = 'variant-rm';
    rm.textContent = '✕';
    rm.onclick = () => row.remove();
    row.appendChild(rm);

    list.appendChild(row);
}

async function uploadVariantImage(fileInput, thumb, imageInput) {
    const file = fileInput.files[0];
    if (!file) return;
    if (file.size > 20 * 1024 * 1024) { alert('Image trop lourde — maximum 20 Mo.'); return; }

    thumb.innerHTML = '';
    thumb.textContent = '⏳';

    const formData = new FormData();
    formData.append('file', file);
    formData.append('folder', 'products/gallery');

    try {
        const res = await fetch('{{ route('products.upload.image') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData,
        });
        const data = await res.json();
        imageInput.value = data.path;
        thumb.innerHTML = '';
        const img = document.createElement('img');
        img.src = data.url;
        thumb.appendChild(img);
    } catch (e) {
        thumb.innerHTML = '';
        thumb.textContent = '📷';
        alert("Erreur lors de l'upload de la photo. Réessayez.");
    }
}

@if($isEdit)
@php
    $existingVariantsForJs = $product->variants->map(function ($v) {
        return [
            'id' => $v->id, 'name' => $v->name, 'price' => $v->price, 'stock' => $v->stock, 'sku' => $v->sku,
            'image' => $v->image, 'image_url' => $v->image_url,
        ];
    })->values();
@endphp
document.addEventListener('DOMContentLoaded', function () {
    const existingVariants = @json($existingVariantsForJs);
    existingVariants.forEach(v => addVariantRow(v));
});
@endif
</script>
@endpush

@push('styles')
@include('boutique._partials.sv-theme')
@endpush
