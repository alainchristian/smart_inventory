{{--
  Inventory snapshot for the browser-print Daily Report (owner + shop print pages).
  Uses the host page's .cell / table styles. Full width: a print page is
  too narrow to fit five number columns in half of it.
  Vars: $snap, $showCost (owner print with profit=1 only), $showLocations, $variant ('owner'|'shop').
--}}
@php
    $t      = $snap['totals'];
    $groups = [];
    if ($showLocations && count($snap['by_location'])) {
        $groups['By location'] = $snap['by_location'];
    }
    $groups['By category'] = $snap['by_category'];
    $pcs = fn ($boxes, $items) => number_format($boxes) . ' · ' . number_format($items) . ' pcs';
@endphp
<style>
.isp td.isp-sec { text-align:left !important; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; padding-top:10px; padding-bottom:5px; background:#fff; }
.isp td.isp-note { text-align:left !important; font-size:11px; white-space:normal; }
.isp td, .isp th { white-space:nowrap; }
.isp .r { text-align:right; }
.isp small { font-size:10px; opacity:.75; }
</style>
<div class="cell span-2 isp">
    @if($variant === 'owner')
    <div class="cell-head"><span class="dot c-green"></span><span class="cell-title">Inventory Snapshot</span></div>
    @else
    <div class="section-heading">Inventory Snapshot</div>
    @endif
    <table>
        <thead><tr>
            <th>Right now</th><th class="r">Stock value</th><th class="r">Full boxes</th><th class="r">Opened</th><th>Items</th>
        </tr></thead>
        <tbody>
        @foreach($groups as $label => $rows)
            <tr><td colspan="5" class="isp-sec">{{ $label }}</td></tr>
            @forelse($rows as $r)
            <tr>
                <td>{{ $r['name'] }}@isset($r['type']) <small>({{ $r['type'] === 'warehouse' ? 'warehouse' : 'shop' }})</small>@endisset</td>
                <td class="r">{{ number_format($r['retail_value']) }}@if($showCost)<br><small>cost {{ number_format($r['cost_value']) }}</small>@endif</td>
                <td class="r">{{ $pcs($r['full_boxes'], $r['full_items']) }}</td>
                <td class="r">{{ $pcs($r['opened_boxes'], $r['opened_items']) }}</td>
                <td>{{ number_format($r['items']) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="isp-note">No stock on hand</td></tr>
            @endforelse
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="r">{{ number_format($t['retail_value']) }}@if($showCost)<br><small>cost {{ number_format($t['cost_value']) }}</small>@endif</td>
                <td class="r">{{ $pcs($t['full_boxes'], $t['full_items']) }}</td>
                <td class="r">{{ $pcs($t['opened_boxes'], $t['opened_items']) }}</td>
                <td>{{ number_format($t['items']) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="isp-note" style="font-weight:400">
                    {{ number_format($t['products']) }} {{ \Illuminate\Support\Str::plural('product', $t['products']) }} in stock
                    · {{ number_format($t['damaged_boxes']) }} damaged {{ \Illuminate\Support\Str::plural('box', $t['damaged_boxes']) }}
                    · {{ number_format($t['in_transit_boxes']) }} {{ \Illuminate\Support\Str::plural('box', $t['in_transit_boxes']) }} in transit (not counted)
                    · values at selling price · stock at the time of printing
                </td>
            </tr>
        </tfoot>
    </table>
</div>
