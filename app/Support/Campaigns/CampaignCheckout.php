<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;
use App\Models\AdCampaignCreative;
use App\Models\AdCampaignPayment;
use App\Models\AdPackage;
use App\Models\Shop;
use App\Models\ShopCreditEntry;
use App\Models\User;
use App\Support\Campaigns\Payment\CampaignPaymentGateway;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Commande, paiement et cycle de vie d'une campagne côté vendeur.
 * Les montants viennent toujours de la formule (CampaignPricing), jamais du formulaire.
 */
class CampaignCheckout
{
    public function __construct(private CampaignPaymentGateway $gateway) {}

    /**
     * Crée la campagne « à payer » après avoir vérifié les règles.
     *
     * @param int[] $productIds vide = toute la boutique
     * @throws CampaignNotAllowed
     */
    public function create(Shop $shop, AdPackage $package, array $productIds, CarbonInterface $startsOn, int $days, User $by, array $extra = []): AdCampaign
    {
        if ($errors = CampaignRules::validate($shop, $package, $productIds, $startsOn, $days)) {
            throw new CampaignNotAllowed($errors);
        }
        $quote = CampaignPricing::quote($package, $days);

        return DB::transaction(function () use ($shop, $package, $productIds, $startsOn, $days, $by, $extra, $quote) {
            $campaign = AdCampaign::create([
                'shop_id'       => $shop->id,
                'ad_package_id' => $package->id,
                'type'          => $package->type,
                'objective'     => $package->isSocial() ? ($extra['objective'] ?? null) : null,
                'targeting'     => $package->isSocial() ? ($extra['targeting'] ?? null) : null,
                'starts_on'     => $startsOn->toDateString(),
                'ends_on'       => CampaignPricing::endDate($startsOn, $days)->toDateString(),
                'duration_days' => $days,
                'status'        => AdCampaign::STATUS_A_PAYER,
                'price_gnf'     => $quote['price_gnf'],
                'ad_budget_gnf' => $quote['ad_budget_gnf'],
                'fee_gnf'       => $quote['fee_gnf'],
                'created_by'    => $by->id,
            ]);
            foreach (array_values(array_unique($productIds)) ?: [null] as $productId) {
                $campaign->items()->create(['product_id' => $productId]);
            }
            return $campaign;
        });
    }

    /**
     * Paie avec le Crédit Shopio : débit, paiement enregistré et campagne activée d'un seul coup.
     * @throws InsufficientCredit|CampaignNotAllowed
     */
    public function payWithCredit(AdCampaign $campaign, User $by): AdCampaign
    {
        $this->assertPayable($campaign);

        return DB::transaction(function () use ($campaign, $by) {
            ShopCredit::debit($campaign->shop, $campaign->price_gnf, ShopCreditEntry::REASON_PAIEMENT_CAMPAGNE, $campaign,
                'Campagne n°' . $campaign->id, $by->id);
            $campaign->payments()->create([
                'method'     => AdCampaignPayment::METHOD_CREDIT,
                'amount_gnf' => $campaign->price_gnf,
                'status'     => AdCampaignPayment::STATUS_PAYE,
                'paid_at'    => now(),
            ]);
            return $this->activate($campaign);
        });
    }

    /**
     * Ouvre un paiement Mobile Money et renvoie l'adresse de la page de paiement.
     * @throws CampaignNotAllowed
     */
    public function startMobileMoney(AdCampaign $campaign): string
    {
        $this->assertPayable($campaign);

        // Un seul paiement en cours à la fois : les tentatives précédentes sont abandonnées
        $campaign->payments()->where('status', AdCampaignPayment::STATUS_EN_ATTENTE)
            ->update(['status' => AdCampaignPayment::STATUS_ECHOUE]);

        $payment = $campaign->payments()->create([
            'method'     => AdCampaignPayment::METHOD_MOBILE_MONEY,
            'amount_gnf' => $campaign->price_gnf,
            'status'     => AdCampaignPayment::STATUS_EN_ATTENTE,
        ]);

        $result = $this->gateway->start(
            $payment,
            'Campagne Shopio n°' . $campaign->id . ' · ' . $campaign->package->name,
            route('boutique.campaigns.payment.return', $payment),
            route('payment.campaign.callback'),
            route('boutique.campaigns.payment.return', $payment),
        );

        if (!$result['success']) {
            $payment->update(['status' => AdCampaignPayment::STATUS_ECHOUE]);
            throw new CampaignNotAllowed([$result['message'] ?? 'Le paiement Mobile Money n\'a pas pu être ouvert. Réessaie dans un instant.']);
        }

        $payment->update(['gateway_operation_id' => $result['operation_id'] ?? null]);
        return $result['payment_url'];
    }

