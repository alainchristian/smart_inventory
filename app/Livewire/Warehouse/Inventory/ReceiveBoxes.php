<?php

namespace App\Livewire\Warehouse\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Warehouse;
use App\Services\Inventory\BoxService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ReceiveBoxes extends Component
{
    use WithFileUploads;

    // Warehouse
    public ?int $warehouseId = null;

    // Manual Mode
    public ?string $productBarcode = null;
    public ?int $productId = null;
    public ?string $productName = null;
    public int $numberOfBoxes = 1;
    public ?string $batchNumber = null;
    public ?string $expiryDate = null;

    // Excel Mode
    public $excelFile;
    public bool $showExcelPreview = false;
    public array $excelPreview = [];
    public array $excelErrors = [];

    // Excel with Barcode Recognition
    public array $excelRecognized = [];
    public array $excelUnknown = [];
    public array $unknownBarcodeSelections = [];
    public array $unknownBarcodeRemember = [];
    public array $productSearches = [];
    public array $productSearchResults = [];
    public array $excelBarcodeAssociations = [];

    // Excel New Product Editing
    public array $editableProductNames = [];
    public array $editableProductSearchQuery = [];  // NEW - for live search
    public array $liveSearchResults = [];           // NEW - search results
    public array $selectedProducts = [];            // NEW - selected from search
    public array $editableProductSkus = [];
    public array $editableProductBarcodes = [];
    public array $editableProductCategories = [];
    public array $editableProductItemsPerBox = [];
    public array $editableProductPrices = [];
    public array $productSuggestions = [];
    public array $excelHasDifferentValues = [];     // Track if Excel values differ from DB
    public array $shouldUpdateProduct = [];         // User choice to update product

    // UI State
    public bool $manualMode = true;
    public bool $showReceiveModal = false;
    public bool $isNewProduct = false;
    public bool $barcodeIsKnown = false;
    public bool $showProductSearch = false;

    // Product Search
    public string $productSearch = '';
    public array $searchResults = [];
    public ?int $selectedProductId = null;
    public ?string $selectedProductName = null;

    // Product Dropdown Mode (alternative to barcode scanning)
    public bool $showProductDropdown = false;

    // Barcode Association
    public bool $rememberBarcode = true;
    public ?string $supplierName = null;

    // New Product Fields
    public string $newProductName = '';
    public string $newProductSku = '';
    public ?int $newProductCategoryId = null;
    public int $newProductItemsPerBox = 1;
    public float $newProductSellingPrice = 0;

    // Results
    public array $recentBoxes = [];
    public int $sessionTotal = 0;

    protected $rules = [
        'warehouseId' => 'required|exists:warehouses,id',
        'numberOfBoxes' => 'required|integer|min:1|max:100',
        'batchNumber' => 'nullable|string|max:50',
        'expiryDate' => 'nullable|date|after:today',
    ];

    protected $listeners = ['barcode-scanned' => 'handleBarcodeScan'];

    public function mount()
    {
        $user = auth()->user();

        // Auto-select warehouse for warehouse managers
        if ($user->isWarehouseManager()) {
            $this->warehouseId = $user->location_id;

            // Log for debugging
            \Log::info('Auto-selected warehouse', [
                'user_id' => $user->id,
                'warehouse_id' => $this->warehouseId,
                'location_type' => $user->location_type,
                'location_id' => $user->location_id,
            ]);
        }
    }

    /**
     * Validate warehouse is selected
     */
    private function validateWarehouseSelected(): bool
    {
        if (empty($this->warehouseId)) {
            session()->flash('error', '⚠️ Please select a warehouse first');
            return false;
        }
        return true;
    }

    /**
     * Debug method to test warehouse state
     */
    public function testWarehouseState()
    {
        dd([
            'warehouseId' => $this->warehouseId,
            'type' => gettype($this->warehouseId),
            'empty' => empty($this->warehouseId),
            'isset' => isset($this->warehouseId),
            'is_null' => is_null($this->warehouseId),
            'user_type' => auth()->user()->location_type,
            'user_location_id' => auth()->user()->location_id,
            'user_is_warehouse_manager' => auth()->user()->isWarehouseManager(),
        ]);
    }

    /**
     * Auto-search when barcode changes
     */
    public function updated($property)
    {
        if ($property === 'productBarcode' && strlen($this->productBarcode) >= 8) {
            $this->searchProduct();
        }

        // Real-time product search
        if ($property === 'productSearch') {
            $this->performProductSearch();
        }

        // Real-time product search for unknown barcodes in Excel mode
        if (str_starts_with($property, 'productSearches.')) {
            $barcode = str_replace('productSearches.', '', $property);
            $this->searchProductForBarcode($barcode);
        }
    }

    public function handleBarcodeScan($barcode)
    {
        $this->productBarcode = $barcode;
        $this->searchProduct();
    }

    /**
     * Search for product by barcode
     */
    public function searchProduct()
    {
        $this->resetErrorBag();

        // CRITICAL: Check warehouse is selected first
        if (!$this->validateWarehouseSelected()) {
            return;
        }

        if (empty($this->productBarcode)) {
            $this->addError('productBarcode', 'Please enter a product barcode or SKU');
            return;
        }

        $barcode = trim($this->productBarcode);
        $boxService = app(BoxService::class);
        $product = $boxService->findProduct($barcode);

        if ($product) {
            // ✅ Barcode is KNOWN - Fast path
            $this->productId = $product->id;
            $this->productName = $product->name;
            $this->barcodeIsKnown = true;
            $this->showProductSearch = false;
            $this->isNewProduct = false;
            $this->showReceiveModal = true;
            return;
        }

        // ❌ Barcode is UNKNOWN - Need to link to product
        $this->productId = null;
        $this->productName = null;
        $this->barcodeIsKnown = false;
        $this->showProductSearch = true;
        $this->isNewProduct = false;
        $this->selectedProductId = null;
        $this->selectedProductName = null;
        $this->productSearch = '';
        $this->searchResults = [];
        $this->showReceiveModal = true;
    }

    /**
     * Search products by name/SKU for unknown barcodes
     */
    public function performProductSearch()
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::where('is_active', true)
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->productSearch}%")
                    ->orWhere('sku', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Select a product from search results
     */
    public function selectProduct(int $productId)
    {
        $product = Product::find($productId);

        if ($product) {
            $this->selectedProductId = $product->id;
            $this->selectedProductName = $product->name;
            $this->productId = $product->id;
            $this->productName = $product->name;
        }
    }

    /**
     * Open product dropdown selector
     */
    public function openProductDropdown()
    {
        // Check warehouse is selected
        if (empty($this->warehouseId)) {
            session()->flash('error', '⚠️ Please select a warehouse first');
            return;
        }

        $this->showProductDropdown = true;
        $this->showReceiveModal = true;
        $this->isNewProduct = false;
        $this->barcodeIsKnown = false;
        $this->showProductSearch = false;
        $this->productSearch = '';
        $this->searchResults = [];
        $this->selectedProductId = null;
        $this->productBarcode = null;
    }

    /**
     * Select product from dropdown and proceed to receive boxes
     */
    public function selectProductFromDropdown(int $productId)
    {
        $product = Product::find($productId);

        if ($product) {
            $this->productId = $product->id;
            $this->productName = $product->name;
            $this->selectedProductId = $product->id;
            $this->selectedProductName = $product->name;
            $this->showProductDropdown = false;
        }
    }

    /**
     * Switch to create new product mode
     */
    public function createNewProduct()
    {
        $this->isNewProduct = true;
        $this->showProductDropdown = false;
        $this->showReceiveModal = true;
        $this->barcodeIsKnown = false;
        $this->showProductSearch = false;

        // Generate a temporary barcode/SKU suggestion
        $this->productBarcode = 'SKU-' . strtoupper(substr(uniqid(), -8));
        $this->newProductSku = $this->productBarcode;
    }

    public function clearProduct()
    {
        $this->productId = null;
        $this->productName = null;
        $this->productBarcode = null;
        $this->showReceiveModal = false;
        $this->isNewProduct = false;
        $this->barcodeIsKnown = false;
        $this->showProductSearch = false;
        $this->showProductDropdown = false;
        $this->selectedProductId = null;
        $this->selectedProductName = null;
        $this->productSearch = '';
        $this->searchResults = [];
        $this->resetErrorBag();
    }

    /**
     * Close receive modal
     */
    public function closeReceiveModal()
    {
        $this->showReceiveModal = false;
        $this->isNewProduct = false;
        $this->barcodeIsKnown = false;
        $this->showProductSearch = false;
        $this->showProductDropdown = false;
        $this->numberOfBoxes = 1;
        $this->batchNumber = null;
        $this->expiryDate = null;
        $this->selectedProductId = null;
        $this->selectedProductName = null;
        $this->productSearch = '';
        $this->searchResults = [];
        $this->supplierName = null;
        $this->reset([
            'newProductName',
            'newProductSku',
            'newProductCategoryId',
            'newProductItemsPerBox',
            'newProductSellingPrice'
        ]);
        $this->resetErrorBag();
    }

    /**
     * Create boxes (and product if new, or barcode association if needed)
     */
    public function createBoxes()
    {
        // CRITICAL: Check warehouse is selected first
        if (empty($this->warehouseId)) {
            session()->flash('error', '⚠️ Please select a warehouse first');
            $this->closeReceiveModal();
            return;
        }

        // Build validation rules
        $rules = $this->rules;

        if ($this->isNewProduct) {
            $rules['newProductName'] = 'required|string|max:255';
            $rules['newProductSku'] = 'required|string|max:100|unique:products,sku';
            $rules['newProductCategoryId'] = 'required|exists:categories,id';
            $rules['newProductItemsPerBox'] = 'required|integer|min:1|max:1000';
            $rules['newProductSellingPrice'] = 'required|numeric|min:0';

            // Barcode is optional when creating from product dropdown, but must be unique if provided
            if (!empty($this->productBarcode)) {
                $rules['productBarcode'] = 'unique:products,barcode';
            }
        } else {
            $rules['productId'] = 'required|exists:products,id';
        }

        $this->validate($rules);

        try {
            $boxService = app(BoxService::class);

            \Illuminate\Support\Facades\DB::transaction(function () use ($boxService) {
                // Step 1: Create product if new
                if ($this->isNewProduct) {
                    $product = Product::create([
                        'category_id' => $this->newProductCategoryId,
                        'name' => $this->newProductName,
                        'sku' => $this->newProductSku,
                        'barcode' => $this->productBarcode,
                        'items_per_box' => $this->newProductItemsPerBox,
                        'purchase_price' => 0,
                        'selling_price' => round($this->newProductSellingPrice * 100),
                        'low_stock_threshold' => 10,
                        'reorder_point' => 20,
                        'unit_of_measure' => 'piece',
                        'is_active' => true,
                    ]);

                    $this->productId = $product->id;
                    $this->productName = $product->name;
                }

                // Step 2: Save barcode association if "remember" is checked and barcode is unknown
                if ($this->rememberBarcode && !$this->barcodeIsKnown && !$this->isNewProduct) {
                    // Check if association doesn't already exist
                    $exists = ProductBarcode::where('barcode', $this->productBarcode)
                        ->where('product_id', $this->productId)
                        ->exists();

                    if (!$exists) {
                        ProductBarcode::create([
                            'product_id' => $this->productId,
                            'barcode' => $this->productBarcode,
                            'supplier_name' => $this->supplierName,
                            'notes' => 'Added during receiving',
                            'is_active' => true,
                        ]);
                    }
                }

                // Step 3: Create boxes
                $boxes = $boxService->createBoxes(
                    $this->productId,
                    $this->warehouseId,
                    $this->numberOfBoxes,
                    [
                        'batch_number' => $this->batchNumber,
                        'expiry_date' => $this->expiryDate,
                        'supplier_barcode' => $this->productBarcode,
                    ]
                );

                // Track results
                $this->recentBoxes = array_merge($boxes, array_slice($this->recentBoxes, 0, 20));
                $this->sessionTotal += count($boxes);
            });

            $message = "✓ ";
            if ($this->isNewProduct) {
                $message .= "Product created and ";
            } elseif ($this->rememberBarcode && !$this->barcodeIsKnown) {
                $message .= "Barcode saved and ";
            }
            $message .= "{$this->numberOfBoxes} boxes received successfully!";

            session()->flash('success', $message);

            // Close modal and reset
            $this->closeReceiveModal();

            // Clear barcode to scan next product
            $this->productBarcode = null;

            $this->dispatch('boxes-created', count: $this->numberOfBoxes);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed: ' . $e->getMessage());
        }
    }

    /**
     * Switch between manual and Excel mode
     */
    public function switchMode(string $mode)
    {
        $this->manualMode = ($mode === 'manual');
        $this->reset(['excelFile', 'excelPreview', 'excelErrors', 'showExcelPreview']);
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $headers = ['barcode', 'product_name', 'sku', 'category', 'items_per_box', 'selling_price', 'boxes', 'batch_number', 'expiry_date'];
        $sample = [
            ['1234567890123', 'Adidas Ultraboost Size 43', 'ADI-UB-43', 'Footwear', '12', '85000', '20', 'BATCH-2024-Q1', '2025-12-31'],
            ['9876543210987', 'Nike Air Max Size 42', 'NIKE-AM-42', 'Footwear', '24', '75000', '15', 'BATCH-2024-Q2', '2026-06-30'],
            ['5555555555555', 'Puma Speed Size 44', 'PUMA-SP-44', 'Footwear', '18', '65000', '10', 'BATCH-2024-Q3', '2026-03-15'],
        ];

        $filename = 'box-receiving-template.csv';
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, $headers);
        foreach ($sample as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response()->streamDownload(function() use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Process uploaded Excel/CSV file with complete product info
     */
    public function processExcelFile()
    {
        // CRITICAL: Check warehouse is selected first
        if (!$this->validateWarehouseSelected()) {
            return;
        }

        $this->validate(['excelFile' => 'required|mimes:xlsx,xls,csv|max:10240']);

        try {
            $data = Excel::toArray([], $this->excelFile->getRealPath());

            if (empty($data) || empty($data[0])) {
                throw new \Exception('Excel file is empty');
            }

            $rows = $data[0];
            array_shift($rows); // Remove header

            // Convert to associative array with ALL product columns
            $parsed = [];
            foreach ($rows as $row) {
                if (empty(array_filter($row))) continue; // Skip empty rows

                $parsed[] = [
                    'barcode' => trim($row[0] ?? ''),
                    'product_name' => trim($row[1] ?? ''),
                    'sku' => trim($row[2] ?? ''),
                    'category' => trim($row[3] ?? ''),
                    'items_per_box' => $row[4] ?? '',
                    'selling_price' => $row[5] ?? '',
                    'boxes' => $row[6] ?? '',
                    'batch_number' => trim($row[7] ?? ''),
                    'expiry_date' => $row[8] ?? '',
                ];
            }

            // Process with product matcher
            $matcher = new \App\Services\Inventory\ExcelProductMatcher();
            $result = $matcher->processRows($parsed);

            $this->excelRecognized = $result['recognized'];
            $this->excelUnknown = $result['unrecognized'];
            $this->excelBarcodeAssociations = $result['barcode_associations'];
            $this->excelErrors = $result['errors'];
            $this->showExcelPreview = true;

            // Initialize editable fields for ALL rows (recognized + unrecognized)
            $allRows = array_merge($this->excelRecognized, $this->excelUnknown);

            foreach ($allRows as $item) {
                $rowNum = $item['row_number'];

                // For RECOGNIZED products, use database values but allow editing
                if ($item['status'] === 'recognized') {
                    $product = Product::find($item['product_id']);

                    $this->editableProductNames[$rowNum] = $product->name;
                    $this->editableProductBarcodes[$rowNum] = $item['barcode'] ?? $product->barcode;
                    $this->editableProductSkus[$rowNum] = $product->sku;
                    $this->editableProductCategories[$rowNum] = $product->category_id;
                    $this->editableProductItemsPerBox[$rowNum] = $product->items_per_box;
                    $this->editableProductPrices[$rowNum] = $product->selling_price / 100;

                    // Track if Excel has different values
                    $this->excelHasDifferentValues[$rowNum] = [
                        'name' => $item['product_name'] !== $product->name,
                        'sku' => ($item['sku'] ?? '') !== $product->sku,
                        'items_per_box' => ($item['items_per_box'] ?? 0) != $product->items_per_box,
                        'price' => ($item['selling_price'] ?? 0) != ($product->selling_price / 100),
                    ];

                } else {
                    // For UNRECOGNIZED products, use Excel values
                    $this->editableProductNames[$rowNum] = $item['product_name'];
                    $this->editableProductBarcodes[$rowNum] = $item['barcode'];
                    $this->editableProductSkus[$rowNum] = $item['sku'] ?: $this->generateSkuFromName($item['product_name']);
                    $this->editableProductCategories[$rowNum] = $item['category_id'];
                    $this->editableProductItemsPerBox[$rowNum] = $item['items_per_box'];
                    $this->editableProductPrices[$rowNum] = $item['selling_price'];
                }

                $this->editableProductSearchQuery[$rowNum] = '';
                $this->liveSearchResults[$rowNum] = [];
            }

            $totalRows = count($allRows);
            $message = "✓ Parsed {$totalRows} rows from Excel";

            if (count($result['barcode_associations']) > 0) {
                $message .= " • " . count($result['barcode_associations']) . " new barcodes to associate";
            }

            session()->flash('success', $message);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to parse Excel: ' . $e->getMessage());
            $this->excelFile = null;
            $this->showExcelPreview = false;
        }
    }

    /**
     * Generate SKU from product name
     */
    private function generateSkuFromName(string $name): string
    {
        // Take first 3 words, uppercase, remove special chars
        $words = explode(' ', $name);
        $sku = strtoupper(substr(implode('', array_slice($words, 0, 3)), 0, 10));
        $sku = preg_replace('/[^A-Z0-9]/', '', $sku);
        $sku .= '-' . strtoupper(substr(uniqid(), -4));
        return $sku;
    }

    /**
     * Live search products as user types
     */
    public function updatedEditableProductSearchQuery($value, $rowNumber)
    {
        if (strlen($value) < 2) {
            $this->liveSearchResults[$rowNumber] = [];
            return;
        }

        $matcher = new \App\Services\Inventory\ExcelProductMatcher();
        $this->liveSearchResults[$rowNumber] = $matcher->liveSearchProducts($value)->toArray();
    }

    /**
     * Select product from live search
     */
    public function selectProductFromSearch(int $rowNumber, int $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        // Mark this row as matched
        foreach ($this->excelUnknown as $index => $item) {
            if ($item['row_number'] === $rowNumber) {
                $this->excelUnknown[$index]['product_id'] = $product->id;
                $this->excelUnknown[$index]['matched_product_name'] = $product->name;
                $this->excelUnknown[$index]['product_sku'] = $product->sku;
                $this->excelUnknown[$index]['matched_items_per_box'] = $product->items_per_box;
                $this->excelUnknown[$index]['status'] = 'matched';

                // Associate barcode if provided
                if (!empty($item['barcode'])) {
                    $this->excelBarcodeAssociations[] = [
                        'product_id' => $product->id,
                        'barcode' => $item['barcode'],
                        'product_name' => $product->name,
                    ];
                }

                break;
            }
        }

        // Clear search
        $this->editableProductSearchQuery[$rowNumber] = '';
        $this->liveSearchResults[$rowNumber] = [];

        session()->flash('success', "✓ Matched to: {$product->name}");
    }

    /**
     * Check if import button should be enabled
     */
    public function getCanImportProperty(): bool
    {
        if (empty($this->warehouseId)) {
            return false;
        }

        if (!$this->showExcelPreview) {
            return false;
        }

        // Check if all unrecognized products have required info
        foreach ($this->excelUnknown as $item) {
            $rowNum = $item['row_number'];

            // If not matched and missing category
            if ($item['status'] !== 'matched' && empty($this->editableProductCategories[$rowNum])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Search products for unknown barcode in Excel mode
     */
    public function searchProductForBarcode(string $barcode)
    {
        $query = $this->productSearches[$barcode] ?? '';

        if (strlen($query) < 2) {
            $this->productSearchResults[$barcode] = [];
            return;
        }

        $this->productSearchResults[$barcode] = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Select product for unknown barcode
     */
    public function selectProductForBarcode(string $barcode, int $productId)
    {
        $this->unknownBarcodeSelections[$barcode] = $productId;
    }

    /**
     * Confirm Excel import with smart barcode + name matching
     */
    public function confirmExcelImport()
    {
        // CRITICAL: Check warehouse is selected first
        if (!$this->validateWarehouseSelected()) {
            return;
        }

        // Validate unrecognized products that are NOT matched via search
        foreach ($this->excelUnknown as $item) {
            $rowNum = $item['row_number'];

            // Skip validation for matched products
            if ($item['status'] === 'matched') {
                continue;
            }

            if (empty($this->editableProductCategories[$rowNum])) {
                $categoryName = $item['category_name'] ?? 'category';
                session()->flash('error', "Row {$rowNum}: Category '{$categoryName}' not found. Please create it first or select a different category.");
                return;
            }

            if (empty($this->editableProductItemsPerBox[$rowNum]) || $this->editableProductItemsPerBox[$rowNum] < 1) {
                session()->flash('error', "Row {$rowNum}: Items per box must be at least 1");
                return;
            }

            if (empty($this->editableProductNames[$rowNum])) {
                session()->flash('error', "Row {$rowNum}: Product name is required");
                return;
            }
        }

        try {
            $boxService = app(BoxService::class);
            $matcher = new \App\Services\Inventory\ExcelProductMatcher();
            $created = [];
            $productsCreated = 0;

            $productsUpdated = 0;

            \Illuminate\Support\Facades\DB::transaction(function () use ($boxService, $matcher, &$created, &$productsCreated, &$productsUpdated) {
                // 1. ASSOCIATE NEW BARCODES to existing products
                foreach ($this->excelBarcodeAssociations as $association) {
                    $matcher->associateBarcode($association['product_id'], $association['barcode']);
                }

                // 2. Import RECOGNIZED products (with optional updates)
                foreach ($this->excelRecognized as $item) {
                    $rowNum = $item['row_number'];

                    // Check if user wants to update product info
                    if ($this->shouldUpdateProduct[$rowNum] ?? false) {
                        $product = Product::find($item['product_id']);

                        $product->update([
                            'name' => $this->editableProductNames[$rowNum],
                            'sku' => $this->editableProductSkus[$rowNum],
                            'items_per_box' => $this->editableProductItemsPerBox[$rowNum],
                            'selling_price' => $this->editableProductPrices[$rowNum] * 100,
                            'category_id' => $this->editableProductCategories[$rowNum],
                        ]);

                        $productsUpdated++;
                    }

                    // Create boxes
                    $boxes = $boxService->createBoxes(
                        $item['product_id'],
                        $this->warehouseId,
                        $item['boxes'],
                        [
                            'batch_number' => $item['batch_number'],
                            'expiry_date' => $item['expiry_date'],
                            'supplier_barcode' => $item['barcode'] ?? null,
                        ]
                    );

                    $created = array_merge($created, $boxes);
                }

                // 3. Import MATCHED (via live search) or CREATE NEW PRODUCTS
                foreach ($this->excelUnknown as $item) {
                    $rowNum = $item['row_number'];

                    // If matched via live search
                    if ($item['status'] === 'matched') {
                        // Check if update requested
                        if ($this->shouldUpdateProduct[$rowNum] ?? false) {
                            $product = Product::find($item['product_id']);

                            $product->update([
                                'name' => $this->editableProductNames[$rowNum],
                                'sku' => $this->editableProductSkus[$rowNum],
                                'items_per_box' => $this->editableProductItemsPerBox[$rowNum],
                                'selling_price' => $this->editableProductPrices[$rowNum] * 100,
                                'category_id' => $this->editableProductCategories[$rowNum],
                            ]);

                            $productsUpdated++;
                        }

                        $boxes = $boxService->createBoxes(
                            $item['product_id'],
                            $this->warehouseId,
                            $item['boxes'],
                            [
                                'batch_number' => $item['batch_number'],
                                'expiry_date' => $item['expiry_date'],
                                'supplier_barcode' => $item['barcode'] ?? null,
                            ]
                        );

                        $created = array_merge($created, $boxes);
                        continue;
                    }

                    // Create new product (data from Excel)
                    $productName = $this->editableProductNames[$rowNum];
                    $sku = $this->editableProductSkus[$rowNum];
                    $barcode = $this->editableProductBarcodes[$rowNum];
                    $categoryId = $this->editableProductCategories[$rowNum];
                    $itemsPerBox = $this->editableProductItemsPerBox[$rowNum];
                    $price = $this->editableProductPrices[$rowNum];

                    $product = Product::create([
                        'category_id' => $categoryId,
                        'name' => $productName,
                        'sku' => $sku,
                        'barcode' => $barcode,
                        'items_per_box' => $itemsPerBox,
                        'purchase_price' => 0,
                        'selling_price' => round($price * 100),
                        'low_stock_threshold' => 10,
                        'reorder_point' => 20,
                        'unit_of_measure' => 'piece',
                        'is_active' => true,
                    ]);

                    $productsCreated++;

                    // Also add to product_barcodes table
                    if (!empty($barcode)) {
                        ProductBarcode::create([
                            'product_id' => $product->id,
                            'barcode' => $barcode,
                            'barcode_type' => 'supplier',
                            'notes' => 'Created from Excel import',
                            'is_active' => true,
                        ]);
                    }

                    // Create boxes
                    $boxes = $boxService->createBoxes(
                        $product->id,
                        $this->warehouseId,
                        $item['boxes'],
                        [
                            'batch_number' => $item['batch_number'],
                            'expiry_date' => $item['expiry_date'],
                            'supplier_barcode' => $barcode,
                        ]
                    );

                    $created = array_merge($created, $boxes);
                }
            });

            // Update UI
            $this->recentBoxes = array_merge($created, array_slice($this->recentBoxes, 0, 20));
            $this->sessionTotal += count($created);

            $barcodesAssociated = count($this->excelBarcodeAssociations);

            $message = "✓ Imported " . count($created) . " boxes";
            if ($productsCreated > 0) {
                $message .= ", created {$productsCreated} new product" . ($productsCreated > 1 ? 's' : '');
            }
            if ($productsUpdated > 0) {
                $message .= ", updated {$productsUpdated} product" . ($productsUpdated > 1 ? 's' : '');
            }
            if ($barcodesAssociated > 0) {
                $message .= ", associated {$barcodesAssociated} new barcode" . ($barcodesAssociated > 1 ? 's' : '');
            }

            session()->flash('success', $message . "!");

            // Reset
            $this->reset([
                'excelFile',
                'excelPreview',
                'excelErrors',
                'showExcelPreview',
                'excelRecognized',
                'excelUnknown',
                'excelBarcodeAssociations',
                'unknownBarcodeSelections',
                'unknownBarcodeRemember',
                'productSearches',
                'productSearchResults',
                'editableProductNames',
                'editableProductSearchQuery',
                'liveSearchResults',
                'selectedProducts',
                'editableProductSkus',
                'editableProductBarcodes',
                'editableProductCategories',
                'editableProductItemsPerBox',
                'editableProductPrices',
                'productSuggestions',
                'excelHasDifferentValues',
                'shouldUpdateProduct'
            ]);
            $this->manualMode = true;

        } catch (\Exception $e) {
            session()->flash('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function cancelExcelImport()
    {
        $this->reset([
            'excelFile',
            'excelPreview',
            'excelErrors',
            'showExcelPreview',
            'excelRecognized',
            'excelUnknown',
            'excelBarcodeAssociations',
            'unknownBarcodeSelections',
            'unknownBarcodeRemember',
            'productSearches',
            'productSearchResults',
            'editableProductNames',
            'editableProductSearchQuery',
            'liveSearchResults',
            'selectedProducts',
            'editableProductSkus',
            'editableProductBarcodes',
            'editableProductCategories',
            'editableProductItemsPerBox',
            'editableProductPrices',
            'productSuggestions',
            'excelHasDifferentValues',
            'shouldUpdateProduct'
        ]);
    }

    public function startNew()
    {
        $this->reset();
        $this->mount();
    }

    public function render()
    {
        return view('livewire.warehouse.inventory.receive-boxes', [
            'warehouses' => auth()->user()->isOwner()
                ? Warehouse::orderBy('name')->get()
                : Warehouse::where('id', $this->warehouseId)->get(),
            'product' => $this->productId ? Product::find($this->productId) : null,
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }
}
