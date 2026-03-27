<div
    class="card"
    style="animation:fadeUp .4s ease .45s both"
    x-data="salesPerfChart()"
    x-init="init()"
    x-destroy="teardown()"
    data-chart='@json($chartData)'
    data-comparison='@json($comparisonData)'
    data-show-comparison='{{ $showComparison ? "1" : "0" }}'
>
    <div class="card-header">
        <div>
            <div class="card-title">Sales Performance</div>
            <div class="card-subtitle">Revenue over time (RWF)</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            {{-- Compare toggle --}}
            <button
                wire:click="toggleComparison"
                style="font-size:12px;font-weight:600;padding:5px 11px;border-radius:var(--rsm);
                       border:1px solid var(--border);cursor:pointer;transition:all var(--tr);
                       {{ $showComparison
                          ? 'background:var(--accent);color:#fff;border-color:var(--accent)'
                          : 'background:var(--surface2);color:var(--text-sub);' }}">
                vs Previous
            </button>
            <div class="period-tabs">
                @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'quarter' => 'Quarter'] as $key => $lbl)
                <button class="period-tab {{ $chartPeriod === $key ? 'active' : '' }}"
                        wire:click="setChartPeriod('{{ $key }}')">{{ $lbl }}</button>
                @endforeach
            </div>
        </div>
    </div>

    @if(!$loaded)
    <div style="padding:0 16px 16px 16px">
        <div class="skeleton-pulse" style="height:220px;border-radius:var(--rsm)"></div>
    </div>
    @endif

    <div wire:ignore style="padding:0 16px 16px 16px;{{ $loaded ? '' : 'display:none' }}">
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

@script
<script>
Alpine.data('salesPerfChart', () => ({
    // CRITICAL: chartInstance stored on DOM element to avoid Alpine proxy recursion.
    morphCleanup: null,

    init() {
        var self = this;
        setTimeout(function() {
            if (document.getElementById('salesChart')) {
                self.draw();
            } else {
                setTimeout(function() { self.draw(); }, 300);
            }
        }, 150);

        self.morphCleanup = Livewire.hook('morph.updated', function(payload) {
            if (payload.el === self.$el) {
                self.updateChart();
            }
        });
    },

    teardown() {
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

    updateChart() {
        var canvas = document.getElementById('salesChart');
        if (!canvas || !canvas._chartInstance) {
            this.draw();
            return;
        }
        var raw = this.$el.dataset.chart;
        var data = raw ? JSON.parse(raw) : null;
        if (!data) return;

        var compRaw = this.$el.dataset.comparison;
        var comp    = compRaw ? JSON.parse(compRaw) : null;
        var showComp = this.$el.dataset.showComparison === '1';

        var inst = canvas._chartInstance;
        inst.data.labels = data.labels;
        inst.data.datasets[0].data = data.revenueData;
        inst.data.datasets[1].data = data.countData;

        if (showComp && comp && comp.revenueData) {
            if (inst.data.datasets.length < 3) {
                inst.data.datasets.push(this.buildCompDataset(comp.revenueData));
            } else {
                inst.data.datasets[2].data = comp.revenueData;
            }
        } else {
            if (inst.data.datasets.length >= 3) {
                inst.data.datasets.splice(2, 1);
            }
        }

        try {
            inst.update('none');
        } catch(e) {
            inst.destroy();
            delete canvas._chartInstance;
            this.draw();
        }
    },

    buildCompDataset(revData) {
        return {
            label: 'Previous Period',
            data: revData,
            type: 'line',
            borderColor: '#a8aec8',
            borderWidth: 1.5,
            borderDash: [5, 5],
            borderDashOffset: 0,
            fill: false,
            pointRadius: 0,
            tension: 0.4,
            yAxisID: 'yRevenue'
        };
    },

    draw() {
        var canvas = document.getElementById('salesChart');
        if (!canvas) return;

        var raw = this.$el.dataset.chart;
        var data = raw ? JSON.parse(raw) : null;
        if (!data) return;

        var compRaw = this.$el.dataset.comparison;
        var comp    = compRaw ? JSON.parse(compRaw) : null;
        var showComp = this.$el.dataset.showComparison === '1';

        // Kill orphaned Chart.js instances
        var orphan = Chart.getChart(canvas);
        if (orphan) orphan.destroy();
        if (canvas._chartInstance) {
            canvas._chartInstance.destroy();
            delete canvas._chartInstance;
        }

        var isMobile    = window.innerWidth <= 640;
        var aspectRatio = isMobile ? 1.5 : (window.innerWidth <= 1024 ? 2 : 2.5);

        // Area fill gradient
        var ctx      = canvas.getContext('2d');
        var gradient = ctx.createLinearGradient(0, 0, 0, canvas.offsetHeight || 200);
        gradient.addColorStop(0, 'rgba(59, 111, 212, 0.15)');
        gradient.addColorStop(1, 'rgba(59, 111, 212, 0)');

        var datasets = [
            {
                label: 'Revenue (RWF)',
                data: data.revenueData,
                type: 'line',
                fill: true,
                backgroundColor: gradient,
                borderColor: '#3b6fd4',
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: '#3b6fd4',
                yAxisID: 'yRevenue'
            },
            {
                label: 'Transactions',
                data: data.countData,
                type: 'line',
                fill: false,
                backgroundColor: 'rgba(14,158,134,0.1)',
                borderColor: '#0e9e86',
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.4,
                yAxisID: 'yCount'
            }
        ];

        if (showComp && comp && comp.revenueData) {
            datasets.push(this.buildCompDataset(comp.revenueData));
        }

        canvas._chartInstance = new Chart(canvas, {
            type: 'line',
            data: { labels: data.labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: aspectRatio,
                animation: {
                    x: { duration: 800, easing: 'easeOutCubic' },
                    y: { duration: 0 }
                },
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
                        grid: { color: 'rgba(0,0,0,0.04)' },
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
}));
</script>
@endscript
