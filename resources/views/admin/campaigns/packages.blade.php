{{--
    Équipe Shopio → Formules des campagnes : nom, prix, places et disponibilité.
    Les prix modifiés valent pour les prochaines commandes ; les campagnes déjà commandées gardent leur prix.
    Données : Admin\AdCampaignController@packages
--}}
@extends('layouts.app')

@section('title', 'Formules des campagnes · Équipe Shopio')

@php
    $bodyClass = 'is-dashboard';
    use App\Support\Campaigns\CampaignPricing as P;
@endphp

@push('styles')
@include('admin.campaigns._styles')
<style>
.ap-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; }
.ap-card { padding: 22px; display: grid; gap: 14px; align-content: start; }
.ap-head { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
.ap-code { font-size: 12px; color: var(--text-2); margin-top: 2px; }
.ap-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; }
.ap-fields .ac-input { width: 100%; font-variant-numeric: tabular-nums; }
.ap-total { font-size: 14px; color: var(--text-2); }
.ap-total strong { color: var(--text); font-variant-numeric: tabular-nums; }
.ap-switch { display: flex; gap: 10px; align-items: center; font-size: 14px; font-weight: 600; cursor: pointer; }
.ap-switch input { width: 20px; height: 20px; margin: 0; accent-color: var(--sv-btn); }
.ap-sec { margin: 0; font-size: 18px; font-weight: 800; }
</style>
@endpush

@section('content')
@include('admin.campaigns._header', ['active' => 'packages'])

<main class="ac-page">
    <div>
        <h1 class="ac-h1">Formules</h1>
        <p class="ac-sub">Les nouveaux prix valent pour les prochaines commandes. Les campagnes déjà commandées gardent leur prix.</p>
    </div>

    @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="cp-flash cp-flash-err" role="alert"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    @foreach(['boost' => 'Mise en avant Shopio', 'social' => 'Réseaux sociaux'] as $type => $title)
    @if(isset($packages[$type]))
    <h2 class="ap-sec">{{ $title }}</h2>
    <div class="ap-list">
        @foreach($packages[$type] as $p)
        <form method="POST" action="{{ route('admin.campaigns.packages.update', $p) }}" class="cp-card ap-card">
            @csrf @method('PUT')
            <div class="ap-head">
                <div>
                    <div class="ac-field">
                        <label for="name{{ $p->id }}" class="sr-only">Nom</label>
                        <input id="name{{ $p->id }}" name="name" value="{{ old('name', $p->name) }}" class="ac-input" style="font-weight:700;font-size:16px" maxlength="60" required>
                    </div>
                    <div class="ap-code">{{ $p->details['promotes'] ?? '' }}{{ isset($p->details['where']) ? ' · ' . $p->details['where'] : '' }}@if($p->isSocial()){{ $p->duration_days }} jours · {{ $p->details['images'] ?? 0 }} visuel(s), {{ $p->details['videos'] ?? 0 }} vidéo(s)@endif</div>
                </div>
                <span class="cp-st {{ $p->is_active ? 'ok' : 'muted' }}">{{ $p->is_active ? 'Proposée' : 'Masquée' }}</span>
            </div>

            @if($p->isSocial())
            <div class="ap-fields">
                <div class="ac-field"><label for="bud{{ $p->id }}">Budget pub (GNF)</label><input id="bud{{ $p->id }}" name="ad_budget_gnf" type="number" min="0" step="1000" class="ac-input" value="{{ $p->ad_budget_gnf }}" required></div>
                <div class="ac-field"><label for="fee{{ $p->id }}">Frais Shopio (GNF)</label><input id="fee{{ $p->id }}" name="fee_gnf" type="number" min="0" step="1000" class="ac-input" value="{{ $p->fee_gnf }}" required></div>
            </div>
            <div class="ap-total">Prix payé par le vendeur : <strong>{{ P::format($p->priceFor($p->duration_days) ?? 0) }}</strong></div>
            @else
            <div class="ap-fields">
                @foreach($p->durations() as $d)
                <div class="ac-field"><label for="pr{{ $p->id }}-{{ $d }}">{{ $d }} jours (GNF)</label><input id="pr{{ $p->id }}-{{ $d }}" name="prices[{{ $d }}]" type="number" min="1000" step="1000" class="ac-input" value="{{ $p->priceFor($d) }}" required></div>
                @endforeach
                <div class="ac-field"><label for="cap{{ $p->id }}">Places {{ $p->capacity_scope === 'category' ? 'par catégorie' : 'en tout' }}</label><input id="cap{{ $p->id }}" name="max_actives" type="number" min="1" max="100" class="ac-input" value="{{ $p->max_actives }}" required></div>
            </div>
            @endif

            <div class="ap-total">{{ $usage[$p->id] ?? 0 }} campagne{{ ($usage[$p->id] ?? 0) > 1 ? 's' : '' }} en cours ou à venir avec cette formule</div>
            <input type="hidden" name="is_active" value="0">
            <label class="ap-switch"><input type="checkbox" name="is_active" value="1" @checked($p->is_active)> Proposer cette formule aux vendeurs</label>
            <div><button class="cp-btn cp-btn-blue cp-btn-sm">Enregistrer</button></div>
        </form>
        @endforeach
    </div>
    @endif
    @endforeach
</main>
@endsection
