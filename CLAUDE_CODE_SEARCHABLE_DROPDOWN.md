# CLAUDE CODE: Custom Searchable Product Dropdown

## 🎯 Goal

Create a professional searchable dropdown that shows:
- ✅ Product icon
- ✅ Product name (first line)
- ✅ Stock info: Full boxes, Opened boxes, Total boxes (second line)
- ✅ When clicked → Adds to cart and becomes disabled/grayed out
- ✅ Real-time search filtering
- ✅ Professional styling

---

## 🎨 Visual Design

```
┌──────────────────────────────────────────────────────────────┐
│ Select Product to Add                                         │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ 🔍 Search products...                                    ▼│ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                               │
│ ┌─ Nike Air Max Size 42 ─────────────────────────────────┐  │
│ │ 📦 Nike Air Max Size 42                                  │  │
│ │    🟢 19 sealed  🟡 1 opened  📊 20 total               │  │
│ └──────────────────────────────────────────────────────────┘  │
│                                                               │
│ ┌─ Adidas Superstar Size 41 ──────────────────────────────┐  │
│ │ 📦 Adidas Superstar Size 41                              │  │
│ │    🟢 15 sealed  📊 15 total                            │  │
│ └──────────────────────────────────────────────────────────┘  │
│                                                               │
│ ┌─ Puma Suede Size 40 (IN CART - GRAYED OUT) ────────────┐  │
│ │ 📦 Puma Suede Size 40                    ✓ In Cart      │  │
│ │    🟢 12 sealed  📊 12 total                            │  │
│ └──────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

---

## 🔧 Implementation

### **Part 1: Livewire Component**
**File:** `app/Livewire/Inventory/Transfers/RequestTransfer.php`

**Add/Update these properties and methods:**

```php
public $search = '';  // Search input
public $showDropdown = false;  // Control dropdown visibility

/**
 * Add product to cart directly (no separate button needed)
 */
public function addProductToCart($productId)
{
    // Check if product already in cart
    foreach ($this->items as $item) {
        if ($item['product_id'] == $productId) {
            // Product already in cart, do nothing
            return;
        }
    }

    // Add to cart with quantity = 0
    $this->items[] = [
        'product_id' => $productId,
        'boxes_requested' => 0,
    ];

    // Clear search
    $this->search = '';
}

/**
 * Get filtered products based on search
 */
public function getFilteredProductsProperty()
{
    if (!$this->fromWarehouseId) {
        return collect([]);
    }

    $query = Product::query()
        ->active()
        ->with('category')
        ->orderBy('name');

    if (strlen($this->search) > 0) {
        $query->where(function($q) {
            $q->where('name', 'LIKE', '%' . $this->search . '%')
              ->orWhere('sku', 'LIKE', '%' . $this->search . '%')
              ->orWhereHas('category', function($cat) {
                  $cat->where('name', 'LIKE', '%' . $this->search . '%');
              });
        });
    }

    return $query->get();
}

/**
 * Check if product is already in cart
 */
