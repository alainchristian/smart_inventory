<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isWarehouseManager() || $user->isShopManager();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->isOwner() || $user->isWarehouseManager() || $user->isShopManager();
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isOwner();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isOwner();
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->isOwner();
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->isOwner();
    }

    public function viewPurchasePrice(User $user): bool
    {
        return $user->isOwner();
    }
}
