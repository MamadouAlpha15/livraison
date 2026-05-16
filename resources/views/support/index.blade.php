@extends('layouts.app')
@section('title', 'Support · ' . ($shop->name ?? 'Boutique'))
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --brand:#6366f1;--brand-dk:#4f46e5;--brand-lt:#e0e7ff;--brand-mlt:#eef2ff;
    --sb-border:rgba(255,255,255,.08);--sb-act:rgba(99,102,241,.52);
    --sb-hov:rgba(255,255,255,.07);--sb-txt:rgba(255,255,255,.62);--sb-txt-act:#fff;
    --bg:#f8fafc;--surface:#ffffff;--border:#e2e8f0;--border-dk:#cbd5e1;
    --text:#0f172a;--text-2:#475569;--muted:#94a3b8;
    --font:'Plus Jakarta Sans',sans-serif;--mono:'JetBrains Mono',monospace;
    --r:14px;--r-sm:9px;--shadow-sm:0 1px 3px rgba(0,0,0,.06);--shadow:0 4px 16px rgba(0,0,0,.07);
    --sb-w:232px;--top-h:58px;
    --green:#10b981;--amber:#f59e0b;--red:#ef4444;
}
html{font-family:var(--font);}
body{background:var(--bg);margin:0;color:var(--text);-webkit-font-smoothing:antialiased;}
.dash-wrap{display:flex;min-height:100vh;}
.dash-wrap .main{margin-left:var(--sb-w);flex:1;min-width:0;}

