@php
use App\Enums\TransferStatus;
@endphp

<div class="space-y-4 md:space-y-6">
    <!-- Transfer Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 md:gap-3 mb-3 md:mb-4">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900">{{ $transfer->transfer_number }}</h2>
                    <span class="px-3 py-1 rounded-full text-xs md:text-sm font-medium {{ $transfer->status->color() }}">
                        {{ $transfer->status->label() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 md:gap-6 text-sm">
                    <div>
                        <span class="font-medium text-gray-500">From Warehouse</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->fromWarehouse->name }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">To Shop</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->toShop->name }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Requested By</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->requestedBy->name }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Requested Date</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->requested_at?->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Total Products</span>
                        <p class="text-gray-900 mt-1">{{ count($items) }} {{ count($items) === 1 ? 'product' : 'products' }}</p>
                    </div>
                    @if($transfer->transporter)
                        <div>
                            <span class="font-medium text-gray-500">Transporter</span>
                            <p class="text-gray-900 mt-1">{{ $transfer->transporter->name }}</p>
                            @if($transfer->transporter->vehicle_number)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $transfer->transporter->vehicle_number }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                @if($transfer->boxes()->count() > 0)
                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $transfer->boxes()->count() }}</div>
                            <div class="text-xs text-gray-600 mt-1">Boxes Assigned</div>
                        </div>
                    </div>
                @endif

                @if($transfer->notes)
                    <div class="mt-4 p-3 md:p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 mb-1">Shop Notes:</p>
                        <p class="text-sm text-gray-600">{{ $transfer->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Requested Items -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 md:mb-6 gap-2">
            <h3 class="text-base md:text-lg font-semibold text-gray-900">Requested Products</h3>
            @if($transfer->status === TransferStatus::PENDING)
                <span class="text-xs md:text-sm text-gray-600">You can modify quantities before approving</span>
            @endif
        </div>

        <div class="space-y-3 md:space-y-4">
            @foreach($items as $index => $item)
                @php
                    $stock = $stockLevels[$item['product_id']] ?? null;
                    $availableBoxes = $stock ? $stock['total_boxes'] : 0;
                    $requestedBoxes = $item['boxes_requested'];
                    $exceedsStock = $requestedBoxes > $availableBoxes;
                    $totalItems = $requestedBoxes * $item['items_per_box'];
                @endphp

                <div class="p-3 md:p-5 border-2 rounded-lg {{ $exceedsStock ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 text-base md:text-lg mb-3">{{ $item['product_name'] }}</h4>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                                <!-- Boxes Input -->
                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">
                                        Boxes Requested
                                    </label>
                                    @if($transfer->status === TransferStatus::PENDING)
                                        <input type="number"
                                               wire:model.live="items.{{ $index }}.boxes_requested"
                                               min="0"
                                               class="block w-full px-3 md:px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 {{ $exceedsStock ? 'border-red-300 bg-red-50' : 'border-gray-300' }}"
                                               placeholder="0">
                                        @error("items.{$index}.boxes_requested")
                                            <p class="mt-1 text-xs md:text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    @else
                                        <div class="text-xl md:text-2xl font-bold text-gray-900">{{ number_format($requestedBoxes) }}</div>
                                    @endif
                                </div>

                                <!-- Available Stock -->
                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">
                                        Available in Warehouse
                                    </label>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xl md:text-2xl font-bold {{ $exceedsStock ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format($availableBoxes) }}
                                        </span>
                                        <span class="text-xs md:text-sm text-gray-600">boxes</span>
                                    </div>
                                    @if($stock)
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $stock['full_boxes'] }} full + {{ $stock['partial_boxes'] }} partial
                                        </div>
                                    @endif
                                </div>

                                <!-- Total Items -->
                                <div>
                                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">
                                        Total Items
                                    </label>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xl md:text-2xl font-bold text-gray-900">
                                            {{ number_format($totalItems) }}
                                        </span>
                                        <span class="text-xs md:text-sm text-gray-600">items</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $item['items_per_box'] }} items per box
                                    </div>
                                </div>
                            </div>

                            <!-- Warning if exceeds stock -->
                            @if($exceedsStock && $requestedBoxes > 0)
                                <div class="mt-3 md:mt-4 flex items-center gap-2 text-red-600 animate-pulse">
                                    <svg class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs md:text-sm font-medium">
                                        Exceeds available stock by {{ number_format($requestedBoxes - $availableBoxes) }} boxes
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Action Buttons -->
    @if($transfer->status === TransferStatus::PENDING)
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <button type="button"
                    wire:click="openRejectModal"
                    class="inline-flex items-center justify-center px-4 md:px-6 py-2 md:py-3 border-2 border-red-600 text-red-600 rounded-lg hover:bg-red-50 font-medium transition-colors text-sm md:text-base">
                <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reject Transfer
            </button>

            <button type="button"
                    wire:click="approve"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center px-6 md:px-8 py-2 md:py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-colors disabled:opacity-50 text-sm md:text-base">
                <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span wire:loading.remove>Approve Transfer</span>
                <span wire:loading>Processing...</span>
            </button>
        </div>
    @elseif($transfer->status === TransferStatus::APPROVED)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 md:p-6 text-center">
            <svg class="mx-auto h-10 w-10 md:h-12 md:w-12 text-green-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-base md:text-lg font-medium text-green-900 mb-2">Transfer Approved</p>
            <p class="text-sm text-green-700 mb-4">This transfer has been approved and is ready for packing.</p>
            <a href="{{ route('warehouse.transfers.pack', $transfer) }}"
               class="inline-flex items-center px-4 md:px-6 py-2 md:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors text-sm md:text-base">
                <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Pack Transfer
            </a>
        </div>
    @elseif($transfer->status === TransferStatus::REJECTED)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 md:p-6">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 md:h-6 md:w-6 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="font-medium text-red-900 text-sm md:text-base">Transfer Rejected</p>
                    @if($transfer->notes)
                        <p class="mt-2 text-xs md:text-sm text-red-700">Reason: {{ $transfer->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Reject Modal -->
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showRejectModal') }">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                     @click="$wire.closeRejectModal()"></div>

                <!-- Modal -->
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Reject Transfer Request</h3>
                        <button type="button"
                                @click="$wire.closeRejectModal()"
                                class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for Rejection <span class="text-red-600">*</span>
                        </label>
                        <textarea wire:model="rejectReason"
                                  rows="4"
                                  class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Explain why this transfer cannot be fulfilled..."></textarea>
                        @error('rejectReason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button"
                                @click="$wire.closeRejectModal()"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="button"
                                wire:click="reject"
                                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                            Reject Transfer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
