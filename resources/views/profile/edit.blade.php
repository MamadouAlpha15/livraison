@extends('layouts.app')
@section('title', 'Mon profil · ' . $user->name)
@php
    $bodyClass  = 'is-dashboard';
    $nameParts  = explode(' ', $user->name ?? 'U X');
    $initials   = strtoupper(substr($nameParts[0],0,1)) . strtoupper(substr($nameParts[1] ?? 'X',0,1));
    $roleLabel  = match($user->role) {
        'admin'      => 'Propriétaire boutique',
        'vendeur'    => 'Vendeur',
        'employe'    => 'Employé',
        'livreur'    => 'Livreur',
        'client'     => 'Client',
        'company'    => 'Responsable entreprise',
        'superadmin' => 'Super administrateur',
        default      => ucfirst($user->role ?? 'Utilisateur'),
    };
    $dashRoute = match($user->role) {
        'client'     => 'client.dashboard',
        'livreur'    => 'livreur.dashboard',
        'company'    => 'company.dashboard',
        'superadmin' => 'admin.dashboard',
        default      => 'boutique.dashboard',
    };
    $countries = [
        'SN' => 'Sénégal', 'CI' => "Côte d'Ivoire", 'ML' => 'Mali',
        'BF' => 'Burkina Faso', 'GN' => 'Guinée', 'TG' => 'Togo',
        'BJ' => 'Bénin', 'CM' => 'Cameroun', 'GA' => 'Gabon',
        'MR' => 'Mauritanie', 'NE' => 'Niger', 'NG' => 'Nigeria',
        'GH' => 'Ghana', 'FR' => 'France', 'MA' => 'Maroc',
        'DZ' => 'Algérie', 'TN' => 'Tunisie',
    ];
@endphp

@push('styles')
<style>
*,*::before,*::after { box-sizing:border-box; }

:root {
    --brand:#6366f1; --brand-dk:#4f46e5; --brand-lt:#e0e7ff; --brand-mlt:#eef2ff;
    --bg:#f1f5f9; --surface:#fff;
    --border:#e2e8f0; --border-dk:#cbd5e1;
    --text:#0f172a; --text2:#475569; --muted:#94a3b8;
    --green:#10b981; --red:#ef4444; --amber:#f59e0b;
    --danger-lt:#fef2f2;
    --font:'Segoe UI',system-ui,sans-serif;
    --mono:'JetBrains Mono','Courier New',monospace;
    --r:14px; --r-sm:9px; --r-xs:6px;
    --shadow-sm:0 1px 3px rgba(0,0,0,.07);
    --shadow:0 4px 20px rgba(0,0,0,.08);
}

body { margin:0; font-family:var(--font); background:var(--bg); color:var(--text); -webkit-font-smoothing:antialiased; }
a { text-decoration:none; color:inherit; }

/* ══ WRAPPER ══ */
.pf-wrap { min-height:100vh; display:flex; flex-direction:column; }

/* ══ HERO ══ */
.pf-hero {
    background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 40%,#2d2470 70%,#3d1fa5 100%);
    padding:32px 24px 90px; position:relative; overflow:hidden;
}
.pf-hero::before {
    content:''; position:absolute; inset:0;
    background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),
                     linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
    background-size:40px 40px; pointer-events:none;
}
.pf-hero::after {
    content:''; position:absolute; top:-60px; right:-60px;
    width:280px; height:280px; border-radius:50%;
    background:radial-gradient(circle,rgba(139,92,246,.25),transparent 70%);
    pointer-events:none;
}
.pf-hero-inner { position:relative; z-index:1; max-width:720px; margin:0 auto; }

