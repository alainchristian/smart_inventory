<?php

namespace App\Livewire\Shop\Transfers;

use App\Enums\AlertSeverity;
use App\Enums\TransferStatus;
use App\Models\Alert;
use App\Models\Box;
use App\Models\Product;
use App\Models\ScannerSession;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Services\Inventory\TransferService;
use Livewire\Component;

class ReceiveTransfer extends Component
{
    use \App\Livewire\Concerns\RequiresOpenSession;

    public Transfer $transfer;
    public string $scanInput = '';
    public array $scannedBoxes = [];
    public bool $enableScanner = true;
    public ?ScannerSession $scannerSession = null;
    public bool $showScannerQR = false;
    public bool $phoneConnected = false;
    public ?\Carbon\Carbon $lastPhoneActivity = null;

    // Quantity panel properties
    public bool    $showQuantityPanel      = false;
    public ?string $pendingBoxCode         = null;
    public ?int    $pendingProductId       = null;
    public string  $pendingProductName     = '';
    public int     $pendingQty             = 1;
    public int     $pendingMaxQty          = 0;
    public int     $pendingAlreadyScanned  = 0;

    protected $listeners = [
        'barcode-scanned' => 'handleBarcodeScan',
    ];

    public function mount(Transfer $transfer)
    {
        $shopId = $this->shopId ?? auth()->user()->location_id;
        if (!$this->checkSession($shopId)) {
            return;
        }

        $user = auth()->user();

        // Verify user is a shop manager
        if (!$user->isShopManager()) {
            abort(403, 'Only shop managers can access this page.');
        }

        // Verify this transfer is for this shop
        if ($transfer->to_shop_id !== $user->location_id) {
            session()->flash('error', 'This transfer is not for your shop.');
            redirect()->route('shop.transfers.index')->dispatch();
            return;
        }

        // Only in-transit or delivered transfers can be received
        if (!in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED])) {
            session()->flash('error', 'Only in-transit or delivered transfers can be received.');
            redirect()->route('shop.transfers.index')->dispatch();
            return;
        }

        $this->transfer = $transfer;

        // Check for active scanner session
        $this->scannerSession = ScannerSession::active()
            ->where('transfer_id', $transfer->id)
            ->where('page_type', 'receive_transfer')
            ->where('user_id', auth()->id())
            ->first();

        if ($this->scannerSession) {
            $this->showScannerQR = true;
        }
    }

    public function handleBarcodeScan($barcode)
    {
        $this->scanInput = $barcode;
        $this->scanBox();
    }

    public function scanBox(): void
    {
        $input = trim($this->scanInput);

        if (empty($input)) {
            return;
        }

        $alreadyScannedBoxIds = array_keys($this->scannedBoxes);

        // --- Try as individual box_code first ---
        $transferBox = $this->transfer->boxes()
            ->whereHas('box', fn ($q) => $q->where('box_code', $input))
            ->where('is_received', false)
            ->whereNotIn('box_id', $alreadyScannedBoxIds)
            ->with('box.product')
            ->first();

        if ($transferBox) {
            // Single specific box scanned — open popup defaulting to 1
            $product   = $transferBox->box->product;
            $productId = $product->id;

            $alreadyScanned = collect($this->scannedBoxes)
                ->where('product_id', $productId)
                ->count();

            $remaining = $this->transfer->boxes()
                ->whereHas('box', fn ($q) => $q->where('product_id', $productId))
                ->where('is_received', false)
                ->whereNotIn('box_id', $alreadyScannedBoxIds)
                ->count();

            $this->pendingBoxCode        = $input;
            $this->pendingProductId      = $productId;
            $this->pendingProductName    = $product->name;
            $this->pendingAlreadyScanned = $alreadyScanned;
            $this->pendingMaxQty         = $remaining;
            $this->pendingQty            = 1;
            $this->showQuantityPanel     = true;
            $this->scanInput             = '';
            $this->resetErrorBag();
            return;
        }

        // --- Try as product barcode ---
        $product = \App\Models\Product::where('barcode', $input)->first();

        if ($product) {
            $available = $this->transfer->boxes()
                ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
                ->where('is_received', false)
                ->whereNotIn('box_id', $alreadyScannedBoxIds)
                ->count();

            if ($available === 0) {
                session()->flash('scan_error', "All boxes of {$product->name} already scanned.");
                $this->scanInput = '';
                return;
            }

            $alreadyScanned = collect($this->scannedBoxes)
                ->where('product_id', $product->id)
                ->count();

            $this->pendingBoxCode        = $input;
            $this->pendingProductId      = $product->id;
            $this->pendingProductName    = $product->name;
            $this->pendingAlreadyScanned = $alreadyScanned;
            $this->pendingMaxQty         = $available;
            $this->pendingQty            = 1;
            $this->showQuantityPanel     = true;
            $this->scanInput             = '';
            $this->resetErrorBag();
            return;
        }

        // Nothing matched
        session()->flash('scan_error', "Not found: {$input}");
        $this->dispatch('scan-error', message: "Not found: {$input}");
        $this->scanInput = '';
    }

    public function updatedPendingQty(): void
    {
        $qty = (int) $this->pendingQty;
        if ($qty < 1) {
            $this->pendingQty = 1;
        } elseif ($qty > $this->pendingMaxQty) {
            $this->pendingQty = $this->pendingMaxQty;
        }
    }

    public function confirmScannedQuantity(): void
    {
        $qty = (int) $this->pendingQty;

        if ($qty < 1) {
            $this->addError('pendingQty', 'Quantity must be at least 1.');
            return;
        }

        if ($qty > $this->pendingMaxQty) {
            $this->addError('pendingQty', "Cannot exceed {$this->pendingMaxQty} box(es) for this product.");
            return;
        }

        $alreadyScannedBoxIds = array_keys($this->scannedBoxes);

        // Find $qty un-received TransferBox rows for this product
        $transferBoxes = $this->transfer->boxes()
            ->whereHas('box', fn ($q) => $q->where('product_id', $this->pendingProductId))
            ->where('is_received', false)
            ->whereNotIn('box_id', $alreadyScannedBoxIds)
            ->with('box.product')
            ->limit($qty)
            ->get();

        if ($transferBoxes->isEmpty()) {
            $this->addError('pendingQty', 'No boxes found to confirm.');
            return;
        }

        foreach ($transferBoxes as $tb) {
            $this->confirmSingleBox($tb);
        }

        $productName = $this->pendingProductName;
        $count       = $transferBoxes->count();
        $this->closeQuantityPanel();

        session()->flash('scan_success', "{$count} box(es) of '{$productName}' confirmed.");
        $this->dispatch('quantity-confirmed');
    }

    public function closeQuantityPanel(): void
    {
        $this->showQuantityPanel     = false;
        $this->pendingBoxCode        = null;
        $this->pendingProductId      = null;
        $this->pendingProductName    = '';
        $this->pendingQty            = 1;
        $this->pendingMaxQty         = 0;
        $this->pendingAlreadyScanned = 0;
        $this->resetErrorBag('pendingQty');
    }

    private function confirmSingleBox(TransferBox $transferBox): void
    {
        if (isset($this->scannedBoxes[$transferBox->box_id])) {
            return; // already in list
        }

        $this->scannedBoxes[$transferBox->box_id] = [
            'box_id'      => $transferBox->box_id,
            'box_code'    => $transferBox->box->box_code,
            'product_id'  => $transferBox->box->product_id,
            'product_name'=> $transferBox->box->product->name,
            'items'       => $transferBox->box->items_remaining,
            'is_damaged'  => false,
            'damage_notes'=> null,
        ];

        session()->flash('scan_success', "Box confirmed: {$transferBox->box->product->name}");
        $this->dispatch('scan-success', message: "Confirmed: {$transferBox->box->product->name}");
    }

    public function markAsDamaged($boxId, $isDamaged = true)
    {
        if (isset($this->scannedBoxes[$boxId])) {
            $this->scannedBoxes[$boxId]['is_damaged'] = $isDamaged;

            if (!$isDamaged) {
                $this->scannedBoxes[$boxId]['damage_notes'] = null;
            }
        }
    }

    public function updateDamageNotes($boxId, $notes)
    {
        if (isset($this->scannedBoxes[$boxId])) {
            $this->scannedBoxes[$boxId]['damage_notes'] = $notes;
        }
    }

    public function removeScannedBox($boxId)
    {
        unset($this->scannedBoxes[$boxId]);
        session()->flash('info', 'Box removed from scanned list');
    }

    public function completeReceipt()
    {
        if (empty($this->scannedBoxes)) {
            session()->flash('error', 'Please scan at least one box');
            return;
        }

        // Validate damage notes for damaged boxes
        foreach ($this->scannedBoxes as $box) {
            if ($box['is_damaged'] && empty($box['damage_notes'])) {
                session()->flash('error', 'Please provide damage notes for all damaged boxes');
                return;
            }
        }

        try {
            $transferService = app(TransferService::class);

            // First mark as delivered if not already
            if ($this->transfer->status === TransferStatus::IN_TRANSIT) {
                $transferService->markAsDelivered($this->transfer);
            }

            $transferService->receiveTransfer(
                $this->transfer,
                array_values($this->scannedBoxes)
            );

            // Create alert for warehouse manager if there are discrepancies
            $expectedCount = $this->transfer->boxes()->count();
            $hasDiscrepancies = count($this->scannedBoxes) < $expectedCount;
            $hasDamagedBoxes = collect($this->scannedBoxes)->contains('is_damaged', true);

            if ($hasDiscrepancies || $hasDamagedBoxes) {
                $warehouseManagerUser = $this->transfer->fromWarehouse->manager ?? null;

                if ($warehouseManagerUser) {
                    $message = "Transfer {$this->transfer->transfer_number} received with issues: ";
                    $issues = [];

                    if ($hasDiscrepancies) {
                        $missing = $expectedCount - count($this->scannedBoxes);
                        $issues[] = "{$missing} missing boxes";
                    }

                    if ($hasDamagedBoxes) {
                        $damaged = collect($this->scannedBoxes)->where('is_damaged', true)->count();
                        $issues[] = "{$damaged} damaged boxes";
                    }

                    $message .= implode(', ', $issues) . '.';

                    Alert::create([
                        'title' => 'Transfer Received with Issues',
                        'message' => $message,
                        'severity' => AlertSeverity::WARNING,
                        'entity_type' => 'transfer',
                        'entity_id' => $this->transfer->id,
                        'user_id' => $warehouseManagerUser->id,
                        'action_url' => route('warehouse.transfers.show', $this->transfer),
                        'action_label' => 'View Transfer',
                    ]);
                }
            }

            session()->flash('success', 'Transfer received successfully');

            return redirect()->route('shop.transfers.index');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function getExpectedBoxesCount(): int
    {
        return $this->transfer->boxes()->count();
    }

    public function getScannedBoxesCount(): int
    {
        // Count boxes already received in database
        $receivedInDb = $this->transfer->boxes()->where('is_received', true)->count();

        // Add currently scanned boxes (not yet persisted)
        return $receivedInDb + count($this->scannedBoxes);
    }

    public function getRemainingBoxesCount(): int
    {
        return $this->getExpectedBoxesCount() - $this->getScannedBoxesCount();
    }

    public function getDamagedBoxesCount(): int
    {
        return collect($this->scannedBoxes)->where('is_damaged', true)->count();
    }

    public function getProgressPercentage(): float
    {
        $expected = $this->getExpectedBoxesCount();
        if ($expected === 0) {
            return 0;
        }

        return ($this->getScannedBoxesCount() / $expected) * 100;
    }

    public function generateScannerSession()
    {
        // Deactivate any existing sessions for this transfer
        ScannerSession::where('transfer_id', $this->transfer->id)
            ->where('page_type', 'receive_transfer')
            ->where('user_id', auth()->id())
            ->update(['is_active' => false]);

        // Create new session
        $this->scannerSession = ScannerSession::create([
            'session_code' => ScannerSession::generateCode(),
            'user_id' => auth()->id(),
            'page_type' => 'receive_transfer',
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
            $this->scanBox();
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

        // Build a summary: for each transfer item, how many boxes received vs shipped
        // Create a map of box_id => product_id from transfer boxes for quick lookup
        $boxProductMap = [];
        foreach ($this->transfer->boxes as $tb) {
            $boxProductMap[$tb->box->id] = $tb->box->product_id;
        }

        $receivingSummary = [];
        foreach ($this->transfer->items as $item) {
            $product = $item->product;
            $boxesShipped = TransferBox::where('transfer_id', $this->transfer->id)
                ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
                ->count();

            // Count boxes already marked as received in database
            $boxesReceivedInDb = TransferBox::where('transfer_id', $this->transfer->id)
                ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
                ->where('is_received', true)
                ->count();

            // Count boxes currently scanned (in scannedBoxes array) for this product
            $boxesScannedNow = 0;
            foreach ($this->scannedBoxes as $scannedBox) {
                $boxProductId = $boxProductMap[$scannedBox['box_id']] ?? null;
                if ($boxProductId === $product->id) {
                    $boxesScannedNow++;
                }
            }

            // Total received = already received in DB + currently scanned (not yet persisted)
            $boxesReceived = $boxesReceivedInDb + $boxesScannedNow;

            $receivingSummary[] = [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'barcode'      => $product->barcode,
                'boxes_shipped' => $boxesShipped,
                'boxes_received' => $boxesReceived,
                'complete'     => $boxesReceived >= $boxesShipped,
            ];
        }

        // Split boxes into three ordered sections using direct DB queries
        // (same pattern as confirmScannedQuantity which already uses whereNotIn successfully)
        $scannedBoxIds = array_map('intval', array_keys($this->scannedBoxes));

        $pendingQuery = TransferBox::where('transfer_id', $this->transfer->id)
            ->where('is_received', false)
            ->with('box.product');
        if (!empty($scannedBoxIds)) {
            $pendingQuery->whereNotIn('box_id', $scannedBoxIds);
        }
        $pendingBoxes = $pendingQuery->get()->map(fn($tb) => [
            'box_id'       => (int) $tb->box_id,
            'box_code'     => $tb->box->box_code,
            'product_name' => $tb->box->product->name,
            'items'        => $tb->box->items_remaining,
        ])->values()->toArray();

        $receivedBoxes = TransferBox::where('transfer_id', $this->transfer->id)
            ->where('is_received', true)
            ->with('box.product')
            ->get()->map(fn($tb) => [
                'box_id'       => (int) $tb->box_id,
                'box_code'     => $tb->box->box_code,
                'product_name' => $tb->box->product->name,
                'items'        => $tb->box->items_remaining,
                'is_damaged'   => (bool) $tb->is_damaged,
            ])->values()->toArray();

        $sessionBoxes = array_values($this->scannedBoxes);

        return view('livewire.shop.transfers.receive-transfer', [
            'receivingSummary' => $receivingSummary,
            'pendingBoxes'     => $pendingBoxes,
            'sessionBoxes'     => $sessionBoxes,
            'receivedBoxes'    => $receivedBoxes,
            'expectedBoxes'    => $this->getExpectedBoxesCount(),
            'scannedCount'     => $this->getScannedBoxesCount(),
            'remainingCount'   => $this->getRemainingBoxesCount(),
            'damagedCount'     => $this->getDamagedBoxesCount(),
            'progressPercentage' => $this->getProgressPercentage(),
        ]);
    }
}
