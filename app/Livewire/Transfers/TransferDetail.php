<?php

namespace App\Livewire\Transfers;

use App\Enums\TransferStatus;
use App\Models\Product;
use App\Models\Transfer;
use App\Models\Transporter;
use App\Services\Inventory\TransferService;
use App\Services\SettingsService;
use Carbon\Carbon;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * One transfer detail page for owner, shop and warehouse
 * (owner|shop|warehouse.transfers.show). The role comes from the user:
 * - owner / warehouse manager (its warehouse): approve with adjusted
 *   quantities, or reject with a reason, while pending; pack link once approved.
 * - shop manager (its shop): mark delivered, receive.
 */
class TransferDetail extends Component
{
    #[Locked]
    public int $transferId;

    #[Locked]
    public string $role = 'owner';

    /** Boxes to approve, keyed by transfer item id (pending only). */
    public array $qty = [];

    public string $approveNote = '';
    public string $rejectReason = '';
    public bool $showReject = false;

    public string $cancelReason = '';
    public bool $showCancel = false;

    // Dispatch sheet (ready → in transit)
    public bool $showDispatch = false;
    public string $transporterName = '';
    public string $handedToName = '';
    public string $instructions = '';
    public string $expectedArrival = '';   // Y-m-d\TH:i in business time
    public string $handoverSignature = '';
    public bool $justDispatched = false;

    public function mount(Transfer $transfer): void
    {
        $user = auth()->user();

        $this->role = match (true) {
            $user->isOwner() || $user->isAdmin() => 'owner',
            $user->isWarehouseManager()          => 'warehouse',
            $user->isShopManager()               => 'shop',
            default                              => abort(403),
        };
        abort_if($this->role === 'warehouse' && $transfer->from_warehouse_id !== $user->location_id, 403, 'This transfer is not from your warehouse.');
        abort_if($this->role === 'shop' && $transfer->to_shop_id !== $user->location_id, 403, 'You can only view transfers for your shop.');

        $this->transferId = $transfer->id;
        foreach ($transfer->items as $item) {
            $this->qty[$item->id] = (int) $item->quantity_requested;
        }
    }

    public function getTransferProperty(): Transfer
    {
        return Transfer::with([
            'items.product:id,name,sku,barcode,items_per_box',
            'boxes.box:id,box_code,product_id,items_remaining,items_total',
            'fromWarehouse:id,name', 'toShop:id,name',
            'requestedBy:id,name', 'reviewedBy:id,name', 'packedBy:id,name', 'receivedBy:id,name', 'transporter',
            'packingDoneBy:id,name', 'shippedBy:id,name', 'deliveredBy:id,name', 'cancelledBy:id,name',
        ])->findOrFail($this->transferId);
    }

    /** Approve / reject controls: TransferService decides (status + role). */
    protected function canReview(Transfer $t): bool
    {
        return app(TransferService::class)->can('approve', $t);
    }

    /** Warehouse stock per product: ['boxes' => full + opened boxes, 'full', 'partial']. */
    protected function stock(Transfer $t): array
    {
        $summary = Product::stockSummaryFor('warehouse', $t->from_warehouse_id, $t->items->pluck('product_id'));

        return collect($summary)->map(fn ($s) => [
            'boxes' => $s['full_boxes'] + $s['partial_boxes'], 'full' => $s['full_boxes'], 'partial' => $s['partial_boxes'],
        ])->all();
    }