    /**
     * Paiement confirmé par l'agrégateur (webhook ou page de retour). Idempotent : un second appel ne fait rien.
     * Si la campagne n'attend plus de paiement (annulée entre-temps), la somme part sur le Crédit Shopio.
     */
    public function confirm(AdCampaignPayment $payment, ?string $operator = null, ?string $transactionRef = null): bool
    {
        return DB::transaction(function () use ($payment, $operator, $transactionRef) {
            $payment = AdCampaignPayment::whereKey($payment->id)->lockForUpdate()->first();
            if (!$payment || $payment->status === AdCampaignPayment::STATUS_PAYE) {
                return false;
            }

            $payment->update([
                'status'          => AdCampaignPayment::STATUS_PAYE,
                'operator'        => $operator ?? $payment->operator,
                'transaction_ref' => $transactionRef ?? $payment->transaction_ref,
                'paid_at'         => now(),
            ]);

            $campaign = $payment->campaign;
            if ($campaign->status === AdCampaign::STATUS_A_PAYER) {
                $this->activate($campaign);
            } else {
                ShopCredit::credit($campaign->shop, $payment->amount_gnf, ShopCreditEntry::REASON_REMBOURSEMENT, $campaign,
                    'Paiement reçu après l\'annulation de la campagne n°' . $campaign->id);
                $payment->update(['status' => AdCampaignPayment::STATUS_REMBOURSE, 'refund_amount_gnf' => $payment->amount_gnf, 'refunded_at' => now()]);
                Log::warning('[Campagnes] Paiement reçu pour une campagne qui n\'attendait plus de paiement', ['campaign' => $campaign->id, 'status' => $campaign->status]);
            }
            return true;
        });
    }

    public function fail(AdCampaignPayment $payment): void
    {
        AdCampaignPayment::whereKey($payment->id)->where('status', AdCampaignPayment::STATUS_EN_ATTENTE)
            ->update(['status' => AdCampaignPayment::STATUS_ECHOUE, 'updated_at' => now()]);
    }

    /** Statut réel d'un paiement en attente auprès de l'agrégateur (page de retour, quand le webhook tarde). */
    public function refresh(AdCampaignPayment $payment): AdCampaignPayment
    {
        if ($payment->status !== AdCampaignPayment::STATUS_EN_ATTENTE || $this->gateway->isFake()) {
            return $payment;
        }
        $check = $this->gateway->check($payment);
        match ($check['status']) {
            AdCampaignPayment::STATUS_PAYE   => $this->confirm($payment, $check['operator'], $check['transaction_ref']),
            AdCampaignPayment::STATUS_ECHOUE => $this->fail($payment),
            default                          => null,
        };
        return $payment->fresh();
    }

    /** Campagne payée : mise en avant programmée (lancée tout de suite si elle commence aujourd'hui), réseaux en préparation. */
    public function activate(AdCampaign $campaign): AdCampaign
    {
        $campaign = CampaignStateMachine::transition($campaign, AdCampaign::STATUS_PAYEE);

        if ($campaign->isSocial()) {
            return CampaignStateMachine::transition($campaign, AdCampaign::STATUS_EN_PREPARATION);
        }

        $campaign = CampaignStateMachine::transition($campaign, AdCampaign::STATUS_PROGRAMMEE);
        if ($campaign->starts_on->lte(today())) {
            $campaign = CampaignStateMachine::transition($campaign, AdCampaign::STATUS_EN_COURS);
        }
        return $campaign;
    }

    /**
     * Annulation par le vendeur avant le lancement, avec remboursement sur le Crédit Shopio.
     * Renvoie le montant remboursé.
     * @throws InvalidCampaignTransition
     */
    public function cancelByVendor(AdCampaign $campaign, User $by): int
    {
        return DB::transaction(function () use ($campaign, $by) {
            $refund = CampaignRefundPolicy::amount($campaign, CampaignRefundPolicy::CASE_ANNULATION_VENDEUR);
            CampaignStateMachine::transition($campaign, AdCampaign::STATUS_ANNULEE);
            $this->refund($campaign, $refund, 'Annulation de la campagne n°' . $campaign->id, $by->id);
            return $refund;
        });
    }

    /**
     * Rembourse $amount sur le Crédit Shopio (rien si 0) et abandonne les paiements Mobile Money encore ouverts.
     * À appeler dans la même transaction que le passage en « annulée » ou « refusée ».
     */
    public function refund(AdCampaign $campaign, int $amount, string $note, ?int $by = null): void
    {
        $campaign->payments()->where('status', AdCampaignPayment::STATUS_EN_ATTENTE)
            ->update(['status' => AdCampaignPayment::STATUS_ECHOUE]);

        if ($amount > 0) {
            ShopCredit::credit($campaign->shop, $amount, ShopCreditEntry::REASON_REMBOURSEMENT, $campaign, $note, $by);
            $campaign->payments()->where('status', AdCampaignPayment::STATUS_PAYE)->latest('id')->first()
                ?->update(['status' => AdCampaignPayment::STATUS_REMBOURSE, 'refund_amount_gnf' => $amount, 'refunded_at' => now()]);
        }
    }

