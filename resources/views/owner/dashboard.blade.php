<x-app-layout>
    <!-- Page Header with Functional Date Filter -->
    <div class="mb-4 lg:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-500 text-sm mt-1">Business Overview & Analytics</p>
            </div>
            
            <!-- Working Date Filter -->
            <div x-data="{ 
                filter: 'today',
                showDropdown: false,
                showCustomModal: false,
                fromDate: '{{ now()->format('Y-m-d') }}',
                toDate: '{{ now()->format('Y-m-d') }}',
                displayText: 'Today',
                applyFilter() {
                    if(this.filter === 'custom') {
                        window.location.href = `?filter=${this.filter}&from=${this.fromDate}&to=${this.toDate}`;
                    } else {
                        window.location.href = `?filter=${this.filter}`;
                    }
                }
            }" class="relative w-full sm:w-auto">
                <button @click="showDropdown = !showDropdown"
                        class="w-full sm:w-auto flex items-center justify-between space-x-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm sm:min-w-[180px]">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700 flex-1" x-text="displayText">Today</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="showDropdown" @click.away="showDropdown = false" x-cloak
                     class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50 py-2">
                    <a href="#" @click.prevent="filter = 'today'; displayText = 'Today'; showDropdown = false; applyFilter()" 
                       class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                        <div class="font-medium">Today</div>
                        <div class="text-xs text-gray-500">{{ now()->format('M d, Y') }}</div>
                    </a>
                    <a href="#" @click.prevent="filter = 'week'; displayText = 'This Week'; showDropdown = false; applyFilter()" 
                       class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                        <div class="font-medium">This Week</div>
                        <div class="text-xs text-gray-500">{{ now()->startOfWeek()->format('M d') }} - {{ now()->endOfWeek()->format('M d') }}</div>
                    </a>
                    <a href="#" @click.prevent="filter = 'month'; displayText = 'This Month'; showDropdown = false; applyFilter()" 
                       class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                        <div class="font-medium">This Month</div>
                        <div class="text-xs text-gray-500">{{ now()->format('F Y') }}</div>
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="#" @click.prevent="filter = 'custom'; showDropdown = false; showCustomModal = true" 
                       class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                        <div class="font-medium">Custom Range</div>
                        <div class="text-xs text-gray-500">Choose dates</div>
                    </a>
                </div>

                <!-- Custom Date Range Modal -->
                <div x-show="showCustomModal" x-cloak
                     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div @click.away="showCustomModal = false" class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Select Date Range</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                                <input type="date" x-model="fromDate" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                                <input type="date" x-model="toDate" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div class="flex space-x-3 mt-6">
                            <button @click="showCustomModal = false" 
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button @click="displayText = fromDate + ' - ' + toDate; showCustomModal = false; applyFilter()" 
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                Apply Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics - Compact 3-line cards with single icon -->
    <div class="mb-4 lg:mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
            <!-- Today's Sales -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-pink-500 rounded-xl flex items-center justify-center shadow-lg shadow-pink-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-600 mb-1">Today's Sales</p>
                <div class="flex items-baseline justify-between">
                    <p class="text-2xl font-bold text-gray-900">
                        @if($salesStats['today'] >= 1000000)
                            {{ number_format($salesStats['today'] / 1000000, 1) }}M
                        @elseif($salesStats['today'] >= 1000)
                            {{ number_format($salesStats['today'] / 1000, 0) }}K
                        @else
                            {{ number_format($salesStats['today'], 0) }}
                        @endif
                        <span class="text-xs text-gray-500 font-normal">RWF</span>
                    </p>
                    <span class="text-xs text-pink-600 font-medium">{{ $salesStats['total_count_today'] }} transactions</span>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-600 mb-1">Active Users</p>
                <div class="flex items-baseline justify-between">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                    <span class="text-xs text-blue-600 font-medium">System users</span>
                </div>
            </div>

            <!-- Warehouses -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-500 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-600 mb-1">Warehouses</p>
                <div class="flex items-baseline justify-between">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_warehouses'] }}</p>
                    <span class="text-xs text-green-600 font-medium">{{ number_format($stats['total_items_in_stock']) }} items</span>
                </div>
            </div>

            <!-- Retail Shops -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-500 rounded-xl flex items-center justify-center shadow-lg shadow-purple-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-600 mb-1">Retail Shops</p>
                <div class="flex items-baseline justify-between">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_shops'] }}</p>
                    <span class="text-xs text-purple-600 font-medium">Active locations</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="mb-4 lg:mb-6">
        <h2 class="text-base lg:text-lg font-semibold text-gray-900 mb-3 lg:mb-4">Financial Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 lg:gap-4">
            <!-- Inventory Value -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <span class="px-2 py-1 bg-blue-50 text-blue-600 text-xs font-semibold rounded-full">Cost</span>
                </div>
                <p class="text-sm text-gray-600 mb-1">Inventory Value</p>
                <p class="text-2xl font-bold text-gray-900 mb-3">
                    @if($stats['inventory_value'] >= 1000000)
                        {{ number_format($stats['inventory_value'] / 1000000, 2) }}M
                    @else
                        {{ number_format($stats['inventory_value'] / 1000, 0) }}K
                    @endif
                    <span class="text-sm text-gray-500 font-normal">RWF</span>
                </p>
                <!-- Mini Bar Chart -->
                <div class="flex items-end justify-between h-10 space-x-1">
                    @for($i = 0; $i < 8; $i++)
                        <div class="flex-1 bg-gradient-to-t from-blue-400 to-blue-500 rounded-t" 
                             style="height: {{ rand(30, 100) }}%"></div>
                    @endfor
                </div>
            </div>

            <!-- Retail Value -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="px-2 py-1 bg-green-50 text-green-600 text-xs font-semibold rounded-full">Selling</span>
                </div>
                <p class="text-sm text-gray-600 mb-1">Retail Value</p>
                <p class="text-2xl font-bold text-gray-900 mb-3">
                    @if($stats['retail_value'] >= 1000000)
                        {{ number_format($stats['retail_value'] / 1000000, 2) }}M
                    @else
                        {{ number_format($stats['retail_value'] / 1000, 0) }}K
                    @endif
                    <span class="text-sm text-gray-500 font-normal">RWF</span>
                </p>
                <!-- Progress Bar -->
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-gradient-to-r from-green-400 to-green-500 h-2 rounded-full" style="width: 100%"></div>
                </div>
            </div>

            <!-- Expected Profit -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <span class="px-2 py-1 bg-amber-50 text-amber-600 text-xs font-semibold rounded-full">
                        {{ $stats['inventory_value'] > 0 ? number_format(($stats['potential_profit'] / $stats['inventory_value']) * 100, 1) : 0 }}%
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-1">Expected Profit</p>
                <p class="text-2xl font-bold text-green-600 mb-3">
                    @if($stats['potential_profit'] >= 1000000)
                        {{ number_format($stats['potential_profit'] / 1000000, 2) }}M
                    @else
                        {{ number_format($stats['potential_profit'] / 1000, 0) }}K
                    @endif
                    <span class="text-sm text-gray-500 font-normal">RWF</span>
                </p>
                <!-- Mini Donut -->
                <div class="flex items-center justify-center">
                    @php
                        $profitMargin = $stats['inventory_value'] > 0 ? ($stats['potential_profit'] / $stats['inventory_value']) * 100 : 0;
                    @endphp
                    <svg class="w-16 h-16 transform -rotate-90">
                        <circle cx="32" cy="32" r="28" fill="none" stroke="#f3f4f6" stroke-width="6"/>
                        <circle cx="32" cy="32" r="28" fill="none" stroke="#10b981" stroke-width="6"
                                stroke-dasharray="{{ $profitMargin * 1.76 }} 176"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales & Top Shops -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-4 lg:mb-6">
        <!-- Sales Performance -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 border border-gray-100">
            <h3 class="text-base lg:text-lg font-bold text-gray-900 mb-4 lg:mb-6">Sales Performance</h3>
            <div class="h-40 lg:h-48 flex items-end justify-between space-x-2 lg:space-x-4 mb-4">
                @php
                    $maxValue = max($salesStats['today'], $salesStats['this_week'], $salesStats['this_month']);
                @endphp
                <div class="flex-1 flex flex-col items-center group">
                    <div class="w-full bg-gradient-to-t from-pink-400 to-pink-500 rounded-t-lg" 
                         style="height: {{ $maxValue > 0 ? ($salesStats['today'] / $maxValue) * 100 : 0 }}%"></div>
                    <span class="text-xs text-gray-600 mt-3 font-medium">Today</span>
                    <span class="text-xs text-pink-600 font-semibold">{{ number_format($salesStats['today'] / 1000, 0) }}K</span>
                </div>
                <div class="flex-1 flex flex-col items-center group">
                    <div class="w-full bg-gradient-to-t from-blue-400 to-blue-500 rounded-t-lg" 
                         style="height: {{ $maxValue > 0 ? ($salesStats['this_week'] / $maxValue) * 100 : 0 }}%"></div>
                    <span class="text-xs text-gray-600 mt-3 font-medium">Week</span>
                    <span class="text-xs text-blue-600 font-semibold">{{ number_format($salesStats['this_week'] / 1000, 0) }}K</span>
                </div>
                <div class="flex-1 flex flex-col items-center group">
                    <div class="w-full bg-gradient-to-t from-green-400 to-green-500 rounded-t-lg" style="height: 100%"></div>
                    <span class="text-xs text-gray-600 mt-3 font-medium">Month</span>
                    <span class="text-xs text-green-600 font-semibold">{{ number_format($salesStats['this_month'] / 1000, 0) }}K</span>
                </div>
            </div>
        </div>

        <!-- Top Shops -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 border border-gray-100">
            <h3 class="text-base lg:text-lg font-bold text-gray-900 mb-4 lg:mb-6">Top Performing Shops</h3>
            <div class="space-y-3 lg:space-y-4">
                @forelse($topShops->take(5) as $index => $shop)
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg font-bold text-white text-sm flex items-center justify-center
                            {{ $index === 0 ? 'bg-gradient-to-br from-yellow-400 to-yellow-500' : '' }}
                            {{ $index === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-400' : '' }}
                            {{ $index === 2 ? 'bg-gradient-to-br from-orange-400 to-orange-500' : '' }}
                            {{ $index > 2 ? 'bg-gradient-to-br from-blue-400 to-blue-500' : '' }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 truncate text-sm">{{ $shop->name }}</p>
                            <div class="flex items-center mt-1">
                                <div class="flex-1 bg-gray-200 rounded-full h-1.5 mr-2">
                                    @php
                                        $maxShopSales = $topShops->max('total_sales');
                                        $shopPercent = $maxShopSales > 0 ? ($shop->total_sales / $maxShopSales) * 100 : 0;
                                    @endphp
                                    <div class="bg-gradient-to-r from-green-400 to-green-500 h-1.5 rounded-full" 
                                         style="width: {{ $shopPercent }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ $shop->sales_count }}</span>
                            </div>
                        </div>
                        <p class="text-base font-bold text-green-600">{{ number_format($shop->total_sales / 1000, 0) }}K</p>
                    </div>
                @empty
                    <p class="text-center text-gray-500 text-sm py-8">No sales data</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Transfers & Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-4 lg:mb-6">
        <!-- Transfer Status -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 border border-gray-100">
            <h3 class="text-base lg:text-lg font-bold text-gray-900 mb-4 lg:mb-6">Transfer Status</h3>
            <div class="flex items-center justify-center mb-4 lg:mb-6">
                @php
                    $total = $transferStats['pending'] + $transferStats['in_transit'] + $transferStats['with_discrepancies'];
                    $pendingPercent = $total > 0 ? ($transferStats['pending'] / $total) * 100 : 33;
                    $transitPercent = $total > 0 ? ($transferStats['in_transit'] / $total) * 100 : 33;
                @endphp
                <svg class="w-28 h-28 lg:w-32 lg:h-32 transform -rotate-90">
                    <circle cx="64" cy="64" r="52" fill="none" stroke="#f59e0b" stroke-width="24" 
                            stroke-dasharray="{{ $pendingPercent * 3.26 }} 326.56"/>
                    <circle cx="64" cy="64" r="52" fill="none" stroke="#3b82f6" stroke-width="24" 
                            stroke-dasharray="{{ $transitPercent * 3.26 }} 326.56" 
                            stroke-dashoffset="-{{ $pendingPercent * 3.26 }}"/>
                    <circle cx="64" cy="64" r="52" fill="none" stroke="#ef4444" stroke-width="24" 
                            stroke-dasharray="{{ (100 - $pendingPercent - $transitPercent) * 3.26 }} 326.56" 
                            stroke-dashoffset="-{{ ($pendingPercent + $transitPercent) * 3.26 }}"/>
                </svg>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                        <span class="text-sm text-gray-700">Pending</span>
                    </div>
                    <span class="text-sm font-bold text-gray-900">{{ $transferStats['pending'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                        <span class="text-sm text-gray-700">In Transit</span>
                    </div>
                    <span class="text-sm font-bold text-gray-900">{{ $transferStats['in_transit'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <span class="text-sm text-gray-700">Discrepancies</span>
                    </div>
                    <span class="text-sm font-bold text-gray-900">{{ $transferStats['with_discrepancies'] }}</span>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 border border-gray-100">
            <h3 class="text-base lg:text-lg font-bold text-gray-900 mb-4 lg:mb-6">Critical Alerts</h3>
            <div class="space-y-2 lg:space-y-3 max-h-64 overflow-y-auto">
                @forelse($criticalAlerts as $alert)
                    <div class="p-3 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                        <p class="text-sm font-semibold text-gray-900">{{ $alert->title }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ $alert->message }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-900">All Systems Operational</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Low Stock (if any) -->
    @if($lowStockProducts->count() > 0)
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 border border-gray-100 mb-4 lg:mb-6">
            <h3 class="text-base lg:text-lg font-bold text-gray-900 mb-4 lg:mb-6">Low Stock Alerts</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-4">
                @foreach($lowStockProducts->take(6) as $product)
                    @php
                        $currentStock = $product->boxes->sum('items_remaining');
                        $percentage = $product->low_stock_threshold > 0 ? ($currentStock / $product->low_stock_threshold) * 100 : 0;
                    @endphp
                    <div class="p-4 border border-gray-200 rounded-lg hover:border-red-300 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</p>
                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full
                                {{ $percentage < 25 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $percentage < 25 ? 'Critical' : 'Low' }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $percentage < 25 ? 'bg-red-500' : 'bg-amber-500' }}" 
                                 style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">{{ number_format($currentStock) }} / {{ number_format($product->low_stock_threshold) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Transfers -->
    <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 border border-gray-100">
        <h3 class="text-base lg:text-lg font-bold text-gray-900 mb-4 lg:mb-6">Recent Transfers</h3>
        <div class="overflow-x-auto -mx-4 lg:mx-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Transfer #</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase hidden sm:table-cell">From</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase hidden sm:table-cell">To</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase hidden md:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentTransfers as $transfer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 lg:px-6 py-3 lg:py-4 text-xs lg:text-sm font-medium text-gray-900">{{ $transfer->transfer_number }}</td>
                            <td class="px-4 lg:px-6 py-3 lg:py-4 text-xs lg:text-sm text-gray-600 hidden sm:table-cell">{{ $transfer->fromWarehouse->name }}</td>
                            <td class="px-4 lg:px-6 py-3 lg:py-4 text-xs lg:text-sm text-gray-600 hidden sm:table-cell">{{ $transfer->toShop->name }}</td>
                            <td class="px-4 lg:px-6 py-3 lg:py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'approved' => 'bg-blue-100 text-blue-700',
                                        'in_transit' => 'bg-purple-100 text-purple-700',
                                        'received' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                    ];
                                    $statusColor = $statusColors[$transfer->status->value] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="inline-flex px-2 lg:px-2.5 py-0.5 lg:py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                    {{ $transfer->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 lg:px-6 py-3 lg:py-4 text-xs lg:text-sm text-gray-600 hidden md:table-cell">{{ $transfer->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 lg:px-6 py-8 lg:py-12 text-center text-xs lg:text-sm text-gray-500">No recent transfers</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>