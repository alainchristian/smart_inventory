<?php

namespace App\Livewire\Warehouse\ExpenseRequests;

use App\Models\ExpenseRequest;
use App\Models\Shop;
use App\Services\DayClose\ExpenseRequestService;
use Illuminate\Support\Collection;
use Livewire\Component;

class CreateRequest extends Component
{
    public int $targetShopId = 0;
    public int $amount = 0;
    public string $reason = '';
    public Collection $shops;
    public Collection $myRequests;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user->isWarehouseManager()) {
            abort(403);
        }

        $this->shops = Shop::active()->orderBy('name')->get();
        $this->loadRequests();
    }

    public function loadRequests(): void
    {
        $this->myRequests = ExpenseRequest::forWarehouse(auth()->user()->location_id)
            ->with('targetShop')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();
    }

    public function submitRequest(): void
    {
        $this->validate([
            'targetShopId' => 'required|integer|min:1|exists:shops,id',
            'amount'       => 'required|integer|min:1',
            'reason'       => 'required|string|min:3|max:500',
        ]);

        try {
            app(ExpenseRequestService::class)->createRequest([
                'target_shop_id' => $this->targetShopId,
                'amount'         => $this->amount,
                'reason'         => $this->reason,
            ], auth()->user());

            $this->reset(['targetShopId', 'amount', 'reason']);
            session()->flash('success', 'Expense request submitted successfully.');
            $this->loadRequests();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.warehouse.expense-requests.create-request');
    }
}
