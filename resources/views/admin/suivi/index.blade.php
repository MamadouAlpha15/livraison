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
        <a href="{{ route('admin.dashboard') }}" class="sb-a"><span class="sb-i">🏠</span><span>Vue d'ensemble</span></a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a"><span class="sb-i">🏪</span><span>Boutiques</span></a>
        <a href="{{ route('admin.products.index') }}" class="sb-a"><span class="sb-i">🛍️</span><span>Produits</span></a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a"><span class="sb-i">📦</span><span>Commandes boutiques</span></a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a"><span class="sb-i">👔</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">🧑‍💼</span><span>Clients boutiques</span></a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a">
            <span class="sb-i">🚚</span><span>Entreprises livraison</span>
            @if($totalPending>0)<span class="sb-pill r">{{ $totalPending }}</span>@endif
        </a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">🏍️</span><span>Livreurs</span></a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a"><span class="sb-i">🗺️</span><span>Zones de livraison</span></a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a on">
            <span class="sb-i">📍</span><span>Suivi en temps réel</span>
            @if($stats['en_livraison']>0)<span class="sb-pill g">{{ $stats['en_livraison'] }}</span>@endif
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

    {{-- Breadcrumb --}}
    <div class="bc">
        <a href="{{ route('admin.dashboard') }}">⚡ Accueil</a>
        <span class="bs">›</span>
        <span style="color:var(--text);font-weight:600">Suivi en temps réel</span>
    </div>

    {{-- Page header --}}
    <div class="ph">
        <div>
            <h1>📍 Suivi en temps réel</h1>
            <div class="ph-sub">
                Toutes les livraisons en cours · GPS actif
                @if($stats['live']>0)
                    <span style="margin-left:6px" class="pulse-wrap"><span class="pulse"></span>{{ $stats['live'] }} position(s) live</span>
                @endif
            </div>
        </div>
        <button onclick="location.reload()" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:var(--bg);border:1px solid var(--bd);font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;font-family:var(--font);transition:all .13s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--bg)'">
            🔄 Actualiser
        </button>
    </div>

    {{-- Stats --}}
    <div class="kpi-g">
        <div class="kpi a">
            <div class="kpi-ic a">🛵</div>
            <div class="kpi-v">{{ $stats['en_livraison'] }}</div>
            <div class="kpi-l">En livraison</div>
            <div class="kpi-s">Commandes actives</div>
        </div>
        <div class="kpi g">
            <div class="kpi-ic g">📡</div>
            <div class="kpi-v">{{ $stats['live'] }}</div>
            <div class="kpi-l">GPS live</div>
            <div class="kpi-s">Ping &lt; 5 min</div>
        </div>
        <div class="kpi b">
            <div class="kpi-ic b">📍</div>
            <div class="kpi-v">{{ $stats['avec_gps'] }}</div>
            <div class="kpi-l">Avec position</div>
            <div class="kpi-s">GPS transmis</div>
        </div>
        <div class="kpi r">
            <div class="kpi-ic r">🚫</div>
            <div class="kpi-v">{{ $stats['sans_gps'] }}</div>
            <div class="kpi-l">Sans GPS</div>
            <div class="kpi-s">Pas de position</div>
        </div>
        <div class="kpi p">
            <div class="kpi-ic p">🏍️</div>
            <div class="kpi-v">{{ $stats['livreurs_busy'] }}</div>
            <div class="kpi-l">Livreurs actifs</div>
            <div class="kpi-s">Statut "En livraison"</div>
        </div>
    </div>

    {{-- Carte --}}
    <div class="map-card">
        <div class="map-hd">
            <div class="map-ht">
                🗺️ Carte des livraisons
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
           class="chip live-chip {{ $curGps==='live' ? 'on' : '' }}">📡 GPS live</a>
        <a href="{{ route('admin.suivi.index', array_merge($bp,['gps'=>'with'])) }}"
           class="chip gps-chip {{ $curGps==='with' ? 'on' : '' }}">📍 Avec GPS</a>
        <a href="{{ route('admin.suivi.index', array_merge($bp,['gps'=>'without'])) }}"
           class="chip {{ $curGps==='without' ? 'on' : '' }}">🚫 Sans GPS</a>
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
                🛵 Livraisons en cours
                <span style="font-size:11px;font-weight:600;color:var(--muted)">({{ $orders->total() }})</span>
            </div>
        </div>

        @if($orders->isEmpty())
            <div class="empty">
                <div class="empty-ico">📍</div>
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
                                <div class="t-sub" style="margin-top:3px">🏪 {{ $order->shop->name }}</div>
                            @endif
                        </td>

                        {{-- Client --}}
                        <td>
                            @if($order->client)
                                <div class="t-name">{{ $order->client->name }}</div>
                                @if($order->client_phone)
                                    <div class="t-sub">📞 {{ $order->client_phone }}</div>
                                @endif
                            @else
                                <span style="color:var(--muted)">—</span>
                            @endif
                        </td>

                        {{-- Destination --}}
                        <td style="max-width:180px">
                            @if($order->delivery_destination)
                                <div style="font-size:11.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:175px" title="{{ $order->delivery_destination }}">
                                    📌 {{ $order->delivery_destination }}
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
                                <span class="bdg b">🏍️ {{ $livreurName }}</span>
                            @else
                                <span style="color:var(--muted)">Non assigné</span>
                            @endif
                        </td>

                        {{-- Entreprise --}}
                        <td>
                            @if($order->deliveryCompany)
                                <div style="font-size:12px;font-weight:600;color:var(--brand)">🚚 {{ $order->deliveryCompany->name }}</div>
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
                                        class="btn-map" style="margin-top:4px;display:block">
                                    🗺️ Voir sur carte
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
