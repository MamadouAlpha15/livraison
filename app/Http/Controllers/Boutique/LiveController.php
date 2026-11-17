<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Support\LiveMaps;
use App\Support\LiveStats;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Boutique → En direct : visiteurs présents, ventes et commandes du jour, comportement des clients,
 * fil « En ce moment » et cartes (Afrique, Monde). La page se met à jour toute seule toutes les 10 s (data()).
 */
class LiveController extends Controller
{
    private function shop(): Shop
    {
        $u = Auth::user();
        $shop = $u->shop ?? Shop::where('user_id', $u->id)->first();
        abort_unless($shop, 404, 'Aucune boutique associée à ce compte.');
        return $shop;
    }

    public function index(): View
    {
        $shop = $this->shop();
        return view('boutique.live.index', ['shop' => $shop, 'stats' => LiveStats::for($shop), 'globe' => LiveMaps::globe()]);
    }

    public function data(): JsonResponse
    {
        return response()->json(LiveStats::for($this->shop()));
    }
}
