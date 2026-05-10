<div class="card" style="display:flex;flex-direction:column">
    <div class="card-header">
        <div>
            <div class="card-title">Recent Transactions</div>
            <div class="card-subtitle">Latest activity · all types</div>
        </div>
        <a href="{{ route('owner.reports.sales') }}" class="card-btn">View all</a>
    </div>

    <div style="flex:1;overflow-y:auto;min-height:0;padding-bottom:4px">
        @forelse($transactions as $tx)
        @php
            $ts = \Carbon\Carbon::parse($tx['ts']);
        @endphp
        <div style="display:flex;align-items:center;gap:12px;padding:10px 0;
                    {{ !$loop->last ? 'border-bottom:1px solid var(--border)' : '' }}">
            <div style="width:34px;height:34px;border-radius:9px;flex-shrink:0;
                        background:{{ $tx['bg'] }};
                        display:flex;align-items:center;justify-content:center">
                <svg width="14" height="14" fill="none" stroke="{{ $tx['color'] }}" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tx['icon'] }}"/>
                </svg>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:var(--text);
                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    {{ $tx['label'] }}
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:1px">
                    {{ $ts->format('M d, Y g:i A') }}
                </div>
            </div>
            <span style="font-size:13px;font-weight:700;font-family:var(--mono);
                         color:{{ $tx['color'] }};white-space:nowrap">
                {{ $tx['sign'] }}{{ number_format($tx['amount']) }} RWF
            </span>
        </div>
        @empty
        <div style="padding:30px 0;text-align:center;font-size:13px;color:var(--text-dim)">
            No recent transactions
        </div>
        @endforelse
    </div>
</div>
