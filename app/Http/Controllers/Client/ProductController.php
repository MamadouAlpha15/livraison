<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product)
    
    {
         $shop = $product->shop; // ✅ on récupère la boutique du produit
        return view('client.show', compact('product', 'shop'));
    }
}
