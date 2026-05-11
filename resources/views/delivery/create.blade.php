@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp
@push('styles')
<style>
/* ── Hero banner ── */
.dc-hero {
    background: linear-gradient(135deg, #0f2a5e 0%, #1a4a9e 50%, #2563eb 100%);
    padding: 4rem 1.5rem 6rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.dc-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.dc-hero .hero-icon {
    width: 72px;
    height: 72px;
    background: rgba(255,255,255,.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
    font-size: 2rem;
    backdrop-filter: blur(8px);
    border: 2px solid rgba(255,255,255,.2);
}
.dc-hero h1 {
    font-size: clamp(1.5rem, 4vw, 2.2rem);
    font-weight: 700;
    color: #fff;
    margin-bottom: .6rem;
    letter-spacing: -.02em;
}
.dc-hero p {
    font-size: 1rem;
    color: rgba(255,255,255,.8);
    max-width: 520px;
    margin: 0 auto;
    line-height: 1.6;
}
.dc-hero .steps-badges {
    display: flex;
    justify-content: center;
    gap: .75rem;
    margin-top: 1.75rem;
    flex-wrap: wrap;
}
.dc-hero .step-badge {
    display: flex;
    align-items: center;
    gap: .45rem;
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 2rem;
    padding: .35rem .85rem;
    font-size: .82rem;
    color: rgba(255,255,255,.95);
    font-weight: 500;
}
.dc-hero .step-badge .dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #34d399;
    flex-shrink: 0;
}

/* ── Card wrapper ── */
.dc-wrap {
    max-width: 860px;
    margin: -3.5rem auto 3rem;
    padding: 0 1rem;
    position: relative;
    z-index: 2;
}
.dc-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(11,45,90,.12), 0 4px 16px rgba(11,45,90,.06);
    overflow: hidden;
}
body.cx-dashboard .dc-card {
    background: var(--cx-surface);
    color: var(--cx-text);
}

/* ── Form header ── */
.dc-form-header {
    padding: 1.75rem 2rem 1.25rem;
    border-bottom: 1px solid rgba(0,0,0,.07);
    display: flex;
    align-items: center;
    gap: 1rem;
}
body.cx-dashboard .dc-form-header {
    border-bottom-color: rgba(255,255,255,.07);
}
.dc-form-header .header-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #2563eb, #1a4a9e);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.2rem;
    color: #fff;
}
.dc-form-header h2 {
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0;
    color: #0f2a5e;
}
body.cx-dashboard .dc-form-header h2 { color: var(--cx-text); }
.dc-form-header p {
    font-size: .82rem;
    color: #6b7280;
    margin: .15rem 0 0;
}

/* ── Form body ── */
.dc-form-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem 2rem;
    padding: 1.75rem 2rem;
}
@media (max-width: 640px) {
    .dc-form-body { grid-template-columns: 1fr; gap: 1rem; padding: 1.25rem; }
    .dc-form-header { padding: 1.25rem; }
}
.dc-form-body .col-full { grid-column: 1 / -1; }

