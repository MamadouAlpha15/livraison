{{--
    Espace vendeur → Relances : chiffres, situations à activer et régler, historique des relances.
    Données : Boutique\FollowUpController@index
--}}
@extends('layouts.app')

@section('title', 'Relances · ' . $shop->name)

@php
    $bodyClass = 'is-dashboard';
    use App\Models\FollowUp;
    use App\Services\FollowUp\FollowUpCatalog as Cat;
    use App\Support\IconLibrary as Ico;
    use App\Support\Money;

    $duration = function (int $minutes) {
        if ($minutes === 0) return 'tout de suite';
        return $minutes % 1440 === 0 ? ($minutes / 1440) . ' j' : round($minutes / 60, 1) . ' h';
    };
@endphp

@push('styles')
@include('boutique._partials.sv-theme')
@include('boutique.followups._styles')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'followups'])

    <main class="main vs-main">
        <div class="topbar cp-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="tb-info">
                <h1 class="cp-title">{!! Ico::svg('bell', '', 22) !!} Relances</h1>
                <div class="tb-sub">Shopio relance tes clients à ta place, au bon moment, avec ton message.</div>
            </div>
        </div>

        <div class="cp-content">
            @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif

            <section class="cp-kpis" aria-label="Résultats des 30 derniers jours">
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Relances envoyées · 30 j</div><div class="cp-kpi-val">{{ $stats['sent'] }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Taux de réponse</div><div class="cp-kpi-val">{{ $stats['rate'] !== null ? $stats['rate'] . ' %' : '—' }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Commandes récupérées</div><div class="cp-kpi-val">{{ $stats['orders'] }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Montant récupéré</div><div class="cp-kpi-val cp-num">{{ Money::gnf($stats['amount'], false) }} <small>GNF</small></div></div>
            </section>

            <section aria-labelledby="fu-types">
                <h2 id="fu-types" class="fu-h2" style="margin-bottom:12px">Situations</h2>
                <div class="fu-list">
                    @foreach($rules as $type => ['meta' => $meta, 'rule' => $rule])
                    <div class="cp-card fu-item">
                        <div class="fu-item-main">
                            <div class="fu-item-name">{{ $meta['label'] }} @if($meta['audience'] === 'vendor')<span class="fu-tag-vendor">Rappel pour toi</span>@endif</div>
                            <div class="fu-help">{{ $meta['help'] }}</div>
                            <div class="fu-sum">
                                <span>Après {{ $duration($rule->delay_minutes) }}</span>
                                <span>{{ $rule->max_attempts }} relance{{ $rule->max_attempts > 1 ? 's' : '' }} max</span>
                                <span>{{ collect($rule->channels)->map(fn ($c) => Cat::CHANNELS[$c] ?? $c)->implode(' + ') }}</span>
                                <span>{{ $rule->send_from_hour }} h – {{ $rule->send_to_hour }} h</span>
                                @if($rule->template)<span>Message personnalisé</span>@endif
                            </div>
                        </div>
                        <div class="fu-item-act">
                            <a href="{{ route('boutique.followups.edit', $type) }}" class="cp-btn cp-btn-line cp-btn-sm">Régler</a>
                            <form method="POST" action="{{ route('boutique.followups.toggle', $type) }}">
                                @csrf
                                <button type="submit" class="fu-switch" role="switch" aria-checked="{{ $rule->enabled ? 'true' : 'false' }}"
                                        aria-label="{{ $rule->enabled ? 'Désactiver' : 'Activer' }} : {{ $meta['label'] }}"></button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            <section class="cp-card" aria-labelledby="fu-hist">
                <div class="cp-list-hd">
                    <h2 id="fu-hist">Historique</h2>
                    @if(request('client'))<a href="{{ route('boutique.followups.index') }}" class="cp-link">Voir tous les clients</a>@endif
                </div>
                @if($history->isEmpty())
                    <div class="cp-empty"><strong>Aucune relance envoyée pour l'instant</strong><span>Les relances partent toutes seules dès qu'une situation activée se présente.</span></div>
                @else
                <table class="cp-table">
                    <thead><tr><th>Client</th><th>Situation</th><th>Date</th><th>Résultat</th></tr></thead>
                    <tbody>
                    @foreach($history as $f)
                        @php $read = $f->status === FollowUp::STATUS_SENT ? $f->readAt() : null; @endphp
                        <tr>
                            <td class="cp-cell-main">
                                <a class="cp-row-name cp-link" href="{{ route('boutique.followups.index', ['client' => $f->user_id]) }}">{{ $f->user?->name ?? 'Client' }}</a>
                                <div class="cp-row-sub">Relance n° {{ $f->attempt }}</div>
                            </td>
                            <td data-label="Situation">{{ Cat::types()[$f->type]['label'] ?? $f->type }}@if($f->product)<span class="fu-st-sub">{{ $f->product->name }}</span>@endif</td>
                            <td data-label="Date">{{ ($f->sent_at ?? $f->updated_at)->format('d/m H:i') }}</td>
                            <td class="cp-cell-st">
                                @if($f->recovered_order_id)
                                    <span class="cp-st ok">Commande · {{ Money::gnf($f->recovered_amount) }}</span>
                                @elseif($f->responded_at)
                                    <span class="cp-st ok">A répondu</span>
                                @elseif($f->status === FollowUp::STATUS_STOPPED)
                                    <span class="cp-st muted">Arrêtée</span><span class="fu-st-sub">{{ FollowUp::STOP_REASONS[$f->stop_reason] ?? '' }}</span>
                                @elseif($read)
                                    <span class="cp-st info">Lue</span>
                                @else
                                    <span class="cp-st muted">Envoyée</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if($history->hasPages())<div class="cp-pagination">{{ $history->links() }}</div>@endif
                @endif
            </section>
        </div>
    </main>
</div>
@endsection
