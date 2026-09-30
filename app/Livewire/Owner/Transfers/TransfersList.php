<?php

namespace App\Livewire\Owner\Transfers;

use App\Livewire\Transfers\Concerns\ListsTransfers;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

/** Owner: every transfer across all warehouses and shops. */
class TransfersList extends Component
{
    use ListsTransfers;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user->isOwner() || $user->isAdmin(), 403, 'Only owners and admins can access this page.');
    }

    protected function role(): string
    {
        return 'owner';
    }

    protected function scope(Builder $query): Builder
    {
        return $query;
    }
}
