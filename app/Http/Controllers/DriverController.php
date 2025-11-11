<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\DeliveryCompany;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    // Ajouter un livreur
    public function store(Request $request)
    {
        // Récupère l'entreprise liée à l'utilisateur
        $company = DeliveryCompany::where('user_id', $request->user()->id)->first();

        if (! $company) {
            return redirect()->route('company.dashboard')
                             ->with('error', "Vous n'avez pas d'entreprise. Créez-en une pour pouvoir ajouter des livreurs.");
        }

        if (! $company->approved) {
            return redirect()->route('company.dashboard')
                             ->with('error', "Votre entreprise est en attente d'approbation. Vous ne pouvez pas ajouter de livreurs pour le moment.");
        }

        $request->validate([
            'name' => 'required|string|max:190',
            'phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|max:2048',
            'vehicle' => 'nullable|string|max:100'
        ]);

        $path = $request->hasFile('photo') ? $request->file('photo')->store('drivers', 'public') : null;

        $company->drivers()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'vehicle' => $request->vehicle,
            'photo' => $path,
            'status' => 'available'
        ]);

        return back()->with('success', 'Livreur ajouté.');
    }

    public function update(Request $request, Driver $driver)
    {
        $company = DeliveryCompany::where('user_id', $request->user()->id)->first();

        if (! $company || $company->id !== $driver->delivery_company_id) {
            abort(403, "Action non autorisée");
        }

        if (! $company->approved) {
            return back()->with('error', "Votre entreprise est en attente d'approbation. Vous ne pouvez pas modifier de livreur.");
        }

        $request->validate([
            'name' => 'required|string|max:190',
            'phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|max:2048',
            'status' => 'nullable|in:available,busy,offline',
            'vehicle' => 'nullable|string|max:100'
        ]);

        if ($request->hasFile('photo')) {
            if ($driver->photo) Storage::disk('public')->delete($driver->photo);
            $driver->photo = $request->file('photo')->store('drivers','public');
        }

        $driver->update($request->only(['name','phone','status','vehicle']));

        return back()->with('success','Livreur mis à jour.');
    }

    public function destroy(Driver $driver)
    {
        $company = DeliveryCompany::where('user_id', auth()->id())->first();

        if (! $company || $company->id !== $driver->delivery_company_id) {
            abort(403, "Action non autorisée");
        }

        if ($driver->photo) Storage::disk('public')->delete($driver->photo);
        $driver->delete();

        return back()->with('success','Livreur supprimé.');
    }
}
