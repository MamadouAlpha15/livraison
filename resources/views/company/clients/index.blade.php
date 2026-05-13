@extends('layouts.app')
@section('title', 'Clients · ' . $company->name)
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}

:root{
    --cx-bg:      #f5f7fa;
    --cx-surface: #ffffff;
    --cx-surface2:#eef1f7;
    --cx-border:  rgba(0,0,0,.08);
    --cx-brand:   #7c3aed;
    --cx-brand2:  #6d28d9;
    --cx-text:    #111827;
    --cx-text2:   #4b5563;
    --cx-muted:   #9ca3af;
    --cx-green:   #10b981;
    --cx-amber:   #f59e0b;
    --cx-red:     #ef4444;
    --cx-blue:    #3b82f6;
    --r:    12px;
    --r-sm: 9px;
    --r-xs: 6px;
    --sb-w: 220px;
    --top-h:60px;
}

body.cx-dark{
    --cx-bg:      #0b0d22;
    --cx-surface: #0d1226;
    --cx-surface2:#111930;
    --cx-border:  rgba(255,255,255,.07);
    --cx-text:    #e2e8f0;
    --cx-text2:   #94a3b8;
    --cx-muted:   #475569;
}
html,body{margin:0;font-family:'Segoe UI',system-ui,sans-serif;background:var(--cx-bg);color:var(--cx-text);-webkit-font-smoothing:antialiased;transition:background .2s,color .2s;}
a{text-decoration:none;color:inherit;}
body.is-dashboard>nav,body.is-dashboard>header,body.is-dashboard .navbar{display:none!important;}
body.is-dashboard>main.app-main{padding:0!important;margin:0!important;max-width:100%!important;width:100%!important;}

/* ══ STRUCTURE ══ */
.cx-wrap{display:flex;min-height:100vh;background:var(--cx-bg);padding-left:var(--sb-w);}
.cx-main{flex:1;min-width:0;display:flex;flex-direction:column;background:var(--cx-bg);}

/* ══ SIDEBAR ══ */
.cx-sidebar{
    position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);
    background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    display:flex;flex-direction:column;z-index:1200;overflow-y:auto;
    scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.3) transparent;
    transition:transform .25s cubic-bezier(.23,1,.32,1);
    border-right:1px solid rgba(99,102,241,.1);
    box-shadow:6px 0 30px rgba(0,0,0,.35);
}
.cx-sidebar::-webkit-scrollbar{width:3px;}
.cx-sidebar::-webkit-scrollbar-thumb{background:rgba(124,58,237,.3);border-radius:3px;}

