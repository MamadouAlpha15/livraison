<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;
use App\Models\AdPackage;
use App\Models\Product;
use App\Models\Shop;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Règles de commande d'une campagne. validate() renvoie la liste des erreurs à montrer
 * au vendeur (vide = la campagne peut être créée).
 */
class CampaignRules
{
    /** Délai minimum avant le début d'une campagne réseaux sociaux (préparation des visuels). */
    public const SOCIAL_MIN_LEAD_DAYS = 2;

    /** Jusqu'où chercher une date libre quand une formule est complète. */
    private const NEXT_FREE_HORIZON_DAYS = 120;

    /**
     * @param int[] $productIds produits promus ; vide = toute la boutique
     * @return string[]
     */
    public static function validate(Shop $shop, AdPackage $package, array $productIds, CarbonInterface $startsOn, int $days): array
    {
        $errors = [];
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        $start = Carbon::parse($startsOn)->startOfDay();
        $end   = CampaignPricing::endDate($start, $days);

        if (!$shop->is_approved) {
            return ['Ta boutique doit être active pour commander une campagne.'];
        }
        if (!$package->is_active) {
            return ["La formule « {$package->name} » n'est plus proposée."];
        }
        if ($package->priceFor($days) === null) {
            $errors[] = "La formule « {$package->name} » ne propose pas {$days} jours.";
        }

        $active = $shop->adCampaigns()->whereIn('status', AdCampaign::ACTIVE_STATUSES)->count();
        if ($active >= AdCampaign::MAX_ACTIVE_PER_SHOP) {
            $errors[] = 'Tu as déjà ' . AdCampaign::MAX_ACTIVE_PER_SHOP . ' campagnes actives : attends la fin de l\'une d\'elles.';
        }

        // Quoi promouvoir
        if ($productIds === []) {
            if (!$package->allows_shop_wide) {
                $errors[] = 'Choisis au moins un produit à promouvoir.';
            }
        } elseif ($package->max_items === 0) {
            $errors[] = "La formule « {$package->name} » promeut toute la boutique, sans choix de produits.";
        } elseif (count($productIds) > $package->max_items) {
            $errors[] = "La formule « {$package->name} » promeut au plus {$package->max_items} produit" . ($package->max_items > 1 ? 's' : '') . '.';
        }

        $products = Product::with('variants')->whereIn('id', $productIds)->get()->keyBy('id');
        foreach ($productIds as $id) {
            $product = $products->get($id);
            if (!$product || (int) $product->shop_id !== (int) $shop->id) {
                $errors[] = "Ce produit n'appartient pas à ta boutique.";
                continue;
            }
            if (!self::isPromotable($product)) {
                $errors[] = "« {$product->name} » est en rupture de stock ou désactivé.";
            }
            if ($package->isBoost() && ($clash = self::boostClash($product, $start, $end))) {
                $errors[] = "« {$product->name} » est déjà mis en avant du {$clash->starts_on->format('d/m')} au {$clash->ends_on->format('d/m')}.";
            }
        }

        // Date de début
        $earliest = self::earliestStart($package);
        if ($start->lt($earliest)) {
            $errors[] = $package->isSocial()
                ? 'Une campagne réseaux sociaux commence au plus tôt le ' . $earliest->format('d/m/Y') . ', le temps de préparer les visuels.'
                : 'La date de début ne peut pas être dans le passé.';
        }

        // Places disponibles
        if ($package->max_actives !== null && $errors === []) {
            $categories = self::capacityCategories($package, $products->values());
            if (self::isFull($package, $start, $end, $categories)) {
                $next = self::nextFreeDate($package, $start, $days, $categories);
                $errors[] = "La formule « {$package->name} » est complète sur ces dates."
                    . ($next ? ' Prochaine date libre : ' . $next->format('d/m/Y') . '.' : '');
            }
        }

        return $errors;
    }

    /** La formule permet-elle de choisir les communes ? (Premium) Sinon la pub vise tout Conakry. */
    public static function allowsCommunes(AdPackage $package): bool
    {
        return ($package->details['targeting'] ?? null) === 'Par commune';
    }

    /**
     * Public d'une campagne réseaux sociaux, recalculé côté serveur à partir du formulaire :
     * valeurs par défaut pour ce qui manque, toutes les communes si la formule ne permet pas de choisir.
     */
    public static function targeting(AdPackage $package, array $input): array
    {
        $t = AdCampaign::DEFAULT_TARGETING;
        $communes = array_values(array_intersect(AdCampaign::COMMUNES, (array) ($input['communes'] ?? [])));
        if (self::allowsCommunes($package) && $communes !== []) {
            $t['communes'] = $communes;
        }
        $min = (int) ($input['age_min'] ?? $t['age_min']);
        $max = (int) ($input['age_max'] ?? $t['age_max']);
        $t['age_min'] = max(AdCampaign::AGE_MIN, min($min, AdCampaign::AGE_MAX));
        $t['age_max'] = max($t['age_min'], min($max, AdCampaign::AGE_MAX));
        $t['genre'] = array_key_exists($input['genre'] ?? '', AdCampaign::GENDERS) ? $input['genre'] : 'tous';
        return $t;
    }

