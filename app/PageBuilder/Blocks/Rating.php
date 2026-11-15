<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Checkbox;
use App\PageBuilder\Settings\Number;

/** Note moyenne des avis réels (commandes livrées). Rien n'est affiché sans avis. */
class Rating extends BlockType
{
    public function type(): string { return 'rating'; }

    public function name(): string { return 'Note des clients'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'star'; }

    public function settings(): array
    {
        return [
            new Number('min_reviews', 'Afficher à partir de', 1, 'Nombre d\'avis réels nécessaires. Sans avis, le bloc reste caché.', 1, 50),
            new Checkbox('show_count', 'Afficher le nombre d\'avis', true),
        ];
    }
}
