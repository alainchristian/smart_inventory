<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <!-- Shop Selector for Owners -->
            @if($isOwner && !empty($availableShops))
                <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-blue-900">Select Shop:</label>
                        <select wire:model.live="shopId"
                                wire:change="changeShop"
                                class="flex-1 max-w-xs px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                            @foreach($availableShops as $shop)
                                <option value="{{ $shop['id'] }}">{{ $shop['name'] }}</option>
                            @endforeach
                        </select>
                        <span class="text-xs text-blue-700">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Owner Mode - Cart will clear when switching shops
                        </span>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Point of Sale</h1>
                    <p class="text-sm text-gray-600">{{ $shopName }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Cart Total</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ number_format($cartTotal / 100, 0) }} RWF</p>
                    </div>
                    @if(!empty($cart))
                        <button wire:click="openCheckout"
                                class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Checkout
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- LEFT: Product Search & Selection -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Barcode Scanner Input -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Scan Barcode
                    </label>
                    <div class="relative">
                        <input type="text"
                               wire:model.live="barcodeInput"
                               placeholder="Scan or type barcode..."
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               autofocus>
                        <div class="absolute right-3 top-3 text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Manual Search -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Search Products
                    </label>
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.300ms="searchQuery"
                               placeholder="Search by name, SKU, or barcode..."
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <div class="absolute right-3 top-3 text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>

                        <!-- Search Results Dropdown -->
                        @if($showSearchResults && !empty($searchResults))
                            <div class="absolute z-20 w-full mt-2 bg-white rounded-lg shadow-lg border border-gray-200 max-h-96 overflow-y-auto">
                                @foreach($searchResults as $result)
                                    <button wire:click="selectProduct({{ $result['id'] }})"
                                            type="button"
                                            class="w-full px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 text-left transition">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-900">{{ $result['name'] }}</p>
                                                <p class="text-sm text-gray-600">
                                                    SKU: {{ $result['sku'] }}
                                                    @if($result['category'])
                                                        • {{ $result['category'] }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-right ml-4">
                                                <p class="font-bold text-indigo-600">{{ $result['selling_price_display'] }} RWF</p>
                                                <p class="text-xs text-gray-500">
                                                    Stock: {{ $result['stock']['total_items'] }} items
                                                </p>
                                            </div>
                                        </div>
                                        @if(!$result['has_stock'])
                                            <span class="inline-block mt-1 px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">Out of Stock</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Cart Items -->
                @if(!empty($cart))
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">Cart Items ({{ count($cart) }})</h3>
                            <button wire:click="clearCart"
                                    class="text-sm text-red-600 hover:text-red-700 font-medium">
                                Clear Cart
                            </button>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($cart as $index => $item)
                                <div class="p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900">{{ $item['product_name'] }}</h4>
                                            <p class="text-sm text-gray-600 mt-1">
                                                Box: {{ $item['box_code'] }}
                                                @if($item['is_full_box'])
                                                    <span class="inline-block ml-2 px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-700 rounded">Full Box</span>
                                                @endif
                                            </p>

                                            @if($item['price_modified'] ?? false)
                                                <div class="mt-2 flex items-center gap-2">
                                                    <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded">Price Modified</span>
                                                    @if($item['requires_owner_approval'] ?? false)
                                                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">Requires Approval</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <div class="text-right">
                                            <p class="font-bold text-gray-900">{{ number_format($item['line_total'] / 100, 0) }} RWF</p>
                                            <p class="text-sm text-gray-600">{{ number_format($item['price'] / 100, 0) }} RWF × {{ $item['quantity'] }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center gap-2">
                                        <div class="flex items-center gap-2">
                                            <button wire:click="updateCartItemQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                                    class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-gray-700 font-semibold">
                                                -
                                            </button>
                                            <span class="px-4 py-1 bg-gray-100 rounded font-semibold text-gray-900">{{ $item['quantity'] }}</span>
                                            <button wire:click="updateCartItemQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                                    class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-gray-700 font-semibold">
                                                +
                                            </button>
                                        </div>

                                        <button wire:click="openPriceModal({{ $index }})"
                                                class="px-3 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded font-medium text-sm">
                                            Modify Price
                                        </button>

                                        <button wire:click="removeCartItem({{ $index }})"
                                                class="ml-auto px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded font-medium text-sm">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-gray-600 font-medium">Cart is empty</p>
                        <p class="text-sm text-gray-500 mt-1">Scan or search for products to add</p>
                    </div>
                @endif
            </div>

            <!-- RIGHT: Quick Info & Summary -->
            <div class="space-y-4">
                <!-- Cart Summary -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sticky top-24">
                    <h3 class="font-semibold text-gray-900 mb-4">Cart Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Items:</span>
                            <span class="font-semibold text-gray-900">{{ count($cart) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Quantity:</span>
                            <span class="font-semibold text-gray-900">{{ array_sum(array_column($cart, 'quantity')) }}</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <div class="flex justify-between text-lg">
                                <span class="font-semibold text-gray-900">Total:</span>
                                <span class="font-bold text-indigo-600">{{ number_format($cartTotal / 100, 0) }} RWF</span>
                            </div>
                        </div>
                    </div>

                    @if(!empty($cart))
                        <button wire:click="openCheckout"
                                class="w-full mt-4 bg-indigo-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                            Proceed to Checkout
                        </button>
                    @endif
                </div>

                <!-- Quick Stats -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-sm p-4 text-white">
                    <h3 class="font-semibold mb-3">Today's Sales</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="opacity-90">Transactions:</span>
                            <span class="font-semibold">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-90">Revenue:</span>
                            <span class="font-semibold">- RWF</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================= MODALS ============================= -->

    <!-- Add Item Modal -->
    @if($showAddItemModal && $selectedProduct)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
                    <h3 class="text-xl font-bold text-gray-900">Add Item to Cart</h3>
                    <button wire:click="closeAddItemModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Product Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-gray-900 text-lg">{{ $selectedProduct['name'] }}</h4>
                        <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">SKU: <span class="font-semibold text-gray-900">{{ $selectedProduct['sku'] }}</span></p>
                                <p class="text-gray-600">Category: <span class="font-semibold text-gray-900">{{ $selectedProduct['category'] ?? 'N/A' }}</span></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Unit Price: <span class="font-semibold text-indigo-600">{{ $selectedProduct['selling_price_display'] }} RWF</span></p>
                                <p class="text-gray-600">Box Price: <span class="font-semibold text-indigo-600">{{ $selectedProduct['box_price_display'] }} RWF</span></p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-4 text-sm">
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full font-semibold">
                                {{ $selectedProduct['stock']['total_items'] }} items in stock
                            </span>
                            <span class="text-gray-600">
                                {{ $selectedProduct['stock']['full_boxes'] }} full • {{ $selectedProduct['stock']['partial_boxes'] }} partial
                            </span>
                        </div>
                    </div>

                    <!-- Sale Mode -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sale Mode</label>
                        <div class="grid grid-cols-2 gap-4">
                            <button wire:click="$set('addItemMode', 'individual')"
                                    class="px-4 py-3 rounded-lg border-2 transition {{ $addItemMode === 'individual' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 hover:border-gray-400' }}">
                                <div class="font-semibold">Individual Items</div>
                                <div class="text-sm opacity-75">Sell by piece</div>
                            </button>
                            <button wire:click="$set('addItemMode', 'full_box')"
                                    class="px-4 py-3 rounded-lg border-2 transition {{ $addItemMode === 'full_box' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 hover:border-gray-400' }}">
                                <div class="font-semibold">Full Box</div>
                                <div class="text-sm opacity-75">Sell entire box</div>
                            </button>
                        </div>
                    </div>

                    <!-- Select Box -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Box</label>
                        <select wire:model="addItemBoxId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            @foreach($selectedProductBoxes as $box)
                                <option value="{{ $box['id'] }}">
                                    {{ $box['box_code'] }} - {{ $box['items_remaining'] }}/{{ $box['items_total'] }} items
                                    @if($box['batch_number']) • Batch: {{ $box['batch_number'] }} @endif
                                    @if($box['expiry_date']) • Exp: {{ $box['expiry_date'] }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Quantity (only for individual mode) -->
                    @if($addItemMode === 'individual')
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <input type="number"
                                   wire:model="addItemQuantity"
                                   min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button wire:click="addItemToCart"
                                class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                            Add to Cart
                        </button>
                        <button wire:click="closeAddItemModal"
                                class="px-6 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Price Modification Modal -->
    @if($showPriceModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">Modify Price</h3>
                    <button wire:click="closePriceModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    @if(isset($cart[$priceModificationCartIndex]))
                        <div class="mb-6 bg-gray-50 rounded-lg p-4">
                            <p class="font-semibold text-gray-900">{{ $cart[$priceModificationCartIndex]['product_name'] }}</p>
                            <p class="text-sm text-gray-600 mt-1">
                                Original: {{ number_format($cart[$priceModificationCartIndex]['original_price'] / 100, 0) }} RWF
                            </p>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Price (RWF)</label>
                            <input type="number"
                                   wire:model="newPrice"
                                   step="1"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason <span class="text-red-500">*</span></label>
                            <textarea wire:model="priceModificationReason"
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                      placeholder="Why is the price being modified?"></textarea>
                            @error('priceModificationReason') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reference (Optional)</label>
                            <input type="text"
                                   wire:model="priceModificationReference"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                   placeholder="e.g., Manager approval, promotion code">
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <p class="text-sm text-yellow-800">
                                <strong>Note:</strong> Price reductions >20% require owner approval before sale can be completed.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button wire:click="applyPriceModification"
                                class="flex-1 bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-yellow-700 transition">
                            Apply Price Change
                        </button>
                        <button wire:click="closePriceModal"
                                class="px-6 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Checkout Modal -->
    @if($showCheckoutModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
                    <h3 class="text-xl font-bold text-gray-900">Complete Sale</h3>
                    <button wire:click="closeCheckoutModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Cart Summary -->
                    <div class="bg-indigo-50 rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700">Items:</span>
                            <span class="font-semibold text-gray-900">{{ count($cart) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700">Total Quantity:</span>
                            <span class="font-semibold text-gray-900">{{ array_sum(array_column($cart, 'quantity')) }}</span>
                        </div>
                        <div class="border-t border-indigo-200 pt-2 mt-2">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Total:</span>
                                <span class="text-2xl font-bold text-indigo-600">{{ number_format($cartTotal / 100, 0) }} RWF</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <select wire:model.live="paymentMethod" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <!-- Amount Received (for cash) -->
                        @if($paymentMethod === 'cash')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received (RWF)</label>
                                <input type="number"
                                       wire:model.live="amountReceived"
                                       step="1"
                                       min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>

                            @if($changeAmount > 0)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="text-sm text-green-800">
                                        <strong>Change:</strong> {{ number_format($changeAmount, 0) }} RWF
                                    </p>
                                </div>
                            @endif
                        @endif

                        <!-- Customer Info -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name (Optional)</label>
                            <input type="text"
                                   wire:model="customerName"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                   placeholder="Enter customer name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Phone (Optional)</label>
                            <input type="text"
                                   wire:model="customerPhone"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                   placeholder="Enter customer phone">
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea wire:model="notes"
                                      rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                      placeholder="Additional notes..."></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button wire:click="completeSale"
                                class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                            Complete Sale
                        </button>
                        <button wire:click="closeCheckoutModal"
                                class="px-6 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Receipt Modal -->
    @if($showReceiptModal && $completedSale)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
                    <h3 class="text-xl font-bold text-gray-900">Sale Receipt</h3>
                    <button wire:click="closeReceipt" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div id="receipt-content" class="p-6">
                    <!-- Receipt Header -->
                    <div class="text-center mb-6 pb-4 border-b-2 border-dashed border-gray-300">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $completedSale->shop->name }}</h2>
                        <p class="text-sm text-gray-600 mt-1">{{ $completedSale->shop->address ?? 'Rwanda' }}</p>
                        <p class="text-sm text-gray-600">{{ $completedSale->shop->phone ?? '' }}</p>
                    </div>

                    <!-- Sale Info -->
                    <div class="mb-6 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Sale #:</span>
                            <span class="font-semibold text-gray-900">{{ $completedSale->sale_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date:</span>
                            <span class="font-semibold text-gray-900">{{ $completedSale->sale_date->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Cashier:</span>
                            <span class="font-semibold text-gray-900">{{ $completedSale->soldBy->name }}</span>
                        </div>
                        @if($completedSale->customer_name)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Customer:</span>
                                <span class="font-semibold text-gray-900">{{ $completedSale->customer_name }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Items -->
                    <div class="mb-6 pb-4 border-b-2 border-dashed border-gray-300">
                        <h4 class="font-semibold text-gray-900 mb-3">Items:</h4>
                        @foreach($completedSale->items as $item)
                            <div class="mb-3">
                                <div class="flex justify-between font-semibold text-gray-900">
                                    <span>{{ $item->product->name }}</span>
                                    <span>{{ number_format($item->line_total / 100, 0) }} RWF</span>
                                </div>
                                <div class="text-sm text-gray-600 flex justify-between">
                                    <span>{{ $item->quantity_sold }} × {{ number_format($item->actual_unit_price / 100, 0) }} RWF</span>
                                    @if($item->is_full_box)
                                        <span class="text-blue-600 font-semibold">Full Box</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Totals -->
                    <div class="space-y-2 text-sm mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold text-gray-900">{{ number_format($completedSale->subtotal / 100, 0) }} RWF</span>
                        </div>
                        @if($completedSale->tax > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax:</span>
                                <span class="font-semibold text-gray-900">{{ number_format($completedSale->tax / 100, 0) }} RWF</span>
                            </div>
                        @endif
                        @if($completedSale->discount > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Discount:</span>
                                <span class="font-semibold">-{{ number_format($completedSale->discount / 100, 0) }} RWF</span>
                            </div>
                        @endif
                        <div class="border-t-2 border-gray-300 pt-2 mt-2">
                            <div class="flex justify-between text-lg">
                                <span class="font-bold text-gray-900">TOTAL:</span>
                                <span class="font-bold text-gray-900">{{ number_format($completedSale->total / 100, 0) }} RWF</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="text-center text-sm text-gray-600 mb-6">
                        <p>Payment: <span class="font-semibold text-gray-900">{{ $completedSale->payment_method->label() }}</span></p>
                    </div>

                    <!-- Footer -->
                    <div class="text-center text-sm text-gray-600 pt-4 border-t border-gray-300">
                        <p class="font-semibold">Thank you for your business!</p>
                        <p class="mt-1">Please come again</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex gap-3">
                    <button wire:click="printReceipt"
                            class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        Print Receipt
                    </button>
                    <button wire:click="closeReceipt"
                            class="px-6 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Print Receipt Script -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('print-receipt', () => {
                window.print();
            });
        });
    </script>

    <!-- Print Styles -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #receipt-content, #receipt-content * {
                visibility: visible;
            }
            #receipt-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
</div>
