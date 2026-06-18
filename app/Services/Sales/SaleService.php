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

            // ── Resolve payment channels ──────────────────────────────────────────
            $payments = collect($data['payments'] ?? [])
                ->filter(fn ($p) => (int) $p['amount'] > 0)
                ->values();

            if ($payments->isEmpty()) {
                // Backward compat: single method passed directly
                $payments = collect([[
                    'method'    => $data['payment_method'] instanceof PaymentMethod
                                    ? $data['payment_method']->value
                                    : ($data['payment_method'] ?? 'cash'),
                    'amount'    => 0, // will be set to total after items are summed
                    'reference' => null,
                ]]);
                $singleMethodFallback = true;
            } else {
                $singleMethodFallback = false;
            }

            $creditAmount  = (int) $payments->where('method', 'credit')->sum('amount');
            $nonCreditPaid = (int) $payments->where('method', '!=', 'credit')->sum('amount');
            $isSplit       = $payments->count() > 1;
            $hasCredit     = $creditAmount > 0;

            // Primary method for the sale record (single-method or first non-credit)
            $primaryMethod = $isSplit
                ? PaymentMethod::CASH  // placeholder for mixed; real split stored in sale_payments
                : PaymentMethod::from(
                    $payments->first()['method'] instanceof PaymentMethod
                        ? $payments->first()['method']->value
                        : $payments->first()['method']
                  );

            // ── Create the sale header ────────────────────────────────────────────
            $sale = Sale::create([
                'sale_number'      => $this->generateSaleNumber(),
                'shop_id'          => $data['shop_id'],
                'type'             => $data['type'],
                'payment_method'   => $primaryMethod,
                'sold_by'          => auth()->id(),
                'sale_date'        => now(),
                'subtotal'         => 0,
                'tax'              => $data['tax'] ?? 0,
                'discount'         => $data['discount'] ?? 0,
                'total'            => 0,
                'has_price_override' => false,
                // Customer — store both FK and denormalized strings for receipts
                'customer_id'      => $data['customer_id'] ?? null,
                'customer_name'    => $data['customer_name'] ?? null,
                'customer_phone'   => $data['customer_phone'] ?? null,
                'notes'            => $data['notes'] ?? null,
                // Payment summary
                'is_split_payment' => $isSplit,
                'amount_paid'      => $nonCreditPaid,
                'credit_amount'    => $creditAmount,
                'has_credit'       => $hasCredit,
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

            // ── Record individual payment channel rows ────────────────────────────
            if ($singleMethodFallback) {
                // Single-method: the one payment covers the full total
                \App\Models\SalePayment::create([
                    'sale_id'        => $sale->id,
                    'payment_method' => $primaryMethod,
                    'amount'         => $total,
                    'reference'      => null,
                ]);
                $sale->update(['amount_paid' => $hasCredit ? 0 : $total]);
            } else {
                foreach ($payments as $pmt) {
                    $method = PaymentMethod::from(
                        $pmt['method'] instanceof PaymentMethod ? $pmt['method']->value : $pmt['method']
                    );
                    \App\Models\SalePayment::create([
                        'sale_id'        => $sale->id,
                        'payment_method' => $method,
                        'amount'         => (int) $pmt['amount'],
                        'reference'      => $pmt['reference'] ?? null,
                    ]);
                }
            }

            // ── Update customer credit account if credit was extended ─────────────
            if ($hasCredit && !empty($data['customer_id'])) {
                $customer = \App\Models\Customer::find($data['customer_id']);
                if ($customer) {
                    $customerService = new CustomerService();
                    $customerService->recordSalePurchase($customer, $creditAmount);
                }
            } elseif (!empty($data['customer_id'])) {
                $customer = \App\Models\Customer::find($data['customer_id']);
                if ($customer) {
                    $customer->update(['last_purchase_at' => now()]);
                }
            }

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

    /**
     * Create a warehouse-direct sale.
     * Boxes come from the warehouse, not the shop. Payment is collected at the shop.
     * Items are specified in full boxes only (no partial-box warehouse sales).
     */
    public function createWarehouseSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {

            // ── Resolve payment channels (same as createSale) ─────────────────────
            $payments = collect($data['payments'] ?? [])
                ->filter(fn ($p) => (int) $p['amount'] > 0)
                ->values();

            $singleMethodFallback = false;
            if ($payments->isEmpty()) {
                $payments = collect([[
                    'method'    => $data['payment_method'] instanceof PaymentMethod
                                    ? $data['payment_method']->value
                                    : ($data['payment_method'] ?? 'cash'),
                    'amount'    => 0,
                    'reference' => null,
                ]]);
                $singleMethodFallback = true;
            }

            $creditAmount  = (int) $payments->where('method', 'credit')->sum('amount');
            $nonCreditPaid = (int) $payments->where('method', '!=', 'credit')->sum('amount');
            $isSplit       = $payments->count() > 1;
            $hasCredit     = $creditAmount > 0;

            $primaryMethod = $isSplit
                ? PaymentMethod::CASH
                : PaymentMethod::from(
                    $payments->first()['method'] instanceof PaymentMethod
                        ? $payments->first()['method']->value
                        : $payments->first()['method']
                  );

            $warehouseId = (int) $data['source_warehouse_id'];

            // ── Create the sale header ────────────────────────────────────────────
            $sale = Sale::create([
                'sale_number'                => $this->generateSaleNumber(),
                'shop_id'                    => $data['shop_id'],
                'type'                       => SaleType::FULL_BOX,
                'payment_method'             => $primaryMethod,
                'sold_by'                    => auth()->id(),
                'sale_date'                  => now(),
                'subtotal'                   => 0,
                'tax'                        => 0,
                'discount'                   => 0,
                'total'                      => 0,
                'has_price_override'         => false,
                'customer_id'                => $data['customer_id'] ?? null,
                'customer_name'              => $data['customer_name'] ?? null,
                'customer_phone'             => $data['customer_phone'] ?? null,
                'notes'                      => $data['notes'] ?? null,
                'is_split_payment'           => $isSplit,
                'amount_paid'                => $nonCreditPaid,
                'credit_amount'              => $creditAmount,
                'has_credit'                 => $hasCredit,
                // Fulfillment fields
                'fulfillment_type'           => 'warehouse_direct',
                'source_warehouse_id'        => $warehouseId,
                'fulfillment_status'         => 'pending',
                'fulfillment_method'         => $data['fulfillment_method'],
                'fulfillment_transporter_id' => $data['fulfillment_transporter_id'] ?? null,
                'fulfillment_notes'          => $data['fulfillment_notes'] ?? null,
            ]);

            $subtotal = 0;

            foreach ($data['items'] as $itemData) {
                $product    = Product::findOrFail($itemData['product_id']);
                $boxesNeeded = (int) $itemData['boxes'];
                $boxPrice   = (int) $itemData['price']; // price per box

                // Select boxes FIFO from warehouse
                $boxes = Box::where('product_id', $product->id)
                    ->where('location_type', 'warehouse')
                    ->where('location_id', $warehouseId)
                    ->whereIn('status', ['full', 'partial'])
                    ->where('items_remaining', '>', 0)
                    ->orderBy('received_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->limit($boxesNeeded)
                    ->get();

                if ($boxes->count() < $boxesNeeded) {
                    throw new \Exception(
                        "Insufficient warehouse stock for {$product->name}. " .
                        "Needed {$boxesNeeded} boxes, only {$boxes->count()} available."
                    );
                }

                $priceModified  = (bool) ($itemData['price_modified'] ?? false);
                $priceReason    = $itemData['price_modification_reason'] ?? null;
                $actualUnitPrice = $product->items_per_box > 0
                    ? (int) round($boxPrice / $product->items_per_box)
                    : $product->selling_price;

                foreach ($boxes as $box) {
                    $itemsConsumed = $box->items_remaining;
                    $lineTotal     = $boxPrice;

                    $sale->items()->create([
                        'product_id'                 => $product->id,
                        'box_id'                     => $box->id,
                        'quantity_sold'              => $itemsConsumed,
                        'is_full_box'                => true,
                        'original_unit_price'        => $product->selling_price,
                        'actual_unit_price'          => $actualUnitPrice,
                        'line_total'                 => $lineTotal,
                        'price_was_modified'         => $priceModified,
                        'price_modification_reason'  => $priceReason,
                    ]);

                    $subtotal += $lineTotal;

                    // Consume box and record movement as 'direct_sale'
                    \App\Models\BoxMovement::create([
                        'box_id'             => $box->id,
                        'from_location_type' => 'warehouse',
                        'from_location_id'   => $warehouseId,
                        'to_location_type'   => null,
                        'to_location_id'     => null,
                        'movement_type'      => 'direct_sale',
                        'moved_by'           => auth()->id(),
                        'moved_at'           => now(),
                        'reference_type'     => 'sale',
                        'reference_id'       => $sale->id,
                        'reason'             => "Warehouse direct sale: {$sale->sale_number}",
                        'items_moved'        => $itemsConsumed,
                    ]);

                    $box->update([
                        'items_remaining' => 0,
                        'status'          => BoxStatus::EMPTY,
                    ]);
                }
            }

            $hasPriceOverride = collect($data['items'])->contains(
                fn ($i) => (bool) ($i['price_modified'] ?? false)
            );

            $sale->update([
                'subtotal'           => $subtotal,
                'total'              => $subtotal,
                'has_price_override' => $hasPriceOverride,
            ]);

            // ── Record payment rows ───────────────────────────────────────────────
            if ($singleMethodFallback) {
                \App\Models\SalePayment::create([
                    'sale_id'        => $sale->id,
                    'payment_method' => $primaryMethod,
                    'amount'         => $subtotal,
                    'reference'      => null,
                ]);
                $sale->update(['amount_paid' => $hasCredit ? 0 : $subtotal]);
            } else {
                foreach ($payments as $pmt) {
                    $method = PaymentMethod::from(
                        $pmt['method'] instanceof PaymentMethod ? $pmt['method']->value : $pmt['method']
                    );
                    \App\Models\SalePayment::create([
                        'sale_id'        => $sale->id,
                        'payment_method' => $method,
                        'amount'         => (int) $pmt['amount'],
                        'reference'      => $pmt['reference'] ?? null,
                    ]);
                }
            }

            // ── Credit handling ───────────────────────────────────────────────────
            if ($hasCredit && !empty($data['customer_id'])) {
                $customer = \App\Models\Customer::find($data['customer_id']);
                if ($customer) {
                    (new CustomerService())->recordSalePurchase($customer, $creditAmount);
                }
            } elseif (!empty($data['customer_id'])) {
                $customer = \App\Models\Customer::find($data['customer_id']);
                $customer?->update(['last_purchase_at' => now()]);
            }

            // ── Activity log ──────────────────────────────────────────────────────
            $shop = Shop::find($sale->shop_id);

            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()?->name,
                'action'            => 'warehouse_direct_sale',
                'entity_type'       => 'Sale',
                'entity_id'         => $sale->id,
                'entity_identifier' => $sale->sale_number,
                'details' => [
                    'total'          => $sale->total,
                    'shop_name'      => $shop?->name,
                    'warehouse_id'   => $warehouseId,
                    'item_count'     => count($data['items']),
                    'fulfillment'    => $data['fulfillment_method'],
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            Cache::flush();

            return $sale;
        });
    }

    /**
     * Create a sale from a mixed cart (shop stock + warehouse stock combined).
     * Shop items use FIFO consumeItems(); warehouse items use direct_sale BoxMovement.
     * If any warehouse items exist, fulfillment fields are set on the Sale.
     */
    public function createMixedSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {

            // ── Resolve payment channels ──────────────────────────────────────────
            $payments = collect($data['payments'] ?? [])
                ->filter(fn ($p) => (int) $p['amount'] > 0)
                ->values();

            $singleMethodFallback = false;
            if ($payments->isEmpty()) {
                $payments = collect([[
                    'method'    => $data['payment_method'] ?? 'cash',
                    'amount'    => 0,
                    'reference' => null,
                ]]);
                $singleMethodFallback = true;
            }

            $creditAmount  = (int) $payments->where('method', 'credit')->sum('amount');
            $nonCreditPaid = (int) $payments->where('method', '!=', 'credit')->sum('amount');
            $isSplit       = $payments->count() > 1;
            $hasCredit     = $creditAmount > 0;

            $primaryMethod = $isSplit
                ? PaymentMethod::CASH
                : PaymentMethod::from(
                    $payments->first()['method'] instanceof PaymentMethod
                        ? $payments->first()['method']->value
                        : $payments->first()['method']
                  );

            // ── Separate items by source ──────────────────────────────────────────
            $shopItems      = array_values(array_filter($data['items'], fn ($i) => ($i['source'] ?? 'shop') === 'shop'));
            $warehouseItems = array_values(array_filter($data['items'], fn ($i) => ($i['source'] ?? '') === 'warehouse'));
            $hasWarehouse   = count($warehouseItems) > 0;
            $warehouseId    = $hasWarehouse ? (int) ($data['source_warehouse_id'] ?? 0) : null;

            // ── Determine SaleType ────────────────────────────────────────────────
            $hasFullBox  = collect($data['items'])->contains(fn ($i) => ($i['mode'] ?? 'box') === 'box');
            $hasItemMode = collect($data['items'])->contains(fn ($i) => ($i['mode'] ?? 'box') === 'item');
            $saleType = ($hasFullBox && $hasItemMode)
                ? SaleType::MIXED
                : ($hasItemMode ? SaleType::INDIVIDUAL_ITEMS : SaleType::FULL_BOX);

            // ── Create the sale header ────────────────────────────────────────────
            $sale = Sale::create([
                'sale_number'      => $this->generateSaleNumber(),
                'shop_id'          => $data['shop_id'],
                'type'             => $saleType,
                'payment_method'   => $primaryMethod,
                'sold_by'          => auth()->id(),
                'sale_date'        => now(),
                'subtotal'         => 0,
                'tax'              => $data['tax'] ?? 0,
                'discount'         => $data['discount'] ?? 0,
                'total'            => 0,
                'has_price_override' => false,
                'customer_id'      => $data['customer_id'] ?? null,
                'customer_name'    => $data['customer_name'] ?? null,
                'customer_phone'   => $data['customer_phone'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'is_split_payment' => $isSplit,
                'amount_paid'      => $nonCreditPaid,
                'credit_amount'    => $creditAmount,
                'has_credit'       => $hasCredit,
                // Fulfillment — only set when warehouse items are present
                'fulfillment_type'           => $hasWarehouse ? 'warehouse_direct' : null,
                'source_warehouse_id'        => $hasWarehouse ? $warehouseId : null,
                'fulfillment_status'         => $hasWarehouse ? 'pending' : null,
                'fulfillment_method'         => $hasWarehouse ? ($data['fulfillment_method'] ?? null) : null,
                'fulfillment_transporter_id' => $hasWarehouse ? ($data['fulfillment_transporter_id'] ?? null) : null,
                'fulfillment_notes'          => $hasWarehouse ? ($data['fulfillment_notes'] ?? null) : null,
            ]);

            $subtotal         = 0;
            $hasPriceOverride = false;

            // ── Shop items (FIFO consumeItems path) ───────────────────────────────
            foreach ($shopItems as $itemData) {
                $product    = Product::findOrFail($itemData['product_id']);
                $isFullBox  = ($itemData['mode'] ?? 'box') === 'box';
                $itemsToSell = $isFullBox
                    ? (int) $itemData['qty'] * (int) $product->items_per_box
                    : (int) $itemData['qty'];
                $finalPrice    = (int) $itemData['price'];
                $originalPrice = $isFullBox
                    ? $product->calculateBoxPrice()
                    : $product->selling_price;

                if ($finalPrice !== $originalPrice) {
                    $hasPriceOverride = true;
                }

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
                        'product_id'                => $product->id,
                        'box_id'                    => $box->id,
                        'quantity_sold'             => $consume,
                        'is_full_box'               => $isFullBox && $consume === $box->items_remaining,
                        'original_unit_price'       => $originalPrice,
                        'actual_unit_price'         => $finalPrice,
                        'line_total'                => $lineTotal,
                        'price_was_modified'        => $finalPrice !== $originalPrice,
                        'price_modification_reason' => $itemData['price_modification_reason'] ?? null,
                    ]);

                    $subtotal += $lineTotal;
                    $box->consumeItems($consume, "Sale: {$sale->sale_number}", $sale->id, 'sale');
                    $remaining -= $consume;
                }

                if ($remaining > 0) {
                    throw new \Exception(
                        "Insufficient shop stock for {$product->name}. " .
                        "Needed {$itemsToSell} items, only " . ($itemsToSell - $remaining) . " available."
                    );
                }
            }

            // ── Warehouse items (direct_sale BoxMovement path) ────────────────────
            foreach ($warehouseItems as $itemData) {
                $product     = Product::findOrFail($itemData['product_id']);
                $boxesNeeded = (int) $itemData['qty'];
                $boxPrice    = (int) $itemData['price'];

                $boxes = Box::where('product_id', $product->id)
                    ->where('location_type', 'warehouse')
                    ->where('location_id', $warehouseId)
                    ->whereIn('status', ['full', 'partial'])
                    ->where('items_remaining', '>', 0)
                    ->orderBy('received_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->limit($boxesNeeded)
                    ->get();

                if ($boxes->count() < $boxesNeeded) {
                    throw new \Exception(
                        "Insufficient warehouse stock for {$product->name}. " .
                        "Needed {$boxesNeeded} boxes, only {$boxes->count()} available."
                    );
                }

                $priceModified   = (bool) ($itemData['price_modified'] ?? false);
                if ($priceModified) $hasPriceOverride = true;
                $actualUnitPrice = $product->items_per_box > 0
                    ? (int) round($boxPrice / $product->items_per_box)
                    : $product->selling_price;

                foreach ($boxes as $box) {
                    $itemsConsumed = $box->items_remaining;

                    $sale->items()->create([
                        'product_id'                => $product->id,
                        'box_id'                    => $box->id,
                        'quantity_sold'             => $itemsConsumed,
                        'is_full_box'               => true,
                        'original_unit_price'       => $product->selling_price,
                        'actual_unit_price'         => $actualUnitPrice,
                        'line_total'                => $boxPrice,
                        'price_was_modified'        => $priceModified,
                        'price_modification_reason' => $itemData['price_modification_reason'] ?? null,
                    ]);

                    $subtotal += $boxPrice;

                    \App\Models\BoxMovement::create([
                        'box_id'             => $box->id,
                        'from_location_type' => 'warehouse',
                        'from_location_id'   => $warehouseId,
                        'to_location_type'   => null,
                        'to_location_id'     => null,
                        'movement_type'      => 'direct_sale',
                        'moved_by'           => auth()->id(),
                        'moved_at'           => now(),
                        'reference_type'     => 'sale',
                        'reference_id'       => $sale->id,
                        'reason'             => "Mixed sale: {$sale->sale_number}",
                        'items_moved'        => $itemsConsumed,
                    ]);

                    $box->update([
                        'items_remaining' => 0,
                        'status'          => BoxStatus::EMPTY,
                    ]);
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

            // ── Record payment rows ───────────────────────────────────────────────
            if ($singleMethodFallback) {
                \App\Models\SalePayment::create([
                    'sale_id'        => $sale->id,
                    'payment_method' => $primaryMethod,
                    'amount'         => $total,
                    'reference'      => null,
                ]);
                $sale->update(['amount_paid' => $hasCredit ? 0 : $total]);
            } else {
                foreach ($payments as $pmt) {
                    $method = PaymentMethod::from(
                        $pmt['method'] instanceof PaymentMethod ? $pmt['method']->value : $pmt['method']
                    );
                    \App\Models\SalePayment::create([
                        'sale_id'        => $sale->id,
                        'payment_method' => $method,
                        'amount'         => (int) $pmt['amount'],
                        'reference'      => $pmt['reference'] ?? null,
                    ]);
                }
            }

            // ── Customer credit ───────────────────────────────────────────────────
            if ($hasCredit && !empty($data['customer_id'])) {
                $customer = \App\Models\Customer::find($data['customer_id']);
                if ($customer) {
                    (new CustomerService())->recordSalePurchase($customer, $creditAmount);
                }
            } elseif (!empty($data['customer_id'])) {
                $customer = \App\Models\Customer::find($data['customer_id']);
                $customer?->update(['last_purchase_at' => now()]);
            }

            // ── Activity log ──────────────────────────────────────────────────────
            $shop = Shop::find($sale->shop_id);
            ActivityLog::create([
                'user_id'           => auth()->id(),
                'user_name'         => auth()->user()?->name,
                'action'            => $hasWarehouse ? 'mixed_sale_created' : 'sale_created',
                'entity_type'       => 'Sale',
                'entity_id'         => $sale->id,
                'entity_identifier' => $sale->sale_number,
                'details' => [
                    'total'           => $sale->total,
                    'shop_name'       => $shop?->name,
                    'shop_items'      => count($shopItems),
                    'warehouse_items' => count($warehouseItems),
                    'fulfillment'     => $hasWarehouse ? ($data['fulfillment_method'] ?? null) : null,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            if ($hasPriceOverride) {
                ActivityLog::create([
                    'user_id'           => auth()->id(),
                    'user_name'         => auth()->user()?->name,
                    'action'            => 'price_modified',
                    'entity_type'       => 'Sale',
                    'entity_id'         => $sale->id,
                    'entity_identifier' => $sale->sale_number,
                    'details'           => ['total' => $sale->total, 'shop_name' => $shop?->name],
                    'ip_address'        => request()->ip(),
                    'user_agent'        => request()->header('User-Agent'),
                ]);
            }

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
