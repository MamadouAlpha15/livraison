{{--
    resources/views/boutique/commissions/index.blade.php
    Route     : GET  /boutique/commissions      → Vendeur\CommissionController@index
    Route     : POST /boutique/commissions/pay  → Vendeur\CommissionController@pay
    Variables :
      $commissions  → LengthAwarePaginator<CourierCommission>
      $status       → string  ('en_attente' | 'payée')
      $totalPending → float
      $totalPaid    → float
      $shop         → Shop
      $devise       → string
--}}

@extends('layouts.app')
@section('title', 'Commissions · ' . $shop->name)
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
    --sb-act:     rgba(99,102,241,.52); --sb-hov: rgba(255,255,255,.07);
    --sb-txt:     rgba(255,255,255,.62); --sb-txt-act: #fff;
    --bg:         #f8fafc; --surface:   #ffffff;
    --border:     #e2e8f0; --border-dk: #cbd5e1;
    --text:       #0f172a; --text-2:    #475569; --muted: #94a3b8;
    --warning:    #f59e0b; --warning-lt: #fef3c7;
    --red:        #ef4444; --red-lt:    #fef2f2;
    --green:      #10b981; --green-lt:  #ecfdf5;
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r: 14px; --r-sm: 9px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.06);
    --shadow:     0 4px 16px rgba(0,0,0,.07);
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
.sb-shop-name { font-size: 14.5px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; letter-spacing: -.3px; color: #fff; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 10.5px; color: var(--sb-txt); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: #6ee7b7; flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px #6ee7b7; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }
.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; }
.sb-section { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.8px; color: rgba(255,255,255,.48); padding: 16px 10px 5px; font-weight: 800; }
.sb-item { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); text-decoration: none; letter-spacing: -.1px; transition: background .15s, color .15s; position: relative; }
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-item.active { background: var(--sb-act); color: #fff; box-shadow: 0 2px 12px rgba(99,102,241,.25); }
.sb-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: #a5b4fc; border-radius: 0 3px 3px 0; }
.sb-item .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); }
.sb-item.active .ico { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.2); }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); }
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); cursor: pointer; transition: background .15s; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); }
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-group-toggle .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.32); transition: transform .2s; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.6); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.1); margin-top: 2px; margin-bottom: 4px; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 13px; font-weight: 500; padding: 6px 10px; color: rgba(255,255,255,.62); letter-spacing: 0; }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.92); }
.sb-sub .sb-item.active { color: #fff; background: var(--sb-act); font-weight: 600; }
.sb-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; background: linear-gradient(180deg,transparent 0%,#0b0b12 25%); }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); text-decoration: none; border: 1px solid transparent; transition: background .15s; }
.sb-user:hover { background: rgba(255,255,255,.06); }
.sb-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg,#6366f1,#4338ca); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(99,102,241,.45); }
.sb-uname { font-size: 13px; font-weight: 700; color: #fff; letter-spacing: -.2px; }
.sb-urole { font-size: 10.5px; color: rgba(255,255,255,.52); margin-top: 1px; font-weight: 500; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.92); font-size: 12.5px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s; text-align: left; }
.sb-logout:hover { background: rgba(220,38,38,.18); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.18); flex-shrink: 0; }
.sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 39; }

/* ══ MAIN + TOPBAR ══ */
.main { display: flex; flex-direction: column; min-width: 0; }
.topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 22px; height: var(--top-h); display: flex; align-items: center; gap: 12px; position: sticky; top: 0; z-index: 30; box-shadow: var(--shadow-sm); }
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); font-size: 20px; }
.tb-info { flex: 1; min-width: 0; }
.tb-title { font-size: 14px; font-weight: 700; color: var(--text); }
.tb-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }
.page-wrap { padding: 22px 22px 60px; }

/* ══ HEADER ══ */
.page-hd { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 22px; flex-wrap: wrap; }
.page-title { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -.4px; margin: 0 0 4px; }
.page-sub   { font-size: 13px; color: var(--muted); margin: 0; }
.devise-badge { display: inline-flex; align-items: center; gap: 5px; background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); font-size: 11px; font-weight: 700; font-family: var(--mono); padding: 4px 10px; border-radius: 20px; }

