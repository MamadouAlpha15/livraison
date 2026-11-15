<?php

namespace App\PageBuilder\Sections;

use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\RichText as RichTextSetting;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;

/** Texte libre : un titre et un paragraphe mis en forme */
class RichContent extends SectionType
{
    public function type(): string { return 'rich-text'; }

    public function name(): string { return 'Texte'; }

    public function category(): string { return 'contenu'; }

    public function icon(): string { return 'list'; }

    public function description(): ?string
    {
        return 'Un titre et un paragraphe pour raconter ton produit.';
    }

    public function settings(): array
    {
        return [
            new Text('heading', 'Titre', 'Pourquoi nos clients l\'adorent', null, 120),
            new RichTextSetting('content', 'Texte', '<p>Raconte ici ce qui rend ton produit unique.</p>'),
            new Select('align', 'Alignement', 'center', null, ['left' => 'À gauche', 'center' => 'Centré']),
        ];
    }

    public function presets(): array
    {
        return [
            ['name' => 'Texte', 'settings' => [], 'blocks' => []],
            ['name' => 'Texte sur fond foncé', 'settings' => ['color_scheme' => 'nuit'], 'blocks' => []],
        ];
    }
}
