<?php

namespace App\Policies;

use App\Models\ExpenseRequest;
use App\Models\User;

class ExpenseRequestPolicy
{
    public function pay(User $user, ExpenseRequest $request): bool
    {
        return $user->isShopManager()
            && $user->location_id === $request->target_shop_id;
    }

    public function reject(User $user, ExpenseRequest $request): bool
    {
        return $this->pay($user, $request);
    }
}
