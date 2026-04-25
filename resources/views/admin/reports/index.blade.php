{{-- resources/views/admin/reports/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Rapports · ' . ($shop->name ?? 'Boutique'))

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
    --r: 14px; --r-sm: 9px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.06);
    --shadow:     0 4px 16px rgba(0,0,0,.07);
    --sb-w:       232px;
    --top-h:      58px;
}
html { font-family: var(--font); }
body { background: var(--bg); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

.dash-wrap { display: flex; min-height: 100vh; }
.dash-wrap .main { margin-left: var(--sb-w); flex: 1; min-width: 0; }

/* SIDEBAR */
.sidebar {
    background: linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    border-right: 1px solid rgba(99,102,241,.15);
    box-shadow: 6px 0 30px rgba(0,0,0,.35);
    display: flex; flex-direction: column;
    position: fixed; top: 0; left: 0; bottom: 0;
    width: var(--sb-w); overflow-y: scroll;
    scrollbar-width: thin; scrollbar-color: rgba(99,102,241,.35) transparent;
    z-index: 40;
}
.sidebar::-webkit-scrollbar { width: 3px; }
.sidebar::-webkit-scrollbar-track { background: transparent; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(99,102,241,.35); border-radius: 3px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(99,102,241,.6); }
.sb-brand { padding: 18px 16px 14px; border-bottom: 1px solid var(--sb-border); flex-shrink: 0; position: relative; }
.sb-close { display: none; position: absolute; top: 14px; right: 12px; width: 30px; height: 30px; border-radius: 8px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.6); font-size: 18px; cursor: pointer; align-items: center; justify-content: center; transition: background .15s, color .15s; }
.sb-close:hover { background: rgba(239,68,68,.18); border-color: rgba(239,68,68,.3); color: #fca5a5; }
@media (max-width: 900px) { .sb-close { display: flex; } }
.sb-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.sb-logo-icon { width: 36px; height: 36px; border-radius: 9px; overflow: hidden; flex-shrink: 0; }
.sb-shop-name { font-size: 14px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; letter-spacing: -.2px; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 10.5px; color: var(--sb-txt); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: #6ee7b7; flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px #6ee7b7; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }
.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; overflow: visible; }
.sb-section { font-size: 9px; text-transform: uppercase; letter-spacing: 1.4px; color: rgba(255,255,255,.35); padding: 16px 10px 5px; font-weight: 700; }
.sb-item { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-sm); font-size: 13px; font-weight: 500; color: var(--sb-txt); text-decoration: none; transition: background .15s, color .15s; position: relative; }
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.88); }
.sb-item.active { background: var(--sb-act); color: var(--sb-txt-act); box-shadow: 0 2px 12px rgba(99,102,241,.25); }
.sb-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: #a5b4fc; border-radius: 0 3px 3px 0; box-shadow: 2px 0 8px rgba(165,180,252,.5); }
.sb-item .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); transition: background .15s; }
.sb-item:hover .ico { background: rgba(255,255,255,.09); }
.sb-item.active .ico { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.2); }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); min-width: 20px; text-align: center; }
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-sm); font-size: 13px; font-weight: 500; color: var(--sb-txt); cursor: pointer; transition: background .15s, color .15s; user-select: none; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); }
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.88); }
.sb-group-toggle.open { color: rgba(255,255,255,.9); background: rgba(255,255,255,.04); }
.sb-group-toggle .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); transition: background .15s; }
.sb-group-toggle:hover .ico { background: rgba(255,255,255,.09); }
.sb-group-toggle.open .ico { background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.14); }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.25); transition: transform .2s; flex-shrink: 0; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.5); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.07); margin-top: 2px; margin-bottom: 4px; overflow: visible; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 12.5px; padding: 6px 10px; color: rgba(255,255,255,.45); }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.75); }
.sb-sub .sb-item.active { color: var(--sb-txt-act); background: var(--sb-act); }
.sb-scroll-hint { position: sticky; bottom: 0; width: 100%; height: 44px; background: linear-gradient(to bottom, transparent, rgba(17,17,24,.95)); pointer-events: none; z-index: 2; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 8px; transition: opacity .3s; margin-top: -44px; align-self: flex-end; }
.sb-scroll-hint.hidden { opacity: 0; }
.sb-scroll-hint-arrow { display: flex; flex-direction: column; align-items: center; gap: 2px; animation: bounceDown 1.5s ease-in-out infinite; }
.sb-scroll-hint-dot { width: 4px; height: 4px; border-radius: 50%; background: rgba(99,102,241,.7); }
.sb-scroll-hint-dot:nth-child(2) { opacity: .5; margin-top: -2px; }
.sb-scroll-hint-dot:nth-child(3) { opacity: .25; margin-top: -2px; }
@keyframes bounceDown { 0%,100%{transform:translateY(0)} 50%{transform:translateY(4px)} }
.sb-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; position: sticky; bottom: 0; background: linear-gradient(180deg,transparent 0%,#0b0b12 25%); z-index: 1; }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); text-decoration: none; border: 1px solid transparent; transition: background .15s, border-color .15s; }
.sb-user:hover { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.07); }
.sb-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg,#6366f1,#4338ca); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(99,102,241,.45),0 2px 8px rgba(99,102,241,.3); letter-spacing: -.5px; }
.sb-uname { font-size: 12.5px; font-weight: 700; color: rgba(255,255,255,.9); }
.sb-urole { font-size: 10px; color: var(--sb-txt); margin-top: 1px; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.85); font-size: 12px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s, color .15s, border-color .15s; text-align: left; }
.sb-logout:hover { background: rgba(220,38,38,.18); border-color: rgba(220,38,38,.35); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.18); flex-shrink: 0; }
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
.page-hd { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
.page-title { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -.4px; margin: 0 0 4px; }
.page-sub   { font-size: 13px; color: var(--muted); margin: 0; }
.devise-badge { display: inline-flex; align-items: center; gap: 5px; background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); font-size: 11px; font-weight: 700; font-family: var(--mono); padding: 4px 10px; border-radius: 20px; }
.super-badge  { display: inline-flex; align-items: center; gap: 5px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }

