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
    --indigo:#6366f1;--ibg:rgba(99,102,241,.1);
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}

/* ─── SIDEBAR ─── */
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-me{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:10px;flex-shrink:0;background:rgba(255,255,255,.05)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;flex-shrink:0;border:2px solid rgba(196,181,253,.5)}
.sb-name{font-size:13px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px}
.sb-badge{display:inline-block;font-size:9.5px;font-weight:700;color:#e9d5ff;background:rgba(167,139,250,.22);border:1px solid rgba(196,181,253,.4);padding:2px 8px;border-radius:20px;margin-top:3px;text-transform:uppercase;letter-spacing:.6px}
.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;padding:16px 18px 6px;display:flex;align-items:center;gap:6px}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s;cursor:pointer}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700}
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}.sb-pill.a{background:var(--amber)}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}
.mn.sb-closed{margin-left:0}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-btn{width:34px;height:34px;background:none;border:1px solid var(--bd);border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:15px;transition:all .13s;text-decoration:none}
.tb-btn:hover{background:var(--bg);color:var(--text)}
.tb-user{display:flex;align-items:center;gap:8px;padding:4px 10px 4px 5px;border:1px solid var(--bd);border-radius:8px;cursor:pointer;background:none;position:relative;transition:all .13s}
.tb-user:hover{background:var(--bg)}
.tb-uav{width:27px;height:27px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--bdk));display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.tb-uname{font-size:12px;font-weight:600;color:var(--text)}.tb-urole{font-size:9.5px;color:var(--muted)}
.drop{position:absolute;top:calc(100% + 7px);right:0;background:#fff;border:1px solid var(--bd);border-radius:11px;padding:7px;box-shadow:0 8px 32px rgba(0,0,0,.13);min-width:185px;z-index:300;display:none;flex-direction:column;gap:2px}
.drop.open{display:flex}
.drop-i{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:7px;font-size:12.5px;color:var(--text);text-decoration:none;transition:background .13s;background:none;border:none;cursor:pointer;font-family:var(--font);font-weight:500;width:100%}
.drop-i:hover{background:var(--bg)}.drop-i.d{color:var(--red)}.drop-i.d:hover{background:var(--rbg)}
.drop-sep{height:1px;background:var(--bd);margin:4px 0}

/* ─── CONTENT ─── */
.con{flex:1;padding:26px}
.flash{display:flex;align-items:center;gap:10px;padding:13px 16px;border-radius:10px;margin-bottom:22px;font-size:13px;font-weight:600}
.flash.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.flash.err{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}
.bc{display:flex;align-items:center;gap:5px;font-size:11.5px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}.bc .bs{color:rgba(0,0,0,.15)}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:20px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px}
.ph-sub{font-size:12px;color:var(--muted)}

