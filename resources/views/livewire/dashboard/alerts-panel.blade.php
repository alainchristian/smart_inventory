<div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-4 sm:p-5" wire:poll.15s>
    <!-- Header -->
    <div class="flex items-start justify-between mb-3.5 sm:mb-4.5">
        <div>
            <h2 class="text-[15px] font-bold" style="color: var(--text);">Active Alerts</h2>
            <p class="text-xs mt-0.5" style="color: var(--text-sub);">
                {{ $alerts->count() }} unresolved {{ Str::plural('flag', $alerts->count()) }}
            </p>
        </div>
        <a href="{{ route('owner.alerts.index') }}"
           class="text-[13px] font-semibold px-2.5 py-1.5 rounded-lg transition-colors"
           style="color: var(--accent); background: var(--accent-dim);">
            Manage All
        </a>
    </div>

    <!-- Alerts List -->
    <div class="flex flex-col gap-2">
        @forelse($alerts as $alert)
            @php
                $colors = $this->getSeverityColors($alert->severity->value);
                $iconPath = $this->getAlertIcon($alert->entity_type ?? 'default');
            @endphp

            <div class="flex gap-2.5 p-3 rounded-lg border cursor-pointer transition-all hover:brightness-110"
                 style="background: {{ str_replace('border-', '', $colors['bg']) }}; border-color: {{ str_replace('text-', '', $colors['border']) }};">

                <!-- Icon -->
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                     style="background: {{ $colors['bg'] }}; color: {{ str_replace('text-', '', $colors['text']) }};">
                    <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                    </svg>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <h3 class="text-[14px] font-semibold leading-tight" style="color: var(--text);">
                        {{ $alert->title }}
                    </h3>
                    <p class="text-[13px] leading-relaxed mt-0.5" style="color: var(--text-sub);">
                        {{ $alert->message }}
                    </p>
                    <div class="text-[12px] mt-1" style="color: var(--text-dim); font-family: var(--mono);">
                        {{ $alert->created_at->diffForHumans() }}
                    </div>
                </div>

                <!-- Severity Badge -->
                <span class="alert-sev {{ $alert->severity->value }} self-start mt-0.5">
                    {{ strtoupper($alert->severity->value) }}
                </span>
            </div>
        @empty
            <div class="text-center py-12">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--text-sub);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm" style="color: var(--text-dim);">No active alerts</p>
                <p class="text-xs mt-1" style="color: var(--text-dim);">All systems operational</p>
            </div>
        @endforelse
    </div>
</div>
