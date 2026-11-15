<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;

/** Choix de l'option du produit (couleur, taille…). Caché si le produit n'a pas d'options. */
class Variants extends BlockType
{
    public function type(): string { return 'variants'; }

    public function name(): string { return 'Options du produit'; }

    public function limit(): ?int { return 1; }

    public function icon(): string { return 'sliders'; }

    public function settings(): array
    {
        return [
            new Text('label', 'Intitulé', 'Choisis ton option', null, 60),
            new Select('style', 'Présentation', 'buttons', null, ['buttons' => 'Boutons', 'dropdown' => 'Liste déroulante']),
        ];
    }
}
