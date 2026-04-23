{{--
    resources/views/boutique/commissions/index.blade.php
    Route     : GET  /boutique/commissions      → Vendeur\CommissionController@index
    Route     : POST /boutique/commissions/pay  → Vendeur\CommissionController@pay
    Variables :
      $commissions  → LengthAwarePaginator<CourierCommission>
      $status       → string  ('en_attente' | 'payée')
      $totalPending → float   (total en attente)
      $totalPaid    → float   (total payé)
      $shop         → Shop
      $devise       → string  (ex: GNF, EUR, USD)
--}}

@extends('layouts.app')

@section('title', 'Commissions · ' . $shop->name)

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
    --warning:    #f59e0b; --warning-lt: #fef3c7;
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
    margin-bottom: 22px; flex-wrap: wrap;
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

/* ── KPI cards ── */
.kpi-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 24px; }
.kpi-card {
    border-radius: var(--r); padding: 22px 24px;
    display: flex; align-items: center; gap: 16px;
    position: relative; overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.kpi-card.pending {
    background: linear-gradient(135deg, #451a03, #78350f);
    border: 1px solid rgba(245,158,11,.25);
}
.kpi-card.paid {
    background: linear-gradient(135deg, #052e16, #14532d);
    border: 1px solid rgba(16,185,129,.25);
}
.kpi-card::after {
    content: ''; position: absolute; right: -30px; top: -30px;
    width: 120px; height: 120px; border-radius: 50%;
    background: rgba(255,255,255,.04); pointer-events: none;
}
.kpi-ico {
    width: 52px; height: 52px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; flex-shrink: 0;
}
.kpi-card.pending .kpi-ico { background: rgba(245,158,11,.15); border: 1px solid rgba(245,158,11,.25); }
.kpi-card.paid    .kpi-ico { background: rgba(16,185,129,.15);  border: 1px solid rgba(16,185,129,.25); }
.kpi-lbl { font-size: 11px; font-weight: 600; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px; }
.kpi-val { font-size: 28px; font-weight: 800; font-family: var(--mono); letter-spacing: -1px; line-height: 1; }
.kpi-card.pending .kpi-val { color: #fcd34d; }
.kpi-card.paid    .kpi-val { color: #34d399; }
.kpi-unit { font-size: 11px; color: rgba(255,255,255,.35); margin-top: 3px; }

/* ── Filtres ── */
.filter-bar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r-sm); padding: 12px 16px;
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 20px; box-shadow: var(--shadow-sm);
    flex-wrap: wrap;
}
.filter-select {
    padding: 8px 14px; border-radius: var(--r-sm);
    border: 1.5px solid var(--border-dk);
    font-size: 13px; font-family: var(--font);
    color: var(--text); background: var(--surface);
    outline: none; transition: border-color .15s; min-width: 160px;
}
.filter-select:focus { border-color: var(--brand); }

/* ── Sticky payout form ── */
.payout-bar {
    position: sticky; top: 0; z-index: 30;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 14px 18px;
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 18px;
    box-shadow: var(--shadow);
    flex-wrap: wrap;
    transition: box-shadow .2s;
}
.payout-bar.has-selection {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(16,185,129,.12), var(--shadow);
}
.payout-input {
    flex: 1; min-width: 180px;
    padding: 9px 14px; border-radius: var(--r-sm);
    border: 1.5px solid var(--border-dk);
    font-size: 13px; font-family: var(--font);
    color: var(--text); background: var(--bg);
    outline: none; transition: border-color .15s;
}
.payout-input:focus { border-color: var(--brand); background: var(--surface); }
.payout-count {
    font-size: 12px; font-weight: 700; color: var(--muted);
    white-space: nowrap; padding: 8px 12px;
    background: var(--bg); border-radius: var(--r-sm);
    border: 1px solid var(--border);
}
.payout-count.active { background: var(--brand-mlt); color: var(--brand-dk); border-color: var(--brand-lt); }

/* ── Boutons ── */
.btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 8px 16px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 600; font-family: var(--font);
    border: 1px solid var(--border-dk); background: var(--surface);
    color: var(--text-2); cursor: pointer; text-decoration: none;
    transition: all .15s; white-space: nowrap;
}
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-sm { padding: 6px 12px; font-size: 12px; }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(16,185,129,.3); }
.btn-primary:hover { background: var(--brand-dk); color: #fff; }
.btn-primary:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }

/* ── Card table ── */
.comm-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm);
    margin-bottom: 18px;
}
.comm-card-hd {
    padding: 13px 20px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--bg);
}
.comm-card-title { font-size: 13px; font-weight: 700; color: var(--text); }

