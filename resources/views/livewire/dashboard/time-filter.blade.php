<div class="db-period-bar">

    {{-- Row 1: preset pills (no Custom button) --}}
    <div class="db-period-pills">
        @foreach([
            'today'      => 'Today',
            'yesterday'  => 'Yesterday',
            'week'       => 'This Week',
            'month'      => 'This Month',
            'last_month' => 'Last Month',
            'last_30'    => 'Last 30 Days',
        ] as $key => $label)
        <button class="db-period-pill {{ $preset === $key ? 'active' : '' }}"
                wire:click="setPreset('{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Row 2: always-visible date inputs + currency --}}
    <div class="db-period-controls">
        <div class="db-period-ctrl-seg db-period-ctrl-grow">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--text-dim)"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
            <input type="date" wire:model.live="dateFrom" class="db-date-input">
            <span style="font-size:13px;color:var(--text-dim);flex-shrink:0;">→</span>
            <input type="date" wire:model.live="dateTo" class="db-date-input">
        </div>
        <div class="db-period-ctrl-seg" style="font-size:12px;font-weight:600;color:var(--text-dim);">
            <span>&#127479;&#127484;</span> {{ $currency }}
        </div>
    </div>

</div>
