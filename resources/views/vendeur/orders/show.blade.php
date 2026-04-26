{{-- resources/views/vendeur/orders/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Commande #' . $order->id)
@php $bodyClass = 'is-dashboard'; @endphp



@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css"/>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; }

:root {
    --brand:    #6366f1; --brand-dk: #4f46e5; --brand-lt: #e0e7ff; --brand-mlt: #eef2ff;
    --bg:       #f1f5f9; --surface:  #ffffff;
    --border:   #e2e8f0; --border-dk:#cbd5e1;
    --text:     #0f172a; --text-2:   #475569; --muted:    #94a3b8;
    --green:    #10b981; --green-lt: #ecfdf5; --green-dk: #065f46;
    --red:      #ef4444; --red-lt:   #fef2f2;
    --amber:    #f59e0b; --amber-lt: #fef3c7;
    --font:     'Plus Jakarta Sans', sans-serif;
    --mono:     'JetBrains Mono', monospace;
    --r:        16px; --r-sm: 10px; --r-xs: 7px;
    --shadow:   0 1px 3px rgba(0,0,0,.06), 0 4px 14px rgba(0,0,0,.06);
    --shadow-md:0 4px 24px rgba(0,0,0,.10);
    --sb-w:     232px; --top-h: 60px;
}

html, body { font-family: var(--font); background: var(--bg); color: var(--text); -webkit-font-smoothing: antialiased; }

/* ─── Layout ─── */
.dash-wrap { display: flex; min-height: 100vh; }
.main { margin-left: var(--sb-w); flex: 1; min-width: 0; display: flex; flex-direction: column; }

/* ─── Sidebar ─── */
.sidebar {
    background: linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    border-right: 1px solid rgba(99,102,241,.15);
    box-shadow: 6px 0 32px rgba(0,0,0,.4);
    display: flex; flex-direction: column;
    position: fixed; top: 0; left: 0; bottom: 0;
    width: var(--sb-w); overflow-y: scroll;
    scrollbar-width: thin; scrollbar-color: rgba(99,102,241,.35) transparent; z-index: 40;
}
.sidebar::-webkit-scrollbar { width: 3px; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(99,102,241,.35); border-radius: 3px; }
.sb-brand { padding: 18px 16px 14px; border-bottom: 1px solid rgba(255,255,255,.08); flex-shrink: 0; position: relative; }
.sb-close { display: none; position: absolute; top: 14px; right: 12px; width: 30px; height: 30px; border-radius: 8px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.6); font-size: 18px; cursor: pointer; align-items: center; justify-content: center; transition: background .15s; }
.sb-close:hover { background: rgba(239,68,68,.18); color: #fca5a5; }
@media (max-width: 900px) { .sb-close { display: flex; } }
.sb-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.sb-logo-icon { width: 36px; height: 36px; border-radius: 9px; overflow: hidden; flex-shrink: 0; }
.sb-shop-name { font-size: 14.5px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; letter-spacing: -.3px; color: #fff; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 10.5px; color: rgba(255,255,255,.55); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: #6ee7b7; flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px #6ee7b7; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }
.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; }
.sb-section { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.8px; color: rgba(255,255,255,.38); padding: 16px 10px 5px; font-weight: 800; }
.sb-item { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-xs); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.75); text-decoration: none; letter-spacing: -.1px; transition: background .15s, color .15s; position: relative; }
.sb-item:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.96); }
.sb-item.active { background: rgba(99,102,241,.52); color: #fff; box-shadow: 0 2px 12px rgba(99,102,241,.25); }
.sb-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: #a5b4fc; border-radius: 0 3px 3px 0; }
.sb-item .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); }
.sb-item.active .ico { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.22); }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; }
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-xs); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.75); cursor: pointer; transition: background .15s; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); }
.sb-group-toggle:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.96); }
.sb-group-toggle .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.3); transition: transform .2s; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.6); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.1); margin-top: 2px; margin-bottom: 4px; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 13px; font-weight: 500; padding: 6px 10px; color: rgba(255,255,255,.6); }
.sb-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-xs); text-decoration: none; transition: background .15s; }
.sb-user:hover { background: rgba(255,255,255,.06); }
.sb-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg,#6366f1,#4338ca); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(99,102,241,.45); }
.sb-uname { font-size: 13px; font-weight: 700; color: #fff; letter-spacing: -.2px; }
.sb-urole { font-size: 10.5px; color: rgba(255,255,255,.5); margin-top: 1px; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-xs); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.92); font-size: 12.5px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s; text-align: left; }
.sb-logout:hover { background: rgba(220,38,38,.18); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.18); flex-shrink: 0; }
.sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 39; }

/* ─── Topbar ─── */
.topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 28px; height: var(--top-h); display: flex; align-items: center; gap: 14px; position: sticky; top: 0; z-index: 30; box-shadow: 0 1px 0 var(--border); }
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); font-size: 20px; }
.tb-back { display: inline-flex; align-items: center; gap: 6px; color: var(--muted); font-size: 13px; font-weight: 600; text-decoration: none; padding: 6px 12px; border-radius: var(--r-xs); border: 1px solid var(--border); background: var(--surface); transition: all .15s; white-space: nowrap; }
.tb-back:hover { color: var(--brand); border-color: var(--brand-lt); background: var(--brand-mlt); }
.tb-divider { width: 1px; height: 20px; background: var(--border); flex-shrink: 0; }
.tb-title { font-size: 15px; font-weight: 800; color: var(--text); letter-spacing: -.3px; }
.tb-sub { font-size: 11px; color: var(--muted); margin-top: 1px; }
.tb-right { margin-left: auto; display: flex; align-items: center; gap: 8px; }

