{{--
    resources/views/admin/reports/index.blade.php
--}}

@extends('layouts.app')

@section('title', 'Rapports · ' . ($shop->name ?? 'Boutique'))

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --brand:      #10b981; --brand-dk:  #059669;
    --brand-lt:   #d1fae5; --brand-mlt: #ecfdf5;
    --sb-bg:      #0d1f18; --sb-border: rgba(255,255,255,.06);
    --sb-act:     rgba(16,185,129,.14);
    --sb-hov:     rgba(255,255,255,.04);
    --sb-txt:     rgba(255,255,255,.55);
    --sb-txt-act: #fff;
    --bg:         #f6f8f7; --surface:   #ffffff;
    --border:     #e8eceb; --border-dk: #d4d9d7;
    --text:       #0f1c18; --text-2:    #4b5c56; --muted: #8a9e98;
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r: 14px; --r-sm: 9px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.06);
    --shadow:     0 4px 16px rgba(0,0,0,.07);
    --sb-w:       230px;
    --top-h:      56px;
}
html { font-family: var(--font); }
body { background: var(--bg); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

.dash-wrap { display: flex; min-height: 100vh; }
.dash-wrap .main { margin-left: var(--sb-w); flex: 1; min-width: 0; }

/* SIDEBAR */
.sidebar { background: var(--sb-bg); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; width: var(--sb-w); overflow-y: scroll; scrollbar-width: thin; scrollbar-color: rgba(16,185,129,.4) rgba(255,255,255,.05); z-index: 40; border-right: 1px solid rgba(0,0,0,.2); }
.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,.04); }
.sidebar::-webkit-scrollbar-thumb { background: rgba(16,185,129,.4); border-radius: 4px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(16,185,129,.7); }
.sb-brand { padding: 18px 16px 14px; border-bottom: 1px solid var(--sb-border); flex-shrink: 0; position: relative; }
.sb-close { display: none; position: absolute; top: 14px; right: 12px; width: 30px; height: 30px; border-radius: 8px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.6); font-size: 18px; cursor: pointer; align-items: center; justify-content: center; transition: background .15s, color .15s; }
.sb-close:hover { background: rgba(239,68,68,.18); border-color: rgba(239,68,68,.3); color: #fca5a5; }
@media (max-width: 900px) { .sb-close { display: flex; } }
.sb-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.sb-logo-icon { width: 32px; height: 32px; background: linear-gradient(135deg, var(--brand), #059669); border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(16,185,129,.35); }
.sb-shop-name { font-size: 14px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 10.5px; color: var(--sb-txt); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: var(--brand); flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px var(--brand); }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }
.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; overflow: visible; }
.sb-section { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.2); padding: 12px 8px 4px; font-weight: 600; }
.sb-item { display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: var(--r-sm); font-size: 13px; font-weight: 500; color: var(--sb-txt); text-decoration: none; transition: background .15s, color .15s; position: relative; }
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.8); }
.sb-item.active { background: var(--sb-act); color: var(--sb-txt-act); }
.sb-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 18px; background: var(--brand); border-radius: 0 3px 3px 0; }
.sb-item .ico { font-size: 14px; width: 20px; text-align: center; flex-shrink: 0; }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); min-width: 20px; text-align: center; }
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: var(--r-sm); font-size: 13px; font-weight: 500; color: var(--sb-txt); cursor: pointer; transition: background .15s, color .15s; user-select: none; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); }
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.8); }
.sb-group-toggle.open { color: rgba(255,255,255,.9); background: rgba(255,255,255,.03); }
.sb-group-toggle .ico { font-size: 14px; width: 20px; text-align: center; flex-shrink: 0; }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.25); transition: transform .2s; flex-shrink: 0; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.5); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.07); margin-top: 2px; margin-bottom: 4px; overflow: visible; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 12.5px; padding: 6px 10px; color: rgba(255,255,255,.45); }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.75); }
.sb-sub .sb-item.active { color: var(--sb-txt-act); background: var(--sb-act); }
.sb-scroll-hint { position: sticky; bottom: 72px; width: 100%; height: 40px; background: linear-gradient(to bottom, transparent, rgba(13,31,24,.9)); pointer-events: none; z-index: 2; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 6px; transition: opacity .3s; margin-top: -40px; align-self: flex-end; }
.sb-scroll-hint.hidden { opacity: 0; }
.sb-scroll-hint-arrow { display: flex; flex-direction: column; align-items: center; gap: 2px; animation: bounceDown 1.5s ease-in-out infinite; }
.sb-scroll-hint-dot { width: 4px; height: 4px; border-radius: 50%; background: rgba(16,185,129,.6); }
.sb-scroll-hint-dot:nth-child(2) { opacity: .5; margin-top: -2px; }
.sb-scroll-hint-dot:nth-child(3) { opacity: .25; margin-top: -2px; }
@keyframes bounceDown { 0%,100%{transform:translateY(0)} 50%{transform:translateY(4px)} }
.sb-footer { padding: 12px 10px; border-top: 1px solid var(--sb-border); flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; position: sticky; bottom: 0; background: var(--sb-bg); z-index: 1; }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); text-decoration: none; transition: background .15s; }
.sb-user:hover { background: var(--sb-hov); }
.sb-av-user { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--brand), #16a34a); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(16,185,129,.25); }
.sb-uname { font-size: 12px; font-weight: 600; color: rgba(255,255,255,.85); }
.sb-urole { font-size: 10px; color: var(--sb-txt); margin-top: 1px; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.85); font-size: 12px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s, color .15s, border-color .15s; text-align: left; }
.sb-logout:hover { background: rgba(220,38,38,.18); border-color: rgba(220,38,38,.35); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; flex-shrink: 0; }
.sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 39; }

