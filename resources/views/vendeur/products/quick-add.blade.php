{{--
    resources/views/vendeur/products/quick-add.blade.php
    Ajout rapide de plusieurs produits en une fois — chacun garde sa propre
    galerie de photos et son propre prix, sans jamais se mélanger entre eux.
    Variables : $categories, $devise, $isPro, $remainingSlots
--}}
@extends('layouts.app')
@section('title', 'Ajout rapide de produits')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after { box-sizing:border-box; }
:root {
    --brand:#6366f1; --brand-dk:#4f46e5; --brand-lt:#e0e7ff; --brand-mlt:#eef2ff;
    --bg:#f1f5f9; --surface:#fff; --border:#e2e8f0; --border-dk:#cbd5e1;
    --text:#0f172a; --text2:#475569; --muted:#94a3b8;
    --green:#10b981; --red:#ef4444; --amber:#f59e0b;
    --font:'Segoe UI',system-ui,sans-serif; --r:14px; --r-sm:9px;
}
body { margin:0; font-family:var(--font); background:var(--bg); color:var(--text); }
a { text-decoration:none; color:inherit; }

.qa-wrap { max-width:960px; margin:0 auto; padding:24px 20px 100px; }
.qa-back { display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:700; color:var(--text2); margin-bottom:14px; }
.qa-back:hover { color:var(--brand); }
.qa-head { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; margin-bottom:18px; }
.qa-title { font-size:22px; font-weight:900; color:var(--text); letter-spacing:-.4px; margin:0 0 4px; }
.qa-sub { font-size:13px; color:var(--text2); }

