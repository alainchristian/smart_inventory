<?php

namespace App\Livewire\Inventory\Transfers;

use App\Models\Transfer;
use App\Services\Inventory\TransferService;
use Livewire\Component;

class ReceiveTransfer extends Component
{
    public Transfer $transfer;
    public string $scanInput = '';
    public array $scannedBoxes = [];
    public bool $enableScanner = true;

    protected $listeners = [
        'barcode-scanned' => 'handleBarcodeScan',
    ];

    public function mount(Transfer $transfer)
    {
        $this->authorize('receive', $transfer);
        $this->transfer = $transfer;
    }

    public function handleBarcodeScan($barcode)
    {
        $this->scanInput = $barcode;
        $this->scanBox();
    }

    public function scanBox()
    {
        $boxCode = trim($this->scanInput);

        if (empty($boxCode)) {
            return;
        }

        // Find box in transfer
        $transferBox = $this->transfer->boxes()
            ->whereHas('box', function ($q) use ($boxCode) {
                $q->where('box_code', $boxCode);
            })
            ->with('box.product')
            ->first();

        if (!$transferBox) {
            session()->flash('scan_error', "Box {$boxCode} not found in this transfer");
            $this->dispatch('scan-error', message: "Box not found: {$boxCode}");
            $this->scanInput = '';
            return;
        }

        // Check if already scanned
        if (isset($this->scannedBoxes[$transferBox->box_id])) {
            session()->flash('scan_warning', "Box {$boxCode} already scanned");
            $this->dispatch('scan-warning', message: "Already scanned: {$boxCode}");
            $this->scanInput = '';
            return;
        }

        // Add to scanned list
        $this->scannedBoxes[$transferBox->box_id] = [
            'box_id' => $transferBox->box_id,
            'box_code' => $boxCode,
            'product_name' => $transferBox->box->product->name,
            'items' => $transferBox->box->items_remaining,
            'is_damaged' => false,
            'damage_notes' => null,
        ];

        session()->flash('scan_success', "Box {$boxCode} scanned successfully");
        $this->dispatch('scan-success', message: "Scanned: {$boxCode}");
        $this->scanInput = '';
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

            $transferService->receiveTransfer(
                $this->transfer,
                array_values($this->scannedBoxes)
            );

            session()->flash('success', 'Transfer received successfully');

            return redirect()->route('transfers.index');
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

    public function render()
    {
        return view('livewire.inventory.transfers.receive-transfer', [
            'expectedBoxes' => $this->getExpectedBoxesCount(),
            'scannedCount' => $this->getScannedBoxesCount(),
            'remainingCount' => $this->getRemainingBoxesCount(),
            'damagedCount' => $this->getDamagedBoxesCount(),
            'progressPercentage' => $this->getProgressPercentage(),
        ]);
    }
}
