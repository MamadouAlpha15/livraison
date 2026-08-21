<?php

// ============================================================
// FICHIER : app/Services/VendorAnalyticsAssistantService.php
// RÔLE    : Assistant d'analyse des ventes (IA) pour le VENDEUR/propriétaire
//           de boutique — étape 5 du plan "assistant IA".
//           Contrairement à ShoppingAssistantService (côté client), celui-ci
//           répond aux questions sur les performances de SA PROPRE boutique :
//           chiffre d'affaires, meilleures ventes, stock faible.
//           Toutes les requêtes sont strictement filtrées par shop_id — un
//           vendeur ne peut jamais voir les chiffres d'une autre boutique.
// ============================================================

namespace App\Services;

use Anthropic\Client;
use Anthropic\Messages\ToolUseBlock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;

class VendorAnalyticsAssistantService
{
    private Client $client;

    private const MAX_TOOL_ITERATIONS = 4;

    public function __construct()
    {
        $this->client = new Client(apiKey: config('services.anthropic.key'));
    }

    private function tools(): array
    {
        return [
            [
                'name' => 'get_sales_summary',
                'description' => "Donne le chiffre d'affaires, le nombre de commandes livrées et le panier moyen de la "
                    . "boutique du vendeur, pour une période donnée. Utile pour comparer deux périodes.",
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => [
                            'type' => 'string',
                            'enum' => ['today', 'yesterday', 'week', 'last_week', 'month', 'last_month'],
                            'description' => 'Période à analyser',
                        ],
                    ],
                    'required' => ['period'],
                ],
            ],
            [
                'name' => 'get_top_products',
                'description' => "Donne les produits les plus vendus (en quantité) de la boutique du vendeur, "
                    . "sur une période donnée, avec le chiffre d'affaires généré par chacun.",
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => [
                            'type' => 'string',
                            'enum' => ['today', 'yesterday', 'week', 'last_week', 'month', 'last_month'],
                            'description' => 'Période à analyser',
                        ],
                    ],
                    'required' => ['period'],
                ],
            ],
            [
                'name' => 'get_low_stock_products',
                'description' => "Liste les produits de la boutique du vendeur dont le stock est faible "
                    . "(en dessous d'un seuil, 5 par défaut), pour repérer ce qu'il faut réapprovisionner.",
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'threshold' => ['type' => 'integer', 'description' => 'Seuil de stock faible (défaut 5)'],
                    ],
                ],
            ],
        ];
    }

    private function systemPrompt(string $shopName): string
    {
        return "Tu es l'assistant d'analyse des ventes de Shopio, pour le vendeur de la boutique \"{$shopName}\". "
            . "Tu l'aides à comprendre ses performances : chiffre d'affaires, produits les plus vendus, stock faible. "
            . "Réponds TOUJOURS en français, de façon claire et concise (3 à 5 phrases maximum, pas de listes à puces). "
            . "Les montants sont en GNF (francs guinéens). "
            . "Utilise TOUJOURS les outils pour obtenir des chiffres réels — ne cite JAMAIS un chiffre inventé. "
            . "Si le vendeur demande pourquoi ses ventes ont augmenté ou baissé, compare deux périodes avec "
            . "get_sales_summary (ex: 'week' vs 'last_week') et donne les chiffres factuels ; "
            . "ne propose JAMAIS de cause que tu ne peux pas vérifier avec les données (ne dis jamais des choses comme "
            . "'c'est probablement à cause de X') — contente-toi de décrire ce que montrent les chiffres.";
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function periodRange(string $period): array
    {
        $now = now();

        return match ($period) {
            'today'      => [$now->copy()->startOfDay(), $now->copy()],
            'yesterday'  => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'week'       => [$now->copy()->startOfWeek(), $now->copy()],
            'last_week'  => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            default      => [$now->copy()->startOfMonth(), $now->copy()], // 'month'
        };
    }

    public function getSalesSummary(int $shopId, string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        $orders = Order::where('shop_id', $shopId)
            ->where('status', Order::STATUS_LIVREE)
            ->whereNotNull('delivered_at')
            ->whereBetween('delivered_at', [$from, $to])
            ->get();

        return [
            'period'          => $period,
            'orders_count'    => $orders->count(),
            'revenue'         => (int) $orders->sum('total'),
            'avg_order_value' => $orders->count() ? (int) round($orders->avg('total')) : 0,
        ];
    }

    public function getTopProducts(int $shopId, string $period, int $limit = 5): array
    {
        [$from, $to] = $this->periodRange($period);

        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.shop_id', $shopId)
            ->where('orders.status', Order::STATUS_LIVREE)
            ->whereNotNull('orders.delivered_at')
            ->whereBetween('orders.delivered_at', [$from, $to])
            ->selectRaw('order_items.product_id, products.name, SUM(order_items.quantity) as qty, SUM(order_items.quantity * order_items.price) as revenue')
            ->groupBy('order_items.product_id', 'products.name')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'product_id'     => (int) $r->product_id,
            'name'           => $r->name,
            'quantity_sold'  => (int) $r->qty,
            'revenue'        => (int) $r->revenue,
        ])->values()->toArray();
    }

    public function getLowStockProducts(int $shopId, int $threshold = 5): array
    {
        $threshold = max(1, min($threshold, 50));

        return Product::where('shop_id', $shopId)
            ->where('is_active', true)
            ->whereNotNull('stock')
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->limit(10)
            ->get()
            ->map(fn (Product $p) => ['id' => $p->id, 'name' => $p->name, 'stock' => $p->stock])
            ->values()
            ->toArray();
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     * @return array{reply: string}
     */
    public function chat(array $history, string $userMessage, int $shopId, string $shopName): array
    {
        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $tools = $this->tools();
        $system = $this->systemPrompt($shopName);

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

                if ($block->name === 'get_sales_summary') {
                    $content = json_encode(
                        $this->getSalesSummary($shopId, (string) ($block->input['period'] ?? 'month')),
                        JSON_UNESCAPED_UNICODE
                    ) ?: '{}';
                } elseif ($block->name === 'get_top_products') {
                    $content = json_encode(
                        $this->getTopProducts($shopId, (string) ($block->input['period'] ?? 'month')),
                        JSON_UNESCAPED_UNICODE
                    ) ?: '[]';
                } elseif ($block->name === 'get_low_stock_products') {
                    $content = json_encode(
                        $this->getLowStockProducts($shopId, (int) ($block->input['threshold'] ?? 5)),
                        JSON_UNESCAPED_UNICODE
                    ) ?: '[]';
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
            'reply' => trim($replyText) ?: "Désolé, je n'ai pas pu traiter votre demande.",
        ];
    }
}
