{{--
    resources/views/boutique/livreurs/index.blade.php
    Route : GET /boutique/livreurs → Boutique\LivreurController@index → boutique.livreurs.index
    Variables :
      $livreurs    → Collection<User>  (tous les livreurs de la boutique)
      $enLigneNow  → Collection<User>  (is_available = true)
      $horsLigne   → Collection<User>  (is_available = false)
      $total       → int
      $enLigne     → int
      $enCourse    → int
      $horsligne   → int
      $shop        → Shop
      $devise      → string
--}}

@extends('layouts.app')
@section('title', 'Livreurs · ' . $shop->name)
@php $bodyClass = 'is-dashboard'; @endphp

@php
$_s  = 'fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"';
$_s2 = 'fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"';
$_s1 = 'fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"';
$I = [
    /* sidebar nav 17px */
    'dash_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>',
    'msg_nav'    => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    'box_nav'    => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
    'tag_nav'    => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
    'users_nav'  => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'team_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="17"/><line x1="9.5" y1="14.5" x2="14.5" y2="14.5"/></svg>',
    'bike_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1"/><path d="M5.5 17.5L11 5.5 15 10l2-3h1.5"/><line x1="11" y1="5.5" x2="7" y2="5.5"/></svg>',
    'bldg_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'wallet_nav' => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M16 13a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" fill="currentColor" stroke="none"/></svg>',
    'card_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
    'chart_nav'  => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    'list_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
    'gear_nav'   => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    'hdp_nav'    => '<svg width="17" height="17" viewBox="0 0 24 24" '.$_s.'><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
    /* topbar */
    'bike_tb'    => '<svg width="15" height="15" viewBox="0 0 24 24" '.$_s2.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1"/><path d="M5.5 17.5L11 5.5 15 10l2-3h1.5"/><line x1="11" y1="5.5" x2="7" y2="5.5"/></svg>',
    'plus_tb'    => '<svg width="13" height="13" viewBox="0 0 24 24" '.$_s2.'><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
    /* stat cards 22px */
    'bike_stat'    => '<svg width="22" height="22" viewBox="0 0 24 24" '.$_s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1"/><path d="M5.5 17.5L11 5.5 15 10l2-3h1.5"/><line x1="11" y1="5.5" x2="7" y2="5.5"/></svg>',
    'online_stat'  => '<svg width="22" height="22" viewBox="0 0 24 24" '.$_s.'><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>',
    'box_stat'     => '<svg width="22" height="22" viewBox="0 0 24 24" '.$_s.'><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
    'offline_stat' => '<svg width="22" height="22" viewBox="0 0 24 24" '.$_s.'><line x1="1" y1="1" x2="23" y2="23"/><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"/><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/><path d="M10.71 5.05A16 16 0 0 1 22.56 9"/><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>',
    /* section header dots 10px */
    'dot_online'  => '<svg width="10" height="10" viewBox="0 0 10 10"><circle cx="5" cy="5" r="5" fill="#10b981"/></svg>',
    'dot_offline' => '<svg width="10" height="10" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4" fill="none" stroke="#94a3b8" stroke-width="1.5"/></svg>',
    'list_hd'     => '<svg width="14" height="14" viewBox="0 0 24 24" '.$_s2.'><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
    /* phone / trash / plus */
    'phone_lnk'  => '<svg width="11" height="11" viewBox="0 0 24 24" '.$_s2.'><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.41 2 2 0 0 1 3.6 1.2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.84a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    'trash_btn'  => '<svg width="13" height="13" viewBox="0 0 24 24" '.$_s2.'><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
    /* mobile labels 11px */
    'box_lbl'    => '<svg width="11" height="11" viewBox="0 0 24 24" '.$_s2.'><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
    'bolt_lbl'   => '<svg width="11" height="11" viewBox="0 0 24 24" '.$_s2.'><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
    /* empty states */
    'bike_empty_sm' => '<svg width="36" height="36" viewBox="0 0 24 24" '.$_s1.' style="opacity:.25"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1"/><path d="M5.5 17.5L11 5.5 15 10l2-3h1.5"/><line x1="11" y1="5.5" x2="7" y2="5.5"/></svg>',
    'bike_empty'    => '<svg width="44" height="44" viewBox="0 0 24 24" '.$_s1.' style="opacity:.3"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1"/><path d="M5.5 17.5L11 5.5 15 10l2-3h1.5"/><line x1="11" y1="5.5" x2="7" y2="5.5"/></svg>',
];
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
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
    --online:     #10b981; --busy:      #f59e0b; --offline: #94a3b8;
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r: 14px; --r-sm: 9px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.06);
    --shadow:     0 4px 20px rgba(0,0,0,.09);
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
.sb-shop-name { font-size: 14.5px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; letter-spacing: -.3px; color: #fff; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 10.5px; color: var(--sb-txt); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: #6ee7b7; flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px #6ee7b7; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }
.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; overflow: visible; }
.sb-section { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.8px; color: rgba(255,255,255,.48); padding: 16px 10px 5px; font-weight: 800; }
.sb-item { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); text-decoration: none; letter-spacing: -.1px; transition: background .15s, color .15s; position: relative; }
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-item.active { background: var(--sb-act); color: #fff; box-shadow: 0 2px 12px rgba(99,102,241,.25); }
.sb-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: #a5b4fc; border-radius: 0 3px 3px 0; box-shadow: 2px 0 8px rgba(165,180,252,.5); }
.sb-item .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); transition: background .15s; }
.sb-item:hover .ico { background: rgba(255,255,255,.09); }
.sb-item.active .ico { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.2); }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); min-width: 20px; text-align: center; }
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); cursor: pointer; transition: background .15s, color .15s; user-select: none; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); }
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-group-toggle.open { color: #fff; background: rgba(255,255,255,.05); }
.sb-group-toggle .ico { font-size: 13px; width: 26px; height: 26px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); transition: background .15s; }
.sb-group-toggle:hover .ico { background: rgba(255,255,255,.09); }
.sb-group-toggle.open .ico { background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.14); }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.32); transition: transform .2s; flex-shrink: 0; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.6); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.1); margin-top: 2px; margin-bottom: 4px; overflow: visible; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 13px; font-weight: 500; padding: 6px 10px; color: rgba(255,255,255,.62); letter-spacing: 0; }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.92); }
.sb-sub .sb-item.active { color: #fff; background: var(--sb-act); font-weight: 600; }
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
.sb-uname { font-size: 13px; font-weight: 700; color: #fff; letter-spacing: -.2px; }
.sb-urole { font-size: 10.5px; color: rgba(255,255,255,.52); margin-top: 1px; font-weight: 500; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.92); font-size: 12.5px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s, color .15s, border-color .15s; text-align: left; }
.sb-logout:hover { background: rgba(220,38,38,.18); border-color: rgba(220,38,38,.35); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 7px; background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.18); flex-shrink: 0; }
.sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 39; }

