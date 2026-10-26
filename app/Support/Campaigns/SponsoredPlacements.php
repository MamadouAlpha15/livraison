<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Emplacements sponsorisés côté acheteur (site et application) :
 *  - accueil, « Boutiques à la une » : 6 boutiques au plus, ordre aléatoire à chaque visite ;
 *  - haut d'une catégorie (Top catégorie) : les 2 premières places, en rotation ;
 *  - liste d'une catégorie (Boost produit) : 1 place sur 8, seulement dans la catégorie du produit ;
 *  - recherche (Boost produit, Top catégorie) : 1 place sur 6, seulement si le produit correspond.
 * Un produit en rupture ou désactivé n'est jamais affiché. Chaque élément affiché porte
 * sponsored_campaign_id (badge « Sponsorisé », lien ?c=) et compte un affichage.
 */
class SponsoredPlacements
{
    public const FEATURED_SHOPS     = 6;
    public const CATEGORY_TOP_SLOTS = 2;
    public const CATEGORY_EVERY     = 8;
    public const SEARCH_EVERY       = 6;

    /** Campagnes en cours par emplacement, lues une fois par requête (oubliées avec la requête). */
    private static ?\WeakMap $running = null;

    /** Lien d'un élément affiché, avec ?c=<campagne> s'il est sponsorisé. */
    public static function url(string $url, $model): string
    {
        $campaignId = $model?->sponsored_campaign_id ?? null;
        if (!$campaignId) {
            return $url;
        }
        $fragment = '';
        if (($hash = strpos($url, '#')) !== false) {
            [$url, $fragment] = [substr($url, 0, $hash), substr($url, $hash)];
        }
        return $url . (str_contains($url, '?') ? '&' : '?') . 'c=' . (int) $campaignId . $fragment;
    }

    /** Boutiques « à la une » payées (accueil). */
    public static function featuredShops(?string $country = null): Collection
    {
        $shops = self::running('home_featured')
            ->filter(fn (AdCampaign $c) => $c->shop && $c->shop->is_approved && (!$country || $c->shop->country === $country))
            ->unique('shop_id')
            ->shuffle()
            ->take(self::FEATURED_SHOPS)
            ->map(function (AdCampaign $c) {
                $shop = $c->shop;
                $shop->setAttribute('sponsored_campaign_id', $c->id);
                return $shop;
            })
            ->values();

        if ($shops->isNotEmpty()) {
            $shops = Shop::whereIn('id', $shops->pluck('id'))->withCount(['products' => fn ($q) => $q->where('is_active', true)])->get()
                ->each(fn (Shop $s) => $s->setAttribute('sponsored_campaign_id', $shops->firstWhere('id', $s->id)->sponsored_campaign_id))
                ->sortBy(fn (Shop $s) => $shops->search(fn ($x) => $x->id === $s->id))
                ->values();
            CampaignTracker::impressions($shops->pluck('sponsored_campaign_id'));
        }
        return $shops;
    }

    /**
     * Mélange les produits sponsorisés dans une page de résultats (recherche ou catégorie).
     * Sans recherche ni catégorie, la page est rendue telle quelle.
     */
    public static function blend(LengthAwarePaginator $page, ?string $search = null, ?string $category = null, ?string $country = null): LengthAwarePaginator
    {
        $search = trim((string) $search);
        $category = trim((string) $category);
        if ($search === '' && $category === '') {
            return $page;
        }

        $items = $page->getCollection()->values();
        if ($search !== '') {
            $ads = self::products(['category_search', 'category_top'], $country,
                fn (Product $p) => self::matches($p, $search) && ($category === '' || $p->category === $category))->shuffle();
            $top = collect();
            $every = self::SEARCH_EVERY;
        } else {
            $top = self::products(['category_top'], $country, fn (Product $p) => $p->category === $category)
                ->shuffle()->take(self::CATEGORY_TOP_SLOTS);
            $ads = self::products(['category_search'], $country, fn (Product $p) => $p->category === $category)
                ->reject(fn (Product $p) => $top->contains('id', $p->id))->shuffle();
            $every = self::CATEGORY_EVERY;
        }
        if ($top->isEmpty() && $ads->isEmpty()) {
            return $page;
        }

        $result = collect();
        $shown = collect();
        $show = function (Product $ad) use ($result, $shown) {
            $result->push($ad);
            $shown->push($ad);
        };

        $top->each($show);
        $queue = $ads->keyBy('id');
        $count = $top->count();
        foreach ($items->reject(fn ($p) => $top->contains('id', $p->id)) as $item) {
            // Produit sponsorisé déjà dans les résultats : il garde sa place, avec le badge (jamais en double)
            $queue->has($item->id) ? $show($queue->pull($item->id)) : $result->push($item);
            if (++$count % ($every - 1) === 0 && $queue->isNotEmpty()) {
                $show($queue->shift());
            }
        }
        // Peu de résultats (moins d'une ligne) : une place sponsorisée en fin de liste
        if ($queue->isNotEmpty() && $shown->count() === $top->count()) {
            $show($queue->shift());
        }

        CampaignTracker::impressions($shown->pluck('sponsored_campaign_id'));
        $page->setCollection($result);
        return $page;
    }

    /** Oublie les campagnes lues (tests, ou longues tâches qui servent plusieurs pages). */
    public static function reset(): void
    {
        self::$running = null;
    }

    /** Produits promus par les campagnes en cours de ces emplacements, encore vendables. */
    private static function products(array $placements, ?string $country, callable $filter): Collection
    {
        return collect($placements)
            ->flatMap(fn ($placement) => self::running($placement))
            ->flatMap(fn (AdCampaign $c) => $c->items->filter(fn ($i) => $i->product)->map(function ($i) use ($c) {
                $i->product->setAttribute('sponsored_campaign_id', $c->id);
                return $i->product;
            }))
            ->filter(fn (Product $p) => $p->shop && $p->shop->is_approved && (!$country || $p->shop->country === $country)
                && CampaignRules::isPromotable($p) && $filter($p))
            ->unique('id')
            ->values();
    }

    private static function running(string $placement): Collection
    {
        // Mémo lié à l'objet requête : une requête suivante (même processus) relit les campagnes
        $request = request();
        self::$running ??= new \WeakMap();
        $memo = self::$running[$request] ?? [];
        if (!isset($memo[$placement])) {
            $memo[$placement] = AdCampaign::query()
                ->where('status', AdCampaign::STATUS_EN_COURS)
                ->whereHas('package', fn ($q) => $q->where('placement', $placement))
                ->with(['shop', 'items.product.variants', 'items.product.shop:id,name,image,country,type,user_id,currency,is_approved'])
                ->get();
            self::$running[$request] = $memo;
        }
        return $memo[$placement];
    }

    /** Même règle que la recherche des listes : nom, description ou catégorie contient le texte. */
    private static function matches(Product $product, string $search): bool
    {
        $needle = Str::lower($search);
        return Str::contains(Str::lower(implode(' ', [$product->name, $product->description, $product->category])), $needle);
    }
}
