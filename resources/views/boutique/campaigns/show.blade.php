{{--
    Suivi d'une campagne : statut, dates, progression, résultats, visites par jour, dernières commandes
    attribuées, actions (payer, mettre en pause, reprendre, prolonger, relancer, annuler).
    Données : Vendeur\AdCampaignController@show
--}}
@extends('layouts.app')

@section('title', 'Campagne n°' . $campaign->id . ' · ' . $shop->name)

@php
    $bodyClass = 'is-dashboard';
    use App\Models\AdCampaign;
    use App\Support\Campaigns\CampaignPricing as P;
    use App\Support\IconLibrary as Ico;

    $num = fn ($n) => number_format($n, 0, ',', "\u{00A0}");
    $products = $campaign->items->pluck('product')->filter();
    $paidPayment = $campaign->payments->firstWhere('status', 'paye') ?? $campaign->payments->firstWhere('status', 'rembourse');
    $methods = ['mobile_money' => 'Mobile Money', 'credit' => 'Crédit Shopio', 'manuel' => 'Paiement manuel'];
    $operators = ['orange_money' => 'Orange Money', 'mtn_momo' => 'MTN Mobile Money'];
    $maxDay = max(1, (int) $daily->max('clicks'));
    $started = $elapsed !== null;
    $canExtend = in_array($campaign->status, [AdCampaign::STATUS_PROGRAMMEE, AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE], true);
    $toValidate = $campaign->creatives->where('status', \App\Models\AdCampaignCreative::STATUS_EN_ATTENTE);
    $creativeStatus = ['en_attente' => ['À valider', 'warn'], 'validee' => ['Validé', 'ok'], 'modification_demandee' => ['Modification demandée', 'muted']];
@endphp

@push('styles')
@include('boutique.campaigns._styles')
<style>
.sh-head { padding: 24px; display: grid; gap: 18px; }
.sh-head-top { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-start; justify-content: space-between; }
.sh-name { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.01em; overflow-wrap: anywhere; }
.sh-meta { display: flex; flex-wrap: wrap; gap: 6px 16px; font-size: 14px; color: var(--text-2); margin-top: 6px; }
.sh-progress { display: grid; gap: 8px; }
.sh-progress-top { display: flex; justify-content: space-between; font-size: 13px; color: var(--text-2); }
.sh-progress-top strong { color: var(--text); }
.sh-track { height: 10px; border-radius: 999px; background: var(--border); overflow: hidden; }
.sh-track span { display: block; height: 100%; border-radius: 999px; background: var(--sv-btn); }
.sh-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.sh-actions form { margin: 0; }
.sh-note { padding: 14px 16px; border-radius: 14px; background: var(--bg); font-size: 14px; color: var(--text-2); line-height: 1.5; }
.sh-note strong { color: var(--text); }