/* ══ MAIN ══ */
.main { display: flex; flex-direction: column; min-width: 0; }
.topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 22px; height: var(--top-h); display: flex; align-items: center; gap: 12px; position: sticky; top: 0; z-index: 30; box-shadow: var(--shadow-sm); }
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); font-size: 20px; }
.tb-info { flex: 1; min-width: 0; }
.tb-title { font-size: 14px; font-weight: 700; color: var(--text); }
.tb-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* ══ CONTENU ══ */
.content { padding: 22px 22px 60px; }

/* Flash */
.flash { padding: 10px 14px; border-radius: var(--r-sm); border: 1px solid; font-size: 13.5px; font-weight: 600; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
.flash-success { background: #eef2ff; border-color: #a5b4fc; color: #3730a3; }
.flash-danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }

/* ══ KPI STATS ══ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 18px 20px;
    box-shadow: var(--shadow-sm);
    display: flex; align-items: center; gap: 14px;
    transition: box-shadow .2s, transform .2s;
    position: relative; overflow: hidden;
}
.stat-card:hover { box-shadow: var(--shadow); transform: translateY(-2px); }
.stat-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; background: var(--sc-color, var(--brand));
}
.stat-ico {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    background: var(--sc-bg, var(--brand-mlt));
}
.stat-val { font-size: 26px; font-weight: 800; font-family: var(--mono); color: var(--text); line-height: 1; letter-spacing: -.5px; }
.stat-lbl { font-size: 11px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-top: 3px; }

/* ══ SECTION TITRE ══ */
.sec-hd {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; gap: 12px;
}
.sec-title {
    font-size: 13px; font-weight: 800; color: var(--text);
    display: flex; align-items: center; gap: 8px;
    letter-spacing: -.2px;
}
.sec-badge {
    background: var(--brand-mlt); color: var(--brand-dk);
    border: 1px solid var(--brand-lt);
    font-size: 10px; font-weight: 700; font-family: var(--mono);
    padding: 2px 8px; border-radius: 20px;
}
.sec-badge.warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }

