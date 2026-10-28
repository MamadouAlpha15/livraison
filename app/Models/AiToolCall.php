<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Journal d'un appel d'outil de Shopio IA (paramètres sans données sensibles, résumé du résultat). */
class AiToolCall extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['shop_id', 'user_id', 'shop_message_id', 'tool', 'input', 'ok', 'summary', 'duration_ms'];

    protected $casts = ['input' => 'array', 'ok' => 'boolean', 'duration_ms' => 'integer'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
