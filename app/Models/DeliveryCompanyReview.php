<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryCompanyReview extends Model
{
    protected $fillable = ['delivery_company_id', 'user_id', 'rating', 'comment'];

    public function company()
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
