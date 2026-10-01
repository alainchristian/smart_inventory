<?php

namespace App\Livewire\Inventory\Transfers;

use App\Models\Box;
use App\Models\Product;
use App\Models\ScannerSession;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Services\Inventory\TransferService;
use Livewire\Component;

class PackTransfer extends Component
{
    public Transfer $transfer;
    public string $scanInput = '';
    public int $scanQuantity = 1;
    public ?int $pendingProductId = null;
    public ?string $pendingProductName = null;
    public ?int $pendingAvailableCount = null;
    public array $packedBoxes = [];
    public bool $enableScanner = true;
    public ?ScannerSession $scannerSession = null;
    public bool $showScannerQR = false;
    public bool $phoneConnected = false;
    public ?\Carbon\Carbon $lastPhoneActivity = null;

    // Quantity panel properties
    public bool $showQuantityPanel      = false;
    public int  $pendingQty             = 1;
    public int  $pendingMaxQty          = 0;
    public int  $pendingAlreadyAssigned = 0;

    /** "Packing done" sheet; reasons for products packed short, keyed by transfer_item id. */
    public bool $confirmFinish = false;
    public array $shortReasons = [];

    protected $listeners = [
        'barcode-scanned' => 'handleBarcodeScan',
    ];

    public function mount(Transfer $transfer)
    {
        $user = auth()->user();

        if (!$user->isWarehouseManager() && !$user->isOwner()) {
            abort(403, 'Only warehouse staff can pack transfers.');
        }

        if ($transfer->status !== \App\Enums\TransferStatus::APPROVED) {
            // Nothing to pack: show the transfer instead.
            $this->redirectRoute('warehouse.transfers.show', $transfer);
            return;
        }

        $this->transfer = $transfer;
        $this->refreshPackedBoxes();

        // Check for active scanner session
        $this->scannerSession = ScannerSession::active()
            ->where('transfer_id', $transfer->id)
            ->where('page_type', 'pack_transfer')
            ->where('user_id', auth()->id())
            ->first();

        if ($this->scannerSession) {
            $this->showScannerQR = true;
        }
    }

    public function handleBarcodeScan($barcode)
    {
        $this->scanInput = $barcode;
        $this->scanProduct();
    }

    /**
     * Scan: a product barcode opens the quantity prompt; otherwise the text is
     * tried as a box label (box code), which packs that exact box. Products
     * without a barcode can also be packed with the row's "Pack" button.
     */
    public function scanProduct(): void
    {
        $input = trim($this->scanInput);
        $this->scanInput = '';

        if ($input === '') {
            session()->flash('scan_error', 'Scan a product barcode or a box label.');
            return;
        }

        if ($product = Product::where('barcode', $input)->first()) {
            $this->openPackFor($product);
            return;
        }

        $box = Box::where('box_code', $input)->first();
        if (! $box) {
            session()->flash('scan_error', "Nothing found for {$input} — not a product barcode or a box label.");
            $this->dispatch('scan-error', message: "Not found: {$input}");
            return;
        }

        try {
            app(TransferService::class)->packBoxByBoxCode($this->transfer, $input);
            $this->refreshPackedBoxes();
            session()->flash('scan_success', "Packed box {$box->box_code} ({$box->product?->name}).");
            $this->dispatch('quantity-confirmed');
        } catch (\Exception $e) {
            session()->flash('scan_error', $e->getMessage());
        }
    }

    /** The row's "Pack" button: same prompt as a barcode scan. */
    public function packProduct(int $productId): void
    {
        if ($product = Product::find($productId)) {
            $this->openPackFor($product);
        }
    }

