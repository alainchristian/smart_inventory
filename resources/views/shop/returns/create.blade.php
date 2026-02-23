<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header with Breadcrumb -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <!-- Breadcrumb -->
                        <nav class="flex items-center text-xs text-gray-500 mb-1">
                            <a href="{{ route('shop.returns.index') }}" class="hover:text-indigo-600 transition-colors">Returns</a>
                            <svg class="w-3.5 h-3.5 mx-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Process Return</span>
                        </nav>
                        <h1 class="text-2xl font-bold text-gray-900">Process Return</h1>
                        <p class="mt-1 text-xs text-gray-500">Process a customer return or exchange</p>
                    </div>
                    <a href="{{ route('shop.returns.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Returns
                    </a>
                </div>
            </div>

            <!-- Livewire Component -->
            <livewire:shop.returns.process-return />
        </div>
    </div>
</x-app-layout>