.sh-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.sh-chart { padding: 20px 24px; display: grid; gap: 10px; }
.sh-chart h2, .sh-block h2 { margin: 0; font-size: 17px; font-weight: 700; }
.sh-bars { display: flex; align-items: flex-end; gap: 4px; height: 160px; padding-top: 8px; border-bottom: 1px solid var(--border); }
.sh-bar { flex: 1 1 0; min-width: 3px; border-radius: 4px 4px 0 0; background: var(--sv-blue); height: var(--h); min-height: 3px; }
.sh-bar.is-zero { background: var(--border); }
.sh-axis { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-2); font-variant-numeric: tabular-nums; }
.sh-block { padding: 20px 24px; display: grid; gap: 12px; }
.sh-orders { display: grid; }
.sh-order { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-top: 1px solid var(--border); font-size: 14px; }
.sh-order:first-child { border-top: 0; }
.sh-pay-choices { display: flex; flex-wrap: wrap; gap: 10px; }
.sh-creatives { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; }
.sh-creative { display: grid; gap: 6px; font-size: 13px; }
.sh-creative img, .sh-creative video { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 12px; background: var(--bg); display: block; }
.sh-caption { padding: 12px 14px; border-radius: 12px; background: var(--bg); font-size: 14px; line-height: 1.5; white-space: pre-line; overflow-wrap: anywhere; }
.sh-validate { display: grid; gap: 12px; padding-top: 4px; }
.sh-validate textarea { width: 100%; min-height: 90px; padding: 10px 14px; border-radius: 12px; border: 0; box-shadow: inset 0 0 0 1.5px var(--border); background: var(--surface); color: var(--text); font: 500 14px var(--font); resize: vertical; }
.sh-validate textarea:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue); }
.sh-validate details summary { cursor: pointer; font-weight: 700; color: var(--sv-btn); font-size: 14px; }
@media (max-width: 760px) {
    .sh-head, .sh-chart, .sh-block { padding: 18px; }
    .sh-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .sh-actions .cp-btn, .sh-actions form, .sh-pay-choices, .sh-pay-choices form, .sh-pay-choices .cp-btn { width: 100%; }
    .sh-actions .cp-btn { white-space: normal; text-align: center; line-height: 1.25; padding-block: 10px; }
}
</style>
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
                <a href="{{ route('boutique.campaigns.index') }}" class="cp-back">{!! Ico::svg('arrow-left', '', 15) !!} Campagnes</a>
                <div class="tb-sub">Campagne n°{{ $campaign->id }}</div>
            </div>
        </div>

        <div class="cp-content">
            @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
            @if(session('info'))<div class="cp-flash cp-flash-info" role="status">{{ session('info') }}</div>@endif
            @if(session('error'))<div class="cp-flash cp-flash-err" role="alert">{{ session('error') }}</div>@endif

            {{-- En-tête : nom, statut, dates, progression, actions --}}
            <section class="cp-card sh-head">
                <div class="sh-head-top">
                    <div style="min-width:0">
                        <h1 class="sh-name">{{ $campaign->title() }}</h1>
                        <div class="sh-meta">
                            <span>{{ $campaign->isBoost() ? 'Mise en avant Shopio' : 'Campagne réseaux sociaux' }}</span>
                            <span class="cp-num">Du {{ $campaign->starts_on->translatedFormat('j F Y') }} au {{ $campaign->ends_on->translatedFormat('j F Y') }}</span>
                            <span class="cp-num">{{ P::format($campaign->price_gnf) }}</span>
                        </div>
                    </div>
                    <span class="cp-st {{ $campaign->statusTone() }}" style="font-size:13px;padding:6px 12px">{{ $campaign->statusLabel() }}</span>
                </div>

                @if($started)
                <div class="sh-progress">
                    <div class="sh-progress-top"><span>Progression</span><strong class="cp-num">Jour {{ $elapsed }} sur {{ $campaign->duration_days }}</strong></div>
                    <div class="sh-track" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $campaign->duration_days }}" aria-valuenow="{{ $elapsed }}"><span style="width:{{ round($elapsed / max(1, $campaign->duration_days) * 100) }}%"></span></div>
                </div>
                @endif

                {{-- Explication de l'état actuel --}}
                @switch($campaign->status)
                    @case(AdCampaign::STATUS_A_PAYER)
                        <div class="sh-note"><strong>Il reste à payer cette campagne.</strong> Sans paiement sous 24 h, elle est supprimée et ses places sont libérées.</div>
                        @break
                    @case(AdCampaign::STATUS_PROGRAMMEE)
                        @if($campaign->isSocial())
                        <div class="sh-note"><strong>Visuels validés.</strong> L'équipe Shopio met ta pub en ligne à partir du {{ $campaign->starts_on->translatedFormat('j F') }}.</div>
                        @else
                        <div class="sh-note"><strong>Paiement reçu.</strong> Ta campagne démarrera le {{ $campaign->starts_on->translatedFormat('j F') }}.</div>
                        @endif
                        @break
                    @case(AdCampaign::STATUS_EN_PREPARATION)
                        <div class="sh-note"><strong>L'équipe Shopio prépare tes visuels.</strong> Tu les recevras ici pour les valider avant la mise en ligne.</div>
                        @break
                    @case(AdCampaign::STATUS_VISUELS_A_VALIDER)
                        <div class="sh-note"><strong>Tes visuels sont prêts.</strong> Valide-les ou demande une modification plus bas. Sans réponse sous 5 jours, la campagne est annulée et le budget pub remboursé.</div>
                        @break
                    @case(AdCampaign::STATUS_EN_PAUSE)
                        <div class="sh-note"><strong>Campagne en pause.</strong>
                            @if($campaign->pause_reason === 'rupture') Un produit promu est en rupture ou désactivé : elle reprendra toute seule quand il sera de nouveau en vente.
                            @elseif($campaign->pause_reason === 'vendeur') Tu l'as mise en pause. Les jours restants seront reportés quand tu la relanceras.
                            @else L'équipe Shopio l'a mise en pause : contacte le support pour en savoir plus.
                            @endif
                        </div>
                        @break
                    @case(AdCampaign::STATUS_REFUSEE)
                        <div class="sh-note"><strong>Campagne refusée par l'équipe Shopio.</strong> {{ $campaign->refusal_reason }} Tu as été remboursé sur ton Crédit Shopio.</div>
                        @break
                @endswitch

                <div class="sh-actions">
                    @if($campaign->status === AdCampaign::STATUS_A_PAYER)
                        <div class="sh-pay-choices">
                            <form method="POST" action="{{ route('boutique.campaigns.pay', $campaign) }}">
                                @csrf <input type="hidden" name="payment" value="mobile_money">
                                <button class="cp-btn cp-btn-blue">{!! Ico::svg('smartphone', '', 17) !!} Payer {{ P::format($campaign->price_gnf) }} par Mobile Money</button>
                            </form>
                            @if($credit >= $campaign->price_gnf)
                            <form method="POST" action="{{ route('boutique.campaigns.pay', $campaign) }}">
                                @csrf <input type="hidden" name="payment" value="credit">
                                <button class="cp-btn cp-btn-line">{!! Ico::svg('wallet', '', 17) !!} Payer avec mon Crédit Shopio</button>
                            </form>
                            @endif
                        </div>
                    @endif
                    @if($canPause)
                        <form method="POST" action="{{ route('boutique.campaigns.pause', $campaign) }}">@csrf <button class="cp-btn cp-btn-line">Mettre en pause</button></form>
                    @endif
                    @if($canResume)
                        <form method="POST" action="{{ route('boutique.campaigns.resume', $campaign) }}">@csrf <button class="cp-btn cp-btn-blue">Reprendre</button></form>
                    @endif
                    @if($canExtend)
                        <a href="{{ route('boutique.campaigns.create', ['depuis' => $campaign->id]) }}" class="cp-btn cp-btn-line">Prolonger</a>
                    @endif
                    @if(in_array($campaign->status, [AdCampaign::STATUS_TERMINEE, AdCampaign::STATUS_ANNULEE, AdCampaign::STATUS_REFUSEE], true))
                        <a href="{{ route('boutique.campaigns.create', ['depuis' => $campaign->id]) }}" class="cp-btn cp-btn-blue">Relancer cette campagne</a>
                    @endif
                    @if($campaign->paid_at)
                        <a href="{{ route('boutique.campaigns.invoice', $campaign) }}" class="cp-btn cp-btn-line">{!! Ico::svg('download', '', 17) !!} Facture PDF</a>
                    @endif
                    @if($canCancel)
                        <form method="POST" action="{{ route('boutique.campaigns.cancel', $campaign) }}"
                              onsubmit="return confirm('{{ $campaign->status === AdCampaign::STATUS_A_PAYER ? 'Annuler cette campagne ?' : 'Annuler cette campagne ? Le remboursement ira sur ton Crédit Shopio.' }}')">
                            @csrf <button class="cp-btn cp-btn-danger">Annuler la campagne</button>
                        </form>
                    @endif
                </div>
            </section>

            {{-- Visuels (réseaux sociaux) --}}
            @if($campaign->isSocial() && $campaign->creatives->isNotEmpty())
            <section class="cp-card sh-block" id="visuels" aria-labelledby="titre-visuels">
                <h2 id="titre-visuels">Visuels de ta pub</h2>
                @php $shown = $toValidate->isNotEmpty() ? $toValidate : $campaign->creatives->where('round', $campaign->creatives->max('round')); @endphp
                <div class="sh-creatives">
                    @foreach($shown as $cr)
                    <div class="sh-creative">
                        @if($cr->type === 'video')
                            <video src="{{ asset('storage/' . $cr->file_path) }}" controls preload="metadata"></video>
                        @else
                            <a href="{{ asset('storage/' . $cr->file_path) }}" target="_blank" rel="noopener"><img src="{{ asset('storage/' . $cr->file_path) }}" alt="Visuel {{ $loop->iteration }}" loading="lazy"></a>
                        @endif
                        <span class="cp-st {{ $creativeStatus[$cr->status][1] ?? 'muted' }}" style="justify-self:start">{{ $creativeStatus[$cr->status][0] ?? $cr->status }}</span>
                    </div>
                    @endforeach
                </div>
                @if($caption = $shown->firstWhere('caption')?->caption)
                    <div class="sh-caption"><strong style="display:block;margin-bottom:4px">Texte de la pub</strong>{{ $caption }}</div>
                @endif

                @if($campaign->status === AdCampaign::STATUS_VISUELS_A_VALIDER)
                <div class="sh-validate">
                    <form method="POST" action="{{ route('boutique.campaigns.creatives.approve', $campaign) }}">
                        @csrf <button class="cp-btn cp-btn-blue">{!! Ico::svg('check', '', 17) !!} Valider les visuels</button>
                    </form>
                    @if($revisionsLeft > 0)
                    <details @if($errors->has('comment')) open @endif>
                        <summary>Demander une modification ({{ $revisionsLeft }} restante{{ $revisionsLeft > 1 ? 's' : '' }})</summary>
                        <form method="POST" action="{{ route('boutique.campaigns.creatives.changes', $campaign) }}" style="display:grid;gap:10px;margin-top:10px">
                            @csrf
                            <label for="comment" class="sr-only">Ce que tu veux changer</label>
                            <textarea id="comment" name="comment" required minlength="5" maxlength="1000" placeholder="Ex. : mets le prix plus en grand, utilise la photo rouge…">{{ old('comment') }}</textarea>
                            @error('comment')<span style="color:var(--sv-err);font-size:13px;font-weight:600">{{ $message }}</span>@enderror
                            <div><button class="cp-btn cp-btn-line">Envoyer ma demande</button></div>
                        </form>
                    </details>
                    @else
                    <p style="margin:0;font-size:13px;color:var(--text-2)">Tu as utilisé tes {{ \App\Support\Campaigns\CampaignStateMachine::MAX_REVISIONS }} demandes de modification : valide les visuels pour lancer ta campagne.</p>
                    @endif
                </div>
                @endif
            </section>
            @endif

            {{-- Résultats --}}
            <section class="sh-stats" aria-label="Résultats de la campagne">
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Affichages</div><div class="cp-kpi-val">{{ $num($results['impressions']) }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Visites</div><div class="cp-kpi-val">{{ $num($results['clicks']) }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Messages</div><div class="cp-kpi-val">{{ $num($results['messages']) }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Commandes</div><div class="cp-kpi-val">{{ $num($results['orders']) }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">CA généré</div><div class="cp-kpi-val">{{ $num($results['revenue']) }} <small>GNF</small></div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Coût par commande</div><div class="cp-kpi-val">{!! $costPerOrder !== null ? $num($costPerOrder) . ' <small>GNF</small>' : '—' !!}</div></div>
            </section>

            {{-- Visites par jour --}}
            <section class="cp-card sh-chart" aria-labelledby="titre-visites">
                <h2 id="titre-visites">Visites par jour</h2>
                @if($daily->isEmpty())
                    <p style="margin:0;color:var(--text-2);font-size:14px">La courbe apparaîtra dès le premier jour de la campagne.</p>
                @else
                    <div class="sh-bars" role="img" aria-label="Visites par jour, maximum {{ $daily->max('clicks') }}">
                        @foreach($daily as $d)
                            <span class="sh-bar {{ $d['clicks'] === 0 ? 'is-zero' : '' }}" style="--h:{{ round($d['clicks'] / $maxDay * 100, 1) }}%" title="{{ $d['day']->translatedFormat('j M') }} : {{ $d['clicks'] }} visite{{ $d['clicks'] > 1 ? 's' : '' }}, {{ $d['orders'] }} commande{{ $d['orders'] > 1 ? 's' : '' }}"></span>
                        @endforeach
                    </div>
                    <div class="sh-axis"><span>{{ $daily->first()['day']->translatedFormat('j M') }}</span>@if($daily->count() > 1)<span>{{ $daily->last()['day']->translatedFormat('j M') }}</span>@endif</div>
                @endif
            </section>

            {{-- Dernières commandes attribuées --}}
            <section class="cp-card sh-block" aria-labelledby="titre-commandes">
                <h2 id="titre-commandes">Dernières commandes de la campagne</h2>
                @if($orders->isEmpty())
                    <p style="margin:0;color:var(--text-2);font-size:14px">Les commandes passées dans les 7 jours après un clic sur ta campagne apparaîtront ici.</p>
                @else
                    <div class="sh-orders">
                        @foreach($orders as $o)
                        <div class="sh-order">
                            <span><strong class="cp-num">#{{ str_pad($o->id, 5, '0', STR_PAD_LEFT) }}</strong> · {{ $o->display_name }} · {{ $o->created_at->translatedFormat('j M') }}</span>
                            <strong class="cp-num" style="white-space:nowrap">{{ P::format((int) round((float) $o->total)) }}</strong>
                        </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Détails --}}
            <section class="cp-card sh-block" aria-labelledby="titre-details">
                <h2 id="titre-details">Détails</h2>
                <dl class="wz-recap" style="display:grid;border-radius:14px;box-shadow:inset 0 0 0 1px var(--border);overflow:hidden;margin:0">
                    @php
                        $rows = [
                            'Formule'   => $campaign->package->name . ' · ' . $campaign->duration_days . ' jours',
                            'Promotion' => $products->isEmpty() ? 'Toute la boutique' : $products->pluck('name')->join(', '),
                        ];
                        if ($campaign->isSocial()) {
                            $rows['Objectif'] = $campaign->objectiveLabel() ?? '—';
                            $rows['Public'] = $campaign->targetingLabel() ?? '—';
                        }
                        if ($campaign->ad_budget_gnf) {
                            $rows['Budget pub'] = P::format($campaign->ad_budget_gnf);
                            $rows['Frais Shopio'] = P::format($campaign->fee_gnf);
                        }
                        $rows['Prix'] = P::format($campaign->price_gnf);
                        if ($paidPayment) {
                            $rows['Payée par'] = ($methods[$paidPayment->method] ?? $paidPayment->method)
                                . ($paidPayment->operator ? ' · ' . ($operators[$paidPayment->operator] ?? '') : '');
                            if ($paidPayment->refund_amount_gnf) $rows['Remboursé'] = P::format($paidPayment->refund_amount_gnf) . ' sur ton Crédit Shopio';
                        }
                    @endphp
                    @foreach($rows as $k => $v)
                    <div style="display:flex;justify-content:space-between;gap:16px;padding:12px 16px;border-top:1px solid var(--border);font-size:14px">
                        <dt style="color:var(--text-2);margin:0">{{ $k }}</dt><dd style="margin:0;font-weight:700;text-align:right;overflow-wrap:anywhere">{{ $v }}</dd>
                    </div>
                    @endforeach
                </dl>
            </section>
        </div>
    </main>
</div>
@endsection

@push('styles')
@include('boutique._partials.sv-theme')
@endpush
