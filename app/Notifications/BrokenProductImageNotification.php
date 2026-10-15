<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Alerte "photo cassée" envoyée par le SuperAdmin au propriétaire d'une
 * boutique — dans son compte Shopio uniquement (pas d'email, sur demande).
 */
class BrokenProductImageNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $noImage = empty($this->product->image);

        return [
            'type'         => 'broken_image',
            'title'        => $noImage ? 'Produit sans photo' : 'Photo produit non affichée',
            'body'         => $noImage
                ? "Le produit « {$this->product->name} » n'a pas de photo principale. Vos clients ne voient qu'un espace vide — ajoutez-en une pour améliorer vos ventes."
                : "Vous avez ajouté une photo pour « {$this->product->name} » mais elle ne s'affiche pas correctement pour vos clients. Vérifiez-la et réajoutez-la si besoin.",
            'product_id'   => $this->product->id,
            'product_name' => $this->product->name,
            'url'          => route('products.edit', $this->product),
        ];
    }
}
