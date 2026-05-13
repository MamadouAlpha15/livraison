@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@section('title', 'Modifier — ' . $driver->name)

@push('styles')
<script>(function(){if(localStorage.getItem('cx-theme')==='dark')document.documentElement.classList.add('cx-predark');})();</script>
<style>
html.cx-predark body{background:#0b0d22!important;color:#e2e8f0!important}</style>
<style>
    :root {
        --cx-bg:      #F5F7FA;
        --cx-card:    #ffffff;
        --cx-border:  rgba(0,0,0,.09);
        --cx-brand:   #7c3aed;
        --cx-text:    #111827;
        --cx-muted:   #6b7280;
        --cx-green:   #10b981;
        --cx-amber:   #f59e0b;
        --cx-red:     #ef4444;
    }
    body { background: var(--cx-bg); color: var(--cx-text); }

    .drv-banner {
        background: linear-gradient(135deg, #1e1b4b 0%, #5b21b6 100%);
        padding: 2.5rem 2rem 2rem; position: relative; overflow: hidden;
    }
    .drv-banner::before {
        content: ''; position: absolute; inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
        background-size: 40px 40px;
    }
    .drv-banner-inner { position: relative; }
    .drv-banner h1 { font-size: 1.75rem; font-weight: 700; margin: 0; }
    .drv-banner-sub { color: #c4b5fd; margin-top: .25rem; font-size: .9rem; }

    .drv-form-layout {
        display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem;
        padding: 1.75rem 2rem; max-width: 1100px; margin: 0 auto;
    }
    @media (max-width: 820px) {
        .drv-form-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .drv-banner { padding: 1.5rem 1.1rem 1.25rem; }
        .drv-banner h1 { font-size: 1.35rem; }
        .drv-form-layout { padding: 1rem 1rem 2rem; gap: 1rem; }
        .drv-panel-body { padding: 1rem; }
        .drv-panel-header { padding: .8rem 1rem; }
        .btn-row { flex-direction: column; }
        .stats-strip { grid-template-columns: repeat(3,1fr); gap: .4rem; }
    }
    @media (max-width: 480px) {
        .drv-banner h1 { font-size: 1.2rem; }
        .drv-banner-sub { font-size: .82rem; }
        .drv-form-layout { padding: .75rem .75rem 2rem; }
        .drv-panel-body { padding: .85rem; }
        .stats-strip { gap: .35rem; }
        .stat-pill-val { font-size: 1.1rem; }
    }
    @media (max-width: 360px) {
        .drv-banner { padding: 1.1rem .85rem 1rem; }
        .drv-banner h1 { font-size: 1.1rem; }
        .drv-form-layout { padding: .6rem .6rem 2rem; gap: .75rem; }
        .drv-panel-body { padding: .75rem; }
        .form-control { font-size: .88rem; padding: .65rem .85rem; }
        .status-grid { grid-template-columns: repeat(3,1fr); }
    }

    .drv-panel {
        background: var(--cx-card); border: 1px solid var(--cx-border);
        border-radius: 16px; overflow: hidden; margin-bottom: 1.25rem;
    }
    .drv-panel:last-child { margin-bottom: 0; }
    .drv-panel-header {
        padding: 1rem 1.4rem; border-bottom: 1px solid var(--cx-border);
        display: flex; align-items: center; gap: .65rem;
    }
    .ph-icon {
        width: 34px; height: 34px; border-radius: 8px;
        background: rgba(124,58,237,.18); display: grid; place-items: center; font-size: 1rem;
    }
    .drv-panel-header h2 { font-size: .95rem; font-weight: 600; margin: 0; }
    .drv-panel-body { padding: 1.4rem; }

    /* ── Photo actuelle ── */
    .current-photo-box {
        border-radius: 14px; overflow: hidden;
        border: 2px solid var(--cx-border);
        background: #f3f4f6;
        aspect-ratio: 1; position: relative;
        margin-bottom: .85rem;
    }
    .current-photo-box img {
        width: 100%; height: 100%; object-fit: cover; display: block;
    }
    .current-photo-badge {
        position: absolute; top: 8px; left: 8px;
        background: rgba(0,0,0,.55); backdrop-filter: blur(4px);
        border: 1px solid rgba(255,255,255,.15); border-radius: 6px;
        padding: .2rem .55rem; font-size: .7rem; color: #e2e8f0;
    }
    /* No photo placeholder */
    .no-photo-box {
        border: 2px dashed var(--cx-border); border-radius: 14px;
        aspect-ratio: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: .5rem;
        color: var(--cx-muted); background: rgba(124,58,237,.04);
        margin-bottom: .85rem;
    }
    .no-photo-box span:first-child { font-size: 2.5rem; }
    .no-photo-box span:last-child { font-size: .82rem; }

    .btn-remove-photo {
        width: 100%; background: rgba(239,68,68,.08);
        border: 1px solid rgba(239,68,68,.25); color: var(--cx-red);
        border-radius: 8px; padding: .55rem; font-size: .82rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: .35rem;
        transition: background .2s; margin-bottom: .85rem;
    }
    .btn-remove-photo:hover { background: rgba(239,68,68,.18); }

    /* Drop zone nouvelle photo */
    .photo-drop-label-sm { font-size: .78rem; color: var(--cx-muted); margin-bottom: .4rem; }
    .photo-drop {
        border: 2px dashed var(--cx-border); border-radius: 12px;
        background: #f9fafb;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .6rem; min-height: 120px; cursor: pointer;
        transition: border-color .2s, background .2s; position: relative; overflow: hidden;
    }
    .photo-drop:hover, .photo-drop.drag-over { border-color: var(--cx-brand); background: rgba(124,58,237,.08); }
    .photo-drop input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .photo-drop-icon { font-size: 1.4rem; }
    .photo-drop-label { font-size: .8rem; color: var(--cx-muted); text-align: center; line-height: 1.5; }
    .photo-drop-label strong { color: var(--cx-brand); }
    .photo-drop-hint { font-size: .7rem; color: #64748b; }

    .photo-preview-wrap { position: relative; }
    .photo-preview-img {
        width: 100%; aspect-ratio: 1; object-fit: cover;
        border-radius: 12px; display: block; border: 2px solid var(--cx-brand);
    }
    .photo-preview-remove {
        position: absolute; top: 8px; right: 8px;
        background: rgba(239,68,68,.85); border: none; color: #fff;
        width: 30px; height: 30px; border-radius: 50%;
        display: grid; place-items: center; cursor: pointer; font-size: 1rem;
        transition: background .2s;
    }
    .photo-preview-remove:hover { background: #dc2626; }
    .photo-meta {
        margin-top: .6rem; padding: .55rem .8rem;
        background: rgba(16,185,129,.08); border: 1px solid rgba(16,185,129,.2);
        border-radius: 8px; font-size: .76rem; color: var(--cx-green);
    }
    .compress-bar-wrap { margin-top: .5rem; background: rgba(0,0,0,.08); border-radius: 99px; height: 5px; overflow: hidden; }
    .compress-bar { height: 100%; background: var(--cx-brand); border-radius: 99px; transition: width .4s ease; width: 0%; }

    /* Stats strip */
    .stats-strip { display: grid; grid-template-columns: repeat(3,1fr); gap: .6rem; margin-bottom: 1.3rem; }
    .stat-pill { background: rgba(124,58,237,.08); border: 1px solid rgba(124,58,237,.18); border-radius: 10px; padding: .7rem .65rem; text-align: center; }
    .stat-pill-val { font-size: 1.35rem; font-weight: 700; color: #a78bfa; }
    .stat-pill-lbl { font-size: .7rem; color: var(--cx-muted); margin-top: .1rem; }

    /* Form */
    .form-group { margin-bottom: 1.15rem; }
    .form-label {
        display: block; margin-bottom: .4rem; font-size: .8rem; font-weight: 600;
        color: var(--cx-muted); text-transform: uppercase; letter-spacing: .04em;
    }
    .form-label .req { color: var(--cx-red); margin-left: 2px; }
    .form-control {
        width: 100%; background: #f9fafb; border: 1px solid var(--cx-border);
        border-radius: 10px; color: var(--cx-text); padding: .75rem 1rem; font-size: .93rem;
        outline: none; transition: border-color .2s, box-shadow .2s; box-sizing: border-box;
    }
    .form-control:focus { border-color: var(--cx-brand); box-shadow: 0 0 0 3px rgba(124,58,237,.18); }
    .form-control::placeholder { color: #9ca3af; }

    /* Password row */
    .pwd-row { display: flex; gap: .5rem; }
    .pwd-row .form-control { border-radius: 10px 0 0 10px; }
    .btn-gen-pwd {
        flex-shrink: 0; padding: 0 .9rem;
        background: rgba(124,58,237,.15); border: 1px solid rgba(124,58,237,.35);
        border-left: none; border-radius: 0 10px 10px 0; color: #a78bfa;
        font-size: .82rem; cursor: pointer; white-space: nowrap; transition: background .2s;
    }
    .btn-gen-pwd:hover { background: rgba(124,58,237,.3); }
    .pwd-copy { margin-top: .35rem; display: flex; align-items: center; gap: .4rem; font-size: .75rem; color: var(--cx-muted); }
    .pwd-copy button { background: none; border: none; color: #a78bfa; cursor: pointer; font-size: .75rem; text-decoration: underline; padding: 0; }
    .pwd-hint {
        margin-top: .5rem; padding: .5rem .75rem;
        background: rgba(245,158,11,.06); border: 1px solid rgba(245,158,11,.2);
        border-radius: 8px; font-size: .74rem; color: #fbbf24;
    }

    /* Status radio */
    .status-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .5rem; }
    .status-radio { display: none; }
    .status-card {
        border: 2px solid var(--cx-border); border-radius: 10px; padding: .7rem .45rem;
        cursor: pointer; transition: border-color .2s, background .2s;
        display: flex; flex-direction: column; align-items: center; gap: .3rem; text-align: center;
    }
    .status-card-icon { font-size: 1.15rem; }
    .status-card-label { font-size: .72rem; font-weight: 600; }
    .status-card-desc { font-size: .62rem; color: var(--cx-muted); }
    .status-radio[value=available]:checked + .status-card { border-color: var(--cx-green); background: rgba(16,185,129,.08); }
    .status-radio[value=busy]:checked     + .status-card { border-color: var(--cx-amber); background: rgba(245,158,11,.08); }
    .status-radio[value=offline]:checked  + .status-card { border-color: #64748b; background: rgba(100,116,139,.08); }

    /* Section divider */
    .section-divider {
        display: flex; align-items: center; gap: .75rem; margin: 1.2rem 0 1rem;
    }
    .section-divider span { font-size: .75rem; font-weight: 600; color: var(--cx-muted); white-space: nowrap; text-transform: uppercase; letter-spacing: .05em; }
    .section-divider::before, .section-divider::after { content: ''; flex: 1; height: 1px; background: var(--cx-border); }

    /* Role radio cards */
    .role-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .6rem; }
    .role-radio { display: none; }
    .role-card {
        border: 2px solid var(--cx-border); border-radius: 11px; padding: .8rem .65rem;
        cursor: pointer; transition: border-color .2s, background .2s;
        display: flex; align-items: center; gap: .6rem;
    }
    .role-card-icon { font-size: 1.3rem; flex-shrink: 0; }
    .role-card-info { min-width: 0; }
    .role-card-label { font-size: .82rem; font-weight: 600; display: block; }
    .role-card-desc  { font-size: .68rem; color: var(--cx-muted); }
    .role-radio:checked + .role-card { border-color: var(--cx-brand); background: rgba(124,58,237,.1); }
    .role-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .12rem .5rem; border-radius: 99px; font-size: .66rem; font-weight: 700;
        border: 1px solid; margin-top: .15rem;
    }
    .role-badge.livreur { background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.3); color: #34d399; }
    .role-badge.employe { background: rgba(99,102,241,.1); border-color: rgba(99,102,241,.3); color: #818cf8; }

    /* Compte lié */
    .linked-account {
        background: rgba(124,58,237,.06); border: 1px solid rgba(124,58,237,.2);
        border-radius: 10px; padding: .65rem .9rem; margin-bottom: .9rem;
        display: flex; align-items: center; gap: .6rem; font-size: .8rem;
    }
    .linked-account-icon { font-size: 1.1rem; }
    .linked-account strong { color: var(--cx-text); }
    .linked-account span { color: var(--cx-muted); }

    /* Buttons */
    .btn-primary {
        background: linear-gradient(135deg, var(--cx-brand), #9333ea);
        border: none; color: #fff; padding: .85rem 2rem; border-radius: 10px;
        font-size: .95rem; font-weight: 600; cursor: pointer; width: 100%;
        transition: opacity .2s, transform .15s;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
    }
    .btn-primary:hover { opacity: .92; transform: translateY(-1px); }
    .btn-ghost {
        background: transparent; border: 1px solid var(--cx-border); color: var(--cx-muted);
        padding: .85rem 1.5rem; border-radius: 10px; font-size: .95rem; cursor: pointer; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
        text-decoration: none; transition: border-color .2s, color .2s;
    }
    .btn-ghost:hover { border-color: var(--cx-brand); color: var(--cx-text); }
    .btn-row { display: flex; gap: .75rem; margin-top: .5rem; }

    .err-msg { color: var(--cx-red); font-size: .78rem; margin-top: .3rem; }

    /* Danger zone */
    .danger-zone { border: 1px solid rgba(239,68,68,.2); border-radius: 12px; overflow: hidden; }
    .danger-zone-header {
        background: rgba(239,68,68,.06); padding: .7rem 1.1rem;
        font-size: .8rem; font-weight: 600; color: var(--cx-red);
        border-bottom: 1px solid rgba(239,68,68,.15);
        display: flex; align-items: center; gap: .35rem;
    }
    .danger-zone-body { padding: .9rem 1.1rem; }
    .danger-zone-body p { font-size: .8rem; color: var(--cx-muted); margin: 0 0 .7rem; }
    .btn-delete {
        background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.25);
        color: var(--cx-red); border-radius: 8px; padding: .55rem 1.2rem;
        font-size: .82rem; cursor: pointer; display: flex; align-items: center; gap: .35rem;
        transition: background .2s;
    }
    .btn-delete:hover { background: rgba(239,68,68,.2); }

    /* Overlays */
    .processing-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.55);
        display: none; place-items: center; z-index: 999; flex-direction: column; gap: 1rem;
    }
    .processing-overlay.active { display: flex; }
    .spinner {
        width: 48px; height: 48px; border-radius: 50%;
        border: 4px solid rgba(124,58,237,.2); border-top-color: var(--cx-brand);
        animation: spin .8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .processing-label { color: var(--cx-muted); font-size: .9rem; }

    .del-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.6); display: none; place-items: center; z-index: 1000; }
    .del-overlay.active { display: grid; }
    .del-modal {
        background: var(--cx-card); border: 1px solid rgba(239,68,68,.3);
        border-radius: 16px; padding: 2rem; max-width: 400px; width: 90%; text-align: center;
    }
    .del-modal-icon { font-size: 2.5rem; margin-bottom: .75rem; }
    .del-modal h3 { margin: 0 0 .5rem; font-size: 1.1rem; }
    .del-modal p { color: var(--cx-muted); font-size: .85rem; margin: 0 0 1.5rem; }
    .del-modal-btns { display: flex; gap: .75rem; justify-content: center; }
    .del-cancel { background: transparent; border: 1px solid var(--cx-border); color: var(--cx-muted); padding: .65rem 1.5rem; border-radius: 8px; cursor: pointer; font-size: .88rem; }
    .del-cancel:hover { border-color: var(--cx-brand); color: var(--cx-text); }
    .del-confirm { background: var(--cx-red); border: none; color: #fff; padding: .65rem 1.5rem; border-radius: 8px; cursor: pointer; font-size: .88rem; font-weight: 600; }
    .del-confirm:hover { background: #dc2626; }

    /* ══ MODE SOMBRE ══ */
    body.cx-dark {
        --cx-bg:     #0b0d22;
        --cx-card:   #0d1226;
        --cx-border: rgba(255,255,255,.08);
        --cx-text:   #e2e8f0;
        --cx-muted:  #94a3b8;
        background: var(--cx-bg) !important;
        color: var(--cx-text);
    }
    body.cx-dark .form-control { background: #111930; color: #e2e8f0; }
    body.cx-dark .form-control::placeholder { color: #475569; }
    body.cx-dark .photo-drop { background: rgba(15,17,41,.6); }
    body.cx-dark .current-photo-box { background: #111930; }
    body.cx-dark .current-photo-badge { background: rgba(0,0,0,.7); }
    body.cx-dark .no-photo-box { background: rgba(124,58,237,.05); }
    body.cx-dark .pwd-hint { background: rgba(245,158,11,.07); border-color: rgba(245,158,11,.2); color: #fbbf24; }
    body.cx-dark .role-card { background: #0d1226; }
    body.cx-dark .status-card { background: #0d1226; }
    body.cx-dark .linked-account { background: rgba(124,58,237,.08); border-color: rgba(124,58,237,.2); }
    body.cx-dark .btn-ghost { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.1); color: #94a3b8; }
    body.cx-dark .btn-ghost:hover { border-color: var(--cx-brand); color: #e2e8f0; }
    body.cx-dark .del-modal { background: #0d1226; }
    body.cx-dark .del-cancel { border-color: rgba(255,255,255,.1); color: #94a3b8; }
    body.cx-dark .del-cancel:hover { border-color: var(--cx-brand); color: #e2e8f0; }
    body.cx-dark .section-divider::before,
    body.cx-dark .section-divider::after { background: rgba(255,255,255,.08); }
    body.cx-dark .compress-bar-wrap { background: rgba(255,255,255,.06); }
</style>
@endpush

@section('content')

<div class="drv-banner">
    <div class="drv-banner-inner">
        <a href="{{ route('company.drivers.index') }}" style="color:#c4b5fd;text-decoration:none;font-size:.85rem;display:inline-flex;align-items:center;gap:.4rem;margin-bottom:.75rem;">
            ← Retour aux chauffeurs
        </a>
        <h1>Modifier · {{ $driver->name }}</h1>
        <p class="drv-banner-sub">Mettez à jour les informations du chauffeur</p>
    </div>
</div>

<form id="driverForm"
      action="{{ route('company.drivers.update', $driver) }}"
      method="POST" enctype="multipart/form-data" novalidate>
    @csrf @method('PUT')
    <input type="hidden" name="photo_data"   id="photoData">
    <input type="hidden" name="remove_photo" id="removePhotoInput" value="0">

    <div class="drv-form-layout">

        {{-- ══ LEFT: Photo ══ --}}
        <div>
            <div class="drv-panel">
                <div class="drv-panel-header">
                    <div class="ph-icon">📷</div>
                    <h2>Photo du chauffeur</h2>
                </div>
                <div class="drv-panel-body">

                    {{-- Photo actuelle --}}
                    <div id="currentPhotoSection">
                        @if($driver->photo)
                            <div class="current-photo-box" id="currentPhotoBox">
                                <img src="{{ asset('storage/' . $driver->photo) }}"
                                     alt="{{ $driver->name }}"
                                     id="currentPhotoImg">
                                <span class="current-photo-badge">Photo actuelle</span>
                            </div>
                            <button type="button" class="btn-remove-photo" id="btnRemovePhoto" onclick="markRemovePhoto()">
                                🗑️ Supprimer la photo actuelle
                            </button>
                        @else
                            <div class="no-photo-box">
                                <span>👤</span>
                                <span>Aucune photo</span>
                            </div>
                        @endif
                    </div>

                    {{-- Nouvelle photo --}}
                    <p class="photo-drop-label-sm" id="newPhotoLabel">
                        {{ $driver->photo ? 'Remplacer par une nouvelle photo :' : 'Ajouter une photo :' }}
                    </p>

                    <div id="dropZone" class="photo-drop">
                        <input type="file" id="photoInput" accept="image/jpeg,image/png,image/webp,image/gif">
                        <span class="photo-drop-icon">🖼️</span>
                        <div class="photo-drop-label"><strong>Cliquez ou glissez</strong><br>une photo ici</div>
                        <div class="photo-drop-hint">JPEG · PNG · WEBP · GIF — max 5 Mo</div>
                    </div>

                    <div id="previewWrap" style="display:none;">
                        <div class="photo-preview-wrap">
                            <img id="previewImg" class="photo-preview-img" src="" alt="Nouveau aperçu">
                            <button type="button" class="photo-preview-remove" onclick="cancelNewPhoto()" title="Annuler">✕</button>
                        </div>
                        <div class="photo-meta" id="photoMeta"></div>
                        <div class="compress-bar-wrap"><div class="compress-bar" id="compressBar"></div></div>
                    </div>

                    @error('photo')<p class="err-msg">{{ $message }}</p>@enderror

                </div>
            </div>

            {{-- Danger zone --}}
            <div class="danger-zone">
                <div class="danger-zone-header">⚠️ Zone sensible</div>
                <div class="danger-zone-body">
                    <p>La suppression est irréversible. Les commandes associées seront préservées.</p>
                    <button type="button" class="btn-delete" onclick="openDeleteModal()">
                        🗑️ Supprimer ce chauffeur
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ RIGHT: Infos ══ --}}
        <div>
            <div class="drv-panel">
                <div class="drv-panel-header">
                    <div class="ph-icon">👤</div>
                    <h2>Informations du chauffeur</h2>
                </div>
                <div class="drv-panel-body">

                    {{-- Stats --}}
                    <div class="stats-strip">
                        <div class="stat-pill">
                            <div class="stat-pill-val">{{ $driver->orders_count ?? 0 }}</div>
                            <div class="stat-pill-lbl">Total</div>
                        </div>
                        <div class="stat-pill">
                            <div class="stat-pill-val">{{ $driver->livrees_count ?? 0 }}</div>
                            <div class="stat-pill-lbl">Livrées</div>
                        </div>
                        <div class="stat-pill">
                            <div class="stat-pill-val">{{ $driver->en_cours_count ?? 0 }}</div>
                            <div class="stat-pill-lbl">En cours</div>
                        </div>
                    </div>

                    {{-- Nom --}}
                    <div class="form-group">
                        <label class="form-label" for="name">Nom complet <span class="req">*</span></label>
                        <input id="name" name="name" type="text" class="form-control"
                               placeholder="Ex : Moussa Diallo"
                               value="{{ old('name', $driver->name) }}" required>
                        @error('name')<p class="err-msg">{{ $message }}</p>@enderror
                    </div>

                    {{-- Téléphone --}}
                    <div class="form-group">
                        <label class="form-label" for="phone">Téléphone</label>
                        <input id="phone" name="phone" type="tel" class="form-control"
                               placeholder="Ex : +224 620 00 00 00"
                               value="{{ old('phone', $driver->phone) }}">
                        @error('phone')<p class="err-msg">{{ $message }}</p>@enderror
                    </div>

                    <div class="section-divider"><span>Connexion & accès</span></div>

                    {{-- Compte lié --}}
                    @if($driver->user)
                        <div class="linked-account">
                            <span class="linked-account-icon">🔗</span>
                            <div>
                                <strong>Compte actif</strong>
                                <span>— {{ $driver->user->email }} ·
                                <span style="color:{{ $driver->user->role==='livreur'?'#34d399':'#818cf8' }};">{{ $driver->user->role }}</span></span>
                            </div>
                        </div>
                    @else
                        <div style="background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.2);border-radius:10px;padding:.6rem .9rem;margin-bottom:.9rem;font-size:.78rem;color:#fbbf24;">
                            ⚠️ Aucun compte de connexion lié. Ajoutez un e-mail pour en créer un.
                        </div>
                    @endif

                    {{-- Rôle (fixé à livreur) --}}
                    <input type="hidden" name="role" value="livreur">
                    <div class="form-group">
                        <label class="form-label">Rôle dans le système</label>
                        <div class="role-card" style="cursor:default;border-color:rgba(16,185,129,.4);background:rgba(16,185,129,.06);">
                            <span class="role-card-icon">🛵</span>
                            <div class="role-card-info">
                                <span class="role-card-label">Livreur</span>
                                <span class="role-badge livreur">livreur</span>
                                <span class="role-card-desc">Accès tableau de bord livraison</span>
                            </div>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="form-label" for="email">Adresse e-mail</label>
                        <input id="email" name="email" type="email" class="form-control"
                               placeholder="chauffeur@exemple.com"
                               value="{{ old('email', $driver->user?->email ?? $driver->email) }}">
                        <p style="font-size:.75rem;color:var(--cx-muted);margin:.3rem 0 0;">Utilisée pour la connexion au tableau de bord</p>
                        @error('email')<p class="err-msg">{{ $message }}</p>@enderror
                    </div>

                    {{-- Nouveau mot de passe --}}
                    <div class="form-group">
                        <label class="form-label" for="password">Nouveau mot de passe</label>
                        <div class="pwd-row">
                            <input id="password" name="password" type="text" class="form-control"
                                   placeholder="Laisser vide pour ne pas changer" autocomplete="off">
                            <button type="button" class="btn-gen-pwd" onclick="generatePassword()">
                                🔀 Générer
                            </button>
                        </div>
                        <div class="pwd-copy">
                            <span id="pwdCopyHint" style="display:none;">Copier :</span>
                            <button type="button" id="pwdCopyBtn" style="display:none;" onclick="copyPassword()">📋 Copier</button>
                        </div>
                        <div class="pwd-hint">
                            ⚠️ Si renseigné, le chauffeur devra changer ce mot de passe à sa prochaine connexion.
                        </div>
                        @error('password')<p class="err-msg">{{ $message }}</p>@enderror
                    </div>

                    <div class="btn-row">
                        <a href="{{ route('company.drivers.index') }}" class="btn-ghost">✕ Annuler</a>
                        <button type="submit" class="btn-primary">✓ Enregistrer les modifications</button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</form>

{{-- Processing overlay --}}
<div class="processing-overlay" id="processingOverlay">
    <div class="spinner"></div>
    <div class="processing-label">Enregistrement…</div>
</div>

{{-- Delete confirm modal --}}
<div class="del-overlay" id="deleteOverlay">
    <div class="del-modal">
        <div class="del-modal-icon">⚠️</div>
        <h3>Supprimer {{ $driver->name }} ?</h3>
        <p>Cette action est irréversible. Le chauffeur sera définitivement supprimé.</p>
        <div class="del-modal-btns">
            <button type="button" class="del-cancel" onclick="closeDeleteModal()">Annuler</button>
            <form action="{{ route('company.drivers.destroy', $driver) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="del-confirm">Supprimer</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ── Thème sombre ── */
(function(){
    const t = localStorage.getItem('cx-theme') || 'light';
    if(t === 'dark') document.body.classList.add('cx-dark');
    else document.body.classList.remove('cx-dark');
    document.documentElement.classList.remove('cx-predark');
})();

/* ── Photo ── */
const MAX_DIM = 800, QUALITY = 0.85, MAX_BYTES = 5 * 1024 * 1024;
const photoInput       = document.getElementById('photoInput');
const dropZone         = document.getElementById('dropZone');
const previewWrap      = document.getElementById('previewWrap');
const previewImg       = document.getElementById('previewImg');
const photoMeta        = document.getElementById('photoMeta');
const compressBar      = document.getElementById('compressBar');
const photoData        = document.getElementById('photoData');
const removePhotoInput = document.getElementById('removePhotoInput');

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('drag-over'); const f = e.dataTransfer.files[0]; if (f) handleFile(f); });
photoInput.addEventListener('change', () => { if (photoInput.files[0]) handleFile(photoInput.files[0]); });

function handleFile(file) {
    if (file.size > MAX_BYTES) { alert('Max 5 Mo.'); return; }
    if (!file.type.startsWith('image/')) { alert('Image requise.'); return; }
    // Atténuer la photo actuelle
    const box = document.getElementById('currentPhotoBox');
    if (box) box.style.opacity = '.3';
    const reader = new FileReader();
    reader.onload = e => compressImage(e.target.result, file.size);
    reader.readAsDataURL(file);
}

function compressImage(dataUrl, originalSize) {
    compressBar.style.width = '0%';
    dropZone.style.display = 'none';
    previewWrap.style.display = 'block';
    previewImg.src = dataUrl;
    photoMeta.textContent = '⏳ Compression…';
    const img = new Image();
    img.onload = () => {
        let w = img.naturalWidth, h = img.naturalHeight;
        const r = Math.min(MAX_DIM/w, MAX_DIM/h, 1.0);
        w = Math.round(w*r); h = Math.round(h*r);
        const canvas = document.createElement('canvas');
        canvas.width = w; canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff'; ctx.fillRect(0,0,w,h);
        ctx.drawImage(img,0,0,w,h);
        compressBar.style.width = '60%';
        canvas.toBlob(blob => {
            compressBar.style.width = '100%';
            const saved = ((originalSize-blob.size)/originalSize*100).toFixed(0);
            photoMeta.innerHTML = `✅ ${w}×${h}px · ${(blob.size/1024).toFixed(0)} Ko · <strong>${saved}% économisé</strong>`;
            previewImg.src = URL.createObjectURL(blob);
            const rd = new FileReader();
            rd.onload = ev => { photoData.value = ev.target.result; };
            rd.readAsDataURL(blob);
        }, 'image/jpeg', QUALITY);
    };
    img.src = dataUrl;
}

function cancelNewPhoto() {
    photoData.value = ''; previewImg.src = '';
    previewWrap.style.display = 'none'; dropZone.style.display = 'flex';
    photoInput.value = ''; compressBar.style.width = '0%';
    const box = document.getElementById('currentPhotoBox');
    if (box) box.style.opacity = '1';
}

function markRemovePhoto() {
    removePhotoInput.value = '1';
    const box = document.getElementById('currentPhotoBox');
    if (box) { box.style.opacity = '.25'; box.style.pointerEvents = 'none'; }
    const btn = document.getElementById('btnRemovePhoto');
    if (btn) btn.style.display = 'none';
    // Ajouter un message d'annulation
    const notice = document.createElement('div');
    notice.id = 'removeNotice';
    notice.style.cssText = 'background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:8px;padding:.5rem .8rem;font-size:.78rem;color:#f87171;display:flex;align-items:center;justify-content:space-between;margin-bottom:.7rem;';
    notice.innerHTML = '<span>🗑️ Photo supprimée à l\'enregistrement</span><button type="button" onclick="undoRemovePhoto()" style="background:none;border:none;color:#f87171;cursor:pointer;text-decoration:underline;font-size:.78rem;">Annuler</button>';
    const section = document.getElementById('currentPhotoSection');
    section.after(notice);
}

function undoRemovePhoto() {
    removePhotoInput.value = '0';
    const box = document.getElementById('currentPhotoBox');
    if (box) { box.style.opacity = '1'; box.style.pointerEvents = ''; }
    const btn = document.getElementById('btnRemovePhoto');
    if (btn) btn.style.display = 'flex';
    const n = document.getElementById('removeNotice');
    if (n) n.remove();
}

/* ── Password ── */
function generatePassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#';
    let pwd = '';
    for (let i = 0; i < 10; i++) pwd += chars[Math.floor(Math.random() * chars.length)];
    document.getElementById('password').value = pwd;
    document.getElementById('pwdCopyHint').style.display = 'inline';
    document.getElementById('pwdCopyBtn').style.display = 'inline';
}

function copyPassword() {
    const val = document.getElementById('password').value;
    if (!val) return;
    navigator.clipboard.writeText(val).then(() => {
        const btn = document.getElementById('pwdCopyBtn');
        btn.textContent = '✅ Copié !';
        setTimeout(() => btn.textContent = '📋 Copier', 2000);
    });
}

/* ── Delete modal ── */
function openDeleteModal()  { document.getElementById('deleteOverlay').classList.add('active'); }
function closeDeleteModal() { document.getElementById('deleteOverlay').classList.remove('active'); }
document.getElementById('deleteOverlay').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeDeleteModal();
});

document.getElementById('driverForm').addEventListener('submit', () => {
    document.getElementById('processingOverlay').classList.add('active');
});
</script>
@endpush
