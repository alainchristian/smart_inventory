<div>
    <!-- Page Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Warehouse Stock Levels</h2>
        <p class="mt-1 text-sm text-gray-600">View current inventory levels at {{ auth()->user()->location?->name }}</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Products</label>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search by name, SKU, or barcode..."
                       class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Stock Status</label>
                <select wire:model.live="statusFilter"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Products</option>
                    <option value="low">Low Stock Only</option>
                    <option value="out">Out of Stock Only</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Stock Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-4 md:mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 md:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm text-gray-600">Products</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900">{{ count($stockData) }}</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 md:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm text-gray-600">Total Boxes</p>
                    <p class="text-xl md:text-2xl font-bold text-green-600">{{ collect($stockData)->sum('full_boxes') + collect($stockData)->sum('partial_boxes') }}</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 md:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm text-gray-600">Low Stock</p>
                    <p class="text-xl md:text-2xl font-bold text-red-600">{{ collect($stockData)->where('is_low_stock', true)->count() }}</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 md:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm text-gray-600">Total Items</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-900">{{ number_format(collect($stockData)->sum('total_items'), 0) }}</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Mobile Card View -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($stockData as $data)
                <div class="border rounded-lg p-3 {{ $data['is_low_stock'] ? 'bg-red-50 border-red-200' : 'border-gray-200' }}">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">{{ $data['product']->name }}</div>
                            <div class="text-xs font-mono text-gray-600 mt-1">{{ $data['product']->barcode }}</div>
                        </div>
                        @if($data['total_items'] == 0)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Out</span>
                        @elseif($data['is_low_stock'])
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Low</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">OK</span>
                        @endif
                    </div>
                    <div class="grid grid-cols-3 gap-2 mt-2">
                        <div class="text-center p-2 bg-gray-50 rounded">
                            <div class="text-lg font-bold text-gray-900">{{ $data['full_boxes'] + $data['partial_boxes'] }}</div>
                            <div class="text-xs text-gray-600">Boxes</div>
                        </div>
                        <div class="text-center p-2 bg-green-50 rounded">
                            <div class="text-lg font-bold text-green-600">{{ $data['full_boxes'] }}</div>
                            <div class="text-xs text-gray-600">Full</div>
                        </div>
                        <div class="text-center p-2 bg-blue-50 rounded">
                            <div class="text-lg font-bold text-blue-600">{{ number_format($data['total_items'], 0) }}</div>
                            <div class="text-xs text-gray-600">Items</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-gray-500 text-sm font-medium">No products found</p>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Boxes</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Full</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Partial</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Items</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stockData as $data)
                        <tr class="{{ $data['is_low_stock'] ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $data['product']->name }}</div>
                                <div class="text-xs font-mono text-gray-500">{{ $data['product']->barcode }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-lg font-bold text-gray-900">{{ $data['full_boxes'] + $data['partial_boxes'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-semibold text-green-600">{{ $data['full_boxes'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-semibold text-yellow-600">{{ $data['partial_boxes'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm text-blue-600">{{ number_format($data['total_items'], 0) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($data['total_items'] == 0)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Out of Stock</span>
                                @elseif($data['is_low_stock'])
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Low Stock</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">In Stock</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium">No products found</p>
                                    <p class="text-gray-400 text-sm mt-1">Try adjusting your search or filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="px-4 md:px-6 py-4 border-t border-gray-200">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
