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
    --brand:      #6366f1; --brand-dk:  #4f46e5;
    --brand-lt:   #e0e7ff; --brand-mlt: #eef2ff;
    --sb-bg:      #0e0e16; --sb-border: rgba(255,255,255,.08);
    --sb-act:     rgba(99,102,241,.52);
    --sb-hov:     rgba(255,255,255,.07);
    --sb-txt:     rgba(255,255,255,.62);
    --sb-txt-act: #fff;
    --bg:         #f8fafc; --surface:   #ffffff;
    --border:     #e2e8f0; --border-dk: #cbd5e1;
    --text:       #0f172a; --text-2:    #475569; --muted: #94a3b8;
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r:          14px; --r-sm: 9px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.06);
    --shadow:     0 4px 16px rgba(0,0,0,.07);
    --sb-w:       232px;
    --top-h:      58px;
}
html { font-family: var(--font); }
body { background: var(--bg); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

/* ══ LAYOUT ══ */
.dash-wrap { display: flex; min-height: 100vh; }
.dash-wrap .main { margin-left: var(--sb-w); flex: 1; min-width: 0; }

/* ══ SIDEBAR ══ */
.sidebar {
    background: linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    border-right: 1px solid rgba(99,102,241,.15);
    box-shadow: 6px 0 30px rgba(0,0,0,.35);
    display: flex; flex-direction: column;
    position: fixed; top: 0; left: 0; bottom: 0;
    width: var(--sb-w);
    overflow-y: scroll;
    scrollbar-width: thin;
    scrollbar-color: rgba(99,102,241,.35) transparent;
    z-index: 40;
}
.sidebar::-webkit-scrollbar       { width: 3px; }
.sidebar::-webkit-scrollbar-track { background: transparent; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(99,102,241,.35); border-radius: 3px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(99,102,241,.6); }

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
.sb-logo-icon { width: 36px; height: 36px; border-radius: 9px; overflow: hidden; flex-shrink: 0; }
.sb-shop-name { font-size: 14.5px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; letter-spacing: -.3px; color: #fff; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 10.5px; color: var(--sb-txt); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: #6ee7b7; flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px #6ee7b7; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }

.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; overflow: visible; }
.sb-section { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.8px; color: rgba(255,255,255,.48); padding: 16px 10px 5px; font-weight: 800; }
.sb-item {
    display: flex; align-items: center; gap: 10px;
    padding: 7px 10px; border-radius: var(--r-sm);
    font-size: 13.5px; font-weight: 600;
    color: rgba(255,255,255,.78); text-decoration: none; letter-spacing: -.1px;
    transition: background .15s, color .15s; position: relative;
}
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-item.active {
    background: var(--sb-act); color: #fff;
    box-shadow: 0 2px 12px rgba(99,102,241,.25);
}
.sb-item.active::before {
    content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
    width: 3px; height: 20px; background: #a5b4fc;
    border-radius: 0 3px 3px 0; box-shadow: 2px 0 8px rgba(165,180,252,.5);
}
.sb-item .ico {
    font-size: 13px; width: 26px; height: 26px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    border-radius: 7px; background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.06); transition: background .15s;
}
.sb-item:hover .ico { background: rgba(255,255,255,.09); }
.sb-item.active .ico { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.2); }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); min-width: 20px; text-align: center; }
.sb-badge.warn { background: #f59e0b; }

.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle {
    display: flex; align-items: center; gap: 10px;
    padding: 7px 10px; border-radius: var(--r-sm);
    font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); cursor: pointer;
    transition: background .15s, color .15s;
    user-select: none; border: none; background: none;
    width: 100%; text-align: left; font-family: var(--font);
}
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-group-toggle.open  { color: #fff; background: rgba(255,255,255,.05); }
.sb-group-toggle .ico  {
    font-size: 13px; width: 26px; height: 26px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    border-radius: 7px; background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.06); transition: background .15s;
}
.sb-group-toggle:hover .ico { background: rgba(255,255,255,.09); }
.sb-group-toggle.open .ico  { background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.14); }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.32); transition: transform .2s; flex-shrink: 0; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.6); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.1); margin-top: 2px; margin-bottom: 4px; overflow: visible; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 13px; font-weight: 500; padding: 6px 10px; color: rgba(255,255,255,.62); letter-spacing: 0; }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.92); }
.sb-sub .sb-item.active { color: #fff; background: var(--sb-act); font-weight: 600; }

.sb-scroll-hint {
    position: sticky; bottom: 0; width: 100%; height: 44px;
    background: linear-gradient(to bottom, transparent, rgba(17,17,24,.95));
    pointer-events: none; z-index: 2;
    display: flex; align-items: flex-end; justify-content: center;
    padding-bottom: 8px; transition: opacity .3s;
    margin-top: -44px; align-self: flex-end;
}
.sb-scroll-hint.hidden { opacity: 0; }
.sb-scroll-hint-arrow { display: flex; flex-direction: column; align-items: center; gap: 2px; animation: bounceDown 1.5s ease-in-out infinite; }
.sb-scroll-hint-dot { width: 4px; height: 4px; border-radius: 50%; background: rgba(99,102,241,.7); }
.sb-scroll-hint-dot:nth-child(2) { opacity: .5; margin-top: -2px; }
.sb-scroll-hint-dot:nth-child(3) { opacity: .25; margin-top: -2px; }
@keyframes bounceDown { 0%,100%{transform:translateY(0)} 50%{transform:translateY(4px)} }

.sb-footer {
    padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.08);
    flex-shrink: 0; display: flex; flex-direction: column; gap: 6px;
    position: sticky; bottom: 0;
    background: linear-gradient(180deg, transparent 0%, #0b0b12 25%);
    z-index: 1;
}
.sb-user {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; border-radius: var(--r-sm); text-decoration: none;
    border: 1px solid transparent;
    transition: background .15s, border-color .15s;
}
.sb-user:hover { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.07); }
.sb-av {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #4338ca);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0;
    box-shadow: 0 0 0 2px rgba(99,102,241,.45), 0 2px 8px rgba(99,102,241,.3);
    letter-spacing: -.5px;
}
.sb-uname { font-size: 13px; font-weight: 700; color: #fff; letter-spacing: -.2px; }
.sb-urole { font-size: 10.5px; color: rgba(255,255,255,.52); margin-top: 1px; font-weight: 500; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.92); font-size: 12.5px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s, color .15s, border-color .15s; text-align: left; }
.sb-logout:hover { background: rgba(220,38,38,.18); border-color: rgba(220,38,38,.35); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.18); flex-shrink: 0; }

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

