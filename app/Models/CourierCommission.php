<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierCommission extends Model
{
    // --- Constantes de statut (utilise celles-ci dans le code pour éviter les fautes) ---
    public const STATUS_EN_ATTENTE = 'en_attente';
    public const STATUS_PAYEE     = 'payée';

    // --- Fillable / casts ---
    protected $fillable = [
        'order_id', 'livreur_id', 'shop_id',
        'order_total', 'rate', 'amount',
        'status', 'paid_at', 'payout_ref', 'payout_note'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    // --- Relations ---
    public function order()   { return $this->belongsTo(Order::class); }
    public function livreur() { return $this->belongsTo(User::class, 'livreur_id'); }
    public function shop()    { return $this->belongsTo(Shop::class); }

    // --- Scopes en français (existants) ---
    public function scopeEnAttente($q) { return $q->where('status', self::STATUS_EN_ATTENTE); }
    public function scopePayee($q)     { return $q->where('status', self::STATUS_PAYEE); }

    // --- Scopes en anglais (alias) pour compatibilité avec le code existant ---
    public function scopePending($q) { return $this->scopeEnAttente($q); }
    public function scopePaid($q)    { return $this->scopePayee($q); }

    // --- Helpers pratiques ---
    public static function getStatusList(): array
    {
        return [
            self::STATUS_EN_ATTENTE,
            self::STATUS_PAYEE,
        ];
    }
}
