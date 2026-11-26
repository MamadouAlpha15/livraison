<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Détourage des photos (fond retiré) avec rembg, via le script tools/rembg/cut.py.
 * Nécessite Python avec rembg installé sur le serveur, et SHOPIO_REMBG_PYTHON dans le .env
 * (ex. « python » ou « /usr/bin/python3 »). Sans réglage, le détourage est simplement indisponible.
 * La photo d'origine n'est jamais modifiée : le résultat va dans cutouts/{large,medium,thumb}/.
 */
class BackgroundRemover
{
    public static function available(): bool
    {
        return trim((string) config('shopio.rembg_python')) !== '' && is_file(base_path('tools/rembg/cut.py'));
    }

    /**
     * @param array<string,string> $images  nom du résultat => chemin de la photo sur le disque public (medium)
     * @return array<string,string|null>   nom => chemin de la photo détourée (medium) ou null si échec
     */
    public function run(array $images): array
    {
        $disk = Storage::disk('public');
        $tmp = storage_path('app/rembg-' . Str::random(10));
        @mkdir($tmp . '/in', 0775, true);
        $jobs = [];
        foreach ($images as $name => $path) {
            // Meilleure qualité disponible : la version large si elle existe, sinon la photo enregistrée
            $src = ImageOptimizer::variant($path, 'large');
            if (!$src || !$disk->exists($src)) $src = $path;
            if (!$src || !$disk->exists($src)) continue;
            $local = $tmp . '/in/' . $name . '.' . (pathinfo($src, PATHINFO_EXTENSION) ?: 'jpg');
            file_put_contents($local, $disk->get($src));
            $jobs[] = ['in' => $local, 'out' => $tmp . '/out', 'name' => $name];
        }
        $result = array_fill_keys(array_keys($images), null);
        if (!$jobs) { $this->clean($tmp); return $result; }

        file_put_contents($tmp . '/jobs.json', json_encode($jobs));
        // Environnement complet du serveur : sous un serveur web, Process ne transmet sinon que les variables
        // présentes dans $_SERVER, et Python sous Windows échoue sans SYSTEMROOT, APPDATA…
        $env = array_filter(getenv() ?: [], fn ($v) => is_string($v));
        $env['NUMBA_CACHE_DIR'] = $env['NUMBA_CACHE_DIR'] ?? storage_path('app/rembg-cache');
        $proc = Process::env($env)->timeout(60 + 45 * count($jobs))->run([config('shopio.rembg_python'), '-I', base_path('tools/rembg/cut.py'), $tmp . '/jobs.json']);
        if (!$proc->successful()) Log::warning('[Détourage] rembg a échoué : ' . Str::limit(trim($proc->errorOutput()), 300, '') . ' … ' . Str::substr(trim($proc->errorOutput()), -600));

        foreach (preg_split('/\R/', $proc->output()) as $line) {
            $r = json_decode($line, true);
            if (!is_array($r) || empty($r['ok']) || !array_key_exists($r['name'], $result)) {
                if (is_array($r) && isset($r['error'])) Log::info('[Détourage] ' . $r['name'] . ' : ' . $r['error']);
                continue;
            }
            foreach (['large', 'medium', 'thumb'] as $size) {
                $file = $tmp . '/out/' . $size . '/' . $r['name'] . '.png';
                if (is_file($file)) $disk->put('cutouts/' . $size . '/' . $r['name'] . '.png', file_get_contents($file));
            }
            $result[$r['name']] = 'cutouts/medium/' . $r['name'] . '.png';
        }
        $this->clean($tmp);
        return $result;
    }

    private function clean(string $dir): void
    {
        if (!is_dir($dir)) return;
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $f) $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        @rmdir($dir);
    }

    /** Supprime les trois tailles d'une photo détourée. */
    public static function forget(?string $cutout): void
    {
        if (!$cutout || !str_starts_with($cutout, 'cutouts/medium/')) return;
        foreach (['large', 'medium', 'thumb'] as $size) Storage::disk('public')->delete(str_replace('/medium/', "/$size/", $cutout));
    }
}
