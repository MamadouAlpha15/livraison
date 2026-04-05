<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;
use App\Models\OrderItem;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'name', 'description', 'price', 'image', 'is_active'
    ];

    public function shop() {
        return $this->belongsTo(Shop::class);  // Cette méthode indique qu'un produit appartient à une boutique (Shop).
    }

    public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}
}
