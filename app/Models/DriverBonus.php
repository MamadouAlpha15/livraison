<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverBonus extends Model
{
    protected $fillable = ['user_id', 'bonus_date', 'deliveries_count', 'amount'];

    protected $casts = [
        'bonus_date' => 'date',
        'amount'     => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
