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
    public Transfer $transfer;
    public string $scanInput = '';
    public int $scanQuantity = 1;
    public ?string $pendingBarcode = null;
    public ?string $pendingProductName = null;
    public ?int $pendingAvailableCount = null;
    public array $scannedBoxes = [];
    public bool $enableScanner = true;
    public ?ScannerSession $scannerSession = null;
    public bool $showScannerQR = false;

    protected $listeners = [
        'barcode-scanned' => 'handleBarcodeScan',
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
            abort(403, 'You can only receive transfers for your shop.');
        }

        // Only in-transit or delivered transfers can be received
        if (!in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED])) {
            abort(403, 'Only in-transit or delivered transfers can be received.');
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

    public function scanBox()
    {
        $input = trim($this->scanInput);
        if (empty($input)) {
            return;
        }

        // --- Try as internal box_code first (legacy / direct lookup) ---
        $transferBox = $this->transfer->boxes()
            ->whereHas('box', fn ($q) => $q->where('box_code', $input))
            ->where('is_received', false)
            ->with('box.product')
            ->first();

        if ($transferBox) {
            $this->confirmSingleBox($transferBox);
            $this->scanInput = '';
            return;
        }

        // --- Try as product barcode ---
        $product = Product::where('barcode', $input)->first();
        if ($product) {
            // Count how many un-received boxes of this product exist in this transfer
            $available = $this->transfer->boxes()
                ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
                ->where('is_received', false)
                ->count();

            if ($available === 0) {
                session()->flash('scan_error', "All boxes of {$product->name} already received in this transfer");
                $this->scanInput = '';
                return;
            }

            // Show quantity input
            $this->pendingBarcode      = $input;
            $this->pendingProductName  = $product->name;
            $this->pendingAvailableCount = $available;
            $this->scanQuantity        = 1;
            $this->scanInput           = '';
            return;
        }

        // Nothing matched
        session()->flash('scan_error', "Not found: {$input}");
        $this->dispatch('scan-error', message: "Not found: {$input}");
        $this->scanInput = '';
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

        // Find un-received TransferBox rows for this product in this transfer
        $product = Product::where('barcode', $this->pendingBarcode)->first();
        $transferBoxes = $this->transfer->boxes()
            ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
            ->where('is_received', false)
            ->with('box.product')
            ->limit($this->scanQuantity)
            ->get();

        foreach ($transferBoxes as $tb) {
            $this->confirmSingleBox($tb);
        }

        // Clear pending state
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

    private function confirmSingleBox(TransferBox $transferBox): void
    {
        if (isset($this->scannedBoxes[$transferBox->box_id])) {
            return; // already in list
        }

        $this->scannedBoxes[$transferBox->box_id] = [
            'box_id'      => $transferBox->box_id,
            'box_code'    => $transferBox->box->box_code,
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
        return count($this->scannedBoxes);
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
    }

    public function checkForScans()
    {
        if (!$this->scannerSession) {
            return;
        }

        $this->scannerSession->refresh();

        if ($this->scannerSession->last_scanned_barcode &&
            $this->scannerSession->last_scan_at &&
            $this->scannerSession->last_scan_at->isAfter(now()->subSeconds(3))) {

            // New scan detected
            $barcode = $this->scannerSession->last_scanned_barcode;

            // Clear the barcode to avoid re-processing
            $this->scannerSession->update(['last_scanned_barcode' => null]);

            // Process the scan (your existing scan logic)
            $this->scanInput = $barcode;
            $this->scanBox();
        }
    }

    public function render()
    {
        return view('livewire.shop.transfers.receive-transfer', [
            'expectedBoxes' => $this->getExpectedBoxesCount(),
            'scannedCount' => $this->getScannedBoxesCount(),
            'remainingCount' => $this->getRemainingBoxesCount(),
            'damagedCount' => $this->getDamagedBoxesCount(),
            'progressPercentage' => $this->getProgressPercentage(),
        ]);
    }
}
