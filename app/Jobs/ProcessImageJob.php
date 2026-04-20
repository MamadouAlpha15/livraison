<?php

namespace App\Jobs;

use App\Models\ShopMessage;
use App\Services\ImageOptimizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 300; // 5 min max par job (lot de 20 images)

    public function __construct(
        private int    $messageId,
        private array  $tempPaths,  // chemins dans storage/app/temp/
        private string $folder,     // ex: messages/12
    ) {
        $this->onQueue('images');
    }

    public function handle(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $message = ShopMessage::find($this->messageId);
        if (!$message) {
            $this->cleanTemp();
            return;
        }

        $paths = [];

        foreach ($this->tempPaths as $tempPath) {
            try {
                $absPath = Storage::disk('local')->path($tempPath);

                if (!file_exists($absPath)) {
                    Log::warning("ProcessImageJob: fichier temp introuvable: {$absPath}");
                    continue;
                }

                // Crée un UploadedFile factice à partir du fichier temp
                $file = new \Illuminate\Http\File($absPath);
                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $absPath,
                    basename($absPath),
                    mime_content_type($absPath) ?: 'image/jpeg',
                    null,
                    true // test mode = pas de validation is_uploaded_file()
                );

                $paths[] = ImageOptimizer::store($uploadedFile, $this->folder);

            } catch (\Throwable $e) {
                Log::error("ProcessImageJob: erreur sur {$tempPath}: " . $e->getMessage());
            }
        }

        // Mettre à jour le message avec les chemins finaux optimisés
        $message->update([
            'images'       => $paths,
            'image_status' => count($paths) > 0 ? 'ready' : 'failed',
        ]);

        $this->cleanTemp();
    }

    public function failed(\Throwable $e): void
    {
        ShopMessage::where('id', $this->messageId)
            ->update(['image_status' => 'failed']);

        $this->cleanTemp();
        Log::error("ProcessImageJob échoué (msg #{$this->messageId}): " . $e->getMessage());
    }

    private function cleanTemp(): void
    {
        foreach ($this->tempPaths as $path) {
            Storage::disk('local')->delete($path);
        }
    }
}
