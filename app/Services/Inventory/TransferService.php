<?php

namespace App\Services\Inventory;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Enums\TransferStatus;
use App\Events\Inventory\TransferApproved;
use App\Events\Inventory\TransferReceived;
use App\Events\Inventory\TransferRequested;
use App\Models\Box;
use App\Models\Transfer;
use App\Models\TransferBox;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function generateTransferNumber(): string
    {
        $yearMonth = now()->format('Y-m');
        $count = Transfer::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count() + 1;

        $sequence = str_pad($count, 5, '0', STR_PAD_LEFT);

        return "TR-{$yearMonth}-{$sequence}";
    }

    public function createTransferRequest(array $data): Transfer
    {
        return DB::transaction(function () use ($data) {
            $transfer = Transfer::create([
                'transfer_number' => $this->generateTransferNumber(),
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_shop_id' => $data['to_shop_id'],
                'status' => TransferStatus::PENDING,
                'requested_by' => auth()->id(),
                'requested_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity'],
                ]);
            }

            // Dispatch event if it exists
            if (class_exists(TransferRequested::class)) {
                event(new TransferRequested($transfer));
            }

            return $transfer;
        });
    }

    public function approveTransfer(Transfer $transfer, ?string $notes = null): Transfer
    {
        if ($transfer->status !== TransferStatus::PENDING) {
            throw new \Exception('Only pending transfers can be approved');
        }

        return DB::transaction(function () use ($transfer, $notes) {
            $transfer->update([
                'status' => TransferStatus::APPROVED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'notes' => $notes ?? $transfer->notes,
            ]);

            // Dispatch event if it exists
            if (class_exists(TransferApproved::class)) {
                event(new TransferApproved($transfer));
            }

            return $transfer;
        });
    }

    public function rejectTransfer(Transfer $transfer, string $reason): Transfer
    {
        if ($transfer->status !== TransferStatus::PENDING) {
            throw new \Exception('Only pending transfers can be rejected');
        }

        return DB::transaction(function () use ($transfer, $reason) {
            $transfer->update([
                'status' => TransferStatus::REJECTED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'notes' => $reason,
            ]);

            return $transfer;
        });
    }

    public function assignBoxesToTransfer(Transfer $transfer, array $boxAssignments): Transfer
    {
        if ($transfer->status !== TransferStatus::APPROVED) {
            throw new \Exception('Only approved transfers can have boxes assigned');
        }

        return DB::transaction(function () use ($transfer, $boxAssignments) {
            // Validate boxes exist and are available
            foreach ($boxAssignments as $assignment) {
                $box = Box::findOrFail($assignment['box_id']);

                if ($box->location_type !== LocationType::WAREHOUSE ||
                    $box->location_id !== $transfer->from_warehouse_id) {
                    throw new \Exception("Box {$box->box_code} is not in the source warehouse");
                }

                if (!in_array($box->status, [BoxStatus::FULL, BoxStatus::PARTIAL])) {
                    throw new \Exception("Box {$box->box_code} is not available for transfer");
                }
            }

            // Assign boxes
            foreach ($boxAssignments as $assignment) {
                TransferBox::create([
                    'transfer_id' => $transfer->id,
                    'box_id' => $assignment['box_id'],
                ]);

                // Update transfer item quantities
                $box = Box::find($assignment['box_id']);
                $transferItem = $transfer->items()
                    ->where('product_id', $box->product_id)
                    ->first();

                if ($transferItem) {
                    $transferItem->increment('quantity_shipped', $box->items_remaining);
                }
            }

            $transfer->update([
                'packed_by' => auth()->id(),
                'packed_at' => now(),
            ]);

            return $transfer;
        });
    }

    public function scanOutBox(Transfer $transfer, string $boxCode): TransferBox
    {
        return DB::transaction(function () use ($transfer, $boxCode) {
            $box = Box::where('box_code', $boxCode)->firstOrFail();

            $transferBox = TransferBox::where('transfer_id', $transfer->id)
                ->where('box_id', $box->id)
                ->firstOrFail();

            if ($transferBox->scanned_out_at) {
                throw new \Exception('Box already scanned out');
            }

            $transferBox->update([
                'scanned_out_by' => auth()->id(),
                'scanned_out_at' => now(),
            ]);

            return $transferBox;
        });
    }

    public function markAsShipped(Transfer $transfer, ?int $transporterId = null): Transfer
    {
        // Verify all boxes are scanned out
        $unscannedCount = $transfer->boxes()->whereNull('scanned_out_at')->count();

        if ($unscannedCount > 0) {
            throw new \Exception("{$unscannedCount} boxes have not been scanned out");
        }

        return DB::transaction(function () use ($transfer, $transporterId) {
            $transfer->update([
                'status' => TransferStatus::IN_TRANSIT,
                'transporter_id' => $transporterId,
                'shipped_at' => now(),
            ]);

            return $transfer;
        });
    }

    public function markAsDelivered(Transfer $transfer): Transfer
    {
        if ($transfer->status !== TransferStatus::IN_TRANSIT) {
            throw new \Exception('Only in-transit transfers can be marked as delivered');
        }

        $transfer->update([
            'status' => TransferStatus::DELIVERED,
            'delivered_at' => now(),
        ]);

        return $transfer;
    }

    public function receiveTransfer(Transfer $transfer, array $receivedBoxes): Transfer
    {
        if ($transfer->status !== TransferStatus::DELIVERED) {
            throw new \Exception('Only delivered transfers can be received');
        }

        return DB::transaction(function () use ($transfer, $receivedBoxes) {
            $hasDiscrepancy = false;

            foreach ($receivedBoxes as $received) {
                $transferBox = TransferBox::where('transfer_id', $transfer->id)
                    ->where('box_id', $received['box_id'])
                    ->firstOrFail();

                $transferBox->update([
                    'scanned_in_by' => auth()->id(),
                    'scanned_in_at' => now(),
                    'is_received' => true,
                    'is_damaged' => $received['is_damaged'] ?? false,
                    'damage_notes' => $received['damage_notes'] ?? null,
                ]);

                // Move box to shop
                $box = Box::find($received['box_id']);

                if ($received['is_damaged'] ?? false) {
                    $box->update(['status' => BoxStatus::DAMAGED]);
                    $hasDiscrepancy = true;
                } else {
                    $box->moveTo(
                        LocationType::SHOP,
                        $transfer->to_shop_id,
                        "Transfer received: {$transfer->transfer_number}",
                        $transfer->id,
                        'transfer'
                    );
                }

                // Update transfer item received quantity
                $transferItem = $transfer->items()
                    ->where('product_id', $box->product_id)
                    ->first();

                if ($transferItem) {
                    $transferItem->increment('quantity_received', $box->items_remaining);
                }
            }

            // Check for missing boxes
            $expectedBoxCount = $transfer->boxes()->count();
            $receivedBoxCount = count($receivedBoxes);

            if ($receivedBoxCount < $expectedBoxCount) {
                $hasDiscrepancy = true;
            }

            // Calculate discrepancies per item
            foreach ($transfer->items as $item) {
                $discrepancy = $item->quantity_received - $item->quantity_shipped;

                if ($discrepancy != 0) {
                    $item->update([
                        'discrepancy_quantity' => $discrepancy,
                        'discrepancy_reason' => $discrepancy < 0 ? 'Missing items' : 'Extra items',
                    ]);
                    $hasDiscrepancy = true;
                }
            }

            $transfer->update([
                'status' => TransferStatus::RECEIVED,
                'received_by' => auth()->id(),
                'received_at' => now(),
                'has_discrepancy' => $hasDiscrepancy,
            ]);

            // Dispatch event if it exists
            if (class_exists(TransferReceived::class)) {
                event(new TransferReceived($transfer));
            }

            return $transfer;
        });
    }

    public function cancelTransfer(Transfer $transfer, string $reason): Transfer
    {
        if (in_array($transfer->status, [TransferStatus::RECEIVED, TransferStatus::CANCELLED])) {
            throw new \Exception('Cannot cancel a received or already cancelled transfer');
        }

        return DB::transaction(function () use ($transfer, $reason) {
            $transfer->update([
                'status' => TransferStatus::CANCELLED,
                'notes' => ($transfer->notes ? $transfer->notes . "\n\n" : '') . "Cancelled: {$reason}",
            ]);

            // If boxes were assigned, unassign them
            if ($transfer->status === TransferStatus::IN_TRANSIT || $transfer->packed_at) {
                TransferBox::where('transfer_id', $transfer->id)->delete();
            }

            return $transfer;
        });
    }
}
