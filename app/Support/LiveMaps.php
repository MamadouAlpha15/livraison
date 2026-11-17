<?php

namespace App\Support;

/**
 * Cartes de la vue en direct : un tracé SVG par pays, fabriqués à partir de Natural Earth (domaine public).
 *  - africa : resources/data/africa-map.json — 1:50m, Mercator, cadre 1000 × 1000.
 *  - world  : resources/data/world-map.json  — 1:110m (petits pays en points, d'après le 1:50m), projection
 *             Natural Earth, 1000 px de large, sans l'Antarctique.
 * Chaque pays : iso (2 lettres), name (français), d (tracé, vide pour un point), c (centre), area (surface en px²).
 * La page « En direct » montre le globe (globe()) ; ces cartes à plat servent aux noms des pays (name()).
 */
class LiveMaps
{
    private static array $cache = [];

    /** @return array{viewBox: array, countries: array<int, array{iso: string, name: string, d: string, c: array, area: int}>} */
    public static function get(string $which): array
    {
        $file = $which === 'world' ? 'world-map.json' : 'africa-map.json';
        return self::$cache[$which] ??= json_decode((string) @file_get_contents(resource_path('data/' . $file)), true) ?: ['viewBox' => [0, 0, 1000, 1000], 'countries' => []];
    }

    /**
     * Globe de la vue en direct : resources/data/globe.json — Natural Earth 1:50m (domaine public), contours simplifiés
     * en longitude / latitude. Chaque pays : iso, name (français), c (centre [lon, lat]), area (degrés², pour les étiquettes),
     * p (contours ; vide pour un petit pays, montré par un point).
     * @return array{countries: array<int, array{iso: string, name: string, c: array, area: float, p: array}>}
     */
    public static function globe(): array
    {
        return self::$cache['globe'] ??= json_decode((string) @file_get_contents(resource_path('data/globe.json')), true) ?: ['countries' => []];
    }

    /** Nom français d'un pays par son code à 2 lettres (null s'il est inconnu). */
    public static function name(?string $iso): ?string
    {
        if (!$iso) return null;
        $iso = strtoupper($iso);
        foreach (self::get('world')['countries'] as $c) if ($c['iso'] === $iso) return $c['name'];
        return null;
    }
}
