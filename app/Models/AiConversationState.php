<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Conversation client ↔ boutique : le vendeur a repris la main, ou l'IA l'a transférée au vendeur. */
class AiConversationState extends Model
{
    protected $fillable = ['shop_id', 'client_id', 'human_until', 'handed_off_at', 'handoff_reason'];

    protected $casts = ['human_until' => 'datetime', 'handed_off_at' => 'datetime'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /** L'IA doit-elle se taire dans cette conversation ? */
    public function pausesAi(): bool
    {
        return $this->handed_off_at !== null || ($this->human_until && $this->human_until->isFuture());
    }
}
