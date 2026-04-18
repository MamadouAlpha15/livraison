<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Disk de stockage actif — 'public' en local, 's3'/'r2' en production CDN.
     * Change FILESYSTEM_DISK dans .env pour basculer vers le cloud.
     */
    private static function disk(): string
    {
        $disk = config('filesystems.default', 'public');
        // Si le disk par défaut est 'local' (privé), on force 'public' pour les images
        return $disk === 'local' ? 'public' : $disk;
    }

    /**
     * Optimise et stocke une image uploadée.
     * Génère 3 tailles : thumb (300px), medium (800px), large (1600px)
     * Convertit en WebP pour 30-50% de gain de taille.
     * Compatible stockage local ET S3/R2/Wasabi sans changer le code.
     *
     * @return string Le chemin de l'image principale (medium)
     */
    public static function store(UploadedFile $file, string $folder = 'products'): string
    {
        // Augmente la mémoire pour traiter les grandes images (photos de téléphone = ~15-20 Mo)
        $prevMemory = ini_set('memory_limit', '512M');
        // Augmente le temps d'exécution par image (pour les traitements en lot)
        $prevTime = ini_get('max_execution_time');
        set_time_limit(300);

        try {
            $manager  = new ImageManager(new Driver());
            $filename = Str::random(20);
            $rawPath  = $file->getRealPath();
            $disk     = self::disk();

            $sizes = [
                'thumb'  => 300,
                'medium' => 800,
                'large'  => 1600,
            ];

            $paths = [];

            foreach ($sizes as $key => $width) {
                $img     = $manager->decode($rawPath);
                $img->scaleDown(width: $width);
                $encoded = $img->encode(new WebpEncoder(quality: 82));

                $path = "{$folder}/{$key}/{$filename}.webp";
                Storage::disk($disk)->put($path, (string) $encoded);
                $paths[$key] = $path;

                unset($img, $encoded);
                gc_collect_cycles(); // libère la mémoire entre chaque taille
            }

            return $paths['medium'];

        } finally {
            // Restaure les paramètres d'origine dans tous les cas
            if ($prevMemory !== false) ini_set('memory_limit', $prevMemory);
            if ($prevTime !== false)   set_time_limit((int) $prevTime);
        }
    }

    /**
     * Récupère le chemin d'une variante à partir du chemin medium stocké.
     * Compatible avec les anciennes images (pas de /medium/ dans le chemin).
     * Ex: products/medium/abc.webp → products/thumb/abc.webp
     */
    public static function variant(?string $mediumPath, string $size = 'thumb'): ?string
    {
        if (!$mediumPath) return null;
        // Nouvelle image optimisée : le chemin contient /medium/
        if (str_contains($mediumPath, '/medium/')) {
            return str_replace('/medium/', "/{$size}/", $mediumPath);
        }
        // Ancienne image non-optimisée : on retourne le chemin d'origine
        return $mediumPath;
    }

    /**
     * Retourne l'URL publique d'une variante.
     * Fonctionne avec stockage local ET S3/CDN automatiquement.
     * Ex: ImageOptimizer::url($product->image, 'thumb')
     */
    public static function url(?string $mediumPath, string $size = 'medium'): ?string
    {
        $path = self::variant($mediumPath, $size);
        if (!$path) return null;

        $disk = self::disk();
        if ($disk === 'public') {
            // Stockage local → URL via asset()
            return asset('storage/' . $path);
        }
        // Stockage cloud (S3/R2/Wasabi) → URL via le disk configuré
        return Storage::disk($disk)->url($path);
    }

    /**
     * Supprime toutes les variantes d'une image.
     * Compatible avec les anciennes images (suppression directe).
     */
    public static function delete(?string $mediumPath): void
    {
        if (!$mediumPath) return;
        $disk = self::disk();
        if (str_contains($mediumPath, '/medium/')) {
            foreach (['thumb', 'medium', 'large'] as $size) {
                Storage::disk($disk)->delete(
                    str_replace('/medium/', "/{$size}/", $mediumPath)
                );
            }
        } else {
            Storage::disk($disk)->delete($mediumPath);
        }
    }
}