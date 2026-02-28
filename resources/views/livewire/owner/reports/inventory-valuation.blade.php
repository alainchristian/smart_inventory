<div>
    <!-- Page Header with Filters -->
    <div class="mb-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Inventory Valuation</h1>
                <p class="text-gray-500 text-xs mt-0.5">Stock value, turnover, and aging analysis</p>
            </div>

            <div class="flex gap-2">
                <!-- Location Filter -->
                <select wire:model.live="locationFilter"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Locations</option>
                    <optgroup label="Warehouses">
                        @foreach($this->warehouses as $warehouse)
                            <option value="warehouse:{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Shops">
                        @foreach($this->shops as $shop)
                            <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
                        @endforeach
                    </optgroup>
                    <option value="warehouses">All Warehouses</option>
                    <option value="shops">All Shops</option>
                </select>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <!-- Purchase Value -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Purchase Value</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">
                RWF {{ number_format($this->inventoryKpis['purchase_value'] / 100, 0) }}
            </p>
            <p class="text-xs opacity-75 mt-2">Total inventory cost</p>
        </div>

        <!-- Retail Value -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Retail Value</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">
                RWF {{ number_format($this->inventoryKpis['retail_value'] / 100, 0) }}
            </p>
            <p class="text-xs opacity-75 mt-2">Total selling value</p>
        </div>

        <!-- Potential Profit -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Potential Profit</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">
                RWF {{ number_format($this->inventoryKpis['potential_profit'] / 100, 0) }}
            </p>
            <p class="text-xs opacity-75 mt-2">Profit if all sold</p>
        </div>

        <!-- Turnover Rate -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Turnover Rate</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">{{ number_format($this->inventoryKpis['turnover_rate'], 2) }}x</p>
            <p class="text-xs opacity-75 mt-2">Times per year</p>
        </div>
    </div>

    <!-- Inventory Distribution Chart -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Inventory Distribution by Location</h2>
        <div id="inventoryDistributionChart" style="height: 350px;"></div>
    </div>

    <!-- Two Column: Aging Analysis & Expiring Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Aging Analysis -->
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Stock Aging Analysis</h2>
            <div id="agingAnalysisChart" style="height: 300px;"></div>
        </div>

        <!-- Expiring Stock -->
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Expiring Stock (Next 30 Days)</h2>
            <div class="overflow-y-auto" style="max-height: 300px;">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-gray-700 font-semibold">Product</th>
                            <th class="px-3 py-2 text-center text-gray-700 font-semibold">Expiry Date</th>
                            <th class="px-3 py-2 text-right text-gray-700 font-semibold">Quantity</th>
                            <th class="px-3 py-2 text-right text-gray-700 font-semibold">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($this->expiringStock as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-gray-900 font-medium">{{ $item['product_name'] }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if(now()->diffInDays($item['expiry_date']) <= 7) bg-red-100 text-red-800
                                        @elseif(now()->diffInDays($item['expiry_date']) <= 14) bg-amber-100 text-amber-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ \Carbon\Carbon::parse($item['expiry_date'])->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right text-gray-600">{{ number_format($item['items_count']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-900 font-semibold">
                                    RWF {{ number_format($item['value'] / 100, 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-gray-500">No stock expiring soon</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stock Health Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
        <!-- Low Stock -->
        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Low Stock Products</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $this->stockHealth['low_stock_count'] }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Products below threshold</p>
        </div>

        <!-- Dead Stock -->
        <div class="bg-white rounded-lg p-4 shadow-sm border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Dead Stock Products</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $this->stockHealth['dead_stock_count'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">No sales in last 90 days</p>
        </div>
    </div>

    <!-- Top Products by Value -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Top 20 Products by Inventory Value</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-gray-700 font-semibold">#</th>
                        <th class="px-3 py-2 text-left text-gray-700 font-semibold">Product</th>
                        <th class="px-3 py-2 text-left text-gray-700 font-semibold">Location</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Quantity</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Purchase Value</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Retail Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->topProductsByValue as $index => $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-3 py-2 text-gray-900 font-medium">{{ $product['product_name'] }}</td>
                            <td class="px-3 py-2 text-gray-600">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $product['location_type'] == 'warehouse' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst(is_string($product['location_type']) ? $product['location_type'] : $product['location_type']->value) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ number_format($product['items_count']) }}</td>
                            <td class="px-3 py-2 text-right text-gray-900 font-semibold">
                                RWF {{ number_format($product['purchase_value'] / 100, 0) }}
                            </td>
                            <td class="px-3 py-2 text-right text-green-600 font-semibold">
                                RWF {{ number_format($product['retail_value'] / 100, 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-gray-500">No inventory data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inventory Distribution Chart
            const inventoryData = @json($this->inventoryByLocation);
            const allLocations = [...inventoryData.warehouses, ...inventoryData.shops];

            const inventoryDistributionChart = new ApexCharts(document.querySelector("#inventoryDistributionChart"), {
                series: [{
                    name: 'Inventory Value',
                    data: allLocations.map(item => (item.value / 100).toFixed(2))
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        distributed: true
                    }
                },
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#84cc16'],
                xaxis: {
                    categories: allLocations.map(item => item.location_name)
                },
                yaxis: {
                    labels: {
                        formatter: function (val) {
                            return 'RWF ' + val.toFixed(0);
                        }
                    }
                },
                legend: { show: false }
            });
            inventoryDistributionChart.render();

            // Aging Analysis Chart
            const agingData = @json($this->agingAnalysis);
            const agingAnalysisChart = new ApexCharts(document.querySelector("#agingAnalysisChart"), {
                series: agingData.map(item => item.value / 100),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: agingData.map(item => item.age_bracket),
                colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return 'RWF ' + val.toFixed(0);
                        }
                    }
                }
            });
            agingAnalysisChart.render();
        });
    </script>
    @endpush
</div>
