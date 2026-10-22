<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Question posée sur une fiche produit ; visible par tous une fois que la boutique a répondu. */
class ProductQuestion extends Model
{
    protected $fillable = ['product_id', 'shop_id', 'user_id', 'question', 'answer', 'answered_at', 'answered_by'];

    protected $casts = ['answered_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAnswered($query)
    {
        return $query->whereNotNull('answered_at');
    }

    public function scopeUnanswered($query)
    {
        return $query->whereNull('answered_at');
    }

    /** Prénom seul de l'auteur, affiché publiquement. */
    public function getAuthorFirstNameAttribute(): string
    {
        $name = trim((string) ($this->user->name ?? ''));
        return $name !== '' ? explode(' ', $name)[0] : 'Un client';
    }
}
