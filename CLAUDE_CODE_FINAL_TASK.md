# CLAUDE CODE TASK: Build Box-Based Transfer Request System

## 🎯 What You're Building

A transfer request system where **shop managers request SEALED BOXES** (not individual items) from the warehouse.

**Key Insight:** This is a wholesale/distribution business where:
- 90% of sales = Sell entire sealed boxes to customers
- 5% of sales = Sell individual items from opened boxes (damage exceptions)
- Boxes stay SEALED throughout: Warehouse → Transfer → Shop → Customer

---

## ✅ Database Status: PERFECT - No Changes Needed!

Your current schema already supports this perfectly. You have:
- ✅ `boxes` table with status tracking
- ✅ `transfer_items` table for requests
- ✅ `transfer_boxes` table for actual box assignment
- ✅ All necessary enums and relationships

**Optional Enhancement:** Add `boxes_requested` field to `transfer_items` table for clearer UX.

---

## 📋 TASK 1: Build Transfer Request Form (Priority 1)

### File 1: Create Main View
**Location:** `resources/views/shop/transfers/request.blade.php`

```blade
<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Request Transfer</h1>
                        <p class="mt-1 text-sm text-gray-600">Request sealed boxes from warehouse</p>
                    </div>
                    <a href="{{ route('shop.transfers.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Transfers
                    </a>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm text-blue-700">
                            <strong>Box-Based System:</strong> You request sealed boxes. The warehouse will assign specific boxes during packing.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Livewire Component -->
            <livewire:inventory.transfers.request-transfer />
        </div>
    </div>
</x-app-layout>
```

### File 2: Update Livewire Component
**Location:** `app/Livewire/Inventory/Transfers/RequestTransfer.php`

**Key Changes Needed:**

1. **Change items array to use boxes:**
```php
public function addItem()
{
    $this->items[] = [
        'product_id' => null,
        'boxes_requested' => 1,  // ← Request BOXES not items
    ];
}
```

2. **Update validation:**
```php
protected $rules = [
    'fromWarehouseId' => 'required|exists:warehouses,id',
    'toShopId' => 'required|exists:shops,id',
    'items.*.product_id' => 'required|exists:products,id',
    'items.*.boxes_requested' => 'required|integer|min:1',
    'notes' => 'nullable|string|max:1000',
];

protected $messages = [
    'items.*.boxes_requested.required' => 'Please enter number of boxes.',
    'items.*.boxes_requested.min' => 'Must request at least 1 box.',
];
```

3. **Add box validation:**
```php
protected function validateWarehouseBoxes()
{
    if (!$this->fromWarehouseId) {
        return;
    }

    foreach ($this->items as $index => $item) {
        if (isset($item['product_id']) && isset($item['boxes_requested'])) {
            $product = Product::find($item['product_id']);
            
            if ($product) {
                $warehouseStock = $product->getCurrentStock('warehouse', $this->fromWarehouseId);
                $availableBoxes = $warehouseStock['full_boxes'] + $warehouseStock['partial_boxes'];
                
                if ($item['boxes_requested'] > $availableBoxes) {
                    $this->addError(
                        "items.{$index}.boxes_requested",
                        "Cannot request {$item['boxes_requested']} boxes. Only {$availableBoxes} boxes available."
                    );
                }
            }
        }
    }
}
```

4. **Update submit method:**
```php
public function submit()
{
    $this->validate();

    // Filter empty items
    $this->items = array_filter($this->items, function ($item) {
        return isset($item['product_id']) && isset($item['boxes_requested']) && $item['boxes_requested'] > 0;
    });

    if (empty($this->items)) {
        session()->flash('error', 'Please add at least one item to the transfer.');
        return;
    }

    // Check duplicates
    $productIds = array_column($this->items, 'product_id');
    if (count($productIds) !== count(array_unique($productIds))) {
        session()->flash('error', 'Cannot add the same product multiple times. Please combine box counts.');
        return;
    }

    // Validate stock
    $this->validateWarehouseBoxes();
    
    if ($this->getErrorBag()->isNotEmpty()) {
        session()->flash('error', 'Please fix the errors below before submitting.');
        return;
    }

    try {
        $transferService = app(TransferService::class);
        
        // Convert boxes to items
        $itemsWithQuantities = [];
        foreach ($this->items as $item) {
            $product = Product::find($item['product_id']);
            $itemsWithQuantities[] = [
                'product_id' => $item['product_id'],
                'quantity_requested' => $item['boxes_requested'] * $product->items_per_box,
            ];
        }

        $transfer = $transferService->createTransferRequest([
            'from_warehouse_id' => $this->fromWarehouseId,
            'to_shop_id' => $this->toShopId,
            'items' => $itemsWithQuantities,
            'notes' => $this->notes,
        ]);

        session()->flash('success', "Transfer request {$transfer->transfer_number} created successfully.");

        return redirect()->route('transfers.show', $transfer);
    } catch (\Exception $e) {
        session()->flash('error', 'Error creating transfer: ' . $e->getMessage());
    }
}
```

