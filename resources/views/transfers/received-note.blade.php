<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Received Note — {{ $transfer->transfer_number }}</title>
    @include('transfers.partials.print-styles')
</head>
<body>
@php
    $fmt = fn ($at) => $at ? local_time($at)->format('d M Y, H:i') : '—';
    $boxes = $transfer->boxes;
    $good = $boxes->where('is_received', true)->where('is_damaged', false);
    $damaged = $boxes->where('is_damaged', true);
    $missing = $boxes->where('is_received', false);
    $byProduct = $boxes->groupBy(fn ($tb) => $tb->box?->product_id);
@endphp
<div class="page">

    <div class="doc-header">
        <div>
            <div class="brand">{{ config('tenant.name') }}</div>
            <div class="brand-sub">{{ $transfer->fromWarehouse?->name }} → {{ $transfer->toShop?->name }}</div>
        </div>
        <div class="doc-type">
            <div class="title">Goods received note</div>
            <div class="number">{{ $transfer->transfer_number }}</div>
            <span class="status-badge" @if($damaged->isNotEmpty() || $missing->isNotEmpty()) style="background:#fdecef;color:#e11d48" @endif>
                {{ $damaged->isNotEmpty() || $missing->isNotEmpty() ? 'Received with issues' : 'Received in full' }}
            </span>
        </div>
    </div>

    <div class="meta-row">
        <div class="meta-item"><div class="meta-label">Dispatched</div><div class="meta-value">{{ $fmt($transfer->shipped_at) }}{{ $transfer->shippedBy ? ' · ' . $transfer->shippedBy->name : '' }}</div></div>
        <div class="meta-item"><div class="meta-label">Arrived</div><div class="meta-value">{{ $fmt($transfer->delivered_at) }}{{ $transfer->deliveredBy ? ' · ' . $transfer->deliveredBy->name : '' }}</div></div>
        <div class="meta-item"><div class="meta-label">Received</div><div class="meta-value">{{ $fmt($transfer->received_at) }} · {{ $transfer->received_by_name ?? $transfer->receivedBy?->name }}</div></div>
        <div class="meta-item"><div class="meta-label">Transporter</div><div class="meta-value">{{ $transfer->transporter?->name ?? '—' }}{{ $transfer->handed_to_name ? ' · ' . $transfer->handed_to_name : '' }}</div></div>
    </div>

    <div class="meta-row" style="justify-content:space-between">
        <div class="meta-item"><div class="meta-label">Boxes shipped</div><div class="meta-value">{{ $boxes->count() }}</div></div>
        <div class="meta-item"><div class="meta-label">Into stock</div><div class="meta-value status-full">{{ $good->count() }} · {{ number_format($good->sum(fn ($tb) => $tb->box?->items_remaining ?? 0)) }} items</div></div>
        <div class="meta-item"><div class="meta-label">Damaged</div><div class="meta-value {{ $damaged->isNotEmpty() ? 'status-damaged' : '' }}">{{ $damaged->count() }}</div></div>
        <div class="meta-item"><div class="meta-label">Missing</div><div class="meta-value {{ $missing->isNotEmpty() ? 'status-damaged' : '' }}">{{ $missing->count() }}</div></div>
    </div>

    <div class="section-heading">By product</div>
    <div class="table-scroll"><table>
        <thead><tr><th>Product</th><th style="text-align:right">Approved</th><th style="text-align:right">Shipped</th><th style="text-align:right">Into stock</th><th style="text-align:right">Damaged</th><th style="text-align:right">Missing</th></tr></thead>
        <tbody>
            @foreach ($transfer->items as $item)
                @php $pb = $byProduct[$item->product_id] ?? collect(); @endphp
                <tr>
                    <td>{{ $item->product?->name }}@if ($item->short_reason)<div style="font-size:11px;color:#d97706">Packed short: {{ $item->short_reason }}</div>@endif</td>
                    <td style="text-align:right">{{ $item->boxesToSend() }}</td>
                    <td style="text-align:right">{{ $pb->count() }}</td>
                    <td style="text-align:right">{{ $pb->where('is_received', true)->where('is_damaged', false)->count() }}</td>
                    <td style="text-align:right">{{ $pb->where('is_damaged', true)->count() ?: '—' }}</td>
                    <td style="text-align:right">{{ $pb->where('is_received', false)->count() ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table></div>

    <div class="section-heading">Every box</div>
    <div class="table-scroll"><table>
        <thead><tr><th style="width:28px">#</th><th>Box</th><th>Product</th><th>Outcome</th><th>Items</th></tr></thead>
        <tbody>
            @foreach ($boxes->sortBy(fn ($tb) => [$tb->is_received && ! $tb->is_damaged ? 1 : 0, $tb->id])->values() as $i => $tb)
                <tr>
                    <td style="color:#7a81a0">{{ $i + 1 }}</td>
                    <td style="font-family:Consolas, monospace;font-weight:600">{{ $tb->box?->box_code }}</td>
                    <td>{{ $tb->box?->product?->name }}</td>
                    <td>
                        @if ($tb->is_damaged)
                            <span class="status-damaged">Damaged</span>@if ($tb->damage_notes)<div style="font-size:11px;color:#4a5372">{{ $tb->damage_notes }}</div>@endif
                        @elseif (! $tb->is_received)
                            <span class="status-damaged">Missing</span>
                        @else
                            <span class="status-full">Received</span>
                        @endif
                        @if ($tb->resolution)
                            <div style="font-size:11px;color:#4a5372">Resolved: {{ ['found' => 'found at the warehouse', 'lost' => 'written off as lost', 'received_late' => 'arrived late'][$tb->resolution] ?? $tb->resolution }}</div>
                        @endif
                    </td>
                    <td>{{ number_format($tb->box?->items_remaining ?? 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table></div>

    <div class="sig-row" style="grid-template-columns:1fr 1fr">
        <div class="sig-block">
            @if ($transfer->handover_signature)
                <div class="sig-line" style="height:auto"><img src="{{ $transfer->handover_signature }}" alt="Transporter signature" style="height:40px;display:block"></div>
            @else
                <div class="sig-line"></div>
            @endif
            <div class="sig-label">Delivered by (transporter)</div>
            <div class="sig-name">{{ $transfer->handed_to_name ?? $transfer->transporter?->name }}</div>
        </div>
        <div class="sig-block">
            @if ($transfer->receipt_signature)
                <div class="sig-line" style="height:auto"><img src="{{ $transfer->receipt_signature }}" alt="Receiver signature" style="height:40px;display:block"></div>
            @else
                <div class="sig-line"></div>
            @endif
            <div class="sig-label">Received by</div>
            <div class="sig-name">{{ $transfer->received_by_name ?? $transfer->receivedBy?->name }}</div>
        </div>
    </div>

    <div class="doc-footer">{{ config('tenant.name') }} · {{ $transfer->transfer_number }} · Printed {{ local_time(now())->format('d M Y, H:i') }}</div>
    <button class="print-btn no-print" onclick="window.print()">Print</button>
</div>
</body>
</html>
