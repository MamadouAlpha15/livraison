<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'type', 'description', 'address', 'phone', 'image',  'is_approved','email','status','commission_rate'
    ];

    public function owner() {
        return $this->belongsTo(User::class, 'user_id'); // Cette méthode indique qu'une boutique (Shop) appartient à un utilisateur (User) qui est le propriétaire.
    }

    public function products() {
    return $this->hasMany(Product::class); // Cette méthode indique qu'une boutique (Shop) peut avoir plusieurs produits (Product).
}

public function orders()
{
    return $this->hasMany(Order::class);
}

public function clients()
{
    return $this->belongsToMany(\App\Models\User::class, 'shop_user')->withTimestamps();  // Une boutique (Shop) peut avoir plusieurs clients (User) abonnés via une table pivot shop_user.
}
protected $casts = [
    'commission_rate' => 'float',
    'is_approved'     => 'boolean',
];

// 🟢 0.15 → 15 (pour l'affichage)
public function getCommissionRatePercentAttribute()
{
    return $this->commission_rate !== null ? round($this->commission_rate * 100, 2) : null;
}

}
