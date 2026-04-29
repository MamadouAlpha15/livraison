@extends('layouts.app')
@section('title', 'Carte en direct · ' . $company->name)
@php
    $bodyClass = 'cx-dashboard';
    $u   = auth()->user();
    $ini = strtoupper(substr($u->name ?? 'A', 0, 1)) . strtoupper(substr(explode(' ', $u->name ?? 'A ')[1] ?? 'X', 0, 1));
@endphp

@push('styles')
<script>(function(){ if(localStorage.getItem('cx-theme')==='light') document.documentElement.classList.add('cx-prelight'); })();</script>
<style>html.cx-prelight body{background:#F5F7FA!important}</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --cx-bg:#0b0d22;--cx-surface:#0d1226;--cx-surface2:#111930;
    --cx-border:rgba(255,255,255,.07);--cx-brand:#7c3aed;--cx-brand2:#6d28d9;
    --cx-text:#e2e8f0;--cx-text2:#94a3b8;--cx-muted:#475569;
    --cx-green:#10b981;--cx-amber:#f59e0b;--cx-red:#ef4444;--cx-blue:#3b82f6;
    --cx-sb-w:220px;--cx-topbar-h:56px;--r:16px;--r-sm:10px;--r-xs:7px;
}
html,body{margin:0;font-family:'Segoe UI',system-ui,sans-serif;background:var(--cx-bg)!important;color:var(--cx-text);-webkit-font-smoothing:antialiased;height:100%;overflow:hidden}
a{text-decoration:none;color:inherit}

body.cx-dashboard>nav,body.cx-dashboard>header,body.cx-dashboard .navbar,
body.cx-dashboard>.topbar-global,body.cx-dashboard .app-footer,
body.cx-dashboard .app-flash{display:none!important}
body.cx-dashboard>main.app-main{padding:0!important;margin:0!important;max-width:100%!important;width:100%!important}

/* ── LAYOUT ── */
.cx-wrap{display:flex;height:100vh;height:100dvh;padding-left:var(--cx-sb-w);overflow:hidden;transition:padding-left .25s}

