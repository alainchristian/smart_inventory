{{--
    Transfer list for owner, shop and warehouse (App\Livewire\Transfers\Concerns\ListsTransfers).
    Prefix tfl-; shared transfer parts come from <x-transfers.*> (tf-).
--}}
@use('App\Enums\TransferStatus', 'S')
<div class="tfl-page" style="font-family:var(--font)">
<style>
.tfl-page { padding:0 0 80px }
.tfl-kpis { margin-bottom:20px }
.tfl-kpis.tfl-kpis-3 { --kpi-cols:3 }

.tfl-card { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0;margin-bottom:20px }
.tfl-card-head { padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px }
.tfl-card-title { font-size:13px;font-weight:700;color:var(--text);margin:0 }
.tfl-card-sub { font-size:12px;color:var(--text-dim);margin-top:2px }
.tfl-count { font-size:11px;font-weight:700;font-family:var(--mono);padding:2px 8px;border-radius:20px;background:var(--amber-dim);color:var(--amber) }

/* Toolbar: search + filters, then status tabs */
.tfl-toolbar { padding:14px 18px 0;display:flex;gap:10px;align-items:center;flex-wrap:wrap }
.tfl-search-wrap { flex:1;min-width:220px;position:relative }
.tfl-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-dim);pointer-events:none }
.tfl-search { width:100%;padding:9px 11px 9px 34px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;background:var(--surface);
              color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.tfl-filters { display:flex;gap:10px;align-items:center }
.tfl-select { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);
              outline:none;cursor:pointer;font-family:var(--font);max-width:240px }
.tfl-search:focus, .tfl-select:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.tfl-clear { font-size:12px;font-weight:600;color:var(--accent);background:none;border:none;cursor:pointer;font-family:var(--font);white-space:nowrap;padding:0 }
.tfl-tabs-row { padding:12px 18px 14px;border-bottom:1px solid var(--border) }
.tfl-tab-n { font:700 11px var(--mono);padding:1px 7px;border-radius:20px;background:var(--surface2);color:var(--text-dim) }
.m-seg-btn.active .tfl-tab-n { background:var(--accent-dim);color:var(--accent) }

