<div>

@if($box)
<style>
/* ── Box detail drawer ─────────────────────────── */
.bd-overlay {
    position:fixed;inset:0;z-index:999;
    display:flex;justify-content:flex-end;overflow:hidden;
}
.bd-scrim {
    position:absolute;inset:0;
    background:rgba(15,23,42,.4);
    backdrop-filter:blur(2px);
}
.bd-drawer {
    position:relative;width:640px;max-width:92vw;height:100vh;
    background:var(--surface);display:flex;flex-direction:column;
    box-shadow:-4px 0 24px rgba(0,0,0,.12);
    transform:translateX(100%);
    transition:transform .25s cubic-bezier(.2,.8,.2,1);
}
.bd-drawer.open { transform:translateX(0) }

/* Header */
.bd-header {
    padding:20px 28px 16px;border-bottom:1px solid var(--border);
    display:flex;justify-content:space-between;align-items:flex-start;
    background:var(--surface);z-index:10;flex-shrink:0;
}
.bd-code {
    font-family:var(--mono);font-size:22px;font-weight:800;
    letter-spacing:-.5px;color:var(--text);margin-bottom:6px;
}
.bd-status-chip {
    display:inline-flex;align-items:center;padding:3px 10px;
    border-radius:20px;font-size:11px;font-weight:700;margin-right:6px;
}
.bd-product-name { font-size:13px;color:var(--text-sub);margin-top:4px }
.bd-close-btn {
    width:32px;height:32px;border:1px solid var(--border);background:var(--surface);
    border-radius:6px;display:grid;place-items:center;color:var(--text-sub);
    cursor:pointer;transition:all .15s;flex-shrink:0;
}
.bd-close-btn:hover { background:var(--surface2);color:var(--text) }

/* Body */
.bd-body { flex:1;overflow-y:auto;padding:22px 28px;display:flex;flex-direction:column;gap:24px }

/* Section */
.bd-section-label {
    font-size:11px;font-weight:700;text-transform:uppercase;
    letter-spacing:.8px;color:var(--text-sub);margin-bottom:12px;
}

/* Identity grid */
.bd-grid {
    display:grid;grid-template-columns:1fr 1fr;gap:0;
    border:1px solid var(--border);border-radius:var(--r);overflow:hidden;
}
.bd-field {
    display:grid;grid-template-columns:110px 1fr;gap:8px;align-items:start;
    padding:10px 14px;border-bottom:1px solid var(--border);
}
.bd-field:nth-child(odd):last-child { grid-column:1/-1 }
.bd-field-label { font-size:11px;font-weight:600;color:var(--text-dim);padding-top:1px }
.bd-field-val   { font-size:13px;color:var(--text);font-weight:500 }

/* Fill bar */
.bd-fill-card {
    background:var(--surface2);border:1px solid var(--border);
    border-radius:var(--r);padding:16px 20px;
}
.bd-fill-nums {
    display:flex;justify-content:space-between;align-items:baseline;
    margin-bottom:10px;
}
.bd-fill-big  { font-family:var(--mono);font-size:28px;font-weight:800;letter-spacing:-1px }
.bd-fill-total { font-family:var(--mono);font-size:15px;color:var(--text-sub) }
.bd-fill-pct  { font-size:13px;font-weight:700 }
.bd-fill-track { height:10px;background:var(--surface3);border-radius:5px;overflow:hidden }
.bd-fill-bar   { height:100%;border-radius:5px;transition:width .4s }

/* Financial card */
.bd-fin-card {
    background:var(--surface2);border:1px solid var(--border);
    border-radius:var(--r);overflow:hidden;
}
.bd-fin-row {
    display:flex;justify-content:space-between;align-items:center;
    padding:11px 16px;border-bottom:1px solid var(--border);
}
.bd-fin-row:last-child { border-bottom:none }
.bd-fin-label { font-size:12px;font-weight:600;color:var(--text-sub) }
.bd-fin-val   { font-family:var(--mono);font-size:14px;font-weight:700;color:var(--text) }

/* Timeline */
.bd-timeline { display:flex;flex-direction:column;gap:0 }
.bd-timeline-item {
    display:flex;gap:14px;padding:12px 0;
    border-bottom:1px solid var(--border);
}
.bd-timeline-item:last-child { border-bottom:none }
.bd-tl-left { flex-shrink:0;width:90px;text-align:right }
.bd-tl-rel  { font-size:11px;color:var(--text-dim);line-height:1.4 }
.bd-tl-abs  { font-size:10px;color:var(--text-dim);margin-top:1px }
.bd-tl-type {
    flex-shrink:0;font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:.5px;padding:3px 8px;border-radius:20px;height:fit-content;
    white-space:nowrap;align-self:flex-start;margin-top:1px;
}
.bd-tl-body { flex:1;min-width:0 }
.bd-tl-route { font-size:12px;color:var(--text-sub) }
.bd-tl-meta { font-size:11px;color:var(--text-dim);margin-top:2px }

