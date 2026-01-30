<?php

namespace App\Services\Sales;

use App\Enums\BoxStatus;
use App\Enums\PaymentMethod;
use App\Enums\SaleType;
use App\Events\Sales\SaleCompleted;
use App\Models\Box;
use App\Models\Product;
use App\Models\Sale;
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

    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::create([
                'sale_number' => $this->generateSaleNumber(),
                'shop_id' => $data['shop_id'],
                'type' => $data['type'],
                'payment_method' => $data['payment_method'],
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'sold_by' => auth()->id(),
                'sale_date' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotal = 0;
            $hasPriceOverride = false;

            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $quantity = $itemData['quantity'];
                $isFullBox = $itemData['is_full_box'] ?? false;

                // Determine price
                $originalPrice = $isFullBox
                    ? $product->calculateBoxPrice()
                    : $product->selling_price;

                $actualPrice = $itemData['price'] ?? $originalPrice;
                $priceModified = $actualPrice != $originalPrice;

                if ($priceModified) {
                    $hasPriceOverride = true;
                }

                $lineTotal = $actualPrice * $quantity;

                // Create sale item
                $saleItem = $sale->items()->create([
                    'product_id' => $product->id,
                    'box_id' => $itemData['box_id'] ?? null,
                    'quantity_sold' => $quantity,
                    'is_full_box' => $isFullBox,
                    'original_unit_price' => $originalPrice,
                    'actual_unit_price' => $actualPrice,
                    'line_total' => $lineTotal,
                    'price_was_modified' => $priceModified,
                    'price_modification_reference' => $itemData['price_override_reference'] ?? null,
                    'price_modification_reason' => $itemData['price_override_reason'] ?? null,
                ]);

                $subtotal += $lineTotal;

                // Consume inventory
                if ($itemData['box_id']) {
                    $box = Box::findOrFail($itemData['box_id']);
                    $itemsToConsume = $isFullBox ? $box->items_remaining : $quantity;

                    $box->consumeItems(
                        $itemsToConsume,
                        "Sale: {$sale->sale_number}",
                        $sale->id,
                        'sale'
                    );
                }
            }

            // Calculate totals
            $tax = $data['tax'] ?? 0;
            $discount = $data['discount'] ?? 0;
            $total = $subtotal + $tax - $discount;

            $sale->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'has_price_override' => $hasPriceOverride,
            ]);

            // Dispatch event if it exists
            if (class_exists(SaleCompleted::class)) {
                event(new SaleCompleted($sale));
            }

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
                        if ($box->items_remaining === $box->items_total) {
                            $box->update(['status' => BoxStatus::FULL]);
                        } else {
                            $box->update(['status' => BoxStatus::PARTIAL]);
                        }
                    }
                }
            }

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

        return $sale;
    }
}
