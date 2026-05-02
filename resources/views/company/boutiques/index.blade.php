@extends('layouts.app')
@section('title', 'Boutiques partenaires · ' . $company->name)
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}

:root{
    --cx-bg:      #0b0d22;
    --cx-surface: #0d1226;
    --cx-surface2:#111930;
    --cx-border:  rgba(255,255,255,.07);
    --cx-brand:   #7c3aed;
    --cx-brand2:  #6d28d9;
    --cx-text:    #e2e8f0;
    --cx-text2:   #94a3b8;
    --cx-muted:   #475569;
    --cx-green:   #10b981;
    --cx-amber:   #f59e0b;
    --cx-red:     #ef4444;
    --cx-blue:    #3b82f6;
    --r:    12px;
    --r-sm: 9px;
    --r-xs: 6px;
    --sb-w: 220px;
    --top-h:60px;
}
body.cx-light{
    --cx-bg:      #F5F7FA;
    --cx-surface: #ffffff;
    --cx-surface2:#eef1f7;
    --cx-border:  rgba(0,0,0,.08);
    --cx-text:    #111827;
    --cx-text2:   #4b5563;
    --cx-muted:   #9ca3af;
}

html,body{margin:0;font-family:'Segoe UI',system-ui,sans-serif;background:var(--cx-bg);color:var(--cx-text);-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}

body.is-dashboard>nav,
body.is-dashboard>header,
body.is-dashboard .navbar{display:none!important;}
body.is-dashboard>main.app-main{padding:0!important;margin:0!important;max-width:100%!important;width:100%!important;}

/* ══ STRUCTURE ══ */
.cx-wrap{display:flex;min-height:100vh;background:var(--cx-bg);padding-left:var(--sb-w);}
.cx-main{flex:1;min-width:0;display:flex;flex-direction:column;background:var(--cx-bg);}

/* ══ SIDEBAR ══ */
.cx-sidebar{
    position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);
    background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    display:flex;flex-direction:column;z-index:1200;overflow-y:auto;
    scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.3) transparent;
    transition:transform .25s cubic-bezier(.23,1,.32,1);
    border-right:1px solid rgba(99,102,241,.1);
    box-shadow:6px 0 30px rgba(0,0,0,.35);
}
.cx-sidebar::-webkit-scrollbar{width:3px;}
.cx-sidebar::-webkit-scrollbar-thumb{background:rgba(124,58,237,.3);border-radius:3px;}