    public function approve(): void
    {
        $t = $this->transfer;
        if (! $this->canReview($t)) {
            $this->dispatch('notification', ['type' => 'error', 'message' => 'Only a pending transfer can be approved.']);
            return;
        }

        // validate() clears the error bag, so it runs before the quantity checks.
        $this->validate(['approveNote' => 'nullable|string|max:500']);

        $stock = $this->stock($t);
        foreach ($t->items as $item) {
            $n = (int) ($this->qty[$item->id] ?? 0);
            $available = $stock[$item->product_id]['boxes'] ?? 0;
            if ($n < 0) {
                $this->addError("qty.{$item->id}", 'Use 0 to leave it out.');
            } elseif ($n > $available) {
                $this->addError("qty.{$item->id}", "Only {$available} in the warehouse.");
            }
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        try {
            // The shop's request stays as it was; the approved numbers go to quantity_approved.
            $approved = $t->items->mapWithKeys(fn ($item) => [$item->id => (int) ($this->qty[$item->id] ?? 0)])->all();
            app(TransferService::class)->approveTransfer($t, trim($this->approveNote) ?: null, $approved);
        } catch (\DomainException $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
            return;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notification', ['type' => 'error', 'message' => 'Could not approve: ' . $e->getMessage()]);
            return;
        }

        $this->approveNote = '';
        $this->dispatch('notification', ['type' => 'success', 'message' => "{$t->transfer_number} approved."]);
    }

    public function openReject(): void
    {
        $this->resetErrorBag();
        $this->rejectReason = '';
        $this->showReject = true;
    }

    public function reject(): void
    {
        $t = $this->transfer;
        if (! $this->canReview($t)) {
            $this->showReject = false;
            return;
        }
        $this->validate(['rejectReason' => 'required|string|min:3|max:500'], [
            'rejectReason.required' => 'Tell the shop why.', 'rejectReason.min' => 'Tell the shop why.',
        ]);

        app(TransferService::class)->rejectTransfer($t, trim($this->rejectReason));
        $this->showReject = false;
        $this->dispatch('notification', ['type' => 'success', 'message' => "{$t->transfer_number} rejected. The shop can see your reason."]);
    }

    public function openCancel(): void
    {
        $this->resetErrorBag();
        $this->cancelReason = '';
        $this->showCancel = true;
    }

    public function cancelTransfer(): void
    {
        $t = $this->transfer;
        $this->validate(['cancelReason' => 'required|string|min:3|max:500'], [
            'cancelReason.required' => 'Say why it is cancelled.', 'cancelReason.min' => 'Say why it is cancelled.',
        ]);

        try {
            app(TransferService::class)->cancelTransfer($t, trim($this->cancelReason));
        } catch (\DomainException $e) {
            $this->showCancel = false;
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
            return;
        }

        $this->showCancel = false;
        $this->dispatch('notification', ['type' => 'success', 'message' => "{$t->transfer_number} cancelled."]);
    }

    public function openDispatch(): void
    {
        $t = $this->transfer;
        $this->resetErrorBag();
        $this->transporterName = $t->transporter?->name ?? '';
        $this->handedToName = '';
        $this->instructions = $t->transporter_instructions ?? '';
        $this->expectedArrival = local_time(now()->addHours(4))->format('Y-m-d\TH:i');
        $this->handoverSignature = '';
        $this->showDispatch = true;
    }

    public function dispatchTransfer(): void
    {
        $t = $this->transfer;
        $needsSignature = app(SettingsService::class)->transferRequireSignature();
        $this->validate([
            'transporterName'   => 'required|string|max:120',
            'handedToName'      => 'required|string|min:2|max:120',
            'instructions'      => 'nullable|string|max:1000',
            'expectedArrival'   => 'nullable|date_format:Y-m-d\TH:i',
            'handoverSignature' => $needsSignature ? 'required|string|starts_with:data:image/png' : 'nullable|string|starts_with:data:image/png',
        ], [
            'transporterName.required'   => 'Choose or type the transporter.',
            'handedToName.required'      => 'Who is taking the boxes?',
            'handoverSignature.required' => 'The driver signs here before leaving.',
        ]);

        $transporter = Transporter::firstOrCreate(['name' => trim($this->transporterName)], ['is_active' => true, 'phone' => '']);
        $eta = $this->expectedArrival
            ? Carbon::createFromFormat('Y-m-d\TH:i', $this->expectedArrival, config('tenant.timezone'))->utc()
            : null;

        try {
            app(TransferService::class)->dispatch($t, $transporter->id, [
                'handed_to_name'           => trim($this->handedToName),
                'handover_signature'       => $this->handoverSignature ?: null,
                'transporter_instructions' => trim($this->instructions) ?: null,
                'expected_arrival_at'      => $eta,
            ]);
        } catch (\DomainException $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
            return;
        }

        $this->showDispatch = false;
        $this->justDispatched = true;
        $this->handoverSignature = '';
        $url = route($this->role === 'owner' ? 'owner.transfers.delivery-note' : 'warehouse.transfers.delivery-note', $t);
        $this->dispatch('transfer-dispatched', url: $url);
        $this->dispatch('notification', ['type' => 'success', 'message' => "{$t->transfer_number} is on its way. {$t->toShop?->name} has been told."]);
    }

    public function reopenPacking(): void
    {
        try {
            app(TransferService::class)->reopenPacking($this->transfer, 'Reopened from the transfer page');
        } catch (\DomainException $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => $e->getMessage()]);
            return;
        }
        $this->redirectRoute('warehouse.transfers.pack', $this->transfer);
    }

    public function markAsDelivered(): void
    {
        $t = $this->transfer;
        if (! app(TransferService::class)->can('arrive', $t)) {
            return;
        }
        app(TransferService::class)->markAsDelivered($t);
        $this->dispatch('transfer-updated', transferId: $t->id);
        $this->dispatch('notification', ['type' => 'success', 'message' => 'Marked as delivered. Scan the boxes to add them to your stock.']);
    }

    #[On('transfer-updated')]
    public function refreshTransfer(): void
    {
        // re-render with fresh data
    }

    public function render()
    {
        $t = $this->transfer;
        $canReview = $this->canReview($t);

        // Boxes per product on this transfer, split by how they ended up.
        $byProduct = $t->boxes->groupBy(fn ($tb) => $tb->box?->product_id);
        $lines = $t->items->map(function ($item) use ($byProduct) {
            $boxes = $byProduct[$item->product_id] ?? collect();

            return [
                'item'     => $item,
                'product'  => $item->product,
                'requested'=> (int) $item->quantity_requested,
                'approved' => $item->quantity_approved,     // null until approved
                'short'    => $item->short_reason,
                'packed'   => $boxes->count(),
                'received' => $boxes->where('is_received', true)->where('is_damaged', false)->count(),
                'damaged'  => $boxes->where('is_damaged', true)->count(),
            ];
        });

        $received = $t->status === TransferStatus::RECEIVED;
        $issues = [
            'damaged' => $t->boxes->where('is_damaged', true)->count(),
            'missing' => $received ? $t->boxes->where('is_received', false)->count() : 0,
        ];

        return view('livewire.transfers.transfer-detail', [
            't'         => $t,
            'lines'     => $lines,
            'stock'     => $canReview ? $this->stock($t) : [],
            'canReview' => $canReview,
            'canCancel' => app(TransferService::class)->can('cancel', $t),
            'canDispatch' => app(TransferService::class)->can('dispatch', $t) && $t->status === TransferStatus::READY,
            'canReopen' => app(TransferService::class)->can('reopen_packing', $t),
            'transporters' => $this->showDispatch ? Transporter::active()->orderBy('name')->get(['id', 'name', 'vehicle_number']) : collect(),
            'needsSignature' => app(SettingsService::class)->transferRequireSignature(),
            'received'  => $received,
            'issues'    => $issues,
            'backUrl'   => route(['owner' => 'owner.transfers.index', 'shop' => 'shop.transfers.index', 'warehouse' => 'warehouse.transfers.index'][$this->role]),
            'noteUrl'   => $this->role === 'shop' ? null
                : route($this->role === 'owner' ? 'owner.transfers.delivery-note' : 'warehouse.transfers.delivery-note', $t),
        ]);
    }
}
