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
 * concerné, ou toute personne qui a reçu le lien de suivi (/suivi/{tracking_token} ; ou un
 * ancien lien signé /suivi/{id}?signature=…, toujours valable).
 */
class OrderAccess
{
    /** Jeton du lien de suivi : 20 lettres et chiffres au hasard (ni le numéro de commande ni la clé du serveur). */
    public static function newToken(): string
    {
        return \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(20));
    }

    /** Jeton de la commande, créé s'il manque (commande enregistrée sans passer par Eloquent). */
    public static function token(Order $order): string
    {
        if (empty($order->tracking_token)) {
            $order->forceFill(['tracking_token' => self::newToken()])->saveQuietly();
        }
        return $order->tracking_token;
    }

    /**
     * Lien de suivi partageable, court et impossible à deviner : /suivi/{tracking_token} (et ses données :
     * suivi.data, suivi.position, suivi.client-location). Les anciens liens signés /suivi/{id}?signature=…
     * restent valables (routes suivi.legacy.*).
     */
    public static function trackingUrl(Order $order, string $route = 'suivi.show'): string
    {
        return route($route, ['suivi' => self::token($order)]);
    }

    /** La page est ouverte avec le bon jeton de suivi (lien partagé). */
    public static function viaToken(Order $order, Request $request): bool
    {
        $given = (string) optional($request->route())->originalParameter('suivi');
        return $order->tracking_token !== null && $given !== '' && hash_equals((string) $order->tracking_token, $given);
    }

    /** Ancienne adresse par numéro (/suivi/123?signature=…) : à remplacer par le lien court. */
    public static function viaNumber(Request $request): bool
    {
        return ctype_digit((string) optional($request->route())->originalParameter('suivi'));
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
        return self::viaToken($order, $request)
            || $request->hasValidRelativeSignature()
            || self::isOwner($order)
            || self::isStaff($request->user(), $order);
    }
}
