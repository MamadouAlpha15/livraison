{{--
    Tableau de bord d'un affilié externe (sans boutique) : lien et QR code, boutiques parrainées,
    commissions, retraits. Données : AffiliateDashboardController@index (contenu dans affiliation._panel)
--}}
@extends('layouts.app')

@section('title', 'Mon parrainage Shopio')

@php
    $bodyClass = 'is-dashboard';
    use App\Support\IconLibrary as Ico;
@endphp

@push('styles')
@include('boutique._partials.sv-theme')
@include('affiliation._styles')
<style>
.afx-bar { position: sticky; top: env(safe-area-inset-top, 0px); z-index: 40; background: var(--sv-night); color: #fff; }
[data-theme="dark"] .afx-bar { background: #07152B; border-bottom: 1px solid #1C3560; }
.afx-bar-in { max-width: 1180px; margin: 0 auto; display: flex; align-items: center; gap: 12px 20px; flex-wrap: wrap;
    padding: 12px max(16px, env(safe-area-inset-right, 0px)) 12px max(16px, env(safe-area-inset-left, 0px)); }
.afx-brand { display: inline-flex; align-items: center; gap: 10px; color: #fff; text-decoration: none; font-size: 16px; }
.afx-brand img { width: 34px; height: 34px; border-radius: 10px; }
.afx-brand-sub { color: var(--sv-sky); font-weight: 600; }
.afx-out { margin: 0 0 0 auto; display: flex; align-items: center; gap: 12px; }
.afx-me { font-size: 13px; color: rgba(255,255,255,.7); max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.afx-out button[type=submit], .afx-dark { background: none; border: 1px solid rgba(255,255,255,.25); color: #fff; border-radius: 999px; cursor: pointer; font: 600 13px var(--font); }
.afx-out button[type=submit] { padding: 7px 14px; }
.afx-dark { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; box-shadow: none; }
.afx-dark:hover, .afx-out button[type=submit]:hover { background: rgba(255,255,255,.1); }
.afx-page { max-width: 1180px; margin: 0 auto; width: 100%; }
.afx-page .cp-content { max-width: none; padding-left: max(16px, env(safe-area-inset-left, 0px)); padding-right: max(16px, env(safe-area-inset-right, 0px)); }
.afx-hd h1 { margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -.02em; }
.afx-hd p { margin: 4px 0 0; color: var(--text-2); font-size: 14px; }
@media (max-width: 760px) { .afx-me { display: none; } .afx-hd h1 { font-size: 20px; } }
</style>
@endpush

@section('content')
<header class="afx-bar">
    <div class="afx-bar-in">
        <a href="{{ route('affilie.dashboard') }}" class="afx-brand">
            <img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="" width="34" height="34">
            <span><strong>Shopio</strong> <span class="afx-brand-sub">Parrainage</span></span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="afx-out">
            @csrf
            @include('admin._partials.dark-toggle', ['class' => 'afx-dark'])
            <span class="afx-me">{{ auth()->user()->name }}</span>
            <button type="submit">Déconnexion</button>
        </form>
    </div>
</header>

<main class="afx-page">
    <div class="cp-content">
        <div class="afx-hd">
            <h1>Bonjour {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') ?: auth()->user()->name }}</h1>
            <p>Invite des vendeurs à ouvrir leur boutique sur Shopio et gagne de l'argent sur leurs abonnements.</p>
        </div>
        @include('affiliation._panel')
    </div>
</main>
@endsection
