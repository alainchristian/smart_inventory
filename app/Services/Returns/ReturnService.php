<?php

namespace App\Services\Returns;

use App\Enums\BoxStatus;
use App\Enums\DispositionType;
use App\Enums\LocationType;
use App\Enums\ReturnReason;
use App\Enums\AlertSeverity;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Box;
use App\Models\BoxMovement;
use App\Models\DamagedGood;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function generateReturnNumber(): string
    {
        $date = now()->format('Ymd');
        $count = ReturnModel::whereDate('created_at', today())->count() + 1;
        $sequence = str_pad($count, 5, '0', STR_PAD_LEFT);

        return "RET-{$date}-{$sequence}";
    }

    /**
     * Process a return.
     *
     * @param array $data Expected keys:
     *   - shop_id (int)
     *   - sale_id (int|null) - Optional link to existing sale
     *   - reason (ReturnReason|string)
     *   - customer_name (string|null)
     *   - customer_phone (string|null)
     *   - is_exchange (bool)
     *   - notes (string|null)
     *   - items (array) - Array of items with keys:
     *     - product_id (int)
     *     - quantity_returned (int)
     *     - quantity_damaged (int)
     *     - condition_notes (string|null)
     *     - original_sale_item_id (int|null)
     *     - is_replacement (bool) - For exchanges
     *     - replacement_product_id (int|null) - For exchanges
     * @param User $processedBy
     * @return ReturnModel
     */
    public function processReturn(array $data, User $processedBy): ReturnModel
    {
        return DB::transaction(function () use ($data, $processedBy) {

            // Convert reason to enum if it's a string
            $reason = $data['reason'];
            if (is_string($reason)) {
                $reason = ReturnReason::from($reason);
            }

            // Calculate refund amount if not provided and not an exchange
            $refundAmount = 0;
            if (!($data['is_exchange'] ?? false)) {
                // Use estimated refund if provided, otherwise calculate from items
                if (isset($data['estimated_refund'])) {
                    $refundAmount = $data['estimated_refund'];
                } elseif (isset($data['items']) && is_array($data['items'])) {
                    // Calculate from returned items
                    foreach ($data['items'] as $item) {
                        if (isset($item['unit_price']) && isset($item['quantity_returned'])) {
                            $refundAmount += $item['unit_price'] * $item['quantity_returned'];
                        }
                    }
                }
            }

            // Create return record
            $return = ReturnModel::create([
                'return_number' => $this->generateReturnNumber(),
                'sale_id' => $data['sale_id'] ?? null,
                'shop_id' => $data['shop_id'],
                'reason' => $reason,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'refund_amount' => $refundAmount,
                'refund_method' => $data['refund_method'] ?? null,
                'is_exchange' => $data['is_exchange'] ?? false,
                'processed_by' => $processedBy->id,
                'processed_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Process return items
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $quantityReturned = $itemData['quantity_returned'];
                $quantityDamaged = $itemData['quantity_damaged'] ?? 0;
                $quantityGood = $quantityReturned - $quantityDamaged;

                // Create return item record
                $returnItem = $return->items()->create([
                    'product_id' => $product->id,
                    'quantity_returned' => $quantityReturned,
                    'quantity_damaged' => $quantityDamaged,
                    'quantity_good' => $quantityGood,
                    'original_sale_item_id' => $itemData['original_sale_item_id'] ?? null,
                    'is_replacement' => $itemData['is_replacement'] ?? false,
                    'condition_notes' => $itemData['condition_notes'] ?? null,
                    'photo_path' => $itemData['photo_path'] ?? null,
                ]);

                // Return good items to inventory
                if ($quantityGood > 0) {
                    $this->returnItemsToInventory(
                        $product,
                        $data['shop_id'],
                        $quantityGood,
                        $return,
                        $processedBy
                    );
                }

                // Create damaged goods records for damaged items
                if ($quantityDamaged > 0) {
                    $this->createDamagedGoodsRecord(
                        $product,
                        $data['shop_id'],
                        $quantityDamaged,
                        $return,
                        $processedBy,
                        $itemData['condition_notes'] ?? 'Returned damaged',
                        $itemData['photo_path'] ?? null
                    );
                }

                // Handle exchange replacements
                if (($itemData['is_replacement'] ?? false) && isset($itemData['replacement_product_id'])) {
                    $replacementProduct = Product::find($itemData['replacement_product_id']);
                    if ($replacementProduct) {
                        // Select a box for the replacement
                        $replacementBox = Box::where('product_id', $replacementProduct->id)
                            ->where('location_type', LocationType::SHOP)
                            ->where('location_id', $data['shop_id'])
                            ->whereIn('status', [BoxStatus::FULL, BoxStatus::PARTIAL])
                            ->where('items_remaining', '>=', $quantityReturned)
                            ->orderBy('received_at', 'asc')
                            ->first();

                        if ($replacementBox) {
                            // Update return item with replacement box
                            $returnItem->update([
                                'replacement_box_id' => $replacementBox->id,
                            ]);

                            // Consume items from replacement box
                            $replacementBox->consumeItems(
                                $quantityReturned,
                                "Exchange replacement for return: {$return->return_number}",
                                $return->id,
                                'return'
                            );
                        }
                    }
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $processedBy->id,
                'user_name' => $processedBy->name,
                'action' => 'processed',
                'entity_type' => 'ReturnModel',
                'entity_id' => $return->id,
                'entity_identifier' => $return->return_number,
                'old_values' => null,
                'new_values' => [
                    'return_number' => $return->return_number,
                    'shop_id' => $return->shop_id,
                    'reason' => $return->reason->value,
                    'is_exchange' => $return->is_exchange,
                    'refund_amount' => $return->refund_amount,
                ],
                'details' => [
                    'items_count' => count($data['items']),
                    'total_quantity' => array_sum(array_column($data['items'], 'quantity_returned')),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            // Note: Large return alerts are now handled in ProcessReturn component
            // This legacy alert code is commented out to avoid duplication

            return $return;
        });
    }

    /**
     * Approve a return.
     *
     * @param ReturnModel $return
     * @param User $approvedBy
     * @return ReturnModel
     */
    public function approveReturn(ReturnModel $return, User $approvedBy): ReturnModel
    {
        if ($return->approved_at) {
            throw new \Exception('Return is already approved');
        }

        return DB::transaction(function () use ($return, $approvedBy) {
            $return->update([
                'approved_by' => $approvedBy->id,
                'approved_at' => now(),
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $approvedBy->id,
                'user_name' => $approvedBy->name,
                'action' => 'approved',
                'entity_type' => 'ReturnModel',
                'entity_id' => $return->id,
                'entity_identifier' => $return->return_number,
                'old_values' => [
                    'approved_at' => null,
                    'approved_by' => null,
                ],
                'new_values' => [
                    'approved_at' => $return->approved_at->toIso8601String(),
                    'approved_by' => $approvedBy->id,
                ],
                'details' => [
                    'refund_amount' => $return->refund_amount,
                    'is_exchange' => $return->is_exchange,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);

            // Create alert for shop manager
            Alert::create([
                'title' => 'Return Approved',
                'message' => "Return {$return->return_number} has been approved",
                'severity' => AlertSeverity::INFO,
                'entity_type' => 'return',
                'entity_id' => $return->id,
                'user_id' => $return->processed_by,
                'action_url' => route('shop.returns.index'),
                'action_label' => 'View Returns',
            ]);

            return $return;
        });
    }

    /**
     * Return items to inventory by finding or creating partial boxes.
     */
    protected function returnItemsToInventory(
        Product $product,
        int $shopId,
        int $quantity,
        ReturnModel $return,
        User $processedBy
    ): void {
        // Find partial boxes first (LIFO - most recently used)
        $boxes = Box::where('product_id', $product->id)
            ->where('location_type', LocationType::SHOP)
            ->where('location_id', $shopId)
            ->where('status', BoxStatus::PARTIAL)
            ->where('items_remaining', '<', DB::raw('items_total'))
            ->orderBy('received_at', 'desc')
            ->get();

        $remaining = $quantity;

        foreach ($boxes as $box) {
            if ($remaining <= 0) break;

            $canReturn = min($remaining, $box->items_total - $box->items_remaining);

            if ($canReturn > 0) {
                $box->increment('items_remaining', $canReturn);

                // Update box status
                if ($box->items_remaining >= $box->items_total) {
                    $box->update(['status' => BoxStatus::FULL]);
                } else {
                    $box->update(['status' => BoxStatus::PARTIAL]);
                }

                // Log box movement
                BoxMovement::create([
                    'box_id' => $box->id,
                    'from_location_type' => null,
                    'from_location_id' => null,
                    'to_location_type' => $box->location_type,
                    'to_location_id' => $box->location_id,
                    'movement_type' => 'return',
                    'moved_by' => $processedBy->id,
                    'moved_at' => now(),
                    'reference_type' => 'return',
                    'reference_id' => $return->id,
                    'reason' => "Return: {$return->return_number}",
                    'items_moved' => $canReturn,
                    'notes' => "Returned {$canReturn} items. Remaining: " . ($box->items_remaining - $canReturn) . " → {$box->items_remaining}",
                ]);

                $remaining -= $canReturn;
            }
        }

        // If still have remaining items, note in activity log
        // In production, you might want to create a new partial box or alert for manager review
        if ($remaining > 0) {
            ActivityLog::create([
                'user_id' => $processedBy->id,
                'user_name' => $processedBy->name,
                'action' => 'return_overflow',
                'entity_type' => 'ReturnModel',
                'entity_id' => $return->id,
                'entity_identifier' => $return->return_number,
                'old_values' => null,
                'new_values' => [
                    'product_id' => $product->id,
                    'quantity_not_returned' => $remaining,
                ],
                'details' => [
                    'message' => "Could not return {$remaining} items of {$product->name} - no partial boxes available",
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);
        }
    }

    /**
     * Create a damaged goods record.
     */
    protected function createDamagedGoodsRecord(
        Product $product,
        int $shopId,
        int $quantity,
        ReturnModel $return,
        User $processedBy,
        string $description,
        ?string $photoPath = null
    ): void {
        $estimatedLoss = $product->purchase_price * $quantity;

        $photos = $photoPath ? [$photoPath] : null;

        DamagedGood::create([
            'damage_reference' => "RET-DMG-{$return->return_number}",
            'source_type' => 'return',
            'source_id' => $return->id,
            'product_id' => $product->id,
            'quantity_damaged' => $quantity,
            'box_id' => null,
            'location_type' => LocationType::SHOP,
            'location_id' => $shopId,
            'disposition' => DispositionType::PENDING,
            'damage_description' => $description,
            'photos' => $photos,
            'estimated_loss' => $estimatedLoss,
            'recorded_by' => $processedBy->id,
            'recorded_at' => now(),
        ]);
    }
}
