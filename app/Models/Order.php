<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CourierCommission;
use App\Models\DeliveryZone;

class Order extends Model
{
    use HasFactory;

    // Ajoute 'livreur_id' si tu fais des fill/update dessus
    protected $fillable = ['user_id','shop_id','total','status','ordonnance','livreur_id','current_lat','current_lng','last_ping_at',
    'image','delivery_fee','delivery_destination','client_phone','client_name','delivery_company_id','driver_id','delivery_zone_id','delivery_batch_id',
    'client_lat','client_lng','client_location_shared_at',
    'vendor_lat','vendor_lng','vendor_location_shared_at','delivered_at'];

    // Nom du client à afficher : compte connecté sinon nom saisi en tant qu'invité
    public function getDisplayNameAttribute(): string
    {
        return $this->client->name ?? $this->client_name ?? 'Client invité';
    }

    // Téléphone à afficher : compte connecté sinon téléphone saisi en tant qu'invité
    public function getDisplayPhoneAttribute(): ?string
    {
        return $this->client->phone ?? $this->client_phone;
    }

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
    'delivered_at'               => 'datetime',
    'last_ping_at'               => 'datetime',
    'current_lat'                => 'float',
    'current_lng'                => 'float',
    'total'                      => 'float',
    'client_lat'                 => 'float',
    'client_lng'                 => 'float',
    'client_location_shared_at'  => 'datetime',
    'vendor_lat'                 => 'float',
    'vendor_lng'                 => 'float',
    'vendor_location_shared_at'  => 'datetime',
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

        $normalized = $map[$value] ?? $value;
        $this->attributes['status'] = $normalized;

        // Fixe delivered_at une seule fois quand le statut passe à "livrée"
        if ($normalized === self::STATUS_LIVREE && empty($this->attributes['delivered_at'])) {
            $this->attributes['delivered_at'] = now();
        }
    }

    // Rattache une commande passée sans compte au compte que le client vient de créer/connecter
    // (appelé juste après l'inscription/connexion si l'utilisateur venait d'une page de suivi invité)
    public static function attachGuestOrderFromSession(User $user): void
    {
        if ($user->role !== 'client') {
            return;
        }

        $orderId = session()->pull('link_order_id');
        if (!$orderId) {
            return;
        }

        static::where('id', $orderId)->whereNull('user_id')->update(['user_id' => $user->id]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commission()
    {
        return $this->hasOne(CourierCommission::class);
    }

    // Entreprise de livraison externe assignée
    public function deliveryCompany()
    {
        return $this->belongsTo(DeliveryCompany::class);
    }

    // Chauffeur de l'entreprise de livraison assigné
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // Zone de livraison assignée
    public function deliveryZone()
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }
}
