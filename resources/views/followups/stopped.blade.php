{{-- Confirmation après le lien « Ne plus recevoir ces rappels » d'une relance. Données : FollowUpStopController --}}
@extends('layouts.app')

@section('title', 'Rappels arrêtés')

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
@include('boutique._partials.sv-theme')
<style>
.fs-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg);
    padding: 32px max(16px, env(safe-area-inset-right, 0px)) 32px max(16px, env(safe-area-inset-left, 0px)); }
.fs-card { max-width: 440px; width: 100%; background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 32px 28px; text-align: center; display: grid; gap: 14px; justify-items: center; }
.fs-card img { width: 52px; height: 52px; border-radius: 14px; }
.fs-card h1 { margin: 0; font-size: 22px; font-weight: 800; color: var(--text); }
.fs-card p { margin: 0; color: var(--text-2); font-size: 15px; line-height: 1.55; }
.fs-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 0 22px; border-radius: 999px; background: var(--sv-btn); color: #fff; font-weight: 700; text-decoration: none; }
.fs-btn:hover { background: var(--sv-btn-h); color: #fff; }
</style>
@endpush

@section('content')
<main class="fs-page">
    <div class="fs-card">
        <img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="Shopio" width="52" height="52">
        <h1>C'est noté</h1>
        <p>Tu ne recevras plus de rappels {{ $shop ? 'de ' . $shop->name : 'des boutiques Shopio' }}.
            Les messages sur tes commandes et tes livraisons continuent d'arriver normalement.</p>
        <a href="{{ url('/') }}" class="fs-btn">Aller sur Shopio</a>
    </div>
</main>
@endsection
