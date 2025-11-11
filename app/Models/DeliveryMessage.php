<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryMessage extends Model
{
    protected $fillable = ['delivery_company_id','shop_id','from_user_id','to_user_id','message','read'];

    public function company()
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id');
    }

    public function shop()
    {
        return $this->belongsTo(\App\Models\Shop::class); // un message peut être lié à une boutique
    }

    public function fromUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'from_user_id'); // un message provient d'un utilisateur
    }

    public function toUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'to_user_id'); // un message est envoyé à un utilisateur
    }
}
