{{--
    resources/views/boutique/livreurs/index.blade.php
    Route : GET /boutique/livreurs → Boutique\LivreurController@index → boutique.livreurs.index
    Variables :
      $livreurs    → Collection<User>  (tous les livreurs de la boutique)
      $enLigneNow  → Collection<User>  (is_available = true)
      $horsLigne   → Collection<User>  (is_available = false)
      $total       → int
      $enLigne     → int
      $enCourse    → int
      $horsligne   → int
      $shop        → Shop
      $devise      → string
--}}

@extends('layouts.app')
@section('title', 'Livreurs · ' . $shop->name)
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
    --online:     #10b981; --busy:      #f59e0b; --offline: #94a3b8;
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r: 14px; --r-sm: 9px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.06);
    --shadow:     0 4px 20px rgba(0,0,0,.09);
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
.flash-danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }

/* ══ KPI STATS ══ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 18px 20px;
    box-shadow: var(--shadow-sm);
    display: flex; align-items: center; gap: 14px;
    transition: box-shadow .2s, transform .2s;
    position: relative; overflow: hidden;
}
.stat-card:hover { box-shadow: var(--shadow); transform: translateY(-2px); }
.stat-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; background: var(--sc-color, var(--brand));
}
.stat-ico {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
    background: var(--sc-bg, var(--brand-mlt));
}
.stat-val { font-size: 26px; font-weight: 800; font-family: var(--mono); color: var(--text); line-height: 1; letter-spacing: -.5px; }
.stat-lbl { font-size: 11px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-top: 3px; }

/* ══ SECTION TITRE ══ */
.sec-hd {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; gap: 12px;
}
.sec-title {
    font-size: 13px; font-weight: 800; color: var(--text);
    display: flex; align-items: center; gap: 8px;
    letter-spacing: -.2px;
}
.sec-badge {
    background: var(--brand-mlt); color: var(--brand-dk);
    border: 1px solid var(--brand-lt);
    font-size: 10px; font-weight: 700; font-family: var(--mono);
    padding: 2px 8px; border-radius: 20px;
}
.sec-badge.warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }

/* ══ CARTES LIVREURS EN LIGNE ══ */
.online-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 32px;
}

.driver-card-online {
    background: var(--surface);
    border: 1px solid var(--brand-lt);
    border-radius: var(--r);
    padding: 20px 18px;
    text-align: center;
    box-shadow: 0 2px 12px rgba(16,185,129,.1);
    position: relative; overflow: hidden;
    transition: box-shadow .2s, transform .2s;
}
.driver-card-online:hover { box-shadow: 0 6px 24px rgba(16,185,129,.18); transform: translateY(-3px); }

/* Halo animé en fond pour les livreurs en ligne */
.driver-card-online::before {
    content: '';
    position: absolute; top: -20px; right: -20px;
    width: 80px; height: 80px; border-radius: 50%;
    background: radial-gradient(circle, rgba(16,185,129,.12) 0%, transparent 70%);
    animation: halopulse 3s ease-in-out infinite;
}
@keyframes halopulse { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.3);opacity:.6} }

.driver-av {
    width: 56px; height: 56px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 800; color: #fff;
    margin: 0 auto 12px;
    position: relative; z-index: 1;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
}
/* Indicateur de statut sur l'avatar */
.driver-av::after {
    content: '';
    position: absolute; bottom: 2px; right: 2px;
    width: 12px; height: 12px; border-radius: 50%;
    background: var(--dot-color, var(--online));
    border: 2px solid var(--surface);
    box-shadow: 0 0 6px var(--dot-color, var(--online));
}

.driver-name {
    font-size: 13.5px; font-weight: 700; color: var(--text);
    margin-bottom: 4px; line-height: 1.2;
}
.driver-phone { font-size: 11.5px; color: var(--muted); margin-bottom: 10px; }

