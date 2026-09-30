<?php

namespace App\Livewire\WarehouseManager\Transfers;

use App\Livewire\Transfers\Concerns\ListsTransfers;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

/** Warehouse manager: transfers leaving their warehouse. */
class TransfersList extends Component
{
    use ListsTransfers;

    public function mount(): void
    {
        abort_unless(auth()->user()->isWarehouseManager(), 403, 'Only warehouse managers can access this page.');
    }

    protected function role(): string
    {
        return 'warehouse';
    }

    protected function scope(Builder $query): Builder
    {
        return $query->where('from_warehouse_id', auth()->user()->location_id);
    }
}
