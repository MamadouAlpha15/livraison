{{--
    resources/views/boutique/clients/index.blade.php
    Route  : GET /boutique/clients → Boutique\ClientController@index → name('boutique.clients.index')
    Variables :
      $clients        → LengthAwarePaginator (user_id, total_depense, nb_commandes, derniere_cmd)
      $topClientIds   → array des IDs top 5 ce mois
      $search         → string|null
      $sortBy         → string
      $totalClients   → int
      $nouveauxCeMois → int
      $caTotal        → float
      $shop           → Shop
--}}

@extends('layouts.app')

@section('title', 'Clients · ' . $shop->name)

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
    --r:          14px; --r-sm: 9px;
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
.sb-close {
    display: none; position: absolute; top: 14px; right: 12px;
    width: 30px; height: 30px; border-radius: 8px;
    background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.6); font-size: 18px; cursor: pointer;
    align-items: center; justify-content: center; transition: background .15s, color .15s;
}
.sb-close:hover { background: rgba(239,68,68,.18); border-color: rgba(239,68,68,.3); color: #fca5a5; }
@media (max-width: 900px) { .sb-close { display: flex; } }

.sb-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.sb-logo-icon {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--brand), #059669);
    border-radius: 9px; display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(16,185,129,.35);
}
.sb-shop-name { font-size: 14px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 10.5px; color: var(--sb-txt); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: var(--brand); flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px var(--brand); }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }

.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; overflow: visible; }
.sb-section { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.2px; color: rgba(255,255,255,.2); padding: 12px 8px 4px; font-weight: 600; }
.sb-item {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 10px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 500;
    color: var(--sb-txt); text-decoration: none;
    transition: background .15s, color .15s; position: relative;
}
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.8); }
.sb-item.active { background: var(--sb-act); color: var(--sb-txt-act); }
.sb-item.active::before {
    content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
    width: 3px; height: 18px; background: var(--brand); border-radius: 0 3px 3px 0;
}
.sb-item .ico { font-size: 14px; width: 20px; text-align: center; flex-shrink: 0; }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); min-width: 20px; text-align: center; }
.sb-badge.warn { background: #f59e0b; }

.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 10px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 500; color: var(--sb-txt); cursor: pointer;
    transition: background .15s, color .15s;
    user-select: none; border: none; background: none;
    width: 100%; text-align: left; font-family: var(--font);
}
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
.sb-av { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--brand), #16a34a); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(16,185,129,.25); }
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

/* ── Page intérieure ── */
.page-wrap { padding: 22px 22px 60px; }

/* ── Header de page ── */
.page-hd {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 16px;
    margin-bottom: 24px; flex-wrap: wrap;
}
.page-title { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -.4px; margin: 0 0 4px; }
.page-sub   { font-size: 13px; color: var(--text-2); margin: 0; }

/* ── KPI mini ── */
.kpi-mini {
    display: flex; gap: 12px; flex-wrap: wrap;
    margin-bottom: 22px;
}
.kpi-chip {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 14px 18px;
    min-width: 150px;
    box-shadow: var(--shadow-sm);
    flex: 1;
}
.kpi-chip-lbl { font-size: 10.5px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
.kpi-chip-val { font-size: 22px; font-weight: 700; color: var(--text); font-family: var(--mono); letter-spacing: -.5px; }
.kpi-chip-sub { font-size: 10px; color: var(--muted); margin-top: 2px; }

/* ── Barre recherche + tri ── */
.toolbar {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 18px; flex-wrap: wrap;
}
.search-box {
    flex: 1; min-width: 200px;
    display: flex; align-items: center; gap: 8px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r-sm); padding: 9px 14px;
    box-shadow: var(--shadow-sm);
}
.search-box input {
    flex: 1; border: none; outline: none;
    font-size: 13px; font-family: var(--font); color: var(--text);
    background: transparent;
}
.search-box input::placeholder { color: var(--muted); }

