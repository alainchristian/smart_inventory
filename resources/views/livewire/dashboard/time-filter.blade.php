<div class="db-period-bar">

    <div class="db-period-pills">
        @foreach([
            'today'      => 'Today',
            'yesterday'  => 'Yesterday',
            'week'       => 'This Week',
            'month'      => 'This Month',
            'last_month' => 'Last Month',
            'last_30'    => 'Last 30 Days',
        ] as $key => $label)
        <button class="db-period-pill {{ $activePeriod === $key ? 'active' : '' }}"
                wire:click="setPeriod('{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>

    <button class="db-period-custom {{ $activePeriod === 'custom' ? 'active' : '' }}"
            wire:click="$toggle('showCustom')">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
        </svg>
        Custom Range
    </button>

    @if($showCustom)
    <div class="db-custom-picker">
        <span style="font-size:12px;color:var(--text-dim);white-space:nowrap">From</span>
        <input type="date" wire:model="customFrom" class="db-date-input">
        <span style="font-size:12px;color:var(--text-dim)">to</span>
        <input type="date" wire:model="customTo" class="db-date-input">
        <button wire:click="applyCustomRange" class="db-period-custom active">Apply</button>
    </div>
    @endif

    <div style="display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:7px;
                background:var(--surface);border:1px solid var(--border);
                font-size:12px;font-weight:600;color:var(--text-dim);white-space:nowrap;flex-shrink:0">
        <span>&#127479;&#127484;</span> {{ $currency }}
    </div>

</div>
