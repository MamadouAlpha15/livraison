<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','product_id','product_variant_id','variant_name','quantity','price',
        'options', // choix faits sur la page produit : palier, lot, cadeau, champs personnalisés (constructeur de page)
    ];

    protected $casts = ['options' => 'array'];

    public function order() {
        return $this->belongsTo(Order::class); //une commande peut avoir un seul OrderItems
    }

    public function product() {
    return $this->belongsTo(Product::class);  //un produit peut avoir un seul OrderItems
    }

    public function variant() {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
