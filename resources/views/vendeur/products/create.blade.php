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
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --brand:     #10b981; --brand-dk: #059669;
    --brand-lt:  #d1fae5; --brand-mlt: #ecfdf5;
    --bg:        #f6f8f7; --surface:  #ffffff;
    --border:    #e2e8f0; --border-dk: #cbd5e1;
    --text:      #0f172a; --text-2:   #475569; --muted: #94a3b8;
    --danger:    #ef4444; --danger-lt: #fef2f2;
    --font:      'Plus Jakarta Sans', sans-serif;
    --mono:      'JetBrains Mono', monospace;
    --r:         14px; --r-sm: 9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
    --shadow:    0 8px 32px rgba(0,0,0,.08);
}
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }

/* ── Layout 2 colonnes ── */
.product-form-wrap {
    max-width: 1100px; margin: 0 auto;
    padding: 28px 20px 60px;
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    align-items: start;
}

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
    box-shadow: 0 0 0 3px rgba(16,185,129,.12);
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

/* ── Allergens & tags ── */
.tag-input-wrap { display: flex; flex-wrap: wrap; gap: 6px; padding: 8px 10px; border: 1.5px solid var(--border); border-radius: var(--r-sm); min-height: 42px; cursor: text; transition: border-color .15s; }
.tag-input-wrap:focus-within { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(16,185,129,.12); }
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
    box-shadow: 0 4px 14px rgba(16,185,129,.3);
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
@keyframes spin { to { transform: rotate(360deg); } }
.upload-spin { display: inline-block; animation: spin .8s linear infinite; font-size: 28px; }
.upload-uploading {
    position: absolute; inset: 0; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: var(--brand-mlt); gap: 8px; z-index: 2;
}
.upload-uploading-txt { font-size: 12px; font-weight: 700; color: var(--brand-dk); }

