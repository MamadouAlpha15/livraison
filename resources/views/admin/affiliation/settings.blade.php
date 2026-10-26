{{--
    Équipe Shopio → Affiliation → Réglages : taux, durées, seuil de retrait, mois offert, plafond, critères « Active ».
    Données : Admin\AffiliationController@settings
--}}
@extends('layouts.app')

@section('title', 'Réglages · Affiliation · Équipe Shopio')

@php
    $bodyClass = 'is-dashboard';
    $fields = [
        ['default_rate', 'Commission par défaut', '%', 'Pour les nouveaux affiliés. Le taux de chaque affilié se change dans la liste.', '0.5'],
        ['commission_months', 'Durée des commissions', 'mois', 'Après le premier paiement de la boutique parrainée.', '1'],
        ['validation_days', 'Délai de validation', 'jours', 'Avant qu\'une commission soit retirable (le temps d\'un éventuel remboursement).', '1'],
        ['payout_threshold', 'Retrait minimum', 'GNF', 'Solde disponible minimum pour demander un retrait.', '1000'],
        ['reward_months', 'Mois offert au parrain vendeur', 'mois', 'Au premier paiement de sa boutique parrainée. 0 pour ne rien offrir.', '1'],
        ['annual_reward_cap', 'Plafond des mois offerts', 'mois / an', 'Par parrain et par année civile.', '1'],
        ['cookie_days', 'Durée du lien', 'jours', 'Temps pendant lequel le code reste mémorisé sur le téléphone du visiteur.', '1'],
        ['active_min_products', 'Boutique « Active » : produits', 'produits', 'Produits en ligne nécessaires.', '1'],
        ['active_min_orders', 'Boutique « Active » : commandes', 'commandes', 'Commandes reçues nécessaires (hors annulées).', '1'],
    ];
@endphp

@push('styles')
@include('admin.affiliation._styles')
<style>
.as-unit { display: flex; align-items: center; gap: 8px; }
.as-unit .ac-input { flex: 1; min-width: 0; font-variant-numeric: tabular-nums; }
.as-unit span { font-size: 13px; color: var(--text-2); white-space: nowrap; }
</style>
@endpush

@section('content')
@include('admin.affiliation._header', ['active' => 'settings'])

<main class="ac-page">
    <div>
        <h1 class="ac-h1">Réglages du programme</h1>
        <p class="ac-sub">Les changements valent pour les prochaines commissions et inscriptions. Les commissions déjà créées gardent leur montant et leur date.</p>
    </div>

    @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="cp-flash cp-flash-err" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.affiliation.settings.update') }}" class="cp-card af-form">
        @csrf @method('PUT')
        <div class="af-grid">
            @foreach($fields as [$name, $label, $unit, $help, $step])
            <div class="ac-field">
                <label for="s_{{ $name }}">{{ $label }}</label>
                <div class="as-unit">
                    <input id="s_{{ $name }}" name="{{ $name }}" type="number" min="0" step="{{ $step }}" class="ac-input" required
                           value="{{ old($name, $name === 'default_rate' ? (float) $settings->$name : $settings->$name) }}">
                    <span>{{ $unit }}</span>
                </div>
                <span class="ac-help">{{ $help }}</span>
                @error($name)<span class="ac-err">{{ $message }}</span>@enderror
            </div>
            @endforeach
        </div>
        <div><button type="submit" class="cp-btn cp-btn-blue">Enregistrer les réglages</button></div>
    </form>
</main>
@endsection
