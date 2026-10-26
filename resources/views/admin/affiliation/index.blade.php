{{--
    Équipe Shopio → Affiliation : chiffres du programme, liste des affiliés (statut, taux), nouvel affilié externe.
    Données : Admin\AffiliationController@index
--}}
@extends('layouts.app')

@section('title', 'Affiliation · Équipe Shopio')

@php
    $bodyClass = 'is-dashboard';
    use App\Enums\AffiliateStatus;
    use App\Models\Affiliate;
    use App\Support\Money;
@endphp

@push('styles')
@include('admin.affiliation._styles')
@endpush

@section('content')
@include('admin.affiliation._header', ['active' => 'index'])

<main class="ac-page">
    <div>
        <h1 class="ac-h1">Affiliation</h1>
        <p class="ac-sub">Boutiques et partenaires qui font connaître Shopio, et leurs commissions sur les abonnements.</p>
    </div>

    @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="cp-flash cp-flash-err" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    @if($summary['toValidate'] > 0 || $summary['flagged'] > 0 || $summary['payouts'] > 0)
    <div class="ac-alerts">
        @if($summary['toValidate'] > 0)<a class="ac-alert" href="{{ route('admin.affiliation.index', ['statut' => 'pending']) }}"><strong>{{ $summary['toValidate'] }}</strong> affilié(s) à valider</a>@endif
        @if($summary['flagged'] > 0)<a class="ac-alert" href="{{ route('admin.affiliation.referrals') }}"><strong>{{ $summary['flagged'] }}</strong> parrainage(s) à vérifier</a>@endif
        @if($summary['payouts'] > 0)<a class="ac-alert" href="{{ route('admin.affiliation.payouts') }}"><strong>{{ $summary['payouts'] }}</strong> retrait(s) à verser · {{ Money::gnf($summary['payoutsTotal']) }}</a>@endif
    </div>
    @endif

    <section class="cp-kpis af-kpis3" aria-label="Chiffres du programme">
        <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Affiliés actifs</div><div class="cp-kpi-val">{{ $summary['affiliates'] }}</div></div>
        <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Clics (30 jours)</div><div class="cp-kpi-val">{{ Money::gnf($summary['clicks30'], false) }}</div></div>
        <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Boutiques parrainées</div><div class="cp-kpi-val">{{ $summary['referrals'] }} <small>dont {{ $summary['paying'] }} abonnées</small></div></div>
        <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Commissions en attente</div><div class="cp-kpi-val">{{ Money::gnf($summary['pending'], false) }} <small>GNF</small></div></div>
        <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Disponibles (dues)</div><div class="cp-kpi-val">{{ Money::gnf($summary['approved'], false) }} <small>GNF</small></div></div>
        <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Déjà versées</div><div class="cp-kpi-val">{{ Money::gnf($summary['paid'], false) }} <small>GNF</small></div></div>
    </section>

    {{-- Nouvel affilié externe --}}
    <details class="cp-card af-new" @if($errors->hasAny(['name', 'email', 'phone', 'code', 'commission_rate', 'payout_method', 'payout_phone'])) open @endif>
        <summary class="cp-list-hd" style="border-bottom:0">
            <h2>Ajouter un affilié externe</h2>
            <span class="cp-btn cp-btn-line cp-btn-sm">{!! \App\Support\IconLibrary::svg('plus', '', 16) !!} Nouveau</span>
        </summary>
        <form method="POST" action="{{ route('admin.affiliation.affiliates.store') }}" class="af-form" style="border-top:1px solid var(--border)">
            @csrf
            <p class="ac-help" style="margin:0">Influenceur, association, agent commercial… Les vendeurs Shopio, eux, ont déjà leur compte dans leur espace (menu Parrainage).</p>
            <div class="af-grid">
                <div class="ac-field"><label for="afName">Nom</label><input id="afName" name="name" class="ac-input" required maxlength="120" value="{{ old('name') }}"></div>
                <div class="ac-field"><label for="afEmail">E-mail</label><input id="afEmail" name="email" type="email" class="ac-input" value="{{ old('email') }}"></div>
                <div class="ac-field"><label for="afPhone">Téléphone</label><input id="afPhone" name="phone" type="tel" class="ac-input" value="{{ old('phone') }}"></div>
                <div class="ac-field"><label for="afCodeIn">Code (facultatif)</label><input id="afCodeIn" name="code" class="ac-input" maxlength="12" placeholder="Créé à partir du nom" value="{{ old('code') }}" style="text-transform:uppercase"></div>
                <div class="ac-field"><label for="afRate">Commission (%)</label><input id="afRate" name="commission_rate" type="number" step="0.5" min="0" max="100" class="ac-input" placeholder="Taux par défaut" value="{{ old('commission_rate') }}"></div>
                <div class="ac-field"><label for="afMethod">Retrait sur</label>
                    <select id="afMethod" name="payout_method" class="ac-select"><option value="">Plus tard</option>
                        @foreach(Affiliate::PAYOUT_METHODS as $value => $label)<option value="{{ $value }}" @selected(old('payout_method') === $value)>{{ $label }}</option>@endforeach
                    </select></div>
                <div class="ac-field"><label for="afPayPhone">Numéro de retrait</label><input id="afPayPhone" name="payout_phone" type="tel" class="ac-input" value="{{ old('payout_phone') }}"></div>
            </div>
            <label class="af-check"><input type="checkbox" name="with_access" value="1" @checked(old('with_access'))> Créer un accès à son tableau de bord (il reçoit un e-mail pour choisir son mot de passe)</label>
            <div><button type="submit" class="cp-btn cp-btn-blue">Créer l'affilié</button></div>
        </form>
    </details>

    {{-- Affiliés --}}
    <section class="cp-card">
        <div class="cp-list-hd">
            <h2>Affiliés</h2>
            <form method="GET" class="af-inline" role="search">
                <label class="sr-only" for="afQ">Rechercher</label>
                <input id="afQ" name="q" class="ac-input" placeholder="Nom, code, e-mail" value="{{ $q }}">
                <label class="sr-only" for="afType">Type</label>
                <label class="sr-only" for="afStatus">Statut</label>
                <select id="afStatus" name="statut" class="ac-select">
                    <option value="">Tous les statuts</option>
                    @foreach(AffiliateStatus::cases() as $st)<option value="{{ $st->value }}" @selected($status === $st->value)>{{ $st->label() }}</option>@endforeach
                </select>
                <select id="afType" name="type" class="ac-select">
                    <option value="">Tous</option>
                    <option value="vendor" @selected($type === 'vendor')>Vendeurs</option>
                    <option value="external" @selected($type === 'external')>Externes</option>
                </select>
                <button class="cp-btn cp-btn-line cp-btn-sm" type="submit">Filtrer</button>
            </form>
        </div>
        @if($affiliates->isEmpty())
            <div class="cp-empty"><strong>Aucun affilié</strong><span>Un vendeur devient affilié en ouvrant le menu Parrainage de son espace.</span></div>
        @else
        <table class="cp-table">
            <thead><tr><th>Affilié</th><th>Code</th><th>Filleuls</th><th>Gagné</th><th>Statut et taux</th></tr></thead>
            <tbody>
            @foreach($affiliates as $affiliate)
                <tr class="{{ $affiliate->status === AffiliateStatus::Pending ? 'is-todo' : '' }}">
                    <td class="cp-cell-main">
                        <div class="cp-row-name">{{ $affiliate->displayName() }} @if($affiliate->status === AffiliateStatus::Pending)<span class="cp-st warn">À valider</span>@endif</div>
                        <div class="cp-row-sub">{{ $affiliate->type->label() }} · {{ $affiliate->contactEmail() ?? 'sans e-mail' }}@if($affiliate->contactPhone()) · {{ $affiliate->contactPhone() }}@endif · depuis le {{ $affiliate->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td data-label="Code"><span class="af-code">{{ $affiliate->code }}</span></td>
                    <td data-label="Filleuls" class="cp-num">{{ $affiliate->referrals_count }} <span class="cp-row-sub" style="display:inline">({{ $affiliate->paying_count }} abonnées)</span></td>
                    <td data-label="Gagné" class="cp-num">{{ Money::gnf($affiliate->earned ?? 0) }}</td>
                    <td class="cp-cell-act">
                        @if($affiliate->status === AffiliateStatus::Pending)
                        <div class="af-actions">
                            <form method="POST" action="{{ route('admin.affiliation.affiliates.approve', $affiliate) }}">@csrf
                                <button class="cp-btn cp-btn-blue cp-btn-sm" type="submit">Valider</button>
                            </form>
                            <form method="POST" action="{{ route('admin.affiliation.affiliates.reject', $affiliate) }}" class="af-inline">@csrf
                                <label class="sr-only" for="rj{{ $affiliate->id }}">Motif du refus</label>
                                <input id="rj{{ $affiliate->id }}" name="reason" class="ac-input" placeholder="Motif (facultatif)" maxlength="255">
                                <button class="cp-btn cp-btn-danger cp-btn-sm" type="submit">Refuser</button>
                            </form>
                        </div>
                        @else
                        <form method="POST" action="{{ route('admin.affiliation.affiliates.update', $affiliate) }}" class="af-inline">
                            @csrf @method('PUT')
                            <label class="sr-only" for="st{{ $affiliate->id }}">Statut</label>
                            <select id="st{{ $affiliate->id }}" name="status" class="ac-select">
                                @foreach(AffiliateStatus::cases() as $st)<option value="{{ $st->value }}" @selected($affiliate->status === $st)>{{ $st->label() }}</option>@endforeach
                            </select>
                            <label class="sr-only" for="rt{{ $affiliate->id }}">Commission en %</label>
                            <input id="rt{{ $affiliate->id }}" name="commission_rate" type="number" step="0.5" min="0" max="100" class="ac-input af-rate" value="{{ (float) $affiliate->commission_rate }}">
                            <span aria-hidden="true">%</span>
                            <button class="cp-btn cp-btn-line cp-btn-sm" type="submit">Enregistrer</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($affiliates->hasPages())<div class="cp-pagination">{{ $affiliates->links() }}</div>@endif
        @endif
    </section>
</main>
@endsection
