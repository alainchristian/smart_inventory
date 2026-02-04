<?php

namespace App\Livewire\Shop\Transfers;

use App\Models\Product;
use App\Models\Transfer;
use Livewire\Component;

class ViewTransfer extends Component
{
    public Transfer $transfer;
    public array $items = [];

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
            $boxesRequested = $item->quantity_requested / $product->items_per_box;

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

    public function render()
    {
        return view('livewire.shop.transfers.view-transfer');
    }
}
