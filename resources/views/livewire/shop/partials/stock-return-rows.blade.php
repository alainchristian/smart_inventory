{{-- Rows of shop stock with a "boxes to send" input. Expects: $rows (objects
     with product_id, name, sku, category_name, boxes, sealed, items). --}}
<table class="sr-table">
    <thead>
        <tr><th>Product</th><th class="num sr-hide-mob">Boxes</th><th class="num sr-hide-mob">Items</th><th class="num">Send back</th></tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr wire:key="sr-row-{{ $row->product_id }}">
                <td>
                    <div class="sr-name">{{ $row->name }}</div>
                    <div class="sr-meta">{{ $row->category_name ?? 'Uncategorised' }} · <span style="font-family:var(--mono)">{{ $row->sku }}</span></div>
                </td>
                <td class="num sr-hide-mob">
                    <span class="sr-mono">{{ $row->boxes }}</span>
                    @if($row->boxes - $row->sealed > 0)<div class="sr-meta">{{ $row->sealed }} sealed · {{ $row->boxes - $row->sealed }} opened</div>@endif
                </td>
                <td class="num sr-hide-mob sr-mono">{{ number_format($row->items) }}</td>
                <td class="num">
                    <span class="sr-qty">
                        <input type="number" min="0" max="{{ $row->boxes }}" class="sr-num" wire:model.live.debounce.300ms="send.{{ $row->product_id }}"
                               placeholder="0" aria-label="Boxes of {{ $row->name }} to send back">
                        <button type="button" class="sr-link" wire:click="sendAll({{ $row->product_id }})">All {{ $row->boxes }}</button>
                    </span>
                    @error('send.' . $row->product_id) <div class="sr-error">{{ $message }}</div> @enderror
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
