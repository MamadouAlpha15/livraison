<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\ProductList;
use App\PageBuilder\Settings\Range;
use App\PageBuilder\Settings\Text;

/** Produits complémentaires à cocher dans la commande, avec une remise facultative */
class Upsells extends BlockType
{
    public function type(): string { return 'upsells'; }

    public function name(): string { return 'Produits complémentaires'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'cart'; }

    public function settings(): array
    {
        return [
            new Text('heading', 'Titre', 'Complète ta commande', null, 80),
            new ProductList('products', 'Produits proposés', [], 'Seuls les produits en ligne et en stock sont affichés.', 3),
            new Range('discount', 'Remise sur ces produits', 0, null, 0, 50, 5, '%'),
        ];
    }
}
