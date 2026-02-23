<div x-data="{ expandedRow: null }">
    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <!-- Total Returns -->
        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-gray-500 uppercase">Total Returns</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($kpiStats['total_returns']) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">In selected period</p>
        </div>

        <!-- Pending Approval -->
        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-gray-500 uppercase">Pending</span>
            </div>
            <p class="text-2xl font-bold {{ $kpiStats['pending_count'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ number_format($kpiStats['pending_count']) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Awaiting approval</p>
        </div>

        <!-- Total Refunds -->
        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-8 h-8 bg-pink-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-gray-500 uppercase">Refunds</span>
            </div>
            <p class="text-2xl font-bold text-pink-600">
                @if($kpiStats['total_refunds'] >= 1000000)
                    {{ number_format($kpiStats['total_refunds'] / 1000000, 1) }}M
                @elseif($kpiStats['total_refunds'] >= 1000)
                    {{ number_format($kpiStats['total_refunds'] / 1000, 0) }}K
                @else
                    {{ number_format($kpiStats['total_refunds']) }}
                @endif
            </p>
            <p class="text-xs text-gray-500 mt-0.5">Total refund value • RWF</p>
        </div>

        <!-- Exchanges -->
        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-gray-500 uppercase">Exchanges</span>
            </div>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($kpiStats['exchange_count']) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Product exchanges</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Search</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Return #, customer..."
                        class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                    >
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Status</label>
                <select
                    wire:model.live="statusFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                >
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending Approval</option>
                    <option value="approved">Approved</option>
                </select>
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Type</label>
                <select
                    wire:model.live="typeFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                >
                    <option value="all">All Types</option>
                    <option value="refund">Refunds Only</option>
                    <option value="exchange">Exchanges Only</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">From Date</label>
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                >
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">To Date</label>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                >
            </div>
        </div>

        <!-- Reset Button -->
        <div class="mt-4 flex justify-end">
            <button
                wire:click="resetFilters"
                class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reset Filters
            </button>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Return #</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Sale #</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Customer</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden md:table-cell">Reason</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Items</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Refund</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden md:table-cell">Date</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($returns as $return)
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer" @click="expandedRow === {{ $return->id }} ? expandedRow = null : expandedRow = {{ $return->id }}">
                            <!-- Return Number -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $return->return_number }}</div>
                            </td>

                            <!-- Sale Number -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap hidden sm:table-cell">
                                @if($return->sale_id && $return->sale)
                                    <a href="{{ route('shop.sales.show', $return->sale_id) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium" @click.stop>
                                        {{ $return->sale->sale_number }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>

                            <!-- Customer -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $return->customer_name ?? 'Walk-in' }}</div>
                                @if($return->customer_phone)
                                    <div class="text-xs text-gray-500">{{ $return->customer_phone }}</div>
                                @endif
                            </td>

                            <!-- Reason -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap hidden md:table-cell">
                                @php
                                    $reasonColors = [
                                        'defective' => 'bg-red-100 text-red-700 border-red-300',
                                        'wrong_item' => 'bg-orange-100 text-orange-700 border-orange-300',
                                        'damaged' => 'bg-red-100 text-red-700 border-red-300',
                                        'expired' => 'bg-gray-100 text-gray-700 border-gray-300',
                                        'customer_request' => 'bg-blue-100 text-blue-700 border-blue-300',
                                        'other' => 'bg-gray-100 text-gray-700 border-gray-300',
                                    ];
                                    $reasonColor = $reasonColors[$return->reason->value] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold border {{ $reasonColor }}">
                                    {{ $return->reason->label() }}
                                </span>
                            </td>

                            <!-- Items Count -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap hidden lg:table-cell">
                                <div class="text-sm font-medium text-gray-900">{{ $return->items->count() }} items</div>
                                <div class="text-xs text-gray-500">{{ $return->items->sum('quantity_returned') }} qty</div>
                            </td>

                            <!-- Refund Amount -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap">
                                @if($return->is_exchange)
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700 border border-blue-300">
                                        Exchange
                                    </span>
                                @else
                                    <div class="text-sm font-bold text-gray-900">RWF {{ number_format($return->refund_amount) }}</div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap">
                                @if($return->approved_at)
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700 border border-green-300">
                                        Approved
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-700 border border-amber-300">
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <!-- Date -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap text-xs text-gray-600 hidden md:table-cell">
                                {{ $return->processed_at->format('M d, Y') }}
                                <div class="text-gray-400">{{ $return->processed_at->format('g:i A') }}</div>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap text-sm" @click.stop>
                                <button
                                    @click="expandedRow === {{ $return->id }} ? expandedRow = null : expandedRow = {{ $return->id }}"
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                                >
                                    <span x-text="expandedRow === {{ $return->id }} ? 'Hide' : 'View'">View</span>
                                    <svg class="w-3.5 h-3.5 ml-1 transition-transform" :class="{ 'rotate-180': expandedRow === {{ $return->id }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <!-- Expandable Detail Row -->
                        <tr x-show="expandedRow === {{ $return->id }}" x-collapse x-cloak>
                            <td colspan="9" class="px-4 lg:px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                    <!-- Return Items -->
                                    <div class="lg:col-span-2">
                                        <h4 class="text-xs font-bold text-gray-600 uppercase mb-2">Returned Items</h4>
                                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600">Product</th>
                                                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600">Returned</th>
                                                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600">Good</th>
                                                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600">Damaged</th>
                                                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-600">Type</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($return->items as $item)
                                                        <tr>
                                                            <td class="px-3 py-2 text-sm text-gray-900 font-medium">{{ $item->product->name ?? 'Unknown' }}</td>
                                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $item->quantity_returned }}</td>
                                                            <td class="px-3 py-2 text-sm text-green-600 font-medium">{{ $item->quantity_good }}</td>
                                                            <td class="px-3 py-2 text-sm text-red-600 font-medium">{{ $item->quantity_damaged }}</td>
                                                            <td class="px-3 py-2 text-sm">
                                                                @if($item->is_replacement)
                                                                    <span class="px-1.5 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded">Exchange</span>
                                                                @else
                                                                    <span class="px-1.5 py-0.5 text-xs font-bold bg-gray-100 text-gray-700 rounded">Refund</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Return Details -->
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-bold text-gray-600 uppercase mb-2">Details</h4>
                                        <div class="bg-white rounded-lg border border-gray-200 p-3 space-y-2">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Processed by</span>
                                                <span class="font-medium text-gray-900">{{ $return->processedBy->name }}</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Processed at</span>
                                                <span class="font-medium text-gray-900">{{ $return->processed_at->format('M d, Y g:i A') }}</span>
                                            </div>
                                            @if($return->approved_at)
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-500">Approved by</span>
                                                    <span class="font-medium text-gray-900">{{ $return->approvedBy->name ?? '—' }}</span>
                                                </div>
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-500">Approved at</span>
                                                    <span class="font-medium text-gray-900">{{ $return->approved_at->format('M d, Y g:i A') }}</span>
                                                </div>
                                            @endif
                                            @if($return->notes)
                                                <div class="pt-2 border-t border-gray-100">
                                                    <span class="text-xs font-bold text-gray-500 uppercase">Notes</span>
                                                    <p class="text-sm text-gray-700 mt-1">{{ $return->notes }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900">No returns found</h3>
                                    <p class="mt-1 text-sm text-gray-500">No returns match your current filters.</p>
                                    <div class="mt-4">
                                        <a href="{{ route('shop.returns.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Process Return
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($returns->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
