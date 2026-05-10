<div class="snap-grid">

    {{-- Panel 1: Cash Position --}}
    <div class="snap-panel">
        <div class="snap-panel-header">
            <span class="snap-panel-title">Cash Position</span>
            <a href="{{ route('owner.finance.overview') }}" class="snap-panel-link">View all</a>
        </div>
        <div>
            @foreach([
                ['Cash', $cashPos['cash'],         '#0e9e86', 'rgba(14,158,134,.12)',  'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                ['MoMo', $cashPos['mobile_money'],  '#3b6fd4', 'rgba(59,111,212,.12)',  'M12 18h.01M8 21l4-4 4 4M3 9l9-9 9 9M19 15a7 7 0 01-14 0'],
                ['Bank', $cashPos['bank'],          '#8b5cf6', 'rgba(139,92,246,.12)', 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5'],
            ] as [$label, $val, $c, $bg, $path])
            <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--border)">
                <div style="width:26px;height:26px;border-radius:7px;background:{{ $bg }};flex-shrink:0;
                            display:flex;align-items:center;justify-content:center">
                    <svg width="13" height="13" fill="none" stroke="{{ $c }}" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                    </svg>
                </div>
                <span style="flex:1;font-size:13px;color:var(--text)">{{ $label }}</span>
                <span style="font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text);white-space:nowrap">
                    {{ number_format($val) }} <span style="font-weight:400;font-size:10px;color:var(--text-dim)">RWF</span>
                </span>
            </div>
            @endforeach
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:9px 12px;background:var(--accent-dim);border-radius:8px;margin-top:10px">
            <span style="font-size:12px;font-weight:700;color:var(--accent)">Total Available</span>
            <span style="font-size:13px;font-weight:800;font-family:var(--mono);color:var(--accent);white-space:nowrap">
                {{ number_format($cashPos['total']) }} <span style="font-size:10px;font-weight:600">RWF</span>
            </span>
        </div>
    </div>

    {{-- Panel 2: Business Overview --}}
    <div class="snap-panel">
        <div class="snap-panel-header">
            <span class="snap-panel-title">Business Overview</span>
            <a href="{{ route('owner.shops.index') }}" class="snap-panel-link">View all</a>
        </div>
        <div>
            @foreach([
                ['Total Shops',     $bizOver['shops'],     '#3b6fd4', 'rgba(59,111,212,.12)',  'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['Total Customers', $bizOver['customers'], '#8b5cf6', 'rgba(139,92,246,.12)', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['Total Products',  $bizOver['products'],  '#0e9e86', 'rgba(14,158,134,.12)',  'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                ['Active Staff',    $bizOver['users'],     '#f97316', 'rgba(249,115,22,.12)',  'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ] as [$label, $val, $c, $bg, $path])
            <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--border)">
                <div style="width:26px;height:26px;border-radius:7px;background:{{ $bg }};flex-shrink:0;
                            display:flex;align-items:center;justify-content:center">
                    <svg width="13" height="13" fill="none" stroke="{{ $c }}" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                    </svg>
                </div>
                <span style="flex:1;font-size:13px;color:var(--text)">{{ $label }}</span>
                <span style="font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text)">
                    {{ number_format($val) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Panel 3: Warehouse Overview --}}
    <div class="snap-panel">
        <div class="snap-panel-header">
            <span class="snap-panel-title">Warehouse Overview</span>
            <a href="{{ route('owner.warehouses.index') }}" class="snap-panel-link">View all</a>
        </div>
        <div>
            @php
                $whRows = [
                    ['Total Stock Items', number_format($stockOver['items']),          'var(--text)',                                              '#3b6fd4', 'rgba(59,111,212,.12)',  'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['Stock Value',       number_format($stockOver['value']).' RWF',   'var(--accent)',                                            '#0e9e86', 'rgba(14,158,134,.12)',  'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['Low Stock Items',   (string)$stockOver['low_stock'],             $stockOver['low_stock'] > 0 ? 'var(--red)' : 'var(--green)', '#f97316', 'rgba(249,115,22,.12)',  'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['Out of Stock',      (string)$stockOver['out_of_stock'],          $stockOver['out_of_stock'] > 0 ? 'var(--red)' : 'var(--green)', '#e11d48', 'rgba(225,29,72,.12)', 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'],
                ];
            @endphp
            @foreach($whRows as $i => [$label, $val, $valColor, $c, $bg, $path])
            <div style="display:flex;align-items:center;gap:10px;padding:9px 0;
                        {{ $i < count($whRows)-1 ? 'border-bottom:1px solid var(--border)' : '' }}">
                <div style="width:26px;height:26px;border-radius:7px;background:{{ $bg }};flex-shrink:0;
                            display:flex;align-items:center;justify-content:center">
                    <svg width="13" height="13" fill="none" stroke="{{ $c }}" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                    </svg>
                </div>
                <span style="flex:1;font-size:13px;color:var(--text)">{{ $label }}</span>
                <span style="font-size:12px;font-weight:700;font-family:var(--mono);color:{{ $valColor }};white-space:nowrap">
                    {{ $val }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Panel 4: Credit Exposure --}}
    <div class="snap-panel"
         x-data="creditDonut()"
         x-init="init()"
         data-outstanding="{{ $creditEx['outstanding'] }}"
         data-repaid="{{ $creditEx['repaid'] }}"
         style="display:flex;flex-direction:column">
        <div class="snap-panel-header">
            <span class="snap-panel-title">Credit Exposure</span>
            <a href="{{ route('owner.credit.writeoffs') }}" class="snap-panel-link">View all</a>
        </div>

        {{-- Content fills remaining height, centred --}}
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px">

            {{-- Donut --}}
            <div style="position:relative;width:120px;height:120px">
                <canvas id="creditDonutCanvas" width="120" height="120" wire:ignore></canvas>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;
                            align-items:center;justify-content:center;pointer-events:none;text-align:center;padding:8px">
                    <div style="font-size:11px;font-weight:800;color:var(--text);font-family:var(--mono);line-height:1.2;word-break:break-all">
                        {{ number_format($creditEx['outstanding']) }}
                    </div>
                    <div style="font-size:9px;color:var(--text-dim);margin-top:2px;white-space:nowrap">RWF</div>
                    <div style="font-size:9px;color:var(--text-dim);white-space:nowrap">Outstanding</div>
                </div>
            </div>

            {{-- Legend — two rows side by side --}}
            @php $tot = max($creditEx['total'] ?? ($creditEx['repaid'] + $creditEx['outstanding']), 1); @endphp
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;width:100%">
                <div style="display:flex;align-items:flex-start;gap:6px;
                            padding:8px 10px;background:rgba(14,158,134,.06);border-radius:8px;
                            border:1px solid rgba(14,158,134,.15)">
                    <div style="width:8px;height:8px;border-radius:50%;background:#0e9e86;flex-shrink:0;margin-top:3px"></div>
                    <div style="min-width:0">
                        <div style="font-size:10px;color:var(--text-dim);margin-bottom:2px">Paid</div>
                        <div style="font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ number_format($creditEx['repaid']) }}
                        </div>
                        <div style="font-size:10px;color:#0e9e86;font-weight:600">{{ round($creditEx['repaid']/$tot*100) }}%</div>
                    </div>
                </div>
                <div style="display:flex;align-items:flex-start;gap:6px;
                            padding:8px 10px;background:rgba(217,119,6,.06);border-radius:8px;
                            border:1px solid rgba(217,119,6,.15)">
                    <div style="width:8px;height:8px;border-radius:50%;background:#d97706;flex-shrink:0;margin-top:3px"></div>
                    <div style="min-width:0">
                        <div style="font-size:10px;color:var(--text-dim);margin-bottom:2px">Pending</div>
                        <div style="font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ number_format($creditEx['outstanding']) }}
                        </div>
                        <div style="font-size:10px;color:#d97706;font-weight:600">{{ round($creditEx['outstanding']/$tot*100) }}%</div>
                    </div>
                </div>
            </div>

        </div>
    </div>


</div>

@script
<script>
Alpine.data('creditDonut', () => ({
    init() {
        var self = this;
        this.$nextTick(() => self.draw());
        this.$wire.$watch('creditEx', () => self.$nextTick(() => self.draw()));
    },
    draw() {
        var canvas = document.getElementById('creditDonutCanvas');
        if (!canvas) return;
        var ex = Chart.getChart(canvas);
        if (ex) ex.destroy();
        var outstanding = parseFloat(this.$el.dataset.outstanding) || 0;
        var repaid      = parseFloat(this.$el.dataset.repaid)      || 0;
        var total       = outstanding + repaid;
        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending'],
                datasets: [{
                    data: total > 0 ? [repaid, outstanding] : [1, 0],
                    backgroundColor: total > 0 ? ['#0e9e86', '#d97706'] : ['#e2e6f3'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: false,
                cutout: '68%',
                animation: false,
                plugins: { legend: { display: false }, tooltip: { enabled: total > 0 } }
            }
        });
    }
}));
</script>
@endscript
