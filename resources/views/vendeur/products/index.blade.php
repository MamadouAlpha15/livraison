{{--
    resources/views/vendeur/products/index.blade.php
    Variables : $products, $categories, $devise, $totalProducts, $activeProducts, $outOfStock
--}}
@extends('layouts.app')
@section('title', 'Mes Produits')
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
    --sb-act:     rgba(99,102,241,.52);
    --sb-hov:     rgba(255,255,255,.07);
    --sb-txt:     rgba(255,255,255,.62);
    --sb-txt-act: #fff;
    --bg:         #f8fafc; --surface:   #ffffff;
    --border:     #e2e8f0; --border-dk: #cbd5e1;
    --text:       #0f172a; --text-2:    #475569; --muted: #94a3b8;
    --danger:     #ef4444; --danger-lt: #fef2f2;
    --amber:      #f59e0b; --amber-lt:  #fffbeb;
    --blue:       #3b82f6; --blue-lt:   #eff6ff;
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
    background: linear-gradient(180deg, #0f0f59 0%, #0e0e16 40%, #10103a 100%);
    display: flex; flex-direction: column;
    position: fixed; top: 0; left: 0; bottom: 0;
    width: var(--sb-w);
    overflow-y: scroll;
    scrollbar-width: thin;
    scrollbar-color: rgba(99,102,241,.35) transparent;
    z-index: 40;
    border-right: 1px solid rgba(99,102,241,.15);
    box-shadow: 6px 0 30px rgba(0,0,0,.35);
}
.sidebar::-webkit-scrollbar       { width: 4px; }
.sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,.04); }
.sidebar::-webkit-scrollbar-thumb { background: rgba(99,102,241,.4); border-radius: 4px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(99,102,241,.7); }

.sb-brand { padding: 18px 16px 14px; border-bottom: 1px solid var(--sb-border); flex-shrink: 0; position: relative; }
.sb-close {
    display: none; position: absolute; top: 14px; right: 12px;
    width: 30px; height: 30px; border-radius: 8px;
    background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.10);
    color: rgba(255,255,255,.6); font-size: 18px; line-height: 1; cursor: pointer;
    align-items: center; justify-content: center; transition: background .15s, color .15s; flex-shrink: 0;
}
.sb-close:hover { background: rgba(239,68,68,.18); border-color: rgba(239,68,68,.3); color: #fca5a5; }
@media (max-width: 900px) { .sb-close { display: flex; } }

.sb-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.sb-logo-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
.sb-shop-name { font-size: 14px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 10.5px; color: var(--sb-txt); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: var(--brand); flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px var(--brand); }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }

.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; overflow: visible; }
.sb-section { font-size: 9px; text-transform: uppercase; letter-spacing: 1.4px; color: rgba(255,255,255,.35); padding: 16px 10px 5px; font-weight: 700; }
.sb-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); font-size: 13px; font-weight: 500; color: var(--sb-txt); text-decoration: none; transition: background .15s, color .15s; position: relative; }
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.85); }
.sb-item.active { background: var(--sb-act); color: var(--sb-txt-act); box-shadow: 0 2px 12px rgba(99,102,241,.25); }
.sb-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: #a5b4fc; border-radius: 0 3px 3px 0; box-shadow: 2px 0 8px rgba(165,180,252,.5); }
.sb-item .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); transition: background .15s; }
.sb-item:hover .ico { background: rgba(255,255,255,.09); }
.sb-item.active .ico { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.2); }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); min-width: 20px; text-align: center; }
.sb-badge.warn { background: #f59e0b; }

/* Accordéon */
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); font-size: 13px; font-weight: 500; color: var(--sb-txt); cursor: pointer; transition: background .15s, color .15s; user-select: none; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); }
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.85); }
.sb-group-toggle.open  { color: rgba(255,255,255,.9); background: rgba(255,255,255,.04); }
.sb-group-toggle .ico  { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.25); transition: transform .2s; flex-shrink: 0; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.5); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.07); margin-top: 2px; margin-bottom: 4px; overflow: visible; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 12.5px; padding: 6px 10px; color: rgba(255,255,255,.45); }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.75); }
.sb-sub .sb-item.active { color: var(--sb-txt-act); background: var(--sb-act); }

/* Scroll hint */
.sb-scroll-hint { position: sticky; top: auto; bottom: 72px; width: 100%; height: 40px; background: linear-gradient(to bottom, transparent, rgba(17,17,24,.95)); pointer-events: none; z-index: 2; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 6px; transition: opacity .3s; margin-top: -40px; align-self: flex-end; }
.sb-scroll-hint.hidden { opacity: 0; pointer-events: none; }
.sb-scroll-hint-arrow { display: flex; flex-direction: column; align-items: center; gap: 2px; animation: bounceDown 1.5s ease-in-out infinite; }
.sb-scroll-hint-dot { width: 4px; height: 4px; border-radius: 50%; background: rgba(99,102,241,.6); }
.sb-scroll-hint-dot:nth-child(2) { opacity: .5; margin-top: -2px; }
.sb-scroll-hint-dot:nth-child(3) { opacity: .25; margin-top: -2px; }
@keyframes bounceDown { 0%,100%{transform:translateY(0)} 50%{transform:translateY(4px)} }

