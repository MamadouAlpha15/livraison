<?php

namespace App\PageBuilder\Commerce;

use App\Models\Product;
use App\Models\ProductVariant;
use App\PageBuilder\Blocks\CustomField;
use App\PageBuilder\Blocks\Offers;
use App\PageBuilder\Renderer;
use Illuminate\Support\Collection;

/**
 * Moteur d'offres de la section Produit, partagé par l'affichage et la commande :
 *   - paliers (quantité, remise en % ou en montant, cadeau) ;
 *   - produits complémentaires (remise facultative) ;
 *   - champs personnalisés.
 * Le prix de départ est toujours le prix réel du produit ou de son option. Le navigateur n'envoie
 * que des choix (palier, option, produits cochés) : quote() refait tout le calcul.
 */
class Checkout
{
    /** Remise en pourcentage maximale, quoi qu'ait saisi le vendeur */
    public const MAX_PERCENT = 90;

    public ?array $offers = null;
    public ?array $upsells = null;
    public ?array $quantity = null;
    public ?array $orderForm = null;
    /** @var array<string, array> réglages des champs personnalisés, par identifiant de bloc */
    public array $fields = [];

    private ?Collection $upsellProducts = null;
    private ?array $gifts = null;

    public function __construct(public Product $product) {}

    /** @param array $blocks blocs visibles d'une section, tels que Renderer::sections() les prépare */
    public static function fromBlocks(array $blocks, Product $product): self
    {
        $c = new self($product);
        foreach ($blocks as $b) {
            $type = $b['type']->type();
            match ($type) {
                'offers'       => $c->offers ??= $b['settings'],
                'upsells'      => $c->upsells ??= $b['settings'],
                'quantity'     => $c->quantity ??= $b['settings'],
                'order_form'   => $c->orderForm ??= $b['settings'],
                'custom_field' => $c->fields[$b['id']] = $b['settings'],
                default        => null,
            };
        }
        return $c;
    }

    /** Moteur de la section Produit d'une page (publiée ou brouillon), ou null s'il n'y a pas de formulaire */
    public static function fromPage(?array $page, Product $product): ?self
    {
        foreach (Renderer::sections($page ?? []) as $s) {
            if ($s['type']->type() === 'main-product') {
                $c = self::fromBlocks($s['blocks'], $product);
                return $c->orderForm ? $c : null;
            }
        }
        return null;
    }

    /** Prix unitaire réel : celui de l'option s'il est fixé, sinon le prix actuel (vente flash comprise) */
    public function unitPrice(?ProductVariant $variant = null): float
    {
        return $variant && $variant->price !== null ? (float) $variant->price : (float) $this->product->current_price;
    }

    /**
     * Paliers proposés pour un prix unitaire, par numéro (1 à 3) :
     * qty, label, badge, base (prix sans remise), total, saving, gift (Product disponible ou null)
     */
    public function tiers(float $unit): array
    {
        if (!$this->offers) {
            return [];
        }
        $out = [];
        for ($i = 1; $i <= Offers::TIERS; $i++) {
            if ($i > 1 && !$this->offers["tier{$i}_enabled"]) {
                continue;
            }
            $qty = max(1, (int) $this->offers["tier{$i}_qty"]);
            $base = (int) round($unit * $qty);
            $total = self::discounted($base, $this->offers["tier{$i}_discount"], (int) $this->offers["tier{$i}_value"]);
            $giftId = $this->offers["tier{$i}_gift"];
            $out[$i] = [
                'index'  => $i,
                'qty'    => $qty,
                'label'  => (string) $this->offers["tier{$i}_label"],
                'badge'  => $this->offers["tier{$i}_badge"],
                'base'   => $base,
                'total'  => $total,
                'saving' => $base - $total,
                'gift'   => $giftId ? ($this->gifts()[(int) $giftId] ?? null) : null,
            ];
        }
        return $out;
    }

    /** Prix d'un lot après remise (jamais négatif, remise en % plafonnée) */
    public static function discounted(int $base, string $type, int $value): int
    {
        return match ($type) {
            'percent' => (int) round($base * (100 - min(max($value, 0), self::MAX_PERCENT)) / 100),
            'amount'  => max(0, $base - max($value, 0)),
            default   => $base,
        };
    }

    public function defaultTier(array $tiers): ?int
    {
        if (!$tiers) {
            return null;
        }
        $wanted = (int) ($this->offers['selected'] ?? 1);
        return isset($tiers[$wanted]) ? $wanted : array_key_first($tiers);
    }

    /** Quantité maximale choisie au sélecteur (1 sans sélecteur ; les offres groupées fixent la quantité) */
    public function maxQuantity(): int
    {
        return $this->quantity && !$this->offers ? max(1, (int) $this->quantity['max']) : 1;
    }

