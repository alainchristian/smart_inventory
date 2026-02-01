<x-app-layout>
    <!-- Page Header with Enhanced Date Filter -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Owner Dashboard</h1>
                <p class="text-gray-500 text-xs mt-0.5">Real-time business metrics and insights</p>
            </div>

            <!-- Enhanced Date Filter with Current Selection Display -->
            <div x-data="{
                filter: '{{ $filter ?? 'today' }}',
                showDropdown: false,
                showCustomModal: false,
                fromDate: '{{ $fromDate ?? now()->format('Y-m-d') }}',
                toDate: '{{ $toDate ?? now()->format('Y-m-d') }}',
                displayText: '{{ ucfirst($filter ?? 'today') }}',
                applyFilter() {
                    if(this.filter === 'custom') {
                        window.location.href = `?filter=${this.filter}&from=${this.fromDate}&to=${this.toDate}`;
                    } else {
                        window.location.href = `?filter=${this.filter}`;
                    }
                }
            }" class="relative w-full sm:w-auto">
                <button @click="showDropdown = !showDropdown"
                        class="w-full sm:w-auto flex items-center justify-between space-x-2 px-3 py-2 bg-white border border-gray-300 rounded-lg hover:border-gray-400 transition-colors shadow-sm sm:min-w-[160px]">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-xs font-bold text-gray-700 flex-1 text-left" x-text="displayText">{{ ucfirst($filter ?? 'Today') }}</span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="showDropdown" @click.away="showDropdown = false" x-cloak
                     class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50 py-2">
                    <a href="#" @click.prevent="filter = 'today'; displayText = 'Today'; showDropdown = false; applyFilter()"
                       class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                        <div class="font-semibold">Today</div>
                        <div class="text-xs text-gray-500">{{ now()->format('M d, Y') }}</div>
                    </a>
                    <a href="#" @click.prevent="filter = 'week'; displayText = 'This Week'; showDropdown = false; applyFilter()"
                       class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                        <div class="font-semibold">This Week</div>
                        <div class="text-xs text-gray-500">{{ now()->startOfWeek()->format('M d') }} - {{ now()->endOfWeek()->format('M d') }}</div>
                    </a>
                    <a href="#" @click.prevent="filter = 'month'; displayText = 'This Month'; showDropdown = false; applyFilter()"
                       class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                        <div class="font-semibold">This Month</div>
                        <div class="text-xs text-gray-500">{{ now()->format('F Y') }}</div>
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="#" @click.prevent="filter = 'custom'; showDropdown = false; showCustomModal = true"
                       class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                        <div class="font-semibold">Custom Range</div>
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
                            <button @click="displayText = 'Custom Range'; showCustomModal = false; applyFilter()"
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                Apply Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority KPIs with Trend Indicators -->
    <div class="mb-4 lg:mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Today's Sales (Priority #1) -->
            <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-pink-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Sales</span>
                    </div>
                    @if(isset($salesStats['trend_percentage']))
                    <span class="text-xs font-bold {{ $salesStats['trend_percentage'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $salesStats['trend_percentage'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($salesStats['trend_percentage']), 1) }}%
                    </span>
                    @endif
                </div>
                <p class="text-2xl font-bold text-gray-900">
                    @if($salesStats['today'] >= 1000000)
                        {{ number_format($salesStats['today'] / 1000000, 1) }}M
                    @elseif($salesStats['today'] >= 1000)
                        {{ number_format($salesStats['today'] / 1000, 0) }}K
                    @else
                        {{ number_format($salesStats['today'], 0) }}
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $salesStats['total_count_today'] }} transactions • RWF</p>
            </div>

            <!-- Expected Profit (Priority #2) -->
            <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Profit</span>
                    </div>
                    <span class="text-xs font-bold text-green-600">
                        {{ $stats['inventory_value'] > 0 ? number_format(($stats['potential_profit'] / $stats['inventory_value']) * 100, 1) : 0 }}%
                    </span>
                </div>
                <p class="text-2xl font-bold text-green-600">
                    @if($stats['potential_profit'] >= 1000000)
                        {{ number_format($stats['potential_profit'] / 1000000, 2) }}M
                    @else
                        {{ number_format($stats['potential_profit'] / 1000, 0) }}K
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-0.5">Expected margin • RWF</p>
            </div>

            <!-- Combined Inventory Value (Priority #3) -->
            <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Inventory</span>
                    </div>
                    @php
                        $marginPercent = $stats['retail_value'] > 0 ? (($stats['potential_profit'] / $stats['retail_value']) * 100) : 0;
                    @endphp
                    <span class="text-xs font-bold text-gray-900">{{ number_format($marginPercent, 1) }}%</span>
                </div>
                <div class="flex items-baseline space-x-1">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['inventory_value'] / 1000, 0) }}K</p>
                    <p class="text-sm text-gray-500">→</p>
                    <p class="text-lg font-bold text-gray-700">{{ number_format($stats['retail_value'] / 1000, 0) }}K</p>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Cost → Retail • RWF</p>
            </div>

            <!-- Locations Summary (Priority #4) -->
            <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Locations</span>
                    </div>
                </div>
                <div class="flex items-baseline space-x-3">
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_warehouses'] }}</p>
                        <p class="text-xs text-gray-500">Warehouses</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_shops'] }}</p>
                        <p class="text-xs text-gray-500">Shops</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-700">{{ $stats['total_users'] }}</p>
                        <p class="text-xs text-gray-500">Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales & Top Shops -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-4 lg:mb-6">
        <!-- Sales Performance -->
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <h3 class="text-base font-bold text-gray-900 mb-3">Sales Performance</h3>
            <div class="h-32 flex items-end justify-between space-x-3 mb-3">
                @php
                    $maxValue = max($salesStats['today'], $salesStats['this_week'], $salesStats['this_month']);
                @endphp
                <div class="flex-1 flex flex-col items-center group">
                    <div class="w-full bg-gradient-to-t from-pink-400 to-pink-500 rounded-t-lg transition-all hover:from-pink-500 hover:to-pink-600"
                         style="height: {{ $maxValue > 0 ? ($salesStats['today'] / $maxValue) * 100 : 0 }}%"></div>
                    <span class="text-xs text-gray-600 mt-3 font-semibold">Today</span>
                    <span class="text-xs text-pink-600 font-bold">{{ number_format($salesStats['today'] / 1000, 0) }}K</span>
                </div>
                <div class="flex-1 flex flex-col items-center group">
                    <div class="w-full bg-gradient-to-t from-blue-400 to-blue-500 rounded-t-lg transition-all hover:from-blue-500 hover:to-blue-600"
                         style="height: {{ $maxValue > 0 ? ($salesStats['this_week'] / $maxValue) * 100 : 0 }}%"></div>
                    <span class="text-xs text-gray-600 mt-3 font-semibold">Week</span>
                    <span class="text-xs text-blue-600 font-bold">{{ number_format($salesStats['this_week'] / 1000, 0) }}K</span>
                </div>
                <div class="flex-1 flex flex-col items-center group">
                    <div class="w-full bg-gradient-to-t from-green-400 to-green-500 rounded-t-lg transition-all hover:from-green-500 hover:to-green-600" style="height: 100%"></div>
                    <span class="text-xs text-gray-600 mt-3 font-semibold">Month</span>
                    <span class="text-xs text-green-600 font-bold">{{ number_format($salesStats['this_month'] / 1000, 0) }}K</span>
                </div>
            </div>
        </div>

        <!-- Top Shops -->
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <h3 class="text-base font-bold text-gray-900 mb-3">Top Performing Shops</h3>
            <div class="space-y-2.5">
                @forelse($topShops->take(5) as $index => $shop)
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg font-bold text-white text-sm flex items-center justify-center
                            {{ $index === 0 ? 'bg-gradient-to-br from-yellow-400 to-yellow-500 shadow-md' : '' }}
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
                                    <div class="bg-gradient-to-r from-green-400 to-green-500 h-1.5 rounded-full transition-all duration-500"
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
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <h3 class="text-base font-bold text-gray-900 mb-3">Transfer Status</h3>
            <div class="space-y-2">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Pending Approval</p>
                            <p class="text-xs text-gray-500">Awaiting review</p>
                        </div>
                    </div>
                    <span class="text-xl font-bold text-gray-900">{{ $transferStats['pending'] }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">In Transit</p>
                            <p class="text-xs text-gray-500">On the way</p>
                        </div>
                    </div>
                    <span class="text-xl font-bold text-gray-900">{{ $transferStats['in_transit'] }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Discrepancies</p>
                            <p class="text-xs text-gray-500">Needs attention</p>
                        </div>
                    </div>
                    <span class="text-xl font-bold text-gray-900">{{ $transferStats['with_discrepancies'] }}</span>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <h3 class="text-base font-bold text-gray-900 mb-3">Critical Alerts</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($criticalAlerts as $alert)
                    <div class="p-2.5 border border-red-200 rounded-lg hover:border-red-300 transition-colors">
                        <div class="flex items-start justify-between">
                            <p class="text-sm font-semibold text-gray-900 flex-1">{{ $alert->title }}</p>
                            <span class="text-xs text-red-600 font-bold ml-2">!</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">{{ $alert->message }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 border-2 border-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-900">All Systems Operational</p>
                        <p class="text-xs text-gray-500 mt-0.5">No critical issues</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Low Stock (if any) -->
    @if($lowStockProducts->count() > 0)
        <div id="low-stock-section" class="bg-white rounded-lg shadow-sm p-4 border border-gray-200 mb-4 lg:mb-6">
            <h3 class="text-base font-bold text-gray-900 mb-3">Low Stock Alerts</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($lowStockProducts->take(6) as $product)
                    @php
                        $currentStock = $product->boxes->sum('items_remaining');
                        $percentage = $product->low_stock_threshold > 0 ? ($currentStock / $product->low_stock_threshold) * 100 : 0;
                    @endphp
                    <div class="p-3 border {{ $percentage < 25 ? 'border-red-300' : 'border-orange-300' }} rounded-lg hover:shadow-sm transition-shadow">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $product->name }}</p>
                            <span class="text-xs font-bold {{ $percentage < 25 ? 'text-red-600' : 'text-orange-600' }}">
                                {{ $percentage < 25 ? 'CRITICAL' : 'LOW' }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mb-2">
                            <div class="h-1.5 rounded-full {{ $percentage < 25 ? 'bg-red-500' : 'bg-orange-500' }}"
                                 style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                        <p class="text-xs text-gray-600">{{ number_format($currentStock) }} / {{ number_format($product->low_stock_threshold) }} items</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Transfers -->
    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200 mb-4 lg:mb-6">
        <h3 class="text-base font-bold text-gray-900 mb-3">Recent Transfers</h3>
        <div class="overflow-x-auto -mx-4 lg:mx-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 lg:px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Transfer #</th>
                        <th class="px-3 lg:px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase hidden sm:table-cell">From</th>
                        <th class="px-3 lg:px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase hidden sm:table-cell">To</th>
                        <th class="px-3 lg:px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Status</th>
                        <th class="px-3 lg:px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase hidden md:table-cell">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentTransfers as $transfer)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 lg:px-4 py-2.5 text-xs font-bold text-gray-900">{{ $transfer->transfer_number }}</td>
                            <td class="px-3 lg:px-4 py-2.5 text-xs text-gray-600 hidden sm:table-cell">{{ $transfer->fromWarehouse->name }}</td>
                            <td class="px-3 lg:px-4 py-2.5 text-xs text-gray-600 hidden sm:table-cell">{{ $transfer->toShop->name }}</td>
                            <td class="px-3 lg:px-4 py-2.5">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-700 border-amber-300',
                                        'approved' => 'bg-blue-100 text-blue-700 border-blue-300',
                                        'in_transit' => 'bg-purple-100 text-purple-700 border-purple-300',
                                        'received' => 'bg-green-100 text-green-700 border-green-300',
                                        'rejected' => 'bg-red-100 text-red-700 border-red-300',
                                    ];
                                    $statusColor = $statusColors[$transfer->status->value] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold border {{ $statusColor }}">
                                    {{ $transfer->status->label() }}
                                </span>
                            </td>
                            <td class="px-3 lg:px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">{{ $transfer->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 lg:px-4 py-8 text-center text-xs text-gray-500">No recent transfers</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Health Footer -->
    <div class="bg-white rounded-lg p-2.5 border border-gray-200">
        <div class="flex items-center justify-between flex-wrap gap-2 text-xs text-gray-600">
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-1.5">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="font-semibold text-green-600">{{ ucfirst($systemHealth['status']) }}</span>
                </div>
                <span class="text-gray-400">•</span>
                <span>Updated {{ $systemHealth['last_sync']->diffForHumans() }}</span>
            </div>
            <button onclick="window.location.reload()" class="inline-flex items-center space-x-1 px-2 py-1 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition-colors text-xs font-medium">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Floating Action Button (FAB) with Quick Actions -->
    <x-floating-action-button>
        <x-slot name="actions">
            <a href="{{ route('owner.products.create') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition-colors">Add Product</p>
                    <p class="text-xs text-gray-500">Create new product</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('owner.warehouses.index') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-green-50 hover:to-green-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-green-700 transition-colors">Manage Stock</p>
                    <p class="text-xs text-gray-500">Warehouses & inventory</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('owner.reports.inventory') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-purple-700 transition-colors">View Reports</p>
                    <p class="text-xs text-gray-500">Analytics & insights</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            @if($lowStockProducts->count() > 0)
            <a href="#low-stock-section"
               class="flex items-center space-x-3 px-5 py-3 bg-gradient-to-r from-orange-50 to-red-50 hover:from-orange-100 hover:to-red-100 transition-all duration-200 group">
                <div class="w-11 h-11 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md animate-pulse">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-orange-900 group-hover:text-red-700 transition-colors">Low Stock Alert</p>
                    <p class="text-xs text-orange-700 font-semibold">{{ $lowStockProducts->count() }} products need attention</p>
                </div>
                <svg class="w-5 h-5 text-orange-600 group-hover:text-red-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            @endif
        </x-slot>
    </x-floating-action-button>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>
