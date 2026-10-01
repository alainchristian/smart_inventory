<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Picking List — {{ $transfer->transfer_number }}</title>
    @include('transfers.partials.print-styles')
</head>
<body>
@php
    $totalToPick = $lines->sum('to_pick');
    $totalApproved = $lines->sum('approved');
@endphp
<div class="page">

    <div class="doc-header">
        <div>
            <div class="brand">{{ config('tenant.name') }}</div>
            <div class="brand-sub">{{ $transfer->fromWarehouse?->name }} → {{ $transfer->toShop?->name }}</div>
        </div>
        <div class="doc-type">
            <div class="title">Picking list</div>
            <div class="number">{{ $transfer->transfer_number }}</div>
            <span class="status-badge">{{ $transfer->status->label() }}</span>
        </div>
    </div>

    <div class="meta-row">
        <div class="meta-item">
            <div class="meta-label">Requested</div>
            <div class="meta-value">{{ local_time($transfer->requested_at)?->format('d M Y, H:i') }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Approved</div>
            <div class="meta-value">{{ $transfer->reviewed_at ? local_time($transfer->reviewed_at)->format('d M Y, H:i') . ($transfer->reviewedBy ? ' · ' . $transfer->reviewedBy->name : '') : '—' }}</div>
        </div>
        @if($transfer->needed_by)
        <div class="meta-item">
            <div class="meta-label">Needed by</div>
            <div class="meta-value">{{ $transfer->needed_by->format('D d M Y') }}</div>
        </div>
        @endif
        <div class="meta-item">
            <div class="meta-label">Boxes to pick</div>
            <div class="meta-value">{{ $totalToPick }} of {{ $totalApproved }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Printed</div>
            <div class="meta-value">{{ local_time(now())->format('d M Y, H:i') }}</div>
        </div>
    </div>

    <div class="section-heading">What to pick</div>
    <div class="table-scroll"><table>
        <thead>
            <tr>
                <th style="width:28px"></th>
                <th>Product</th>
                <th>Barcode</th>
                <th style="text-align:right">Approved</th>
                <th style="text-align:right">Packed</th>
                <th style="text-align:right">To pick</th>
                <th style="text-align:left">Oldest boxes first</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
                <tr>
                    <td><span class="check-box"></span></td>
                    <td style="min-width:150px"><strong>{{ $line['product']?->name }}</strong><div style="font-size:11px;color:#7a81a0">{{ $line['product']?->sku }} · {{ $line['product']?->items_per_box }}/box</div></td>
                    <td style="font-family:Consolas, monospace">{{ $line['product']?->barcode }}</td>
                    <td style="text-align:right">{{ $line['approved'] }}</td>
                    <td style="text-align:right">{{ $line['packed'] }}</td>
                    <td style="text-align:right"><strong>{{ $line['to_pick'] }}</strong></td>
                    <td style="text-align:left;min-width:220px">
                        @if($line['to_pick'] === 0)
                            <span style="color:#0e9e86">Done</span>
                        @elseif($line['suggested']->isEmpty())
                            <span class="status-damaged">None in stock</span>
                        @else
                            <div class="codes">{{ $line['suggested']->implode(' · ') }}</div>
                            @if($line['suggested']->count() < $line['to_pick'])
                                <div class="status-damaged" style="font-size:11px">Only {{ $line['suggested']->count() }} in stock</div>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table></div>

    @if($transfer->notes || $transfer->review_notes)
        <div class="section-heading">Notes</div>
        <div class="notes-box">
            @if($transfer->notes)<div><strong>Shop's request:</strong> {{ $transfer->notes }}</div>@endif
            @if($transfer->review_notes)<div @if($transfer->notes) style="margin-top:6px" @endif><strong>Approval note:</strong> {{ $transfer->review_notes }}</div>@endif
        </div>
    @endif

    <div class="sig-row" style="grid-template-columns:1fr 1fr">
        <div><div class="sig-line"></div><div class="sig-label">Picked by</div></div>
        <div><div class="sig-line"></div><div class="sig-label">Checked by</div></div>
    </div>

    <div class="doc-footer">{{ config('tenant.name') }} · {{ $transfer->transfer_number }} · Printed {{ local_time(now())->format('d M Y, H:i') }}</div>
    <button class="print-btn no-print" onclick="window.print()">Print</button>
</div>
</body>
</html>
