<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note — {{ $transfer->transfer_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size:16px;
            color: #1a1a2e;
            background: #fff;
            padding: 0;
        }

        .page {
            max-width: 800px;
            margin: 0 auto;
            padding: 32px 36px;
        }

        /* ── Header ── */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1a1a2e;
            padding-bottom: 18px;
            margin-bottom: 20px;
        }
        .doc-header .brand { font-size:26px; font-weight: 800; letter-spacing: -0.5px; }
        .doc-header .doc-type { text-align: right; }
        .doc-header .doc-type .title { font-size:24px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .doc-header .doc-type .number { font-size:16px; color: #555; margin-top: 3px; font-family: monospace; }
        .doc-header .doc-type .status-badge {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size:13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        /* ── Info grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }
        .info-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .info-card .card-head {
            background: #f5f5f5;
            padding: 8px 14px;
            font-size:12px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #555;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-card .card-body { padding: 12px 14px; }
        .info-card .location-name { font-size:18px; font-weight: 700; margin-bottom: 3px; }
        .info-card .location-detail { font-size:14px; color: #555; line-height: 1.5; }

        /* ── Transporter card ── */
        .transporter-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        /* ── Meta row ── */
        .meta-row {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            padding: 12px 16px;
            background: #f8f8f8;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }
        .meta-item { }
        .meta-label { font-size:12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #888; }
        .meta-value { font-size:16px; font-weight: 600; color: #1a1a2e; margin-top: 2px; }

        /* ── Section heading ── */
        .section-heading {
            font-size:13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-bottom: 20px;
        }
        thead th {
            background: #1a1a2e;
            color: #fff;
            padding: 9px 12px;
            text-align: left;
            font-size:13px;
            font-weight: 600;
            letter-spacing: 0.4px;
        }
        thead th:last-child { text-align: right; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        tbody td:last-child { text-align: right; }
        tfoot td {
            padding: 9px 12px;
            font-weight: 700;
            border-top: 2px solid #1a1a2e;
            background: #f0f0f0;
        }
        tfoot td:last-child { text-align: right; }

        .status-full    { color: #2e7d32; font-weight: 600; }
        .status-partial { color: #e65100; font-weight: 600; }
        .status-damaged { color: #b71c1c; font-weight: 600; }

        /* ── Notes ── */
        .notes-box {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 14px;
            min-height: 56px;
            font-size: 12.5px;
            color: #333;
            margin-bottom: 20px;
        }
        .notes-box.empty { color: #999; font-style: italic; }

        /* ── Signatures ── */
        .sig-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 24px;
        }
        .sig-block { }
        .sig-line {
            border-bottom: 1.5px solid #333;
            height: 44px;
            margin-bottom: 8px;
        }
        .sig-label { font-size:13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #444; }
        .sig-name  { font-size:14px; color: #666; margin-top: 3px; }

        /* ── Footer ── */
        .doc-footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size:13px;
            color: #999;
        }

        /* ── Print button (screen only) ── */
        .print-btn {
            display: block;
            margin: 20px auto 0;
            padding: 10px 28px;
            background: #1a1a2e;
            color: white;
            border: none;
            border-radius: 6px;
            font-size:17px;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: 0.3px;
        }
        .print-btn:hover { background: #2d2d4e; }

        @media print {
            body { padding: 0; }
            .page { padding: 18px 22px; }
            .no-print { display: none !important; }
            thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tfoot td  { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    
/* Responsive base — applied to all transfer pages */
@media(max-width:600px) {
    /* Cards */
    .tl-card, .rf-card {
        border-radius:var(--rsm, 8px);
    }
    /* Tables inside cards — make them scroll horizontally */
    table {
        display:block;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        white-space:nowrap;
    }
    /* Prevent text overflow on narrow screens */
    .tl-num, .rf-prod-name, .tl-route-node {
        max-width:140px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    /* Badges wrap instead of overflow */
    .tl-card-meta, .tl-dates {
        flex-wrap:wrap;
        gap:4px;
    }
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
        'boxes.box.product',
        'items.product',
    ]);
    $totalBoxes = $transfer->boxes->count();
    $totalItems = $transfer->boxes->sum(fn($tb) => $tb->box?->items_remaining ?? 0);
@endphp

<div class="page">

    {{-- ── Document header ── --}}
    <div class="doc-header">
        <div>
            <div class="brand">Smart Inventory</div>
            <div style="font-size:14px;color:#666;margin-top:4px;">Warehouse Management System</div>
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
            <div class="meta-value">{{ $transfer->shipped_at ? $transfer->shipped_at->format('d M Y, H:i') : '—' }}</div>
        </div>
        @if ($transfer->packed_at)
        <div class="meta-item">
            <div class="meta-label">Date Packed</div>
            <div class="meta-value">{{ $transfer->packed_at->format('d M Y, H:i') }}</div>
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
        <div class="meta-item">
            <div class="meta-label">Printed</div>
            <div class="meta-value">{{ now()->format('d M Y, H:i') }}</div>
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
    <table>
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
                <td style="color:#999;font-size:13px">{{ $i + 1 }}</td>
                <td style="font-family:monospace;font-weight:600;">{{ $box?->box_code ?? '—' }}</td>
                <td>{{ $box?->product?->name ?? '—' }}</td>
                <td>
                    @if ($box)
                        @php $s = $box->status->value; @endphp
                        <span class="status-{{ $s }}">{{ ucfirst($s) }}</span>
                    @else —
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
                <td colspan="4">Total</td>
                <td>{{ number_format($totalItems) }} items</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- ── Product summary ── --}}
    @if ($transfer->items->isNotEmpty())
    <div class="section-heading">Product Summary</div>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align:right">Qty Requested</th>
                <th style="text-align:right">Qty Shipped</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transfer->items as $item)
            <tr>
                <td>{{ $item->product?->name ?? '—' }}</td>
                <td style="text-align:right;font-family:monospace">{{ number_format($item->quantity_requested) }}</td>
                <td style="text-align:right;font-family:monospace">{{ $item->quantity_shipped !== null ? number_format($item->quantity_shipped) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Notes ── --}}
    <div class="section-heading">Notes</div>
    <div class="notes-box {{ $transfer->notes ? '' : 'empty' }}">
        {{ $transfer->notes ?: 'No notes recorded for this transfer.' }}
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
            <div class="sig-name">&nbsp;</div>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="doc-footer">
        This delivery note is generated by Smart Inventory · {{ $transfer->transfer_number }} · Printed {{ now()->format('d M Y H:i') }}
    </div>

    {{-- ── Print button (hidden when printing) ── --}}
    <div class="no-print" style="text-align:center;margin-top:24px">
        <button class="print-btn" onclick="window.print()">
            Print / Save as PDF
        </button>
        <div style="margin-top:10px;font-size:14px;color:#999">Use your browser's Print function to save as PDF</div>
    </div>

</div>
</body>
</html>
