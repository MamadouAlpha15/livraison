{{-- resources/views/vendeur/orders/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Commande #' . $order->id . ' · ' . $shop->name)
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --brand:      #6366f1; --brand-dk:  #4f46e5;
    --brand-lt:   #e0e7ff; --brand-mlt: #eef2ff;
    --sb-bg:      #0e0e16; --sb-border: rgba(255,255,255,.08);
    --sb-act:     rgba(99,102,241,.52); --sb-hov: rgba(255,255,255,.07);
    --sb-txt:     rgba(255,255,255,.62); --sb-txt-act: #fff;
    --bg:         #f8fafc; --surface:   #ffffff;
    --border:     #e2e8f0; --border-dk: #cbd5e1;
    --text:       #0f172a; --text-2:    #475569; --muted: #94a3b8;
    --green:      #10b981; --green-lt:  #ecfdf5; --green-dk: #065f46;
    --warning:    #f59e0b; --warning-lt:#fef3c7;
    --red:        #ef4444; --red-lt:    #fef2f2;
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r: 14px; --r-sm: 9px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.06);
    --shadow:     0 4px 16px rgba(0,0,0,.08);
    --sb-w: 232px; --top-h: 58px;
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
    width: var(--sb-w); overflow-y: scroll;
    scrollbar-width: thin; scrollbar-color: rgba(99,102,241,.35) transparent;
    z-index: 40;
}
.sidebar::-webkit-scrollbar { width: 3px; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(99,102,241,.35); border-radius: 3px; }
.sb-brand { padding: 18px 16px 14px; border-bottom: 1px solid var(--sb-border); flex-shrink: 0; position: relative; }
.sb-close { display: none; position: absolute; top: 14px; right: 12px; width: 30px; height: 30px; border-radius: 8px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.6); font-size: 18px; cursor: pointer; align-items: center; justify-content: center; transition: background .15s; }
.sb-close:hover { background: rgba(239,68,68,.18); color: #fca5a5; }
@media (max-width: 900px) { .sb-close { display: flex; } }
.sb-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.sb-logo-icon { width: 36px; height: 36px; border-radius: 9px; overflow: hidden; flex-shrink: 0; }
.sb-shop-name { font-size: 14px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; letter-spacing: -.2px; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 10.5px; color: var(--sb-txt); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: #6ee7b7; flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px #6ee7b7; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }
.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; }
.sb-section { font-size: 9px; text-transform: uppercase; letter-spacing: 1.4px; color: rgba(255,255,255,.35); padding: 16px 10px 5px; font-weight: 700; }
.sb-item { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-sm); font-size: 13px; font-weight: 500; color: var(--sb-txt); text-decoration: none; transition: background .15s, color .15s; position: relative; }
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.88); }
.sb-item.active { background: var(--sb-act); color: var(--sb-txt-act); box-shadow: 0 2px 12px rgba(99,102,241,.25); }
.sb-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: #a5b4fc; border-radius: 0 3px 3px 0; }
.sb-item .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); }
.sb-item.active .ico { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.2); }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); }
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-sm); font-size: 13px; font-weight: 500; color: var(--sb-txt); cursor: pointer; transition: background .15s; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); }
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.88); }
.sb-group-toggle .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.25); transition: transform .2s; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.5); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.07); margin-top: 2px; margin-bottom: 4px; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 12.5px; padding: 6px 10px; color: rgba(255,255,255,.45); }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.75); }
.sb-sub .sb-item.active { color: var(--sb-txt-act); background: var(--sb-act); }
.sb-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; background: linear-gradient(180deg,transparent 0%,#0b0b12 25%); }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); text-decoration: none; border: 1px solid transparent; transition: background .15s; }
.sb-user:hover { background: rgba(255,255,255,.06); }
.sb-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg,#6366f1,#4338ca); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(99,102,241,.45); }
.sb-uname { font-size: 12.5px; font-weight: 700; color: rgba(255,255,255,.9); }
.sb-urole { font-size: 10px; color: var(--sb-txt); margin-top: 1px; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.85); font-size: 12px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s; text-align: left; }
.sb-logout:hover { background: rgba(220,38,38,.18); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.18); flex-shrink: 0; }
.sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 39; }

