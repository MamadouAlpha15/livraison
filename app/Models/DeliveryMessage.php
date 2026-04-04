<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryMessage extends Model
{
    protected $fillable = [
        'delivery_company_id',
        'shop_id',
        'sender_id',
        'sender_role',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // Lien vers l'entreprise
    public function company()
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id');
    }

    // Lien vers la boutique
    public function shop()
    {
        return $this->belongsTo(\App\Models\Shop::class, 'shop_id');
    }

    // Auteur (user) éventuellement
    public function sender()
    {
        return $this->belongsTo(\App\Models\User::class, 'sender_id');
    }
}
