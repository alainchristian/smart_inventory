<?php

namespace App\Policies;

use App\Models\BankDeposit;
use App\Models\User;

class BankDepositPolicy
{
    public function void(User $user, BankDeposit $deposit): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        return $user->isShopManager()
            && $user->location_id === $deposit->shop_id;
    }
}
