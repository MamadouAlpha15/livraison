<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Campagne publicitaire commandée par une boutique.
 * Les changements de statut passent tous par App\Support\Campaigns\CampaignStateMachine.
 */
class AdCampaign extends Model
{
    public const STATUS_A_PAYER           = 'a_payer';
    public const STATUS_PAYEE             = 'payee';
    public const STATUS_EN_PREPARATION    = 'en_preparation';
    public const STATUS_VISUELS_A_VALIDER = 'visuels_a_valider';
    public const STATUS_PROGRAMMEE        = 'programmee';
    public const STATUS_EN_COURS          = 'en_cours';
    public const STATUS_EN_PAUSE          = 'en_pause';
    public const STATUS_TERMINEE          = 'terminee';
    public const STATUS_REFUSEE           = 'refusee';
    public const STATUS_ANNULEE           = 'annulee';

    /** Campagnes payées et pas encore finies : elles comptent dans la limite de 5 par boutique. */
    public const ACTIVE_STATUSES = [
        self::STATUS_PAYEE, self::STATUS_EN_PREPARATION, self::STATUS_VISUELS_A_VALIDER,
        self::STATUS_PROGRAMMEE, self::STATUS_EN_COURS, self::STATUS_EN_PAUSE,
    ];

    /** Statuts qui bloquent un produit ou une place (une campagne à payer réserve aussi sa place). */
    public const RESERVING_STATUSES = [self::STATUS_A_PAYER, ...self::ACTIVE_STATUSES];

    public const MAX_ACTIVE_PER_SHOP = 5;

    /** Libellé et ton (couleur de la pastille) de chaque statut, pour l'espace vendeur et l'admin. */
    public const STATUS_LABELS = [
        self::STATUS_A_PAYER           => ['À payer', 'warn'],
        self::STATUS_PAYEE             => ['Payée', 'info'],
        self::STATUS_EN_PREPARATION    => ['En préparation', 'info'],
        self::STATUS_VISUELS_A_VALIDER => ['Visuels à valider', 'warn'],
        self::STATUS_PROGRAMMEE        => ['Programmée', 'info'],
        self::STATUS_EN_COURS          => ['En cours', 'ok'],
        self::STATUS_EN_PAUSE          => ['En pause', 'warn'],
        self::STATUS_TERMINEE          => ['Terminée', 'muted'],
        self::STATUS_REFUSEE           => ['Refusée', 'err'],
        self::STATUS_ANNULEE           => ['Annulée', 'muted'],
    ];

    public const OBJECTIVES = ['messages' => 'Recevoir des messages', 'visites' => 'Recevoir des visites', 'notoriete' => 'Se faire connaître'];
    public const COMMUNES   = ['Kaloum', 'Dixinn', 'Matam', 'Ratoma', 'Matoto'];
    public const GENDERS    = ['tous' => 'Tout le monde', 'femmes' => 'Femmes', 'hommes' => 'Hommes'];

    /** Public par défaut d'une campagne réseaux sociaux : tout Conakry, 18–45 ans. */
    public const DEFAULT_TARGETING = ['communes' => self::COMMUNES, 'age_min' => 18, 'age_max' => 45, 'genre' => 'tous'];
    public const AGE_MIN = 18;
    public const AGE_MAX = 65;

    protected $fillable = [
        'shop_id', 'ad_package_id', 'type', 'objective', 'targeting', 'starts_on', 'ends_on', 'duration_days', 'status',
        'price_gnf', 'ad_budget_gnf', 'fee_gnf', 'pause_reason', 'refusal_reason', 'external_ref', 'revision_count',
        'paid_at', 'creatives_sent_at', 'creatives_reminded_at', 'launched_at', 'paused_at', 'ending_soon_notified_at',
        'finished_at', 'cancelled_at', 'refused_at', 'created_by',
    ];

    protected $casts = [
        'targeting'               => 'array',
        'starts_on'               => 'date',
        'ends_on'                 => 'date',
        'duration_days'           => 'integer',
        'price_gnf'               => 'integer',
        'ad_budget_gnf'           => 'integer',
        'fee_gnf'                 => 'integer',
        'revision_count'          => 'integer',
        'paid_at'                 => 'datetime',
        'creatives_sent_at'       => 'datetime',
        'creatives_reminded_at'   => 'datetime',
        'launched_at'             => 'datetime',
        'paused_at'               => 'datetime',
        'ending_soon_notified_at' => 'datetime',
        'finished_at'             => 'datetime',
        'cancelled_at'            => 'datetime',
        'refused_at'              => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function package()
    {
        return $this->belongsTo(AdPackage::class, 'ad_package_id');
    }

    public function items()
    {
        return $this->hasMany(AdCampaignItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'ad_campaign_items')->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(AdCampaignPayment::class);
    }

    public function creatives()
    {
        return $this->hasMany(AdCampaignCreative::class);
    }

    public function stats()
    {
        return $this->hasMany(AdCampaignStat::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status][0] ?? $this->status;
    }

    public function statusTone(): string
    {
        return self::STATUS_LABELS[$this->status][1] ?? 'muted';
    }

    /** « Boost · Montre Rolex » : nom affiché dans les listes. */
    public function title(): string
    {
        $products = $this->items->map(fn ($i) => $i->product?->name)->filter();
        $what = $products->isEmpty() ? 'Toute la boutique' : $products->first() . ($products->count() > 1 ? ' + ' . ($products->count() - 1) : '');
        return ($this->package?->name ?? 'Campagne') . ' · ' . $what;
    }

    /** « Ratoma, Matoto · 18–45 ans · Femmes » ; « Tout Conakry » quand les 5 communes sont visées. */
    public function targetingLabel(): ?string
    {
        if (!$this->isSocial() || !$this->targeting) {
            return null;
        }
        $t = $this->targeting + self::DEFAULT_TARGETING;
        $communes = count(array_intersect(self::COMMUNES, (array) $t['communes'])) === count(self::COMMUNES)
            ? 'Tout Conakry' : implode(', ', (array) $t['communes']);
        return $communes . ' · ' . $t['age_min'] . '–' . $t['age_max'] . ' ans · ' . (self::GENDERS[$t['genre']] ?? 'Tout le monde');
    }

    public function objectiveLabel(): ?string
    {
        return self::OBJECTIVES[$this->objective] ?? null;
    }

    public function isBoost(): bool
    {
        return $this->type === AdPackage::TYPE_BOOST;
    }

    public function isSocial(): bool
    {
        return $this->type === AdPackage::TYPE_SOCIAL;
    }

    /** Toute la boutique est promue (aucun produit précis). */
    public function isShopWide(): bool
    {
        return $this->items->every(fn (AdCampaignItem $item) => $item->product_id === null);
    }
}
