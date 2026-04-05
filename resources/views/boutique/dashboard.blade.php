{{--
    resources/views/boutique/dashboard.blade.php
    Route : GET /boutique/dashboard  → ShopController@admin  → name('boutique.dashboard')
    Variables injectées :
      $shop               → App\Models\Shop
      $livreursDisponibles → Collection<User>  (role=livreur, is_available=true, shop_id)
      $deliveryCompanies  → Collection<DeliveryCompany>
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
    --brand:      #10b981;
    --brand-dk:   #059669;
    --brand-lt:   #d1fae5;
    --brand-mlt:  #ecfdf5;
    --sb-bg:      #0d1f18;
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

/* ════════════════════════════════════════════
   LAYOUT
════════════════════════════════════════════ */
.dash-wrap {
    display: flex;
    min-height: 100vh;
}
.dash-wrap .sidebar {
    flex-shrink: 0;
}
.dash-wrap .main {
    margin-left: var(--sb-w);
    flex: 1;
    min-width: 0;
}

/* ════════════════════════════════════════════
   SIDEBAR
════════════════════════════════════════════ */
.sidebar {
    background: var(--sb-bg);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: var(--sb-w);
    overflow-y: auto;
    scrollbar-width: none;
    z-index: 40;
    border-right: 1px solid rgba(0,0,0,.2);
}
.sidebar::-webkit-scrollbar { display: none; }

.sb-brand {
    padding: 18px 16px 14px;
    border-bottom: 1px solid var(--sb-border);
    flex-shrink: 0;
}
.sb-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: #fff;
}
.sb-logo-icon {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--brand), #059669);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(16,185,129,.35);
}
.sb-shop-name {
    font-size: 14px; font-weight: 600;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    max-width: 148px;
}
.sb-status {
    display: flex; align-items: center; gap: 6px;
    margin-top: 9px; font-size: 10.5px; color: var(--sb-txt);
    font-weight: 500;
}
.pulse {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--brand); flex-shrink: 0;
    animation: blink 2.2s ease-in-out infinite;
    box-shadow: 0 0 5px var(--brand);
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }

.sb-nav {
    padding: 10px 10px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1px;
    overflow-y: auto;
    scrollbar-width: none;
}
.sb-nav::-webkit-scrollbar { display: none; }

