<div class="card" wire:poll.30s>
  <div class="card-header">
    <div>
      <div class="card-title">Transfer Status</div>
      <div class="card-subtitle">Live pipeline</div>
    </div>
    <a href="{{ route('owner.transfers.index') }}" class="card-btn">Manage</a>
  </div>

  @foreach([
    ['label'=>'Pending Approval', 'sub'=>'Awaiting warehouse review',   'count'=>$pendingApproval, 'color'=>'amber',  'status'=>'pending'],
    ['label'=>'In Transit',       'sub'=>'On the way to shops',          'count'=>$inTransit,       'color'=>'blue',   'status'=>'in_transit'],
    ['label'=>'Discrepancies',    'sub'=>'Missing or extra boxes found', 'count'=>$discrepancies,   'color'=>'red',    'status'=>'discrepancy'],
    ['label'=>'Delivered Today',  'sub'=>'Successfully received',        'count'=>$deliveredToday,  'color'=>'green',  'status'=>'received'],
  ] as $row)
  <a href="{{ route('owner.transfers.index', ['status' => $row['status']]) }}"
     style="display:flex;align-items:center;gap:12px;padding:12px 0;
            border-bottom:1px solid var(--border);text-decoration:none;
            cursor:pointer;transition:var(--tr)"
     onmouseover="this.style.background='var(--surface2)'"
     onmouseout="this.style.background=''">
    <div class="ts-icon {{ $row['color'] }}"
         style="width:38px;height:38px;border-radius:10px;display:grid;place-items:center;flex-shrink:0">
      @if($row['color'] === 'amber')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      @elseif($row['color'] === 'blue')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      @elseif($row['color'] === 'red')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      @else
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>
      @endif
    </div>
    <div style="flex:1">
      <div style="font-weight:600;font-size:13px;color:var(--text)">{{ $row['label'] }}</div>
      <div style="font-size:11px;color:var(--text-sub);margin-top:2px">{{ $row['sub'] }}</div>
    </div>
    <div class="ts-count {{ $row['color'] }}"
         style="font-size:22px;font-weight:700;font-family:var(--mono)">
      {{ $row['count'] }}
    </div>
  </a>
  @endforeach
</div>
