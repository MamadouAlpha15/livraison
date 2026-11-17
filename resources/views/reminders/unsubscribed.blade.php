{{-- Confirmation après le lien « Me désinscrire » d'un e-mail de relance. Données : ReminderUnsubscribeController --}}
@extends('layouts.app')

@section('title', 'Désinscription des rappels')

@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
@include('boutique._partials.sv-theme')
<style>
.ru-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg);
    padding: 32px max(16px, env(safe-area-inset-right, 0px)) 32px max(16px, env(safe-area-inset-left, 0px)); }
.ru-card { max-width: 440px; width: 100%; background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 32px 28px; text-align: center; display: grid; gap: 14px; justify-items: center; }
.ru-card img { width: 52px; height: 52px; border-radius: 14px; }
.ru-card h1 { margin: 0; font-size: 22px; font-weight: 800; color: var(--text); }
.ru-card p { margin: 0; color: var(--text-2); font-size: 15px; line-height: 1.55; }
.ru-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 0 22px; border-radius: 999px; background: var(--sv-btn); color: #fff; font-weight: 700; text-decoration: none; }
.ru-btn:hover { background: var(--sv-btn-h); color: #fff; }
</style>
@endpush

@section('content')
<main class="ru-page">
    <div class="ru-card">
        <img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="Shopio" width="52" height="52">
        <h1>C'est noté</h1>
        <p>Tu ne recevras plus nos rappels « Tu nous manques ». Les messages sur tes commandes et tes livraisons continuent d'arriver normalement.</p>
        <a href="{{ url('/') }}" class="ru-btn">Aller sur Shopio</a>
    </div>
</main>
@endsection
