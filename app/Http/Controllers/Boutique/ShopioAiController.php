<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\AiConversationState;
use App\Models\AiReplySuggestion;
use App\Models\AiUsage;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopAiSetting;
use App\Models\ShopMessage;
use App\Models\User;
use App\Services\ShopioAI\UsageMeter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Espace vendeur → Shopio IA : réglages (activation, mode, ton, remise maximale, infos de la boutique),
 * réponses proposées à valider, conversations traitées avec « Reprendre la main », statistiques.
 */
class ShopioAiController extends Controller
{
    public function index(Request $request)
    {
        $shop = $this->shop($request);
        $since = now()->subDays(30);

        $aiMessages = ShopMessage::where('shop_id', $shop->id)->where('is_ai', true)->where('created_at', '>=', $since);
        $conversations = ShopMessage::where('shop_id', $shop->id)->where('is_ai', true)->where('created_at', '>=', $since)
            ->select('receiver_id', DB::raw('max(id) as last_id'), DB::raw('count(*) as replies'))->groupBy('receiver_id')
            ->orderByDesc('last_id')->limit(30)->get();
        $states = AiConversationState::where('shop_id', $shop->id)->whereIn('client_id', $conversations->pluck('receiver_id'))->get()->keyBy('client_id');

        return view('boutique.shopio-ai.index', [
            'shop'        => $shop,
            'settings'    => ShopAiSetting::forShop($shop->id),
            'used'        => UsageMeter::used($shop),
            'limit'       => UsageMeter::limit($shop),
            'autoAllowed' => UsageMeter::autoAllowed($shop),
            'stats'       => [
                'answered'  => (clone $aiMessages)->count() + AiReplySuggestion::where('shop_id', $shop->id)->where('created_at', '>=', $since)->count(),
                'handoffs'  => AiConversationState::where('shop_id', $shop->id)->where('handed_off_at', '>=', $since)->count(),
                'orders'    => $this->ordersAfterAi($shop, $since),
            ],
            'suggestions' => AiReplySuggestion::with(['client:id,name', 'replyTo:id,body', 'product:id,name'])
                ->where('shop_id', $shop->id)->where('status', AiReplySuggestion::STATUS_PENDING)->latest()->limit(20)->get(),
            'conversations' => $conversations->map(fn ($c) => [
                'client'  => User::find($c->receiver_id, ['id', 'name']),
                'last'    => ShopMessage::find($c->last_id, ['id', 'body', 'created_at']),
                'replies' => (int) $c->replies,
                'state'   => $states[$c->receiver_id] ?? null,
            ])->filter(fn ($c) => $c['client']),
        ]);
    }

