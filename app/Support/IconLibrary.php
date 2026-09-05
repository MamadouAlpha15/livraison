<?php

namespace App\Support;

/**
 * ═══════════════════════════════════════════════════════════════
 * IconLibrary — icônes SVG (ligne fine, style "premium") pour la page
 * d'accueil, à la place des émojis. currentColor → hérite la couleur du
 * texte environnant.
 * ═══════════════════════════════════════════════════════════════
 * Classe statique plutôt que des closures définies dans un @php Blade :
 * welcome.blade.php inclut partials/catalogue-results.blade.php, qui est
 * AUSSI rendu seul (fragment AJAX) par WelcomeController@index pour la
 * recherche en direct. Un appel de méthode statique fonctionne à
 * l'identique dans les deux cas, sans dépendre de la portée des variables
 * Blade (contrairement à `@include`, qui ne fait pas remonter les
 * variables définies dans la vue incluse vers la vue appelante).
 */
class IconLibrary
{
    /** @var array<string, string> */
    private static array $icons = [
        'home'       => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/>',
        'store'      => '<path d="M4 9 5.2 4h13.6L20 9"/><path d="M4 9v11h16V9"/><path d="M4 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/>',
        'search'     => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/>',
        'package'    => '<path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v9l9 5 9-5V8"/><path d="M12 13v9"/>',
        'bag'        => '<path d="M6 8h12l1 13H5L6 8Z"/><path d="M9 8a3 3 0 0 1 6 0"/>',
        'truck'      => '<rect x="1" y="7" width="13" height="10" rx="1"/><path d="M14 10h4l3 3v4h-7z"/><circle cx="5.5" cy="18.5" r="1.6"/><circle cx="17.5" cy="18.5" r="1.6"/>',
        'zap'        => '<path d="M13 3 4 14h6l-1 7 9-11h-6l1-7Z"/>',
        'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
        'star'       => '<path d="m12 3 2.6 5.9 6.4.6-4.8 4.3 1.4 6.2L12 16.9 6.4 20l1.4-6.2L3 9.5l6.4-.6L12 3Z"/>',
        'cart'       => '<circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h2l2.6 12.4a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L21 7H6"/>',
        'tag'        => '<path d="M20 12.5 12.5 20a1.5 1.5 0 0 1-2.1 0L3 12.6V4h8.6L20 12.5Z"/><circle cx="7.5" cy="7.5" r="1.3"/>',
        'grid'       => '<rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/>',
        'eye'        => '<path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/>',
        'plus'       => '<path d="M12 5v14M5 12h14"/>',
        'sort'       => '<path d="M7 4v16M4 7l3-3 3 3"/><path d="M17 20V4M14 17l3 3 3-3"/>',
        'smartphone' => '<rect x="6" y="2" width="12" height="20" rx="2.2"/><path d="M11 18h2"/>',
        'laptop'     => '<rect x="3" y="4" width="18" height="12" rx="1.2"/><path d="M2 20h20"/>',
        'shirt'      => '<path d="M8 3 4 6l1.5 3L8 8v13h8V8l2.5 1L20 6l-4-3-2 2h-4L8 3Z"/>',
        'shoe'       => '<path d="M3 20v-3c0-1 .5-2 1.5-2.5L11 11V8a2 2 0 0 1 2-2h1c2 0 3 1 5 3l2 2c.7.7 1 1.6 1 2.5V17a2 2 0 0 1-2 2H3Z"/>',
        'sparkles'   => '<path d="m12 3 1.2 3.6L17 8l-3.8 1.4L12 13l-1.2-3.6L7 8l3.8-1.4L12 3Z"/><path d="m19 14 .7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7.7-2Z"/>',
        'sofa'       => '<path d="M4 12V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M3 12h18v5a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-5Z"/><path d="M5 18v2M19 18v2"/>',
        'utensils'   => '<path d="M6 2v8M4 2v4a2 2 0 0 0 4 0V2"/><path d="M18 2c-2 1-3 3-3 6 0 2 1 3 3 3v11"/>',
        'image'      => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-4 4-3-3-6 6"/>',
        'apple'      => '<path d="M12 8c-2.2 0-4 2.2-4 5.5S9.8 20 12 20s4-2.5 4-6.5S14.2 8 12 8Z"/><path d="M12 8c0-2 1-3 2.5-3.5"/>',
        'cup'        => '<path d="M6 8h12l-1 12H7L6 8Z"/><path d="m6.5 8 1-4h9l1 4"/>',
        'dumbbell'   => '<path d="M4 9v6M2 10.5v3M20 9v6M22 10.5v3M7 12h10"/>',
        'baby'       => '<circle cx="12" cy="13" r="6"/><circle cx="7" cy="6" r="2.2"/><circle cx="17" cy="6" r="2.2"/>',
        'car'        => '<path d="M3 13 4.5 8A2 2 0 0 1 6.4 6.5h11.2A2 2 0 0 1 19.5 8L21 13"/><rect x="2" y="13" width="20" height="5" rx="1.5"/><circle cx="7" cy="18" r="1.7"/><circle cx="17" cy="18" r="1.7"/>',
        'bike'       => '<circle cx="5.5" cy="17.5" r="3"/><circle cx="18.5" cy="17.5" r="3"/><path d="M5.5 17.5 9 9h4l3 4.5h3M9 9l2 3"/>',
        'gem'        => '<path d="M6 3h12l3 6-9 12L3 9l3-6Z"/><path d="M3 9h18M9 3l3 6-3 12M15 3l-3 6 3 12"/>',
        'watch'      => '<circle cx="12" cy="12" r="6"/><path d="M12 9v3l2 1.5"/><path d="M9 3h6l-.5 3h-5L9 3ZM9 21h6l-.5-3h-5l-.5 3Z"/>',
        'pill'       => '<rect x="3.5" y="7.5" width="17" height="9" rx="4.5" transform="rotate(-30 12 12)"/><path d="m9 8.5 6 7"/>',
        'book'       => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5v-17Z"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>',
        'sprout'     => '<path d="M12 21v-8"/><path d="M5 10c0 4 3 5 7 5 0-4-3-5-7-5Z"/><path d="M19 6c0 4-3 5-7 5 0-4 3-5 7-5Z"/>',
        'wrench'     => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.8 2.8-2-2 2.8-2.8Z"/>',
        'wind'       => '<path d="M4 12h9a3 3 0 1 0-3-3"/><path d="M4 17h11a3 3 0 1 1-3 3"/><path d="M4 7h6a2 2 0 1 0-2-2"/>',
        'heart'      => '<path d="M12 20.5s-7-4.35-9.5-8.7C.7 8.6 2 5 5.4 5c2 0 3.4 1.2 4.1 2.4C10.2 6.2 11.6 5 13.6 5 17 5 18.3 8.6 16.5 11.8 14 16.15 12 20.5 12 20.5Z"/>',
        'x'          => '<path d="M18 6 6 18M6 6l12 12"/>',
        'shield'     => '<path d="M12 3 5 6v5c0 4.6 3 7.7 7 9 4-1.3 7-4.4 7-9V6l-7-3Z"/><path d="m9.2 12.2 2 2 3.6-3.8"/>',
        'rotate'     => '<path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/>',
        'wallet'     => '<rect x="2" y="6" width="20" height="14" rx="2.5"/><path d="M2 10.5h20"/><circle cx="17" cy="15" r="1.3" fill="currentColor" stroke="none"/>',
        'chevron-up' => '<path d="m18 15-6-6-6 6"/>',
        'trophy'     => '<path d="M8 21h8"/><path d="M12 17v4"/><path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M7 5H4a3 3 0 0 0 3 3"/><path d="M17 5h3a3 3 0 0 1-3 3"/>',
        'message'    => '<path d="M4 4h16v13H8l-4 3.5V4Z"/>',
        'check'      => '<path d="M20 6 9 17l-5-5"/>',
        'arrow-left' => '<path d="M19 12H5"/><path d="m11 18-6-6 6-6"/>',
        'user'       => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>',
        'map-pin'    => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'phone'      => '<path d="M4 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L14 13l5 2v4a2 2 0 0 1-2 2C9.5 21 3 14.5 3 6a2 2 0 0 1 1-2Z"/>',
        'alert'      => '<path d="M12 3 2 20h20L12 3Z"/><path d="M12 10v4"/><path d="M12 17h.01"/>',
        'gift'       => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v9H5v-9"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C10 3 12 8 12 8s2-5 4.5-5a2.5 2.5 0 0 1 0 5"/>',
    ];