.driver-status-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 10.5px; font-weight: 700;
    padding: 4px 10px; border-radius: 20px;
}
.status-online  { background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); }
.status-busy    { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.status-offline { background: #f3f6f4; color: var(--muted); border: 1px solid var(--border); }

/* Indicateur pulsant pour les en ligne */
.dot-pulse {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--brand);
    animation: dotpulse 1.8s ease-in-out infinite;
    flex-shrink: 0;
}
@keyframes dotpulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.7)} }
.dot-busy    { background: var(--busy); animation: dotpulse 1s ease-in-out infinite; }
.dot-offline { background: var(--offline); animation: none; }

.driver-stats {
    display: flex; justify-content: center; gap: 14px;
    margin-top: 12px; padding-top: 10px;
    border-top: 1px solid var(--border);
    font-size: 10.5px; color: var(--muted);
}
.driver-stat-val { font-family: var(--mono); font-weight: 700; color: var(--text); font-size: 14px; display: block; }

/* ══ TABLE TOUS LES LIVREURS ══ */
.all-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm);
}
.all-card-hd {
    padding: 12px 18px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--bg);
}
.all-card-title { font-size: 13px; font-weight: 700; color: var(--text); }

/* Table desktop */
.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl thead th { padding: 10px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; background: var(--bg); border-bottom: 1px solid var(--border); white-space: nowrap; }
.tbl tbody td { padding: 12px 16px; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; }

.lv-cell { display: flex; align-items: center; gap: 10px; }
.lv-av-sm { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; position: relative; }
.lv-av-sm .status-dot { position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; border-radius: 50%; border: 2px solid var(--surface); }
.lv-av-sm .status-dot.online  { background: var(--online);  box-shadow: 0 0 4px var(--online); }
.lv-av-sm .status-dot.busy    { background: var(--busy);    box-shadow: 0 0 4px var(--busy); }
.lv-av-sm .status-dot.offline { background: var(--offline); }

.lv-name { font-size: 13px; font-weight: 600; color: var(--text); }
.lv-email { font-size: 11px; color: var(--muted); margin-top: 1px; }
.lv-phone { font-size: 11px; color: var(--muted); }

