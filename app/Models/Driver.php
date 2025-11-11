<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = ['delivery_company_id','name','phone','photo','status'];

    public function company()
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id'); // un chauffeur appartient à une entreprise de livraison
    }
}
