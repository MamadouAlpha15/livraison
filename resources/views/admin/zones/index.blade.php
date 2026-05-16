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
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
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
.sb-i{width:18px;height:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:rgba(255,255,255,.75);opacity:.85}
.sb-a:hover .sb-i,.sb-a.on .sb-i{opacity:1;color:#fff}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}.sb-pill.a{background:var(--amber)}.sb-pill.g{background:var(--green)}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}.mn.sb-closed{margin-left:0}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100;box-shadow:0 1px 0 var(--bd)}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-acts{display:flex;align-items:center;gap:6px}
.tb-btn{width:34px;height:34px;background:none;border:1px solid var(--bd);border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;text-decoration:none}
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
.flash.err{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}
.bc{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}.bc .bs{color:rgba(0,0,0,.15)}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:19px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px;display:flex;align-items:center;gap:8px}
.ph-sub{font-size:11.5px;color:var(--muted)}
.kpi-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:12px;margin-bottom:22px}
.kpi{background:var(--card);border-radius:13px;padding:16px;border:1px solid var(--bd);position:relative;overflow:hidden;transition:transform .18s,box-shadow .18s;cursor:default}
.kpi:hover{transform:translateY(-2px);box-shadow:0 5px 18px rgba(0,0,0,.08)}
.kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:13px 13px 0 0}
.kpi.p::before{background:linear-gradient(90deg,#7c3aed,#8b5cf6)}
.kpi.g::before{background:linear-gradient(90deg,#10b981,#34d399)}
.kpi.r::before{background:linear-gradient(90deg,#ef4444,#f87171)}
.kpi.b::before{background:linear-gradient(90deg,#3b82f6,#60a5fa)}
.kpi.a::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.kpi.i::before{background:linear-gradient(90deg,#6366f1,#818cf8)}
.kpi-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-bottom:11px}
.kpi-ic.p{background:rgba(139,92,246,.12);color:var(--brand)}
.kpi-ic.g{background:var(--gbg);color:var(--green)}
.kpi-ic.r{background:var(--rbg);color:var(--red)}
.kpi-ic.b{background:var(--bbg);color:var(--blue)}
.kpi-ic.a{background:var(--abg);color:var(--amber)}
.kpi-ic.i{background:rgba(99,102,241,.1);color:#6366f1}
.kpi-v{font-size:24px;font-weight:900;color:var(--text);letter-spacing:-1px;line-height:1;margin-bottom:4px}
.kpi-l{font-size:11.5px;color:var(--muted);font-weight:500}
.chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px}
.chip{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:11.5px;font-weight:700;text-decoration:none;border:1.5px solid var(--bd);color:var(--muted);background:var(--card);transition:all .15s}
.chip:hover{border-color:#a78bfa;color:var(--brand)}
.chip.on{background:rgba(124,58,237,.09);border-color:#a78bfa;color:var(--brand)}
.chip.act{background:var(--gbg);border-color:rgba(16,185,129,.3);color:#065f46}
.chip.inact{background:var(--rbg);border-color:rgba(239,68,68,.3);color:#7f1d1d}
.fb{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.fb-sel{height:34px;border:1px solid var(--bd);border-radius:8px;padding:0 10px;font-size:12px;font-family:var(--font);color:var(--text);background:var(--card);outline:none;cursor:pointer;transition:border-color .15s}
.fb-sel:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.fb-inp{height:34px;border:1px solid var(--bd);border-radius:8px;padding:0 10px;font-size:12px;font-family:var(--font);color:var(--text);background:var(--card);outline:none;width:220px;transition:border-color .15s}
.fb-inp:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.fb-btn{height:34px;padding:0 14px;border-radius:8px;border:1px solid var(--bd);background:var(--card);font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;font-family:var(--font);transition:all .13s;display:inline-flex;align-items:center;gap:5px;text-decoration:none}
.fb-btn:hover{background:var(--bg);color:var(--text)}
.filter-tag{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.2);color:#1d4ed8;margin-bottom:12px}
.filter-tag a{color:inherit;text-decoration:none;font-size:13px;line-height:1;opacity:.7;display:flex;align-items:center}
.filter-tag a:hover{opacity:1}
.sc{background:var(--card);border-radius:13px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.sc-h{padding:14px 20px 12px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.sc-t{font-size:13px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:7px}
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse}
.tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;padding:9px 16px;background:var(--bg);border-bottom:1px solid var(--bd);white-space:nowrap;text-align:left}
.tbl td{padding:11px 16px;font-size:12px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr:hover{background:rgba(124,58,237,.02)}
.z-name{display:flex;align-items:center;gap:8px}
.z-dot{width:12px;height:12px;border-radius:50%;flex-shrink:0;border:2px solid rgba(255,255,255,.6);box-shadow:0 0 0 1px rgba(0,0,0,.1)}
.z-label{font-weight:700;font-size:12.5px}
.z-desc{font-size:10.5px;color:var(--muted);margin-top:1px}
.bdg{font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;white-space:nowrap}
.bdg.g{color:#065f46;background:var(--gbg)}.bdg.r{color:#7f1d1d;background:var(--rbg)}
.bdg.m{color:var(--muted);background:rgba(100,116,139,.1)}.bdg.b{color:#1d4ed8;background:var(--bbg)}
.price-badge{display:inline-flex;align-items:center;font-size:13px;font-weight:900;color:var(--brand)}
.time-badge{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:600;color:var(--muted)}
.btn-sm{display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:7px;font-size:11px;font-weight:700;border:none;cursor:pointer;font-family:var(--font);transition:all .13s}
.btn-sm:hover{transform:translateY(-1px)}
.btn-toggle-on{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}
.btn-toggle-on:hover{background:rgba(239,68,68,.2)}
.btn-toggle-off{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.btn-toggle-off:hover{background:rgba(16,185,129,.2)}
.empty{padding:56px 20px;text-align:center}
.empty-ico{width:64px;height:64px;border-radius:50%;background:rgba(100,116,139,.08);color:rgba(100,116,139,.35);display:flex;align-items:center;justify-content:center;margin:0 auto 12px}
.empty-t{font-size:13px;font-weight:700;color:var(--muted)}
.empty-s{font-size:11px;color:rgba(100,116,139,.65);margin-top:4px}
.pag{padding:12px 20px;border-top:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
.pag-info{font-size:11.5px;color:var(--muted)}
.btn-g{display:inline-flex;align-items:center;gap:5px;padding:8px 14px;border-radius:8px;background:var(--bg);color:var(--muted);font-size:11.5px;font-weight:700;border:1px solid var(--bd);cursor:pointer;text-decoration:none;transition:all .13s;font-family:var(--font)}
.btn-g:hover{background:#e2e8f0;color:var(--text)}
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

    $fmtMin = function($min) {
        if ($min < 60) return $min . ' min';
        $h = intdiv($min, 60); $m = $min % 60;
        return $m > 0 ? "{$h}h{$m}" : "{$h}h";
    };

    $fmtCur = function($co) {
        $raw = $co->currency ?? \App\Models\DeliveryCompany::currencyForCountry($co->country ?? '');
        return match(strtoupper($raw)) {
            'GNF'        => 'GNF',
            'XOF','XAF'  => 'FCFA',
            'EUR'        => '€',
            'USD'        => '$',
            'GBP'        => '£',
            default      => strtoupper($raw),
        };
    };

    $globalCur = $filteredCompany ? $fmtCur($filteredCompany) : null;

    $s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
    $I = [
        'brand'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
        'home'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><polyline points="9 21 9 12 15 12 15 21"/></svg>',
        'store'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2L3 6v2a3 3 0 006 0 3 3 0 006 0 3 3 0 006 0V6l-3-4H6z"/><path d="M3 6h18M19 10v9a1 1 0 01-1 1H6a1 1 0 01-1-1v-9"/></svg>',
        'bag'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>',
        'box'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>',
        'brief'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>',
        'users'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
        'user'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'truck'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
        'bike'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6h-5l-3 8h10.5"/><path d="M15 6l3 6"/></svg>',
        'map'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>',
        'pin'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'card'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
        'trend'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
        'dollar'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>',
        'receipt' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>',
        'star'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'ticket'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M2 9a3 3 0 010-6h20a3 3 0 010 6"/><path d="M2 15a3 3 0 000 6h20a3 3 0 000-6"/><line x1="2" y1="12" x2="22" y2="12"/></svg>',
        'cog'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
        'logout'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
        'clock'   => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        'menu'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
        'close'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        'chevron' => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="6 9 12 15 18 9"/></svg>',
        'check'   => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="20 6 9 17 4 12"/></svg>',
        'pause'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>',
        'play'    => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="5 3 19 12 5 21 5 3"/></svg>',
        'arrdown' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>',
        'arrup'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>',
        'dot_g'   => '<svg width="8" height="8" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4" fill="#10b981"/></svg>',
        'dot_r'   => '<svg width="8" height="8" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4" fill="#ef4444"/></svg>',
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
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">{!! $I['user'] !!}</span><span>Clients boutiques</span></a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span>
            @if($totalPending>0)<span class="sb-pill r">{{ $totalPending }}</span>@endif
        </a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['bike'] !!}</span><span>Livreurs</span></a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a on"><span class="sb-i">{!! $I['map'] !!}</span><span>Zones de livraison</span></a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a"><span class="sb-i">{!! $I['pin'] !!}</span><span>Suivi en temps réel</span></a>
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
        <a href="{{ route('profile.edit') }}" class="sb-ft-row">{!! $I['user'] !!}Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sb-ft-row">{!! $I['logout'] !!}Déconnexion</button>
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
        <a href="{{ route('profile.edit') }}" class="tb-btn" title="Profil">{!! $I['user'] !!}</a>
    </div>
    <div style="position:relative">
        <button class="tb-user" id="tbU" onclick="toggleDrop()">
            <div class="tb-uav">{{ $meInit }}</div>
            <div style="text-align:left">
                <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                <div class="tb-urole">SuperAdmin</div>
            </div>
            {!! $I['chevron'] !!}
        </button>
        <div class="drop" id="drop">
            <a href="{{ route('profile.edit') }}" class="drop-i">{!! $I['user'] !!} Mon profil</a>
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
        <div class="flash ok">{!! $I['check'] !!} {{ session('success') }}</div>
    @endif
    @if(session('error') || session('danger'))
        <div class="flash err">{!! $I['close'] !!} {{ session('error') ?? session('danger') }}</div>
    @endif

    <div class="bc">
        <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;gap:4px">{!! $I['brand'] !!} Accueil</a>
        <span class="bs">›</span>
        @if($filteredCompany)
            <a href="{{ route('admin.entreprises.index') }}">Entreprises</a>
            <span class="bs">›</span>
            <span style="color:var(--text);font-weight:600">{{ $filteredCompany->name }}</span>
            <span class="bs">›</span>
            <span style="color:var(--text);font-weight:600">Zones</span>
        @else
            <span style="color:var(--text);font-weight:600">Zones de livraison</span>
        @endif
    </div>

    <div class="ph">
        <div>
            <h1>{!! $I['map'] !!} Zones de livraison
                @if($filteredCompany)
                    <span style="font-size:14px;font-weight:600;color:var(--muted)">— {{ $filteredCompany->name }}</span>
                @endif
            </h1>
            <div class="ph-sub">
                @if($filteredCompany)
                    Zones de livraison de <strong>{{ $filteredCompany->name }}</strong>
                @else
                    Toutes les zones de livraison du SaaS Livraison
                @endif
            </div>
        </div>
        @if($filteredCompany)
        <a href="{{ route('admin.zones.index') }}" class="btn-g">{!! $I['close'] !!} Voir toutes</a>
        @endif
    </div>

    @if($filteredCompany)
    <div style="margin-bottom:12px">
        <span class="filter-tag">
            {!! $I['truck'] !!} {{ $filteredCompany->name }}
            <a href="{{ route('admin.zones.index', array_filter(['status'=>request('status'),'search'=>request('search')])) }}">{!! $I['close'] !!}</a>
        </span>
    </div>
    @endif

    <div class="kpi-g">
        <div class="kpi p">
            <div class="kpi-ic p">{!! $I['map'] !!}</div>
            <div class="kpi-v">{{ $stats['total'] }}</div>
            <div class="kpi-l">Total zones</div>
        </div>
        <div class="kpi g">
            <div class="kpi-ic g">{!! $I['dot_g'] !!}</div>
            <div class="kpi-v">{{ $stats['actives'] }}</div>
            <div class="kpi-l">Actives</div>
        </div>
        <div class="kpi r">
            <div class="kpi-ic r">{!! $I['dot_r'] !!}</div>
            <div class="kpi-v">{{ $stats['inactives'] }}</div>
            <div class="kpi-l">Inactives</div>
        </div>
        <div class="kpi b">
            <div class="kpi-ic b">{!! $I['dollar'] !!}</div>
            <div class="kpi-v" style="font-size:18px">
                {{ $stats['prix_moy'] ? number_format($stats['prix_moy'], 0, ',', ' ') : '–' }}
                @if($stats['prix_moy'] && $globalCur)<span style="font-size:12px;font-weight:600;color:var(--muted)"> {{ $globalCur }}</span>@endif
            </div>
            <div class="kpi-l">Prix moyen</div>
        </div>
        <div class="kpi a">
            <div class="kpi-ic a">{!! $I['arrdown'] !!}</div>
            <div class="kpi-v" style="font-size:18px">
                {{ $stats['prix_min'] ? number_format($stats['prix_min'], 0, ',', ' ') : '–' }}
                @if($stats['prix_min'] && $globalCur)<span style="font-size:12px;font-weight:600;color:var(--muted)"> {{ $globalCur }}</span>@endif
            </div>
            <div class="kpi-l">Prix min</div>
        </div>
        <div class="kpi i">
            <div class="kpi-ic i">{!! $I['arrup'] !!}</div>
            <div class="kpi-v" style="font-size:18px">
                {{ $stats['prix_max'] ? number_format($stats['prix_max'], 0, ',', ' ') : '–' }}
                @if($stats['prix_max'] && $globalCur)<span style="font-size:12px;font-weight:600;color:var(--muted)"> {{ $globalCur }}</span>@endif
            </div>
            <div class="kpi-l">Prix max</div>
        </div>
    </div>

    @php
        $curStatus  = request('status','');
        $curCompany = request('company_id','');
        $curSearch  = request('search','');
        $bp = array_filter(['company_id'=>$curCompany,'search'=>$curSearch]);
    @endphp
    <div class="chips">
        <a href="{{ route('admin.zones.index', $bp) }}"
           class="chip {{ $curStatus==='' ? 'on' : '' }}">Toutes</a>
        <a href="{{ route('admin.zones.index', array_merge($bp,['status'=>'active'])) }}"
           class="chip act {{ $curStatus==='active' ? 'on' : '' }}">{!! $I['dot_g'] !!} Actives</a>
        <a href="{{ route('admin.zones.index', array_merge($bp,['status'=>'inactive'])) }}"
           class="chip inact {{ $curStatus==='inactive' ? 'on' : '' }}">{!! $I['dot_r'] !!} Inactives</a>
    </div>

    <form id="filterForm" method="GET" action="{{ route('admin.zones.index') }}">
        @if($curStatus)<input type="hidden" name="status" value="{{ $curStatus }}">@endif
        <div class="fb">
            <select name="company_id" class="fb-sel" onchange="document.getElementById('filterForm').submit()">
                <option value="">Toutes les entreprises</option>
                @foreach($companies as $co)
                    <option value="{{ $co->id }}" {{ (string)$curCompany === (string)$co->id ? 'selected' : '' }}>
                        {{ $co->name }}
                    </option>
                @endforeach
            </select>
            <input type="text" name="search" class="fb-inp" placeholder="Nom ou description…" value="{{ $curSearch }}">
            <button type="submit" class="fb-btn">Filtrer</button>
            @if($curSearch || $curCompany)
                <a href="{{ route('admin.zones.index', $curStatus ? ['status'=>$curStatus] : []) }}" class="fb-btn">{!! $I['close'] !!} Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="sc">
        <div class="sc-h">
            <div class="sc-t">
                {!! $I['map'] !!} Zones
                <span style="font-size:11px;font-weight:600;color:var(--muted)">({{ $zones->total() }})</span>
            </div>
        </div>

        @if($zones->isEmpty())
            <div class="empty">
                <div class="empty-ico">{!! $I['map'] !!}</div>
                <div class="empty-t">Aucune zone trouvée</div>
                <div class="empty-s">Modifiez vos filtres ou sélectionnez une autre entreprise.</div>
            </div>
        @else
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Zone</th>
                        <th>Entreprise</th>
                        <th>Prix</th>
                        <th>Délai estimé</th>
                        <th>Statut</th>
                        <th>Créée le</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($zones as $zone)
                    @php
                        $co  = $zone->company;
                        $cur = $co ? $fmtCur($co) : 'GNF';
                    @endphp
                    <tr>
                        <td>
                            <div class="z-name">
                                <div class="z-dot" style="background:{{ $zone->color ?? '#7c3aed' }}"></div>
                                <div>
                                    <div class="z-label">{{ $zone->name }}</div>
                                    @if($zone->description)
                                        <div class="z-desc">{{ Str::limit($zone->description, 55) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($co)
                                <a href="{{ route('admin.zones.index', ['company_id'=>$co->id]) }}"
                                   style="display:inline-flex;align-items:center;gap:5px;color:var(--brand);font-weight:600;font-size:12px;text-decoration:none">
                                    {!! $I['truck'] !!} {{ $co->name }}
                                </a>
                            @else
                                <span style="color:var(--muted)">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="price-badge">
                                {{ number_format($zone->price, 0, ',', ' ') }}
                                <span style="font-size:11px;font-weight:600;margin-left:3px;color:var(--muted)">{{ $cur }}</span>
                            </span>
                        </td>
                        <td>
                            <span class="time-badge">
                                {!! $I['clock'] !!} {{ $fmtMin($zone->estimated_minutes) }}
                            </span>
                        </td>
                        <td>
                            @if($zone->active)
                                <span class="bdg g">{!! $I['dot_g'] !!} Active</span>
                            @else
                                <span class="bdg r">{!! $I['dot_r'] !!} Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:11.5px">{{ optional($zone->created_at)->format('d/m/Y') }}</div>
                            <div style="font-size:10px;color:var(--muted)">{{ optional($zone->created_at)->diffForHumans() }}</div>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.zones.toggle', $zone) }}" style="margin:0">
                                @csrf
                                @if($zone->active)
                                    <button type="submit" class="btn-sm btn-toggle-on">{!! $I['pause'] !!} Désactiver</button>
                                @else
                                    <button type="submit" class="btn-sm btn-toggle-off">{!! $I['play'] !!} Activer</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($zones->hasPages())
        <div class="pag">
            <div class="pag-info">Affichage {{ $zones->firstItem() }}–{{ $zones->lastItem() }} sur {{ $zones->total() }}</div>
            {{ $zones->withQueryString()->links() }}
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
