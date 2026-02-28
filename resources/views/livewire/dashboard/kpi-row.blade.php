<div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-3.5">
        <!-- Total Active Boxes -->
        <div class="kpi-card-wrapper blue bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 cursor-default">
            <div class="flex items-start justify-between mb-3.5">
                <div class="w-10 h-10 rounded-[10px] flex items-center justify-center flex-shrink-0"
                     style="background: var(--accent-glow); color: var(--accent);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                @if($this->totalBoxes['delta'] != 0)
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                          style="background: {{ $this->totalBoxes['delta'] > 0 ? 'var(--success-glow)' : 'var(--red-glow)' }};
                                 color: {{ $this->totalBoxes['delta'] > 0 ? 'var(--success)' : 'var(--red)' }};">
                        {{ $this->totalBoxes['delta'] > 0 ? '↑' : '↓' }} {{ abs($this->totalBoxes['deltaPercentage']) }}%
                    </span>
                @endif
            </div>
            <div class="text-[28px] font-bold leading-none tracking-tight" style="color: var(--text); font-family: var(--font);">
                {{ number_format($this->totalBoxes['value']) }}
            </div>
            <div class="text-[13px] font-medium mt-1" style="color: var(--text-sub);">Total Active Boxes</div>
            <div class="text-[11px] mt-2" style="color: var(--text-dim); font-family: var(--mono);">
                vs last week
            </div>
        </div>

        <!-- Today's Sales Revenue -->
        <div class="kpi-card-wrapper green bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 cursor-default">
            <div class="flex items-start justify-between mb-3.5">
                <div class="w-10 h-10 rounded-[10px] flex items-center justify-center flex-shrink-0"
                     style="background: var(--green-glow); color: var(--green);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                @if($this->todaysSales['delta'] != 0)
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                          style="background: {{ $this->todaysSales['delta'] > 0 ? 'var(--success-glow)' : 'var(--red-glow)' }};
                                 color: {{ $this->todaysSales['delta'] > 0 ? 'var(--success)' : 'var(--red)' }};">
                        {{ $this->todaysSales['delta'] > 0 ? '↑' : '↓' }} {{ abs($this->todaysSales['deltaPercentage']) }}%
                    </span>
                @endif
            </div>
            <div class="text-[28px] font-bold leading-none tracking-tight" style="color: var(--text); font-family: var(--font);">
                RWF {{ number_format($this->todaysSales['value'], 0) }}
            </div>
            <div class="text-[13px] font-medium mt-1" style="color: var(--text-sub);">Today's Revenue</div>
            <div class="text-[11px] mt-2" style="color: var(--text-dim); font-family: var(--mono);">
                vs yesterday
            </div>
        </div>

        <!-- Active Transfers -->
        <div class="kpi-card-wrapper amber bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 cursor-default">
            <div class="flex items-start justify-between mb-3.5">
                <div class="w-10 h-10 rounded-[10px] flex items-center justify-center flex-shrink-0"
                     style="background: var(--amber-glow); color: var(--amber);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                @if($this->activeTransfers['value'] > 0)
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                          style="background: var(--amber-glow); color: var(--amber);">
                        {{ $this->activeTransfers['value'] }} pending
                    </span>
                @endif
            </div>
            <div class="text-[28px] font-bold leading-none tracking-tight" style="color: var(--text); font-family: var(--font);">
                {{ number_format($this->activeTransfers['value']) }}
            </div>
            <div class="text-[13px] font-medium mt-1" style="color: var(--text-sub);">Active Transfers</div>
            <div class="text-[11px] mt-2" style="color: var(--text-dim); font-family: var(--mono);">
                In transit & awaiting receipt
            </div>
        </div>

        <!-- Critical Alerts -->
        <div class="kpi-card-wrapper red bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 cursor-default">
            <div class="flex items-start justify-between mb-3.5">
                <div class="w-10 h-10 rounded-[10px] flex items-center justify-center flex-shrink-0"
                     style="background: var(--red-glow); color: var(--red);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                @if($this->criticalAlerts['delta'] != 0)
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                          style="background: {{ $this->criticalAlerts['delta'] > 0 ? 'var(--red-glow)' : 'var(--success-glow)' }};
                                 color: {{ $this->criticalAlerts['delta'] > 0 ? 'var(--red)' : 'var(--success)' }};">
                        {{ $this->criticalAlerts['delta'] > 0 ? '↑' : '↓' }} {{ abs($this->criticalAlerts['delta']) }} new
                    </span>
                @endif
            </div>
            <div class="text-[28px] font-bold leading-none tracking-tight" style="color: var(--text); font-family: var(--font);">
                {{ number_format($this->criticalAlerts['value']) }}
            </div>
            <div class="text-[13px] font-medium mt-1" style="color: var(--text-sub);">Critical Alerts</div>
            <div class="text-[11px] mt-2" style="color: var(--text-dim); font-family: var(--mono);">
                Requiring attention
            </div>
        </div>
    </div>
</div>
