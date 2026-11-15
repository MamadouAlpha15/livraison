<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Select;

/** Nom du produit */
class Title extends BlockType
{
    public function type(): string { return 'title'; }

    public function name(): string { return 'Titre'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'sliders'; }

    public function settings(): array
    {
        return [
            new Select('size', 'Taille', 'large', null, ['medium' => 'Moyenne', 'large' => 'Grande', 'xlarge' => 'Très grande']),
        ];
    }
}
