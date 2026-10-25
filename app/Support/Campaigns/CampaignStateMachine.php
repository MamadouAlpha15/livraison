<?php

namespace App\Support\Campaigns;

use App\Events\CampaignStatusChanged;
use App\Models\AdCampaign as C;
use App\Models\AdPackage;
use Illuminate\Support\Facades\DB;

/**
 * Seul endroit où une campagne change de statut.
 *
 * Mise en avant (boost) : a_payer → payee → programmee → en_cours ⇄ en_pause → terminee
 * Réseaux sociaux       : a_payer → payee → en_preparation → visuels_a_valider
 *                         (modification demandée : retour en_preparation, 2 fois max)
 *                         → programmee → en_cours ⇄ en_pause → terminee
 * Avant en_cours : refusee (équipe Shopio) ou annulee (vendeur, ou automatique).
 *
 * L'écriture est conditionnelle (« where status = ancien statut ») : si une autre requête
 * (tâche planifiée, webhook de paiement…) a changé le statut entre-temps, rien n'est écrasé.
 */
class CampaignStateMachine
{
    public const MAX_REVISIONS = 2;

    private const BOTH   = [AdPackage::TYPE_BOOST, AdPackage::TYPE_SOCIAL];
    private const BOOST  = [AdPackage::TYPE_BOOST];
    private const SOCIAL = [AdPackage::TYPE_SOCIAL];

    /** statut de départ => [statut d'arrivée => types de campagne autorisés] */
    private const TRANSITIONS = [
        C::STATUS_A_PAYER => [
            C::STATUS_PAYEE   => self::BOTH,
            C::STATUS_ANNULEE => self::BOTH,
        ],
        C::STATUS_PAYEE => [
            C::STATUS_PROGRAMMEE     => self::BOOST,
            C::STATUS_EN_PREPARATION => self::SOCIAL,
            C::STATUS_REFUSEE        => self::BOTH,
            C::STATUS_ANNULEE        => self::BOTH,
        ],
        C::STATUS_EN_PREPARATION => [
            C::STATUS_VISUELS_A_VALIDER => self::SOCIAL,
            C::STATUS_REFUSEE           => self::SOCIAL,
            C::STATUS_ANNULEE           => self::SOCIAL,
        ],
        C::STATUS_VISUELS_A_VALIDER => [
            C::STATUS_PROGRAMMEE     => self::SOCIAL,  // visuels validés
            C::STATUS_EN_PREPARATION => self::SOCIAL,  // modification demandée
            C::STATUS_REFUSEE        => self::SOCIAL,
            C::STATUS_ANNULEE        => self::SOCIAL,
        ],
        C::STATUS_PROGRAMMEE => [
            C::STATUS_EN_COURS => self::BOTH,
            C::STATUS_REFUSEE  => self::BOTH,
            C::STATUS_ANNULEE  => self::BOTH,
        ],
        C::STATUS_EN_COURS => [
            C::STATUS_EN_PAUSE => self::BOTH,
            C::STATUS_TERMINEE => self::BOTH,
        ],
        C::STATUS_EN_PAUSE => [
            C::STATUS_EN_COURS => self::BOTH,
            C::STATUS_TERMINEE => self::BOTH,
        ],
    ];

    /** Statuts accessibles depuis le statut actuel de la campagne. */
    public static function allowedTargets(C $campaign): array
    {
        return array_values(array_filter(
            array_keys(self::TRANSITIONS[$campaign->status] ?? []),
            fn (string $to) => self::can($campaign, $to)
        ));
    }

    public static function can(C $campaign, string $to): bool
    {
        return self::refusal($campaign, $to) === null;
    }

    /**
     * Change le statut et pose les dates qui vont avec. $attributes complète l'écriture
     * (motif de refus, raison de la pause, identifiant de la pub…).
     *
     * @throws InvalidCampaignTransition
     */
    public static function transition(C $campaign, string $to, array $attributes = []): C
    {
        if ($reason = self::refusal($campaign, $to)) {
            throw new InvalidCampaignTransition($reason);
        }

        $from    = $campaign->status;
        $changes = ['status' => $to] + self::sideEffects($campaign, $from, $to, $attributes) + $attributes;

        $before  = $campaign->getAttributes();
        $updated = C::whereKey($campaign->getKey())->where('status', $from)->update($changes + ['updated_at' => now()]);
        if ($updated === 0) {
            throw new InvalidCampaignTransition("La campagne a changé entre-temps : elle n'est plus « {$from} ».");
        }

        $campaign->refresh();
        // Notifications (App\Listeners\NotifyCampaignStatusChange) seulement si la transaction aboutit
        DB::afterCommit(fn () => event(new CampaignStatusChanged($campaign->fresh(), $from, $to, $before)));

        return $campaign;
    }

    /** Pourquoi la transition est interdite, ou null si elle est permise. */
    private static function refusal(C $campaign, string $to): ?string
    {
        $types = self::TRANSITIONS[$campaign->status][$to] ?? null;
        if ($types === null || !in_array($campaign->type, $types, true)) {
            return "Passage de « {$campaign->status} » à « {$to} » impossible pour une campagne {$campaign->type}.";
        }
        if ($campaign->status === C::STATUS_VISUELS_A_VALIDER && $to === C::STATUS_EN_PREPARATION
            && $campaign->revision_count >= self::MAX_REVISIONS) {
            return 'Les ' . self::MAX_REVISIONS . ' demandes de modification des visuels ont déjà été utilisées.';
        }
        return null;
    }

    private static function sideEffects(C $campaign, string $from, string $to, array $attributes): array
    {
        $now = now();

        return match ($to) {
            C::STATUS_PAYEE => ['paid_at' => $now],

            C::STATUS_EN_PREPARATION => $from === C::STATUS_VISUELS_A_VALIDER
                ? ['revision_count' => $campaign->revision_count + 1]
                : [],

            C::STATUS_VISUELS_A_VALIDER => ['creatives_sent_at' => $now, 'creatives_reminded_at' => null],

            // Reprise après une pause : les jours de pause sont ajoutés à la fin de la campagne
            C::STATUS_EN_COURS => $from === C::STATUS_EN_PAUSE
                ? [
                    'ends_on'      => $campaign->ends_on->copy()->addDays(self::pausedDays($campaign))->toDateString(),
                    'paused_at'    => null,
                    'pause_reason' => null,
                ]
                : ['launched_at' => $campaign->launched_at ?? $now],

            C::STATUS_EN_PAUSE => ['paused_at' => $now, 'pause_reason' => $attributes['pause_reason'] ?? 'admin'],
            C::STATUS_TERMINEE => ['finished_at' => $now],
            C::STATUS_ANNULEE  => ['cancelled_at' => $now],
            C::STATUS_REFUSEE  => ['refused_at' => $now],
            default            => [],
        };
    }

    /** Jours calendaires passés en pause (0 si la campagne reprend le jour même). */
    private static function pausedDays(C $campaign): int
    {
        if (!$campaign->paused_at) {
            return 0;
        }
        return (int) $campaign->paused_at->copy()->startOfDay()->diffInDays(now()->startOfDay());
    }
}
