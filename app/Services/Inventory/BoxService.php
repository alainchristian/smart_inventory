<?php

namespace App\Services\Inventory;

use App\Enums\BoxStatus;
use App\Enums\LocationType;
use App\Models\Box;
use App\Models\BoxMovement;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class BoxService
{
    /**
     * Create multiple boxes for a product
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $numberOfBoxes
     * @param array $optional (batch_number, expiry_date, supplier_barcode)
     * @return array Created boxes
     */
    public function createBoxes(int $productId, int $warehouseId, int $numberOfBoxes, array $optional = []): array
    {
        return DB::transaction(function () use ($productId, $warehouseId, $numberOfBoxes, $optional) {
            $product = Product::findOrFail($productId);
            $warehouse = Warehouse::findOrFail($warehouseId);

            $boxes = [];
            $codes = $this->generateBoxCodes($numberOfBoxes);

            for ($i = 0; $i < $numberOfBoxes; $i++) {
                $box = Box::create([
                    'product_id' => $product->id,
                    'box_code' => $codes[$i],
                    'supplier_barcode' => $optional['supplier_barcode'] ?? null,
                    'status' => BoxStatus::FULL,
                    'items_total' => $product->items_per_box,
                    'items_remaining' => $product->items_per_box,
                    'location_type' => LocationType::WAREHOUSE,
                    'location_id' => $warehouse->id,
                    'received_by' => auth()->id(),
                    'received_at' => now(),
                    'batch_number' => $optional['batch_number'] ?? null,
                    'expiry_date' => $optional['expiry_date'] ?? null,
                ]);

                // Create movement record
                BoxMovement::create([
                    'box_id' => $box->id,
                    'from_location_type' => null,
                    'from_location_id' => null,
                    'to_location_type' => LocationType::WAREHOUSE,
                    'to_location_id' => $warehouse->id,
                    'movement_type' => 'received',
                    'moved_by' => auth()->id(),
                    'moved_at' => now(),
                    'reason' => 'Initial warehouse receipt',
                ]);

                $boxes[] = $box;
            }

            return $boxes;
        });
    }

    /**
     * Generate unique box code
     * Format: BOX-YYYYMMDD-XXXXX
     */
    public function generateBoxCode(): string
    {
        return $this->generateBoxCodes(1)[0];
    }

    /**
     * Generate a batch of unique, sequential box codes with a fixed query
     * cost instead of a COUNT + uniqueness-check pair per code (that pattern
     * made bulk receives/imports do 2+ DB round-trips per box).
     * Format: BOX-YYYYMMDD-XXXXX
     */
    public function generateBoxCodes(int $count): array
    {
        $date = now()->format('Ymd');
        $sequence = Box::whereDate('created_at', today())->count() + 1;

        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = "BOX-{$date}-" . str_pad($sequence + $i, 5, '0', STR_PAD_LEFT);
        }

        // Ensure uniqueness in one round-trip rather than one exists() check per code
        $collisions = Box::whereIn('box_code', $codes)->pluck('box_code')->all();
        while (!empty($collisions)) {
            $sequence += $count;
            $codes = [];
            for ($i = 0; $i < $count; $i++) {
                $codes[] = "BOX-{$date}-" . str_pad($sequence + $i, 5, '0', STR_PAD_LEFT);
            }
            $collisions = Box::whereIn('box_code', $codes)->pluck('box_code')->all();
        }

        return $codes;
    }

    /**
     * Find product by barcode or SKU
     * Searches both product.barcode and product_barcodes table
     */
    public function findProduct(string $identifier): ?Product
    {
        // First try product_barcodes table (aliases)
        $product = ProductBarcode::findProductByBarcode($identifier);

        if ($product) {
            return $product;
        }

        // Fallback to direct product barcode or SKU
        return Product::where('barcode', $identifier)
            ->orWhere('sku', $identifier)
            ->first();
    }

    /**
     * Bulk create from Excel data
     *
     * @param array $excelData [['product_barcode' => '123', 'boxes' => 5, 'batch' => 'B1'], ...]
     * @param int $warehouseId
     * @return array ['created' => [...], 'errors' => [...]]
     */
    public function bulkCreateFromExcel(array $excelData, int $warehouseId): array
    {
        $created = [];
        $errors = [];

        foreach ($excelData as $index => $row) {
            try {
                // Find product
                $product = $this->findProduct($row['product_barcode']);
                if (!$product) {
                    throw new \Exception("Product not found: {$row['product_barcode']}");
                }

                // Create boxes
                $boxes = $this->createBoxes(
                    $product->id,
                    $warehouseId,
                    $row['boxes'],
                    [
                        'batch_number' => $row['batch_number'] ?? null,
                        'expiry_date' => $row['expiry_date'] ?? null,
                    ]
                );

                $created = array_merge($created, $boxes);

            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                    'data' => $row,
                ];
            }
        }

        return [
            'created' => $created,
            'errors' => $errors,
        ];
    }
}
