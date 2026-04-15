<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\ShopMessage;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoutiqueMessageController extends Controller
{
    /**
     * Répondre à un client
     * POST /boutique/messages/reply/{client}/{product?}
     */
    public function reply(Request $request, $client, $product = null)
    {
        $request->validate([
            'body'       => ['required', 'string', 'max:1000'],
            'client_id'  => ['required', 'exists:users,id'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;
        abort_unless($shop, 403);

        $productId = $request->product_id ?: ($product && $product != 0 ? $product : null);
        $clientId  = $request->client_id;

        ShopMessage::create([
            'shop_id'     => $shop->id,
            'product_id'  => $productId,
            'sender_id'   => $vendeur->id,
            'receiver_id' => $clientId,
            'body'        => $request->body,
            'read_at'     => null,
        ]);

        /* Marquer les messages du client comme lus */
        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $clientId)
            ->where('receiver_id', $vendeur->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Réponse envoyée.');
    }

    /**
     * Polling AJAX — nb messages non lus + reload flag
     * GET /boutique/messages/poll
     */
    public function poll(Request $request)
    {
        if (!$request->ajax()) abort(403);

        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;

        if (!$shop) {
            return response()->json(['unread' => 0, 'reload' => false]);
        }

        /* Messages non lus de la boutique (envoyés par des clients) */
        $unread = ShopMessage::where('shop_id', $shop->id)
            ->whereNull('read_at')
            ->whereHas('sender', fn($q) => $q->where('role', 'client'))
            ->count();

        /* Nouveaux messages dans les 12 dernières secondes */
        $hasNew = ShopMessage::where('shop_id', $shop->id)
            ->where('created_at', '>=', now()->subSeconds(12))
            ->whereHas('sender', fn($q) => $q->where('role', 'client'))
            ->exists();

        return response()->json([
            'unread' => $unread,
            'reload' => $hasNew,
        ]);
    }
}