/* ─── Buttons ─── */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: var(--r-xs); font-size: 13px; font-weight: 700; font-family: var(--font); border: 1.5px solid var(--border-dk); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(99,102,241,.3); }
.btn-primary:hover { background: var(--brand-dk); color: #fff; border-color: var(--brand-dk); }
.btn-success { background: var(--green); color: #fff; border-color: #059669; box-shadow: 0 2px 8px rgba(16,185,129,.3); }
.btn-success:hover { background: #059669; color: #fff; }
.btn-danger  { background: var(--red-lt); color: var(--red); border-color: #fca5a5; }
.btn-danger:hover  { background: var(--red); color: #fff; border-color: var(--red); }
.btn-sm { padding: 6px 13px; font-size: 12px; }

/* ─── Status badges ─── */
.badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; white-space: nowrap; }
.badge-attente  { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge-confirmee{ background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }
.badge-livraison{ background: #cffafe; color: #155e75; border: 1px solid #a5f3fc; }
.badge-livree   { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.badge-annulee  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

/* ─── Page ─── */
.page { padding: 28px 28px 80px; }

/* ─── Hero Order Header ─── */
.order-header {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 0;
    margin-bottom: 24px;
    box-shadow: var(--shadow);
    overflow: hidden;
}
.oh-top {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e1b4b 100%);
    padding: 24px 28px;
    display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap;
}
.oh-id-wrap { display: flex; align-items: center; gap: 14px; }
.oh-icon { width: 52px; height: 52px; border-radius: 13px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
.oh-id { font-size: 28px; font-weight: 900; color: #fff; letter-spacing: -1px; font-family: var(--mono); line-height: 1; }
.oh-date { font-size: 12px; color: rgba(255,255,255,.5); margin-top: 4px; }
.oh-total-wrap { text-align: right; }
.oh-total-lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: rgba(255,255,255,.4); margin-bottom: 4px; }
.oh-total { font-size: 34px; font-weight: 900; color: #fff; font-family: var(--mono); letter-spacing: -1.5px; line-height: 1; }
.oh-total span { font-size: 14px; font-family: var(--font); font-weight: 600; color: rgba(255,255,255,.5); }
.oh-actions-bar {
    padding: 14px 28px;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    border-bottom: 0;
    background: var(--surface);
}
.oh-status-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

/* ─── Timeline statut ─── */
.status-steps { display: flex; align-items: center; gap: 0; overflow-x: auto; padding-bottom: 2px; }
.step-item { display: flex; align-items: center; gap: 0; }
.step-dot { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; border: 2px solid var(--border); background: var(--surface); color: var(--muted); flex-shrink: 0; transition: all .2s; }
.step-dot.done  { background: var(--green); border-color: var(--green); color: #fff; }
.step-dot.active{ background: var(--brand); border-color: var(--brand); color: #fff; box-shadow: 0 0 0 4px rgba(99,102,241,.15); }
.step-dot.cancel{ background: var(--red); border-color: var(--red); color: #fff; }
.step-label { font-size: 10.5px; font-weight: 600; color: var(--muted); margin-top: 4px; white-space: nowrap; }
.step-label.done  { color: var(--green-dk); }
.step-label.active{ color: var(--brand-dk); font-weight: 700; }
.step-label.cancel{ color: var(--red); }
.step-wrap { display: flex; flex-direction: column; align-items: center; gap: 0; }
.step-line { width: 36px; height: 2px; background: var(--border); flex-shrink: 0; }
.step-line.done { background: var(--green); }

/* ─── Grid principal ─── */
.detail-grid { display: grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: start; }

/* ─── Cards ─── */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); box-shadow: var(--shadow); overflow: hidden; }
.card + .card { margin-top: 20px; }
.card-head { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.card-title { font-size: 13.5px; font-weight: 800; color: var(--text); display: flex; align-items: center; gap: 8px; letter-spacing: -.2px; }
.card-body { padding: 20px; }

/* ─── Items ─── */
.items-wrap { overflow-x: auto; }
table.items-tbl { width: 100%; border-collapse: collapse; min-width: 400px; }
table.items-tbl thead th { padding: 10px 16px; text-align: left; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: var(--muted); background: var(--bg); border-bottom: 1px solid var(--border); }
table.items-tbl thead th:last-child { text-align: right; }
table.items-tbl tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
table.items-tbl tbody tr:last-child td { border-bottom: none; }
table.items-tbl tbody tr:hover td { background: #fafbff; }
.prod-cell { display: flex; align-items: center; gap: 12px; }
.prod-img { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; border: 1px solid var(--border); flex-shrink: 0; background: var(--bg); }
.prod-img-placeholder { width: 48px; height: 48px; border-radius: 10px; background: var(--bg); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.prod-name { font-size: 13.5px; font-weight: 700; color: var(--text); }
.prod-ref { font-size: 11px; color: var(--muted); margin-top: 1px; font-family: var(--mono); }
.qty-chip { display: inline-flex; align-items: center; justify-content: center; background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); font-size: 12.5px; font-weight: 700; border-radius: 6px; padding: 3px 10px; font-family: var(--mono); }
.price-cell { font-family: var(--mono); font-weight: 600; color: var(--text-2); font-size: 13px; }
.total-cell { font-family: var(--mono); font-weight: 800; color: var(--text); font-size: 13.5px; text-align: right; }

/* ─── Totaux ─── */
.totaux { border-top: 1px solid var(--border); background: var(--bg); }
.tot-row { display: flex; justify-content: space-between; align-items: center; padding: 11px 20px; font-size: 13px; border-bottom: 1px solid var(--border); }
.tot-row:last-child { border-bottom: none; }
.tot-row .lbl { color: var(--text-2); font-weight: 500; display: flex; align-items: center; gap: 6px; }
.tot-row .val { font-family: var(--mono); font-weight: 700; color: var(--text); }
.tot-grand { background: linear-gradient(135deg, #1e1b4b, #312e81); }
.tot-grand .lbl { color: rgba(255,255,255,.7); font-weight: 700; font-size: 13.5px; }
.tot-grand .val { color: #fff; font-size: 20px; letter-spacing: -.5px; }

/* ─── Info rows ─── */
.info-list { display: flex; flex-direction: column; gap: 0; }
.info-row { display: flex; align-items: flex-start; gap: 12px; padding: 11px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.info-row:last-child { border-bottom: none; padding-bottom: 0; }
.info-row:first-child { padding-top: 0; }
.info-row .lbl { color: var(--muted); font-weight: 600; font-size: 11.5px; text-transform: uppercase; letter-spacing: .4px; min-width: 90px; flex-shrink: 0; padding-top: 1px; }
.info-row .val { font-weight: 600; color: var(--text); flex: 1; word-break: break-word; }
.info-row .val.mono { font-family: var(--mono); }
.info-row a { color: var(--brand); text-decoration: none; font-weight: 700; }
.info-row a:hover { text-decoration: underline; }

/* ─── Client avatar ─── */
.client-card-top { display: flex; align-items: center; gap: 14px; padding: 16px 20px; border-bottom: 1px solid var(--border); }
.c-avatar { width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 17px; font-weight: 900; color: #fff; flex-shrink: 0; }
.c-name { font-size: 15px; font-weight: 800; color: var(--text); letter-spacing: -.3px; }
.c-email { font-size: 12px; color: var(--muted); margin-top: 2px; }

/* Bootstrap applique max-width:100%;height:auto à tous les <img> — ça casse les tuiles Leaflet */
.leaflet-container img { max-width: none; height: auto; }
.leaflet-tile          { max-width: none !important; }

/* ─── GPS card ─── */
.gps-map-wrap { height: 460px; position: relative; z-index: 1; }
.gps-footer { padding: 12px 20px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--bg); }
.gps-live { display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700; color: #15803d; }
.gps-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; animation: blink 2s ease-in-out infinite; box-shadow: 0 0 6px #22c55e; flex-shrink: 0; }
.gps-ping { font-size: 11px; color: var(--muted); font-weight: 600; }
.gps-empty { padding: 40px 20px; text-align: center; }
.gps-empty-ico { font-size: 42px; display: block; margin-bottom: 12px; opacity: .25; }
.gps-empty-txt { font-size: 13px; color: var(--muted); line-height: 1.6; }

/* ─── Livreur inline ─── */
.livreur-inline { display: flex; align-items: center; gap: 12px; }
.l-av { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #0891b2, #0e7490); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; color: #fff; flex-shrink: 0; }
.l-name { font-size: 13.5px; font-weight: 700; color: var(--text); }
.l-avail { font-size: 11px; color: var(--muted); margin-top: 2px; display: flex; align-items: center; gap: 4px; }
.l-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

/* ─── Flash ─── */
.flash { padding: 11px 16px; border-radius: var(--r-xs); border: 1px solid; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
.flash-success { background: #f0fdf4; border-color: #86efac; color: #166534; }
.flash-warning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.flash-danger  { background: var(--red-lt); border-color: #fca5a5; color: #991b1b; }

/* ─── Responsive ─── */
@media (max-width: 1024px) {
    .detail-grid { grid-template-columns: 1fr; }
}
@media (max-width: 900px) {
    .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .page { padding: 16px 14px 60px; }
    .topbar { padding: 0 14px; }
    .oh-top { padding: 18px 18px; }
    .oh-actions-bar { padding: 12px 18px; }
    .oh-id { font-size: 22px; }
    .oh-total { font-size: 26px; }
    .status-steps { gap: 0; }
    .step-line { width: 24px; }
}
@media (max-width: 520px) {
    .oh-top { flex-direction: column; gap: 14px; }
    .oh-total-wrap { text-align: left; }
    .tb-back span { display: none; }
}
</style>
@endpush

@section('content')
@php
    $user     = auth()->user();
    $parts    = explode(' ', $user->name);
    $initials = strtoupper(substr($parts[0],0,1)).strtoupper(substr($parts[1] ?? 'X',0,1));
    $init     = fn(string $n): string =>
        strtoupper(substr(explode(' ',$n)[0],0,1)).
        strtoupper(substr(explode(' ',$n)[1] ?? substr($n,1,1),0,1));

    $devise      = $shop->currency ?? 'GNF';
    $ordersRoute = route('employe.orders.index');

    $pendingCount = $shop->orders()->whereIn('status',['en_attente','pending'])->count();

    $statusMap = [
        'en_attente'   => ['label'=>'En attente',   'class'=>'badge-attente',  'ico'=>'⏳', 'step'=>0],
        'pending'      => ['label'=>'En attente',   'class'=>'badge-attente',  'ico'=>'⏳', 'step'=>0],
        'confirmée'    => ['label'=>'Confirmée',    'class'=>'badge-confirmee','ico'=>'✓',  'step'=>1],
        'en_livraison' => ['label'=>'En livraison', 'class'=>'badge-livraison','ico'=>'🚴', 'step'=>2],
        'livrée'       => ['label'=>'Livrée',       'class'=>'badge-livree',   'ico'=>'📦', 'step'=>3],
        'annulée'      => ['label'=>'Annulée',      'class'=>'badge-annulee',  'ico'=>'✕',  'step'=>-1],
    ];
    $sInfo    = $statusMap[$order->status] ?? ['label'=>$order->status,'class'=>'badge-attente','ico'=>'•','step'=>0];
    $curStep  = $sInfo['step'];
    $isCancelled = $order->status === 'annulée';

    $canConfirm = in_array($order->status, ['en_attente','pending']);
    $canCancel  = in_array($order->status, ['en_attente','pending','confirmée']);
    $canAssign  = $order->status === 'confirmée';

    $subtotal = $order->items->sum(fn($i) => $i->price * $i->quantity);
@endphp

<div class="dash-wrap">

{{-- ─── Sidebar ─── --}}
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
            <div class="sb-logo-icon"><img src="/images/shopio3.jpeg" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
            <span class="sb-shop-name">{{ $shop->name }}</span>
        </a>
        <button class="sb-close" id="btnCloseSidebar">✕</button>
        <div class="sb-status">
            <span class="pulse"></span>
            {{ $shop->is_approved ? 'Boutique active' : 'En attente' }}
            &nbsp;·&nbsp; {{ ucfirst($user->role_in_shop ?? $user->role) }}
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('boutique.dashboard') }}" class="sb-item"><span class="ico">⊞</span> Tableau de bord</a>
        <div class="sb-section">Boutique</div>
        <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">💬</span> Messages</a>
        <a href="{{ $ordersRoute }}" class="sb-item active">
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
                <span class="ico">💰</span> Finances & Rapports <span class="sb-arrow">▶</span>
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
                <div class="sb-uname">{{ Str::limit($user->name,20) }}</div>
                <div class="sb-urole">{{ $user->role === 'admin' ? 'Administrateur' : ucfirst($user->role) }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="sb-logout"><span class="ico">⎋</span> Se déconnecter</button>
        </form>
    </div>
</aside>
<div class="sb-overlay" id="sbOverlay"></div>

{{-- ─── Main ─── --}}
<main class="main">

    {{-- Topbar --}}
    <div class="topbar">
        <button class="btn-hamburger" id="btnMenu">☰</button>
        <a href="{{ $ordersRoute }}" class="tb-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            <span>Commandes</span>
        </a>
        <div class="tb-divider"></div>
        <div>
            <div class="tb-title">Commande #{{ $order->id }}</div>
            <div class="tb-sub">{{ $order->created_at->format('d M Y à H:i') }}</div>
        </div>
        <div class="tb-right" id="uiActionBtns">
            @if($canConfirm)
            <form method="POST" action="{{ route('orders.confirm',$order) }}">@csrf @method('PUT')
                <button class="btn btn-success btn-sm">✓ Confirmer</button>
            </form>
            @endif
            @if($canAssign)
            <a href="{{ route('orders.assign.show',$order) }}" class="btn btn-primary btn-sm">🚴 Assigner</a>
            @endif
            @if($canCancel)
            <form method="POST" action="{{ route('orders.cancel',$order) }}" onsubmit="return confirm('Annuler cette commande ?')">@csrf @method('PUT')
                <button class="btn btn-danger btn-sm">✕ Annuler</button>
            </form>
            @endif
        </div>
    </div>

    <div class="page">

        {{-- Flash --}}
        @foreach(['success','warning','danger'] as $t)
            @if(session($t))
            <div class="flash flash-{{ $t }}">
                {{ $t === 'success' ? '✓' : '⚠' }} {{ session($t) }}
            </div>
            @endif
        @endforeach

        {{-- ═══ ORDER HEADER ═══ --}}
        <div class="order-header">
            {{-- Top dark band --}}
            <div class="oh-top">
                <div class="oh-id-wrap">
                    <div class="oh-icon">📦</div>
                    <div>
                        <div class="oh-id">#{{ $order->id }}</div>
                        <div class="oh-date">{{ $order->created_at->format('d/m/Y à H\hi') }}</div>
                    </div>
                    <span class="badge {{ $sInfo['class'] }}" id="uiBadgeHeader" style="margin-left:4px">{{ $sInfo['ico'] }} {{ $sInfo['label'] }}</span>
                </div>
                <div class="oh-total-wrap">
                    <div class="oh-total-lbl">Total commande</div>
                    <div class="oh-total">{{ number_format($order->total,0,',',' ') }} <span>{{ $devise }}</span></div>
                </div>
            </div>

            {{-- Status timeline --}}
            <div class="oh-actions-bar">
                <div id="uiStatusArea">
                @if(!$isCancelled)
                <div class="status-steps" id="uiStatusSteps">
                    @php
                        $steps = [
                            ['label'=>'En attente','ico'=>'⏳'],
                            ['label'=>'Confirmée', 'ico'=>'✓'],
                            ['label'=>'Livraison', 'ico'=>'🚴'],
                            ['label'=>'Livrée',    'ico'=>'📦'],
                        ];
                    @endphp
                    @foreach($steps as $i => $step)
                        <div class="step-item">
                            <div class="step-wrap">
                                <div class="step-dot {{ $i < $curStep ? 'done' : ($i === $curStep ? 'active' : '') }}">
                                    {{ $i < $curStep ? '✓' : $step['ico'] }}
                                </div>
                                <div class="step-label {{ $i < $curStep ? 'done' : ($i === $curStep ? 'active' : '') }}">
                                    {{ $step['label'] }}
                                </div>
                            </div>
                            @if(!$loop->last)
                            <div class="step-line {{ $i < $curStep ? 'done' : '' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @else
                <span class="badge badge-annulee" id="uiCancelledBadge" style="font-size:13px;padding:6px 16px">✕ Commande annulée</span>
                @endif
                </div>{{-- /uiStatusArea --}}

                <div style="display:flex;align-items:center;gap:6px;font-size:11.5px;color:var(--muted)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ $order->created_at->format('d M Y') }}
                </div>
            </div>
        </div>

        {{-- ═══ DETAIL GRID ═══ --}}
        <div class="detail-grid">

            {{-- ── Colonne gauche ── --}}
            <div>

                {{-- Articles --}}
                <div class="card">
                    <div class="card-head">
                        <span class="card-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                            Articles commandés
                        </span>
                        <span style="font-size:11.5px;color:var(--muted);background:var(--bg);border:1px solid var(--border);padding:3px 10px;border-radius:20px;font-weight:700;font-family:var(--mono)">
                            {{ $order->items->count() }} article{{ $order->items->count() > 1 ? 's' : '' }}
                        </span>
                    </div>
                    <div class="items-wrap">
                        <table class="items-tbl">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th style="text-align:center">Qté</th>
                                    <th>Prix unit.</th>
                                    <th>Sous-total</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($order->items as $item)
                            @php $prod = $item->product; @endphp
                            <tr>
                                <td>
                                    <div class="prod-cell">
                                        @if($prod?->image)
                                            <img src="{{ asset('storage/'.$prod->image) }}" class="prod-img" alt="">
                                        @else
                                            <div class="prod-img-placeholder">📦</div>
                                        @endif
                                        <div>
                                            <div class="prod-name">{{ $prod?->name ?? 'Produit supprimé' }}</div>
                                            @if($prod?->sku)<div class="prod-ref">REF: {{ $prod->sku }}</div>@endif
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align:center"><span class="qty-chip">{{ $item->quantity }}</span></td>
                                <td class="price-cell">{{ number_format($item->price,0,',',' ') }} <span style="font-size:10px;color:var(--muted)">{{ $devise }}</span></td>
                                <td class="total-cell">{{ number_format($item->price * $item->quantity,0,',',' ') }} <span style="font-size:10px;color:var(--muted);font-family:var(--font);font-weight:600">{{ $devise }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" style="padding:32px;text-align:center;color:var(--muted)">Aucun article dans cette commande.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Totaux --}}
                    <div class="totaux">
                        @if($order->delivery_fee)
                        <div class="tot-row">
                            <span class="lbl">Sous-total articles</span>
                            <span class="val">{{ number_format($subtotal,0,',',' ') }} {{ $devise }}</span>
                        </div>
                        <div class="tot-row">
                            <span class="lbl">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
                                Frais de livraison
                            </span>
                            <span class="val">{{ number_format($order->delivery_fee,0,',',' ') }} {{ $devise }}</span>
                        </div>
                        @endif
                        <div class="tot-row tot-grand">
                            <span class="lbl">Total à payer</span>
                            <span class="val">{{ number_format($order->total,0,',',' ') }} {{ $devise }}</span>
                        </div>
                    </div>
                </div>

                {{-- Client --}}
                <div class="card" style="margin-top:20px">
                    <div class="card-head">
                        <span class="card-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Informations client
                        </span>
                        @if($order->client)
                        <span style="font-size:11.5px;color:var(--muted);font-weight:600">{{ $order->client->orders()->count() }} commande{{ $order->client->orders()->count() > 1 ? 's' : '' }} au total</span>
                        @endif
                    </div>
                    @if($order->client)
                    @php $cl = $order->client; @endphp
                    <div class="client-card-top">
                        <div class="c-avatar" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)">{{ $init($cl->name) }}</div>
                        <div>
                            <div class="c-name">{{ $cl->name }}</div>
                            @if($cl->email)<div class="c-email">{{ $cl->email }}</div>@endif
                        </div>
                    </div>
                    <div class="card-body" style="padding-top:4px">
                        <div class="info-list">
                            @if($cl->phone)
                            <div class="info-row">
                                <span class="lbl">Téléphone</span>
                                <a href="tel:{{ $cl->phone }}" class="val">📞 {{ $cl->phone }}</a>
                            </div>
                            @endif
                            @if($cl->address)
                            <div class="info-row">
                                <span class="lbl">Adresse</span>
                                <span class="val">🏠 {{ $cl->address }}</span>
                            </div>
                            @endif
                            @if($order->delivery_destination)
                            <div class="info-row">
                                <span class="lbl">Livraison</span>
                                <span class="val">📍 {{ $order->delivery_destination }}</span>
                            </div>
                            @endif
                            @if($cl->created_at)
                            <div class="info-row">
                                <span class="lbl">Client depuis</span>
                                <span class="val">{{ $cl->created_at->format('d M Y') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="card-body" style="text-align:center;color:var(--muted);padding:28px">Client introuvable.</div>
                    @endif
                </div>

            </div>

            {{-- ── Colonne droite ── --}}
            <div>

                {{-- Récapitulatif commande --}}
                <div class="card">
                    <div class="card-head">
                        <span class="card-title">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Récapitulatif
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="info-list">
                            <div class="info-row">
                                <span class="lbl">N° commande</span>
                                <span class="val mono">#{{ $order->id }}</span>
                            </div>
                            <div class="info-row">
                                <span class="lbl">Date</span>
                                <span class="val">{{ $order->created_at->format('d/m/Y · H:i') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="lbl">Statut</span>
                                <span class="val"><span class="badge {{ $sInfo['class'] }}" id="uiBadgeRecap" style="font-size:11px">{{ $sInfo['ico'] }} {{ $sInfo['label'] }}</span></span>
                            </div>
                            <div class="info-row">
                                <span class="lbl">Devise</span>
                                <span class="val mono">{{ $devise }}</span>
                            </div>
                            <div id="uiLivreurRow">
                            @if($order->livreur)
                            <div class="info-row" style="align-items:center">
                                <span class="lbl">Livreur</span>
                                <div class="livreur-inline">
                                    <div class="l-av">{{ $init($order->livreur->name) }}</div>
                                    <div>
                                        <div class="l-name">{{ $order->livreur->name }}</div>
                                        <div class="l-avail">
                                            @if($order->status === 'livrée')
                                                <span class="l-dot" style="background:#22c55e"></span> Livraison terminée
                                            @elseif($order->status === 'en_livraison')
                                                <span class="l-dot" style="background:#f59e0b"></span> En livraison
                                            @elseif($order->livreur->is_available)
                                                <span class="l-dot" style="background:#22c55e"></span> Disponible
                                            @else
                                                <span class="l-dot" style="background:#94a3b8"></span> Occupé
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @elseif($canAssign)
                            <div class="info-row">
                                <span class="lbl">Livreur</span>
                                <a href="{{ route('orders.assign.show',$order) }}" class="val" style="color:var(--brand)">Assigner un livreur →</a>
                            </div>
                            @endif
                            </div>{{-- /uiLivreurRow --}}
                            @if($order->delivery_fee)
                            <div class="info-row">
                                <span class="lbl">Frais livr.</span>
                                <span class="val mono">{{ number_format($order->delivery_fee,0,',',' ') }} {{ $devise }}</span>
                            </div>
                            @endif
                            @if($order->delivery_destination)
                            <div class="info-row">
                                <span class="lbl">Destination</span>
                                <span class="val">{{ $order->delivery_destination }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>


            </div>
        </div>{{-- /detail-grid --}}

        {{-- ═══ GPS PLEINE LARGEUR ═══ --}}
        <div class="card" style="margin-top:20px;overflow:hidden">
            <div class="card-head">
                <span class="card-title" style="font-size:14.5px">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
                    Suivi GPS du livreur
                    @if($order->livreur)
                    — <span id="uiGpsLivreurName" style="font-weight:500;color:var(--text-2)">{{ $order->livreur->name }}</span>
                    @endif
                </span>
                <div id="uiGpsLiveArea" style="display:flex;align-items:center;gap:10px">
                    @if($order->current_lat && $order->current_lng)
                    <div class="gps-live" id="uiGpsLivePill"><span class="gps-live-dot"></span> En direct · <span id="lastPingLabel" style="font-weight:500">{{ $order->last_ping_at?->diffForHumans() ?? '—' }}</span></div>
                    @endif
                </div>
            </div>

            <div id="uiGpsBody">
            @if($order->current_lat && $order->current_lng)
                <div id="gpsMap" class="gps-map-wrap"></div>
                <div class="gps-footer" style="padding:13px 20px">
                    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
                        <span style="font-size:12px;color:var(--muted)">🔄 Actualisation automatique toutes les 5 secondes</span>
                        <span id="uiGpsCoords" style="font-size:12px;color:var(--muted);font-family:var(--mono)">
                            {{ number_format((float)$order->current_lat, 5) }}, {{ number_format((float)$order->current_lng, 5) }}
                        </span>
                    </div>
                    <a href="#" data-recenter style="font-size:11.5px;color:var(--brand);font-weight:600;text-decoration:none">⊕ Centrer</a>
                </div>
            @elseif($order->livreur && $order->status === 'en_livraison')
                <div class="gps-empty" id="uiGpsEmptySignal" style="padding:60px 20px">
                    <span class="gps-empty-ico" style="font-size:52px">📡</span>
                    <div class="gps-empty-txt" style="font-size:14px">
                        En attente du signal GPS de <strong style="color:var(--text-2)">{{ $order->livreur->name }}</strong>…<br>
                        <span style="font-size:12px">La position s'affichera dès que le livreur active l'application.</span>
                    </div>
                </div>
            @elseif($order->livreur)
                <div class="gps-empty" id="uiGpsEmptyWait" style="padding:60px 20px">
                    <span class="gps-empty-ico" style="font-size:52px">🗺️</span>
                    <div class="gps-empty-txt" style="font-size:14px">
                        Livreur assigné, en attente du démarrage.<br>
                        <span style="font-size:12px">Le GPS s'activera quand le livreur sera en route.</span>
                    </div>
                </div>
            @else
                <div class="gps-empty" id="uiGpsEmptyNoLivreur" style="padding:60px 20px">
                    <span class="gps-empty-ico" style="font-size:52px">🗺️</span>
                    <div class="gps-empty-txt" style="font-size:14px">
                        Aucun livreur assigné.<br>
                        <span style="font-size:12px">Le GPS s'activera après l'assignation du livreur.</span>
                    </div>
                </div>
            @endif
            </div>{{-- /uiGpsBody --}}
        </div>

    </div>{{-- /page --}}
</main>
</div>{{-- /dash-wrap --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script>
/* ── Sidebar ── */
(function(){
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('sbOverlay');
    document.getElementById('btnMenu')?.addEventListener('click', () => { sb.classList.add('open'); ov.classList.add('open'); });
    ov?.addEventListener('click', () => { sb.classList.remove('open'); ov.classList.remove('open'); });
    document.getElementById('btnCloseSidebar')?.addEventListener('click', () => { sb.classList.remove('open'); ov.classList.remove('open'); });
})();

function toggleGroup(btn) {
    const sub = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');
    document.querySelectorAll('.sb-sub.open').forEach(s => { s.classList.remove('open'); s.previousElementSibling?.classList.remove('open'); });
    if (!isOpen) { sub.classList.add('open'); btn.classList.add('open'); }
}

/* ── Auto-refresh unifié ── */
(function(){
    const DATA_URL     = '{{ route('suivi.data', $order) }}';
    const CONFIRM_URL  = '{{ route('orders.confirm', $order) }}';
    const ASSIGN_URL   = '{{ route('orders.assign.show', $order) }}';
    const CANCEL_URL   = '{{ route('orders.cancel', $order) }}';
    const CSRF         = '{{ csrf_token() }}';

    @php
        $lat0 = $order->current_lat;
        $lng0 = $order->current_lng;
    @endphp
    const HAS_GPS      = {{ ($lat0 && $lng0) ? 'true' : 'false' }};
    const IS_DELIVERED = {{ ($order->status === 'livrée') ? 'true' : 'false' }};
    const IS_CANCELLED = {{ ($order->status === 'annulée') ? 'true' : 'false' }};

    let currentStatus = {!! json_encode($order->status) !!};
    let lname         = {!! json_encode($order->livreur?->name ?? '') !!};
    let pollId        = null;

    /* ── Map state ── */
    let map = null, marker = null, halo = null, trail = null;
    let mapLat = {{ $lat0 ?? 9.6412 }};
    let mapLng = {{ $lng0 ?? -13.5784 }};

    const TILE_PROVIDERS = [
        { url:'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
          opts:{ attribution:'© OpenStreetMap', maxZoom:19, subdomains:'abc', crossOrigin:'' } },
        { url:'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
          opts:{ attribution:'© CartoDB', subdomains:'abcd', maxZoom:20, crossOrigin:'' } },
        { url:'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}',
          opts:{ attribution:'© Esri', maxZoom:19, crossOrigin:'' } },
    ];

    function makeMotoIcon() {
        return L.divIcon({
            html: `<div style="width:46px;height:46px;border-radius:50%;
                background:linear-gradient(135deg,#6366f1,#4f46e5);
                border:3px solid #fff;
                box-shadow:0 4px 20px rgba(99,102,241,.6),0 0 0 6px rgba(99,102,241,.15);
                display:flex;align-items:center;justify-content:center;font-size:22px">🛵</div>`,
            iconSize:[46,46], iconAnchor:[23,23], className:''
        });
    }

    function initMap(lat, lng) {
        if (map) return; /* déjà initialisée */
        map = L.map('gpsMap', { zoomControl: true }).setView([lat, lng], 16);

        let _pIdx = 0, _layer = null, _fails = 0;
        function _loadTiles(i) {
            if (i >= TILE_PROVIDERS.length) return;
            if (_layer) map.removeLayer(_layer);
            _fails = 0;
            const p = TILE_PROVIDERS[i];
            _layer = L.tileLayer(p.url, p.opts).addTo(map);
            _layer.on('tileerror', () => { if (++_fails >= 3) _loadTiles(++_pIdx); });
        }
        _loadTiles(0);
        setTimeout(() => map.invalidateSize(), 150);
        setTimeout(() => map.invalidateSize(), 700);
        window.addEventListener('load', () => map.invalidateSize());

        marker = L.marker([lat, lng], { icon: makeMotoIcon() }).addTo(map);
        if (lname) marker.bindPopup(`<div style="font-family:system-ui;padding:4px 2px"><strong style="font-size:13.5px">${lname}</strong></div>`, { offset:[0,-20] }).openPopup();

        halo = L.circle([lat, lng], { color:'#6366f1', fillColor:'#6366f1', fillOpacity:.10, radius:60, weight:1.5 }).addTo(map);
        trail = L.polyline([[lat, lng]], { color:'#6366f1', weight:3, opacity:.45, dashArray:'6 4' }).addTo(map);

        document.addEventListener('click', e => {
            if (e.target.closest('[data-recenter]')) {
                e.preventDefault();
                if (marker) map.setView(marker.getLatLng(), 16, { animate: true });
            }
        });
    }

    /* ── Initialiser la carte si GPS disponible au chargement ── */
    if (HAS_GPS) initMap(mapLat, mapLng);

    /* ── Helpers DOM ── */
    function getInitials(name) {
        const p = name.trim().split(/\s+/);
        return ((p[0]?.[0] ?? '').toUpperCase() + (p[1]?.[0] ?? p[0]?.[1] ?? '').toUpperCase());
    }

    const STEPS_DEF = [
        {label:'En attente', ico:'⏳'},
        {label:'Confirmée',  ico:'✓'},
        {label:'Livraison',  ico:'🚴'},
        {label:'Livrée',     ico:'📦'},
    ];

    function updateBadges(ico, label, cls) {
        [document.getElementById('uiBadgeHeader'), document.getElementById('uiBadgeRecap')].forEach(b => {
            if (!b) return;
            b.textContent = ico + ' ' + label;
            b.className = 'badge ' + cls;
        });
    }

    function updateTimeline(step, isCancelled) {
        const area = document.getElementById('uiStatusArea');
        if (!area) return;
        if (isCancelled) {
            area.innerHTML = `<span class="badge badge-annulee" style="font-size:13px;padding:6px 16px">✕ Commande annulée</span>`;
            return;
        }
        const html = STEPS_DEF.map((s, i) => {
            const dCls = i < step ? 'done' : i === step ? 'active' : '';
            const lCls = i < step ? 'done' : i === step ? 'active' : '';
            const lineCls = i < step ? 'done' : '';
            const line = i < STEPS_DEF.length - 1 ? `<div class="step-line ${lineCls}"></div>` : '';
            return `<div class="step-item"><div class="step-wrap">
                <div class="step-dot ${dCls}">${i < step ? '✓' : s.ico}</div>
                <div class="step-label ${lCls}">${s.label}</div>
            </div>${line}</div>`;
        }).join('');
        area.innerHTML = `<div class="status-steps">${html}</div>`;
    }

    function updateLivreurRow(livreur, d) {
        const row = document.getElementById('uiLivreurRow');
        if (!row) return;
        if (!livreur) {
            /* Afficher le lien d'assignation si pas encore livré/annulé */
            if (!d.is_delivered && !d.is_cancelled && d.step === 1) {
                row.innerHTML = `<div class="info-row"><span class="lbl">Livreur</span><a href="${ASSIGN_URL}" class="val" style="color:var(--brand)">Assigner un livreur →</a></div>`;
            }
            return;
        }
        lname = livreur.name;
        const ini = getInitials(livreur.name);
        const dotColor = d.is_delivered ? '#22c55e' : d.is_ongoing ? '#f59e0b' : '#94a3b8';
        const dotLabel = d.is_delivered ? 'Livraison terminée' : d.is_ongoing ? 'En livraison' : 'Assigné';
        row.innerHTML = `<div class="info-row" style="align-items:center">
            <span class="lbl">Livreur</span>
            <div class="livreur-inline">
                <div class="l-av">${ini}</div>
                <div>
                    <div class="l-name">${livreur.name}</div>
                    <div class="l-avail"><span class="l-dot" style="background:${dotColor}"></span> ${dotLabel}</div>
                </div>
            </div>
        </div>`;

        /* Titre GPS card */
        const gpsName = document.getElementById('uiGpsLivreurName');
        if (gpsName) gpsName.textContent = livreur.name;
    }

    function updateActionBtns(d) {
        const wrap = document.getElementById('uiActionBtns');
        if (!wrap) return;
        let html = '';
        if (d.step === 0) { /* en_attente */
            html = `<form method="POST" action="${CONFIRM_URL}"><input type="hidden" name="_token" value="${CSRF}"><input type="hidden" name="_method" value="PUT">
                <button class="btn btn-success btn-sm">✓ Confirmer</button></form>
                <form method="POST" action="${CANCEL_URL}" onsubmit="return confirm('Annuler cette commande ?')"><input type="hidden" name="_token" value="${CSRF}"><input type="hidden" name="_method" value="PUT">
                <button class="btn btn-danger btn-sm">✕ Annuler</button></form>`;
        } else if (d.step === 1) { /* confirmée */
            html = `<a href="${ASSIGN_URL}" class="btn btn-primary btn-sm">🚴 Assigner</a>
                <form method="POST" action="${CANCEL_URL}" onsubmit="return confirm('Annuler cette commande ?')"><input type="hidden" name="_token" value="${CSRF}"><input type="hidden" name="_method" value="PUT">
                <button class="btn btn-danger btn-sm">✕ Annuler</button></form>`;
        }
        wrap.innerHTML = html;
    }

    function showGpsMap(lat, lng, livreurName) {
        const body = document.getElementById('uiGpsBody');
        if (!body || document.getElementById('gpsMap')) return; /* map already in DOM */
        body.innerHTML = `
            <div id="gpsMap" class="gps-map-wrap"></div>
            <div class="gps-footer" style="padding:13px 20px">
                <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
                    <span style="font-size:12px;color:var(--muted)">🔄 Actualisation automatique toutes les 5 secondes</span>
                    <span id="uiGpsCoords" style="font-size:12px;color:var(--muted);font-family:var(--mono)">${lat.toFixed(5)}, ${lng.toFixed(5)}</span>
                </div>
                <a href="#" data-recenter style="font-size:11.5px;color:var(--brand);font-weight:600;text-decoration:none">⊕ Centrer</a>
            </div>`;
        initMap(lat, lng);

        /* Afficher le pill En direct */
        const liveArea = document.getElementById('uiGpsLiveArea');
        if (liveArea && !liveArea.querySelector('.gps-live')) {
            liveArea.innerHTML = `<div class="gps-live" id="uiGpsLivePill"><span class="gps-live-dot"></span> En direct · <span id="lastPingLabel" style="font-weight:500">À l'instant</span></div>`;
        }
    }

    /* ── Rafraîchissement unifié ── */
    async function refreshAll() {
        try {
            const r = await fetch(DATA_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) return;
            const d = await r.json();

            /* ── Changement de statut ── */
            if (d.status !== currentStatus) {
                currentStatus = d.status;
                updateBadges(d.status_ico, d.status_label, d.status_badge);
                updateTimeline(d.step, d.is_cancelled);
                updateActionBtns(d);
                if (d.livreur) updateLivreurRow(d.livreur, d);

                if (d.is_delivered || d.is_cancelled) {
                    clearInterval(pollId); pollId = null;
                    if (d.is_delivered && map && d.lat != null) {
                        L.circleMarker([parseFloat(d.lat), parseFloat(d.lng)], {
                            radius:9, color:'#fff', weight:2.5, fillColor:'#10b981', fillOpacity:1
                        }).addTo(map).bindTooltip('✅ Livré ici', { permanent:true, direction:'top' });
                    }
                    const pl = document.getElementById('lastPingLabel');
                    if (pl) pl.textContent = d.is_delivered ? '✅ Livraison terminée' : '❌ Annulée';
                    return;
                }
            } else if (d.livreur && d.livreur.name !== lname) {
                updateLivreurRow(d.livreur, d);
            }

            /* ── GPS ── */
            if (d.lat != null && d.lng != null) {
                const nLat = parseFloat(d.lat);
                const nLng = parseFloat(d.lng);

                /* GPS vient d'apparaître → créer la carte dynamiquement */
                if (!map) {
                    showGpsMap(nLat, nLng, lname);
                    mapLat = nLat; mapLng = nLng;
                } else {
                    const delta = Math.abs(nLat - mapLat) + Math.abs(nLng - mapLng);
                    if (delta > 0.00013) {
                        marker?.setLatLng([nLat, nLng]);
                        halo?.setLatLng([nLat, nLng]);
                        trail?.addLatLng([nLat, nLng]);
                        if (delta > 0.0004) map.panTo([nLat, nLng], { animate:true, duration:1 });
                        mapLat = nLat; mapLng = nLng;
                        const coords = document.getElementById('uiGpsCoords');
                        if (coords) coords.textContent = nLat.toFixed(5) + ', ' + nLng.toFixed(5);
                    }
                }

                /* Indicateur ping */
                if (d.updated) {
                    const dt    = new Date(d.updated);
                    const diffS = Math.round((Date.now() - dt.getTime()) / 1000);
                    const el    = document.getElementById('lastPingLabel');
                    if (el) el.textContent = diffS < 60 ? `il y a ${diffS}s` : dt.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' });
                }
            }

        } catch (e) { /* silencieux */ }
    }

    /* ── Démarrage ── */
    if (!IS_DELIVERED && !IS_CANCELLED) {
        pollId = setInterval(refreshAll, 5000);
        refreshAll();
    }

})();
</script>
@endpush
