<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Order;

class DeliveryCompany extends Model
{
    protected $fillable = ['name','slug','description','logo','phone','email','active','user_id', 'approved', 'commission_percent', 'address', 'image', 'approved_at', 'country', 'currency'];

    public static function currencyForCountry(string $country): string
    {
        return match(strtoupper($country)) {
            'GN' => 'GNF',  // Guinée
            'SN' => 'XOF',  // Sénégal
            'ML' => 'XOF',  // Mali
            'CI' => 'XOF',  // Côte d'Ivoire
            'BF' => 'XOF',  // Burkina Faso
            'NE' => 'XOF',  // Niger
            'TG' => 'XOF',  // Togo
            'BJ' => 'XOF',  // Bénin
            'CM' => 'XAF',  // Cameroun
            'CD' => 'CDF',  // Congo RDC
            'CG' => 'XAF',  // Congo Brazza
            'GA' => 'XAF',  // Gabon
            'MR' => 'MRU',  // Mauritanie
            'MA' => 'MAD',  // Maroc
            'TN' => 'TND',  // Tunisie
            'DZ' => 'DZD',  // Algérie
            'EG' => 'EGP',  // Égypte
            'NG' => 'NGN',  // Nigeria
            'GH' => 'GHS',  // Ghana
            'KE' => 'KES',  // Kenya
            'TZ' => 'TZS',  // Tanzanie
            'RW' => 'RWF',  // Rwanda
            'ZA' => 'ZAR',  // Afrique du Sud
            'ET' => 'ETB',  // Éthiopie
            'FR' => 'EUR',  // France
            'DE' => 'EUR',  // Allemagne
            'ES' => 'EUR',  // Espagne
            'IT' => 'EUR',  // Italie
            'BE' => 'EUR',  // Belgique
            'PT' => 'EUR',  // Portugal
            'GB' => 'GBP',  // Royaume-Uni
            'US' => 'USD',  // États-Unis
            'CA' => 'CAD',  // Canada
            'AU' => 'AUD',  // Australie
            'JP' => 'JPY',  // Japon
            'CN' => 'CNY',  // Chine
            'BR' => 'BRL',  // Brésil
            'MX' => 'MXN',  // Mexique
            default => 'USD',
        };
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_company_id');
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
