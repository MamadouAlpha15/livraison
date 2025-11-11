<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryCompany extends Model
{
    protected $fillable = ['name','slug','description','logo','phone','email','active','user_id', 'approved', 'commission_percent', 'address', 'image', 'approved_at'];

    public function drivers()
    {
        return $this->hasMany(Driver::class); // peut avoir plusieurs chauffeurs
    }

    public function messages()
    {
        return $this->hasMany(DeliveryMessage::class); // peut avoir plusieurs messages
    }

     protected $casts = [
        'approved' => 'boolean',
        'active' => 'boolean',
    ];

    // Auto slug
    protected static function booted()
    {
        static::creating(function ($company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name) . '-' . time();
            }
        });
    }

   

    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
