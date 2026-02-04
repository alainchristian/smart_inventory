<div>
    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 p-4 shadow-sm">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-4 shadow-sm">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Main Form -->
    <form wire:submit.prevent="submit" class="space-y-6">
        <!-- Transfer Details Card -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Transfer Details
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- From Warehouse -->
                    <div>
                        <label for="fromWarehouseId" class="block text-sm font-medium text-gray-700 mb-1">
                            From Warehouse <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model.live="fromWarehouseId"
                            id="fromWarehouseId"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            @if(auth()->user()->isShopManager()) disabled @endif
                        >
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @error('fromWarehouseId')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- To Shop -->
                    <div>
                        <label for="toShopId" class="block text-sm font-medium text-gray-700 mb-1">
                            To Shop <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model.live="toShopId"
                            id="toShopId"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            @if(auth()->user()->isShopManager()) disabled @endif
                        >
                            <option value="">Select Shop</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                            @endforeach
                        </select>
                        @error('toShopId')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                    <textarea
                        wire:model="notes"
                        id="notes"
                        rows="3"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                        placeholder="Add any special instructions or notes..."
                    ></textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Add Product Section -->
        @if($fromWarehouseId)
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="bg-green-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Products
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <select
                                wire:model="selectedProductId"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 text-sm"
                            >
                                <option value="">Search and select a product...</option>
                                @foreach($products as $product)
                                    @php
                                        $stock = $stockLevels[$product->id] ?? null;
                                        $totalBoxes = $stock ? $stock['total_boxes'] : 0;
                                        $sealedBoxes = $stock ? $stock['full_boxes'] : 0;
                                        $openedBoxes = $stock ? $stock['partial_boxes'] : 0;
                                    @endphp
                                    <option value="{{ $product->id }}" @if($totalBoxes == 0) disabled @endif>
                                        {{ $product->name }}@if($product->category) ({{ $product->category->name }}) @endif
                                        – 🟢{{ $sealedBoxes }} 🟡{{ $openedBoxes }} 🔵{{ $totalBoxes }}
                                        @if($totalBoxes == 0) – OUT OF STOCK @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button
                            type="button"
                            wire:click="addProduct"
                            class="inline-flex items-center justify-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-sm transition-colors"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Product
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Items Cart -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-purple-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-white">Transfer Cart</h3>
                </div>
                <span class="px-3 py-1 bg-purple-500/40 rounded-full text-xs font-medium text-white">
                    {{ count($items) }} {{ count($items) === 1 ? 'item' : 'items' }}
                </span>
            </div>
            <div class="p-6">
                @if(!$fromWarehouseId)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p class="mt-4 text-base font-medium text-gray-500">Please select a warehouse first</p>
                        <p class="mt-2 text-sm text-gray-400">You'll be able to add products once a warehouse is selected</p>
                    </div>
                @elseif(count($items) === 0)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="mt-4 text-base font-medium text-gray-500">Your cart is empty</p>
                        <p class="mt-2 text-sm text-gray-400">Add products using the dropdown above</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($items as $index => $item)
                            @php
                                $product = $products->firstWhere('id', $item['product_id']);
                                $stock = $stockLevels[$product->id] ?? null;
                                $requestedBoxes = $item['boxes_requested'] ?? 0;
                                $availableBoxes = $stock ? $stock['total_boxes'] : 0;
                                $sealedBoxes = $stock ? $stock['full_boxes'] : 0;
                                $openedBoxes = $stock ? $stock['partial_boxes'] : 0;
                                $exceedsStock = $requestedBoxes > $availableBoxes;
                                $estimatedItems = $requestedBoxes * ($product->items_per_box ?? 0);
                            @endphp
                            <div class="border {{ $exceedsStock ? 'border-red-300' : 'border-gray-200' }} rounded-lg p-5 bg-gray-50">
                                <div class="flex items-start gap-4">
                                    <!-- Numbered Badge -->
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center font-semibold text-sm">
                                            {{ $index + 1 }}
                                        </div>
                                    </div>
                                    <!-- Product Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <h4 class="text-base font-semibold text-gray-900">{{ $product->name }}</h4>
                                                @if($product->category)
                                                    <p class="text-xs text-gray-500 mt-1">{{ $product->category->name }}</p>
                                                @endif
                                            </div>
                                            <button
                                                type="button"
                                                wire:click="removeItem({{ $index }})"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-md transition-colors"
                                                title="Remove from cart"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- Stock Badges -->
                                        <div class="flex flex-wrap items-center gap-2 mt-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span>
                                                {{ $sealedBoxes }} Sealed
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <span class="w-2 h-2 rounded-full bg-yellow-500 mr-1"></span>
                                                {{ $openedBoxes }} Opened
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <span class="w-2 h-2 rounded-full bg-blue-500 mr-1"></span>
                                                {{ $availableBoxes }} Total
                                            </span>
                                        </div>
                                        <!-- Quantity Input and Estimate -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                                    Number of Boxes <span class="text-red-500">*</span>
                                                </label>
                                                <input
                                                    type="number"
                                                    wire:model.live="items.{{ $index }}.boxes_requested"
                                                    min="1"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500 font-semibold text-base"
                                                    placeholder="0"
                                                />
                                                @error('items.' . $index . '.boxes_requested')
                                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                                    Estimated Items
                                                </label>
                                                <div class="h-12 flex items-center px-4 bg-gray-100 border border-gray-200 rounded-md">
                                                    @if($estimatedItems > 0)
                                                        <span class="text-base font-semibold text-purple-700">≈ {{ number_format($estimatedItems) }} items</span>
                                                    @else
                                                        <span class="text-sm text-gray-400">Enter quantity</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Stock Warning -->
                                        @if($exceedsStock && $availableBoxes > 0)
                                            <div class="mt-3 flex items-start p-3 bg-red-50 border-l-4 border-red-500 rounded-r-md">
                                                <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                <p class="text-sm font-medium text-red-800">
                                                    Cannot request {{ $requestedBoxes }} boxes. Only {{ $availableBoxes }} available in warehouse.
                                                </p>
                                            </div>
                                        @elseif($exceedsStock && $availableBoxes == 0)
                                            <div class="mt-3 flex items-start p-3 bg-red-50 border-l-4 border-red-500 rounded-r-md">
                                                <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                <p class="text-sm font-medium text-red-800">
                                                    Product is out of stock.
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <a
                href="{{ route('shop.transfers.index') }}"
                class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancel
            </a>
            <button
                type="submit"
                class="inline-flex items-center px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md shadow-md transition-transform duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                wire:target="submit"
            >
                <svg class="w-5 h-5 mr-2" wire:loading.remove wire:target="submit" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg class="animate-spin w-5 h-5 mr-2" wire:loading wire:target="submit" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="submit">Submit Transfer Request</span>
                <span wire:loading wire:target="submit">Submitting...</span>
            </button>
        </div>
    </form>
</div>
