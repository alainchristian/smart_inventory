<x-app-layout>
    <div class="bg-white rounded-lg shadow p-6" style="background: var(--surface); border: 1px solid var(--border);">
        <div class="mb-6" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <a href="{{ route('owner.transfers.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold" style="color: var(--accent);">
                ← Back to Transfers
            </a>
            @if (in_array($transfer->status, [\App\Enums\TransferStatus::IN_TRANSIT, \App\Enums\TransferStatus::DELIVERED, \App\Enums\TransferStatus::RECEIVED]))
            <a href="{{ route('owner.transfers.delivery-note', $transfer) }}" target="_blank"
               style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:8px;font-size:16px;font-weight:600;text-decoration:none;background:var(--surface2);color:var(--text);border:1px solid var(--border);">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Delivery Note
            </a>
            @endif
        </div>

        <h1 class="text-2xl font-bold mb-4" style="color: var(--text);">Transfer Details: {{ $transfer->transfer_number }}</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-2xl" style="color: var(--text-sub);">Status</p>
                <p class="font-semibold text-2xl" style="color: var(--text);">{{ $transfer->status->label() }}</p>
            </div>
            <div>
                <p class="text-2xl" style="color: var(--text-sub);">From Warehouse</p>
                <p class="font-semibold" style="color: var(--text);">{{ $transfer->fromWarehouse->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-2xl" style="color: var(--text-sub);">To Shop</p>
                <p class="font-semibold" style="color: var(--text);">{{ $transfer->toShop->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-2xl" style="color: var(--text-sub);">Requested By</p>
                <p class="font-semibold" style="color: var(--text);">{{ $transfer->requestedBy->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-2xl" style="color: var(--text-sub);">Requested At</p>
                <p class="font-semibold" style="color: var(--text);">{{ $transfer->requested_at?->format('d M Y, H:i') ?? '—' }}</p>
            </div>
            @if($transfer->delivered_at)
            <div>
                <p class="text-2xl" style="color: var(--text-sub);">Delivered At</p>
                <p class="font-semibold" style="color: var(--text);">{{ $transfer->delivered_at->format('d M Y, H:i') }}</p>
            </div>
            @endif
        </div>

        <h2 class="text-2xl font-bold mb-3" style="color: var(--text);">Items</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: var(--border);">
                <thead style="background: var(--surface2);">
                    <tr>
                        <th class="px-6 py-3 text-left text-2xl font-medium uppercase" style="color: var(--text-sub);">Product</th>
                        <th class="px-6 py-3 text-left text-2xl font-medium uppercase" style="color: var(--text-sub);">Boxes Requested</th>
                        <th class="px-6 py-3 text-left text-2xl font-medium uppercase" style="color: var(--text-sub);">Boxes Shipped</th>
                        <th class="px-6 py-3 text-left text-2xl font-medium uppercase" style="color: var(--text-sub);">Boxes Received</th>
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
