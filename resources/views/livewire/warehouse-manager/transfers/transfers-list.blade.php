@php use App\Enums\TransferStatus; @endphp
<div>
<style>
/* ── Transfers List ─────────────────────────────────────────────── */
.wtl-wrap { display:flex; flex-direction:column; gap:16px; }

/* Page header */
.wtl-page-header {
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; flex-wrap:wrap;
}
.wtl-page-title { font-size:22px; font-weight:700; color:var(--text); margin:0; }
.wtl-page-sub   { font-size:13px; color:var(--text-dim); margin:2px 0 0; }

/* Tab pills filter */
.wtl-tabs {
    display:flex; align-items:center; gap:2px;
    background:var(--surface); border:1px solid var(--border);
    border-radius:9px; padding:3px; overflow-x:auto; scrollbar-width:none;
    flex-shrink:0;
}
.wtl-tabs::-webkit-scrollbar { display:none; }
.wtl-tab {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px; border-radius:6px; border:none;
    font-size:13px; font-weight:500; color:var(--text-dim);
    background:transparent; cursor:pointer; white-space:nowrap;
    transition:all .15s; line-height:1.4; flex-shrink:0;
}
.wtl-tab:hover { color:var(--text); background:rgba(0,0,0,.04); }
.wtl-tab.active { background:#3b6bd4; color:#fff; font-weight:600; }
.wtl-tab-badge {
    font-size:11px; font-weight:700; padding:1px 6px;
    border-radius:10px; background:rgba(255,255,255,.25); color:inherit;
    line-height:1.5;
}
.wtl-tab:not(.active) .wtl-tab-badge {
    background:var(--surface2); color:var(--text-dim);
}

/* Transfer cards */
.wtl-list { display:flex; flex-direction:column; gap:8px; }

.wtl-card {
    background:var(--surface); border:1px solid var(--border);
    border-radius:12px; overflow:hidden;
    transition:border-color .15s, box-shadow .15s;
}
.wtl-card:hover {
    border-color:var(--card-accent, #3b6bd4);
    box-shadow:0 2px 12px rgba(0,0,0,.06);
}

/* Left accent bar */
.wtl-card-inner {
    display:flex; align-items:stretch;
}
.wtl-accent-bar {
    width:3px; flex-shrink:0;
    background:var(--card-accent, var(--border));
    border-radius:12px 0 0 0;
}

.wtl-card-body { flex:1; min-width:0; }

/* Top row */
.wtl-card-top {
    display:flex; align-items:stretch; gap:0;
    padding:14px 16px 14px 14px;
}
.wtl-card-info { flex:1; min-width:0; display:flex; flex-direction:column; gap:6px; }

/* Number + badge row */
.wtl-card-meta { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
.wtl-num  { font-size:14px; font-weight:700; color:var(--text); letter-spacing:-.1px; }
.wtl-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 8px; border-radius:20px;
    font-size:11px; font-weight:600; letter-spacing:.3px; text-transform:uppercase;
    background:var(--badge-bg); color:var(--badge-color);
}
.wtl-badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; flex-shrink:0; }
.wtl-urgent {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 8px; border-radius:20px;
    font-size:11px; font-weight:600;
    background:rgba(217,119,6,.1); color:#d97706;
}

/* Route */
.wtl-route { display:flex; align-items:center; gap:7px; font-size:13px; }
.wtl-route-node {
    display:inline-flex; align-items:center; gap:4px;
    font-weight:600; color:var(--text); max-width:160px;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}
.wtl-route-node svg { color:var(--text-dim); flex-shrink:0; }
.wtl-route-arrow { color:var(--text-faint); flex-shrink:0; }

/* Dates */
.wtl-dates { display:flex; gap:12px; flex-wrap:wrap; }
.wtl-date  { font-size:12px; color:var(--text-dim); }
.wtl-date strong { color:var(--text); font-weight:600; }

/* Stats panel */
.wtl-card-stats {
    display:flex; align-items:stretch;
    border-left:1px solid var(--border); margin:10px 0; flex-shrink:0;
}
.wtl-stat {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:0 20px; gap:1px; border-right:1px solid var(--border);
}
.wtl-stat:last-child { border-right:none; }
.wtl-stat-v { font-size:20px; font-weight:700; color:var(--text); line-height:1.2; font-family:var(--mono); }
.wtl-stat-l { font-size:10px; font-weight:600; letter-spacing:.6px; text-transform:uppercase; color:var(--text-dim); white-space:nowrap; }

/* Footer */
.wtl-card-foot {
    display:flex; align-items:center; gap:8px;
    padding:9px 16px; border-top:1px solid var(--border);
    background:var(--surface2,rgba(0,0,0,.02));
}
.wtl-action {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:7px;
    font-size:12px; font-weight:500;
    border:1px solid var(--border); cursor:pointer;
    text-decoration:none; background:var(--surface); color:var(--text);
    transition:all .15s; white-space:nowrap;
}
.wtl-action:hover        { border-color:#3b6bd4; color:#3b6bd4; }
.wtl-action.primary      { background:#3b6bd4; color:#fff; border-color:#3b6bd4; }
.wtl-action.primary:hover{ opacity:.88; }
.wtl-action.warn         { background:#d97706; color:#fff; border-color:#d97706; }
.wtl-action.warn:hover   { opacity:.88; }
.wtl-foot-spacer { flex:1; }
.wtl-foot-time   { font-size:11px; color:var(--text-faint); white-space:nowrap; }

/* Empty state */
.wtl-empty {
    text-align:center; padding:56px 32px;
    background:var(--surface); border:1px dashed var(--border); border-radius:12px;
}
.wtl-empty-ico { width:48px; height:48px; margin:0 auto 14px; color:var(--text-faint); }
.wtl-empty h3  { font-size:16px; font-weight:600; color:var(--text); margin:0 0 6px; }
.wtl-empty p   { font-size:13px; color:var(--text-dim); margin:0; }

/* Discrepancy highlight */
.wtl-card--discrepancy { border-color:rgba(239,68,68,.35) !important; }
.wtl-discrepancy-flag {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600;
    background:rgba(239,68,68,.1); color:#ef4444;
}

/* Pagination */
.wtl-pagination { margin-top:4px; }

/* Responsive */
@media(max-width:900px) {
    .wtl-card-stats { flex-wrap:wrap; }
}
@media(max-width:768px) {
    .wtl-page-header { flex-direction:column; align-items:flex-start; }
    .wtl-tabs { width:100%; }
    .wtl-card-top { flex-direction:column; }
    .wtl-card-stats { border-left:none; border-top:1px solid var(--border); margin:0; flex-wrap:wrap; }
    .wtl-stat { padding:10px 16px; flex:1; min-width:90px; }
    .wtl-card-foot { flex-wrap:wrap; }
    .wtl-action { flex:1; justify-content:center; }
    .wtl-foot-time { width:100%; text-align:center; }
}
@media(max-width:520px) {
    .wtl-stat-v { font-size:18px; }
    .wtl-route-node { max-width:110px; }
}
</style>

<div class="wtl-wrap">

    {{-- ── Page Header ────────────────────────────────── --}}
    <div class="wtl-page-header">
        <div>
            <h1 class="wtl-page-title">Outbound Transfers</h1>
            <p class="wtl-page-sub">Transfer requests from shops to this warehouse</p>
        </div>
    </div>

    {{-- ── Tab Filter ──────────────────────────────────── --}}
    <div class="wtl-tabs">
        <button wire:click="$set('statusFilter','all')"
                class="wtl-tab {{ $statusFilter==='all' ? 'active' : '' }}">
            All
            <span class="wtl-tab-badge">{{ $transfers->total() }}</span>
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::PENDING->value }}')"
                class="wtl-tab {{ $statusFilter===TransferStatus::PENDING->value ? 'active' : '' }}">
            Pending
            @if($pendingCount > 0)<span class="wtl-tab-badge">{{ $pendingCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::APPROVED->value }}')"
                class="wtl-tab {{ $statusFilter===TransferStatus::APPROVED->value ? 'active' : '' }}">
            Approved
            @if($approvedCount > 0)<span class="wtl-tab-badge">{{ $approvedCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::IN_TRANSIT->value }}')"
                class="wtl-tab {{ $statusFilter===TransferStatus::IN_TRANSIT->value ? 'active' : '' }}">
            In Transit
            @if($inTransitCount > 0)<span class="wtl-tab-badge">{{ $inTransitCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::DELIVERED->value }}')"
                class="wtl-tab {{ $statusFilter===TransferStatus::DELIVERED->value ? 'active' : '' }}">
            Delivered
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::RECEIVED->value }}')"
                class="wtl-tab {{ $statusFilter===TransferStatus::RECEIVED->value ? 'active' : '' }}">
            Received
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::REJECTED->value }}')"
                class="wtl-tab {{ $statusFilter===TransferStatus::REJECTED->value ? 'active' : '' }}">
            Rejected
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::CANCELLED->value }}')"
                class="wtl-tab {{ $statusFilter===TransferStatus::CANCELLED->value ? 'active' : '' }}">
            Cancelled
        </button>
    </div>

    {{-- ── Transfer Cards ──────────────────────────────── --}}
    <div class="wtl-list">
        @forelse($transfers as $transfer)
        @php
            $accent = $transfer->status->cssColor();
            $sc = [
                'color' => $accent,
                'bg'    => match($transfer->status->value) {
                    'pending'    => 'rgba(217,119,6,.1)',
                    'approved'   => 'rgba(59,107,212,.1)',
                    'in_transit' => 'rgba(139,92,246,.1)',
                    'delivered'  => 'rgba(14,165,233,.1)',
                    'received'   => 'rgba(16,185,129,.1)',
                    'rejected'   => 'rgba(239,68,68,.1)',
                    default      => 'rgba(100,116,139,.08)',
                },
            ];
            $itemsReq = $transfer->items->sum(function ($item) {
                $ipb = max(1, (int) ($item->product->items_per_box ?? 1));
                return (int) round($item->quantity_requested / $ipb);
            });
        @endphp
        <div class="wtl-card {{ $transfer->has_discrepancy ? 'wtl-card--discrepancy' : '' }}"
             style="--card-accent:{{ $accent }}">
            <div class="wtl-card-inner">
                <div class="wtl-accent-bar" style="background:{{ $accent }};"></div>
                <div class="wtl-card-body">

                    <div class="wtl-card-top">
                        {{-- Core info --}}
                        <div class="wtl-card-info">

                            {{-- Number + status badges --}}
                            <div class="wtl-card-meta">
                                <span class="wtl-num">{{ $transfer->transfer_number }}</span>
                                <span class="wtl-badge"
                                      style="--badge-bg:{{ $sc['bg'] }};--badge-color:{{ $sc['color'] }}">
                                    <span class="wtl-badge-dot"></span>
                                    {{ $transfer->status->label() }}
                                </span>
                                @if($transfer->status === TransferStatus::PENDING)
                                <span class="wtl-urgent">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v5h2v-5h-2zm0 6v2h2v-2h-2z"/></svg>
                                    Action Required
                                </span>
                                @endif
                                @if($transfer->has_discrepancy)
                                <span class="wtl-discrepancy-flag">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2z"/></svg>
                                    Discrepancy
                                </span>
                                @endif
                            </div>

                            {{-- Route --}}
                            <div class="wtl-route">
                                <div class="wtl-route-node">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="1"/><path d="M8 21h8M12 17v4"/></svg>
                                    Your Warehouse
                                </div>
                                <svg class="wtl-route-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                <div class="wtl-route-node">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    {{ $transfer->toShop->name ?? '—' }}
                                </div>
                            </div>

                            {{-- Dates --}}
                            <div class="wtl-dates">
                                <span class="wtl-date">Requested: <strong>{{ $transfer->requested_at?->format('d M Y') ?? '—' }}</strong></span>
                                @if($transfer->requestedBy)
                                <span class="wtl-date">By: <strong>{{ $transfer->requestedBy->name }}</strong></span>
                                @endif
                                @if($transfer->delivered_at)
                                <span class="wtl-date">Dispatched: <strong>{{ $transfer->delivered_at->format('d M Y') }}</strong></span>
                                @endif
                                @if($transfer->received_at)
                                <span class="wtl-date">Received: <strong>{{ $transfer->received_at->format('d M Y') }}</strong></span>
                                @endif
                            </div>

                        </div>

                        {{-- Stats panel --}}
                        <div class="wtl-card-stats">
                            <div class="wtl-stat">
                                <div class="wtl-stat-v">{{ $transfer->items->count() }}</div>
                                <div class="wtl-stat-l">Products</div>
                            </div>
                            <div class="wtl-stat">
                                <div class="wtl-stat-v">{{ $itemsReq }}</div>
                                <div class="wtl-stat-l">Boxes</div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer actions --}}
                    <div class="wtl-card-foot">
                        @if($transfer->status === TransferStatus::PENDING)
                        <a href="{{ route('warehouse.transfers.show', $transfer) }}" class="wtl-action warn">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                            Review &amp; Approve
                        </a>
                        @elseif($transfer->status === TransferStatus::APPROVED)
                        <a href="{{ route('warehouse.transfers.pack', $transfer) }}" class="wtl-action primary">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                            Pack Transfer
                        </a>
                        @else
                        <a href="{{ route('warehouse.transfers.show', $transfer) }}" class="wtl-action">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            View Details
                        </a>
                        @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED, TransferStatus::RECEIVED]))
                        <a href="{{ route('warehouse.transfers.delivery-note', $transfer) }}" target="_blank" class="wtl-action">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Delivery Note
                        </a>
                        @endif
                        @endif
                        <span class="wtl-foot-spacer"></span>
                        <span class="wtl-foot-time">{{ $transfer->created_at->diffForHumans() }}</span>
                    </div>

                </div>
            </div>
        </div>
        @empty
        <div class="wtl-empty">
            <svg class="wtl-empty-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <h3>No transfers found</h3>
            <p>{{ $statusFilter === 'all' ? 'No transfer requests have been made from shops yet.' : 'No transfers match the selected filter.' }}</p>
        </div>
        @endforelse
    </div>

    {{-- ── Pagination ──────────────────────────────────── --}}
    @if($transfers->hasPages())
    <div class="wtl-pagination">{{ $transfers->links() }}</div>
    @endif

</div>
</div>
