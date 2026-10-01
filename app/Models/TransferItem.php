<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'product_id',
        'quantity_requested',   // boxes the shop asked for (never changed after the request)
        'quantity_approved',    // boxes approved to send (null until approved)
        'short_reason',         // why fewer boxes were packed than approved
        'quantity_shipped',
        'quantity_received',
        'discrepancy_quantity',
        'discrepancy_reason',
    ];

    protected $casts = [
        'quantity_requested' => 'integer',
        'quantity_approved' => 'integer',
        'quantity_shipped' => 'integer',
        'quantity_received' => 'integer',
        'discrepancy_quantity' => 'integer',
    ];

    // Relationships
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Boxes to pack and send: the approved number, or the request before approval. */
    public function boxesToSend(): int
    {
        return (int) ($this->quantity_approved ?? $this->quantity_requested);
    }

    // Helper methods
    public function hasDiscrepancy(): bool
    {
        return $this->discrepancy_quantity != 0;
    }

    public function getFulfillmentPercentage(): float
    {
        if ($this->quantity_requested === 0) {
            return 0;
        }

        return ($this->quantity_received / $this->quantity_requested) * 100;
    }
}
