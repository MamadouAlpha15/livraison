{{--
    Espace vendeur → Campagnes (maquette « Mes campagnes ») : chiffres du mois, présentation des deux types,
    liste filtrable des campagnes de la boutique.
    Données : Vendeur\AdCampaignController@index
--}}
@extends('layouts.app')

@section('title', 'Campagnes · ' . $shop->name)

@php
    $bodyClass = 'is-dashboard';
    use App\Models\AdCampaign;
    use App\Support\Campaigns\CampaignPricing as P;
    use App\Support\IconLibrary as Ico;

    $num = fn ($n) => number_format($n, 0, ',', "\u{00A0}");
    $fromPrice = fn ($type) => ($packages[$type] ?? collect())->map(fn ($p) => $p->priceFor(7))->filter()->min();
    $filters = ['toutes' => 'Toutes', 'en-cours' => 'En cours', 'a-valider' => 'À valider', 'terminees' => 'Terminées'];
@endphp

@push('styles')
@include('boutique.campaigns._styles')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'campaigns'])

    <main class="main vs-main">
        <div class="topbar cp-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="tb-info">
                <h1 class="cp-title">{!! Ico::svg('star', '', 22) !!} Campagnes</h1>
                <div class="tb-sub">Fais connaître ta boutique sur Shopio et sur les réseaux sociaux.</div>
            </div>
            <a href="{{ route('boutique.campaigns.create') }}" class="cp-btn cp-btn-blue">{!! Ico::svg('plus', '', 18) !!} Nouvelle campagne</a>
        </div>

        <div class="cp-content">
            @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="cp-flash cp-flash-err" role="alert">{{ session('error') }}</div>@endif

            @include('boutique.campaigns._news', ['news' => $news, 'readAllUrl' => route('boutique.campaigns.news.read-all')])

            {{-- Chiffres du mois --}}
            <section class="cp-kpis" aria-label="Chiffres de tes campagnes ce mois-ci">
                <div class="cp-card cp-kpi">
                    <div class="cp-kpi-lbl">Campagnes en cours</div>
                    <div class="cp-kpi-val">{{ $summary['running'] }}</div>
                </div>
                <div class="cp-card cp-kpi">
                    <div class="cp-kpi-lbl">Commandes générées ce mois</div>
                    <div class="cp-kpi-val">{{ $num($summary['orders']) }}</div>
                </div>
                <div class="cp-card cp-kpi">
                    <div class="cp-kpi-lbl">Chiffre d'affaires généré</div>
                    <div class="cp-kpi-val">{{ $num($summary['revenue']) }} <small>GNF</small></div>
                </div>
                <div class="cp-card cp-kpi">
                    <div class="cp-kpi-lbl">Dépensé ce mois</div>
                    <div class="cp-kpi-val">{{ $num($summary['spent']) }} <small>GNF</small></div>
                </div>
            </section>

            {{-- Les deux types de campagne --}}
            <section class="cp-types" aria-label="Types de campagne">
                <div class="cp-card cp-type">
                    <span class="cp-type-ico blue">{!! Ico::svg('star', '', 28) !!}</span>
                    <div style="min-width:0">
                        <div class="cp-type-name">Mise en avant Shopio</div>
                        <div class="cp-type-desc">Tes produits en « Sponsorisé » sur l'accueil, les catégories et la recherche. Démarre tout de suite.</div>
                        @if($p = $fromPrice('boost'))<div class="cp-type-price">Dès {{ P::format($p) }} / 7 jours</div>@endif
                    </div>
                </div>
                <div class="cp-card cp-type">
                    <span class="cp-type-ico amber">{!! Ico::svg('megaphone', '', 28) !!}</span>
                    <div style="min-width:0">
                        <div class="cp-type-name">Campagne réseaux sociaux</div>
                        <div class="cp-type-desc">L'équipe Shopio crée et diffuse ta pub sur Facebook, Instagram et TikTok.</div>
                        @if($p = $fromPrice('social'))<div class="cp-type-price">Dès {{ P::format($p) }} / 7 jours</div>@endif
                        <a href="{{ route('boutique.campaigns.create', ['type' => 'social']) }}" class="cp-link" style="display:inline-block;margin-top:8px">Lancer une campagne réseaux sociaux</a>
                    </div>
                </div>
            </section>

            @if($credit > 0)
            <div class="cp-flash cp-flash-info">Tu as {{ P::format($credit) }} de Crédit Shopio, utilisable pour payer tes campagnes.</div>
            @endif

            {{-- Liste --}}
            <section class="cp-card" aria-labelledby="titre-liste" style="overflow:hidden">
                <div class="cp-list-hd">
                    <h2 id="titre-liste">Mes campagnes</h2>
                    <nav class="cp-pills" aria-label="Filtrer les campagnes">
                        @foreach($filters as $key => $label)
                        <a href="{{ route('boutique.campaigns.index', $key === 'toutes' ? [] : ['filtre' => $key]) }}" class="cp-pill" @if($filter === $key) aria-current="page" @endif>{{ $label }}</a>
                        @endforeach
                    </nav>
                </div>

                @if($campaigns->isEmpty())
                <div class="cp-empty">
                    @if($filter === 'toutes')
                    <strong>Pas encore de campagne</strong>
                    <span>Mets un produit en avant sur Shopio pour toucher plus d'acheteurs.</span>
                    <a href="{{ route('boutique.campaigns.create') }}" class="cp-btn cp-btn-blue">{!! Ico::svg('plus', '', 18) !!} Nouvelle campagne</a>
                    @else
                    <strong>Aucune campagne ici</strong>
                    <a href="{{ route('boutique.campaigns.index') }}" class="cp-link">Voir toutes les campagnes</a>
                    @endif
                </div>
                @else
                <div style="overflow-x:auto">
                    <table class="cp-table">
                        <thead>
                            <tr>
                                <th>Campagne</th><th>Type</th><th>Dates</th><th>Statut</th><th>Résultats</th>
                                <th><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($campaigns as $c)
                        @php
                            $r = $results[$c->id];
                            $products = $c->items->pluck('product')->filter();
                            $started = in_array($c->status, [AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE, AdCampaign::STATUS_TERMINEE], true);
                            $todo = in_array($c->status, [AdCampaign::STATUS_A_PAYER, AdCampaign::STATUS_VISUELS_A_VALIDER], true);
                        @endphp
                        <tr class="{{ $todo ? 'is-todo' : '' }}">
                            <td class="cp-cell-main">
                                <div class="cp-row-name">{{ $c->title() }}</div>
                                <div class="cp-row-sub">{{ $products->isEmpty() ? 'Toute la boutique' : $products->count() . ' produit' . ($products->count() > 1 ? 's' : '') }}</div>
                            </td>
                            <td data-label="Type">{{ $c->isBoost() ? 'Mise en avant' : 'Réseaux sociaux' }}</td>
                            <td data-label="Dates" class="cp-num" style="white-space:nowrap">{{ $c->starts_on->translatedFormat('j M') }} → {{ $c->ends_on->translatedFormat('j M') }}</td>
                            <td class="cp-cell-st"><span class="cp-st {{ $c->statusTone() }}">{{ $c->statusLabel() }}</span></td>
                            <td data-label="Résultats">
                                @if(!$started)
                                    <span style="color:var(--text-2)">Pas encore lancée</span>
                                @elseif($c->isBoost())
                                    {{ $num($r['impressions']) }} affichage{{ $r['impressions'] > 1 ? 's' : '' }} · <strong>{{ $r['orders'] }} commande{{ $r['orders'] > 1 ? 's' : '' }}</strong>
                                @else
                                    {{ $num($r['impressions']) }} personnes touchées · <strong>{{ $r['messages'] }} message{{ $r['messages'] > 1 ? 's' : '' }}</strong>
                                @endif
                            </td>
                            <td class="cp-cell-act">
                                @if($c->status === AdCampaign::STATUS_A_PAYER)
                                    <a href="{{ route('boutique.campaigns.show', $c) }}" class="cp-btn cp-btn-blue cp-btn-sm">Payer</a>
                                @elseif($c->status === AdCampaign::STATUS_VISUELS_A_VALIDER)
                                    <a href="{{ route('boutique.campaigns.show', $c) }}#visuels" class="cp-btn cp-btn-blue cp-btn-sm">Valider les visuels</a>
                                @elseif($c->status === AdCampaign::STATUS_TERMINEE)
                                    <a href="{{ route('boutique.campaigns.create', ['depuis' => $c->id]) }}" class="cp-link">Relancer</a>
                                    · <a href="{{ route('boutique.campaigns.show', $c) }}" class="cp-link">Voir</a>
                                @else
                                    <a href="{{ route('boutique.campaigns.show', $c) }}" class="cp-link">Voir</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if($campaigns->hasPages())<div class="cp-pagination">{{ $campaigns->links() }}</div>@endif
                @endif
            </section>
        </div>
    </main>
</div>
@endsection

@push('styles')
@include('boutique._partials.sv-theme')
@endpush
