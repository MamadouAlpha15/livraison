@extends('layouts.app')
@section('title', 'Mes commandes · ' . $company->name)
@php $bodyClass = 'is-dashboard'; @endphp


@push('styles')
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

/* Masquer la navbar globale du layout */
body.is-dashboard > nav,
body.is-dashboard > header,
body.is-dashboard .navbar { display:none !important; }

body{font-family:'Segoe UI',system-ui,sans-serif;background:var(--cx-bg);color:var(--cx-text);margin:0;-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}

/* ══ STRUCTURE ══ */
.cx-wrap { display:flex; min-height:100vh; }

/* ══ SIDEBAR ══ */
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
.cx-sidebar a{color:#c7d2fe;transition:all .2s;}
.cx-sidebar a:hover{background:rgba(99,102,241,.15);color:#fff;}

/* Brand */
.cx-brand-hd{padding:14px 14px 10px;border-bottom:1px solid rgba(255,255,255,.06);flex-shrink:0;display:flex;flex-direction:column;gap:8px;}
.cx-brand-top{display:flex;align-items:center;justify-content:space-between;}
.cx-logo{display:flex;align-items:center;gap:9px;color:#fff;font-size:16px;font-weight:800;}
.cx-logo-icon{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#7c3aed,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.cx-sys-badge{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:600;color:var(--cx-green);padding:3px 8px;border-radius:20px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);}
.cx-sys-dot{width:6px;height:6px;border-radius:50%;background:var(--cx-green);animation:blink 2.2s ease-in-out infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}
.cx-close-btn{display:none;background:none;border:none;color:rgba(255,255,255,.45);font-size:18px;cursor:pointer;padding:2px 6px;border-radius:6px;line-height:1;transition:color .15s;}
.cx-close-btn:hover{color:#fff;}

/* Nav */
.cx-nav{padding:8px 8px 12px;flex:1;}
.cx-nav-sec{font-size:9px;font-weight:800;letter-spacing:1.8px;color:rgba(255,255,255,.3);padding:14px 10px 4px;text-transform:uppercase;}
.cx-nav-item{display:flex;align-items:center;gap:9px;padding:7px 10px;border-radius:var(--r-sm);color:rgba(255,255,255,.68);font-size:13px;font-weight:500;transition:background .14s,color .14s;position:relative;cursor:pointer;margin-bottom:1px;}
.cx-nav-item:hover{background:rgba(255,255,255,.06);color:#fff;}
.cx-nav-item.active{background:rgba(124,58,237,.22);color:#fff;font-weight:700;}
.cx-nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:18px;background:#8b5cf6;border-radius:0 3px 3px 0;box-shadow:2px 0 8px rgba(139,92,246,.5);}
.cx-nav-ico{width:24px;height:24px;border-radius:6px;background:rgba(255,255,255,.05);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}
.cx-nav-item.active .cx-nav-ico{background:rgba(139,92,246,.18);}

/* User foot */
.cx-user-foot{padding:10px 10px 12px;border-top:1px solid rgba(255,255,255,.07);flex-shrink:0;}
.cx-user-row{display:flex;align-items:center;gap:9px;padding:7px 8px;border-radius:var(--r-sm);background:rgba(255,255,255,.04);cursor:pointer;transition:background .15s;margin-bottom:6px;}
.cx-user-row:hover{background:rgba(255,255,255,.08);}
.cx-user-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4338ca);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;}
.cx-user-name{font-size:12px;font-weight:700;color:#fff;line-height:1.2;}
.cx-user-role{font-size:10px;color:var(--cx-text2);}
.cx-logout-btn{width:30px;height:30px;border-radius:8px;flex-shrink:0;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);display:flex;align-items:center;justify-content:center;color:#f87171;cursor:pointer;transition:background .15s,color .15s;padding:0;}
.cx-logout-btn:hover{background:rgba(239,68,68,.22);color:#fff;}

/* ══ OVERLAY ══ */
.cx-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1100;}
.cx-overlay.open{display:block;}

/* ══ MAIN ══ */
.cx-main{margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;flex:1;min-width:0;background:var(--cx-bg);}

/* ══ TOPBAR ══ */
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
.banner-inner{max-width:1280px;margin:0 auto;position:relative;z-index:1;}
.banner-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
.banner-title{font-size:22px;font-weight:900;color:#fff;letter-spacing:-.4px;display:flex;align-items:center;gap:10px;}
.banner-sub{font-size:13px;color:rgba(255,255,255,.55);margin-top:4px;}

/* Stats bar */
.stats-bar{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-top:22px;}
.stat-pill{
    background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);
    border-radius:var(--r-sm);padding:14px 16px;backdrop-filter:blur(4px);
    position:relative;overflow:hidden;
}
.stat-pill::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--s-accent,var(--cx-brand));}
.stat-pill-val{font-size:22px;font-weight:900;color:#fff;line-height:1;letter-spacing:-.5px;}
.stat-pill-lbl{font-size:10.5px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-top:4px;}

/* ── BODY ── */
.pw{max-width:1280px;margin:0 auto;padding:20px 24px 80px;}

/* Filter bar */
.filter-bar{
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
    background:var(--cx-card);border:1px solid var(--cx-border);
    border-radius:var(--r);padding:14px 16px;margin-bottom:16px;
}
.tab-btn{
    padding:7px 16px;border-radius:30px;font-size:12.5px;font-weight:700;
    border:1px solid transparent;cursor:pointer;transition:all .15s;
    background:transparent;color:var(--cx-text2);font-family:inherit;
}
.tab-btn:hover{background:var(--cx-card2);color:var(--cx-text);}
.tab-btn.active{background:var(--cx-brand);color:#fff;border-color:var(--cx-brand);}
.tab-sep{width:1px;height:22px;background:var(--cx-border);flex-shrink:0;}
.search-wrap{position:relative;flex:1;min-width:180px;}
.search-ico{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--cx-muted);pointer-events:none;}
.search-input{
    width:100%;padding:8px 12px 8px 34px;
    background:var(--cx-card2);border:1px solid var(--cx-border);
    border-radius:var(--r-xs);color:var(--cx-text);font-size:13px;font-family:inherit;outline:none;transition:border-color .15s;
}
.search-input::placeholder{color:var(--cx-muted);}
.search-input:focus{border-color:var(--cx-brand);}
.date-input{
    padding:8px 12px;background:var(--cx-card2);border:1px solid var(--cx-border);
    border-radius:var(--r-xs);color:var(--cx-text);font-size:13px;font-family:inherit;outline:none;transition:border-color .15s;
}
.date-input:focus{border-color:var(--cx-brand);}

/* Table card */
.table-card{background:var(--cx-card);border:1px solid var(--cx-border);border-radius:var(--r);overflow:hidden;}
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
thead th{
    padding:11px 16px;text-align:left;
    font-size:10.5px;font-weight:700;color:var(--cx-muted);
    text-transform:uppercase;letter-spacing:.6px;
    background:var(--cx-card2);border-bottom:2px solid var(--cx-border);white-space:nowrap;
    position:sticky;top:0;z-index:10;
}
tbody tr{border-bottom:1px solid var(--cx-border);transition:background .12s;}
tbody tr:last-child{border-bottom:none;}
tbody tr:hover{background:rgba(124,58,237,.05);}
tbody tr:nth-child(even){background:rgba(0,0,0,.018);}
tbody tr:nth-child(even):hover{background:rgba(124,58,237,.05);}
td{padding:13px 16px;font-size:13px;vertical-align:middle;}

.order-id{font-size:13px;font-weight:800;color:var(--cx-text);font-family:monospace;}
.order-items{font-size:11px;color:var(--cx-muted);margin-top:2px;}
.client-name{font-weight:700;color:var(--cx-text);}
.client-phone{font-size:11px;color:var(--cx-muted);}
.shop-badge{
    display:inline-flex;align-items:center;gap:5px;
    background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.2);
    color:#6d28d9;font-size:11.5px;font-weight:600;padding:3px 10px;border-radius:20px;
}
.driver-cell{display:flex;align-items:center;gap:8px;}
.driver-av{
    width:30px;height:30px;border-radius:7px;flex-shrink:0;overflow:hidden;
    background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2));
    display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;
}
.driver-av img{width:100%;height:100%;object-fit:cover;}
.driver-name{font-weight:700;color:var(--cx-text);font-size:12.5px;}
.driver-phone{font-size:11px;color:var(--cx-muted);}
.no-driver{color:var(--cx-muted);font-size:12px;font-style:italic;}
.amount-val{font-weight:800;color:var(--cx-text);font-family:monospace;font-size:13px;}
.fee-val{font-size:11px;color:#059669;font-weight:700;}

.badge{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;}
.badge-wait   {background:rgba(245,158,11,.1); color:#b45309;border:1px solid rgba(245,158,11,.25);}
.badge-confirm{background:rgba(59,130,246,.1); color:#1d4ed8;border:1px solid rgba(59,130,246,.25);}
.badge-deliv  {background:rgba(124,58,237,.1); color:#6d28d9;border:1px solid rgba(124,58,237,.25);}
.badge-done   {background:rgba(16,185,129,.1); color:#065f46;border:1px solid rgba(16,185,129,.25);}
.badge-cancel {background:rgba(239,68,68,.08); color:#b91c1c;border:1px solid rgba(239,68,68,.2);}

.btn-action{
    display:inline-flex;align-items:center;gap:5px;
    padding:6px 12px;border-radius:var(--r-xs);font-size:12px;font-weight:700;
    border:1px solid;cursor:pointer;font-family:inherit;transition:all .15s;white-space:nowrap;background:transparent;
}
.btn-assign{color:#a78bfa;border-color:rgba(139,92,246,.3);}
.btn-assign:hover{background:rgba(139,92,246,.15);}
.btn-done{color:#34d399;border-color:rgba(16,185,129,.3);}
.btn-done:hover{background:rgba(16,185,129,.1);}
.btn-status{color:var(--cx-text2);border-color:var(--cx-border);}
.btn-status:hover{background:var(--cx-card2);color:var(--cx-text);}

.empty-state{padding:64px 24px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:12px;}
.empty-ico{font-size:48px;opacity:.25;}
.empty-txt{font-size:15px;font-weight:700;color:var(--cx-text);}
.empty-sub{font-size:13px;color:var(--cx-muted);}

/* Pagination */
.pagination-wrap{padding:16px 20px;border-top:1px solid var(--cx-border);}
.pagination-wrap .pagination{display:flex;gap:4px;list-style:none;margin:0;padding:0;justify-content:center;}
.pagination-wrap .page-item .page-link{
    display:flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;
    background:var(--cx-card2);border:1px solid var(--cx-border);border-radius:var(--r-xs);
    color:var(--cx-text2);font-size:13px;font-weight:600;text-decoration:none;transition:all .15s;
}
.pagination-wrap .page-item.active .page-link{background:var(--cx-brand);border-color:var(--cx-brand);color:#fff;}
.pagination-wrap .page-item.disabled .page-link{opacity:.4;pointer-events:none;}

/* ── MODAL ── */
.modal-overlay{
    display:none;
    position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:1500;
    align-items:center;justify-content:center;padding:20px;
}
.modal-overlay.open{ display:flex; }
.cx-modal{
    background:var(--cx-card);border:1px solid var(--cx-border2);
    border-radius:var(--r);width:100%;max-width:520px;max-height:90vh;
    overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.5);
    transform:translateY(16px);transition:transform .2s;
}
.modal-overlay.open .cx-modal{transform:translateY(0);}
.cx-modal-hd{
    padding:18px 22px;border-bottom:1px solid var(--cx-border);
    display:flex;align-items:flex-start;justify-content:space-between;gap:12px;
    position:sticky;top:0;background:var(--cx-card);z-index:1;
}
.cx-modal-title{font-size:15px;font-weight:800;color:var(--cx-text);}
.cx-modal-sub{font-size:12px;color:var(--cx-muted);margin-top:3px;}
.cx-modal-close{
    background:var(--cx-card2);border:1px solid var(--cx-border);border-radius:var(--r-xs);
    width:30px;height:30px;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    color:var(--cx-text2);cursor:pointer;font-size:16px;transition:all .14s;
}
.cx-modal-close:hover{background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.25);color:#dc2626;}
.cx-modal-body{padding:20px 22px;}

.driver-radio{display:flex;flex-direction:column;gap:8px;max-height:280px;overflow-y:auto;
    scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.25) transparent;margin-bottom:4px;}
.driver-radio::-webkit-scrollbar{width:3px;}
.driver-radio::-webkit-scrollbar-thumb{background:rgba(124,58,237,.3);border-radius:3px;}
.driver-opt{
    display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:var(--r-sm);
    border:1.5px solid var(--cx-border);cursor:pointer;transition:all .14s;background:var(--cx-card2);
}
.driver-opt:hover{border-color:rgba(124,58,237,.4);background:rgba(124,58,237,.04);}
.driver-opt input[type=radio]{display:none;}
.driver-opt.selected{border-color:var(--cx-brand);background:rgba(124,58,237,.08);
    box-shadow:0 0 0 3px rgba(124,58,237,.1);}
.driver-opt.not-avail{opacity:.5;cursor:default;}
.driver-opt.not-avail:hover{border-color:var(--cx-border);background:var(--cx-card2);}
.d-av-lg{
    width:40px;height:40px;border-radius:var(--r-xs);flex-shrink:0;overflow:hidden;
    background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2));
    display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;
}
.d-av-lg img{width:100%;height:100%;object-fit:cover;}
.d-name{font-size:13px;font-weight:700;color:var(--cx-text);}
.d-phone{font-size:11.5px;color:var(--cx-muted);margin-top:1px;}
.d-status-badge{margin-left:auto;font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap;flex-shrink:0;}
.ds-avail{background:rgba(16,185,129,.12);color:#065f46;border:1px solid rgba(16,185,129,.3);}
.ds-busy {background:rgba(245,158,11,.1);color:#92400e;border:1px solid rgba(245,158,11,.25);}
.ds-off  {background:rgba(100,116,139,.08);color:#6b7280;border:1px solid rgba(0,0,0,.1);}
.avail-section-lbl{
    font-size:10px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;
    color:var(--cx-muted);padding:8px 2px 4px;
}
.no-avail-warn{
    padding:11px 14px;border-radius:var(--r-sm);margin-bottom:14px;
    background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.22);
    color:#92400e;font-size:12.5px;font-weight:600;display:flex;align-items:center;gap:8px;
}
.selected-summary{
    display:none;padding:11px 14px;border-radius:var(--r-sm);margin-bottom:14px;
    background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.25);
    color:#065f46;font-size:12.5px;font-weight:700;align-items:center;gap:8px;
}
.selected-summary.show{display:flex;}

/* ── Blocs adresses dans le modal ── */
.addr-flow{display:flex;align-items:stretch;gap:10px;margin-bottom:18px;}
.addr-box{flex:1;padding:12px 14px;border-radius:var(--r-sm);border:1.5px solid;min-width:0;}
.addr-pickup {background:rgba(59,130,246,.06);border-color:rgba(59,130,246,.3);}
.addr-delivery{background:rgba(16,185,129,.06);border-color:rgba(16,185,129,.3);}
.addr-arrow{display:flex;align-items:center;color:var(--cx-muted);font-size:20px;flex-shrink:0;padding-top:4px;}
.addr-lbl{font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.9px;margin-bottom:5px;}
.addr-pickup  .addr-lbl{color:#1d4ed8;}
.addr-delivery .addr-lbl{color:#065f46;}
.addr-val{font-size:12.5px;font-weight:600;color:var(--cx-text);line-height:1.45;word-break:break-word;}
.addr-val.addr-empty{color:var(--cx-muted);font-style:italic;font-weight:400;}
.form-group{margin-bottom:14px;}
.form-label{display:block;font-size:11.5px;font-weight:700;color:var(--cx-text2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;}
.form-input{
    width:100%;padding:10px 14px;background:var(--cx-card2);border:1.5px solid var(--cx-border);
    border-radius:var(--r-sm);color:var(--cx-text);font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s;
}
.form-input:focus{border-color:var(--cx-brand);box-shadow:0 0 0 3px rgba(124,58,237,.12);}
.form-input::placeholder{color:var(--cx-muted);}
.btn-primary{
    width:100%;padding:13px;background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2));
    color:#fff;border:none;border-radius:var(--r-sm);font-size:14px;font-weight:800;font-family:inherit;
    cursor:pointer;box-shadow:0 4px 14px rgba(124,58,237,.35);transition:all .18s;
    display:flex;align-items:center;justify-content:center;gap:8px;
}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(124,58,237,.5);}
.btn-primary:disabled{opacity:.6;cursor:not-allowed;transform:none;}

.status-radio{display:flex;flex-direction:column;gap:8px;}
.status-opt{
    display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:var(--r-sm);
    border:1.5px solid var(--cx-border);cursor:pointer;transition:border-color .14s;background:var(--cx-card2);
}
.status-opt:hover{border-color:var(--cx-border2);}
.status-opt input[type=radio]{display:none;}
.status-opt.selected{border-color:var(--cx-brand);}

.toast{
    position:fixed;bottom:24px;right:24px;z-index:9999;
    padding:12px 20px;border-radius:var(--r-sm);font-size:13.5px;font-weight:700;color:#fff;
    box-shadow:0 8px 24px rgba(0,0,0,.4);transform:translateY(20px);opacity:0;
    transition:all .25s;pointer-events:none;
}
.toast.show{transform:translateY(0);opacity:1;}
.toast-success{background:linear-gradient(135deg,#059669,#10b981);}
.toast-error{background:linear-gradient(135deg,#dc2626,#ef4444);}

@media(max-width:1024px){
    .stats-bar{grid-template-columns:repeat(3,1fr);}
    .cx-sidebar{transform:translateX(-100%);}
    .cx-sidebar.open{transform:translateX(0);}
    .cx-main{margin-left:0;}
    .cx-hamburger{display:flex;}
    .cx-close-btn{display:block;}
}
@media(max-width:768px){
    .stats-bar{grid-template-columns:1fr 1fr 1fr;}
    .cx-topbar{padding:0 14px;}
    .cx-tb-urole{display:none;}
    .cx-tb-uname{font-size:11.5px;}
}
@media(max-width:640px){
    .pw{padding:12px 12px 60px;}
    .stats-bar{grid-template-columns:1fr 1fr;}
    .page-banner{padding:18px 14px 16px;}
    .filter-bar{flex-direction:column;align-items:stretch;}
}
@media(max-width:480px){
    .stats-bar{grid-template-columns:1fr 1fr;}
    .cx-topbar-title span{display:none;}
}
</style>
@endpush

@section('content')
@php
    $statusMap = [
        'en_attente'   => ['lbl'=>'En attente',   'cls'=>'badge-wait'],
        'confirmée'    => ['lbl'=>'Confirmée',    'cls'=>'badge-confirm'],
        'en_livraison' => ['lbl'=>'En livraison', 'cls'=>'badge-deliv'],
        'livrée'       => ['lbl'=>'Livrée',       'cls'=>'badge-done'],
        'annulée'      => ['lbl'=>'Annulée',      'cls'=>'badge-cancel'],
    ];
    $fmt = fn($n) => number_format($n ?? 0, 0, ',', ' ') . ' GNF';
@endphp

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
                <div class="cx-logo-icon">🚚</div>
                ShipXpress
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
        <a href="{{ route('company.orders.index') }}" class="cx-nav-item active">
            <span class="cx-nav-ico">📦</span> Commandes
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">🚚</span> Livraisons
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">🗺️</span> Carte en direct
        </a>
        <a href="{{ route('company.drivers.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">🚴</span> Chauffeurs
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">🏪</span> Boutiques
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">👥</span> Clients
        </a>

        <div class="cx-nav-sec">Gestion</div>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">📍</span> Zone de livraison
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">💲</span> Tarification
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">🔔</span> Notifications
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">📊</span> Rapports
        </a>

        <div class="cx-nav-sec">Configuration</div>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">⚙️</span> Paramètres
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">👤</span> Utilisateurs
        </a>
        <a href="#" class="cx-nav-item">
            <span class="cx-nav-ico">🔌</span> Intégrations
        </a>
    </nav>

    <div class="cx-user-foot">
        <div class="cx-user-row">
            <div class="cx-user-av">{{ $ini }}</div>
            <div style="flex:1;min-width:0">
                <div class="cx-user-name">{{ Str::limit($u->name ?? 'Admin', 18) }}</div>
                <div class="cx-user-role">Super Administrateur</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="flex-shrink:0">
                @csrf
                <button type="submit" class="cx-logout-btn" title="Se deconnecter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="cx-overlay" id="cxOverlay"></div>

{{-- ══ MAIN ══ --}}
<main class="cx-main">

    {{-- TOPBAR --}}
    <div class="cx-topbar">
        <button class="cx-hamburger" id="cxHamburger">☰</button>
        <div class="cx-topbar-title">📦 <span>Commandes</span></div>
        <div class="cx-tb-right">
            <a href="{{ route('company.chat.inbox') }}" class="cx-tb-btn" title="Chat">💬</a>
            <div class="cx-tb-user">
                <div class="cx-tb-av">{{ $ini }}</div>
                <div>
                    <div class="cx-tb-uname">{{ Str::limit($u->name ?? 'Admin', 16) }}</div>
                    <div class="cx-tb-urole">Super Admin</div>
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
                <div class="banner-title">📦 Mes commandes</div>
                <div class="banner-sub">{{ $company->name }} · Commandes assignées à votre entreprise</div>
            </div>
        </div>
        <div class="stats-bar">
            <div class="stat-pill" style="--s-accent:#a78bfa">
                <div class="stat-pill-val">{{ $stats['total'] }}</div>
                <div class="stat-pill-lbl">Total</div>
            </div>
            <div class="stat-pill" style="--s-accent:#fbbf24">
                <div class="stat-pill-val">{{ $stats['en_attente'] }}</div>
                <div class="stat-pill-lbl">En attente</div>
            </div>
            <div class="stat-pill" style="--s-accent:#a78bfa">
                <div class="stat-pill-val">{{ $stats['en_livraison'] }}</div>
                <div class="stat-pill-lbl">En livraison</div>
            </div>
            <div class="stat-pill" style="--s-accent:#34d399">
                <div class="stat-pill-val">{{ $stats['livrees_today'] }}</div>
                <div class="stat-pill-lbl">Livrées auj.</div>
            </div>
            <div class="stat-pill" style="--s-accent:#34d399">
                <div class="stat-pill-val" style="font-size:15px;">{{ $fmt($stats['revenus_today']) }}</div>
                <div class="stat-pill-lbl">Revenus auj.</div>
            </div>
        </div>
    </div>
</div>

<div class="pw">

    {{-- FILTER BAR --}}
    <form method="GET" action="{{ route('company.orders.index') }}" id="filterForm">
        <div class="filter-bar">
            @php
                $tabs = ['all'=>'Toutes','en_attente'=>'En attente','confirmée'=>'Confirmées','en_livraison'=>'En livraison','livrée'=>'Livrées','annulée'=>'Annulées'];
                $cur  = request('status','all');
            @endphp
            @foreach($tabs as $val => $lbl)
            <button type="button" class="tab-btn {{ $cur===$val?'active':'' }}" onclick="setStatus('{{ $val }}')">{{ $lbl }}</button>
            @endforeach
            <div class="tab-sep"></div>
            <div class="search-wrap">
                <svg class="search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="search-input" type="text" name="search" id="searchInput"
                       placeholder="Client, boutique, destination…" value="{{ request('search') }}">
            </div>
            <input class="date-input" type="date" name="date" value="{{ request('date') }}"
                   onchange="document.getElementById('filterForm').submit()">
            <input type="hidden" name="status" id="statusInput" value="{{ $cur }}">
        </div>
    </form>

    {{-- TABLE --}}
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Boutique</th>
                        <th>Destination</th>
                        <th>Chauffeur</th>
                        <th>Frais liv.</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                @php $st = $statusMap[$order->status] ?? ['lbl'=>$order->status,'cls'=>'badge-wait']; @endphp
                <tr>
                    <td>
                        <div class="order-id">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                        <div class="order-items">{{ $order->items->count() }} article(s)</div>
                    </td>
                    <td>
                        <div class="client-name">{{ $order->client->name ?? '—' }}</div>
                        <div class="client-phone">{{ $order->client->phone ?? '' }}</div>
                    </td>
                    <td><span class="shop-badge">🏪 {{ $order->shop->name ?? '—' }}</span></td>
                    <td style="color:var(--cx-text2);font-size:12.5px;max-width:160px;">
                        @php $dest = $order->delivery_destination ?: ($order->client->address ?? null); @endphp
                        @if($dest)
                            <div style="font-size:12px;line-height:1.4;">{{ $dest }}</div>
                        @else
                            <span style="color:var(--cx-muted);font-style:italic;font-size:11.5px;">Non renseignée</span>
                        @endif
                    </td>
                    <td>
                        @if($order->driver)
                            <div class="driver-cell">
                                <div class="driver-av">
                                    @if($order->driver->photo)
                                        <img src="{{ asset('storage/'.$order->driver->photo) }}" alt="">
                                    @else
                                        {{ strtoupper(substr($order->driver->name,0,2)) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="driver-name">{{ $order->driver->name }}</div>
                                    <div class="driver-phone">{{ $order->driver->phone ?? '' }}</div>
                                </div>
                            </div>
                        @else
                            <span class="no-driver">Non assigné</span>
                        @endif
                    </td>
                    <td><div class="fee-val">{{ $order->delivery_fee ? $fmt($order->delivery_fee) : '—' }}</div></td>
                    <td><div class="amount-val">{{ $fmt($order->total) }}</div></td>
                    <td><span class="badge {{ $st['cls'] }}">{{ $st['lbl'] }}</span></td>
                    <td style="color:var(--cx-muted);font-size:12px;white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/y H:i') }}
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            @if(in_array($order->status, ['en_attente','confirmée']))
                            <button class="btn-action btn-assign"
                                    data-id="{{ $order->id }}"
                                    data-fee="{{ $order->delivery_fee ?? '' }}"
                                    data-dest="{{ $order->delivery_destination ?? '' }}"
                                    data-num="{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}"
                                    data-shop="{{ $order->shop->name ?? '' }}"
                                    data-shop-addr="{{ $order->shop->address ?? '' }}"
                                    data-client-addr="{{ $order->client->address ?? '' }}"
                                    onclick="openAssign(this)">
                                🚴 Assigner
                            </button>
                            @endif
                            @if($order->status === 'en_livraison')
                            <button class="btn-action btn-done"
                                    data-id="{{ $order->id }}"
                                    onclick="markDone(this)">✅ Livrée</button>
                            @endif
                            @if(!in_array($order->status, ['livrée','annulée']))
                            <button class="btn-action btn-status"
                                    data-id="{{ $order->id }}"
                                    data-status="{{ $order->status }}"
                                    onclick="openStatus(this)">✏️ Statut</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10">
                    <div class="empty-state">
                        <div class="empty-ico">📦</div>
                        <div class="empty-txt">Aucune commande assignée à votre entreprise</div>
                        <div class="empty-sub">Les boutiques vous confient leurs livraisons depuis leur tableau de bord.</div>
                    </div>
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="pagination-wrap">{{ $orders->links() }}</div>
        @endif
    </div>
</div>

{{-- ═══ MODAL ASSIGNER CHAUFFEUR ═══ --}}
@php
    $availCount  = $drivers->where('status','available')->count();
    $availDrivers = $drivers->where('status','available');
    $otherDrivers = $drivers->where('status','!=','available');
@endphp
<div class="modal-overlay" id="assignModal">
    <div class="cx-modal">
        <div class="cx-modal-hd">
            <div>
                <div class="cx-modal-title">🚴 Assigner un chauffeur</div>
                <div class="cx-modal-sub" id="assignModalSub">Sélectionnez un chauffeur disponible</div>
            </div>
            <button class="cx-modal-close" data-close="assignModal">✕</button>
        </div>
        <div class="cx-modal-body">

            {{-- ── Blocs adresses ── --}}
            <div class="addr-flow">
                <div class="addr-box addr-pickup">
                    <div class="addr-lbl">📦 Retrait — Boutique</div>
                    <div class="addr-val" id="shopAddrVal">—</div>
                </div>
                <div class="addr-arrow">→</div>
                <div class="addr-box addr-delivery">
                    <div class="addr-lbl">📍 Livraison — Client</div>
                    <div class="addr-val" id="clientAddrVal">—</div>
                </div>
            </div>

            {{-- Avertissement si aucun chauffeur disponible --}}
            @if($availCount === 0)
            <div class="no-avail-warn">
                ⚠️ Aucun chauffeur disponible en ce moment
            </div>
            @endif

            {{-- Liste chauffeurs --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <div class="form-label" style="margin:0">Chauffeur</div>
                @if($availCount > 0)
                <span class="d-status-badge ds-avail">● {{ $availCount }} disponible{{ $availCount>1?'s':'' }}</span>
                @endif
            </div>

            <div class="driver-radio" id="driverList">
                {{-- Disponibles en premier --}}
                @foreach($availDrivers as $driver)
                <label class="driver-opt" data-driver-id="{{ $driver->id }}" data-driver-name="{{ $driver->name }}" onclick="selectDriver(this, {{ $driver->id }})">
                    <input type="radio" name="driver_id" value="{{ $driver->id }}">
                    <div class="d-av-lg">
                        @if($driver->photo)<img src="{{ asset('storage/'.$driver->photo) }}" alt="">
                        @else{{ strtoupper(substr($driver->name,0,2)) }}@endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="d-name">{{ $driver->name }}</div>
                        <div class="d-phone">{{ $driver->phone ?? 'Aucun numéro' }}</div>
                    </div>
                    <span class="d-status-badge ds-avail">● Disponible</span>
                </label>
                @endforeach

                {{-- Séparateur si d'autres chauffeurs existent --}}
                @if($availCount > 0 && $otherDrivers->count() > 0)
                <div class="avail-section-lbl">— Non disponibles</div>
                @endif

                {{-- Busy + offline (non sélectionnables) --}}
                @foreach($otherDrivers as $driver)
                @php
                    $dCls = $driver->status === 'busy' ? 'ds-busy' : 'ds-off';
                    $dLbl = $driver->status === 'busy' ? '◉ En mission' : '○ Hors ligne';
                @endphp
                <label class="driver-opt not-avail" title="{{ $driver->status === 'busy' ? 'En mission' : 'Hors ligne' }}">
                    <input type="radio" name="driver_id" value="{{ $driver->id }}" disabled>
                    <div class="d-av-lg" style="filter:grayscale(.4)">
                        @if($driver->photo)<img src="{{ asset('storage/'.$driver->photo) }}" alt="">
                        @else{{ strtoupper(substr($driver->name,0,2)) }}@endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="d-name">{{ $driver->name }}</div>
                        <div class="d-phone">{{ $driver->phone ?? 'Aucun numéro' }}</div>
                    </div>
                    <span class="d-status-badge {{ $dCls }}">{{ $dLbl }}</span>
                </label>
                @endforeach

                @if($drivers->isEmpty())
                <div style="text-align:center;padding:24px;color:var(--cx-muted);font-size:13px;">
                    Aucun chauffeur. Ajoutez-en depuis le dashboard.
                </div>
                @endif
            </div>

            {{-- Résumé chauffeur sélectionné --}}
            <div class="selected-summary" id="selectedSummary">
                ✓ <span id="selectedDriverName"></span> sélectionné
            </div>

            <div class="form-group">
                <label class="form-label">💰 Frais de livraison (GNF)</label>
                <input type="number" class="form-input" id="assignFee" placeholder="Ex: 50 000" min="0" step="500">
            </div>
            <div class="form-group">
                <label class="form-label">📍 Adresse de livraison (modifiable)</label>
                <input type="text" class="form-input" id="assignDest" placeholder="Adresse du client…">
                <div style="font-size:11px;color:var(--cx-muted);margin-top:4px;">Pré-remplie avec l'adresse du client. Modifiez si besoin.</div>
            </div>
            <button class="btn-primary" id="assignBtn" onclick="submitAssign()">
                🚴 Confirmer l'assignation
            </button>
        </div>
    </div>
</div>

{{-- ═══ MODAL STATUT ═══ --}}
<div class="modal-overlay" id="statusModal">
    <div class="cx-modal">
        <div class="cx-modal-hd">
            <div class="cx-modal-title">✏️ Changer le statut</div>
            <button class="cx-modal-close" data-close="statusModal">✕</button>
        </div>
        <div class="cx-modal-body">
            <div class="form-group">
                <div class="form-label">Nouveau statut</div>
                <div class="status-radio" id="statusList">
                    @foreach(['en_livraison'=>['🚴','En livraison','badge-deliv'],'livrée'=>['✅','Livrée','badge-done'],'annulée'=>['❌','Annulée','badge-cancel']] as $val=>[$ico,$lbl,$cls])
                    <label class="status-opt">
                        <input type="radio" name="status_val" value="{{ $val }}">
                        <span class="badge {{ $cls }}">{{ $ico }} {{ $lbl }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <button class="btn-primary" id="statusBtn" onclick="submitStatus()">Enregistrer</button>
        </div>
    </div>
</div>

</main>{{-- /cx-main --}}
</div>{{-- /cx-wrap --}}

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script>
/* ── Sidebar toggle ── */
(function(){
    const sidebar  = document.getElementById('cxSidebar');
    const overlay  = document.getElementById('cxOverlay');
    const open = () => { sidebar.classList.add('open'); overlay.classList.add('open'); };
    const close= () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); };
    document.getElementById('cxHamburger')?.addEventListener('click', open);
    document.getElementById('cxClose')?.addEventListener('click', close);
    overlay?.addEventListener('click', close);
    document.addEventListener('keydown', e => { if(e.key==='Escape') close(); });
})();

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ── Filtre ── */
function setStatus(v){ document.getElementById('statusInput').value=v; document.getElementById('filterForm').submit(); }
var _st;
document.getElementById('searchInput').addEventListener('input',function(){ clearTimeout(_st); _st=setTimeout(function(){ document.getElementById('filterForm').submit(); },500); });

/* ── État global ── */
var currentOrderId=null, selectedDriverId=null, selectedStatusVal=null;

/* ── Modals ── */
function openModal(id){ document.getElementById(id).classList.add('open'); }
function closeModal(id){
    document.getElementById(id).classList.remove('open');
    currentOrderId=null; selectedDriverId=null; selectedStatusVal=null;
    document.querySelectorAll('.driver-opt,.status-opt').forEach(function(el){ el.classList.remove('selected'); });
    var s=document.getElementById('selectedSummary'); if(s) s.classList.remove('show');
}
document.querySelectorAll('.modal-overlay').forEach(function(el){
    el.addEventListener('click',function(e){ if(e.target===el) closeModal(el.id); });
});
document.querySelectorAll('[data-close]').forEach(function(btn){
    btn.addEventListener('click',function(){ closeModal(btn.getAttribute('data-close')); });
});
document.addEventListener('keydown',function(e){ if(e.key==='Escape'){ closeModal('assignModal'); closeModal('statusModal'); } });

/* ── Assigner : ouverture du modal ── */
function openAssign(btn){
    var orderId    = btn.getAttribute('data-id');
    var fee        = btn.getAttribute('data-fee');
    var dest       = btn.getAttribute('data-dest');
    var orderNum   = btn.getAttribute('data-num');
    var shopName   = btn.getAttribute('data-shop');
    var shopAddr   = btn.getAttribute('data-shop-addr');
    var clientAddr = btn.getAttribute('data-client-addr');

    currentOrderId=orderId;
    selectedDriverId=null;
    document.querySelectorAll('.driver-opt').forEach(function(el){ el.classList.remove('selected'); });

    /* Blocs adresses */
    var shopEl   = document.getElementById('shopAddrVal');
    var clientEl = document.getElementById('clientAddrVal');
    if(shopAddr){
        shopEl.textContent = shopAddr;
        shopEl.className = 'addr-val';
    } else {
        shopEl.textContent = 'Adresse boutique non renseignée';
        shopEl.className = 'addr-val addr-empty';
    }
    /* Adresse de livraison = destination déjà sauvegardée, sinon adresse du client */
    var deliveryAddr = dest || clientAddr || '';
    if(deliveryAddr){
        clientEl.textContent = deliveryAddr;
        clientEl.className = 'addr-val';
    } else {
        clientEl.textContent = 'Adresse client non renseignée';
        clientEl.className = 'addr-val addr-empty';
    }

    /* Champ destination pré-rempli */
    document.getElementById('assignFee').value = (fee && parseFloat(fee) > 0) ? fee : '';
    document.getElementById('assignDest').value = deliveryAddr;

    var s=document.getElementById('selectedSummary'); s.classList.remove('show');
    document.getElementById('selectedDriverName').textContent='';

    var sub = orderNum ? 'Commande #'+orderNum : '';
    if(shopName) sub += (sub?' · ':'')+shopName;
    document.getElementById('assignModalSub').textContent = sub || 'Sélectionnez un chauffeur disponible';

    /* Auto-sélectionner le 1er disponible */
    var first=document.querySelector('#driverList .driver-opt:not(.not-avail)');
    if(first) selectDriver(first, first.getAttribute('data-driver-id'));
    openModal('assignModal');
}

/* ── Sélection d'un chauffeur ── */
function selectDriver(el, id){
    document.querySelectorAll('.driver-opt').forEach(function(e){ e.classList.remove('selected'); });
    el.classList.add('selected');
    selectedDriverId=id;
    var radio=el.querySelector('input[type=radio]'); if(radio) radio.checked=true;
    var name=el.getAttribute('data-driver-name') || (el.querySelector('.d-name')||{}).textContent || '';
    document.getElementById('selectedDriverName').textContent=name.trim();
    document.getElementById('selectedSummary').classList.add('show');
}

/* Clic sur un chauffeur dans le modal */
document.querySelectorAll('#driverList .driver-opt:not(.not-avail)').forEach(function(opt){
    opt.addEventListener('click',function(){ selectDriver(this, this.getAttribute('data-driver-id')); });
});

/* ── Soumettre l'assignation ── */
function submitAssign(){
    if(!currentOrderId){ toast('Aucune commande.','error'); return; }
    if(!selectedDriverId){ toast('Choisissez un chauffeur disponible.','error'); return; }
    var fee=document.getElementById('assignFee').value.trim();
    if(fee===''||isNaN(Number(fee))||Number(fee)<0){ toast('Entrez des frais valides (0 ou plus).','error'); return; }
    var btn=document.getElementById('assignBtn');
    btn.disabled=true; btn.textContent='⏳ En cours…';
    var fd=new FormData();
    fd.append('_token',CSRF);
    fd.append('driver_id',selectedDriverId);
    fd.append('delivery_fee',fee);
    fd.append('delivery_destination',document.getElementById('assignDest').value);
    fetch('/company/orders/'+currentOrderId+'/assign',{method:'POST',body:fd})
        .then(function(r){ return r.json(); })
        .then(function(data){
            if(data.success){
                toast('✅ '+data.driver_name+' assigné · '+data.delivery_fee+' GNF','success');
                closeModal('assignModal');
                setTimeout(function(){ location.reload(); },1200);
            } else {
                toast('Erreur : '+(data.message||'inconnue'),'error');
                btn.disabled=false; btn.textContent='🚴 Confirmer l\'assignation';
            }
        })
        .catch(function(err){
            toast('Erreur réseau : '+err.message,'error');
            btn.disabled=false; btn.textContent='🚴 Confirmer l\'assignation';
        });
}

/* ── Statut ── */
function openStatus(btn){
    var orderId = btn.getAttribute('data-id');
    var current = btn.getAttribute('data-status');
    currentOrderId=orderId; selectedStatusVal=null;
    document.querySelectorAll('.status-opt').forEach(function(el){
        el.classList.remove('selected');
        var input=el.querySelector('input');
        if(input.value===current){ el.classList.add('selected'); selectedStatusVal=current; input.checked=true; }
    });
    openModal('statusModal');
}
document.querySelectorAll('.status-opt').forEach(function(opt){
    opt.addEventListener('click',function(){
        document.querySelectorAll('.status-opt').forEach(function(e){ e.classList.remove('selected'); });
        this.classList.add('selected');
        var input=this.querySelector('input'); selectedStatusVal=input.value; input.checked=true;
    });
});
function submitStatus(){
    if(!currentOrderId||!selectedStatusVal){ toast('Choisissez un statut.','error'); return; }
    var btn=document.getElementById('statusBtn');
    btn.disabled=true; btn.textContent='⏳ En cours…';
    var fd=new FormData();
    fd.append('_token',CSRF); fd.append('status',selectedStatusVal);
    fetch('/company/orders/'+currentOrderId+'/status',{method:'POST',body:fd})
        .then(function(r){ return r.json(); })
        .then(function(data){
            if(data.success){
                toast('✅ Statut mis à jour.','success');
                closeModal('statusModal');
                setTimeout(function(){ location.reload(); },1000);
            } else {
                toast('Erreur : '+(data.message||'inconnue'),'error');
                btn.disabled=false; btn.textContent='Enregistrer';
            }
        })
        .catch(function(err){
            toast('Erreur réseau.','error');
            btn.disabled=false; btn.textContent='Enregistrer';
        });
}
function markDone(btn){
    if(!confirm('Marquer cette commande comme livrée ?')) return;
    currentOrderId=btn.getAttribute('data-id'); selectedStatusVal='livrée'; submitStatus();
}

/* ── Toast ── */
function toast(msg,type){
    type=type||'success';
    var el=document.getElementById('toast');
    el.textContent=msg; el.className='toast toast-'+type+' show';
    setTimeout(function(){ el.classList.remove('show'); },3500);
}
</script>
@endpush
