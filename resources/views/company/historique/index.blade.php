@extends('layouts.app')
@section('title', 'Historique · ' . $company->name)
@php
    $bodyClass = 'cx-dashboard';
    $u   = auth()->user();
    $ini = strtoupper(substr($u->name ?? 'A', 0, 1)) . strtoupper(substr(explode(' ', $u->name ?? 'A ')[1] ?? 'X', 0, 1));
    $fmt = fn($n) => number_format($n ?? 0, 0, ',', ' ') . ' ' . ($company->currency ?? 'GNF');
    $curPeriod = request('period', '');
    $curStatus = request('status', 'all');
    $curDriver = request('driver_id', '');
@endphp

@push('styles')
<script>(function(){ if(localStorage.getItem('cx-theme')==='light') document.documentElement.classList.add('cx-prelight'); })();</script>
<style>html.cx-prelight body{background:#F5F7FA!important}</style>
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --cx-bg:#0b0d22; --cx-surface:#0d1226; --cx-surface2:#111930;
    --cx-border:rgba(255,255,255,.07); --cx-border2:rgba(255,255,255,.12);
    --cx-brand:#7c3aed; --cx-brand2:#6d28d9;
    --cx-text:#e2e8f0; --cx-text2:#94a3b8; --cx-muted:#475569;
    --cx-green:#10b981; --cx-amber:#f59e0b; --cx-red:#ef4444; --cx-blue:#3b82f6;
    --cx-sb-w:220px; --r:16px; --r-sm:10px; --r-xs:7px;
}
html,body{margin:0;font-family:'Segoe UI',system-ui,sans-serif;background:var(--cx-bg)!important;color:var(--cx-text);-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}

body.cx-dashboard>nav,body.cx-dashboard>header,body.cx-dashboard .navbar,
body.cx-dashboard>.topbar-global,body.cx-dashboard .app-footer,
body.cx-dashboard .app-flash{display:none!important}
body.cx-dashboard>main.app-main{padding:0!important;margin:0!important;max-width:100%!important;width:100%!important;min-height:100vh;background:var(--cx-bg)!important}

/* ── LAYOUT ── */
.cx-wrap{display:flex;min-height:100vh;padding-left:var(--cx-sb-w);background:var(--cx-bg)}

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

