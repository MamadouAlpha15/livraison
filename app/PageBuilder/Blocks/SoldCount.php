<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Number;
use App\PageBuilder\Settings\Text;

/** Nombre réel de commandes livrées de ce produit, affiché à partir d'un minimum */
class SoldCount extends BlockType
{
    public function type(): string { return 'sold_count'; }

    public function name(): string { return 'Clients livrés'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'users'; }

    public function settings(): array
    {
        return [
            new Number('min', 'Afficher à partir de', 10, 'Nombre de commandes livrées nécessaires.', 1, 100000),
            new Text('text', 'Texte', '{n} clients ont déjà reçu ce produit', '{n} est remplacé par le nombre réel de commandes livrées.', 100),
        ];
    }
}
