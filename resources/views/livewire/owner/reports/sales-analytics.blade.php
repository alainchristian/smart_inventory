<div>
    <!-- Page Header with Filters -->
    <div class="mb-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Sales Analytics</h1>
                <p class="text-gray-500 text-xs mt-0.5">Revenue insights and sales performance metrics</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <!-- Location Filter -->
                <select wire:model.live="locationFilter"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Locations</option>
                    @foreach($this->shops as $shop)
                        <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>

                <!-- Date Range Filters -->
                <div class="flex gap-2">
                    <button wire:click="setDateRange('today')"
                            class="px-3 py-2 text-xs {{ $dateFrom === now()->format('Y-m-d') && $dateTo === now()->format('Y-m-d') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg hover:bg-blue-700 hover:text-white transition-colors">
                        Today
                    </button>
                    <button wire:click="setDateRange('week')"
                            class="px-3 py-2 text-xs {{ $dateFrom === now()->subDays(7)->format('Y-m-d') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg hover:bg-blue-700 hover:text-white transition-colors">
                        Week
                    </button>
                    <button wire:click="setDateRange('month')"
                            class="px-3 py-2 text-xs {{ $dateFrom === now()->subDays(30)->format('Y-m-d') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg hover:bg-blue-700 hover:text-white transition-colors">
                        Month
                    </button>
                </div>

                <!-- Custom Date Range -->
                <div class="flex gap-2">
                    <input type="date" wire:model.live="dateFrom"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <input type="date" wire:model.live="dateTo"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <!-- Total Revenue -->
        <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Total Revenue</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">
                RWF {{ number_format($this->revenueKpis['total_revenue'] / 100, 0) }}
            </p>
            <div class="flex items-center mt-2">
                @if($this->revenueKpis['growth_percentage'] >= 0)
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span class="text-xs font-semibold">+{{ number_format($this->revenueKpis['growth_percentage'], 1) }}%</span>
                @else
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                    <span class="text-xs font-semibold">{{ number_format($this->revenueKpis['growth_percentage'], 1) }}%</span>
                @endif
                <span class="text-xs opacity-75 ml-1">vs previous period</span>
            </div>
        </div>

        <!-- Transactions Count -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Transactions</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">{{ number_format($this->revenueKpis['transactions_count']) }}</p>
            <p class="text-xs opacity-75 mt-2">Total sales transactions</p>
        </div>

        <!-- Average Transaction Value -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Avg Transaction</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">
                RWF {{ number_format($this->revenueKpis['avg_transaction_value'] / 100, 0) }}
            </p>
            <p class="text-xs opacity-75 mt-2">Per transaction average</p>
        </div>

        <!-- Growth Rate -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Growth Rate</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">
                {{ $this->revenueKpis['growth_percentage'] >= 0 ? '+' : '' }}{{ number_format($this->revenueKpis['growth_percentage'], 1) }}%
            </p>
            <p class="text-xs opacity-75 mt-2">Compared to previous period</p>
        </div>
    </div>

    <!-- Revenue Trend Chart -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Revenue Trend</h2>
        <div id="revenueTrendChart" style="height: 300px;"></div>
    </div>

    <!-- Two Column: Payment Methods & Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Payment Methods Breakdown -->
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Payment Methods</h2>
            <div id="paymentMethodsChart" style="height: 300px;"></div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Top 20 Products by Revenue</h2>
            <div class="overflow-y-auto" style="max-height: 300px;">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-gray-700 font-semibold">#</th>
                            <th class="px-3 py-2 text-left text-gray-700 font-semibold">Product</th>
                            <th class="px-3 py-2 text-right text-gray-700 font-semibold">Qty Sold</th>
                            <th class="px-3 py-2 text-right text-gray-700 font-semibold">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($this->topProducts as $index => $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-gray-600">{{ $index + 1 }}</td>
                                <td class="px-3 py-2 text-gray-900 font-medium">{{ $product['product_name'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-600">{{ number_format($product['quantity_sold']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-900 font-semibold">
                                    RWF {{ number_format($product['revenue'] / 100, 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-gray-500">No product data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Shop Performance Comparison -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Shop Performance Comparison</h2>
        <div id="shopPerformanceChart" style="height: 350px;"></div>
    </div>

    <!-- Sales by Hour Heatmap -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Sales by Hour of Day</h2>
        <div id="salesByHourChart" style="height: 300px;"></div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Revenue Trend Chart
            const revenueTrendData = @json($this->revenueTrend);
            const revenueTrendChart = new ApexCharts(document.querySelector("#revenueTrendChart"), {
                series: [{
                    name: 'Revenue',
                    data: revenueTrendData.map(item => ({
                        x: new Date(item.date).getTime(),
                        y: (item.revenue / 100).toFixed(2)
                    }))
                }],
                chart: {
                    type: 'line',
                    height: 300,
                    toolbar: { show: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                colors: ['#ec4899'],
                xaxis: {
                    type: 'datetime',
                    labels: {
                        datetimeFormatter: {
                            day: 'MMM dd'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function (val) {
                            return 'RWF ' + val.toFixed(0);
                        }
                    }
                },
                tooltip: {
                    x: { format: 'MMM dd, yyyy' }
                }
            });
            revenueTrendChart.render();

            // Payment Methods Chart
            const paymentMethodsData = @json($this->paymentMethods);
            const paymentMethodsChart = new ApexCharts(document.querySelector("#paymentMethodsChart"), {
                series: paymentMethodsData.map(item => item.revenue / 100),
                chart: {
                    type: 'pie',
                    height: 300
                },
                labels: paymentMethodsData.map(item => item.method),
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'],
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
            paymentMethodsChart.render();

            // Shop Performance Chart
            const shopPerformanceData = @json($this->shopPerformance);
            const shopPerformanceChart = new ApexCharts(document.querySelector("#shopPerformanceChart"), {
                series: [{
                    name: 'Revenue',
                    data: shopPerformanceData.map(item => (item.revenue / 100).toFixed(2))
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true
                    }
                },
                colors: ['#ec4899', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
                xaxis: {
                    categories: shopPerformanceData.map(item => item.shop_name),
                    labels: {
                        formatter: function (val) {
                            return 'RWF ' + val;
                        }
                    }
                },
                legend: { show: false }
            });
            shopPerformanceChart.render();

            // Sales by Hour Chart
            const salesByHourData = @json($this->salesByHour);
            const salesByHourChart = new ApexCharts(document.querySelector("#salesByHourChart"), {
                series: [{
                    name: 'Transactions',
                    data: salesByHourData.map(item => ({
                        x: item.hour + ':00',
                        y: item.count
                    }))
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: { show: false }
                },
                colors: ['#3b82f6'],
                xaxis: {
                    title: { text: 'Hour of Day' }
                },
                yaxis: {
                    title: { text: 'Number of Transactions' }
                }
            });
            salesByHourChart.render();
        });
    </script>
    @endpush
</div>
