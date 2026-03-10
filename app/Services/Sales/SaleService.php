<?php

namespace App\Services\Sales;

use App\Enums\BoxStatus;
use App\Enums\PaymentMethod;
use App\Enums\SaleType;
use App\Events\Sales\SaleCompleted;
use App\Models\ActivityLog;
use App\Models\Box;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function generateSaleNumber(): string
    {
        $date = now()->format('Ymd');
        $count = Sale::whereDate('created_at', today())->count() + 1;
        $sequence = str_pad($count, 5, '0', STR_PAD_LEFT);

        return "SALE-{$date}-{$sequence}";
    }

    /**
     * Create a sale.
     * Items must pass total item quantity (not a specific box_id).
     * Boxes are selected automatically using FIFO (oldest received_at first).
     */
    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {

            $sale = Sale::create([
                'sale_number'      => $this->generateSaleNumber(),
                'shop_id'          => $data['shop_id'],
                'type'             => $data['type'],
                'payment_method'   => $data['payment_method'],
                'sold_by'          => auth()->id(),
                'sale_date'        => now(),
                'subtotal'         => 0,
                'tax'              => $data['tax'] ?? 0,
                'discount'         => $data['discount'] ?? 0,
                'total'            => 0,
                'has_price_override' => false,
                'customer_name'    => $data['customer_name'] ?? null,
                'customer_phone'   => $data['customer_phone'] ?? null,
                'notes'            => $data['notes'] ?? null,
            ]);

            $subtotal         = 0;
            $hasPriceOverride = false;

            foreach ($data['items'] as $itemData) {
                $product     = Product::findOrFail($itemData['product_id']);
                $itemsToSell = (int) $itemData['quantity'];
                $isFullBox   = (bool) ($itemData['is_full_box'] ?? false);
                $finalPrice  = (int) $itemData['price'];
                $originalPrice = $isFullBox
                    ? $product->calculateBoxPrice()
                    : $product->selling_price;

                $priceModified = $finalPrice !== $originalPrice;
                if ($priceModified) {
                    $hasPriceOverride = true;
                }

                // Auto-select boxes FIFO
                $boxes = Box::where('product_id', $product->id)
                    ->where('location_type', 'shop')
                    ->where('location_id', $data['shop_id'])
                    ->whereIn('status', ['full', 'partial'])
                    ->where('items_remaining', '>', 0)
                    ->orderBy('received_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $remaining = $itemsToSell;

                foreach ($boxes as $box) {
                    if ($remaining <= 0) break;

                    $consume   = min($remaining, $box->items_remaining);
                    $lineTotal = $isFullBox ? $finalPrice : ($consume * $finalPrice);

                    $sale->items()->create([
                        'product_id'                   => $product->id,
                        'box_id'                       => $box->id,
                        'quantity_sold'                => $consume,
                        'is_full_box'                  => $isFullBox && $consume === $box->items_remaining,
                        'original_unit_price'          => $originalPrice,
                        'actual_unit_price'            => $finalPrice,
                        'line_total'                   => $lineTotal,
                        'price_was_modified'           => $priceModified,
                        'price_modification_reference' => $itemData['price_modification_reference'] ?? null,
                        'price_modification_reason'    => $itemData['price_modification_reason'] ?? null,
                    ]);

                    $subtotal += $lineTotal;

                    $box->consumeItems(
                        $consume,
                        "Sale: {$sale->sale_number}",
                        $sale->id,
                        'sale'
                    );

                    $remaining -= $consume;
                }

                if ($remaining > 0) {
                    throw new \Exception(
                        "Insufficient stock for {$product->name}. " .
                        "Needed {$itemsToSell} items, only " . ($itemsToSell - $remaining) . " available."
                    );
                }
            }

            $tax      = $data['tax'] ?? 0;
            $discount = $data['discount'] ?? 0;
            $total    = $subtotal + $tax - $discount;

            $sale->update([
                'subtotal'           => $subtotal,
                'total'              => $total,
                'has_price_override' => $hasPriceOverride,
            ]);

            // ── Primary sale log ─────────────────────────────────────────────────────
            $shop = Shop::find($sale->shop_id);

            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()?->name,
                'action'            => 'sale_created',
                'entity_type'       => 'Sale',
                'entity_id'         => $sale->id,
                'entity_identifier' => $sale->sale_number,
                'details' => [
                    'total'        => $sale->total,
                    'shop_name'    => $shop?->name,
                    'item_count'   => count($data['items']),
                    'payment'      => $data['payment_method'] instanceof \BackedEnum
                                        ? $data['payment_method']->value
                                        : $data['payment_method'],
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            // ── Separate log if any item had a price override ─────────────────────────
            if ($hasPriceOverride) {
                ActivityLog::create([
                    'user_id'           => auth()->id(),
                    'user_name'         => auth()->user()?->name,
                    'action'            => 'price_modified',
                    'entity_type'       => 'Sale',
                    'entity_id'         => $sale->id,
                    'entity_identifier' => $sale->sale_number,
                    'details' => [
                        'total'     => $sale->total,
                        'shop_name' => $shop?->name,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                ]);
            }

            // Event removed — App\Events\Sales\SaleCompleted has no registered listeners.
            // Re-add once a listener exists: event(new SaleCompleted($sale));

            // Invalidate analytics cache after sale creation
            Cache::flush();

            return $sale;
        });
    }

    public function voidSale(Sale $sale, string $reason): Sale
    {
        if ($sale->voided_at) {
            throw new \Exception('Sale is already voided');
        }

        return DB::transaction(function () use ($sale, $reason) {
            $sale->update([
                'voided_at' => now(),
                'voided_by' => auth()->id(),
                'void_reason' => $reason,
            ]);

            // Return items to inventory
            foreach ($sale->items as $item) {
                if ($item->box_id) {
                    $box = Box::find($item->box_id);
                    if ($box) {
                        $box->increment('items_remaining', $item->quantity_sold);

                        // Update box status
                        if ($box->items_remaining >= $box->items_total) {
                            $box->update(['status' => BoxStatus::FULL]);
                        } else {
                            $box->update(['status' => BoxStatus::PARTIAL]);
                        }
                    }
                }
            }

            $shop = Shop::find($sale->shop_id);

            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()?->name,
                'action'            => 'sale_voided',
                'entity_type'       => 'Sale',
                'entity_id'         => $sale->id,
                'entity_identifier' => $sale->sale_number,
                'details' => [
                    'reason'    => $reason,
                    'total'     => $sale->total,
                    'shop_name' => $shop?->name,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            // Invalidate analytics cache after voiding sale
            Cache::flush();

            return $sale;
        });
    }

    public function approvePriceOverride(Sale $sale, string $reason): Sale
    {
        if (!$sale->has_price_override) {
            throw new \Exception('Sale does not have price overrides');
        }

        $sale->update([
            'price_override_approved_by' => auth()->id(),
            'price_override_approved_at' => now(),
            'price_override_reason' => $reason,
        ]);

        $shop = Shop::find($sale->shop_id);

        ActivityLog::create([
            'user_id'           => auth()->id(),
            'user_name'         => auth()->user()?->name,
            'action'            => 'price_override_approved',
            'entity_type'       => 'Sale',
            'entity_id'         => $sale->id,
            'entity_identifier' => $sale->sale_number,
            'details' => [
                'reason'    => $reason,
                'total'     => $sale->total,
                'shop_name' => $shop?->name,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);

        // Invalidate analytics cache after approving price override
        Cache::flush();

        return $sale;
    }
}
