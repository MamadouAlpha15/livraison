{{--
    Équipe Shopio → Campagnes : toutes les campagnes des boutiques, filtrables, avec ce qui attend une action.
    Données : Admin\AdCampaignController@index
--}}
@extends('layouts.app')

@section('title', 'Campagnes · Équipe Shopio')

@php
    $bodyClass = 'is-dashboard';
    use App\Models\AdCampaign;
    use App\Support\Campaigns\CampaignPricing as P;
    use App\Support\IconLibrary as Ico;
    use App\Http\Controllers\Admin\AdCampaignController as Ctl;

    $num = fn ($n) => number_format($n, 0, ',', "\u{00A0}");
    $alertLabels = [
        'preparer' => ['à préparer (visuels)', 'preparation'],
        'lancer'   => ['à mettre en ligne', 'programmees'],
        'cloturer' => ['à clôturer', 'en-cours'],
        'vendeur'  => ['visuels sans réponse du vendeur depuis 48 h', 'preparation'],
    ];
    $qs = fn (array $over) => array_filter(array_merge(['filtre' => $filter === 'toutes' ? null : $filter, 'type' => $type, 'q' => $search ?: null], $over));
@endphp

@push('styles')
@include('admin.campaigns._styles')
@endpush

@section('content')
@include('admin.campaigns._header', ['active' => 'campaigns'])

<main class="ac-page">
    <div>
        <h1 class="ac-h1">Campagnes</h1>
        <p class="ac-sub">Toutes les campagnes commandées par les boutiques.</p>
    </div>

    @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="cp-flash cp-flash-err" role="alert">{{ session('error') }}</div>@endif

    @include('boutique.campaigns._news', ['news' => $news, 'readAllUrl' => route('admin.campaigns.news.read-all')])

    <section class="cp-kpis" aria-label="Chiffres clés">
        <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Campagnes en cours</div><div class="cp-kpi-val">{{ $num($kpis['running']) }}</div></div>
        <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">À traiter</div><div class="cp-kpi-val" @if($kpis['todo']) style="color:var(--sv-amb-ink)" @endif>{{ $num($kpis['todo']) }}</div></div>
        <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Vendu ce mois</div><div class="cp-kpi-val">{{ $num($kpis['month']) }} <small>GNF</small></div></div>
    </section>

    @if($alerts)
    <div class="ac-alerts" role="status">
        @foreach($alerts as $key => $n)
        <a class="ac-alert" href="{{ route('admin.campaigns.index', ['filtre' => $alertLabels[$key][1]]) }}">
            {!! Ico::svg('alert', '', 16) !!} <strong>{{ $n }}</strong> {{ $alertLabels[$key][0] }}
        </a>
        @endforeach
    </div>
    @endif

    <section class="cp-card" aria-labelledby="titre-liste" style="overflow:hidden">
        <div class="cp-list-hd">
            <nav class="cp-pills" aria-label="Filtrer par statut">
                @foreach(Ctl::FILTERS as $key => [$label])
                <a href="{{ route('admin.campaigns.index', $qs(['filtre' => $key === 'toutes' ? null : $key])) }}" class="cp-pill" @if($filter === $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
            <form method="GET" action="{{ route('admin.campaigns.index') }}" class="ac-tools" role="search">
                @if($filter !== 'toutes')<input type="hidden" name="filtre" value="{{ $filter }}">@endif
                <label class="sr-only" for="fType">Type</label>
                <select name="type" id="fType" class="ac-select" onchange="this.form.submit()">
                    <option value="">Tous les types</option>
                    <option value="boost" @selected($type === 'boost')>Mise en avant</option>
                    <option value="social" @selected($type === 'social')>Réseaux sociaux</option>
                </select>
                <label class="sr-only" for="fQ">Boutique ou numéro</label>
                <input type="search" name="q" id="fQ" value="{{ $search }}" class="ac-input" placeholder="Boutique ou n° de campagne">
                <button class="cp-btn cp-btn-line cp-btn-sm" type="submit">Rechercher</button>
            </form>
        </div>
        <h2 id="titre-liste" class="sr-only">Liste des campagnes</h2>

        @if($campaigns->isEmpty())
        <div class="cp-empty">
            <strong>Aucune campagne ici</strong>
            @if($filter !== 'toutes' || $type || $search)<a href="{{ route('admin.campaigns.index') }}" class="cp-link">Voir toutes les campagnes</a>@endif
        </div>
        @else
        <div style="overflow-x:auto">
            <table class="cp-table">
                <thead>
                    <tr><th>Campagne</th><th>Boutique</th><th>Dates</th><th>Prix</th><th>Statut</th><th>Résultats</th><th><span class="sr-only">Actions</span></th></tr>
                </thead>
                <tbody>
                @foreach($campaigns as $c)
                @php
                    $r = $results[$c->id];
                    $started = in_array($c->status, [AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE, AdCampaign::STATUS_TERMINEE], true);
                    $todo = in_array($c->status, [AdCampaign::STATUS_PAYEE, AdCampaign::STATUS_EN_PREPARATION], true)
                        || ($c->status === AdCampaign::STATUS_PROGRAMMEE && $c->isSocial() && $c->starts_on->lte(today()))
                        || (in_array($c->status, [AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE], true) && $c->ends_on->lt(today()));
                @endphp
                <tr class="{{ $todo ? 'is-todo' : '' }}">
                    <td class="cp-cell-main">
                        <div class="cp-row-name">n°{{ $c->id }} · {{ $c->title() }}</div>
                        <div class="cp-row-sub">{{ $c->isBoost() ? 'Mise en avant' : 'Réseaux sociaux' }}</div>
                    </td>
                    <td data-label="Boutique">{{ $c->shop?->name ?? '—' }}</td>
                    <td data-label="Dates" class="cp-num" style="white-space:nowrap">{{ $c->starts_on->translatedFormat('j M') }} → {{ $c->ends_on->translatedFormat('j M') }}</td>
                    <td data-label="Prix" class="cp-num" style="white-space:nowrap">{{ P::format($c->price_gnf) }}</td>
                    <td class="cp-cell-st"><span class="cp-st {{ $c->statusTone() }}">{{ $c->statusLabel() }}</span></td>
                    <td data-label="Résultats">
                        @if($started)
                            {{ $num($r['impressions']) }} affichages · {{ $num($r['clicks']) }} visites · <strong>{{ $r['orders'] }} cmd</strong>
                        @else
                            <span style="color:var(--text-2)">—</span>
                        @endif
                    </td>
                    <td class="cp-cell-act"><a href="{{ route('admin.campaigns.show', $c) }}" class="{{ $todo ? 'cp-btn cp-btn-blue cp-btn-sm' : 'cp-link' }}">{{ $todo ? 'Traiter' : 'Voir' }}</a></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($campaigns->hasPages())<div class="cp-pagination">{{ $campaigns->links() }}</div>@endif
        @endif
    </section>
</main>
@endsection