public function isInCart($productId)
{
    return collect($this->items)->pluck('product_id')->contains($productId);
}
```

---

### **Part 2: Update View - Custom Searchable Dropdown**
**File:** `resources/views/livewire/inventory/transfers/request-transfer.blade.php`

**Replace the entire product selection section with:**

```blade
<!-- Custom Searchable Product Dropdown -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <!-- Search Header -->
    <div class="flex items-center gap-2 mb-4 text-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <h2 class="font-bold text-lg">Add Products to Transfer</h2>
    </div>

    @if(!$fromWarehouseId)
        <!-- No Warehouse Selected -->
        <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-200">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="mt-3 text-gray-400 text-sm">Select a warehouse on the left to view inventory.</p>
        </div>
    @else
        <!-- Search Input -->
        <div class="relative mb-4">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text"
                wire:model.live.debounce.300ms="search"
                class="block w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out shadow-sm"
                placeholder="🔍 Search products by name, SKU, or category..."
                wire:focus="$set('showDropdown', true)"
            />
            @if(strlen($search) > 0)
                <button 
                    type="button"
                    wire:click="$set('search', '')"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>

        <!-- Products List -->
        <div class="max-h-[400px] overflow-y-auto space-y-2 pr-1 custom-scrollbar">
            @forelse($this->filteredProducts as $product)
                @php
                    $stock = $stockLevels[$product->id] ?? ['total_boxes'=>0, 'full_boxes'=>0, 'partial_boxes'=>0, 'total_items'=>0];
                    $isOutOfStock = $stock['total_boxes'] == 0;
                    $inCart = $this->isInCart($product->id);
                @endphp
                
                <button 
                    type="button"
                    wire:click="addProductToCart({{ $product->id }})"
                    @if($isOutOfStock || $inCart) disabled @endif
                    class="w-full text-left group block p-4 rounded-xl border transition-all duration-150
                        @if($inCart)
                            bg-gray-50 border-gray-200 cursor-not-allowed opacity-60
                        @elseif($isOutOfStock)
                            bg-gray-50 border-gray-200 cursor-not-allowed opacity-50
                        @else
                            bg-white border-gray-200 hover:border-indigo-400 hover:bg-indigo-50/30 hover:shadow-md cursor-pointer
                        @endif
                    "
                >
                    <div class="flex items-start justify-between gap-3">
                        <!-- Left: Icon + Product Info -->
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <!-- Product Icon -->
                            <div class="flex-shrink-0 mt-0.5">
                                <div class="h-11 w-11 rounded-lg flex items-center justify-center
                                    @if($inCart)
                                        bg-green-100
                                    @elseif($isOutOfStock)
                                        bg-gray-200
                                    @else
                                        bg-indigo-100 group-hover:bg-indigo-200
                                    @endif
                                    transition-colors duration-150
                                ">
                                    @if($inCart)
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 @if($isOutOfStock) text-gray-400 @else text-indigo-600 group-hover:text-indigo-700 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <!-- Product Details -->
                            <div class="flex-1 min-w-0">
                                <!-- Product Name -->
                                <h4 class="text-sm font-semibold truncate
                                    @if($inCart)
                                        text-gray-500
                                    @elseif($isOutOfStock)
                                        text-gray-400
                                    @else
                                        text-gray-900 group-hover:text-indigo-900
                                    @endif
                                ">
                                    {{ $product->name }}
                                </h4>

                                <!-- Category -->
                                @if($product->category)
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $product->category->name }}
                                    </p>
                                @endif

                                <!-- Stock Info - Second Line -->
                                <div class="flex items-center gap-2 mt-2 flex-wrap">
                                    @if($isOutOfStock)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-700">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Out of Stock
                                        </span>
                                    @else
                                        <!-- Sealed Boxes -->
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $stock['full_boxes'] }} sealed
                                        </span>

                                        <!-- Opened Boxes (if any) -->
                                        @if($stock['partial_boxes'] > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-yellow-100 text-yellow-700">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                                                </svg>
                                                {{ $stock['partial_boxes'] }} opened
                                            </span>
                                        @endif

                                        <!-- Total Boxes -->
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/>
                                                <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/>
                                                <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/>
                                            </svg>
                                            {{ $stock['total_boxes'] }} total
                                        </span>

                                        <!-- Total Items -->
                                        <span class="text-xs text-gray-500">
                                            ({{ number_format($stock['total_items']) }} items)
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: Status Badge -->
                        <div class="flex-shrink-0 flex items-center">
                            @if($inCart)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    In Cart
                                </span>
                            @elseif(!$isOutOfStock)
                                <div class="text-gray-300 group-hover:text-indigo-600 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>
                </button>

            @empty
                <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @if(strlen($search) > 0)
                        <p class="mt-3 text-sm font-medium text-gray-900">No products found</p>
                        <p class="mt-1 text-sm text-gray-500">Try searching with different keywords</p>
                    @else
                        <p class="mt-3 text-sm font-medium text-gray-900">No products available</p>
                        <p class="mt-1 text-sm text-gray-500">Add products to your inventory first</p>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Quick Stats -->
        @if($this->filteredProducts->count() > 0)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500 text-center">
                    Showing {{ $this->filteredProducts->count() }} 
                    {{ $this->filteredProducts->count() === 1 ? 'product' : 'products' }}
                    @if(strlen($search) > 0)
                        matching "{{ $search }}"
                    @endif
                </p>
            </div>
        @endif
    @endif
</div>

<!-- Add custom scrollbar styles -->
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
```

---

## ✅ Features Included

### **1. Rich Product Display:**
- ✅ Product icon (box icon, checkmark when in cart)
- ✅ Product name (first line, bold)
- ✅ Category (under product name)
- ✅ Stock info badges (second line):
  - 🟢 Green badge: "19 sealed"
  - 🟡 Yellow badge: "1 opened" (if any)
  - 🔵 Blue badge: "20 total"
  - Gray text: "(240 items)"

### **2. Interactive States:**
- ✅ **Normal**: White background, hover effects, clickable
- ✅ **In Cart**: Gray background, green checkmark icon, "In Cart" badge, disabled
- ✅ **Out of Stock**: Gray background, red badge, disabled
- ✅ **Hover**: Indigo border, indigo background tint, shadow

### **3. Search Functionality:**
- ✅ Real-time search (300ms debounce)
- ✅ Searches: product name, SKU, category
- ✅ Clear button (X) appears when searching
- ✅ Shows result count

### **4. Visual Feedback:**
- ✅ Products in cart show green checkmark icon
- ✅ "In Cart" badge prevents re-adding
- ✅ Grayed out and disabled when in cart
- ✅ Smooth transitions and hover effects

---

## 🧪 Testing Checklist

1. **Search:**
   - [ ] Type in search box
   - [ ] Products filter in real-time
   - [ ] Clear button (X) appears
   - [ ] Click X clears search

2. **Product Display:**
   - [ ] Shows product icon
   - [ ] Shows product name (first line)
   - [ ] Shows category (under name)
   - [ ] Shows stock badges (second line)
   - [ ] Shows sealed, opened, total boxes
   - [ ] Shows total items

3. **Adding to Cart:**
   - [ ] Click product → Adds to cart
   - [ ] Product becomes grayed out
   - [ ] Shows green checkmark icon
   - [ ] Shows "In Cart" badge
   - [ ] Can't click again (disabled)

4. **Removing from Cart:**
   - [ ] Delete from cart
   - [ ] Product returns to normal state
   - [ ] Becomes clickable again
   - [ ] Checkmark and badge disappear

5. **Out of Stock:**
   - [ ] Shows red "Out of Stock" badge
   - [ ] Grayed out
   - [ ] Disabled (can't click)

---

## 📌 Key Implementation Points

### **Product in Cart Check:**
```php
public function isInCart($productId)
{
    return collect($this->items)->pluck('product_id')->contains($productId);
}
```

### **Direct Add (No Button):**
```php
public function addProductToCart($productId)
{
    // Check if already in cart
    if ($this->isInCart($productId)) {
        return;
    }
    
    // Add with quantity = 0
    $this->items[] = [
        'product_id' => $productId,
        'boxes_requested' => 0,
    ];
}
```

### **Dynamic Filtering:**
```php
public function getFilteredProductsProperty()
{
    return Product::query()
        ->when($this->search, function($q) {
            $q->where('name', 'LIKE', '%' . $this->search . '%')
              ->orWhere('sku', 'LIKE', '%' . $this->search . '%');
        })
        ->get();
}
```

### **Conditional Styling:**
```blade
@if($inCart)
    bg-gray-50 border-gray-200 cursor-not-allowed opacity-60
@elseif($isOutOfStock)
    bg-gray-50 border-gray-200 cursor-not-allowed opacity-50
@else
    bg-white hover:border-indigo-400 hover:bg-indigo-50/30 cursor-pointer
@endif
```

---

## 🎨 Visual States

### **Normal Product:**
```
┌─────────────────────────────────────────────────────┐
│ 📦  Nike Air Max Size 42                        [+] │
│     Footwear > Sneakers                             │
│     🟢 19 sealed  🟡 1 opened  🔵 20 total         │
└─────────────────────────────────────────────────────┘
```

### **Product in Cart:**
```
┌─────────────────────────────────────────────────────┐
│ ✓  Nike Air Max Size 42          [✓ In Cart]       │
│    Footwear > Sneakers                              │
│    🟢 19 sealed  🟡 1 opened  🔵 20 total          │
└─────────────────────────────────────────────────────┘
     ↑ Grayed out, disabled
```

### **Out of Stock:**
```
┌─────────────────────────────────────────────────────┐
│ 📦  Puma Suede Size 38                              │
│     Footwear > Sneakers                             │
│     🔴 Out of Stock                                 │
└─────────────────────────────────────────────────────┘
     ↑ Grayed out, disabled
```

---

## 🚀 Summary

**What Changes:**
1. ✅ Searchable input at top
2. ✅ Rich product cards with icons
3. ✅ Stock badges on second line
4. ✅ Click product → Adds to cart directly
5. ✅ Product becomes inactive (grayed + disabled)
6. ✅ Shows "In Cart" badge with checkmark
7. ✅ Can't re-add (disabled state)

**User Flow:**
1. Type in search → Products filter
2. Click product → Adds to cart with qty = 0
3. Product grays out with checkmark
4. Enter quantity in cart
5. Delete from cart → Product active again

**READY TO IMPLEMENT!** 🎨
