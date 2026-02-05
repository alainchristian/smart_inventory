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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-6 text-sm">
                    <div>
                        <span class="font-medium text-gray-500">From Warehouse</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->fromWarehouse->name }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Shipped Date</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->shipped_at?->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Transporter</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->transporter?->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Phone Scanner Mode Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-base md:text-lg font-semibold text-gray-900">📱 Phone Scanner Mode</h3>
                <p class="text-xs md:text-sm text-gray-600">Use your phone as a dedicated scanner while working on desktop</p>
            </div>

            @if(!$showScannerQR)
                <button type="button"
                        wire:click="generateScannerSession"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold text-sm md:text-base transition-colors">
                    Enable Phone Scanner
                </button>
            @else
                <button type="button"
                        wire:click="closeScannerSession"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold text-sm md:text-base transition-colors">
                    Disable Scanner
                </button>
            @endif
        </div>

        @if($showScannerQR && $scannerSession)
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 md:p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Manual Code Section -->
                    <div class="flex flex-col justify-center">
                        <div class="text-center mb-4">
                            <p class="text-sm text-gray-600 mb-2">Enter this code on your phone:</p>
                            <div class="bg-white px-6 py-4 rounded-lg shadow-sm">
                                <p class="text-3xl md:text-4xl font-bold text-purple-600 tracking-widest">
                                    {{ $scannerSession->session_code }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 md:p-4">
                            <p class="text-sm text-purple-800 font-semibold mb-2">📱 Steps:</p>
                            <ol class="text-xs md:text-sm text-purple-700 space-y-1">
                                <li>1. On your phone, go to: <strong class="text-purple-900">{{ url('/scanner') }}</strong></li>
                                <li>2. Enter the code above</li>
                                <li>3. Start scanning barcodes</li>
                                <li>4. Scans appear here automatically</li>
                            </ol>
                        </div>

                        <div class="mt-3 text-xs text-gray-500 text-center">
                            Session expires: {{ $scannerSession->expires_at->diffForHumans() }}
                        </div>
                    </div>

                    <!-- Status Section -->
                    <div class="flex flex-col justify-center">
                        <div class="bg-white rounded-lg shadow-sm border-2 border-dashed border-gray-300 p-6 text-center">
                            <div class="text-purple-600 mb-3">
                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Waiting for Phone</h4>
                            <p class="text-sm text-gray-600">
                                Open <strong>{{ url('/scanner') }}</strong> on your phone and enter the code above
                            </p>
                            <div class="mt-4 text-xs text-gray-500">
                                Scans will appear on this page automatically
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Polling for scans -->
            <div wire:poll.2s="checkForScans"></div>
        @endif
    </div>

    <!-- Scanner Section -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg shadow-lg p-4 md:p-6 text-white">
        <div class="flex items-center gap-3 md:gap-4 mb-3 md:mb-4">
            <svg class="w-6 h-6 md:w-8 md:h-8 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-base md:text-lg font-semibold">Scan Product Barcode</h3>
                <p class="text-purple-100 text-xs md:text-sm">Scan in any order - mix products freely</p>
            </div>
        </div>

        @if(!$pendingBarcode)
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text"
                           wire:model="scanInput"
                           wire:keydown.enter="scanBox"
                           placeholder="Scan product barcode..."
                           class="block w-full px-3 md:px-4 py-2 md:py-3 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-white border-0 text-base md:text-lg font-mono"
                           autofocus>
                </div>
                <button type="button"
                        wire:click="scanBox"
                        class="px-6 md:px-8 py-2 md:py-3 bg-white text-purple-600 rounded-lg hover:bg-purple-50 font-semibold transition-colors">
                    Scan
                </button>
            </div>
        @else
            <!-- Quantity Confirmation -->
            <div class="bg-white text-gray-900 rounded-lg p-3 md:p-4">
                <h4 class="font-semibold text-sm md:text-base mb-2">{{ $pendingProductName }}</h4>
                <p class="text-xs md:text-sm text-gray-600 mb-3 md:mb-4">Available: {{ $pendingAvailableCount }} box(es)</p>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 md:gap-3">
                    <label class="text-xs md:text-sm font-medium">Boxes:</label>
                    <input type="number"
                           wire:model="scanQuantity"
                           min="1"
                           max="{{ $pendingAvailableCount }}"
                           class="px-3 py-2 border border-gray-300 rounded-lg w-full sm:w-24 text-center">
                    <button type="button"
                            wire:click="confirmQuantity"
                            class="px-4 md:px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors text-sm md:text-base">
                        Confirm
                    </button>
                    <button type="button"
                            wire:click="cancelPending"
                            class="px-4 md:px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition-colors text-sm md:text-base">
                        Cancel
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-5 w-5 text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs md:text-sm text-green-800 flex-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('scan_success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-5 w-5 text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs md:text-sm text-green-800 flex-1">{{ session('scan_success') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-5 w-5 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs md:text-sm text-red-800 flex-1">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('scan_error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-5 w-5 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs md:text-sm text-red-800 flex-1">{{ session('scan_error') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 md:p-4">
            <div class="flex items-start gap-2 md:gap-3">
                <svg class="h-5 w-5 text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs md:text-sm text-blue-800 flex-1">{{ session('info') }}</p>
            </div>
        </div>
    @endif

    <!-- Progress Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4">Receiving Progress</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-4">
            <div class="text-center p-3 md:p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl md:text-3xl font-bold text-blue-600">{{ $expectedBoxes }}</div>
                <div class="text-xs md:text-sm text-gray-600 mt-1">Expected</div>
            </div>
            <div class="text-center p-3 md:p-4 bg-green-50 rounded-lg">
                <div class="text-2xl md:text-3xl font-bold text-green-600">{{ $scannedCount }}</div>
                <div class="text-xs md:text-sm text-gray-600 mt-1">Scanned</div>
            </div>
            <div class="text-center p-3 md:p-4 bg-yellow-50 rounded-lg">
                <div class="text-2xl md:text-3xl font-bold text-yellow-600">{{ $remainingCount }}</div>
                <div class="text-xs md:text-sm text-gray-600 mt-1">Remaining</div>
            </div>
            <div class="text-center p-3 md:p-4 bg-red-50 rounded-lg">
                <div class="text-2xl md:text-3xl font-bold text-red-600">{{ $damagedCount }}</div>
                <div class="text-xs md:text-sm text-gray-600 mt-1">Damaged</div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-3 md:h-4">
            <div class="h-3 md:h-4 rounded-full bg-green-600 transition-all"
                 style="width: {{ $progressPercentage }}%"></div>
        </div>
        <p class="text-xs md:text-sm text-gray-600 text-center mt-2">{{ number_format($progressPercentage, 1) }}% Complete</p>
    </div>

    <!-- Scanned Boxes -->
    @if(!empty($scannedBoxes))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="flex items-center justify-between mb-4 md:mb-6">
                <h3 class="text-base md:text-lg font-semibold text-gray-900">Scanned Boxes ({{ count($scannedBoxes) }})</h3>
            </div>

            <div class="space-y-3 md:space-y-4">
                @foreach($scannedBoxes as $boxId => $box)
                    <div class="border-2 rounded-lg p-3 md:p-4 {{ $box['is_damaged'] ? 'border-red-300 bg-red-50' : 'border-green-300 bg-green-50' }}">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 md:gap-3 mb-2">
                                    <span class="font-mono text-xs md:text-sm font-bold text-gray-900">{{ $box['box_code'] }}</span>
                                    <span class="text-xs md:text-sm text-gray-700">{{ $box['product_name'] }}</span>
                                </div>

                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 md:gap-3">
                                    <button type="button"
                                            wire:click="markAsDamaged({{ $boxId }}, {{ $box['is_damaged'] ? 'false' : 'true' }})"
                                            class="px-3 py-1.5 rounded-lg text-xs md:text-sm font-medium transition-colors {{ $box['is_damaged'] ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                        {{ $box['is_damaged'] ? '✓ Damaged' : 'Mark as Damaged' }}
                                    </button>

                                    @if($box['is_damaged'])
                                        <input type="text"
                                               wire:model.blur="scannedBoxes.{{ $boxId }}.damage_notes"
                                               wire:change="updateDamageNotes({{ $boxId }}, $event.target.value)"
                                               placeholder="Enter damage notes..."
                                               value="{{ $box['damage_notes'] }}"
                                               class="flex-1 px-3 py-1.5 text-xs md:text-sm border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500">
                                    @endif
                                </div>
                            </div>

                            <button type="button"
                                    wire:click="removeScannedBox({{ $boxId }})"
                                    class="text-red-600 hover:text-red-900 text-xs md:text-sm font-medium self-start md:ml-4">
                                Remove
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Complete Receipt Button -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
        <button type="button"
                wire:click="completeReceipt"
                @if(empty($scannedBoxes)) disabled @endif
                class="w-full px-4 md:px-6 py-3 md:py-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold text-sm md:text-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-5 h-5 md:w-6 md:h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Complete Receiving
        </button>
        <p class="text-xs text-gray-500 text-center mt-2">Partial deliveries allowed. Missing boxes will be reported.</p>
    </div>
</div>
