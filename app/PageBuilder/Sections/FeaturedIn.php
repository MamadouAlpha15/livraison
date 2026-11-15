<?php

namespace App\PageBuilder\Sections;

use App\PageBuilder\SectionType;
use App\PageBuilder\Settings\Checkbox;
use App\PageBuilder\Settings\Text;

/**
 * « Vu dans » : logos des médias qui ont parlé de la boutique. Rien n'est affiché aux clients tant que
 * le vendeur n'a pas confirmé que ces médias ont réellement parlé de lui.
 */
class FeaturedIn extends SectionType
{
    public function type(): string { return 'featured-in'; }

    public function name(): string { return 'Vu dans'; }

    public function category(): string { return 'preuve'; }

    public function icon(): string { return 'megaphone'; }

    public function maxPerPage(): ?int { return 1; }

    public function maxBlocks(): int { return 8; }

    public function description(): ?string
    {
        return 'Les logos des médias qui ont vraiment parlé de ta boutique.';
    }

    public function settings(): array
    {
        return [
            new Text('heading', 'Titre', 'Ils parlent de nous', null, 80),
            new Checkbox('confirmed', 'Je confirme que ces médias ont réellement parlé de ma boutique', false, 'Obligatoire pour afficher la section à tes clients.'),
        ];
    }

    public function blocks(): array
    {
        return ['logo'];
    }

    public function presets(): array
    {
        return [['name' => 'Vu dans', 'settings' => [], 'blocks' => [['type' => 'logo'], ['type' => 'logo'], ['type' => 'logo']]]];
    }
}
