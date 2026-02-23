<div class="bg-white rounded-lg border border-gray-200 p-5">
    <!-- Widget Header -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-gray-700 uppercase flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            Returns Analytics
        </h3>
        <select wire:model.live="period" class="text-xs border-gray-300 rounded px-2 py-1">
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
        </select>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 gap-3 mb-4">
        <!-- Total Returns -->
        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-3 border border-indigo-200">
            <p class="text-xs text-indigo-600 font-bold uppercase mb-1">Total Returns</p>
            <p class="text-2xl font-bold text-indigo-900">{{ $analytics['total_returns'] }}</p>
            <p class="text-xs text-indigo-600 mt-1">
                <span class="font-bold">{{ $analytics['refund_count'] }}</span> refunds,
                <span class="font-bold">{{ $analytics['exchange_count'] }}</span> exchanges
            </p>
        </div>

        <!-- Return Rate -->
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-3 border border-amber-200">
            <p class="text-xs text-amber-600 font-bold uppercase mb-1">Return Rate</p>
            <p class="text-2xl font-bold text-amber-900">{{ $analytics['return_rate'] }}%</p>
            <p class="text-xs text-amber-600 mt-1">of {{ $analytics['total_sales'] }} sales</p>
        </div>

        <!-- Total Refunds -->
        <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-lg p-3 border border-pink-200">
            <p class="text-xs text-pink-600 font-bold uppercase mb-1">Total Refunds</p>
            <p class="text-lg font-bold text-pink-900">RWF {{ number_format($analytics['total_refunds']) }}</p>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-3 border border-red-200">
            <p class="text-xs text-red-600 font-bold uppercase mb-1">Pending</p>
            <p class="text-2xl font-bold text-red-900">{{ $analytics['pending_approvals'] }}</p>
            @if($analytics['pending_approvals'] > 0)
                <a href="{{ route('shop.returns.index') }}" class="text-xs text-red-700 hover:text-red-900 font-bold mt-1 inline-block">
                    Review now →
                </a>
            @endif
        </div>
    </div>

    <!-- Return Reasons -->
    @if($analytics['returns_by_reason']->count() > 0)
        <div class="mb-4 pb-4 border-b border-gray-200">
            <h4 class="text-xs font-bold text-gray-600 uppercase mb-2">Top Return Reasons</h4>
            <div class="space-y-2">
                @foreach($analytics['returns_by_reason'] as $item)
                    @php
                        $percentage = $analytics['total_returns'] > 0 ? round(($item['count'] / $analytics['total_returns']) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-700 font-medium">{{ $item['reason'] }}</span>
                            <span class="text-gray-500 font-bold">{{ $item['count'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Most Returned Products -->
    @if($analytics['most_returned_products']->count() > 0)
        <div>
            <h4 class="text-xs font-bold text-gray-600 uppercase mb-2">Most Returned Products</h4>
            <div class="space-y-1.5">
                @foreach($analytics['most_returned_products'] as $product)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-700 truncate mr-2">{{ $product->name }}</span>
                        <span class="text-red-600 font-bold shrink-0">{{ $product->total_returned }} units</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Empty State -->
    @if($analytics['total_returns'] === 0)
        <div class="text-center py-6">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">No returns in this period</p>
            <p class="text-xs text-gray-500 mt-1">Great job maintaining quality!</p>
        </div>
    @endif
</div>
