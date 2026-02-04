# CLAUDE CODE: Professional Scrollable Product Dropdown

## 🎯 Goal

Create a custom, professional dropdown that:
- ✅ Looks like a native select but styled beautifully
- ✅ Opens/closes on click
- ✅ Has search input inside
- ✅ Shows scrollable product list
- ✅ Closes when clicking outside
- ✅ Shows selected count
- ✅ Professional animations

---

## 🎨 Visual Preview

```
CLOSED STATE:
┌──────────────────────────────────────────────────┐
│ 🔍 Select products to add (2 selected)      [▼] │
└──────────────────────────────────────────────────┘

OPEN STATE:
┌──────────────────────────────────────────────────┐
│ 🔍 Select products to add (2 selected)      [▲] │
├──────────────────────────────────────────────────┤
│ ┌──────────────────────────────────────────────┐ │
│ │ Search: [Nike...                         ] │ │
│ └──────────────────────────────────────────────┘ │
│                                                  │
│ ┌──────────────────────────────────────────────┐ │
│ │ 📦 Nike Air Max Size 42             [+]     │ │
│ │    🟢 19 sealed  🟡 1 opened  🔵 20 total   │ │
│ ├──────────────────────────────────────────────┤ │
│ │ ✓ Adidas Superstar 41    [✓ In Cart]       │ │ ← Selected
│ │    🟢 15 sealed  🔵 15 total                │ │
│ ├──────────────────────────────────────────────┤ │
│ │ 📦 Puma Suede Size 40               [+]     │ │
│ │    🟢 12 sealed  🔵 12 total                │ │
│ └──────────────────────────────────────────────┘ │
│                                                  │
│ Showing 3 products                               │
└──────────────────────────────────────────────────┘
```

---

## 🔧 Implementation

### **Part 1: Update Livewire Component**

**File:** `app/Livewire/Inventory/Transfers/RequestTransfer.php`

**Add these properties:**

```php
public $search = '';
public $dropdownOpen = false;  // Control dropdown state

/**
 * Toggle dropdown open/close
 */
public function toggleDropdown()
{
    $this->dropdownOpen = !$this->dropdownOpen;
}

/**
 * Close dropdown
 */
public function closeDropdown()
{
    $this->dropdownOpen = false;
}

/**
 * Add product to cart and keep dropdown open
 */
public function addProductToCart($productId)
{
    // Check if already in cart
    foreach ($this->items as $item) {
        if ($item['product_id'] == $productId) {
            return;
        }
    }

    // Add to cart with quantity = 0
    $this->items[] = [
        'product_id' => $productId,
        'boxes_requested' => 0,
    ];
    
    // Keep dropdown open so user can add more products
    // $this->dropdownOpen = true; // Already open
}
```

---

### **Part 2: Replace Product Selection Section in View**

**File:** `resources/views/livewire/inventory/transfers/request-transfer.blade.php`

**FIND the product section (around line 70-120) and REPLACE with:**

