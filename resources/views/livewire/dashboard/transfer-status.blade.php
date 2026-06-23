<div style="background:var(--surface);border:1px solid var(--border);
            border-radius:var(--r);overflow:hidden;
            display:flex;flex-direction:column" wire:poll.29s>

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:16px 20px;border-bottom:1px solid var(--border)">
        <div>
            <div style="font-size:18px;font-weight:700;color:var(--text)">Transfer Status</div>
            <div style="font-size:14px;color:var(--text-dim);margin-top:2px">Live pipeline</div>
        </div>
        <a href="{{ route('owner.transfers.index') }}"
           style="font-size:14px;font-weight:600;color:var(--accent);
                  text-decoration:none;padding:4px 10px;border-radius:7px;
                  background:var(--accent-dim)">
            Manage
        </a>
    </div>

    {{-- Status rows --}}
    @php
        $statuses = [
            [
                'label'    => 'Pending Approval',
                'sub'      => 'Awaiting warehouse review',
                'count'    => $pendingApproval,
                'color'    => 'var(--amber)',
                'bg'       => 'var(--amber-dim)',
                'icon'     => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'link'     => route('owner.transfers.index') . '?status=pending',
            ],
            [
                'label'    => 'In Transit',
                'sub'      => 'On the way to shops',
                'count'    => $inTransit,
                'color'    => 'var(--accent)',
                'bg'       => 'var(--accent-dim)',
                'icon'     => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10',
                'link'     => route('owner.transfers.index') . '?status=in_transit',
            ],
            [
                'label'    => 'Discrepancies',
                'sub'      => 'Missing or extra boxes found',
                'count'    => $discrepancies,
                'color'    => 'var(--red)',
                'bg'       => 'var(--red-dim)',
                'icon'     => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'link'     => route('owner.transfers.index'),
            ],
            [
                'label'    => 'Delivered Today',
                'sub'      => 'Successfully received',
                'count'    => $deliveredToday,
                'color'    => 'var(--green)',
                'bg'       => 'var(--green-dim)',
                'icon'     => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'link'     => route('owner.transfers.index') . '?status=delivered',
            ],
        ];
    @endphp

    <div class="card-scroll">
        @foreach($statuses as $s)
        <a href="{{ $s['link'] }}"
           style="display:flex;align-items:center;gap:12px;
                  padding:11px 20px;text-decoration:none;
                  border-bottom:1px solid var(--border);
                  transition:background var(--tr)"
           onmouseover="this.style.background='var(--surface2)'"
           onmouseout="this.style.background='transparent'">

            <div style="width:34px;height:34px;border-radius:9px;flex-shrink:0;
                        background:{{ $s['bg'] }};
                        display:flex;align-items:center;justify-content:center">
                <svg width="15" height="15" fill="none" stroke="{{ $s['color'] }}"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
                </svg>
            </div>

            <div style="flex:1;min-width:0">
                <div style="font-size:16px;font-weight:600;color:var(--text)">{{ $s['label'] }}</div>
                <div style="font-size:13px;color:var(--text-dim);margin-top:1px">{{ $s['sub'] }}</div>
            </div>

            <div style="min-width:26px;height:26px;border-radius:50%;flex-shrink:0;
                        background:{{ $s['bg'] }};
                        display:flex;align-items:center;justify-content:center;
                        font-size:16px;font-weight:800;font-family:var(--mono);
                        color:{{ $s['color'] }};padding:0 6px">
                {{ $s['count'] }}
            </div>

        </a>
        @endforeach
    </div>

    {{-- Recent active transfers --}}
    <div style="border-top:1px solid var(--border)">
        <div style="padding:8px 20px 4px;font-size:12px;font-weight:700;
                    letter-spacing:.5px;text-transform:uppercase;
                    color:var(--text-dim)">
            Recent Active
        </div>

        @forelse($recentTransfers as $t)
        @php
            $sc = match($t['status']) {
                'pending'    => ['bg'=>'var(--amber-dim)',  'c'=>'var(--amber)'],
                'approved'   => ['bg'=>'var(--accent-dim)', 'c'=>'var(--accent)'],
                'in_transit' => ['bg'=>'var(--violet-dim)', 'c'=>'var(--violet)'],
                'delivered'  => ['bg'=>'var(--green-dim)',  'c'=>'var(--green)'],
                default      => ['bg'=>'var(--surface2)',   'c'=>'var(--text-dim)'],
            };
        @endphp
        <a href="{{ route('owner.transfers.show', $t['id']) }}"
           style="display:flex;align-items:center;justify-content:space-between;
                  padding:9px 20px;text-decoration:none;gap:10px;
                  border-top:1px solid var(--border);
                  transition:background var(--tr)"
           onmouseover="this.style.background='var(--surface2)'"
           onmouseout="this.style.background='transparent'">
            <div style="min-width:0;flex:1">
                <div style="font-size:16px;font-weight:600;color:var(--text);
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ $t['from'] }}
                    <span style="color:var(--text-dim)"> → </span>
                    {{ $t['to'] }}
                </div>
                <div style="font-size:13px;color:var(--text-dim);
                            font-family:var(--mono);margin-top:1px">
                    {{ $t['age'] }}
                </div>
            </div>
            <span style="font-size:13px;font-weight:700;padding:2px 8px;
                         border-radius:20px;white-space:nowrap;flex-shrink:0;
                         background:{{ $sc['bg'] }};color:{{ $sc['c'] }}">
                {{ ucfirst(str_replace('_',' ',$t['status'])) }}
            </span>
        </a>
        @empty
        <div style="padding:12px 20px;border-top:1px solid var(--border);
                    font-size:14px;color:var(--text-dim);text-align:center">
            No active transfers right now
        </div>
        @endforelse
    </div>

