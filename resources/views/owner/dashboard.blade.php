<x-layouts.owner>
    <x-slot name="header">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Overview</h1>
            <p class="mt-1 text-sm text-gray-600">Welcome back! Here's what's happening with your business today.</p>
        </div>
        <div class="text-sm text-gray-500">
            {{ now()->format('l, F j, Y') }}
        </div>
    </x-slot>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-4">
        <!-- Total Users -->
        <div class="relative overflow-hidden bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                        <p class="mt-2 text-xs text-green-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            Active users
                        </p>
                    </div>
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
        </div>

        <!-- Total Warehouses -->
        <div class="relative overflow-hidden bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Warehouses</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_warehouses'] }}</p>
                        <p class="mt-2 text-xs text-gray-500">Distribution centers</p>
                    </div>
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-green-500 to-green-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-1 bg-gradient-to-r from-green-500 to-green-600"></div>
        </div>

        <!-- Total Shops -->
        <div class="relative overflow-hidden bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Retail Shops</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_shops'] }}</p>
                        <p class="mt-2 text-xs text-gray-500">Sales locations</p>
                    </div>
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-purple-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-1 bg-gradient-to-r from-purple-500 to-purple-600"></div>
        </div>

        <!-- Total Products -->
        <div class="relative overflow-hidden bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Products</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_products'] }}</p>
                        <p class="mt-2 text-xs text-gray-500">SKUs in catalog</p>
                    </div>
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-orange-500 to-orange-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-1 bg-gradient-to-r from-orange-500 to-orange-600"></div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-3">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-sm border border-blue-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Inventory Value</h3>
                <span class="px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-200 rounded-full">Purchase Cost</span>
            </div>
            <p class="text-4xl font-bold text-gray-900 mb-2">{{ number_format($stats['inventory_value'], 0) }} <span class="text-xl text-gray-600">RWF</span></p>
            <p class="text-sm text-gray-600">{{ number_format($stats['total_items_in_stock']) }} items in stock</p>
            <div class="mt-4 flex items-center text-sm text-blue-600">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Total capital invested
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl shadow-sm border border-green-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Retail Value</h3>
                <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full">Selling Price</span>
            </div>
            <p class="text-4xl font-bold text-gray-900 mb-2">{{ number_format($stats['retail_value'], 0) }} <span class="text-xl text-gray-600">RWF</span></p>
            <p class="text-sm text-gray-600">Potential revenue from stock</p>
            <div class="mt-4 flex items-center text-sm text-green-600">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Maximum achievable value
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-xl shadow-sm border border-yellow-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Potential Profit</h3>
                <span class="px-3 py-1 text-xs font-semibold text-yellow-700 bg-yellow-200 rounded-full">
                    {{ $stats['inventory_value'] > 0 ? number_format(($stats['potential_profit'] / $stats['inventory_value']) * 100, 1) : 0 }}% Margin
                </span>
            </div>
            <p class="text-4xl font-bold text-green-600 mb-2">{{ number_format($stats['potential_profit'], 0) }} <span class="text-xl text-gray-600">RWF</span></p>
            <p class="text-sm text-gray-600">Expected gross profit</p>
            <div class="mt-4 flex items-center text-sm text-yellow-600">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Revenue - Cost
            </div>
        </div>
    </div>

    <!-- Sales Performance -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Sales Performance</h2>
                <p class="text-sm text-gray-600 mt-1">Revenue across all locations</p>
            </div>
            <div class="flex space-x-2">
                <span class="px-3 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full">
                    {{ $transferStats['pending'] }} Pending Transfers
                </span>
                <span class="px-3 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                    {{ $transferStats['in_transit'] }} In Transit
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                <p class="text-sm font-medium text-gray-600 mb-2">Today's Sales</p>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($salesStats['today'], 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">RWF</p>
                <p class="text-xs text-gray-600 mt-2">{{ $salesStats['total_count_today'] }} transactions</p>
            </div>

            <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                <p class="text-sm font-medium text-gray-600 mb-2">This Week</p>
                <p class="text-3xl font-bold text-blue-900">{{ number_format($salesStats['this_week'], 0) }}</p>
                <p class="text-xs text-blue-600 mt-1">RWF</p>
                <p class="text-xs text-gray-600 mt-2">7 days total</p>
            </div>

            <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg border border-green-200">
                <p class="text-sm font-medium text-gray-600 mb-2">This Month</p>
                <p class="text-3xl font-bold text-green-900">{{ number_format($salesStats['this_month'], 0) }}</p>
                <p class="text-xs text-green-600 mt-1">RWF</p>
                <p class="text-xs text-gray-600 mt-2">{{ now()->format('F') }}</p>
            </div>

            <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg border border-purple-200">
                <p class="text-sm font-medium text-gray-600 mb-2">Daily Average</p>
                <p class="text-3xl font-bold text-purple-900">{{ $salesStats['total_count_today'] > 0 ? number_format($salesStats['today'] / $salesStats['total_count_today'], 0) : 0 }}</p>
                <p class="text-xs text-purple-600 mt-1">RWF per sale</p>
                <p class="text-xs text-gray-600 mt-2">Average ticket</p>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Performing Shops -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Top Performing Shops</h3>
                <span class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">This Month</span>
            </div>
            <div class="space-y-4">
                @forelse($topShops as $index => $shop)
                    <div class="flex items-center p-4 bg-gradient-to-r from-gray-50 to-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full font-bold text-white
                            {{ $index === 0 ? 'bg-gradient-to-br from-yellow-400 to-yellow-500' : '' }}
                            {{ $index === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-400' : '' }}
                            {{ $index === 2 ? 'bg-gradient-to-br from-orange-400 to-orange-500' : '' }}
                            {{ $index > 2 ? 'bg-gradient-to-br from-blue-400 to-blue-500' : '' }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 ml-4">
                            <p class="font-semibold text-gray-900">{{ $shop->name }}</p>
                            <p class="text-sm text-gray-600">{{ $shop->sales_count }} sales completed</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-green-600">{{ number_format($shop->total_sales, 0) }}</p>
                            <p class="text-xs text-gray-500">RWF</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No sales data available yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Critical Alerts -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Critical Alerts</h3>
                @if($criticalAlerts->count() > 0)
                    <span class="px-3 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full">{{ $criticalAlerts->count() }} Active</span>
                @endif
            </div>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($criticalAlerts as $alert)
                    <div class="flex items-start p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $alert->title }}</p>
                            <p class="mt-1 text-xs text-gray-700">{{ $alert->message }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $alert->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">All systems operating normally</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Transfers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Recent Transfers</h3>
                <a href="{{ route('owner.reports.transfers') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All →</a>
            </div>
            <div class="space-y-3">
                @forelse($recentTransfers as $transfer)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $transfer->fromWarehouse->name }} → {{ $transfer->toShop->name }}
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ $transfer->items->count() }} items • {{ $transfer->requested_at->format('M d, Y') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            @if($transfer->status->value === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($transfer->status->value === 'in_transit') bg-blue-100 text-blue-800
                            @elseif($transfer->status->value === 'received') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($transfer->status->value) }}
                        </span>
                    </div>
                @empty
                    <p class="text-center text-sm text-gray-500 py-8">No recent transfers</p>
                @endforelse
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Low Stock Products</h3>
                @if($lowStockProducts->count() > 0)
                    <span class="px-3 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-full">{{ $lowStockProducts->count() }} Items</span>
                @endif
            </div>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between p-3 bg-orange-50 border-l-4 border-orange-500 rounded-r-lg">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-xs text-gray-600">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-orange-600">Low Stock</p>
                            <p class="text-xs text-gray-500">Min: {{ $product->low_stock_threshold }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">All products are well-stocked</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.owner>
