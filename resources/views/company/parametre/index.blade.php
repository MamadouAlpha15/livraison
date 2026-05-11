@extends('layouts.app')
@section('title', 'Paramètres · ' . $company->name)
@php
    $bodyClass = 'cx-dashboard';
    $u   = auth()->user();
    $ini = strtoupper(substr($u->name ?? 'A', 0, 1)) . strtoupper(substr(explode(' ', $u->name ?? 'A ')[1] ?? 'X', 0, 1));

    $countryCurrencies = [
        'GN'=>'GNF','SN'=>'XOF','ML'=>'XOF','CI'=>'XOF','BF'=>'XOF',
        'NE'=>'XOF','TG'=>'XOF','BJ'=>'XOF','CM'=>'XAF','CD'=>'CDF',
        'CG'=>'XAF','GA'=>'XAF','MR'=>'MRU','MA'=>'MAD','TN'=>'TND',
        'DZ'=>'DZD','EG'=>'EGP','NG'=>'NGN','GH'=>'GHS','KE'=>'KES',
        'TZ'=>'TZS','RW'=>'RWF','ZA'=>'ZAR','ET'=>'ETB',
        'FR'=>'EUR','DE'=>'EUR','ES'=>'EUR','IT'=>'EUR','BE'=>'EUR','PT'=>'EUR',
        'GB'=>'GBP','US'=>'USD','CA'=>'CAD','AU'=>'AUD','JP'=>'JPY',
        'CN'=>'CNY','BR'=>'BRL','MX'=>'MXN',
    ];

    $countryNames = [
        'GN'=>'Guinée','SN'=>'Sénégal','ML'=>'Mali','CI'=>"Côte d'Ivoire",'BF'=>'Burkina Faso',
        'NE'=>'Niger','TG'=>'Togo','BJ'=>'Bénin','CM'=>'Cameroun','CD'=>'Congo (RDC)',
        'CG'=>'Congo (Brazza)','GA'=>'Gabon','MR'=>'Mauritanie','MA'=>'Maroc','TN'=>'Tunisie',
        'DZ'=>'Algérie','EG'=>'Égypte','NG'=>'Nigeria','GH'=>'Ghana','KE'=>'Kenya',
        'TZ'=>'Tanzanie','RW'=>'Rwanda','ZA'=>'Afrique du Sud','ET'=>'Éthiopie',
        'FR'=>'France','DE'=>'Allemagne','ES'=>'Espagne','IT'=>'Italie','BE'=>'Belgique','PT'=>'Portugal',
        'GB'=>'Royaume-Uni','US'=>'États-Unis','CA'=>'Canada','AU'=>'Australie','JP'=>'Japon',
        'CN'=>'Chine','BR'=>'Brésil','MX'=>'Mexique',
    ];
@endphp

