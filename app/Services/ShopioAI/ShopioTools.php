<?php

namespace App\Services\ShopioAI;

use App\Models\AiConversationState;
use App\Models\AiToolCall;
use App\Models\FaqEntry;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\ShopMessage;
use App\Services\ImageOptimizer;
use App\Services\PushService;
use App\Support\Money;
use App\Support\Storefront;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Outils de Shopio IA. L'IA ne lit jamais la base elle-même : elle demande un outil, qui vérifie la
 * permission (gates shopio-ai.*, ShopioAiToolPolicy), lit les données en direct et renvoie seulement
 * ce qui est utile. Chaque appel est journalisé (ai_tool_calls) sans données sensibles.
 */
class ShopioTools
{
    /** Paramètres gardés dans le journal (rien de personnel) */
    private const LOGGED_INPUT = ['texte', 'categorie', 'prix_max', 'produit_id', 'commande_id', 'sujet', 'prix', 'raison'];

    /** Définitions données à l'IA */
    public function definitions(): array
    {
        $obj = fn (array $props, array $required = []) => ['type' => 'object', 'properties' => (object) $props, 'required' => $required];
        return [
            ['name' => 'rechercher_produits', 'description' => 'Cherche des produits en ligne de cette boutique (mots-clés, catégorie, prix maximum en GNF). Renvoie prix, stock et lien.',
                'inputSchema' => $obj(['texte' => ['type' => 'string'], 'categorie' => ['type' => 'string'], 'prix_max' => ['type' => 'integer']])],
            ['name' => 'details_produit', 'description' => 'Détails d\'un produit de cette boutique : prix, prix barré, stock, variantes, photos, description.',
                'inputSchema' => $obj(['produit_id' => ['type' => 'integer']], ['produit_id'])],
            ['name' => 'infos_boutique', 'description' => 'Informations de la boutique : contact, adresse, horaires, livraison (zones, prix, délais), politique de retour, réponses fréquentes.',
                'inputSchema' => $obj([])],
            ['name' => 'statut_commande', 'description' => 'Commandes du client de la conversation (la commande indiquée, sinon ses 5 dernières) : statut, montant, articles.',
                'inputSchema' => $obj(['commande_id' => ['type' => 'integer']])],
            ['name' => 'suivi_livraison', 'description' => 'Suivi de livraison d\'une commande du client : statut, livreur assigné, créneau, date de livraison.',
                'inputSchema' => $obj(['commande_id' => ['type' => 'integer']], ['commande_id'])],
            ['name' => 'codes_promo_actifs', 'description' => 'Codes promo valables en ce moment dans cette boutique.',
                'inputSchema' => $obj([])],
            ['name' => 'faq_shopio', 'description' => 'FAQ de Shopio : paiement à la livraison, offres et négociation, compte, plans Pro et Business, parrainage, livraison, retours.',
                'inputSchema' => $obj(['sujet' => ['type' => 'string']])],
            ['name' => 'creer_offre', 'description' => 'Le client propose un prix pour un produit : transmet sa proposition au vendeur (qui accepte ou refuse). N\'accepte jamais toi-même un prix.',
                'inputSchema' => $obj(['produit_id' => ['type' => 'integer'], 'prix' => ['type' => 'integer']], ['produit_id', 'prix'])],
            ['name' => 'transferer_au_vendeur', 'description' => 'Passe la conversation au vendeur (humain) : client qui le demande, mécontent, litige, remboursement, ou information que tu n\'as pas.',
                'inputSchema' => $obj(['raison' => ['type' => 'string']], ['raison'])],
        ];
    }

