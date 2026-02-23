<div x-data="{ expandedRow: null }">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed top-4 right-4 z-50 max-w-sm"
        >
            <div class="bg-green-50 border border-green-300 rounded-lg px-4 py-3 shadow-lg flex items-center space-x-3">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <!-- Total Damaged -->
        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-gray-500 uppercase">Total Damaged</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($kpiStats['total_damaged']) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ number_format($kpiStats['total_quantity']) }} units</p>
        </div>

        <!-- Pending Decision -->
        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-gray-500 uppercase">Pending</span>
            </div>
            <p class="text-2xl font-bold {{ $kpiStats['pending_count'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ number_format($kpiStats['pending_count']) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Awaiting disposition</p>
        </div>

        <!-- Total Loss -->
        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-gray-500 uppercase">Est. Loss</span>
            </div>
            <p class="text-xl font-bold text-red-600">
                @if($kpiStats['total_loss'] >= 1000000)
                    {{ number_format($kpiStats['total_loss'] / 1000000, 1) }}M
                @elseif($kpiStats['total_loss'] >= 1000)
                    {{ number_format($kpiStats['total_loss'] / 1000, 0) }}K
                @else
                    {{ number_format($kpiStats['total_loss']) }}
                @endif
            </p>
            <p class="text-xs text-gray-500 mt-0.5">RWF value lost</p>
        </div>

        <!-- Location Info -->
        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200">
            <div class="flex items-center space-x-2 mb-2">
                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-gray-500 uppercase">Location</span>
            </div>
            <p class="text-lg font-bold text-gray-900 truncate">{{ $locationName }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $isOwner ? 'All Locations' : 'Your Location' }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ $isOwner ? '6' : '5' }} gap-4">
            <!-- Search -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Search</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Reference #, product..."
                        class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                    >
                </div>
            </div>

            <!-- Disposition Filter -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Disposition</label>
                <select
                    wire:model.live="dispositionFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                >
                    <option value="all">All Dispositions</option>
                    <option value="pending">Pending Decision</option>
                    <option value="return_to_supplier">Return to Supplier</option>
                    <option value="dispose">Dispose</option>
                    <option value="discount_sale">Discount Sale</option>
                    <option value="write_off">Write Off</option>
                    <option value="repair">Repair</option>
                </select>
            </div>

            <!-- Location Filter (Owner Only) -->
            @if($isOwner)
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Location</label>
                    <select
                        wire:model.live="locationFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                    >
                        <option value="all">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location['id'] }}">{{ $location['name'] }} ({{ $location['type'] }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Date From -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">From Date</label>
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                >
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">To Date</label>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                >
            </div>
        </div>

        <!-- Reset Button -->
        <div class="mt-4 flex justify-end">
            <button
                wire:click="resetFilters"
                class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reset Filters
            </button>
        </div>
    </div>

    <!-- Damaged Goods Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Reference</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden md:table-cell">Source</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Quantity</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Est. Loss</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Disposition</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden md:table-cell">Date</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($damagedGoods as $damagedGood)
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer" @click="expandedRow === {{ $damagedGood->id }} ? expandedRow = null : expandedRow = {{ $damagedGood->id }}">
                            <!-- Reference -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $damagedGood->damage_reference }}</div>
                            </td>

                            <!-- Product -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $damagedGood->product->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">{{ $damagedGood->product->sku ?? '—' }}</div>
                            </td>

                            <!-- Source -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap hidden md:table-cell">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                    {{ ucfirst(str_replace('_', ' ', $damagedGood->source_type)) }}
                                </span>
                            </td>

                            <!-- Quantity -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap">
                                <div class="text-sm font-bold text-red-600">{{ $damagedGood->quantity_damaged }}</div>
                            </td>

                            <!-- Estimated Loss -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap hidden lg:table-cell">
                                <div class="text-sm font-bold text-gray-900">RWF {{ number_format($damagedGood->estimated_loss) }}</div>
                            </td>

                            <!-- Disposition -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap">
                                @php
                                    $dispositionColors = [
                                        'pending' => 'bg-amber-100 text-amber-700 border-amber-300',
                                        'return_to_supplier' => 'bg-blue-100 text-blue-700 border-blue-300',
                                        'dispose' => 'bg-red-100 text-red-700 border-red-300',
                                        'discount_sale' => 'bg-green-100 text-green-700 border-green-300',
                                        'write_off' => 'bg-gray-100 text-gray-700 border-gray-300',
                                        'repair' => 'bg-purple-100 text-purple-700 border-purple-300',
                                    ];
                                    $dispositionColor = $dispositionColors[$damagedGood->disposition->value] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold border {{ $dispositionColor }}">
                                    {{ $damagedGood->disposition->label() }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap text-xs text-gray-600 hidden md:table-cell">
                                {{ $damagedGood->recorded_at->format('M d, Y') }}
                            </td>

                            <!-- Actions -->
                            <td class="px-4 lg:px-6 py-3 whitespace-nowrap text-sm" @click.stop>
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="expandedRow === {{ $damagedGood->id }} ? expandedRow = null : expandedRow = {{ $damagedGood->id }}"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                                    >
                                        <span x-text="expandedRow === {{ $damagedGood->id }} ? 'Hide' : 'View'">View</span>
                                        <svg class="w-3.5 h-3.5 ml-1 transition-transform" :class="{ 'rotate-180': expandedRow === {{ $damagedGood->id }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    @if($damagedGood->isPending())
                                        <button
                                            wire:click="openDispositionModal({{ $damagedGood->id }})"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            Decide
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- Expandable Detail Row -->
                        <tr x-show="expandedRow === {{ $damagedGood->id }}" x-collapse x-cloak>
                            <td colspan="8" class="px-4 lg:px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <!-- Damage Details -->
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-600 uppercase mb-2">Damage Details</h4>
                                        <div class="bg-white rounded-lg border border-gray-200 p-3 space-y-2">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Description</span>
                                                <span class="font-medium text-gray-900 text-right">{{ $damagedGood->damage_description }}</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Recorded by</span>
                                                <span class="font-medium text-gray-900">{{ $damagedGood->recordedBy->name }}</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500">Recorded at</span>
                                                <span class="font-medium text-gray-900">{{ $damagedGood->recorded_at->format('M d, Y g:i A') }}</span>
                                            </div>
                                            @if($damagedGood->disposition_decided_at)
                                                <div class="pt-2 border-t border-gray-100">
                                                    <div class="flex justify-between text-sm">
                                                        <span class="text-gray-500">Decided by</span>
                                                        <span class="font-medium text-gray-900">{{ $damagedGood->dispositionDecidedBy->name ?? '—' }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-sm mt-1">
                                                        <span class="text-gray-500">Decided at</span>
                                                        <span class="font-medium text-gray-900">{{ $damagedGood->disposition_decided_at->format('M d, Y g:i A') }}</span>
                                                    </div>
                                                    @if($damagedGood->disposition_notes)
                                                        <div class="mt-2">
                                                            <span class="text-xs font-bold text-gray-500 uppercase">Notes</span>
                                                            <p class="text-sm text-gray-700 mt-1">{{ $damagedGood->disposition_notes }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Photos -->
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-600 uppercase mb-2">Photos</h4>
                                        @if($damagedGood->photos && count($damagedGood->photos) > 0)
                                            <div class="grid grid-cols-2 gap-2">
                                                @foreach($damagedGood->photos as $photo)
                                                    <div class="relative group">
                                                        <img src="{{ asset('storage/' . $photo) }}" alt="Damage photo" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                                                        <a href="{{ asset('storage/' . $photo) }}" target="_blank" class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center rounded-lg">
                                                            <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="bg-white rounded-lg border border-gray-200 p-6 text-center">
                                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <p class="text-sm text-gray-500">No photos uploaded</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900">No damaged goods found</h3>
                                    <p class="mt-1 text-sm text-gray-500">No damaged goods match your current filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($damagedGoods->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $damagedGoods->links() }}
            </div>
        @endif
    </div>

    <!-- Disposition Decision Modal -->
    @if($showDispositionModal && $selectedDamagedGood)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-on:keydown.escape="$wire.closeDispositionModal()">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" wire:click="closeDispositionModal"></div>

            <!-- Modal Content -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Decide Disposition</h3>
                        <button wire:click="closeDispositionModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 py-4">
                        <!-- Damaged Good Info -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $selectedDamagedGood->product->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $selectedDamagedGood->damage_reference }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-red-600">{{ $selectedDamagedGood->quantity_damaged }} units</p>
                                    <p class="text-xs text-gray-500 mt-0.5">RWF {{ number_format($selectedDamagedGood->estimated_loss) }} loss</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 mt-2 italic">{{ $selectedDamagedGood->damage_description }}</p>
                        </div>

                        <!-- Disposition Options -->
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Select Disposition *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    wire:click="$set('dispositionDecision', 'return_to_supplier')"
                                    class="flex items-center p-3 rounded-lg border-2 transition-all {{ $dispositionDecision === 'return_to_supplier' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <svg class="w-5 h-5 mr-2 {{ $dispositionDecision === 'return_to_supplier' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                    <span class="text-sm font-medium {{ $dispositionDecision === 'return_to_supplier' ? 'text-blue-700' : 'text-gray-700' }}">Return to Supplier</span>
                                </button>

                                <button
                                    wire:click="$set('dispositionDecision', 'dispose')"
                                    class="flex items-center p-3 rounded-lg border-2 transition-all {{ $dispositionDecision === 'dispose' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <svg class="w-5 h-5 mr-2 {{ $dispositionDecision === 'dispose' ? 'text-red-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span class="text-sm font-medium {{ $dispositionDecision === 'dispose' ? 'text-red-700' : 'text-gray-700' }}">Dispose</span>
                                </button>

                                <button
                                    wire:click="$set('dispositionDecision', 'discount_sale')"
                                    class="flex items-center p-3 rounded-lg border-2 transition-all {{ $dispositionDecision === 'discount_sale' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <svg class="w-5 h-5 mr-2 {{ $dispositionDecision === 'discount_sale' ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium {{ $dispositionDecision === 'discount_sale' ? 'text-green-700' : 'text-gray-700' }}">Discount Sale</span>
                                </button>

                                <button
                                    wire:click="$set('dispositionDecision', 'write_off')"
                                    class="flex items-center p-3 rounded-lg border-2 transition-all {{ $dispositionDecision === 'write_off' ? 'border-gray-500 bg-gray-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <svg class="w-5 h-5 mr-2 {{ $dispositionDecision === 'write_off' ? 'text-gray-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span class="text-sm font-medium {{ $dispositionDecision === 'write_off' ? 'text-gray-700' : 'text-gray-700' }}">Write Off</span>
                                </button>

                                <button
                                    wire:click="$set('dispositionDecision', 'repair')"
                                    class="flex items-center p-3 rounded-lg border-2 transition-all col-span-2 {{ $dispositionDecision === 'repair' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <svg class="w-5 h-5 mr-2 {{ $dispositionDecision === 'repair' ? 'text-purple-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium {{ $dispositionDecision === 'repair' ? 'text-purple-700' : 'text-gray-700' }}">Repair</span>
                                </button>
                            </div>
                            @error('dispositionDecision')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea
                                wire:model="dispositionNotes"
                                rows="3"
                                placeholder="Add any additional notes about this disposition decision..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm resize-none"
                            ></textarea>
                            @error('dispositionNotes')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end space-x-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                        <button
                            wire:click="closeDispositionModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            wire:click="saveDisposition"
                            wire:loading.attr="disabled"
                            {{ !$dispositionDecision ? 'disabled' : '' }}
                            class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
                        >
                            <span wire:loading.remove wire:target="saveDisposition">Save Decision</span>
                            <span wire:loading wire:target="saveDisposition">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
