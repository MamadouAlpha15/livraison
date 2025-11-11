<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Ajoute 'livreur_id' si tu fais des fill/update dessus
    protected $fillable = ['user_id','shop_id','total','status','ordonnance','livreur_id','current_lat','current_lng','last_ping_at', 'image'];

    /* Relations */

    // Client qui a passé la commande
    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Boutique propriétaire de la commande
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // Items de la commande
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Livreur assigné
    public function livreur()
    {
        return $this->belongsTo(User::class, 'livreur_id');
    }

    // Paiement associé
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Avis / retour associé
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    /* Scopes */

    // Filtrer par boutique (utilisé dans ton contrôleur)
    public function scopeInShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    // app/Models/Order.php

  // ...tes autres colonnes
  


protected $casts = [
  'last_ping_at' => 'datetime',
];

/* Statuts normalisés en français */
    public const STATUS_EN_ATTENTE   = 'en_attente';
    public const STATUS_CONFIRMEE    = 'confirmée';
    public const STATUS_EN_LIVRAISON = 'en_livraison';
    public const STATUS_LIVREE       = 'livrée';
    public const STATUS_ANNULEE      = 'annulée';

    /* Mutator pour forcer français si ancien code envoie anglais */
    public function setStatusAttribute($value)
    {
        $map = [
            'pending'   => self::STATUS_EN_ATTENTE,
            'confirmed' => self::STATUS_CONFIRMEE,
            'delivering'=> self::STATUS_EN_LIVRAISON,
            'delivered' => self::STATUS_LIVREE,
            'canceled'  => self::STATUS_ANNULEE,
        ];

        $this->attributes['status'] = $map[$value] ?? $value;
    }
}
