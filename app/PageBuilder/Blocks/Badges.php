<?php

namespace App\PageBuilder\Blocks;

use App\PageBuilder\BlockType;
use App\PageBuilder\Settings\Header;
use App\PageBuilder\Settings\Icon;
use App\PageBuilder\Settings\Select;
use App\PageBuilder\Settings\Text;

/** Trois engagements de la boutique (icône + texte). Un texte vide masque l'engagement. */
class Badges extends BlockType
{
    public function type(): string { return 'badges'; }

    public function name(): string { return 'Engagements'; }

    public function limit(): ?int { return 2; }

    public function icon(): string { return 'shield'; }

    public function settings(): array
    {
        $defaults = [1 => ['wallet', 'Paiement à la livraison'], 2 => ['map-pin', 'Livraison suivie en direct'], 3 => ['truck', 'Livraison à domicile']];
        $settings = [new Select('layout', 'Présentation', 'row', null, ['row' => 'Côte à côte', 'list' => 'En liste'])];
        foreach ($defaults as $i => [$icon, $text]) {
            $settings[] = new Header("Engagement {$i}");
            $settings[] = new Icon("icon{$i}", 'Icône', $icon);
            $settings[] = new Text("text{$i}", 'Texte', $text, null, 60);
        }
        return $settings;
    }
}
