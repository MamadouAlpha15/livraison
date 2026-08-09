{{--
    resources/views/boutique/dashboard.blade.php
    Route : GET /boutique/dashboard  → ShopController@admin  → name('boutique.dashboard')
--}}

@extends('layouts.app')

@section('title', 'Dashboard · ' . $shop->name)

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
{{-- display=swap (au lieu de "block") : le texte s'affiche tout de suite avec une police de
     secours au lieu de rester invisible jusqu'à 3s en attendant la police custom. --}}
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
</noscript>
@vite(['resources/css/boutique-dashboard.css'])
@endpush

@php
/* ══════════════════════════════════════════════════
   BIBLIOTHÈQUE D'ICÔNES SVG PREMIUM — boutique dashboard
══════════════════════════════════════════════════ */
$s  = 'stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"';
$s2 = 'stroke-linecap="round" stroke-linejoin="round" stroke-width="2"';
$I = [
    // ── Sidebar nav 17px ──
    'grid'      => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
    'msg'       => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    'box'       => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
    'tag'       => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
    'promo'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>',
    'users'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'briefcase' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
    'bike'      => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1m-2 1l3 6H5.5m2.5-6h2l4 6"/></svg>',
    'building'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'coin'      => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    'card'      => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
    'chart'     => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    'clip'      => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>',
    'gear'      => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    'headphone' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
    // ── Close / hamburger ──
    'close'     => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s2.'><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    'menu'      => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s2.'><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
    // ── Footer 14px ──
    'logout'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
    // ── Topbar 16px ──
    'bell'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
    'moon'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
    // ── Notification dropdown 14px ──
    'box_sm'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
    'msg_sm'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    'hdp_sm'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
    // ── Export 12px ──
    'download'  => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
    'table_sm'  => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>',
    'file_sm'   => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
    // ── Flash 13px ──
    'check_sm'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s2.'><polyline points="20 6 9 17 4 12"/></svg>',
    'x_sm'      => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s2.'><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    'info_sm'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s2.'><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    // ── KPI 22px (blanc sur gradient coloré) ──
    'dollar_kpi'=> '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    'box_kpi'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
    'cart_kpi'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
    'bike_kpi'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1m-2 1l3 6H5.5m2.5-6h2l4 6"/></svg>',
    // ── Today cards 22px (blanc) ──
    'money_kpi' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>',
    'box_today' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
    // ── Kanban 20px (couleur via currentColor) ──
    'clock_k'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    'check_k'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><polyline points="20 6 9 17 4 12"/></svg>',
    'truck_k'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
    'trophy_k'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><polyline points="8 6 2 6 2 12 8 12"/><polyline points="16 6 22 6 22 12 16 12"/><path d="M12 17v4"/><line x1="8" y1="21" x2="16" y2="21"/><path d="M8 6v6a4 4 0 0 0 8 0V6"/></svg>',
    'x_k'       => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    // ── Quick actions 22px ──
    'list_q'    => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
    'plus_q'    => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
    'bike_q'    => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1m-2 1l3 6H5.5m2.5-6h2l4 6"/></svg>',
    'card_q'    => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
    // ── Alert 16px ──
    'alert_a'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    'warn_a'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    'clock_a'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    'party_a'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
    // ── Livreurs phone/warn 13px ──
    'phone_sm'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.37a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.29 6.29l.54-.54a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    'warn_sm'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    // ── Delivery hero 22px (blanc) ──
    'bike_hero' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1m-2 1l3 6H5.5m2.5-6h2l4 6"/></svg>',
    'truck_hero'=> '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
    'truck_co'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
    // ── Tabs 13px ──
    'bike_tab'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1m-2 1l3 6H5.5m2.5-6h2l4 6"/></svg>',
    'bldg_tab'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    // ── No livreur notice 36px ──
    'bike_noti' => '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1m-2 1l3 6H5.5m2.5-6h2l4 6"/></svg>',
    // ── Button inline 13px ──
    'users_btn' => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'bldg_btn'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'msg_btn'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    // ── Card title 14px ──
    'flash_t'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
    'cal_t'     => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
    'chart_t'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    'warn_t'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    'trophy_t'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><polyline points="8 6 2 6 2 12 8 12"/><polyline points="16 6 22 6 22 12 16 12"/><path d="M12 17v4"/><line x1="8" y1="21" x2="16" y2="21"/><path d="M8 6v6a4 4 0 0 0 8 0V6"/></svg>',
    // ── Résumé rapide 18px ──
    'cart_r'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
    'users_r'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'bike_r'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 0 0-1-1h-1m-2 1l3 6H5.5m2.5-6h2l4 6"/></svg>',
    'bldg_r'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'dollar_r'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    // ── Chat modal ──
    'close_chat'=> '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    'bldg_chat' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    // ── Confier zone ──
    'box_conf'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
    'pin_zone'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
    'dollar_zn' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    'clock_zn'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    // ── Produits vide 28px ──
    'check_big' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
    'tag_ph'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
];
@endphp
@php
    $parts    = explode(' ', auth()->user()->name);
    $initials = strtoupper(substr($parts[0],0,1)) . (isset($parts[1]) ? strtoupper(substr($parts[1],0,1)) : strtoupper(substr($parts[0],1,1)));
    $now      = \Illuminate\Support\Carbon::now();
    $_dayLabel  = fn($d) => __('app.days')[$d->dayOfWeek];
    $_monthYear = fn($d) => __('app.months')[$d->month] . ' ' . $d->year;

    $commissionsPaieesMonth = (float) \App\Models\CourierCommission::where('shop_id', $shop->id)->where('status', 'payée')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('amount');
    $caGrossMonth = (float) $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->sum('total');
    $caMonth = $caGrossMonth - $commissionsPaieesMonth;
    $commissionsPaieesPrev = (float) \App\Models\CourierCommission::where('shop_id', $shop->id)->where('status', 'payée')->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->sum('amount');
    $caGrossPrev  = (float) $shop->orders()->whereMonth('created_at',$now->copy()->subMonth()->month)->whereYear('created_at',$now->copy()->subMonth()->year)->where('status','livrée')->sum('total');
    $caNetPrev    = $caGrossPrev - $commissionsPaieesPrev;
    $caDelta      = $caNetPrev > 0 ? round((($caMonth - $caNetPrev) / $caNetPrev) * 100, 1) : ($caMonth > 0 ? 100 : 0);
    $cmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where(function($q){$q->whereNotNull('livreur_id')->orWhereNotNull('delivery_company_id');})->count();
    $cmdToday = $shop->orders()->whereDate('created_at', today())->whereNotIn('status',['annulée','cancelled'])->count();
    $cmdYest  = $shop->orders()->whereDate('created_at', today()->subDay())->whereNotIn('status',['annulée','cancelled'])->count();
    $cmdLivreesMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->count();
    $panier          = $cmdLivreesMonth > 0 ? round($caMonth / $cmdLivreesMonth) : 0;
    $cmdLivreesPrev  = $shop->orders()->whereMonth('created_at',$now->copy()->subMonth()->month)->whereYear('created_at',$now->copy()->subMonth()->year)->where('status','livrée')->count();
    $panierPrev      = $cmdLivreesPrev > 0 ? round($caNetPrev / $cmdLivreesPrev) : 0;
    $panierDelta     = $panierPrev > 0 ? round((($panier - $panierPrev) / $panierPrev) * 100, 1) : ($panier > 0 ? 100 : 0);
    $totalCmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->whereNotIn('status',['annulée','cancelled'])->count();
    $livres        = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->count();
    $tauxLiv       = $totalCmdMonth > 0 ? round(($livres / $totalCmdMonth) * 100, 1) : 0;
    $days7 = collect(range(6,0))->map(function ($i) use ($shop, $now, $_dayLabel) {
        $day      = $now->copy()->subDays($i)->toDateString();
        $caJour   = (float) $shop->orders()->whereDate('created_at', $day)->where('status','livrée')->sum('total');
        $commJour = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop, $day) {
            $q->where('shop_id', $shop->id)->whereDate('created_at', $day);
        })->where('status', 'payée')->sum('amount');
        $d = $now->copy()->subDays($i);
        return ['label' => $_dayLabel($d), 'value' => max(0, $caJour - $commJour), 'today' => $i === 0];
    })->values();
    $max7 = $days7->max('value') ?: 1;
    $prev7Total = (float) collect(range(13,7))->sum(function ($i) use ($shop, $now) {
        $day      = $now->copy()->subDays($i)->toDateString();
        $caJ      = (float) $shop->orders()->whereDate('created_at', $day)->where('status','livrée')->sum('total');
        $commJ    = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop, $day) {
            $q->where('shop_id', $shop->id)->whereDate('created_at', $day);
        })->where('status', 'payée')->sum('amount');
        return max(0, $caJ - $commJ);
    });
    $recentOrders = $shop->orders()->with('user')->latest()->take(6)->get();
    $topProducts = $shop->products()->withCount('orderItems')->orderByDesc('order_items_count')->take(5)->get();
    $maxSales    = $topProducts->max('order_items_count') ?: 1;
    $statusMap = [
        'livrée'=>['label'=>'Livré','cls'=>'p-success'],
        'pending'=>['label'=>'En attente','cls'=>'p-warning'],
        'processing'=>['label'=>'En traitement','cls'=>'p-info'],
        'confirmée'=>['label'=>'Confirmé','cls'=>'p-info'],
        'en_livraison'=>['label'=>'En livraison','cls'=>'p-info'],
        'shipped'=>['label'=>'Expédié','cls'=>'p-info'],
        'annulée'=>['label'=>'Annulé','cls'=>'p-danger'],
    ];
    $pendingCount = $shop->orders()->whereIn('status',['en attente','en_attente','pending'])->count();
    $avColors = ['#10b981','#6366f1','#f59e0b','#8b5cf6','#14b8a6','#f43f5e'];
    $devise = $shop->currency ?? 'GNF';
    $proGnf = number_format(150000, 0, ',', ' ');
    $proPriceLabel = "{$proGnf} GNF/mois";
    $caGrossToday    = (float) $shop->orders()->whereDate('created_at', today())->where('status','livrée')->sum('total');
    $commToday       = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop) { $q->where('shop_id', $shop->id)->whereDate('created_at', today()); })->where('status', 'payée')->sum('amount');
    $caToday         = max(0, $caGrossToday - $commToday);
    $caGrossYesterday = (float) $shop->orders()->whereDate('created_at', today()->subDay())->where('status','livrée')->sum('total');
    $commYesterday    = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop) { $q->where('shop_id', $shop->id)->whereDate('created_at', today()->subDay()); })->where('status', 'payée')->sum('amount');
    $caYesterday      = max(0, $caGrossYesterday - $commYesterday);
    $caTodayDelta = $caYesterday > 0 ? round((($caToday - $caYesterday) / $caYesterday) * 100, 1) : ($caToday > 0 ? 100 : 0);
    $alerts = collect();
    $cmdEnRetard = $shop->orders()->whereIn('status', ['pending', 'en attente', 'en_attente'])->where('created_at', '<', now()->subHours(2))->count();
    if ($cmdEnRetard > 0) $alerts->push(['type'=>'danger','ico'=>$I['alert_a'],'msg'=>"{$cmdEnRetard} commande(s) en attente depuis plus de 2h — à traiter urgemment",'link'=>route('boutique.orders.index'),'cta'=>'Voir les commandes']);
    $cmdALivrer = $shop->orders()->whereIn('status', ['confirmée', 'confirmed', 'processing'])->whereNull('driver_id')->whereNull('delivery_company_id')->count();
    if ($cmdALivrer > 0 && $livreursDisponibles->isEmpty()) $alerts->push(['type'=>'warning','ico'=>$I['warn_a'],'msg'=>"{$cmdALivrer} commande(s) prête(s) à livrer mais aucun livreur disponible",'link'=>route('delivery.companies.index'),'cta'=>'Trouver un livreur']);
    if (!$shop->is_approved) $alerts->push(['type'=>'warning','ico'=>$I['clock_a'],'msg'=>"Votre boutique est en attente de validation par l'administrateur",'link'=>null,'cta'=>null]);
    if ($caTodayDelta >= 20 && $caToday > 0) $alerts->push(['type'=>'success','ico'=>$I['party_a'],'msg'=>"Excellente journée ! Vos revenus d'aujourd'hui sont en hausse de {$caTodayDelta}% vs hier",'link'=>null,'cta'=>null]);
    $kanban = [
        ['key'=>'en_attente',  'label'=>'En attente','count'=>$shop->orders()->whereIn('status',['pending','en attente','en_attente'])->count(),'color'=>'#f59e0b','bg'=>'#fffbeb','ico'=>$I['clock_k']],
        ['key'=>'confirmees',  'label'=>'Confirmées','count'=>$shop->orders()->whereIn('status',['confirmed','confirmée','processing'])->count(),'color'=>'#10b981','bg'=>'#ecfdf5','ico'=>$I['check_k']],
        ['key'=>'en_livraison','label'=>'En livraison','count'=>$shop->orders()->whereIn('status',['en_livraison','delivering','shipped'])->count(),'color'=>'#6366f1','bg'=>'#eef2ff','ico'=>$I['truck_k']],
        ['key'=>'terminees',   'label'=>'Terminées','count'=>$shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','livrée')->count(),'color'=>'#22c55e','bg'=>'#dcfce7','ico'=>$I['trophy_k']],
        ['key'=>'annulees',    'label'=>'Annulées','count'=>$shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->whereIn('status',['annulée','cancelled'])->count(),'color'=>'#ef4444','bg'=>'#fef2f2','ico'=>$I['x_k']],
    ];
    $hasLivreurs  = $livreursDisponibles->isNotEmpty();
    $hasCompanies = isset($deliveryCompanies) && $deliveryCompanies->isNotEmpty();
    $produitsRisque = $shop->products()
        ->withCount(['orderItems as ventes_mois' => function ($q) use ($now) {
            $q->whereHas('order', function ($o) use ($now) { $o->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year); });
        }])
        ->having('ventes_mois', '=', 0)->orderBy('created_at', 'desc')->take(5)->get();
    /* Résumé rapide */
    $totalProduits    = $shop->products()->count();
    $clientsActifsCount = $shop->orders()->distinct('user_id')->count('user_id');
    $livreursActifsCount = $livreursDisponibles->count();
    $partenairesCount = isset($deliveryCompanies) ? $deliveryCompanies->count() : 0;
    /* Mini bar chart commandes 7j */
    $cmdDays7 = collect(range(6,0))->map(function($i) use ($shop, $now, $_dayLabel) {
        $day = $now->copy()->subDays($i)->toDateString();
        $cnt = $shop->orders()->whereDate('created_at', $day)->whereNotIn('status',['annulée','cancelled'])->count();
        $d   = $now->copy()->subDays($i);
        return ['label' => $_dayLabel($d), 'count' => $cnt, 'today' => $i === 0];
    })->values();
    $maxCmd7 = $cmdDays7->max('count') ?: 1;

    // ── Plan & limites ──
    $isPro           = $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
    $maxProduits     = 5;     // limite plan gratuit
    $maxCmdMois      = 10;    // limite plan gratuit
    $usageProdPct    = $isPro ? 100 : min(100, round(($totalProduits / $maxProduits) * 100));
    $usageCmdPct     = $isPro ? 100 : min(100, round(($cmdMonth / $maxCmdMois) * 100));
    $prodClass       = $isPro ? 'ok' : ($usageProdPct >= 100 ? 'danger' : ($usageProdPct >= 80 ? 'warn' : 'ok'));
    $cmdClass        = $isPro ? 'ok' : ($usageCmdPct >= 100 ? 'danger' : ($usageCmdPct >= 80 ? 'warn' : 'ok'));
