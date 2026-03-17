<x-app-layout>
    <div class="bg-white rounded-lg shadow p-6" style="background: var(--surface); border: 1px solid var(--border);">
        <div class="mb-6">
            <a href="{{ route('owner.transfers.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold" style="color: var(--accent);">
                ← Back to Transfers
            </a>
        </div>

        <h1 class="text-2xl font-bold mb-4" style="color: var(--text);">Transfer Details: {{ $transfer->transfer_number }}</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-sm" style="color: var(--text-sub);">Status</p>
                <p class="font-semibold text-lg" style="color: var(--text);">{{ $transfer->status->label() }}</p>
            </div>
            <div>
                <p class="text-sm" style="color: var(--text-sub);">From Warehouse</p>
                <p class="font-semibold" style="color: var(--text);">{{ $transfer->fromWarehouse->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm" style="color: var(--text-sub);">To Shop</p>
                <p class="font-semibold" style="color: var(--text);">{{ $transfer->toShop->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm" style="color: var(--text-sub);">Requested By</p>
                <p class="font-semibold" style="color: var(--text);">{{ $transfer->requestedBy->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm" style="color: var(--text-sub);">Requested At</p>
                <p class="font-semibold" style="color: var(--text);">{{ $transfer->requested_at?->format('d M Y, H:i') ?? '—' }}</p>
            </div>
            @if($transfer->delivered_at)
            <div>
                <p class="text-sm" style="color: var(--text-sub);">Delivered At</p>
                <p class="font-semibold" style="color: var(--text);">{{ $transfer->delivered_at->format('d M Y, H:i') }}</p>
            </div>
            @endif
        </div>

        <h2 class="text-xl font-bold mb-3" style="color: var(--text);">Items</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: var(--border);">
                <thead style="background: var(--surface2);">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-sub);">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-sub);">Boxes Requested</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-sub);">Boxes Shipped</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-sub);">Boxes Received</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="background: var(--surface); border-color: var(--border);">
                    @foreach($transfer->items as $item)
                    @php
                        $ipb = max(1, (int) ($item->product->items_per_box ?? 1));
                        $bxReq = (int) round($item->quantity_requested / $ipb);
                        $bxShipped = $item->quantity_shipped ? (int) round($item->quantity_shipped / $ipb) : 0;
                        $bxReceived = $item->quantity_received ? (int) round($item->quantity_received / $ipb) : 0;
                    @endphp
                    <tr>
                        <td class="px-6 py-4" style="color: var(--text);">{{ $item->product->name ?? '—' }}</td>
                        <td class="px-6 py-4" style="color: var(--text);">{{ $bxReq }} box{{ $bxReq === 1 ? '' : 'es' }}</td>
                        <td class="px-6 py-4" style="color: var(--text);">{{ $bxShipped }} box{{ $bxShipped === 1 ? '' : 'es' }}</td>
                        <td class="px-6 py-4" style="color: var(--text);">{{ $bxReceived }} box{{ $bxReceived === 1 ? '' : 'es' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
