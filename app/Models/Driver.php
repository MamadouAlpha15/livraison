<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = ['user_id','delivery_company_id','name','phone','email','password','must_change_password','photo','status'];

    protected $hidden = ['password'];

    protected $casts = ['must_change_password' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function company()
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
