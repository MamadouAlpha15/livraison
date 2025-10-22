<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'shop_id',
        'role_in_shop',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
// app/Models/User.php

public function shop()
{
    return $this->belongsTo(Shop::class, 'shop_id');
}


public function orders()
{
    return $this->hasMany(\App\Models\Order::class, 'user_id');  // Un utilisateur peut avoir plusieurs commandes (Order).
}


public function subscribedShops()
{
    return $this->belongsToMany(\App\Models\Shop::class, 'shop_user')->withTimestamps(); // Un utilisateur peut s'abonner à plusieurs boutiques (Shop) via une table pivot shop_user.
}

// app/Models/User.php

public function assignedShop()
{
    return $this->belongsTo(\App\Models\Shop::class, 'shop_id');
}

public function isShopAdmin(): bool
{
    return $this->role === 'admin' || $this->role_in_shop === 'admin';
}

// app/Models/User.php




     
}
