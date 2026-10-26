{{--
    Équipe Shopio → Affiliation → Retraits : demandes à verser par Mobile Money (référence obligatoire) ou à refuser.
    Données : Admin\AffiliationController@payouts
--}}
@extends('layouts.app')

@section('title', 'Retraits · Affiliation · Équipe Shopio')

@php
    $bodyClass = 'is-dashboard';
    use App\Enums\PayoutStatus;
    use App\Support\Money;
    $filters = collect(PayoutStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all() + ['tous' => 'Tous'];
@endphp

@push('styles')
@include('admin.affiliation._styles')
@endpush

@section('content')
@include('admin.affiliation._header', ['active' => 'payouts'])

<main class="ac-page">
    <div>
        <h1 class="ac-h1">Retraits</h1>
        <p class="ac-sub">Envoie l'argent sur le Mobile Money indiqué, puis saisis la référence de la transaction : l'affilié est prévenu par notification, e-mail et SMS.</p>
    </div>

    @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="cp-flash cp-flash-err" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    <section class="cp-card">
        <div class="cp-list-hd">
            <h2>Demandes</h2>
            <nav class="cp-pills" aria-label="Filtrer">
                @foreach($filters as $key => $label)
                <a class="cp-pill" href="{{ route('admin.affiliation.payouts', ['statut' => $key]) }}" @if($filter === $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
        @if($payouts->isEmpty())
            <div class="cp-empty"><strong>Aucune demande</strong><span>{{ $filter === 'requested' ? 'Aucun retrait en attente.' : 'Rien dans cette liste.' }}</span></div>
        @else
        <table class="cp-table">
            <thead><tr><th>Affilié</th><th>Montant</th><th>Mobile Money</th><th>Statut</th><th>Traitement</th></tr></thead>
            <tbody>
            @foreach($payouts as $payout)
                <tr class="{{ $payout->status === PayoutStatus::Requested ? 'is-todo' : '' }}">
                    <td class="cp-cell-main">
                        <div class="cp-row-name">{{ $payout->affiliate?->displayName() ?? '—' }}</div>
                        <div class="cp-row-sub">Demandé le {{ $payout->created_at->format('d/m/Y à H:i') }} · {{ $payout->commissions_count }} commission(s)</div>
                    </td>
                    <td data-label="Montant" class="cp-num"><strong>{{ Money::gnf($payout->amount) }}</strong></td>
                    <td data-label="Mobile Money">{{ $payout->methodLabel() }} · {{ $payout->phone }}</td>
                    <td class="cp-cell-st"><span class="cp-st {{ $payout->status->tone() }}">{{ $payout->status->label() }}</span></td>
                    <td class="cp-cell-act">
                        @if($payout->status === PayoutStatus::Requested)
                        <div class="af-actions">
                            <form method="POST" action="{{ route('admin.affiliation.payouts.pay', $payout) }}" class="af-inline">@csrf
                                <label class="sr-only" for="ref{{ $payout->id }}">Référence de la transaction</label>
                                <input id="ref{{ $payout->id }}" name="reference" class="ac-input" placeholder="Référence" required maxlength="100">
                                <button class="cp-btn cp-btn-blue cp-btn-sm" type="submit">Marquer payé</button>
                            </form>
                            <form method="POST" action="{{ route('admin.affiliation.payouts.reject', $payout) }}" class="af-inline">@csrf
                                <label class="sr-only" for="note{{ $payout->id }}">Motif du refus</label>
                                <input id="note{{ $payout->id }}" name="note" class="ac-input" placeholder="Motif du refus" required maxlength="255">
                                <button class="cp-btn cp-btn-danger cp-btn-sm" type="submit">Refuser</button>
                            </form>
                        </div>
                        @else
                        <span class="cp-row-sub">{{ $payout->processed_at?->format('d/m/Y') }} @if($payout->processor) · {{ $payout->processor->name }}@endif @if($payout->reference) · réf. {{ $payout->reference }}@endif @if($payout->note) · {{ $payout->note }}@endif</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($payouts->hasPages())<div class="cp-pagination">{{ $payouts->links() }}</div>@endif
        @endif
    </section>
</main>
@endsection
