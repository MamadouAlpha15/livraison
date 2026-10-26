<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Code de remise : le client le donne au livreur, qui ne peut marquer la commande
 * « livrée » qu'avec ce code. Les commandes créées avant la fonctionnalité n'en ont pas
 * et restent validables sans code.
 */
class DeliveryCode
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 600;

    /** Code partagé par les commandes créées dans la même requête (un panier multi-boutiques = un seul code). */
    private static ?string $requestCode = null;

    public static function forNewOrder(): string
    {
        return self::$requestCode ??= str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifie le code saisi par le livreur pour un ensemble de commandes remises ensemble.
     * Retourne un message d'erreur, ou null si la remise peut être validée.
     */
    public static function check(iterable $orders, ?string $input): ?string
    {
        $orders = collect($orders)->filter(fn (Order $o) => filled($o->delivery_code));
        if ($orders->isEmpty()) {
            return null;
        }

        $key = 'delivery-code:' . $orders->pluck('id')->sort()->implode('-');
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            return "Trop d'essais. Réessayez dans {$minutes} min, ou demandez à la boutique de valider la livraison.";
        }

        $input = preg_replace('/\D/', '', (string) $input);
        if ($input === '') {
            return 'Demandez au client son code de remise (4 chiffres, affiché dans son suivi de commande).';
        }

        $ok = $orders->every(fn (Order $o) => hash_equals($o->delivery_code, $input));
        if (! $ok) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            return 'Code de remise incorrect. Vérifiez-le avec le client.';
        }

        RateLimiter::clear($key);
        return null;
    }

    /** Le visiteur a-t-il le droit de voir le code de cette commande ? */
    public static function visibleTo(Order $order): bool
    {
        return filled($order->delivery_code) && OrderAccess::isOwner($order);
    }

    /** Mémorise en session une commande passée sans compte, pour lui montrer son code. */
    public static function rememberGuestOrder(Order $order): void
    {
        $ids = (array) session('guest_order_ids', []);
        $ids[] = $order->id;
        session(['guest_order_ids' => array_values(array_unique(array_slice($ids, -20)))]);
    }
}
