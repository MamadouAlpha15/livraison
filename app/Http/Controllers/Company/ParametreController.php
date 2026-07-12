<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Services\ImageOptimizer;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ParametreController extends Controller
{
    private function getCompany(): DeliveryCompany
    {
        $company = DeliveryCompany::forUser(auth()->user());
        abort_if(!$company, 404);
        return $company;
    }

    public function index()
    {
        $company = $this->getCompany();

        $svc          = app(SubscriptionService::class);
        $isBusiness   = $svc->companyPlan($company) === 'business';
        $maxDrivers   = SubscriptionService::COMP_FREE_MAX_DRIVERS;
        $maxZones     = SubscriptionService::COMP_FREE_MAX_ZONES;
        $maxOrders    = SubscriptionService::COMP_FREE_MAX_ORDERS;
        $totalDrivers = $company->drivers()->count();
        $totalZones   = $company->zones()->count();
        $usedOrders   = $svc->monthlyCompanyOrderCount($company);

        return view('company.parametre.index', compact(
            'company',
            'isBusiness', 'maxDrivers', 'maxZones', 'maxOrders',
            'totalDrivers', 'totalZones', 'usedOrders'
        ));
    }

    public function updateInfo(Request $request)
    {
        $company = $this->getCompany();

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'phone'       => ['nullable', 'string', 'max:60'],
            'email'       => ['nullable', 'email', 'max:190'],
            'address'     => ['nullable', 'string', 'max:255'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = ImageOptimizer::store($request->file('image'), 'delivery_companies');
        } else {
            unset($data['image']);
        }

        $company->update($data);

        return back()->with('success_info', 'Informations mises à jour avec succès.');
    }

    public function updateCountry(Request $request)
    {
        $company = $this->getCompany();

        $request->validate([
            'country' => ['required', 'string', 'size:2'],
        ]);

        $country  = strtoupper($request->input('country'));
        $currency = DeliveryCompany::currencyForCountry($country);

        // Mettre à jour l'entreprise et l'utilisateur propriétaire
        $company->update(['country' => $country, 'currency' => $currency]);
        auth()->user()->update(['country' => $country]);

        return back()->with('success_country', "Pays mis à jour → devise automatique : {$currency}");
    }

    public function updatePassword(Request $request)
    {
        $user     = auth()->user();
        $isGoogle = !empty($user->google_id);

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // Les comptes Google n'ont pas de mot de passe connu → pas de vérification requise
        if (!$isGoogle) {
            $rules['current_password'] = ['required'];
        }

        $request->validate($rules, [
            'current_password.required' => 'Veuillez entrer votre mot de passe actuel.',
            'password.min'              => 'Le nouveau mot de passe doit avoir au moins 8 caractères.',
            'password.confirmed'        => 'La confirmation ne correspond pas.',
        ]);

        if (!$isGoogle && !Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.'])->withInput();
        }

        $user->update(['password' => Hash::make($request->input('password'))]);

        $message = $isGoogle
            ? 'Mot de passe défini avec succès. Vous pouvez désormais vous connecter avec ce mot de passe ou continuer avec Google.'
            : 'Mot de passe modifié avec succès.';

        return back()->with('success_password', $message);
    }
}
