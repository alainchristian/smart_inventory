<?php

namespace App\Livewire\WarehouseManager\Transfers;

use App\Enums\AlertSeverity;
use App\Enums\BoxStatus;
use App\Enums\TransferStatus;
use App\Models\Alert;
use App\Models\Box;
use App\Models\Product;
use App\Models\ScannerSession;
use App\Models\Transfer;
use App\Models\Transporter;
use App\Services\Inventory\TransferService;
use Livewire\Component;

class PackTransfer extends Component
{
    public Transfer $transfer;
    public array $items = [];
    public array $assignedBoxes = [];
    public string $scanInput = '';
    public ?int $transporterId = null;
    public bool $showShipModal = false;

    // Scanner session properties
    public ?ScannerSession $scannerSession = null;
    public bool $showScannerQR = false;
    public bool $phoneConnected = false;
    public ?\Carbon\Carbon $lastPhoneActivity = null;

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

        // Only approved transfers can be packed
        if ($transfer->status !== TransferStatus::APPROVED) {
            abort(403, 'Only approved transfers can be packed.');
        }

        $this->transfer = $transfer;

        // Load items with boxes calculation
        foreach ($transfer->items as $item) {
            $product = $item->product;
            $boxesRequested = $item->quantity_requested / $product->items_per_box;

            $this->items[$item->id] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $product->name,
                'items_per_box' => $product->items_per_box,
                'boxes_requested' => $boxesRequested,
                'quantity_requested' => $item->quantity_requested,
                'boxes_assigned' => 0,
                'quantity_assigned' => 0,
            ];
        }

        // Load already assigned boxes (if any)
        $this->loadAssignedBoxes();

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

    protected function loadAssignedBoxes()
    {
        $this->assignedBoxes = [];

        foreach ($this->transfer->boxes as $transferBox) {
            $box = $transferBox->box;
            $this->assignedBoxes[$box->id] = [
                'id' => $box->id,
                'box_code' => $box->box_code,
                'product_id' => $box->product_id,
                'product_name' => $box->product->name,
                'items_remaining' => $box->items_remaining,
                'status' => $box->status->label(),
                'transfer_item_id' => null,
            ];

            // Update item stats
            foreach ($this->items as $itemId => &$item) {
                if ($item['product_id'] == $box->product_id) {
                    $item['boxes_assigned']++;
                    $item['quantity_assigned'] += $box->items_remaining;
                    $this->assignedBoxes[$box->id]['transfer_item_id'] = $itemId;
                    break;
                }
            }
        }
    }

    public function scanBox()
    {
        $boxCode = trim($this->scanInput);

        if (empty($boxCode)) {
            $this->addError('scanInput', 'Please enter a box code.');
            return;
        }

        // Find the box
        $box = Box::where('box_code', $boxCode)
            ->where('location_type', 'warehouse')
            ->where('location_id', $this->transfer->from_warehouse_id)
            ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
            ->first();

        if (!$box) {
            $this->addError('scanInput', "Box '{$boxCode}' not found or not available in this warehouse.");
            $this->scanInput = '';
            return;
        }

        // Check if box is for a product in this transfer
        $transferItemId = null;
        foreach ($this->items as $itemId => $item) {
            if ($item['product_id'] == $box->product_id) {
                $transferItemId = $itemId;
                break;
            }
        }

        if (!$transferItemId) {
            $this->addError('scanInput', "Box '{$boxCode}' contains {$box->product->name}, which is not in this transfer request.");
            $this->scanInput = '';
            return;
        }

        // Check if already assigned
        if (isset($this->assignedBoxes[$box->id])) {
            $this->addError('scanInput', "Box '{$boxCode}' is already assigned to this transfer.");
            $this->scanInput = '';
            return;
        }

        // Assign the box
        $this->assignBox($box, $transferItemId);

        session()->flash('success', "Box '{$boxCode}' assigned successfully.");
        $this->scanInput = '';
    }

    protected function assignBox(Box $box, int $transferItemId)
    {
        $this->assignedBoxes[$box->id] = [
            'id' => $box->id,
            'box_code' => $box->box_code,
            'product_id' => $box->product_id,
            'product_name' => $box->product->name,
            'items_remaining' => $box->items_remaining,
            'status' => $box->status->label(),
            'transfer_item_id' => $transferItemId,
        ];

        // Update item stats
        $this->items[$transferItemId]['boxes_assigned']++;
        $this->items[$transferItemId]['quantity_assigned'] += $box->items_remaining;
    }

    public function removeBox($boxId)
    {
        if (!isset($this->assignedBoxes[$boxId])) {
            return;
        }

        $assignedBox = $this->assignedBoxes[$boxId];
        $transferItemId = $assignedBox['transfer_item_id'];

        // Update item stats
        $this->items[$transferItemId]['boxes_assigned']--;
        $this->items[$transferItemId]['quantity_assigned'] -= $assignedBox['items_remaining'];

        // Remove box
        unset($this->assignedBoxes[$boxId]);

        session()->flash('success', "Box '{$assignedBox['box_code']}' removed from transfer.");
    }

    public function addBoxToProduct($productId)
    {
        // Find available boxes for this product
        $boxes = Box::where('product_id', $productId)
            ->where('location_type', 'warehouse')
            ->where('location_id', $this->transfer->from_warehouse_id)
            ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
            ->whereNotIn('id', array_keys($this->assignedBoxes))
            ->orderBy('status', 'desc') // Full boxes first
            ->orderBy('created_at', 'asc') // Oldest first (FIFO)
            ->limit(10)
            ->get();

        if ($boxes->isEmpty()) {
            session()->flash('error', 'No available boxes found for this product.');
            return;
        }

        // Find the transfer item
        $transferItemId = null;
        foreach ($this->items as $itemId => $item) {
            if ($item['product_id'] == $productId) {
                $transferItemId = $itemId;
                break;
            }
        }

        // Assign the first available box
        $box = $boxes->first();
        $this->assignBox($box, $transferItemId);

        session()->flash('success', "Box '{$box->box_code}' assigned successfully.");
    }

    public function openShipModal()
    {
        // Check if at least one box is assigned
        if (empty($this->assignedBoxes)) {
            session()->flash('error', 'Please assign at least one box before shipping.');
            return;
        }

        $this->showShipModal = true;
    }

    public function closeShipModal()
    {
        $this->showShipModal = false;
        $this->transporterId = null;
    }

    public function ship()
    {
        if (empty($this->assignedBoxes)) {
            $this->addError('assignedBoxes', 'Please assign at least one box before shipping.');
            return;
        }

        try {
            $transferService = app(TransferService::class);

            // Assign boxes to transfer in database
            $boxAssignments = [];
            foreach ($this->assignedBoxes as $boxId => $assignedBox) {
                $boxAssignments[] = ['box_id' => $boxId];
            }

            $transferService->assignBoxesToTransfer($this->transfer, $boxAssignments);

            // Scan out all boxes
            foreach ($this->assignedBoxes as $boxId => $assignedBox) {
                $transferService->scanOutBox($this->transfer, $assignedBox['box_code']);
            }

            // Mark as shipped
            $transferService->markAsShipped($this->transfer, $this->transporterId);

            // Create alert for shop manager
            $shopManagerUser = $this->transfer->requestedBy;
            Alert::create([
                'title' => 'Transfer Shipped - Action Required',
                'message' => "Transfer {$this->transfer->transfer_number} with " . count($this->assignedBoxes) . " boxes has been shipped to your shop. Please receive it when it arrives.",
                'severity' => AlertSeverity::WARNING,
                'entity_type' => 'transfer',
                'entity_id' => $this->transfer->id,
                'user_id' => $shopManagerUser->id,
                'action_url' => route('shop.transfers.receive', $this->transfer),
                'action_label' => 'Receive Transfer',
            ]);

            session()->flash('success', "Transfer {$this->transfer->transfer_number} has been shipped successfully.");
            return redirect()->route('warehouse.transfers.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error shipping transfer: ' . $e->getMessage());
            $this->closeShipModal();
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
            'expires_at' => now()->addHours(2),
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

            // Process the scan using existing scanBox logic
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
        // Get available boxes for each product
        $availableBoxes = [];
        foreach ($this->items as $item) {
            $boxes = Box::where('product_id', $item['product_id'])
                ->where('location_type', 'warehouse')
                ->where('location_id', $this->transfer->from_warehouse_id)
                ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
                ->whereNotIn('id', array_keys($this->assignedBoxes))
                ->orderBy('status', 'desc')
                ->orderBy('created_at', 'asc')
                ->limit(5)
                ->get();

            $availableBoxes[$item['product_id']] = $boxes;
        }

        $transporters = Transporter::where('is_active', true)->orderBy('name')->get();

        return view('livewire.warehouse-manager.transfers.pack-transfer', [
            'availableBoxes' => $availableBoxes,
            'transporters' => $transporters,
        ]);
    }
}
