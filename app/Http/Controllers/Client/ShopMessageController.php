<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShopMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                    'id'                  => $m->id,
                    'body'                => $m->body,
                    'mine'                => $m->sender_id === $client->id,
                    'sender'              => $m->sender->name,
                    'time'                => $m->created_at->format('H:i'),
                    'date'                => $m->created_at->diffForHumans(),
                    'read'                => $m->read_at !== null,
                    'type'                => $m->type ?? 'text',
                    'proposed_price'      => $m->proposed_price,
                    'proposal_status'     => $m->proposal_status,
                    'negotiated_order_id' => $m->negotiated_order_id,
                ]);

            ShopMessage::where('shop_id', $shop->id)
                ->where('product_id', $product->id)
                ->where('receiver_id', $client->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json($messages);
        }

        $messages = ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where(function ($q) use ($client) {
                $q->where('sender_id', $client->id)
                  ->orWhere('receiver_id', $client->id);
            })
            ->with(['sender:id,name'])
            ->orderBy('created_at')
            ->get();

        ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where('receiver_id', $client->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('client.messages.show', compact('product', 'shop', 'messages'));
    }

    /**
     * Envoyer un message texte.
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
            'type'        => ShopMessage::TYPE_TEXT,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['sent' => true]);
        }

        return back()->with('chat_sent', true);
    }

    /**
     * Client propose un nouveau prix pour un produit.
     * POST /client/messages/propose-price
     */
    public function proposePrice(Request $request)
    {
        $request->validate([
            'product_id'     => ['required', 'exists:products,id'],
            'proposed_price' => ['required', 'numeric', 'min:1'],
        ]);

        $product = Product::with('shop')->findOrFail($request->product_id);
        $shop    = $product->shop;

        abort_unless($shop && $shop->is_approved, 403);

        $client  = Auth::user();
        $vendeur = $shop->user;

        abort_unless($vendeur, 403);

        $devise = $shop->currency ?? 'GNF';
        $price  = (float) $request->proposed_price;

        ShopMessage::create([
            'shop_id'         => $shop->id,
            'product_id'      => $product->id,
            'sender_id'       => $client->id,
            'receiver_id'     => $vendeur->id,
            'body'            => "💰 Je propose d'acheter **{$product->name}** à " . number_format($price, 0, ',', ' ') . " {$devise} au lieu de " . number_format($product->price, 0, ',', ' ') . " {$devise}.",
            'type'            => ShopMessage::TYPE_PRICE_PROPOSAL,
            'proposed_price'  => $price,
            'proposal_status' => ShopMessage::STATUS_PENDING,
        ]);

        return response()->json(['sent' => true]);
    }

    /**
     * Client confirme l'offre de prix du vendeur → crée une commande.
     * POST /client/messages/confirm-offer/{message}
     */
    public function confirmOffer(Request $request, ShopMessage $message)
    {
        $client = Auth::user();

        abort_unless(
            $message->type === ShopMessage::TYPE_PRICE_OFFER &&
            $message->receiver_id === $client->id &&
            $message->proposal_status === ShopMessage::STATUS_PENDING,
            403
        );

        $product = Product::with('shop')->findOrFail($message->product_id);
        $shop    = $product->shop;

        abort_unless($shop, 403);

        $negotiatedPrice = (float) $message->proposed_price;
        $devise          = $shop->currency ?? 'GNF';
        $orderId         = null;

        DB::transaction(function () use ($message, $client, $product, $shop, $negotiatedPrice, &$orderId) {
            // Créer la commande avec le prix négocié (jamais product.price)
            $order = Order::create([
                'user_id' => $client->id,
                'shop_id' => $shop->id,
                'total'   => $negotiatedPrice,
                'status'  => Order::STATUS_EN_ATTENTE,
            ]);

            // OrderItem avec le prix négocié stocké
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => 1,
                'price'      => $negotiatedPrice,
            ]);

            // Décrémenter le stock si géré
            if ($product->stock !== null) {
                $product->decrement('stock', 1);
            }

            // Paiement en attente
            Payment::create([
                'order_id' => $order->id,
                'method'   => 'cash',
                'amount'   => $negotiatedPrice,
                'status'   => 'en_attente',
            ]);

            // Marquer l'offre comme acceptée + lier la commande
            $message->update([
                'proposal_status'     => ShopMessage::STATUS_ACCEPTED,
                'negotiated_order_id' => $order->id,
            ]);

            // Message de confirmation dans la conversation
            ShopMessage::create([
                'shop_id'             => $shop->id,
                'product_id'          => $product->id,
                'sender_id'           => $client->id,
                'receiver_id'         => $message->sender_id,
                'body'                => "✅ Commande confirmée au prix négocié de " . number_format($negotiatedPrice, 0, ',', ' ') . " {$this->getDevise($shop)}. Commande n°{$order->id} créée avec succès !",
                'type'                => ShopMessage::TYPE_ORDER_CREATED,
                'proposed_price'      => $negotiatedPrice,
                'proposal_status'     => ShopMessage::STATUS_ACCEPTED,
                'negotiated_order_id' => $order->id,
            ]);

            $orderId = $order->id;
        });

        return response()->json([
            'success'  => true,
            'order_id' => $orderId,
            'message'  => 'Commande créée avec succès !',
        ]);
    }

    private function getDevise($shop): string
    {
        return $shop->currency ?? 'GNF';
    }
}
