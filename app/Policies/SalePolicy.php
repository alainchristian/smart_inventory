<?php

namespace App\Policies;

use App\Enums\LocationType;
use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Sale $sale): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        return $user->isShopManager() && $user->hasLocationAccess(
            LocationType::SHOP,
            $sale->shop_id
        );
    }

    public function create(User $user): bool
    {
        // Only shop managers and owners can create sales
        return $user->isShopManager() || $user->isOwner();
    }

    public function void(User $user, Sale $sale): bool
    {
        // Cannot void already voided sale
        if ($sale->voided_at) {
            return false;
        }

        // Owner can void any sale
        if ($user->isOwner()) {
            return true;
        }

        // Shop manager can void sales from their shop
        return $user->isShopManager() && $user->hasLocationAccess(
            LocationType::SHOP,
            $sale->shop_id
        );
    }

    public function modifyPrice(User $user, Sale $sale): bool
    {
        // Shop managers can modify (requires approval)
        // Owners can modify without approval
        return $user->isShopManager() || $user->isOwner();
    }

    public function approvePriceOverride(User $user, Sale $sale): bool
    {
        // Only owners can approve price overrides
        return $user->isOwner() && $sale->has_price_override;
    }
}