    /** Exécute un outil, avec permission vérifiée et journal */
    public function execute(string $tool, array $input, AiContext $ctx): array
    {
        $started = microtime(true);
        try {
            $result = match ($tool) {
                'rechercher_produits'   => $this->searchProducts($input, $ctx),
                'details_produit'       => $this->productDetails($input, $ctx),
                'infos_boutique'        => $this->shopInfo($ctx),
                'statut_commande'       => $this->orderStatus($input, $ctx),
                'suivi_livraison'       => $this->deliveryTracking($input, $ctx),
                'codes_promo_actifs'    => $this->activePromoCodes($ctx),
                'faq_shopio'            => $this->faq($input),
                'creer_offre'           => $this->createOffer($input, $ctx),
                'transferer_au_vendeur' => $this->handOff($input, $ctx),
                default                 => ['erreur' => 'Outil inconnu.'],
            };
        } catch (\Throwable $e) {
            Log::warning("[Shopio IA] Outil {$tool} en erreur : " . $e->getMessage());
            $result = ['erreur' => 'Information indisponible pour le moment.'];
        }

        $ctx->toolResults[] = $result;
        AiToolCall::create([
            'shop_id'         => $ctx->shop->id,
            'user_id'         => $ctx->client?->id,
            'shop_message_id' => $ctx->messageId,
            'tool'            => $tool,
            'input'           => array_intersect_key($input, array_flip(self::LOGGED_INPUT)),
            'ok'              => !isset($result['erreur']),
            'summary'         => Str::limit($result['erreur'] ?? $this->summarize($tool, $result), 190, '…'),
            'duration_ms'     => (int) round((microtime(true) - $started) * 1000),
        ]);

        return $result;
    }

    private function summarize(string $tool, array $r): string
    {
        return match ($tool) {
            'rechercher_produits' => count($r['produits'] ?? []) . ' produit(s)',
            'statut_commande'     => count($r['commandes'] ?? []) . ' commande(s)',
            'codes_promo_actifs'  => count($r['codes'] ?? []) . ' code(s)',
            'faq_shopio'          => count($r['questions'] ?? []) . ' question(s)',
            default               => $r['resultat'] ?? ($r['nom'] ?? 'ok'),
        };
    }

    /* ── Produits ── */

    private function productRow(Product $p, AiContext $ctx): array
    {
        $stock = $p->stock === null ? 'non suivi' : ($p->stock > 0 ? $p->stock . ' en stock' : 'rupture de stock');
        return array_filter([
            'id'         => $p->id,
            'nom'        => $p->name,
            'prix'       => $this->money($p->current_price, $ctx),
            'prix_barre' => $p->original_price && $p->original_price > $p->current_price ? $this->money($p->original_price, $ctx) : null,
            'stock'      => $stock,
            'lien'       => route('client.products.show', $p),
        ], fn ($v) => $v !== null);
    }

    private function searchProducts(array $in, AiContext $ctx): array
    {
        $q = Product::where('shop_id', $ctx->shop->id)->where('is_active', true);
        if (filled($in['texte'] ?? null)) {
            $words = array_filter(preg_split('/\s+/', Str::lower(trim($in['texte']))), fn ($w) => mb_strlen($w) > 1);
            $q->where(function ($w) use ($words) {
                foreach ($words as $word) {
                    $w->orWhere('name', 'like', "%{$word}%")->orWhere('description', 'like', "%{$word}%")->orWhere('tags', 'like', "%{$word}%");
                }
            });
        }
        if (filled($in['categorie'] ?? null)) {
            $q->where('category', 'like', '%' . trim($in['categorie']) . '%');
        }
        $products = $q->latest()->limit(30)->get();
        if (!empty($in['prix_max'])) {
            $products = $products->filter(fn ($p) => $p->current_price <= (int) $in['prix_max']);
        }
        return ['produits' => $products->take(8)->map(fn ($p) => $this->productRow($p, $ctx))->values()->all()];
    }

    private function productDetails(array $in, AiContext $ctx): array
    {
        $p = Product::with('variants')->find((int) ($in['produit_id'] ?? 0));
        if (!$p || Gate::forUser($ctx->client)->denies('shopio-ai.view-product', [$p, $ctx->shop])) {
            return ['erreur' => 'Produit introuvable dans cette boutique.'];
        }
        $photos = collect([$p->image])->merge($p->gallery_array)->filter()->take(5)
            ->map(fn ($path) => ImageOptimizer::url($path))->filter()->values()->all();

        return $this->productRow($p, $ctx) + array_filter([
            'description' => $p->description ? Str::limit(strip_tags($p->description), 600) : null,
            'categorie'   => $p->category,
            'unite'       => $p->unit,
            'variantes'   => $p->variants->where('is_active', true)->map(fn ($v) => [
                'nom' => $v->name, 'prix' => $v->price ? $this->money($v->price, $ctx) : null,
                'stock' => $v->stock === null ? 'non suivi' : ($v->stock > 0 ? $v->stock . ' en stock' : 'rupture de stock'),
            ])->values()->all() ?: null,
            'photos' => $photos ?: null,
        ]);
    }

