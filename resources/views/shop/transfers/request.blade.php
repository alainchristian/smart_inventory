<x-app-layout>
    <div class="py-2 sm:py-6 lg:py-8">
        <div class="max-w-6xl mx-auto px-0 sm:px-4 lg:px-8">

            <div class="mb-4 sm:mb-6 flex items-center gap-3">
                <a href="{{ route('shop.transfers.index') }}"
                   class="p-2.5 rounded-lg flex-shrink-0"
                   style="background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold" style="color:var(--text);">New Stock Request</h1>
                    <p class="text-sm mt-0.5" style="color:var(--text-dim);">Request boxes from warehouse</p>
                </div>
            </div>

            <livewire:inventory.transfers.request-transfer />

        </div>
    </div>
</x-app-layout>
