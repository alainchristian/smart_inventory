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
        if ($user->isOwner() || $user->isAdmin()) {
            return true;
        }

        return $user->isShopManager() && $user->hasLocationAccess(
            LocationType::SHOP,
            $sale->shop_id
        );
    }

    public function create(User $user): bool
    {
        // Shop managers, owners, and admins can create sales
        return $user->isShopManager() || $user->isOwner() || $user->isAdmin();
    }

    public function void(User $user, Sale $sale): bool
    {
        // Cannot void already voided sale
        if ($sale->voided_at) {
            return false;
        }

        // Owner or admin can void any sale
        if ($user->isOwner() || $user->isAdmin()) {
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
        // Owners and admins can modify without approval
        return $user->isShopManager() || $user->isOwner() || $user->isAdmin();
    }

    public function approvePriceOverride(User $user, Sale $sale): bool
    {
        // Owners and admins can approve price overrides
        return ($user->isOwner() || $user->isAdmin()) && $sale->has_price_override;
    }
}
