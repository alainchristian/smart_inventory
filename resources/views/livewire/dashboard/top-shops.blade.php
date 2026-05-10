<div class="card" style="display:flex;flex-direction:column">
    <div class="card-header">
        <div>
            <div class="card-title">Sales by Shop</div>
            <div class="card-subtitle">Revenue breakdown · RWF</div>
        </div>
        <a href="{{ route('owner.shops.index') }}" class="card-btn">View all</a>
    </div>

    <div style="flex:1;overflow-y:auto;min-height:0;padding-bottom:4px">
        @forelse($shops as $shop)
        <div style="padding:11px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border)' : '' }}">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:26px;height:26px;background:var(--accent-dim);border-radius:7px;flex-shrink:0;
                                display:flex;align-items:center;justify-content:center">
                        <svg width="13" height="13" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span style="font-size:12px;font-weight:600;color:var(--text)">{{ $shop['name'] }}</span>
                </div>
                <span style="font-size:11px;font-family:var(--mono);color:var(--text-dim)">
                    {{ number_format($shop['revenue']) }} RWF
                </span>
            </div>
            <div style="height:4px;background:var(--surface2);border-radius:10px;overflow:hidden">
                <div style="height:100%;border-radius:10px;background:var(--accent);
                            width:{{ $maxRevenue > 0 ? round($shop['revenue'] / $maxRevenue * 100) : 0 }}%">
                </div>
            </div>
        </div>
        @empty
        <div style="padding:20px 0;text-align:center;font-size:13px;color:var(--text-dim)">
            No sales this period
        </div>
        @endforelse
    </div>
</div>
