<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokenImageReport extends Model
{
    protected $fillable = [
        'product_id', 'shop_id', 'missing_paths',
        'status', 'notified_at', 'notified_by', 'resolved_at',
    ];

    protected $casts = [
        'missing_paths' => 'array',
        'notified_at'   => 'datetime',
        'resolved_at'   => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