.sort-select {
    padding: 9px 14px; border-radius: var(--r-sm);
    border: 1px solid var(--border-dk); background: var(--surface);
    font-size: 12px; font-weight: 600; font-family: var(--font);
    color: var(--text-2); cursor: pointer; outline: none;
    box-shadow: var(--shadow-sm);
}

/* ── Table clients ── */
.clients-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 22px;
}

.tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
.tbl thead th {
    padding: 12px 16px;
    text-align: left; font-size: 10.5px; font-weight: 700;
    color: var(--muted); text-transform: uppercase; letter-spacing: .6px;
    background: var(--bg); border-bottom: 1px solid var(--border);
}
.tbl tbody td {
    padding: 13px 16px;
    border-bottom: 1px solid #f3f6f4;
    vertical-align: middle;
}
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; cursor: pointer; }

/* Colonne client : avatar + nom + email */
.client-cell { display: flex; align-items: center; gap: 12px; }
.c-av {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.c-name { font-size: 13.5px; font-weight: 600; color: var(--text); }
.c-email { font-size: 11px; color: var(--muted); margin-top: 1px; }
.c-phone { font-size: 11px; color: var(--muted); }

/* Montant */
.c-amount { font-family: var(--mono); font-weight: 700; color: var(--text); font-size: 13px; }
.c-amount-sub { font-size: 10px; color: var(--muted); }

/* Badge top client */
.badge-top {
    display: inline-flex; align-items: center; gap: 4px;
    background: #fef3c7; color: #92400e;
    border: 1px solid #fde68a;
    font-size: 10px; font-weight: 700;
    padding: 2px 8px; border-radius: 20px;
}

/* Badge nb commandes */
.badge-cmd {
    display: inline-flex; align-items: center;
    background: var(--brand-mlt); color: var(--brand-dk);
    border: 1px solid var(--brand-lt);
    font-size: 11px; font-weight: 700; font-family: var(--mono);
    padding: 3px 10px; border-radius: 20px;
}

/* Bouton voir fiche */
.btn-fiche {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 6px 12px; border-radius: var(--r-sm);
    font-size: 11.5px; font-weight: 600; font-family: var(--font);
    border: 1px solid var(--border-dk); background: var(--surface);
    color: var(--text-2); text-decoration: none; transition: all .15s;
}
.btn-fiche:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-mlt); }

/* ── Pagination ── */
.pagination-wrap { display: flex; justify-content: center; margin-top: 8px; }

