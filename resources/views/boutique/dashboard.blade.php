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
    top: 0; left: 0; bottom: 0;
    width: var(--sb-w);
    /* overflow-y: scroll sur la sidebar ENTIÈRE — scrollbar verte toujours visible,
       permet de scroller même quand le sous-menu Finance est ouvert */
    overflow-y: scroll;
    scrollbar-width: thin; /* Firefox */
    scrollbar-color: rgba(16,185,129,.4) rgba(255,255,255,.05);
    z-index: 40;
    border-right: 1px solid rgba(0,0,0,.2);
}
/* Chrome / Safari — scrollbar verte fine toujours visible */
.sidebar::-webkit-scrollbar       { width: 4px; }
.sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,.04); }
.sidebar::-webkit-scrollbar-thumb { background: rgba(16,185,129,.4); border-radius: 4px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(16,185,129,.7); }

.sb-brand {
    padding: 18px 16px 14px;
    border-bottom: 1px solid var(--sb-border);
    flex-shrink: 0;
    position: relative;
}

/* ── Bouton fermeture sidebar (mobile uniquement) ── */
.sb-close {
    display: none; /* masqué sur desktop */
    position: absolute;
    top: 14px; right: 12px;
    width: 30px; height: 30px;
    border-radius: 8px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.6);
    font-size: 18px; line-height: 1;
    cursor: pointer;
    align-items: center; justify-content: center;
    transition: background .15s, color .15s;
    flex-shrink: 0;
}
.sb-close:hover {
    background: rgba(239,68,68,.18);
    border-color: rgba(239,68,68,.3);
    color: #fca5a5;
}

@media (max-width: 900px) {
    .sb-close { display: flex; } /* visible seulement sur mobile */
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
    padding: 10px 10px 32px; /* padding-bottom large — Support toujours visible
                                au-dessus du footer, même accordéon ouvert */
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1px;
    /* PAS de scroll interne sur .sb-nav :
       c'est .sidebar entière qui scrolle avec sa scrollbar verte */
    overflow: visible;
}

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

/* ── Groupe accordéon sous-menu ── */
.sb-group { display: flex; flex-direction: column; }

.sb-group-toggle {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 10px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 500;
    color: var(--sb-txt); cursor: pointer;
    transition: background .15s, color .15s;
    user-select: none; border: none; background: none;
    width: 100%; text-align: left; font-family: var(--font);
}
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.8); }
.sb-group-toggle.open  { color: rgba(255,255,255,.9); background: rgba(255,255,255,.03); }
.sb-group-toggle .ico  { font-size: 14px; width: 20px; text-align: center; flex-shrink: 0; }
.sb-group-toggle .sb-arrow {
    margin-left: auto; font-size: 10px;
    color: rgba(255,255,255,.25); transition: transform .2s; flex-shrink: 0;
}
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.5); }

/* Sous-items indentés */
.sb-sub {
    display: none; flex-direction: column; gap: 1px;
    margin-left: 12px; padding-left: 14px;
    border-left: 1px solid rgba(255,255,255,.07);
    margin-top: 2px; margin-bottom: 4px;
    overflow: visible; /* ne jamais couper les sous-items */
}
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 12.5px; padding: 6px 10px; color: rgba(255,255,255,.45); }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.75); }
.sb-sub .sb-item.active { color: var(--sb-txt-act); background: var(--sb-act); }

.sb-footer {
    padding: 12px 10px;
    border-top: 1px solid var(--sb-border);
    flex-shrink: 0; /* ne rétrécit pas */
    display: flex;
    flex-direction: column;
    gap: 6px;
    /* Colle naturellement en bas du flux de scroll —
       visible uniquement quand on arrive en bas de la sidebar */
    position: sticky;
    bottom: 0;
    background: var(--sb-bg);
    z-index: 1;
}

/* ── Indicateur de scroll sidebar ─────────────────────────────
   Ombre en bas de la sidebar qui indique qu'il y a du contenu
   en dessous. Disparaît quand on arrive tout en bas.
   ─────────────────────────────────────────────────────────── */
.sb-scroll-hint {
    position: sticky; /* sticky dans le flux de la sidebar */
    top: auto;
    bottom: 72px; /* au-dessus du footer sticky */
    width: 100%;
    height: 40px;
    background: linear-gradient(to bottom, transparent, rgba(13,31,24,.9));
    pointer-events: none;
    z-index: 2;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-bottom: 6px;
    transition: opacity .3s;
    margin-top: -40px; /* overlap avec le contenu au-dessus */
    align-self: flex-end;
    /* Positionné via JS — visible uniquement si scroll nécessaire */
}
.sb-scroll-hint.hidden { opacity: 0; pointer-events: none; }
.sb-scroll-hint-arrow {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    animation: bounceDown 1.5s ease-in-out infinite;
}
.sb-scroll-hint-dot {
    width: 4px; height: 4px;
    border-radius: 50%;
    background: rgba(16,185,129,.6);
}
.sb-scroll-hint-dot:nth-child(2) { opacity: .5; margin-top: -2px; }
.sb-scroll-hint-dot:nth-child(3) { opacity: .25; margin-top: -2px; }
@keyframes bounceDown {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(4px); }
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
    .content { padding: 14px; }
    .topbar  { padding: 0 14px; }
    .kpi-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .kpi-val  { font-size: 20px; }
    .tb-actions .btn:not(.btn-primary) { display: none; }
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

/* ════════════════════════════════════════════════════════════════
   ÉTAPE 1 — NOUVEAUX COMPOSANTS CSS
   ════════════════════════════════════════════════════════════════ */

/* ── REVENUS AUJOURD'HUI ─────────────────────────────────────────
   Bandeau hero en haut du contenu : chiffre du jour bien visible
   avec comparaison hier. Design sobre mais impact fort.
   ────────────────────────────────────────────────────────────── */
