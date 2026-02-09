<?php

namespace App\Policies;

use App\Enums\LocationType;
use App\Enums\TransferStatus;
use App\Enums\UserRole;
use App\Models\Transfer;
use App\Models\User;

class TransferPolicy
{
    public function viewAny(User $user): bool
    {
        // All authenticated users can view transfers
        return true;
    }

    public function view(User $user, Transfer $transfer): bool
    {
        // Owner can view all
        if ($user->isOwner()) {
            return true;
        }

        // Warehouse manager can view if from their warehouse
        if ($user->isWarehouseManager()) {
            return $user->hasLocationAccess(
                LocationType::WAREHOUSE,
                $transfer->from_warehouse_id
            );
        }

        // Shop manager can view if to their shop
        if ($user->isShopManager()) {
            return $user->hasLocationAccess(
                LocationType::SHOP,
                $transfer->to_shop_id
            );
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Only shop managers and owners can create transfer requests
        return $user->isShopManager() || $user->isOwner();
    }

    public function approve(User $user, Transfer $transfer): bool
    {
        // Only warehouse manager or owner can approve
        if (!($user->isWarehouseManager() || $user->isOwner())) {
            return false;
        }

        // Must be pending
        if ($transfer->status !== TransferStatus::PENDING) {
            return false;
        }

        // Warehouse manager must be from the source warehouse
        if ($user->isWarehouseManager()) {
            return $user->hasLocationAccess(
                LocationType::WAREHOUSE,
                $transfer->from_warehouse_id
            );
        }

        return true;
    }

    public function pack(User $user, Transfer $transfer): bool
    {
        // Must be approved first
        if ($transfer->status !== TransferStatus::APPROVED) {
            return false;
        }

        // Only warehouse personnel or owners
        return $user->isWarehouseManager() || $user->isOwner();
    }

    public function receive(User $user, Transfer $transfer): bool
    {
        // Must be delivered
        if ($transfer->status !== TransferStatus::DELIVERED) {
            return false;
        }

        // Shop manager or owner
        if ($user->isOwner()) {
            return true;
        }

        return $user->isShopManager() && $user->hasLocationAccess(
            LocationType::SHOP,
            $transfer->to_shop_id
        );
    }

    public function cancel(User $user, Transfer $transfer): bool
    {
        // Only owner can cancel
        if (!$user->isOwner()) {
            return false;
        }

        // Cannot cancel if already received
        return !in_array($transfer->status, [
            TransferStatus::RECEIVED,
            TransferStatus::CANCELLED,
        ]);
    }
}