/* ── SIDEBAR ── */
.sidebar{background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);overflow-y:scroll;scrollbar-width:thin;scrollbar-color:rgba(99,102,241,.35) transparent;z-index:40;border-right:1px solid rgba(99,102,241,.15);box-shadow:6px 0 30px rgba(0,0,0,.35);}
.sidebar::-webkit-scrollbar{width:4px;}
.sidebar::-webkit-scrollbar-track{background:rgba(255,255,255,.04);}
.sidebar::-webkit-scrollbar-thumb{background:rgba(99,102,241,.4);border-radius:4px;}
.sidebar::-webkit-scrollbar-thumb:hover{background:rgba(99,102,241,.7);}
.sb-brand{padding:18px 16px 14px;border-bottom:1px solid var(--sb-border);flex-shrink:0;position:relative;}
.sb-close{display:none;position:absolute;top:14px;right:12px;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.6);font-size:18px;line-height:1;cursor:pointer;align-items:center;justify-content:center;transition:background .15s,color .15s;flex-shrink:0;}
.sb-close:hover{background:rgba(239,68,68,.18);border-color:rgba(239,68,68,.3);color:#fca5a5;}
@media(max-width:900px){.sb-close{display:flex;}}
.sb-logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:#fff;}
.sb-logo-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;}
.sb-shop-name{font-size:14.5px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:148px;letter-spacing:-.3px;color:#fff;}
.sb-status{display:flex;align-items:center;gap:6px;margin-top:9px;font-size:10.5px;color:var(--sb-txt);font-weight:500;}
.pulse{width:6px;height:6px;border-radius:50%;background:var(--brand);flex-shrink:0;animation:blink 2.2s ease-in-out infinite;box-shadow:0 0 5px var(--brand);}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.35}}
.sb-nav{padding:10px 10px 32px;flex:1;display:flex;flex-direction:column;gap:1px;overflow:visible;}
.sb-section{font-size:9.5px;text-transform:uppercase;letter-spacing:1.8px;color:rgba(255,255,255,.48);padding:16px 10px 5px;font-weight:800;}
.sb-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:rgba(255,255,255,.78);text-decoration:none;transition:background .15s,color .15s;position:relative;}
.sb-item:hover{background:var(--sb-hov);color:rgba(255,255,255,.96);}
.sb-item.active{background:var(--sb-act);color:#fff;box-shadow:0 2px 12px rgba(99,102,241,.25);}
.sb-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:20px;background:#a5b4fc;border-radius:0 3px 3px 0;box-shadow:2px 0 8px rgba(165,180,252,.5);}
.sb-item .ico{font-size:13px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);transition:background .15s;}
.sb-item:hover .ico{background:rgba(255,255,255,.09);}
.sb-item.active .ico{background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.2);}
.sb-badge{margin-left:auto;background:var(--brand);color:#fff;font-size:10px;font-weight:700;border-radius:20px;padding:1px 7px;font-family:var(--mono);min-width:20px;text-align:center;}
.sb-group{display:flex;flex-direction:column;}
.sb-group-toggle{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:rgba(255,255,255,.78);cursor:pointer;transition:background .15s,color .15s;user-select:none;border:none;background:none;width:100%;text-align:left;font-family:var(--font);}
.sb-group-toggle:hover{background:var(--sb-hov);color:rgba(255,255,255,.96);}
.sb-group-toggle.open{color:#fff;background:rgba(255,255,255,.05);}
.sb-group-toggle .ico{font-size:13px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);}
.sb-group-toggle .sb-arrow{margin-left:auto;font-size:10px;color:rgba(255,255,255,.32);transition:transform .2s;flex-shrink:0;}
.sb-group-toggle.open .sb-arrow{transform:rotate(90deg);color:rgba(255,255,255,.6);}
.sb-sub{display:none;flex-direction:column;gap:1px;margin-left:12px;padding-left:14px;border-left:1px solid rgba(255,255,255,.1);margin-top:2px;margin-bottom:4px;}
.sb-sub.open{display:flex;}
.sb-sub .sb-item{font-size:13px;font-weight:500;padding:6px 10px;color:rgba(255,255,255,.62);}
.sb-sub .sb-item:hover{color:rgba(255,255,255,.92);}
.sb-sub .sb-item.active{color:#fff;background:var(--sb-act);font-weight:600;}
.sb-scroll-hint{position:sticky;bottom:72px;width:100%;height:40px;background:linear-gradient(to bottom,transparent,rgba(17,17,24,.95));pointer-events:none;z-index:2;display:flex;align-items:flex-end;justify-content:center;padding-bottom:6px;transition:opacity .3s;margin-top:-40px;align-self:flex-end;}
.sb-scroll-hint.hidden{opacity:0;}
.sb-scroll-hint-arrow{display:flex;flex-direction:column;align-items:center;gap:2px;animation:bounceDown 1.5s ease-in-out infinite;}
.sb-scroll-hint-dot{width:4px;height:4px;border-radius:50%;background:rgba(99,102,241,.6);}
.sb-scroll-hint-dot:nth-child(2){opacity:.5;margin-top:-2px;}
.sb-scroll-hint-dot:nth-child(3){opacity:.25;margin-top:-2px;}
@keyframes bounceDown{0%,100%{transform:translateY(0)}50%{transform:translateY(4px)}}
.sb-footer{padding:12px 10px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0;display:flex;flex-direction:column;gap:6px;position:sticky;bottom:0;background:linear-gradient(180deg,transparent 0%,#0b0b12 25%);z-index:1;}
.sb-user{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);text-decoration:none;transition:background .15s;border:1px solid transparent;}
.sb-user:hover{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.07);}
.sb-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4338ca);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;box-shadow:0 0 0 2px rgba(99,102,241,.45),0 2px 8px rgba(99,102,241,.3);letter-spacing:-.5px;}
.sb-uname{font-size:13px;font-weight:700;color:#fff;letter-spacing:-.2px;}
.sb-urole{font-size:10.5px;color:rgba(255,255,255,.52);margin-top:1px;font-weight:500;}
.sb-logout{display:flex;align-items:center;gap:8px;width:100%;padding:8px 10px;border-radius:var(--r-sm);background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.15);color:rgba(252,165,165,.92);font-size:12.5px;font-weight:600;font-family:var(--font);cursor:pointer;text-decoration:none;transition:background .15s,color .15s,border-color .15s;text-align:left;}
.sb-logout:hover{background:rgba(220,38,38,.18);border-color:rgba(220,38,38,.35);color:#fca5a5;}
.sb-logout .ico{font-size:13px;flex-shrink:0;}
.sb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:39;}

/* ── MAIN ── */
.main{display:flex;flex-direction:column;min-width:0;}
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 22px;height:var(--top-h);display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:30;box-shadow:var(--shadow-sm);}
.btn-hamburger{display:none;background:none;border:none;cursor:pointer;padding:6px;color:var(--text);font-size:20px;}
.tb-info{flex:1;min-width:0;}
.tb-title{font-size:14px;font-weight:700;color:var(--text);}
.tb-sub{font-size:11px;color:var(--muted);margin-top:1px;}
.content{padding:20px 22px;flex:1;}

