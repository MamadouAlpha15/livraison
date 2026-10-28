<?php

namespace App\Services\ShopioAI;

use App\Models\AiUsage;
use App\Models\Shop;
use App\Models\ShopioAiSetting;

/** Consommation de Shopio IA par boutique et par mois, et limite de messages selon le plan. */
class UsageMeter
{
    public static function month(): string
    {
        return now()->format('Y-m');
    }

    public static function isPro(Shop $shop): bool
    {
        return $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
    }

    public static function limit(Shop $shop): int
    {
        $s = ShopioAiSetting::current();
        return self::isPro($shop) ? $s->pro_monthly_limit : $s->free_monthly_limit;
    }

    /** Réponse automatique permise pour ce plan ? (sinon : suggestions seulement) */
    public static function autoAllowed(Shop $shop): bool
    {
        return self::isPro($shop) || ShopioAiSetting::current()->free_auto_allowed;
    }

    public static function used(Shop $shop): int
    {
        return (int) AiUsage::where('shop_id', $shop->id)->where('month', self::month())->value('messages');
    }

    public static function remaining(Shop $shop): int
    {
        return max(0, self::limit($shop) - self::used($shop));
    }

    public static function record(Shop $shop, ChatResult $result, bool $handoff = false): void
    {
        AiUsage::firstOrCreate(['shop_id' => $shop->id, 'month' => self::month()]);
        $cost = $result->inputTokens * ClaudeChatModel::INPUT_MICRO_USD + $result->outputTokens * ClaudeChatModel::OUTPUT_MICRO_USD;
        // Incréments faits par la base : justes même si deux réponses sont comptées en même temps
        AiUsage::where('shop_id', $shop->id)->where('month', self::month())->update([
            'messages'       => \DB::raw('messages + 1'),
            'handoffs'       => \DB::raw('handoffs + ' . ($handoff ? 1 : 0)),
            'input_tokens'   => \DB::raw('input_tokens + ' . (int) $result->inputTokens),
            'output_tokens'  => \DB::raw('output_tokens + ' . (int) $result->outputTokens),
            'cost_micro_usd' => \DB::raw('cost_micro_usd + ' . (int) $cost),
            'updated_at'     => now(),
        ]);
    }
}
