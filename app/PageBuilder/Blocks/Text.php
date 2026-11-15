<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\RichText;
use App\PageBuilder\Settings\Select;

/** Paragraphe libre */
class Text extends BlockType
{
    public function type(): string { return 'text'; }

    public function name(): string { return 'Texte'; }

    public function icon(): string { return 'list'; }

    public function settings(): array
    {
        return [
            new RichText('content', 'Texte', '<p>Décris ton produit en quelques mots.</p>'),
            new Select('style', 'Style', 'body', null, ['body' => 'Normal', 'subtitle' => 'Sous-titre', 'caption' => 'Petit texte']),
        ];
    }
}
