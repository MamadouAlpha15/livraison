<?php

namespace App\Support;

use App\Models\Order;

/**
 * Commande en double : même boutique, même numéro, même produit, il y a peu de temps et pas annulée.
 * Le client voit un avertissement et peut confirmer qu'il s'agit bien d'une nouvelle commande.
 * Le message ne dévoile rien de la commande existante (numéro, nom, adresse) : seul le numéro saisi est connu.
 */
class DuplicateOrders
{
    public const MINUTES = 30;

    public static function recent(int $shopId, ?string $phone, int $productId): ?Order
    {
        $key = PhoneKey::of($phone);
        if (!$key) {
            return null;
        }
        return Order::where('shop_id', $shopId)->where('client_phone_key', $key)
            ->where('status', '!=', Order::STATUS_ANNULEE)
            ->where('created_at', '>=', now()->subMinutes(self::MINUTES))
            ->whereHas('items', fn ($q) => $q->where('product_id', $productId))
            ->latest()->first();
    }

    /** Réponse 409 pour le formulaire : le client peut confirmer avec confirm_duplicate=1 */
    public static function response(Order $existing, string $productName): array
    {
        $minutes = max(1, (int) $existing->created_at->diffInMinutes(now()));
        return [
            'duplicate' => true,
            'message'   => "Une commande de « {$productName} » a déjà été passée avec ce numéro il y a {$minutes} min. "
                . 'La boutique va appeler ce numéro pour la confirmer : inutile de commander à nouveau.',
        ];
    }
}
