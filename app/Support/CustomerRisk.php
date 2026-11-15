<?php

namespace App\Support;

use App\Models\Order;

/**
 * Historique de livraison d'un numéro sur tout Shopio, pour le vendeur qui reçoit une commande payée à la
 * livraison. Seuls des totaux sont donnés (jamais les boutiques ni les produits) : livrées et annulées.
 *   - new     : aucune commande terminée avec ce numéro ;
 *   - good    : plus de livraisons que d'annulations ;
 *   - watch   : au moins une annulation, autant ou plus que de livraisons ;
 *   - risky   : au moins 2 annulations et plus d'annulations que de livraisons.
 */
class CustomerRisk
{
    public const LABELS = [
        'new'   => 'Nouveau client',
        'good'  => 'Client fiable',
        'watch' => 'À surveiller',
        'risky' => 'Client à risque',
    ];

    /** ['level', 'label', 'delivered', 'cancelled', 'summary'] — $except : la commande consultée elle-même */
    public static function forPhone(?string $phone, ?int $except = null): array
    {
        $key = PhoneKey::of($phone);
        $counts = $key
            ? Order::where('client_phone_key', $key)->when($except, fn ($q) => $q->whereKeyNot($except))
                ->whereIn('status', [Order::STATUS_LIVREE, Order::STATUS_ANNULEE])
                ->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status')
            : collect();
        $delivered = (int) ($counts[Order::STATUS_LIVREE] ?? 0);
        $cancelled = (int) ($counts[Order::STATUS_ANNULEE] ?? 0);

        $level = match (true) {
            $delivered + $cancelled === 0              => 'new',
            $cancelled >= 2 && $cancelled > $delivered => 'risky',
            $cancelled >= 1 && $cancelled >= $delivered => 'watch',
            default                                    => 'good',
        };

        return [
            'level'     => $level,
            'label'     => self::LABELS[$level],
            'delivered' => $delivered,
            'cancelled' => $cancelled,
            'summary'   => $level === 'new' ? 'Aucune commande terminée avec ce numéro sur Shopio'
                : $delivered . ' livrée' . ($delivered > 1 ? 's' : '') . ' · ' . $cancelled . ' annulée' . ($cancelled > 1 ? 's' : '') . ' sur Shopio',
        ];
    }
}
