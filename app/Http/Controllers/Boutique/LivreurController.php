<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class LivreurController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $shop = $user->shop ?? $user->assignedShop ?? null;

        abort_unless($shop, 403, 'Aucune boutique rattachée.');

        $today = Carbon::today()->toDateString();

        /* ── Tous les livreurs de cette boutique ── */
        $livreurs = User::where('shop_id', $shop->id)
            ->where(function ($q) {
                $q->where('role', 'livreur')
                  ->orWhere('role_in_shop', 'livreur');
            })
            ->latest()
            ->get();

        $livreurIds = $livreurs->pluck('id');

        /* ── Requêtes groupées — évite N+1 ── */
        $totauxLivraisons = Order::where('shop_id', $shop->id)
            ->where('status', 'livrée')
            ->whereIn('livreur_id', $livreurIds)
            ->selectRaw('livreur_id, COUNT(*) as total')
            ->groupBy('livreur_id')
            ->pluck('total', 'livreur_id');

        $livraisonsToday = Order::where('shop_id', $shop->id)
            ->where('status', 'livrée')
            ->whereIn('livreur_id', $livreurIds)
            ->whereDate('updated_at', $today)
            ->selectRaw('livreur_id, COUNT(*) as total')
            ->groupBy('livreur_id')
            ->pluck('total', 'livreur_id');

        /* ── Injecter les compteurs sur chaque livreur ── */
        $livreurs->each(function ($lv) use ($totauxLivraisons, $livraisonsToday) {
            $lv->nb_livraisons       = $totauxLivraisons->get($lv->id, 0);
            $lv->nb_livraisons_today = $livraisonsToday->get($lv->id, 0);
        });

        /* ── Stats rapides ── */
        $total     = $livreurs->count();
        $enLigne   = $livreurs->where('is_available', true)->count();
        $enCourse  = $livreurs->whereNotNull('current_order_id')->count();
        $horsligne = $total - $enLigne;

        /* ── Groupes pour l'affichage ── */
        $enLigneNow = $livreurs->where('is_available', true);
        $horsLigne  = $livreurs->where('is_available', false);

        $devise = $shop->currency ?? 'GNF';

        return view('boutique.livreurs.index', compact(
            'livreurs', 'shop', 'devise',
            'total', 'enLigne', 'enCourse', 'horsligne',
            'enLigneNow', 'horsLigne'
        ));
    }
}