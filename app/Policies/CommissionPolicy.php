<?php

namespace App\Policies;

use App\Models\Commission;
use App\Models\User;

/** Un affilié ne voit que ses commissions ; seule l'équipe enregistre un remboursement. */
class CommissionPolicy
{
    public function viewAny(User $user): bool
    {
        return AffiliatePolicy::isAdmin($user);
    }

    public function view(User $user, Commission $commission): bool
    {
        return AffiliatePolicy::isAdmin($user) || $commission->affiliate?->user_id === $user->id;
    }

    public function update(User $user, Commission $commission): bool
    {
        return AffiliatePolicy::isAdmin($user);
    }
}
