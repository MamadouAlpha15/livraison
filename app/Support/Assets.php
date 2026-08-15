<?php

namespace App\Support;

/**
 * ═══════════════════════════════════════════════════════════════
 * Assets — URL versionnée pour les fichiers statiques (public/images, ...).
 * ═══════════════════════════════════════════════════════════════
 * .htaccess met les images en cache jusqu'à 1 MOIS chez le visiteur
 * (ExpiresByType image/png "access plus 1 month"). Sans un paramètre de
 * version dans l'URL, remplacer un fichier sur le serveur (ex: nouveau
 * logo lors d'un rebranding) ne suffit pas : les navigateurs continuent
 * de servir l'ancienne image en cache jusqu'à un mois, même après avoir
 * réinstallé la PWA (le cache HTTP du navigateur est indépendant du
 * service worker). NE TOUCHE PAS au loader lui-même (#pg-loader) — sert
 * uniquement à corriger l'URL de l'image affichée dedans.
 *
 * ::v() ajoute automatiquement `?v=<date de modification du fichier>` à
 * l'URL : dès que le fichier change sur le serveur, l'URL change aussi,
 * ce qui force un téléchargement frais — sans jamais avoir à penser à
 * bumper un numéro de version à la main.
 */
class Assets
{
    public static function v(string $path): string
    {
        $relative = ltrim($path, '/');
        $full = public_path($relative);
        $version = is_file($full) ? filemtime($full) : time();

        return asset($relative) . '?v=' . $version;
    }
}
