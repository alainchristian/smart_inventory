<?php

namespace App\Services\Inventory;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Enums\TransferStatus;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Box;
use App\Models\Product;
use App\Models\Transfer;
use App\Models\TransferBox;
use App\Models\TransferEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The transfer process (see CLAUDE.md "Transfer process"):
 * requested → approved → (packing) → ready → in_transit → delivered → received → closed,
 * with rejected (from pending) and cancelled (before dispatch) as dead ends.
 * Every step goes through assertCan() (status + role) and record() (history).
 */
class TransferService
{
    /** step => [statuses it may start from, roles allowed]. Roles are relative to the transfer. */
    public const STEPS = [
        'approve'        => [['pending'], ['owner', 'warehouse']],
        'reject'         => [['pending'], ['owner', 'warehouse']],
        'pack'           => [['approved'], ['owner', 'warehouse']],
        'finish_packing' => [['approved'], ['owner', 'warehouse']],
        'reopen_packing' => [['ready'], ['owner', 'warehouse']],
        // Dispatching straight from approved finishes packing on the way.
        'dispatch'       => [['ready', 'approved'], ['owner', 'warehouse']],
        'arrive'         => [['in_transit'], ['shop']],
        'receive'        => [['delivered', 'in_transit'], ['shop']],
        // The shop may only withdraw its own request before it is approved.
        'cancel'         => [['pending', 'approved', 'ready'], ['owner', 'warehouse', 'shop']],
        'resolve'        => [['received'], ['owner', 'warehouse']],
    ];

    /** owner | warehouse | shop | null — the user's part in this transfer. */
    public function roleFor(?User $user, Transfer $transfer): ?string
    {
        return match (true) {
            ! $user                                                                    => null,
            $user->isOwner() || $user->isAdmin()                                       => 'owner',
            $user->isWarehouseManager() && (int) $user->location_id === (int) $transfer->from_warehouse_id => 'warehouse',
            $user->isShopManager() && (int) $user->location_id === (int) $transfer->to_shop_id => 'shop',
            default                                                                    => null,
        };
    }

    /** Whether $user (default: the signed-in user) may take $step on $transfer now. */
    public function can(string $step, Transfer $transfer, ?User $user = null): bool
    {
        [$from, $roles] = self::STEPS[$step];
        $role = $this->roleFor($user ?? auth()->user(), $transfer);

        if (! in_array($transfer->status->value, $from, true) || ! in_array($role, $roles, true)) {
            return false;
        }

        return ! ($step === 'cancel' && $role === 'shop' && $transfer->status !== TransferStatus::PENDING);
    }

    public function assertCan(string $step, Transfer $transfer): void
    {
        if ($this->can($step, $transfer)) {
            return;
        }
        $role = $this->roleFor(auth()->user(), $transfer);
        [$from, $roles] = self::STEPS[$step];

        $done = [
            'approve' => 'approved', 'reject' => 'rejected', 'pack' => 'packed', 'finish_packing' => 'marked as packed',
            'reopen_packing' => 'reopened for packing', 'dispatch' => 'dispatched', 'arrive' => 'marked as arrived',
            'receive' => 'received', 'cancel' => 'cancelled', 'resolve' => 'resolved',
        ][$step];

        throw new \DomainException(in_array($role, $roles, true)
            ? "{$transfer->transfer_number} is {$transfer->status->label()}, so it can't be {$done} now."
            : "You can't do this on {$transfer->transfer_number}.");
    }

    /**
     * Steps that reach the notification bell through ActivityLog here (the
     * others — requested, approved, rejected, packing started, received,
     * discrepancy — are logged where they happen). Who sees which action:
     * NotificationBell::TRANSFER_AUDIENCE.
     */
    private const LOGGED_STEPS = [
        'packing_done'   => 'transfer_ready',
        'dispatched'     => 'transfer_dispatched',
        'arrived'        => 'transfer_arrived',
        'cancelled'      => 'transfer_cancelled',
        'issue_resolved' => 'transfer_issue_resolved',
        'closed'         => 'transfer_closed',
    ];

    /** Overdue alerts (alerts:generate) a step clears once the transfer moves on. */
    public const DELAY_ALERTS = [
        'Pending Transfer Approval', 'Transfer Not Packed', 'Transfer Waiting for Transporter',
        'Transfer Overdue in Transit', 'Transfer Not Received',
    ];

