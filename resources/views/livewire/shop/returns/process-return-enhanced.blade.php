<div x-data x-on:keydown.ctrl.s.prevent="$wire.confirmSubmit()" x-on:keydown.escape="$wire.cancelSubmit()">
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

    <!-- Keyboard Shortcuts Help -->
    <div class="mb-4 p-2 bg-gray-50 border border-gray-200 rounded-lg">
        <p class="text-xs text-gray-500 flex items-center space-x-4">
            <span>⌨️ <strong>Ctrl+S</strong> Submit</span>
            <span><strong>Esc</strong> Cancel</span>
        </p>
    </div>

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
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-sm font-bold text-gray-700 uppercase">Select Sale</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Search for the original sale to link this return to.</p>
                        </div>
                        <button
                            wire:click="loadTodaySales"
                            wire:loading.attr="disabled"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="loadTodaySales">📅 Today's Sales</span>
                            <span wire:loading wire:target="loadTodaySales">Loading...</span>
                        </button>
                    </div>

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
                                @if($showQuickSales)
                                    <div class="px-4 py-2 bg-indigo-50 border-b border-indigo-200">
                                        <p class="text-xs font-bold text-indigo-700">Today's Sales ({{ count($saleSearchResults) }})</p>
                                    </div>
                                @endif
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
                    @if(!$showSaleSearchDropdown && strlen($saleSearch) === 0 && !$showQuickSales)
                        <div class="text-center py-8 mt-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-700">Find the original sale</p>
                            <p class="text-xs text-gray-500 mt-1">Type to search or click "Today's Sales" above</p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Step 2: Select Items from Sale -->
            @if($currentStep === 2 && $linkedSale)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <!-- Warnings -->
                    @if($saleAgeWarning && $saleAgeDays > 0)
                        <div class="mb-4 p-3 bg-amber-50 border border-amber-300 rounded-lg flex items-start space-x-2">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800">Old Sale Warning</p>
                                <p class="text-xs text-amber-700 mt-1">
                                    This sale is <strong>{{ $saleAgeDays }} days old</strong>. Returns older than 7 days may require manager approval.
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($existingReturnWarning)
                        <div class="mb-4 p-3 bg-orange-50 border border-orange-300 rounded-lg flex items-start space-x-2">
                            <svg class="w-5 h-5 text-orange-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-orange-800">Duplicate Return Warning</p>
                                <p class="text-xs text-orange-700 mt-1">{{ $existingReturnWarning }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Rest of Step 2 content from original file... -->
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

                    <!-- Sale Items List (keep existing from original file)... -->
                </div>
            @endif

            <!-- Step 3: Return Details -->
            @if($currentStep === 3)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h2 class="text-sm font-bold text-gray-700 uppercase mb-4">Return Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Customer fields... (keep from original) -->

                        <!-- Return Type: Refund or Exchange -->
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Return Type</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    wire:click="$set('isExchange', false)"
                                    class="flex flex-col items-center justify-center px-4 py-3 rounded-lg border-2 transition-colors
                                        {{ !$isExchange ? 'border-pink-500 bg-pink-50' : 'border-gray-200 bg-white hover:border-gray-300' }}"
                                >
                                    <svg class="w-8 h-8 {{ !$isExchange ? 'text-pink-600' : 'text-gray-400' }} mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-bold {{ !$isExchange ? 'text-pink-700' : 'text-gray-600' }}">Refund</span>
                                </button>
                                <button
                                    wire:click="$set('isExchange', true)"
                                    class="flex flex-col items-center justify-center px-4 py-3 rounded-lg border-2 transition-colors
                                        {{ $isExchange ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:border-gray-300' }}"
                                >
                                    <svg class="w-8 h-8 {{ $isExchange ? 'text-blue-600' : 'text-gray-400' }} mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    <span class="text-sm font-bold {{ $isExchange ? 'text-blue-700' : 'text-gray-600' }}">Exchange</span>
                                </button>
                            </div>
                        </div>

                        <!-- Refund Method (only if not exchange) -->
                        @if(!$isExchange)
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Refund Method *</label>
                                <select
                                    wire:model="refundMethod"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="card">Card Refund</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="store_credit">Store Credit</option>
                                </select>
                                @error('refundMethod') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <!-- Notes (keep from original)... -->
                </div>
            @endif
        </div>

        <!-- Right Column: Summary Card -->
        <div>
            <div class="bg-white rounded-lg border border-gray-200 p-5 sticky top-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Return Summary</h3>

                <!-- Existing sale info... (keep from original) -->

                <!-- Stats (keep from original)... -->

                <!-- Inventory Impact Preview -->
                @if($totalItems > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-2">Inventory Impact</p>
                        <div class="space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-green-600">✓ {{ $totalGood }} items return to stock</span>
                            </div>
                            @if($totalDamaged > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-red-600">✗ {{ $totalDamaged }} items marked damaged</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Submit section (keep from original with modifications)... -->
            </div>
        </div>
    </div>
</div>
