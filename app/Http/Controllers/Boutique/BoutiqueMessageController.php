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
     * Répondre à un client (message texte normal)
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
            'type'        => ShopMessage::TYPE_TEXT,
            'read_at'     => null,
        ]);

        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $clientId)
            ->where('receiver_id', $vendeur->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Réponse envoyée.');
    }

    /**
     * Créer une offre de prix personnalisée pour un client
     * POST /boutique/messages/price-offer
     */
    public function createPriceOffer(Request $request)
    {
        $request->validate([
            'client_id'          => ['required', 'exists:users,id'],
            'product_id'         => ['required', 'exists:products,id'],
            'offered_price'      => ['required', 'numeric', 'min:1'],
            'proposal_message_id'=> ['nullable', 'exists:shop_messages,id'],
        ]);

        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;
        abort_unless($shop, 403);

        $product = Product::findOrFail($request->product_id);
        $offeredPrice = (float) $request->offered_price;

        // Si on répond à une proposition du client, on la marque "acceptée"
        if ($request->filled('proposal_message_id')) {
            ShopMessage::where('id', $request->proposal_message_id)
                ->where('shop_id', $shop->id)
                ->where('type', ShopMessage::TYPE_PRICE_PROPOSAL)
                ->where('proposal_status', ShopMessage::STATUS_PENDING)
                ->update(['proposal_status' => ShopMessage::STATUS_ACCEPTED]);
        }

        $devise = $shop->currency ?? 'GNF';

        ShopMessage::create([
            'shop_id'         => $shop->id,
            'product_id'      => $request->product_id,
            'sender_id'       => $vendeur->id,
            'receiver_id'     => $request->client_id,
            'body'            => "🏷️ Je vous propose **{$product->name}** au prix négocié de " . number_format($offeredPrice, 0, ',', ' ') . " {$devise}. Cliquez sur « Confirmer » pour valider votre commande.",
            'type'            => ShopMessage::TYPE_PRICE_OFFER,
            'proposed_price'  => $offeredPrice,
            'proposal_status' => ShopMessage::STATUS_PENDING,
        ]);

        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $request->client_id)
            ->where('receiver_id', $vendeur->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Offre envoyée au client.']);
    }

    /**
     * Refuser la proposition de prix d'un client
     * POST /boutique/messages/refuse-proposal/{message}
     */
    public function refuseProposal(Request $request, ShopMessage $message)
    {
        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;

        abort_unless($shop && $message->shop_id === $shop->id, 403);
        abort_unless(
            $message->type === ShopMessage::TYPE_PRICE_PROPOSAL &&
            $message->proposal_status === ShopMessage::STATUS_PENDING,
            403
        );

        $message->update(['proposal_status' => ShopMessage::STATUS_REFUSED]);

        $devise = $shop->currency ?? 'GNF';

        ShopMessage::create([
            'shop_id'     => $shop->id,
            'product_id'  => $message->product_id,
            'sender_id'   => $vendeur->id,
            'receiver_id' => $message->sender_id,
            'body'        => "❌ Votre proposition de " . number_format($message->proposed_price, 0, ',', ' ') . " {$devise} n'a pas été acceptée. N'hésitez pas à faire une nouvelle proposition.",
            'type'        => ShopMessage::TYPE_TEXT,
        ]);

        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $message->sender_id)
            ->where('receiver_id', $vendeur->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
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

        $unread = ShopMessage::where('shop_id', $shop->id)
            ->whereNull('read_at')
            ->whereHas('sender', fn($q) => $q->where('role', 'client'))
            ->count();

        $hasNew = ShopMessage::where('shop_id', $shop->id)
            ->where('created_at', '>=', now()->subSeconds(12))
            ->whereHas('sender', fn($q) => $q->where('role', 'client'))
            ->exists();

        return response()->json([
            'unread' => $unread,
            'reload' => $hasNew,
        ]);
    }

    /**
     * Marquer les messages d'un client comme lus (quand on ouvre la discussion)
     * POST /boutique/messages/read
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'client_id'  => ['required', 'exists:users,id'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;

        abort_unless($shop, 403);

        $query = ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $request->client_id)
            ->where('receiver_id', $vendeur->id)
            ->whereNull('read_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $updated = $query->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'marked'  => $updated,
        ]);
    }
}