/* Boutons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: var(--r-sm); font-size: 12px; font-weight: 600; font-family: var(--font); border: 1px solid var(--border-dk); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-sm { padding: 5px 10px; font-size: 11.5px; }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(16,185,129,.25); }
.btn-primary:hover { background: var(--brand-dk); color: #fff; }
.btn-danger { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.btn-danger:hover { background: #fee2e2; }

/* ── Cartes mobiles ── */
.mobile-drivers { display: none; flex-direction: column; gap: 10px; padding: 14px; }
.m-driver-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); }
.m-driver-hd { padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.m-driver-body { padding: 0 14px 12px; display: flex; flex-direction: column; gap: 6px; }
.m-row { display: flex; align-items: center; justify-content: space-between; }
.m-lbl { font-size: 10.5px; color: var(--muted); font-weight: 600; text-transform: uppercase; }

/* Empty */
.empty-box { padding: 48px 20px; text-align: center; }
.empty-ico  { font-size: 44px; display: block; margin-bottom: 12px; opacity: .3; }
.empty-txt  { font-size: 14px; color: var(--muted); }

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .content { padding: 14px; }
}
@media (max-width: 700px) {
    .tbl-wrap { display: none; }
    .mobile-drivers { display: flex; }
    .online-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
}
@media (max-width: 520px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .stat-card { padding: 14px 12px; gap: 10px; }
    .stat-val { font-size: 22px; }
    .stat-ico { width: 38px; height: 38px; font-size: 17px; }
    .content { padding: 10px; }
    .online-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .driver-card-online { padding: 14px 12px; }
    .driver-av { width: 46px; height: 46px; font-size: 15px; }
}
@media (max-width: 380px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .online-grid { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush

@section('content')

@php
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $pendingCount = $shop->orders()->whereIn('status',['pending','en attente','en_attente','confirmée','processing'])->count();

    /* Couleurs d'avatars */
    $avColors = ['#059669','#2563eb','#d97706','#7c3aed','#0891b2','#e11d48','#065f46','#1d4ed8'];

    $initiales = function(string $name): string {
        $p = explode(' ', $name);
        return strtoupper(substr($p[0],0,1)) . strtoupper(substr($p[1] ?? 'X',0,1));
    };
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
        <a href="{{ route('boutique.clients.index') }}" class="sb-item">
            <span class="ico">👥</span> Clients
        </a>
        <a href="{{ route('boutique.employees.index') }}" class="sb-item">
            <span class="ico">🧑‍💼</span> Équipe
        </a>
        <div class="sb-section">Livraison</div>
        {{-- Livreurs — actif sur cette page ── --}}
        <a href="{{ route('boutique.livreurs.index') }}" class="sb-item active">
            <span class="ico">🚴</span> Livreurs
            @if($enLigne > 0)
                <span class="sb-badge">{{ $enLigne }}</span>
            @endif
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
            <div class="tb-title">🚴 Livreurs</div>
            <div class="tb-sub">{{ $shop->name }} · {{ $total }} livreur(s) · <span style="color:var(--brand);font-weight:700">{{ $enLigne }} en ligne</span></div>
        </div>
        <a href="{{ route('boutique.employees.create') }}" class="btn btn-primary btn-sm">
            ➕ Ajouter un livreur
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

        {{-- ════ KPI STATS ════ --}}
        <div class="stats-grid">
            <div class="stat-card" style="--sc-color:#3b82f6;--sc-bg:#eff6ff">
                <div class="stat-ico">🚴</div>
                <div>
                    <div class="stat-val">{{ $total }}</div>
                    <div class="stat-lbl">Total livreurs</div>
                </div>
            </div>
            <div class="stat-card" style="--sc-color:var(--brand);--sc-bg:var(--brand-mlt)">
                <div class="stat-ico">🟢</div>
                <div>
                    <div class="stat-val" style="color:var(--brand)">{{ $enLigne }}</div>
                    <div class="stat-lbl">En ligne</div>
                </div>
            </div>
            <div class="stat-card" style="--sc-color:#f59e0b;--sc-bg:#fffbeb">
                <div class="stat-ico">📦</div>
                <div>
                    <div class="stat-val" style="color:#d97706">{{ $enCourse }}</div>
                    <div class="stat-lbl">En course</div>
                </div>
            </div>
            <div class="stat-card" style="--sc-color:#94a3b8;--sc-bg:#f3f6f4">
                <div class="stat-ico">⭕</div>
                <div>
                    <div class="stat-val" style="color:var(--muted)">{{ $horsligne }}</div>
                    <div class="stat-lbl">Hors ligne</div>
                </div>
            </div>
        </div>

        {{-- ════ LIVREURS EN LIGNE MAINTENANT ════ --}}
        <div class="sec-hd">
            <div class="sec-title">
                🟢 En ligne maintenant
                <span class="sec-badge">{{ $enLigne }} disponible{{ $enLigne > 1 ? 's' : '' }}</span>
            </div>
        </div>

        @if($enLigneNow->isEmpty())
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:32px;text-align:center;margin-bottom:28px;box-shadow:var(--shadow-sm)">
            <div style="font-size:36px;opacity:.25;margin-bottom:10px">🚴</div>
            <div style="font-size:13px;color:var(--muted);font-weight:500">Aucun livreur en ligne pour le moment.</div>
        </div>
        @else
        <div class="online-grid" style="margin-bottom:28px">
            @foreach($enLigneNow as $i => $lv)
            @php
                $init  = $initiales($lv->name);
                $color = $avColors[$i % count($avColors)];
                $inCourse = !empty($lv->current_order_id);
            @endphp
            <div class="driver-card-online">
                {{-- Avatar avec indicateur de statut ── --}}
                <div class="driver-av"
                     style="background:{{ $color }};--dot-color:{{ $inCourse ? 'var(--busy)' : 'var(--online)' }}">
                    {{ $init }}
                </div>

                {{-- Nom ── --}}
                <div class="driver-name">{{ $lv->name }}</div>

                {{-- Téléphone ── --}}
                @if($lv->phone)
                <div class="driver-phone">📞 {{ $lv->phone }}</div>
                @else
                <div class="driver-phone" style="visibility:hidden">—</div>
                @endif

                {{-- Statut ── --}}
                @if($inCourse)
                <span class="driver-status-pill status-busy">
                    <span class="dot-pulse dot-busy"></span>
                    En course
                </span>
                @else
                <span class="driver-status-pill status-online">
                    <span class="dot-pulse"></span>
                    Disponible
                </span>
                @endif

                {{-- Mini stats ── --}}
                <div class="driver-stats">
                    <div style="text-align:center">
                        <span class="driver-stat-val">
                            {{ $lv->nb_livraisons }}
                        </span>
                        Livraisons
                    </div>
                    <div style="text-align:center">
                        <span class="driver-stat-val">
                            {{ $lv->nb_livraisons_today }}
                        </span>
                        Aujourd'hui
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ════ TOUS LES LIVREURS ════ --}}
        <div class="sec-hd" style="margin-bottom:16px">
            <div class="sec-title">
                📋 Tous les livreurs
                <span class="sec-badge warn">{{ $total }}</span>
            </div>
            <a href="{{ route('boutique.employees.create') }}" class="btn btn-sm">
                ➕ Ajouter
            </a>
        </div>

        @if($livreurs->isEmpty())
        <div class="empty-box">
            <span class="empty-ico">🛵</span>
            <div class="empty-txt">Aucun livreur rattaché à cette boutique.</div>
            <a href="{{ route('boutique.employees.create') }}" class="btn btn-primary" style="display:inline-flex;margin-top:16px">
                ➕ Ajouter le premier livreur
            </a>
        </div>
        @else

        {{-- ── TABLE DESKTOP (> 700px) ── --}}
        <div class="all-card tbl-wrap">
            <div class="all-card-hd">
                <span class="all-card-title">Liste complète — {{ $total }} livreur(s)</span>
                <span style="font-size:11px;color:var(--muted)">
                    🟢 {{ $enLigne }} en ligne &nbsp;·&nbsp; ⭕ {{ $horsligne }} hors ligne
                </span>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Livreur</th>
                        <th>Statut</th>
                        <th>Livraisons totales</th>
                        <th>Aujourd'hui</th>
                        <th>Membre depuis</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($livreurs as $i => $lv)
                    @php
                        $init     = $initiales($lv->name);
                        $color    = $avColors[$i % count($avColors)];
                        $inCourse = !empty($lv->current_order_id);
                        $dotClass = $lv->is_available ? ($inCourse ? 'busy' : 'online') : 'offline';
                        $totalLivs  = $lv->nb_livraisons;
                        $todayLivs  = $lv->nb_livraisons_today;
                    @endphp
                    <tr>
                        {{-- Livreur ── --}}
                        <td>
                            <div class="lv-cell">
                                <div class="lv-av-sm" style="background:{{ $color }}">
                                    {{ $init }}
                                    <span class="status-dot {{ $dotClass }}"></span>
                                </div>
                                <div>
                                    <div class="lv-name">{{ $lv->name }}</div>
                                    @if($lv->email)<div class="lv-email">{{ $lv->email }}</div>@endif
                                    @if($lv->phone)<div class="lv-phone">📞 {{ $lv->phone }}</div>@endif
                                </div>
                            </div>
                        </td>

                        {{-- Statut ── --}}
                        <td>
                            @if(!$lv->is_available)
                                <span class="driver-status-pill status-offline">
                                    <span class="dot-pulse dot-offline"></span>
                                    Hors ligne
                                </span>
                            @elseif($inCourse)
                                <span class="driver-status-pill status-busy">
                                    <span class="dot-pulse dot-busy"></span>
                                    En course
                                </span>
                            @else
                                <span class="driver-status-pill status-online">
                                    <span class="dot-pulse"></span>
                                    Disponible
                                </span>
                            @endif
                        </td>

                        {{-- Livraisons ── --}}
                        <td>
                            <span style="font-family:var(--mono);font-weight:700;font-size:14px;color:var(--text)">{{ $totalLivs }}</span>
                            <span style="font-size:10px;color:var(--muted)"> livraisons</span>
                        </td>

                        {{-- Aujourd'hui ── --}}
                        <td>
                            <span style="font-family:var(--mono);font-weight:700;font-size:14px;color:{{ $todayLivs > 0 ? 'var(--brand)' : 'var(--muted)' }}">
                                {{ $todayLivs }}
                            </span>
                            <span style="font-size:10px;color:var(--muted)"> auj.</span>
                        </td>

                        {{-- Membre depuis ── --}}
                        <td style="font-size:12px;color:var(--text-2)">
                            {{ $lv->created_at->diffForHumans() }}
                        </td>

                        {{-- Actions ── --}}
                        <td>
                            <form method="POST"
                                  action="{{ route('boutique.employees.destroy', $lv) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($lv->name) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── CARTES MOBILES (< 700px) ── --}}
        <div class="mobile-drivers">
            @foreach($livreurs as $i => $lv)
            @php
                $init     = $initiales($lv->name);
                $color    = $avColors[$i % count($avColors)];
                $inCourse = !empty($lv->current_order_id);
                $dotClass = $lv->is_available ? ($inCourse ? 'busy' : 'online') : 'offline';
                $totalLivs = $lv->nb_livraisons;
                $todayLivs = $lv->nb_livraisons_today;
            @endphp
            <div class="m-driver-card">
                <div class="m-driver-hd">
                    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                        <div class="lv-av-sm" style="background:{{ $color }};width:40px;height:40px;font-size:13px">
                            {{ $init }}
                            <span class="status-dot {{ $dotClass }}"></span>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div class="lv-name">{{ $lv->name }}</div>
                            @if($lv->phone)<div class="lv-phone">📞 {{ $lv->phone }}</div>@endif
                        </div>
                    </div>
                    @if(!$lv->is_available)
                        <span class="driver-status-pill status-offline" style="flex-shrink:0">
                            <span class="dot-pulse dot-offline"></span> Hors ligne
                        </span>
                    @elseif($inCourse)
                        <span class="driver-status-pill status-busy" style="flex-shrink:0">
                            <span class="dot-pulse dot-busy"></span> En course
                        </span>
                    @else
                        <span class="driver-status-pill status-online" style="flex-shrink:0">
                            <span class="dot-pulse"></span> Disponible
                        </span>
                    @endif
                </div>
                <div class="m-driver-body">
                    <div class="m-row">
                        <span class="m-lbl">📦 Total livraisons</span>
                        <span style="font-family:var(--mono);font-weight:700;color:var(--text)">{{ $totalLivs }}</span>
                    </div>
                    <div class="m-row">
                        <span class="m-lbl">⚡ Aujourd'hui</span>
                        <span style="font-family:var(--mono);font-weight:700;color:{{ $todayLivs > 0 ? 'var(--brand)' : 'var(--muted)' }}">{{ $todayLivs }}</span>
                    </div>
                    <div class="m-row" style="margin-top:4px">
                        <span style="font-size:11px;color:var(--muted)">Membre {{ $lv->created_at->diffForHumans() }}</span>
                        <form method="POST"
                              action="{{ route('boutique.employees.destroy', $lv) }}"
                              onsubmit="return confirm('Supprimer {{ addslashes($lv->name) }} ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️ Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @endif

    </div>{{-- /content --}}
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

    /* ── Auto-refresh toutes les 30s pour mettre à jour les statuts en ligne ── */
    setTimeout(() => window.location.reload(), 30_000);
});
</script>
@endpush