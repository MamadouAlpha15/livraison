{{--
    Espace vendeur → Parrainage : lien et QR code à partager, boutiques parrainées, commissions, retraits.
    Données : AffiliateDashboardController@index (contenu dans affiliation._panel)
--}}
@extends('layouts.app')

@section('title', 'Parrainage · ' . $shop->name)

@php
    $bodyClass = 'is-dashboard';
    use App\Support\IconLibrary as Ico;
@endphp

@push('styles')
@include('affiliation._styles')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'referral'])

    <main class="main vs-main">
        <div class="topbar cp-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="tb-info">
                <h1 class="cp-title">{!! Ico::svg('gift', '', 22) !!} Parrainage</h1>
                <div class="tb-sub">Invite d'autres vendeurs sur Shopio et gagne de l'argent sur leurs abonnements.</div>
            </div>
        </div>

        <div class="cp-content">
            @include('affiliation._panel')
        </div>
    </main>
</div>
@endsection

@push('styles')
@include('boutique._partials.sv-theme')
@endpush