    /** Icône par catégorie (recherche par mot-clé, insensible à la casse). @var array<string, string> */
    private static array $categoryKeywords = [
        'électro' => 'smartphone', 'electro' => 'smartphone', 'téléphone' => 'smartphone', 'telephone' => 'smartphone', 'informatique' => 'laptop', 'ordinateur' => 'laptop',
        'mode' => 'shirt', 'vêtement' => 'shirt', 'vetement' => 'shirt', 'habill' => 'shirt', 'chaussure' => 'shoe',
        'beauté' => 'sparkles', 'beaute' => 'sparkles', 'cosmétique' => 'sparkles', 'cosmetique' => 'sparkles', 'parfum' => 'sparkles',
        'maison' => 'home', 'meuble' => 'sofa', 'cuisine' => 'utensils', 'déco' => 'image', 'deco' => 'image',
        'alimentation' => 'apple', 'épicerie' => 'apple', 'epicerie' => 'apple', 'boisson' => 'cup',
        'sport' => 'dumbbell', 'fitness' => 'dumbbell',
        'enfant' => 'baby', 'bébé' => 'baby', 'bebe' => 'baby', 'jouet' => 'baby',
        'auto' => 'car', 'moto' => 'bike', 'véhicule' => 'car', 'vehicule' => 'car',
        'bijou' => 'gem', 'montre' => 'watch', 'sac' => 'bag',
        'santé' => 'pill', 'sante' => 'pill', 'pharma' => 'pill',
        'livre' => 'book', 'papeterie' => 'book',
        'jardin' => 'sprout', 'bricolage' => 'wrench', 'outil' => 'wrench', 'électroménager' => 'wind', 'electromenager' => 'wind',
    ];

