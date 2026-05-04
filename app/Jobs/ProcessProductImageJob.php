<?php

namespace App\Jobs;

use App\Services\ImageOptimizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessProductImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        private string $rawPath,    // chemin dans disk 'public' : temp/{filename}.ext
        private string $folder,     // 'products' ou 'products/gallery'
        private string $filename,   // nom sans extension (pré-calculé par le contrôleur)
    ) {
        $this->onQueue('images');
    }

    public function handle(): void
    {
        $prevMemory = ini_set('memory_limit', '512M');
        $prevTime   = ini_get('max_execution_time');
        set_time_limit(120);

        try {
            $this->process();
        } finally {
            if ($prevMemory !== false) ini_set('memory_limit', $prevMemory);
            if ($prevTime !== false)   set_time_limit((int) $prevTime);
        }
    }

    private function process(): void
    {
        $absPath = Storage::disk('public')->path($this->rawPath);

        if (!file_exists($absPath)) {
            Log::warning("ProcessProductImageJob: fichier introuvable: {$absPath}");
            return;
        }

        // Utiliser Intervention Image directement pour générer les WebP avec le bon nom
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $disk    = Storage::disk('public');

        $sizes = ['thumb' => 300, 'medium' => 800, 'large' => 1600];

        foreach ($sizes as $key => $width) {
            $img     = $manager->read($absPath);
            $img->scaleDown(width: $width);
            $encoded = $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 82));
            $disk->put("{$this->folder}/{$key}/{$this->filename}.webp", (string) $encoded);
            unset($img, $encoded);
            gc_collect_cycles();
        }

        // Supprimer le fichier brut temporaire
        $disk->delete($this->rawPath);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ProcessProductImageJob échoué ({$this->rawPath}): " . $e->getMessage());
    }
}
