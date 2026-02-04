<?php

namespace App\Livewire\WarehouseManager\Transfers;

use App\Enums\TransferStatus;
use App\Models\Product;
use App\Models\Transfer;
use App\Services\Inventory\TransferService;
use Livewire\Component;

class ReviewTransfer extends Component
{
    public Transfer $transfer;
    public array $items = [];
    public string $rejectReason = '';
    public bool $showRejectModal = false;

    public function mount(Transfer $transfer)
    {
        $user = auth()->user();

        // Verify user is a warehouse manager
        if (!$user->isWarehouseManager()) {
            abort(403, 'Only warehouse managers can access this page.');
        }

        // Verify this transfer is from this warehouse
        if ($transfer->from_warehouse_id !== $user->location_id) {
            abort(403, 'You can only manage transfers from your warehouse.');
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

    public function approve()
    {
        // Validate that we can approve
        if ($this->transfer->status !== TransferStatus::PENDING) {
            session()->flash('error', 'Only pending transfers can be approved.');
            return;
        }

        // Validate all quantities
        $hasErrors = false;
        foreach ($this->items as $index => $item) {
            if (!isset($item['boxes_requested']) || $item['boxes_requested'] < 1) {
                $this->addError(
                    "items.{$index}.boxes_requested",
                    "Please enter quantity for \"{$item['product_name']}\". Minimum 1 box required."
                );
                $hasErrors = true;
                continue;
            }

            // Check warehouse stock
            $product = Product::find($item['product_id']);
            if ($product) {
                $warehouseStock = $product->getCurrentStock('warehouse', $this->transfer->from_warehouse_id);
                $availableBoxes = $warehouseStock['full_boxes'] + $warehouseStock['partial_boxes'];

                if ($item['boxes_requested'] > $availableBoxes) {
                    $this->addError(
                        "items.{$index}.boxes_requested",
                        "\"{$item['product_name']}\": Cannot approve {$item['boxes_requested']} boxes. Only {$availableBoxes} boxes available in warehouse."
                    );
                    $hasErrors = true;
                }
            }
        }

        if ($hasErrors) {
            session()->flash('error', 'Please fix the validation errors before approving.');
            return;
        }

        try {
            // Update transfer items with modified quantities
            foreach ($this->items as $item) {
                $transferItem = $this->transfer->items()->find($item['id']);
                if ($transferItem) {
                    $product = Product::find($item['product_id']);
                    $newQuantity = $item['boxes_requested'] * $product->items_per_box;
                    $transferItem->update([
                        'quantity_requested' => $newQuantity,
                    ]);
                }
            }

            // Approve the transfer
            $transferService = app(TransferService::class);
            $transferService->approveTransfer($this->transfer);

            session()->flash('success', "Transfer {$this->transfer->transfer_number} approved successfully.");
            return redirect()->route('warehouse.transfers.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error approving transfer: ' . $e->getMessage());
        }
    }

    public function openRejectModal()
    {
        $this->showRejectModal = true;
        $this->rejectReason = '';
    }

    public function closeRejectModal()
    {
        $this->showRejectModal = false;
        $this->rejectReason = '';
    }

    public function reject()
    {
        if (empty($this->rejectReason)) {
            $this->addError('rejectReason', 'Please provide a reason for rejection.');
            return;
        }

        if ($this->transfer->status !== TransferStatus::PENDING) {
            session()->flash('error', 'Only pending transfers can be rejected.');
            return;
        }

        try {
            $transferService = app(TransferService::class);
            $transferService->rejectTransfer($this->transfer, $this->rejectReason);

            session()->flash('success', "Transfer {$this->transfer->transfer_number} rejected.");
            return redirect()->route('warehouse.transfers.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error rejecting transfer: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Get warehouse stock for all products
        $stockLevels = [];
        foreach ($this->items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $stock = $product->getCurrentStock('warehouse', $this->transfer->from_warehouse_id);
                $stockLevels[$product->id] = [
                    'full_boxes' => $stock['full_boxes'],
                    'partial_boxes' => $stock['partial_boxes'],
                    'total_boxes' => $stock['full_boxes'] + $stock['partial_boxes'],
                    'total_items' => $stock['total_items'],
                ];
            }
        }

        return view('livewire.warehouse-manager.transfers.review-transfer', [
            'stockLevels' => $stockLevels,
        ]);
    }
}
