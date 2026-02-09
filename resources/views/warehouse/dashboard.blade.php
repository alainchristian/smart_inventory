<x-app-layout>
    <!-- Warehouse Selector (for Owners) -->
    @if(auth()->user()->isOwner())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Warehouse</label>
            <form method="GET" action="{{ route('warehouse.dashboard') }}">
                <select name="warehouse_id"
                        onchange="this.form.submit()"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @foreach(\App\Models\Warehouse::orderBy('name')->get() as $wh)
                        <option value="{{ $wh->id }}" {{ $wh->id == $warehouse->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900">{{ $warehouse->name }}</h1>
        <p class="text-gray-500 text-xs mt-0.5">Warehouse inventory and operations management</p>
    </div>

    <!-- Compact KPI Cards -->
    <div class="mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Total Boxes -->
            <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Total Boxes</span>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stockStats['total_boxes']) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">All warehouse boxes</p>
            </div>

            <!-- Full Boxes -->
            <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Full Boxes</span>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stockStats['full_boxes']) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Ready to ship</p>
            </div>

            <!-- Partial Boxes -->
            <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Partial Boxes</span>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stockStats['partial_boxes']) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">In use</p>
            </div>

            <!-- Total Items -->
            <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Total Items</span>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stockStats['total_items']) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Individual units</p>
            </div>
        </div>
    </div>

    <!-- Inventory Value & Status Summary -->
    <div class="mb-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Inventory Value -->
            <div class="lg:col-span-2 bg-white rounded-lg p-3 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase mb-1">Inventory Value</p>
                        <p class="text-3xl font-bold text-gray-900">
                            @if($stockStats['inventory_value'] >= 1000000)
                                {{ number_format($stockStats['inventory_value'] / 1000000, 2) }}M
                            @else
                                {{ number_format($stockStats['inventory_value'] / 1000, 0) }}K
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">RWF (purchase prices)</p>
                    </div>
                    <div class="flex space-x-6">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-700">{{ $stockStats['empty_boxes'] }}</p>
                            <p class="text-xs text-gray-500">Empty</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-700">{{ $stockStats['damaged_boxes'] }}</p>
                            <p class="text-xs text-gray-500">Damaged</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expiring Soon Alert -->
            @if($expiringBoxes->count() > 0)
            <div class="bg-white rounded-lg p-3 shadow-sm border-2 border-orange-500">
                <div class="flex items-center space-x-2 mb-2">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold text-gray-900">Expiring Soon</span>
                </div>
                <p class="text-3xl font-bold text-orange-600">{{ $expiringBoxes->count() }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Boxes within 30 days</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Transfers Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
        <!-- Pending Approvals -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-gray-900">Pending Approvals</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-900">
                    {{ $pendingTransfers->count() }}
                </span>
            </div>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($pendingTransfers as $transfer)
                    <div class="p-2 bg-gray-50 rounded border-l-2 border-gray-400">
                        <p class="font-semibold text-gray-900 text-sm">{{ $transfer->transfer_number }}</p>
                        <p class="text-xs text-gray-600">To: {{ $transfer->toShop->name }}</p>
                        <p class="text-xs text-gray-500">{{ $transfer->requested_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 text-center py-4">No pending requests</p>
                @endforelse
            </div>
        </div>

        <!-- Awaiting Shipment -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-gray-900">Awaiting Shipment</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-900">
                    {{ $awaitingShipment->count() }}
                </span>
            </div>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($awaitingShipment as $transfer)
                    <div class="p-2 bg-gray-50 rounded border-l-2 border-blue-400">
                        <p class="font-semibold text-gray-900 text-sm">{{ $transfer->transfer_number }}</p>
                        <p class="text-xs text-gray-600">To: {{ $transfer->toShop->name }}</p>
                        <p class="text-xs text-gray-500">Approved {{ $transfer->reviewed_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 text-center py-4">No shipments pending</p>
                @endforelse
            </div>
        </div>

        <!-- In Transit -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-gray-900">In Transit</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-900">
                    {{ $inTransit->count() }}
                </span>
            </div>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($inTransit as $transfer)
                    <div class="p-2 bg-gray-50 rounded border-l-2 border-green-400">
                        <p class="font-semibold text-gray-900 text-sm">{{ $transfer->transfer_number }}</p>
                        <p class="text-xs text-gray-600">To: {{ $transfer->toShop->name }}</p>
                        <p class="text-xs text-gray-500">Shipped {{ $transfer->shipped_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 text-center py-4">No transfers in transit</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Low Stock & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
        <!-- Low Stock Products -->
        @if($lowStockProducts->count() > 0)
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200" id="low-stock-section">
            <h2 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                <svg class="w-4 h-4 text-orange-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Low Stock Products ({{ $lowStockProducts->count() }})
            </h2>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($lowStockProducts as $product)
                    @php
                        $totalStock = $product->boxes()->whereIn('status', ['full', 'partial'])->sum('items_remaining');
                    @endphp
                    <div class="p-2 bg-orange-50 rounded border-l-2 border-orange-400">
                        <p class="font-semibold text-gray-900 text-sm">{{ $product->name }}</p>
                        <p class="text-xs text-orange-700 font-bold">{{ $totalStock }} items (threshold: {{ $product->low_stock_threshold }})</p>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent Box Receipts -->
        <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
            <h2 class="text-sm font-bold text-gray-900 mb-3">Recent Box Receipts</h2>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($recentBoxes as $box)
                    <div class="flex items-start space-x-2 p-2 bg-gray-50 rounded">
                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-900 truncate">{{ $box->product->name }}</p>
                            <p class="text-xs text-gray-600">{{ $box->box_code }} • {{ $box->items_remaining }} items</p>
                            <p class="text-xs text-gray-500">Received {{ $box->received_at->diffForHumans() }} by {{ $box->receivedBy->name }}</p>
                        </div>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $box->status->value === 'full' ? 'bg-green-100 text-green-800' : ($box->status->value === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ $box->status->label() }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-xs text-gray-500">No recent box receipts</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- System Status Footer -->
    <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-1.5">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="font-semibold text-green-600">Operational</span>
                </div>
                <span class="text-gray-400">•</span>
                <span>Last updated {{ now()->diffForHumans() }}</span>
            </div>
            <button onclick="window.location.reload()" class="inline-flex items-center space-x-1 px-2 py-1 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition-colors text-xs font-medium">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Floating Action Button (FAB) with Quick Actions -->
    <x-floating-action-button>
        <x-slot name="actions">
            <a href="{{ route('warehouse.transfers.index') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition-colors">Approve Transfers</p>
                    <p class="text-xs text-gray-500">Review pending requests</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('warehouse.inventory.boxes') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-green-50 hover:to-green-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-green-700 transition-colors">Manage Boxes</p>
                    <p class="text-xs text-gray-500">View all boxes</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('warehouse.transfers.index') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Ship Orders</p>
                    <p class="text-xs text-gray-500">Process shipments</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            <a href="{{ route('warehouse.reports.inventory') }}"
               class="flex items-center space-x-3 px-5 py-3 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-indigo-100 transition-all duration-200 group border-b border-gray-100">
                <div class="w-11 h-11 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900 group-hover:text-indigo-700 transition-colors">View Reports</p>
                    <p class="text-xs text-gray-500">Analytics & insights</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            @if($pendingTransfers->count() > 0 || $lowStockProducts->count() > 0)
            <div class="px-5 py-2 bg-gray-100">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Attention Required</p>
            </div>

            @if($pendingTransfers->count() > 0)
            <a href="{{ route('warehouse.transfers.index') }}"
               class="flex items-center space-x-3 px-5 py-3 bg-gradient-to-r from-orange-50 to-red-50 hover:from-orange-100 hover:to-red-100 transition-all duration-200 group border-b border-orange-200">
                <div class="w-11 h-11 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md animate-pulse">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-orange-900 group-hover:text-red-700 transition-colors">Pending Approvals</p>
                    <p class="text-xs text-orange-700 font-semibold">{{ $pendingTransfers->count() }} transfers waiting</p>
                </div>
                <svg class="w-5 h-5 text-orange-600 group-hover:text-red-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            @endif

            @if($lowStockProducts->count() > 0)
            <a href="#low-stock-section"
               class="flex items-center space-x-3 px-5 py-3 bg-gradient-to-r from-yellow-50 to-amber-50 hover:from-yellow-100 hover:to-amber-100 transition-all duration-200 group">
                <div class="w-11 h-11 bg-gradient-to-br from-yellow-500 to-amber-500 rounded-xl flex items-center justify-center group-hover:scale-110 group-hover:shadow-lg transition-all duration-200 shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-yellow-900 group-hover:text-amber-700 transition-colors">Low Stock Alert</p>
                    <p class="text-xs text-yellow-700 font-semibold">{{ $lowStockProducts->count() }} products low</p>
                </div>
                <svg class="w-5 h-5 text-yellow-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            @endif
            @endif
        </x-slot>
    </x-floating-action-button>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>
