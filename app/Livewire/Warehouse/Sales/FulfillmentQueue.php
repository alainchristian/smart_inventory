<?php

namespace App\Livewire\Warehouse\Sales;

use App\Models\ActivityLog;
use App\Models\Sale;
use App\Models\Warehouse;
use Livewire\Component;

class FulfillmentQueue extends Component
{
    public int $warehouseId;
    public string $warehouseName = '';
    public string $tab = 'pending'; // 'pending' | 'history'
    public ?int $expandedSaleId = null;
    public ?int $expandedHistoryId = null;

    // Confirm state
    public ?int $confirmingFulfillmentId = null;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->isOwner()) {
            $warehouse = Warehouse::first();
        } else {
            $warehouse = Warehouse::find($user->location_id);
        }

        if (!$warehouse) {
            abort(403, 'No warehouse assigned.');
        }

        $this->warehouseId   = $warehouse->id;
        $this->warehouseName = $warehouse->name;
    }

    public function getPendingSalesProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return Sale::warehouseDirect()
            ->pendingFulfillment()
            ->where('source_warehouse_id', $this->warehouseId)
            ->with(['items.box', 'items.product', 'shop', 'fulfillmentTransporter', 'payments', 'soldBy'])
            ->latest('sale_date')
            ->get();
    }

    public function getFulfilledTodayCountProperty(): int
    {
        return Sale::warehouseDirect()
            ->where('source_warehouse_id', $this->warehouseId)
            ->where('fulfillment_status', 'fulfilled')
            ->whereDate('fulfillment_confirmed_at', today())
            ->count();
    }

    public function getBoxesDispatchedTodayProperty(): int
    {
        $sales = Sale::warehouseDirect()
            ->where('source_warehouse_id', $this->warehouseId)
            ->where('fulfillment_status', 'fulfilled')
            ->whereDate('fulfillment_confirmed_at', today())
            ->with(['items.box'])
            ->get();

        return $sales->sum(
            fn ($s) => $s->items->filter(fn ($i) => $i->box?->location_type?->value === 'warehouse')->count()
        );
    }

    public function getFulfilledHistoryProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return Sale::warehouseDirect()
            ->where('source_warehouse_id', $this->warehouseId)
            ->where('fulfillment_status', 'fulfilled')
            ->with(['items.box', 'items.product', 'shop', 'fulfillmentTransporter', 'fulfillmentConfirmedBy', 'payments', 'soldBy'])
            ->latest('fulfillment_confirmed_at')
            ->limit(100)
            ->get();
    }

    public function setTab(string $tab): void
    {
        $this->tab            = $tab;
        $this->expandedSaleId = null;
        $this->expandedHistoryId = null;
    }

    public function toggleHistory(int $saleId): void
    {
        $this->expandedHistoryId = $this->expandedHistoryId === $saleId ? null : $saleId;
    }

    public function toggleExpand(int $saleId): void
    {
        $this->expandedSaleId = $this->expandedSaleId === $saleId ? null : $saleId;
    }

    public function requestFulfillment(int $saleId): void
    {
        $this->confirmingFulfillmentId = $saleId;
    }

    public function cancelFulfillment(): void
    {
        $this->confirmingFulfillmentId = null;
    }

    public function markFulfilled(int $saleId): void
    {
        $sale = Sale::warehouseDirect()
            ->pendingFulfillment()
            ->where('source_warehouse_id', $this->warehouseId)
            ->findOrFail($saleId);

        $sale->update([
            'fulfillment_status'      => 'fulfilled',
            'fulfillment_confirmed_at' => now(),
            'fulfillment_confirmed_by' => auth()->id(),
        ]);

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'action'            => 'fulfillment_confirmed',
            'entity_type'       => 'Sale',
            'entity_id'         => $sale->id,
            'entity_identifier' => $sale->sale_number,
            'details'           => "Fulfillment confirmed at {$this->warehouseName}",
        ]);

        $this->confirmingFulfillmentId = null;

        if ($this->expandedSaleId === $saleId) {
            $this->expandedSaleId = null;
        }
    }

    public function render()
    {
        return view('livewire.warehouse.sales.fulfillment-queue', [
            'pendingSales'         => $this->pendingSales,
            'fulfilledToday'       => $this->fulfilledTodayCount,
            'boxesDispatchedToday' => $this->boxesDispatchedToday,
            'fulfilledHistory'     => $this->tab === 'history' ? $this->fulfilledHistory : collect(),
        ]);
    }
}
