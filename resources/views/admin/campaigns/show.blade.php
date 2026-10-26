{{--
    Équipe Shopio → fiche d'une campagne : ce qu'il y a à faire selon son statut (paiement manuel,
    visuels, mise en ligne, statistiques, pause, clôture, refus), puis détails, visuels, résultats, paiements.
    Données : Admin\AdCampaignController@show
--}}
@extends('layouts.app')

@section('title', 'Campagne n°' . $campaign->id . ' · Équipe Shopio')

@php
    $bodyClass = 'is-dashboard';
    use App\Models\AdCampaign;
    use App\Models\AdCampaignCreative;
    use App\Support\Campaigns\CampaignPricing as P;
    use App\Support\Campaigns\CampaignStateMachine;
    use App\Support\IconLibrary as Ico;

    $num = fn ($n) => number_format($n, 0, ',', "\u{00A0}");
    $products = $campaign->items->pluck('product')->filter();
    $methods = ['mobile_money' => 'Mobile Money', 'credit' => 'Crédit Shopio', 'manuel' => 'Paiement manuel'];
    $operators = ['orange_money' => 'Orange Money', 'mtn_momo' => 'MTN Mobile Money'];
    $payStatus = ['en_attente' => ['En attente', 'warn'], 'paye' => ['Payé', 'ok'], 'echoue' => ['Échoué', 'muted'], 'rembourse' => ['Remboursé', 'info']];
    $creativeStatus = [
        AdCampaignCreative::STATUS_EN_ATTENTE            => ['Pas encore validé', 'warn'],
        AdCampaignCreative::STATUS_VALIDEE               => ['Validé', 'ok'],
        AdCampaignCreative::STATUS_MODIFICATION_DEMANDEE => ['Modification demandée', 'err'],
    ];
    $rounds = $campaign->creatives->groupBy('round')->sortKeysDesc();
    $owner = $campaign->shop?->user;
@endphp

