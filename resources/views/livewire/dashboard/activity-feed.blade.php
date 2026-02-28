<div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-4 sm:p-5" wire:poll.30s>
    <!-- Header -->
    <div class="flex items-start justify-between mb-3.5 sm:mb-4.5">
        <div>
            <h2 class="text-[15px] font-bold" style="color: var(--text);">Live Activity</h2>
            <p class="text-xs mt-0.5" style="color: var(--text-sub);">Real-time operations trail</p>
        </div>
        <a href="{{ route('owner.activity-logs.index') }}"
           class="text-[13px] font-semibold px-2.5 py-1.5 rounded-lg transition-colors"
           style="color: var(--accent); background: var(--accent-dim);">
            Full Log
        </a>
    </div>

    <!-- Activity Timeline -->
    <div class="flex flex-col overflow-y-auto" style="max-height: 320px;">
        @forelse($activities as $index => $activity)
            <div class="flex gap-3 pb-3 {{ !$loop->last ? 'border-b' : '' }}" style="border-color: var(--border);">
                <!-- Timeline Thread -->
                <div class="flex flex-col items-center pt-0.5">
                    @php
                        $color = $this->getActionColor($activity->action);
                    @endphp
                    <div class="act-dot {{ $color }}"></div>
                    @if(!$loop->last)
                        <div class="act-line"></div>
                    @endif
                </div>

                <!-- Activity Content -->
                <div class="flex-1 min-w-0 pt-0.5">
                    <div class="text-[14px] leading-relaxed" style="color: var(--text-sub);">
                        {!! $this->formatDescription($activity) !!}
                    </div>
                    <div class="text-[12px] mt-0.5" style="color: var(--text-dim); font-family: var(--mono);">
                        {{ $activity->created_at->diffForHumans() }} · {{ $activity->user_name }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--text-sub);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm" style="color: var(--text-dim);">No activity logs yet</p>
            </div>
        @endforelse
    </div>
</div>
