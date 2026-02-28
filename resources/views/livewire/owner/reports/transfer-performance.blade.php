<div>
    <!-- Page Header with Filters -->
    <div class="mb-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Transfer Performance</h1>
                <p class="text-gray-500 text-xs mt-0.5">Transfer efficiency and logistics analytics</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <!-- Status Filter -->
                <select wire:model.live="statusFilter"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All Statuses</option>
                    @foreach($this->transferStatuses as $status)
                        <option value="{{ $status->value }}">{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
                    @endforeach
                </select>

                <!-- Date Range Filters -->
                <div class="flex gap-2">
                    <button wire:click="setDateRange('today')"
                            class="px-3 py-2 text-xs {{ $dateFrom === now()->format('Y-m-d') && $dateTo === now()->format('Y-m-d') ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg hover:bg-purple-700 hover:text-white transition-colors">
                        Today
                    </button>
                    <button wire:click="setDateRange('week')"
                            class="px-3 py-2 text-xs {{ $dateFrom === now()->subDays(7)->format('Y-m-d') ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg hover:bg-purple-700 hover:text-white transition-colors">
                        Week
                    </button>
                    <button wire:click="setDateRange('month')"
                            class="px-3 py-2 text-xs {{ $dateFrom === now()->subDays(30)->format('Y-m-d') ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 border border-gray-300' }} rounded-lg hover:bg-purple-700 hover:text-white transition-colors">
                        Month
                    </button>
                </div>

                <!-- Custom Date Range -->
                <div class="flex gap-2">
                    <input type="date" wire:model.live="dateFrom"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <input type="date" wire:model.live="dateTo"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <!-- Total Transfers -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Total Transfers</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">{{ number_format($this->transferKpis['total_transfers']) }}</p>
            <p class="text-xs opacity-75 mt-2">Total transfers in period</p>
        </div>

        <!-- Average Completion Time -->
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Avg Completion</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">{{ number_format($this->transferKpis['avg_completion_hours'], 1) }}h</p>
            <p class="text-xs opacity-75 mt-2">Average hours to complete</p>
        </div>

        <!-- Discrepancy Rate -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">Discrepancy Rate</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">{{ number_format($this->transferKpis['discrepancy_rate'], 2) }}%</p>
            <p class="text-xs opacity-75 mt-2">Transfers with discrepancies</p>
        </div>

        <!-- In-Transit Count -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase opacity-90">In Transit</span>
                <svg class="w-6 h-6 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold">{{ number_format($this->transferKpis['in_transit_count']) }}</p>
            <p class="text-xs opacity-75 mt-2">Currently in transit</p>
        </div>
    </div>

    <!-- Transfer Volume Trend -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Transfer Volume Trend</h2>
        <div id="transferVolumeTrendChart" style="height: 300px;"></div>
    </div>

    <!-- Transfer Routes Matrix -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Transfer Routes (Warehouse → Shop)</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-gray-700 font-semibold">From Warehouse</th>
                        <th class="px-3 py-2 text-left text-gray-700 font-semibold">To Shop</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Transfer Count</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Discrepancies</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Success Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->transferRoutes as $route)
                        @php
                            $successRate = $route['transfer_count'] > 0
                                ? (($route['transfer_count'] - $route['discrepancy_count']) / $route['transfer_count']) * 100
                                : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-gray-900 font-medium">{{ $route['warehouse_name'] }}</td>
                            <td class="px-3 py-2 text-gray-900 font-medium">{{ $route['shop_name'] }}</td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ number_format($route['transfer_count']) }}</td>
                            <td class="px-3 py-2 text-right">
                                @if($route['discrepancy_count'] > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">
                                        {{ $route['discrepancy_count'] }}
                                    </span>
                                @else
                                    <span class="text-green-600">0</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $successRate >= 95 ? 'bg-green-100 text-green-800' : ($successRate >= 80 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format($successRate, 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">No transfer data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Two Column: Status Distribution & Completion Time -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Status Distribution -->
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Status Distribution</h2>
            <div id="statusDistributionChart" style="height: 300px;"></div>
        </div>

        <!-- Completion Time Distribution -->
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Completion Time Distribution</h2>
            <div id="completionTimeChart" style="height: 300px;"></div>
        </div>
    </div>

    <!-- Two Column: Most Transferred Products & Recent Discrepancies -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Most Transferred Products -->
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Most Transferred Products</h2>
            <div class="overflow-y-auto" style="max-height: 350px;">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-gray-700 font-semibold">#</th>
                            <th class="px-3 py-2 text-left text-gray-700 font-semibold">Product</th>
                            <th class="px-3 py-2 text-right text-gray-700 font-semibold">Sent</th>
                            <th class="px-3 py-2 text-right text-gray-700 font-semibold">Received</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($this->mostTransferredProducts as $index => $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-gray-600">{{ $index + 1 }}</td>
                                <td class="px-3 py-2 text-gray-900 font-medium">{{ $product['product_name'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-600">{{ number_format($product['total_sent']) }}</td>
                                <td class="px-3 py-2 text-right">
                                    @if($product['total_discrepancy'] > 0)
                                        <span class="text-amber-600">{{ number_format($product['total_received']) }}</span>
                                    @else
                                        <span class="text-green-600">{{ number_format($product['total_received']) }}</span>
                                    @endif
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

        <!-- Recent Discrepancies -->
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Recent Discrepancies</h2>
            <div class="overflow-y-auto" style="max-height: 350px;">
                <div class="space-y-2">
                    @forelse($this->recentDiscrepancies as $transfer)
                        <div class="p-3 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                            <div class="flex items-start justify-between mb-1">
                                <span class="text-xs font-bold text-gray-900">{{ $transfer['transfer_number'] }}</span>
                                <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($transfer['created_at'])->format('M d, Y') }}</span>
                            </div>
                            <div class="text-xs text-gray-700 mb-1">
                                <strong>From:</strong> {{ $transfer['from_warehouse'] }} →
                                <strong>To:</strong> {{ $transfer['to_shop'] }}
                            </div>
                            @if($transfer['discrepancy_notes'])
                                <p class="text-xs text-gray-600 mt-1">{{ Str::limit($transfer['discrepancy_notes'], 80) }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-center text-gray-500 py-4">No recent discrepancies</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Warehouse Efficiency -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Warehouse Efficiency</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-gray-700 font-semibold">Warehouse</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Total Transfers</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Avg Completion</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Discrepancies</th>
                        <th class="px-3 py-2 text-right text-gray-700 font-semibold">Success Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->warehouseEfficiency as $warehouse)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-gray-900 font-medium">{{ $warehouse['warehouse_name'] }}</td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ number_format($warehouse['total_transfers']) }}</td>
                            <td class="px-3 py-2 text-right text-gray-600">{{ number_format($warehouse['avg_completion_hours'], 1) }}h</td>
                            <td class="px-3 py-2 text-right">
                                @if($warehouse['discrepancy_count'] > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">
                                        {{ $warehouse['discrepancy_count'] }}
                                    </span>
                                @else
                                    <span class="text-green-600">0</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ (100 - $warehouse['discrepancy_rate']) >= 95 ? 'bg-green-100 text-green-800' : ((100 - $warehouse['discrepancy_rate']) >= 80 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format(100 - $warehouse['discrepancy_rate'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">No warehouse data available</td>
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
            // Transfer Volume Trend Chart
            const transferVolumeTrendData = @json($this->transferVolumeTrend);
            const transferVolumeTrendChart = new ApexCharts(document.querySelector("#transferVolumeTrendChart"), {
                series: [{
                    name: 'Transfers',
                    data: transferVolumeTrendData.map(item => ({
                        x: new Date(item.date).getTime(),
                        y: item.count
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
                colors: ['#8b5cf6'],
                xaxis: {
                    type: 'datetime',
                    labels: {
                        datetimeFormatter: {
                            day: 'MMM dd'
                        }
                    }
                },
                yaxis: {
                    title: { text: 'Number of Transfers' }
                },
                tooltip: {
                    x: { format: 'MMM dd, yyyy' }
                }
            });
            transferVolumeTrendChart.render();

            // Status Distribution Chart
            const statusDistributionData = @json($this->statusDistribution);
            const statusDistributionChart = new ApexCharts(document.querySelector("#statusDistributionChart"), {
                series: statusDistributionData.map(item => item.count),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: statusDistributionData.map(item => item.status.replace('_', ' ').toUpperCase()),
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'],
                legend: {
                    position: 'bottom'
                }
            });
            statusDistributionChart.render();

            // Completion Time Distribution Chart
            const completionTimeData = @json($this->completionTimeDistribution);
            const completionTimeChart = new ApexCharts(document.querySelector("#completionTimeChart"), {
                series: [{
                    name: 'Transfers',
                    data: completionTimeData.map(item => item.count)
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        distributed: true
                    }
                },
                colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                xaxis: {
                    categories: completionTimeData.map(item => item.time_bucket)
                },
                legend: { show: false }
            });
            completionTimeChart.render();
        });
    </script>
    @endpush
</div>