/* ══ CARTES LIVREURS EN LIGNE ══ */
.online-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 32px;
}

.driver-card-online {
    background: var(--surface);
    border: 1px solid var(--brand-lt);
    border-radius: var(--r);
    padding: 20px 18px;
    text-align: center;
    box-shadow: 0 2px 12px rgba(99,102,241,.1);
    position: relative; overflow: hidden;
    transition: box-shadow .2s, transform .2s;
}
.driver-card-online:hover { box-shadow: 0 6px 24px rgba(99,102,241,.18); transform: translateY(-3px); }

/* Halo animé en fond pour les livreurs en ligne */
.driver-card-online::before {
    content: '';
    position: absolute; top: -20px; right: -20px;
    width: 80px; height: 80px; border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,.12) 0%, transparent 70%);
    animation: halopulse 3s ease-in-out infinite;
}
@keyframes halopulse { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.3);opacity:.6} }

.driver-av {
    width: 56px; height: 56px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 800; color: #fff;
    margin: 0 auto 12px;
    position: relative; z-index: 1;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
}
/* Indicateur de statut sur l'avatar */
.driver-av::after {
    content: '';
    position: absolute; bottom: 2px; right: 2px;
    width: 12px; height: 12px; border-radius: 50%;
    background: var(--dot-color, var(--online));
    border: 2px solid var(--surface);
    box-shadow: 0 0 6px var(--dot-color, var(--online));
}

.driver-name {
    font-size: 13.5px; font-weight: 700; color: var(--text);
    margin-bottom: 4px; line-height: 1.2;
}
.driver-phone { font-size: 11.5px; color: var(--muted); margin-bottom: 10px; }

.driver-status-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 10.5px; font-weight: 700;
    padding: 4px 10px; border-radius: 20px;
}
.status-online  { background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); }
.status-busy    { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.status-offline { background: #f3f6f4; color: var(--muted); border: 1px solid var(--border); }

/* Indicateur pulsant pour les en ligne */
.dot-pulse {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--brand);
    animation: dotpulse 1.8s ease-in-out infinite;
    flex-shrink: 0;
}
@keyframes dotpulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.7)} }
.dot-busy    { background: var(--busy); animation: dotpulse 1s ease-in-out infinite; }
.dot-offline { background: var(--offline); animation: none; }

.driver-stats {
    display: flex; justify-content: center; gap: 14px;
    margin-top: 12px; padding-top: 10px;
    border-top: 1px solid var(--border);
    font-size: 10.5px; color: var(--muted);
}
.driver-stat-val { font-family: var(--mono); font-weight: 700; color: var(--text); font-size: 14px; display: block; }

/* ══ TABLE TOUS LES LIVREURS ══ */
.all-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm);
}
.all-card-hd {
    padding: 12px 18px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--bg);
}
.all-card-title { font-size: 13px; font-weight: 700; color: var(--text); }

/* Table desktop */
.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl thead th { padding: 10px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; background: var(--bg); border-bottom: 1px solid var(--border); white-space: nowrap; }
.tbl tbody td { padding: 12px 16px; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; }

