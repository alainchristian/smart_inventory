<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\SaleType;
use App\Models\Transporter;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sale_number',
        'shop_id',
        'type',
        'payment_method',
        'subtotal',
        'tax',
        'discount',
        'total',
        'customer_id',
        'is_split_payment',
        'amount_paid',
        'credit_amount',
        'has_credit',
        'customer_name',
        'customer_phone',
        'sold_by',
        'sale_date',
        'voided_at',
        'voided_by',
        'void_reason',
        'has_price_override',
        'price_override_approved_by',
        'price_override_approved_at',
        'price_override_reason',
        'notes',
        'fulfillment_type',
        'source_warehouse_id',
        'fulfillment_status',
        'fulfillment_method',
        'fulfillment_transporter_id',
        'fulfillment_notes',
        'fulfillment_confirmed_at',
        'fulfillment_confirmed_by',
    ];

    protected $casts = [
        'type' => SaleType::class,
        'payment_method' => PaymentMethod::class,
        'subtotal' => 'integer',
        'tax' => 'integer',
        'discount' => 'integer',
        'total' => 'integer',
        'is_split_payment' => 'boolean',
        'amount_paid' => 'integer',
        'credit_amount' => 'integer',
        'has_credit' => 'boolean',
        'sale_date' => 'datetime',
        'voided_at' => 'datetime',
        'has_price_override' => 'boolean',
        'price_override_approved_at' => 'datetime',
        'fulfillment_confirmed_at' => 'datetime',
    ];

    // Relationships
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function soldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function priceOverrideApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'price_override_approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnModel::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function fulfillmentTransporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class, 'fulfillment_transporter_id');
    }

    public function fulfillmentConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfillment_confirmed_by');
    }

    // Helper methods
    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    public function needsPriceOverrideApproval(): bool
    {
        return $this->has_price_override && $this->price_override_approved_at === null;
    }

    public function isPendingFulfillment(): bool
    {
        return $this->fulfillment_status === 'pending';
    }

    public function isWarehouseDirect(): bool
    {
        return $this->fulfillment_type === 'warehouse_direct';
    }

    // Scopes
    public function scopeNotVoided($query)
    {
        return $query->whereNull('voided_at');
    }

    public function scopeVoided($query)
    {
        return $query->whereNotNull('voided_at');
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }

    public function scopeWithPriceOverride($query)
    {
        return $query->where('has_price_override', true);
    }

    public function scopeWarehouseDirect($query)
    {
        return $query->where('fulfillment_type', 'warehouse_direct');
    }

    public function scopePendingFulfillment($query)
    {
        return $query->where('fulfillment_status', 'pending');
    }
}
