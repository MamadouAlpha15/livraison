<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Page d'un produit construite avec le constructeur (sections et blocs).
 * « draft » est ce que le vendeur modifie ; « published » ce que voient les clients.
 * Les clients ne la voient que publiée ET activée (« is_enabled », désactivée par défaut) ; sinon le produit
 * garde la page produit actuelle (App\Support\ProductPageSettings).
 */
class ProductPage extends Model
{
    use HasFactory;

    /** Publications gardées pour revenir en arrière */
    public const VERSIONS_KEPT = 10;

    protected $fillable = ['product_id', 'shop_id', 'draft', 'published', 'published_at', 'is_enabled', 'version', 'page_template_id', 'updated_by'];

    protected $casts = [
        'draft'        => 'array',
        'published'    => 'array',
        'published_at' => 'datetime',
        'is_enabled'   => 'boolean',
        'version'      => 'integer',
    ];

    /** Désactivée par défaut, comme en base */
    protected $attributes = ['is_enabled' => false];

    protected static function booted(): void
    {
        // Les versions partent avec la page, même sans clés étrangères appliquées (tables MyISAM)
        static::deleting(fn (ProductPage $page) => $page->versions()->delete());
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function template()
    {
        return $this->belongsTo(PageTemplate::class, 'page_template_id');
    }

    public function versions()
    {
        return $this->hasMany(ProductPageVersion::class)->orderByDesc('version');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isPublished(): bool
    {
        return !empty($this->published);
    }

    /** Vue par les clients : publiée et activée par le vendeur (sinon : page produit habituelle) */
    public function isLive(): bool
    {
        return $this->is_enabled && $this->isPublished();
    }

    /** Le brouillon contient des changements pas encore publiés ? */
    public function hasUnpublishedChanges(): bool
    {
        return $this->draft !== null && $this->draft !== $this->published;
    }
}
