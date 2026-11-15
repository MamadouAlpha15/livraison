<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Checkbox;

/** Prix du produit (et ancien prix barré s'il existe) */
class Price extends BlockType
{
    public function type(): string { return 'price'; }

    public function name(): string { return 'Prix'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'tag'; }

    public function settings(): array
    {
        return [
            new Checkbox('show_compare', 'Afficher l\'ancien prix barré', true),
            new Checkbox('show_saving', 'Afficher l\'économie en pourcentage', true),
        ];
    }
}
