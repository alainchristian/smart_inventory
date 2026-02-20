{{--
    Enhanced Shop Dashboard for Smart Inventory

    This Blade view refactors the standard shop dashboard into a more actionable
    interface with trend indicators, risk summaries and improved quick actions.  It
    expects additional variables to be provided by the controller or Livewire
    component:

    - $todaySales['total_sales'], ['transaction_count'], ['items_sold'], ['average_transaction']
    - $todaySalesChange (float): percentage change vs yesterday (sales)
    - $todayTransactionsChange (float): percentage change vs yesterday (transactions)
    - $todayItemsChange (float): percentage change vs yesterday (items sold)
    - $todayAvgTxnChange (float): percentage change vs yesterday (avg transaction)
    - $weekSales, $monthSales, $stockStats['total_items'], $stockStats['total_boxes']
    - $lowStockProducts (collection), $pendingReturns, $pendingTransfers, $incomingTransfers
    - $recentSales, $recentReturns
    - $alerts (collection)
    - $lastSync (Carbon): last system sync time

    This file maintains the existing sections (sales performance, pending actions,
    low stock products, top products, recent sales/returns) but enhances KPI cards
    with trend indicators and introduces a compact risk summary card after the
    quick actions.
--}}

<x-app-layout>
    <!-- Page Header -->
    <div class="mb-4 flex flex-col lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $shop->name }}</h1>
            <p class="text-gray-600 text-sm">Sales and inventory managemenst</p>
        </div>
        <div class="mt-2 lg:mt-0 text-sm text-gray-500">Last sync: {{ $lastSync->diffForHumans() }}</div>
    </div>

    <!-- Today's Sales Overview - Compact KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <!-- Today's Revenue with trend -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $todaySalesChange >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                    {{ $todaySalesChange >= 0 ? '▲' : '▼' }} {{ number_format(abs($todaySalesChange), 1) }}%
                </span>
            </div>
            <p class="text-xs text-gray-600 mb-0.5">Today's Sales</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($todaySales['total_sales'], 0) }}</p>
            <p class="text-xs text-gray-500">RWF</p>
        </div>

        <!-- Transactions Today with trend -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $todayTransactionsChange >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                    {{ $todayTransactionsChange >= 0 ? '▲' : '▼' }} {{ number_format(abs($todayTransactionsChange), 1) }}%
                </span>
            </div>
            <p class="text-xs text-gray-600 mb-0.5">Transactions</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($todaySales['transaction_count']) }}</p>
            <p class="text-xs text-gray-500">Today</p>
        </div>

        <!-- Items Sold with trend -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $todayItemsChange >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                    {{ $todayItemsChange >= 0 ? '▲' : '▼' }} {{ number_format(abs($todayItemsChange), 1) }}%
                </span>
            </div>
            <p class="text-xs text-gray-600 mb-0.5">Items Sold</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($todaySales['items_sold']) }}</p>
            <p class="text-xs text-gray-500">Today</p>
        </div>

        <!-- Average Transaction with trend -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $todayAvgTxnChange >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                    {{ $todayAvgTxnChange >= 0 ? '▲' : '▼' }} {{ number_format(abs($todayAvgTxnChange), 1) }}%
                </span>
            </div>
            <p class="text-xs text-gray-600 mb-0.5">Avg Transaction</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($todaySales['average_transaction'], 0) }}</p>
            <p class="text-xs text-gray-500">RWF</p>
        </div>
    </div>

    <!-- Sales Performance & Stock Summary - Horizontal Compact Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <!-- This Week -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-600">This Week</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($weekSales, 0) }}</p>
                    <p class="text-xs text-gray-500">RWF</p>
                </div>
            </div>
        </div>

        <!-- This Month -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-600">This Month</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($monthSales, 0) }}</p>
                    <p class="text-xs text-gray-500">RWF</p>
                </div>
            </div>
        </div>

        <!-- Stock Summary -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-600">Current Stock</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stockStats['total_items']) }}</p>
                    <p class="text-xs text-gray-500">{{ $stockStats['total_boxes'] }} boxes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Actions - Compact Style -->
    @if($pendingReturns->count() > 0 || $pendingTransfers->count() > 0 || $incomingTransfers->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
            <!-- Pending Returns -->
            @if($pendingReturns->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">Pending Returns</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            {{ $pendingReturns->count() }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        @foreach($pendingReturns as $return)
                            <div class="p-2 bg-gray-50 rounded border-l-2 border-orange-400">
                                <p class="text-sm font-medium text-gray-900">Return #{{ $return->id }}</p>
                                <p class="text-xs text-gray-600">{{ $return->items->count() }} items</p>
                                <p class="text-xs text-gray-500">{{ $return->processed_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Pending Transfer Requests -->
            @if($pendingTransfers->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">Pending Transfers</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $pendingTransfers->count() }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        @foreach($pendingTransfers as $transfer)
                            <div class="p-2 bg-gray-50 rounded border-l-2 border-yellow-400">
                                <p class="text-sm font-medium text-gray-900">{{ $transfer->transfer_number }}</p>
                                <p class="text-xs text-gray-600">From: {{ $transfer->fromWarehouse->name }}</p>
                                <p class="text-xs text-gray-500">{{ $transfer->requested_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Incoming Transfers -->
            @if($incomingTransfers->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">Incoming</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $incomingTransfers->count() }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        @foreach($incomingTransfers as $transfer)
                            <div class="p-2 bg-gray-50 rounded border-l-2 border-blue-400">
                                <p class="text-sm font-medium text-gray-900">{{ $transfer->transfer_number }}</p>
                                <p class="text-xs text-gray-600">From: {{ $transfer->fromWarehouse->name }}</p>
                                <p class="text-xs text-gray-500">{{ $transfer->status->label() }} • {{ $transfer->shipped_at ? $transfer->shipped_at->diffForHumans() : 'Not shipped' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Low Stock Products & Top Products - Compact -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- Low Stock Products -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-900">Low Stock Products</h2>
                @if($lowStockProducts->count() > 0)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    {{ $lowStockProducts->count() }}
                </span>
                @endif
            </div>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between p-2 bg-orange-50 rounded border-l-2 border-orange-400">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-gray-600">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right ml-2">
                            <p class="text-lg font-bold text-orange-600">{{ $product->current_stock }}</p>
                            <p class="text-xs text-gray-500">of {{ $product->low_stock_threshold }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-gray-500">All products well stocked</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Top Products This Month -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-900">Top Products (This Month)</h2>
                @if($topProducts->count() > 0)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ $topProducts->count() }}
                </span>
                @endif
            </div>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($topProducts as $product)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-gray-600">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right ml-2">
                            <p class="text-lg font-bold text-green-600">{{ $product->sale_items_count }}</p>
                            <p class="text-xs text-gray-500">sold</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-sm text-gray-500">No sales yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Sales - Compact Table -->
    <div class="bg-white rounded-lg shadow-sm p-3 mb-4 border border-gray-200">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Recent Sales</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sale #</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sold By</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentSales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ $sale->sale_number }}</td>
                            <td class="px-3 py-2 text-sm text-gray-600">{{ $sale->items->sum('quantity_sold') }} items</td>
                            <td class="px-3 py-2 text-sm font-semibold text-gray-900 text-right">{{ number_format($sale->total / 100, 0) }} RWF</td>
                            <td class="px-3 py-2 text-sm text-gray-600">{{ $sale->soldBy->name }}</td>
                            <td class="px-3 py-2 text-sm text-gray-600">{{ $sale->sale_date->format('M d, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-sm text-gray-500">No sales yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Returns - Compact Table -->
    @if($recentReturns->count() > 0)
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200 mb-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Recent Returns</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Return #</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Processed By</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentReturns as $return)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-sm font-medium text-gray-900">#{{ $return->id }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ $return->items->count() }} items</td>
                                <td class="px-3 py-2 text-sm text-gray-600 truncate max-w-xs">{{ $return->reason }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ $return->processedBy->name }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ $return->processed_at->format('M d, H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- System Status Footer -->
    <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200 mb-20">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-xs text-gray-600">System Status: <span class="font-semibold text-green-600">Online</span></span>
            </div>
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-xs text-gray-500">Last updated: {{ $lastSync->format('M d, Y H:i:s') }}</span>
            </div>
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-xs text-gray-500">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>

    <!-- Floating Action Button (FAB) with Quick Actions -->
    <x-floating-action-button>
        <x-slot name="actions">
            <a href="{{ route('shop.pos') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition-colors">Point of Sale</p>
                    <p class="text-xs text-gray-500">Create new sale</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('shop.transfers.request') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-gray-600 to-gray-700 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-gray-700 transition-colors">Request Transfer</p>
                    <p class="text-xs text-gray-500">Order from warehouse</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('shop.returns.create') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-red-700 transition-colors">Process Return</p>
                    <p class="text-xs text-gray-500">Handle customer returns</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-red-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('shop.reports.sales') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-indigo-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Sales Report</p>
                    <p class="text-xs text-gray-500">View analytics</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            @if($lowStockProducts->count() > 0 || $pendingReturns->count() > 0 || $pendingTransfers->count() > 0)
            <div class="px-5 py-2 bg-gray-100">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Attention Required</p>
            </div>

            @if($lowStockProducts->count() > 0)
            <a href="#"
               class="flex items-center space-x-3 px-5 py-3 bg-gradient-to-r from-orange-50 to-yellow-50 hover:from-orange-100 hover:to-yellow-100 transition-all duration-200 group border-b border-orange-200">
                <div class="w-11 h-11 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md animate-pulse">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-orange-900 group-hover:text-orange-700 transition-colors">Low Stock</p>
                    <p class="text-xs text-orange-700 font-semibold">{{ $lowStockProducts->count() }} products low</p>
                </div>
                <svg class="w-5 h-5 text-orange-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            @endif

            @if($pendingReturns->count() > 0)
            <a href="#"
               class="flex items-center space-x-3 px-5 py-3 bg-gradient-to-r from-yellow-50 to-amber-50 hover:from-yellow-100 hover:to-amber-100 transition-all duration-200 group border-b border-yellow-200">
                <div class="w-11 h-11 bg-gradient-to-br from-yellow-500 to-amber-500 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-yellow-900 group-hover:text-yellow-700 transition-colors">Pending Returns</p>
                    <p class="text-xs text-yellow-700 font-semibold">{{ $pendingReturns->count() }} pending</p>
                </div>
                <svg class="w-5 h-5 text-yellow-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            @endif

            @if($pendingTransfers->count() > 0)
            <a href="#"
               class="flex items-center space-x-3 px-5 py-3 bg-gradient-to-r from-blue-50 to-cyan-50 hover:from-blue-100 hover:to-cyan-100 transition-all duration-200 group">
                <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-blue-900 group-hover:text-blue-700 transition-colors">Pending Transfers</p>
                    <p class="text-xs text-blue-700 font-semibold">{{ $pendingTransfers->count() }} awaiting approval</p>
                </div>
                <svg class="w-5 h-5 text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            @endif
            @endif
        </x-slot>
    </x-floating-action-button>
</x-app-layout>