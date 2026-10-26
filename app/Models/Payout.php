<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Retrait demandé par un affilié : tout son solde disponible, versé par Mobile Money. */
class Payout extends Model
{
    use HasFactory;

    protected $fillable = ['affiliate_id', 'amount', 'method', 'phone', 'status', 'reference', 'note', 'processed_by', 'processed_at'];

    protected $casts = [
        'status'       => PayoutStatus::class,
        'amount'       => 'integer',
        'processed_at' => 'datetime',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function methodLabel(): string
    {
        return Affiliate::PAYOUT_METHODS[$this->method] ?? $this->method;
    }
}