/* ── TOPBAR ── */
.cx-topbar{height:60px;background:var(--cx-surface);border-bottom:1px solid var(--cx-border);display:flex;align-items:center;gap:12px;padding:0 20px;position:sticky;top:0;z-index:100;flex-shrink:0}
.cx-hamburger{display:none;background:none;border:none;color:var(--cx-text2);font-size:18px;cursor:pointer;padding:4px 8px;border-radius:6px}
.cx-topbar-title{font-size:15px;font-weight:800;color:var(--cx-text)}
.cx-tb-right{margin-left:auto;display:flex;align-items:center;gap:8px}
.cx-tb-link{height:34px;padding:0 12px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid var(--cx-border);color:var(--cx-text2);font-size:13px;font-weight:600;cursor:pointer;transition:background .14s,color .14s;display:inline-flex;align-items:center;gap:6px;text-decoration:none}
.cx-tb-link:hover{background:rgba(255,255,255,.1);color:#fff}

/* ── BANNER ── */
.hx-banner{
    background:linear-gradient(135deg,#1e1b4b 0%,#312e81 40%,#4338ca 100%);
    padding:24px 28px 20px;position:relative;overflow:hidden;flex-shrink:0;
}
.hx-banner::before{content:'';position:absolute;top:-60px;right:-80px;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(124,58,237,.18) 0%,transparent 70%);pointer-events:none}
.hx-banner-grid{position:absolute;inset:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:40px 40px}
.hx-banner-inner{max-width:1300px;margin:0 auto;position:relative;z-index:1}
.hx-banner-title{font-size:22px;font-weight:900;color:#fff;letter-spacing:-.4px;display:flex;align-items:center;gap:10px;margin-bottom:4px}
.hx-banner-sub{font-size:13px;color:rgba(255,255,255,.55)}

/* Stats */
.hx-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-top:20px}
.hx-stat{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:var(--r-sm);padding:14px 16px;backdrop-filter:blur(4px);position:relative;overflow:hidden}
.hx-stat::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--s-accent,var(--cx-brand))}
.hx-stat-val{font-size:22px;font-weight:900;color:#fff;line-height:1;letter-spacing:-.5px}
.hx-stat-lbl{font-size:10.5px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-top:4px}

/* ── BODY ── */
.hx-body{max-width:1300px;margin:0 auto;padding:20px 24px 80px;width:100%}

/* ── FILTER CARD ── */
.hx-filters{background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r);padding:16px 18px;margin-bottom:16px}
.hx-filter-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.hx-period-btns{display:flex;gap:6px;flex-wrap:wrap}
.hx-period-btn{padding:6px 14px;border-radius:30px;font-size:12px;font-weight:700;border:1px solid var(--cx-border);cursor:pointer;transition:all .15s;background:transparent;color:var(--cx-text2);font-family:inherit}
.hx-period-btn:hover{background:var(--cx-surface2);color:var(--cx-text)}
.hx-period-btn.active{background:var(--cx-brand);color:#fff;border-color:var(--cx-brand)}
.hx-sep{width:1px;height:24px;background:var(--cx-border);flex-shrink:0}
.hx-input{padding:8px 12px;background:var(--cx-surface2);border:1px solid var(--cx-border);border-radius:var(--r-xs);color:var(--cx-text);font-size:13px;font-family:inherit;outline:none;transition:border-color .15s}
.hx-input:focus{border-color:var(--cx-brand);box-shadow:0 0 0 3px rgba(124,58,237,.12)}
.hx-select{padding:8px 12px;background:var(--cx-surface2);border:1px solid var(--cx-border);border-radius:var(--r-xs);color:var(--cx-text);font-size:13px;font-family:inherit;outline:none;cursor:pointer;min-width:160px}
.hx-search-wrap{position:relative;flex:1;min-width:180px}
.hx-search-ico{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--cx-muted);pointer-events:none}
.hx-search{width:100%;padding:8px 12px 8px 34px;background:var(--cx-surface2);border:1px solid var(--cx-border);border-radius:var(--r-xs);color:var(--cx-text);font-size:13px;font-family:inherit;outline:none;transition:border-color .15s}
.hx-search:focus{border-color:var(--cx-brand);box-shadow:0 0 0 3px rgba(124,58,237,.12)}
.hx-search::placeholder,.hx-input::placeholder{color:var(--cx-muted)}
.hx-filter-second{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:10px;padding-top:10px;border-top:1px solid var(--cx-border)}
.hx-date-range{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.hx-date-lbl{font-size:11px;font-weight:700;color:var(--cx-muted);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}

/* ── TABLE ── */
.hx-table-card{background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r);overflow:hidden}
.hx-table-wrap{overflow-x:auto}
.hx-table{width:100%;border-collapse:collapse;min-width:800px}
.hx-table thead th{padding:12px 15px;text-align:left;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--cx-muted);background:var(--cx-surface2);border-bottom:1px solid var(--cx-border);white-space:nowrap;position:sticky;top:0;z-index:10}
.hx-table td{padding:14px 15px;border-bottom:1px solid var(--cx-border);vertical-align:middle;font-size:13.5px;color:var(--cx-text)}
.hx-table tr:last-child td{border-bottom:none}
.hx-table tbody tr{transition:background .12s}
.hx-table tbody tr:hover{background:rgba(255,255,255,.03)}

