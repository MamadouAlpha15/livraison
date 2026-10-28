<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Consommation de Shopio IA d'une boutique sur un mois (messages, tokens, coût en millionièmes de dollar). */
class AiUsage extends Model
{
    protected $fillable = ['shop_id', 'month', 'messages', 'handoffs', 'input_tokens', 'output_tokens', 'cost_micro_usd'];

    protected $casts = [
        'messages' => 'integer', 'handoffs' => 'integer', 'input_tokens' => 'integer',
        'output_tokens' => 'integer', 'cost_micro_usd' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function costUsd(): float
    {
        return $this->cost_micro_usd / 1_000_000;
    }
}