.today-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 20px;
}
.today-card {
    background: linear-gradient(135deg, #0d1f18 0%, #1a3328 100%);
    border: 1px solid rgba(16,185,129,.2);
    border-radius: var(--r);
    padding: 20px 22px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,.12);
    position: relative; overflow: hidden;
}
/* Décoration de fond */
.today-card::after {
    content: '';
    position: absolute; right: -20px; top: -20px;
    width: 100px; height: 100px;
    background: rgba(16,185,129,.06);
    border-radius: 50%;
}
.today-icon {
    width: 48px; height: 48px;
    background: rgba(16,185,129,.15);
    border: 1px solid rgba(16,185,129,.25);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.today-lbl  { font-size: 11px; font-weight: 600; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.today-val  { font-size: 26px; font-weight: 700; color: #fff; font-family: var(--mono); letter-spacing: -1px; line-height: 1; }
.today-unit { font-size: 10px; color: rgba(255,255,255,.35); margin-top: 3px; }
.today-delta {
    font-size: 11px; font-weight: 700;
    margin-top: 6px; display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 8px; border-radius: 20px;
}
.today-delta.up   { background: rgba(16,185,129,.2); color: #34d399; }
.today-delta.down { background: rgba(239,68,68,.2);  color: #fca5a5; }
.today-delta.flat { background: rgba(255,255,255,.08); color: rgba(255,255,255,.4); }

/* ── ALERTES INTELLIGENTES ────────────────────────────────────────
   Zone d'alertes dynamiques : n'apparaît que s'il y a des alertes.
   Chaque alerte a une couleur selon sa gravité.
   ────────────────────────────────────────────────────────────── */
.alerts-zone { margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; }

.alert-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 14px;
    border-radius: var(--r-sm);
    border: 1px solid;
    font-size: 12.5px; font-weight: 500;
    animation: slideIn .3s ease;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.alert-item.danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.alert-item.warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
.alert-item.success { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; }
.alert-item.info    { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }

.alert-ico  { font-size: 16px; flex-shrink: 0; }
.alert-msg  { flex: 1; line-height: 1.4; }
.alert-cta  {
    font-size: 11px; font-weight: 700;
    padding: 4px 10px; border-radius: 6px;
    border: 1px solid currentColor;
    text-decoration: none; color: inherit;
    white-space: nowrap; flex-shrink: 0;
    opacity: .75; transition: opacity .15s;
}
.alert-cta:hover { opacity: 1; }

/* ── KANBAN STATUTS ───────────────────────────────────────────────
   5 colonnes représentant le flux de vie d'une commande.
   Le propriétaire voit instantanément où en est son business.
   ────────────────────────────────────────────────────────────── */
.kanban-section { margin-bottom: 22px; }
.kanban-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
}
.kanban-col {
    background: var(--surface);
    border: 1px solid var(--border);
    border-top: 3px solid var(--k-color);
    border-radius: var(--r);
    padding: 14px 14px 12px;
    text-align: center;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .2s, transform .2s;
    cursor: default;
}
.kanban-col:hover {
    box-shadow: var(--shadow);
    transform: translateY(-2px);
}
.kanban-ico   { font-size: 20px; margin-bottom: 8px; }
.kanban-count {
    font-size: 28px; font-weight: 700;
    font-family: var(--mono); color: var(--k-color);
    line-height: 1; letter-spacing: -1px;
}
.kanban-lbl {
    font-size: 10.5px; font-weight: 600;
    color: var(--muted); margin-top: 4px;
    text-transform: uppercase; letter-spacing: .4px;
}
/* Indicateur visuel si count > 0 */
.kanban-col.has-items { background: var(--k-bg); }

/* ── ACTIONS RAPIDES ──────────────────────────────────────────────
   Grille de boutons d'action directe : les tâches les plus courantes
   accessibles en 1 clic sans naviguer dans les menus.
   ────────────────────────────────────────────────────────────── */
/* ── ACTIONS RAPIDES — carte conteneur + boutons inline ────────────
   Tous les boutons sont regroupés dans une seule card propre.
   Design horizontal : icône colorée + texte sur la même ligne.
   ────────────────────────────────────────────────────────────── */
.quick-section { margin-bottom: 22px; }

/* La card qui contient tous les boutons */
.quick-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

/* Header de la card */
.quick-card-hd {
    padding: 13px 18px 12px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.quick-card-title {
    font-size: 13px; font-weight: 700; color: var(--text);
    display: flex; align-items: center; gap: 8px;
}
.quick-card-title .title-ico {
    width: 24px; height: 24px;
    background: var(--brand-mlt);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
}

/* Grille de boutons à l'intérieur de la card */
.quick-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
}

/* Séparateur vertical entre boutons */
.quick-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 9px;
    padding: 20px 14px 18px;
    text-decoration: none;
    border-right: 1px solid var(--border);
    transition: background .15s, transform .15s;
    position: relative;
    cursor: pointer;
}
/* Dernier bouton sans bordure droite */
.quick-btn:last-child { border-right: none; }

/* Hover : fond coloré léger + indicateur en haut */
.quick-btn:hover { background: var(--q-bg, var(--brand-mlt)); }
.quick-btn::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0;
    height: 2px;
    background: var(--q-color, var(--brand));
    opacity: 0;
    transition: opacity .15s;
}
.quick-btn:hover::before { opacity: 1; }

/* Icône dans un cercle coloré */
.quick-btn-ico {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    background: var(--q-bg, var(--brand-mlt));
    border: 1px solid var(--q-border, var(--brand-lt));
    transition: transform .15s;
    flex-shrink: 0;
}
.quick-btn:hover .quick-btn-ico {
    transform: scale(1.08);
}

.quick-btn-lbl {
    font-size: 12.5px; font-weight: 700;
    color: var(--text); line-height: 1.2;
    text-align: center;
}
.quick-btn-sub {
    font-size: 10.5px; color: var(--muted);
    line-height: 1.3; text-align: center;
}

@media (max-width: 860px) {
    .kanban-grid { grid-template-columns: repeat(3,1fr); }
    .quick-grid  { grid-template-columns: repeat(2,1fr); }
    .quick-btn   { border-right: none; border-bottom: 1px solid var(--border); }
    .quick-btn:last-child { border-bottom: none; }
    .today-grid  { grid-template-columns: 1fr; }
    .e2-grid     { grid-template-columns: 1fr; }
}

/* ================================================================
   ETAPE 2 — CSS : Top clients, Produits a risque, Graphique 6 mois
   ================================================================ */

/* Grille 2 colonnes pour blocs A et B cote a cote */
.e2-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 22px;
}

/* TOP CLIENTS */
.client-list { display: flex; flex-direction: column; }
.client-row {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 18px;
    border-bottom: 1px solid #f3f6f4;
    transition: background .12s;
}
.client-row:last-child { border-bottom: none; }
.client-row:hover { background: var(--bg); }
.client-av {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.client-info { flex: 1; min-width: 0; }
.client-name {
    font-size: 12.5px; font-weight: 600; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.client-meta { font-size: 10.5px; color: var(--muted); margin-top: 1px; }
.client-bar-wrap { width: 60px; flex-shrink: 0; }
.client-bar-track {
    height: 5px; background: #eef1f0;
    border-radius: 3px; overflow: hidden; margin-bottom: 3px;
}
.client-bar-fill {
    height: 100%; border-radius: 3px; background: var(--brand);
    transition: width 1s cubic-bezier(.23,1,.32,1);
}
.client-amount {
    font-family: var(--mono); font-size: 10px;
    font-weight: 600; color: var(--text);
    text-align: right; white-space: nowrap;
}
.client-rank {
    width: 20px; flex-shrink: 0;
    font-size: 11px; font-weight: 700;
    color: var(--muted); text-align: center;
}
.client-rank.top { color: #f59e0b; font-size: 14px; }

/* PRODUITS A RISQUE */
.risk-list { display: flex; flex-direction: column; }
.risk-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 18px;
    border-bottom: 1px solid #f3f6f4;
    transition: background .12s;
}
.risk-row:last-child { border-bottom: none; }
.risk-row:hover { background: var(--bg); }
.risk-img {
    width: 38px; height: 38px; border-radius: var(--r-sm);
    object-fit: cover; flex-shrink: 0;
    border: 1px solid var(--border);
}
.risk-img-placeholder {
    width: 38px; height: 38px; border-radius: var(--r-sm);
    background: #f3f6f4; border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.risk-info { flex: 1; min-width: 0; }
.risk-name {
    font-size: 12.5px; font-weight: 600; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.risk-meta { font-size: 10.5px; color: var(--muted); margin-top: 1px; }
.risk-badge {
    font-size: 10px; font-weight: 700;
    padding: 3px 8px; border-radius: 20px;
    background: #fef2f2; color: #991b1b;
    border: 1px solid #fca5a5;
    white-space: nowrap; flex-shrink: 0;
}
.risk-empty {
    padding: 20px 18px; text-align: center;
    font-size: 13px; color: var(--muted);
}
.risk-empty .ico { font-size: 28px; display: block; margin-bottom: 6px; }

/* ── SELECTEUR DE PERIODE ──────────────────────────────────────
   Card avec boutons de periodes rapides + résumé des stats
   pour la période sélectionnée. Mise à jour via AJAX (fetch).
   ─────────────────────────────────────────────────────────── */
.period-card { margin-bottom: 22px; }

/* Grille de boutons de période */
.period-btns {
    display: flex; flex-wrap: wrap; gap: 6px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
}
.period-btn {
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 600; font-family: var(--font);
    border: 1px solid var(--border-dk);
    background: var(--bg); color: var(--text-2);
    cursor: pointer; transition: all .15s;
    white-space: nowrap;
}
.period-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-mlt); }
.period-btn.active {
    background: var(--brand); color: #fff;
    border-color: var(--brand-dk);
    box-shadow: 0 2px 8px rgba(16,185,129,.25);
}

/* Zone stats de la période */
.period-stats {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 0;
}
.period-stat {
    padding: 16px 18px;
    border-right: 1px solid var(--border);
    text-align: center;
}
.period-stat:last-child { border-right: none; }
.period-stat-lbl { font-size: 10.5px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .3px; margin-bottom: 5px; }
.period-stat-val { font-size: 20px; font-weight: 700; font-family: var(--mono); color: var(--text); letter-spacing: -.5px; }
.period-stat-sub { font-size: 10px; color: var(--muted); margin-top: 2px; }

/* Zone graphique barres journalières */
.period-chart {
    padding: 16px 18px 14px;
    border-top: 1px solid var(--border);
}
.period-bars {
    display: flex; align-items: flex-end; gap: 3px;
    height: 70px; margin-bottom: 6px;
}
.period-bar-wrap { flex: 1; height: 100%; display: flex; align-items: flex-end; }
.period-bar {
    width: 100%; border-radius: 3px 3px 0 0;
    background: var(--brand); opacity: .8;
    transition: height .4s cubic-bezier(.23,1,.32,1), opacity .15s;
    cursor: pointer; position: relative; min-height: 2px;
}
.period-bar:hover { opacity: 1; }
.period-bar.empty { opacity: .15; background: #9ca3af; }
.period-bar::after {
    content: attr(data-tip);
    position: absolute; bottom: calc(100% + 5px); left: 50%;
    transform: translateX(-50%);
    background: #0f1c18; color: #fff;
    font-size: 10px; font-weight: 600; font-family: var(--mono);
    padding: 3px 7px; border-radius: 4px;
    white-space: nowrap; pointer-events: none;
    opacity: 0; transition: opacity .15s; z-index: 10;
}
.period-bar:hover::after { opacity: 1; }
.period-bar-labels {
    display: flex; gap: 3px; overflow: hidden;
}
.period-bar-lbl {
    flex: 1; text-align: center;
    font-size: 9px; color: var(--muted); font-family: var(--mono);
    white-space: nowrap; overflow: hidden; text-overflow: clip;
}

/* Loader */
.period-loading {
    display: none; align-items: center; justify-content: center;
    padding: 32px; gap: 10px;
    font-size: 13px; color: var(--muted); font-weight: 500;
}
.period-loading.show { display: flex; }
.spin {
    width: 18px; height: 18px; border-radius: 50%;
    border: 2px solid var(--brand-lt);
    border-top-color: var(--brand);
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Label de la période active */
.period-label {
    font-size: 11px; color: var(--muted); font-weight: 500;
    padding: 0 18px 10px;
}
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

    /* ── REVENU NET = CA livrée - Commissions PAYÉES aux livreurs ────────────
     * On soustrait les commissions déjà versées pour afficher le vrai revenu
     * net de la boutique. Seules les commissions au statut 'payée' sont
     * déduites — les commissions en attente ne sont pas encore sorties.
     * ──────────────────────────────────────────────────────────────────────── */

    /* Commissions payées CE MOIS — à soustraire du CA */
    $commissionsPaieesMonth = (float) \App\Models\CourierCommission::where('shop_id', $shop->id)
        ->where('status', 'payée')
        ->whereMonth('created_at', $now->month)
        ->whereYear('created_at', $now->year)
        ->sum('amount');

    /* CA brut ce mois (commandes livrées) */
    $caGrossMonth = (float) $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->sum('total');
    /* Revenu NET = CA brut - commissions payées */
    $caMonth = $caGrossMonth - $commissionsPaieesMonth;

    /* Mois précédent */
    $commissionsPaieesPrev = (float) \App\Models\CourierCommission::where('shop_id', $shop->id)
        ->where('status', 'payée')
        ->whereMonth('created_at', $now->copy()->subMonth()->month)
        ->whereYear('created_at', $now->copy()->subMonth()->year)
        ->sum('amount');
    $caGrossPrev  = (float) $shop->orders()->whereMonth('created_at',$now->copy()->subMonth()->month)->whereYear('created_at',$now->copy()->subMonth()->year)->where('status','livrée')->sum('total');
    $caPrev       = ($caGrossPrev - $commissionsPaieesPrev) ?: 1;

    $caDelta  = round((($caMonth - $caPrev) / $caPrev) * 100, 1);

    /* Commandes reçues ce mois (toutes sauf annulées) — sert au KPI "Commandes" */
    /* On exclut les annulées : une commande annulée ne compte pas dans les stats */
    $cmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->whereNotIn('status',['annulée','cancelled'])->count();
    $cmdToday = $shop->orders()->whereDate('created_at', today())->whereNotIn('status',['annulée','cancelled'])->count();
    $cmdYest  = $shop->orders()->whereDate('created_at', today()->subDay())->whereNotIn('status',['annulée','cancelled'])->count();

    /* Panier moyen net — CA net / nb livraisons */
    $cmdLivreesMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->count();
    $panier   = $cmdLivreesMonth > 0 ? round($caMonth / $cmdLivreesMonth) : 0;

    /* Taux livraison */
    /* Taux livraison = livrées / commandes non annulées ce mois */
    $totalCmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereNotIn('status',['annulée','cancelled'])->count();
    $livres        = $shop->orders()->whereMonth('created_at',$now->month)->where('status','livrée')->count();
    $tauxLiv       = $totalCmdMonth > 0 ? round(($livres / $totalCmdMonth) * 100, 1) : 0;

    /* Graph 7 jours — revenus NETS (livrée - commissions payées du jour) */
    $days7 = collect(range(6,0))->map(function ($i) use ($shop, $now) {
        $day        = $now->copy()->subDays($i)->toDateString();
        $caJour     = (float) $shop->orders()->whereDate('created_at', $day)->where('status','livrée')->sum('total');
        $commJour   = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop, $day) {
            $q->where('shop_id', $shop->id)->whereDate('created_at', $day);
        })->where('status', 'payée')->sum('amount');
        return [
            'label' => $now->copy()->subDays($i)->isoFormat('dd'),
            'value' => max(0, $caJour - $commJour),
            'today' => $i === 0,
        ];
    });
    $max7 = $days7->max('value') ?: 1;

    /* Commandes récentes */
    $recentOrders = $shop->orders()->with('user')->latest()->take(6)->get();

    /* Top produits */
    $topProducts = $shop->products()->withCount('orderItems')->orderByDesc('order_items_count')->take(5)->get();
    $maxSales    = $topProducts->max('order_items_count') ?: 1;

    /* Statuts */
    $statusMap = [
        'livrée'      => ['label'=>'Livré',        'cls'=>'p-success'],
        'pending'     => ['label'=>'En attente',   'cls'=>'p-warning'],
        'processing'  => ['label'=>'En traitement','cls'=>'p-info'],
        'confirmée'   => ['label'=>'Confirmé',     'cls'=>'p-info'],
        'en_livraison'=> ['label'=>'En livraison', 'cls'=>'p-info'],
        'shipped'     => ['label'=>'Expédié',      'cls'=>'p-info'],
        'annulée'     => ['label'=>'Annulé',       'cls'=>'p-danger'],
    ];

    /* Pending badge */
    $pendingCount = $shop->orders()->whereIn('status',['en attente','en_attente','pending','confirmée','processing'])->count();

    /* Avatar colors */
    $avColors = ['av-green','av-blue','av-amber','av-purple','av-teal','av-rose'];

    /* ── DEVISE DE LA BOUTIQUE ────────────────────────────────────
     * On lit la devise choisie à la création de la boutique.
     * Si aucune devise n'est définie, on tombe sur 'GNF' par défaut.
     * Cette variable $devise est utilisée PARTOUT dans la vue pour
     * afficher la bonne unité monétaire.
     * ──────────────────────────────────────────────────────────── */
    $devise = $shop->currency ?? 'GNF';

    /* ═══════════════════════════════════════════════════════════════
     * ÉTAPE 1 — NOUVELLES DONNÉES : contrôle opérationnel quotidien
     * ═══════════════════════════════════════════════════════════════ */

    /* ── REVENUS AUJOURD'HUI / HIER — NET (livrée - commissions payées) ──────────
     * On soustrait les commissions déjà payées pour afficher le vrai revenu net.
     * ──────────────────────────────────────────────────────────── */
    $caGrossToday    = (float) $shop->orders()->whereDate('created_at', today())->where('status','livrée')->sum('total');
    $commToday       = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop) {
        $q->where('shop_id', $shop->id)->whereDate('created_at', today());
    })->where('status', 'payée')->sum('amount');
    $caToday         = max(0, $caGrossToday - $commToday);

    $caGrossYesterday = (float) $shop->orders()->whereDate('created_at', today()->subDay())->where('status','livrée')->sum('total');
    $commYesterday    = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop) {
        $q->where('shop_id', $shop->id)->whereDate('created_at', today()->subDay());
    })->where('status', 'payée')->sum('amount');
    $caYesterday      = max(0, $caGrossYesterday - $commYesterday);
    // Variation en % (si hier = 0, on met 0 pour éviter la division par zéro)
    $caTodayDelta = $caYesterday > 0
        ? round((($caToday - $caYesterday) / $caYesterday) * 100, 1)
        : ($caToday > 0 ? 100 : 0);

    /* ── ALERTES INTELLIGENTES ────────────────────────────────────
     * On génère une liste d'alertes selon l'état de la boutique.
     * Chaque alerte a : type (warning/danger/info), icône, message.
     * Le propriétaire voit d'un coup ce qui nécessite son attention.
     * ──────────────────────────────────────────────────────────── */
    $alerts = collect();

    // Alerte 1 : commandes en attente depuis plus de 2h → action urgente
    $cmdEnRetard = $shop->orders()
        ->whereIn('status', ['pending', 'en attente', 'en_attente'])
        ->where('created_at', '<', now()->subHours(2))
        ->count();
    if ($cmdEnRetard > 0) {
        $alerts->push([
            'type' => 'danger',
            'ico'  => '🚨',
            'msg'  => "{$cmdEnRetard} commande(s) en attente depuis plus de 2h — à traiter urgemment",
            'link' => route('boutique.orders.index'),
            'cta'  => 'Voir les commandes',
        ]);
    }

    // Alerte 2 : aucun livreur disponible alors qu'il y a des commandes à livrer
    $cmdALivrer = $shop->orders()
        ->whereIn('status', ['confirmée', 'confirmed', 'processing'])
        ->count();
    if ($cmdALivrer > 0 && $livreursDisponibles->isEmpty()) {
        $alerts->push([
            'type' => 'warning',
            'ico'  => '⚠️',
            'msg'  => "{$cmdALivrer} commande(s) prête(s) à livrer mais aucun livreur disponible",
            'link' => route('delivery.companies.index'),
            'cta'  => 'Trouver un livreur',
        ]);
    }

    // Alerte 3 : boutique non approuvée → le propriétaire doit le savoir
    if (!$shop->is_approved) {
        $alerts->push([
            'type' => 'warning',
            'ico'  => '⏳',
            'msg'  => "Votre boutique est en attente de validation par l'administrateur",
            'link' => null,
            'cta'  => null,
        ]);
    }

    // Alerte 4 : bonne nouvelle → si CA aujourd'hui > hier, on encourage
    if ($caTodayDelta >= 20 && $caToday > 0) {
        $alerts->push([
            'type' => 'success',
            'ico'  => '🎉',
            'msg'  => "Excellente journée ! Vos revenus d'aujourd'hui sont en hausse de {$caTodayDelta}% vs hier",
            'link' => null,
            'cta'  => null,
        ]);
    }

    /* ── KANBAN STATUTS ───────────────────────────────────────────
     * On compte le nombre de commandes dans chaque étape du flux.
     * Permet au propriétaire de voir instantanément où en est
     * son pipeline de commandes sans ouvrir la liste complète.
     * ──────────────────────────────────────────────────────────── */
    /* ── KANBAN : pipeline en temps réel ─────────────────────────────
     * Logique des statuts utilisés par le livreur (LivreurOrderController) :
     *   - Livreur clique "Commencer" → status = 'en_livraison'
     *   - Livreur clique "Terminer"  → status = 'livrée'
     *
     * Les colonnes "En livraison" et "Terminées" reflètent ces deux états
     * en temps réel : le chiffre monte/descend à chaque action du livreur.
     * ─────────────────────────────────────────────────────────────── */
    $kanban = [

        // ── Étape 1 : EN ATTENTE ──────────────────────────────────
        // Commandes reçues mais pas encore prises en charge.
        // Statuts possibles (FR + EN pour compatibilité) :
        [
            'label'  => 'En attente',
            'count'  => $shop->orders()
                            ->whereIn('status', ['pending','en attente','en_attente'])
                            ->count(),
            'color'  => '#f59e0b',
            'bg'     => '#fffbeb',
            'ico'    => '📥',
        ],

        // ── Étape 2 : CONFIRMÉES ──────────────────────────────────
        // Commandes validées par la boutique, en attente d'être
        // assignées à un livreur.
        [
            'label'  => 'Confirmées',
            'count'  => $shop->orders()
                            ->whereIn('status', ['confirmed','confirmée','processing'])
                            ->count(),
            'color'  => '#3b82f6',
            'bg'     => '#eff6ff',
            'ico'    => '✅',
        ],

        // ── Étape 3 : EN LIVRAISON ────────────────────────────────
        // Le livreur a cliqué "Commencer" → status = 'en_livraison'.
        // Ce chiffre monte quand le livreur part en course.
        // Il redescend quand il clique "Terminer".
        [
            'label'  => 'En livraison',
            'count'  => $shop->orders()
                            ->whereIn('status', ['en_livraison','delivering','shipped'])
                            ->count(),
            'color'  => '#8b5cf6',
            'bg'     => '#f5f3ff',
            'ico'    => '🚴',
        ],

        // ── Étape 4 : TERMINÉES (livrées) ─────────────────────────
        // Le livreur a cliqué "Terminer" → status = 'livrée'.
        // Ce chiffre monte chaque fois qu'une livraison est complétée.
        // Filtre sur le mois courant pour rester pertinent.
        [
            'label'  => 'Terminées',
            'count'  => $shop->orders()
                            ->whereMonth('created_at', $now->month)
                            ->where('status', 'livrée')
                            ->count(),
            'color'  => '#10b981',
            'bg'     => '#ecfdf5',
            'ico'    => '🎯',
        ],

        // ── Étape 5 : ANNULÉES ────────────────────────────────────
        // Commandes annulées ce mois. Si ce chiffre monte,
        // le propriétaire doit investiguer pourquoi.
        [
            'label'  => 'Annulées',
            'count'  => $shop->orders()
                            ->whereMonth('created_at', $now->month)
                            ->whereIn('status', ['annulée','cancelled'])
                            ->count(),
            'color'  => '#ef4444',
            'bg'     => '#fef2f2',
            'ico'    => '❌',
        ],

    ];

    /* ── LIVRAISON LOGIC (inchangé) ──────────────────────────────*/
    $hasLivreurs  = $livreursDisponibles->isNotEmpty();
    $hasCompanies = isset($deliveryCompanies) && $deliveryCompanies->isNotEmpty();

    /* ═══════════════════════════════════════════════════════════════
     * ÉTAPE 2 — ANALYSE BUSINESS : top clients, produits à risque,
     *           évolution mensuelle sur 6 mois
     * ═══════════════════════════════════════════════════════════════ */

    /* ── PRODUITS À RISQUE ────────────────────────────────────────
     * Produits du catalogue qui n'ont fait AUCUNE vente ce mois.
     * Signal d'alerte : soit le produit est mal affiché, soit
     * le prix est trop élevé, soit il ne correspond plus à la demande.
     * On limite à 5 pour ne pas surcharger le dashboard.
     * ──────────────────────────────────────────────────────────── */
    $produitsRisque = $shop->products()
        ->withCount([
            // Compte uniquement les order_items liés à des commandes de ce mois
            'orderItems as ventes_mois' => function ($q) use ($now) {
                $q->whereHas('order', function ($o) use ($now) {
                    $o->whereMonth('created_at', $now->month)
                      ->whereYear('created_at', $now->year);
                });
            }
        ])
        ->having('ventes_mois', '=', 0)                        // seulement ceux avec 0 vente
        ->orderBy('created_at', 'desc')                         // les plus récents d'abord
        ->take(5)
        ->get();

    /* Graphique 6 mois remplacé par sélecteur de période dynamique (AJAX) */
