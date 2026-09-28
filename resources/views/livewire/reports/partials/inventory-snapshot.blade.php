{{--
  Inventory snapshot — stock on hand right now (Daily Report, owner + shop).
  One half-width card placed LAST in the report grid: the grid packs densely,
  so it fills whichever row was left with a single card.
  Vars: $snap (InventoryAnalyticsService::getStockSnapshot), $showCost (owner with profit on),
        $showLocations (owner "All shops" view).
  Opened boxes are ordinary stock (loose sales, exchanges, damage checks…) — shown, not flagged.
--}}
@php
    $t     = $snap['totals'];
    $cols  = 5;
    $share = fn ($v) => $t['retail_value'] > 0 ? round($v / $t['retail_value'] * 100) : 0;
    $groups = [];
    if ($showLocations && count($snap['by_location'])) {
        $groups['By location'] = $snap['by_location'];
    }
    $groups['By category'] = $snap['by_category'];
    $marginPct = ($showCost && $t['retail_value'] > 0) ? round($t['margin_value'] / $t['retail_value'] * 100, 1) : null;
@endphp
<div class="isn">
<style>
.isn { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0; }
.isn-head { padding:14px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:baseline;gap:10px;flex-wrap:wrap; }
.isn-title { font-size:13px;font-weight:700;color:var(--text); }
.isn-meta { font-size:11px;color:var(--text-dim); }
.isn-tbl { width:max-content;min-width:100%;border-collapse:collapse; }
.isn-tbl th, .isn-tbl td { white-space:nowrap; }
.isn-tbl thead tr { border-bottom:2px solid var(--border); }
.isn-tbl th { padding:10px 12px;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);text-align:right; }
.isn-tbl th:first-child, .isn-tbl td:first-child { text-align:left; }
.isn-tbl td { padding:10px 12px;font-size:13px;text-align:right;border-bottom:1px solid var(--border);vertical-align:middle; }
.isn-tbl td.isn-sec { padding:10px 12px 6px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--accent);text-align:left;border-bottom:none; }
.isn-tbl tr.isn-total td { font-weight:700;border-top:2px solid var(--border);border-bottom:none; }
.isn-foot { font-size:11px;color:var(--text-dim);padding:10px 14px 12px;border-top:1px solid var(--border);line-height:1.5; }
.isn-tbl td.isn-note { font-size:11px;color:var(--text-dim);text-align:left;white-space:normal;border-bottom:none;padding-top:8px;padding-bottom:12px; }
.isn-name { font-weight:600;color:var(--text); }
.isn-val { font-family:var(--mono);font-weight:700;color:var(--text); }
.isn-num { font-family:var(--mono);color:var(--text-sub); }
.isn-sub { display:block;font-weight:500;font-size:10.5px;font-weight:500;color:var(--text-dim);margin-top:2px;font-family:inherit; }
@media (max-width:640px) {
    .isn-head { padding:12px 14px; }
    .isn-tbl th, .isn-tbl td { padding-left:12px;padding-right:12px; }
}
</style>

<div class="isn-head">
    <div class="isn-title">Inventory Snapshot</div>
    <div class="isn-meta">Right now · not affected by the date filter</div>
</div>

<div class="m-scroll">
<table class="isn-tbl">
    <thead><tr>
        <th></th><th>Stock value</th><th>Full boxes</th><th>Opened</th><th>Items</th>
    </tr></thead>
    <tbody>
    @foreach($groups as $label => $rows)
        <tr><td colspan="{{ $cols }}" class="isn-sec">{{ $label }}</td></tr>
        @forelse($rows as $r)
        @php
            $flags = array_filter([
                number_format($r['products']) . ' ' . \Illuminate\Support\Str::plural('product', $r['products']),
                !empty($r['damaged_boxes']) ? $r['damaged_boxes'] . ' damaged' : null,
                !empty($r['in_transit_boxes']) ? $r['in_transit_boxes'] . ' in transit' : null,
            ]);
        @endphp
        <tr>
            <td>
                <span class="isn-name">{{ $r['name'] }}</span>
                <span class="isn-sub">@isset($r['type'])<b style="color:{{ $r['type'] === 'warehouse' ? 'var(--violet)' : 'var(--accent)' }}">{{ $r['type'] === 'warehouse' ? 'Warehouse' : 'Shop' }}</b> · @endisset{{ implode(' · ', $flags) }}</span>
            </td>
            <td>
                <span class="isn-val">{{ number_format($r['retail_value']) }}</span>
                <span class="isn-sub">{{ $share($r['retail_value']) }}%@if($showCost) · cost {{ number_format($r['cost_value']) }}@endif</span>
            </td>
            <td class="isn-num">{{ number_format($r['full_boxes']) }}<span class="isn-sub">{{ number_format($r['full_items']) }} pcs</span></td>
            <td class="isn-num">{{ number_format($r['opened_boxes']) }}<span class="isn-sub">{{ number_format($r['opened_items']) }} pcs</span></td>
            <td class="isn-num">{{ number_format($r['items']) }}</td>
        </tr>
        @empty
        <tr><td colspan="{{ $cols }}" class="isn-note">No stock on hand</td></tr>
        @endforelse
    @endforeach
        <tr class="isn-total">
            <td>Total</td>
            <td><span class="isn-val">{{ number_format($t['retail_value']) }}</span>@if($showCost)<span class="isn-sub">cost {{ number_format($t['cost_value']) }}</span>@endif</td>
            <td class="isn-num">{{ number_format($t['full_boxes']) }}<span class="isn-sub">{{ number_format($t['full_items']) }} pcs</span></td>
            <td class="isn-num">{{ number_format($t['opened_boxes']) }}<span class="isn-sub">{{ number_format($t['opened_items']) }} pcs</span></td>
            <td class="isn-num">{{ number_format($t['items']) }}</td>
        </tr>
    </tbody>
</table>
</div>
<div class="isn-foot">
            {{ number_format($t['products']) }} {{ \Illuminate\Support\Str::plural('product', $t['products']) }} in stock
            · {{ number_format($t['damaged_boxes']) }} damaged {{ \Illuminate\Support\Str::plural('box', $t['damaged_boxes']) }}
            · {{ number_format($t['in_transit_boxes']) }} {{ \Illuminate\Support\Str::plural('box', $t['in_transit_boxes']) }} in transit (not counted)
            @if($showCost) · potential margin {{ number_format($t['margin_value']) }} ({{ $marginPct }}%)@endif
            · values at selling price
</div>
</div>