/* ── Input fields ── */
.dc-field label {
    display: block;
    font-size: .82rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: .4rem;
    letter-spacing: .01em;
}
body.cx-dashboard .dc-field label { color: var(--cx-text2); }
.dc-field .input-wrap {
    position: relative;
}
.dc-field .input-wrap .field-icon {
    position: absolute;
    left: .85rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: .95rem;
    pointer-events: none;
}
.dc-field input, .dc-field textarea {
    width: 100%;
    padding: .65rem .9rem .65rem 2.4rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: .92rem;
    background: #f9fafb;
    color: #111827;
    transition: border-color .2s, box-shadow .2s, background .2s;
    outline: none;
}
.dc-field textarea {
    padding-left: 2.4rem;
    resize: vertical;
    min-height: 90px;
}
.dc-field input:focus, .dc-field textarea:focus {
    border-color: #2563eb;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(37,99,235,.12);
}
body.cx-dashboard .dc-field input,
body.cx-dashboard .dc-field textarea {
    background: var(--cx-bg);
    border-color: rgba(255,255,255,.12);
    color: var(--cx-text);
}
body.cx-dashboard .dc-field input:focus,
body.cx-dashboard .dc-field textarea:focus {
    background: var(--cx-surface);
    border-color: #3b82f6;
}
.dc-field .is-invalid { border-color: #ef4444 !important; }
.dc-field .invalid-feedback { font-size: .78rem; color: #ef4444; margin-top: .3rem; }

/* ── Image upload ── */
.image-upload-zone {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    position: relative;
    overflow: hidden;
}
.image-upload-zone:hover { border-color: #2563eb; background: #f0f5ff; }
body.cx-dashboard .image-upload-zone { border-color: rgba(255,255,255,.15); background: var(--cx-bg); }
body.cx-dashboard .image-upload-zone:hover { border-color: #3b82f6; background: rgba(59,130,246,.08); }
.image-upload-zone input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.image-upload-zone .upload-placeholder { pointer-events: none; }
.image-upload-zone .upload-icon { font-size: 2rem; margin-bottom: .5rem; color: #9ca3af; }
.image-upload-zone .upload-text { font-size: .85rem; color: #6b7280; }
.image-upload-zone .upload-text strong { color: #2563eb; }
#imgPreviewWrap { display: none; position: relative; }
#imgPreviewWrap img {
    width: 100%; max-height: 160px; object-fit: cover; border-radius: 8px;
}
#imgPreviewWrap .remove-img {
    position: absolute; top: .4rem; right: .4rem;
    background: rgba(239,68,68,.9); color: #fff;
    border: none; border-radius: 50%; width: 26px; height: 26px;
    font-size: .9rem; cursor: pointer; display: flex; align-items: center; justify-content: center;
    line-height: 1;
}

/* ── Info notice ── */
.dc-notice {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: .85rem 1rem;
    font-size: .83rem;
    color: #1e40af;
    line-height: 1.5;
}
body.cx-dashboard .dc-notice { background: rgba(37,99,235,.1); border-color: rgba(37,99,235,.3); color: #93c5fd; }
.dc-notice .notice-icon { font-size: 1rem; flex-shrink: 0; margin-top: .05rem; }

/* ── Footer actions ── */
.dc-form-footer {
    padding: 1.25rem 2rem 1.75rem;
    border-top: 1px solid rgba(0,0,0,.07);
}
body.cx-dashboard .dc-form-footer { border-top-color: rgba(255,255,255,.07); }
.btn-create {
    width: 100%;
    padding: .85rem 1.5rem;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    font-size: .98rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s, opacity .15s;
    box-shadow: 0 4px 14px rgba(37,99,235,.35);
    letter-spacing: .01em;
}
.btn-create:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,.45); }
.btn-create:active { transform: translateY(0); }
.btn-create:disabled { opacity: .6; cursor: not-allowed; transform: none; }

/* ── Why us cards ── */
.dc-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    max-width: 860px;
    margin: 0 auto 3rem;
    padding: 0 1rem;
}
@media (max-width: 640px) {
    .dc-features { grid-template-columns: 1fr; }
}
.dc-feature-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.25rem;
    box-shadow: 0 2px 12px rgba(11,45,90,.06);
    display: flex;
    align-items: flex-start;
    gap: .85rem;
}
body.cx-dashboard .dc-feature-card { background: var(--cx-surface); }
.dc-feature-card .feat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.dc-feature-card h4 { font-size: .88rem; font-weight: 700; margin: 0 0 .2rem; color: #111827; }
body.cx-dashboard .dc-feature-card h4 { color: var(--cx-text); }
.dc-feature-card p { font-size: .78rem; color: #6b7280; margin: 0; line-height: 1.5; }
</style>
@endpush

@section('content')

{{-- ── Hero Banner ── --}}
<div class="dc-hero">
    <div class="hero-icon">🚚</div>
    <h1>Créez votre entreprise de livraison</h1>
    <p>Rejoignez notre réseau et commencez à gérer vos livraisons dès aujourd'hui. Votre entreprise sera visible par les boutiques de votre pays.</p>
    <div class="steps-badges">
        <div class="step-badge"><span class="dot"></span> Remplissez le formulaire</div>
        <div class="step-badge"><span class="dot" style="background:#f59e0b"></span> Approbation administrateur</div>
        <div class="step-badge"><span class="dot" style="background:#3b82f6"></span> Démarrez vos livraisons</div>
    </div>
</div>

{{-- ── Form Card ── --}}
<div class="dc-wrap">
    <div class="dc-card">

        {{-- Header --}}
        <div class="dc-form-header">
            <div class="header-icon">🏢</div>
            <div>
                <h2>Informations de votre entreprise</h2>
                <p>Tous les champs marqués * sont obligatoires</p>
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('delivery.company.store') }}" method="POST" enctype="multipart/form-data" id="createForm">
            @csrf

            <div class="dc-form-body">

                {{-- Nom --}}
                <div class="dc-field col-full">
                    <label for="name">Nom de l'entreprise *</label>
                    <div class="input-wrap">
                        <span class="field-icon">🏢</span>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               placeholder="Ex: Express Livraison Conakry"
                               class="@error('name') is-invalid @enderror" required>
                    </div>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Description --}}
                <div class="dc-field col-full">
                    <label for="description">Description</label>
                    <div class="input-wrap">
                        <span class="field-icon" style="top:1rem;transform:none">📝</span>
                        <textarea id="description" name="description"
                                  placeholder="Décrivez votre entreprise, vos zones de couverture...">{{ old('description') }}</textarea>
                    </div>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Téléphone --}}
                <div class="dc-field">
                    <label for="phone">Téléphone</label>
                    <div class="input-wrap">
                        <span class="field-icon">📞</span>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               placeholder="+224 6xx xxx xxx"
                               class="@error('phone') is-invalid @enderror">
                    </div>
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Email --}}
                <div class="dc-field">
                    <label for="email">Email professionnel</label>
                    <div class="input-wrap">
                        <span class="field-icon">✉️</span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               placeholder="contact@monentreprise.com"
                               class="@error('email') is-invalid @enderror">
                    </div>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Adresse --}}
                <div class="dc-field col-full">
                    <label for="address">Adresse / Quartier</label>
                    <div class="input-wrap">
                        <span class="field-icon">📍</span>
                        <input type="text" id="address" name="address" value="{{ old('address') }}"
                               placeholder="Rue, quartier, ville">
                    </div>
                </div>

                {{-- Image --}}
                <div class="dc-field col-full">
                    <label>Logo / Photo de l'entreprise</label>
                    <div class="image-upload-zone" id="uploadZone">
                        <input type="file" name="image" id="imageInput" accept="image/*">
                        <div class="upload-placeholder" id="uploadPlaceholder">
                            <div class="upload-icon">🖼️</div>
                            <div class="upload-text">
                                <strong>Cliquez pour choisir</strong> ou glissez votre image ici<br>
                                <span style="font-size:.78rem;color:#9ca3af">JPG, PNG, WEBP — max 4 Mo</span>
                            </div>
                        </div>
                        <div id="imgPreviewWrap">
                            <img id="imgPreview" src="" alt="Aperçu">
                            <button type="button" class="remove-img" id="removeImg" title="Supprimer">✕</button>
                        </div>
                    </div>
                    @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Notice --}}
                <div class="dc-notice col-full">
                    <span class="notice-icon">ℹ️</span>
                    <span>Après soumission, un administrateur examinera votre dossier. Vous aurez accès à votre tableau de bord en attendant l'approbation. Votre entreprise sera visible uniquement par les boutiques de votre pays.</span>
                </div>

            </div>

            {{-- Footer --}}
            <div class="dc-form-footer">
                <button type="submit" class="btn-create" id="submitBtn">
                    Créer mon entreprise et soumettre pour approbation
                </button>
            </div>

        </form>
    </div>
