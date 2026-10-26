<?php

namespace App\Exports;

use App\Models\Commission;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export CSV des commissions d'affiliation (séparateur « ; » et BOM UTF-8 pour Excel en français).
 * Montants en GNF entiers, sans séparateur de milliers pour rester calculables.
 */
class CommissionsCsv
{
    public static function download(Builder $query, string $filename, bool $withAffiliate = false): StreamedResponse
    {
        return response()->streamDownload(function () use ($query, $withAffiliate) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, array_merge(
                ['Date', 'Boutique parrainée'],
                $withAffiliate ? ['Affilié', 'Code'] : [],
                ['Paiement abonnement (GNF)', 'Commission (GNF)', 'Statut', 'Disponible le', 'Retrait n°']
            ), ';');

            $query->with(['referral.shop:id,name', 'subscriptionPayment:id,amount', 'affiliate.user:id,name,shop_id'])
                ->orderBy('id')
                ->chunk(500, function ($commissions) use ($out, $withAffiliate) {
                    foreach ($commissions as $c) {
                        /** @var Commission $c */
                        fputcsv($out, array_merge(
                            [$c->created_at->format('d/m/Y'), $c->referral?->shop?->name ?? '—'],
                            $withAffiliate ? [$c->affiliate?->displayName() ?? '—', $c->affiliate?->code ?? ''] : [],
                            [
                                (int) ($c->subscriptionPayment?->amount ?? 0),
                                $c->amount,
                                $c->status->label(),
                                $c->available_at->format('d/m/Y'),
                                $c->payout_id ?? '',
                            ]
                        ), ';');
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
