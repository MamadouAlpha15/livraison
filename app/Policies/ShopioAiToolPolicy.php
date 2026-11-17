<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;

/**
 * Permissions des outils de Shopio IA (gates « shopio-ai.* », déclarées dans AppServiceProvider).
 * L'IA agit au nom du client de la conversation : il ne voit que ses commandes, et seulement les produits
 * en ligne de la boutique avec laquelle il parle.
 */
class ShopioAiToolPolicy
{
    /** Produit en ligne de la boutique de la conversation */
    public function viewProduct(?User $client, Product $product, Shop $shop): bool
    {
        return (int) $product->shop_id === (int) $shop->id && $product->is_active;
    }

    /** Statut et suivi : uniquement les commandes du client connecté */
    public function viewOrder(?User $client, Order $order): bool
    {
        return $client !== null && (int) $order->user_id === (int) $client->id;
    }

    /** Proposer un prix : un client connecté, sur un produit en ligne de cette boutique */
    public function createOffer(?User $client, Product $product, Shop $shop): bool
    {
        return $client !== null && $client->role === 'client' && $this->viewProduct($client, $product, $shop);
    }
}
