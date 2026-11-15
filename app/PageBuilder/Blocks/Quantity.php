<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Number;
use App\PageBuilder\Settings\Text;

/** Sélecteur de quantité. Ignoré si la section a des offres groupées (l'offre fixe la quantité). */
class Quantity extends BlockType
{
    public function type(): string { return 'quantity'; }

    public function name(): string { return 'Quantité'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'plus'; }

    public function settings(): array
    {
        return [
            new Text('label', 'Intitulé', 'Quantité', null, 40),
            new Number('max', 'Quantité maximale par commande', 10, 'Limitée aussi par le stock réel.', 1, 50),
        ];
    }
}