    /** Rend une icône SVG inline par son nom. */
    public static function svg(string $name, string $class = '', int $size = 18): string
    {
        $inner = self::$icons[$name] ?? self::$icons['tag'];
        $cls = trim('ico ' . $class);

        return '<svg class="' . $cls . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>';
    }

    /** Devine la clé d'icône la plus pertinente pour un nom de catégorie. */
    public static function categoryKey(?string $name): string
    {
        $n = mb_strtolower((string) $name);
        foreach (self::$categoryKeywords as $needle => $key) {
            if (str_contains($n, $needle)) {
                return $key;
            }
        }

        return 'tag';
    }

    /** Rend directement l'icône SVG correspondant à un nom de catégorie. */
    public static function categorySvg(?string $name, string $class = '', int $size = 18): string
    {
        return self::svg(self::categoryKey($name), $class, $size);
    }

    /** Rend 5 étoiles (pleines jusqu'à la note arrondie, vides ensuite). */
    public static function stars(float $rating, int $size = 12): string
    {
        $rating = max(0, min(5, $rating));
        $full = (int) round($rating);
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $filled = $i <= $full;
            $cls = 'ico star-ico ' . ($filled ? 'is-filled' : 'is-empty');
            $fill = $filled ? 'currentColor' : 'none';
            $html .= '<svg class="' . $cls . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="' . $fill . '" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . self::$icons['star'] . '</svg>';
        }

        return $html;
    }
}