/* ── KPI Stats ── */
.kpi-mini {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 16px;
    margin-bottom: 28px;
}
.kpi-chip {
    background: var(--surface);
    border: 1px solid rgba(99,102,241,.1);
    border-radius: var(--r);
    padding: 22px 22px 18px;
    box-shadow: 0 4px 20px rgba(99,102,241,.05),0 1px 4px rgba(0,0,0,.03);
    display: flex; flex-direction: column;
    transition: transform .2s, box-shadow .2s;
    overflow: hidden; position: relative;
}
.kpi-chip::after { content:''; position:absolute; bottom:-16px; right:-16px; width:72px; height:72px; border-radius:50%; background:var(--k-accent,rgba(99,102,241,.07)); pointer-events:none; }
.kpi-chip:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(99,102,241,.1),0 2px 8px rgba(0,0,0,.04); }
.kpi-chip-ico { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; margin-bottom:14px; background:var(--k-ico-bg,rgba(99,102,241,.1)); border:1.5px solid var(--k-ico-border,rgba(99,102,241,.2)); box-shadow:0 0 0 3px var(--k-ico-ring,rgba(99,102,241,.06)), 0 3px 10px var(--k-ico-glow,rgba(99,102,241,.15)); }
.kpi-chip-lbl { font-size:10.5px; font-weight:700; color:var(--k-color,#6366f1); text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
.kpi-chip-val { font-size:30px; font-weight:800; color:var(--text); font-family:var(--mono); letter-spacing:-1.2px; line-height:1; }
.kpi-chip-sub { font-size:11px; color:var(--muted); margin-top:6px; }

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

/* ── Table premium ── */
.tbl thead th {
    background: linear-gradient(135deg,rgba(99,102,241,.05),rgba(139,92,246,.03));
    border-bottom: 1px solid rgba(99,102,241,.1);
    color: #6366f1;
}
.tbl tbody tr:hover td { background: rgba(99,102,241,.025); cursor: pointer; }
.clients-card { border: 1px solid rgba(99,102,241,.1); box-shadow: 0 4px 20px rgba(99,102,241,.05),0 1px 4px rgba(0,0,0,.03); }

/* ── TOP CLIENTS PREMIUM ── */
.top-card {
    background: var(--surface);
    border: 1px solid rgba(99,102,241,.12);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(99,102,241,.07),0 1px 4px rgba(0,0,0,.04);
    margin-bottom: 28px;
}
.top-card-hd {
    padding: 16px 22px 14px;
    background: linear-gradient(135deg,rgba(99,102,241,.06) 0%,rgba(139,92,246,.03) 100%);
    border-bottom: 1px solid rgba(99,102,241,.1);
    display: flex; align-items: center; justify-content: space-between;
}
.top-card-title {
    font-size: 13.5px; font-weight: 700; color: var(--text);
    display: flex; align-items: center; gap: 8px;
}
.top-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0;
}
.top-item {
    display: flex; flex-direction: column;
    align-items: center; gap: 7px;
    padding: 22px 12px 20px;
    border-right: 1px solid var(--border);
    text-decoration: none;
    transition: background .18s, transform .18s;
    position: relative;
}
.top-item:first-child { background: linear-gradient(180deg,rgba(245,158,11,.04) 0%,transparent 100%); }
.top-item:last-child { border-right: none; }
.top-item:hover { background: var(--bg); transform: translateY(-2px); }

.top-medal { font-size: 24px; line-height: 1; }
.top-av {
    width: 48px; height: 48px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; font-weight: 800; color: #fff;
    box-shadow: 0 3px 12px rgba(0,0,0,.18);
}
.top-item:first-child .top-av { width:56px; height:56px; font-size:17px; box-shadow:0 4px 18px rgba(245,158,11,.35); }
.top-name {
    font-size: 12px; font-weight: 700; color: var(--text);
    text-align: center; line-height: 1.2;
}
.top-bar-track {
    width: 80%; height: 4px;
    background: rgba(0,0,0,.06); border-radius: 3px; overflow: hidden;
}
.top-bar-fill { height: 100%; border-radius: 3px; transition: width 1s cubic-bezier(.23,1,.32,1); }
.top-amount { font-size: 11.5px; font-weight: 700; font-family: var(--mono); color: var(--text); text-align: center; }
.top-cmds { font-size: 10px; color: var(--muted); text-align: center; }

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
    $initials = strtoupper(substr($parts[0],0,1)) . (isset($parts[1]) ? strtoupper(substr($parts[1],0,1)) : strtoupper(substr($parts[0],1,1)));
    $avPalette = [
        'linear-gradient(135deg,#6366f1,#4338ca)',
        'linear-gradient(135deg,#10b981,#059669)',
        'linear-gradient(135deg,#f59e0b,#d97706)',
        'linear-gradient(135deg,#8b5cf6,#6d28d9)',
        'linear-gradient(135deg,#ef4444,#b91c1c)',
        'linear-gradient(135deg,#14b8a6,#0d9488)',
        'linear-gradient(135deg,#f97316,#ea580c)',
        'linear-gradient(135deg,#06b6d4,#0891b2)',
        'linear-gradient(135deg,#ec4899,#be185d)',
        'linear-gradient(135deg,#84cc16,#4d7c0f)',
    ];
    $pendingCount = $shop->orders()->whereIn('status',['pending','en attente','en_attente','confirmée','processing'])->count();
@endphp

<div class="dash-wrap">

{{-- ══════ SIDEBAR ══════ --}}
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
            <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
            <span class="sb-shop-name">{{ $shop->name }}</span>
        </a>
        <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer le menu">✕</button>
        <div class="sb-status">
            <span class="pulse"></span>
            {{ $shop->is_approved ? 'Boutique active' : 'En attente de validation' }}
            &nbsp;·&nbsp;
            {{ ucfirst(auth()->user()->role_in_shop ?? auth()->user()->role) }}
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
        <a href="{{ route('boutique.messages.hub') }}" class="sb-item">
            <span class="ico">💬</span> Messages
        </a>
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

        <div class="kpi-chip" style="border-top:3px solid #6366f1;--k-color:#6366f1;--k-accent:rgba(99,102,241,.08);--k-ico-bg:rgba(99,102,241,.1);--k-ico-border:rgba(99,102,241,.22);--k-ico-ring:rgba(99,102,241,.06);--k-ico-glow:rgba(99,102,241,.15)">
            <div class="kpi-chip-ico">👥</div>
            <div class="kpi-chip-lbl">Total clients</div>
            <div class="kpi-chip-val">{{ $totalClients }}</div>
            <div class="kpi-chip-sub">clients uniques enregistrés</div>
        </div>

        <div class="kpi-chip" style="border-top:3px solid #8b5cf6;--k-color:#8b5cf6;--k-accent:rgba(139,92,246,.08);--k-ico-bg:rgba(139,92,246,.1);--k-ico-border:rgba(139,92,246,.22);--k-ico-ring:rgba(139,92,246,.06);--k-ico-glow:rgba(139,92,246,.15)">
            <div class="kpi-chip-ico">🆕</div>
            <div class="kpi-chip-lbl">Nouveaux ce mois</div>
            <div class="kpi-chip-val" style="color:#8b5cf6">{{ $nouveauxCeMois }}</div>
            <div class="kpi-chip-sub">ont commandé en {{ now()->translatedFormat('F') }}</div>
        </div>

        <div class="kpi-chip" style="border-top:3px solid #3b82f6;--k-color:#3b82f6;--k-accent:rgba(59,130,246,.08);--k-ico-bg:rgba(59,130,246,.1);--k-ico-border:rgba(59,130,246,.22);--k-ico-ring:rgba(59,130,246,.06);--k-ico-glow:rgba(59,130,246,.15)">
            <div class="kpi-chip-ico">💰</div>
            <div class="kpi-chip-lbl">CA total généré</div>
            <div class="kpi-chip-val" style="color:#2563eb">{{ $caTotal >= 1000000 ? number_format($caTotal/1000000,1).'M' : number_format($caTotal/1000,0).'k' }}</div>
            <div class="kpi-chip-sub">{{ $shop->currency ?? 'GNF' }} cumulés</div>
        </div>

    </div>

    {{-- Flash messages --}}
    @foreach(['success','info','warning','danger'] as $type)
        @if(session($type))
        <div style="margin-bottom:16px;padding:10px 14px;font-size:12.5px;font-weight:500;border-radius:var(--r-sm);border:1px solid;
            background:{{ $type==='success'?'#eef2ff':($type==='danger'?'#fef2f2':($type==='info'?'#eff6ff':'#fffbeb')) }};
            border-color:{{ $type==='success'?'#a5b4fc':($type==='danger'?'#fca5a5':($type==='info'?'#93c5fd':'#fcd34d')) }};
            color:{{ $type==='success'?'#3730a3':($type==='danger'?'#991b1b':($type==='info'?'#1e40af':'#92400e')) }}">
            {{ session($type) }}
        </div>
        @endif
    @endforeach

    {{-- ════════════════════════════════════════════════════════════
         BARRE RECHERCHE + TRI
         ════════════════════════════════════════════════════════════ --}}
    <form method="GET" action="{{ route('boutique.clients.index') }}" id="searchForm">
        <div class="toolbar">

            {{-- Recherche par nom / email / téléphone --}}
            <div class="search-box" id="searchBoxWrap">
                <span style="font-size:15px;flex-shrink:0">🔍</span>
                <input type="text"
                       id="searchInput"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Nom, email, téléphone…"
                       autocomplete="off">
                <button type="button" id="btnClearInput"
                    style="display:{{ $search ? 'flex' : 'none' }};background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px;padding:0;align-items:center;flex-shrink:0"
                    title="Effacer">✕</button>
            </div>

            {{-- Tri --}}
            <select name="sort" id="sortSelect" class="sort-select">
                <option value="total_depense" {{ $sortBy === 'total_depense' ? 'selected' : '' }}>💰 Plus dépensier</option>
                <option value="nb_commandes"  {{ $sortBy === 'nb_commandes'  ? 'selected' : '' }}>📦 Plus de commandes</option>
                <option value="derniere_cmd"  {{ $sortBy === 'derniere_cmd'  ? 'selected' : '' }}>🕐 Dernière commande</option>
            </select>

            {{-- Bouton recherche --}}
            <button type="submit" id="btnSearch"
                style="padding:9px 18px;border-radius:var(--r-sm);background:var(--brand);color:#fff;border:none;font-size:12.5px;font-weight:700;font-family:var(--font);cursor:pointer;white-space:nowrap">
                Rechercher
            </button>

            {{-- Reset complet --}}
            @if($search)
            <a href="{{ route('boutique.clients.index') }}?sort={{ $sortBy }}"
               style="padding:9px 14px;border-radius:var(--r-sm);border:1px solid var(--border-dk);background:var(--surface);font-size:12px;font-weight:600;color:var(--text-2);text-decoration:none;white-space:nowrap">
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
        $medailles   = ['🥇','🥈','🥉','4ᵉ','5ᵉ'];
        $medalColors = ['#f59e0b','#9ca3af','#b45309','#6b7280','#6b7280'];
        $medalBars   = [
            'linear-gradient(90deg,#f59e0b,#d97706)',
            'linear-gradient(90deg,#9ca3af,#6b7280)',
            'linear-gradient(90deg,#cd7c2b,#b45309)',
            'linear-gradient(90deg,#818cf8,#6366f1)',
            'linear-gradient(90deg,#818cf8,#6366f1)',
        ];
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
                $c    = $item->user;
                if (!$c) continue;
                $tp   = explode(' ', $c->name ?? 'CL');
                $init = strtoupper(substr($tp[0],0,1)).(isset($tp[1]) ? strtoupper(substr($tp[1],0,1)) : strtoupper(substr($tp[0],1,1)));
                $cCol = $avPalette[$c->id % count($avPalette)];
                $pct  = round(($item->total_mois / $maxTop) * 100);
            @endphp
            <a href="{{ route('boutique.clients.show', $c) }}" class="top-item">
                <div class="top-medal" style="color:{{ $medalColors[$i] }}">{{ $medailles[$i] }}</div>
                <div class="top-av" style="background:{{ $cCol }}">{{ $init }}</div>
                <div class="top-name">{{ Str::limit($c->name, 14) }}</div>
                <div class="top-bar-track">
                    <div class="top-bar-fill" data-pct="{{ $pct }}" style="width:0%;background:{{ $medalBars[$i] }}"></div>
                </div>
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
    @php $medailles = ['🥇','🥈','🥉','4ᵉ','5ᵉ']; @endphp

    {{-- ════ TABLE DESKTOP (visible > 700px) ════ --}}
    <div class="clients-card clients-table">
        @if($clients->isEmpty())
            <div style="padding:56px 32px;text-align:center;">
                @if($search)
                    <div style="font-size:42px;margin-bottom:14px;opacity:.35">🔍</div>
                    <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px">
                        Aucun résultat pour « {{ $search }} »
                    </div>
                    <div style="font-size:13px;color:var(--muted);margin-bottom:20px">
                        Aucun client ne correspond à cette recherche.
                    </div>
                    <a href="{{ route('boutique.clients.index') }}?sort={{ $sortBy }}"
                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:var(--r-sm);background:var(--brand);color:#fff;font-size:12.5px;font-weight:700;text-decoration:none">
                        ✕ Effacer la recherche
                    </a>
                @else
                    <div style="font-size:42px;margin-bottom:14px;opacity:.3">👥</div>
                    <div style="font-size:15px;font-weight:700;color:var(--text)">Aucun client pour le moment.</div>
                    <div style="font-size:13px;color:var(--muted);margin-top:6px">Les clients apparaîtront ici dès leur première commande.</div>
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
                    $cp    = explode(' ', $client->name ?? 'CL');
                    $init  = strtoupper(substr($cp[0],0,1)).(isset($cp[1]) ? strtoupper(substr($cp[1],0,1)) : strtoupper(substr($cp[0],1,1)));
                    $cGrad = $avPalette[$client->id % count($avPalette)];
                    $isTop = in_array($client->id, $topClientIds);
                    $rang  = array_search($client->id, $topClientIds);
                @endphp
                <tr onclick="window.location='{{ route('boutique.clients.show', $client) }}'" style="cursor:pointer">
                    <td>
                        <div class="client-cell">
                            <div class="c-av" style="background:{{ $cGrad }}">{{ $init }}</div>
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
            $cp    = explode(' ', $client->name ?? 'CL');
            $init  = strtoupper(substr($cp[0],0,1)).(isset($cp[1]) ? strtoupper(substr($cp[1],0,1)) : strtoupper(substr($cp[0],1,1)));
            $cGrad = $avPalette[$client->id % count($avPalette)];
            $isTop = in_array($client->id, $topClientIds);
            $rang  = array_search($client->id, $topClientIds);
        @endphp
        <div class="m-client-card">

            {{-- Header : avatar + nom + badge top ── --}}
            <div class="m-client-hd">
                <div class="m-client-info">
                    <div class="c-av" style="background:{{ $cGrad }};width:38px;height:38px;font-size:12px;flex-shrink:0">{{ $init }}</div>
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
        <div style="padding:44px 20px;text-align:center;">
            @if($search)
                <div style="font-size:38px;margin-bottom:12px;opacity:.35">🔍</div>
                <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:6px">
                    Aucun résultat pour « {{ $search }} »
                </div>
                <div style="font-size:12px;color:var(--muted);margin-bottom:16px">
                    Aucun client ne correspond à cette recherche.
                </div>
                <a href="{{ route('boutique.clients.index') }}?sort={{ $sortBy }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--r-sm);background:var(--brand);color:#fff;font-size:12px;font-weight:700;text-decoration:none">
                    ✕ Effacer
                </a>
            @else
                <div style="font-size:38px;margin-bottom:12px;opacity:.3">👥</div>
                <div style="font-size:14px;font-weight:700;color:var(--text)">Aucun client pour le moment.</div>
                <div style="font-size:12px;color:var(--muted);margin-top:5px">Les clients apparaîtront ici dès leur première commande.</div>
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

    /* ══ RECHERCHE & FILTRE ══ */
    const searchForm  = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const sortSelect  = document.getElementById('sortSelect');
    const btnClear    = document.getElementById('btnClearInput');

    /* Soumettre le formulaire sur changement du tri */
    sortSelect?.addEventListener('change', () => searchForm.submit());

    /* Soumettre sur touche Entrée dans le champ de recherche */
    searchInput?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); searchForm.submit(); }
    });

    /* Afficher/cacher le ✕ dans la barre de recherche selon la saisie */
    searchInput?.addEventListener('input', () => {
        if (btnClear) btnClear.style.display = searchInput.value ? 'flex' : 'none';
    });

    /* Vider le champ et soumettre */
    btnClear?.addEventListener('click', () => {
        searchInput.value = '';
        btnClear.style.display = 'none';
        searchInput.focus();
        searchForm.submit();
    });
});
</script>
@endpush