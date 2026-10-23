{{--
    resources/views/boutique/dashboard.blade.php
    Route : GET /boutique/dashboard  → ShopController@admin  → name('boutique.dashboard')
--}}

@extends('layouts.app')

@section('title', 'Dashboard · ' . $shop->name)

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
{{-- Page lourde (beaucoup d'images : logo, photos produits, graphiques) — on demande
     au loader du layout (voir layouts/app.blade.php) d'attendre AUSSI que toutes les
     images de la page soient chargées avant de la révéler, pas seulement le CSS/police.
     Ce drapeau doit s'exécuter avant le script du loader plus bas dans le document —
     @push('styles') est rendu dans le <head>, avant le loader qui est dans le <body>.
     N'affecte que cette page : les autres continuent comme avant. --}}
<script>window.__pgWaitImages = true;</script>
{{-- Polices auto-hébergées (avant : fonts.googleapis.com + fonts.gstatic.com) — même fichier
     déjà servi par shopio-app.com, zéro nouvelle connexion DNS/TLS externe à négocier.
     display=swap : le texte s'affiche tout de suite avec une police de secours au lieu de
     rester invisible en attendant la police custom. --}}
<link rel="preload" as="style" href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}">
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet" media="print" onload="this.media='all'">
<noscript>
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
</noscript>
@include('boutique.dashboard.styles')
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
    'sun'       => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.$s.'><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>',
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

    /* ── Comparaison sectorielle (Plan Pro) ──
       Moyenne anonyme du revenu du mois chez les autres boutiques du même type
       (ex: "Pharmacie") et du même pays. Seuil minimum de 3 boutiques pour rester
       anonyme (jamais assez pour deviner le chiffre d'une boutique en particulier)
       et statistiquement pertinent — sinon la carte ne s'affiche pas du tout. */
    $sectorAvg = null; $sectorDelta = null; $sectorCount = 0;
    if ($shop->type) {
        $sectorShopIds = \App\Models\Shop::where('type', $shop->type)
            ->where('country', $shop->country)
            ->where('is_approved', true)
            ->where('id', '!=', $shop->id)
            ->pluck('id');
        $sectorCount = $sectorShopIds->count();
        if ($sectorCount >= 3) {
            $sectorTotal = (float) \App\Models\Order::whereIn('shop_id', $sectorShopIds)
                ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)
                ->where('status', 'livrée')->sum('total');
            $sectorAvg = $sectorTotal / $sectorCount;
            $sectorDelta = $sectorAvg > 0 ? round((($caGrossMonth - $sectorAvg) / $sectorAvg) * 100, 1) : ($caGrossMonth > 0 ? 100 : 0);
        }
    }
    $cmdLivreesPrev  = $shop->orders()->whereMonth('created_at',$now->copy()->subMonth()->month)->whereYear('created_at',$now->copy()->subMonth()->year)->where('status','livrée')->count();
    $panierPrev      = $cmdLivreesPrev > 0 ? round($caNetPrev / $cmdLivreesPrev) : 0;
    $panierDelta     = $panierPrev > 0 ? round((($panier - $panierPrev) / $panierPrev) * 100, 1) : ($panier > 0 ? 100 : 0);
    $totalCmdMonth = $shop->orders()->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->whereNotIn('status',['annulée','cancelled'])->count();
    // $livres était une requête strictement identique à $cmdLivreesMonth (calculée juste
    // au-dessus) — réutilisée au lieu d'interroger la base une 2e fois pour la même chose.
    $livres        = $cmdLivreesMonth;
    $tauxLiv       = $totalCmdMonth > 0 ? round(($livres / $totalCmdMonth) * 100, 1) : 0;
    /* Performance : ces 3 blocs (revenu/commissions/commandes des 14 derniers jours)
       tournaient auparavant en boucle jour par jour — 2 à 3 requêtes SQL séparées
       PAR JOUR, soit ~35 requêtes rien que pour ça. Remplacé par une requête groupée
       par jour pour chaque source de données (3 requêtes au total), puis simple
       lecture en mémoire dans les boucles ci-dessous — mêmes résultats, beaucoup
       moins d'allers-retours vers la base de données. */
    $range14Start   = $now->copy()->subDays(13)->startOfDay();
    $revenueByDay   = $shop->orders()->where('status', 'livrée')->where('created_at', '>=', $range14Start)
        ->selectRaw('DATE(created_at) as d, SUM(total) as t')->groupBy('d')->pluck('t', 'd');
    $commissionsByDay = \App\Models\CourierCommission::query()
        ->join('orders', 'orders.id', '=', 'courier_commissions.order_id')
        ->where('orders.shop_id', $shop->id)->where('courier_commissions.status', 'payée')
        ->where('orders.created_at', '>=', $range14Start)
        ->selectRaw('DATE(orders.created_at) as d, SUM(courier_commissions.amount) as t')->groupBy('d')->pluck('t', 'd');

    $days7 = collect(range(6,0))->map(function ($i) use ($now, $_dayLabel, $revenueByDay, $commissionsByDay) {
        $d        = $now->copy()->subDays($i);
        $caJour   = (float) ($revenueByDay[$d->toDateString()] ?? 0);
        $commJour = (float) ($commissionsByDay[$d->toDateString()] ?? 0);
        return ['label' => $_dayLabel($d), 'value' => max(0, $caJour - $commJour), 'today' => $i === 0];
    })->values();
    $max7 = $days7->max('value') ?: 1;
    $prev7Total = (float) collect(range(13,7))->sum(function ($i) use ($now, $revenueByDay, $commissionsByDay) {
        $day = $now->copy()->subDays($i)->toDateString();
        $caJ   = (float) ($revenueByDay[$day] ?? 0);
        $commJ = (float) ($commissionsByDay[$day] ?? 0);
        return max(0, $caJ - $commJ);
    });
    $recentOrders = $shop->orders()->with('user')->latest()->take(6)->get();
    /* Bug corrigé : la carte affiche "ventes du mois" mais comptait en fait TOUTES
       les ventes depuis toujours (aucun filtre de date) — désormais bien limité au
       mois en cours, comme "Produits à risque" juste en dessous sur la page, et
       comme la page de classement complète (route products.top) qui elle était déjà
       correcte. On exclut aussi les commandes annulées (une vente annulée n'est pas
       un succès) et on compte les UNITÉS vendues (somme des quantités), pas juste le
       nombre de commandes : un produit acheté ×3 dans une même commande doit compter
       plus qu'un produit acheté ×1 dans 3 commandes différentes. */
    $topProducts = $shop->products()
        ->withSum(['orderItems as unites_vendues' => function ($q) use ($now) {
            $q->whereHas('order', function ($o) use ($now) {
                $o->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)
                  ->whereNotIn('status', ['annulée', 'cancelled']);
            });
        }], 'quantity')
        ->having('unites_vendues', '>', 0)->orderByDesc('unites_vendues')->take(5)->get();
    $maxSales    = $topProducts->max('unites_vendues') ?: 1;
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
    $avColors = ['#10b981','#1668D8','#f59e0b','#1F7CF6','#14b8a6','#f43f5e'];
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
        ['key'=>'en_livraison','label'=>'En livraison','count'=>$shop->orders()->whereIn('status',['en_livraison','delivering','shipped'])->count(),'color'=>'#1668D8','bg'=>'#EAF2FF','ico'=>$I['truck_k']],
        ['key'=>'terminees',   'label'=>'Terminées','count'=>$cmdLivreesMonth,'color'=>'#22c55e','bg'=>'#dcfce7','ico'=>$I['trophy_k']], // même valeur que $cmdLivreesMonth, calculée plus haut — requête en moins
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
    $countsByDay = $shop->orders()->whereNotIn('status', ['annulée', 'cancelled'])->where('created_at', '>=', $now->copy()->subDays(6)->startOfDay())
        ->selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd');
    $cmdDays7 = collect(range(6,0))->map(function($i) use ($now, $_dayLabel, $countsByDay) {
        $d   = $now->copy()->subDays($i);
        $cnt = (int) ($countsByDay[$d->toDateString()] ?? 0);
        return ['label' => $_dayLabel($d), 'count' => $cnt, 'today' => $i === 0];
    })->values();
    $maxCmd7 = $cmdDays7->max('count') ?: 1;

    // ── Plan & limites ──
    $isPro           = $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
    $isOnTrial       = $isPro && app(\App\Services\SubscriptionService::class)->shopIsOnTrial($shop);
    $maxProduits     = 5;     // limite plan gratuit
    $maxCmdMois      = 10;    // limite plan gratuit
    $usageProdPct    = $isPro ? 100 : min(100, round(($totalProduits / $maxProduits) * 100));
    $usageCmdPct     = $isPro ? 100 : min(100, round(($cmdMonth / $maxCmdMois) * 100));
    $prodClass       = $isPro ? 'ok' : ($usageProdPct >= 100 ? 'danger' : ($usageProdPct >= 80 ? 'warn' : 'ok'));
    $cmdClass        = $isPro ? 'ok' : ($usageCmdPct >= 100 ? 'danger' : ($usageCmdPct >= 80 ? 'warn' : 'ok'));

    /* ── Refonte « espace vendeur » : blocs Bonjour / À traiter / Meilleurs clients / Livraison ── */
    $pendingStatuses   = ['pending', 'en attente', 'en_attente'];
    $unassignedQuery   = fn () => $shop->orders()->whereIn('status', $pendingStatuses)
        ->whereNull('driver_id')->whereNull('delivery_company_id')->whereNull('livreur_id');
    $unassignedCount   = $unassignedQuery()->count();
    // Les plus anciennes d'abord : ce sont elles qui attendent depuis le plus longtemps
    $urgentOrders      = $unassignedQuery()->with(['user:id,name', 'items.product:id,name'])->oldest()->take(4)->get();
    // Meilleurs clients : montant des commandes livrées (vraies données, aucune estimation)
    $topClients = $shop->orders()->where('status', 'livrée')
        ->selectRaw('user_id, client_name, SUM(total) as spent, COUNT(*) as n')
        ->groupBy('user_id', 'client_name')->orderByDesc('spent')->take(3)->get();
    $topClientNames = \App\Models\User::whereIn('id', $topClients->pluck('user_id')->filter())->pluck('name', 'id');
    $topClientMax   = (float) ($topClients->max('spent') ?: 1);
    $firstName      = $parts[0] ?? auth()->user()->name;
    $shopPublicUrl  = route('public.shops.products', $shop);
    // Pipeline en 3D : hauteur des colonnes proportionnelle au nombre de commandes
    $pipeMax   = max(1, collect($kanban)->max('count'));
    $pipeStyle = [
        'en_attente'   => ['#F59E0B', '#C27C08', '#FBBF4A', 'en_attente'],
        'confirmees'   => ['#1F7CF6', '#1260C9', '#5BA0FA', 'confirmée'],
        'en_livraison' => ['#7DB4FF', '#5B95E6', '#A9CDFF', 'en_livraison'],
        'terminees'    => ['#15803D', '#0F5F2D', '#3FA663', 'livrée'],
        'annulees'     => ['#DC2626', '#A61B1B', '#EF6262', 'annulée'],
    ];
