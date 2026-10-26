<?php

namespace App\Support\Campaigns;

use App\Models\User;
use App\Notifications\CampaignNotification;
use Illuminate\Support\Collection;

/** Notifications de campagnes non lues d'un utilisateur (vendeur ou équipe Shopio). */
class CampaignInbox
{
    public static function query(User $user)
    {
        return $user->unreadNotifications()->where('type', CampaignNotification::class);
    }

    public static function count(User $user): int
    {
        return self::query($user)->count();
    }

    public static function latest(User $user, int $limit = 5): Collection
    {
        return self::query($user)->latest()->take($limit)->get();
    }

    /** La campagne est ouverte : ses notifications sont lues. */
    public static function markCampaignRead(User $user, int $campaignId): void
    {
        self::query($user)->get()
            ->filter(fn ($n) => (int) ($n->data['campaign_id'] ?? 0) === $campaignId)
            ->each->markAsRead();
    }

    public static function markAllRead(User $user): void
    {
        self::query($user)->update(['read_at' => now()]);
    }
}
