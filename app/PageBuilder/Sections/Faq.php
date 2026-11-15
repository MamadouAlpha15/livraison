<?php

namespace App\PageBuilder\Sections;

use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\Text;
use App\Support\ProductPageSettings;

/** Questions fréquentes : une question par bloc, réponses repliables */
class Faq extends SectionType
{
    public function type(): string { return 'faq'; }

    public function name(): string { return 'Questions fréquentes'; }

    public function category(): string { return 'contenu'; }

    public function icon(): string { return 'message'; }

    public function maxBlocks(): int { return 20; }

    public function description(): ?string
    {
        return 'Réponds d\'avance aux questions qui font hésiter.';
    }

    public function settings(): array
    {
        return [new Text('heading', 'Titre', 'Questions fréquentes', null, 80)];
    }

    public function blocks(): array
    {
        return ['question'];
    }

    /** Préréglage : les réponses vraies sur Shopio (paiement à la livraison, confirmation par la boutique) */
    public function presets(): array
    {
        $faq = array_slice(ProductPageSettings::shopDefaults()['faq'], 0, 2);
        return [[
            'name'     => 'Questions fréquentes',
            'settings' => [],
            'blocks'   => array_map(fn ($f) => ['type' => 'question', 'settings' => ['question' => $f['q'], 'answer' => '<p>' . e($f['a']) . '</p>']], $faq),
        ]];
    }
}
