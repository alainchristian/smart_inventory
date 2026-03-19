<div
    class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-4 sm:p-5"
    wire:poll.31s
    x-data="stockDistChart()"
    x-init="init()"
    x-destroy="teardown()"
    data-chart='@json($this->stockDistribution)'
    data-total="{{ $this->totalBoxes }}"
    style="display:flex;flex-direction:column"
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
        <div wire:ignore class="flex items-center justify-center mb-4" style="flex-shrink:0">
            <div style="width:180px;height:180px;position:relative;">
                <canvas id="stockDistributionChart" style="display:block"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <div id="stockDistTotal" style="font-size:28px;font-weight:800;
                         letter-spacing:-1px;color:var(--text)">
                        {{ number_format($this->totalBoxes) }}
                    </div>
                    <div style="font-size:13px;margin-top:2px;color:var(--text-sub);
                                font-weight:600">
                        Sellable Boxes
                    </div>
                </div>
            </div>
        </div>

        <div class="card-scroll" style="margin-top:12px">
            @foreach($this->stockDistribution as $loc)
            @php
                $pct = $this->totalBoxes > 0
                    ? round(($loc['box_count'] / $this->totalBoxes) * 100)
                    : 0;
            @endphp
            <div style="display:flex;align-items:center;gap:10px;
                        padding:10px 0;border-bottom:1px solid var(--border)">

                {{-- Color dot --}}
                <div style="width:10px;height:10px;border-radius:50%;
                            flex-shrink:0;background:{{ $loc['color'] }}">
                </div>

                {{-- Name + type --}}
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:700;color:var(--text);
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $loc['location_name'] }}
                    </div>
                    <div style="font-size:11px;color:var(--text-dim);margin-top:1px;
                                text-transform:capitalize">
                        {{ $loc['location_type'] }}
                    </div>
                </div>

                {{-- Box count — PRIMARY --}}
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:18px;font-weight:800;font-family:var(--mono);
                                letter-spacing:-0.5px;color:var(--text)">
                        {{ number_format($loc['box_count']) }}
                    </div>
                    <div style="font-size:11px;color:var(--text-dim)">
                        boxes · {{ $pct }}%
                    </div>
                </div>

            </div>
            @endforeach

            {{-- Damaged boxes --}}
            @if($this->damagedBoxes > 0)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 0">
                <div style="width:10px;height:10px;border-radius:50%;
                            flex-shrink:0;background:var(--red-dim);
                            border:1.5px solid var(--red)">
                </div>
                <div style="flex:1;font-size:13px;color:var(--text-dim)">
                    Damaged (not sellable)
                </div>
                <div style="font-size:16px;font-weight:700;font-family:var(--mono);
                            color:var(--red)">
                    {{ $this->damagedBoxes }}
                </div>
            </div>
            @else
            <div style="display:flex;align-items:center;gap:10px;padding:10px 0">
                <div style="width:10px;height:10px;border-radius:50%;
                            flex-shrink:0;background:var(--surface3)">
                </div>
                <div style="flex:1;font-size:13px;color:var(--text-dim)">
                    Damaged boxes (not sellable)
                </div>
                <div style="font-size:13px;font-weight:600;color:var(--text-dim)">
                    None
                </div>
            </div>
            @endif
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


</div>

@script
<script>
Alpine.data('stockDistChart', () => ({
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
            var canvas = document.getElementById('stockDistributionChart');
            if (canvas && canvas._chartInstance) {
                canvas._chartInstance.destroy();
                delete canvas._chartInstance;
            }
            if (canvas) {
                var orphan = Chart.getChart(canvas);
                if (orphan) orphan.destroy();
            }
            if (typeof this.morphHook === 'function') {
                this.morphHook();
            }
        },

        updateChart() {
            var canvas = document.getElementById('stockDistributionChart');
            if (!canvas || !canvas._chartInstance) {
                this.draw();
                return;
            }
            var raw = this.$el.dataset.chart;
            var stockData = raw ? JSON.parse(raw) : [];
            if (!stockData.length) return;

            canvas._chartInstance.data.labels = stockData.map(function(i) { return i.location_name; });
            canvas._chartInstance.data.datasets[0].data = stockData.map(function(i) { return i.box_count; });
            canvas._chartInstance.data.datasets[0].backgroundColor = stockData.map(function(i) { return i.color; });
            try {
                canvas._chartInstance.update('none');
            } catch (e) {
                canvas._chartInstance.destroy();
                delete canvas._chartInstance;
                this.draw();
            }

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

            if (canvas._chartInstance) {
                canvas._chartInstance.destroy();
                delete canvas._chartInstance;
            }

            canvas._chartInstance = new Chart(canvas, {
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
}));
</script>
@endscript