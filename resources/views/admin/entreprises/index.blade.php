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
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;
    position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;
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
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);
    display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;flex-shrink:0;
    border:2px solid rgba(196,181,253,.5);box-shadow:0 2px 8px rgba(124,58,237,.4)}
.sb-name{font-size:13px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px}
.sb-badge{display:inline-block;font-size:9.5px;font-weight:700;color:#e9d5ff;
    background:rgba(167,139,250,.22);border:1px solid rgba(196,181,253,.4);
    padding:2px 8px;border-radius:20px;margin-top:3px;text-transform:uppercase;letter-spacing:.6px}
.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;
    padding:16px 18px 6px;display:flex;align-items:center;gap:6px}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);
    text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;
    transition:all .15s;cursor:pointer;letter-spacing:.1px}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700;
    box-shadow:inset 0 0 0 1px rgba(167,139,250,.15)}
.sb-i{width:18px;height:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-a:hover .sb-i,.sb-a.on .sb-i{opacity:1}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}.sb-pill.a{background:var(--amber)}.sb-pill.g{background:var(--green)}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;
    color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;
    font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;
    background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);
    cursor:pointer;display:flex;align-items:center;justify-content:center;
    transition:all .15s;flex-shrink:0}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}
.mn.sb-closed{margin-left:0}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);
    animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;
    padding:0 22px;gap:12px;position:sticky;top:0;z-index:100;box-shadow:0 1px 0 var(--bd)}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;
    border-radius:7px;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-acts{display:flex;align-items:center;gap:6px}
.tb-btn{width:34px;height:34px;background:none;border:1px solid var(--bd);border-radius:8px;
    cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;text-decoration:none}
.tb-btn:hover{background:var(--bg);color:var(--text)}
.tb-user{display:flex;align-items:center;gap:8px;padding:4px 10px 4px 5px;border:1px solid var(--bd);
    border-radius:8px;cursor:pointer;background:none;position:relative;transition:all .13s}
.tb-user:hover{background:var(--bg)}
.tb-uav{width:27px;height:27px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--bdk));
    display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.tb-uname{font-size:12px;font-weight:600;color:var(--text)}.tb-urole{font-size:9.5px;color:var(--muted)}
.drop{position:absolute;top:calc(100% + 7px);right:0;background:#fff;border:1px solid var(--bd);
    border-radius:11px;padding:7px;box-shadow:0 8px 32px rgba(0,0,0,.13);min-width:185px;z-index:300;display:none;flex-direction:column;gap:2px}
.drop.open{display:flex}
.drop-i{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:7px;font-size:12.5px;
    color:var(--text);text-decoration:none;transition:background .13s;background:none;border:none;cursor:pointer;font-family:var(--font);font-weight:500;width:100%}
