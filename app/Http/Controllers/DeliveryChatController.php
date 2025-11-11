<?php

// app/Http/Controllers/DeliveryChatController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryCompany;
use App\Models\DeliveryMessage;
use App\Models\Shop;

class DeliveryChatController extends Controller
{
    /**
     * Affiche l'UI de chat pour un shop (vendeur) vers une entreprise
     * $company est route-model-bound
     * $shopId optionnel : si pas fourni, on essaye de retrouver le shop depuis l'utilisateur connecté
     */
    public function show(Request $request, DeliveryCompany $company, $shopId = null)
    {
        $shop = null;
        if ($shopId) {
            $shop = Shop::find($shopId);
        } else {
            // si l'utilisateur est connecté et possède un shop -> on prend le premier
            $user = $request->user();
            if ($user) {
                $shop = Shop::where('user_id', $user->id)->first();
            }
        }

        // passe company et shop à la vue
        return view('company.chat', compact('company','shop'));
    }

    /**
     * Envoie un message : du vendeur (shop) vers l'entreprise (ou inverse selon l'UI)
     */
    public function send(Request $request, DeliveryCompany $company)
    {
        $request->validate([
            'shop_id' => 'nullable|exists:shops,id',
            'message' => 'required|string|max:2000',
        ]);

        // shop_id si le vendeur envoie
        $shopId = $request->input('shop_id') ?? null;

        // on va tenter de déterminer l'owner de l'entreprise (destinataire)
        // Hypothèse : delivery_companies a user_id qui est le propriétaire / compte associé
        $ownerUserId = $company->user_id ?? null;

        // Crée le message
        $msg = DeliveryMessage::create([
            'delivery_company_id' => $company->id,
            'shop_id' => $shopId,
            'from_user_id' => $request->user() ? $request->user()->id : null,
            'to_user_id' => $ownerUserId, // facultatif mais pratique
            'message' => $request->input('message'),
        ]);

        // Optionnel : tu peux ici déclencher une notification, envoyer email, broadcast etc.
        // event(new \App\Events\DeliveryMessageSent($msg));

        // renvoie l'objet fraîchement créé
        return response()->json(['ok' => true, 'message' => $msg]);
    }

    /**
     * Récupère les messages (polling).
     * Query params:
     *  - shop_id (optionnel) : si fourni, filtre sur ce shop (utile pour multi-shop)
     *  - after_id (optionnel) : prend seulement les messages id > after_id
     */
    public function messages(Request $request, DeliveryCompany $company)
    {
        $shopId = $request->query('shop_id', null);
        $lastId = (int) $request->query('after_id', 0);

        $query = $company->messages()->when($shopId, function($q) use ($shopId) {
            return $q->where('shop_id', $shopId);
        });

        if ($lastId) {
            $query->where('id', '>', $lastId);
        }

        $msgs = $query->orderBy('created_at', 'asc')->limit(200)->get();

        return response()->json(['messages' => $msgs]);
    }
}
       