.cx-brand-hd{padding:14px 14px 10px;border-bottom:1px solid rgba(255,255,255,.06);flex-shrink:0;display:flex;flex-direction:column;gap:8px;}
.cx-brand-top{display:flex;align-items:center;justify-content:space-between;}
.cx-logo{display:flex;align-items:center;gap:9px;color:#fff;font-size:16px;font-weight:800;}
.cx-logo-icon{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#7c3aed,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.cx-sys-badge{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:600;color:var(--cx-green);padding:3px 8px;border-radius:20px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);}
.cx-sys-dot{width:6px;height:6px;border-radius:50%;background:var(--cx-green);animation:blink 2.2s ease-in-out infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}
.cx-close-btn{display:none;background:none;border:none;color:rgba(255,255,255,.45);font-size:18px;cursor:pointer;padding:2px 6px;border-radius:6px;line-height:1}
.cx-close-btn:hover{color:#fff}

.cx-nav{padding:8px 8px 12px;flex:1}
.cx-nav-sec{font-size:10px;font-weight:800;letter-spacing:1.6px;color:rgba(255,255,255,.58);padding:14px 10px 5px;text-transform:uppercase}
.cx-nav-item{display:flex;align-items:center;gap:10px;padding:8px 11px;border-radius:var(--r-xs);color:rgba(255,255,255,.85);font-size:13.5px;font-weight:600;transition:all .22s;position:relative;cursor:pointer;margin-bottom:2px;border:1px solid transparent}
.cx-nav-item:hover{background:rgba(124,58,237,.18);color:#fff;border-color:rgba(124,58,237,.25);box-shadow:0 2px 12px rgba(124,58,237,.2),inset 0 1px 0 rgba(255,255,255,.06)}
.cx-nav-item.active{background:linear-gradient(90deg,rgba(124,58,237,.35) 0%,rgba(99,102,241,.2) 100%);color:#fff;font-weight:700;border-color:rgba(139,92,246,.3);box-shadow:0 4px 16px rgba(124,58,237,.25),inset 0 1px 0 rgba(255,255,255,.08)}
.cx-nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:22px;background:linear-gradient(180deg,#a78bfa,#7c3aed);border-radius:0 3px 3px 0;box-shadow:2px 0 12px rgba(167,139,250,.7)}
.cx-nav-ico{width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;}
.cx-nav-item.active .cx-nav-ico{background:rgba(139,92,246,.3);border-color:rgba(139,92,246,.4);}
.cx-nav-badge{margin-left:auto;background:var(--cx-brand);color:#fff;font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:20px;min-width:18px;text-align:center}

.cx-user-foot{padding:10px 10px 12px;border-top:1px solid rgba(255,255,255,.07);flex-shrink:0}
.cx-user-row{display:flex;align-items:center;gap:9px;padding:7px 8px;border-radius:var(--r-xs);background:rgba(255,255,255,.04);cursor:pointer;transition:background .15s;margin-bottom:6px}
.cx-user-row:hover{background:rgba(255,255,255,.08)}
.cx-user-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4338ca);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;}
.cx-user-name{font-size:12px;font-weight:700;color:#fff;line-height:1.2}
.cx-user-role{font-size:10px;color:var(--cx-text2)}
.cx-logout-btn{width:30px;height:30px;border-radius:8px;flex-shrink:0;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);display:flex;align-items:center;justify-content:center;color:#f87171;cursor:pointer;transition:background .15s;padding:0;}
.cx-logout-btn:hover{background:rgba(239,68,68,.22);color:#fff}

/* ══ TOPBAR ══ */
.cx-topbar{height:var(--top-h);background:var(--cx-surface);border-bottom:1px solid var(--cx-border);display:flex;align-items:center;gap:12px;padding:0 24px;position:sticky;top:0;z-index:1050;flex-shrink:0;}
.cx-hamburger{display:none;background:none;border:none;color:var(--cx-text2);font-size:20px;cursor:pointer;padding:4px;line-height:1;}
.cx-topbar-title{font-size:15px;font-weight:800;color:var(--cx-text);}
.cx-topbar-sub{font-size:12px;color:var(--cx-muted);}
.cx-tb-right{margin-left:auto;display:flex;align-items:center;gap:8px;}
.cx-tb-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4338ca);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;}
.cx-tb-uname{font-size:12.5px;font-weight:700;color:var(--cx-text);}
.cx-tb-urole{font-size:10px;color:var(--cx-text2);}
body.cx-light .cx-topbar{background:#fff;border-bottom-color:rgba(0,0,0,.07);box-shadow:0 1px 4px rgba(0,0,0,.06);}

/* ══ PAGE ══ */
.cx-content{padding:24px;flex:1;}

/* Stats bar */
.stats-bar{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;}
.stat-card{background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r);padding:18px 20px;display:flex;align-items:center;gap:14px;transition:box-shadow .2s;}
.stat-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.15);}
.stat-ico{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.stat-ico.purple{background:rgba(124,58,237,.15);border:1px solid rgba(124,58,237,.2);}
.stat-ico.green{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.2);}
.stat-ico.amber{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.2);}
.stat-val{font-size:22px;font-weight:800;color:var(--cx-text);line-height:1;}
.stat-lbl{font-size:11.5px;color:var(--cx-text2);margin-top:3px;}

/* Toolbar */
.toolbar{display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap;}
.toolbar-title{font-size:17px;font-weight:800;color:var(--cx-text);flex:1;min-width:140px;}
.search-box{display:flex;align-items:center;gap:8px;background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r-sm);padding:0 12px;height:38px;min-width:220px;transition:border-color .15s;}
.search-box:focus-within{border-color:rgba(124,58,237,.5);}
.search-box input{border:none;background:none;color:var(--cx-text);font-size:13px;outline:none;flex:1;min-width:0;}
.search-box input::placeholder{color:var(--cx-muted);}

/* Clients table */
.clients-table-wrap{background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r);overflow:hidden;}
.clients-table-hd{padding:14px 20px;border-bottom:1px solid var(--cx-border);display:flex;align-items:center;justify-content:space-between;}
.clients-table-title{font-size:13.5px;font-weight:800;color:var(--cx-text);}
.clients-table-count{font-size:11.5px;color:var(--cx-muted);}
.ctbl{width:100%;border-collapse:collapse;}
.ctbl thead th{padding:10px 16px;font-size:10px;font-weight:800;color:var(--cx-muted);text-transform:uppercase;letter-spacing:.7px;text-align:left;border-bottom:1px solid var(--cx-border);white-space:nowrap;background:var(--cx-surface2);}
.ctbl tbody td{padding:13px 16px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle;}
.ctbl tbody tr:last-child td{border-bottom:none;}
.ctbl tbody tr:hover td{background:rgba(124,58,237,.05);}
body.cx-light .ctbl tbody tr:hover td{background:rgba(124,58,237,.04);}

