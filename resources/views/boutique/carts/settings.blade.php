{{--
    resources/views/boutique/carts/settings.blade.php
    Route     : GET /boutique/paniers-abandonnes/reglages → Boutique\AbandonedCartController@editSettings → name('boutique.carts.settings')
    Rôle      : Réglages des paniers abandonnés : collecte, délai avant « À rappeler », page visible par les closers,
                message WhatsApp ({prenom}, {boutique}, {produits}, {total}). Notifications : à venir.
    Variables : $shop, $settings, $delays
--}}
@extends('layouts.app')
@section('title', 'Réglages paniers abandonnés · ' . $shop->name)
@php $bodyClass = 'is-dashboard'; $tab = request('onglet') === 'notifications' ? 'notifications' : 'general'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.cs { padding: 18px 28px 80px; max-width: 760px; width: 100%; }
.cs-back { display: inline-grid; place-items: center; width: 34px; height: 34px; border-radius: 9px; color: var(--text-2); text-decoration: none; box-shadow: inset 0 0 0 1px var(--border); background: var(--surface); }
.cs-head { display: flex; align-items: flex-start; gap: 12px; flex-wrap: wrap; margin: 14px 0; }
.cs-head h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.02em; }
.cs-head p { margin: 4px 0 0; color: var(--text-2); font-size: 13.5px; }
.cs-head > div { flex: 1; min-width: 220px; }
.cs-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 0 16px; border: 0; border-radius: 10px; font: 700 13.5px var(--font); cursor: pointer; background: var(--sv-btn); color: #fff; }
.cs-tabs { display: flex; gap: 18px; border-bottom: 1px solid var(--border); margin-bottom: 14px; }
.cs-tabs a { padding: 8px 2px; font-weight: 700; font-size: 13.5px; color: var(--text-2); text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; }
.cs-tabs a[aria-current="page"] { color: var(--text); border-bottom-color: var(--text); }
.cs-card { padding: 16px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); margin-bottom: 12px; display: grid; gap: 12px; }
.cs-card h2 { margin: 0; font-size: 15px; }
.cs-card > p { margin: -8px 0 0; color: var(--text-2); font-size: 12.5px; }
.cs-row { display: flex; align-items: center; gap: 12px; padding-top: 10px; border-top: 1px solid var(--border); }
.cs-row:first-of-type { border-top: 0; padding-top: 0; }
.cs-row > div { flex: 1; min-width: 0; }
.cs-row b { display: block; font-size: 13.5px; }
.cs-row small { display: block; color: var(--text-2); font-size: 12px; margin-top: 2px; }
.cs-sw { position: relative; width: 44px; height: 26px; flex-shrink: 0; }
.cs-sw input { position: absolute; inset: 0; opacity: 0; margin: 0; cursor: pointer; z-index: 1; }
.cs-sw span { position: absolute; inset: 0; border-radius: 999px; background: #CBD5E1; transition: background-color 180ms; }
.cs-sw span::after { content: ''; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: #fff; transition: transform 200ms; box-shadow: 0 1px 3px rgba(0,0,0,.25); }
.cs-sw input:checked + span { background: #22C55E; }
.cs-sw input:checked + span::after { transform: translateX(18px); }
.cs-sw input:focus-visible + span { outline: 3px solid #7DB4FF; outline-offset: 2px; }
.cs-select, .cs-area { min-height: 40px; padding: 8px 12px; border: 0; border-radius: 10px; background: var(--bg); color: var(--text); font: 600 13.5px var(--font); box-shadow: inset 0 0 0 1px var(--border); }
.cs-area { width: 100%; min-height: 130px; font-weight: 500; line-height: 1.5; resize: vertical; }
.cs-flash { padding: 12px 14px; border-radius: 12px; font-weight: 700; font-size: 13.5px; margin-bottom: 12px; }
.cs-flash.is-ok { background: var(--sv-ok50); color: var(--sv-ok); } .cs-flash.is-err { background: var(--sv-err50); color: var(--sv-err); }
@media (max-width: 900px) { .cs { padding: 14px 14px 80px; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'orders'])
    <main class="main vs-main">
        <form class="cs" method="POST" action="{{ route('boutique.carts.settings.update') }}">
            @csrf @method('PUT')
            <a class="cs-back" href="{{ route('boutique.carts.index') }}" aria-label="Retour aux paniers abandonnés">‹</a>
            <div class="cs-head">
                <div><h1>Réglages paniers abandonnés</h1><p>Ce qui est enregistré, quand un client apparaît, et le message pour le relancer.</p></div>
                @if($tab === 'general')<button type="submit" class="cs-btn">Sauvegarder</button>@endif
            </div>
            @if(session('success'))<p class="cs-flash is-ok" role="status">{{ session('success') }}</p>@endif
            @if($errors->any())<p class="cs-flash is-err" role="alert">{{ $errors->first() }}</p>@endif
            <nav class="cs-tabs"><a href="{{ route('boutique.carts.settings') }}" @if($tab === 'general') aria-current="page" @endif>Général</a><a href="{{ route('boutique.carts.settings', ['onglet' => 'notifications']) }}" @if($tab === 'notifications') aria-current="page" @endif>Notifications</a></nav>

            @if($tab === 'general')
            <section class="cs-card">
                <h2>Enregistrement</h2>
                <p>Les clients qui laissent leur numéro dans le formulaire de commande sans valider.</p>
                <div class="cs-row"><div><b id="csEnL">Activer la collecte des paniers abandonnés</b><small>Coupée : plus aucun nouveau client n’est enregistré. Ceux déjà enregistrés restent.</small></div><label class="cs-sw"><input type="checkbox" name="enabled" value="1" aria-labelledby="csEnL" @checked(old('enabled', $settings['enabled']))><span></span></label></div>
                <div class="cs-row"><div><b><label for="csDelay">Délai avant le panier abandonné</label></b><small>Temps sans action du client avant qu’il apparaisse dans « À rappeler ».</small></div><select class="cs-select" name="delay" id="csDelay">@foreach($delays as $k => $l)<option value="{{ $k }}" @selected((int) old('delay', $settings['delay']) === $k)>{{ $l }}</option>@endforeach</select></div>
                <div class="cs-row"><div><b id="csClL">Page visible par les closers</b><small>Coupée : les employés de la boutique ne voient plus la page des paniers abandonnés.</small></div><label class="cs-sw"><input type="checkbox" name="closers" value="1" aria-labelledby="csClL" @checked(old('closers', $settings['closers']))><span></span></label></div>
            </section>
            <section class="cs-card">
                <h2><label for="csWa">Message WhatsApp</label></h2>
                <p>Le message pré-rempli quand vous touchez « WhatsApp » sur un client.</p>
                <textarea class="cs-area" name="wa" id="csWa" maxlength="600">{{ old('wa', $settings['wa']) }}</textarea>
                <small style="color:var(--text-2)">Remplacés automatiquement : <code>{prenom}</code> le prénom du client, <code>{boutique}</code> le nom de votre boutique, <code>{produits}</code> son panier, <code>{total}</code> le montant.</small>
            </section>
            @else
            <section class="cs-card">
                <h2>Notifications</h2>
                <p>Être prévenu sur votre téléphone quand un client part sans commander.</p>
                <div class="cs-row"><div><b>Notification à chaque panier abandonné</b><small>Pas encore disponible. En attendant, la pastille de l’onglet « Paniers abandonnés » montre les clients à rappeler.</small></div><label class="cs-sw"><input type="checkbox" disabled aria-label="Notification à chaque panier abandonné"><span></span></label></div>
            </section>
            @endif
        </form>
    </main>
</div>
@endsection
