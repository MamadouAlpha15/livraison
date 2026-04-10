{{--
    resources/views/vendeur/employees/index.blade.php
    Route   : GET /boutique/employees → Vendeur\EmployeeController@index → boutique.employees.index
    Variables :
      $employees → LengthAwarePaginator<User>
--}}

@extends('layouts.app')
@section('title', 'Équipe · ' . (auth()->user()->shop?->name ?? 'Boutique'))
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
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
    --danger:     #ef4444; --danger-lt: #fef2f2;
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r: 14px; --r-sm: 9px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.06);
    --shadow:     0 4px 16px rgba(0,0,0,.08);
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

/* ══ MAIN ══ */
.main { display: flex; flex-direction: column; min-width: 0; }
.topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 22px; height: var(--top-h); display: flex; align-items: center; gap: 12px; position: sticky; top: 0; z-index: 30; box-shadow: var(--shadow-sm); }
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); font-size: 20px; }
.tb-info { flex: 1; min-width: 0; }
.tb-title { font-size: 14px; font-weight: 700; color: var(--text); }
.tb-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* ══ CONTENU ══ */
.content { padding: 22px 22px 60px; }

/* Flash */
.flash { padding: 10px 14px; border-radius: var(--r-sm); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
.flash-success { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; }
.flash-danger  { background: var(--danger-lt); border-color: #fca5a5; color: #991b1b; }

/* ══ PAGE HEADER ══ */
.page-hd { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 22px; flex-wrap: wrap; }
.page-title { font-size: 20px; font-weight: 800; color: var(--text); letter-spacing: -.4px; margin: 0 0 3px; }
.page-sub   { font-size: 13px; color: var(--muted); margin: 0; }

/* ══ STATS ══ */
.stats-row { display: flex; gap: 12px; margin-bottom: 22px; flex-wrap: wrap; }
.stat-chip { background: var(--surface); border: 1px solid var(--border); border-left: 3px solid var(--sc-color, var(--brand)); border-radius: var(--r-sm); padding: 12px 16px; display: flex; align-items: center; gap: 10px; box-shadow: var(--shadow-sm); flex: 1; min-width: 0; }
.stat-ico { font-size: 20px; flex-shrink: 0; }
.stat-val { font-size: 22px; font-weight: 800; font-family: var(--mono); color: var(--text); line-height: 1; }
.stat-lbl { font-size: 10.5px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }

/* ══ RÔLE BADGES ══ */
.role-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700; padding: 3px 10px;
    border-radius: 20px; white-space: nowrap;
}
.role-vendeur  { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.role-livreur  { background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); }
.role-employe  { background: #f5f3ff; color: #5b21b6; border: 1px solid #ddd6fe; }

/* ══ TABLE ══ */
.emp-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); }
.emp-card-hd { padding: 13px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--bg); }
.emp-card-title { font-size: 13px; font-weight: 700; color: var(--text); }

.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl thead th { padding: 11px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; background: var(--bg); border-bottom: 1px solid var(--border); white-space: nowrap; }
.tbl tbody td { padding: 13px 16px; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; }

.emp-av { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.emp-name  { font-size: 13px; font-weight: 600; color: var(--text); }
.emp-email { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* Boutons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: var(--r-sm); font-size: 12px; font-weight: 600; font-family: var(--font); border: 1px solid var(--border-dk); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-sm { padding: 5px 10px; font-size: 11.5px; }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(16,185,129,.25); }
.btn-primary:hover { background: var(--brand-dk); color: #fff; }
.btn-danger { background: var(--danger-lt); border-color: #fca5a5; color: #991b1b; }
.btn-danger:hover { background: #fee2e2; }

/* Empty */
.empty-state { padding: 56px 20px; text-align: center; }
.empty-ico { font-size: 44px; display: block; margin-bottom: 12px; opacity: .3; }
.empty-txt { font-size: 14px; color: var(--muted); }

/* Pagination */
.pagination-wrap { display: flex; justify-content: center; padding: 14px 0 2px; }

/* ══ CARTES MOBILES ══ */
.emp-table  { display: block; }
.emp-mobile { display: none; flex-direction: column; gap: 10px; margin-top: 14px; }

.m-emp-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); }
.m-emp-hd   { padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--bg); border-bottom: 1px solid var(--border); }
.m-emp-body { padding: 12px 14px; display: flex; flex-direction: column; gap: 7px; }
.m-emp-foot { padding: 10px 14px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; }
.m-row { display: flex; align-items: center; justify-content: space-between; }
.m-lbl { font-size: 10.5px; color: var(--muted); font-weight: 600; text-transform: uppercase; }

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .content { padding: 14px; }
    .stats-row { gap: 8px; }
    .stat-chip { padding: 10px 12px; }
    .stat-val  { font-size: 18px; }
}
@media (max-width: 640px) {
    .emp-table  { display: none; }
    .emp-mobile { display: flex; }
    .stats-row  { gap: 6px; }
    .stat-chip  { padding: 8px 10px; flex: 1; }
    .stat-val   { font-size: 16px; }
    .stat-lbl   { font-size: 9.5px; }
    .page-hd    { flex-direction: column; align-items: stretch; }
    .page-hd .btn { justify-content: center; }
    .content { padding: 10px; }
}
@media (max-width: 380px) {
    .stats-row { flex-wrap: wrap; }
    .stat-chip { min-width: calc(50% - 4px); flex: none; }
}
</style>
@endpush