.sb-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; position: sticky; bottom: 0; background: linear-gradient(180deg, transparent 0%, #0b0b12 25%); z-index: 1; }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); text-decoration: none; transition: background .15s, border-color .15s; border: 1px solid transparent; }
.sb-user:hover { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.07); }
.sb-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #4338ca); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(99,102,241,.45), 0 2px 8px rgba(99,102,241,.3); letter-spacing: -.5px; }
.sb-uname { font-size: 12.5px; font-weight: 700; color: rgba(255,255,255,.9); }
.sb-urole { font-size: 10px; color: var(--sb-txt); margin-top: 1px; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.85); font-size: 12px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s, color .15s, border-color .15s; text-align: left; }
.sb-logout:hover { background: rgba(220,38,38,.18); border-color: rgba(220,38,38,.35); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; flex-shrink: 0; }

/* Overlay mobile */
.sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 39; }

/* ══ MAIN + TOPBAR ══ */
.main { display: flex; flex-direction: column; min-width: 0; }
.topbar {
    background: var(--surface); border-bottom: 1px solid var(--border);
    padding: 0 22px; height: var(--top-h);
    display: flex; align-items: center; gap: 12px;
    position: sticky; top: 0; z-index: 30; box-shadow: var(--shadow-sm);
}
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); font-size: 20px; }
.tb-info { flex: 1; min-width: 0; }
.tb-title { font-size: 14px; font-weight: 700; color: var(--text); }
.tb-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* ── Page intérieure ── */
.page-wrap { padding: 24px 22px 60px; }