/* Cellules */
.hx-order-id{font-size:14px;font-weight:800;color:var(--cx-brand);font-family:monospace}
.hx-date{font-size:12px;color:var(--cx-muted);margin-top:2px}
.hx-shop{font-weight:700;color:var(--cx-text);font-size:13.5px}
.hx-shop-addr{font-size:11.5px;color:var(--cx-muted);margin-top:2px}
.hx-client{font-weight:600;color:var(--cx-text);font-size:13px}
.hx-phone{font-size:11.5px;color:var(--cx-muted);margin-top:1px}
.hx-dest{font-size:12.5px;color:var(--cx-text2);max-width:180px;word-break:break-word;line-height:1.4}
.hx-driver{display:flex;align-items:center;gap:8px}
.hx-driver-av{width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;overflow:hidden}
.hx-driver-av img{width:100%;height:100%;object-fit:cover}
.hx-driver-name{font-weight:700;color:var(--cx-text);font-size:13px}
.hx-driver-phone{font-size:11.5px;color:var(--cx-muted)}
.hx-no-driver{color:var(--cx-muted);font-size:12.5px;font-style:italic}
.hx-fee{font-weight:800;color:var(--cx-green);font-size:13.5px;font-family:monospace}
.hx-amount{font-weight:800;color:var(--cx-text);font-family:monospace;font-size:13.5px}

/* Badges */
.hx-badge{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;padding:4px 12px;border-radius:20px;white-space:nowrap}
.hx-badge-livree  {background:rgba(16,185,129,.12);color:#34d399;border:1px solid rgba(16,185,129,.25)}
.hx-badge-annulee {background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.2)}

/* Empty */
.hx-empty{text-align:center;padding:64px 24px;display:flex;flex-direction:column;align-items:center;gap:12px}
.hx-empty-ico{font-size:52px;opacity:.2}
.hx-empty-title{font-size:16px;font-weight:800;color:var(--cx-text)}
.hx-empty-sub{font-size:13px;color:var(--cx-muted)}