    /** Produits complémentaires réellement proposés : en ligne, de la même boutique, en stock, dans l'ordre choisi */
    public function upsellProducts(): Collection
    {
        if ($this->upsellProducts !== null) {
            return $this->upsellProducts;
        }
        $ids = array_values(array_diff(array_map('intval', (array) ($this->upsells['products'] ?? [])), [$this->product->id]));
        if (!$ids) {
            return $this->upsellProducts = collect();
        }
        $found = self::simpleProducts($ids, $this->product->shop_id)->keyBy('id');
        return $this->upsellProducts = collect($ids)->map(fn ($id) => $found[$id] ?? null)->filter()->values();
    }

    public function upsellPrice(Product $p): int
    {
        return self::discounted((int) round((float) $p->current_price), 'percent', (int) ($this->upsells['discount'] ?? 0));
    }

    /** Cadeaux disponibles (produits sans option, en ligne, en stock), par identifiant */
    public function gifts(): array
    {
        if ($this->gifts !== null) {
            return $this->gifts;
        }
        $ids = [];
        for ($i = 1; $i <= Offers::TIERS; $i++) {
            if ($this->offers && $this->offers["tier{$i}_gift"]) {
                $ids[] = (int) $this->offers["tier{$i}_gift"];
            }
        }
        return $this->gifts = $ids ? self::simpleProducts($ids, $this->product->shop_id)->keyBy('id')->all() : [];
    }

    /** Produits de la boutique, en ligne, en stock et sans options (on ne peut pas choisir d'option pour eux) */
    private static function simpleProducts(array $ids, int $shopId): Collection
    {
        return Product::whereIn('id', $ids)->where('shop_id', $shopId)->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('stock')->orWhere('stock', '>', 0))
            ->whereDoesntHave('variants', fn ($q) => $q->where('is_active', true))->get();
    }

    /** Champs personnalisés : label, type, options, required, placeholder */
    public function customFields(): array
    {
        return collect($this->fields)->map(fn ($s) => [
            'label'       => (string) $s['label'],
            'type'        => (string) $s['field_type'],
            'options'     => CustomField::options($s),
            'required'    => (bool) $s['required'],
            'placeholder' => $s['placeholder'],
        ])->filter(fn ($f) => $f['type'] !== 'select' || $f['options'])->all();
    }

    /**
     * Panier calculé sur le serveur. $tier : palier choisi (si offres), $quantity : quantité (sinon),
     * $upsellIds : produits complémentaires cochés.
     * Renvoie ['lines' => [[product, variant, qty, total, label, options]], 'total' => int].
     *
     * @throws QuoteException choix impossible (palier inconnu, quantité hors limite, produit non proposé)
     */
    public function quote(?ProductVariant $variant, ?int $tier, int $quantity, array $upsellIds): array
    {
        $unit = $this->unitPrice($variant);
        $lines = [];

        if ($this->offers) {
            $tiers = $this->tiers($unit);
            $t = $tiers[$tier ?? 0] ?? throw new QuoteException('Cette offre n\'existe plus : recharge la page.');
            $lines[] = [
                'product' => $this->product, 'variant' => $variant, 'qty' => $t['qty'], 'total' => $t['total'],
                'label'   => trim(($variant ? $variant->name . ' · ' : '') . $t['label']),
                'options' => ['offer' => ['tier' => $t['index'], 'label' => $t['label'], 'qty' => $t['qty'], 'base' => $t['base'], 'saving' => $t['saving']]],
            ];
            if ($t['gift']) {
                $lines[] = [
                    'product' => $t['gift'], 'variant' => null, 'qty' => 1, 'total' => 0,
                    'label'   => 'Cadeau offert', 'options' => ['gift' => true, 'offer_tier' => $t['index']],
                ];
            }
        } else {
            if ($quantity < 1 || $quantity > $this->maxQuantity()) {
                throw new QuoteException('Quantité non disponible : choisis entre 1 et ' . $this->maxQuantity() . '.');
            }
            $lines[] = [
                'product' => $this->product, 'variant' => $variant, 'qty' => $quantity, 'total' => (int) round($unit * $quantity),
                'label'   => $variant?->name, 'options' => [],
            ];
        }

        $offered = $this->upsellProducts()->keyBy('id');
        foreach (array_unique(array_map('intval', $upsellIds)) as $id) {
            $p = $offered[$id] ?? throw new QuoteException('Un produit complémentaire n\'est plus disponible : recharge la page.');
            $lines[] = [
                'product' => $p, 'variant' => null, 'qty' => 1, 'total' => $this->upsellPrice($p),
                'label'   => 'Produit complémentaire', 'options' => ['upsell' => true, 'discount' => (int) $this->upsells['discount']],
            ];
        }

        return ['lines' => $lines, 'total' => array_sum(array_column($lines, 'total'))];
    }
}