.lv-cell { display: flex; align-items: center; gap: 10px; }
.lv-av-sm { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; position: relative; }
.lv-av-sm .status-dot { position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; border-radius: 50%; border: 2px solid var(--surface); }
.lv-av-sm .status-dot.online  { background: var(--online);  box-shadow: 0 0 4px var(--online); }
.lv-av-sm .status-dot.busy    { background: var(--busy);    box-shadow: 0 0 4px var(--busy); }
.lv-av-sm .status-dot.offline { background: var(--offline); }

.lv-name { font-size: 13px; font-weight: 600; color: var(--text); }
.lv-email { font-size: 11px; color: var(--muted); margin-top: 1px; }
.lv-phone { font-size: 11px; color: var(--muted); }

/* Boutons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: var(--r-sm); font-size: 12px; font-weight: 600; font-family: var(--font); border: 1px solid var(--border-dk); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-sm { padding: 5px 10px; font-size: 11.5px; }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(99,102,241,.25); }
.btn-primary:hover { background: var(--brand-dk); color: #fff; }
.btn-danger { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.btn-danger:hover { background: #fee2e2; }

/* ── Cartes mobiles ── */
.mobile-drivers { display: none; flex-direction: column; gap: 10px; padding: 14px; }
.m-driver-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); }
.m-driver-hd { padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.m-driver-body { padding: 0 14px 12px; display: flex; flex-direction: column; gap: 6px; }
.m-row { display: flex; align-items: center; justify-content: space-between; }
.m-lbl { font-size: 10.5px; color: var(--muted); font-weight: 600; text-transform: uppercase; }

/* Empty */
.empty-box { padding: 48px 20px; text-align: center; }
.empty-ico  { display: flex; align-items:center; justify-content:center; margin-bottom: 12px; }
.empty-txt  { font-size: 14px; color: var(--muted); }

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .content { padding: 14px; }
}
@media (max-width: 700px) {
    .tbl-wrap { display: none; }
    .mobile-drivers { display: flex; }
    .online-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
}
@media (max-width: 520px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .stat-card { padding: 14px 12px; gap: 10px; }
    .stat-val { font-size: 22px; }
    .stat-ico { width: 38px; height: 38px; }
    .content { padding: 10px; }
    .online-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .driver-card-online { padding: 14px 12px; }
    .driver-av { width: 46px; height: 46px; font-size: 15px; }
}
@media (max-width: 380px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .online-grid { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush

@section('content')

@php
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $pendingCount   = $shop->orders()->whereIn('status',['pending','en attente','en_attente','confirmée','processing'])->count();
    $isPro          = $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
    $processedCount = $shop->orders()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where(function($q){$q->whereNotNull('livreur_id')->orWhereNotNull('delivery_company_id');})->count();
    $productCount   = $shop->products()->count();
    $_sbCmdMax = 10; $_sbProdMax = 5;
    if ($isPro)                            { $_sbCmdClr = '#10b981'; } elseif ($processedCount >= $_sbCmdMax) { $_sbCmdClr = '#ef4444'; } elseif ($processedCount >= 7) { $_sbCmdClr = '#f59e0b'; } else { $_sbCmdClr = '#8b5cf6'; }
    if ($isPro)                           { $_sbProdClr = '#10b981'; } elseif ($productCount >= $_sbProdMax) { $_sbProdClr = '#ef4444'; } elseif ($productCount >= 4)  { $_sbProdClr = '#f59e0b'; } else { $_sbProdClr = '#8b5cf6'; }

    /* Couleurs d'avatars */
    $avColors = ['#059669','#2563eb','#d97706','#7c3aed','#0891b2','#e11d48','#065f46','#1d4ed8'];

    $initiales = function(string $name): string {
        $p = explode(' ', $name);
        return strtoupper(substr($p[0],0,1)) . strtoupper(substr($p[1] ?? 'X',0,1));
    };
@endphp

<div class="dash-wrap">

{{-- ══════ SIDEBAR ══════ --}}
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
            <div class="sb-logo-icon"><img src="/images/shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
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
        <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px">
            <span class="ico">{!! $I['dash_nav'] !!}</span> Tableau de bord
        </a>
        <div class="sb-section">Boutique</div>
        <a href="{{ route('boutique.messages.hub') }}" class="sb-item">
            <span class="ico">{!! $I['msg_nav'] !!}</span> Messages
        </a>
        <a href="{{ route('boutique.orders.index') }}" class="sb-item">
            <span class="ico">{!! $I['box_nav'] !!}</span> Commandes
            @if(!$isPro)
                <span class="sb-badge" style="background:{{ $_sbCmdClr }};color:#fff;font-size:10px;padding:1px 5px;border-radius:8px;margin-left:auto">{{ $processedCount }}/{{ $_sbCmdMax }}</span>
            @elseif($pendingCount > 0)
                <span class="sb-badge">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('products.index') }}" class="sb-item">
            <span class="ico">{!! $I['tag_nav'] !!}</span> Produits
            @if(!$isPro)<span class="sb-badge" style="background:{{ $_sbProdClr }};color:#fff;font-size:10px;padding:1px 5px;border-radius:8px;margin-left:auto">{{ $productCount }}/{{ $_sbProdMax }}</span>@endif
        </a>
        <a href="{{ route('boutique.clients.index') }}" class="sb-item">
            <span class="ico">{!! $I['users_nav'] !!}</span> Clients
        </a>
        @if($isPro)
        <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">{!! $I['team_nav'] !!}</span> Équipe</a>
        @else
        <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['team_nav'] !!}</span> Équipe <span class="sb-badge" style="background:#f59e0b;margin-left:auto;">🔒</span></a>
        @endif
        <div class="sb-section">Livraison</div>
        {{-- Livreurs — actif sur cette page ── --}}
        <a href="{{ route('boutique.livreurs.index') }}" class="sb-item active">
            <span class="ico">{!! $I['bike_nav'] !!}</span> Livreurs
            @if($enLigne > 0)
                <span class="sb-badge">{{ $enLigne }}</span>
            @endif
        </a>
        @if($isPro)
        <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">{!! $I['bldg_nav'] !!}</span> Partenaires</a>
        @else
        <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['bldg_nav'] !!}</span> Partenaires <span class="sb-badge" style="background:#f59e0b;margin-left:auto;">🔒</span></a>
        @endif
        <div class="sb-section">Finances</div>
        <div class="sb-group">
            <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                <span class="ico">{!! $I['wallet_nav'] !!}</span>
                Finances & Rapports
                @if(!$isPro)<span style="font-size:11px;margin-left:4px;opacity:.7;">🔒</span>@endif
                <span class="sb-arrow">▶</span>
            </button>
            <div class="sb-sub">
                <a href="{{ route('boutique.payments.index') }}" class="sb-item">
                    <span class="ico">{!! $I['card_nav'] !!}</span> Paiements
                </a>
                <a href="{{ route('boutique.commissions.index') }}" class="sb-item">
                    <span class="ico">{!! $I['chart_nav'] !!}</span> Commissions
                </a>
                @if($isPro)
                <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">{!! $I['list_nav'] !!}</span> Rapports</a>
                @else
                <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['list_nav'] !!}</span> Rapports <span class="sb-badge" style="background:#f59e0b;margin-left:auto;">🔒</span></a>
                @endif
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('shop.edit', $shop) }}" class="sb-item">
                    <span class="ico">{!! $I['gear_nav'] !!}</span> Paramètres
                </a>
                @endif
            </div>
        </div>
        <div class="sb-section">Aide</div>
        <a href="{{ route('support.index') }}" class="sb-item">
            <span class="ico">{!! $I['hdp_nav'] !!}</span> Support
        </a>
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
            <button type="submit" class="sb-logout">
                <span class="ico">⎋</span> Se déconnecter
            </button>
        </form>
    </div>
