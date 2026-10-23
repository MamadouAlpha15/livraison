<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Services\PushService;
use Illuminate\Console\Command;

/**
 * Résumé de la journée envoyé chaque soir au propriétaire de chaque boutique :
 * commandes reçues, livrées et encaissées, en attente, questions sans réponse, stock faible.
 * Aucune notification pour une boutique sans activité ni rien à traiter.
 */
class SendShopDailySummary extends Command
{
    protected $signature = 'shops:daily-summary {--dry-run : Affiche les résumés sans envoyer de notification}';

    protected $description = 'Envoie à chaque boutique le résumé de sa journée';

    public function handle(): int
    {
        $start = now()->startOfDay();
        $sent  = 0;

        Shop::with('user')->whereNotNull('user_id')->chunkById(100, function ($shops) use ($start, &$sent) {
            foreach ($shops as $shop) {
                $received  = Order::where('shop_id', $shop->id)->where('created_at', '>=', $start)->count();
                $delivered = Order::where('shop_id', $shop->id)->where('status', Order::STATUS_LIVREE)->where('delivered_at', '>=', $start);
                $doneCount = (clone $delivered)->count();
                $cashed    = (float) (clone $delivered)->sum('total');
                $pending   = Order::where('shop_id', $shop->id)->where('status', Order::STATUS_EN_ATTENTE)->count();
                $lowStock  = Product::where('shop_id', $shop->id)->where('is_active', true)->whereNotNull('stock')
                    ->whereColumn('stock', '<=', 'low_stock_threshold')->count();

                if (! $received && ! $doneCount && ! $pending && ! $lowStock) continue;

                $parts = [];
                $parts[] = $received . ' commande' . ($received > 1 ? 's' : '') . ' reçue' . ($received > 1 ? 's' : '');
                if ($doneCount) {
                    $parts[] = $doneCount . ' livrée' . ($doneCount > 1 ? 's' : '') . ' (' . number_format($cashed, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF') . ')';
                }
                if ($pending)   $parts[] = $pending . ' en attente';
                if ($lowStock)  $parts[] = $lowStock . ' produit' . ($lowStock > 1 ? 's' : '') . ' en stock faible';

                $body = "Aujourd'hui : " . implode(' · ', $parts) . '.';

                if ($this->option('dry-run')) {
                    $this->line($shop->name . ' → ' . $body);
                    continue;
                }
                if (! $shop->user) continue;
                try {
                    $push = app(PushService::class);
                    $push->sendToUser($shop->user, 'Votre journée sur Shopio', $body, $push->vendorBadgeCount($shop->user), '/boutique/dashboard');
                    $sent++;
                } catch (\Throwable $e) {
                    $this->warn($shop->name . ' : ' . $e->getMessage());
                }
            }
        });

        $this->info($sent . ' résumé(s) envoyé(s).');
        return self::SUCCESS;
    }
}
