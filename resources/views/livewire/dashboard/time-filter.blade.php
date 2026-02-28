<div class="time-filter-row">

    {{-- Segmented period buttons --}}
    <div class="time-seg">
        @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'quarter' => 'Quarter', 'year' => 'Year'] as $key => $label)
            <button class="time-seg-btn {{ $activePeriod === $key ? 'active' : '' }}"
                    wire:click="setPeriod('{{ $key }}')">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Custom range toggle --}}
    <button class="time-custom-btn" wire:click="$toggle('showCustom')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Custom Range
    </button>

    {{-- Inline date pickers --}}
    @if($showCustom)
    <div style="display:flex;align-items:center;gap:6px">
        <input type="date" wire:model="customFrom"
               style="padding:6px 10px;border:1px solid var(--border);border-radius:var(--rsm);
                      background:var(--surface);color:var(--text);font-size:12px">
        <span style="color:var(--text-dim);font-size:12px">→</span>
        <input type="date" wire:model="customTo"
               style="padding:6px 10px;border:1px solid var(--border);border-radius:var(--rsm);
                      background:var(--surface);color:var(--text);font-size:12px">
        <button wire:click="applyCustomRange"
                class="time-seg-btn active" style="border-radius:var(--rsm);padding:6px 14px">
            Apply
        </button>
    </div>
    @endif

    {{-- Currency chip --}}
    <div class="currency-chip">
        <span>🇷🇼</span> {{ $currency }}
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    </div>

</div>