.pf-back {
    display:inline-flex; align-items:center; gap:7px;
    padding:8px 16px; border-radius:10px;
    background:rgba(255,255,255,.1); border:1.5px solid rgba(255,255,255,.18);
    color:rgba(255,255,255,.85); font-size:12.5px; font-weight:700;
    transition:all .15s; margin-bottom:28px;
}
.pf-back:hover { background:rgba(255,255,255,.2); color:#fff; transform:translateX(-2px); }

.pf-hero-body { display:flex; align-items:center; gap:22px; flex-wrap:wrap; }

.pf-avatar {
    width:80px; height:80px; border-radius:50%; flex-shrink:0;
    background:linear-gradient(135deg,#818cf8,#4f46e5);
    display:flex; align-items:center; justify-content:center;
    font-size:26px; font-weight:900; color:#fff; letter-spacing:-.5px;
    box-shadow:0 0 0 5px rgba(255,255,255,.15), 0 8px 24px rgba(0,0,0,.35);
}

.pf-hero-info { flex:1; min-width:0; }
.pf-name  { font-size:24px; font-weight:900; color:#fff; letter-spacing:-.4px; margin:0 0 6px; }
.pf-role-badge {
    display:inline-flex; align-items:center; gap:5px;
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
    color:rgba(255,255,255,.85); font-size:11.5px; font-weight:700;
    padding:4px 12px; border-radius:20px; margin-bottom:8px;
}
.pf-meta { font-size:12px; color:rgba(255,255,255,.5); display:flex; gap:16px; flex-wrap:wrap; }
.pf-meta span { display:flex; align-items:center; gap:4px; }

/* ══ BODY ══ */
.pf-body { flex:1; padding:0 24px 60px; margin-top:-60px; position:relative; z-index:1; }
.pf-inner { max-width:720px; margin:0 auto; display:flex; flex-direction:column; gap:20px; }

/* ══ CARDS ══ */
.pf-card {
    background:var(--surface); border-radius:var(--r);
    box-shadow:var(--shadow); overflow:hidden;
}
.pf-card-hd {
    padding:18px 24px; border-bottom:1px solid var(--border);
    display:flex; align-items:center; gap:12px;
}
.pf-card-ico {
    width:40px; height:40px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:18px;
}
.pf-card-title { font-size:14px; font-weight:800; color:var(--text); }
.pf-card-sub   { font-size:11.5px; color:var(--muted); margin-top:1px; }
.pf-card-body  { padding:24px; }

/* ══ FORMULAIRE ══ */
.pf-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.pf-grid.full { grid-template-columns:1fr; }
.pf-field { display:flex; flex-direction:column; gap:6px; }
.pf-field.span2 { grid-column:1/-1; }
.pf-label {
    font-size:12px; font-weight:700; color:var(--text2);
    text-transform:uppercase; letter-spacing:.4px;
    display:flex; align-items:center; gap:5px;
}
.pf-label span { font-size:11px; }
.pf-input, .pf-select {
    width:100%; padding:11px 14px;
    border:1.5px solid var(--border); border-radius:var(--r-sm);
    font-size:13.5px; font-family:var(--font);
    color:var(--text); background:var(--bg);
    outline:none; transition:border-color .15s, box-shadow .15s;
}
.pf-input:focus, .pf-select:focus {
    border-color:var(--brand);
    box-shadow:0 0 0 3px rgba(99,102,241,.12);
    background:#fff;
}
.pf-input::placeholder { color:var(--muted); }
.pf-input.mono { font-family:var(--mono); letter-spacing:0; }
.pf-input-wrap { position:relative; }
.pf-input-wrap .pf-input { padding-right:42px; }
.pf-eye {
    position:absolute; right:12px; top:50%; transform:translateY(-50%);
    background:none; border:none; cursor:pointer; color:var(--muted);
    font-size:16px; padding:2px; transition:color .15s; line-height:1;
}
.pf-eye:hover { color:var(--brand); }

/* Hint sous champ */
.pf-hint { font-size:11px; color:var(--muted); margin-top:2px; }

/* ══ BOUTONS ══ */
.pf-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:7px;
    padding:11px 22px; border-radius:var(--r-sm);
    font-size:13px; font-weight:700; font-family:var(--font);
    cursor:pointer; transition:all .15s; border:none; white-space:nowrap;
}
.pf-btn-primary { background:var(--brand); color:#fff; }
.pf-btn-primary:hover { background:var(--brand-dk); transform:translateY(-1px); box-shadow:0 4px 12px rgba(99,102,241,.35); }
.pf-btn-danger { background:var(--red); color:#fff; }
.pf-btn-danger:hover { background:#dc2626; transform:translateY(-1px); box-shadow:0 4px 12px rgba(239,68,68,.35); }
.pf-btn-ghost {
    background:transparent; color:var(--text2);
    border:1.5px solid var(--border-dk);
}
.pf-btn-ghost:hover { border-color:var(--brand); color:var(--brand); background:var(--brand-mlt); }
.pf-btn[disabled] { opacity:.55; cursor:not-allowed; transform:none !important; box-shadow:none !important; }

.pf-foot {
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; flex-wrap:wrap;
    padding-top:20px; margin-top:20px; border-top:1px solid var(--border);
}
.pf-foot-left  { display:flex; align-items:center; gap:8px; }
.pf-foot-right { display:flex; align-items:center; gap:8px; }

/* ══ FLASH ══ */
.pf-flash {
    display:flex; align-items:center; gap:10px;
    padding:12px 18px; border-radius:var(--r-sm); border:1px solid;
    font-size:13px; font-weight:600; margin-bottom:6px;
}
.pf-flash-success { background:#f0fdf4; border-color:#86efac; color:#15803d; }
.pf-flash-error   { background:var(--danger-lt); border-color:#fca5a5; color:#991b1b; }

/* ══ ERREURS ══ */
.pf-err { font-size:11.5px; color:var(--red); font-weight:600; margin-top:3px; display:flex; align-items:center; gap:4px; }
.pf-input.err, .pf-select.err { border-color:var(--red); box-shadow:0 0 0 3px rgba(239,68,68,.1); }

/* ══ AVATAR DISPLAY ══ */
.pf-av-display {
    display:flex; align-items:center; gap:16px;
    padding:16px 20px; background:var(--bg); border-radius:var(--r-sm);
    border:1.5px solid var(--border); margin-bottom:6px;
}
.pf-av-circle {
    width:56px; height:56px; border-radius:50%; flex-shrink:0;
    background:linear-gradient(135deg,#818cf8,#4f46e5);
    display:flex; align-items:center; justify-content:center;
    font-size:18px; font-weight:800; color:#fff;
}
.pf-av-info { flex:1; min-width:0; }
.pf-av-name { font-size:14px; font-weight:700; color:var(--text); }
.pf-av-sub  { font-size:11.5px; color:var(--muted); margin-top:2px; }

/* ══ DANGER ZONE ══ */
.pf-danger-box {
    background:#fffbfb; border:1.5px solid #fecaca;
    border-radius:var(--r-sm); padding:18px 20px;
    display:flex; align-items:flex-start; gap:14px;
}
.pf-danger-ico { font-size:24px; flex-shrink:0; margin-top:2px; }
.pf-danger-text { flex:1; min-width:0; }
.pf-danger-title { font-size:13.5px; font-weight:800; color:#991b1b; margin-bottom:4px; }
.pf-danger-sub   { font-size:12.5px; color:#b91c1c; line-height:1.5; }

/* ══ MODAL ══ */
.pf-modal {
    display:none; position:fixed; inset:0; z-index:500;
    background:rgba(0,0,0,.5); align-items:center; justify-content:center;
    padding:20px;
}
.pf-modal.open { display:flex; }
.pf-modal-box {
    background:#fff; border-radius:var(--r);
    padding:30px 28px; max-width:420px; width:100%;
    box-shadow:0 20px 60px rgba(0,0,0,.25);
    animation:modalIn .2s ease;
}
@keyframes modalIn { from{opacity:0;transform:scale(.95) translateY(-8px)} to{opacity:1;transform:scale(1) translateY(0)} }
.pf-modal-ico   { font-size:40px; text-align:center; margin-bottom:14px; }
.pf-modal-title { font-size:17px; font-weight:800; color:var(--text); text-align:center; margin-bottom:8px; }
.pf-modal-sub   { font-size:13px; color:var(--muted); text-align:center; line-height:1.6; margin-bottom:20px; }
.pf-modal-sub strong { color:var(--text); }
.pf-modal-actions { display:flex; gap:10px; }
.pf-modal-actions .pf-btn { flex:1; font-size:13px; }

/* ══ PROGRESS bar mot de passe ══ */
.pf-strength { margin-top:6px; }
.pf-strength-bar { height:4px; border-radius:2px; background:var(--border); overflow:hidden; }
.pf-strength-fill { height:100%; border-radius:2px; transition:width .3s, background .3s; width:0; }
.pf-strength-lbl  { font-size:10.5px; font-weight:700; color:var(--muted); margin-top:4px; }

/* ══ SEPARATOR ══ */
.pf-sep { display:flex; align-items:center; gap:12px; margin:6px 0; color:var(--muted); font-size:11px; }
.pf-sep::before, .pf-sep::after { content:''; flex:1; height:1px; background:var(--border); }

/* ══ RESPONSIVE ══ */
@media(max-width:640px) {
    .pf-hero  { padding:20px 16px 80px; }
    .pf-body  { padding:0 12px 50px; }
    .pf-grid  { grid-template-columns:1fr; gap:14px; }
    .pf-grid .pf-field.span2 { grid-column:1; }
    .pf-card-body  { padding:18px 16px; }
    .pf-card-hd    { padding:14px 16px; }
    .pf-name  { font-size:20px; }
    .pf-avatar { width:66px; height:66px; font-size:22px; }
    .pf-foot  { flex-direction:column-reverse; align-items:stretch; }
    .pf-foot-left, .pf-foot-right { width:100%; }
    .pf-btn   { width:100%; }
    .pf-danger-box { flex-direction:column; gap:8px; }
    .pf-modal-box  { padding:22px 18px; }
    .pf-modal-actions { flex-direction:column; }
}

@media(max-width:400px) {
    .pf-hero-body { gap:14px; }
    .pf-avatar { width:54px; height:54px; font-size:18px; }
    .pf-name  { font-size:17px; }
    .pf-back  { font-size:11.5px; padding:7px 12px; margin-bottom:18px; }
}
</style>
@endpush

@section('content')

{{-- ══ MODAL SUPPRESSION ══ --}}
<div class="pf-modal" id="deleteModal">
    <div class="pf-modal-box">
        <div class="pf-modal-ico">⚠️</div>
        <div class="pf-modal-title">Supprimer mon compte ?</div>
        <div class="pf-modal-sub">
            Cette action est <strong>irréversible</strong>. Toutes vos données seront effacées définitivement.
            Saisissez votre mot de passe pour confirmer.
        </div>
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf @method('DELETE')
            <div class="pf-field" style="margin-bottom:16px">
                <label class="pf-label">Mot de passe</label>
                <div class="pf-input-wrap">
                    <input type="password" name="password" id="delPassword"
                           class="pf-input {{ $errors->userDeletion->has('password') ? 'err' : '' }}"
                           placeholder="••••••••" autocomplete="current-password">
                    <button type="button" class="pf-eye" onclick="toggleVis('delPassword',this)">👁</button>
                </div>
                @error('password', 'userDeletion')
                <div class="pf-err">⚠ {{ $message }}</div>
                @enderror
            </div>
            <div class="pf-modal-actions">
                <button type="button" class="pf-btn pf-btn-ghost" onclick="closeModal()">← Annuler</button>
                <button type="submit" class="pf-btn pf-btn-danger">🗑️ Supprimer</button>
            </div>
        </form>
    </div>
</div>

<div class="pf-wrap">

    {{-- ══ HERO ══ --}}
    <div class="pf-hero">
        <div class="pf-hero-inner">
            <a href="{{ route($dashRoute) }}" class="pf-back">← Retour au tableau de bord</a>
            <div class="pf-hero-body">
                <div class="pf-avatar" id="heroAvatar">{{ $initials }}</div>
                <div class="pf-hero-info">
                    <h1 class="pf-name" id="heroName">{{ $user->name }}</h1>
                    <div class="pf-role-badge">{{ $roleLabel }}</div>
                    <div class="pf-meta">
                        @if($user->email)
                        <span>✉️ {{ $user->email }}</span>
                        @endif
                        @if($user->phone)
                        <span>📞 {{ $user->phone }}</span>
                        @endif
                        <span>🗓️ Membre depuis {{ $user->created_at->translatedFormat('M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ BODY ══ --}}
    <div class="pf-body">
        <div class="pf-inner">

            {{-- ── Flash messages ── --}}
            @if(session('status') === 'profile-updated')
            <div class="pf-flash pf-flash-success">✅ Profil mis à jour avec succès.</div>
            @endif
            @if(session('status') === 'password-updated')
            <div class="pf-flash pf-flash-success">🔐 Mot de passe modifié avec succès.</div>
            @endif
            @foreach(['success','error'] as $t)
                @if(session($t))
                <div class="pf-flash pf-flash-{{ $t }}">{{ session($t) }}</div>
                @endif
            @endforeach

            {{-- ════════ CARTE 1 : Informations personnelles ════════ --}}
            <div class="pf-card">
                <div class="pf-card-hd">
                    <div class="pf-card-ico" style="background:#eef2ff">👤</div>
                    <div>
                        <div class="pf-card-title">Informations personnelles</div>
                        <div class="pf-card-sub">Nom, email, téléphone et adresse</div>
                    </div>
                </div>
                <div class="pf-card-body">

                    {{-- Avatar preview --}}
                    <div class="pf-av-display">
                        <div class="pf-av-circle" id="cardAvatar">{{ $initials }}</div>
                        <div class="pf-av-info">
                            <div class="pf-av-name" id="cardName">{{ $user->name }}</div>
                            <div class="pf-av-sub">{{ $user->email }} · {{ $roleLabel }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
                        @csrf @method('PATCH')

                        <div class="pf-grid" style="margin-top:20px">

                            {{-- Nom --}}
                            <div class="pf-field span2">
                                <label class="pf-label" for="name">Nom complet</label>
                                <input type="text" id="name" name="name"
                                       class="pf-input {{ $errors->has('name') ? 'err' : '' }}"
                                       value="{{ old('name', $user->name) }}"
                                       placeholder="Prénom Nom"
                                       oninput="syncNamePreview(this.value)"
                                       required>
                                @error('name')
                                <div class="pf-err">⚠ {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="pf-field span2">
                                <label class="pf-label" for="email">Adresse email</label>
                                <input type="email" id="email" name="email"
                                       class="pf-input {{ $errors->has('email') ? 'err' : '' }}"
                                       value="{{ old('email', $user->email) }}"
                                       placeholder="vous@exemple.com"
                                       required>
                                @if($user->email_verified_at)
                                <div class="pf-hint" style="color:var(--green)">✅ Email vérifié</div>
                                @endif
                                @error('email')
                                <div class="pf-err">⚠ {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Téléphone --}}
                            <div class="pf-field">
                                <label class="pf-label" for="phone">
                                    Téléphone <span style="color:var(--muted);font-weight:400;text-transform:none">(optionnel)</span>
                                </label>
                                <input type="tel" id="phone" name="phone"
                                       class="pf-input mono {{ $errors->has('phone') ? 'err' : '' }}"
                                       value="{{ old('phone', $user->phone) }}"
                                       placeholder="+221 77 000 00 00">
                                @error('phone')
                                <div class="pf-err">⚠ {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Pays --}}
                            <div class="pf-field">
                                <label class="pf-label" for="country">Pays</label>
                                <select id="country" name="country"
                                        class="pf-select {{ $errors->has('country') ? 'err' : '' }}">
                                    <option value="">— Sélectionner —</option>
                                    @foreach($countries as $code => $label)
                                    <option value="{{ $code }}" {{ old('country', $user->country) === $code ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('country')
                                <div class="pf-err">⚠ {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Adresse --}}
                            <div class="pf-field span2">
                                <label class="pf-label" for="address">
                                    Adresse <span style="color:var(--muted);font-weight:400;text-transform:none">(optionnel)</span>
                                </label>
                                <input type="text" id="address" name="address"
                                       class="pf-input {{ $errors->has('address') ? 'err' : '' }}"
                                       value="{{ old('address', $user->address) }}"
                                       placeholder="Rue, quartier, ville">
                                @error('address')
                                <div class="pf-err">⚠ {{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="pf-foot">
                            <div class="pf-foot-left">
                                <span style="font-size:11.5px;color:var(--muted)">
                                    🕐 Dernière mise à jour : {{ $user->updated_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="pf-foot-right">
                                <button type="reset" class="pf-btn pf-btn-ghost">↺ Réinitialiser</button>
                                <button type="submit" class="pf-btn pf-btn-primary" id="saveBtn">
                                    ✓ Enregistrer
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            {{-- ════════ CARTE 2 : Mot de passe ════════ --}}
            <div class="pf-card">
                <div class="pf-card-hd">
                    <div class="pf-card-ico" style="background:#fef3c7">🔐</div>
                    <div>
                        <div class="pf-card-title">Changer le mot de passe</div>
                        <div class="pf-card-sub">Utilisez un mot de passe fort d'au moins 8 caractères</div>
                    </div>
                </div>
                <div class="pf-card-body">

                    @if($errors->updatePassword->any())
                    <div class="pf-flash pf-flash-error" style="margin-bottom:16px">
                        ⚠ {{ $errors->updatePassword->first() }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" id="pwdForm">
                        @csrf @method('PUT')

                        <div class="pf-grid full">

                            {{-- Mot de passe actuel (caché pour les comptes Google) --}}
                            @if($user->google_id)
                            <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:var(--r-sm)">
                                <span style="font-size:20px">🔗</span>
                                <div>
                                    <div style="font-size:13px;font-weight:700;color:#15803d">Compte connecté via Google</div>
                                    <div style="font-size:12px;color:#166534;margin-top:2px">Vous pouvez définir un mot de passe directement sans saisir l'ancien.</div>
                                </div>
                            </div>
                            @else
                            <div class="pf-field">
                                <label class="pf-label" for="current_password">Mot de passe actuel</label>
                                <div class="pf-input-wrap">
                                    <input type="password" id="current_password" name="current_password"
                                           class="pf-input {{ $errors->updatePassword->has('current_password') ? 'err' : '' }}"
                                           placeholder="••••••••" autocomplete="current-password">
                                    <button type="button" class="pf-eye" onclick="toggleVis('current_password',this)">👁</button>
                                </div>
                                @error('current_password', 'updatePassword')
                                <div class="pf-err">⚠ {{ $message }}</div>
                                @enderror
                            </div>
                            @endif

                            <div class="pf-sep">nouveau mot de passe</div>

                            {{-- Nouveau --}}
                            <div class="pf-field">
                                <label class="pf-label" for="password">Nouveau mot de passe</label>
                                <div class="pf-input-wrap">
                                    <input type="password" id="password" name="password"
                                           class="pf-input {{ $errors->updatePassword->has('password') ? 'err' : '' }}"
                                           placeholder="Min. 8 caractères" autocomplete="new-password"
                                           oninput="checkStrength(this.value)">
                                    <button type="button" class="pf-eye" onclick="toggleVis('password',this)">👁</button>
                                </div>
                                <div class="pf-strength" id="strengthWrap" style="display:none">
                                    <div class="pf-strength-bar">
                                        <div class="pf-strength-fill" id="strengthFill"></div>
                                    </div>
                                    <div class="pf-strength-lbl" id="strengthLbl"></div>
                                </div>
                                @error('password', 'updatePassword')
                                <div class="pf-err">⚠ {{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Confirmation --}}
                            <div class="pf-field">
                                <label class="pf-label" for="password_confirmation">Confirmer le mot de passe</label>
                                <div class="pf-input-wrap">
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                           class="pf-input"
                                           placeholder="••••••••" autocomplete="new-password"
                                           oninput="checkMatch()">
                                    <button type="button" class="pf-eye" onclick="toggleVis('password_confirmation',this)">👁</button>
                                </div>
                                <div class="pf-hint" id="matchHint"></div>
                            </div>

                        </div>

                        <div class="pf-foot">
                            <div class="pf-foot-left">
                                <span style="font-size:11.5px;color:var(--muted)">🔑 Minimum 8 caractères</span>
                            </div>
                            <div class="pf-foot-right">
                                <button type="reset" class="pf-btn pf-btn-ghost" onclick="resetPwdForm()">↺ Effacer</button>
                                <button type="submit" class="pf-btn pf-btn-primary">🔐 Modifier</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            {{-- ════════ CARTE 3 : Danger ════════ --}}
            <div class="pf-card">
                <div class="pf-card-hd">
                    <div class="pf-card-ico" style="background:#fef2f2">🗑️</div>
                    <div>
                        <div class="pf-card-title" style="color:#991b1b">Zone de danger</div>
                        <div class="pf-card-sub">Actions irréversibles sur votre compte</div>
                    </div>
                </div>
                <div class="pf-card-body">
                    <div class="pf-danger-box">
                        <div class="pf-danger-ico">⚠️</div>
                        <div class="pf-danger-text">
                            <div class="pf-danger-title">Supprimer mon compte définitivement</div>
                            <div class="pf-danger-sub">
                                Toutes vos données seront effacées : commandes, boutique, historique.
                                Cette action ne peut pas être annulée.
                            </div>
                        </div>
                        <div style="flex-shrink:0">
                            <button type="button" class="pf-btn pf-btn-danger" onclick="openModal()"
                                    style="font-size:12.5px;padding:9px 18px">
                                🗑️ Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
/* ── Prévisualisation du nom / avatar ── */
function syncNamePreview(val) {
    const parts = val.trim().split(/\s+/);
    const ini   = (parts[0]?.[0] ?? '').toUpperCase() + (parts[1]?.[0] ?? '').toUpperCase();
    const name  = val.trim() || '—';
    document.getElementById('heroName')?.textContent && (document.getElementById('heroName').textContent = name);
    document.getElementById('cardName') && (document.getElementById('cardName').textContent = name);
    if (ini) {
        document.getElementById('heroAvatar') && (document.getElementById('heroAvatar').textContent = ini);
        document.getElementById('cardAvatar') && (document.getElementById('cardAvatar').textContent = ini);
    }
}

/* ── Afficher / cacher mot de passe ── */
function toggleVis(id, btn) {
    const inp = document.getElementById(id);
    if (!inp) return;
    const show = inp.type === 'password';
    inp.type   = show ? 'text' : 'password';
    btn.textContent = show ? '🙈' : '👁';
}

/* ── Force du mot de passe ── */
function checkStrength(val) {
    const wrap = document.getElementById('strengthWrap');
    const fill = document.getElementById('strengthFill');
    const lbl  = document.getElementById('strengthLbl');
    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
        { pct:20,  color:'#ef4444', label:'Très faible' },
        { pct:40,  color:'#f97316', label:'Faible' },
        { pct:60,  color:'#f59e0b', label:'Moyen' },
        { pct:80,  color:'#10b981', label:'Fort' },
        { pct:100, color:'#059669', label:'Très fort' },
    ];
    const lvl = levels[Math.min(score, 4)];
    fill.style.width      = lvl.pct + '%';
    fill.style.background = lvl.color;
    lbl.textContent       = lvl.label;
    lbl.style.color       = lvl.color;
}

/* ── Correspondance confirmation ── */
function checkMatch() {
    const pwd  = document.getElementById('password').value;
    const conf = document.getElementById('password_confirmation').value;
    const hint = document.getElementById('matchHint');
    if (!conf) { hint.textContent = ''; return; }
    if (pwd === conf) {
        hint.textContent  = '✅ Les mots de passe correspondent';
        hint.style.color  = 'var(--green)';
    } else {
        hint.textContent  = '❌ Les mots de passe ne correspondent pas';
        hint.style.color  = 'var(--red)';
    }
}

function resetPwdForm() {
    document.getElementById('pwdForm').reset();
    document.getElementById('strengthWrap').style.display = 'none';
    document.getElementById('matchHint').textContent = '';
}

/* ── Modal suppression ── */
function openModal()  { document.getElementById('deleteModal').classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal() { document.getElementById('deleteModal').classList.remove('open'); document.body.style.overflow=''; }
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

/* ── Ouvrir modal si erreur suppression ── */
@if($errors->userDeletion->any())
openModal();
@endif
</script>
@endpush
