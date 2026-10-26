<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Adresse enregistrée par un client, proposée en un clic à la commande. */
class ClientAddress extends Model
{
    public const MAX_PER_USER = 10;

    protected $fillable = ['user_id', 'label', 'address', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Adresses d'un client, la principale en premier. */
    public static function forUser(int $userId)
    {
        return static::where('user_id', $userId)->orderByDesc('is_default')->latest('updated_at')->get();
    }

    /**
     * Enregistre l'adresse utilisée pour une commande si le client l'a demandé.
     * Une adresse déjà connue n'est pas dupliquée ; la première devient la principale.
     */
    public static function remember(User $user, ?string $address): void
    {
        $address = trim((string) $address);
        if ($address === '') return;

        $existing = static::where('user_id', $user->id)->get();
        if ($existing->contains(fn ($a) => mb_strtolower($a->address) === mb_strtolower($address))) return;
        if ($existing->count() >= self::MAX_PER_USER) return;

        static::create([
            'user_id'    => $user->id,
            'label'      => $existing->isEmpty() ? 'Domicile' : 'Adresse ' . ($existing->count() + 1),
            'address'    => mb_substr($address, 0, 255),
            'is_default' => $existing->isEmpty(),
        ]);
    }
}