/* ── Couleurs avatars ── */
.av-green  { background: #059669; }
.av-blue   { background: #2563eb; }
.av-amber  { background: #d97706; }
.av-purple { background: #7c3aed; }
.av-teal   { background: #0891b2; }
.av-rose   { background: #e11d48; }

/* ── TOP CLIENTS PODIUM ─────────────────────────────────────────
   Card avec 5 colonnes représentant le classement du mois.
   Chaque colonne : médaille + avatar + nom + barre + montant.
   ─────────────────────────────────────────────────────────────── */
.top-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 22px;
}
.top-card-hd {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.top-card-title {
    font-size: 13px; font-weight: 700; color: var(--text);
    display: flex; align-items: center; gap: 6px;
}
.top-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0;
}
.top-item {
    display: flex; flex-direction: column;
    align-items: center; gap: 6px;
    padding: 20px 12px 18px;
    border-right: 1px solid var(--border);
    text-decoration: none;
    transition: background .15s;
}
.top-item:last-child { border-right: none; }
.top-item:hover { background: var(--bg); }

/* Médaille en grand */
.top-medal { font-size: 22px; line-height: 1; }

/* Avatar circulaire */
.top-av {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}

/* Nom du client */
.top-name {
    font-size: 12px; font-weight: 700; color: var(--text);
    text-align: center; line-height: 1.2;
}

/* Barre de progression */
.top-bar-track {
    width: 100%; height: 5px;
    background: #eef1f0; border-radius: 3px; overflow: hidden;
}
.top-bar-fill {
    height: 100%; border-radius: 3px;
    transition: width 1s cubic-bezier(.23,1,.32,1);
}

/* Montant et nb commandes */
.top-amount {
    font-size: 11px; font-weight: 700; font-family: var(--mono);
    color: var(--text); text-align: center;
}
.top-cmds {
    font-size: 10px; color: var(--muted); text-align: center;
}

/* ══════════════════════════════════════════
   CARTES MOBILES — remplacent la table
   sur les petits écrans
══════════════════════════════════════════ */
.clients-table  { display: block; }   /* table visible par défaut */
.clients-mobile { display: none; }    /* cartes cachées par défaut */

.m-client-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 10px;
    transition: box-shadow .2s;
}
.m-client-card:hover { box-shadow: var(--shadow); }

.m-client-hd {
    padding: 12px 14px;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    justify-content: space-between; gap: 8px;
}
.m-client-info { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
.m-client-body { padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; }
.m-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.m-lbl { font-size: 10.5px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
.m-val { font-size: 13px; font-weight: 600; color: var(--text); }
.m-client-foot {
    padding: 10px 14px;
    border-top: 1px solid var(--border);
    display: flex; justify-content: flex-end;
}

/* ══ RESPONSIVE ══ */

/* Sidebar cachée sur mobile */
@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .page-wrap { padding: 16px; }
    .kpi-chip { min-width: 100px; padding: 10px 12px; }
    .kpi-chip-val { font-size: 18px; }
    .top-grid { grid-template-columns: repeat(3, 1fr); }
}

/* Tablette : basculer sur cartes à 700px */
@media (max-width: 700px) {
    .clients-table  { display: none; }
    .clients-mobile { display: block; }
    .top-grid { grid-template-columns: repeat(3, 1fr); }
    .toolbar { gap: 8px; }
}

/* Mobile */
@media (max-width: 520px) {
    .page-wrap { padding: 12px 10px; }
    .kpi-mini { gap: 8px; }
    .kpi-chip { min-width: 0; padding: 9px 10px; flex: 1; }
    .kpi-chip-val { font-size: 15px; }
    .kpi-chip-lbl { font-size: 9.5px; }
    .top-grid { grid-template-columns: repeat(2, 1fr); }
    .top-item { padding: 14px 8px 12px; }
    .top-av { width: 36px; height: 36px; font-size: 12px; }
    .toolbar { flex-direction: column; align-items: stretch; }
    .toolbar .sort-select { width: 100%; }
    .search-box { min-width: 0; }
    .page-title { font-size: 18px; }
    .page-hd { margin-bottom: 16px; }
}

@media (max-width: 380px) {
    .top-grid { grid-template-columns: repeat(2, 1fr); }
    .kpi-mini { flex-direction: column; }
}
</style>
@endpush

@section('content')

@php
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $pendingCount = $shop->orders()->whereIn('status',['pending','en attente','en_attente','confirmée','processing'])->count();
@endphp

<div class="dash-wrap">

{{-- ══════ SIDEBAR ══════ --}}
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
            <div class="sb-logo-icon">🛍️</div>
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
        <a href="{{ route('boutique.clients.index') }}" class="sb-item active">
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
            <div class="sb-av">{{ $initials }}</div>
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
            <div class="tb-title">👥 Clients</div>
            <div class="tb-sub">{{ $shop->name }} · {{ $totalClients }} client(s)</div>
        </div>
    </div>

<div class="page-wrap">

    {{-- ── En-tête de page ── --}}
    <div class="page-hd">
        <div>
            <h1 class="page-title">👥 Clients</h1>
            <p class="page-sub">Tous les clients qui ont commandé dans {{ $shop->name }}</p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         KPI GLOBAUX : 3 chiffres clés en haut de page
         ════════════════════════════════════════════════════════════ --}}
    <div class="kpi-mini">

        {{-- Total clients distincts --}}
        <div class="kpi-chip">
            <div class="kpi-chip-lbl">Total clients</div>
            <div class="kpi-chip-val">{{ $totalClients }}</div>
            <div class="kpi-chip-sub">clients uniques</div>
        </div>

        {{-- Nouveaux ce mois --}}
        <div class="kpi-chip" style="border-top:3px solid #10b981">
            <div class="kpi-chip-lbl">Nouveaux ce mois</div>
            <div class="kpi-chip-val" style="color:var(--brand)">{{ $nouveauxCeMois }}</div>
            <div class="kpi-chip-sub">ont commandé ce mois</div>
        </div>

        {{-- CA total généré par les clients --}}
        <div class="kpi-chip" style="border-top:3px solid #3b82f6">
            <div class="kpi-chip-lbl">CA total généré</div>
            <div class="kpi-chip-val" style="color:#2563eb">{{ number_format($caTotal/1000, 0) }}k</div>
            <div class="kpi-chip-sub">{{ $shop->currency ?? 'GNF' }} cumulés</div>
        </div>

    </div>

    {{-- Flash messages --}}
    @foreach(['success','info','warning','danger'] as $type)
        @if(session($type))
        <div style="margin-bottom:16px;padding:10px 14px;font-size:12.5px;font-weight:500;border-radius:var(--r-sm);border:1px solid;
            background:{{ $type==='success'?'#ecfdf5':($type==='danger'?'#fef2f2':($type==='info'?'#eff6ff':'#fffbeb')) }};
            border-color:{{ $type==='success'?'#6ee7b7':($type==='danger'?'#fca5a5':($type==='info'?'#93c5fd':'#fcd34d')) }};
            color:{{ $type==='success'?'#065f46':($type==='danger'?'#991b1b':($type==='info'?'#1e40af':'#92400e')) }}">
            {{ session($type) }}
        </div>
        @endif
    @endforeach

    {{-- ════════════════════════════════════════════════════════════
         BARRE RECHERCHE + TRI
         ════════════════════════════════════════════════════════════ --}}
    <form method="GET" action="{{ route('boutique.clients.index') }}">
        <div class="toolbar">

            {{-- Recherche par nom / email / téléphone --}}
            <div class="search-box">
                <span style="font-size:15px">🔍</span>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Rechercher par nom, email, téléphone…"
                       autocomplete="off">
            </div>

            {{-- Tri --}}
            <select name="sort" class="sort-select" onchange="this.form.submit()">
                <option value="total_depense"  {{ $sortBy === 'total_depense'  ? 'selected' : '' }}>Trier : Plus dépensier</option>
                <option value="nb_commandes"   {{ $sortBy === 'nb_commandes'   ? 'selected' : '' }}>Trier : Plus de commandes</option>
                <option value="derniere_cmd"   {{ $sortBy === 'derniere_cmd'   ? 'selected' : '' }}>Trier : Dernière commande</option>
            </select>

            {{-- Bouton recherche --}}
            <button type="submit" style="padding:9px 18px;border-radius:var(--r-sm);background:var(--brand);color:#fff;border:none;font-size:12.5px;font-weight:600;font-family:var(--font);cursor:pointer">
                Rechercher
            </button>

            {{-- Reset --}}
            @if($search)
            <a href="{{ route('boutique.clients.index') }}"
               style="padding:9px 14px;border-radius:var(--r-sm);border:1px solid var(--border-dk);background:var(--surface);font-size:12px;font-weight:600;color:var(--text-2);text-decoration:none">
                ✕ Effacer
            </a>
            @endif

        </div>
    </form>

    {{-- ════════════════════════════════════════════════════════════
         TOP 5 CLIENTS DU MOIS
         Podium visuel des meilleurs clients ce mois.
         Affiché au-dessus de la table pour une visibilité immédiate.
         ════════════════════════════════════════════════════════════ --}}
    @php
        /* Calcul du top 5 clients du mois pour l'affichage podium */
        $topClientsMonth = $shop->orders()
            ->with('user')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->select('user_id',
                \Illuminate\Support\Facades\DB::raw('SUM(total) as total_mois'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as nb_cmd_mois')
            )
            ->groupBy('user_id')
            ->orderByDesc('total_mois')
            ->take(5)
            ->get();
        $maxTop = $topClientsMonth->max('total_mois') ?: 1;
        $avColors = ['av-green','av-blue','av-amber','av-purple','av-teal'];
        $medailles = ['🥇','🥈','🥉','4e','5e'];
        $medalColors = ['#f59e0b','#9ca3af','#b45309','#6b7280','#6b7280'];
    @endphp

    @if($topClientsMonth->isNotEmpty())
    <div class="top-card">
        <div class="top-card-hd">
            <div class="top-card-title">
                <span>🏆</span> Top clients — {{ now()->translatedFormat('F Y') }}
            </div>
            <span style="font-size:11px;color:var(--muted)">par montant dépensé ce mois</span>
        </div>
        <div class="top-grid">
            @foreach($topClientsMonth as $i => $item)
            @php
                $c     = $item->user;
                if (!$c) continue;
                $parts = explode(' ', $c->name ?? 'C L');
                $init  = strtoupper(substr($parts[0],0,1)).strtoupper(substr($parts[1]??'X',0,1));
                $col   = $avColors[$i % count($avColors)];
                $pct   = round(($item->total_mois / $maxTop) * 100);
            @endphp
            <a href="{{ route('boutique.clients.show', $c) }}" class="top-item">
                {{-- Médaille --}}
                <div class="top-medal" style="color:{{ $medalColors[$i] }}">
                    {{ $medailles[$i] }}
                </div>
                {{-- Avatar --}}
                <div class="top-av {{ $col }}">{{ $init }}</div>
                {{-- Nom --}}
                <div class="top-name">{{ Str::limit($c->name, 14) }}</div>
                {{-- Barre de progression --}}
                <div class="top-bar-track">
                    <div class="top-bar-fill" data-pct="{{ $pct }}" style="width:0%;background:{{ $medalColors[$i] }}"></div>
                </div>
                {{-- Montant --}}
                <div class="top-amount">{{ number_format($item->total_mois/1000,0) }}k {{ $shop->currency ?? 'GNF' }}</div>
                <div class="top-cmds">{{ $item->nb_cmd_mois }} cmd</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         TABLE CLIENTS
         Chaque ligne : avatar + nom + email + téléphone +
         montant total + nb commandes + dernière cmd + badge top + lien fiche
         ════════════════════════════════════════════════════════════ --}}
    @php
        $avCols    = ['av-green','av-blue','av-amber','av-purple','av-teal','av-rose'];
        $medailles = ['🥇','🥈','🥉','4e','5e'];
    @endphp

    {{-- ════ TABLE DESKTOP (visible > 700px) ════ --}}
    <div class="clients-card clients-table">
        @if($clients->isEmpty())
            <div style="padding:48px;text-align:center;font-size:14px;color:var(--muted)">
                @if($search)
                    Aucun client trouvé pour « {{ $search }} »
                @else
                    Aucun client pour le moment.
                @endif
            </div>
        @else
        <table class="tbl">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Montant total</th>
                    <th>Commandes</th>
                    <th>Dernière cmd</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $i => $item)
                @php
                    $client = $item->user;
                    if (!$client) continue;
                    $parts  = explode(' ', $client->name ?? 'C L');
                    $init   = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
                    $col    = $avCols[$i % count($avCols)];
                    $isTop  = in_array($client->id, $topClientIds);
                    $rang   = array_search($client->id, $topClientIds);
                @endphp
                <tr onclick="window.location='{{ route('boutique.clients.show', $client) }}'" style="cursor:pointer">
                    <td>
                        <div class="client-cell">
                            <div class="c-av {{ $col }}">{{ $init }}</div>
                            <div>
                                <div class="c-name">{{ $client->name }}</div>
                                @if($client->email)<div class="c-email">{{ $client->email }}</div>@endif
                                @if($client->phone)<div class="c-phone">📞 {{ $client->phone }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="c-amount">{{ number_format($item->total_depense, 0, ',', ' ') }}</div>
                        <div class="c-amount-sub">{{ $shop->currency ?? 'GNF' }}</div>
                    </td>
                    <td><span class="badge-cmd">{{ $item->nb_commandes }}</span></td>
                    <td style="font-size:12px;color:var(--text-2)">
                        {{ \Carbon\Carbon::parse($item->derniere_cmd)->diffForHumans() }}
                        <div style="font-size:10px;color:var(--muted)">{{ \Carbon\Carbon::parse($item->derniere_cmd)->format('d/m/Y') }}</div>
                    </td>
                    <td>
                        @if($isTop)
                            <span class="badge-top">{{ $medailles[$rang] }} Top client</span>
                        @else
                            <span style="font-size:11px;color:var(--muted)">Client régulier</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('boutique.clients.show', $client) }}" class="btn-fiche" onclick="event.stopPropagation()">
                            Voir fiche →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- ════ CARTES MOBILES (visible < 700px) ════ --}}
    <div class="clients-mobile">
        @forelse($clients as $i => $item)
        @php
            $client = $item->user;
            if (!$client) continue;
            $parts  = explode(' ', $client->name ?? 'C L');
            $init   = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
            $col    = $avCols[$i % count($avCols)];
            $isTop  = in_array($client->id, $topClientIds);
            $rang   = array_search($client->id, $topClientIds);
        @endphp
        <div class="m-client-card">

            {{-- Header : avatar + nom + badge top ── --}}
            <div class="m-client-hd">
                <div class="m-client-info">
                    <div class="c-av {{ $col }}" style="width:38px;height:38px;font-size:12px;flex-shrink:0">{{ $init }}</div>
                    <div style="flex:1;min-width:0">
                        <div class="c-name" style="font-size:13.5px">{{ $client->name }}</div>
                        @if($client->phone)
                            <div class="c-phone" style="font-size:11px;color:var(--muted);margin-top:1px">📞 {{ $client->phone }}</div>
                        @elseif($client->email)
                            <div class="c-email" style="font-size:11px;color:var(--muted);margin-top:1px">{{ Str::limit($client->email, 28) }}</div>
                        @endif
                    </div>
                </div>
                @if($isTop)
                    <span class="badge-top" style="flex-shrink:0">{{ $medailles[$rang] }} Top</span>
                @endif
            </div>

            {{-- Corps : stats en lignes ── --}}
            <div class="m-client-body">

                <div class="m-row">
                    <span class="m-lbl">💰 Montant total</span>
                    <span class="m-val" style="font-family:var(--mono);color:var(--brand)">
                        {{ number_format($item->total_depense, 0, ',', ' ') }}
                        <span style="font-size:10px;color:var(--muted);font-weight:500">{{ $shop->currency ?? 'GNF' }}</span>
                    </span>
                </div>

                <div class="m-row">
                    <span class="m-lbl">📦 Commandes</span>
                    <span class="badge-cmd">{{ $item->nb_commandes }}</span>
                </div>

                <div class="m-row">
                    <span class="m-lbl">🕐 Dernière commande</span>
                    <span style="font-size:12px;color:var(--text-2);font-weight:500">
                        {{ \Carbon\Carbon::parse($item->derniere_cmd)->diffForHumans() }}
                    </span>
                </div>

            </div>

            {{-- Footer : lien fiche ── --}}
            <div class="m-client-foot">
                <a href="{{ route('boutique.clients.show', $client) }}" class="btn-fiche">
                    Voir la fiche →
                </a>
            </div>
        </div>
        @empty
        <div style="padding:40px 20px;text-align:center;font-size:14px;color:var(--muted)">
            @if($search)
                Aucun client trouvé pour « {{ $search }} »
            @else
                Aucun client pour le moment.
            @endif
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($clients->hasPages())
    <div class="pagination-wrap">
        {{ $clients->links() }}
    </div>
    @endif

</div>
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

    /* Animation barres top clients */
    document.querySelectorAll('.top-bar-fill').forEach((el, i) => {
        setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 100 + i * 120);
    });
});
</script>
@endpush