.drop-i:hover{background:var(--bg)}.drop-i.d{color:var(--red)}.drop-i.d:hover{background:var(--rbg)}
.drop-sep{height:1px;background:var(--bd);margin:4px 0}
.con{flex:1;padding:24px}
.flash{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:12.5px;font-weight:600}
.flash.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.flash.err{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}
.bc{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}.bc .bs{color:rgba(0,0,0,.15)}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:19px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px;display:flex;align-items:center;gap:9px}
.ph-sub{font-size:11.5px;color:var(--muted)}
.kpi-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:22px}
.kpi{background:var(--card);border-radius:13px;padding:16px;border:1px solid var(--bd);position:relative;overflow:hidden;transition:transform .18s,box-shadow .18s;cursor:default}
.kpi:hover{transform:translateY(-2px);box-shadow:0 5px 18px rgba(0,0,0,.08)}
.kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:13px 13px 0 0}
.kpi.p::before{background:linear-gradient(90deg,#7c3aed,#8b5cf6)}
.kpi.g::before{background:linear-gradient(90deg,#10b981,#34d399)}
.kpi.a::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.kpi.b::before{background:linear-gradient(90deg,#3b82f6,#60a5fa)}
.kpi.r::before{background:linear-gradient(90deg,#ef4444,#f87171)}
.kpi.i::before{background:linear-gradient(90deg,#6366f1,#818cf8)}
.kpi-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-bottom:11px}
.kpi-ic.p{background:rgba(139,92,246,.12);color:var(--brand)}
.kpi-ic.g{background:var(--gbg);color:var(--green)}
.kpi-ic.a{background:var(--abg);color:var(--amber)}
.kpi-ic.b{background:var(--bbg);color:var(--blue)}
.kpi-ic.r{background:var(--rbg);color:var(--red)}
.kpi-ic.i{background:var(--ibg);color:var(--indigo)}
.kpi-v{font-size:24px;font-weight:900;color:var(--text);letter-spacing:-1px;line-height:1;margin-bottom:4px}
.kpi-l{font-size:11.5px;color:var(--muted);font-weight:500}
.chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px}
.chip{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:11.5px;font-weight:700;
    text-decoration:none;border:1.5px solid var(--bd);color:var(--muted);background:var(--card);transition:all .15s;cursor:pointer}
.chip:hover{border-color:#a78bfa;color:var(--brand)}
.chip.on{background:rgba(124,58,237,.09);border-color:#a78bfa;color:var(--brand)}
.chip.pending{background:var(--abg);border-color:rgba(245,158,11,.3);color:#92400e}
.chip.approved{background:var(--gbg);border-color:rgba(16,185,129,.3);color:#065f46}
.chip-ico{display:inline-flex;align-items:center}
.fb{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.fb-sel{height:34px;border:1px solid var(--bd);border-radius:8px;padding:0 10px;font-size:12px;
    font-family:var(--font);color:var(--text);background:var(--card);outline:none;cursor:pointer;transition:border-color .15s}
.fb-sel:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.fb-inp{height:34px;border:1px solid var(--bd);border-radius:8px;padding:0 10px;font-size:12px;
    font-family:var(--font);color:var(--text);background:var(--card);outline:none;width:220px;transition:border-color .15s}
.fb-inp:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.fb-btn{height:34px;padding:0 14px;border-radius:8px;border:1px solid var(--bd);background:var(--card);
    font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;font-family:var(--font);transition:all .13s;
    display:inline-flex;align-items:center;gap:5px;text-decoration:none}
.fb-btn:hover{background:var(--bg);color:var(--text)}
.sc{background:var(--card);border-radius:13px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.sc-h{padding:14px 20px 12px;border-bottom:1px solid var(--bd);
    display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.sc-t{font-size:13px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:7px}
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse}
.tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;
    letter-spacing:.5px;padding:9px 16px;background:var(--bg);border-bottom:1px solid var(--bd);white-space:nowrap;text-align:left}
.tbl td{padding:11px 16px;font-size:12px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr:hover{background:rgba(124,58,237,.02)}
.t-comp{display:flex;align-items:center;gap:9px}
.t-av{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);
    display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0}
.t-name{font-weight:700;font-size:12.5px}
.t-sub{font-size:10.5px;color:var(--muted)}
.bdg{font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;display:inline-flex;align-items:center;gap:3px;white-space:nowrap}
.bdg.g{color:#065f46;background:var(--gbg)}.bdg.a{color:#92400e;background:var(--abg)}
.bdg.r{color:#7f1d1d;background:var(--rbg)}.bdg.b{color:#1d4ed8;background:var(--bbg)}
.bdg.m{color:var(--muted);background:rgba(100,116,139,.1)}.bdg.p{color:var(--bdk);background:rgba(124,58,237,.1)}
.act-btns{display:flex;align-items:center;gap:5px;flex-wrap:wrap}
.btn-sm{display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:7px;
    font-size:11px;font-weight:700;border:none;cursor:pointer;font-family:var(--font);transition:all .13s;text-decoration:none}
.btn-sm:hover{transform:translateY(-1px)}
.btn-approve{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.btn-approve:hover{background:rgba(16,185,129,.2)}
.btn-reject{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}
.btn-reject:hover{background:rgba(239,68,68,.2)}
.btn-toggle{background:var(--abg);color:#92400e;border:1px solid rgba(245,158,11,.2)}
.btn-toggle:hover{background:rgba(245,158,11,.2)}
.btn-toggle.on{background:rgba(100,116,139,.1);color:var(--muted);border-color:var(--bd)}
.empty{padding:56px 20px;text-align:center}
.empty-ico{width:64px;height:64px;border-radius:50%;background:rgba(100,116,139,.08);color:rgba(100,116,139,.35);
    display:flex;align-items:center;justify-content:center;margin:0 auto 14px}
.empty-t{font-size:13px;font-weight:700;color:var(--muted)}
.empty-s{font-size:11px;color:rgba(100,116,139,.65);margin-top:4px}
.pag{padding:12px 20px;border-top:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
.pag-info{font-size:11.5px;color:var(--muted)}
.btn-p{display:inline-flex;align-items:center;gap:5px;padding:8px 14px;border-radius:8px;
    background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;font-size:11.5px;font-weight:700;
    border:none;cursor:pointer;text-decoration:none;transition:all .13s;font-family:var(--font)}
.btn-p:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}
.flag{font-size:16px;line-height:1}
.globe-ico{display:inline-flex;align-items:center;color:var(--muted);opacity:.6}
@media(max-width:900px){.sb{transform:translateX(-100%)}.sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.32)}.sb-ov.open{display:block}.mn{margin-left:0}.kpi-g{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.con{padding:13px}.tb{padding:0 13px}}
</style>
@endpush

@section('content')
@php
    $me     = auth()->user();
    $meName = $me->name ?? 'Fondateur';
    $meInit = strtoupper(substr($meName,0,1));

    $totalPending = \App\Models\DeliveryCompany::where('approved', false)->count();

    $flagMap = [
        'GN'=>'🇬🇳','SN'=>'🇸🇳','CI'=>'🇨🇮','ML'=>'🇲🇱','BF'=>'🇧🇫','NE'=>'🇳🇪','TG'=>'🇹🇬',
        'BJ'=>'🇧🇯','CM'=>'🇨🇲','CD'=>'🇨🇩','CG'=>'🇨🇬','GA'=>'🇬🇦','MR'=>'🇲🇷','MA'=>'🇲🇦',
        'TN'=>'🇹🇳','DZ'=>'🇩🇿','EG'=>'🇪🇬','NG'=>'🇳🇬','GH'=>'🇬🇭','KE'=>'🇰🇪','TZ'=>'🇹🇿',
        'RW'=>'🇷🇼','ZA'=>'🇿🇦','ET'=>'🇪🇹','FR'=>'🇫🇷','DE'=>'🇩🇪','GB'=>'🇬🇧','US'=>'🇺🇸',
        'CA'=>'🇨🇦','BE'=>'🇧🇪','ES'=>'🇪🇸','IT'=>'🇮🇹',
    ];

    $curMap = fn($c) => match(strtoupper($c ?? '')) {
        'GNF'           => 'GNF',
        'XOF','XAF'     => 'FCFA',
        'EUR'           => '€',
        'USD'           => '$',
        'GBP'           => '£',
        'MAD'           => 'MAD',
        'TND'           => 'TND',
        'DZD'           => 'DZD',
        'NGN'           => '₦',
        'GHS'           => 'GH₵',
        'KES'           => 'KSh',
        default         => strtoupper($c ?? 'GNF'),
    };

    $s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
    $I = [
        'bolt'      => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'bolt_sm'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'home'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 12L12 3l9 9M4 10v9a1 1 0 001 1h5v-5h4v5h5a1 1 0 001-1v-9" '.$s.'/></svg>',
        'store'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-6 9 6v11a1 1 0 01-1 1H4a1 1 0 01-1-1V9z" '.$s.'/><polyline points="9 22 9 12 15 12 15 22" '.$s.'/></svg>',
        'bag'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" '.$s.'/><line x1="3" y1="6" x2="21" y2="6" '.$s.'/><path d="M16 10a4 4 0 01-8 0" '.$s.'/></svg>',
        'box'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" '.$s.'/><polyline points="3.27 6.96 12 12.01 20.73 6.96" '.$s.'/><line x1="12" y1="22.08" x2="12" y2="12" '.$s.'/></svg>',
        'brief'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="2" y="7" width="20" height="14" rx="2" '.$s.'/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2M12 12v4M10 14h4" '.$s.'/></svg>',
        'users'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" '.$s.'/></svg>',
        'truck'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" rx="1" '.$s.'/><path d="M16 8h4l3 3v5h-7V8z" '.$s.'/><circle cx="5.5" cy="18.5" r="2.5" '.$s.'/><circle cx="18.5" cy="18.5" r="2.5" '.$s.'/></svg>',
        'truck_lg'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" rx="1" '.$s.'/><path d="M16 8h4l3 3v5h-7V8z" '.$s.'/><circle cx="5.5" cy="18.5" r="2.5" '.$s.'/><circle cx="18.5" cy="18.5" r="2.5" '.$s.'/></svg>',
        'bike'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="5.5" cy="17.5" r="3.5" '.$s.'/><circle cx="18.5" cy="17.5" r="3.5" '.$s.'/><path d="M15 6h2l2 5.5M5.5 17.5L9 7h5l2 5H8.5" '.$s.'/><path d="M9 7l1.5 5.5" '.$s.'/></svg>',
        'bike_lg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="5.5" cy="17.5" r="3.5" '.$s.'/><circle cx="18.5" cy="17.5" r="3.5" '.$s.'/><path d="M15 6h2l2 5.5M5.5 17.5L9 7h5l2 5H8.5" '.$s.'/><path d="M9 7l1.5 5.5" '.$s.'/></svg>',
        'bike_sm'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="5.5" cy="17.5" r="3.5" '.$s.'/><circle cx="18.5" cy="17.5" r="3.5" '.$s.'/><path d="M15 6h2l2 5.5M5.5 17.5L9 7h5l2 5H8.5" '.$s.'/><path d="M9 7l1.5 5.5" '.$s.'/></svg>',
        'map'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" '.$s.'/><line x1="8" y1="2" x2="8" y2="18" '.$s.'/><line x1="16" y1="6" x2="16" y2="22" '.$s.'/></svg>',
        'map_sm'    => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" '.$s.'/><line x1="8" y1="2" x2="8" y2="18" '.$s.'/><line x1="16" y1="6" x2="16" y2="22" '.$s.'/></svg>',
        'pin'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" '.$s.'/><circle cx="12" cy="10" r="3" '.$s.'/></svg>',
        'card'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="1" y="4" width="22" height="16" rx="2" '.$s.'/><line x1="1" y1="10" x2="23" y2="10" '.$s.'/></svg>',
        'trend'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18" '.$s.'/><polyline points="17 6 23 6 23 12" '.$s.'/></svg>',
        'dollar'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><line x1="12" y1="1" x2="12" y2="23" '.$s.'/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" '.$s.'/></svg>',
        'receipt'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1z" '.$s.'/><line x1="8" y1="9" x2="16" y2="9" '.$s.'/><line x1="8" y1="13" x2="16" y2="13" '.$s.'/><line x1="8" y1="17" x2="12" y2="17" '.$s.'/></svg>',
        'star'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" '.$s.'/></svg>',
        'ticket'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" '.$s.'/><line x1="7" y1="7" x2="7.01" y2="7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>',
        'cog'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" '.$s.'/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" '.$s.'/></svg>',
        'user'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" '.$s.'/></svg>',
        'user_sm'   => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" '.$s.'/></svg>',
        'logout'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" '.$s.'/></svg>',
        'menu'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><line x1="3" y1="6" x2="21" y2="6" '.$s.'/><line x1="3" y1="12" x2="21" y2="12" '.$s.'/><line x1="3" y1="18" x2="21" y2="18" '.$s.'/></svg>',
        'x'         => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" '.$s.'/><line x1="6" y1="6" x2="18" y2="18" '.$s.'/></svg>',
        'x_sm'      => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" '.$s.'/><line x1="6" y1="6" x2="18" y2="18" '.$s.'/></svg>',
        'check'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" '.$s.'/></svg>',
        'check_sm'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" '.$s.'/></svg>',
        'check_c'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" '.$s.'/><polyline points="22 4 12 14.01 9 11.01" '.$s.'/></svg>',
        'clock'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" '.$s.'/><polyline points="12 6 12 12 16 14" '.$s.'/></svg>',
        'clock_lg'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" '.$s.'/><polyline points="12 6 12 12 16 14" '.$s.'/></svg>',
        'clock_sm'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" '.$s.'/><polyline points="12 6 12 12 16 14" '.$s.'/></svg>',
        'power'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M18.36 6.64a9 9 0 11-12.73 0" '.$s.'/><line x1="12" y1="2" x2="12" y2="12" '.$s.'/></svg>',
        'slash_c'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" '.$s.'/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07" '.$s.'/></svg>',
        'globe'     => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" '.$s.'/><line x1="2" y1="12" x2="22" y2="12" '.$s.'/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" '.$s.'/></svg>',
        'search'    => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" '.$s.'/><line x1="21" y1="21" x2="16.65" y2="16.65" '.$s.'/></svg>',
        'chevron_d' => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><polyline points="6 9 12 15 18 9" '.$s.'/></svg>',
        'pause'     => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><rect x="6" y="4" width="4" height="16" '.$s.'/><rect x="14" y="4" width="4" height="16" '.$s.'/></svg>',
        'play'      => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polygon points="5 3 19 12 5 21 5 3" '.$s.'/></svg>',
        'building'  => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" '.$s.'/><path d="M9 9h2M9 13h2M13 9h2M13 13h2M9 17h6" '.$s.'/></svg>',
        'truck_em'  => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" rx="1" '.$s.'/><path d="M16 8h4l3 3v5h-7V8z" '.$s.'/><circle cx="5.5" cy="18.5" r="2.5" '.$s.'/><circle cx="18.5" cy="18.5" r="2.5" '.$s.'/></svg>',
    ];
@endphp

<div class="sa">

{{-- ════════ SIDEBAR ════════ --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap">{!! $I['bolt'] !!}</div>
        <div>
            <div class="sb-appname">{{ config('app.name','Shopio') }}</div>
            <div class="sb-apptag">Plateforme · Super Admin</div>
        </div>
        <button class="sb-close" onclick="closeSb()" title="Fermer">{!! $I['x'] !!}</button>
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
        <a href="{{ route('admin.shops.index') }}" class="sb-a">
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
            <span class="sb-i">{!! $I['users'] !!}</span><span>Clients boutiques</span>
        </a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a on">
            <span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span>
            @if($totalPending>0)<span class="sb-pill r">{{ $totalPending }}</span>@endif
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
            <span style="display:inline-flex;opacity:.8">{!! $I['user'] !!}</span>Mon profil
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sb-ft-row">
                <span style="display:inline-flex;opacity:.8">{!! $I['logout'] !!}</span>Déconnexion
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
        <a href="{{ route('profile.edit') }}" class="tb-btn" title="Profil">{!! $I['user_sm'] !!}</a>
    </div>
    <div style="position:relative">
        <button class="tb-user" id="tbU" onclick="toggleDrop()">
            <div class="tb-uav">{{ $meInit }}</div>
            <div style="text-align:left">
                <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                <div class="tb-urole">SuperAdmin</div>
            </div>
            <span style="display:inline-flex;color:var(--muted)">{!! $I['chevron_d'] !!}</span>
        </button>
        <div class="drop" id="drop">
            <a href="{{ route('profile.edit') }}" class="drop-i">
                {!! $I['user'] !!} Mon profil
            </a>
            <div class="drop-sep"></div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="drop-i d">{!! $I['logout'] !!} Déconnexion</button>
            </form>
        </div>
    </div>
</header>

<div class="con">

    @if(session('success'))
        <div class="flash ok">
            {!! $I['check_c'] !!} {{ session('success') }}
        </div>
    @endif
    @if(session('error') || session('danger'))
        <div class="flash err">
            {!! $I['x'] !!} {{ session('error') ?? session('danger') }}
        </div>
    @endif

    {{-- Breadcrumb --}}
    <div class="bc">
        <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;gap:4px">
            {!! $I['bolt_sm'] !!} Accueil
        </a>
        <span class="bs">›</span>
        <span style="color:var(--text);font-weight:600">Entreprises livraison</span>
    </div>

    {{-- Page header --}}
    <div class="ph">
        <div>
            <h1>{!! $I['truck_lg'] !!} Entreprises livraison</h1>
            <div class="ph-sub">Gestion et validation des entreprises du SaaS Livraison</div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="kpi-g">
        <div class="kpi p">
            <div class="kpi-ic p">{!! $I['truck'] !!}</div>
            <div class="kpi-v">{{ $stats['total'] }}</div>
            <div class="kpi-l">Total entreprises</div>
        </div>
        <div class="kpi g">
            <div class="kpi-ic g">{!! $I['check_c'] !!}</div>
            <div class="kpi-v">{{ $stats['approved'] }}</div>
            <div class="kpi-l">Approuvées</div>
        </div>
        <div class="kpi a">
            <div class="kpi-ic a">{!! $I['clock_lg'] !!}</div>
            <div class="kpi-v">{{ $stats['pending'] }}</div>
            <div class="kpi-l">En attente</div>
        </div>
        <div class="kpi b">
            <div class="kpi-ic b">{!! $I['power'] !!}</div>
            <div class="kpi-v">{{ $stats['active'] }}</div>
            <div class="kpi-l">Actives</div>
        </div>
        <div class="kpi r">
            <div class="kpi-ic r">{!! $I['slash_c'] !!}</div>
            <div class="kpi-v">{{ $stats['inactive'] }}</div>
            <div class="kpi-l">Inactives</div>
        </div>
        <div class="kpi i">
            <div class="kpi-ic i">{!! $I['bike_lg'] !!}</div>
            <div class="kpi-v">{{ $stats['drivers'] }}</div>
            <div class="kpi-l">Livreurs total</div>
        </div>
    </div>

    {{-- Status chips --}}
    @php
        $currentStatus  = request('status','');
        $currentCountry = request('country','');
        $currentSearch  = request('search','');
        $baseParams     = array_filter(['status'=>$currentStatus,'country'=>$currentCountry,'search'=>$currentSearch]);
    @endphp
    <div class="chips">
        <a href="{{ route('admin.entreprises.index', array_merge($baseParams,['status'=>''])) }}"
           class="chip {{ $currentStatus==='' ? 'on' : '' }}">Toutes</a>
        <a href="{{ route('admin.entreprises.index', array_merge($baseParams,['status'=>'approved'])) }}"
           class="chip approved {{ $currentStatus==='approved' ? 'on' : '' }}">
            <span class="chip-ico">{!! $I['check_sm'] !!}</span> Approuvées
        </a>
        <a href="{{ route('admin.entreprises.index', array_merge($baseParams,['status'=>'pending'])) }}"
           class="chip pending {{ $currentStatus==='pending' ? 'on' : '' }}">
            <span class="chip-ico">{!! $I['clock_sm'] !!}</span> En attente
            @if($stats['pending']>0)
                <span style="background:var(--amber);color:#fff;padding:1px 6px;border-radius:10px;font-size:10px;margin-left:2px">{{ $stats['pending'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.entreprises.index', array_merge($baseParams,['status'=>'active'])) }}"
           class="chip {{ $currentStatus==='active' ? 'on' : '' }}">
            <span style="display:inline-flex;color:var(--green)"><svg width="8" height="8" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4" fill="currentColor"/></svg></span>
            Actives
        </a>
        <a href="{{ route('admin.entreprises.index', array_merge($baseParams,['status'=>'inactive'])) }}"
           class="chip {{ $currentStatus==='inactive' ? 'on' : '' }}">
            <span style="display:inline-flex;color:var(--red)"><svg width="8" height="8" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4" fill="currentColor"/></svg></span>
            Inactives
        </a>
    </div>

    {{-- Filter bar --}}
    <form id="filterForm" method="GET" action="{{ route('admin.entreprises.index') }}">
        @if($currentStatus)<input type="hidden" name="status" value="{{ $currentStatus }}">@endif
        <div class="fb">
            @if($countries->count())
            <select name="country" class="fb-sel" onchange="document.getElementById('filterForm').submit()">
                <option value="">Tous les pays</option>
                @foreach($countries as $co)
                    <option value="{{ $co }}" {{ $currentCountry === $co ? 'selected' : '' }}>
                        {{ $flagMap[$co] ?? '🌐' }} {{ $co }}
                    </option>
                @endforeach
            </select>
            @endif
            <input type="text" name="search" class="fb-inp" placeholder="Nom, email, téléphone…" value="{{ $currentSearch }}">
            <button type="submit" class="fb-btn">
                {!! $I['search'] !!} Filtrer
            </button>
            @if($currentSearch || $currentCountry)
                <a href="{{ route('admin.entreprises.index', $currentStatus ? ['status'=>$currentStatus] : []) }}" class="fb-btn">
                    {!! $I['x_sm'] !!} Réinitialiser
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="sc">
        <div class="sc-h">
            <div class="sc-t">
                {!! $I['truck'] !!} Entreprises
                <span style="font-size:11px;font-weight:600;color:var(--muted);margin-left:4px">({{ $entreprises->total() }})</span>
            </div>
        </div>

        @if($entreprises->isEmpty())
            <div class="empty">
                <div class="empty-ico">{!! $I['truck_em'] !!}</div>
                <div class="empty-t">Aucune entreprise trouvée</div>
                <div class="empty-s">Modifiez vos filtres ou attendez de nouvelles inscriptions.</div>
            </div>
        @else
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Entreprise</th>
                        <th>Pays</th>
                        <th>Commission</th>
                        <th>Livreurs</th>
                        <th>Zones</th>
                        <th>Statut</th>
                        <th>Activation</th>
                        <th>Inscrit le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entreprises as $co)
                    @php
                        $pts  = explode(' ', $co->name ?? 'X');
                        $av   = strtoupper(substr($pts[0],0,1)) . strtoupper(substr($pts[1] ?? '',0,1));
                        $cCode = strtoupper($co->country ?? '');
                        $flag  = $flagMap[$cCode] ?? null;
                        $cur   = $curMap($co->currency ?? DeliveryCompany::currencyForCountry($co->country ?? ''));
                    @endphp
                    <tr>
                        {{-- Entreprise --}}
                        <td>
                            <div class="t-comp">
                                @if($co->image)
                                    <img src="{{ asset('storage/'.$co->image) }}" style="width:34px;height:34px;border-radius:9px;object-fit:cover;flex-shrink:0" alt="">
                                @else
                                    <div class="t-av">{!! $av ?: $I['building'] !!}</div>
                                @endif
                                <div>
                                    <div class="t-name">{{ $co->name }}</div>
                                    @if($co->email)<div class="t-sub">{{ $co->email }}</div>@endif
                                    @if($co->phone)<div class="t-sub">{{ $co->phone }}</div>@endif
                                </div>
                            </div>
                        </td>

                        {{-- Pays --}}
                        <td>
                            @if($co->country)
                                @if($flag)
                                    <span class="flag">{{ $flag }}</span>
                                    <span style="font-size:11px;color:var(--muted);margin-left:4px">{{ $co->country }}</span>
                                @else
                                    <span class="globe-ico" title="{{ $co->country }}">{!! $I['globe'] !!}</span>
                                    <span style="font-size:11px;color:var(--muted);margin-left:4px">{{ $co->country }}</span>
                                @endif
                            @else
                                <span style="color:var(--muted)">—</span>
                            @endif
                        </td>

                        {{-- Commission --}}
                        <td>
                            <span style="font-weight:700;color:var(--brand)">{{ number_format($co->commission_percent,1) }}%</span>
                        </td>

                        {{-- Livreurs --}}
                        <td>
                            @php $dc = $co->drivers_count ?? 0; @endphp
                            <span class="bdg {{ $dc>0 ? 'b' : 'm' }}">
                                {!! $I['bike_sm'] !!} {{ $dc }}
                            </span>
                        </td>

                        {{-- Zones --}}
                        <td>
                            @php $zc = $co->zones_count ?? 0; @endphp
                            <span class="bdg {{ $zc>0 ? 'g' : 'm' }}">
                                {!! $I['map_sm'] !!} {{ $zc }}
                            </span>
                        </td>

                        {{-- Statut approbation --}}
                        <td>
                            @if($co->approved)
                                <span class="bdg g">
                                    {!! $I['check_sm'] !!} Approuvée
                                </span>
                                @if($co->approved_at)
                                    <div style="font-size:10px;color:var(--muted);margin-top:3px">{{ optional($co->approved_at)->format('d/m/Y') }}</div>
                                @endif
                            @else
                                <span class="bdg a">
                                    {!! $I['clock_sm'] !!} En attente
                                </span>
                            @endif
                        </td>

                        {{-- Activation --}}
                        <td>
                            @if($co->active)
                                <span class="bdg g">
                                    <svg width="8" height="8" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4" fill="currentColor"/></svg>
                                    Active
                                </span>
                            @else
                                <span class="bdg r">
                                    <svg width="8" height="8" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4" fill="currentColor"/></svg>
                                    Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td>
                            <div style="font-size:11.5px">{{ optional($co->created_at)->format('d/m/Y') }}</div>
                            <div style="font-size:10px;color:var(--muted)">{{ optional($co->created_at)->diffForHumans() }}</div>
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="act-btns">
                                @if(!$co->approved)
                                    <form method="POST" action="{{ route('admin.entreprises.approve', $co) }}" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn-sm btn-approve">
                                            {!! $I['check_sm'] !!} Approuver
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.entreprises.reject', $co) }}" style="margin:0"
                                        onsubmit="return confirm('Révoquer l\'approbation de {{ addslashes($co->name) }} ?')">
                                        @csrf
                                        <button type="submit" class="btn-sm btn-reject">
                                            {!! $I['x_sm'] !!} Révoquer
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.entreprises.toggle', $co) }}" style="margin:0">
                                    @csrf
                                    <button type="submit" class="btn-sm btn-toggle {{ $co->active ? '' : 'on' }}"
                                        title="{{ $co->active ? 'Désactiver' : 'Activer' }}">
                                        @if($co->active)
                                            {!! $I['pause'] !!} Désactiver
                                        @else
                                            {!! $I['play'] !!} Activer
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($entreprises->hasPages())
        <div class="pag">
            <div class="pag-info">
                Affichage {{ $entreprises->firstItem() }}–{{ $entreprises->lastItem() }} sur {{ $entreprises->total() }}
            </div>
            {{ $entreprises->withQueryString()->links() }}
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
<script>
const sb=document.getElementById('sb'),ov=document.getElementById('sbOv'),mn=document.querySelector('.mn');
function closeSb(){sb.classList.remove('open');ov.classList.remove('open');sb.classList.add('closed');mn.classList.add('sb-closed')}
function toggleSb(){if(sb.classList.contains('closed')){sb.classList.remove('closed');mn.classList.remove('sb-closed')}else{sb.classList.toggle('open');ov.classList.toggle('open')}}
const dr=document.getElementById('drop');
function toggleDrop(){dr.classList.toggle('open')}
document.addEventListener('click',e=>{const b=document.getElementById('tbU');if(b&&!b.contains(e.target)&&!dr.contains(e.target))dr.classList.remove('open');});
let _t;
function nt(msg='Bientôt disponible'){const el=document.getElementById('toast');el.textContent=msg;el.style.transform='translateY(0)';el.style.opacity='1';clearTimeout(_t);_t=setTimeout(()=>{el.style.transform='translateY(80px)';el.style.opacity='0';},2800);}
</script>
@endpush
