<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Transfer Requests</h1>
                        <p class="mt-1 text-sm text-gray-600">View and manage your inventory transfer requests</p>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm text-blue-700">
                            <strong>Transfer Workflow:</strong> Request transfers from the warehouse, track their status, and receive inventory when delivered.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Livewire Component -->
            <livewire:shop.transfers.transfers-list />
        </div>
    </div>
</x-app-layout>