    /* ── Boutique ── */

    private function shopInfo(AiContext $ctx): array
    {
        $shop = $ctx->shop;
        $s = $ctx->settings;
        $unknown = 'non renseigné par la boutique';
        return [
            'nom'               => $shop->name,
            'adresse'           => $shop->address ?: $unknown,
            'telephone'         => $shop->phone ?: $unknown,
            'lien'              => Storefront::publicUrl($shop),
            'horaires'          => $s->opening_hours ?: $unknown,
            'livraison'         => $s->delivery_info ?: $unknown,
            'retours'           => $s->return_policy ?: $unknown,
            'reponses_frequentes' => $s->faq ?: $unknown,
            'paiement'          => 'En espèces au livreur, à la livraison (par défaut sur Shopio).',
        ];
    }

    private function activePromoCodes(AiContext $ctx): array
    {
        $codes = PromoCode::where('shop_id', $ctx->shop->id)->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get()->filter(fn ($c) => $c->max_uses === null || $c->uses_count < $c->max_uses);

        return ['codes' => $codes->map(fn ($c) => array_filter([
            'code'           => $c->code,
            'reduction'      => $c->type === 'percent' ? ((int) $c->value) . ' %' : $this->money($c->value, $ctx),
            'achat_minimum'  => $c->min_purchase_amount ? $this->money($c->min_purchase_amount, $ctx) : null,
            'valable_jusqu_au' => $c->expires_at?->format('d/m/Y'),
        ]))->values()->all()];
    }

    private function faq(array $in): array
    {
        $topic = Str::lower(trim((string) ($in['sujet'] ?? '')));
        $entries = FaqEntry::where('is_active', true)->orderBy('position')->get();
        if ($topic !== '') {
            $matching = $entries->filter(fn ($e) => str_contains(Str::lower($e->category . ' ' . $e->question . ' ' . $e->answer), $topic)
                || collect(preg_split('/\s+/', $topic))->contains(fn ($w) => mb_strlen($w) > 3 && str_contains(Str::lower($e->question . ' ' . $e->answer), $w)));
            $entries = $matching->isNotEmpty() ? $matching : $entries;
        }
        return ['questions' => $entries->take(15)->map(fn ($e) => ['question' => $e->question, 'reponse' => $e->answer])->values()->all()];
    }

    /* ── Commandes du client ── */

    private function orderRow(Order $o, AiContext $ctx): array
    {
        return [
            'numero'   => $o->id,
            'date'     => $o->created_at->format('d/m/Y'),
            'boutique' => $o->shop?->name,
            'statut'   => $o->status,
            'montant'  => $this->money($o->total, $ctx),
            'articles' => $o->items->map(fn ($i) => ($i->quantity ?? 1) . ' × ' . ($i->product?->name ?? 'article'))->values()->all(),
        ];
    }

    private function orderStatus(array $in, AiContext $ctx): array
    {
        if (!$ctx->client) {
            return ['erreur' => 'Le client doit être connecté pour voir ses commandes.'];
        }
        if (!empty($in['commande_id'])) {
            $o = Order::with('items.product', 'shop')->find((int) $in['commande_id']);
            if (!$o || Gate::forUser($ctx->client)->denies('shopio-ai.view-order', $o)) {
                return ['erreur' => 'Aucune commande de ce client avec ce numéro.'];
            }
            return ['commandes' => [$this->orderRow($o, $ctx)]];
        }
        $orders = Order::with('items.product', 'shop')->where('user_id', $ctx->client->id)->latest()->limit(5)->get();
        return ['commandes' => $orders->map(fn ($o) => $this->orderRow($o, $ctx))->all()];
    }

    private function deliveryTracking(array $in, AiContext $ctx): array
    {
        $o = $ctx->client ? Order::with('livreur:id,name', 'driver:id,name', 'deliveryCompany:id,name')->find((int) ($in['commande_id'] ?? 0)) : null;
        if (!$o || Gate::forUser($ctx->client)->denies('shopio-ai.view-order', $o)) {
            return ['erreur' => 'Aucune commande de ce client avec ce numéro.'];
        }
        return array_filter([
            'numero'        => $o->id,
            'statut'        => $o->status,
            'livreur'       => $o->driver?->name ?? $o->livreur?->name ?? 'pas encore assigné',
            'entreprise'    => $o->deliveryCompany?->name,
            'creneau'       => $o->delivery_slot,
            'livree_le'     => $o->delivered_at?->format('d/m/Y H:i'),
            'estimation'    => $o->delivery_slot ? null : 'pas d\'estimation enregistrée',
        ]);
    }

