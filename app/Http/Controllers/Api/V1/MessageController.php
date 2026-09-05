<?php

namespace App\Http\Controllers\Api\V1;

// ─────────────────────────────────────────────────────────────────────────────
// MessageController (API v1) — messagerie client ↔ vendeur, une conversation
// par (boutique, produit), comme sur le site (Client\ShopMessageController) :
// texte, propositions de prix, offres/contre-offres, envoi de photos.
// ─────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShopMessage;
use App\Services\ImageOptimizer;
use App\Services\PushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    /** GET /api/v1/messages — liste des conversations (dernière boutique/produit en premier) */
    public function index(Request $request): JsonResponse
    {
        $client = $request->user();

        $allMsgs = ShopMessage::where(function ($q) use ($client) {
                $q->where('sender_id', $client->id)
                  ->orWhere('receiver_id', $client->id);
            })
            ->with(['shop:id,name,image', 'product:id,name,price,image', 'sender:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $conversations = $allMsgs
            ->groupBy(fn ($m) => ($m->shop_id ?? 0) . '_' . ($m->product_id ?? 0))
            ->map(function ($msgs) use ($client) {
                $last   = $msgs->first();
                $unread = $msgs->filter(fn ($m) => is_null($m->read_at) && $m->receiver_id === $client->id)->count();

                return [
                    'shop_id'      => $last->shop_id,
                    'shop_name'    => $last->shop?->name,
                    'product_id'   => $last->product_id,
                    'product_name' => $last->product?->name,
                    'last_message' => $last->body ?? $last->note,
                    'last_at'      => $last->created_at?->toIso8601String(),
                    'unread'       => $unread,
                ];
            })
            ->values();

        return response()->json(['data' => $conversations]);
    }

    /** GET /api/v1/products/{product}/messages — fil de discussion pour ce produit */
    public function show(Request $request, Product $product): JsonResponse
    {
        $shop   = $product->shop;
        $client = $request->user();

        abort_unless($shop, 404);

        $messages = ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where(function ($q) use ($client) {
                $q->where('sender_id', $client->id)->orWhere('receiver_id', $client->id);
            })
            ->with('sender:id,name')
            ->orderBy('created_at')
            ->get();

        if (!$request->boolean('poll')) {
            ShopMessage::where('shop_id', $shop->id)
                ->where('product_id', $product->id)
                ->where('receiver_id', $client->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json(['data' => MessageResource::collection($messages)]);
    }

    /** POST /api/v1/products/{product}/messages — { body } */
    public function store(Request $request, Product $product): JsonResponse
    {
        $request->validate(['body' => ['required', 'string', 'max:1000']]);

        $shop = $product->shop;
        abort_unless($shop && $shop->is_approved, 404);

        $client  = $request->user();
        $vendeur = $shop->user;
        abort_unless($vendeur, 404);

        $message = ShopMessage::create([
            'shop_id'     => $shop->id,
            'product_id'  => $product->id,
            'sender_id'   => $client->id,
            'receiver_id' => $vendeur->id,
            'body'        => $request->body,
            'type'        => ShopMessage::TYPE_TEXT,
        ]);

        try {
            $push = app(PushService::class);
            $push->sendToUser(
                $vendeur,
                'Nouveau message de ' . $client->name,
                $request->body,
                $push->vendorBadgeCount($vendeur),
                '/boutique/messages'
            );
        } catch (\Throwable $e) {}

        return response()->json(['data' => new MessageResource($message->load('sender'))], 201);
    }

    /** POST /api/v1/messages/propose-price — { product_id, proposed_price, message? } */
    public function proposePrice(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'     => ['required', 'exists:products,id'],
            'proposed_price' => ['required', 'numeric', 'min:1'],
            'message'        => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::with('shop')->findOrFail($request->product_id);
        $shop    = $product->shop;
        abort_unless($shop && $shop->is_approved, 403);

        $client  = $request->user();
        $vendeur = $shop->user;
        abort_unless($vendeur, 403);

        $devise        = $shop->currency ?? 'GNF';
        $price         = (float) $request->proposed_price;
        $customMessage = trim((string) $request->input('message', ''));

        $autoText = "💰 Je propose d'acheter **{$product->name}** à "
                  . number_format($price, 0, ',', ' ') . " {$devise} au lieu de "
                  . number_format($product->price, 0, ',', ' ') . " {$devise}.";

        $message = ShopMessage::create([
            'shop_id'         => $shop->id,
            'product_id'      => $product->id,
            'sender_id'       => $client->id,
            'receiver_id'     => $vendeur->id,
            'body'            => $customMessage !== '' ? ($customMessage . "\n\n" . $autoText) : $autoText,
            'note'            => $customMessage !== '' ? $customMessage : null,
            'type'            => ShopMessage::TYPE_PRICE_PROPOSAL,
            'proposed_price'  => $price,
            'proposal_status' => ShopMessage::STATUS_PENDING,
        ]);

        try {
            $pushBody = $client->name . ' propose ' . number_format($price, 0, ',', ' ') . " {$devise} pour {$product->name}.";
            if ($customMessage !== '') $pushBody .= ' « ' . Str::limit($customMessage, 60) . ' »';
            app(PushService::class)->sendToUser($vendeur, 'Proposition de prix 💰', $pushBody, 1, '/boutique/messages');
        } catch (\Throwable $e) {}

        return response()->json(['data' => new MessageResource($message->load('sender'))], 201);
    }

    /** POST /api/v1/messages/{message}/confirm-offer — accepte l'offre du vendeur, crée la commande */
    public function confirmOffer(Request $request, ShopMessage $message): JsonResponse
    {
        $client = $request->user();

        abort_unless(
            in_array($message->type, [ShopMessage::TYPE_PRICE_OFFER, ShopMessage::TYPE_COUNTER_OFFER]) &&
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

        DB::transaction(function () use ($message, $client, $product, $shop, $negotiatedPrice, $devise, &$orderId) {
            $order = Order::create([
                'user_id' => $client->id,
                'shop_id' => $shop->id,
                'total'   => $negotiatedPrice,
                'status'  => Order::STATUS_EN_ATTENTE,
            ]);

            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => 1,
                'price'      => $negotiatedPrice,
            ]);

            if ($product->stock !== null) {
                $product->decrement('stock', 1);
            }

            Payment::create([
                'order_id' => $order->id,
                'method'   => 'cash',
                'amount'   => $negotiatedPrice,
                'status'   => 'en_attente',
            ]);

            $message->update([
                'proposal_status'     => ShopMessage::STATUS_ACCEPTED,
                'negotiated_order_id' => $order->id,
            ]);

            ShopMessage::create([
                'shop_id'             => $shop->id,
                'product_id'          => $product->id,
                'sender_id'           => $client->id,
                'receiver_id'         => $message->sender_id,
                'body'                => "✅ Commande confirmée au prix négocié de "
                                        . number_format($negotiatedPrice, 0, ',', ' ')
                                        . " {$devise}. Commande n°{$order->id} créée avec succès !",
                'type'                => ShopMessage::TYPE_ORDER_CREATED,
                'proposed_price'      => $negotiatedPrice,
                'proposal_status'     => ShopMessage::STATUS_ACCEPTED,
                'negotiated_order_id' => $order->id,
            ]);

            $orderId = $order->id;
        });

        return response()->json(['order_id' => $orderId, 'message' => 'Commande créée avec succès !'], 201);
    }

    /** POST /api/v1/messages/{message}/refuse-offer — refuse l'offre du vendeur */
    public function refuseOffer(Request $request, ShopMessage $message): JsonResponse
    {
        $client = $request->user();
        abort_unless(
            in_array($message->type, [ShopMessage::TYPE_PRICE_OFFER, ShopMessage::TYPE_COUNTER_OFFER]) &&
            $message->receiver_id === $client->id &&
            $message->proposal_status === ShopMessage::STATUS_PENDING,
            403
        );

        $message->update(['proposal_status' => ShopMessage::STATUS_REFUSED]);

        $product = Product::with('shop')->find($message->product_id);
        $devise  = $product?->shop?->currency ?? 'GNF';

        ShopMessage::create([
            'shop_id'     => $message->shop_id,
            'product_id'  => $message->product_id,
            'sender_id'   => $client->id,
            'receiver_id' => $message->sender_id,
            'body'        => "❌ Le client a refusé l'offre de "
                            . number_format($message->proposed_price, 0, ',', ' ')
                            . " {$devise}. Une nouvelle négociation est possible.",
            'type'        => ShopMessage::TYPE_TEXT,
        ]);

        return response()->json(['success' => true]);
    }

    /** POST /api/v1/messages/counter-offer — { message_id, counter_price, message? } */
    public function counterOffer(Request $request): JsonResponse
    {
        $request->validate([
            'message_id'    => ['required', 'exists:shop_messages,id'],
            'counter_price' => ['required', 'numeric', 'min:1'],
            'message'       => ['nullable', 'string', 'max:500'],
        ]);

        $client   = $request->user();
        $original = ShopMessage::findOrFail($request->message_id);

        abort_unless(
            in_array($original->type, [ShopMessage::TYPE_PRICE_OFFER, ShopMessage::TYPE_COUNTER_OFFER]) &&
            $original->receiver_id === $client->id &&
            $original->proposal_status === ShopMessage::STATUS_PENDING,
            403
        );

        $product = Product::with('shop')->find($original->product_id);
        $shop    = $product?->shop;
        $devise  = $shop?->currency ?? 'GNF';
        $vendeur = $shop?->user;
        abort_unless($vendeur, 403);

        $counterPrice  = (float) $request->counter_price;
        $customMessage = trim((string) $request->input('message', ''));

        $original->update(['proposal_status' => ShopMessage::STATUS_REFUSED]);

        $autoText = "🔄 Contre-proposition du client : " . number_format($counterPrice, 0, ',', ' ') . " {$devise}.";

        $message = ShopMessage::create([
            'shop_id'         => $original->shop_id,
            'product_id'      => $original->product_id,
            'sender_id'       => $client->id,
            'receiver_id'     => $vendeur->id,
            'body'            => $customMessage !== '' ? ($customMessage . "\n\n" . $autoText) : $autoText,
            'note'            => $customMessage !== '' ? $customMessage : null,
            'type'            => ShopMessage::TYPE_COUNTER_OFFER,
            'proposed_price'  => $counterPrice,
            'proposal_status' => ShopMessage::STATUS_PENDING,
        ]);

        try {
            $pushBody = $client->name . ' contre-propose ' . number_format($counterPrice, 0, ',', ' ') . " {$devise} pour {$product->name}.";
            if ($customMessage !== '') $pushBody .= ' « ' . Str::limit($customMessage, 60) . ' »';
            app(PushService::class)->sendToUser($vendeur, 'Contre-offre reçue 🔄', $pushBody, 1, '/boutique/messages');
        } catch (\Throwable $e) {}

        return response()->json(['data' => new MessageResource($message->load('sender'))], 201);
    }

    /** POST /api/v1/products/{product}/messages/images — envoi de photos (multipart/form-data, champ "images[]") */
    public function sendImages(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'images'   => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:10240'],
        ]);

        $client = $request->user();
        $shop   = $product->shop;
        abort_unless($shop && $shop->is_approved, 404);

        $vendeur = $shop->user;
        abort_unless($vendeur, 404);

        $paths = [];
        foreach ($request->file('images') as $file) {
            try {
                $paths[] = ImageOptimizer::store($file, 'messages/' . $shop->id);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('sendImages optimize failed: ' . $e->getMessage());
            }
        }

        $count = count($request->file('images'));

        $message = ShopMessage::create([
            'shop_id'      => $shop->id,
            'product_id'   => $product->id,
            'sender_id'    => $client->id,
            'receiver_id'  => $vendeur->id,
            'body'         => $count . ' photo(s)',
            'images'       => $paths,
            'image_status' => count($paths) > 0 ? 'ready' : 'failed',
            'type'         => ShopMessage::TYPE_IMAGES,
        ]);

        try {
            app(PushService::class)->sendToUser(
                $vendeur, 'Photo de ' . $client->name,
                '📷 ' . $count . ' photo(s) reçue(s) — cliquez pour voir', 1, '/boutique/messages'
            );
        } catch (\Throwable $e) {}

        return response()->json(['data' => new MessageResource($message->load('sender'))], 201);
    }
}
