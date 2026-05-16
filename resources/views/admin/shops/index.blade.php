@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --brand:#7c3aed;--blt:#8b5cf6;--bdk:#5b21b6;--glow:rgba(124,58,237,.22);
    --sb:#1e1b4b;--sb-text:rgba(255,255,255,.88);--sb-w:268px;
    --bg:#f1f5f9;--card:#fff;--bd:rgba(0,0,0,.07);
    --text:#0f172a;--muted:#64748b;
    --green:#10b981;--gbg:rgba(16,185,129,.1);
    --amber:#f59e0b;--abg:rgba(245,158,11,.1);
    --red:#ef4444;--rbg:rgba(239,68,68,.1);
    --blue:#3b82f6;--bbg:rgba(59,130,246,.1);
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}

/* ─── SIDEBAR ─── */
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;
    position:fixed;top:0;left:0;bottom:0;z-index:200;
    overflow-y:auto;overflow-x:hidden;
    transition:transform .28s cubic-bezier(.4,0,.2,1);
    scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}
.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}

.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);
    display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;
    background:linear-gradient(135deg,#a78bfa,#7c3aed);
    display:flex;align-items:center;justify-content:center;color:#fff;
    box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}

.sb-me{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.12);
    display:flex;align-items:center;gap:10px;flex-shrink:0;background:rgba(255,255,255,.05)}
.sb-av{width:36px;height:36px;border-radius:50%;
    background:linear-gradient(135deg,#a78bfa,#6d28d9);
    display:flex;align-items:center;justify-content:center;
    font-size:14px;font-weight:900;color:#fff;flex-shrink:0;
    border:2px solid rgba(196,181,253,.5);box-shadow:0 2px 8px rgba(124,58,237,.4)}
.sb-name{font-size:13px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px}
.sb-badge{display:inline-block;font-size:9.5px;font-weight:700;color:#e9d5ff;
    background:rgba(167,139,250,.22);border:1px solid rgba(196,181,253,.4);
    padding:2px 8px;border-radius:20px;margin-top:3px;text-transform:uppercase;letter-spacing:.6px}

.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;padding:16px 18px 6px;display:flex;align-items:center;gap:6px}
.sb-sec.shop{color:#6ee7b7}
.sb-sec.livr{color:#93c5fd}
.sb-sec.fin{color:#fcd34d}
.sb-sec.plat{color:#c4b5fd}

.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;
    color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;
    border-left:3px solid transparent;transition:all .15s;cursor:pointer;letter-spacing:.1px}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700;
    box-shadow:inset 0 0 0 1px rgba(167,139,250,.15)}
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-a:hover .sb-i,.sb-a.on .sb-i{opacity:1}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}
.sb-pill.a{background:var(--amber)}

.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:10px;padding:9px 11px;
    border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;
    background:none;border:none;cursor:pointer;font-family:var(--font);
    width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7}
.sb-ft-row:hover .sb-ft-ico{opacity:1}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);
    border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;
    transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);
    animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}

/* ─── TOPBAR ─── */
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);
    display:flex;align-items:center;padding:0 22px;gap:12px;
    position:sticky;top:0;z-index:100}
.ham{display:flex;width:32px;height:32px;background:none;border:none;
    cursor:pointer;border-radius:7px;align-items:center;justify-content:center;
    color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.sb.closed{transform:translateX(-100%)}
.mn.sb-closed{margin-left:0}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}
.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-srch{display:flex;align-items:center;background:var(--bg);border:1px solid var(--bd);
    border-radius:9px;padding:0 11px;gap:7px;height:34px;width:220px;transition:all .18s}
.tb-srch:focus-within{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09);background:#fff}
.tb-srch input{border:none;background:none;font-size:12.5px;color:var(--text);width:100%;outline:none;font-family:var(--font)}
.tb-srch input::placeholder{color:var(--muted)}
.tb-acts{display:flex;align-items:center;gap:6px}
.tb-btn{width:34px;height:34px;background:none;border:1px solid var(--bd);border-radius:8px;
    cursor:pointer;display:flex;align-items:center;justify-content:center;
    color:var(--muted);transition:all .13s;text-decoration:none}
