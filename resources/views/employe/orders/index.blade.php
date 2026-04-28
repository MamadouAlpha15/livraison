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
    --brand:#6366f1;--brand-dk:#4f46e5;--brand-lt:#e0e7ff;--brand-mlt:#eef2ff;
    --sb-bg:#0e0e16;--sb-border:rgba(255,255,255,.08);--sb-act:rgba(99,102,241,.52);
    --sb-hov:rgba(255,255,255,.07);--sb-txt:rgba(255,255,255,.62);--sb-txt-act:#fff;
    --bg:#f8fafc;--surface:#ffffff;--border:#e2e8f0;--border-dk:#cbd5e1;
    --text:#0f172a;--text-2:#475569;--muted:#94a3b8;
    --font:'Plus Jakarta Sans',sans-serif;--mono:'JetBrains Mono',monospace;
    --r:14px;--r-sm:9px;--shadow-sm:0 1px 3px rgba(0,0,0,.06);--shadow:0 4px 16px rgba(0,0,0,.07);
    --sb-w:232px;--top-h:58px;
}
html{font-family:var(--font);}
body{background:var(--bg);margin:0;color:var(--text);-webkit-font-smoothing:antialiased;}
.dash-wrap{display:flex;min-height:100vh;}
.dash-wrap .main{margin-left:var(--sb-w);flex:1;min-width:0;}
.sidebar{background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);overflow-y:scroll;scrollbar-width:thin;scrollbar-color:rgba(99,102,241,.35) transparent;z-index:40;border-right:1px solid rgba(99,102,241,.15);box-shadow:6px 0 30px rgba(0,0,0,.35);}
.sidebar::-webkit-scrollbar{width:4px;}
.sidebar::-webkit-scrollbar-track{background:rgba(255,255,255,.04);}
.sidebar::-webkit-scrollbar-thumb{background:rgba(99,102,241,.4);border-radius:4px;}
.sidebar::-webkit-scrollbar-thumb:hover{background:rgba(99,102,241,.7);}
.sb-brand{padding:18px 16px 14px;border-bottom:1px solid var(--sb-border);flex-shrink:0;position:relative;}
.sb-close{display:none;position:absolute;top:14px;right:12px;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.6);font-size:18px;line-height:1;cursor:pointer;align-items:center;justify-content:center;transition:background .15s,color .15s;flex-shrink:0;}
.sb-close:hover{background:rgba(239,68,68,.18);border-color:rgba(239,68,68,.3);color:#fca5a5;}
@media(max-width:900px){.sb-close{display:flex;}}
.sb-logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:#fff;}
.sb-logo-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;}
.sb-shop-name{font-size:14.5px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:148px;letter-spacing:-.3px;color:#fff;}
.sb-status{display:flex;align-items:center;gap:6px;margin-top:9px;font-size:10.5px;color:var(--sb-txt);font-weight:500;}
.pulse{width:6px;height:6px;border-radius:50%;background:var(--brand);flex-shrink:0;animation:blink 2.2s ease-in-out infinite;box-shadow:0 0 5px var(--brand);}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.35}}
.sb-nav{padding:10px 10px 32px;flex:1;display:flex;flex-direction:column;gap:1px;overflow:visible;}
.sb-section{font-size:9.5px;text-transform:uppercase;letter-spacing:1.8px;color:rgba(255,255,255,.48);padding:16px 10px 5px;font-weight:800;}
.sb-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:rgba(255,255,255,.78);text-decoration:none;transition:background .15s,color .15s;position:relative;}
.sb-item:hover{background:var(--sb-hov);color:rgba(255,255,255,.96);}
.sb-item.active{background:var(--sb-act);color:#fff;box-shadow:0 2px 12px rgba(99,102,241,.25);}
.sb-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:20px;background:#a5b4fc;border-radius:0 3px 3px 0;box-shadow:2px 0 8px rgba(165,180,252,.5);}
.sb-item .ico{font-size:13px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);transition:background .15s;}
.sb-item:hover .ico{background:rgba(255,255,255,.09);}
.sb-item.active .ico{background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.2);}
.sb-badge{margin-left:auto;background:var(--brand);color:#fff;font-size:10px;font-weight:700;border-radius:20px;padding:1px 7px;font-family:var(--mono);min-width:20px;text-align:center;}
.sb-badge.warn{background:#f59e0b;}
.sb-group{display:flex;flex-direction:column;}
.sb-group-toggle{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:rgba(255,255,255,.78);cursor:pointer;transition:background .15s,color .15s;user-select:none;border:none;background:none;width:100%;text-align:left;font-family:var(--font);}
.sb-group-toggle:hover{background:var(--sb-hov);color:rgba(255,255,255,.96);}
.sb-group-toggle.open{color:#fff;background:rgba(255,255,255,.05);}
.sb-group-toggle .ico{font-size:13px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);}
.sb-group-toggle .sb-arrow{margin-left:auto;font-size:10px;color:rgba(255,255,255,.32);transition:transform .2s;flex-shrink:0;}
.sb-group-toggle.open .sb-arrow{transform:rotate(90deg);color:rgba(255,255,255,.6);}
.sb-sub{display:none;flex-direction:column;gap:1px;margin-left:12px;padding-left:14px;border-left:1px solid rgba(255,255,255,.1);margin-top:2px;margin-bottom:4px;overflow:visible;}
.sb-sub.open{display:flex;}
.sb-sub .sb-item{font-size:13px;font-weight:500;padding:6px 10px;color:rgba(255,255,255,.62);}
.sb-sub .sb-item:hover{color:rgba(255,255,255,.92);}
.sb-sub .sb-item.active{color:#fff;background:var(--sb-act);font-weight:600;}
.sb-scroll-hint{position:sticky;top:auto;bottom:72px;width:100%;height:40px;background:linear-gradient(to bottom,transparent,rgba(17,17,24,.95));pointer-events:none;z-index:2;display:flex;align-items:flex-end;justify-content:center;padding-bottom:6px;transition:opacity .3s;margin-top:-40px;align-self:flex-end;}
.sb-scroll-hint.hidden{opacity:0;pointer-events:none;}
.sb-scroll-hint-arrow{display:flex;flex-direction:column;align-items:center;gap:2px;animation:bounceDown 1.5s ease-in-out infinite;}
.sb-scroll-hint-dot{width:4px;height:4px;border-radius:50%;background:rgba(99,102,241,.6);}
.sb-scroll-hint-dot:nth-child(2){opacity:.5;margin-top:-2px;}
.sb-scroll-hint-dot:nth-child(3){opacity:.25;margin-top:-2px;}
@keyframes bounceDown{0%,100%{transform:translateY(0)}50%{transform:translateY(4px)}}
.sb-footer{padding:12px 10px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0;display:flex;flex-direction:column;gap:6px;position:sticky;bottom:0;background:linear-gradient(180deg,transparent 0%,#0b0b12 25%);z-index:1;}
.sb-user{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);text-decoration:none;transition:background .15s,border-color .15s;border:1px solid transparent;}
.sb-user:hover{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.07);}
.sb-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4338ca);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;box-shadow:0 0 0 2px rgba(99,102,241,.45),0 2px 8px rgba(99,102,241,.3);letter-spacing:-.5px;}
.sb-uname{font-size:13px;font-weight:700;color:#fff;letter-spacing:-.2px;}
.sb-urole{font-size:10.5px;color:rgba(255,255,255,.52);margin-top:1px;font-weight:500;}
.sb-logout{display:flex;align-items:center;gap:8px;width:100%;padding:8px 10px;border-radius:var(--r-sm);background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.15);color:rgba(252,165,165,.92);font-size:12.5px;font-weight:600;font-family:var(--font);cursor:pointer;text-decoration:none;transition:background .15s,color .15s,border-color .15s;text-align:left;}
.sb-logout:hover{background:rgba(220,38,38,.18);border-color:rgba(220,38,38,.35);color:#fca5a5;}
.sb-logout .ico{font-size:13px;flex-shrink:0;}
.sb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:39;}
.main{display:flex;flex-direction:column;min-width:0;}
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 22px;height:var(--top-h);display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:30;box-shadow:var(--shadow-sm);}
.btn-hamburger{display:none;background:none;border:none;cursor:pointer;padding:6px;color:var(--text);font-size:20px;}
.tb-info{flex:1;min-width:0;}
.tb-title{font-size:14px;font-weight:700;color:var(--text);}
.tb-sub{font-size:11px;color:var(--muted);margin-top:1px;}
.content{padding:20px 22px;flex:1;}
.flash{padding:10px 14px;border-radius:var(--r-sm);border:1px solid;font-size:13.5px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.flash-success{background:#eef2ff;border-color:#a5b4fc;color:#3730a3;}
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
.c-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--brand),#4f46e5);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;}
.c-name{font-size:13px;font-weight:600;color:var(--text);}
.c-sub{font-size:11px;color:var(--muted);}
.prod-img{width:36px;height:36px;border-radius:var(--r-sm);object-fit:cover;border:1px solid var(--border);flex-shrink:0;}
.prod-ph{width:36px;height:36px;border-radius:var(--r-sm);background:#f3f6f4;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;}
.amount{font-family:var(--mono);font-weight:700;font-size:13px;color:var(--text);white-space:nowrap;}
.amount small{font-size:10px;color:var(--muted);font-weight:500;}
.pill{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap;}
.p-success{background:#e0e7ff;color:#3730a3;}
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
/* ── Barre de filtres ── */
.filter-bar{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px 16px;margin-bottom:14px;box-shadow:var(--shadow-sm);display:flex;flex-direction:column;gap:10px;}
.filter-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.filter-search-wrap{position:relative;flex:1;min-width:180px;}
.filter-search-wrap .ico-search{position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:13px;pointer-events:none;color:var(--muted);}
.filter-input{width:100%;padding:8px 10px 8px 32px;border:1.5px solid var(--border-dk);border-radius:var(--r-sm);font-size:13px;font-family:var(--font);color:var(--text);background:var(--surface);outline:none;transition:border-color .15s;}
.filter-input:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(99,102,241,.1);}
.filter-select{padding:8px 10px;border:1.5px solid var(--border-dk);border-radius:var(--r-sm);font-size:13px;font-family:var(--font);color:var(--text);background:var(--surface);outline:none;cursor:pointer;transition:border-color .15s;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;background-size:14px;padding-right:28px;}
.filter-select:focus{border-color:var(--brand);}
.date-chips{display:flex;gap:5px;flex-wrap:wrap;}
.date-chip{padding:6px 12px;border-radius:20px;font-size:11.5px;font-weight:600;border:1.5px solid var(--border-dk);background:var(--surface);color:var(--text-2);cursor:pointer;transition:all .15s;white-space:nowrap;}
.date-chip:hover{border-color:var(--brand);color:var(--brand);}
.date-chip.active{background:var(--brand-mlt);border-color:var(--brand-lt);color:var(--brand-dk);}
.custom-dates{display:none;align-items:center;gap:6px;flex-wrap:wrap;}
.custom-dates.show{display:flex;}
.custom-dates input[type=date]{padding:7px 10px;border:1.5px solid var(--border-dk);border-radius:var(--r-sm);font-size:12.5px;font-family:var(--font);color:var(--text);background:var(--surface);outline:none;transition:border-color .15s;}
.custom-dates input[type=date]:focus{border-color:var(--brand);}
.btn-filter-apply{padding:8px 16px;border-radius:var(--r-sm);font-size:13px;font-weight:700;font-family:var(--font);background:var(--brand);color:#fff;border:none;cursor:pointer;transition:all .15s;white-space:nowrap;}
.btn-filter-apply:hover{background:var(--brand-dk);}
.btn-filter-reset{padding:8px 12px;border-radius:var(--r-sm);font-size:12.5px;font-weight:600;font-family:var(--font);background:var(--surface);color:var(--text-2);border:1.5px solid var(--border-dk);cursor:pointer;transition:all .15s;text-decoration:none;display:inline-flex;align-items:center;white-space:nowrap;}
.btn-filter-reset:hover{border-color:var(--brand);color:var(--brand);}
.filter-active-badge{display:inline-flex;align-items:center;gap:4px;background:#fef3c7;border:1px solid #fcd34d;color:#92400e;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;}
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

{{-- ══ MODAL ENTREPRISE DE LIVRAISON ══ --}}
<div class="modal-overlay" id="companyModal">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-icon">🏢</div>
        <div class="modal-title">Confier à une entreprise</div>
        <div class="modal-sub" style="margin-bottom:14px;">Sélectionnez l'entreprise qui prendra en charge cette livraison.</div>

        <form id="companyForm" method="POST" action="">
            @csrf @method('PUT')
            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:18px;max-height:260px;overflow-y:auto;">
                @forelse($deliveryCompanies as $dc)
                <label style="display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:10px;border:1.5px solid var(--border);cursor:pointer;transition:border-color .14s;background:var(--bg);"
                       onclick="this.style.borderColor='#6366f1'"
                       onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='var(--border)'">
                    <input type="radio" name="delivery_company_id" value="{{ $dc->id }}"
                           style="accent-color:#6366f1;width:16px;height:16px;flex-shrink:0;"
                           onchange="document.querySelectorAll('#companyModal label').forEach(l=>l.style.borderColor='var(--border)'); this.closest('label').style.borderColor='#6366f1';">
                    <div style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
                        @if($dc->image)
                            <img src="{{ asset('storage/'.$dc->image) }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                        @else
                            <span style="font-size:16px;">🚚</span>
                        @endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13.5px;font-weight:700;color:var(--text);">{{ $dc->name }}</div>
                        <div style="font-size:11px;color:var(--muted);">
                            {{ $dc->phone ?? '' }}
                            @if($dc->commission_percent) · Commission {{ number_format($dc->commission_percent,0) }}% @endif
                        </div>
                    </div>
                </label>
                @empty
                <div style="text-align:center;padding:20px;color:var(--muted);font-size:13px;">
                    Aucune entreprise de livraison approuvée disponible.
                </div>
                @endforelse
            </div>

            <div class="modal-actions">
                <button type="button" class="btn" onclick="closeCompanyModal()">← Annuler</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">🏢 Confier la livraison</button>
            </div>
        </form>
    </div>
</div>

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
                <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
                <span class="sb-shop-name">{{ $shop->name ?? 'Boutique' }}</span>
            </a>
            <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer le menu">✕</button>
            <div class="sb-status">
                <span class="pulse"></span>
                {{ ($shop->is_approved ?? true) ? 'Boutique active' : 'En attente de validation' }}
                &nbsp;·&nbsp;
                {{ ucfirst(auth()->user()->role_in_shop ?? auth()->user()->role) }}
            </div>
        </div>
        <div class="sb-scroll-hint" id="sbScrollHint">
            <div class="sb-scroll-hint-arrow">
                <div class="sb-scroll-hint-dot"></div>
                <div class="sb-scroll-hint-dot"></div>
                <div class="sb-scroll-hint-dot"></div>
            </div>
        </div>
        <nav class="sb-nav">
            <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px"><span class="ico">⊞</span> Tableau de bord</a>
            <div class="sb-section">Boutique</div>
            <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">💬</span> Messages</a>
            <a href="{{ route('boutique.orders.index') }}" class="sb-item active">
                <span class="ico">📦</span> Commandes
                @if($pendingCount > 0)<span class="sb-badge">{{ $pendingCount }}</span>@endif
            </a>
            <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">🏷️</span> Produits</a>
            <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">👥</span> Clients</a>
            <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">🧑‍💼</span> Équipe</a>
            <div class="sb-section">Livraison</div>
            <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">🚴</span> Livreurs</a>
            <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">🏢</span> Partenaires</a>
            <div class="sb-section">Finances</div>
            <div class="sb-group">
                <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                    <span class="ico">💰</span> Finances & Rapports <span class="sb-arrow">▶</span>
                </button>
                <div class="sb-sub">
                    <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">💳</span> Paiements</a>
                    <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">📊</span> Commissions</a>
                    <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">📋</span> Rapports</a>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">⚙️</span> Paramètres</a>
                    @endif
                </div>
            </div>
            <div class="sb-section">Aide</div>
            <a href="{{ route('support.index') }}" class="sb-item"><span class="ico">🎧</span> Support</a>
        </nav>
        <div class="sb-footer">
            <a href="{{ route('profile.edit') }}" class="sb-user">
                <div class="sb-av">{{ $initials }}</div>
                <div style="flex:1;min-width:0">
                    <div class="sb-uname">{{ Str::limit(auth()->user()->name, 20) }}</div>
                    <div class="sb-urole">{{ auth()->user()->role === 'admin' ? 'Administrateur' : ucfirst(auth()->user()->role) }}</div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sb-logout"><span class="ico">⎋</span> Se déconnecter</button>
            </form>
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
            <div id="autoRefreshBadge" style="display:flex;align-items:center;gap:6px;background:#f0fdf4;border:1px solid #86efac;border-radius:20px;padding:4px 12px;font-size:11.5px;font-weight:600;color:#166534;cursor:pointer;transition:all .2s;white-space:nowrap;flex-shrink:0" onclick="togglePause()" title="Cliquer pour mettre en pause">
                <span id="refreshDot" style="width:7px;height:7px;border-radius:50%;background:#22c55e;animation:blink 2.2s ease-in-out infinite;flex-shrink:0"></span>
                <span id="refreshLabel">Actu dans <strong id="refreshCount">30</strong>s</span>
            </div>
        </div>

        <div class="content">

            @foreach(['success','warning','danger'] as $type)
                @if(session($type))<div class="flash flash-{{ $type }}"><span>{{ $type === 'success' ? '✓' : '⚠' }}</span>{{ session($type) }}</div>@endif
            @endforeach

            {{-- ── BARRE DE FILTRES ── --}}
            <form method="GET" action="{{ route('employe.orders.index') }}" id="filterForm">
            <div class="filter-bar">

                {{-- Ligne 1 : recherche + statut + boutons --}}
                <div class="filter-row">
                    <div class="filter-search-wrap">
                        <span class="ico-search">🔍</span>
                        <input type="text" name="search" class="filter-input"
                               placeholder="Rechercher client ou #commande…"
                               value="{{ $search ?? '' }}">
                    </div>

                    <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Tous les statuts</option>
                        @foreach([
                            'en_attente'   => 'En attente',
                            'confirmée'    => 'Confirmée',
                            'en_livraison' => 'En livraison',
                            'livrée'       => 'Livrée',
                            'annulée'      => 'Annulée',
                        ] as $val => $label)
                        <option value="{{ $val }}" {{ ($status ?? '') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn-filter-apply">Filtrer</button>

                    @if(($search ?? '') || ($status ?? '') || (($dateFilter ?? 'all') !== 'all'))
                    <a href="{{ route('employe.orders.index') }}" class="btn-filter-reset">✕ Reset</a>
                    <span class="filter-active-badge">⚡ Filtre actif</span>
                    @endif
                </div>

                {{-- Ligne 2 : date chips --}}
                <div class="filter-row">
                    <span style="font-size:11.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;white-space:nowrap">Période :</span>
                    <div class="date-chips">
                        @foreach(['all'=>'Tout','today'=>"Aujourd'hui",'week'=>'Cette semaine','month'=>'Ce mois','custom'=>'Personnalisé'] as $val=>$lbl)
                        <button type="button" class="date-chip {{ ($dateFilter ?? 'all') === $val ? 'active' : '' }}"
                                onclick="setDate('{{ $val }}')">{{ $lbl }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="date" id="dateInput" value="{{ $dateFilter ?? 'all' }}">
                </div>

                {{-- Dates personnalisées --}}
                <div class="custom-dates {{ ($dateFilter ?? 'all') === 'custom' ? 'show' : '' }}" id="customDates">
                    <span style="font-size:12px;color:var(--muted);font-weight:600">Du</span>
                    <input type="date" name="from" value="{{ $dateFrom ?? '' }}">
                    <span style="font-size:12px;color:var(--muted);font-weight:600">au</span>
                    <input type="date" name="to" value="{{ $dateTo ?? '' }}">
                    <button type="submit" class="btn-filter-apply" style="padding:7px 14px;font-size:12.5px">Appliquer</button>
                </div>

            </div>
            </form>

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
                    <thead><tr><th>#</th><th>Client</th><th>Produit</th><th>Montant</th><th>Statut</th><th>Livreur / Entreprise</th><th>Assigner</th><th>Actions</th></tr></thead>
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
                        <td>
                            @if($order->livreur)
                                <div class="lv-chip"><div class="lv-chip-av">{{ initiales($order->livreur->name) }}</div>{{ Str::limit($order->livreur->name,14) }}</div>
                            @elseif($order->deliveryCompany)
                                <div class="lv-chip" style="background:#eef2ff;border-color:#c7d2fe;color:#4f46e5;">
                                    <div class="lv-chip-av" style="background:#6366f1;">🏢</div>
                                    {{ Str::limit($order->deliveryCompany->name,14) }}
                                </div>
                            @else
                                <span class="pill p-warning">Aucun</span>
                            @endif
                        </td>
                        <td>
                            @if(!$order->livreur && !$order->deliveryCompany)
                            <div class="assign-form" style="gap:4px;">
                                {{-- Livreur interne --}}
                                <form action="{{ route('employe.orders.assign',$order) }}" method="POST" style="display:flex;gap:4px;align-items:center;">
                                    @csrf @method('PUT')
                                    <select name="livreur_id" class="assign-select" required>
                                        <option value="">🚴 Livreur…</option>
                                        @foreach($livreurs as $lv)
                                        <option value="{{ $lv->id }}">{{ $lv->name }} {{ $lv->is_available ? '🟢' : '🟡' }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm" title="Assigner ce livreur">✅</button>
                                </form>
                                {{-- Entreprise externe --}}
                                <button type="button"
                                        class="btn btn-sm"
                                        style="background:#eef2ff;border-color:#c7d2fe;color:#4f46e5;white-space:nowrap;"
                                        onclick="openCompanyModal({{ $order->id }})">
                                    🏢
                                </button>
                            </div>
                            @elseif($order->livreur || $order->deliveryCompany)
                                <span class="btn btn-assigned btn-sm">✔ Assignée</span>
                            @endif
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
/* ── Modal entreprise de livraison ── */
function openCompanyModal(orderId) {
    const url = `/employe/orders/${orderId}/send-to-company`;
    document.getElementById('companyForm').action = url;
    // reset sélection
    document.querySelectorAll('#companyModal input[type=radio]').forEach(r => r.checked = false);
    document.querySelectorAll('#companyModal label').forEach(l => l.style.borderColor = 'var(--border)');
    document.getElementById('companyModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeCompanyModal() {
    document.getElementById('companyModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('companyModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeCompanyModal();
});

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
    const sidebar    = document.getElementById('sidebar');
    const overlay    = document.getElementById('sbOverlay');
    const scrollHint = document.getElementById('sbScrollHint');

    function openSidebar()  { sidebar.classList.add('open');    overlay.classList.add('open'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }

    document.getElementById('btnMenu')?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);
    document.getElementById('btnCloseSidebar')?.addEventListener('click', closeSidebar);

    function updateScrollHint() {
        if (!sidebar || !scrollHint) return;
        const atBottom    = sidebar.scrollTop + sidebar.clientHeight >= sidebar.scrollHeight - 16;
        const needsScroll = sidebar.scrollHeight > sidebar.clientHeight + 20;
        scrollHint.classList.toggle('hidden', atBottom || !needsScroll);
    }
    sidebar?.addEventListener('scroll', updateScrollHint);
    window.addEventListener('resize', updateScrollHint);
    setTimeout(updateScrollHint, 300);
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

function setDate(val) {
    document.getElementById('dateInput').value = val;
    document.querySelectorAll('.date-chip').forEach(c => c.classList.remove('active'));
    event.currentTarget.classList.add('active');
    const custom = document.getElementById('customDates');
    if (val === 'custom') {
        custom.classList.add('show');
    } else {
        custom.classList.remove('show');
        document.getElementById('filterForm').submit();
    }
}

/* ── Auto-refresh ── */
(function () {
    const INTERVAL = 30;
    let seconds    = INTERVAL;
    let paused     = false;
    let timer      = null;

    const badge   = document.getElementById('autoRefreshBadge');
    const dot     = document.getElementById('refreshDot');
    const count   = document.getElementById('refreshCount');
    const label   = document.getElementById('refreshLabel');

    function isModalOpen() {
        return document.getElementById('cancelModal').classList.contains('open') ||
               document.getElementById('restoreModal').classList.contains('open');
    }

    function isUserTyping() {
        const active = document.activeElement;
        if (!active) return false;
        return ['INPUT','TEXTAREA','SELECT'].includes(active.tagName);
    }

    function tick() {
        if (paused || isModalOpen() || isUserTyping()) return;

        seconds--;
        count.textContent = seconds;

        if (seconds <= 0) {
            location.reload();
        }
    }

    window.togglePause = function () {
        paused = !paused;
        if (paused) {
            badge.style.background = '#fef9c3';
            badge.style.borderColor = '#fde047';
            badge.style.color = '#854d0e';
            dot.style.background = '#eab308';
            dot.style.animation = 'none';
            label.innerHTML = '⏸ En pause';
        } else {
            seconds = INTERVAL;
            badge.style.background = '#f0fdf4';
            badge.style.borderColor = '#86efac';
            badge.style.color = '#166534';
            dot.style.background = '#22c55e';
            dot.style.animation = 'blink 2.2s ease-in-out infinite';
            label.innerHTML = 'Actu dans <strong id="refreshCount">' + seconds + '</strong>s';
        }
    };

    timer = setInterval(tick, 1000);
})();
</script>
@endpush

@endsection