.sb-section {
    font-size: 9.5px; text-transform: uppercase;
    letter-spacing: 1.2px; color: rgba(255,255,255,.2);
    padding: 12px 8px 4px; font-weight: 600;
}
.sb-item {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 10px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 500;
    color: var(--sb-txt); text-decoration: none;
    transition: background .15s, color .15s;
    position: relative;
}
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.8); }
.sb-item.active {
    background: var(--sb-act);
    color: var(--sb-txt-act);
}
.sb-item.active::before {
    content: '';
    position: absolute; left: 0; top: 50%;
    transform: translateY(-50%);
    width: 3px; height: 18px;
    background: var(--brand);
    border-radius: 0 3px 3px 0;
}
.sb-item .ico {
    font-size: 14px; width: 20px;
    text-align: center; flex-shrink: 0;
}
.sb-badge {
    margin-left: auto;
    background: var(--brand); color: #fff;
    font-size: 10px; font-weight: 700;
    border-radius: 20px; padding: 1px 7px;
    font-family: var(--mono);
    min-width: 20px; text-align: center;
}
.sb-badge.warn { background: #f59e0b; }

.sb-footer {
    padding: 12px 10px;
    border-top: 1px solid var(--sb-border);
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.sb-user {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; border-radius: var(--r-sm);
    text-decoration: none; transition: background .15s;
}
.sb-user:hover { background: var(--sb-hov); }
.sb-av {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, var(--brand), #16a34a);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0;
    box-shadow: 0 0 0 2px rgba(16,185,129,.25);
}
.sb-uname { font-size: 12px; font-weight: 600; color: rgba(255,255,255,.85); }
.sb-urole { font-size: 10px; color: var(--sb-txt); margin-top: 1px; }
.sb-logout {
    display: flex; align-items: center; gap: 8px;
    width: 100%; padding: 8px 10px;
    border-radius: var(--r-sm);
    background: rgba(220,38,38,.08);
    border: 1px solid rgba(220,38,38,.15);
    color: rgba(252,165,165,.85);
    font-size: 12px; font-weight: 600; font-family: var(--font);
    cursor: pointer; text-decoration: none;
    transition: background .15s, color .15s, border-color .15s;
    text-align: left;
}
.sb-logout:hover {
    background: rgba(220,38,38,.18);
    border-color: rgba(220,38,38,.35);
    color: #fca5a5;
}
.sb-logout .ico { font-size: 13px; flex-shrink: 0; }

/* ════════════════════════════════════════════
   MAIN CONTENT
════════════════════════════════════════════ */
.main {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

/* ── Topbar ── */
.topbar {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 24px;
    height: var(--top-h);
    display: flex; align-items: center; gap: 12px;
    position: sticky; top: 0; z-index: 30;
    box-shadow: var(--shadow-sm);
}
.tb-info { flex: 1; min-width: 0; }
.tb-title { font-size: 14px; font-weight: 600; color: var(--text); }
.tb-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }
.tb-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

/* ── Buttons ── */
.btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: var(--r-sm);
    font-size: 12px; font-weight: 600; font-family: var(--font);
    border: 1px solid var(--border-dk); background: var(--surface); color: var(--text-2);
    cursor: pointer; text-decoration: none; transition: all .15s;
    white-space: nowrap;
}
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); }
.btn-primary:hover { background: var(--brand-dk); border-color: var(--brand-dk); color: #fff; }
.btn-sm { padding: 5px 10px; font-size: 11px; }
.btn-ghost {
    background: transparent; border-color: transparent;
    color: var(--brand); padding: 5px 8px;
}
.btn-ghost:hover { background: var(--brand-mlt); border-color: var(--brand-lt); }

/* hamburger */
.btn-hamburger {
    display: none;
    background: none; border: none;
    cursor: pointer; padding: 6px;
    color: var(--text); font-size: 20px;
}

/* ── Page body ── */
.content { padding: 22px 24px; flex: 1; }

/* ── Flash ── */
.flash {
    margin: 12px 24px 0;
    padding: 10px 14px;
    font-size: 12.5px; font-weight: 500;
    border-radius: var(--r-sm);
    border: 1px solid;
    display: flex; align-items: center; gap: 8px;
}
.flash-success { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; }
.flash-info    { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
.flash-warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
.flash-danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }

/* ── Banner ── */
.comm-banner {
    background: var(--brand-mlt);
    border: 1px solid var(--brand-lt);
    border-radius: var(--r-sm);
    padding: 10px 14px;
    display: flex; align-items: center; gap: 10px;
    font-size: 12.5px; color: #065f46;
    margin-bottom: 20px;
    font-weight: 500;
}

/* ════════════════════════════════════════════
   KPI GRID
════════════════════════════════════════════ */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}
.kpi {
    background: var(--surface);
    border: 1px solid var(--border);
    border-top: 3px solid var(--kpi-color, var(--brand));
    border-radius: var(--r);
    padding: 16px 18px 14px;
    position: relative; overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .2s, border-color .2s;
}
.kpi:hover { box-shadow: var(--shadow); }
.kpi-icon {
    width: 36px; height: 36px;
    background: var(--kpi-bg, var(--brand-mlt));
    border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; margin-bottom: 10px;
}
.kpi-lbl {
    font-size: 11px; color: var(--muted); font-weight: 600;
    letter-spacing: .3px; margin-bottom: 4px;
    text-transform: uppercase;
}
.kpi-val {
    font-size: 24px; font-weight: 700;
    color: var(--text); letter-spacing: -.8px;
    font-family: var(--mono);
    line-height: 1;
}
.kpi-unit { font-size: 10px; color: var(--muted); margin-top: 4px; }
.kpi-delta {
    font-size: 11px; font-weight: 600;
    margin-top: 6px; display: flex; align-items: center; gap: 3px;
}
.up   { color: #059669; }
.down { color: #dc2626; }

/* ════════════════════════════════════════════
   CARDS
════════════════════════════════════════════ */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.card-hd {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    gap: 10px;
}
.card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.card-bd { padding: 16px 18px; }

/* ════════════════════════════════════════════
   CHART
════════════════════════════════════════════ */
.chart-wrap { margin-bottom: 22px; }
.chart-inner { padding: 16px 18px; }
.chart-bars {
    display: flex; align-items: flex-end; gap: 6px;
    height: 80px; padding: 0 2px; margin-bottom: 8px;
}
.bar-wrap { flex: 1; height: 100%; display: flex; align-items: flex-end; }
.bar {
    width: 100%; border-radius: 4px 4px 0 0;
    background: var(--brand); opacity: .9;
    transition: opacity .15s, height .5s cubic-bezier(.23,1,.32,1);
    cursor: pointer;
}
.bar:hover { opacity: 1; }
.bar.dim { opacity: .22; }
.bar-labels { display: flex; gap: 6px; padding: 0 2px; }
.bar-lbl { flex: 1; text-align: center; font-size: 10px; color: var(--muted); font-family: var(--mono); }

/* ════════════════════════════════════════════
   CONTENT GRID
════════════════════════════════════════════ */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 18px;
    margin-bottom: 22px;
}
.right-col { display: flex; flex-direction: column; gap: 16px; }

/* ════════════════════════════════════════════
   TABLE (recent orders)
════════════════════════════════════════════ */
.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl th {
    text-align: left; font-size: 10px; font-weight: 700;
    color: var(--muted); text-transform: uppercase; letter-spacing: .6px;
    padding: 0 10px 10px 0; border-bottom: 1px solid var(--border);
}
.tbl td {
    padding: 10px 10px 10px 0;
    border-bottom: 1px solid #f3f6f4;
    vertical-align: middle;
}
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: var(--bg); }
.oid  { font-family: var(--mono); font-size: 11px; color: var(--muted); }
.onam { font-weight: 600; color: var(--text); margin-top: 1px; }
.oamt { font-family: var(--mono); font-weight: 600; color: var(--text); white-space: nowrap; text-align: right; }

