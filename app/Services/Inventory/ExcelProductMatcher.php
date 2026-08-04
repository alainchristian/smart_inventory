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

        // Preload everything once instead of querying per row (was causing
        // up to 6 DB round-trips per row and timing out on larger imports).
        $activeProducts = Product::where('is_active', true)->get();
        $productsByBarcode = $activeProducts->filter(fn ($p) => !empty($p->barcode))
            ->keyBy(fn ($p) => $p->barcode);
        $productsByNameLower = $activeProducts->keyBy(fn ($p) => strtolower($p->name));

        $productBarcodesByBarcode = ProductBarcode::where('is_active', true)
            ->with('product')
            ->get()
            ->filter(fn ($pb) => $pb->product)
            ->keyBy('barcode');

        $categories = Category::all();
        $categoriesByNameLower = $categories->keyBy(fn ($c) => strtolower($c->name));

        foreach ($rows as $index => $row) {
            $barcode = trim($row['barcode'] ?? '');
            $productName = trim($row['product_name'] ?? '');
            $sku = trim($row['sku'] ?? '');
            $categoryName = trim($row['category'] ?? '');
            $itemsPerBox = (int)($row['items_per_box'] ?? 0);
            $boxPurchasePrice = (float)($row['box_purchase_price'] ?? 0);
            $boxSellingPrice  = (float)($row['box_selling_price'] ?? 0);
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
                $category = $this->findCategoryByName($categoryName, $categoriesByNameLower, $categories);
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
                'box_purchase_price' => $boxPurchasePrice,
                'box_selling_price'  => $boxSellingPrice,
                'boxes' => $boxes,
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate,
            ];

            // STEP 1: Try to find by BARCODE
            $productByBarcode = null;
            if (!empty($barcode)) {
                $productByBarcode = $productBarcodesByBarcode->get($barcode)?->product
                    ?? $productsByBarcode->get($barcode);
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
                $productByName = $this->findProductByName($productName, $productsByNameLower, $activeProducts);
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
            $rowData['has_complete_info'] = !empty($categoryName) && $itemsPerBox > 0 && $boxSellingPrice > 0;
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
     * Find product by name (case-insensitive, fuzzy) against a preloaded set —
     * avoids one DB round-trip per Excel row.
     */
    private function findProductByName(string $name, Collection $productsByNameLower, Collection $activeProducts): ?Product
    {
        if (empty($name)) {
            return null;
        }

        // Exact match (case-insensitive)
        $product = $productsByNameLower->get(strtolower($name));
        if ($product) {
            return $product;
        }

        // Partial match
        $needle = strtolower($name);
        return $activeProducts->first(fn ($p) => str_contains(strtolower($p->name), $needle));
    }

    /**
     * Find category by name (case-insensitive) against a preloaded set —
     * avoids one DB round-trip per Excel row.
     */
    private function findCategoryByName(string $name, Collection $categoriesByNameLower, Collection $categories): ?Category
    {
        if (empty($name)) {
            return null;
        }

        // Exact match
        $category = $categoriesByNameLower->get(strtolower($name));
        if ($category) {
            return $category;
        }

        // Partial match
        $needle = strtolower($name);
        return $categories->first(fn ($c) => str_contains(strtolower($c->name), $needle));
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