/* ─── STAT CARDS ─── */
.stat-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:22px}
.stat-c{background:var(--card);border-radius:13px;padding:16px 18px;border:1px solid var(--bd);position:relative;overflow:hidden;transition:transform .18s;cursor:default}
.stat-c:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.08)}
.stat-c::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:13px 13px 0 0}
.stat-c.tot::before{background:linear-gradient(90deg,#7c3aed,#8b5cf6)}
.stat-c.ouv::before{background:linear-gradient(90deg,#10b981,#34d399)}
.stat-c.fer::before{background:linear-gradient(90deg,#94a3b8,#cbd5e1)}
.stat-c.nrp::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.stat-row{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px}
.stat-ico{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center}
.stat-ico.tot{background:rgba(139,92,246,.12)}
.stat-ico.ouv{background:rgba(16,185,129,.12)}
.stat-ico.fer{background:rgba(148,163,184,.12)}
.stat-ico.nrp{background:rgba(245,158,11,.12)}
.stat-v{font-size:22px;font-weight:900;color:var(--text);letter-spacing:-.8px;line-height:1;margin-bottom:3px}
.stat-l{font-size:11px;color:var(--muted);font-weight:600}

/* ─── FILTER ─── */
.filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px 18px}
.filter-bar label{font-size:11.5px;font-weight:700;color:var(--muted);white-space:nowrap}
.f-sel{padding:7px 11px;border-radius:8px;border:1px solid var(--bd);background:var(--bg);font-size:12.5px;color:var(--text);font-family:var(--font);outline:none;cursor:pointer;min-width:150px}
.f-sel:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.f-input{padding:7px 11px;border-radius:8px;border:1px solid var(--bd);background:var(--bg);font-size:12.5px;color:var(--text);font-family:var(--font);outline:none;flex:1;min-width:180px}
.f-input:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09);background:#fff}
.f-input::placeholder{color:var(--muted)}
.btn-search{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:8px;background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;font-family:var(--font);transition:all .15s}
.btn-search:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}
.btn-reset{display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;background:var(--bg);color:var(--muted);font-size:12px;font-weight:700;border:1px solid var(--bd);cursor:pointer;text-decoration:none;font-family:var(--font);transition:all .15s}
.btn-reset:hover{background:#e2e8f0;color:var(--text)}

/* ─── QS CHIPS ─── */
.qs-row{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.qs{display:inline-flex;align-items:center;gap:5px;padding:6px 13px;border-radius:20px;font-size:11.5px;font-weight:700;border:1px solid var(--bd);background:var(--card);color:var(--muted);cursor:pointer;text-decoration:none;transition:all .15s}
.qs:hover{background:var(--bg)}
.qs.all{background:rgba(124,58,237,.1);border-color:rgba(124,58,237,.3);color:var(--brand)}
.qs.ouv{background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3);color:#065f46}
.qs.fer{background:rgba(148,163,184,.1);border-color:rgba(148,163,184,.3);color:#475569}

/* ─── TABLE ─── */
.mc{background:var(--card);border-radius:14px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.mc-h{padding:15px 22px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.mc-t{font-size:14px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px}
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse;min-width:760px}
.tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;padding:10px 18px;background:#f8fafc;border-bottom:1px solid var(--bd);white-space:nowrap;text-align:left}
.tbl td{padding:13px 18px;font-size:12.5px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr{transition:background .13s}.tbl tbody tr:hover{background:rgba(124,58,237,.02)}

/* badges */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
.badge.open{background:rgba(16,185,129,.12);color:#065f46;border:1px solid rgba(16,185,129,.25)}
.badge.closed{background:rgba(148,163,184,.12);color:#475569;border:1px solid rgba(148,163,184,.25)}

/* boutique pill */
.shop-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;background:rgba(99,102,241,.08);font-size:11px;font-weight:700;color:#3730a3;border:1px solid rgba(99,102,241,.2)}

/* user cell */
.u-cell{display:flex;align-items:center;gap:8px}
.u-av{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0}
.u-name{font-size:12.5px;font-weight:700;color:var(--text)}
.u-role{font-size:10.5px;color:var(--muted)}

/* actions */
.btn-view{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:7px;background:rgba(124,58,237,.1);color:var(--brand);font-size:12px;font-weight:700;text-decoration:none;border:1px solid rgba(124,58,237,.2);transition:all .15s;white-space:nowrap}
.btn-view:hover{background:var(--brand);color:#fff}

/* msg count */
.msg-count{display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;color:var(--muted)}
.msg-dot{width:6px;height:6px;border-radius:50%;background:var(--indigo);flex-shrink:0}

/* empty */
.empty{text-align:center;padding:60px 20px}
.empty-ico{display:flex;justify-content:center;margin-bottom:12px}
.empty-t{font-size:15px;font-weight:800;color:var(--text);margin-bottom:6px}
.empty-s{font-size:13px;color:var(--muted)}

/* pagination */
.pag{display:flex;justify-content:center;padding:16px 22px;border-top:1px solid var(--bd);gap:4px}
.pag a,.pag span{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;font-size:12.5px;font-weight:700;text-decoration:none;border:1px solid var(--bd);color:var(--muted);background:var(--card);transition:all .13s}
.pag a:hover{background:var(--bg);color:var(--text)}
.pag span.cur{background:var(--brand);color:#fff;border-color:var(--brand)}

/* ticket-id */
.tk-id{font-size:12px;font-weight:800;color:var(--brand);background:rgba(124,58,237,.08);padding:3px 8px;border-radius:6px;display:inline-block}

@media(max-width:900px){
    .sb{transform:translateX(-100%)}.sb.open{transform:none}
    .mn{margin-left:0}.sb-ov.open{display:block}
    .tbl{min-width:640px}
}
@media(max-width:600px){
    .con{padding:16px}
    .stat-g{grid-template-columns:repeat(2,1fr)}
    .filter-bar{flex-direction:column;align-items:stretch}
    .f-input,.f-sel{width:100%}
}
</style>
@endpush

@section('content')
@php
$s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
$sl= 'stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"';
$I = [
'brand_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>',
'home'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>',
'store'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9h18v1a4 4 0 0 1-8 0 4 4 0 0 1-8 0V9z"/><path d="M4 10v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9"/><path d="M9 21v-7h6v7"/><path d="M3 9l2.5-5h13L21 9"/></svg>',
'bag'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
'box'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>',
'brief'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="12.01"/></svg>',
'user'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
'users'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
'truck'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
'bike'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6h-3l-2 5H5.5"/><path d="M9 11l3-5h5l2 6h-4.5"/></svg>',
'map'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>',
'pin'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
'card'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
'trend'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
'dollar' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
'receipt'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1z"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="14" y2="13"/></svg>',
'star'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
'ticket' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg>',
'cog'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
'logout' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
'profile'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
'close'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
'menu'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
'search' => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
'check'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="20 6 9 17 4 12"/></svg>',
'lock'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
'alert'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
'msg'    => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
'eye'    => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
'store_sm'=> '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9h18v1a4 4 0 0 1-8 0 4 4 0 0 1-8 0V9z"/><path d="M4 10v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9"/><path d="M9 21v-7h6v7"/><path d="M3 9l2.5-5h13L21 9"/></svg>',
'ticket_stat'=> '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg>',
'check_stat'=> '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="20 6 9 17 4 12"/></svg>',
'lock_stat' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
];
    $u      = Auth::user();
    $meInit = strtoupper(substr($u->name ?? 'S', 0, 1));
    $meName = $u->name ?? 'SuperAdmin';

    $currentStatus = request('status', '');
    $currentShop   = request('shop_id', '');
    $currentSearch = request('search', '');
@endphp

<div class="sa">

{{-- ════════ SIDEBAR ════════ --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap" style="color:#fff">{!! $I['brand_lg'] !!}</div>
        <div>
            <div class="sb-appname">{{ config('app.name','Shopio') }}</div>
            <div class="sb-apptag">Plateforme · Super Admin</div>
        </div>
        <button class="sb-close" onclick="closeSb()" title="Fermer la sidebar">{!! $I['close'] !!}</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0">
            <div class="sb-name">{{ Str::limit($meName,22) }}</div>
            <div class="sb-badge">Fondateur &amp; Développeur</div>
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a"><span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span></a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span></a>
        <a href="{{ route('admin.products.index') }}" class="sb-a"><span class="sb-i">{!! $I['bag'] !!}</span><span>Produits</span></a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a"><span class="sb-i">{!! $I['box'] !!}</span><span>Commandes boutiques</span></a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['brief'] !!}</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">{!! $I['user'] !!}</span><span>Clients boutiques</span></a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span></a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['bike'] !!}</span><span>Livreurs</span></a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a"><span class="sb-i">{!! $I['map'] !!}</span><span>Zones de livraison</span></a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a"><span class="sb-i">{!! $I['pin'] !!}</span><span>Suivi en temps réel</span></a>
        <div class="sb-sec fin">── Finance</div>
        <a href="{{ route('admin.paiements.index') }}" class="sb-a"><span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span></a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a"><span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span></a>
        <a href="{{ route('admin.revenus-boutiques.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Revenus boutiques</span></a>
        <a href="{{ route('admin.revenus-entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Revenus entreprises</span></a>
        <a href="{{ route('admin.revenus.index') }}" class="sb-a"><span class="sb-i">{!! $I['dollar'] !!}</span><span>Revenus plateforme</span></a>
        <div class="sb-sec plat">── Plateforme</div>
        <a href="{{ route('admin.users.index') }}" class="sb-a"><span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span></a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a"><span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span></a>
        <a href="{{ route('admin.support.index') }}" class="sb-a on"><span class="sb-i">{!! $I['ticket'] !!}</span><span>Tickets support</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['profile'] !!}</span>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf<button type="submit" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['logout'] !!}</span>Déconnexion</button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- ════════ MAIN ════════ --}}
<div class="mn" id="mn">
    <header class="tb">
        <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
        <div class="tb-ttl" style="display:flex;align-items:center;gap:6px"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg> <b>Tickets support</b></div>
        <div class="tb-sp"></div>
        <a href="{{ route('admin.dashboard') }}" class="tb-btn" title="Dashboard" style="display:flex;align-items:center;justify-content:center">{!! $I['home'] !!}</a>
        <div style="position:relative">
            <button class="tb-user" id="tbU" onclick="toggleDrop()">
                <div class="tb-uav">{{ $meInit }}</div>
                <div style="text-align:left;display:none" class="d-sm-block">
                    <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                    <div class="tb-urole">SuperAdmin</div>
                </div>
                <span style="font-size:11px;color:var(--muted)">▾</span>
            </button>
            <div class="drop" id="drop">
                <a href="{{ route('profile.edit') }}" class="drop-i">{!! $I['profile'] !!} Mon profil</a>
                <a href="{{ route('admin.dashboard') }}" class="drop-i">{!! $I['home'] !!} Dashboard</a>
                <div class="drop-sep"></div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf<button type="submit" class="drop-i d">{!! $I['logout'] !!} Déconnexion</button>
                </form>
            </div>
        </div>
    </header>

    <div class="con">

        @if(session('success'))
            <div class="flash ok"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> {{ session('success') }}</div>
        @endif

        <div class="bc">
            <span style="display:flex;color:var(--brand)">{!! $I['brand_lg'] !!}</span><span class="bs">›</span>
            <a href="{{ route('admin.dashboard') }}">Vue d'ensemble</a>
            <span class="bs">›</span>
            <span style="color:var(--text);font-weight:600">Tickets support</span>
        </div>

        <div class="ph">
            <div>
                <h1 style="display:flex;align-items:center;gap:8px"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg>Tickets support</h1>
                <div class="ph-sub">Messages reçus des boutiques — répondez directement depuis chaque ticket</div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="stat-g">
            <div class="stat-c tot">
                <div class="stat-row"><div class="stat-ico tot" style="color:#8b5cf6">{!! $I['ticket_stat'] !!}</div></div>
                <div class="stat-v">{{ $stats['total'] }}</div>
                <div class="stat-l">Total tickets</div>
            </div>
            <div class="stat-c ouv">
                <div class="stat-row"><div class="stat-ico ouv" style="color:#10b981">{!! $I['check_stat'] !!}</div></div>
                <div class="stat-v">{{ $stats['open'] }}</div>
                <div class="stat-l">Ouverts</div>
            </div>
            <div class="stat-c fer">
                <div class="stat-row"><div class="stat-ico fer" style="color:#94a3b8">{!! $I['lock_stat'] !!}</div></div>
                <div class="stat-v">{{ $stats['closed'] }}</div>
                <div class="stat-l">Fermés</div>
            </div>
            <div class="stat-c nrp">
                <div class="stat-row"><div class="stat-ico nrp" style="color:#f59e0b">{!! $I['alert'] !!}</div></div>
                <div class="stat-v">{{ $stats['pending'] }}</div>
                <div class="stat-l">En attente de réponse</div>
            </div>
        </div>

        {{-- Quick filters --}}
        <div class="qs-row">
            <a href="{{ route('admin.support.index', array_merge(request()->except('status','page'), [])) }}"
               class="qs {{ !$currentStatus ? 'all' : '' }}">Tous ({{ $stats['total'] }})</a>
            <a href="{{ route('admin.support.index', array_merge(request()->except('status','page'), ['status'=>'open'])) }}"
               class="qs {{ $currentStatus==='open' ? 'ouv' : '' }}">{!! $I['check_stat'] !!} Ouverts ({{ $stats['open'] }})</a>
            <a href="{{ route('admin.support.index', array_merge(request()->except('status','page'), ['status'=>'closed'])) }}"
               class="qs {{ $currentStatus==='closed' ? 'fer' : '' }}">{!! $I['lock'] !!} Fermés ({{ $stats['closed'] }})</a>
        </div>

        {{-- Filter bar --}}
        <form method="GET" action="{{ route('admin.support.index') }}" class="filter-bar">
            @if($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            <label style="display:flex;align-items:center">{!! $I['store_sm'] !!}</label>
            <select name="shop_id" class="f-sel" onchange="this.form.submit()">
                <option value="">Toutes les boutiques</option>
                @foreach($shops as $s)
                    <option value="{{ $s->id }}" {{ (string)$currentShop===(string)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <input type="text" name="search" class="f-input" placeholder="Sujet, créateur…" value="{{ $currentSearch }}">
            <div style="display:flex;gap:6px">
                <button type="submit" class="btn-search">{!! $I['search'] !!} Filtrer</button>
                @if($currentStatus || $currentShop || $currentSearch)
                    <a href="{{ route('admin.support.index') }}" class="btn-reset">{!! $I['close'] !!} Effacer</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="mc">
            <div class="mc-h">
                <div class="mc-t">{!! $I['ticket_stat'] !!} {{ $tickets->total() }} ticket{{ $tickets->total()>1?'s':'' }}</div>
            </div>
            @if($tickets->isEmpty())
                <div class="empty">
                    <div class="empty-ico" style="opacity:.2"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg></div>
                    <div class="empty-t">Aucun ticket pour le moment</div>
                    <div class="empty-s">Les boutiques peuvent vous contacter depuis leur espace support</div>
                </div>
            @else
            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Boutique</th>
                            <th>Créateur</th>
                            <th>Sujet</th>
                            <th>Messages</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr>
                            <td><span class="tk-id">#{{ $ticket->id }}</span></td>
                            <td>
                                @if($ticket->shop)
                                    <span class="shop-pill">{!! $I['store_sm'] !!} {{ Str::limit($ticket->shop->name, 20) }}</span>
                                @else
                                    <span style="color:var(--muted);font-size:12px">—</span>
                                @endif
                            </td>
                            <td>
                                @if($ticket->creator)
                                <div class="u-cell">
                                    <div class="u-av">{{ strtoupper(substr($ticket->creator->name,0,1)) }}</div>
                                    <div>
                                        <div class="u-name">{{ Str::limit($ticket->creator->name,18) }}</div>
                                        <div class="u-role">{{ ucfirst($ticket->creator->role) }}</div>
                                    </div>
                                </div>
                                @else
                                    <span style="color:var(--muted)">—</span>
                                @endif
                            </td>
                            <td style="max-width:260px">
                                <div style="font-weight:700;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    {{ $ticket->subject }}
                                </div>
                                @if($ticket->messages_count > 0)
                                    <div style="font-size:11px;color:var(--muted);margin-top:2px">
                                        Dernier message {{ $ticket->updated_at->diffForHumans() }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="msg-count">
                                    <span class="msg-dot"></span>
                                    {{ $ticket->messages_count }}
                                </div>
                            </td>
                            <td>
                                @if($ticket->status === 'open')
                                    <span class="badge open">{!! $I['check'] !!} Ouvert</span>
                                @else
                                    <span class="badge closed">{!! $I['lock'] !!} Fermé</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;font-size:12px;color:var(--muted)">
                                {{ $ticket->created_at->format('d/m/Y') }}<br>
                                <span style="font-size:11px">{{ $ticket->created_at->format('H:i') }}</span>
                            </td>
                            <td>
                                <a href="{{ route('support.show', $ticket) }}" class="btn-view">
                                    {!! $I['msg'] !!} Voir
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($tickets->hasPages())
            <div class="pag">
                {{-- Précédent --}}
                @if($tickets->onFirstPage())
                    <span style="opacity:.4">‹</span>
                @else
                    <a href="{{ $tickets->previousPageUrl() }}">‹</a>
                @endif
                {{-- Pages --}}
                @foreach($tickets->getUrlRange(max(1,$tickets->currentPage()-2), min($tickets->lastPage(),$tickets->currentPage()+2)) as $page => $url)
                    @if($page == $tickets->currentPage())
                        <span class="cur">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
                {{-- Suivant --}}
                @if($tickets->hasMorePages())
                    <a href="{{ $tickets->nextPageUrl() }}">›</a>
                @else
                    <span style="opacity:.4">›</span>
                @endif
            </div>
            @endif
            @endif
        </div>

    </div>{{-- /con --}}
</div>{{-- /mn --}}
</div>{{-- /sa --}}
@endsection

@push('scripts')
<script>
function toggleSb(){const s=document.getElementById('sb'),m=document.getElementById('mn'),o=document.getElementById('sbOv');s.classList.toggle('open');s.classList.toggle('closed');m.classList.toggle('sb-closed');o.classList.toggle('open')}
function closeSb(){const s=document.getElementById('sb'),m=document.getElementById('mn'),o=document.getElementById('sbOv');s.classList.remove('open');s.classList.add('closed');m.classList.add('sb-closed');o.classList.remove('open')}
function toggleDrop(){document.getElementById('drop').classList.toggle('open')}
document.addEventListener('click',e=>{const d=document.getElementById('drop'),b=document.getElementById('tbU');if(!d.contains(e.target)&&!b.contains(e.target))d.classList.remove('open')});
function nt(){alert('Bientôt disponible !');}
</script>
@endpush