    /** Append one step to the transfer's history. */
    public function record(Transfer $transfer, string $action, ?TransferStatus $from = null, ?TransferStatus $to = null, ?string $note = null, array $meta = []): TransferEvent
    {
        $user = auth()->user();

        if (isset(self::LOGGED_STEPS[$action])) {
            ActivityLog::create([
                'user_id'           => $user?->id,
                'user_name'         => $user?->name,
                'action'            => self::LOGGED_STEPS[$action],
                'entity_type'       => 'Transfer',
                'entity_id'         => $transfer->id,
                'entity_identifier' => $transfer->transfer_number,
                'details'           => array_filter([
                    'shop_name'           => $transfer->toShop?->name,
                    'warehouse_name'      => $transfer->fromWarehouse?->name,
                    'expected_arrival_at' => $transfer->expected_arrival_at?->toIso8601String(),
                    'note'                => $note,
                ] + $meta),
                'ip_address'        => request()->ip(),
                'user_agent'        => request()->header('User-Agent'),
            ]);
        }

        // Any status change ends the wait an overdue alert was about.
        if ($to !== null || $action === 'closed') {
            Alert::where('entity_type', Transfer::class)->where('entity_id', $transfer->id)
                ->whereIn('title', self::DELAY_ALERTS)->whereNull('resolved_at')
                ->each(fn ($alert) => $alert->markAsResolved());
        }

        return TransferEvent::create([
            'transfer_id' => $transfer->id,
            'action'      => $action,
            'from_status' => $from?->value,
            'to_status'   => $to?->value,
            'user_id'     => $user?->id,
            'user_name'   => $user?->name,
            'note'        => $note,
            'meta'        => $meta ?: null,
            'created_at'  => now(),
        ]);
    }

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
        // A specialised shop may only request the categories it sells —
        // the same rule for shop managers and the owner (no override).
        $shop = \App\Models\Shop::findOrFail($data['to_shop_id']);
        foreach ($data['items'] as $item) {
            $product = Product::with('category:id,name')->find($item['product_id']);
            if ($product && ! $shop->sellsProduct($product)) {
                throw new \DomainException(
                    "{$shop->name} doesn't sell " . ($product->category?->name ?? 'uncategorised products')
                    . " — {$product->name} can't be requested for this shop."
                );
            }
        }

