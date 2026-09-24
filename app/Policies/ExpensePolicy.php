<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function void(User $user, Expense $expense): bool
    {
        if ($user->isOwner() || $user->isAdmin()) {
            return true;
        }

        return $user->isShopManager()
            && $user->location_id === $expense->dailySession->shop_id;
    }
}
