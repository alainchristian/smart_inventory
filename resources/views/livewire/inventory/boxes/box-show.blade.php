<div style="font-family:var(--font)">
<style>
/* ── Box show page ─────────────────────────────── */
.bs-back { display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;
           color:var(--text-dim);text-decoration:none;padding:6px 0;margin-bottom:20px;
           transition:color var(--tr) }
.bs-back:hover { color:var(--text) }

.bs-header { display:flex;align-items:flex-start;justify-content:space-between;
             gap:12px;margin-bottom:24px;flex-wrap:wrap }
.bs-code  { font-family:var(--mono);font-size:28px;font-weight:800;letter-spacing:-.5px;color:var(--text) }
.bs-badge { display:inline-flex;align-items:center;padding:4px 13px;border-radius:20px;
            font-size:12px;font-weight:700;margin-top:6px }

/* Info card */
.bs-card { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);
           padding:22px;margin-bottom:24px }
.bs-card-title { font-size:13px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
                 color:var(--text-dim);margin-bottom:16px }
.bs-grid { display:grid;grid-template-columns:1fr 1fr;gap:0 }
.bs-field { padding:12px 0;border-bottom:1px solid var(--border);display:grid;grid-template-columns:140px 1fr;align-items:start;gap:10px }
.bs-field:last-child { border-bottom:none }
.bs-field-label { font-size:12px;font-weight:600;color:var(--text-dim);padding-top:1px }
.bs-field-val   { font-size:13px;color:var(--text);font-weight:500 }

/* Fill bar */
.bs-bar-track { height:6px;background:var(--surface3);border-radius:3px;margin-top:6px;overflow:hidden;max-width:160px }
.bs-bar-fill  { height:100%;border-radius:3px }

/* Table */
.bs-table-wrap { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden }
.bs-section-title { font-size:15px;font-weight:800;color:var(--text);margin-bottom:14px }
.bs-table { width:100%;border-collapse:collapse;font-size:13px }
.bs-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.bs-table thead th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;
                     letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.bs-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.bs-table tbody tr:last-child { border-bottom:none }
.bs-table tbody tr:hover { background:var(--surface2) }
.bs-table td { padding:11px 16px;vertical-align:middle }

.bs-badge-sm { display:inline-flex;align-items:center;padding:2px 8px;border-radius:12px;
               font-size:11px;font-weight:700;white-space:nowrap }

@media(max-width:700px) {
    .bs-grid { grid-template-columns:1fr }
    .bs-field { grid-template-columns:120px 1fr }
    .bs-hide-sm { display:none !important }
}
</style>

{{-- Back button --}}
<a href="{{ route('owner.boxes.index') }}" class="bs-back">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <polyline points="15 18 9 12 15 6"/>
    </svg>
    All Boxes
</a>

{{-- Header --}}
@php
    $statusColor = match($box->status->value) {
        'full'    => ['bg'=>'var(--green-dim,rgba(22,163,74,.12))',  'color'=>'var(--green)'],
        'partial' => ['bg'=>'var(--amber-dim,rgba(217,119,6,.12))', 'color'=>'var(--amber)'],
        'empty'   => ['bg'=>'var(--surface2)',                       'color'=>'var(--text-dim)'],
        'damaged' => ['bg'=>'var(--red-dim,rgba(220,38,38,.12))',    'color'=>'var(--red)'],
        default   => ['bg'=>'var(--surface2)',                       'color'=>'var(--text-dim)'],
    };
    $fillPct = $box->items_total > 0
        ? round(($box->items_remaining / $box->items_total) * 100)
        : 0;
    $barColor = match($box->status->value) {
        'full'    => 'var(--green)',
        'partial' => 'var(--amber)',
        'damaged' => 'var(--red)',
        default   => 'var(--text-dim)',
    };
@endphp

<div class="bs-header">
    <div>
        <div class="bs-code">{{ $box->box_code }}</div>
        <span class="bs-badge"
              style="background:{{ $statusColor['bg'] }};color:{{ $statusColor['color'] }}">
            {{ ucfirst($box->status->value) }}
        </span>
    </div>
</div>

