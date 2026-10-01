<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note — {{ $transfer->transfer_number }}</title>
    @include('transfers.partials.print-styles')
</head>
<body>
@php
    $transfer->loadMissing([
        'fromWarehouse',
        'toShop',
        'transporter',
        'packedBy',
        'packingDoneBy',
        'shippedBy',
        'reviewedBy',
        'requestedBy',
        'deliveredBy',
        'receivedBy',
        'boxes.box.product',
        'items.product',
    ]);
    $totalBoxes = $transfer->boxes->count();
    // Damaged boxes arrived but aren't delivered stock.
    $totalItems = $transfer->boxes->reject(fn ($tb) => $tb->is_damaged)->sum(fn ($tb) => $tb->box?->items_remaining ?? 0);
    $damagedBoxes = $transfer->boxes->where('is_damaged', true)->count();
    // Shipped per product, counted in boxes from the manifest (quantity_shipped is in items).
    $boxesByProduct = $transfer->boxes->groupBy(fn ($tb) => $tb->box?->product_id);
@endphp

<div class="page">

    {{-- ── Document header ── --}}
    <div class="doc-header">
        <div>
            <div class="brand">{{ config('tenant.name') }}</div>
            <div class="brand-sub">Stock transfer</div>
        </div>
        <div class="doc-type">
            <div class="title">Delivery Note</div>
            <div class="number">{{ $transfer->transfer_number }}</div>
            <span class="status-badge">{{ str_replace('_', ' ', $transfer->status->value) }}</span>
        </div>
    </div>

    {{-- ── The dated trail: every step with who ── --}}
    @php
        $fmt = fn ($at) => $at ? local_time($at)->format('d M Y, H:i') : null;
        $trail = array_filter([
            'Requested'   => $fmt($transfer->requested_at) ? $fmt($transfer->requested_at) . ($transfer->requestedBy ? ' · ' . $transfer->requestedBy->name : '') : null,
            'Approved'    => $fmt($transfer->reviewed_at) ? $fmt($transfer->reviewed_at) . ($transfer->reviewedBy ? ' · ' . $transfer->reviewedBy->name : '') : null,
            'Packed'      => $fmt($transfer->packing_done_at ?? $transfer->packed_at) ? $fmt($transfer->packing_done_at ?? $transfer->packed_at) . (($transfer->packingDoneBy ?? $transfer->packedBy) ? ' · ' . ($transfer->packingDoneBy ?? $transfer->packedBy)->name : '') : null,
            'Dispatched'  => $fmt($transfer->shipped_at) ? $fmt($transfer->shipped_at) . ($transfer->shippedBy ? ' · ' . $transfer->shippedBy->name : '') : null,
            'Expected'    => $fmt($transfer->expected_arrival_at),
            'Arrived'     => $fmt($transfer->delivered_at) ? $fmt($transfer->delivered_at) . ($transfer->deliveredBy ? ' · ' . $transfer->deliveredBy->name : '') : null,
            'Received'    => $fmt($transfer->received_at) ? $fmt($transfer->received_at) . ($transfer->receivedBy ? ' · ' . $transfer->receivedBy->name : '') : null,
        ]);
    @endphp
    <div class="meta-row">
        @foreach ($trail as $label => $value)
            <div class="meta-item">
                <div class="meta-label">{{ $label }}</div>
                <div class="meta-value">{{ $value }}</div>
            </div>
        @endforeach
        <div class="meta-item">
            <div class="meta-label">Boxes · items</div>
            <div class="meta-value">{{ $totalBoxes }} · {{ number_format($totalItems) }}</div>
        </div>
    </div>

    {{-- ── From / To ── --}}
    <div class="info-grid">
        <div class="info-card">
            <div class="card-head">From — Warehouse</div>
            <div class="card-body">
                <div class="location-name">{{ $transfer->fromWarehouse?->name ?? '—' }}</div>
                @if ($transfer->fromWarehouse?->city)
                    <div class="location-detail">{{ $transfer->fromWarehouse->city }}</div>
                @endif
                @if ($transfer->fromWarehouse?->address)
                    <div class="location-detail">{{ $transfer->fromWarehouse->address }}</div>
                @endif
                @if ($transfer->fromWarehouse?->phone)
                    <div class="location-detail">Tel: {{ $transfer->fromWarehouse->phone }}</div>
                @endif
                @if ($transfer->packedBy)
                    <div class="location-detail" style="margin-top:6px;">Packed by: <strong>{{ $transfer->packedBy->name }}</strong></div>
                @endif
            </div>
        </div>
        <div class="info-card">
            <div class="card-head">To — Shop</div>
            <div class="card-body">
                <div class="location-name">{{ $transfer->toShop?->name ?? '—' }}</div>
                @if ($transfer->toShop?->city)
                    <div class="location-detail">{{ $transfer->toShop->city }}</div>
                @endif
                @if ($transfer->toShop?->address)
                    <div class="location-detail">{{ $transfer->toShop->address }}</div>
                @endif
                @if ($transfer->toShop?->manager_name || $transfer->toShop?->phone)
                    <div class="location-detail" style="margin-top:6px;">Receiver: <strong>{{ collect([$transfer->toShop->manager_name, $transfer->toShop->phone ? 'Tel: ' . $transfer->toShop->phone : null])->filter()->implode(' · ') }}</strong></div>
                @endif
                @if ($transfer->needed_by)
                    <div class="location-detail">Needed by: <strong>{{ $transfer->needed_by->format('D d M Y') }}</strong></div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Transporter ── --}}
    @if ($transfer->transporter)
    <div class="transporter-row">
        <div class="info-card">
            <div class="card-head">Transporter</div>
            <div class="card-body">
                <div class="location-name">{{ $transfer->transporter->name }}</div>
                @if ($transfer->transporter->company_name)
                    <div class="location-detail">{{ $transfer->transporter->company_name }}</div>
                @endif
                @if ($transfer->transporter->phone)
                    <div class="location-detail">Tel: {{ $transfer->transporter->phone }}</div>
                @endif
            </div>
        </div>
        <div class="info-card">
            <div class="card-head">Vehicle Details</div>
            <div class="card-body">
                @if ($transfer->transporter->vehicle_number)
                    <div class="location-name" style="font-family:monospace">{{ $transfer->transporter->vehicle_number }}</div>
                    <div class="location-detail">Plate / Vehicle No.</div>
                @endif
                @if ($transfer->transporter->license_number)
                    <div class="location-detail" style="margin-top:6px;">License No.: <strong>{{ $transfer->transporter->license_number }}</strong></div>
                @endif
                @if (! $transfer->transporter->vehicle_number && ! $transfer->transporter->license_number)
                    <div class="location-detail" style="font-style:italic;">No vehicle details recorded</div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if ($transfer->transporter_instructions || $transfer->handed_to_name || $transfer->expected_arrival_at)
    <div class="section-heading">For the transporter</div>
    <div class="notes-box">
        @if ($transfer->transporter_instructions)<div><strong>Instructions:</strong> {{ $transfer->transporter_instructions }}</div>@endif
        @if ($transfer->expected_arrival_at)<div style="margin-top:4px"><strong>Expected at the shop:</strong> {{ local_time($transfer->expected_arrival_at)->format('D d M Y, H:i') }}</div>@endif
        @if ($transfer->handed_to_name)<div style="margin-top:4px"><strong>Handed to:</strong> {{ $transfer->handed_to_name }}@if ($transfer->shipped_at) on {{ local_time($transfer->shipped_at)->format('d M Y, H:i') }}@endif</div>@endif
    </div>
    @endif

    {{-- ── Box list ── --}}
    <div class="section-heading">Box Manifest ({{ $totalBoxes }} {{ Str::plural('box', $totalBoxes) }})</div>
    <div class="table-scroll"><table>
        <thead>
            <tr>
                <th style="width:28px">#</th>
                <th>Box Code</th>
                <th>Product</th>
                <th>Status</th>
                <th>Items</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transfer->boxes as $i => $tb)
            @php $box = $tb->box; @endphp
            <tr>
                <td style="color:#7a81a0">{{ $i + 1 }}</td>
                <td style="font-family:monospace;font-weight:600;">{{ $box?->box_code ?? '—' }}</td>
                <td>{{ $box?->product?->name ?? '—' }}</td>
                <td>
                    {{-- The box's state on this transfer, not its current stock status --}}
                    @if ($tb->is_damaged)
                        <span class="status-damaged">Damaged</span>
                        @if ($tb->damage_notes)<div style="font-size:12px;color:#666;margin-top:2px">{{ $tb->damage_notes }}</div>@endif
                    @elseif ($tb->is_received)
                        <span class="status-full">Received</span>
                    @elseif ($transfer->shipped_at)
                        <span class="status-partial">In transit</span>
                    @else
                        <span>Packed</span>
                    @endif
                </td>
                <td>{{ $box ? number_format($box->items_remaining) : '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#999;font-style:italic;padding:16px">No boxes recorded</td>
            </tr>
            @endforelse
        </tbody>
        @if ($totalBoxes > 0)
        <tfoot>
            <tr>
                <td colspan="4">Total{{ $damagedBoxes ? ' (excluding ' . $damagedBoxes . ' damaged ' . Str::plural('box', $damagedBoxes) . ')' : '' }}</td>
                <td>{{ number_format($totalItems) }} items</td>
            </tr>
        </tfoot>
        @endif
    </table></div>

    {{-- ── Product summary ── --}}
    @if ($transfer->items->isNotEmpty())
    <div class="section-heading">Product Summary</div>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align:right">Requested</th>
                <th style="text-align:right">Approved</th>
                <th style="text-align:right">Boxes shipped</th>
                <th style="text-align:right">Items shipped</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transfer->items as $item)
            <tr>
                <td>{{ $item->product?->name ?? '—' }}</td>
                @php $shippedBoxes = ($boxesByProduct[$item->product_id] ?? collect())->count(); @endphp
                <td style="text-align:right;font-family:monospace">{{ number_format($item->quantity_requested) }}</td>
                <td style="text-align:right;font-family:monospace">{{ $item->quantity_approved !== null ? number_format($item->quantity_approved) : '—' }}</td>
                <td style="text-align:right;font-family:monospace">{{ $shippedBoxes ?: '—' }}</td>
                <td style="text-align:right;font-family:monospace">{{ $item->quantity_shipped !== null ? number_format($item->quantity_shipped) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Notes ── --}}
    <div class="section-heading">Notes</div>
    <div class="notes-box {{ $transfer->notes || $transfer->review_notes ? '' : 'empty' }}">
        @if ($transfer->notes || $transfer->review_notes)
            @if ($transfer->notes)<div><strong>Shop's request:</strong> {{ $transfer->notes }}</div>@endif
            @if ($transfer->review_notes)<div @if ($transfer->notes) style="margin-top:6px" @endif><strong>Approval note:</strong> {{ $transfer->review_notes }}</div>@endif
        @else
            No notes recorded for this transfer.
        @endif
    </div>

    {{-- ── Signatures ── --}}
    <div class="section-heading">Acknowledgement &amp; Signatures</div>
    {{-- Captured signatures sit on the line; blank lines are for signing on paper. --}}
    <div class="sig-row">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Dispatched by</div>
            <div class="sig-name">{{ ($transfer->shippedBy ?? $transfer->packingDoneBy ?? $transfer->packedBy)?->name ?? '' }}</div>
        </div>
        <div class="sig-block">
            @if ($transfer->handover_signature)
                <div class="sig-line" style="height:auto;border-bottom-width:1.5px"><img src="{{ $transfer->handover_signature }}" alt="Transporter signature" style="height:40px;display:block"></div>
            @else
                <div class="sig-line"></div>
            @endif
            <div class="sig-label">Transporter</div>
            <div class="sig-name">{{ $transfer->handed_to_name ?? $transfer->transporter?->name ?? '' }}</div>
        </div>
        <div class="sig-block">
            @if ($transfer->receipt_signature)
                <div class="sig-line" style="height:auto;border-bottom-width:1.5px"><img src="{{ $transfer->receipt_signature }}" alt="Receiver signature" style="height:40px;display:block"></div>
            @else
                <div class="sig-line"></div>
            @endif
            <div class="sig-label">Received by</div>
            <div class="sig-name">{{ $transfer->received_by_name ?? $transfer->receivedBy?->name ?? '' }}</div>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="doc-footer">
        {{ config('tenant.name') }} · {{ $transfer->transfer_number }} · Printed {{ local_time(now())->format('d M Y, H:i') }}
    </div>

    {{-- ── Print button (hidden when printing) ── --}}
    <div class="no-print" style="text-align:center;margin-top:24px">
        <button class="print-btn" onclick="window.print()">
            Print / Save as PDF
        </button>
        <div style="margin-top:8px;font-size:11px;color:#7a81a0">Or use your browser's Print to save it as a PDF</div>
    </div>

</div>
</body>
</html>
