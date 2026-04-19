{{--
    resources/views/vendeur/payments/index.blade.php
    Route     : GET /boutique/payments  → Vendeur\PaymentController@index
    Variables :
      $payments     → LengthAwarePaginator<Payment>  (with order.user)
      $totalRevenue → float   (total global de tous les paiements confirmés)
      $shop         → Shop
      $devise       → string  (ex: GNF, EUR, USD)
--}}

@extends('layouts.app')

@section('title', 'Revenus · ' . $shop->name)

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

/* ══ LAYOUT ══ */
.dash-wrap { display: flex; min-height: 100vh; }
.dash-wrap .main { margin-left: var(--sb-w); flex: 1; min-width: 0; }

/* ══ SIDEBAR ══ */
.sidebar {
    background: var(--sb-bg);
    display: flex; flex-direction: column;
    position: fixed; top: 0; left: 0; bottom: 0;
    width: var(--sb-w);
    overflow-y: scroll;
    scrollbar-width: thin;
    scrollbar-color: rgba(16,185,129,.4) rgba(255,255,255,.05);
    z-index: 40;
    border-right: 1px solid rgba(0,0,0,.2);
}
.sidebar::-webkit-scrollbar       { width: 4px; }
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
.sb-badge.warn { background: #f59e0b; }
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: var(--r-sm); font-size: 13px; font-weight: 500; color: var(--sb-txt); cursor: pointer; transition: background .15s, color .15s; user-select: none; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); }
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.8); }
.sb-group-toggle.open  { color: rgba(255,255,255,.9); background: rgba(255,255,255,.03); }
.sb-group-toggle .ico  { font-size: 14px; width: 20px; text-align: center; flex-shrink: 0; }
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

/* ══ MAIN + TOPBAR ══ */
.main { display: flex; flex-direction: column; min-width: 0; }
.topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 22px; height: var(--top-h); display: flex; align-items: center; gap: 12px; position: sticky; top: 0; z-index: 30; box-shadow: var(--shadow-sm); }
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); font-size: 20px; }
.tb-info { flex: 1; min-width: 0; }
.tb-title { font-size: 14px; font-weight: 700; color: var(--text); }
.tb-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* ── Page intérieure ── */
.page-wrap { padding: 22px 22px 60px; }

/* ── Header ── */
.page-hd {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 16px;
    margin-bottom: 24px; flex-wrap: wrap;
}
.page-title { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -.4px; margin: 0 0 4px; }
.page-sub   { font-size: 13px; color: var(--muted); margin: 0; }
.devise-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--brand-mlt); color: var(--brand-dk);
    border: 1px solid var(--brand-lt);
    font-size: 11px; font-weight: 700; font-family: var(--mono);
    padding: 4px 10px; border-radius: 20px;
}

/* ── Hero revenus ── */
.revenue-hero {
    background: linear-gradient(135deg, #0d1f18 0%, #1a3328 100%);
    border: 1px solid rgba(16,185,129,.2);
    border-radius: var(--r);
    padding: 28px 32px;
    display: flex; align-items: center; gap: 24px;
    margin-bottom: 22px;
    position: relative; overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.12);
}
.revenue-hero::after {
    content: '';
    position: absolute; right: -40px; top: -40px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(16,185,129,.1) 0%, transparent 70%);
    pointer-events: none;
}
.hero-icon {
    width: 64px; height: 64px;
    background: rgba(16,185,129,.15);
    border: 1px solid rgba(16,185,129,.25);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; flex-shrink: 0;
}
.hero-lbl { font-size: 12px; font-weight: 600; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.hero-val  { font-size: 36px; font-weight: 800; color: #fff; font-family: var(--mono); letter-spacing: -1.5px; line-height: 1; }
.hero-unit { font-size: 13px; color: rgba(255,255,255,.4); margin-top: 4px; }
.hero-right { margin-left: auto; text-align: right; }
.hero-stat-lbl { font-size: 11px; color: rgba(255,255,255,.35); font-weight: 500; margin-bottom: 3px; }
.hero-stat-val { font-size: 18px; font-weight: 700; font-family: var(--mono); color: rgba(255,255,255,.7); }

/* ── KPI row ── */
.kpi-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 24px; }
.kpi-chip {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 16px 18px;
    box-shadow: var(--shadow-sm);
    border-top: 3px solid var(--kc, var(--brand));
}
.kpi-chip-lbl { font-size: 10.5px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
.kpi-chip-val { font-size: 22px; font-weight: 700; font-family: var(--mono); color: var(--text); letter-spacing: -.5px; }
.kpi-chip-sub { font-size: 11px; color: var(--muted); margin-top: 3px; }

/* ── Flash ── */
.flash {
    padding: 10px 14px; border-radius: var(--r-sm); border: 1px solid;
    font-size: 13px; font-weight: 500; margin-bottom: 18px;
    display: flex; align-items: center; gap: 8px;
}
.flash-success { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; }
.flash-danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }

/* ── Table card ── */
.pay-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm);
    margin-bottom: 22px;
}
.pay-card-hd {
    padding: 13px 20px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--bg);
}
.pay-card-title { font-size: 13px; font-weight: 700; color: var(--text); }

