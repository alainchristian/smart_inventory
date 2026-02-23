<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Returns</h1>
                        <p class="mt-1 text-xs text-gray-500">Process and manage customer returns</p>
                    </div>
                    <a href="{{ route('shop.returns.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Process Return
                    </a>
                </div>
            </div>

            <!-- Livewire Component (includes KPI cards + filters + table) -->
            <livewire:shop.returns.return-list />
        </div>
    </div>
</x-app-layout>
