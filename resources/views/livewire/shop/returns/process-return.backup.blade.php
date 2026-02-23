<div>
    <!-- Flash Messages - Auto-dismissing Toasts -->
    @if (session()->has('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed top-4 right-4 z-50 max-w-sm"
        >
            <div class="bg-green-50 border border-green-300 rounded-lg px-4 py-3 shadow-lg flex items-center space-x-3">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
                <button @click="show = false" class="text-green-500 hover:text-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 6000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed top-4 right-4 z-50 max-w-sm"
        >
            <div class="bg-red-50 border border-red-300 rounded-lg px-4 py-3 shadow-lg flex items-center space-x-3">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium text-red-800">{{ session('error') }}</span>
                <button @click="show = false" class="text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Step Progress Bar -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            @php
                $steps = [
                    1 => 'Select Sale',
                    2 => 'Select Items',
                    3 => 'Return Details',
                ];
            @endphp
            @foreach($steps as $stepNum => $stepLabel)
                <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                    <button
                        wire:click="goToStep({{ $stepNum }})"
                        class="flex items-center space-x-2 {{ $currentStep >= $stepNum ? 'cursor-pointer' : 'cursor-not-allowed' }}"
                        {{ $currentStep < $stepNum ? 'disabled' : '' }}
                    >
                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors
                            {{ $currentStep > $stepNum ? 'bg-green-500 text-white' : ($currentStep == $stepNum ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500') }}">
                            @if($currentStep > $stepNum)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                {{ $stepNum }}
                            @endif
                        </span>
                        <span class="text-sm font-medium {{ $currentStep == $stepNum ? 'text-indigo-700' : 'text-gray-500' }} hidden sm:inline">{{ $stepLabel }}</span>
                    </button>
                    @if(!$loop->last)
                        <div class="flex-1 mx-3 h-0.5 {{ $currentStep > $stepNum ? 'bg-green-400' : 'bg-gray-200' }} transition-colors"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Steps -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Step 1: Select Sale -->
            @if($currentStep === 1)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h2 class="text-sm font-bold text-gray-700 uppercase mb-1">Select Sale</h2>
                    <p class="text-xs text-gray-500 mb-4">Search for the original sale to link this return to.</p>

                    <div class="relative">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="saleSearch"
                                placeholder="Search by sale number, customer name, or phone..."
                                class="w-full pl-9 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                autofocus
                            >
                        </div>

                        <!-- Search Results Dropdown -->
                        @if($showSaleSearchDropdown && count($saleSearchResults) > 0)
                            <div class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-80 overflow-y-auto">
                                @foreach($saleSearchResults as $sale)
                                    <button
                                        wire:click="selectSale({{ $sale['id'] }})"
                                        class="w-full text-left px-4 py-3 hover:bg-indigo-50 border-b border-gray-100 last:border-b-0 transition-colors"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-sm font-bold text-gray-900">{{ $sale['sale_number'] }}</span>
                                                @if($sale['customer_name'])
                                                    <span class="text-xs text-gray-500 ml-2">{{ $sale['customer_name'] }}</span>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xs text-gray-500">{{ $sale['created_at'] }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3 mt-1">
                                            <span class="text-xs text-gray-500">{{ $sale['items_count'] }} items</span>
                                            <span class="text-xs text-gray-500">•</span>
                                            <span class="text-xs font-medium text-gray-700">RWF {{ number_format($sale['total']) }}</span>
                                            @if($sale['sold_by'])
                                                <span class="text-xs text-gray-500">•</span>
                                                <span class="text-xs text-gray-500">by {{ $sale['sold_by'] }}</span>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Empty state hint -->
                    @if(!$showSaleSearchDropdown && strlen($saleSearch) === 0)
                        <div class="text-center py-8 mt-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-700">Find the original sale</p>
                            <p class="text-xs text-gray-500 mt-1">Type a sale number, customer name, or phone to search</p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Step 2: Select Items from Sale -->
            @if($currentStep === 2 && $linkedSale)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <!-- Linked Sale Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-bold text-gray-700 uppercase mb-0.5">Select Items to Return</h2>
                            <p class="text-xs text-gray-500">
                                Sale <span class="font-bold text-indigo-600">{{ $linkedSale->sale_number }}</span>
                                @if($linkedSale->customer_name)
                                    — {{ $linkedSale->customer_name }}
                                @endif
                                <span class="text-gray-400 ml-1">• RWF {{ number_format($linkedSale->total ?? 0) }}</span>
                            </p>
                        </div>
                        <button wire:click="changeSale" class="text-xs text-gray-500 hover:text-red-600 font-medium transition-colors">
                            Change Sale
                        </button>
                    </div>

                    <!-- Sale Items List -->
                    <div class="space-y-2">
                        @foreach($linkedSale->items as $saleItem)
                            @php
                                $isSelected = $this->isItemSelected($saleItem->id);
                                $selectedIndex = collect($items)->search(function ($item) use ($saleItem) {
                                    return ($item['original_sale_item_id'] ?? null) == $saleItem->id;
                                });
                            @endphp
                            <div class="border rounded-lg transition-all {{ $isSelected ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                <!-- Item Header (toggle) -->
                                <button
                                    wire:click="toggleItem({{ $saleItem->id }})"
                                    class="w-full text-left px-4 py-3 flex items-center justify-between"
                                >
                                    <div class="flex items-center space-x-3">
                                        <!-- Checkbox -->
                                        <div class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors
                                            {{ $isSelected ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 bg-white' }}">
                                            @if($isSelected)
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $saleItem->product->name }}</p>
                                            <p class="text-xs text-gray-500">
                                                Sold: <span class="font-bold">{{ $saleItem->quantity_sold }}</span>
                                                • RWF {{ number_format($saleItem->unit_price ?? 0) }} each
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700">RWF {{ number_format(($saleItem->unit_price ?? 0) * $saleItem->quantity_sold) }}</span>
                                </button>

                                <!-- Expanded Details (when selected) -->
                                @if($isSelected && $selectedIndex !== false)
                                    <div class="px-4 pb-4 border-t border-indigo-200 pt-3">
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            <!-- Quantity to Return -->
                                            <div>
                                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Qty to Return</label>
                                                <input
                                                    type="number"
                                                    value="{{ $items[$selectedIndex]['quantity_returned'] }}"
                                                    wire:change="updateItemQuantity({{ $selectedIndex }}, 'quantity_returned', $event.target.value)"
                                                    min="1"
                                                    max="{{ $items[$selectedIndex]['quantity_sold'] }}"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                                >
                                                <span class="text-xs text-gray-400 mt-0.5">Max: {{ $items[$selectedIndex]['quantity_sold'] }}</span>
                                            </div>

                                            <!-- Quantity Damaged -->
                                            <div>
                                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Qty Damaged</label>
                                                <input
                                                    type="number"
                                                    value="{{ $items[$selectedIndex]['quantity_damaged'] }}"
                                                    wire:change="updateItemQuantity({{ $selectedIndex }}, 'quantity_damaged', $event.target.value)"
                                                    min="0"
                                                    max="{{ $items[$selectedIndex]['quantity_returned'] }}"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                                >
                                            </div>

                                            <!-- Condition Notes -->
                                            <div class="col-span-2 sm:col-span-1">
                                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Condition</label>
                                                <input
                                                    type="text"
                                                    value="{{ $items[$selectedIndex]['condition_notes'] }}"
                                                    wire:change="updateConditionNotes({{ $selectedIndex }}, $event.target.value)"
                                                    placeholder="e.g. scratched, broken seal..."
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                                >
                                            </div>
                                        </div>

                                        <!-- Good/Damaged summary for this item -->
                                        <div class="flex items-center space-x-4 mt-2 text-xs">
                                            <span class="text-green-600 font-bold">{{ $items[$selectedIndex]['quantity_good'] }} good</span>
                                            @if($items[$selectedIndex]['quantity_damaged'] > 0)
                                                <span class="text-red-600 font-bold">{{ $items[$selectedIndex]['quantity_damaged'] }} damaged</span>
                                            @endif
                                            <span class="text-gray-400">• Subtotal: RWF {{ number_format(($items[$selectedIndex]['unit_price'] ?? 0) * $items[$selectedIndex]['quantity_returned']) }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Next Step Button -->
                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-xs text-gray-500">
                            {{ count($items) }} of {{ $linkedSale->items->count() }} items selected
                        </p>
                        <button
                            wire:click="goToStep(3)"
                            {{ count($items) === 0 ? 'disabled' : '' }}
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-lg transition-colors"
                        >
                            Continue
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Step 3: Return Details -->
            @if($currentStep === 3)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h2 class="text-sm font-bold text-gray-700 uppercase mb-4">Return Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Customer Name -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Customer Name</label>
                            <input
                                type="text"
                                wire:model="customerName"
                                placeholder="Enter customer name..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                            >
                        </div>

                        <!-- Customer Phone -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Customer Phone</label>
                            <input
                                type="text"
                                wire:model="customerPhone"
                                placeholder="Enter phone number..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                            >
                        </div>

                        <!-- Return Reason -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Return Reason *</label>
                            <select
                                wire:model="reason"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                            >
                                @foreach($returnReasons as $r)
                                    <option value="{{ $r->value }}">{{ $r->label() }}</option>
                                @endforeach
                            </select>
                            @error('reason') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Return Type: Refund or Exchange -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">What does the customer need?</label>
                            <div class="grid grid-cols-2 gap-2 mt-1">
                                <button
                                    wire:click="$set('isExchange', false)"
                                    class="flex items-center justify-center px-3 py-2.5 rounded-lg border-2 transition-colors text-sm font-medium
                                        {{ !$isExchange ? 'border-pink-500 bg-pink-50 text-pink-700' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}"
                                >
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Refund
                                </button>
                                <button
                                    wire:click="$set('isExchange', true)"
                                    class="flex items-center justify-center px-3 py-2.5 rounded-lg border-2 transition-colors text-sm font-medium
                                        {{ $isExchange ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}"
                                >
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    Exchange
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mt-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Notes</label>
                        <textarea
                            wire:model="notes"
                            rows="3"
                            placeholder="Add any additional notes about this return..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm resize-none"
                        ></textarea>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Summary Card -->
        <div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 sticky top-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Return Summary</h3>

                <!-- Linked Sale -->
                @if($linkedSale)
                    <div class="flex items-center space-x-2 mb-4 p-2.5 bg-gray-50 rounded-lg border border-gray-200">
                        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-900 truncate">{{ $linkedSale->sale_number }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $linkedSale->customer_name ?? 'Walk-in' }} • RWF {{ number_format($linkedSale->total ?? 0) }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center space-x-2 mb-4 p-2.5 bg-amber-50 rounded-lg border border-amber-200">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <p class="text-xs text-amber-700 font-medium">No sale selected yet</p>
                    </div>
                @endif

                <!-- Return Type Indicator -->
                @if($currentStep >= 3)
                    <div class="flex items-center space-x-3 p-3 rounded-lg mb-4 {{ $isExchange ? 'bg-blue-50 border border-blue-200' : 'bg-pink-50 border border-pink-200' }}">
                        @if($isExchange)
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-blue-800">Exchange</p>
                                <p class="text-xs text-blue-600">Product replacement</p>
                            </div>
                        @else
                            <div class="w-8 h-8 bg-pink-500 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-pink-800">Refund</p>
                                <p class="text-xs text-pink-600">Money back</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Stats -->
                <div class="space-y-2.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Items selected</span>
                        <span class="font-bold text-gray-900">{{ $totalItems }}</span>
                    </div>
                    @if($totalItems > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Return qty</span>
                            <span class="font-bold text-gray-900">{{ $totalQuantity }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Good</span>
                            <span class="font-bold text-green-600">{{ $totalGood }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Damaged</span>
                            <span class="font-bold {{ $totalDamaged > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $totalDamaged }}</span>
                        </div>
                    @endif

                    @if($estimatedRefund > 0 && !$isExchange)
                        <div class="pt-2.5 mt-2.5 border-t border-gray-100">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Est. Refund</span>
                                <span class="font-bold text-pink-600">RWF {{ number_format($estimatedRefund) }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Items List -->
                @if($totalItems > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-2">Items</p>
                        <div class="space-y-1.5">
                            @foreach($items as $item)
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-700 truncate mr-2">{{ $item['product_name'] }}</span>
                                    <span class="text-gray-500 shrink-0">×{{ $item['quantity_returned'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Submit Section (visible in step 3) -->
                @if($currentStep === 3)
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        @if($showConfirmation)
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-3">
                                <p class="text-xs font-bold text-amber-800 uppercase mb-1">Confirm Submission</p>
                                <p class="text-xs text-amber-700">
                                    Process a <strong>{{ $isExchange ? 'product exchange' : 'refund' }}</strong>
                                    for <strong>{{ $totalItems }} item(s)</strong> ({{ $totalQuantity }} qty)
                                    @if(!$isExchange && $estimatedRefund > 0)
                                        — <strong>RWF {{ number_format($estimatedRefund) }}</strong>
                                    @endif
                                    @if($customerName)
                                        for <strong>{{ $customerName }}</strong>
                                    @endif
                                    ?
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    wire:click="cancelSubmit"
                                    class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    wire:click="submitReturn"
                                    wire:loading.attr="disabled"
                                    class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-lg transition-colors"
                                >
                                    <span wire:loading.remove wire:target="submitReturn">Confirm</span>
                                    <span wire:loading wire:target="submitReturn">Processing...</span>
                                </button>
                            </div>
                        @else
                            <button
                                wire:click="confirmSubmit"
                                wire:loading.attr="disabled"
                                {{ count($items) === 0 ? 'disabled' : '' }}
                                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-lg transition-colors flex items-center justify-center"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Process Return
                            </button>
                        @endif
                    </div>

                    @error('items')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @error('linkedSaleId')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                @endif
            </div>
        </div>
    </div>
</div>
