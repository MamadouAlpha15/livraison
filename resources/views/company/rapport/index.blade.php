@extends('layouts.app')
@section('title', 'Rapport · ' . $company->name)
@php
    $bodyClass = 'cx-dashboard';
    $u   = auth()->user();
    $ini = strtoupper(substr($u->name ?? 'A', 0, 1)) . strtoupper(substr(explode(' ', $u->name ?? 'A ')[1] ?? 'X', 0, 1));
    $fmt = fn($n) => number_format($n ?? 0, 0, ',', ' ') . ' ' . $devise;
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
.cx-nav-ico { width:26px; height:26px; border-radius:7px; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.07); display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
.cx-user-foot { padding:10px 10px 12px; border-top:1px solid rgba(255,255,255,.07); flex-shrink:0; }
.cx-user-row { display:flex; align-items:center; gap:9px; padding:7px 8px; border-radius:var(--r-xs); background:rgba(255,255,255,.04); cursor:pointer; transition:background .15s; margin-bottom:6px; }
.cx-user-row:hover { background:rgba(255,255,255,.08); }
.cx-user-av { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#7c3aed,#4338ca); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; color:#fff; flex-shrink:0; }
.cx-user-name { font-size:12px; font-weight:700; color:#fff; }
.cx-user-role { font-size:10px; color:var(--cx-text2); }
.cx-logout-btn { width:30px; height:30px; border-radius:8px; flex-shrink:0; background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.2); display:flex; align-items:center; justify-content:center; color:#f87171; cursor:pointer; transition:all .15s; padding:0; }
.cx-logout-btn:hover { background:rgba(239,68,68,.22); color:#fff; }
.cx-dark-row { display:flex; align-items:center; justify-content:space-between; padding:4px 8px; cursor:pointer; }
.cx-dark-lbl { font-size:11.5px; color:var(--cx-text2); }
.cx-toggle { width:34px; height:18px; background:#475569; border-radius:9px; position:relative; transition:background .25s; flex-shrink:0; }
.cx-toggle::after { content:''; position:absolute; top:3px; left:3px; width:12px; height:12px; background:#fff; border-radius:50%; transition:left .25s; }
.cx-toggle.on { background:var(--cx-brand); }
.cx-toggle.on::after { left:19px; }
.cx-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:1100; }
.cx-overlay.open { display:block; }

/* ══ TOPBAR ══ */
.cx-main { flex:1; min-width:0; display:flex; flex-direction:column; min-height:100vh; }
.cx-topbar { height:60px; background:var(--cx-surface); border-bottom:1px solid var(--cx-border); display:flex; align-items:center; gap:12px; padding:0 20px; position:sticky; top:0; z-index:100; }
.cx-hamburger { display:none; background:none; border:none; color:var(--cx-text2); font-size:18px; cursor:pointer; padding:4px 8px; border-radius:6px; }
.cx-topbar-title { font-size:15px; font-weight:800; color:var(--cx-text); }
.cx-topbar-sub { font-size:12px; color:var(--cx-muted); margin-left:2px; }
.cx-tb-right { margin-left:auto; display:flex; align-items:center; gap:8px; }
.cx-tb-av { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#7c3aed,#4338ca); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; color:#fff; flex-shrink:0; }
.cx-tb-uname { font-size:12.5px; font-weight:700; color:var(--cx-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:120px; }

/* ══ BANNER ══ */
.rp-banner { background:linear-gradient(135deg,#1e1b4b 0%,#2d2470 35%,#3d1fa5 65%,#5b21b6 100%); padding:28px 24px 22px; position:relative; overflow:hidden; }
.rp-banner::before { content:''; position:absolute; inset:0; background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px); background-size:44px 44px; pointer-events:none; }
.rp-banner-inner { position:relative; z-index:1; display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.rp-banner-left { display:flex; align-items:center; gap:14px; }
.rp-banner-ico { width:48px; height:48px; border-radius:14px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
.rp-banner-title { font-size:22px; font-weight:900; color:#fff; letter-spacing:-.4px; }
.rp-banner-sub { font-size:12.5px; color:rgba(255,255,255,.55); margin-top:2px; }

/* Filtre période */
.rp-period { display:flex; gap:6px; flex-wrap:wrap; }
.rp-period a { padding:7px 14px; border-radius:8px; font-size:12px; font-weight:700; border:1.5px solid rgba(255,255,255,.2); color:rgba(255,255,255,.7); background:rgba(255,255,255,.07); transition:all .15s; white-space:nowrap; }
.rp-period a:hover { background:rgba(255,255,255,.15); color:#fff; }
.rp-period a.active { background:rgba(255,255,255,.22); color:#fff; border-color:rgba(255,255,255,.45); }

/* ══ CONTENT ══ */
.rp-body { padding:24px; display:flex; flex-direction:column; gap:22px; max-width:1200px; margin:0 auto; width:100%; }

/* ══ KPI GRID ══ */
.rp-kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
.rp-kpi { background:var(--cx-surface); border:1px solid var(--cx-border); border-radius:var(--r); padding:18px 20px; display:flex; align-items:center; gap:14px; transition:transform .15s,box-shadow .15s; position:relative; overflow:hidden; }
.rp-kpi:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.3); }
.rp-kpi::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--kpi-accent,#7c3aed); }
.rp-kpi-ico { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; background:var(--kpi-bg,rgba(124,58,237,.12)); }
.rp-kpi-val { font-size:24px; font-weight:900; color:var(--cx-text); font-family:monospace; line-height:1; }
.rp-kpi-lbl { font-size:11px; font-weight:600; color:var(--cx-muted); margin-top:3px; text-transform:uppercase; letter-spacing:.4px; }
.rp-kpi-sub { font-size:11.5px; font-weight:700; margin-top:4px; }

/* ══ SECTION CARDS ══ */
.rp-row { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.rp-row.thirds { grid-template-columns:1fr 1fr 1fr; }
.rp-card { background:var(--cx-surface); border:1px solid var(--cx-border); border-radius:var(--r); overflow:hidden; }
.rp-card-hd { padding:16px 20px; border-bottom:1px solid var(--cx-border); display:flex; align-items:center; justify-content:space-between; }
.rp-card-title { font-size:13.5px; font-weight:800; color:var(--cx-text); display:flex; align-items:center; gap:8px; }
.rp-card-badge { font-size:11px; font-weight:700; padding:3px 9px; border-radius:20px; background:rgba(124,58,237,.15); color:var(--cx-brand-lt); border:1px solid rgba(124,58,237,.2); }

/* Revenue card */
.rev-grid { display:grid; grid-template-columns:1fr 1fr; gap:0; }
.rev-item { padding:18px 20px; position:relative; }
.rev-item + .rev-item { border-left:1px solid var(--cx-border); }
.rev-item-lbl { font-size:10.5px; font-weight:700; color:var(--cx-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
.rev-item-val { font-size:20px; font-weight:900; font-family:monospace; }
.rev-item-sub { font-size:11px; color:var(--cx-muted); margin-top:3px; }

/* Table */
.rp-table { width:100%; border-collapse:collapse; }
.rp-table th { padding:10px 16px; font-size:10.5px; font-weight:700; color:var(--cx-muted); text-transform:uppercase; letter-spacing:.5px; text-align:left; border-bottom:1px solid var(--cx-border); background:var(--cx-surface2); }
.rp-table td { padding:12px 16px; font-size:13px; color:var(--cx-text); border-bottom:1px solid rgba(255,255,255,.04); }
.rp-table tr:last-child td { border-bottom:none; }
.rp-table tr:hover td { background:rgba(255,255,255,.02); }
.td-name { font-weight:700; }
.td-num { font-family:monospace; font-weight:700; }
.td-badge { display:inline-flex; align-items:center; padding:3px 8px; border-radius:6px; font-size:10.5px; font-weight:700; }
.badge-ok { background:rgba(16,185,129,.12); color:#34d399; border:1px solid rgba(16,185,129,.2); }
.badge-warn { background:rgba(245,158,11,.12); color:#fbbf24; border:1px solid rgba(245,158,11,.2); }

/* Stars */
.stars { color:#f59e0b; letter-spacing:1px; font-size:13px; }

/* Chart */
.rp-chart-wrap { padding:20px; }
canvas { max-height:200px; }

/* Empty row */
.rp-empty { padding:32px 20px; text-align:center; color:var(--cx-muted); font-size:13px; }

/* ══ MODE CLAIR ══ */
body.cx-light { --cx-bg:#F5F7FA; --cx-surface:#fff; --cx-surface2:#eef1f7; --cx-border:rgba(0,0,0,.08); --cx-text:#0f172a; --cx-text2:#374151; --cx-muted:#6b7280; }
body.cx-light,html.cx-light body { background:#F5F7FA !important; }
body.cx-light .cx-topbar { background:#fff; border-color:rgba(0,0,0,.07); }
body.cx-light .cx-dark-lbl { color:#4b5563; }
body.cx-light footer.app-footer { background:#eef1f7 !important; color:#6b7280 !important; border-top:1px solid rgba(0,0,0,.07); }

/* ══ RESPONSIVE ══ */
@media(max-width:900px) {
    .rp-kpi-grid { grid-template-columns:repeat(2,1fr); }
    .rp-row { grid-template-columns:1fr; }
    .rp-row.thirds { grid-template-columns:1fr; }
}
@media(max-width:640px) {
    :root { --cx-sb-w:0px; }
    .cx-wrap { padding-left:0; }
    .cx-sidebar { transform:translateX(-100%); width:220px; }
    .cx-sidebar.open { transform:translateX(0); }
    .cx-hamburger { display:flex; }
    .rp-banner { padding:18px 16px 16px; }
    .rp-banner-title { font-size:17px; }
    .rp-banner-ico { width:40px; height:40px; font-size:18px; }
    .rp-body { padding:16px; gap:16px; }
    .rp-kpi-grid { grid-template-columns:1fr 1fr; gap:10px; }
    .rp-kpi { padding:14px; gap:10px; }
    .rp-kpi-val { font-size:18px; }
    .rp-kpi-ico { width:38px; height:38px; font-size:17px; }
    .rp-period a { padding:6px 10px; font-size:11px; }
    .rp-table th, .rp-table td { padding:10px 12px; font-size:12px; }
}
@media(max-width:400px) {
    .rp-kpi-grid { grid-template-columns:1fr; }
    .rev-grid { grid-template-columns:1fr; }
    .rev-item + .rev-item { border-left:none; border-top:1px solid var(--cx-border); }
}
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
        <a href="{{ route('company.rapport.index') }}" class="cx-nav-item active"><span class="cx-nav-ico">📈</span> Rapport</a>

        <div class="cx-nav-sec">Configuration</div>
        <a href="{{ route('company.parametre.index') }}" class="cx-nav-item"><span class="cx-nav-ico">⚙️</span> Paramètres</a>
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
            <span class="cx-topbar-title">📈 Rapport général</span>
            <span class="cx-topbar-sub">· {{ $company->name }}</span>
        </div>
        <div class="cx-tb-right">
            <div style="display:flex;align-items:center;gap:8px;">
                <div class="cx-tb-av">{{ $ini }}</div>
                <div style="display:none" class="d-sm-block">
                    <div class="cx-tb-uname">{{ $u->name ?? '' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- BANNER --}}
    <div class="rp-banner">
        <div class="rp-banner-inner">
            <div class="rp-banner-left">
                <div class="rp-banner-ico">📈</div>
                <div>
                    <div class="rp-banner-title">Rapport général</div>
                    <div class="rp-banner-sub">{{ $company->name }} · Période : {{ $days }} derniers jours</div>
                </div>
            </div>
            <div class="rp-period">
                <a href="?days=7"   class="{{ $days == 7   ? 'active' : '' }}">7 jours</a>
                <a href="?days=30"  class="{{ $days == 30  ? 'active' : '' }}">30 jours</a>
                <a href="?days=90"  class="{{ $days == 90  ? 'active' : '' }}">90 jours</a>
                <a href="?days=365" class="{{ $days == 365 ? 'active' : '' }}">1 an</a>
            </div>
        </div>
    </div>

    {{-- BODY --}}
    <div class="rp-body">

        {{-- ── KPI COMMANDES ── --}}
        <div class="rp-kpi-grid">
            <div class="rp-kpi" style="--kpi-accent:#7c3aed;--kpi-bg:rgba(124,58,237,.1)">
                <div class="rp-kpi-ico">📦</div>
                <div>
                    <div class="rp-kpi-val">{{ $totalOrders }}</div>
                    <div class="rp-kpi-lbl">Total commandes</div>
                </div>
            </div>
            <div class="rp-kpi" style="--kpi-accent:#10b981;--kpi-bg:rgba(16,185,129,.1)">
                <div class="rp-kpi-ico">✅</div>
                <div>
                    <div class="rp-kpi-val" style="color:#34d399">{{ $totalLivrees }}</div>
                    <div class="rp-kpi-lbl">Livrées</div>
                    @if($tauxReussite !== null)
                    <div class="rp-kpi-sub" style="color:#34d399">{{ $tauxReussite }}% succès</div>
                    @endif
                </div>
            </div>
            <div class="rp-kpi" style="--kpi-accent:#ef4444;--kpi-bg:rgba(239,68,68,.08)">
                <div class="rp-kpi-ico">❌</div>
                <div>
                    <div class="rp-kpi-val" style="color:#f87171">{{ $totalAnnulees }}</div>
                    <div class="rp-kpi-lbl">Annulées</div>
                </div>
            </div>
            <div class="rp-kpi" style="--kpi-accent:#f59e0b;--kpi-bg:rgba(245,158,11,.1)">
                <div class="rp-kpi-ico">🚚</div>
                <div>
                    <div class="rp-kpi-val" style="color:#fbbf24">{{ $totalEnCours }}</div>
                    <div class="rp-kpi-lbl">En cours</div>
                    @if($avgMins)
                    <div class="rp-kpi-sub" style="color:var(--cx-muted)">Moy. {{ $avgMins }} min</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── REVENUS + NOTE ── --}}
        <div class="rp-row">
            {{-- Revenus --}}
            <div class="rp-card">
                <div class="rp-card-hd">
                    <span class="rp-card-title">💰 Revenus (commissions)</span>
                    <span class="rp-card-badge">{{ $days }}j</span>
                </div>
                <div class="rev-grid">
                    <div class="rev-item">
                        <div class="rev-item-lbl">Total généré</div>
                        <div class="rev-item-val" style="color:#a78bfa">{{ $fmt($revenusTotal) }}</div>
                        <div class="rev-item-sub">Frais de livraison perçus</div>
                    </div>
                    <div class="rev-item">
                        <div class="rev-item-lbl">Encaissé</div>
                        <div class="rev-item-val" style="color:#34d399">{{ $fmt($revenusEncaiss) }}</div>
                        <div class="rev-item-sub" style="color:#fbbf24">En attente : {{ $fmt($revenusAttente) }}</div>
                    </div>
                </div>
            </div>

            {{-- Note --}}
            <div class="rp-card">
                <div class="rp-card-hd">
                    <span class="rp-card-title">⭐ Satisfaction clients</span>
                    <span class="rp-card-badge">{{ $ratingCount }} avis</span>
                </div>
                <div style="padding:24px 20px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                    @if($avgRating !== null)
                    <div style="text-align:center;flex-shrink:0;">
                        <div style="font-size:42px;font-weight:900;color:#f59e0b;font-family:monospace;line-height:1;">{{ $avgRating }}</div>
                        <div style="font-size:11px;color:var(--cx-muted);margin-top:2px;">sur 5</div>
                    </div>
                    <div style="flex:1;min-width:120px;">
                        @php $starsStr = str_repeat('★',(int)$avgRating).str_repeat('☆',5-(int)$avgRating); @endphp
                        <div class="stars" style="font-size:22px;">{{ $starsStr }}</div>
                        <div style="font-size:12px;color:var(--cx-muted);margin-top:6px;">Basé sur {{ $ratingCount }} évaluation{{ $ratingCount > 1 ? 's' : '' }}</div>
                        @php $pct = round($avgRating / 5 * 100); @endphp
                        <div style="margin-top:10px;height:6px;background:rgba(255,255,255,.1);border-radius:3px;overflow:hidden;">
                            <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#f59e0b,#fbbf24);border-radius:3px;"></div>
                        </div>
                    </div>
                    @else
                    <div style="color:var(--cx-muted);font-size:13px;padding:8px 0;">Aucun avis reçu sur cette période.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── GRAPHE COMMANDES ── --}}
        <div class="rp-card">
            <div class="rp-card-hd">
                <span class="rp-card-title">📊 Évolution des commandes ({{ min($days,30) }} derniers jours)</span>
            </div>
            <div class="rp-chart-wrap">
                <canvas id="rpChart"></canvas>
            </div>
        </div>

        {{-- ── TOP LIVREURS + ZONES ── --}}
        <div class="rp-row">
            {{-- Top livreurs --}}
            <div class="rp-card">
                <div class="rp-card-hd">
                    <span class="rp-card-title">🚴 Top livreurs</span>
                    <span class="rp-card-badge">{{ $topDrivers->count() }}</span>
                </div>
                @if($topDrivers->where('livrees','>',0)->count())
                <table class="rp-table">
                    <thead><tr>
                        <th>Livreur</th>
                        <th style="text-align:right">Livrées</th>
                        <th style="text-align:right">Statut</th>
                    </tr></thead>
                    <tbody>
                    @foreach($topDrivers->where('livrees','>',0) as $drv)
                    <tr>
                        <td class="td-name">{{ $drv->name }}</td>
                        <td class="td-num" style="text-align:right;color:#34d399">{{ $drv->livrees }}</td>
                        <td style="text-align:right">
                            <span class="td-badge {{ $drv->status === 'available' ? 'badge-ok' : 'badge-warn' }}">
                                {{ $drv->status === 'available' ? 'Disponible' : ($drv->status === 'busy' ? 'Occupé' : 'Hors ligne') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @else
                <div class="rp-empty">Aucune livraison sur cette période.</div>
                @endif
            </div>

            {{-- Zones --}}
            <div class="rp-card">
                <div class="rp-card-hd">
                    <span class="rp-card-title">📍 Performance par zone</span>
                    <span class="rp-card-badge">{{ $zonePerf->count() }}</span>
                </div>
                @if($zonePerf->count())
                <table class="rp-table">
                    <thead><tr>
                        <th>Zone</th>
                        <th style="text-align:right">Livraisons</th>
                        <th style="text-align:right">Revenus</th>
                    </tr></thead>
                    <tbody>
                    @foreach($zonePerf as $z)
                    <tr>
                        <td class="td-name">{{ $z['name'] }}</td>
                        <td class="td-num" style="text-align:right">{{ $z['count'] }}</td>
                        <td class="td-num" style="text-align:right;color:#a78bfa">{{ $fmt($z['revenue']) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @else
                <div class="rp-empty">Aucune donnée de zone disponible.</div>
                @endif
            </div>
        </div>

        {{-- ── TOP BOUTIQUES ── --}}
        <div class="rp-card">
            <div class="rp-card-hd">
                <span class="rp-card-title">🏪 Boutiques partenaires</span>
                <span class="rp-card-badge">{{ $topShops->count() }}</span>
            </div>
            @if($topShops->count())
            <div style="overflow-x:auto;">
            <table class="rp-table" style="min-width:500px;">
                <thead><tr>
                    <th>Boutique</th>
                    <th style="text-align:right">Commandes</th>
                    <th style="text-align:right">Livrées</th>
                    <th style="text-align:right">Taux</th>
                    <th style="text-align:right">Revenus</th>
                </tr></thead>
                <tbody>
                @foreach($topShops as $s)
                @php $taux = $s['total'] > 0 ? round($s['livrees']/$s['total']*100) : 0; @endphp
                <tr>
                    <td class="td-name">{{ $s['name'] }}</td>
                    <td class="td-num" style="text-align:right">{{ $s['total'] }}</td>
                    <td class="td-num" style="text-align:right;color:#34d399">{{ $s['livrees'] }}</td>
                    <td style="text-align:right">
                        <span class="td-badge {{ $taux >= 70 ? 'badge-ok' : 'badge-warn' }}">{{ $taux }}%</span>
                    </td>
                    <td class="td-num" style="text-align:right;color:#a78bfa">{{ $fmt($s['revenue']) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @else
            <div class="rp-empty">Aucune boutique partenaire sur cette période.</div>
            @endif
        </div>

    </div>{{-- /rp-body --}}
</div>{{-- /cx-main --}}
</div>{{-- /cx-wrap --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
})();

/* ── Graphe ── */
(function(){
    const ctx = document.getElementById('rpChart');
    if (!ctx) return;
    const labels  = {!! json_encode($ordersChart->pluck('label')) !!};
    const totals  = {!! json_encode($ordersChart->pluck('total')) !!};
    const livrees = {!! json_encode($ordersChart->pluck('livrees')) !!};
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label:'Total', data:totals,  backgroundColor:'rgba(124,58,237,.35)', borderColor:'rgba(167,139,250,.7)', borderWidth:1.5, borderRadius:4 },
                { label:'Livrées', data:livrees, backgroundColor:'rgba(16,185,129,.3)',  borderColor:'rgba(52,211,153,.7)',  borderWidth:1.5, borderRadius:4 },
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:true,
            plugins:{ legend:{ labels:{ color:'#94a3b8', font:{ size:11 } } } },
            scales:{
                x:{ ticks:{ color:'#64748b', font:{ size:10 } }, grid:{ color:'rgba(255,255,255,.04)' } },
                y:{ ticks:{ color:'#64748b', font:{ size:10 } }, grid:{ color:'rgba(255,255,255,.05)' }, beginAtZero:true }
            }
        }
    });
})();
</script>
@endpush