</aside>
<div class="sb-overlay" id="sbOverlay"></div>

{{-- ══════ MAIN ══════ --}}
<main class="main">

    {{-- Topbar ── --}}
    <div class="topbar">
        <button class="btn-hamburger" id="btnMenu" aria-label="Menu">☰</button>
        <div class="tb-info">
            <div class="tb-title">{!! $I['bike_tb'] !!} Livreurs</div>
            <div class="tb-sub">{{ $shop->name }} · {{ $total }} livreur(s) · <span style="color:var(--brand);font-weight:700">{{ $enLigne }} en ligne</span></div>
        </div>
        <a href="{{ route('boutique.employees.create') }}" class="btn btn-primary btn-sm">
            {!! $I['plus_tb'] !!} Ajouter un livreur
        </a>
    </div>

    <div class="content">

        {{-- Flash ── --}}
        @foreach(['success','danger'] as $type)
            @if(session($type))
            <div class="flash flash-{{ $type }}">
                <span>{{ $type === 'success' ? '✓' : '⚠' }}</span>
                {{ session($type) }}
            </div>
            @endif
        @endforeach

        {{-- ════ KPI STATS ════ --}}
        <div class="stats-grid">
            <div class="stat-card" style="--sc-color:#3b82f6;--sc-bg:#eff6ff">
                <div class="stat-ico">{!! $I['bike_stat'] !!}</div>
                <div>
                    <div class="stat-val">{{ $total }}</div>
                    <div class="stat-lbl">Total livreurs</div>
                </div>
            </div>
            <div class="stat-card" style="--sc-color:var(--brand);--sc-bg:var(--brand-mlt)">
                <div class="stat-ico">{!! $I['online_stat'] !!}</div>
                <div>
                    <div class="stat-val" style="color:var(--brand)">{{ $enLigne }}</div>
                    <div class="stat-lbl">En ligne</div>
                </div>
            </div>
            <div class="stat-card" style="--sc-color:#f59e0b;--sc-bg:#fffbeb">
                <div class="stat-ico">{!! $I['box_stat'] !!}</div>
                <div>
                    <div class="stat-val" style="color:#d97706">{{ $enCourse }}</div>
                    <div class="stat-lbl">En course</div>
                </div>
            </div>
            <div class="stat-card" style="--sc-color:#94a3b8;--sc-bg:#f3f6f4">
                <div class="stat-ico">{!! $I['offline_stat'] !!}</div>
                <div>
                    <div class="stat-val" style="color:var(--muted)">{{ $horsligne }}</div>
                    <div class="stat-lbl">Hors ligne</div>
                </div>
            </div>
        </div>

        {{-- ════ LIVREURS EN LIGNE MAINTENANT ════ --}}
        <div class="sec-hd">
            <div class="sec-title">
                {!! $I['dot_online'] !!} En ligne maintenant
                <span class="sec-badge">{{ $enLigne }} disponible{{ $enLigne > 1 ? 's' : '' }}</span>
            </div>
        </div>

        @if($enLigneNow->isEmpty())
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:32px;text-align:center;margin-bottom:28px;box-shadow:var(--shadow-sm)">
            <div style="display:flex;justify-content:center;margin-bottom:10px">{!! $I['bike_empty_sm'] !!}</div>
            <div style="font-size:13px;color:var(--muted);font-weight:500">Aucun livreur en ligne pour le moment.</div>
        </div>
        @else
        <div class="online-grid" style="margin-bottom:28px">
            @foreach($enLigneNow as $i => $lv)
            @php
                $init  = $initiales($lv->name);
                $color = $avColors[$i % count($avColors)];
                $inCourse = !empty($lv->current_order_id);
            @endphp
            <div class="driver-card-online">
                {{-- Avatar avec indicateur de statut ── --}}
                <div class="driver-av"
                     style="background:{{ $color }};--dot-color:{{ $inCourse ? 'var(--busy)' : 'var(--online)' }}">
                    {{ $init }}
                </div>

                {{-- Nom ── --}}
                <div class="driver-name">{{ $lv->name }}</div>

                {{-- Téléphone ── --}}
                @if($lv->phone)
                <div class="driver-phone" style="display:flex;align-items:center;justify-content:center;gap:3px">{!! $I['phone_lnk'] !!} {{ $lv->phone }}</div>
                @else
                <div class="driver-phone" style="visibility:hidden">—</div>
                @endif

                {{-- Statut ── --}}
                @if($inCourse)
                <span class="driver-status-pill status-busy">
                    <span class="dot-pulse dot-busy"></span>
                    En course
                </span>
                @else
                <span class="driver-status-pill status-online">
                    <span class="dot-pulse"></span>
                    Disponible
                </span>
                @endif

                {{-- Mini stats ── --}}
                <div class="driver-stats">
                    <div style="text-align:center">
                        <span class="driver-stat-val">
                            {{ $lv->nb_livraisons }}
                        </span>
                        Livraisons
                    </div>
                    <div style="text-align:center">
                        <span class="driver-stat-val">
                            {{ $lv->nb_livraisons_today }}
                        </span>
                        Aujourd'hui
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ════ TOUS LES LIVREURS ════ --}}
        <div class="sec-hd" style="margin-bottom:16px">
            <div class="sec-title">
                {!! $I['list_hd'] !!} Tous les livreurs
                <span class="sec-badge warn">{{ $total }}</span>
            </div>
            <a href="{{ route('boutique.employees.create') }}" class="btn btn-sm">
                {!! $I['plus_tb'] !!} Ajouter
            </a>
        </div>

        @if($livreurs->isEmpty())
        <div class="empty-box">
            <span class="empty-ico">{!! $I['bike_empty'] !!}</span>
            <div class="empty-txt">Aucun livreur rattaché à cette boutique.</div>
            <a href="{{ route('boutique.employees.create') }}" class="btn btn-primary" style="display:inline-flex;margin-top:16px">
                {!! $I['plus_tb'] !!} Ajouter le premier livreur
            </a>
        </div>
        @else

        {{-- ── TABLE DESKTOP (> 700px) ── --}}
        <div class="all-card tbl-wrap">
            <div class="all-card-hd">
                <span class="all-card-title">Liste complète — {{ $total }} livreur(s)</span>
                <span style="font-size:11px;color:var(--muted);display:inline-flex;align-items:center;gap:6px">
                    <span style="display:inline-flex;align-items:center;gap:3px">{!! $I['dot_online'] !!} {{ $enLigne }} en ligne</span>
                    &nbsp;·&nbsp;
                    <span style="display:inline-flex;align-items:center;gap:3px">{!! $I['dot_offline'] !!} {{ $horsligne }} hors ligne</span>
                </span>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Livreur</th>
                        <th>Statut</th>
                        <th>Livraisons totales</th>
                        <th>Aujourd'hui</th>
                        <th>Membre depuis</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($livreurs as $i => $lv)
                    @php
                        $init     = $initiales($lv->name);
                        $color    = $avColors[$i % count($avColors)];
                        $inCourse = !empty($lv->current_order_id);
                        $dotClass = $lv->is_available ? ($inCourse ? 'busy' : 'online') : 'offline';
                        $totalLivs  = $lv->nb_livraisons;
                        $todayLivs  = $lv->nb_livraisons_today;
                    @endphp
                    <tr>
                        {{-- Livreur ── --}}
                        <td>
                            <div class="lv-cell">
                                <div class="lv-av-sm" style="background:{{ $color }}">
                                    {{ $init }}
                                    <span class="status-dot {{ $dotClass }}"></span>
                                </div>
                                <div>
                                    <div class="lv-name">{{ $lv->name }}</div>
                                    @if($lv->email)<div class="lv-email">{{ $lv->email }}</div>@endif
                                    @if($lv->phone)<div class="lv-phone" style="display:flex;align-items:center;gap:3px">{!! $I['phone_lnk'] !!} {{ $lv->phone }}</div>@endif
                                </div>
                            </div>
                        </td>

                        {{-- Statut ── --}}
                        <td>
                            @if(!$lv->is_available)
                                <span class="driver-status-pill status-offline">
                                    <span class="dot-pulse dot-offline"></span>
                                    Hors ligne
                                </span>
                            @elseif($inCourse)
                                <span class="driver-status-pill status-busy">
                                    <span class="dot-pulse dot-busy"></span>
                                    En course
                                </span>
                            @else
                                <span class="driver-status-pill status-online">
                                    <span class="dot-pulse"></span>
                                    Disponible
                                </span>
                            @endif
                        </td>

                        {{-- Livraisons ── --}}
                        <td>
                            <span style="font-family:var(--mono);font-weight:700;font-size:14px;color:var(--text)">{{ $totalLivs }}</span>
                            <span style="font-size:10px;color:var(--muted)"> livraisons</span>
                        </td>

                        {{-- Aujourd'hui ── --}}
                        <td>
                            <span style="font-family:var(--mono);font-weight:700;font-size:14px;color:{{ $todayLivs > 0 ? 'var(--brand)' : 'var(--muted)' }}">
                                {{ $todayLivs }}
                            </span>
                            <span style="font-size:10px;color:var(--muted)"> auj.</span>
                        </td>

                        {{-- Membre depuis ── --}}
                        <td style="font-size:12px;color:var(--text-2)">
                            {{ $lv->created_at->diffForHumans() }}
                        </td>

                        {{-- Actions ── --}}
                        <td>
                            <form method="POST"
                                  action="{{ route('boutique.employees.destroy', $lv) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($lv->name) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">{!! $I['trash_btn'] !!}</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── CARTES MOBILES (< 700px) ── --}}
        <div class="mobile-drivers">
            @foreach($livreurs as $i => $lv)
            @php
                $init     = $initiales($lv->name);
                $color    = $avColors[$i % count($avColors)];
                $inCourse = !empty($lv->current_order_id);
                $dotClass = $lv->is_available ? ($inCourse ? 'busy' : 'online') : 'offline';
                $totalLivs = $lv->nb_livraisons;
                $todayLivs = $lv->nb_livraisons_today;
            @endphp
            <div class="m-driver-card">
                <div class="m-driver-hd">
                    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                        <div class="lv-av-sm" style="background:{{ $color }};width:40px;height:40px;font-size:13px">
                            {{ $init }}
                            <span class="status-dot {{ $dotClass }}"></span>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div class="lv-name">{{ $lv->name }}</div>
                            @if($lv->phone)<div class="lv-phone" style="display:flex;align-items:center;gap:3px">{!! $I['phone_lnk'] !!} {{ $lv->phone }}</div>@endif
                        </div>
                    </div>
                    @if(!$lv->is_available)
                        <span class="driver-status-pill status-offline" style="flex-shrink:0">
                            <span class="dot-pulse dot-offline"></span> Hors ligne
                        </span>
                    @elseif($inCourse)
                        <span class="driver-status-pill status-busy" style="flex-shrink:0">
                            <span class="dot-pulse dot-busy"></span> En course
                        </span>
                    @else
                        <span class="driver-status-pill status-online" style="flex-shrink:0">
                            <span class="dot-pulse"></span> Disponible
                        </span>
                    @endif
                </div>
                <div class="m-driver-body">
                    <div class="m-row">
                        <span class="m-lbl" style="display:inline-flex;align-items:center;gap:4px">{!! $I['box_lbl'] !!} Total livraisons</span>
                        <span style="font-family:var(--mono);font-weight:700;color:var(--text)">{{ $totalLivs }}</span>
                    </div>
                    <div class="m-row">
                        <span class="m-lbl" style="display:inline-flex;align-items:center;gap:4px">{!! $I['bolt_lbl'] !!} Aujourd'hui</span>
                        <span style="font-family:var(--mono);font-weight:700;color:{{ $todayLivs > 0 ? 'var(--brand)' : 'var(--muted)' }}">{{ $todayLivs }}</span>
                    </div>
                    <div class="m-row" style="margin-top:4px">
                        <span style="font-size:11px;color:var(--muted)">Membre {{ $lv->created_at->diffForHumans() }}</span>
                        <form method="POST"
                              action="{{ route('boutique.employees.destroy', $lv) }}"
                              onsubmit="return confirm('Supprimer {{ addslashes($lv->name) }} ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">{!! $I['trash_btn'] !!} Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @endif

    </div>{{-- /content --}}
</main>
</div>{{-- /dash-wrap --}}

@endsection

@push('scripts')
<script>
/* ══ SIDEBAR ══ */
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

    /* ── Auto-refresh toutes les 30s pour mettre à jour les statuts en ligne ── */
    setTimeout(() => window.location.reload(), 30_000);
});
</script>
@endpush