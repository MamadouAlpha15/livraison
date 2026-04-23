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
    --brand:      #10b981; /* couleur des ecritures */
    --brand-dk:   #059669;
    --brand-lt:   #d1fae5;
    --brand-mlt:  #ecfdf5;
    --sb-bg:      #0d1f18; /* l'arriere plan du side bar */
    --sb-border:  rgba(255,255,255,.06);
    --sb-act:     rgba(16,185,129,.14);
    --sb-hov:     rgba(255,255,255,.04);
    --sb-txt:     rgba(255,255,255,.55);
    --sb-txt-act: #fff;
    --bg:         #f6f8f7;
    --surface:    #ffffff;
    --border:     #e8eceb;
    --border-dk:  #d4d9d7;
    --text:       #0f1c18;
    --text-2:     #4b5c56;
    --muted:      #8a9e98;
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r:          14px;
    --r-sm:       9px;
    --r-xs:       6px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow:     0 4px 16px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    --sb-w:       230px;
    --top-h:      58px;
}

html { font-family: var(--font); }
body { background: var(--bg); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

.dash-wrap { display: flex; min-height: 100vh; }
.dash-wrap .sidebar { flex-shrink: 0; }
.dash-wrap .main { margin-left: var(--sb-w); flex: 1; min-width: 0; }

/* SIDEBAR */
.sidebar { background: var(--sb-bg); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; width: var(--sb-w); overflow-y: scroll; scrollbar-width: thin; scrollbar-color:  rgba(255,255,255,.05); z-index: 40; border-right: 1px solid rgba(0,0,0,.2); }
.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,.04); }
.sidebar::-webkit-scrollbar-thumb { background: rgba(16,185,129,.4); border-radius: 4px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(16,185,129,.7); }
.sb-brand { padding: 18px 16px 14px; border-bottom: 1px solid var(--sb-border); flex-shrink: 0; position: relative; }
.sb-close { display: none; position: absolute; top: 14px; right: 12px; width: 30px; height: 30px; border-radius: 8px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.6); font-size: 18px; line-height: 1; cursor: pointer; align-items: center; justify-content: center; transition: background .15s, color .15s; flex-shrink: 0; }
.sb-close:hover { background: rgba(239,68,68,.18); border-color: rgba(239,68,68,.3); color: #fca5a5; }
@media (max-width: 900px) { .sb-close { display: flex; } }
.sb-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.sb-logo-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
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
.sb-group-toggle.open { color: rgba(255,255,255,.9); background: rgba(255,255,255,.03); }
.sb-group-toggle .ico { font-size: 14px; width: 20px; text-align: center; flex-shrink: 0; }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.25); transition: transform .2s; flex-shrink: 0; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.5); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.07); margin-top: 2px; margin-bottom: 4px; overflow: visible; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 12.5px; padding: 6px 10px; color: rgba(255,255,255,.45); }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.75); }
.sb-sub .sb-item.active { color: var(--sb-txt-act); background: var(--sb-act); }
.sb-footer { padding: 12px 10px; border-top: 1px solid var(--sb-border); flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; position: sticky; bottom: 0; background: var(--sb-bg); z-index: 1; }
.sb-scroll-hint { position: sticky; top: auto; bottom: 72px; width: 100%; height: 40px; background: linear-gradient(to bottom, transparent, rgba(13,31,24,.9)); pointer-events: none; z-index: 2; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 6px; transition: opacity .3s; margin-top: -40px; align-self: flex-end; }
.sb-scroll-hint.hidden { opacity: 0; pointer-events: none; }
.sb-scroll-hint-arrow { display: flex; flex-direction: column; align-items: center; gap: 2px; animation: bounceDown 1.5s ease-in-out infinite; }
.sb-scroll-hint-dot { width: 4px; height: 4px; border-radius: 50%; background: rgba(16,185,129,.6); }
.sb-scroll-hint-dot:nth-child(2) { opacity: .5; margin-top: -2px; }
.sb-scroll-hint-dot:nth-child(3) { opacity: .25; margin-top: -2px; }
@keyframes bounceDown { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(4px); } }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); text-decoration: none; transition: background .15s; }
.sb-user:hover { background: var(--sb-hov); }
.sb-av { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--brand), #16a34a); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(16,185,129,.25); }
.sb-uname { font-size: 12px; font-weight: 600; color: rgba(255,255,255,.85); }
.sb-urole { font-size: 10px; color: var(--sb-txt); margin-top: 1px; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.85); font-size: 12px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s, color .15s, border-color .15s; text-align: left; }
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
.flash-success { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; }
.flash-info    { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
.flash-warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
.flash-danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.comm-banner { background: var(--brand-mlt); border: 1px solid var(--brand-lt); border-radius: var(--r-sm); padding: 10px 14px; display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #065f46; margin-bottom: 20px; font-weight: 500; }

/* KPI GRID */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
.kpi { background: var(--surface); border: 1px solid var(--border); border-top: 3px solid var(--kpi-color, var(--brand)); border-radius: var(--r); padding: 16px 18px 14px; position: relative; overflow: hidden; box-shadow: var(--shadow-sm); transition: box-shadow .2s, border-color .2s; }
.kpi:hover { box-shadow: var(--shadow); }
.kpi-icon { width: 36px; height: 36px; background: var(--kpi-bg, var(--brand-mlt)); border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; font-size: 16px; margin-bottom: 10px; }
.kpi-lbl { font-size: 11px; color: var(--muted); font-weight: 600; letter-spacing: .3px; margin-bottom: 4px; text-transform: uppercase; }
.kpi-val { font-size: 24px; font-weight: 700; color: var(--text); letter-spacing: -.8px; font-family: var(--mono); line-height: 1; }
.kpi-unit { font-size: 10px; color: var(--muted); margin-top: 4px; }
.kpi-delta { font-size: 11px; font-weight: 600; margin-top: 6px; display: flex; align-items: center; gap: 3px; }
.up   { color: #059669; }
.down { color: #dc2626; }

/* CARDS */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); }
.card-hd { padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.card-bd { padding: 16px 18px; }

/* CHART */
.chart-wrap { margin-bottom: 22px; }
.rc-header { padding: 16px 20px 12px; border-bottom: 1px solid var(--border); display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.rc-header-left .rc-title { font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
.rc-total { font-size: 26px; font-weight: 800; color: var(--text); font-family: var(--mono); line-height: 1; }
.rc-total sup { font-size: 13px; font-weight: 600; vertical-align: super; color: var(--muted); }
.rc-delta { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; margin-top: 6px; }
.rc-delta.up   { background: #d1fae5; color: #065f46; }
.rc-delta.down { background: #fee2e2; color: #991b1b; }
.rc-delta.flat { background: #f3f4f6; color: #6b7280; }
.rc-header-right { text-align: right; font-size: 11px; color: var(--muted); }
.rc-header-right .rc-best { font-size: 12px; font-weight: 700; color: var(--brand); }
.rc-svg-wrap { padding: 4px 20px 16px; position: relative; }
.rc-tooltip { position: absolute; background: #1e293b; color: #f8fafc; border-radius: 8px; padding: 8px 12px; font-size: 11.5px; pointer-events: none; opacity: 0; transition: opacity .15s; white-space: nowrap; box-shadow: 0 4px 16px rgba(0,0,0,.25); z-index: 10; }
.rc-tooltip strong { display: block; font-size: 13px; font-family: var(--mono); color: #34d399; }
.rc-tooltip span   { color: #94a3b8; font-size: 10px; }
.rc-day-dots { display: flex; justify-content: space-around; padding: 0 0 2px; }
.rc-day-dot { flex: 1; text-align: center; font-size: 10px; color: var(--muted); font-family: var(--mono); }
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
.p-success { background: #d1fae5; color: #065f46; }
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
.lv-phone { font-size: 11.5px; color: #059669; font-weight: 600; margin-top: 2px; display: flex; align-items: center; gap: 4px; }
.lv-phone-warn { font-size: 11px; color: #f59e0b; font-weight: 600; margin-top: 2px; }
.lv-status-badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; flex-shrink: 0; }
.lv-status-badge.available { background: #d1fae5; color: #065f46; }
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
.status-dot.available { background: #10b981; box-shadow: 0 0 6px rgba(16,185,129,.5); }
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
.sp-track { flex: 1; height: 7px; background: #eef1f0; border-radius: 4px; overflow: hidden; }
.sp-fill { height: 100%; border-radius: 4px; background: var(--brand); transition: width 1.1s cubic-bezier(.23,1,.32,1); }
.sp-val { font-family: var(--mono); font-size: 11.5px; font-weight: 600; color: var(--text); width: 30px; text-align: right; flex-shrink: 0; }

/* AVATAR COLORS */
.av-green  { background: #059669; }
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
.today-card { background: linear-gradient(135deg, #0d1f18 0%, #1a3328 100%); border: 1px solid rgba(16,185,129,.2); border-radius: var(--r); padding: 20px 22px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(0,0,0,.12); position: relative; overflow: hidden; }
.today-card::after { content: ''; position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: rgba(16,185,129,.06); border-radius: 50%; }
.today-icon { width: 48px; height: 48px; background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.25); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
.today-lbl  { font-size: 11px; font-weight: 600; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.today-val  { font-size: 26px; font-weight: 700; color: #fff; font-family: var(--mono); letter-spacing: -1px; line-height: 1; }
.today-unit { font-size: 10px; color: rgba(255,255,255,.35); margin-top: 3px; }
.today-delta { font-size: 11px; font-weight: 700; margin-top: 6px; display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 20px; }
.today-delta.up   { background: rgba(16,185,129,.2); color: #34d399; }
.today-delta.down { background: rgba(239,68,68,.2);  color: #fca5a5; }
.today-delta.flat { background: rgba(255,255,255,.08); color: rgba(255,255,255,.4); }
.alerts-zone { margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; }
.alert-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: var(--r-sm); border: 1px solid; font-size: 12.5px; font-weight: 500; animation: slideIn .3s ease; }
@keyframes slideIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
.alert-item.danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.alert-item.warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
.alert-item.success { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; }
.alert-item.info    { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
.alert-ico  { font-size: 16px; flex-shrink: 0; }
.alert-msg  { flex: 1; line-height: 1.4; }
.alert-cta  { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; border: 1px solid currentColor; text-decoration: none; color: inherit; white-space: nowrap; flex-shrink: 0; opacity: .75; transition: opacity .15s; }
.alert-cta:hover { opacity: 1; }
.kanban-section { margin-bottom: 22px; }
.kanban-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
.kanban-col { background: var(--surface); border: 1px solid var(--border); border-top: 3px solid var(--k-color); border-radius: var(--r); padding: 14px 14px 12px; text-align: center; box-shadow: var(--shadow-sm); transition: box-shadow .2s, transform .2s; cursor: default; }
.kanban-col:hover { box-shadow: var(--shadow); transform: translateY(-2px); }
.kanban-ico   { font-size: 20px; margin-bottom: 8px; }
.kanban-count { font-size: 28px; font-weight: 700; font-family: var(--mono); color: var(--k-color); line-height: 1; letter-spacing: -1px; }
.kanban-lbl   { font-size: 10.5px; font-weight: 600; color: var(--muted); margin-top: 4px; text-transform: uppercase; letter-spacing: .4px; }
.kanban-col.has-items { background: var(--k-bg); }
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
.period-btn.active { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(16,185,129,.25); }
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
.tt-ca     { color: #34d399; font-size: 12px; display: block; }
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
</style>
@endpush

@php
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X', 0, 1));
    $now      = \Illuminate\Support\Carbon::now();

    $commissionsPaieesMonth = (float) \App\Models\CourierCommission::where('shop_id', $shop->id)->where('status', 'payée')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('amount');
    $caGrossMonth = (float) $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->sum('total');
    $caMonth = $caGrossMonth - $commissionsPaieesMonth;
    $commissionsPaieesPrev = (float) \App\Models\CourierCommission::where('shop_id', $shop->id)->where('status', 'payée')->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->sum('amount');
    $caGrossPrev  = (float) $shop->orders()->whereMonth('created_at',$now->copy()->subMonth()->month)->whereYear('created_at',$now->copy()->subMonth()->year)->where('status','livrée')->sum('total');
    $caPrev       = ($caGrossPrev - $commissionsPaieesPrev) ?: 1;
    $caDelta  = round((($caMonth - $caPrev) / $caPrev) * 100, 1);
    $cmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->whereNotIn('status',['annulée','cancelled'])->count();
    $cmdToday = $shop->orders()->whereDate('created_at', today())->whereNotIn('status',['annulée','cancelled'])->count();
    $cmdYest  = $shop->orders()->whereDate('created_at', today()->subDay())->whereNotIn('status',['annulée','cancelled'])->count();
    $cmdLivreesMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->count();
    $panier   = $cmdLivreesMonth > 0 ? round($caMonth / $cmdLivreesMonth) : 0;
    $totalCmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereNotIn('status',['annulée','cancelled'])->count();
    $livres        = $shop->orders()->whereMonth('created_at',$now->month)->where('status','livrée')->count();
    $tauxLiv       = $totalCmdMonth > 0 ? round(($livres / $totalCmdMonth) * 100, 1) : 0;
    $days7 = collect(range(6,0))->map(function ($i) use ($shop, $now) {
        $day        = $now->copy()->subDays($i)->toDateString();
        $caJour     = (float) $shop->orders()->whereDate('created_at', $day)->where('status','livrée')->sum('total');
        $commJour   = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop, $day) {
            $q->where('shop_id', $shop->id)->whereDate('created_at', $day);
        })->where('status', 'payée')->sum('amount');
        $d = $now->copy()->subDays($i);
        $d->locale('fr');
        $label = ucfirst($d->isoFormat('ddd')); // Lun, Mar, Mer, Jeu, Ven, Sam, Dim
        $dow = $d->dayOfWeek === 0 ? 7 : $d->dayOfWeek; // 1=Lun … 7=Dim
        return ['label' => $label, 'value' => max(0, $caJour - $commJour), 'today' => $i === 0, 'dow' => $dow];
    })->sortBy('dow')->values();
    $max7 = $days7->max('value') ?: 1;
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
    $avColors = ['av-green','av-blue','av-amber','av-purple','av-teal','av-rose'];
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
    $cmdALivrer = $shop->orders()->whereIn('status', ['confirmée', 'confirmed', 'processing'])->count();
    if ($cmdALivrer > 0 && $livreursDisponibles->isEmpty()) $alerts->push(['type'=>'warning','ico'=>'⚠️','msg'=>"{$cmdALivrer} commande(s) prête(s) à livrer mais aucun livreur disponible",'link'=>route('delivery.companies.index'),'cta'=>'Trouver un livreur']);
    if (!$shop->is_approved) $alerts->push(['type'=>'warning','ico'=>'⏳','msg'=>"Votre boutique est en attente de validation par l'administrateur",'link'=>null,'cta'=>null]);
    if ($caTodayDelta >= 20 && $caToday > 0) $alerts->push(['type'=>'success','ico'=>'🎉','msg'=>"Excellente journée ! Vos revenus d'aujourd'hui sont en hausse de {$caTodayDelta}% vs hier",'link'=>null,'cta'=>null]);
    $kanban = [
        ['label'=>'En attente','count'=>$shop->orders()->whereIn('status',['pending','en attente','en_attente'])->count(),'color'=>'#f59e0b','bg'=>'#fffbeb','ico'=>'📥'],
        ['label'=>'Confirmées','count'=>$shop->orders()->whereIn('status',['confirmed','confirmée','processing'])->count(),'color'=>'#3b82f6','bg'=>'#eff6ff','ico'=>'✅'],
        ['label'=>'En livraison','count'=>$shop->orders()->whereIn('status',['en_livraison','delivering','shipped'])->count(),'color'=>'#8b5cf6','bg'=>'#f5f3ff','ico'=>'🚴'],
        ['label'=>'Terminées','count'=>$shop->orders()->whereMonth('created_at',$now->month)->where('status','livrée')->count(),'color'=>'#10b981','bg'=>'#ecfdf5','ico'=>'🎯'],
        ['label'=>'Annulées','count'=>$shop->orders()->whereMonth('created_at',$now->month)->whereIn('status',['annulée','cancelled'])->count(),'color'=>'#ef4444','bg'=>'#fef2f2','ico'=>'❌'],
    ];
    $hasLivreurs  = $livreursDisponibles->isNotEmpty();
    $hasCompanies = isset($deliveryCompanies) && $deliveryCompanies->isNotEmpty();
    $produitsRisque = $shop->products()
        ->withCount(['orderItems as ventes_mois' => function ($q) use ($now) {
            $q->whereHas('order', function ($o) use ($now) { $o->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year); });
        }])
        ->having('ventes_mois', '=', 0)->orderBy('created_at', 'desc')->take(5)->get();
@endphp

<div class="dash-wrap" id="dashWrap">

    {{-- SIDEBAR --}}
    <aside class="sidebar" id="sidebar">
        <div class="sb-brand">
            <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
                <div class="sb-logo-icon"><img src="/images/Shopio.jpeg" alt="Shopio" style="width:50px;height:50px;object-fit:cover;border-radius:9px"></div>
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
                <div class="tb-title">{{ $shop->name }}</div>
                <div class="tb-sub">{{ now()->translatedFormat('l j F Y') }}</div>
            </div>
            <div class="tb-actions">

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
                <div class="kpi" style="--kpi-color:#10b981;--kpi-bg:#ecfdf5">
                    <div class="kpi-icon">💰</div>
                    <div class="kpi-lbl">Revenu net</div>
                    <div class="kpi-val">{{ number_format($caMonth,0,',',' ') }}</div>
                    <div class="kpi-unit">{{ $devise }} · {{ $now->translatedFormat('F Y') }}</div>
                    <div class="kpi-delta {{ $caDelta >= 0 ? 'up':'down' }}">{{ $caDelta >= 0 ? '↑':'↓' }} {{ abs($caDelta) }}% vs mois précédent</div>
                    @if($commissionsPaieesMonth > 0)
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:3px">
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--muted)"><span>CA brut</span><span style="font-family:var(--mono);color:var(--text-2)">{{ number_format($caGrossMonth,0,',',' ') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--muted)"><span>Commissions</span><span style="font-family:var(--mono);color:#dc2626">− {{ number_format($commissionsPaieesMonth,0,',',' ') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;font-weight:700;padding-top:3px;border-top:1px dashed var(--border)"><span style="color:var(--brand)">Net</span><span style="font-family:var(--mono);color:var(--brand)">{{ number_format($caMonth,0,',',' ') }}</span></div>
                    </div>
                    @endif
                </div>
                <div class="kpi" style="--kpi-color:#3b82f6;--kpi-bg:#eff6ff">
                    <div class="kpi-icon">📦</div>
                    <div class="kpi-lbl">Commandes ce mois</div>
                    <div class="kpi-val">{{ $cmdMonth }}</div>
                    <div class="kpi-unit">commandes</div>
                    <div class="kpi-delta {{ $cmdToday >= $cmdYest ? 'up':'down' }}">{{ $cmdToday >= $cmdYest ? '↑':'↓' }} {{ $cmdToday }} aujourd'hui</div>
                </div>
                <div class="kpi" style="--kpi-color:#f59e0b;--kpi-bg:#fffbeb">
                    <div class="kpi-icon">🛒</div>
                    <div class="kpi-lbl">Panier moyen</div>
                    <div class="kpi-val">{{ number_format($panier,0,',',' ') }}</div>
                    <div class="kpi-unit">{{ $devise }} / commande</div>
                    <div class="kpi-delta {{ $caDelta >= 0 ? 'up':'down' }}">{{ $caDelta >= 0 ? '↑':'↓' }} {{ abs($caDelta) }}%</div>
                </div>
                <div class="kpi" style="--kpi-color:#8b5cf6;--kpi-bg:#f5f3ff">
                    <div class="kpi-icon">🚴</div>
                    <div class="kpi-lbl">Taux de livraison</div>
                    <div class="kpi-val">{{ $tauxLiv }}%</div>
                    <div class="kpi-unit">{{ $livres }} / {{ $totalCmdMonth }} livrées</div>
                    <div class="kpi-delta {{ $tauxLiv >= 90 ? 'up':'down' }}">{{ $tauxLiv >= 90 ? '✓ Excellent':'⚠ À améliorer' }}</div>
                </div>
            </div>


            {{-- BLOC A --}}
            <div class="today-grid">
                <div class="today-card">
                    <div class="today-icon">💵</div>
                    <div style="flex:1;min-width:0">
                        <div class="today-lbl">Revenu net aujourd'hui</div>
                        <div class="today-val">{{ number_format($caToday, 0, ',', ' ') }}</div>
                        <div class="today-unit">{{ $devise }}</div>
                        <div class="today-delta {{ $caTodayDelta > 0 ? 'up' : ($caTodayDelta < 0 ? 'down' : 'flat') }}">
                            @if($caTodayDelta > 0) ↑ +{{ $caTodayDelta }}% vs hier
                            @elseif($caTodayDelta < 0) ↓ {{ $caTodayDelta }}% vs hier
                            @else — Même niveau qu'hier @endif
                        </div>
                        @if($commToday > 0)
                        <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.12);display:flex;flex-direction:column;gap:3px">
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.4)"><span>CA brut</span><span style="font-family:var(--mono);color:rgba(255,255,255,.6)">{{ number_format($caGrossToday,0,',',' ') }}</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.4)"><span>Commissions</span><span style="font-family:var(--mono);color:#fca5a5">− {{ number_format($commToday,0,',',' ') }}</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:10px;font-weight:700;padding-top:3px;border-top:1px dashed rgba(255,255,255,.1)"><span style="color:#34d399">Net</span><span style="font-family:var(--mono);color:#34d399">{{ number_format($caToday,0,',',' ') }}</span></div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="today-card">
                    <div class="today-icon">📦</div>
                    <div>
                        <div class="today-lbl">Commandes aujourd'hui</div>
                        <div class="today-val">{{ $cmdToday }}</div>
                        <div class="today-unit">commandes reçues</div>
                        <div class="today-delta {{ $cmdToday >= $cmdYest ? 'up' : 'down' }}">
                            @if($cmdToday > $cmdYest) ↑ +{{ $cmdToday - $cmdYest }} vs hier
                            @elseif($cmdToday < $cmdYest) ↓ {{ $cmdYest - $cmdToday }} de moins vs hier
                            @else — Même niveau qu'hier @endif
                        </div>
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
                        <div class="kanban-ico">{{ $col['ico'] }}</div>
                        <div class="kanban-count">{{ $col['count'] }}</div>
                        <div class="kanban-lbl">{{ $col['label'] }}</div>
                        @if($col['label'] === 'En livraison' && $col['count'] > 0)<div style="font-size:9px;color:var(--muted);margin-top:3px;font-weight:600">🔴 En cours</div>
                        @elseif($col['label'] === 'Terminées')<div style="font-size:9px;color:var(--muted);margin-top:3px">ce mois</div>@endif
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
                        <a href="{{ route('products.create') }}" class="quick-btn" style="--q-bg:#ecfdf5;--q-border:#6ee7b7;--q-color:#10b981"><div class="quick-btn-ico">➕</div><div class="quick-btn-lbl">Nouveau produit</div><div class="quick-btn-sub">Ajouter au catalogue</div></a>
                        <a href="{{ route('boutique.livreurs.index') }}" class="quick-btn" style="--q-bg:#f5f3ff;--q-border:#c4b5fd;--q-color:#8b5cf6"><div class="quick-btn-ico">🚴</div><div class="quick-btn-lbl">Livreurs</div><div class="quick-btn-sub">Voir en ligne</div></a>
                        <a href="{{ route('boutique.payments.index') }}" class="quick-btn" style="--q-bg:#eff6ff;--q-border:#93c5fd;--q-color:#3b82f6"><div class="quick-btn-ico">💳</div><div class="quick-btn-lbl">Paiements</div><div class="quick-btn-sub">Revenus reçus</div></a>
                    </div>
                </div>
            </div>

            {{-- CHART 7J --}}
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
            <div class="card chart-wrap">
                {{-- Header --}}
                <div class="rc-header">
                    <div class="rc-header-left">
                        <div class="rc-title">Revenus — 7 derniers jours</div>
                        <div class="rc-total">
                            <sup>{{ $devise }} </sup>{{ number_format($week7Total, 0, ',', ' ') }}
                        </div>
                        @php
                            $prevWeek = $days7->sum('value'); // placeholder — could add prev-week query
                            // show today vs yesterday as delta proxy
                            $todayVal = $days7->last()['value'];
                            $yesterVal = $days7->count() >= 2 ? $days7->get($days7->count()-2)['value'] : 0;
                            $rcDelta = $yesterVal > 0 ? round((($todayVal - $yesterVal)/$yesterVal)*100,1) : ($todayVal > 0 ? 100 : 0);
                        @endphp
                        <div class="rc-delta {{ $rcDelta > 0 ? 'up' : ($rcDelta < 0 ? 'down' : 'flat') }}">
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
                                <stop offset="0%"   stop-color="#10b981" stop-opacity=".25"/>
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
                            </linearGradient>
                        </defs>

                        {{-- Y gridlines --}}
                        @foreach($yGrid as $g)
                        <line x1="{{ $pL }}" y1="{{ $g['y'] }}" x2="{{ $W - $pR }}" y2="{{ $g['y'] }}"
                              stroke="#e5e7eb" stroke-width="1" stroke-dasharray="{{ $g['val'] > 0 ? '4 4' : 'none' }}"/>
                        <text x="{{ $pL - 6 }}" y="{{ $g['y'] + 4 }}" text-anchor="end"
                              font-size="9" fill="#9ca3af" font-family="monospace">{{ rcFmt($g['val']) }}</text>
                        @endforeach

                        {{-- Area fill --}}
                        <polygon points="{{ $areaPoints }}" fill="url(#areaGrad)"/>

                        {{-- Line --}}
                        <polyline points="{{ $polyline }}" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>

                        {{-- Points + hit areas --}}
                        @foreach($pts as $i => $p)
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4"
                                fill="{{ $p['today'] ? '#10b981' : '#fff' }}"
                                stroke="#10b981" stroke-width="2"
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

            {{-- CONTENT GRID --}}
            <div class="content-grid">
                <div class="card">
                    <div class="card-hd"><span class="card-title">Commandes récentes</span><a href="{{ route('boutique.orders.index') }}" class="btn-ghost btn btn-sm">Voir tout →</a></div>
                    <div style="padding:0 18px">
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
                                    $linit = strtoupper(substr($lp[0],0,1)).strtoupper(substr($lp[1]??'X',0,1));
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

            {{-- SÉLECTEUR DE PÉRIODE --}}
            <div class="card period-card" id="periodCard">
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

    /* Sparklines */
    document.querySelectorAll('.sp-fill').forEach((el, i) => { setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 100 + i * 90); });

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
    let _prevMsg    = -1;
    let _prevOrders = -1;
    let _notifOpen  = false;

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
            right:20px;background:${type==='order'?'#0d1f18':type==='msg'?'#1e40af':'#1f2937'};
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
            const isOrder = a.type === 'order';
            /* Badge "En cours" pour les commandes non livrées */
            const badge = isOrder
                ? `<span style="font-size:9px;font-weight:700;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:20px;padding:1px 6px;white-space:nowrap;flex-shrink:0">En cours</span>`
                : '';
            /* Bouton × uniquement pour les messages (pas pour les commandes) */
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
            /* onclick : dismiss seulement pour les messages, pas pour les commandes */
            const clickHandler = isOrder ? '' : `onclick="_dismissAlert(${a.id})"`;
            const rowBg = isOrder ? '#fffbeb' : '#fff';
            const rowHover = isOrder ? '#fef9ec' : '#f9fafb';

            return `
            <div style="display:flex;align-items:center;border-bottom:1px solid #f3f6f4;background:${rowBg};transition:background .12s"
                 onmouseover="this.style.background='${rowHover}'" onmouseout="this.style.background='${rowBg}'">
                <a href="${a.url}" ${clickHandler}
                   style="display:flex;align-items:center;gap:10px;flex:1;padding:10px 12px;text-decoration:none;min-width:0">
                    <span style="font-size:18px;flex-shrink:0">${a.ico}</span>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:12.5px;font-weight:600;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${a.msg}</div>
                        <div style="display:flex;align-items:center;gap:6px;margin-top:3px">
                            <span style="font-size:10.5px;color:#9ca3af">${a.time}</span>
                            ${badge}
                        </div>
                    </div>
                    <span style="font-size:13px;color:#d1d5db;flex-shrink:0;margin-left:4px">›</span>
                </a>
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
    function pushAlert(ico, msg, url, type) {
        const now = new Date();
        const time = now.getHours().toString().padStart(2,'0')+':'+now.getMinutes().toString().padStart(2,'0');
        _alerts.unshift({ id: ++_alertIdSeq, ico, msg, url, time, type: type || 'msg' });
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
            if (_prevMsg >= 0 && d.messages_unread > _prevMsg) {
                const n = d.messages_unread - _prevMsg;
                showToast(`💬 <div>${n} nouveau${n>1?'x':''} message${n>1?'s':''} client</div>`, 'msg');
                pushAlert('💬', `${n} nouveau${n>1?'x':''} message${n>1?'s':''} non lu${n>1?'s':''}`, '{{ route("boutique.messages.hub") }}', 'msg');
            }
            _prevMsg = d.messages_unread;

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

            /* Cloche totale */
            const total = d.messages_unread + d.orders_pending;
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
</script>
@endpush