/* MAIN + TOPBAR */
.main { display: flex; flex-direction: column; min-width: 0; }
.topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 22px; height: var(--top-h); display: flex; align-items: center; gap: 12px; position: sticky; top: 0; z-index: 30; box-shadow: var(--shadow-sm); }
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); font-size: 20px; }
.tb-info { flex: 1; min-width: 0; }
.tb-title { font-size: 14px; font-weight: 700; color: var(--text); }
.tb-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }

.page-wrap { padding: 22px 22px 60px; }

/* Header */
.page-hd { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
.page-title { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -.4px; margin: 0 0 4px; }
.page-sub   { font-size: 13px; color: var(--muted); margin: 0; }
.devise-badge { display: inline-flex; align-items: center; gap: 5px; background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); font-size: 11px; font-weight: 700; font-family: var(--mono); padding: 4px 10px; border-radius: 20px; }
.super-badge  { display: inline-flex; align-items: center; gap: 5px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }

/* Section title */
.sec-title { font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; margin: 28px 0 12px; display: flex; align-items: center; gap: 8px; }
.sec-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }

/* KPI GRIDS */
.kpi-grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 14px; }
.kpi-grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 22px; }
.kpi-grid-2 { display: grid; grid-template-columns: repeat(2,1fr); gap: 14px; margin-bottom: 22px; }