    /**
     * Paiement reçu hors de Shopio (espèces, virement…) et enregistré par l'équipe : la campagne est activée.
     * @throws CampaignNotAllowed
     */
    public function markPaidManually(AdCampaign $campaign, User $by, ?string $reference = null): AdCampaign
    {
        $this->assertPayable($campaign);

        return DB::transaction(function () use ($campaign, $by, $reference) {
            $campaign->payments()->where('status', AdCampaignPayment::STATUS_EN_ATTENTE)
                ->update(['status' => AdCampaignPayment::STATUS_ECHOUE]);
            $campaign->payments()->create([
                'method'          => AdCampaignPayment::METHOD_MANUEL,
                'amount_gnf'      => $campaign->price_gnf,
                'status'          => AdCampaignPayment::STATUS_PAYE,
                'transaction_ref' => $reference,
                'paid_at'         => now(),
                'recorded_by'     => $by->id,
            ]);
            return $this->activate($campaign);
        });
    }

    /** Le vendeur valide les visuels proposés : la campagne réseaux sociaux est programmée. */
    public function approveCreatives(AdCampaign $campaign): AdCampaign
    {
        return DB::transaction(function () use ($campaign) {
            $campaign = CampaignStateMachine::transition($campaign, AdCampaign::STATUS_PROGRAMMEE);
            $campaign->creatives()->where('status', AdCampaignCreative::STATUS_EN_ATTENTE)
                ->update(['status' => AdCampaignCreative::STATUS_VALIDEE]);
            return $campaign;
        });
    }

    /**
     * Le vendeur demande une modification des visuels (2 fois au plus) : retour en préparation.
     * @throws InvalidCampaignTransition quand les modifications sont épuisées
     */
    public function requestCreativeChanges(AdCampaign $campaign, string $comment): AdCampaign
    {
        return DB::transaction(function () use ($campaign, $comment) {
            $campaign = CampaignStateMachine::transition($campaign, AdCampaign::STATUS_EN_PREPARATION);
            $campaign->creatives()->where('status', AdCampaignCreative::STATUS_EN_ATTENTE)
                ->update(['status' => AdCampaignCreative::STATUS_MODIFICATION_DEMANDEE, 'seller_comment' => $comment]);
            return $campaign;
        });
    }

    /**
     * Pause demandée par le vendeur ; les jours restants sont reportés à la reprise.
     * @throws CampaignNotAllowed pour une campagne réseaux sociaux (pausée par l'équipe Shopio)
     */
    public function pauseByVendor(AdCampaign $campaign): AdCampaign
    {
        if ($campaign->isSocial()) {
            throw new CampaignNotAllowed(['Pour mettre en pause une pub sur les réseaux sociaux, contacte l\'équipe Shopio.']);
        }
        return CampaignStateMachine::transition($campaign, AdCampaign::STATUS_EN_PAUSE, ['pause_reason' => 'vendeur']);
    }

    /**
     * Reprise d'une pause demandée par le vendeur. Une pause pour rupture reprend toute seule
     * quand le stock revient ; une pause de l'équipe Shopio ne se lève pas ici.
     * @throws CampaignNotAllowed|InvalidCampaignTransition
     */
    public function resumeByVendor(AdCampaign $campaign): AdCampaign
    {
        if ($campaign->pause_reason !== 'vendeur') {
            throw new CampaignNotAllowed([$campaign->pause_reason === 'rupture'
                ? 'Ta campagne reprendra toute seule quand le produit sera de nouveau en stock.'
                : 'Cette pause a été décidée par l\'équipe Shopio : contacte le support.']);
        }
        $unavailable = $campaign->products->reject(fn ($p) => CampaignRules::isPromotable($p));
        if ($unavailable->isNotEmpty()) {
            throw new CampaignNotAllowed(['« ' . $unavailable->first()->name . ' » est en rupture de stock ou désactivé : remets-le en vente avant de reprendre.']);
        }
        return CampaignStateMachine::transition($campaign, AdCampaign::STATUS_EN_COURS);
    }

    /** @throws CampaignNotAllowed */
    private function assertPayable(AdCampaign $campaign): void
    {
        if ($campaign->status !== AdCampaign::STATUS_A_PAYER) {
            throw new CampaignNotAllowed(['Cette campagne n\'attend plus de paiement.']);
        }
    }
}