.tb-btn:hover{background:var(--bg);color:var(--brand)}
.tb-user{display:flex;align-items:center;gap:8px;padding:4px 10px 4px 5px;
    border:1px solid var(--bd);border-radius:8px;cursor:pointer;background:none;
    position:relative;transition:all .13s}
.tb-user:hover{background:var(--bg)}
.tb-uav{width:27px;height:27px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--bdk));
    display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.tb-uname{font-size:12px;font-weight:600;color:var(--text)}
.tb-urole{font-size:9.5px;color:var(--muted)}
.drop{position:absolute;top:calc(100% + 7px);right:0;background:#fff;
    border:1px solid var(--bd);border-radius:11px;padding:7px;
    box-shadow:0 8px 32px rgba(0,0,0,.13);min-width:185px;z-index:300;
    display:none;flex-direction:column;gap:2px}
.drop.open{display:flex}
.drop-i{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:7px;
    font-size:12.5px;color:var(--text);text-decoration:none;transition:background .13s;
    background:none;border:none;cursor:pointer;font-family:var(--font);font-weight:500;width:100%}
.drop-i:hover{background:var(--bg)}
.drop-i.d{color:var(--red)}.drop-i.d:hover{background:var(--rbg)}
.drop-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;opacity:.7;flex-shrink:0}
.drop-sep{height:1px;background:var(--bd);margin:4px 0}

/* ─── CONTENT ─── */
.con{flex:1;padding:26px}

/* flash */
.flash{display:flex;align-items:center;gap:10px;padding:13px 16px;border-radius:10px;margin-bottom:22px;font-size:13px;font-weight:600}
.flash.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.flash.err{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}

/* breadcrumb */
.bc{display:flex;align-items:center;gap:5px;font-size:11.5px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}
.bc .bs{color:rgba(0,0,0,.15)}

/* page header */
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:20px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px}
.ph-sub{font-size:12px;color:var(--muted)}
.ph-acts{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

/* ─── STAT CARDS ─── */
.stat-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:14px;margin-bottom:24px}
.stat-c{background:var(--card);border-radius:13px;padding:18px;border:1px solid var(--bd);
    position:relative;overflow:hidden;transition:transform .18s;cursor:default}
