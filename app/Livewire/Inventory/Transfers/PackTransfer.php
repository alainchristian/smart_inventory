<?php

namespace App\Livewire\Inventory\Transfers;

use App\Models\Product;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Services\Inventory\TransferService;
use Livewire\Component;

class PackTransfer extends Component
{
    public Transfer $transfer;
    public string $scanInput = '';
    public int $scanQuantity = 1;
    public ?string $pendingBarcode = null;
    public ?string $pendingProductName = null;
    public ?int $pendingAvailableCount = null;
    public array $packedBoxes = [];
    public bool $enableScanner = true;
    public ?int $transporter_id = null;

    protected $listeners = [
        'barcode-scanned' => 'handleBarcodeScan',
    ];

    protected $rules = [
        'transporter_id' => 'required|exists:transporters,id',
    ];

    public function mount(Transfer $transfer)
    {
        $this->authorize('pack', $transfer);
        $this->transfer = $transfer;
        $this->refreshPackedBoxes();
    }

    public function handleBarcodeScan($barcode)
    {
        $this->scanInput = $barcode;
        $this->scanProduct();
    }

    public function scanProduct()
    {
        $input = trim($this->scanInput);
        if (empty($input)) {
            return;
        }

        // Try as product barcode
        $product = Product::where('barcode', $input)->first();

        if (!$product) {
            session()->flash('scan_error', "No product found with barcode: {$input}");
            $this->dispatch('scan-error', message: "Not found: {$input}");
            $this->scanInput = '';
            return;
        }

        // Verify product is in this transfer's items
        $transferItem = $this->transfer->items()->where('product_id', $product->id)->first();
        if (!$transferItem) {
            session()->flash('scan_error', "{$product->name} is not requested in this transfer");
            $this->scanInput = '';
            return;
        }

        // Count how many boxes are available at the warehouse for this product
        // (excluding boxes already assigned to this transfer)
        $alreadyAssigned = TransferBox::where('transfer_id', $this->transfer->id)->pluck('box_id');

        $available = \App\Models\Box::where('product_id', $product->id)
            ->where('location_type', 'warehouse')
            ->where('location_id', $this->transfer->from_warehouse_id)
            ->whereIn('status', ['full', 'partial'])
            ->where('items_remaining', '>', 0)
            ->whereNotIn('id', $alreadyAssigned)
            ->count();

        if ($available === 0) {
            session()->flash('scan_error', "No boxes of {$product->name} available at this warehouse");
            $this->scanInput = '';
            return;
        }

        // Calculate how many more boxes are still needed for this transfer item
        $alreadyPacked = TransferBox::where('transfer_id', $this->transfer->id)
            ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
            ->count();

        $totalBoxesRequested = (int) ($transferItem->quantity_requested / $product->items_per_box);
        $stillNeeded = max(0, $totalBoxesRequested - $alreadyPacked);

        $this->pendingBarcode         = $input;
        $this->pendingProductName     = $product->name;
        $this->pendingAvailableCount  = min($available, $stillNeeded > 0 ? $stillNeeded : $available);
        $this->scanQuantity           = min(1, $this->pendingAvailableCount);
        $this->scanInput              = '';
    }

    public function confirmQuantity()
    {
        if (!$this->pendingBarcode) {
            return;
        }

        if ($this->scanQuantity < 1 || $this->scanQuantity > $this->pendingAvailableCount) {
            session()->flash('scan_error', "Quantity must be between 1 and {$this->pendingAvailableCount}");
            return;
        }

        try {
            $transferService = app(TransferService::class);
            $transferService->packBoxesByProductBarcode(
                $this->transfer,
                $this->pendingBarcode,
                $this->scanQuantity
            );

            $this->refreshPackedBoxes();

            session()->flash('scan_success', "Packed {$this->scanQuantity} box(es) of {$this->pendingProductName}");
            $this->dispatch('scan-success', message: "Packed: {$this->pendingProductName}");
        } catch (\Exception $e) {
            session()->flash('scan_error', $e->getMessage());
        }

        $this->pendingBarcode         = null;
        $this->pendingProductName     = null;
        $this->pendingAvailableCount  = null;
        $this->scanQuantity           = 1;
    }

