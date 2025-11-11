<?php

// app/Http/Controllers/Controller.php
namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // Renvoie l'id de la boutique rattachée au user (assignée ou possédée)
    protected function currentShopId(): ?int
    {
        $u = Auth::user();
        return $u?->shop_id ?: optional($u?->shop)->id;
    }
}
