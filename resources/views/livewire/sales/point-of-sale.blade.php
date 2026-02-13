<div class="min-h-screen bg-gray-100" x-data="posPage()">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-0 z-10">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Point of Sale</h1>
            <p class="text-xs text-gray-500">{{ auth()->user()->location?->name }}</p>
        </div>
        <div class="flex items-center gap-4 text-sm text-gray-600">
            <span>{{ now()->format('l, d M Y') }}</span>
            <span class="font-semibold text-gray-900" x-text="currentTime"></span>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('error'))
        <div class="mx-6 mt-3 px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif
    @if(session('scan_error'))
        <div class="mx-6 mt-3 px-4 py-3 bg-orange-50 border border-orange-200 text-orange-800 text-sm rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('scan_error') }}
        </div>
    @endif

    <div class="p-6 grid grid-cols-12 gap-6">

        {{-- LEFT: Scan + Search --}}
        <div class="col-span-12 lg:col-span-7 space-y-4">

            {{-- Scan Box --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">📦 Scan Box Code</label>
                <div class="flex gap-3">
                    <input type="text"
                           wire:model="scanInput"
                           wire:keydown.enter="scanBox"
                           placeholder="Scan box label barcode or type box code…"
                           class="flex-1 rounded-lg border-gray-300 font-mono text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button wire:click="scanBox"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Add
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">Scan the barcode on the physical box — system identifies the product automatically</p>
            </div>

            {{-- Search Product --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5"
                 x-data="{ open: @entangle('showSearchResults') }"
                 @click.away="open = false; $wire.closeSearch()">

                <label class="block text-sm font-semibold text-gray-700 mb-2">🔍 Search Product</label>

                <div class="relative">
                    {{-- Search input --}}
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text"
                               wire:model.live.debounce.200ms="productSearch"
                               wire:focus="loadAvailableProducts"
                               placeholder="Click to browse or type to filter…"
                               autocomplete="off"
                               class="block w-full pl-9 pr-8 py-2.5 rounded-lg border-gray-300
                                      focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        {{-- Clear button --}}
                        @if($productSearch)
                            <button type="button"
                                    wire:click="$set('productSearch', '')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    </div>

                    {{-- Dropdown --}}
                    <div x-show="open"
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">

                        {{-- Result count header --}}
                        <div class="px-3 py-2 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500 font-medium">
                                @if($productSearch)
                                    {{ count($searchResults) }} result{{ count($searchResults) !== 1 ? 's' : '' }}
                                    for "{{ $productSearch }}"
                                @else
                                    {{ count($searchResults) }} product{{ count($searchResults) !== 1 ? 's' : '' }} in stock
                                @endif
                            </span>
                            <button type="button"
                                    @click="open = false; $wire.closeSearch()"
                                    class="text-gray-400 hover:text-gray-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Product list --}}
                        <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                            @forelse($searchResults as $result)
                                <button type="button"
                                        wire:click="addProductToCart({{ $result['id'] }})"
                                        @click="open = false"
                                        class="w-full text-left px-4 py-3 hover:bg-blue-50 transition group">
                                    <div class="flex justify-between items-start gap-3">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-900 text-sm truncate
                                                      group-hover:text-blue-700 transition">
                                                {{ $result['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                {{ $result['sku'] }}
                                                @if($result['category'] !== '—')
                                                    · {{ $result['category'] }}
                                                @endif
                                                · {{ $result['items_per_box'] }} items/box
                                            </p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-sm font-bold text-green-700">
                                                {{ number_format($result['box_price'] / 100) }} RWF
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{ $result['available_boxes'] }} box{{ $result['available_boxes'] !== 1 ? 'es' : '' }}
                                                · {{ number_format($result['available_items']) }} items
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-sm text-gray-400">No products with stock found</p>
                                    @if($productSearch)
                                        <p class="text-xs text-gray-300 mt-1">Try a different search term</p>
                                    @endif
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- RIGHT: Cart --}}
        <div class="col-span-12 lg:col-span-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col" style="min-height:480px">

                {{-- Cart header --}}
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900 flex items-center gap-2">
                        Cart
                        @if($this->itemCount > 0)
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-semibold">
                                {{ $this->itemCount }}
                            </span>
                        @endif
                    </h2>
                    @if(!empty($cart))
                        <button wire:click="clearCart"
                                wire:confirm="Clear the entire cart?"
                                class="text-xs text-red-500 hover:text-red-700 font-medium">
                            Clear all
                        </button>
                    @endif
                </div>

                {{-- Items --}}
                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2">
                    @forelse($cart as $key => $item)
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 border border-gray-200 group">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $item['product_name'] }}</p>
                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                    <span class="text-xs px-2 py-0.5 rounded font-medium
                                        {{ $item['sell_by'] === 'box' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $item['sell_by'] === 'box' ? '📦 Full boxes' : '🔢 Items' }}
                                    </span>
                                    @if($item['price_modified'])
                                        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded font-medium">✏ Price modified</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600 mt-1">
                                    {{ $item['quantity'] }}
                                    {{ $item['sell_by'] === 'box'
                                        ? 'box' . ($item['quantity'] > 1 ? 'es' : '')
                                        : 'item' . ($item['quantity'] > 1 ? 's' : '') }}
                                    × {{ number_format($item['final_price'] / 100) }} RWF
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-bold text-gray-900 text-sm">
                                    {{ number_format(($item['quantity'] * $item['final_price']) / 100) }}
                                </p>
                                <p class="text-xs text-gray-400">RWF</p>
                            </div>
                            <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition shrink-0">
                                <button wire:click="openEditItem('{{ $key }}')"
                                        class="text-xs text-blue-500 hover:text-blue-700 font-medium">Edit</button>
                                <button wire:click="removeFromCart('{{ $key }}')"
                                        class="text-xs text-red-400 hover:text-red-600 font-medium">Remove</button>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center py-16 text-center">
                            <svg class="w-14 h-14 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-400">Cart is empty</p>
                            <p class="text-xs text-gray-300 mt-1">Scan a box or search a product to begin</p>
                        </div>
                    @endforelse
                </div>

                {{-- Totals + Checkout --}}
                @if(!empty($cart))
                    <div class="border-t border-gray-100 px-5 py-4 space-y-2">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-medium text-gray-900">{{ number_format($this->subtotal / 100) }} RWF</span>
                        </div>
                        @if($tax > 0)
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Tax</span>
                                <span>{{ number_format($tax) }} RWF</span>
                            </div>
                        @endif
                        @if($discount > 0)
                            <div class="flex justify-between text-sm text-red-500">
                                <span>Discount</span>
                                <span>-{{ number_format($discount) }} RWF</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                            <span>Total</span>
                            <span class="text-blue-700">{{ number_format($this->total / 100) }} RWF</span>
                        </div>
                        <button wire:click="$set('showCheckoutModal', true)"
                                class="w-full mt-2 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-base shadow-md transition">
                            Proceed to Checkout
                        </button>
                    </div>
                @endif

            </div>
        </div>

    </div>


    {{-- ── EDIT ITEM MODAL ──────────────────────────────────────────────────── --}}
    @if($showEditModal && $editingCartKey && isset($cart[$editingCartKey]))
        @php $ei = $cart[$editingCartKey]; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('showEditModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4"
                 x-data="{
                     qty: {{ $ei['quantity'] }},
                     sellBy: '{{ $ei['sell_by'] }}',
                     finalPrice: {{ $ei['final_price'] }},
                     priceReason: '{{ $ei['price_reason'] ?? '' }}',
                     get originalPrice() { return this.sellBy === 'box' ? {{ $ei['box_price'] }} : {{ $ei['unit_price'] }}; },
                     get lineTotal() { return this.qty * this.finalPrice; }
                 }" @click.stop>

                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-gray-900">Edit Cart Item</h3>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $ei['product_name'] }}</p>
                    </div>
                    <button wire:click="$set('showEditModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                {{-- Sell by --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sell as</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label :class="sellBy === 'box' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                               class="flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer">
                            <input type="radio" x-model="sellBy" value="box" class="sr-only">
                            <span class="text-sm font-medium">📦 Full Boxes</span>
                        </label>
                        <label :class="sellBy === 'item' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'"
                               class="flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer">
                            <input type="radio" x-model="sellBy" value="item" class="sr-only">
                            <span class="text-sm font-medium">🔢 Individual Items</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        Available:
                        <span x-text="sellBy === 'box' ? '{{ $ei['available_boxes'] }} box(es)' : '{{ number_format($ei['available_items']) }} items'"></span>
                    </p>
                </div>

                {{-- Quantity --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="if(qty > 1) qty--"
                                class="w-10 h-10 rounded-lg border border-gray-300 text-xl font-bold hover:bg-gray-50 flex items-center justify-center">−</button>
                        <input type="number" x-model.number="qty" min="1"
                               class="flex-1 text-center rounded-lg border-gray-300 font-semibold text-lg">
                        <button type="button" @click="qty++"
                                class="w-10 h-10 rounded-lg border border-gray-300 text-xl font-bold hover:bg-gray-50 flex items-center justify-center">+</button>
                    </div>
                </div>

                {{-- Price --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Price (RWF) <span class="text-xs text-gray-400 font-normal">per <span x-text="sellBy === 'box' ? 'box' : 'item'"></span></span>
                    </label>
                    <input type="number" x-model.number="finalPrice" min="0"
                           :class="finalPrice !== originalPrice ? 'border-yellow-400 bg-yellow-50' : 'border-gray-300'"
                           class="block w-full rounded-lg">
                    <p class="text-xs text-gray-400 mt-1">
                        Standard: <span x-text="(originalPrice / 100).toLocaleString()"></span> RWF
                        <template x-if="finalPrice !== originalPrice">
                            <span class="text-yellow-600 font-medium ml-1">⚠ Modified</span>
                        </template>
                    </p>
                </div>

                {{-- Price reason --}}
                <div x-show="finalPrice !== originalPrice" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for price change <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="priceReason"
                           placeholder="e.g. Bulk discount, Promotion, Damaged packaging"
                           class="block w-full rounded-lg border-yellow-300">
                </div>

                {{-- Line total --}}
                <div class="bg-gray-50 rounded-lg px-4 py-3 flex justify-between">
                    <span class="text-sm text-gray-600">Line total:</span>
                    <span class="font-bold text-blue-700" x-text="(lineTotal / 100).toLocaleString() + ' RWF'"></span>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3">
                    <button wire:click="$set('showEditModal', false)"
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="$wire.updateCartItem(
                                '{{ $editingCartKey }}',
                                qty,
                                sellBy,
                                finalPrice,
                                finalPrice !== originalPrice,
                                priceReason || null
                            )"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700">
                        Update
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ── CHECKOUT MODAL ──────────────────────────────────────────────────── --}}
    @if($showCheckoutModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('showCheckoutModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-5" @click.stop>

                <h2 class="text-lg font-bold text-gray-900">Checkout</h2>

                {{-- Order summary --}}
                <div class="bg-gray-50 rounded-xl p-4 space-y-2 max-h-48 overflow-y-auto">
                    @foreach($cart as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">
                                {{ $item['product_name'] }}
                                <span class="text-gray-400">× {{ $item['quantity'] }} {{ $item['sell_by'] === 'box' ? 'box(es)' : 'item(s)' }}</span>
                            </span>
                            <span class="font-medium">{{ number_format(($item['quantity'] * $item['final_price']) / 100) }}</span>
                        </div>
                    @endforeach
                    <div class="border-t border-gray-200 pt-2 mt-1 space-y-1">
                        @if($tax > 0)
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Tax</span><span>+ {{ number_format($tax) }}</span>
                            </div>
                        @endif
                        @if($discount > 0)
                            <div class="flex justify-between text-sm text-red-500">
                                <span>Discount</span><span>- {{ number_format($discount) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-bold text-base pt-1">
                            <span>Total</span>
                            <span class="text-blue-700">{{ number_format($this->total / 100) }} RWF</span>
                        </div>
                    </div>
                </div>

                {{-- Discount --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount (RWF)</label>
                    <input type="number" wire:model.live="discount" min="0"
                           class="block w-full rounded-lg border-gray-300 text-sm">
                </div>

                {{-- Payment method --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([
                            ['cash',          '💵 Cash'],
                            ['card',          '💳 Card'],
                            ['mobile_money',  '📱 Mobile Money'],
                            ['bank_transfer', '🏦 Bank Transfer'],
                        ] as [$val, $label])
                            <label class="flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer transition
                                {{ $paymentMethod === $val ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="paymentMethod" value="{{ $val }}" class="sr-only">
                                <span class="text-sm font-medium">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('paymentMethod')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button wire:click="$set('showCheckoutModal', false)"
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50">
                        Back
                    </button>
                    <button wire:click="completeSale"
                            wire:loading.attr="disabled"
                            class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white rounded-xl font-bold text-base shadow transition">
                        <span wire:loading.remove wire:target="completeSale">Complete Sale</span>
                        <span wire:loading wire:target="completeSale">Processing…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ── RECEIPT MODAL ───────────────────────────────────────────────────── --}}
    @if($showReceipt && $completedSale)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center space-y-4">

                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-9 h-9 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900">Sale Complete!</h2>
                    <p class="text-sm text-gray-500 mt-1 font-mono">{{ $completedSale['sale_number'] }}</p>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2">
                    @foreach($completedSale['items'] as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">
                                {{ $item['product_name'] }}
                                <span class="text-gray-400">× {{ $item['quantity'] }}</span>
                            </span>
                            <span class="font-medium">{{ number_format(($item['quantity'] * $item['final_price']) / 100) }}</span>
                        </div>
                    @endforeach
                    <div class="border-t border-gray-200 pt-2 flex justify-between font-bold">
                        <span>Total Paid</span>
                        <span class="text-green-700">{{ number_format($completedSale['total'] / 100) }} RWF</span>
                    </div>
                    <p class="text-xs text-gray-400 text-right mt-1">
                        via {{ ucwords(str_replace('_', ' ', $completedSale['payment_method'])) }}
                    </p>
                </div>

                <div class="flex gap-3">
                    <button onclick="window.print()"
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 hover:bg-gray-50">
                        🖨 Print
                    </button>
                    <button wire:click="closeReceipt"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700">
                        New Sale
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
function posPage() {
    return {
        currentTime: '',
        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
        },
        tick() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    };
}
</script>
@endpush
