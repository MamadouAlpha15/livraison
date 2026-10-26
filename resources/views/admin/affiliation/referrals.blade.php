{{--
    Équipe Shopio → Affiliation → Filleuls : boutiques parrainées, en priorité celles à vérifier
    (même IP ou même appareil que le parrain). Données : Admin\AffiliationController@referrals
--}}
@extends('layouts.app')

@section('title', 'Filleuls · Affiliation · Équipe Shopio')

@php
    $bodyClass = 'is-dashboard';
    use App\Enums\ReferralStatus;
    $filters = ['a-verifier' => 'À vérifier', 'tous' => 'Tous'] + collect(ReferralStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
@endphp

@push('styles')
@include('admin.affiliation._styles')
@endpush

@section('content')
@include('admin.affiliation._header', ['active' => 'referrals'])

<main class="ac-page">
    <div>
        <h1 class="ac-h1">Boutiques parrainées</h1>
        <p class="ac-sub">Une boutique « à vérifier » a la même IP ou le même appareil que son parrain : ses commissions restent en attente et le mois offert n'est pas donné tant qu'elle n'est pas vérifiée.</p>
    </div>

    @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif

    <section class="cp-card">
        <div class="cp-list-hd">
            <h2>Filleuls</h2>
            <nav class="cp-pills" aria-label="Filtrer">
                @foreach($filters as $key => $label)
                <a class="cp-pill" href="{{ route('admin.affiliation.referrals', ['filtre' => $key]) }}" @if($filter === $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
        @if($referrals->isEmpty())
            <div class="cp-empty"><strong>Rien à afficher</strong><span>{{ $filter === 'a-verifier' ? 'Aucun parrainage suspect pour l\'instant.' : 'Aucune boutique dans cette liste.' }}</span></div>
        @else
        <table class="cp-table">
            <thead><tr><th>Boutique</th><th>Parrain</th><th>Inscrite le</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach($referrals as $referral)
                <tr class="{{ $referral->flagged && $referral->status !== ReferralStatus::Cancelled ? 'is-todo' : '' }}">
                    <td class="cp-cell-main">
                        <div class="cp-row-name">{{ $referral->shop?->name ?? 'Boutique supprimée' }}</div>
                        <div class="cp-row-sub">{{ $referral->shop?->email }}@if($referral->shop?->phone) · {{ $referral->shop->phone }}@endif @if($referral->sourceLabel()) · connu par : {{ $referral->sourceLabel() }}@endif</div>
                        @if($referral->flagged)<span class="af-flag">À vérifier : {{ $referral->flag_reason }}</span>@endif
                    </td>
                    <td data-label="Parrain">{{ $referral->affiliate?->displayName() ?? '—' }} <span class="af-code">{{ $referral->affiliate?->code }}</span></td>
                    <td data-label="Inscrite le">{{ $referral->created_at->format('d/m/Y') }}</td>
                    <td class="cp-cell-st"><span class="cp-st {{ $referral->status->tone() }}">{{ $referral->status->label() }}</span></td>
                    <td class="cp-cell-act">
                        @if($referral->status !== ReferralStatus::Cancelled)
                        <div class="af-actions">
                            @if($referral->flagged)
                            <form method="POST" action="{{ route('admin.affiliation.referrals.clear', $referral) }}">@csrf
                                <button class="cp-btn cp-btn-blue cp-btn-sm" type="submit">C'est bon</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.affiliation.referrals.cancel', $referral) }}" onsubmit="return confirm('Annuler ce parrainage ? Ses commissions non versées seront annulées.')">@csrf
                                <button class="cp-btn cp-btn-danger cp-btn-sm" type="submit">Annuler le parrainage</button>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($referrals->hasPages())<div class="cp-pagination">{{ $referrals->links() }}</div>@endif
        @endif
    </section>
</main>
@endsection
