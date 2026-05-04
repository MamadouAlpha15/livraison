<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $fillable = ['delivery_company_id', 'name', 'description', 'price', 'estimated_minutes', 'color', 'active'];

    protected $casts = ['active' => 'boolean', 'price' => 'decimal:2'];

    public function company()
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id');
    }
}
