<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // 🔹 Récupère toutes les boutiques activées
        $shops = Shop::where('is_approved', true)->latest()->paginate(12);

        return view('dashboards.client', compact('shops'));
    }
}
