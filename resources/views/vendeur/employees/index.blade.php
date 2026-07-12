{{--
    resources/views/vendeur/employees/index.blade.php
    Route   : GET /boutique/employees → Vendeur\EmployeeController@index → boutique.employees.index
    Variables :
      $employees → LengthAwarePaginator<User>
--}}

@extends('layouts.app')
@section('title', 'Équipe · ' . (auth()->user()->shop?->name ?? 'Boutique'))
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
    'team_tb'    => '<svg width="15" height="15" viewBox="0 0 24 24" '.$_s2.'><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="17"/><line x1="9.5" y1="14.5" x2="14.5" y2="14.5"/></svg>',
    'plus_tb'    => '<svg width="13" height="13" viewBox="0 0 24 24" '.$_s2.'><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
    /* stat chips 20px */
    'user_stat'  => '<svg width="20" height="20" viewBox="0 0 24 24" '.$_s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    'bike_stat'  => '<svg width="20" height="20" viewBox="0 0 24 24" '.$_s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1"/><path d="M5.5 17.5L11 5.5 15 10l2-3h1.5"/><line x1="11" y1="5.5" x2="7" y2="5.5"/></svg>',
    /* role badge icons 12px */
    'store_role' => '<svg width="12" height="12" viewBox="0 0 24 24" '.$_s2.'><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
    'bike_role'  => '<svg width="12" height="12" viewBox="0 0 24 24" '.$_s2.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1"/><path d="M5.5 17.5L11 5.5 15 10l2-3h1.5"/><line x1="11" y1="5.5" x2="7" y2="5.5"/></svg>',
    'team_role'  => '<svg width="12" height="12" viewBox="0 0 24 24" '.$_s2.'><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>',
    'user_role'  => '<svg width="12" height="12" viewBox="0 0 24 24" '.$_s2.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    /* inline icons */
    'phone_lnk'  => '<svg width="11" height="11" viewBox="0 0 24 24" '.$_s2.'><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.41 2 2 0 0 1 3.6 1.2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.84a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    'warn_lnk'   => '<svg width="11" height="11" viewBox="0 0 24 24" '.$_s2.'><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    'edit_btn'   => '<svg width="13" height="13" viewBox="0 0 24 24" '.$_s2.'><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
    'trash_btn'  => '<svg width="13" height="13" viewBox="0 0 24 24" '.$_s2.'><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
    /* empty state 44px */
    'empty_icon' => '<svg width="44" height="44" viewBox="0 0 24 24" '.$_s1.' style="opacity:.3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
];
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=block" rel="stylesheet">
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
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r: 14px; --r-sm: 9px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.06);
    --shadow:     0 4px 16px rgba(0,0,0,.08);
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
.flash-danger  { background: var(--danger-lt); border-color: #fca5a5; color: #991b1b; }

/* ══ PAGE HEADER ══ */
.page-hd { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 22px; flex-wrap: wrap; }
.page-title { font-size: 20px; font-weight: 800; color: var(--text); letter-spacing: -.4px; margin: 0 0 3px; }
.page-sub   { font-size: 13px; color: var(--muted); margin: 0; }

/* ══ STATS ══ */
.stats-row { display: flex; gap: 12px; margin-bottom: 22px; flex-wrap: wrap; }
.stat-chip { background: var(--surface); border: 1px solid var(--border); border-left: 3px solid var(--sc-color, var(--brand)); border-radius: var(--r-sm); padding: 12px 16px; display: flex; align-items: center; gap: 10px; box-shadow: var(--shadow-sm); flex: 1; min-width: 0; }
.stat-ico { flex-shrink: 0; display:flex; align-items:center; justify-content:center; }
.stat-val { font-size: 22px; font-weight: 800; font-family: var(--mono); color: var(--text); line-height: 1; }
.stat-lbl { font-size: 10.5px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }

/* ══ RÔLE BADGES ══ */
.role-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700; padding: 3px 10px;
    border-radius: 20px; white-space: nowrap;
}
.role-vendeur  { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.role-livreur  { background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); }
.role-employe  { background: #f5f3ff; color: #5b21b6; border: 1px solid #ddd6fe; }

/* ══ TABLE ══ */
.emp-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); }
.emp-card-hd { padding: 13px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--bg); }
.emp-card-title { font-size: 13px; font-weight: 700; color: var(--text); }

