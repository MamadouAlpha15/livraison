{{--
    Équipe Shopio → Affiliation → Commissions : toutes les commissions, export CSV, remboursement d'un paiement.
    Données : Admin\AffiliationController@commissions
--}}
@extends('layouts.app')

@section('title', 'Commissions · Affiliation · Équipe Shopio')

@php
    $bodyClass = 'is-dashboard';
    use App\Enums\CommissionStatus;
    use App\Support\Money;
    $filters = ['' => 'Toutes'] + collect(CommissionStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
@endphp

@push('styles')
@include('admin.affiliation._styles')
@endpush

@section('content')
@include('admin.affiliation._header', ['active' => 'commissions'])

<main class="ac-page">
    <div class="af-sec">
        <div>
            <h1 class="ac-h1">Commissions</h1>
            <p class="ac-sub">Créées à chaque abonnement payé par une boutique parrainée, disponibles après le délai de validation.</p>
        </div>
        <a class="cp-btn cp-btn-line" href="{{ route('admin.affiliation.commissions.export', array_filter(['statut' => $filter])) }}">{!! \App\Support\IconLibrary::svg('download', '', 18) !!} Exporter (CSV)</a>
    </div>

    @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="cp-flash cp-flash-err" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    <section class="cp-card">
        <div class="cp-list-hd">
            <h2>Liste</h2>
            <nav class="cp-pills" aria-label="Filtrer">
                @foreach($filters as $key => $label)
                <a class="cp-pill" href="{{ route('admin.affiliation.commissions', array_filter(['statut' => $key])) }}" @if((string) $filter === (string) $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
        @if($commissions->isEmpty())
            <div class="cp-empty"><strong>Aucune commission</strong><span>Elles apparaissent quand une boutique parrainée paie son abonnement.</span></div>
        @else
        <table class="cp-table">
            <thead><tr><th>Boutique parrainée</th><th>Affilié</th><th>Paiement</th><th>Commission</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            @foreach($commissions as $commission)
                <tr>
                    <td class="cp-cell-main">
                        <div class="cp-row-name">{{ $commission->referral?->shop?->name ?? '—' }}</div>
                        <div class="cp-row-sub">{{ $commission->created_at->format('d/m/Y') }} · disponible le {{ $commission->available_at->format('d/m/Y') }}</div>
                    </td>
                    <td data-label="Affilié">{{ $commission->affiliate?->displayName() ?? '—' }}</td>
                    <td data-label="Paiement" class="cp-num">{{ Money::gnf($commission->subscriptionPayment?->amount ?? 0) }}@if($commission->subscriptionPayment?->refunded_at) <span class="cp-row-sub" style="display:inline">· remboursé</span>@endif</td>
                    <td data-label="Commission" class="cp-num"><strong>{{ Money::gnf($commission->amount) }}</strong></td>
                    <td class="cp-cell-st"><span class="cp-st {{ $commission->status->tone() }}">{{ $commission->status->label() }}</span></td>
                    <td class="cp-cell-act">
                        @if(in_array($commission->status, [CommissionStatus::Pending, CommissionStatus::Approved], true))
                        <form method="POST" action="{{ route('admin.affiliation.commissions.refund', $commission) }}" onsubmit="return confirm('La boutique a été remboursée de ce paiement ? La commission sera annulée.')">@csrf
                            <button class="cp-btn cp-btn-danger cp-btn-sm" type="submit">Paiement remboursé</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($commissions->hasPages())<div class="cp-pagination">{{ $commissions->links() }}</div>@endif
        @endif
    </section>
</main>
@endsection
