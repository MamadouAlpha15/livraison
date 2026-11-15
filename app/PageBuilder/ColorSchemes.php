<?php

namespace App\PageBuilder;

use App\Models\Shop;
use App\Support\ProductPage;

/**
 * Jeux de couleurs d'une section, tirés des couleurs de la boutique (couleur d'accent des pages produit).
 * Chaque jeu donne un fond, un texte, un texte secondaire, une bordure, et la couleur des boutons.
 */
class ColorSchemes
{
    public const LABELS = [
        'clair'  => 'Clair',
        'doux'   => 'Doux',
        'accent' => 'Couleur de la boutique',
        'nuit'   => 'Nuit',
    ];

    /** Variables CSS de chaque jeu pour une boutique */
    public static function for(Shop $shop): array
    {
        $accent = ProductPage::shop($shop)['accent'];
        $onAccent = ProductPage::onAccent($accent);
        $accentNight = ProductPage::accentOnNight($accent);

        return [
            'clair'  => ['bg' => '#FFFFFF', 'text' => '#16162A', 'muted' => '#45455E', 'line' => '#E3E8F2', 'button' => $accent, 'on_button' => $onAccent],
            'doux'   => ['bg' => '#F6F9FF', 'text' => '#16162A', 'muted' => '#45455E', 'line' => '#E3E8F2', 'button' => $accent, 'on_button' => $onAccent],
            'accent' => ['bg' => $accent, 'text' => $onAccent, 'muted' => $onAccent, 'line' => 'rgba(255,255,255,.25)', 'button' => $onAccent === '#FFFFFF' ? '#FFFFFF' : '#16162A', 'on_button' => $onAccent === '#FFFFFF' ? $accent : '#FFFFFF'],
            'nuit'   => ['bg' => '#0B1E3D', 'text' => '#FFFFFF', 'muted' => '#9FB3D3', 'line' => '#1C3560', 'button' => $accentNight, 'on_button' => ProductPage::onAccent($accentNight)],
        ];
    }

    /** Déclaration CSS d'un jeu (variables --cs-*) pour l'attribut style d'une section */
    public static function css(Shop $shop, ?string $key): string
    {
        $scheme = self::for($shop)[$key] ?? self::for($shop)['clair'];
        return collect($scheme)->map(fn ($v, $k) => '--cs-' . str_replace('_', '-', $k) . ':' . $v)->implode(';');
    }
}
