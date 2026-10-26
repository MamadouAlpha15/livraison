{{--
    Contenu du tableau de bord de parrainage, commun au vendeur (/vendeur/parrainage) et à l'affilié
    externe (/affilie/tableau-de-bord). Données : AffiliateDashboardController@index.
    Variables : $affiliate, $stats, $settings, $referrals, $commissions, $payouts, $rewards, $qrSvg, $routes
--}}
@php
    use App\Enums\AffiliateStatus;
    use App\Enums\AffiliateType;
    use App\Enums\PayoutStatus;
    use App\Models\Affiliate;
    use App\Models\AffiliateReward;
    use App\Support\IconLibrary as Ico;
    use App\Support\Money;

    $isVendor = $affiliate->type === AffiliateType::Vendor;
    $link = $affiliate->link();
    $rate = rtrim(rtrim(number_format((float) $affiliate->commission_rate, 2, ',', ''), '0'), ',');
    $pendingPayout = $payouts->firstWhere('status', PayoutStatus::Requested);
    $canWithdraw = !$pendingPayout && $stats['available'] > 0 && $stats['available'] >= $settings->payout_threshold && $affiliate->isActive();
    $offersReward = $isVendor && $settings->reward_months > 0;
    $shareText = $isVendor
        ? "Je vends sur Shopio et ça marche bien. Crée ta boutique en ligne gratuitement avec mon lien : {$link}"
        : "Crée ta boutique en ligne sur Shopio avec mon lien : {$link}";
@endphp

@if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
@if($errors->any())
<div class="cp-flash cp-flash-err" role="alert">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif
@if($affiliate->status === AffiliateStatus::Suspended)
<div class="cp-flash cp-flash-err" role="alert">Ton compte de parrainage est suspendu. Ton lien ne fonctionne pas : contacte l'équipe Shopio.</div>
@endif

@if($affiliate->status === AffiliateStatus::Pending)
{{-- En attente de validation par l'équipe : ni lien ni QR code tant que le compte n'est pas validé --}}
<section class="cp-card af-wait" aria-labelledby="af-wait-title">
    <span class="af-wait-ico" aria-hidden="true">{!! Ico::svg('clock', '', 26) !!}</span>
    <div>
        <h2 id="af-wait-title" class="af-h2">Ta demande est en cours de vérification</h2>
        <p class="af-lead">L'équipe Shopio vérifie chaque nouvel affilié. Dès que ton compte est validé, tu reçois ton code et ton lien
            {{ $affiliate->contactPhone() ? 'par e-mail et par SMS au ' . $affiliate->contactPhone() : 'par e-mail' }}, et ils apparaissent ici.</p>
    </div>
</section>
@else
{{-- Lien, code et QR code --}}
<section class="cp-card af-share" aria-labelledby="af-share-title">
    <div class="af-share-main">
        <h2 id="af-share-title" class="af-h2">Ton lien de parrainage</h2>
        <p class="af-lead">
            Invite des vendeurs à ouvrir leur boutique sur Shopio. Tu gagnes <strong>{{ $rate }} %</strong> de chaque abonnement
            qu'ils paient pendant {{ $settings->commission_months }} mois{{ $offersReward ? ',' : '.' }}
            @if($offersReward) et <strong>{{ $settings->reward_months }} mois d'abonnement offert</strong> à leur premier paiement. @endif
        </p>

        <div class="af-code">
            <span class="af-code-lbl">Ton code</span>
            <strong class="af-code-val" id="afCode">{{ $affiliate->code }}</strong>
        </div>

        <label class="sr-only" for="afLink">Ton lien de parrainage</label>
        <div class="af-link">
            <input type="text" id="afLink" class="af-link-input" value="{{ $link }}" readonly>
            <button type="button" class="cp-btn cp-btn-blue" data-copy="#afLink">{!! Ico::svg('link', '', 18) !!} <span data-copy-label>Copier</span></button>
        </div>

        <div class="af-share-btns">
            <a class="cp-btn cp-btn-line" href="https://wa.me/?text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener">{!! Ico::svg('message', '', 18) !!} Partager sur WhatsApp</a>
            <a class="cp-btn cp-btn-line" href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($link) }}" target="_blank" rel="noopener">{!! Ico::svg('send', '', 18) !!} Facebook</a>
            <button type="button" class="cp-btn cp-btn-line" data-share data-share-text="{{ $shareText }}" data-share-url="{{ $link }}" hidden>{!! Ico::svg('send', '', 18) !!} Autre application</button>
        </div>
        <p class="af-note">Le lien reste valable {{ $settings->cookie_days }} jours sur le téléphone de la personne : si elle crée sa boutique plus tard, ton code est déjà rempli.</p>
    </div>

    @if($qrSvg)
    <div class="af-qr">
        <div class="af-qr-img" aria-label="QR code de ton lien de parrainage" role="img">{!! $qrSvg !!}</div>
        <a href="{{ route($routes['qr']) }}" class="cp-btn cp-btn-line cp-btn-sm">{!! Ico::svg('download', '', 16) !!} Télécharger le QR code</a>
        <span class="af-note">À imprimer sur tes sacs, cartes ou affiches.</span>
    </div>
    @endif