/* ── Responsive ── */
@media (max-width: 900px) {
    .product-form-wrap { grid-template-columns: 1fr; }
    .sidebar-col { display: none; }
    .field-row-3 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 560px) {
    .product-form-wrap { padding: 14px 12px 40px; }
    .field-row, .field-row-3 { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="product-form-wrap">

    {{-- ═══ COLONNE PRINCIPALE ═══ --}}
    <div class="main-col">

        {{-- Breadcrumb --}}
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:12.5px;color:var(--muted)">
            <a href="{{ route('products.index') }}" style="color:var(--muted);text-decoration:none;font-weight:600">
                ← Catalogue
            </a>
            <span>/</span>
            <span style="color:var(--text);font-weight:600">
                {{ $isEdit ? 'Modifier · ' . Str::limit($product->name, 30) : 'Ajouter un produit' }}
            </span>
        </div>

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

        <form action="{{ $isEdit ? route('products.update', $product) : route('products.store') }}"
              method="POST"
              enctype="multipart/form-data"
              id="productForm">
            @csrf
            @if($isEdit) @method('PUT') @endif

            {{-- ── CARD 1 : Informations principales ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico">🏷️</div>
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
                        <label class="field-lbl" for="description">Description</label>
                        <textarea name="description" id="description"
                                  class="field-textarea"
                                  rows="3"
                                  placeholder="Décrivez votre produit : ingrédients, matière, caractéristiques…">{{ old('description', $isEdit ? $product->description : '') }}</textarea>
                        <div class="field-hint">Max 2000 caractères. Une bonne description augmente les ventes.</div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label class="field-lbl" for="category">Catégorie</label>
                            @php
                                $currentCat = old('category', $isEdit ? $product->category : '');
                                $isCustomCat = $currentCat && !in_array($currentCat, $categories);
                            @endphp

                            {{-- Champ réel envoyé au serveur --}}
                            <input type="hidden" name="category" id="categoryHidden" value="{{ $currentCat }}">

                            {{-- Sélecteur liste --}}
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
                                    @if($isCustomCat)
                                    <option value="{{ $currentCat }}" selected>{{ $currentCat }}</option>
                                    @endif
                                    <option value="__custom__">➕ Saisir une catégorie…</option>
                                </select>
                            </div>

                            {{-- Champ texte libre (masqué par défaut) --}}
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

            {{-- ── CARD 2 : Prix & Stock ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico">💰</div>
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

            {{-- ── CARD 3 : Images ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico">🖼️</div>
                    <span class="form-card-title">Photos du produit</span>
                    <span class="form-card-sub">Max 20 Mo par image</span>
                </div>
                <div class="form-card-body">

                    <div class="field-row" style="align-items:start">
                        {{-- Image principale --}}
                        <div class="field">
                            <label class="field-lbl">Photo principale</label>
                            {{-- Picker caché — déclenché par clic, upload AJAX immédiat ── --}}
                            <input type="file" id="mainImageInput"
                                   class="upload-main-input" accept="image/*">
                            {{-- Chemin retourné par l'AJAX (c'est ce qui est soumis) --}}
                            <input type="hidden" id="mainImageUploaded" name="image_uploaded" value="">

                            {{-- Zone cliquable ── --}}
                            <div class="upload-main" id="mainUploadZone"
                                 onclick="document.getElementById('mainImageInput').click()">
                                <img id="mainPreview" class="upload-main-preview" src="" alt="Aperçu">
                                <div class="upload-main-overlay">📷 Changer l'image</div>
                                <div class="upload-placeholder" id="mainPlaceholder">
                                    <span class="upload-placeholder-ico">📷</span>
                                    <div class="upload-placeholder-txt">Photo principale</div>
                                    <div class="upload-placeholder-hint">JPG · PNG · WEBP — Max 2 Mo</div>
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
                                {{-- Bouton ➕ pour ajouter des photos --}}
                                <div class="gallery-slot" id="galleryAddBtn"
                                     onclick="document.getElementById('galleryInput').click()"
                                     title="Ajouter une photo">
                                    ➕
                                </div>
                            </div>
                            {{-- Picker caché SANS name — les fichiers vont dans des inputs dynamiques --}}
                            <input type="file" id="galleryInput" accept="image/*" multiple style="display:none">
                            <div class="field-hint" style="margin-top:8px">
                                ✕ pour supprimer · ➕ pour ajouter · max 20 photos.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── CARD 4 : Options avancées ── --}}
            <div class="form-card">
                <div class="form-card-hd">
                    <div class="form-card-hd-ico">⚙️</div>
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
                    <div class="form-card-hd-ico">⚠️</div>
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
                <a href="{{ route('products.index') }}" class="btn-cancel">← Retour</a>
                <button type="submit" class="btn-submit" id="submitBtn">
                    {{ $isEdit ? '💾 Enregistrer les modifications' : '🚀 Ajouter le produit' }}
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
                    <div class="form-card-hd-ico">👁️</div>
                    <span class="form-card-title">Aperçu client</span>
                </div>
                <div class="preview-card-product">
                    <div class="preview-card-img" id="previewImgWrap">
                        <img id="previewImgEl" src="" alt="">
                        <span id="previewImgPlaceholder">🏷️</span>
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
                    <div class="form-card-hd-ico">💡</div>
                    <span class="form-card-title">Conseils de vente</span>
                </div>
                <div class="form-card-body" style="padding:16px 18px;display:flex;flex-direction:column;gap:12px">
                    @foreach([
                        ['📸', 'Bonne photo', 'Une photo nette augmente les ventes de 3×.'],
                        ['✍️', 'Description claire', 'Mentionnez les ingrédients, taille, matière.'],
                        ['💰', 'Prix juste', 'Comparez avec le marché local.'],
                        ['⭐', 'Mettez en vedette', 'Les produits vedettes sont vus en premier.'],
                    ] as [$ico, $t, $d])
                    <div style="display:flex;gap:10px;align-items:flex-start">
                        <span style="font-size:18px;flex-shrink:0">{{ $ico }}</span>
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

    mainPreview.classList.remove('visible');
    mainRemoveBtn.classList.remove('visible');
    mainPlaceholder.style.display = 'none';
    mainUploadZone.style.pointerEvents = 'none';

    const spinner = document.createElement('div');
    spinner.className = 'upload-uploading';
    spinner.innerHTML = `<span class="upload-spin">⏳</span>
                         <div class="upload-uploading-txt">Upload en cours…</div>`;
    mainUploadZone.appendChild(spinner);

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
        showMainPreview(json.url);

    } catch (err) {
        mainPlaceholder.style.display = '';
        alert(`Erreur upload image : ${err.message}`);
    } finally {
        spinner.remove();
        mainUploadZone.style.pointerEvents = '';
        mainUploading = false;
    }
}

function showMainPreview(url) {
    mainPreview.src = url;
    mainPreview.classList.add('visible');
    mainPlaceholder.style.display = 'none';
    mainRemoveBtn.classList.add('visible');
    if (previewImgEl) {
        previewImgEl.src = url;
        previewImgEl.style.display = 'block';
        if (previewImgPlaceholder) previewImgPlaceholder.style.display = 'none';
    }
}

function removeMainImage() {
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
    document.getElementById('previewName').textContent = e.target.value || 'Nom du produit';
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
    }
}

function onCustomCategoryInput(val) {
    /* Synchroniser en temps réel vers le champ caché + preview */
    document.getElementById('categoryHidden').value = val;
    document.getElementById('previewCat').textContent = val;
}

function cancelCustomCategory() {
    /* Revenir à la liste */
    document.getElementById('categoryCustomWrap').style.display = 'none';
    document.getElementById('categoryCustomInput').value = '';
    document.getElementById('categoryHidden').value = '';
    document.getElementById('categorySelect').value = '';
    document.getElementById('previewCat').textContent = '';
}
document.getElementById('price').addEventListener('input', e => {
    document.getElementById('previewPrice').innerHTML =
        previewFmt(e.target.value) +
        ' <span style="font-size:11px;font-weight:600;color:var(--muted)">{{ $devise }}</span>';
});
document.getElementById('original_price').addEventListener('input', e => {
    const el = document.getElementById('previewOrig');
    if (e.target.value) {
        el.textContent = previewFmt(e.target.value);
        el.style.display = '';
    } else {
        el.style.display = 'none';
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
    btn.innerHTML = '⏳ Enregistrement…';
});
</script>
@endpush