<?php

namespace App\Observers;

use App\Jobs\AnswerWithShopioAi;
use App\Models\AiConversationState;
use App\Models\ShopAiSetting;
use App\Models\ShopMessage;
use App\Models\User;

/**
 * Message client dans une boutique où Shopio IA est active : réponse préparée en file d'attente.
 * Message écrit à la main par le vendeur : l'IA se met en pause 2 h dans cette conversation.
 * Ne bloque jamais l'envoi du message.
 */
class ShopioAiObserver
{
    public const HUMAN_PAUSE_HOURS = 2;

    public function created(ShopMessage $message): void
    {
        try {
            if (!$message->shop_id || !ShopAiSetting::where('shop_id', $message->shop_id)->where('enabled', true)->exists()) {
                return;
            }
            $role = User::whereKey($message->sender_id)->value('role');

            if ($role === 'client') {
                if (in_array($message->type, [ShopMessage::TYPE_TEXT, ShopMessage::TYPE_IMAGES], true)) {
                    AnswerWithShopioAi::dispatch($message->id)->afterCommit();
                }
            } elseif (!$message->is_ai && !$message->follow_up_id && $message->receiver_id) {
                AiConversationState::updateOrCreate(
                    ['shop_id' => $message->shop_id, 'client_id' => $message->receiver_id],
                    ['human_until' => now()->addHours(self::HUMAN_PAUSE_HOURS)]
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
