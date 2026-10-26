<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Clic sur un lien ?ref=CODE. L'IP n'est jamais gardée en clair (ip_hash). */
class ReferralClick extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['affiliate_id', 'ip_hash', 'user_agent', 'landing_url'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
