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

/* ─── SIDEBAR (identique SuperAdmin) ─── */
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
    display:flex;align-items:center;justify-content:center;font-size:19px;
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
.sb-i{width:18px;text-align:center;font-size:15px;flex-shrink:0}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}
.sb-pill.a{background:var(--amber)}

.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;
    border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;
    background:none;border:none;cursor:pointer;font-family:var(--font);
    width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
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
    color:var(--muted);font-size:18px;transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
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
    color:var(--muted);font-size:15px;transition:all .13s;text-decoration:none}
.tb-btn:hover{background:var(--bg);color:var(--text)}
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
.drop-i{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:7px;
    font-size:12.5px;color:var(--text);text-decoration:none;transition:background .13s;
    background:none;border:none;cursor:pointer;font-family:var(--font);font-weight:500;width:100%}
.drop-i:hover{background:var(--bg)}
.drop-i.d{color:var(--red)}.drop-i.d:hover{background:var(--rbg)}
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
.stat-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px}
.stat-ico.p{background:rgba(139,92,246,.12)}
.stat-ico.g{background:var(--gbg)}
.stat-ico.a{background:var(--abg)}
.stat-ico.r{background:var(--rbg)}
.stat-v{font-size:26px;font-weight:900;color:var(--text);letter-spacing:-1px;line-height:1;margin-bottom:4px}
.stat-l{font-size:12px;color:var(--muted);font-weight:500}

/* ─── TOOLBAR ─── */
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;
    margin-bottom:16px;flex-wrap:wrap}
.filter-g{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.filter-btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;
    font-size:12px;font-weight:700;border:1px solid var(--bd);background:var(--card);
    color:var(--muted);cursor:pointer;transition:all .15s;font-family:var(--font);text-decoration:none}
.filter-btn:hover{background:var(--bg);color:var(--text)}
.filter-btn.active{background:rgba(124,58,237,.1);border-color:rgba(124,58,237,.3);color:var(--brand)}
.srch-box{display:flex;align-items:center;background:var(--card);border:1px solid var(--bd);
    border-radius:9px;padding:0 12px;gap:7px;height:36px;min-width:220px;transition:all .18s}