@endphp

<div class="dash-wrap" id="dashWrap">

    {{-- ══════ SIDEBAR ══════ --}}
    <aside class="sidebar" id="sidebar">
        <div class="sb-brand">
            <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
                <div class="sb-logo-icon">🛍️</div>
                <span class="sb-shop-name">{{ $shop->name }}</span>
            </a>
            {{-- Croix de fermeture — visible uniquement sur mobile --}}
            <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer le menu">✕</button>
            <div class="sb-status">
                <span class="pulse"></span>
                {{ $shop->is_approved ? 'Boutique active' : 'En attente de validation' }}
                &nbsp;·&nbsp;
                {{ ucfirst(auth()->user()->role_in_shop ?? auth()->user()->role) }}
            </div>
        </div>

        {{-- Indicateur scroll : 3 points verts animés qui indiquent
             qu'il y a des éléments en dessous dans la sidebar --}}
        <div class="sb-scroll-hint" id="sbScrollHint">
            <div class="sb-scroll-hint-arrow">
                <div class="sb-scroll-hint-dot"></div>
                <div class="sb-scroll-hint-dot"></div>
                <div class="sb-scroll-hint-dot"></div>
            </div>
        </div>

        <nav class="sb-nav">

            {{-- ════════ DASHBOARD ════════ --}}
            <a href="{{ route('boutique.dashboard') }}" class="sb-item active" style="margin-bottom:4px">
                <span class="ico">⊞</span> Tableau de bord
            </a>

            {{-- ════════ BOUTIQUE ════════ --}}
            <div class="sb-section">Boutique</div>

            <a href="{{ route('boutique.orders.index') }}" class="sb-item">
                <span class="ico">📦</span> Commandes
                @if($pendingCount > 0)
                    <span class="sb-badge">{{ $pendingCount }}</span>
                @endif
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

            {{-- ════════ LIVRAISON ════════ --}}
            <div class="sb-section">Livraison</div>

            <a href="{{ route('boutique.livreurs.index') }}" class="sb-item">
                <span class="ico">🚴</span> Livreurs
                @if($livreursDisponibles->count() > 0)
                    <span class="sb-badge">{{ $livreursDisponibles->count() }}</span>
                @endif
            </a>
            <a href="{{ route('delivery.companies.index') }}" class="sb-item">
                <span class="ico">🏢</span> Partenaires
            </a>

            {{-- ════════ FINANCES (accordéon) ════════ --}}
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

            {{-- ════════ AIDE ════════ --}}
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
            {{-- ── KPI Grid — premier regard du matin ── --}}
            <div class="kpi-grid" style="margin-bottom:22px">

                <div class="kpi" style="--kpi-color:#10b981;--kpi-bg:#ecfdf5">
                    <div class="kpi-icon">💰</div>
                    <div class="kpi-lbl">Revenu net</div>
                    <div class="kpi-val">{{ number_format($caMonth,0,',',' ') }}</div>
                    <div class="kpi-unit">{{ $devise }} · {{ $now->translatedFormat('F Y') }}</div>
                    <div class="kpi-delta {{ $caDelta >= 0 ? 'up':'down' }}">
                        {{ $caDelta >= 0 ? '↑':'↓' }} {{ abs($caDelta) }}% vs mois précédent
                    </div>
                    {{-- Décomposition CA brut - commissions ── --}}
                    @if($commissionsPaieesMonth > 0)
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:3px">
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--muted)">
                            <span>CA brut</span>
                            <span style="font-family:var(--mono);color:var(--text-2)">{{ number_format($caGrossMonth,0,',',' ') }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--muted)">
                            <span>Commissions</span>
                            <span style="font-family:var(--mono);color:#dc2626">− {{ number_format($commissionsPaieesMonth,0,',',' ') }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;font-weight:700;padding-top:3px;border-top:1px dashed var(--border)">
                            <span style="color:var(--brand)">Net</span>
                            <span style="font-family:var(--mono);color:var(--brand)">{{ number_format($caMonth,0,',',' ') }}</span>
                        </div>
                    </div>
                    @endif
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
                    <div class="kpi-unit">{{ $devise }} / commande</div>
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

            {{-- Commission banner --}}
            @if($shop->commission_rate)
            <div class="comm-banner">
                💡 <span>Taux de commission : <strong>{{ $shop->commission_rate_percent }}%</strong> — appliqué à chaque commande validée.</span>
            </div>
            @endif

            {{-- ════════════════════════════════════════════════════════
                 ÉTAPE 1 — BLOC A : REVENUS AUJOURD'HUI
                 Deux cartes sombres mises en avant : revenus du jour
                 et nombre de commandes du jour. Premier regard le matin.
                 ════════════════════════════════════════════════════════ --}}
            <div class="today-grid">

                {{-- Carte : Revenus aujourd'hui --}}
                <div class="today-card">
                    <div class="today-icon">💵</div>
                    <div style="flex:1;min-width:0">
                        <div class="today-lbl">Revenu net aujourd'hui</div>
                        <div class="today-val">{{ number_format($caToday, 0, ',', ' ') }}</div>
                        <div class="today-unit">{{ $devise }}</div>
                        {{-- Tendance vs hier --}}
                        <div class="today-delta {{ $caTodayDelta > 0 ? 'up' : ($caTodayDelta < 0 ? 'down' : 'flat') }}">
                            @if($caTodayDelta > 0) ↑ +{{ $caTodayDelta }}% vs hier
                            @elseif($caTodayDelta < 0) ↓ {{ $caTodayDelta }}% vs hier
                            @else — Même niveau qu'hier
                            @endif
                        </div>
                        {{-- Décomposition CA brut - commissions aujourd'hui ── --}}
                        @if($commToday > 0)
                        <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.12);display:flex;flex-direction:column;gap:3px">
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.4)">
                                <span>CA brut</span>
                                <span style="font-family:var(--mono);color:rgba(255,255,255,.6)">{{ number_format($caGrossToday,0,',',' ') }}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.4)">
                                <span>Commissions</span>
                                <span style="font-family:var(--mono);color:#fca5a5">− {{ number_format($commToday,0,',',' ') }}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:10px;font-weight:700;padding-top:3px;border-top:1px dashed rgba(255,255,255,.1)">
                                <span style="color:#34d399">Net</span>
                                <span style="font-family:var(--mono);color:#34d399">{{ number_format($caToday,0,',',' ') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Carte : Commandes aujourd'hui --}}
                <div class="today-card">
                    <div class="today-icon">📦</div>
                    <div>
                        <div class="today-lbl">Commandes aujourd'hui</div>
                        <div class="today-val">{{ $cmdToday }}</div>
                        <div class="today-unit">commandes reçues</div>
                        {{-- Tendance vs hier --}}
                        <div class="today-delta {{ $cmdToday >= $cmdYest ? 'up' : 'down' }}">
                            @if($cmdToday > $cmdYest) ↑ +{{ $cmdToday - $cmdYest }} vs hier
                            @elseif($cmdToday < $cmdYest) ↓ {{ $cmdYest - $cmdToday }} de moins vs hier
                            @else — Même niveau qu'hier
                            @endif
                        </div>
                    </div>
                </div>

            </div>
            {{-- /ÉTAPE 1 — BLOC A --}}


            {{-- ════════════════════════════════════════════════════════
                 ÉTAPE 1 — BLOC B : ALERTES INTELLIGENTES
                 N'apparaît QUE s'il y a des alertes à signaler.
                 Le propriétaire voit immédiatement ce qui nécessite
                 son attention sans chercher dans les menus.
                 ════════════════════════════════════════════════════════ --}}
            @if($alerts->isNotEmpty())
            <div class="alerts-zone">
                @foreach($alerts as $alert)
                <div class="alert-item {{ $alert['type'] }}">
                    <span class="alert-ico">{{ $alert['ico'] }}</span>
                    <span class="alert-msg">{{ $alert['msg'] }}</span>
                    {{-- Bouton d'action direct si une route est définie --}}
                    @if($alert['link'])
                    <a href="{{ $alert['link'] }}" class="alert-cta">
                        {{ $alert['cta'] }} →
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
            {{-- /ÉTAPE 1 — BLOC B --}}


            {{-- ════════════════════════════════════════════════════════
                 ÉTAPE 1 — BLOC C : KANBAN STATUTS COMMANDES
                 5 colonnes = les 5 étapes de vie d'une commande.
                 Permet au propriétaire de voir son pipeline en 1 coup
                 d'œil : combien sont en attente, en cours, livrées.
                 ════════════════════════════════════════════════════════ --}}
            <div class="kanban-section">
                <div class="card-hd" style="padding:0 0 12px;border:none;background:transparent">
                    <span class="card-title" style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">
                        Pipeline commandes
                    </span>
                    <a href="{{ route('boutique.orders.index') }}" class="btn btn-ghost btn-sm">
                        Gérer les commandes →
                    </a>
                </div>
                <div class="kanban-grid">
                    @foreach($kanban as $col)
                    <div class="kanban-col {{ $col['count'] > 0 ? 'has-items' : '' }}"
                         style="--k-color:{{ $col['color'] }};--k-bg:{{ $col['bg'] }}">

                        {{-- Icône de l'étape --}}
                        <div class="kanban-ico">{{ $col['ico'] }}</div>

                        {{-- Chiffre principal coloré selon l'étape --}}
                        <div class="kanban-count">{{ $col['count'] }}</div>

                        {{-- Label de l'étape --}}
                        <div class="kanban-lbl">{{ $col['label'] }}</div>

                        {{-- Sous-texte contextuel :
                             "En livraison" → indique que le livreur est en route
                             "Terminées"    → précise que c'est ce mois --}}
                        @if($col['label'] === 'En livraison' && $col['count'] > 0)
                            <div style="font-size:9px;color:var(--muted);margin-top:3px;font-weight:600">
                                🔴 En cours
                            </div>
                        @elseif($col['label'] === 'Terminées')
                            <div style="font-size:9px;color:var(--muted);margin-top:3px">
                                ce mois
                            </div>
                        @endif

                    </div>
                    @endforeach
                </div>
            </div>
            {{-- /ÉTAPE 1 — BLOC C --}}


            {{-- ════════════════════════════════════════════════════════
                 ÉTAPE 1 — BLOC D : ACTIONS RAPIDES
                 Les 4 actions les plus fréquentes en 1 clic.
                 Évite au propriétaire de naviguer dans les menus
                 pour les tâches quotidiennes.
                 ════════════════════════════════════════════════════════ --}}
            {{-- ════════════════════════════════════════════════════════
                 ÉTAPE 1 — BLOC D : ACTIONS RAPIDES
                 Tous les boutons sont regroupés dans une seule card.
                 Design : icône colorée + label + sous-titre.
                 Hover : fond coloré + barre de couleur en haut.
                 ════════════════════════════════════════════════════════ --}}
            <div class="quick-section">
                <div class="quick-card">

                    {{-- En-tête de la card --}}
                    <div class="quick-card-hd">
                        <div class="quick-card-title">
                            <span class="title-ico">⚡</span>
                            Actions rapides
                        </div>
                        <span style="font-size:11px;color:var(--muted)">Accès direct aux tâches courantes</span>
                    </div>

                    {{-- Grille des 4 boutons d'action --}}
                    <div class="quick-grid">

                        {{-- Action 1 : Commandes en attente
                             Couleur ambre = urgence modérée --}}
                        <a href="{{ route('boutique.orders.index') }}"
                           class="quick-btn"
                           style="--q-bg:#fffbeb;--q-border:#fde68a;--q-color:#f59e0b">
                            <div class="quick-btn-ico">📋</div>
                            <div class="quick-btn-lbl">Commandes</div>
                            <div class="quick-btn-sub">Voir & gérer</div>
                        </a>

                        {{-- Action 2 : Ajouter un produit au catalogue
                             Couleur verte = action positive --}}
                        <a href="{{ route('products.create') }}"
                           class="quick-btn"
                           style="--q-bg:#ecfdf5;--q-border:#6ee7b7;--q-color:#10b981">
                            <div class="quick-btn-ico">➕</div>
                            <div class="quick-btn-lbl">Nouveau produit</div>
                            <div class="quick-btn-sub">Ajouter au catalogue</div>
                        </a>

                        {{-- Action 3 : Gestion des livreurs
                             Couleur violette = gestion livreurs --}}
                        <a href="{{ route('boutique.livreurs.index') }}"
                           class="quick-btn"
                           style="--q-bg:#f5f3ff;--q-border:#c4b5fd;--q-color:#8b5cf6">
                            <div class="quick-btn-ico">🚴</div>
                            <div class="quick-btn-lbl">Livreurs</div>
                            <div class="quick-btn-sub">Voir en ligne</div>
                        </a>

                        {{-- Action 4 : Paiements et revenus
                             Couleur bleue = finances --}}
                        <a href="{{ route('boutique.payments.index') }}"
                           class="quick-btn"
                           style="--q-bg:#eff6ff;--q-border:#93c5fd;--q-color:#3b82f6">
                            <div class="quick-btn-ico">💳</div>
                            <div class="quick-btn-lbl">Paiements</div>
                            <div class="quick-btn-sub">Revenus reçus</div>
                        </a>

                    </div>{{-- /quick-grid --}}
                </div>{{-- /quick-card --}}
            </div>
            {{-- /ÉTAPE 1 — BLOC D --}}



            {{-- ── Chart 7 jours ── --}}
            <div class="card chart-wrap">
                <div class="card-hd">
                    <span class="card-title">Revenus — 7 derniers jours</span>
                    <span style="font-size:11px;color:var(--muted);font-weight:500">{{ $devise }}</span>
                </div>
                <div class="chart-inner">
                    <div class="chart-bars" id="chartBars">
                        @foreach($days7 as $day)
                        @php $pct = $day['value'] > 0 ? max(round(($day['value']/$max7)*100), 5) : 0; @endphp
                        <div class="bar-wrap">
                            <div class="bar {{ $day['today'] ? '' : 'dim' }}"
                                 data-h="{{ $pct }}"
                                 style="height:0%"
                                 title="{{ $day['label'] }} : {{ number_format($day['value'],0,',',' ') }} {{ $devise }}">
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
                                        <span style="font-size:9px;color:var(--muted)"> {{ $devise }}</span>
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
                            <div class="notice-title">Aucun livreur disponible</div>
                            <div class="notice-sub">
                                Votre boutique n'a pas encore de livreurs assignés.<br>
                                Vous pouvez ajouter vos propres livreurs dans
                                <strong>Équipe</strong>, ou contacter une entreprise
                                de livraison partenaire.
                            </div>
                            <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;justify-content:center">
                                <a href="{{ route('boutique.employees.index') }}" class="btn btn-sm">
                                    👥 Ajouter un livreur
                                </a>
                                <a href="{{ route('delivery.companies.index') }}" class="btn btn-primary btn-sm">
                                    🏢 Trouver une entreprise
                                </a>
                            </div>
                        </div>

                        @else
                        {{-- Pas de livreurs mais des entreprises disponibles --}}
                        <div style="padding:12px 18px;background:#fffbeb;border-bottom:1px solid #fde68a;display:flex;align-items:flex-start;gap:10px">
                            <span style="font-size:18px;flex-shrink:0">⚠️</span>
                            <div>
                                <div style="font-size:12.5px;color:#92400e;font-weight:700;margin-bottom:3px">
                                    Vous n'avez pas de livreurs
                                </div>
                                <div style="font-size:11.5px;color:#b45309;line-height:1.55">
                                    Contactez une entreprise partenaire ci-dessous pour organiser vos livraisons.
                                    Cliquez sur <strong>💬 Contacter</strong> pour ouvrir une discussion.
                                </div>
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


            {{-- ================================================================
                 ETAPE 2 — PRODUITS A RISQUE (pleine largeur)
                 Top clients déplacé dans la page /boutique/clients
                 ================================================================ --}}
            <div class="card" style="margin-bottom:22px">
                <div class="card-hd">
                    <span class="card-title">⚠️ Produits à risque — 0 vente ce mois</span>
                    <a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">
                        Gérer les produits →
                    </a>
                </div>

                @if($produitsRisque->isEmpty())
                    {{-- Bonne nouvelle : tous les produits se vendent --}}
                    <div class="risk-empty">
                        <span class="ico">🎉</span>
                        Tous vos produits ont été vendus ce mois !
                    </div>
                @else
                {{-- Grille horizontale : jusqu'à 5 produits côte à côte --}}
                <div style="display:flex;flex-wrap:wrap;gap:0;padding:0">
                    @foreach($produitsRisque as $product)
                    <div class="risk-row" style="flex:1;min-width:180px;border-right:1px solid #f3f6f4;border-bottom:none">
                        {{-- Image ou placeholder --}}
                        @if(!empty($product->image))
                            <img src="{{ asset('storage/'.$product->image) }}"
                                 alt="{{ $product->name }}" class="risk-img">
                        @else
                            <div class="risk-img-placeholder">🏷️</div>
                        @endif
                        {{-- Nom et prix --}}
                        <div class="risk-info">
                            <div class="risk-name" title="{{ $product->name }}">
                                {{ Str::limit($product->name, 20) }}
                            </div>
                            <div class="risk-meta">
                                {{ $product->price ? number_format($product->price,0,',',' ').' '.$devise : 'Prix non défini' }}
                            </div>
                        </div>
                        <span class="risk-badge">0 vente</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            {{-- /ETAPE 2 — PRODUITS A RISQUE --}}


            {{-- ================================================================
                 ETAPE 2 — BLOC C : SÉLECTEUR DE PÉRIODE
                 Remplace le graphique 6 mois statique.
                 L'utilisateur clique sur une période → les stats et le
                 mini-graphique se mettent à jour via AJAX sans rechargement.
                 Périodes : Hier · Aujourd'hui · 7j · 30j · Ce mois ·
                            Mois dernier · Cette année · Année dernière
                 ================================================================ --}}
            <div class="card period-card" id="periodCard">
                <div class="card-hd">
                    <span class="card-title">📅 Analyse par période</span>
                    {{-- Label de la période active --}}
                    <span class="period-label" id="periodLabel">
                        Période : <strong>Ce mois</strong>
                    </span>
                </div>

                {{-- ── Boutons de sélection rapide ── --}}
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

                {{-- ── Loader (visible pendant le fetch) ── --}}
                <div class="period-loading" id="periodLoading">
                    <div class="spin"></div>
                    Chargement…
                </div>

                {{-- ── Stats de la période (mises à jour dynamiquement) ── --}}
                <div id="periodStatsWrap">
                    <div class="period-stats">
                        <div class="period-stat">
                            <div class="period-stat-lbl">Chiffre d'affaires</div>
                            <div class="period-stat-val" id="pCA">—</div>
                            <div class="period-stat-sub">{{ $devise }}</div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Commandes</div>
                            <div class="period-stat-val" id="pCMD">—</div>
                            <div class="period-stat-sub">commandes</div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Panier moyen</div>
                            <div class="period-stat-val" id="pPANIER">—</div>
                            <div class="period-stat-sub">{{ $devise }} / cmd</div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Taux livraison</div>
                            <div class="period-stat-val" id="pTAUX">—</div>
                            <div class="period-stat-sub">% livrées</div>
                        </div>
                    </div>

                    {{-- ── Mini graphique barres ── --}}
                    <div class="period-chart">
                        <div class="period-bars" id="periodBars">
                            {{-- Rempli dynamiquement par JS --}}
                        </div>
                        <div class="period-bar-labels" id="periodLabels">
                            {{-- Rempli dynamiquement par JS --}}
                        </div>
                    </div>
                </div>
            </div>
            {{-- /ETAPE 2 — BLOC C --}}


        </div>{{-- /content --}}
    </main>
</div>{{-- /dash-wrap --}}

@push('scripts')
<script>
/* ═══════════════════════════════════════════════════════════
   ACCORDÉON SOUS-MENUS SIDEBAR
   - Un seul groupe ouvert à la fois
   - Scroll automatique vers Support après ouverture
     pour que l'item ne soit jamais caché sous le footer
   ═══════════════════════════════════════════════════════════ */
function toggleGroup(btn) {
    const sub    = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');

    /* Fermer tous les groupes */
    document.querySelectorAll('.sb-sub.open').forEach(s => {
        s.classList.remove('open');
        s.previousElementSibling?.classList.remove('open');
    });

    if (!isOpen) {
        sub.classList.add('open');
        btn.classList.add('open');

        /* Après l'animation (200ms), scroller la sidebar
           pour que Support soit visible en bas */
        const sidebar = document.getElementById('sidebar');
        setTimeout(() => {
            const support = sidebar?.querySelector('a[href*="support"]');
            if (support && sidebar) {
                support.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }, 220);
    }

    /* Mettre à jour l'indicateur de scroll */
    setTimeout(() => {
        const sidebar    = document.getElementById('sidebar');
        const scrollHint = document.getElementById('sbScrollHint');
        if (!sidebar || !scrollHint) return;
        const atBottom    = sidebar.scrollTop + sidebar.clientHeight >= sidebar.scrollHeight - 16;
        const needsScroll = sidebar.scrollHeight > sidebar.clientHeight + 20;
        scrollHint.classList.toggle('hidden', atBottom || !needsScroll);
    }, 300);
}

/* Auto-ouvrir le groupe si un sous-item est actif au chargement */
document.querySelectorAll('.sb-sub .sb-item.active').forEach(item => {
    const sub = item.closest('.sb-sub');
    if (sub) {
        sub.classList.add('open');
        sub.previousElementSibling?.classList.add('open');
    }
});

document.addEventListener('DOMContentLoaded', () => {

    /* ── Sidebar mobile toggle ── */
    const sidebar    = document.getElementById('sidebar');
    const overlay    = document.getElementById('sbOverlay');
    const btnMenu    = document.getElementById('btnMenu');
    const scrollHint = document.getElementById('sbScrollHint');

    function openSidebar()  { sidebar.classList.add('open');  overlay.classList.add('open'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }

    btnMenu?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);

    /* Bouton ✕ dans la sidebar — ferme le menu sur mobile */
    document.getElementById('btnCloseSidebar')?.addEventListener('click', closeSidebar);

    /* ── Indicateur scroll sidebar ──────────────────────────────────
     * Masque les 3 points verts quand on est en bas ou pas besoin de scroll.
     * Le scroll se fait sur .sidebar entière (overflow-y: scroll).
     * ─────────────────────────────────────────────────────────── */
    function updateScrollHint() {
        if (!sidebar || !scrollHint) return;
        const atBottom    = sidebar.scrollTop + sidebar.clientHeight >= sidebar.scrollHeight - 16;
        const needsScroll = sidebar.scrollHeight > sidebar.clientHeight + 20;
        scrollHint.classList.toggle('hidden', atBottom || !needsScroll);
    }
    sidebar?.addEventListener('scroll', updateScrollHint);
    window.addEventListener('resize', updateScrollHint);
    setTimeout(updateScrollHint, 300);

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

    /* ── Sparkline animation (top produits) ── */
    document.querySelectorAll('.sp-fill').forEach((el, i) => {
        setTimeout(() => {
            el.style.width = el.dataset.pct + '%';
        }, 100 + i * 90);
    });

    /* ════════════════════════════════════════════════════════════
       SÉLECTEUR DE PÉRIODE — Logique AJAX
       ════════════════════════════════════════════════════════════
       Au clic sur un bouton de période :
         1. On marque le bouton comme actif
         2. On affiche le loader
         3. On fetch la route boutique.period.stats
         4. On reçoit un JSON avec : ca, nb, panier, taux, points[]
         5. On met à jour les 4 KPI + le mini-graphique
       ════════════════════════════════════════════════════════════ */

    /* Labels lisibles pour chaque période */
    const periodLabels = {
        yesterday  : 'Hier',
        today      : "Aujourd'hui",
        '7days'    : '7 derniers jours',
        '30days'   : '30 derniers jours',
        this_month : 'Ce mois',
        last_month : 'Mois dernier',
        this_year  : 'Cette année',
        last_year  : 'Année dernière',
    };

    /* Devise de la boutique (définie à la création) */
    const DEVISE = '{{ $devise }}';

    /* Formater un nombre — exact en dessous de 1M, abrégé au-dessus
     * 389 500 → "389 500"  (pas "390k")
     * 1 500 000 → "1,5M"
     * Cohérent avec number_format PHP côté serveur */
    function fmt(n) {
        if (n >= 1_000_000) return (n / 1_000_000).toFixed(2).replace(/\.?0+$/, '') + 'M';
        return Math.round(n).toLocaleString('fr-FR');
    }

    /* Dessiner le mini-graphique barres */
    function drawBars(points) {
        const barsEl   = document.getElementById('periodBars');
        const labelsEl = document.getElementById('periodLabels');
        if (!barsEl || !labelsEl) return;

        const max = Math.max(...points.map(p => p.ca), 1);

        barsEl.innerHTML   = points.map(p => {
            const h   = p.ca > 0 ? Math.max(Math.round((p.ca / max) * 100), 3) : 2;
            const tip = fmt(p.ca) + ' ' + DEVISE + ' · ' + p.nb + ' cmd';
            return `<div class="period-bar-wrap">
                <div class="period-bar ${p.ca === 0 ? 'empty' : ''}"
                     style="height:0%"
                     data-h="${h}"
                     data-tip="${tip}">
                </div>
            </div>`;
        }).join('');

        labelsEl.innerHTML = points.map(p =>
            `<div class="period-bar-lbl">${p.label}</div>`
        ).join('');

        /* Animer les barres */
        barsEl.querySelectorAll('.period-bar').forEach((bar, i) => {
            setTimeout(() => {
                bar.style.transition = 'height .4s cubic-bezier(.23,1,.32,1)';
                bar.style.height = bar.dataset.h + '%';
            }, i * 30);
        });
    }

    /* Charger les données d'une période via AJAX */
    async function loadPeriod(period) {
        const loading    = document.getElementById('periodLoading');
        const statsWrap  = document.getElementById('periodStatsWrap');
        const labelEl    = document.getElementById('periodLabel');

        /* Mettre à jour le label */
        if (labelEl) {
            labelEl.innerHTML = 'Période : <strong>' + (periodLabels[period] || period) + '</strong>';
        }

        /* Afficher loader, cacher stats */
        loading.classList.add('show');
        statsWrap.style.opacity = '.3';

        try {
            const res  = await fetch(`{{ route('boutique.period.stats') }}?period=${period}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            if (!res.ok) throw new Error('Erreur HTTP ' + res.status);
            const data = await res.json();

            /* Mettre à jour les 4 KPI */
            document.getElementById('pCA').textContent     = fmt(data.ca);
            document.getElementById('pCMD').textContent    = data.nb;
            document.getElementById('pPANIER').textContent = fmt(data.panier);
            document.getElementById('pTAUX').textContent   = data.taux + '%';

            /* Dessiner le graphique */
            drawBars(data.points);

        } catch (err) {
            console.error('Erreur chargement période:', err);
            document.getElementById('pCA').textContent = '—';
        } finally {
            loading.classList.remove('show');
            statsWrap.style.opacity = '1';
        }
    }

    /* Gestion des clics sur les boutons */
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            /* Retirer actif de tous, mettre sur celui cliqué */
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadPeriod(btn.dataset.period);
        });
    });

    /* Charger la période par défaut au démarrage */
    loadPeriod('this_month');

});
</script>
@endpush