/* ── CONTENU SUPPORT ── */
.flash{padding:10px 14px;border-radius:var(--r-sm);border:1px solid;font-size:13.5px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.flash-success{background:#eef2ff;border-color:#a5b4fc;color:#3730a3;}
.stats-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;}
.stat-chip{background:var(--surface);border:1px solid var(--border);border-radius:var(--r-sm);padding:10px 16px;display:flex;align-items:center;gap:8px;box-shadow:var(--shadow-sm);font-size:12.5px;font-weight:600;color:var(--text-2);flex:1;min-width:130px;}
.stat-chip .val{font-family:var(--mono);font-size:16px;font-weight:700;color:var(--text);}
.stat-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.page-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap;}
.btn-new{display:inline-flex;align-items:center;gap:7px;padding:9px 20px;border-radius:var(--r-sm);background:var(--brand);color:#fff;font-size:13.5px;font-weight:700;text-decoration:none;border:none;cursor:pointer;font-family:var(--font);transition:background .14s;white-space:nowrap;}
.btn-new:hover{background:var(--brand-dk);}

/* Tickets desktop */
.tickets-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--shadow-sm);}
.tk-row{display:flex;align-items:center;gap:14px;padding:15px 18px;border-bottom:1px solid var(--border);text-decoration:none;color:var(--text);transition:background .14s;}
.tk-row:last-child{border-bottom:none;}
.tk-row:hover{background:#f8fafc;}
.tk-ico{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.tk-ico.open{background:rgba(99,102,241,.1);}
.tk-ico.closed{background:#f1f5f9;}
.tk-body{flex:1;min-width:0;}
.tk-subject{font-size:14px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.tk-meta{font-size:12px;color:var(--muted);margin-top:3px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.tk-right{display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0;}
.badge-open{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(16,185,129,.1);color:#047857;}
.badge-closed{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#f1f5f9;color:var(--muted);}
.badge-dot{width:5px;height:5px;border-radius:50%;}
.badge-open .badge-dot{background:#10b981;}
.badge-closed .badge-dot{background:var(--muted);}
.tk-date{font-size:11px;color:var(--muted);}
.tk-msgs{font-size:11px;color:var(--muted);font-family:var(--mono);}

/* Mobile card list */
.mobile-list{display:none;flex-direction:column;gap:10px;}
.m-card{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--shadow-sm);text-decoration:none;color:var(--text);display:block;transition:border-color .15s;}
.m-card:hover{border-color:var(--brand);}
.m-card-hd{padding:12px 14px;background:var(--bg);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:8px;}
.m-card-body{padding:12px 14px;display:flex;flex-direction:column;gap:8px;}
.m-lbl{font-size:10px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;}

/* Empty state */
.empty-state{padding:56px 20px;text-align:center;}
.empty-state .ico{font-size:44px;display:block;margin-bottom:14px;}
.empty-state p{font-size:14px;color:var(--muted);}

/* ── Responsive ── */
@media(max-width:1200px) and (min-width:901px){
    .stat-chip{min-width:100px;}
}
@media(max-width:900px){
    :root{--sb-w:230px;}
    .dash-wrap .main{margin-left:0;}
    .sidebar{transform:translateX(-100%);transition:transform .25s cubic-bezier(.23,1,.32,1);}
    .sidebar.open{transform:translateX(0);}
    .sb-overlay.open{display:block;}
    .btn-hamburger{display:flex;}
    .content{padding:14px;}
}
@media(max-width:640px){
    .tickets-card{display:none !important;}
    .mobile-list{display:flex !important;}
    .content{padding:10px;}
    .stats-row{gap:6px;}
    .stat-chip{min-width:calc(50% - 6px);flex:1 1 calc(50% - 6px);padding:8px 10px;font-size:11px;}
    .stat-chip .val{font-size:14px;}
    .topbar{padding:0 12px;}
    .page-hd{gap:8px;}
}
@media(max-width:480px){
    .content{padding:8px;}
    .btn-new{padding:8px 14px;font-size:13px;}
}
*{scrollbar-width:thin;scrollbar-color:rgba(99,102,241,.2) transparent;}
</style>
@endpush

@section('content')
@php
    $u        = Auth::user();
    $parts    = explode(' ', $u->name ?? 'U X');
    $initials = strtoupper(substr($parts[0],0,1)).strtoupper(substr($parts[1] ?? 'X',0,1));
    $isSA     = $u->role === 'superadmin';
    $openCount   = $tickets->getCollection()->where('status','open')->count();
    $closedCount = $tickets->getCollection()->where('status','closed')->count();
@endphp

<div class="dash-wrap">

{{-- SIDEBAR --}}
@if(!$isSA && $shop)
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
            <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
            <span class="sb-shop-name">{{ $shop->name ?? 'Boutique' }}</span>
        </a>
        <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer le menu">✕</button>
        <div class="sb-status">
            <span class="pulse"></span>
            {{ ($shop->is_approved ?? true) ? 'Boutique active' : 'En attente' }}
            &nbsp;·&nbsp;
            {{ ucfirst($u->role_in_shop ?? $u->role) }}
        </div>
    </div>
    <div class="sb-scroll-hint" id="sbScrollHint">
        <div class="sb-scroll-hint-arrow">
            <div class="sb-scroll-hint-dot"></div><div class="sb-scroll-hint-dot"></div><div class="sb-scroll-hint-dot"></div>
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px"><span class="ico">⊞</span> Tableau de bord</a>
        <div class="sb-section">Boutique</div>
        <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">💬</span> Messages</a>
        <a href="{{ route('boutique.orders.index') }}" class="sb-item"><span class="ico">📦</span> Commandes</a>
        <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">🏷️</span> Produits</a>
        <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">👥</span> Clients</a>
        <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">🧑‍💼</span> Équipe</a>
        <div class="sb-section">Livraison</div>
        <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">🚴</span> Livreurs</a>
        <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">🏢</span> Partenaires</a>
        <div class="sb-section">Finances</div>
        <div class="sb-group">
            <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                <span class="ico">💰</span> Finances &amp; Rapports <span class="sb-arrow">▶</span>
            </button>
            <div class="sb-sub">
                <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">💳</span> Paiements</a>
                <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">📊</span> Commissions</a>
                <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">📋</span> Rapports</a>
                @if($u->role === 'admin')
                <a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">⚙️</span> Paramètres</a>
                @endif
            </div>
        </div>
        <div class="sb-section">Aide</div>
        <a href="{{ route('support.index') }}" class="sb-item active"><span class="ico">🎧</span> Support</a>
    </nav>
    <div class="sb-footer">
        <a href="{{ route('profile.edit') }}" class="sb-user">
            <div class="sb-av">{{ $initials }}</div>
            <div style="flex:1;min-width:0">
                <div class="sb-uname">{{ Str::limit($u->name, 20) }}</div>
                <div class="sb-urole">{{ $u->role === 'admin' ? 'Administrateur' : ucfirst($u->role) }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-logout"><span class="ico">⎋</span> Se déconnecter</button>
        </form>
    </div>
</aside>
<div class="sb-overlay" id="sbOverlay"></div>
@endif

{{-- MAIN --}}
<main class="main">
    <div class="topbar">
        @if(!$isSA && $shop)<button class="btn-hamburger" id="btnMenu">☰</button>@endif
        <div class="tb-info">
            <div class="tb-title">🎧 Support</div>
            <div class="tb-sub">
                @if($isSA) Tous les tickets plateforme @else {{ $shop->name ?? 'Boutique' }} · Contactez le SuperAdmin @endif
            </div>
        </div>
        @if(!$isSA)
        <a href="{{ route('support.create') }}" class="btn-new">✚ Nouveau ticket</a>
        @endif
    </div>

    <div class="content">

        @if(session('success'))
        <div class="flash flash-success">✅ {{ session('success') }}</div>
        @endif

        {{-- Stats --}}
        <div class="stats-row">
            <div class="stat-chip">
                <span class="stat-dot" style="background:#6366f1"></span>
                <div><div class="val">{{ $tickets->total() }}</div><div>Total tickets</div></div>
            </div>
            <div class="stat-chip">
                <span class="stat-dot" style="background:#10b981"></span>
                <div><div class="val">{{ $openCount }}</div><div>Ouverts</div></div>
            </div>
            <div class="stat-chip">
                <span class="stat-dot" style="background:var(--muted)"></span>
                <div><div class="val">{{ $closedCount }}</div><div>Fermés</div></div>
            </div>
        </div>

        @if($tickets->isEmpty())
        <div class="empty-state">
            <span class="ico">🎧</span>
            <p style="font-size:16px;font-weight:800;color:var(--text);margin-bottom:6px">Aucun ticket de support</p>
            <p>@if($isSA) Aucune boutique n'a ouvert de ticket pour l'instant. @else Créez un ticket si vous avez un problème ou une question. @endif</p>
            @if(!$isSA)<a href="{{ route('support.create') }}" class="btn-new" style="margin:0 auto;display:inline-flex">✚ Ouvrir un ticket</a>@endif
        </div>
        @else

        {{-- Desktop list --}}
        <div class="tickets-card">
            @foreach($tickets as $t)
            <a href="{{ route('support.show', $t) }}" class="tk-row">
                <div class="tk-ico {{ $t->status === 'open' ? 'open' : 'closed' }}">{{ $t->status === 'open' ? '🎧' : '✅' }}</div>
                <div class="tk-body">
                    <div class="tk-subject">{{ $t->subject }}</div>
                    <div class="tk-meta">
                        @if($isSA && $t->shop)<span>🏪 {{ $t->shop->name }}</span> ·@endif
                        <span>{{ $t->creator->name ?? '—' }}</span>
                        <span class="tk-msgs">· {{ $t->messages_count }} msg</span>
                    </div>
                </div>
                <div class="tk-right">
                    <span class="badge-{{ $t->status }}"><span class="badge-dot"></span>{{ $t->status === 'open' ? 'Ouvert' : 'Fermé' }}</span>
                    <span class="tk-date">{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y') }}</span>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Mobile card list --}}
        <div class="mobile-list">
            @foreach($tickets as $t)
            <a href="{{ route('support.show', $t) }}" class="m-card">
                <div class="m-card-hd">
                    <div style="font-size:13.5px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1">
                        {{ $t->status === 'open' ? '🎧' : '✅' }} {{ $t->subject }}
                    </div>
                    <span class="badge-{{ $t->status }}"><span class="badge-dot"></span>{{ $t->status === 'open' ? 'Ouvert' : 'Fermé' }}</span>
                </div>
                <div class="m-card-body">
                    @if($isSA && $t->shop)
                    <div><div class="m-lbl">Boutique</div><div style="font-size:13px;font-weight:600">🏪 {{ $t->shop->name }}</div></div>
                    @endif
                    <div style="display:flex;gap:16px">
                        <div><div class="m-lbl">Ouvert par</div><div style="font-size:12.5px">{{ $t->creator->name ?? '—' }}</div></div>
                        <div><div class="m-lbl">Messages</div><div style="font-size:12.5px;font-family:var(--mono)">{{ $t->messages_count }}</div></div>
                        <div><div class="m-lbl">Date</div><div style="font-size:12.5px">{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y') }}</div></div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        @if($tickets->hasPages())
        <div style="display:flex;justify-content:center;padding:16px 0 4px">{{ $tickets->links() }}</div>
        @endif
        @endif

    </div>
</main>
</div>
@endsection

@push('scripts')
<script>
function toggleGroup(btn) {
    const sub = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');
    document.querySelectorAll('.sb-sub.open').forEach(s => { s.classList.remove('open'); s.previousElementSibling?.classList.remove('open'); });
    if (!isOpen) { sub.classList.add('open'); btn.classList.add('open'); }
}
document.addEventListener('DOMContentLoaded', () => {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sbOverlay');
    const hint     = document.getElementById('sbScrollHint');
    function openSb()  { sidebar?.classList.add('open');    overlay?.classList.add('open'); }
    function closeSb() { sidebar?.classList.remove('open'); overlay?.classList.remove('open'); }
    document.getElementById('btnMenu')?.addEventListener('click', openSb);
    overlay?.addEventListener('click', closeSb);
    document.getElementById('btnCloseSidebar')?.addEventListener('click', closeSb);
    function updateHint() {
        if (!sidebar || !hint) return;
        const atBottom = sidebar.scrollTop + sidebar.clientHeight >= sidebar.scrollHeight - 16;
        hint.classList.toggle('hidden', atBottom || sidebar.scrollHeight <= sidebar.clientHeight + 20);
    }
    sidebar?.addEventListener('scroll', updateHint);
    window.addEventListener('resize', updateHint);
    setTimeout(updateHint, 300);
});
</script>
@endpush