@push('styles')
<script>(function(){ var t=localStorage.getItem('cx-theme'); if(t==='light') document.documentElement.classList.add('cx-prelight'); })();</script>
<style>html.cx-prelight body { background:#F5F7FA !important; }</style>
<style>
*,*::before,*::after { box-sizing:border-box; }
:root {
    --cx-bg:#0b0d22; --cx-surface:#0d1226; --cx-surface2:#111930;
    --cx-sb:#06070f; --cx-border:rgba(255,255,255,.07);
    --cx-brand:#7c3aed; --cx-brand2:#6d28d9; --cx-brand-lt:#a78bfa;
    --cx-text:#e2e8f0; --cx-text2:#94a3b8; --cx-muted:#475569;
    --cx-green:#10b981; --cx-amber:#f59e0b; --cx-red:#ef4444;
    --cx-sb-w:220px; --r:16px; --r-sm:10px; --r-xs:7px;
}
html,body { margin:0; font-family:'Segoe UI',system-ui,sans-serif; background:var(--cx-bg) !important; color:var(--cx-text); -webkit-font-smoothing:antialiased; }
a { text-decoration:none; color:inherit; }
body.cx-dashboard > nav, body.cx-dashboard > header, body.cx-dashboard .navbar,
body.cx-dashboard > .topbar-global, body.cx-dashboard .app-footer, body.cx-dashboard .app-flash { display:none !important; }
body.cx-dashboard > main.app-main { padding:0 !important; margin:0 !important; max-width:100% !important; width:100% !important; background:var(--cx-bg) !important; min-height:100vh; }

/* ══ LAYOUT ══ */
.cx-wrap { display:flex; min-height:100vh; padding-left:var(--cx-sb-w); background:var(--cx-bg); }

/* ══ SIDEBAR ══ */
.cx-sidebar {
    position:fixed; top:0; left:0; bottom:0; width:var(--cx-sb-w);
    background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    display:flex; flex-direction:column;
    border-right:1px solid rgba(99,102,241,.15);
    box-shadow:6px 0 30px rgba(0,0,0,.35);
    z-index:1200; overflow-y:auto;
    scrollbar-width:thin; scrollbar-color:rgba(124,58,237,.3) transparent;
    transition:transform .25s cubic-bezier(.23,1,.32,1);
}
.cx-sidebar::-webkit-scrollbar { width:3px; }
.cx-sidebar::-webkit-scrollbar-thumb { background:rgba(124,58,237,.3); border-radius:3px; }
.cx-brand-hd { padding:14px 14px 10px; border-bottom:1px solid rgba(255,255,255,.06); flex-shrink:0; }
.cx-brand-top { display:flex; align-items:center; justify-content:space-between; }
.cx-logo { display:flex; align-items:center; gap:9px; color:#fff; font-size:16px; font-weight:800; }
.cx-logo-icon { width:34px; height:34px; border-radius:9px; background:linear-gradient(135deg,#7c3aed,#4f46e5); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
.cx-sys-badge { display:inline-flex; align-items:center; gap:5px; font-size:10px; font-weight:600; color:var(--cx-green); padding:3px 8px; border-radius:20px; background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.2); margin-top:8px; }
.cx-sys-dot { width:6px; height:6px; border-radius:50%; background:var(--cx-green); animation:blink 2.2s ease-in-out infinite; flex-shrink:0; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.25} }
.cx-close-btn { display:none; background:none; border:none; color:rgba(255,255,255,.45); font-size:18px; cursor:pointer; padding:2px 6px; border-radius:6px; line-height:1; }
.cx-close-btn:hover { color:#fff; }
.cx-nav { padding:8px 8px 12px; flex:1; }
.cx-nav-sec { font-size:10px; font-weight:800; letter-spacing:1.6px; color:rgba(255,255,255,.58); padding:14px 10px 5px; text-transform:uppercase; }
.cx-nav-item { display:flex; align-items:center; gap:10px; padding:8px 11px; border-radius:var(--r-xs); color:rgba(255,255,255,.85); font-size:13.5px; font-weight:600; transition:all .22s; position:relative; cursor:pointer; margin-bottom:2px; border:1px solid transparent; }
.cx-nav-item:hover { background:rgba(124,58,237,.18); color:#fff; border-color:rgba(124,58,237,.25); }
.cx-nav-item.active { background:linear-gradient(90deg,rgba(124,58,237,.35),rgba(99,102,241,.2)); color:#fff; font-weight:700; border-color:rgba(139,92,246,.3); }
.cx-nav-item.active::before { content:''; position:absolute; left:0; top:50%; transform:translateY(-50%); width:3px; height:22px; background:linear-gradient(180deg,#a78bfa,#7c3aed); border-radius:0 3px 3px 0; }
.cx-nav-ico { font-size:14px; width:20px; text-align:center; flex-shrink:0; }
.cx-user-foot { padding:10px 12px 14px; border-top:1px solid rgba(255,255,255,.07); flex-shrink:0; }
.cx-user-row { display:flex; align-items:center; gap:9px; margin-bottom:10px; }
.cx-user-av { width:32px; height:32px; border-radius:9px; background:linear-gradient(135deg,#7c3aed,#4f46e5); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:#fff; flex-shrink:0; }
.cx-user-name { font-size:12px; font-weight:700; color:var(--cx-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cx-user-role { font-size:10px; color:var(--cx-text2); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cx-logout-btn { background:none; border:none; color:var(--cx-muted); cursor:pointer; font-size:16px; padding:3px 5px; border-radius:5px; transition:color .2s; }
.cx-logout-btn:hover { color:var(--cx-red); }
.cx-dark-row { display:flex; align-items:center; justify-content:space-between; cursor:pointer; padding:4px 2px; border-radius:6px; user-select:none; }
.cx-dark-lbl { font-size:11px; color:var(--cx-text2); }
.cx-toggle { width:32px; height:17px; border-radius:9px; background:rgba(255,255,255,.12); position:relative; transition:background .25s; }
.cx-toggle::after { content:''; position:absolute; left:2px; top:2px; width:13px; height:13px; border-radius:50%; background:#fff; transition:transform .25s; }
.cx-toggle.on { background:var(--cx-brand); }
.cx-toggle.on::after { transform:translateX(15px); }

/* ══ TOPBAR ══ */
.cx-main { flex:1; min-width:0; display:flex; flex-direction:column; }
.cx-topbar { height:54px; background:var(--cx-surface); border-bottom:1px solid var(--cx-border); display:flex; align-items:center; gap:12px; padding:0 18px; position:sticky; top:0; z-index:100; }
.cx-hamburger { display:none; background:none; border:none; color:var(--cx-text2); font-size:20px; cursor:pointer; padding:4px 6px; border-radius:6px; }
.cx-topbar-title { font-size:14px; font-weight:700; color:var(--cx-text); }
.cx-topbar-sub { font-size:12px; color:var(--cx-text2); }
.cx-tb-right { margin-left:auto; display:flex; align-items:center; gap:10px; }
.cx-tb-av { width:30px; height:30px; border-radius:8px; background:linear-gradient(135deg,#7c3aed,#4f46e5); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:#fff; }

/* ══ RESPONSIVE SIDEBAR ══ */
@media (max-width:768px) {
    .cx-wrap { padding-left:0; }
    .cx-sidebar { transform:translateX(-100%); }
    .cx-sidebar.open { transform:translateX(0); }
    .cx-overlay { display:block; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:1100; opacity:0; pointer-events:none; transition:opacity .25s; }
    .cx-overlay.open { opacity:1; pointer-events:auto; }
    .cx-hamburger, .cx-close-btn { display:flex !important; align-items:center; }
}
@media (min-width:769px) {
    .cx-overlay { display:none !important; }
}

/* ══ LIGHT MODE ══ */
body.cx-light {
    --cx-bg:#f5f7fa; --cx-surface:#ffffff; --cx-surface2:#eef1f7;
    --cx-sb:#1e2035; --cx-border:rgba(0,0,0,.08);
    --cx-text:#1a1d2e; --cx-text2:#6b7280; --cx-muted:#9ca3af;
}
body.cx-light .cx-topbar { background:#fff; border-bottom:1px solid #e5e7eb; }
body.cx-light .cx-main { background:#f5f7fa; }

/* ══ SETTINGS CONTENT ══ */
.pm-body {
    padding: 2rem;
    max-width: 780px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}
@media (max-width:640px) { .pm-body { padding: 1rem; } }

/* ── Banner ── */
.pm-banner {
    background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 60%, #7c3aed 100%);
    border-radius: var(--r);
    padding: 1.5rem 2rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    position: relative;
    overflow: hidden;
}
.pm-banner::before {
    content:'';position:absolute;inset:0;
    background:url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.04'%3E%3Ccircle cx='20' cy='20' r='3'/%3E%3C/g%3E%3C/svg%3E");
}
.pm-banner-ico { font-size:2rem; flex-shrink:0; position:relative; }
.pm-banner-title { font-size:1.2rem; font-weight:800; color:#fff; position:relative; }
.pm-banner-sub { font-size:.82rem; color:rgba(255,255,255,.75); position:relative; margin-top:.2rem; }

/* ── Section card ── */
.pm-section {
    background: var(--cx-surface);
    border: 1px solid var(--cx-border);
    border-radius: var(--r);
    overflow: hidden;
}
.pm-section-head {
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--cx-border);
    display: flex;
    align-items: center;
    gap: .75rem;
}
.pm-section-head .sh-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.pm-section-head h3 { font-size: .98rem; font-weight: 700; margin: 0; color: var(--cx-text); }
.pm-section-head p  { font-size: .78rem; color: var(--cx-text2); margin: .15rem 0 0; }
.pm-section-body { padding: 1.5rem; }

/* ── Form grid ── */
.pm-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.1rem 1.5rem;
}
@media (max-width:580px) { .pm-grid { grid-template-columns: 1fr; } }
.pm-grid .col-full { grid-column: 1 / -1; }

/* ── Field ── */
.pm-field label {
    display: block;
    font-size: .78rem; font-weight: 600;
    color: var(--cx-text2);
    margin-bottom: .35rem;
    letter-spacing: .02em;
    text-transform: uppercase;
}
.pm-field input,
.pm-field textarea,
.pm-field select {
    width: 100%;
    padding: .6rem .85rem;
    border: 1.5px solid rgba(255,255,255,.1);
    border-radius: var(--r-xs);
    background: var(--cx-surface2);
    color: var(--cx-text);
    font-size: .9rem;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    font-family: inherit;
}
body.cx-light .pm-field input,
body.cx-light .pm-field textarea,
body.cx-light .pm-field select {
    border-color: #e5e7eb;
    background: #f9fafb;
    color: #111827;
}
.pm-field input:focus,
.pm-field textarea:focus,
.pm-field select:focus {
    border-color: var(--cx-brand);
    box-shadow: 0 0 0 3px rgba(124,58,237,.18);
}
.pm-field textarea { resize: vertical; min-height: 80px; }
.pm-field .is-invalid { border-color: var(--cx-red) !important; }
.pm-field .field-err { font-size: .75rem; color: var(--cx-red); margin-top: .3rem; }

/* ── Currency badge ── */
.currency-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(16,185,129,.12);
    border: 1px solid rgba(16,185,129,.3);
    border-radius: 2rem;
    padding: .4rem 1rem;
    font-size: .88rem;
    font-weight: 700;
    color: #34d399;
    transition: all .3s;
}
.currency-badge .cb-icon { font-size: 1rem; }

/* ── Alert / toast ── */
.pm-alert {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .75rem 1rem;
    border-radius: var(--r-xs);
    font-size: .85rem;
    font-weight: 500;
    margin-bottom: 1rem;
}
.pm-alert.success { background: rgba(16,185,129,.12); border: 1px solid rgba(16,185,129,.3); color: #34d399; }
.pm-alert.error   { background: rgba(239,68,68,.1);   border: 1px solid rgba(239,68,68,.3);  color: #f87171; }

/* ── Image upload ── */
.pm-img-zone {
    border: 2px dashed rgba(255,255,255,.15);
    border-radius: 10px;
    padding: 1.25rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    position: relative;
}
.pm-img-zone:hover { border-color: var(--cx-brand); background: rgba(124,58,237,.06); }
body.cx-light .pm-img-zone { border-color: #d1d5db; }
body.cx-light .pm-img-zone:hover { border-color: #7c3aed; background: #f5f0ff; }
.pm-img-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.pm-img-current { display:flex; align-items:center; gap:1rem; pointer-events:none; }
.pm-img-current img { width:56px; height:56px; object-fit:cover; border-radius:10px; border:2px solid rgba(255,255,255,.12); }
.pm-img-text { text-align:left; }
.pm-img-text strong { font-size:.85rem; color:var(--cx-text); display:block; }
.pm-img-text span { font-size:.75rem; color:var(--cx-text2); }

/* ── Submit btn ── */
.pm-btn {
    padding: .65rem 1.5rem;
    border: none;
    border-radius: var(--r-xs);
    font-size: .88rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s, transform .15s;
    font-family: inherit;
}
.pm-btn:hover { opacity: .88; transform: translateY(-1px); }
.pm-btn-primary { background: linear-gradient(135deg, #7c3aed, #6d28d9); color: #fff; box-shadow: 0 3px 12px rgba(124,58,237,.35); }
.pm-btn-green   { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 3px 12px rgba(16,185,129,.3); }
.pm-btn-red     { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 3px 12px rgba(239,68,68,.3); }
.pm-btn-actions { display: flex; justify-content: flex-end; margin-top: 1.25rem; }

/* ── Password strength ── */
.pw-strength { height: 4px; border-radius: 2px; margin-top: .45rem; background: rgba(255,255,255,.08); overflow: hidden; }
body.cx-light .pw-strength { background: #e5e7eb; }
.pw-strength-bar { height: 100%; border-radius: 2px; width: 0%; transition: width .3s, background .3s; }
.pw-hint { font-size: .72rem; color: var(--cx-text2); margin-top: .3rem; }
</style>
@endpush

@section('content')
<div class="cx-wrap">

{{-- SIDEBAR --}}
<aside class="cx-sidebar" id="cxSidebar">
    <div class="cx-brand-hd">
        <div class="cx-brand-top">
            <a href="{{ route('company.dashboard') }}" class="cx-logo">
                <div class="cx-logo-icon">🏢</div>
                <span style="font-size:13px;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $company->name }}</span>
            </a>
            <button class="cx-close-btn" id="cxClose">✕</button>
        </div>
        <div class="cx-sys-badge"><span class="cx-sys-dot"></span> Système actif</div>
    </div>

    <nav class="cx-nav">
        <div class="cx-nav-sec">Principal</div>
        <a href="{{ route('company.dashboard') }}" class="cx-nav-item"><span class="cx-nav-ico">⊞</span> Tableau de bord</a>
        <a href="{{ route('company.chat.inbox') }}" class="cx-nav-item"><span class="cx-nav-ico">💬</span> Demandes (Chat)</a>
        <a href="{{ route('company.orders.index') }}" class="cx-nav-item"><span class="cx-nav-ico">📦</span> Commandes</a>
        <a href="{{ route('company.drivers.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🚴</span> Chauffeurs</a>
        <a href="{{ route('company.livraisons.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🚚</span> Livraisons</a>
        <a href="{{ route('company.carte.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🗺️</span> Carte en direct</a>
        <a href="{{ route('company.boutiques.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🏪</span> Boutiques</a>
        <a href="{{ route('company.clients.index') }}" class="cx-nav-item"><span class="cx-nav-ico">👥</span> Clients</a>

        <div class="cx-nav-sec">Gestion</div>
        <a href="{{ route('company.zones.index') }}" class="cx-nav-item"><span class="cx-nav-ico">📍</span> Zone de livraison</a>
        <a href="{{ route('company.historique.index') }}" class="cx-nav-item"><span class="cx-nav-ico">📊</span> Historique</a>
        <a href="{{ route('company.rapport.index') }}" class="cx-nav-item"><span class="cx-nav-ico">📈</span> Rapport</a>

        <div class="cx-nav-sec">Configuration</div>
        <a href="{{ route('company.parametre.index') }}" class="cx-nav-item active"><span class="cx-nav-ico">⚙️</span> Paramètres</a>
        <a href="{{ route('company.users.index') }}" class="cx-nav-item"><span class="cx-nav-ico">👤</span> Utilisateurs</a>
    </nav>

    <div class="cx-user-foot">
        <div class="cx-user-row">
            <div class="cx-user-av">{{ $ini }}</div>
            <div style="flex:1;min-width:0">
                <div class="cx-user-name">{{ Str::limit($u->name ?? 'Admin', 16) }}</div>
                <div class="cx-user-role">{{ $company->name }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="cx-logout-btn" title="Déconnexion">⏻</button>
            </form>
        </div>
        <div class="cx-dark-row" id="cxThemeRow">
            <span class="cx-dark-lbl">Mode sombre</span>
            <div class="cx-toggle" id="cxToggle"></div>
        </div>
    </div>
</aside>
<div class="cx-overlay" id="cxOverlay"></div>

{{-- MAIN --}}
<div class="cx-main">

    {{-- TOPBAR --}}
    <div class="cx-topbar">
        <button class="cx-hamburger" id="cxHamburger">☰</button>
        <div>
            <span class="cx-topbar-title">⚙️ Paramètres</span>
            <span class="cx-topbar-sub">· {{ $company->name }}</span>
        </div>
        <div class="cx-tb-right">
            <div class="cx-tb-av">{{ $ini }}</div>
        </div>
    </div>

    {{-- BODY --}}
    <div class="pm-body">

        {{-- Banner --}}
        <div class="pm-banner">
            <div class="pm-banner-ico">⚙️</div>
            <div>
                <div class="pm-banner-title">Paramètres de l'entreprise</div>
                <div class="pm-banner-sub">Modifiez les informations, le pays et la sécurité de votre compte.</div>
            </div>
        </div>

        {{-- ══ SECTION 1 : Informations ══ --}}
        <div class="pm-section">
            <div class="pm-section-head">
                <div class="sh-icon" style="background:rgba(124,58,237,.15)">🏢</div>
                <div>
                    <h3>Informations de l'entreprise</h3>
                    <p>Nom, description, contacts et logo</p>
                </div>
            </div>
            <div class="pm-section-body">
                @if(session('success_info'))
                    <div class="pm-alert success">✅ {{ session('success_info') }}</div>
                @endif

                <form action="{{ route('company.parametre.updateInfo') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PATCH')

                    <div class="pm-grid">
                        {{-- Nom --}}
                        <div class="pm-field col-full">
                            <label>Nom de l'entreprise *</label>
                            <input type="text" name="name" value="{{ old('name', $company->name) }}"
                                   class="{{ $errors->has('name') ? 'is-invalid' : '' }}" required>
                            @error('name') <div class="field-err">{{ $message }}</div> @enderror
                        </div>

                        {{-- Description --}}
                        <div class="pm-field col-full">
                            <label>Description</label>
                            <textarea name="description" placeholder="Décrivez votre entreprise...">{{ old('description', $company->description) }}</textarea>
                        </div>

                        {{-- Téléphone --}}
                        <div class="pm-field">
                            <label>Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                                   placeholder="+224 6xx xxx xxx">
                        </div>

                        {{-- Email --}}
                        <div class="pm-field">
                            <label>Email professionnel</label>
                            <input type="email" name="email" value="{{ old('email', $company->email) }}"
                                   placeholder="contact@entreprise.com"
                                   class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
                            @error('email') <div class="field-err">{{ $message }}</div> @enderror
                        </div>

                        {{-- Adresse --}}
                        <div class="pm-field col-full">
                            <label>Adresse</label>
                            <input type="text" name="address" value="{{ old('address', $company->address) }}"
                                   placeholder="Rue, quartier, ville">
                        </div>

                        {{-- Logo --}}
                        <div class="pm-field col-full">
                            <label>Logo / Image de l'entreprise</label>
                            <div class="pm-img-zone">
                                <input type="file" name="image" id="logoInput" accept="image/*">
                                <div class="pm-img-current">
                                    <img id="logoPreview"
                                         src="{{ $company->image ? asset('storage/'.$company->image) : asset('images/placeholder-company.png') }}"
                                         alt="Logo">
                                    <div class="pm-img-text">
                                        <strong>Cliquez pour changer le logo</strong>
                                        <span>JPG, PNG, WEBP — max 4 Mo</span>
                                    </div>
                                </div>
                            </div>
                            @error('image') <div class="field-err">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="pm-btn-actions">
                        <button type="submit" class="pm-btn pm-btn-primary">Enregistrer les informations</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══ SECTION 2 : Pays & Devise ══ --}}
        <div class="pm-section">
            <div class="pm-section-head">
                <div class="sh-icon" style="background:rgba(16,185,129,.12)">🌍</div>
                <div>
                    <h3>Pays & Devise</h3>
                    <p>La devise est automatiquement déduite du pays. Votre entreprise sera visible par les boutiques du même pays.</p>
                </div>
            </div>
            <div class="pm-section-body">
                @if(session('success_country'))
                    <div class="pm-alert success">✅ {{ session('success_country') }}</div>
                @endif

                <form action="{{ route('company.parametre.updateCountry') }}" method="POST">
                    @csrf @method('PATCH')

                    <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:1.25rem;">
                        <div class="pm-field" style="flex:1;min-width:200px">
                            <label>Pays</label>
                            <select name="country" id="countrySelect"
                                    class="{{ $errors->has('country') ? 'is-invalid' : '' }}">
                                @php
                                    $regions = [
                                        'Afrique de l\'Ouest' => ['GN','SN','ML','CI','BF','NE','TG','BJ','MR','GH','NG'],
                                        'Afrique Centrale'    => ['CM','CD','CG','GA'],
                                        'Afrique du Nord'     => ['MA','TN','DZ','EG'],
                                        'Afrique de l\'Est'   => ['KE','TZ','RW','ET','ZA'],
                                        'Europe'              => ['FR','DE','ES','IT','BE','PT','GB'],
                                        'Amériques & Asie'    => ['US','CA','BR','MX','AU','JP','CN'],
                                    ];
                                @endphp
                                @foreach($regions as $region => $codes)
                                    <optgroup label="{{ $region }}">
                                        @foreach($codes as $code)
                                            <option value="{{ $code }}"
                                                    data-currency="{{ $countryCurrencies[$code] ?? 'USD' }}"
                                                    {{ ($company->country ?? $u->country) === $code ? 'selected' : '' }}>
                                                {{ $countryNames[$code] ?? $code }} ({{ $countryCurrencies[$code] ?? '—' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('country') <div class="field-err">{{ $message }}</div> @enderror
                        </div>

                        <div style="flex-shrink:0;padding-bottom:.1rem">
                            <div style="font-size:.75rem;font-weight:600;color:var(--cx-text2);text-transform:uppercase;letter-spacing:.02em;margin-bottom:.45rem;">Devise automatique</div>
                            <div class="currency-badge" id="currencyBadge">
                                <span class="cb-icon">💰</span>
                                <span id="currencyText">{{ $company->currency ?? 'GNF' }}</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:.75rem;font-size:.78rem;color:var(--cx-text2);background:rgba(255,255,255,.04);border-radius:8px;padding:.65rem .85rem;border:1px solid rgba(255,255,255,.07)">
                        ℹ️ En changeant de pays, votre entreprise disparaît des recherches de l'ancien pays et apparaît dans les recherches du nouveau pays. La devise est mise à jour immédiatement.
                    </div>

                    <div class="pm-btn-actions" style="margin-top:1rem">
                        <button type="submit" class="pm-btn pm-btn-green">Mettre à jour le pays</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══ SECTION 3 : Mot de passe ══ --}}
        <div class="pm-section">
            <div class="pm-section-head">
                <div class="sh-icon" style="background:rgba(239,68,68,.1)">🔒</div>
                <div>
                    <h3>Sécurité — Mot de passe</h3>
                    <p>Modifiez le mot de passe de votre compte propriétaire</p>
                </div>
            </div>
            <div class="pm-section-body">
                @if(session('success_password'))
                    <div class="pm-alert success">✅ {{ session('success_password') }}</div>
                @endif
                @if($errors->has('current_password'))
                    <div class="pm-alert error">❌ {{ $errors->first('current_password') }}</div>
                @endif

                <form action="{{ route('company.parametre.updatePassword') }}" method="POST">
                    @csrf @method('PATCH')

                    <div class="pm-grid">
                        <div class="pm-field col-full">
                            <label>Mot de passe actuel</label>
                            <input type="password" name="current_password" autocomplete="current-password"
                                   class="{{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                                   placeholder="••••••••">
                        </div>

                        <div class="pm-field">
                            <label>Nouveau mot de passe</label>
                            <input type="password" name="password" id="newPw" autocomplete="new-password"
                                   class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                   placeholder="••••••••" minlength="8">
                            <div class="pw-strength"><div class="pw-strength-bar" id="pwBar"></div></div>
                            <div class="pw-hint" id="pwHint">8 caractères minimum</div>
                            @error('password') <div class="field-err">{{ $message }}</div> @enderror
                        </div>

                        <div class="pm-field">
                            <label>Confirmer le nouveau mot de passe</label>
                            <input type="password" name="password_confirmation" autocomplete="new-password"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="pm-btn-actions">
                        <button type="submit" class="pm-btn pm-btn-red">Changer le mot de passe</button>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- /pm-body --}}
</div>{{-- /cx-main --}}
</div>{{-- /cx-wrap --}}
@endsection

@push('scripts')
<script>
(function(){
    /* ── Thème ── */
    const saved = localStorage.getItem('cx-theme');
    const body  = document.body;
    const tog   = document.getElementById('cxToggle');
    if (saved === 'light') { body.classList.add('cx-light'); } else { tog?.classList.add('on'); }
    document.getElementById('cxThemeRow')?.addEventListener('click', () => {
        const isLight = body.classList.toggle('cx-light');
        tog?.classList.toggle('on', !isLight);
        localStorage.setItem('cx-theme', isLight ? 'light' : 'dark');
    });

    /* ── Hamburger ── */
    const sb  = document.getElementById('cxSidebar');
    const ov  = document.getElementById('cxOverlay');
    const ham = document.getElementById('cxHamburger');
    const cls = document.getElementById('cxClose');
    function openSb()  { sb?.classList.add('open'); ov?.classList.add('open'); }
    function closeSb() { sb?.classList.remove('open'); ov?.classList.remove('open'); }
    ham?.addEventListener('click', openSb);
    cls?.addEventListener('click', closeSb);
    ov?.addEventListener('click', closeSb);

    /* ── Logo preview ── */
    document.getElementById('logoInput')?.addEventListener('change', function(){
        const file = this.files[0];
        if (!file) return;
        document.getElementById('logoPreview').src = URL.createObjectURL(file);
    });

    /* ── Country → currency live preview ── */
    const sel = document.getElementById('countrySelect');
    const txt = document.getElementById('currencyText');
    sel?.addEventListener('change', function(){
        const opt = this.options[this.selectedIndex];
        txt.textContent = opt.dataset.currency || '—';
    });

    /* ── Password strength ── */
    document.getElementById('newPw')?.addEventListener('input', function(){
        const v   = this.value;
        const bar = document.getElementById('pwBar');
        const hnt = document.getElementById('pwHint');
        let score = 0;
        if (v.length >= 8)  score++;
        if (/[A-Z]/.test(v))  score++;
        if (/[0-9]/.test(v))  score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;
        const colors = ['#ef4444','#f59e0b','#3b82f6','#10b981'];
        const labels = ['Trop court','Faible','Correct','Fort'];
        bar.style.width   = (score * 25) + '%';
        bar.style.background = colors[score - 1] || '#ef4444';
        hnt.textContent   = v.length ? labels[score - 1] || 'Trop court' : '8 caractères minimum';
    });
})();
</script>
@endpush
