<?php

namespace App\Livewire\Shop\Transfers;

use App\Models\Product;
use App\Models\Transfer;
use Livewire\Component;

class ViewTransfer extends Component
{
    public Transfer $transfer;
    public array $items = [];

    protected $listeners = [
        'transfer-updated' => 'refreshTransfer',
    ];

    public function mount(Transfer $transfer)
    {
        $user = auth()->user();

        // Verify user is a shop manager
        if (!$user->isShopManager()) {
            abort(403, 'Only shop managers can access this page.');
        }

        // Verify this transfer is for this shop
        if ($transfer->to_shop_id !== $user->location_id) {
            abort(403, 'You can only view transfers for your shop.');
        }

        $this->transfer = $transfer;

        // Load items with boxes calculation
        foreach ($transfer->items as $item) {
            $product = $item->product;
            $boxesRequested = (int) $item->quantity_requested;

            $this->items[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $product->name,
                'items_per_box' => $product->items_per_box,
                'boxes_requested' => $boxesRequested,
                'quantity_requested' => $item->quantity_requested,
            ];
        }
    }

    public function refreshTransfer()
    {
        // Refresh the transfer data from database
        $this->transfer->refresh();
        $this->transfer->load(['items.product', 'boxes.box.product', 'fromWarehouse', 'toShop', 'requestedBy', 'reviewedBy', 'receivedBy', 'transporter']);

        // Reload items
        $this->items = [];
        foreach ($this->transfer->items as $item) {
            $product = $item->product;
            $boxesRequested = (int) $item->quantity_requested;

            $this->items[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $product->name,
                'items_per_box' => $product->items_per_box,
                'boxes_requested' => $boxesRequested,
                'quantity_requested' => $item->quantity_requested,
            ];
        }
    }

    public function markAsDelivered()
    {
        if ($this->transfer->status !== \App\Enums\TransferStatus::IN_TRANSIT) {
            session()->flash('error', 'Only in-transit transfers can be marked as delivered.');
            return;
        }

        try {
            $transferService = app(\App\Services\Inventory\TransferService::class);
            $transferService->markAsDelivered($this->transfer);
            $this->transfer->refresh();

            session()->flash('success', 'Transfer marked as delivered successfully.');
            $this->dispatch('transfer-updated', transferId: $this->transfer->id);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        // Refresh transfer data on each render to ensure latest data
        $this->transfer->refresh();
        $this->transfer->load(['items.product', 'boxes.box.product', 'fromWarehouse', 'toShop', 'requestedBy', 'reviewedBy', 'receivedBy', 'transporter']);

        return view('livewire.shop.transfers.view-transfer');
    }
}