</section>

{{-- Chiffres --}}
<section class="cp-kpis af-kpis" aria-label="Résultats de ton parrainage">
    <div class="cp-card cp-kpi">
        <div class="cp-kpi-lbl">Clics sur ton lien</div>
        <div class="cp-kpi-val">{{ Money::gnf($stats['clicks'], false) }}</div>
    </div>
    <div class="cp-card cp-kpi">
        <div class="cp-kpi-lbl">Boutiques inscrites</div>
        <div class="cp-kpi-val">{{ $stats['registered'] }}</div>
    </div>
    <div class="cp-card cp-kpi">
        <div class="cp-kpi-lbl">Boutiques actives</div>
        <div class="cp-kpi-val">{{ $stats['active'] }}</div>
    </div>
    <div class="cp-card cp-kpi">
        <div class="cp-kpi-lbl">Boutiques abonnées</div>
        <div class="cp-kpi-val">{{ $stats['paying'] }}</div>
    </div>
</section>

{{-- Gains et retrait --}}
<section class="cp-card af-money" aria-labelledby="af-money-title">
    <div class="af-money-hd">
        <h2 id="af-money-title" class="af-h2">Tes gains</h2>
        <a href="{{ route($routes['export']) }}" class="cp-btn cp-btn-line cp-btn-sm">{!! Ico::svg('download', '', 16) !!} Exporter (CSV)</a>
    </div>
    <div class="af-money-grid">
        <div class="af-money-cell">
            <span class="af-money-lbl">En attente</span>
            <strong class="af-money-val cp-num">{{ Money::gnf($stats['pending']) }}</strong>
            <span class="af-note">Disponibles {{ $settings->validation_days }} jours après le paiement de la boutique.</span>
        </div>
        <div class="af-money-cell is-main">
            <span class="af-money-lbl">Disponible</span>
            <strong class="af-money-val cp-num">{{ Money::gnf($stats['available']) }}</strong>
            <span class="af-note">Retrait possible à partir de {{ Money::gnf($settings->payout_threshold) }}.</span>
        </div>
        <div class="af-money-cell">
            <span class="af-money-lbl">Déjà reçu</span>
            <strong class="af-money-val cp-num">{{ Money::gnf($stats['paid']) }}</strong>
            @if($stats['in_payout'] > 0)<span class="af-note">{{ Money::gnf($stats['in_payout']) }} en cours de versement.</span>@endif
        </div>
    </div>

    @if($pendingPayout)
        <div class="cp-flash cp-flash-info af-payout-msg">
            Retrait de {{ Money::gnf($pendingPayout->amount) }} demandé le {{ $pendingPayout->created_at->format('d/m/Y') }}
            sur {{ $pendingPayout->methodLabel() }} ({{ $pendingPayout->phone }}). L'équipe Shopio le traite.
        </div>
    @elseif($canWithdraw)
        <form method="POST" action="{{ route($routes['payout']) }}" class="af-payout" data-once>
            @csrf
            <div class="af-field">
                <label for="afMethod">Recevoir sur</label>
                <select id="afMethod" name="method" class="af-input" required>
                    @foreach(Affiliate::PAYOUT_METHODS as $value => $label)
                    <option value="{{ $value }}" @selected(old('method', $affiliate->payout_method) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="af-field">
                <label for="afPhone">Numéro</label>
                <input id="afPhone" name="phone" type="tel" inputmode="tel" class="af-input" required
                       placeholder="622 45 81 37" value="{{ old('phone', $affiliate->contactPhone()) }}">
            </div>
            <button type="submit" class="cp-btn cp-btn-blue af-payout-btn">{!! Ico::svg('wallet', '', 18) !!} Retirer {{ Money::gnf($stats['available']) }}</button>
        </form>
    @endif
</section>

{{-- Boutiques parrainées --}}
<section class="cp-card" aria-labelledby="af-ref-title">
    <div class="cp-list-hd"><h2 id="af-ref-title">Tes filleuls</h2></div>
    @if($referrals->isEmpty())
        <div class="cp-empty">
            <strong>Aucune boutique inscrite avec ton code pour l'instant</strong>
            <span>Partage ton lien sur WhatsApp à des vendeurs que tu connais : leur boutique apparaîtra ici.</span>
        </div>
    @else
        <table class="cp-table">
            <thead><tr><th>Boutique</th><th>Inscrite le</th><th>Statut</th></tr></thead>
            <tbody>
            @foreach($referrals as $referral)
                <tr>
                    <td class="cp-cell-main"><div class="cp-row-name">{{ $referral->shop?->name ?? 'Boutique supprimée' }}</div></td>
                    <td data-label="Inscrite le">{{ $referral->created_at->format('d/m/Y') }}</td>
                    <td class="cp-cell-st"><span class="cp-st {{ $referral->status->tone() }}">{{ $referral->status->label() }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($referrals->hasPages())<div class="cp-pagination">{{ $referrals->links() }}</div>@endif
    @endif
</section>

{{-- Commissions --}}
<section class="cp-card" aria-labelledby="af-com-title">
    <div class="cp-list-hd"><h2 id="af-com-title">Tes commissions</h2></div>
    @if($commissions->isEmpty())
        <div class="cp-empty">
            <strong>Pas encore de commission</strong>
            <span>Tu touches une commission chaque fois qu'une de tes boutiques parrainées paie son abonnement.</span>
        </div>
    @else
        <table class="cp-table">
            <thead><tr><th>Date</th><th>Boutique</th><th>Montant</th><th>Disponible le</th><th>Statut</th></tr></thead>
            <tbody>
            @foreach($commissions as $commission)
                <tr>
                    <td data-label="Date">{{ $commission->created_at->format('d/m/Y') }}</td>
                    <td class="cp-cell-main"><div class="cp-row-name">{{ $commission->referral?->shop?->name ?? '—' }}</div></td>
                    <td data-label="Montant" class="cp-num"><strong>{{ Money::gnf($commission->amount) }}</strong></td>
                    <td data-label="Disponible le">{{ $commission->available_at->format('d/m/Y') }}</td>
                    <td class="cp-cell-st"><span class="cp-st {{ $commission->status->tone() }}">{{ $commission->status->label() }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($commissions->hasPages())<div class="cp-pagination">{{ $commissions->links() }}</div>@endif
    @endif
</section>

@if($payouts->isNotEmpty() || $rewards->isNotEmpty())
<div class="af-history">
    @if($payouts->isNotEmpty())
    <section class="cp-card" aria-labelledby="af-pay-title">
        <div class="cp-list-hd"><h2 id="af-pay-title">Tes retraits</h2></div>
        <ul class="af-list">
            @foreach($payouts as $payout)
            <li>
                <div>
                    <strong class="cp-num">{{ Money::gnf($payout->amount) }}</strong>
                    <span class="af-note">{{ $payout->created_at->format('d/m/Y') }} · {{ $payout->methodLabel() }}@if($payout->reference) · réf. {{ $payout->reference }}@endif</span>
                    @if($payout->status === PayoutStatus::Rejected && $payout->note)<span class="af-note">{{ $payout->note }}</span>@endif
                </div>
                <span class="cp-st {{ $payout->status->tone() }}">{{ $payout->status->label() }}</span>
            </li>
            @endforeach
        </ul>
    </section>
    @endif

    @if($rewards->isNotEmpty())
    <section class="cp-card" aria-labelledby="af-rew-title">
        <div class="cp-list-hd"><h2 id="af-rew-title">Mois offerts</h2></div>
        <ul class="af-list">
            @foreach($rewards as $reward)
            <li>
                <div>
                    <strong>{{ $reward->months }} mois · {{ $reward->referral?->shop?->name ?? '—' }}</strong>
                    <span class="af-note">{{ $reward->created_at->format('d/m/Y') }} ·
                        {{ $reward->kind === AffiliateReward::KIND_EXTENSION ? 'abonnement prolongé' : Money::gnf($reward->amount_gnf) . ' ajoutés au Crédit Shopio' }}</span>
                </div>
            </li>
            @endforeach
        </ul>
        <p class="af-note af-cap">Jusqu'à {{ $settings->annual_reward_cap }} mois offerts par année.</p>
    </section>
    @endif
</div>
@endif

@endif

{{-- Fonctionnement : vraie séquence, d'où la numérotation --}}
<section class="cp-card af-how" aria-labelledby="af-how-title">
    <h2 id="af-how-title" class="af-h2">Comment ça marche</h2>
    <ol class="af-steps">
        <li><strong>Partage ton lien</strong><span>La personne ouvre ton lien ou scanne ton QR code, puis crée sa boutique. Ton code est rempli tout seul.</span></li>
        <li><strong>Sa boutique devient active</strong><span>Quand elle a {{ $settings->active_min_products }} produit(s) en ligne et {{ $settings->active_min_orders }} commande(s).</span></li>
        <li><strong>Elle paie son abonnement</strong><span>Tu gagnes {{ $rate }} % de chaque paiement pendant {{ $settings->commission_months }} mois{{ $offersReward ? ', et ton abonnement est prolongé de ' . $settings->reward_months . ' mois au premier paiement' : '' }}.</span></li>
        <li><strong>Tu retires ton argent</strong><span>Après {{ $settings->validation_days }} jours, la commission est disponible. Retrait par Mobile Money dès {{ Money::gnf($settings->payout_threshold) }}.</span></li>
    </ol>
    <p class="af-note">Utiliser son propre code, ou créer une boutique pour soi-même avec, n'est pas autorisé : ces inscriptions sont vérifiées et leurs commissions annulées.</p>
</section>

<script>
(() => {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-copy]');
        if (btn) {
            const input = document.querySelector(btn.dataset.copy);
            const label = btn.querySelector('[data-copy-label]');
            try { await navigator.clipboard.writeText(input.value); }
            catch { input.select(); document.execCommand('copy'); }
            if (label) { label.textContent = 'Copié'; setTimeout(() => { label.textContent = 'Copier'; }, 2000); }
            return;
        }
        const share = e.target.closest('[data-share]');
        if (share && navigator.share) {
            navigator.share({ text: share.dataset.shareText, url: share.dataset.shareUrl }).catch(() => {});
        }
    });
    if (navigator.share) document.querySelectorAll('[data-share]').forEach(b => { b.hidden = false; });
    document.querySelectorAll('form[data-once]').forEach(f => f.addEventListener('submit', () => {
        f.querySelectorAll('button[type=submit]').forEach(b => { b.disabled = true; });
    }));
})();
</script>
