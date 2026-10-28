<?php

namespace App\Services\ShopioAI;

use App\Models\AiConversationState;
use App\Models\AiReplySuggestion;
use App\Models\Shop;
use App\Models\ShopAiSetting;
use App\Models\ShopioAiSetting;
use App\Models\ShopMessage;
use App\Models\User;
use App\Services\PushService;
use App\Support\Ai\AiUnavailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Shopio IA dans la messagerie : répond au message d'un client avec les vraies données de Shopio.
 *   - mode « auto » : la réponse part dans la conversation, marquée « Shopio IA » ;
 *   - mode « suggestion » : le vendeur la reçoit et l'envoie en un clic.
 * L'IA se tait si la boutique l'a désactivée, si le vendeur a repris la main, si la limite du mois est
 * atteinte ou si le client ou la boutique écrivent trop vite (limites de requêtes).
 */
class ShopioAssistant
{
    public const FALLBACK = 'Merci pour ton message ! Le vendeur te répond très vite.';
    public const VERIFY = 'Je préfère vérifier cette information avec le vendeur : il te répond très vite.';

    public function __construct(private ChatModel $model, private ShopioTools $tools) {}

    /** Pourquoi l'IA ne répondra pas à ce message (null = elle répond) */
    public function skipReason(ShopMessage $message): ?string
    {
        $client = User::find($message->sender_id);
        if (!$message->shop_id || !$client || $client->role !== 'client') return 'pas_un_client';
        if (!ShopioAiSetting::current()->enabled) return 'desactivee_shopio';
        $settings = ShopAiSetting::where('shop_id', $message->shop_id)->first();
        if (!$settings?->enabled) return 'desactivee_boutique';
        if (in_array(Str::upper(trim((string) $message->body)), ['STOP', 'ARRET', 'ARRÊT'], true)) return 'stop';
        $state = AiConversationState::where('shop_id', $message->shop_id)->where('client_id', $client->id)->first();
        if ($state?->pausesAi()) return 'vendeur';
        $shop = Shop::find($message->shop_id);
        if (!$shop || UsageMeter::remaining($shop) <= 0) return 'limite';
        return null;
    }

    /**
     * Traite le message du client.
     * @throws AiUnavailable si l'IA ne répond pas (le job réessaie puis envoie le message de secours)
     */
    public function handle(ShopMessage $message): ?string
    {
        if ($reason = $this->skipReason($message)) {
            return $reason;
        }
        // Limites de requêtes : 6 messages par minute et par client, 60 par minute et par boutique
        if (!RateLimiter::attempt('shopio-ai:client:' . $message->sender_id, 6, fn () => true)
            || !RateLimiter::attempt('shopio-ai:shop:' . $message->shop_id, 60, fn () => true)) {
            return 'trop_rapide';
        }

        $shop = Shop::with('owner')->find($message->shop_id);
        $client = User::find($message->sender_id);
        $settings = ShopAiSetting::where('shop_id', $shop->id)->first();
        $ctx = new AiContext($shop, $client, $settings, $message->product, $message->id);

        $conversation = $this->transcript($shop, $client);
        $result = $this->model->run(
            PromptBuilder::system($ctx),
            [['role' => 'user', 'content' => "Conversation (la plus récente en dernier) :\n{$conversation}\n\nRéponds au dernier message du client."]],
            $this->tools->definitions(),
            fn (string $tool, array $input) => $this->tools->execute($tool, $input, $ctx),
        );

        $text = trim($result->text);
        if ($unsupported = ReplyGuard::unsupported($text, $ctx->toolResults, $conversation)) {
            // Chiffre absent des données : on n'envoie pas la réponse et on passe la main au vendeur
            Log::warning('[Shopio IA] Réponse bloquée, valeurs non vérifiées : ' . implode(', ', $unsupported), ['shop' => $shop->id]);
            $this->tools->execute('transferer_au_vendeur', ['raison' => 'Shopio IA n\'a pas pu vérifier une information'], $ctx);
            $text = self::VERIFY;
        }

        UsageMeter::record($shop, $result, $ctx->handedOff);
        $this->deliver($shop, $client, $settings, $message, $text);
        return 'repondu';
    }

    /** Envoi selon le mode : réponse marquée « Shopio IA », ou suggestion à valider par le vendeur */
    public function deliver(Shop $shop, User $client, ShopAiSetting $settings, ShopMessage $replyTo, string $text): void
    {
        $auto = $settings->mode === ShopAiSetting::MODE_AUTO && UsageMeter::autoAllowed($shop);
        if ($auto || $text === self::FALLBACK) {
            ShopMessage::create([
                'shop_id' => $shop->id, 'product_id' => $replyTo->product_id, 'sender_id' => $shop->user_id,
                'receiver_id' => $client->id, 'body' => $text, 'type' => ShopMessage::TYPE_TEXT, 'is_ai' => true,
            ]);
            return;
        }

        AiReplySuggestion::create([
            'shop_id' => $shop->id, 'client_id' => $client->id, 'product_id' => $replyTo->product_id,
            'reply_to_message_id' => $replyTo->id, 'body' => $text, 'status' => AiReplySuggestion::STATUS_PENDING,
        ]);
        try {
            if ($shop->owner) {
                app(PushService::class)->sendToUser($shop->owner, 'Shopio IA a préparé une réponse', $client->name . ' : ' . Str::limit($text, 90), 1, '/boutique/shopio-ia');
            }
        } catch (\Throwable $e) {
        }
    }

    /** Message de secours quand l'IA ne répond pas, seulement en mode automatique */
    public function fallback(ShopMessage $message): void
    {
        $settings = ShopAiSetting::where('shop_id', $message->shop_id)->first();
        $shop = Shop::find($message->shop_id);
        $client = User::find($message->sender_id);
        if (!$settings || !$shop || !$client || $settings->mode !== ShopAiSetting::MODE_AUTO || !UsageMeter::autoAllowed($shop)) {
            return;
        }
        // Une seule réponse de secours par message
        if (ShopMessage::where('shop_id', $shop->id)->where('receiver_id', $client->id)->where('is_ai', true)->where('id', '>', $message->id)->exists()) {
            return;
        }
        $this->deliver($shop, $client, $settings, $message, self::FALLBACK);
    }

    /** Les 20 derniers messages de la conversation, sans données inutiles */
    private function transcript(Shop $shop, User $client): string
    {
        return ShopMessage::where('shop_id', $shop->id)
            ->where(fn ($q) => $q->where('sender_id', $client->id)->orWhere('receiver_id', $client->id))
            ->orderByDesc('id')->limit(20)->get()->reverse()
            ->map(function (ShopMessage $m) use ($client) {
                $who = (int) $m->sender_id === (int) $client->id ? 'Client' : ($m->is_ai ? 'Shopio IA' : 'Vendeur');
                $price = $m->proposed_price ? ' [prix proposé : ' . number_format((float) $m->proposed_price, 0, ',', ' ') . ' GNF, ' . $m->proposal_status . ']' : '';
                return $who . ' : ' . Str::limit(trim((string) $m->body), 600) . $price;
            })->implode("\n");
    }
}
