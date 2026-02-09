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
                        <span class="font-medium text-gray-500">To Shop</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->toShop->name }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Requested Date</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->requested_at?->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Approved Date</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->reviewed_at?->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Phone Scanner Mode Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">

        @if(!$showScannerQR)
            <!-- Scanner Disabled State -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base md:text-lg font-semibold text-gray-900">📱 Phone Scanner Mode</h3>
                    <p class="text-xs md:text-sm text-gray-600">Use your phone as a dedicated scanner while working on desktop</p>
                </div>
                <button type="button"
                        wire:click="generateScannerSession"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold text-sm md:text-base transition-colors">
                    Enable Phone Scanner
                </button>
            </div>

        @elseif($phoneConnected)
            <!-- Phone Connected - Compact View -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <!-- Animated pulse indicator -->
                    <div class="relative">
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-green-600">📱 Phone Connected</h3>
                        <p class="text-xs md:text-sm text-gray-600">Scanner ready - Point camera at barcodes</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button"
                            wire:click="$toggle('showScannerQR')"
                            class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-xs md:text-sm font-medium transition-colors">
                        Show QR Code
                    </button>
                    <button type="button"
                            wire:click="closeScannerSession"
                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-xs md:text-sm font-medium transition-colors">
                        Disconnect
                    </button>
                </div>
            </div>

            <!-- Session info (collapsible) -->
            <div class="mt-3 pt-3 border-t border-gray-200 text-xs text-gray-500 flex items-center justify-between">
                <span>Session: <code class="bg-gray-100 px-2 py-0.5 rounded font-mono">{{ $scannerSession->session_code }}</code></span>
                <span>Expires: {{ $scannerSession->expires_at->diffForHumans() }}</span>
            </div>

            <!-- Polling for scans -->
            <div wire:poll.2s="checkForScans"></div>

        @elseif($scannerSession && $scannerSession->expires_at->isPast())
            <!-- Session Expired State -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-100">
                        <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-orange-600">⏱️ Session Expired</h3>
                        <p class="text-xs md:text-sm text-gray-600">Your phone scanner session has expired. Reconnect to continue.</p>
                    </div>
                </div>
                <button type="button"
                        wire:click="generateScannerSession"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-semibold text-sm md:text-base transition-colors">
                    Reconnect Phone
                </button>
            </div>

        @elseif($showScannerQR && $scannerSession)
            <!-- Scanner Enabled - Waiting for Phone Connection -->
            @php
                // Generate scanner URL using config APP_URL to ensure it uses PC's IP
                $scannerUrl = config('app.url') . '/scanner?code=' . $scannerSession->session_code;
            @endphp

            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-base md:text-lg font-semibold text-gray-900">📱 Phone Scanner Mode</h3>
                    <p class="text-xs md:text-sm text-gray-600">Scan QR code with your phone to connect</p>
                </div>
                <button type="button"
                        wire:click="closeScannerSession"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold text-sm md:text-base transition-colors">
                    Cancel
                </button>
            </div>

            <!-- QR Code Section (Only show when waiting for connection) -->
            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-4 md:p-6">
                <div class="grid md:grid-cols-2 gap-6">

                    <!-- QR CODE SECTION (PRIMARY - LEFT SIDE) -->
                    <div class="text-center">
                        <div class="bg-white p-6 rounded-lg inline-block shadow-lg border-4 border-indigo-200">
                            {!! QrCode::size(250)->generate($scannerUrl) !!}
                        </div>
                        <div class="mt-4 bg-indigo-100 rounded-lg p-3">
                            <p class="text-sm font-bold text-indigo-900">📱 PRIMARY METHOD</p>
                            <p class="text-xs text-indigo-700 mt-1">Open phone camera and point at QR code above</p>
                        </div>
                    </div>

                    <!-- INSTRUCTIONS & MANUAL CODE (BACKUP - RIGHT SIDE) -->
                    <div class="flex flex-col justify-center">
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg p-4 mb-4">
                            <p class="font-bold mb-3 text-lg">📷 Quick Setup:</p>
                            <ol class="text-sm space-y-2">
                                <li class="flex items-start">
                                    <span class="font-bold mr-2">1.</span>
                                    <span>Open <strong>phone camera app</strong></span>
                                </li>
                                <li class="flex items-start">
                                    <span class="font-bold mr-2">2.</span>
                                    <span>Point at <strong>QR code</strong> (left)</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="font-bold mr-2">3.</span>
                                    <span>Tap notification to open scanner</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="font-bold mr-2">4.</span>
                                    <span><strong>Done!</strong> Start scanning barcodes</span>
                                </li>
                            </ol>
                        </div>

                        <!-- Alternative Manual Code -->
                        <div class="bg-white border-2 border-gray-200 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-2">Alternative (if camera doesn't work):</p>
                            <p class="text-xs text-gray-600 mb-2">Go to: <strong class="text-indigo-600">{{ url('/scanner') }}</strong></p>
                            <p class="text-xs text-gray-600 mb-2">Enter code:</p>
                            <div class="bg-gray-50 px-4 py-2 rounded border border-gray-300">
                                <p class="text-2xl font-bold text-gray-700 tracking-widest text-center">
                                    {{ $scannerSession->session_code }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 text-xs text-gray-500 text-center">
                            ⏱️ Session expires: {{ $scannerSession->expires_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Polling for scans and connection status -->
            <div wire:poll.2s="checkForScans"></div>
        @endif
    </div>

    <!-- Scanner Section -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-lg shadow-lg p-4 md:p-6 text-white">
        <div class="flex items-center gap-3 md:gap-4 mb-3 md:mb-4">
            <svg class="w-6 h-6 md:w-8 md:h-8 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-base md:text-lg font-semibold">Scan Product Barcode</h3>
                <p class="text-indigo-100 text-xs md:text-sm">Scan in any order - mix products freely</p>
            </div>
        </div>

        @if(!$pendingBarcode)
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text"
                           wire:model="scanInput"
                           wire:keydown.enter="scanProduct"
                           placeholder="Scan product barcode..."
                           class="block w-full px-3 md:px-4 py-2 md:py-3 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-white border-0 text-base md:text-lg font-mono"
                           autofocus>
                </div>
                <button type="button"
                        wire:click="scanProduct"
                        class="px-6 md:px-8 py-2 md:py-3 bg-white text-indigo-600 rounded-lg hover:bg-indigo-50 font-semibold transition-colors">
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
                            class="px-4 md:px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors text-sm md:text-base">
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

    <!-- Packing Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4">Packing Summary</h3>

        <div class="overflow-x-auto -mx-4 md:mx-0">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 md:px-6 py-2 md:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-3 md:px-6 py-2 md:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Needed</th>
                            <th class="px-3 md:px-6 py-2 md:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Packed</th>
                            <th class="px-3 md:px-6 py-2 md:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($packingSummary as $summary)
                            <tr class="{{ $summary['complete'] ? 'bg-green-50' : '' }}">
                                <td class="px-3 md:px-6 py-3 md:py-4">
                                    <div class="text-xs md:text-sm font-medium text-gray-900">{{ $summary['product_name'] }}</div>
                                    <div class="text-xs font-mono text-gray-600 mt-1">{{ $summary['barcode'] }}</div>
                                    <div class="text-xs text-gray-500 md:hidden mt-1">
                                        @if($summary['complete'])
                                            ✓ Complete
                                        @else
                                            {{ $summary['boxes_needed'] - $summary['boxes_packed'] }} remaining
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-center">
                                    <span class="text-sm md:text-base font-semibold text-gray-900">{{ $summary['boxes_needed'] }}</span>
                                    <span class="text-xs text-gray-500 block md:hidden">boxes</span>
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-center">
                                    <span class="text-sm md:text-base font-semibold text-blue-600">{{ $summary['boxes_packed'] }}</span>
                                    <span class="text-xs text-gray-500 block md:hidden">boxes</span>
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-center hidden md:table-cell">
                                    @if($summary['complete'])
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            ✓ Complete
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                            {{ $summary['boxes_needed'] - $summary['boxes_packed'] }} remaining
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Packed Boxes List -->
    @if(!empty($packedBoxes))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="flex items-center justify-between mb-4 md:mb-6">
                <h3 class="text-base md:text-lg font-semibold text-gray-900">Packed Boxes ({{ count($packedBoxes) }})</h3>
            </div>

            <div class="space-y-2 md:space-y-0 md:overflow-x-auto -mx-4 md:mx-0">
                <!-- Mobile Card View -->
                <div class="md:hidden space-y-3 px-4">
                    @foreach($packedBoxes as $box)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <div class="font-mono text-sm font-bold text-gray-900">{{ $box['box_code'] }}</div>
                                    <div class="text-sm text-gray-700 mt-1">{{ $box['product_name'] }}</div>
                                </div>
                                @if($box['scanned_out'])
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        ✓ Ready
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Box Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($packedBoxes as $box)
                                <tr class="bg-green-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-medium text-gray-900">
                                        {{ $box['box_code'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $box['product_name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($box['scanned_out'])
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                ✓ Scanned Out
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                                Assigned
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Transporter & Ship Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-4">Select Transporter</h3>

        <div class="mb-4 md:mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Transporter <span class="text-red-500">*</span>
            </label>
            <select wire:model="transporter_id"
                    class="block w-full px-3 md:px-4 py-2 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm md:text-base">
                <option value="">Select a transporter...</option>
                @foreach($transporters as $transporter)
                    <option value="{{ $transporter->id }}">
                        {{ $transporter->name }}
                        @if($transporter->vehicle_number)
                            - {{ $transporter->vehicle_number }}
                        @endif
                    </option>
                @endforeach
            </select>
            @error('transporter_id')
                <p class="mt-1 text-xs md:text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="button"
                wire:click="shipTransfer"
                @if(empty($packedBoxes)) disabled @endif
                class="w-full px-4 md:px-6 py-3 md:py-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold text-sm md:text-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-5 h-5 md:w-6 md:h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Ship Transfer
        </button>
        <p class="text-xs text-gray-500 text-center mt-2">Partial shipments allowed. Pack at least one box to ship.</p>
    </div>
</div>
