<?php

namespace App\Policies;

use App\Models\DailySession;
use App\Models\User;

class DailySessionPolicy
{
    public function lock(User $user, DailySession $session): bool
    {
        return $user->isOwner();
    }
}
