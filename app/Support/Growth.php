<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShopMessage;
use App\Models\ShopVisit;
use App\Models\StorefrontClick;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * « Faire grandir ma vitrine » : couverture et logo, collections, sections automatiques,
 * brouillon et publication programmée, statistiques et score de qualité.
 * Toutes les données viennent de la boutique : aucun chiffre n'est inventé.
 */
class Growth
{
    public const PREVIEW_HOURS = 48;
    public const FEEDBACK_MAX = 30;
    public const DELIVERED_MIN = 5;   // « N commandes livrées » affiché à partir de 5
    public const RATING_MIN = 3;      // note affichée à partir de 3 avis
    public const RESPONSE_MIN_SAMPLES = 3;

    // ─────────────────────────────────────────────────────────── Publication, brouillon, programmation

    /**
     * Publie une vitrine (historique des 10 dernières versions). Le brouillon et sa programmation
     * sont effacés, sauf $keepDraft (changement publié tout de suite sans toucher au brouillon).
     */
    public static function publish(Shop $shop, array $config, string $accent, array $extra = [], bool $keepDraft = false): void
    {
        $history = array_slice(array_merge(
            [['at' => now()->toIso8601String(), 'config' => $config, 'accent' => $accent]],
            $shop->storefront_history ?? []
        ), 0, Storefront::HISTORY_MAX);

        $shop->update($extra + [
            'storefront'         => $config,
            'accent_color'       => $accent,
            'storefront_history' => $history,
        ] + ($keepDraft ? [] : ['storefront_draft' => null, 'storefront_publish_at' => null]));
    }

    /** Version en cours de travail : le brouillon s'il y en a un, sinon la version publiée. */
    public static function working(Shop $shop): array
    {
        $draft = $shop->storefront_draft;
        if (is_array($draft) && isset($draft['config'])) {
            return [Storefront::normalize($draft['config']), Storefront::isHex($draft['accent'] ?? null) ? strtoupper($draft['accent']) : self::accent($shop)];
        }
        return [Storefront::forShop($shop), self::accent($shop)];
    }

    public static function accent(Shop $shop): string
    {
        return Storefront::isHex($shop->accent_color) ? strtoupper($shop->accent_color) : '#1F7CF6';
    }

    /** État de la vitrine : publiée, brouillon en attente, ou brouillon programmé. */
    public static function status(Shop $shop): array
    {
        if ($shop->storefront_draft && $shop->storefront_publish_at) {
            return ['scheduled', 'Programmée', 'Publication le ' . $shop->storefront_publish_at->timezone('Africa/Conakry')->translatedFormat('j F à H:i')];
        }
        if ($shop->storefront_draft) return ['draft', 'Brouillon', 'Vos changements ne sont pas encore visibles des clients.'];
        return ['published', 'Publiée', 'Les clients voient la version actuelle.'];
    }

    /** Publie le brouillon programmé dont l'heure est passée (appelé à l'affichage et chaque minute). */
    public static function applyScheduled(Shop $shop): bool
    {
        if (!$shop->storefront_publish_at || !$shop->storefront_draft || $shop->storefront_publish_at->isFuture()) return false;
        [$config, $accent] = self::working($shop);
        self::publish($shop, $config, $accent);
        return true;
    }

    /**
     * Applique un changement à la version publiée ET au brouillon s'il existe
     * (couverture, logo, conversion : enregistrés et publiés tout de suite).
     */
    public static function applyEverywhere(Shop $shop, callable $change, ?string $accent = null, array $extra = []): void
    {
        $config = $change(Storefront::forShop($shop));
        self::publish($shop, Storefront::normalize($config), $accent ?? self::accent($shop), $extra, true);
    }

    // ─────────────────────────────────────────────────────────── Couverture et logo

    /** Diapos de la couverture, prêtes à afficher : ['img' => url] ou ['css' => dégradé]. */
    public static function slides(Shop $shop, array $config): array
    {
        return array_map(function ($s) use ($shop) {
            if ($s['src'] === 'photo' && $s['img']) {
                return ['src' => 'photo', 'img' => ImageOptimizer::url(str_replace('/medium/', '/large/', $s['img']), 'medium') ?? ImageOptimizer::url($s['img'])];
            }
            if ($s['src'] === 'product') {
                $url = self::productPhoto($shop, $s['pid']);
                if ($url) return ['src' => 'product', 'img' => $url];
            }
            return ['src' => $s['src'], 'css' => Storefront::COVER_GRADIENTS[$s['src']] ?? Storefront::COVER_GRADIENTS['default']];
        }, $config['cover']['slides']);
    }