/* Transfer list */
.bd-transfer-item {
    padding:12px 0;border-bottom:1px solid var(--border);
}
.bd-transfer-item:last-child { border-bottom:none }
.bd-tf-num   { font-family:var(--mono);font-size:13px;font-weight:700;color:var(--text) }
.bd-tf-route { font-size:12px;color:var(--text-sub);margin-top:2px }
.bd-tf-meta  { display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:5px }

.bd-empty {
    padding:28px;text-align:center;color:var(--text-sub);font-size:13px;
    background:var(--surface2);border:1px dashed var(--border);border-radius:var(--r);
}

@media(max-width:600px) {
    .bd-grid { grid-template-columns:1fr }
    .bd-body { padding:16px 18px }
    .bd-header { padding:16px 18px 14px }
}
</style>

@php
    $statusColor = match($box->status->value) {
        'full'    => ['bg'=>'var(--green-dim,rgba(22,163,74,.12))',  'color'=>'var(--green)'],
        'partial' => ['bg'=>'var(--amber-dim,rgba(217,119,6,.12))', 'color'=>'var(--amber)'],
        'empty'   => ['bg'=>'var(--surface2)',                       'color'=>'var(--text-dim)'],
        'damaged' => ['bg'=>'var(--red-dim,rgba(220,38,38,.12))',    'color'=>'var(--red)'],
        default   => ['bg'=>'var(--surface2)',                       'color'=>'var(--text-dim)'],
    };
    $fillPct   = $box->items_total > 0
        ? round(($box->items_remaining / $box->items_total) * 100)
        : 0;
    $fillColor = $fillPct >= 60
        ? 'var(--success,var(--green))'
        : ($fillPct >= 20 ? 'var(--warn,var(--amber))' : 'var(--danger,var(--red))');
    $ageColor = $ageDays === null ? 'var(--text-dim)'
              : ($ageDays <= 30  ? 'var(--success,var(--green))'
              : ($ageDays <= 90  ? 'var(--warn,var(--amber))'
              :                    'var(--danger,var(--red))'));
    $expiryStyle = '';
    $expiryText  = '—';
    if ($box->expiry_date) {
        $daysLeft   = (int) now()->diffInDays($box->expiry_date, false);
        $expiryText = $box->expiry_date->format('d M Y');
        $expiryStyle = $daysLeft <= 30  ? 'color:var(--danger,var(--red));font-weight:700'
                     : ($daysLeft <= 90 ? 'color:var(--warn,var(--amber));font-weight:700'
                     :                    'color:var(--success,var(--green))');
    }
@endphp

