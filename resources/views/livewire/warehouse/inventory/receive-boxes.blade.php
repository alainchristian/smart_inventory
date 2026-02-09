<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Receive Boxes</h1>
        <p class="mt-1 text-sm text-gray-600">Scan product barcode and enter number of boxes</p>
    </div>

    <!-- Mode Toggle -->
    <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center space-x-4">
            <button wire:click="switchMode('manual')"
                    class="px-6 py-2 rounded-lg font-semibold {{ $manualMode ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                📦 Manual Entry
            </button>
            <button wire:click="switchMode('excel')"
                    class="px-6 py-2 rounded-lg font-semibold {{ !$manualMode ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                📊 Excel Upload
            </button>
            @if(!$manualMode)
                <button wire:click="downloadTemplate"
                        class="ml-auto px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">
                    ⬇️ Download Template
                </button>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm text-red-800">{{ session('error') }}</p>
        </div>
    @endif
    @if (session()->has('warning'))
        <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-sm text-yellow-800">{{ session('warning') }}</p>
        </div>
    @endif

    @if($manualMode)
        {{-- MANUAL MODE - Simple barcode input --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="space-y-6">
                <!-- Warehouse -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Warehouse</label>
                    <select wire:model.live="warehouseId"
                            class="block w-full rounded-lg border-gray-300 shadow-sm"
                            {{ auth()->user()->isWarehouseManager() ? 'disabled' : '' }}>
                        <option value="">Select warehouse...</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouseId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Product Barcode - Opens Modal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Product Barcode <span class="text-xs text-gray-500">(scan from physical box)</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text"
                               wire:model.live="productBarcode"
                               placeholder="Scan or type barcode from box"
                               class="flex-1 rounded-lg border-gray-300 font-mono text-lg {{ !$warehouseId ? 'opacity-50 cursor-not-allowed' : '' }}"
                               {{ !$warehouseId ? 'disabled' : '' }}
                               autofocus>
                        @if($productBarcode)
                            <button wire:click="clearProduct"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                Clear
                            </button>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        @if(!$warehouseId)
                            <span class="text-red-600 font-semibold">⚠️ Please select a warehouse first</span>
                        @else
                            System will open receive form when you scan/enter barcode
                        @endif
                    </p>
                    @error('productBarcode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('warehouseId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Divider with OR -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">OR</span>
                    </div>
                </div>

                <!-- Browse Products Button -->
                <div>
                    <button wire:click="openProductDropdown"
                            type="button"
                            {{ !$warehouseId ? 'disabled' : '' }}
                            class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center justify-center gap-2
                                   {{ !$warehouseId ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Browse & Select Product
                    </button>
                    <p class="mt-2 text-xs text-gray-500 text-center">
                        @if(!$warehouseId)
                            <span class="text-red-600 font-semibold">⚠️ Please select a warehouse first</span>
                        @else
                            Select from existing products or create a new one
                        @endif
                    </p>
                </div>
            </div>
        </div>

    @else
        {{-- EXCEL MODE --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            @if(!$showExcelPreview)
                <div class="space-y-4">
                    <!-- Warehouse -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Warehouse</label>
                        <select wire:model.live="warehouseId"
                                class="block w-full rounded-lg border-gray-300"
                                {{ auth()->user()->isWarehouseManager() ? 'disabled' : '' }}>
                            <option value="">Select warehouse...</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Instructions -->
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <h3 class="font-semibold text-blue-900 mb-2">📋 Smart Import Instructions</h3>
                        <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                            <li>Download the CSV/Excel template below</li>
                            <li>Fill in complete product info: <strong>barcode, product_name, sku, category, items_per_box, selling_price, boxes</strong>, batch_number (optional), expiry_date (optional)</li>
                            <li>Upload the file - system will auto-match existing products</li>
                            <li><strong>Smart matching:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-0.5">
                                    <li>Known barcodes → Instant match ✓</li>
                                    <li>Known product names → Auto-associate new barcode</li>
                                    <li>New products → Pre-filled from Excel, just verify or search existing</li>
                                </ul>
                            </li>
                        </ol>
                    </div>

                    <!-- File Upload -->
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                        <label class="cursor-pointer">
                            <span class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-block">
                                Choose CSV/Excel File
                            </span>
                            <input type="file"
                                   wire:model="excelFile"
                                   accept=".xlsx,.xls,.csv"
                                   class="hidden">
                        </label>
                        @if($excelFile)
                            <p class="mt-2 text-sm text-gray-600">Selected: {{ $excelFile->getClientOriginalName() }}</p>
                        @endif
                        @error('excelFile') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if($excelFile && !$showExcelPreview)
                        <div class="flex justify-end">
                            <button wire:click="processExcelFile"
                                    {{ !$warehouseId ? 'disabled' : '' }}
                                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold
                                           {{ !$warehouseId ? 'opacity-50 cursor-not-allowed' : '' }}">
                                📊 Preview Import
                            </button>
                        </div>
                        @if(!$warehouseId)
                            <p class="text-sm text-red-600 text-center mt-2">⚠️ Please select a warehouse first</p>
                        @endif
                    @endif
                </div>
            @else
                <!-- Preview with Barcode Recognition -->
                <div class="space-y-6">

                    <!-- RECOGNIZED SECTION -->
                    @if(!empty($excelRecognized))
                        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <h3 class="font-semibold text-green-900">
                                    ✓ Products Found ({{ count($excelRecognized) }} rows - {{ array_sum(array_column($excelRecognized, 'boxes')) }} boxes)
                                </h3>
                            </div>

                            <div class="space-y-4">
                                @foreach($excelRecognized as $item)
                                    @php $rowNum = $item['row_number']; @endphp

                                    <div class="bg-white rounded-lg p-4 border-2 border-green-300">

                                        <!-- Row Header -->
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-1 bg-green-200 text-green-800 text-xs font-semibold rounded">
                                                        Row {{ $rowNum }}
                                                    </span>
                                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">
                                                        ✓ Matched by {{ $item['match_method'] }}
                                                    </span>
                                                    @if($item['new_barcode'] ?? false)
                                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded">
                                                            🔵 New Barcode
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    {{ $item['boxes'] }} boxes
                                                    @if($item['batch_number']) | Batch: {{ $item['batch_number'] }} @endif
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Change Detection Warning -->
                                        @if(!empty($excelHasDifferentValues[$rowNum]) && in_array(true, $excelHasDifferentValues[$rowNum]))
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
                                                <div class="flex items-start gap-2 mb-2">
                                                    <svg class="w-4 h-4 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-yellow-800">Excel has different values:</p>
                                                        <ul class="text-xs text-yellow-700 mt-1 space-y-0.5">
                                                            @if($excelHasDifferentValues[$rowNum]['name'] ?? false)
                                                                <li>• Product name differs</li>
                                                            @endif
                                                            @if($excelHasDifferentValues[$rowNum]['sku'] ?? false)
                                                                <li>• SKU differs</li>
                                                            @endif
                                                            @if($excelHasDifferentValues[$rowNum]['items_per_box'] ?? false)
                                                                <li>• Items per box differs</li>
                                                            @endif
                                                            @if($excelHasDifferentValues[$rowNum]['price'] ?? false)
                                                                <li>• Price differs</li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>

                                                <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                                    <input type="checkbox"
                                                           wire:model="shouldUpdateProduct.{{ $rowNum }}"
                                                           class="rounded border-yellow-400 text-yellow-600 focus:ring-yellow-500">
                                                    <span class="text-sm font-medium text-yellow-900">
                                                        Update product with values from Excel
                                                    </span>
                                                </label>
                                            </div>
                                        @endif

                                        <!-- Editable Fields -->
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                            <p class="text-xs font-medium text-gray-700 mb-3">
                                                Product Information (editable):
                                            </p>

                                            <div class="space-y-3">
                                                <!-- Product Name -->
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                                        Product Name
                                                    </label>
                                                    <input type="text"
                                                           wire:model="editableProductNames.{{ $rowNum }}"
                                                           class="block w-full rounded-lg border-gray-300 text-sm">
                                                </div>

                                                <!-- Grid: Barcode + SKU -->
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                                            Barcode
                                                        </label>
                                                        <input type="text"
                                                               wire:model="editableProductBarcodes.{{ $rowNum }}"
                                                               class="block w-full rounded-lg border-gray-300 text-sm font-mono">
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                                            SKU
                                                        </label>
                                                        <input type="text"
                                                               wire:model="editableProductSkus.{{ $rowNum }}"
                                                               class="block w-full rounded-lg border-gray-300 text-sm font-mono">
                                                    </div>
                                                </div>

                                                <!-- Grid: Category + Items/Box + Price -->
                                                <div class="grid grid-cols-3 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                                            Category
                                                        </label>
                                                        <select wire:model="editableProductCategories.{{ $rowNum }}"
                                                                class="block w-full rounded-lg border-gray-300 text-sm">
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                                            Items/Box
                                                        </label>
                                                        <input type="number"
                                                               wire:model="editableProductItemsPerBox.{{ $rowNum }}"
                                                               min="1"
                                                               class="block w-full rounded-lg border-gray-300 text-sm">
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                                            Price (RWF)
                                                        </label>
                                                        <input type="number"
                                                               wire:model="editableProductPrices.{{ $rowNum }}"
                                                               min="0"
                                                               step="0.01"
                                                               class="block w-full rounded-lg border-gray-300 text-sm">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- NEW PRODUCTS SECTION -->
                    @if(!empty($excelUnknown))
                        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <h3 class="font-semibold text-yellow-900">
                                    ⚠️ Verify Product Names ({{ count($excelUnknown) }} rows)
                                </h3>
                            </div>

                            <div class="space-y-4">
                                @foreach($excelUnknown as $item)
                                    @php $rowNum = $item['row_number']; @endphp

                                    <div class="bg-white rounded-lg p-4 border-2 {{ $item['status'] === 'matched' ? 'border-green-300' : 'border-yellow-300' }}">

                                        <!-- Row Header -->
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-1 bg-yellow-200 text-yellow-800 text-xs font-semibold rounded">
                                                        Row {{ $rowNum }}
                                                    </span>
                                                    @if($item['status'] === 'matched')
                                                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">
                                                            ✓ Matched
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded">
                                                            ⚠️ New Product
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    {{ $item['boxes'] }} boxes
                                                    @if($item['batch_number']) | Batch: {{ $item['batch_number'] }} @endif
                                                </p>
                                            </div>
                                        </div>

                                        @if($item['status'] === 'matched')
                                            <!-- MATCHED VIA SEARCH -->
                                            <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                                <p class="text-sm font-semibold text-green-900">{{ $item['matched_product_name'] }}</p>
                                                <p class="text-xs text-green-700 mt-1">
                                                    SKU: {{ $item['product_sku'] }} | {{ $item['matched_items_per_box'] }} items/box
                                                </p>
                                            </div>
                                        @else
                                            <!-- NEW PRODUCT - PRE-FILLED FROM EXCEL -->
                                            <div class="space-y-3">

                                                <!-- Live Search Product Name -->
                                                <div class="relative">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Product Name
                                                        <span class="text-xs text-gray-500 font-normal">(Search existing or keep as is)</span>
                                                    </label>

                                                    <!-- Display current name -->
                                                    <div class="mb-2 p-2 bg-blue-50 border border-blue-200 rounded">
                                                        <p class="text-sm font-medium text-blue-900">
                                                            From Excel: "{{ $editableProductNames[$rowNum] }}"
                                                        </p>
                                                    </div>

                                                    <!-- Live search input -->
                                                    <input type="text"
                                                           wire:model.live.debounce.300ms="editableProductSearchQuery.{{ $rowNum }}"
                                                           placeholder="Type to search existing products..."
                                                           class="block w-full rounded-lg border-gray-300 text-sm"
                                                           autocomplete="off">

                                                    <!-- Live search results dropdown -->
                                                    @if(!empty($liveSearchResults[$rowNum]))
                                                        <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                                            @foreach($liveSearchResults[$rowNum] as $result)
                                                                <button type="button"
                                                                        wire:click="selectProductFromSearch({{ $rowNum }}, {{ $result['id'] }})"
                                                                        class="w-full text-left px-4 py-3 hover:bg-blue-50 border-b border-gray-100 last:border-0">
                                                                    <p class="font-medium text-gray-900">{{ $result['name'] }}</p>
                                                                    <p class="text-xs text-gray-600 mt-1">
                                                                        SKU: {{ $result['sku'] }} |
                                                                        {{ $result['items_per_box'] }} items/box |
                                                                        {{ number_format($result['selling_price'] / 100) }} RWF
                                                                    </p>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    <p class="text-xs text-gray-500 mt-1">
                                                        💡 Type to search, or leave as is to create new product
                                                    </p>
                                                </div>

                                                <!-- Pre-filled fields from Excel -->
                                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                                    <p class="text-xs font-medium text-gray-700 mb-2">Pre-filled from Excel:</p>

                                                    <div class="grid grid-cols-2 gap-3">
                                                        <!-- Barcode (read-only) -->
                                                        <div>
                                                            <label class="block text-xs text-gray-600 mb-1">Barcode</label>
                                                            <input type="text"
                                                                   value="{{ $editableProductBarcodes[$rowNum] }}"
                                                                   disabled
                                                                   class="block w-full rounded border-gray-200 bg-gray-100 text-xs font-mono">
                                                        </div>

                                                        <!-- SKU (editable) -->
                                                        <div>
                                                            <label class="block text-xs text-gray-600 mb-1">SKU</label>
                                                            <input type="text"
                                                                   wire:model="editableProductSkus.{{ $rowNum }}"
                                                                   class="block w-full rounded border-gray-300 text-xs font-mono">
                                                        </div>

                                                        <!-- Category (from Excel, may need selection if not found) -->
                                                        <div>
                                                            <label class="block text-xs text-gray-600 mb-1">
                                                                Category <span class="text-red-500">*</span>
                                                            </label>
                                                            @if($editableProductCategories[$rowNum])
                                                                <input type="text"
                                                                       value="{{ $item['category_name'] }}"
                                                                       disabled
                                                                       class="block w-full rounded border-gray-200 bg-green-100 text-xs">
                                                            @else
                                                                <select wire:model="editableProductCategories.{{ $rowNum }}"
                                                                        class="block w-full rounded border-red-300 bg-red-50 text-xs">
                                                                    <option value="">⚠️ "{{ $item['category_name'] }}" not found - Select:</option>
                                                                    @foreach($categories as $category)
                                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @endif
                                                        </div>

                                                        <!-- Items per Box -->
                                                        <div>
                                                            <label class="block text-xs text-gray-600 mb-1">Items/Box</label>
                                                            <input type="number"
                                                                   wire:model="editableProductItemsPerBox.{{ $rowNum }}"
                                                                   class="block w-full rounded border-gray-300 text-xs">
                                                        </div>

                                                        <!-- Price -->
                                                        <div class="col-span-2">
                                                            <label class="block text-xs text-gray-600 mb-1">Selling Price (RWF)</label>
                                                            <input type="number"
                                                                   wire:model="editableProductPrices.{{ $rowNum }}"
                                                                   class="block w-full rounded border-gray-300 text-xs">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- ERRORS SECTION -->
                    @if(!empty($excelErrors))
                        <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <h3 class="font-semibold text-red-900">
                                    ❌ Errors ({{ count($excelErrors) }} rows)
                                </h3>
                            </div>
                            <div class="space-y-1">
                                @foreach($excelErrors as $error)
                                    <p class="text-sm text-red-700">Row {{ $error['row'] }}: {{ $error['error'] }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- SUMMARY & ACTIONS -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h3 class="font-semibold text-gray-900 mb-3">Import Summary</h3>

                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <div class="text-center">
                                <p class="text-xs text-gray-600 uppercase mb-1">Ready</p>
                                <p class="text-2xl font-bold text-green-600">
                                    {{ array_sum(array_column($excelRecognized, 'boxes')) }}
                                </p>
                                <p class="text-xs text-gray-500">boxes</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-600 uppercase mb-1">New Products</p>
                                <p class="text-2xl font-bold text-yellow-600">
                                    {{ count(array_filter($excelUnknown, fn($i) => $i['status'] !== 'matched')) }}
                                </p>
                                <p class="text-xs text-gray-500">to create</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-600 uppercase mb-1">Errors</p>
                                <p class="text-2xl font-bold text-red-600">
                                    {{ count($excelErrors) }}
                                </p>
                                <p class="text-xs text-gray-500">rows</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-600 uppercase mb-1">Total</p>
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ array_sum(array_column($excelRecognized, 'boxes')) + array_sum(array_column($excelUnknown, 'boxes')) }}
                                </p>
                                <p class="text-xs text-gray-500">boxes</p>
                            </div>
                        </div>

                        <!-- Show warnings if any -->
                        @if(!$this->canImport && !empty($warehouseId))
                            <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-4">
                                <p class="text-sm text-yellow-800">
                                    ⚠️ Please select categories for all new products before importing
                                </p>
                            </div>
                        @endif

                        <div class="flex justify-end gap-3">
                            <button wire:click="cancelExcelImport"
                                    type="button"
                                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button wire:click="confirmExcelImport"
                                    type="button"
                                    @if(!$this->canImport) disabled @endif
                                    class="px-6 py-2 text-white rounded-lg font-semibold shadow-lg
                                           {{ $this->canImport ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }}">
                                Import All ({{ array_sum(array_column($excelRecognized, 'boxes')) + array_sum(array_column($excelUnknown, 'boxes')) }} boxes)
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Recent Boxes -->
    @if(!empty($recentBoxes))
        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Recently Created ({{ count($recentBoxes) }})</h2>
                <button wire:click="startNew"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm">
                    🔄 Start New
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Box Code</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier Barcode</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentBoxes as $box)
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono text-blue-600">{{ $box->box_code }}</td>
                                <td class="px-4 py-3 text-sm">{{ $box->product->name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $box->items_remaining }} items</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $box->supplier_barcode ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $box->batch_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $box->expiry_date ? $box->expiry_date->format('Y-m-d') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($sessionTotal > 0)
        <div class="mt-6 bg-blue-50 rounded-lg p-4">
            <p class="text-sm font-semibold text-blue-900">📊 Session Total: {{ $sessionTotal }} boxes created</p>
        </div>
    @endif

    <!-- Unified Receive Modal -->
    @if($showReceiveModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                 wire:click="closeReceiveModal"></div>

            <!-- Modal Container -->
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col"
                     @click.stop>

                    <!-- Fixed Header -->
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">
                                @if($isNewProduct)
                                    ➕ Create New Product
                                @elseif($barcodeIsKnown)
                                    ✓ Receive Boxes
                                @elseif($showProductSearch)
                                    🔍 Link Barcode to Product
                                @elseif($showProductDropdown)
                                    📦 Select Product
                                @else
                                    Receive Boxes
                                @endif
                            </h2>
                            @if($productBarcode && !$showProductDropdown && !$isNewProduct)
                                <p class="text-sm text-gray-600 mt-1">Barcode: <span class="font-mono text-blue-600">{{ $productBarcode }}</span></p>
                            @endif
                        </div>
                        <button wire:click="closeReceiveModal"
                                type="button"
                                class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="flex-1 overflow-y-auto px-6 py-4">
                        <div class="space-y-4">
                            <!-- Warehouse Warning -->
                            @if(!$warehouseId)
                                <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-red-800">Warehouse Not Selected</p>
                                            <p class="text-xs text-red-700 mt-1">Please close this dialog and select a warehouse on the main screen first.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($barcodeIsKnown && !$showProductSearch)
                                <!-- SCENARIO 1: Known Barcode (Fast Path) -->
                                <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <h3 class="font-semibold text-green-900">Product Recognized!</h3>
                                    </div>
                                    @if($product)
                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                            <div><span class="text-gray-600">Name:</span> <span class="font-semibold ml-2">{{ $product->name }}</span></div>
                                            <div><span class="text-gray-600">SKU:</span> <span class="font-mono ml-2">{{ $product->sku }}</span></div>
                                            <div><span class="text-gray-600">Items/Box:</span> <span class="font-semibold ml-2">{{ $product->items_per_box }}</span></div>
                                            <div><span class="text-gray-600">Price:</span> <span class="ml-2">{{ number_format($product->selling_price / 100, 0) }} RWF</span></div>
                                        </div>
                                    @endif
                                </div>

                            @elseif($showProductSearch)
                                <!-- SCENARIO 2: Unknown Barcode (Learning Path) -->
                                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <h3 class="font-semibold text-yellow-900">Barcode Not Recognized</h3>
                                    </div>
                                    <p class="text-sm text-yellow-800">
                                        Barcode <strong class="font-mono">{{ $productBarcode }}</strong> is not in the system.
                                        Search for the product below.
                                    </p>
                                </div>

                                <!-- Product Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Product</label>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="productSearch"
                                           placeholder="Type product name or SKU..."
                                           class="block w-full rounded-lg border-gray-300 text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Start typing to see matching products</p>
                                </div>

                                <!-- Search Results -->
                                @if(!empty($searchResults))
                                    <div class="space-y-2 max-h-60 overflow-y-auto">
                                        <p class="text-xs font-medium text-gray-600 uppercase">Select a product:</p>
                                        @foreach($searchResults as $result)
                                            <div wire:click="selectProduct({{ $result['id'] }})"
                                                 class="p-3 border rounded-lg cursor-pointer transition-all
                                                        {{ $selectedProductId == $result['id'] ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-200' : 'border-gray-300 hover:bg-gray-50' }}">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <p class="font-medium text-gray-900">{{ $result['name'] }}</p>
                                                        <p class="text-xs text-gray-600 mt-1">
                                                            SKU: <span class="font-mono">{{ $result['sku'] }}</span> |
                                                            {{ $result['items_per_box'] }} items/box
                                                        </p>
                                                    </div>
                                                    @if($selectedProductId == $result['id'])
                                                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(strlen($productSearch) >= 2)
                                    <div class="text-center py-8 text-gray-500 text-sm">
                                        No products found. Try a different search term.
                                    </div>
                                @endif

                                <!-- Remember Barcode Checkbox -->
                                @if($selectedProductId)
                                    <div class="bg-blue-50 border border-blue-300 rounded-lg p-4">
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input type="checkbox"
                                                   wire:model="rememberBarcode"
                                                   checked
                                                   class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-900">Remember this association</p>
                                                <p class="text-xs text-gray-700 mt-1">
                                                    Save barcode <strong class="font-mono">{{ $productBarcode }}</strong> for
                                                    "<strong>{{ $selectedProductName }}</strong>" so it's recognized instantly next time.
                                                </p>
                                            </div>
                                        </label>

                                        @if($rememberBarcode)
                                            <div class="mt-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Supplier Name (Optional)</label>
                                                <input type="text"
                                                       wire:model="supplierName"
                                                       placeholder="e.g., Supplier A, Supplier B"
                                                       class="block w-full rounded border-gray-300 text-sm">
                                            </div>
                                        @endif
                                    </div>
                                @endif

                            @elseif($showProductDropdown)
                                <!-- SCENARIO 3: Product Dropdown (Browse & Select) -->
                                <div class="bg-indigo-50 border-2 border-indigo-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                                        </svg>
                                        <h3 class="font-semibold text-indigo-900">Select a Product</h3>
                                    </div>
                                    <p class="text-sm text-indigo-800">
                                        Browse available products or create a new one if it doesn't exist.
                                    </p>
                                </div>

                                <!-- Product Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Product</label>
                                    <input type="text"
                                           wire:model.live.debounce.300ms="productSearch"
                                           placeholder="Type product name or SKU..."
                                           class="block w-full rounded-lg border-gray-300 text-sm"
                                           autofocus>
                                    <p class="mt-1 text-xs text-gray-500">Start typing to see matching products</p>
                                </div>

                                <!-- Search Results -->
                                @if(!empty($searchResults))
                                    <div class="space-y-2 max-h-60 overflow-y-auto">
                                        <p class="text-xs font-medium text-gray-600 uppercase">Select a product:</p>
                                        @foreach($searchResults as $result)
                                            <div wire:click="selectProductFromDropdown({{ $result['id'] }})"
                                                 class="p-3 border rounded-lg cursor-pointer transition-all
                                                        {{ $selectedProductId == $result['id'] ? 'bg-indigo-50 border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-300 hover:bg-gray-50' }}">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <p class="font-medium text-gray-900">{{ $result['name'] }}</p>
                                                        <p class="text-xs text-gray-600 mt-1">
                                                            SKU: <span class="font-mono">{{ $result['sku'] }}</span> |
                                                            {{ $result['items_per_box'] }} items/box |
                                                            {{ number_format($result['selling_price'] / 100, 0) }} RWF
                                                        </p>
                                                    </div>
                                                    @if($selectedProductId == $result['id'])
                                                        <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(strlen($productSearch) >= 2)
                                    <div class="text-center py-6 text-gray-500 text-sm">
                                        <p class="mb-4">No products found matching "{{ $productSearch }}"</p>
                                        <button wire:click="createNewProduct"
                                                type="button"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Create New Product
                                        </button>
                                    </div>
                                @else
                                    <div class="text-center py-6">
                                        <button wire:click="createNewProduct"
                                                type="button"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Create New Product
                                        </button>
                                    </div>
                                @endif

                            @elseif($isNewProduct)
                                <!-- SCENARIO 4: Create New Product -->
                                <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <h3 class="font-semibold text-green-900">Create New Product</h3>
                                    </div>
                                    <p class="text-sm text-green-800">
                                        Fill in the product details below. This product will be created and boxes will be received in one step.
                                    </p>
                                </div>

                                <!-- New Product Form -->
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Product Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               wire:model="newProductName"
                                               placeholder="e.g., Coca Cola 500ml"
                                               class="block w-full rounded-lg border-gray-300">
                                        @error('newProductName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            SKU <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               wire:model="newProductSku"
                                               placeholder="e.g., COC-500ML"
                                               class="block w-full rounded-lg border-gray-300 font-mono">
                                        @error('newProductSku') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Barcode <span class="text-xs text-gray-500">(optional)</span>
                                        </label>
                                        <input type="text"
                                               wire:model="productBarcode"
                                               placeholder="Leave empty or enter barcode"
                                               class="block w-full rounded-lg border-gray-300 font-mono">
                                        @error('productBarcode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Category <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model="newProductCategoryId"
                                                class="block w-full rounded-lg border-gray-300">
                                            <option value="">Select category...</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('newProductCategoryId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Items per Box <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number"
                                                   wire:model="newProductItemsPerBox"
                                                   min="1"
                                                   placeholder="e.g., 24"
                                                   class="block w-full rounded-lg border-gray-300">
                                            @error('newProductItemsPerBox') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Selling Price (RWF) <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number"
                                                   wire:model="newProductSellingPrice"
                                                   min="0"
                                                   step="1"
                                                   placeholder="e.g., 500"
                                                   class="block w-full rounded-lg border-gray-300">
                                            @error('newProductSellingPrice') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if((!$showProductSearch || $selectedProductId) && !$showProductDropdown || $isNewProduct)
                                <!-- Divider -->
                                <div class="border-t border-gray-300 my-4"></div>

                                <!-- BOX ENTRY SECTION -->
                                <h3 class="font-semibold text-gray-900">Box Details</h3>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Number of Boxes <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number"
                                           wire:model="numberOfBoxes"
                                           min="1"
                                           max="100"
                                           class="block w-full rounded-lg border-gray-300">
                                    @if($product)
                                        <p class="mt-1 text-xs text-gray-500">≈ {{ $numberOfBoxes * $product->items_per_box }} items</p>
                                    @endif
                                    @error('numberOfBoxes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Batch Number <span class="text-xs text-gray-500">(optional)</span>
                                        </label>
                                        <input type="text"
                                               wire:model="batchNumber"
                                               placeholder="e.g., BATCH-2024-Q1"
                                               class="block w-full rounded-lg border-gray-300 text-sm">
                                        @error('batchNumber') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Expiry Date <span class="text-xs text-gray-500">(optional)</span>
                                        </label>
                                        <input type="date"
                                               wire:model="expiryDate"
                                               class="block w-full rounded-lg border-gray-300 text-sm">
                                        @error('expiryDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Fixed Footer -->
                    <div class="flex justify-end space-x-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <button wire:click="closeReceiveModal"
                                type="button"
                                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">
                            Cancel
                        </button>
                        @if((!$showProductSearch || $selectedProductId) && !$showProductDropdown)
                            <button wire:click="createBoxes"
                                    type="button"
                                    {{ !$warehouseId ? 'disabled' : '' }}
                                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold text-sm
                                           {{ !$warehouseId ? 'opacity-50 cursor-not-allowed' : '' }}">
                                @if($isNewProduct)
                                    Create Product & Receive {{ $numberOfBoxes }} Box{{ $numberOfBoxes > 1 ? 'es' : '' }}
                                @else
                                    Receive {{ $numberOfBoxes }} Box{{ $numberOfBoxes > 1 ? 'es' : '' }}
                                @endif
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
