<div class="card" style="display:flex;flex-direction:column"
     x-data="expBreakChart()"
     x-init="init()"
     data-cats='@json($categories)'
     data-total="{{ $total }}">

    <div class="card-header">
        <div>
            <div class="card-title">Expenses Breakdown</div>
            <div class="card-subtitle">By category · RWF</div>
        </div>
        <a href="{{ route('owner.finance.overview') }}" class="card-btn">View all</a>
    </div>

    {{-- Donut LEFT + Legend RIGHT --}}
    <div style="display:flex;align-items:center;gap:18px;flex:1;min-height:0;overflow:hidden;padding-bottom:4px">

        {{-- Donut --}}
        <div style="position:relative;flex-shrink:0;width:140px;height:140px">
            <canvas id="expBreakCanvas" width="140" height="140" wire:ignore></canvas>
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;
                        align-items:center;justify-content:center;pointer-events:none;text-align:center;padding:0 8px">
                <div style="font-size:13px;font-weight:800;color:var(--text);font-family:var(--mono);line-height:1.2">
                    {{ number_format($total) }}
                </div>
                <div style="font-size:9px;color:var(--text-dim);margin-top:3px;white-space:nowrap">Total Expenses</div>
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
                    <div style="font-size:11px;font-weight:600;color:var(--text);font-family:var(--mono);white-space:nowrap">
                        {{ number_format($cat['total']) }}
                    </div>
                    <div style="font-size:10px;color:var(--text-dim);white-space:nowrap">({{ $cat['pct'] }}%)</div>
                </div>
            </div>
            @empty
            <div style="padding:20px 0;text-align:center;color:var(--text-dim);font-size:13px">
                No expenses this period
            </div>
            @endforelse
        </div>

    </div>
</div>

@script
<script>
Alpine.data('expBreakChart', () => ({
    init() {
        var self = this;
        this.$nextTick(() => self.draw());
        this.$wire.$watch('categories', () => self.$nextTick(() => self.draw()));
    },
    draw() {
        var canvas = document.getElementById('expBreakCanvas');
        if (!canvas) return;
        var ex = Chart.getChart(canvas);
        if (ex) ex.destroy();

        var raw = this.$el.dataset.cats;
        var cats = raw ? JSON.parse(raw) : [];

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: cats.length ? cats.map(c => c.name) : ['No data'],
                datasets: [{
                    data:            cats.length ? cats.map(c => c.total) : [1],
                    backgroundColor: cats.length ? cats.map(c => c.color) : ['#e2e6f3'],
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
                        enabled: cats.length > 0,
                        callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString() + ' RWF' }
                    }
                }
            }
        });
    }
}));
</script>
@endscript
