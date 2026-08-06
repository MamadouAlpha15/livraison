{{--
    resources/views/boutique/promo-codes/index.blade.php
    Route     : GET    /boutique/promo-codes          → Boutique\PromoCodeController@index
    Route     : POST   /boutique/promo-codes           → @store
    Route     : PUT    /boutique/promo-codes/{promoCode} → @update
    Route     : DELETE /boutique/promo-codes/{promoCode} → @destroy
    Route     : POST   /boutique/promo-codes/{promoCode}/toggle → @toggleActive
    Variables :
      $promoCodes → Collection<PromoCode>
      $shop       → Shop
--}}

@extends('layouts.app')
@section('title', 'Codes promo · ' . $shop->name)
@php $bodyClass = 'is-dashboard'; @endphp

@php
$_s = 'stroke-width="1.75"';
$I = [
'dash_nav'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
'msg_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
'box_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
'tag_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
'percent_nav' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>',
'users_nav' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
'team_nav'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>',
'bike_nav'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="17" r="3"/><circle cx="19" cy="17" r="3"/><path d="M12 4h4l2 6"/><path d="M6.8 17H12l5-9"/><path d="M5 14l2.5-5H12"/></svg>',
'bldg_nav'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><rect x="9" y="14" width="6" height="7"/></svg>',
'wallet_nav'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg>',
'card_nav'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
'chart_nav' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>',
'list_nav'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
'gear_nav'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
'hdp_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$_s.' stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
'plus_ico'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
'trash_ico' => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>',
'edit_ico'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z"/></svg>',
'empty_ico' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>',
];
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=block" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --brand: #6366f1; --brand-dk: #4f46e5; --brand-lt: #e0e7ff; --brand-mlt: #eef2ff;
    --sb-bg: #0e0e16; --sb-border: rgba(255,255,255,.08);
    --sb-act: rgba(99,102,241,.52); --sb-hov: rgba(255,255,255,.07);
    --bg: #f8fafc; --surface: #ffffff; --border: #e2e8f0; --border-dk: #cbd5e1;
    --text: #0f172a; --text-2: #475569; --muted: #94a3b8;
    --red: #ef4444; --red-lt: #fef2f2; --green: #10b981; --green-lt: #ecfdf5;
    --font: 'Plus Jakarta Sans', sans-serif; --mono: 'JetBrains Mono', monospace;
    --r: 14px; --r-sm: 9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06); --shadow: 0 4px 16px rgba(0,0,0,.07);
    --sb-w: 232px; --top-h: 58px;
}
html { font-family: var(--font); }
body { background: var(--bg); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

.dash-wrap { display: flex; min-height: 100vh; }
.dash-wrap .main { margin-left: var(--sb-w); flex: 1; min-width: 0; }
.sidebar {
    background: linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);
    border-right: 1px solid rgba(99,102,241,.15); box-shadow: 6px 0 30px rgba(0,0,0,.35);
    display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0;
    width: var(--sb-w); overflow-y: scroll; scrollbar-width: thin; scrollbar-color: rgba(99,102,241,.35) transparent; z-index: 40;
}
.sidebar::-webkit-scrollbar { width: 3px; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(99,102,241,.35); border-radius: 3px; }
.sb-brand { padding: 18px 16px 14px; border-bottom: 1px solid var(--sb-border); flex-shrink: 0; position: relative; }
.sb-close { display: none; position: absolute; top: 14px; right: 12px; width: 30px; height: 30px; border-radius: 8px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.6); font-size: 18px; cursor: pointer; align-items: center; justify-content: center; }
@media (max-width: 900px) { .sb-close { display: flex; } }
.sb-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.sb-logo-icon { width: 36px; height: 36px; border-radius: 9px; overflow: hidden; flex-shrink: 0; }
.sb-shop-name { font-size: 14.5px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; color: #fff; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 10.5px; color: rgba(255,255,255,.62); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: #6ee7b7; flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px #6ee7b7; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }
.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; }
.sb-section { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.8px; color: rgba(255,255,255,.48); padding: 16px 10px 5px; font-weight: 800; }
.sb-item { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); text-decoration: none; transition: background .15s, color .15s; position: relative; }
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-item.active { background: var(--sb-act); color: #fff; box-shadow: 0 2px 12px rgba(99,102,241,.25); }
.sb-item .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); }
.sb-item.active .ico { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.2); }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); }
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); cursor: pointer; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); }
.sb-group-toggle .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.32); transition: transform .2s; }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.1); margin-top: 2px; margin-bottom: 4px; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 13px; font-weight: 500; padding: 6px 10px; color: rgba(255,255,255,.62); }
.sb-sub .sb-item.active { color: #fff; background: var(--sb-act); font-weight: 600; }
.sb-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); text-decoration: none; }
.sb-user:hover { background: rgba(255,255,255,.06); }
.sb-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg,#6366f1,#4338ca); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0; }
.sb-uname { font-size: 13px; font-weight: 700; color: #fff; }
.sb-urole { font-size: 10.5px; color: rgba(255,255,255,.52); margin-top: 1px; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.92); font-size: 12.5px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; text-align: left; }
.sb-logout:hover { background: rgba(220,38,38,.18); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.18); flex-shrink: 0; }
.sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 39; }

