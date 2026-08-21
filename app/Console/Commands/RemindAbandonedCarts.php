<?php

// ============================================================
// FICHIER : app/Console/Commands/RemindAbandonedCarts.php
// RÔLE    : Relance automatique des paniers abandonnés (étape 4).
//           Cherche les articles en panier depuis plus de 24h qui n'ont
//           pas encore déclenché de relance, et envoie UNE notification
//           push par client concerné (pas une par article).
//           Lancée automatiquement chaque jour via routes/console.php.
// ============================================================

namespace App\Console\Commands;

use App\Models\CartItem;
use App\Services\PushService;
use Illuminate\Console\Command;

class RemindAbandonedCarts extends Command
{
    protected $signature = 'cart:remind-abandoned {--hours=24 : Ancienneté minimale du panier avant relance}';

    protected $description = "Envoie une notification push aux clients ayant des articles oubliés dans leur panier.";

    public function handle(PushService $push): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $items = CartItem::whereNull('reminded_at')
            ->where('created_at', '<=', $cutoff)
            ->with(['user', 'product'])
            ->get()
            // Un produit supprimé/désactivé entretemps ne doit pas déclencher de relance.
            ->filter(fn (CartItem $i) => $i->user && $i->product && $i->product->is_active)
            ->groupBy('user_id');

        $sent = 0;

        foreach ($items as $userItems) {
            $user = $userItems->first()->user;
            $count = (int) $userItems->sum('quantity');
            $firstProductName = $userItems->first()->product->name;

            $body = $userItems->count() === 1
                ? "Vous avez laissé « {$firstProductName} » dans votre panier — il est toujours disponible !"
                : "Vous avez laissé {$count} article(s) dans votre panier — ils sont toujours disponibles !";

            try {
                $push->sendToUser($user, '🛒 Votre panier vous attend', $body, 0, '/client/cart');
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }

            CartItem::whereIn('id', $userItems->pluck('id'))->update(['reminded_at' => now()]);
        }

        $this->info("Relance de panier envoyée à {$sent} client(s).");

        return self::SUCCESS;
    }
}