    protected function openPackFor(Product $product): void
    {
        $transferItem = $this->transfer->items()->where('product_id', $product->id)->first();
        if (! $transferItem) {
            session()->flash('scan_error', "{$product->name} is not on this transfer.");
            return;
        }

        $alreadyPacked = app(TransferService::class)->packedCount($this->transfer, $product->id);
        $remaining = max(0, $transferItem->boxesToSend() - $alreadyPacked);
        if ($remaining <= 0) {
            session()->flash('scan_error', "All approved boxes of {$product->name} are already packed.");
            return;
        }

        $this->pendingProductId       = $product->id;
        $this->pendingProductName     = $product->name;
        $this->pendingAlreadyAssigned = $alreadyPacked;
        $this->pendingMaxQty          = $remaining;
        $this->pendingQty             = 1;
        $this->showQuantityPanel      = true;
        $this->resetErrorBag();
    }

    public function confirmScannedQuantity(): void
    {
        $qty = (int) $this->pendingQty;

        if ($qty < 1) {
            $this->addError('pendingQty', 'Quantity must be at least 1.');
            return;
        }

        if ($qty > $this->pendingMaxQty) {
            $this->addError('pendingQty', "Cannot exceed {$this->pendingMaxQty} box(es) remaining for this product.");
            return;
        }

        $productId = $this->pendingProductId;
        $this->closeQuantityPanel();
        $this->packProductBoxes($productId, $qty);
        $this->dispatch('quantity-confirmed');
    }

    public function closeQuantityPanel(): void
    {
        $this->showQuantityPanel      = false;
        $this->pendingProductId       = null;
        $this->pendingProductName     = null;
        $this->pendingQty             = 1;
        $this->pendingMaxQty          = 0;
        $this->pendingAlreadyAssigned = 0;
        $this->resetErrorBag('pendingQty');
    }

    public function updatedPendingQty(): void
    {
        // Clamp to valid range in real time
        $qty = (int) $this->pendingQty;
        if ($qty < 1) {
            $this->pendingQty = 1;
        } elseif ($qty > $this->pendingMaxQty) {
            $this->pendingQty = $this->pendingMaxQty;
        }
    }

    protected function packProductBoxes(int $productId, int $quantity): void
    {
        try {
            app(TransferService::class)->packBoxesForProduct($this->transfer, $productId, $quantity);
            $this->refreshPackedBoxes();

            $name = Product::find($productId)?->name;
            session()->flash('scan_success', "Packed {$quantity} " . \Illuminate\Support\Str::plural('box', $quantity) . " of {$name}.");
            $this->dispatch('scan-success', message: "Packed: {$quantity}x {$name}");
            $this->dispatch('transfer-updated', transferId: $this->transfer->id);
        } catch (\Exception $e) {
            session()->flash('scan_error', $e->getMessage());
            $this->dispatch('scan-error', message: $e->getMessage());
        }
    }

    /** Take a wrongly packed box off the transfer (back on sale at the warehouse). */
    public function removeBox(int $boxId): void
    {
        try {
            app(TransferService::class)->unpackBox($this->transfer->fresh(), $boxId);
            $this->transfer->refresh();
            $this->refreshPackedBoxes();
            session()->flash('scan_success', 'Box removed. It is back in warehouse stock.');
        } catch (\Throwable $e) {
            session()->flash('scan_error', $e->getMessage());
        }
    }

    /** Open the "packing done" sheet (asks a reason for every product packed short). */
    public function openFinish(): void
    {
        if (empty($this->packedBoxes)) {
            session()->flash('scan_error', 'Pack at least one box first.');
            return;
        }
        $this->resetErrorBag();
        $this->confirmFinish = true;
    }

