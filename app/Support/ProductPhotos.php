<?php

namespace App\Support;

use App\Jobs\ProcessProductImageJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Range une image (octets) comme une photo produit envoyée par le vendeur : même traitement que
 * ProductController::uploadImage (WebP 300 / 800 / 1600 px). Sert aux photos créées par Shopio IA
 * et aux photos importées depuis un lien.
 */
class ProductPhotos
{
    /** Chemins acceptés pour une photo produit (principale ou galerie). */
    public const PATH = '#^products(/gallery)?/medium/[A-Za-z0-9]{20}\.webp$#';

    /** @return array{path: string, url: string} */
    public static function store(string $bytes, string $folder = 'products'): array
    {
        $info = @getimagesizefromstring($bytes);
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$info['mime'] ?? ''] ?? null;
        if (!$ext) throw new \RuntimeException('Ce fichier n’est pas une photo lisible.');
        $folder = $folder === 'products/gallery' ? 'products/gallery' : 'products';
        $name = Str::random(20);
        $raw = "temp/{$name}.{$ext}";
        Storage::disk('public')->put($raw, $bytes);
        ProcessProductImageJob::dispatchSync($raw, $folder, $name);
        $path = "{$folder}/medium/{$name}.webp";
        if (!Storage::disk('public')->exists($path)) throw new \RuntimeException('La photo n’a pas pu être préparée. Réessayez.');
        return ['path' => $path, 'url' => Storage::disk('public')->url($path)];
    }
}
