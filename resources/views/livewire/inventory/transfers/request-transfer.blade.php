<div class="min-h-screen bg-gray-50/50 p-6">
    <div class="max-w-7xl mx-auto space-y-6">

        <form wire:submit.prevent="submit">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                        <div class="flex items-center gap-2 mb-6 text-gray-800 relative z-10">
                            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h2 class="font-bold text-lg">Transfer Config</h2>
                        </div>

                        <div class="space-y-6 relative z-10">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">From Warehouse</label>
                                <div class="relative">
                                    <select 
                                        wire:model.live="fromWarehouseId" 
                                        class="block w-full pl-3 pr-10 py-3 text-base border-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg bg-gray-50 transition-shadow duration-200 @error('fromWarehouseId') border-red-300 focus:ring-red-200 focus:border-red-400 @enderror"
                                    >
                                        <option value="">Select Warehouse</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('fromWarehouseId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex justify-center -my-2 relative">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="w-full border-t border-gray-100"></div>
                                </div>
                                <div class="relative flex justify-center">
                                    <span class="bg-white px-2 text-gray-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">To Shop</label>
                                <select 
                                    wire:model.live="toShopId" 
                                    class="block w-full pl-3 pr-10 py-3 text-base border-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg bg-gray-50 transition-shadow duration-200 @error('toShopId') border-red-300 focus:ring-red-200 focus:border-red-400 @enderror"
                                >
                                    <option value="">Select Shop</option>
                                    @foreach($shops as $shop)
                                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                                    @endforeach
                                </select>
                                @error('toShopId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Internal Notes</label>
                                <textarea 
                                    wire:model="notes" 
                                    rows="4" 
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-200 rounded-lg bg-gray-50 resize-none p-3 transition-shadow duration-200" 
                                    placeholder="Purpose of transfer..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative z-30" x-data="{ open: @entangle('dropdownOpen') }" @click.away="open = false">
                        <button
                            type="button"
                            @click="open = !open"
                            @if(!$fromWarehouseId) disabled @endif
                            class="w-full flex items-center justify-between px-4 py-3 bg-gradient-to-r from-indigo-50 to-indigo-100 border-2 border-indigo-200 rounded-xl hover:from-indigo-100 hover:to-indigo-200 transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed group"
                        >
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-full bg-indigo-500 text-white flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="font-semibold text-gray-900">
                                        @if($fromWarehouseId)
                                            Select products to add
                                        @else
                                            Select warehouse first
                                        @endif
                                    </div>
                                    @if(count($items) > 0)
                                        <div class="text-xs text-indigo-600 font-medium flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            {{ count($items) }} {{ count($items) === 1 ? 'product' : 'products' }} selected
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500">
                                            Click to browse inventory
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="transform transition-transform duration-200" :class="{'rotate-180': open}">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-2xl overflow-hidden z-50"
                            style="display: none;"
                        >
                            <div class="p-4 bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg wire:loading.remove wire:target="search" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <svg wire:loading wire:target="search" class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        wire:model.live.debounce.300ms="search"
                                        type="text"
                                        class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition"
                                        placeholder="Search products by name or SKU..."
                                        autofocus
                                    />
                                    @if(strlen($search) > 0)
                                        <button
                                            type="button"
                                            wire:click="$set('search', '')"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    @if(strlen($search) > 0)
                                        <div class="text-xs text-gray-600 font-medium">
                                            Found {{ count($products) }} {{ count($products) === 1 ? 'product' : 'products' }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-400">Recent items</div>
                                    @endif
                                    
                                    <div class="text-[10px] text-gray-400 uppercase tracking-wide font-bold">Stock Status</div>
                                </div>
                            </div>

                            <div class="max-h-[28rem] overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 #f1f5f9;">
                                @forelse($products as $product)
                                    @php
                                        $stock = $stockLevels[$product->id] ?? ['total_boxes'=>0, 'full_boxes'=>0, 'partial_boxes'=>0];
                                        $isOutOfStock = $stock['total_boxes'] == 0;
                                        
                                        // Check if product is in cart
                                        $isInCart = false;
                                        foreach ($items as $item) {
                                            if ($item['product_id'] == $product->id) {
                                                $isInCart = true;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <button
                                        type="button"
                                        wire:click="addProductToCart({{ $product->id }})"
                                        @if($isOutOfStock || $isInCart) disabled @endif
                                        class="w-full text-left group flex items-center justify-between p-4 border-b border-gray-100 last:border-b-0 transition-all duration-200
                                            @if($isInCart)
                                                bg-green-50/50 cursor-not-allowed opacity-75
                                            @elseif($isOutOfStock)
                                                bg-gray-50 cursor-not-allowed opacity-60
                                            @else
                                                bg-white hover:bg-indigo-50/50 cursor-pointer
                                            @endif
                                        "
                                    >
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center flex-shrink-0 transition-colors shadow-sm
                                                @if($isInCart)
                                                    bg-green-500 text-white
                                                @elseif($isOutOfStock)
                                                    bg-gray-200 text-gray-400
                                                @else
                                                    bg-indigo-100 text-indigo-600 group-hover:bg-indigo-500 group-hover:text-white
                                                @endif
                                            ">
                                                @if($isInCart)
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                    </svg>
                                                @endif
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</h4>
                                                    @if($isInCart)
                                                        <span class="text-[10px] font-bold bg-green-500 text-white px-2 py-0.5 rounded-full flex-shrink-0 shadow-sm">Added</span>
                                                    @endif
                                                </div>
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    @if($isOutOfStock)
                                                        <span class="text-[10px] font-semibold bg-red-100 text-red-700 px-2 py-0.5 rounded border border-red-200">Out of Stock</span>
                                                    @else
                                                        <span class="text-[10px] font-medium bg-green-100 text-green-700 px-2 py-0.5 rounded border border-green-200 flex items-center gap-1" title="Sealed Boxes">
                                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                            {{ $stock['full_boxes'] }} sealed
                                                        </span>
                                                        <span class="text-[10px] font-medium bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded border border-yellow-200 flex items-center gap-1" title="Opened Boxes">
                                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                                                            {{ $stock['partial_boxes'] }} opened
                                                        </span>
                                                        <span class="text-[10px] font-medium bg-blue-100 text-blue-700 px-2 py-0.5 rounded border border-blue-200 flex items-center gap-1">
                                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                            {{ $stock['total_boxes'] }} total
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if(!$isOutOfStock && !$isInCart)
                                            <div class="text-indigo-300 group-hover:text-indigo-600 transition-colors flex-shrink-0 ml-4">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </div>
                                        @endif
                                    </button>
                                @empty
                                    <div class="text-center py-16 text-gray-400 bg-gray-50/50">
                                        @if(strlen($search) > 0)
                                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-sm font-medium">No products found</p>
                                            <p class="text-xs mt-1">Try different keywords or SKU</p>
                                        @else
                                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                            <p class="text-sm font-medium">Start searching</p>
                                            <p class="text-xs mt-1">Type to find products in stock</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col min-h-[400px]">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 rounded-t-2xl">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-gray-800 text-lg">Transfer Cart</h3>
                                <span class="px-2 py-0.5 bg-gray-200 text-gray-700 text-xs font-bold rounded-full">{{ count($items) }}</span>
                            </div>
                            @if(count($items) > 0)
                                <button type="button" wire:click="$set('items', [])" class="text-xs font-medium text-red-500 hover:text-red-700 hover:underline transition">
                                    Clear All
                                </button>
                            @endif
                        </div>

                        <div class="p-6 flex-grow">
                            @if(count($items) === 0)
                                <div class="h-full flex flex-col items-center justify-center text-gray-300 py-12">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-lg font-medium text-gray-400">Your cart is empty</p>
                                    <p class="text-sm text-gray-400 mt-1">Select items from the dropdown above.</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach($items as $index => $item)
                                        @php
                                            $product = $products->firstWhere('id', $item['product_id']);
                                            if (!$product) {
                                                $product = \App\Models\Product::find($item['product_id']);
                                            }
                                            $stock = $stockLevels[$product->id] ?? ['total_boxes'=>0, 'full_boxes'=>0, 'partial_boxes'=>0];
                                            $availableBoxes = $stock['total_boxes'];
                                            $requestedBoxes = $item['boxes_requested'] ?? 0;
                                            $totalItems = $requestedBoxes * ($product->items_per_box ?? 0);
                                            $exceedsStock = $requestedBoxes > $availableBoxes;
                                        @endphp
                                        @if($product)
                                            <div wire:key="item-{{ $item['product_id'] }}" class="p-4 bg-white rounded-xl border-2 transition-all duration-200 {{ $exceedsStock && $requestedBoxes > 0 ? 'border-red-300 shadow-red-50' : 'border-gray-100 shadow-sm hover:border-indigo-200' }}">
                                                <div class="flex items-start gap-4">
                                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold flex-shrink-0 border border-indigo-100">
                                                        {{ $index + 1 }}
                                                    </div>

                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex justify-between items-start">
                                                            <h4 class="font-semibold text-gray-900 mb-2 truncate pr-4">{{ $product->name }}</h4>
                                                            <button
                                                                type="button"
                                                                wire:click="removeItem({{ $index }})"
                                                                class="text-gray-400 hover:text-red-500 transition-colors"
                                                                title="Remove"
                                                            >
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </div>

                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                            <div>
                                                                <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Boxes (Available: {{ $availableBoxes }})</label>
                                                                <input
                                                                    type="number"
                                                                    wire:model.live="items.{{ $index }}.boxes_requested"
                                                                    min="0"
                                                                    max="{{ $availableBoxes }}"
                                                                    class="w-full px-3 py-2 border rounded-lg text-sm font-semibold transition {{ $exceedsStock && $requestedBoxes > 0 ? 'border-red-300 bg-red-50 text-red-700 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 focus:ring-indigo-500 focus:border-indigo-500' }}"
                                                                    placeholder="0"
                                                                />
                                                                @error("items.{$index}.boxes_requested") <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                                                            </div>

                                                            <div>
                                                                <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Estimated Units</label>
                                                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg flex justify-between items-center h-[38px]">
                                                                    <span class="text-xs text-gray-500">{{ $product->items_per_box ?? 0 }}/box</span>
                                                                    <span class="text-sm font-bold text-gray-800">
                                                                        {{ number_format($totalItems) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if($exceedsStock && $requestedBoxes > 0)
                                                            <div class="mt-3 flex items-start gap-2 text-red-600 animate-pulse">
                                                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                                </svg>
                                                                <p class="text-xs font-semibold">
                                                                    Exceeds stock by {{ $requestedBoxes - $availableBoxes }} boxes.
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if(count($items) > 0)
                            <div class="bg-gray-50 p-6 rounded-b-2xl border-t border-gray-200">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Transfer Summary</h4>
                                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                                    <div class="text-gray-600 text-sm">
                                        <span class="font-medium text-gray-900">{{ count($items) }}</span> Unique Products
                                    </div>
                                    <div class="flex gap-4">
                                        @php
                                            $grandTotalBoxes = 0;
                                            $grandTotalItems = 0;
                                            foreach($items as $itm) {
                                                $bx = (int)($itm['boxes_requested'] ?? 0);
                                                $grandTotalBoxes += $bx;
                                                $prd = $products->firstWhere('id', $itm['product_id']) ?? \App\Models\Product::find($itm['product_id']);
                                                if($prd) $grandTotalItems += $bx * ($prd->items_per_box ?? 0);
                                            }
                                        @endphp
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500">Total Boxes</div>
                                            <div class="font-bold text-gray-900 text-lg">{{ number_format($grandTotalBoxes) }}</div>
                                        </div>
                                        <div class="text-right pl-4 border-l border-gray-200">
                                            <div class="text-xs text-gray-500">Est. Units</div>
                                            <div class="font-bold text-indigo-600 text-lg">{{ number_format($grandTotalItems) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-2">
                        <a href="{{ route('shop.transfers.index') }}" class="px-6 py-3 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:text-gray-700 transition-colors shadow-sm">
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="relative bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-3 rounded-xl shadow-lg shadow-indigo-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none min-w-[180px]"
                            @if(count($items) == 0) disabled @endif
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>Complete Transfer</span>
                            <span wire:loading class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>