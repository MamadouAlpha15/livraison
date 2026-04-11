<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ShopMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopMessageController extends Controller
{
    /**
     * Envoyer un message depuis le client vers le vendeur.
     * Déclenché depuis la vue create_from_product.
     *
     * POST /client/products/{product}/message
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $shop = $product->shop;
        abort_unless($shop && $shop->is_approved, 404);

        $client  = Auth::user();
        $vendeur = $shop->user; // le propriétaire de la boutique

        /* Créer le message */
        ShopMessage::create([
            'shop_id'     => $shop->id,
            'product_id'  => $product->id,
            'sender_id'   => $client->id,
            'receiver_id' => $vendeur->id,
            'body'        => $request->body,
        ]);

        /* Retourner la conversation mise à jour */
        return back()->with('chat_sent', true);
    }

    /**
     * Récupérer les messages AJAX (polling toutes les 5s).
     * Marque les messages du vendeur comme lus.
     *
     * GET /client/products/{product}/messages
     */
    public function index(Product $product)
    {
        $shop   = $product->shop;
        $client = Auth::user();

        $messages = ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where(function ($q) use ($client) {
                $q->where('sender_id', $client->id)
                  ->orWhere('receiver_id', $client->id);
            })
            ->with(['sender:id,name'])
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'mine'       => $m->sender_id === $client->id,
                'sender'     => $m->sender->name,
                'time'       => $m->created_at->format('H:i'),
                'date'       => $m->created_at->diffForHumans(),
                'read'       => $m->read_at !== null,
            ]);

        /* Marquer les messages reçus comme lus */
        ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where('receiver_id', $client->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }
}