.main { display: flex; flex-direction: column; min-width: 0; }
.topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 22px; height: var(--top-h); display: flex; align-items: center; gap: 12px; position: sticky; top: 0; z-index: 30; box-shadow: var(--shadow-sm); }
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); font-size: 20px; }
.tb-info { flex: 1; min-width: 0; }
.tb-title { font-size: 14px; font-weight: 700; color: var(--text); }
.tb-sub { font-size: 11px; color: var(--muted); margin-top: 1px; }
.page-wrap { padding: 22px 22px 60px; }
.page-hd { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 22px; flex-wrap: wrap; }
.page-title { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -.4px; margin: 0 0 4px; }
.page-sub { font-size: 13px; color: var(--muted); margin: 0; }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: var(--r-sm); font-size: 13px; font-weight: 700; font-family: var(--font); border: 1px solid var(--border-dk); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-sm { padding: 6px 11px; font-size: 12px; }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(99,102,241,.3); }
.btn-primary:hover { background: var(--brand-dk); color: #fff; }
.btn-danger { background: var(--red-lt); color: #991b1b; border-color: #fca5a5; }
.btn-danger:hover { background: var(--red); color: #fff; }

.flash { padding: 10px 14px; border-radius: var(--r-sm); border: 1px solid; font-size: 13.5px; font-weight: 600; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
.flash-success { background: #eef2ff; border-color: #a5b4fc; color: #3730a3; }
.flash-danger { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }

.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); }
.tbl-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl thead th { padding: 11px 14px; text-align: left; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; background: var(--bg); border-bottom: 1px solid var(--border); white-space: nowrap; }
.tbl tbody td { padding: 12px 14px; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; }
.code-chip { font-family: var(--mono); font-weight: 800; font-size: 13px; background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); padding: 3px 10px; border-radius: 8px; letter-spacing: .5px; }
.pill { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
.p-on { background: var(--green-lt); color: #065f46; }
.p-off { background: #f1f5f9; color: var(--muted); }
.p-expired { background: var(--red-lt); color: #991b1b; }
.actions-cell { display: flex; gap: 6px; }
.icon-btn { width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--border-dk); background: var(--surface); color: var(--text-2); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all .15s; }
.icon-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-mlt); }
.icon-btn.danger:hover { border-color: var(--red); color: var(--red); background: var(--red-lt); }

.empty-state { padding: 56px 20px; text-align: center; }
.empty-state .ico { display: flex; align-items: center; justify-content: center; margin-bottom: 12px; opacity: .3; }
.empty-state h3 { font-size: 15px; font-weight: 700; color: var(--text); margin: 0 0 6px; }
.empty-state p { font-size: 13px; color: var(--muted); margin: 0 0 16px; }

/* ── Modal formulaire ── */
.modal-overlay { display: none; position: fixed; inset: 0; z-index: 500; background: rgba(0,0,0,.55); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal-box { background: var(--surface); border-radius: 20px; padding: 0; width: 100%; max-width: 460px; margin: 20px; box-shadow: 0 24px 60px rgba(0,0,0,.25); max-height: 90vh; overflow-y: auto; }
.modal-header { padding: 20px 24px 14px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.modal-title { font-size: 16px; font-weight: 800; color: var(--text); margin: 0; }
.modal-close { background: none; border: none; font-size: 18px; color: var(--muted); cursor: pointer; padding: 4px; }
.modal-body { padding: 18px 24px; display: flex; flex-direction: column; gap: 14px; }
.modal-footer { padding: 0 24px 22px; display: flex; gap: 10px; }
.field { display: flex; flex-direction: column; gap: 5px; }
.field label { font-size: 11.5px; font-weight: 700; color: var(--text-2); text-transform: uppercase; letter-spacing: .4px; }
.field input, .field select { padding: 10px 12px; border-radius: var(--r-sm); border: 1.5px solid var(--border-dk); font-size: 13.5px; font-family: var(--font); color: var(--text); background: var(--bg); outline: none; transition: border-color .15s; }
.field input:focus, .field select:focus { border-color: var(--brand); background: var(--surface); }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field-hint { font-size: 11px; color: var(--muted); }
.field-err { font-size: 11.5px; color: var(--red); font-weight: 600; }
.modal-cancel { flex: 1; padding: 11px; border-radius: var(--r-sm); border: 1.5px solid var(--border); background: var(--surface); font-size: 13.5px; font-weight: 700; font-family: var(--font); cursor: pointer; color: var(--text-2); }
.modal-cancel:hover { border-color: var(--border-dk); background: var(--bg); }
.modal-confirm { flex: 2; padding: 11px; border-radius: var(--r-sm); border: none; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-size: 13.5px; font-weight: 700; font-family: var(--font); cursor: pointer; box-shadow: 0 4px 14px rgba(99,102,241,.35); }
.modal-confirm:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
.modal-confirm.danger { background: linear-gradient(135deg, #ef4444, #b91c1c); box-shadow: 0 4px 14px rgba(239,68,68,.35); }
.modal-confirm.danger:hover { background: linear-gradient(135deg, #dc2626, #991b1b); }

@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
}
@media (max-width: 640px) {
    .page-wrap { padding: 14px 12px 40px; }
    .field-row { grid-template-columns: 1fr; }
    .tbl thead { display: none; }
    .tbl, .tbl tbody { display: block; }
    .tbl tbody tr { display: block; border: 1px solid var(--border); border-radius: 12px; margin-bottom: 12px; overflow: hidden; background: var(--surface); box-shadow: var(--shadow-sm); }
    .tbl tbody td { display: flex !important; justify-content: space-between; align-items: center; padding: 10px 14px; border-bottom: 1px solid #f3f4f6; font-size: 13px; gap: 10px; }
    .tbl tbody td:last-child { border-bottom: none; }
    .tbl tbody td::before { content: attr(data-label); font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; flex-shrink: 0; }
}
</style>
@endpush

@section('content')
@php $isPro = $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture(); @endphp

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
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px"><span class="ico">{!! $I['dash_nav'] !!}</span> Tableau de bord</a>
        <div class="sb-section">Boutique</div>
        <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">{!! $I['msg_nav'] !!}</span> Messages</a>
        <a href="{{ route('boutique.orders.index') }}" class="sb-item"><span class="ico">{!! $I['box_nav'] !!}</span> Commandes</a>
        <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">{!! $I['tag_nav'] !!}</span> Produits</a>
        <a href="{{ route('boutique.promo-codes.index') }}" class="sb-item active"><span class="ico">{!! $I['percent_nav'] !!}</span> Codes promo</a>
        <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">{!! $I['users_nav'] !!}</span> Clients</a>
        <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">{!! $I['team_nav'] !!}</span> Équipe</a>
        <div class="sb-section">Livraison</div>
        <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">{!! $I['bike_nav'] !!}</span> Livreurs</a>
        <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">{!! $I['bldg_nav'] !!}</span> Partenaires</a>
        <div class="sb-section">Finances</div>
        <div class="sb-group">
            <button class="sb-group-toggle open" onclick="toggleGroup(this)" type="button">
                <span class="ico">{!! $I['wallet_nav'] !!}</span> Finances & Rapports
                <span class="sb-arrow" style="transform:rotate(90deg);color:rgba(255,255,255,.5)">▶</span>
            </button>
            <div class="sb-sub open">
                <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">{!! $I['card_nav'] !!}</span> Paiements</a>
                <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">{!! $I['chart_nav'] !!}</span> Commissions</a>
                <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">{!! $I['list_nav'] !!}</span> Rapports</a>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">{!! $I['gear_nav'] !!}</span> Paramètres</a>
                @endif
            </div>
        </div>
        <div class="sb-section">Aide</div>
        <a href="{{ route('support.index') }}" class="sb-item"><span class="ico">{!! $I['hdp_nav'] !!}</span> Support</a>
    </nav>
    <div class="sb-footer">
        <a href="{{ route('profile.edit') }}" class="sb-user">
            <div class="sb-av">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
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
            <div class="tb-title">🏷️ Codes promo</div>
            <div class="tb-sub">{{ $shop->name }}</div>
        </div>
    </div>

<div class="page-wrap">

    <div class="page-hd">
        <div>
            <h1 class="page-title">🏷️ Codes promo</h1>
            <p class="page-sub">Créez des réductions pour attirer et fidéliser vos clients.</p>
        </div>
        <button class="btn btn-primary" onclick="openCreateModal()">{!! $I['plus_ico'] !!} Nouveau code</button>
    </div>

    @foreach(['success','danger'] as $flashType)
        @if(session($flashType))
        <div class="flash flash-{{ $flashType }}"><span>{{ $flashType === 'success' ? '✓' : '✕' }}</span> {{ session($flashType) }}</div>
        @endif
    @endforeach
    @if($errors->any())
        <div class="flash flash-danger"><span>✕</span> {{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Réduction</th>
                        <th>Achat min.</th>
                        <th>Utilisations</th>
                        <th>Expiration</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promoCodes as $promo)
                    @php
                        $expired = $promo->expires_at && $promo->expires_at->isPast();
                        $exhausted = $promo->max_uses !== null && $promo->uses_count >= $promo->max_uses;
                    @endphp
                    <tr>
                        <td data-label="Code"><span class="code-chip">{{ $promo->code }}</span></td>
                        <td data-label="Réduction">{{ $promo->type === 'percent' ? '-' . $promo->value . '%' : '-' . number_format($promo->value, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF') }}</td>
                        <td data-label="Achat min.">{{ $promo->min_purchase_amount ? number_format($promo->min_purchase_amount, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF') : '—' }}</td>
                        <td data-label="Utilisations">{{ $promo->uses_count }}{{ $promo->max_uses ? ' / ' . $promo->max_uses : '' }}</td>
                        <td data-label="Expiration">{{ $promo->expires_at ? $promo->expires_at->format('d/m/Y') : '—' }}</td>
                        <td data-label="Statut">
                            @if($expired)
                                <span class="pill p-expired">Expiré</span>
                            @elseif($exhausted)
                                <span class="pill p-expired">Épuisé</span>
                            @elseif($promo->is_active)
                                <span class="pill p-on">Actif</span>
                            @else
                                <span class="pill p-off">Inactif</span>
                            @endif
                        </td>
                        <td data-label="">
                            <div class="actions-cell">
                                <button type="button" class="icon-btn" title="Modifier" onclick='openEditModal(@json($promo))'>{!! $I['edit_ico'] !!}</button>
                                <form method="POST" action="{{ route('boutique.promo-codes.toggle', $promo) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="icon-btn" title="{{ $promo->is_active ? 'Désactiver' : 'Activer' }}">{{ $promo->is_active ? '⏸' : '▶' }}</button>
                                </form>
                                <button type="button" class="icon-btn danger" title="Supprimer" onclick='openDeleteModal({{ $promo->id }}, @json($promo->code))'>{!! $I['trash_ico'] !!}</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="ico">{!! $I['empty_ico'] !!}</div>
                                <h3>Aucun code promo pour le moment</h3>
                                <p>Créez votre premier code pour lancer une promotion.</p>
                                <button class="btn btn-primary" onclick="openCreateModal()">{!! $I['plus_ico'] !!} Nouveau code</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>
</div>

{{-- ══ MODAL CRÉER / MODIFIER ══ --}}
<div class="modal-overlay" id="formModal">
    <div class="modal-box">
        <form method="POST" id="promoForm" action="{{ route('boutique.promo-codes.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="modal-header">
                <h3 class="modal-title" id="formTitle">Nouveau code promo</h3>
                <button type="button" class="modal-close" onclick="closeFormModal()">✕</button>
            </div>
            <div class="modal-body">
                <div class="field">
                    <label for="f_code">Code</label>
                    <input type="text" name="code" id="f_code" placeholder="Ex : PROMO10" maxlength="30" required style="text-transform:uppercase">
                    <span class="field-hint">Le client entrera ce code au moment de commander.</span>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="f_type">Type de réduction</label>
                        <select name="type" id="f_type">
                            <option value="percent">Pourcentage (%)</option>
                            <option value="fixed">Montant fixe ({{ $shop->currency ?? 'GNF' }})</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="f_value">Valeur</label>
                        <input type="number" name="value" id="f_value" min="1" required>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="f_min">Achat minimum <span style="text-transform:none;font-weight:500">(optionnel)</span></label>
                        <input type="number" name="min_purchase_amount" id="f_min" min="0">
                    </div>
                    <div class="field">
                        <label for="f_max">Nb max d'utilisations <span style="text-transform:none;font-weight:500">(optionnel)</span></label>
                        <input type="number" name="max_uses" id="f_max" min="1">
                    </div>
                </div>
                <div class="field">
                    <label for="f_expires">Date d'expiration <span style="text-transform:none;font-weight:500">(optionnel)</span></label>
                    <input type="date" name="expires_at" id="f_expires">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-cancel" onclick="closeFormModal()">Annuler</button>
                <button type="submit" class="modal-confirm">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ MODAL SUPPRESSION ══ --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box" style="max-width:400px">
        <form method="POST" id="deleteForm">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h3 class="modal-title">Supprimer ce code ?</h3>
                <button type="button" class="modal-close" onclick="closeDeleteModal()">✕</button>
            </div>
            <div class="modal-body">
                <p style="font-size:13.5px;color:var(--text-2);margin:0">Le code <strong id="deleteCodeLabel"></strong> sera définitivement supprimé. Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-cancel" onclick="closeDeleteModal()">Annuler</button>
                <button type="submit" class="modal-confirm danger">Supprimer</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleGroup(btn) {
    const sub = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');
    if (!isOpen) { sub.classList.add('open'); btn.classList.add('open'); }
    else { sub.classList.remove('open'); btn.classList.remove('open'); }
}
(function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sbOverlay');
    document.getElementById('btnMenu')?.addEventListener('click', () => { sidebar.classList.add('open'); overlay.classList.add('open'); });
    overlay?.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });
    document.getElementById('btnCloseSidebar')?.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });
})();

const STORE_URL = @json(route('boutique.promo-codes.store'));

function openCreateModal() {
    document.getElementById('promoForm').reset();
    document.getElementById('promoForm').action = STORE_URL;
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('formTitle').textContent = 'Nouveau code promo';
    document.getElementById('formModal').classList.add('open');
}

function openEditModal(promo) {
    const form = document.getElementById('promoForm');
    form.action = STORE_URL + '/' + promo.id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('formTitle').textContent = 'Modifier le code promo';
    document.getElementById('f_code').value = promo.code;
    document.getElementById('f_type').value = promo.type;
    document.getElementById('f_value').value = promo.value;
    document.getElementById('f_min').value = promo.min_purchase_amount ?? '';
    document.getElementById('f_max').value = promo.max_uses ?? '';
    document.getElementById('f_expires').value = promo.expires_at ? promo.expires_at.substring(0, 10) : '';
    document.getElementById('formModal').classList.add('open');
}

function closeFormModal() { document.getElementById('formModal').classList.remove('open'); }

function openDeleteModal(id, code) {
    document.getElementById('deleteForm').action = STORE_URL + '/' + id;
    document.getElementById('deleteCodeLabel').textContent = code;
    document.getElementById('deleteModal').classList.add('open');
}
function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('open'); }

@if($errors->any())
document.getElementById('formModal').classList.add('open');
@endif
</script>
@endpush