</div>

{{-- ── Feature cards ── --}}
<div class="dc-features">
    <div class="dc-feature-card">
        <div class="feat-icon" style="background:#eff6ff">📦</div>
        <div>
            <h4>Gérez vos livraisons</h4>
            <p>Tableau de bord complet pour suivre toutes vos commandes en temps réel.</p>
        </div>
    </div>
    <div class="dc-feature-card">
        <div class="feat-icon" style="background:#f0fdf4">🚴</div>
        <div>
            <h4>Ajoutez vos livreurs</h4>
            <p>Créez et gérez votre équipe de livreurs après approbation de votre compte.</p>
        </div>
    </div>
    <div class="dc-feature-card">
        <div class="feat-icon" style="background:#fefce8">💰</div>
        <div>
            <h4>Commissions claires</h4>
            <p>Suivez vos revenus et les paiements des boutiques partenaires en toute transparence.</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const imageInput = document.getElementById('imageInput');
const placeholder = document.getElementById('uploadPlaceholder');
const previewWrap = document.getElementById('imgPreviewWrap');
const previewImg  = document.getElementById('imgPreview');
const removeBtn   = document.getElementById('removeImg');
const submitBtn   = document.getElementById('submitBtn');

imageInput.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const url = URL.createObjectURL(file);
    previewImg.src = url;
    placeholder.style.display = 'none';
    previewWrap.style.display = 'block';
});

removeBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    imageInput.value = '';
    previewImg.src = '';
    previewWrap.style.display = 'none';
    placeholder.style.display = '';
});

document.getElementById('createForm').addEventListener('submit', function () {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Création en cours…';
});
</script>
@endpush