/* Pagination */
.hx-pagination{padding:16px 18px;border-top:1px solid var(--cx-border)}
.hx-pagination .pagination{display:flex;gap:4px;list-style:none;margin:0;padding:0;justify-content:center;flex-wrap:wrap}
.hx-pagination .page-item .page-link{display:flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 10px;background:var(--cx-surface2);border:1px solid var(--cx-border);border-radius:var(--r-xs);color:var(--cx-text2);font-size:13px;font-weight:600;text-decoration:none;transition:all .15s}
.hx-pagination .page-item.active .page-link{background:var(--cx-brand);border-color:var(--cx-brand);color:#fff}
.hx-pagination .page-item.disabled .page-link{opacity:.4;pointer-events:none}

/* ── MODE CLAIR ── */
body.cx-light{--cx-bg:#F5F7FA;--cx-surface:#fff;--cx-surface2:#eef1f7;--cx-border:rgba(0,0,0,.08);--cx-border2:rgba(0,0,0,.13);--cx-text:#111827;--cx-text2:#4b5563;--cx-muted:#9ca3af}
body.cx-light,html.cx-light body{background:#F5F7FA!important}
body.cx-light .cx-topbar{background:#fff;border-bottom-color:rgba(0,0,0,.07);box-shadow:0 1px 4px rgba(0,0,0,.06)}
body.cx-light .cx-tb-link{background:rgba(0,0,0,.05);border-color:rgba(0,0,0,.08);color:#374151}
body.cx-light .hx-filters,.body.cx-light .hx-table-card{background:#fff;border-color:rgba(0,0,0,.08)}
body.cx-light .hx-input,.body.cx-light .hx-select,.body.cx-light .hx-search{background:#f3f4f6;border-color:rgba(0,0,0,.1);color:#111827}
body.cx-light .hx-table thead th{background:#f3f4f6}
body.cx-light .hx-table tbody tr:hover{background:rgba(0,0,0,.02)}
body.cx-light .hx-period-btn{color:#4b5563;border-color:rgba(0,0,0,.1)}
body.cx-light .hx-period-btn:hover{background:#e5e7eb}
body.cx-light .hx-period-btn.active{background:var(--cx-brand);color:#fff}
body.cx-light .hx-sep{background:rgba(0,0,0,.1)}
body.cx-light .hx-filter-second{border-top-color:rgba(0,0,0,.07)}
body.cx-light .hx-order-id{color:var(--cx-brand)}
body.cx-light .hx-badge-livree{background:rgba(16,185,129,.1);color:#065f46}
body.cx-light .hx-badge-annulee{background:rgba(239,68,68,.08);color:#b91c1c}
body.cx-light .hx-fee{color:#059669}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
    .cx-sidebar{transform:translateX(-100%)}
    .cx-sidebar.open{transform:translateX(0)}
    .cx-wrap{padding-left:0}
    .cx-hamburger{display:block}
    .hx-stats{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:768px){
    .hx-stats{grid-template-columns:repeat(2,1fr)}
    .hx-banner{padding:18px 16px 16px}
    .hx-body{padding:14px 14px 60px}
    .hx-banner-title{font-size:18px}
}
@media(max-width:640px){
    .hx-stats{grid-template-columns:1fr 1fr}
    .hx-filter-row{gap:6px}
    .hx-period-btns{gap:4px}
    .hx-period-btn{padding:5px 10px;font-size:11px}
    .hx-sep{display:none}
    .hx-search-wrap{flex:1 1 100%}
    .hx-select{min-width:unset;width:100%}
    .hx-date-range{width:100%}
    .hx-input{flex:1}
    /* Table → cards */
    .hx-table-wrap{overflow-x:unset}
    .hx-table,.hx-table tbody{display:block}
    .hx-table thead{display:none}
    .hx-table tbody tr{display:block;border:1px solid var(--cx-border);border-radius:var(--r-sm);margin-bottom:10px;padding:12px 14px;background:var(--cx-surface)}
    .hx-table td{display:flex;align-items:flex-start;padding:5px 0;border:none;font-size:13.5px;gap:10px}
    .hx-table td::before{content:attr(data-label);font-size:10.5px;font-weight:800;color:var(--cx-muted);text-transform:uppercase;letter-spacing:.6px;min-width:80px;flex-shrink:0;padding-top:2px}
    .hx-table td[data-label="Actions"],.hx-table td:last-child::before{display:none}
}
@media(max-width:480px){
    .hx-stats{grid-template-columns:1fr 1fr}
    .hx-stat-val{font-size:18px}
}
@media(max-width:360px){
    .hx-stats{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')

{{-- ══ SIDEBAR ══ --}}
<aside class="cx-sidebar" id="cxSidebar">
    <div class="cx-brand-hd">
        <div class="cx-brand-top">
            <a href="{{ route('company.dashboard') }}" class="cx-logo">
                <div style="width:34px;height:34px;border-radius:9px;overflow:hidden;flex-shrink:0"><img src="/images/Shopio3.jpeg" alt="" style="width:100%;height:100%;object-fit:cover"></div>
                <span>{{ $company->name }}</span>
            </a>
            <button class="cx-close-btn" id="cxCloseBtn">✕</button>
        </div>
        <div class="cx-sys-badge"><span class="cx-sys-dot"></span> Système actif</div>
    </div>
    <nav class="cx-nav">
        <div class="cx-nav-sec">Principal</div>
        <a href="{{ route('company.dashboard') }}"        class="cx-nav-item"><span class="cx-nav-ico">⊞</span> Tableau de bord</a>
        <a href="{{ route('company.chat.inbox') }}"       class="cx-nav-item"><span class="cx-nav-ico">💬</span> Demandes (Chat)</a>
        <a href="{{ route('company.orders.index') }}"     class="cx-nav-item"><span class="cx-nav-ico">📦</span> Commandes</a>
        <a href="{{ route('company.livraisons.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🚚</span> Livraisons</a>
        <a href="{{ route('company.carte.index') }}"      class="cx-nav-item"><span class="cx-nav-ico">🗺️</span> Carte en direct</a>
        <a href="{{ route('company.drivers.index') }}"    class="cx-nav-item"><span class="cx-nav-ico">🚴</span> Chauffeurs</a>
      
        <a href="{{ route('company.boutiques.index') }}" class="cx-nav-item"><span class="cx-nav-ico">🏪</span> Boutiques</a>
        <a href="{{route('company.clients.index')}}" class="cx-nav-item"><span class="cx-nav-ico">👥</span> Clients</a>

                <div class="cx-nav-sec">Gestion</div>
        <a href="{{route('company.zones.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📍</span> Zone de livraison
        </a>
       
         <a href="{{ route('company.historique.index') }}" class="cx-nav-item active"><span class="cx-nav-ico">📊</span> Historique</a>
         <a href="{{ route('company.rapport.index') }}" class="cx-nav-item"><span class="cx-nav-ico">📈</span> Rapport</a>

        <div class="cx-nav-sec">Configuration</div>
        
               <a href="{{ route('company.parametre.index') }}" class="cx-nav-item"><span class="cx-nav-ico">⚙️</span> Paramètres</a>
               <a href="{{ route('company.users.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">👤</span> Utilisateurs
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
                <button type="submit" class="cx-logout-btn" title="Déconnexion">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
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

    {{-- TOPBAR --}}
    <div class="cx-topbar">
        <button class="cx-hamburger" id="cxHamburger">☰</button>
        <span class="cx-topbar-title">📊 Historique des livraisons</span>
        <div class="cx-tb-right">
            @if(request('shop_id'))
                <a href="{{ route('company.historique.index') }}" class="cx-tb-link" style="background:rgba(124,58,237,.15);border-color:rgba(124,58,237,.3);color:#c4b5fd;">
                    ✕ Voir tout l'historique
                </a>
            @endif
            <a href="{{ route('company.livraisons.index') }}" class="cx-tb-link">🚚 En cours</a>
        </div>
    </div>

    {{-- BOUTIQUE FILTER BANNER --}}
    @if(request('shop_id'))
    @php $filteredShop = \App\Models\Shop::find(request('shop_id')); @endphp
    @if($filteredShop)
    <div style="background:linear-gradient(90deg,rgba(124,58,237,.15),rgba(99,102,241,.08));border-bottom:1px solid rgba(124,58,237,.2);padding:10px 24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <span style="font-size:18px;">🏪</span>
        <div>
            <div style="font-size:13px;font-weight:800;color:#c4b5fd;">Filtré par boutique : {{ $filteredShop->name }}</div>
            <div style="font-size:11.5px;color:var(--cx-text2);">Affichage de l'historique uniquement pour cette boutique</div>
        </div>
        <a href="{{ route('company.historique.index') }}" style="margin-left:auto;font-size:12px;color:var(--cx-muted);padding:5px 12px;border:1px solid rgba(124,58,237,.3);border-radius:6px;background:rgba(124,58,237,.1);color:#c4b5fd;white-space:nowrap;">
            ✕ Effacer le filtre boutique
        </a>
    </div>
    @endif
    @endif

    {{-- BANNER --}}
    <div class="hx-banner">
        <div class="hx-banner-grid"></div>
        <div class="hx-banner-inner">
            <div class="hx-banner-title">📊 Historique des livraisons</div>
            <div class="hx-banner-sub">{{ $company->name }} · Toutes les livraisons terminées et annulées</div>
            <div class="hx-stats">
                <div class="hx-stat" style="--s-accent:#34d399">
                    <div class="hx-stat-val">{{ $stats['total_livrees'] }}</div>
                    <div class="hx-stat-lbl">Total livrées</div>
                </div>
                <div class="hx-stat" style="--s-accent:#f87171">
                    <div class="hx-stat-val">{{ $stats['total_annulees'] }}</div>
                    <div class="hx-stat-lbl">Annulées</div>
                </div>
                <div class="hx-stat" style="--s-accent:#34d399">
                    <div class="hx-stat-val">{{ $stats['livrees_today'] }}</div>
                    <div class="hx-stat-lbl">Livrées auj.</div>
                </div>
                <div class="hx-stat" style="--s-accent:#a78bfa">
                    <div class="hx-stat-val" style="font-size:15px">{{ $fmt($stats['revenus_month']) }}</div>
                    <div class="hx-stat-lbl">Revenus ce mois</div>
                </div>
                <div class="hx-stat" style="--s-accent:#60a5fa">
                    <div class="hx-stat-val" style="font-size:15px">{{ $fmt($stats['revenus_total']) }}</div>
                    <div class="hx-stat-lbl">Revenus total</div>
                </div>
            </div>
        </div>
    </div>

    {{-- BODY --}}
    <div class="hx-body">

        {{-- FILTRES --}}
        <form method="GET" action="{{ route('company.historique.index') }}" id="hxForm">
            <div class="hx-filters">

                {{-- Ligne 1 : périodes + statut + recherche --}}
                <div class="hx-filter-row">
                    <div class="hx-period-btns">
                        <button type="button" class="hx-period-btn {{ $curPeriod===''?'active':'' }}"     onclick="setPeriod('')">Tout</button>
                        <button type="button" class="hx-period-btn {{ $curPeriod==='today'?'active':'' }}" onclick="setPeriod('today')">Aujourd'hui</button>
                        <button type="button" class="hx-period-btn {{ $curPeriod==='week'?'active':'' }}"  onclick="setPeriod('week')">Cette semaine</button>
                        <button type="button" class="hx-period-btn {{ $curPeriod==='month'?'active':'' }}" onclick="setPeriod('month')">Ce mois</button>
                    </div>
                    <div class="hx-sep"></div>
                    <select class="hx-select" name="status" onchange="document.getElementById('hxForm').submit()">
                        <option value="all"    {{ $curStatus==='all'?'selected':'' }}>Tous les statuts</option>
                        <option value="livrée" {{ $curStatus==='livrée'?'selected':'' }}>✅ Livrées</option>
                        <option value="annulée"{{ $curStatus==='annulée'?'selected':'' }}>❌ Annulées</option>
                    </select>
                    @if($drivers->count())
                    <select class="hx-select" name="driver_id" onchange="document.getElementById('hxForm').submit()">
                        <option value="">Tous les chauffeurs</option>
                        @foreach($drivers as $drv)
                        <option value="{{ $drv->id }}" {{ $curDriver==$drv->id?'selected':'' }}>{{ $drv->name }}</option>
                        @endforeach
                    </select>
                    @endif
                    <div class="hx-search-wrap">
                        <svg class="hx-search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input class="hx-search" type="text" name="search" id="hxSearch"
                               placeholder="N° commande, client, boutique, chauffeur…"
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Ligne 2 : plage de dates --}}
                <div class="hx-filter-second">
                    <div class="hx-date-range">
                        <span class="hx-date-lbl">Du</span>
                        <input class="hx-input" type="date" name="date_from" value="{{ request('date_from') }}"
                               onchange="document.getElementById('hxForm').submit()">
                        <span class="hx-date-lbl">Au</span>
                        <input class="hx-input" type="date" name="date_to" value="{{ request('date_to') }}"
                               onchange="document.getElementById('hxForm').submit()">
                        @if(request()->hasAny(['date_from','date_to','search','status','driver_id','period','shop_id']))
                        <a href="{{ route('company.historique.index') }}"
                           style="font-size:12px;color:var(--cx-muted);text-decoration:none;padding:6px 12px;border:1px solid var(--cx-border);border-radius:var(--r-xs);background:transparent;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center;gap:4px">
                            ✕ Réinitialiser
                        </a>
                        @endif
                    </div>
                    <div style="margin-left:auto;font-size:12px;color:var(--cx-muted)">
                        {{ $orders->total() }} résultat{{ $orders->total() > 1 ? 's' : '' }}
                    </div>
                </div>

            </div>

            <input type="hidden" name="period" id="hxPeriod" value="{{ $curPeriod }}">
            @if(request('shop_id'))
            <input type="hidden" name="shop_id" value="{{ request('shop_id') }}">
            @endif
        </form>

        {{-- TABLE --}}
        <div class="hx-table-card">
            <div class="hx-table-wrap">
                <table class="hx-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Boutique</th>
                            <th>Client</th>
                            <th>Destination</th>
                            <th>Chauffeur</th>
                            <th>Frais liv.</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date clôture</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td data-label="Commande">
                            <div>
                                <div class="hx-order-id">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="hx-date">{{ $order->created_at->format('d/m/y') }}</div>
                            </div>
                        </td>
                        <td data-label="Boutique">
                            <div>
                                <div class="hx-shop">🏪 {{ optional($order->shop)->name ?? '—' }}</div>
                                @if(optional($order->shop)->address)
                                <div class="hx-shop-addr">📍 {{ $order->shop->address }}</div>
                                @endif
                            </div>
                        </td>
                        <td data-label="Client">
                            <div>
                                <div class="hx-client">{{ optional($order->client)->name ?? '—' }}</div>
                                <div class="hx-phone">{{ optional($order->client)->phone ?? '' }}</div>
                            </div>
                        </td>
                        <td data-label="Destination">
                            @if($order->delivery_destination)
                                <div class="hx-dest">{{ $order->delivery_destination }}</div>
                            @else
                                <span style="color:var(--cx-muted);font-size:12px;font-style:italic">—</span>
                            @endif
                        </td>
                        <td data-label="Chauffeur">
                            @if($order->driver)
                                @php
                                    $parts = explode(' ', $order->driver->name);
                                    $av = strtoupper(substr($parts[0],0,1)).strtoupper(substr($parts[1]??'X',0,1));
                                @endphp
                                <div class="hx-driver">
                                    <div class="hx-driver-av">
                                        @if($order->driver->photo)
                                            <img src="{{ asset('storage/'.$order->driver->photo) }}" alt="">
                                        @else
                                            {{ $av }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="hx-driver-name">{{ $order->driver->name }}</div>
                                        <div class="hx-driver-phone">{{ $order->driver->phone ?? '' }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="hx-no-driver">Non assigné</span>
                            @endif
                        </td>
                        <td data-label="Frais">
                            <span class="hx-fee">{{ $order->delivery_fee ? $fmt($order->delivery_fee) : '—' }}</span>
                        </td>
                        <td data-label="Montant">
                            <span class="hx-amount">{{ $fmt($order->total) }}</span>
                        </td>
                        <td data-label="Statut">
                            @if($order->status === 'livrée')
                                <span class="hx-badge hx-badge-livree">✅ Livrée</span>
                            @else
                                <span class="hx-badge hx-badge-annulee">❌ Annulée</span>
                            @endif
                        </td>
                        <td data-label="Clôture" style="color:var(--cx-muted);font-size:12.5px;white-space:nowrap">
                            {{ $order->updated_at->format('d/m/y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9">
                        <div class="hx-empty">
                            <div class="hx-empty-ico">📊</div>
                            <div class="hx-empty-title">Aucun historique trouvé</div>
                            <div class="hx-empty-sub">Modifiez les filtres ou attendez que des livraisons soient terminées.</div>
                        </div>
                    </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
            <div class="hx-pagination">{{ $orders->links() }}</div>
            @endif
        </div>

    </div>{{-- /hx-body --}}

</main>
</div>{{-- /cx-wrap --}}

@endsection

@push('scripts')
<script>
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

/* ── Périodes ── */
function setPeriod(v) {
    document.getElementById('hxPeriod').value = v;
    document.getElementById('hxForm').submit();
}

/* ── Recherche auto-submit ── */
let _st;
document.getElementById('hxSearch')?.addEventListener('input', function() {
    clearTimeout(_st);
    _st = setTimeout(() => document.getElementById('hxForm').submit(), 500);
});
</script>
@endpush