        return DB::transaction(function () use ($data) {
            $transfer = Transfer::create([
                'transfer_number' => $this->generateTransferNumber(),
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_shop_id' => $data['to_shop_id'],
                'status' => TransferStatus::PENDING,
                'requested_by' => auth()->id(),
                'requested_at' => now(),
                'needed_by' => $data['needed_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity'],
                ]);
            }

            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()?->name,
                'action'            => 'transfer_requested',
                'entity_type'       => 'Transfer',
                'entity_id'         => $transfer->id,
                'entity_identifier' => $transfer->transfer_number,
                'details' => [
                    'warehouse_name' => $transfer->fromWarehouse?->name,
                    'shop_name'      => $transfer->toShop?->name,
                    'item_count'     => count($data['items']),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            $this->record($transfer, 'requested', null, TransferStatus::PENDING, $transfer->notes,
                array_filter(['needed_by' => $transfer->needed_by?->toDateString()]));

            return $transfer;
        });
    }

    /**
     * @param array<int,int> $approved  boxes to send per transfer_item id (default: as requested);
     *                                  0 drops a product, at least one box must remain.
     */
    public function approveTransfer(Transfer $transfer, ?string $notes = null, array $approved = []): Transfer
    {
        $this->assertCan('approve', $transfer);

        $quantities = $transfer->items->mapWithKeys(fn ($item) => [
            $item->id => max(0, (int) ($approved[$item->id] ?? $item->quantity_requested)),
        ]);
        if ($quantities->sum() < 1) {
            throw new \DomainException('Approve at least one box, or reject the request.');
        }

        return DB::transaction(function () use ($transfer, $notes, $quantities) {
            foreach ($transfer->items as $item) {
                $item->update(['quantity_approved' => $quantities[$item->id]]);
            }

            $transfer->update([
                'status' => TransferStatus::APPROVED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                // The shop's request note stays in `notes`.
                'review_notes' => $notes ?: $transfer->review_notes,
            ]);

            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()?->name,
                'action'            => 'transfer_approved',
                'entity_type'       => 'Transfer',
                'entity_id'         => $transfer->id,
                'entity_identifier' => $transfer->transfer_number,
                'details' => [
                    'warehouse_name' => $transfer->fromWarehouse?->name,
                    'shop_name'      => $transfer->toShop?->name,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            // Auto-resolve any unresolved alert about this transfer awaiting approval.
            // Matches both "New Transfer Request" and "Pending Transfer Approval" titles
            // because both were used at different points.
            Alert::where('entity_type', Transfer::class)
                ->where('entity_id', $transfer->id)
                ->whereIn('title', [
                    'New Transfer Request',
                    'Pending Transfer Approval',
                    'Transfer Approval Required',
                ])
                ->whereNull('resolved_at')
                ->each(function ($alert) {
                    $alert->markAsResolved();
                });

            $changed = $transfer->items->filter(fn ($i) => $quantities[$i->id] !== (int) $i->quantity_requested)
                ->map(fn ($i) => ['product_id' => $i->product_id, 'requested' => (int) $i->quantity_requested, 'approved' => $quantities[$i->id]])
                ->values()->all();
            $this->record($transfer, 'approved', TransferStatus::PENDING, TransferStatus::APPROVED, $notes, array_filter(['changed' => $changed]));

            return $transfer;
        });
    }

    public function rejectTransfer(Transfer $transfer, string $reason): Transfer
    {
        $this->assertCan('reject', $transfer);

        return DB::transaction(function () use ($transfer, $reason) {
            $transfer->update([
                'status' => TransferStatus::REJECTED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => $reason,
            ]);

            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()?->name,
                'action'            => 'transfer_rejected',
                'entity_type'       => 'Transfer',
                'entity_id'         => $transfer->id,
                'entity_identifier' => $transfer->transfer_number,
                'details' => [
                    'reason'         => $reason,
                    'warehouse_name' => $transfer->fromWarehouse?->name,
                    'shop_name'      => $transfer->toShop?->name,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            Alert::where('entity_type', Transfer::class)
                ->where('entity_id', $transfer->id)
                ->whereIn('title', [
                    'New Transfer Request',
                    'Pending Transfer Approval',
                    'Transfer Approval Required',
                ])
                ->whereNull('resolved_at')
                ->each(function ($alert) {
                    $alert->markAsResolved();
                });

            $this->record($transfer, 'rejected', TransferStatus::PENDING, TransferStatus::REJECTED, $reason);

            return $transfer;
        });
    }

    public function assignBoxesToTransfer(Transfer $transfer, array $boxAssignments): Transfer
    {
        $this->assertCan('pack', $transfer);

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
                $box = Box::find($assignment['box_id']);
                TransferBox::create([
                    'transfer_id'       => $transfer->id,
                    'box_id'            => $box->id,
                    'box_status_before' => $this->holdBox($box),
                ]);

                // Update transfer item quantities
                $transferItem = $transfer->items()
                    ->where('product_id', $box->product_id)
                    ->first();

                if ($transferItem) {
                    $transferItem->increment('quantity_shipped', $box->items_remaining);
                }
            }

            $this->markPackingStarted($transfer, count($boxAssignments));

            return $transfer;
        });
    }

    public function scanOutBox(Transfer $transfer, string $boxCode): TransferBox
    {
        return DB::transaction(function () use ($transfer, $boxCode) {
            // Primary lookup: by internal box_code
            $box = Box::where('box_code', $boxCode)->first();

            // Fallback: treat input as a product barcode, pick one un-scanned box
            if (!$box) {
                $product = Product::where('barcode', $boxCode)->first();
                if ($product) {
                    $box = Box::where('product_id', $product->id)
                        ->where('location_type', LocationType::WAREHOUSE)
                        ->where('location_id', $transfer->from_warehouse_id)
                        ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
                        ->whereNotIn('id',
                            TransferBox::where('transfer_id', $transfer->id)
                                ->whereNotNull('scanned_out_at')
                                ->pluck('box_id')
                        )
                        ->first();
                }
            }

            if (!$box) {
                throw new \Exception("No box found for code/barcode: {$boxCode}");
            }

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

    /**
     * Packing is done: the transfer waits for the transporter (approved → ready).
     *
     * @param array<int,string> $shortReasons  reason per transfer_item id packed short (required for each)
     */
    public function finishPacking(Transfer $transfer, array $shortReasons = []): Transfer
    {
        $this->assertCan('finish_packing', $transfer);

        if (! $transfer->boxes()->exists()) {
            throw new \DomainException('Pack at least one box first.');
        }

        $short = $transfer->items->map(fn ($item) => [
            'item' => $item, 'packed' => $this->packedCount($transfer, $item->product_id), 'approved' => $item->boxesToSend(),
        ])->filter(fn ($r) => $r['packed'] < $r['approved']);

        foreach ($short as $r) {
            if (trim((string) ($shortReasons[$r['item']->id] ?? '')) === '') {
                throw new \DomainException("Say why {$r['item']->product?->name} is short ({$r['packed']} of {$r['approved']} boxes).");
            }
        }

        return DB::transaction(function () use ($transfer, $short, $shortReasons) {
            foreach ($short as $r) {
                $r['item']->update(['short_reason' => trim($shortReasons[$r['item']->id])]);
            }
            $transfer->update([
                'status'          => TransferStatus::READY,
                'packing_done_at' => now(),
                'packing_done_by' => auth()->id(),
            ]);
            $this->record($transfer, 'packing_done', TransferStatus::APPROVED, TransferStatus::READY, null, array_filter([
                'boxes' => $transfer->boxes()->count(),
                'short' => $short->map(fn ($r) => ['product_id' => $r['item']->product_id, 'packed' => $r['packed'],
                    'approved' => $r['approved'], 'reason' => trim($shortReasons[$r['item']->id])])->values()->all(),
            ]));

            return $transfer;
        });
    }

    /** Back to packing from ready (e.g. a box must be swapped before dispatch). */
    public function reopenPacking(Transfer $transfer, ?string $reason = null): Transfer
    {
        $this->assertCan('reopen_packing', $transfer);

        $transfer->update(['status' => TransferStatus::APPROVED, 'packing_done_at' => null, 'packing_done_by' => null]);
        $transfer->items()->update(['short_reason' => null]);
        $this->record($transfer, 'packing_reopened', TransferStatus::READY, TransferStatus::APPROVED, $reason);

        return $transfer;
    }

    /**
     * Hand the boxes to the transporter (ready → in_transit).
     *
     * @param array $handover  handed_to_name, handover_signature (data URL), transporter_instructions,
     *                         expected_arrival_at (Carbon|string, UTC), short_reasons (when dispatching
     *                         straight from approved)
     */
    public function dispatch(Transfer $transfer, ?int $transporterId, array $handover = []): Transfer
    {
        $this->assertCan('dispatch', $transfer);

        if ($transfer->status === TransferStatus::APPROVED) {
            // One-step flow (pack → ship): finish packing on the way, recording
            // any short product as "not packed" unless a reason is given.
            $reasons = $handover['short_reasons'] ?? [];
            foreach ($transfer->items as $item) {
                $reasons[$item->id] ??= 'Not packed before dispatch';
            }
            $this->finishPacking($transfer, $reasons);
            $transfer->refresh();
        }

        $unscanned = $transfer->boxes()->whereNull('scanned_out_at')->count();
        if ($unscanned > 0) {
            throw new \DomainException("{$unscanned} boxes have not been scanned out");
        }

        return DB::transaction(function () use ($transfer, $transporterId, $handover) {
            $transfer->update([
                'status'                   => TransferStatus::IN_TRANSIT,
                'transporter_id'           => $transporterId,
                'shipped_at'               => now(),
                'shipped_by'               => auth()->id(),
                'handed_to_name'           => $handover['handed_to_name'] ?? null,
                'handover_signature'       => $handover['handover_signature'] ?? null,
                'transporter_instructions' => $handover['transporter_instructions'] ?? null,
                'expected_arrival_at'      => $handover['expected_arrival_at'] ?? null,
            ]);
            $this->record($transfer, 'dispatched', TransferStatus::READY, TransferStatus::IN_TRANSIT, $transfer->transporter_instructions, array_filter([
                'transporter_id'      => $transporterId,
                'handed_to'           => $transfer->handed_to_name,
                'signed'              => $transfer->handover_signature ? true : null,
                'expected_arrival_at' => $transfer->expected_arrival_at?->toIso8601String(),
                'boxes'               => $transfer->boxes()->count(),
            ]));

            return $transfer;
        });
    }

    /** @deprecated use dispatch(); kept for callers of the old name. */
    public function markAsShipped(Transfer $transfer, ?int $transporterId = null, array $handover = []): Transfer
    {
        return $this->dispatch($transfer, $transporterId, $handover);
    }

    /** The boxes reached the shop (in_transit → delivered); the shop scans them in next. */
    public function markAsDelivered(Transfer $transfer): Transfer
    {
        $this->assertCan('arrive', $transfer);

        $transfer->update([
            'status'       => TransferStatus::DELIVERED,
            'delivered_at' => now(),
            'delivered_by' => auth()->id(),
        ]);
        $this->record($transfer, 'arrived', TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED);

        return $transfer;
    }

    /**
     * @param array $receivedBoxes  [['box_id', 'is_damaged', 'damage_notes'], …] — boxes not listed are missing
     * @param array $receipt        received_by_name, receipt_signature (data URL)
     */
    public function receiveTransfer(Transfer $transfer, array $receivedBoxes, array $receipt = []): Transfer
    {
        $this->assertCan('receive', $transfer);
        if ($transfer->status === TransferStatus::IN_TRANSIT) {
            $this->markAsDelivered($transfer);
        }

        return DB::transaction(function () use ($transfer, $receivedBoxes, $receipt) {
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
                    // It arrived: it sits at the shop now, off sale, and goes to
                    // Damaged Goods where the owner decides what happens to it.
                    $box->moveTo(LocationType::SHOP, $transfer->to_shop_id, "Transfer received damaged: {$transfer->transfer_number}", $transfer->id, 'transfer');
                    $box->update(['status' => BoxStatus::DAMAGED]);
                    $this->recordDamagedGood($transfer, $box, 'transfer', $received['damage_notes'] ?? 'Damaged on arrival', LocationType::SHOP, $transfer->to_shop_id);
                    $transferBox->update(['resolution' => 'damaged_goods', 'resolved_by' => auth()->id(), 'resolved_at' => now()]);
                    $hasDiscrepancy = true;
                } else {
                    $box->moveTo(
                        LocationType::SHOP,
                        $transfer->to_shop_id,
                        "Transfer received: {$transfer->transfer_number}",
                        $transfer->id,
                        'transfer'
                    );
                    $this->releaseBox($transferBox, $box);
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

            // Boxes that never arrived stay on hold (in_transit, off sale
            // everywhere) until the owner or warehouse resolves each one:
            // found at the warehouse, lost, or arrived late (resolveBox()).
            $missingCount = TransferBox::where('transfer_id', $transfer->id)->where('is_received', false)->count();

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
                'received_by_name' => $receipt['received_by_name'] ?? null,
                'receipt_signature' => $receipt['receipt_signature'] ?? null,
                'has_discrepancy' => $hasDiscrepancy,
                // Complete unless boxes are missing (damaged ones went to Damaged Goods).
                'closed_at' => $missingCount > 0 ? null : now(),
            ]);
            $this->record($transfer, 'received', TransferStatus::DELIVERED, TransferStatus::RECEIVED, null, array_filter([
                'received' => count($receivedBoxes),
                'damaged'  => collect($receivedBoxes)->where('is_damaged', true)->count(),
                'missing'  => $transfer->boxes()->count() - count($receivedBoxes),
                'signed'   => ! empty($receipt['receipt_signature']) ? true : null,
            ]));
            if ($missingCount === 0) {
                $this->record($transfer, 'closed');
            }

            // Log the receipt
            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()?->name,
                'action'            => 'transfer_received',
                'entity_type'       => 'Transfer',
                'entity_id'         => $transfer->id,
                'entity_identifier' => $transfer->transfer_number,
                'details' => [
                    'box_count'       => $transfer->boxes()->count(),
                    'has_discrepancy' => $hasDiscrepancy,
                    'shop_name'       => $transfer->toShop?->name,
                    'warehouse_name'  => $transfer->fromWarehouse?->name,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            // If there's a discrepancy, log that too
            if ($hasDiscrepancy) {
                ActivityLog::create([
                    'user_id'           => auth()->id(),
                    'user_name'         => auth()->user()?->name,
                    'action'            => 'transfer_discrepancy',
                    'entity_type'       => 'Transfer',
                    'entity_id'         => $transfer->id,
                    'entity_identifier' => $transfer->transfer_number,
                    'details' => [
                        'box_count'       => $transfer->boxes()->count(),
                        'received_count'  => count($receivedBoxes),
                        'shop_name'       => $transfer->toShop?->name,
                        'warehouse_name'  => $transfer->fromWarehouse?->name,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                ]);
            }

            // Resolve the "Transfer Shipped" alert now that it has been received.
            Alert::where('entity_type', Transfer::class)
                ->where('entity_id', $transfer->id)
                ->whereIn('title', [
                    'Transfer Shipped - Ready to Receive',
                    'Transfer Shipped - Action Required',
                    'Transfer In Transit',
                ])
                ->whereNull('resolved_at')
                ->each(function ($alert) {
                    $alert->markAsResolved();
                });

            return $transfer;
        });
    }

    /**
     * Resolve a scanned barcode to a product.
     * Returns the Product if the barcode matches products.barcode, null otherwise.
     */
    public function resolveProductByBarcode(string $barcode): ?Product
    {
        return Product::where('barcode', $barcode)->first();
    }

    /**
     * During the PACK stage: the warehouse manager scans a product barcode
     * and types a quantity. This method finds that many available boxes at
     * the source warehouse, creates TransferBox records, and scans them out
     * in one atomic operation.
     *
     * @param  Transfer $transfer   Must be in APPROVED status.
     * @param  string   $barcode    The product barcode that was scanned.
     * @param  int      $quantity   Number of boxes to pack (not items — boxes).
     * @return array                The TransferBox instances that were created.
     * @throws \Exception           If product not found, or not enough boxes available.
     */
    public function packBoxesByProductBarcode(Transfer $transfer, string $barcode, int $quantity): array
    {
        $product = $this->resolveProductByBarcode($barcode);
        if (! $product) {
            throw new \DomainException("No product found with barcode: {$barcode}");
        }

        return $this->packBoxesForProduct($transfer, $product->id, $quantity);
    }

    /**
     * Pack $quantity boxes of a product (oldest available first). Used by
     * barcode scans and by the pack screen's per-product "Pack" button —
     * many products have no barcode.
     */
    public function packBoxesForProduct(Transfer $transfer, int $productId, int $quantity): array
    {
        $this->assertCan('pack', $transfer);

        return DB::transaction(function () use ($transfer, $productId, $quantity) {
            $product = Product::findOrFail($productId);

            // Verify this product is actually in the transfer request
            $transferItem = $transfer->items()->where('product_id', $product->id)->first();
            if (!$transferItem) {
                throw new \Exception("Product {$product->name} is not part of this transfer request");
            }

            $this->assertRoomFor($transfer, $transferItem, $quantity);

            // Find available boxes at the source warehouse, excluding any already assigned to this transfer
            $alreadyAssignedBoxIds = TransferBox::where('transfer_id', $transfer->id)
                ->pluck('box_id');

            $boxes = Box::where('product_id', $product->id)
                ->where('location_type', LocationType::WAREHOUSE)
                ->where('location_id', $transfer->from_warehouse_id)
                ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
                ->where('items_remaining', '>', 0)
                ->whereNotIn('id', $alreadyAssignedBoxIds)
                ->orderBy('id')
                ->limit($quantity)
                ->lockForUpdate()
                ->get();

            if ($boxes->count() < $quantity) {
                throw new \Exception(
                    "Only {$boxes->count()} box(es) available for {$product->name} at this warehouse. Requested: {$quantity}"
                );
            }

            $createdTransferBoxes = [];
            foreach ($boxes as $box) {
                $tb = TransferBox::create([
                    'transfer_id'       => $transfer->id,
                    'box_id'            => $box->id,
                    'box_status_before' => $this->holdBox($box),
                    'scanned_out_by'    => auth()->id(),
                    'scanned_out_at'    => now(),
                ]);
                $createdTransferBoxes[] = $tb;

                // Increment quantity_shipped on the TransferItem
                $transferItem->increment('quantity_shipped', $box->items_remaining);
            }

            $this->markPackingStarted($transfer, count($createdTransferBoxes));

            return $createdTransferBoxes;
        });
    }

    public const RESOLUTIONS = [
        'found'         => 'Found at the warehouse',
        'lost'          => 'Lost in transit',
        'received_late' => 'Arrived late',
    ];

    /**
     * Settle one box that didn't arrive (owner / warehouse, transfer received):
     * - found:         it never left — back on sale at the warehouse
     * - lost:          written off (Damaged Goods, disposition write-off, transporter named)
     * - received_late: it turned up at the shop — received into its stock now
     * The transfer closes when no missing box is left.
     */
    public function resolveBox(Transfer $transfer, int $boxId, string $resolution, string $note): TransferBox
    {
        $this->assertCan('resolve', $transfer);
        if (! isset(self::RESOLUTIONS[$resolution])) {
            throw new \DomainException('Unknown resolution.');
        }
        if (trim($note) === '') {
            throw new \DomainException('Add a note on what happened.');
        }

        return DB::transaction(function () use ($transfer, $boxId, $resolution, $note) {
            $tb = TransferBox::where('transfer_id', $transfer->id)->where('box_id', $boxId)
                ->where('is_received', false)->whereNull('resolution')->lockForUpdate()->firstOrFail();
            $box = Box::lockForUpdate()->findOrFail($boxId);

            match ($resolution) {
                'found' => $this->releaseBox($tb, $box),
                'lost'  => $this->writeOffLostBox($transfer, $box, $note),
                'received_late' => $this->receiveLateBox($transfer, $tb, $box),
            };

            $tb->update([
                'resolution' => $resolution, 'resolved_by' => auth()->id(), 'resolved_at' => now(), 'resolution_notes' => trim($note),
            ] + ($resolution === 'received_late' ? ['is_received' => true, 'scanned_in_by' => auth()->id(), 'scanned_in_at' => now()] : []));

            $this->record($transfer, 'issue_resolved', null, null, trim($note), ['box_code' => $box->box_code, 'resolution' => $resolution]);

            if (! $transfer->openIssues()->exists()) {
                $transfer->update(['closed_at' => now()]);
                $this->record($transfer, 'closed');
            }

            return $tb;
        });
    }

    private function writeOffLostBox(Transfer $transfer, Box $box, string $note): void
    {
        $box->update(['status' => BoxStatus::DAMAGED]);
        $who = $transfer->transporter?->name ?? 'unknown transporter';
        $this->recordDamagedGood($transfer, $box, 'transfer_lost', "Lost in transit ({$who}{$this->handedToSuffix($transfer)}): " . trim($note),
            LocationType::WAREHOUSE, $transfer->from_warehouse_id, writeOff: true);
    }

    private function handedToSuffix(Transfer $transfer): string
    {
        return $transfer->handed_to_name ? ", driver {$transfer->handed_to_name}" : '';
    }

    private function receiveLateBox(Transfer $transfer, TransferBox $tb, Box $box): void
    {
        $box->moveTo(LocationType::SHOP, $transfer->to_shop_id, "Transfer box arrived late: {$transfer->transfer_number}", $transfer->id, 'transfer');
        $this->releaseBox($tb, $box);
        $transfer->items()->where('product_id', $box->product_id)->first()?->increment('quantity_received', $box->items_remaining);
    }

    /** A damaged or lost transfer box becomes a Damaged Goods record (counts in Loss Analysis). */
    private function recordDamagedGood(Transfer $transfer, Box $box, string $source, string $description, LocationType $locType, int $locId, bool $writeOff = false): void
    {
        $price = (int) ($box->product?->selling_price ?? 0);
        \App\Models\DamagedGood::create([
            'damage_reference'       => ($source === 'transfer_lost' ? 'TR-LOST-' : 'TR-DMG-') . $box->box_code,
            'source_type'            => $source,
            'source_id'              => $transfer->id,
            'product_id'             => $box->product_id,
            'quantity_damaged'       => (int) $box->items_remaining,
            'box_id'                 => $box->id,
            'location_type'          => $locType,
            'location_id'            => $locId,
            'disposition'            => $writeOff ? \App\Enums\DispositionType::WRITE_OFF : \App\Enums\DispositionType::PENDING,
            'disposition_decided_by' => $writeOff ? auth()->id() : null,
            'disposition_decided_at' => $writeOff ? now() : null,
            'disposition_notes'      => $writeOff ? "Written off from transfer {$transfer->transfer_number}" : null,
            'damage_description'     => $description,
            'estimated_loss'         => $price * (int) $box->items_remaining,
            'recorded_by'            => auth()->id(),
            'recorded_at'            => now(),
        ]);
    }

    /**
     * Take a wrongly packed box off an approved transfer: the box goes back on
     * sale at the warehouse (releaseBox), quantity_shipped drops by its items,
     * and packed_at is cleared again when nothing is left packed.
     */
    /** Boxes of a product already packed on this transfer. */
    public function packedCount(Transfer $transfer, int $productId): int
    {
        return TransferBox::where('transfer_id', $transfer->id)
            ->whereHas('box', fn ($q) => $q->where('product_id', $productId))
            ->count();
    }

    /** Packing never goes beyond the approved number of boxes. */
    private function assertRoomFor(Transfer $transfer, $transferItem, int $adding): void
    {
        $left = $transferItem->boxesToSend() - $this->packedCount($transfer, $transferItem->product_id);
        if ($adding > $left) {
            $name = $transferItem->product?->name ?? 'this product';
            throw new \DomainException($left > 0
                ? "Only {$left} more " . \Illuminate\Support\Str::plural('box', $left) . " of {$name} " . ($left === 1 ? 'is' : 'are') . ' approved.'
                : "All approved boxes of {$name} are already packed.");
        }
    }

    /** First box packed: packing has started (who, when). */
    private function markPackingStarted(Transfer $transfer, int $boxCount): void
    {
        if ($transfer->packed_at) {
            return;
        }
        $transfer->update(['packed_by' => auth()->id(), 'packed_at' => now()]);

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()?->name,
            'action'            => 'transfer_packed',
            'entity_type'       => 'Transfer',
            'entity_id'         => $transfer->id,
            'entity_identifier' => $transfer->transfer_number,
            'details'           => ['box_count' => $boxCount, 'warehouse_name' => $transfer->fromWarehouse?->name, 'shop_name' => $transfer->toShop?->name],
            'ip_address'        => request()->ip(),
            'user_agent'        => request()->header('User-Agent'),
        ]);
        $this->record($transfer, 'packing_started');
    }

    public function unpackBox(Transfer $transfer, int $boxId): void
    {
        $this->assertCan('pack', $transfer);

        DB::transaction(function () use ($transfer, $boxId) {
            $tb = TransferBox::where('transfer_id', $transfer->id)->where('box_id', $boxId)->lockForUpdate()->firstOrFail();
            $box = Box::lockForUpdate()->findOrFail($boxId);

            $this->releaseBox($tb, $box);
            $transfer->items()->where('product_id', $box->product_id)->first()
                ?->decrement('quantity_shipped', $box->items_remaining);
            $tb->delete();

            if (! TransferBox::where('transfer_id', $transfer->id)->exists()) {
                $transfer->update(['packed_by' => null, 'packed_at' => null]);
            }

            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()?->name,
                'action'            => 'transfer_box_unpacked',
                'entity_type'       => 'Transfer',
                'entity_id'         => $transfer->id,
                'entity_identifier' => $transfer->transfer_number,
                'details'           => ['box_code' => $box->box_code],
                'ip_address'        => request()->ip(),
                'user_agent'        => request()->header('User-Agent'),
            ]);
            $this->record($transfer, 'box_unpacked', null, null, null, ['box_code' => $box->box_code]);
        });
    }

    /**
     * Pack a specific box by its box code.
     * This allows warehouse staff to scan individual box codes.
     *
     * @param  Transfer $transfer   Must be in APPROVED status.
     * @param  string   $boxCode    The box code that was scanned.
     * @param  int      $quantity   Number of boxes with this same code to pack (usually 1).
     * @return TransferBox          The TransferBox instance that was created.
     * @throws \Exception           If box not found, already packed, or exceeds requested quantity.
     */
    public function packBoxByBoxCode(Transfer $transfer, string $boxCode, int $quantity = 1): TransferBox
    {
        $this->assertCan('pack', $transfer);

        return DB::transaction(function () use ($transfer, $boxCode, $quantity) {
            // Find the box by code
            $box = Box::where('box_code', $boxCode)
                ->where('location_type', LocationType::WAREHOUSE)
                ->where('location_id', $transfer->from_warehouse_id)
                ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
                ->where('items_remaining', '>', 0)
                ->first();

            if (!$box) {
                throw new \Exception("Box '{$boxCode}' not found or not available at this warehouse");
            }

            // Check if box is already assigned to this transfer
            $existingTransferBox = TransferBox::where('transfer_id', $transfer->id)
                ->where('box_id', $box->id)
                ->first();

            if ($existingTransferBox) {
                throw new \Exception("Box '{$boxCode}' is already packed in this transfer");
            }

            // Verify this product is in the transfer request
            $transferItem = $transfer->items()->where('product_id', $box->product_id)->first();
            if (!$transferItem) {
                throw new \Exception("Box contains {$box->product->name}, which is not in this transfer request");
            }

            $this->assertRoomFor($transfer, $transferItem, 1);

            // Create the transfer box record
            $tb = TransferBox::create([
                'transfer_id'       => $transfer->id,
                'box_id'            => $box->id,
                'box_status_before' => $this->holdBox($box),
                'scanned_out_by'    => auth()->id(),
                'scanned_out_at'    => now(),
            ]);

            // Increment quantity_shipped on the TransferItem
            $transferItem->increment('quantity_shipped', $box->items_remaining);

            $this->markPackingStarted($transfer, 1);

            return $tb;
        });
    }

    /**
     * During the RECEIVE stage: the shop manager scans a product barcode
     * and types a quantity. This method finds that many un-received
     * TransferBox rows for that product in this transfer and marks them
     * as received, moving the boxes to the shop.
     *
     * @param  Transfer $transfer   Must be in DELIVERED or IN_TRANSIT status.
     * @param  string   $barcode    The product barcode that was scanned.
     * @param  int      $quantity   Number of boxes to receive.
     * @return array                The TransferBox instances that were updated.
     * @throws \Exception
     */
    public function receiveBoxesByProductBarcode(Transfer $transfer, string $barcode, int $quantity): array
    {
        $this->assertCan('receive', $transfer);

        return DB::transaction(function () use ($transfer, $barcode, $quantity) {
            $product = $this->resolveProductByBarcode($barcode);
            if (!$product) {
                throw new \Exception("No product found with barcode: {$barcode}");
            }

            // Find un-received TransferBox rows for this product in this transfer
            $transferBoxes = TransferBox::where('transfer_id', $transfer->id)
                ->whereHas('box', fn ($q) => $q->where('product_id', $product->id))
                ->where('is_received', false)
                ->limit($quantity)
                ->get();

            if ($transferBoxes->count() < $quantity) {
                throw new \Exception(
                    "Only {$transferBoxes->count()} un-received box(es) of {$product->name} in this transfer. Requested: {$quantity}"
                );
            }

            $updated = [];
            foreach ($transferBoxes as $tb) {
                $tb->update([
                    'scanned_in_by' => auth()->id(),
                    'scanned_in_at' => now(),
                    'is_received'   => true,
                ]);

                // Move the box to the destination shop
                $box = $tb->box;
                $box->moveTo(
                    LocationType::SHOP,
                    $transfer->to_shop_id,
                    "Transfer received: {$transfer->transfer_number}",
                    $transfer->id,
                    'transfer'
                );
                $this->releaseBox($tb, $box);

                // Increment quantity_received on TransferItem
                $transferItem = $transfer->items()->where('product_id', $product->id)->first();
                if ($transferItem) {
                    $transferItem->increment('quantity_received', $box->items_remaining);
                }

                $updated[] = $tb;
            }

            return $updated;
        });
    }

    public function cancelTransfer(Transfer $transfer, string $reason): Transfer
    {
        $this->assertCan('cancel', $transfer);
        $from = $transfer->status;

        return DB::transaction(function () use ($transfer, $reason, $from) {
            $transfer->update([
                'status' => TransferStatus::CANCELLED,
                'review_notes' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);
            $this->record($transfer, 'cancelled', $from, TransferStatus::CANCELLED, $reason);

            // Unassign packed boxes, putting any that hadn't reached the shop
            // back on sale. (This used to test $transfer->status right after
            // setting it to CANCELLED.)
            $packed = TransferBox::where('transfer_id', $transfer->id)->with('box')->get();
            $packed->where('is_received', false)
                ->each(function (TransferBox $tb) { if ($tb->box) { $this->releaseBox($tb, $tb->box); } });
            TransferBox::whereIn('id', $packed->pluck('id'))->delete();

            return $transfer;
        });
    }

    /**
     * Packing takes a box off sale at the warehouse (and out of reach of
     * other transfers) until it reaches the shop or the transfer is
     * cancelled. Returns the status it had, to store on the TransferBox.
     */
    private function holdBox(Box $box): string
    {
        $before = $box->status instanceof BoxStatus ? $box->status->value : (string) $box->status;
        $box->update(['status' => BoxStatus::IN_TRANSIT]);

        return $before;
    }

    /** Undo holdBox(); only touches a box that is still on hold. */
    private function releaseBox(TransferBox $tb, Box $box): void
    {
        if ($box->status !== BoxStatus::IN_TRANSIT) {
            return;
        }

        $restore = $tb->box_status_before
            ?? ($box->items_remaining < $box->items_total ? BoxStatus::PARTIAL->value : BoxStatus::FULL->value);
        $box->update(['status' => $restore]);
    }
}
