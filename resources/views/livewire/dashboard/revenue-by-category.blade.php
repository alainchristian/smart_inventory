<div class="card" style="display:flex;flex-direction:column"
     x-data="revCatChart()"
     x-init="init()"
     data-cats='@json($categories)'
     data-total="{{ $total }}">

    <div class="card-header">
        <div>
            <div class="card-title">Revenue by Category</div>
            <div class="card-subtitle">Sales breakdown · RWF</div>
        </div>
        <a href="{{ route('owner.reports.sales') }}" class="card-btn">View all</a>
    </div>

    {{-- Donut LEFT + Legend RIGHT --}}
    <div style="display:flex;align-items:center;gap:18px;flex:1;min-height:0;overflow:hidden;padding-bottom:4px">

        {{-- Donut --}}
        <div style="position:relative;flex-shrink:0;width:140px;height:140px">
            <canvas id="revCatCanvas" width="140" height="140" wire:ignore></canvas>
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;
                        align-items:center;justify-content:center;pointer-events:none;text-align:center;padding:0 8px">
                <div style="font-size:13px;font-weight:800;color:var(--text);font-family:var(--mono);line-height:1.2">{{ number_format($total) }}</div>
                <div style="font-size:9px;color:var(--text-dim);margin-top:3px;white-space:nowrap">Total Revenue</div>
            </div>
        </div>

        {{-- Legend --}}
        <div style="flex:1;min-width:0;overflow-y:auto;min-height:0">
            @forelse($categories as $cat)
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:9px">
                <div style="width:10px;height:10px;border-radius:50%;flex-shrink:0;background:{{ $cat['color'] }}"></div>
                <div style="flex:1;min-width:0;font-size:12px;font-weight:500;color:var(--text);
                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cat['name'] }}</div>
                <div style="flex-shrink:0;text-align:right">
                    <div style="font-size:11px;font-weight:600;color:var(--text);font-family:var(--mono);white-space:nowrap">{{ number_format($cat['revenue']) }}</div>
                    <div style="font-size:10px;color:var(--text-dim);white-space:nowrap">({{ $cat['pct'] }}%)</div>
                </div>
            </div>
            @empty
            <div style="padding:20px 0;text-align:center;color:var(--text-dim);font-size:13px">
                No sales data for this period
            </div>
            @endforelse
        </div>

    </div>
</div>

@script
<script>
Alpine.data('revCatChart', () => ({
    init() {
        var self = this;
        this.$nextTick(() => self.draw());
        this.$wire.$watch('categories', () => self.$nextTick(() => self.draw()));
    },

    draw() {
        var canvas = document.getElementById('revCatCanvas');
        if (!canvas) return;

        var existing = Chart.getChart(canvas);
        if (existing) existing.destroy();
        if (canvas._revCatChart) { canvas._revCatChart.destroy(); delete canvas._revCatChart; }

        var raw = this.$el.dataset.cats;
        var cats = raw ? JSON.parse(raw) : [];
        if (!cats.length) return;

        canvas._revCatChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: cats.map(c => c.name),
                datasets: [{
                    data: cats.map(c => c.revenue),
                    backgroundColor: cats.map(c => c.color),
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: false,
                cutout: '66%',
                animation: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString() + ' RWF'
                        }
                    }
                }
            }
        });
    }
}));
</script>
@endscript