{{-- Info card --}}
<div class="bs-card">
    <div class="bs-card-title">Box Details</div>
    <div class="bs-grid">
        {{-- Column 1 --}}
        <div>
            <div class="bs-field">
                <div class="bs-field-label">Product</div>
                <div class="bs-field-val">{{ $box->product?->name ?? '—' }}</div>
            </div>
            <div class="bs-field">
                <div class="bs-field-label">Category</div>
                <div class="bs-field-val">{{ $box->product?->category?->name ?? '—' }}</div>
            </div>
            <div class="bs-field">
                <div class="bs-field-label">Location</div>
                <div class="bs-field-val">
                    {{ $box->location?->name ?? '—' }}
                    @if($box->location_type)
                    <span style="font-size:10px;color:var(--text-dim);margin-left:4px;text-transform:capitalize">
                        ({{ $box->location_type->value }})
                    </span>
                    @endif
                </div>
            </div>
            <div class="bs-field">
                <div class="bs-field-label">Items</div>
                <div class="bs-field-val">
                    <span style="font-weight:700">{{ number_format($box->items_remaining) }}</span>
                    <span style="color:var(--text-dim)"> / {{ number_format($box->items_total) }} remaining</span>
                    <div class="bs-bar-track">
                        <div class="bs-bar-fill"
                             style="width:{{ $fillPct }}%;background:{{ $barColor }}"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Column 2 --}}
        <div>
            <div class="bs-field">
                <div class="bs-field-label">Batch #</div>
                <div class="bs-field-val" style="font-family:var(--mono)">
                    {{ $box->batch_number ?? '—' }}
                </div>
            </div>
            <div class="bs-field">
                <div class="bs-field-label">Expiry Date</div>
                @php
                    $expiryStyle = '';
                    $expiryText  = '—';
                    if ($box->expiry_date) {
                        $expiryText = $box->expiry_date->format('d M Y');
                        $daysLeft   = now()->diffInDays($box->expiry_date, false);
                        if ($daysLeft <= 30)     $expiryStyle = 'color:var(--red);font-weight:700';
                        elseif ($daysLeft <= 90) $expiryStyle = 'color:var(--amber);font-weight:700';
                        else                     $expiryStyle = 'color:var(--green)';
                    }
                @endphp
                <div class="bs-field-val" style="{{ $expiryStyle }}">{{ $expiryText }}</div>
            </div>
            <div class="bs-field">
                <div class="bs-field-label">Received By</div>
                <div class="bs-field-val">{{ $box->receivedBy?->name ?? '—' }}</div>
            </div>
            <div class="bs-field">
                <div class="bs-field-label">Received At</div>
                <div class="bs-field-val">
                    {{ $box->received_at?->format('d M Y, H:i') ?? '—' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Movement history --}}
<div class="bs-section-title">Movement History</div>
<div class="bs-table-wrap">
    <div style="overflow-x:auto">
    <table class="bs-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>From</th>
                <th>To</th>
                <th>Items</th>
                <th class="bs-hide-sm">Moved By</th>
                <th class="bs-hide-sm">Reference</th>
                <th class="bs-hide-sm">Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($box->movements as $movement)
            @php
                $mvTypeColors = [
                    'transfer'    => ['bg'=>'var(--accent-dim,rgba(59,111,212,.12))', 'color'=>'var(--accent)'],
                    'consumption' => ['bg'=>'var(--amber-dim,rgba(217,119,6,.12))',  'color'=>'var(--amber)'],
                    'receive'     => ['bg'=>'var(--green-dim,rgba(22,163,74,.12))',   'color'=>'var(--green)'],
                    'damage'      => ['bg'=>'var(--red-dim,rgba(220,38,38,.12))',     'color'=>'var(--red)'],
                ];
                $mvColor = $mvTypeColors[$movement->movement_type] ?? ['bg'=>'var(--surface2)', 'color'=>'var(--text-dim)'];

                $fromStr = '—';
                if ($movement->from_location_type && $movement->from_location_id) {
                    $key = $movement->from_location_type->value . '_' . $movement->from_location_id;
                    $fromStr = $locationNames[$key] ?? ucfirst($movement->from_location_type->value) . ' #' . $movement->from_location_id;
                }
                $toStr = '—';
                if ($movement->to_location_type && $movement->to_location_id) {
                    $key = $movement->to_location_type->value . '_' . $movement->to_location_id;
                    $toStr = $locationNames[$key] ?? ucfirst($movement->to_location_type->value) . ' #' . $movement->to_location_id;
                }
            @endphp
            <tr>
                <td style="font-size:12px;color:var(--text-dim);white-space:nowrap">
                    {{ $movement->moved_at?->format('d M Y, H:i') ?? $movement->created_at->format('d M Y, H:i') }}
                </td>
                <td>
                    <span class="bs-badge-sm"
                          style="background:{{ $mvColor['bg'] }};color:{{ $mvColor['color'] }}">
                        {{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}
                    </span>
                </td>
                <td style="font-size:13px;color:var(--text-sub)">{{ $fromStr }}</td>
                <td style="font-size:13px;color:var(--text-sub)">{{ $toStr }}</td>
                <td style="font-family:var(--mono);font-size:13px;font-weight:600;color:var(--text)">
                    {{ number_format($movement->items_moved) }}
                </td>
                <td class="bs-hide-sm" style="font-size:12px;color:var(--text-sub)">
                    {{ $movement->movedBy?->name ?? '—' }}
                </td>
                <td class="bs-hide-sm" style="font-size:12px;color:var(--text-dim);font-family:var(--mono)">
                    @if($movement->reference_type && $movement->reference_id)
                        {{ class_basename($movement->reference_type) }} #{{ $movement->reference_id }}
                    @else
                        —
                    @endif
                </td>
                <td class="bs-hide-sm" style="font-size:12px;color:var(--text-dim);max-width:200px">
                    {{ $movement->notes ?? $movement->reason ?? '—' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div style="padding:40px 20px;text-align:center">
                        <div style="font-size:28px;margin-bottom:8px">📋</div>
                        <div style="font-size:14px;font-weight:700;color:var(--text-sub)">No movements recorded</div>
                        <div style="font-size:12px;color:var(--text-dim);margin-top:4px">
                            Box movements will appear here as items are transferred or consumed
                        </div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

</div>
