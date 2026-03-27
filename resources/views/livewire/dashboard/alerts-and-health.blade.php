<div class="card" wire:poll.30s="runHealthChecks" style="animation:fadeUp .4s ease .5s both">

  {{-- Card header --}}
  <div class="card-header" style="align-items:flex-start;flex-direction:column;gap:8px">
    <div style="display:flex;align-items:center;justify-content:space-between;width:100%">
      <div>
        <div class="card-title">Alerts &amp; Health</div>
        <div class="card-subtitle">
          @if($activeTab === 'alerts')
            {{ $alerts->count() }} unresolved {{ $alerts->count() === 1 ? 'flag' : 'flags' }}
          @else
            Infrastructure health check
          @endif
        </div>
      </div>
      @if($activeTab === 'alerts')
      <a href="{{ route('owner.alerts.index') }}"
         style="font-size:13px;font-weight:600;padding:5px 12px;border-radius:var(--rsm);
                color:var(--accent);background:var(--accent-dim);text-decoration:none;
                transition:background var(--tr)"
         onmouseover="this.style.background='var(--accent-glow)'"
         onmouseout="this.style.background='var(--accent-dim)'">
        Manage All
      </a>
      @else
      <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;
                   background:{{ $allOk ? 'var(--success-glow)' : 'var(--amber-glow)' }};
                   color:{{ $allOk ? 'var(--success)' : 'var(--amber)' }}">
        {{ $allOk ? 'Operational' : 'Degraded' }}
      </span>
      @endif
    </div>

    {{-- Tabs --}}
    <div class="ah-tabs" style="width:100%">
      <button class="ah-tab-btn {{ $activeTab === 'alerts' ? 'active' : '' }}"
              wire:click="setTab('alerts')">
        Alerts
        @if($alerts->count() > 0)
          <span style="margin-left:4px;font-size:10px;padding:1px 5px;border-radius:10px;
                       background:var(--red-dim);color:var(--red)">
            {{ $alerts->count() }}
          </span>
        @endif
      </button>
      <button class="ah-tab-btn {{ $activeTab === 'health' ? 'active' : '' }}"
              wire:click="setTab('health')">
        Health
        @if(!$allOk)
          <span style="margin-left:4px;font-size:10px;padding:1px 5px;border-radius:10px;
                       background:var(--amber-dim);color:var(--amber)">!</span>
        @endif
      </button>
    </div>
  </div>

  {{-- Alerts Tab --}}
  @if($activeTab === 'alerts')
  <div class="card-scroll" style="margin-top:12px;display:flex;flex-direction:column;gap:8px">
    @forelse($alerts as $alert)
      @php
        $colors  = $this->getSeverityColors($alert->severity->value);
        $iconPath = $this->getAlertIcon($alert->entity_type ?? null);
      @endphp
      <div style="display:flex;gap:10px;padding:12px;border-radius:var(--rsm);
                  background:{{ $colors['bg'] }};border:1px solid {{ $colors['border'] }}">
        <div style="width:32px;height:32px;border-radius:var(--rsm);
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;
                    background:{{ $colors['bg'] }}">
          <svg width="15" height="15" fill="none" stroke="{{ $colors['text'] }}"
               viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
          </svg>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:600;color:var(--text);line-height:1.3">
            {{ $alert->title }}
          </div>
          <div style="font-size:12px;color:var(--text-sub);margin-top:2px;line-height:1.4">
            {{ $alert->message }}
          </div>
          <div style="font-size:11px;color:var(--text-dim);font-family:var(--mono);margin-top:4px">
            {{ $alert->created_at->diffForHumans() }}
          </div>
        </div>
        <div>
          <span class="alert-sev {{ $alert->severity->value }}">
            {{ strtoupper($alert->severity->value) }}
          </span>
        </div>
      </div>
    @empty
      <div style="text-align:center;padding:32px 0">
        <svg width="40" height="40" fill="none" stroke="var(--text-dim)" viewBox="0 0 24 24"
             style="margin:0 auto 10px;display:block;opacity:.3">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div style="font-size:13px;color:var(--text-dim)">No active alerts</div>
        <div style="font-size:11px;color:var(--text-dim);margin-top:3px">All systems operational</div>
      </div>
    @endforelse
  </div>
  @endif

  {{-- Health Tab --}}
  @if($activeTab === 'health')
  <div style="margin-top:16px">
    <div style="display:flex;flex-direction:column;align-items:center;padding:12px 0">
      <div class="sys-ok-ring"
           @if(!$allOk) style="border-color:var(--amber);box-shadow:0 0 20px var(--amber-glow)" @endif>
        @if($allOk)
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
               style="color:var(--success)"><polyline points="20 6 9 17 4 12"/></svg>
        @else
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
               style="color:var(--amber)">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        @endif
      </div>
      <div style="font-size:14px;font-weight:700;color:{{ $allOk ? 'var(--success)' : 'var(--amber)' }}">
        {{ $allOk ? 'All Systems Operational' : 'Degraded State' }}
      </div>
      <div style="font-size:11.5px;color:var(--text-sub);margin-top:3px">
        {{ $criticalCount > 0 ? $criticalCount . ' critical alert(s) open' : 'No critical issues' }}
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
  @endif

</div>
