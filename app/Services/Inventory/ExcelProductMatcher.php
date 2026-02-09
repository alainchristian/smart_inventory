<?php

namespace App\Services\Inventory;

use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Category;
use Illuminate\Support\Collection;

class ExcelProductMatcher
{
    /**
     * Process Excel rows with complete product info
     */
    public function processRows(array $rows): array
    {
        $recognized = [];
        $unrecognized = [];
        $barcodeAssociations = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $barcode = trim($row['barcode'] ?? '');
            $productName = trim($row['product_name'] ?? '');
            $sku = trim($row['sku'] ?? '');
            $categoryName = trim($row['category'] ?? '');
            $itemsPerBox = (int)($row['items_per_box'] ?? 0);
            $sellingPrice = (float)($row['selling_price'] ?? 0);
            $boxes = (int)($row['boxes'] ?? 0);
            $batchNumber = trim($row['batch_number'] ?? '') ?: null;
            $expiryDate = $this->parseDate($row['expiry_date'] ?? null);

            // Validation
            if (empty($productName)) {
                $errors[] = [
                    'row' => $index + 2,
                    'error' => 'Product name is required',
                    'data' => $row,
                ];
                continue;
            }

            if ($boxes < 1 || $boxes > 1000) {
                $errors[] = [
                    'row' => $index + 2,
                    'error' => 'Number of boxes must be between 1 and 1000',
                    'data' => $row,
                ];
                continue;
            }

            if ($itemsPerBox < 1) {
                $errors[] = [
                    'row' => $index + 2,
                    'error' => 'Items per box must be at least 1',
                    'data' => $row,
                ];
                continue;
            }

            // Find category by name
            $category = null;
            if (!empty($categoryName)) {
                $category = $this->findCategoryByName($categoryName);
            }

            $rowData = [
                'row_number' => $index + 2,
                'barcode' => $barcode,
                'product_name' => $productName,
                'original_name' => $productName,
                'sku' => $sku,
                'category_name' => $categoryName,
                'category_id' => $category?->id,
                'items_per_box' => $itemsPerBox,
                'selling_price' => $sellingPrice,
                'boxes' => $boxes,
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate,
            ];

            // STEP 1: Try to find by BARCODE
            $productByBarcode = null;
            if (!empty($barcode)) {
                $productByBarcode = $this->findProductByBarcode($barcode);
            }

            if ($productByBarcode) {
                // ✅ FOUND BY BARCODE
                $rowData['product_id'] = $productByBarcode->id;
                $rowData['matched_product_name'] = $productByBarcode->name;
                $rowData['product_sku'] = $productByBarcode->sku;
                $rowData['matched_items_per_box'] = $productByBarcode->items_per_box;
                $rowData['status'] = 'recognized';
                $rowData['match_method'] = 'barcode';
                $recognized[] = $rowData;
                continue;
            }

            // STEP 2: Try by PRODUCT NAME
            $productByName = null;
            if (!empty($productName)) {
                $productByName = $this->findProductByName($productName);
            }

            if ($productByName) {
                // ✅ FOUND BY NAME
                $rowData['product_id'] = $productByName->id;
                $rowData['matched_product_name'] = $productByName->name;
                $rowData['product_sku'] = $productByName->sku;
                $rowData['matched_items_per_box'] = $productByName->items_per_box;
                $rowData['status'] = 'recognized';
                $rowData['match_method'] = 'name';

                // New barcode association needed
                if (!empty($barcode)) {
                    $rowData['new_barcode'] = true;
                    $rowData['needs_barcode_association'] = true;
                    $barcodeAssociations[] = [
                        'product_id' => $productByName->id,
                        'barcode' => $barcode,
                        'product_name' => $productByName->name,
                    ];
                }

                $recognized[] = $rowData;
                continue;
            }

            // STEP 3: NOT FOUND - New product (with complete info from Excel)
            $rowData['status'] = 'unrecognized';
            $rowData['needs_confirmation'] = true;
            $rowData['match_method'] = 'none';
            $rowData['has_complete_info'] = !empty($categoryName) && $itemsPerBox > 0 && $sellingPrice > 0;
            $unrecognized[] = $rowData;
        }

        return [
            'recognized' => $recognized,
            'unrecognized' => $unrecognized,
            'barcode_associations' => $barcodeAssociations,
            'errors' => $errors,
        ];
    }

    /**
     * Find product by barcode
     */
    private function findProductByBarcode(string $barcode): ?Product
    {
        $productBarcode = ProductBarcode::where('barcode', $barcode)
            ->where('is_active', true)
            ->with('product')
            ->first();

        if ($productBarcode && $productBarcode->product) {
            return $productBarcode->product;
        }

        return Product::where('barcode', $barcode)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Find product by name (case-insensitive, fuzzy)
     */
    private function findProductByName(string $name): ?Product
    {
        if (empty($name)) {
            return null;
        }

        // Exact match (case-insensitive)
        $product = Product::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where('is_active', true)
            ->first();

        if ($product) {
            return $product;
        }

        // Partial match
        $product = Product::where('name', 'like', "%{$name}%")
            ->where('is_active', true)
            ->first();

        return $product;
    }

    /**
     * Find category by name (case-insensitive)
     */
    private function findCategoryByName(string $name): ?Category
    {
        if (empty($name)) {
            return null;
        }

        // Exact match
        $category = Category::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if ($category) {
            return $category;
        }

        // Partial match
        return Category::where('name', 'like', "%{$name}%")->first();
    }

    /**
     * Live search products by name (for dropdown)
     */
    public function liveSearchProducts(string $query): Collection
    {
        if (strlen($query) < 2) {
            return collect([]);
        }

        return Product::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->limit(10)
            ->get();
    }

    /**
     * Associate barcode to product
     */
    public function associateBarcode(int $productId, string $barcode): void
    {
        $exists = ProductBarcode::where('barcode', $barcode)->exists();

        if ($exists) {
            return;
        }

        ProductBarcode::create([
            'product_id' => $productId,
            'barcode' => $barcode,
            'barcode_type' => 'supplier',
            'notes' => 'Auto-associated from Excel import',
            'is_active' => true,
        ]);
    }

    /**
     * Parse date from Excel
     */
    private function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            }

            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Suggest similar products
     */
    public function suggestSimilarProducts(string $name, int $limit = 5): Collection
    {
        if (empty($name)) {
            return collect([]);
        }

        return Product::where('is_active', true)
            ->where(function($query) use ($name) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%'])
                      ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . strtolower($name) . '%']);
            })
            ->limit($limit)
            ->get();
    }
}
