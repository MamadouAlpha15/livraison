<?php

namespace App\Support;

use App\Models\Driver;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Qui peut suivre une commande. Les numéros de commande se suivent : la page de suivi
 * ne peut donc pas être ouverte par simple numéro. Elle l'est par le client, le personnel
 * concerné, ou toute personne qui a reçu le lien signé (partage WhatsApp, lien copié).
 */
class OrderAccess
{
    /** Lien de suivi partageable, impossible à deviner à partir du numéro de commande. */
    public static function trackingUrl(Order $order, string $route = 'suivi.show'): string
    {
        return url(URL::signedRoute($route, $order, null, false));
    }

    /** Client qui a passé la commande (compte connecté, ou navigateur qui l'a passée sans compte). */
    public static function isOwner(Order $order): bool
    {
        if ($order->user_id) {
            return auth()->check() && (int) $order->user_id === (int) auth()->id();
        }
        return in_array((int) $order->id, array_map('intval', (array) session('guest_order_ids', [])), true);
    }

    /** Boutique, livreur assigné, société de livraison assignée ou super-admin. */
    public static function isStaff(?User $user, Order $order): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->role === 'superadmin') {
            return true;
        }
        if ($order->shop_id && ((int) $user->shop_id === (int) $order->shop_id || (int) optional($order->shop)->user_id === (int) $user->id)) {
            return true;
        }
        if (self::isAssignedCourier($user, $order, $order->driver_id ? Driver::forUser($user) : null)) {
            return true;
        }
        if ($order->delivery_company_id && $user->role === 'company') {
            $company = $user->deliveryCompany ?? $user->ownedCompany;
            return $company && (int) $company->id === (int) $order->delivery_company_id;
        }
        return false;
    }

    /**
     * Le compte livre-t-il cette commande ? Livreur de boutique (livreur_id) ou chauffeur
     * de société (driver_id). $driver : fiche chauffeur du compte, chargée une fois par l'appelant.
     */
    public static function isAssignedCourier(User $user, Order $order, ?Driver $driver): bool
    {
        return (int) $order->livreur_id === (int) $user->id
            || ($driver && (int) $order->driver_id === (int) $driver->id);
    }

    /**
     * Exige que le compte livre cette commande (sinon 403).
     * Retourne sa fiche chauffeur s'il livre pour une société, null s'il livre pour la boutique.
     */
    public static function authorizeCourier(User $user, Order $order, string $message = 'Accès interdit'): ?Driver
    {
        if ((int) $order->livreur_id === (int) $user->id) {
            return null;
        }
        $driver = Driver::forUser($user);
        abort_unless(self::isAssignedCourier($user, $order, $driver), 403, $message);
        return $driver;
    }

    /** Accès à la page de suivi publique et à ses données. */
    public static function canTrack(Order $order, Request $request): bool
    {
        return $request->hasValidRelativeSignature()
            || self::isOwner($order)
            || self::isStaff($request->user(), $order);
    }
}