/* Brand */
.cx-brand-hd{padding:14px 14px 10px;border-bottom:1px solid rgba(255,255,255,.06);flex-shrink:0;display:flex;flex-direction:column;gap:8px;}
.cx-brand-top{display:flex;align-items:center;justify-content:space-between;}
.cx-logo{display:flex;align-items:center;gap:9px;color:#fff;font-size:16px;font-weight:800;}
.cx-logo-icon{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#7c3aed,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.cx-sys-badge{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:600;color:var(--cx-green);padding:3px 8px;border-radius:20px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);}
.cx-sys-dot{width:6px;height:6px;border-radius:50%;background:var(--cx-green);animation:blink 2.2s ease-in-out infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}
.cx-close-btn{display:none;background:none;border:none;color:rgba(255,255,255,.45);font-size:18px;cursor:pointer;padding:2px 6px;border-radius:6px;line-height:1}
.cx-close-btn:hover{color:#fff}

/* Nav */
.cx-nav{padding:8px 8px 12px;flex:1}
.cx-nav-sec{font-size:10px;font-weight:800;letter-spacing:1.6px;color:rgba(255,255,255,.58);padding:14px 10px 5px;text-transform:uppercase}
.cx-nav-item{display:flex;align-items:center;gap:10px;padding:8px 11px;border-radius:var(--r-xs);color:rgba(255,255,255,.85);font-size:13.5px;font-weight:600;transition:all .22s cubic-bezier(.23,1,.32,1);position:relative;cursor:pointer;margin-bottom:2px;border:1px solid transparent}
.cx-nav-item:hover{background:rgba(124,58,237,.18);color:#fff;border-color:rgba(124,58,237,.25);box-shadow:0 2px 12px rgba(124,58,237,.2),inset 0 1px 0 rgba(255,255,255,.06)}
.cx-nav-item:hover .cx-nav-ico{background:rgba(139,92,246,.25);box-shadow:0 0 8px rgba(139,92,246,.3)}
.cx-nav-item.active{background:linear-gradient(90deg,rgba(124,58,237,.35) 0%,rgba(99,102,241,.2) 100%);color:#fff;font-weight:700;border-color:rgba(139,92,246,.3);box-shadow:0 4px 16px rgba(124,58,237,.25),inset 0 1px 0 rgba(255,255,255,.08)}
.cx-nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:22px;background:linear-gradient(180deg,#a78bfa,#7c3aed);border-radius:0 3px 3px 0;box-shadow:2px 0 12px rgba(167,139,250,.7)}
.cx-nav-ico{width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;transition:all .22s cubic-bezier(.23,1,.32,1)}
.cx-nav-item.active .cx-nav-ico{background:rgba(139,92,246,.3);border-color:rgba(139,92,246,.4);box-shadow:0 0 10px rgba(139,92,246,.4)}
.cx-nav-item.active:hover{background:linear-gradient(90deg,rgba(124,58,237,.45) 0%,rgba(99,102,241,.3) 100%);box-shadow:0 4px 20px rgba(124,58,237,.35),inset 0 1px 0 rgba(255,255,255,.1)}
.cx-nav-badge{margin-left:auto;background:var(--cx-brand);color:#fff;font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:20px;min-width:18px;text-align:center}

/* User foot */
.cx-user-foot{padding:10px 10px 12px;border-top:1px solid rgba(255,255,255,.07);flex-shrink:0}
.cx-user-row{display:flex;align-items:center;gap:9px;padding:7px 8px;border-radius:var(--r-xs);background:rgba(255,255,255,.04);cursor:pointer;transition:background .15s;margin-bottom:6px}
.cx-user-row:hover{background:rgba(255,255,255,.08)}
.cx-user-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4338ca);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;}
.cx-user-name{font-size:12px;font-weight:700;color:#fff;line-height:1.2}
.cx-user-role{font-size:10px;color:var(--cx-text2)}
.cx-logout-btn{width:30px;height:30px;border-radius:8px;flex-shrink:0;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);display:flex;align-items:center;justify-content:center;color:#f87171;cursor:pointer;transition:background .15s;padding:0;}
.cx-logout-btn:hover{background:rgba(239,68,68,.22);color:#fff}

/* ══ TOPBAR ══ */
.cx-topbar{height:var(--top-h);background:var(--cx-surface);border-bottom:1px solid var(--cx-border);display:flex;align-items:center;gap:12px;padding:0 24px;position:sticky;top:0;z-index:1050;flex-shrink:0;}
.cx-hamburger{display:none;background:none;border:none;color:var(--cx-text2);font-size:20px;cursor:pointer;padding:4px;line-height:1;}
.cx-topbar-title{font-size:15px;font-weight:800;color:var(--cx-text);}
.cx-topbar-sub{font-size:12px;color:var(--cx-muted);}
.cx-tb-right{margin-left:auto;display:flex;align-items:center;gap:8px;}
.cx-tb-user{display:flex;align-items:center;gap:8px;padding:5px 10px;border-radius:var(--r-sm);transition:background .14s;flex-shrink:0;}
.cx-tb-user:hover{background:rgba(255,255,255,.06);}
.cx-tb-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4338ca);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;}
.cx-tb-uname{font-size:12.5px;font-weight:700;color:var(--cx-text);line-height:1.2;}
.cx-tb-urole{font-size:10px;color:var(--cx-text2);}
body.cx-light .cx-topbar{background:#fff;border-bottom-color:rgba(0,0,0,.07);box-shadow:0 1px 4px rgba(0,0,0,.06);}

/* ══ PAGE ══ */
.cx-content{padding:24px;flex:1;}

/* Stats bar */
.stats-bar{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;}
.stat-card{background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r);padding:18px 20px;display:flex;align-items:center;gap:14px;transition:box-shadow .2s;}
.stat-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.15);}
.stat-ico{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.stat-ico.purple{background:rgba(124,58,237,.15);border:1px solid rgba(124,58,237,.2);}
.stat-ico.green{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.2);}
.stat-ico.amber{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.2);}
.stat-val{font-size:22px;font-weight:800;color:var(--cx-text);line-height:1;}
.stat-lbl{font-size:11.5px;color:var(--cx-text2);margin-top:3px;}

/* Toolbar */
.toolbar{display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap;}
.toolbar-title{font-size:17px;font-weight:800;color:var(--cx-text);flex:1;min-width:140px;}
.search-box{display:flex;align-items:center;gap:8px;background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r-sm);padding:0 12px;height:38px;min-width:220px;transition:border-color .15s;}
.search-box:focus-within{border-color:rgba(124,58,237,.5);}
.search-box input{border:none;background:none;color:var(--cx-text);font-size:13px;outline:none;flex:1;min-width:0;}
.search-box input::placeholder{color:var(--cx-muted);}
.btn-search{background:none;border:none;color:var(--cx-muted);cursor:pointer;font-size:14px;padding:0;}

/* Grid boutiques */
.shops-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;}