/* Table */
.tfl-table { width:100%;border-collapse:collapse;table-layout:fixed }
.tfl-table thead tr { border-bottom:2px solid var(--border) }
.tfl-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.tfl-table td { padding:12px 16px;font-size:13px;color:var(--text-sub);vertical-align:middle;white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.tfl-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.tfl-table tbody tr:last-child { border-bottom:none }
.tfl-table tbody tr:hover { background:var(--surface2) }
.tfl-num { font:700 13px var(--mono);color:var(--text);text-decoration:none;letter-spacing:-.3px }
.tfl-num:hover { color:var(--accent) }
.tfl-sub { font-size:12px;color:var(--text-dim);margin-top:2px;overflow:hidden;text-overflow:ellipsis }
.tfl-place { font-weight:600;color:var(--text) }
.tfl-date { color:var(--text-sub) }
.tfl-num-cell { text-align:right }
.tfl-mono { font:700 13px var(--mono);color:var(--text) }
.tfl-unit { font-size:12px;color:var(--text-dim) }
.tfl-acts { display:flex;gap:4px;justify-content:flex-end;align-items:center }
.tfl-action { padding:5px 11px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;
              font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap;text-decoration:none }
.tfl-action:hover { border-color:var(--accent);color:var(--accent) }
.tfl-ico { width:30px;height:30px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;color:var(--text-dim);transition:all var(--tr) }
.tfl-ico:hover { background:var(--surface3);color:var(--text) }

.tfl-empty { padding:56px 20px;text-align:center }
.tfl-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.tfl-empty-sub { font-size:13px;color:var(--text-dim);margin-bottom:16px }
.tfl-pages { padding:12px 16px;border-top:1px solid var(--border) }

@media (max-width:900px) { .tfl-kpis.tfl-kpis-3 { --kpi-cols-sm:3 } }
@media (max-width:640px) {
    .tfl-toolbar { padding:12px 12px 0 }
    .tfl-tabs-row { padding:10px 12px 12px }
    .tfl-search-wrap { min-width:0 }
    .tfl-filters { flex-direction:column;align-items:stretch }
    .tfl-select { max-width:none }
    .tfl-card-head { padding:12px }
}
</style>

{{-- ═══ Header ═══════════════════════════════════════════════════════ --}}
@php
    $headSub = [
        'owner'     => 'Stock moving from warehouses to shops',
        'shop'      => 'Stock you requested from the warehouse',
        'warehouse' => 'Requests from shops, and what is on the road',
    ][$role];
@endphp
<x-transfers.header title="Transfers" dup-title :sub="$headSub">
    @if($role === 'shop')
        <x-slot:actions>
            <a href="{{ route('shop.transfers.request') }}" wire:navigate class="tf-btn tf-btn-primary">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                New request
            </a>
        </x-slot:actions>
    @endif
</x-transfers.header>

{{-- ═══ Summary ══════════════════════════════════════════════════════ --}}
@php
    $icon = [
        'clock' => '<circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/>',
        'box'   => '<path stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        'truck' => '<path stroke-linejoin="round" d="M1 3h15v13H1zM16 8h4l3 3v5h-7"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        'check' => '<circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 12l3 3 5-6"/>',
        'alert' => '<path stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><path stroke-linecap="round" d="M12 9v4M12 17h.01"/>',
    ];
    $r = $summary['received'];
    $cards = [];
    $awaiting = ['label' => 'Awaiting approval', 'sub' => 'Waiting for the warehouse', 'tone' => 'amber', 'icon' => 'clock', 'val' => $summary['pending'],
        'rows' => [[$summary['pending_oldest'] ?? '—', 'Oldest waiting'], [number_format($summary['pending_boxes']), 'Boxes requested'], [$summary['pending_shops'], 'Shops asking']]];
    $road = ['label' => 'On the road', 'sub' => 'Shipped, not yet received', 'tone' => 'violet', 'icon' => 'truck', 'val' => $summary['in_transit'] + $summary['delivered'],
        'rows' => [[$summary['in_transit'], 'In transit'], [$summary['delivered'], 'Delivered, to receive'], [number_format($summary['road_boxes']), 'Boxes on the road']]];
    $received = ['label' => 'Received this month', 'sub' => 'Transfers completed', 'tone' => 'green', 'icon' => 'check', 'val' => $r['count'],
        'rows' => [[number_format($r['boxes']), 'Boxes received'], [$r['damaged'], 'Damaged boxes'], [$r['lead'] ?? '—', 'Avg request → received']]];
    if ($role === 'owner') {
        $cards = [$awaiting, $road, $received,
            ['label' => 'Discrepancies', 'sub' => 'Received this month', 'tone' => 'red', 'icon' => 'alert', 'val' => $summary['discrepancies'],
             'rows' => [[$r['damaged'], 'Damaged boxes'], [$r['missing'], 'Missing boxes'], [$summary['discrepancies_all'], 'All time']]]];
    } elseif ($role === 'warehouse') {
        $cards = [
            array_merge($awaiting, ['label' => 'To approve', 'sub' => 'Requests from shops']),
            ['label' => 'To pack', 'sub' => 'Approved, not shipped', 'tone' => 'accent', 'icon' => 'box', 'val' => $summary['approved'],
             'rows' => [[number_format($summary['approved_boxes']), 'Boxes to pack'], [$summary['packing_started'], 'Packing started'], [$summary['approved_oldest'] ?? '—', 'Oldest approved']]],
            array_merge($road, ['rows' => [[$summary['shipped_today'], 'Shipped today'], [$summary['delivered'], 'Delivered, to receive'], [number_format($summary['road_boxes']), 'Boxes on the road']]]),
        ];
    } else {
        $cards = [
            array_merge($awaiting, ['rows' => [[$summary['pending_oldest'] ?? '—', 'Oldest waiting'], [number_format($summary['pending_boxes']), 'Boxes requested'], [$summary['approved'], 'Approved, being packed']]]),
            array_merge($road, ['label' => 'Arriving', 'sub' => 'On the way to your shop',
                'rows' => [[$summary['in_transit'], 'In transit'], [$summary['delivered'], 'Delivered, to receive'], [number_format($summary['road_boxes']), 'Boxes coming']]]),
            $received,
        ];
    }
@endphp
<div class="ui-kpis m-kpis tfl-kpis {{ count($cards) === 3 ? 'tfl-kpis-3' : '' }}">
    @foreach($cards as $c)
        <div class="ui-kpi">
            <div class="ui-kpi-row">
                <div class="ui-kpi-icon" style="background:var(--{{ $c['tone'] }}-dim);color:var(--{{ $c['tone'] }})">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $icon[$c['icon']] !!}</svg>
                </div>
                <div class="ui-kpi-body">
                    <div class="ui-kpi-label">{{ $c['label'] }}</div>
                    <div class="ui-kpi-sub">{{ $c['sub'] }}</div>
                </div>
            </div>
            <div class="ui-kpi-val" style="color:var(--{{ $c['tone'] }})">{{ number_format($c['val']) }}</div>
            <div class="ui-kpi-divider"></div>
            <div class="ui-kpi-footer">
                @foreach($c['rows'] as [$v, $l])
                    <div class="ui-kpi-stat"><span class="ui-kpi-stat-v">{{ $v }}</span><span class="ui-kpi-stat-l">{{ $l }}</span></div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

@php
    // Fits the content width at 1440px; phones scroll sideways with the first column frozen.
    $cols = ['Transfer' => 165, ($role === 'shop' ? 'From' : 'Shop') => 250, 'Boxes' => 85, 'Status' => 140,
             'Requested' => 150, 'Last update' => 150, '' => 140];
    $minWidth = array_sum($cols);
@endphp

