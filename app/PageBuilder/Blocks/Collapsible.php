<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Checkbox;
use App\PageBuilder\Settings\Icon;
use App\PageBuilder\Settings\RichText;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;

/** Onglet repliable : description du produit ou texte libre (composition, entretien, retours…) */
class Collapsible extends BlockType
{
    public function type(): string { return 'collapsible'; }

    public function name(): string { return 'Onglet repliable'; }

    public function icon(): string { return 'chevron-right'; }

    public function settings(): array
    {
        return [
            new Text('heading', 'Titre', 'Description', null, 80),
            new Icon('icon', 'Icône', 'info'),
            new Select('source', 'Contenu', 'description', null, ['description' => 'Description du produit', 'custom' => 'Texte libre']),
            new RichText('content', 'Texte libre', null),
            new Checkbox('open', 'Ouvert à l\'arrivée sur la page', false),
        ];
    }
}
