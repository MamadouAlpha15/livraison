<?php

namespace App\PageBuilder\Sections;

use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\Icon;
use App\PageBuilder\Settings\Text;
use App\PageBuilder\Settings\Url;

/** Bannière d'annonce : une ligne courte (livraison, paiement…), en général tout en haut de la page */
class Announcement extends SectionType
{
    public function type(): string { return 'announcement'; }

    public function name(): string { return 'Bannière d\'annonce'; }

    public function category(): string { return 'mise_en_page'; }

    public function icon(): string { return 'bell'; }

    public function description(): ?string
    {
        return 'Une ligne courte en haut de la page : paiement, livraison, nouveauté.';
    }

    public function settings(): array
    {
        return [
            new Icon('icon', 'Icône', 'truck'),
            new Text('text', 'Texte', 'Paiement à la livraison · Commande en 30 secondes', null, 120),
            new Url('link', 'Lien', null, 'Facultatif.'),
        ];
    }

    public function presets(): array
    {
        return [[
            'name'     => 'Bannière d\'annonce',
            'settings' => ['color_scheme' => 'nuit', 'padding_top_mobile' => 8, 'padding_bottom_mobile' => 8, 'padding_top_desktop' => 8, 'padding_bottom_desktop' => 8],
            'blocks'   => [],
        ]];
    }
}
