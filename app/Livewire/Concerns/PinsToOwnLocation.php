<?php

namespace App\Livewire\Concerns;

/**
 * For components whose shop / warehouse id is bound to a <select> that only
 * owners and admins use. A shop manager's `shopId` and a warehouse manager's
 * `warehouseId` are set in mount() from their own assignment; since any public
 * Livewire property can be overwritten from the browser, a manager must not be
 * able to change it. (Ids no one changes from the page use #[Locked] instead.)
 *
 * Livewire calls this through its trait hook: updating{TraitName}().
 */
trait PinsToOwnLocation
{
    public function updatingPinsToOwnLocation(string $name, mixed $value): void
    {
        $user = auth()->user();

        $pinned = match (true) {
            (bool) $user?->isShopManager()      => 'shopId',
            (bool) $user?->isWarehouseManager() => 'warehouseId',
            default                             => null,
        };

        if ($pinned !== null && $name === $pinned) {
            abort(403, 'You cannot switch to another location.');
        }
    }
}