/* ── Table ── */
.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl thead th {
    padding: 11px 16px; text-align: left;
    font-size: 10px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: .6px;
    background: var(--bg); border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.tbl tbody td { padding: 13px 16px; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; }

/* Colonnes */
.ref { font-family: var(--mono); font-size: 11px; color: var(--muted); }
.client-cell { display: flex; align-items: center; gap: 9px; }
.c-av {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, var(--brand), #2563eb);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.c-name { font-size: 13px; font-weight: 600; color: var(--text); }
.c-sub  { font-size: 11px; color: var(--muted); }

.amount-cell {
    font-family: var(--mono); font-weight: 700; font-size: 14px;
    color: var(--text); white-space: nowrap;
}
.amount-cell small { font-size: 10px; color: var(--muted); font-weight: 500; }

.method-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: #f3f6f4; color: var(--text-2);
    border: 1px solid var(--border);
    font-size: 11px; font-weight: 600;
    padding: 3px 9px; border-radius: 20px;
}

/* Pills */
.pill { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
.p-success { background: #d1fae5; color: #065f46; }
.p-warning { background: #fef3c7; color: #92400e; }

.date-cell { font-size: 12px; color: var(--text-2); }
.date-cell small { font-size: 10px; color: var(--muted); display: block; margin-top: 1px; }

/* Empty */
.empty-state { padding: 56px 20px; text-align: center; }
.empty-state .ico { font-size: 40px; display: block; margin-bottom: 12px; opacity: .35; }
.empty-state p { font-size: 14px; color: var(--muted); }

/* Pagination */
.pagination-wrap { display: flex; justify-content: center; padding: 14px 0 2px; }

/* ══ RESPONSIVE ══ */

/* Cartes mobiles paiements */
.pay-table  { display: block; }
.pay-mobile { display: none; flex-direction: column; gap: 10px; }

.m-pay-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); }
.m-pay-hd { padding: 11px 14px; background: var(--bg); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.m-pay-body { padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; }
.m-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.m-lbl { font-size: 10.5px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }

@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .page-wrap { padding: 16px 14px 40px; }
    .revenue-hero { flex-direction: column; padding: 22px 20px; gap: 16px; }
    .hero-right { margin-left: 0; text-align: left; }
    .kpi-row { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 640px) {
    .pay-table  { display: none; }
    .pay-mobile { display: flex; }
}

@media (max-width: 520px) {
    .page-wrap { padding: 12px 10px 40px; }
    .kpi-row { grid-template-columns: 1fr 1fr; gap: 8px; }
    .revenue-hero { padding: 18px 16px; gap: 14px; }
    .hero-val { font-size: 28px; }
    .hero-icon { width: 48px; height: 48px; font-size: 22px; }
}

@media (max-width: 380px) {
    .kpi-row { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

@php
    $devise      = $devise ?? ($shop->currency ?? 'GNF');
    $nbPaiements = $payments->total();
    $panierMoyen = $nbPaiements > 0 ? round($totalRevenue / $nbPaiements) : 0;
    $caPage      = $payments->getCollection()->sum('amount');
    $init        = function(string $name): string {
        $p = explode(' ', $name);
        return strtoupper(substr($p[0],0,1)) . strtoupper(substr($p[1]??'X',0,1));
    };
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $pendingCount = $shop->orders()->whereIn('status',['pending','en attente','en_attente','confirmée','processing'])->count();
@endphp

<div class="dash-wrap">

{{-- ══════ SIDEBAR ══════ --}}
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
           <div class="sb-logo-icon"><img src="/images/Shopio2.jpeg" alt="Shopio" style="width:40px;height:40px;object-fit:cover;border-radius:9px"></div>
            <span class="sb-shop-name">{{ $shop->name }}</span>
        </a>
        <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer">✕</button>
        <div class="sb-status">
            <span class="pulse"></span>
            {{ $shop->is_approved ? 'Boutique active' : 'En attente' }}
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
        <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px">
            <span class="ico">⊞</span> Tableau de bord
        </a>
        <div class="sb-section">Boutique</div>
        <a href="{{ route('boutique.orders.index') }}" class="sb-item">
            <span class="ico">📦</span> Commandes
            @if($pendingCount > 0)<span class="sb-badge">{{ $pendingCount }}</span>@endif
        </a>
        <a href="{{ route('products.index') }}" class="sb-item">
            <span class="ico">🏷️</span> Produits
        </a>
        <a href="{{ route('boutique.clients.index') }}" class="sb-item">
            <span class="ico">👥</span> Clients
        </a>
        <a href="{{ route('boutique.employees.index') }}" class="sb-item">
            <span class="ico">🧑‍💼</span> Équipe
        </a>
        <div class="sb-section">Livraison</div>
        <a href="{{ route('boutique.livreurs.index') }}" class="sb-item">
            <span class="ico">🚴</span> Livreurs
        </a>
        <a href="{{ route('delivery.companies.index') }}" class="sb-item">
            <span class="ico">🏢</span> Partenaires
        </a>
        <div class="sb-section">Finances</div>
        <div class="sb-group">
            <button class="sb-group-toggle open" onclick="toggleGroup(this)" type="button">
                <span class="ico">💰</span>
                Finances & Rapports
                <span class="sb-arrow" style="transform:rotate(90deg);color:rgba(255,255,255,.5)">▶</span>
            </button>
            <div class="sb-sub open">
                <a href="{{ route('boutique.payments.index') }}" class="sb-item active">
                    <span class="ico">💳</span> Paiements
                </a>
                <a href="{{ route('boutique.commissions.index') }}" class="sb-item">
                    <span class="ico">📊</span> Commissions
                </a>
                <a href="{{ route('boutique.reports.index') }}" class="sb-item">
                    <span class="ico">📋</span> Rapports
                </a>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('shop.edit', $shop) }}" class="sb-item">
                    <span class="ico">⚙️</span> Paramètres
                </a>
                @endif
            </div>
        </div>
        <div class="sb-section">Aide</div>
        <a href="{{ route('support.index') }}" class="sb-item">
            <span class="ico">🎧</span> Support
        </a>
    </nav>
    <div class="sb-footer">
        <a href="{{ route('profile.edit') }}" class="sb-user">
            <div class="sb-av-user">{{ $initials }}</div>
            <div style="flex:1;min-width:0">
                <div class="sb-uname">{{ Str::limit(auth()->user()->name, 20) }}</div>
                <div class="sb-urole">{{ auth()->user()->role === 'admin' ? 'Administrateur' : ucfirst(auth()->user()->role) }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-logout">
                <span class="ico">⎋</span> Se déconnecter
            </button>
        </form>
    </div>
</aside>
<div class="sb-overlay" id="sbOverlay"></div>

{{-- ══════ MAIN ══════ --}}
<main class="main">
    <div class="topbar">
        <button class="btn-hamburger" id="btnMenu" aria-label="Menu">☰</button>
        <div class="tb-info">
            <div class="tb-title">💰 Revenus & Paiements</div>
            <div class="tb-sub">{{ $shop->name }} · <span style="font-family:var(--mono);font-size:10px;background:var(--brand-mlt);color:var(--brand-dk);padding:1px 7px;border-radius:10px;border:1px solid var(--brand-lt)">{{ $devise }}</span></div>
        </div>
    </div>

<div class="page-wrap">

    {{-- ── Header ── --}}
    <div class="page-hd">
        <div>
            <h1 class="page-title">💰 Revenus</h1>
            <p class="page-sub">
                {{ $shop->name }}
                &nbsp;·&nbsp;
                <span class="devise-badge">💱 {{ $devise }}</span>
            </p>
        </div>
    </div>

    {{-- ── Hero total revenus ── --}}
    <div class="revenue-hero">
        <div class="hero-icon">💵</div>
        <div>
            <div class="hero-lbl">Total des revenus confirmés</div>
            {{-- Devise dynamique de la boutique --}}
            <div class="hero-val">{{ number_format($totalRevenue, 0, ',', ' ') }}</div>
            <div class="hero-unit">{{ $devise }} · paiements confirmés uniquement</div>
        </div>
        <div class="hero-right">
            <div class="hero-stat-lbl">Paiements</div>
            <div class="hero-stat-val">{{ $nbPaiements }}</div>
            <div class="hero-stat-lbl" style="margin-top:10px">Panier moyen</div>
            <div class="hero-stat-val">{{ number_format($panierMoyen, 0, ',', ' ') }} {{ $devise }}</div>
        </div>
    </div>

    {{-- ── KPI row ── --}}
    <div class="kpi-row">
        <div class="kpi-chip" style="--kc:#10b981">
            <div class="kpi-chip-lbl">CA total confirmé</div>
            <div class="kpi-chip-val">{{ number_format($totalRevenue/1000, 1) }}k</div>
            <div class="kpi-chip-sub">{{ $devise }}</div>
        </div>
        <div class="kpi-chip" style="--kc:#3b82f6">
            <div class="kpi-chip-lbl">Nb paiements</div>
            <div class="kpi-chip-val">{{ $nbPaiements }}</div>
            <div class="kpi-chip-sub">paiements confirmés</div>
        </div>
        <div class="kpi-chip" style="--kc:#f59e0b">
            <div class="kpi-chip-lbl">Panier moyen</div>
            <div class="kpi-chip-val">{{ number_format($panierMoyen/1000, 1) }}k</div>
            <div class="kpi-chip-sub">{{ $devise }} / paiement</div>
        </div>
    </div>

    {{-- Flash --}}
    @foreach(['success','danger'] as $type)
        @if(session($type))
        <div class="flash flash-{{ $type }}">
            <span>{{ $type === 'success' ? '✓' : '✕' }}</span>
            {{ session($type) }}
        </div>
        @endif
    @endforeach

    {{-- ── Table paiements ── --}}
    <div class="pay-card pay-table">
        <div class="pay-card-hd">
            <span class="pay-card-title">Historique des paiements</span>
            <span style="font-size:11px;color:var(--muted)">
                Page {{ $payments->currentPage() }}/{{ $payments->lastPage() }}
                · {{ $payments->total() }} paiement(s)
            </span>
        </div>

        @if($payments->isEmpty())
        <div class="empty-state">
            <span class="ico">💳</span>
            <p>Aucun paiement confirmé pour le moment.</p>
        </div>
        @else
        <table class="tbl">
            <thead>
                <tr>
                    <th>Réf</th>
                    <th>Client</th>
                    <th>Montant</th>
                    <th>Méthode</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                @php
                    $client = $payment->order?->user;
                    $initiales = $client ? $init($client->name) : 'CL';
                @endphp
                <tr>
                    {{-- Référence commande --}}
                    <td>
                        <span class="ref">#{{ $payment->order?->id ?? $payment->id }}</span>
                    </td>

                    {{-- Client --}}
                    <td>
                        <div class="client-cell">
                            <div class="c-av">{{ $initiales }}</div>
                            <div>
                                <div class="c-name">{{ $client?->name ?? 'Client inconnu' }}</div>
                                @if($client?->phone)
                                <div class="c-sub">📞 {{ $client->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Montant avec la devise de la boutique --}}
                    <td>
                        <div class="amount-cell">
                            {{ number_format($payment->amount, 0, ',', ' ') }}
                            <small>{{ $devise }}</small>
                        </div>
                    </td>

                    {{-- Méthode de paiement --}}
                    <td>
                        <span class="method-badge">
                            @if($payment->method === 'cash') 💵 @elseif($payment->method === 'mobile') 📱 @else 💳 @endif
                            {{ ucfirst($payment->method) }}
                        </span>
                    </td>

                    {{-- Statut --}}
                    <td>
                        @if($payment->status === 'payé' || $payment->status === 'paid')
                            <span class="pill p-success">✓ Payé</span>
                        @else
                            <span class="pill p-warning">⏳ En attente</span>
                        @endif
                    </td>

                    {{-- Date --}}
                    <td>
                        <div class="date-cell">
                            {{ $payment->created_at->format('d/m/Y') }}
                            <small>{{ $payment->created_at->format('H:i') }}</small>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrap">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

    {{-- ════ CARTES MOBILES (< 640px) ════ --}}
    <div class="pay-mobile">
        @forelse($payments as $payment)
        @php
            $client    = $payment->order?->user;
            $initiales = $client ? $init($client->name) : 'CL';
        @endphp
        <div class="m-pay-card">
            {{-- Header : ref + statut --}}
            <div class="m-pay-hd">
                <div style="display:flex;align-items:center;gap:9px">
                    <div class="c-av" style="width:34px;height:34px;font-size:11px">{{ $initiales }}</div>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $client?->name ?? 'Client inconnu' }}</div>
                        <div style="font-size:10.5px;color:var(--muted);font-family:var(--mono)">#{{ $payment->order?->id ?? $payment->id }}</div>
                    </div>
                </div>
                @if($payment->status === 'payé' || $payment->status === 'paid')
                    <span class="pill p-success">✓ Payé</span>
                @else
                    <span class="pill p-warning">⏳ En attente</span>
                @endif
            </div>
            {{-- Corps --}}
            <div class="m-pay-body">
                <div class="m-row">
                    <span class="m-lbl">💰 Montant</span>
                    <span style="font-family:var(--mono);font-weight:700;font-size:15px;color:var(--brand)">
                        {{ number_format($payment->amount, 0, ',', ' ') }}
                        <span style="font-size:10px;color:var(--muted);font-weight:500">{{ $devise }}</span>
                    </span>
                </div>
                <div class="m-row">
                    <span class="m-lbl">💳 Méthode</span>
                    <span class="method-badge">
                        @if($payment->method === 'cash') 💵 @elseif($payment->method === 'mobile') 📱 @else 💳 @endif
                        {{ ucfirst($payment->method) }}
                    </span>
                </div>
                <div class="m-row">
                    <span class="m-lbl">📅 Date</span>
                    <span style="font-size:12px;color:var(--text-2);font-weight:500">
                        {{ $payment->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;font-size:14px;color:var(--muted)">
            Aucun paiement confirmé pour le moment.
        </div>
        @endforelse
        <div class="pagination-wrap">{{ $payments->links() }}</div>
    </div>

</div>{{-- /page-wrap --}}
</main>
</div>{{-- /dash-wrap --}}

@endsection

@push('scripts')
<script>
function toggleGroup(btn) {
    const sub    = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');
    document.querySelectorAll('.sb-sub.open').forEach(s => {
        s.classList.remove('open');
        s.previousElementSibling?.classList.remove('open');
    });
    if (!isOpen) {
        sub.classList.add('open');
        btn.classList.add('open');
        const sidebar = document.getElementById('sidebar');
        setTimeout(() => {
            const support = sidebar?.querySelector('a[href*="support"]');
            if (support && sidebar) support.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 220);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const sidebar    = document.getElementById('sidebar');
    const overlay    = document.getElementById('sbOverlay');
    const scrollHint = document.getElementById('sbScrollHint');

    document.getElementById('btnMenu')?.addEventListener('click', () => {
        sidebar.classList.add('open'); overlay.classList.add('open');
    });
    overlay?.addEventListener('click', () => {
        sidebar.classList.remove('open'); overlay.classList.remove('open');
    });
    document.getElementById('btnCloseSidebar')?.addEventListener('click', () => {
        sidebar.classList.remove('open'); overlay.classList.remove('open');
    });

    function updateScrollHint() {
        if (!sidebar || !scrollHint) return;
        const atBottom    = sidebar.scrollTop + sidebar.clientHeight >= sidebar.scrollHeight - 16;
        const needsScroll = sidebar.scrollHeight > sidebar.clientHeight + 20;
        scrollHint.classList.toggle('hidden', atBottom || !needsScroll);
    }
    sidebar?.addEventListener('scroll', updateScrollHint);
    window.addEventListener('resize', updateScrollHint);
    setTimeout(updateScrollHint, 300);
});
</script>
@endpush