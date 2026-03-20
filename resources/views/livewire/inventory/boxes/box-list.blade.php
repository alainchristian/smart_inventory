<div style="font-family:var(--font)">
<style>
/* ── KPI strip ───────────────────────────────────── */
.bx-kpis {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
    gap:16px;
    margin-bottom:24px;
}
/* Extend .bkpi with amber + red variants */
.bkpi.amber::after { background:linear-gradient(90deg,var(--amber),transparent) }
.bkpi.red::after   { background:linear-gradient(90deg,var(--red),transparent) }
.bkpi-icon.amber   { background:rgba(217,119,6,.12);color:var(--amber) }
.bkpi-icon.red     { background:rgba(220,38,38,.12);color:var(--red) }
.bkpi-pct.amber    { background:rgba(217,119,6,.15);color:var(--amber) }
.bkpi-pct.red      { background:rgba(220,38,38,.12);color:var(--red) }
.bkpi-pct.down     { background:rgba(220,38,38,.12);color:var(--red) }

/* ── Filter bar ──────────────────────────────────── */
.bx-bar { display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:16px }
.bx-search-wrap { flex:1;min-width:200px;position:relative }
.bx-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.bx-search {
    width:100%;padding:9px 11px 9px 33px;
    border:1.5px solid var(--border);border-radius:10px;
    font-size:13px;background:var(--surface);color:var(--text);
    outline:none;box-sizing:border-box;font-family:var(--font);
    transition:border-color var(--tr)
}
.bx-search:focus { border-color:var(--accent) }
.bx-select {
    padding:8px 12px;border:1.5px solid var(--border);border-radius:10px;
    font-size:13px;background:var(--surface);color:var(--text);
    outline:none;cursor:pointer;font-family:var(--font)
}
.bx-toggle-wrap {
    display:flex;align-items:center;gap:7px;padding:7px 14px;
    border:1.5px solid var(--border);border-radius:10px;background:var(--surface);
    cursor:pointer;font-size:13px;color:var(--text-sub);white-space:nowrap
}
.bx-toggle-wrap.active { border-color:var(--amber);color:var(--amber);background:var(--amber-dim,rgba(217,119,6,.08)) }
.bx-btn-clear {
    padding:9px 16px;background:transparent;border:1.5px solid var(--border);
    border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;
    font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap
}
.bx-btn-clear:hover { border-color:var(--border-hi);color:var(--text) }

/* ── Table ───────────────────────────────────────── */
.bx-table-wrap { background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden }
.bx-table { width:100%;border-collapse:collapse;font-size:13px }
.bx-table thead tr { background:var(--bg);border-bottom:1px solid var(--border) }
.bx-table thead th {
    padding:10px 14px;text-align:left;
    font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;
    color:var(--text-dim);white-space:nowrap
}
.bx-table thead th.sortable { cursor:pointer }
.bx-table thead th.sortable:hover { color:var(--text) }
.bx-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.bx-table tbody tr:last-child { border-bottom:none }
.bx-table tbody tr:hover { background:var(--surface2) }
.bx-table td { padding:11px 14px;vertical-align:middle }

/* Badges */
.bx-chip { display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap }
.bx-badge-sm { display:inline-flex;align-items:center;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;white-space:nowrap }

/* View button */
.bx-view-btn {
    padding:5px 12px;border-radius:7px;border:1.5px solid var(--border);
    background:transparent;font-size:12px;font-weight:600;cursor:pointer;
    font-family:var(--font);color:var(--text-sub);transition:all var(--tr)
}
.bx-view-btn:hover { border-color:var(--accent);color:var(--accent) }

/* Box code chip */
.bx-code-chip {
    display:inline-block;font-family:var(--mono);font-size:12px;font-weight:700;
    padding:3px 9px;border-radius:7px;cursor:pointer;transition:opacity var(--tr)
}
.bx-code-chip:hover { opacity:.75 }

/* Empty state */
.bx-empty { padding:60px 20px;text-align:center }
.bx-empty-icon  { font-size:36px;margin-bottom:10px }
.bx-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.bx-empty-sub   { font-size:13px;color:var(--text-dim);margin-bottom:16px }
.bx-empty-btn   { padding:8px 20px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-weight:600;color:var(--text-sub);cursor:pointer;background:transparent;font-family:var(--font) }

