<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\RichText;
use App\PageBuilder\Settings\Text;

/** Une question et sa réponse (section Questions fréquentes) */
class Question extends BlockType
{
    public function type(): string { return 'question'; }

    public function name(): string { return 'Question'; }

    public function icon(): string { return 'message'; }

    public function settings(): array
    {
        return [
            new Text('question', 'Question', 'Ta question ici ?', null, 160),
            new RichText('answer', 'Réponse', '<p>Ta réponse ici.</p>', null, 2000),
        ];
    }
}
