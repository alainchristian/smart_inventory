<x-app-layout>
    <div class="py-2 sm:py-6">
        <div class="max-w-3xl mx-auto px-0 sm:px-4 lg:px-8">
            <div class="mb-4 sm:mb-6 flex items-center gap-3">
                <a href="{{ route('shop.day-close.index') }}"
                   class="p-2 rounded-lg transition-colors"
                   style="background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold" style="color:var(--text);">Pending Expense Requests</h1>
                    <p class="mt-0.5 text-xs" style="color:var(--text-dim);">Warehouse requests awaiting approval</p>
                </div>
            </div>

            <livewire:shop.day-close.pending-requests />
        </div>
    </div>
</x-app-layout>