@endphp

{{-- ══ MODAL CHAT BOUTIQUE ↔ ENTREPRISE (depuis la cloche) ══ --}}
<div class="bq-chat-overlay" id="bqChatModal" style="display:none">
    <div class="bq-chat-panel">
        <div class="bq-chat-hd">
            <div class="bq-chat-hd-info">
                <div class="bq-chat-hd-av" id="bqChatAv">{!! $I['bldg_chat'] !!}</div>
                <div>
                    <div class="bq-chat-hd-name" id="bqChatName">Entreprise</div>
                    <div class="bq-chat-hd-sub" id="bqChatSub">Entreprise de livraison partenaire</div>
                </div>
            </div>
            <button class="bq-chat-close" onclick="bqCloseChatModal()">{!! $I['close_chat'] !!}</button>
        </div>
        {{-- Toggle livraison (mobile only) --}}
        <button class="bq-confier-toggle" id="bqConfierToggle" onclick="bqToggleConfierZone()" type="button" style="display:none;">
            <span class="bq-ct-left">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <span class="bq-ct-label">Livraison</span>
                <span class="bq-ct-badge" id="bqConfierBadge">Commandes dispo</span>
            </span>
            <span class="bq-ct-arrow">▾</span>
        </button>

        {{-- Zone confier la livraison (au-dessus des messages pour ne pas masquer le dernier) --}}
        <div class="bq-confier-zone" id="bqConfierZone" style="display:none;">
            <div class="bq-confier-hint" style="display:flex;align-items:flex-start;gap:6px">
                {!! $I['box_conf'] !!} <strong>Confier une livraison</strong>
                <span style="display:block;font-size:10.5px;color:#047857;font-weight:400;margin-top:2px;">
                    Cochez une ou plusieurs commandes → choisissez une zone → cliquez Confier
                </span>
            </div>
            <div id="bqOrdersList" style="display:flex;flex-direction:column;gap:5px;max-height:150px;overflow-y:auto;margin-bottom:8px;scrollbar-width:thin;scrollbar-color:#86efac #f0fdf4;"></div>
            <div id="bqZonePickerWrap" style="display:none;margin-bottom:8px;">
                <label style="font-size:11px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:5px;margin-bottom:8px;">{!! $I['pin_zone'] !!} Zone de livraison</label>
                {{-- Champ de recherche --}}
                <div id="bqZoneSearchBox">
                    <div style="position:relative;display:flex;align-items:center;margin-bottom:6px;">
                        <span style="position:absolute;left:10px;pointer-events:none;font-size:14px;color:#9ca3af;">🔍</span>
                        <input type="text" id="bqZoneSearch" placeholder="Tapez le nom d'une zone…" autocomplete="off"
                               oninput="bqFilterZones(this.value)"
                               style="width:100%;padding:10px 34px 10px 34px;border:1.5px solid #bbf7d0;border-radius:10px;font-size:16px;font-family:inherit;background:#fff;color:#0f172a;outline:none;box-sizing:border-box;transition:border-color .15s;"
                               onfocus="this.style.borderColor='#059669'" onblur="this.style.borderColor='#bbf7d0'">
                        <button id="bqZoneSearchClear" onclick="bqZoneClearSearch()" style="display:none;position:absolute;right:8px;background:none;border:none;cursor:pointer;color:#9ca3af;font-size:18px;padding:4px;line-height:1;touch-action:manipulation;">✕</button>
                    </div>
                    <div id="bqZoneResults" style="display:flex;flex-direction:column;gap:4px;max-height:160px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:#86efac #f0fdf4;"></div>
                </div>
                {{-- Zone sélectionnée (chip) --}}
                <div id="bqZoneSelectedChip" style="display:none;"></div>
            </div>
            <button class="bq-btn-confier" id="bqBtnConfier" onclick="bqConfierLivraison()" style="gap:6px">
                {!! $I['box_conf'] !!} Confier la livraison à cette entreprise
            </button>
        </div>

        <div class="bq-chat-msgs" id="bqChatMsgList">
            <div class="bq-chat-empty" id="bqChatEmpty">Chargement…</div>
        </div>

        <div class="bq-chat-input-zone">
            <textarea class="bq-chat-textarea" id="bqChatInput" placeholder="Votre message…" rows="1"
                      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();bqSendMsg();}"></textarea>
            <button class="bq-chat-send-btn" id="bqChatSendBtn" onclick="bqSendMsg()" title="Envoyer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </div>
    </div>
</div>

