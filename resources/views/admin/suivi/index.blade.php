@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
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
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:19px;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-me{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:10px;flex-shrink:0;background:rgba(255,255,255,.05)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;flex-shrink:0;border:2px solid rgba(196,181,253,.5);box-shadow:0 2px 8px rgba(124,58,237,.4)}
.sb-name{font-size:13px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px}
.sb-badge{display:inline-block;font-size:9.5px;font-weight:700;color:#e9d5ff;background:rgba(167,139,250,.22);border:1px solid rgba(196,181,253,.4);padding:2px 8px;border-radius:20px;margin-top:3px;text-transform:uppercase;letter-spacing:.6px}
.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;padding:16px 18px 6px;display:flex;align-items:center;gap:6px}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s;cursor:pointer;letter-spacing:.1px}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700;box-shadow:inset 0 0 0 1px rgba(167,139,250,.15)}
.sb-i{width:18px;text-align:center;font-size:15px;flex-shrink:0}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}.sb-pill.a{background:var(--amber)}.sb-pill.g{background:var(--green)}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}.mn.sb-closed{margin-left:0}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100;box-shadow:0 1px 0 var(--bd)}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);font-size:18px;transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-acts{display:flex;align-items:center;gap:6px}
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
.con{flex:1;padding:24px}
.flash{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:12.5px;font-weight:600}
.flash.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.bc{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}.bc .bs{color:rgba(0,0,0,.15)}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:19px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px}
.ph-sub{font-size:11.5px;color:var(--muted)}

