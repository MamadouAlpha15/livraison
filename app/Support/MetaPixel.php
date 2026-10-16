<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

/**
 * Pixel Meta (Facebook) d'un vendeur.
 *
 * Le pixel n'est chargé que sur les pages de SA boutique (vitrine publique, fiches produit)
 * et à la confirmation d'une commande passée chez lui. Il n'est jamais chargé :
 *  - pour le vendeur lui-même (ses propres visites fausseraient ses statistiques) ;
 *  - dans l'aperçu de l'éditeur de vitrine.
 *
 * Exception : le « mode test » (page Marketing → Tester mon pixel) active le pixel pour le
 * vendeur pendant 30 minutes et affiche un panneau listant les événements envoyés.
 */
class MetaPixel
{
    public const TEST_MINUTES = 30;
    /** Un identifiant de pixel Meta est un nombre de 15 ou 16 chiffres ; on tolère 10 à 20. */
    public static function normalize(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);
        return ($digits !== '' && strlen($digits) >= 10 && strlen($digits) <= 20) ? $digits : null;
    }

    /** Le pixel de cette boutique doit-il être chargé pour le visiteur actuel ? */
    public static function activeFor(?Shop $shop, bool $isPreview = false): bool
    {
        if (!$shop || !$shop->meta_pixel_id || $isPreview) return false;

        $user = Auth::user();
        $isOwner = $user && ((int) $shop->user_id === (int) $user->id || (int) $user->shop_id === (int) $shop->id);

        return !$isOwner || self::testingShop($shop);
    }

    /** Démarre le mode test pour la boutique du vendeur connecté. */
    public static function startTest(Shop $shop): void
    {
        session(['meta_pixel_test' => [
            'shop_id' => $shop->id,
            'pixel'   => (string) $shop->meta_pixel_id,
            'until'   => now()->addMinutes(self::TEST_MINUTES)->timestamp,
        ]]);
    }

    public static function stopTest(): void
    {
        session()->forget('meta_pixel_test');
    }

    /** Mode test en cours (et non expiré) : [shop_id, pixel, until], sinon null. */
    public static function test(): ?array
    {
        $test = session('meta_pixel_test');
        if (!is_array($test) || (int) ($test['until'] ?? 0) < now()->timestamp) return null;
        return $test;
    }

    private static function testingShop(Shop $shop): bool
    {
        $test = self::test();
        return $test && (int) $test['shop_id'] === (int) $shop->id && $test['pixel'] === (string) $shop->meta_pixel_id;
    }

    /**
     * Prépare l'événement « Purchase » d'une commande pour la page suivante (redirection).
     * Une même commande n'est envoyée qu'une fois par session ; l'identifiant d'événement
     * « order-{id} » permet aussi à Meta de dédoublonner.
     */
    public static function queuePurchase(Order $order): void
    {
        $shop = $order->shop;
        if (!self::activeFor($shop)) return;

        $sent = session('meta_pixel_orders', []);
        if (in_array($order->id, $sent, true)) return;
        session(['meta_pixel_orders' => array_slice(array_merge($sent, [$order->id]), -50)]);

        $items = $order->items()->get(['product_id', 'quantity']);
        // Liste : une commande panier peut concerner plusieurs boutiques (donc plusieurs pixels)
        $queue = session('meta_pixel_purchases', []);
        $queue[] = [
            'pixel'    => $shop->meta_pixel_id,
            'event_id' => 'order-' . $order->id,
            'params'   => [
                'value'        => (float) $order->total,
                'currency'     => $shop->currency ?? 'GNF',
                'content_type' => 'product',
                'content_ids'  => $items->pluck('product_id')->map(fn ($id) => (string) $id)->values()->all(),
                'num_items'    => (int) $items->sum('quantity'),
            ],
        ];
        session()->flash('meta_pixel_purchases', $queue);
    }
}
