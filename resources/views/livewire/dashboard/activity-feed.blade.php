<div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-4 sm:p-5" wire:poll.37s>

    {{-- Header --}}
    <div class="flex items-start justify-between mb-4">
        <div>
            <h2 class="text-[15px] font-bold" style="color: var(--text);">Live Activity</h2>
            <p class="text-xs mt-0.5" style="color: var(--text-sub);">Real-time operations trail</p>
        </div>
        <a href="{{ route('owner.activity-logs.index') }}"
           class="text-[12px] font-semibold px-2.5 py-1.5 rounded-lg transition-colors"
           style="color: var(--accent); background: var(--accent-dim);">
            Full Log
        </a>
    </div>

    {{-- Timeline --}}
    <div class="overflow-y-auto card-scroll">
        @forelse($activities as $activity)
            @php
                $parsed  = $this->parseAction($activity);
                $context = $this->buildContext($activity);
                $label   = $parsed['label'];
                $icon    = $parsed['icon'];
                $color   = $parsed['color'];

                $iconBg = match($color) {
                    'green'   => 'background:var(--success-glow); color:var(--success)',
                    'blue'    => 'background:var(--accent-glow);  color:var(--accent)',
                    'amber'   => 'background:var(--amber-glow);   color:var(--amber)',
                    'red'     => 'background:var(--red-glow);     color:var(--red)',
                    default   => 'background:var(--surface2);     color:var(--text-dim)',
                };
            @endphp

            <div class="flex gap-3 py-2.5 {{ !$loop->last ? 'border-b' : '' }}"
                 style="border-color: var(--border);">

                {{-- Icon badge --}}
                <div class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center mt-0.5"
                     style="{{ $iconBg }}">
                    @if($icon === 'transfer')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    @elseif($icon === 'sale')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    @elseif($icon === 'return')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    @elseif($icon === 'box')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    @elseif($icon === 'check')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    @elseif($icon === 'warning')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    @elseif($icon === 'x')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    @elseif($icon === 'product')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    @elseif($icon === 'user')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @else
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">

                    {{-- Action label --}}
                    <p class="text-[13px] font-semibold leading-tight" style="color: var(--text);">
                        {{ $label }}
                    </p>

                    {{-- Context line: reference · shop · amount etc --}}
                    @if($context)
                        <p class="text-[11.5px] mt-0.5 truncate" style="color: var(--text-sub); font-family: var(--mono);">
                            {{ $context }}
                        </p>
                    @endif

                    {{-- Who + when --}}
                    <p class="text-[11px] mt-1" style="color: var(--text-dim);">
                        {{ $activity->user_name ?? 'System' }}
                        &nbsp;·&nbsp;
                        {{ $activity->created_at->diffForHumans() }}
                    </p>

                </div>
            </div>

        @empty
            <div class="text-center py-10">
                <svg class="w-10 h-10 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24" style="color: var(--text-sub);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-[13px]" style="color: var(--text-dim);">No activity yet</p>
                <p class="text-[11px] mt-1" style="color: var(--text-dim);">
                    Actions will appear here as operations happen
                </p>
            </div>
        @endforelse
    </div>
</div>