/* Mobile */
@media(max-width:1100px) { .bx-hide-lg { display:none !important } }
@media(max-width:800px)  { .bx-hide-md { display:none !important } }
@media(max-width:640px) {
    .bx-bar { flex-direction:column;align-items:stretch }
    .bx-select,.bx-toggle-wrap,.bx-btn-clear { width:100% }
    .bx-table td,.bx-table th { padding:9px 10px }
}
</style>

{{-- ── Page header ─────────────────────────────────────────────── --}}
<div class="dashboard-page-header" style="margin-bottom:20px">
    <div>
        <h1 style="font-size:26px;font-weight:800;color:var(--text);letter-spacing:-.4px;margin:0 0 4px">Boxes</h1>
        <p style="font-size:14px;color:var(--text-dim);margin:0">Physical inventory — lifecycle, location, and value of every box</p>
    </div>
</div>

{{-- ── KPI strip ───────────────────────────────────────────────── --}}
@php $sellable = ($stats->full_count ?? 0) + ($stats->partial_count ?? 0); @endphp
<div class="bx-kpis">

    {{-- Card 1: Sellable Boxes --}}
    <div class="bkpi blue">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="bkpi-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </div>
                <span class="bkpi-name">Sellable Boxes</span>
            </div>
            <span class="bkpi-pct blue">of {{ number_format($stats->total ?? 0) }}</span>
        </div>
        <div class="bkpi-value" style="color:var(--accent)">{{ number_format($sellable) }}</div>
        <div class="bkpi-meta">Full &amp; partial in stock &middot; boxes</div>
        <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ number_format($stats->full_count ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Full</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--amber);font-family:var(--mono)">{{ number_format($stats->partial_count ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Partial</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ number_format($stats->empty_count ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Empty</div>
            </div>
        </div>
    </div>

    {{-- Card 2: Fill Rate --}}
    @php
        $fillRateColor    = $fillRate === null ? 'var(--text-dim)'
            : ($fillRate >= 70 ? 'var(--green)' : ($fillRate >= 40 ? 'var(--amber)' : 'var(--red)'));
        $fillRatePctClass = $fillRate !== null && $fillRate >= 70 ? 'green'
            : ($fillRate !== null && $fillRate >= 40 ? 'amber' : 'down');
    @endphp
    <div class="bkpi green">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="bkpi-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                </div>
                <span class="bkpi-name">Fill Rate</span>
            </div>
            <span class="bkpi-pct {{ $fillRatePctClass }}">{{ number_format($stats->total_items ?? 0) }} items</span>
        </div>
        <div class="bkpi-value" style="color:{{ $fillRateColor }}">
            @if($fillRate !== null){{ $fillRate }}%@else —@endif
        </div>
        <div class="bkpi-meta">Capacity utilisation &middot; sellable only</div>
        <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ number_format($stats->total_items ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Items</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-sub);font-family:var(--mono)">{{ number_format($stats->total_capacity ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Capacity</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--accent);font-family:var(--mono)">{{ number_format($sellable) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Sellable</div>
            </div>
        </div>
    </div>

    {{-- Card 3: Stagnant Boxes --}}
    @php
        $stagnantValColor  = $stagnantCount > 0 ? 'var(--amber)' : 'var(--green)';
        $stagnantPctClass  = $stagnantCount > 0 ? 'amber' : 'green';
        $stagnantRate      = ($stats->total ?? 0) > 0
            ? round($stagnantCount / $stats->total * 100) : 0;
    @endphp
    <div class="bkpi violet">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="bkpi-icon violet">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <span class="bkpi-name">Stagnant</span>
            </div>
            <span class="bkpi-pct {{ $stagnantPctClass }}">{{ $stagnantRate }}%</span>
        </div>
        <div class="bkpi-value" style="color:{{ $stagnantValColor }}">{{ $stagnantCount }}</div>
        <div class="bkpi-meta">No movement in 30+ days</div>
        <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:{{ $stagnantCount > 0 ? 'var(--amber)' : 'var(--green)' }};font-family:var(--mono)">{{ $stagnantCount }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Stagnant</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-sub);font-family:var(--mono)">{{ number_format(max(0, ($stats->total ?? 0) - $stagnantCount)) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Active</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">30d</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Threshold</div>
            </div>
        </div>
    </div>

    {{-- Card 5: Damaged (clickable → filter) --}}
    @php
        $damagedValColor  = ($stats->damaged_count ?? 0) > 0 ? 'var(--red)' : 'var(--green)';
        $damagedPctClass  = ($stats->damaged_count ?? 0) > 0 ? 'down' : 'green';
        $damagedRate      = ($stats->total ?? 0) > 0
            ? round(($stats->damaged_count ?? 0) / $stats->total * 100) : 0;
    @endphp
    <div class="bkpi red" style="cursor:pointer" wire:click="$set('status', 'damaged')">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="bkpi-icon red">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
                <span class="bkpi-name">Damaged</span>
            </div>
            <span class="bkpi-pct {{ $damagedPctClass }}">{{ $damagedRate }}%</span>
        </div>
        <div class="bkpi-value" style="color:{{ $damagedValColor }}">{{ number_format($stats->damaged_count ?? 0) }}</div>
        <div class="bkpi-meta">Click to filter &middot; awaiting disposition</div>
        <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:{{ ($stats->damaged_count ?? 0) > 0 ? 'var(--red)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ number_format($stats->damaged_count ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Damaged</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--green);font-family:var(--mono)">{{ number_format(max(0, ($stats->total ?? 0) - ($stats->damaged_count ?? 0))) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Intact</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">{{ $damagedRate }}%</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Rate</div>
            </div>
        </div>
    </div>

    {{-- Card 6: Expiring Soon (clickable → filter) --}}
    @php
        $expValColor  = ($stats->expiring_soon ?? 0) > 0 ? 'var(--amber)' : 'var(--green)';
        $expPctClass  = ($stats->expiring_soon ?? 0) > 0 ? 'amber' : 'green';
    @endphp
    <div class="bkpi amber" style="cursor:pointer" wire:click="$set('expiringOnly', true)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:8px">
                <div class="bkpi-icon amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <span class="bkpi-name">Expiring Soon</span>
            </div>
            <span class="bkpi-pct {{ $expPctClass }}">&le;30 days</span>
        </div>
        <div class="bkpi-value" style="color:{{ $expValColor }}">{{ number_format($stats->expiring_soon ?? 0) }}</div>
        <div class="bkpi-meta">Click to filter &middot; within 30 days</div>
        <div style="display:flex;gap:16px;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:{{ ($stats->expiring_soon ?? 0) > 0 ? 'var(--amber)' : 'var(--text-dim)' }};font-family:var(--mono)">{{ number_format($stats->expiring_soon ?? 0) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">&le;30 days</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-sub);font-family:var(--mono)">{{ number_format(max(0, ($stats->total ?? 0) - ($stats->expiring_soon ?? 0))) }}</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Safe</div>
            </div>
            <div style="text-align:center;flex:1">
                <div style="font-size:11px;font-weight:700;color:var(--text-dim);font-family:var(--mono)">30d</div>
                <div style="font-size:10px;color:var(--text-dim);margin-top:1px">Window</div>
            </div>
        </div>
    </div>

</div>

{{-- ── Filter bar ─────────────────────────────────────────────── --}}
<div class="bx-bar">

    {{-- Search --}}
    <div class="bx-search-wrap">
        <svg class="bx-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input wire:model.live.debounce.300ms="search"
               class="bx-search" type="text"
               placeholder="Search by box code…">
    </div>

    {{-- Location Type --}}
    <select wire:model.live="locationType" class="bx-select">
        <option value="">All Locations</option>
        <option value="warehouse">Warehouse</option>
        <option value="shop">Shop</option>
    </select>

    {{-- Location (cascades) --}}
    @if($locationType === 'warehouse')
    <select wire:model.live="locationId" class="bx-select">
        <option value="">All Warehouses</option>
        @foreach($warehouses as $wh)
            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
        @endforeach
    </select>
    @elseif($locationType === 'shop')
    <select wire:model.live="locationId" class="bx-select">
        <option value="">All Shops</option>
        @foreach($shops as $shop)
            <option value="{{ $shop->id }}">{{ $shop->name }}</option>
        @endforeach
    </select>
    @endif

    {{-- Product --}}
    <select wire:model.live="productId" class="bx-select">
        <option value="">All Products</option>
        @foreach($products as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
        @endforeach
    </select>

    {{-- Status --}}
    <select wire:model.live="status" class="bx-select">
        <option value="">All Statuses</option>
        @foreach($statuses as $s)
            <option value="{{ $s->value }}">{{ ucfirst($s->value) }}</option>
        @endforeach
    </select>

    {{-- Sort By --}}
    <select wire:model.live="sortBy" class="bx-select">
        <option value="received_at">Date received</option>
        <option value="items_remaining">Items remaining</option>
        <option value="cost_value">Cost value</option>
        <option value="expiry_date">Expiry date</option>
        <option value="status">Status</option>
    </select>

    {{-- Sort Direction --}}
    <select wire:model.live="sortDirection" class="bx-select">
        <option value="desc">Newest first</option>
        <option value="asc">Oldest first</option>
    </select>

    {{-- Expiring toggle --}}
    <label class="bx-toggle-wrap {{ $expiringOnly ? 'active' : '' }}" style="cursor:pointer">
        <input type="checkbox" wire:model.live="expiringOnly" style="display:none">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        Expiring
    </label>

    {{-- Clear --}}
    @if($search || $locationType || $productId || $status || $expiringOnly)
    <button wire:click="clearFilters" class="bx-btn-clear">Clear</button>
    @endif

</div>

{{-- ── Table ──────────────────────────────────────────────────── --}}
<div class="bx-table-wrap">
    <div style="overflow-x:auto">
    <table class="bx-table">
        <thead>
            <tr>
                <th>Box Code</th>
                <th>Product</th>
                <th>Location</th>

                {{-- Status — sortable --}}
                <th wire:click="sortColumn('status')" class="sortable">
                    Status
                    @if($sortBy === 'status')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                {{-- Fill — sortable --}}
                <th wire:click="sortColumn('items_remaining')" class="sortable">
                    Fill
                    @if($sortBy === 'items_remaining')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                {{-- Age — sortable --}}
                <th wire:click="sortColumn('received_at')" class="sortable bx-hide-md">
                    Age
                    @if($sortBy === 'received_at')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                {{-- Expiry — sortable --}}
                <th wire:click="sortColumn('expiry_date')" class="sortable bx-hide-md">
                    Expiry
                    @if($sortBy === 'expiry_date')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>

                {{-- Cost Value — owner only, sortable --}}
                @if($isOwner)
                <th wire:click="sortColumn('cost_value')" class="sortable bx-hide-lg" style="text-align:right">
                    Cost Value
                    @if($sortBy === 'cost_value')
                        <span style="color:var(--accent)">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>
                @endif

                <th class="bx-hide-lg">Batch</th>
                <th style="text-align:right">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($boxes as $box)
            @php
                $statusColors = match($box->status->value) {
                    'full'    => ['bg'=>'var(--green-dim,rgba(22,163,74,.12))',  'color'=>'var(--green)'],
                    'partial' => ['bg'=>'var(--amber-dim,rgba(217,119,6,.12))', 'color'=>'var(--amber)'],
                    'empty'   => ['bg'=>'var(--surface2)',                       'color'=>'var(--text-dim)'],
                    'damaged' => ['bg'=>'var(--red-dim,rgba(220,38,38,.12))',    'color'=>'var(--red)'],
                    default   => ['bg'=>'var(--surface2)',                       'color'=>'var(--text-dim)'],
                };

                // Fill bar
                $fillPct   = $box->items_total > 0
                    ? round(($box->items_remaining / $box->items_total) * 100)
                    : 0;
                $fillColor = $fillPct >= 60
                    ? 'var(--success,var(--green))'
                    : ($fillPct >= 20 ? 'var(--warn,var(--amber))' : 'var(--danger,var(--red))');

                // Age
                $ageDays   = $box->received_at ? (int) $box->received_at->diffInDays(now()) : null;
                $ageColor  = $ageDays === null ? 'var(--text-dim)'
                           : ($ageDays <= 30   ? 'var(--success,var(--green))'
                           : ($ageDays <= 90   ? 'var(--warn,var(--amber))'
                           :                     'var(--danger,var(--red))'));

                // Location icon
                $locIcon = $box->location_type?->value === 'warehouse'
                    ? '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'
                    : '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>';

                // Cost value (owner)
                $costVal = $isOwner && $box->product
                    ? $box->items_remaining * $box->product->purchase_price
                    : 0;
            @endphp
            <tr>
                {{-- Box Code --}}
                <td>
                    <span class="bx-code-chip"
                          style="background:{{ $statusColors['bg'] }};color:{{ $statusColors['color'] }}"
                          wire:click="$dispatch('open-box-detail', {boxId: {{ $box->id }}})">
                        {{ $box->box_code }}
                    </span>
                </td>

                {{-- Product --}}
                <td>
                    <div style="font-weight:600;color:var(--text);font-size:13px">
                        {{ $box->product?->name ?? '—' }}
                    </div>
                    @if($box->product?->category)
                    <span style="font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;
                                 background:var(--accent-dim,rgba(59,111,212,.1));color:var(--accent);
                                 margin-top:2px;display:inline-block">
                        {{ $box->product->category->name }}
                    </span>
                    @endif
                </td>

                {{-- Location --}}
                <td>
                    <div style="display:flex;align-items:center;gap:5px;color:var(--text-sub);font-size:13px">
                        {!! $locIcon !!}
                        <span style="font-weight:600">{{ $box->location?->name ?? '—' }}</span>
                    </div>
                    @if($box->location_type)
                    <div style="font-size:10px;color:var(--text-dim);margin-top:1px;text-transform:capitalize">
                        {{ $box->location_type->value }}
                    </div>
                    @endif
                </td>

                {{-- Status --}}
                <td>
                    <span class="bx-chip"
                          style="background:{{ $statusColors['bg'] }};color:{{ $statusColors['color'] }}">
                        {{ ucfirst($box->status->value) }}
                    </span>
                </td>

                {{-- Fill --}}
                <td style="min-width:110px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="flex:1;height:5px;background:var(--surface3);border-radius:3px;min-width:50px">
                            <div style="height:100%;width:{{ $fillPct }}%;background:{{ $fillColor }};border-radius:3px"></div>
                        </div>
                        <span style="font-size:12px;color:var(--text-sub);white-space:nowrap">
                            {{ $box->items_remaining }}/{{ $box->items_total }}
                        </span>
                    </div>
                </td>

                {{-- Age --}}
                <td class="bx-hide-md">
                    <span style="font-size:13px;font-weight:600;color:{{ $ageColor }}">
                        {{ $ageDays !== null ? $ageDays . 'd' : '—' }}
                    </span>
                </td>

                {{-- Expiry --}}
                <td class="bx-hide-md">
                    @if($box->expiry_date)
                        @php
                            $daysToExpiry = (int) now()->diffInDays($box->expiry_date, false);
                            $expColor = $daysToExpiry <= 7  ? 'var(--danger,var(--red))'
                                      : ($daysToExpiry <= 30 ? 'var(--warn,var(--amber))'
                                      :                        'var(--success,var(--green))');
                        @endphp
                        <span style="font-size:12px;font-weight:600;color:{{ $expColor }};
                                     background:{{ $expColor }};
                                     -webkit-background-clip:text;padding:2px 7px;border-radius:12px;
                                     background:transparent;white-space:nowrap">
                            {{ $box->expiry_date->format('d M Y') }}
                        </span>
                    @else
                        <span style="color:var(--text-dim);font-size:13px">—</span>
                    @endif
                </td>

                {{-- Cost Value --}}
                @if($isOwner)
                <td class="bx-hide-lg" style="text-align:right">
                    @if($costVal > 0)
                    <span style="font-family:var(--mono);font-size:12px;font-weight:600;color:var(--text)">
                        {{ number_format($costVal) }}
                        <span style="font-size:10px;color:var(--text-dim)">RWF</span>
                    </span>
                    @else
                    <span style="color:var(--text-dim);font-size:12px">—</span>
                    @endif
                </td>
                @endif

                {{-- Batch --}}
                <td class="bx-hide-lg" style="font-family:var(--mono);font-size:12px;color:var(--text-dim)">
                    {{ $box->batch_number ?? '—' }}
                </td>

                {{-- Action --}}
                <td style="text-align:right">
                    <button class="bx-view-btn"
                            wire:click="$dispatch('open-box-detail', {boxId: {{ $box->id }}})">
                        View
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $isOwner ? 10 : 9 }}">
                    <div class="bx-empty">
                        <div class="bx-empty-icon">📦</div>
                        <div class="bx-empty-title">
                            @if($search || $locationType || $productId || $status || $expiringOnly)
                                No boxes match your filters
                            @else
                                No boxes in the system yet
                            @endif
                        </div>
                        <div class="bx-empty-sub">
                            @if($search || $locationType || $productId || $status || $expiringOnly)
                                Try adjusting or clearing your filters
                            @else
                                Boxes will appear here once they are received into a warehouse or shop
                            @endif
                        </div>
                        @if($search || $locationType || $productId || $status || $expiringOnly)
                        <button wire:click="clearFilters" class="bx-empty-btn">Clear Filters</button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    {{-- Pagination --}}
    @if($boxes->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--border)">
        {{ $boxes->links() }}
    </div>
    @endif
</div>

{{-- ── Box detail drawer ──────────────────────────────────────── --}}
<livewire:inventory.boxes.box-detail />

</div>
