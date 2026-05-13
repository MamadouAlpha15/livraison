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
.sb-pill.r{background:var(--red)}.sb-pill.a{background:var(--amber)}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}

/* ─── TOPBAR ─── */
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);font-size:18px;transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}
.mn.sb-closed{margin-left:0}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-srch{display:flex;align-items:center;background:var(--bg);border:1px solid var(--bd);border-radius:9px;padding:0 11px;gap:7px;height:34px;width:220px;transition:all .18s}
.tb-srch:focus-within{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09);background:#fff}
.tb-srch input{border:none;background:none;font-size:12.5px;color:var(--text);width:100%;outline:none;font-family:var(--font)}
.tb-srch input::placeholder{color:var(--muted)}
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
.ph-sub{font-size:12px;color:var(--muted)}.ph-acts{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

/* ─── STAT CARDS ─── */
.stat-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:12px;margin-bottom:22px}
.stat-c{background:var(--card);border-radius:13px;padding:16px;border:1px solid var(--bd);position:relative;overflow:hidden;transition:transform .18s;cursor:default}
.stat-c:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.08)}
.stat-c::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:13px 13px 0 0}
.stat-c.p::before{background:linear-gradient(90deg,#7c3aed,#8b5cf6)}
.stat-c.g::before{background:linear-gradient(90deg,#10b981,#34d399)}
.stat-c.sl::before{background:linear-gradient(90deg,#64748b,#94a3b8)}
.stat-c.r::before{background:linear-gradient(90deg,#ef4444,#f87171)}
.stat-c.a::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.stat-c.i::before{background:linear-gradient(90deg,#6366f1,#818cf8)}
.stat-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px}
.stat-ico{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px}
.stat-ico.p{background:rgba(139,92,246,.12)}.stat-ico.g{background:var(--gbg)}.stat-ico.sl{background:rgba(100,116,139,.1)}
.stat-ico.r{background:var(--rbg)}.stat-ico.a{background:var(--abg)}.stat-ico.i{background:rgba(99,102,241,.1)}
.stat-v{font-size:24px;font-weight:900;color:var(--text);letter-spacing:-1px;line-height:1;margin-bottom:3px}
.stat-l{font-size:11.5px;color:var(--muted);font-weight:500}

/* ─── TOOLBAR ─── */
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px;flex-wrap:wrap}
.filter-g{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.fbtn{display:inline-flex;align-items:center;gap:5px;padding:7px 13px;border-radius:8px;font-size:11.5px;font-weight:700;border:1px solid var(--bd);background:var(--card);color:var(--muted);cursor:pointer;transition:all .15s;font-family:var(--font)}
.fbtn:hover{background:var(--bg);color:var(--text)}
.fbtn.on{background:rgba(124,58,237,.1);border-color:rgba(124,58,237,.3);color:var(--brand)}
.toolbar-right{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.sel-shop{padding:7px 12px;border-radius:8px;border:1px solid var(--bd);background:var(--card);font-size:12px;color:var(--text);font-family:var(--font);cursor:pointer;outline:none;min-width:180px}
.sel-shop:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.srch-box{display:flex;align-items:center;background:var(--card);border:1px solid var(--bd);border-radius:9px;padding:0 11px;gap:7px;height:34px;min-width:200px;transition:all .18s}
.srch-box:focus-within{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.srch-box input{border:none;background:none;font-size:12.5px;color:var(--text);width:100%;outline:none;font-family:var(--font)}
.srch-box input::placeholder{color:var(--muted)}

/* ─── MAIN CARD ─── */
.mc{background:var(--card);border-radius:14px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.mc-h{padding:15px 22px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.mc-t{font-size:14px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px}

/* ─── TABLE ─── */
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse;min-width:820px}
.tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;padding:10px 18px;background:#f8fafc;border-bottom:1px solid var(--bd);white-space:nowrap;text-align:left}
.tbl td{padding:13px 18px;font-size:12.5px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr{transition:background .13s}
.tbl tbody tr:hover{background:rgba(124,58,237,.02)}

/* product cell */
.prod-cell{display:flex;align-items:center;gap:11px}
.prod-img{width:42px;height:42px;border-radius:10px;object-fit:cover;flex-shrink:0;border:1px solid var(--bd)}
.prod-av{width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,var(--brand),#4f46e5);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0}
.prod-name{font-size:13px;font-weight:700;color:var(--text)}
.prod-cat{font-size:10.5px;color:var(--muted);margin-top:1px}

/* shop cell */
.shop-cell{display:flex;align-items:center;gap:8px}
.shop-dot{width:28px;height:28px;border-radius:7px;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff;flex-shrink:0}
.shop-name{font-size:12px;font-weight:600;color:var(--text)}
.shop-owner{font-size:10.5px;color:var(--muted)}

/* price cell */
.price-main{font-size:13px;font-weight:800;color:var(--text)}
.price-old{font-size:11px;color:var(--muted);text-decoration:line-through;margin-left:4px}
.price-promo{font-size:10px;font-weight:700;color:#fff;background:var(--red);padding:1px 6px;border-radius:20px;margin-left:4px}

/* stock badge */
.stock-ok{color:#065f46;background:var(--gbg);border:1px solid rgba(16,185,129,.2);font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;display:inline-flex;align-items:center;gap:4px}
.stock-low{color:#92400e;background:var(--abg);border:1px solid rgba(245,158,11,.2);font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;display:inline-flex;align-items:center;gap:4px}
.stock-out{color:#7f1d1d;background:var(--rbg);border:1px solid rgba(239,68,68,.2);font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;display:inline-flex;align-items:center;gap:4px}

/* status badge */
.bdg-on{color:#065f46;background:var(--gbg);border:1px solid rgba(16,185,129,.2);font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;display:inline-block}
.bdg-off{color:var(--muted);background:rgba(100,116,139,.1);border:1px solid rgba(100,116,139,.2);font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;display:inline-block}
.bdg-feat{color:#1d4ed8;background:var(--bbg);border:1px solid rgba(59,130,246,.2);font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;display:inline-block}

/* action buttons */
.btn-on{display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border-radius:7px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:11px;font-weight:700;border:none;cursor:pointer;font-family:var(--font);transition:all .15s}
.btn-on:hover{transform:translateY(-1px);box-shadow:0 3px 10px rgba(16,185,129,.4)}
.btn-off{display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border-radius:7px;background:rgba(100,116,139,.1);color:var(--muted);font-size:11px;font-weight:700;border:1px solid rgba(100,116,139,.2);cursor:pointer;font-family:var(--font);transition:all .15s}
.btn-off:hover{background:rgba(100,116,139,.18);color:var(--text)}

/* pagination */
.pag-wrap{padding:14px 20px;border-top:1px solid var(--bd);display:flex;justify-content:center}
.pag-wrap .pagination{gap:3px}
.pag-wrap .page-link{border-radius:8px !important;font-size:12px;font-weight:600;padding:5px 11px;transition:all .15s}
.pag-wrap .page-item.active .page-link{background:var(--brand);border-color:var(--bdk);color:#fff}

/* empty */
.empty-state{padding:56px 20px;text-align:center}
.empty-ico{font-size:44px;opacity:.22;margin-bottom:12px}
.empty-t{font-size:14px;font-weight:700;color:var(--muted)}
.empty-s{font-size:12px;color:var(--muted);margin-top:5px}

/* buttons */
.btn-g{display:inline-flex;align-items:center;gap:6px;padding:8px 15px;border-radius:8px;background:var(--bg);color:var(--muted);font-size:12px;font-weight:700;border:1px solid var(--bd);cursor:pointer;text-decoration:none;transition:all .15s;font-family:var(--font)}
.btn-g:hover{background:#e2e8f0;color:var(--text)}

/* ─── RESPONSIVE ─── */
@media(max-width:900px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.32)}
    .sb-ov.open{display:block}.mn{margin-left:0}.ham{display:flex}.tb-srch{display:none}
    .stat-g{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:640px){
    .con{padding:13px}.tb{padding:0 13px}
    .ph{flex-direction:column}.toolbar{flex-direction:column;align-items:stretch}
    .stat-g{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:400px){.stat-g{grid-template-columns:repeat(2,1fr)}}
</style>
@endpush

@section('content')
@php
    $me     = auth()->user();
    $meName = $me->name ?? 'SuperAdmin';
    $meInit = strtoupper(substr($meName,0,1));
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
        <a href="{{ route('admin.shops.index') }}" class="sb-a">
            <span class="sb-i">🏪</span><span>Boutiques</span>
        </a>
        <a href="{{ route('admin.products.index') }}" class="sb-a on">
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
        <a href="{{ route('admin.zones.index') }}" class="sb-a">
            <span class="sb-i">🗺️</span><span>Zones de livraison</span>
        </a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a">
            <span class="sb-i">📍</span><span>Suivi en temps réel</span>
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
        <div class="tb-ttl">🛍️ <b>Produits</b></div>
        <div class="tb-sp"></div>
        <div class="tb-srch">
            <span style="color:var(--muted);font-size:13px">🔍</span>
            <input type="text" id="tbSearch" placeholder="Rechercher un produit…">
        </div>
        <div style="display:flex;align-items:center;gap:6px">
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
                    @csrf<button type="submit" class="drop-i d">⎋ Déconnexion</button>
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

        <div class="bc">
            <span>⚡</span><span class="bs">›</span>
            <a href="{{ route('admin.dashboard') }}">Vue d'ensemble</a>
            <span class="bs">›</span>
            <span style="color:var(--text);font-weight:600">Produits</span>
        </div>

        <div class="ph">
            <div>
                <h1>Catalogue produits — toutes boutiques</h1>
                <div class="ph-sub">{{ $stats['total'] }} produit{{ $stats['total'] > 1 ? 's' : '' }} enregistré{{ $stats['total'] > 1 ? 's' : '' }} sur la plateforme</div>
            </div>
            <div class="ph-acts">
                <a href="{{ route('admin.shops.index') }}" class="btn-g">🏪 Boutiques</a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="stat-g">
            <div class="stat-c p">
                <div class="stat-top"><div class="stat-ico p">🛍️</div></div>
                <div class="stat-v">{{ $stats['total'] }}</div>
                <div class="stat-l">Total produits</div>
            </div>
            <div class="stat-c g">
                <div class="stat-top"><div class="stat-ico g">✅</div></div>
                <div class="stat-v">{{ $stats['active'] }}</div>
                <div class="stat-l">Actifs</div>
            </div>
            <div class="stat-c sl">
                <div class="stat-top"><div class="stat-ico sl">🚫</div></div>
                <div class="stat-v">{{ $stats['inactive'] }}</div>
                <div class="stat-l">Inactifs</div>
            </div>
            <div class="stat-c r">
                <div class="stat-top"><div class="stat-ico r">📦</div></div>
                <div class="stat-v">{{ $stats['out_stock'] }}</div>
                <div class="stat-l">Rupture de stock</div>
            </div>
            <div class="stat-c i">
                <div class="stat-top"><div class="stat-ico i">⭐</div></div>
                <div class="stat-v">{{ $stats['featured'] }}</div>
                <div class="stat-l">En vedette</div>
            </div>
            <div class="stat-c a">
                <div class="stat-top"><div class="stat-ico a">🏷️</div></div>
                <div class="stat-v">{{ $stats['promo'] }}</div>
                <div class="stat-l">En promotion</div>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="toolbar">
            <div class="filter-g">
                <button class="fbtn on" onclick="doFilter('all',this)">Tous</button>
                <button class="fbtn" onclick="doFilter('active',this)">✅ Actifs</button>
                <button class="fbtn" onclick="doFilter('inactive',this)">🚫 Inactifs</button>
                <button class="fbtn" onclick="doFilter('outstock',this)">📦 Rupture</button>
                <button class="fbtn" onclick="doFilter('featured',this)">⭐ Vedette</button>
                <button class="fbtn" onclick="doFilter('promo',this)">🏷️ Promo</button>
            </div>
            <div class="toolbar-right">
                <select class="sel-shop" id="shopFilter" onchange="doShopFilter()">
                    <option value="">🏪 Toutes les boutiques</option>
                    @foreach($shops as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
                <div class="srch-box">
                    <span style="color:var(--muted);font-size:13px">🔍</span>
                    <input type="text" id="tableSearch" placeholder="Nom, catégorie, boutique…">
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="mc">
            <div class="mc-h">
                <div class="mc-t">
                    🛍️ Liste des produits
                    <span style="font-size:11px;font-weight:600;padding:2px 9px;border-radius:20px;background:rgba(124,58,237,.1);color:var(--brand)">
                        Page {{ $products->currentPage() }} / {{ $products->lastPage() }}
                    </span>
                </div>
                <div style="font-size:12px;color:var(--muted)">{{ $products->total() }} résultat{{ $products->total()>1?'s':'' }}</div>
            </div>

            <div class="tbl-wrap">
                <table class="tbl" id="prodTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produit</th>
                            <th>Boutique</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Statut</th>
                            <th>Tags</th>
                            <th>Ajouté le</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        @php
                            $init   = strtoupper(substr($product->name,0,1));
                            $sName  = $product->shop->name ?? '—';
                            $sInit  = strtoupper(substr($sName,0,1));
                            $oName  = $product->shop->owner->name ?? '—';
                            $isOut  = $product->stock <= 0;
                            $isLow  = $product->stock > 0 && $product->stock <= 5;
                            $isPromo= $product->original_price && $product->original_price > $product->price;

                            $filterTags = implode(' ', array_filter([
                                $product->is_active   ? 'active'   : 'inactive',
                                $isOut                ? 'outstock' : '',
                                $product->is_featured ? 'featured' : '',
                                $isPromo              ? 'promo'    : '',
                            ]));

                            $cur = match(strtoupper($product->shop->currency ?? 'GNF')) {
                                'GNF' => 'GNF', 'XOF','XAF' => 'FCFA',
                                'EUR' => '€', 'USD' => '$', 'GBP' => '£',
                                'MAD' => 'MAD', 'DZD' => 'DA', 'TND' => 'TND',
                                default => strtoupper($product->shop->currency ?? 'GNF'),
                            };
                        @endphp
                        <tr data-filter="{{ $filterTags }}"
                            data-shop="{{ $product->shop_id }}"
                            data-search="{{ strtolower($product->name . ' ' . ($product->category??'') . ' ' . $sName) }}">
                            <td style="color:var(--muted);font-weight:600;font-size:12px">
                                {{ ($products->currentPage()-1)*$products->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <div class="prod-cell">
                                    @if($product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" class="prod-img" alt="">
                                    @else
                                        <div class="prod-av">{{ $init }}</div>
                                    @endif
                                    <div>
                                        <div class="prod-name">{{ $product->name }}</div>
                                        <div class="prod-cat">
                                            @if($product->category)📂 {{ $product->category }}@endif
                                            @if($product->unit) · {{ $product->unit }}@endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="shop-cell">
                                    <div class="shop-dot">{{ $sInit }}</div>
                                    <div>
                                        <div class="shop-name">{{ $sName }}</div>
                                        <div class="shop-owner">{{ $oName }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;flex-wrap:wrap;gap:4px">
                                    <span class="price-main">
                                        {{ number_format($product->price,0,',',' ') }} {{ $cur }}
                                    </span>
                                    @if($isPromo)
                                        <span class="price-old">{{ number_format($product->original_price,0,',',' ') }} {{ $cur }}</span>
                                        <span class="price-promo">-{{ round((1 - $product->price/$product->original_price)*100) }}%</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($isOut)
                                    <span class="stock-out">📦 Rupture</span>
                                @elseif($isLow)
                                    <span class="stock-low">⚠️ {{ $product->stock }} restant{{ $product->stock>1?'s':'' }}</span>
                                @else
                                    <span class="stock-ok">✓ {{ $product->stock ?? '∞' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="bdg-on">● Actif</span>
                                @else
                                    <span class="bdg-off">○ Inactif</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;flex-wrap:wrap">
                                    @if($product->is_featured)
                                        <span class="bdg-feat">⭐ Vedette</span>
                                    @endif
                                    @if($isPromo)
                                        <span style="font-size:10px;font-weight:700;color:#7f1d1d;background:var(--rbg);border:1px solid rgba(239,68,68,.2);padding:2px 7px;border-radius:20px">🏷️ Promo</span>
                                    @endif
                                    @if(!$product->is_featured && !$isPromo)
                                        <span style="color:var(--muted);font-size:11px">—</span>
                                    @endif
                                </div>
                            </td>
                            <td style="font-size:11.5px;color:var(--muted);white-space:nowrap">
                                {{ optional($product->created_at)->format('d/m/Y') }}
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.products.toggle',$product) }}" style="margin:0">
                                    @csrf
                                    @if($product->is_active)
                                        <button type="submit" class="btn-off">⏸ Désactiver</button>
                                    @else
                                        <button type="submit" class="btn-on">▶ Activer</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-ico">🛍️</div>
                                    <div class="empty-t">Aucun produit trouvé</div>
                                    <div class="empty-s">Les produits des boutiques apparaîtront ici.</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="pag-wrap">{{ $products->links() }}</div>
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

/* active filter */
let currentFilter='all', currentShop='';

function doFilter(f, btn){
    currentFilter=f;
    document.querySelectorAll('.fbtn').forEach(b=>b.classList.remove('on'));
    btn.classList.add('on');
    applyFilters();
}

function doShopFilter(){
    currentShop=document.getElementById('shopFilter').value;
    applyFilters();
}

function applyFilters(){
    const q=document.getElementById('tableSearch').value.toLowerCase().trim();
    document.querySelectorAll('#prodTable tbody tr[data-filter]').forEach(tr=>{
        const tags=tr.dataset.filter;
        const matchF = currentFilter==='all' || tags.includes(currentFilter);
        const matchS = !currentShop || tr.dataset.shop===currentShop;
        const matchQ = !q || tr.dataset.search.includes(q);
        tr.style.display=(matchF && matchS && matchQ) ? '' : 'none';
    });
}

document.getElementById('tableSearch').addEventListener('input',applyFilters);
document.getElementById('tbSearch').addEventListener('input',e=>{
    document.getElementById('tableSearch').value=e.target.value;
    applyFilters();
});
</script>
@endpush
