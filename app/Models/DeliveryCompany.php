<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    // Membres ayant accès au panel de cette entreprise (hors propriétaire)
    public function members()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    // Résout l'entreprise pour un utilisateur : propriétaire ou membre
    public static function forUser(User $user): ?self
    {
        return static::where('user_id', $user->id)->first()
            ?? ($user->company_id ? static::find($user->company_id) : null);
    }

    public function zones()
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function reviews()
    {
        return $this->hasMany(DeliveryCompanyReview::class);
    }
}