.kpi { background: var(--surface); border: 1px solid var(--border); border-top: 3px solid var(--kc, var(--brand)); border-radius: var(--r); padding: 16px 18px; box-shadow: var(--shadow-sm); transition: box-shadow .2s; }
.kpi:hover { box-shadow: var(--shadow); }
.kpi-ico  { font-size: 20px; margin-bottom: 8px; }
.kpi-lbl  { font-size: 10.5px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 5px; }
.kpi-val  { font-size: 24px; font-weight: 800; font-family: var(--mono); color: var(--text); letter-spacing: -.8px; line-height: 1; cursor: help; }
.kpi-unit { font-size: 10px; color: var(--muted); margin-top: 4px; }
.kpi-delta { font-size: 11px; font-weight: 700; margin-top: 6px; display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 20px; }
.kpi-delta.up   { background: var(--brand-mlt); color: var(--brand-dk); }
.kpi-delta.down { background: #fef2f2; color: #991b1b; }
.kpi-delta.flat { background: #f3f6f4; color: var(--muted); }
.kpi-explain { font-size: 10.5px; color: var(--muted); margin-top: 5px; line-height: 1.5; }

/* ── Valeur exacte sous le chiffre abrégé (mobile + desktop) ── */
.kpi-full {
    font-size: 10px;  color: #16a34a; font-family: var(--mono);
    margin-top: 3px; font-weight: 700; display: block;
}

/* Graphique barres */
.chart-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 22px; }
.chart-card-hd { padding: 13px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--bg); }
.chart-card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.chart-body { padding: 18px 18px 12px; }
.chart-tabs { display: flex; gap: 6px; margin-bottom: 14px; }
.chart-tab { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid var(--border-dk); background: var(--bg); color: var(--text-2); cursor: pointer; transition: all .15s; }
.chart-tab.active { background: var(--brand); color: #fff; border-color: var(--brand-dk); }
.chart-bars-wrap { display: flex; align-items: flex-end; gap: 8px; height: 100px; margin-bottom: 8px; }
.chart-bar-col { flex: 1; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 3px; }
.chart-bar { width: 100%; border-radius: 4px 4px 0 0; background: var(--brand); opacity: .85; transition: height .6s cubic-bezier(.23,1,.32,1), opacity .15s; cursor: pointer; position: relative; min-height: 3px; }
.chart-bar.actuel { opacity: 1; }
.chart-bar:hover { opacity: 1; }
.chart-bar::after { content: attr(data-tip); position: absolute; bottom: calc(100% + 5px); left: 50%; transform: translateX(-50%); background: #0f1c18; color: #fff; font-size: 10px; font-weight: 600; font-family: var(--mono); padding: 3px 7px; border-radius: 4px; white-space: nowrap; pointer-events: none; opacity: 0; transition: opacity .15s; z-index: 10; }
.chart-bar:hover::after { opacity: 1; }
.chart-bar.orders-bar { background: #3b82f6; }
.chart-nb { font-size: 9px; color: var(--muted); font-family: var(--mono); }
.chart-labels { display: flex; gap: 8px; }
.chart-lbl { flex: 1; text-align: center; font-size: 10px; color: var(--muted); font-family: var(--mono); }
.chart-lbl.actuel { color: var(--brand); font-weight: 700; }

/* Top produits */
.top-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 22px; }
.sp-row { display: flex; align-items: center; gap: 12px; padding: 10px 18px; border-bottom: 1px solid #f3f6f4; }
.sp-row:last-child { border-bottom: none; }
.sp-rank { width: 22px; font-size: 11px; font-weight: 700; color: var(--muted); text-align: center; flex-shrink: 0; }
.sp-rank.top { color: #f59e0b; font-size: 14px; }
.sp-lbl  { flex: 1; font-size: 12.5px; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sp-track { width: 120px; height: 6px; background: #eef1f0; border-radius: 3px; overflow: hidden; flex-shrink: 0; }
.sp-fill  { height: 100%; border-radius: 3px; background: var(--brand); transition: width 1s cubic-bezier(.23,1,.32,1); }
.sp-val   { font-family: var(--mono); font-size: 12px; font-weight: 700; color: var(--text); width: 28px; text-align: right; flex-shrink: 0; }

/* Liens rapides */
.links-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; margin-bottom: 22px; }
.quick-link { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 16px 14px; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; transition: all .18s; box-shadow: var(--shadow-sm); }
.quick-link:hover { border-color: var(--brand); background: var(--brand-mlt); transform: translateY(-2px); }
.quick-link-ico { font-size: 24px; }
.quick-link-lbl { font-size: 12px; font-weight: 700; color: var(--text); }
.quick-link-sub { font-size: 10.5px; color: var(--muted); }

/* RESPONSIVE */
@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .kpi-grid-4 { grid-template-columns: repeat(2,1fr); }
    .kpi-grid-3 { grid-template-columns: repeat(2,1fr); }
    .links-grid { grid-template-columns: repeat(2,1fr); }
}
@media (max-width: 560px) {
    .kpi-grid-4, .kpi-grid-3, .kpi-grid-2 { grid-template-columns: 1fr 1fr; }
    .links-grid { grid-template-columns: repeat(2,1fr); }
    .page-wrap { padding: 14px 12px 40px; }
    .kpi-val { font-size: 20px; }
    .sp-track { width: 70px; }
    .chart-tabs { flex-wrap: wrap; gap: 4px; }
}
@media (max-width: 380px) {
    .kpi-grid-4, .kpi-grid-3, .kpi-grid-2 { grid-template-columns: 1fr; }
    .links-grid { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush

@section('content')

@php
    $devise   = $devise ?? ($shop->currency ?? 'GNF');
    $fmt = fn($n) => $n >= 1_000_000
        ? number_format($n/1_000_000, 2, ',', ' ').'M'
        : number_format($n, 0, ',', ' ');
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $pendingCount = $shop->orders()->whereIn('status',['pending','en attente','en_attente','confirmée','processing'])->count();
@endphp

<div class="dash-wrap">

{{-- SIDEBAR --}}
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
          <div class="sb-logo-icon"><img src="/images/Shopio2.jpeg" alt="Shopio" style="width:40px;height:40px;object-fit:cover;border-radius:9px"></div>
            <span class="sb-shop-name">{{ $shop->name ?? 'Boutique' }}</span>
        </a>
        <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer">✕</button>
        <div class="sb-status">
            <span class="pulse"></span>
            {{ ($shop->is_approved ?? true) ? 'Boutique active' : 'En attente' }}
        </div>
    </div>
    <div class="sb-scroll-hint" id="sbScrollHint">
        <div class="sb-scroll-hint-arrow">
            <div class="sb-scroll-hint-dot"></div>
            <div class="sb-scroll-hint-dot"></div>
            <div class="sb-scroll-hint-dot"></div>
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px"><span class="ico">⊞</span> Tableau de bord</a>
        <div class="sb-section">Boutique</div>
        <a href="{{ route('boutique.orders.index') }}" class="sb-item"><span class="ico">📦</span> Commandes @if($pendingCount > 0)<span class="sb-badge">{{ $pendingCount }}</span>@endif</a>
        <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">🏷️</span> Produits</a>
        <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">👥</span> Clients</a>
        <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">🧑‍💼</span> Équipe</a>
        <div class="sb-section">Livraison</div>
        <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">🚴</span> Livreurs</a>
        <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">🏢</span> Partenaires</a>
        <div class="sb-section">Finances</div>
        <div class="sb-group">
            <button class="sb-group-toggle open" onclick="toggleGroup(this)" type="button">
                <span class="ico">💰</span> Finances & Rapports
                <span class="sb-arrow" style="transform:rotate(90deg);color:rgba(255,255,255,.5)">▶</span>
            </button>
            <div class="sb-sub open">
                <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">💳</span> Paiements</a>
                <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">📊</span> Commissions</a>
                <a href="{{ route('boutique.reports.index') }}" class="sb-item active"><span class="ico">📋</span> Rapports</a>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">⚙️</span> Paramètres</a>
                @endif
            </div>
        </div>
        <div class="sb-section">Aide</div>
        <a href="{{ route('support.index') }}" class="sb-item"><span class="ico">🎧</span> Support</a>
    </nav>
    <div class="sb-footer">
        <a href="{{ route('profile.edit') }}" class="sb-user">
            <div class="sb-av-user">{{ $initials }}</div>
            <div style="flex:1;min-width:0">
                <div class="sb-uname">{{ Str::limit(auth()->user()->name, 20) }}</div>
                <div class="sb-urole">{{ auth()->user()->role === 'admin' ? 'Administrateur' : ucfirst(auth()->user()->role) }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="sb-logout"><span class="ico">⎋</span> Se déconnecter</button>
        </form>
    </div>
</aside>
<div class="sb-overlay" id="sbOverlay"></div>

{{-- MAIN --}}
<main class="main">
    <div class="topbar">
        <button class="btn-hamburger" id="btnMenu" aria-label="Menu">☰</button>
        <div class="tb-info">
            <div class="tb-title">📋 Rapports & Statistiques</div>
            <div class="tb-sub">{{ $shop->name ?? 'Boutique' }}@if($isSuper) · <span style="color:#f59e0b;font-weight:700">👑 Super Admin</span>@endif</div>
        </div>
    </div>

<div class="page-wrap">

    <div class="page-hd">
        <div>
            <h1 class="page-title">📋 Rapports & Statistiques</h1>
            <p class="page-sub">
                {{ $shop->name ?? 'Toutes les boutiques' }} &nbsp;·&nbsp;
                <span class="devise-badge">💱 {{ $devise }}</span>
                @if($isSuper) &nbsp;<span class="super-badge">👑 Super Admin</span>@endif
            </p>
        </div>
    </div>

    {{-- SECTION 1 : CE MOIS --}}
    <div class="sec-title">📅 Ce mois-ci</div>
    <div class="kpi-grid-2" style="margin-bottom:14px">

        <div class="kpi" style="--kc:#10b981">
            <div class="kpi-ico">💰</div>
            <div class="kpi-lbl">Revenu net ce mois</div>
            <div class="kpi-val" title="{{ number_format($revenueThisMonth, 0, ',', ' ') }} {{ $devise }}">
                {{ $fmt($revenueThisMonth) }}
            </div>
            @if($revenueThisMonth >= 1_000_000)
            <div class="kpi-full">= {{ number_format($revenueThisMonth, 0, ',', ' ') }} {{ $devise }}</div>
            @endif
            <div class="kpi-unit">{{ $devise }}</div>
            <div class="kpi-delta {{ $revenueDelta >= 0 ? 'up' : 'down' }}">
                {{ $revenueDelta >= 0 ? '↑' : '↓' }} {{ abs($revenueDelta) }}% vs mois précédent
            </div>
            <div class="kpi-explain">CA livrée ce mois, moins les commissions payées aux livreurs.</div>
        </div>

        <div class="kpi" style="--kc:#3b82f6">
            <div class="kpi-ico">📦</div>
            <div class="kpi-lbl">Commandes ce mois</div>
            <div class="kpi-val">{{ $ordersThisMonth }}</div>
            <div class="kpi-unit">commandes reçues</div>
            <div class="kpi-explain">Toutes les commandes passées ce mois, quel que soit leur statut.</div>
        </div>

    </div>

    {{-- SECTION 2 : PIPELINE --}}
    <div class="sec-title">📊 État des commandes (global)</div>
    <div class="kpi-grid-4" style="margin-bottom:22px">
        <div class="kpi" style="--kc:#6b7280">
            <div class="kpi-ico">🗂️</div><div class="kpi-lbl">Total commandes</div>
            <div class="kpi-val">{{ $totalOrders }}</div>
            <div class="kpi-unit">depuis le début</div>
            <div class="kpi-explain">Toutes les commandes créées dans cette boutique.</div>
        </div>
        <div class="kpi" style="--kc:#f59e0b">
            <div class="kpi-ico">⏳</div><div class="kpi-lbl">En attente</div>
            <div class="kpi-val">{{ $pendingOrders }}</div>
            <div class="kpi-unit">à traiter</div>
            <div class="kpi-explain">Commandes reçues mais pas encore confirmées. À traiter rapidement.</div>
        </div>
        <div class="kpi" style="--kc:#8b5cf6">
            <div class="kpi-ico">🚴</div><div class="kpi-lbl">En livraison</div>
            <div class="kpi-val">{{ $deliveringOrders }}</div>
            <div class="kpi-unit">en route</div>
            <div class="kpi-explain">Commandes actuellement en cours de livraison par un livreur.</div>
        </div>
        <div class="kpi" style="--kc:#ef4444">
            <div class="kpi-ico">❌</div><div class="kpi-lbl">Annulées</div>
            <div class="kpi-val">{{ $cancelledOrders }}</div>
            <div class="kpi-unit">commandes perdues</div>
            <div class="kpi-explain">Si ce chiffre est élevé, investiguer les raisons d'annulation.</div>
        </div>
    </div>

    {{-- SECTION 3 : PERFORMANCE --}}
    <div class="sec-title">🎯 Performance</div>
    <div class="kpi-grid-3">
        <div class="kpi" style="--kc:#10b981">
            <div class="kpi-ico">✅</div><div class="kpi-lbl">Commandes livrées</div>
            <div class="kpi-val">{{ $deliveredOrders }}</div>
            <div class="kpi-unit">livrées avec succès</div>
            <div class="kpi-explain">Commandes arrivées à destination. C'est votre indicateur de succès principal.</div>
        </div>
        <div class="kpi" style="--kc:{{ $tauxLivraison >= 80 ? '#10b981' : ($tauxLivraison >= 50 ? '#f59e0b' : '#ef4444') }}">
            <div class="kpi-ico">{{ $tauxLivraison >= 80 ? '🏆' : ($tauxLivraison >= 50 ? '⚠️' : '🚨') }}</div>
            <div class="kpi-lbl">Taux de livraison</div>
            <div class="kpi-val">{{ $tauxLivraison }}%</div>
            <div class="kpi-unit">des commandes livrées</div>
            <div class="kpi-explain">
                @if($tauxLivraison >= 80) ✓ Excellent — votre service est très fiable.
                @elseif($tauxLivraison >= 50) ⚠ Moyen — des améliorations sont possibles.
                @else 🚨 Faible — investiguer les causes d'échec de livraison.
                @endif
            </div>
        </div>
        <div class="kpi" style="--kc:#f59e0b">
            <div class="kpi-ico">🛒</div><div class="kpi-lbl">Panier moyen</div>
            <div class="kpi-val" title="{{ number_format($panierMoyen, 0, ',', ' ') }} {{ $devise }}">
                {{ $fmt($panierMoyen) }}
            </div>
            @if($panierMoyen >= 1_000_000)
            <div class="kpi-full">= {{ number_format($panierMoyen, 0, ',', ' ') }} {{ $devise }}</div>
            @endif
            <div class="kpi-unit">{{ $devise }} / commande livrée</div>
            <div class="kpi-explain">Montant moyen dépensé par commande livrée. Plus c'est élevé, mieux c'est.</div>
        </div>
    </div>

    {{-- SECTION 4 : GRAPHIQUE --}}
    <div class="sec-title">📈 Évolution sur 6 mois</div>
    <div class="chart-card">
        <div class="chart-card-hd">
            <span class="chart-card-title">Revenus & Commandes — 6 derniers mois</span>
            <div class="chart-tabs">
                <button class="chart-tab active" data-chart="revenue">💰 Revenus</button>
                <button class="chart-tab" data-chart="orders">📦 Commandes</button>
            </div>
        </div>
        <div class="chart-body">
            <div class="chart-bars-wrap" id="chartBars">
                @foreach($chartMois as $m)
                @php
                    $hRev = $m['revenue'] > 0 ? max(round(($m['revenue']/$maxRevenue)*100), 4) : 2;
                    $hOrd = $m['orders']  > 0 ? max(round(($m['orders'] /$maxOrders) *100), 4) : 2;
                    $tipRev = number_format($m['revenue'],0,',',' ').' '.$devise.' · '.$m['orders'].' cmd';
                    $tipOrd = $m['orders'].' commandes · '.number_format($m['revenue'],0,',',' ').' '.$devise;
                @endphp
                <div class="chart-bar-col">
                    <div class="chart-nb" id="nb-{{ $loop->index }}">{{ $m['orders'] > 0 ? $m['orders'] : '' }}</div>
                    <div class="chart-bar {{ $m['actuel'] ? 'actuel' : '' }}"
                         id="bar-{{ $loop->index }}"
                         data-h-revenue="{{ $hRev }}"
                         data-h-orders="{{ $hOrd }}"
                         data-tip-revenue="{{ $tipRev }}"
                         data-tip-orders="{{ $tipOrd }}"
                         data-h="{{ $hRev }}"
                         data-tip="{{ $tipRev }}"
                         style="height:0%">
                    </div>
                </div>
                @endforeach
            </div>
            <div class="chart-labels">
                @foreach($chartMois as $m)
                <div class="chart-lbl {{ $m['actuel'] ? 'actuel' : '' }}">{{ $m['label'] }}</div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- SECTION 5 : FINANCES --}}
    <div class="sec-title">💳 Finances</div>
    <div class="kpi-grid-3">

        <div class="kpi" style="--kc:#10b981">
            <div class="kpi-ico">💵</div><div class="kpi-lbl">Revenu net total</div>
            <div class="kpi-val" title="{{ number_format($totalRevenue, 0, ',', ' ') }} {{ $devise }}">
                {{ $fmt($totalRevenue) }}
            </div>
            @if($totalRevenue >= 1_000_000)
            <div class="kpi-full">= {{ number_format($totalRevenue, 0, ',', ' ') }} {{ $devise }}</div>
            @endif
            <div class="kpi-unit">{{ $devise }}</div>
            <div class="kpi-explain">CA livré - commissions payées aux livreurs. Votre vrai revenu net.</div>
        </div>

        <div class="kpi" style="--kc:#f59e0b">
            <div class="kpi-ico">⏳</div><div class="kpi-lbl">Commissions à payer</div>
            <div class="kpi-val" title="{{ number_format($commissionsPending, 0, ',', ' ') }} {{ $devise }}">
                {{ $fmt($commissionsPending) }}
            </div>
            @if($commissionsPending >= 1_000_000)
            <div class="kpi-full">= {{ number_format($commissionsPending, 0, ',', ' ') }} {{ $devise }}</div>
            @endif
            <div class="kpi-unit">{{ $devise }} · à régler aux livreurs</div>
            <div class="kpi-explain">
                Ce montant est dû à vos livreurs.
                <a href="{{ route('boutique.commissions.index') }}" style="color:var(--brand);font-weight:600">Payer maintenant →</a>
            </div>
        </div>

        <div class="kpi" style="--kc:#3b82f6">
            <div class="kpi-ico">✅</div><div class="kpi-lbl">Commissions payées</div>
            <div class="kpi-val" title="{{ number_format($commissionsPaid, 0, ',', ' ') }} {{ $devise }}">
                {{ $fmt($commissionsPaid) }}
            </div>
            @if($commissionsPaid >= 1_000_000)
            <div class="kpi-full">= {{ number_format($commissionsPaid, 0, ',', ' ') }} {{ $devise }}</div>
            @endif
            <div class="kpi-unit">{{ $devise }} · déjà réglés</div>
            <div class="kpi-explain">Total des commissions déjà versées à vos livreurs.</div>
        </div>

    </div>

    {{-- SECTION 6 : ÉQUIPE --}}
    <div class="sec-title">👥 Équipe</div>
    <div class="kpi-grid-2" style="margin-bottom:22px">
        <div class="kpi" style="--kc:#2563eb">
            <div class="kpi-ico">👤</div><div class="kpi-lbl">Vendeurs actifs</div>
            <div class="kpi-val">{{ $vendors }}</div>
            <div class="kpi-unit">vendeurs rattachés</div>
            <div class="kpi-explain">Nombre de vendeurs ayant traité des commandes dans cette boutique.</div>
        </div>
        <div class="kpi" style="--kc:#7c3aed">
            <div class="kpi-ico">🚴</div><div class="kpi-lbl">Livreurs actifs</div>
            <div class="kpi-val">{{ $livreurs }}</div>
            <div class="kpi-unit">livreurs rattachés</div>
            <div class="kpi-explain">Nombre de livreurs ayant effectué au moins une livraison.</div>
        </div>
    </div>

    {{-- SECTION 7 : TOP PRODUITS --}}
    @if(isset($topProducts) && $topProducts->count() > 0)
    @php $maxVentes = $topProducts->max('ventes') ?: 1; $rangs = ['🥇','🥈','🥉','4','5']; @endphp
    <div class="sec-title">🏆 Top produits ce mois</div>
    <div class="top-card">
        <div class="chart-card-hd">
            <span class="chart-card-title">Produits les plus vendus ce mois</span>
            <a href="{{ route('products.index') }}" style="font-size:11px;color:var(--brand);font-weight:600;text-decoration:none">Voir tous →</a>
        </div>
        @foreach($topProducts as $i => $product)
        @php $pct = round(($product->ventes/$maxVentes)*100); @endphp
        <div class="sp-row">
            <span class="sp-rank {{ $i < 3 ? 'top' : '' }}">{{ $rangs[$i] ?? $i+1 }}</span>
            <span class="sp-lbl" title="{{ $product->name }}">{{ Str::limit($product->name, 30) }}</span>
            <div class="sp-track"><div class="sp-fill" data-pct="{{ $pct }}" style="width:0%"></div></div>
            <span class="sp-val">{{ $product->ventes }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- SECTION 8 : LIENS RAPIDES --}}
    <div class="sec-title">🔗 Accès rapide</div>
    <div class="links-grid">
        <a href="{{ route('boutique.orders.index') }}" class="quick-link"><span class="quick-link-ico">📦</span><span class="quick-link-lbl">Commandes</span><span class="quick-link-sub">{{ $pendingOrders }} en attente</span></a>
        <a href="{{ route('boutique.payments.index') }}" class="quick-link"><span class="quick-link-ico">💳</span><span class="quick-link-lbl">Paiements</span><span class="quick-link-sub">Voir les revenus</span></a>
        <a href="{{ route('boutique.commissions.index') }}" class="quick-link"><span class="quick-link-ico">💸</span><span class="quick-link-lbl">Commissions</span><span class="quick-link-sub">{{ number_format($commissionsPending/1000,0) }}k {{ $devise }} à payer</span></a>
        <a href="{{ route('boutique.clients.index') }}" class="quick-link"><span class="quick-link-ico">👥</span><span class="quick-link-lbl">Clients</span><span class="quick-link-sub">Voir les clients</span></a>
    </div>

</div>{{-- /page-wrap --}}
</main>
</div>{{-- /dash-wrap --}}

@endsection

@push('scripts')
<script>
function toggleGroup(btn) {
    const sub = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');
    document.querySelectorAll('.sb-sub.open').forEach(s => { s.classList.remove('open'); s.previousElementSibling?.classList.remove('open'); });
    if (!isOpen) {
        sub.classList.add('open'); btn.classList.add('open');
        const sidebar = document.getElementById('sidebar');
        setTimeout(() => { const support = sidebar?.querySelector('a[href*="support"]'); if (support && sidebar) support.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }, 220);
    }
}

(function initSidebar() {
    const sidebar    = document.getElementById('sidebar');
    const overlay    = document.getElementById('sbOverlay');
    const scrollHint = document.getElementById('sbScrollHint');
    document.getElementById('btnMenu')?.addEventListener('click', () => { sidebar.classList.add('open'); overlay.classList.add('open'); });
    overlay?.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });
    document.getElementById('btnCloseSidebar')?.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });
    function updateScrollHint() {
        if (!sidebar || !scrollHint) return;
        scrollHint.classList.toggle('hidden', sidebar.scrollTop + sidebar.clientHeight >= sidebar.scrollHeight - 16);
    }
    sidebar?.addEventListener('scroll', updateScrollHint);
    window.addEventListener('resize', updateScrollHint);
    setTimeout(updateScrollHint, 300);
})();