    public function cancelPending()
    {
        $this->pendingBarcode         = null;
        $this->pendingProductName     = null;
        $this->pendingAvailableCount  = null;
        $this->scanQuantity           = 1;
    }

    /**
     * After boxes are packed, ship the transfer (allows partial shipments).
     */
    public function shipTransfer()
    {
        // Require at least one box to be packed
        if (empty($this->packedBoxes)) {
            session()->flash('scan_error', 'Please pack at least one box before shipping');
            return;
        }

        // Validate transporter is selected
        $this->validate();

        try {
            $transferService = app(TransferService::class);

            // Check for discrepancies (products with fewer boxes than requested)
            $discrepancies = [];
            foreach ($this->transfer->items as $item) {
                $product = $item->product;
                $boxesAssigned = TransferBox::where('transfer_id', $this->transfer->id)
                    ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
                    ->count();

                $boxesNeeded = (int) ($item->quantity_requested / $product->items_per_box);

                if ($boxesAssigned < $boxesNeeded) {
                    $remaining = $boxesNeeded - $boxesAssigned;
                    $discrepancies[] = "{$product->name}: {$remaining} box(es) short";
                }
            }

            // Update transporter before shipping
            $this->transfer->update(['transporter_id' => $this->transporter_id]);

            // Mark as shipped (partial shipments allowed)
            $transferService->markAsShipped($this->transfer);
            $this->transfer->refresh();

            // Create alert for shop manager
            $shopManager = $this->transfer->toShop->manager ?? null;
            if ($shopManager) {
                \App\Models\Alert::create([
                    'title' => 'Transfer Shipped - Ready to Receive',
                    'message' => "Transfer {$this->transfer->transfer_number} has been shipped from warehouse and is on the way.",
                    'severity' => \App\Enums\AlertSeverity::WARNING,
                    'entity_type' => 'transfer',
                    'entity_id' => $this->transfer->id,
                    'user_id' => $shopManager->id,
                    'action_url' => route('shop.transfers.receive', $this->transfer),
                    'action_label' => 'Receive Transfer',
                ]);
            }

            $message = "Transfer {$this->transfer->transfer_number} shipped successfully";
            if (!empty($discrepancies)) {
                $message .= " with discrepancies: " . implode(', ', $discrepancies);
            }

            session()->flash('success', $message);

            return redirect()->route('warehouse.transfers.index');
        } catch (\Exception $e) {
            session()->flash('scan_error', $e->getMessage());
        }
    }

    private function refreshPackedBoxes(): void
    {
        $this->packedBoxes = [];
        $transferBoxes = TransferBox::where('transfer_id', $this->transfer->id)
            ->with('box.product')
            ->get();

        foreach ($transferBoxes as $tb) {
            $this->packedBoxes[] = [
                'box_id'       => $tb->box->id,
                'box_code'     => $tb->box->box_code,
                'product_name' => $tb->box->product->name,
                'items'        => $tb->box->items_remaining,
                'scanned_out'  => $tb->scanned_out_at !== null,
            ];
        }
    }

    public function render()
    {
        $this->transfer->load(['items.product', 'boxes.box.product']);

        // Build a summary: for each transfer item, how many boxes packed vs needed
        $packingSummary = [];
        foreach ($this->transfer->items as $item) {
            $product = $item->product;
            $boxesNeeded = (int) ($item->quantity_requested / $product->items_per_box);
            $boxesPacked = TransferBox::where('transfer_id', $this->transfer->id)
                ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
                ->count();

            $packingSummary[] = [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'barcode'      => $product->barcode,
                'boxes_needed' => $boxesNeeded,
                'boxes_packed' => $boxesPacked,
                'complete'     => $boxesPacked >= $boxesNeeded,
            ];
        }

        // Get active transporters
        $transporters = \App\Models\Transporter::active()->orderBy('name')->get();

        return view('livewire.inventory.transfers.pack-transfer', [
            'packingSummary' => $packingSummary,
            'transporters' => $transporters,
        ]);
    }
}
