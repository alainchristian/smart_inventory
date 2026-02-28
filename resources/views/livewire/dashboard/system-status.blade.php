<div class="card" wire:poll.60s>
  <div class="card-header" style="margin-bottom:0">
    <div>
      <div class="card-title">System Status</div>
      <div class="card-subtitle">Infrastructure health</div>
    </div>
    <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;
                 background:{{ $allOk ? 'var(--success-glow)' : 'var(--amber-glow)' }};
                 color:{{ $allOk ? 'var(--success)' : 'var(--amber)' }}">
      {{ $allOk ? 'Operational' : 'Degraded' }}
    </span>
  </div>

  <div style="display:flex;flex-direction:column;align-items:center;padding:16px 0">
    <div class="sys-ok-ring"
         @if(!$allOk) style="border-color:var(--amber);box-shadow:0 0 20px var(--amber-glow)" @endif>
      @if($allOk)
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             style="color:var(--success)"><polyline points="20 6 9 17 4 12"/></svg>
      @else
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             style="color:var(--amber)">
          <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
          <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
      @endif
    </div>
    <div style="font-size:14px;font-weight:700;color:{{ $allOk ? 'var(--success)' : 'var(--amber)' }}">
      {{ $allOk ? 'All Systems Operational' : 'Degraded State' }}
    </div>
    <div style="font-size:11.5px;color:var(--text-sub);margin-top:3px">
      {{ $criticalCount > 0 ? $criticalCount.' critical alert(s) open' : 'No critical issues' }}
    </div>
  </div>

  <div style="border-top:1px solid var(--border);padding-top:12px;display:flex;flex-direction:column;gap:6px">
    @foreach([
      ['ok' => $dbOk,    'label' => 'Database connections healthy'],
      ['ok' => true,     'label' => 'Barcode scanners online'],
      ['ok' => $posOk,   'label' => 'POS terminals responsive'],
      ['ok' => $queueOk, 'label' => 'Sync queues clear'],
    ] as $check)
    <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-sub)">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
           stroke="{{ $check['ok'] ? 'var(--success)' : 'var(--red)' }}" stroke-width="2.5">
        @if($check['ok'])
          <polyline points="20 6 9 17 4 12"/>
        @else
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        @endif
      </svg>
      {{ $check['label'] }}
    </div>
    @endforeach
  </div>
</div>
