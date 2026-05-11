@extends('layouts.app')
@section('title', 'Utilisateurs · ' . $company->name)
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<script>
(function(){
    if(localStorage.getItem('cx-theme')==='dark')
        document.documentElement.classList.add('cx-preusers-dark');
})();
</script>
<style>
html.cx-preusers-dark body{background:#0b0d22!important}
</style>
<style>
*,*::before,*::after{box-sizing:border-box}

:root{
    --cx-bg:     #F5F7FA;
    --cx-card:   #ffffff;
    --cx-card2:  #EEF1F7;
    --cx-border: rgba(0,0,0,.08);
    --cx-border2:rgba(0,0,0,.13);
    --cx-brand:  #7c3aed;
    --cx-brand2: #6d28d9;
    --cx-brand3: #5b21b6;
    --cx-lt:     rgba(124,58,237,.08);
    --cx-text:   #111827;
    --cx-text2:  #4b5563;
    --cx-muted:  #9ca3af;
    --cx-green:  #10b981;
    --cx-amber:  #f59e0b;
    --cx-red:    #ef4444;
    --cx-blue:   #3b82f6;
    --r:  14px;
    --r-sm: 9px;
    --r-xs: 6px;
    --sb-w: 220px;
    --top-h: 58px;
}

body.is-dashboard > nav,
body.is-dashboard > header,
body.is-dashboard .navbar { display:none !important; }

body{font-family:'Segoe UI',system-ui,sans-serif;background:var(--cx-bg);color:var(--cx-text);margin:0;-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}

.cx-wrap { display:flex; min-height:100vh; }

/* ── SIDEBAR ── */
.cx-sidebar{
    position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);
    background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    display:flex;flex-direction:column;
    z-index:1200;overflow-y:auto;
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
.cx-sys-badge{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:600;color:var(--cx-green);padding:3px 8px;border-radius:20px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);}
.cx-sys-dot{width:6px;height:6px;border-radius:50%;background:var(--cx-green);animation:blink 2.2s ease-in-out infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}
.cx-close-btn{display:none;background:none;border:none;color:rgba(255,255,255,.45);font-size:18px;cursor:pointer;padding:2px 6px;border-radius:6px;line-height:1;transition:color .15s;}
.cx-close-btn:hover{color:#fff;}

.cx-nav{padding:8px 8px 12px;flex:1;}
.cx-nav-sec{font-size:10px;font-weight:800;letter-spacing:1.6px;color:rgba(255,255,255,.58);padding:14px 10px 5px;text-transform:uppercase;}
.cx-nav-item{display:flex;align-items:center;gap:10px;padding:8px 11px;border-radius:var(--r-sm);color:rgba(255,255,255,.85);font-size:13.5px;font-weight:600;transition:all .22s cubic-bezier(.23,1,.32,1);position:relative;cursor:pointer;margin-bottom:2px;border:1px solid transparent;}
.cx-nav-item:hover{background:rgba(124,58,237,.18);color:#fff;border-color:rgba(124,58,237,.25);box-shadow:0 2px 12px rgba(124,58,237,.2),inset 0 1px 0 rgba(255,255,255,.06);}
.cx-nav-item:hover .cx-nav-ico{background:rgba(139,92,246,.25);box-shadow:0 0 8px rgba(139,92,246,.3);}
.cx-nav-item.active{background:linear-gradient(90deg,rgba(124,58,237,.35) 0%,rgba(99,102,241,.2) 100%);color:#fff;font-weight:700;border-color:rgba(139,92,246,.3);box-shadow:0 4px 16px rgba(124,58,237,.25),inset 0 1px 0 rgba(255,255,255,.08);}
.cx-nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:22px;background:linear-gradient(180deg,#a78bfa,#7c3aed);border-radius:0 3px 3px 0;box-shadow:2px 0 12px rgba(167,139,250,.7);}
.cx-nav-ico{width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;transition:all .22s cubic-bezier(.23,1,.32,1);}
.cx-nav-item.active .cx-nav-ico{background:rgba(139,92,246,.3);border-color:rgba(139,92,246,.4);box-shadow:0 0 10px rgba(139,92,246,.4);}

.cx-user-foot{padding:10px 10px 12px;border-top:1px solid rgba(255,255,255,.07);flex-shrink:0;}
.cx-user-row{display:flex;align-items:center;gap:9px;padding:7px 8px;border-radius:var(--r-sm);background:rgba(255,255,255,.04);cursor:pointer;transition:background .15s;margin-bottom:6px;}
.cx-user-row:hover{background:rgba(255,255,255,.08);}
.cx-user-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4338ca);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;}
.cx-user-name{font-size:12px;font-weight:700;color:#fff;line-height:1.2;}
.cx-user-role{font-size:10px;color:var(--cx-text2);}
.cx-logout-btn{width:30px;height:30px;border-radius:8px;flex-shrink:0;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);display:flex;align-items:center;justify-content:center;color:#f87171;cursor:pointer;transition:background .15s,color .15s;padding:0;}
.cx-logout-btn:hover{background:rgba(239,68,68,.22);color:#fff;}
.cx-dark-row{display:flex;align-items:center;justify-content:space-between;padding:4px 8px;cursor:pointer;border-radius:var(--r-sm);transition:background .14s;}
.cx-dark-row:hover{background:rgba(255,255,255,.04);}
.cx-dark-lbl{font-size:11.5px;color:rgba(255,255,255,.45);}
.cx-toggle{width:34px;height:18px;background:#475569;border-radius:9px;position:relative;transition:background .25s;flex-shrink:0;}
.cx-toggle::after{content:'';position:absolute;top:3px;left:3px;width:12px;height:12px;background:#fff;border-radius:50%;transition:left .25s;}
.cx-toggle.on{background:var(--cx-brand);}
.cx-toggle.on::after{left:19px;}

.cx-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1100;}
.cx-overlay.open{display:block;}

/* ── MAIN ── */
.cx-main{margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;flex:1;min-width:0;background:var(--cx-bg);}

/* ── TOPBAR ── */
.cx-topbar{height:var(--top-h);background:var(--cx-card);border-bottom:1px solid var(--cx-border);display:flex;align-items:center;gap:12px;padding:0 20px;position:sticky;top:0;z-index:1050;flex-shrink:0;box-shadow:0 1px 0 rgba(0,0,0,.06);}
.cx-hamburger{display:none;background:none;border:none;color:var(--cx-text);font-size:20px;cursor:pointer;padding:4px;line-height:1;}
.cx-topbar-title{font-size:15px;font-weight:800;color:var(--cx-text);display:flex;align-items:center;gap:8px;}
.cx-tb-right{display:flex;align-items:center;gap:10px;margin-left:auto;}
.cx-tb-user{display:flex;align-items:center;gap:8px;cursor:pointer;padding:5px 10px;border-radius:var(--r-sm);transition:background .14s;flex-shrink:0;}
.cx-tb-user:hover{background:rgba(0,0,0,.04);}
.cx-tb-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4338ca);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;}
.cx-tb-uname{font-size:12.5px;font-weight:700;color:var(--cx-text);line-height:1.2;}
.cx-tb-urole{font-size:10px;color:var(--cx-text2);}
.cx-tb-btn{width:36px;height:36px;border-radius:10px;background:rgba(0,0,0,.04);border:1px solid rgba(0,0,0,.08);display:flex;align-items:center;justify-content:center;color:var(--cx-text2);font-size:16px;cursor:pointer;transition:background .14s,color .14s;flex-shrink:0;text-decoration:none;}
.cx-tb-btn:hover{background:rgba(0,0,0,.08);color:var(--cx-text);}

/* ── PAGE BANNER ── */
.page-banner{
    background:linear-gradient(135deg,#1e1b4b 0%,#312e81 40%,#4338ca 100%);
    padding:28px 28px 24px;position:relative;overflow:hidden;
}
.page-banner::before{
    content:'';position:absolute;top:-60px;right:-80px;
    width:300px;height:300px;border-radius:50%;
    background:radial-gradient(circle,rgba(124,58,237,.18) 0%,transparent 70%);
    pointer-events:none;
}
.banner-grid{
    position:absolute;inset:0;pointer-events:none;
    background-image:
        linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
    background-size:40px 40px;
}
.banner-inner{max-width:900px;margin:0 auto;position:relative;z-index:1;}
.banner-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
.banner-title{font-size:22px;font-weight:900;color:#fff;letter-spacing:-.4px;display:flex;align-items:center;gap:10px;}
.banner-sub{font-size:13px;color:rgba(255,255,255,.55);margin-top:4px;}

/* ── BODY ── */
.pw{max-width:900px;margin:0 auto;padding:24px 24px 80px;}

/* ── ALERT ── */
.alert{padding:12px 16px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
.alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#065f46;}
.alert-error  {background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#991b1b;}

/* ── CARDS ── */
.section-card{background:var(--cx-card);border:1px solid var(--cx-border);border-radius:var(--r);margin-bottom:24px;overflow:hidden;}
.section-hd{padding:18px 22px;border-bottom:1px solid var(--cx-border);display:flex;align-items:center;gap:10px;}
.section-hd-icon{font-size:18px;}
.section-hd-title{font-size:15px;font-weight:800;color:var(--cx-text);}
.section-hd-sub{font-size:12px;color:var(--cx-muted);margin-top:2px;}
.section-body{padding:20px 22px;}

/* ── ADD FORM ── */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-group{display:flex;flex-direction:column;gap:6px;}
.form-group.full{grid-column:1/-1;}
.form-label{font-size:11.5px;font-weight:700;color:var(--cx-text2);text-transform:uppercase;letter-spacing:.5px;}
.form-input{
    width:100%;padding:10px 14px;background:var(--cx-card2);border:1.5px solid var(--cx-border);
    border-radius:var(--r-sm);color:var(--cx-text);font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s;
}
.form-input:focus{border-color:var(--cx-brand);box-shadow:0 0 0 3px rgba(124,58,237,.12);}
.form-input::placeholder{color:var(--cx-muted);}
.form-error{font-size:12px;color:#dc2626;font-weight:600;}
.btn-primary{
    padding:11px 24px;background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2));
    color:#fff;border:none;border-radius:var(--r-sm);font-size:13.5px;font-weight:800;font-family:inherit;
    cursor:pointer;box-shadow:0 4px 14px rgba(124,58,237,.3);transition:all .18s;
    display:inline-flex;align-items:center;gap:8px;
}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(124,58,237,.45);}

/* ── MEMBERS TABLE ── */
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
thead th{
    padding:11px 16px;text-align:left;
    font-size:11.5px;font-weight:700;color:var(--cx-muted);
    text-transform:uppercase;letter-spacing:.6px;
    background:var(--cx-card2);border-bottom:2px solid var(--cx-border);white-space:nowrap;
}
tbody tr{border-bottom:1px solid var(--cx-border);transition:background .12s;}
tbody tr:last-child{border-bottom:none;}
tbody tr:hover{background:rgba(124,58,237,.04);}
td{padding:14px 16px;font-size:14px;vertical-align:middle;}

.member-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4338ca);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;}
.member-name{font-size:14px;font-weight:700;color:var(--cx-text);}
.member-email{font-size:12px;color:var(--cx-muted);margin-top:2px;}
.member-cell{display:flex;align-items:center;gap:10px;}

.badge-role{display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;
    background:rgba(124,58,237,.1);border:1px solid rgba(124,58,237,.25);color:#6d28d9;}
.badge-owner{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);color:#92400e;}
.badge-pass{display:inline-flex;align-items:center;gap:4px;font-size:11.5px;font-weight:600;padding:3px 9px;border-radius:20px;
    background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.18);color:#b91c1c;}

.btn-rm{
    padding:6px 12px;border-radius:var(--r-xs);border:1.5px solid #fca5a5;
    background:#fee2e2;color:#991b1b;font-size:12px;font-weight:700;
    cursor:pointer;font-family:inherit;transition:all .13s;white-space:nowrap;
}
.btn-rm:hover{background:#fecaca;}

.empty-state{padding:48px 24px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:10px;}
.empty-ico{font-size:40px;opacity:.25;}
.empty-txt{font-size:14px;font-weight:700;color:var(--cx-text);}
.empty-sub{font-size:12.5px;color:var(--cx-muted);}

/* ── READONLY INFO ── */
.readonly-note{padding:12px 16px;background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.2);border-radius:var(--r-sm);font-size:13px;color:#1d4ed8;font-weight:600;display:flex;align-items:center;gap:8px;}

/* ── DARK MODE ── */
body.cx-dark{
    --cx-bg:#0b0d22;--cx-card:#0d1226;--cx-card2:#111930;
    --cx-border:rgba(255,255,255,.07);--cx-border2:rgba(255,255,255,.12);
    --cx-text:#e2e8f0;--cx-text2:#94a3b8;--cx-muted:#475569;
    background:var(--cx-bg)!important;
}
body.cx-dark .cx-main{background:var(--cx-bg);}
body.cx-dark .cx-topbar{background:var(--cx-card);border-bottom-color:var(--cx-border);box-shadow:none;}
body.cx-dark .cx-topbar-title{color:var(--cx-text);}
body.cx-dark .cx-tb-user:hover{background:rgba(255,255,255,.06);}
body.cx-dark .cx-tb-uname{color:var(--cx-text);}
body.cx-dark .cx-tb-urole{color:var(--cx-text2);}
body.cx-dark .cx-tb-btn{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.08);color:var(--cx-text2);}
body.cx-dark .cx-tb-btn:hover{background:rgba(255,255,255,.1);color:#fff;}
body.cx-dark .section-card{background:var(--cx-card);border-color:var(--cx-border);}
body.cx-dark .section-hd{border-bottom-color:var(--cx-border);}
body.cx-dark .section-hd-title{color:var(--cx-text);}
body.cx-dark .form-input{background:var(--cx-card2);border-color:var(--cx-border);color:var(--cx-text);}
body.cx-dark .form-input::placeholder{color:var(--cx-muted);}
body.cx-dark .form-label{color:var(--cx-text2);}
body.cx-dark thead th{background:var(--cx-card2);border-bottom-color:var(--cx-border);color:var(--cx-muted);}
body.cx-dark tbody tr{border-bottom-color:var(--cx-border);}
body.cx-dark tbody tr:hover{background:rgba(124,58,237,.07);}
body.cx-dark .member-name{color:var(--cx-text);}
body.cx-dark .badge-role{background:rgba(124,58,237,.18);color:#a78bfa;border-color:rgba(124,58,237,.35);}
body.cx-dark .badge-owner{background:rgba(245,158,11,.15);color:#fbbf24;border-color:rgba(245,158,11,.3);}
body.cx-dark .readonly-note{background:rgba(59,130,246,.08);border-color:rgba(59,130,246,.25);color:#60a5fa;}
body.cx-dark .alert-success{background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.25);color:#34d399;}
body.cx-dark .alert-error{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.25);color:#f87171;}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
    .cx-sidebar{transform:translateX(-100%);}
    .cx-sidebar.open{transform:translateX(0);}
    .cx-main{margin-left:0;}
    .cx-hamburger{display:flex;}
    .cx-close-btn{display:block;}
}
@media(max-width:768px){
    .cx-topbar{padding:0 14px;}
    .cx-tb-urole{display:none;}
    .cx-tb-uname{font-size:11.5px;}
    .form-grid{grid-template-columns:1fr;}
    .form-group.full{grid-column:unset;}
}
@media(max-width:640px){
    .pw{padding:14px 12px 60px;}
    .page-banner{padding:18px 14px 16px;}
    .banner-title{font-size:17px;}
    .banner-sub{font-size:11.5px;}
    .section-hd,.section-body{padding:14px 14px;}
    /* Table → card layout on mobile */
    .table-wrap{overflow-x:unset;}
    .table-wrap table,.table-wrap tbody{display:block;}
    .table-wrap thead{display:none;}
    .table-wrap tbody tr{
        display:block;border:1px solid var(--cx-border);
        border-radius:var(--r-sm);margin-bottom:10px;
        padding:12px 14px;background:var(--cx-card);
        box-shadow:0 1px 4px rgba(0,0,0,.06);
    }
    .table-wrap td{
        display:flex;align-items:flex-start;
        padding:6px 0;border:none;font-size:14px;gap:10px;
    }
    .table-wrap td::before{
        content:attr(data-label);
        font-size:11px;font-weight:800;color:var(--cx-muted);
        text-transform:uppercase;letter-spacing:.6px;
        min-width:80px;flex-shrink:0;padding-top:2px;
    }
    .table-wrap td[data-label="Action"]{
        margin-top:8px;border-top:1px solid var(--cx-border);padding-top:12px;
    }
    .table-wrap td[data-label="Action"]::before{display:none;}
    body.cx-dark .table-wrap tbody tr{background:var(--cx-card);border-color:var(--cx-border);}
}
@media(max-width:480px){
    .cx-topbar-title span{display:none;}
}
</style>
@endpush

@section('content')
@php
    $u    = auth()->user();
    $parts= explode(' ', $u->name ?? 'Admin');
    $ini  = strtoupper(substr($parts[0],0,1)).(isset($parts[1])?strtoupper(substr($parts[1],0,1)):'');
@endphp

<div class="cx-wrap">

{{-- ══ SIDEBAR ══ --}}
<aside class="cx-sidebar" id="cxSidebar">

    <div class="cx-brand-hd">
        <div class="cx-brand-top">
            <a href="{{ route('company.dashboard') }}" class="cx-logo">
                <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width:40px;height:40px;object-fit:cover;border-radius:9px"></div>
                <span class="sb-shop-name">{{ $company->name }}</span>
            </a>
            <button class="cx-close-btn" id="cxClose">✕</button>
        </div>
        <div class="cx-sys-badge">
            <span class="cx-sys-dot"></span> Système actif
        </div>
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
        <a href="{{ route('company.clients.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">👥</span> Clients
        </a>

        <div class="cx-nav-sec">Gestion</div>
        <a href="{{ route('company.zones.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📍</span> Zone de livraison
        </a>
        <a href="{{ route('company.historique.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📊</span> Historique
        </a>

        <div class="cx-nav-sec">Configuration</div>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">⚙️</span> Paramètres
        </a>
        <a href="{{ route('company.users.index') }}" class="cx-nav-item active">
            <span class="cx-nav-ico">👤</span> Utilisateurs
        </a>
    </nav>

    <div class="cx-user-foot">
        <div class="cx-user-row">
            <div class="cx-user-av">{{ $ini }}</div>
            <div style="flex:1;min-width:0">
                <div class="cx-user-name">{{ Str::limit($u->name ?? 'Admin', 18) }}</div>
                <div class="cx-user-role">{{ $isOwner ? 'Propriétaire' : 'Membre' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="flex-shrink:0">
                @csrf
                <button type="submit" class="cx-logout-btn" title="Se déconnecter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
        <div class="cx-dark-row" id="cxDarkToggle">
            <span class="cx-dark-lbl">Mode sombre</span>
            <div class="cx-toggle" id="cxDarkSwitch"></div>
        </div>
    </div>
</aside>

<div class="cx-overlay" id="cxOverlay"></div>

{{-- ══ MAIN ══ --}}
<main class="cx-main">

    {{-- TOPBAR --}}
    <div class="cx-topbar">
        <button class="cx-hamburger" id="cxHamburger">☰</button>
        <div class="cx-topbar-title">👤 <span>Utilisateurs</span></div>
        <div class="cx-tb-right">
            <a href="{{ route('company.chat.inbox') }}" class="cx-tb-btn" title="Chat">💬</a>
            <div class="cx-tb-user">
                <div class="cx-tb-av">{{ $ini }}</div>
                <div>
                    <div class="cx-tb-uname">{{ Str::limit($u->name ?? 'Admin', 16) }}</div>
                    <div class="cx-tb-urole">{{ $company->name }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- BANNER --}}
    <div class="page-banner">
        <div class="banner-grid"></div>
        <div class="banner-inner">
            <div class="banner-top">
                <div>
                    <div class="banner-title">👤 Utilisateurs</div>
                    <div class="banner-sub">{{ $company->name }} · Gestion des accès à votre espace entreprise</div>
                </div>
            </div>
        </div>
    </div>

    <div class="pw">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif

        {{-- ── ADD USER FORM (owner only) ── --}}
        @if($isOwner)
        <div class="section-card">
            <div class="section-hd">
                <span class="section-hd-icon">➕</span>
                <div>
                    <div class="section-hd-title">Ajouter un utilisateur</div>
                    <div class="section-hd-sub">Cet utilisateur pourra accéder au panel de {{ $company->name }}</div>
                </div>
            </div>
            <div class="section-body">
                <form method="POST" action="{{ route('company.users.store') }}" id="addForm">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nom complet</label>
                            <input class="form-input" type="text" name="name" value="{{ old('name') }}" placeholder="Ex: Mamadou Diallo" required>
                            @error('name')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Adresse e-mail</label>
                            <input class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="utilisateur@email.com" required>
                            @error('email')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Téléphone (optionnel)</label>
                            <input class="form-input" type="text" name="phone" value="{{ old('phone') }}" placeholder="+224 6xx xxx xxx">
                            @error('phone')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mot de passe</label>
                            <input class="form-input" type="password" name="password" placeholder="Minimum 8 caractères" required>
                            @error('password')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmer le mot de passe</label>
                            <input class="form-input" type="password" name="password_confirmation" placeholder="Répéter le mot de passe" required>
                        </div>
                        <div class="form-group" style="display:flex;align-items:flex-end;">
                            <button type="submit" class="btn-primary">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Ajouter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="readonly-note">
            ℹ️ Seul le propriétaire de l'entreprise peut ajouter ou retirer des membres.
        </div>
        @endif

        {{-- ── MEMBERS LIST ── --}}
        <div class="section-card">
            <div class="section-hd">
                <span class="section-hd-icon">👥</span>
                <div>
                    <div class="section-hd-title">Membres avec accès</div>
                    <div class="section-hd-sub">{{ $members->count() }} membre{{ $members->count() > 1 ? 's' : '' }} ajouté{{ $members->count() > 1 ? 's' : '' }}</div>
                </div>
            </div>

            @if($members->isEmpty())
            <div class="empty-state">
                <div class="empty-ico">👤</div>
                <div class="empty-txt">Aucun membre pour l'instant</div>
                <div class="empty-sub">Ajoutez des utilisateurs pour qu'ils puissent accéder au panel.</div>
            </div>
            @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Téléphone</th>
                            <th>Statut</th>
                            <th>Ajouté le</th>
                            @if($isOwner)<th>Action</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                        @php
                            $mParts = explode(' ', $member->name ?? '?');
                            $mIni   = strtoupper(substr($mParts[0],0,1)).(isset($mParts[1])?strtoupper(substr($mParts[1],0,1)):'');
                        @endphp
                        <tr>
                            <td data-label="Utilisateur">
                                <div class="member-cell">
                                    <div class="member-av">{{ $mIni }}</div>
                                    <div>
                                        <div class="member-name">{{ $member->name }}</div>
                                        <div class="member-email">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Téléphone">
                                @if($member->phone)
                                <a href="tel:{{ $member->phone }}" style="color:var(--cx-text2);text-decoration:none;">{{ $member->phone }}</a>
                                @else
                                <span style="color:var(--cx-muted);font-style:italic;font-size:13px;">—</span>
                                @endif
                            </td>
                            <td data-label="Statut">
                                <span class="badge-role">Membre</span>
                                @if($member->must_change_password)
                                <span class="badge-pass" style="margin-left:4px;">⚠️ Doit changer mdp</span>
                                @endif
                            </td>
                            <td data-label="Ajouté le" style="font-size:13px;color:var(--cx-text2);">
                                {{ $member->created_at->format('d/m/Y') }}
                            </td>
                            @if($isOwner)
                            <td data-label="Action">
                                <form method="POST" action="{{ route('company.users.destroy', $member) }}" onsubmit="return confirm('Retirer l\'accès de {{ addslashes($member->name) }} ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-rm">✕ Retirer</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Owner info card --}}
        <div class="section-card">
            <div class="section-hd">
                <span class="section-hd-icon">👑</span>
                <div>
                    <div class="section-hd-title">Propriétaire</div>
                    <div class="section-hd-sub">Accès complet · non modifiable</div>
                </div>
            </div>
            <div class="section-body">
                @php $owner = $company->user; @endphp
                @if($owner)
                @php
                    $oParts = explode(' ', $owner->name ?? '?');
                    $oIni   = strtoupper(substr($oParts[0],0,1)).(isset($oParts[1])?strtoupper(substr($oParts[1],0,1)):'');
                @endphp
                <div class="member-cell">
                    <div class="member-av" style="background:linear-gradient(135deg,#f59e0b,#d97706);">{{ $oIni }}</div>
                    <div>
                        <div class="member-name">{{ $owner->name }}</div>
                        <div class="member-email">{{ $owner->email }}@if($owner->phone) · {{ $owner->phone }}@endif</div>
                    </div>
                    <span class="badge-owner" style="margin-left:auto;">👑 Propriétaire</span>
                </div>
                @endif
            </div>
        </div>

    </div>{{-- /pw --}}
</main>{{-- /cx-main --}}
</div>{{-- /cx-wrap --}}

@endsection

@push('scripts')
<script>
/* ── Mode sombre ── */
(function initTheme(){
    const sw   = document.getElementById('cxDarkSwitch');
    const row  = document.getElementById('cxDarkToggle');
    const lbl  = row?.querySelector('.cx-dark-lbl');
    const body = document.body;
    const saved = localStorage.getItem('cx-theme') || 'light';
    const apply = (theme) => {
        if(theme === 'dark'){
            body.classList.add('cx-dark');
            sw?.classList.add('on');
            if(lbl) lbl.textContent = 'Mode sombre';
        } else {
            body.classList.remove('cx-dark');
            sw?.classList.remove('on');
            if(lbl) lbl.textContent = 'Mode clair';
        }
    };
    apply(saved);
    document.documentElement.classList.remove('cx-preusers-dark');
    row?.addEventListener('click', () => {
        const isDark = body.classList.toggle('cx-dark');
        sw?.classList.toggle('on', isDark);
        if(lbl) lbl.textContent = isDark ? 'Mode sombre' : 'Mode clair';
        localStorage.setItem('cx-theme', isDark ? 'dark' : 'light');
    });
})();

/* ── Sidebar toggle ── */
(function(){
    const sidebar = document.getElementById('cxSidebar');
    const overlay = document.getElementById('cxOverlay');
    const open  = () => { sidebar.classList.add('open'); overlay.classList.add('open'); };
    const close = () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); };
    document.getElementById('cxHamburger')?.addEventListener('click', open);
    document.getElementById('cxClose')?.addEventListener('click', close);
    overlay?.addEventListener('click', close);
    document.addEventListener('keydown', e => { if(e.key==='Escape') close(); });
})();
</script>
@endpush