@push('styles')
@include('admin.campaigns._styles')
<style>
.as-grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: 22px; align-items: start; }
.as-col { display: grid; gap: 22px; min-width: 0; }
.as-card { padding: 22px 24px; display: grid; gap: 14px; }
.as-card h2 { margin: 0; font-size: 17px; font-weight: 700; }
.as-head { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; align-items: flex-start; }
.as-name { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.01em; overflow-wrap: anywhere; }
.as-meta { display: flex; flex-wrap: wrap; gap: 6px 16px; color: var(--text-2); font-size: 14px; margin-top: 6px; }
.as-todo { box-shadow: inset 0 0 0 2px var(--sv-blue); }
.as-note { padding: 12px 14px; border-radius: 12px; background: var(--bg); font-size: 14px; color: var(--text-2); line-height: 1.5; }
.as-note strong { color: var(--text); }
.as-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
.as-row form { margin: 0; }
.as-dl { margin: 0; display: grid; border-radius: 14px; box-shadow: inset 0 0 0 1px var(--border); overflow: hidden; }
.as-dl div { display: flex; justify-content: space-between; gap: 16px; padding: 11px 14px; border-top: 1px solid var(--border); font-size: 14px; }
.as-dl div:first-child { border-top: 0; }
.as-dl dt { color: var(--text-2); margin: 0; }
.as-dl dd { margin: 0; font-weight: 700; text-align: right; overflow-wrap: anywhere; }
.as-link { display: flex; gap: 8px; align-items: center; }
.as-link input { flex: 1; min-width: 0; }
.as-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
.as-creatives { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
.as-creative { display: grid; gap: 6px; font-size: 13px; }
.as-creative img, .as-creative video { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 12px; background: var(--bg); display: block; }
.as-creative form { margin: 0; }
.as-round { display: grid; gap: 10px; }
.as-round h3 { margin: 0; font-size: 14px; font-weight: 700; color: var(--text-2); }
.as-comment { padding: 10px 12px; border-radius: 10px; background: var(--sv-err50); color: var(--sv-err); font-size: 13px; font-weight: 600; }
.as-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.as-table th { text-align: left; font-size: 12px; color: var(--text-2); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; padding: 8px 0; }
.as-table td { padding: 10px 0; border-top: 1px solid var(--border); font-variant-numeric: tabular-nums; }
.as-table td:last-child, .as-table th:last-child { text-align: right; }
.as-danger summary { cursor: pointer; font-weight: 700; color: var(--sv-err); font-size: 14px; }
.as-danger[open] summary { margin-bottom: 10px; }
@media (max-width: 960px) { .as-grid { grid-template-columns: 1fr; } }
@media (max-width: 760px) {
    .as-card { padding: 18px; }
    .as-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .as-row > *, .as-row form, .as-row .cp-btn { width: 100%; }
}
</style>
@endpush

@section('content')
@include('admin.campaigns._header', ['active' => 'campaigns'])

<main class="ac-page">
    <a href="{{ route('admin.campaigns.index') }}" class="cp-back">{!! Ico::svg('arrow-left', '', 15) !!} Toutes les campagnes</a>

    @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="cp-flash cp-flash-err" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="cp-flash cp-flash-err" role="alert"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <section class="cp-card as-card">
        <div class="as-head">
            <div style="min-width:0">
                <h1 class="as-name">n°{{ $campaign->id }} · {{ $campaign->title() }}</h1>
                <div class="as-meta">
                    <span>{{ $campaign->shop?->name }}</span>
                    <span>{{ $campaign->isBoost() ? 'Mise en avant Shopio' : 'Réseaux sociaux' }}</span>
                    <span class="cp-num">Du {{ $campaign->starts_on->translatedFormat('j F Y') }} au {{ $campaign->ends_on->translatedFormat('j F Y') }}</span>
                    <span class="cp-num">{{ P::format($campaign->price_gnf) }}</span>
                </div>
            </div>
            <span class="cp-st {{ $campaign->statusTone() }}" style="font-size:13px;padding:6px 12px">{{ $campaign->statusLabel() }}</span>
        </div>
    </section>

    <div class="as-grid">
        <div class="as-col">
            {{-- ── À faire ── --}}
            <section class="cp-card as-card {{ $can['pay'] || $can['creatives'] || $can['launch'] ? 'as-todo' : '' }}" aria-labelledby="titre-afaire">
                <h2 id="titre-afaire">À faire</h2>

                @switch($campaign->status)
                    @case(AdCampaign::STATUS_A_PAYER)
                        <div class="as-note">La boutique n'a pas encore payé. Si elle a réglé hors de Shopio (espèces, virement), enregistre le paiement : la campagne est activée tout de suite.</div>
                        <form method="POST" action="{{ route('admin.campaigns.mark-paid', $campaign) }}" class="as-row"
                              onsubmit="return confirm('Enregistrer un paiement manuel de {{ P::format($campaign->price_gnf) }} ?')">
                            @csrf
                            <div class="ac-field" style="flex:1 1 220px">
                                <label for="ref">Référence du paiement (facultatif)</label>
                                <input id="ref" name="reference" class="ac-input" maxlength="100" placeholder="Reçu, n° de virement…">
                            </div>
                            <button class="cp-btn cp-btn-blue">Marquer payée · {{ P::format($campaign->price_gnf) }}</button>
                        </form>
                        @break

                    @case(AdCampaign::STATUS_PAYEE)
                    @case(AdCampaign::STATUS_EN_PREPARATION)
                        <div class="as-note">
                            <strong>Prépare les visuels</strong> ({{ $campaign->package->details['images'] ?? 0 }} image{{ ($campaign->package->details['images'] ?? 0) > 1 ? 's' : '' }}@if(($campaign->package->details['videos'] ?? 0) > 0) et {{ $campaign->package->details['videos'] }} vidéo{{ $campaign->package->details['videos'] > 1 ? 's' : '' }}@endif dans la formule {{ $campaign->package->name }}), puis envoie-les au vendeur.
                            @if($campaign->revision_count > 0) Le vendeur a demandé une modification ({{ $campaign->revision_count }} sur {{ CampaignStateMachine::MAX_REVISIONS }}) : son commentaire est sous « Visuels ».@endif
                        </div>
                        @if($can['creatives'])
                        <form method="POST" action="{{ route('admin.campaigns.creatives.store', $campaign) }}" enctype="multipart/form-data" class="as-card" style="padding:0;gap:12px">
                            @csrf
                            <div class="ac-field">
                                <label for="files">Visuels (images ou vidéos)</label>
                                <input id="files" type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime" class="ac-input" style="padding:8px 12px" required>
                                <span class="ac-help">JPG, PNG ou WebP jusqu'à {{ $limits['image'] / 1024 }} Mo ; MP4 ou MOV jusqu'à {{ $limits['video'] / 1024 }} Mo.</span>
                            </div>
                            <div class="ac-field">
                                <label for="caption">Texte de la pub (facultatif)</label>
                                <textarea id="caption" name="caption" class="ac-textarea" maxlength="2000" placeholder="Texte qui accompagnera les visuels">{{ old('caption') }}</textarea>
                            </div>
                            <div class="as-row"><button class="cp-btn cp-btn-line">{!! Ico::svg('image', '', 16) !!} Ajouter</button></div>
                        </form>
                        <form method="POST" action="{{ route('admin.campaigns.creatives.send', $campaign) }}" class="as-row">
                            @csrf
                            <button class="cp-btn cp-btn-blue" @disabled($pendingCount === 0)>{{ $pendingCount === 0 ? 'Envoyer au vendeur' : 'Envoyer ' . $pendingCount . ' visuel' . ($pendingCount > 1 ? 's' : '') . ' au vendeur' }}</button>
                        </form>
                        @endif
                        @break

                    @case(AdCampaign::STATUS_VISUELS_A_VALIDER)
                        <div class="as-note"><strong>En attente du vendeur</strong> depuis le {{ $campaign->creatives_sent_at?->translatedFormat('j F à H:i') }}. Il valide les visuels ou demande une modification ({{ $campaign->revision_count }} sur {{ CampaignStateMachine::MAX_REVISIONS }} utilisées).</div>
                        @break

                    @case(AdCampaign::STATUS_PROGRAMMEE)
                        @if($campaign->isSocial())
                        <div class="as-note">Visuels validés. Crée la pub sur Meta ou TikTok avec le lien de suivi ci-dessous, puis colle son identifiant pour la marquer en ligne.
                            @if($campaign->starts_on->lt(today())) La date prévue est passée : la campagne gardera ses {{ $campaign->duration_days }} jours à partir d'aujourd'hui.@endif</div>
                        <form method="POST" action="{{ route('admin.campaigns.launch', $campaign) }}" class="as-row">
                            @csrf
                            <div class="ac-field" style="flex:1 1 220px">
                                <label for="ext">Identifiant de la pub Meta / TikTok</label>
                                <input id="ext" name="external_ref" class="ac-input" maxlength="100" required value="{{ old('external_ref') }}">
                            </div>
                            <button class="cp-btn cp-btn-blue">Marquer en ligne</button>
                        </form>
                        @elseif($can['launch'])
                        <div class="as-note">La mise en avant devait commencer le {{ $campaign->starts_on->translatedFormat('j F') }}.</div>
                        <form method="POST" action="{{ route('admin.campaigns.launch', $campaign) }}">@csrf <button class="cp-btn cp-btn-blue">Démarrer maintenant</button></form>
                        @else
                        <div class="as-note">Démarre toute seule le {{ $campaign->starts_on->translatedFormat('j F Y') }}.</div>
                        @endif
                        @break

                    @case(AdCampaign::STATUS_EN_COURS)
                    @case(AdCampaign::STATUS_EN_PAUSE)
                        @if($campaign->status === AdCampaign::STATUS_EN_PAUSE)
                        <div class="as-note"><strong>En pause</strong> depuis le {{ $campaign->paused_at?->translatedFormat('j F') }} ·
                            {{ ['rupture' => 'produit en rupture', 'vendeur' => 'demandée par le vendeur', 'admin' => 'décidée par l\'équipe'][$campaign->pause_reason] ?? $campaign->pause_reason }}.</div>
                        @elseif($campaign->ends_on->lt(today()))
                        <div class="as-note"><strong>La date de fin est passée</strong> : clôture la campagne.</div>
                        @endif
                        <div class="as-row">
                            @if($can['pause'])<form method="POST" action="{{ route('admin.campaigns.pause', $campaign) }}">@csrf <button class="cp-btn cp-btn-line">Mettre en pause</button></form>@endif
                            @if($can['resume'])<form method="POST" action="{{ route('admin.campaigns.resume', $campaign) }}">@csrf <button class="cp-btn cp-btn-blue">Relancer</button></form>@endif
                            @if($can['close'])
                            <form method="POST" action="{{ route('admin.campaigns.close', $campaign) }}" onsubmit="return confirm('Clôturer cette campagne ?')">@csrf <button class="cp-btn {{ $campaign->ends_on->lt(today()) ? 'cp-btn-blue' : 'cp-btn-line' }}">Clôturer</button></form>
                            @endif
                        </div>
                        @break

                    @case(AdCampaign::STATUS_REFUSEE)
                        <div class="as-note"><strong>Refusée</strong> le {{ $campaign->refused_at?->translatedFormat('j F Y') }} : {{ $campaign->refusal_reason }}</div>
                        @break
                    @default
                        <div class="as-note">Rien à faire : la campagne est {{ mb_strtolower($campaign->statusLabel()) }}.</div>
                @endswitch

                @if($can['refuse'])
                <details class="as-danger">
                    <summary>Refuser la campagne</summary>
                    <form method="POST" action="{{ route('admin.campaigns.refuse', $campaign) }}" class="as-card" style="padding:0;gap:10px"
                          onsubmit="return confirm('Refuser la campagne ? La boutique sera remboursée sur son Crédit Shopio.')">
                        @csrf
                        <div class="ac-field">
                            <label for="reason">Motif (visible par le vendeur)</label>
                            <textarea id="reason" name="reason" class="ac-textarea" required minlength="5" maxlength="500" placeholder="Ex. : produit interdit à la publicité, photo trompeuse…">{{ old('reason') }}</textarea>
                        </div>
                        <div class="as-row"><button class="cp-btn cp-btn-danger">Refuser et rembourser</button></div>
                    </form>
                </details>
                @endif
            </section>

            {{-- ── Visuels ── --}}
            @if($campaign->isSocial())
            <section class="cp-card as-card" aria-labelledby="titre-visuels">
                <h2 id="titre-visuels">Visuels</h2>
                @forelse($rounds as $round => $items)
                <div class="as-round">
                    <h3>Proposition {{ $round }}</h3>
                    @if($comment = $items->firstWhere('seller_comment')?->seller_comment)
                        <div class="as-comment">Le vendeur : « {{ $comment }} »</div>
                    @endif
                    <div class="as-creatives">
                        @foreach($items as $cr)
                        <div class="as-creative">
                            @if($cr->type === 'video')
                                <video src="{{ asset('storage/' . $cr->file_path) }}" controls preload="metadata"></video>
                            @else
                                <a href="{{ asset('storage/' . $cr->file_path) }}" target="_blank" rel="noopener"><img src="{{ asset('storage/' . $cr->file_path) }}" alt="Visuel {{ $loop->iteration }}" loading="lazy"></a>
                            @endif
                            <span class="cp-st {{ $creativeStatus[$cr->status][1] ?? 'muted' }}" style="justify-self:start">{{ $creativeStatus[$cr->status][0] ?? $cr->status }}</span>
                            @if($cr->caption)<span style="color:var(--text-2);overflow-wrap:anywhere">{{ \Illuminate\Support\Str::limit($cr->caption, 90) }}</span>@endif
                            @if($can['creatives'] && $cr->status === AdCampaignCreative::STATUS_EN_ATTENTE)
                            <form method="POST" action="{{ route('admin.campaigns.creatives.destroy', [$campaign, $cr]) }}" onsubmit="return confirm('Retirer ce visuel ?')">
                                @csrf @method('DELETE') <button class="cp-btn cp-btn-danger cp-btn-sm">Retirer</button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <div class="as-note">Aucun visuel déposé pour l'instant.</div>
                @endforelse
            </section>
            @endif

            {{-- ── Statistiques (réseaux sociaux) ── --}}
            @if($campaign->isSocial())
            <section class="cp-card as-card" aria-labelledby="titre-stats">
                <h2 id="titre-stats">Statistiques Meta / TikTok</h2>
                @if($can['stats'])
                <form method="POST" action="{{ route('admin.campaigns.stats.store', $campaign) }}" class="as-row">
                    @csrf
                    <div class="ac-field" style="flex:1 1 180px">
                        <label for="sday">Jour</label>
                        <select id="sday" name="day" class="ac-select">@foreach($statsDays as $d => $label)<option value="{{ $d }}">{{ $label }}</option>@endforeach</select>
                    </div>
                    <div class="ac-field" style="flex:1 1 130px">
                        <label for="simp">Affichages</label>
                        <input id="simp" name="impressions" type="number" min="0" step="1" inputmode="numeric" class="ac-input" required>
                    </div>
                    <div class="ac-field" style="flex:1 1 150px">
                        <label for="sspend">Dépense (GNF)</label>
                        <input id="sspend" name="spend_gnf" type="number" min="0" step="1" inputmode="numeric" class="ac-input" required>
                    </div>
                    <button class="cp-btn cp-btn-blue">Enregistrer</button>
                </form>
                <span class="ac-help">Une saisie par jour : enregistrer à nouveau le même jour remplace les chiffres. Les visites, messages et commandes sont comptés tout seuls grâce au lien de suivi.</span>
                @else
                <div class="as-note">La saisie s'ouvre quand la campagne est en ligne.</div>
                @endif

                @if($campaign->stats->isNotEmpty())
                <div style="overflow-x:auto">
                    <table class="as-table">
                        <thead><tr><th>Jour</th><th>Affichages</th><th>Dépense</th></tr></thead>
                        <tbody>
                        @foreach($campaign->stats as $s)
                            <tr><td>{{ \Illuminate\Support\Carbon::parse($s->day)->translatedFormat('j M Y') }}</td><td>{{ $num($s->impressions) }}</td><td>{{ P::format((int) $s->spend_gnf) }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </section>
            @endif

            {{-- ── Résultats ── --}}
            <section class="as-stats" aria-label="Résultats">
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Affichages</div><div class="cp-kpi-val">{{ $num($results['impressions']) }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Visites</div><div class="cp-kpi-val">{{ $num($results['clicks']) }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Messages</div><div class="cp-kpi-val">{{ $num($results['messages']) }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Commandes</div><div class="cp-kpi-val">{{ $num($results['orders']) }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">CA généré</div><div class="cp-kpi-val">{{ $num($results['revenue']) }} <small>GNF</small></div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Coût par commande</div><div class="cp-kpi-val">{!! $costPerOrder !== null ? $num($costPerOrder) . ' <small>GNF</small>' : '—' !!}</div></div>
            </section>
        </div>

        <div class="as-col">
            <section class="cp-card as-card" aria-labelledby="titre-details">
                <h2 id="titre-details">Détails</h2>
                <dl class="as-dl">
                    <div><dt>Boutique</dt><dd>{{ $campaign->shop?->name }}</dd></div>
                    @if($owner)<div><dt>Propriétaire</dt><dd>{{ $owner->name }}@if($owner->phone)<br><a class="cp-link" href="tel:{{ $owner->phone }}">{{ $owner->phone }}</a>@endif</dd></div>@endif
                    <div><dt>Formule</dt><dd>{{ $campaign->package->name }} · {{ $campaign->duration_days }} jours</dd></div>
                    <div><dt>Promotion</dt><dd>{{ $products->isEmpty() ? 'Toute la boutique' : $products->pluck('name')->join(', ') }}</dd></div>
                    @if($campaign->isSocial())
                    <div><dt>Objectif</dt><dd>{{ $campaign->objectiveLabel() ?? '—' }}</dd></div>
                    <div><dt>Public</dt><dd>{{ $campaign->targetingLabel() ?? '—' }}</dd></div>
                    <div><dt>Réseaux</dt><dd>{{ collect($campaign->package->details['networks'] ?? [])->map(fn ($n) => ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok'][$n] ?? $n)->join(', ') }}</dd></div>
                    <div><dt>Budget pub</dt><dd>{{ P::format($campaign->ad_budget_gnf) }}</dd></div>
                    <div><dt>Frais Shopio</dt><dd>{{ P::format($campaign->fee_gnf) }}</dd></div>
                    @if($campaign->external_ref)<div><dt>ID de la pub</dt><dd>{{ $campaign->external_ref }}</dd></div>@endif
                    @endif
                    <div><dt>Prix</dt><dd>{{ P::format($campaign->price_gnf) }}</dd></div>
                    <div><dt>Commandée le</dt><dd>{{ $campaign->created_at->translatedFormat('j F Y à H:i') }}</dd></div>
                </dl>
            </section>

            @if($trackingUrl)
            <section class="cp-card as-card" aria-labelledby="titre-lien">
                <h2 id="titre-lien">Lien de suivi</h2>
                <p class="ac-help" style="margin:0">À mettre dans la pub : les visites, messages et commandes qui en viennent sont attribués à la campagne.</p>
                <div class="as-link">
                    <label class="sr-only" for="trk">Lien de suivi</label>
                    <input id="trk" class="ac-input" value="{{ $trackingUrl }}" readonly onclick="this.select()">
                    <button type="button" class="cp-btn cp-btn-line cp-btn-sm" onclick="navigator.clipboard?.writeText(document.getElementById('trk').value).then(() => { this.textContent = 'Copié'; })">Copier</button>
                </div>
            </section>
            @endif

            <section class="cp-card as-card" aria-labelledby="titre-paiements">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px">
                    <h2 id="titre-paiements">Paiements</h2>
                    @if($campaign->paid_at)<a href="{{ route('admin.campaigns.invoice', $campaign) }}" class="cp-link" style="font-size:14px">Facture PDF</a>@endif
                </div>
                @forelse($campaign->payments as $pay)
                <div style="display:flex;justify-content:space-between;gap:12px;font-size:14px;align-items:center">
                    <span>
                        <strong>{{ $methods[$pay->method] ?? $pay->method }}</strong>@if($pay->operator) · {{ $operators[$pay->operator] ?? $pay->operator }}@endif
                        <span style="display:block;color:var(--text-2);font-size:12.5px">{{ ($pay->paid_at ?? $pay->created_at)->translatedFormat('j M Y H:i') }}@if($pay->transaction_ref) · {{ $pay->transaction_ref }}@endif</span>
                        @if($pay->refund_amount_gnf)<span style="display:block;color:var(--text-2);font-size:12.5px">{{ P::format($pay->refund_amount_gnf) }} remboursés sur le Crédit Shopio</span>@endif
                    </span>
                    <span style="text-align:right"><strong class="cp-num">{{ P::format($pay->amount_gnf) }}</strong><br><span class="cp-st {{ $payStatus[$pay->status][1] ?? 'muted' }}">{{ $payStatus[$pay->status][0] ?? $pay->status }}</span></span>
                </div>
                @empty
                <div class="as-note">Aucun paiement.</div>
                @endforelse
            </section>

            <section class="cp-card as-card" aria-labelledby="titre-cmd">
                <h2 id="titre-cmd">Commandes attribuées</h2>
                @forelse($orders as $o)
                <div style="display:flex;justify-content:space-between;gap:12px;font-size:14px">
                    <span><strong class="cp-num">#{{ str_pad($o->id, 5, '0', STR_PAD_LEFT) }}</strong> · {{ $o->created_at->translatedFormat('j M') }} · {{ $o->status }}</span>
                    <strong class="cp-num" style="white-space:nowrap">{{ P::format((int) round((float) $o->total)) }}</strong>
                </div>
                @empty
                <div class="as-note">Aucune commande attribuée pour l'instant.</div>
                @endforelse
            </section>
        </div>
    </div>
</main>
@endsection