5. **Update render method:**
```php
public function render()
{
    $warehouses = Warehouse::active()->get();
    $shops = Shop::active()->get();
    $products = Product::active()->with('category')->orderBy('name')->get();

    // Get warehouse BOX stock
    $stockLevels = [];
    if ($this->fromWarehouseId) {
        foreach ($products as $product) {
            $stock = $product->getCurrentStock('warehouse', $this->fromWarehouseId);
            $stockLevels[$product->id] = [
                'full_boxes' => $stock['full_boxes'],      // Sealed boxes
                'partial_boxes' => $stock['partial_boxes'], // Opened boxes
                'total_boxes' => $stock['full_boxes'] + $stock['partial_boxes'],
                'total_items' => $stock['total_items'],
            ];
        }
    }

    return view('livewire.inventory.transfers.request-transfer', [
        'warehouses' => $warehouses,
        'shops' => $shops,
        'products' => $products,
        'stockLevels' => $stockLevels,
    ]);
}
```

### File 3: Create Livewire View
**Location:** `resources/views/livewire/inventory/transfers/request-transfer.blade.php`

**Key Sections:**

1. **Product Dropdown - Show Box Counts:**
```blade
<select wire:model.live="items.{{ $index }}.product_id" class="w-full rounded-md border-gray-300">
    <option value="">Select Product</option>
    @foreach($products as $product)
        @php
            $stock = $stockLevels[$product->id] ?? null;
            $totalBoxes = $stock ? $stock['total_boxes'] : 0;
            $sealedBoxes = $stock ? $stock['full_boxes'] : 0;
        @endphp
        <option value="{{ $product->id }}" @if($totalBoxes == 0) class="text-gray-400" @endif>
            {{ $product->name }}
            @if($product->category)
                ({{ $product->category->name }})
            @endif
            - {{ $sealedBoxes }} sealed boxes, {{ $totalBoxes }} total
            @if($totalBoxes == 0) - OUT OF STOCK @endif
        </option>
    @endforeach
</select>
```

2. **Boxes Input (not quantity):**
```blade
<div class="md:col-span-3">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Number of Boxes <span class="text-red-500">*</span>
    </label>
    <input type="number" 
           wire:model="items.{{ $index }}.boxes_requested" 
           min="1"
           class="w-full rounded-md border-gray-300"
           placeholder="0">
    @error('items.' . $index . '.boxes_requested')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    
    @if($item['product_id'] && $item['boxes_requested'])
        @php
            $product = $products->firstWhere('id', $item['product_id']);
            $estimatedItems = $item['boxes_requested'] * ($product->items_per_box ?? 0);
        @endphp
        <p class="mt-1 text-xs text-gray-500">
            ≈ {{ number_format($estimatedItems) }} items
        </p>
    @endif
</div>
```

