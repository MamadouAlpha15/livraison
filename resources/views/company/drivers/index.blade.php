@extends('layouts.app')
@section('title', 'Chauffeurs · ' . $company->name)
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --cx-bg:     #07091a;
    --cx-card:   #0d1025;
    --cx-card2:  #111530;
    --cx-border: rgba(255,255,255,.07);
    --cx-border2:rgba(255,255,255,.13);
    --cx-brand:  #7c3aed;
    --cx-brand2: #6d28d9;
    --cx-text:   #e2e8f0;
    --cx-text2:  #94a3b8;
    --cx-muted:  #64748b;
    --cx-green:  #10b981;
    --cx-amber:  #f59e0b;
    --cx-red:    #ef4444;
    --r:  16px;
    --r-sm: 10px;
    --r-xs: 7px;
}
body{font-family:'Segoe UI',system-ui,sans-serif;background:var(--cx-bg);color:var(--cx-text);margin:0;-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}

/* ── BANNER ── */
.page-banner{
    background:linear-gradient(135deg,#1e1b4b 0%,#2d2470 35%,#3d1fa5 65%,#5b21b6 100%);
    padding:32px 32px 28px;position:relative;overflow:hidden;
}
.banner-grid{
    position:absolute;inset:0;pointer-events:none;
    background-image:
        linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
    background-size:44px 44px;
}
.banner-glow{
    position:absolute;top:-100px;right:-60px;
    width:400px;height:400px;border-radius:50%;
    background:radial-gradient(circle,rgba(124,58,237,.2) 0%,transparent 65%);
    pointer-events:none;
}
.banner-glow2{
    position:absolute;bottom:-80px;left:-40px;
    width:280px;height:280px;border-radius:50%;
    background:radial-gradient(circle,rgba(59,130,246,.1) 0%,transparent 65%);
    pointer-events:none;
}
.banner-inner{max-width:1280px;margin:0 auto;position:relative;z-index:1;}
.banner-top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:24px;}
.banner-title-group{display:flex;align-items:center;gap:14px;}
.banner-icon{
    width:52px;height:52px;border-radius:14px;
    background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);
    backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;
    font-size:24px;flex-shrink:0;
}
.banner-title{font-size:24px;font-weight:900;color:#fff;letter-spacing:-.5px;margin:0;}
.banner-sub{font-size:13px;color:rgba(255,255,255,.55);margin-top:3px;}
.btn-add-driver{
    display:inline-flex;align-items:center;gap:8px;
    background:#fff;color:var(--cx-brand);
    font-size:14px;font-weight:800;font-family:inherit;
    padding:12px 22px;border-radius:var(--r-sm);border:none;
    box-shadow:0 4px 20px rgba(0,0,0,.25);cursor:pointer;
    transition:all .18s;white-space:nowrap;
}
.btn-add-driver:hover{background:#f0e7ff;transform:translateY(-1px);box-shadow:0 6px 24px rgba(0,0,0,.3);}

/* Stats bar */
.stats-bar{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;}
.stat-pill{
    background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.11);
    border-radius:var(--r-sm);padding:16px 18px;backdrop-filter:blur(4px);
    position:relative;overflow:hidden;transition:background .15s;
}
.stat-pill:hover{background:rgba(255,255,255,.1);}
.stat-pill::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--s-accent,#a78bfa);}
.stat-pill-ico{font-size:18px;margin-bottom:6px;}
.stat-pill-val{font-size:24px;font-weight:900;color:#fff;line-height:1;letter-spacing:-.6px;}
.stat-pill-lbl{font-size:10.5px;font-weight:600;color:rgba(255,255,255,.48);text-transform:uppercase;letter-spacing:.5px;margin-top:4px;}

/* ── BODY ── */
.pw{max-width:1280px;margin:0 auto;padding:28px 32px 80px;}

/* Filter bar */
.filter-bar{
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
    background:var(--cx-card);border:1px solid var(--cx-border);
    border-radius:var(--r);padding:14px 16px;margin-bottom:22px;
}
.tab-btn{
    padding:7px 18px;border-radius:30px;font-size:12.5px;font-weight:700;
    border:1px solid transparent;cursor:pointer;transition:all .15s;
    background:transparent;color:var(--cx-text2);font-family:inherit;
}
.tab-btn:hover{background:var(--cx-card2);color:var(--cx-text);}
.tab-btn.active{background:var(--cx-brand);color:#fff;border-color:var(--cx-brand);}
.tab-btn.active-green{background:rgba(16,185,129,.15);color:#34d399;border-color:rgba(16,185,129,.3);}
.tab-btn.active-amber{background:rgba(245,158,11,.15);color:#fbbf24;border-color:rgba(245,158,11,.3);}
.tab-btn.active-gray{background:rgba(100,116,139,.15);color:var(--cx-text2);border-color:var(--cx-border2);}
.tab-sep{width:1px;height:22px;background:var(--cx-border);flex-shrink:0;}
.search-wrap{position:relative;flex:1;min-width:180px;}
.search-ico{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--cx-muted);pointer-events:none;}
.search-input{
    width:100%;padding:9px 12px 9px 35px;
    background:var(--cx-card2);border:1px solid var(--cx-border);
    border-radius:var(--r-xs);color:var(--cx-text);font-size:13px;font-family:inherit;outline:none;transition:border-color .15s;
}
.search-input::placeholder{color:var(--cx-muted);}
.search-input:focus{border-color:var(--cx-brand);box-shadow:0 0 0 3px rgba(124,58,237,.12);}

/* ── DRIVER GRID ── */
.drivers-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:18px;
}

/* Driver card */
.driver-card{
    background:var(--cx-card);border:1px solid var(--cx-border);
    border-radius:var(--r);overflow:hidden;
    transition:border-color .18s,box-shadow .18s,transform .18s;
    position:relative;
}
.driver-card:hover{
    border-color:rgba(124,58,237,.35);
    box-shadow:0 8px 32px rgba(124,58,237,.15),0 2px 8px rgba(0,0,0,.3);
    transform:translateY(-2px);
}
.driver-card-accent{height:3px;width:100%;}

/* Card header */
.dc-header{
    padding:22px 20px 0;
    display:flex;align-items:flex-start;justify-content:space-between;gap:12px;
}
.dc-avatar-wrap{position:relative;flex-shrink:0;}
.dc-avatar{
    width:68px;height:68px;border-radius:18px;overflow:hidden;
    border:2px solid rgba(255,255,255,.1);
    display:flex;align-items:center;justify-content:center;
    font-size:22px;font-weight:900;color:#fff;letter-spacing:-.5px;
}
.dc-avatar img{width:100%;height:100%;object-fit:cover;}
.dc-status-dot{
    position:absolute;bottom:-3px;right:-3px;
    width:18px;height:18px;border-radius:50%;border:3px solid var(--cx-card);
}
.dot-avail{background:#10b981;box-shadow:0 0 8px rgba(16,185,129,.6);}
.dot-busy {background:#f59e0b;box-shadow:0 0 8px rgba(245,158,11,.5);}
.dot-off  {background:#475569;}

.dc-actions{display:flex;gap:6px;flex-shrink:0;}
.dc-action-btn{
    width:32px;height:32px;border-radius:var(--r-xs);
    display:flex;align-items:center;justify-content:center;
    border:1px solid var(--cx-border);background:var(--cx-card2);
    color:var(--cx-text2);cursor:pointer;transition:all .14s;font-size:14px;
}
.dc-action-btn:hover{background:rgba(124,58,237,.15);border-color:rgba(124,58,237,.3);color:#c4b5fd;}
.dc-action-btn.del:hover{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#f87171;}

/* Card body */
.dc-body{padding:14px 20px 18px;}
.dc-name{font-size:17px;font-weight:800;color:#fff;letter-spacing:-.3px;margin-bottom:3px;}
.dc-phone{display:flex;align-items:center;gap:5px;font-size:12.5px;color:var(--cx-text2);margin-bottom:14px;}

/* Status badge */
.dc-status{
    display:inline-flex;align-items:center;gap:6px;
    font-size:11.5px;font-weight:700;padding:4px 12px;border-radius:20px;
    margin-bottom:16px;
}
.status-avail{background:rgba(16,185,129,.12);color:#34d399;border:1px solid rgba(16,185,129,.2);}
.status-busy {background:rgba(245,158,11,.12);color:#fbbf24;border:1px solid rgba(245,158,11,.2);}
.status-off  {background:rgba(100,116,139,.1);color:var(--cx-muted);border:1px solid var(--cx-border);}
.status-dot-sm{width:6px;height:6px;border-radius:50%;flex-shrink:0;}

/* Stats row */
.dc-stats{
    display:grid;grid-template-columns:repeat(3,1fr);gap:8px;
    background:var(--cx-card2);border:1px solid var(--cx-border);
    border-radius:var(--r-sm);padding:12px;margin-bottom:16px;
}
.dc-stat{text-align:center;}
.dc-stat-val{font-size:18px;font-weight:900;color:#fff;letter-spacing:-.3px;line-height:1;}
.dc-stat-lbl{font-size:10px;font-weight:600;color:var(--cx-muted);text-transform:uppercase;letter-spacing:.4px;margin-top:3px;}

/* Status info (read-only, géré par le livreur) */
.dc-status-info{
    display:flex;align-items:center;justify-content:space-between;
    background:var(--cx-card2);border:1px solid var(--cx-border);
    border-radius:var(--r-xs);padding:9px 12px;font-size:11.5px;
}
.dc-status-info-label{color:var(--cx-muted);font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;}
.dc-status-pill{
    display:inline-flex;align-items:center;gap:5px;
    font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;
}
.pill-available{background:rgba(16,185,129,.12);color:#34d399;border:1px solid rgba(16,185,129,.25);}
.pill-busy     {background:rgba(245,158,11,.12);color:#fbbf24;border:1px solid rgba(245,158,11,.25);}
.pill-offline  {background:rgba(100,116,139,.1);color:#94a3b8;border:1px solid rgba(100,116,139,.2);}
.dc-status-dot-sm{width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.dot-sm-green{background:#10b981;box-shadow:0 0 5px rgba(16,185,129,.5);}
.dot-sm-amber{background:#f59e0b;box-shadow:0 0 5px rgba(245,158,11,.4);}
.dot-sm-gray {background:#64748b;}

/* Empty state */
.empty-state{
    grid-column:1/-1;text-align:center;padding:72px 24px;
    display:flex;flex-direction:column;align-items:center;gap:14px;
}
.empty-ico-wrap{
    width:80px;height:80px;border-radius:22px;
    background:rgba(124,58,237,.1);border:1px solid rgba(124,58,237,.2);
    display:flex;align-items:center;justify-content:center;font-size:36px;
}
.empty-title{font-size:18px;font-weight:800;color:var(--cx-text);}
.empty-sub{font-size:13.5px;color:var(--cx-muted);max-width:340px;line-height:1.6;}

/* ── MODAL ── */
.modal-overlay{
    position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:900;
    display:flex;align-items:center;justify-content:center;padding:20px;
    backdrop-filter:blur(5px);opacity:0;pointer-events:none;transition:opacity .22s;
}
.modal-overlay.open{opacity:1;pointer-events:all;}
.modal{
    background:var(--cx-card);border:1px solid var(--cx-border2);
    border-radius:var(--r);width:100%;max-width:480px;max-height:92vh;
    overflow-y:auto;box-shadow:0 32px 80px rgba(0,0,0,.6);
    transform:translateY(18px) scale(.98);transition:transform .22s;
}
.modal-overlay.open .modal{transform:translateY(0) scale(1);}
.modal-hd{
    padding:20px 24px;border-bottom:1px solid var(--cx-border);
    display:flex;align-items:center;justify-content:space-between;
    position:sticky;top:0;background:var(--cx-card);z-index:1;
}
.modal-title{font-size:16px;font-weight:900;color:#fff;display:flex;align-items:center;gap:9px;}
.modal-title-ico{
    width:32px;height:32px;border-radius:8px;
    background:rgba(124,58,237,.2);border:1px solid rgba(124,58,237,.3);
    display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;
}
.modal-close{
    width:32px;height:32px;border-radius:var(--r-xs);
    background:var(--cx-card2);border:1px solid var(--cx-border);
    display:flex;align-items:center;justify-content:center;
    color:var(--cx-text2);cursor:pointer;font-size:17px;transition:all .14s;
}
.modal-close:hover{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.3);color:#f87171;}
.modal-body{padding:22px 24px;}

/* Avatar upload */
.avatar-upload{
    display:flex;flex-direction:column;align-items:center;gap:12px;margin-bottom:22px;
}
.avatar-preview{
    width:90px;height:90px;border-radius:22px;
    background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2));
    border:2px solid rgba(124,58,237,.3);
    display:flex;align-items:center;justify-content:center;
    font-size:36px;font-weight:900;color:#fff;overflow:hidden;cursor:pointer;
    position:relative;transition:all .18s;
}
.avatar-preview:hover{border-color:var(--cx-brand);box-shadow:0 0 0 4px rgba(124,58,237,.15);}
.avatar-preview img{width:100%;height:100%;object-fit:cover;}
.avatar-preview-overlay{
    position:absolute;inset:0;background:rgba(0,0,0,.5);
    display:flex;align-items:center;justify-content:center;
    font-size:22px;opacity:0;transition:opacity .15s;border-radius:20px;
}
.avatar-preview:hover .avatar-preview-overlay{opacity:1;}
.avatar-hint{font-size:11.5px;color:var(--cx-muted);text-align:center;}
.avatar-file-input{display:none;}

/* Form */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-group{display:flex;flex-direction:column;gap:6px;}
.form-group.full{grid-column:1/-1;}
.form-label{font-size:11.5px;font-weight:700;color:var(--cx-text2);text-transform:uppercase;letter-spacing:.5px;}
.form-input{
    padding:11px 14px;background:var(--cx-card2);border:1.5px solid var(--cx-border);
    border-radius:var(--r-sm);color:var(--cx-text);font-size:13.5px;font-family:inherit;outline:none;
    transition:border-color .15s,box-shadow .15s;
}
.form-input:focus{border-color:var(--cx-brand);box-shadow:0 0 0 3px rgba(124,58,237,.15);}
.form-input::placeholder{color:var(--cx-muted);}
select.form-input{cursor:pointer;}
select.form-input option{background:#1e1b4b;color:#e2e8f0;}

/* Submit btn */
.btn-submit{
    width:100%;padding:14px;margin-top:18px;
    background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2));
    color:#fff;border:none;border-radius:var(--r-sm);
    font-size:14.5px;font-weight:800;font-family:inherit;cursor:pointer;
    box-shadow:0 4px 16px rgba(124,58,237,.4);transition:all .18s;
    display:flex;align-items:center;justify-content:center;gap:9px;
}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 6px 22px rgba(124,58,237,.5);}
.btn-submit:disabled{opacity:.6;cursor:not-allowed;transform:none;}

/* Delete modal */
.del-modal .modal-body{text-align:center;padding:28px 24px;}
.del-icon{font-size:48px;margin-bottom:12px;}
.del-title{font-size:17px;font-weight:800;color:#fff;margin-bottom:8px;}
.del-sub{font-size:13px;color:var(--cx-text2);line-height:1.65;margin-bottom:24px;}
.del-actions{display:flex;gap:10px;}
.btn-cancel-del{
    flex:1;padding:12px;border-radius:var(--r-sm);
    background:var(--cx-card2);border:1px solid var(--cx-border2);
    color:var(--cx-text2);font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .15s;
}
.btn-cancel-del:hover{background:var(--cx-card);color:var(--cx-text);}
.btn-confirm-del{
    flex:1;padding:12px;border-radius:var(--r-sm);
    background:linear-gradient(135deg,#dc2626,#ef4444);
    border:none;color:#fff;font-size:13.5px;font-weight:800;font-family:inherit;cursor:pointer;
    box-shadow:0 4px 12px rgba(239,68,68,.3);transition:all .15s;
}
.btn-confirm-del:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(239,68,68,.4);}

/* Toast */
.toast{
    position:fixed;bottom:28px;right:28px;z-index:9999;
    padding:13px 22px;border-radius:var(--r-sm);font-size:13.5px;font-weight:700;color:#fff;
    box-shadow:0 10px 30px rgba(0,0,0,.4);transform:translateY(24px);opacity:0;
    transition:all .28s cubic-bezier(.23,1,.32,1);pointer-events:none;max-width:360px;
}
.toast.show{transform:translateY(0);opacity:1;}
.toast-success{background:linear-gradient(135deg,#059669,#10b981);}
.toast-error  {background:linear-gradient(135deg,#dc2626,#ef4444);}

/* Flash */
.flash{
    display:flex;align-items:center;gap:10px;padding:12px 18px;
    border-radius:var(--r-sm);border:1px solid;font-size:13.5px;font-weight:600;
    margin-bottom:20px;
}
.flash-success{background:rgba(16,185,129,.08);border-color:rgba(16,185,129,.2);color:#34d399;}
.flash-error  {background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.2);color:#f87171;}

@media(max-width:1024px){.stats-bar{grid-template-columns:repeat(3,1fr);}}
@media(max-width:768px){
    .page-banner{padding:22px 18px 20px;}
    .pw{padding:18px 16px 60px;}
    .stats-bar{grid-template-columns:1fr 1fr;}
    .drivers-grid{grid-template-columns:1fr;}
    .form-grid{grid-template-columns:1fr;}
}
</style>
@endpush

@section('content')

{{-- ════════════════ BANNER ════════════════ --}}
<div class="page-banner">
    <div class="banner-grid"></div>
    <div class="banner-glow"></div>
    <div class="banner-glow2"></div>
    <div class="banner-inner">
        <div class="banner-top">
            <div class="banner-title-group">
                <div class="banner-icon">🚴</div>
                <div>
                    <h1 class="banner-title">Mes chauffeurs</h1>
                    <div class="banner-sub">{{ $company->name }} · Gestion de l'équipe de livraison</div>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <a href="{{ route('company.dashboard') }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:10px 18px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);border-radius:var(--r-sm);color:#fff;font-size:13px;font-weight:700;transition:background .15s;"
                   onmouseover="this.style.background='rgba(255,255,255,.18)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                    ← Dashboard
                </a>
                <a href="{{ route('company.drivers.create') }}" class="btn-add-driver">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Ajouter un chauffeur
                </a>
            </div>
        </div>

        <div class="stats-bar">
            <div class="stat-pill" style="--s-accent:#a78bfa;">
                <div class="stat-pill-ico">👥</div>
                <div class="stat-pill-val">{{ $stats['total'] }}</div>
                <div class="stat-pill-lbl">Total</div>
            </div>
            <div class="stat-pill" style="--s-accent:#34d399;">
                <div class="stat-pill-ico">🟢</div>
                <div class="stat-pill-val">{{ $stats['available'] }}</div>
                <div class="stat-pill-lbl">Disponibles</div>
            </div>
            <div class="stat-pill" style="--s-accent:#fbbf24;">
                <div class="stat-pill-ico">🔄</div>
                <div class="stat-pill-val">{{ $stats['busy'] }}</div>
                <div class="stat-pill-lbl">En mission</div>
            </div>
            <div class="stat-pill" style="--s-accent:#94a3b8;">
                <div class="stat-pill-ico">⚫</div>
                <div class="stat-pill-val">{{ $stats['offline'] }}</div>
                <div class="stat-pill-lbl">Hors ligne</div>
            </div>
            <div class="stat-pill" style="--s-accent:#60a5fa;">
                <div class="stat-pill-ico">✅</div>
                <div class="stat-pill-val">{{ $stats['livrees'] }}</div>
                <div class="stat-pill-lbl">Livraisons</div>
            </div>
        </div>
    </div>
</div>

{{-- ════ BODY ════ --}}
<div class="pw">

    {{-- Flash --}}
    @if(session('success'))
    <div class="flash flash-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="flash flash-error">❌ {{ session('error') }}</div>
    @endif

    {{-- FILTER BAR --}}
    <div class="filter-bar">
        <button class="tab-btn active" id="tab-all"        onclick="filterStatus('all')">Tous ({{ $stats['total'] }})</button>
        <button class="tab-btn"        id="tab-available"  onclick="filterStatus('available')">🟢 Disponibles ({{ $stats['available'] }})</button>
        <button class="tab-btn"        id="tab-busy"       onclick="filterStatus('busy')">🟡 En mission ({{ $stats['busy'] }})</button>
        <button class="tab-btn"        id="tab-offline"    onclick="filterStatus('offline')">⚫ Hors ligne ({{ $stats['offline'] }})</button>
        <div class="tab-sep"></div>
        <div class="search-wrap">
            <svg class="search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input class="search-input" type="text" id="searchInput" placeholder="Rechercher un chauffeur…" oninput="filterCards()">
        </div>
    </div>

    {{-- GRID --}}
    <div class="drivers-grid" id="driversGrid">

        @forelse($drivers as $driver)
        @php
            $statusCfg = match($driver->status) {
                'available' => ['cls'=>'status-avail','dot'=>'dot-avail','lbl'=>'Disponible','accent'=>'linear-gradient(90deg,#10b981,#059669)','tdot'=>'t-avail'],
                'busy'      => ['cls'=>'status-busy', 'dot'=>'dot-busy', 'lbl'=>'En mission', 'accent'=>'linear-gradient(90deg,#f59e0b,#d97706)','tdot'=>'t-busy'],
                default     => ['cls'=>'status-off',  'dot'=>'dot-off',  'lbl'=>'Hors ligne', 'accent'=>'linear-gradient(90deg,#475569,#334155)','tdot'=>'t-off'],
            };
            $ini = strtoupper(substr($driver->name,0,1)) . strtoupper(substr(explode(' ',$driver->name)[1] ?? 'X',0,1));
            $avatarBg = 'linear-gradient(135deg,#7c3aed,#4f46e5)';
        @endphp

        <div class="driver-card" data-status="{{ $driver->status }}" data-name="{{ strtolower($driver->name) }}">
            <div class="driver-card-accent" style="background:{{ $statusCfg['accent'] }};"></div>

            <div class="dc-header">
                <div class="dc-avatar-wrap">
                    <div class="dc-avatar" style="background:{{ $avatarBg }};">
                        @if($driver->photo)
                            <img src="{{ asset('storage/'.$driver->photo) }}" alt="{{ $driver->name }}">
                        @else
                            {{ $ini }}
                        @endif
                    </div>
                    <div class="dc-status-dot {{ $statusCfg['dot'] }}"></div>
                </div>

                <div class="dc-actions">
                    <a href="{{ route('company.drivers.edit', $driver) }}" class="dc-action-btn" title="Modifier">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <button class="dc-action-btn del" onclick="openDeleteModal({{ $driver->id }}, '{{ e($driver->name) }}')" title="Supprimer">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </div>
            </div>

            <div class="dc-body">
                <div class="dc-name">{{ $driver->name }}</div>
                <div class="dc-phone">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    {{ $driver->phone ?? 'Aucun numéro' }}
                </div>

                <div class="dc-status {{ $statusCfg['cls'] }}">
                    <span class="status-dot-sm" style="background:{{ $driver->status==='available'?'#10b981':($driver->status==='busy'?'#f59e0b':'#475569') }};"></span>
                    {{ $statusCfg['lbl'] }}
                </div>

                <div class="dc-stats">
                    <div class="dc-stat">
                        <div class="dc-stat-val">{{ $driver->orders_count ?? 0 }}</div>
                        <div class="dc-stat-lbl">Total</div>
                    </div>
                    <div class="dc-stat">
                        <div class="dc-stat-val" style="color:#34d399;">{{ $driver->livrees_count ?? 0 }}</div>
                        <div class="dc-stat-lbl">Livrées</div>
                    </div>
                    <div class="dc-stat">
                        <div class="dc-stat-val" style="color:#fbbf24;">{{ $driver->en_cours_count ?? 0 }}</div>
                        <div class="dc-stat-lbl">En cours</div>
                    </div>
                </div>

                {{-- Statut (lecture seule — géré par le livreur depuis son dashboard) --}}
                @php
                    $pillCfg = match($driver->status) {
                        'available' => ['pill-available','dot-sm-green','🟢','Disponible'],
                        'busy'      => ['pill-busy',     'dot-sm-amber','🟠','En mission'],
                        default     => ['pill-offline',  'dot-sm-gray', '⚫','Hors ligne'],
                    };
                @endphp
                <div class="dc-status-info">
                    <span class="dc-status-info-label">Statut</span>
                    <span class="dc-status-pill {{ $pillCfg[0] }}">
                        <span class="dc-status-dot-sm {{ $pillCfg[1] }}"></span>
                        {{ $pillCfg[3] }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-ico-wrap">🚴</div>
            <div class="empty-title">Aucun chauffeur enregistré</div>
            <div class="empty-sub">Commencez par ajouter votre premier chauffeur pour pouvoir assigner des livraisons.</div>
            <a href="{{ route('company.drivers.create') }}" class="btn-add-driver" style="font-size:13.5px;padding:11px 20px;">
                + Ajouter le premier chauffeur
            </a>
        </div>
        @endforelse
    </div>
</div>

{{-- ═══════ MODAL AJOUTER ═══════ --}}
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-hd">
            <div class="modal-title">
                <div class="modal-title-ico">🚴</div>
                Ajouter un chauffeur
            </div>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('company.drivers.store') }}" enctype="multipart/form-data" id="addForm">
                @csrf

                {{-- Avatar upload --}}
                <div class="avatar-upload">
                    <div class="avatar-preview" id="addAvatarPreview" onclick="document.getElementById('addPhotoInput').click()">
                        🚴
                        <div class="avatar-preview-overlay">📷</div>
                    </div>
                    <div class="avatar-hint">Cliquez pour ajouter une photo (optionnel)</div>
                    <input type="file" name="photo" id="addPhotoInput" class="avatar-file-input" accept="image/*"
                           onchange="previewAvatar(this,'addAvatarPreview')">
                </div>

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Nom complet *</label>
                        <input type="text" name="name" class="form-input" placeholder="Ex : Alpha Diallo" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-input" placeholder="Ex : +224 622…">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Statut initial</label>
                        <select name="status" class="form-input">
                            <option value="available">🟢 Disponible</option>
                            <option value="offline">⚫ Hors ligne</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Ajouter le chauffeur
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ═══════ MODAL MODIFIER ═══════ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-hd">
            <div class="modal-title">
                <div class="modal-title-ico">✏️</div>
                Modifier le chauffeur
            </div>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <div class="modal-body">
            <form method="POST" id="editForm" action="" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="avatar-upload">
                    <div class="avatar-preview" id="editAvatarPreview" onclick="document.getElementById('editPhotoInput').click()">
                        ✏️
                        <div class="avatar-preview-overlay">📷</div>
                    </div>
                    <div class="avatar-hint">Cliquez pour changer la photo</div>
                    <input type="file" name="photo" id="editPhotoInput" class="avatar-file-input" accept="image/*"
                           onchange="previewAvatar(this,'editAvatarPreview')">
                </div>

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Nom complet *</label>
                        <input type="text" name="name" id="editName" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" id="editPhone" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="status" id="editStatus" class="form-input">
                            <option value="available">🟢 Disponible</option>
                            <option value="busy">🟡 En mission</option>
                            <option value="offline">⚫ Hors ligne</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Enregistrer les modifications
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ═══════ MODAL SUPPRESSION ═══════ --}}
<div class="modal-overlay del-modal" id="deleteModal">
    <div class="modal" style="max-width:400px;">
        <div class="modal-body">
            <div class="del-icon">🗑️</div>
            <div class="del-title">Supprimer ce chauffeur ?</div>
            <div class="del-sub">
                Vous allez supprimer <strong id="delDriverName" style="color:#fff;"></strong>.<br>
                Cette action est <strong style="color:#f87171;">irréversible</strong>.
            </div>
            <div class="del-actions">
                <button class="btn-cancel-del" onclick="closeModal('deleteModal')">← Annuler</button>
                <form id="deleteForm" method="POST" action="" style="flex:1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-confirm-del" style="width:100%;">🗑️ Confirmer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script>
/* ── Modals ── */
function openModal(id)  { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) closeModal(el.id); })
);
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['addModal','editModal','deleteModal'].forEach(closeModal);
});

function openAddModal() { openModal('addModal'); }

function openEditModal(id, name, phone, status) {
    document.getElementById('editForm').action = `/company/drivers/${id}`;
    document.getElementById('editName').value   = name;
    document.getElementById('editPhone').value  = phone;
    document.getElementById('editStatus').value = status;
    // reset avatar
    const prev = document.getElementById('editAvatarPreview');
    prev.innerHTML = `✏️<div class="avatar-preview-overlay">📷</div>`;
    openModal('editModal');
}

function openDeleteModal(id, name) {
    document.getElementById('deleteForm').action = `/company/drivers/${id}`;
    document.getElementById('delDriverName').textContent = name;
    openModal('deleteModal');
}

/* ── Avatar preview ── */
function previewAvatar(input, previewId) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById(previewId);
        prev.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:20px;"><div class="avatar-preview-overlay">📷</div>`;
    };
    reader.readAsDataURL(file);
}

/* ── Filtres ── */
let currentFilter = 'all';

function filterStatus(status) {
    currentFilter = status;
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('active','active-green','active-amber','active-gray');
    });
    const btn = document.getElementById('tab-' + (status === 'all' ? 'all' : status));
    if (status === 'available') btn.classList.add('active-green');
    else if (status === 'busy') btn.classList.add('active-amber');
    else if (status === 'offline') btn.classList.add('active-gray');
    else btn.classList.add('active');
    filterCards();
}

function filterCards() {
    const search  = document.getElementById('searchInput').value.toLowerCase();
    const cards   = document.querySelectorAll('.driver-card');
    let visible   = 0;

    cards.forEach(card => {
        const matchStatus = currentFilter === 'all' || card.dataset.status === currentFilter;
        const matchSearch = !search || card.dataset.name.includes(search);
        const show = matchStatus && matchSearch;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    // empty state dynamique
    let empty = document.getElementById('dynamicEmpty');
    if (visible === 0 && cards.length > 0) {
        if (!empty) {
            empty = document.createElement('div');
            empty.id = 'dynamicEmpty';
            empty.className = 'empty-state';
            empty.innerHTML = `<div class="empty-ico-wrap">🔍</div><div class="empty-title">Aucun résultat</div><div class="empty-sub">Essayez un autre filtre ou une autre recherche.</div>`;
            document.getElementById('driversGrid').appendChild(empty);
        }
        empty.style.display = '';
    } else if (empty) {
        empty.style.display = 'none';
    }
}

/* ── Toast flash ── */
@if(session('success'))
    setTimeout(() => toast('✅ {{ session('success') }}','success'), 200);
@endif
@if(session('error'))
    setTimeout(() => toast('❌ {{ session('error') }}','error'), 200);
@endif

function toast(msg, type = 'success') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = `toast toast-${type} show`;
    setTimeout(() => el.classList.remove('show'), 4000);
}
</script>
@endpush
