<?php

namespace App\Models;

// ─────────────────────────────────────────────────────────────────────────────
// Modèle Subscription
// Représente un abonnement payant d'une boutique (plan Pro)
// ou d'une entreprise de livraison (plan Business).
// ─────────────────────────────────────────────────────────────────────────────

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Subscription extends Model
{
    protected $fillable = [
        'subscriber_type',
        'subscriber_id',
        'plan',
        'amount',
        'currency',
        'payment_method',
        'payment_reference',
        'status',
        'started_at',
        'expires_at',
        'gateway_response',
    ];

    protected $casts = [
        'started_at'       => 'datetime',
        'expires_at'       => 'datetime',
        'gateway_response' => 'array',
        'amount'           => 'integer',
    ];

    // ── Relation polymorphe : Shop ou DeliveryCompany ─────────────────────────
    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Scopes utiles ─────────────────────────────────────────────────────────

    // Retourne uniquement les abonnements actifs (non expirés)
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('expires_at', '>', now());
    }

    // Retourne les abonnements expirés (date passée mais encore marqués active)
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
                     ->where('expires_at', '<=', now());
    }

    // ── Helpers d'état ───────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at?->isFuture();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Nombre de jours restants avant expiration
    public function daysLeft(): int
    {
        if (!$this->expires_at || !$this->isActive()) return 0;
        return (int) now()->diffInDays($this->expires_at, false);
    }
}