3. **Stock Display - Emphasize Sealed Boxes:**
```blade
@if($item['product_id'] && isset($stockLevels[$item['product_id']]))
    @php
        $stock = $stockLevels[$item['product_id']];
        $requestedBoxes = $item['boxes_requested'] ?? 0;
        $availableBoxes = $stock['total_boxes'];
        $sealedBoxes = $stock['full_boxes'];
        $exceedsStock = $requestedBoxes > $availableBoxes;
    @endphp
    <div class="mt-2 p-3 bg-white rounded border @if($exceedsStock) border-red-300 @else border-blue-200 @endif">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-700">
                📦 Warehouse Stock:
            </p>
            @if($availableBoxes == 0)
                <span class="text-xs font-medium text-red-600">NO BOXES</span>
            @elseif($exceedsStock)
                <span class="text-xs font-medium text-red-600">⚠️ Not Enough</span>
            @endif
        </div>
        
        <div class="grid grid-cols-3 gap-2 text-xs">
            <div class="text-center p-2 bg-green-50 rounded">
                <div class="text-gray-600 text-xs mb-1">Sealed</div>
                <div class="text-lg font-bold text-green-700">{{ $sealedBoxes }}</div>
            </div>
            <div class="text-center p-2 bg-yellow-50 rounded">
                <div class="text-gray-600 text-xs mb-1">Opened</div>
                <div class="text-lg font-bold text-yellow-700">{{ $stock['partial_boxes'] }}</div>
            </div>
            <div class="text-center p-2 bg-blue-50 rounded">
                <div class="text-gray-600 text-xs mb-1">Total</div>
                <div class="text-lg font-bold @if($exceedsStock) text-red-600 @else text-blue-700 @endif">
                    {{ $availableBoxes }}
                </div>
            </div>
        </div>
        
        <div class="mt-2 pt-2 border-t border-gray-200 text-xs text-gray-600">
            Total Items: {{ number_format($stock['total_items']) }}
        </div>
        
        @if($exceedsStock && $availableBoxes > 0)
            <div class="mt-2 p-2 bg-red-50 rounded text-xs text-red-700">
                ⚠️ Requesting {{ $requestedBoxes }} boxes, only {{ $availableBoxes }} available
            </div>
        @endif
        
        <div class="mt-2 pt-2 border-t border-gray-200 text-xs text-gray-500 italic">
            Sealed boxes prioritized. Warehouse assigns specific boxes during packing.
        </div>
    </div>
@endif
```

---

## 📋 TASK 2: Optional Database Enhancement

**Only if you want explicit box tracking in transfer_items:**

Create migration: `database/migrations/YYYY_MM_DD_add_boxes_requested_to_transfer_items.php`

```php
Schema::table('transfer_items', function (Blueprint $table) {
    $table->integer('boxes_requested')->nullable()->after('quantity_requested')
        ->comment('Number of boxes requested');
});
```

Then update TransferService to save it:
```php
TransferItem::create([
    'transfer_id' => $transfer->id,
    'product_id' => $item['product_id'],
    'boxes_requested' => $item['boxes_requested'] ?? null, // NEW
    'quantity_requested' => $item['quantity_requested'],
]);
```

**This is optional - you can calculate boxes from quantity in UI if preferred.**

---

## ✅ Testing Steps

1. **Login as shop manager**
2. **Navigate to** `/shop/transfers/request`
3. **Select product**: Should show "X sealed boxes, Y total"
4. **Enter boxes**: Should show "≈ Z items"
5. **Stock display**: Should show sealed/opened/total breakdown
6. **Try exceeding stock**: Should show validation error
7. **Submit valid request**: Should create transfer successfully

---

## 🎯 Success Criteria

- [ ] Form asks for "Number of Boxes" (not "Quantity")
- [ ] Product dropdown shows sealed box count
- [ ] Stock display emphasizes sealed vs opened boxes
- [ ] Shows estimated items (e.g., "5 boxes ≈ 120 items")
- [ ] Validates against available boxes
- [ ] Prevents requesting more than available
- [ ] Creates transfer with correct quantity_requested (boxes × items_per_box)
- [ ] Clear messaging about box-based system
- [ ] No errors in logs

---

## 📁 Files to Create/Modify Summary

**Create:**
1. `resources/views/shop/transfers/request.blade.php` (main page)
2. `resources/views/livewire/inventory/transfers/request-transfer.blade.php` (component view)

**Modify:**
1. `app/Livewire/Inventory/Transfers/RequestTransfer.php` (change to box-based)

**Optional:**
1. Migration to add `boxes_requested` field

**Already Exists (No Changes):**
- `app/Services/Inventory/TransferService.php` (works as-is)
- Route already exists: `Route::get('/request', ...)->name('request');`

---

## 💡 Key Points to Remember

1. **Shop requests BOXES** (not items)
   - UI: "How many boxes?"
   - Storage: Convert to items (boxes × items_per_box)

2. **Emphasize SEALED boxes**
   - Show sealed count prominently
   - Opened boxes are exceptions

3. **Warehouse assigns later**
   - Shop requests quantities
   - Warehouse picks specific boxes during packing

4. **Database already perfect**
   - No schema changes required (optional enhancement available)
   - All tables and relationships correct

---

## 🚀 Start Here

**Recommendation:** Start by creating the three main files in order:

1. Main view (`request.blade.php`)
2. Component view (`request-transfer.blade.php`)
3. Update component logic (`RequestTransfer.php`)

Then test the workflow end-to-end!
