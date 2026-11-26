<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShopLook;
use App\Services\BackgroundRemover;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Détoure des photos d'une boutique (produits, motifs, looks) avec rembg et enregistre la photo
 * détourée à côté de l'originale (colonne image_cutout). Boutique → Looks → « Retirer le fond ».
 */
class RemoveBackgroundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 1800;
    public int $tries = 1;

    /** @param array<int,array{type:string,id:int}> $targets */
    public function __construct(public int $shopId, public array $targets) {}

    public static function busyKey(int $shopId): string
    {
        return 'cutouts-running:' . $shopId;
    }

    public function handle(BackgroundRemover $remover): void
    {
        @set_time_limit($this->timeout);
        $models = ['product' => Product::class, 'variant' => ProductVariant::class, 'look' => ShopLook::class];
        $images = [];
        $owners = [];
        foreach ($this->targets as $t) {
            $class = $models[$t['type']] ?? null;
            $m = $class ? $class::find($t['id']) : null;
            if (!$m || !$m->image) continue;
            // Sécurité : seulement les photos de la boutique qui a demandé
            $shopId = $t['type'] === 'variant' ? Product::whereKey($m->product_id)->value('shop_id') : $m->shop_id;
            if ((int) $shopId !== $this->shopId) continue;
            $name = substr($t['type'], 0, 1) . $m->id . '-' . Str::lower(Str::random(6));
            $images[$name] = $m->image;
            $owners[$name] = $m;
        }
        try {
            foreach ($remover->run($images) as $name => $cutout) {
                if (!$cutout) continue;
                $m = $owners[$name];
                BackgroundRemover::forget($m->image_cutout);
                $m->forceFill(['image_cutout' => $cutout])->saveQuietly();
            }
        } finally {
            Cache::forget(self::busyKey($this->shopId));
        }
    }
}