/* ── Header ── */
.page-hd { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
.page-title { font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -.5px; margin: 0 0 3px; }
.page-sub   { font-size: 13px; color: var(--muted); margin: 0; }

/* ── Stats ── */
.stats-row { display: flex; gap: 12px; margin-bottom: 22px; flex-wrap: wrap; }
.stat-card {
    background: var(--surface); border: 1px solid var(--border);
    border-left: 3px solid var(--sc-color, var(--brand));
    border-radius: var(--r-sm); padding: 12px 18px;
    display: flex; align-items: center; gap: 12px;
    box-shadow: var(--shadow-sm); min-width: 150px; flex: 1;
}
.stat-ico { font-size: 22px; flex-shrink: 0; }
.stat-val { font-size: 24px; font-weight: 800; font-family: var(--mono); color: var(--text); line-height: 1; }
.stat-lbl { font-size: 11px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }

/* ── Filtres ── */
.filters-bar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 14px 18px;
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    margin-bottom: 20px; box-shadow: var(--shadow-sm);
}
.filter-input, .filter-select {
    padding: 8px 12px; border: 1.5px solid var(--border);
    border-radius: var(--r-sm); font-size: 12.5px; font-family: var(--font);
    color: var(--text); background: var(--bg); outline: none;
    transition: border-color .15s;
}
.filter-input:focus, .filter-select:focus { border-color: var(--brand); }
.filter-input { flex: 1; min-width: 180px; }
.filter-select { min-width: 140px; }
.filter-btn {
    padding: 8px 16px; border-radius: var(--r-sm);
    font-size: 12.5px; font-weight: 600; font-family: var(--font);
    border: 1.5px solid var(--border-dk); background: var(--surface);
    color: var(--text-2); cursor: pointer; white-space: nowrap;
    transition: all .15s; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
}
.filter-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-mlt); }
.filter-btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); }
.filter-btn-primary:hover { background: var(--brand-dk); color: #fff; }
.filter-results { font-size: 12px; color: var(--muted); margin-left: auto; white-space: nowrap; }

/* ── Flash ── */
.flash { padding: 11px 16px; border-radius: var(--r-sm); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
.flash-success { background: #eef2ff; border-color: #a5b4fc; color: #3730a3; }
.flash-danger  { background: var(--danger-lt); border-color: #fca5a5; color: #991b1b; }

/* ── Grille produits ── */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

/* ── Card produit ── */
.product-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .2s, transform .2s;
    display: flex; flex-direction: column;
    position: relative;
}
.product-card:hover {
    box-shadow: var(--shadow);
    transform: translateY(-3px);
}

/* Image */
.product-img-wrap {
    position: relative; overflow: hidden;
    height: 190px; background: #f3f6f4;
    flex-shrink: 0;
}
.product-img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s ease;
}
.product-card:hover .product-img { transform: scale(1.06); }
.product-img-placeholder {
    width: 100%; height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 6px; background: linear-gradient(135deg, #f3f6f4, #eef1f0);
    color: var(--muted);
}
.product-img-placeholder span { font-size: 36px; opacity: .4; }
.product-img-placeholder p { font-size: 11px; font-weight: 600; margin: 0; }

/* Badges sur l'image */
.product-badges {
    position: absolute; top: 10px; left: 10px;
    display: flex; flex-direction: column; gap: 4px;
}
.badge {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10px; font-weight: 700;
    padding: 3px 8px; border-radius: 20px; white-space: nowrap;
}
.badge-active   { background: #e0e7ff; color: #3730a3; }
.badge-inactive { background: #fee2e2; color: #991b1b; }
.badge-featured { background: #fef3c7; color: #92400e; }
.badge-promo    { background: #fecaca; color: #7f1d1d; }
.badge-stock0   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

/* Toggle actif (coin haut droit) */
.toggle-wrap {
    position: absolute; top: 10px; right: 10px;
}
.toggle-switch {
    width: 36px; height: 20px; border-radius: 20px;
    background: #d1d5db; border: none; cursor: pointer;
    position: relative; transition: background .2s;
    outline: none;
}
.toggle-switch.on { background: var(--brand); }
.toggle-switch::after {
    content: '';
    position: absolute; top: 2px; left: 2px;
    width: 16px; height: 16px; border-radius: 50%;
    background: #fff; transition: transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.toggle-switch.on::after { transform: translateX(16px); }

/* Corps de la card */
.product-body {
    padding: 14px 14px 12px;
    flex: 1; display: flex; flex-direction: column; gap: 8px;
}
.product-category {
    font-size: 10px; font-weight: 700; color: var(--brand);
    text-transform: uppercase; letter-spacing: .5px;
}
.product-name {
    font-size: 14px; font-weight: 700; color: var(--text);
    line-height: 1.3; margin: 0;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.product-desc {
    font-size: 11.5px; color: var(--muted); line-height: 1.5; margin: 0;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}

/* Prix */
.product-price-row { display: flex; align-items: baseline; gap: 6px; }
.product-price {
    font-size: 17px; font-weight: 800; font-family: var(--mono);
    color: var(--brand); letter-spacing: -.5px;
}
.product-price-orig {
    font-size: 12px; font-family: var(--mono);
    color: var(--muted); text-decoration: line-through;
}
.product-price-unit { font-size: 10px; color: var(--muted); font-weight: 600; }

/* Stock + Prep time */
.product-meta-row {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.product-meta-chip {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 600; color: var(--text-2);
    background: var(--bg); border: 1px solid var(--border);
    padding: 3px 8px; border-radius: 6px;
}
.product-meta-chip.danger { color: var(--danger); background: var(--danger-lt); border-color: #fca5a5; }
.product-meta-chip.amber  { color: #92400e; background: var(--amber-lt); border-color: #fde68a; }

/* Footer actions */
.product-footer {
    padding: 10px 14px;
    border-top: 1px solid #f3f6f4;
    display: flex; align-items: center; gap: 6px;
}
.action-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 6px 10px; border-radius: var(--r-sm);
    font-size: 11.5px; font-weight: 600; font-family: var(--font);
    border: 1px solid var(--border-dk); background: var(--surface);
    color: var(--text-2); cursor: pointer; text-decoration: none;
    transition: all .15s; white-space: nowrap;
    flex: 1; justify-content: center;
}
.action-btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.action-btn.edit  { border-color: #93c5fd; color: #1e40af; background: #eff6ff; }
.action-btn.edit:hover { background: #dbeafe; }
.action-btn.dupe  { border-color: #d8b4fe; color: #6d28d9; background: #f5f3ff; }
.action-btn.dupe:hover { background: #ede9fe; }
.action-btn.del   { border-color: #fca5a5; color: #991b1b; background: var(--danger-lt); }
.action-btn.del:hover { background: #fee2e2; }

/* Empty state */
.empty-state { padding: 64px 20px; text-align: center; grid-column: 1/-1; }
.empty-ico { font-size: 56px; display: block; margin: 0 auto 16px; opacity: .3; }
.empty-title { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
.empty-sub   { font-size: 14px; color: var(--muted); margin-bottom: 20px; }

/* Modal suppression */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 200;
    align-items: center; justify-content: center; padding: 20px;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: var(--surface); border-radius: var(--r);
    padding: 28px 26px; max-width: 400px; width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: modalIn .2s ease;
}
@keyframes modalIn { from { opacity:0; transform:scale(.95) translateY(-8px); } to { opacity:1; transform:scale(1) translateY(0); } }
.modal-icon  { font-size: 38px; text-align: center; margin-bottom: 12px; }
.modal-title { font-size: 16px; font-weight: 700; color: var(--text); text-align: center; margin-bottom: 8px; }
.modal-sub   { font-size: 13px; color: var(--muted); text-align: center; margin-bottom: 22px; line-height: 1.6; }
.modal-sub strong { color: var(--text); }
.modal-actions { display: flex; gap: 10px; }
.modal-actions .action-btn { font-size: 13px; padding: 9px 14px; }
.btn-del-confirm { background: #dc2626; border-color: #b91c1c; color: #fff; font-weight: 700; }
.btn-del-confirm:hover { background: #b91c1c; color: #fff; }

/* Pagination */
.pagination-wrap { display: flex; justify-content: center; padding-top: 8px; }

/* ══════════════════════════════════════════════
   RESPONSIVE ULTRA
   5 breakpoints pour couvrir tous les appareils
   ══════════════════════════════════════════════ */

/* ── Tablette large (900px) — sidebar cachée ── */
@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .products-grid { grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .page-wrap { padding: 20px 16px 50px; }
}

/* ── Tablette (700px) ── */
@media (max-width: 700px) {
    .products-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .filters-bar { gap: 8px; padding: 12px 14px; }
    .filter-input { min-width: 0; flex: 1 1 100%; order: -1; }
    .filter-select { flex: 1; min-width: 0; }
    .filter-results { width: 100%; text-align: center; margin-left: 0; order: 10; }
    .stats-row { gap: 8px; }
    .stat-card { padding: 10px 14px; }
    .stat-val { font-size: 20px; }
}

/* ── Mobile (520px) ── */
@media (max-width: 520px) {
    .page-wrap { padding: 12px 10px 40px; }
    .page-hd { gap: 10px; }
    .page-title { font-size: 18px; }
    .products-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .product-img-wrap { height: 130px; }
    .product-body { padding: 8px 9px 6px; gap: 5px; }
    .product-name { font-size: 12.5px; }
    .product-price { font-size: 14px; }
    .product-desc { display: none; } /* caché sur mobile pour gagner de la place */
    .product-meta-row { gap: 4px; }
    .product-meta-chip { font-size: 9.5px; padding: 2px 6px; }
    .product-footer { padding: 7px 9px; gap: 4px; }
    .action-btn { padding: 5px 6px; font-size: 10.5px; gap: 2px; }
    .stats-row { gap: 6px; }
    .stat-card { padding: 8px 10px; min-width: 0; }
    .stat-val { font-size: 18px; }
    .stat-lbl { font-size: 9px; }
    .stat-ico { font-size: 18px; }
    /* Masquer le bouton "Dupliquer" sur mobile — trop petit */
    .action-btn.dupe { display: none; }
    /* Ajouter produit en pleine largeur */
    .page-hd { flex-direction: column; align-items: stretch; }
    .page-hd > a { text-align: center; justify-content: center; }
    .filters-bar { flex-wrap: wrap; gap: 6px; padding: 10px 12px; }
    .filter-select { min-width: 0; flex: 1; font-size: 12px; }
    .filter-btn { font-size: 11.5px; padding: 7px 12px; }
}

/* ── Très petit mobile (380px) ── */
@media (max-width: 380px) {
    .products-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; }
    .product-img-wrap { height: 110px; }
    .product-body { padding: 6px 7px; gap: 4px; }
    .product-name { font-size: 11.5px; }
    .product-footer { flex-wrap: wrap; padding: 6px; }
    .action-btn.edit { flex: 1; }
    .action-btn.del  { flex: 0 0 auto; }
    .badge { font-size: 9px; padding: 2px 6px; }
}

/* ── Desktop large (1200px+) ── */
@media (min-width: 1200px) {
    .products-grid { grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); }
}
@media (min-width: 1500px) {
    .products-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
}
.gallery-chip {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 700; cursor: pointer;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1e40af; border: 1px solid #93c5fd;
    padding: 3px 8px; border-radius: 6px;
    transition: all .15s; user-select: none;
}
.gallery-chip:hover {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border-color: #60a5fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(59,130,246,.2);
}
.lb-overlay {
    display: none; position: fixed; inset: 0; z-index: 9000;
    background: rgba(0,0,0,.94);
    align-items: center; justify-content: center;
    flex-direction: column;
}
.lb-overlay.open { display: flex; }
.lb-header {
    position: absolute; top: 0; left: 0; right: 0;
    padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between;
    background: linear-gradient(to bottom, rgba(0,0,0,.7), transparent);
    z-index: 2;
}
.lb-product-name {
    font-size: 14px; font-weight: 700; color: #fff;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 280px;
}
.lb-photo-count { font-size: 12px; color: rgba(255,255,255,.5); font-family: var(--mono); }
.lb-close {
    width: 38px; height: 38px; border-radius: 50%;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
    color: #fff; font-size: 18px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s;
}
.lb-close:hover { background: rgba(255,255,255,.22); }
.lb-main {
    flex: 1; width: 100%; display: flex;
    align-items: center; justify-content: center;
    position: relative; overflow: hidden;
    padding: 62px 62px 10px;
}
.lb-img {
    max-width: 100%; max-height: 100%;
    object-fit: contain; border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0,0,0,.5);
    user-select: none; display: block;
}
.lb-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 46px; height: 46px; border-radius: 50%;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
    color: #fff; font-size: 22px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s, transform .15s; z-index: 2;
}
.lb-nav:hover { background: rgba(255,255,255,.22); transform: translateY(-50%) scale(1.08); }
.lb-prev { left: 12px; }
.lb-next { right: 12px; }
.lb-nav.hidden { display: none; }
.lb-thumbs {
    width: 100%; padding: 10px 16px 16px;
    display: flex; align-items: center; justify-content: center;
    gap: 8px; overflow-x: auto; flex-shrink: 0;
    background: linear-gradient(to top, rgba(0,0,0,.6), transparent);
    scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.2) transparent;
}
.lb-thumb {
    width: 54px; height: 54px; border-radius: 8px;
    object-fit: cover; cursor: pointer; flex-shrink: 0;
    border: 2.5px solid transparent;
    opacity: .5; transition: all .2s;
}
.lb-thumb:hover { opacity: .85; border-color: rgba(255,255,255,.5); }
.lb-thumb.active {
    opacity: 1; border-color: var(--brand);
    box-shadow: 0 0 0 2px rgba(99,102,241,.4);
    transform: scale(1.1);
}
.lb-counter-mobile {
    display: none; position: absolute; bottom: 80px; left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,.5); color: rgba(255,255,255,.8);
    font-size: 11px; font-family: var(--mono); font-weight: 600;
    padding: 4px 12px; border-radius: 20px; white-space: nowrap; pointer-events: none;
}
@media (max-width: 600px) {
    .lb-main { padding: 56px 8px 6px; }
    .lb-nav { width: 38px; height: 38px; font-size: 17px; }
    .lb-prev { left: 4px; }
    .lb-next { right: 4px; }
    .lb-thumb { width: 44px; height: 44px; border-radius: 6px; }
    .lb-product-name { max-width: 160px; font-size: 12.5px; }
    .lb-header { padding: 10px 12px; }
    .lb-counter-mobile { display: block; }
}
</style>
@endpush

@section('content')

@php
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $shop     = auth()->user()->shop ?? null;
    $pendingOrdersCount = $shop
        ? $shop->orders()->whereIn('status',['pending','en attente','en_attente','confirmée','processing'])->count()
        : 0;
@endphp

{{-- Modal suppression --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">🗑️</div>
        <div class="modal-title">Supprimer ce produit ?</div>
        <div class="modal-sub">
            <strong id="modalProductName"></strong><br>
            Cette action est <strong>irréversible</strong>. Le produit et ses images seront supprimés.
        </div>
        <div class="modal-actions">
            <button type="button" class="action-btn" onclick="closeDeleteModal()" style="flex:1">← Retour</button>
            <form id="deleteForm" method="POST" style="flex:1;display:contents">
                @csrf @method('DELETE')
                <button type="submit" class="action-btn btn-del-confirm" style="flex:1">🗑️ Supprimer</button>
            </form>
        </div>
    </div>
</div>

<div class="dash-wrap">

{{-- ══════ SIDEBAR ══════ --}}
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
        <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px">
            <span class="ico">⊞</span> Tableau de bord
        </a>
        <div class="sb-section">Boutique</div>
        <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">💬</span> Messages</a>
        <a href="{{ route('boutique.orders.index') }}" class="sb-item">
            <span class="ico">📦</span> Commandes
            @if($pendingOrdersCount > 0)<span class="sb-badge">{{ $pendingOrdersCount }}</span>@endif
        </a>
        <a href="{{ route('products.index') }}" class="sb-item active">
            <span class="ico">🏷️</span> Produits
        </a>
        <a href="{{ route('boutique.clients.index') }}" class="sb-item">
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
                <span class="ico">💰</span> Finances & Rapports <span class="sb-arrow">▶</span>
            </button>
            <div class="sb-sub">
                <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">💳</span> Paiements</a>
                <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">📊</span> Commissions</a>
                <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">📋</span> Rapports</a>
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
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-logout"><span class="ico">⎋</span> Se déconnecter</button>
        </form>
    </div>
</aside>

<div class="sb-overlay" id="sbOverlay"></div>

{{-- ══════ MAIN ══════ --}}
<main class="main">

    {{-- Topbar --}}
    <div class="topbar">
        <button class="btn-hamburger" id="btnMenu" aria-label="Menu">☰</button>
        <div class="tb-info">
            <div class="tb-title">🏷️ Catalogue produits</div>
            <div class="tb-sub">{{ $shop->name ?? 'Boutique' }} · {{ $totalProducts }} produit(s)</div>
        </div>
        <a href="{{ route('products.create') }}" style="padding:7px 14px;border-radius:var(--r-sm);font-size:12px;font-weight:700;background:var(--brand);color:#fff;border:1px solid var(--brand-dk);text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background .15s">
            ➕ Ajouter
        </a>
    </div>

{{-- ══════════════════════════════════════════════════════════════
     LIGHTBOX GALERIE PHOTOS
     Ouvert par openGallery(photos[], nom, imagePrincipale)
     Navigation : flèches, miniatures, clavier (←→ Esc)
══════════════════════════════════════════════════════════════ --}}
<div class="lb-overlay" id="lbOverlay" role="dialog" aria-modal="true">

    {{-- Header : nom produit + compteur + fermeture --}}
    <div class="lb-header">
        <div class="lb-product-info">
            <span id="lbProductName" class="lb-product-name"></span>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <span id="lbPhotoCount" class="lb-photo-count"></span>
            <button class="lb-close" onclick="closeLightbox()" title="Fermer (Échap)">✕</button>
        </div>
    </div>

    {{-- Zone image principale --}}
    <div class="lb-main">
        <button class="lb-nav lb-prev" id="lbPrev" onclick="lbNavigate(-1)" title="Précédente">‹</button>
        <img class="lb-img" id="lbMainImg" src="" alt="">
        <button class="lb-nav lb-next" id="lbNext" onclick="lbNavigate(1)"  title="Suivante">›</button>
        <div class="lb-counter-mobile" id="lbCounterMobile"></div>
    </div>

    {{-- Barre miniatures --}}
    <div class="lb-thumbs" id="lbThumbs"></div>

</div>

<div class="page-wrap">

    {{-- Header --}}
    <div class="page-hd">
        <div>
            <h1 class="page-title">🏷️ Catalogue produits</h1>
            <p class="page-sub">Gérez vos produits — modification, activation et suivi du stock.</p>
        </div>
        <a href="{{ route('products.create') }}" class="filter-btn filter-btn-primary" style="font-size:13px;padding:10px 20px">
            ➕ Ajouter un produit
        </a>
    </div>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-card" style="--sc-color:#3b82f6">
            <span class="stat-ico">📦</span>
            <div>
                <div class="stat-val">{{ $totalProducts }}</div>
                <div class="stat-lbl">Total produits</div>
            </div>
        </div>
        <div class="stat-card" style="--sc-color:#6366f1">
            <span class="stat-ico">✅</span>
            <div>
                {{-- id="statActiveCount" utilisé par le JS toggle pour mise à jour en temps réel --}}
                <div class="stat-val" id="statActiveCount">{{ $activeProducts }}</div>
                <div class="stat-lbl">Actifs</div>
            </div>
        </div>
        <div class="stat-card" style="--sc-color:#ef4444">
            <span class="stat-ico">⚠️</span>
            <div>
                <div class="stat-val">{{ $outOfStock }}</div>
                <div class="stat-lbl">Rupture stock</div>
            </div>
        </div>
    </div>

    {{-- Flash --}}
    @foreach(['success','danger','error'] as $t)
        @if(session($t))
        <div class="flash flash-{{ $t === 'error' ? 'danger' : $t }}">
            <span>{{ $t === 'success' ? '✓' : '⚠' }}</span>
            {{ session($t) }}
        </div>
        @endif
    @endforeach

    {{-- Filtres --}}
    {{-- ── FILTRES ──
         Méthode GET — les valeurs restent dans l'URL pour
         que la pagination conserve les filtres actifs.
    ── --}}
    {{-- ── FILTRES ──
         • Les selects (catégorie, statut) soumettent le formulaire dès la sélection
         • La recherche texte soumet après 500ms de frappe (debounce)
         • Bouton "Filtrer" reste disponible pour confirmation manuelle
    ── --}}
    <form action="{{ route('products.index') }}" method="GET" id="filterForm">
        <div class="filters-bar">

            {{-- Recherche texte (soumission auto après frappe) --}}
            <input type="text"
                   name="search"
                   id="searchInput"
                   class="filter-input"
                   placeholder="🔍  Rechercher un produit…"
                   value="{{ request('search', '') }}"
                   autocomplete="off">

            {{-- Filtre catégorie — soumet automatiquement au changement --}}
            <select name="category"
                    class="filter-select"
                    onchange="document.getElementById('filterForm').submit()">
                <option value="">Toutes les catégories</option>

                {{-- Catégories prédéfinies ── --}}
                <optgroup label="── Catégories standards ──">
                @foreach($categories as $cat)
                <option value="{{ $cat }}"
                    {{ request('category') === $cat ? 'selected' : '' }}>
                    {{ $cat }}
                </option>
                @endforeach
                </optgroup>

                {{-- Catégories personnalisées saisies manuellement ── --}}
                @if(!empty($customCats))
                <optgroup label="── Mes catégories ──">
                    @foreach($customCats as $cat)
                    <option value="{{ $cat }}"
                        {{ request('category') === $cat ? 'selected' : '' }}>
                        ✏️ {{ $cat }}
                    </option>
                    @endforeach
                </optgroup>
                @endif

            </select>

            {{-- Filtre statut — soumet automatiquement au changement --}}
            <select name="status"
                    class="filter-select"
                    style="min-width:130px"
                    onchange="document.getElementById('filterForm').submit()">
                <option value="">Tous statuts</option>
                <option value="active"
                    {{ request('status') === 'active'   ? 'selected' : '' }}>✅ Actifs</option>
                <option value="inactive"
                    {{ request('status') === 'inactive' ? 'selected' : '' }}>❌ Inactifs</option>
            </select>

            <button type="submit" class="filter-btn filter-btn-primary">
                🔍 Filtrer
            </button>

            @if(request()->hasAny(['search','category','status']))
            <a href="{{ route('products.index') }}" class="filter-btn">
                ✕ Effacer
            </a>
            @endif

            <span class="filter-results">
                {{ $products->total() }} produit(s)
            </span>
        </div>
    </form>

    {{-- Grille produits --}}
    <div class="products-grid">

        @forelse($products as $product)
        @php
            $gallery   = $product->gallery ? json_decode($product->gallery, true) : [];
            $hasPromo  = $product->original_price && $product->original_price > $product->price;
            /* stock peut être 0 (rupture) ou null (colonne absente) */
            $stockVal  = $product->stock ?? null;
            $stockLow  = $stockVal !== null && $stockVal > 0 && $stockVal <= 5;
            $stockOut  = $stockVal !== null && $stockVal <= 0;
        @endphp
        <div class="product-card" id="card-{{ $product->id }}">

            {{-- Image --}}
            <div class="product-img-wrap">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}"
                         class="product-img" alt="{{ $product->name }}">
                @else
                    <div class="product-img-placeholder">
                        <span>🏷️</span>
                        <p>Aucune image</p>
                    </div>
                @endif

                {{-- Badges --}}
                <div class="product-badges">
                    @if($product->is_active)
                        <span class="badge badge-active">● Actif</span>
                    @else
                        <span class="badge badge-inactive">● Inactif</span>
                    @endif
                    @if(!empty($product->is_featured))
                        <span class="badge badge-featured">⭐ Vedette</span>
                    @endif
                    @if($hasPromo)
                        @php $remise = round((1 - $product->price / $product->original_price) * 100); @endphp
                        <span class="badge badge-promo">-{{ $remise }}%</span>
                    @endif
                    @if($stockOut)
                        <span class="badge badge-stock0">Rupture</span>
                    @endif
                </div>

                {{-- Toggle actif/inactif --}}
                <div class="toggle-wrap">
                    <button type="button"
                            class="toggle-switch {{ $product->is_active ? 'on' : '' }}"
                            data-id="{{ $product->id }}"
                            data-url="{{ route('products.toggle', $product) }}"
                            title="{{ $product->is_active ? 'Désactiver' : 'Activer' }}">
                    </button>
                </div>
            </div>

            {{-- Corps --}}
            <div class="product-body">
                @if($product->category)
                <div class="product-category">{{ $product->category }}</div>
                @endif

                <h3 class="product-name">{{ $product->name }}</h3>

                @if($product->description)
                <p class="product-desc">{{ $product->description }}</p>
                @endif

                {{-- Prix --}}
                <div class="product-price-row">
                    <span class="product-price">
                        {{ number_format($product->price, 0, ',', ' ') }}
                        <span style="font-size:10px;font-weight:600;color:var(--muted)">{{ $devise }}</span>
                    </span>
                    @if($hasPromo)
                    <span class="product-price-orig">
                        {{ number_format($product->original_price, 0, ',', ' ') }}
                    </span>
                    @endif
                    @if($product->unit ?? false)
                    <span class="product-price-unit">/ {{ $product->unit }}</span>
                    @endif
                </div>

                {{-- Meta : stock + temps de préparation --}}
                <div class="product-meta-row">
                    {{-- Stock : affiché même si 0 (rupture) ── --}}
                    @if($product->stock !== null)
                    <span class="product-meta-chip {{ $stockOut ? 'danger' : ($stockLow ? 'amber' : '') }}">
                        📦 {{ $product->stock }} en stock
                    </span>
                    @endif
                    {{-- Temps préparation restaurant ── --}}
                    @if($product->preparation_time)
                    <span class="product-meta-chip">
                        ⏱ {{ $product->preparation_time }}min
                    </span>
                    @endif
                    @if(count($gallery) > 0)
                    {{-- Chip cliquable → ouvre le lightbox avec toutes les photos --}}
                    @php
                        /* Construire le tableau JSON des photos :
                           photo principale + galerie supplémentaire */
                        $allPhotos = [];
                        if ($product->image) {
                            $allPhotos[] = asset('storage/' . $product->image);
                        }
                        foreach ($gallery as $g) {
                            $allPhotos[] = asset('storage/' . $g);
                        }
                        $photosJson = json_encode($allPhotos);
                        $totalPhotos = count($allPhotos);
                    @endphp
                    <span class="gallery-chip"
                          onclick="openGallery({{ $photosJson }}, {{ json_encode($product->name) }}, {{ $totalPhotos }})"
                          title="Voir les {{ $totalPhotos }} photos">
                        🖼 {{ $totalPhotos }} photo{{ $totalPhotos > 1 ? 's' : '' }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Footer actions --}}
            <div class="product-footer">
                <a href="{{ route('products.edit', $product) }}" class="action-btn edit">✏️ Modifier</a>

                <form action="{{ route('products.duplicate', $product) }}" method="POST" style="flex:0.7">
                    @csrf
                    <button type="submit" class="action-btn dupe" style="width:100%">⧉ Dupliquer</button>
                </form>

                <button type="button" class="action-btn del"
                        onclick="openDeleteModal('{{ route('products.destroy', $product) }}', '{{ addslashes($product->name) }}')">
                    🗑️
                </button>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <span class="empty-ico">📭</span>
            <div class="empty-title">Aucun produit trouvé</div>
            <p class="empty-sub">
                @if(request()->hasAny(['search','category','status']))
                    Aucun produit ne correspond à vos filtres.
                @else
                    Ajoutez votre premier produit pour commencer à vendre.
                @endif
            </p>
            <a href="{{ route('products.create') }}" class="filter-btn filter-btn-primary" style="display:inline-flex">
                ➕ Ajouter un produit
            </a>
        </div>
        @endforelse

    </div>

    {{-- Pagination --}}
    <div class="pagination-wrap">
        {{ $products->links() }}
    </div>

</div>
</main>
</div>{{-- /dash-wrap --}}

@endsection

@push('scripts')
<script>
/* ══ SIDEBAR MOBILE ══ */
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

/* ══════════════════════════════════════════════════════════════
   LIGHTBOX GALERIE PHOTOS
   ══════════════════════════════════════════════════════════════ */

let lbPhotos  = [];   // tableau des URLs de photos
let lbCurrent = 0;    // index de la photo affichée
let lbName    = '';   // nom du produit

/**
 * Ouvrir le lightbox avec un tableau de photos
 * @param {string[]} photos   - URLs des photos (principale + galerie)
 * @param {string}   name     - Nom du produit
 * @param {number}   total    - Nombre total de photos
 */
function openGallery(photos, name, total) {
    lbPhotos  = photos;
    lbCurrent = 0;
    lbName    = name;

    document.getElementById('lbProductName').textContent = name;
    document.getElementById('lbOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';

    renderLbThumbs();
    showLbPhoto(0);
}

/** Afficher la photo à l'index donné */
function showLbPhoto(idx) {
    if (idx < 0 || idx >= lbPhotos.length) return;
    lbCurrent = idx;

    const img = document.getElementById('lbMainImg');
    img.style.animation = 'none';
    img.offsetHeight; // reflow
    img.style.animation = '';
    img.src = lbPhotos[idx];
    img.alt = lbName + ' — photo ' + (idx + 1);

    /* Compteurs */
    const countTxt = (idx + 1) + ' / ' + lbPhotos.length;
    document.getElementById('lbPhotoCount').textContent    = countTxt;
    document.getElementById('lbCounterMobile').textContent = countTxt;

    /* Flèches : masquer si première/dernière photo */
    document.getElementById('lbPrev').classList.toggle('hidden', idx === 0);
    document.getElementById('lbNext').classList.toggle('hidden', idx === lbPhotos.length - 1);

    /* Activer la miniature */
    document.querySelectorAll('.lb-thumb').forEach((t, i) => {
        t.classList.toggle('active', i === idx);
    });

    /* Scroll la miniature active dans la vue */
    const activThumb = document.querySelector('.lb-thumb.active');
    if (activThumb) {
        activThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
}

/** Naviguer de -1 ou +1 */
function lbNavigate(dir) {
    const newIdx = lbCurrent + dir;
    if (newIdx >= 0 && newIdx < lbPhotos.length) showLbPhoto(newIdx);
}

/** Construire la barre de miniatures */
function renderLbThumbs() {
    const bar = document.getElementById('lbThumbs');
    bar.innerHTML = lbPhotos.map((url, i) => `
        <img class="lb-thumb ${i === 0 ? 'active' : ''}"
             src="${url}" alt="Photo ${i + 1}"
             onclick="showLbPhoto(${i})"
             loading="lazy">
    `).join('');
}

/** Fermer le lightbox */
function closeLightbox() {
    document.getElementById('lbOverlay').classList.remove('open');
    document.body.style.overflow = '';
    lbPhotos  = [];
    lbCurrent = 0;
}

/* Clic sur l'overlay (hors image) → ferme */
document.getElementById('lbOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
});

/* Navigation clavier */
document.addEventListener('keydown', e => {
    if (!document.getElementById('lbOverlay').classList.contains('open')) return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  lbNavigate(-1);
    if (e.key === 'ArrowRight') lbNavigate(1);
});

/* Swipe tactile sur mobile */
let lbTouchStartX = 0;
document.getElementById('lbOverlay').addEventListener('touchstart', e => {
    lbTouchStartX = e.touches[0].clientX;
}, { passive: true });
document.getElementById('lbOverlay').addEventListener('touchend', e => {
    const dx = e.changedTouches[0].clientX - lbTouchStartX;
    if (Math.abs(dx) > 50) lbNavigate(dx < 0 ? 1 : -1);
}, { passive: true });

/* ── Debounce recherche texte (soumet après 500ms de frappe) ── */
let searchTimer = null;
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
    /* Soumettre immédiatement si on appuie sur Entrée */
    searchInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            clearTimeout(searchTimer);
            document.getElementById('filterForm').submit();
        }
    });
}

/* ── Modal suppression ── */
function openDeleteModal(url, name) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('modalProductName').textContent = name;
    document.getElementById('deleteModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });

/* ══════════════════════════════════════════════════════
   TOGGLE ACTIF / INACTIF — AJAX
   Met à jour en temps réel :
     1. Le switch visuel (vert / gris)
     2. Le badge Actif/Inactif sur l'image
     3. Le compteur "X Actifs" dans les stats en haut
   ══════════════════════════════════════════════════════ */

/* Compteur local du nombre de produits actifs sur cette page */
let activeCount = parseInt(document.getElementById('statActiveCount')?.textContent || '0');

document.querySelectorAll('.toggle-switch').forEach(btn => {
    btn.addEventListener('click', async () => {
        /* Désactiver pendant la requête pour éviter double-clic */
        btn.disabled = true;
        btn.style.opacity = '0.6';

        const url = btn.dataset.url;
        const wasActive = btn.classList.contains('on');

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            const isNowActive = data.active;

            /* ── 1. Mettre à jour le switch ── */
            btn.classList.toggle('on', isNowActive);
            btn.title = isNowActive ? 'Désactiver' : 'Activer';

            /* ── 2. Mettre à jour le badge sur la card ── */
            const card = btn.closest('.product-card');
            const badgeEl = card.querySelector('.badge-active, .badge-inactive');
            if (badgeEl) {
                badgeEl.className = `badge badge-${isNowActive ? 'active' : 'inactive'}`;
                badgeEl.textContent = isNowActive ? '● Actif' : '● Inactif';
            }

            /* ── 3. Mettre à jour le compteur "X Actifs" ── */
            const countEl = document.getElementById('statActiveCount');
            if (countEl) {
                if (isNowActive && !wasActive) activeCount++;
                if (!isNowActive && wasActive) activeCount--;
                countEl.textContent = activeCount;
            }

            /* ── 4. Feedback visuel flash sur la card ── */
            card.style.transition = 'outline .15s';
            card.style.outline = `2px solid ${isNowActive ? 'var(--brand)' : '#ef4444'}`;
            setTimeout(() => { card.style.outline = 'none'; }, 600);

        } catch(err) {
            console.error('Toggle failed:', err);
            /* Annuler visuellement en cas d'erreur */
            btn.classList.toggle('on', wasActive);
            alert('Erreur lors de la mise à jour. Veuillez réessayer.');
        } finally {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    });
});
</script>
@endpush