    /**
     * Packing is done: approved → ready. The transfer then waits for the
     * transporter; dispatch (hand-over, signature, instructions) happens on
     * the transfer page.
     */
    public function finishPacking()
    {
        $this->resetErrorBag();
        $this->transfer->refresh();
        foreach ($this->transfer->items as $item) {
            $short = $item->boxesToSend() - app(TransferService::class)->packedCount($this->transfer, $item->product_id);
            if ($short > 0 && trim((string) ($this->shortReasons[$item->id] ?? '')) === '') {
                $this->addError("shortReasons.{$item->id}", 'Say why it is short.');
            }
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        try {
            app(TransferService::class)->finishPacking($this->transfer, $this->shortReasons);
        } catch (\DomainException $e) {
            session()->flash('scan_error', $e->getMessage());
            $this->confirmFinish = false;
            return;
        }

        session()->flash('success', "{$this->transfer->transfer_number} is packed and ready to dispatch.");

        return redirect()->route('warehouse.transfers.show', $this->transfer);
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

    public function generateScannerSession()
    {
        // Deactivate any existing sessions for this transfer
        ScannerSession::where('transfer_id', $this->transfer->id)
            ->where('page_type', 'pack_transfer')
            ->where('user_id', auth()->id())
            ->update(['is_active' => false]);

        // Create new session
        $this->scannerSession = ScannerSession::create([
            'session_code' => ScannerSession::generateCode(),
            'user_id' => auth()->id(),
            'page_type' => 'pack_transfer',
            'transfer_id' => $this->transfer->id,
            'is_active' => true,
            'expires_at' => now()->addHours(2), // Session expires in 2 hours
        ]);

        $this->showScannerQR = true;
    }

    public function closeScannerSession()
    {
        if ($this->scannerSession) {
            $this->scannerSession->deactivate();
            $this->scannerSession = null;
        }
        $this->showScannerQR = false;
        $this->phoneConnected = false;
        $this->lastPhoneActivity = null;
    }

    public function checkForScans()
    {
        if (!$this->scannerSession) {
            return;
        }

        $this->scannerSession->refresh();

        // Check if phone has been active recently (within last 10 seconds)
        if ($this->scannerSession->last_scan_at &&
            $this->scannerSession->last_scan_at->gt(now()->subSeconds(10))) {
            $this->phoneConnected = true;
            $this->lastPhoneActivity = $this->scannerSession->last_scan_at;
        } elseif ($this->phoneConnected &&
                  $this->lastPhoneActivity &&
                  $this->lastPhoneActivity->lt(now()->subSeconds(30))) {
            // Phone hasn't scanned in 30 seconds, mark as potentially disconnected
            $this->phoneConnected = false;
        }

        // Check for new scans
        if ($this->scannerSession->last_scanned_barcode &&
            $this->scannerSession->last_scan_at &&
            $this->scannerSession->last_scan_at->isAfter(now()->subSeconds(3))) {

            // New scan detected
            $barcode = $this->scannerSession->last_scanned_barcode;

            // Mark phone as connected when scan is received
            $this->phoneConnected = true;
            $this->lastPhoneActivity = now();

            // Clear the barcode to avoid re-processing
            $this->scannerSession->update(['last_scanned_barcode' => null]);

            // Process the scan (your existing scan logic)
            $this->scanInput = $barcode;
            $this->scanProduct();
        }

        // Check if session has expired
        if ($this->scannerSession->expires_at->isPast()) {
            $this->phoneConnected = false;
            session()->flash('info', 'Scanner session expired. Please reconnect your phone.');
        }
    }

    public function render()
    {
        $this->transfer->load(['items.product', 'boxes.box.product']);

        // Build a summary: for each transfer item, how many boxes packed vs needed
        $packingSummary = [];
        foreach ($this->transfer->items as $item) {
            $product = $item->product;
            $boxesNeeded = $item->boxesToSend();
            $boxesPacked = TransferBox::where('transfer_id', $this->transfer->id)
                ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
                ->count();

            $packingSummary[] = [
                'item_id'      => $item->id,
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'barcode'      => $product->barcode,
                'boxes_needed' => $boxesNeeded,
                'boxes_packed' => $boxesPacked,
                'complete'     => $boxesPacked >= $boxesNeeded,
            ];
        }


        return view('livewire.inventory.transfers.pack-transfer', [
            'packingSummary' => $packingSummary,
        ]);
    }
}