.stat-c:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.08)}
.stat-c::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:13px 13px 0 0}
.stat-c.p::before{background:linear-gradient(90deg,#7c3aed,#8b5cf6)}
.stat-c.g::before{background:linear-gradient(90deg,#10b981,#34d399)}
.stat-c.a::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.stat-c.r::before{background:linear-gradient(90deg,#ef4444,#f87171)}
.stat-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px}
.stat-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center}
.stat-ico.p{background:rgba(139,92,246,.12);color:#7c3aed}
.stat-ico.g{background:var(--gbg);color:#059669}
.stat-ico.a{background:var(--abg);color:#d97706}
.stat-ico.r{background:var(--rbg);color:#dc2626}
.stat-v{font-size:26px;font-weight:900;color:var(--text);letter-spacing:-1px;line-height:1;margin-bottom:4px}
.stat-l{font-size:12px;color:var(--muted);font-weight:500}

/* ─── TOOLBAR ─── */
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;
    margin-bottom:16px;flex-wrap:wrap}
.filter-g{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.filter-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;
    font-size:12px;font-weight:700;border:1px solid var(--bd);background:var(--card);
    color:var(--muted);cursor:pointer;transition:all .15s;font-family:var(--font);text-decoration:none}
.filter-btn:hover{background:var(--bg);color:var(--text)}
.filter-btn.active{background:rgba(124,58,237,.1);border-color:rgba(124,58,237,.3);color:var(--brand)}
.filter-ico{width:14px;height:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.srch-box{display:flex;align-items:center;background:var(--card);border:1px solid var(--bd);
    border-radius:9px;padding:0 12px;gap:7px;height:36px;min-width:220px;transition:all .18s}
.srch-box:focus-within{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.srch-box input{border:none;background:none;font-size:12.5px;color:var(--text);width:100%;outline:none;font-family:var(--font)}
.srch-box input::placeholder{color:var(--muted)}
.srch-ico{width:15px;height:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--muted)}

/* ─── MAIN CARD ─── */
.mc{background:var(--card);border-radius:14px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.mc-h{padding:16px 22px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px}
.mc-t{font-size:14px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px}
.mc-ico{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--brand)}

/* ─── TABLE ─── */
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse;min-width:680px}
.tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;
    letter-spacing:.5px;padding:10px 20px;background:#f8fafc;
    border-bottom:1px solid var(--bd);white-space:nowrap;text-align:left}
.tbl td{padding:14px 20px;font-size:13px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr{transition:background .13s}
.tbl tbody tr:hover{background:rgba(124,58,237,.025)}

/* shop cell */
.shop-cell{display:flex;align-items:center;gap:11px}
.shop-av{width:38px;height:38px;border-radius:10px;
    background:linear-gradient(135deg,var(--brand),#4f46e5);
    display:flex;align-items:center;justify-content:center;
    font-size:14px;font-weight:800;color:#fff;flex-shrink:0}
.shop-name{font-size:13px;font-weight:700;color:var(--text)}
.shop-meta{font-size:11px;color:var(--muted);margin-top:1px;display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.shop-meta-item{display:flex;align-items:center;gap:3px}

/* owner cell */
.owner-cell{display:flex;align-items:center;gap:9px}
.owner-av{width:30px;height:30px;border-radius:50%;
    background:linear-gradient(135deg,#10b981,#059669);
    display:flex;align-items:center;justify-content:center;
    font-size:11px;font-weight:800;color:#fff;flex-shrink:0}
.owner-name{font-size:12.5px;font-weight:600;color:var(--text)}
.owner-email{font-size:11px;color:var(--muted)}

/* badges */
.bdg{font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
.bdg.g{color:#065f46;background:var(--gbg);border:1px solid rgba(16,185,129,.2)}
.bdg.a{color:#92400e;background:var(--abg);border:1px solid rgba(245,158,11,.2)}
.bdg.r{color:#7f1d1d;background:var(--rbg);border:1px solid rgba(239,68,68,.2)}
.bdg-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.bdg.g .bdg-dot{background:#10b981}
.bdg.a .bdg-dot{background:#f59e0b}

/* action buttons */
.btn-approve{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;
    background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:12px;font-weight:700;
    border:none;cursor:pointer;font-family:var(--font);transition:all .15s}
.btn-approve:hover{transform:translateY(-1px);box-shadow:0 3px 10px rgba(16,185,129,.4)}
.btn-disable{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;
    background:var(--rbg);color:var(--red);font-size:12px;font-weight:700;
    border:1px solid rgba(239,68,68,.2);cursor:pointer;font-family:var(--font);transition:all .15s}
.btn-disable:hover{background:#fee2e2;transform:translateY(-1px)}
.btn-ico{width:13px;height:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* pagination */
.pag-wrap{padding:14px 20px;border-top:1px solid var(--bd);display:flex;justify-content:center}
.pag-wrap .pagination{gap:3px}
.pag-wrap .page-link{border-radius:8px !important;font-size:12px;font-weight:600;padding:5px 11px;transition:all .15s}
.pag-wrap .page-item.active .page-link{background:var(--brand);border-color:var(--bdk);color:#fff}

/* empty */
.empty-state{padding:56px 20px;text-align:center}
.empty-state-ico{width:56px;height:56px;border-radius:50%;background:rgba(124,58,237,.08);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;color:var(--brand)}
.empty-state-t{font-size:14px;font-weight:700;color:var(--muted)}
.empty-state-s{font-size:12px;color:var(--muted);margin-top:5px}

/* buttons */
.btn-p{display:inline-flex;align-items:center;gap:6px;padding:8px 15px;border-radius:8px;
    background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;font-size:12px;font-weight:700;
    border:none;cursor:pointer;text-decoration:none;transition:all .15s;font-family:var(--font)}
.btn-p:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}
.btn-g{display:inline-flex;align-items:center;gap:6px;padding:8px 15px;border-radius:8px;
    background:var(--bg);color:var(--muted);font-size:12px;font-weight:700;
    border:1px solid var(--bd);cursor:pointer;text-decoration:none;transition:all .15s;font-family:var(--font)}
.btn-g:hover{background:#e2e8f0;color:var(--text)}

/* ─── RESPONSIVE ─── */
@media(max-width:900px){
    .sb{transform:translateX(-100%)}
    .sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.32)}
    .sb-ov.open{display:block}
    .mn{margin-left:0}
    .ham{display:flex}
    .tb-srch{display:none}
    .stat-g{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:600px){
    .con{padding:14px}
    .tb{padding:0 14px}
    .ph{flex-direction:column}
    .toolbar{flex-direction:column;align-items:stretch}
    .filter-g{justify-content:stretch}
    .filter-btn{flex:1;justify-content:center}
}
@media(max-width:400px){.stat-g{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
    $me       = auth()->user();
    $meName   = $me->name ?? 'SuperAdmin';
    $meInit   = strtoupper(substr($meName,0,1));
    $total    = $shops->total();
    $approved = $shops->getCollection()->where('is_approved',true)->count();
    $pending  = $shops->getCollection()->where('is_approved',false)->count();

    /* ═══ BIBLIOTHÈQUE D'ICÔNES SVG PREMIUM ═══ */
    $s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
    $I = [
    'brand'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>',
    'brand_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>',
    'home'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'store'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
    'store_lg'=> '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
    'bag'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
    'box'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
    'brief'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
    'users'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'user'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    'truck'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect width="7" height="7" x="14" y="12" rx="1"/><path d="M5 17a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/><path d="M15 19a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/></svg>',
    'bike'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg>',
    'map'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/></svg>',
    'pin'     => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
    'card'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>',
    'trend'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
    'dollar'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    'star'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'ticket'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3Z"/></svg>',
    'cog'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
    'logout'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
    'menu'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>',
    'close'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    'chevron' => '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>',
    'search'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
    'globe'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
    'phone'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.34 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    'check'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
    'check_sm'=> '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
    'x_sm'    => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    'clock'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    'receipt' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"/><path d="M14 8H8m8 4H8m5 4H8"/></svg>',
    ];
@endphp

<div class="sa">

{{-- ════════ SIDEBAR ════════ --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap">{!! $I['brand_lg'] !!}</div>
        <div>
            <div class="sb-appname">{{ config('app.name','Shopio') }}</div>
            <div class="sb-apptag">Plateforme · Super Admin</div>
        </div>
        <button class="sb-close" onclick="closeSb()" title="Fermer">{!! $I['close'] !!}</button>
    </div>

    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0">
            <div class="sb-name">{{ Str::limit($meName,22) }}</div>
            <div class="sb-badge">Fondateur &amp; Développeur</div>
        </div>
    </div>

    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a">
            <span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span>
        </a>

        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a on">
            <span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span>
        </a>
        <a href="{{ route('admin.products.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['bag'] !!}</span><span>Produits</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['box'] !!}</span><span>Commandes boutiques</span>
        </a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['brief'] !!}</span><span>Vendeurs &amp; Employés</span>
        </a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['user'] !!}</span><span>Clients boutiques</span>
        </a>

        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span>
        </a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['bike'] !!}</span><span>Livreurs</span>
        </a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['map'] !!}</span><span>Zones de livraison</span>
        </a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['pin'] !!}</span><span>Suivi en temps réel</span>
        </a>

        <div class="sb-sec fin">── Finance</div>
        <a href="{{ route('admin.paiements.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span>
        </a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span>
        </a>
        <a href="{{ route('admin.revenus-boutiques.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['store'] !!}</span><span>Revenus boutiques</span>
        </a>
        <a href="{{ route('admin.revenus-entreprises.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['truck'] !!}</span><span>Revenus entreprises</span>
        </a>
        <a href="{{ route('admin.revenus.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['dollar'] !!}</span><span>Revenus plateforme</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">{!! $I['receipt'] !!}</span><span>Factures &amp; Exports</span>
        </a>

        <div class="sb-sec plat">── Plateforme</div>
        <a href="{{ route('admin.users.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span>
        </a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span>
        </a>
        <a href="{{ route('admin.support.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['ticket'] !!}</span><span>Tickets support</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span>
        </a>
    </nav>

    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row">
            <span class="sb-ft-ico">{!! $I['user'] !!}</span>Mon profil
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sb-ft-row">
                <span class="sb-ft-ico">{!! $I['logout'] !!}</span>Déconnexion
            </button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- ════════ MAIN ════════ --}}
<div class="mn">

    <header class="tb">
        <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
        <div class="tb-ttl"><b>Boutiques</b></div>
        <div class="tb-sp"></div>
        <div class="tb-srch">
            <span class="srch-ico">{!! $I['search'] !!}</span>
            <input type="text" id="tbSearch" placeholder="Rechercher une boutique…">
        </div>
        <div class="tb-acts">
            <a href="{{ route('admin.dashboard') }}" class="tb-btn" title="Dashboard">{!! $I['home'] !!}</a>
        </div>
        <div style="position:relative">
            <button class="tb-user" id="tbU" onclick="toggleDrop()">
                <div class="tb-uav">{{ $meInit }}</div>
                <div class="d-none d-sm-block" style="text-align:left">
                    <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                    <div class="tb-urole">SuperAdmin</div>
                </div>
                <span style="color:var(--muted);margin-left:2px">{!! $I['chevron'] !!}</span>
            </button>
            <div class="drop" id="drop">
                <a href="{{ route('profile.edit') }}" class="drop-i">
                    <span class="drop-ico">{!! $I['user'] !!}</span>Mon profil
                </a>
                <a href="{{ route('admin.dashboard') }}" class="drop-i">
                    <span class="drop-ico">{!! $I['home'] !!}</span>Dashboard
                </a>
                <div class="drop-sep"></div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="drop-i d">
                        <span class="drop-ico">{!! $I['logout'] !!}</span>Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="con">

        @if(session('success'))
            <div class="flash ok">{!! $I['check'] !!} {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash err">{!! $I['x_sm'] !!} {{ session('error') }}</div>
        @endif

        {{-- Breadcrumb --}}
        <div class="bc">
            <span style="display:flex;align-items:center">{!! $I['brand'] !!}</span>
            <span class="bs">›</span>
            <a href="{{ route('admin.dashboard') }}">Vue d'ensemble</a>
            <span class="bs">›</span>
            <span style="color:var(--text);font-weight:600">Boutiques</span>
        </div>

        {{-- Page header --}}
        <div class="ph">
            <div>
                <h1>Gestion des boutiques</h1>
                <div class="ph-sub">{{ $shops->total() }} boutique{{ $shops->total() > 1 ? 's' : '' }} enregistrée{{ $shops->total() > 1 ? 's' : '' }} sur la plateforme</div>
            </div>
            <div class="ph-acts">
                <a href="{{ route('admin.dashboard') }}" class="btn-g">{!! $I['home'] !!} Dashboard</a>
            </div>
        </div>

        {{-- Stats --}}
        @php
            $allShops   = \App\Models\Shop::count();
            $allApproved= \App\Models\Shop::where('is_approved',true)->count();
            $allPending = \App\Models\Shop::where('is_approved',false)->count();
        @endphp
        <div class="stat-g">
            <div class="stat-c p">
                <div class="stat-top">
                    <div class="stat-ico p">{!! $I['store_lg'] !!}</div>
                </div>
                <div class="stat-v">{{ $allShops }}</div>
                <div class="stat-l">Total boutiques</div>
            </div>
            <div class="stat-c g">
                <div class="stat-top">
                    <div class="stat-ico g">{!! $I['check'] !!}</div>
                </div>
                <div class="stat-v">{{ $allApproved }}</div>
                <div class="stat-l">Approuvées</div>
            </div>
            <div class="stat-c a">
                <div class="stat-top">
                    <div class="stat-ico a">{!! $I['clock'] !!}</div>
                </div>
                <div class="stat-v">{{ $allPending }}</div>
                <div class="stat-l">En attente</div>
            </div>
            <div class="stat-c r">
                <div class="stat-top">
                    <div class="stat-ico r">{!! $I['x_sm'] !!}</div>
                </div>
                <div class="stat-v">0</div>
                <div class="stat-l">Désactivées</div>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="toolbar">
            <div class="filter-g">
                <button class="filter-btn active" onclick="filterStatus('all',this)">
                    Toutes <span style="font-size:10px;opacity:.7">({{ $shops->total() }})</span>
                </button>
                <button class="filter-btn" onclick="filterStatus('approved',this)">
                    <span class="filter-ico" style="color:#10b981">{!! $I['check'] !!}</span> Approuvées
                </button>
                <button class="filter-btn" onclick="filterStatus('pending',this)">
                    <span class="filter-ico" style="color:#f59e0b">{!! $I['clock'] !!}</span> En attente
                </button>
            </div>
            <div class="srch-box">
                <span class="srch-ico">{!! $I['search'] !!}</span>
                <input type="text" id="tableSearch" placeholder="Filtrer par nom, propriétaire…">
            </div>
        </div>

        {{-- Table --}}
        <div class="mc">
            <div class="mc-h">
                <div class="mc-t">
                    <span class="mc-ico">{!! $I['store_lg'] !!}</span>
                    Liste des boutiques
                    <span style="font-size:11px;font-weight:600;padding:2px 9px;border-radius:20px;background:rgba(124,58,237,.1);color:var(--brand)">
                        Page {{ $shops->currentPage() }} / {{ $shops->lastPage() }}
                    </span>
                </div>
                <div style="font-size:12px;color:var(--muted)">{{ $shops->total() }} résultat{{ $shops->total() > 1 ? 's' : '' }}</div>
            </div>

            <div class="tbl-wrap">
                <table class="tbl" id="shopsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Boutique</th>
                            <th>Propriétaire</th>
                            <th>Téléphone</th>
                            <th>Adresse</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shops as $shop)
                        @php
                            $words = explode(' ', $shop->name ?? 'S');
                            $initials = strtoupper(substr($words[0],0,1)) . strtoupper(substr($words[1]??'',0,1));
                            $ownerName = $shop->owner->name ?? 'Utilisateur supprimé';
                            $ownerInit = strtoupper(substr($ownerName,0,1));
                        @endphp
                        <tr data-status="{{ $shop->is_approved ? 'approved' : 'pending' }}"
                            data-search="{{ strtolower($shop->name . ' ' . $ownerName . ' ' . ($shop->owner->email??'')) }}">
                            <td style="color:var(--muted);font-weight:600;font-size:12px">
                                {{ ($shops->currentPage()-1)*$shops->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <div class="shop-cell">
                                    <div class="shop-av">{{ $initials ?: 'S' }}</div>
                                    <div>
                                        <div class="shop-name">{{ $shop->name }}</div>
                                        <div class="shop-meta">
                                            @if($shop->country)
                                            <span class="shop-meta-item">
                                                {!! $I['globe'] !!} {{ $shop->country }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="owner-cell">
                                    <div class="owner-av">{{ $ownerInit }}</div>
                                    <div>
                                        <div class="owner-name">{{ $ownerName }}</div>
                                        <div class="owner-email">{{ $shop->owner->email ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($shop->phone)
                                    <span style="font-size:12.5px;color:var(--text);display:flex;align-items:center;gap:5px">
                                        {!! $I['phone'] !!} {{ $shop->phone }}
                                    </span>
                                @else
                                    <span style="color:var(--muted)">—</span>
                                @endif
                            </td>
                            <td>
                                @if($shop->address)
                                    <span style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:4px">
                                        {!! $I['pin'] !!} {{ Str::limit($shop->address,30) }}
                                    </span>
                                @else
                                    <span style="color:var(--muted)">—</span>
                                @endif
                            </td>
                            <td>
                                @if($shop->is_approved)
                                    <span class="bdg g"><span class="bdg-dot"></span> Approuvée</span>
                                @else
                                    <span class="bdg a">{!! $I['clock'] !!} En attente</span>
                                @endif
                            </td>
                            <td style="font-size:11.5px;color:var(--muted);white-space:nowrap">
                                {{ optional($shop->created_at)->format('d/m/Y') }}
                            </td>
                            <td>
                                <form action="{{ route('admin.shops.update', $shop) }}" method="POST" style="margin:0">
                                    @csrf
                                    @method('PUT')
                                    @if($shop->is_approved)
                                        <button type="submit" class="btn-disable">
                                            <span class="btn-ico">{!! $I['x_sm'] !!}</span> Désactiver
                                        </button>
                                    @else
                                        <button type="submit" class="btn-approve">
                                            <span class="btn-ico">{!! $I['check_sm'] !!}</span> Approuver
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-ico">{!! $I['store_lg'] !!}</div>
                                    <div class="empty-state-t">Aucune boutique trouvée</div>
                                    <div class="empty-state-s">Les boutiques enregistrées apparaîtront ici.</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($shops->hasPages())
                <div class="pag-wrap">
                    {{ $shops->links() }}
                </div>
            @endif
        </div>

    </div>{{-- /con --}}
</div>{{-- /mn --}}
</div>{{-- /sa --}}

<div id="toast" style="position:fixed;bottom:22px;right:22px;background:#1e293b;color:#fff;padding:10px 17px;border-radius:10px;font-size:12px;font-weight:600;box-shadow:0 8px 28px rgba(0,0,0,.22);transform:translateY(80px);opacity:0;transition:all .27s cubic-bezier(.4,0,.2,1);pointer-events:none;z-index:9999"></div>

@endsection

@push('scripts')
<script>
/* sidebar */
const sb=document.getElementById('sb'),ov=document.getElementById('sbOv'),mn=document.querySelector('.mn');
function closeSb(){sb.classList.remove('open');ov.classList.remove('open');sb.classList.add('closed');mn.classList.add('sb-closed')}
function toggleSb(){if(sb.classList.contains('closed')){sb.classList.remove('closed');mn.classList.remove('sb-closed')}else{sb.classList.toggle('open');ov.classList.toggle('open')}}

/* dropdown */
const dr=document.getElementById('drop');
function toggleDrop(){dr.classList.toggle('open')}
document.addEventListener('click',e=>{
    const b=document.getElementById('tbU');
    if(b&&!b.contains(e.target)&&!dr.contains(e.target))dr.classList.remove('open');
});

/* toast */
let _t;
function nt(msg='Bientôt disponible'){
    const el=document.getElementById('toast');
    el.textContent=msg;el.style.transform='translateY(0)';el.style.opacity='1';
    clearTimeout(_t);
    _t=setTimeout(()=>{el.style.transform='translateY(80px)';el.style.opacity='0';},2800);
}

/* filter by status */
function filterStatus(status, btn){
    document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#shopsTable tbody tr[data-status]').forEach(tr=>{
        tr.style.display = (status==='all' || tr.dataset.status===status) ? '' : 'none';
    });
}

/* live search */
function liveSearch(val){
    const q=val.toLowerCase().trim();
    document.querySelectorAll('#shopsTable tbody tr[data-search]').forEach(tr=>{
        tr.style.display = (!q || tr.dataset.search.includes(q)) ? '' : 'none';
    });
}
document.getElementById('tableSearch').addEventListener('input',e=>liveSearch(e.target.value));
document.getElementById('tbSearch').addEventListener('input',e=>liveSearch(e.target.value));
</script>
@endpush