/* Client avatar */
.cli-av{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;}
.cli-name{font-size:13.5px;font-weight:700;color:var(--cx-text);}
.cli-contact{font-size:11.5px;color:var(--cx-text2);margin-top:2px;}

/* Stats pills */
.mini-stat{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px;white-space:nowrap;}
.ms-green{background:rgba(16,185,129,.12);color:#34d399;border:1px solid rgba(16,185,129,.2);}
.ms-amber{background:rgba(245,158,11,.1);color:#fbbf24;border:1px solid rgba(245,158,11,.18);}
.ms-red{background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.18);}
.ms-blue{background:rgba(59,130,246,.1);color:#93c5fd;border:1px solid rgba(59,130,246,.18);}

/* Montant */
.mono{font-family:'Courier New',monospace;font-weight:700;font-size:13px;color:#6d28d9;}

/* Date */
.date-chip{font-size:11px;color:#4b5563;background:#f3f4f6;border:1px solid #e5e7eb;padding:3px 8px;border-radius:6px;white-space:nowrap;}

/* Empty state */
.empty-state{text-align:center;padding:60px 20px;color:var(--cx-text2);}
.empty-ico{font-size:48px;margin-bottom:12px;}
.empty-title{font-size:16px;font-weight:700;color:var(--cx-text);margin-bottom:6px;}
.empty-sub{font-size:13px;}

/* Rank badge */
.rank-badge{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;}
.rank-1{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;box-shadow:0 2px 8px rgba(245,158,11,.4);}
.rank-2{background:linear-gradient(135deg,#94a3b8,#64748b);color:#fff;box-shadow:0 2px 8px rgba(100,116,139,.4);}
.rank-3{background:linear-gradient(135deg,#cd7c2b,#b45309);color:#fff;box-shadow:0 2px 8px rgba(180,120,60,.4);}
.rank-n{background:var(--cx-surface2);color:var(--cx-muted);}

/* Pagination */
.pagination-wrap{margin-top:28px;display:flex;justify-content:center;}


/* ── Desktop / Mobile visibility ── */
.cli-desktop{display:block}
.cli-mobile{display:none}

/* ── Cartes mobile clients ── */
.cli-mc-card{background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r-sm);margin-bottom:12px;overflow:hidden}
.cli-mc-head{display:flex;align-items:center;gap:12px;padding:13px 14px;border-bottom:1px solid var(--cx-border)}
.cli-mc-av{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0}
.cli-mc-name{font-size:14px;font-weight:800;color:var(--cx-text);line-height:1.2}
.cli-mc-phone{font-size:12px;color:var(--cx-text2);margin-top:2px}
.cli-mc-rank{margin-left:auto;flex-shrink:0}
.cli-mc-stats{display:grid;grid-template-columns:repeat(4,1fr);border-bottom:1px solid var(--cx-border)}
.cli-mc-stat{padding:11px 8px;text-align:center;border-right:1px solid var(--cx-border)}
.cli-mc-stat:last-child{border-right:none}
.cli-mc-stat-val{font-size:17px;font-weight:900;color:var(--cx-text);line-height:1}
.cli-mc-stat-lbl{font-size:10px;font-weight:600;color:var(--cx-muted);text-transform:uppercase;letter-spacing:.4px;margin-top:3px}
.cli-mc-foot{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;flex-wrap:wrap;gap:8px}
.cli-mc-montant{font-family:'Courier New',monospace;font-size:16px;font-weight:800;color:#6d28d9}
body.cx-dark .cli-mc-montant{color:#c4b5fd}

/* Responsive */
@media(max-width:1024px){
    .cx-sidebar{transform:translateX(-100%);}
    .cx-sidebar.open{transform:translateX(0);}
    .cx-wrap{padding-left:0;}
    .cx-hamburger{display:flex;}
    .cx-close-btn{display:block;}
    .cx-content{padding:20px 20px 48px;}
}
@media(max-width:768px){
    .cx-content{padding:16px 14px 40px;}
    .stats-bar{grid-template-columns:1fr 1fr 1fr;gap:10px;}
    .stat-card{padding:14px 12px;}
    .stat-val{font-size:18px;}
    .cx-tb-uname,.cx-tb-urole{display:none;}
    .cx-topbar{padding:0 14px;}
    .cx-topbar-sub{display:none;}
}
@media(max-width:640px){
    .cx-content{padding:12px 12px 48px;}
    .stats-bar{grid-template-columns:1fr 1fr;gap:8px;}
    .stat-card{padding:12px 10px;gap:10px;}
    .stat-ico{width:36px;height:36px;font-size:16px;}
    .stat-val{font-size:16px;}
    .stat-lbl{font-size:10.5px;}
    .cx-topbar{padding:0 12px;gap:8px;}
    .cx-topbar-title{font-size:13px;}
    .toolbar{flex-direction:column;align-items:stretch;gap:8px;margin-bottom:14px;}
    .toolbar-title{font-size:15px;}
    .search-box{min-width:0;}
    /* Basculement tableau ↔ cartes */
    .cli-desktop{display:none}
    .cli-mobile{display:block}
}
@media(max-width:480px){
    .stats-bar{grid-template-columns:1fr 1fr;}
    .stat-val{font-size:15px;}
    .cli-mc-stat-val{font-size:15px;}
    .cli-mc-montant{font-size:14px;}
}
@media(max-width:360px){
    .stats-bar{grid-template-columns:1fr 1fr;}
    .stat-card{padding:10px 8px;gap:8px;}
    .stat-val{font-size:14px;}
    .cx-topbar-title{font-size:11.5px;}
    .cli-mc-stats{grid-template-columns:repeat(2,1fr)}
    .cli-mc-stat{padding:9px 6px;}
    .cli-mc-stat-val{font-size:14px;}
}
.cx-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1100;}
.cx-overlay.open{display:block;}
/* Dark mode overrides (hors sidebar qui est toujours dark) */
body.cx-dark .date-chip{color:#94a3b8;background:#1e2440;border-color:rgba(255,255,255,.08);}
body.cx-dark .mono{color:#c4b5fd;}
body.cx-dark .cx-topbar{background:#0d1226;border-bottom-color:rgba(255,255,255,.07);}
body.cx-dark .clients-table-wrap{background:#0d1226;border-color:rgba(255,255,255,.07);}
body.cx-dark .clients-table-hd{border-bottom-color:rgba(255,255,255,.06);}
body.cx-dark .ctbl thead th{background:#111930;}
body.cx-dark .ctbl tbody td{border-bottom-color:rgba(255,255,255,.04);}
body.cx-dark .ctbl tbody tr:hover td{background:rgba(124,58,237,.08);}
body.cx-dark .stat-card{background:#0d1226;border-color:rgba(255,255,255,.07);}
body.cx-dark .toolbar .search-box{background:#0d1226;border-color:rgba(255,255,255,.08);}
body.cx-dark .toolbar .search-box input{color:#e2e8f0;}
</style>
@endpush

@section('content')
@php
    $u   = auth()->user();
    $ini = strtoupper(substr($u->name ?? 'A', 0, 1)) . strtoupper(substr(explode(' ', $u->name ?? 'A ')[1] ?? 'X', 0, 1));
    $devise = $company->currency ?? 'GNF';
    function cliIni(string $n): string {
        $p = explode(' ', $n);
        return strtoupper(substr($p[0],0,1)).strtoupper(substr($p[1]??'X',0,1));
    }
@endphp

{{-- Sidebar --}}
<aside class="cx-sidebar" id="cxSidebar">
    <div class="cx-brand-hd">
        <div class="cx-brand-top">
            <a href="{{ route('company.dashboard') }}" class="cx-logo">
                <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width: 40px;;height: 40px;object-fit:cover;border-radius:9px"></div>
                <span>{{ Str::limit($company->name, 14) }}</span>
            </a>
            <button class="cx-close-btn" id="cxClose">✕</button>
        </div>
        <div class="cx-sys-badge"><span class="cx-sys-dot"></span> Système actif</div>
    </div>

    <nav class="cx-nav">
        <div class="cx-nav-sec">Principal</div>
        <a href="{{ route('company.dashboard') }}" class="cx-nav-item">
            <span class="cx-nav-ico">⊞</span> Tableau de bord
        </a>
        <a href="{{ route('company.chat.inbox') }}" class="cx-nav-item">
            <span class="cx-nav-ico">💬</span> Demandes (Chat)
        </a>
        <a href="{{ route('company.orders.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📦</span> Commandes
        </a>
        <a href="{{ route('company.livraisons.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">🚚</span> Livraisons
        </a>
        <a href="{{ route('company.carte.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">🗺️</span> Carte en direct
        </a>
        <a href="{{ route('company.drivers.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">🚴</span> Chauffeurs
        </a>
        <a href="{{ route('company.boutiques.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">🏪</span> Boutiques
        </a>
        <a href="{{ route('company.clients.index') }}" class="cx-nav-item active">
            <span class="cx-nav-ico">👥</span> Clients
        </a>
       <div class="cx-nav-sec">Gestion</div>
        <a href="{{route('company.zones.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📍</span> Zone de livraison
        </a>
        <a href="{{ route('company.historique.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📊</span> Historique
        </a>
        <a href="{{ route('company.rapport.index') }}" class="cx-nav-item"><span class="cx-nav-ico">📈</span> Rapport</a>

        <div class="cx-nav-sec">Configuration</div>
        <a href="{{ route('company.parametre.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">⚙️</span> Paramètres
        </a>
        <a href="{{ route('company.users.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">👤</span> Utilisateurs
        </a>

    </nav>

    <div class="cx-user-foot">
        {{-- Toggle mode sombre --}}
        <button id="themeToggle" onclick="toggleTheme()" style="
            width:100%;display:flex;align-items:center;gap:10px;
            padding:8px 10px;border-radius:8px;margin-bottom:8px;
            background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);
            color:rgba(255,255,255,.75);font-size:12.5px;font-weight:600;
            cursor:pointer;transition:background .15s;text-align:left;font-family:inherit;">
            <span id="themeIco" style="font-size:15px;">☀️</span>
            <span id="themeLbl">Mode clair</span>
        </button>
        <div class="cx-user-row">
            <div class="cx-user-av">{{ $ini }}</div>
            <div style="flex:1;min-width:0;">
                <div class="cx-user-name">{{ Str::limit($u->name ?? 'Admin', 18) }}</div>
                <div class="cx-user-role">{{ $company->name }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="cx-logout-btn" title="Déconnexion">↩</button>
            </form>
        </div>
    </div>
</aside>

<div class="cx-overlay" id="cxOverlay"></div>

<div class="cx-wrap">
<main class="cx-main">

    <div class="cx-topbar">
        <button class="cx-hamburger" id="cxHamburger">☰</button>
        <div>
            <div class="cx-topbar-title">👥 Clients livrés</div>
            <div class="cx-topbar-sub">{{ $company->name }}</div>
        </div>
        <div class="cx-tb-right">
            <div style="display:flex;align-items:center;gap:8px;padding:5px 10px;">
                <div class="cx-tb-av">{{ $ini }}</div>
                <div>
                    <div class="cx-tb-uname">{{ Str::limit($u->name ?? 'Admin', 16) }}</div>
                    <div class="cx-tb-urole">{{ $company->name }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="cx-content">

        {{-- Stats --}}
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-ico purple">👥</div>
                <div>
                    <div class="stat-val">{{ number_format($stats['total_clients']) }}</div>
                    <div class="stat-lbl">Clients uniques</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-ico green">✅</div>
                <div>
                    <div class="stat-val">{{ number_format($stats['total_livrees']) }}</div>
                    <div class="stat-lbl">Commandes livrées</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-ico amber">🏆</div>
                <div>
                    @if($stats['top_client'])
                    <div class="stat-val" style="font-size:14px;">{{ Str::limit($stats['top_client']->name, 18) }}</div>
                    <div class="stat-lbl">Top client · {{ $stats['top_client']->total_orders }} commandes</div>
                    @else
                    <div class="stat-val">—</div>
                    <div class="stat-lbl">Aucun client encore</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Toolbar --}}
        <form method="GET" action="{{ route('company.clients.index') }}" id="searchForm">
        <div class="toolbar">
            <span class="toolbar-title">Liste des clients</span>
            <div class="search-box">
                <span style="color:var(--cx-muted);font-size:14px;">🔍</span>
                <input type="text" name="search" placeholder="Nom, téléphone, email…"
                       value="{{ $search ?? '' }}"
                       onchange="document.getElementById('searchForm').submit()">
            </div>
            @if($search)
            <a href="{{ route('company.clients.index') }}"
               style="padding:8px 14px;border-radius:var(--r-sm);background:var(--cx-surface);border:1px solid var(--cx-border);color:var(--cx-text2);font-size:12.5px;font-weight:600;">
                ✕ Effacer
            </a>
            @endif
        </div>
        </form>

        {{-- Table DESKTOP --}}
        <div class="cli-desktop">
        <div class="clients-table-wrap">
            <div class="clients-table-hd">
                <span class="clients-table-title">👥 Clients livrés par {{ $company->name }}</span>
                <span class="clients-table-count">{{ $clients->total() }} client(s) · page {{ $clients->currentPage() }}/{{ $clients->lastPage() }}</span>
            </div>

            @if($clients->isEmpty())
            <div class="empty-state">
                <div class="empty-ico">👥</div>
                <div class="empty-title">Aucun client trouvé</div>
                <div class="empty-sub">
                    @if($search)
                        Aucun client ne correspond à « {{ $search }} ».
                    @else
                        Quand des commandes seront livrées, les clients apparaîtront ici.
                    @endif
                </div>
            </div>
            @else
            <table class="ctbl">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th class="hide-mobile">Contact</th>
                        <th>Commandes</th>
                        <th class="hide-mobile">Livrées</th>
                        <th class="hide-mobile">Annulées</th>
                        <th class="hide-mobile">En cours</th>
                        <th>Montant livré</th>
                        <th class="hide-mobile">Dernière commande</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($clients as $i => $client)
                @php
                    $rank = $clients->firstItem() + $i;
                    $rankCls = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-n'));
                    $initials = cliIni($client->name ?? 'CL');
                @endphp
                <tr>
                    <td>
                        <div class="rank-badge {{ $rankCls }}">{{ $rank }}</div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="cli-av">{{ $initials }}</div>
                            <div>
                                <div class="cli-name">{{ $client->name ?? 'Inconnu' }}</div>
                                @php $bp = $client->order_phone ?: $client->phone; @endphp
                                @if($bp)
                                <div class="cli-contact">
                                    <a href="tel:{{ $bp }}" style="text-decoration:none;color:inherit">📞 {{ $bp }}</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="hide-mobile">
                        <div style="font-size:12px;color:var(--cx-text2);">
                            @php $bp = $client->order_phone ?: $client->phone; @endphp
                            @if($bp)
                            <a href="tel:{{ $bp }}" style="display:block;text-decoration:none;color:var(--cx-text2)">📞 {{ $bp }}</a>
                            @endif
                            @if($client->order_address)
                            <div style="margin-top:3px;font-size:11px;color:var(--cx-muted);">📍 {{ Str::limit($client->order_address, 35) }}</div>
                            @endif
                            @if($client->email)
                            <div style="margin-top:2px;font-size:11px;color:var(--cx-muted);">{{ $client->email }}</div>
                            @endif
                            @if(!$bp && !$client->order_address && !$client->email)
                            <span style="color:var(--cx-muted);">—</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="mini-stat ms-blue">📦 {{ $client->total_orders }}</span>
                    </td>
                    <td class="hide-mobile">
                        @if($client->livrees > 0)
                        <span class="mini-stat ms-green">✅ {{ $client->livrees }}</span>
                        @else
                        <span style="color:var(--cx-muted);font-size:12px;">—</span>
                        @endif
                    </td>
                    <td class="hide-mobile">
                        @if($client->annulees > 0)
                        <span class="mini-stat ms-red">❌ {{ $client->annulees }}</span>
                        @else
                        <span style="color:var(--cx-muted);font-size:12px;">—</span>
                        @endif
                    </td>
                    <td class="hide-mobile">
                        @if($client->en_cours > 0)
                        <span class="mini-stat ms-amber">🚚 {{ $client->en_cours }}</span>
                        @else
                        <span style="color:var(--cx-muted);font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($client->total_montant > 0)
                        <span class="mono">{{ number_format($client->total_montant, 0, ',', ' ') }} <span style="font-size:10px;font-weight:500;color:var(--cx-muted);">{{ $devise }}</span></span>
                        @else
                        <span style="color:var(--cx-muted);font-size:12px;">0 {{ $devise }}</span>
                        @endif
                    </td>
                    <td class="hide-mobile">
                        @if($client->derniere_commande)
                        <span class="date-chip">{{ \Carbon\Carbon::parse($client->derniere_commande)->locale('fr')->diffForHumans() }}</span>
                        @else
                        <span style="color:var(--cx-muted);font-size:12px;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
        </div>{{-- /cli-desktop --}}

        {{-- Cartes MOBILE --}}
        <div class="cli-mobile">
            @if($clients->isEmpty())
            <div class="empty-state" style="background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r-sm);">
                <div class="empty-ico">👥</div>
                <div class="empty-title">Aucun client trouvé</div>
                <div class="empty-sub">
                    @if($search) Aucun client ne correspond à « {{ $search }} ».
                    @else Quand des commandes seront livrées, les clients apparaîtront ici.
                    @endif
                </div>
            </div>
            @else
            @foreach($clients as $i => $client)
            @php
                $rank2    = $clients->firstItem() + $i;
                $rankCls2 = $rank2 === 1 ? 'rank-1' : ($rank2 === 2 ? 'rank-2' : ($rank2 === 3 ? 'rank-3' : 'rank-n'));
                $initials2 = cliIni($client->name ?? 'CL');
                $bp2 = $client->order_phone ?: $client->phone;
            @endphp
            <div class="cli-mc-card">

                {{-- En-tête : avatar + nom + rang --}}
                <div class="cli-mc-head">
                    <div class="cli-mc-av">{{ $initials2 }}</div>
                    <div style="flex:1;min-width:0">
                        <div class="cli-mc-name">{{ $client->name ?? 'Inconnu' }}</div>
                        @if($bp2)
                        <div class="cli-mc-phone">
                            <a href="tel:{{ $bp2 }}" style="text-decoration:none;color:inherit">📞 {{ $bp2 }}</a>
                        </div>
                        @endif
                    </div>
                    <div class="cli-mc-rank"><div class="rank-badge {{ $rankCls2 }}">{{ $rank2 }}</div></div>
                </div>

                {{-- Stats : Total / Livrées / Annulées / En cours --}}
                <div class="cli-mc-stats">
                    <div class="cli-mc-stat">
                        <div class="cli-mc-stat-val" style="color:var(--cx-brand)">{{ $client->total_orders }}</div>
                        <div class="cli-mc-stat-lbl">Total</div>
                    </div>
                    <div class="cli-mc-stat">
                        <div class="cli-mc-stat-val" style="color:#34d399">{{ $client->livrees }}</div>
                        <div class="cli-mc-stat-lbl">Livrées</div>
                    </div>
                    <div class="cli-mc-stat">
                        <div class="cli-mc-stat-val" style="color:#f87171">{{ $client->annulees }}</div>
                        <div class="cli-mc-stat-lbl">Annulées</div>
                    </div>
                    <div class="cli-mc-stat">
                        <div class="cli-mc-stat-val" style="color:#fbbf24">{{ $client->en_cours }}</div>
                        <div class="cli-mc-stat-lbl">En cours</div>
                    </div>
                </div>

                {{-- Pied : montant + dernière commande --}}
                <div class="cli-mc-foot">
                    <span class="cli-mc-montant">
                        {{ $client->total_montant > 0 ? number_format($client->total_montant,0,',',' ').' '.$devise : '0 '.$devise }}
                    </span>
                    @if($client->derniere_commande)
                    <span class="date-chip">{{ \Carbon\Carbon::parse($client->derniere_commande)->locale('fr')->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            @endforeach
            @endif
        </div>{{-- /cli-mobile --}}

        {{-- Pagination --}}
        @if($clients->hasPages())
        <div class="pagination-wrap">
            {{ $clients->withQueryString()->links() }}
        </div>
        @endif

    </div>{{-- /cx-content --}}
</main>
</div>{{-- /cx-wrap --}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('cxSidebar');
    const overlay = document.getElementById('cxOverlay');
    const hamburger = document.getElementById('cxHamburger');
    const closeBtn = document.getElementById('cxClose');

    function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('open'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }

    hamburger?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);
    closeBtn?.addEventListener('click', closeSidebar);

    /* Restaure le thème sauvegardé */
    if (localStorage.getItem('cx-theme') === 'dark') {
        document.body.classList.add('cx-dark');
        updateThemeBtn(true);
    }
});

window.toggleTheme = function() {
    const isDark = document.body.classList.toggle('cx-dark');
    localStorage.setItem('cx-theme', isDark ? 'dark' : 'light');
    updateThemeBtn(isDark);
};

function updateThemeBtn(isDark) {
    const ico = document.getElementById('themeIco');
    const lbl = document.getElementById('themeLbl');
    if (!ico || !lbl) return;
    ico.textContent = isDark ? '🌙' : '☀️';
    lbl.textContent = isDark ? 'Mode sombre' : 'Mode clair';
}
</script>
@endpush

@endsection