/* pills */
.pill {
    display: inline-block; font-size: 10.5px; font-weight: 600;
    padding: 3px 9px; border-radius: 20px; white-space: nowrap;
}
.p-success { background: #d1fae5; color: #065f46; }
.p-warning { background: #fef3c7; color: #92400e; }
.p-info    { background: #dbeafe; color: #1e40af; }
.p-danger  { background: #fee2e2; color: #991b1b; }
.p-muted   { background: #f3f6f4; color: #6b7280; }

/* ════════════════════════════════════════════
   LIVRAISON SECTION
   Logique :
   - Si boutique a des livreurs → onglet "Livreurs" + onglet "Entreprises"
   - Si pas de livreurs → message invite + liste des entreprises
════════════════════════════════════════════ */
.delivery-card {}

/* Tabs */
.tab-bar {
    display: flex; border-bottom: 1px solid var(--border);
    padding: 0 18px; gap: 0;
}
.tab-btn {
    padding: 11px 14px; font-size: 12px; font-weight: 600;
    color: var(--muted); cursor: pointer; border: none;
    background: none; font-family: var(--font);
    border-bottom: 2.5px solid transparent;
    transition: color .15s, border-color .15s;
    display: flex; align-items: center; gap: 6px;
    white-space: nowrap;
    margin-bottom: -1px;
}
.tab-btn:hover { color: var(--text); }
.tab-btn.active { color: var(--brand); border-bottom-color: var(--brand); }
.tab-count {
    background: var(--brand-lt); color: var(--brand);
    font-size: 10px; font-weight: 700;
    padding: 1px 6px; border-radius: 10px;
}
.tab-count.zero { background: #f3f6f4; color: var(--muted); }

.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* No livreur notice */
.no-livreur-notice {
    display: flex; flex-direction: column; align-items: center;
    gap: 6px; padding: 20px 18px;
    text-align: center;
}
.no-livreur-notice .notice-icon { font-size: 28px; }
.no-livreur-notice .notice-title { font-size: 13px; font-weight: 700; color: var(--text); }
.no-livreur-notice .notice-sub   { font-size: 12px; color: var(--muted); line-height: 1.5; }

/* Livreur list */
.lv-list { display: flex; flex-direction: column; }
.lv-row {
    display: flex; align-items: center; gap: 11px;
    padding: 11px 18px;
    border-bottom: 1px solid #f3f6f4;
    transition: background .12s;
}
.lv-row:last-child { border-bottom: none; }
.lv-row:hover { background: var(--bg); }
.lv-av {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.lv-info { flex: 1; min-width: 0; }
.lv-nm { font-size: 12.5px; font-weight: 600; color: var(--text); }
.lv-mt { font-size: 11px; color: var(--muted); margin-top: 1px; }
.status-dot {
    width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0;
}
.status-dot.available { background: #10b981; box-shadow: 0 0 6px rgba(16,185,129,.5); }
.status-dot.busy      { background: #f59e0b; box-shadow: 0 0 6px rgba(245,158,11,.5); }

/* Company list */
.co-list { display: flex; flex-direction: column; }
.co-row {
    display: flex; align-items: center; gap: 11px;
    padding: 11px 18px;
    border-bottom: 1px solid #f3f6f4;
    transition: background .12s; cursor: pointer;
}
.co-row:last-child { border-bottom: none; }
.co-row:hover { background: var(--bg); }
.co-logo {
    width: 38px; height: 38px; border-radius: var(--r-sm);
    background: var(--bg); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0; overflow: hidden;
}
.co-logo img { width: 28px; height: 28px; object-fit: contain; }
.co-info { flex: 1; min-width: 0; }
.co-nm { font-size: 12.5px; font-weight: 600; color: var(--text); }
.co-mt { font-size: 11px; color: var(--muted); margin-top: 1px; }
.co-commission {
    font-size: 10px; font-weight: 700;
    background: var(--brand-mlt); color: var(--brand-dk);
    padding: 2px 7px; border-radius: 12px;
    white-space: nowrap;
}

/* ════════════════════════════════════════════
   TOP PRODUCTS (sparklines)
════════════════════════════════════════════ */
.sp-row {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 12px;
}
.sp-row:last-child { margin-bottom: 0; }
.sp-lbl {
    font-size: 12px; font-weight: 500; color: var(--text-2);
    width: 110px; flex-shrink: 0;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sp-track {
    flex: 1; height: 7px; background: #eef1f0;
    border-radius: 4px; overflow: hidden;
}
.sp-fill {
    height: 100%; border-radius: 4px; background: var(--brand);
    transition: width 1.1s cubic-bezier(.23,1,.32,1);
}
.sp-val {
    font-family: var(--mono); font-size: 11.5px;
    font-weight: 600; color: var(--text);
    width: 30px; text-align: right; flex-shrink: 0;
}

/* Avatar colors */
.av-green  { background: #059669; }
.av-blue   { background: #2563eb; }
.av-amber  { background: #d97706; }
.av-purple { background: #7c3aed; }
.av-teal   { background: #0891b2; }
.av-rose   { background: #e11d48; }

/* ════════════════════════════════════════════
   SIDEBAR OVERLAY (mobile)
════════════════════════════════════════════ */
.sb-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 39;
}

/* ════════════════════════════════════════════
   RESPONSIVE
════════════════════════════════════════════ */
@media (max-width: 1100px) {
    .content-grid { grid-template-columns: 1fr 340px; }
}

@media (max-width: 900px) {
    :root { --sb-w: 230px; }

    .dash-wrap .main { margin-left: 0; }

    .sidebar {
        transform: translateX(-100%);
        transition: transform .25s cubic-bezier(.23,1,.32,1);
    }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }

    .btn-hamburger { display: flex; }

    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .content-grid { grid-template-columns: 1fr; }
    .right-col { gap: 14px; }
}

@media (max-width: 520px) {
    .content { padding: 16px; }
    .topbar  { padding: 0 16px; }
    .kpi-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .kpi-val  { font-size: 20px; }
    .tb-actions .btn:not(.btn-primary) { display: none; }
    .flash { margin: 10px 16px 0; }
    .comm-banner { margin-bottom: 16px; }
}
</style>
@endpush

@php
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X', 0, 1));
    $now      = \Illuminate\Support\Carbon::now();

    /* CA mensuel */
    $caMonth  = (float) $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->sum('total');
    $caPrev   = (float) $shop->orders()->whereMonth('created_at',$now->copy()->subMonth()->month)->whereYear('created_at',$now->copy()->subMonth()->year)->sum('total') ?: 1;
    $caDelta  = round((($caMonth - $caPrev) / $caPrev) * 100, 1);

    /* Commandes */
    $cmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->count();
    $cmdToday = $shop->orders()->whereDate('created_at', today())->count();
    $cmdYest  = $shop->orders()->whereDate('created_at', today()->subDay())->count();

    /* Panier moyen */
    $panier   = $cmdMonth > 0 ? round($caMonth / $cmdMonth) : 0;

    /* Taux livraison */
    $totalCmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->count();
   $livres = $shop->orders()->whereMonth('created_at',$now->month)->where('status','livrée')->count();
    $tauxLiv       = $totalCmdMonth > 0 ? round(($livres / $totalCmdMonth) * 100, 1) : 0;

    /* Graph 7 jours */
    $days7 = collect(range(6,0))->map(fn($i) => [
        'label' => $now->copy()->subDays($i)->isoFormat('dd'),
        'value' => (float) $shop->orders()->whereDate('created_at', $now->copy()->subDays($i)->toDateString())->sum('total'),
        'today' => $i === 0,
    ]);
    $max7 = $days7->max('value') ?: 1;

    /* Commandes récentes */
    $recentOrders = $shop->orders()->with('user')->latest()->take(6)->get();

    /* Top produits */
    $topProducts = $shop->products()->withCount('orderItems')->orderByDesc('order_items_count')->take(5)->get();
    $maxSales    = $topProducts->max('order_items_count') ?: 1;

    /* Statuts */
    $statusMap = [
        'delivered'   => ['label'=>'Livré',       'cls'=>'p-success'],
        'pending'     => ['label'=>'En attente',   'cls'=>'p-warning'],
        'processing'  => ['label'=>'En traitement','cls'=>'p-info'],
        'confirmed'   => ['label'=>'Confirmé',     'cls'=>'p-info'],
        'livrée'      => ['label'=>'Livré', 'cls'=>'p-success'],
        'shipped'     => ['label'=>'Expédié',      'cls'=>'p-info'],
        'cancelled'   => ['label'=>'Annulé',       'cls'=>'p-danger'],
    ];

    /* Pending badge */
    $pendingCount = $shop->orders()->whereIn('status',['pending','processing'])->count();

    /* Avatar colors */
    $avColors = ['av-green','av-blue','av-amber','av-purple','av-teal','av-rose'];

    /* Livraison logic */
    $hasLivreurs  = $livreursDisponibles->isNotEmpty();
    $hasCompanies = isset($deliveryCompanies) && $deliveryCompanies->isNotEmpty();
@endphp

<div class="dash-wrap" id="dashWrap">

    {{-- ══════ SIDEBAR ══════ --}}
    <aside class="sidebar" id="sidebar">
        <div class="sb-brand">
            <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
                <div class="sb-logo-icon">🛍️</div>
                <span class="sb-shop-name">{{ $shop->name }}</span>
            </a>
            <div class="sb-status">
                <span class="pulse"></span>
                {{ $shop->is_approved ? 'Boutique active' : 'En attente de validation' }}
                &nbsp;·&nbsp;
                {{ ucfirst(auth()->user()->role_in_shop ?? auth()->user()->role) }}
            </div>
        </div>

        <nav class="sb-nav">

            <div class="sb-section">Principal</div>

            <a href="{{ route('boutique.dashboard') }}" class="sb-item active">
                <span class="ico">⊞</span> Tableau de bord
            </a>
            <a href="{{ route('boutique.orders.index') }}" class="sb-item">
                <span class="ico">📦</span> Commandes
                @if($pendingCount > 0)
                    <span class="sb-badge">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('products.index') }}" class="sb-item">
                <span class="ico">🏷️</span> Produits
            </a>
            <a href="{{ route('boutique.employees.index') }}" class="sb-item">
                <span class="ico">👥</span> Équipe
            </a>

            <div class="sb-section">Livraison</div>

            {{-- Livreurs = employés de la boutique avec rôle livreur --}}
            <a href="{{ route('boutique.employees.index') }}" class="sb-item">
                <span class="ico">🚴</span> Livreurs
                @if($livreursDisponibles->count() > 0)
                    <span class="sb-badge">{{ $livreursDisponibles->count() }}</span>
                @endif
            </a>

            {{-- Entreprises = sociétés de livraison externes à contacter --}}
            <a href="{{ route('delivery.companies.index') }}" class="sb-item">
                <span class="ico">🏢</span> Entreprises partenaires
            </a>

            <div class="sb-section">Gestion</div>

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

            <div class="sb-section">Support</div>

            <a href="{{ route('support.index') }}" class="sb-item">
                <span class="ico">🎧</span> Support
            </a>

        </nav>

        <div class="sb-footer">
            <a href="{{ route('profile.edit') }}" class="sb-user">
                <div class="sb-av">{{ $initials }}</div>
                <div style="flex:1;min-width:0">
                    <div class="sb-uname">{{ Str::limit(auth()->user()->name, 20) }}</div>
                    <div class="sb-urole">
                        {{ auth()->user()->role === 'admin' ? 'Administrateur' : ucfirst(auth()->user()->role) }}
                    </div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sb-logout">
                    <span class="ico">⎋</span>
                    Se déconnecter
                </button>
            </form>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div class="sb-overlay" id="sbOverlay"></div>

    {{-- ══════ MAIN ══════ --}}
    <main class="main">

        {{-- Topbar --}}
        <div class="topbar">
            <button class="btn-hamburger" id="btnMenu" aria-label="Menu">☰</button>
            <div class="tb-info">
                <div class="tb-title">{{ $shop->name }}</div>
                <div class="tb-sub">{{ now()->translatedFormat('l j F Y') }}</div>
            </div>
            <div class="tb-actions">
                <a href="{{ route('boutique.export.orders.excel') }}" class="btn btn-sm">⬇ Excel</a>
                <a href="{{ route('boutique.export.orders.pdf') }}" class="btn btn-sm">⬇ PDF</a>
                <a href="{{ route('boutique.orders.index') }}" class="btn btn-primary btn-sm">+ Commande</a>
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

        {{-- Content --}}
        <div class="content">

            {{-- Commission banner --}}
            @if($shop->commission_rate)
            <div class="comm-banner">
                💡 <span>Taux de commission : <strong>{{ $shop->commission_rate_percent }}%</strong> — appliqué à chaque commande validée.</span>
            </div>
            @endif

            {{-- ── KPI Grid ── --}}
            <div class="kpi-grid">

                <div class="kpi" style="--kpi-color:#10b981;--kpi-bg:#ecfdf5">
                    <div class="kpi-icon">💰</div>
                    <div class="kpi-lbl">Chiffre d'affaires</div>
                    <div class="kpi-val">{{ number_format($caMonth,0,',',' ') }}</div>
                    <div class="kpi-unit">GNF · {{ $now->translatedFormat('F Y') }}</div>
                    <div class="kpi-delta {{ $caDelta >= 0 ? 'up':'down' }}">
                        {{ $caDelta >= 0 ? '↑':'↓' }} {{ abs($caDelta) }}% vs mois précédent
                    </div>
                </div>

                <div class="kpi" style="--kpi-color:#3b82f6;--kpi-bg:#eff6ff">
                    <div class="kpi-icon">📦</div>
                    <div class="kpi-lbl">Commandes ce mois</div>
                    <div class="kpi-val">{{ $cmdMonth }}</div>
                    <div class="kpi-unit">commandes</div>
                    <div class="kpi-delta {{ $cmdToday >= $cmdYest ? 'up':'down' }}">
                        {{ $cmdToday >= $cmdYest ? '↑':'↓' }} {{ $cmdToday }} aujourd'hui
                    </div>
                </div>

                <div class="kpi" style="--kpi-color:#f59e0b;--kpi-bg:#fffbeb">
                    <div class="kpi-icon">🛒</div>
                    <div class="kpi-lbl">Panier moyen</div>
                    <div class="kpi-val">{{ number_format($panier,0,',',' ') }}</div>
                    <div class="kpi-unit">GNF / commande</div>
                    <div class="kpi-delta {{ $caDelta >= 0 ? 'up':'down' }}">
                        {{ $caDelta >= 0 ? '↑':'↓' }} {{ abs($caDelta) }}%
                    </div>
                </div>

                <div class="kpi" style="--kpi-color:#8b5cf6;--kpi-bg:#f5f3ff">
                    <div class="kpi-icon">🚴</div>
                    <div class="kpi-lbl">Taux de livraison</div>
                    <div class="kpi-val">{{ $tauxLiv }}%</div>
                    <div class="kpi-unit">{{ $livres }} / {{ $totalCmdMonth }} livrées</div>
                    <div class="kpi-delta {{ $tauxLiv >= 90 ? 'up':'down' }}">
                        {{ $tauxLiv >= 90 ? '✓ Excellent':'⚠ À améliorer' }}
                    </div>
                </div>

            </div>

            {{-- ── Chart 7 jours ── --}}
            <div class="card chart-wrap">
                <div class="card-hd">
                    <span class="card-title">Revenus — 7 derniers jours</span>
                    <span style="font-size:11px;color:var(--muted);font-weight:500">GNF</span>
                </div>
                <div class="chart-inner">
                    <div class="chart-bars" id="chartBars">
                        @foreach($days7 as $day)
                        @php $pct = $day['value'] > 0 ? max(round(($day['value']/$max7)*100), 5) : 0; @endphp
                        <div class="bar-wrap">
                            <div class="bar {{ $day['today'] ? '' : 'dim' }}"
                                 data-h="{{ $pct }}"
                                 style="height:0%"
                                 title="{{ $day['label'] }} : {{ number_format($day['value'],0,',',' ') }} GNF">
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="bar-labels">
                        @foreach($days7 as $day)
                            <div class="bar-lbl">{{ $day['label'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── Content grid ── --}}
            <div class="content-grid">

                {{-- Commandes récentes --}}
                <div class="card">
                    <div class="card-hd">
                        <span class="card-title">Commandes récentes</span>
                        <a href="{{ route('boutique.orders.index') }}" class="btn-ghost btn btn-sm">
                            Voir tout →
                        </a>
                    </div>
                    <div style="padding:0 18px">
                        @if($recentOrders->isEmpty())
                            <div style="padding:28px 0;text-align:center;font-size:13px;color:var(--muted)">
                                Aucune commande pour le moment.
                            </div>
                        @else
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Réf / Client</th>
                                    <th>Statut</th>
                                    <th style="text-align:right">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                @php $st = $statusMap[$order->status] ?? ['label'=>ucfirst($order->status),'cls'=>'p-muted']; @endphp
                                <tr>
                                    <td>
                                        <div class="oid">#{{ $order->id }}</div>
                                        <div class="onam">{{ $order->user->name ?? 'Client inconnu' }}</div>
                                    </td>
                                    <td><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span></td>
                                    <td class="oamt">
                                        {{ number_format($order->total,0,',',' ') }}
                                        <span style="font-size:9px;color:var(--muted)"> GNF</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>

                {{-- ── Colonne droite ── --}}
                <div class="right-col">

                    {{-- ══ BLOC LIVRAISON (logique claire) ══
                         Cas A : La boutique a des livreurs propres → 2 onglets
                         Cas B : Pas de livreurs → invitation + entreprises
                    ══ --}}

                    @if($hasLivreurs)
                    {{-- CAS A : La boutique a ses propres livreurs --}}
                    <div class="card delivery-card">
                        <div class="card-hd" style="padding-bottom:0;border-bottom:none">
                            <span class="card-title">Livraison</span>
                        </div>
                        <div class="tab-bar">
                            <button class="tab-btn active" data-tab="livreurs">
                                🚴 Livreurs
                                <span class="tab-count">{{ $livreursDisponibles->count() }}</span>
                            </button>
                            @if($hasCompanies)
                            <button class="tab-btn" data-tab="companies">
                                🏢 Entreprises
                                <span class="tab-count {{ $deliveryCompanies->count() === 0 ? 'zero':'' }}">
                                    {{ $deliveryCompanies->count() }}
                                </span>
                            </button>
                            @endif
                        </div>

                        {{-- Onglet Livreurs --}}
                        <div class="tab-panel active" id="tab-livreurs">
                            <div class="lv-list">
                                @foreach($livreursDisponibles->take(5) as $i => $livreur)
                                @php
                                    $lp    = explode(' ', $livreur->name);
                                    $linit = strtoupper(substr($lp[0],0,1)) . strtoupper(substr($lp[1]??'X',0,1));
                                    $lcol  = $avColors[$i % count($avColors)];
                                    $busy  = !empty($livreur->current_order_id);
                                @endphp
                                <a href="{{ route('delivery.companies.index') }}"
                                   class="lv-row" style="text-decoration:none">
                                    <div class="lv-av {{ $lcol }}">{{ $linit }}</div>
                                    <div class="lv-info">
                                        <div class="lv-nm">{{ $livreur->name }}</div>
                                        <div class="lv-mt">
                                            {{ $livreur->phone ?? 'Aucun téléphone' }}
                                            @if($busy) &nbsp;·&nbsp; <span style="color:#f59e0b">En course</span>@endif
                                        </div>
                                    </div>
                                    <span class="status-dot {{ $busy ? 'busy':'available' }}"
                                          title="{{ $busy ? 'En course':'Disponible' }}"></span>
                                </a>
                                @endforeach
                                @if($livreursDisponibles->count() > 5)
                                <div style="padding:10px 18px;text-align:center">
                                    <a href="{{ route('delivery.companies.index') }}"
                                       class="btn btn-ghost btn-sm">
                                        + {{ $livreursDisponibles->count()-5 }} livreur(s) →
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Onglet Entreprises (si disponibles) --}}
                        @if($hasCompanies)
                        <div class="tab-panel" id="tab-companies">
                            @include('boutique._partials.delivery_companies_list', [
                                'companies' => $deliveryCompanies->take(4)
                            ])
                            @if($deliveryCompanies->count() > 4)
                            <div style="padding:10px 18px;text-align:center">
                                <a href="{{ route('delivery.companies.index') }}" class="btn btn-ghost btn-sm">
                                    Voir toutes →
                                </a>
                            </div>
                            @endif
                        </div>
                        @endif

                    </div>

                    @else
                    {{-- CAS B : Pas de livreurs propres → on pousse vers les entreprises --}}
                    <div class="card delivery-card">
                        <div class="card-hd">
                            <span class="card-title">Livraison</span>
                            <a href="{{ route('delivery.companies.index') }}" class="btn btn-ghost btn-sm">
                                Gérer →
                            </a>
                        </div>

                        @if(!$hasCompanies)
                        {{-- Ni livreurs ni entreprises --}}
                        <div class="no-livreur-notice">
                            <div class="notice-icon">🚚</div>
                            <div class="notice-title">Aucun livreur configuré</div>
                            <div class="notice-sub">
                                Votre boutique n'a pas encore de livreurs.<br>
                                Contactez une entreprise partenaire pour commencer.
                            </div>
                            <a href="{{ route('delivery.companies.index') }}" class="btn btn-primary" style="margin-top:8px">
                                Trouver une entreprise
                            </a>
                        </div>

                        @else
                        {{-- Pas de livreurs mais des entreprises disponibles --}}
                        <div style="padding:12px 18px 6px;background:#fffbeb;border-bottom:1px solid #fde68a;display:flex;align-items:flex-start;gap:9px">
                            <span style="font-size:16px;flex-shrink:0;margin-top:1px">⚠️</span>
                            <div style="font-size:12px;color:#92400e;font-weight:500;line-height:1.5">
                                Vous n'avez pas de livreurs. Sélectionnez une entreprise partenaire ci-dessous pour démarrer une discussion et organiser vos livraisons.
                            </div>
                        </div>
                        <div class="co-list">
                            @foreach($deliveryCompanies->take(4) as $company)
                            <div class="co-row"
                                 onclick="window.location='{{ route('company.chat.show', $company) }}'"
                                 title="Ouvrir la discussion">
                                <div class="co-logo">
                                    @if(!empty($company->logo))
                                        <img src="{{ asset('storage/'.$company->logo) }}" alt="{{ $company->name }}">
                                    @else
                                        🚚
                                    @endif
                                </div>
                                <div class="co-info">
                                    <div class="co-nm">{{ $company->name }}</div>
                                    <div class="co-mt">
                                        {{ $company->phone ?? 'Contact non renseigné' }}
                                    </div>
                                </div>
                                @if($company->commission_rate)
                                <span class="co-commission">{{ number_format($company->commission_rate*100,1) }}%</span>
                                @endif
                                <a href="{{ route('company.chat.show', $company) }}"
                                   class="btn btn-sm btn-primary"
                                   onclick="event.stopPropagation()">
                                    💬 Contacter
                                </a>
                            </div>
                            @endforeach
                        </div>
                        @if($deliveryCompanies->count() > 4)
                        <div style="padding:10px 18px;text-align:center">
                            <a href="{{ route('delivery.companies.index') }}" class="btn btn-ghost btn-sm">
                                Voir toutes les entreprises →
                            </a>
                        </div>
                        @endif

                        @endif
                    </div>
                    @endif

                </div>{{-- /right-col --}}
            </div>{{-- /content-grid --}}

            {{-- ── Top Produits ── --}}
            @if($topProducts->isNotEmpty())
            <div class="card">
                <div class="card-hd">
                    <span class="card-title">Top produits — ventes du mois</span>
                    <a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">
                        Tous les produits →
                    </a>
                </div>
                <div class="card-bd">
                    @foreach($topProducts as $product)
                    @php $pct = round(($product->order_items_count / $maxSales)*100); @endphp
                    <div class="sp-row">
                        <span class="sp-lbl" title="{{ $product->name }}">
                            {{ Str::limit($product->name, 18) }}
                        </span>
                        <div class="sp-track">
                            <div class="sp-fill" data-pct="{{ $pct }}" style="width:0%"></div>
                        </div>
                        <span class="sp-val">{{ $product->order_items_count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>{{-- /content --}}
    </main>
</div>{{-- /dash-wrap --}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Sidebar mobile toggle ── */
    const sidebar    = document.getElementById('sidebar');
    const overlay    = document.getElementById('sbOverlay');
    const btnMenu    = document.getElementById('btnMenu');

    function openSidebar()  { sidebar.classList.add('open');  overlay.classList.add('open'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }

    btnMenu?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);

    /* ── Tabs livraison ── */
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab;
            const card  = btn.closest('.delivery-card');
            card.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            card.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            const panel = document.getElementById('tab-' + tabId);
            if (panel) panel.classList.add('active');
        });
    });

    /* ── Bar chart animation ── */
    document.querySelectorAll('#chartBars .bar').forEach((bar, i) => {
        const h = bar.dataset.h + '%';
        setTimeout(() => {
            bar.style.transition = `height 0.55s cubic-bezier(.23,1,.32,1)`;
            bar.style.height = h;
        }, i * 60);
    });

    /* ── Sparkline animation ── */
    document.querySelectorAll('.sp-fill').forEach((el, i) => {
        setTimeout(() => {
            el.style.width = el.dataset.pct + '%';
        }, 100 + i * 90);
    });

});
</script>
@endpush