    /** Commandes passées par un client dans les 48 h qui suivent une réponse de Shopio IA */
    private function ordersAfterAi(Shop $shop, $since): array
    {
        $orders = Order::where('shop_id', $shop->id)->where('created_at', '>=', $since)->whereNotNull('user_id')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('shop_messages')
                ->where('shop_messages.shop_id', $shop->id)->where('shop_messages.is_ai', true)
                ->whereColumn('shop_messages.receiver_id', 'orders.user_id')
                ->whereColumn('shop_messages.created_at', '<=', 'orders.created_at')
                ->where('shop_messages.created_at', '>=', DB::raw(DB::getDriverName() === 'sqlite'
                    ? "datetime(orders.created_at, '-2 days')" : 'DATE_SUB(orders.created_at, INTERVAL 2 DAY)')))
            ->get(['id', 'total']);
        return ['count' => $orders->count(), 'amount' => (int) $orders->sum('total')];
    }

    public function update(Request $request)
    {
        $shop = $this->shop($request);
        $data = $request->validate([
            'mode'                 => ['required', Rule::in([ShopAiSetting::MODE_SUGGESTION, ShopAiSetting::MODE_AUTO])],
            'address_form'         => ['required', Rule::in(['tu', 'vous'])],
            'max_discount_percent' => ['nullable', 'integer', 'between:1,90'],
            'opening_hours'        => ['nullable', 'string', 'max:500'],
            'delivery_info'        => ['nullable', 'string', 'max:1000'],
            'return_policy'        => ['nullable', 'string', 'max:1000'],
            'faq'                  => ['nullable', 'string', 'max:3000'],
            'tone_notes'           => ['nullable', 'string', 'max:500'],
        ], ['max_discount_percent.between' => 'La remise maximale va de 1 à 90 %.']);
        $data['enabled'] = $request->boolean('enabled');
        if ($data['mode'] === ShopAiSetting::MODE_AUTO && !UsageMeter::autoAllowed($shop)) {
            return back()->withErrors(['mode' => 'La réponse automatique est réservée au plan Pro : garde le mode « suggestion » ou passe au plan Pro.'])->withInput();
        }

        ShopAiSetting::updateOrCreate(['shop_id' => $shop->id], $data);
        return back()->with('success', 'Réglages de Shopio IA enregistrés.');
    }

    /** Envoi d'une réponse proposée (éventuellement modifiée), marquée « Shopio IA » */
    public function sendSuggestion(Request $request, AiReplySuggestion $suggestion)
    {
        $shop = $this->shop($request);
        abort_unless((int) $suggestion->shop_id === $shop->id, 404);
        $data = $request->validate(['body' => ['nullable', 'string', 'max:1000']]);
        $body = trim((string) ($data['body'] ?? '')) ?: $suggestion->body;
        if ($suggestion->status !== AiReplySuggestion::STATUS_PENDING) {
            return back()->withErrors(['suggestion' => 'Cette réponse a déjà été traitée.']);
        }

        $message = ShopMessage::create([
            'shop_id' => $shop->id, 'product_id' => $suggestion->product_id, 'sender_id' => $shop->user_id,
            'receiver_id' => $suggestion->client_id, 'type' => ShopMessage::TYPE_TEXT, 'body' => $body, 'is_ai' => true,
        ]);
        $suggestion->update(['status' => AiReplySuggestion::STATUS_SENT, 'shop_message_id' => $message->id, 'body' => $body]);
        return back()->with('success', 'Réponse envoyée à ' . ($suggestion->client?->name ?? 'ton client') . '.');
    }

    public function dismissSuggestion(Request $request, AiReplySuggestion $suggestion)
    {
        $shop = $this->shop($request);
        abort_unless((int) $suggestion->shop_id === $shop->id, 404);
        $suggestion->update(['status' => AiReplySuggestion::STATUS_DISMISSED]);
        return back()->with('success', 'Réponse ignorée.');
    }

    /** « Reprendre la main » : l'IA ne répond plus à ce client tant que le vendeur ne la relance pas */
    public function takeOver(Request $request, User $client)
    {
        $shop = $this->shop($request);
        AiConversationState::updateOrCreate(['shop_id' => $shop->id, 'client_id' => $client->id], ['handed_off_at' => now(), 'handoff_reason' => 'Le vendeur a repris la main']);
        AiReplySuggestion::where('shop_id', $shop->id)->where('client_id', $client->id)->where('status', AiReplySuggestion::STATUS_PENDING)
            ->update(['status' => AiReplySuggestion::STATUS_DISMISSED]);
        return back()->with('success', 'Tu as repris la main avec ' . $client->name . ' : Shopio IA ne lui répond plus.');
    }

    public function handBack(Request $request, User $client)
    {
        $shop = $this->shop($request);
        AiConversationState::where('shop_id', $shop->id)->where('client_id', $client->id)
            ->update(['handed_off_at' => null, 'human_until' => null, 'handoff_reason' => null]);
        return back()->with('success', 'Shopio IA répond de nouveau à ' . $client->name . '.');
    }

    private function shop(Request $request): Shop
    {
        $shop = $request->user()->shop_id ? Shop::find($request->user()->shop_id) : null;
        abort_unless($shop && (int) $shop->user_id === (int) $request->user()->id, 403);
        return $shop;
    }
}