```blade
<!-- Custom Scrollable Dropdown -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <div class="flex items-center gap-2 mb-4 text-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        <h2 class="font-bold text-lg">Add Products</h2>
    </div>

    @if(!$fromWarehouseId)
        <!-- No Warehouse Selected -->
        <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-200">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="mt-3 text-gray-400 text-sm">Select a warehouse first to view products.</p>
        </div>
    @else
        <!-- Custom Dropdown Container -->
        <div class="relative" x-data="{ open: @entangle('dropdownOpen') }">
            
            <!-- Dropdown Trigger Button -->
            <button 
                type="button"
                @click="open = !open"
                class="w-full flex items-center justify-between px-4 py-4 bg-white border-2 border-gray-200 rounded-xl hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                :class="{ 'border-indigo-500 ring-2 ring-indigo-500': open }"
            >
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700">
                        Select products to add
                        @if(count($items) > 0)
                            <span class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold">
                                {{ count($items) }} selected
                            </span>
                        @endif
                    </span>
                </div>
                <svg 
                    class="w-5 h-5 text-gray-500 transition-transform duration-200"
                    :class="{ 'rotate-180': open }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Panel -->
            <div 
                x-show="open"
                @click.away="open = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-2xl border border-gray-200"
                style="display: none;"
            >
                <!-- Search Box Inside Dropdown -->
                <div class="p-4 border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            class="block w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Search by name or SKU..."
                            @click.stop
                        />
                        @if(strlen($search) > 0)
                            <button 
                                type="button"
                                wire:click="$set('search', '')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Scrollable Product List -->
                <div class="max-h-96 overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: #CBD5E0 #F7FAFC;">
                    @forelse($products as $product)
                        @php
                            $stock = $stockLevels[$product->id] ?? ['total_boxes'=>0, 'full_boxes'=>0, 'partial_boxes'=>0];
                            $isOutOfStock = $stock['total_boxes'] == 0;
                            $inCart = false;
                            foreach($items as $item) {
                                if($item['product_id'] == $product->id) {
                                    $inCart = true;
                                    break;
                                }
                            }
                        @endphp
                        
                        <button 
                            type="button"
                            wire:click="addProductToCart({{ $product->id }})"
                            @if($isOutOfStock || $inCart) disabled @endif
                            class="w-full text-left flex items-center justify-between p-4 border-b border-gray-50 transition-all
                                @if($inCart)
                                    bg-green-50 hover:bg-green-50 cursor-not-allowed
                                @elseif($isOutOfStock)
                                    bg-gray-50 cursor-not-allowed opacity-50
                                @else
                                    hover:bg-indigo-50 cursor-pointer
                                @endif
                            "
                            @click.stop
                        >
                            <!-- Left: Icon + Info -->
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <!-- Icon -->
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="h-10 w-10 rounded-lg flex items-center justify-center
                                        @if($inCart)
                                            bg-green-200
                                        @elseif($isOutOfStock)
                                            bg-gray-200
                                        @else
                                            bg-indigo-100
                                        @endif
                                    ">
                                        @if($inCart)
                                            <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 @if($isOutOfStock) text-gray-400 @else text-indigo-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                            text-gray-600
                                        @elseif($isOutOfStock)
                                            text-gray-400
                                        @else
                                            text-gray-900
                                        @endif
                                    ">
                                        {{ $product->name }}
                                    </h4>

                                    <!-- Stock Badges -->
                                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                        @if($isOutOfStock)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                                                Out of Stock
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                                {{ $stock['full_boxes'] }} sealed
                                            </span>
                                            @if($stock['partial_boxes'] > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">
                                                    {{ $stock['partial_boxes'] }} opened
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                {{ $stock['total_boxes'] }} total
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Add Button or Status -->
                            <div class="flex-shrink-0 ml-4">
                                @if($inCart)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-600 text-white">
                                        ✓ Added
                                    </span>
                                @elseif(!$isOutOfStock)
                                    <div class="w-8 h-8 rounded-lg bg-green-600 flex items-center justify-center group-hover:bg-green-700 transition-colors">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </button>

                    @empty
                        <div class="text-center py-12 px-4">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            <p class="mt-3 text-sm text-gray-500">
                                @if(strlen($search) > 0)
                                    No products found matching "{{ $search }}"
                                @else
                                    No products available
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>

                <!-- Footer with Count -->
                @if($products->count() > 0)
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 rounded-b-xl">
                        <p class="text-xs text-gray-600 text-center">
                            Showing {{ $products->count() }} products
                            @if(strlen($search) > 0)
                                matching "<span class="font-semibold">{{ $search }}</span>"
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Custom Scrollbar Styles -->
<style>
    [x-cloak] { display: none !important; }
    
    /* Custom scrollbar for product list */
    .max-h-96::-webkit-scrollbar {
        width: 8px;
    }
    
    .max-h-96::-webkit-scrollbar-track {
        background: #F7FAFC;
        border-radius: 4px;
    }
    
    .max-h-96::-webkit-scrollbar-thumb {
        background: #CBD5E0;
        border-radius: 4px;
    }
    
    .max-h-96::-webkit-scrollbar-thumb:hover {
        background: #A0AEC0;
    }
</style>
```

---

## ✅ Features

### **1. Professional Dropdown:**
- ✅ Looks like a native select (but better styled)
- ✅ Opens/closes with smooth animation
- ✅ Closes when clicking outside
- ✅ Shows selected count badge

### **2. Search Inside Dropdown:**
- ✅ Search box appears when dropdown opens
- ✅ Filters products in real-time
- ✅ Clear button (X) to reset search

### **3. Scrollable Product List:**
- ✅ Max height 96 (24rem)
- ✅ Custom styled scrollbar
- ✅ Smooth scrolling

### **4. Product States:**
- ✅ Normal: White, hover indigo, clickable
- ✅ In Cart: Green background, checkmark, "✓ Added" badge
- ✅ Out of Stock: Gray, disabled

### **5. Animations:**
- ✅ Smooth dropdown open/close
- ✅ Fade + slide transition
- ✅ Arrow rotation on toggle

---

## 🧪 Testing Steps

1. **Dropdown Closed:**
   - [ ] Shows "Select products to add"
   - [ ] Shows selected count if items in cart
   - [ ] Arrow pointing down

2. **Click to Open:**
   - [ ] Dropdown slides down smoothly
   - [ ] Arrow rotates up
   - [ ] Search box appears

3. **Search:**
   - [ ] Type "Nike" → Filters products
   - [ ] Shows result count at bottom
   - [ ] Clear button (X) resets

4. **Add Products:**
   - [ ] Click product → Adds to cart
   - [ ] Product turns green with checkmark
   - [ ] Shows "✓ Added" badge
   - [ ] Dropdown stays open

5. **Click Outside:**
   - [ ] Dropdown closes
   - [ ] Selected count updates in trigger

---

## 📌 Key Features Explained

### **Alpine.js Integration:**
```blade
x-data="{ open: @entangle('dropdownOpen') }"
```
This syncs the dropdown state with Livewire.

### **Click Outside to Close:**
```blade
@click.away="open = false"
```

### **Smooth Transitions:**
```blade
x-transition:enter="transition ease-out duration-200"
x-transition:enter-start="opacity-0 translate-y-1"
x-transition:enter-end="opacity-100 translate-y-0"
```

### **Prevent Dropdown Close on Click:**
```blade
@click.stop
```
Prevents clicks inside dropdown from closing it.

---

## 🎯 Summary

**What This Gives You:**
1. ✅ Professional dropdown (like a select but custom)
2. ✅ Search inside dropdown
3. ✅ Scrollable product list
4. ✅ Click outside to close
5. ✅ Shows selected count
6. ✅ Smooth animations
7. ✅ Products can be added without closing dropdown

**User Experience:**
1. Click dropdown → Opens with animation
2. Type in search → Filters products
3. Click product → Adds to cart (dropdown stays open)
4. Product shows checkmark and "✓ Added"
5. Click outside → Dropdown closes
6. Can add multiple products in one go

**This is production-ready!** 🚀
