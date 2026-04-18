<?php

namespace App\Policies;

use App\Models\OwnerWithdrawal;
use App\Models\User;

class OwnerWithdrawalPolicy
{
    public function void(User $user, OwnerWithdrawal $withdrawal): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        return $user->isShopManager()
            && $user->location_id === $withdrawal->shop_id;
    }
}
