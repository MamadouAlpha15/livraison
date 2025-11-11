<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\CourierCommission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    public function index()
    {
        $livreurId = Auth::id();
        abort_unless($livreurId, 403);

        // Commissions en attente
        $pending = CourierCommission::with(['order', 'shop'])
            ->where('livreur_id', $livreurId)
            ->enAttente()   // scope correct
            ->latest()
            ->paginate(10, ['*'], 'en_attente_page');

        // Commissions payées
        $paid = CourierCommission::with(['order', 'shop'])
            ->where('livreur_id', $livreurId)
            ->payee()       // scope correct
            ->latest()
            ->paginate(10, ['*'], 'payee_page');

        $pendingTotal = CourierCommission::where('livreur_id', $livreurId)
            ->enAttente()
            ->sum('amount');

        $paidTotal = CourierCommission::where('livreur_id', $livreurId)
            ->payee()
            ->sum('amount');

        return view('livreur.commissions.index', compact(
            'pending', 'paid', 'pendingTotal', 'paidTotal'
        ));
    }
}
