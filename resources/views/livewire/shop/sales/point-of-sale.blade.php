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

                {{-- ── Phone Scanner Panel ─────────────────────────────────────────────── --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">

                    {{-- Header row --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center
                                {{ $showScannerPanel ? 'bg-green-100' : 'bg-gray-100' }}">
                                <svg class="w-4 h-4 {{ $showScannerPanel ? 'text-green-600' : 'text-gray-500' }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Phone Scanner</p>
                                <p class="text-xs {{ $showScannerPanel ? 'text-green-600 font-medium' : 'text-gray-400' }}">
                                    {{ $showScannerPanel ? '● Connected — polling for scans' : 'Use your phone as a barcode scanner' }}
                                </p>
                            </div>
                        </div>

                        {{-- Toggle button --}}
                        @if($showScannerPanel)
                            <button wire:click="disablePhoneScanner"
                                    class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200
                                           rounded-lg hover:bg-red-50 transition">
                                Disconnect
                            </button>
                        @else
                            <button wire:click="enablePhoneScanner"
                                    class="px-3 py-1.5 text-xs font-medium text-blue-600 border border-blue-200
                                           rounded-lg hover:bg-blue-50 transition">
                                Enable
                            </button>
                        @endif
                    </div>

                    {{-- Expanded panel: QR code + instructions --}}
                    @if($showScannerPanel && $scannerSession)
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">

                            {{-- QR Code --}}
                            <div class="flex flex-col items-center justify-center bg-gray-50 rounded-xl p-4 border border-gray-200">
                                <p class="text-xs text-gray-500 mb-3 font-medium">Scan QR with your phone camera</p>
                                {!! QrCode::size(140)->generate(url('/scanner') . '?code=' . $scannerSession->session_code) !!}
                                <p class="text-xs text-gray-400 mt-3">Or open <strong class="text-gray-600">{{ url('/scanner') }}</strong></p>
                                <div class="mt-2 px-4 py-1.5 bg-white border border-gray-300 rounded-lg">
                                    <p class="text-xl font-bold tracking-[0.3em] text-gray-800 text-center">
                                        {{ $scannerSession->session_code }}
                                    </p>
                                </div>
                                <p class="text-xs text-gray-400 mt-2">
                                    Expires {{ $scannerSession->expires_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Instructions --}}
                            <div class="space-y-3">
                                <p class="text-sm font-semibold text-gray-800">How to connect:</p>
                                <ol class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start gap-2">
                                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                                        <span>Open your phone camera and point it at the QR code</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                                        <span>Tap the notification to open the scanner page</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                                        <span>Scan any box — it will appear in the POS instantly</span>
                                    </li>
                                </ol>

                                {{-- Live status indicator --}}
                                <div class="flex items-center gap-2 mt-3 px-3 py-2 bg-green-50 border border-green-200 rounded-lg">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                    </span>
                                    <p class="text-xs text-green-700 font-medium">Listening for scans every 2 seconds</p>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Polling: only active when scanner session is open --}}
                @if($showScannerPanel)
                    <div wire:poll.2000ms="checkForScans"></div>
                @endif

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
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4"
                     x-data="{ open: @entangle('showSearchResults') }"
                     @click.away="open = false; $wire.closeSearch()">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        🔍 Search Products
                    </label>
                    <div class="relative">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   wire:model.live.debounce.200ms="searchQuery"
                                   wire:focus="loadAvailableProducts"
                                   placeholder="Click to browse or type to filter..."
                                   autocomplete="off"
                                   class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            @if($searchQuery)
                                <button type="button"
                                        wire:click="$set('searchQuery', '')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <!-- Search Results Dropdown -->
                        <div x-show="open"
                             x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute z-20 w-full mt-2 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden">

                            <!-- Result count header -->
                            <div class="px-4 py-2 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                <span class="text-xs text-gray-500 font-medium">
                                    @if($searchQuery)
                                        {{ count($searchResults) }} result{{ count($searchResults) !== 1 ? 's' : '' }}
                                        for "{{ $searchQuery }}"
                                    @else
                                        {{ count($searchResults) }} product{{ count($searchResults) !== 1 ? 's' : '' }} in stock
                                    @endif
                                </span>
                                <button type="button"
                                        @click="open = false; $wire.closeSearch()"
                                        class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Product list -->
                            <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                                @forelse($searchResults as $result)
                                    <button wire:click="selectProduct({{ $result['id'] }})"
                                            @click="open = false"
                                            type="button"
                                            class="w-full px-4 py-3 hover:bg-gray-50 text-left transition group">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-900 truncate group-hover:text-indigo-700 transition">{{ $result['name'] }}</p>
                                                <p class="text-sm text-gray-600 mt-0.5">
                                                    SKU: {{ $result['sku'] }}
                                                    @if($result['category'])
                                                        · {{ $result['category'] }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-right ml-4 shrink-0">
                                                <p class="font-bold text-indigo-600">{{ $result['selling_price_display'] }} RWF</p>
                                                <p class="text-xs text-gray-500">
                                                    Stock: {{ $result['stock']['total_items'] }} items
                                                </p>
                                            </div>
                                        </div>
                                        @if(!$result['has_stock'])
                                            <span class="inline-block mt-2 px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">Out of Stock</span>
                                        @endif
                                    </button>
                                @empty
                                    <div class="px-4 py-8 text-center">
                                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p class="text-sm text-gray-500">No products with stock found</p>
                                        @if($searchQuery)
                                            <p class="text-xs text-gray-400 mt-1">Try a different search term</p>
                                        @endif
                                    </div>
                                @endforelse
                            </div>
                        </div>
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
                                        <button wire:click="openEditItem({{ $index }})"
                                                class="px-3 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded font-medium text-sm flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </button>

                                        <button wire:click="removeCartItem({{ $index }})"
                                                class="ml-auto px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded font-medium text-sm flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
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

    <!-- ── Add/Edit Item Modal ──────────────────────────────────────────────── -->
    @if($showAddModal && $stagingProduct)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
             x-data="{
                 mode: @entangle('stagingMode'),
                 qty: @entangle('stagingQty'),
                 price: @entangle('stagingPrice'),
                 priceModified: @entangle('stagingPriceModified'),
                 get lineTotal() {
                     return this.price * this.qty;
                 },
                 get lineTotalDisplay() {
                     return Math.floor(this.lineTotal / 100).toLocaleString();
                 }
             }">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ $stagingCartIndex !== null ? 'Edit Cart Item' : 'Add to Cart' }}
                    </h3>
                    <button wire:click="closeAddModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Product Info -->
                    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-4 border border-indigo-100">
                        <h4 class="font-bold text-gray-900 text-lg">{{ $stagingProduct['name'] }}</h4>
                        <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">SKU: <span class="font-semibold text-gray-900">{{ $stagingProduct['sku'] }}</span></p>
                                <p class="text-gray-600">Category: <span class="font-semibold text-gray-900">{{ $stagingProduct['category'] ?? 'N/A' }}</span></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Item Price: <span class="font-semibold text-indigo-600">{{ number_format($stagingProduct['selling_price'] / 100, 0) }} RWF</span></p>
                                <p class="text-gray-600">Box Price ({{ $stagingProduct['items_per_box'] }} items): <span class="font-semibold text-indigo-600">{{ number_format($stagingProduct['box_price'] / 100, 0) }} RWF</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Summary -->
                    @if($stagingStock)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-green-900">📦 Available Stock:</span>
                                <div class="flex items-center gap-4">
                                    <span class="text-green-700">
                                        <strong>{{ $stagingStock['total_items'] }}</strong> items
                                    </span>
                                    <span class="text-green-600">
                                        {{ $stagingStock['full_boxes'] }} full box{{ $stagingStock['full_boxes'] !== 1 ? 'es' : '' }}
                                        • {{ $stagingStock['partial_boxes'] }} partial
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Mode Toggle -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Sale Mode</label>
                        <div class="grid grid-cols-2 gap-4">
                            <button wire:click="$set('stagingMode', 'box')"
                                    type="button"
                                    class="px-4 py-3 rounded-lg border-2 transition {{ $stagingMode === 'box' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-300 hover:border-gray-400 text-gray-700' }}">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <span class="font-semibold">Full Boxes</span>
                                </div>
                                <div class="text-xs opacity-75 mt-1">Sell by the box</div>
                            </button>
                            <button wire:click="$set('stagingMode', 'item')"
                                    type="button"
                                    class="px-4 py-3 rounded-lg border-2 transition {{ $stagingMode === 'item' ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-300 hover:border-gray-400 text-gray-700' }}">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    <span class="font-semibold">Individual Items</span>
                                </div>
                                <div class="text-xs opacity-75 mt-1">Sell by piece</div>
                            </button>
                        </div>
                    </div>

                    <!-- Quantity Controls -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Quantity
                            <span class="text-gray-500 font-normal" x-show="mode === 'box'">
                                (number of boxes @ {{ $stagingProduct['items_per_box'] }} items each)
                            </span>
                            <span class="text-gray-500 font-normal" x-show="mode === 'item'">
                                (number of individual items)
                            </span>
                        </label>
                        <div class="flex items-center gap-3">
                            <button wire:click="$set('stagingQty', {{ max(1, $stagingQty - 1) }})"
                                    type="button"
                                    class="w-12 h-12 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold text-xl transition">
                                −
                            </button>
                            <input type="number"
                                   wire:model.live="stagingQty"
                                   min="1"
                                   class="flex-1 text-center text-2xl font-bold px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <button wire:click="$set('stagingQty', {{ $stagingQty + 1 }})"
                                    type="button"
                                    class="w-12 h-12 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 font-bold text-xl transition">
                                +
                            </button>
                        </div>
                    </div>

                    <!-- Price (editable) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Unit Price (RWF)
                            <span class="text-gray-500 font-normal">
                                — price per {{ $stagingMode === 'box' ? 'box' : 'item' }}
                            </span>
                        </label>
                        <div class="relative">
                            <input type="number"
                                   wire:model.live="stagingPrice"
                                   wire:change="$set('stagingPriceModified', {{ $stagingPrice }} !== ({{ $stagingMode }} === 'box' ? {{ $stagingProduct['box_price'] }} : {{ $stagingProduct['selling_price'] }}))"
                                   min="0"
                                   step="100"
                                   class="w-full text-lg font-semibold px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent {{ $stagingPriceModified ? 'bg-yellow-50 border-yellow-400' : '' }}">
                            <div class="absolute right-3 top-3 text-gray-500">
                                RWF
                            </div>
                        </div>
                        @if($stagingPriceModified)
                            <div class="mt-2">
                                <label class="block text-xs text-yellow-700 font-medium mb-1">Price Modification Reason</label>
                                <input type="text"
                                       wire:model="stagingPriceReason"
                                       placeholder="e.g., Bulk discount, damage, manager approval..."
                                       class="w-full text-sm px-3 py-2 border border-yellow-300 rounded-lg bg-yellow-50 focus:ring-2 focus:ring-yellow-500">
                            </div>
                        @endif
                    </div>

                    <!-- Live Line Total -->
                    <div class="bg-indigo-100 border-2 border-indigo-300 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-indigo-700 font-medium">Line Total</p>
                                <p class="text-xs text-indigo-600">
                                    <span x-text="qty"></span> × <span x-text="Math.floor(price / 100).toLocaleString()"></span> RWF
                                </p>
                            </div>
                            <p class="text-3xl font-bold text-indigo-900">
                                <span x-text="lineTotalDisplay"></span> <span class="text-xl">RWF</span>
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4">
                        <button wire:click="confirmAddToCart"
                                type="button"
                                class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ $stagingCartIndex !== null ? 'Update Cart' : 'Add to Cart' }}
                        </button>
                        <button wire:click="closeAddModal"
                                type="button"
                                class="px-6 py-3 border-2 border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition">
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
