<?php

namespace App\PageBuilder\Sections;

use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\Text;

/** Étapes de commande numérotées (un bloc par étape) */
class Steps extends SectionType
{
    public function type(): string { return 'steps'; }

    public function name(): string { return 'Étapes de commande'; }

    public function category(): string { return 'conversion'; }

    public function icon(): string { return 'navigation'; }

    public function maxBlocks(): int { return 6; }

    public function description(): ?string
    {
        return 'Comment se passe une commande, du formulaire à la livraison.';
    }

    public function settings(): array
    {
        return [new Text('heading', 'Titre', 'Comment ça marche ?', null, 80)];
    }

    public function blocks(): array
    {
        return ['step'];
    }

    /** Préréglage : le vrai parcours d'une commande Shopio payée à la livraison */
    public function presets(): array
    {
        $steps = [
            ['check', 'Tu commandes', 'Le formulaire prend 30 secondes.'],
            ['phone', 'La boutique te confirme', 'Par appel ou WhatsApp, juste après ta commande.'],
            ['map-pin', 'Tu suis le livreur', 'Sur la carte, en direct.'],
            ['wallet', 'Tu paies à la réception', 'En espèces, en main propre.'],
        ];
        return [[
            'name'     => 'Étapes de commande',
            'settings' => [],
            'blocks'   => array_map(fn ($s) => ['type' => 'step', 'settings' => ['icon' => $s[0], 'title' => $s[1], 'text' => $s[2]]], $steps),
        ]];
    }
}
