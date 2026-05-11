<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCompany;
use App\Models\DeliveryMessage;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DeliveryChatController extends Controller
{
    /**
     * Inbox de la company : toutes les conversations groupées par boutique.
     * Accessible sur GET /company/chat/inbox
     */
    public function inbox(Request $request)
    {
        $user    = $request->user();
        $company = DeliveryCompany::forUser($user);

        if (!$company) {
            return redirect()->route('company.dashboard');
        }

        $conversations = DeliveryMessage::where('delivery_company_id', $company->id)
            ->whereNotNull('shop_id')
            ->with('shop')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('shop_id')
            ->map(function ($msgs) {
                $last  = $msgs->first();
                return [
                    'shop_id'      => (int) $last->shop_id,
                    'shop_name'    => $last->shop?->name ?: ('Boutique #' . $last->shop_id),
                    'last_message' => $last->message,
                    'last_at'      => $last->created_at,
                    'unread'       => $msgs->where('sender_role', 'shop')->whereNull('read_at')->count(),
                ];
            })
            ->values();

        $activeShopId = $request->query('shop_id');
        $messages     = collect();

        if ($activeShopId) {
            $messages = DeliveryMessage::where('delivery_company_id', $company->id)
                ->where('shop_id', $activeShopId)
                ->with('shop')
                ->orderBy('created_at')
                ->get();
            // Le marquage "lu" est fait côté JS via POST /chat/mark-read
            // uniquement quand l'utilisateur clique explicitement sur une conversation
        }

        return view('company.incoming_chat', compact('company', 'conversations', 'messages', 'activeShopId'));
    }

    /**
     * API : liste des conversations avec dernier message + non-lus.
     * GET /company/chat/conversations
     */
    public function conversations(Request $request)
    {
        $user    = $request->user();
        $company = DeliveryCompany::forUser($user);

        if (!$company) {
            return response()->json(['ok' => false, 'error' => 'Entreprise introuvable'], 404);
        }

        $conversations = DeliveryMessage::where('delivery_company_id', $company->id)
            ->whereNotNull('shop_id')
            ->with('shop')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('shop_id')
            ->map(function ($msgs) {
                $last = $msgs->first();
                return [
                    'shop_id'      => (int) $last->shop_id,
                    'shop_name'    => $last->shop?->name ?: ('Boutique #' . $last->shop_id),
                    'last_message' => Str::limit($last->message, 60),
                    'last_at'      => $last->created_at->toDateTimeString(),
                    'unread'       => $msgs->where('sender_role', 'shop')->whereNull('read_at')->count(),
                ];
            })
            ->values();

        return response()->json(['ok' => true, 'conversations' => $conversations]);
    }

    /**
     * Page de chat (boutique → company).
     * GET /company/{company}/chat
     */
    public function show(DeliveryCompany $company, Request $request)
    {
        $user            = $request->user();
        $isCompanyMember = $company->user_id === $user->id || $user->company_id === $company->id;

        if (!$company->approved && !$isCompanyMember) {
            abort(403, 'Entreprise non disponible.');
        }

        $shopId = $request->query('shop') ?? $request->query('shop_id') ?? null;

        if (!$shopId) {
            if (isset($user->shop_id) && $user->shop_id) {
                $shopId = $user->shop_id;
            } elseif (method_exists($user, 'shop')) {
                $rel    = $user->shop()->first();
                $shopId = $rel ? $rel->id : null;
            }
        }

        if (!$shopId && !$isCompanyMember && $user->role !== 'admin') {
            abort(403, 'Accès réservé aux vendeurs ou à l\'entreprise.');
        }

        $init = $request->query('init', '');

        $messages = DeliveryMessage::where('delivery_company_id', $company->id)
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->orderBy('created_at')
            ->get();

        $zones = $company->zones()->where('active', true)->orderBy('price')->get();

        $pendingOrders = collect();
        if ($shopId) {
            $pendingOrders = Order::with(['client', 'items.product'])
                ->where('shop_id', $shopId)
                ->whereNull('delivery_company_id')
                ->whereNull('livreur_id')
                ->whereIn('status', ['en_attente', 'confirmée', 'confirmee', 'pending'])
                ->latest()
                ->limit(20)
                ->get();
        }

        return view('company.chat', [
            'company'       => $company,
            'shopId'        => $shopId,
            'init'          => $init,
            'messages'      => $messages,
            'zones'         => $zones,
            'pendingOrders' => $pendingOrders,
        ]);
    }

    /**
     * Envoi d'un message (AJAX).
     * POST /company/{company}/chat/send
     */
    public function send(DeliveryCompany $company, Request $request)
    {
        $user = $request->user();
        $data = $request->all();

        $shopId = $data['shop_id'] ?? $data['shop'] ?? null;

        $validator = Validator::make($data, [
            'message' => 'required|string|max:2000',
            'shop_id' => 'nullable|integer|exists:shops,id',
            'shop'    => 'nullable|integer|exists:shops,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()], 422);
        }

        $isCompanyMember = $company->user_id === $user->id || $user->company_id === $company->id;
        $isAdmin         = in_array($user->role, ['admin', 'superadmin']);
        $isShopOwner     = false;

        if ($shopId) {
            if (isset($user->shop_id) && $user->shop_id == $shopId) {
                $isShopOwner = true;
            } elseif (method_exists($user, 'shop') && optional($user->shop()->first())->id == $shopId) {
                $isShopOwner = true;
            } elseif (method_exists($user, 'currentShopId') && $user->currentShopId() == $shopId) {
                $isShopOwner = true;
            }
        }

        if (!($isCompanyMember || $isShopOwner || $isAdmin)) {
            return response()->json(['ok' => false, 'error' => 'Non autorisé'], 403);
        }

        $senderRole = $isCompanyMember ? 'company' : ($isShopOwner ? 'shop' : 'admin');

        $msg = DeliveryMessage::create([
            'delivery_company_id' => $company->id,
            'shop_id'             => $shopId,
            'sender_id'           => $user->id,
            'sender_role'         => $senderRole,
            'message'             => $data['message'],
        ]);

        $senderName = $isCompanyMember
            ? $company->name
            : ($msg->shop?->name ?? $user->name ?? 'Boutique');

        return response()->json([
            'ok'   => true,
            'message' => [
                'id'          => $msg->id,
                'body'        => $msg->message,
                'sender_id'   => $msg->sender_id,
                'from_type'   => $msg->sender_role,
                'sender_name' => $senderName,
                'created_at'  => $msg->created_at->toDateTimeString(),
            ],
            'last' => $msg->created_at->toDateTimeString(),
        ]);
    }

    /**
     * Récupère les messages en JSON (polling AJAX).
     * GET /company/{company}/chat/messages
     */
    public function messages(DeliveryCompany $company, Request $request)
    {
        $user   = $request->user();
        $shopId = $request->query('shop') ?? $request->query('shop_id') ?? null;

        if (!$shopId && isset($user->shop_id)) {
            $shopId = $user->shop_id;
        }

        $isCompanyMember = $company->user_id === $user->id || $user->company_id === $company->id;
        $isAdmin         = in_array($user->role, ['admin', 'superadmin']);
        $isShopOwner     = false;

        if ($shopId) {
            if (isset($user->shop_id) && $user->shop_id == $shopId) {
                $isShopOwner = true;
            } elseif (method_exists($user, 'shop') && optional($user->shop()->first())->id == $shopId) {
                $isShopOwner = true;
            } elseif (method_exists($user, 'currentShopId') && $user->currentShopId() == $shopId) {
                $isShopOwner = true;
            }
        }

        if (!($isCompanyMember || $isShopOwner || $isAdmin)) {
            return response()->json(['ok' => false, 'error' => 'Non autorisé'], 403);
        }

        $query = DeliveryMessage::where('delivery_company_id', $company->id)
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId));

        if ($after = $request->query('after')) {
            $query->where('created_at', '>', $after);
        }

        $msgs = $query->orderBy('created_at')->get();

        $payload = $msgs->map(fn ($m) => [
            'id'          => $m->id,
            'body'        => $m->message,
            'sender_id'   => $m->sender_id,
            'from_type'   => $m->sender_role,
            'sender_name' => $m->sender?->name ?? $m->shop?->name ?? $company->name,
            'created_at'  => $m->created_at->toDateTimeString(),
        ]);

        return response()->json([
            'ok'       => true,
            'messages' => $payload,
            'last'     => $msgs->last()?->created_at?->toDateTimeString(),
        ]);
    }

    /**
     * Marque les messages d'une boutique comme lus.
     * Appelé UNIQUEMENT quand l'utilisateur ouvre explicitement la conversation.
     * POST /company/chat/mark-read
     */
    public function markRead(Request $request)
    {
        $user    = $request->user();
        $company = DeliveryCompany::forUser($user);

        if (!$company) {
            return response()->json(['ok' => false, 'error' => 'Entreprise introuvable'], 404);
        }

        $shopId = $request->input('shop_id');

        if ($shopId) {
            DeliveryMessage::where('delivery_company_id', $company->id)
                ->where('shop_id', $shopId)
                ->where('sender_role', 'shop')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }
}