<div class="dash-wrap" id="dashWrap">

    {{-- SIDEBAR --}}
    <aside class="sidebar" id="sidebar">
          <div class="sb-brand">
            <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
               <div class="sb-logo-icon"><img src="/images/shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
                <span class="sb-shop-name">{{ $shop->name }}</span>
            </a>  
            <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer le menu">{!! $I['close'] !!}</button>
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
            <a href="{{ route('boutique.dashboard') }}" class="sb-item active" style="margin-bottom:4px"><span class="ico">{!! $I['grid'] !!}</span> Tableau de bord</a>
            <div class="sb-section">Boutique</div>
            <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">{!! $I['msg'] !!}</span> Messages <span class="sb-badge" id="sbMsgBadge" style="display:none"></span></a>
            <a href="{{ route('boutique.orders.index') }}" class="sb-item"><span class="ico">{!! $I['box'] !!}</span> Commandes
                @if(!$isPro)
                    <span class="sb-badge" style="background:{{ $cmdClass === 'danger' ? '#dc2626' : ($cmdClass === 'warn' ? '#d97706' : '#6366f1') }};margin-left:auto;">{{ $cmdMonth }}/{{ $maxCmdMois }}</span>
                @else
                    <span class="sb-badge" id="sbOrdersBadge" style="{{ $pendingCount > 0 ? '' : 'display:none' }}">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">{!! $I['tag'] !!}</span> Produits
                @if(!$isPro)<span class="sb-badge" style="background:{{ $prodClass === 'danger' ? '#ef4444' : ($prodClass === 'warn' ? '#f59e0b' : '#8b5cf6') }};color:#fff;font-size:10px;padding:1px 5px;border-radius:8px;margin-left:auto">{{ $totalProduits }}/{{ $maxProduits }}</span>@endif
            </a>
            @if($isPro)
            <a href="{{ route('boutique.promo-codes.index') }}" class="sb-item"><span class="ico">{!! $I['promo'] !!}</span> Codes promo</a>
            @else
            <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['promo'] !!}</span> Codes promo <span class="sb-badge" style="background:#f59e0b;">🔒</span></a>
            @endif
            <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">{!! $I['users'] !!}</span> Clients</a>
            @if($isPro)
            <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">{!! $I['briefcase'] !!}</span> Équipe</a>
            @else
            <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['briefcase'] !!}</span> Équipe <span class="sb-badge" style="background:#f59e0b;">🔒</span></a>
            @endif
            <div class="sb-section">Livraison</div>
            @if($isPro)
            <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">{!! $I['bike'] !!}</span> Livreurs <span class="sb-badge" id="sbLivreursBadge" style="{{ $livreursDisponibles->count() > 0 ? '' : 'display:none' }}">{{ $livreursDisponibles->count() }}</span></a>
            @else
            <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['bike'] !!}</span> Livreurs <span class="sb-badge" style="background:#f59e0b;">🔒</span></a>
            @endif
            @if($isPro)
            <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">{!! $I['building'] !!}</span> Partenaires</a>
            @else
            <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['building'] !!}</span> Partenaires <span class="sb-badge" style="background:#f59e0b;">🔒</span></a>
            @endif
            <div class="sb-section">Finances</div>
            <div class="sb-group">
                <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                    <span class="ico">{!! $I['coin'] !!}</span> Finances & Rapports
                    @if(!$isPro)<span style="font-size:11px;margin-left:4px;opacity:.7;">🔒</span>@endif
                    <span class="sb-arrow">▶</span>
                </button>
                <div class="sb-sub">
                    <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">{!! $I['card'] !!}</span> Paiements</a>
                    <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">{!! $I['chart'] !!}</span> Commissions</a>
                    @if($isPro)
                    <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">{!! $I['clip'] !!}</span> Rapports</a>
                    @else
                    <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['clip'] !!}</span> Rapports <span class="sb-badge" style="background:#f59e0b;">🔒</span></a>
                    @endif
                    @if(auth()->user()->role === 'admin')<a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">{!! $I['gear'] !!}</span> Paramètres</a>@endif
                </div>
            </div>
            <div class="sb-section">Aide</div>
            <a href="{{ route('support.index') }}" class="sb-item"><span class="ico">{!! $I['headphone'] !!}</span> Support <span class="sb-badge" id="sbSupportBadge" style="display:none;background:#10b981"></span></a>
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
                <button type="submit" class="sb-logout"><span class="sb-ft-ico">{!! $I['logout'] !!}</span> {{ __('app.logout') }}</button>
            </form>
        </div>
    </aside>

    <div class="sb-overlay" id="sbOverlay"></div>

    <main class="main">

        {{-- ══ BANNIÈRE PAIEMENT RÉUSSI ══ --}}
        @if($recentPayment)
        <div id="paymentSuccessBanner" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;padding:14px 24px;display:flex;align-items:center;gap:12px;border-radius:12px;margin:16px 16px 0;box-shadow:0 4px 20px rgba(16,185,129,.35);">
            <span style="font-size:22px">🎉</span>
            <div style="flex:1">
                <strong style="font-size:15px">Paiement réussi ! Votre abonnement Pro est maintenant actif.</strong>
                <div style="font-size:12px;opacity:.85;margin-top:2px">Vous avez accès à toutes les fonctionnalités premium.</div>
            </div>
            <button onclick="document.getElementById('paymentSuccessBanner').remove()" style="background:rgba(255,255,255,.2);border:none;color:#fff;border-radius:8px;padding:6px 12px;cursor:pointer;font-size:13px">✕</button>
        </div>
        @endif

        {{-- ══ BANNIÈRE PAIEMENT ÉCHOUÉ ══ --}}
        @if(session('payment_failed'))
        <div id="paymentFailedBanner" style="background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;padding:14px 24px;display:flex;align-items:center;gap:12px;border-radius:12px;margin:16px 16px 0;box-shadow:0 4px 20px rgba(239,68,68,.35);">
            <span style="font-size:22px">⚠️</span>
            <div style="flex:1">
                <strong style="font-size:15px">Le paiement n'a pas abouti.</strong>
                <div style="font-size:12px;opacity:.85;margin-top:2px">Votre carte ou moyen de paiement a été refusé. Aucun montant n'a été débité — réessayez.</div>
            </div>
            <button onclick="document.getElementById('paymentFailedBanner').remove()" style="background:rgba(255,255,255,.2);border:none;color:#fff;border-radius:8px;padding:6px 12px;cursor:pointer;font-size:13px">✕</button>
        </div>
        @endif

        {{-- ══ TOPBAR RESPONSIVE ══ --}}
        <div class="topbar">
            <button class="btn-hamburger" id="btnMenu" aria-label="Menu">{!! $I['menu'] !!}</button>
            <div class="tb-info">
                <div class="tb-greeting">Bonjour, {{ auth()->user()->name }} 👋</div>
                <div class="tb-greeting-sub">
                    Voici ce qui se passe avec votre boutique aujourd'hui.
                    <span id="dashLastUpdate" style="margin-left:6px;font-size:10px;color:var(--muted);font-weight:500"></span>
                </div>
            </div>

            

            <div class="tb-actions">

                {{-- Activer sons (mobile) --}}
                <button class="tb-icon-btn" id="btnEnableSound" title="Activer les sons de notification"
                        onclick="enableSoundManual()"
                        style="display:none;position:relative">
                    🔔
                    <span style="position:absolute;top:-3px;right:-3px;width:8px;height:8px;background:#ef4444;border-radius:50%;border:2px solid var(--surface)"></span>
                </button>

                {{-- Mode sombre --}}
                <button class="tb-icon-btn" id="btnDarkMode" title="Mode sombre / clair">{!! $I['moon'] !!}</button>

                {{-- Cloche notifications temps réel --}}
                <div class="bq-notif-backdrop" id="bqNotifBackdrop"></div>
                <div class="notif-bell-wrap" id="notifBellWrap" style="position:relative;display:inline-flex">
                    <button class="btn btn-sm" id="notifBellBtn" onclick="toggleNotifDropdown()" title="Notifications" style="position:relative;padding:6px 10px;display:inline-flex;align-items:center;gap:4px">
                        {!! $I['bell'] !!}
                        <span id="notifBellCount" style="display:none;position:absolute;top:-5px;right:-5px;background:#ef4444;color:#fff;font-size:9px;font-weight:800;min-width:16px;height:16px;border-radius:20px;padding:0 3px;align-items:center;justify-content:center;border:2px solid #fff"></span>
                    </button>
                    <div id="notifDropdown" class="bq-notif-panel">
                        <div class="bq-np-hd">
                            <div class="bq-np-title">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                Notifications
                            </div>
                            <span class="bq-np-cnt" id="notifDropdownTotal" style="display:none">0</span>
                        </div>
                        <div id="notifList" class="bq-np-list"></div>
                        <div class="bq-np-foot">
                            <a href="{{ route('boutique.orders.index') }}" class="btn btn-sm">{!! $I['box_sm'] !!} Commandes</a>
                            <a href="{{ route('boutique.messages.hub') }}" class="btn btn-sm">{!! $I['msg_sm'] !!} Messages</a>
                            <a href="{{ route('support.index') }}" class="btn btn-sm" style="flex:1;justify-content:center;font-size:11px;gap:4px">{!! $I['hdp_sm'] !!} Support</a>
                        </div>
                    </div>
                </div>

                {{-- Bouton Messages (depuis partial) --}}
                @include('boutique._partials.messages_btn_and_drawer')

                {{-- Export grand écran --}}
                <div class="topbar-export-group">
                    <a href="{{ route('boutique.export.orders.excel', ['shop_id' => $shop->id]) }}" class="btn btn-sm" style="gap:4px" data-noprogress>{!! $I['download'] !!} Excel</a>
                    <a href="{{ route('boutique.export.orders.pdf',   ['shop_id' => $shop->id]) }}" class="btn btn-sm" style="gap:4px" data-noprogress>{!! $I['download'] !!} PDF</a>
                </div>

                {{-- Export petit écran : dropdown --}}
                <div class="topbar-export-dropdown">
                    <button class="export-dropdown-btn" onclick="toggleExportMenu(this)" type="button" style="gap:4px">{!! $I['download'] !!} ▾</button>
                    <div class="export-menu" id="exportMenu">
                        <a href="{{ route('boutique.export.orders.excel', ['shop_id' => $shop->id]) }}" style="gap:8px" data-noprogress>{!! $I['table_sm'] !!} Excel</a>
                        <a href="{{ route('boutique.export.orders.pdf',   ['shop_id' => $shop->id]) }}" style="gap:8px" data-noprogress>{!! $I['file_sm'] !!} PDF</a>
                    </div>
                </div>

                {{-- Nouvelle commande --}}
                <a href="{{ route('boutique.orders.index') }}" class="btn btn-primary btn-sm">
                    + <span class="btn-commande-label">Commande</span>
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @foreach(['success','info','warning','danger'] as $type)
            @if(session($type))
            <div class="flash flash-{{ $type }}">
                <span style="display:flex;align-items:center">{!! $type === 'success' ? $I['check_sm'] : ($type === 'danger' ? $I['x_sm'] : $I['info_sm']) !!}</span>
                {{ session($type) }}
            </div>
            @endif
        @endforeach

        <div class="content">

            {{-- ── Bannière plan avec indicateurs d'usage ── --}}
            <div class="plan-banner {{ $isPro ? 'pro' : '' }}">
                {{-- Badge plan --}}
                <div class="plan-banner-badge {{ $isPro ? 'pro' : 'free' }}">
                    @if($isPro)
                        ✦ Plan Pro actif — expire le {{ $shop->plan_expires_at->format('d/m/Y') }}
                    @else
                        🛍 Plan Gratuit
                    @endif
                </div>

                {{-- Barres d'usage (seulement plan gratuit) --}}
                @if(!$isPro)
                <div class="plan-usages">
                    {{-- Produits --}}
                    <div class="plan-usage-item">
                        <div class="plan-usage-top">
                            <span class="plan-usage-lbl">Produits</span>
                            <span class="plan-usage-count {{ $prodClass }}">{{ $totalProduits }}/{{ $maxProduits }}</span>
                        </div>
                        <div class="plan-usage-track">
                            <div class="plan-usage-fill {{ $prodClass }}" style="width:{{ $usageProdPct }}%"></div>
                        </div>
                    </div>
                    {{-- Commandes ce mois --}}
                    <div class="plan-usage-item">
                        <div class="plan-usage-top">
                            <span class="plan-usage-lbl">Commandes (mois)</span>
                            <span class="plan-usage-count {{ $cmdClass }}">{{ $cmdMonth }}/{{ $maxCmdMois }}</span>
                        </div>
                        <div class="plan-usage-track">
                            <div class="plan-usage-fill {{ $cmdClass }}" style="width:{{ $usageCmdPct }}%"></div>
                        </div>
                    </div>
                    {{-- Fonctionnalités bloquées --}}
                    <div style="display:flex;flex-wrap:wrap;gap:5px;align-items:center">
                        <span style="font-size:10.5px;font-weight:700;color:var(--text-2);">Bloqués :</span>
                        @foreach(['Livreurs','Partenaires','Rapports','Équipe','Graphiques','Analyse période'] as $feat)
                            <span style="font-size:10px;font-weight:700;background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;padding:2px 8px;border-radius:20px;">🔒 {{ $feat }}</span>
                        @endforeach
                    </div>
                </div>
                {{-- CTA --}}
                <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-banner-cta">
                    ✦ Passer au Plan Pro — {{ $proPriceLabel }}
                </a>
                @else
                {{-- Pro actif : pas de bouton renouveler --}}
                <div style="font-size:12px;color:#4338ca;font-weight:600;flex:1">
                    Toutes les fonctionnalités débloquées.
                </div>
                @endif
            </div>

            {{-- KPI Grid --}}
            <div class="kpi-grid" style="margin-bottom:22px">
                <div class="kpi"  style="--kpi-color:#8b5cf6;--kpi-bg:#f5f3ff">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);border-color:rgba(139,92,246,.3);box-shadow:0 0 0 3px rgba(139,92,246,.12),0 4px 14px rgba(139,92,246,.4)">{!! $I['dollar_kpi'] !!}</div>
                    <div class="kpi-lbl">{{ __('app.net_revenue') }}</div>
                    <div class="kpi-val" id="kpiCaVal">{{ number_format($caMonth,0,',',' ') }}</div>
                    <div class="kpi-unit">{{ $devise }} · {{ $_monthYear($now) }}</div>
                    <div class="kpi-delta {{ $caDelta >= 0 ? 'up':'down' }}" id="kpiCaDelta">{{ $caDelta >= 0 ? '↑':'↓' }} {{ abs($caDelta) }}% vs mois précédent</div>
                    @if($commissionsPaieesMonth > 0)
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:3px">
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--muted)"><span>CA brut</span><span style="font-family:var(--mono);color:var(--text-2)">{{ number_format($caGrossMonth,0,',',' ') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--muted)"><span>Commissions</span><span style="font-family:var(--mono);color:#dc2626">− {{ number_format($commissionsPaieesMonth,0,',',' ') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:10.5px;font-weight:700;padding-top:3px;border-top:1px dashed var(--border)"><span style="color:var(--brand)">Net</span><span style="font-family:var(--mono);color:var(--brand)">{{ number_format($caMonth,0,',',' ') }}</span></div>
                    </div>
                    @endif
                </div>
                @php
                    $_cmdColor  = !$isPro && $cmdClass === 'danger' ? '#dc2626' : (!$isPro && $cmdClass === 'warn' ? '#d97706' : '#8b5cf6');
                    $_cmdBg     = !$isPro && $cmdClass === 'danger' ? '#fef2f2' : (!$isPro && $cmdClass === 'warn' ? '#fffbeb' : '#f5f3ff');
                    $_cmdGrad   = !$isPro && $cmdClass === 'danger' ? '#ef4444,#b91c1c' : (!$isPro && $cmdClass === 'warn' ? '#f59e0b,#b45309' : '#8b5cf6,#6d28d9');
                    $_cmdShadow = !$isPro && $cmdClass === 'danger' ? '0 0 0 3px rgba(220,38,38,.12),0 4px 14px rgba(220,38,38,.35)' : (!$isPro && $cmdClass === 'warn' ? '0 0 0 3px rgba(217,119,6,.12),0 4px 14px rgba(217,119,6,.35)' : '0 0 0 3px rgba(139,92,246,.12),0 4px 14px rgba(139,92,246,.4)');
                    $_cmdBorder = !$isPro && $cmdClass === 'danger' ? 'border:2px solid #fca5a5;' : (!$isPro && $cmdClass === 'warn' ? 'border:2px solid #fde68a;' : '');
                @endphp
                <div class="kpi" style="--kpi-color:{{ $_cmdColor }};--kpi-bg:{{ $_cmdBg }};{{ $_cmdBorder }}">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,{{ $_cmdGrad }});border-color:rgba(139,92,246,.3);box-shadow:{{ $_cmdShadow }}">{!! $I['box_kpi'] !!}</div>
                    <div class="kpi-lbl">{{ __('app.orders_this_month') }}</div>
                    <div class="kpi-val" id="kpiCmdVal">
                        @if(!$isPro)
                            {{ $cmdMonth }}<span style="font-size:16px;font-weight:600;color:var(--muted)">/{{ $maxCmdMois }}</span>
                        @else
                            {{ $cmdMonth }}
                        @endif
                    </div>
                    <div class="kpi-unit">commandes traitées</div>
                    <div class="kpi-delta {{ $cmdToday >= $cmdYest ? 'up':'down' }}" id="kpiCmdDelta">{{ $cmdToday >= $cmdYest ? '↑':'↓' }} {{ $cmdToday }} aujourd'hui</div>

                    @if(!$isPro)
                    {{-- Barre de progression --}}
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                            <span style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;">Utilisation</span>
                            <span style="font-size:10.5px;font-weight:800;font-family:var(--mono);color:{{ $_cmdColor }}">{{ $usageCmdPct }}%</span>
                        </div>
                        <div style="height:5px;border-radius:20px;background:{{ !$isPro && $cmdClass === 'danger' ? '#fee2e2' : (!$isPro && $cmdClass === 'warn' ? '#fef3c7' : '#ede9fe') }};overflow:hidden;">
                            <div style="height:100%;border-radius:20px;width:{{ $usageCmdPct }}%;background:linear-gradient(90deg,{{ $_cmdGrad }});transition:width .4s ease;"></div>
                        </div>
                        @if($cmdClass === 'danger')
                        <div style="margin-top:8px;display:flex;align-items:center;justify-content:space-between;gap:6px;">
                            <span style="font-size:10.5px;font-weight:800;color:#dc2626;">🔒 Limite atteinte</span>
                            <a href="{{ route('boutique.subscription.upgrade') }}" style="font-size:10px;font-weight:800;color:#6366f1;background:#eef2ff;border:1px solid #c7d2fe;padding:2px 8px;border-radius:20px;text-decoration:none;white-space:nowrap;">⚡ Plan Pro</a>
                        </div>
                        @elseif($cmdClass === 'warn')
                        <div style="margin-top:7px;font-size:10.5px;font-weight:700;color:#d97706;">⚠ Bientôt la limite — {{ $maxCmdMois - $cmdMonth }} restante{{ $maxCmdMois - $cmdMonth > 1 ? 's' : '' }}</div>
                        @else
                        <div style="margin-top:7px;font-size:10.5px;color:var(--muted);">{{ $maxCmdMois - $cmdMonth }} commande{{ $maxCmdMois - $cmdMonth > 1 ? 's' : '' }} restante{{ $maxCmdMois - $cmdMonth > 1 ? 's' : '' }} ce mois</div>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="kpi" style="--kpi-color:#d97706;--kpi-bg:#fffbeb">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#b45309);border-color:rgba(169, 141, 110, 0.3);box-shadow:0 0 0 3px rgba(217,119,6,.12),0 4px 14px rgba(147, 94, 33, 0.4)">{!! $I['cart_kpi'] !!}</div>
                    <div class="kpi-lbl">{{ __('app.avg_basket') }}</div>
                    <div class="kpi-val" id="kpiPanierVal">{{ number_format($panier,0,',',' ') }}</div>
                    <div class="kpi-unit">{{ $devise }} / commande</div>
                    <div class="kpi-delta {{ $panierDelta >= 0 ? 'up':'down' }}" id="kpiPanierDelta">{{ $panierDelta >= 0 ? '↑':'↓' }} {{ abs($panierDelta) }}% vs mois précédent</div>
                </div>
                <div class="kpi" style="--kpi-color:#d97706;--kpi-bg:#fffbeb">
                    <div class="kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#b45309);border-color:rgba(217,119,6,.3);box-shadow:0 0 0 3px rgba(217,119,6,.12),0 4px 14px rgba(217,119,6,.4)">{!! $I['bike_kpi'] !!}</div>
                    <div class="kpi-lbl">{{ __('app.delivery_rate') }}</div>
                    <div class="kpi-val" id="kpiTauxVal">{{ $tauxLiv }}%</div>
                    <div class="kpi-unit" id="kpiTauxUnit">{{ $livres }} / {{ $totalCmdMonth }} livrées</div>
                    <div class="kpi-delta {{ $tauxLiv >= 90 ? 'up':'down' }}" id="kpiTauxDelta">{{ $tauxLiv >= 90 ? '✓ Excellent':'⚠ À améliorer' }}</div>
                </div>

            </div>

            {{-- Barre visites --}}
            <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:12px 18px;margin-bottom:22px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span style="font-size:12px;font-weight:700;color:#0284c7;text-transform:uppercase;letter-spacing:.4px;margin-right:6px">Visites boutique</span>
                <span style="font-size:12px;color:#0369a1;background:#e0f2fe;padding:3px 10px;border-radius:20px;font-weight:700">{{ number_format($visitesToday,0,',',' ') }} aujourd'hui</span>
                <span style="color:#bae6fd">·</span>
                <span style="font-size:12px;color:#0369a1;background:#e0f2fe;padding:3px 10px;border-radius:20px;font-weight:700">{{ number_format($visitesWeek,0,',',' ') }} cette semaine</span>
                <span style="color:#bae6fd">·</span>
                <span style="font-size:12px;color:#0369a1;background:#e0f2fe;padding:3px 10px;border-radius:20px;font-weight:700">{{ number_format($visitesMonth,0,',',' ') }} ce mois</span>
                <span style="color:#bae6fd">·</span>
                <span style="font-size:12px;color:#64748b;margin-left:2px">{{ number_format($visitesTotal,0,',',' ') }} au total</span>
            </div>


            {{-- BLOC A --}}
            <div class="today-grid">
                <div class="today-card">
                    <div class="today-icon">{!! $I['money_kpi'] !!}</div>
                    <div style="flex:1;min-width:0">
                        <div class="today-lbl">{{ __('app.today_net_revenue') }}</div>
                        <div class="today-val" id="todayCaVal">{{ number_format($caToday, 0, ',', ' ') }}</div>
                        <div class="today-unit">{{ $devise }}</div>
                        <div class="today-delta {{ $caTodayDelta > 0 ? 'up' : ($caTodayDelta < 0 ? 'down' : 'flat') }}" id="todayCaDelta">
                            @if($caTodayDelta > 0) ↑ +{{ $caTodayDelta }}% vs hier
                            @elseif($caTodayDelta < 0) ↓ {{ $caTodayDelta }}% vs hier
                            @else — Même niveau qu'hier @endif
                        </div>
                        @if($commToday > 0)
                        <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.12);display:flex;flex-direction:column;gap:3px">
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.4)"><span>CA brut</span><span style="font-family:var(--mono);color:rgba(255,255,255,.6)">{{ number_format($caGrossToday,0,',',' ') }}</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.4)"><span>Commissions</span><span style="font-family:var(--mono);color:#fca5a5">− {{ number_format($commToday,0,',',' ') }}</span></div>
                            <div style="display:flex;justify-content:space-between;font-size:10px;font-weight:700;padding-top:3px;border-top:1px dashed rgba(255,255,255,.1)"><span style="color:#a5b4fc">Net</span><span style="font-family:var(--mono);color:#a5b4fc">{{ number_format($caToday,0,',',' ') }}</span></div>
                        </div>
                        @endif
                    </div>
                    {{-- Wave sparkline décoration --}}
                    <div class="today-wave">
                        <svg viewBox="0 0 200 100" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0,70 C20,70 25,30 50,40 C75,50 80,20 100,25 C120,30 130,60 150,50 C170,40 180,15 200,20 L200,100 L0,100 Z" fill="rgba(255,255,255,.12)"/>
                            <path d="M0,80 C25,80 30,50 55,58 C80,66 90,35 115,42 C140,49 150,68 175,60 C190,55 200,35 210,30 L210,100 L0,100 Z" fill="rgba(255,255,255,.07)"/>
                            <path d="M0,70 C20,70 25,30 50,40 C75,50 80,20 100,25 C120,30 130,60 150,50 C170,40 180,15 200,20" stroke="rgba(255,255,255,.35)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <div class="today-card" style="background:linear-gradient(135deg, #0c1a35 0%, #0f2850 40%, #0d3060 100%); box-shadow:0 8px 32px rgba(15,40,80,.4), 0 2px 8px rgba(0,0,0,.25); border-color:rgba(59,130,246,.25)">
                    <div class="today-icon">{!! $I['box_today'] !!}</div>
                    <div>
                        <div class="today-lbl">{{ __('app.today_orders') }}</div>
                        <div class="today-val" id="todayCmdVal">{{ $cmdToday }}</div>
                        <div class="today-unit">commandes reçues</div>
                        <div class="today-delta {{ $cmdToday >= $cmdYest ? 'up' : 'down' }}" id="todayCmdDelta">
                            @if($cmdToday > $cmdYest) ↑ +{{ $cmdToday - $cmdYest }} vs hier
                            @elseif($cmdToday < $cmdYest) ↓ {{ $cmdYest - $cmdToday }} de moins vs hier
                            @else — Même niveau qu'hier @endif
                        </div>
                    </div>
                    {{-- Wave sparkline décoration --}}
                    <div class="today-wave">
                        <svg viewBox="0 0 200 100" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0,60 C15,60 25,80 50,65 C75,50 85,25 110,35 C135,45 140,70 165,55 C182,44 195,20 210,25 L210,100 L0,100 Z" fill="rgba(99,102,241,.18)"/>
                            <path d="M0,75 C20,75 30,90 55,78 C80,66 90,42 115,50 C140,58 150,78 175,68 C192,61 200,42 210,38 L210,100 L0,100 Z" fill="rgba(99,102,241,.10)"/>
                            <path d="M0,60 C15,60 25,80 50,65 C75,50 85,25 110,35 C135,45 140,70 165,55 C182,44 195,20 210,25" stroke="rgba(99,102,241,.55)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- BLOC B --}}
            @if($alerts->isNotEmpty())
            <div class="alerts-zone">
                @foreach($alerts as $alert)
                <div class="alert-item {{ $alert['type'] }}">
                    <span class="alert-ico">{!! $alert['ico'] !!}</span>
                    <span class="alert-msg">{{ $alert['msg'] }}</span>
                    @if($alert['link'])<a href="{{ $alert['link'] }}" class="alert-cta">{{ $alert['cta'] }} →</a>@endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- BLOC C KANBAN --}}
            <div class="kanban-section">
                <div class="card-hd" style="padding:0 0 12px;border:none;background:transparent">
                    <span class="card-title" style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Pipeline commandes</span>
                    <a href="{{ route('boutique.orders.index') }}" class="btn btn-ghost btn-sm">Gérer les commandes →</a>
                </div>
                <div class="kanban-grid">
                    @foreach($kanban as $col)
                    <div class="kanban-col {{ $col['count'] > 0 ? 'has-items' : '' }}" style="--k-color:{{ $col['color'] }};--k-bg:{{ $col['bg'] }}">
                        <div class="kanban-ico-wrap" style="color:{{ $col['color'] }}">{!! $col['ico'] !!}</div>
                        <div class="kanban-count" id="kb-{{ $col['key'] }}">{{ $col['count'] }}</div>
                        <div class="kanban-lbl">{{ $col['label'] }}</div>
                        @if($col['label'] === 'Terminées')<div style="font-size:9px;color:var(--muted);margin-top:3px;font-weight:600">ce mois</div>@endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- BLOC D ACTIONS RAPIDES --}}
            <div class="quick-section">
                <div class="quick-card">
                    <div class="quick-card-hd">
                        <div class="quick-card-title"><span class="title-ico">{!! $I['flash_t'] !!}</span> Actions rapides</div>
                        <span style="font-size:11px;color:var(--muted)">Accès direct aux tâches courantes</span>
                    </div>
                    <div class="quick-grid">
                        {{-- Commandes — toujours accessible, mais avec usage pour plan gratuit --}}
                        <a href="{{ route('boutique.orders.index') }}" class="quick-btn" style="--q-bg:#fffbeb;--q-border:#fde68a;--q-color:#f59e0b">
                            <div class="quick-btn-ico" style="color:#f59e0b">{!! $I['list_q'] !!}</div>
                            <div class="quick-btn-lbl">Commandes</div>
                            @if(!$isPro)
                                <div class="quick-btn-sub {{ $cmdClass === 'danger' ? '' : '' }}" style="color:{{ $cmdClass === 'danger' ? '#dc2626' : ($cmdClass === 'warn' ? '#d97706' : '') }}">{{ $cmdMonth }}/{{ $maxCmdMois }} ce mois</div>
                            @else
                                <div class="quick-btn-sub">Voir & gérer</div>
                            @endif
                        </a>

                        {{-- Nouveau produit — accessible mais avec usage --}}
                        <a href="{{ $isPro || $totalProduits < $maxProduits ? route('products.create') : route('boutique.subscription.upgrade') }}" class="quick-btn" style="--q-bg:#eef2ff;--q-border:#a5b4fc;--q-color:#6366f1">
                            <div class="quick-btn-ico" style="color:#6366f1">{!! $I['plus_q'] !!}</div>
                            <div class="quick-btn-lbl">Nouveau produit</div>
                            @if(!$isPro)
                                <div class="quick-btn-sub" style="color:{{ $prodClass === 'danger' ? '#dc2626' : ($prodClass === 'warn' ? '#d97706' : '') }}">{{ $totalProduits }}/{{ $maxProduits }} produits</div>
                                @if($totalProduits >= $maxProduits)
                                    <div class="qb-locked-badge">🔒 Limite atteinte</div>
                                @endif
                            @else
                                <div class="quick-btn-sub">Ajouter au catalogue</div>
                            @endif
                        </a>

                        {{-- Livreurs — bloqué plan gratuit --}}
                        @if($isPro)
                            <a href="{{ route('boutique.livreurs.index') }}" class="quick-btn" style="--q-bg:#f5f3ff;--q-border:#c4b5fd;--q-color:#8b5cf6">
                                <div class="quick-btn-ico" style="color:#8b5cf6">{!! $I['bike_q'] !!}</div>
                                <div class="quick-btn-lbl">Livreurs</div>
                                <div class="quick-btn-sub">Voir en ligne</div>
                            </a>
                        @else
                            <a href="{{ route('boutique.subscription.upgrade') }}" class="quick-btn locked" style="--q-bg:#fef2f2;--q-border:#fca5a5;--q-color:#ef4444">
                                <div class="quick-btn-ico" style="color:#ef4444;filter:grayscale(.5)">{!! $I['bike_q'] !!}</div>
                                <div class="quick-btn-lbl" style="color:var(--muted)">Livreurs</div>
                                <div class="qb-locked-badge">🔒 Plan Pro</div>
                            </a>
                        @endif

                        {{-- Paiements — toujours accessible --}}
                        <a href="{{ route('boutique.payments.index') }}" class="quick-btn" style="--q-bg:#eff6ff;--q-border:#93c5fd;--q-color:#3b82f6">
                            <div class="quick-btn-ico" style="color:#3b82f6">{!! $I['card_q'] !!}</div>
                            <div class="quick-btn-lbl">Paiements</div>
                            <div class="quick-btn-sub">Revenus reçus</div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- CHARTS DUO : Revenus + Commandes 7j --}}
            {{-- ===================== REVENUE CHART 7J ===================== --}}
            @php
                $W = 660; $H = 160;
                $pL = 52; $pR = 16; $pT = 16; $pB = 28;
                $iW = $W - $pL - $pR;
                $iH = $H - $pT - $pB;
                $n7 = count($days7);
                $pts = [];
                foreach ($days7 as $i => $day) {
                    $px = $pL + ($n7 > 1 ? ($i / ($n7 - 1)) * $iW : $iW / 2);
                    $py = $pT + $iH - ($max7 > 0 ? ($day['value'] / $max7) * $iH : 0);
                    $pts[] = ['x' => round($px,2), 'y' => round($py,2), 'v' => $day['value'], 'lbl' => $day['label'], 'today' => $day['today']];
                }
                $polyline   = implode(' ', array_map(fn($p) => $p['x'].','.$p['y'], $pts));
                $areaPoints = ($pts[0]['x'].','.(  $pT+$iH).' '.$polyline.' '.$pts[$n7-1]['x'].','.(  $pT+$iH));
                $week7Total = $days7->sum('value');
                $bestDay    = $days7->sortByDesc('value')->first();
                // Y-axis labels: 0, mid, max
                $yGrid = [
                    ['val' => $max7,     'y' => $pT],
                    ['val' => $max7 / 2, 'y' => $pT + $iH / 2],
                    ['val' => 0,         'y' => $pT + $iH],
                ];
                function rcFmt($n) {
                    if ($n >= 1000000) return round($n/1000000,1).'M';
                    if ($n >= 1000)    return round($n/1000).'k';
                    return round($n);
                }
            @endphp
            <div class="plan-locked-wrap">
            @if(!$isPro)
            <div class="plan-locked-overlay">
                <div class="plan-locked-ico">📊</div>
                <div class="plan-locked-title">Graphiques — Plan Pro requis</div>
                <div class="plan-locked-sub">Les graphiques de revenus et de commandes sont réservés au Plan Pro. Passez au Pro pour visualiser l'évolution de votre activité.</div>
                <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-locked-btn">✦ Débloquer les graphiques — {{ $proPriceLabel }}</a>
            </div>
            @endif
            <div class="charts-duo" style="{{ !$isPro ? 'filter:blur(3px);pointer-events:none;user-select:none' : '' }}">
            <div class="card chart-wrap" style="margin-bottom:0">
                {{-- Header --}}
                <div class="rc-header">
                    <div class="rc-header-left">
                        <div class="rc-title">{{ __('app.revenue_7days') }}</div>
                        <div class="rc-total">
                            <sup>{{ $devise }} </sup>{{ number_format($week7Total, 0, ',', ' ') }}
                        </div>
                        @php
                            $prevWeek  = $prev7Total;
                            $todayEntry = $days7->firstWhere('today', true);
                            $todayVal   = $todayEntry['value'] ?? 0;
                            $n7c        = $days7->count();
                            $yesterVal  = $n7c >= 2 ? $days7->get($n7c - 2)['value'] : 0;
                            $rcDelta    = $yesterVal > 0 ? round((($todayVal - $yesterVal) / $yesterVal) * 100, 1) : ($todayVal > 0 ? 100 : 0);
                        @endphp
                        <div id="rcDeltaBadge" class="rc-delta {{ $rcDelta > 0 ? 'up' : ($rcDelta < 0 ? 'down' : 'flat') }}">
                            @if($rcDelta > 0) ↑ +{{ $rcDelta }}% aujourd'hui vs hier
                            @elseif($rcDelta < 0) ↓ {{ $rcDelta }}% aujourd'hui vs hier
                            @else → Stable aujourd'hui
                            @endif
                        </div>
                    </div>
                    <div class="rc-header-right">
                        <div style="margin-bottom:2px">{{ __('app.best_day') }}</div>
                        <div class="rc-best">{{ $bestDay['label'] }} — {{ number_format($bestDay['value'],0,',',' ') }} {{ $devise }}</div>
                        <div style="margin-top:6px">{{ __('app.avg_per_day') }}</div>
                        <div style="font-weight:700;color:var(--text);font-size:12px">{{ number_format($week7Total/7,0,',',' ') }} {{ $devise }}</div>
                    </div>
                </div>

                {{-- SVG Chart --}}
                <div class="rc-svg-wrap">
                    <div class="rc-tooltip" id="rcTip">
                        <span id="rcTipDay"></span>
                        <strong id="rcTipVal"></strong>
                    </div>
                    <svg viewBox="0 0 {{ $W }} {{ $H }}" width="100%" height="160" preserveAspectRatio="none" overflow="visible" style="display:block">
                        <defs>
                            <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%"   stop-color="#8b5cf6" stop-opacity=".38"/>
                                <stop offset="45%"  stop-color="#6366f1" stop-opacity=".14"/>
                                <stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="lineGrad" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0%"   stop-color="#8b5cf6"/>
                                <stop offset="100%" stop-color="#6366f1"/>
                            </linearGradient>
                            <filter id="glowLine" x="-10%" y="-80%" width="120%" height="260%">
                                <feGaussianBlur stdDeviation="3.5" result="blur"/>
                                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                            </filter>
                            <filter id="glowDot" x="-80%" y="-80%" width="260%" height="260%">
                                <feGaussianBlur stdDeviation="4" result="blur"/>
                                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                            </filter>
                        </defs>

                        {{-- Y gridlines premium --}}
                        @foreach($yGrid as $g)
                        <line x1="{{ $pL }}" y1="{{ $g['y'] }}" x2="{{ $W - $pR }}" y2="{{ $g['y'] }}"
                              stroke="rgba(99,102,241,.12)" stroke-width="1" stroke-dasharray="{{ $g['val'] > 0 ? '5 6' : 'none' }}"/>
                        <text x="{{ $pL - 8 }}" y="{{ $g['y'] + 4 }}" text-anchor="end"
                              font-size="9" fill="#94a3b8" font-family="monospace">{{ rcFmt($g['val']) }}</text>
                        @endforeach

                        {{-- Area fill --}}
                        <polygon points="{{ $areaPoints }}" fill="url(#areaGrad)"/>

                        {{-- Glow line shadow --}}
                        <polyline points="{{ $polyline }}" fill="none" stroke="rgba(139,92,246,.35)" stroke-width="6" stroke-linejoin="round" stroke-linecap="round"/>
                        {{-- Line --}}
                        <polyline points="{{ $polyline }}" fill="none" stroke="url(#lineGrad)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>

                        {{-- Points + hit areas --}}
                        @foreach($pts as $i => $p)
                        @if($p['today'])
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="9" fill="rgba(139,92,246,.18)" stroke="none"/>
                        @endif
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $p['today'] ? 5 : 3.5 }}"
                                fill="{{ $p['today'] ? '#8b5cf6' : '#fff' }}"
                                stroke="{{ $p['today'] ? '#c4b5fd' : '#6366f1' }}" stroke-width="{{ $p['today'] ? 2.5 : 1.8 }}"
                                class="rc-pt"
                                data-val="{{ $p['v'] }}"
                                data-lbl="{{ $p['lbl'] }}"
                                data-today="{{ $p['today'] ? '1' : '0' }}"/>
                        {{-- invisible wide hit area --}}
                        <rect x="{{ $p['x'] - 20 }}" y="{{ $pT }}" width="40" height="{{ $iH }}"
                              fill="transparent"
                              class="rc-hit"
                              data-idx="{{ $i }}"/>
                        @endforeach
                    </svg>

                    {{-- X-axis day labels --}}
                    <div class="rc-day-dots" style="margin-left:{{ $pL }}px;margin-right:{{ $pR }}px">
                        @foreach($days7 as $day)
                        <div class="rc-day-dot {{ $day['today'] ? 'today' : '' }}">{{ $day['label'] }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Mini bar chart : Commandes 7j --}}
            <div class="card" style="margin-bottom:0">
                <div class="mini-chart-hd">
                    <div>
                        <div class="mini-chart-lbl">{{ __('app.orders_chart') }}</div>
                        <div class="mini-chart-val">{{ $cmdDays7->sum('count') }}</div>
                        <div class="mini-chart-sub">{{ __('app.last_7_days') }}</div>
                        @php
                            $cmdToday7  = $cmdDays7->last()['count'] ?? 0;
                            $cmdYest7   = $cmdDays7->count() >= 2 ? $cmdDays7->get($cmdDays7->count()-2)['count'] : 0;
                            $cmdDelta7  = $cmdToday7 - $cmdYest7;
                        @endphp
                        <div id="cmdDeltaBadge" class="mini-delta {{ $cmdDelta7 > 0 ? 'up' : ($cmdDelta7 < 0 ? 'down' : 'flat') }}">
                            @if($cmdDelta7 > 0) ↑ {{ $cmdToday7 }} aujourd'hui
                            @elseif($cmdDelta7 < 0) ↓ {{ $cmdToday7 }} aujourd'hui
                            @else → {{ $cmdToday7 }} aujourd'hui
                            @endif
                        </div>
                    </div>
                    <span class="mini-period-badge">30 derniers jours ▾</span>
                </div>
                <div class="mini-bars-wrap">
                    @foreach($cmdDays7 as $cd)
                    @php $hPct = $maxCmd7 > 0 ? max(round(($cd['count']/$maxCmd7)*100),2) : 2; @endphp
                    <div class="mini-bar-col">
                        <div class="mini-bar {{ $cd['today'] ? 'actuel' : '' }}"
                             data-h="{{ $hPct }}"
                             style="height:{{ $hPct }}%;transform:scaleY(0);{{ $cd['today'] ? 'opacity:1' : '' }}"></div>
                    </div>
                    @endforeach
                </div>
                <div class="mini-bar-labs">
                    @foreach($cmdDays7 as $cd)
                    <div class="mini-bar-lbl" style="flex:1;text-align:center;{{ $cd['today'] ? 'color:var(--brand);font-weight:700' : '' }}">{{ $cd['label'] }}</div>
                    @endforeach
                </div>
            </div>
            </div>{{-- /charts-duo --}}
            </div>{{-- /plan-locked-wrap charts --}}

            {{-- CONTENT GRID --}}
            <div class="content-grid">
                <div class="card">
                    <div class="card-hd"><span class="card-title">{{ __('app.recent_orders') }}</span><a href="{{ route('boutique.orders.index') }}" class="btn-ghost btn btn-sm">{{ __('app.see_all') }}</a></div>
                    {{-- Desktop : tableau --}}
                    <div class="tbl-wrap" style="padding:0 18px">
                        @if($recentOrders->isEmpty())<div style="padding:28px 0;text-align:center;font-size:13px;color:var(--muted)">Aucune commande pour le moment.</div>
                        @else
                        <table class="tbl">
                            <thead><tr><th>Réf / Client</th><th>Statut</th><th style="text-align:right">Montant</th></tr></thead>
                            <tbody>
                            @foreach($recentOrders as $order)
                            @php $st = $statusMap[$order->status] ?? ['label'=>ucfirst($order->status),'cls'=>'p-muted']; @endphp
                            <tr>
                                <td><div class="oid">#{{ $order->id }}</div><div class="onam">{{ $order->user->name ?? 'Client inconnu' }}</div></td>
                                <td><span class="pill {{ $st['cls'] }}">{{ $st['label'] }}</span></td>
                                <td class="oamt">{{ number_format($order->total,0,',',' ') }} <span style="font-size:9px;color:var(--muted)"> {{ $devise }}</span></td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>

                    {{-- Mobile : cartes --}}
                    @if($recentOrders->isEmpty())
                    <div class="orders-mobile" style="padding:28px 14px;text-align:center;font-size:13px;color:var(--muted);">Aucune commande pour le moment.</div>
                    @else
                    <div class="orders-mobile">
                        @foreach($recentOrders as $order)
                        @php $st = $statusMap[$order->status] ?? ['label'=>ucfirst($order->status),'cls'=>'p-muted']; @endphp
                        <a href="{{ route('boutique.orders.index') }}" class="om-card">
                            <div class="om-card-av">#{{ substr($order->id, -2) }}</div>
                            <div class="om-card-body">
                                <div class="om-card-ref">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="om-card-client">{{ $order->user->name ?? 'Client inconnu' }}</div>
                            </div>
                            <div class="om-card-right">
                                <span class="pill {{ $st['cls'] }}" style="font-size:10px;padding:3px 8px;">{{ $st['label'] }}</span>
                                <div class="om-card-amt" style="margin-top:4px;">
                                    {{ number_format($order->total,0,',',' ') }}
                                    <span class="om-card-devise">{{ $devise }}</span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="right-col">
                    @if($hasLivreurs)
                    <div class="card delivery-card">
                        {{-- Hero header --}}
                        <div class="delivery-card-hero">
                            <div class="delivery-card-hero-left">
                                <div class="delivery-card-icon">{!! $I['bike_hero'] !!}</div>
                                <div>
                                    <div class="delivery-card-title">Livraison</div>
                                    <div class="delivery-card-sub">{{ $livreursDisponibles->count() }} livreur(s) disponible(s)</div>
                                </div>
                            </div>
                            <a href="{{ route('boutique.employees.index') }}" class="delivery-card-manage">Gérer →</a>
                        </div>
                        {{-- Tabs --}}
                        <div class="tab-bar">
                            <button class="tab-btn active" data-tab="livreurs" style="gap:5px">{!! $I['bike_tab'] !!} Livreurs <span class="tab-count">{{ $livreursDisponibles->count() }}</span></button>
                            @if($hasCompanies)
                                @if($isPro)
                                <button class="tab-btn" data-tab="companies" style="gap:5px">{!! $I['bldg_tab'] !!} Entreprises <span class="tab-count {{ $deliveryCompanies->count() === 0 ? 'zero':'' }}">{{ $deliveryCompanies->count() }}</span></button>
                                @else
                                <button class="tab-btn" style="gap:5px;opacity:.55;cursor:default;" type="button" onclick="document.getElementById('planLockPartenairesModal').style.display='flex'">{!! $I['bldg_tab'] !!} Entreprises 🔒</button>
                                @endif
                            @endif
                        </div>
                        {{-- Liste livreurs --}}
                        <div class="tab-panel active" id="tab-livreurs">
                            <div class="lv-list">
                                @foreach($livreursDisponibles->take(5) as $i => $livreur)
                                @php
                                    $lp    = explode(' ', $livreur->name);
                                    $linit = strtoupper(substr($lp[0],0,1)).(isset($lp[1]) ? strtoupper(substr($lp[1],0,1)) : strtoupper(substr($lp[0],1,1)));
                                    $lcol  = $avColors[$i % count($avColors)];
                                    $busy  = !empty($livreur->current_order_id);
                                    $waNum = preg_replace('/\D/', '', $livreur->phone ?? '');
                                @endphp
                                <div class="lv-row">
                                    <div class="lv-av" style="background:{{ $lcol }}">{{ $linit }}</div>
                                    <div class="lv-info">
                                        <div class="lv-nm">{{ $livreur->name }}</div>
                                        @if($livreur->phone)
                                            <div class="lv-phone">
                                                {!! $I['phone_sm'] !!}
                                                <a href="tel:{{ $livreur->phone }}" style="color:inherit;text-decoration:none">{{ $livreur->phone }}</a>
                                            </div>
                                        @else
                                            <div class="lv-phone-warn" style="display:flex;align-items:center;gap:4px">{!! $I['warn_sm'] !!} Pas de téléphone</div>
                                        @endif
                                    </div>
                                    <span class="lv-status-badge {{ $busy ? 'busy' : 'available' }}">{{ $busy ? 'En course' : 'Dispo' }}</span>
                                    @if($waNum)
                                    <a href="https://wa.me/{{ $waNum }}" target="_blank" class="lv-wa-btn" title="Contacter par WhatsApp">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </a>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @if($livreursDisponibles->count() > 5)
                            <div class="lv-footer">
                                <a href="{{ route('boutique.employees.index') }}">+ {{ $livreursDisponibles->count()-5 }} autre(s) livreur(s) →</a>
                            </div>
                            @endif
                        </div>
                        @if($hasCompanies)
                        <div class="tab-panel plan-locked-wrap" id="tab-companies">
                            <div class="co-list">
                                @foreach($deliveryCompanies->take(4) as $company)
                                <div class="co-row" onclick="window.location='{{ route('company.chat.show', $company) }}'" title="Ouvrir la discussion">
                                    <div class="co-logo">@if(!empty($company->logo))<img src="{{ asset('storage/'.$company->logo) }}" alt="{{ $company->name }}">@else {!! $I['truck_co'] !!} @endif</div>
                                    <div class="co-info"><div class="co-nm">{{ $company->name }}</div><div class="co-mt">{{ $company->phone ?? 'Contact non renseigné' }}</div></div>
                                    @if($company->commission_rate)<span class="co-commission">{{ number_format($company->commission_rate*100,1) }}%</span>@endif
                                    <a href="{{ route('company.chat.show', $company) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation()" style="gap:4px">{!! $I['msg_btn'] !!} Contacter</a>
                                </div>
                                @endforeach
                            </div>
                            @if($deliveryCompanies->count() > 4)<div class="lv-footer"><a href="{{ route('delivery.companies.index') }}">Voir toutes →</a></div>@endif
                            @if(!$isPro)
                            <div class="plan-locked-overlay">
                                <div class="plan-locked-ico">🚚</div>
                                <div class="plan-locked-title">Partenaires — Plan Pro requis</div>
                                <div class="plan-locked-sub">L'accès aux entreprises de livraison partenaires est réservé au Plan Pro. Passez au Pro pour contacter et collaborer avec les transporteurs.</div>
                                <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-locked-btn">✦ Débloquer les partenaires — {{ $proPriceLabel }}</a>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="card delivery-card">
                        {{-- Hero header vide --}}
                        <div class="delivery-card-hero">
                            <div class="delivery-card-hero-left">
                                <div class="delivery-card-icon">{!! $I['truck_hero'] !!}</div>
                                <div>
                                    <div class="delivery-card-title">Livraison</div>
                                    <div class="delivery-card-sub">Aucun livreur actif</div>
                                </div>
                            </div>
                            <a href="{{ route('boutique.employees.create') }}" class="delivery-card-manage">+ Ajouter →</a>
                        </div>
                        @if(!$hasCompanies)
                        <div class="no-livreur-notice">
                            <div class="notice-icon">{!! $I['bike_noti'] !!}</div>
                            <div class="notice-title">Aucun livreur disponible</div>
                            <div class="notice-sub">Ajoutez vos propres livreurs dans <strong>Équipe</strong>, ou contactez une entreprise partenaire.</div>
                            <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;justify-content:center">
                                <a href="{{ route('boutique.employees.create') }}" class="btn btn-sm" style="gap:4px">{!! $I['users_btn'] !!} Ajouter un livreur</a>
                                <a href="{{ route('delivery.companies.index') }}" class="btn btn-primary btn-sm" style="gap:4px">{!! $I['bldg_btn'] !!} Entreprise partenaire</a>
                            </div>
                        </div>
                        @else
                        <div class="plan-locked-wrap">
                            <div style="padding:12px 18px;background:#fffbeb;border-bottom:1px solid #fde68a;display:flex;align-items:flex-start;gap:10px">
                                <span style="flex-shrink:0;display:flex;margin-top:2px">{!! $I['warn_a'] !!}</span>
                                <div><div style="font-size:12.5px;color:#92400e;font-weight:700;margin-bottom:3px">Vous n'avez pas de livreurs</div><div style="font-size:11.5px;color:#b45309;line-height:1.55">Contactez une entreprise partenaire ci-dessous. Cliquez sur <strong>Contacter</strong> pour ouvrir une discussion.</div></div>
                            </div>
                            <div class="co-list">
                                @foreach($deliveryCompanies->take(4) as $company)
                                <div class="co-row">
                                    <div class="co-logo">@if(!empty($company->logo))<img src="{{ asset('storage/'.$company->logo) }}" alt="{{ $company->name }}">@else {!! $I['truck_co'] !!} @endif</div>
                                    <div class="co-info"><div class="co-nm">{{ $company->name }}</div><div class="co-mt">{{ $company->phone ?? 'Contact non renseigné' }}</div></div>
                                    @if($company->commission_rate)<span class="co-commission">{{ number_format($company->commission_rate*100,1) }}%</span>@endif
                                </div>
                                @endforeach
                            </div>
                            @if(!$isPro)
                            <div class="plan-locked-overlay">
                                <div class="plan-locked-ico">🚚</div>
                                <div class="plan-locked-title">Partenaires — Plan Pro requis</div>
                                <div class="plan-locked-sub">L'accès aux entreprises de livraison partenaires est réservé au Plan Pro. Passez au Pro pour collaborer avec les transporteurs.</div>
                                <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-locked-btn">✦ Débloquer les partenaires — {{ $proPriceLabel }}</a>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- TOP PRODUITS --}}
            @if($topProducts->isNotEmpty())
            <div class="card">
                <div class="card-hd"><span class="card-title" style="cursor:pointer;display:inline-flex;align-items:center;gap:5px" onclick="window.location='{{ route('products.top') }}'">Top produits — ventes du mois {!! $I['trophy_t'] !!}</span><a href="{{ route('products.top') }}" class="btn btn-ghost btn-sm">Voir le classement →</a></div>
                <div class="card-bd">
                    @foreach($topProducts as $product)
                    @php $pct = round(($product->order_items_count / $maxSales)*100); @endphp
                    <div class="sp-row">
                        <span class="sp-lbl" title="{{ $product->name }}">{{ Str::limit($product->name, 18) }}</span>
                        <div class="sp-track"><div class="sp-fill" data-pct="{{ $pct }}" style="width:{{ $pct }}%;transform:scaleX(0)"></div></div>
                        <span class="sp-val">{{ $product->order_items_count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- PRODUITS A RISQUE --}}
            <div class="card" style="margin-bottom:22px">
                <div class="card-hd"><span class="card-title" style="display:inline-flex;align-items:center;gap:5px">{!! $I['warn_t'] !!} Produits à risque — 0 vente ce mois</span><a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Gérer les produits →</a></div>
                @if($produitsRisque->isEmpty())
                    <div class="risk-empty"><span class="ico">{!! $I['check_big'] !!}</span>Tous vos produits ont été vendus ce mois !</div>
                @else
                <div style="display:flex;flex-wrap:wrap;gap:0;padding:0">
                    @foreach($produitsRisque as $product)
                    <div class="risk-row" style="flex:1;min-width:180px;border-right:1px solid #f3f6f4;border-bottom:none">
                        @if(!empty($product->image))<img src="{{ \App\Services\ImageOptimizer::url($product->image, 'thumb') ?? asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="risk-img" loading="lazy" width="38" height="38">
                        @else<div class="risk-img-placeholder">{!! $I['tag_ph'] !!}</div>@endif
                        <div class="risk-info">
                            <div class="risk-name" title="{{ $product->name }}">{{ Str::limit($product->name, 20) }}</div>
                            <div class="risk-meta">{{ $product->price ? number_format($product->price,0,',',' ').' '.$devise : 'Prix non défini' }}</div>
                        </div>
                        <span class="risk-badge">0 vente</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- SÉLECTEUR DE PÉRIODE + RÉSUMÉ RAPIDE --}}
            <div class="perf-grid">
            <div class="plan-locked-wrap">
            @if(!$isPro)
            <div class="plan-locked-overlay">
                <div class="plan-locked-ico">📅</div>
                <div class="plan-locked-title">Analyse par période — Plan Pro requis</div>
                <div class="plan-locked-sub">Analysez vos revenus semaine par semaine, mois par mois. Disponible avec le Plan Pro.</div>
                <a href="{{ route('boutique.subscription.upgrade') }}" class="plan-locked-btn">✦ Débloquer l'analyse — {{ $proPriceLabel }}</a>
            </div>
            @endif
            <div class="card period-card" id="periodCard" style="margin-bottom:0;{{ !$isPro ? 'filter:blur(3px);pointer-events:none;user-select:none' : '' }}">
                <div class="card-hd">
                    <span class="card-title" style="display:inline-flex;align-items:center;gap:5px">{!! $I['cal_t'] !!} Analyse par période</span>
                    <span class="period-label" id="periodLabel">Période : <strong>Ce mois</strong></span>
                </div>
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
                <div class="period-loading" id="periodLoading"><div class="spin"></div> Chargement…</div>
                <div id="periodStatsWrap">
                    <div class="period-stats">
                        <div class="period-stat">
                            <div class="period-stat-lbl">Chiffre d'affaires</div>
                            <div class="period-stat-val" id="pCA" title="" style="cursor:help">—</div>
                            <div class="period-stat-sub">{{ $devise }}</div>
                            <div id="pCA-full" style="font-size:10px;color:var(--brand);font-family:var(--mono);margin-top:3px;display:none;font-weight:600"></div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Commandes</div>
                            <div class="period-stat-val" id="pCMD">—</div>
                            <div class="period-stat-sub">commandes</div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Panier moyen</div>
                            <div class="period-stat-val" id="pPANIER" title="" style="cursor:help">—</div>
                            <div class="period-stat-sub">{{ $devise }} / cmd</div>
                            <div id="pPANIER-full" style="font-size:10px;color:var(--brand);font-family:var(--mono);margin-top:3px;display:none;font-weight:600"></div>
                        </div>
                        <div class="period-stat">
                            <div class="period-stat-lbl">Taux livraison</div>
                            <div class="period-stat-val" id="pTAUX">—</div>
                            <div class="period-stat-sub">% livrées</div>
                        </div>
                    </div>
                    <div class="period-chart">
                        <div class="period-bars" id="periodBars"></div>
                        <div class="period-bar-labels" id="periodLabels"></div>
                    </div>
                </div>
            </div>
            </div>{{-- /plan-locked-wrap period --}}

            {{-- Résumé rapide --}}
            <div class="card" style="margin-bottom:0">
                <div class="card-hd"><span class="card-title" style="display:inline-flex;align-items:center;gap:5px">{!! $I['chart_t'] !!} Résumé rapide</span></div>
                <div class="card-bd">
                    <div class="resume-items">
                        <div class="resume-item">
                            <div class="resume-item-ico">{!! $I['cart_r'] !!}</div>
                            <div>
                                <div class="resume-item-val">{{ $totalProduits }}</div>
                                <div class="resume-item-lbl">Total produits</div>
                            </div>
                        </div>
                        <div class="resume-item">
                            <div class="resume-item-ico">{!! $I['users_r'] !!}</div>
                            <div>
                                <div class="resume-item-val">{{ $clientsActifsCount }}</div>
                                <div class="resume-item-lbl">Clients actifs</div>
                            </div>
                        </div>
                        <div class="resume-item">
                            <div class="resume-item-ico">{!! $I['bike_r'] !!}</div>
                            <div>
                                <div class="resume-item-val">{{ $livreursActifsCount }}</div>
                                <div class="resume-item-lbl">Livreurs actifs</div>
                            </div>
                        </div>
                        <div class="resume-item">
                            <div class="resume-item-ico">{!! $I['bldg_r'] !!}</div>
                            <div>
                                <div class="resume-item-val">{{ $partenairesCount }}</div>
                                <div class="resume-item-lbl">Partenaires</div>
                            </div>
                        </div>
                        <div class="resume-item" style="border-color:var(--brand-lt);background:var(--brand-mlt)">
                            <div class="resume-item-ico" style="background:var(--brand-mlt);border-color:var(--brand-lt);color:var(--brand)">{!! $I['dollar_r'] !!}</div>
                            <div>
                                <div class="resume-item-val" style="color:var(--brand);font-size:16px">{{ number_format($caMonth/1000000,1) }}M</div>
                                <div class="resume-item-lbl" style="color:var(--brand-dk)">Revenu net ce mois</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>{{-- /perf-grid --}}

        </div>{{-- /content --}}
    </main>
</div>{{-- /dash-wrap --}}

@push('scripts')
<script>
window.BQ_CFG = {
    devise: @json($devise),
    uid: {{ auth()->id() }},
    shopId: {{ $shop->id ?? 0 }},
    periodStatsUrl: @json(route('boutique.period.stats')),
    messagesHubUrl: @json(route('boutique.messages.hub')),
    ordersIndexUrl: @json(route('boutique.orders.index')),
    kpiLiveUrl: @json(route('boutique.kpi.live')),
};
</script>
@vite(['resources/js/boutique-dashboard.js'])
@endpush