    /* ── Actions ── */

    private function createOffer(array $in, AiContext $ctx): array
    {
        $p = Product::find((int) ($in['produit_id'] ?? 0));
        $price = (int) ($in['prix'] ?? 0);
        if (!$p || Gate::forUser($ctx->client)->denies('shopio-ai.create-offer', [$p, $ctx->shop])) {
            return ['erreur' => $ctx->client ? 'Produit introuvable dans cette boutique.' : 'Le client doit être connecté pour proposer un prix.'];
        }
        $current = (float) $p->current_price;
        if ($price <= 0 || $price >= $current) {
            return ['erreur' => 'Le prix proposé doit être inférieur au prix affiché (' . $this->money($current, $ctx) . ').'];
        }

        $owner = $ctx->shop->user_id;
        $pct = $ctx->settings->max_discount_percent;
        // Le vendeur a fixé une remise maximale : un prix au-dessus du minimum devient une offre à confirmer
        if ($pct !== null && $price >= (int) ceil($current * (100 - $pct) / 100)) {
            ShopMessage::create([
                'shop_id' => $ctx->shop->id, 'product_id' => $p->id, 'sender_id' => $owner, 'receiver_id' => $ctx->client->id,
                'type' => ShopMessage::TYPE_PRICE_OFFER, 'proposed_price' => $price, 'proposal_status' => ShopMessage::STATUS_PENDING, 'is_ai' => true,
                'body' => "🏷️ D'accord pour {$p->name} à " . $this->money($price, $ctx) . '. Clique sur « Confirmer » pour valider ta commande.',
            ]);
            return ['resultat' => 'offre_envoyee', 'prix' => $this->money($price, $ctx),
                'explication' => 'Le prix est dans la limite fixée par le vendeur : une offre à confirmer a été envoyée au client.'];
        }

        ShopMessage::create([
            'shop_id' => $ctx->shop->id, 'product_id' => $p->id, 'sender_id' => $ctx->client->id, 'receiver_id' => $owner,
            'type' => ShopMessage::TYPE_PRICE_PROPOSAL, 'proposed_price' => $price, 'proposal_status' => ShopMessage::STATUS_PENDING,
            'body' => '💬 Proposition de prix pour ' . $p->name . ' : ' . $this->money($price, $ctx),
        ]);
        $this->notifyVendor($ctx, 'Proposition de prix', $ctx->client->name . ' propose ' . $this->money($price, $ctx) . ' pour ' . $p->name . '.');
        return ['resultat' => 'proposition_transmise', 'prix' => $this->money($price, $ctx),
            'explication' => 'La proposition est transmise au vendeur, qui l\'accepte, la refuse ou fait une contre-offre.'];
    }

    private function handOff(array $in, AiContext $ctx): array
    {
        if ($ctx->client) {
            AiConversationState::updateOrCreate(
                ['shop_id' => $ctx->shop->id, 'client_id' => $ctx->client->id],
                ['handed_off_at' => now(), 'handoff_reason' => Str::limit((string) ($in['raison'] ?? ''), 190, '')]
            );
        }
        $ctx->handedOff = true;
        $this->notifyVendor($ctx, 'Shopio IA te passe la main', ($ctx->client?->name ?? 'Un client') . ' : ' . Str::limit((string) ($in['raison'] ?? 'demande à parler au vendeur'), 120));
        return ['resultat' => 'transfere', 'explication' => 'Le vendeur est prévenu et répondra lui-même.'];
    }

    private function notifyVendor(AiContext $ctx, string $title, string $body): void
    {
        try {
            $owner = $ctx->shop->owner;
            if ($owner) {
                app(PushService::class)->sendToUser($owner, $title, $body, 1, '/boutique/messages');
            }
        } catch (\Throwable $e) {
            // la notification ne doit jamais bloquer la réponse
        }
    }

    private function money($amount, AiContext $ctx): string
    {
        $currency = $ctx->shop->currency ?: 'GNF';
        return $currency === 'GNF' ? Money::gnf($amount) : number_format((float) $amount, 0, ',', "\u{00A0}") . "\u{00A0}" . $currency;
    }
}
