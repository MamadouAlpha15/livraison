<?php

namespace App\Policies;

use App\Models\Affiliate;
use App\Models\User;
use App\Services\AffiliateService;

/** Un affilié ne voit que son propre compte ; seule l'équipe Shopio gère les affiliés. */
class AffiliatePolicy
{
    public static function isAdmin(User $user): bool
    {
        return in_array($user->role, AffiliateService::ADMIN_ROLES, true);
    }

    public function viewAny(User $user): bool
    {
        return self::isAdmin($user);
    }

    public function view(User $user, Affiliate $affiliate): bool
    {
        return self::isAdmin($user) || ($affiliate->user_id !== null && $affiliate->user_id === $user->id);
    }

    /** Seul l'affilié lui-même demande un retrait, jamais l'équipe à sa place */
    public function requestPayout(User $user, Affiliate $affiliate): bool
    {
        return $affiliate->user_id !== null && $affiliate->user_id === $user->id && $affiliate->isActive();
    }

    public function create(User $user): bool
    {
        return self::isAdmin($user);
    }

    public function update(User $user, Affiliate $affiliate): bool
    {
        return self::isAdmin($user);
    }
}