/* PERIOD FILTER BAR */
.filter-bar { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 14px 18px; margin-bottom: 22px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; box-shadow: var(--shadow-sm); }
.filter-bar-label { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; flex-shrink: 0; }
.period-btns { display: flex; gap: 6px; flex-wrap: wrap; }
.period-btn { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid var(--border-dk); background: var(--bg); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; }
.period-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-mlt); }
.period-btn.active { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(99,102,241,.3); }
.filter-sep { width: 1px; height: 28px; background: var(--border); flex-shrink: 0; }
.custom-dates { display: flex; align-items: center; gap: 8px; }
.custom-dates input[type="date"] { padding: 5px 10px; border: 1px solid var(--border-dk); border-radius: var(--r-sm); font-size: 12px; font-family: var(--font); color: var(--text); background: var(--bg); cursor: pointer; }
.custom-dates input[type="date"]:focus { outline: none; border-color: var(--brand); }
.btn-apply { padding: 6px 14px; border-radius: var(--r-sm); font-size: 12px; font-weight: 700; border: none; background: var(--brand); color: #fff; cursor: pointer; transition: background .15s; }
.btn-apply:hover { background: var(--brand-dk); }
.btn-export { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: var(--r-sm); font-size: 12px; font-weight: 700; border: 1px solid #22c55e; background: rgba(34,197,94,.08); color: #16a34a; text-decoration: none; transition: all .15s; flex-shrink: 0; }
.btn-export:hover { background: rgba(34,197,94,.18); border-color: #16a34a; }

/* Period label */
.period-label-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; font-size: 12px; color: var(--muted); }
.period-label-pill { background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); font-size: 11.5px; font-weight: 700; padding: 4px 12px; border-radius: 20px; font-family: var(--mono); }

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
.kpi-full { font-size: 10px; color: var(--brand); font-family: var(--mono); margin-top: 3px; font-weight: 700; display: block; }

/* Chart.js card */
.chart-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 22px; }
.chart-card-hd { padding: 13px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--bg); flex-wrap: wrap; gap: 8px; }
.chart-card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.chart-body { padding: 18px 18px 12px; }
.chart-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.chart-tab { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1px solid var(--border-dk); background: var(--bg); color: var(--text-2); cursor: pointer; transition: all .15s; }
.chart-tab.active { background: var(--brand); color: #fff; border-color: var(--brand-dk); }
.chartjs-wrap { position: relative; height: 220px; }

/* Top cards */
.top-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 22px; }
.sp-row { display: flex; align-items: center; gap: 12px; padding: 10px 18px; border-bottom: 1px solid #f3f6f4; }
.sp-row:last-child { border-bottom: none; }
.sp-rank { width: 22px; font-size: 11px; font-weight: 700; color: var(--muted); text-align: center; flex-shrink: 0; }
.sp-rank.top { color: #f59e0b; font-size: 14px; }
.sp-lbl  { flex: 1; font-size: 12.5px; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sp-sub  { font-size: 10.5px; color: var(--muted); font-weight: 400; margin-left: 4px; }
.sp-track { width: 120px; height: 6px; background: #eef1f0; border-radius: 3px; overflow: hidden; flex-shrink: 0; }
.sp-fill  { height: 100%; border-radius: 3px; background: var(--brand); transition: width 1s cubic-bezier(.23,1,.32,1); }
.sp-fill.green { background: #22c55e; }
.sp-val   { font-family: var(--mono); font-size: 12px; font-weight: 700; color: var(--text); width: 50px; text-align: right; flex-shrink: 0; }

/* Two-col grid for top sections */
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 22px; }

/* Client stats donut-like */
.client-stats-wrap { display: flex; flex-direction: column; gap: 14px; padding: 18px; }
.client-stat-row { display: flex; align-items: center; gap: 12px; }
.client-stat-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.client-stat-label { flex: 1; font-size: 13px; font-weight: 600; color: var(--text); }
.client-stat-val { font-family: var(--mono); font-size: 14px; font-weight: 800; color: var(--text); }
.client-stat-pct { font-size: 11px; color: var(--muted); margin-left: 4px; }
.client-bar-wrap { height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; margin-top: 6px; }
.client-bar-fill { height: 100%; border-radius: 4px; transition: width 1s cubic-bezier(.23,1,.32,1); }

/* No data state */
.no-data { padding: 32px 18px; text-align: center; color: var(--muted); font-size: 13px; }
.no-data-ico { font-size: 32px; margin-bottom: 10px; display: block; }

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
    .two-col { grid-template-columns: 1fr; }
    .btn-export { margin-left: 0; width: 100%; justify-content: center; }
    .filter-sep { display: none; }
}
@media (max-width: 560px) {
    .kpi-grid-4, .kpi-grid-3, .kpi-grid-2 { grid-template-columns: 1fr 1fr; }
    .links-grid { grid-template-columns: repeat(2,1fr); }
    .page-wrap { padding: 14px 12px 40px; }
    .kpi-val { font-size: 20px; }
    .sp-track { width: 70px; }
    .custom-dates { flex-wrap: wrap; }
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

    $exportUrl = route('boutique.reports.export', array_filter([
        'period' => $period,
        'from'   => $period === 'custom' ? $dateFrom->format('Y-m-d') : null,
        'to'     => $period === 'custom' ? $dateTo->format('Y-m-d') : null,
    ]));

    // Chart data for JS
    $chartLabels  = $chartMois->pluck('label')->toJson();
    $chartRevenue = $chartMois->pluck('revenue')->toJson();
    $chartOrders  = $chartMois->pluck('orders')->toJson();
    $chartActuel  = $chartMois->search(fn($m) => $m['actuel']);
@endphp

<div class="dash-wrap">

{{-- SIDEBAR --}}
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
            <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
            <span class="sb-shop-name">{{ $shop->name ?? 'Boutique' }}</span>
        </a>
        <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer le menu">✕</button>
        <div class="sb-status">
            <span class="pulse"></span>
            {{ ($shop->is_approved ?? true) ? 'Boutique active' : 'En attente de validation' }}
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
        <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px"><span class="ico">⊞</span> Tableau de bord</a>
        <div class="sb-section">Boutique</div>
        <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">💬</span> Messages</a>
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
            <div class="sb-av">{{ $initials }}</div>
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

    {{-- FILTER BAR --}}
    <form method="GET" action="{{ route('boutique.reports.index') }}" id="filterForm">
    <div class="filter-bar">
        <span class="filter-bar-label">Période :</span>
        <div class="period-btns">
            @foreach(['month' => 'Ce mois', '3months' => '3 mois', '6months' => '6 mois', 'year' => 'Cette année', 'custom' => 'Personnalisée'] as $key => $lbl)
            <button type="button" class="period-btn {{ $period === $key ? 'active' : '' }}"
                onclick="setPeriod('{{ $key }}')">{{ $lbl }}</button>
            @endforeach
        </div>

        <div class="filter-sep"></div>

        <div class="custom-dates" id="customDates" style="{{ $period === 'custom' ? '' : 'display:none' }}">
            <input type="date" name="from" value="{{ $period === 'custom' ? $dateFrom->format('Y-m-d') : '' }}"
                   max="{{ now()->format('Y-m-d') }}" placeholder="Début">
            <span style="color:var(--muted);font-size:12px">→</span>
            <input type="date" name="to" value="{{ $period === 'custom' ? $dateTo->format('Y-m-d') : '' }}"
                   max="{{ now()->format('Y-m-d') }}" placeholder="Fin">
            <button type="submit" class="btn-apply">Appliquer</button>
        </div>

        <input type="hidden" name="period" id="periodInput" value="{{ $period }}">

        <a href="{{ $exportUrl }}" class="btn-export">
            ⬇️ Exporter CSV
        </a>
    </div>
    </form>

    {{-- Period label --}}
    <div class="period-label-bar">
        <span>Données pour :</span>
        <span class="period-label-pill">{{ $periodLabel }}</span>
        <span>· du {{ $dateFrom->format('d/m/Y') }} au {{ $dateTo->format('d/m/Y') }}</span>
    </div>

    {{-- SECTION 1 : KPI PÉRIODE --}}
    <div class="sec-title">📅 {{ $periodLabel }}</div>
    <div class="kpi-grid-2" style="margin-bottom:14px">

        <div class="kpi" style="--kc:#6366f1">
            <div class="kpi-ico">💰</div>
            <div class="kpi-lbl">Revenu net — période</div>
            <div class="kpi-val" title="{{ number_format($revenueThisMonth, 0, ',', ' ') }} {{ $devise }}">
                {{ $fmt($revenueThisMonth) }}
            </div>
            @if($revenueThisMonth >= 1_000_000)
            <div class="kpi-full">= {{ number_format($revenueThisMonth, 0, ',', ' ') }} {{ $devise }}</div>
            @endif
            <div class="kpi-unit">{{ $devise }}</div>
            <div class="kpi-delta {{ $revenueDelta >= 0 ? 'up' : 'down' }}">
                {{ $revenueDelta >= 0 ? '↑' : '↓' }} {{ abs($revenueDelta) }}% vs période précédente
            </div>
            <div class="kpi-explain">CA livrée sur la période, moins les commissions payées aux livreurs.</div>
        </div>

        <div class="kpi" style="--kc:#3b82f6">
            <div class="kpi-ico">📦</div>
            <div class="kpi-lbl">Commandes — période</div>
            <div class="kpi-val">{{ $ordersThisMonth }}</div>
            <div class="kpi-unit">commandes reçues</div>
            <div class="kpi-explain">Toutes les commandes passées sur cette période, quel que soit leur statut.</div>
        </div>

    </div>

    {{-- SECTION 2 : PIPELINE --}}
    <div class="sec-title">📊 Pipeline commandes — période</div>
    <div class="kpi-grid-4" style="margin-bottom:22px">
        <div class="kpi" style="--kc:#6b7280">
            <div class="kpi-ico">🗂️</div><div class="kpi-lbl">Total (all time)</div>
            <div class="kpi-val">{{ $totalOrders }}</div>
            <div class="kpi-unit">depuis le début</div>
        </div>
        <div class="kpi" style="--kc:#f59e0b">
            <div class="kpi-ico">⏳</div><div class="kpi-lbl">En attente</div>
            <div class="kpi-val">{{ $pendingOrders }}</div>
            <div class="kpi-unit">à traiter</div>
        </div>
        <div class="kpi" style="--kc:#8b5cf6">
            <div class="kpi-ico">🚴</div><div class="kpi-lbl">En livraison</div>
            <div class="kpi-val">{{ $deliveringOrders }}</div>
            <div class="kpi-unit">en route</div>
        </div>
        <div class="kpi" style="--kc:#ef4444">
            <div class="kpi-ico">❌</div><div class="kpi-lbl">Annulées</div>
            <div class="kpi-val">{{ $cancelledOrders }}</div>
            <div class="kpi-unit">commandes perdues</div>
        </div>
    </div>

    {{-- SECTION 3 : PERFORMANCE --}}
    <div class="sec-title">🎯 Performance — période</div>
    <div class="kpi-grid-4">
        <div class="kpi" style="--kc:#6366f1">
            <div class="kpi-ico">✅</div><div class="kpi-lbl">Livrées</div>
            <div class="kpi-val">{{ $deliveredOrders }}</div>
            <div class="kpi-unit">livrées avec succès</div>
        </div>
        <div class="kpi" style="--kc:{{ $tauxLivraison >= 80 ? '#6366f1' : ($tauxLivraison >= 50 ? '#f59e0b' : '#ef4444') }}">
            <div class="kpi-ico">{{ $tauxLivraison >= 80 ? '🏆' : ($tauxLivraison >= 50 ? '⚠️' : '🚨') }}</div>
            <div class="kpi-lbl">Taux livraison</div>
            <div class="kpi-val">{{ $tauxLivraison }}%</div>
            <div class="kpi-unit">des commandes livrées</div>
        </div>
        <div class="kpi" style="--kc:{{ $tauxAnnulation <= 10 ? '#22c55e' : ($tauxAnnulation <= 25 ? '#f59e0b' : '#ef4444') }}">
            <div class="kpi-ico">{{ $tauxAnnulation <= 10 ? '✅' : ($tauxAnnulation <= 25 ? '⚠️' : '🚨') }}</div>
            <div class="kpi-lbl">Taux annulation</div>
            <div class="kpi-val">{{ $tauxAnnulation }}%</div>
            <div class="kpi-unit">des commandes annulées</div>
            <div class="kpi-explain">
                @if($tauxAnnulation <= 10) Très faible — excellent signe.
                @elseif($tauxAnnulation <= 25) Modéré — à surveiller.
                @else Élevé — investiguer les raisons.
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
            <div class="kpi-unit">{{ $devise }} / commande</div>
        </div>
    </div>

    {{-- SECTION 4 : GRAPHIQUE CHART.JS --}}
    <div class="sec-title">📈 Évolution sur 6 mois</div>
    <div class="chart-card">
        <div class="chart-card-hd">
            <span class="chart-card-title">Revenus & Commandes — 6 derniers mois</span>
            <div class="chart-tabs">
                <button class="chart-tab active" id="tabRevenue" onclick="switchChart('revenue')">💰 Revenus</button>
                <button class="chart-tab" id="tabOrders" onclick="switchChart('orders')">📦 Commandes</button>
            </div>
        </div>
        <div class="chart-body">
            <div class="chartjs-wrap">
                <canvas id="mainChart"></canvas>
            </div>
        </div>
    </div>

    {{-- SECTION 5 : FINANCES --}}
    <div class="sec-title">💳 Finances globales</div>
    <div class="kpi-grid-3">
        <div class="kpi" style="--kc:#6366f1">
            <div class="kpi-ico">💵</div><div class="kpi-lbl">Revenu net total</div>
            <div class="kpi-val" title="{{ number_format($totalRevenue, 0, ',', ' ') }} {{ $devise }}">
                {{ $fmt($totalRevenue) }}
            </div>
            @if($totalRevenue >= 1_000_000)
            <div class="kpi-full">= {{ number_format($totalRevenue, 0, ',', ' ') }} {{ $devise }}</div>
            @endif
            <div class="kpi-unit">{{ $devise }}</div>
            <div class="kpi-explain">CA livré - commissions payées. Votre vrai revenu net.</div>
        </div>
        <div class="kpi" style="--kc:#f59e0b">
            <div class="kpi-ico">⏳</div><div class="kpi-lbl">Commissions à payer</div>
            <div class="kpi-val" title="{{ number_format($commissionsPending, 0, ',', ' ') }} {{ $devise }}">
                {{ $fmt($commissionsPending) }}
            </div>
            @if($commissionsPending >= 1_000_000)
            <div class="kpi-full">= {{ number_format($commissionsPending, 0, ',', ' ') }} {{ $devise }}</div>
            @endif
            <div class="kpi-unit">{{ $devise }} · à régler</div>
            <div class="kpi-explain">
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
            <div class="kpi-unit">{{ $devise }} · réglés</div>
        </div>
    </div>

    {{-- SECTION 6 : ÉQUIPE --}}
    <div class="sec-title">👥 Équipe</div>
    <div class="kpi-grid-2" style="margin-bottom:22px">
        <div class="kpi" style="--kc:#2563eb">
            <div class="kpi-ico">👤</div><div class="kpi-lbl">Vendeurs actifs</div>
            <div class="kpi-val">{{ $vendors }}</div>
            <div class="kpi-unit">vendeurs rattachés</div>
        </div>
        <div class="kpi" style="--kc:#7c3aed">
            <div class="kpi-ico">🚴</div><div class="kpi-lbl">Livreurs actifs</div>
            <div class="kpi-val">{{ $livreurs }}</div>
            <div class="kpi-unit">livreurs rattachés</div>
        </div>
    </div>

    {{-- SECTION 7 : TOP PRODUITS + TOP LIVREURS (côte à côte) --}}
    <div class="sec-title">🏆 Classements — {{ $periodLabel }}</div>
    <div class="two-col">

        {{-- Top produits --}}
        <div class="top-card">
            <div class="chart-card-hd">
                <span class="chart-card-title">🏷️ Top produits vendus</span>
                <a href="{{ route('products.top') }}" style="font-size:11px;color:var(--brand);font-weight:600;text-decoration:none">Voir tous →</a>
            </div>
            @if(isset($topProducts) && $topProducts->count() > 0)
            @php $maxVentes = $topProducts->max('ventes') ?: 1; $rangs = ['🥇','🥈','🥉','4','5']; @endphp
            @foreach($topProducts as $i => $product)
            @php $pct = round(($product->ventes/$maxVentes)*100); @endphp
            <div class="sp-row">
                <span class="sp-rank {{ $i < 3 ? 'top' : '' }}">{{ $rangs[$i] ?? $i+1 }}</span>
                <span class="sp-lbl" title="{{ $product->name }}">{{ Str::limit($product->name, 28) }}</span>
                <div class="sp-track"><div class="sp-fill" data-pct="{{ $pct }}" style="width:0%"></div></div>
                <span class="sp-val">{{ $product->ventes }}<span class="sp-sub">vte</span></span>
            </div>
            @endforeach
            @else
            <div class="no-data"><span class="no-data-ico">📭</span>Aucune vente sur cette période.</div>
            @endif
        </div>

        {{-- Top livreurs --}}
        <div class="top-card">
            <div class="chart-card-hd">
                <span class="chart-card-title">🚴 Top livreurs</span>
                <a href="{{ route('boutique.livreurs.index') }}" style="font-size:11px;color:var(--brand);font-weight:600;text-decoration:none">Voir tous →</a>
            </div>
            @if(isset($topLivreurs) && $topLivreurs->count() > 0)
            @php $maxLiv = $topLivreurs->max('livraisons') ?: 1; $rangs2 = ['🥇','🥈','🥉','4','5']; @endphp
            @foreach($topLivreurs as $i => $livreur)
            @php $pct2 = round(($livreur->livraisons/$maxLiv)*100); @endphp
            <div class="sp-row">
                <span class="sp-rank {{ $i < 3 ? 'top' : '' }}">{{ $rangs2[$i] ?? $i+1 }}</span>
                <span class="sp-lbl" title="{{ $livreur->name }}">{{ Str::limit($livreur->name, 22) }}</span>
                <div class="sp-track"><div class="sp-fill green" data-pct="{{ $pct2 }}" style="width:0%"></div></div>
                <span class="sp-val">{{ $livreur->livraisons }}<span class="sp-sub">liv</span></span>
            </div>
            @endforeach
            @else
            <div class="no-data"><span class="no-data-ico">🚴</span>Aucune livraison sur cette période.</div>
            @endif
        </div>

    </div>

    {{-- SECTION 8 : CLIENTS NOUVEAUX VS FIDÈLES --}}
    <div class="sec-title">👥 Rétention clients — {{ $periodLabel }}</div>
    @php
        $cTotal    = $clientStats['total']    ?? 0;
        $cNouveaux = $clientStats['nouveaux'] ?? 0;
        $cFideles  = $clientStats['fideles']  ?? 0;
        $pctNouv   = $cTotal > 0 ? round(($cNouveaux / $cTotal) * 100) : 0;
        $pctFid    = $cTotal > 0 ? round(($cFideles  / $cTotal) * 100) : 0;
    @endphp
    <div class="two-col" style="margin-bottom:22px">
        <div class="top-card">
            <div class="chart-card-hd">
                <span class="chart-card-title">👤 Clients sur la période</span>
            </div>
            <div class="client-stats-wrap">
                <div>
                    <div class="client-stat-row">
                        <span class="client-stat-dot" style="background:#6366f1"></span>
                        <span class="client-stat-label">Nouveaux clients</span>
                        <span class="client-stat-val">{{ $cNouveaux }}<span class="client-stat-pct">({{ $pctNouv }}%)</span></span>
                    </div>
                    <div class="client-bar-wrap">
                        <div class="client-bar-fill" data-pct="{{ $pctNouv }}" style="width:0%;background:#6366f1"></div>
                    </div>
                </div>
                <div>
                    <div class="client-stat-row">
                        <span class="client-stat-dot" style="background:#22c55e"></span>
                        <span class="client-stat-label">Clients fidèles</span>
                        <span class="client-stat-val">{{ $cFideles }}<span class="client-stat-pct">({{ $pctFid }}%)</span></span>
                    </div>
                    <div class="client-bar-wrap">
                        <div class="client-bar-fill" data-pct="{{ $pctFid }}" style="width:0%;background:#22c55e"></div>
                    </div>
                </div>
                <div style="margin-top:8px;padding-top:10px;border-top:1px solid var(--border)">
                    <div class="client-stat-row">
                        <span class="client-stat-dot" style="background:var(--muted)"></span>
                        <span class="client-stat-label" style="color:var(--muted)">Total clients uniques</span>
                        <span class="client-stat-val" style="color:var(--muted)">{{ $cTotal }}</span>
                    </div>
                    <div class="kpi-explain" style="margin-top:8px">
                        Un client <strong>fidèle</strong> a déjà commandé avant cette période.
                        Un client <strong>nouveau</strong> commande pour la 1ère fois.
                    </div>
                </div>
            </div>
        </div>

        <div class="kpi-grid-2" style="margin:0;align-content:start;gap:12px">
            <div class="kpi" style="--kc:#6366f1;grid-column:1/-1">
                <div class="kpi-ico">📋</div>
                <div class="kpi-lbl">Export CSV</div>
                <div style="font-size:13px;color:var(--text-2);margin:6px 0 12px;line-height:1.5">
                    Téléchargez toutes les commandes de la période sélectionnée avec détails (client, livreur, montants, statut).
                </div>
                <a href="{{ $exportUrl }}" class="btn-export" style="display:inline-flex;margin:0">
                    ⬇️ Exporter — {{ $periodLabel }}
                </a>
            </div>
            <div class="kpi" style="--kc:#f59e0b">
                <div class="kpi-ico">📊</div>
                <div class="kpi-lbl">Taux rétention</div>
                <div class="kpi-val">{{ $cTotal > 0 ? $pctFid : 0 }}%</div>
                <div class="kpi-unit">clients sont fidèles</div>
                <div class="kpi-explain">
                    @if($pctFid >= 50) Excellent — vous fidélisez bien vos clients.
                    @elseif($pctFid >= 25) Moyen — travaillez la fidélisation.
                    @else Faible — concentrez-vous sur la rétention.
                    @endif
                </div>
            </div>
            <div class="kpi" style="--kc:#6366f1">
                <div class="kpi-ico">✨</div>
                <div class="kpi-lbl">Nouveaux clients</div>
                <div class="kpi-val">{{ $cNouveaux }}</div>
                <div class="kpi-unit">1ère commande</div>
                <div class="kpi-explain">Clients qui commandent pour la 1ère fois sur cette période.</div>
            </div>
        </div>
    </div>

    {{-- SECTION 9 : LIENS RAPIDES --}}
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

/* Period filter */
function setPeriod(p) {
    document.getElementById('periodInput').value = p;
    document.querySelectorAll('.period-btn').forEach(b => b.classList.toggle('active', b.getAttribute('onclick').includes("'"+p+"'")));
    document.getElementById('customDates').style.display = p === 'custom' ? 'flex' : 'none';
    if (p !== 'custom') document.getElementById('filterForm').submit();
}

/* Chart.js */
const chartLabels  = {!! $chartLabels !!};
const chartRevenue = {!! $chartRevenue !!};
const chartOrders  = {!! $chartOrders !!};
const actuelIdx    = {{ $chartActuel !== false ? (int)$chartActuel : 5 }};
const devise       = '{{ $devise }}';

const bgColors = chartLabels.map((_, i) => i === actuelIdx ? 'rgba(99,102,241,1)' : 'rgba(99,102,241,0.5)');
const bgGreen  = chartLabels.map((_, i) => i === actuelIdx ? 'rgba(34,197,94,1)'   : 'rgba(34,197,94,0.5)');

let currentMode = 'revenue';
let mainChart;

function formatNum(n) {
    if (n >= 1000000) return (n/1000000).toFixed(1).replace('.',',')+' M';
    if (n >= 1000)    return (n/1000).toFixed(0)+' k';
    return n.toString();
}

function buildChart(mode) {
    const ctx = document.getElementById('mainChart').getContext('2d');
    const isRev = mode === 'revenue';
    const data  = isRev ? chartRevenue : chartOrders;
    const color = isRev ? bgColors : bgGreen;
    const label = isRev ? ('Revenu net (' + devise + ')') : 'Commandes';

    if (mainChart) mainChart.destroy();

    mainChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label,
                data,
                backgroundColor: color,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => isRev
                            ? ' ' + formatNum(ctx.parsed.y) + ' ' + devise
                            : ' ' + ctx.parsed.y + ' commande(s)'
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,.04)' },
                    ticks: {
                        font: { size: 11 }, color: '#94a3b8',
                        callback: (v) => isRev ? formatNum(v) : v
                    }
                }
            }
        }
    });
}

function switchChart(mode) {
    currentMode = mode;
    document.getElementById('tabRevenue').classList.toggle('active', mode === 'revenue');
    document.getElementById('tabOrders').classList.toggle('active',  mode === 'orders');
    buildChart(mode);
}

document.addEventListener('DOMContentLoaded', () => {
    buildChart('revenue');

    /* Sparklines top produits & livreurs */
    document.querySelectorAll('.sp-fill').forEach((el, i) => {
        setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 200 + i * 80);
    });

    /* Client bars */
    document.querySelectorAll('.client-bar-fill').forEach((el, i) => {
        setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 300 + i * 150);
    });
});
</script>
@endpush
