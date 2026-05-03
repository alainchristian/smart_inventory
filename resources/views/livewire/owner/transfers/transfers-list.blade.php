@php use App\Enums\TransferStatus; @endphp
<div>
<style>
/* ── Owner Transfer List ─────────────────────────────── */
.otl-wrap { display:flex; flex-direction:column; gap:16px; }

/* Header */
.otl-page-header { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.otl-page-title  { font-size:18px; font-weight:800; color:var(--text); letter-spacing:-.3px; }

/* Status filter tabs */
.otl-tabs {
    display:flex; align-items:center; gap:2px;
    background:var(--surface); border:1px solid var(--border);
    border-radius:9px; padding:3px; overflow-x:auto; scrollbar-width:none;
}
.otl-tabs::-webkit-scrollbar { display:none; }
.otl-tab {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:6px; border:none;
    font-size:12px; font-weight:500; color:var(--text-dim);
    background:transparent; cursor:pointer; white-space:nowrap; transition:all .15s;
}
.otl-tab:hover { color:var(--text); background:rgba(0,0,0,.04); }
.otl-tab.active { background:var(--accent); color:#fff; font-weight:600; }
.otl-tab-badge {
    font-size:10px; font-weight:700; padding:1px 5px;
    border-radius:8px; background:rgba(255,255,255,.25); color:inherit; line-height:1.5;
}
.otl-tab:not(.active) .otl-tab-badge { background:var(--surface2); color:var(--text-dim); }

/* Transfer cards */
.otl-list { display:flex; flex-direction:column; gap:8px; }
.otl-card {
    background:var(--surface); border:1px solid var(--border);
    border-radius:12px; overflow:hidden;
    transition:border-color .15s, box-shadow .15s;
    position:relative;
}
.otl-card:hover {
    border-color:var(--card-color, var(--accent));
    box-shadow:0 2px 12px rgba(0,0,0,.06);
}
.otl-card-stripe {
    position:absolute; top:0; left:0; bottom:0; width:3px;
    background:var(--card-color, transparent);
}

/* Card top */
.otl-card-top {
    display:flex; align-items:stretch;
    padding:12px 14px 12px 18px; gap:0;
}
.otl-card-info { flex:1; display:flex; flex-direction:column; gap:5px; justify-content:center; }

/* Number + badge */
.otl-card-meta { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
.otl-num   { font-size:14px; font-weight:700; color:var(--text); font-family:var(--mono); letter-spacing:-.1px; }
.otl-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 8px; border-radius:20px;
    font-size:10px; font-weight:700; letter-spacing:.4px; text-transform:uppercase;
    background:var(--badge-bg); color:var(--badge-color);
}
.otl-badge-dot { width:4px; height:4px; border-radius:50%; background:currentColor; }
.otl-urgent {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 7px; border-radius:20px;
    font-size:10px; font-weight:600;
    background:var(--amber-dim); color:var(--amber);
}

/* Route */
.otl-route { display:flex; align-items:center; gap:7px; font-size:12px; flex-wrap:wrap; }
.otl-route-node { display:inline-flex; align-items:center; gap:4px; font-weight:600; color:var(--text); }
.otl-route-node svg { color:var(--text-dim); flex-shrink:0; }
.otl-route-dash { display:flex; align-items:center; gap:3px; }
.otl-route-dash-line { width:28px; border-top:1.5px dashed var(--border); }

/* Dates */
.otl-dates { display:flex; gap:12px; flex-wrap:wrap; }
.otl-date  { font-size:11px; color:var(--text-dim); }
.otl-date strong { color:var(--text); font-weight:600; }

/* Stats panel */
.otl-card-stats {
    display:flex; align-items:stretch;
    border-left:1px solid var(--border); margin:10px 0;
}
.otl-stat {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:0 16px; gap:2px; border-right:1px solid var(--border);
}
.otl-stat:last-child { border-right:none; }
.otl-stat-v { font-size:18px; font-weight:800; color:var(--text); font-family:var(--mono); line-height:1.1; }
.otl-stat-l { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); }

/* Footer */
.otl-card-foot {
    display:flex; align-items:center; gap:7px;
    padding:9px 14px; border-top:1px solid var(--border);
    background:var(--surface2);
}
.otl-action {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 11px; border-radius:6px; font-size:12px; font-weight:600;
    border:1px solid var(--border); cursor:pointer; text-decoration:none;
    background:var(--surface); color:var(--text); transition:all .15s;
}
.otl-action:hover         { border-color:var(--accent); color:var(--accent); }
.otl-action.primary       { background:var(--accent); color:#fff; border-color:var(--accent); }
.otl-action.primary:hover { opacity:.88; }
.otl-foot-time { margin-left:auto; font-size:11px; color:var(--text-dim); }

/* Empty state */
.otl-empty {
    text-align:center; padding:48px 24px;
    background:var(--surface); border:1px dashed var(--border); border-radius:12px;
}
.otl-empty h3 { font-size:15px; font-weight:700; color:var(--text); margin:0 0 6px; }
.otl-empty p  { font-size:12px; color:var(--text-dim); margin:0; }

/* Flash */
.otl-flash {
    display:flex; align-items:flex-start; gap:10px;
    padding:10px 14px; border-radius:10px; font-size:12px; border:1px solid; line-height:1.5;
}
.otl-flash.ok  { background:var(--green-dim);  border-color:rgba(16,185,129,.25); color:var(--green); }
.otl-flash.err { background:var(--red-dim);    border-color:rgba(225,29,72,.25);  color:var(--red); }

/* Responsive */
@media(max-width:768px) {
    .otl-page-header  { flex-direction:column; align-items:stretch; }
    .otl-card-top     { flex-direction:column; padding:12px 14px; }
    .otl-card-stats   { border-left:none; border-top:1px solid var(--border); margin:0; flex-wrap:wrap; }
    .otl-stat         { padding:10px 14px; flex:1; min-width:80px; }
}
@media(max-width:480px) {
    .otl-route-dash-line { width:16px; }
    .otl-card-foot  { flex-wrap:wrap; gap:6px; }
    .otl-action     { flex:1; justify-content:center; }
    .otl-foot-time  { width:100%; text-align:center; margin-left:0; }
}
</style>

<div class="otl-wrap">

    {{-- Flash --}}
    @if(session()->has('error'))
    <div class="otl-flash err">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="otl-page-header">
        <span class="otl-page-title">All Transfers</span>

        {{-- Status filter tabs --}}
        <div class="otl-tabs">
            <button wire:click="$set('statusFilter','all')" class="otl-tab {{ $statusFilter==='all'?'active':'' }}">
                All
                <span class="otl-tab-badge">{{ $transfers->total() }}</span>
            </button>
            <button wire:click="$set('statusFilter','{{ TransferStatus::PENDING->value }}')"
                    class="otl-tab {{ $statusFilter===TransferStatus::PENDING->value?'active':'' }}">
                Pending
                @if($pendingCount > 0)<span class="otl-tab-badge">{{ $pendingCount }}</span>@endif
            </button>
            <button wire:click="$set('statusFilter','{{ TransferStatus::APPROVED->value }}')"
                    class="otl-tab {{ $statusFilter===TransferStatus::APPROVED->value?'active':'' }}">
                Approved
                @if($approvedCount > 0)<span class="otl-tab-badge">{{ $approvedCount }}</span>@endif
            </button>
            <button wire:click="$set('statusFilter','{{ TransferStatus::IN_TRANSIT->value }}')"
                    class="otl-tab {{ $statusFilter===TransferStatus::IN_TRANSIT->value?'active':'' }}">
                In Transit
                @if($inTransitCount > 0)<span class="otl-tab-badge">{{ $inTransitCount }}</span>@endif
            </button>
            <button wire:click="$set('statusFilter','{{ TransferStatus::DELIVERED->value }}')"
                    class="otl-tab {{ $statusFilter===TransferStatus::DELIVERED->value?'active':'' }}">
                Delivered
                @if($deliveredCount > 0)<span class="otl-tab-badge">{{ $deliveredCount }}</span>@endif
            </button>
            <button wire:click="$set('statusFilter','{{ TransferStatus::RECEIVED->value }}')"
                    class="otl-tab {{ $statusFilter===TransferStatus::RECEIVED->value?'active':'' }}">
                Received
                @if($receivedCount > 0)<span class="otl-tab-badge">{{ $receivedCount }}</span>@endif
            </button>
            <button wire:click="$set('statusFilter','{{ TransferStatus::REJECTED->value }}')"
                    class="otl-tab {{ $statusFilter===TransferStatus::REJECTED->value?'active':'' }}">
                Rejected
            </button>
            <button wire:click="$set('statusFilter','discrepancy')"
                    class="otl-tab {{ $statusFilter==='discrepancy'?'active':'' }}">
                Discrepancies
                @if($discrepancyCount > 0)<span class="otl-tab-badge">{{ $discrepancyCount }}</span>@endif
            </button>
        </div>
    </div>

    {{-- Transfer cards --}}
    <div class="otl-list">
        @forelse($transfers as $transfer)
        @php
            $sc = match($transfer->status->value) {
                'pending'    => ['color'=>'var(--amber)',  'bg'=>'var(--amber-dim)'],
                'approved'   => ['color'=>'var(--accent)', 'bg'=>'var(--accent-dim)'],
                'in_transit' => ['color'=>'#8b5cf6',       'bg'=>'rgba(139,92,246,.1)'],
                'delivered'  => ['color'=>'#0ea5e9',       'bg'=>'rgba(14,165,233,.1)'],
                'received'   => ['color'=>'var(--green)',  'bg'=>'var(--green-dim)'],
                'rejected'   => ['color'=>'var(--red)',    'bg'=>'var(--red-dim)'],
                default      => ['color'=>'var(--text-dim)','bg'=>'var(--surface2)'],
            };
            $boxesReq = $transfer->items->sum(fn($item) => (int) $item->quantity_requested);
        @endphp
        <div class="otl-card" style="--card-color:{{ $sc['color'] }}">
            <div class="otl-card-stripe"></div>

            <div class="otl-card-top">
                <div class="otl-card-info">
                    {{-- Number + status --}}
                    <div class="otl-card-meta">
                        <span class="otl-num">{{ $transfer->transfer_number }}</span>
                        <span class="otl-badge" style="--badge-bg:{{ $sc['bg'] }};--badge-color:{{ $sc['color'] }}">
                            <span class="otl-badge-dot"></span>
                            {{ $transfer->status->label() }}
                        </span>
                        @if($transfer->status === TransferStatus::PENDING)
                            <span class="otl-urgent">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v5h2v-5h-2zm0 6v2h2v-2h-2z"/></svg>
                                Awaiting Approval
                            </span>
                        @endif
                        @if($transfer->has_discrepancy)
                            <span class="otl-urgent" style="background:var(--red-dim);color:var(--red)">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v5h2v-5h-2zm0 6v2h2v-2h-2z"/></svg>
                                Discrepancy
                            </span>
                        @endif
                    </div>

                    {{-- Route --}}
                    <div class="otl-route">
                        <div class="otl-route-node">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="1"/><path d="M8 21h8M12 17v4"/></svg>
                            {{ $transfer->fromWarehouse->name ?? '—' }}
                        </div>
                        <div class="otl-route-dash">
                            <div class="otl-route-dash-line"></div>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="{{ $sc['color'] }}"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </div>
                        <div class="otl-route-node">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $transfer->toShop->name ?? '—' }}
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="otl-dates">
                        <span class="otl-date">Requested: <strong>{{ $transfer->requested_at?->format('d M Y') ?? '—' }}</strong></span>
                        @if($transfer->requestedBy)
                            <span class="otl-date">By: <strong>{{ $transfer->requestedBy->name }}</strong></span>
                        @endif
                        @if($transfer->shipped_at)
                            <span class="otl-date">Shipped: <strong>{{ $transfer->shipped_at->format('d M Y') }}</strong></span>
                        @endif
                        @if($transfer->received_at)
                            <span class="otl-date">Received: <strong>{{ $transfer->received_at->format('d M Y') }}</strong></span>
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div class="otl-card-stats">
                    <div class="otl-stat">
                        <div class="otl-stat-v">{{ $transfer->items->count() }}</div>
                        <div class="otl-stat-l">Products</div>
                    </div>
                    <div class="otl-stat">
                        <div class="otl-stat-v">{{ $boxesReq }}</div>
                        <div class="otl-stat-l">Boxes</div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="otl-card-foot">
                <a href="{{ route('owner.transfers.show', $transfer) }}" class="otl-action">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    View Details
                </a>
                @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED, TransferStatus::RECEIVED]))
                <a href="{{ route('owner.transfers.delivery-note', $transfer) }}" target="_blank" class="otl-action">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Delivery Note
                </a>
                @endif
                <span class="otl-foot-time">{{ $transfer->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="otl-empty">
            <h3>No transfers found</h3>
            <p>{{ $statusFilter === 'all' ? 'No transfer requests have been made yet.' : 'No transfers match the selected filter.' }}</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($transfers->hasPages())
        <div>{{ $transfers->links() }}</div>
    @endif

</div>
</div>