</div>

<style>
/* Responsive base — applied to all transfer pages */
@media(max-width:600px) {
    /* Cards */
    .tl-card, .rf-card {
        border-radius:var(--rsm, 8px);
    }
    /* Tables inside cards — make them scroll horizontally */
    table {
        display:block;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        white-space:nowrap;
    }
    /* Prevent text overflow on narrow screens */
    .tl-num, .rf-prod-name, .tl-route-node {
        max-width:140px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    /* Badges wrap instead of overflow */
    .tl-card-meta, .tl-dates {
        flex-wrap:wrap;
        gap:4px;
    }
}
@media(max-width:900px) {
    .tl-pipeline { grid-template-columns: repeat(3, 1fr); }
}
@media(max-width:600px) {
    .tl-pipeline { grid-template-columns: repeat(2, 1fr); gap:0; }
    .tl-pipeline-step { padding:10px 12px; }
    .tl-step-num  { font-size:20px; }
    .tl-step-sub  { display:none; }
    .tl-card-top    { flex-direction:column; padding:0 14px; }
    .tl-card-stats  { border-left:none; border-top:1px solid var(--border); margin:0 0 8px; flex-wrap:wrap; }
    .tl-stat        { padding:8px 14px; flex:1; min-width:80px; }
    .tl-bar         { gap:4px; padding:8px 10px; }
    .tl-chip        { padding:4px 10px; font-size:11px; }
    .tl-search      { width:100%; margin-left:0; margin-top:6px; }
    .tl-search input{ width:100%; }
    .tl-route-dash-line { width:20px; }
    .tl-card-foot   { flex-wrap:wrap; gap:6px; }
    .tl-action      { flex:1; justify-content:center; }
    .tl-foot-time   { width:100%; text-align:center; margin-left:0; }
    .tl-page-header         { flex-direction:column; align-items:flex-start; }
    .tl-page-header-left h1 { font-size:20px; }
    .tl-new-btn             { width:100%; justify-content:center; }
    .rf-row2 { grid-template-columns:1fr; }
    .rf-prod-row    { flex-wrap:wrap; gap:8px; }
    .rf-prod-info   { width:100%; }
    .rf-stock       { align-items:flex-start; }
    .rf-add-btn     { width:100%; justify-content:center; }
    .rf-item-top    { flex-wrap:wrap; }
    .rf-qty-ctrl    { width:100%; justify-content:space-between; }
}
@media(max-width:860px) {
    .rf-layout { grid-template-columns:1fr; }
    .rf-summary { position:static; }
}
</style>
