<div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-4 sm:p-5" wire:poll.30s>
    <!-- Header -->
    <div class="flex items-start justify-between mb-3.5 sm:mb-4.5">
        <div>
            <h3 class="text-[15px] font-bold" style="color: var(--text);">Stock Distribution</h3>
            <p class="text-xs mt-0.5" style="color: var(--text-sub);">By location</p>
        </div>
        <a href="{{ route('owner.boxes.index') }}" class="text-[13px] font-semibold px-2.5 py-1.5 rounded-lg transition-colors"
           style="color: var(--accent); background: var(--accent-dim);">
            View All
        </a>
    </div>

    @if($this->stockDistribution && count($this->stockDistribution) > 0)
        <!-- Chart Container -->
        <div class="flex items-center justify-center mb-4">
            <div style="width: 180px; height: 180px; position: relative;">
                <canvas id="stockDistributionChart"></canvas>
                <!-- Center Label -->
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <div class="text-2xl font-bold" style="color: var(--text); font-family: var(--font);">
                        {{ number_format($this->totalBoxes) }}
                    </div>
                    <div class="text-[11px] mt-0.5" style="color: var(--text-sub);">Total Boxes</div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="space-y-2 overflow-y-auto" style="max-height: 160px;">
            @foreach($this->stockDistribution as $location)
                @php
                    $percentage = $this->totalBoxes > 0 ? ($location['box_count'] / $this->totalBoxes) * 100 : 0;
                @endphp
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background: {{ $location['color'] }};"></div>
                        <span class="text-[14px] truncate" style="color: var(--text-sub);">{{ $location['location_name'] }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded" style="background: var(--surface2); color: var(--text-dim); font-family: var(--mono);">
                            {{ strtoupper($location['location_type']) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-[14px] font-semibold" style="color: var(--text); font-family: var(--mono);">
                            {{ $location['box_count'] }}
                        </span>
                        <span class="text-[11px]" style="color: var(--text-dim);">
                            ({{ number_format($percentage, 1) }}%)
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="flex flex-col items-center justify-center py-12">
            <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--text-sub);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-sm" style="color: var(--text-dim);">No boxes in inventory</p>
        </div>
    @endif

    @push('scripts')
    <script>
        function initStockDistributionChart() {
            const canvas = document.getElementById('stockDistributionChart');
            if (!canvas) return;

            // Destroy existing chart
            const existingChart = Chart.getChart(canvas);
            if (existingChart) {
                existingChart.destroy();
            }

            const stockData = @json($this->stockDistribution);
            if (!stockData || stockData.length === 0) return;

            const labels = stockData.map(item => item.location_name);
            const data = stockData.map(item => item.box_count);
            const colors = stockData.map(item => item.color);

            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 0,
                        spacing: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#111420',
                            titleColor: '#e4e8f5',
                            bodyColor: '#a8b0cf',
                            borderColor: '#232840',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' boxes (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Load Chart.js and initialize
        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            script.onload = () => {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initStockDistributionChart);
                } else {
                    initStockDistributionChart();
                }
            };
            document.head.appendChild(script);
        } else {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initStockDistributionChart);
            } else {
                initStockDistributionChart();
            }
        }

        // Re-initialize on Livewire updates
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morph.updated', () => {
                setTimeout(() => {
                    if (typeof Chart !== 'undefined') {
                        initStockDistributionChart();
                    }
                }, 100);
            });
        }
    </script>
    @endpush
</div>
