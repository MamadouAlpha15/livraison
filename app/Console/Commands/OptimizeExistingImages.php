<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageOptimizer;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Illuminate\Support\Str;

/**
 * php artisan images:optimize
 *
 * Convertit toutes les images brutes (jpg/png) du storage public
 * en WebP avec 3 tailles (thumb/medium/large), exactement comme
 * ImageOptimizer::store() — compatible avec ImageOptimizer::url().
 *
 * Les anciens fichiers sont conservés pour éviter les liens cassés
 * déjà en base de données. On génère les variantes WebP à côté.
 */
class OptimizeExistingImages extends Command
{
    protected $signature   = 'images:optimize
                              {--dry-run : Affiche ce qui sera fait sans rien modifier}
                              {--delete-originals : Supprime les fichiers originaux après conversion}';
    protected $description = 'Convertit les images brutes (jpg/png) en WebP 3 tailles (thumb/medium/large)';

    public function handle(): int
    {
        $dryRun         = $this->option('dry-run');
        $deleteOriginals = $this->option('delete-originals');

        $this->info('🔍 Recherche des images brutes non optimisées…');

        // Trouver tous les fichiers jpg/png qui NE sont PAS déjà dans un dossier /medium/ /thumb/ /large/
        $allFiles = Storage::disk('public')->allFiles();
        $rawImages = array_filter($allFiles, function ($path) {
            // Exclure les variantes déjà optimisées
            if (preg_match('#/(medium|thumb|large)/#', $path)) return false;
            // Garder seulement les images
            return preg_match('/\.(jpg|jpeg|png|gif|bmp)$/i', $path);
        });

        $total = count($rawImages);

        if ($total === 0) {
            $this->info('✅ Toutes les images sont déjà optimisées !');
            return self::SUCCESS;
        }

        $this->warn("📷 {$total} image(s) brute(s) trouvée(s)" . ($dryRun ? ' (dry-run, aucune modification)' : ''));

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        $manager = new ImageManager(new Driver());
        $sizes   = ['thumb' => 300, 'medium' => 800, 'large' => 1600];
        $success = 0;
        $errors  = 0;

        foreach ($rawImages as $rawPath) {
            $bar->setMessage(basename($rawPath));

            if ($dryRun) {
                $this->newLine();
                $this->line("  [dry-run] Convertirait : {$rawPath}");
                $bar->advance();
                continue;
            }

            try {
                $absolutePath = Storage::disk('public')->path($rawPath);
                $folder       = dirname($rawPath);          // ex: products
                $filename     = Str::random(20);            // nouveau nom aléatoire

                // Générer les 3 variantes WebP
                foreach ($sizes as $key => $width) {
                    $img     = $manager->decode($absolutePath);
                    $img->scaleDown(width: $width);
                    $encoded = $img->encode(new WebpEncoder(quality: 82));
                    $newPath = "{$folder}/{$key}/{$filename}.webp";
                    Storage::disk('public')->put($newPath, (string) $encoded);
                    unset($img, $encoded);
                }

                // Mettre à jour la base de données : remplacer l'ancien chemin par /medium/
                $newMedium = "{$folder}/medium/{$filename}.webp";
                $this->updateDatabase($rawPath, $newMedium);

                if ($deleteOriginals) {
                    Storage::disk('public')->delete($rawPath);
                }

                $success++;
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->error("  ✗ Erreur sur {$rawPath} : " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ {$success} image(s) converties en WebP" . ($errors > 0 ? ", ⚠️ {$errors} erreur(s)" : ''));

        if (!$deleteOriginals && !$dryRun) {
            $this->comment('ℹ️  Les fichiers originaux sont conservés. Utilisez --delete-originals pour les supprimer.');
        }

        return self::SUCCESS;
    }

    /**
     * Met à jour les colonnes `image` et `gallery` dans les tables concernées.
     */
    private function updateDatabase(string $oldPath, string $newMediumPath): void
    {
        $tables = [
            ['table' => 'products', 'column' => 'image'],
            ['table' => 'products', 'column' => 'gallery'], // JSON
            ['table' => 'shops',    'column' => 'image'],
            ['table' => 'delivery_companies', 'column' => 'image'],
            ['table' => 'users',    'column' => 'image'],
            ['table' => 'orders',   'column' => 'image'],
        ];

        foreach ($tables as $entry) {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasTable($entry['table'])) continue;
                if (!\Illuminate\Support\Facades\Schema::hasColumn($entry['table'], $entry['column'])) continue;

                \Illuminate\Support\Facades\DB::table($entry['table'])
                    ->where($entry['column'], $oldPath)
                    ->update([$entry['column'] => $newMediumPath]);

                // Pour les colonnes JSON (gallery)
                \Illuminate\Support\Facades\DB::table($entry['table'])
                    ->whereRaw("JSON_SEARCH(`{$entry['column']}`, 'one', ?) IS NOT NULL", [$oldPath])
                    ->each(function ($row) use ($entry, $oldPath, $newMediumPath) {
                        $gallery = json_decode($row->{$entry['column']}, true);
                        if (is_array($gallery)) {
                            $gallery = array_map(fn($p) => $p === $oldPath ? $newMediumPath : $p, $gallery);
                            \Illuminate\Support\Facades\DB::table($entry['table'])
                                ->where('id', $row->id)
                                ->update([$entry['column'] => json_encode($gallery)]);
                        }
                    });
            } catch (\Throwable) {
                // Table ou colonne inexistante → on continue
            }
        }
    }
}
