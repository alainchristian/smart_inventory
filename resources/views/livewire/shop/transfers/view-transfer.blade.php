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
                        <span class="font-medium text-gray-500">Requested Date</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->requested_at?->format('M d, Y') }}</p>
                    </div>
                    @if($transfer->reviewed_at)
                        <div>
                            <span class="font-medium text-gray-500">{{ $transfer->status === TransferStatus::APPROVED ? 'Approved' : 'Reviewed' }} Date</span>
                            <p class="text-gray-900 mt-1">{{ $transfer->reviewed_at?->format('M d, Y') }}</p>
                        </div>
                    @endif
                    @if($transfer->shipped_at)
                        <div>
                            <span class="font-medium text-gray-500">Shipped Date</span>
                            <p class="text-gray-900 mt-1">{{ $transfer->shipped_at?->format('M d, Y') }}</p>
                        </div>
                    @endif
                    @if($transfer->delivered_at)
                        <div>
                            <span class="font-medium text-gray-500">Delivered Date</span>
                            <p class="text-gray-900 mt-1">{{ $transfer->delivered_at?->format('M d, Y') }}</p>
                        </div>
                    @endif
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
                            <div class="text-xs text-gray-600 mt-1">Boxes Shipped</div>
                        </div>
                        @if($transfer->status === TransferStatus::RECEIVED)
                            @php
                                $receivedCount = $transfer->boxes()->whereNotNull('scanned_in_at')->count();
                                $damagedCount = $transfer->boxes()->where('is_damaged', true)->count();
                            @endphp
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ $receivedCount }}</div>
                                <div class="text-xs text-gray-600 mt-1">Boxes Received</div>
                            </div>
                            @if($damagedCount > 0)
                                <div class="text-center p-3 bg-red-50 rounded-lg">
                                    <div class="text-2xl font-bold text-red-600">{{ $damagedCount }}</div>
                                    <div class="text-xs text-gray-600 mt-1">Damaged</div>
                                </div>
                            @endif
                        @endif
                    </div>
                @endif

                @if($transfer->notes)
                    <div class="mt-4 p-3 md:p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 mb-1">Notes:</p>
                        <p class="text-sm text-gray-600">{{ $transfer->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Info -->
    @if($transfer->status === TransferStatus::PENDING)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-4 w-4 md:h-5 md:w-5 text-yellow-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="text-xs md:text-sm text-yellow-800">
                        <strong>Pending Review:</strong> Your transfer request is awaiting warehouse approval.
                    </p>
                </div>
            </div>
        </div>
    @elseif($transfer->status === TransferStatus::APPROVED)
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-4 w-4 md:h-5 md:w-5 text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="text-xs md:text-sm text-green-800">
                        <strong>Approved:</strong> The warehouse has approved your request and is preparing your order.
                    </p>
                </div>
            </div>
        </div>
    @elseif($transfer->status === TransferStatus::IN_TRANSIT)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-4 w-4 md:h-5 md:w-5 text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-xs md:text-sm text-blue-800">
                        <strong>In Transit:</strong> Your order has been shipped and is on the way to your shop.
                    </p>
                </div>
            </div>
        </div>
    @elseif($transfer->status === TransferStatus::DELIVERED)
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-4 w-4 md:h-5 md:w-5 text-purple-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-xs md:text-sm text-purple-800">
                        <strong>Delivered:</strong> Your order has been delivered. Please scan the boxes to receive them into your inventory.
                    </p>
                    <a href="{{ route('shop.transfers.receive', $transfer) }}"
                       class="inline-flex items-center mt-3 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Receive Transfer
                    </a>
                </div>
            </div>
        </div>
    @elseif($transfer->status === TransferStatus::RECEIVED)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-4 w-4 md:h-5 md:w-5 text-gray-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="text-xs md:text-sm text-gray-800">
                        <strong>Received:</strong> Transfer completed on {{ $transfer->received_at?->format('M d, Y') }}.
                    </p>
                </div>
            </div>
        </div>
    @elseif($transfer->status === TransferStatus::REJECTED)
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-4 w-4 md:h-5 md:w-5 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="text-xs md:text-sm text-red-800">
                        <strong>Rejected:</strong> The warehouse was unable to fulfill this request.
                        @if($transfer->notes)
                            <br><span class="font-medium">Reason:</span> {{ $transfer->notes }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Requested Items -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-4 md:mb-6">Requested Products</h3>

        <div class="space-y-3 md:space-y-4">
            @foreach($items as $item)
                @php
                    $requestedBoxes = $item['boxes_requested'];
                    $totalItems = $requestedBoxes * $item['items_per_box'];
                @endphp

                <div class="p-3 md:p-5 border-2 border-gray-200 rounded-lg bg-white">
                    <h4 class="font-semibold text-gray-900 text-base md:text-lg mb-3">{{ $item['product_name'] }}</h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                        <!-- Boxes Requested -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">
                                Boxes Requested
                            </label>
                            <div class="flex items-baseline gap-2">
                                <span class="text-xl md:text-2xl font-bold text-gray-900">{{ number_format($requestedBoxes) }}</span>
                                <span class="text-xs md:text-sm text-gray-600">boxes</span>
                            </div>
                        </div>

                        <!-- Items per Box -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">
                                Items per Box
                            </label>
                            <div class="flex items-baseline gap-2">
                                <span class="text-xl md:text-2xl font-bold text-gray-900">{{ number_format($item['items_per_box']) }}</span>
                                <span class="text-xs md:text-sm text-gray-600">items</span>
                            </div>
                        </div>

                        <!-- Total Items -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2">
                                Total Items
                            </label>
                            <div class="flex items-baseline gap-2">
                                <span class="text-xl md:text-2xl font-bold text-indigo-600">{{ number_format($totalItems) }}</span>
                                <span class="text-xs md:text-sm text-gray-600">items</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
