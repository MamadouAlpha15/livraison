<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Une publication passée d'une page produit (pour revenir en arrière). */
class ProductPageVersion extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['product_page_id', 'version', 'data', 'published_by'];

    protected $casts = ['data' => 'array', 'version' => 'integer'];

    public function page()
    {
        return $this->belongsTo(ProductPage::class, 'product_page_id');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