/* ══ KPI ══ */
.kpi-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 10px; }
.kpi-card { border-radius: var(--r); padding: 18px 20px; display: flex; align-items: center; gap: 14px; position: relative; overflow: hidden; box-shadow: var(--shadow-sm); }
.kpi-card.pending { background: linear-gradient(135deg, #451a03, #78350f); border: 1px solid rgba(245,158,11,.25); }
.kpi-card.paid    { background: linear-gradient(135deg, #161021, #4F46E5); border: 1px solid rgba(99,102,241,.25); }
.kpi-card::after  { content: ''; position: absolute; right: -30px; top: -30px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,.04); pointer-events: none; }
.kpi-ico { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
.kpi-card.pending .kpi-ico { background: rgba(245,158,11,.15); border: 1px solid rgba(245,158,11,.25); }
.kpi-card.paid    .kpi-ico { background: rgba(99,102,241,.15); border: 1px solid rgba(99,102,241,.25); }
.kpi-lbl  { font-size: 11px; font-weight: 600; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.kpi-val  { font-size: 24px; font-weight: 800; font-family: var(--mono); letter-spacing: -1px; line-height: 1; }
.kpi-card.pending .kpi-val { color: #fcd34d; }
.kpi-card.paid    .kpi-val { color: #a5b4fc; }
.kpi-unit { font-size: 11px; color: rgba(255,255,255,.35); margin-top: 3px; }
.kpi-sub  { font-size: 11px; color: rgba(255,255,255,.3); margin-top: 4px; }

/* ══ KPI DÉTAIL PAR TYPE ══ */
.kpi-detail-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 22px; }
.kpi-detail { border-radius: var(--r-sm); padding: 12px 16px; display: flex; align-items: center; gap: 12px; box-shadow: var(--shadow-sm); }
.kpi-detail.shop    { background: #f0fdf4; border: 1.5px solid #86efac; }
.kpi-detail.company { background: #eef2ff; border: 1.5px solid #c7d2fe; }
.kpi-detail-ico { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.kpi-detail.shop    .kpi-detail-ico { background: #dcfce7; border: 1px solid #86efac; }
.kpi-detail.company .kpi-detail-ico { background: #e0e7ff; border: 1px solid #c7d2fe; }
.kpi-detail-lbl { font-size: 10.5px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
.kpi-detail.shop    .kpi-detail-lbl { color: #15803d; }
.kpi-detail.company .kpi-detail-lbl { color: #4338ca; }
.kpi-detail-val { font-size: 16px; font-weight: 800; font-family: var(--mono); }
.kpi-detail.shop    .kpi-detail-val { color: #166534; }
.kpi-detail.company .kpi-detail-val { color: #3730a3; }
.kpi-detail-unit { font-size: 10px; color: var(--muted); font-weight: 500; margin-top: 1px; }

/* ══ SECTION SÉPARATEUR ══ */
.section-sep {
    display: flex; align-items: center; gap: 14px;
    margin: 28px 0 16px; font-size: 13.5px; font-weight: 800;
    color: var(--text); letter-spacing: -.2px;
}
.section-sep::before, .section-sep::after {
    content: ''; flex: 1; height: 1.5px;
}
.section-sep.shop::before, .section-sep.shop::after    { background: #86efac; }
.section-sep.company::before, .section-sep.company::after { background: #c7d2fe; }
.section-sep .sep-pill {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 6px 16px; border-radius: 20px; white-space: nowrap;
    font-size: 13px; font-weight: 800;
}
.section-sep.shop    .sep-pill { background: #f0fdf4; border: 1.5px solid #86efac; color: #15803d; }
.section-sep.company .sep-pill { background: #eef2ff; border: 1.5px solid #c7d2fe; color: #4338ca; }

/* ══ COMPANY INFO BOX ══ */
.company-info-box {
    background: #eef2ff; border: 1.5px solid #c7d2fe;
    border-radius: var(--r-sm); padding: 10px 14px;
    font-size: 12.5px; color: #4338ca; font-weight: 600;
    margin-bottom: 12px; display: flex; align-items: flex-start; gap: 8px;
}
.company-info-box .ico { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
.company-ro-note {
    font-size: 11px; color: #4f46e5; font-weight: 600;
    background: #eef2ff; border: 1px solid #c7d2fe;
    border-radius: 6px; padding: 3px 8px; display: inline-block; margin-top: 4px;
}

/* ══ ONGLETS TYPE (niveau 1) ══ */
.type-tabs { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.type-tab {
    display: flex; align-items: center; gap: 8px;
    padding: 11px 20px; border-radius: var(--r); font-size: 13.5px; font-weight: 700;
    text-decoration: none; border: 2px solid var(--border); background: var(--surface);
    color: var(--muted); cursor: pointer; transition: all .18s; font-family: var(--font);
    box-shadow: var(--shadow-sm);
}
.type-tab:hover { border-color: var(--brand-lt); color: var(--brand-dk); background: var(--brand-mlt); }
.type-tab.active.shop    { background: #f0fdf4; border-color: #86efac; color: #15803d; }
.type-tab.active.company { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
.type-tab .type-badge {
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 800; border-radius: 20px;
    padding: 1px 7px; font-family: var(--mono); min-width: 20px;
}
.type-tab:not(.active) .type-badge { background: var(--border); color: var(--muted); }
.type-tab.active.shop    .type-badge { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
.type-tab.active.company .type-badge { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }

/* ══ TABS STATUT (niveau 2) ══ */
.status-tabs { display: flex; gap: 0; margin-bottom: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); }
.status-tab {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 13px 20px; font-size: 13.5px; font-weight: 700;
    text-decoration: none; color: var(--muted); border-bottom: 3px solid transparent;
    transition: all .18s; cursor: pointer; border: none; background: none; font-family: var(--font);
}
.status-tab:hover { background: var(--bg); color: var(--text); }
.status-tab.active {
    color: var(--brand-dk); background: var(--brand-mlt);
    border-bottom: 3px solid var(--brand);
}
.status-tab .tab-count {
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--brand); color: #fff;
    font-size: 10px; font-weight: 800; border-radius: 20px;
    padding: 1px 7px; font-family: var(--mono); min-width: 20px;
}
.status-tab:not(.active) .tab-count { background: var(--border); color: var(--muted); }

/* ══ GUIDE ÉTAPES ══ */
.how-to {
    background: linear-gradient(135deg,#0f2027,#203a43,#2c5364);
    border-radius: var(--r); padding: 18px 22px; margin-bottom: 20px;
    display: flex; gap: 16px; align-items: flex-start;
}
.how-to-steps { display: flex; gap: 8px; flex-wrap: wrap; flex: 1; }
.how-to-step {
    display: flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.08); border-radius: 10px; padding: 8px 13px;
    font-size: 12px; color: rgba(255,255,255,.82); font-weight: 600;
}
.how-to-step.final { background: rgba(99,102,241,.2); border: 1px solid rgba(99,102,241,.3); color: #a5b4fc; }
.step-num {
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--warning); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800; flex-shrink: 0;
}
.how-to-step.final .step-num { background: var(--brand); }

/* ══ BARRE PAIEMENT ══ */
.payout-bar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 14px 18px;
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 18px; box-shadow: var(--shadow); flex-wrap: wrap;
}
.payout-bar.has-selection { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(99,102,241,.12), var(--shadow); }
.payout-input {
    flex: 1; min-width: 160px; padding: 9px 14px; border-radius: var(--r-sm);
    border: 1.5px solid var(--border-dk); font-size: 13px; font-family: var(--font);
    color: var(--text); background: var(--bg); outline: none; transition: border-color .15s;
}
.payout-input:focus { border-color: var(--brand); background: var(--surface); }
.payout-count { font-size: 12px; font-weight: 700; color: var(--muted); white-space: nowrap; padding: 8px 12px; background: var(--bg); border-radius: var(--r-sm); border: 1px solid var(--border); }
.payout-count.active { background: var(--brand-mlt); color: var(--brand-dk); border-color: var(--brand-lt); }

/* Total live */
.payout-total {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 14px; border-radius: var(--r-sm);
    background: #fef9ec; border: 1.5px solid #f59e0b;
    font-family: var(--mono); font-size: 14px; font-weight: 800; color: #92400e;
    white-space: nowrap; transition: all .2s;
}
.payout-total.zero { background: var(--bg); border-color: var(--border); color: var(--muted); }
.payout-total-lbl { font-size: 10px; font-weight: 600; color: #b45309; font-family: var(--font); display: block; margin-bottom: 1px; }

/* ══ BOUTONS ══ */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 8px 16px; border-radius: var(--r-sm); font-size: 13px; font-weight: 600; font-family: var(--font); border: 1px solid var(--border-dk); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-sm { padding: 6px 12px; font-size: 12px; }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(99,102,241,.3); }
.btn-primary:hover { background: var(--brand-dk); color: #fff; }
.btn-primary:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }
.btn-export { background: var(--green-lt); color: #065f46; border-color: #a7f3d0; }
.btn-export:hover { background: var(--green); color: #fff; border-color: var(--green); }

/* ══ TABLE ══ */
.comm-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 18px; }
.comm-card-hd { padding: 13px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--bg); flex-wrap: wrap; gap: 8px; }
.comm-card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl thead th { padding: 11px 14px; text-align: left; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; background: var(--bg); border-bottom: 1px solid var(--border); white-space: nowrap; }
.tbl tbody td { padding: 12px 14px; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; }
.tbl tbody tr.selected td { background: var(--brand-mlt); }
.tbl tbody tr.row-warn td { background: #fff7ed; }
input[type=checkbox] { width: 16px; height: 16px; border-radius: 4px; accent-color: var(--brand); cursor: pointer; }

/* Montant commande (référence) */
.order-amount { font-family: var(--mono); font-size: 12px; color: var(--text-2); font-weight: 600; }
.order-amount small { display: block; font-size: 9.5px; color: var(--muted); font-family: var(--font); margin-top: 1px; }

/* Destination livraison */
.dest-cell { font-size: 12.5px; font-weight: 600; color: var(--text); max-width: 180px; }
.dest-cell small { display: block; font-size: 10px; color: var(--muted); margin-top: 2px; font-family: var(--font); font-weight: 500; }
.dest-empty { font-size: 12px; color: var(--muted); font-style: italic; }

/* Input montant commission */
.comm-input {
    width: 130px; padding: 8px 12px;
    border: 2px solid #f59e0b; border-radius: 8px;
    font-size: 14px; font-weight: 800; font-family: var(--mono);
    color: #92400e; background: #fff; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.comm-input:focus { border-color: #d97706; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }
.comm-input.warn { border-color: var(--red) !important; box-shadow: 0 0 0 3px rgba(239,68,68,.15) !important; }

/* Lv chip */
.lv-chip { display: inline-flex; align-items: center; gap: 6px; background: #f3f6f4; border: 1px solid var(--border); border-radius: 20px; padding: 3px 10px 3px 4px; font-size: 12px; font-weight: 600; color: var(--text-2); }
.lv-av { width: 22px; height: 22px; border-radius: 50%; background: linear-gradient(135deg, var(--brand), #2563eb); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 8px; font-weight: 700; }
.amount { font-family: var(--mono); font-weight: 700; font-size: 13.5px; color: var(--text); white-space: nowrap; }
.amount small { font-size: 10px; color: var(--muted); font-weight: 500; }
.pill { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
.p-success { background: #e0e7ff; color: #3730a3; }
.p-warning { background: #fef3c7; color: #92400e; }
.ref-cell { font-family: var(--mono); font-size: 11px; color: var(--muted); }
.ref-link { font-family: var(--mono); font-size: 11.5px; color: var(--brand); font-weight: 700; text-decoration: none; }
.ref-link:hover { text-decoration: underline; color: var(--brand-dk); }
.date-cell { font-size: 12px; color: var(--text-2); }
.date-cell small { font-size: 10px; color: var(--muted); display: block; }

/* ══ ALERTE LIGNE ══ */
.row-alert { font-size: 11px; color: var(--red); font-weight: 600; margin-top: 4px; display: none; }
.row-alert.visible { display: block; }

/* ══ FLASH ══ */
.flash { padding: 10px 14px; border-radius: var(--r-sm); border: 1px solid; font-size: 13.5px; font-weight: 600; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
.flash-success { background: #eef2ff; border-color: #a5b4fc; color: #3730a3; }
.flash-danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }

/* ══ EMPTY ══ */
.empty-state { padding: 56px 20px; text-align: center; }
.empty-state .ico { font-size: 40px; display: block; margin-bottom: 12px; opacity: .3; }
.empty-state h3 { font-size: 15px; font-weight: 700; color: var(--text); margin: 0 0 6px; }
.empty-state p { font-size: 13px; color: var(--muted); margin: 0; }

/* ══ PAGINATION ══ */
.pagination-wrap { display: flex; justify-content: center; padding: 14px 0 2px; }

/* ══ MODAL CONFIRMATION ══ */
.modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 500;
    background: rgba(0,0,0,.55); backdrop-filter: blur(4px);
    align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: var(--surface); border-radius: 20px;
    padding: 0; width: 100%; max-width: 440px; margin: 20px;
    box-shadow: 0 24px 60px rgba(0,0,0,.25);
    animation: modalIn .25s cubic-bezier(.34,1.56,.64,1);
}
@keyframes modalIn { from { opacity:0; transform:scale(.92) translateY(12px); } to { opacity:1; transform:scale(1) translateY(0); } }
.modal-header {
    padding: 22px 24px 16px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 12px;
}
.modal-ico { font-size: 28px; flex-shrink: 0; }
.modal-title { font-size: 17px; font-weight: 800; color: var(--text); margin: 0; }
.modal-sub   { font-size: 13px; color: var(--muted); margin: 4px 0 0; }
.modal-body { padding: 20px 24px; }
.modal-summary {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: var(--r-sm); padding: 14px 18px;
    display: flex; flex-direction: column; gap: 10px;
}
.modal-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
.modal-row .lbl { color: var(--text-2); }
.modal-row .val { font-weight: 700; color: var(--text); font-family: var(--mono); }
.modal-row.total { border-top: 1px solid var(--border); padding-top: 10px; }
.modal-row.total .lbl { font-weight: 700; color: var(--text); font-family: var(--font); }
.modal-row.total .val { font-size: 18px; color: var(--brand-dk); }
.modal-footer { padding: 0 24px 24px; display: flex; gap: 10px; }
.modal-cancel { flex: 1; padding: 11px; border-radius: var(--r-sm); border: 1.5px solid var(--border); background: var(--surface); font-size: 13.5px; font-weight: 700; font-family: var(--font); cursor: pointer; color: var(--text-2); transition: all .15s; }
.modal-cancel:hover { border-color: var(--border-dk); background: var(--bg); }
.modal-confirm { flex: 2; padding: 11px; border-radius: var(--r-sm); border: none; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-size: 13.5px; font-weight: 700; font-family: var(--font); cursor: pointer; box-shadow: 0 4px 14px rgba(99,102,241,.35); transition: all .15s; }
.modal-confirm:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); }

/* ══ TOOLTIP ══ */
.tooltip-wrap { position: relative; display: inline-flex; align-items: center; }
.tooltip-icon { width: 16px; height: 16px; border-radius: 50%; background: var(--border); color: var(--muted); font-size: 10px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; cursor: help; margin-left: 5px; flex-shrink: 0; }
.tooltip-box { display: none; position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); background: #0f172a; color: #e2e8f0; font-size: 11px; font-weight: 500; padding: 7px 11px; border-radius: 8px; white-space: nowrap; z-index: 100; pointer-events: none; font-family: var(--font); }
.tooltip-box::after { content: ''; position: absolute; top: 100%; left: 50%; transform: translateX(-50%); border: 5px solid transparent; border-top-color: #0f172a; }
.tooltip-wrap:hover .tooltip-box { display: block; }

/* ══ TABLE SCROLL (tablette) ══ */
.tbl-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

/* ══ RESPONSIVE ══ */

/* Tablette large */
@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    /* Masquer colonnes secondaires sur tablette */
    .col-montant { display: none; }
    .col-date    { display: none; }
}

/* Tablette portrait */
@media (max-width: 768px) {
    .page-wrap { padding: 14px 14px 40px; }
    .kpi-card { padding: 14px 16px; gap: 10px; }
    .kpi-val { font-size: 22px; }
    .kpi-ico { width: 40px; height: 40px; font-size: 18px; }
    .status-tab { font-size: 12.5px; padding: 12px 14px; }
    .how-to-step { font-size: 11.5px; padding: 6px 10px; }
    .payout-bar { gap: 8px; }
}

/* Mobile : table → cartes + barre de paiement empilée */
@media (max-width: 640px) {
    .kpi-row { grid-template-columns: 1fr; }
    .page-wrap { padding: 12px 10px 40px; }
    .how-to { flex-direction: column; padding: 14px 16px; }
    .status-tab { font-size: 12px; padding: 11px 10px; }

    /* Payout bar → colonne unique */
    .payout-bar {
        flex-direction: column; align-items: stretch; flex-wrap: nowrap;
        padding: 12px; gap: 10px;
    }
    .payout-count { text-align: center; justify-content: center; }
    .payout-input { width: 100%; min-width: unset; }
    #markPaidBtn { width: 100%; justify-content: center; align-self: auto; padding: 13px; }

    /* ── TABLE → CARTES ── */
    .tbl-wrap { overflow-x: visible; }
    .tbl thead { display: none; }
    .tbl, .tbl tbody { display: block; }

    .tbl tbody tr {
        display: block;
        border: 1px solid var(--border);
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
        background: var(--surface);
        box-shadow: var(--shadow-sm);
    }
    .tbl tbody tr.selected {
        border-color: var(--brand);
        box-shadow: 0 0 0 2px rgba(99,102,241,.18);
    }
    .tbl tbody tr.row-warn { background: #fff7ed; }

    .tbl tbody td {
        display: flex !important; /* override col-montant/col-date display:none */
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
        background: transparent !important;
        gap: 10px;
        min-height: 40px;
    }
    .tbl tbody td:last-child { border-bottom: none; }

    /* Label auto via data-label */
    .tbl tbody td::before {
        content: attr(data-label);
        font-size: 10px; font-weight: 700; color: var(--muted);
        text-transform: uppercase; letter-spacing: .5px;
        flex-shrink: 0; white-space: nowrap;
    }
    /* Cellule sans label (checkbox) */
    .tbl tbody td[data-label=""] { justify-content: flex-start; }
    .tbl tbody td[data-label=""]::before { display: none; }

    /* Cellule commission → colonne pleine largeur */
    .td-comm {
        flex-direction: column !important;
        align-items: flex-start !important;
        background: #fffbeb !important;
        gap: 6px !important;
    }
    .td-comm::before { margin-bottom: 2px; }
    .comm-input { width: 100%; box-sizing: border-box; }
}

/* Petits mobiles */
@media (max-width: 480px) {
    .page-wrap { padding: 10px 8px 36px; }
    .status-tab span.tab-label { display: none; }
    .kpi-val { font-size: 18px; }
    .kpi-sub { font-size: 10px; }
    .modal-box { margin: 8px; }
    .modal-header { padding: 16px 16px 12px; }
    .modal-body  { padding: 14px 16px; }
    .modal-footer { padding: 0 16px 16px; }
}

/* Très petits écrans */
@media (max-width: 360px) {
    .page-wrap { padding: 8px 6px 32px; }
    .tbl tbody td { padding: 9px 10px; }
    .payout-bar { padding: 10px; }
    .kpi-val { font-size: 16px; }
}
</style>
@endpush

@section('content')
@php
    $devise   = $devise ?? ($shop->currency ?? 'GNF');
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $init     = fn(string $n): string => strtoupper(substr(explode(' ',$n)[0],0,1))
                                       . strtoupper(substr(explode(' ',$n)[1] ?? substr($n,1,1),0,1));
    $pendingCount = $shop->orders()->whereIn('status',['pending','en attente','en_attente','confirmée','processing'])->count();
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
            {{ ucfirst(auth()->user()->role_in_shop ?? auth()->user()->role) }}
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
            <button class="sb-group-toggle open" onclick="toggleGroup(this)" type="button">
                <span class="ico">💰</span> Finances & Rapports
                <span class="sb-arrow" style="transform:rotate(90deg);color:rgba(255,255,255,.5)">▶</span>
            </button>
            <div class="sb-sub open">
                <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">💳</span> Paiements</a>
                <a href="{{ route('boutique.commissions.index') }}" class="sb-item active"><span class="ico">📊</span> Commissions</a>
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

{{-- ══ MAIN ══ --}}
<main class="main">
    <div class="topbar">
        <button class="btn-hamburger" id="btnMenu">☰</button>
        <div class="tb-info">
            <div class="tb-title">💸 Commissions livreurs</div>
            <div class="tb-sub">{{ $shop->name }} · <span style="font-family:var(--mono);font-size:10px;background:var(--brand-mlt);color:var(--brand-dk);padding:1px 7px;border-radius:10px;border:1px solid var(--brand-lt)">{{ $devise }}</span></div>
        </div>
    </div>

<div class="page-wrap">

    {{-- ── Header ── --}}
    <div class="page-hd">
        <div>
            <h1 class="page-title">💸 Commissions livreurs</h1>
            <p class="page-sub">{{ $shop->name }} &nbsp;·&nbsp; <span class="devise-badge">💱 {{ $devise }}</span></p>
        </div>
    </div>

    {{-- ── KPI selon l'onglet actif ── --}}
    @if($type === 'shop')
    <div class="kpi-row" style="grid-template-columns:1fr">
        <div class="kpi-card paid">
            <div class="kpi-ico">✅</div>
            <div>
                <div class="kpi-lbl">Total déjà payé — mes livreurs</div>
                <div class="kpi-val">{{ number_format($shopPaid, 0, ',', ' ') }}</div>
                <div class="kpi-unit">{{ $devise }}</div>
                <div class="kpi-sub">Paiements confirmés à vos livreurs boutique</div>
            </div>
        </div>
    </div>
    @else
    <div class="kpi-row">
        <div class="kpi-card pending">
            <div class="kpi-ico">⏳</div>
            <div>
                <div class="kpi-lbl">Total en attente — entreprises</div>
                <div class="kpi-val">{{ number_format($companyPending, 0, ',', ' ') }}</div>
                <div class="kpi-unit">{{ $devise }}</div>
                <div class="kpi-sub">Frais à régler aux entreprises partenaires</div>
            </div>
        </div>
        <div class="kpi-card paid">
            <div class="kpi-ico">✅</div>
            <div>
                <div class="kpi-lbl">Total déjà payé — entreprises</div>
                <div class="kpi-val">{{ number_format($companyPaid, 0, ',', ' ') }}</div>
                <div class="kpi-unit">{{ $devise }}</div>
                <div class="kpi-sub">Paiements confirmés aux entreprises</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Flash --}}
    @foreach(['success','danger'] as $flashType)
        @if(session($flashType))
        <div class="flash flash-{{ $flashType }}">
            <span>{{ $flashType === 'success' ? '✓' : '✕' }}</span>
            {{ session($flashType) }}
        </div>
        @endif
    @endforeach

    {{-- ── ONGLETS TYPE (niveau 1) ── --}}
    @php
        $shopPendingCount   = \App\Models\CourierCommission::where('shop_id',$shop->id)->where('status','en_attente')->whereHas('order',fn($q)=>$q->whereNull('driver_id'))->count();
        $companyPendingCount= \App\Models\CourierCommission::where('shop_id',$shop->id)->where('status','en_attente')->whereHas('order',fn($q)=>$q->whereNotNull('driver_id'))->count();
    @endphp
    <div class="type-tabs">
        <a href="{{ route('boutique.commissions.index', ['type' => 'shop', 'status' => $status]) }}"
           class="type-tab {{ $type === 'shop' ? 'active shop' : '' }}">
            🚴 Commissions de mes livreurs
            @if($shopPendingCount > 0)
            <span class="type-badge">{{ $shopPendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('boutique.commissions.index', ['type' => 'company', 'status' => $status]) }}"
           class="type-tab {{ $type === 'company' ? 'active company' : '' }}">
            🏢 Commissions entreprises
            @if($companyPendingCount > 0)
            <span class="type-badge">{{ $companyPendingCount }}</span>
            @endif
        </a>
    </div>

    {{-- ── SOUS-ONGLETS STATUT (niveau 2) ── --}}
    <div class="status-tabs">
        <a href="{{ route('boutique.commissions.index', ['type' => $type, 'status' => 'en_attente']) }}"
           class="status-tab {{ $status === 'en_attente' ? 'active' : '' }}">
            ⏳ <span class="tab-label">En attente</span>
            <span class="tab-count">{{ $status === 'en_attente' ? $commissions->total() : '—' }}</span>
        </a>
        <a href="{{ route('boutique.commissions.index', ['type' => $type, 'status' => 'payée']) }}"
           class="status-tab {{ $status !== 'en_attente' ? 'active' : '' }}">
            ✅ <span class="tab-label">Payées</span>
            <span class="tab-count">{{ $status !== 'en_attente' ? $commissions->total() : '—' }}</span>
        </a>
        @if($status !== 'en_attente' && $commissions->isNotEmpty())
        <a href="{{ route('boutique.commissions.export', ['type' => $type]) }}" class="status-tab" style="flex:0;padding:13px 18px;color:#065f46;background:#ecfdf5;border-left:1px solid var(--border)">
            ⬇️ <span class="tab-label">Exporter CSV</span>
        </a>
        @endif
    </div>

    @if($status === 'en_attente')
    {{-- ════════════════════════
         EN ATTENTE → formulaire paiement
    ════════════════════════ --}}

    @if($type === 'shop')
    <div class="how-to">
        <div style="font-size:20px;flex-shrink:0;margin-top:2px">💡</div>
        <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:10px">Comment payer un livreur de votre boutique ?</div>
            <div class="how-to-steps">
                <div class="how-to-step"><span class="step-num">1</span>Cochez les lignes à payer</div>
                <div class="how-to-step"><span class="step-num">2</span>Saisissez le montant dans chaque ligne</div>
                <div class="how-to-step"><span class="step-num">3</span>Ajoutez une référence paiement (ex: Orange Money)</div>
                <div class="how-to-step final"><span class="step-num">4</span>Cliquez "Marquer comme payées"</div>
            </div>
        </div>
    </div>
    @else
    <div class="company-info-box">
        <span class="ico">ℹ️</span>
        <div>
            Ces frais correspondent aux livraisons effectuées par les chauffeurs de vos <strong>entreprises partenaires</strong>.
            Cochez les lignes, saisissez le montant convenu et marquez-les comme payées.
        </div>
    </div>
    <div class="how-to">
        <div style="font-size:20px;flex-shrink:0;margin-top:2px">💡</div>
        <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:10px">Comment payer une commission entreprise ?</div>
            <div class="how-to-steps">
                <div class="how-to-step"><span class="step-num">1</span>Cochez les livraisons à régler</div>
                <div class="how-to-step"><span class="step-num">2</span>Saisissez le montant convenu avec l'entreprise</div>
                <div class="how-to-step"><span class="step-num">3</span>Ajoutez une référence paiement</div>
                <div class="how-to-step final"><span class="step-num">4</span>Cliquez "Marquer comme payées"</div>
            </div>
        </div>
    </div>
    @endif

    @if($commissions->count())
    <form id="payForm" action="{{ route('boutique.commissions.pay') }}" method="POST">
        @csrf

        <div class="payout-bar" id="payoutBar">
            <span class="payout-count" id="selectionCount">0 sélectionné</span>

            <div style="display:flex;flex-direction:column;">
                <span class="payout-total-lbl">Total à payer</span>
                <div class="payout-total zero" id="payoutTotal">0 <span style="font-size:10px;font-weight:600;font-family:var(--font)">{{ $devise }}</span></div>
            </div>

            <div style="flex:1;min-width:160px;display:flex;flex-direction:column;gap:3px;">
                <label style="font-size:11px;font-weight:700;color:var(--text-2);display:flex;align-items:center;">
                    📎 Référence paiement
                    <div class="tooltip-wrap">
                        <span class="tooltip-icon">?</span>
                        <div class="tooltip-box">Ex : "Orange Money #001", "Virement banque". Sert à retrouver ce paiement.</div>
                    </div>
                </label>
                <input type="text" name="payout_ref" class="payout-input" placeholder="Ex: Orange Money 001, Wave...">
            </div>

            <div style="flex:2;min-width:160px;display:flex;flex-direction:column;gap:3px;">
                <label style="font-size:11px;font-weight:700;color:var(--text-2);display:flex;align-items:center;">
                    📝 Note interne
                    <div class="tooltip-wrap">
                        <span class="tooltip-icon">?</span>
                        <div class="tooltip-box">Visible uniquement par vous et vos admins.</div>
                    </div>
                </label>
                <input type="text" name="payout_note" class="payout-input" placeholder="Ex: Paiement semaine 17 - vérifié">
            </div>

            <button type="button" id="markPaidBtn" class="btn btn-primary"
                    style="flex-shrink:0;padding:10px 20px;font-size:13.5px;align-self:flex-end;opacity:.4;cursor:not-allowed"
                    data-disabled="1">
                ✅ Marquer comme payées
            </button>
        </div>

        <div class="comm-card">
            <div class="comm-card-hd">
                @if($type === 'shop')
                <span class="comm-card-title" style="color:#15803d">🚴 {{ $commissions->total() }} commission{{ $commissions->total() > 1 ? 's' : '' }} livreur boutique en attente</span>
                @else
                <span class="comm-card-title" style="color:#4338ca">🏢 {{ $commissions->total() }} commission{{ $commissions->total() > 1 ? 's' : '' }} entreprise en attente</span>
                @endif
                <label style="display:flex;align-items:center;gap:7px;font-size:12px;font-weight:600;color:var(--muted);cursor:pointer">
                    <input type="checkbox" id="checkAll"> Tout sélectionner
                </label>
            </div>
            <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:36px"></th>
                        <th>Réf commande</th>
                        @if($type === 'shop')
                        <th>Livreur</th>
                        @else
                        <th>Entreprise partenaire</th>
                        <th>Chauffeur</th>
                        @endif
                        <th>📍 Destination</th>
                        <th class="col-montant">Montant commande</th>
                        <th style="background:#fef9ec;color:#92400e;">💰 Commission à payer</th>
                        <th class="col-date">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commissions as $c)
                    @php
                        $orderTotal = $c->order?->total ?? null;
                        $dest       = $c->order?->delivery_destination ?: $c->order?->client?->address;
                        if ($type === 'shop') {
                            $lv       = $c->livreur;
                            $linit    = $lv ? $init($lv->name) : 'LV';
                            /* Batch boutique : nombre de commandes dans ce lot */
                            $bCount      = $c->delivery_batch_id ? ($batchCounts[$c->delivery_batch_id]->cnt ?? 1) : 1;
                            $shopClient  = $c->order?->client?->name ?? null;
                        } else {
                            $company  = $c->order?->deliveryCompany;
                            $driver   = $c->order?->driver?->user;
                            $dInit    = $driver ? $init($driver->name) : 'CH';
                            /* Nombre de commandes dans ce groupe (même client + chauffeur + destination) */
                            $destNorm = strtolower(trim($c->order?->delivery_destination ?? ''));
                            $gKey     = ($c->order?->user_id ?? '') . '::' . ($c->order?->driver_id ?? '') . '::' . $destNorm;
                            $gCount   = $groupCounts[$gKey]->cnt ?? 1;
                            $clientName = $c->order?->client?->name ?? null;
                            /* Montant effectif : commission stockée > frais livraison > prix zone */
                            $effectiveAmount = (float)($c->amount) ?: ((float)($c->order?->delivery_fee) ?: (float)($c->order?->deliveryZone?->price));
                        }
                    @endphp
                    <tr class="comm-row" data-id="{{ $c->id }}">
                        <td data-label="">
                            <input type="checkbox" name="ids[]" value="{{ $c->id }}" class="rowCheckbox">
                        </td>
                        <td data-label="Réf commande">
                            @if($c->order_id)
                                <a href="{{ route('orders.show', $c->order_id) }}" class="ref-link">#{{ $c->order_id }}</a>
                            @else
                                <span class="ref-cell">—</span>
                            @endif
                            @if($type === 'shop' && ($bCount ?? 1) > 1)
                            <div style="margin-top:4px;">
                                <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;background:#f0fdf4;color:#15803d;border:1px solid #86efac;border-radius:5px;padding:2px 7px;white-space:nowrap;">
                                    📦 {{ $bCount }} commandes · 1 trajet
                                </span>
                                @if($shopClient ?? false)
                                <div style="font-size:10px;color:var(--muted);margin-top:2px;">👤 {{ $shopClient }}</div>
                                @endif
                            </div>
                            @endif
                            @if($type === 'company' && isset($gCount) && $gCount > 1)
                            <div style="margin-top:4px;">
                                <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;border-radius:5px;padding:2px 7px;white-space:nowrap;">
                                    📦 {{ $gCount }} commandes · 1 trajet
                                </span>
                                @if($clientName)
                                <div style="font-size:10px;color:var(--muted);margin-top:2px;">👤 {{ $clientName }}</div>
                                @endif
                            </div>
                            @endif
                        </td>
                        @if($type === 'shop')
                        <td data-label="Livreur">
                            <div style="text-align:right">
                                <div class="lv-chip">
                                    <div class="lv-av">{{ $linit }}</div>
                                    {{ $lv?->name ?? '—' }}
                                </div>
                                @if($lv?->phone ?? false)
                                <div style="font-size:10.5px;color:var(--muted);margin-top:3px">📞 {{ $lv->phone }}</div>
                                @endif
                            </div>
                        </td>
                        @else
                        <td data-label="Entreprise">
                            @if($company ?? false)
                            <div style="display:flex;align-items:center;gap:6px">
                                @if($company->image)
                                <img src="{{ asset('storage/' . $company->image) }}" alt="" style="width:22px;height:22px;border-radius:6px;object-fit:cover;flex-shrink:0">
                                @else
                                <div style="width:22px;height:22px;border-radius:6px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:10px;color:#4338ca;flex-shrink:0">🏢</div>
                                @endif
                                <div>
                                    <div style="font-size:12.5px;font-weight:700;color:#3730a3">{{ $company->name }}</div>
                                    @if($company->phone)
                                    <div style="font-size:10px;color:var(--muted)">📞 {{ $company->phone }}</div>
                                    @endif
                                </div>
                            </div>
                            @else
                            <span style="color:var(--muted);font-size:12px">—</span>
                            @endif
                        </td>
                        <td data-label="Chauffeur">
                            @if($driver ?? false)
                            <div class="lv-chip" style="background:#eef2ff;border-color:#c7d2fe">
                                <div class="lv-av" style="background:linear-gradient(135deg,#6366f1,#4338ca)">{{ $dInit }}</div>
                                {{ $driver->name }}
                            </div>
                            @else
                            <span style="color:var(--muted);font-size:12px">—</span>
                            @endif
                        </td>
                        @endif
                        <td data-label="Destination">
                            @if($dest)
                                <div class="dest-cell" style="text-align:right">
                                    📍 {{ $dest }}
                                    <small>{{ $c->order?->delivery_destination ? 'Destination' : 'Adresse client' }}</small>
                                </div>
                            @else
                                <span class="dest-empty">Non renseignée</span>
                            @endif
                        </td>
                        <td data-label="Montant cmd" class="col-montant">
                            @if($orderTotal)
                                <div class="order-amount" style="text-align:right">
                                    {{ number_format($orderTotal, 0, ',', ' ') }} <span style="font-size:10px;color:var(--muted)">{{ $devise }}</span>
                                    <small>Total commande</small>
                                </div>
                            @else
                                <span style="color:var(--muted);font-size:12px">—</span>
                            @endif
                            @if($type === 'company' && ($effectiveAmount ?? 0) > 0)
                                <div style="text-align:right;margin-top:5px;padding-top:5px;border-top:1px dashed #c7d2fe">
                                    <span style="font-size:13px;font-weight:700;color:#4338ca">{{ number_format($effectiveAmount, 0, ',', ' ') }}</span>
                                    <span style="font-size:10px;color:#6366f1;font-weight:600"> {{ $devise }}</span>
                                    <br><small style="color:#6366f1;font-size:10px">Commission fixée</small>
                                </div>
                            @endif
                        </td>
                        <td data-label="Commission à payer" class="td-comm">
                            <div style="display:flex;align-items:center;gap:6px;width:100%">
                                <input type="number"
                                       name="amounts[{{ $c->id }}]"
                                       class="comm-input comm-amount-input"
                                       data-row="{{ $c->id }}"
                                       value="{{ $type === 'company' ? (($effectiveAmount ?? 0) ?: '') : ($c->amount ?: '') }}"
                                       min="0"
                                       placeholder="Ex: 50 000">
                                <span style="font-size:11px;font-weight:700;color:#92400e;white-space:nowrap">{{ $devise }}</span>
                            </div>
                            <div class="row-alert" id="alert-{{ $c->id }}">⚠️ Entrez un montant avant de payer</div>
                        </td>
                        <td data-label="Date" class="col-date">
                            <div class="date-cell" style="text-align:right">
                                {{ $c->created_at->format('d/m/Y') }}
                                <small>{{ $c->created_at->format('H:i') }}</small>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

    </form>

    @else
    <div class="comm-card">
        <div class="empty-state">
            <span class="ico">{{ $type === 'shop' ? '🚴' : '🏢' }}</span>
            <h3>Aucune commission en attente</h3>
            <p>{{ $type === 'shop' ? 'Tous vos livreurs boutique ont été payés.' : 'Toutes les commissions entreprise ont été réglées.' }}</p>
        </div>
    </div>
    @endif

    @else
    {{-- ════════════════════════
         PAYÉES → lecture seule
    ════════════════════════ --}}

    @if($commissions->isEmpty())
    <div class="comm-card">
        <div class="empty-state">
            <span class="ico">{{ $type === 'shop' ? '🚴' : '🏢' }}</span>
            <h3>Aucune commission payée</h3>
            <p>{{ $type === 'shop' ? 'Aucun paiement enregistré pour vos livreurs boutique.' : 'Aucun paiement enregistré pour les entreprises partenaires.' }}</p>
        </div>
    </div>
    @else
    <div class="comm-card">
        <div class="comm-card-hd">
            @if($type === 'shop')
            <span class="comm-card-title" style="color:#15803d">🚴 {{ $commissions->total() }} commission{{ $commissions->total() > 1 ? 's' : '' }} livreur boutique payée{{ $commissions->total() > 1 ? 's' : '' }}</span>
            @else
            <span class="comm-card-title" style="color:#4338ca">🏢 {{ $commissions->total() }} commission{{ $commissions->total() > 1 ? 's' : '' }} entreprise payée{{ $commissions->total() > 1 ? 's' : '' }}</span>
            @endif
        </div>
        <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Réf commande</th>
                    @if($type === 'shop')
                    <th>Livreur</th>
                    @else
                    <th>Entreprise partenaire</th>
                    <th>Chauffeur</th>
                    @endif
                    <th>📍 Destination</th>
                    <th class="col-montant">Montant commande</th>
                    <th>Commission payée</th>
                    <th>Statut</th>
                    <th>Référence paiement</th>
                    <th class="col-date">Payée le</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commissions as $c)
                @php
                    $orderTotal = $c->order?->total ?? null;
                    $dest       = $c->order?->delivery_destination ?: $c->order?->client?->address;
                    if ($type === 'shop') {
                        $lv       = $c->livreur;
                        $linit    = $lv ? $init($lv->name) : 'LV';
                        $bCount   = $c->delivery_batch_id ? ($batchCounts[$c->delivery_batch_id]->cnt ?? 1) : 1;
                        $shopClient = $c->order?->client?->name ?? null;
                    } else {
                        $company = $c->order?->deliveryCompany;
                        $driver  = $c->order?->driver?->user;
                        $dInit   = $driver ? $init($driver->name) : 'CH';
                    }
                @endphp
                <tr>
                    <td data-label="Réf">
                        @if($c->order_id)
                            <a href="{{ route('orders.show', $c->order_id) }}" class="ref-link">#{{ $c->order_id }}</a>
                        @else
                            <span class="ref-cell">—</span>
                        @endif
                        @if($type === 'shop' && ($bCount ?? 1) > 1)
                        <div style="margin-top:4px;">
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;background:#f0fdf4;color:#15803d;border:1px solid #86efac;border-radius:5px;padding:2px 7px;white-space:nowrap;">
                                📦 {{ $bCount }} commandes · 1 trajet
                            </span>
                            @if($shopClient ?? false)
                            <div style="font-size:10px;color:var(--muted);margin-top:2px;">👤 {{ $shopClient }}</div>
                            @endif
                        </div>
                        @endif
                    </td>
                    @if($type === 'shop')
                    <td data-label="Livreur">
                        <div class="lv-chip">
                            <div class="lv-av">{{ $linit }}</div>
                            {{ $lv?->name ?? '—' }}
                        </div>
                    </td>
                    @else
                    <td data-label="Entreprise">
                        @if($company ?? false)
                        <div style="display:flex;align-items:center;gap:6px">
                            @if($company->image)
                            <img src="{{ asset('storage/' . $company->image) }}" alt="" style="width:22px;height:22px;border-radius:6px;object-fit:cover;flex-shrink:0">
                            @else
                            <div style="width:22px;height:22px;border-radius:6px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:10px;color:#4338ca;flex-shrink:0">🏢</div>
                            @endif
                            <div>
                                <div style="font-size:12.5px;font-weight:700;color:#3730a3">{{ $company->name }}</div>
                                @if($company->phone)
                                <div style="font-size:10px;color:var(--muted)">📞 {{ $company->phone }}</div>
                                @endif
                            </div>
                        </div>
                        @else
                        <span style="color:var(--muted);font-size:12px">—</span>
                        @endif
                    </td>
                    <td data-label="Chauffeur">
                        @if($driver ?? false)
                        <div class="lv-chip" style="background:#eef2ff;border-color:#c7d2fe">
                            <div class="lv-av" style="background:linear-gradient(135deg,#6366f1,#4338ca)">{{ $dInit }}</div>
                            {{ $driver->name }}
                        </div>
                        @else
                        <span style="color:var(--muted);font-size:12px">—</span>
                        @endif
                    </td>
                    @endif
                    <td data-label="Destination">
                        @if($dest)
                            <div class="dest-cell" style="text-align:right">📍 {{ $dest }}</div>
                        @else
                            <span class="dest-empty">—</span>
                        @endif
                    </td>
                    <td data-label="Montant cmd" class="col-montant">
                        @if($orderTotal)
                            <div class="order-amount" style="text-align:right">
                                {{ number_format($orderTotal, 0, ',', ' ') }} <span style="font-size:10px;color:var(--muted)">{{ $devise }}</span>
                                <small>Total commande</small>
                            </div>
                        @else
                            <span style="color:var(--muted);font-size:12px">—</span>
                        @endif
                        @if($type === 'company' && $c->amount)
                            <div style="text-align:right;margin-top:5px;padding-top:5px;border-top:1px dashed #c7d2fe">
                                <span style="font-size:13px;font-weight:700;color:#4338ca">{{ number_format($c->amount, 0, ',', ' ') }}</span>
                                <span style="font-size:10px;color:#6366f1;font-weight:600"> {{ $devise }}</span>
                                <br><small style="color:#6366f1;font-size:10px">Commission payée</small>
                            </div>
                        @endif
                    </td>
                    <td data-label="Commission">
                        <div class="amount" style="{{ $type === 'company' ? 'color:#4338ca' : '' }}">
                            {{ number_format($c->amount, 0, ',', ' ') }}
                            <small style="{{ $type === 'company' ? 'color:#6366f1' : '' }}">{{ $devise }}</small>
                        </div>
                    </td>
                    <td data-label="Statut">
                        <span class="pill p-success">✓ Payée</span>
                    </td>
                    <td data-label="Réf paiement">
                        @if($c->payout_ref)
                            <span class="ref-cell">{{ $c->payout_ref }}</span>
                        @else
                            <span style="color:var(--muted);font-size:12px">—</span>
                        @endif
                    </td>
                    <td data-label="Payée le" class="col-date">
                        <div class="date-cell" style="text-align:right">
                            {{ optional($c->paid_at)->format('d/m/Y') ?? '—' }}
                            @if($c->paid_at)<small>{{ $c->paid_at->format('H:i') }}</small>@endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    @endif

    @if($commissions->hasPages())
    <div class="pagination-wrap">{{ $commissions->withQueryString()->links() }}</div>
    @endif

</div>{{-- /page-wrap --}}
</main>
</div>{{-- /dash-wrap --}}

{{-- ══ MODAL CONFIRMATION ══ --}}
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <div class="modal-header">
            <span class="modal-ico">💸</span>
            <div>
                <div class="modal-title">Confirmer le paiement</div>
                <div class="modal-sub">Cette action est irréversible</div>
            </div>
        </div>
        <div class="modal-body">
            <div class="modal-summary">
                <div class="modal-row">
                    <span class="lbl">{{ $type === 'company' ? 'Commissions sélectionnées' : 'Livreurs sélectionnés' }}</span>
                    <span class="val" id="modalCount">—</span>
                </div>
                <div class="modal-row">
                    <span class="lbl">Référence paiement</span>
                    <span class="val" id="modalRef" style="font-family:var(--font);font-size:13px">—</span>
                </div>
                <div class="modal-row total">
                    <span class="lbl">Total à décaisser</span>
                    <span class="val" id="modalTotal">— {{ $devise }}</span>
                </div>
            </div>
            <p style="font-size:12px;color:var(--muted);margin:12px 0 0;line-height:1.6">
                Les commissions sélectionnées seront marquées comme <strong>payées</strong>. Vous ne pourrez plus les modifier.
            </p>
        </div>
        <div class="modal-footer">
            <button class="modal-cancel" onclick="closeConfirmModal()">Annuler</button>
            <button class="modal-confirm" id="modalConfirmBtn">✅ Confirmer le paiement</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ══ SIDEBAR ══ */
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

/* ══ LOGIQUE PAIEMENT ══ */
(function initPayment() {
    var payForm = document.getElementById('payForm');
    if (!payForm) return;

    var checkAll    = document.getElementById('checkAll');
    var markPaidBtn = document.getElementById('markPaidBtn');
    var payoutBar   = document.getElementById('payoutBar');
    var selCount    = document.getElementById('selectionCount');
    var totalEl     = document.getElementById('payoutTotal');

    function getCheckboxes() {
        return Array.from(payForm.querySelectorAll('input.rowCheckbox'));
    }

    function calcTotal() {
        var sum = 0;
        payForm.querySelectorAll('tr.comm-row').forEach(function(row) {
            var cb  = row.querySelector('input.rowCheckbox');
            var inp = row.querySelector('input.comm-amount-input');
            if (cb && cb.checked && inp) {
                sum += parseFloat(inp.value) || 0;
            }
        });
        return sum;
    }

    function updateState() {
        var boxes   = getCheckboxes();
        var checked = boxes.filter(function(cb) { return cb.checked; });
        var n       = checked.length;
        var total   = calcTotal();

        /* activer/désactiver le bouton via data-disabled + style */
        if (markPaidBtn) {
            if (n === 0) {
                markPaidBtn.setAttribute('data-disabled', '1');
                markPaidBtn.style.opacity = '0.4';
                markPaidBtn.style.cursor  = 'not-allowed';
            } else {
                markPaidBtn.removeAttribute('data-disabled');
                markPaidBtn.style.opacity = '1';
                markPaidBtn.style.cursor  = 'pointer';
            }
        }

        if (selCount) {
            selCount.textContent = n > 0 ? (n + ' sélectionné' + (n > 1 ? 's' : '')) : '0 sélectionné';
            selCount.className   = 'payout-count' + (n > 0 ? ' active' : '');
        }

        if (totalEl) {
            var fmt = new Intl.NumberFormat('fr-FR').format(total);
            totalEl.innerHTML = fmt + ' <span style="font-size:10px;font-weight:600;font-family:var(--font)">{{ $devise }}</span>';
            totalEl.classList.toggle('zero', total === 0);
        }

        payForm.querySelectorAll('tr.comm-row').forEach(function(row) {
            var cb = row.querySelector('input.rowCheckbox');
            row.classList.toggle('selected', !!(cb && cb.checked));
        });

        if (payoutBar) payoutBar.classList.toggle('has-selection', n > 0);
    }

    /* ── Délégation sur le formulaire (capture change sur checkboxes + input sur montants) ── */
    payForm.addEventListener('change', function(e) {
        var t = e.target;
        if (t === checkAll) {
            /* Tout sélectionner */
            getCheckboxes().forEach(function(cb) { cb.checked = t.checked; });
            updateState();
        } else if (t.classList.contains('rowCheckbox')) {
            /* Checkbox individuelle */
            if (checkAll) {
                var boxes = getCheckboxes();
                checkAll.checked = boxes.length > 0 && boxes.every(function(c) { return c.checked; });
            }
            updateState();
        }
    });

    payForm.addEventListener('input', function(e) {
        var t = e.target;
        if (t.classList.contains('comm-amount-input')) {
            var rowId   = t.dataset.row;
            var alertEl = document.getElementById('alert-' + rowId);
            if (parseFloat(t.value) > 0) {
                t.classList.remove('warn');
                if (alertEl) alertEl.classList.remove('visible');
            }
            updateState();
        }
    });

    /* ── Bouton payer → listener direct ── */
    if (markPaidBtn) {
        markPaidBtn.addEventListener('click', function() {
            if (markPaidBtn.getAttribute('data-disabled') === '1') return;
            openConfirmModal();
        });
    }

    /* init */
    updateState();
})();

/* ══ MODAL CONFIRMATION ══ */
function openConfirmModal() {
    var payForm2     = document.getElementById('payForm');
    if (!payForm2) return;
    var rowCheckboxes = Array.from(payForm2.querySelectorAll('input.rowCheckbox'));
    var checked       = rowCheckboxes.filter(function(cb) { return cb.checked; });

    if (checked.length === 0) return;

    /* Validation : vérifier montants */
    var hasError = false;
    checked.forEach(function(cb) {
        var row    = cb.closest('.comm-row');
        var rowId  = row ? row.dataset.id : null;
        var inp    = row ? row.querySelector('input.comm-amount-input') : null;
        var alertEl = rowId ? document.getElementById('alert-' + rowId) : null;
        if (!inp || !(parseFloat(inp.value) > 0)) {
            if (inp) inp.classList.add('warn');
            if (alertEl) alertEl.classList.add('visible');
            hasError = true;
        }
    });
    if (hasError) {
        var firstWarn = payForm2.querySelector('input.comm-input.warn');
        if (firstWarn) firstWarn.scrollIntoView({ behavior: 'smooth', block: 'center' });
        /* Afficher une alerte globale visible */
        var existing = document.getElementById('globalAmountAlert');
        if (!existing) {
            var msg = document.createElement('div');
            msg.id = 'globalAmountAlert';
            msg.style.cssText = 'position:fixed;top:70px;left:50%;transform:translateX(-50%);background:#ef4444;color:#fff;padding:12px 24px;border-radius:10px;font-weight:700;font-size:14px;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.3)';
            msg.textContent = '⚠️ Saisissez le montant dans chaque ligne cochée avant de payer';
            document.body.appendChild(msg);
            setTimeout(function() { msg.remove(); }, 3500);
        }
        return;
    }

    /* Remplir le modal */
    let total = 0;
    checked.forEach(cb => {
        const row = cb.closest('.comm-row');
        const inp = row?.querySelector('.comm-amount-input');
        total += parseFloat(inp?.value || 0);
    });
    const ref    = document.querySelector('input[name="payout_ref"]')?.value || '(aucune)';
    const fmt    = new Intl.NumberFormat('fr-FR').format(total);

    var unitLabel = '{{ $type === "company" ? "commission" : "livreur" }}';
    document.getElementById('modalCount').textContent = checked.length + ' ' + unitLabel + (checked.length > 1 ? 's' : '');
    document.getElementById('modalRef').textContent   = ref;
    document.getElementById('modalTotal').textContent = fmt + ' {{ $devise }}';

    document.getElementById('confirmModal').classList.add('open');
    document.body.style.overflow = 'hidden';

    /* Confirmer → soumettre le form */
    document.getElementById('modalConfirmBtn').onclick = function() {
        this.textContent = '⏳ Traitement…';
        this.disabled = true;
        payForm2.submit();
    };
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('open');
    document.body.style.overflow = '';
}

/* Fermer le modal en cliquant en dehors */
document.getElementById('confirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeConfirmModal();
});
</script>
@endpush
