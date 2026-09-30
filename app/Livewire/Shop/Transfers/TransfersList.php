<?php

namespace App\Livewire\Shop\Transfers;

use App\Livewire\Transfers\Concerns\ListsTransfers;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;

/** Shop manager: transfers coming to their shop. */
class TransfersList extends Component
{
    use ListsTransfers;

    public function mount(): void
    {
        abort_unless(auth()->user()->isShopManager(), 403, 'Only shop managers can access this page.');
    }

    #[On('transfer-updated')]
    public function refreshList(): void
    {
        // re-render
    }

    protected function role(): string
    {
        return 'shop';
    }

    protected function scope(Builder $query): Builder
    {
        return $query->where('to_shop_id', auth()->user()->location_id);
    }
}
