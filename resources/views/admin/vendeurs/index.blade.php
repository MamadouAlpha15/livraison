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
    --teal:#14b8a6;--tealbg:rgba(20,184,166,.1);
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}

/* ─── SIDEBAR ─── */
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:19px;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}
.mn.sb-closed{margin-left:0}
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
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);font-size:18px;transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
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
.bc{display:flex;align-items:center;gap:5px;font-size:11.5px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}.bc .bs{color:rgba(0,0,0,.15)}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:20px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px}
.ph-sub{font-size:12px;color:var(--muted)}.ph-acts{display:flex;align-items:center;gap:8px}

/* ─── STAT CARDS ─── */
.stat-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:12px;margin-bottom:22px}
.stat-c{background:var(--card);border-radius:13px;padding:16px 18px;border:1px solid var(--bd);position:relative;overflow:hidden;transition:transform .18s;cursor:default}
.stat-c:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.08)}
.stat-c::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:13px 13px 0 0}
.stat-c.tot::before{background:linear-gradient(90deg,#7c3aed,#8b5cf6)}
.stat-c.adm::before{background:linear-gradient(90deg,#7c3aed,#a78bfa)}
.stat-c.ven::before{background:linear-gradient(90deg,#10b981,#34d399)}
.stat-c.emp::before{background:linear-gradient(90deg,#3b82f6,#60a5fa)}
.stat-c.liv::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.stat-c.dis::before{background:linear-gradient(90deg,#14b8a6,#2dd4bf)}
.stat-ico{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;margin-bottom:10px}
.stat-ico.tot{background:rgba(139,92,246,.12)}.stat-ico.adm{background:rgba(124,58,237,.1)}
.stat-ico.ven{background:var(--gbg)}.stat-ico.emp{background:var(--bbg)}
.stat-ico.liv{background:var(--abg)}.stat-ico.dis{background:var(--tealbg)}
.stat-v{font-size:24px;font-weight:900;color:var(--text);letter-spacing:-.8px;line-height:1;margin-bottom:3px}
.stat-l{font-size:11px;color:var(--muted);font-weight:600}

/* ─── FILTER ─── */
.filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px 18px}
.f-sel{padding:7px 11px;border-radius:8px;border:1px solid var(--bd);background:var(--bg);font-size:12.5px;color:var(--text);font-family:var(--font);outline:none;cursor:pointer;min-width:150px}
.f-sel:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.f-input{padding:7px 11px;border-radius:8px;border:1px solid var(--bd);background:var(--bg);font-size:12.5px;color:var(--text);font-family:var(--font);outline:none;flex:1;min-width:180px}
.f-input:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09);background:#fff}
.f-input::placeholder{color:var(--muted)}
.btn-search{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:8px;background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;font-family:var(--font);transition:all .15s}
.btn-search:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}
.btn-reset{display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;background:var(--bg);color:var(--muted);font-size:12px;font-weight:700;border:1px solid var(--bd);cursor:pointer;text-decoration:none;font-family:var(--font);transition:all .15s}
.btn-reset:hover{background:#e2e8f0;color:var(--text)}

/* ─── ROLE FILTER CHIPS ─── */
.role-chips{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.rc{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:11.5px;font-weight:700;border:1px solid var(--bd);background:var(--card);color:var(--muted);cursor:pointer;text-decoration:none;transition:all .15s}
.rc:hover{background:var(--bg)}
.rc.all.on{background:rgba(124,58,237,.1);border-color:rgba(124,58,237,.3);color:var(--brand)}
.rc.adm.on{background:rgba(124,58,237,.1);border-color:rgba(124,58,237,.3);color:var(--bdk)}
.rc.ven.on{background:var(--gbg);border-color:rgba(16,185,129,.3);color:#065f46}
.rc.emp.on{background:var(--bbg);border-color:rgba(59,130,246,.3);color:#1d4ed8}
.rc.liv.on{background:var(--abg);border-color:rgba(245,158,11,.3);color:#92400e}

/* ─── TABLE ─── */
.mc{background:var(--card);border-radius:14px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.mc-h{padding:15px 22px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.mc-t{font-size:14px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px}
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse;min-width:700px}
.tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;padding:10px 18px;background:#f8fafc;border-bottom:1px solid var(--bd);white-space:nowrap;text-align:left}
.tbl td{padding:13px 18px;font-size:12.5px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr{transition:background .13s}.tbl tbody tr:hover{background:rgba(124,58,237,.02)}

/* user cell */
.u-cell{display:flex;align-items:center;gap:10px}
.u-av{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0}
.u-name{font-size:13px;font-weight:700;color:var(--text)}
.u-email{font-size:11px;color:var(--muted)}

/* shop badge */
.shop-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;background:var(--gbg);font-size:11px;font-weight:700;color:#065f46;border:1px solid rgba(16,185,129,.2);white-space:nowrap}

/* role badge */
.rb{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:20px;display:inline-flex;align-items:center;gap:4px}
.rb.admin{background:rgba(124,58,237,.1);color:var(--bdk)}
.rb.vendeur{background:var(--gbg);color:#065f46}
.rb.employe,.rb.caissier{background:var(--bbg);color:#1d4ed8}
.rb.livreur{background:var(--abg);color:#92400e}
.rb.other{background:rgba(100,116,139,.1);color:var(--muted)}

/* dispo dot */
.dispo{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600}
.dispo-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.dispo-dot.on{background:var(--green)}
.dispo-dot.off{background:#cbd5e1}

/* pagination */
.pag-wrap{padding:14px 20px;border-top:1px solid var(--bd);display:flex;justify-content:center}
.pag-wrap .page-item.active .page-link{background:var(--brand);border-color:var(--bdk);color:#fff}
.pag-wrap .page-link{border-radius:8px !important;font-size:12px;font-weight:600;padding:5px 11px}

/* empty */
.empty-state{padding:56px 20px;text-align:center}
.empty-ico{font-size:44px;opacity:.22;margin-bottom:12px}
.empty-t{font-size:14px;font-weight:700;color:var(--muted)}
.empty-s{font-size:12px;color:var(--muted);margin-top:5px}

/* ─── RESPONSIVE ─── */
@media(max-width:900px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.32)}
    .sb-ov.open{display:block}.mn{margin-left:0}.ham{display:flex}
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
    $me     = auth()->user();
    $meName = $me->name ?? 'SuperAdmin';
    $meInit = strtoupper(substr($meName,0,1));
    $currentShop   = request('shop_id','');
    $currentRole   = request('role','');
    $currentSearch = request('search','');

    $roleMap = [
        'admin'   => ['label'=>'Admin boutique','ico'=>'👑','cls'=>'admin'],
        'vendeur' => ['label'=>'Vendeur',        'ico'=>'🛍️','cls'=>'vendeur'],
        'employe' => ['label'=>'Employé',        'ico'=>'👔','cls'=>'employe'],
        'caissier'=> ['label'=>'Caissier',       'ico'=>'💳','cls'=>'caissier'],
        'livreur' => ['label'=>'Livreur boutique','ico'=>'🏍️','cls'=>'livreur'],
    ];

    $avatarColors = ['#7c3aed','#10b981','#3b82f6','#f59e0b','#ef4444','#14b8a6','#6366f1','#ec4899'];
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
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a on"><span class="sb-i">👔</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">🧑‍💼</span><span>Clients boutiques</span></a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a"><span class="sb-i">🚚</span><span>Entreprises livraison</span></a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">🏍️</span><span>Livreurs</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">🗺️</span><span>Zones de livraison</span></a>
        <div class="sb-sec fin">── Finance</div>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">💳</span><span>Paiements</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">💹</span><span>Commissions</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">💰</span><span>Revenus plateforme</span></a>
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
            @csrf<button type="submit" class="sb-ft-row"><span style="font-size:14px">⎋</span>Déconnexion</button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- ════════ MAIN ════════ --}}
<div class="mn">
    <header class="tb">
        <button class="ham" onclick="toggleSb()">☰</button>
        <div class="tb-ttl">👔 <b>Vendeurs &amp; Employés</b></div>
        <div class="tb-sp"></div>
        <a href="{{ route('admin.dashboard') }}" class="tb-btn" title="Dashboard">🏠</a>
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

        <div class="bc">
            <span>⚡</span><span class="bs">›</span>
            <a href="{{ route('admin.dashboard') }}">Vue d'ensemble</a>
            <span class="bs">›</span>
            <span style="color:var(--text);font-weight:600">Vendeurs &amp; Employés</span>
        </div>

        <div class="ph">
            <div>
                <h1>Vendeurs &amp; Employés{{ $filteredShop ? ' — '.$filteredShop->name : ' des boutiques' }}</h1>
                <div class="ph-sub">
                    {{ number_format($stats['total']) }} membre{{ $stats['total']>1?'s':'' }}
                    {{ $filteredShop ? 'dans cette boutique' : 'sur toute la plateforme' }}
                    @if($filteredShop)
                        · <span style="color:var(--brand);font-weight:700">{{ $filteredShop->type ?? '' }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="stat-g">
            <div class="stat-c tot">
                <div class="stat-ico tot">👥</div>
                <div class="stat-v">{{ $stats['total'] }}</div>
                <div class="stat-l">Total membres</div>
            </div>
            <div class="stat-c adm">
                <div class="stat-ico adm">👑</div>
                <div class="stat-v">{{ $stats['admins'] }}</div>
                <div class="stat-l">Admins boutique</div>
            </div>
            <div class="stat-c ven">
                <div class="stat-ico ven">🛍️</div>
                <div class="stat-v">{{ $stats['vendeurs'] }}</div>
                <div class="stat-l">Vendeurs</div>
            </div>
            <div class="stat-c emp">
                <div class="stat-ico emp">👔</div>
                <div class="stat-v">{{ $stats['employes'] }}</div>
                <div class="stat-l">Employés / Caissiers</div>
            </div>
            <div class="stat-c liv">
                <div class="stat-ico liv">🏍️</div>
                <div class="stat-v">{{ $stats['livreurs'] }}</div>
                <div class="stat-l">Livreurs boutique</div>
            </div>
            <div class="stat-c dis">
                <div class="stat-ico dis">🟢</div>
                <div class="stat-v">{{ $stats['disponibles'] }}</div>
                <div class="stat-l">Disponibles</div>
            </div>
        </div>

        {{-- Chips rôle (counts suivent le filtre boutique actif) --}}
        <div class="role-chips">
            @php $shopParam = $currentShop ? ['shop_id'=>$currentShop] : []; @endphp
            <a href="{{ route('admin.vendeurs.index', array_merge($shopParam, request()->only('search'))) }}"
               class="rc all {{ !$currentRole ? 'on' : '' }}">Tous ({{ $stats['total'] }})</a>
            <a href="{{ route('admin.vendeurs.index', array_merge($shopParam, request()->only('search'), ['role'=>'admin'])) }}"
               class="rc adm {{ $currentRole==='admin' ? 'on' : '' }}">👑 Admins ({{ $stats['admins'] }})</a>
            <a href="{{ route('admin.vendeurs.index', array_merge($shopParam, request()->only('search'), ['role'=>'vendeur'])) }}"
               class="rc ven {{ $currentRole==='vendeur' ? 'on' : '' }}">🛍️ Vendeurs ({{ $stats['vendeurs'] }})</a>
            <a href="{{ route('admin.vendeurs.index', array_merge($shopParam, request()->only('search'), ['role'=>'employe'])) }}"
               class="rc emp {{ $currentRole==='employe' ? 'on' : '' }}">👔 Employés ({{ $stats['employes'] }})</a>
            <a href="{{ route('admin.vendeurs.index', array_merge($shopParam, request()->only('search'), ['role'=>'livreur'])) }}"
               class="rc liv {{ $currentRole==='livreur' ? 'on' : '' }}">🏍️ Livreurs ({{ $stats['livreurs'] }})</a>
        </div>

        {{-- Filtre --}}
        <form method="GET" action="{{ route('admin.vendeurs.index') }}" id="fForm" class="filter-bar">
            @if($currentRole)<input type="hidden" name="role" value="{{ $currentRole }}">@endif
            <div style="display:flex;align-items:center;gap:6px">
                <span style="font-size:13px;color:var(--muted)">🏪</span>
                <select name="shop_id" class="f-sel" onchange="document.getElementById('fForm').submit()">
                    <option value="">Toutes les boutiques</option>
                    @foreach($shops as $s)
                        <option value="{{ $s->id }}" {{ (string)$currentShop===(string)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="text" name="search" class="f-input" placeholder="Nom, email, téléphone…" value="{{ $currentSearch }}">
            <div style="display:flex;gap:6px">
                <button type="submit" class="btn-search">Filtrer</button>
                @if($currentRole||$currentShop||$currentSearch)
                    <a href="{{ route('admin.vendeurs.index') }}" class="btn-reset">✕ Effacer</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="mc">
            <div class="mc-h">
                <div class="mc-t">
                    👔 Équipes boutiques
                    @if($currentRole && isset($roleMap[$currentRole]))
                        <span style="font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;background:var(--gbg);color:#065f46">
                            {{ $roleMap[$currentRole]['ico'] }} {{ $roleMap[$currentRole]['label'] }}
                        </span>
                    @endif
                </div>
                <span style="font-size:12px;color:var(--muted);font-weight:600">
                    {{ number_format($vendeurs->total()) }} membre{{ $vendeurs->total()>1?'s':'' }}
                </span>
            </div>

            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Membre</th>
                            <th>Boutique</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Inscrit le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendeurs as $u)
                        @php
                            $init  = strtoupper(substr($u->name,0,1));
                            $color = $avatarColors[($u->id % count($avatarColors))];
                            $role  = $u->role_in_shop ?? 'other';
                            $rm    = $roleMap[$role] ?? ['label'=>ucfirst($role),'ico'=>'👤','cls'=>'other'];
                            $shop  = $u->shop;
                        @endphp
                        <tr>
                            <td>
                                <div class="u-cell">
                                    <div class="u-av" style="background:{{ $color }}">{{ $init }}</div>
                                    <div>
                                        <div class="u-name">{{ $u->name }}</div>
                                        <div class="u-email">{{ $u->email }}</div>
                                        @if($u->phone)
                                            <div class="u-email">📞 {{ $u->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($shop)
                                    <span class="shop-pill">
                                        🏪 {{ Str::limit($shop->name,20) }}
                                    </span>
                                    <div style="font-size:10.5px;color:var(--muted);margin-top:4px">{{ $shop->type ?? '' }}</div>
                                @else
                                    <span style="color:var(--muted);font-size:12px">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="rb {{ $rm['cls'] }}">{{ $rm['ico'] }} {{ $rm['label'] }}</span>
                            </td>
                            <td>
                                @if($role === 'livreur')
                                    @if($u->is_available)
                                        <span class="dispo"><span class="dispo-dot on"></span>Disponible</span>
                                    @else
                                        <span class="dispo"><span class="dispo-dot off"></span><span style="color:var(--muted)">Indisponible</span></span>
                                    @endif
                                @else
                                    @if($shop && $shop->is_approved)
                                        <span class="dispo"><span class="dispo-dot on"></span>Active</span>
                                    @else
                                        <span class="dispo"><span class="dispo-dot off"></span><span style="color:var(--muted)">Inactive</span></span>
                                    @endif
                                @endif
                            </td>
                            <td style="font-size:11.5px;color:var(--muted);white-space:nowrap">
                                {{ optional($u->created_at)->format('d/m/Y') }}<br>
                                <span style="font-size:10.5px">{{ optional($u->created_at)->format('H:i') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-ico">👔</div>
                                    <div class="empty-t">Aucun membre trouvé</div>
                                    <div class="empty-s">
                                        @if($currentSearch||$currentRole||$currentShop)
                                            Modifiez vos filtres pour voir plus de résultats.
                                        @else
                                            Les membres des équipes boutiques apparaîtront ici.
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($vendeurs->hasPages())
                <div class="pag-wrap">{{ $vendeurs->links() }}</div>
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
