{{--
    resources/views/boutique/dashboard.blade.php
    Route : GET /boutique/dashboard  → ShopController@admin  → name('boutique.dashboard')
--}}

@extends('layouts.app')

@section('title', 'Dashboard · ' . $shop->name)

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    /* ── Accent indigo premium ── */
    --brand:      #6366f1;
    --brand-dk:   #4f46e5;
    --brand-lt:   #e0e7ff;
    --brand-mlt:  #eef2ff;

    /* ── Sidebar noir profond ── */
    --sb-bg:      #0e0e16;
    --sb-border:  rgba(255,255,255,.08);
    --sb-act:     rgba(99,102,241,.52);
    --sb-hov:     rgba(255,255,255,.07);
    --sb-txt:     rgba(255,255,255,.62);
    --sb-txt-act: #fff;

    /* ── Surfaces ── */
    --bg:         #f8fafc;
    --surface:    #ffffff;
    --border:     #e2e8f0;
    --border-dk:  #cbd5e1;

    /* ── Texte ── */
    --text:       #0f172a;
    --text-2:     #475569;
    --muted:      #94a3b8;

    /* ── Typo & divers ── */
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r:          12px;
    --r-sm:       8px;
    --r-xs:       5px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.05), 0 1px 2px rgba(0,0,0,.03);
    --shadow:     0 4px 20px rgba(0,0,0,.06), 0 1px 4px rgba(0,0,0,.03);
    --shadow-lg:  0 10px 40px rgba(0,0,0,.08);
    --sb-w:       232px;
    --top-h:      58px;
}

html { font-family: var(--font); }
body { background: var(--bg); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

.dash-wrap { display: flex; min-height: 100vh; }
.dash-wrap .sidebar { flex-shrink: 0; }
.dash-wrap .main { margin-left: var(--sb-w); flex: 1; min-width: 0; }

/* SIDEBAR */
.sidebar { background: linear-gradient(180deg, #0f0f59 0%, #0e0e16 40%, #10103a 100%); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; width: var(--sb-w); overflow-y: scroll; scrollbar-width: thin; scrollbar-color: rgba(99,102,241,.35) transparent; z-index: 40; border-right: 1px solid rgba(99,102,241,.15); box-shadow: 6px 0 30px rgba(0,0,0,.35); -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; }
.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,.04); }
.sidebar::-webkit-scrollbar-thumb { background: rgba(99,102,241,.4); border-radius: 4px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(99,102,241,.7); }
.sb-brand { padding: 18px 16px 14px; border-bottom: 1px solid var(--sb-border); flex-shrink: 0; position: relative; }
.sb-close { display: none; position: absolute; top: 14px; right: 12px; width: 30px; height: 30px; border-radius: 8px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.6); font-size: 18px; line-height: 1; cursor: pointer; align-items: center; justify-content: center; transition: background .15s, color .15s; flex-shrink: 0; }
.sb-close:hover { background: rgba(239,68,68,.18); border-color: rgba(239,68,68,.3); color: #fca5a5; }
@media (max-width: 900px) { .sb-close { display: flex; } }
.sb-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.sb-logo-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
.sb-shop-name { font-size: 14.5px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; letter-spacing: -.3px; color: #fff; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 11px; color: rgba(255,255,255,.58); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: #6ee7b7; flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px #6ee7b7; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }
.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; overflow: visible; }
.sb-section { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.8px; color: rgba(255,255,255,.48); padding: 16px 10px 5px; font-weight: 800; }
.sb-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); text-decoration: none; transition: background .15s, color .15s; position: relative; letter-spacing: -.1px; }
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-item.active { background: var(--sb-act); color: #fff; box-shadow: 0 2px 12px rgba(99,102,241,.25); }
.sb-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: #a5b4fc; border-radius: 0 3px 3px 0; box-shadow: 2px 0 8px rgba(165,180,252,.5); }
.sb-item .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border-radius: 7px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); transition: background .15s; }
.sb-item:hover .ico { background: rgba(255,255,255,.11); border-color: rgba(255,255,255,.14); }
.sb-item.active .ico { background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.25); }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); min-width: 20px; text-align: center; }
.sb-badge.warn { background: #f59e0b; }
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); cursor: pointer; transition: background .15s, color .15s; user-select: none; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); letter-spacing: -.1px; }
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-group-toggle.open { color: #fff; background: rgba(255,255,255,.05); }
.sb-group-toggle .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border-radius: 7px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.32); transition: transform .2s; flex-shrink: 0; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.6); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.1); margin-top: 2px; margin-bottom: 4px; overflow: visible; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 13px; font-weight: 500; padding: 6px 10px; color: rgba(255,255,255,.62); letter-spacing: 0; }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.92); }
.sb-sub .sb-item.active { color: #fff; background: var(--sb-act); font-weight: 600; }
.sb-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; position: sticky; bottom: 0; background: linear-gradient(180deg, transparent 0%, #0b0b12 25%); z-index: 1; }
.sb-scroll-hint { position: sticky; top: auto; bottom: 72px; width: 100%; height: 40px; background: linear-gradient(to bottom, transparent, rgba(17,17,24,.95)); pointer-events: none; z-index: 2; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 6px; transition: opacity .3s; margin-top: -40px; align-self: flex-end; }
.sb-scroll-hint.hidden { opacity: 0; pointer-events: none; }
.sb-scroll-hint-arrow { display: flex; flex-direction: column; align-items: center; gap: 2px; animation: bounceDown 1.5s ease-in-out infinite; }
.sb-scroll-hint-dot { width: 4px; height: 4px; border-radius: 50%; background: rgba(99,102,241,.6); }
.sb-scroll-hint-dot:nth-child(2) { opacity: .5; margin-top: -2px; }
.sb-scroll-hint-dot:nth-child(3) { opacity: .25; margin-top: -2px; }
@keyframes bounceDown { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(4px); } }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); text-decoration: none; transition: background .15s, border-color .15s; border: 1px solid transparent; }
.sb-user:hover { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.08); }
.sb-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #4338ca); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(99,102,241,.45), 0 2px 8px rgba(99,102,241,.3); letter-spacing: -.5px; }
.sb-uname { font-size: 13px; font-weight: 700; color: #fff; letter-spacing: -.2px; }
.sb-urole { font-size: 10.5px; color: rgba(255,255,255,.52); margin-top: 1px; font-weight: 500; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.9); font-size: 12.5px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s, color .15s, border-color .15s; text-align: left; letter-spacing: -.1px; }
.sb-logout:hover { background: rgba(220,38,38,.18); border-color: rgba(220,38,38,.35); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; flex-shrink: 0; }

/* MAIN */
.main { display: flex; flex-direction: column; min-width: 0; }

/* ══════════════════════════════════════════════════
   TOPBAR — responsive complet
   ══════════════════════════════════════════════════ */
.topbar {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 16px;
    height: var(--top-h);
    display: flex; align-items: center; gap: 8px;
    position: sticky; top: 0; z-index: 30;
    box-shadow: var(--shadow-sm);
}
.tb-info { flex: 1; min-width: 0; overflow: hidden; }
.tb-title { font-size: 14px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tb-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }
.tb-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

/* Boutons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 11px; border-radius: var(--r-sm); font-size: 12px; font-weight: 600; font-family: var(--font); border: 1px solid var(--border-dk); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); }
.btn-primary:hover { background: var(--brand-dk); border-color: var(--brand-dk); color: #fff; }
.btn-sm { padding: 5px 10px; font-size: 11px; }
.btn-ghost { background: transparent; border-color: transparent; color: var(--brand); padding: 5px 8px; }
.btn-ghost:hover { background: var(--brand-mlt); border-color: var(--brand-lt); }
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); font-size: 20px; }

/* Export : visible sur grand écran, caché sur petit */
.topbar-export-group { display: flex; gap: 6px; }

/* Dropdown export : caché sur grand écran, visible sur petit */
.topbar-export-dropdown { display: none; position: relative; }
.export-dropdown-btn { display: inline-flex; align-items: center; gap: 4px; padding: 5px 10px; border-radius: var(--r-sm); font-size: 12px; font-weight: 700; font-family: var(--font); background: var(--surface); color: var(--text-2); border: 1px solid var(--border-dk); cursor: pointer; white-space: nowrap; }
.export-dropdown-btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.export-menu { position: absolute; top: calc(100% + 6px); right: 0; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-sm); box-shadow: var(--shadow); min-width: 130px; z-index: 200; display: none; flex-direction: column; overflow: hidden; }
.export-menu.open { display: flex; }
.export-menu a { display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 12.5px; font-weight: 600; color: var(--text-2); text-decoration: none; transition: background .12s; }
.export-menu a:hover { background: var(--bg); }

/* Responsive topbar */
@media (max-width: 680px) {
    .topbar-export-group { display: none; }
    .topbar-export-dropdown { display: block; }
}
@media (max-width: 540px) {
    .tb-sub { display: none; }
    .tb-title { font-size: 13px; }
    .msg-btn-label { display: none; }
}
@media (max-width: 420px) {
    .btn-commande-label { display: none; }
}

