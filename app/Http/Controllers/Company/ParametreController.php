<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Services\ImageOptimizer;
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
        return view('company.parametre.index', compact('company'));
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
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Veuillez entrer votre mot de passe actuel.',
            'password.min'              => 'Le nouveau mot de passe doit avoir au moins 8 caractères.',
            'password.confirmed'        => 'La confirmation ne correspond pas.',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.'])->withInput();
        }

        $user->update(['password' => Hash::make($request->input('password'))]);

        return back()->with('success_password', 'Mot de passe modifié avec succès.');
    }
}
