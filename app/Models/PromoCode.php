<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'shop_id', 'code', 'type', 'value',
        'min_purchase_amount', 'max_uses', 'uses_count',
        'expires_at', 'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    // Les codes sont insensibles à la casse pour le client
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Vérifie si le code est utilisable pour un sous-total donné
    public function isValidFor(float $subtotal): array
    {
        if (!$this->is_active) {
            return ['ok' => false, 'error' => 'Ce code promo n\'est plus actif.'];
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return ['ok' => false, 'error' => 'Ce code promo a expiré.'];
        }
        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return ['ok' => false, 'error' => 'Ce code promo a atteint son nombre maximal d\'utilisations.'];
        }
        if ($this->min_purchase_amount && $subtotal < $this->min_purchase_amount) {
            return ['ok' => false, 'error' => "Achat minimum de {$this->min_purchase_amount} requis pour ce code."];
        }

        return ['ok' => true, 'error' => null];
    }

    public function calculateDiscount(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * $this->value / 100
            : $this->value;

        return max(0, min($discount, $subtotal));
    }
}