/* kpi */
.kpi-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:12px;margin-bottom:22px}
.kpi{background:var(--card);border-radius:13px;padding:16px;border:1px solid var(--bd);position:relative;overflow:hidden;transition:transform .18s,box-shadow .18s;cursor:default}
.kpi:hover{transform:translateY(-2px);box-shadow:0 5px 18px rgba(0,0,0,.08)}
.kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:13px 13px 0 0}
.kpi.p::before{background:linear-gradient(90deg,#7c3aed,#8b5cf6)}
.kpi.g::before{background:linear-gradient(90deg,#10b981,#34d399)}
.kpi.a::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.kpi.b::before{background:linear-gradient(90deg,#3b82f6,#60a5fa)}
.kpi.r::before{background:linear-gradient(90deg,#ef4444,#f87171)}
.kpi-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;margin-bottom:11px}
.kpi-ic.p{background:rgba(139,92,246,.12)}.kpi-ic.g{background:var(--gbg)}.kpi-ic.a{background:var(--abg)}
.kpi-ic.b{background:var(--bbg)}.kpi-ic.r{background:var(--rbg)}
.kpi-v{font-size:24px;font-weight:900;color:var(--text);letter-spacing:-1px;line-height:1;margin-bottom:4px}
.kpi-l{font-size:11.5px;color:var(--muted);font-weight:500}
.kpi-s{font-size:10px;color:rgba(100,116,139,.55);margin-top:2px}

/* svg sidebar icons */
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-a:hover .sb-i,.sb-a.on .sb-i{opacity:1}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7}
.sb-ft-row:hover .sb-ft-ico{opacity:1}

/* live pulse */
.pulse-wrap{display:flex;align-items:center;gap:5px;font-size:10.5px;font-weight:700;color:var(--green)}
.pulse{width:8px;height:8px;border-radius:50%;background:var(--green);animation:pulse 1.5s ease-in-out infinite}
@keyframes pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.4);opacity:.6}}

/* map */
.map-card{background:var(--card);border-radius:13px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:20px}
.map-hd{padding:12px 18px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.map-ht{font-size:13px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:7px}
#map{height:400px;width:100%;z-index:1}
.map-legend{padding:10px 18px;border-top:1px solid var(--bd);display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.map-leg-item{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted);font-weight:600}
.leg-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}

/* chips */
.chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px}
.chip{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:11.5px;font-weight:700;text-decoration:none;border:1.5px solid var(--bd);color:var(--muted);background:var(--card);transition:all .15s}
.chip:hover{border-color:#a78bfa;color:var(--brand)}
.chip.on{background:rgba(124,58,237,.09);border-color:#a78bfa;color:var(--brand)}
.chip.live-chip{background:var(--gbg);border-color:rgba(16,185,129,.3);color:#065f46}
.chip.gps-chip{background:var(--bbg);border-color:rgba(59,130,246,.3);color:#1d4ed8}

/* filter bar */
.fb{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.fb-sel{height:34px;border:1px solid var(--bd);border-radius:8px;padding:0 10px;font-size:12px;font-family:var(--font);color:var(--text);background:var(--card);outline:none;cursor:pointer;transition:border-color .15s}
.fb-sel:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.fb-inp{height:34px;border:1px solid var(--bd);border-radius:8px;padding:0 10px;font-size:12px;font-family:var(--font);color:var(--text);background:var(--card);outline:none;width:220px;transition:border-color .15s}
.fb-inp:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.fb-btn{height:34px;padding:0 14px;border-radius:8px;border:1px solid var(--bd);background:var(--card);font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;font-family:var(--font);transition:all .13s}
.fb-btn:hover{background:var(--bg);color:var(--text)}

/* section card */
.sc{background:var(--card);border-radius:13px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.sc-h{padding:14px 20px 12px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.sc-t{font-size:13px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:7px}

/* table */
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse}
.tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;padding:9px 16px;background:var(--bg);border-bottom:1px solid var(--bd);white-space:nowrap;text-align:left}
.tbl td{padding:10px 16px;font-size:12px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr:hover{background:rgba(124,58,237,.02)}

/* order row */
.t-ord{display:flex;align-items:center;gap:8px}
.t-id{font-size:11px;font-weight:800;color:var(--brand);background:rgba(124,58,237,.07);padding:2px 7px;border-radius:6px;white-space:nowrap}
.t-name{font-weight:600;font-size:12px}.t-sub{font-size:10.5px;color:var(--muted)}

/* badges */
.bdg{font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;display:inline-flex;align-items:center;gap:3px;white-space:nowrap}
.bdg.g{color:#065f46;background:var(--gbg)}.bdg.b{color:#1d4ed8;background:var(--bbg)}
.bdg.a{color:#92400e;background:var(--abg)}.bdg.m{color:var(--muted);background:rgba(100,116,139,.1)}
.bdg.r{color:#7f1d1d;background:var(--rbg)}

/* gps status */
.gps-live{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;color:#065f46}
.gps-live::before{content:'';width:7px;height:7px;border-radius:50%;background:var(--green);animation:pulse 1.5s ease-in-out infinite;flex-shrink:0}
.gps-old{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;color:#92400e}
.gps-old::before{content:'';width:7px;height:7px;border-radius:50%;background:var(--amber);flex-shrink:0}
.gps-none{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--muted)}
.gps-none::before{content:'';width:7px;height:7px;border-radius:50%;background:#94a3b8;flex-shrink:0}

/* view btn */
.btn-map{display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:7px;font-size:11px;font-weight:700;background:var(--bbg);color:#1d4ed8;border:1px solid rgba(59,130,246,.2);cursor:pointer;transition:all .13s;text-decoration:none}
.btn-map:hover{background:rgba(59,130,246,.18);transform:translateY(-1px)}

/* empty */
.empty{padding:56px 20px;text-align:center}
.empty-ico{font-size:36px;opacity:.25;margin-bottom:10px}
.empty-t{font-size:13px;font-weight:700;color:var(--muted)}
.empty-s{font-size:11px;color:rgba(100,116,139,.65);margin-top:4px}

/* pagination */
.pag{padding:12px 20px;border-top:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
.pag-info{font-size:11.5px;color:var(--muted)}

@media(max-width:900px){.sb{transform:translateX(-100%)}.sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.32)}.sb-ov.open{display:block}.mn{margin-left:0}.kpi-g{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.con{padding:13px}.tb{padding:0 13px}#map{height:260px}}
</style>
@endpush

@section('content')
@php
    $me     = auth()->user();
    $meName = $me->name ?? 'Fondateur';
    $meInit = strtoupper(substr($meName,0,1));
    $totalPending = \App\Models\DeliveryCompany::where('approved', false)->count();

    $curGps    = request('gps','');
    $curCo     = request('company_id','');
    $curSearch = request('search','');
    $bp        = array_filter(['company_id'=>$curCo,'search'=>$curSearch]);

    $now = now();

    /* ═══ BIBLIOTHÈQUE D'ICÔNES SVG PREMIUM ═══ */
    $s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
    $sl= 'stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"';
    $I = [
        'brand_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>',
        'home'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
        'store'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'bag'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'box'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
        'brief'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
        'user'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'users'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'truck'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect width="7" height="7" x="14" y="12" rx="1"/><path d="M5 17a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/><path d="M15 19a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/></svg>',
        'bike'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg>',
        'map'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/></svg>',
        'pin'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
        'card'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>',
        'trend'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
        'dollar' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'receipt'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"/><path d="M14 8H8m8 4H8m5 4H8"/></svg>',
        'star'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'ticket' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3Z"/></svg>',
        'cog'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
        'logout' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
        'profile'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'close'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        'menu'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>',
        'chevron'=> '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>',
        // Grandes versions pour KPI / contenu
        'bike_lg'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" '.$sl.'><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg>',
        'signal_lg'=> '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" '.$sl.'><path d="M2 20h.01"/><path d="M7 20v-4"/><path d="M12 20v-8"/><path d="M17 20V8"/><path d="M22 4v16"/></svg>',
        'pin_lg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" '.$sl.'><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
        'ban_lg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" '.$sl.'><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>',
        'nav_lg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" '.$sl.'><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>',
        'map_lg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" '.$sl.'><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/></svg>',
        'refresh'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" '.$sl.'><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>',
        'truck_sm' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect width="7" height="7" x="14" y="12" rx="1"/><path d="M5 17a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/><path d="M15 19a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/></svg>',
        'store_sm' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'phone_sm' => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.34 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'loc_sm'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
        'eye_sm'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
        'bike_sm'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg>',
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
        <a href="{{ route('admin.dashboard') }}" class="sb-a"><span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span></a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span></a>
        <a href="{{ route('admin.products.index') }}" class="sb-a"><span class="sb-i">{!! $I['bag'] !!}</span><span>Produits</span></a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a"><span class="sb-i">{!! $I['box'] !!}</span><span>Commandes boutiques</span></a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['brief'] !!}</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">{!! $I['user'] !!}</span><span>Clients boutiques</span></a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span>
            @if($totalPending>0)<span class="sb-pill r">{{ $totalPending }}</span>@endif
        </a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['bike'] !!}</span><span>Livreurs</span></a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a"><span class="sb-i">{!! $I['map'] !!}</span><span>Zones de livraison</span></a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a on">
            <span class="sb-i">{!! $I['pin'] !!}</span><span>Suivi en temps réel</span>
            @if($stats['en_livraison']>0)<span class="sb-pill g">{{ $stats['en_livraison'] }}</span>@endif
        </a>
        <div class="sb-sec fin">── Finance</div>
        <a href="{{ route('admin.paiements.index') }}" class="sb-a"><span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span></a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a"><span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span></a>
        <a href="{{ route('admin.revenus-boutiques.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Revenus boutiques</span></a>
        <a href="{{ route('admin.revenus-entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Revenus entreprises</span></a>
        <a href="{{ route('admin.revenus.index') }}" class="sb-a"><span class="sb-i">{!! $I['dollar'] !!}</span><span>Revenus plateforme</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">{!! $I['receipt'] !!}</span><span>Factures &amp; Exports</span></a>
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
        <a href="{{ route('profile.edit') }}" class="sb-ft-row">
            <span class="sb-ft-ico">{!! $I['profile'] !!}</span>Mon profil
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
    <div class="tb-ttl">Super<b>Admin</b></div>
    <div class="tb-sp"></div>
    <div class="tb-acts">
        <a href="{{ route('profile.edit') }}" class="tb-btn" title="Profil">{!! $I['profile'] !!}</a>
    </div>
    <div style="position:relative">
        <button class="tb-user" id="tbU" onclick="toggleDrop()">
            <div class="tb-uav">{{ $meInit }}</div>
            <div style="text-align:left">
                <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                <div class="tb-urole">SuperAdmin</div>
            </div>
            <span style="display:flex;align-items:center;color:var(--muted)">{!! $I['chevron'] !!}</span>
        </button>
        <div class="drop" id="drop">
            <a href="{{ route('profile.edit') }}" class="drop-i">
                <span style="display:flex">{!! $I['profile'] !!}</span> Mon profil
            </a>
            <div class="drop-sep"></div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="drop-i d">
                    <span style="display:flex">{!! $I['logout'] !!}</span> Déconnexion
                </button>
            </form>
        </div>
    </div>
</header>

<div class="con">

    {{-- Breadcrumb --}}
    <div class="bc">
        <a href="{{ route('admin.dashboard') }}">
            <span style="display:inline-flex;align-items:center;gap:4px">{!! $I['home'] !!} Accueil</span>
        </a>
        <span class="bs">›</span>
        <span style="color:var(--text);font-weight:600">Suivi en temps réel</span>
    </div>

    {{-- Page header --}}
    <div class="ph">
        <div>
            <h1 style="display:flex;align-items:center;gap:10px">
                <span style="display:inline-flex;width:34px;height:34px;background:rgba(124,58,237,.1);border-radius:9px;align-items:center;justify-content:center;color:var(--brand)">{!! $I['pin_lg'] !!}</span>
                Suivi en temps réel
            </h1>
            <div class="ph-sub">
                Toutes les livraisons en cours · GPS actif
                @if($stats['live']>0)
                    <span style="margin-left:6px" class="pulse-wrap"><span class="pulse"></span>{{ $stats['live'] }} position(s) live</span>
                @endif
            </div>
        </div>
        <button onclick="location.reload()" style="display:inline-flex;align-items:center;gap:7px;padding:8px 15px;border-radius:8px;background:var(--bg);border:1px solid var(--bd);font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;font-family:var(--font);transition:all .13s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--bg)'">
            {!! $I['refresh'] !!} Actualiser
        </button>
    </div>

    {{-- Stats --}}
    <div class="kpi-g">
        <div class="kpi a">
            <div class="kpi-ic a" style="color:var(--amber)">{!! $I['bike_lg'] !!}</div>
            <div class="kpi-v">{{ $stats['en_livraison'] }}</div>
            <div class="kpi-l">En livraison</div>
            <div class="kpi-s">Commandes actives</div>
        </div>
        <div class="kpi g">
            <div class="kpi-ic g" style="color:var(--green)">{!! $I['signal_lg'] !!}</div>
            <div class="kpi-v">{{ $stats['live'] }}</div>
            <div class="kpi-l">GPS live</div>
            <div class="kpi-s">Ping &lt; 5 min</div>
        </div>
        <div class="kpi b">
            <div class="kpi-ic b" style="color:var(--blue)">{!! $I['pin_lg'] !!}</div>
            <div class="kpi-v">{{ $stats['avec_gps'] }}</div>
            <div class="kpi-l">Avec position</div>
            <div class="kpi-s">GPS transmis</div>
        </div>
        <div class="kpi r">
            <div class="kpi-ic r" style="color:var(--red)">{!! $I['ban_lg'] !!}</div>
            <div class="kpi-v">{{ $stats['sans_gps'] }}</div>
            <div class="kpi-l">Sans GPS</div>
            <div class="kpi-s">Pas de position</div>
        </div>
        <div class="kpi p">
            <div class="kpi-ic p" style="color:var(--brand)">{!! $I['nav_lg'] !!}</div>
            <div class="kpi-v">{{ $stats['livreurs_busy'] }}</div>
            <div class="kpi-l">Livreurs actifs</div>
            <div class="kpi-s">Statut "En livraison"</div>
        </div>
    </div>

    {{-- Carte --}}
    <div class="map-card">
        <div class="map-hd">
            <div class="map-ht">
                <span style="display:inline-flex;color:var(--brand)">{!! $I['map_lg'] !!}</span>
                Carte des livraisons
                @if($mapPoints->count()>0)
                    <span style="font-size:11px;font-weight:600;color:var(--muted)">({{ $mapPoints->count() }} position(s))</span>
                @endif
            </div>
            @if($mapPoints->count()>0)
                <div class="pulse-wrap"><span class="pulse"></span>Données en direct</div>
            @endif
        </div>
        <div id="map"></div>
        <div class="map-legend">
            <div class="map-leg-item"><div class="leg-dot" style="background:#10b981"></div>GPS live (&lt; 5 min)</div>
            <div class="map-leg-item"><div class="leg-dot" style="background:#f59e0b"></div>GPS ancien (&gt; 5 min)</div>
            <div style="margin-left:auto;font-size:10.5px;color:var(--muted)">
                Powered by <a href="https://leafletjs.com" target="_blank" style="color:var(--brand)">Leaflet</a> · OpenStreetMap
            </div>
        </div>
    </div>

    {{-- GPS chips --}}
    <div class="chips">
        <a href="{{ route('admin.suivi.index', $bp) }}"
           class="chip {{ $curGps==='' ? 'on' : '' }}">Toutes</a>
        <a href="{{ route('admin.suivi.index', array_merge($bp,['gps'=>'live'])) }}"
           class="chip live-chip {{ $curGps==='live' ? 'on' : '' }}">
            <span style="display:inline-flex">{!! $I['signal_lg'] !!}</span> GPS live
        </a>
        <a href="{{ route('admin.suivi.index', array_merge($bp,['gps'=>'with'])) }}"
           class="chip gps-chip {{ $curGps==='with' ? 'on' : '' }}">
            <span style="display:inline-flex">{!! $I['pin_lg'] !!}</span> Avec GPS
        </a>
        <a href="{{ route('admin.suivi.index', array_merge($bp,['gps'=>'without'])) }}"
           class="chip {{ $curGps==='without' ? 'on' : '' }}">
            <span style="display:inline-flex">{!! $I['ban_lg'] !!}</span> Sans GPS
        </a>
    </div>

    {{-- Filter bar --}}
    <form id="filterForm" method="GET" action="{{ route('admin.suivi.index') }}">
        @if($curGps)<input type="hidden" name="gps" value="{{ $curGps }}">@endif
        <div class="fb">
            <select name="company_id" class="fb-sel" onchange="document.getElementById('filterForm').submit()">
                <option value="">🚚 Toutes les entreprises</option>
                @foreach($companies as $co)
                    <option value="{{ $co->id }}" {{ (string)$curCo === (string)$co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                @endforeach
            </select>
            <input type="text" name="search" class="fb-inp" placeholder="🔍 ID, client, destination…" value="{{ $curSearch }}">
            <button type="submit" class="fb-btn">Filtrer</button>
            @if($curSearch || $curCo)
                <a href="{{ route('admin.suivi.index', $curGps ? ['gps'=>$curGps] : []) }}" class="fb-btn">✕ Réinitialiser</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="sc">
        <div class="sc-h">
            <div class="sc-t">
                <span style="display:inline-flex;color:var(--amber)">{!! $I['bike_lg'] !!}</span> Livraisons en cours
                <span style="font-size:11px;font-weight:600;color:var(--muted)">({{ $orders->total() }})</span>
            </div>
        </div>

        @if($orders->isEmpty())
            <div class="empty">
                <div class="empty-ico" style="display:flex;justify-content:center;opacity:.25;margin-bottom:10px;font-size:36px">{!! $I['pin_lg'] !!}</div>
                <div class="empty-t">Aucune livraison en cours</div>
                <div class="empty-s">Toutes les commandes sont livrées ou en attente.</div>
            </div>
        @else
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Destination</th>
                        <th>Livreur / Driver</th>
                        <th>Entreprise</th>
                        <th>GPS</th>
                        <th>Dernier ping</th>
                        <th>Depuis</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    @php
                        $hasGps   = $order->current_lat && $order->current_lng;
                        $isLive   = $hasGps && $order->last_ping_at && $order->last_ping_at->gt($now->copy()->subMinutes(5));
                        $isOld    = $hasGps && !$isLive;

                        $livreurName = $order->driver?->name
                                    ?? $order->livreur?->name
                                    ?? null;
                    @endphp
                    <tr>
                        {{-- ID + boutique --}}
                        <td>
                            <div class="t-ord">
                                <span class="t-id">#{{ $order->id }}</span>
                            </div>
                            @if($order->shop)
                                <div class="t-sub" style="margin-top:3px;display:flex;align-items:center;gap:3px"><span style="display:inline-flex;opacity:.65">{!! $I['store_sm'] !!}</span> {{ $order->shop->name }}</div>
                            @endif
                        </td>

                        {{-- Client --}}
                        <td>
                            @if($order->client)
                                <div class="t-name">{{ $order->client->name }}</div>
                                @if($order->client_phone)
                                    <div class="t-sub" style="display:flex;align-items:center;gap:3px"><span style="display:inline-flex;opacity:.65">{!! $I['phone_sm'] !!}</span> {{ $order->client_phone }}</div>
                                @endif
                            @else
                                <span style="color:var(--muted)">—</span>
                            @endif
                        </td>

                        {{-- Destination --}}
                        <td style="max-width:180px">
                            @if($order->delivery_destination)
                                <div style="font-size:11.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:175px;display:flex;align-items:center;gap:3px" title="{{ $order->delivery_destination }}">
                                    <span style="display:inline-flex;flex-shrink:0;opacity:.65">{!! $I['loc_sm'] !!}</span> {{ $order->delivery_destination }}
                                </div>
                            @else
                                <span style="color:var(--muted)">—</span>
                            @endif
                            @if($order->deliveryZone)
                                <div class="t-sub">{{ $order->deliveryZone->name }}</div>
                            @endif
                        </td>

                        {{-- Livreur --}}
                        <td>
                            @if($livreurName)
                                <span class="bdg b" style="display:inline-flex;align-items:center;gap:4px"><span style="display:inline-flex">{!! $I['bike_sm'] !!}</span> {{ $livreurName }}</span>
                            @else
                                <span style="color:var(--muted)">Non assigné</span>
                            @endif
                        </td>

                        {{-- Entreprise --}}
                        <td>
                            @if($order->deliveryCompany)
                                <div style="font-size:12px;font-weight:600;color:var(--brand);display:flex;align-items:center;gap:4px"><span style="display:inline-flex">{!! $I['truck_sm'] !!}</span> {{ $order->deliveryCompany->name }}</div>
                            @else
                                <span style="color:var(--muted)">—</span>
                            @endif
                        </td>

                        {{-- GPS --}}
                        <td>
                            @if($isLive)
                                <span class="gps-live">Live</span>
                                @if($hasGps)
                                <button onclick="flyTo({{ $order->current_lat }},{{ $order->current_lng }},'#{{ $order->id }}')"
                                        class="btn-map" style="margin-top:4px;display:inline-flex">
                                    {!! $I['eye_sm'] !!} Voir sur carte
                                </button>
                                @endif
                            @elseif($isOld)
                                <span class="gps-old">Position ancienne</span>
                            @else
                                <span class="gps-none">Pas de GPS</span>
                            @endif
                        </td>

                        {{-- Dernier ping --}}
                        <td>
                            @if($order->last_ping_at)
                                <div style="font-size:11.5px">{{ $order->last_ping_at->format('H:i') }}</div>
                                <div class="t-sub">{{ $order->last_ping_at->diffForHumans() }}</div>
                            @else
                                <span style="color:var(--muted)">—</span>
                            @endif
                        </td>

                        {{-- En livraison depuis --}}
                        <td>
                            <div style="font-size:11.5px">{{ optional($order->updated_at)->diffForHumans() }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="pag">
            <div class="pag-info">Affichage {{ $orders->firstItem() }}–{{ $orders->lastItem() }} sur {{ $orders->total() }}</div>
            {{ $orders->withQueryString()->links() }}
        </div>
        @endif
        @endif
    </div>

</div>{{-- /con --}}
</div>{{-- /mn --}}
</div>{{-- /sa --}}

<div id="toast" style="position:fixed;bottom:22px;right:22px;background:#1e293b;color:#fff;padding:10px 17px;border-radius:10px;font-size:12px;font-weight:600;box-shadow:0 8px 28px rgba(0,0,0,.22);transform:translateY(80px);opacity:0;transition:all .27s cubic-bezier(.4,0,.2,1);pointer-events:none;z-index:9999;max-width:270px"></div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* sidebar */
const sb=document.getElementById('sb'),ov=document.getElementById('sbOv'),mn=document.querySelector('.mn');
function closeSb(){sb.classList.remove('open');ov.classList.remove('open');sb.classList.add('closed');mn.classList.add('sb-closed')}
function toggleSb(){if(sb.classList.contains('closed')){sb.classList.remove('closed');mn.classList.remove('sb-closed')}else{sb.classList.toggle('open');ov.classList.toggle('open')}}
const dr=document.getElementById('drop');
function toggleDrop(){dr.classList.toggle('open')}
document.addEventListener('click',e=>{const b=document.getElementById('tbU');if(b&&!b.contains(e.target)&&!dr.contains(e.target))dr.classList.remove('open');});
let _t;
function nt(msg='Bientôt disponible'){const el=document.getElementById('toast');el.textContent=msg;el.style.transform='translateY(0)';el.style.opacity='1';clearTimeout(_t);_t=setTimeout(()=>{el.style.transform='translateY(80px)';el.style.opacity='0';},2800);}

/* ─── Leaflet map ─── */
@php
    $pts = $mapPoints->map(fn($o) => [
        'lat'   => $o->current_lat,
        'lng'   => $o->current_lng,
        'id'    => $o->id,
        'live'  => $o->last_ping_at && $o->last_ping_at->gt(now()->subMinutes(5)),
        'label' => '#'.$o->id.' — '.($o->client?->name ?? 'Client')
                  .($o->delivery_destination ? ' → '.Str::limit($o->delivery_destination,30) : ''),
        'ping'  => $o->last_ping_at ? $o->last_ping_at->diffForHumans() : null,
    ]);
@endphp

const mapPoints = @json($pts);

const map = L.map('map');
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

function makeIcon(live) {
    return L.divIcon({
        html: `<div style="width:14px;height:14px;border-radius:50%;background:${live?'#10b981':'#f59e0b'};border:2.5px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3)"></div>`,
        className:'',
        iconSize:[14,14],
        iconAnchor:[7,7]
    });
}

const bounds = [];

if (mapPoints.length > 0) {
    mapPoints.forEach(p => {
        const m = L.marker([p.lat, p.lng], {icon: makeIcon(p.live)}).addTo(map);
        m.bindPopup(`
            <strong>${p.label}</strong><br>
            <span style="font-size:11px;color:#64748b">${p.ping ? '🕐 ' + p.ping : 'Pas de ping'}</span><br>
            <span style="font-size:10px;color:#64748b">${p.lat.toFixed(5)}, ${p.lng.toFixed(5)}</span>
        `);
        bounds.push([p.lat, p.lng]);
    });
    map.fitBounds(bounds, {padding:[30,30]});
} else {
    // Centre sur l'Afrique de l'Ouest par défaut
    map.setView([11.0, -10.5], 5);
}

function flyTo(lat, lng, label) {
    map.flyTo([lat, lng], 15, {duration: 1.2});
    setTimeout(() => {
        L.popup().setLatLng([lat,lng]).setContent(`<strong>${label}</strong>`).openOn(map);
    }, 1300);
    document.getElementById('map').scrollIntoView({behavior:'smooth', block:'center'});
}
</script>
@endpush
