<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DeliveryCompanyController extends Controller
{  // liste des entreprises de livraison
    public function index()
    {
        $companies = DeliveryCompany::where('active', true)->where('approved', true)->paginate(12);
        return view('delivery.index', compact('companies'));
    }


   public function create()
    {
        return view('delivery.create'); // le beau formulaire
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:190'],
            'description' => ['nullable','string','max:2000'],
            'phone' => ['nullable','string','max:60'],
            'email' => ['nullable','email','max:190'],
            'address' => ['nullable','string','max:255'],
            'commission_percent' => ['required','numeric','between:0,100'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        // stockage de l'image si presente
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('delivery_companies', 'public');
        }

        // lier la company à l'utilisateur connecté
        $data['user_id'] = $request->user()->id;
        $data['approved'] = false; // en attente d'approbation
        $data['active'] = true;
        $data['slug'] = Str::slug($data['name']) . '-' . time();

        $company = DeliveryCompany::create($data);

        // redirection DIRECTE vers le tableau de bord de l'entreprise,
        // l'utilisateur pourra accéder au dashboard et voir la page "en attente" si non approuvée
        return redirect()->route('company.dashboard')
                         ->with('success', 'Votre entreprise a été créée. Elle est en attente d’approbation par un administrateur. Vous pouvez accéder à votre tableau de bord en attendant.');
    }

    // afficher les détails d'une entreprise de livraison

     public function show(DeliveryCompany $company)
    {
        abort_unless($company->approved, 403, 'Entreprise non approuvée');
        return view('delivery.show', compact('company'));
    }

    // tableau de bord de l'entreprise de livraison
  public function dashboard(Request $request)
{
    $company = DeliveryCompany::where('user_id', $request->user()->id)->first();

    // 1) Si l'utilisateur n'a pas d'entreprise, on affiche la vue de création (ou une vue dédiée)
    if (! $company) {
        // Option A : afficher directement le formulaire de création (delivery.create)
        // return view('delivery.create');

        // Option B (recommandée) : afficher une page qui propose de créer + explique l'attente
        return view('delivery.create');
    }

    // 2) Si l'entreprise existe mais n'est pas approuvée -> page d'attente (dashboard restreint)
    if (! $company->approved) {
        return view('company.waiting_approval', compact('company'));
    }

    // 3) Entreprise approuvée -> dashboard complet
    $drivers = $company->drivers()->paginate(20);
    return view('company.dashboard', compact('company', 'drivers'));
}


    // Validation par superadmin
    public function approve(DeliveryCompany $company)
    {
        $company->update(['approved' => true, 'approved_at' => now()]);
        return back()->with('success', "L'entreprise {$company->name} a été approuvée.");
    }
}