@endphp

{{-- ══ MODAL CHAT BOUTIQUE ↔ ENTREPRISE (depuis la cloche) ══ --}}
@include('boutique.dashboard.chat-modal')

<div class="dash-wrap" id="dashWrap">

    {{-- SIDEBAR --}}
    @include('boutique.dashboard.sidebar')

    <div class="sb-overlay" id="sbOverlay"></div>

    <main class="main">

        {{-- ══ BANNIÈRES : paiement réussi / échoué, photos non affichées ══ --}}
        @include('boutique.dashboard.banners')

        {{-- ══ TOPBAR RESPONSIVE ══ --}}
        @include('boutique.dashboard.topbar')

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
            @include('boutique.dashboard.plan-banner')

            {{-- ══ BONJOUR + VISITES (maquette « Espace vendeur ») ══ --}}
            @include('boutique.dashboard.overview')

            {{-- KPI Grid --}}
            @include('boutique.dashboard.kpis')



            {{-- BLOCS A à D : aujourd'hui, alertes, kanban des commandes, meilleurs clients · actions rapides · livraison --}}
            @include('boutique.dashboard.activity')

            {{-- CHARTS DUO : Revenus + Commandes 7j --}}
            @include('boutique.dashboard.charts')

            {{-- CONTENT GRID --}}
            @include('boutique.dashboard.content-grid')

            {{-- TOP PRODUITS · COMPARAISON SECTEUR · PRODUITS À RISQUE --}}
            @include('boutique.dashboard.insights')

            {{-- SÉLECTEUR DE PÉRIODE + RÉSUMÉ RAPIDE --}}
            @include('boutique.dashboard.period')

        </div>{{-- /content --}}
    </main>
</div>{{-- /dash-wrap --}}

{{-- ══ ASSISTANT D'ANALYSE DES VENTES (IA) ══
     JS dans resources/js/boutique-dashboard.js.
     CSS remis ici en ligne (le fichier externe ne s'affichait pas correctement en prod). --}}
@include('boutique.dashboard.ai-assistant')

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
    assistantChatUrl: @json(route('boutique.assistant.chat')),
    assistantResetUrl: @json(route('boutique.assistant.reset')),
};
</script>
@vite(['resources/js/boutique-dashboard.js'])
@endpush

@push('scripts')
<script>
/* ══ Refonte « espace vendeur » : scène 3D du bandeau, partage, compteurs ══ */
(function () {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Scène 3D : bascule selon la souris (±6° vertical, ±13° horizontal autour de la pose de repos)
    const hello = document.getElementById('svHello'), stage = document.getElementById('svStage');
    if (hello && stage && !reduce && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        hello.addEventListener('pointermove', e => {
            const r = hello.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
            stage.style.setProperty('--rx', (6 - (y - .5) * 12).toFixed(2) + 'deg');
            stage.style.setProperty('--ry', (-16 + (x - .5) * 26).toFixed(2) + 'deg');
        });
        hello.addEventListener('pointerleave', () => { stage.style.removeProperty('--rx'); stage.style.removeProperty('--ry'); });
    }

    // Petit message en bas de l'écran
    function toast(msg) {
        document.querySelector('.sv-toast')?.remove();
        const t = document.createElement('div');
        t.className = 'sv-toast'; t.setAttribute('role', 'status'); t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 2600);
    }

    // Partager ma boutique : feuille de partage du téléphone, sinon copie du lien
    document.querySelectorAll('[data-share-shop]').forEach(btn => btn.addEventListener('click', async () => {
        const url = btn.dataset.shareShop, title = btn.dataset.shareName;
        try {
            if (navigator.share) { await navigator.share({ title, url }); return; }
            await navigator.clipboard.writeText(url);
            toast('Lien de la boutique copié');
        } catch (e) {
            if (e && e.name === 'AbortError') return;
            window.prompt('Copiez le lien de votre boutique :', url);
        }
    }));

    // Compteurs : montent jusqu'à leur valeur réelle (le chiffre exact est déjà dans la page)
    if (!reduce && 'IntersectionObserver' in window) {
        const fmt = n => Math.round(n).toLocaleString('fr-FR');
        const io = new IntersectionObserver(entries => entries.forEach(en => {
            if (!en.isIntersecting) return;
            io.unobserve(en.target);
            const el = en.target, end = parseInt(el.dataset.count, 10) || 0;
            if (end < 2) return;
            const t0 = performance.now(), dur = 900;
            (function step(t) {
                const p = Math.min(1, (t - t0) / dur), eased = 1 - Math.pow(1 - p, 3);
                el.textContent = fmt(end * eased);
                if (p < 1) requestAnimationFrame(step);
            })(t0);
        }), { threshold: .4 });
        document.querySelectorAll('[data-count]').forEach(el => io.observe(el));
    }
})();
</script>
@endpush
