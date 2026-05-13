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

/* sidebar */
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
    display:flex;align-items:center;justify-content:center;font-size:19px;
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
.sb-i{width:18px;text-align:center;font-size:15px;flex-shrink:0}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}.sb-pill.a{background:var(--amber)}.sb-pill.g{background:var(--green)}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;
    color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;
    font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;
    background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);
    font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;
    transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}
.mn.sb-closed{margin-left:0}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);
    animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}

/* main */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;
    padding:0 22px;gap:12px;position:sticky;top:0;z-index:100;box-shadow:0 1px 0 var(--bd)}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;
    border-radius:7px;align-items:center;justify-content:center;color:var(--muted);font-size:18px;transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-acts{display:flex;align-items:center;gap:6px}
.tb-btn{width:34px;height:34px;background:none;border:1px solid var(--bd);border-radius:8px;
    cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:15px;transition:all .13s;text-decoration:none}
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

/* content */
.con{flex:1;padding:24px}
.flash{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:12.5px;font-weight:600}
.flash.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.flash.err{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}
.bc{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}.bc .bs{color:rgba(0,0,0,.15)}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:19px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px}
.ph-sub{font-size:11.5px;color:var(--muted)}

/* kpi */
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
.kpi-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;margin-bottom:11px}
.kpi-ic.p{background:rgba(139,92,246,.12)}.kpi-ic.g{background:var(--gbg)}.kpi-ic.a{background:var(--abg)}
.kpi-ic.b{background:var(--bbg)}.kpi-ic.r{background:var(--rbg)}.kpi-ic.i{background:var(--ibg)}
.kpi-v{font-size:24px;font-weight:900;color:var(--text);letter-spacing:-1px;line-height:1;margin-bottom:4px}
.kpi-l{font-size:11.5px;color:var(--muted);font-weight:500}

/* chips */
.chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px}
.chip{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:11.5px;font-weight:700;
    text-decoration:none;border:1.5px solid var(--bd);color:var(--muted);background:var(--card);transition:all .15s;cursor:pointer}
