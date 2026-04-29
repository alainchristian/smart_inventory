<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 sm:mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold" style="color:var(--text);">Daily Close Report</h1>
                    <p class="mt-1 text-sm" style="color:var(--text-dim);">Review and lock daily sessions by date</p>
                </div>
                <a href="{{ route('owner.finance.overview') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium"
                   style="background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);">
                    Finance Overview →
                </a>
            </div>

            <livewire:owner.finance.daily-close-report />
        </div>
    </div>
</x-app-layout>
