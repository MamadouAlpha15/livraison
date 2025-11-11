<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryCompany;

class DashboardController extends Controller
{
    public function index()
    {
        // Récupère toutes les entreprises en attente d'approbation
        $pendingCompanies = DeliveryCompany::where('approved', false)->paginate(20);

        return view('dashboards.SuperAdmin', compact('pendingCompanies'));
    }

    // Action pour approuver une entreprise
    public function approveCompany(DeliveryCompany $company)
    {
        $company->update([
            'approved' => true,
            'approved_at' => now()
        ]);

        return back()->with('success', "L'entreprise {$company->name} a été approuvée.");
    }
}