.chip:hover{border-color:#a78bfa;color:var(--brand)}
.chip.on{background:rgba(124,58,237,.09);border-color:#a78bfa;color:var(--brand)}
.chip.pending{background:var(--abg);border-color:rgba(245,158,11,.3);color:#92400e}
.chip.approved{background:var(--gbg);border-color:rgba(16,185,129,.3);color:#065f46}

/* filter bar */
.fb{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.fb-sel{height:34px;border:1px solid var(--bd);border-radius:8px;padding:0 10px;font-size:12px;
    font-family:var(--font);color:var(--text);background:var(--card);outline:none;cursor:pointer;transition:border-color .15s}
.fb-sel:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.fb-inp{height:34px;border:1px solid var(--bd);border-radius:8px;padding:0 10px;font-size:12px;
    font-family:var(--font);color:var(--text);background:var(--card);outline:none;width:220px;transition:border-color .15s}
.fb-inp:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.fb-btn{height:34px;padding:0 14px;border-radius:8px;border:1px solid var(--bd);background:var(--card);
    font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;font-family:var(--font);transition:all .13s}
.fb-btn:hover{background:var(--bg);color:var(--text)}

/* section card */
.sc{background:var(--card);border-radius:13px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.sc-h{padding:14px 20px 12px;border-bottom:1px solid var(--bd);
    display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.sc-t{font-size:13px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:7px}

/* table */
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

/* badges */
.bdg{font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;display:inline-flex;align-items:center;gap:3px;white-space:nowrap}
.bdg.g{color:#065f46;background:var(--gbg)}.bdg.a{color:#92400e;background:var(--abg)}
.bdg.r{color:#7f1d1d;background:var(--rbg)}.bdg.b{color:#1d4ed8;background:var(--bbg)}
.bdg.m{color:var(--muted);background:rgba(100,116,139,.1)}.bdg.p{color:var(--bdk);background:rgba(124,58,237,.1)}

/* action buttons */
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
.btn-view{background:var(--bbg);color:#1d4ed8;border:1px solid rgba(59,130,246,.2)}
.btn-view:hover{background:rgba(59,130,246,.18)}

/* empty */
.empty{padding:56px 20px;text-align:center}
.empty-ico{font-size:36px;opacity:.25;margin-bottom:10px}
.empty-t{font-size:13px;font-weight:700;color:var(--muted)}
.empty-s{font-size:11px;color:rgba(100,116,139,.65);margin-top:4px}

/* pagination */
.pag{padding:12px 20px;border-top:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
.pag-info{font-size:11.5px;color:var(--muted)}

/* buttons global */
.btn-p{display:inline-flex;align-items:center;gap:5px;padding:8px 14px;border-radius:8px;
    background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;font-size:11.5px;font-weight:700;
    border:none;cursor:pointer;text-decoration:none;transition:all .13s;font-family:var(--font)}
.btn-p:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}
.btn-g{display:inline-flex;align-items:center;gap:5px;padding:8px 14px;border-radius:8px;
    background:var(--bg);color:var(--muted);font-size:11.5px;font-weight:700;
    border:1px solid var(--bd);cursor:pointer;text-decoration:none;transition:all .13s;font-family:var(--font)}
.btn-g:hover{background:#e2e8f0;color:var(--text)}

/* flag */
.flag{font-size:16px;line-height:1}

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
        'GNF'                       => 'GNF',
        'XOF','XAF'                 => 'FCFA',
        'EUR'                       => '€',
        'USD'                       => '$',
        'GBP'                       => '£',
        'MAD'                       => 'MAD',
        'TND'                       => 'TND',
        'DZD'                       => 'DZD',
        'NGN'                       => '₦',
        'GHS'                       => 'GH₵',
        'KES'                       => 'KSh',
        default                     => strtoupper($c ?? 'GNF'),
    };
@endphp

<div class="sa">

{{-- ════════ SIDEBAR ════════ --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap">⚡</div>
        <div>
            <div class="sb-appname">{{ config('app.name','Shopio') }}</div>
            <div class="sb-apptag">Plateforme · Super Admin</div>
        </div>
        <button class="sb-close" onclick="closeSb()" title="Fermer">✕</button>
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
            <span class="sb-i">🏠</span><span>Vue d'ensemble</span>
        </a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a">
            <span class="sb-i">🏪</span><span>Boutiques</span>
        </a>
        <a href="{{ route('admin.products.index') }}" class="sb-a">
            <span class="sb-i">🛍️</span><span>Produits</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a">
            <span class="sb-i">📦</span><span>Commandes boutiques</span>
        </a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a">
            <span class="sb-i">👔</span><span>Vendeurs &amp; Employés</span>
        </a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a">
            <span class="sb-i">🧑‍💼</span><span>Clients boutiques</span>
        </a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a on">
            <span class="sb-i">🚚</span><span>Entreprises livraison</span>
            @if($totalPending>0)<span class="sb-pill r">{{ $totalPending }}</span>@endif
        </a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a">
            <span class="sb-i">🏍️</span><span>Livreurs</span>
        </a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a">
            <span class="sb-i">🗺️</span><span>Zones de livraison</span>
        </a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a">
            <span class="sb-i">📍</span><span>Suivi en temps réel</span>
        </a>
        <div class="sb-sec fin">── Finance</div>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">💳</span><span>Paiements</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">💹</span><span>Commissions</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">💰</span><span>Revenus plateforme</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">🧾</span><span>Factures &amp; Exports</span></a>
        <div class="sb-sec plat">── Plateforme</div>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">👥</span><span>Tous les utilisateurs</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">📊</span><span>Rapports globaux</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">⭐</span><span>Avis &amp; Notation</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">🎫</span><span>Tickets support</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">⚙️</span><span>Paramètres système</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row"><span style="font-size:14px">👤</span>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sb-ft-row"><span style="font-size:14px">⎋</span>Déconnexion</button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- ════════ MAIN ════════ --}}
<div class="mn">
<header class="tb">
    <button class="ham" onclick="toggleSb()">☰</button>
    <div class="tb-ttl">Super<b>Admin</b></div>
    <div class="tb-sp"></div>
    <div class="tb-acts">
        <a href="{{ route('profile.edit') }}" class="tb-btn" title="Profil">👤</a>
    </div>
    <div style="position:relative">
        <button class="tb-user" id="tbU" onclick="toggleDrop()">
            <div class="tb-uav">{{ $meInit }}</div>
            <div style="text-align:left">
                <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                <div class="tb-urole">SuperAdmin</div>
            </div>
            <span style="font-size:11px;color:var(--muted)">▾</span>
        </button>
        <div class="drop" id="drop">
            <a href="{{ route('profile.edit') }}" class="drop-i">👤 Mon profil</a>
            <div class="drop-sep"></div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="drop-i d">⎋ Déconnexion</button>
            </form>
        </div>
    </div>
</header>

<div class="con">

    @if(session('success'))
        <div class="flash ok">✅ {{ session('success') }}</div>
    @endif
    @if(session('error') || session('danger'))
        <div class="flash err">❌ {{ session('error') ?? session('danger') }}</div>
    @endif

    {{-- Breadcrumb --}}
    <div class="bc">
        <a href="{{ route('admin.dashboard') }}">⚡ Accueil</a>
        <span class="bs">›</span>
        <span style="color:var(--text);font-weight:600">Entreprises livraison</span>
    </div>

    {{-- Page header --}}
    <div class="ph">
        <div>
            <h1>🚚 Entreprises livraison</h1>
            <div class="ph-sub">Gestion et validation des entreprises du SaaS Livraison</div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="kpi-g">
        <div class="kpi p">
            <div class="kpi-ic p">🚚</div>
            <div class="kpi-v">{{ $stats['total'] }}</div>
            <div class="kpi-l">Total entreprises</div>
        </div>
        <div class="kpi g">
            <div class="kpi-ic g">✅</div>
            <div class="kpi-v">{{ $stats['approved'] }}</div>
            <div class="kpi-l">Approuvées</div>
        </div>
        <div class="kpi a">
            <div class="kpi-ic a">⏳</div>
            <div class="kpi-v">{{ $stats['pending'] }}</div>
            <div class="kpi-l">En attente</div>
        </div>
        <div class="kpi b">
            <div class="kpi-ic b">🟢</div>
            <div class="kpi-v">{{ $stats['active'] }}</div>
            <div class="kpi-l">Actives</div>
        </div>
        <div class="kpi r">
            <div class="kpi-ic r">🔴</div>
            <div class="kpi-v">{{ $stats['inactive'] }}</div>
            <div class="kpi-l">Inactives</div>
        </div>
        <div class="kpi i">
            <div class="kpi-ic i">🏍️</div>
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
           class="chip approved {{ $currentStatus==='approved' ? 'on' : '' }}">✅ Approuvées</a>
        <a href="{{ route('admin.entreprises.index', array_merge($baseParams,['status'=>'pending'])) }}"
           class="chip pending {{ $currentStatus==='pending' ? 'on' : '' }}">
            ⏳ En attente
            @if($stats['pending']>0)<span style="background:var(--amber);color:#fff;padding:1px 6px;border-radius:10px;font-size:10px;margin-left:2px">{{ $stats['pending'] }}</span>@endif
        </a>
        <a href="{{ route('admin.entreprises.index', array_merge($baseParams,['status'=>'active'])) }}"
           class="chip {{ $currentStatus==='active' ? 'on' : '' }}">🟢 Actives</a>
        <a href="{{ route('admin.entreprises.index', array_merge($baseParams,['status'=>'inactive'])) }}"
           class="chip {{ $currentStatus==='inactive' ? 'on' : '' }}">🔴 Inactives</a>
    </div>

    {{-- Filter bar --}}
    <form id="filterForm" method="GET" action="{{ route('admin.entreprises.index') }}">
        @if($currentStatus)<input type="hidden" name="status" value="{{ $currentStatus }}">@endif
        <div class="fb">
            @if($countries->count())
            <select name="country" class="fb-sel" onchange="document.getElementById('filterForm').submit()">
                <option value="">🌍 Tous les pays</option>
                @foreach($countries as $co)
                    <option value="{{ $co }}" {{ $currentCountry === $co ? 'selected' : '' }}>
                        {{ $flagMap[$co] ?? '🌐' }} {{ $co }}
                    </option>
                @endforeach
            </select>
            @endif
            <input type="text" name="search" class="fb-inp" placeholder="🔍 Nom, email, téléphone…" value="{{ $currentSearch }}">
            <button type="submit" class="fb-btn">Filtrer</button>
            @if($currentSearch || $currentCountry)
                <a href="{{ route('admin.entreprises.index', $currentStatus ? ['status'=>$currentStatus] : []) }}" class="fb-btn">✕ Réinitialiser</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="sc">
        <div class="sc-h">
            <div class="sc-t">
                🚚 Entreprises
                <span style="font-size:11px;font-weight:600;color:var(--muted);margin-left:4px">({{ $entreprises->total() }})</span>
            </div>
        </div>

        @if($entreprises->isEmpty())
            <div class="empty">
                <div class="empty-ico">🚚</div>
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
                        $pts = explode(' ', $co->name ?? 'X');
                        $av  = strtoupper(substr($pts[0],0,1)) . strtoupper(substr($pts[1] ?? '',0,1));
                        $flag = $flagMap[$co->country ?? ''] ?? '🌐';
                        $cur  = $curMap($co->currency ?? DeliveryCompany::currencyForCountry($co->country ?? ''));
                    @endphp
                    <tr>
                        {{-- Entreprise --}}
                        <td>
                            <div class="t-comp">
                                @if($co->image)
                                    <img src="{{ asset('storage/'.$co->image) }}" style="width:34px;height:34px;border-radius:9px;object-fit:cover;flex-shrink:0" alt="">
                                @else
                                    <div class="t-av">{{ $av ?: '🏢' }}</div>
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
                                <span class="flag">{{ $flag }}</span>
                                <span style="font-size:11px;color:var(--muted);margin-left:4px">{{ $co->country }}</span>
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
                            <span class="bdg {{ $dc>0 ? 'b' : 'm' }}">🏍️ {{ $dc }}</span>
                        </td>

                        {{-- Zones --}}
                        <td>
                            @php $zc = $co->zones_count ?? 0; @endphp
                            <span class="bdg {{ $zc>0 ? 'g' : 'm' }}">🗺️ {{ $zc }}</span>
                        </td>

                        {{-- Statut approbation --}}
                        <td>
                            @if($co->approved)
                                <span class="bdg g">✅ Approuvée</span>
                                @if($co->approved_at)
                                    <div style="font-size:10px;color:var(--muted);margin-top:3px">{{ optional($co->approved_at)->format('d/m/Y') }}</div>
                                @endif
                            @else
                                <span class="bdg a">⏳ En attente</span>
                            @endif
                        </td>

                        {{-- Activation --}}
                        <td>
                            @if($co->active)
                                <span class="bdg g">🟢 Active</span>
                            @else
                                <span class="bdg r">🔴 Inactive</span>
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
                                        <button type="submit" class="btn-sm btn-approve">✓ Approuver</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.entreprises.reject', $co) }}" style="margin:0"
                                        onsubmit="return confirm('Révoquer l\'approbation de {{ addslashes($co->name) }} ?')">
                                        @csrf
                                        <button type="submit" class="btn-sm btn-reject">✕ Révoquer</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.entreprises.toggle', $co) }}" style="margin:0">
                                    @csrf
                                    <button type="submit" class="btn-sm btn-toggle {{ $co->active ? '' : 'on' }}" title="{{ $co->active ? 'Désactiver' : 'Activer' }}">
                                        {{ $co->active ? '⏸ Désactiver' : '▶ Activer' }}
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
