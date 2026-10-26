<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;
use App\Models\AdCampaignPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Facture PDF d'une campagne payée. Réseaux sociaux : budget pub et frais Shopio sur deux lignes ;
 * mise en avant : une ligne. Un remboursement éventuel (Crédit Shopio) est indiqué sous le total.
 */
class CampaignInvoice
{
    public static function available(AdCampaign $campaign): bool
    {
        return $campaign->paid_at !== null;
    }

    /** « FC-2026-000042 » : année du paiement et numéro de la campagne. */
    public static function number(AdCampaign $campaign): string
    {
        return 'FC-' . $campaign->paid_at->format('Y') . '-' . str_pad((string) $campaign->id, 6, '0', STR_PAD_LEFT);
    }

    /** @return array<int, array{label: string, detail: ?string, amount: int}> */
    public static function lines(AdCampaign $campaign): array
    {
        $what = $campaign->isShopWide() ? 'Toute la boutique' : $campaign->items->map(fn ($i) => $i->product?->name)->filter()->join(', ');
        $period = 'Du ' . $campaign->starts_on->format('d/m/Y') . ' au ' . $campaign->ends_on->format('d/m/Y') . ' · ' . $campaign->duration_days . ' jours';

        if ($campaign->isSocial()) {
            return [
                ['label' => 'Budget publicitaire · ' . $campaign->package->name, 'detail' => 'Diffusion sur les réseaux sociaux · ' . $period, 'amount' => $campaign->ad_budget_gnf],
                ['label' => 'Frais Shopio', 'detail' => 'Création des visuels et gestion de la campagne', 'amount' => $campaign->fee_gnf],
            ];
        }
        return [
            ['label' => 'Mise en avant Shopio · ' . $campaign->package->name, 'detail' => $what . ' · ' . $period, 'amount' => $campaign->price_gnf],
        ];
    }

    public static function download(AdCampaign $campaign): Response
    {
        $campaign->loadMissing(['shop.user', 'package', 'items.product', 'payments']);
        $payment = $campaign->payments->whereIn('status', [AdCampaignPayment::STATUS_PAYE, AdCampaignPayment::STATUS_REMBOURSE])->sortByDesc('id')->first();

        return Pdf::loadView('boutique.campaigns.invoice', [
            'campaign' => $campaign,
            'number'   => self::number($campaign),
            'lines'    => self::lines($campaign),
            'payment'  => $payment,
            'refunded' => (int) $campaign->payments->sum('refund_amount_gnf'),
        ])->setPaper('a4')->download('Facture-Shopio-' . self::number($campaign) . '.pdf');
    }
}
