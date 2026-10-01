<?php

namespace App\Models;

use App\Enums\TransferStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_shop_id',
        'status',
        'requested_by',
        'requested_at',
        'reviewed_by',
        'reviewed_at',
        'packed_by',
        'packed_at',
        'transporter_id',
        'shipped_at',
        'delivered_at',
        'received_by',
        'received_at',
        'has_discrepancy',
        'discrepancy_notes',
        'notes',          // the shop's request note
        'review_notes',   // approval note, rejection or cancellation reason
        'needed_by',
        'packing_done_at', 'packing_done_by',
        'shipped_by', 'handed_to_name', 'handover_signature', 'transporter_instructions', 'expected_arrival_at',
        'delivered_by',
        'received_by_name', 'receipt_signature',
        'cancelled_at', 'cancelled_by',
        'closed_at',
    ];

    protected $casts = [
        'status' => TransferStatus::class,
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'received_at' => 'datetime',
        'has_discrepancy' => 'boolean',
        'needed_by' => 'date',
        'packing_done_at' => 'datetime',
        'expected_arrival_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /** Signatures are data URLs; keep them out of arrays / JSON (Livewire state, logs). */
    protected $hidden = ['handover_signature', 'receipt_signature'];

    // Relationships
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'to_shop_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function packedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packed_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }

    public function packingDoneBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packing_done_by');
    }

    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /** Every step, oldest first (written by TransferService::record()). */
    public function events(): HasMany
    {
        return $this->hasMany(TransferEvent::class)->orderBy('created_at')->orderBy('id');
    }

    /** Boxes missing or damaged on receipt that nobody has resolved yet. */
    public function openIssues(): HasMany
    {
        return $this->hasMany(TransferBox::class)
            ->whereNull('resolution')
            ->where(fn ($q) => $q->where('is_received', false)->orWhere('is_damaged', true));
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public function boxes(): HasMany
    {
        return $this->hasMany(TransferBox::class);
    }

    // Scopes
    public function scopeStatus($query, TransferStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', TransferStatus::PENDING);
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', TransferStatus::IN_TRANSIT);
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('from_warehouse_id', $warehouseId);
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('to_shop_id', $shopId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /** The latest thing that happened, for list rows: ['label' => 'Shipped', 'at' => UTC Carbon]. */
    public function lastEvent(): array
    {
        if ($this->status === TransferStatus::CANCELLED) {
            return ['label' => 'Cancelled', 'at' => $this->updated_at];
        }

        foreach ([
            'received_at'  => 'Received',
            'delivered_at' => 'Delivered',
            'shipped_at'   => 'Shipped',
            'packed_at'    => 'Packing started',
            'reviewed_at'  => $this->status === TransferStatus::REJECTED ? 'Rejected' : 'Approved',
        ] as $column => $label) {
            if ($this->{$column}) {
                return ['label' => $label, 'at' => $this->{$column}];
            }
        }

        return ['label' => 'Requested', 'at' => $this->requested_at];
    }

    /**
     * Progress steps for <x-transfers.timeline>: each is
     * ['key', 'label', 'state' => done|current|todo|stopped, 'who', 'at' (UTC)].
     * A rejected or cancelled transfer ends in a red "stopped" step after the
     * last step it reached.
     */
    public function timeline(): array
    {
        $status  = $this->status;
        $shipped = in_array($status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED, TransferStatus::RECEIVED], true);

        $steps = [
            ['key' => 'requested', 'label' => 'Requested', 'done' => true, 'who' => $this->requestedBy?->name, 'at' => $this->requested_at],
            ['key' => 'approved', 'label' => 'Approved', 'done' => $this->reviewed_at !== null && $status !== TransferStatus::REJECTED,
                'who' => $this->reviewedBy?->name, 'at' => $this->reviewed_at],
            // packed_at is when packing started; the step is done once it ships.
            ['key' => 'packed', 'label' => 'Packed', 'done' => $shipped || $this->shipped_at !== null,
                'who' => $this->packedBy?->name, 'at' => $this->packed_at],
            ['key' => 'shipped', 'label' => 'Shipped', 'done' => $this->shipped_at !== null,
                'who' => $this->transporter?->name, 'at' => $this->shipped_at],
            ['key' => 'delivered', 'label' => 'Delivered', 'done' => $this->delivered_at !== null, 'who' => null, 'at' => $this->delivered_at],
            ['key' => 'received', 'label' => 'Received', 'done' => $this->received_at !== null,
                'who' => $this->receivedBy?->name, 'at' => $this->received_at],
        ];

        if (in_array($status, [TransferStatus::REJECTED, TransferStatus::CANCELLED], true)) {
            $reached = array_values(array_filter($steps, fn ($s) => $s['done']));
            $reached[] = [
                'key' => $status->value, 'label' => $status->label(), 'done' => false, 'stopped' => true,
                'who' => $status === TransferStatus::REJECTED ? $this->reviewedBy?->name : null,
                'at'  => $status === TransferStatus::REJECTED ? $this->reviewed_at : $this->updated_at,
            ];
            $steps = $reached;
        }

        $currentSet = false;
        foreach ($steps as $i => $step) {
            $state = match (true) {
                ! empty($step['stopped']) => 'stopped',
                $step['done']             => 'done',
                ! $currentSet             => 'current',
                default                   => 'todo',
            };
            $currentSet = $currentSet || $state === 'current';
            // A step not reached yet shows no stale "who" (e.g. packer while only approved).
            $steps[$i] = [
                'key' => $step['key'], 'label' => $step['label'], 'state' => $state,
                'who' => in_array($state, ['done', 'stopped'], true) || ($state === 'current' && $step['at']) ? $step['who'] : null,
                'at'  => in_array($state, ['done', 'stopped'], true) || ($state === 'current' && $step['at']) ? $step['at'] : null,
            ];
        }

        return $steps;
    }
}
