@extends('layouts.app')
@section('title', 'Mes commandes · ' . $company->name)
@php $bodyClass = 'is-dashboard'; @endphp


@push('styles')
{{-- Anti-flash : applique le fond sombre avant le premier paint si thème dark --}}
<script>
(function(){
    if(localStorage.getItem('cx-theme')==='dark')
        document.documentElement.classList.add('cx-preorders-dark');
})();
</script>
<style>
html.cx-preorders-dark body{background:#0b0d22!important}
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
.cx-nav-sec{font-size:10px;font-weight:800;letter-spacing:1.6px;color:rgba(255,255,255,.58);padding:14px 10px 5px;text-transform:uppercase;}
.cx-nav-item{display:flex;align-items:center;gap:10px;padding:8px 11px;border-radius:var(--r-sm);color:rgba(255,255,255,.85);font-size:13.5px;font-weight:600;transition:all .22s cubic-bezier(.23,1,.32,1);position:relative;cursor:pointer;margin-bottom:2px;border:1px solid transparent;}
.cx-nav-item:hover{background:rgba(124,58,237,.18);color:#fff;border-color:rgba(124,58,237,.25);box-shadow:0 2px 12px rgba(124,58,237,.2),inset 0 1px 0 rgba(255,255,255,.06);}
.cx-nav-item:hover .cx-nav-ico{background:rgba(139,92,246,.25);box-shadow:0 0 8px rgba(139,92,246,.3);}
.cx-nav-item.active{background:linear-gradient(90deg,rgba(124,58,237,.35) 0%,rgba(99,102,241,.2) 100%);color:#fff;font-weight:700;border-color:rgba(139,92,246,.3);box-shadow:0 4px 16px rgba(124,58,237,.25),inset 0 1px 0 rgba(255,255,255,.08);}
.cx-nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:22px;background:linear-gradient(180deg,#a78bfa,#7c3aed);border-radius:0 3px 3px 0;box-shadow:2px 0 12px rgba(167,139,250,.7);}
.cx-nav-ico{width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;transition:all .22s cubic-bezier(.23,1,.32,1);}
.cx-nav-item.active .cx-nav-ico{background:rgba(139,92,246,.3);border-color:rgba(139,92,246,.4);box-shadow:0 0 10px rgba(139,92,246,.4);}
.cx-nav-item.active:hover{background:linear-gradient(90deg,rgba(124,58,237,.45) 0%,rgba(99,102,241,.3) 100%);box-shadow:0 4px 20px rgba(124,58,237,.35),inset 0 1px 0 rgba(255,255,255,.1);}

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
/* Période */
.period-bar{display:flex;align-items:center;gap:6px;flex-wrap:wrap;padding:10px 16px 14px;border-top:1px solid var(--cx-border);}
.period-lbl{font-size:11.5px;font-weight:700;color:var(--cx-muted);white-space:nowrap;margin-right:2px;}
.period-btn{padding:5px 13px;border-radius:20px;font-size:12px;font-weight:700;border:1.5px solid var(--cx-border);background:transparent;color:var(--cx-text2);font-family:inherit;cursor:pointer;transition:all .15s;white-space:nowrap;}
.period-btn:hover{background:var(--cx-card2);color:var(--cx-text);}
.period-btn.active{background:var(--cx-brand);color:#fff;border-color:var(--cx-brand);}
.custom-dates{display:none;align-items:center;gap:6px;flex-wrap:wrap;padding:0 16px 12px;}
.custom-dates.show{display:flex;}
.custom-dates label{font-size:11px;font-weight:700;color:var(--cx-muted);}
body.cx-dark .period-btn{border-color:var(--cx-border);color:var(--cx-text2);}
body.cx-dark .period-btn:hover{background:var(--cx-card2);color:var(--cx-text);}
body.cx-dark .custom-dates label{color:var(--cx-muted);}

/* Table card */
.table-card{background:var(--cx-card);border:1px solid var(--cx-border);border-radius:var(--r);overflow:hidden;}
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
thead th{
    padding:12px 16px;text-align:left;
    font-size:12px;font-weight:700;color:var(--cx-muted);
    text-transform:uppercase;letter-spacing:.6px;
    background:var(--cx-card2);border-bottom:2px solid var(--cx-border);white-space:nowrap;
    position:sticky;top:0;z-index:10;
}
tbody tr{border-bottom:1px solid var(--cx-border);transition:background .12s;}
tbody tr:last-child{border-bottom:none;}
tbody tr:hover{background:rgba(124,58,237,.05);}
tbody tr:nth-child(even){background:rgba(0,0,0,.018);}
tbody tr:nth-child(even):hover{background:rgba(124,58,237,.05);}
td{padding:14px 16px;font-size:14.5px;vertical-align:middle;}
.td-dest{max-width:160px;}
.td-date{white-space:nowrap;}

.order-id{font-size:15px;font-weight:800;color:var(--cx-text);font-family:monospace;}
.order-items{font-size:12.5px;color:var(--cx-muted);margin-top:3px;}
.client-name{font-size:14.5px;font-weight:700;color:var(--cx-text);}
.client-phone{font-size:12.5px;color:var(--cx-muted);}
.shop-badge{
    display:inline-flex;align-items:center;gap:5px;
    background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.2);
    color:#6d28d9;font-size:13px;font-weight:600;padding:4px 12px;border-radius:20px;
}
.shop-addr{font-size:12.5px;color:var(--cx-muted);margin-top:5px;line-height:1.35;}
.driver-cell{display:flex;align-items:center;gap:8px;}
.driver-av{
    width:34px;height:34px;border-radius:7px;flex-shrink:0;overflow:hidden;
    background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2));
    display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;
}
.driver-av img{width:100%;height:100%;object-fit:cover;}
.driver-name{font-weight:700;color:var(--cx-text);font-size:14px;}
.driver-phone{font-size:12.5px;color:var(--cx-muted);}
.no-driver{color:var(--cx-muted);font-size:13.5px;font-style:italic;}
.amount-val{font-weight:800;color:var(--cx-text);font-family:monospace;font-size:15px;}
.fee-val{font-size:13px;color:#059669;font-weight:700;}

.badge{display:inline-flex;align-items:center;gap:4px;font-size:12.5px;font-weight:700;padding:4px 12px;border-radius:20px;white-space:nowrap;}
.badge-wait   {background:rgba(245,158,11,.1); color:#b45309;border:1px solid rgba(245,158,11,.25);}
.badge-confirm{background:rgba(59,130,246,.1); color:#1d4ed8;border:1px solid rgba(59,130,246,.25);}
.badge-deliv  {background:rgba(124,58,237,.1); color:#6d28d9;border:1px solid rgba(124,58,237,.25);}
.badge-done   {background:rgba(16,185,129,.1); color:#065f46;border:1px solid rgba(16,185,129,.25);}
.badge-cancel {background:rgba(239,68,68,.08); color:#b91c1c;border:1px solid rgba(239,68,68,.2);}

.btn-action{
    display:inline-flex;align-items:center;gap:5px;
    padding:8px 14px;border-radius:var(--r-xs);font-size:13px;font-weight:700;
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

/* ── Barre sélection multiple ── */
.sel-bar{
    display:none;align-items:center;gap:12px;flex-wrap:wrap;
    background:linear-gradient(135deg,rgba(124,58,237,.08),rgba(99,102,241,.06));
    border:1.5px solid rgba(124,58,237,.25);border-radius:var(--r-sm);
    padding:12px 16px;margin-bottom:12px;
}
.sel-bar.show{display:flex;}
.sel-count{font-size:13px;font-weight:800;color:var(--cx-brand);}
.sel-bar-actions{display:flex;gap:8px;margin-left:auto;flex-wrap:wrap;}
.btn-sel-assign{
    padding:9px 18px;border-radius:var(--r-xs);border:none;cursor:pointer;font-family:inherit;
    background:linear-gradient(135deg,var(--cx-brand),var(--cx-brand2));
    color:#fff;font-size:13px;font-weight:800;
    display:flex;align-items:center;gap:6px;
    box-shadow:0 3px 10px rgba(124,58,237,.3);transition:all .15s;
}
.btn-sel-assign:hover{transform:translateY(-1px);box-shadow:0 5px 16px rgba(124,58,237,.45);}
.btn-sel-cancel{
    padding:9px 14px;border-radius:var(--r-xs);border:1px solid var(--cx-border);
    background:var(--cx-card2);color:var(--cx-text2);font-size:13px;font-weight:700;
    cursor:pointer;font-family:inherit;transition:all .15s;
}
.btn-sel-cancel:hover{background:var(--cx-card);color:var(--cx-text);}
.btn-sel-annuler{
    padding:9px 14px;border-radius:var(--r-xs);border:1.5px solid #fca5a5;
    background:#fee2e2;color:#991b1b;font-size:13px;font-weight:700;
    cursor:pointer;font-family:inherit;transition:all .15s;
}
.btn-sel-annuler:hover{background:#fecaca;}
.btn-sel-restaurer{
    padding:9px 14px;border-radius:var(--r-xs);border:1.5px solid #6ee7b7;
    background:#d1fae5;color:#065f46;font-size:13px;font-weight:700;
    cursor:pointer;font-family:inherit;transition:all .15s;
}
.btn-sel-restaurer:hover{background:#a7f3d0;}
.btn-action-cancel{
    padding:5px 10px;border-radius:6px;border:1.5px solid #fca5a5;
    background:#fee2e2;color:#991b1b;font-size:12px;font-weight:700;
    cursor:pointer;white-space:nowrap;transition:all .13s;
}
.btn-action-cancel:hover{background:#fecaca;}
.btn-action-restore{
    padding:5px 10px;border-radius:6px;border:1.5px solid #6ee7b7;
    background:#d1fae5;color:#065f46;font-size:12px;font-weight:700;
    cursor:pointer;white-space:nowrap;transition:all .13s;
}
.btn-action-restore:hover{background:#a7f3d0;}
/* Checkbox cell */
.chk-th{width:44px;padding:12px 8px 12px 16px!important;}
.chk-td{padding:12px 8px 12px 16px!important;}
/* Ligne sélectionnée */
tbody tr.row-selected{background:rgba(124,58,237,.07)!important;}
body.cx-dark tbody tr.row-selected{background:rgba(124,58,237,.13)!important;}
.mc-card.row-selected{border-color:rgba(124,58,237,.4)!important;box-shadow:0 0 0 2px rgba(124,58,237,.15)!important;}
body.cx-dark .mc-card.row-selected{border-color:rgba(139,92,246,.5)!important;box-shadow:0 0 0 2px rgba(124,58,237,.25)!important;}
/* Multi-mode : infos commandes dans le modal */
.multi-orders-list{max-height:120px;overflow-y:auto;display:flex;flex-direction:column;gap:4px;margin-bottom:14px;
    scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.3) transparent;}
.multi-orders-list::-webkit-scrollbar{width:4px;}
.multi-orders-list::-webkit-scrollbar-thumb{background:rgba(124,58,237,.3);border-radius:4px;}
.multi-order-item{font-size:12px;font-weight:600;color:var(--cx-text2);padding:5px 10px;
    background:var(--cx-card2);border:1px solid var(--cx-border);border-radius:var(--r-xs);}
body.cx-dark .sel-bar{background:rgba(124,58,237,.12);border-color:rgba(124,58,237,.3);}
body.cx-dark .btn-sel-cancel{background:var(--cx-card2);border-color:var(--cx-border);color:var(--cx-text2);}
body.cx-dark .multi-order-item{background:var(--cx-card2);border-color:var(--cx-border);color:var(--cx-text2);}
.multi-order-item-dest{font-size:10.5px;color:var(--cx-muted);font-weight:400;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.multi-common-dest{padding:9px 12px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:var(--r-xs);margin-bottom:10px;}
.multi-common-dest-lbl{font-size:10px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;}
.multi-common-dest-val{font-size:12.5px;font-weight:700;color:#065f46;}
.multi-common-dest-fee{font-size:11.5px;color:#047857;margin-top:3px;font-weight:600;}
body.cx-dark .multi-common-dest{background:rgba(16,185,129,.08);border-color:rgba(16,185,129,.2);}
body.cx-dark .multi-common-dest-lbl{color:#34d399;}
body.cx-dark .multi-common-dest-val{color:#6ee7b7;}
body.cx-dark .multi-common-dest-fee{color:#34d399;}

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

/* ── Desktop / Mobile visibility ── */
.ord-desktop{display:block}
.ord-mobile{display:none}

/* ── Mobile cards (mc-*) ── */
.mc-card{background:var(--cx-card);border:1px solid var(--cx-border);border-radius:var(--r-sm);margin-bottom:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.mc-head{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border-bottom:1px solid var(--cx-border);gap:8px;flex-wrap:wrap}
.mc-id{font-size:15px;font-weight:900;color:var(--cx-brand);font-family:monospace}
.mc-amounts{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--cx-border)}
.mc-amount-box{padding:13px 14px;background:var(--cx-card2)}
.mc-amount-fee{border-right:1px solid var(--cx-border)}
.mc-amount-lbl{font-size:10.5px;font-weight:700;color:var(--cx-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px}
.mc-amount-val{font-size:18px;font-weight:900;font-family:monospace;color:#059669;word-break:break-all;line-height:1.2}
.mc-amount-total .mc-amount-val{color:var(--cx-text)}
.mc-rows{padding:4px 0}
.mc-row{display:flex;align-items:flex-start;justify-content:space-between;padding:9px 14px;border-bottom:1px solid var(--cx-border);gap:10px}
.mc-lbl{font-size:12px;font-weight:700;color:var(--cx-muted);flex-shrink:0;min-width:100px}
.mc-val{font-size:13px;font-weight:600;color:var(--cx-text);text-align:right;word-break:break-word}
.mc-actions{display:flex;gap:8px;flex-wrap:wrap;padding:10px 14px;border-top:1px solid var(--cx-border);background:var(--cx-card2)}
body.cx-dark .mc-amount-val{color:#34d399}
body.cx-dark .mc-amount-total .mc-amount-val{color:var(--cx-text)}

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
    .banner-title{font-size:17px;}
    .banner-sub{font-size:11.5px;}
    /* Topbar */
    .cx-topbar{padding:0 12px;gap:8px;}
    .cx-topbar-title{font-size:13px;}
    .cx-tb-right{gap:6px;}
    /* Filter bar: tabs wrap as pills, search+date full-width */
    .filter-bar{flex-wrap:wrap;gap:6px;padding:10px 12px;}
    .tab-btn{padding:5px 10px;font-size:11px;}
    .tab-sep{display:none;}
    .search-wrap{flex:1 1 100%;min-width:unset;}
    .date-input{flex:1 1 100%;}
    /* Période */
    .period-bar{padding:8px 12px 10px;gap:4px;flex-wrap:wrap;}
    .period-btn{padding:4px 10px;font-size:11px;}
    /* Sélection multiple */
    .sel-bar{padding:10px 12px;gap:8px;}
    .sel-bar-actions{gap:6px;}
    .btn-sel-assign,.btn-sel-annuler,.btn-sel-restaurer,.btn-sel-cancel{padding:7px 12px;font-size:12px;}
    /* Basculement tableau ↔ cartes */
    .ord-desktop{display:none}
    .ord-mobile{display:block}
    /* Modal → bottom sheet */
    .modal-overlay{padding:0;align-items:flex-end;}
    .cx-modal{max-width:100%;border-radius:var(--r) var(--r) 0 0;max-height:92vh;}
    /* Addr-flow stack vertically */
    .addr-flow{flex-direction:column;}
    .addr-arrow{transform:rotate(90deg);align-self:center;}
}
@media(max-width:480px){
    .stats-bar{grid-template-columns:1fr 1fr;}
    .page-banner{padding:16px 12px 14px;}
    .banner-title{font-size:15px;gap:7px;}
    .stat-pill{padding:10px 10px;}
    .stat-pill-val{font-size:18px;}
    .mc-amount-val{font-size:16px;}
    .pw{padding:10px 10px 60px;}
    .cx-topbar-title span{display:none;}
}
@media(max-width:360px){
    .stats-bar{grid-template-columns:1fr 1fr;}
    .stat-pill{padding:8px;}
    .stat-pill-val{font-size:15px;}
    .banner-title{font-size:14px;}
    .cx-topbar-title{font-size:11.5px;}
    .period-btn{padding:3px 8px;font-size:10.5px;}
    .mc-amount-val{font-size:15px;}
    .mc-lbl{min-width:85px;}
    .mc-actions{gap:6px;}
    .btn-action,.btn-action-cancel,.btn-action-restore{padding:6px 10px;font-size:11.5px;}
}

/* ══ TOGGLE MODE SOMBRE ══ */
.cx-dark-row{display:flex;align-items:center;justify-content:space-between;padding:4px 8px;cursor:pointer;border-radius:var(--r-sm);transition:background .14s;}
.cx-dark-row:hover{background:rgba(255,255,255,.04);}
.cx-dark-lbl{font-size:11.5px;color:rgba(255,255,255,.45);}
.cx-toggle{width:34px;height:18px;background:#475569;border-radius:9px;position:relative;transition:background .25s;flex-shrink:0;}
.cx-toggle::after{content:'';position:absolute;top:3px;left:3px;width:12px;height:12px;background:#fff;border-radius:50%;transition:left .25s;}
.cx-toggle.on{background:var(--cx-brand);}
.cx-toggle.on::after{left:19px;}

/* ══ MODE SOMBRE ══ */
body.cx-dark{
    --cx-bg:     #0b0d22;
    --cx-card:   #0d1226;
    --cx-card2:  #111930;
    --cx-border: rgba(255,255,255,.07);
    --cx-border2:rgba(255,255,255,.12);
    --cx-text:   #e2e8f0;
    --cx-text2:  #94a3b8;
    --cx-muted:  #475569;
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
/* Filter bar */
body.cx-dark .filter-bar{background:var(--cx-card);border-color:var(--cx-border);}
body.cx-dark .tab-btn{color:var(--cx-text2);}
body.cx-dark .tab-btn:hover{background:var(--cx-card2);color:var(--cx-text);}
body.cx-dark .tab-sep{background:var(--cx-border);}
body.cx-dark .search-input{background:var(--cx-card2);border-color:var(--cx-border);color:var(--cx-text);}
body.cx-dark .search-input::placeholder{color:var(--cx-muted);}
body.cx-dark .date-input{background:var(--cx-card2);border-color:var(--cx-border);color:var(--cx-text);}
/* Table */
body.cx-dark .table-card{background:var(--cx-card);border-color:var(--cx-border);}
body.cx-dark thead th{background:var(--cx-card2);border-bottom-color:var(--cx-border);color:var(--cx-muted);}
body.cx-dark tbody tr{border-bottom-color:var(--cx-border);}
body.cx-dark tbody tr:hover{background:rgba(124,58,237,.08);}
body.cx-dark tbody tr:nth-child(even){background:rgba(255,255,255,.018);}
body.cx-dark tbody tr:nth-child(even):hover{background:rgba(124,58,237,.08);}
body.cx-dark .order-id{color:var(--cx-text);}
body.cx-dark .client-name{color:var(--cx-text);}
body.cx-dark .driver-name{color:var(--cx-text);}
body.cx-dark .amount-val{color:var(--cx-text);}
body.cx-dark .fee-val{color:#34d399;}
body.cx-dark .shop-badge{background:rgba(124,58,237,.18);color:#a78bfa;border-color:rgba(124,58,237,.35);}
body.cx-dark .badge-wait   {color:#fbbf24;border-color:rgba(245,158,11,.3);}
body.cx-dark .badge-confirm{color:#60a5fa;border-color:rgba(59,130,246,.3);}
body.cx-dark .badge-deliv  {color:#a78bfa;border-color:rgba(124,58,237,.3);}
body.cx-dark .badge-done   {color:#34d399;border-color:rgba(16,185,129,.3);}
body.cx-dark .badge-cancel {color:#f87171;border-color:rgba(239,68,68,.25);}
body.cx-dark .no-driver{color:var(--cx-muted);}
/* Boutons actions */
body.cx-dark .btn-status{color:var(--cx-text2);border-color:var(--cx-border);}
body.cx-dark .btn-status:hover{background:var(--cx-card2);color:var(--cx-text);}
/* Pagination */
body.cx-dark .pagination-wrap{border-top-color:var(--cx-border);}
body.cx-dark .pagination-wrap .page-item .page-link{background:var(--cx-card2);border-color:var(--cx-border);color:var(--cx-text2);}
body.cx-dark .pagination-wrap .page-item.active .page-link{background:var(--cx-brand);border-color:var(--cx-brand);color:#fff;}
/* Modal */
body.cx-dark .modal-overlay{background:rgba(0,0,0,.75);}
body.cx-dark .cx-modal{background:var(--cx-card);border-color:var(--cx-border2);}
body.cx-dark .cx-modal-hd{background:var(--cx-card);border-bottom-color:var(--cx-border);}
body.cx-dark .cx-modal-title{color:var(--cx-text);}
body.cx-dark .cx-modal-sub{color:var(--cx-muted);}
body.cx-dark .cx-modal-close{background:var(--cx-card2);border-color:var(--cx-border);color:var(--cx-text2);}
body.cx-dark .driver-opt{background:var(--cx-card2);border-color:var(--cx-border);}
body.cx-dark .d-name{color:var(--cx-text);}
body.cx-dark .d-phone{color:var(--cx-muted);}
body.cx-dark .status-opt{background:var(--cx-card2);border-color:var(--cx-border);}
body.cx-dark .form-label{color:var(--cx-text2);}
body.cx-dark .form-input{background:var(--cx-card2);border-color:var(--cx-border);color:var(--cx-text);}
body.cx-dark .form-input::placeholder{color:var(--cx-muted);}
body.cx-dark .avail-section-lbl{color:var(--cx-muted);}
body.cx-dark .no-avail-warn{background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.2);color:#fbbf24;}
body.cx-dark .selected-summary{background:rgba(16,185,129,.07);border-color:rgba(16,185,129,.2);color:#34d399;}
body.cx-dark .addr-val{color:var(--cx-text);}
/* Dark mode card rows on mobile */
body.cx-dark .table-wrap tbody tr{background:var(--cx-card);border-color:var(--cx-border);}
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
    $fmt = fn($n) => number_format($n ?? 0, 0, ',', ' ') . ' ' . $devise;
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
                 <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width: 40px;;height: 40px;object-fit:cover;border-radius:9px"></div>
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
        <a href="{{ route('company.orders.index') }}" class="cx-nav-item active">
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
        <a href="{{route('company.clients.index')}}" class="cx-nav-item">
            <span class="cx-nav-ico">👥</span> Clients
        </a>

        <div class="cx-nav-sec">Gestion</div>
        <a href="{{route('company.zones.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📍</span> Zone de livraison
        </a>
       
        <a href="{{ route('company.historique.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📊</span> Historique
        </a>
        <a href="{{ route('company.rapport.index') }}" class="cx-nav-item"><span class="cx-nav-ico">📈</span> Rapport</a>

        <div class="cx-nav-sec">Configuration</div>
        <a href="{{ route('company.parametre.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">⚙️</span> Paramètres
        </a>
        <a href="{{ route('company.users.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">👤</span> Utilisateurs
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
        <div class="cx-topbar-title">📦 <span>{{ $shopFilter ? 'Commandes · '.$shopFilter->name : 'Commandes' }}</span></div>
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
                <div class="banner-title">📦 Mes commandes</div>
                <div class="banner-sub">
                    @if($shopFilter)
                        {{ $shopFilter->name }} · Commandes de cette boutique
                    @else
                        {{ $company->name }} · Commandes assignées à votre entreprise
                    @endif
                </div>
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

    {{-- BADGE BOUTIQUE FILTRÉE --}}
    @if($shopFilter)
    <div style="background:linear-gradient(90deg,rgba(124,58,237,.15),rgba(99,102,241,.08));border:1px solid rgba(124,58,237,.2);border-radius:var(--r-sm);padding:10px 16px;margin-bottom:14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <span style="font-size:18px;">🏪</span>
        <div>
            <div style="font-size:13px;font-weight:800;color:#a78bfa;">Filtré par boutique : {{ $shopFilter->name }}</div>
            <div style="font-size:11.5px;color:var(--cx-text2);">Affichage des commandes uniquement pour cette boutique</div>
        </div>
        <a href="{{ route('company.orders.index') }}" style="margin-left:auto;font-size:12px;padding:5px 12px;border:1px solid rgba(124,58,237,.3);border-radius:6px;background:rgba(124,58,237,.1);color:#c4b5fd;white-space:nowrap;">
            ✕ Voir toutes les commandes
        </a>
    </div>
    @endif

    {{-- FILTER BAR --}}
    <form method="GET" action="{{ route('company.orders.index') }}" id="filterForm">
        @php
            $tabs   = ['all'=>'Toutes','en_attente'=>'En attente','confirmée'=>'Confirmées','en_livraison'=>'En livraison','livrée'=>'Livrées','annulée'=>'Annulées'];
            $cur    = request('status','all');
            $curPer = $period ?? request('period','all');
        @endphp
        @if($shopFilter)
        <input type="hidden" name="boutique" value="{{ $shopFilter->id }}">
        @endif
        {{-- Ligne 1 : statuts + recherche --}}
        <div class="filter-bar" style="border-bottom:none;border-radius:var(--r) var(--r) 0 0;padding-bottom:10px;">
            @foreach($tabs as $val => $lbl)
            <button type="button" class="tab-btn {{ $cur===$val?'active':'' }}" onclick="setStatus('{{ $val }}')">{{ $lbl }}</button>
            @endforeach
            <div class="tab-sep"></div>
            <div class="search-wrap">
                <svg class="search-ico" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="search-input" type="text" name="search" id="searchInput"
                       placeholder="Client, boutique, destination…" value="{{ request('search') }}">
            </div>
            <input type="hidden" name="status" id="statusInput" value="{{ $cur }}">
            <input type="hidden" name="period" id="periodInput" value="{{ $curPer }}">
        </div>
        {{-- Ligne 2 : Période --}}
        <div class="filter-bar" style="border-top:1px solid var(--cx-border);border-radius:0 0 var(--r) var(--r);padding-top:10px;">
            <span class="period-lbl">Période :</span>
            @foreach(['all'=>'Tout','today'=>"Aujourd'hui",'yesterday'=>'Hier','week'=>'Cette semaine','month'=>'Ce mois','custom'=>'Personnalisé'] as $pval => $plbl)
            <button type="button" class="period-btn {{ $curPer===$pval?'active':'' }}" onclick="setPeriod('{{ $pval }}')">{{ $plbl }}</button>
            @endforeach
        </div>
        {{-- Champs date personnalisée --}}
        <div class="custom-dates {{ $curPer==='custom'?'show':'' }}" id="customDates"
             style="background:var(--cx-card);border:1px solid var(--cx-border);border-top:none;border-radius:0 0 var(--r) var(--r);padding:8px 16px 12px;">
            <label>Du</label>
            <input class="date-input" type="date" name="date_from" value="{{ request('date_from') }}">
            <label>Au</label>
            <input class="date-input" type="date" name="date_to" value="{{ request('date_to') }}"
                   onchange="document.getElementById('filterForm').submit()">
        </div>
    </form>

    {{-- BARRE SÉLECTION MULTIPLE --}}
    <div class="sel-bar" id="selBar">
        <span class="sel-count" id="selCount">0 commande sélectionnée</span>
        <div class="sel-bar-actions">
            <button class="btn-sel-assign" onclick="openMultiAssign()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                <span id="selBtnLabel">Assigner</span>
            </button>
            <button class="btn-sel-annuler" onclick="bulkCancelOrders()">🚫 Annuler</button>
            <button class="btn-sel-restaurer" onclick="bulkRestoreOrders()">♻️ Restaurer</button>
            <button class="btn-sel-cancel" onclick="clearSelection()">✕ Effacer</button>
        </div>
    </div>

    {{-- TABLE DESKTOP --}}
    <div class="table-card ord-desktop">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="chk-th">
                            <input type="checkbox" id="chkAll" onclick="toggleAllRows(this)"
                                   title="Tout sélectionner"
                                   style="width:16px;height:16px;accent-color:var(--cx-brand);cursor:pointer;border-radius:4px;">
                        </th>
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
                <tr data-order-id="{{ $order->id }}">
                    <td class="chk-td" data-label="">
                        @if(in_array($order->status, ['en_attente','confirmée']))
                        <input type="checkbox" class="row-chk"
                               data-order-id="{{ $order->id }}"
                               data-num="{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}"
                               data-fee="{{ $order->delivery_fee ?? $order->deliveryZone?->price ?? '' }}"
                               data-dest="{{ $order->delivery_destination ?? $order->client->address ?? '' }}"
                               data-shop="{{ $order->shop->name ?? '' }}"
                               data-client="{{ $order->client->name ?? '' }}"
                               data-client-id="{{ $order->user_id }}"
                               data-shop-addr="{{ $order->shop->address ?? '' }}"
                               data-client-addr="{{ $order->client->address ?? '' }}"
                               onchange="toggleRow(this)"
                               style="width:16px;height:16px;accent-color:var(--cx-brand);cursor:pointer;border-radius:4px;">
                        @endif
                    </td>
                    <td data-label="Commande">
                        <div>
                            <div class="order-id">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="order-items">{{ $order->items->count() }} article(s)</div>
                        </div>
                    </td>
                    <td data-label="Client">
                        <div>
                            <div class="client-name">{{ $order->client->name ?? '—' }}</div>
                            @php $cPhone = $order->client_phone ?: ($order->client->phone ?? null); @endphp
                            @if($cPhone)
                            <a href="tel:{{ $cPhone }}" class="client-phone" style="text-decoration:none;color:var(--cx-muted)">📞 {{ $cPhone }}</a>
                            @endif
                        </div>
                    </td>
                    <td data-label="Boutique">
                        <div>
                            <span class="shop-badge">🏪 {{ $order->shop->name ?? '—' }}</span>
                            @if($order->shop?->address)
                            <div class="shop-addr">📍 {{ $order->shop->address }}</div>
                            @endif
                        </div>
                    </td>
                    <td data-label="Destination" style="color:var(--cx-text2);font-size:12.5px;" class="td-dest">
                        @php $dest = $order->delivery_destination ?: ($order->client->address ?? null); @endphp
                        @if($dest)
                            <div style="font-size:12px;line-height:1.4;">{{ $dest }}</div>
                        @else
                            <span style="color:var(--cx-muted);font-style:italic;font-size:11.5px;">Non renseignée</span>
                        @endif
                    </td>
                    <td data-label="Chauffeur">
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
                    @php $displayFee = $order->delivery_fee ?? $order->deliveryZone?->price ?? null; @endphp
                    <td data-label="Frais">
                        <div class="fee-val">{{ $displayFee ? $fmt($displayFee) : '—' }}</div>
                        @if(!$order->delivery_fee && $order->deliveryZone)
                        <div style="font-size:10px;color:var(--cx-muted);margin-top:2px;">📍 {{ $order->deliveryZone->name }}</div>
                        @endif
                    </td>
                    <td data-label="Montant"><div class="amount-val">{{ $fmt($order->total) }}</div></td>
                    <td data-label="Statut"><span class="badge {{ $st['cls'] }}">{{ $st['lbl'] }}</span></td>
                    <td data-label="Date" style="color:var(--cx-muted);font-size:12px;" class="td-date">
                        {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/y H:i') }}
                    </td>
                    <td data-label="Actions">
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            @if(in_array($order->status, ['en_attente','confirmée']))
                            <button class="btn-action btn-assign"
                                    data-id="{{ $order->id }}"
                                    data-fee="{{ $order->delivery_fee ?? '' }}"
                                    data-zone-price="{{ $order->deliveryZone?->price ?? '' }}"
                                    data-dest="{{ $order->delivery_destination ?? '' }}"
                                    data-num="{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}"
                                    data-shop="{{ $order->shop->name ?? '' }}"
                                    data-shop-addr="{{ $order->shop->address ?? '' }}"
                                    data-client-addr="{{ $order->client->address ?? '' }}"
                                    onclick="openAssign(this)">
                                🚴 Assigner
                            </button>
                            @endif
                            @if(!in_array($order->status, ['livrée','annulée']))
                            <button class="btn-action-cancel"
                                    onclick="cancelOrder({{ $order->id }}, this)">
                                🚫 Annuler
                            </button>
                            @endif
                            @if($order->status === 'annulée')
                            <button class="btn-action-restore"
                                    onclick="restoreOrder({{ $order->id }}, this)">
                                ♻️ Restaurer
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11">
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

{{-- ══ CARTES MOBILE ══ --}}
<div class="ord-mobile">
    @forelse($orders as $order)
    @php
        $st         = $statusMap[$order->status] ?? ['lbl'=>$order->status,'cls'=>'badge-wait'];
        $cPhone     = $order->client_phone ?: ($order->client->phone ?? null);
        $dest       = $order->delivery_destination ?: ($order->client->address ?? null);
        $displayFee = $order->delivery_fee ?? $order->deliveryZone?->price ?? null;
    @endphp
    <div class="mc-card" data-order-id="{{ $order->id }}">

        {{-- En-tête : ID + checkbox + statut --}}
        <div class="mc-head">
            <div style="display:flex;align-items:center;gap:10px;">
                @if(in_array($order->status, ['en_attente','confirmée']))
                <input type="checkbox" class="row-chk"
                       data-order-id="{{ $order->id }}"
                       data-num="{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}"
                       data-fee="{{ $order->delivery_fee ?? $order->deliveryZone?->price ?? '' }}"
                       data-dest="{{ $order->delivery_destination ?? $order->client->address ?? '' }}"
                       data-shop="{{ $order->shop->name ?? '' }}"
                       data-client="{{ $order->client->name ?? '' }}"
                       data-client-id="{{ $order->user_id }}"
                       data-shop-addr="{{ $order->shop->address ?? '' }}"
                       data-client-addr="{{ $order->client->address ?? '' }}"
                       onchange="toggleRow(this)"
                       style="width:18px;height:18px;accent-color:var(--cx-brand);cursor:pointer;border-radius:4px;flex-shrink:0;">
                @endif
                <span class="mc-id">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <span class="badge {{ $st['cls'] }}">{{ $st['lbl'] }}</span>
        </div>

        {{-- Montants en évidence --}}
        <div class="mc-amounts">
            <div class="mc-amount-box mc-amount-fee">
                <div class="mc-amount-lbl">🚚 Frais liv.</div>
                <div class="mc-amount-val">{{ $displayFee ? $fmt($displayFee) : '—' }}</div>
            </div>
            <div class="mc-amount-box mc-amount-total">
                <div class="mc-amount-lbl">💰 Montant</div>
                <div class="mc-amount-val">{{ $fmt($order->total) }}</div>
            </div>
        </div>

        {{-- Détails --}}
        <div class="mc-rows">
            <div class="mc-row">
                <span class="mc-lbl">👤 Client</span>
                <span class="mc-val">{{ $order->client->name ?? '—' }}
                    @if($cPhone)<span style="color:var(--cx-muted);font-size:11.5px;display:block">📞 {{ $cPhone }}</span>@endif
                </span>
            </div>
            <div class="mc-row">
                <span class="mc-lbl">🏪 Boutique</span>
                <span class="mc-val">{{ $order->shop->name ?? '—' }}</span>
            </div>
            @if($dest)
            <div class="mc-row">
                <span class="mc-lbl">📍 Destination</span>
                <span class="mc-val">{{ $dest }}</span>
            </div>
            @endif
            <div class="mc-row">
                <span class="mc-lbl">🚴 Chauffeur</span>
                <span class="mc-val">
                    @if($order->driver)
                        {{ $order->driver->name }}
                        @if($order->driver->phone)<span style="color:var(--cx-muted);font-size:11.5px;display:block">{{ $order->driver->phone }}</span>@endif
                    @else
                        <span style="color:var(--cx-muted);font-style:italic;font-size:12.5px">Non assigné</span>
                    @endif
                </span>
            </div>
            <div class="mc-row" style="border-bottom:none">
                <span class="mc-lbl">📅 Date</span>
                <span class="mc-val" style="color:var(--cx-muted)">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/y H:i') }}</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mc-actions">
            @if(in_array($order->status, ['en_attente','confirmée']))
            <button class="btn-action btn-assign"
                    data-id="{{ $order->id }}"
                    data-fee="{{ $order->delivery_fee ?? '' }}"
                    data-zone-price="{{ $order->deliveryZone?->price ?? '' }}"
                    data-dest="{{ $order->delivery_destination ?? '' }}"
                    data-num="{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}"
                    data-shop="{{ $order->shop->name ?? '' }}"
                    data-shop-addr="{{ $order->shop->address ?? '' }}"
                    data-client-addr="{{ $order->client->address ?? '' }}"
                    onclick="openAssign(this)">
                🚴 Assigner
            </button>
            @endif
            @if(!in_array($order->status, ['livrée','annulée']))
            <button class="btn-action-cancel" onclick="cancelOrder({{ $order->id }}, this)">🚫 Annuler</button>
            @endif
            @if($order->status === 'annulée')
            <button class="btn-action-restore" onclick="restoreOrder({{ $order->id }}, this)">♻️ Restaurer</button>
            @endif
        </div>

    </div>
    @empty
    <div class="empty-state" style="background:var(--cx-card);border:1px solid var(--cx-border);border-radius:var(--r-sm);">
        <div class="empty-ico">📦</div>
        <div class="empty-txt">Aucune commande assignée à votre entreprise</div>
        <div class="empty-sub">Les boutiques vous confient leurs livraisons depuis leur tableau de bord.</div>
    </div>
    @endforelse
    @if($orders->hasPages())
    <div class="pagination-wrap" style="background:var(--cx-card);border:1px solid var(--cx-border);border-radius:var(--r-sm);margin-top:4px;">
        {{ $orders->links() }}
    </div>
    @endif
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

            {{-- ── Multi-commandes (visible en mode bulk) ── --}}
            <div id="multiOrdersSection" style="display:none;margin-bottom:14px;">
                <div class="form-label">📦 Commandes sélectionnées</div>
                <div class="multi-orders-list" id="multiOrdersList"></div>
                <div id="multiDestInfo"></div>
                <div id="multiDestNote" style="font-size:11.5px;color:var(--cx-muted);">Les frais saisis s'appliqueront à chaque commande.</div>
                {{-- Groupes par client (multi-clients) --}}
                <div id="groupedFeesList" style="display:none;margin-top:10px;display:flex;flex-direction:column;gap:8px;"></div>
            </div>

            {{-- ── Blocs adresses (visible en mode simple) ── --}}
            <div class="addr-flow" id="addrFlow">
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

            <div class="form-group" id="assignFeeGroup">
                <label class="form-label">💰 Frais de livraison ({{ $devise }})</label>
                <input type="number" class="form-input" id="assignFee" placeholder="Ex: 50 000" min="0" step="500">
            </div>
            <div class="form-group" id="destGroup">
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
const DEVISE = @json($devise);
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
    document.documentElement.classList.remove('cx-preorders-dark');

    row?.addEventListener('click', () => {
        const isDark = body.classList.toggle('cx-dark');
        sw?.classList.toggle('on', isDark);
        if(lbl) lbl.textContent = isDark ? 'Mode sombre' : 'Mode clair';
        localStorage.setItem('cx-theme', isDark ? 'dark' : 'light');
    });
})();

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

/* ── Filtre statut ── */
function setStatus(v){ document.getElementById('statusInput').value=v; document.getElementById('filterForm').submit(); }

/* ── Filtre période ── */
function setPeriod(v){
    document.getElementById('periodInput').value = v;
    const cd = document.getElementById('customDates');
    if (v === 'custom') { cd.classList.add('show'); }
    else { cd.classList.remove('show'); document.getElementById('filterForm').submit(); }
}

var _st;
document.getElementById('searchInput').addEventListener('input',function(){ clearTimeout(_st); _st=setTimeout(function(){ document.getElementById('filterForm').submit(); },500); });

/* ── État global ── */
var currentOrderId=null, selectedDriverId=null, selectedStatusVal=null;
var _bulkMode=false, _bulkOrderIds=new Set();

/* ── Modals ── */
function openModal(id){ document.getElementById(id).classList.add('open'); }
function closeModal(id){
    document.getElementById(id).classList.remove('open');
    currentOrderId=null; selectedDriverId=null; selectedStatusVal=null;
    _bulkMode=false; _isMultiClientBulk=false; _assignGroups=[];
    document.querySelectorAll('.driver-opt,.status-opt').forEach(function(el){ el.classList.remove('selected'); });
    var s=document.getElementById('selectedSummary'); if(s) s.classList.remove('show');
    /* Remettre le btn + champs en état normal */
    var btn=document.getElementById('assignBtn');
    if(btn){ btn.disabled=false; btn.textContent='🚴 Confirmer l\'assignation'; }
    var afg=document.getElementById('assignFeeGroup'); if(afg) afg.style.display='';
    var gfl=document.getElementById('groupedFeesList'); if(gfl){ gfl.style.display='none'; gfl.innerHTML=''; }
}
document.querySelectorAll('.modal-overlay').forEach(function(el){
    el.addEventListener('click',function(e){ if(e.target===el) closeModal(el.id); });
});
document.querySelectorAll('[data-close]').forEach(function(btn){
    btn.addEventListener('click',function(){ closeModal(btn.getAttribute('data-close')); });
});
document.addEventListener('keydown',function(e){ if(e.key==='Escape'){ closeModal('assignModal'); closeModal('statusModal'); } });

/* ── Sélection multiple ── */
function toggleRow(chk){
    var tr=chk.closest('tr')||chk.closest('.mc-card');
    if(chk.checked){ _bulkOrderIds.add(chk.dataset.orderId); if(tr)tr.classList.add('row-selected'); }
    else            { _bulkOrderIds.delete(chk.dataset.orderId); if(tr)tr.classList.remove('row-selected'); }
    updateSelBar();
}
function toggleAllRows(masterChk){
    document.querySelectorAll('.row-chk').forEach(function(chk){
        chk.checked=masterChk.checked;
        var tr=chk.closest('tr')||chk.closest('.mc-card');
        if(masterChk.checked){ _bulkOrderIds.add(chk.dataset.orderId); if(tr)tr.classList.add('row-selected'); }
        else                  { _bulkOrderIds.delete(chk.dataset.orderId); if(tr)tr.classList.remove('row-selected'); }
    });
    updateSelBar();
}
function updateSelBar(){
    var n=_bulkOrderIds.size;
    var bar=document.getElementById('selBar');
    var cnt=document.getElementById('selCount');
    var lbl=document.getElementById('selBtnLabel');
    if(n>0){
        bar.classList.add('show');
        cnt.textContent=n+' commande'+(n>1?'s':'')+' sélectionné'+(n>1?'es':'e');
        lbl.textContent='Assigner ('+n+')';
    } else {
        bar.classList.remove('show');
    }
    /* Maj checkbox "tout sélectionner" */
    var total=document.querySelectorAll('.row-chk').length;
    var chkAll=document.getElementById('chkAll');
    if(chkAll){ chkAll.checked=n>0&&n===total; chkAll.indeterminate=n>0&&n<total; }
}
function clearSelection(){
    _bulkOrderIds.clear();
    document.querySelectorAll('.row-chk').forEach(function(chk){
        chk.checked=false;
        var tr=chk.closest('tr')||chk.closest('.mc-card');
        if(tr)tr.classList.remove('row-selected');
    });
    var chkAll=document.getElementById('chkAll');
    if(chkAll){ chkAll.checked=false; chkAll.indeterminate=false; }
    updateSelBar();
}
/* Groupes de clients pour l'assignation bulk multi-clients */
var _assignGroups = [];
var _isMultiClientBulk = false;

function openMultiAssign(){
    if(_bulkOrderIds.size===0) return;
    _bulkMode=true;

    /* Collecter les infos de chaque commande sélectionnée */
    var orders=[];
    document.querySelectorAll('.row-chk:checked').forEach(function(chk){
        orders.push({
            orderId:  chk.dataset.orderId,
            num:      chk.dataset.num||chk.dataset.orderId,
            dest:     (chk.dataset.dest||'').trim(),
            fee:      chk.dataset.fee ? parseFloat(chk.dataset.fee) : 0,
            shop:     chk.dataset.shop||'',
            client:   chk.dataset.client||'',
            clientId: chk.dataset.clientId||''
        });
    });

    var fmt = function(n){ return new Intl.NumberFormat('fr').format(n); };

    /* ── Construire les groupes par client ── */
    var groupMap = {};
    orders.forEach(function(o){
        var key = o.clientId || ('anon_'+o.client);
        if(!groupMap[key]){
            groupMap[key] = { clientId:o.clientId, clientName:o.client||'Client', dest:o.dest, baseFee:o.fee, orders:[] };
        }
        groupMap[key].orders.push(o);
    });
    _assignGroups = Object.values(groupMap);
    _isMultiClientBulk = _assignGroups.length > 1;

    var listEl          = document.getElementById('multiOrdersList');
    var destInfoEl      = document.getElementById('multiDestInfo');
    var destNoteEl      = document.getElementById('multiDestNote');
    var groupedFeesEl   = document.getElementById('groupedFeesList');
    var assignFeeGroup  = document.getElementById('assignFeeGroup');
    listEl.innerHTML=''; destInfoEl.innerHTML=''; groupedFeesEl.innerHTML='';

    if(_isMultiClientBulk){
        /* ══ PLUSIEURS CLIENTS → groupes séparés, frais indépendants ══ */
        listEl.style.display='none';
        destInfoEl.innerHTML='';
        destNoteEl.style.display='none';
        assignFeeGroup.style.display='none';
        groupedFeesEl.style.display='flex';

        var totalClients = _assignGroups.length;
        var baseFee = _assignGroups[0].baseFee || 0;

        _assignGroups.forEach(function(g, gi){
            var nums = g.orders.map(function(o){ return '#'+o.num; }).join(', ');
            var feeVal = g.baseFee || baseFee;
            var card = document.createElement('div');
            card.style.cssText='padding:11px 13px;border-radius:9px;border:1.5px solid var(--cx-border);background:var(--cx-card2);';
            card.innerHTML=
                '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;gap:8px;">'
                    +'<div style="font-size:12.5px;font-weight:800;color:var(--cx-text);">👤 '+g.clientName+'</div>'
                    +'<span style="font-size:10.5px;font-weight:700;background:#eef2ff;color:#4f46e5;border:1.5px solid #c7d2fe;border-radius:6px;padding:2px 8px;white-space:nowrap;">'+g.orders.length+' commande'+(g.orders.length>1?'s':'')+' · 1 trajet</span>'
                +'</div>'
                +(g.dest ? '<div style="font-size:11.5px;color:var(--cx-muted);margin-bottom:6px;">📍 '+g.dest+'</div>' : '')
                +'<div style="font-size:11px;color:var(--cx-muted);margin-bottom:5px;">'+nums+'</div>'
                +'<div style="display:flex;align-items:center;gap:7px;">'
                    +'<span style="font-size:11.5px;font-weight:700;color:var(--cx-text);white-space:nowrap;">💰 Frais :</span>'
                    +'<input type="number" id="group-fee-'+gi+'" value="'+feeVal+'" min="0" step="500" '
                        +'style="flex:1;padding:6px 10px;border:1.5px solid var(--cx-border);border-radius:7px;font-size:12.5px;font-family:inherit;background:var(--cx-card);color:var(--cx-text);" '
                        +'placeholder="Frais '+DEVISE+'">'
                    +'<span style="font-size:11px;color:var(--cx-muted);white-space:nowrap;">'+DEVISE+'</span>'
                +'</div>';
            groupedFeesEl.appendChild(card);
        });

        /* Résumé total */
        var totalEl = document.createElement('div');
        totalEl.style.cssText='padding:8px 12px;border-radius:8px;background:#f0fdf4;border:1.5px solid #bbf7d0;font-size:12px;font-weight:700;color:#065f46;';
        totalEl.id='groupFeeTotal';
        totalEl.innerHTML='📊 '+totalClients+' trajets · Total frais : '+fmt(baseFee*totalClients)+' '+DEVISE;
        groupedFeesEl.appendChild(totalEl);

        /* Recalcul total en live */
        _assignGroups.forEach(function(g, gi){
            document.getElementById('group-fee-'+gi).addEventListener('input', function(){
                var total=0;
                _assignGroups.forEach(function(_, i){
                    total += parseFloat(document.getElementById('group-fee-'+i).value)||0;
                });
                document.getElementById('groupFeeTotal').innerHTML='📊 '+totalClients+' trajets · Total frais : '+fmt(total)+' '+DEVISE;
            });
        });

        document.getElementById('assignBtn').textContent='🚴 Assigner les '+totalClients+' groupes';

    } else {
        /* ══ UN SEUL CLIENT (ou destinations différentes) ══ */
        listEl.style.display='';
        assignFeeGroup.style.display='';
        groupedFeesEl.style.display='none';

        var g = _assignGroups[0];
        var fees = orders.map(function(o){ return o.fee; }).filter(function(f){ return f>0; });
        var allSameFee = fees.length===orders.length && fees.every(function(f){ return f===fees[0]; });

        orders.forEach(function(o){
            var item=document.createElement('div');
            item.className='multi-order-item';
            item.innerHTML='<span style="font-weight:700;">#'+o.num+'</span>';
            listEl.appendChild(item);
        });

        var feeOnce = (allSameFee && fees[0]) ? fees[0] : 0;
        destInfoEl.innerHTML='<div class="multi-common-dest">'
            +'<div class="multi-common-dest-lbl">👤 '+(g.clientName||'Client')+' · 📍 Destination</div>'
            +'<div class="multi-common-dest-val">'+(g.dest||'Non renseignée')+'</div>'
            +(feeOnce ? '<div class="multi-common-dest-fee">💰 Frais de livraison : '+fmt(feeOnce)+' '+DEVISE+(orders.length>1?' <span style="font-size:10px;opacity:.7;">(1 seul trajet)</span>':'')+'</div>' : '')
            +'</div>';
        document.getElementById('assignFee').value = feeOnce||'';
        destNoteEl.innerHTML = orders.length>1 ? '⚠️ Même client — frais appliqué <b>une seule fois</b>, pas par commande.' : '';
        destNoteEl.style.cssText = orders.length>1 ? 'font-size:11.5px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:5px 9px;margin-top:4px;' : '';
        destNoteEl.style.display = orders.length>1 ? '' : 'none';
        document.getElementById('assignBtn').textContent='🚴 Confirmer l\'assignation';
    }

    document.getElementById('multiOrdersSection').style.display='block';
    document.getElementById('addrFlow').style.display='none';
    document.getElementById('destGroup').style.display='none';
    document.getElementById('assignModalSub').textContent=_bulkOrderIds.size+' commandes · '+_assignGroups.length+' groupe'+ (_assignGroups.length>1?'s':'')+' — choisissez un chauffeur';

    selectedDriverId=null;
    document.querySelectorAll('.driver-opt').forEach(function(el){ el.classList.remove('selected'); });
    var s=document.getElementById('selectedSummary'); s.classList.remove('show');

    var first=document.querySelector('#driverList .driver-opt:not(.not-avail)');
    if(first) selectDriver(first, first.getAttribute('data-driver-id'));
    openModal('assignModal');
}

/* ── Assigner : ouverture du modal ── */
function openAssign(btn){
    _bulkMode=false;
    var orderId    = btn.getAttribute('data-id');
    var fee        = btn.getAttribute('data-fee');
    var zonePrice  = btn.getAttribute('data-zone-price');
    var dest       = btn.getAttribute('data-dest');
    var orderNum   = btn.getAttribute('data-num');
    var shopName   = btn.getAttribute('data-shop');
    var shopAddr   = btn.getAttribute('data-shop-addr');
    var clientAddr = btn.getAttribute('data-client-addr');
    /* Fallback : si delivery_fee non encore défini, utiliser le prix de la zone */
    if ((!fee || parseFloat(fee) <= 0) && zonePrice && parseFloat(zonePrice) > 0) fee = zonePrice;

    currentOrderId=orderId;
    selectedDriverId=null;
    document.getElementById('multiOrdersSection').style.display='none';
    document.getElementById('addrFlow').style.display='flex';
    document.getElementById('destGroup').style.display='block';
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
async function submitAssign(){
    if(!selectedDriverId){ toast('Choisissez un chauffeur disponible.','error'); return; }
    /* En mode multi-clients, les frais sont par groupe (pas le champ global) */
    var fee = '';
    if(!_isMultiClientBulk){
        fee=document.getElementById('assignFee').value.trim();
        if(fee===''||isNaN(Number(fee))||Number(fee)<0){ toast('Entrez des frais valides (0 ou plus).','error'); return; }
    }

    var btn=document.getElementById('assignBtn');
    btn.disabled=true;

    /* ── Mode multi (plusieurs commandes) ── */
    if(_bulkMode){
        var ok=0;

        if(_isMultiClientBulk){
            /* Groupes séparés par client : chaque groupe avec son propre frais */
            var totalOrders = _assignGroups.reduce(function(s,g){ return s+g.orders.length; },0);
            btn.textContent='⏳ Groupe 1/'+_assignGroups.length+'…';

            for(var gi=0;gi<_assignGroups.length;gi++){
                btn.textContent='⏳ Groupe '+(gi+1)+'/'+_assignGroups.length+'…';
                var group = _assignGroups[gi];
                var groupFee = parseFloat(document.getElementById('group-fee-'+gi).value)||0;

                for(var oi=0;oi<group.orders.length;oi++){
                    /* Full fee sur la 1ère commande du groupe, 0 sur les autres (même client = 1 trajet) */
                    var orderFee = oi===0 ? groupFee : 0;
                    var fd=new FormData();
                    fd.append('_token',CSRF);
                    fd.append('driver_id',selectedDriverId);
                    fd.append('delivery_fee',orderFee);
                    try{
                        var r=await fetch('/company/orders/'+group.orders[oi].orderId+'/assign',{method:'POST',body:fd});
                        var d=await r.json();
                        if(d.success) ok++;
                    }catch(e){}
                }
            }
        } else {
            /* Même client ou destinations différentes : frais global */
            var ids=Array.from(_bulkOrderIds);
            btn.textContent='⏳ 0/'+ids.length+'…';
            var isSingleClientBulk = _assignGroups.length===1 && _assignGroups[0].orders.length>1;

            for(var i=0;i<ids.length;i++){
                btn.textContent='⏳ '+(i+1)+'/'+ids.length+'…';
                /* Même client → fee uniquement sur 1ère commande */
                var orderFee = (isSingleClientBulk && i>0) ? 0 : Number(fee);
                var fd=new FormData();
                fd.append('_token',CSRF);
                fd.append('driver_id',selectedDriverId);
                fd.append('delivery_fee',orderFee);
                try{
                    var r=await fetch('/company/orders/'+ids[i]+'/assign',{method:'POST',body:fd});
                    var d=await r.json();
                    if(d.success) ok++;
                }catch(e){}
            }
        }

        closeModal('assignModal');
        clearSelection();
        if(ok>0){
            toast('✅ '+ok+' commande'+(ok>1?'s':'')+' assignée'+(ok>1?'s':'')+' à '+document.getElementById('selectedDriverName').textContent,'success');
            setTimeout(function(){ location.reload(); },1500);
        } else {
            toast('Erreur lors de l\'assignation.','error');
        }
        return;
    }

    /* ── Mode simple (une commande) ── */
    if(!currentOrderId){ toast('Aucune commande.','error'); btn.disabled=false; return; }
    btn.textContent='⏳ En cours…';
    var fd=new FormData();
    fd.append('_token',CSRF);
    fd.append('driver_id',selectedDriverId);
    fd.append('delivery_fee',fee);
    fd.append('delivery_destination',document.getElementById('assignDest').value);
    try{
        var r=await fetch('/company/orders/'+currentOrderId+'/assign',{method:'POST',body:fd});
        var data=await r.json();
        if(data.success){
            toast('✅ '+data.driver_name+' assigné · '+data.delivery_fee+' '+DEVISE,'success');
            closeModal('assignModal');
            setTimeout(function(){ location.reload(); },1200);
        } else {
            toast('Erreur : '+(data.message||'inconnue'),'error');
            btn.disabled=false; btn.textContent='🚴 Confirmer l\'assignation';
        }
    }catch(err){
        toast('Erreur réseau : '+err.message,'error');
        btn.disabled=false; btn.textContent='🚴 Confirmer l\'assignation';
    }
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

/* ── Annuler / Restaurer (unitaire) ── */
function cancelOrder(orderId, btn) {
    if (!confirm('Annuler cette commande ?')) return;
    btn.disabled = true;
    fetch('/company/orders/' + orderId + '/cancel', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (data.success) { toast('🚫 Commande annulée.', 'success'); setTimeout(() => location.reload(), 1200); }
        else { toast('Erreur lors de l\'annulation.', 'error'); btn.disabled = false; }
    }).catch(() => { toast('Erreur réseau.', 'error'); btn.disabled = false; });
}
function restoreOrder(orderId, btn) {
    if (!confirm('Restaurer cette commande en attente ?')) return;
    btn.disabled = true;
    fetch('/company/orders/' + orderId + '/restore', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (data.success) { toast('♻️ Commande restaurée.', 'success'); setTimeout(() => location.reload(), 1200); }
        else { toast('Erreur lors de la restauration.', 'error'); btn.disabled = false; }
    }).catch(() => { toast('Erreur réseau.', 'error'); btn.disabled = false; });
}

/* ── Annuler / Restaurer (bulk) ── */
async function bulkCancelOrders() {
    if (!_bulkOrderIds.size) return;
    if (!confirm('Annuler ' + _bulkOrderIds.size + ' commande(s) sélectionnée(s) ?')) return;
    try {
        const res  = await fetch('{{ route("company.orders.bulk-cancel") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ order_ids: [..._bulkOrderIds] })
        });
        const data = await res.json();
        if (data.success) {
            toast('🚫 ' + data.count + ' commande(s) annulée(s).', 'success');
            clearSelection();
            setTimeout(() => location.reload(), 1400);
        } else { toast('Erreur lors de l\'annulation.', 'error'); }
    } catch(e) { toast('Erreur réseau.', 'error'); }
}
async function bulkRestoreOrders() {
    if (!_bulkOrderIds.size) return;
    if (!confirm('Restaurer ' + _bulkOrderIds.size + ' commande(s) sélectionnée(s) ?')) return;
    try {
        const res  = await fetch('{{ route("company.orders.bulk-restore") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ order_ids: [..._bulkOrderIds] })
        });
        const data = await res.json();
        if (data.success) {
            toast('♻️ ' + data.count + ' commande(s) restaurée(s).', 'success');
            clearSelection();
            setTimeout(() => location.reload(), 1400);
        } else { toast('Erreur lors de la restauration.', 'error'); }
    } catch(e) { toast('Erreur réseau.', 'error'); }
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
