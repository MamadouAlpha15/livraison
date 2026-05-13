<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\CourierCommission;
use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    public function index()
    {
        $livreur   = Auth::user();
        $livreurId = $livreur->id;
        abort_unless($livreurId, 403);
        $shop  = $livreur->shop ?? $livreur->assignedShop;

        $driver          = Driver::with('company')->where('user_id', $livreurId)->first();
        $isCompanyDriver = $driver && $driver->delivery_company_id;
        $companyName     = $isCompanyDriver ? ($driver->company?->name ?? 'l\'entreprise') : null;

        // Devise : priorité à l'entreprise de livraison, sinon boutique, sinon GNF
        if ($isCompanyDriver && $driver->company) {
            $rawCurrency = $driver->company->currency
                        ?? \App\Models\DeliveryCompany::currencyForCountry($driver->company->country ?? '');
        } else {
            $rawCurrency = $shop?->currency ?? 'GNF';
        }
        $devise = match(strtoupper($rawCurrency)) {
            'XOF','XAF'  => 'FCFA',
            'EUR'        => '€',
            'USD'        => '$',
            'GBP'        => '£',
            default      => strtoupper($rawCurrency),
        };

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
            'pending', 'paid', 'pendingTotal', 'paidTotal', 'devise',
            'isCompanyDriver', 'companyName'
        ));
    }
}
