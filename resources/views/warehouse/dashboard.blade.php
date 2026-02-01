<x-app-layout>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $warehouse->name }}</h1>
        <p class="text-gray-600 text-sm">Warehouse inventory and operations management</p>
    </div>

    <!-- Stock Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Boxes -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Boxes</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stockStats['total_boxes']) }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Full Boxes -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Full Boxes</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stockStats['full_boxes']) }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Partial Boxes -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Partial Boxes</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stockStats['partial_boxes']) }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Items -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Items</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stockStats['total_items']) }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Value -->
    <div class="bg-white rounded-lg shadow p-6 mb-6 border-t-4 border-blue-600">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Warehouse Inventory Value</p>
                <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($stockStats['inventory_value'], 0) }}</p>
                <p class="text-sm text-gray-600 mt-2">RWF (Based on purchase prices)</p>
            </div>
            <div class="flex space-x-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $stockStats['empty_boxes'] }}</p>
                    <p class="text-xs text-gray-500">Empty Boxes</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $stockStats['damaged_boxes'] }}</p>
                    <p class="text-xs text-gray-500">Damaged</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfers Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Pending Approvals -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Pending Approvals</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-900">
                    {{ $pendingTransfers->count() }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse($pendingTransfers as $transfer)
                    <div class="p-3 bg-gray-50 rounded border-l-2 border-gray-400">
                        <p class="font-medium text-gray-900">{{ $transfer->transfer_number }}</p>
                        <p class="text-sm text-gray-600">To: {{ $transfer->toShop->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $transfer->requested_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No pending requests</p>
                @endforelse
            </div>
        </div>

        <!-- Awaiting Shipment -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Awaiting Shipment</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-900">
                    {{ $awaitingShipment->count() }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse($awaitingShipment as $transfer)
                    <div class="p-3 bg-gray-50 rounded border-l-2 border-gray-400">
                        <p class="font-medium text-gray-900">{{ $transfer->transfer_number }}</p>
                        <p class="text-sm text-gray-600">To: {{ $transfer->toShop->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">Approved {{ $transfer->reviewed_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No shipments pending</p>
                @endforelse
            </div>
        </div>

        <!-- In Transit -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">In Transit</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-900">
                    {{ $inTransit->count() }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse($inTransit as $transfer)
                    <div class="p-3 bg-gray-50 rounded border-l-2 border-gray-400">
                        <p class="font-medium text-gray-900">{{ $transfer->transfer_number }}</p>
                        <p class="text-sm text-gray-600">To: {{ $transfer->toShop->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">Shipped {{ $transfer->shipped_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No items in transit</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if($alerts->count() > 0)
        <div class="bg-white rounded-lg shadow p-6 mb-6 border-l-4 border-orange-500">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-orange-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                Alerts
            </h2>
            <div class="space-y-2">
                @foreach($alerts as $alert)
                    <div class="p-3 bg-orange-50 rounded border-l-2 border-orange-400">
                        <p class="font-medium text-gray-900">{{ $alert->title }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $alert->message }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Low Stock Products -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Low Stock Products</h2>
            <div class="space-y-3">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-sm text-gray-600">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">{{ $product->current_stock }}</p>
                            <p class="text-xs text-gray-500">Threshold: {{ $product->low_stock_threshold }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">All products well stocked</p>
                @endforelse
            </div>
        </div>

        <!-- Expiring Boxes -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Expiring Soon (30 Days)</h2>
            <div class="space-y-3">
                @forelse($expiringBoxes as $box)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $box->product->name }}</p>
                            <p class="text-sm text-gray-600">Box: {{ $box->box_code }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $box->items_remaining }} items remaining</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ $box->expiry_date ? $box->expiry_date->format('M d, Y') : 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $box->expiry_date ? $box->expiry_date->diffForHumans() : '' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No boxes expiring soon</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Box Receipts -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Box Receipts</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Box Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentBoxes as $box)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $box->box_code }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $box->product->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $box->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">{{ $box->items_remaining }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $box->received_at ? $box->received_at->format('M d, Y') : 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $box->expiry_date ? $box->expiry_date->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No recent receipts</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
