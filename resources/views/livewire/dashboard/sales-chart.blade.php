<div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-4 sm:p-5">
    <!-- Header -->
    <div class="flex items-start justify-between mb-3.5 sm:mb-4.5">
        <div>
            <h2 class="text-[15px] font-bold" style="color: var(--text);">Sales Overview</h2>
            <p class="text-[13px] mt-0.5" style="color: var(--text-sub);">Last 7 days revenue breakdown</p>
        </div>
        <a href="#" class="text-[13px] font-semibold px-2.5 py-1.5 rounded-lg transition-colors"
           style="color: var(--accent); background: var(--accent-dim);">
            View All
        </a>
    </div>

    <!-- Chart Canvas -->
    <div class="relative" style="height: 240px;">
        <canvas id="salesChart"></canvas>
    </div>

    <!-- Legend -->
    <div class="flex items-center justify-center gap-6 mt-4.5 pt-4 border-t" style="border-color: var(--border);">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full" style="background: var(--accent);"></div>
            <span class="text-[13px]" style="color: var(--text-sub);">Full Box</span>
            <span class="text-[13px] font-semibold ml-1" style="color: var(--text); font-family: var(--mono);">
                RWF {{ number_format(array_sum($this->salesData['datasets'][0]['data']), 0) }}
            </span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full" style="background: var(--green);"></div>
            <span class="text-[13px]" style="color: var(--text-sub);">Individual Items</span>
            <span class="text-[13px] font-semibold ml-1" style="color: var(--text); font-family: var(--mono);">
                RWF {{ number_format(array_sum($this->salesData['datasets'][1]['data']), 0) }}
            </span>
        </div>
    </div>

    @push('scripts')
    <script>
        function initSalesChart() {
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;

            const chartData = @json($this->salesData);

            new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    borderRadius: 4,
                    barPercentage: 0.7,
                    categoryPercentage: 0.8,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#111420',
                            titleColor: '#e4e8f5',
                            bodyColor: '#8b92b3',
                            borderColor: '#232840',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': RWF ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                color: '#232840'
                            },
                            ticks: {
                                color: '#3d4460',
                                font: {
                                    family: 'DM Mono, monospace',
                                    size: 10
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: '#232840',
                                drawBorder: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                color: '#3d4460',
                                font: {
                                    family: 'DM Mono, monospace',
                                    size: 10
                                },
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 750,
                        easing: 'easeInOutQuart'
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
                    document.addEventListener('DOMContentLoaded', initSalesChart);
                } else {
                    initSalesChart();
                }
            };
            document.head.appendChild(script);
        } else {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSalesChart);
            } else {
                initSalesChart();
            }
        }
    </script>
    @endpush
</div>