/* ── Table ── */
.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl thead th {
    padding: 11px 14px; text-align: left;
    font-size: 10px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: .6px;
    background: var(--bg); border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.tbl tbody td { padding: 12px 14px; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; }
.tbl tbody tr.selected td { background: var(--brand-mlt); }

/* Checkbox custom */
input[type=checkbox] {
    width: 16px; height: 16px; border-radius: 4px;
    accent-color: var(--brand); cursor: pointer;
}

/* Livreur chip */
.lv-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f3f6f4; border: 1px solid var(--border);
    border-radius: 20px; padding: 3px 10px 3px 4px;
    font-size: 12px; font-weight: 600; color: var(--text-2);
}
.lv-av {
    width: 22px; height: 22px; border-radius: 50%;
    background: linear-gradient(135deg, var(--brand), #2563eb);
    color: #fff; display: flex; align-items: center;
    justify-content: center; font-size: 8px; font-weight: 700;
}

/* Montant */
.amount { font-family: var(--mono); font-weight: 700; font-size: 13.5px; color: var(--text); white-space: nowrap; }
.amount small { font-size: 10px; color: var(--muted); font-weight: 500; }

/* Pills */
.pill { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
.p-success { background: #d1fae5; color: #065f46; }
.p-warning { background: #fef3c7; color: #92400e; }

.ref-cell { font-family: var(--mono); font-size: 11px; color: var(--muted); }
.date-cell { font-size: 12px; color: var(--text-2); }
.date-cell small { font-size: 10px; color: var(--muted); display: block; }

/* ── Flash ── */
.flash {
    padding: 10px 14px; border-radius: var(--r-sm); border: 1px solid;
    font-size: 13px; font-weight: 500; margin-bottom: 18px;
    display: flex; align-items: center; gap: 8px;
}
.flash-success { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; }
.flash-danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }

/* Empty */
.empty-state { padding: 48px 20px; text-align: center; }
.empty-state .ico { font-size: 38px; display: block; margin-bottom: 10px; opacity: .35; }
.empty-state p { font-size: 14px; color: var(--muted); }

/* Pagination */
.pagination-wrap { display: flex; justify-content: center; padding: 14px 0 2px; }

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    /* La payout-bar sticky doit rester sous la topbar */
    .payout-bar { top: var(--top-h); }
}
@media (max-width: 640px) {
    .kpi-row { grid-template-columns: 1fr; }
    .page-wrap { padding: 14px 12px 40px; }
    .payout-bar { flex-direction: column; align-items: stretch; flex-wrap: nowrap; }
    .payout-input { min-width: unset; }
    .tbl thead th:nth-child(4),
    .tbl tbody td:nth-child(4) { display: none; }
    .tbl thead th:nth-child(7),
    .tbl tbody td:nth-child(7) { display: none; }
    .kpi-card { padding: 16px 18px; gap: 12px; }
    .kpi-val { font-size: 22px; }
    .kpi-ico { width: 42px; height: 42px; font-size: 20px; }
}
@media (max-width: 480px) {
    .filter-bar { flex-direction: column; align-items: stretch; }
    .filter-select { min-width: unset; width: 100%; }
}
</style>
@endpush

@section('content')

@php
    $devise   = $devise ?? ($shop->currency ?? 'GNF');
    $init     = fn(string $n): string => strtoupper(substr(explode(' ',$n)[0],0,1))
                                       . strtoupper(substr(explode(' ',$n)[1]??'X',0,1));
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
                <a href="{{ route('boutique.payments.index') }}" class="sb-item">
                    <span class="ico">💳</span> Paiements
                </a>
                <a href="{{ route('boutique.commissions.index') }}" class="sb-item active">
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
            <div class="tb-title">💸 Commissions livreurs</div>
            <div class="tb-sub">{{ $shop->name }} · <span style="font-family:var(--mono);font-size:10px;background:var(--brand-mlt);color:var(--brand-dk);padding:1px 7px;border-radius:10px;border:1px solid var(--brand-lt)">{{ $devise }}</span></div>
        </div>
    </div>

<div class="page-wrap">

    {{-- ── Header ── --}}
    <div class="page-hd">
        <div>
            <h1 class="page-title">💸 Commissions livreurs</h1>
            <p class="page-sub">
                {{ $shop->name }}
                &nbsp;·&nbsp;
                <span class="devise-badge">💱 {{ $devise }}</span>
            </p>
        </div>
    </div>

    {{-- ── KPI : En attente + Payé ── --}}
    <div class="kpi-row">

        {{-- En attente — fond ambré sombre --}}
        <div class="kpi-card pending">
            <div class="kpi-ico">⏳</div>
            <div>
                <div class="kpi-lbl">Total en attente</div>
                {{-- Devise dynamique de la boutique --}}
                <div class="kpi-val">{{ number_format($totalPending, 0, ',', ' ') }}</div>
                <div class="kpi-unit">{{ $devise }}</div>
            </div>
        </div>

        {{-- Payé — fond vert sombre --}}
        <div class="kpi-card paid">
            <div class="kpi-ico">✅</div>
            <div>
                <div class="kpi-lbl">Total payé</div>
                {{-- Devise dynamique de la boutique --}}
                <div class="kpi-val">{{ number_format($totalPaid, 0, ',', ' ') }}</div>
                <div class="kpi-unit">{{ $devise }}</div>
            </div>
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

    {{-- ── Filtre statut ── --}}
    <div class="filter-bar">
        <span style="font-size:12.5px;font-weight:600;color:var(--text-2)">Afficher :</span>
        <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="en_attente" {{ $status === 'en_attente' ? 'selected' : '' }}>
                    ⏳ En attente
                </option>
                <option value="payé" {{ $status === 'payé' ? 'selected' : '' }}>
                    ✅ Payées
                </option>
            </select>
        </form>
        <span style="font-size:11px;color:var(--muted);margin-left:auto">
            {{ $commissions->total() }} commission(s)
        </span>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         CAS A : Commissions EN ATTENTE → formulaire de paiement
    ════════════════════════════════════════════════════════════ --}}
    @if($status === 'en_attente' && $commissions->count())

    {{-- ── Guide étapes ── --}}
    <div style="background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);border-radius:14px;padding:18px 22px;margin-bottom:20px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
        <div style="font-size:18px;flex-shrink:0;">💡</div>
        <div style="flex:1;min-width:200px;">
            <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:8px;">Comment payer un livreur ?</div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:7px;background:rgba(255,255,255,.08);border-radius:10px;padding:7px 12px;">
                    <span style="width:22px;height:22px;background:var(--warning);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;">1</span>
                    <span style="font-size:12px;color:rgba(255,255,255,.8);font-weight:600;">Entrez le montant à payer</span>
                </div>
                <div style="display:flex;align-items:center;gap:7px;background:rgba(255,255,255,.08);border-radius:10px;padding:7px 12px;">
                    <span style="width:22px;height:22px;background:var(--warning);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;">2</span>
                    <span style="font-size:12px;color:rgba(255,255,255,.8);font-weight:600;">Cochez les lignes à payer</span>
                </div>
                <div style="display:flex;align-items:center;gap:7px;background:rgba(255,255,255,.08);border-radius:10px;padding:7px 12px;">
                    <span style="width:22px;height:22px;background:var(--warning);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;">3</span>
                    <span style="font-size:12px;color:rgba(255,255,255,.8);font-weight:600;">Ajoutez une référence (optionnel)</span>
                </div>
                <div style="display:flex;align-items:center;gap:7px;background:rgba(16,185,129,.2);border-radius:10px;padding:7px 12px;border:1px solid rgba(16,185,129,.3);">
                    <span style="width:22px;height:22px;background:var(--brand);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;">4</span>
                    <span style="font-size:12px;color:#6ee7b7;font-weight:600;">Cliquez "Marquer comme payées"</span>
                </div>
            </div>
        </div>
    </div>

    <form id="payForm" action="{{ route('boutique.commissions.pay') }}" method="POST">
        @csrf

        {{-- ── Table des commissions ── --}}
        <div class="comm-card">
            <div class="comm-card-hd">
                <span class="comm-card-title">⏳ Commissions en attente de paiement</span>
                <label style="display:flex;align-items:center;gap:7px;font-size:12px;font-weight:600;color:var(--muted);cursor:pointer">
                    <input type="checkbox" id="checkAll"> Tout sélectionner
                </label>
            </div>

            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:36px"></th>
                        <th>Réf cmd</th>
                        <th>Livreur</th>
                        <th>Destination</th>
                        <th style="background:#fef9ec;color:#92400e;">💰 Montant à payer</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commissions as $c)
                    @php
                        $lv   = $c->livreur;
                        $linit = $lv ? $init($lv->name) : 'LV';
                        $dest = $c->order?->delivery_destination ?: $c->order?->client?->address ?: null;
                    @endphp
                    <tr class="comm-row" data-id="{{ $c->id }}">

                        {{-- Checkbox --}}
                        <td>
                            <input type="checkbox" name="ids[]" value="{{ $c->id }}" class="rowCheckbox">
                        </td>

                        {{-- Réf commande --}}
                        <td><span class="ref-cell">#{{ $c->order_id }}</span></td>

                        {{-- Livreur --}}
                        <td>
                            <div class="lv-chip">
                                <div class="lv-av">{{ $linit }}</div>
                                {{ $lv?->name ?? '—' }}
                            </div>
                            @if($lv?->phone)
                            <div style="font-size:10.5px;color:var(--muted);margin-top:3px;">📞 {{ $lv->phone }}</div>
                            @endif
                        </td>

                        {{-- Destination --}}
                        <td>
                            @if($dest)
                                <span style="font-size:12px;color:var(--text-2)">📍 {{ $dest }}</span>
                            @else
                                <span style="color:var(--muted);font-size:12px">—</span>
                            @endif
                        </td>

                        {{-- Montant éditable — mis en avant --}}
                        <td style="background:#fffbeb;">
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                <div style="font-size:10px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.4px;">Saisir le montant ✏️</div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <input type="number"
                                           name="amounts[{{ $c->id }}]"
                                           class="comm-amount-input"
                                           value="{{ $c->amount ?: '' }}"
                                           min="0"
                                           placeholder="Ex: 50000"
                                           style="width:120px;padding:8px 12px;border:2px solid #f59e0b;border-radius:8px;font-size:14px;font-weight:800;font-family:var(--mono);color:#92400e;background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;"
                                           onfocus="this.style.borderColor='#d97706';this.style.boxShadow='0 0 0 3px rgba(245,158,11,.2)'"
                                           onblur="this.style.borderColor='#f59e0b';this.style.boxShadow='none'">
                                    <span style="font-size:11px;font-weight:700;color:#92400e;white-space:nowrap;">{{ $devise }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Date --}}
                        <td>
                            <div class="date-cell">
                                {{ $c->created_at->format('d/m/Y') }}
                                <small>{{ $c->created_at->format('H:i') }}</small>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrap">
                {{ $commissions->withQueryString()->links() }}
            </div>
        </div>

        {{-- ── Barre de paiement en bas ── --}}
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:16px 20px;margin-top:16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;box-shadow:var(--shadow);" id="payoutBar">
            <span class="payout-count" id="selectionCount" style="flex-shrink:0;">0 sélectionné</span>
            <input type="text" name="payout_ref" class="payout-input"
                   placeholder="📎 Référence paiement (ex: Orange Money 001)">
            <input type="text" name="payout_note" class="payout-input"
                   placeholder="📝 Note interne (optionnel)" style="flex:2">
            <button type="submit" id="markPaidBtn" class="btn btn-primary" disabled style="flex-shrink:0;padding:10px 20px;font-size:13.5px;">
                ✅ Marquer comme PAYÉES
            </button>
        </div>

    </form>

    {{-- ════════════════════════════════════════════════════════════
         CAS B : Commissions PAYÉES → lecture seule
    ════════════════════════════════════════════════════════════ --}}
    @else

    <div class="comm-card">
        <div class="comm-card-hd">
            <span class="comm-card-title">
                {{ $status === 'en_attente' ? 'Commissions en attente' : 'Commissions payées' }}
            </span>
        </div>

        @if($commissions->isEmpty())
        <div class="empty-state">
            <span class="ico">💸</span>
            <p>Aucune commission {{ $status === 'payé' ? 'payée' : 'en attente' }} pour le moment.</p>
        </div>
        @else
        <table class="tbl">
            <thead>
                <tr>
                    <th>Réf cmd</th>
                    <th>Livreur</th>
                    <th>Destination</th>
                    <th>Commission</th>
                    <th>Statut</th>
                    <th>Référence paiement</th>
                    <th>Payée le</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commissions as $c)
                @php
                    $lv    = $c->livreur;
                    $linit = $lv ? $init($lv->name) : 'LV';
                    $dest  = $c->order?->delivery_destination
                          ?: $c->order?->client?->address
                          ?: null;
                @endphp
                <tr>
                    <td><span class="ref-cell">#{{ $c->order_id }}</span></td>

                    <td>
                        <div class="lv-chip">
                            <div class="lv-av">{{ $linit }}</div>
                            {{ $lv?->name ?? '—' }}
                        </div>
                    </td>

                    {{-- Destination --}}
                    <td>
                        @if($dest)
                            <span style="font-size:12px;color:var(--text-2)">📍 {{ $dest }}</span>
                        @else
                            <span style="color:var(--muted);font-size:12px">—</span>
                        @endif
                    </td>

                    {{-- Commission avec devise --}}
                    <td>
                        <div class="amount">
                            {{ number_format($c->amount, 0, ',', ' ') }}
                            <small>{{ $devise }}</small>
                        </div>
                    </td>

                    <td>
                        @if($c->status === 'en_attente')
                            <span class="pill p-warning">⏳ En attente</span>
                        @else
                            <span class="pill p-success">✓ Payée</span>
                        @endif
                    </td>

                    <td>
                        @if($c->payout_ref)
                            <span class="ref-cell">{{ $c->payout_ref }}</span>
                        @else
                            <span style="color:var(--muted);font-size:12px">—</span>
                        @endif
                    </td>

                    <td>
                        <div class="date-cell">
                            {{ optional($c->paid_at)->format('d/m/Y') ?? '—' }}
                            @if($c->paid_at)
                            <small>{{ $c->paid_at->format('H:i') }}</small>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrap">
            {{ $commissions->withQueryString()->links() }}
        </div>
        @endif
    </div>

    @endif

