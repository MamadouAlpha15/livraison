<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCompany;
use App\Models\DeliveryMessage;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DeliveryChatController extends Controller
{
    /**
     * Affiche la page de chat (blade). `init` : message initial.
     */
    public function show(DeliveryCompany $company, Request $request)
    {
        // accès seulement si entreprise approuvée (ou propriétaire)
        if (!$company->approved && $company->user_id !== $request->user()->id) {
            abort(403, 'Entreprise non disponible.');
        }

        $user = $request->user();

        // déterminer shopId du vendeur connecté (si vendeur)
        $shopId = $request->query('shop') ?? $request->query('shop_id') ?? null;
        if (! $shopId) {
            if (isset($user->shop_id) && $user->shop_id) $shopId = $user->shop_id;
            elseif (method_exists($user, 'shop')) {
                $rel = $user->shop()->first();
                $shopId = $rel ? $rel->id : null;
            }
        }

        // sécurité : si pas shopId et pas propriétaire => interdit
        if (!$shopId && $company->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Accès réservé aux vendeurs ou à l’entreprise.');
        }

        $init = $request->query('init', '');

        // charger quelques messages pour rendu initial (optionnel)
        $messages = DeliveryMessage::where('delivery_company_id', $company->id)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->orderBy('created_at')
            ->get();

        return view('company.chat', [
            'company' => $company,
            'shopId' => $shopId,
            'init' => $init,
            'messages' => $messages,
        ]);
    }

    /**
     * Envoie d'un message (AJAX). Body: { message:, shop_id || shop: }
     */
    public function send(DeliveryCompany $company, Request $request)
    {
        $user = $request->user();

        $data = $request->all();
        // accepter shop ou shop_id dans payload
        $shopId = $data['shop_id'] ?? $data['shop'] ?? null;

        $validator = Validator::make($data, [
            'message' => 'required|string|max:2000',
            'shop_id'  => 'nullable|integer|exists:shops,id',
            'shop'     => 'nullable|integer|exists:shops,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        // autorisation : company owner OR shop owner (vendeur) OR admin
        $isCompanyOwner = $company->user_id === $user->id;
        $isAdmin = in_array($user->role, ['admin','superadmin']);
        $isShopOwner = false;
        if ($shopId) {
            // vérifier si l'utilisateur est lié à la boutique
            if (isset($user->shop_id) && $user->shop_id == $shopId) $isShopOwner = true;
            elseif (method_exists($user,'shop') && optional($user->shop()->first())->id == $shopId) $isShopOwner = true;
            // role vendeur could also be accepted if linked differently
        }

        if (!($isCompanyOwner || $isShopOwner || $isAdmin)) {
            return response()->json(['ok' => false, 'error' => 'Non autorisé'], 403);
        }

        $senderRole = $isCompanyOwner ? 'company' : ($isShopOwner ? 'shop' : ($isAdmin ? 'admin' : 'user'));

        $msg = DeliveryMessage::create([
            'delivery_company_id' => $company->id,
            'shop_id' => $shopId,
            'sender_id' => $user->id,
            'sender_role' => $senderRole,
            'message' => $data['message'],
        ]);

        // répondre avec la forme que le front attend (body + from_type + sender_name)
        return response()->json([
            'ok' => true,
            'message' => [
                'id' => $msg->id,
                'body' => $msg->message,
                'from_type' => $msg->sender_role,
                'sender_name' => $msg->sender ? $msg->sender->name ?? null : ($msg->shop?->name ?? $company->name),
                'created_at' => $msg->created_at->toDateTimeString(),
            ],
            'last' => $msg->created_at->toDateTimeString(),
        ]);
    }

    /**
     * Récupère les messages JSON. Query params : shop || shop_id, after (ISO)
     */
    public function messages(DeliveryCompany $company, Request $request)
    {
        $user = $request->user();

        // accepter shop ou shop_id
        $shopId = $request->query('shop') ?? $request->query('shop_id') ?? null;
        if (! $shopId && isset($user->shop_id)) $shopId = $user->shop_id;

        // autorisation : propriétaire company OR shop owner OR admin
        $isCompanyOwner = $company->user_id === $user->id;
        $isAdmin = in_array($user->role, ['admin','superadmin']);
        $isShopOwner = false;
        if ($shopId) {
            if (isset($user->shop_id) && $user->shop_id == $shopId) $isShopOwner = true;
            elseif (method_exists($user,'shop') && optional($user->shop()->first())->id == $shopId) $isShopOwner = true;
        }

        if (!($isCompanyOwner || $isShopOwner || $isAdmin)) {
            return response()->json(['ok' => false, 'error' => 'Non autorisé'], 403);
        }

        $query = DeliveryMessage::where('delivery_company_id', $company->id)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId));

        if ($after = $request->query('after')) {
            $query->where('created_at', '>', $after);
        }

        $messages = $query->orderBy('created_at')->get();

        // Optionnel : si c'est la company qui lit, marquer shop->company messages comme lus
        if ($isCompanyOwner || $isAdmin) {
            DeliveryMessage::where('delivery_company_id', $company->id)
                ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
                ->where('sender_role', 'shop')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $payload = $messages->map(function ($m) use ($company) {
            return [
                'id' => $m->id,
                'body' => $m->message,
                'from_type' => $m->sender_role,
                'sender_name' => $m->sender ? $m->sender->name ?? null : ($m->shop?->name ?? $company->name),
                'created_at' => $m->created_at->toDateTimeString(),
            ];
        });

        $last = $messages->last()?->created_at?->toDateTimeString() ?? null;

        return response()->json([
            'ok' => true,
            'messages' => $payload,
            'last' => $last,
        ]);
    }
}