    /** Photo de produit pour la couverture : le produit choisi, sinon le produit à la une, sinon le plus récent avec photo. */
    public static function productPhoto(Shop $shop, ?int $pid = null): ?string
    {
        $q = fn () => $shop->products()->where('is_active', true)->whereNotNull('image')->where('image', '!=', '');
        $p = ($pid ? $q()->whereKey($pid)->first() : null)
            ?? $q()->where('is_featured', true)->latest()->first()
            ?? $q()->latest()->first();
        return $p ? (ImageOptimizer::url($p->image, 'medium') ?? asset('storage/' . $p->image)) : null;
    }

    public static function initials(string $name): string
    {
        $words = preg_split('/\s+/u', trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $name)), -1, PREG_SPLIT_NO_EMPTY);
        $out = mb_strtoupper(mb_substr($words[0] ?? 'S', 0, 1) . (isset($words[1]) ? mb_substr($words[1], 0, 1) : ''));
        return $out !== '' ? $out : 'S';
    }

    public static function logoUrl(Shop $shop): ?string
    {
        return $shop->image ? (ImageOptimizer::url($shop->image, 'thumb') ?? asset('storage/' . $shop->image)) : null;
    }

    /** Image de partage (WhatsApp, Facebook) : la couverture photo si elle existe, sinon le logo. */
    public static function shareImage(Shop $shop, array $config): ?string
    {
        if ($config['cover']['on']) {
            foreach (self::slides($shop, $config) as $s) if (!empty($s['img'])) return $s['img'];
        }
        return $shop->image ? (ImageOptimizer::url($shop->image, 'medium') ?? asset('storage/' . $shop->image)) : null;
    }

    // ─────────────────────────────────────────────────────────── Collections et sections automatiques

    public static function collection(array $config, string $id): ?array
    {
        foreach ($config['collections'] as $c) if ($c['id'] === $id) return $c;
        return null;
    }

    /** Produits d'une collection (en ligne, de la boutique). $limit = null : tous. */
    public static function collectionProducts(Shop $shop, array $c, ?int $limit = 8): Collection
    {
        $q = $shop->products()->where('is_active', true);
        if ($c['kind'] === 'manual') {
            if (!$c['ids']) return collect();
            $found = $q->whereIn('id', $c['ids'])->get()->keyBy('id');
            $list = collect($c['ids'])->map(fn ($id) => $found->get($id))->filter()->values();
            return $limit ? $list->take($limit) : $list;
        }
        if ($c['kind'] === 'under') {
            // Prix actuel (promo flash comprise) sous le maximum : filtré après lecture, le prix courant est calculé
            $list = $q->orderByDesc('price')->get()->filter(fn ($p) => $p->current_price <= $c['max'])->sortByDesc('current_price')->values();
            return $limit ? $list->take($limit) : $list;
        }
        $q->where('created_at', '>=', now()->subDays(7))->latest();
        return $limit ? $q->take($limit)->get() : $q->get();
    }

    public static function collectionUrl(Shop $shop, array $c): string
    {
        return $shop->slug
            ? route('public.shops.slug.collection', [$shop->slug, $c['id']])
            : route('public.shops.collection', [$shop, $c['id']]);
    }

    /** « Les plus vus cette semaine » : vues réelles des fiches produit sur 7 jours. */
    public static function trending(Shop $shop, int $limit = 4): Collection
    {
        $views = ProductView::where('shop_id', $shop->id)->where('day', '>=', now()->subDays(6)->toDateString())
            ->selectRaw('product_id, SUM(views) as v')->groupBy('product_id')->orderByDesc('v')->take(20)->pluck('v', 'product_id');
        if ($views->isEmpty()) return collect();
        $products = $shop->products()->where('is_active', true)->whereIn('id', $views->keys())->get()->keyBy('id');
        return $views->keys()->map(fn ($id) => $products->get($id))->filter()->take($limit)->values();
    }

    /** « Bientôt en rupture » : stock réel de 1 ou 2 unités. */
    public static function lowStock(Shop $shop, int $limit = 8): Collection
    {
        return $shop->products()->where('is_active', true)->whereNotNull('stock')->whereBetween('stock', [1, 2])
            ->orderBy('stock')->latest()->take($limit)->get();
    }

    // ─────────────────────────────────────────────────────────── Preuves de confiance

    /**
     * Délai de réponse de la boutique aux messages des clients (30 derniers jours) :
     * ['minutes' => médiane, 'samples' => nombre de réponses], gardé en cache 6 h.
     */
    public static function responseTime(Shop $shop): array
    {
        return Cache::remember('shop_response_time_' . $shop->id, now()->addHours(6), function () use ($shop) {
            $team = User::where('shop_id', $shop->id)->pluck('id')->push($shop->user_id)->unique()->all();
            $msgs = ShopMessage::where('shop_id', $shop->id)->where('created_at', '>=', now()->subDays(30))
                ->orderBy('created_at')->get(['sender_id', 'receiver_id', 'created_at']);
            $waiting = [];
            $delays = [];
            foreach ($msgs as $m) {
                $fromTeam = in_array($m->sender_id, $team, true);
                $client = $fromTeam ? $m->receiver_id : $m->sender_id;
                if (!$fromTeam) { $waiting[$client] ??= $m->created_at; continue; }
                if (isset($waiting[$client])) {
                    $delays[] = $waiting[$client]->diffInMinutes($m->created_at);
                    unset($waiting[$client]);
                }
            }
            sort($delays);
            $n = count($delays);
            return ['minutes' => $n ? (int) $delays[intdiv($n, 2)] : null, 'samples' => $n];
        });
    }

    /** Preuves réelles affichables dans l'en-tête de la vitrine (selon les réglages du vendeur). */
    public static function proofs(Shop $shop, array $settings, int $delivered, int $reviewCount): array
    {
        $rt = self::responseTime($shop);
        return [
            'delivered' => $settings['proof_delivered'] && $delivered >= self::DELIVERED_MIN,
            'response'  => $settings['proof_response'] && $rt['samples'] >= self::RESPONSE_MIN_SAMPLES && $rt['minutes'] !== null && $rt['minutes'] <= 60,
            'rating'    => $reviewCount >= self::RATING_MIN,
        ];
    }

    // ─────────────────────────────────────────────────────────── Statistiques (onglet Qualité)

    /** Visites : aujourd'hui, 7 jours, 30 jours (glissants) et total. */
    public static function visits(Shop $shop): array
    {
        $r = ShopVisit::where('shop_id', $shop->id)->selectRaw(
            'SUM(CASE WHEN visited_on = ? THEN count ELSE 0 END) as today,
             SUM(CASE WHEN visited_on >= ? THEN count ELSE 0 END) as week,
             SUM(CASE WHEN visited_on >= ? THEN count ELSE 0 END) as month,
             SUM(count) as total',
            [today()->toDateString(), today()->subDays(6)->toDateString(), today()->subDays(29)->toDateString()]
        )->first();
        return ['today' => (int) ($r->today ?? 0), 'week' => (int) ($r->week ?? 0), 'month' => (int) ($r->month ?? 0), 'total' => (int) ($r->total ?? 0)];
    }

    /** Clics par section de la vitrine, 30 derniers jours : [libellé, clics], du plus cliqué au moins cliqué. */
    public static function sectionClicks(Shop $shop): array
    {
        return StorefrontClick::where('shop_id', $shop->id)->where('day', '>=', today()->subDays(29)->toDateString())
            ->selectRaw('block, SUM(clicks) as n')->groupBy('block')->orderByDesc('n')->get()
            ->map(fn ($r) => ['label' => $r->block === '__banner' ? 'En-tête' : (Storefront::BLOCKS[$r->block][0] ?? $r->block), 'clicks' => (int) $r->n])
            ->all();
    }

    /** Catégories classées par vues des fiches (30 jours), avec les commandes. */
    public static function categoryRanking(Shop $shop): array
    {
        $from = now()->subDays(29)->startOfDay();
        $views = ProductView::query()->join('products', 'products.id', '=', 'product_views.product_id')
            ->where('product_views.shop_id', $shop->id)->where('product_views.day', '>=', $from->toDateString())
            ->selectRaw("COALESCE(NULLIF(products.category, ''), 'Sans catégorie') as cat, SUM(product_views.views) as v")
            ->groupBy('cat')->pluck('v', 'cat');
        $orders = OrderItem::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.shop_id', $shop->id)->where('orders.created_at', '>=', $from)
            ->where('orders.status', '<>', Order::STATUS_ANNULEE)
            ->selectRaw("COALESCE(NULLIF(products.category, ''), 'Sans catégorie') as cat, COUNT(DISTINCT orders.id) as n")
            ->groupBy('cat')->pluck('n', 'cat');
        return $views->keys()->merge($orders->keys())->unique()
            ->map(fn ($c) => ['name' => $c, 'views' => (int) ($views[$c] ?? 0), 'orders' => (int) ($orders[$c] ?? 0)])
            ->sortByDesc(fn ($r) => [$r['views'], $r['orders']])->values()->all();
    }

    /** Produits vus au moins 5 fois en 30 jours, jamais commandés sur la période. */
    public static function seenNeverOrdered(Shop $shop): array
    {
        $from = now()->subDays(29)->startOfDay();
        $views = ProductView::where('shop_id', $shop->id)->where('day', '>=', $from->toDateString())
            ->selectRaw('product_id, SUM(views) as v')->groupBy('product_id')->havingRaw('SUM(views) >= 5')->pluck('v', 'product_id');
        if ($views->isEmpty()) return [];
        $ordered = OrderItem::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.shop_id', $shop->id)->where('orders.created_at', '>=', $from)
            ->whereIn('order_items.product_id', $views->keys())->distinct()->pluck('order_items.product_id')->all();
        return $shop->products()->whereIn('id', $views->keys()->diff($ordered))->get(['id', 'name', 'image'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'views' => (int) $views[$p->id], 'thumb' => $p->image ? ImageOptimizer::url($p->image, 'thumb') : null, 'edit' => route('products.edit', $p)])
            ->sortByDesc('views')->values()->take(8)->all();
    }

    // ─────────────────────────────────────────────────────────── Score de vitrine

    /**
     * Conseils vérifiables (aucun n'est inventé) : [clé, titre, détail, fait, lien, action].
     * Score = part des conseils faits, en pourcentage.
     */
    public static function quality(Shop $shop, array $config): array
    {
        $active = $shop->products()->where('is_active', true);
        $total = (clone $active)->count();
        $noPhoto = (clone $active)->where(fn ($q) => $q->whereNull('image')->orWhere('image', ''))->count();
        $noDesc = (clone $active)->where(fn ($q) => $q->whereNull('description')->orWhereRaw('CHAR_LENGTH(TRIM(description)) < 30'))->count();
        $cats = count(Storefront::categoryLinks($shop));
        $reviews = Review::where('vendeur_id', $shop->user_id)->count();
        $hours = collect($config['blocks'])->contains(fn ($b) => $b['type'] === 'hours' && $b['on']);
        $grow = fn ($tab) => route('boutique.grow', ['onglet' => $tab]);

        $tips = [
            ['cover', 'Une couverture pour la boutique', 'La première chose que voit le client.', $config['cover']['on'] && collect($config['cover']['slides'])->contains(fn ($s) => $s['src'] !== 'default'), $grow('couverture'), 'Choisir'],
            ['logo', 'Un logo', 'Il apparaît sur la vitrine, dans la recherche et sur WhatsApp.', (bool) $shop->image, $grow('couverture'), 'Ajouter'],
            ['catlinks', 'Des liens de catégories', 'Au moins 2 catégories : chacune a son lien à partager.', $cats >= 2, route('products.index'), 'Ranger mes produits'],
            ['photos', $noPhoto ? $noPhoto . ' produit' . ($noPhoto > 1 ? 's' : '') . ' sans photo' : 'Tous les produits ont une photo', 'Un produit sans photo est rarement commandé.', $total > 0 && $noPhoto === 0, route('products.index'), 'Ajouter les photos'],
            ['desc', $noDesc ? $noDesc . ' produit' . ($noDesc > 1 ? 's' : '') . ' sans vraie description' : 'Chaque produit a une description', 'Référence, taille, état, garantie : ce que le client demanderait au téléphone.', $total > 0 && $noDesc === 0, route('products.index'), 'Compléter'],
            ['hours', 'Votre adresse et vos horaires', 'Affiche « Ouvert maintenant » ou « Fermé » en haut de la vitrine.', $hours, route('boutique.storefront.edit'), 'Ajouter le bloc'],
            ['reviews', $reviews >= self::RATING_MIN ? 'Au moins ' . self::RATING_MIN . ' avis clients' : ($reviews ? $reviews . ' avis client' . ($reviews > 1 ? 's' : '') . ' sur ' . self::RATING_MIN : 'Aucun avis client pour l’instant'), 'La note s’affiche à partir de ' . self::RATING_MIN . ' avis de clients livrés.', $reviews >= self::RATING_MIN, route('boutique.orders.index'), 'Voir mes commandes'],
        ];
        $rows = array_map(fn ($t) => array_combine(['key', 'title', 'hint', 'done', 'url', 'cta'], $t), $tips);
        $done = count(array_filter($rows, fn ($r) => $r['done']));
        return ['score' => (int) round($done / count($rows) * 100), 'done' => $done, 'tips' => $rows];
    }

    // ─────────────────────────────────────────────────────────── Lien d'aperçu

    public static function previewActive(Shop $shop): bool
    {
        return $shop->preview_token && $shop->preview_expires_at && $shop->preview_expires_at->isFuture();
    }
}
