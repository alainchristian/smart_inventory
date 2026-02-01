<x-app-layout>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $shop->name }}</h1>
        <p class="text-gray-600 text-sm">Sales and inventory management</p>
    </div>

    <!-- Today's Sales Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Today's Revenue -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Today's Sales</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($todaySales['total_sales'], 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">RWF</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Transactions Today -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Transactions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($todaySales['transaction_count']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Today</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Items Sold -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Items Sold</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($todaySales['items_sold']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Today</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Average Transaction -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Avg Transaction</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($todaySales['average_transaction'], 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">RWF</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- This Week -->
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-blue-600">
            <p class="text-sm font-medium text-gray-500">This Week</p>
            <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($weekSales, 0) }}</p>
            <p class="text-sm text-gray-600 mt-2">RWF</p>
        </div>

        <!-- This Month -->
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-gray-400">
            <p class="text-sm font-medium text-gray-500">This Month</p>
            <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($monthSales, 0) }}</p>
            <p class="text-sm text-gray-600 mt-2">RWF</p>
        </div>

        <!-- Stock Summary -->
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-gray-600">
            <p class="text-sm font-medium text-gray-500">Current Stock</p>
            <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($stockStats['total_items']) }}</p>
            <p class="text-sm text-gray-600 mt-2">{{ $stockStats['total_boxes'] }} boxes in shop</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('shop.pos') }}" class="flex items-center justify-center space-x-3 px-6 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="font-semibold">Point of Sale</span>
            </a>
            <a href="{{ route('shop.transfers.request') }}" class="flex items-center justify-center space-x-3 px-6 py-4 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span class="font-semibold">Request Transfer</span>
            </a>
            <a href="{{ route('shop.returns.create') }}" class="flex items-center justify-center space-x-3 px-6 py-4 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path>
                </svg>
                <span class="font-semibold">Process Return</span>
            </a>
        </div>
    </div>

    <!-- Pending Actions -->
    @if($pendingReturns->count() > 0 || $pendingTransfers->count() > 0 || $incomingTransfers->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Pending Returns -->
            @if($pendingReturns->count() > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Pending Returns</h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-900">
                            {{ $pendingReturns->count() }}
                        </span>
                    </div>
                    <div class="space-y-3">
                        @foreach($pendingReturns as $return)
                            <div class="p-3 bg-gray-50 rounded border-l-2 border-gray-400">
                                <p class="font-medium text-gray-900">Return #{{ $return->id }}</p>
                                <p class="text-sm text-gray-600">{{ $return->items->count() }} items</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $return->processed_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Pending Transfer Requests -->
            @if($pendingTransfers->count() > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Pending Transfers</h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-900">
                            {{ $pendingTransfers->count() }}
                        </span>
                    </div>
                    <div class="space-y-3">
                        @foreach($pendingTransfers as $transfer)
                            <div class="p-3 bg-gray-50 rounded border-l-2 border-gray-400">
                                <p class="font-medium text-gray-900">{{ $transfer->transfer_number }}</p>
                                <p class="text-sm text-gray-600">From: {{ $transfer->fromWarehouse->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $transfer->requested_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Incoming Transfers -->
            @if($incomingTransfers->count() > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Incoming</h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-900">
                            {{ $incomingTransfers->count() }}
                        </span>
                    </div>
                    <div class="space-y-3">
                        @foreach($incomingTransfers as $transfer)
                            <div class="p-3 bg-gray-50 rounded border-l-2 border-gray-400">
                                <p class="font-medium text-gray-900">{{ $transfer->transfer_number }}</p>
                                <p class="text-sm text-gray-600">From: {{ $transfer->fromWarehouse->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $transfer->status->label() }} • {{ $transfer->shipped_at ? $transfer->shipped_at->diffForHumans() : 'Not shipped' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Alerts -->
    @if($alerts->count() > 0)
        <div class="bg-white rounded-lg shadow p-6 mb-6 border-l-4 border-orange-500">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-orange-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                Alerts
            </h2>
            <div class="space-y-2">
                @foreach($alerts as $alert)
                    <div class="p-3 bg-orange-50 rounded border-l-2 border-orange-400">
                        <p class="font-medium text-gray-900">{{ $alert->title }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $alert->message }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Low Stock Products -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Low Stock Products</h2>
            <div class="space-y-3">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-sm text-gray-600">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">{{ $product->current_stock }}</p>
                            <p class="text-xs text-gray-500">Threshold: {{ $product->low_stock_threshold }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">All products well stocked</p>
                @endforelse
            </div>
        </div>

        <!-- Top Products This Month -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Products (This Month)</h2>
            <div class="space-y-3">
                @forelse($topProducts as $product)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-sm text-gray-600">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">{{ $product->sale_items_count }}</p>
                            <p class="text-xs text-gray-500">Units sold</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No sales yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Sales</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sale #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sold By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentSales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $sale->sale_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->items->sum('quantity_sold') }} items</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($sale->total / 100, 0) }} RWF</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->soldBy->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->sale_date->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No sales yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Returns -->
    @if($recentReturns->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Returns</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Processed By</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentReturns as $return)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">#{{ $return->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $return->items->count() }} items</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $return->reason }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $return->processedBy->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $return->processed_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
