<?php

namespace App\Policies;

use App\Models\PageTemplate;
use App\Models\Product;
use App\Models\User;

/** Un vendeur ne construit que les pages des produits de sa boutique, et n'utilise que ses modèles et ceux de Shopio. */
class ProductPagePolicy
{
    /** Modifier la page d'un produit (gate « edit-product-page ») */
    public function edit(User $user, Product $product): bool
    {
        return $user->shop_id !== null && (int) $product->shop_id === (int) $user->shop_id
            && in_array($user->role, ['admin', 'vendeur', 'employe'], true);
    }

    /** Appliquer un modèle (gate « use-page-template ») */
    public function useTemplate(User $user, PageTemplate $template): bool
    {
        return $template->is_active && ($template->shop_id === null || (int) $template->shop_id === (int) $user->shop_id);
    }

    /** Modifier ou supprimer un modèle (gate « manage-page-template ») : seulement ceux de sa boutique */
    public function manageTemplate(User $user, PageTemplate $template): bool
    {
        return $template->shop_id !== null && (int) $template->shop_id === (int) $user->shop_id;
    }
}
