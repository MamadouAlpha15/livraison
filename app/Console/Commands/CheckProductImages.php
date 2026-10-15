<?php

namespace App\Console\Commands;

use App\Models\BrokenImageReport;
use App\Models\Product;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckProductImages extends Command
{
    /**
     * Parcourt tous les produits actifs et vérifie qu'ils ont une photo
     * principale ET que le fichier existe réellement sur le disque (photo
     * principale + galerie). Signale les problèmes dans `broken_image_reports`
     * pour que le SuperAdmin puisse prévenir le vendeur concerné. Résout
     * automatiquement un signalement dès que le vendeur a ajouté/corrigé sa
     * photo (détecté au passage suivant).
     */
    protected $signature = 'products:check-images';

    protected $description = "Vérifie que les produits ont une photo qui existe réellement (aucune photo OU upload cassé)";

    public function handle(): int
    {
        $disk = ImageOptimizer::activeDisk();
        $flaggedCount  = 0;
        $resolvedCount = 0;

        Product::where('is_active', true)
            ->chunkById(200, function ($products) use ($disk, &$flaggedCount, &$resolvedCount) {
                foreach ($products as $product) {
                    $missing = $this->missingPathsFor($product, $disk);

                    $existingReport = BrokenImageReport::where('product_id', $product->id)
                        ->whereIn('status', ['open', 'notified'])
                        ->first();

                    if (!empty($missing)) {
                        if ($existingReport) {
                            $existingReport->update(['missing_paths' => $missing]);
                        } else {
                            BrokenImageReport::create([
                                'product_id'     => $product->id,
                                'shop_id'        => $product->shop_id,
                                'missing_paths'  => $missing,
                                'status'         => 'open',
                            ]);
                            $flaggedCount++;
                        }
                    } elseif ($existingReport) {
                        // Le vendeur a corrigé sa photo depuis — on referme automatiquement.
                        $existingReport->update(['status' => 'resolved', 'resolved_at' => now()]);
                        $resolvedCount++;
                    }
                }
            });

        $this->info("Vérification terminée : {$flaggedCount} nouveau(x) signalement(s), {$resolvedCount} résolu(s) automatiquement.");
        return self::SUCCESS;
    }

    /** @return array<int,string> chemins manquants pour ce produit (image principale + galerie) */
    private function missingPathsFor(Product $product, string $disk): array
    {
        // Pas de photo principale du tout (jamais ajoutée) — cas distinct de l'upload cassé,
        // mais même conséquence pour le client : rien ne s'affiche. On le signale pareil.
        if (!$product->image) {
            return ['__aucune_photo__'];
        }

        $missing = [];
        $candidates = [];

        $candidates[] = ImageOptimizer::variant($product->image, 'medium');
        $candidates[] = ImageOptimizer::variant($product->image, 'thumb');

        if ($product->gallery) {
            $gallery = is_string($product->gallery) ? json_decode($product->gallery, true) : $product->gallery;
            foreach ((array) $gallery as $path) {
                if ($path) $candidates[] = ImageOptimizer::variant($path, 'medium');
            }
        }

        foreach (array_unique(array_filter($candidates)) as $path) {
            if (!Storage::disk($disk)->exists($path)) {
                $missing[] = $path;
            }
        }

        return $missing;
    }
}