/* ── SIDEBAR ── */
.cx-sidebar{
    position:fixed;top:0;left:0;bottom:0;width:var(--cx-sb-w);
    background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    display:flex;flex-direction:column;
    border-right:1px solid rgba(99,102,241,.15);
    box-shadow:6px 0 30px rgba(0,0,0,.35);
    z-index:1200;overflow-y:auto;
    scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.3) transparent;
    transition:transform .25s cubic-bezier(.23,1,.32,1)
}
.cx-sidebar::-webkit-scrollbar{width:3px}
.cx-sidebar::-webkit-scrollbar-thumb{background:rgba(124,58,237,.3);border-radius:3px}
.cx-sidebar a{color:#c7d2fe;transition:all .2s}
.cx-sidebar a:hover{background:rgba(99,102,241,.15);color:#fff}
.cx-sidebar a.active{background:linear-gradient(90deg,#6366f1,#4f46e5);color:#fff}
.cx-brand-hd{padding:14px 14px 10px;border-bottom:1px solid rgba(255,255,255,.06);flex-shrink:0}
.cx-brand-top{display:flex;align-items:center;justify-content:space-between}
.cx-logo{display:flex;align-items:center;gap:9px;color:#fff;font-size:16px;font-weight:800}
.cx-logo-icon{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#7c3aed,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.cx-sys-badge{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:600;color:var(--cx-green);padding:3px 8px;border-radius:20px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);margin-top:8px}
.cx-sys-dot{width:6px;height:6px;border-radius:50%;background:var(--cx-green);animation:blink 2.2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}
.cx-close-btn{display:none;background:none;border:none;color:rgba(255,255,255,.45);font-size:18px;cursor:pointer;padding:2px 6px;border-radius:6px;line-height:1}
.cx-close-btn:hover{color:#fff}
.cx-nav{padding:8px 8px 12px;flex:1}
.cx-nav-sec{font-size:9px;font-weight:800;letter-spacing:1.8px;color:rgba(255,255,255,.3);padding:14px 10px 4px;text-transform:uppercase}
.cx-nav-item{display:flex;align-items:center;gap:9px;padding:7px 10px;border-radius:var(--r-xs);color:rgba(255,255,255,.68);font-size:13px;font-weight:500;transition:background .14s,color .14s;position:relative;cursor:pointer;margin-bottom:1px}
.cx-nav-item:hover{background:rgba(255,255,255,.06);color:#fff}
.cx-nav-item.active{background:rgba(124,58,237,.22);color:#fff;font-weight:700}
.cx-nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:18px;background:#8b5cf6;border-radius:0 3px 3px 0;box-shadow:2px 0 8px rgba(139,92,246,.5)}
.cx-nav-ico{width:24px;height:24px;border-radius:6px;background:rgba(255,255,255,.05);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0}
.cx-nav-item.active .cx-nav-ico{background:rgba(139,92,246,.18)}
.cx-user-foot{padding:10px 10px 12px;border-top:1px solid rgba(255,255,255,.07);flex-shrink:0}
.cx-user-row{display:flex;align-items:center;gap:9px;padding:7px 8px;border-radius:var(--r-xs);background:rgba(255,255,255,.04);cursor:pointer;transition:background .15s;margin-bottom:6px}
.cx-user-row:hover{background:rgba(255,255,255,.08)}
.cx-user-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4338ca);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0}
.cx-user-name{font-size:12px;font-weight:700;color:#fff;line-height:1.2}
.cx-user-role{font-size:10px;color:var(--cx-text2)}
.cx-logout-btn{width:30px;height:30px;border-radius:8px;flex-shrink:0;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);display:flex;align-items:center;justify-content:center;color:#f87171;cursor:pointer;transition:background .15s,color .15s;padding:0}
.cx-logout-btn:hover{background:rgba(239,68,68,.22);color:#fff}
.cx-dark-row{display:flex;align-items:center;justify-content:space-between;padding:4px 8px;cursor:pointer}
.cx-dark-lbl{font-size:11.5px;color:var(--cx-text2)}
.cx-toggle{width:34px;height:18px;background:#475569;border-radius:9px;position:relative;transition:background .25s;flex-shrink:0}
.cx-toggle::after{content:'';position:absolute;top:3px;left:3px;width:12px;height:12px;background:#fff;border-radius:50%;transition:left .25s}
.cx-toggle.on{background:var(--cx-brand)}
.cx-toggle.on::after{left:19px}
.cx-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1100}
.cx-overlay.open{display:block}

/* ── MAIN ── */
.cx-main{flex:1;min-width:0;display:flex;flex-direction:column;overflow:hidden}
.cx-topbar{
    height:var(--cx-topbar-h);background:var(--cx-surface);
    border-bottom:1px solid var(--cx-border);
    display:flex;align-items:center;gap:10px;padding:0 16px;
    flex-shrink:0;z-index:100;
}
.cx-hamburger{display:none;background:none;border:none;color:var(--cx-text2);font-size:18px;cursor:pointer;padding:4px 8px;border-radius:6px;flex-shrink:0}
.cx-topbar-title{font-size:14px;font-weight:800;color:var(--cx-text);white-space:nowrap}
.cx-topbar-sub{font-size:12px;color:var(--cx-muted);margin-left:2px;white-space:nowrap}
.cx-tb-right{margin-left:auto;display:flex;align-items:center;gap:8px;flex-shrink:0}
.cx-tb-btn{height:32px;padding:0 11px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid var(--cx-border);color:var(--cx-text2);font-size:12px;font-weight:600;cursor:pointer;transition:background .14s,color .14s;display:flex;align-items:center;gap:5px;white-space:nowrap}
.cx-tb-btn:hover{background:rgba(255,255,255,.1);color:#fff}
.cx-pulse{display:flex;align-items:center;gap:6px;font-size:11.5px;color:var(--cx-muted)}
.cx-pulse-dot{width:7px;height:7px;border-radius:50%;background:var(--cx-green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
.cx-pulse-txt{white-space:nowrap}

/* bouton panneau dans la topbar (tablette) */
.cx-panel-toggle{
    display:none;height:32px;padding:0 10px;border-radius:8px;
    background:rgba(124,58,237,.15);border:1px solid rgba(124,58,237,.3);
    color:#c4b5fd;font-size:12px;font-weight:700;cursor:pointer;
    align-items:center;gap:5px;white-space:nowrap;flex-shrink:0;
}
.cx-panel-toggle:hover{background:rgba(124,58,237,.25)}

/* ── BODY ── */
.map-body{display:flex;flex:1;overflow:hidden;position:relative}

/* Panneau latéral livraisons */
.map-panel{
    width:300px;flex-shrink:0;
    background:var(--cx-surface);border-right:1px solid var(--cx-border);
    display:flex;flex-direction:column;overflow:hidden;
    transition:transform .3s cubic-bezier(.23,1,.32,1),width .3s;
}
.map-panel-drag{display:none;width:40px;height:4px;background:rgba(255,255,255,.18);border-radius:2px;margin:10px auto 0;flex-shrink:0}
.map-panel-hd{padding:14px 16px 10px;border-bottom:1px solid var(--cx-border);flex-shrink:0}
.map-panel-title{font-size:13px;font-weight:800;color:var(--cx-text);margin-bottom:2px}
.map-panel-sub{font-size:11px;color:var(--cx-muted)}
.map-panel-list{flex:1;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.2) transparent}
.map-panel-list::-webkit-scrollbar{width:3px}
.map-panel-list::-webkit-scrollbar-thumb{background:rgba(124,58,237,.2);border-radius:3px}

/* Carte livraison dans le panneau */
.mp-card{padding:12px 14px;border-bottom:1px solid var(--cx-border);cursor:pointer;transition:background .12s;position:relative}
.mp-card:hover{background:rgba(255,255,255,.04)}
.mp-card.selected{background:rgba(124,58,237,.1);border-left:3px solid var(--cx-brand)}
.mp-card-top{display:flex;align-items:center;gap:8px;margin-bottom:6px}
.mp-color{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.mp-id{font-size:11px;font-weight:800;color:var(--cx-brand)}
.mp-shop{font-size:13px;font-weight:700;color:var(--cx-text);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mp-badge{font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;flex-shrink:0}
.mp-badge-liv{background:rgba(245,158,11,.15);color:#fbbf24;border:1px solid rgba(245,158,11,.25)}
.mp-badge-conf{background:rgba(59,130,246,.12);color:#60a5fa;border:1px solid rgba(59,130,246,.2)}
.mp-driver{font-size:12px;color:var(--cx-text2);display:flex;align-items:center;gap:5px}
.mp-dest{font-size:11.5px;color:var(--cx-muted);margin-top:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mp-ping{font-size:10.5px;color:var(--cx-muted);margin-top:4px;display:flex;align-items:center;gap:4px}
.mp-ping-ok{color:var(--cx-green)}
.mp-ping-stale{color:var(--cx-amber)}
.mp-no-gps{font-size:10.5px;color:var(--cx-muted);font-style:italic;margin-top:4px}
.mp-empty{padding:40px 20px;text-align:center;color:var(--cx-muted)}
.mp-empty-ico{font-size:36px;margin-bottom:10px}
.mp-empty-title{font-size:14px;font-weight:700;color:var(--cx-text);margin-bottom:4px}

/* Carte Leaflet */
.map-container{flex:1;position:relative;min-width:0}
#map{width:100%;height:100%}
/* Reset Leaflet images — évite que Bootstrap applique max-width:100% aux tuiles */
#map img,.leaflet-container img,.leaflet-tile{max-width:none!important;height:auto}

/* Bouton fermer le panneau (visible tablette+mobile) */
.map-panel-close{
    display:none;position:absolute;top:12px;right:12px;
    width:28px;height:28px;border-radius:8px;
    background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);
    color:rgba(255,255,255,.6);font-size:14px;cursor:pointer;
    align-items:center;justify-content:center;
    transition:background .15s,color .15s;flex-shrink:0;
    z-index:1;
}
.map-panel-close:hover{background:rgba(239,68,68,.2);color:#f87171;border-color:rgba(239,68,68,.25)}
body.cx-light .map-panel-close{background:rgba(0,0,0,.06);border-color:rgba(0,0,0,.10);color:#6b7280}
body.cx-light .map-panel-close:hover{background:rgba(239,68,68,.1);color:#dc2626}

/* FAB mobile — liste chauffeurs */
.map-fab{
    display:none;
    position:absolute;bottom:20px;left:50%;transform:translateX(-50%);
    z-index:450;
    background:var(--cx-brand);color:#fff;
    border:none;border-radius:30px;
    padding:10px 20px;font-size:13px;font-weight:800;
    cursor:pointer;gap:8px;align-items:center;
    box-shadow:0 4px 20px rgba(124,58,237,.5);
    white-space:nowrap;
    transition:background .15s,opacity .2s,transform .1s;
}
.map-fab.hidden{opacity:0;pointer-events:none}
.map-fab:active{transform:translateX(-50%) scale(.96)}
.map-fab:hover{background:var(--cx-brand2)}
.map-fab-count{
    background:rgba(255,255,255,.25);border-radius:20px;
    padding:1px 8px;font-size:11px;
}

/* Overlay panneau mobile — doit être SOUS le panneau */
.map-panel-overlay{
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);
    z-index:395;
}
.map-panel-overlay.open{display:block}

/* Overlay "aucun GPS" */
.map-no-signal{position:absolute;top:14px;left:50%;transform:translateX(-50%);z-index:500;background:rgba(0,0,0,.75);color:#fff;padding:8px 18px;border-radius:20px;font-size:12.5px;font-weight:700;display:none;gap:7px;align-items:center;backdrop-filter:blur(6px);white-space:nowrap}
.map-no-signal.show{display:flex}

/* Compteur topbar */
.map-count{background:rgba(124,58,237,.2);border:1px solid rgba(124,58,237,.35);color:#c4b5fd;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap}

/* Leaflet popup dark */
.leaflet-popup-content-wrapper{background:var(--cx-surface);color:var(--cx-text);border:1px solid var(--cx-border);border-radius:var(--r-sm);box-shadow:0 8px 32px rgba(0,0,0,.6)}
.leaflet-popup-tip{background:var(--cx-surface)}
.leaflet-popup-content{margin:12px 14px;font-size:12.5px;line-height:1.6}
.lp-title{font-weight:800;font-size:13.5px;margin-bottom:4px}
.lp-row{display:flex;gap:6px;color:var(--cx-text2)}
.lp-row strong{color:var(--cx-text)}

/* MODE CLAIR */
body.cx-light{--cx-bg:#F5F7FA;--cx-surface:#fff;--cx-surface2:#eef1f7;--cx-border:rgba(0,0,0,.08);--cx-text:#111827;--cx-text2:#4b5563;--cx-muted:#9ca3af}
body.cx-light,html.cx-light,html.cx-light body{background:#F5F7FA!important}
body.cx-light .cx-topbar{background:#fff;border-bottom-color:rgba(0,0,0,.07);box-shadow:0 1px 4px rgba(0,0,0,.06)}
body.cx-light .cx-tb-btn{background:rgba(0,0,0,.05);border-color:rgba(0,0,0,.08);color:#374151}
body.cx-light .map-panel{background:#fff;border-right-color:rgba(0,0,0,.08)}
body.cx-light .map-panel-drag{background:rgba(0,0,0,.15)}
body.cx-light .mp-card:hover{background:rgba(0,0,0,.02)}
body.cx-light .mp-card.selected{background:rgba(124,58,237,.06)}
body.cx-light .leaflet-popup-content-wrapper{background:#fff;color:#111}
body.cx-light .leaflet-popup-tip{background:#fff}
body.cx-light .cx-panel-toggle{background:rgba(124,58,237,.08);border-color:rgba(124,58,237,.2);color:#7c3aed}

/* ═══════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════ */

/* ── Tablette large : 901px – 1280px ── */
@media(max-width:1280px) and (min-width:901px){
    .map-panel{width:260px}
    .cx-topbar-sub{display:none}
}

/* ── Tablette : ≤ 900px ── */
@media(max-width:900px){
    .cx-sidebar{transform:translateX(-100%)}
    .cx-sidebar.open{transform:translateX(0)}
    .cx-close-btn{display:flex}
    .cx-wrap{padding-left:0}
    .cx-hamburger{display:flex}
    .cx-topbar-sub{display:none}
    .cx-panel-toggle{display:flex}
    /* le panneau devient une drawer sur le côté */
    .map-panel{
        position:absolute;top:0;left:0;bottom:0;
        width:280px;z-index:400;   /* AU-DESSUS de l'overlay (395) */
        transform:translateX(-100%);
        box-shadow:8px 0 30px rgba(0,0,0,.4);
    }
    .map-panel.show{transform:translateX(0)}
    .map-panel-close{display:flex}
    .map-fab{display:none!important}
}

/* ── Mobile : ≤ 640px ── */
@media(max-width:640px){
    :root{--cx-topbar-h:52px}
    .cx-topbar{padding:0 10px;gap:8px}
    .cx-topbar-title{font-size:13px}
    .cx-pulse-txt{display:none}
    .cx-tb-btn{display:none}
    .cx-panel-toggle{display:none}
    /* panneau = bottom sheet */
    .map-panel{
        position:fixed;
        top:auto;left:0;right:0;bottom:0;
        width:100%;
        max-height:62vh;
        border-right:none;
        border-top:1px solid var(--cx-border);
        border-radius:18px 18px 0 0;
        box-shadow:0 -8px 40px rgba(0,0,0,.45);
        transform:translateY(100%);
        z-index:410;  /* AU-DESSUS de l'overlay (395) */
    }
    .map-panel.show{transform:translateY(0)}
    .map-panel-drag{display:flex}
    .map-fab{display:flex}
    .map-panel-close{display:flex;top:10px;right:10px}
}

/* ── Très petit mobile : ≤ 380px ── */
@media(max-width:380px){
    .map-count{display:none}
    .cx-topbar-title{font-size:12px}
}
</style>
@endpush

@section('content')

{{-- ══ SIDEBAR ══ --}}
<aside class="cx-sidebar" id="cxSidebar">
    <div class="cx-brand-hd">
        <div class="cx-brand-top">
            <div class="cx-logo"><div class="cx-logo-icon">🚀</div> ShipXpress</div>
            <button class="cx-close-btn" id="cxCloseBtn">✕</button>
        </div>
        <div class="cx-sys-badge"><span class="cx-sys-dot"></span> Système actif</div>
    </div>
    <nav class="cx-nav">
        <div class="cx-nav-sec">Principal</div>
        <a href="{{ route('company.dashboard') }}"        class="cx-nav-item"><span class="cx-nav-ico">⊞</span> Tableau de bord</a>
        <a href="{{ route('company.chat.inbox') }}"       class="cx-nav-item"><span class="cx-nav-ico">💬</span> Demandes (Chat)</a>
        <a href="{{ route('company.orders.index') }}"     class="cx-nav-item"><span class="cx-nav-ico">📦</span> Commandes</a>
        <a href="{{ route('company.drivers.index') }}"    class="cx-nav-item"><span class="cx-nav-ico">🚴</span> Chauffeurs</a>
        <a href="{{ route('company.livraisons.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🚚</span> Livraisons</a>
        <a href="{{ route('company.carte.index') }}"      class="cx-nav-item active"><span class="cx-nav-ico">🗺️</span> Carte en direct</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">🏪</span> Boutiques</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">👥</span> Clients</a>
        <div class="cx-nav-sec">Gestion</div>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">📍</span> Zone de livraison</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">💲</span> Tarification</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">🔔</span> Notifications</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">📊</span> Rapports</a>
        <div class="cx-nav-sec">Configuration</div>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">⚙️</span> Paramètres</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">👤</span> Utilisateurs</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">🔌</span> Intégrations</a>
    </nav>
    <div class="cx-user-foot">
        <div class="cx-user-row">
            <div class="cx-user-av">{{ $ini }}</div>
            <div style="flex:1;min-width:0">
                <div class="cx-user-name">{{ Str::limit($u->name ?? 'Admin', 16) }}</div>
                <div class="cx-user-role">{{ $company->name }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" class="cx-logout-btn" title="Déconnexion">⏻</button>
            </form>
        </div>
        <div class="cx-dark-row" id="cxDarkToggle">
            <span class="cx-dark-lbl" id="cxDarkLbl">Mode sombre</span>
            <div class="cx-toggle" id="cxDarkSwitch"></div>
        </div>
    </div>
</aside>

{{-- overlay sidebar --}}
<div class="cx-overlay" id="cxOverlay"></div>

{{-- overlay panneau livraisons (tablette/mobile) --}}
<div class="map-panel-overlay" id="panelOverlay"></div>

<div class="cx-wrap">
<main class="cx-main">

    {{-- Topbar --}}
    <div class="cx-topbar">
        <button class="cx-hamburger" id="cxHamburger">☰</button>
        <span class="cx-topbar-title">🗺️ Carte en direct</span>
        <span class="cx-topbar-sub">· {{ $company->name }}</span>
        <div class="cx-tb-right">
            <div class="cx-pulse">
                <span class="cx-pulse-dot"></span>
                <span class="cx-pulse-txt">Temps réel</span>
            </div>
            <span class="map-count" id="mapCount">0 en cours</span>
            <button class="cx-panel-toggle" id="panelToggleBtn" onclick="togglePanel()">
                🚴 <span id="panelToggleLbl">Chauffeurs</span>
            </button>
           
        </div>
    </div>

    <div class="map-body">

        {{-- Panneau liste --}}
        <div class="map-panel" id="mapPanel">
            {{-- Drag handle (mobile) --}}
            <div class="map-panel-drag" id="panelDrag"></div>
            {{-- Bouton fermer (tablette + mobile) --}}
            <button class="map-panel-close" onclick="togglePanel()" title="Fermer">✕</button>
            <div class="map-panel-hd">
                <div class="map-panel-title">🚴 Chauffeurs en mission</div>
                <div class="map-panel-sub" id="panelSub">Chargement…</div>
            </div>
            <div class="map-panel-list" id="mapPanelList">
                <div class="mp-empty">
                    <div class="mp-empty-ico">🗺️</div>
                    <div class="mp-empty-title">Chargement de la carte…</div>
                </div>
            </div>
        </div>

        {{-- Carte Leaflet --}}
        <div class="map-container">
            <div id="map"></div>

            {{-- FAB mobile --}}
            <button class="map-fab" id="mapFab" onclick="togglePanel()">
                🚴 <span id="fabLabel">Chauffeurs</span>
                <span class="map-fab-count" id="fabCount">0</span>
            </button>

            <div class="map-no-signal" id="mapNoSignal">
                📡 Aucun chauffeur avec GPS actif pour le moment
            </div>
        </div>
    </div>

</main>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ── Thème ── */
(function(){
    const body=document.body, sw=document.getElementById('cxDarkSwitch'),
          lbl=document.getElementById('cxDarkLbl'), row=document.getElementById('cxDarkToggle');
    const saved=localStorage.getItem('cx-theme')||'dark';
    function apply(t){
        if(t==='light'){body.classList.remove('cx-dark');body.classList.add('cx-light');sw?.classList.remove('on');if(lbl)lbl.textContent='Mode clair';}
        else{body.classList.remove('cx-light');body.classList.add('cx-dark');sw?.classList.add('on');if(lbl)lbl.textContent='Mode sombre';}
    }
    apply(saved); document.documentElement.classList.remove('cx-prelight');
    row?.addEventListener('click',()=>{const n=body.classList.contains('cx-light')?'dark':'light';apply(n);localStorage.setItem('cx-theme',n);});
})();

/* ── Sidebar mobile ── */
const ham=document.getElementById('cxHamburger'),sb=document.getElementById('cxSidebar'),
      ov=document.getElementById('cxOverlay'),cb=document.getElementById('cxCloseBtn');
function openSidebar(){
    sb?.classList.add('open'); ov?.classList.add('open');
    setTimeout(() => map.invalidateSize(), 280);
}
function closeSidebar(){
    sb?.classList.remove('open'); ov?.classList.remove('open');
    setTimeout(() => map.invalidateSize(), 280);
}
ham?.addEventListener('click', openSidebar);
ov?.addEventListener('click',  closeSidebar);
cb?.addEventListener('click',  closeSidebar);

/* ── Panneau livraisons (tablette + mobile) ── */
const panel       = document.getElementById('mapPanel');
const panelOv     = document.getElementById('panelOverlay');
const panelTogBtn = document.getElementById('panelToggleBtn');
const panelTogLbl = document.getElementById('panelToggleLbl');
let panelOpen = false;

function togglePanel() {
    panelOpen = !panelOpen;
    panel?.classList.toggle('show', panelOpen);
    panelOv?.classList.toggle('open', panelOpen);
    if (panelTogLbl) panelTogLbl.textContent = panelOpen ? 'Masquer' : 'Chauffeurs';
    // Cacher/afficher le FAB selon l'état du panneau (mobile)
    const fab = document.getElementById('mapFab');
    if (fab) fab.classList.toggle('hidden', panelOpen);
    // Leaflet recalcule après l'animation
    setTimeout(() => map.invalidateSize(), 320);
}
panelOv?.addEventListener('click', () => {
    panelOpen = true; togglePanel(); // force fermeture
});

/* ── Drag handle (bottom sheet swipe-down pour fermer) ── */
const drag = document.getElementById('panelDrag');
if (drag) {
    let startY = 0;
    drag.addEventListener('touchstart', e => { startY = e.touches[0].clientY; }, {passive:true});
    drag.addEventListener('touchend', e => {
        if (e.changedTouches[0].clientY - startY > 40) { panelOpen = true; togglePanel(); }
    }, {passive:true});
}

/* ── Couleurs par chauffeur ── */
const COLORS = ['#f59e0b','#3b82f6','#10b981','#ec4899','#8b5cf6','#06b6d4','#f97316','#84cc16','#ef4444','#a78bfa'];
function getColor(idx){ return COLORS[idx % COLORS.length]; }

/* ── Icône chauffeur SVG ── */
function driverIcon(color, isMoving) {
    const pulse = isMoving
        ? `<circle cx="20" cy="20" r="18" fill="${color}" opacity=".15"><animate attributeName="r" from="14" to="22" dur="1.5s" repeatCount="indefinite"/><animate attributeName="opacity" from=".3" to="0" dur="1.5s" repeatCount="indefinite"/></circle>`
        : '';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
        ${pulse}
        <circle cx="20" cy="20" r="13" fill="${color}" stroke="#fff" stroke-width="2.5"/>
        <text x="20" y="25" font-size="13" text-anchor="middle" fill="#fff" font-weight="900" font-family="Segoe UI,sans-serif">🚴</text>
    </svg>`;
    return L.divIcon({ html:svg, iconSize:[40,40], iconAnchor:[20,20], popupAnchor:[0,-20], className:'' });
}

/* ── Initialisation Leaflet ── */
const map = L.map('map', { center:[9.537,-13.677], zoom:13, zoomControl:true });

const tilesDark  = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',  {attribution:'© OpenStreetMap © CARTO',maxZoom:19});
const tilesLight = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',              {attribution:'© OpenStreetMap',         maxZoom:19});

function applyMapTheme() {
    const isLight = document.body.classList.contains('cx-light');
    if (isLight) { map.removeLayer(tilesDark);  tilesLight.addTo(map); }
    else          { map.removeLayer(tilesLight); tilesDark.addTo(map);  }
    // Forcer Leaflet à recalculer après le swap de tuiles
    setTimeout(() => map.invalidateSize(), 50);
    setTimeout(() => map.invalidateSize(), 400);
}
new MutationObserver(applyMapTheme).observe(document.body, {attributes:true, attributeFilter:['class']});
applyMapTheme();

// Repositionner le zoom control en bas à droite pour éviter la FAB
map.zoomControl.setPosition('bottomright');

/* ── State ── */
const markers  = {};
const traces   = {};
const polylines= {};
let   colorMap = {};
let   colorIdx = 0;
let   selectedId = null;

function getOrderColor(id) {
    if (colorMap[id] === undefined) { colorMap[id] = colorIdx++; }
    return getColor(colorMap[id]);
}

/* ── Rendu panneau ── */
function renderPanel(orders) {
    const list = document.getElementById('mapPanelList');
    const sub  = document.getElementById('panelSub');
    const cnt  = document.getElementById('mapCount');
    const fab  = document.getElementById('fabCount');
    const fabL = document.getElementById('fabLabel');
    const n = orders.length;
    if (cnt) cnt.textContent = n + ' en cours';
    if (fab) fab.textContent = n;
    if (fabL) fabL.textContent = n ? n + ' chauffeur' + (n>1?'s':'') : 'Aucun actif';

    if (!n) {
        list.innerHTML = `<div class="mp-empty"><div class="mp-empty-ico">🚚</div><div class="mp-empty-title">Aucune livraison en cours</div><div style="font-size:12px;color:var(--cx-muted);margin-top:4px">Les livraisons assignées apparaîtront ici dès qu'elles démarrent.</div></div>`;
        if (sub) sub.textContent = 'Aucune livraison active';
        return;
    }
    if (sub) sub.textContent = n + ' livraison' + (n>1?'s':'') + ' active' + (n>1?'s':'');

    list.innerHTML = orders.map(o => {
        const color   = getOrderColor(o.id);
        const isLiv   = o.status === 'en_livraison';
        const badge   = isLiv
            ? `<span class="mp-badge mp-badge-liv">En route</span>`
            : `<span class="mp-badge mp-badge-conf">Assignée</span>`;
        const hasGps  = o.lat && o.lng;
        const pingAge = hasGps ? Date.now()/1000 - new Date(o.ping).getTime()/1000 : null;
        const pingCls = pingAge !== null && pingAge < 30 ? 'mp-ping-ok' : 'mp-ping-stale';
        const gpsLine = hasGps
            ? `<div class="mp-ping"><span class="${pingCls}">📡 ${o.ping_ago}</span></div>`
            : `<div class="mp-no-gps">📡 GPS non encore reçu</div>`;
        const sel = selectedId === o.id ? ' selected' : '';
        return `<div class="mp-card${sel}" onclick="focusOrder(${o.id})" data-id="${o.id}">
            <div class="mp-card-top">
                <span class="mp-color" style="background:${color}"></span>
                <span class="mp-id">#${o.id}</span>
                <span class="mp-shop">${esc(o.shop)}</span>
                ${badge}
            </div>
            <div class="mp-driver">🚴 ${esc(o.driver)}${o.driver_phone ? ' · '+esc(o.driver_phone) : ''}</div>
            ${o.destination ? `<div class="mp-dest">📍 ${esc(o.destination)}</div>` : ''}
            ${gpsLine}
        </div>`;
    }).join('');
}

/* ── Mise à jour carte ── */
function updateMap(orders) {
    const activeIds = new Set(orders.map(o => o.id));
    Object.keys(markers).forEach(id => {
        if (!activeIds.has(parseInt(id))) {
            map.removeLayer(markers[id]); delete markers[id];
            if (polylines[id]) { map.removeLayer(polylines[id]); delete polylines[id]; }
            delete traces[id];
        }
    });

    let hasGps = false;
    const bounds = [];

    orders.forEach(o => {
        if (!o.lat || !o.lng) return;
        hasGps = true;
        const color    = getOrderColor(o.id);
        const isMoving = o.status === 'en_livraison';
        const pos      = [o.lat, o.lng];

        if (!traces[o.id]) traces[o.id] = [];
        const last = traces[o.id].at(-1);
        if (!last || last[0] !== pos[0] || last[1] !== pos[1]) traces[o.id].push(pos);

        if (traces[o.id].length > 1) {
            if (polylines[o.id]) { polylines[o.id].setLatLngs(traces[o.id]); }
            else { polylines[o.id] = L.polyline(traces[o.id], {color, weight:3, opacity:.75, dashArray:isMoving?null:'6,6'}).addTo(map); }
        }

        if (markers[o.id]) {
            markers[o.id].setLatLng(pos);
            markers[o.id].setIcon(driverIcon(color, isMoving));
        } else {
            markers[o.id] = L.marker(pos, {icon:driverIcon(color, isMoving)}).addTo(map).bindPopup(()=>buildPopup(o));
        }
        if (markers[o.id].isPopupOpen()) markers[o.id].setPopupContent(buildPopup(o));
        bounds.push(pos);
    });

    const noSig = document.getElementById('mapNoSignal');
    if (noSig) noSig.classList.toggle('show', !hasGps && orders.length > 0);

    if (bounds.length > 0 && selectedId === null) {
        bounds.length === 1
            ? map.setView(bounds[0], Math.max(map.getZoom(), 15))
            : map.fitBounds(bounds, {padding:[40,40], maxZoom:16});
    }
}

function buildPopup(o) {
    const color = getOrderColor(o.id);
    return `<div class="lp-title" style="color:${color}">🚴 ${esc(o.driver)}</div>
        <div class="lp-row"><span>Commande&nbsp;</span><strong>#${o.id} · ${esc(o.shop)}</strong></div>
        <div class="lp-row"><span>Client&nbsp;</span><strong>${esc(o.client)}</strong></div>
        ${o.destination ? `<div class="lp-row"><span>Destination&nbsp;</span><strong>${esc(o.destination)}</strong></div>` : ''}
        <div class="lp-row"><span>GPS&nbsp;</span><strong>${esc(o.ping_ago)}</strong></div>`;
}

/* ── Focus commande ── */
function focusOrder(id) {
    selectedId = (selectedId === id) ? null : id;
    document.querySelectorAll('.mp-card').forEach(c => c.classList.toggle('selected', parseInt(c.dataset.id) === selectedId));
    if (selectedId && markers[selectedId]) {
        map.setView(markers[selectedId].getLatLng(), 16, {animate:true});
        markers[selectedId].openPopup();
        // Sur mobile : ferme le panneau pour voir la carte
        if (window.innerWidth <= 640 && panelOpen) { panelOpen = true; togglePanel(); }
    }
}

/* ── Polling ── */
async function poll() {
    try {
        const res = await fetch('{{ route('company.carte.data') }}', {credentials:'same-origin', headers:{'Accept':'application/json'}});
        if (!res.ok) return;
        const d = await res.json();
        if (!d.ok) return;
        renderPanel(d.orders);
        updateMap(d.orders);
    } catch(e) { /* silencieux */ }
}

function esc(s){ return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

poll();
setInterval(poll, 4000);

// Recalcul taille carte si fenêtre redimensionnée
window.addEventListener('resize', () => map.invalidateSize());
</script>
@endpush
