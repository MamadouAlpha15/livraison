<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Image;
use App\PageBuilder\Settings\Text;
use App\PageBuilder\Settings\Url;

/** Logo d'un média (section Vu dans) */
class Logo extends BlockType
{
    public function type(): string { return 'logo'; }

    public function name(): string { return 'Logo'; }

    public function icon(): string { return 'image'; }

    public function settings(): array
    {
        return [
            new Image('image', 'Logo'),
            new Text('name', 'Nom du média', null, 'Lu par les lecteurs d\'écran.', 60),
            new Url('link', 'Lien vers l\'article', null, 'Facultatif, mais il prouve ce que tu affiches.'),
        ];
    }
}
