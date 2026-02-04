@php
use App\Enums\TransferStatus;
@endphp

<div>
    <!-- Header with Status Filters -->
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
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
                                    <span class="font-medium">Destination:</span>
                                    <span>{{ $transfer->toShop->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Requested by:</span>
                                    <span>{{ $transfer->requestedBy->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Date:</span>
                                    <span>{{ $transfer->requested_at?->format('M d, Y g:i A') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Items:</span>
                                    <span>{{ $transfer->items->count() }} products</span>
                                </div>
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
                        </div>

                        <!-- Actions -->
                        <div class="ml-6 flex flex-col gap-2">
                            @if($transfer->status === TransferStatus::PENDING)
                                <a href="{{ route('warehouse.transfers.show', $transfer) }}"
                                   class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition-colors">
                                    Review
                                </a>
                            @elseif($transfer->status === TransferStatus::APPROVED)
                                <a href="{{ route('warehouse.transfers.pack', $transfer) }}"
                                   class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                                    Pack
                                </a>
                            @else
                                <a href="{{ route('warehouse.transfers.show', $transfer) }}"
                                   class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors">
                                    View
                                </a>
                            @endif
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
                        There are no transfer requests yet.
                    @else
                        There are no transfers with status "{{ ucfirst($statusFilter) }}".
                    @endif
                </p>
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
