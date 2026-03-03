<div
    class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-4 sm:p-5"
    wire:poll.30s
    x-data="stockDistChart()"
    x-init="init()"
    x-destroy="teardown()"
    data-chart='@json($this->stockDistribution)'
    data-total="{{ $this->totalBoxes }}"
>
    <div class="flex items-start justify-between mb-3.5 sm:mb-4.5">
        <div>
            <h3 class="text-[15px] font-bold" style="color: var(--text);">Stock Distribution</h3>
            <p class="text-xs mt-0.5" style="color: var(--text-sub);">Sellable boxes by location</p>
        </div>
        <a href="{{ route('owner.boxes.index') }}"
           class="text-[13px] font-semibold px-2.5 py-1.5 rounded-lg transition-colors"
           style="color: var(--accent); background: var(--accent-dim);">
            View All
        </a>
    </div>

    @if($this->stockDistribution && count($this->stockDistribution) > 0)
        <div wire:ignore class="flex items-center justify-center mb-4">
            <div style="width:180px;height:180px;position:relative;">
                <canvas id="stockDistributionChart" style="display:block"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <div id="stockDistTotal" class="text-2xl font-bold" style="color:var(--text)">
                        {{ number_format($this->totalBoxes) }}
                    </div>
                    <div class="text-[11px] mt-0.5" style="color:var(--text-sub)">Sellable Boxes</div>
                </div>
            </div>
        </div>

        <div class="space-y-2 overflow-y-auto" style="max-height:140px">
            @foreach($this->stockDistribution as $location)
                @php $pct = $this->totalBoxes > 0 ? round(($location['box_count'] / $this->totalBoxes) * 100) : 0; @endphp
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <div style="width:10px;height:10px;border-radius:3px;background:{{ $location['color'] }};flex-shrink:0"></div>
                        <span class="truncate" style="color:var(--text-sub)">{{ $location['location_name'] }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded flex-shrink-0"
                              style="background:var(--bg);color:var(--text-dim)">
                            {{ ucfirst($location['location_type']) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                        <span style="color:var(--text);font-weight:700;font-family:var(--mono)">
                            {{ number_format($location['box_count']) }}
                        </span>
                        <span style="color:var(--text-dim);min-width:28px;text-align:right">{{ $pct }}%</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-6">
            <svg class="w-12 h-12 mb-2" style="color:var(--border)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-xs" style="color:var(--text-dim)">No sellable boxes in inventory</p>
        </div>
    @endif

    <div class="mt-3 pt-3 flex items-center justify-between text-xs"
         style="border-top:1px solid var(--border)">
        <div class="flex items-center gap-2">
            <div style="width:10px;height:10px;border-radius:3px;flex-shrink:0;
                        background:{{ $this->damagedBoxes > 0 ? '#ef4444' : 'var(--border)' }}"></div>
            <span style="color:var(--text-sub)">Damaged boxes (not sellable)</span>
        </div>
        <span style="font-weight:700;font-family:var(--mono);
                     color:{{ $this->damagedBoxes > 0 ? '#ef4444' : 'var(--text-dim)' }}">
            {{ $this->damagedBoxes > 0 ? number_format($this->damagedBoxes) : 'None' }}
        </span>
    </div>
</div>

<script>
function stockDistChart() {
    return {
        chartInstance: null,
        morphHook: null,

        init() {
            setTimeout(function() { this.draw(); }.bind(this), 50);

            var self = this;
            this.morphHook = Livewire.hook('morph.updated', function(payload) {
                if (payload.el === self.$el) {
                    self.updateChart();
                }
            });
        },

        teardown() {
            // Kill our tracked instance
            if (this.chartInstance) {
                this.chartInstance.destroy();
                this.chartInstance = null;
            }
            // Kill any orphaned instance Chart.js still knows about
            // (happens when teardown() didn't fire cleanly during SPA nav)
            var orphan = Chart.getChart(canvas);
            if (orphan) orphan.destroy();
            if (typeof this.morphHook === 'function') {
                this.morphHook();
            }
        },

        updateChart() {
            if (!this.chartInstance) {
                this.draw();
                return;
            }
            var raw = this.$el.dataset.chart;
            var stockData = raw ? JSON.parse(raw) : [];
            if (!stockData.length) return;

            this.chartInstance.data.labels = stockData.map(function(i) { return i.location_name; });
            this.chartInstance.data.datasets[0].data = stockData.map(function(i) { return i.box_count; });
            this.chartInstance.data.datasets[0].backgroundColor = stockData.map(function(i) { return i.color; });
            this.chartInstance.update('none');

            var totalEl = document.getElementById('stockDistTotal');
            if (totalEl) {
                totalEl.textContent = parseInt(this.$el.dataset.total || 0).toLocaleString();
            }
        },

        draw() {
            var canvas = document.getElementById('stockDistributionChart');
            if (!canvas) return;

            var raw = this.$el.dataset.chart;
            var stockData = raw ? JSON.parse(raw) : [];
            if (!stockData.length) return;

            if (this.chartInstance) {
                this.chartInstance.destroy();
                this.chartInstance = null;
            }

            this.chartInstance = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: stockData.map(function(i) { return i.location_name; }),
                    datasets: [{
                        data: stockData.map(function(i) { return i.box_count; }),
                        backgroundColor: stockData.map(function(i) { return i.color; }),
                        borderWidth: 0,
                        spacing: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '70%',
                    animation: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return ' ' + ctx.parsed + ' sellable boxes'; }
                            }
                        }
                    }
                }
            });
        }
    };
}
</script>