/* Shop card */
.shop-card{background:var(--cx-surface);border:1px solid var(--cx-border);border-radius:var(--r);overflow:hidden;transition:transform .2s,box-shadow .2s;display:flex;flex-direction:column;}
.shop-card:hover{transform:translateY(-3px);box-shadow:0 8px 30px rgba(0,0,0,.2),0 0 0 1px rgba(124,58,237,.15);}
.shop-banner{height:80px;background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#1e1b4b 100%);position:relative;overflow:hidden;flex-shrink:0;}
.shop-banner-img{width:100%;height:100%;object-fit:cover;}
.shop-banner-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,rgba(0,0,0,.5));}
.shop-avatar{position:absolute;bottom:-18px;left:16px;width:48px;height:48px;border-radius:10px;border:2px solid var(--cx-surface);background:var(--cx-surface2);display:flex;align-items:center;justify-content:center;font-size:20px;overflow:hidden;flex-shrink:0;}
.shop-avatar img{width:100%;height:100%;object-fit:cover;}
.shop-body{padding:26px 16px 16px;flex:1;display:flex;flex-direction:column;}
.shop-name{font-size:14.5px;font-weight:800;color:var(--cx-text);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.shop-meta{display:flex;align-items:center;gap:6px;margin-bottom:10px;flex-wrap:wrap;}
.shop-type{font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.2);color:#a78bfa;}
.shop-country{font-size:10.5px;color:var(--cx-muted);}
.shop-addr{font-size:11.5px;color:var(--cx-text2);margin-bottom:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.shop-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:12px;}
.shop-stat{background:var(--cx-surface2);border-radius:var(--r-xs);padding:7px 6px;text-align:center;}
.shop-stat-val{font-size:15px;font-weight:800;color:var(--cx-text);line-height:1;}
.shop-stat-lbl{font-size:9.5px;color:var(--cx-muted);margin-top:2px;}
.shop-stat.green .shop-stat-val{color:#34d399;}
.shop-stat.red .shop-stat-val{color:#f87171;}
.shop-revenue{display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.15);border-radius:var(--r-xs);margin-bottom:12px;}
.shop-revenue-lbl{font-size:11px;color:var(--cx-text2);}
.shop-revenue-val{font-size:14px;font-weight:800;color:#a78bfa;}
.shop-actions{display:flex;gap:8px;margin-top:auto;}
.btn-shop{flex:1;height:34px;border-radius:var(--r-xs);border:1px solid var(--cx-border);background:var(--cx-surface2);color:var(--cx-text2);font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:5px;}
.btn-shop:hover{background:rgba(124,58,237,.15);border-color:rgba(124,58,237,.3);color:#a78bfa;}
.btn-shop.primary{background:rgba(124,58,237,.2);border-color:rgba(124,58,237,.35);color:#c4b5fd;}
.btn-shop.primary:hover{background:rgba(124,58,237,.35);color:#fff;}

/* Empty state */
.empty-state{text-align:center;padding:60px 20px;color:var(--cx-text2);}
.empty-ico{font-size:48px;margin-bottom:12px;}
.empty-title{font-size:16px;font-weight:700;color:var(--cx-text);margin-bottom:6px;}
.empty-sub{font-size:13px;}

/* Pagination */
.pagination-wrap{margin-top:28px;display:flex;justify-content:center;}

/* Light mode */
body.cx-light .shop-card{background:#fff;border-color:rgba(0,0,0,.07);}
body.cx-light .shop-banner{background:linear-gradient(135deg,#e0e7ff 0%,#c7d2fe 100%);}
body.cx-light .shop-stat{background:#f5f7fa;}
body.cx-light .stat-card{background:#fff;}
body.cx-light .toolbar .search-box{background:#fff;}

/* ══ RESPONSIVE ══ */
@media(max-width:900px){
    .cx-sidebar{transform:translateX(-100%);}
    .cx-sidebar.open{transform:translateX(0);}
    .cx-wrap{padding-left:0;}
    .cx-hamburger{display:flex;}
}
@media(max-width:768px){
    .cx-content{padding:16px 14px 40px;}
    .stats-bar{grid-template-columns:1fr 1fr;}
    .cx-tb-uname,.cx-tb-urole{display:none;}
    .cx-topbar{padding:0 14px;}
}
@media(max-width:540px){
    .stats-bar{grid-template-columns:1fr;}
    .shops-grid{grid-template-columns:1fr;}
    .toolbar{flex-direction:column;align-items:stretch;}
    .search-box{min-width:0;}
}
.cx-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1100;}
.cx-overlay.open{display:block;}
</style>
@endpush

@section('content')
@php
    $u   = auth()->user();
    $ini = strtoupper(substr($u->name ?? 'A', 0, 1)) . strtoupper(substr(explode(' ', $u->name ?? 'A ')[1] ?? 'X', 0, 1));
@endphp

{{-- Sidebar --}}
<aside class="cx-sidebar" id="cxSidebar">
    <div class="cx-brand-hd">
        <div class="cx-brand-top">
            <a href="{{ route('company.dashboard') }}" class="cx-logo">
                <div class="cx-logo-icon">🚚</div>
                <span>{{ Str::limit($company->name, 14) }}</span>
            </a>
            <button class="cx-close-btn" id="cxClose">✕</button>
        </div>
        <div class="cx-sys-badge"><span class="cx-sys-dot"></span> Système actif</div>
    </div>

    <nav class="cx-nav">
        <div class="cx-nav-sec">Principal</div>
        <a href="{{ route('company.dashboard') }}" class="cx-nav-item">
            <span class="cx-nav-ico">⊞</span> Tableau de bord
        </a>
        <a href="{{ route('company.chat.inbox') }}" class="cx-nav-item">
            <span class="cx-nav-ico">💬</span> Demandes (Chat)
        </a>
        <a href="{{ route('company.orders.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📦</span> Commandes
        </a>
        <a href="{{ route('company.livraisons.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">🚚</span> Livraisons
        </a>
        <a href="{{ route('company.carte.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">🗺️</span> Carte en direct
        </a>
        <a href="{{ route('company.drivers.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">🚴</span> Chauffeurs
        </a>
        <a href="{{ route('company.boutiques.index') }}" class="cx-nav-item active">
            <span class="cx-nav-ico">🏪</span> Boutiques
        </a>
        <div class="cx-nav-sec">Gestion</div>
        <a href="{{ route('company.historique.index') }}" class="cx-nav-item">
            <span class="cx-nav-ico">📊</span> Historique
        </a>
        <div class="cx-nav-sec">Configuration</div>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">⚙️</span> Paramètres</a>
        <a href="#" class="cx-nav-item"><span class="cx-nav-ico">👤</span> Utilisateurs</a>
    </nav>

    <div class="cx-user-foot">
        <div class="cx-user-row">
            <div class="cx-user-av">{{ $ini }}</div>
            <div style="flex:1;min-width:0;">
                <div class="cx-user-name">{{ Str::limit($u->name ?? 'Admin', 18) }}</div>
                <div class="cx-user-role">{{ $company->name }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="cx-logout-btn" title="Déconnexion">↩</button>
            </form>
        </div>
    </div>
</aside>

<div class="cx-overlay" id="cxOverlay"></div>

{{-- Main --}}
<div class="cx-wrap">
<main class="cx-main">

    <div class="cx-topbar">
        <button class="cx-hamburger" id="cxHamburger">☰</button>
        <div>
            <div class="cx-topbar-title">🏪 Boutiques partenaires</div>
            <div class="cx-topbar-sub">{{ $company->name }}</div>
        </div>
        <div class="cx-tb-right">
            <div class="cx-tb-user">
                <div class="cx-tb-av">{{ $ini }}</div>
                <div>
                    <div class="cx-tb-uname">{{ Str::limit($u->name ?? 'Admin', 16) }}</div>
                    <div class="cx-tb-urole">{{ $company->name }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="cx-content">

        {{-- Stats --}}
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-ico purple">🏪</div>
                <div>
                    <div class="stat-val">{{ $stats['total_boutiques'] }}</div>
                    <div class="stat-lbl">Boutiques partenaires</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-ico green">✅</div>
                <div>
                    <div class="stat-val">{{ number_format($stats['total_livrees']) }}</div>
                    <div class="stat-lbl">Livraisons réussies</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-ico amber">💰</div>
                <div>
                    <div class="stat-val">{{ number_format($stats['total_revenus'], 0, ',', ' ') }} GNF</div>
                    <div class="stat-lbl">Revenus de livraison</div>
                </div>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="toolbar">
            <div class="toolbar-title">
                {{ $shops->total() }} boutique{{ $shops->total() > 1 ? 's' : '' }} trouvée{{ $shops->total() > 1 ? 's' : '' }}
            </div>
            <form method="GET" action="{{ route('company.boutiques.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;">
                <div class="search-box">
                    <button class="btn-search" type="submit">🔍</button>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher une boutique…">
                    @if($search)
                        <a href="{{ route('company.boutiques.index') }}" style="color:var(--cx-muted);font-size:14px;line-height:1;">✕</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Grid --}}
        @if($shops->count())
        <div class="shops-grid">
            @foreach($shops as $shop)
            <div class="shop-card">
                {{-- Banner --}}
                <div class="shop-banner">
                    @if($shop->image)
                        <img src="{{ asset('storage/' . $shop->image) }}" alt="" class="shop-banner-img">
                    @endif
                    <div class="shop-banner-overlay"></div>
                    <div class="shop-avatar">
                        @if($shop->image)
                            <img src="{{ asset('storage/' . $shop->image) }}" alt="">
                        @else
                            🏪
                        @endif
                    </div>
                </div>

                {{-- Body --}}
                <div class="shop-body">
                    <div class="shop-name" title="{{ $shop->name }}">{{ $shop->name }}</div>
                    <div class="shop-meta">
                        @if($shop->type)
                            <span class="shop-type">{{ $shop->type }}</span>
                        @endif
                        @if($shop->country)
                            <span class="shop-country">🌍 {{ $shop->country }}</span>
                        @endif
                    </div>
                    @if($shop->address)
                        <div class="shop-addr">📍 {{ $shop->address }}</div>
                    @endif

                    {{-- Stats commandes --}}
                    <div class="shop-stats">
                        <div class="shop-stat">
                            <div class="shop-stat-val">{{ $shop->total_orders }}</div>
                            <div class="shop-stat-lbl">Commandes</div>
                        </div>
                        <div class="shop-stat green">
                            <div class="shop-stat-val">{{ $shop->livrees }}</div>
                            <div class="shop-stat-lbl">Livrées</div>
                        </div>
                        <div class="shop-stat red">
                            <div class="shop-stat-val">{{ $shop->annulees }}</div>
                            <div class="shop-stat-lbl">Annulées</div>
                        </div>
                    </div>

                    {{-- Revenus --}}
                    <div class="shop-revenue">
                        <span class="shop-revenue-lbl">💰 Revenus générés</span>
                        <span class="shop-revenue-val">{{ number_format($shop->revenus ?? 0, 0, ',', ' ') }} GNF</span>
                    </div>

                    {{-- Actions --}}
                    <div class="shop-actions">
                        <a href="{{ route('company.orders.index', ['boutique' => $shop->id]) }}" class="btn-shop primary">
                            📦 Commandes
                        </a>
                        @if($shop->en_cours > 0)
                            <a href="{{ route('company.livraisons.index') }}" class="btn-shop" style="position:relative;">
                                🚚 En cours
                                <span style="position:absolute;top:-5px;right:-5px;background:#7c3aed;color:#fff;font-size:9px;font-weight:800;padding:1px 5px;border-radius:10px;">{{ $shop->en_cours }}</span>
                            </a>
                        @else
                            <a href="{{ route('company.historique.index', ['shop_id' => $shop->id]) }}" class="btn-shop">📊 Historique</a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($shops->hasPages())
        <div class="pagination-wrap">
            {{ $shops->links() }}
        </div>
        @endif

        @else
        <div class="empty-state">
            <div class="empty-ico">🏪</div>
            <div class="empty-title">Aucune boutique partenaire</div>
            <div class="empty-sub">
                @if($search)
                    Aucun résultat pour "{{ $search }}".
                    <a href="{{ route('company.boutiques.index') }}" style="color:#a78bfa;">Voir toutes les boutiques</a>
                @else
                    Les boutiques apparaîtront ici dès qu'elles auront passé des commandes via votre service.
                @endif
            </div>
        </div>
        @endif

    </div>
</main>
</div>

<script>
const ham = document.getElementById('cxHamburger');
const sb  = document.getElementById('cxSidebar');
const ov  = document.getElementById('cxOverlay');
const cl  = document.getElementById('cxClose');
function openSb()  { sb.classList.add('open'); ov.classList.add('open'); if(cl) cl.style.display='flex'; }
function closeSb() { sb.classList.remove('open'); ov.classList.remove('open'); }
ham?.addEventListener('click', openSb);
ov?.addEventListener('click', closeSb);
cl?.addEventListener('click', closeSb);

// Dark/light mode
const saved = localStorage.getItem('cx-theme');
if (saved) document.body.classList.add('cx-' + saved);
</script>
@endsection
