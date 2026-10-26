<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Carnet d'adresses du client : Maison, Bureau… proposées en un clic à la commande. */
class AddressController extends Controller
{
    public function index()
    {
        return view('client.addresses.index', [
            'addresses' => ClientAddress::forUser(Auth::id()),
            'max'       => ClientAddress::MAX_PER_USER,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'   => ['required', 'string', 'max:40'],
            'address' => ['required', 'string', 'min:4', 'max:255'],
        ], [
            'label.required'   => 'Donnez un nom à cette adresse (ex. Maison, Bureau).',
            'address.required' => "Indiquez l'adresse : quartier, rue, point de repère.",
            'address.min'      => 'Précisez un peu plus l\'adresse.',
        ]);

        $count = ClientAddress::where('user_id', Auth::id())->count();
        if ($count >= ClientAddress::MAX_PER_USER) {
            return back()->with('warning', 'Vous avez déjà ' . ClientAddress::MAX_PER_USER . ' adresses. Supprimez-en une pour en ajouter une autre.');
        }

        ClientAddress::create($data + ['user_id' => Auth::id(), 'is_default' => $count === 0]);

        return back()->with('success', 'Adresse enregistrée.');
    }

    public function makeDefault(ClientAddress $address)
    {
        abort_unless($address->user_id === Auth::id(), 403);

        ClientAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', '« ' . $address->label . ' » est maintenant votre adresse principale.');
    }

    public function destroy(ClientAddress $address)
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $wasDefault = $address->is_default;
        $address->delete();

        // L'adresse principale supprimée : la plus récente prend le relais
        if ($wasDefault) {
            ClientAddress::where('user_id', Auth::id())->latest('updated_at')->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Adresse supprimée.');
    }
}
