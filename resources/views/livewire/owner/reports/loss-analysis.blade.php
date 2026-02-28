<div>
    <!-- Page Header with Filters -->
    <div class="mb-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Loss Analysis</h1>
                <p class="text-gray-500 text-xs mt-0.5">Returns and damaged goods tracking</p>
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
                            class="px-3 py-2 text-xs {{ $dateFrom === now()->format('Y-m-d') && $dateTo === now()->format('Y-m-d') ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg hover:bg-red-700 hover:text-white transition-colors">
                        Today
                    </button>
                    <button wire:click="setDateRange('week')"
                            class="px-3 py-2 text-xs {{ $dateFrom === now()->subDays(7)->format('Y-m-d') ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg hover:bg-red-700 hover:text-white transition-colors">
                        Week
                    </button>
                    <button wire:click="setDateRange('month')"
                            class="px-3 py-2 text-xs {{ $dateFrom === now()->subDays(30)->format('Y-m-d') ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg hover:bg-red-700 hover:text-white transition-colors">
                        Month
                    </button>
                </div>

                <!-- Custom Date Range -->
                <div class="flex gap-2">
                    <input type="date" wire:model.live="dateFrom"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <input type="date" wire:model.live="dateTo"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <!-- Total Refunds -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Total Refunds</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">
                RWF {{ number_format($this->lossKpis['total_refunds'] / 100, 0) }}
            </p>
            <p class="text-xs opacity-75 mt-2">Amount refunded to customers</p>
        </div>

        <!-- Return Rate -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Return Rate</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">{{ number_format($this->lossKpis['return_rate'], 2) }}%</p>
            <p class="text-xs opacity-75 mt-2">Returns vs sales</p>
        </div>

        <!-- Returns Count -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Returns Count</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">{{ number_format($this->lossKpis['returns_count']) }}</p>
            <p class="text-xs opacity-75 mt-2">Number of returns</p>
        </div>

        <!-- Damaged Goods Loss -->
        <div class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Damaged Loss</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">
                RWF {{ number_format($this->lossKpis['damaged_loss'] / 100, 0) }}
            </p>
            <p class="text-xs opacity-75 mt-2">Estimated damaged goods loss</p>
        </div>
    </div>

    <!-- Loss Trends Chart -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Loss Trends Over Time</h2>
        <div id="lossTrendChart" style="height: 300px;"></div>
    </div>

    <!-- Two Column: Return Reasons & Disposition Types -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Return Reasons Breakdown -->
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Return Reasons</h2>
            <div id="returnReasonsChart" style="height: 300px;"></div>
        </div>

        <!-- Disposition Types -->
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Damaged Goods Disposition</h2>
            <div id="dispositionChart" style="height: 300px;"></div>
        </div>
    </div>

    <!-- Problem Products -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Problem Products (Top 20 by Loss)</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-gray-700 font-semibold">#</th>
                        <th class="px-3 py-2 text-left text-gray-700 font-semibold">Product</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Returns</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Returned Qty</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Refund Amount</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Damage Count</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Damaged Qty</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Total Loss</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->problemProducts as $index => $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-3 py-2 text-gray-900 font-medium">{{ $product['product_name'] }}</td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ $product['return_count'] }}</td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ number_format($product['returned_quantity']) }}</td>
                            <td class="px-3 py-2 text-right text-red-600">
                                RWF {{ number_format($product['refund_amount'] / 100, 0) }}
                            </td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ $product['damage_count'] }}</td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ number_format($product['damaged_quantity']) }}</td>
                            <td class="px-3 py-2 text-right text-red-700 font-bold">
                                RWF {{ number_format($product['total_loss'] / 100, 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-4 text-center text-gray-500">No loss data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Returns by Location -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Returns by Shop</h2>
        <div id="returnsByLocationChart" style="height: 350px;"></div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Loss Trend Chart
            const lossTrendData = @json($this->lossTrend);
            const lossTrendChart = new ApexCharts(document.querySelector("#lossTrendChart"), {
                series: [{
                    name: 'Refunds',
                    data: lossTrendData.map(item => ({
                        x: new Date(item.date).getTime(),
                        y: (item.refunds / 100).toFixed(2)
                    }))
                }, {
                    name: 'Damaged Loss',
                    data: lossTrendData.map(item => ({
                        x: new Date(item.date).getTime(),
                        y: (item.damaged_loss / 100).toFixed(2)
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
                colors: ['#ef4444', '#f59e0b'],
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
            lossTrendChart.render();

            // Return Reasons Chart
            const returnReasonsData = @json($this->returnReasons);
            const returnReasonsChart = new ApexCharts(document.querySelector("#returnReasonsChart"), {
                series: returnReasonsData.map(item => item.count),
                chart: {
                    type: 'pie',
                    height: 300
                },
                labels: returnReasonsData.map(item => item.reason),
                colors: ['#ef4444', '#f59e0b', '#f97316', '#dc2626', '#ea580c'],
                legend: {
                    position: 'bottom'
                }
            });
            returnReasonsChart.render();

            // Disposition Chart
            const dispositionData = @json($this->dispositionBreakdown);
            const dispositionChart = new ApexCharts(document.querySelector("#dispositionChart"), {
                series: dispositionData.map(item => item.count),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: dispositionData.map(item => item.disposition),
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                legend: {
                    position: 'bottom'
                }
            });
            dispositionChart.render();

            // Returns by Location Chart
            const returnsByLocationData = @json($this->returnsByLocation);
            const returnsByLocationChart = new ApexCharts(document.querySelector("#returnsByLocationChart"), {
                series: [{
                    name: 'Returns Count',
                    data: returnsByLocationData.map(item => item.returns_count)
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
                colors: ['#ef4444', '#f59e0b', '#f97316', '#dc2626', '#ea580c'],
                xaxis: {
                    categories: returnsByLocationData.map(item => item.shop_name)
                },
                legend: { show: false }
            });
            returnsByLocationChart.render();
        });
    </script>
    @endpush
</div>
