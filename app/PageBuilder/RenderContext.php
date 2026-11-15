<?php

namespace App\PageBuilder;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use App\PageBuilder\Commerce\Checkout;
use App\Services\ImageOptimizer;
use App\Support\ProductPage;
use Illuminate\Support\Collection;

/**
 * Ce que les vues des sections et des blocs savent du produit affiché : photos, prix, boutique.
 * $editing : rendu dans l'aperçu de l'éditeur (sections cliquables, liens et formulaires désactivés).
 */
class RenderContext
{
    private ?array $photos = null;

    private array $memo = [];

    /** Moteur d'offres de la section Produit en cours de rendu (posé par sa vue) */
    public ?Checkout $checkout = null;

    /** Rendu destiné au cache : le jeton du formulaire est remplacé à chaque affichage (Renderer::published) */
    public bool $csrfPlaceholder = false;

    public function __construct(public Shop $shop, public Product $product, public bool $editing = false) {}

    /** Photos du produit (principale puis galerie) : [['thumb' => url, 'medium' => url], …] — les photos produit n'ont pas de grande taille */
    public function photos(): array
    {
        return $this->photos ??= collect(array_merge([$this->product->image], $this->product->gallery_array))
            ->filter()->unique()->values()
            ->map(fn ($p) => [
                'thumb'  => ImageOptimizer::url($p, 'thumb'),
                'medium' => ImageOptimizer::url($p, 'medium'),
            ])->all();
    }

    /** URL d'une image envoyée depuis l'éditeur (réglage « image ») */
    public function media(?string $path, string $size = 'medium'): ?string
    {
        return $path ? ImageOptimizer::url(ImageOptimizer::variant($path, 'medium'), $size) : null;
    }

    public function price(): float
    {
        return (float) $this->product->current_price;
    }

    /** Ancien prix, seulement s'il est plus élevé que le prix actuel */
    public function compareAt(): ?float
    {
        $original = (float) ($this->product->original_price ?? 0);
        return $original > $this->price() ? $original : null;
    }

    public function savingPercent(): int
    {
        $compare = $this->compareAt();
        return $compare ? (int) round(100 - $this->price() * 100 / $compare) : 0;
    }

    /** Options en vente du produit */
    public function variants(): Collection
    {
        return $this->memo['variants'] ??= $this->product->activeVariants()->get();
    }

    /** Note moyenne et nombre des avis réels (laissés sur des commandes livrées de ce produit) */
    public function reviewStats(): array
    {
        return $this->memo['reviews'] ??= (function () {
            $q = Review::whereHas('order.items', fn ($q) => $q->where('product_id', $this->product->id));
            $count = (clone $q)->count();
            return ['count' => $count, 'avg' => $count ? round((float) $q->avg('rating'), 1) : null];
        })();
    }

    /** Derniers avis réels sur ce produit, avec le prénom du client */
    public function reviews(int $limit, bool $onlyCommented = true): Collection
    {
        return Review::with('client:id,name')
            ->whereHas('order.items', fn ($q) => $q->where('product_id', $this->product->id))
            ->when($onlyCommented, fn ($q) => $q->whereNotNull('comment')->where('comment', '!=', ''))
            ->latest()->limit($limit)->get();
    }

    /** Commandes livrées contenant ce produit */
    public function deliveredCount(): int
    {
        return $this->memo['delivered'] ??= Order::where('status', Order::STATUS_LIVREE)
            ->whereHas('items', fn ($q) => $q->where('product_id', $this->product->id))->count();
    }

    /** Stock réel restant (somme des options s'il y en a), null si le stock n'est pas suivi */
    public function stockLeft(): ?int
    {
        if ($this->variants()->isNotEmpty()) {
            return (int) $this->variants()->sum('stock');
        }
        return $this->product->stock === null ? null : (int) $this->product->stock;
    }

    public function unavailable(): bool
    {
        $p = $this->product;
        return !$p->is_active || $p->is_available === false || ($p->stock !== null && $p->stock <= 0 && $this->variants()->isEmpty());
    }

    /** Frais de livraison par commune indiqués par la boutique (commune => montant ; absente : à confirmer) */
    public function deliveryFees(): array
    {
        return array_filter(ProductPage::shop($this->shop)['delivery_fees'], fn ($v) => $v !== null && $v !== '');
    }

    public function user(): ?User
    {
        return auth()->user();
    }
}
