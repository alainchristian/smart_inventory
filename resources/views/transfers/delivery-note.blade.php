<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note — {{ $transfer->transfer_number }}</title>
    <style>
        /* Print document: its own palette (no app CSS variables here). */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; line-height: 1.45; color: #1a1f36; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 32px 36px; }

        /* Header */
        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
                      border-bottom: 2px solid #1a1f36; padding-bottom: 14px; margin-bottom: 18px; }
        .doc-header .brand { font-size: 22px; font-weight: 800; letter-spacing: -0.3px; }
        .doc-header .brand-sub { font-size: 12px; color: #7a81a0; margin-top: 2px; }
        .doc-header .doc-type { text-align: right; }
        .doc-header .doc-type .title { font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .doc-header .doc-type .number { font-size: 14px; color: #4a5372; margin-top: 2px; font-family: Consolas, monospace; font-weight: 700; }
        .doc-header .doc-type .status-badge { display: inline-block; margin-top: 6px; padding: 2px 9px; border-radius: 6px; font-size: 11px;
                      font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase; background: #e7f6f3; color: #0e9e86; }

        /* Meta row */
        .meta-row { display: flex; gap: 22px; flex-wrap: wrap; padding: 10px 14px; border: 1px solid #e2e6f3; border-radius: 8px; margin-bottom: 16px; }
        .meta-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #7a81a0; }
        .meta-value { font-size: 13px; font-weight: 600; margin-top: 2px; }

        /* Cards */
        .info-grid, .transporter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
        .info-card { border: 1px solid #e2e6f3; border-radius: 8px; }
        .info-card .card-head { padding: 7px 12px; font-size: 10px; font-weight: 700; letter-spacing: 0.7px; text-transform: uppercase;
                                color: #7a81a0; border-bottom: 1px solid #e2e6f3; }
        .info-card .card-body { padding: 10px 12px; }
        .info-card .location-name { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
        .info-card .location-detail { font-size: 12px; color: #4a5372; }

        /* Sections + tables */
        .section-heading { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #4a5372;
                           padding-bottom: 6px; margin: 4px 0 8px; border-bottom: 1px solid #e2e6f3; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; margin-bottom: 18px; }
        thead th { padding: 7px 10px; text-align: left; font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
                   color: #7a81a0; border-bottom: 2px solid #1a1f36; }
        thead th:last-child { text-align: right; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #eceff7; vertical-align: top; }
        tbody td:last-child { text-align: right; }
        tfoot td { padding: 8px 10px; font-weight: 700; border-top: 2px solid #1a1f36; }
        tfoot td:last-child { text-align: right; }
        .status-full    { color: #0e9e86; font-weight: 600; }
        .status-partial { color: #7c3aed; font-weight: 600; }
        .status-damaged { color: #e11d48; font-weight: 600; }

        /* Notes */
        .notes-box { border: 1px solid #e2e6f3; border-radius: 8px; padding: 10px 12px; min-height: 48px; font-size: 12.5px; color: #1a1f36; margin-bottom: 18px; }
        .notes-box.empty { color: #7a81a0; font-style: italic; }

        /* Signatures */
        .sig-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 22px; }
        .sig-line { border-bottom: 1.5px solid #1a1f36; height: 40px; margin-bottom: 6px; }
        .sig-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #4a5372; }
        .sig-name  { font-size: 12px; color: #7a81a0; margin-top: 2px; }

        /* Footer + print button */
        .doc-footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #e2e6f3; text-align: center; font-size: 11px; color: #7a81a0; }
        .print-btn { display: block; margin: 18px auto 0; padding: 9px 22px; background: #3b6fd4; color: #fff; border: none; border-radius: 8px;
                     font-size: 13px; font-weight: 600; cursor: pointer; }
        .print-btn:hover { opacity: .88; }

        @media print {
            .page { padding: 0; }
            .no-print { display: none !important; }
        }
        @media (max-width: 600px) {
            .page { padding: 16px; }
            .doc-header { flex-direction: column; }
            .doc-header .doc-type { text-align: left; }
            .info-grid, .transporter-row, .sig-row { grid-template-columns: 1fr; }
            .table-scroll { overflow-x: auto; }
            .table-scroll table { min-width: 520px; }
        }
    </style>
</head>
<body>
@php
    $transfer->loadMissing([
        'fromWarehouse',
        'toShop',
        'transporter',
        'packedBy',
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

    {{-- ── Meta row: dates ── --}}
    <div class="meta-row">
        <div class="meta-item">
            <div class="meta-label">Date Shipped</div>
            <div class="meta-value">{{ $transfer->shipped_at ? local_time($transfer->shipped_at)->format('d M Y, H:i') : '—' }}</div>
        </div>
        @if ($transfer->packed_at)
        <div class="meta-item">
            <div class="meta-label">Date Packed</div>
            <div class="meta-value">{{ local_time($transfer->packed_at)->format('d M Y, H:i') }}</div>
        </div>
        @endif
        <div class="meta-item">
            <div class="meta-label">Total Boxes</div>
            <div class="meta-value">{{ $totalBoxes }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Total Items</div>
            <div class="meta-value">{{ number_format($totalItems) }}</div>
        </div>
        @if($transfer->received_at)
        <div class="meta-item">
            <div class="meta-label">Date Received</div>
            <div class="meta-value">{{ local_time($transfer->received_at)->format('d M Y, H:i') }}</div>
        </div>
        @endif
        <div class="meta-item">
            <div class="meta-label">Printed</div>
            <div class="meta-value">{{ local_time(now())->format('d M Y, H:i') }}</div>
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
    <div class="sig-row">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Packed / Dispatched By</div>
            <div class="sig-name">{{ $transfer->packedBy?->name ?? '' }}</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Transporter</div>
            <div class="sig-name">{{ $transfer->transporter?->name ?? '' }}</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Received By</div>
            <div class="sig-name">{{ $transfer->receivedBy?->name ?? '' }}</div>
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
