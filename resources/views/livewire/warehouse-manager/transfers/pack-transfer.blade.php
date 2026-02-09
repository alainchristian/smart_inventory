@php
use App\Enums\TransferStatus;
@endphp

<div>
    <!-- Transfer Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $transfer->transfer_number }}</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $transfer->status->color() }}">
                        {{ $transfer->status->label() }}
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-6 text-sm">
                    <div>
                        <span class="font-medium text-gray-500">Destination</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->toShop->name }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Requested By</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->requestedBy->name }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-500">Approved Date</span>
                        <p class="text-gray-900 mt-1">{{ $transfer->reviewed_at?->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Phone Scanner Mode Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">

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
    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-lg shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center gap-4 mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-lg font-semibold">Scan Box Barcode</h3>
                <p class="text-indigo-100 text-sm">Scan or enter box code to add to transfer</p>
            </div>
        </div>

        <div class="flex gap-3">
            <div class="flex-1">
                <input type="text"
                       wire:model="scanInput"
                       wire:keydown.enter="scanBox"
                       placeholder="Enter box code (e.g., BOX-001)"
                       class="block w-full px-4 py-3 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-white border-0 text-lg font-mono"
                       autofocus>
                @error('scanInput')
                    <p class="mt-2 text-sm text-red-200">{{ $message }}</p>
                @enderror
            </div>
            <button type="button"
                    wire:click="scanBox"
                    class="px-8 py-3 bg-white text-indigo-600 rounded-lg hover:bg-indigo-50 font-semibold transition-colors">
                Scan
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

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

    <!-- Products to Pack -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Products to Pack</h3>

        <div class="space-y-6">
            @foreach($items as $item)
                @php
                    $boxesRequested = $item['boxes_requested'];
                    $boxesAssigned = $item['boxes_assigned'];
                    $progress = $boxesRequested > 0 ? ($boxesAssigned / $boxesRequested) * 100 : 0;
                    $isComplete = $boxesAssigned >= $boxesRequested;
                    $availableBoxesForProduct = $availableBoxes[$item['product_id']] ?? collect();
                @endphp

                <div class="border-2 rounded-lg p-5 {{ $isComplete ? 'border-green-300 bg-green-50' : 'border-gray-200' }}">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h4 class="font-semibold text-gray-900 text-lg">{{ $item['product_name'] }}</h4>
                                @if($isComplete)
                                    <span class="px-2 py-1 bg-green-600 text-white text-xs font-medium rounded-full">Complete</span>
                                @endif
                            </div>

                            <div class="text-sm text-gray-600 mb-3">
                                <span class="font-medium">Progress:</span>
                                <span class="{{ $isComplete ? 'text-green-600 font-semibold' : 'text-gray-900' }}">
                                    {{ number_format($boxesAssigned) }} / {{ number_format($boxesRequested) }} boxes assigned
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                                <div class="h-3 rounded-full transition-all {{ $isComplete ? 'bg-green-600' : 'bg-indigo-600' }}"
                                     style="width: {{ min($progress, 100) }}%"></div>
                            </div>
                        </div>

                        <!-- Quick Add Button -->
                        @if(!$isComplete && $availableBoxesForProduct->isNotEmpty())
                            <button type="button"
                                    wire:click="addBoxToProduct({{ $item['product_id'] }})"
                                    class="ml-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Quick Add Box
                            </button>
                        @endif
                    </div>

                    <!-- Available Boxes -->
                    @if($availableBoxesForProduct->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm font-medium text-gray-700 mb-3">Available Boxes (showing first 5):</p>
                            <div class="grid grid-cols-5 gap-3">
                                @foreach($availableBoxesForProduct as $box)
                                    <button type="button"
                                            wire:click="scanInput = '{{ $box->box_code }}'; scanBox();"
                                            class="p-3 border-2 border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors text-left">
                                        <div class="font-mono text-xs font-semibold text-gray-900">{{ $box->box_code }}</div>
                                        <div class="text-xs text-gray-600 mt-1">
                                            {{ $box->status->label() }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $box->items_remaining }} items
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="mt-4 pt-4 border-t border-gray-200 text-center py-4">
                            <p class="text-sm text-gray-500">No more available boxes for this product</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Assigned Boxes -->
    @if(!empty($assignedBoxes))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Assigned Boxes ({{ count($assignedBoxes) }})</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Box Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($assignedBoxes as $box)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-medium text-gray-900">{{ $box['box_code'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $box['product_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        {{ $box['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($box['items_remaining']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button type="button"
                                            wire:click="removeBox({{ $box['id'] }})"
                                            class="text-red-600 hover:text-red-900">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Summary and Ship Button -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-3 gap-6 mb-6">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-3xl font-bold text-gray-900">{{ count($assignedBoxes) }}</div>
                <div class="text-sm text-gray-600 mt-1">Boxes Assigned</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                @php
                    $totalItemsAssigned = array_sum(array_column($items, 'quantity_assigned'));
                @endphp
                <div class="text-3xl font-bold text-gray-900">{{ number_format($totalItemsAssigned) }}</div>
                <div class="text-sm text-gray-600 mt-1">Total Items</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                @php
                    $productsComplete = 0;
                    foreach ($items as $item) {
                        if ($item['boxes_assigned'] >= $item['boxes_requested']) {
                            $productsComplete++;
                        }
                    }
                @endphp
                <div class="text-3xl font-bold text-gray-900">{{ $productsComplete }}/{{ count($items) }}</div>
                <div class="text-sm text-gray-600 mt-1">Products Complete</div>
            </div>
        </div>

        <button type="button"
                wire:click="openShipModal"
                @if(empty($assignedBoxes)) disabled @endif
                class="w-full px-6 py-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold text-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-6 h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            Ship Transfer
        </button>
    </div>

    <!-- Ship Modal -->
    @if($showShipModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                     wire:click="closeShipModal"></div>

                <!-- Modal -->
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Ship Transfer</h3>
                        <button type="button"
                                wire:click="closeShipModal"
                                class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-6">
                        <p class="text-sm text-gray-600 mb-4">
                            You are about to ship <strong>{{ count($assignedBoxes) }} boxes</strong> to <strong>{{ $transfer->toShop->name }}</strong>.
                        </p>

                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Transporter (Optional)
                        </label>
                        <select wire:model="transporterId"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">No Transporter</option>
                            @foreach($transporters as $transporter)
                                <option value="{{ $transporter->id }}">
                                    {{ $transporter->name }}
                                    @if($transporter->vehicle_number)
                                        - {{ $transporter->vehicle_number }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button"
                                wire:click="closeShipModal"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="button"
                                wire:click="ship"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors">
                            Confirm Ship
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
