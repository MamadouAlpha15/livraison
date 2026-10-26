<?php

namespace App\Policies;

use App\Models\Referral;
use App\Models\User;

/** Un parrain ne voit que ses propres filleuls ; seule l'équipe les vérifie ou les annule. */
class ReferralPolicy
{
    public function view(User $user, Referral $referral): bool
    {
        return AffiliatePolicy::isAdmin($user) || $referral->affiliate?->user_id === $user->id;
    }

    public function update(User $user, Referral $referral): bool
    {
        return AffiliatePolicy::isAdmin($user);
    }
}