.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl thead th { padding: 11px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; background: var(--bg); border-bottom: 1px solid var(--border); white-space: nowrap; }
.tbl tbody td { padding: 13px 16px; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #fafcfb; }

.emp-av { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.emp-name  { font-size: 13px; font-weight: 600; color: var(--text); }
.emp-email { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* Boutons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: var(--r-sm); font-size: 12px; font-weight: 600; font-family: var(--font); border: 1px solid var(--border-dk); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-sm { padding: 5px 10px; font-size: 11.5px; }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(99,102,241,.25); }
.btn-primary:hover { background: var(--brand-dk); color: #fff; }
.btn-danger { background: var(--danger-lt); border-color: #fca5a5; color: #991b1b; }
.btn-danger:hover { background: #fee2e2; }

/* Empty */
.empty-state { padding: 56px 20px; text-align: center; }
.empty-ico { display: flex; align-items:center; justify-content:center; margin-bottom: 12px; }
.empty-txt { font-size: 14px; color: var(--muted); }

/* Pagination */
.pagination-wrap { display: flex; justify-content: center; padding: 14px 0 2px; }

/* ══ CARTES MOBILES ══ */
.emp-table  { display: block; }
.emp-mobile { display: none; flex-direction: column; gap: 10px; margin-top: 14px; }

.m-emp-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); }
.m-emp-hd   { padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; background: var(--bg); border-bottom: 1px solid var(--border); }
.m-emp-body { padding: 12px 14px; display: flex; flex-direction: column; gap: 7px; }
.m-emp-foot { padding: 10px 14px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; }
.m-row { display: flex; align-items: center; justify-content: space-between; }
.m-lbl { font-size: 10.5px; color: var(--muted); font-weight: 600; text-transform: uppercase; }

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .content { padding: 14px; }
    .stats-row { gap: 8px; }
    .stat-chip { padding: 10px 12px; }
    .stat-val  { font-size: 18px; }
}
@media (max-width: 640px) {
    .emp-table  { display: none; }
    .emp-mobile { display: flex; }
    .stats-row  { gap: 6px; }
    .stat-chip  { padding: 8px 10px; flex: 1; }
    .stat-val   { font-size: 16px; }
    .stat-lbl   { font-size: 9.5px; }
    .page-hd    { flex-direction: column; align-items: stretch; }
    .page-hd .btn { justify-content: center; }
    .content { padding: 10px; }
}
@media (max-width: 380px) {
    .stats-row { flex-wrap: wrap; }
    .stat-chip { min-width: calc(50% - 4px); flex: none; }
}
</style>
@endpush

@section('content')

@php
    $shop     = auth()->user()->shop ?? auth()->user()->assignedShop ?? null;
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $pendingCount   = $shop ? $shop->orders()->whereIn('status',['pending','en attente','en_attente'])->count() : 0;
    $isPro          = $shop && $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
    $processedCount = $shop ? $shop->orders()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where(function($q){$q->whereNotNull('livreur_id')->orWhereNotNull('delivery_company_id');})->count() : 0;
    $productCount   = $shop ? $shop->products()->count() : 0;
    $_sbCmdMax = 10; $_sbProdMax = 5;
    if ($isPro)                            { $_sbCmdClr = '#10b981'; }
    elseif ($processedCount >= $_sbCmdMax) { $_sbCmdClr = '#ef4444'; }
    elseif ($processedCount >= 7)          { $_sbCmdClr = '#f59e0b'; }
    else                                   { $_sbCmdClr = '#8b5cf6'; }
    if ($isPro)                           { $_sbProdClr = '#10b981'; }
    elseif ($productCount >= $_sbProdMax) { $_sbProdClr = '#ef4444'; }
    elseif ($productCount >= 4)           { $_sbProdClr = '#f59e0b'; }
    else                                  { $_sbProdClr = '#8b5cf6'; }

    $avColors  = ['#059669','#2563eb','#d97706','#7c3aed','#0891b2','#e11d48'];
    $initiales = function(string $name): string {
        $p = explode(' ', $name);
        return strtoupper(substr($p[0],0,1)) . strtoupper(substr($p[1] ?? 'X',0,1));
    };

    $totalEmp   = $employees->total();
    $vendeurs   = $employees->getCollection()->where('role_in_shop','vendeur')->count();
    $livreurs   = $employees->getCollection()->where('role_in_shop','livreur')->count();
    $employes   = $employees->getCollection()->where('role_in_shop','employe')->count();

    $roleConfig = [
        'vendeur' => ['label' => 'Vendeur',  'class' => 'role-vendeur', 'ico' => $I['store_role']],
        'livreur' => ['label' => 'Livreur',  'class' => 'role-livreur', 'ico' => $I['bike_role']],
        'employe' => ['label' => 'Employé',  'class' => 'role-employe', 'ico' => $I['team_role']],
    ];
@endphp

<div class="dash-wrap">

{{-- ══════ SIDEBAR ══════ --}}
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
            <div class="sb-logo-icon"><img src="/images/shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
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
        <a href="{{ route('boutique.employees.index') }}" class="sb-item active">
            <span class="ico">{!! $I['team_nav'] !!}</span> Équipe
        </a>
        <div class="sb-section">Livraison</div>
        @if($isPro)
        <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">{!! $I['bike_nav'] !!}</span> Livreurs</a>
        @else
        <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['bike_nav'] !!}</span> Livreurs <span class="sb-badge" style="background:#f59e0b;margin-left:auto;">🔒</span></a>
        @endif
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
            <div class="tb-title">{!! $I['team_tb'] !!} Équipe</div>
            <div class="tb-sub">{{ $shop->name ?? 'Boutique' }} · {{ $totalEmp }} membre(s)</div>
        </div>
        <a href="{{ route('boutique.employees.create') }}" class="btn btn-primary btn-sm">
            {!! $I['plus_tb'] !!} Ajouter un employé
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

        {{-- ── STATS ── --}}
        <div class="stats-row">
            <div class="stat-chip" style="--sc-color:#3b82f6">
                <span class="stat-ico">{!! $I['user_stat'] !!}</span>
                <div>
                    <div class="stat-val">{{ $totalEmp }}</div>
                    <div class="stat-lbl">Total</div>
                </div>
            </div>
           
            <div class="stat-chip" style="--sc-color:var(--brand)">
                <span class="stat-ico">{!! $I['bike_stat'] !!}</span>
                <div>
                    <div class="stat-val">{{ $livreurs }}</div>
                    <div class="stat-lbl">Livreurs</div>
                </div>
            </div>
           
        </div>

        {{-- ── TABLE DESKTOP (> 640px) ── --}}
        <div class="emp-card emp-table">
            <div class="emp-card-hd">
                <span class="emp-card-title">Liste des membres</span>
                <span style="font-size:11px;color:var(--muted)">
                    Page {{ $employees->currentPage() }} / {{ $employees->lastPage() }}
                    · {{ $employees->total() }} membre(s)
                </span>
            </div>

            @if($employees->isEmpty())
            <div class="empty-state">
                <span class="empty-ico">{!! $I['empty_icon'] !!}</span>
                <div class="empty-txt">Aucun employé pour le moment.</div>
                <a href="{{ route('boutique.employees.create') }}" class="btn btn-primary" style="display:inline-flex;margin-top:16px">
                    {!! $I['plus_tb'] !!} Ajouter le premier employé
                </a>
            </div>
            @else
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Membre</th>
                        <th>Rôle</th>
                        <th>Membre depuis</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $i => $emp)
                    @php
                        $init  = $initiales($emp->name);
                        $color = $avColors[$i % count($avColors)];
                        $role  = $roleConfig[$emp->role_in_shop] ?? ['label' => ucfirst($emp->role_in_shop), 'class' => 'role-employe', 'ico' => $I['user_role']];
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div class="emp-av" style="background:{{ $color }}">{{ $init }}</div>
                                <div>
                                    <div class="emp-name">{{ $emp->name }}</div>
                                    <div class="emp-email">{{ $emp->email }}</div>
                                    @if($emp->phone)
                                    <div style="font-size:11.5px;color:var(--brand);margin-top:2px;display:flex;align-items:center;gap:3px">{!! $I['phone_lnk'] !!} {{ $emp->phone }}</div>
                                    @else
                                    <div style="font-size:11px;color:#f59e0b;margin-top:2px;display:flex;align-items:center;gap:3px">{!! $I['warn_lnk'] !!} Pas de téléphone</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge {{ $role['class'] }}">
                                {!! $role['ico'] !!} {{ $role['label'] }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--text-2)">
                            {{ $emp->created_at->diffForHumans() }}
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;align-items:center">
                            <a href="{{ route('boutique.employees.edit', $emp) }}" class="btn btn-sm" style="background:#f0f2f5;color:#374151;border:1px solid #d1d5db">{!! $I['edit_btn'] !!} Modifier</a>
                            <form method="POST"
                                  action="{{ route('boutique.employees.destroy', $emp) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($emp->name) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">{!! $I['trash_btn'] !!} Supprimer</button>
                            </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination-wrap">{{ $employees->links() }}</div>
            @endif
        </div>

        {{-- ── CARTES MOBILES (< 640px) ── --}}
        <div class="emp-mobile">
            @forelse($employees as $i => $emp)
            @php
                $init  = $initiales($emp->name);
                $color = $avColors[$i % count($avColors)];
                $role  = $roleConfig[$emp->role_in_shop] ?? ['label' => ucfirst($emp->role_in_shop), 'class' => 'role-employe', 'ico' => $I['user_role']];
            @endphp
            <div class="m-emp-card">
                <div class="m-emp-hd">
                    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                        <div class="emp-av" style="background:{{ $color }};width:40px;height:40px;font-size:13px;flex-shrink:0">{{ $init }}</div>
                        <div style="min-width:0">
                            <div class="emp-name">{{ $emp->name }}</div>
                            <div class="emp-email" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $emp->email }}</div>
                        </div>
                    </div>
                    <span class="role-badge {{ $role['class'] }}" style="flex-shrink:0">
                        {!! $role['ico'] !!} {{ $role['label'] }}
                    </span>
                </div>
                <div class="m-emp-body">
                    <div class="m-row">
                        <span class="m-lbl">Membre depuis</span>
                        <span style="font-size:12px;color:var(--text-2);font-weight:500">{{ $emp->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="m-emp-foot">
                    <form method="POST"
                          action="{{ route('boutique.employees.destroy', $emp) }}"
                          onsubmit="return confirm('Supprimer {{ addslashes($emp->name) }} ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">{!! $I['trash_btn'] !!} Supprimer</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <span class="empty-ico">{!! $I['empty_icon'] !!}</span>
                <div class="empty-txt">Aucun employé pour le moment.</div>
                <a href="{{ route('boutique.employees.create') }}" class="btn btn-primary" style="display:inline-flex;margin-top:16px">
                    {!! $I['plus_tb'] !!} Ajouter le premier employé
                </a>
            </div>
            @endforelse
            <div class="pagination-wrap">{{ $employees->links() }}</div>
        </div>

    </div>{{-- /content --}}
</main>
</div>{{-- /dash-wrap --}}

@endsection

@push('scripts')
<script>
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
</script>
@endpush