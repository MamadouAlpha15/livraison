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
    --teal:#14b8a6;--tealbg:rgba(20,184,166,.1);
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}

/* ─── SIDEBAR ─── */
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}.mn.sb-closed{margin-left:0}
.sb-me{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:10px;flex-shrink:0;background:rgba(255,255,255,.05)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;flex-shrink:0;border:2px solid rgba(196,181,253,.5)}
.sb-name{font-size:13px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px}
.sb-badge{display:inline-block;font-size:9.5px;font-weight:700;color:#e9d5ff;background:rgba(167,139,250,.22);border:1px solid rgba(196,181,253,.4);padding:2px 8px;border-radius:20px;margin-top:3px;text-transform:uppercase;letter-spacing:.6px}
.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;padding:16px 18px 6px;display:flex;align-items:center;gap:6px}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s;cursor:pointer;letter-spacing:.1px}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700;box-shadow:inset 0 0 0 1px rgba(167,139,250,.15)}
.sb-i{width:18px;height:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-a:hover .sb-i,.sb-a.on .sb-i{opacity:1}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.sb-ft-ico{width:15px;height:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.8}
.sb-ft-row:hover .sb-ft-ico{opacity:1}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:7px}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-btn{width:34px;height:34px;background:none;border:1px solid var(--bd);border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;text-decoration:none}
.tb-btn:hover{background:var(--bg);color:var(--text)}
.tb-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center}
.tb-user{display:flex;align-items:center;gap:8px;padding:4px 10px 4px 5px;border:1px solid var(--bd);border-radius:8px;cursor:pointer;background:none;position:relative;transition:all .13s}
.tb-user:hover{background:var(--bg)}
.tb-uav{width:27px;height:27px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--bdk));display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.tb-uname{font-size:12px;font-weight:600;color:var(--text)}.tb-urole{font-size:9.5px;color:var(--muted)}
.tb-chev{width:10px;height:10px;display:flex;align-items:center;justify-content:center;color:var(--muted)}
.drop{position:absolute;top:calc(100% + 7px);right:0;background:#fff;border:1px solid var(--bd);border-radius:11px;padding:7px;box-shadow:0 8px 32px rgba(0,0,0,.13);min-width:185px;z-index:300;display:none;flex-direction:column;gap:2px}
.drop.open{display:flex}
.drop-i{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:7px;font-size:12.5px;color:var(--text);text-decoration:none;transition:background .13s;background:none;border:none;cursor:pointer;font-family:var(--font);font-weight:500;width:100%}
.drop-i:hover{background:var(--bg)}.drop-i.d{color:var(--red)}.drop-i.d:hover{background:var(--rbg)}
.drop-sep{height:1px;background:var(--bd);margin:4px 0}
.drop-ico{width:14px;height:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* ─── CONTENT ─── */
.con{flex:1;padding:26px}
.bc{display:flex;align-items:center;gap:5px;font-size:11.5px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}.bc .bs{color:rgba(0,0,0,.15)}
.bc-ico{width:13px;height:13px;display:flex;align-items:center;justify-content:center;color:var(--brand)}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:20px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px}
.ph-sub{font-size:12px;color:var(--muted)}

/* ─── STAT CARDS ─── */
.stat-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(165px,1fr));gap:12px;margin-bottom:22px}
.stat-c{background:var(--card);border-radius:13px;padding:18px;border:1px solid var(--bd);position:relative;overflow:hidden;transition:transform .18s;cursor:default}
.stat-c:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.08)}
.stat-c::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:13px 13px 0 0}
.stat-c.tot::before{background:linear-gradient(90deg,#7c3aed,#8b5cf6)}
.stat-c.act::before{background:linear-gradient(90deg,#10b981,#34d399)}
.stat-c.ina::before{background:linear-gradient(90deg,#94a3b8,#cbd5e1)}
.stat-c.dep::before{background:linear-gradient(90deg,#14b8a6,#2dd4bf)}
.stat-c.cmd::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.stat-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px}
.stat-ico.tot{background:rgba(139,92,246,.12);color:#8b5cf6}
.stat-ico.act{background:var(--gbg);color:var(--green)}
.stat-ico.ina{background:rgba(148,163,184,.12);color:#94a3b8}
.stat-ico.dep{background:var(--tealbg);color:var(--teal)}
.stat-ico.cmd{background:var(--abg);color:var(--amber)}
.stat-v{font-size:22px;font-weight:900;color:var(--text);letter-spacing:-.8px;line-height:1;margin-bottom:3px}
.stat-v.sm{font-size:15px;letter-spacing:-.3px}
.stat-l{font-size:11px;color:var(--muted);font-weight:600}

/* ─── FILTER ─── */
.filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap;background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px 18px}
.act-chips{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.ac{display:inline-flex;align-items:center;gap:5px;padding:5px 13px;border-radius:20px;font-size:11.5px;font-weight:700;border:1px solid var(--bd);background:var(--card);color:var(--muted);cursor:pointer;text-decoration:none;transition:all .15s}
.ac:hover{background:var(--bg)}
.ac.all.on{background:rgba(124,58,237,.1);border-color:rgba(124,58,237,.3);color:var(--brand)}
.ac.act.on{background:var(--gbg);border-color:rgba(16,185,129,.3);color:#065f46}
.ac.ina.on{background:rgba(148,163,184,.12);border-color:rgba(148,163,184,.3);color:var(--muted)}
.ac-ico{width:12px;height:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.f-sel{padding:7px 11px;border-radius:8px;border:1px solid var(--bd);background:var(--bg);font-size:12.5px;color:var(--text);font-family:var(--font);outline:none;cursor:pointer}
.f-sel:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.f-input{padding:7px 12px;border-radius:8px;border:1px solid var(--bd);background:var(--bg);font-size:12.5px;color:var(--text);font-family:var(--font);outline:none;flex:1;min-width:200px}
.f-input:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09);background:#fff}
.f-input::placeholder{color:var(--muted)}
.btn-search{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:8px;background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;font-family:var(--font);transition:all .15s}
.btn-search:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}
.btn-reset{display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;background:var(--bg);color:var(--muted);font-size:12px;font-weight:700;border:1px solid var(--bd);cursor:pointer;text-decoration:none;font-family:var(--font);transition:all .15s}
.btn-reset:hover{background:#e2e8f0;color:var(--text)}
.srch-ico{width:13px;height:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.f-ico{width:13px;height:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--muted)}

/* ─── TABLE ─── */
.mc{background:var(--card);border-radius:14px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.mc-h{padding:15px 22px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px}
.mc-t{font-size:14px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px}
.mc-ico{width:18px;height:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse;min-width:750px}
.tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;padding:10px 18px;background:#f8fafc;border-bottom:1px solid var(--bd);white-space:nowrap;text-align:left}
.tbl td{padding:13px 18px;font-size:12.5px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr{transition:background .13s}.tbl tbody tr:hover{background:rgba(124,58,237,.02)}

/* client cell */
.u-cell{display:flex;align-items:center;gap:10px}
.u-av{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0}
.u-name{font-size:13px;font-weight:700;color:var(--text)}
.u-sub{font-size:11px;color:var(--muted)}
.u-phone{font-size:11px;color:var(--muted);display:flex;align-items:center;gap:3px;margin-top:1px}
.phone-ico{width:11px;height:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* country */
.globe-ico{width:20px;height:20px;display:flex;align-items:center;justify-content:center;color:var(--muted);opacity:.5}

/* badges */
.cmd-bdg{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:800}
.cmd-bdg.top{background:rgba(124,58,237,.1);color:var(--brand)}
.cmd-bdg.mid{background:var(--bbg);color:#1d4ed8}
.cmd-bdg.low{background:rgba(100,116,139,.1);color:var(--muted)}
.cmd-bdg.zero{background:var(--rbg);color:#7f1d1d}
.cmd-ico{width:12px;height:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

.dep-val{font-size:13px;font-weight:800;color:var(--text)}
.dep-sub{font-size:10.5px;color:var(--muted)}
.last-order{font-size:11.5px;font-weight:600;color:var(--text)}
.last-order-sub{font-size:10.5px;color:var(--muted)}

/* chip-inline */
.chip-ico{width:11px;height:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.pill-ico{width:11px;height:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* pagination */
.pag-wrap{padding:14px 20px;border-top:1px solid var(--bd);display:flex;justify-content:center}
.pag-wrap .page-item.active .page-link{background:var(--brand);border-color:var(--bdk);color:#fff}
.pag-wrap .page-link{border-radius:8px !important;font-size:12px;font-weight:600;padding:5px 11px}

/* empty */
.empty-state{padding:56px 20px;text-align:center}
.empty-ico{width:64px;height:64px;border-radius:50%;background:rgba(100,116,139,.08);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;color:rgba(100,116,139,.35)}
.empty-t{font-size:14px;font-weight:700;color:var(--muted)}
.empty-s{font-size:12px;color:var(--muted);margin-top:5px}

@media(max-width:900px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.32)}
    .sb-ov.open{display:block}.mn{margin-left:0}
    .stat-g{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:640px){
    .con{padding:13px}.tb{padding:0 13px}
    .ph{flex-direction:column}.stat-g{grid-template-columns:repeat(2,1fr)}
    .filter-bar{flex-direction:column;align-items:stretch}
}
</style>
@endpush

@section('content')
@php
    $me            = auth()->user();
    $meName        = $me->name ?? 'SuperAdmin';
    $meInit        = strtoupper(substr($meName,0,1));
    $currentAct    = request('activity','');
    $currentSearch = request('search','');
    $currentShop   = request('shop_id','');

    $avatarColors = ['#7c3aed','#10b981','#3b82f6','#f59e0b','#ef4444','#14b8a6','#6366f1','#ec4899','#8b5cf6','#06b6d4'];

    $countryFlags = [
        'GN'=>'🇬🇳','SN'=>'🇸🇳','CI'=>'🇨🇮','ML'=>'🇲🇱','BF'=>'🇧🇫',
        'CM'=>'🇨🇲','MA'=>'🇲🇦','FR'=>'🇫🇷','US'=>'🇺🇸','GB'=>'🇬🇧',
    ];

    $s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
    $I = [
        'brand'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" '.$s.'/></svg>',
        'home'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" '.$s.'/><polyline points="9 22 9 12 15 12 15 22" '.$s.'/></svg>',
        'store'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" '.$s.'/><path d="M3 9l2.45-4.9A2 2 0 017.24 3h9.52a2 2 0 011.8 1.1L21 9" '.$s.'/><line x1="12" y1="3" x2="12" y2="9" '.$s.'/></svg>',
        'store_sm' => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" '.$s.'/><path d="M3 9l2.45-4.9A2 2 0 017.24 3h9.52a2 2 0 011.8 1.1L21 9" '.$s.'/><line x1="12" y1="3" x2="12" y2="9" '.$s.'/></svg>',
        'bag'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" '.$s.'/><line x1="3" y1="6" x2="21" y2="6" '.$s.'/><path d="M16 10a4 4 0 01-8 0" '.$s.'/></svg>',
        'box'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" '.$s.'/><polyline points="3.27 6.96 12 12.01 20.73 6.96" '.$s.'/><line x1="12" y1="22.08" x2="12" y2="12" '.$s.'/></svg>',
        'box_lg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" '.$s.'/><polyline points="3.27 6.96 12 12.01 20.73 6.96" '.$s.'/><line x1="12" y1="22.08" x2="12" y2="12" '.$s.'/></svg>',
        'box_sm'   => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" '.$s.'/><polyline points="3.27 6.96 12 12.01 20.73 6.96" '.$s.'/><line x1="12" y1="22.08" x2="12" y2="12" '.$s.'/></svg>',
        'brief'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="7" width="20" height="14" rx="2" ry="2" '.$s.'/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16" '.$s.'/></svg>',
        'users'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" '.$s.'/><circle cx="9" cy="7" r="4" '.$s.'/><path d="M23 21v-2a4 4 0 00-3-3.87" '.$s.'/><path d="M16 3.13a4 4 0 010 7.75" '.$s.'/></svg>',
        'users_lg' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" '.$s.'/><circle cx="9" cy="7" r="4" '.$s.'/><path d="M23 21v-2a4 4 0 00-3-3.87" '.$s.'/><path d="M16 3.13a4 4 0 010 7.75" '.$s.'/></svg>',
        'user'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" '.$s.'/><circle cx="12" cy="7" r="4" '.$s.'/></svg>',
        'truck'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" '.$s.'/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8" '.$s.'/><circle cx="5.5" cy="18.5" r="2.5" '.$s.'/><circle cx="18.5" cy="18.5" r="2.5" '.$s.'/></svg>',
        'bike'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="5.5" cy="17.5" r="3.5" '.$s.'/><circle cx="18.5" cy="17.5" r="3.5" '.$s.'/><path d="M15 6h-3l-3 8h9" '.$s.'/><path d="M5.5 17.5L9 10l3 4" '.$s.'/><circle cx="15" cy="5" r="1" fill="currentColor"/></svg>',
        'map'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" '.$s.'/><line x1="8" y1="2" x2="8" y2="18" '.$s.'/><line x1="16" y1="6" x2="16" y2="22" '.$s.'/></svg>',
        'pin'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" '.$s.'/><circle cx="12" cy="10" r="3" '.$s.'/></svg>',
        'card'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="1" y="4" width="22" height="16" rx="2" ry="2" '.$s.'/><line x1="1" y1="10" x2="23" y2="10" '.$s.'/></svg>',
        'trend'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18" '.$s.'/><polyline points="17 6 23 6 23 12" '.$s.'/></svg>',
        'dollar'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><line x1="12" y1="1" x2="12" y2="23" '.$s.'/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" '.$s.'/></svg>',
        'dollar_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><line x1="12" y1="1" x2="12" y2="23" '.$s.'/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" '.$s.'/></svg>',
        'star'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" '.$s.'/></svg>',
        'ticket'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" '.$s.'/></svg>',
        'cog'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" '.$s.'/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" '.$s.'/></svg>',
        'logout'   => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" '.$s.'/><polyline points="16 17 21 12 16 7" '.$s.'/><line x1="21" y1="12" x2="9" y2="12" '.$s.'/></svg>',
        'menu'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><line x1="3" y1="6" x2="21" y2="6" '.$s.'/><line x1="3" y1="12" x2="21" y2="12" '.$s.'/><line x1="3" y1="18" x2="21" y2="18" '.$s.'/></svg>',
        'close'    => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" '.$s.'/><line x1="6" y1="6" x2="18" y2="18" '.$s.'/></svg>',
        'check'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" '.$s.'/></svg>',
        'search'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" '.$s.'/><line x1="21" y1="21" x2="16.65" y2="16.65" '.$s.'/></svg>',
        'phone'    => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.67 9.5a19.79 19.79 0 01-3.07-8.63A2 2 0 012.47 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.16 6.16l1.27-.81a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" '.$s.'/></svg>',
        'chevron_d'=> '<svg width="10" height="10" viewBox="0 0 24 24" fill="none"><polyline points="6 9 12 15 18 9" '.$s.'/></svg>',
        'cart'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="21" r="1" '.$s.'/><circle cx="20" cy="21" r="1" '.$s.'/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-7.43H6" '.$s.'/></svg>',
        'cart_sm'  => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="21" r="1" '.$s.'/><circle cx="20" cy="21" r="1" '.$s.'/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-7.43H6" '.$s.'/></svg>',
        'moon'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" '.$s.'/></svg>',
        'moon_sm'  => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" '.$s.'/></svg>',
        'globe'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" '.$s.'/><line x1="2" y1="12" x2="22" y2="12" '.$s.'/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" '.$s.'/></svg>',
        'users_mc' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" '.$s.'/><circle cx="9" cy="7" r="4" '.$s.'/><path d="M23 21v-2a4 4 0 00-3-3.87" '.$s.'/><path d="M16 3.13a4 4 0 010 7.75" '.$s.'/></svg>',
    ];
@endphp

<div class="sa">

{{-- ════════ SIDEBAR ════════ --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap">{!! $I['brand'] !!}</div>
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
        <a href="{{ route('admin.dashboard') }}" class="sb-a"><span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span></a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span></a>
        <a href="{{ route('admin.products.index') }}" class="sb-a"><span class="sb-i">{!! $I['bag'] !!}</span><span>Produits</span></a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a"><span class="sb-i">{!! $I['box'] !!}</span><span>Commandes boutiques</span></a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['brief'] !!}</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a on"><span class="sb-i">{!! $I['users'] !!}</span><span>Clients boutiques</span></a>
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
        <a href="{{ route('admin.support.index') }}" class="sb-a"><span class="sb-i">{!! $I['ticket'] !!}</span><span>Tickets support</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['user'] !!}</span>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf<button type="submit" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['logout'] !!}</span>Déconnexion</button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- ════════ MAIN ════════ --}}
<div class="mn">
    <header class="tb">
        <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
        <div class="tb-ttl">
            <span class="tb-ico" style="color:var(--brand)">{!! $I['users'] !!}</span>
            <b>Clients boutiques</b>
        </div>
        <div class="tb-sp"></div>
        <a href="{{ route('admin.dashboard') }}" class="tb-btn" title="Dashboard">
            <span class="tb-ico">{!! $I['home'] !!}</span>
        </a>
        <div style="position:relative">
            <button class="tb-user" id="tbU" onclick="toggleDrop()">
                <div class="tb-uav">{{ $meInit }}</div>
                <div style="text-align:left">
                    <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                    <div class="tb-urole">SuperAdmin</div>
                </div>
                <span class="tb-chev">{!! $I['chevron_d'] !!}</span>
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
                    @csrf<button type="submit" class="drop-i d">
                        <span class="drop-ico">{!! $I['logout'] !!}</span>Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="con">

        <div class="bc">
            <span class="bc-ico">{!! $I['brand'] !!}</span>
            <span class="bs">›</span>
            <a href="{{ route('admin.dashboard') }}">Vue d'ensemble</a>
            <span class="bs">›</span>
            <span style="color:var(--text);font-weight:600">Clients boutiques</span>
        </div>

        <div class="ph">
            <div>
                <h1>Clients{{ $filteredShop ? ' — '.$filteredShop->name : ' boutiques' }}</h1>
                <div class="ph-sub">
                    {{ number_format($stats['total']) }} client{{ $stats['total']>1?'s':'' }}
                    {{ $filteredShop ? 'ayant commandé dans cette boutique' : 'inscrits sur la plateforme' }}
                    · {{ number_format($stats['total_cmd']) }} commande{{ $stats['total_cmd']>1?'s':'' }} au total
                </div>
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="stat-g">
            <div class="stat-c tot">
                <div class="stat-ico tot">{!! $I['users_lg'] !!}</div>
                <div class="stat-v">{{ $stats['total'] }}</div>
                <div class="stat-l">Total clients</div>
            </div>
            <div class="stat-c act">
                <div class="stat-ico act">{!! $I['cart'] !!}</div>
                <div class="stat-v">{{ $stats['actifs'] }}</div>
                <div class="stat-l">Ont commandé</div>
            </div>
            <div class="stat-c ina">
                <div class="stat-ico ina">{!! $I['moon'] !!}</div>
                <div class="stat-v">{{ $stats['inactifs'] }}</div>
                <div class="stat-l">Jamais commandé</div>
            </div>
            <div class="stat-c cmd">
                <div class="stat-ico cmd">{!! $I['box_lg'] !!}</div>
                <div class="stat-v">{{ number_format($stats['total_cmd']) }}</div>
                <div class="stat-l">Total commandes</div>
            </div>
            <div class="stat-c dep">
                <div class="stat-ico dep">{!! $I['dollar_lg'] !!}</div>
                <div class="stat-v sm">{{ number_format($stats['total_depense'],0,',',' ') }}</div>
                <div class="stat-l">Volume total dépensé</div>
            </div>
        </div>

        {{-- Chips activité --}}
        <div class="act-chips">
            <a href="{{ route('admin.clients.index', request()->only('search')) }}"
               class="ac all {{ !$currentAct ? 'on' : '' }}">Tous ({{ $stats['total'] }})</a>
            <a href="{{ route('admin.clients.index', array_merge(request()->only('search'), ['activity'=>'actif'])) }}"
               class="ac act {{ $currentAct==='actif' ? 'on' : '' }}">
                <span class="ac-ico">{!! $I['cart_sm'] !!}</span>Ont commandé ({{ $stats['actifs'] }})
            </a>
            <a href="{{ route('admin.clients.index', array_merge(request()->only('search'), ['activity'=>'inactif'])) }}"
               class="ac ina {{ $currentAct==='inactif' ? 'on' : '' }}">
                <span class="ac-ico">{!! $I['moon_sm'] !!}</span>Jamais commandé ({{ $stats['inactifs'] }})
            </a>
        </div>

        {{-- Filtres --}}
        <form method="GET" action="{{ route('admin.clients.index') }}" id="cForm" class="filter-bar">
            @if($currentAct)<input type="hidden" name="activity" value="{{ $currentAct }}">@endif
            <div style="display:flex;align-items:center;gap:6px">
                <span class="f-ico">{!! $I['store_sm'] !!}</span>
                <select name="shop_id" class="f-sel" onchange="document.getElementById('cForm').submit()" style="min-width:170px">
                    <option value="">Toutes les boutiques</option>
                    @foreach($shops as $sh)
                        <option value="{{ $sh->id }}" {{ (string)$currentShop===(string)$sh->id ? 'selected' : '' }}>{{ $sh->name }}</option>
                    @endforeach
                </select>
            </div>
            <span class="f-ico">{!! $I['search'] !!}</span>
            <input type="text" name="search" class="f-input" placeholder="Nom, email, téléphone…" value="{{ $currentSearch }}">
            <div style="display:flex;gap:6px">
                <button type="submit" class="btn-search">
                    <span class="srch-ico">{!! $I['search'] !!}</span>Filtrer
                </button>
                @if($currentAct||$currentSearch||$currentShop)
                    <a href="{{ route('admin.clients.index') }}" class="btn-reset">
                        <span class="srch-ico">{!! $I['close'] !!}</span>Effacer
                    </a>
                @endif
            </div>
        </form>

        {{-- Chip boutique active --}}
        @if($filteredShop)
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:14px">
            <span style="font-size:11px;color:var(--muted);font-weight:700">Boutique :</span>
            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;background:rgba(124,58,237,.1);color:var(--brand);font-size:11.5px;font-weight:700;border:1px solid rgba(124,58,237,.2)">
                <span class="pill-ico">{!! $I['store_sm'] !!}</span>
                {{ $filteredShop->name }}
                <a href="{{ route('admin.clients.index', array_filter(['activity'=>$currentAct,'search'=>$currentSearch])) }}"
                   style="color:var(--brand);text-decoration:none;font-size:14px;line-height:1;margin-left:2px">×</a>
            </span>
        </div>
        @endif

        {{-- Table --}}
        <div class="mc">
            <div class="mc-h">
                <div class="mc-t">
                    <span class="mc-ico">{!! $I['users_mc'] !!}</span>
                    Liste des clients
                    @if($currentAct==='actif')
                        <span style="font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;background:var(--gbg);color:#065f46;display:inline-flex;align-items:center;gap:4px">
                            <span class="chip-ico">{!! $I['cart_sm'] !!}</span>Ont commandé
                        </span>
                    @elseif($currentAct==='inactif')
                        <span style="font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;background:rgba(148,163,184,.12);color:var(--muted);display:inline-flex;align-items:center;gap:4px">
                            <span class="chip-ico">{!! $I['moon_sm'] !!}</span>Jamais commandé
                        </span>
                    @endif
                </div>
                <span style="font-size:12px;color:var(--muted);font-weight:600">
                    {{ number_format($clients->total()) }} client{{ $clients->total()>1?'s':'' }}
                </span>
            </div>

            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Pays</th>
                            <th>Commandes</th>
                            <th>Total dépensé</th>
                            <th>Dernière commande</th>
                            <th>Inscrit le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $c)
                        @php
                            $init    = strtoupper(substr($c->name,0,1));
                            $color   = $avatarColors[$c->id % count($avatarColors)];
                            $nbCmd   = $c->orders_count;
                            $total   = $c->orders_sum_total ?? 0;
                            $lastAt  = $c->orders_max_created_at;
                            $cCode   = strtoupper($c->country ?? '');
                            $flag    = $countryFlags[$cCode] ?? null;

                            $cmdClass = match(true) {
                                $nbCmd === 0 => 'zero',
                                $nbCmd >= 20 => 'top',
                                $nbCmd >= 5  => 'mid',
                                default      => 'low',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="u-cell">
                                    <div class="u-av" style="background:{{ $color }}">{{ $init }}</div>
                                    <div>
                                        <div class="u-name">{{ $c->name }}</div>
                                        <div class="u-sub">{{ $c->email }}</div>
                                        @if($c->phone)
                                            <div class="u-phone">
                                                <span class="phone-ico">{!! $I['phone'] !!}</span>
                                                {{ $c->phone }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($flag)
                                    <span style="font-size:18px" title="{{ $c->country ?? 'Inconnu' }}">{{ $flag }}</span>
                                @else
                                    <span class="globe-ico" title="{{ $c->country ?? 'Inconnu' }}">{!! $I['globe'] !!}</span>
                                @endif
                                @if($c->country)
                                    <div style="font-size:10.5px;color:var(--muted);margin-top:2px">{{ strtoupper($c->country) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="cmd-bdg {{ $cmdClass }}">
                                    <span class="cmd-ico">{!! $I['box_sm'] !!}</span>
                                    {{ $nbCmd }} commande{{ $nbCmd>1?'s':'' }}
                                </span>
                            </td>
                            <td>
                                @if($total > 0)
                                    <div class="dep-val">{{ number_format($total,0,',',' ') }}</div>
                                    <div class="dep-sub">toutes devises</div>
                                @else
                                    <span style="color:var(--muted);font-size:12px">—</span>
                                @endif
                            </td>
                            <td>
                                @if($lastAt)
                                    <div class="last-order">{{ \Carbon\Carbon::parse($lastAt)->format('d/m/Y') }}</div>
                                    <div class="last-order-sub">{{ \Carbon\Carbon::parse($lastAt)->diffForHumans() }}</div>
                                @else
                                    <span style="color:var(--muted);font-size:12px">Aucune commande</span>
                                @endif
                            </td>
                            <td style="font-size:11.5px;color:var(--muted);white-space:nowrap">
                                {{ optional($c->created_at)->format('d/m/Y') }}<br>
                                <span style="font-size:10.5px">{{ optional($c->created_at)->format('H:i') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-ico">{!! $I['users_lg'] !!}</div>
                                    <div class="empty-t">Aucun client trouvé</div>
                                    <div class="empty-s">
                                        @if($currentSearch||$currentAct)
                                            Modifiez vos filtres pour voir plus de résultats.
                                        @else
                                            Les clients apparaîtront ici dès leur inscription.
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
                <div class="pag-wrap">{{ $clients->links() }}</div>
            @endif
        </div>

    </div>
</div>
</div>

<div id="toast" style="position:fixed;bottom:22px;right:22px;background:#1e293b;color:#fff;padding:10px 17px;border-radius:10px;font-size:12px;font-weight:600;box-shadow:0 8px 28px rgba(0,0,0,.22);transform:translateY(80px);opacity:0;transition:all .27s cubic-bezier(.4,0,.2,1);pointer-events:none;z-index:9999"></div>
@endsection

@push('scripts')
<script>
const sb=document.getElementById('sb'),ov=document.getElementById('sbOv'),mn=document.querySelector('.mn');
function closeSb(){sb.classList.remove('open');ov.classList.remove('open');sb.classList.add('closed');mn.classList.add('sb-closed')}
function toggleSb(){if(sb.classList.contains('closed')){sb.classList.remove('closed');mn.classList.remove('sb-closed')}else{sb.classList.toggle('open');ov.classList.toggle('open')}}
const dr=document.getElementById('drop');
function toggleDrop(){dr.classList.toggle('open')}
document.addEventListener('click',e=>{
    const b=document.getElementById('tbU');
    if(b&&!b.contains(e.target)&&!dr.contains(e.target))dr.classList.remove('open');
});
let _t;
function nt(msg='Bientôt disponible'){
    const el=document.getElementById('toast');
    el.textContent=msg;el.style.transform='translateY(0)';el.style.opacity='1';
    clearTimeout(_t);_t=setTimeout(()=>{el.style.transform='translateY(80px)';el.style.opacity='0';},2800);
}
</script>
@endpush
