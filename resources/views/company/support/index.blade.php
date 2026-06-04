@extends('layouts.app')
@section('title', 'Support · ' . $company->name)
@php
    $bodyClass = 'cx-dashboard';
    $u   = auth()->user();
    $ini = strtoupper(substr($u->name ?? 'A', 0, 1)) . strtoupper(substr(explode(' ', $u->name ?? 'A ')[1] ?? 'X', 0, 1));
@endphp

@push('styles')
<script>(function(){ if(localStorage.getItem('cx-theme')==='light') document.documentElement.classList.add('cx-prelight'); })();</script>
<style>html.cx-prelight body{background:#F5F7FA!important}</style>
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --cx-bg:#0b0d22; --cx-surface:#0d1226; --cx-surface2:#111930;
    --cx-border:rgba(255,255,255,.07); --cx-brand:#7c3aed; --cx-brand2:#6d28d9;
    --cx-text:#e2e8f0; --cx-text2:#94a3b8; --cx-muted:#475569;
    --cx-green:#10b981; --cx-amber:#f59e0b; --cx-red:#ef4444; --cx-blue:#3b82f6;
    --cx-sb-w:220px; --r:16px; --r-sm:10px; --r-xs:7px;
}
html,body{margin:0;font-family:'Segoe UI',system-ui,sans-serif;background:var(--cx-bg)!important;color:var(--cx-text);-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
body.cx-dashboard>nav,body.cx-dashboard>header,body.cx-dashboard .navbar,
body.cx-dashboard>.topbar-global,body.cx-dashboard .app-footer,
body.cx-dashboard .app-flash{display:none!important}
body.cx-dashboard>main.app-main{padding:0!important;margin:0!important;max-width:100%!important;width:100%!important}

/* ── LAYOUT ── */
.cx-wrap{display:flex;position:fixed;inset:0;padding-left:var(--cx-sb-w)}

/* ── SIDEBAR ── */
.cx-sidebar{position:fixed;top:0;left:0;bottom:0;width:var(--cx-sb-w);background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);display:flex;flex-direction:column;border-right:1px solid rgba(99,102,241,.15);box-shadow:6px 0 30px rgba(0,0,0,.35);z-index:1200;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.3) transparent;transition:transform .25s cubic-bezier(.23,1,.32,1)}
.cx-sidebar::-webkit-scrollbar{width:3px}
.cx-sidebar::-webkit-scrollbar-thumb{background:rgba(124,58,237,.3);border-radius:3px}
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
.cx-nav-sec{font-size:10px;font-weight:800;letter-spacing:1.6px;color:rgba(255,255,255,.58);padding:14px 10px 5px;text-transform:uppercase}
.cx-nav-item{display:flex;align-items:center;gap:10px;padding:8px 11px;border-radius:var(--r-xs);color:rgba(255,255,255,.85);font-size:13.5px;font-weight:600;transition:all .22s;position:relative;cursor:pointer;margin-bottom:2px;border:1px solid transparent}
.cx-nav-item:hover{background:rgba(124,58,237,.18);color:#fff;border-color:rgba(124,58,237,.25)}
.cx-nav-item.active{background:linear-gradient(90deg,rgba(124,58,237,.35),rgba(99,102,241,.2));color:#fff;font-weight:700;border-color:rgba(139,92,246,.3)}
.cx-nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:22px;background:linear-gradient(180deg,#a78bfa,#7c3aed);border-radius:0 3px 3px 0}
.cx-nav-ico{width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0}
.cx-nav-item.active .cx-nav-ico{background:rgba(139,92,246,.3);border-color:rgba(139,92,246,.4)}
.cx-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1100}
.cx-overlay.open{display:block}
.cx-user-foot{padding:10px 10px 12px;border-top:1px solid rgba(255,255,255,.07);flex-shrink:0}
.cx-user-row{display:flex;align-items:center;gap:9px;padding:7px 8px;border-radius:var(--r-xs);background:rgba(255,255,255,.04);cursor:pointer;margin-bottom:6px}
.cx-user-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4338ca);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0}
.cx-user-name{font-size:12px;font-weight:700;color:#fff;line-height:1.2}
.cx-user-role{font-size:10px;color:var(--cx-text2)}
.cx-logout-btn{width:30px;height:30px;border-radius:8px;flex-shrink:0;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);display:flex;align-items:center;justify-content:center;color:#f87171;cursor:pointer;padding:0;transition:background .15s}
.cx-logout-btn:hover{background:rgba(239,68,68,.22);color:#fff}
.cx-dark-row{display:flex;align-items:center;justify-content:space-between;padding:4px 8px;cursor:pointer}
.cx-dark-lbl{font-size:11.5px;color:var(--cx-text2)}
.cx-toggle{width:34px;height:18px;background:#475569;border-radius:9px;position:relative;transition:background .25s;flex-shrink:0}
.cx-toggle::after{content:'';position:absolute;top:3px;left:3px;width:12px;height:12px;background:#fff;border-radius:50%;transition:left .25s}
.cx-toggle.on{background:var(--cx-brand)}
.cx-toggle.on::after{left:19px}

/* ── MAIN ── */
.cx-main{flex:1;min-width:0;min-height:0;display:flex;flex-direction:column;background:var(--cx-bg)}
.cx-topbar{height:60px;background:var(--cx-surface);border-bottom:1px solid var(--cx-border);display:flex;align-items:center;gap:12px;padding:0 20px;flex-shrink:0;position:sticky;top:0;z-index:100}
.cx-hamburger{display:none;background:none;border:none;color:var(--cx-text2);font-size:18px;cursor:pointer;padding:4px 8px;border-radius:6px}
.cx-topbar-title{font-size:15px;font-weight:800;color:var(--cx-text);display:flex;align-items:center;gap:8px}
.cx-tb-right{margin-left:auto;display:flex;align-items:center;gap:8px}

/* ── STATUS BADGE ── */
.sp-badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px}
.sp-badge.open  {background:rgba(16,185,129,.12);color:#34d399;border:1px solid rgba(16,185,129,.2)}
.sp-badge.closed{background:rgba(100,116,139,.15);color:#94a3b8;border:1px solid rgba(100,116,139,.2)}
.sp-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0}
.sp-badge.open  .sp-dot{background:#10b981;animation:blink 2s ease-in-out infinite}
.sp-badge.closed .sp-dot{background:#64748b}

/* ── CHAT CONTAINER ── */
.sp-chat{flex:1;display:flex;flex-direction:column;min-height:0;overflow:hidden}
.sp-messages{flex:1;min-height:0;overflow-y:auto;padding:24px 20px;display:flex;flex-direction:column;gap:14px;scroll-behavior:smooth}
.sp-messages::before{content:'';flex:1;min-height:0}
.sp-messages::-webkit-scrollbar{width:5px}
.sp-messages::-webkit-scrollbar-thumb{background:rgba(124,58,237,.3);border-radius:4px}

/* ── BULLES ── */
.sp-row{display:flex;gap:10px;max-width:78%;align-items:flex-end}
.sp-row.me{margin-left:auto;flex-direction:row-reverse}
.sp-av{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0}
.sp-row.me .sp-av{background:linear-gradient(135deg,#7c3aed,#4338ca)}
.sp-row.them .sp-av{background:linear-gradient(135deg,#0ea5e9,#0369a1)}
.sp-bubble-wrap{display:flex;flex-direction:column;gap:3px}
.sp-row.me .sp-bubble-wrap{align-items:flex-end}
.sp-bubble{padding:10px 14px;border-radius:18px;font-size:13.5px;line-height:1.55;word-break:break-word;max-width:100%}
.sp-row.me   .sp-bubble{background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;border-bottom-right-radius:4px}
.sp-row.them .sp-bubble{background:var(--cx-surface2);color:var(--cx-text);border:1px solid var(--cx-border);border-bottom-left-radius:4px}
.sp-time{font-size:10px;color:var(--cx-muted);padding:0 4px}
.sp-author{font-size:10.5px;font-weight:600;color:var(--cx-text2);padding:0 4px;margin-bottom:1px}

/* ── ZONE VIDE ── */
.sp-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;color:var(--cx-muted);text-align:center;padding:40px 20px}
.sp-empty-ico{font-size:52px;opacity:.25}
.sp-empty-title{font-size:16px;font-weight:700;color:var(--cx-text2)}
.sp-empty-sub{font-size:13px;color:var(--cx-muted);max-width:300px;line-height:1.55}

/* ── INPUT ── */
.sp-input-area{padding:16px 20px;border-top:1px solid var(--cx-border);background:var(--cx-surface);flex-shrink:0}
.sp-form{display:flex;gap:10px;align-items:flex-end}
.sp-closed-msg{text-align:center;font-size:13px;color:var(--cx-muted);padding:6px;font-style:italic}
.sp-textarea{flex:1;padding:11px 16px;border-radius:var(--r-sm);background:var(--cx-surface2);border:1.5px solid var(--cx-border);color:var(--cx-text);font-size:13.5px;font-family:inherit;resize:none;outline:none;min-height:44px;max-height:140px;line-height:1.5;transition:border-color .15s}
.sp-textarea:focus{border-color:var(--cx-brand);box-shadow:0 0 0 3px rgba(124,58,237,.12)}
.sp-textarea::placeholder{color:var(--cx-muted)}
.sp-send{width:44px;height:44px;border-radius:var(--r-xs);background:var(--cx-brand);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s,transform .1s}
.sp-send:hover{background:var(--cx-brand2);transform:scale(1.06)}
.sp-send:disabled{opacity:.5;cursor:not-allowed;transform:none}

/* ── INFO BARRE ── */
.sp-info-bar{display:flex;align-items:center;gap:10px;padding:10px 20px;background:var(--cx-surface2);border-bottom:1px solid var(--cx-border);font-size:12px;color:var(--cx-text2);flex-wrap:wrap;gap:8px}
.sp-info-bar strong{color:var(--cx-text);font-weight:700}

/* ── MODE CLAIR ── */
body.cx-light{--cx-bg:#F5F7FA;--cx-surface:#fff;--cx-surface2:#eef1f7;--cx-border:rgba(0,0,0,.08);--cx-text:#111827;--cx-text2:#4b5563;--cx-muted:#9ca3af}
body.cx-light,html.cx-light,html.cx-light body{background:#F5F7FA!important}
body.cx-light .cx-topbar,body.cx-light .sp-input-area{background:#fff;border-color:rgba(0,0,0,.07)}
body.cx-light .sp-row.them .sp-bubble{background:#f3f4f6;border-color:rgba(0,0,0,.08)}
body.cx-light .sp-textarea{background:#f3f4f6;border-color:rgba(0,0,0,.1);color:#111827}
body.cx-light .sp-info-bar{background:#f8fafc;border-color:rgba(0,0,0,.07)}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
    .cx-sidebar{transform:translateX(-100%)}
    .cx-sidebar.open{transform:translateX(0)}
    .cx-wrap{padding-left:0;position:fixed;inset:0}
    .cx-hamburger{display:block}
    .cx-close-btn{display:flex}
}
@media(max-width:640px){
    .cx-topbar{padding:0 12px;gap:8px}
    .sp-messages{padding:16px 12px}
    .sp-input-area{padding:12px;position:sticky;bottom:0;z-index:10}
    .sp-info-bar{padding:8px 12px}
    .sp-row{max-width:92%}
    .sp-bubble{font-size:13px;padding:9px 12px}
    .cx-topbar-title{font-size:13px}
}
@media(max-width:400px){
    .sp-row{max-width:96%}
}
</style>
@endpush

@section('content')

{{-- ══ SIDEBAR ══ --}}
<aside class="cx-sidebar" id="cxSidebar">
    <div class="cx-brand-hd">
        <div class="cx-brand-top">
            <div class="sb-logo-icon"><img src="/images/shopio3.jpeg" alt="Shopio" style="width:40px;height:40px;object-fit:cover;border-radius:9px"></div>
            <button class="cx-close-btn" id="cxCloseBtn"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="cx-sys-badge"><span class="cx-sys-dot"></span> Système actif</div>
    </div>
    <nav class="cx-nav">
        <div class="cx-nav-sec">Principal</div>
        <a href="{{ route('company.dashboard') }}" class="cx-nav-item">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span> Tableau de bord
        </a>
        <a href="{{ route('company.chat.inbox') }}" class="cx-nav-item">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span> Demandes (Chat)
        </a>

        {{-- Commandes : badge usage --}}
        <a href="{{ route('company.orders.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span>
            Commandes
            @if(!$isBusiness)
                @php
                    $pctO = $maxOrders > 0 ? $usedOrders / $maxOrders : 0;
                    $colO = $pctO >= 1 ? '#ef4444' : ($pctO >= 0.7 ? '#f59e0b' : '#7c3aed');
                @endphp
                <span style="margin-left:auto;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;background:{{ $colO }};color:#fff;">
                    {{ $usedOrders }}/{{ $maxOrders }}
                </span>
            @endif
        </a>

        {{-- Chauffeurs : badge usage --}}
        <a href="{{ route('company.drivers.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg></span>
            Chauffeurs
            @if(!$isBusiness)
                @php
                    $pctD = $maxDrivers > 0 ? $totalDrivers / $maxDrivers : 0;
                    $colD = $pctD >= 1 ? '#ef4444' : ($pctD >= 0.7 ? '#f59e0b' : '#7c3aed');
                @endphp
                <span style="margin-left:auto;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;background:{{ $colD }};color:#fff;">
                    {{ $totalDrivers }}/{{ $maxDrivers }}
                </span>
            @endif
        </a>

        <a href="{{ route('company.livraisons.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></span> Livraisons
        </a>

        {{-- Carte : verrouillée en plan gratuit --}}
        <a href="{{ $isBusiness ? route('company.carte.index') : route('company.subscription.upgrade') }}"
           class="cx-nav-item"
           @if(!$isBusiness) style="opacity:.6" @endif>
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg></span>
            Carte en direct
            @if(!$isBusiness)
                <span style="margin-left:auto;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;background:#f59e0b;color:#1c1917;">🔒</span>
            @endif
        </a>

        <a href="{{ route('company.boutiques.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 9l1-5h16l1 5"/><path d="M3 9a2 2 0 0 0 4 0m4 0a2 2 0 0 0 4 0m4 0a2 2 0 0 0-4 0m-8 0a2 2 0 0 0-4 0"/><path d="M5 9v12h14V9"/><path d="M10 14h4v6h-4z"/></svg></span> Boutiques
        </a>
        <a href="{{ route('company.clients.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span> Clients
        </a>

        <div class="cx-nav-sec">Gestion</div>

        {{-- Zones : badge usage --}}
        <a href="{{ route('company.zones.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
            Zone de livraison
            @if(!$isBusiness)
                @php
                    $pctZ = $maxZones > 0 ? $totalZones / $maxZones : 0;
                    $colZ = $pctZ >= 1 ? '#ef4444' : ($pctZ >= 0.7 ? '#f59e0b' : '#7c3aed');
                @endphp
                <span style="margin-left:auto;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;background:{{ $colZ }};color:#fff;">
                    {{ $totalZones }}/{{ $maxZones }}
                </span>
            @endif
        </a>

        <a href="{{ route('company.historique.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg></span> Historique
        </a>

        {{-- Rapport : verrouillé en plan gratuit --}}
        <a href="{{ $isBusiness ? route('company.rapport.index') : route('company.subscription.upgrade') }}"
           class="cx-nav-item"
           @if(!$isBusiness) style="opacity:.6" @endif>
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg></span>
            Rapport
            @if(!$isBusiness)
                <span style="margin-left:auto;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;background:#f59e0b;color:#1c1917;">🔒</span>
            @endif
        </a>

        <div class="cx-nav-sec">Configuration</div>
        <a href="{{ route('company.parametre.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span> Paramètres
        </a>

        {{-- Utilisateurs : verrouillé en plan gratuit --}}
        <a href="{{ $isBusiness ? route('company.users.index') : route('company.subscription.upgrade') }}"
           class="cx-nav-item"
           @if(!$isBusiness) style="opacity:.6" @endif>
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            Utilisateurs
            @if(!$isBusiness)
                <span style="margin-left:auto;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;background:#f59e0b;color:#1c1917;">🔒</span>
            @endif
        </a>

        {{-- Support (active) --}}
        <a href="{{ route('company.support.index') }}" class="cx-nav-item active">
            <span class="cx-nav-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg></span> Support
        </a>
    </nav>
    <div class="cx-user-foot">
        <div class="cx-user-row">
            <div class="cx-user-av">{{ $ini }}</div>
            <div style="flex:1;min-width:0">
                <div class="cx-user-name">{{ Str::limit($u->name ?? 'Admin', 16) }}</div>
                <div class="cx-user-role">{{ $company->name }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" class="cx-logout-btn" title="Déconnexion"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></button>
            </form>
        </div>
        <div class="cx-dark-row" id="cxDarkToggle">
            <span class="cx-dark-lbl" id="cxDarkLbl">Mode sombre</span>
            <div class="cx-toggle" id="cxDarkSwitch"></div>
        </div>
    </div>
</aside>

<div class="cx-overlay" id="cxOverlay"></div>

<div class="cx-wrap">
<main class="cx-main">

    {{-- Topbar --}}
    <div class="cx-topbar">
        <button class="cx-hamburger" id="cxHamburger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span class="cx-topbar-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
            Support
        </span>
        <div class="cx-tb-right">
            <span class="sp-badge {{ $ticket->status }}">
                <span class="sp-dot"></span>
                {{ $ticket->status === 'open' ? 'Ouvert' : 'Fermé' }}
            </span>
        </div>
    </div>

    {{-- Info bar --}}
    <div class="sp-info-bar">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Conversation directe avec le <strong>SuperAdmin</strong> · Ticket #{{ $ticket->id }}
        <span style="margin-left:auto;font-size:11px;color:var(--cx-muted)">Les messages sont traités dans les meilleurs délais</span>
    </div>

    {{-- Chat --}}
    <div class="sp-chat" id="spChat">
        <div class="sp-messages" id="spMessages">
            @if($messages->isEmpty())
            <div class="sp-empty" id="spEmpty">
                <div class="sp-empty-ico">🎧</div>
                <div class="sp-empty-title">Aucun message pour le moment</div>
                <div class="sp-empty-sub">Décrivez votre problème ci-dessous. Le SuperAdmin vous répondra dès que possible.</div>
            </div>
            @else
            @foreach($messages as $msg)
            @php $isMe = $msg->user_id === auth()->id(); @endphp
            <div class="sp-row {{ $isMe ? 'me' : 'them' }}" id="msg-{{ $msg->id }}">
                <div class="sp-av">{{ strtoupper(substr($msg->author?->name ?? 'S', 0, 2)) }}</div>
                <div class="sp-bubble-wrap">
                    @if(!$isMe)<div class="sp-author">{{ $msg->author?->name ?? 'Support' }}</div>@endif
                    <div class="sp-bubble">{{ $msg->body }}</div>
                    <div class="sp-time">{{ $msg->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Zone saisie --}}
        <div class="sp-input-area">
            @if($ticket->status === 'open')
            <form class="sp-form" id="spForm">
                @csrf
                <textarea
                    class="sp-textarea"
                    id="spInput"
                    placeholder="Décrivez votre problème…"
                    rows="1"
                    maxlength="10000"
                ></textarea>
                <button type="submit" class="sp-send" id="spSend" title="Envoyer">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>
            @else
            <div class="sp-closed-msg">Ce ticket est fermé. Contactez le support pour en ouvrir un nouveau.</div>
            @endif
        </div>
    </div>

</main>
</div>

@endsection

@push('scripts')
<script>
const CSRF        = document.querySelector('meta[name=csrf-token]')?.content ?? '';
const SEND_URL    = '{{ route('company.support.send', $ticket) }}';
const POLL_URL    = '{{ route('company.support.poll', $ticket) }}';
const MY_ID       = {{ auth()->id() }};

/* ── Thème ── */
(function(){
    const body = document.body;
    const sw   = document.getElementById('cxDarkSwitch');
    const lbl  = document.getElementById('cxDarkLbl');
    const row  = document.getElementById('cxDarkToggle');
    const saved = localStorage.getItem('cx-theme') || 'dark';
    function apply(t) {
        if (t === 'light') { body.classList.remove('cx-dark'); body.classList.add('cx-light'); sw?.classList.remove('on'); if(lbl) lbl.textContent='Mode clair'; }
        else               { body.classList.remove('cx-light'); body.classList.add('cx-dark'); sw?.classList.add('on');    if(lbl) lbl.textContent='Mode sombre'; }
    }
    apply(saved);
    document.documentElement.classList.remove('cx-prelight');
    row?.addEventListener('click', () => {
        const next = body.classList.contains('cx-light') ? 'dark' : 'light';
        apply(next); localStorage.setItem('cx-theme', next);
    });
})();

/* ── Sidebar mobile ── */
const ham = document.getElementById('cxHamburger');
const sb  = document.getElementById('cxSidebar');
const ov  = document.getElementById('cxOverlay');
const cb  = document.getElementById('cxCloseBtn');
ham?.addEventListener('click', () => { sb?.classList.add('open'); ov?.classList.add('open'); if(cb) cb.style.display='flex'; });
ov?.addEventListener('click',  () => { sb?.classList.remove('open'); ov?.classList.remove('open'); });
cb?.addEventListener('click',  () => { sb?.classList.remove('open'); ov?.classList.remove('open'); });

/* ── Auto-resize textarea ── */
const inp = document.getElementById('spInput');
inp?.addEventListener('input', () => {
    inp.style.height = 'auto';
    inp.style.height = Math.min(inp.scrollHeight, 140) + 'px';
});

/* ── Scroll bas ── */
function scrollBottom() {
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            const el = document.getElementById('spMessages');
            if (el) el.scrollTop = el.scrollHeight;
        });
    });
}
scrollBottom();

// Quand le clavier s'ouvre sur mobile, rescroller vers le bas
const _inp = document.getElementById('spInput');
if (_inp) {
    _inp.addEventListener('focus', () => setTimeout(scrollBottom, 320));
}
if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', () => setTimeout(scrollBottom, 100));
}

/* ── Rendu message ── */
function formatTime(iso) {
    const d = new Date(iso);
    const now = new Date();
    const diff = (now - d) / 1000;
    if (diff < 60)  return 'À l\'instant';
    if (diff < 3600) return Math.floor(diff/60) + ' min';
    if (diff < 86400) return Math.floor(diff/3600) + 'h';
    return d.toLocaleDateString('fr-FR', { day:'numeric', month:'short' });
}

function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, c =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])
    );
}

function renderMsg(m) {
    const isMe    = m.user_id === MY_ID;
    const side    = isMe ? 'me' : 'them';
    const name    = m.author?.name ?? 'Support';
    const ini     = name.toUpperCase().slice(0,2);
    const author  = !isMe ? `<div class="sp-author">${esc(name)}</div>` : '';
    const time    = formatTime(m.created_at);
    return `<div class="sp-row ${side}" id="msg-${m.id}">
        <div class="sp-av">${esc(ini)}</div>
        <div class="sp-bubble-wrap">
            ${author}
            <div class="sp-bubble">${esc(m.body)}</div>
            <div class="sp-time">${time}</div>
        </div>
    </div>`;
}

/* ── Suivi des IDs rendus ── */
let knownIds = new Set(
    [...document.querySelectorAll('[id^="msg-"]')].map(el => parseInt(el.id.split('-')[1]))
);

/* ── Polling ── */
async function poll() {
    try {
        const r = await fetch(POLL_URL, { headers: { 'Accept': 'application/json' } });
        if (!r.ok) return;
        const msgs = await r.json();
        const container = document.getElementById('spMessages');
        const empty     = document.getElementById('spEmpty');
        let added = false;
        msgs.forEach(m => {
            if (!knownIds.has(m.id)) {
                knownIds.add(m.id);
                if (empty) { empty.remove(); }
                container.insertAdjacentHTML('beforeend', renderMsg(m));
                added = true;
            }
        });
        if (added) scrollBottom();
    } catch (_) {}
}

setInterval(poll, 5000);

/* ── Envoi message ── */
const form = document.getElementById('spForm');
const send = document.getElementById('spSend');

form?.addEventListener('submit', async e => {
    e.preventDefault();
    const body = inp?.value.trim();
    if (!body) return;
    send.disabled = true;

    try {
        const r = await fetch(SEND_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ body })
        });
        const d = await r.json();
        if (d.ok && d.msg) {
            const container = document.getElementById('spMessages');
            const empty     = document.getElementById('spEmpty');
            if (empty) empty.remove();
            knownIds.add(d.msg.id);
            container.insertAdjacentHTML('beforeend', renderMsg(d.msg));
            scrollBottom();
            if (inp) { inp.value = ''; inp.style.height = 'auto'; }
        }
    } catch (_) {}

    send.disabled = false;
    inp?.focus();
});

/* ── Entrée = envoyer, Shift+Entrée = retour ligne ── */
inp?.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        form?.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    }
});
</script>
@endpush