/* ══ TOPBAR ══ */
.main { display: flex; flex-direction: column; min-width: 0; }
.topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 22px; height: var(--top-h); display: flex; align-items: center; gap: 12px; position: sticky; top: 0; z-index: 30; box-shadow: var(--shadow-sm); }
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); font-size: 20px; }
.tb-info { flex: 1; min-width: 0; }
.tb-title { font-size: 14px; font-weight: 700; color: var(--text); }
.tb-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* ══ PAGE ══ */
.page-wrap { padding: 22px 22px 60px; max-width: 1100px; }

/* ══ BREADCRUMB ══ */
.breadcrumb { display: flex; align-items: center; gap: 6px; margin-bottom: 18px; font-size: 12.5px; color: var(--muted); flex-wrap: wrap; }
.breadcrumb a { color: var(--brand); font-weight: 600; text-decoration: none; }
.breadcrumb a:hover { text-decoration: underline; }
.breadcrumb span { color: var(--muted); }

/* ══ HERO COMMANDE ══ */
.order-hero {
    background: linear-gradient(135deg, #0f0f59 0%, #1a1060 50%, #2d1b6e 100%);
    border-radius: var(--r); padding: 24px 28px; margin-bottom: 20px;
    display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap;
    position: relative; overflow: hidden;
    box-shadow: 0 8px 32px rgba(99,102,241,.2);
}
.order-hero::before {
    content: ''; position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Ccircle cx='30' cy='30' r='1.5' fill='%236366f1' opacity='.08'/%3E%3C/svg%3E");
}
.oh-left { position: relative; z-index: 1; }
.oh-id { font-size: 26px; font-weight: 900; color: #fff; letter-spacing: -.5px; margin-bottom: 6px; }
.oh-id span { color: #a5b4fc; }
.oh-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.oh-date { font-size: 12px; color: rgba(255,255,255,.5); }
.oh-right { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
.oh-total { font-size: 32px; font-weight: 900; color: #fff; font-family: var(--mono); letter-spacing: -1px; line-height: 1; }
.oh-total-lbl { font-size: 10px; color: rgba(255,255,255,.4); font-family: var(--font); margin-bottom: 2px; text-align: right; }
.oh-actions { display: flex; gap: 8px; flex-wrap: wrap; }

/* ══ STATUT BADGE ══ */
.status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
.s-attente   { background: rgba(245,158,11,.15); color: #fcd34d; border: 1px solid rgba(245,158,11,.3); }
.s-confirmee { background: rgba(99,102,241,.18); color: #a5b4fc;  border: 1px solid rgba(99,102,241,.35); }
.s-livraison { background: rgba(6,182,212,.18);  color: #67e8f9;  border: 1px solid rgba(6,182,212,.35); }
.s-livree    { background: rgba(16,185,129,.18); color: #6ee7b7;  border: 1px solid rgba(16,185,129,.35); }
.s-annulee   { background: rgba(239,68,68,.15);  color: #fca5a5;  border: 1px solid rgba(239,68,68,.3); }

/* ══ BOUTONS ══ */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: var(--r-sm); font-size: 13px; font-weight: 600; font-family: var(--font); border: 1.5px solid var(--border-dk); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(99,102,241,.3); }
.btn-primary:hover { background: var(--brand-dk); color: #fff; }
.btn-success { background: var(--green); color: #fff; border-color: var(--green-dk); }
.btn-success:hover { opacity: .88; }
.btn-danger  { background: var(--red-lt); color: var(--red); border-color: #fca5a5; }
.btn-danger:hover  { background: var(--red); color: #fff; border-color: var(--red); }
.btn-ghost   { background: rgba(255,255,255,.1); color: #fff; border-color: rgba(255,255,255,.2); }
.btn-ghost:hover { background: rgba(255,255,255,.2); color: #fff; }
.btn-sm { padding: 6px 12px; font-size: 12px; }

/* ══ GRILLE 2 COL ══ */
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px; }
.grid-13 { display: grid; grid-template-columns: 1fr minmax(280px,340px); gap: 18px; align-items: start; }

/* ══ CARDS ══ */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 18px; }
.card-hd { padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--bg); flex-wrap: wrap; gap: 8px; }
.card-title { font-size: 13px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 7px; }
.card-body { padding: 18px; }

/* ══ CLIENT ══ */
.client-row { display: flex; align-items: center; gap: 14px; }
.client-av { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--brand), #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 3px rgba(99,102,241,.2); }
.client-name { font-size: 15px; font-weight: 700; color: var(--text); }
.client-meta { font-size: 12px; color: var(--muted); margin-top: 2px; }
.client-meta a { color: var(--brand); text-decoration: none; font-weight: 600; }

/* ══ INFO LIST ══ */
.info-list { display: flex; flex-direction: column; gap: 10px; }
.info-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; font-size: 13px; }
.info-row .lbl { color: var(--muted); font-weight: 500; white-space: nowrap; }
.info-row .val { font-weight: 600; color: var(--text); text-align: right; }
.info-row .val.mono { font-family: var(--mono); }

/* ══ ITEMS TABLE ══ */
.items-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
.items-tbl thead th { padding: 10px 14px; text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--muted); background: var(--bg); border-bottom: 1px solid var(--border); }
.items-tbl tbody td { padding: 13px 14px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.items-tbl tbody tr:last-child td { border-bottom: none; }
.items-tbl tbody tr:hover td { background: #fafbff; }
.prod-thumb { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border); flex-shrink: 0; background: var(--bg); }
.prod-thumb-placeholder { width: 44px; height: 44px; border-radius: 8px; background: var(--bg); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.prod-info { display: flex; align-items: center; gap: 10px; }
.prod-name { font-weight: 600; color: var(--text); font-size: 13px; }
.prod-ref  { font-size: 10.5px; color: var(--muted); margin-top: 1px; font-family: var(--mono); }
.qty-badge { display: inline-flex; align-items: center; justify-content: center; background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); font-size: 12px; font-weight: 700; border-radius: 6px; padding: 2px 8px; font-family: var(--mono); }
.amount { font-family: var(--mono); font-weight: 700; color: var(--text); }
.amount small { font-size: 10px; color: var(--muted); font-weight: 500; }

/* ══ TOTAUX ══ */
.totaux { display: flex; flex-direction: column; gap: 0; }
.tot-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 18px; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
.tot-row:last-child { border-bottom: none; }
.tot-row .lbl { color: var(--text-2); font-weight: 500; }
.tot-row .val { font-family: var(--mono); font-weight: 700; color: var(--text); }
.tot-row.grand-total { background: linear-gradient(135deg, #0f0f59, #1e1b4b); }
.tot-row.grand-total .lbl { color: rgba(255,255,255,.65); font-weight: 700; font-size: 13.5px; }
.tot-row.grand-total .val { color: #fff; font-size: 18px; }

/* ══ LIVREUR CARD ══ */
.livreur-row { display: flex; align-items: center; gap: 14px; }
.livreur-av { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #0891b2, #0e7490); display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 3px rgba(6,182,212,.2); }
.livreur-name { font-size: 14px; font-weight: 700; color: var(--text); }
.livreur-meta { font-size: 12px; color: var(--muted); margin-top: 2px; }
.avail-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 4px; vertical-align: middle; }
.avail-yes { background: #10b981; box-shadow: 0 0 4px #10b981; }
.avail-no  { background: var(--muted); }

/* ══ COMMISSION CARD ══ */
.comm-status { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.comm-amount-big { font-size: 28px; font-weight: 900; font-family: var(--mono); letter-spacing: -1px; color: #92400e; }
.comm-paid-amount { color: var(--green-dk); }

/* ══ TIMELINE ══ */
.timeline { display: flex; flex-direction: column; gap: 0; }
.tl-item { display: flex; gap: 14px; padding: 12px 0; position: relative; }
.tl-item:not(:last-child)::after { content: ''; position: absolute; left: 15px; top: 40px; bottom: 0; width: 2px; background: var(--border); }
.tl-dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; z-index: 1; }
.tl-dot.done   { background: var(--brand-mlt); border: 2px solid var(--brand); }
.tl-dot.active { background: linear-gradient(135deg, var(--brand), #8b5cf6); box-shadow: 0 0 0 3px rgba(99,102,241,.2); }
.tl-dot.pending { background: var(--bg); border: 2px solid var(--border); }
.tl-dot.cancelled { background: var(--red-lt); border: 2px solid #fca5a5; }
.tl-content { flex: 1; padding-top: 5px; }
.tl-label { font-size: 13px; font-weight: 700; color: var(--text); }
.tl-date  { font-size: 11px; color: var(--muted); margin-top: 2px; }
.tl-done  { color: var(--brand); }
.tl-pend  { color: var(--muted); }
.tl-canc  { color: var(--red); }

/* ══ FLASH ══ */
.flash { padding: 10px 14px; border-radius: var(--r-sm); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.flash-success { background: #eef2ff; border-color: #a5b4fc; color: #3730a3; }
.flash-warning { background: var(--warning-lt); border-color: #fcd34d; color: #92400e; }

/* ══ EMPTY BLOCK ══ */
.empty-block { padding: 32px 20px; text-align: center; }
.empty-block .ico { font-size: 32px; display: block; margin-bottom: 8px; opacity: .3; }
.empty-block p { font-size: 13px; color: var(--muted); margin: 0; }

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .grid-13 { grid-template-columns: 1fr; }
    .grid-2  { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .page-wrap { padding: 14px 12px 40px; }
    .order-hero { padding: 18px 16px; }
    .oh-id { font-size: 20px; }
    .oh-total { font-size: 24px; }
    .oh-right { align-items: flex-start; }
    .items-tbl thead th:nth-child(3),
    .items-tbl tbody td:nth-child(3) { display: none; }
}
</style>
@endpush

@section('content')
@php
    $user     = auth()->user();
    $parts    = explode(' ', $user->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $init     = fn(string $n): string => strtoupper(substr(explode(' ',$n)[0],0,1))
                                       . strtoupper(substr(explode(' ',$n)[1] ?? substr($n,1,1),0,1));

    $pendingCount = $shop->orders()
        ->whereIn('status', ['en_attente', 'pending'])
        ->count();

    $devise = $shop->currency ?? 'GNF';

    /* Badge statut */
    $statusMap = [
        'en_attente'   => ['label' => 'En attente',   'class' => 's-attente',   'ico' => '⏳'],
        'pending'      => ['label' => 'En attente',   'class' => 's-attente',   'ico' => '⏳'],
        'confirmée'    => ['label' => 'Confirmée',    'class' => 's-confirmee', 'ico' => '✅'],
        'en_livraison' => ['label' => 'En livraison', 'class' => 's-livraison', 'ico' => '🚴'],
        'livrée'       => ['label' => 'Livrée',       'class' => 's-livree',    'ico' => '📦'],
        'annulée'      => ['label' => 'Annulée',      'class' => 's-annulee',   'ico' => '✕'],
    ];
    $sInfo = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 's-attente', 'ico' => '•'];

    $canConfirm = $order->status === 'en_attente' || $order->status === 'pending';
    $canCancel  = in_array($order->status, ['en_attente', 'pending', 'confirmée']);
    $canAssign  = $order->status === 'confirmée' && !$order->livreur_id;

    /* Subtotal articles */
    $subtotal = $order->items->sum(fn($i) => $i->price * $i->quantity);
@endphp

<div class="dash-wrap">

{{-- ══ SIDEBAR ══ --}}
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
            <div class="sb-logo-icon"><img src="/images/shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
            <span class="sb-shop-name">{{ $shop->name }}</span>
        </a>
        <button class="sb-close" id="btnCloseSidebar">✕</button>
        <div class="sb-status">
            <span class="pulse"></span>
            {{ $shop->is_approved ? 'Boutique active' : 'En attente de validation' }}
            &nbsp;·&nbsp;
            {{ ucfirst($user->role_in_shop ?? $user->role) }}
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('boutique.dashboard') }}" class="sb-item">
            <span class="ico">⊞</span> Tableau de bord
        </a>
        <div class="sb-section">Boutique</div>
        <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">💬</span> Messages</a>
        <a href="{{ route('boutique.orders.index') }}" class="sb-item active">
            <span class="ico">📦</span> Commandes
            @if($pendingCount > 0)<span class="sb-badge">{{ $pendingCount }}</span>@endif
        </a>
        <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">🏷️</span> Produits</a>
        <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">👥</span> Clients</a>
        <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">🧑‍💼</span> Équipe</a>
        <div class="sb-section">Livraison</div>
        <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">🚴</span> Livreurs</a>
        <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">🏢</span> Partenaires</a>
        <div class="sb-section">Finances</div>
        <div class="sb-group">
            <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                <span class="ico">💰</span> Finances & Rapports
                <span class="sb-arrow">▶</span>
            </button>
            <div class="sb-sub">
                <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">💳</span> Paiements</a>
                <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">📊</span> Commissions</a>
                <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">📋</span> Rapports</a>
                @if($user->role === 'admin')
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
                <div class="sb-uname">{{ Str::limit($user->name, 20) }}</div>
                <div class="sb-urole">{{ $user->role === 'admin' ? 'Administrateur' : ucfirst($user->role) }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-logout"><span class="ico">⎋</span> Se déconnecter</button>
        </form>
    </div>
</aside>
<div class="sb-overlay" id="sbOverlay"></div>

{{-- ══ MAIN ══ --}}
<main class="main">
    <div class="topbar">
        <button class="btn-hamburger" id="btnMenu">☰</button>
        <div class="tb-info">
            <div class="tb-title">📦 Commande #{{ $order->id }}</div>
            <div class="tb-sub">{{ $shop->name }} · <span style="font-family:var(--mono);font-size:10px;background:var(--brand-mlt);color:var(--brand-dk);padding:1px 7px;border-radius:10px;border:1px solid var(--brand-lt)">{{ $devise }}</span></div>
        </div>
        <a href="{{ route('boutique.orders.index') }}" class="btn btn-sm" style="flex-shrink:0">← Retour</a>
    </div>

    <div class="page-wrap">

        {{-- Breadcrumb --}}
        <div class="breadcrumb">
            <a href="{{ route('boutique.dashboard') }}">Tableau de bord</a>
            <span>›</span>
            <a href="{{ route('boutique.orders.index') }}">Commandes</a>
            <span>›</span>
            <span style="color:var(--text);font-weight:600">Commande #{{ $order->id }}</span>
        </div>

        {{-- Flash --}}
        @foreach(['success','warning'] as $t)
            @if(session($t))
            <div class="flash flash-{{ $t }}">
                <span>{{ $t === 'success' ? '✓' : '⚠️' }}</span> {{ session($t) }}
            </div>
            @endif
        @endforeach

        {{-- ══ HERO ══ --}}
        <div class="order-hero">
            <div class="oh-left">
                <div class="oh-id">Commande <span>#{{ $order->id }}</span></div>
                <div class="oh-meta">
                    <span class="status-badge {{ $sInfo['class'] }}">{{ $sInfo['ico'] }} {{ $sInfo['label'] }}</span>
                    <span class="oh-date">📅 {{ $order->created_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
            <div class="oh-right">
                <div>
                    <div class="oh-total-lbl">Total commande</div>
                    <div class="oh-total">{{ number_format($order->total, 0, ',', ' ') }} <span style="font-size:14px;font-weight:600;font-family:var(--font)">{{ $devise }}</span></div>
                </div>
                <div class="oh-actions">
                    @if($canConfirm)
                    <form method="POST" action="{{ route('boutique.orders.confirm', $order) }}" style="display:inline">
                        @csrf @method('PUT')
                        <button type="submit" class="btn btn-success">✅ Confirmer</button>
                    </form>
                    @endif
                    @if($canAssign)
                    <a href="{{ route('boutique.orders.assign.show', $order) }}" class="btn btn-primary">🚴 Assigner un livreur</a>
                    @endif
                    @if($canCancel)
                    <form method="POST" action="{{ route('boutique.orders.cancel', $order) }}" style="display:inline"
                          onsubmit="return confirm('Annuler cette commande ?')">
                        @csrf @method('PUT')
                        <button type="submit" class="btn btn-ghost btn-sm">✕ Annuler</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══ GRILLE PRINCIPALE ══ --}}
        <div class="grid-13">

            {{-- ── Colonne gauche ── --}}
            <div>
                {{-- Articles commandés --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">🛒 Articles commandés</span>
                        <span style="font-size:12px;color:var(--muted);font-family:var(--mono)">{{ $order->items->count() }} article{{ $order->items->count() > 1 ? 's' : '' }}</span>
                    </div>
                    @if($order->items->isEmpty())
                    <div class="empty-block">
                        <span class="ico">🛒</span>
                        <p>Aucun article dans cette commande.</p>
                    </div>
                    @else
                    <table class="items-tbl">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th style="text-align:center">Qté</th>
                                <th style="text-align:right">Prix unit.</th>
                                <th style="text-align:right">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            @php $prod = $item->product; @endphp
                            <tr>
                                <td>
                                    <div class="prod-info">
                                        @if($prod?->image)
                                            <img src="{{ asset('storage/' . $prod->image) }}" alt="" class="prod-thumb">
                                        @else
                                            <div class="prod-thumb-placeholder">📦</div>
                                        @endif
                                        <div>
                                            <div class="prod-name">{{ $prod?->name ?? 'Produit supprimé' }}</div>
                                            <div class="prod-ref">ref:{{ $prod?->id ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align:center">
                                    <span class="qty-badge">×{{ $item->quantity }}</span>
                                </td>
                                <td style="text-align:right">
                                    <span class="amount">{{ number_format($item->price, 0, ',', ' ') }} <small>{{ $devise }}</small></span>
                                </td>
                                <td style="text-align:right">
                                    <span class="amount" style="color:var(--brand-dk)">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} <small>{{ $devise }}</small></span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Totaux --}}
                    <div class="totaux" style="border-top:1px solid var(--border)">
                        <div class="tot-row">
                            <span class="lbl">Sous-total articles</span>
                            <span class="val">{{ number_format($subtotal, 0, ',', ' ') }} {{ $devise }}</span>
                        </div>
                        @if($order->delivery_fee)
                        <div class="tot-row">
                            <span class="lbl">🚴 Frais de livraison</span>
                            <span class="val">+ {{ number_format($order->delivery_fee, 0, ',', ' ') }} {{ $devise }}</span>
                        </div>
                        @endif
                        <div class="tot-row grand-total">
                            <span class="lbl">Total</span>
                            <span class="val">{{ number_format($order->total, 0, ',', ' ') }} {{ $devise }}</span>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Livraison --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">🚴 Livraison</span>
                        @if($order->livreur_id)
                            <a href="{{ route('boutique.orders.assign.show', $order) }}" class="btn btn-sm">✏️ Changer</a>
                        @elseif($order->status === 'confirmée')
                            <a href="{{ route('boutique.orders.assign.show', $order) }}" class="btn btn-sm btn-primary">Assigner un livreur</a>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($order->livreur)
                        @php $lv = $order->livreur; @endphp
                        <div class="livreur-row" style="margin-bottom:16px">
                            <div class="livreur-av">{{ $init($lv->name) }}</div>
                            <div>
                                <div class="livreur-name">{{ $lv->name }}</div>
                                <div class="livreur-meta">
                                    <span class="avail-dot {{ $lv->is_available ? 'avail-yes' : 'avail-no' }}"></span>
                                    {{ $lv->is_available ? 'Disponible' : 'Indisponible' }}
                                    @if($lv->phone) &nbsp;·&nbsp; 📞 {{ $lv->phone }} @endif
                                </div>
                            </div>
                        </div>
                        <div class="info-list">
                            @if($order->delivery_destination)
                            <div class="info-row">
                                <span class="lbl">📍 Destination</span>
                                <span class="val">{{ $order->delivery_destination }}</span>
                            </div>
                            @endif
                            @if($order->delivery_fee)
                            <div class="info-row">
                                <span class="lbl">💰 Frais de livraison</span>
                                <span class="val mono">{{ number_format($order->delivery_fee, 0, ',', ' ') }} {{ $devise }}</span>
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="empty-block">
                            <span class="ico">🚴</span>
                            <p>
                                @if($order->status === 'confirmée')
                                    Aucun livreur assigné. <a href="{{ route('boutique.orders.assign.show', $order) }}" style="color:var(--brand);font-weight:600">Assigner maintenant →</a>
                                @elseif($order->status === 'en_attente' || $order->status === 'pending')
                                    Confirmez la commande avant d'assigner un livreur.
                                @else
                                    Aucun livreur assigné.
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Commission --}}
                @if($order->commission)
                @php $comm = $order->commission; @endphp
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">💸 Commission livreur</span>
                        <a href="{{ route('boutique.commissions.index') }}" class="btn btn-sm">Voir toutes →</a>
                    </div>
                    <div class="card-body">
                        <div class="comm-status">
                            <div>
                                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:4px">Montant commission</div>
                                <div class="comm-amount-big {{ $comm->status === 'payée' ? 'comm-paid-amount' : '' }}">
                                    {{ number_format($comm->amount, 0, ',', ' ') }}
                                    <span style="font-size:14px;font-family:var(--font);font-weight:600">{{ $devise }}</span>
                                </div>
                            </div>
                            @if($comm->status === 'payée')
                                <span class="status-badge s-livree">✅ Payée</span>
                            @else
                                <span class="status-badge s-attente">⏳ En attente</span>
                            @endif
                        </div>
                        @if($comm->payout_ref || $comm->paid_at)
                        <div class="info-list" style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">
                            @if($comm->payout_ref)
                            <div class="info-row">
                                <span class="lbl">📎 Référence paiement</span>
                                <span class="val mono">{{ $comm->payout_ref }}</span>
                            </div>
                            @endif
                            @if($comm->paid_at)
                            <div class="info-row">
                                <span class="lbl">📅 Payée le</span>
                                <span class="val">{{ $comm->paid_at->format('d/m/Y à H:i') }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- ── Colonne droite ── --}}
            <div>
                {{-- Client --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">👤 Client</span>
                    </div>
                    <div class="card-body">
                        @if($order->client)
                        @php $cl = $order->client; @endphp
                        <div class="client-row" style="margin-bottom:16px">
                            <div class="client-av">{{ $init($cl->name) }}</div>
                            <div>
                                <div class="client-name">{{ $cl->name }}</div>
                                <div class="client-meta">
                                    @if($cl->email)<a href="mailto:{{ $cl->email }}">{{ $cl->email }}</a>@endif
                                </div>
                            </div>
                        </div>
                        <div class="info-list">
                            @if($cl->phone)
                            <div class="info-row">
                                <span class="lbl">📞 Téléphone</span>
                                <span class="val">{{ $cl->phone }}</span>
                            </div>
                            @endif
                            <div class="info-row">
                                <span class="lbl">📦 Total commandes</span>
                                <span class="val mono">{{ $cl->orders()->count() }}</span>
                            </div>
                        </div>
                        @else
                        <div class="empty-block">
                            <span class="ico">👤</span>
                            <p>Client introuvable.</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Infos commande --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">📋 Détails</span>
                    </div>
                    <div class="card-body">
                        <div class="info-list">
                            <div class="info-row">
                                <span class="lbl">N° commande</span>
                                <span class="val mono">#{{ $order->id }}</span>
                            </div>
                            <div class="info-row">
                                <span class="lbl">Statut</span>
                                <span class="val"><span class="status-badge {{ $sInfo['class'] }}" style="font-size:10.5px">{{ $sInfo['ico'] }} {{ $sInfo['label'] }}</span></span>
                            </div>
                            <div class="info-row">
                                <span class="lbl">Date commande</span>
                                <span class="val">{{ $order->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="lbl">Heure</span>
                                <span class="val mono">{{ $order->created_at->format('H:i') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="lbl">Devise</span>
                                <span class="val mono">{{ $devise }}</span>
                            </div>
                            @if($order->delivery_destination)
                            <div class="info-row">
                                <span class="lbl">📍 Adresse livraison</span>
                                <span class="val" style="max-width:180px;word-break:break-word;text-align:right">{{ $order->delivery_destination }}</span>
                            </div>
                            @endif
                            @if($order->ordonnance)
                            <div class="info-row">
                                <span class="lbl">📄 Ordonnance</span>
                                <a href="{{ asset('storage/' . $order->ordonnance) }}" target="_blank" class="val" style="color:var(--brand)">Voir le fichier →</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">⏱ Historique</span>
                    </div>
                    <div class="card-body" style="padding:14px 18px">
                        @php
                            $steps = [
                                ['en_attente',   '⏳', 'Commande reçue',     'Commande passée par le client'],
                                ['confirmée',    '✅', 'Confirmée',           'Commande validée par la boutique'],
                                ['en_livraison', '🚴', 'En cours de livraison','Livreur en route'],
                                ['livrée',       '📦', 'Livrée',             'Commande remise au client'],
                            ];
                            $statuses  = array_column($steps, 0);
                            $currentIdx = array_search($order->status, $statuses);
                            $isCancelled = $order->status === 'annulée';
                        @endphp
                        <div class="timeline">
                            @if($isCancelled)
                            <div class="tl-item">
                                <div class="tl-dot cancelled">✕</div>
                                <div class="tl-content">
                                    <div class="tl-label tl-canc">Commande annulée</div>
                                    <div class="tl-date">{{ $order->updated_at->format('d/m/Y à H:i') }}</div>
                                </div>
                            </div>
                            @else
                            @foreach($steps as $i => [$st, $ico, $label, $desc])
                            @php
                                $isDone   = $currentIdx !== false && $i < $currentIdx;
                                $isActive = $currentIdx !== false && $i === $currentIdx;
                                $isPend   = $currentIdx !== false && $i > $currentIdx;
                            @endphp
                            <div class="tl-item">
                                <div class="tl-dot {{ $isDone ? 'done' : ($isActive ? 'active' : 'pending') }}">
                                    {{ $isDone ? '✓' : ($isActive ? $ico : '○') }}
                                </div>
                                <div class="tl-content">
                                    <div class="tl-label {{ $isDone ? 'tl-done' : ($isActive ? '' : 'tl-pend') }}">{{ $label }}</div>
                                    <div class="tl-date {{ $isPend ? '' : '' }}">{{ $desc }}</div>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Paiement --}}
                @if($order->payment)
                @php $pay = $order->payment; @endphp
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">💳 Paiement</span>
                    </div>
                    <div class="card-body">
                        <div class="info-list">
                            <div class="info-row">
                                <span class="lbl">Méthode</span>
                                <span class="val">{{ $pay->method ?? '—' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="lbl">Statut</span>
                                <span class="val">{{ $pay->status ?? '—' }}</span>
                            </div>
                            @if($pay->amount)
                            <div class="info-row">
                                <span class="lbl">Montant payé</span>
                                <span class="val mono">{{ number_format($pay->amount, 0, ',', ' ') }} {{ $devise }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>{{-- /grid-13 --}}

    </div>{{-- /page-wrap --}}
</main>
</div>{{-- /dash-wrap --}}

@endsection

@push('scripts')
<script>
function toggleGroup(btn) {
    const sub = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');
    document.querySelectorAll('.sb-sub.open').forEach(s => {
        s.classList.remove('open');
        s.previousElementSibling?.classList.remove('open');
    });
    if (!isOpen) { sub.classList.add('open'); btn.classList.add('open'); }
}

(function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sbOverlay');
    document.getElementById('btnMenu')?.addEventListener('click', () => {
        sidebar.classList.add('open'); overlay.classList.add('open');
    });
    overlay?.addEventListener('click', () => {
        sidebar.classList.remove('open'); overlay.classList.remove('open');
    });
    document.getElementById('btnCloseSidebar')?.addEventListener('click', () => {
        sidebar.classList.remove('open'); overlay.classList.remove('open');
    });
})();
</script>
@endpush
