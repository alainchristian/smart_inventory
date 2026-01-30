<x-layouts.warehouse>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $warehouse->name }}</h1>
        <p class="text-sm text-gray-600 mt-1">Warehouse Manager Dashboard</p>
    </div>

    <!-- Stock Statistics - Top Row -->
    <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-4">
        <!-- Inventory Value -->
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl shadow-sm border border-amber-100 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Inventory Value</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($stockStats['inventory_value'], 0) }} <span class="text-xl text-gray-600">RWF</span></p>
                    <p class="text-sm text-gray-600 mt-1">{{ number_format($stockStats['total_items']) }} items in stock</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-600">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Boxes -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-sm border border-blue-100 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Total Boxes</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($stockStats['total_boxes']) }}</p>
                    <p class="text-sm text-gray-600 mt-1">In warehouse</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Full Boxes -->
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl shadow-sm border border-green-100 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Full Boxes</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($stockStats['full_boxes']) }}</p>
                    <p class="text-sm text-gray-600 mt-1">Ready for transfer</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-emerald-600">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl shadow-sm border border-purple-100 p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Pending Requests</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ $pendingTransfers->count() }}</p>
                    <p class="text-sm text-gray-600 mt-1">Need approval</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-600">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Box Status Overview -->
    <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Partial Boxes</h3>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-yellow-600">{{ $stockStats['partial_boxes'] }}</p>
            <p class="text-sm text-gray-600 mt-1">In use</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Empty Boxes</h3>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-600">{{ $stockStats['empty_boxes'] }}</p>
            <p class="text-sm text-gray-600 mt-1">Available</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Damaged Boxes</h3>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-red-600">{{ $stockStats['damaged_boxes'] }}</p>
            <p class="text-sm text-gray-600 mt-1">Need attention</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Box Capacity</h3>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            @php
                $usedBoxes = $stockStats['full_boxes'] + $stockStats['partial_boxes'];
                $capacity = $usedBoxes > 0 ? round(($usedBoxes / $stockStats['total_boxes']) * 100) : 0;
            @endphp
            <p class="text-3xl font-bold text-blue-600">{{ $capacity }}%</p>
            <p class="text-sm text-gray-600 mt-1">Utilization</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Pending Transfer Requests -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            Pending Transfer Requests
                            @if($pendingTransfers->count() > 0)
                                <span class="ml-2 px-2.5 py-1 text-xs font-bold bg-purple-100 text-purple-800 rounded-full">
                                    {{ $pendingTransfers->count() }}
                                </span>
                            @endif
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Awaiting your approval</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($pendingTransfers as $transfer)
                        <div class="p-4 bg-purple-50 border-l-4 border-purple-500 rounded-lg">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $transfer->transfer_number }}</p>
                                    <p class="text-sm text-gray-600 mt-1">To {{ $transfer->toShop->name }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $transfer->items->count() }} products • Requested by {{ $transfer->requestedBy->name }}</p>
                                    <p class="text-xs text-gray-500 mt-2">{{ $transfer->requested_at->diffForHumans() }}</p>
                                </div>
                                <a href="{{ route('warehouse.transfers.show', $transfer->id) }}" class="ml-4 px-4 py-2 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 transition-colors">
                                    Review
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500">No pending requests</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Awaiting Shipment -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            Awaiting Shipment
                            @if($awaitingShipment->count() > 0)
                                <span class="ml-2 px-2.5 py-1 text-xs font-bold bg-green-100 text-green-800 rounded-full">
                                    {{ $awaitingShipment->count() }}
                                </span>
                            @endif
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Approved, ready to pack</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($awaitingShipment as $transfer)
                        <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $transfer->transfer_number }}</p>
                                    <p class="text-sm text-gray-600 mt-1">To {{ $transfer->toShop->name }}</p>
                                    <p class="text-xs text-gray-500 mt-2">Approved {{ $transfer->reviewed_at->diffForHumans() }}</p>
                                </div>
                                <a href="{{ route('warehouse.transfers.pack', $transfer->id) }}" class="ml-4 px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                    Pack
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-500">No transfers ready for packing</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Transfers in Transit -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    In Transit
                    @if($inTransit->count() > 0)
                        <span class="ml-2 px-2.5 py-1 text-xs font-bold bg-blue-100 text-blue-800 rounded-full">
                            {{ $inTransit->count() }}
                        </span>
                    @endif
                </h3>
                <p class="text-sm text-gray-600 mt-1">Currently being delivered</p>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($inTransit as $transfer)
                        <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $transfer->transfer_number }}</p>
                                    <p class="text-sm text-gray-600 mt-1">To {{ $transfer->toShop->name }}</p>
                                    <p class="text-xs text-gray-600 mt-1">Via {{ $transfer->transporter->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 mt-2">Shipped {{ $transfer->shipped_at->diffForHumans() }}</p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                                    In Transit
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-500">No transfers in transit</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            Low Stock Alerts
                            @if($lowStockProducts->count() > 0)
                                <span class="ml-2 px-2.5 py-1 text-xs font-bold bg-orange-100 text-orange-800 rounded-full">
                                    {{ $lowStockProducts->count() }}
                                </span>
                            @endif
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Need restocking</p>
                    </div>
                    <a href="{{ route('warehouse.inventory.receive-boxes') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-600 to-orange-600 text-white text-sm font-semibold rounded-lg hover:from-amber-700 hover:to-orange-700 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Receive Boxes
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($lowStockProducts as $product)
                        <div class="p-4 bg-orange-50 border-l-4 border-orange-500 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-600 mt-1">SKU: {{ $product->sku }}</p>
                                    <div class="flex items-center mt-2 space-x-4">
                                        <span class="text-xs font-medium text-orange-600">Current: {{ $product->current_stock }}</span>
                                        <span class="text-xs text-gray-500">•</span>
                                        <span class="text-xs text-gray-600">Min: {{ $product->low_stock_threshold }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500">All products well-stocked</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row - Expiring Boxes & Recent Receipts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Expiring Boxes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    Expiring Soon
                    @if($expiringBoxes->count() > 0)
                        <span class="ml-2 px-2.5 py-1 text-xs font-bold bg-red-100 text-red-800 rounded-full">
                            {{ $expiringBoxes->count() }}
                        </span>
                    @endif
                </h3>
                <p class="text-sm text-gray-600 mt-1">Within 30 days</p>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($expiringBoxes as $box)
                        <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900">{{ $box->product->name }}</p>
                                    <p class="text-xs text-gray-600 mt-1">Box: {{ $box->box_code }}</p>
                                    <p class="text-xs text-red-600 mt-2 font-medium">
                                        Expires: {{ $box->expiry_date->format('M d, Y') }} ({{ $box->expiry_date->diffForHumans() }})
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $box->items_remaining }} items remaining</p>
                                </div>
                                <div class="ml-4">
                                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500">No boxes expiring soon</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Box Receipts -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Receipts</h3>
                <p class="text-sm text-gray-600 mt-1">Latest boxes received</p>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($recentBoxes as $box)
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900">{{ $box->product->name }}</p>
                                    <p class="text-xs text-gray-600 mt-1">Box: {{ $box->box_code }}</p>
                                    <div class="flex items-center mt-2 space-x-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if($box->status->value === 'full') bg-green-100 text-green-800
                                            @elseif($box->status->value === 'partial') bg-yellow-100 text-yellow-800
                                            @elseif($box->status->value === 'damaged') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $box->status->label() }}
                                        </span>
                                        <span class="text-xs text-gray-600">{{ $box->items_remaining }} items</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Received {{ $box->received_at->diffForHumans() }}
                                        @if($box->receivedBy)
                                            by {{ $box->receivedBy->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-500">No recent receipts</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    @if($alerts->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">System Alerts</h3>
                <p class="text-sm text-gray-600 mt-1">Important notifications</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($alerts as $alert)
                        <div class="flex items-start p-4 rounded-lg
                            @if($alert->severity->value === 'critical') bg-red-50 border-l-4 border-red-500
                            @elseif($alert->severity->value === 'warning') bg-orange-50 border-l-4 border-orange-500
                            @else bg-blue-50 border-l-4 border-blue-500
                            @endif">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">{{ $alert->title }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $alert->message }}</p>
                                <p class="text-xs text-gray-500 mt-2">{{ $alert->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-layouts.warehouse>
