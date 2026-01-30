<x-layouts.shop>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $shop->name }}</h1>
        <p class="text-sm text-gray-600 mt-1">Shop Manager Dashboard</p>
    </div>

    <!-- Today's Sales Statistics - Top Row -->
    <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-4">
        <!-- Today's Sales -->
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl shadow-sm border border-green-100 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Today's Sales</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($todaySales['total_sales'], 0) }} <span class="text-xl text-gray-600">RWF</span></p>
                    <p class="text-sm text-gray-600 mt-1">{{ $todaySales['transaction_count'] }} transactions</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-emerald-600">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Items Sold Today -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-sm border border-blue-100 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Items Sold</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($todaySales['items_sold']) }}</p>
                    <p class="text-sm text-gray-600 mt-1">Today</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Average Transaction -->
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl shadow-sm border border-purple-100 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Avg Transaction</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($todaySales['average_transaction'], 0) }} <span class="text-xl text-gray-600">RWF</span></p>
                    <p class="text-sm text-gray-600 mt-1">Per sale</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-600">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- This Week -->
        <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl shadow-sm border border-orange-100 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Weekly Sales</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($weekSales, 0) }} <span class="text-lg text-gray-600">RWF</span></p>
                    <p class="text-sm text-gray-600 mt-1">Month: {{ number_format($monthSales, 0) }} RWF</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-amber-600">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Overview -->
    <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Total Inventory</h3>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stockStats['total_boxes'] }}</p>
            <p class="text-sm text-gray-600 mt-1">boxes • {{ number_format($stockStats['total_items']) }} items</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Full Boxes</h3>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-green-600">{{ $stockStats['full_boxes'] }}</p>
            <p class="text-sm text-gray-600 mt-1">Ready to sell</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Partial Boxes</h3>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-yellow-600">{{ $stockStats['partial_boxes'] }}</p>
            <p class="text-sm text-gray-600 mt-1">In progress</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Recent Sales -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Recent Sales</h3>
                        <p class="text-sm text-gray-600 mt-1">Latest transactions</p>
                    </div>
                    <a href="{{ route('shop.pos') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Sale
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($recentSales as $sale)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-100">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-lg font-bold text-gray-900">{{ number_format($sale->total / 100, 0) }} RWF</p>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        @if($sale->payment_method->value === 'cash') bg-green-100 text-green-800
                                        @elseif($sale->payment_method->value === 'card') bg-blue-100 text-blue-800
                                        @elseif($sale->payment_method->value === 'mobile_money') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $sale->payment_method->label() }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">
                                    {{ $sale->items->count() }} items • {{ $sale->sale_date->format('M d, h:i A') }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">By {{ $sale->soldBy->name }}</p>
                            </div>
                            <a href="{{ route('shop.sales.show', $sale->id) }}" class="ml-4 p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <p class="text-gray-500">No sales yet today</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            Low Stock Alerts
                            @if($lowStockProducts->count() > 0)
                                <span class="ml-2 px-2.5 py-1 text-xs font-bold bg-orange-100 text-orange-800 rounded-full">
                                    {{ $lowStockProducts->count() }}
                                </span>
                            @endif
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Products need restocking</p>
                    </div>
                    <a href="{{ route('shop.transfers.request') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Request Transfer
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($lowStockProducts as $product)
                        <div class="p-4 bg-orange-50 border-l-4 border-orange-500 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-600 mt-1">SKU: {{ $product->sku }}</p>
                                    <div class="flex items-center mt-2 space-x-4">
                                        <span class="text-xs font-medium text-orange-600">Current: {{ $product->current_stock }}</span>
                                        <span class="text-xs text-gray-500">•</span>
                                        <span class="text-xs text-gray-600">Min: {{ $product->low_stock_threshold }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500">All products well-stocked</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Pending Transfers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    Pending Transfer Requests
                    @if($pendingTransfers->count() > 0)
                        <span class="ml-2 px-2.5 py-1 text-xs font-bold bg-yellow-100 text-yellow-800 rounded-full">
                            {{ $pendingTransfers->count() }}
                        </span>
                    @endif
                </h3>
                <p class="text-sm text-gray-600 mt-1">Awaiting warehouse approval</p>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($pendingTransfers as $transfer)
                        <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $transfer->transfer_number }}</p>
                                    <p class="text-sm text-gray-600 mt-1">From {{ $transfer->fromWarehouse->name }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $transfer->items->count() }} products requested</p>
                                    <p class="text-xs text-gray-500 mt-2">{{ $transfer->requested_at->diffForHumans() }}</p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">
                                    Pending
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-500">No pending requests</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Incoming Transfers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    Incoming Transfers
                    @if($incomingTransfers->count() > 0)
                        <span class="ml-2 px-2.5 py-1 text-xs font-bold bg-blue-100 text-blue-800 rounded-full">
                            {{ $incomingTransfers->count() }}
                        </span>
                    @endif
                </h3>
                <p class="text-sm text-gray-600 mt-1">In transit or delivered</p>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($incomingTransfers as $transfer)
                        <div class="p-4 rounded-lg
                            @if($transfer->status->value === 'in_transit') bg-blue-50 border-l-4 border-blue-500
                            @else bg-green-50 border-l-4 border-green-500
                            @endif">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $transfer->transfer_number }}</p>
                                    <p class="text-sm text-gray-600 mt-1">From {{ $transfer->fromWarehouse->name }}</p>
                                    <p class="text-xs text-gray-600 mt-1">Via {{ $transfer->transporter->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 mt-2">Shipped {{ $transfer->shipped_at->diffForHumans() }}</p>
                                </div>
                                @if($transfer->status->value === 'delivered')
                                    <a href="{{ route('shop.transfers.receive', $transfer->id) }}" class="ml-4 px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                        Receive
                                    </a>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                                        In Transit
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-500">No incoming transfers</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row - Top Products & Returns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Selling Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Products</h3>
                <p class="text-sm text-gray-600 mt-1">Best sellers this month</p>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @forelse($topProducts as $index => $product)
                        <div class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg border border-purple-100">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-600 text-white font-bold mr-4">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">{{ $product->name }}</p>
                                <p class="text-xs text-gray-600 mt-1">SKU: {{ $product->sku }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-purple-600">{{ $product->sale_items_count }}</p>
                                <p class="text-xs text-gray-500">sold</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-500">No sales data yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Returns -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            Recent Returns
                            @if($pendingReturns->count() > 0)
                                <span class="ml-2 px-2.5 py-1 text-xs font-bold bg-red-100 text-red-800 rounded-full">
                                    {{ $pendingReturns->count() }} pending
                                </span>
                            @endif
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Customer returns</p>
                    </div>
                    <a href="{{ route('shop.returns.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-rose-600 text-white text-sm font-semibold rounded-lg hover:from-red-700 hover:to-rose-700 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        Process Return
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($recentReturns as $return)
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $return->reason->label() }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $return->items->count() }} items • {{ $return->processed_at->format('M d, h:i A') }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">By {{ $return->processedBy->name }}</p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                    @if($return->approved_at) bg-green-100 text-green-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $return->approved_at ? 'Approved' : 'Pending' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-500">No recent returns</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    @if($alerts->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">System Alerts</h3>
                <p class="text-sm text-gray-600 mt-1">Important notifications</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($alerts as $alert)
                        <div class="flex items-start p-4 rounded-lg
                            @if($alert->severity->value === 'critical') bg-red-50 border-l-4 border-red-500
                            @elseif($alert->severity->value === 'warning') bg-orange-50 border-l-4 border-orange-500
                            @else bg-blue-50 border-l-4 border-blue-500
                            @endif">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">{{ $alert->title }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $alert->message }}</p>
                                <p class="text-xs text-gray-500 mt-2">{{ $alert->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-layouts.shop>
