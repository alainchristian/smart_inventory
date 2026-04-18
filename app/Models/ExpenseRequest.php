<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ExpenseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'requested_by',
        'warehouse_id',
        'target_shop_id',
        'amount',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'expense_id',
        'paid_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'paid_at'     => 'datetime',
        'amount'      => 'integer',
    ];

    // Relationships

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function targetShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'target_shop_id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('target_shop_id', $shopId);
    }

    // Static helpers

    public static function generateReference(): string
    {
        $count = DB::table('expense_requests')->count() + 1;
        return 'EXPR-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
