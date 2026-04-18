<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 sm:mb-8 flex items-center gap-3">
                <a href="{{ route('shop.day-close.index') }}"
                   class="p-2.5 rounded-lg flex-shrink-0"
                   style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold" style="color:var(--text);">Session History</h1>
                    <p class="mt-0.5 text-sm" style="color:var(--text-dim);">Closed and locked daily sessions</p>
                </div>
            </div>

            <livewire:shop.day-close.session-history />
        </div>
    </div>
</x-app-layout>
