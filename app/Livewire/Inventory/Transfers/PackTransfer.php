<?php

namespace App\Livewire\Inventory\Transfers;

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
    public ?string $pendingBarcode = null;
    public ?string $pendingProductName = null;
    public ?int $pendingAvailableCount = null;
    public array $packedBoxes = [];
    public bool $enableScanner = true;
    public ?int $transporter_id = null;
    public string $transporterInput = '';
    public ?ScannerSession $scannerSession = null;
    public bool $showScannerQR = false;
    public bool $phoneConnected = false;
    public ?\Carbon\Carbon $lastPhoneActivity = null;

    // Quantity panel properties
    public bool $showQuantityPanel      = false;
    public int  $pendingQty             = 1;
    public int  $pendingMaxQty          = 0;
    public int  $pendingAlreadyAssigned = 0;

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
            session()->flash('error', "Transfer {$transfer->transfer_number} is {$transfer->status->label()} and cannot be packed.");
            redirect()->route('warehouse.transfers.index')->dispatch();
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

    public function scanProduct(): void
    {
        $input = trim($this->scanInput);

        if (empty($input)) {
            session()->flash('scan_error', 'Please enter a barcode.');
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

        // Count already packed boxes for this product
        $alreadyPacked = TransferBox::where('transfer_id', $this->transfer->id)
            ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
            ->count();

        $totalBoxesRequested = (int) $transferItem->quantity_requested;
        $remaining = max(0, $totalBoxesRequested - $alreadyPacked);

        if ($remaining <= 0) {
            session()->flash('scan_error', "All requested boxes for {$product->name} have already been packed");
            $this->scanInput = '';
            return;
        }

        // Open quantity panel
        $this->pendingBarcode         = $input;
        $this->pendingProductName     = $product->name;
        $this->pendingAlreadyAssigned = $alreadyPacked;
        $this->pendingMaxQty          = $remaining;
        $this->pendingQty             = 1;
        $this->showQuantityPanel      = true;
        $this->scanInput              = '';
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

        $barcode = $this->pendingBarcode;
        $this->closeQuantityPanel();
        $this->packProductBoxes($barcode, $qty);
        $this->dispatch('quantity-confirmed');
    }

    public function closeQuantityPanel(): void
    {
        $this->showQuantityPanel      = false;
        $this->pendingBarcode         = null;
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

    protected function packProductBoxes(string $barcode, int $quantity)
    {
        try {
            $transferService = app(TransferService::class);
            $transferService->packBoxesByProductBarcode(
                $this->transfer,
                $barcode,
                $quantity
            );

            $this->refreshPackedBoxes();

            $product = Product::where('barcode', $barcode)->first();
            session()->flash('scan_success', "Packed {$quantity} box(es) of {$product->name}");
            $this->dispatch('scan-success', message: "Packed: {$quantity}x {$product->name}");

            // Dispatch event to notify shop manager of updates
            $this->dispatch('transfer-updated', transferId: $this->transfer->id);
        } catch (\Exception $e) {
            session()->flash('scan_error', $e->getMessage());
            $this->dispatch('scan-error', message: $e->getMessage());
        }
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

        // Resolve transporter: find existing by name or create new with defaults
        $name = trim($this->transporterInput);
        if (empty($name)) {
            $this->addError('transporterInput', 'Please select or enter a transporter name.');
            return;
        }
        $transporter = \App\Models\Transporter::firstOrCreate(
            ['name' => $name],
            ['is_active' => true, 'phone' => '']
        );
        $this->transporter_id = $transporter->id;

        try {
            $transferService = app(TransferService::class);

            // Check for discrepancies (products with fewer boxes than requested)
            $discrepancies = [];
            foreach ($this->transfer->items as $item) {
                $product = $item->product;
                $boxesAssigned = TransferBox::where('transfer_id', $this->transfer->id)
                    ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
                    ->count();

                $boxesNeeded = (int) $item->quantity_requested;

                if ($boxesAssigned < $boxesNeeded) {
                    $remaining = $boxesNeeded - $boxesAssigned;
                    $discrepancies[] = "{$product->name}: {$remaining} box(es) short";
                }
            }

            // Mark as shipped — pass transporter ID directly so markAsShipped doesn't null it out
            $transferService->markAsShipped($this->transfer, $this->transporter_id);
            $this->transfer->refresh();

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
            $boxesNeeded = (int) $item->quantity_requested;
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