<div class="bd-overlay" x-data="boxDetailDrawer()" x-init="initDrawer()">
    {{-- Backdrop --}}
    <div class="bd-scrim" wire:click="close" x-show="open" x-transition.opacity></div>

    {{-- Drawer panel --}}
    <div class="bd-drawer" x-ref="drawer" :class="{ 'open': open }">

        {{-- Header --}}
        <div class="bd-header">
            <div style="min-width:0;flex:1;padding-right:14px">
                <div class="bd-code">{{ $box->box_code }}</div>
                <div>
                    <span class="bd-status-chip"
                          style="background:{{ $statusColor['bg'] }};color:{{ $statusColor['color'] }}">
                        {{ ucfirst($box->status->value) }}
                    </span>
                </div>
                @if($box->product)
                <div class="bd-product-name">{{ $box->product->name }}</div>
                @endif
            </div>
            <button wire:click="close" class="bd-close-btn" aria-label="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- Scrollable body --}}
        <div class="bd-body" id="bd-body">

            {{-- Identity grid --}}
            <div>
                <div class="bd-section-label">Box Details</div>
                <div class="bd-grid">
                    <div class="bd-field">
                        <div class="bd-field-label">Product</div>
                        <div class="bd-field-val">{{ $box->product?->name ?? '—' }}</div>
                    </div>
                    <div class="bd-field">
                        <div class="bd-field-label">Category</div>
                        <div class="bd-field-val">{{ $box->product?->category?->name ?? '—' }}</div>
                    </div>
                    <div class="bd-field">
                        <div class="bd-field-label">SKU</div>
                        <div class="bd-field-val" style="font-family:var(--mono);font-size:12px">
                            {{ $box->product?->sku ?? '—' }}
                        </div>
                    </div>
                    <div class="bd-field">
                        <div class="bd-field-label">Location</div>
                        <div class="bd-field-val">
                            {{ $box->location?->name ?? '—' }}
                            @if($box->location_type)
                            <span style="font-size:10px;color:var(--text-dim);text-transform:capitalize">
                                ({{ $box->location_type->value }})
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="bd-field">
                        <div class="bd-field-label">Received by</div>
                        <div class="bd-field-val">{{ $box->receivedBy?->name ?? '—' }}</div>
                    </div>
                    <div class="bd-field">
                        <div class="bd-field-label">Received at</div>
                        <div class="bd-field-val" style="font-size:12px">
                            {{ $box->received_at?->format('d M Y, H:i') ?? '—' }}
                        </div>
                    </div>
                    <div class="bd-field">
                        <div class="bd-field-label">Batch #</div>
                        <div class="bd-field-val" style="font-family:var(--mono);font-size:12px">
                            {{ $box->batch_number ?? '—' }}
                        </div>
                    </div>
                    <div class="bd-field">
                        <div class="bd-field-label">Expiry date</div>
                        <div class="bd-field-val" style="{{ $expiryStyle }}">{{ $expiryText }}</div>
                    </div>
                    <div class="bd-field" style="grid-column:1/-1">
                        <div class="bd-field-label">Age</div>
                        <div class="bd-field-val" style="color:{{ $ageColor }};font-weight:700">
                            {{ $ageDays !== null ? $ageDays . ' days' : '—' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fill bar --}}
            <div>
                <div class="bd-section-label">Stock Fill</div>
                <div class="bd-fill-card">
                    <div class="bd-fill-nums">
                        <div>
                            <span class="bd-fill-big" style="color:{{ $fillColor }}">
                                {{ number_format($box->items_remaining) }}
                            </span>
                            <span class="bd-fill-total"> / {{ number_format($box->items_total) }} items</span>
                        </div>
                        <span class="bd-fill-pct" style="color:{{ $fillColor }}">{{ $fillPct }}%</span>
                    </div>
                    <div class="bd-fill-track">
                        <div class="bd-fill-bar"
                             style="width:{{ $fillPct }}%;background:{{ $fillColor }}"></div>
                    </div>
                </div>
            </div>

            {{-- Financial block (owner only) --}}
            @if($isOwner)
            <div>
                <div class="bd-section-label">Financial</div>
                <div class="bd-fin-card">
                    <div class="bd-fin-row">
                        <span class="bd-fin-label">Cost value</span>
                        <span class="bd-fin-val">
                            {{ number_format($costValue) }}
                            <span style="font-size:11px;font-weight:400;color:var(--text-dim)">RWF</span>
                        </span>
                    </div>
                    <div class="bd-fin-row">
                        <span class="bd-fin-label">Retail value</span>
                        <span class="bd-fin-val" style="color:var(--success,var(--green))">
                            {{ number_format($retailValue) }}
                            <span style="font-size:11px;font-weight:400;color:var(--text-dim)">RWF</span>
                        </span>
                    </div>
                    <div class="bd-fin-row">
                        <span class="bd-fin-label">Gross upside</span>
                        <span class="bd-fin-val" style="color:var(--accent)">
                            {{ number_format($retailValue - $costValue) }}
                            <span style="font-size:11px;font-weight:400;color:var(--text-dim)">RWF</span>
                        </span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Movement timeline --}}
            <div>
                <div class="bd-section-label">Movement History</div>
                @if(count($movements) > 0)
                <div style="border:1px solid var(--border);border-radius:var(--r);overflow:hidden;background:var(--surface)">
                    <div class="bd-timeline" style="padding:0 16px">
                        @foreach($movements as $m)
                        @php
                            $mvColors = match($m['type']) {
                                'receive'     => ['bg'=>'var(--green-dim,rgba(22,163,74,.12))',  'color'=>'var(--green)'],
                                'transfer'    => ['bg'=>'var(--accent-dim,rgba(59,111,212,.12))','color'=>'var(--accent)'],
                                'consumption' => ['bg'=>'var(--amber-dim,rgba(217,119,6,.12))', 'color'=>'var(--amber)'],
                                'damage'      => ['bg'=>'var(--red-dim,rgba(220,38,38,.12))',    'color'=>'var(--red)'],
                                default       => ['bg'=>'var(--surface2)',                       'color'=>'var(--text-dim)'],
                            };
                        @endphp
                        <div class="bd-timeline-item">
                            <div class="bd-tl-left">
                                <div class="bd-tl-rel">{{ $m['relative'] }}</div>
                                <div class="bd-tl-abs">{{ $m['date'] }}</div>
                            </div>
                            <span class="bd-tl-type"
                                  style="background:{{ $mvColors['bg'] }};color:{{ $mvColors['color'] }}">
                                {{ ucfirst(str_replace('_', ' ', $m['type'])) }}
                            </span>
                            <div class="bd-tl-body">
                                <div class="bd-tl-route">
                                    {{ $m['from'] }} &rarr; {{ $m['to'] }}
                                    @if($m['items'] > 0)
                                    <span style="font-family:var(--mono);font-size:11px;
                                                 color:var(--text-dim);margin-left:6px">
                                        {{ $m['items'] }} items
                                    </span>
                                    @endif
                                </div>
                                <div class="bd-tl-meta">
                                    {{ $m['moved_by'] }}
                                    @if($m['reason'] !== '—')
                                     &middot; {{ $m['reason'] }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="bd-empty">No movement records found.</div>
                @endif
            </div>

            {{-- Transfer history --}}
            <div>
                <div class="bd-section-label">Transfer History</div>
                @if(count($transfers) > 0)
                <div style="border:1px solid var(--border);border-radius:var(--r);overflow:hidden;background:var(--surface)">
                    <div style="padding:0 16px">
                        @foreach($transfers as $tf)
                        @php
                            $tfStatusColors = [
                                'pending'    => ['bg'=>'var(--surface2)',                        'color'=>'var(--text-dim)'],
                                'approved'   => ['bg'=>'var(--accent-dim,rgba(59,111,212,.12))','color'=>'var(--accent)'],
                                'in_transit' => ['bg'=>'var(--amber-dim,rgba(217,119,6,.12))',  'color'=>'var(--amber)'],
                                'delivered'  => ['bg'=>'var(--accent-dim,rgba(59,111,212,.12))','color'=>'var(--accent)'],
                                'received'   => ['bg'=>'var(--green-dim,rgba(22,163,74,.12))',   'color'=>'var(--green)'],
                                'cancelled'  => ['bg'=>'var(--surface2)',                        'color'=>'var(--text-dim)'],
                                'rejected'   => ['bg'=>'var(--red-dim,rgba(220,38,38,.12))',     'color'=>'var(--red)'],
                            ];
                            $tfColor = $tfStatusColors[$tf->status] ?? ['bg'=>'var(--surface2)','color'=>'var(--text-dim)'];
                        @endphp
                        <div class="bd-transfer-item">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
                                <div class="bd-tf-num">{{ $tf->transfer_number ?? 'TRF-' . $tf->id }}</div>
                                <div style="display:flex;gap:5px;flex-shrink:0">
                                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:12px;
                                                 background:{{ $tfColor['bg'] }};color:{{ $tfColor['color'] }};
                                                 text-transform:uppercase;white-space:nowrap">
                                        {{ str_replace('_', ' ', $tf->status) }}
                                    </span>
                                    @if($tf->has_discrepancy)
                                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:12px;
                                                 background:var(--red-dim,rgba(220,38,38,.12));color:var(--red);
                                                 white-space:nowrap">
                                        ! Discrepancy
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="bd-tf-route">
                                {{ $tf->warehouse_name ?? '—' }}
                                &rarr;
                                {{ $tf->shop_name ?? '—' }}
                            </div>
                            <div class="bd-tf-meta">
                                @if($tf->scanned_out_at)
                                <span style="font-size:11px;color:var(--text-dim)">
                                    Out: {{ \Carbon\Carbon::parse($tf->scanned_out_at)->format('d M Y') }}
                                </span>
                                @endif
                                @if($tf->scanned_in_at)
                                <span style="font-size:11px;color:var(--text-dim)">
                                    &middot; In: {{ \Carbon\Carbon::parse($tf->scanned_in_at)->format('d M Y') }}
                                </span>
                                @endif
                                @if($tf->is_received)
                                <span style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;
                                             background:var(--green-dim,rgba(22,163,74,.12));color:var(--green)">
                                    Received
                                </span>
                                @endif
                                @if($tf->is_damaged)
                                <span style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;
                                             background:var(--red-dim,rgba(220,38,38,.12));color:var(--red)">
                                    Damaged in transit
                                </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="bd-empty">This box has not been part of any transfer.</div>
                @endif
            </div>

            {{-- Damage notes --}}
            @if($box->damage_notes)
            <div style="background:var(--danger-glow,rgba(220,38,38,.08));
                        border:1px solid var(--danger,var(--red));
                        border-radius:var(--rsm,6px);padding:12px 16px">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;
                            letter-spacing:.7px;color:var(--danger,var(--red));margin-bottom:6px">
                    Damage Notes
                </div>
                <p style="font-size:13px;color:var(--text);margin:0">{{ $box->damage_notes }}</p>
            </div>
            @endif

        </div>{{-- /bd-body --}}
    </div>{{-- /bd-drawer --}}
</div>{{-- /bd-overlay --}}

@script
<script>
    Alpine.data('boxDetailDrawer', () => ({
        open: false,

        initDrawer() {
            const drawer = this.$refs.drawer;
            if (drawer) drawer.scrollTop = 0;

            requestAnimationFrame(() => {
                this.open = true;
            });
        }
    }));
</script>
@endscript

@endif
</div>