{{-- ═══ Needs you (warehouse) ════════════════════════════════════════ --}}
@if($showNeeds && $needs->isNotEmpty())
<div class="tfl-card">
    <div class="tfl-card-head">
        <div>
            <h2 class="tfl-card-title">Needs you</h2>
            <div class="tfl-card-sub">Requests to approve, transfers to pack or dispatch, and missing boxes to resolve — oldest first</div>
        </div>
        <span class="tfl-count">{{ $needs->count() }}</span>
    </div>
    <div class="m-scroll">
        <table class="tfl-table m-sticky-first" style="min-width:{{ $minWidth }}px">
            <colgroup>@foreach($cols as $w)<col style="width:{{ $w }}px">@endforeach</colgroup>
            <thead><tr>@foreach($cols as $label => $w)<th @if($label === 'Boxes') style="text-align:right" @endif>{{ $label === 'Last update' ? 'Waiting' : $label }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach($needs as $t)
                    @include('livewire.transfers.partials.list-row', ['t' => $t, 'role' => $role, 'waiting' => true])
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═══ All transfers ════════════════════════════════════════════════ --}}
<div class="tfl-card" x-data="{ f:false }">
    @if($showNeeds && $needs->isNotEmpty())
        <div class="tfl-card-head"><h2 class="tfl-card-title">Other transfers</h2></div>
    @endif

    <div class="tfl-toolbar">
        <div class="m-filter-bar tfl-search-wrap">
            <div class="m-grow" style="position:relative">
                <svg class="tfl-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                <input class="tfl-search" wire:model.live.debounce.300ms="search"
                       placeholder="{{ $role === 'shop' ? 'Search transfer # or product…' : 'Search transfer #, shop or product…' }}" aria-label="Search transfers">
            </div>
            <button type="button" class="m-filter-toggle m-only" @click="f = true">
                Filters @if($sheetFilters > 0)<span class="m-count">{{ $sheetFilters }}</span>@endif
            </button>
        </div>
        <div class="m-sheet-overlay m-only" x-show="f" x-cloak @click="f = false"></div>
        <div class="m-filter-panel tfl-filters" :class="{ open: f }">
            <div class="m-sheet-handle m-only"></div>
            <select class="tfl-select" wire:model.live="period" aria-label="Requested">
                @foreach($periods as $key => $label)
                    <option value="{{ $key }}">{{ $key === 'any' ? 'Requested: any time' : 'Requested: ' . strtolower($label) }}</option>
                @endforeach
            </select>
            @if($role !== 'shop')
                <select class="tfl-select" wire:model.live="shopFilter" aria-label="Shop">
                    <option value="">All shops</option>
                    @foreach($shops as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            @endif
            @if($filtersOn > 0 || $statusFilter !== 'all')
                <button type="button" class="tfl-clear m-hide" wire:click="clearFilters">Clear filters</button>
            @endif
            <div class="m-sheet-foot m-only">
                @if($sheetFilters > 0)<button type="button" class="tf-btn tf-btn-ghost" wire:click="clearFilters" @click="f = false">Clear</button>@endif
                <button type="button" class="tf-btn tf-btn-primary" @click="f = false">Done</button>
            </div>
        </div>
    </div>

    <div class="tfl-tabs-row">
        <div class="m-seg" role="tablist" aria-label="Status">
            @foreach($tabs as $key => $label)
                <button type="button" role="tab" aria-selected="{{ $statusFilter === $key ? 'true' : 'false' }}"
                        class="m-seg-btn {{ $statusFilter === $key ? 'active' : '' }}" wire:click="setStatus('{{ $key }}')">
                    {{ $label }} <span class="tfl-tab-n">{{ $counts[$key] ?? 0 }}</span>
                </button>
            @endforeach
        </div>
    </div>

    @if($transfers->isEmpty())
        <div class="tfl-empty">
            @if($filtersOn > 0 || $statusFilter !== 'all')
                <div class="tfl-empty-title">No transfers match these filters</div>
                <div class="tfl-empty-sub">Try another status or period, or clear the filters.</div>
                <button type="button" class="tf-btn tf-btn-ghost" wire:click="clearFilters">Clear filters</button>
            @elseif($showNeeds && $needs->isNotEmpty())
                <div class="tfl-empty-title">Nothing else yet</div>
                <div class="tfl-empty-sub">Shipped and finished transfers appear here.</div>
            @elseif($role === 'shop')
                <div class="tfl-empty-title">No transfers yet</div>
                <div class="tfl-empty-sub">Request boxes from the warehouse when your shop runs low.</div>
                <a href="{{ route('shop.transfers.request') }}" wire:navigate class="tf-btn tf-btn-primary">New request</a>
            @else
                <div class="tfl-empty-title">No transfers yet</div>
                <div class="tfl-empty-sub">Requests from shops appear here.</div>
            @endif
        </div>
    @else
        <div class="m-scroll">
            <table class="tfl-table m-sticky-first" style="min-width:{{ $minWidth }}px">
                <colgroup>@foreach($cols as $w)<col style="width:{{ $w }}px">@endforeach</colgroup>
                <thead><tr>@foreach($cols as $label => $w)<th @if($label === 'Boxes') style="text-align:right" @endif>{{ $label }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach($transfers as $t)
                        @include('livewire.transfers.partials.list-row', ['t' => $t, 'role' => $role, 'waiting' => false])
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
            <div class="tfl-pages">{{ $transfers->links() }}</div>
        @endif
    @endif
</div>
</div>
