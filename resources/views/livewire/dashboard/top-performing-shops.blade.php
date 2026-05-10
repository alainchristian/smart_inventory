<div class="card" style="display:flex;flex-direction:column">
    <div class="card-header">
        <div>
            <div class="card-title">Top Performing Shops</div>
            <div class="card-subtitle">Revenue leaders this period</div>
        </div>
        <a href="{{ route('owner.shops.index') }}" class="card-btn">View all</a>
    </div>

    <div style="flex:1;overflow-y:auto;min-height:0">
        @forelse($topShops as $shop)
        @php
            $badge = match($shop['rank']) {
                1 => ['bg' => '#fef3c7',                   'color' => '#d97706', 'type' => 'trophy'],
                2 => ['bg' => 'rgba(59,111,212,.12)',       'color' => '#3b6fd4', 'type' => 'number'],
                default => ['bg' => 'rgba(249,115,22,.12)', 'color' => '#f97316', 'type' => 'number'],
            };
        @endphp
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0;
                    {{ !$loop->last ? 'border-bottom:1px solid var(--border)' : '' }}">
            <div style="width:26px;height:26px;border-radius:50%;background:{{ $badge['bg'] }};flex-shrink:0;
                        display:flex;align-items:center;justify-content:center">
                @if($badge['type'] === 'trophy')
                <svg width="14" height="14" viewBox="0 0 24 24" fill="{{ $badge['color'] }}" stroke="none">
                    <path d="M12 2l2.4 4.86 5.36.78-3.88 3.78.92 5.34L12 14.27l-4.8 2.49.92-5.34L4.24 7.64l5.36-.78z"/>
                </svg>
                @else
                <span style="font-size:12px;font-weight:800;color:{{ $badge['color'] }}">{{ $shop['rank'] }}</span>
                @endif
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:var(--text);
                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $shop['name'] }}</div>
                <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:1px">
                    {{ number_format($shop['revenue']) }} RWF
                </div>
            </div>
            <span style="font-size:12px;font-weight:700;white-space:nowrap;
                         color:{{ $shop['growth'] >= 0 ? '#0e9e86' : '#e11d48' }}">
                {{ $shop['growth'] >= 0 ? '↑' : '↓' }} {{ abs($shop['growth']) }}%
            </span>
        </div>
        @empty
        <div style="padding:30px 0;text-align:center;font-size:13px;color:var(--text-dim)">
            No sales this period
        </div>
        @endforelse
    </div>
</div>
