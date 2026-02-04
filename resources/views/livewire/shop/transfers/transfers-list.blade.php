@php
use App\Enums\TransferStatus;
@endphp

<div>
    <!-- Header with Status Filters and New Request Button -->
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex flex-wrap items-center gap-4">
                <button wire:click="$set('statusFilter', 'all')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All Transfers
                </button>
                <button wire:click="$set('statusFilter', '{{ TransferStatus::PENDING->value }}')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === TransferStatus::PENDING->value ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Pending
                    @if($pendingCount > 0)
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-yellow-500 text-white">{{ $pendingCount }}</span>
                    @endif
                </button>
                <button wire:click="$set('statusFilter', '{{ TransferStatus::APPROVED->value }}')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === TransferStatus::APPROVED->value ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Approved
                    @if($approvedCount > 0)
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-green-500 text-white">{{ $approvedCount }}</span>
                    @endif
                </button>
                <button wire:click="$set('statusFilter', '{{ TransferStatus::IN_TRANSIT->value }}')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === TransferStatus::IN_TRANSIT->value ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    In Transit
                    @if($inTransitCount > 0)
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-blue-500 text-white">{{ $inTransitCount }}</span>
                    @endif
                </button>
                <button wire:click="$set('statusFilter', '{{ TransferStatus::DELIVERED->value }}')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === TransferStatus::DELIVERED->value ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Delivered
                    @if($deliveredCount > 0)
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-purple-500 text-white">{{ $deliveredCount }}</span>
                    @endif
                </button>
            </div>
            <a href="{{ route('shop.transfers.request') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Request
            </a>
        </div>
    </div>

    <!-- Transfers List -->
    <div class="space-y-4">
        @forelse($transfers as $transfer)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <!-- Transfer Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $transfer->transfer_number }}</h3>
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $transfer->status->color() }}">
                                    {{ $transfer->status->label() }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                <div>
                                    <span class="font-medium">From Warehouse:</span>
                                    <span>{{ $transfer->fromWarehouse->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Requested:</span>
                                    <span>{{ $transfer->requested_at?->format('M d, Y g:i A') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Items:</span>
                                    <span>{{ $transfer->items->count() }} products</span>
                                </div>
                                @if($transfer->status === TransferStatus::DELIVERED || $transfer->status === TransferStatus::RECEIVED)
                                    <div>
                                        <span class="font-medium">Delivered:</span>
                                        <span>{{ $transfer->delivered_at?->format('M d, Y g:i A') }}</span>
                                    </div>
                                @endif
                            </div>

                            @if($transfer->notes)
                                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                    <p class="text-sm text-gray-700">
                                        <span class="font-medium">Notes:</span> {{ $transfer->notes }}
                                    </p>
                                </div>
                            @endif

                            <!-- Products Summary -->
                            <div class="mt-4">
                                <div class="text-xs font-medium text-gray-500 mb-2">REQUESTED PRODUCTS</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($transfer->items as $item)
                                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-gray-100 rounded-lg text-sm">
                                            <span class="font-medium text-gray-900">{{ $item->product->name }}</span>
                                            <span class="text-gray-600">×{{ number_format($item->quantity_requested) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Status-specific messages -->
                            @if($transfer->status === TransferStatus::PENDING)
                                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-sm text-yellow-800">
                                        ⏳ Awaiting warehouse approval
                                    </p>
                                </div>
                            @elseif($transfer->status === TransferStatus::APPROVED)
                                <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <p class="text-sm text-green-800">
                                        ✓ Approved - Warehouse is preparing your order
                                    </p>
                                </div>
                            @elseif($transfer->status === TransferStatus::IN_TRANSIT)
                                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-blue-800">
                                            🚚 In transit - Click "Receive Transfer" when boxes arrive
                                        </p>
                                    </div>
                                </div>
                            @elseif($transfer->status === TransferStatus::DELIVERED)
                                <div class="mt-4 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                    <p class="text-sm text-purple-800">
                                        📦 Delivered - Click "Receive Transfer" to scan and receive boxes
                                    </p>
                                </div>
                            @elseif($transfer->status === TransferStatus::RECEIVED)
                                <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                    <p class="text-sm text-gray-800">
                                        ✓ Received on {{ $transfer->received_at?->format('M d, Y g:i A') }}
                                    </p>
                                </div>
                            @elseif($transfer->status === TransferStatus::REJECTED)
                                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-sm text-red-800">
                                        ✗ Rejected by warehouse
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="ml-6 flex flex-col gap-2">
                            @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED]))
                                <a href="{{ route('shop.transfers.receive', $transfer) }}"
                                   class="inline-flex items-center justify-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    Receive Transfer
                                </a>
                            @endif
                            <a href="{{ route('shop.transfers.show', $transfer) }}"
                               class="inline-flex items-center justify-center px-4 py-2 {{ in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED]) ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No transfers found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($statusFilter === 'all')
                        You haven't requested any transfers yet.
                    @else
                        There are no transfers with status "{{ ucfirst($statusFilter) }}".
                    @endif
                </p>
                <div class="mt-6">
                    <a href="{{ route('shop.transfers.request') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Request Transfer
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($transfers->hasPages())
        <div class="mt-6">
            {{ $transfers->links() }}
        </div>
    @endif
</div>