    /** Un produit ne peut être promu que s'il est actif et disponible (lui ou l'une de ses variantes). */
    public static function isPromotable(Product $product): bool
    {
        if (!$product->is_active) {
            return false;
        }
        if ((int) $product->stock > 0) {
            return true;
        }
        $variants = $product->relationLoaded('variants') ? $product->variants : $product->variants()->get();
        return $variants->contains(fn ($v) => $v->is_active && (int) $v->stock > 0);
    }

    /** Première date de début autorisée pour la formule. */
    public static function earliestStart(AdPackage $package): Carbon
    {
        return today()->addDays($package->isSocial() ? self::SOCIAL_MIN_LEAD_DAYS : 0);
    }

    /** Autre mise en avant qui contient déjà ce produit sur une période qui chevauche [start, end]. */
    public static function boostClash(Product $product, CarbonInterface $start, CarbonInterface $end, ?int $ignoreCampaignId = null): ?AdCampaign
    {
        return AdCampaign::query()
            ->where('type', AdPackage::TYPE_BOOST)
            ->whereIn('status', AdCampaign::RESERVING_STATUSES)
            ->when($ignoreCampaignId, fn ($q) => $q->whereKeyNot($ignoreCampaignId))
            ->whereDate('starts_on', '<=', $end->toDateString())
            ->whereDate('ends_on', '>=', $start->toDateString())
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->orderBy('starts_on')
            ->first();
    }

    /**
     * Catégories sur lesquelles compter les places (formules limitées par catégorie),
     * [null] pour une formule limitée au global.
     */
    public static function capacityCategories(AdPackage $package, Collection $products): array
    {
        if ($package->capacity_scope !== 'category') {
            return [null];
        }
        $categories = $products->pluck('category')->filter()->unique()->values()->all();
        return $categories ?: [null];
    }

    /** Toutes les places sont-elles prises un des jours de [start, end] ? */
    public static function isFull(AdPackage $package, CarbonInterface $start, CarbonInterface $end, array $categories): bool
    {
        if ($package->max_actives === null) {
            return false;
        }
        $campaigns = self::reservingCampaigns($package, $start);
        foreach ($categories as $category) {
            if (self::peakLoad($campaigns, $category, $start, $end) >= $package->max_actives) {
                return true;
            }
        }
        return false;
    }

    /** Première date à partir de $from où une campagne de $days jours trouve une place. */
    public static function nextFreeDate(AdPackage $package, CarbonInterface $from, int $days, array $categories): ?Carbon
    {
        $campaigns = self::reservingCampaigns($package, $from);
        $start = Carbon::parse($from)->startOfDay();

        for ($i = 0; $i <= self::NEXT_FREE_HORIZON_DAYS; $i++, $start = $start->copy()->addDay()) {
            $end = CampaignPricing::endDate($start, $days);
            $free = collect($categories)->every(
                fn ($category) => self::peakLoad($campaigns, $category, $start, $end) < $package->max_actives
            );
            if ($free) {
                return $start;
            }
        }
        return null;
    }

    /** Campagnes de la formule qui occupent (ou réservent) une place à partir de $from. */
    private static function reservingCampaigns(AdPackage $package, CarbonInterface $from): Collection
    {
        return AdCampaign::with('items.product:id,category')
            ->where('ad_package_id', $package->id)
            ->whereIn('status', AdCampaign::RESERVING_STATUSES)
            ->whereDate('ends_on', '>=', $from->toDateString())
            ->get();
    }

    /** Plus grand nombre de campagnes diffusées le même jour entre $start et $end (dans la catégorie, si donnée). */
    private static function peakLoad(Collection $campaigns, ?string $category, CarbonInterface $start, CarbonInterface $end): int
    {
        $relevant = $category === null
            ? $campaigns
            : $campaigns->filter(fn (AdCampaign $c) => $c->items->contains(fn ($i) => $i->product?->category === $category));

        $peak = 0;
        for ($day = Carbon::parse($start)->startOfDay(); $day->lte($end); $day = $day->copy()->addDay()) {
            $load = $relevant->filter(fn (AdCampaign $c) => $c->starts_on->lte($day) && $c->ends_on->gte($day))->count();
            $peak = max($peak, $load);
        }
        return $peak;
    }
}
