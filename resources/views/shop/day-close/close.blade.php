<x-app-layout>
    <div class="py-2 sm:py-6 lg:py-8">
        <div class="max-w-5xl mx-auto px-0 sm:px-4 lg:px-8">
            <div class="mb-4 sm:mb-6 flex items-center gap-3">
                <a href="{{ route('shop.day-close.index') }}"
                   class="p-2.5 rounded-lg flex-shrink-0"
                   style="background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold" style="color:var(--text);">Close Register</h1>
                    <div class="mt-0.5 flex items-center gap-2 flex-wrap">
                        @if ($session && $session->session_date->toDateString() !== today()->toDateString())
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:700;background:var(--amber-dim);color:var(--amber);border:1px solid var(--amber);">
                                <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                Old session — {{ $session->session_date->format('d M Y') }}
                            </span>
                        @endif
                        <p class="text-sm" style="color:var(--text-dim);">
                            {{ $session ? $session->session_date->format('d M Y') : 'Step through the closing wizard' }}
                        </p>
                    </div>
                </div>
            </div>

            <livewire:shop.day-close.close-wizard :dailySessionId="$session?->id ?? 0" />
        </div>
    </div>
</x-app-layout>
