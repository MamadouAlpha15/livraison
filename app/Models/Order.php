<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','shop_id','total','status','ordonnance'];

    public function client() {
        return $this->belongsTo(User::class, 'user_id'); // Une commande appartient à un utilisateur (client)
    }

    public function shop() {
        return $this->belongsTo(Shop::class);  // Une commande appartient à une boutique (Shop).
    }

    public function items() {
        return $this->hasMany(OrderItem::class); // Une commande peut avoir plusieurs OrderItems
    }

    public function livreur() {
    return $this->belongsTo(User::class, 'livreur_id');  // Une commande peut appartenir à un livreur (User)
    
}

public function payment()
{
    return $this->hasOne(Payment::class); // Une commande a un seul paiement
}

public function review()
{
    return $this->hasOne(\App\Models\Review::class);
}


}
