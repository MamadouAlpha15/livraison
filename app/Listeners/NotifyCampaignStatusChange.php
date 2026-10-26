<?php

namespace App\Listeners;

use App\Events\CampaignStatusChanged;
use App\Models\AdCampaign as C;
use App\Models\AdCampaignCreative;
use App\Support\Campaigns\CampaignNotifier;
use App\Support\Campaigns\CampaignRules;

/**
 * Quel changement de statut prévient qui :
 *  - payée                         → vendeur « paiement reçu » + équipe
 *  - visuels à valider             → vendeur « visuels prêts » (SMS)
 *  - modification demandée         → équipe
 *  - visuels validés               → équipe (pub à mettre en ligne)
 *  - en ligne (programmée → en cours) → vendeur
 *  - pause pour rupture / reprise  → vendeur
 *  - terminée                      → vendeur « bilan prêt »
 *  - refusée                       → vendeur (SMS)
 * L'annulation automatique (visuels non validés) est notifiée par CampaignScheduler.
 */
class NotifyCampaignStatusChange
{
    public function __construct(private CampaignNotifier $notifier) {}

    public function handle(CampaignStatusChanged $e): void
    {
        $c = $e->campaign;
        try {
            match (true) {
                $e->to === C::STATUS_PAYEE => $this->paid($c),
                $e->to === C::STATUS_VISUELS_A_VALIDER => $this->notifier->shop($c, 'visuels_prets'),
                $e->from === C::STATUS_VISUELS_A_VALIDER && $e->to === C::STATUS_EN_PREPARATION
                    => $this->notifier->team($c, 'equipe_modification_demandee', ['comment' => $this->lastComment($c)]),
                $e->from === C::STATUS_VISUELS_A_VALIDER && $e->to === C::STATUS_PROGRAMMEE => $this->notifier->team($c, 'equipe_visuels_valides'),
                // Mise en avant lancée au paiement : « Paiement reçu » dit déjà qu'elle est en ligne
                $e->from === C::STATUS_PROGRAMMEE && $e->to === C::STATUS_EN_COURS
                    => $c->paid_at?->gt(now()->subMinutes(5)) && $c->isBoost() ? null : $this->notifier->shop($c, 'en_ligne'),
                $e->from === C::STATUS_EN_PAUSE && $e->to === C::STATUS_EN_COURS && ($e->before['pause_reason'] ?? null) === 'rupture'
                    => $this->notifier->shop($c, 'reprise'),
                $e->to === C::STATUS_EN_PAUSE && $c->pause_reason === 'rupture'
                    => $this->notifier->shop($c, 'pause_rupture', ['product' => $this->unavailableProduct($c)]),
                $e->to === C::STATUS_TERMINEE => $this->notifier->shop($c, 'rapport_pret'),
                $e->to === C::STATUS_REFUSEE => $this->notifier->shop($c, 'refusee'),
                default => null,
            };
        } catch (\Throwable $ex) {
            report($ex); // une notification ratée ne doit pas casser le changement de statut
        }
    }

    private function paid(C $c): void
    {
        $this->notifier->shop($c, 'paiement_recu');
        $this->notifier->team($c, 'equipe_paiement_recu');
    }

    private function lastComment(C $c): ?string
    {
        return $c->creatives()->where('status', AdCampaignCreative::STATUS_MODIFICATION_DEMANDEE)
            ->whereNotNull('seller_comment')->latest('id')->value('seller_comment');
    }

    private function unavailableProduct(C $c): ?string
    {
        $name = $c->products()->with('variants')->get()->first(fn ($p) => !CampaignRules::isPromotable($p))?->name;
        return $name ? '« ' . $name . ' »' : null;
    }
}
