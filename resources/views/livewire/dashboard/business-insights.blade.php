<div class="card" style="display:flex;flex-direction:column">
    <div class="card-header">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;background:var(--accent-dim);border-radius:8px;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="14" height="14" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div class="card-title" style="margin:0">Business Insights</div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:10px;flex:1">
        @foreach($insights as $insight)
        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 14px;
                    background:var(--surface2);border-radius:10px;
                    border-left:3px solid {{ $insight['color'] }}">
            <div style="width:30px;height:30px;border-radius:8px;background:{{ $insight['bg'] }};flex-shrink:0;
                        display:flex;align-items:center;justify-content:center;margin-top:1px">
                <svg width="13" height="13" fill="none" stroke="{{ $insight['color'] }}" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $insight['icon'] }}"/>
                </svg>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:var(--text);line-height:1.4">
                    {{ $insight['headline'] }}
                </div>
                <div style="font-size:12px;color:var(--text-dim);margin-top:3px">
                    {{ $insight['detail'] }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
