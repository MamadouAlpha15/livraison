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
.prod-img{width:58px;height:58px;border-radius:var(--r-sm);object-fit:cover;border:2px solid var(--border);flex-shrink:0;cursor:pointer;transition:transform .15s,box-shadow .15s;}
.prod-img:hover{transform:scale(1.07);box-shadow:0 4px 16px rgba(0,0,0,.18);}
.prod-ph{width:58px;height:58px;border-radius:var(--r-sm);background:#f3f6f4;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;}
.addr-cell{font-size:11.5px;color:var(--text-2);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.addr-empty{color:var(--muted);font-size:11px;}
/* ── LIGHTBOX PHOTO ── */
.lb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.82);z-index:500;align-items:center;justify-content:center;padding:16px;cursor:zoom-out;}
.lb-overlay.open{display:flex;}
.lb-img{max-width:92vw;max-height:88vh;border-radius:12px;box-shadow:0 24px 72px rgba(0,0,0,.5);object-fit:contain;}
.lb-caption{position:absolute;bottom:24px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.8);font-size:13px;font-weight:600;background:rgba(0,0,0,.4);padding:6px 16px;border-radius:20px;pointer-events:none;white-space:nowrap;}
.lb-close{position:absolute;top:16px;right:16px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);color:#fff;width:36px;height:36px;border-radius:50%;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .14s;}
.lb-close:hover{background:rgba(255,255,255,.3);}
.amount{font-family:var(--mono);font-weight:700;font-size:13px;color:var(--text);white-space:nowrap;}
.amount small{font-size:10px;color:var(--muted);font-weight:500;}
.pill{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap;}
.p-success{background:#e0e7ff;color:#3730a3;}
.p-warning{background:#fef3c7;color:#92400e;}
.p-info{background:#dbeafe;color:#1e40af;}
.p-danger{background:#fee2e2;color:#991b1b;}
.p-purple{background:#f5f3ff;color:#5b21b6;}
.p-muted{background:#f3f6f4;color:#6b7280;}
/* ── BULK SELECTION ── */
.order-cb{width:15px;height:15px;cursor:pointer;accent-color:var(--brand);flex-shrink:0;}
#selectAll{width:15px;height:15px;cursor:pointer;accent-color:var(--brand);}
.tbl tbody tr.row-selected td{background:#eef2ff!important;}
.bulk-bar{display:none;align-items:center;gap:10px;flex-wrap:wrap;background:linear-gradient(135deg,#4f46e5,#6366f1);border-radius:var(--r-sm);padding:10px 16px;margin-bottom:12px;box-shadow:0 4px 20px rgba(99,102,241,.28);animation:fadeIn .2s ease;}
@keyframes fadeIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
.bulk-bar.visible{display:flex;}
.bulk-count{font-size:13px;font-weight:800;color:#fff;flex-shrink:0;font-family:var(--mono);}
.bulk-btn{padding:7px 14px;border-radius:7px;border:none;cursor:pointer;font-size:12.5px;font-weight:700;font-family:var(--font);display:inline-flex;align-items:center;gap:6px;transition:all .14s;white-space:nowrap;}
.bulk-btn-driver{background:#fff;color:#4f46e5;}
.bulk-btn-driver:hover{background:#e0e7ff;}
.bulk-btn-company{background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.35);}
.bulk-btn-company:hover{background:rgba(255,255,255,.28);}
.bulk-btn-clear{margin-left:auto;background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);border:1px solid rgba(255,255,255,.18);padding:5px 10px;border-radius:6px;font-size:11.5px;}
.bulk-btn-clear:hover{background:rgba(255,255,255,.22);color:#fff;}
/* ── BULK LIVREUR MODAL ── */
.blv-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:800;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(3px);}
.blv-modal.open{display:flex;}
.blv-box{background:var(--surface);border-radius:var(--r);padding:24px 22px;width:100%;max-width:430px;box-shadow:0 20px 60px rgba(0,0,0,.18);animation:fadeIn .2s ease;}
.blv-hd{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;}
.blv-title{font-size:15px;font-weight:800;color:var(--text);}
.blv-sub{font-size:12px;color:var(--muted);margin-top:3px;}
.blv-close{background:var(--bg);border:1px solid var(--border);border-radius:8px;width:30px;height:30px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:var(--muted);flex-shrink:0;}
.blv-list{display:flex;flex-direction:column;gap:6px;max-height:260px;overflow-y:auto;margin-bottom:16px;}
.blv-item{display:flex;align-items:center;gap:10px;padding:10px 13px;border-radius:10px;border:1.5px solid var(--border);cursor:pointer;transition:border-color .14s,background .14s;user-select:none;}
.blv-item:hover{border-color:var(--brand);background:var(--brand-mlt);}
.blv-item input[type=radio]{width:15px;height:15px;accent-color:var(--brand);cursor:pointer;flex-shrink:0;}
.blv-item.checked{border-color:var(--brand-dk);background:#eef2ff;}
.blv-footer{display:flex;gap:8px;}
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

/* ── CHAT MODAL ── */
.chat-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:300;align-items:center;justify-content:center;padding:16px;}
.chat-overlay.open{display:flex;}
.chat-panel{
    background:#fff;border-radius:16px;width:100%;max-width:520px;
    display:flex;flex-direction:column;height:90vh;max-height:680px;
    box-shadow:0 28px 80px rgba(0,0,0,.22);overflow:hidden;animation:modalIn .2s ease;
}
.chat-hd{
    padding:14px 18px;border-bottom:1px solid var(--border);flex-shrink:0;
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;
}
.chat-hd-info{display:flex;align-items:center;gap:10px;}
.chat-hd-av{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;overflow:hidden;}
.chat-hd-av img{width:100%;height:100%;object-fit:cover;}
.chat-hd-name{font-size:14px;font-weight:800;color:#fff;line-height:1.2;}
.chat-hd-sub{font-size:11px;color:rgba(255,255,255,.7);margin-top:1px;}
.chat-close{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;font-size:16px;flex-shrink:0;transition:background .14s;}
.chat-close:hover{background:rgba(255,255,255,.3);}

/* Bloc contexte commande */
.chat-order-ctx{
    padding:10px 14px;flex-shrink:0;
    background:#eff6ff;border-bottom:1px solid #bfdbfe;
    display:flex;align-items:flex-start;gap:12px;
}
.chat-order-num{font-size:11px;font-weight:800;color:#1d4ed8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;}
.chat-order-client{font-size:13px;font-weight:700;color:#1e40af;}
.chat-order-meta{font-size:11.5px;color:#3b82f6;margin-top:2px;}
.chat-ctx-ico{width:34px;height:34px;border-radius:9px;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}

/* Messages */
.chat-msgs{flex:1;overflow-y:auto;padding:14px 16px;display:flex;flex-direction:column;gap:8px;background:#f8fafc;scrollbar-width:thin;scrollbar-color:#c7d2fe transparent;}
.chat-msgs::-webkit-scrollbar{width:3px;}
.chat-msgs::-webkit-scrollbar-thumb{background:#c7d2fe;border-radius:3px;}
.msg-row{display:flex;flex-direction:column;max-width:80%;}
.msg-row.mine{align-self:flex-end;align-items:flex-end;}
.msg-row.theirs{align-self:flex-start;align-items:flex-start;}
.msg-bubble{padding:9px 13px;border-radius:14px;font-size:13px;line-height:1.5;word-break:break-word;white-space:pre-wrap;}
.msg-row.mine .msg-bubble{background:#4f46e5;color:#fff;border-bottom-right-radius:4px;}
.msg-row.theirs .msg-bubble{background:#fff;color:#1e293b;border-bottom-left-radius:4px;border:1px solid #e2e8f0;}
.msg-bubble.system{background:#fef9c3;color:#92400e;border:1px solid #fde68a;font-size:12px;font-style:italic;border-radius:10px;}
.msg-meta{font-size:10px;color:#94a3b8;margin-top:2px;padding:0 2px;}
.chat-empty{text-align:center;padding:32px 16px;color:#94a3b8;font-size:13px;}

/* Bouton confier */
.chat-confier-zone{padding:10px 14px;flex-shrink:0;background:#f0fdf4;border-top:1.5px solid #bbf7d0;}
.btn-confier{
    width:100%;padding:12px;border-radius:10px;border:none;cursor:pointer;
    background:linear-gradient(135deg,#059669,#10b981);color:#fff;
    font-size:14px;font-weight:800;font-family:var(--font);
    display:flex;align-items:center;justify-content:center;gap:8px;
    box-shadow:0 4px 14px rgba(16,185,129,.35);transition:all .16s;
}
.btn-confier:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(16,185,129,.5);}
.btn-confier:disabled{opacity:.6;cursor:not-allowed;transform:none;}
.btn-confier.done{background:linear-gradient(135deg,#6b7280,#9ca3af);box-shadow:none;}

/* Zone saisie */
.chat-input-zone{padding:10px 14px;flex-shrink:0;border-top:1px solid var(--border);background:#fff;display:flex;gap:8px;align-items:flex-end;}
.chat-textarea{
    flex:1;resize:none;border:1.5px solid var(--border);border-radius:10px;
    padding:9px 12px;font-size:13px;font-family:var(--font);color:var(--text);
    outline:none;line-height:1.4;max-height:120px;min-height:40px;
    transition:border-color .14s;scrollbar-width:thin;
}
.chat-textarea:focus{border-color:var(--brand);}
.chat-send-btn{
    width:38px;height:38px;border-radius:10px;border:none;cursor:pointer;
    background:var(--brand);color:#fff;font-size:17px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;transition:background .14s;
}
.chat-send-btn:hover{background:var(--brand-dk);}
.chat-send-btn:disabled{opacity:.5;cursor:not-allowed;}

/* Bouton message sur chaque entreprise */
.btn-msg-company{
    width:32px;height:32px;border-radius:8px;border:1.5px solid #c7d2fe;
    background:#eef2ff;color:#4f46e5;cursor:pointer;font-size:15px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
    transition:all .14s;
}
.btn-msg-company:hover{background:#4f46e5;color:#fff;border-color:#4f46e5;}
.btn-restore-confirm{background:#2563eb;border-color:#1d4ed8;color:#fff;font-weight:700;}
.btn-restore-confirm:hover{background:#1d4ed8;color:#fff;}
/* ── Desktop medium (sidebar visible, écrans 900–1200px) ── */
@media(max-width:1200px) and (min-width:901px){
    table.tbl{min-width:720px !important;}
    .tbl thead th,.tbl tbody td{padding:8px 9px;font-size:12px;}
    .tbl thead th:nth-child(5),.tbl tbody td:nth-child(5){display:none;} /* masque Adresse */
    .assign-select{min-width:82px;max-width:115px;font-size:11.5px;padding:5px 8px;}
    .prod-img,.prod-ph{width:44px;height:44px;}
    .lv-chip{font-size:11px;}
    .amount{font-size:12.5px;}
}
@media(max-width:900px){
    :root{--sb-w:230px;}
    .dash-wrap .main{margin-left:0;}
    .sidebar{transform:translateX(-100%);transition:transform .25s cubic-bezier(.23,1,.32,1);}
    .sidebar.open{transform:translateX(0);}
    .sb-overlay.open{display:block;}
    .btn-hamburger{display:flex;}
    .content{padding:14px;}
    /* Table compacte — sidebar masquée donc pleine largeur disponible */
    table.tbl{min-width:660px !important;}
    .tbl thead th,.tbl tbody td{padding:7px 9px;font-size:11.5px;}
    .tbl thead th:nth-child(5),.tbl tbody td:nth-child(5){display:none;} /* masque Adresse */
    .prod-img,.prod-ph{width:40px;height:40px;font-size:18px;}
    .assign-select{min-width:78px;max-width:110px;font-size:11px;padding:5px 6px;}
    .lv-chip{font-size:10.5px;padding:3px 8px 3px 3px;}
    .c-name{font-size:12px;}
    .c-sub{font-size:10px;}
    .amount{font-size:12px;}
}
@media(max-width:750px){
    table.tbl{min-width:540px !important;}
    .tbl thead th:nth-child(4),.tbl tbody td:nth-child(4){display:none;} /* masque Produit */
}
@media(max-width:640px){
    .desktop-table{display:none !important;}
    .mobile-list{display:flex !important;}
    .content{padding:10px;}
    .stats-row{gap:5px;flex-wrap:wrap;}
    .stat-chip{min-width:calc(50% - 5px);padding:7px 9px;flex:1 1 calc(50% - 5px);font-size:10px;}
    .filter-bar{padding:10px 12px;}
    .filter-row{gap:6px;}
    .date-chips{gap:4px;}
    .date-chip{padding:5px 9px;font-size:10.5px;}
    .topbar{padding:0 12px;gap:8px;}
    #autoRefreshBadge{padding:3px 8px;font-size:10.5px;}
}
@media(max-width:480px){
    .content{padding:8px;}
    .stats-row{gap:4px;}
    .stat-chip{font-size:9.5px;padding:6px 7px;}
    .m-card-hd,.m-card-body,.m-card-foot{padding-left:10px;padding-right:10px;}
    .m-card-foot .btn,.m-card-foot .btn-cancel,.m-card-foot .btn-restore,.m-card-foot .btn-noter{font-size:11px;padding:6px 8px;}
    .assign-select{min-width:90px;}
    .filter-search-wrap{min-width:0;width:100%;}
    .filter-row{flex-wrap:wrap;}
    .btn-filter-apply,.btn-filter-reset{width:100%;justify-content:center;}
    .chat-panel{border-radius:16px 16px 0 0;}
    .chat-overlay{padding:0;align-items:flex-end;}
}
@media(max-width:360px){
    .topbar{padding:0 8px;}
    .m-card-hd{flex-wrap:wrap;gap:6px;}
    .stat-chip .val{font-size:14px;}
    #autoRefreshBadge{display:none !important;}
}
*{scrollbar-width:thin;scrollbar-color:rgba(99,102,241,.2) transparent;}
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
.m-card{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--shadow-sm);transition:border-color .15s,background .15s;}
.m-card.row-selected{border-color:var(--brand) !important;background:#eef2ff;}
.m-card.row-selected .m-card-hd{background:#e0e7ff;}
.m-card-hd{padding:11px 14px;background:var(--bg);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:8px;}
.m-card-body{padding:12px 14px;display:flex;flex-direction:column;gap:9px;}
.m-row{display:flex;align-items:center;justify-content:space-between;gap:8px;}
.m-lbl{font-size:10.5px;color:var(--muted);font-weight:600;text-transform:uppercase;}
.m-card-foot{padding:10px 14px;border-top:1px solid var(--border);display:flex;gap:7px;flex-wrap:wrap;}
.m-card-foot .btn{flex:1;justify-content:center;}
/* ── MODAL NOTATION ENTREPRISE ── */
.rate-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:400;align-items:center;justify-content:center;padding:16px;}
.rate-overlay.open{display:flex;}
.rate-box{background:#fff;border-radius:16px;padding:28px 24px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.22);animation:modalIn .2s ease;}
.rate-stars{display:flex;gap:8px;justify-content:center;margin:16px 0 10px;}
.star-btn{font-size:30px;cursor:pointer;background:none;border:none;padding:4px;transition:transform .12s;line-height:1;outline:none;}
.star-btn:hover,.star-btn.lit{transform:scale(1.18);}
.rate-comment{width:100%;padding:10px 12px;border:1.5px solid var(--border-dk);border-radius:10px;font-size:13px;font-family:var(--font);resize:vertical;min-height:76px;outline:none;transition:border-color .15s;margin-top:4px;box-sizing:border-box;}
.rate-comment:focus{border-color:var(--brand);}
.btn-rate-submit{width:100%;padding:12px;border-radius:10px;border:none;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-size:14px;font-weight:800;font-family:var(--font);cursor:pointer;transition:all .15s;margin-top:14px;display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-rate-submit:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 6px 18px rgba(245,158,11,.4);}
.btn-rate-submit:disabled{opacity:.5;cursor:not-allowed;}
.btn-noter{background:#fffbeb;border:1.5px solid #fcd34d;color:#92400e;padding:5px 10px;font-size:11.5px;font-weight:700;border-radius:var(--r-sm);cursor:pointer;font-family:var(--font);display:inline-flex;align-items:center;gap:4px;transition:all .14s;white-space:nowrap;}
.btn-noter:hover{background:#fef3c7;border-color:#fbbf24;color:#78350f;}
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
    $nonAssignees = $col->filter(fn($o) => !$o->livreur && !$o->deliveryCompany)->count();
    $assignees    = $col->filter(fn($o) => $o->livreur || $o->deliveryCompany)->count();
    $caPage       = $col->where('status','livrée')->sum('total');
    $pendingCount = $col->filter(fn($o) => in_array($o->status,['pending','en attente','en_attente','confirmée','processing']))->count();
    $reviewsByOrderId = \App\Models\Review::whereIn('order_id', $orders->pluck('id'))
        ->where('user_id', auth()->id())
        ->get()->keyBy('order_id');
    function initiales(string $name): string {
        $p = explode(' ',$name);
        return strtoupper(substr($p[0],0,1)).strtoupper(substr($p[1] ?? 'X',0,1));
    }
@endphp

{{-- ══ MODAL ENTREPRISE DE LIVRAISON ══ --}}
<div class="modal-overlay" id="companyModal">
    <div class="modal-box" style="max-width:480px;padding:22px 20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div>
                <div style="font-size:15px;font-weight:800;color:var(--text);">🏢 Choisir une entreprise</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px;">Discutez et confiez la livraison</div>
            </div>
            <button onclick="closeCompanyModal()" style="background:var(--bg);border:1px solid var(--border);border-radius:8px;width:30px;height:30px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:var(--muted);">✕</button>
        </div>

        {{-- Contexte commande --}}
        <div id="companyModalCtx" style="padding:10px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;margin-bottom:14px;font-size:12px;color:#1d4ed8;display:none;">
            <div style="font-weight:800;margin-bottom:2px;" id="ctxOrderNum"></div>
            <div id="ctxClientName" style="font-weight:700;font-size:13px;color:#1e40af;"></div>
            <div id="ctxClientPhone" style="margin-top:2px;"></div>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;max-height:280px;overflow-y:auto;padding-right:2px;">
            @forelse($deliveryCompanies as $dc)
            <div style="display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;border:1.5px solid var(--border);background:var(--bg);">
                {{-- Logo --}}
                <div style="width:38px;height:38px;border-radius:9px;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
                    @if($dc->image)
                        <img src="{{ asset('storage/'.$dc->image) }}" style="width:100%;height:100%;object-fit:cover;" alt="" id="dc-img-{{ $dc->id }}">
                    @else
                        <span style="font-size:17px;">🚚</span>
                    @endif
                </div>
                {{-- Infos --}}
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13.5px;font-weight:700;color:var(--text);">{{ $dc->name }}</div>
                    <div style="font-size:11px;color:var(--muted);">
                        {{ $dc->phone ?? 'Aucun numéro' }}
                        @if($dc->commission_percent) · {{ number_format($dc->commission_percent,0) }}% commission @endif
                    </div>
                </div>
                {{-- Boutons --}}
                <div style="display:flex;gap:5px;flex-shrink:0;">
                    <button class="btn-msg-company bulk-hide"
                            title="Discuter avec {{ $dc->name }}"
                            data-company-id="{{ $dc->id }}"
                            data-company-name="{{ $dc->name }}"
                            data-company-img="{{ $dc->image ? asset('storage/'.$dc->image) : '' }}"
                            onclick="openChatFromCompany(this)">
                        💬
                    </button>
                    <button class="btn btn-sm bulk-assign-company bulk-show"
                            style="display:none;background:#eef2ff;border-color:#c7d2fe;color:#4f46e5;font-weight:700;"
                            data-company-id="{{ $dc->id }}"
                            data-company-name="{{ addslashes($dc->name) }}"
                            onclick="submitBulkCompany(this)"
                            title="Confier les commandes sélectionnées à cette entreprise">
                        ✅ Confier
                    </button>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:24px;color:var(--muted);font-size:13px;">
                Aucune entreprise de livraison approuvée disponible.
            </div>
            @endforelse
        </div>

        <div style="margin-top:16px;">
            <button type="button" class="btn" style="width:100%;justify-content:center;" onclick="closeCompanyModal()">← Fermer</button>
        </div>
    </div>
</div>

{{-- ══ MODAL BULK LIVREUR ══ --}}
<div class="blv-modal" id="bulkDriverModal">
    <div class="blv-box">
        <div class="blv-hd">
            <div>
                <div class="blv-title">🚴 Assigner à un livreur</div>
                <div class="blv-sub"><span id="bulkDriverCount">0</span> commande(s) sélectionnée(s) seront assignées</div>
            </div>
            <button class="blv-close" onclick="closeBulkDriverModal()">✕</button>
        </div>
        @if($livreurs->isEmpty())
        <div style="text-align:center;padding:28px;color:var(--muted);font-size:13px;">Aucun livreur disponible.</div>
        @else
        <div class="blv-list" id="blvList">
            @foreach($livreurs as $lv)
            <label class="blv-item" for="blv-{{ $lv->id }}">
                <input type="radio" name="bulk_livreur" id="blv-{{ $lv->id }}" value="{{ $lv->id }}"
                       onchange="document.querySelectorAll('.blv-item').forEach(el=>el.classList.remove('checked'));this.closest('.blv-item').classList.add('checked')">
                <div class="c-av" style="flex-shrink:0;">{{ initiales($lv->name) }}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:700;color:var(--text);">{{ $lv->name }}</div>
                    <div style="font-size:11px;color:var(--muted);">{{ $lv->is_available ? '🟢 Disponible' : '🟡 Occupé' }}@if($lv->phone) · {{ $lv->phone }}@endif</div>
                </div>
            </label>
            @endforeach
        </div>
        @endif
        <div class="blv-footer">
            <button class="btn" style="flex:0 0 auto;" onclick="closeBulkDriverModal()">Annuler</button>
            <button class="btn btn-primary" style="flex:1;justify-content:center;" id="bulkDriverSubmit" onclick="submitBulkDriver()">✅ Assigner les commandes</button>
        </div>
    </div>
</div>

{{-- ══ MODAL CHAT BOUTIQUE ↔ ENTREPRISE ══ --}}
<div class="chat-overlay" id="chatModal">
    <div class="chat-panel">

        {{-- Header --}}
        <div class="chat-hd">
            <div class="chat-hd-info">
                <div class="chat-hd-av" id="chatCompanyAv">🏢</div>
                <div>
                    <div class="chat-hd-name" id="chatCompanyName">Entreprise</div>
                    <div class="chat-hd-sub">Livraison · Discussion en cours</div>
                </div>
            </div>
            <button class="chat-close" onclick="closeChatModal()">✕</button>
        </div>

        {{-- Contexte commande --}}
        <div class="chat-order-ctx">
            <div class="chat-ctx-ico">📦</div>
            <div>
                <div class="chat-order-num" id="chatOrderNum">Commande #—</div>
                <div class="chat-order-client" id="chatClientName">Client —</div>
                <div class="chat-order-meta" id="chatClientMeta"></div>
            </div>
        </div>

        {{-- Messages --}}
        <div class="chat-msgs" id="chatMsgList">
            <div class="chat-empty" id="chatEmpty">Aucun message. Commencez la discussion !</div>
        </div>

        {{-- Zone selector + Bouton Confier --}}
        <div class="chat-confier-zone" id="chatConfierZone">

            {{-- Sélecteur de zone (affiché si l'entreprise a des zones) --}}
            <div id="zonePickerWrap" style="display:none;margin-bottom:8px;">
                <label style="font-size:11px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px;">📍 Zone de livraison</label>
                <input type="text" id="zoneSearch" placeholder="🔍 Rechercher une zone…" autocomplete="off"
                       oninput="filterZoneSelect(this,'zonePicker')"
                       style="width:100%;padding:7px 10px;border:1.5px solid #bbf7d0;border-radius:8px;font-size:12px;font-family:inherit;background:#fff;color:#0f172a;outline:none;margin-bottom:6px;box-sizing:border-box;">
                <select id="zonePicker"
                    style="width:100%;padding:9px 12px;border:1.5px solid #bbf7d0;border-radius:9px;font-size:13px;font-family:inherit;background:#fff;color:#0f172a;outline:none;cursor:pointer;"
                    onchange="onZonePick(this)">
                    <option value="">— Choisir une zone —</option>
                </select>
                <div id="zonePriceHint" style="display:none;margin-top:5px;padding:7px 10px;background:#f0fdf4;border-radius:7px;font-size:12px;font-weight:700;color:#065f46;">
                    💰 Prix : <span id="zonePriceVal"></span> · ⏱ <span id="zoneDelayVal"></span> min
                </div>
            </div>

            <button class="btn-confier" id="btnConfier" onclick="confierLivraison()">
                📦 Confier la livraison à cette entreprise
            </button>
        </div>

        {{-- Saisie --}}
        <div class="chat-input-zone">
            <textarea class="chat-textarea" id="chatInput" placeholder="Votre message…" rows="1"
                      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg();}"></textarea>
            <button class="chat-send-btn" id="chatSendBtn" onclick="sendMsg()" title="Envoyer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </div>

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

{{-- ══ MODAL NOTATION ENTREPRISE ══ --}}
<div class="rate-overlay" id="rateModal">
    <div class="rate-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <div style="font-size:15px;font-weight:800;color:var(--text);">⭐ Noter l'entreprise</div>
            <button onclick="closeRateModal()" style="background:var(--bg);border:1px solid var(--border);border-radius:8px;width:30px;height:30px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:var(--muted);">✕</button>
        </div>
        <div id="rateCompanyName" style="font-size:13px;font-weight:700;color:var(--text-2);margin-bottom:2px;"></div>
        <div id="rateOrderNum" style="font-size:11px;color:var(--brand);font-weight:700;"></div>
        <div class="rate-stars" id="rateStars">
            <button class="star-btn" data-v="1" onclick="setStar(1)">☆</button>
            <button class="star-btn" data-v="2" onclick="setStar(2)">☆</button>
            <button class="star-btn" data-v="3" onclick="setStar(3)">☆</button>
            <button class="star-btn" data-v="4" onclick="setStar(4)">☆</button>
            <button class="star-btn" data-v="5" onclick="setStar(5)">☆</button>
        </div>
        <div id="rateLabel" style="text-align:center;font-size:13.5px;font-weight:600;color:#d97706;min-height:22px;margin-bottom:10px;"></div>
        <label style="font-size:12px;font-weight:600;color:var(--muted);">Commentaire <span style="font-weight:400">(optionnel)</span></label>
        <textarea class="rate-comment" id="rateComment" placeholder="Dites-nous ce que vous avez pensé de ce service…"></textarea>
        <button class="btn-rate-submit" id="rateSubmitBtn" onclick="submitRating()" disabled>⭐ Envoyer l'avis</button>
    </div>
</div>

{{-- ══ LIGHTBOX PHOTO PRODUIT ══ --}}
<div class="lb-overlay" id="lbOverlay" onclick="closeLightbox()">
    <button class="lb-close" onclick="closeLightbox()">✕</button>
    <img class="lb-img" id="lbImg" src="" alt="">
    <div class="lb-caption" id="lbCaption"></div>
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

            {{-- ── BARRE D'ACTIONS GROUPÉES ── --}}
            <div class="bulk-bar" id="bulkBar">
                <span class="bulk-count" id="bulkCount">0 sélectionnée(s)</span>
                <button class="bulk-btn bulk-btn-driver" onclick="openBulkDriverModal()">🚴 Assigner à mes livreurs</button>
                <button class="bulk-btn bulk-btn-company" onclick="openBulkCompanyModalMode()">🏢 Assigner à une entreprise</button>
                <button class="bulk-btn bulk-btn-clear" onclick="clearBulkSelection()">✕ Effacer</button>
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
                <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
                <table class="tbl" style="min-width:720px;">
                    <thead><tr><th style="width:36px;"><input type="checkbox" id="selectAll" title="Tout sélectionner / désélectionner"></th><th>#</th><th>Client</th><th>Produit</th><th>Adresse</th><th>Montant</th><th>Statut</th><th>Livreur / Entreprise</th><th>Assigner</th><th>Actions</th></tr></thead>
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
                    <tr data-order-id="{{ $order->id }}">
                        <td style="width:36px;"><input type="checkbox" class="order-cb" value="{{ $order->id }}" onchange="onCbChange(this)"></td>
                        <td><span style="font-family:var(--mono);font-size:11px;color:var(--muted)">#{{ $order->id }}</span></td>
                        <td><div style="display:flex;align-items:center;gap:9px"><div class="c-av">{{ $init }}</div><div><div class="c-name">{{ $client->name ?? 'Inconnu' }}</div>@if($client?->phone)<div class="c-sub">📞 {{ $client->phone }}</div>@endif</div></div></td>
                        <td>
                            @if($product)<div style="display:flex;align-items:center;gap:8px">@if($product->image)<img src="{{ asset('storage/'.$product->image) }}" class="prod-img" alt="{{ $product->name }}" onclick="openLightbox('{{ asset('storage/'.$product->image) }}','{{ addslashes($product->name) }}')">@else<div class="prod-ph">🏷️</div>@endif<div><div style="font-size:12.5px;font-weight:600;color:var(--text)">{{ Str::limit($product->name,22) }}</div><div style="font-size:11px;color:var(--muted)">Qté : {{ $order->items->first()->quantity ?? 1 }}</div></div></div>
                            @else<span style="color:var(--muted);font-size:12px">—</span>@endif
                        </td>
                        <td>
                            @php $addr = $order->delivery_destination ?: ($client?->address ?? ''); @endphp
                            @if($addr)<div class="addr-cell" title="{{ $addr }}">📍 {{ $addr }}</div>@else<span class="addr-empty">—</span>@endif
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
                                        data-order-id="{{ $order->id }}"
                                        data-order-num="#{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}"
                                        data-client="{{ addslashes($client->name ?? 'Inconnu') }}"
                                        data-phone="{{ $client->phone ?? '' }}"
                                        data-dest="{{ addslashes($order->delivery_destination ?: ($client->address ?? '')) }}"
                                        data-shop-id="{{ $shop->id ?? '' }}"
                                        onclick="openCompanyModal(this)">
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
                            @if($order->status === 'livrée' && $order->deliveryCompany)
                                @if(!isset($reviewsByOrderId[$order->id]))
                                <button type="button" class="btn-noter"
                                    data-order-id="{{ $order->id }}"
                                    data-order-num="#{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}"
                                    data-company="{{ addslashes($order->deliveryCompany->name) }}"
                                    data-rate-url="{{ route('employe.orders.rate-company',$order) }}"
                                    onclick="openRateModal(this)">⭐ Noter</button>
                                @else
                                <span class="pill p-success" title="Noté {{ $reviewsByOrderId[$order->id]->rating }}/5">{{ $reviewsByOrderId[$order->id]->rating }}⭐</span>
                                @endif
                            @endif
                        </div></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>{{-- /overflow-x --}}
                <div class="pagination-wrap">{{ $orders->links() }}</div>
                @endif
            </div>

            {{-- CARTES MOBILE --}}
            <div class="mobile-list">
                {{-- Barre Tout sélectionner (mobile uniquement) --}}
                @if($orders->isNotEmpty())
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 4px 4px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
                        <input type="checkbox" id="selectAllMobile"
                               style="width:16px;height:16px;accent-color:var(--brand);cursor:pointer;flex-shrink:0;"
                               onchange="onSelectAllMobile(this)">
                        <span style="font-size:12.5px;font-weight:700;color:var(--text-2);">Tout sélectionner</span>
                    </label>
                    <span style="font-size:11px;color:var(--muted);">{{ $orders->count() }} commande(s)</span>
                </div>
                @endif
                @forelse($orders as $order)
                @php
                    $st=$statusMap[$order->status]??['label'=>ucfirst($order->status),'cls'=>'p-muted'];
                    $product=$order->items->first()?->product;
                    $client=$order->client;
                    $init=$client?initiales($client->name):'CL';
                    $peutAnnuler=in_array($order->status,$annulables);
                    $dejaAnnulee=in_array($order->status,$restaurables);
                @endphp
                <div class="m-card" data-order-id="{{ $order->id }}">
                    <div class="m-card-hd">
                        <div style="display:flex;align-items:center;gap:9px"><input type="checkbox" class="order-cb" value="{{ $order->id }}" onchange="onCbChange(this)" style="flex-shrink:0;"><div class="c-av">{{ $init }}</div><div><div class="c-name">{{ $client->name ?? 'Inconnu' }}</div>@if($client?->phone)<div class="c-sub">📞 {{ $client->phone }}</div>@endif</div></div>
                        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0"><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span><span style="font-family:var(--mono);font-size:10px;color:var(--muted)">#{{ $order->id }}</span></div>
                    </div>
                    <div class="m-card-body">
                        @if($product)<div style="display:flex;align-items:center;gap:9px">@if($product->image)<img src="{{ asset('storage/'.$product->image) }}" class="prod-img" alt="{{ $product->name }}" onclick="openLightbox('{{ asset('storage/'.$product->image) }}','{{ addslashes($product->name) }}')">@else<div class="prod-ph">🏷️</div>@endif<div><div style="font-size:13px;font-weight:600">{{ $product->name }}</div><div style="font-size:11px;color:var(--muted)">Qté : {{ $order->items->first()->quantity ?? 1 }}</div></div></div>@endif
                        @php $mAddr = $order->delivery_destination ?: ($client?->address ?? ''); @endphp
                        @if($mAddr)<div class="m-row"><span class="m-lbl">Adresse</span><span style="font-size:12px;color:var(--text-2);text-align:right;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $mAddr }}">📍 {{ $mAddr }}</span></div>@endif
                        <div class="m-row"><span class="m-lbl">Montant</span><span class="amount">{{ number_format($order->total,0,',',' ') }} <small>{{ $devise }}</small></span></div>
                        <div class="m-row"><span class="m-lbl">Assignation</span>@if($order->livreur)<div class="lv-chip"><div class="lv-chip-av">{{ initiales($order->livreur->name) }}</div>{{ $order->livreur->name }}</div>@elseif($order->deliveryCompany)<div class="lv-chip" style="background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;"><div class="lv-chip-av" style="background:#3b82f6;font-size:10px;">🏢</div>{{ $order->deliveryCompany->name }}</div>@else<span class="pill p-warning">Non assigné</span>@endif</div>
                        @if(!$order->livreur && !$order->deliveryCompany)
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            @if($livreurs->isNotEmpty())<form action="{{ route('employe.orders.assign',$order) }}" method="POST" style="display:flex;gap:8px;align-items:center">@csrf @method('PUT')<select name="livreur_id" class="assign-select" style="flex:1" required><option value="">— Choisir —</option>@foreach($livreurs as $lv)<option value="{{ $lv->id }}">{{ $lv->name }} {{ $lv->is_available ? '🟢' : '🟡' }}</option>@endforeach</select><button type="submit" class="btn btn-primary btn-sm">✅</button></form>@endif
                            <button type="button" class="btn btn-sm" style="background:#eef2ff;border-color:#c7d2fe;color:#4f46e5;width:100%;justify-content:center;"
                                    data-order-id="{{ $order->id }}"
                                    data-order-num="#{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}"
                                    data-client="{{ addslashes($client->name ?? 'Inconnu') }}"
                                    data-phone="{{ $client->phone ?? '' }}"
                                    data-dest="{{ addslashes($order->delivery_destination ?: ($client->address ?? '')) }}"
                                    data-shop-id="{{ $shop->id ?? '' }}"
                                    onclick="openCompanyModal(this)">🏢 Confier à une entreprise</button>
                        </div>
                        @endif
                    </div>
                    <div class="m-card-foot">
                        <a href="{{ route('orders.show',$order) }}" class="btn btn-info btn-sm">🔍 Détails</a>
                        @if($order->livreur || $order->deliveryCompany)<span class="btn btn-assigned btn-sm">✔ Assignée</span>@endif
                        @if($peutAnnuler)<button type="button" class="btn-cancel" style="flex:1;justify-content:center" onclick="openCancelModal('{{ route('employe.orders.cancel',$order) }}','#{{ $order->id }}','{{ addslashes($client->name ?? 'Inconnu') }}')">✕ Annuler</button>
                        @elseif($dejaAnnulee)<button type="button" class="btn-restore" style="flex:1;justify-content:center" onclick="openRestoreModal('{{ route('employe.orders.restore',$order) }}','#{{ $order->id }}','{{ addslashes($client->name ?? 'Inconnu') }}')">🔄 Restaurer</button>@endif
                        @if($order->status === 'livrée' && $order->deliveryCompany)
                            @if(!isset($reviewsByOrderId[$order->id]))
                            <button type="button" class="btn-noter" style="flex:1;justify-content:center;"
                                data-order-id="{{ $order->id }}"
                                data-order-num="#{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}"
                                data-company="{{ addslashes($order->deliveryCompany->name) }}"
                                data-rate-url="{{ route('employe.orders.rate-company',$order) }}"
                                onclick="openRateModal(this)">⭐ Noter l'entreprise</button>
                            @else
                            <span class="pill p-success" style="flex:1;justify-content:center;" title="Noté {{ $reviewsByOrderId[$order->id]->rating }}/5">{{ $reviewsByOrderId[$order->id]->rating }}⭐ Noté</span>
                            @endif
                        @endif
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
/* ════════════════════════════════════════════════
   MODAL ENTREPRISE + CHAT BOUTIQUE ↔ ENTREPRISE
   ════════════════════════════════════════════════ */

/* Contexte de la commande courante */
let _orderId   = null;
let _orderNum  = '';
let _clientName= '';
let _clientPhone= '';
let _dest      = '';
let _shopId    = '';

/* Polling */
let _chatInterval  = null;
let _lastMsgTime   = null;
let _currentCompanyId = null;
let _confierDone   = false;

/* ── Ouvre le modal de sélection d'entreprise ── */
function openCompanyModal(btn) {
    _orderId    = btn.dataset.orderId;
    _orderNum   = btn.dataset.orderNum  || ('#' + _orderId);
    _clientName = btn.dataset.client    || 'Client';
    _clientPhone= btn.dataset.phone     || '';
    _dest       = btn.dataset.dest      || '';
    _shopId     = btn.dataset.shopId    || '';

    /* Remplir le bloc contexte */
    document.getElementById('ctxOrderNum').textContent   = 'Commande ' + _orderNum;
    document.getElementById('ctxClientName').textContent = _clientName;
    document.getElementById('ctxClientPhone').textContent= _clientPhone ? '📞 ' + _clientPhone : '';
    document.getElementById('companyModalCtx').style.display = 'block';

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

/* ── Ouvre le chat avec une entreprise spécifique ── */
function openChatFromCompany(btn) {
    const companyId   = btn.dataset.companyId;
    const companyName = btn.dataset.companyName || 'Entreprise';
    const companyImg  = btn.dataset.companyImg  || '';

    _currentCompanyId = companyId;
    _lastMsgTime      = null;
    _confierDone      = false;
    loadCompanyZones(companyId);

    /* Mise à jour header */
    const av = document.getElementById('chatCompanyAv');
    if (companyImg) {
        av.innerHTML = `<img src="${companyImg}" style="width:100%;height:100%;object-fit:cover;" alt="">`;
    } else {
        av.textContent = '🏢';
    }
    document.getElementById('chatCompanyName').textContent = companyName;

    /* Bloc contexte commande */
    document.getElementById('chatOrderNum').textContent   = 'Commande ' + _orderNum;
    document.getElementById('chatClientName').textContent = _clientName;
    let meta = '';
    if (_clientPhone) meta += '📞 ' + _clientPhone;
    if (_dest)        meta += (meta ? '  ·  ' : '') + '📍 ' + _dest;
    document.getElementById('chatClientMeta').textContent = meta;

    /* Reset bouton confier */
    const btnC = document.getElementById('btnConfier');
    btnC.disabled  = false;
    btnC.classList.remove('done');
    btnC.textContent = '📦 Confier la livraison à cette entreprise';

    /* Vider messages */
    const list = document.getElementById('chatMsgList');
    list.innerHTML = '<div class="chat-empty" id="chatEmpty">Chargement…</div>';

    /* Fermer le modal entreprise, ouvrir le chat */
    closeCompanyModal();
    document.getElementById('chatModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('chatInput').focus();

    /* Charger messages + démarrer polling */
    loadMessages(true);
    _chatInterval = setInterval(() => loadMessages(false), 3000);
}

/* ── Ferme le chat ── */
function closeChatModal() {
    clearInterval(_chatInterval);
    _chatInterval = null;
    document.getElementById('chatModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('chatModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeChatModal();
});

/* ── Charge (ou poll) les messages ── */
function loadMessages(initial) {
    if (!_currentCompanyId || !_orderId) return;

    const url = new URL(`/employe/companies/${_currentCompanyId}/chat/messages`, location.origin);
    url.searchParams.set('shop_id', _shopId || '');
    if (_lastMsgTime && !initial) url.searchParams.set('after', _lastMsgTime);

    fetch(url.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const msgs = data.messages || data || [];
        if (msgs.length) {
            if (initial) {
                renderMessages(msgs, true);
            } else {
                renderMessages(msgs, false);
            }
            _lastMsgTime = msgs[msgs.length - 1].created_at || null;
                    /* Marque les messages vus pour ce fil — évite double notif sur dashboard */
                    try {
                        const lastId = msgs[msgs.length - 1].id;
                        const seen   = JSON.parse(sessionStorage.getItem('bq_co_seen') || '{}');
                        seen[String(_currentCompanyId)] = lastId;
                        sessionStorage.setItem('bq_co_seen', JSON.stringify(seen));
                    } catch(e) {}
        } else if (initial) {
            document.getElementById('chatMsgList').innerHTML =
                '<div class="chat-empty" id="chatEmpty">Aucun message. Commencez la discussion !</div>';
        }
    })
    .catch(() => {
        if (initial) {
            document.getElementById('chatMsgList').innerHTML =
                '<div class="chat-empty" id="chatEmpty">Aucun message. Commencez la discussion !</div>';
        }
    });
}

/* ── Affiche les messages ── */
function renderMessages(msgs, replace) {
    const list = document.getElementById('chatMsgList');

    if (replace) list.innerHTML = '';

    /* Supprimer l'état vide s'il est encore là */
    const empty = list.querySelector('.chat-empty');
    if (empty) empty.remove();

    msgs.forEach(m => {
        /* Évite les doublons lors du polling */
        if (document.getElementById('msg-' + m.id)) return;

        /* API retourne from_type (ou sender_role pour les locaux) */
        const role    = m.from_type || m.sender_role || 'shop';
        const isMine  = role === 'shop';
        const isSystem= role === 'system';

        const row = document.createElement('div');
        row.className = isSystem ? 'msg-row mine' : ('msg-row ' + (isMine ? 'mine' : 'theirs'));
        row.id = 'msg-' + m.id;

        const bubbleCls = isSystem ? 'msg-bubble system' : 'msg-bubble';
        /* API retourne body ; messages locaux utilisent message */
        const text    = m.body || m.message || '';
        const timeStr = m.created_at
            ? new Date(m.created_at).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'})
            : '';
        const label   = isSystem ? 'Système' : (isMine ? 'Vous' : 'Entreprise');

        row.innerHTML = `<div class="${bubbleCls}">${escapeHtml(text)}</div>`
                      + `<div class="msg-meta">${label} · ${timeStr}</div>`;
        list.appendChild(row);
    });

    /* Scroll bas */
    list.scrollTop = list.scrollHeight;
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}

/* ── Envoie un message ── */
function sendMsg() {
    const input = document.getElementById('chatInput');
    const msg   = input.value.trim();
    if (!msg || !_currentCompanyId) return;

    const sendBtn = document.getElementById('chatSendBtn');
    sendBtn.disabled = true;

    fetch(`/employe/companies/${_currentCompanyId}/chat/send`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            message: msg,
            shop_id: _shopId
        })
    })
    .then(r => r.json())
    .then(data => {
        input.value = '';
        input.style.height = '';
        sendBtn.disabled = false;
        /* Le contrôleur retourne { ok:true, message: { id, body, from_type, created_at } } */
        if (data.ok && data.message) {
            renderMessages([data.message], false);
            _lastMsgTime = data.message.created_at || null;
        } else {
            loadMessages(false);
        }
    })
    .catch(() => { sendBtn.disabled = false; });
}

/* ── Charge les zones d'une entreprise ── */
function loadCompanyZones(companyId) {
    const wrap    = document.getElementById('zonePickerWrap');
    const picker  = document.getElementById('zonePicker');
    const hint    = document.getElementById('zonePriceHint');

    wrap.style.display  = 'none';
    hint.style.display  = 'none';
    picker.innerHTML    = '<option value="">— Choisir une zone —</option>';
    const srch = document.getElementById('zoneSearch');
    if (srch) srch.value = '';

    if (!companyId) return;

    fetch(`/company-zones/${companyId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(zones => {
        if (!zones.length) return;
        zones.forEach(z => {
            const opt = document.createElement('option');
            opt.value        = z.id;
            opt.textContent  = z.name + ' — ' + new Intl.NumberFormat('fr-FR').format(z.price) + ' GNF';
            opt.dataset.price   = z.price;
            opt.dataset.minutes = z.estimated_minutes;
            picker.appendChild(opt);
        });
        wrap.style.display = 'block';
    })
    .catch(() => {});
}

function filterZoneSelect(input, selectId) {
    const q   = input.value.toLowerCase().trim();
    const sel = document.getElementById(selectId);
    if (!sel) return;
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        opt.hidden = q.length > 0 && !opt.text.toLowerCase().includes(q);
    });
    const cur = sel.options[sel.selectedIndex];
    if (cur && cur.value && cur.hidden) {
        sel.value = '';
        sel.dispatchEvent(new Event('change'));
    }
}

function onZonePick(sel) {
    const hint     = document.getElementById('zonePriceHint');
    const priceEl  = document.getElementById('zonePriceVal');
    const delayEl  = document.getElementById('zoneDelayVal');
    const opt      = sel.options[sel.selectedIndex];
    if (!sel.value) { hint.style.display = 'none'; return; }
    priceEl.textContent = new Intl.NumberFormat('fr-FR').format(opt.dataset.price) + ' GNF';
    delayEl.textContent = opt.dataset.minutes;
    hint.style.display  = 'block';
}

/* ── Confie la livraison à l'entreprise ── */
function confierLivraison() {
    if (!_orderId || !_currentCompanyId || _confierDone) return;

    const picker = document.getElementById('zonePicker');
    const zoneId = picker?.value || null;
    const zoneOpt = zoneId ? picker.options[picker.selectedIndex] : null;

    const btn = document.getElementById('btnConfier');
    btn.disabled = true;
    btn.textContent = '⏳ Envoi en cours…';

    /* FormData + _method=PUT pour le method spoofing Laravel */
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('delivery_company_id', _currentCompanyId);
    if (zoneId)                         formData.append('delivery_zone_id', zoneId);
    if (zoneOpt?.dataset?.price)        formData.append('delivery_fee', zoneOpt.dataset.price);

    fetch(`/employe/orders/${_orderId}/send-to-company`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            _confierDone = true;
            btn.classList.add('done');
            btn.textContent = '✅ Livraison confiée ! Statut : En attente';

            /* Message de confirmation local */
            renderMessages([{
                id: 'local-' + Date.now(),
                sender_role: 'system',
                message: '✅ Commande ' + _orderNum + ' confiée à cette entreprise. Statut : En attente.',
                created_at: new Date().toISOString()
            }], false);

            /* Recharger la page après 2s pour refléter le nouveau statut */
            setTimeout(() => location.reload(), 2000);
        } else {
            btn.disabled = false;
            btn.textContent = '📦 Confier la livraison à cette entreprise';
            alert(data.message || 'Erreur lors de la soumission.');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = '📦 Confier la livraison à cette entreprise';
        alert('Erreur réseau. Veuillez réessayer.');
    });
}

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
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal();
        closeRestoreModal();
        closeChatModal();
        closeCompanyModal();
        if (typeof closeRateModal === 'function') closeRateModal();
    }
});

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
               document.getElementById('restoreModal').classList.contains('open') ||
               document.getElementById('companyModal').classList.contains('open') ||
               document.getElementById('chatModal').classList.contains('open') ||
               document.getElementById('rateModal').classList.contains('open');
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

/* ════════════════════════════════════════
   MODAL NOTATION ENTREPRISE
   ════════════════════════════════════════ */
(function () {
    let _rateOrderId = null;
    let _rateUrl     = null;
    let _rateStar    = 0;
    const labels = ['','😞 Très mauvais','😕 Mauvais','😐 Correct','😊 Bien','🤩 Excellent !'];

    window.openRateModal = function(btn) {
        _rateOrderId = btn.dataset.orderId;
        _rateUrl     = btn.dataset.rateUrl;
        _rateStar    = 0;
        document.getElementById('rateCompanyName').textContent = '🏢 ' + (btn.dataset.company || 'Entreprise');
        document.getElementById('rateOrderNum').textContent    = 'Commande ' + (btn.dataset.orderNum || ('#' + _rateOrderId));
        document.getElementById('rateComment').value = '';
        document.getElementById('rateLabel').textContent = '';
        const submitBtn = document.getElementById('rateSubmitBtn');
        submitBtn.disabled  = true;
        submitBtn.textContent = '⭐ Envoyer l\'avis';
        setStar(0);
        document.getElementById('rateModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    window.closeRateModal = function() {
        document.getElementById('rateModal').classList.remove('open');
        document.body.style.overflow = '';
    };

    document.getElementById('rateModal')?.addEventListener('click', function(e) {
        if (e.target === this) window.closeRateModal();
    });

    window.setStar = function(n) {
        _rateStar = n;
        document.querySelectorAll('#rateStars .star-btn').forEach(b => {
            const v = parseInt(b.dataset.v);
            b.textContent = v <= n ? '⭐' : '☆';
            b.classList.toggle('lit', v <= n);
        });
        document.getElementById('rateLabel').textContent = labels[n] || '';
        document.getElementById('rateSubmitBtn').disabled = n === 0;
    };

    window.submitRating = async function() {
        if (!_rateStar || !_rateUrl) return;
        const btn = document.getElementById('rateSubmitBtn');
        btn.disabled    = true;
        btn.textContent = '⏳ Envoi…';

        try {
            const resp = await fetch(_rateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    rating:  _rateStar,
                    comment: document.getElementById('rateComment').value.trim() || null
                })
            });
            const data = await resp.json();
            if (data.success) {
                window.closeRateModal();
                /* Remplace tous les boutons de notation pour cette commande */
                document.querySelectorAll(`[data-rate-url][data-order-id="${_rateOrderId}"]`).forEach(b => {
                    const pill = document.createElement('span');
                    pill.className = 'pill p-success';
                    pill.title     = 'Noté ' + _rateStar + '/5';
                    pill.textContent = _rateStar + '⭐';
                    b.replaceWith(pill);
                });
            } else {
                btn.disabled    = false;
                btn.textContent = '⭐ Envoyer l\'avis';
                alert(data.error || 'Erreur lors de l\'envoi.');
            }
        } catch (e) {
            btn.disabled    = false;
            btn.textContent = '⭐ Envoyer l\'avis';
            alert('Erreur réseau. Veuillez réessayer.');
        }
    };
})();

/* ── LIGHTBOX PHOTO PRODUIT ── */
function openLightbox(src, name) {
    document.getElementById('lbImg').src       = src;
    document.getElementById('lbCaption').textContent = name || '';
    document.getElementById('lbOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lbOverlay').classList.remove('open');
    document.getElementById('lbImg').src = '';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeLightbox(); closeBulkDriverModal(); }
});

/* ═══════════════════════════════════════
   SÉLECTION GROUPÉE — BULK ASSIGN
═══════════════════════════════════════ */
const BULK_ASSIGN_URL = '{{ route('employe.orders.bulk-assign') }}';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

let _bulkIds = new Set();
let _bulkCompanyMode = false;

/* Met à jour la barre d'actions et les compteurs */
function updateBulkBar() {
    const n   = _bulkIds.size;
    const bar = document.getElementById('bulkBar');
    if (n > 0) {
        bar.classList.add('visible');
        document.getElementById('bulkCount').textContent =
            n + ' commande' + (n > 1 ? 's' : '') + ' sélectionnée' + (n > 1 ? 's' : '');
    } else {
        bar.classList.remove('visible');
    }
    const el = document.getElementById('bulkDriverCount');
    if (el) el.textContent = n;

    /* Sync indeterminate des deux selectAll (desktop + mobile) */
    const all     = document.querySelectorAll('.order-cb');
    const checked = document.querySelectorAll('.order-cb:checked');
    const indet   = checked.length > 0 && checked.length < all.length;
    const allChk  = all.length > 0 && checked.length === all.length;
    const sa  = document.getElementById('selectAll');
    const sam = document.getElementById('selectAllMobile');
    if (sa)  { sa.indeterminate = indet; sa.checked = allChk; }
    if (sam) { sam.indeterminate = indet; sam.checked = allChk; }
}

/* Callback sur chaque checkbox — fonctionne pour <tr> (desktop) et .m-card (mobile) */
function onCbChange(cb) {
    const row = cb.closest('tr') || cb.closest('.m-card');
    if (cb.checked) { _bulkIds.add(cb.value); row?.classList.add('row-selected'); }
    else            { _bulkIds.delete(cb.value); row?.classList.remove('row-selected'); }
    updateBulkBar();
}

/* Helper commun sélection totale */
function applySelectAll(checked) {
    document.querySelectorAll('.order-cb').forEach(cb => {
        cb.checked = checked;
        const row  = cb.closest('tr') || cb.closest('.m-card');
        if (checked) { _bulkIds.add(cb.value); row?.classList.add('row-selected'); }
        else         { _bulkIds.delete(cb.value); row?.classList.remove('row-selected'); }
    });
    /* Synchroniser les deux checkboxes */
    const sa  = document.getElementById('selectAll');
    const sam = document.getElementById('selectAllMobile');
    if (sa)  { sa.checked = checked; sa.indeterminate = false; }
    if (sam) { sam.checked = checked; sam.indeterminate = false; }
    updateBulkBar();
}

/* Tout sélectionner — table desktop */
document.getElementById('selectAll')?.addEventListener('change', function() {
    applySelectAll(this.checked);
});

/* Tout sélectionner — liste mobile */
function onSelectAllMobile(el) {
    applySelectAll(el.checked);
}

/* Effacer toute la sélection */
function clearBulkSelection() {
    document.querySelectorAll('.order-cb').forEach(cb => {
        cb.checked = false;
        (cb.closest('tr') || cb.closest('.m-card'))?.classList.remove('row-selected');
    });
    const sa  = document.getElementById('selectAll');
    const sam = document.getElementById('selectAllMobile');
    if (sa)  { sa.checked = false; sa.indeterminate = false; }
    if (sam) { sam.checked = false; sam.indeterminate = false; }
    _bulkIds.clear();
    updateBulkBar();
}

/* ── Toast ── */
function showBulkToast(msg, ok) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:22px;left:50%;transform:translateX(-50%);' +
        'padding:11px 22px;border-radius:10px;font-size:13px;font-weight:700;' +
        'z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.18);white-space:nowrap;' +
        (ok ? 'background:#059669;color:#fff;' : 'background:#dc2626;color:#fff;');
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3200);
}

/* ── MODAL LIVREUR BULK ── */
function openBulkDriverModal() {
    if (!_bulkIds.size) return;
    document.getElementById('bulkDriverCount').textContent = _bulkIds.size;
    document.querySelectorAll('input[name="bulk_livreur"]').forEach(r => { r.checked = false; });
    document.querySelectorAll('.blv-item').forEach(el => el.classList.remove('checked'));
    document.getElementById('bulkDriverModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeBulkDriverModal() {
    document.getElementById('bulkDriverModal').classList.remove('open');
    document.body.style.overflow = '';
}

async function submitBulkDriver() {
    const radio = document.querySelector('input[name="bulk_livreur"]:checked');
    if (!radio) { showBulkToast('Sélectionnez un livreur.', false); return; }

    const btn = document.getElementById('bulkDriverSubmit');
    btn.disabled = true; btn.textContent = '⏳ Assignation…';

    try {
        const res = await fetch(BULK_ASSIGN_URL, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ order_ids: [..._bulkIds], livreur_id: radio.value })
        });
        const data = await res.json();
        closeBulkDriverModal();
        if (data.success) {
            showBulkToast(`✅ ${data.assigned} commande(s) assignée(s) !`, true);
            clearBulkSelection();
            setTimeout(() => location.reload(), 1400);
        } else {
            showBulkToast(data.message || 'Erreur lors de l\'assignation.', false);
            btn.disabled = false; btn.textContent = '✅ Assigner les commandes';
        }
    } catch(e) {
        showBulkToast('Erreur réseau.', false);
        btn.disabled = false; btn.textContent = '✅ Assigner les commandes';
    }
}

/* ── MODE BULK dans le modal entreprise ── */
function openBulkCompanyModalMode() {
    if (!_bulkIds.size) return;
    _bulkCompanyMode = true;

    /* Contexte */
    const n = _bulkIds.size;
    document.getElementById('ctxOrderNum').textContent   = `📦 ${n} commande(s) sélectionnée(s)`;
    document.getElementById('ctxClientName').textContent = 'Choisissez une entreprise puis cliquez ✅ Confier';
    document.getElementById('ctxClientPhone').textContent = '';
    document.getElementById('companyModalCtx').style.display = 'block';

    /* Afficher boutons "Confier", cacher boutons chat */
    document.querySelectorAll('.bulk-show').forEach(el => el.style.display = '');
    document.querySelectorAll('.bulk-hide').forEach(el => el.style.display = 'none');

    document.getElementById('companyModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

/* Quand on ferme le modal entreprise : restaurer mode normal */
const _origCloseCompany = window.closeCompanyModal;
window.closeCompanyModal = function() {
    _bulkCompanyMode = false;
    document.querySelectorAll('.bulk-show').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.bulk-hide').forEach(el => el.style.display = '');
    _origCloseCompany?.();
};

async function submitBulkCompany(btn) {
    const companyId   = btn.dataset.companyId;
    const companyName = btn.dataset.companyName;
    const origText = btn.textContent;
    btn.disabled = true; btn.textContent = '⏳…';

    try {
        const res = await fetch(BULK_ASSIGN_URL, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ order_ids: [..._bulkIds], delivery_company_id: companyId })
        });
        const data = await res.json();
        window.closeCompanyModal();
        if (data.success) {
            showBulkToast(`✅ ${data.assigned} commande(s) confiée(s) à ${companyName} !`, true);
            clearBulkSelection();
            setTimeout(() => location.reload(), 1400);
        } else {
            showBulkToast(data.message || 'Erreur.', false);
            btn.disabled = false; btn.textContent = origText;
        }
    } catch(e) {
        showBulkToast('Erreur réseau.', false);
        btn.disabled = false; btn.textContent = origText;
    }
}
</script>
@endpush

@endsection