</div>{{-- /page-wrap --}}
</main>
</div>{{-- /dash-wrap --}}

@endsection

@push('scripts')
<script>
/* ══ SIDEBAR ══ */
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

(function initSidebar() {
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
})();

document.addEventListener('DOMContentLoaded', () => {

    const checkAll      = document.getElementById('checkAll');
    const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
    const markPaidBtn   = document.getElementById('markPaidBtn');
    const payoutBar     = document.getElementById('payoutBar');
    const selCount      = document.getElementById('selectionCount');
    const payForm       = document.getElementById('payForm');

    /* ── Mettre à jour l'état du bouton + compteur ── */
    function updateState() {
        const checked = Array.from(rowCheckboxes).filter(cb => cb.checked);
        const n       = checked.length;

        if (markPaidBtn) markPaidBtn.disabled = n === 0;

        if (selCount) {
            selCount.textContent = n > 0 ? `${n} sélectionné${n > 1 ? 's' : ''}` : '0 sélectionné';
            selCount.className   = 'payout-count' + (n > 0 ? ' active' : '');
        }

        /* Mettre en valeur la barre sticky quand une sélection est active */
        if (payoutBar) {
            payoutBar.classList.toggle('has-selection', n > 0);
        }

        /* Colorier les lignes sélectionnées */
        document.querySelectorAll('.comm-row').forEach(row => {
            const cb = row.querySelector('.rowCheckbox');
            row.classList.toggle('selected', cb?.checked ?? false);
        });
    }

    /* ── Sélectionner / désélectionner tout ── */
    checkAll?.addEventListener('change', e => {
        rowCheckboxes.forEach(cb => cb.checked = e.target.checked);
        updateState();
    });

    /* ── Écouter chaque checkbox ── */
    rowCheckboxes.forEach(cb => cb.addEventListener('change', () => {
        /* Synchroniser la checkbox "tout sélectionner" */
        if (checkAll) {
            checkAll.checked = Array.from(rowCheckboxes).every(c => c.checked);
        }
        updateState();
    }));

    /* ── Validation avant envoi ── */
    payForm?.addEventListener('submit', e => {
        const checked = Array.from(rowCheckboxes).filter(cb => cb.checked);
        if (checked.length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins une commission à marquer comme payée.');
            return;
        }
        /* Désactiver le bouton pour éviter le double-submit */
        if (markPaidBtn) {
            markPaidBtn.disabled   = true;
            markPaidBtn.textContent = '⏳ Traitement…';
        }
    });

    /* Init */
    updateState();
});
</script>
@endpush