document.addEventListener('DOMContentLoaded', () => {
    /* Animation barres graphique */
    document.querySelectorAll('#chartBars .chart-bar').forEach((bar, i) => {
        setTimeout(() => { bar.style.transition = 'height .6s cubic-bezier(.23,1,.32,1)'; bar.style.height = bar.dataset.h + '%'; }, i * 80);
    });

    /* Sparklines top produits */
    document.querySelectorAll('.sp-fill').forEach((el, i) => {
        setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 200 + i * 100);
    });

    /* Afficher .kpi-full sur desktop au survol */
    document.querySelectorAll('.kpi-full').forEach(el => {
        el.style.display = '';
    });

    /* Tabs graphique */
    document.querySelectorAll('.chart-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const type = tab.dataset.chart;
            document.querySelectorAll('#chartBars .chart-bar').forEach((bar, i) => {
                const h   = bar.dataset['h' + (type === 'revenue' ? 'Revenue' : 'Orders')];
                const tip = bar.dataset['tip' + (type === 'revenue' ? 'Revenue' : 'Orders')];
                bar.dataset.tip = tip;
                bar.classList.toggle('orders-bar', type === 'orders');
                bar.style.transition = 'height .4s cubic-bezier(.23,1,.32,1)';
                setTimeout(() => { bar.style.height = h + '%'; }, i * 40);
            });
        });
    });
});
</script>
@endpush