/* PAGE */
.content { padding: 22px 24px; flex: 1; }
.flash { margin: 12px 24px 0; padding: 10px 14px; font-size: 12.5px; font-weight: 500; border-radius: var(--r-sm); border: 1px solid; display: flex; align-items: center; gap: 8px; }
.flash-success { background: #eef2ff; border-color: #a5b4fc; color: #3730a3; }
.flash-info    { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
.flash-warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
.flash-danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.comm-banner { background: var(--brand-mlt); border: 1px solid var(--brand-lt); border-radius: var(--r-sm); padding: 10px 14px; display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #3730a3; margin-bottom: 20px; font-weight: 500; }

/* KPI GRID */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
.kpi { background: var(--surface); border: 1px solid var(--border); border-top: 3px solid var(--kpi-color, var(--brand)); border-radius: var(--r); padding: 18px 20px 16px; position: relative; overflow: hidden; box-shadow: var(--shadow-sm); transition: box-shadow .22s, transform .22s, border-color .15s; }
.kpi:hover { box-shadow: 0 8px 28px rgba(0,0,0,.09), 0 2px 6px rgba(0,0,0,.04); transform: translateY(-2px); }
.kpi-icon { width: 44px; height: 44px; background: var(--kpi-bg, var(--brand-mlt)); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; border: 1.5px solid var(--kpi-border, rgba(0,0,0,.08)); box-shadow: 0 0 0 3px var(--kpi-ring, transparent), 0 2px 10px var(--kpi-glow, rgba(0,0,0,.06)); }
.kpi-lbl { font-size: 10.5px; color: var(--muted); font-weight: 700; letter-spacing: .4px; margin-bottom: 5px; text-transform: uppercase; }
.kpi-val { font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -.8px; font-family: var(--mono); line-height: 1; }
.kpi-unit { font-size: 10px; color: var(--muted); margin-top: 4px; }
.kpi-delta { font-size: 11px; font-weight: 700; margin-top: 7px; display: flex; align-items: center; gap: 3px; }
.up   { color: #16a34a; }
.down { color: #dc2626; }

/* CARDS */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm);   transition: all 0.3s ease; }
.card-hd { padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.card-bd { padding: 16px 18px; }
.card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

/* CHART */
.chart-wrap { margin-bottom: 22px; }
.rc-header { padding: 18px 22px 14px; border-bottom: 1px solid rgba(99,102,241,.1); display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: linear-gradient(135deg,rgba(99,102,241,.045) 0%,rgba(139,92,246,.02) 100%); }
.rc-header-left .rc-title { font-size: 10.5px; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 6px; }
.rc-total { font-size: 28px; font-weight: 800; color: var(--text); font-family: var(--mono); line-height: 1; letter-spacing: -1px; }
.rc-total sup { font-size: 12px; font-weight: 600; vertical-align: super; color: var(--muted); margin-right: 2px; }
.rc-delta { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; margin-top: 8px; }
.rc-delta.up   { background: #dcfce7; color: #15803d; }
.rc-delta.down { background: #fee2e2; color: #991b1b; }
.rc-delta.flat { background: #f3f4f6; color: #6b7280; }
.rc-header-right { text-align: right; font-size: 11px; color: var(--muted); background: rgba(99,102,241,.04); border: 1px solid rgba(99,102,241,.1); border-radius: 10px; padding: 10px 14px; }
.rc-header-right .rc-best { font-size: 12px; font-weight: 700; color: var(--brand); margin-top: 2px; }
.rc-svg-wrap { padding: 14px 22px 4px; position: relative; }
.rc-tooltip { position: absolute; background: linear-gradient(135deg,#1e1b4b,#2d2b6e); color: #f8fafc; border-radius: 10px; padding: 10px 14px; font-size: 11.5px; pointer-events: none; opacity: 0; transition: opacity .15s; white-space: nowrap; box-shadow: 0 8px 28px rgba(99,102,241,.4),0 2px 8px rgba(0,0,0,.2); z-index: 10; border: 1px solid rgba(165,180,252,.2); }
.rc-tooltip strong { display: block; font-size: 14px; font-family: var(--mono); color: #a5b4fc; }
.rc-tooltip span   { color: #94a3b8; font-size: 10px; }
.rc-day-dots { display: flex; justify-content: space-around; padding: 2px 0 4px; }
.rc-day-dot { flex: 1; text-align: center; font-size: 9.5px; color: var(--muted); font-family: var(--mono); }
.rc-day-dot.today { color: var(--brand); font-weight: 700; }

/* CONTENT GRID */
.content-grid { display: grid; grid-template-columns: 1fr 380px; gap: 18px; margin-bottom: 22px; }
.right-col { display: flex; flex-direction: column; gap: 16px; }

/* TABLE */
.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl th { text-align: left; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; padding: 0 10px 10px 0; border-bottom: 1px solid var(--border); }
.tbl td { padding: 10px 10px 10px 0; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: var(--bg); }
.oid  { font-family: var(--mono); font-size: 11px; color: var(--muted); }
.onam { font-weight: 600; color: var(--text); margin-top: 1px; }
.oamt { font-family: var(--mono); font-weight: 600; color: var(--text); white-space: nowrap; text-align: right; }
.pill { display: inline-block; font-size: 10.5px; font-weight: 600; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
.p-success { background: #dcfce7; color: #15803d; }
.p-warning { background: #fef3c7; color: #92400e; }
.p-info    { background: #dbeafe; color: #1e40af; }
.p-danger  { background: #fee2e2; color: #991b1b; }
.p-muted   { background: #f3f6f4; color: #6b7280; }

/* ═══ CARTE LIVRAISON ═══ */
.delivery-card { border-radius: 16px !important; overflow: hidden; border: none !important; box-shadow: 0 4px 20px rgba(0,0,0,.08) !important; }
.delivery-card-hero { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); padding: 18px 20px 14px; display: flex; align-items: center; justify-content: space-between; }
.delivery-card-hero-left { display: flex; align-items: center; gap: 12px; }
.delivery-card-icon { width: 44px; height: 44px; background: rgba(255,255,255,.12); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
.delivery-card-title { font-size: 15px; font-weight: 800; color: #fff; margin: 0; }
.delivery-card-sub { font-size: 11px; color: rgba(255,255,255,.55); margin-top: 2px; }
.delivery-card-manage { font-size: 11.5px; font-weight: 700; color: rgba(255,255,255,.8); text-decoration: none; background: rgba(255,255,255,.1); padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,.2); transition: all .15s; }
.delivery-card-manage:hover { background: rgba(255,255,255,.2); color: #fff; }

/* Tabs */
.tab-bar { display: flex; border-bottom: 1px solid var(--border); padding: 0 16px; gap: 0; background: #fff; }
.tab-btn { padding: 11px 14px; font-size: 12px; font-weight: 600; color: var(--muted); cursor: pointer; border: none; background: none; font-family: var(--font); border-bottom: 2.5px solid transparent; transition: color .15s, border-color .15s; display: flex; align-items: center; gap: 6px; white-space: nowrap; margin-bottom: -1px; }
.tab-btn:hover { color: var(--text); }
.tab-btn.active { color: var(--brand); border-bottom-color: var(--brand); }
.tab-count { background: var(--brand-lt); color: var(--brand); font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 10px; }
.tab-count.zero { background: #f3f6f4; color: var(--muted); }
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* Livreurs */
.lv-list { display: flex; flex-direction: column; padding: 6px 0; }
.lv-row { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-bottom: 1px solid #f3f6f4; transition: background .12s; cursor: pointer; }
.lv-row:last-child { border-bottom: none; }
.lv-row:hover { background: #f8fafc; }
.lv-av { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
.lv-info { flex: 1; min-width: 0; }
.lv-nm { font-size: 13px; font-weight: 700; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lv-phone { font-size: 11.5px; color: var(--brand); font-weight: 600; margin-top: 2px; display: flex; align-items: center; gap: 4px; }
.lv-phone-warn { font-size: 11px; color: #f59e0b; font-weight: 600; margin-top: 2px; }
.lv-status-badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; flex-shrink: 0; }
.lv-status-badge.available { background: #dcfce7; color: #15803d; }
.lv-status-badge.busy { background: #fef3c7; color: #92400e; }
.lv-wa-btn { width: 30px; height: 30px; background: #25d366; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; text-decoration: none; transition: transform .15s, box-shadow .15s; }
.lv-wa-btn:hover { transform: scale(1.12); box-shadow: 0 4px 12px rgba(37,211,102,.4); }
.lv-footer { padding: 10px 16px; text-align: center; border-top: 1px solid #f3f6f4; }
.lv-footer a { font-size: 12px; font-weight: 700; color: var(--brand); text-decoration: none; }
.lv-footer a:hover { text-decoration: underline; }

/* Notice aucun livreur */
.no-livreur-notice { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 28px 20px; text-align: center; }
.no-livreur-notice .notice-icon { font-size: 36px; }
.no-livreur-notice .notice-title { font-size: 14px; font-weight: 700; color: var(--text); }
.no-livreur-notice .notice-sub   { font-size: 12px; color: var(--muted); line-height: 1.6; max-width: 260px; }

.status-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.status-dot.available { background: #22c55e; box-shadow: 0 0 6px rgba(34,197,94,.5); }
.status-dot.busy      { background: #f59e0b; box-shadow: 0 0 6px rgba(245,158,11,.5); }
.co-list { display: flex; flex-direction: column; }
.co-row { display: flex; align-items: center; gap: 11px; padding: 11px 18px; border-bottom: 1px solid #f3f6f4; transition: background .12s; cursor: pointer; }
.co-row:last-child { border-bottom: none; }
.co-row:hover { background: var(--bg); }
.co-logo { width: 38px; height: 38px; border-radius: var(--r-sm); background: var(--bg); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; overflow: hidden; }
.co-logo img { width: 28px; height: 28px; object-fit: contain; }
.co-info { flex: 1; min-width: 0; }
.co-nm { font-size: 12.5px; font-weight: 600; color: var(--text); }
.co-mt { font-size: 11px; color: var(--muted); margin-top: 1px; }
.co-commission { font-size: 10px; font-weight: 700; background: var(--brand-mlt); color: var(--brand-dk); padding: 2px 7px; border-radius: 12px; white-space: nowrap; }

/* TOP PRODUITS */
.sp-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.sp-row:last-child { margin-bottom: 0; }
.sp-lbl { font-size: 12px; font-weight: 500; color: var(--text-2); width: 110px; flex-shrink: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sp-track { flex: 1; height: 7px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
.sp-fill { height: 100%; border-radius: 4px; background: var(--brand); transition: width 1.1s cubic-bezier(.23,1,.32,1); }
.sp-val { font-family: var(--mono); font-size: 11.5px; font-weight: 600; color: var(--text); width: 30px; text-align: right; flex-shrink: 0; }

/* AVATAR COLORS */
.av-green  { background: #6366f1; }
.av-blue   { background: #2563eb; }
.av-amber  { background: #d97706; }
.av-purple { background: #7c3aed; }
.av-teal   { background: #0891b2; }
.av-rose   { background: #e11d48; }

/* SIDEBAR OVERLAY */
.sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 39; }

/* RESPONSIVE GLOBAL */
@media (max-width: 1100px) { .content-grid { grid-template-columns: 1fr 340px; } }
@media (max-width: 900px) {
    :root { --sb-w: 230px; }
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .content-grid { grid-template-columns: 1fr; }
    .right-col { gap: 14px; }
}
@media (max-width: 520px) {
    .content { padding: 14px; }
    .topbar  { padding: 0 10px; }
    .kpi-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .kpi-val  { font-size: 20px; }
    .flash { margin: 10px 14px 0; }
    .comm-banner { margin-bottom: 14px; font-size: 11.5px; }
    .kanban-grid { grid-template-columns: repeat(2,1fr); }
    .kanban-count { font-size: 22px; }
    .quick-grid  { grid-template-columns: repeat(2,1fr); }
    .quick-btn   { border-right: none; border-bottom: 1px solid var(--border); }
    .quick-btn:last-child { border-bottom: none; }
    .quick-btn-ico { width: 36px; height: 36px; font-size: 18px; }
    .quick-btn-lbl { font-size: 11.5px; }
    .today-grid  { grid-template-columns: 1fr; }
    .today-card  { padding: 16px; gap: 12px; }
    .today-val   { font-size: 22px; }
    .period-stats { grid-template-columns: repeat(2,1fr); }
    .period-stat:nth-child(2) { border-right: none; }
    .period-stat:nth-child(3) { border-top: 1px solid var(--border); }
    .tb-title { font-size: 13px; }
    .tb-sub   { display: none; }
}
@media (max-width: 380px) {
    .kpi-grid { grid-template-columns: 1fr; }
    .today-grid { grid-template-columns: 1fr; }
    .kanban-grid { grid-template-columns: repeat(2,1fr); }
}

/* ÉTAPE 1 CSS */
.today-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
.today-card { background: linear-gradient(90deg, #291d95, #291d95, #aa28d9 ); border: 1px solid rgba(99,102,241,.35); border-radius: var(--r); padding: 20px 22px; display: flex; align-items: center; gap: 16px; box-shadow: 0 8px 32px rgba(79,70,229,.35), 0 2px 8px rgba(0,0,0,.2); position: relative; overflow: hidden; }
.today-card::before { content: ''; position: absolute; right: -30px; top: -30px; width: 140px; height: 140px; background: rgba(255,255,255,.06); border-radius: 50%; pointer-events: none; }
.today-card::after  { content: ''; position: absolute; left: -20px; bottom: -30px; width: 110px; height: 110px; background: rgba(255,255,255,.04); border-radius: 50%; pointer-events: none; }
.today-icon { width: 48px; height: 48px; background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; backdrop-filter: blur(4px); }
.today-lbl  { font-size: 10px; font-weight: 700; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; }
.today-val  { font-size: 26px; font-weight: 800; color: #fff; font-family: var(--mono); letter-spacing: -1px; line-height: 1; text-shadow: 0 2px 8px rgba(0,0,0,.2); }
.today-unit { font-size: 10px; color: rgba(255,255,255,.45); margin-top: 3px; }
.today-delta { font-size: 11px; font-weight: 700; margin-top: 6px; display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 20px; }
.today-delta.up   { background: rgba(255,255,255,.15); color: #c7d2fe; border: 1px solid rgba(255,255,255,.2); }
.today-delta.down { background: rgba(239,68,68,.25);  color: #fca5a5; border: 1px solid rgba(239,68,68,.3); }
.today-delta.flat { background: rgba(255,255,255,.08); color: rgba(255,255,255,.5); }
.alerts-zone { margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; }
.alert-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: var(--r-sm); border: 1px solid; font-size: 12.5px; font-weight: 500; animation: slideIn .3s ease; }
@keyframes slideIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
.alert-item.danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.alert-item.warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
.alert-item.success { background: #eef2ff; border-color: #a5b4fc; color: #3730a3; }
.alert-item.info    { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
.alert-ico  { font-size: 16px; flex-shrink: 0; }
.alert-msg  { flex: 1; line-height: 1.4; }
.alert-cta  { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; border: 1px solid currentColor; text-decoration: none; color: inherit; white-space: nowrap; flex-shrink: 0; opacity: .75; transition: opacity .15s; }
.alert-cta:hover { opacity: 1; }
.kanban-section { margin-bottom: 22px; }
.kanban-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
.kanban-col { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 16px 14px 14px; text-align: center; box-shadow: var(--shadow-sm); transition: all .2s; cursor: default; position: relative; overflow: hidden; }
.kanban-col::after { content: ''; position: absolute; bottom: -10px; right: -10px; width: 55px; height: 55px; border-radius: 50%; background: var(--k-color); opacity: .05; pointer-events: none; }
.kanban-col:hover { box-shadow: var(--shadow); transform: translateY(-3px); border-color: var(--k-color); }
.kanban-ico-wrap { width: 46px; height: 46px; border-radius: 50%; background: var(--k-bg); border: 2.5px solid var(--k-color); box-shadow: 0 0 0 4px var(--k-bg), 0 4px 14px rgba(0,0,0,.10); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 20px; transition: transform .2s; }
.kanban-col:hover .kanban-ico-wrap { transform: scale(1.1); }
.kanban-count { font-size: 30px; font-weight: 800; font-family: var(--mono); color: var(--k-color); line-height: 1; letter-spacing: -1.5px; }
.kanban-lbl   { font-size: 10px; font-weight: 700; color: var(--muted); margin-top: 5px; text-transform: uppercase; letter-spacing: .5px; }
.kanban-col.has-items { background: var(--k-bg); border-color: var(--k-color); }
.quick-section { margin-bottom: 22px; }
.quick-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); box-shadow: var(--shadow-sm); overflow: hidden; }
.quick-card-hd { padding: 13px 18px 12px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.quick-card-title { font-size: 13px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 8px; }
.quick-card-title .title-ico { width: 24px; height: 24px; background: var(--brand-mlt); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 13px; }
.quick-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
.quick-btn { display: flex; flex-direction: column; align-items: center; gap: 9px; padding: 20px 14px 18px; text-decoration: none; border-right: 1px solid var(--border); transition: background .15s, transform .15s; position: relative; cursor: pointer; }
.quick-btn:last-child { border-right: none; }
.quick-btn:hover { background: var(--q-bg, var(--brand-mlt)); }
.quick-btn::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: var(--q-color, var(--brand)); opacity: 0; transition: opacity .15s; }
.quick-btn:hover::before { opacity: 1; }
.quick-btn-ico { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; background: var(--q-bg, var(--brand-mlt)); border: 1px solid var(--q-border, var(--brand-lt)); transition: transform .15s; flex-shrink: 0; }
.quick-btn:hover .quick-btn-ico { transform: scale(1.08); }
.quick-btn-lbl { font-size: 12.5px; font-weight: 700; color: var(--text); line-height: 1.2; text-align: center; }
.quick-btn-sub { font-size: 10.5px; color: var(--muted); line-height: 1.3; text-align: center; }

@media (max-width: 860px) {
    .kanban-grid { grid-template-columns: repeat(3,1fr); }
    .quick-grid  { grid-template-columns: repeat(2,1fr); }
    .quick-btn   { border-right: none; border-bottom: 1px solid var(--border); }
    .quick-btn:last-child { border-bottom: none; }
    .today-grid  { grid-template-columns: 1fr; }
    .e2-grid     { grid-template-columns: 1fr; }
}

/* ÉTAPE 2 CSS */
.e2-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px; }
.client-list { display: flex; flex-direction: column; }
.client-row { display: flex; align-items: center; gap: 12px; padding: 11px 18px; border-bottom: 1px solid #f3f6f4; transition: background .12s; }
.client-row:last-child { border-bottom: none; }
.client-row:hover { background: var(--bg); }
.client-av { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.client-info { flex: 1; min-width: 0; }
.client-name { font-size: 12.5px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.client-meta { font-size: 10.5px; color: var(--muted); margin-top: 1px; }
.client-bar-wrap { width: 60px; flex-shrink: 0; }
.client-bar-track { height: 5px; background: #eef1f0; border-radius: 3px; overflow: hidden; margin-bottom: 3px; }
.client-bar-fill { height: 100%; border-radius: 3px; background: var(--brand); transition: width 1s cubic-bezier(.23,1,.32,1); }
.client-amount { font-family: var(--mono); font-size: 10px; font-weight: 600; color: var(--text); text-align: right; white-space: nowrap; }
.client-rank { width: 20px; flex-shrink: 0; font-size: 11px; font-weight: 700; color: var(--muted); text-align: center; }
.client-rank.top { color: #f59e0b; font-size: 14px; }
.risk-list { display: flex; flex-direction: column; }
.risk-row { display: flex; align-items: center; gap: 12px; padding: 10px 18px; border-bottom: 1px solid #f3f6f4; transition: background .12s; }
.risk-row:last-child { border-bottom: none; }
.risk-row:hover { background: var(--bg); }
.risk-img { width: 38px; height: 38px; border-radius: var(--r-sm); object-fit: cover; flex-shrink: 0; border: 1px solid var(--border); }
.risk-img-placeholder { width: 38px; height: 38px; border-radius: var(--r-sm); background: #f3f6f4; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.risk-info { flex: 1; min-width: 0; }
.risk-name { font-size: 12.5px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.risk-meta { font-size: 10.5px; color: var(--muted); margin-top: 1px; }
.risk-badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; white-space: nowrap; flex-shrink: 0; }
.risk-empty { padding: 20px 18px; text-align: center; font-size: 13px; color: var(--muted); }
.risk-empty .ico { font-size: 28px; display: block; margin-bottom: 6px; }

/* PÉRIODE */
.period-card { margin-bottom: 22px; }
.period-btns { display: flex; flex-wrap: wrap; gap: 6px; padding: 14px 18px; border-bottom: 1px solid var(--border); }
.period-btn { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; font-family: var(--font); border: 1px solid var(--border-dk); background: var(--bg); color: var(--text-2); cursor: pointer; transition: all .15s; white-space: nowrap; }
.period-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-mlt); }
.period-btn.active { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(99,102,241,.25); }
.period-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
.period-stat { padding: 16px 18px; border-right: 1px solid var(--border); text-align: center; }
.period-stat:last-child { border-right: none; }
.period-stat-lbl { font-size: 10.5px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .3px; margin-bottom: 5px; }
.period-stat-val { font-size: 20px; font-weight: 700; font-family: var(--mono); color: var(--text); letter-spacing: -.5px; }
.period-stat-sub { font-size: 10px; color: var(--muted); margin-top: 2px; }
.period-chart { padding: 16px 18px 14px; border-top: 1px solid var(--border); }
.period-bars { display: flex; align-items: flex-end; gap: 3px; height: 70px; margin-bottom: 6px; }
.period-bar-wrap { flex: 1; height: 100%; display: flex; align-items: flex-end; position: relative; }

/* ══ TOOLTIP SURVOL BARRE ══ */
.period-bar-tooltip {
    position: absolute; bottom: calc(100% + 8px); left: 50%;
    transform: translateX(-50%);
    background: #0f1c18; color: #fff;
    font-size: 11px; font-weight: 600; font-family: var(--mono);
    padding: 7px 11px; border-radius: var(--r-sm);
    white-space: nowrap; pointer-events: none;
    opacity: 0; transition: opacity .15s; z-index: 50;
    text-align: center; min-width: 110px;
    box-shadow: 0 4px 14px rgba(0,0,0,.25);
}
.period-bar-tooltip::after {
    content: ''; position: absolute; top: 100%; left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent; border-top-color: #0f1c18;
}
.period-bar-wrap:hover .period-bar-tooltip { opacity: 1; }
.tt-date   { font-size: 9px; color: rgba(255,255,255,.45); display: block; margin-bottom: 3px; }
.tt-ca     { color: #a5b4fc; font-size: 12px; display: block; }
.tt-detail { color: rgba(255,255,255,.55); font-size: 9.5px; display: block; margin-top: 2px; }

.period-bar { width: 100%; border-radius: 3px 3px 0 0; background: var(--brand); opacity: .8; transition: height .4s cubic-bezier(.23,1,.32,1), opacity .15s; cursor: pointer; position: relative; min-height: 2px; }
.period-bar:hover { opacity: 1; }
.period-bar.empty { opacity: .15; background: #9ca3af; }
.period-bar-labels { display: flex; gap: 3px; overflow: hidden; }
.period-bar-lbl { flex: 1; text-align: center; font-size: 9px; color: var(--muted); font-family: var(--mono); white-space: nowrap; overflow: hidden; text-overflow: clip; }
.period-loading { display: none; align-items: center; justify-content: center; padding: 32px; gap: 10px; font-size: 13px; color: var(--muted); font-weight: 500; }
.period-loading.show { display: flex; }
.spin { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--brand-lt); border-top-color: var(--brand); animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.period-label { font-size: 11px; color: var(--muted); font-weight: 500; padding: 0 18px 10px; }
.period-label strong { color: var(--text); }

@media (max-width: 640px) {
    .period-stats { grid-template-columns: repeat(2,1fr); }
    .period-stat:nth-child(2) { border-right: none; }
    .period-stat:nth-child(3) { border-top: 1px solid var(--border); }
}

/* ═══════════════════════════════
   PREMIUM ENHANCEMENTS
═══════════════════════════════ */

/* Sidebar active item — glow premium (override) */
.sb-item.active {
    background: linear-gradient(90deg, rgba(99,102,241,.58) 0%, rgba(99,102,241,.32) 100%);
    box-shadow: 0 2px 14px rgba(99,102,241,.22), inset 0 1px 0 rgba(255,255,255,.08);
}
.sb-item.active::before { box-shadow: 2px 0 10px rgba(165,180,252,.6), 0 0 8px rgba(99,102,241,.35); }

/* Topbar search */
.tb-search { display:flex; align-items:center; gap:8px; background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:6px 14px; font-size:12px; color:var(--muted); cursor:pointer; white-space:nowrap; transition:border-color .15s, box-shadow .15s; flex-shrink:0; min-width:160px; }
.tb-search:hover { border-color:var(--brand-lt); box-shadow:0 0 0 3px rgba(99,102,241,.08); }
.tb-search kbd { background:#f1f5f9; border:1px solid var(--border-dk); border-radius:4px; padding:1px 5px; font-size:10px; color:var(--muted); font-family:var(--mono); margin-left:auto; }
@media(max-width:680px){ .tb-search{ display:none; } }

/* Topbar icon button */
.tb-icon-btn { width:34px; height:34px; border-radius:8px; background:transparent; border:1px solid var(--border); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:15px; transition:all .15s; color:var(--text-2); flex-shrink:0; text-decoration:none; }
.tb-icon-btn:hover { background:var(--brand-mlt); border-color:var(--brand-lt); color:var(--brand); }

/* Topbar greeting */
.tb-greeting { font-size:14px; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.tb-greeting-sub { font-size:11px; color:var(--muted); margin-top:1px; }

/* Hero card wave decoration */
.today-wave { position:absolute; right:0; top:0; bottom:0; width:55%; pointer-events:none; overflow:hidden; z-index:0; }
.today-wave svg { position:absolute; right:-10px; bottom:0; width:115%; height:100%; }
.today-icon, .today-card > div:not(.today-wave) { position:relative; z-index:1; }

/* KPI card - subtle brand accent dot */
.kpi::before { content:''; position:absolute; top:0; right:14px; width:4px; height:4px; border-radius:50%; background:var(--kpi-color, var(--brand)); opacity:.5; margin-top:9px; }

/* KPI card hover lift */
.kpi { transition:box-shadow .2s, transform .2s; }
.kpi:hover { box-shadow:0 8px 32px rgba(99,102,241,.12), 0 2px 8px rgba(0,0,0,.05); transform:translateY(-2px); }

/* Charts side-by-side */
.charts-duo { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:24px; }
@media(max-width:860px){ .charts-duo{ grid-template-columns:1fr; } }
.charts-duo .card { border:1px solid rgba(99,102,241,.12); box-shadow:0 4px 28px rgba(99,102,241,.08),0 1px 6px rgba(0,0,0,.04); overflow:hidden; }
.card.chart-wrap { border-top:3px solid #6366f1; }
.charts-duo>.card:not(.chart-wrap) { border-top:3px solid #8b5cf6; }

/* Mini bar chart (commandes) */
.mini-chart-hd { padding:18px 22px 12px; display:flex; align-items:flex-start; justify-content:space-between; gap:12px; background:linear-gradient(135deg,rgba(139,92,246,.045) 0%,rgba(99,102,241,.02) 100%); border-bottom:1px solid rgba(139,92,246,.1); }
.mini-chart-lbl { font-size:10.5px; font-weight:700; color:#8b5cf6; text-transform:uppercase; letter-spacing:.6px; margin-bottom:6px; }
.mini-chart-val { font-size:26px; font-weight:800; font-family:var(--mono); color:var(--text); letter-spacing:-.8px; line-height:1; }
.mini-chart-sub { font-size:10px; color:var(--muted); margin-top:3px; }
.mini-period-badge { font-size:10.5px; color:#8b5cf6; background:rgba(139,92,246,.09); border:1px solid rgba(139,92,246,.2); border-radius:20px; padding:4px 12px; font-weight:700; white-space:nowrap; cursor:pointer; align-self:flex-start; margin-top:4px; }
.mini-delta { font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:3px; padding:3px 10px; border-radius:20px; margin-top:7px; }
.mini-delta.up { background:#dcfce7; color:#15803d; } .mini-delta.down { background:#fee2e2; color:#991b1b; }
.mini-bars-wrap { display:flex; align-items:flex-end; gap:5px; padding:18px 22px 6px; height:120px; }
.mini-bar-col { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%; }
.mini-bar { width:80%; border-radius:6px 6px 0 0; background:linear-gradient(180deg,#a78bfa 0%,#7c3aed 100%); opacity:.45; min-height:3px; transition:height .65s cubic-bezier(.23,1,.32,1), opacity .2s, box-shadow .2s; cursor:pointer; }
.mini-bar:hover { opacity:.85; box-shadow:0 -4px 14px rgba(139,92,246,.4); }
.mini-bar.actuel { background:linear-gradient(180deg,#c4b5fd 0%,#7c3aed 100%); opacity:1; box-shadow:0 -6px 20px rgba(139,92,246,.55); }
.mini-bar-lbl { font-size:9px; color:var(--muted); font-family:var(--mono); }
.mini-bar-labs { display:flex; gap:5px; padding:0 22px 14px; }

/* Résumé rapide */
.perf-grid { display:grid; grid-template-columns:1fr 268px; gap:16px; margin-bottom:22px; align-items:start; }
@media(max-width:1000px){ .perf-grid{ grid-template-columns:1fr; } }
.resume-items { display:flex; flex-direction:column; gap:10px; }
.resume-item { display:flex; align-items:center; gap:12px; padding:13px 16px; background:var(--bg); border:1px solid var(--border); border-radius:var(--r-sm); transition:all .18s; }
.resume-item:hover { border-color:var(--brand-lt); background:var(--brand-mlt); transform:translateX(3px); }
.resume-item-ico { width:36px; height:36px; border-radius:9px; background:var(--surface); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.resume-item-val { font-size:19px; font-weight:800; font-family:var(--mono); color:var(--text); letter-spacing:-.5px; line-height:1; }
.resume-item-lbl { font-size:10.5px; color:var(--muted); margin-top:2px; font-weight:600; }

/* ── Chat boutique ↔ entreprise (modal depuis cloche) ── */
.bq-chat-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:16px;}
.bq-chat-overlay.open{display:flex;}
.bq-chat-panel{background:#fff;border-radius:16px;width:100%;max-width:520px;display:flex;flex-direction:column;height:88vh;max-height:660px;box-shadow:0 28px 80px rgba(0,0,0,.22);overflow:hidden;animation:bqModalIn .2s ease;}
@keyframes bqModalIn{from{opacity:0;transform:scale(.95) translateY(-8px)}to{opacity:1;transform:scale(1) translateY(0)}}
.bq-chat-hd{padding:14px 18px;border-bottom:1px solid #e2e8f0;flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:10px;background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;}
.bq-chat-hd-info{display:flex;align-items:center;gap:10px;}
.bq-chat-hd-av{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;overflow:hidden;}
.bq-chat-hd-av img{width:100%;height:100%;object-fit:cover;}
.bq-chat-hd-name{font-size:14px;font-weight:800;color:#fff;line-height:1.2;}
.bq-chat-hd-sub{font-size:11px;color:rgba(255,255,255,.7);margin-top:1px;}
.bq-chat-close{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;font-size:16px;flex-shrink:0;transition:background .14s;}
.bq-chat-close:hover{background:rgba(255,255,255,.3);}
.bq-chat-msgs{flex:1;overflow-y:auto;padding:14px 16px;display:flex;flex-direction:column;gap:8px;background:#f8fafc;scrollbar-width:thin;scrollbar-color:#c7d2fe transparent;}
.bq-chat-msgs::-webkit-scrollbar{width:3px;}
.bq-chat-msgs::-webkit-scrollbar-thumb{background:#c7d2fe;border-radius:3px;}
.bq-msg-row{display:flex;flex-direction:column;max-width:80%;}
.bq-msg-row.mine{align-self:flex-end;align-items:flex-end;}
.bq-msg-row.theirs{align-self:flex-start;align-items:flex-start;}
.bq-msg-bubble{padding:9px 13px;border-radius:14px;font-size:13px;line-height:1.5;word-break:break-word;white-space:pre-wrap;}
.bq-msg-row.mine .bq-msg-bubble{background:#4f46e5;color:#fff;border-bottom-right-radius:4px;}
.bq-msg-row.theirs .bq-msg-bubble{background:#fff;color:#1e293b;border-bottom-left-radius:4px;border:1px solid #e2e8f0;}
.bq-msg-bubble.system{background:#fef9c3;color:#92400e;border:1px solid #fde68a;font-size:12px;font-style:italic;border-radius:10px;}
.bq-msg-meta{font-size:10px;color:#94a3b8;margin-top:2px;padding:0 2px;}
.bq-chat-empty{text-align:center;padding:32px 16px;color:#94a3b8;font-size:13px;}
.bq-chat-input-zone{padding:10px 14px;flex-shrink:0;border-top:1px solid #e2e8f0;background:#fff;display:flex;gap:8px;align-items:flex-end;}
.bq-chat-textarea{flex:1;resize:none;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;font-family:var(--font);color:var(--text);outline:none;line-height:1.4;max-height:120px;min-height:40px;transition:border-color .14s;scrollbar-width:thin;}
.bq-chat-textarea:focus{border-color:#6366f1;}
.bq-chat-send-btn{width:38px;height:38px;border-radius:10px;border:none;cursor:pointer;background:#6366f1;color:#fff;font-size:17px;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:background .14s;}
.bq-chat-send-btn:hover{background:#4f46e5;}
.bq-chat-send-btn:disabled{opacity:.5;cursor:not-allowed;}
.bq-confier-zone{padding:10px 14px;flex-shrink:0;background:#f0fdf4;border-bottom:1.5px solid #bbf7d0;}
.bq-confier-row{display:flex;gap:8px;align-items:center;}
.bq-order-select{flex:1;padding:8px 10px;border:1.5px solid #86efac;border-radius:9px;font-size:12.5px;font-family:var(--font);color:var(--text);background:#fff;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;background-size:14px;padding-right:28px;transition:border-color .14s;}
.bq-order-select:focus{border-color:#059669;}
.bq-btn-confier{padding:8px 14px;border-radius:9px;border:none;cursor:pointer;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:13px;font-weight:800;font-family:var(--font);display:flex;align-items:center;gap:6px;white-space:nowrap;box-shadow:0 3px 10px rgba(16,185,129,.3);transition:all .15s;flex-shrink:0;}
.bq-btn-confier:hover{transform:translateY(-1px);box-shadow:0 5px 16px rgba(16,185,129,.45);}
.bq-btn-confier:disabled{opacity:.6;cursor:not-allowed;transform:none;}
.bq-btn-confier.done{background:linear-gradient(135deg,#6b7280,#9ca3af);box-shadow:none;}
.bq-order-card{padding:9px 12px;border-radius:9px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;transition:all .14s;display:flex;align-items:center;gap:10px;}
.bq-order-card:hover{border-color:#86efac;background:#f0fdf4;}
.bq-order-card.selected{border-color:#059669;background:#f0fdf4;box-shadow:0 0 0 3px rgba(16,185,129,.12);}
.bq-order-card-num{font-size:11px;font-weight:700;color:#6366f1;font-family:var(--mono);flex-shrink:0;}
.bq-order-card-info{flex:1;min-width:0;}
.bq-order-card-client{font-size:12.5px;font-weight:700;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.bq-order-card-amount{font-size:11px;color:#6b7280;margin-top:1px;}
.bq-order-card-check{width:20px;height:20px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:11px;transition:all .14s;color:transparent;}
.bq-order-card.selected .bq-order-card-check{background:#059669;border-color:#059669;color:#fff;}
.bq-order-card-thumb{width:40px;height:40px;border-radius:8px;flex-shrink:0;overflow:hidden;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:18px;border:1px solid #e2e8f0;}
.bq-order-card-thumb img{width:100%;height:100%;object-fit:cover;}
.bq-order-card-address{font-size:10.5px;color:#6b7280;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:200px;}
.bq-confier-hint{font-size:11px;color:#059669;font-weight:600;margin-bottom:6px;}

/* ══════════════════════════════════════════════════
   CORRECTIFS RESPONSIVE COMPLÉMENTAIRES
══════════════════════════════════════════════════ */

/* Table scrollable horizontalement sur mobile */
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;}
.tbl-wrap .tbl{min-width:420px;}

/* Notification dropdown : ne déborde pas sur petits écrans */
@media(max-width:400px){
    #notifDropdown{width:calc(100vw - 24px);right:auto;left:50%;transform:translateX(-50%);}
}

/* co-row (liste entreprises partenaires) : bouton passe en dessous sur très petit écran */
@media(max-width:400px){
    .co-row{flex-wrap:wrap;gap:8px;}
    .co-info{min-width:0;flex:1 1 calc(100% - 50px);}
    .co-commission{margin-left:auto;}
    .co-row .btn{width:100%;justify-content:center;font-size:11px;}
}

/* Period stats : 1 colonne sous 400px */
@media(max-width:400px){
    .period-stats{grid-template-columns:1fr !important;}
    .period-stat{border-right:none !important;border-top:1px solid var(--border);}
    .period-stat:first-child{border-top:none;}
}

/* risk-row : 1 par ligne sur très petit écran */
@media(max-width:400px){
    .risk-row{min-width:100% !important;border-right:none !important;}
}

/* Topbar : compresse les actions sur très petit écran */
@media(max-width:360px){
    .tb-actions{gap:4px;}
    .tb-icon-btn{width:30px;height:30px;font-size:13px;}
    .btn-hamburger{padding:4px;}
    .topbar{padding:0 8px;}
}

/* KPI : 1 colonne sous 340px */
@media(max-width:340px){
    .kpi-grid{grid-template-columns:1fr !important;}
}

/* Kanban : 2 colonnes sous 400px */
@media(max-width:400px){
    .kanban-grid{grid-template-columns:repeat(2,1fr) !important;gap:8px;}
    .kanban-count{font-size:24px;}
    .kanban-ico-wrap{width:38px;height:38px;font-size:17px;margin-bottom:8px;}
}

/* Quick actions : 2 colonnes, moins de padding sur mobile */
@media(max-width:400px){
    .quick-btn{padding:14px 8px 12px;}
    .quick-btn-ico{width:36px;height:36px;font-size:18px;}
    .quick-btn-lbl{font-size:11px;}
    .quick-btn-sub{display:none;}
}

/* Charts : hauteur adaptée sur mobile */
@media(max-width:520px){
    .mini-bars-wrap{height:80px;padding:12px 14px 4px;}
    .mini-chart-hd{padding:12px 14px 10px;}
    .mini-chart-val{font-size:22px;}
    .rc-svg-wrap{padding:10px 14px 2px;}
}

/* Today cards : empilées sur mobile */
@media(max-width:520px){
    .today-grid{grid-template-columns:1fr !important;}
    .today-card{padding:16px;}
    .today-val{font-size:22px;}
    .today-wave{opacity:.5;}
}

/* perf-grid : 1 colonne sur mobile */
@media(max-width:700px){
    .perf-grid{grid-template-columns:1fr !important;}
}

/* Bq chat panel : plein écran sur mobile */
@media(max-width:560px){
    .bq-chat-overlay{padding:0;align-items:flex-end;}
    .bq-chat-panel{border-radius:20px 20px 0 0;max-height:90vh;max-width:100%;}
    .bq-confier-row{flex-wrap:wrap;}
    .bq-btn-confier{width:100%;justify-content:center;}
}

/* Résumé rapide : compact sur mobile */
@media(max-width:520px){
    .resume-item{padding:10px 12px;}
    .resume-item-val{font-size:16px;}
    .resume-item-ico{width:30px;height:30px;font-size:15px;}
}

/* Card header : texte tronqué sur mobile */
@media(max-width:520px){
    .card-hd{flex-wrap:wrap;gap:6px;padding:12px 14px;}
    .card-title{font-size:12px;}
    .card-bd{padding:12px 14px;}
    .card-hd .btn-ghost{font-size:11px;padding:4px 6px;}
}

/* lv-row compact sur mobile */
@media(max-width:400px){
    .lv-row{padding:8px 12px;gap:8px;}
    .lv-av{width:34px;height:34px;font-size:11px;}
    .lv-nm{font-size:12px;}
}

/* Scrollbar globale fine */
*{scrollbar-width:thin;scrollbar-color:rgba(99,102,241,.2) transparent;}
::-webkit-scrollbar{width:4px;height:4px;}
::-webkit-scrollbar-thumb{background:rgba(99,102,241,.2);border-radius:4px;}
</style>
@endpush

@php
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . (isset($parts[1]) ? strtoupper(substr($parts[1],0,1)) : strtoupper(substr($parts[0],1,1)));
    $now      = \Illuminate\Support\Carbon::now();
    $_locale  = app()->getLocale();
    $_dayAbbr = [
        'fr' => ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
        'en' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    ];
    $_months = [
        'fr' => ['', 'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
        'en' => ['', 'January','February','March','April','May','June','July','August','September','October','November','December'],
    ];
    $_dayLabel  = fn($d) => ($_dayAbbr[$_locale] ?? $_dayAbbr['en'])[$d->dayOfWeek];
    $_monthYear = fn($d) => ($_months[$_locale] ?? $_months['en'])[$d->month] . ' ' . $d->year;

    $commissionsPaieesMonth = (float) \App\Models\CourierCommission::where('shop_id', $shop->id)->where('status', 'payée')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('amount');
    $caGrossMonth = (float) $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->sum('total');
    $caMonth = $caGrossMonth - $commissionsPaieesMonth;
    $commissionsPaieesPrev = (float) \App\Models\CourierCommission::where('shop_id', $shop->id)->where('status', 'payée')->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->sum('amount');
    $caGrossPrev  = (float) $shop->orders()->whereMonth('created_at',$now->copy()->subMonth()->month)->whereYear('created_at',$now->copy()->subMonth()->year)->where('status','livrée')->sum('total');
    $caNetPrev    = $caGrossPrev - $commissionsPaieesPrev;
    $caDelta      = $caNetPrev > 0 ? round((($caMonth - $caNetPrev) / $caNetPrev) * 100, 1) : ($caMonth > 0 ? 100 : 0);
    $cmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->whereNotIn('status',['annulée','cancelled'])->count();
    $cmdToday = $shop->orders()->whereDate('created_at', today())->whereNotIn('status',['annulée','cancelled'])->count();
    $cmdYest  = $shop->orders()->whereDate('created_at', today()->subDay())->whereNotIn('status',['annulée','cancelled'])->count();
    $cmdLivreesMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->count();
    $panier          = $cmdLivreesMonth > 0 ? round($caMonth / $cmdLivreesMonth) : 0;
    $cmdLivreesPrev  = $shop->orders()->whereMonth('created_at',$now->copy()->subMonth()->month)->whereYear('created_at',$now->copy()->subMonth()->year)->where('status','livrée')->count();
    $panierPrev      = $cmdLivreesPrev > 0 ? round($caNetPrev / $cmdLivreesPrev) : 0;
    $panierDelta     = $panierPrev > 0 ? round((($panier - $panierPrev) / $panierPrev) * 100, 1) : ($panier > 0 ? 100 : 0);
    $totalCmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->whereNotIn('status',['annulée','cancelled'])->count();
    $livres        = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->count();
    $tauxLiv       = $totalCmdMonth > 0 ? round(($livres / $totalCmdMonth) * 100, 1) : 0;
    $days7 = collect(range(6,0))->map(function ($i) use ($shop, $now, $_dayLabel) {
        $day      = $now->copy()->subDays($i)->toDateString();
        $caJour   = (float) $shop->orders()->whereDate('created_at', $day)->where('status','livrée')->sum('total');
        $commJour = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop, $day) {
            $q->where('shop_id', $shop->id)->whereDate('created_at', $day);
        })->where('status', 'payée')->sum('amount');
        $d = $now->copy()->subDays($i);
        return ['label' => $_dayLabel($d), 'value' => max(0, $caJour - $commJour), 'today' => $i === 0];
    })->values();
    $max7 = $days7->max('value') ?: 1;
    $prev7Total = (float) collect(range(13,7))->sum(function ($i) use ($shop, $now) {
        $day      = $now->copy()->subDays($i)->toDateString();
        $caJ      = (float) $shop->orders()->whereDate('created_at', $day)->where('status','livrée')->sum('total');
        $commJ    = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop, $day) {
            $q->where('shop_id', $shop->id)->whereDate('created_at', $day);
        })->where('status', 'payée')->sum('amount');
        return max(0, $caJ - $commJ);
    });
    $recentOrders = $shop->orders()->with('user')->latest()->take(6)->get();
    $topProducts = $shop->products()->withCount('orderItems')->orderByDesc('order_items_count')->take(5)->get();
    $maxSales    = $topProducts->max('order_items_count') ?: 1;
    $statusMap = [
        'livrée'=>['label'=>'Livré','cls'=>'p-success'],
        'pending'=>['label'=>'En attente','cls'=>'p-warning'],
        'processing'=>['label'=>'En traitement','cls'=>'p-info'],
        'confirmée'=>['label'=>'Confirmé','cls'=>'p-info'],
        'en_livraison'=>['label'=>'En livraison','cls'=>'p-info'],
        'shipped'=>['label'=>'Expédié','cls'=>'p-info'],
        'annulée'=>['label'=>'Annulé','cls'=>'p-danger'],
    ];
    $pendingCount = $shop->orders()->whereIn('status',['en attente','en_attente','pending','confirmée','processing'])->count();
    $avColors = ['#10b981','#6366f1','#f59e0b','#8b5cf6','#14b8a6','#f43f5e'];
    $devise = $shop->currency ?? 'GNF';
    $caGrossToday    = (float) $shop->orders()->whereDate('created_at', today())->where('status','livrée')->sum('total');
    $commToday       = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop) { $q->where('shop_id', $shop->id)->whereDate('created_at', today()); })->where('status', 'payée')->sum('amount');
    $caToday         = max(0, $caGrossToday - $commToday);
    $caGrossYesterday = (float) $shop->orders()->whereDate('created_at', today()->subDay())->where('status','livrée')->sum('total');
    $commYesterday    = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop) { $q->where('shop_id', $shop->id)->whereDate('created_at', today()->subDay()); })->where('status', 'payée')->sum('amount');
    $caYesterday      = max(0, $caGrossYesterday - $commYesterday);
    $caTodayDelta = $caYesterday > 0 ? round((($caToday - $caYesterday) / $caYesterday) * 100, 1) : ($caToday > 0 ? 100 : 0);
    $alerts = collect();
    $cmdEnRetard = $shop->orders()->whereIn('status', ['pending', 'en attente', 'en_attente'])->where('created_at', '<', now()->subHours(2))->count();
    if ($cmdEnRetard > 0) $alerts->push(['type'=>'danger','ico'=>'🚨','msg'=>"{$cmdEnRetard} commande(s) en attente depuis plus de 2h — à traiter urgemment",'link'=>route('boutique.orders.index'),'cta'=>'Voir les commandes']);
    $cmdALivrer = $shop->orders()->whereIn('status', ['confirmée', 'confirmed', 'processing'])->whereNull('driver_id')->whereNull('delivery_company_id')->count();
    if ($cmdALivrer > 0 && $livreursDisponibles->isEmpty()) $alerts->push(['type'=>'warning','ico'=>'⚠️','msg'=>"{$cmdALivrer} commande(s) prête(s) à livrer mais aucun livreur disponible",'link'=>route('delivery.companies.index'),'cta'=>'Trouver un livreur']);
    if (!$shop->is_approved) $alerts->push(['type'=>'warning','ico'=>'⏳','msg'=>"Votre boutique est en attente de validation par l'administrateur",'link'=>null,'cta'=>null]);
    if ($caTodayDelta >= 20 && $caToday > 0) $alerts->push(['type'=>'success','ico'=>'🎉','msg'=>"Excellente journée ! Vos revenus d'aujourd'hui sont en hausse de {$caTodayDelta}% vs hier",'link'=>null,'cta'=>null]);
    $kanban = [
        ['key'=>'en_attente',  'label'=>'En attente','count'=>$shop->orders()->whereIn('status',['pending','en attente','en_attente'])->count(),'color'=>'#f59e0b','bg'=>'#fffbeb','ico'=>'⏰'],
        ['key'=>'confirmees',  'label'=>'Confirmées','count'=>$shop->orders()->whereIn('status',['confirmed','confirmée','processing'])->count(),'color'=>'#10b981','bg'=>'#ecfdf5','ico'=>'✅'],
        ['key'=>'en_livraison','label'=>'En livraison','count'=>$shop->orders()->whereIn('status',['en_livraison','delivering','shipped'])->count(),'color'=>'#6366f1','bg'=>'#eef2ff','ico'=>'🚚'],
        ['key'=>'terminees',   'label'=>'Terminées','count'=>$shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->count(),'color'=>'#22c55e','bg'=>'#dcfce7','ico'=>'✅'],
        ['key'=>'annulees',    'label'=>'Annulées','count'=>$shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->whereIn('status',['annulée','cancelled'])->count(),'color'=>'#ef4444','bg'=>'#fef2f2','ico'=>'❌'],
    ];
    $hasLivreurs  = $livreursDisponibles->isNotEmpty();
    $hasCompanies = isset($deliveryCompanies) && $deliveryCompanies->isNotEmpty();
    $produitsRisque = $shop->products()
        ->withCount(['orderItems as ventes_mois' => function ($q) use ($now) {
            $q->whereHas('order', function ($o) use ($now) { $o->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year); });
        }])
        ->having('ventes_mois', '=', 0)->orderBy('created_at', 'desc')->take(5)->get();
    /* Résumé rapide */
    $totalProduits    = $shop->products()->count();
    $clientsActifsCount = $shop->orders()->distinct('user_id')->count('user_id');
    $livreursActifsCount = $livreursDisponibles->count();
    $partenairesCount = isset($deliveryCompanies) ? $deliveryCompanies->count() : 0;
    /* Mini bar chart commandes 7j */
    $cmdDays7 = collect(range(6,0))->map(function($i) use ($shop, $now, $_dayLabel) {
        $day = $now->copy()->subDays($i)->toDateString();
        $cnt = $shop->orders()->whereDate('created_at', $day)->whereNotIn('status',['annulée','cancelled'])->count();
        $d   = $now->copy()->subDays($i);
        return ['label' => $_dayLabel($d), 'count' => $cnt, 'today' => $i === 0];
    })->values();
    $maxCmd7 = $cmdDays7->max('count') ?: 1;
@endphp

{{-- ══ MODAL CHAT BOUTIQUE ↔ ENTREPRISE (depuis la cloche) ══ --}}
<div class="bq-chat-overlay" id="bqChatModal">
    <div class="bq-chat-panel">
        <div class="bq-chat-hd">
            <div class="bq-chat-hd-info">
                <div class="bq-chat-hd-av" id="bqChatAv">🏢</div>
                <div>
                    <div class="bq-chat-hd-name" id="bqChatName">Entreprise</div>
                    <div class="bq-chat-hd-sub">Discussion en cours</div>
                </div>
            </div>
            <button class="bq-chat-close" onclick="bqCloseChatModal()">✕</button>
        </div>
        {{-- Zone confier la livraison (au-dessus des messages pour ne pas masquer le dernier) --}}
        <div class="bq-confier-zone" id="bqConfierZone" style="display:none;">
            <div class="bq-confier-hint">📦 Confier une commande à cette entreprise</div>
            <div id="bqOrdersList" style="display:flex;flex-direction:column;gap:5px;max-height:150px;overflow-y:auto;margin-bottom:8px;scrollbar-width:thin;"></div>
            <div id="bqZonePickerWrap" style="display:none;margin-bottom:8px;">
                <label style="font-size:11px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px;">📍 Zone de livraison</label>
                <select id="bqZonePicker" style="width:100%;padding:9px 12px;border:1.5px solid #bbf7d0;border-radius:9px;font-size:13px;font-family:inherit;background:#fff;color:#0f172a;outline:none;cursor:pointer;" onchange="bqOnZonePick(this)">
                    <option value="">— Choisir une zone —</option>
                </select>
                <div id="bqZonePriceHint" style="display:none;margin-top:5px;padding:7px 10px;background:#f0fdf4;border-radius:7px;font-size:12px;font-weight:700;color:#065f46;">
                    💰 Prix : <span id="bqZonePriceVal"></span> · ⏱ <span id="bqZoneDelayVal"></span> min
                </div>
            </div>
            <button class="bq-btn-confier" id="bqBtnConfier" onclick="bqConfierLivraison()">
                📦 Confier la livraison à cette entreprise
            </button>
        </div>

        <div class="bq-chat-msgs" id="bqChatMsgList">
            <div class="bq-chat-empty" id="bqChatEmpty">Chargement…</div>
        </div>

        <div class="bq-chat-input-zone">
            <textarea class="bq-chat-textarea" id="bqChatInput" placeholder="Votre message…" rows="1"
                      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();bqSendMsg();}"></textarea>
            <button class="bq-chat-send-btn" id="bqChatSendBtn" onclick="bqSendMsg()" title="Envoyer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </div>
    </div>
</div>

<div class="dash-wrap" id="dashWrap">

    {{-- SIDEBAR --}}
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
            <a href="{{ route('boutique.dashboard') }}" class="sb-item active" style="margin-bottom:4px"><span class="ico">⊞</span> Tableau de bord</a> 
            <div class="sb-section">Boutique</div>
            <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">💬</span> Messages <span class="sb-badge" id="sbMsgBadge" style="display:none"></span></a>
            <a href="{{ route('boutique.orders.index') }}" class="sb-item"><span class="ico">📦</span> Commandes <span class="sb-badge" id="sbOrdersBadge" style="{{ $pendingCount > 0 ? '' : 'display:none' }}">{{ $pendingCount }}</span></a>
            <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">🏷️</span> Produits</a>
            <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">👥</span> Clients</a>
            <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">🧑‍💼</span> Équipe</a>
            <div class="sb-section">Livraison</div>
            <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">🚴</span> Livreurs <span class="sb-badge" id="sbLivreursBadge" style="{{ $livreursDisponibles->count() > 0 ? '' : 'display:none' }}">{{ $livreursDisponibles->count() }}</span></a>
            <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">🏢</span> Partenaires</a>
            <div class="sb-section">Finances</div>
            <div class="sb-group">
                <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                    <span class="ico">💰</span> Finances & Rapports <span class="sb-arrow">▶</span>
                </button>
                <div class="sb-sub">
                    <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">💳</span> Paiements</a>
                    <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">📊</span> Commissions</a>
                    <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">📋</span> Rapports</a>
                    @if(auth()->user()->role === 'admin')<a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">⚙️</span> Paramètres</a>@endif
                </div>
            </div>
            <div class="sb-section">Aide</div>
            <a href="{{ route('support.index') }}" class="sb-item"><span class="ico">🎧</span> Support</a>
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
                <button type="submit" class="sb-logout"><span class="ico">⎋</span> Se déconnecter</button>
            </form>
        </div>
    </aside>

    <div class="sb-overlay" id="sbOverlay"></div>

    <main class="main">

        {{-- ══ TOPBAR RESPONSIVE ══ --}}
        <div class="topbar">
            <button class="btn-hamburger" id="btnMenu" aria-label="Menu">☰</button>
            <div class="tb-info">
                <div class="tb-greeting">Bonjour, {{ auth()->user()->name }} 👋</div>
                <div class="tb-greeting-sub">Voici ce qui se passe avec votre boutique aujourd'hui.</div>
            </div>

            

            <div class="tb-actions">

                {{-- Mode sombre --}}
                <button class="tb-icon-btn" id="btnDarkMode" title="Mode sombre / clair">🌙</button>

                {{-- Cloche notifications temps réel --}}
                <div class="notif-bell-wrap" id="notifBellWrap" style="position:relative;display:inline-flex">
                    <button class="btn btn-sm" id="notifBellBtn" onclick="toggleNotifDropdown()" title="Notifications" style="position:relative;padding:6px 10px;font-size:15px">
                        🔔
                        <span id="notifBellCount" style="display:none;position:absolute;top:-5px;right:-5px;background:#ef4444;color:#fff;font-size:9px;font-weight:800;min-width:16px;height:16px;border-radius:20px;padding:0 3px;display:none;align-items:center;justify-content:center;border:2px solid #fff"></span>
                    </button>
                    <div id="notifDropdown" style="display:none;position:absolute;top:calc(100% + 8px);right:0;background:#fff;border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--shadow);width:280px;z-index:500;overflow:hidden">
                        <div style="padding:10px 14px;border-bottom:1px solid var(--border);font-size:12px;font-weight:700;color:var(--text);display:flex;align-items:center;justify-content:space-between">
                            🔔 Notifications <span id="notifDropdownTotal" style="background:var(--brand-lt);color:var(--brand-dk);font-size:10px;padding:1px 7px;border-radius:20px">0</span>
                        </div>
                        <div id="notifList" style="max-height:320px;overflow-y:auto;overflow-x:hidden;scrollbar-width:thin;scrollbar-color:#d1d5db #f9fafb"></div>
                        <div style="padding:8px 14px;border-top:1px solid var(--border);display:flex;gap:6px">
                            <a href="{{ route('boutique.orders.index') }}" class="btn btn-sm" style="flex:1;justify-content:center;font-size:11px">📦 Commandes</a>
                            <a href="{{ route('boutique.messages.hub') }}" class="btn btn-sm" style="flex:1;justify-content:center;font-size:11px">💬 Messages</a>
                        </div>
                    </div>
                </div>

                {{-- Bouton Messages (depuis partial) --}}
                @include('boutique._partials.messages_btn_and_drawer')

                {{-- Export grand écran --}}
                <div class="topbar-export-group">
                    <a href="{{ route('boutique.export.orders.excel') }}" class="btn btn-sm">⬇ Excel</a>
                    <a href="{{ route('boutique.export.orders.pdf') }}" class="btn btn-sm">⬇ PDF</a>
                </div>

                {{-- Export petit écran : dropdown --}}
                <div class="topbar-export-dropdown">
                    <button class="export-dropdown-btn" onclick="toggleExportMenu(this)" type="button">⬇ ▾</button>
                    <div class="export-menu" id="exportMenu">
                        <a href="{{ route('boutique.export.orders.excel') }}">📊 Excel</a>
                        <a href="{{ route('boutique.export.orders.pdf') }}">📄 PDF</a>
                    </div>
                </div>

                {{-- Nouvelle commande --}}
                <a href="{{ route('boutique.orders.index') }}" class="btn btn-primary btn-sm">
                    + <span class="btn-commande-label">Commande</span>
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @foreach(['success','info','warning','danger'] as $type)
            @if(session($type))
            <div class="flash flash-{{ $type }}">
                <span>{{ $type === 'success' ? '✓' : ($type === 'danger' ? '✕' : 'ℹ') }}</span>
                {{ session($type) }}
            </div>
            @endif
        @endforeach

        <div class="content">

            {{-- KPI Grid --}}
            <div class="kpi-grid" style="margin-bottom:22px">
                <div class="kpi"  style="--kpi-color:#8b5cf6;--kpi-bg:#f5f3ff">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);border-color:rgba(139,92,246,.3);box-shadow:0 0 0 3px rgba(139,92,246,.12),0 4px 14px rgba(139,92,246,.4)">💰</div>
                    <div class="kpi-lbl">Revenu net</div>
                    <div class="kpi-val" id="kpiCaVal">{{ number_format($caMonth,0,',',' ') }}</div>
                    <div class="kpi-unit">{{ $devise }} · {{ $_monthYear($now) }}</div>
                    <div class="kpi-delta {{ $caDelta >= 0 ? 'up':'down' }}" id="kpiCaDelta">{{ $caDelta >= 0 ? '↑':'↓' }} {{ abs($caDelta) }}% vs mois précédent</div>
                    @if($commissionsPaieesMonth > 0)
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:3px">
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--muted)"><span>CA brut</span><span style="font-family:var(--mono);color:var(--text-2)">{{ number_format($caGrossMonth,0,',',' ') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--muted)"><span>Commissions</span><span style="font-family:var(--mono);color:#dc2626">− {{ number_format($commissionsPaieesMonth,0,',',' ') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;font-weight:700;padding-top:3px;border-top:1px dashed var(--border)"><span style="color:var(--brand)">Net</span><span style="font-family:var(--mono);color:var(--brand)">{{ number_format($caMonth,0,',',' ') }}</span></div>
                    </div>
                    @endif
                </div>
                <div class="kpi" style="--kpi-color:#8b5cf6;--kpi-bg:#f5f3ff">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);border-color:rgba(139,92,246,.3);box-shadow:0 0 0 3px rgba(139,92,246,.12),0 4px 14px rgba(139,92,246,.4)">📦</div>
                    <div class="kpi-lbl">Commandes ce mois</div>
                    <div class="kpi-val" id="kpiCmdVal">{{ $cmdMonth }}</div>
                    <div class="kpi-unit">commandes</div>
                    <div class="kpi-delta {{ $cmdToday >= $cmdYest ? 'up':'down' }}" id="kpiCmdDelta">{{ $cmdToday >= $cmdYest ? '↑':'↓' }} {{ $cmdToday }} aujourd'hui</div>
                </div>
                <div class="kpi" style="--kpi-color:#d97706;--kpi-bg:#fffbeb">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#b45309);border-color:rgba(169, 141, 110, 0.3);box-shadow:0 0 0 3px rgba(217,119,6,.12),0 4px 14px rgba(147, 94, 33, 0.4)">🛒</div>
                    <div class="kpi-lbl">Panier moyen</div>
                    <div class="kpi-val" id="kpiPanierVal">{{ number_format($panier,0,',',' ') }}</div>
                    <div class="kpi-unit">{{ $devise }} / commande</div>
                    <div class="kpi-delta {{ $panierDelta >= 0 ? 'up':'down' }}" id="kpiPanierDelta">{{ $panierDelta >= 0 ? '↑':'↓' }} {{ abs($panierDelta) }}% vs mois précédent</div>
                </div>
                <div class="kpi" style="--kpi-color:#d97706;--kpi-bg:#fffbeb">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#b45309);border-color:rgba(217,119,6,.3);box-shadow:0 0 0 3px rgba(217,119,6,.12),0 4px 14px rgba(217,119,6,.4)">🚴</div>
                    <div class="kpi-lbl">Taux de livraison</div>
                    <div class="kpi-val" id="kpiTauxVal">{{ $tauxLiv }}%</div>
                    <div class="kpi-unit" id="kpiTauxUnit">{{ $livres }} / {{ $totalCmdMonth }} livrées</div>
                    <div class="kpi-delta {{ $tauxLiv >= 90 ? 'up':'down' }}" id="kpiTauxDelta">{{ $tauxLiv >= 90 ? '✓ Excellent':'⚠ À améliorer' }}</div>
                </div>
            </div>


            {{-- BLOC A --}}
            <div class="today-grid">
                <div class="today-card">
                    <div class="today-icon">💵</div>
                    <div style="flex:1;min-width:0">
                        <div class="today-lbl">Revenu net aujourd'hui</div>
                        <div class="today-val" id="todayCaVal">{{ number_format($caToday, 0, ',', ' ') }}</div>
                        <div class="today-unit">{{ $devise }}</div>
                        <div class="today-delta {{ $caTodayDelta > 0 ? 'up' : ($caTodayDelta < 0 ? 'down' : 'flat') }}" id="todayCaDelta">
                            @if($caTodayDelta > 0) ↑ +{{ $caTodayDelta }}% vs hier
                            @elseif($caTodayDelta < 0) ↓ {{ $caTodayDelta }}% vs hier
                            @else — Même niveau qu'hier @endif
                        </div>
                        @if($commToday > 0)
                        <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.12);display:flex;flex-direction:column;gap:3px">
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.4)"><span>CA brut</span><span style="font-family:var(--mono);color:rgba(255,255,255,.6)">{{ number_format($caGrossToday,0,',',' ') }}</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.4)"><span>Commissions</span><span style="font-family:var(--mono);color:#fca5a5">− {{ number_format($commToday,0,',',' ') }}</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:10px;font-weight:700;padding-top:3px;border-top:1px dashed rgba(255,255,255,.1)"><span style="color:#a5b4fc">Net</span><span style="font-family:var(--mono);color:#a5b4fc">{{ number_format($caToday,0,',',' ') }}</span></div>
                        </div>
                        @endif
                    </div>
                    {{-- Wave sparkline décoration --}}
                    <div class="today-wave">
                        <svg viewBox="0 0 200 100" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0,70 C20,70 25,30 50,40 C75,50 80,20 100,25 C120,30 130,60 150,50 C170,40 180,15 200,20 L200,100 L0,100 Z" fill="rgba(255,255,255,.12)"/>
                            <path d="M0,80 C25,80 30,50 55,58 C80,66 90,35 115,42 C140,49 150,68 175,60 C190,55 200,35 210,30 L210,100 L0,100 Z" fill="rgba(255,255,255,.07)"/>
                            <path d="M0,70 C20,70 25,30 50,40 C75,50 80,20 100,25 C120,30 130,60 150,50 C170,40 180,15 200,20" stroke="rgba(255,255,255,.35)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <div class="today-card" style="background:linear-gradient(135deg, #0c1a35 0%, #0f2850 40%, #0d3060 100%); box-shadow:0 8px 32px rgba(15,40,80,.4), 0 2px 8px rgba(0,0,0,.25); border-color:rgba(59,130,246,.25)">
                    <div class="today-icon">📦</div>
                    <div>
                        <div class="today-lbl">Commandes aujourd'hui</div>
                        <div class="today-val" id="todayCmdVal">{{ $cmdToday }}</div>
                        <div class="today-unit">commandes reçues</div>
                        <div class="today-delta {{ $cmdToday >= $cmdYest ? 'up' : 'down' }}" id="todayCmdDelta">
                            @if($cmdToday > $cmdYest) ↑ +{{ $cmdToday - $cmdYest }} vs hier
                            @elseif($cmdToday < $cmdYest) ↓ {{ $cmdYest - $cmdToday }} de moins vs hier
                            @else — Même niveau qu'hier @endif
                        </div>
                    </div>
                    {{-- Wave sparkline décoration --}}
                    <div class="today-wave">
                        <svg viewBox="0 0 200 100" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0,60 C15,60 25,80 50,65 C75,50 85,25 110,35 C135,45 140,70 165,55 C182,44 195,20 210,25 L210,100 L0,100 Z" fill="rgba(99,102,241,.18)"/>
                            <path d="M0,75 C20,75 30,90 55,78 C80,66 90,42 115,50 C140,58 150,78 175,68 C192,61 200,42 210,38 L210,100 L0,100 Z" fill="rgba(99,102,241,.10)"/>
                            <path d="M0,60 C15,60 25,80 50,65 C75,50 85,25 110,35 C135,45 140,70 165,55 C182,44 195,20 210,25" stroke="rgba(99,102,241,.55)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- BLOC B --}}
            @if($alerts->isNotEmpty())
            <div class="alerts-zone">
                @foreach($alerts as $alert)
                <div class="alert-item {{ $alert['type'] }}">
                    <span class="alert-ico">{{ $alert['ico'] }}</span>
                    <span class="alert-msg">{{ $alert['msg'] }}</span>
                    @if($alert['link'])<a href="{{ $alert['link'] }}" class="alert-cta">{{ $alert['cta'] }} →</a>@endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- BLOC C KANBAN --}}
            <div class="kanban-section">
                <div class="card-hd" style="padding:0 0 12px;border:none;background:transparent">
                    <span class="card-title" style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Pipeline commandes</span>
                    <a href="{{ route('boutique.orders.index') }}" class="btn btn-ghost btn-sm">Gérer les commandes →</a>
                </div>
                <div class="kanban-grid">
                    @foreach($kanban as $col)
                    <div class="kanban-col {{ $col['count'] > 0 ? 'has-items' : '' }}" style="--k-color:{{ $col['color'] }};--k-bg:{{ $col['bg'] }}">
                        <div class="kanban-ico-wrap">{{ $col['ico'] }}</div>
                        <div class="kanban-count" id="kb-{{ $col['key'] }}">{{ $col['count'] }}</div>
                        <div class="kanban-lbl">{{ $col['label'] }}</div>
                        @if($col['label'] === 'Terminées')<div style="font-size:9px;color:var(--muted);margin-top:3px;font-weight:600">ce mois</div>@endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- BLOC D ACTIONS RAPIDES --}}
            <div class="quick-section">
                <div class="quick-card">
                    <div class="quick-card-hd">
                        <div class="quick-card-title"><span class="title-ico">⚡</span> Actions rapides</div>
                        <span style="font-size:11px;color:var(--muted)">Accès direct aux tâches courantes</span>
                    </div>
                    <div class="quick-grid">
                        <a href="{{ route('boutique.orders.index') }}" class="quick-btn" style="--q-bg:#fffbeb;--q-border:#fde68a;--q-color:#f59e0b"><div class="quick-btn-ico">📋</div><div class="quick-btn-lbl">Commandes</div><div class="quick-btn-sub">Voir & gérer</div></a>
                        <a href="{{ route('products.create') }}" class="quick-btn" style="--q-bg:#eef2ff;--q-border:#a5b4fc;--q-color:#6366f1"><div class="quick-btn-ico">➕</div><div class="quick-btn-lbl">Nouveau produit</div><div class="quick-btn-sub">Ajouter au catalogue</div></a>
                        <a href="{{ route('boutique.livreurs.index') }}" class="quick-btn" style="--q-bg:#f5f3ff;--q-border:#c4b5fd;--q-color:#8b5cf6"><div class="quick-btn-ico">🚴</div><div class="quick-btn-lbl">Livreurs</div><div class="quick-btn-sub">Voir en ligne</div></a>
                        <a href="{{ route('boutique.payments.index') }}" class="quick-btn" style="--q-bg:#eff6ff;--q-border:#93c5fd;--q-color:#3b82f6"><div class="quick-btn-ico">💳</div><div class="quick-btn-lbl">Paiements</div><div class="quick-btn-sub">Revenus reçus</div></a>
                    </div>
                </div>
            </div>

            {{-- CHARTS DUO : Revenus + Commandes 7j --}}
            {{-- ===================== REVENUE CHART 7J ===================== --}}
            @php
                $W = 660; $H = 160;
                $pL = 52; $pR = 16; $pT = 16; $pB = 28;
                $iW = $W - $pL - $pR;
                $iH = $H - $pT - $pB;
                $n7 = count($days7);
                $pts = [];
                foreach ($days7 as $i => $day) {
                    $px = $pL + ($n7 > 1 ? ($i / ($n7 - 1)) * $iW : $iW / 2);
                    $py = $pT + $iH - ($max7 > 0 ? ($day['value'] / $max7) * $iH : 0);
                    $pts[] = ['x' => round($px,2), 'y' => round($py,2), 'v' => $day['value'], 'lbl' => $day['label'], 'today' => $day['today']];
                }
                $polyline   = implode(' ', array_map(fn($p) => $p['x'].','.$p['y'], $pts));
                $areaPoints = ($pts[0]['x'].','.(  $pT+$iH).' '.$polyline.' '.$pts[$n7-1]['x'].','.(  $pT+$iH));
                $week7Total = $days7->sum('value');
                $bestDay    = $days7->sortByDesc('value')->first();
                // Y-axis labels: 0, mid, max
                $yGrid = [
                    ['val' => $max7,     'y' => $pT],
                    ['val' => $max7 / 2, 'y' => $pT + $iH / 2],
                    ['val' => 0,         'y' => $pT + $iH],
                ];
                function rcFmt($n) {
                    if ($n >= 1000000) return round($n/1000000,1).'M';
                    if ($n >= 1000)    return round($n/1000).'k';
                    return round($n);
                }
            @endphp
            <div class="charts-duo">
            <div class="card chart-wrap" style="margin-bottom:0">
                {{-- Header --}}
                <div class="rc-header">
                    <div class="rc-header-left">
                        <div class="rc-title">Revenus — 7 derniers jours</div>
                        <div class="rc-total">
                            <sup>{{ $devise }} </sup>{{ number_format($week7Total, 0, ',', ' ') }}
                        </div>
                        @php
                            $prevWeek  = $prev7Total;
                            $todayEntry = $days7->firstWhere('today', true);
                            $todayVal   = $todayEntry['value'] ?? 0;
                            $n7c        = $days7->count();
                            $yesterVal  = $n7c >= 2 ? $days7->get($n7c - 2)['value'] : 0;
                            $rcDelta    = $yesterVal > 0 ? round((($todayVal - $yesterVal) / $yesterVal) * 100, 1) : ($todayVal > 0 ? 100 : 0);
                        @endphp
                        <div id="rcDeltaBadge" class="rc-delta {{ $rcDelta > 0 ? 'up' : ($rcDelta < 0 ? 'down' : 'flat') }}">
                            @if($rcDelta > 0) ↑ +{{ $rcDelta }}% aujourd'hui vs hier
                            @elseif($rcDelta < 0) ↓ {{ $rcDelta }}% aujourd'hui vs hier
                            @else → Stable aujourd'hui
                            @endif
                        </div>
                    </div>
                    <div class="rc-header-right">
                        <div style="margin-bottom:2px">Meilleure journée</div>
                        <div class="rc-best">{{ $bestDay['label'] }} — {{ number_format($bestDay['value'],0,',',' ') }} {{ $devise }}</div>
                        <div style="margin-top:6px">Moy. / jour</div>
                        <div style="font-weight:700;color:var(--text);font-size:12px">{{ number_format($week7Total/7,0,',',' ') }} {{ $devise }}</div>
                    </div>
                </div>

                {{-- SVG Chart --}}
                <div class="rc-svg-wrap">
                    <div class="rc-tooltip" id="rcTip">
                        <span id="rcTipDay"></span>
                        <strong id="rcTipVal"></strong>
                    </div>
                    <svg viewBox="0 0 {{ $W }} {{ $H }}" width="100%" height="160" preserveAspectRatio="none" overflow="visible" style="display:block">
                        <defs>
                            <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%"   stop-color="#8b5cf6" stop-opacity=".38"/>
                                <stop offset="45%"  stop-color="#6366f1" stop-opacity=".14"/>
                                <stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="lineGrad" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0%"   stop-color="#8b5cf6"/>
                                <stop offset="100%" stop-color="#6366f1"/>
                            </linearGradient>
                            <filter id="glowLine" x="-10%" y="-80%" width="120%" height="260%">
                                <feGaussianBlur stdDeviation="3.5" result="blur"/>
                                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                            </filter>
                            <filter id="glowDot" x="-80%" y="-80%" width="260%" height="260%">
                                <feGaussianBlur stdDeviation="4" result="blur"/>
                                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                            </filter>
                        </defs>

                        {{-- Y gridlines premium --}}
                        @foreach($yGrid as $g)
                        <line x1="{{ $pL }}" y1="{{ $g['y'] }}" x2="{{ $W - $pR }}" y2="{{ $g['y'] }}"
                              stroke="rgba(99,102,241,.12)" stroke-width="1" stroke-dasharray="{{ $g['val'] > 0 ? '5 6' : 'none' }}"/>
                        <text x="{{ $pL - 8 }}" y="{{ $g['y'] + 4 }}" text-anchor="end"
                              font-size="9" fill="#94a3b8" font-family="monospace">{{ rcFmt($g['val']) }}</text>
                        @endforeach

                        {{-- Area fill --}}
                        <polygon points="{{ $areaPoints }}" fill="url(#areaGrad)"/>

                        {{-- Glow line shadow --}}
                        <polyline points="{{ $polyline }}" fill="none" stroke="rgba(139,92,246,.35)" stroke-width="6" stroke-linejoin="round" stroke-linecap="round"/>
                        {{-- Line --}}
                        <polyline points="{{ $polyline }}" fill="none" stroke="url(#lineGrad)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>

                        {{-- Points + hit areas --}}
                        @foreach($pts as $i => $p)
                        @if($p['today'])
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="9" fill="rgba(139,92,246,.18)" stroke="none"/>
                        @endif
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $p['today'] ? 5 : 3.5 }}"
                                fill="{{ $p['today'] ? '#8b5cf6' : '#fff' }}"
                                stroke="{{ $p['today'] ? '#c4b5fd' : '#6366f1' }}" stroke-width="{{ $p['today'] ? 2.5 : 1.8 }}"
                                class="rc-pt"
                                data-val="{{ $p['v'] }}"
                                data-lbl="{{ $p['lbl'] }}"
                                data-today="{{ $p['today'] ? '1' : '0' }}"/>
                        {{-- invisible wide hit area --}}
                        <rect x="{{ $p['x'] - 20 }}" y="{{ $pT }}" width="40" height="{{ $iH }}"
                              fill="transparent"
                              class="rc-hit"
                              data-idx="{{ $i }}"/>
                        @endforeach
                    </svg>

                    {{-- X-axis day labels --}}
                    <div class="rc-day-dots" style="margin-left:{{ $pL }}px;margin-right:{{ $pR }}px">
                        @foreach($days7 as $day)
                        <div class="rc-day-dot {{ $day['today'] ? 'today' : '' }}">{{ $day['label'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Mini bar chart : Commandes 7j --}}
            <div class="card" style="margin-bottom:0">
                <div class="mini-chart-hd">
                    <div>
                        <div class="mini-chart-lbl">Commandes</div>
                        <div class="mini-chart-val">{{ $cmdDays7->sum('count') }}</div>
                        <div class="mini-chart-sub">7 derniers jours</div>
                        @php
                            $cmdToday7  = $cmdDays7->last()['count'] ?? 0;
                            $cmdYest7   = $cmdDays7->count() >= 2 ? $cmdDays7->get($cmdDays7->count()-2)['count'] : 0;
                            $cmdDelta7  = $cmdToday7 - $cmdYest7;
                        @endphp
                        <div id="cmdDeltaBadge" class="mini-delta {{ $cmdDelta7 > 0 ? 'up' : ($cmdDelta7 < 0 ? 'down' : 'flat') }}">
                            @if($cmdDelta7 > 0) ↑ {{ $cmdToday7 }} aujourd'hui
                            @elseif($cmdDelta7 < 0) ↓ {{ $cmdToday7 }} aujourd'hui
                            @else → {{ $cmdToday7 }} aujourd'hui
                            @endif
                        </div>
                    </div>
                    <span class="mini-period-badge">30 derniers jours ▾</span>
                </div>
                <div class="mini-bars-wrap">
                    @foreach($cmdDays7 as $cd)
                    @php $hPct = $maxCmd7 > 0 ? max(round(($cd['count']/$maxCmd7)*100),2) : 2; @endphp
                    <div class="mini-bar-col">
                        <div class="mini-bar {{ $cd['today'] ? 'actuel' : '' }}"
                             data-h="{{ $hPct }}"
                             style="height:0%;{{ $cd['today'] ? 'opacity:1' : '' }}"></div>
                    </div>
                    @endforeach
                </div>
                <div class="mini-bar-labs">
                    @foreach($cmdDays7 as $cd)
                    <div class="mini-bar-lbl" style="flex:1;text-align:center;{{ $cd['today'] ? 'color:var(--brand);font-weight:700' : '' }}">{{ $cd['label'] }}</div>
                    @endforeach
                </div>
            </div>
            </div>{{-- /charts-duo --}}

            {{-- CONTENT GRID --}}
            <div class="content-grid">
                <div class="card">
                    <div class="card-hd"><span class="card-title">Commandes récentes</span><a href="{{ route('boutique.orders.index') }}" class="btn-ghost btn btn-sm">Voir tout →</a></div>
                    <div class="tbl-wrap" style="padding:0 18px">
                        @if($recentOrders->isEmpty())<div style="padding:28px 0;text-align:center;font-size:13px;color:var(--muted)">Aucune commande pour le moment.</div>
                        @else
                        <table class="tbl">
                            <thead><tr><th>Réf / Client</th><th>Statut</th><th style="text-align:right">Montant</th></tr></thead>
                            <tbody>
                            @foreach($recentOrders as $order)
                            @php $st = $statusMap[$order->status] ?? ['label'=>ucfirst($order->status),'cls'=>'p-muted']; @endphp
                            <tr>
                                <td><div class="oid">#{{ $order->id }}</div><div class="onam">{{ $order->user->name ?? 'Client inconnu' }}</div></td>
                                <td><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span></td>
                                <td class="oamt">{{ number_format($order->total,0,',',' ') }} <span style="font-size:9px;color:var(--muted)"> {{ $devise }}</span></td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>
                <div class="right-col">
                    @if($hasLivreurs)
                    <div class="card delivery-card">
                        {{-- Hero header --}}
                        <div class="delivery-card-hero">
                            <div class="delivery-card-hero-left">
                                <div class="delivery-card-icon">🚴</div>
                                <div>
                                    <div class="delivery-card-title">Livraison</div>
                                    <div class="delivery-card-sub">{{ $livreursDisponibles->count() }} livreur(s) disponible(s)</div>
                                </div>
                            </div>
                            <a href="{{ route('boutique.employees.index') }}" class="delivery-card-manage">Gérer →</a>
                        </div>
                        {{-- Tabs --}}
                        <div class="tab-bar">
                            <button class="tab-btn active" data-tab="livreurs">🚴 Livreurs <span class="tab-count">{{ $livreursDisponibles->count() }}</span></button>
                            @if($hasCompanies)<button class="tab-btn" data-tab="companies">🏢 Entreprises <span class="tab-count {{ $deliveryCompanies->count() === 0 ? 'zero':'' }}">{{ $deliveryCompanies->count() }}</span></button>@endif
                        </div>
                        {{-- Liste livreurs --}}
                        <div class="tab-panel active" id="tab-livreurs">
                            <div class="lv-list">
                                @foreach($livreursDisponibles->take(5) as $i => $livreur)
                                @php
                                    $lp    = explode(' ', $livreur->name);
                                    $linit = strtoupper(substr($lp[0],0,1)).(isset($lp[1]) ? strtoupper(substr($lp[1],0,1)) : strtoupper(substr($lp[0],1,1)));
                                    $lcol  = $avColors[$i % count($avColors)];
                                    $busy  = !empty($livreur->current_order_id);
                                    $waNum = preg_replace('/\D/', '', $livreur->phone ?? '');
                                @endphp
                                <div class="lv-row">
                                    <div class="lv-av" style="background:{{ $lcol }}">{{ $linit }}</div>
                                    <div class="lv-info">
                                        <div class="lv-nm">{{ $livreur->name }}</div>
                                        @if($livreur->phone)
                                            <div class="lv-phone">
                                                <span>📞</span>
                                                <a href="tel:{{ $livreur->phone }}" style="color:inherit;text-decoration:none">{{ $livreur->phone }}</a>
                                            </div>
                                        @else
                                            <div class="lv-phone-warn">⚠️ Pas de téléphone</div>
                                        @endif
                                    </div>
                                    <span class="lv-status-badge {{ $busy ? 'busy' : 'available' }}">{{ $busy ? 'En course' : 'Dispo' }}</span>
                                    @if($waNum)
                                    <a href="https://wa.me/{{ $waNum }}" target="_blank" class="lv-wa-btn" title="Contacter par WhatsApp">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </a>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @if($livreursDisponibles->count() > 5)
                            <div class="lv-footer">
                                <a href="{{ route('boutique.employees.index') }}">+ {{ $livreursDisponibles->count()-5 }} autre(s) livreur(s) →</a>
                            </div>
                            @endif
                        </div>
                        @if($hasCompanies)
                        <div class="tab-panel" id="tab-companies">
                            <div class="co-list">
                                @foreach($deliveryCompanies->take(4) as $company)
                                <div class="co-row" onclick="window.location='{{ route('company.chat.show', $company) }}'" title="Ouvrir la discussion">
                                    <div class="co-logo">@if(!empty($company->logo))<img src="{{ asset('storage/'.$company->logo) }}" alt="{{ $company->name }}">@else 🚚 @endif</div>
                                    <div class="co-info"><div class="co-nm">{{ $company->name }}</div><div class="co-mt">{{ $company->phone ?? 'Contact non renseigné' }}</div></div>
                                    @if($company->commission_rate)<span class="co-commission">{{ number_format($company->commission_rate*100,1) }}%</span>@endif
                                    <a href="{{ route('company.chat.show', $company) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation()">💬 Contacter</a>
                                </div>
                                @endforeach
                            </div>
                            @if($deliveryCompanies->count() > 4)<div class="lv-footer"><a href="{{ route('delivery.companies.index') }}">Voir toutes →</a></div>@endif
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="card delivery-card">
                        {{-- Hero header vide --}}
                        <div class="delivery-card-hero">
                            <div class="delivery-card-hero-left">
                                <div class="delivery-card-icon">🚚</div>
                                <div>
                                    <div class="delivery-card-title">Livraison</div>
                                    <div class="delivery-card-sub">Aucun livreur actif</div>
                                </div>
                            </div>
                            <a href="{{ route('boutique.employees.create') }}" class="delivery-card-manage">+ Ajouter →</a>
                        </div>
                        @if(!$hasCompanies)
                        <div class="no-livreur-notice">
                            <div class="notice-icon">🚴</div>
                            <div class="notice-title">Aucun livreur disponible</div>
                            <div class="notice-sub">Ajoutez vos propres livreurs dans <strong>Équipe</strong>, ou contactez une entreprise partenaire.</div>
                            <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;justify-content:center">
                                <a href="{{ route('boutique.employees.create') }}" class="btn btn-sm">👥 Ajouter un livreur</a>
                                <a href="{{ route('delivery.companies.index') }}" class="btn btn-primary btn-sm">🏢 Entreprise partenaire</a>
                            </div>
                        </div>
                        @else
                        <div style="padding:12px 18px;background:#fffbeb;border-bottom:1px solid #fde68a;display:flex;align-items:flex-start;gap:10px">
                            <span style="font-size:18px;flex-shrink:0">⚠️</span>
                            <div><div style="font-size:12.5px;color:#92400e;font-weight:700;margin-bottom:3px">Vous n'avez pas de livreurs</div><div style="font-size:11.5px;color:#b45309;line-height:1.55">Contactez une entreprise partenaire ci-dessous. Cliquez sur <strong>💬 Contacter</strong> pour ouvrir une discussion.</div></div>
                        </div>
                        <div class="co-list">
                            @foreach($deliveryCompanies->take(4) as $company)
                            <div class="co-row" onclick="window.location='{{ route('company.chat.show', $company) }}'" title="Ouvrir la discussion">
                                <div class="co-logo">@if(!empty($company->logo))<img src="{{ asset('storage/'.$company->logo) }}" alt="{{ $company->name }}">@else 🚚 @endif</div>
                                <div class="co-info"><div class="co-nm">{{ $company->name }}</div><div class="co-mt">{{ $company->phone ?? 'Contact non renseigné' }}</div></div>
                                @if($company->commission_rate)<span class="co-commission">{{ number_format($company->commission_rate*100,1) }}%</span>@endif
                                <a href="{{ route('company.chat.show', $company) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation()">💬 Contacter</a>
                            </div>
                            @endforeach
                        </div>
                        @if($deliveryCompanies->count() > 4)<div style="padding:10px 18px;text-align:center"><a href="{{ route('delivery.companies.index') }}" class="btn btn-ghost btn-sm">Voir toutes les entreprises →</a></div>@endif
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- TOP PRODUITS --}}
            @if($topProducts->isNotEmpty())
            <div class="card">
                <div class="card-hd"><span class="card-title" style="cursor:pointer;" onclick="window.location='{{ route('products.top') }}'">Top produits — ventes du mois 🏆</span><a href="{{ route('products.top') }}" class="btn btn-ghost btn-sm">Voir le classement →</a></div>
                <div class="card-bd">
                    @foreach($topProducts as $product)
                    @php $pct = round(($product->order_items_count / $maxSales)*100); @endphp
                    <div class="sp-row">
                        <span class="sp-lbl" title="{{ $product->name }}">{{ Str::limit($product->name, 18) }}</span>
                        <div class="sp-track"><div class="sp-fill" data-pct="{{ $pct }}" style="width:0%"></div></div>
                        <span class="sp-val">{{ $product->order_items_count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- PRODUITS A RISQUE --}}
            <div class="card" style="margin-bottom:22px">
                <div class="card-hd"><span class="card-title">⚠️ Produits à risque — 0 vente ce mois</span><a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Gérer les produits →</a></div>
                @if($produitsRisque->isEmpty())
                    <div class="risk-empty"><span class="ico">🎉</span>Tous vos produits ont été vendus ce mois !</div>
                @else
                <div style="display:flex;flex-wrap:wrap;gap:0;padding:0">
                    @foreach($produitsRisque as $product)
                    <div class="risk-row" style="flex:1;min-width:180px;border-right:1px solid #f3f6f4;border-bottom:none">
                        @if(!empty($product->image))<img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="risk-img">
                        @else<div class="risk-img-placeholder">🏷️</div>@endif
                        <div class="risk-info">
                            <div class="risk-name" title="{{ $product->name }}">{{ Str::limit($product->name, 20) }}</div>
                            <div class="risk-meta">{{ $product->price ? number_format($product->price,0,',',' ').' '.$devise : 'Prix non défini' }}</div>
                        </div>
                        <span class="risk-badge">0 vente</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- SÉLECTEUR DE PÉRIODE + RÉSUMÉ RAPIDE --}}
            <div class="perf-grid">
            <div class="card period-card" id="periodCard" style="margin-bottom:0">
                <div class="card-hd">
                    <span class="card-title">📅 Analyse par période</span>
                    <span class="period-label" id="periodLabel">Période : <strong>Ce mois</strong></span>
                </div>
                <div class="period-btns">
                    <button class="period-btn" data-period="yesterday">Hier</button>
                    <button class="period-btn" data-period="today">Aujourd'hui</button>
                    <button class="period-btn" data-period="7days">7 derniers jours</button>
                    <button class="period-btn" data-period="30days">30 derniers jours</button>
                    <button class="period-btn active" data-period="this_month">Ce mois</button>
                    <button class="period-btn" data-period="last_month">Mois dernier</button>
                    <button class="period-btn" data-period="this_year">Cette année</button>
                    <button class="period-btn" data-period="last_year">Année dernière</button>
                </div>
                <div class="period-loading" id="periodLoading"><div class="spin"></div> Chargement…</div>
                <div id="periodStatsWrap">
                    <div class="period-stats">
                        <div class="period-stat">
                            <div class="period-stat-lbl">Chiffre d'affaires</div>
                            <div class="period-stat-val" id="pCA" title="" style="cursor:help">—</div>
                            <div class="period-stat-sub">{{ $devise }}</div>
                            <div id="pCA-full" style="font-size:10px;color:var(--brand);font-family:var(--mono);margin-top:3px;display:none;font-weight:600"></div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Commandes</div>
                            <div class="period-stat-val" id="pCMD">—</div>
                            <div class="period-stat-sub">commandes</div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Panier moyen</div>
                            <div class="period-stat-val" id="pPANIER" title="" style="cursor:help">—</div>
                            <div class="period-stat-sub">{{ $devise }} / cmd</div>
                            <div id="pPANIER-full" style="font-size:10px;color:var(--brand);font-family:var(--mono);margin-top:3px;display:none;font-weight:600"></div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Taux livraison</div>
                            <div class="period-stat-val" id="pTAUX">—</div>
                            <div class="period-stat-sub">% livrées</div>
                        </div>
                    </div>
                    <div class="period-chart">
                        <div class="period-bars" id="periodBars"></div>
                        <div class="period-bar-labels" id="periodLabels"></div>
                    </div>
                </div>
            </div>

            {{-- Résumé rapide --}}
            <div class="card" style="margin-bottom:0">
                <div class="card-hd"><span class="card-title">📊 Résumé rapide</span></div>
                <div class="card-bd">
                    <div class="resume-items">
                        <div class="resume-item">
                            <div class="resume-item-ico">🛒</div>
                            <div>
                                <div class="resume-item-val">{{ $totalProduits }}</div>
                                <div class="resume-item-lbl">Total produits</div>
                            </div>
                        </div>
                        <div class="resume-item">
                            <div class="resume-item-ico">👥</div>
                            <div>
                                <div class="resume-item-val">{{ $clientsActifsCount }}</div>
                                <div class="resume-item-lbl">Clients actifs</div>
                            </div>
                        </div>
                        <div class="resume-item">
                            <div class="resume-item-ico">🚴</div>
                            <div>
                                <div class="resume-item-val">{{ $livreursActifsCount }}</div>
                                <div class="resume-item-lbl">Livreurs actifs</div>
                            </div>
                        </div>
                        <div class="resume-item">
                            <div class="resume-item-ico">🏢</div>
                            <div>
                                <div class="resume-item-val">{{ $partenairesCount }}</div>
                                <div class="resume-item-lbl">Partenaires</div>
                            </div>
                        </div>
                        <div class="resume-item" style="border-color:var(--brand-lt);background:var(--brand-mlt)">
                            <div class="resume-item-ico" style="background:var(--brand-mlt);border-color:var(--brand-lt)">💰</div>
                            <div>
                                <div class="resume-item-val" style="color:var(--brand);font-size:16px">{{ number_format($caMonth/1000000,1) }}M</div>
                                <div class="resume-item-lbl" style="color:var(--brand-dk)">Revenu net ce mois</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>{{-- /perf-grid --}}

        </div>{{-- /content --}}
    </main>
</div>{{-- /dash-wrap --}}

@push('scripts')
<script>
/* SIDEBAR */
function toggleGroup(btn) {
    const sub = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');
    document.querySelectorAll('.sb-sub.open').forEach(s => { s.classList.remove('open'); s.previousElementSibling?.classList.remove('open'); });
    if (!isOpen) {
        sub.classList.add('open'); btn.classList.add('open');
        const sidebar = document.getElementById('sidebar');
        setTimeout(() => { const support = sidebar?.querySelector('a[href*="support"]'); if (support && sidebar) support.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }, 220);
    }
    setTimeout(() => {
        const sidebar = document.getElementById('sidebar'); const scrollHint = document.getElementById('sbScrollHint');
        if (!sidebar || !scrollHint) return;
        scrollHint.classList.toggle('hidden', sidebar.scrollTop + sidebar.clientHeight >= sidebar.scrollHeight - 16);
    }, 300);
}
document.querySelectorAll('.sb-sub .sb-item.active').forEach(item => {
    const sub = item.closest('.sb-sub');
    if (sub) { sub.classList.add('open'); sub.previousElementSibling?.classList.add('open'); }
});

/* EXPORT DROPDOWN */
function toggleExportMenu(btn) {
    const menu = document.getElementById('exportMenu');
    menu.classList.toggle('open');
}
document.addEventListener('click', e => {
    if (!e.target.closest('.topbar-export-dropdown')) document.getElementById('exportMenu')?.classList.remove('open');
});

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sbOverlay');
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

    /* Tabs livraison */
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab; const card = btn.closest('.delivery-card');
            card.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            card.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            const panel = document.getElementById('tab-' + tabId); if (panel) panel.classList.add('active');
        });
    });

    /* Revenue Chart 7j — tooltip */
    (function() {
        const tip    = document.getElementById('rcTip');
        const tipDay = document.getElementById('rcTipDay');
        const tipVal = document.getElementById('rcTipVal');
        const DEVISE = '{{ $devise }}';
        const pts    = document.querySelectorAll('.rc-pt');
        const hits   = document.querySelectorAll('.rc-hit');

        function showTip(pt, rect) {
            const v = parseInt(pt.dataset.val);
            tipDay.textContent = pt.dataset.lbl + (pt.dataset.today === '1' ? " (aujourd'hui)" : '');
            tipVal.textContent = v.toLocaleString('fr-FR') + ' ' + DEVISE;
            tip.style.opacity  = '1';
            // position relative to svg wrap
            const wrap = tip.parentElement.getBoundingClientRect();
            const cx   = rect ? rect.left + rect.width/2 - wrap.left : 0;
            const cy   = rect ? rect.top - wrap.top - 60 : 0;
            tip.style.left = Math.max(0, cx - tip.offsetWidth/2) + 'px';
            tip.style.top  = Math.max(0, cy) + 'px';
        }

        pts.forEach(pt => {
            pt.addEventListener('mouseenter', function(e) {
                this.setAttribute('r', '6');
                showTip(this, this.getBoundingClientRect());
            });
            pt.addEventListener('mouseleave', function() {
                this.setAttribute('r', '4');
                tip.style.opacity = '0';
            });
        });

        hits.forEach((hit, i) => {
            const pt = pts[i];
            if (!pt) return;
            hit.addEventListener('mouseenter', () => pt.dispatchEvent(new Event('mouseenter')));
            hit.addEventListener('mouseleave', () => pt.dispatchEvent(new Event('mouseleave')));
        });
    })();

    /* Sparklines produits */
    document.querySelectorAll('.sp-fill').forEach((el, i) => { setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 100 + i * 90); });

    /* Mini bars commandes */
    document.querySelectorAll('.mini-bar').forEach((bar, i) => {
        setTimeout(() => { bar.style.transition = 'height .5s cubic-bezier(.23,1,.32,1)'; bar.style.height = bar.dataset.h + '%'; }, 100 + i * 60);
    });

    /* Dark mode toggle */
    const btnDark = document.getElementById('btnDarkMode');
    if (btnDark) {
        btnDark.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            btnDark.textContent = document.documentElement.classList.contains('dark') ? '☀️' : '🌙';
        });
    }

    /* Search bar (Ctrl+K) */
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); }
    });

    /* Période */
    const periodLabels = { yesterday:'Hier', today:"Aujourd'hui", '7days':'7 derniers jours', '30days':'30 derniers jours', this_month:'Ce mois', last_month:'Mois dernier', this_year:'Cette année', last_year:'Année dernière' };
    const DEVISE = '{{ $devise }}';

    function fmt(n) {
        if (n >= 1_000_000) return (n / 1_000_000).toFixed(2).replace(/\.?0+$/, '') + 'M';
        return Math.round(n).toLocaleString('fr-FR');
    }

    function drawBars(points) {
        const barsEl   = document.getElementById('periodBars');
        const labelsEl = document.getElementById('periodLabels');
        if (!barsEl || !labelsEl) return;
        const max = Math.max(...points.map(p => p.ca), 1);

        barsEl.innerHTML = points.map(p => {
            const h     = p.ca > 0 ? Math.max(Math.round((p.ca / max) * 100), 3) : 2;
            const pMoy  = (p.nb > 0) ? Math.round(p.ca / p.nb) : 0;
            return `<div class="period-bar-wrap">
                <div class="period-bar-tooltip">
                    <span class="tt-date">${p.label}</span>
                    <span class="tt-ca">${fmt(p.ca)} ${DEVISE}</span>
                    <span class="tt-detail">${p.nb > 0 ? p.nb + ' cmd · panier ' + fmt(pMoy) + ' ' + DEVISE : 'Aucune vente'}</span>
                </div>
                <div class="period-bar ${p.ca === 0 ? 'empty' : ''}" style="height:0%" data-h="${h}"></div>
            </div>`;
        }).join('');

        labelsEl.innerHTML = points.map(p => `<div class="period-bar-lbl">${p.label}</div>`).join('');

        barsEl.querySelectorAll('.period-bar').forEach((bar, i) => {
            setTimeout(() => { bar.style.transition = 'height .4s cubic-bezier(.23,1,.32,1)'; bar.style.height = bar.dataset.h + '%'; }, i * 30);
        });
    }

    async function loadPeriod(period) {
        const loading   = document.getElementById('periodLoading');
        const statsWrap = document.getElementById('periodStatsWrap');
        const labelEl   = document.getElementById('periodLabel');
        if (labelEl) labelEl.innerHTML = 'Période : <strong>' + (periodLabels[period] || period) + '</strong>';
        loading.classList.add('show'); statsWrap.style.opacity = '.3';
        try {
            const res = await fetch(`{{ route('boutique.period.stats') }}?period=${period}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            document.getElementById('pCA').textContent     = fmt(data.ca);
            document.getElementById('pCA').title           = Math.round(data.ca).toLocaleString('fr-FR') + ' ' + DEVISE;
            document.getElementById('pCMD').textContent    = data.nb;
            document.getElementById('pPANIER').textContent = fmt(data.panier);
            document.getElementById('pPANIER').title       = Math.round(data.panier).toLocaleString('fr-FR') + ' ' + DEVISE;
            document.getElementById('pTAUX').textContent   = data.taux + '%';

            /* Valeur exacte sous le chiffre abrégé (visible mobile + desktop) */
            const caFull = document.getElementById('pCA-full');
            if (caFull) {
                if (data.ca >= 1_000_000) {
                    caFull.textContent  = '= ' + Math.round(data.ca).toLocaleString('fr-FR') + ' ' + DEVISE;
                    caFull.style.display = '';
                } else {
                    caFull.style.display = 'none';
                }
            }
            const panierFull = document.getElementById('pPANIER-full');
            if (panierFull) {
                if (data.panier >= 1_000_000) {
                    panierFull.textContent  = '= ' + Math.round(data.panier).toLocaleString('fr-FR') + ' ' + DEVISE;
                    panierFull.style.display = '';
                } else {
                    panierFull.style.display = 'none';
                }
            }
            drawBars(data.points);
        } catch (err) {
            console.error(err); document.getElementById('pCA').textContent = '—';
        } finally {
            loading.classList.remove('show'); statsWrap.style.opacity = '1';
        }
    }

    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active'); loadPeriod(btn.dataset.period);
        });
    });

    /* Badge "30 derniers jours ▾" → déclenche l'analyse 30j + scroll */
    document.querySelector('.mini-period-badge')?.addEventListener('click', () => {
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        const btn30 = document.querySelector('.period-btn[data-period="30days"]');
        if (btn30) { btn30.classList.add('active'); loadPeriod('30days'); }
        document.getElementById('periodCard')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    loadPeriod('this_month');
});
</script>

{{-- ══════════════════════════════════════════════════
     NOTIFICATIONS TEMPS RÉEL — polling global vendeur
══════════════════════════════════════════════════ --}}
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── État local ── */
    let _prevMsg            = -1;
    let _prevOrders         = -1;
    let _prevCompanyMsg     = -1;
    let _notifOpen          = false;
    /* Persiste entre navigations pour éviter de re-notifier les mêmes messages */
    let _lastSeenMsgId        = parseInt(sessionStorage.getItem('bq_last_msg_id') || '0', 10);
    let _lastSeenCompanyMsgId = parseInt(sessionStorage.getItem('bq_last_company_msg_id') || '0', 10);

    /* Restaurer les alertes depuis sessionStorage (persiste entre navigations) */
    let _alerts = [];
    try {
        const saved = sessionStorage.getItem('boutique_notif_alerts');
        if (saved) _alerts = JSON.parse(saved);
    } catch(e) {}

    function _saveAlerts() {
        try { sessionStorage.setItem('boutique_notif_alerts', JSON.stringify(_alerts)); } catch(e) {}
    }

    /* ── Helpers badge générique ── */
    function setBadge(id, count) {
        const el = document.getElementById(id);
        if (!el) return;
        if (count > 0) {
            el.textContent = count > 99 ? '99+' : count;
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    /* ── Toast bas d'écran ── */
    function showToast(msg, type) {
        const t = document.createElement('div');
        t.style.cssText = `
            position:fixed;bottom:${20 + document.querySelectorAll('.rt-toast').length * 60}px;
            right:20px;background:${type==='order'?'#111118':type==='msg'?'#1e40af':type==='company'?'#4f46e5':'#1f2937'};
            color:#fff;padding:12px 18px;border-radius:12px;font-size:13px;font-weight:600;
            z-index:99999;box-shadow:0 8px 24px rgba(0,0,0,.25);
            animation:slideInRight .3s cubic-bezier(.23,1,.32,1);
            display:flex;align-items:center;gap:10px;max-width:280px;cursor:pointer;
        `;
        t.className = 'rt-toast';
        t.innerHTML = msg;
        t.onclick   = () => { t.style.opacity='0'; setTimeout(()=>t.remove(),300); };
        document.body.appendChild(t);
        setTimeout(() => { t.style.opacity='0'; t.style.transform='translateX(120%)';
            t.style.transition='all .3s'; setTimeout(()=>t.remove(),300); }, 5000);
    }

    /* ── Dropdown notifications ── */
    let _alertIdSeq = 0;

    function renderNotifList() {
        const list = document.getElementById('notifList');
        if (!list) return;
        if (!_alerts.length) {
            list.innerHTML = '<div style="padding:20px;text-align:center;color:#9ca3af;font-size:12.5px">✅ Aucune alerte</div>';
            return;
        }
        list.innerHTML = _alerts.slice(0, 20).map(a => {
            const isOrder      = a.type === 'order';
            const isCompanyMsg = a.type === 'company_msg';
            /* Badge contextuel */
            const badge = isOrder
                ? `<span style="font-size:9px;font-weight:700;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:20px;padding:1px 6px;white-space:nowrap;flex-shrink:0">En cours</span>`
                : isCompanyMsg
                ? `<span style="font-size:9px;font-weight:700;background:#eef2ff;color:#4f46e5;border:1px solid #c7d2fe;border-radius:20px;padding:1px 6px;white-space:nowrap;flex-shrink:0">Entreprise</span>`
                : '';
            /* Bouton × pour messages uniquement */
            const closeBtn = !isOrder
                ? `<button onclick="event.stopPropagation();_dismissAlert(${a.id})"
                           title="Supprimer"
                           style="flex-shrink:0;width:28px;height:28px;border:none;background:none;
                                  color:#9ca3af;font-size:15px;cursor:pointer;border-radius:6px;
                                  display:flex;align-items:center;justify-content:center;
                                  margin-right:6px;transition:all .15s"
                           onmouseover="this.style.background='#fee2e2';this.style.color='#dc2626'"
                           onmouseout="this.style.background='none';this.style.color='#9ca3af'">×</button>`
                : '';
            /* onclick : pour entreprise → ouvrir chat, pour messages → dismiss, commandes → rien */
            let rowClickHandler = '';
            let rowHref = a.url || '#';
            let rowTag = 'a';
            if (isCompanyMsg && a.companyId) {
                rowTag = 'div';
                const _safeName = encodeURIComponent(a.companyName || '');
                rowClickHandler = `onclick="bqOpenCompanyChat(${a.companyId},decodeURIComponent('${_safeName}'));_dismissAlert(${a.id})"`;
            } else if (!isOrder) {
                rowClickHandler = `onclick="_dismissAlert(${a.id})"`;
            }
            const rowBg    = isOrder ? '#fffbeb' : isCompanyMsg ? '#f5f3ff' : '#fff';
            const rowHover = isOrder ? '#fef9ec' : isCompanyMsg ? '#ede9fe' : '#f9fafb';

            const innerRow = isCompanyMsg && a.companyId
                ? `<div ${rowClickHandler} style="display:flex;align-items:center;gap:10px;flex:1;padding:10px 12px;cursor:pointer;min-width:0">`
                : `<a href="${rowHref}" ${rowClickHandler} style="display:flex;align-items:center;gap:10px;flex:1;padding:10px 12px;text-decoration:none;min-width:0">`;
            const innerClose = isCompanyMsg && a.companyId ? `</div>` : `</a>`;

            return `
            <div style="display:flex;align-items:center;border-bottom:1px solid #f3f6f4;background:${rowBg};transition:background .12s"
                 onmouseover="this.style.background='${rowHover}'" onmouseout="this.style.background='${rowBg}'">
                ${innerRow}
                    <span style="font-size:18px;flex-shrink:0">${a.ico}</span>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:12.5px;font-weight:600;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${a.msg}</div>
                        <div style="display:flex;align-items:center;gap:6px;margin-top:3px">
                            <span style="font-size:10.5px;color:#9ca3af">${a.time}</span>
                            ${badge}
                        </div>
                    </div>
                    <span style="font-size:13px;color:#d1d5db;flex-shrink:0;margin-left:4px">›</span>
                ${innerClose}
                ${closeBtn}
            </div>`;
        }).join('');
    }

    /* ── Supprimer une alerte par son id (uniquement type message) ── */
    window._dismissAlert = function(id) {
        _alerts = _alerts.filter(a => a.id !== id);
        _saveAlerts();
        renderNotifList();
        const totalEl = document.getElementById('notifDropdownTotal');
        if (totalEl) totalEl.textContent = _alerts.length;
    };

    /* ── Retire toutes les alertes company_msg pour une entreprise donnée ── */
    window._bqRemoveCompanyAlert = function(companyId) {
        _alerts = _alerts.filter(a => !(a.type === 'company_msg' && String(a.companyId) === String(companyId)));
        _saveAlerts();
        if (_notifOpen) renderNotifList();
        const companyAlertCount = _alerts.filter(a => a.type === 'company_msg').length;
        const badge = document.getElementById('notifBellCount');
        /* badge sera recalculé au prochain poll */
    };

    window.toggleNotifDropdown = function() {
        _notifOpen = !_notifOpen;
        const dd = document.getElementById('notifDropdown');
        if (dd) dd.style.display = _notifOpen ? 'block' : 'none';
        if (_notifOpen) renderNotifList();
    };

    document.addEventListener('click', e => {
        if (!e.target.closest('#notifBellWrap')) {
            _notifOpen = false;
            const dd = document.getElementById('notifDropdown');
            if (dd) dd.style.display = 'none';
        }
    });

    /* ── Push alerte dans la file ── */
    function pushAlert(ico, msg, url, type, body, time, companyId, companyName) {
        if (!time) {
            const now = new Date();
            time = now.getHours().toString().padStart(2,'0')+':'+now.getMinutes().toString().padStart(2,'0');
        }
        _alerts.unshift({
            id: ++_alertIdSeq, ico, msg, url, time,
            type: type || 'msg', body: body || '',
            companyId: companyId || null,
            companyName: companyName || ''
        });
        if (_alerts.length > 30) _alerts.pop();
        _saveAlerts();
    }

    /* ── Polling principal ── */
    async function pollNotifications() {
        try {
            const res = await fetch('/boutique/notifications/poll', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            if (!res.ok) return;
            const d = await res.json();

            /* Messages */
            setBadge('sbMsgBadge', d.messages_unread);
            setBadge('msgTopbarCount', d.messages_unread);
            const msgBtn = document.getElementById('msgTopbarBtn');
            if (msgBtn) msgBtn.classList.toggle('has-unread', d.messages_unread > 0);

            /* Nouveaux messages — même logique au 1er poll et aux suivants */
            if (Array.isArray(d.latest_messages) && d.latest_messages.length > 0) {
                const newMsgs = d.latest_messages.filter(m => m.id > _lastSeenMsgId);
                if (newMsgs.length > 0) {
                    /* Toast seulement si ce n'est pas le premier poll (évite le spam au chargement) */
                    if (_prevMsg >= 0) {
                        const n = newMsgs.length;
                        showToast(`💬 <div>${n} nouveau${n>1?'x':''} message${n>1?'s':''} non lu${n>1?'s':''}</div>`, 'msg');
                    }
                    /* Alertes individuelles (du plus ancien au plus récent dans la cloche) */
                    [...newMsgs].reverse().forEach(m => {
                        const label = m.product_name
                            ? `${m.sender_name} · ${m.product_name} — message non lu`
                            : `${m.sender_name} — message non lu`;
                        pushAlert('💬', label, '{{ route("boutique.messages.hub") }}', 'msg', '', m.time);
                    });
                    _lastSeenMsgId = newMsgs[0].id;
                    try { sessionStorage.setItem('bq_last_msg_id', _lastSeenMsgId); } catch(e) {}
                }
            }
            _prevMsg = d.messages_unread;

            /* ── Messages des entreprises de livraison ── */
            if (Array.isArray(d.latest_company_messages) && d.latest_company_messages.length > 0) {
                const newCMsgs = d.latest_company_messages.filter(m => {
                    if (m.id <= _lastSeenCompanyMsgId) return false;
                    try {
                        const seen = JSON.parse(sessionStorage.getItem('bq_co_seen') || '{}');
                        if (seen[m.company_id] && m.id <= seen[m.company_id]) return false;
                    } catch(e) {}
                    return true;
                });
                if (newCMsgs.length > 0) {
                    if (_prevCompanyMsg >= 0) {
                        const n = newCMsgs.length;
                        showToast(`🏢 <div>${n} nouveau${n>1?'x':''} message${n>1?'s':''} d'entreprise${n>1?'s':''}</div>`, 'company');
                    }
                    /* Grouper par entreprise — une seule alerte par entreprise */
                    const byCompany = {};
                    newCMsgs.forEach(m => {
                        const cid = m.company_id;
                        if (!byCompany[cid]) byCompany[cid] = { company_id: cid, company_name: m.company_name, count: 0, time: m.time, body: m.body };
                        byCompany[cid].count++;
                    });
                    Object.values(byCompany).forEach(g => {
                        const label = g.count > 1
                            ? `${g.company_name} — ${g.count} nouveaux messages`
                            : `${g.company_name} — ${g.body}`;
                        const existing = _alerts.find(a => a.type === 'company_msg' && a.companyId === g.company_id);
                        if (existing) {
                            existing.msg  = label;
                            existing.time = g.time;
                        } else {
                            pushAlert('🏢', label, null, 'company_msg', '', g.time, g.company_id, g.company_name);
                        }
                    });
                    _saveAlerts();
                    _lastSeenCompanyMsgId = newCMsgs[0].id;
                    try { sessionStorage.setItem('bq_last_company_msg_id', _lastSeenCompanyMsgId); } catch(e) {}
                }
            }
            _prevCompanyMsg = d.company_messages_unread ?? 0;

            /* Commandes — une seule entrée dans _alerts, compteur mis à jour */
            setBadge('sbOrdersBadge', d.orders_pending);
            if (d.orders_pending > 0) {
                const label = `${d.orders_pending} commande${d.orders_pending > 1 ? 's' : ''} en attente`;
                const existing = _alerts.find(a => a.type === 'order');
                if (existing) {
                    /* Mettre à jour le compteur de l'entrée existante */
                    existing.msg = label;
                    if (_prevOrders >= 0 && d.orders_pending > _prevOrders) {
                        const n = d.orders_pending - _prevOrders;
                        showToast(`📦 <div>${n} nouvelle${n>1?'s':''} commande${n>1?'s':''} !</div>`, 'order');
                        existing.time = new Date().getHours().toString().padStart(2,'0')+':'+new Date().getMinutes().toString().padStart(2,'0');
                    }
                } else {
                    /* Créer l'entrée commande pour la première fois */
                    if (_prevOrders >= 0 && d.orders_pending > _prevOrders) {
                        const n = d.orders_pending - _prevOrders;
                        showToast(`📦 <div>${n} nouvelle${n>1?'s':''} commande${n>1?'s':''} !</div>`, 'order');
                    }
                    pushAlert('📦', label, '{{ route("boutique.orders.index") }}', 'order');
                }
                _saveAlerts();
            } else if (d.orders_pending === 0 && _alerts.some(a => a.type === 'order')) {
                /* Plus aucune commande en attente → supprimer l'entrée */
                _alerts = _alerts.filter(a => a.type !== 'order');
                _saveAlerts();
            }
            _prevOrders = d.orders_pending;

            /* Livreurs */
            setBadge('sbLivreursBadge', d.livreurs_available);

            /* Cloche totale : messages clients + commandes + alertes entreprises non dismissées */
            const companyAlertCount = _alerts.filter(a => a.type === 'company_msg').length;
            const total = d.messages_unread + d.orders_pending + companyAlertCount;
            setBadge('notifBellCount', total);
            const totalEl = document.getElementById('notifDropdownTotal');
            if (totalEl) totalEl.textContent = _alerts.length;

            if (_notifOpen) renderNotifList();
        } catch(e) {}
    }

    /* ── Démarrage ── */
    pollNotifications();
    setInterval(pollNotifications, 6000);

    /* ── Animation CSS + scrollbar notifList ── */
    const s = document.createElement('style');
    s.textContent = `
        @keyframes slideInRight{from{opacity:0;transform:translateX(60px)}to{opacity:1;transform:translateX(0)}}
        #notifList::-webkit-scrollbar{width:5px;}
        #notifList::-webkit-scrollbar-track{background:#f9fafb;}
        #notifList::-webkit-scrollbar-thumb{background:#d1d5db;border-radius:4px;}
        #notifList::-webkit-scrollbar-thumb:hover{background:#9ca3af;}
    `;
    document.head.appendChild(s);
})();

/* ════════════════════════════════════════════════════════
   CHAT BOUTIQUE ↔ ENTREPRISE — depuis la cloche de notif
   ════════════════════════════════════════════════════════ */
(function () {
    const SHOP_ID = {{ $shop->id ?? 0 }};
    const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let _bqCompanyId      = null;
    let _bqCompanyName    = '';
    let _bqLastMsgTime    = null;
    let _bqInterval       = null;
    let _bqConfierDone    = false;
    let _bqSelectedOrderId = null;

    /* ── Ouvre le chat pour une entreprise donnée ── */
    window.bqOpenCompanyChat = function(companyId, companyName) {
        _bqCompanyId      = companyId;
        _bqCompanyName    = companyName || 'Entreprise';
        _bqLastMsgTime    = null;
        _bqConfierDone    = false;
        _bqSelectedOrderId = null;

        /* Ferme le dropdown notif */
        const dd = document.getElementById('notifDropdown');
        if (dd) dd.style.display = 'none';

        /* Dismiss la notification cloche pour cette entreprise */
        if (typeof window._bqRemoveCompanyAlert === 'function') {
            window._bqRemoveCompanyAlert(companyId);
        }

        /* Header */
        document.getElementById('bqChatAv').textContent   = '🏢';
        document.getElementById('bqChatName').textContent = _bqCompanyName;

        /* Vider messages */
        document.getElementById('bqChatMsgList').innerHTML =
            '<div class="bq-chat-empty" id="bqChatEmpty">Chargement…</div>';

        /* Reset zone confier */
        document.getElementById('bqConfierZone').style.display  = 'none';
        document.getElementById('bqOrdersList').innerHTML        = '';
        document.getElementById('bqZonePickerWrap').style.display = 'none';
        document.getElementById('bqZonePriceHint').style.display  = 'none';
        document.getElementById('bqZonePicker').innerHTML         = '<option value="">— Choisir une zone —</option>';
        const btnC = document.getElementById('bqBtnConfier');
        btnC.disabled  = false;
        btnC.classList.remove('done');
        btnC.innerHTML = '📦 Confier la livraison à cette entreprise';

        /* Ouvrir */
        document.getElementById('bqChatModal').classList.add('open');
        document.body.style.overflow = 'hidden';
        document.getElementById('bqChatInput').focus();

        /* Charger messages + commandes */
        bqLoadMessages(true);
        bqLoadPendingOrders();
        _bqInterval = setInterval(() => bqLoadMessages(false), 3000);
    };

    /* ── Charge les commandes non assignées ── */
    function bqLoadPendingOrders() {
        const zone = document.getElementById('bqConfierZone');
        const list = document.getElementById('bqOrdersList');

        fetch('/employe/orders/pending-json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(orders => {
            list.innerHTML = '';
            if (!orders.length) { zone.style.display = 'none'; return; }

            orders.forEach(o => {
                const card = document.createElement('div');
                card.className = 'bq-order-card';
                card.dataset.orderId = o.id;
                const thumbHtml = o.photo
                    ? `<div class="bq-order-card-thumb"><img src="${o.photo}" alt="" onerror="this.parentElement.innerHTML='🏷️'"></div>`
                    : `<div class="bq-order-card-thumb">🏷️</div>`;
                const addrHtml = o.address
                    ? `<div class="bq-order-card-address">📍 ${bqEsc(o.address)}</div>`
                    : '';
                card.innerHTML =
                    thumbHtml +
                    `<div class="bq-order-card-info">` +
                        `<div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;">` +
                            `<span class="bq-order-card-num">${o.num}</span>` +
                            `<span class="bq-order-card-client">${bqEsc(o.client)}</span>` +
                        `</div>` +
                        addrHtml +
                        `<div class="bq-order-card-amount">${o.total} ${o.devise || 'GNF'}</div>` +
                    `</div>` +
                    `<div class="bq-order-card-check">✓</div>`;
                card.addEventListener('click', () => bqSelectOrderCard(card, o.id));
                list.appendChild(card);
            });
            zone.style.display = 'block';
            /* La zone vient d'apparaître et a réduit la zone messages → re-scroller en bas */
            requestAnimationFrame(() => {
                const ml = document.getElementById('bqChatMsgList');
                ml.scrollTop = ml.scrollHeight;
            });
        })
        .catch(() => { document.getElementById('bqConfierZone').style.display = 'none'; });
    }

    /* ── Sélectionne une commande et charge les zones ── */
    function bqSelectOrderCard(card, orderId) {
        document.querySelectorAll('#bqOrdersList .bq-order-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        _bqSelectedOrderId = orderId;
        bqLoadZones();
    }

    /* ── Charge les zones de l'entreprise ── */
    function bqLoadZones() {
        const wrap   = document.getElementById('bqZonePickerWrap');
        const picker = document.getElementById('bqZonePicker');
        const hint   = document.getElementById('bqZonePriceHint');

        wrap.style.display = 'none';
        hint.style.display = 'none';
        picker.innerHTML   = '<option value="">— Choisir une zone —</option>';

        if (!_bqCompanyId) return;

        fetch(`/company-zones/${_bqCompanyId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(zones => {
            if (!zones || !zones.length) return;
            zones.forEach(z => {
                const opt = document.createElement('option');
                opt.value           = z.id;
                opt.textContent     = `${z.name} — ${new Intl.NumberFormat('fr-FR').format(z.price)} GNF`;
                opt.dataset.price   = z.price;
                opt.dataset.minutes = z.estimated_minutes;
                picker.appendChild(opt);
            });
            wrap.style.display = 'block';
        })
        .catch(() => {});
    }

    /* ── Zone sélectionnée ── */
    window.bqOnZonePick = function(sel) {
        const hint    = document.getElementById('bqZonePriceHint');
        const priceEl = document.getElementById('bqZonePriceVal');
        const delayEl = document.getElementById('bqZoneDelayVal');
        const opt     = sel.options[sel.selectedIndex];
        if (!sel.value) { hint.style.display = 'none'; return; }
        priceEl.textContent = new Intl.NumberFormat('fr-FR').format(opt.dataset.price) + ' GNF';
        delayEl.textContent = opt.dataset.minutes;
        hint.style.display  = 'block';
    };

    /* ── Confie la commande sélectionnée ── */
    window.bqConfierLivraison = function() {
        if (_bqConfierDone) return;
        const orderId = _bqSelectedOrderId;
        if (!orderId) {
            const list = document.getElementById('bqOrdersList');
            list.style.outline = '2px solid #f87171';
            list.style.borderRadius = '9px';
            setTimeout(() => { list.style.outline = ''; list.style.borderRadius = ''; }, 1200);
            return;
        }

        const btn     = document.getElementById('bqBtnConfier');
        btn.disabled  = true;
        btn.innerHTML = '⏳ Envoi…';

        const picker  = document.getElementById('bqZonePicker');
        const zoneId  = picker?.value || null;
        const zoneOpt = zoneId ? picker.options[picker.selectedIndex] : null;

        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('delivery_company_id', _bqCompanyId);
        if (zoneId)                  formData.append('delivery_zone_id', zoneId);
        if (zoneOpt?.dataset?.price) formData.append('delivery_fee', zoneOpt.dataset.price);

        fetch(`/employe/orders/${orderId}/send-to-company`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                _bqConfierDone = true;
                btn.classList.add('done');
                btn.innerHTML = '✅ Confiée !';
                document.getElementById('bqConfierZone').style.display = 'none';

                const selCard = document.querySelector('#bqOrdersList .bq-order-card.selected');
                const numTxt  = selCard ? (selCard.querySelector('.bq-order-card-num')?.textContent || ('#' + orderId)) : ('#' + orderId);
                bqRenderMessages([{
                    id: 'local-' + Date.now(),
                    from_type: 'system',
                    body: `✅ Commande ${numTxt} confiée à ${_bqCompanyName}. Statut : En attente.`,
                    created_at: new Date().toISOString()
                }], false);

                setTimeout(() => location.reload(), 2000);
            } else {
                btn.disabled  = false;
                btn.innerHTML = '📦 Confier la livraison à cette entreprise';
                alert(data.message || 'Erreur lors de la soumission.');
            }
        })
        .catch(() => {
            btn.disabled  = false;
            btn.innerHTML = '📦 Confier la livraison à cette entreprise';
            alert('Erreur réseau. Veuillez réessayer.');
        });
    };

    /* ── Ferme le chat ── */
    window.bqCloseChatModal = function() {
        clearInterval(_bqInterval);
        _bqInterval = null;
        document.getElementById('bqChatModal').classList.remove('open');
        document.body.style.overflow = '';
    };

    document.getElementById('bqChatModal')?.addEventListener('click', function(e) {
        if (e.target === this) window.bqCloseChatModal();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') window.bqCloseChatModal();
    });

    /* ── Charge les messages ── */
    function bqLoadMessages(initial) {
        if (!_bqCompanyId) return;
        const url = new URL(`/employe/companies/${_bqCompanyId}/chat/messages`, location.origin);
        url.searchParams.set('shop_id', SHOP_ID);
        if (_bqLastMsgTime && !initial) url.searchParams.set('after', _bqLastMsgTime);

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            const msgs = data.messages || [];
            if (msgs.length) {
                bqRenderMessages(msgs, initial);
                _bqLastMsgTime = msgs[msgs.length - 1].created_at || null;
                /* Mémoriser le dernier message vu pour ce fil — évite fausse notif au retour */
                try {
                    const lastId = msgs[msgs.length - 1].id;
                    const seen   = JSON.parse(sessionStorage.getItem('bq_co_seen') || '{}');
                    seen[String(_bqCompanyId)] = lastId;
                    sessionStorage.setItem('bq_co_seen', JSON.stringify(seen));
                } catch(e) {}
            } else if (initial) {
                document.getElementById('bqChatMsgList').innerHTML =
                    '<div class="bq-chat-empty" id="bqChatEmpty">Aucun message. Commencez la discussion !</div>';
            }
        })
        .catch(() => {
            if (initial)
                document.getElementById('bqChatMsgList').innerHTML =
                    '<div class="bq-chat-empty" id="bqChatEmpty">Aucun message. Commencez la discussion !</div>';
        });
    }

    /* ── Rend les messages ── */
    function bqRenderMessages(msgs, replace) {
        const list = document.getElementById('bqChatMsgList');
        if (replace) list.innerHTML = '';
        const empty = list.querySelector('.bq-chat-empty');
        if (empty) empty.remove();

        msgs.forEach(m => {
            if (document.getElementById('bqmsg-' + m.id)) return;
            const role     = m.from_type || m.sender_role || 'shop';
            const isMine   = role === 'shop';
            const isSystem = role === 'system';
            const row      = document.createElement('div');
            row.className  = 'bq-msg-row ' + (isSystem ? 'mine' : isMine ? 'mine' : 'theirs');
            row.id         = 'bqmsg-' + m.id;
            const cls      = isSystem ? 'bq-msg-bubble system' : 'bq-msg-bubble';
            const text     = m.body || m.message || '';
            const timeStr  = m.created_at
                ? new Date(m.created_at).toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'})
                : '';
            const label    = isSystem ? 'Système' : isMine ? 'Vous' : _bqCompanyName;
            row.innerHTML  = `<div class="${cls}">${bqEsc(text)}</div>` +
                             `<div class="bq-msg-meta">${label} · ${timeStr}</div>`;
            list.appendChild(row);
        });
        /* Scroll bas garanti après rendu DOM */
        requestAnimationFrame(() => { list.scrollTop = list.scrollHeight; });
    }

    function bqEsc(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Envoie un message ── */
    window.bqSendMsg = function() {
        const input   = document.getElementById('bqChatInput');
        const msg     = input.value.trim();
        if (!msg || !_bqCompanyId) return;

        const sendBtn = document.getElementById('bqChatSendBtn');
        sendBtn.disabled = true;

        fetch(`/employe/companies/${_bqCompanyId}/chat/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: msg, shop_id: SHOP_ID })
        })
        .then(r => r.json())
        .then(data => {
            input.value = '';
            sendBtn.disabled = false;
            if (data.ok && data.message) {
                bqRenderMessages([data.message], false);
                _bqLastMsgTime = data.message.created_at || null;
            } else {
                bqLoadMessages(false);
            }
        })
        .catch(() => { sendBtn.disabled = false; });
    };
})();

/* ════════════════════════════════════════════════════════
   KPI TEMPS RÉEL — polling 30s
   ════════════════════════════════════════════════════════ */
(function () {
    const KPI_URL = '{{ route("boutique.kpi.live") }}';
    const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function fmt(n) {
        return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function set(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function setClass(id, cls) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('up', 'down', 'flat');
        el.classList.add(cls);
    }

    function pollKpi() {
        fetch(KPI_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.ok ? r.json() : null)
        .then(d => {
            if (!d) return;

            /* KPI Revenu net */
            set('kpiCaVal', d.ca_month);
            const caCls = d.ca_delta >= 0 ? 'up' : 'down';
            setClass('kpiCaDelta', caCls);
            set('kpiCaDelta', (d.ca_delta >= 0 ? '↑' : '↓') + ' ' + Math.abs(d.ca_delta) + '% vs mois précédent');

            /* KPI Commandes */
            set('kpiCmdVal', d.cmd_month);
            const cmdCls = d.cmd_today >= d.cmd_yest ? 'up' : 'down';
            setClass('kpiCmdDelta', cmdCls);
            set('kpiCmdDelta', (d.cmd_today >= d.cmd_yest ? '↑' : '↓') + ' ' + d.cmd_today + ' aujourd\'hui');

            /* KPI Panier moyen */
            set('kpiPanierVal', d.panier);
            setClass('kpiPanierDelta', d.panier_delta >= 0 ? 'up' : 'down');
            set('kpiPanierDelta', (d.panier_delta >= 0 ? '↑' : '↓') + ' ' + Math.abs(d.panier_delta) + '% vs mois précédent');

            /* KPI Taux livraison */
            set('kpiTauxVal', d.taux_liv + '%');
            set('kpiTauxUnit', d.livres + ' / ' + d.total_cmd_month + ' livrées');
            const tauxOk = d.taux_liv >= 90;
            setClass('kpiTauxDelta', tauxOk ? 'up' : 'down');
            set('kpiTauxDelta', tauxOk ? '✓ Excellent' : '⚠ À améliorer');

            /* Today CA */
            set('todayCaVal', d.ca_today);
            const todayCaCls = d.ca_today_delta > 0 ? 'up' : (d.ca_today_delta < 0 ? 'down' : 'flat');
            setClass('todayCaDelta', todayCaCls);
            if (d.ca_today_delta > 0)      set('todayCaDelta', '↑ +' + d.ca_today_delta + '% vs hier');
            else if (d.ca_today_delta < 0) set('todayCaDelta', '↓ ' + d.ca_today_delta + '% vs hier');
            else                           set('todayCaDelta', '— Même niveau qu\'hier');

            /* Today commandes */
            set('todayCmdVal', d.cmd_today);
            const todayCmdCls = d.cmd_today >= d.cmd_yest ? 'up' : 'down';
            setClass('todayCmdDelta', todayCmdCls);
            const diff = Math.abs(d.cmd_today - d.cmd_yest);
            if (d.cmd_today > d.cmd_yest)      set('todayCmdDelta', '↑ +' + diff + ' vs hier');
            else if (d.cmd_today < d.cmd_yest) set('todayCmdDelta', '↓ ' + diff + ' de moins vs hier');
            else                               set('todayCmdDelta', '— Même niveau qu\'hier');

            /* Kanban */
            if (d.kanban) {
                Object.entries(d.kanban).forEach(([key, cnt]) => {
                    const el = document.getElementById('kb-' + key);
                    if (el) el.textContent = cnt;
                });
            }

            /* Flèche chart Revenus 7j (today vs hier) */
            const rcBadge = document.getElementById('rcDeltaBadge');
            if (rcBadge) {
                const v = d.ca_today_delta;
                rcBadge.classList.remove('up','down','flat');
                if (v > 0)      { rcBadge.classList.add('up');   rcBadge.textContent = '↑ +' + v + '% aujourd\'hui vs hier'; }
                else if (v < 0) { rcBadge.classList.add('down'); rcBadge.textContent = '↓ ' + v + '% aujourd\'hui vs hier'; }
                else            { rcBadge.classList.add('flat'); rcBadge.textContent = '→ Stable aujourd\'hui'; }
            }

            /* Flèche chart Commandes 7j (today vs hier) */
            const cmdBadge = document.getElementById('cmdDeltaBadge');
            if (cmdBadge) {
                const diff = d.cmd_today - d.cmd_yest;
                cmdBadge.classList.remove('up','down','flat');
                if (diff > 0)      { cmdBadge.classList.add('up');   cmdBadge.textContent = '↑ ' + d.cmd_today + ' aujourd\'hui'; }
                else if (diff < 0) { cmdBadge.classList.add('down'); cmdBadge.textContent = '↓ ' + d.cmd_today + ' aujourd\'hui'; }
                else               { cmdBadge.classList.add('flat'); cmdBadge.textContent = '→ ' + d.cmd_today + ' aujourd\'hui'; }
            }
        })
        .catch(() => {});
    }

    /* Premier appel après 30s puis toutes les 30s */
    setInterval(pollKpi, 30000);
})();
</script>
@endpush