@section('content')

@php
    $shop     = auth()->user()->shop ?? auth()->user()->assignedShop ?? null;
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $pendingCount = $shop ? $shop->orders()->whereIn('status',['pending','en attente','en_attente','confirmée','processing'])->count() : 0;

    $avColors  = ['#059669','#2563eb','#d97706','#7c3aed','#0891b2','#e11d48'];
    $initiales = function(string $name): string {
        $p = explode(' ', $name);
        return strtoupper(substr($p[0],0,1)) . strtoupper(substr($p[1] ?? 'X',0,1));
    };

    $totalEmp   = $employees->total();
    $vendeurs   = $employees->getCollection()->where('role_in_shop','vendeur')->count();
    $livreurs   = $employees->getCollection()->where('role_in_shop','livreur')->count();
    $employes   = $employees->getCollection()->where('role_in_shop','employe')->count();

    $roleConfig = [
        'vendeur' => ['label' => 'Vendeur',  'class' => 'role-vendeur', 'ico' => '🏪'],
        'livreur' => ['label' => 'Livreur',  'class' => 'role-livreur', 'ico' => '🚴'],
        'employe' => ['label' => 'Employé',  'class' => 'role-employe', 'ico' => '🧑‍💼'],
    ];
@endphp

<div class="dash-wrap">

