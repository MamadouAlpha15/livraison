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
     * Afficher la vue HTML du chat client ↔ vendeur.
     * GET /client/products/{product}/messages
     */
    public function index(Product $product)
    {
        $shop   = $product->shop;
        $client = Auth::user();

        abort_unless($shop, 404);

        // Si requête AJAX → retourner JSON (polling)
        if (request()->ajax() || request()->wantsJson()) {
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
                    'id'     => $m->id,
                    'body'   => $m->body,
                    'mine'   => $m->sender_id === $client->id,
                    'sender' => $m->sender->name,
                    'time'   => $m->created_at->format('H:i'),
                    'date'   => $m->created_at->diffForHumans(),
                    'read'   => $m->read_at !== null,
                ]);

            ShopMessage::where('shop_id', $shop->id)
                ->where('product_id', $product->id)
                ->where('receiver_id', $client->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json($messages);
        }

        // Sinon → vue HTML
        $messages = ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where(function ($q) use ($client) {
                $q->where('sender_id', $client->id)
                  ->orWhere('receiver_id', $client->id);
            })
            ->with(['sender:id,name'])
            ->orderBy('created_at')
            ->get();

        // Marquer comme lus
        ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where('receiver_id', $client->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('client.messages.show', compact('product', 'shop', 'messages'));
    }

    /**
     * Envoyer un message.
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
        $vendeur = $shop->user;

        abort_unless($vendeur, 404);

        ShopMessage::create([
            'shop_id'     => $shop->id,
            'product_id'  => $product->id,
            'sender_id'   => $client->id,
            'receiver_id' => $vendeur->id,
            'body'        => $request->body,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['sent' => true]);
        }

        return back()->with('chat_sent', true);
    }
}