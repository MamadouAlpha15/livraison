{{--
    resources/views/employe/orders/index.blade.php
--}}

@extends('layouts.app')
@section('title', 'Commandes · ' . ($shop->name ?? 'Boutique'))
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --brand:#10b981;--brand-dk:#059669;--brand-lt:#d1fae5;--brand-mlt:#ecfdf5;
    --sb-bg:#0d1f18;--sb-border:rgba(255,255,255,.06);--sb-act:rgba(16,185,129,.14);
    --sb-hov:rgba(255,255,255,.04);--sb-txt:rgba(255,255,255,.55);--sb-txt-act:#fff;
    --bg:#f6f8f7;--surface:#ffffff;--border:#e8eceb;--border-dk:#d4d9d7;
    --text:#0f1c18;--text-2:#4b5c56;--muted:#8a9e98;
    --font:'Plus Jakarta Sans',sans-serif;--mono:'JetBrains Mono',monospace;
    --r:14px;--r-sm:9px;--shadow-sm:0 1px 3px rgba(0,0,0,.06);--shadow:0 4px 16px rgba(0,0,0,.07);
    --sb-w:230px;--top-h:56px;
}
html{font-family:var(--font);}
body{background:var(--bg);margin:0;color:var(--text);-webkit-font-smoothing:antialiased;}
.dash-wrap{display:flex;min-height:100vh;}
.dash-wrap .main{margin-left:var(--sb-w);flex:1;min-width:0;}
.sidebar{background:var(--sb-bg);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);overflow-y:scroll;scrollbar-width:thin;scrollbar-color:rgba(16,185,129,.4) rgba(255,255,255,.05);z-index:40;border-right:1px solid rgba(0,0,0,.2);}
.sidebar::-webkit-scrollbar{width:4px;}
.sidebar::-webkit-scrollbar-track{background:rgba(255,255,255,.04);}
.sidebar::-webkit-scrollbar-thumb{background:rgba(16,185,129,.4);border-radius:4px;}
.sb-brand{padding:18px 16px 14px;border-bottom:1px solid var(--sb-border);flex-shrink:0;position:relative;}
.sb-close{display:none;position:absolute;top:14px;right:12px;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.6);font-size:18px;cursor:pointer;align-items:center;justify-content:center;transition:background .15s,color .15s;}
@media(max-width:900px){.sb-close{display:flex;}}
.sb-logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:#fff;}
.sb-logo-icon{width:32px;height:32px;background:linear-gradient(135deg,var(--brand),#059669);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;}
.sb-shop-name{font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:148px;}
.sb-status{display:flex;align-items:center;gap:6px;margin-top:9px;font-size:10.5px;color:var(--sb-txt);font-weight:500;}
.pulse{width:6px;height:6px;border-radius:50%;background:var(--brand);flex-shrink:0;animation:blink 2.2s ease-in-out infinite;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.35}}
.sb-nav{padding:10px 10px 32px;flex:1;display:flex;flex-direction:column;gap:1px;overflow:visible;}
.sb-section{font-size:9.5px;text-transform:uppercase;letter-spacing:1.2px;color:rgba(255,255,255,.2);padding:12px 8px 4px;font-weight:600;}
.sb-item{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:var(--r-sm);font-size:13px;font-weight:500;color:var(--sb-txt);text-decoration:none;transition:background .15s,color .15s;position:relative;}
.sb-item:hover{background:var(--sb-hov);color:rgba(255,255,255,.8);}
.sb-item.active{background:var(--sb-act);color:var(--sb-txt-act);}
.sb-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:18px;background:var(--brand);border-radius:0 3px 3px 0;}
.sb-item .ico{font-size:14px;width:20px;text-align:center;flex-shrink:0;}
.sb-badge{margin-left:auto;background:var(--brand);color:#fff;font-size:10px;font-weight:700;border-radius:20px;padding:1px 7px;font-family:var(--mono);min-width:20px;text-align:center;}
.sb-group{display:flex;flex-direction:column;}
.sb-group-toggle{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:var(--r-sm);font-size:13px;font-weight:500;color:var(--sb-txt);cursor:pointer;transition:background .15s,color .15s;user-select:none;border:none;background:none;width:100%;text-align:left;font-family:var(--font);}
.sb-group-toggle:hover{background:var(--sb-hov);color:rgba(255,255,255,.8);}
.sb-group-toggle.open{color:rgba(255,255,255,.9);background:rgba(255,255,255,.03);}
.sb-group-toggle .ico{font-size:14px;width:20px;text-align:center;flex-shrink:0;}
.sb-group-toggle .sb-arrow{margin-left:auto;font-size:10px;color:rgba(255,255,255,.25);transition:transform .2s;flex-shrink:0;}
.sb-group-toggle.open .sb-arrow{transform:rotate(90deg);color:rgba(255,255,255,.5);}
.sb-sub{display:none;flex-direction:column;gap:1px;margin-left:12px;padding-left:14px;border-left:1px solid rgba(255,255,255,.07);margin-top:2px;margin-bottom:4px;}
.sb-sub.open{display:flex;}
.sb-sub .sb-item{font-size:12.5px;padding:6px 10px;color:rgba(255,255,255,.45);}
.sb-sub .sb-item.active{color:var(--sb-txt-act);background:var(--sb-act);}
.sb-footer{padding:12px 10px;border-top:1px solid var(--sb-border);flex-shrink:0;display:flex;flex-direction:column;gap:6px;position:sticky;bottom:0;background:var(--sb-bg);z-index:1;}
.sb-scroll-hint{position:sticky;bottom:72px;width:100%;height:40px;background:linear-gradient(to bottom,transparent,rgba(13,31,24,.9));pointer-events:none;z-index:2;display:flex;align-items:flex-end;justify-content:center;padding-bottom:6px;transition:opacity .3s;margin-top:-40px;align-self:flex-end;}
.sb-scroll-hint.hidden{opacity:0;pointer-events:none;}
.sb-scroll-hint-arrow{display:flex;flex-direction:column;align-items:center;gap:2px;animation:bounceDown 1.5s ease-in-out infinite;}
.sb-scroll-hint-dot{width:4px;height:4px;border-radius:50%;background:rgba(16,185,129,.6);}
@keyframes bounceDown{0%,100%{transform:translateY(0)}50%{transform:translateY(4px)}}
.sb-user{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);text-decoration:none;transition:background .15s;}
.sb-user:hover{background:var(--sb-hov);}
.sb-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--brand),#16a34a);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;}
.sb-uname{font-size:12px;font-weight:600;color:rgba(255,255,255,.85);}
.sb-urole{font-size:10px;color:var(--sb-txt);margin-top:1px;}
.sb-logout{display:flex;align-items:center;gap:8px;width:100%;padding:8px 10px;border-radius:var(--r-sm);background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.15);color:rgba(252,165,165,.85);font-size:12px;font-weight:600;font-family:var(--font);cursor:pointer;text-decoration:none;transition:background .15s,color .15s,border-color .15s;text-align:left;}
.sb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:39;}
.main{display:flex;flex-direction:column;min-width:0;}
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 22px;height:var(--top-h);display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:30;box-shadow:var(--shadow-sm);}
.btn-hamburger{display:none;background:none;border:none;cursor:pointer;padding:6px;color:var(--text);font-size:20px;}
.tb-info{flex:1;min-width:0;}
.tb-title{font-size:14px;font-weight:700;color:var(--text);}
.tb-sub{font-size:11px;color:var(--muted);margin-top:1px;}
.content{padding:20px 22px;flex:1;}
.flash{padding:10px 14px;border-radius:var(--r-sm);border:1px solid;font-size:13px;font-weight:500;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.flash-success{background:#ecfdf5;border-color:#6ee7b7;color:#065f46;}
.flash-warning{background:#fffbeb;border-color:#fcd34d;color:#92400e;}
.flash-danger{background:#fef2f2;border-color:#fca5a5;color:#991b1b;}
.devise-badge{display:inline-flex;align-items:center;gap:5px;background:var(--brand-mlt);color:var(--brand-dk);border:1px solid var(--brand-lt);font-size:11px;font-weight:700;font-family:var(--mono);padding:4px 10px;border-radius:20px;}
.stats-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;}
.stat-chip{background:var(--surface);border:1px solid var(--border);border-radius:var(--r-sm);padding:10px 16px;display:flex;align-items:center;gap:8px;box-shadow:var(--shadow-sm);font-size:12.5px;font-weight:600;color:var(--text-2);flex:1;min-width:0;}
.stat-chip .val{font-family:var(--mono);font-size:16px;font-weight:700;color:var(--text);}
.stat-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.orders-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--shadow-sm);margin-bottom:20px;}
.orders-card-hd{padding:12px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--bg);}
.orders-card-title{font-size:13px;font-weight:700;color:var(--text);}
.tbl{width:100%;border-collapse:collapse;font-size:12.5px;}
.tbl thead th{padding:10px 13px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;background:var(--bg);border-bottom:1px solid var(--border);white-space:nowrap;}
.tbl tbody td{padding:11px 13px;border-bottom:1px solid #f3f6f4;vertical-align:middle;}
.tbl tbody tr:last-child td{border-bottom:none;}
.tbl tbody tr:hover td{background:#fafcfb;}
.c-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--brand),#2563eb);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;}
.c-name{font-size:13px;font-weight:600;color:var(--text);}
.c-sub{font-size:11px;color:var(--muted);}
.prod-img{width:36px;height:36px;border-radius:var(--r-sm);object-fit:cover;border:1px solid var(--border);flex-shrink:0;}
.prod-ph{width:36px;height:36px;border-radius:var(--r-sm);background:#f3f6f4;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;}
.amount{font-family:var(--mono);font-weight:700;font-size:13px;color:var(--text);white-space:nowrap;}
.amount small{font-size:10px;color:var(--muted);font-weight:500;}
.pill{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap;}
.p-success{background:#d1fae5;color:#065f46;}
.p-warning{background:#fef3c7;color:#92400e;}
.p-info{background:#dbeafe;color:#1e40af;}
.p-danger{background:#fee2e2;color:#991b1b;}
.p-purple{background:#f5f3ff;color:#5b21b6;}
.p-muted{background:#f3f6f4;color:#6b7280;}
.assign-form{display:flex;align-items:center;gap:6px;flex-wrap:nowrap;}
.assign-select{padding:6px 10px;border-radius:var(--r-sm);border:1.5px solid var(--border-dk);font-size:12px;font-family:var(--font);color:var(--text);background:var(--surface);outline:none;min-width:110px;max-width:150px;transition:border-color .15s;}
.assign-select:focus{border-color:var(--brand);}
.btn{display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:var(--r-sm);font-size:12px;font-weight:600;font-family:var(--font);border:1px solid var(--border-dk);background:var(--surface);color:var(--text-2);cursor:pointer;text-decoration:none;transition:all .15s;white-space:nowrap;}
.btn:hover{background:var(--bg);border-color:var(--brand);color:var(--brand);}
.btn-sm{padding:5px 9px;font-size:11.5px;}
.btn-primary{background:var(--brand);color:#fff;border-color:var(--brand-dk);}
.btn-primary:hover{background:var(--brand-dk);color:#fff;}
.btn-assigned{background:var(--brand-mlt);border-color:var(--brand-lt);color:var(--brand-dk);cursor:default;pointer-events:none;}
.btn-info{background:#eff6ff;border-color:#93c5fd;color:#1e40af;}
.btn-info:hover{background:#dbeafe;border-color:#60a5fa;color:#1d4ed8;}
.btn-cancel{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:5px 9px;font-size:11.5px;font-weight:600;border-radius:var(--r-sm);cursor:pointer;font-family:var(--font);display:inline-flex;align-items:center;gap:4px;transition:background .15s,border-color .15s,color .15s;white-space:nowrap;}
.btn-cancel:hover{background:#fee2e2;border-color:#f87171;color:#7f1d1d;}
.btn-cancel.disabled,.btn-cancel:disabled{background:#f3f6f4;border-color:var(--border);color:var(--muted);cursor:not-allowed;pointer-events:none;}
.btn-restore{background:#eff6ff;border:1px solid #93c5fd;color:#1e40af;padding:5px 9px;font-size:11.5px;font-weight:600;border-radius:var(--r-sm);cursor:pointer;font-family:var(--font);display:inline-flex;align-items:center;gap:4px;transition:background .15s,border-color .15s,color .15s;white-space:nowrap;}
.btn-restore:hover{background:#dbeafe;border-color:#60a5fa;color:#1d4ed8;}
.lv-chip{display:inline-flex;align-items:center;gap:6px;background:var(--brand-mlt);border:1px solid var(--brand-lt);border-radius:20px;padding:4px 10px 4px 4px;font-size:11.5px;font-weight:600;color:var(--brand-dk);}
.lv-chip-av{width:22px;height:22px;border-radius:50%;background:var(--brand);color:#fff;display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:700;}
.empty-state{padding:56px 20px;text-align:center;}
.empty-state .ico{font-size:40px;display:block;margin-bottom:12px;opacity:.35;}
.empty-state p{font-size:14px;color:var(--muted);}
.pagination-wrap{display:flex;justify-content:center;padding:16px 0 4px;}
.pagination-wrap .pagination{gap:4px;margin:0;}
.pagination-wrap .page-link{border-radius:var(--r-sm)!important;border:1px solid var(--border);color:var(--text-2);font-size:13px;font-weight:600;padding:6px 13px;font-family:var(--font);transition:all .15s;background:var(--surface);}
.pagination-wrap .page-link:hover{background:var(--brand-mlt);border-color:var(--brand-lt);color:var(--brand-dk);}
.pagination-wrap .page-item.active .page-link{background:var(--brand);border-color:var(--brand-dk);color:#fff;}
.pagination-wrap .page-item.disabled .page-link{opacity:.4;cursor:not-allowed;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center;padding:20px;}
.modal-overlay.open{display:flex;}
.modal-box{background:var(--surface);border-radius:var(--r);padding:28px 26px;max-width:400px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:modalIn .2s ease;}
@keyframes modalIn{from{opacity:0;transform:scale(.95) translateY(-8px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-icon{font-size:38px;text-align:center;margin-bottom:12px;}
.modal-title{font-size:16px;font-weight:700;color:var(--text);text-align:center;margin-bottom:8px;}
.modal-sub{font-size:13px;color:var(--muted);text-align:center;line-height:1.6;margin-bottom:22px;}
.modal-sub strong{color:var(--text);font-weight:700;}
.modal-actions{display:flex;gap:10px;}
.modal-actions .btn{flex:1;justify-content:center;}
.btn-cancel-confirm{background:#dc2626;border-color:#b91c1c;color:#fff;font-weight:700;}
.btn-cancel-confirm:hover{background:#b91c1c;color:#fff;}
.btn-restore-confirm{background:#2563eb;border-color:#1d4ed8;color:#fff;font-weight:700;}
.btn-restore-confirm:hover{background:#1d4ed8;color:#fff;}
@media(max-width:900px){
    :root{--sb-w:230px;}
    .dash-wrap .main{margin-left:0;}
    .sidebar{transform:translateX(-100%);transition:transform .25s cubic-bezier(.23,1,.32,1);}
    .sidebar.open{transform:translateX(0);}
    .sb-overlay.open{display:block;}
    .btn-hamburger{display:flex;}
    .desktop-table{display:none !important;}
    .mobile-list{display:flex !important;}
    .content{padding:14px;}
}
@media(max-width:520px){
    .content{padding:10px;}
    .stats-row{gap:5px;}
    .stat-chip{min-width:0;padding:7px 9px;flex:1;font-size:10px;}
}
.mobile-list{display:none;flex-direction:column;gap:12px;}
.m-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--shadow-sm);}
.m-card-hd{padding:11px 14px;background:var(--bg);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:8px;}
.m-card-body{padding:12px 14px;display:flex;flex-direction:column;gap:9px;}
.m-row{display:flex;align-items:center;justify-content:space-between;gap:8px;}
.m-lbl{font-size:10.5px;color:var(--muted);font-weight:600;text-transform:uppercase;}
.m-card-foot{padding:10px 14px;border-top:1px solid var(--border);display:flex;gap:7px;flex-wrap:wrap;}
.m-card-foot .btn{flex:1;justify-content:center;}
</style>
@endpush

@section('content')

@php
    $devise   = $devise ?? ($shop->currency ?? 'GNF');
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $statusMap = [
        'livrée'=>['label'=>'Livrée','cls'=>'p-success'],
        'pending'=>['label'=>'En attente','cls'=>'p-warning'],
        'en attente'=>['label'=>'En attente','cls'=>'p-warning'],
        'en_attente'=>['label'=>'En attente','cls'=>'p-warning'],
        'confirmée'=>['label'=>'Confirmée','cls'=>'p-info'],
        'confirmed'=>['label'=>'Confirmée','cls'=>'p-info'],
        'en_livraison'=>['label'=>'En livraison','cls'=>'p-purple'],
        'delivering'=>['label'=>'En livraison','cls'=>'p-purple'],
        'annulée'=>['label'=>'Annulée','cls'=>'p-danger'],
        'cancelled'=>['label'=>'Annulée','cls'=>'p-danger'],
    ];
    $annulables   = ['pending','en attente','en_attente','confirmée','confirmed'];
    $restaurables = ['annulée','cancelled'];
    $col          = $orders->getCollection();
    $nonAssignees = $col->filter(fn($o) => !$o->livreur)->count();
    $assignees    = $col->filter(fn($o) => $o->livreur)->count();
    $caPage       = $col->where('status','livrée')->sum('total');
    $pendingCount = $col->filter(fn($o) => in_array($o->status,['pending','en attente','en_attente','confirmée','processing']))->count();
    function initiales(string $name): string {
        $p = explode(' ',$name);
        return strtoupper(substr($p[0],0,1)).strtoupper(substr($p[1] ?? 'X',0,1));
    }
@endphp

{{-- MODALS --}}
<div class="modal-overlay" id="cancelModal">
    <div class="modal-box">
        <div class="modal-icon">⚠️</div>
        <div class="modal-title">Annuler cette commande ?</div>
        <div class="modal-sub">Commande <strong id="modalOrderId"></strong> — client <strong id="modalClientName"></strong><br>Cette action est <strong>irréversible</strong>.</div>
        <div class="modal-actions">
            <button type="button" class="btn" onclick="closeModal()">← Retour</button>
            <form id="cancelForm" method="POST" style="flex:1;display:contents">
                @csrf @method('PUT')
                <button type="submit" class="btn btn-cancel-confirm" style="flex:1">❌ Confirmer</button>
            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="restoreModal">
    <div class="modal-box">
        <div class="modal-icon">🔄</div>
        <div class="modal-title">Restaurer cette commande ?</div>
        <div class="modal-sub">Commande <strong id="modalRestoreOrderId"></strong> — client <strong id="modalRestoreClientName"></strong><br>La commande repassera au statut <strong>En attente</strong>.</div>
        <div class="modal-actions">
            <button type="button" class="btn" onclick="closeRestoreModal()">← Retour</button>
            <form id="restoreForm" method="POST" style="flex:1;display:contents">
                @csrf @method('PUT')
                <button type="submit" class="btn btn-restore-confirm" style="flex:1">🔄 Confirmer</button>
            </form>
        </div>
    </div>
</div>

<div class="dash-wrap">
    <aside class="sidebar" id="sidebar">
        <div class="sb-brand">
            <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
               <div class="sb-logo-icon"><img src="/images/Shopio.jpeg" alt="Shopio" style="width:40px;height:40px;object-fit:cover;border-radius:9px"></div>
                <span class="sb-shop-name">{{ $shop->name ?? 'Boutique' }}</span>
            </a>
            <button class="sb-close" id="btnCloseSidebar">✕</button>
            <div class="sb-status"><span class="pulse"></span>{{ ($shop->is_approved ?? true) ? 'Boutique active' : 'En attente' }} &nbsp;·&nbsp; {{ ucfirst(auth()->user()->role_in_shop ?? auth()->user()->role) }}</div>
        </div>
        <div class="sb-scroll-hint" id="sbScrollHint"><div class="sb-scroll-hint-arrow"><div class="sb-scroll-hint-dot"></div><div class="sb-scroll-hint-dot"></div><div class="sb-scroll-hint-dot"></div></div></div>
        <nav class="sb-nav">
            <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px"><span class="ico">🏠</span> Accueil</a>
            <div class="sb-section">Boutique</div>
            <a href="{{ route('boutique.orders.index') }}" class="sb-item active"><span class="ico">📦</span> Commandes @if($pendingCount > 0)<span class="sb-badge">{{ $pendingCount }}</span>@endif</a>
            <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">🏷️</span> Produits</a>
            <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">👥</span> Clients</a>
            <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">🧑‍💼</span> Équipe</a>
            <div class="sb-section">Livraison</div>
            <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">🚴</span> Livreurs</a>
            <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">🏢</span> Partenaires</a>
            <div class="sb-section">Finances</div>
            <div class="sb-group">
                <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button"><span class="ico">💰</span> Finances & Rapports <span class="sb-arrow">▶</span></button>
                <div class="sb-sub">
                    <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">💳</span> Paiements</a>
                    <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">📊</span> Commissions</a>
                    <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">📋</span> Rapports</a>
                    @if(auth()->user()->role === 'admin')<a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">⚙️</span> Paramètres</a>@endif
                </div>
            </div>
            <div class="sb-section">Aide</div>
            <a href="{{ route('support.index') }}" class="sb-item"><span class="ico">🎧</span> Support</a>
        </nav>
        <div class="sb-footer">
            <a href="{{ route('profile.edit') }}" class="sb-user">
                <div class="sb-av">{{ $initials }}</div>
                <div style="flex:1;min-width:0"><div class="sb-uname">{{ Str::limit(auth()->user()->name, 20) }}</div><div class="sb-urole">{{ auth()->user()->role === 'admin' ? 'Administrateur' : ucfirst(auth()->user()->role) }}</div></div>
            </a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="sb-logout"><span class="ico">⎋</span> Se déconnecter</button></form>
        </div>
    </aside>

    <div class="sb-overlay" id="sbOverlay"></div>

    <main class="main">
        <div class="topbar">
            <button class="btn-hamburger" id="btnMenu">☰</button>
            <div class="tb-info">
                <div class="tb-title">📦 Commandes</div>
                <div class="tb-sub">{{ $shop->name ?? 'Boutique' }} &nbsp;·&nbsp; <span class="devise-badge" style="font-size:10px;padding:2px 8px">💱 {{ $devise }}</span></div>
            </div>
        </div>

        <div class="content">

            @foreach(['success','warning','danger'] as $type)
                @if(session($type))<div class="flash flash-{{ $type }}"><span>{{ $type === 'success' ? '✓' : '⚠' }}</span>{{ session($type) }}</div>@endif
            @endforeach

            <div class="stats-row">
                <div class="stat-chip"><span class="stat-dot" style="background:#f59e0b"></span><span>Non assignées</span><span class="val">{{ $nonAssignees }}</span></div>
                <div class="stat-chip"><span class="stat-dot" style="background:var(--brand)"></span><span>Assignées</span><span class="val">{{ $assignees }}</span></div>
                <div class="stat-chip"><span class="stat-dot" style="background:#3b82f6"></span><span>CA page</span><span class="val">{{ number_format($caPage,0,',',' ') }} <small style="font-size:9px;font-weight:600;color:var(--muted)">{{ $devise }}</small></span></div>
                <div class="stat-chip"><span>Total</span><span class="val">{{ $orders->total() }}</span></div>
            </div>

            {{-- TABLE DESKTOP --}}
            <div class="orders-card desktop-table">
                <div class="orders-card-hd">
                    <span class="orders-card-title">Liste des commandes</span>
                    <span style="font-size:11px;color:var(--muted)">Page {{ $orders->currentPage() }}/{{ $orders->lastPage() }} · {{ $orders->total() }} commande(s)</span>
                </div>
                @if($orders->isEmpty())
                <div class="empty-state"><span class="ico">📭</span><p>Aucune commande.</p></div>
                @else
                <table class="tbl">
                    <thead><tr><th>#</th><th>Client</th><th>Produit</th><th>Montant</th><th>Statut</th><th>Livreur</th><th>Assigner</th><th>Actions</th></tr></thead>
                    <tbody>
                    @foreach($orders as $order)
                    @php
                        $st=$statusMap[$order->status]??['label'=>ucfirst($order->status),'cls'=>'p-muted'];
                        $product=$order->items->first()?->product;
                        $client=$order->client;
                        $init=$client?initiales($client->name):'CL';
                        $peutAnnuler=in_array($order->status,$annulables);
                        $dejaAnnulee=in_array($order->status,$restaurables);
                    @endphp
                    <tr>
                        <td><span style="font-family:var(--mono);font-size:11px;color:var(--muted)">#{{ $order->id }}</span></td>
                        <td><div style="display:flex;align-items:center;gap:9px"><div class="c-av">{{ $init }}</div><div><div class="c-name">{{ $client->name ?? 'Inconnu' }}</div>@if($client?->phone)<div class="c-sub">📞 {{ $client->phone }}</div>@endif</div></div></td>
                        <td>
                            @if($product)<div style="display:flex;align-items:center;gap:8px">@if($product->image)<img src="{{ asset('storage/'.$product->image) }}" class="prod-img" alt="{{ $product->name }}">@else<div class="prod-ph">🏷️</div>@endif<div><div style="font-size:12.5px;font-weight:600;color:var(--text)">{{ Str::limit($product->name,22) }}</div><div style="font-size:11px;color:var(--muted)">Qté : {{ $order->items->first()->quantity ?? 1 }}</div></div></div>
                            @else<span style="color:var(--muted);font-size:12px">—</span>@endif
                        </td>
                        <td><div class="amount">{{ number_format($order->total,0,',',' ') }} <small>{{ $devise }}</small></div></td>
                        <td><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span></td>
                        <td>@if($order->livreur)<div class="lv-chip"><div class="lv-chip-av">{{ initiales($order->livreur->name) }}</div>{{ Str::limit($order->livreur->name,14) }}</div>@else<span class="pill p-warning">Aucun</span>@endif</td>
                        <td>
                            @if(!$order->livreur)
                            <form action="{{ route('employe.orders.assign',$order) }}" method="POST" class="assign-form">@csrf @method('PUT')
                                <select name="livreur_id" class="assign-select" required><option value="">— Choisir —</option>@foreach($livreurs as $lv)<option value="{{ $lv->id }}">{{ $lv->name }} {{ $lv->is_available ? '🟢' : '🟡' }}</option>@endforeach</select>
                                <button type="submit" class="btn btn-primary btn-sm">✅</button>
                            </form>
                            @else<span class="btn btn-assigned btn-sm">✔ Assignée</span>@endif
                        </td>
                        <td><div style="display:flex;align-items:center;gap:5px;flex-wrap:nowrap">
                            <a href="{{ route('orders.show',$order) }}" class="btn btn-info btn-sm">🔍</a>
                            @if($peutAnnuler)<button type="button" class="btn-cancel" onclick="openCancelModal('{{ route('employe.orders.cancel',$order) }}','#{{ $order->id }}','{{ addslashes($client->name ?? 'Inconnu') }}')">✕ Annuler</button>
                            @elseif($dejaAnnulee)<button type="button" class="btn-restore" onclick="openRestoreModal('{{ route('employe.orders.restore',$order) }}','#{{ $order->id }}','{{ addslashes($client->name ?? 'Inconnu') }}')">🔄 Restaurer</button>
                            @else<button type="button" class="btn-cancel disabled" disabled>✕</button>@endif
                        </div></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="pagination-wrap">{{ $orders->links() }}</div>
                @endif
            </div>

            {{-- CARTES MOBILE --}}
            <div class="mobile-list">
                @forelse($orders as $order)
                @php
                    $st=$statusMap[$order->status]??['label'=>ucfirst($order->status),'cls'=>'p-muted'];
                    $product=$order->items->first()?->product;
                    $client=$order->client;
                    $init=$client?initiales($client->name):'CL';
                    $peutAnnuler=in_array($order->status,$annulables);
                    $dejaAnnulee=in_array($order->status,$restaurables);
                @endphp
                <div class="m-card">
                    <div class="m-card-hd">
                        <div style="display:flex;align-items:center;gap:9px"><div class="c-av">{{ $init }}</div><div><div class="c-name">{{ $client->name ?? 'Inconnu' }}</div>@if($client?->phone)<div class="c-sub">📞 {{ $client->phone }}</div>@endif</div></div>
                        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0"><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span><span style="font-family:var(--mono);font-size:10px;color:var(--muted)">#{{ $order->id }}</span></div>
                    </div>
                    <div class="m-card-body">
                        @if($product)<div style="display:flex;align-items:center;gap:9px">@if($product->image)<img src="{{ asset('storage/'.$product->image) }}" class="prod-img" alt="{{ $product->name }}">@else<div class="prod-ph">🏷️</div>@endif<div><div style="font-size:13px;font-weight:600">{{ $product->name }}</div><div style="font-size:11px;color:var(--muted)">Qté : {{ $order->items->first()->quantity ?? 1 }}</div></div></div>@endif
                        <div class="m-row"><span class="m-lbl">Montant</span><span class="amount">{{ number_format($order->total,0,',',' ') }} <small>{{ $devise }}</small></span></div>
                        <div class="m-row"><span class="m-lbl">Livreur</span>@if($order->livreur)<div class="lv-chip"><div class="lv-chip-av">{{ initiales($order->livreur->name) }}</div>{{ $order->livreur->name }}</div>@else<span class="pill p-warning">Non assigné</span>@endif</div>
                        @if(!$order->livreur && $livreurs->isNotEmpty())<form action="{{ route('employe.orders.assign',$order) }}" method="POST" style="display:flex;gap:8px;align-items:center">@csrf @method('PUT')<select name="livreur_id" class="assign-select" style="flex:1" required><option value="">— Choisir —</option>@foreach($livreurs as $lv)<option value="{{ $lv->id }}">{{ $lv->name }} {{ $lv->is_available ? '🟢' : '🟡' }}</option>@endforeach</select><button type="submit" class="btn btn-primary btn-sm">✅</button></form>@endif
                    </div>
                    <div class="m-card-foot">
                        <a href="{{ route('orders.show',$order) }}" class="btn btn-info btn-sm">🔍 Détails</a>
                        @if($order->livreur)<span class="btn btn-assigned btn-sm">✔ Assignée</span>@endif
                        @if($peutAnnuler)<button type="button" class="btn-cancel" style="flex:1;justify-content:center" onclick="openCancelModal('{{ route('employe.orders.cancel',$order) }}','#{{ $order->id }}','{{ addslashes($client->name ?? 'Inconnu') }}')">✕ Annuler</button>
                        @elseif($dejaAnnulee)<button type="button" class="btn-restore" style="flex:1;justify-content:center" onclick="openRestoreModal('{{ route('employe.orders.restore',$order) }}','#{{ $order->id }}','{{ addslashes($client->name ?? 'Inconnu') }}')">🔄 Restaurer</button>@endif
                    </div>
                </div>
                @empty
                <div class="empty-state"><span class="ico">📭</span><p>Aucune commande.</p></div>
                @endforelse
                <div class="pagination-wrap">{{ $orders->links() }}</div>
            </div>

        </div>{{-- /content --}}
    </main>
</div>{{-- /dash-wrap --}}

@push('scripts')
<script>
function toggleGroup(btn) {
    const sub = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');
    document.querySelectorAll('.sb-sub.open').forEach(s => { s.classList.remove('open'); s.previousElementSibling?.classList.remove('open'); });
    if (!isOpen) { sub.classList.add('open'); btn.classList.add('open'); }
}
document.querySelectorAll('.sb-sub .sb-item.active').forEach(item => {
    const sub = item.closest('.sb-sub');
    if (sub) { sub.classList.add('open'); sub.previousElementSibling?.classList.add('open'); }
});
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sbOverlay');
    function openSidebar() { sidebar.classList.add('open'); overlay.classList.add('open'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }
    document.getElementById('btnMenu')?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);
    document.getElementById('btnCloseSidebar')?.addEventListener('click', closeSidebar);
});
function openCancelModal(url, orderId, clientName) {
    document.getElementById('cancelForm').action = url;
    document.getElementById('modalOrderId').textContent = orderId;
    document.getElementById('modalClientName').textContent = clientName;
    document.getElementById('cancelModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal() { document.getElementById('cancelModal').classList.remove('open'); document.body.style.overflow = ''; }
document.getElementById('cancelModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
function openRestoreModal(url, orderId, clientName) {
    document.getElementById('restoreForm').action = url;
    document.getElementById('modalRestoreOrderId').textContent = orderId;
    document.getElementById('modalRestoreClientName').textContent = clientName;
    document.getElementById('restoreModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeRestoreModal() { document.getElementById('restoreModal').classList.remove('open'); document.body.style.overflow = ''; }
document.getElementById('restoreModal').addEventListener('click', function(e) { if (e.target === this) closeRestoreModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeRestoreModal(); } });
</script>
@endpush

@endsection