.qa-banner { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:var(--r-sm); font-size:13px; font-weight:600; margin-bottom:18px; }
.qa-banner.info    { background:var(--brand-mlt); border:1px solid var(--brand-lt); color:var(--brand-dk); }
.qa-banner.warn     { background:#fffbeb; border:1px solid #fde68a; color:#92400e; }
.qa-banner.error    { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }
.qa-banner.success  { background:#f0fdf4; border:1px solid #86efac; color:#15803d; }

.qa-block { background:var(--surface); border:1.5px solid var(--border); border-radius:var(--r); margin-bottom:18px; overflow:hidden; }
.qa-block-hd { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; background:#f8fafc; border-bottom:1px solid var(--border); }
.qa-block-num { font-size:12.5px; font-weight:800; color:var(--brand-dk); background:var(--brand-mlt); padding:3px 12px; border-radius:20px; }
.qa-block-remove { background:none; border:none; color:var(--muted); font-size:13px; font-weight:700; cursor:pointer; padding:4px 8px; border-radius:6px; }
.qa-block-remove:hover { background:#fef2f2; color:var(--red); }
.qa-block-body { padding:18px; display:grid; grid-template-columns:150px 1fr; gap:18px; }

.qa-field { display:flex; flex-direction:column; gap:5px; margin-bottom:12px; }
.qa-label { font-size:11px; font-weight:700; color:var(--text2); text-transform:uppercase; letter-spacing:.4px; }
.qa-input, .qa-select { width:100%; padding:9px 12px; border:1.5px solid var(--border); border-radius:var(--r-sm); font-size:13.5px; font-family:var(--font); color:var(--text); background:var(--bg); outline:none; }
.qa-input:focus, .qa-select:focus { border-color:var(--brand); background:#fff; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
.qa-row2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

.qa-main-upload { width:150px; height:150px; border:2px dashed var(--border-dk); border-radius:var(--r-sm); display:flex; align-items:center; justify-content:center; flex-direction:column; gap:4px; cursor:pointer; position:relative; overflow:hidden; background:var(--bg); flex-shrink:0; }
.qa-main-upload img { position:absolute; inset:0; width:100%; height:100%; object-fit:contain; background:#fff; display:none; }
.qa-main-upload.has-img img { display:block; }
.qa-main-upload.has-img .qa-ph { display:none; }
.qa-ph { font-size:11px; color:var(--muted); text-align:center; padding:0 8px; }
.qa-ph-ico { font-size:22px; }
.qa-main-remove { position:absolute; top:4px; right:4px; width:22px; height:22px; border-radius:50%; background:rgba(0,0,0,.55); color:#fff; border:none; font-size:12px; cursor:pointer; display:none; align-items:center; justify-content:center; z-index:2; }
.qa-main-upload.has-img .qa-main-remove { display:flex; }

.qa-gallery { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
.qa-gallery-thumb { width:56px; height:56px; border-radius:8px; overflow:hidden; position:relative; border:1.5px solid var(--border); flex-shrink:0; }
.qa-gallery-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.qa-gallery-thumb button { position:absolute; top:1px; right:1px; width:16px; height:16px; border-radius:50%; background:rgba(0,0,0,.6); color:#fff; border:none; font-size:9px; cursor:pointer; line-height:1; }
.qa-gallery-add { width:56px; height:56px; border-radius:8px; border:1.5px dashed var(--border-dk); display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:18px; color:var(--muted); flex-shrink:0; background:var(--bg); }
.qa-uploading { font-size:10px; color:var(--brand); font-weight:700; }

.qa-actions { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-top:6px; }
.qa-btn { display:inline-flex; align-items:center; gap:7px; padding:11px 20px; border-radius:var(--r-sm); font-size:13px; font-weight:700; font-family:var(--font); cursor:pointer; border:none; transition:all .15s; }
.qa-btn-ghost { background:#fff; color:var(--text2); border:1.5px solid var(--border-dk); }
.qa-btn-ghost:hover { border-color:var(--brand); color:var(--brand); background:var(--brand-mlt); }
.qa-btn-primary { background:var(--brand); color:#fff; }
.qa-btn-primary:hover { background:var(--brand-dk); }
.qa-btn-primary:disabled { opacity:.55; cursor:not-allowed; }
.qa-submit-bar { position:sticky; bottom:0; background:var(--surface); border-top:1px solid var(--border); padding:14px 18px; margin:24px -20px -100px; display:flex; justify-content:flex-end; gap:10px; }

@media(max-width:600px) {
    .qa-block-body { grid-template-columns:1fr; }
    .qa-main-upload { width:100%; height:220px; }
    .qa-row2 { grid-template-columns:1fr; }
    .qa-submit-bar { flex-direction:column-reverse; }
    .qa-submit-bar .qa-btn { width:100%; justify-content:center; }
}
</style>
@endpush

@section('content')
<div class="qa-wrap">
    <a href="{{ route('products.index') }}" class="qa-back">← Retour aux produits</a>

    <div class="qa-head">
        <div>
            <h1 class="qa-title">⚡ Ajout rapide de produits</h1>
            <div class="qa-sub">Ajoutez plusieurs produits d'un coup — chacun garde sa propre galerie et son propre prix.</div>
        </div>
    </div>

    @if(session('plan_error'))
    <div class="qa-banner error">⚠ {{ session('plan_error') }}</div>
    @endif
    @if($errors->any())
    <div class="qa-banner error">⚠ {{ $errors->first() }}</div>
    @endif

    @if(!$isPro)
    <div class="qa-banner {{ $remainingSlots > 0 ? 'warn' : 'error' }}">
        {{ $remainingSlots > 0 ? "🔓 Plan Gratuit : vous pouvez encore ajouter {$remainingSlots} produit(s) (max 5 au total)." : "🔒 Limite du Plan Gratuit atteinte (5 produits max)." }}
        <a href="{{ route('boutique.subscription.upgrade') }}" style="margin-left:auto;font-weight:800;color:var(--brand-dk)">Passer au Plan Pro →</a>
    </div>
    @endif

    <form id="quickAddForm" method="POST" action="{{ route('products.quick-add.store') }}">
        @csrf
        <div id="productBlocks"></div>

        <div class="qa-actions">
            <button type="button" class="qa-btn qa-btn-ghost" id="addBlockBtn" onclick="addProductBlock()">
                ➕ Ajouter un autre produit
            </button>
            <span style="font-size:12px;color:var(--muted)" id="blockCountLbl"></span>
        </div>

        <div class="qa-submit-bar">
            <a href="{{ route('products.index') }}" class="qa-btn qa-btn-ghost">Annuler</a>
            <button type="submit" class="qa-btn qa-btn-primary" id="submitBtn">✓ Enregistrer tous les produits</button>
        </div>
    </form>
</div>

{{-- ── TEMPLATE d'un bloc produit (cloné en JS) ── --}}
<template id="blockTemplate">
    <div class="qa-block" data-block>
        <div class="qa-block-hd">
            <span class="qa-block-num" data-block-num>Produit 1</span>
            <button type="button" class="qa-block-remove" onclick="removeBlock(this)">✕ Retirer</button>
        </div>
        <div class="qa-block-body">
            <div>
                <div class="qa-main-upload" data-main-upload>
                    <input type="file" accept="image/*" style="display:none" data-main-input>
                    <input type="hidden" data-main-hidden>
                    <img data-main-preview src="" alt="">
                    <button type="button" class="qa-main-remove" data-main-remove>✕</button>
                    <div class="qa-ph">
                        <div class="qa-ph-ico">📷</div>
                        Photo principale
                    </div>
                </div>
                <div class="qa-gallery" data-gallery>
                    <div class="qa-gallery-add" data-gallery-add title="Ajouter des photos">➕</div>
                    <input type="file" accept="image/*" multiple style="display:none" data-gallery-input>
                </div>
            </div>
            <div>
                <div class="qa-field">
                    <label class="qa-label">Nom du produit *</label>
                    <input type="text" class="qa-input" placeholder="Ex : iPhone 14 — Batterie" data-field="name" required>
                </div>
                <div class="qa-row2">
                    <div class="qa-field">
                        <label class="qa-label">Prix ({{ $devise }}) *</label>
                        <input type="number" min="0" step="1" class="qa-input" placeholder="0" data-field="price" required>
                    </div>
                    <div class="qa-field">
                        <label class="qa-label">Stock</label>
                        <input type="number" min="0" step="1" class="qa-input" placeholder="0" data-field="stock">
                    </div>
                </div>
                <div class="qa-field">
                    <label class="qa-label">Catégorie</label>
                    <input type="hidden" data-field="category" data-cat-hidden>
                    <select class="qa-select" data-cat-select>
                        <option value="">— Aucune —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                        @foreach($customCats ?? [] as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                        <option value="__custom__">➕ Saisir une catégorie…</option>
                    </select>
                    <div data-cat-custom-wrap style="display:none;gap:8px;align-items:center;margin-top:8px">
                        <input type="text" class="qa-input" data-cat-custom-input placeholder="Ex : Produits locaux, Artisanat…">
                        <button type="button" data-cat-custom-cancel class="qa-btn qa-btn-ghost" style="padding:8px 12px;font-size:12px">✕</button>
                    </div>
                </div>
                <div class="qa-field">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2px">
                        <label class="qa-label" style="margin-bottom:0">Description</label>
                        @if($isPro)
                        <button type="button" class="qa-ia-btn" data-ia-btn
                            style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;border:1.5px solid #6366f1;background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:#4f46e5;font-size:11.5px;font-weight:700;cursor:pointer;font-family:var(--font)">
                            ✨ Shopio IA
                        </button>
                        @else
                        <a href="{{ route('boutique.subscription.upgrade') }}"
                            style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;border:1.5px solid #d1d5db;background:#f9fafb;color:#9ca3af;font-size:11.5px;font-weight:700;white-space:nowrap"
                            title="Fonctionnalité réservée au plan Pro">
                            🔒 Shopio IA <span style="font-size:9px;background:#f59e0b;color:#fff;padding:1px 5px;border-radius:10px;font-weight:800">PRO</span>
                        </a>
                        @endif
                    </div>
                    <textarea class="qa-input" rows="3" style="resize:vertical;font-family:var(--font)"
                              placeholder="Décrivez le produit…" data-field="description" data-ia-textarea></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
let qaIndex = 0;
const uploadUrl = "{{ route('products.upload.image') }}";
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function updateBlockCount() {
    const n = document.querySelectorAll('#productBlocks [data-block]').length;
    document.getElementById('blockCountLbl').textContent = n + ' produit(s) prêt(s) à enregistrer';
    document.getElementById('submitBtn').textContent = n > 1
        ? `✓ Enregistrer les ${n} produits`
        : '✓ Enregistrer le produit';
}

function renumberBlocks() {
    document.querySelectorAll('#productBlocks [data-block]').forEach((block, i) => {
        block.querySelector('[data-block-num]').textContent = 'Produit ' + (i + 1);
    });
}

async function uploadFile(file, folder) {
    const fd = new FormData();
    fd.append('file', file);
    fd.append('folder', folder);
    const res = await fetch(uploadUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: fd,
    });
    if (!res.ok) throw new Error('upload failed');
    return res.json(); // { path, url }
}

function wireBlock(block) {
    const idx = qaIndex++;

    // Champs texte simples
    block.querySelectorAll('[data-field]').forEach(input => {
        const field = input.dataset.field;
        input.name = `products[${idx}][${field}]`;
    });

    // Catégorie : liste + saisie libre ("➕ Saisir une catégorie…")
    const catHidden      = block.querySelector('[data-cat-hidden]');
    const catSelect      = block.querySelector('[data-cat-select]');
    const catCustomWrap  = block.querySelector('[data-cat-custom-wrap]');
    const catCustomInput = block.querySelector('[data-cat-custom-input]');
    const catCustomCancel = block.querySelector('[data-cat-custom-cancel]');

    catSelect.addEventListener('change', () => {
        if (catSelect.value === '__custom__') {
            catCustomWrap.style.display = 'flex';
            catHidden.value = '';
            catCustomInput.value = '';
            catCustomInput.focus();
        } else {
            catCustomWrap.style.display = 'none';
            catHidden.value = catSelect.value;
        }
    });
    catCustomInput.addEventListener('input', () => {
        catHidden.value = catCustomInput.value.trim();
    });
    catCustomCancel.addEventListener('click', () => {
        catCustomWrap.style.display = 'none';
        catCustomInput.value = '';
        catHidden.value = '';
        catSelect.value = '';
    });

    // Photo principale
    const mainUpload  = block.querySelector('[data-main-upload]');
    const mainInput   = block.querySelector('[data-main-input]');
    const mainHidden  = block.querySelector('[data-main-hidden]');
    const mainPreview = block.querySelector('[data-main-preview]');
    const mainRemove  = block.querySelector('[data-main-remove]');
    mainHidden.name = `products[${idx}][image_uploaded]`;

    mainUpload.addEventListener('click', (e) => {
        if (e.target === mainRemove) return;
        mainInput.click();
    });
    mainInput.addEventListener('change', async () => {
        const file = mainInput.files[0];
        if (!file) return;
        mainUpload.classList.add('has-img');
        mainPreview.src = URL.createObjectURL(file);
        try {
            const { path, url } = await uploadFile(file, 'products');
            mainHidden.value = path;
            mainPreview.src = url;
        } catch (e) {
            alert('Échec de l\'envoi de la photo principale, réessayez.');
            mainUpload.classList.remove('has-img');
        }
    });
    mainRemove.addEventListener('click', (e) => {
        e.stopPropagation();
        mainHidden.value = '';
        mainInput.value = '';
        mainUpload.classList.remove('has-img');
    });

    // Galerie
    const galleryWrap  = block.querySelector('[data-gallery]');
    const galleryAdd   = block.querySelector('[data-gallery-add]');
    const galleryInput = block.querySelector('[data-gallery-input]');
    let galleryCount = 0;

    galleryAdd.addEventListener('click', () => galleryInput.click());
    galleryInput.addEventListener('change', async () => {
        const files = Array.from(galleryInput.files);
        for (const file of files) {
            if (galleryCount >= 20) break;
            const thumb = document.createElement('div');
            thumb.className = 'qa-gallery-thumb';
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `products[${idx}][gallery_uploaded][]`;
            const rm = document.createElement('button');
            rm.type = 'button';
            rm.textContent = '✕';
            rm.onclick = () => thumb.remove();
            thumb.append(img, hidden, rm);
            galleryWrap.insertBefore(thumb, galleryAdd);
            galleryCount++;

            try {
                const { path, url } = await uploadFile(file, 'products/gallery');
                hidden.value = path;
                img.src = url;
            } catch (e) {
                thumb.remove();
                galleryCount--;
            }
        }
        galleryInput.value = '';
    });

    // Shopio IA — génère la description à partir du nom/prix/photo de ce bloc précis
    const iaBtn = block.querySelector('[data-ia-btn]');
    if (iaBtn) {
        iaBtn.addEventListener('click', async () => {
            const nameInput  = block.querySelector('[data-field="name"]');
            const priceInput = block.querySelector('[data-field="price"]');
            const textarea   = block.querySelector('[data-ia-textarea]');
            const name  = nameInput.value.trim();
            const price = priceInput.value;

            let imageBase64 = null;
            if (mainUpload.classList.contains('has-img') && mainPreview.src) {
                try {
                    const imgRes = await fetch(mainPreview.src);
                    const blob   = await imgRes.blob();
                    imageBase64  = await new Promise(resolve => {
                        const reader = new FileReader();
                        reader.onload = e => resolve(e.target.result);
                        reader.readAsDataURL(blob);
                    });
                } catch (e) { imageBase64 = null; }
            }

            if (!name && !imageBase64) {
                alert('Ajoutez au moins une photo ou un nom de produit.');
                return;
            }

            iaBtn.disabled = true;
            const originalLabel = iaBtn.innerHTML;
            iaBtn.innerHTML = '⏳ Génération…';

            try {
                const res = await fetch('{{ route("products.ai.description") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ name, price: price || null, image: imageBase64 }),
                });
                const data = await res.json();
                if (data.description) {
                    textarea.value = data.description;
                    textarea.style.borderColor = '#6366f1';
                    setTimeout(() => { textarea.style.borderColor = ''; }, 2000);
                } else {
                    alert(data.error || 'Erreur Shopio IA. Réessayez.');
                }
            } catch (e) {
                alert('Erreur Shopio IA. Réessayez.');
            } finally {
                iaBtn.disabled = false;
                iaBtn.innerHTML = originalLabel;
            }
        });
    }
}

function addProductBlock() {
    const tpl = document.getElementById('blockTemplate');
    const clone = tpl.content.cloneNode(true);
    const block = clone.querySelector('[data-block]');
    document.getElementById('productBlocks').appendChild(clone);
    wireBlock(block);
    renumberBlocks();
    updateBlockCount();
}

function removeBlock(btn) {
    const blocks = document.querySelectorAll('#productBlocks [data-block]');
    if (blocks.length <= 1) {
        alert('Il doit rester au moins un produit. Utilisez "Annuler" pour quitter sans enregistrer.');
        return;
    }
    btn.closest('[data-block]').remove();
    renumberBlocks();
    updateBlockCount();
}

document.getElementById('quickAddForm').addEventListener('submit', function (e) {
    const blocks = document.querySelectorAll('#productBlocks [data-block]');
    if (blocks.length === 0) {
        e.preventDefault();
        alert('Ajoutez au moins un produit.');
        return;
    }
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').textContent = '⏳ Enregistrement en cours…';
});

// Premier bloc au chargement
addProductBlock();
</script>
@endpush
