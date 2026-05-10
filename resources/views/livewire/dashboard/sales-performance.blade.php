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
            <div class="card-title">Revenue &amp; Profit Trend</div>
            <div class="card-subtitle">RWF · revenue, gross &amp; net profit over time</div>
        </div>
        <div>
            <select
                wire:change="setChartPeriod($event.target.value)"
                style="font-size:12px;font-weight:600;padding:5px 28px 5px 10px;
                       border-radius:var(--rsm);border:1px solid var(--border);
                       background:var(--surface2);color:var(--text);cursor:pointer;
                       appearance:none;-webkit-appearance:none;
                       background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%236b7494' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E\");
                       background-repeat:no-repeat;background-position:right 8px center">
                <option value="daily"   {{ $chartPeriod === 'daily'   ? 'selected' : '' }}>Daily</option>
                <option value="weekly"  {{ $chartPeriod === 'weekly'  ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ $chartPeriod === 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
        </div>
    </div>

    @if(!$loaded)
    <div style="padding:0 16px 16px 16px">
        <div class="skeleton-pulse" style="height:220px;border-radius:var(--rsm)"></div>
    </div>
    @endif

    <div class="chart-container" style="height:240px;padding:0 16px 16px 16px;{{ $loaded ? '' : 'display:none' }}">
        <canvas id="salesChart" wire:ignore style="width:100%;height:100%"></canvas>
    </div>
</div>

@script
<script>
Alpine.data('salesPerfChart', () => ({

    init() {
        var self = this;

        this.$wire.$watch('chartData', function() {
            self._scheduleRedraw();
        });

        setTimeout(function() {
            var canvas = document.getElementById('salesChart');
            if (canvas && !canvas._chartInstance) {
                self._scheduleRedraw();
            }
        }, 400);
    },

    _scheduleRedraw() {
        var self = this;
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                self.updateChart();
            });
        });
    },

    teardown() {
        var canvas = document.getElementById('salesChart');
        if (canvas && canvas._chartInstance) {
            canvas._chartInstance.destroy();
            delete canvas._chartInstance;
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

        var inst = canvas._chartInstance;
        inst.data.labels             = data.labels;
        inst.data.datasets[0].data  = data.revenueData;
        inst.data.datasets[1].data  = data.profitData;
        inst.data.datasets[2].data  = data.netData;

        try {
            inst.resize();
            inst.update('none');
        } catch(e) {
            inst.destroy();
            delete canvas._chartInstance;
            this.draw();
        }
    },

    draw() {
        var canvas = document.getElementById('salesChart');
        if (!canvas) return;
        if (canvas.offsetWidth === 0) {
            var self = this;
            requestAnimationFrame(function() { self.draw(); });
            return;
        }

        var raw = this.$el.dataset.chart;
        var data = raw ? JSON.parse(raw) : null;
        if (!data) return;

        var orphan = Chart.getChart(canvas);
        if (orphan) orphan.destroy();
        if (canvas._chartInstance) {
            canvas._chartInstance.destroy();
            delete canvas._chartInstance;
        }

        canvas._chartInstance = new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: data.revenueData,
                        borderColor: '#3b6fd4',
                        backgroundColor: '#3b6fd4',
                        fill: false,
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#3b6fd4',
                        pointBorderWidth: 0
                    },
                    {
                        label: 'Gross Profit',
                        data: data.profitData,
                        borderColor: '#0e9e86',
                        backgroundColor: '#0e9e86',
                        fill: false,
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#0e9e86',
                        pointBorderWidth: 0
                    },
                    {
                        label: 'Net Profit',
                        data: data.netData,
                        borderColor: '#8b5cf6',
                        backgroundColor: '#8b5cf6',
                        fill: false,
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#8b5cf6',
                        pointBorderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'start',
                        labels: {
                            color: '#6b7494',
                            font: { size: 11, weight: '600', family: 'DM Sans' },
                            padding: 16,
                            boxWidth: 10,
                            boxHeight: 10,
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
                        boxPadding: 6,
                        callbacks: {
                            label: function(ctx) {
                                var val = ctx.parsed.y;
                                var formatted = val < 0
                                    ? '-' + Math.abs(val).toLocaleString()
                                    : val.toLocaleString();
                                return ' ' + ctx.dataset.label + ': ' + formatted + ' RWF';
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
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            color: '#a8aec8',
                            font: { size: 11, family: 'DM Sans' },
                            padding: 8,
                            callback: function(val) {
                                if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                                if (val >= 1000)    return Math.round(val / 1000) + 'K';
                                return val;
                            }
                        },
                        border: { display: false }
                    }
                }
            }
        });
        canvas._chartInstance.resize();
    }
}));
</script>
@endscript