.srch-box:focus-within{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.srch-box input{border:none;background:none;font-size:12.5px;color:var(--text);width:100%;outline:none;font-family:var(--font)}
.srch-box input::placeholder{color:var(--muted)}

/* ─── MAIN CARD ─── */
.mc{background:var(--card);border-radius:14px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.mc-h{padding:16px 22px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px}
.mc-t{font-size:14px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px}

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

/* owner cell */
.owner-cell{display:flex;align-items:center;gap:9px}
.owner-av{width:30px;height:30px;border-radius:50%;
    background:linear-gradient(135deg,#10b981,#059669);
    display:flex;align-items:center;justify-content:center;
    font-size:11px;font-weight:800;color:#fff;flex-shrink:0}
.owner-name{font-size:12.5px;font-weight:600;color:var(--text)}
.owner-email{font-size:11px;color:var(--muted)}

/* badges */
.bdg{font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;white-space:nowrap}
.bdg.g{color:#065f46;background:var(--gbg);border:1px solid rgba(16,185,129,.2)}
.bdg.a{color:#92400e;background:var(--abg);border:1px solid rgba(245,158,11,.2)}
.bdg.r{color:#7f1d1d;background:var(--rbg);border:1px solid rgba(239,68,68,.2)}

/* action buttons */
.btn-approve{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;
    background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:12px;font-weight:700;
    border:none;cursor:pointer;font-family:var(--font);transition:all .15s}
.btn-approve:hover{transform:translateY(-1px);box-shadow:0 3px 10px rgba(16,185,129,.4)}
.btn-disable{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;
    background:var(--rbg);color:var(--red);font-size:12px;font-weight:700;
    border:1px solid rgba(239,68,68,.2);cursor:pointer;font-family:var(--font);transition:all .15s}
.btn-disable:hover{background:#fee2e2;transform:translateY(-1px)}

/* pagination */
.pag-wrap{padding:14px 20px;border-top:1px solid var(--bd);display:flex;justify-content:center}
.pag-wrap .pagination{gap:3px}
.pag-wrap .page-link{border-radius:8px !important;font-size:12px;font-weight:600;padding:5px 11px;transition:all .15s}
.pag-wrap .page-item.active .page-link{background:var(--brand);border-color:var(--bdk);color:#fff}

/* empty */
.empty-state{padding:56px 20px;text-align:center}
.empty-state-ico{font-size:44px;opacity:.25;margin-bottom:12px}
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
        <button class="sb-close" onclick="closeSb()" title="Fermer la sidebar">✕</button>
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
        <a href="{{ route('admin.shops.index') }}" class="sb-a on">
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
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a">
            <span class="sb-i">🚚</span><span>Entreprises livraison</span>
        </a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a">
            <span class="sb-i">🏍️</span><span>Livreurs</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">🗺️</span><span>Zones de livraison</span>
        </a>

        <div class="sb-sec fin">── Finance</div>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">💳</span><span>Paiements</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">💹</span><span>Commissions</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">💰</span><span>Revenus plateforme</span>
        </a>

        <div class="sb-sec plat">── Plateforme</div>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">👥</span><span>Tous les utilisateurs</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">📊</span><span>Rapports globaux</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">⭐</span><span>Avis &amp; Notation</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">🎫</span><span>Tickets support</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">⚙️</span><span>Paramètres système</span>
        </a>
    </nav>

    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row">
            <span style="font-size:14px">👤</span>Mon profil
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sb-ft-row">
                <span style="font-size:14px">⎋</span>Déconnexion
            </button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- ════════ MAIN ════════ --}}
<div class="mn">

    <header class="tb">
        <button class="ham" onclick="toggleSb()">☰</button>
        <div class="tb-ttl">🏪 <b>Boutiques</b></div>
        <div class="tb-sp"></div>
        <div class="tb-srch">
            <span style="color:var(--muted);font-size:13px">🔍</span>
            <input type="text" id="tbSearch" placeholder="Rechercher une boutique…">
        </div>
        <div class="tb-acts">
            <a href="{{ route('admin.dashboard') }}" class="tb-btn" title="Dashboard">🏠</a>
        </div>
        <div style="position:relative">
            <button class="tb-user" id="tbU" onclick="toggleDrop()">
                <div class="tb-uav">{{ $meInit }}</div>
                <div class="d-none d-sm-block" style="text-align:left">
                    <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                    <div class="tb-urole">SuperAdmin</div>
                </div>
                <span style="font-size:11px;color:var(--muted)">▾</span>
            </button>
            <div class="drop" id="drop">
                <a href="{{ route('profile.edit') }}" class="drop-i">👤 Mon profil</a>
                <a href="{{ route('admin.dashboard') }}" class="drop-i">🏠 Dashboard</a>
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
        @if(session('error'))
            <div class="flash err">❌ {{ session('error') }}</div>
        @endif

        {{-- Breadcrumb --}}
        <div class="bc">
            <span>⚡</span>
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
                <a href="{{ route('admin.dashboard') }}" class="btn-g">← Dashboard</a>
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
                    <div class="stat-ico p">🏪</div>
                </div>
                <div class="stat-v">{{ $allShops }}</div>
                <div class="stat-l">Total boutiques</div>
            </div>
            <div class="stat-c g">
                <div class="stat-top">
                    <div class="stat-ico g">✅</div>
                </div>
                <div class="stat-v">{{ $allApproved }}</div>
                <div class="stat-l">Approuvées</div>
            </div>
            <div class="stat-c a">
                <div class="stat-top">
                    <div class="stat-ico a">⏳</div>
                </div>
                <div class="stat-v">{{ $allPending }}</div>
                <div class="stat-l">En attente</div>
            </div>
            <div class="stat-c r">
                <div class="stat-top">
                    <div class="stat-ico r">🚫</div>
                </div>
                <div class="stat-v">0</div>
                <div class="stat-l">Désactivées</div>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="toolbar">
            <div class="filter-g">
                <button class="filter-btn active" onclick="filterStatus('all',this)">Toutes <span style="font-size:10px;opacity:.7">({{ $shops->total() }})</span></button>
                <button class="filter-btn" onclick="filterStatus('approved',this)">✅ Approuvées</button>
                <button class="filter-btn" onclick="filterStatus('pending',this)">⏳ En attente</button>
            </div>
            <div class="srch-box">
                <span style="color:var(--muted);font-size:13px">🔍</span>
                <input type="text" id="tableSearch" placeholder="Filtrer par nom, propriétaire…">
            </div>
        </div>

        {{-- Table --}}
        <div class="mc">
            <div class="mc-h">
                <div class="mc-t">
                    🏪 Liste des boutiques
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
                                    <div class="shop-av">{{ $initials ?: '🏪' }}</div>
                                    <div>
                                        <div class="shop-name">{{ $shop->name }}</div>
                                        <div class="shop-meta">
                                            @if($shop->country)<span>🌍 {{ $shop->country }}</span>@endif
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
                                    <span style="font-size:12.5px;color:var(--text)">📞 {{ $shop->phone }}</span>
                                @else
                                    <span style="color:var(--muted)">—</span>
                                @endif
                            </td>
                            <td>
                                @if($shop->address)
                                    <span style="font-size:12px;color:var(--muted)">📍 {{ Str::limit($shop->address,30) }}</span>
                                @else
                                    <span style="color:var(--muted)">—</span>
                                @endif
                            </td>
                            <td>
                                @if($shop->is_approved)
                                    <span class="bdg g">● Approuvée</span>
                                @else
                                    <span class="bdg a">⏳ En attente</span>
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
                                        <button type="submit" class="btn-disable">🚫 Désactiver</button>
                                    @else
                                        <button type="submit" class="btn-approve">✓ Approuver</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-ico">🏪</div>
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

/* live search (client-side sur la page courante) */
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
