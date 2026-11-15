<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Icon;
use App\PageBuilder\Settings\Text;

/** Une étape (section Étapes de commande) */
class Step extends BlockType
{
    public function type(): string { return 'step'; }

    public function name(): string { return 'Étape'; }

    public function icon(): string { return 'check'; }

    public function settings(): array
    {
        return [
            new Icon('icon', 'Icône', 'check'),
            new Text('title', 'Titre', 'Une étape', null, 60),
            new Text('text', 'Texte', null, null, 160),
        ];
    }
}
