<?php

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;

/** Les retraits sont traités (payés ou refusés) uniquement par l'équipe Shopio. */
class PayoutPolicy
{
    public function viewAny(User $user): bool
    {
        return AffiliatePolicy::isAdmin($user);
    }

    public function view(User $user, Payout $payout): bool
    {
        return AffiliatePolicy::isAdmin($user) || $payout->affiliate?->user_id === $user->id;
    }

    public function update(User $user, Payout $payout): bool
    {
        return AffiliatePolicy::isAdmin($user);
    }
}