{{-- ══════ SIDEBAR ══════ --}}
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
            <div class="sb-logo-icon">🛍️</div>
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
        <a href="{{ route('boutique.employees.index') }}" class="sb-item active">
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
            <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                <span class="ico">💰</span>
                Finances & Rapports
                <span class="sb-arrow">▶</span>
            </button>
            <div class="sb-sub">
                <a href="{{ route('boutique.payments.index') }}" class="sb-item">
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

    {{-- Topbar ── --}}
    <div class="topbar">
        <button class="btn-hamburger" id="btnMenu" aria-label="Menu">☰</button>
        <div class="tb-info">
            <div class="tb-title">🧑‍💼 Équipe</div>
            <div class="tb-sub">{{ $shop->name ?? 'Boutique' }} · {{ $totalEmp }} membre(s)</div>
        </div>
        <a href="{{ route('boutique.employees.create') }}" class="btn btn-primary btn-sm">
            ➕ Ajouter un employé
        </a>
    </div>

    <div class="content">

        {{-- Flash ── --}}
        @foreach(['success','danger'] as $type)
            @if(session($type))
            <div class="flash flash-{{ $type }}">
                <span>{{ $type === 'success' ? '✓' : '⚠' }}</span>
                {{ session($type) }}
            </div>
            @endif
        @endforeach

        {{-- ── STATS ── --}}
        <div class="stats-row">
            <div class="stat-chip" style="--sc-color:#3b82f6">
                <span class="stat-ico">👤</span>
                <div>
                    <div class="stat-val">{{ $totalEmp }}</div>
                    <div class="stat-lbl">Total</div>
                </div>
            </div>
            <div class="stat-chip" style="--sc-color:#1e40af">
                <span class="stat-ico">🏪</span>
                <div>
                    <div class="stat-val">{{ $vendeurs }}</div>
                    <div class="stat-lbl">Vendeurs</div>
                </div>
            </div>
            <div class="stat-chip" style="--sc-color:var(--brand)">
                <span class="stat-ico">🚴</span>
                <div>
                    <div class="stat-val">{{ $livreurs }}</div>
                    <div class="stat-lbl">Livreurs</div>
                </div>
            </div>
            <div class="stat-chip" style="--sc-color:#7c3aed">
                <span class="stat-ico">🧑‍💼</span>
                <div>
                    <div class="stat-val">{{ $employes }}</div>
                    <div class="stat-lbl">Employés</div>
                </div>
            </div>
        </div>

        {{-- ── TABLE DESKTOP (> 640px) ── --}}
        <div class="emp-card emp-table">
            <div class="emp-card-hd">
                <span class="emp-card-title">Liste des membres</span>
                <span style="font-size:11px;color:var(--muted)">
                    Page {{ $employees->currentPage() }} / {{ $employees->lastPage() }}
                    · {{ $employees->total() }} membre(s)
                </span>
            </div>

            @if($employees->isEmpty())
            <div class="empty-state">
                <span class="empty-ico">👥</span>
                <div class="empty-txt">Aucun employé pour le moment.</div>
                <a href="{{ route('boutique.employees.create') }}" class="btn btn-primary" style="display:inline-flex;margin-top:16px">
                    ➕ Ajouter le premier employé
                </a>
            </div>
            @else
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Membre</th>
                        <th>Rôle</th>
                        <th>Membre depuis</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $i => $emp)
                    @php
                        $init  = $initiales($emp->name);
                        $color = $avColors[$i % count($avColors)];
                        $role  = $roleConfig[$emp->role_in_shop] ?? ['label' => ucfirst($emp->role_in_shop), 'class' => 'role-employe', 'ico' => '👤'];
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div class="emp-av" style="background:{{ $color }}">{{ $init }}</div>
                                <div>
                                    <div class="emp-name">{{ $emp->name }}</div>
                                    <div class="emp-email">{{ $emp->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge {{ $role['class'] }}">
                                {{ $role['ico'] }} {{ $role['label'] }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--text-2)">
                            {{ $emp->created_at->diffForHumans() }}
                        </td>
                        <td>
                            <form method="POST"
                                  action="{{ route('boutique.employees.destroy', $emp) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($emp->name) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">🗑️ Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination-wrap">{{ $employees->links() }}</div>
            @endif
        </div>

        {{-- ── CARTES MOBILES (< 640px) ── --}}
        <div class="emp-mobile">
            @forelse($employees as $i => $emp)
            @php
                $init  = $initiales($emp->name);
                $color = $avColors[$i % count($avColors)];
                $role  = $roleConfig[$emp->role_in_shop] ?? ['label' => ucfirst($emp->role_in_shop), 'class' => 'role-employe', 'ico' => '👤'];
            @endphp
            <div class="m-emp-card">
                <div class="m-emp-hd">
                    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                        <div class="emp-av" style="background:{{ $color }};width:40px;height:40px;font-size:13px;flex-shrink:0">{{ $init }}</div>
                        <div style="min-width:0">
                            <div class="emp-name">{{ $emp->name }}</div>
                            <div class="emp-email" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $emp->email }}</div>
                        </div>
                    </div>
                    <span class="role-badge {{ $role['class'] }}" style="flex-shrink:0">
                        {{ $role['ico'] }} {{ $role['label'] }}
                    </span>
                </div>
                <div class="m-emp-body">
                    <div class="m-row">
                        <span class="m-lbl">Membre depuis</span>
                        <span style="font-size:12px;color:var(--text-2);font-weight:500">{{ $emp->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="m-emp-foot">
                    <form method="POST"
                          action="{{ route('boutique.employees.destroy', $emp) }}"
                          onsubmit="return confirm('Supprimer {{ addslashes($emp->name) }} ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Supprimer</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <span class="empty-ico">👥</span>
                <div class="empty-txt">Aucun employé pour le moment.</div>
                <a href="{{ route('boutique.employees.create') }}" class="btn btn-primary" style="display:inline-flex;margin-top:16px">
                    ➕ Ajouter le premier employé
                </a>
            </div>
            @endforelse
            <div class="pagination-wrap">{{ $employees->links() }}</div>
        </div>

    </div>{{-- /content --}}
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