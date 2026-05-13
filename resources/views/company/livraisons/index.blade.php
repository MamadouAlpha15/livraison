@extends('layouts.app')
@section('title', 'Livraisons en cours · ' . $company->name)
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
.cx-wrap{display:flex;min-height:100vh;padding-left:var(--cx-sb-w)}

/* ── SIDEBAR ── */
.cx-sidebar{
    position:fixed;top:0;left:0;bottom:0;width:var(--cx-sb-w);
    background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    display:flex;flex-direction:column;
    border-right:1px solid rgba(99,102,241,.15);
    box-shadow:6px 0 30px rgba(0,0,0,.35);
    z-index:1200;overflow-y:auto;
    scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.3) transparent;
    transition:transform .25s cubic-bezier(.23,1,.32,1);
}
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
.cx-nav-item{display:flex;align-items:center;gap:10px;padding:8px 11px;border-radius:var(--r-xs);color:rgba(255,255,255,.85);font-size:13.5px;font-weight:600;transition:all .22s cubic-bezier(.23,1,.32,1);position:relative;cursor:pointer;margin-bottom:2px;border:1px solid transparent}
.cx-nav-item:hover{background:rgba(124,58,237,.18);color:#fff;border-color:rgba(124,58,237,.25);box-shadow:0 2px 12px rgba(124,58,237,.2),inset 0 1px 0 rgba(255,255,255,.06)}
.cx-nav-item:hover .cx-nav-ico{background:rgba(139,92,246,.25);box-shadow:0 0 8px rgba(139,92,246,.3)}
.cx-nav-item.active{background:linear-gradient(90deg,rgba(124,58,237,.35) 0%,rgba(99,102,241,.2) 100%);color:#fff;font-weight:700;border-color:rgba(139,92,246,.3);box-shadow:0 4px 16px rgba(124,58,237,.25),inset 0 1px 0 rgba(255,255,255,.08)}
.cx-nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:22px;background:linear-gradient(180deg,#a78bfa,#7c3aed);border-radius:0 3px 3px 0;box-shadow:2px 0 12px rgba(167,139,250,.7)}
.cx-nav-ico{width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;transition:all .22s cubic-bezier(.23,1,.32,1)}
.cx-nav-item.active .cx-nav-ico{background:rgba(139,92,246,.3);border-color:rgba(139,92,246,.4);box-shadow:0 0 10px rgba(139,92,246,.4)}
.cx-nav-item.active:hover{background:linear-gradient(90deg,rgba(124,58,237,.45) 0%,rgba(99,102,241,.3) 100%);box-shadow:0 4px 20px rgba(124,58,237,.35),inset 0 1px 0 rgba(255,255,255,.1)}
.cx-nav-badge{margin-left:auto;background:var(--cx-brand);color:#fff;font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:20px;min-width:18px;text-align:center}
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
.cx-main{flex:1;min-width:0;display:flex;flex-direction:column;min-height:100vh;background:var(--cx-bg)}
.cx-topbar{height:60px;background:var(--cx-surface);border-bottom:1px solid var(--cx-border);display:flex;align-items:center;gap:12px;padding:0 20px;position:sticky;top:0;z-index:100}
.cx-hamburger{display:none;background:none;border:none;color:var(--cx-text2);font-size:18px;cursor:pointer;padding:4px 8px;border-radius:6px}
.cx-topbar-title{font-size:15px;font-weight:800;color:var(--cx-text)}
.cx-topbar-sub{font-size:12px;color:var(--cx-muted);margin-left:2px}
.cx-tb-right{margin-left:auto;display:flex;align-items:center;gap:8px}
.cx-tb-btn{height:34px;padding:0 12px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid var(--cx-border);color:var(--cx-text2);font-size:13px;font-weight:600;cursor:pointer;transition:background .14s,color .14s;display:flex;align-items:center;gap:6px;position:relative}
.cx-tb-btn:hover{background:rgba(255,255,255,.1);color:#fff}

/* ── STATS ── */
.lv-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;padding:24px 28px 0}
.lv-stat{background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r);padding:18px 20px;display:flex;align-items:center;gap:14px;position:relative;overflow:hidden}
.lv-stat::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--s-color)}
.lv-stat-ico{width:44px;height:44px;border-radius:12px;background:var(--s-bg);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.lv-stat-val{font-size:28px;font-weight:900;color:var(--cx-text);letter-spacing:-.8px;line-height:1}
.lv-stat-lbl{font-size:11px;font-weight:600;color:var(--cx-muted);text-transform:uppercase;letter-spacing:.5px;margin-top:3px}

/* ── TOOLBAR ── */
.lv-toolbar{display:flex;align-items:center;gap:10px;padding:20px 28px 0;flex-wrap:wrap}
.lv-search-wrap{position:relative;flex:1;min-width:200px;max-width:340px}
.lv-search-ico{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--cx-muted);pointer-events:none}
.lv-search{width:100%;padding:9px 12px 9px 34px;background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r-xs);color:var(--cx-text);font-size:13px;font-family:inherit;outline:none;transition:border-color .15s}
.lv-search:focus{border-color:var(--cx-brand);box-shadow:0 0 0 3px rgba(124,58,237,.12)}
.lv-search::placeholder{color:var(--cx-muted)}
.lv-filter-btn{padding:8px 16px;border-radius:30px;font-size:12.5px;font-weight:700;border:1px solid transparent;cursor:pointer;transition:all .15s;background:transparent;color:var(--cx-text2);font-family:inherit}
.lv-filter-btn:hover{background:var(--cx-surface2);color:var(--cx-text)}
.lv-filter-btn.active{background:var(--cx-brand);color:#fff;border-color:var(--cx-brand)}
.lv-filter-btn.f-confirm{background:rgba(59,130,246,.12);color:#60a5fa;border-color:rgba(59,130,246,.25)}
.lv-filter-btn.f-livraison{background:rgba(245,158,11,.12);color:#fbbf24;border-color:rgba(245,158,11,.25)}
.lv-pulse{display:flex;align-items:center;gap:6px;margin-left:auto;font-size:11.5px;color:var(--cx-muted)}
.lv-pulse-dot{width:7px;height:7px;border-radius:50%;background:var(--cx-green);animation:blink 2s ease-in-out infinite;flex-shrink:0}

/* ── TABLE ── */
.lv-table-wrap{padding:16px 28px 48px;overflow-x:auto}
.lv-table{width:100%;border-collapse:collapse;min-width:760px}
.lv-table th{padding:10px 14px;text-align:left;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--cx-muted);border-bottom:1px solid var(--cx-border);white-space:nowrap}
.lv-table td{padding:13px 14px;border-bottom:1px solid var(--cx-border);vertical-align:middle;font-size:13px;color:var(--cx-text)}
.lv-table tr:last-child td{border-bottom:none}
.lv-table tbody tr{transition:background .12s}
.lv-table tbody tr:hover{background:rgba(255,255,255,.03)}

/* Badges statut */
.lv-badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;white-space:nowrap}
.lv-badge-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.b-confirm{background:rgba(59,130,246,.12);color:#60a5fa;border:1px solid rgba(59,130,246,.2)}
.b-confirm .lv-badge-dot{background:#3b82f6}
.b-livraison{background:rgba(245,158,11,.12);color:#fbbf24;border:1px solid rgba(245,158,11,.2)}
.b-livraison .lv-badge-dot{background:#f59e0b;box-shadow:0 0 5px rgba(245,158,11,.5)}

/* Chauffeur */
.lv-driver{display:flex;align-items:center;gap:8px}
.lv-driver-av{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff;flex-shrink:0}
.lv-driver-name{font-size:12.5px;font-weight:700;color:var(--cx-text)}
.lv-driver-phone{font-size:11px;color:var(--cx-muted)}

/* Destination */
.lv-dest{max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12.5px;color:var(--cx-text2)}

/* Actions */
.lv-act-btn{width:30px;height:30px;border-radius:var(--r-xs);border:1px solid var(--cx-border);background:var(--cx-surface2);color:var(--cx-text2);cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:13px;transition:all .14s}
.lv-act-btn:hover{background:rgba(124,58,237,.15);border-color:rgba(124,58,237,.3);color:#c4b5fd}
.lv-act-btn.done:hover{background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3);color:#34d399}
.lv-act-btn.cancel:hover{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#f87171}

/* Temps écoulé */
.lv-time{font-size:11.5px;color:var(--cx-muted);white-space:nowrap}
.lv-time.urgent{color:var(--cx-amber);font-weight:700}

/* Empty */
.lv-empty{text-align:center;padding:72px 24px;color:var(--cx-muted)}
.lv-empty-ico{font-size:48px;margin-bottom:14px}
.lv-empty-title{font-size:17px;font-weight:800;color:var(--cx-text);margin-bottom:6px}
.lv-empty-sub{font-size:13px;color:var(--cx-muted)}

/* Toast */
.cx-toast-wrap{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none}
.cx-toast{background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r-sm);padding:12px 16px;display:flex;align-items:center;gap:10px;box-shadow:0 12px 32px rgba(0,0,0,.5);transform:translateX(120%);opacity:0;transition:all .28s cubic-bezier(.23,1,.32,1);pointer-events:all;min-width:260px;max-width:340px}
.cx-toast.show{transform:translateX(0);opacity:1}
.cx-toast.hiding{transform:translateX(120%);opacity:0}
.cx-toast-ico{font-size:18px;flex-shrink:0}
.cx-toast-msg{font-size:13px;font-weight:600;color:var(--cx-text);flex:1}

/* ── Desktop / Mobile visibility ── */
.lv-desktop{display:block}
.lv-mobile{display:none}

/* ── Cartes mobile ── */
.lv-mc-card{background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r-sm);margin-bottom:12px;overflow:hidden}
.lv-mc-head{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border-bottom:1px solid var(--cx-border);gap:8px;flex-wrap:wrap}
.lv-mc-id{font-size:15px;font-weight:900;color:var(--cx-brand);font-family:monospace}
.lv-mc-time{font-size:11px;color:var(--cx-muted);margin-left:6px}
.lv-mc-time.urgent{color:var(--cx-amber);font-weight:700}
.lv-mc-fee-box{padding:13px 14px;background:var(--cx-surface2);border-bottom:1px solid var(--cx-border);display:flex;align-items:center;justify-content:space-between}
.lv-mc-fee-lbl{font-size:10.5px;font-weight:700;color:var(--cx-muted);text-transform:uppercase;letter-spacing:.5px}
.lv-mc-fee-val{font-size:20px;font-weight:900;font-family:monospace;color:var(--cx-green)}
.lv-mc-rows{padding:4px 0}
.lv-mc-row{display:flex;align-items:flex-start;justify-content:space-between;padding:9px 14px;border-bottom:1px solid var(--cx-border);gap:10px}
.lv-mc-lbl{font-size:12px;font-weight:700;color:var(--cx-muted);flex-shrink:0;min-width:100px}
.lv-mc-val{font-size:13px;font-weight:600;color:var(--cx-text);text-align:right;word-break:break-word}
.lv-mc-actions{display:flex;gap:8px;padding:10px 14px;border-top:1px solid var(--cx-border);background:var(--cx-surface2)}
.lv-mc-btn{flex:1;padding:9px 12px;border-radius:var(--r-xs);font-size:13px;font-weight:700;border:1px solid;cursor:pointer;font-family:inherit;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:5px}
.lv-mc-btn-done{background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3);color:#34d399}
.lv-mc-btn-done:hover{background:rgba(16,185,129,.2)}
.lv-mc-btn-cancel{background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.25);color:#f87171}
.lv-mc-btn-cancel:hover{background:rgba(239,68,68,.18)}

/* ── MODE CLAIR ── */
body.cx-light{--cx-bg:#F5F7FA;--cx-surface:#fff;--cx-surface2:#eef1f7;--cx-border:rgba(0,0,0,.08);--cx-text:#111827;--cx-text2:#4b5563;--cx-muted:#9ca3af}
body.cx-light,html.cx-light,html.cx-light body{background:#F5F7FA!important}
body.cx-light .cx-topbar{background:#fff;border-bottom-color:rgba(0,0,0,.07);box-shadow:0 1px 4px rgba(0,0,0,.06)}
body.cx-light .cx-tb-btn{background:rgba(0,0,0,.05);border-color:rgba(0,0,0,.08);color:#374151}
body.cx-light .lv-stat,body.cx-light .lv-search{background:#fff;border-color:rgba(0,0,0,.08)}
body.cx-light .lv-table tbody tr:hover{background:rgba(0,0,0,.02)}
body.cx-light .lv-act-btn{background:#f3f4f6;border-color:rgba(0,0,0,.08)}
body.cx-light .lv-mc-card{background:#fff;border-color:rgba(0,0,0,.08)}
body.cx-light .lv-mc-fee-box{background:#f3f4f6;border-color:rgba(0,0,0,.07)}
body.cx-light .lv-mc-head{border-color:rgba(0,0,0,.07)}
body.cx-light .lv-mc-row{border-color:rgba(0,0,0,.06)}
body.cx-light .lv-mc-actions{background:#f3f4f6;border-color:rgba(0,0,0,.07)}
body.cx-light .lv-mc-fee-val{color:#059669}
body.cx-light .lv-mc-btn-done{background:rgba(16,185,129,.08);color:#065f46}
body.cx-light .lv-mc-btn-cancel{background:rgba(239,68,68,.06);color:#b91c1c}

/* ══ RESPONSIVE ══ */
@media(max-width:1024px){
    .cx-sidebar{transform:translateX(-100%)}
    .cx-sidebar.open{transform:translateX(0)}
    .cx-wrap{padding-left:0}
    .cx-hamburger{display:block}
    .cx-close-btn{display:flex}
    .lv-stats{padding:20px 20px 0}
    .lv-toolbar{padding:16px 20px 0}
    .lv-table-wrap{padding:14px 20px 48px}
}
@media(max-width:768px){
    .lv-stats{grid-template-columns:repeat(3,1fr);gap:10px;padding:16px 16px 0}
    .lv-stat{padding:14px 14px}
    .lv-stat-val{font-size:22px}
    .cx-topbar-sub{display:none}
}
@media(max-width:640px){
    .lv-stats{grid-template-columns:1fr 1fr;gap:8px;padding:14px 14px 0}
    .lv-stat:last-child{grid-column:1/-1}
    .lv-stat{padding:12px 12px}
    .lv-stat-val{font-size:20px}
    .lv-toolbar{padding:12px 14px 0;gap:6px;flex-wrap:wrap}
    .lv-search-wrap{flex:1 1 100%;max-width:100%}
    .lv-filter-btn{padding:6px 12px;font-size:11.5px}
    .lv-pulse{display:none}
    .cx-topbar{padding:0 12px;gap:8px}
    .cx-topbar-title{font-size:13px}
    /* Basculement tableau ↔ cartes */
    .lv-desktop{display:none}
    .lv-mobile{display:block;padding:12px 14px 48px}
}
@media(max-width:480px){
    .lv-stats{grid-template-columns:1fr 1fr}
    .lv-stat-val{font-size:18px}
    .lv-stat-lbl{font-size:10px}
    .lv-mc-fee-val{font-size:18px}
}
@media(max-width:360px){
    .lv-stats{grid-template-columns:1fr 1fr}
    .lv-stat{padding:10px 8px}
    .lv-stat-val{font-size:16px}
    .cx-topbar-title{font-size:11.5px}
    .lv-mc-lbl{min-width:85px}
    .lv-mc-fee-val{font-size:16px}
    .lv-filter-btn{padding:5px 8px;font-size:11px}
}
</style>
@endpush

@section('content')

{{-- ══ SIDEBAR ══ --}}
<aside class="cx-sidebar" id="cxSidebar">
    <div class="cx-brand-hd">
        <div class="cx-brand-top">
            <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width: 40px;;height: 40px;object-fit:cover;border-radius:9px"></div>
            <button class="cx-close-btn" id="cxCloseBtn">✕</button>
        </div>
        <div class="cx-sys-badge"><span class="cx-sys-dot"></span> Système actif</div>
    </div>
    <nav class="cx-nav">
        <div class="cx-nav-sec">Principal</div>
        <a href="{{ route('company.dashboard') }}"     class="cx-nav-item"><span class="cx-nav-ico">⊞</span> Tableau de bord</a>
        <a href="{{ route('company.chat.inbox') }}"    class="cx-nav-item"><span class="cx-nav-ico">💬</span> Demandes (Chat)</a>
        <a href="{{ route('company.orders.index') }}"  class="cx-nav-item"><span class="cx-nav-ico">📦</span> Commandes</a>
        <a href="{{ route('company.drivers.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🚴</span> Chauffeurs</a>
        <a href="{{ route('company.livraisons.index') }}" class="cx-nav-item active"><span class="cx-nav-ico">🚚</span> Livraisons</a>
        <a href="{{ route('company.carte.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🗺️</span> Carte en direct</a>
        <a href="{{ route('company.boutiques.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🏪</span> Boutiques</a>
        <a href="{{route('company.clients.index') }}" class="cx-nav-item"><span class="cx-nav-ico">👥</span> Clients</a>

        <div class="cx-nav-sec">Gestion</div>
        <a href="{{ route('company.zones.index') }}" class="cx-nav-item"><span class="cx-nav-ico">📍</span> Zone de livraison</a>
               <a href="{{ route('company.historique.index') }}" class="cx-nav-item"><span class="cx-nav-ico">📊</span> Historique</a>
               <a href="{{ route('company.rapport.index') }}" class="cx-nav-item"><span class="cx-nav-ico">📈</span> Rapport</a>

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

<div class="cx-overlay" id="cxOverlay"></div>

<div class="cx-wrap">
<main class="cx-main">

    {{-- Topbar --}}
    <div class="cx-topbar">
        <button class="cx-hamburger" id="cxHamburger">☰</button>
        <span class="cx-topbar-title">🚚 Livraisons en cours</span>
        <span class="cx-topbar-sub">· {{ $company->name }}</span>
      
    </div>

    {{-- Stats --}}
    <div class="lv-stats">
        <div class="lv-stat" style="--s-color:#f59e0b;--s-bg:rgba(245,158,11,.1)">
            <div class="lv-stat-ico">🚚</div>
            <div>
                <div class="lv-stat-val" id="statTotal">{{ $stats['total'] }}</div>
                <div class="lv-stat-lbl">En cours total</div>
            </div>
        </div>
        <div class="lv-stat" style="--s-color:#3b82f6;--s-bg:rgba(59,130,246,.1)">
            <div class="lv-stat-ico">✅</div>
            <div>
                <div class="lv-stat-val" id="statConfirm">{{ $stats['confirmees'] }}</div>
                <div class="lv-stat-lbl">Assignées (départ)</div>
            </div>
        </div>
        <div class="lv-stat" style="--s-color:#10b981;--s-bg:rgba(16,185,129,.1)">
            <div class="lv-stat-ico">📍</div>
            <div>
                <div class="lv-stat-val" id="statLiv">{{ $stats['en_livraison'] }}</div>
                <div class="lv-stat-lbl">En route</div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="lv-toolbar">
        <div class="lv-search-wrap">
            <svg class="lv-search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input class="lv-search" type="text" id="lvSearch" placeholder="Rechercher boutique, chauffeur, destination…" oninput="filterRows()">
        </div>
        <button class="lv-filter-btn active" id="f-all"       onclick="setFilter('all')">Tous</button>
        <button class="lv-filter-btn"        id="f-confirmée" onclick="setFilter('confirmée')">✅ Assignées</button>
        <button class="lv-filter-btn"        id="f-en_livraison" onclick="setFilter('en_livraison')">🚚 En route</button>
        <div class="lv-pulse">
            <span class="lv-pulse-dot"></span> Temps réel
        </div>
    </div>

    {{-- Table DESKTOP --}}
    <div class="lv-desktop">
    <div class="lv-table-wrap">
        <table class="lv-table" id="lvTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Boutique</th>
                    <th>Client</th>
                    <th>Destination</th>
                    <th>Chauffeur</th>
                    <th>Statut</th>
                    <th>Mis à jour</th>
                    <th>Frais</th>
                </tr>
            </thead>
            <tbody id="lvBody">
            @forelse($orders as $order)
            @php
                $isLiv = $order->status === 'en_livraison';
                $drvIni = $order->driver
                    ? strtoupper(substr($order->driver->name,0,1)).strtoupper(substr(explode(' ',$order->driver->name)[1]??'X',0,1))
                    : '?';
            @endphp
            <tr data-status="{{ $order->status }}"
                data-search="{{ strtolower(optional($order->shop)->name.' '.optional($order->client)->name.' '.($order->delivery_destination ?? '').' '.optional($order->driver)->name) }}">
                <td><span style="font-size:12px;font-weight:800;color:var(--cx-brand)">#{{ $order->id }}</span></td>
                <td style="font-weight:700">{{ optional($order->shop)->name ?? '—' }}</td>
                <td style="color:var(--cx-text2)">{{ optional($order->client)->name ?? '—' }}</td>
                <td><div class="lv-dest" title="{{ $order->delivery_destination ?? '' }}">{{ $order->delivery_destination ?? '—' }}</div></td>
                <td>
                    @if($order->driver)
                    <div class="lv-driver">
                        <div class="lv-driver-av">{{ $drvIni }}</div>
                        <div>
                            <div class="lv-driver-name">{{ $order->driver->name }}</div>
                            <div class="lv-driver-phone">{{ $order->driver->phone ?? '' }}</div>
                        </div>
                    </div>
                    @else
                    <span style="color:var(--cx-muted);font-size:12px">Non assigné</span>
                    @endif
                </td>
                <td>
                    @if($isLiv)
                        <span class="lv-badge b-livraison"><span class="lv-badge-dot"></span> En route</span>
                    @else
                        <span class="lv-badge b-confirm"><span class="lv-badge-dot"></span> Assignée</span>
                    @endif
                </td>
                <td>
                    @php $diff = $order->updated_at->diffInMinutes(now()); @endphp
                    <span class="lv-time {{ $diff > 60 ? 'urgent' : '' }}">{{ $order->updated_at->diffForHumans() }}</span>
                </td>
                <td style="font-weight:700;color:var(--cx-green)">
                    {{ $order->delivery_fee ? number_format($order->delivery_fee,0,',',' ') : '—' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="8">
                <div class="lv-empty">
                    <div class="lv-empty-ico">🚚</div>
                    <div class="lv-empty-title">Aucune livraison en cours</div>
                    <div class="lv-empty-sub">Les livraisons assignées et en route apparaîtront ici.</div>
                </div>
            </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </div>{{-- /lv-desktop --}}

    {{-- Cartes MOBILE --}}
    <div class="lv-mobile" id="lvMobile">
        @forelse($orders as $order)
        @php
            $isLiv2  = $order->status === 'en_livraison';
            $drvIni2 = $order->driver
                ? strtoupper(substr($order->driver->name,0,1)).strtoupper(substr(explode(' ',$order->driver->name)[1]??'X',0,1))
                : '?';
            $diff2 = $order->updated_at->diffInMinutes(now());
        @endphp
        <div class="lv-mc-card"
             data-status="{{ $order->status }}"
             data-search="{{ strtolower(optional($order->shop)->name.' '.optional($order->client)->name.' '.($order->delivery_destination ?? '').' '.optional($order->driver)->name) }}">

            {{-- En-tête --}}
            <div class="lv-mc-head">
                <div>
                    <span class="lv-mc-id">#{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}</span>
                    <span class="lv-mc-time {{ $diff2 > 60 ? 'urgent' : '' }}">· {{ $order->updated_at->diffForHumans() }}</span>
                </div>
                @if($isLiv2)
                    <span class="lv-badge b-livraison"><span class="lv-badge-dot"></span> En route</span>
                @else
                    <span class="lv-badge b-confirm"><span class="lv-badge-dot"></span> Assignée</span>
                @endif
            </div>

            {{-- Frais --}}
            <div class="lv-mc-fee-box">
                <span class="lv-mc-fee-lbl">🚚 Frais livraison</span>
                <span class="lv-mc-fee-val">
                    {{ $order->delivery_fee ? number_format($order->delivery_fee,0,',',' ').' '.($company->currency ?? 'GNF') : '—' }}
                </span>
            </div>

            {{-- Détails --}}
            <div class="lv-mc-rows">
                <div class="lv-mc-row">
                    <span class="lv-mc-lbl">🏪 Boutique</span>
                    <span class="lv-mc-val">{{ optional($order->shop)->name ?? '—' }}</span>
                </div>
                <div class="lv-mc-row">
                    <span class="lv-mc-lbl">👤 Client</span>
                    <span class="lv-mc-val">{{ optional($order->client)->name ?? '—' }}</span>
                </div>
                @if($order->delivery_destination)
                <div class="lv-mc-row">
                    <span class="lv-mc-lbl">📍 Destination</span>
                    <span class="lv-mc-val">{{ $order->delivery_destination }}</span>
                </div>
                @endif
                <div class="lv-mc-row" style="border-bottom:none">
                    <span class="lv-mc-lbl">🚴 Chauffeur</span>
                    <span class="lv-mc-val">
                        @if($order->driver)
                            <div style="display:flex;align-items:center;gap:7px;justify-content:flex-end">
                                <div class="lv-driver-av" style="width:24px;height:24px;font-size:9px;">{{ $drvIni2 }}</div>
                                <div style="text-align:right">
                                    <div style="font-weight:700;font-size:13px">{{ $order->driver->name }}</div>
                                    @if($order->driver->phone)<div style="font-size:11px;color:var(--cx-muted)">{{ $order->driver->phone }}</div>@endif
                                </div>
                            </div>
                        @else
                            <span style="color:var(--cx-muted);font-style:italic;font-size:12.5px">Non assigné</span>
                        @endif
                    </span>
                </div>
            </div>

        </div>
        @empty
        <div class="lv-empty" style="background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r-sm)">
            <div class="lv-empty-ico">🚚</div>
            <div class="lv-empty-title">Aucune livraison en cours</div>
            <div class="lv-empty-sub">Les livraisons assignées et en route apparaîtront ici.</div>
        </div>
        @endforelse
    </div>{{-- /lv-mobile --}}

</main>
</div>

<div class="cx-toast-wrap" id="cxToasts"></div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]')?.content ?? '';

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

/* ── Filtre + recherche ── */
let currentFilter = 'all';

function setFilter(f) {
    currentFilter = f;
    document.querySelectorAll('.lv-filter-btn').forEach(b => {
        b.classList.remove('active','f-confirm','f-livraison');
    });
    const btn = document.getElementById('f-' + f);
    if (f === 'confirmée')    btn.classList.add('f-confirm');
    else if (f === 'en_livraison') btn.classList.add('f-livraison');
    else btn.classList.add('active');
    filterRows();
}

function filterRows() {
    const q = document.getElementById('lvSearch').value.toLowerCase();
    document.querySelectorAll('#lvBody tr[data-status], #lvMobile .lv-mc-card[data-status]').forEach(el => {
        const matchF = currentFilter === 'all' || el.dataset.status === currentFilter;
        const matchQ = !q || (el.dataset.search || '').includes(q);
        el.style.display = matchF && matchQ ? '' : 'none';
    });
}

/* ── Mise à jour statut ── */
async function updateStatus(orderId, status) {
    const label = status === 'livrée' ? 'Marquer cette livraison comme livrée ?' : 'Annuler cette livraison ?';
    if (!confirm(label)) return;
    try {
        const res = await fetch(`/company/orders/${orderId}/status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ status })
        });
        const d = await res.json();
        if (d.success) {
            showToast(status === 'livrée' ? '✅ Livraison terminée !' : '❌ Livraison annulée', status === 'livrée' ? 'green' : 'red');
            setTimeout(() => pollData(), 600);
        }
    } catch(e) { showToast('❌ Erreur réseau', 'red'); }
}

/* ── Polling temps réel ── */
let lastTs = 0;

async function pollData() {
    try {
        const res = await fetch('{{ route('company.livraisons.data') }}', {
            credentials: 'same-origin', headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const d = await res.json();
        if (!d.ok) return;

        const orders = d.orders;

        // Mettre à jour les compteurs
        const total    = orders.length;
        const confirm  = orders.filter(o => o.status === 'confirmée').length;
        const enRoute  = orders.filter(o => o.status === 'en_livraison').length;
        document.getElementById('statTotal').textContent   = total;
        document.getElementById('statConfirm').textContent = confirm;
        document.getElementById('statLiv').textContent     = enRoute;

        // Reconstruire le tbody
        const tbody = document.getElementById('lvBody');
        if (!orders.length) {
            tbody.innerHTML = `<tr><td colspan="8">
                <div class="lv-empty">
                    <div class="lv-empty-ico">🚚</div>
                    <div class="lv-empty-title">Aucune livraison en cours</div>
                    <div class="lv-empty-sub">Les livraisons assignées et en route apparaîtront ici.</div>
                </div>
            </td></tr>`;
            lastTs = 0; return;
        }

        // Détecter les nouvelles lignes (timestamp plus récent)
        const maxTs = Math.max(...orders.map(o => o.updated_ts));
        const isNew = maxTs > lastTs && lastTs > 0;
        lastTs = maxTs;

        tbody.innerHTML = orders.map(o => {
            const isLiv = o.status === 'en_livraison';
            const badge = isLiv
                ? `<span class="lv-badge b-livraison"><span class="lv-badge-dot"></span> En route</span>`
                : `<span class="lv-badge b-confirm"><span class="lv-badge-dot"></span> Assignée</span>`;
            const drvIni = o.driver !== 'Non assigné'
                ? o.driver.split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase()
                : '?';
            const driver = o.driver !== 'Non assigné'
                ? `<div class="lv-driver">
                    <div class="lv-driver-av">${esc(drvIni)}</div>
                    <div><div class="lv-driver-name">${esc(o.driver)}</div><div class="lv-driver-phone">${esc(o.driver_phone)}</div></div>
                   </div>`
                : `<span style="color:var(--cx-muted);font-size:12px">Non assigné</span>`;
            return `<tr data-status="${o.status}" data-search="${esc((o.shop+' '+o.client+' '+o.destination+' '+o.driver).toLowerCase())}">
                <td><span style="font-size:12px;font-weight:800;color:var(--cx-brand)">#${o.id}</span></td>
                <td style="font-weight:700">${esc(o.shop)}</td>
                <td style="color:var(--cx-text2)">${esc(o.client)}</td>
                <td><div class="lv-dest" title="${esc(o.destination)}">${esc(o.destination)}</div></td>
                <td>${driver}</td>
                <td>${badge}</td>
                <td><span class="lv-time">${esc(o.updated_at)}</span></td>
                <td style="font-weight:700;color:var(--cx-green)">${o.fee ? Number(o.fee).toLocaleString('fr-FR') : '—'}</td>
            </tr>`;
        }).join('');

        filterRows();
        if (isNew) showToast('🚚 Mise à jour des livraisons', 'blue');

    } catch(e) { /* silencieux */ }
}

function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, c =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])
    );
}

/* ── Toast ── */
function showToast(msg, color = 'green') {
    const wrap = document.getElementById('cxToasts');
    const t = document.createElement('div');
    t.className = 'cx-toast';
    const colors = { green:'#10b981', red:'#ef4444', blue:'#3b82f6' };
    t.innerHTML = `<span style="width:3px;height:36px;background:${colors[color]??'#10b981'};border-radius:2px;flex-shrink:0"></span>
                   <span class="cx-toast-msg">${msg}</span>`;
    wrap.appendChild(t);
    requestAnimationFrame(() => requestAnimationFrame(() => t.classList.add('show')));
    setTimeout(() => { t.classList.add('hiding'); setTimeout(() => t.remove(), 320); }, 3500);
}

// Initialiser lastTs avec la commande la plus récente de la page initiale
@if($orders->count())
lastTs = {{ $orders->max(fn($o) => $o->updated_at->timestamp) }};
@endif

pollData();
setInterval(pollData, 5000);
</script>
@endpush
