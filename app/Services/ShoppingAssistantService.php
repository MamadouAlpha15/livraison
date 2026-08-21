<?php

// ============================================================
// FICHIER : app/Services/ShoppingAssistantService.php
// RÔLE    : Assistant d'achat conversationnel (IA) pour les clients Shopio.
//           Phase 1 : recherche de produits + vérification du stock.
//           Phase 2 : recommandations / ventes complémentaires, basées sur
//           les commandes réelles (produits achetés ensemble) — pas des
//           suggestions inventées.
//           Phase 3 : passer une VRAIE commande depuis le chat — mais
//           UNIQUEMENT après un récapitulatif que le client confirme
//           explicitement (voir consigne stricte dans systemPrompt()).
// ============================================================

namespace App\Services;

use Anthropic\Client;
use Anthropic\Messages\ToolUseBlock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Rules\RealisticGuineaPhone;
use App\Services\PushService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShoppingAssistantService
{
    private Client $client;

    /** Nombre max d'aller-retours outils dans une seule réponse (garde-fou anti-boucle). */
    private const MAX_TOOL_ITERATIONS = 4;

    public function __construct()
    {
        $this->client = new Client(apiKey: config('services.anthropic.key'));
    }

    private function tools(): array
    {
        return [
            [
                'name' => 'search_products',
                'description' => "Recherche des produits dans le catalogue Shopio à partir de mots-clés, "
                    . "d'une catégorie et/ou d'un budget maximum en GNF (francs guinéens). "
                    . "Retourne jusqu'à 5 produits correspondants avec leur prix et leur stock.",
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string', 'description' => 'Mots-clés de recherche, ex: "samsung", "robe noire"'],
                        'category' => ['type' => 'string', 'description' => 'Catégorie du produit si mentionnée (optionnel)'],
                        'max_price' => ['type' => 'number', 'description' => 'Budget maximum en GNF (optionnel)'],
                    ],
                    'required' => ['query'],
                ],
            ],
            [
                'name' => 'check_stock',
                'description' => "Vérifie la quantité en stock disponible pour UN produit précis, "
                    . "à partir de son ID (obtenu via search_products).",
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'product_id' => ['type' => 'integer', 'description' => "L'ID du produit"],
                    ],
                    'required' => ['product_id'],
                ],
            ],
            [
                'name' => 'get_recommendations',
                'description' => "Suggère des produits complémentaires pour UN produit précis (vente additionnelle). "
                    . "Se base sur ce que les autres clients ont VRAIMENT acheté en même temps que ce produit "
                    . "(ou, à défaut, sur des produits de la même catégorie). "
                    . "À utiliser quand le client montre un intérêt clair pour un produit précis "
                    . "(il vient de le trouver, demande son prix/stock, ou dit vouloir l'acheter) — "
                    . "pas systématiquement à chaque message.",
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'product_id' => ['type' => 'integer', 'description' => "L'ID du produit pour lequel chercher des compléments"],
                    ],
                    'required' => ['product_id'],
                ],
            ],
            [
                'name' => 'create_order',
                'description' => "Passe une VRAIE commande pour le client — ceci crée réellement la commande en base "
                    . "et diminue le stock, ce n'est PAS un aperçu. "
                    . "RÈGLE ABSOLUE : n'appelle cet outil QUE si tu as d'abord présenté au client un récapitulatif clair "
                    . "(produit, quantité, prix total, adresse de livraison, téléphone) ET que le client a répondu "
                    . "explicitement pour confirmer (\"oui\", \"c'est bon\", \"confirme\", etc.) DANS UN MESSAGE SÉPARÉ "
                    . "après ce récapitulatif. Ne l'appelle JAMAIS sur une simple intention (\"je le prends\", \"je veux "
                    . "commander\") sans être passé par cette étape de récapitulatif + confirmation.",
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'product_id' => ['type' => 'integer'],
                        'variant_id' => ['type' => 'integer', 'description' => 'Optionnel, si le produit a des variantes'],
                        'quantity' => ['type' => 'integer', 'description' => 'Quantité commandée'],
                        'delivery_destination' => ['type' => 'string', 'description' => 'Adresse de livraison donnée par le client'],
                        'client_phone' => ['type' => 'string', 'description' => 'Numéro de téléphone du client'],
                    ],
                    'required' => ['product_id', 'quantity', 'delivery_destination', 'client_phone'],
                ],
            ],
        ];
    }

    private function systemPrompt(?string $country): string
    {
        $lieu = $country ? "Le client se trouve dans le pays : {$country}." : '';

        return "Tu es l'assistant d'achat de Shopio, un site marchand en ligne en Afrique de l'Ouest. "
            . "Tu aides les clients à trouver des produits, à vérifier leur disponibilité, "
            . "à découvrir des produits complémentaires, et à passer commande directement dans le chat. {$lieu} "
            . "Réponds TOUJOURS en français, de façon brève et chaleureuse (2 à 4 phrases maximum, pas de listes à puces). "
            . "Les prix sont en GNF (francs guinéens). "
            . "Utilise TOUJOURS l'outil search_products avant de proposer un produit — "
            . "ne cite JAMAIS un nom de produit, un prix ou une disponibilité qui ne vient pas directement du résultat d'un outil. "
            . "Si aucun produit ne correspond, dis-le simplement et propose d'élargir la recherche. "
            . "Quand le client montre un intérêt clair pour UN produit précis (il demande son prix/stock, "
            . "ou dit vouloir l'acheter), utilise get_recommendations pour lui proposer 1 ou 2 produits complémentaires, "
            . "de façon naturelle et sans insister — pas à chaque message, et jamais si le client cherche encore juste des idées. "
            . "Si l'outil ne renvoie aucun résultat, ne propose rien plutôt que d'inventer. "
            . "\n\nPour une commande passée dans le chat, suis TOUJOURS ces étapes dans l'ordre, une à la fois : "
            . "1) confirme le produit et la quantité souhaités ; "
            . "2) demande l'adresse de livraison si tu ne l'as pas ; "
            . "3) demande le numéro de téléphone si tu ne l'as pas ; "
            . "4) présente un récapitulatif complet (produit, quantité, prix total, adresse, téléphone) et demande "
            . "explicitement au client de confirmer ; "
            . "5) UNIQUEMENT si le client confirme dans un message séparé après ce récapitulatif, appelle create_order. "
            . "Ne saute JAMAIS l'étape de récapitulatif + confirmation, même si le client semble pressé. "
            . "Si le client préfère, tu peux aussi simplement lui dire de cliquer sur le bouton \"Commander\" "
            . "sous le produit affiché au lieu de commander par le chat.";
    }

    public function searchProducts(string $query, ?string $category, ?float $maxPrice, ?string $country): array
    {
        $q = Product::where('is_active', true)
            ->whereHas('shop', function ($sq) use ($country) {
                $sq->where('is_approved', true);
                if ($country) $sq->where('country', $country);
            })
            ->with('shop:id,name,currency');

        $keyword = trim($query);
        if ($keyword !== '') {
            $q->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('category', 'like', "%{$keyword}%");
            });
        }

        if ($category) {
            $q->where('category', 'like', '%' . $category . '%');
        }
        if ($maxPrice) {
            $q->where('price', '<=', $maxPrice);
        }

        return $q->latest()->limit(5)->get()->map(fn (Product $p) => [
            'id'       => $p->id,
            'name'     => $p->name,
            'price'    => (int) $p->current_price,
            'category' => $p->category,
            'shop'     => $p->shop->name ?? '',
            'currency' => $p->shop->currency ?? 'GNF',
            'stock'    => $p->has_variants ? null : $p->stock,
            'out_of_stock' => $p->out_of_stock,
            'image'    => $p->image ? asset('storage/' . $p->image) : null,
        ])->values()->toArray();
    }

    public function checkStock(int $productId): array
    {
        $product = Product::find($productId);
        if (!$product) {
            return ['found' => false];
        }

        return [
            'found'        => true,
            'name'         => $product->name,
            'has_variants' => $product->has_variants,
            'stock'        => $product->has_variants ? null : $product->stock,
            'out_of_stock' => $product->out_of_stock,
        ];
    }

    /**
     * Produits complémentaires pour $productId : d'abord ceux VRAIMENT achetés
     * dans les mêmes commandes (co-achats réels), sinon repli sur la même
     * catégorie si le produit n'a pas encore assez d'historique de ventes.
     */
    public function getRecommendations(int $productId, ?string $country): array
    {
        $product = Product::find($productId);
        if (!$product) {
            return [];
        }

        $orderIds = OrderItem::where('product_id', $productId)->pluck('order_id');

        $coPurchasedIds = collect();
        if ($orderIds->isNotEmpty()) {
            $coPurchasedIds = OrderItem::whereIn('order_id', $orderIds)
                ->where('product_id', '!=', $productId)
                ->select('product_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('product_id')
                ->orderByDesc('cnt')
                ->limit(6)
                ->pluck('product_id');
        }

        $query = Product::where('is_active', true)
            ->where('id', '!=', $productId)
            ->whereHas('shop', function ($sq) use ($country) {
                $sq->where('is_approved', true);
                if ($country) $sq->where('country', $country);
            })
            ->with('shop:id,name,currency');

        if ($coPurchasedIds->isNotEmpty()) {
            $query->whereIn('id', $coPurchasedIds);
        } elseif ($product->category) {
            $query->where('category', $product->category);
        } else {
            return [];
        }

        return $query->limit(4)->get()->map(fn (Product $p) => [
            'id'       => $p->id,
            'name'     => $p->name,
            'price'    => (int) $p->current_price,
            'category' => $p->category,
            'shop'     => $p->shop->name ?? '',
            'currency' => $p->shop->currency ?? 'GNF',
            'stock'    => $p->has_variants ? null : $p->stock,
            'out_of_stock' => $p->out_of_stock,
            'image'    => $p->image ? asset('storage/' . $p->image) : null,
        ])->values()->toArray();
    }

    /**
     * Crée une VRAIE commande (appelé uniquement après confirmation explicite du client
     * dans la conversation — voir la consigne stricte dans systemPrompt()). Réutilise les
     * mêmes règles que le reste du site : vérifie le stock, décrémente le stock,
     * crée le paiement (espèces à la livraison) et notifie le vendeur.
     */
    public function createOrder(
        int $userId,
        int $productId,
        ?int $variantId,
        int $quantity,
        string $deliveryDestination,
        string $clientPhone
    ): array {
        $quantity = max(1, min($quantity, 20));

        $validator = Validator::make(
            ['delivery_destination' => $deliveryDestination, 'client_phone' => $clientPhone],
            [
                'delivery_destination' => ['required', 'string', 'min:2', 'max:255'],
                'client_phone' => ['required', 'string', 'max:30', new RealisticGuineaPhone],
            ]
        );
        if ($validator->fails()) {
            return ['success' => false, 'error' => implode(' ', $validator->errors()->all())];
        }

        $product = Product::with('shop')->find($productId);
        if (!$product || !$product->is_active || !$product->shop || !$product->shop->is_approved) {
            return ['success' => false, 'error' => 'Ce produit n\'est plus disponible.'];
        }

        $variant = null;
        if ($product->has_variants && $variantId) {
            $variant = $product->variants()->find($variantId);
            if ($variant && $variant->stock < $quantity) {
                return ['success' => false, 'error' => "Stock insuffisant pour cette variante ({$variant->stock} disponible(s))."];
            }
        } elseif (!$product->has_variants && $product->stock !== null && $product->stock < $quantity) {
            return ['success' => false, 'error' => "Stock insuffisant ({$product->stock} disponible(s))."];
        }

        $unitPrice = $variant ? $variant->effective_price : $product->current_price;
        $total = $unitPrice * $quantity;

        $order = Order::create([
            'user_id' => $userId,
            'shop_id' => $product->shop->id,
            'total' => $total,
            'status' => Order::STATUS_EN_ATTENTE,
            'delivery_destination' => $deliveryDestination,
            'client_phone' => $clientPhone,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'variant_name' => $variant?->name,
            'quantity' => $quantity,
            'price' => $unitPrice,
        ]);

        if ($variant) {
            $variant->decrement('stock', $quantity);
        } elseif (!$product->has_variants && $product->stock !== null) {
            $product->decrement('stock', $quantity);
        }

        Payment::create([
            'order_id' => $order->id,
            'method' => 'cash',
            'amount' => $total,
            'status' => 'en_attente',
        ]);

        try {
            $push = app(PushService::class);
            $summary = $product->name . ($variant ? ' (' . $variant->name . ')' : '') . ' × ' . $quantity
                . ' — ' . number_format($total, 0, ',', ' ') . ' GNF';
            $shopOwner = $product->shop->user ?? null;
            if ($shopOwner) {
                $push->sendToUser($shopOwner, 'Nouvelle commande !', $summary, $push->vendorBadgeCount($shopOwner), '/employe/orders');
            }
            $push->notifyShopStaff($product->shop, 'Nouvelle commande !', $summary, '/employe/orders', $product->shop->user_id);
        } catch (\Throwable $e) {}

        return [
            'success'      => true,
            'order_id'     => $order->id,
            'product_name' => $product->name,
            'quantity'     => $quantity,
            'total'        => (int) $total,
        ];
    }

    /**
     * @param array<int, array{role: string, content: string}> $history Échanges précédents (texte brut uniquement)
     * @return array{reply: string, products: array, recommended: array, order: ?array}
     */
    public function chat(array $history, string $userMessage, ?string $country, ?int $userId = null): array
    {
        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $tools = $this->tools();
        $system = $this->systemPrompt($country);
        $lastProducts = [];
        $recommended = [];
        $orderResult = null;

        $response = $this->client->messages->create(
            model: 'claude-opus-5',
            maxTokens: 1024,
            system: $system,
            tools: $tools,
            messages: $messages,
        );

        $iterations = 0;
        while ($response->stopReason === 'tool_use' && $iterations < self::MAX_TOOL_ITERATIONS) {
            $iterations++;
            $toolResults = [];

            foreach ($response->content as $block) {
                if (!($block instanceof ToolUseBlock)) continue;

                if ($block->name === 'search_products') {
                    $results = $this->searchProducts(
                        (string) ($block->input['query'] ?? ''),
                        $block->input['category'] ?? null,
                        isset($block->input['max_price']) ? (float) $block->input['max_price'] : null,
                        $country
                    );
                    $lastProducts = $results;
                    $content = json_encode($results, JSON_UNESCAPED_UNICODE) ?: '[]';
                } elseif ($block->name === 'check_stock') {
                    $content = json_encode(
                        $this->checkStock((int) ($block->input['product_id'] ?? 0)),
                        JSON_UNESCAPED_UNICODE
                    ) ?: '{}';
                } elseif ($block->name === 'get_recommendations') {
                    $results = $this->getRecommendations((int) ($block->input['product_id'] ?? 0), $country);
                    $recommended = $results;
                    $content = json_encode($results, JSON_UNESCAPED_UNICODE) ?: '[]';
                } elseif ($block->name === 'create_order') {
                    if (!$userId) {
                        $result = ['success' => false, 'error' => 'Vous devez être connecté pour commander.'];
                    } else {
                        $result = $this->createOrder(
                            $userId,
                            (int) ($block->input['product_id'] ?? 0),
                            isset($block->input['variant_id']) ? (int) $block->input['variant_id'] : null,
                            (int) ($block->input['quantity'] ?? 1),
                            (string) ($block->input['delivery_destination'] ?? ''),
                            (string) ($block->input['client_phone'] ?? '')
                        );
                    }
                    $orderResult = $result;
                    $content = json_encode($result, JSON_UNESCAPED_UNICODE) ?: '{}';
                } else {
                    $content = json_encode(['error' => 'Outil inconnu']);
                }

                $toolResults[] = [
                    'type' => 'tool_result',
                    'toolUseID' => $block->id,
                    'content' => $content,
                ];
            }

            $messages[] = ['role' => 'assistant', 'content' => $response->content];
            $messages[] = ['role' => 'user', 'content' => $toolResults];

            $response = $this->client->messages->create(
                model: 'claude-opus-5',
                maxTokens: 1024,
                system: $system,
                tools: $tools,
                messages: $messages,
            );
        }

        $replyText = '';
        foreach ($response->content as $block) {
            if ($block->type === 'text') {
                $replyText .= $block->text;
            }
        }

        return [
            'reply'       => trim($replyText) ?: "Désolé, je n'ai pas pu traiter votre demande.",
            'products'    => $lastProducts,
            'recommended' => $recommended,
            'order'       => $orderResult,
        ];
    }
}
