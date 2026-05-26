<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    // Affiche la page de choix de plan pour l'entreprise de livraison
    public function upgrade(SubscriptionService $service)
    {
        $user    = Auth::user();
        // deliveryCompany() → company_id | ownedCompany() → propriétaire via user_id
        $company = $user->deliveryCompany ?? $user->ownedCompany;

        abort_unless($company, 404, 'Entreprise introuvable.');

        $currentPlan = $service->companyPlan($company);

        return view('company.subscription.upgrade', compact('company', 'currentPlan'));
    }
}
