<div
    class="card"
    style="animation:fadeUp .4s ease .45s both"
    x-data="salesPerfChart()"
    x-init="init()"
    x-destroy="teardown()"
    data-chart='@json($chartData)'
>
    <div class="card-header">
        <div>
            <div class="card-title">Sales Performance</div>
            <div class="card-subtitle">Revenue over time (RWF)</div>
        </div>
        <div class="period-tabs">
            @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'quarter' => 'Quarter'] as $key => $lbl)
            <button class="period-tab {{ $chartPeriod === $key ? 'active' : '' }}"
                    wire:click="setChartPeriod('{{ $key }}')">{{ $lbl }}</button>
            @endforeach
        </div>
    </div>

    <div wire:ignore style="padding:0 16px 16px 16px">
        <canvas id="salesChart" style="max-height:280px"></canvas>
    </div>

    @php $summaries = $this->getPeriodSummaries(); @endphp
    <div class="sp-period-row">
        @foreach([0 => ['today','Today'], 1 => ['week','This Week'], 2 => ['month','This Month'], 3 => ['quarter','This Quarter']] as $idx => [$key, $lbl])
        <div class="sp-period-col {{ $activePeriodCol === $idx ? 'active-period' : '' }}"
             wire:click="setActivePeriodCol({{ $idx }})">
            <div class="sp-period-name">{{ $lbl }}</div>
            <div class="sp-period-val {{ $summaries[$key] > 0 ? 'blue' : 'ok' }}">
                {{ $summaries[$key] > 0 ? number_format($summaries[$key]) : '0' }}
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
function salesPerfChart() {
    return {
        // --- CRITICAL: chartInstance is NOT declared here. -------------------
        //
        // Declaring it inside return{} makes Alpine wrap it in a reactive Proxy.
        // When Livewire morphs, it calls toRaw() to unwrap the proxy chain.
        // Chart.js instances have a circular reference: chart.canvas.chart = chart.
        // toRaw() recurses into that cycle forever --- "too much recursion" crash.
        // The corrupted proxy then passes undefined to plugins --- "fullSize undefined".
        //
        // Fix: store the chart directly on the DOM element (this.$el._chart).
        // DOM element properties are invisible to Alpine's reactivity system.
        // ---------------------------------------------------------------------

        morphCleanup: null,

        init() {
            var self = this;
            setTimeout(function() { self.draw(); }, 50);

            self.morphCleanup = Livewire.hook('morph.updated', function(payload) {
                if (payload.el === self.$el) {
                    self.updateChart();
                }
            });
        },

        teardown() {
            // Clean up the chart stored on the DOM element
            var canvas = document.getElementById('salesChart');
            if (canvas && canvas._chartInstance) {
                canvas._chartInstance.destroy();
                delete canvas._chartInstance;
            }
            if (typeof this.morphCleanup === 'function') {
                this.morphCleanup();
                this.morphCleanup = null;
            }
        },

        // Called after Livewire morph - updates chart data in-place.
        updateChart() {
            var canvas = document.getElementById('salesChart');
            if (!canvas || !canvas._chartInstance) {
                this.draw();
                return;
            }
            var raw = this.$el.dataset.chart;
            var data = raw ? JSON.parse(raw) : null;
            if (!data) return;

            canvas._chartInstance.data.labels = data.labels;
            canvas._chartInstance.data.datasets[0].data = data.revenueData;
            canvas._chartInstance.data.datasets[1].data = data.countData;
            canvas._chartInstance.update('none');
        },

        draw() {
            var canvas = document.getElementById('salesChart');
            if (!canvas) return;

            var raw = this.$el.dataset.chart;
            var data = raw ? JSON.parse(raw) : null;
            if (!data) return;

            // Kill any orphaned Chart.js instance on this canvas
            var orphan = Chart.getChart(canvas);
            if (orphan) orphan.destroy();
            if (canvas._chartInstance) {
                canvas._chartInstance.destroy();
                delete canvas._chartInstance;
            }

            var isMobile = window.innerWidth <= 640;
            var aspectRatio = isMobile ? 1.5 : (window.innerWidth <= 1024 ? 2 : 2.5);

            // Store on the canvas DOM element - NOT on Alpine reactive state
            canvas._chartInstance = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Revenue (RWF)',
                            data: data.revenueData,
                            backgroundColor: '#3b6fd4',
                            borderRadius: isMobile ? 4 : 6,
                            borderSkipped: false,
                            yAxisID: 'yRevenue'
                        },
                        {
                            label: 'Transactions',
                            data: data.countData,
                            type: 'line',
                            backgroundColor: '#0e9e8625',
                            borderColor: '#0e9e86',
                            borderWidth: 2,
                            pointRadius: 3,
                            tension: 0.3,
                            yAxisID: 'yCount'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: aspectRatio,
                    animation: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                color: '#6b7494',
                                font: { size: 11, weight: '600', family: 'DM Sans' },
                                padding: 12,
                                boxWidth: 12,
                                boxHeight: 12,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#1a1f36',
                            bodyColor: '#6b7494',
                            borderColor: '#e2e6f3',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            boxPadding: 6,
                            callbacks: {
                                label: function(ctx) {
                                    if (ctx.dataset.yAxisID === 'yRevenue') {
                                        return ' RWF ' + ctx.parsed.y.toLocaleString();
                                    }
                                    return ' ' + ctx.parsed.y + ' transactions';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#a8aec8', font: { size: 11, family: 'DM Sans' } },
                            border: { display: false }
                        },
                        yRevenue: {
                            position: 'left',
                            beginAtZero: true,
                            grid: { color: '#e2e6f3' },
                            ticks: {
                                color: '#a8aec8',
                                font: { size: 11, family: 'DM Sans' },
                                padding: 8,
                                callback: function(val) {
                                    return val >= 1000 ? Math.round(val / 1000) + 'K' : val;
                                }
                            },
                            border: { display: false }
                        },
                        yCount: {
                            position: 'right',
                            beginAtZero: true,
                            grid: { drawOnChartArea: false },
                            ticks: {
                                color: '#0e9e86',
                                font: { size: 10, family: 'DM Sans' },
                                precision: 0
                            },
                            border: { display: false }
                        }
                    }
                }
            });
        }
    };
}
</script>