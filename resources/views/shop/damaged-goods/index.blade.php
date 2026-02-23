<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header with Breadcrumb -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <!-- Breadcrumb -->
                        <nav class="flex items-center text-xs text-gray-500 mb-1">
                            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                            <svg class="w-3.5 h-3.5 mx-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Damaged Goods</span>
                        </nav>
                        <h1 class="text-2xl font-bold text-gray-900">Damaged Goods</h1>
                        <p class="mt-1 text-xs text-gray-500">Track and manage damaged inventory items</p>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-6 flex items-start space-x-3">
                <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-bold text-blue-800">Manage Damaged Inventory</p>
                    <p class="text-xs text-blue-700 mt-1">
                        Review damaged items from returns, transfers, and warehouse audits. Decide on disposition: return to supplier, dispose, sell at discount, write off, or repair.
                    </p>
                </div>
            </div>

            <!-- Livewire Component -->
            <livewire:shop.damaged-goods.damaged-goods-list />
        </div>
    </div>
</x-app-layout>
