@php use App\Enums\TransferStatus; @endphp
<div>
<style>
/* ─── Warehouse Transfer List Styles (shared namespace wtl) ─── */
.wtl-wrap { display:flex; flex-direction:column; gap:20px; }

/* ── Page header */
.wtl-page-header { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.wtl-page-header-left h1 { font-size:29px; font-weight:800; letter-spacing:-.5px; color:var(--text); margin:0; }

/* ── Status dropdown */
.wtl-status-dropdown { position:relative; }
.wtl-dropdown-btn {
    display:flex; align-items:center; gap:8px;
    padding:10px 16px; background:var(--surface); color:var(--text);
    border:1.5px solid var(--border-hi); border-radius:8px;
    font-size:14px; font-weight:600; cursor:pointer;
    transition:border-color .15s, box-shadow .15s;
}
.wtl-dropdown-btn:hover { border-color:var(--accent); }
.wtl-dropdown-btn svg { transition:transform .2s; }
.wtl-dropdown-btn.open svg { transform:rotate(180deg); }
.wtl-dropdown-menu {
    position:absolute; top:calc(100% + 6px); right:0; z-index:10;
    background:var(--surface); border:1px solid var(--border);
    border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.12);
    min-width:200px; padding:6px; display:none;
}
.wtl-dropdown-menu.open { display:block; }
.wtl-dropdown-item {
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 12px; border-radius:6px; cursor:pointer;
    font-size:14px; font-weight:600; color:var(--text);
    transition:background .15s;
}
.wtl-dropdown-item:hover { background:var(--surface2); }
.wtl-dropdown-item.active { background:var(--accent-dim); color:var(--accent); }
.wtl-dropdown-count {
    font-size:12px; font-weight:700; padding:2px 8px;
    background:var(--surface3); border-radius:12px;
    color:var(--text-sub);
}
.wtl-dropdown-item.active .wtl-dropdown-count { background:var(--accent); color:#fff; }

/* ── Pipeline strip (hidden in favor of dropdown) */
.wtl-pipeline { display:none; }

/* ── Filter / search bar (removed in favor of dropdown) */
.wtl-bar { display:none; }

/* ── Transfer cards */
.wtl-list { display:flex; flex-direction:column; gap:10px; }

.wtl-card {
    background:var(--surface);
    border:2px solid rgba(128,128,128,.35);
    border-radius:var(--r);
    overflow:hidden;
    transition:border-color .2s, box-shadow .18s;
    position:relative;
}
.wtl-card:hover {
    border-color:var(--card-color,var(--accent));
    box-shadow:0 4px 20px rgba(0,0,0,.08);
}
.wtl-card-stripe {
    position:absolute; top:0; left:0; bottom:0; width:4px;
    background:transparent;
    transition:background .2s;
}
.wtl-card:hover .wtl-card-stripe {
    background:var(--card-color,var(--accent));
}

/* Card top row */
.wtl-card-top {
    display:flex; align-items:stretch; gap:0;
    padding:0 18px 0 22px; min-height:88px;
}
.wtl-card-info { flex:1; display:flex; flex-direction:column; justify-content:center; gap:5px; padding:14px 0; }

/* Number + badge row */
.wtl-card-meta { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.wtl-num       { font-size:17px; font-weight:800; color:var(--text); letter-spacing:-.2px; }
.wtl-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 9px; border-radius:20px; font-size:12px; font-weight:700;
    letter-spacing:.5px; text-transform:uppercase;
    background:var(--badge-bg); color:var(--badge-color);
}
.wtl-badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }

/* Urgency flag */
.wtl-urgent {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 9px; border-radius:20px; font-size:12px; font-weight:700;
    background:rgba(217,119,6,.12); color:#d97706;
}

/* Route */
.wtl-route { display:flex; align-items:center; gap:8px; font-size:14px; }
.wtl-route-node {
    display:inline-flex; align-items:center; gap:5px; font-weight:700; color:var(--text);
}
.wtl-route-node svg { color:var(--text-sub); flex-shrink:0; }
.wtl-route-dash {
    flex:0 0 auto; display:flex; align-items:center; gap:3px;
}
.wtl-route-dash-line { width:40px; border-top:1.5px dashed var(--border); }

/* Dates */
.wtl-dates { display:flex; gap:14px; flex-wrap:wrap; }
.wtl-date  { font-size:13px; color:var(--text-sub); }
.wtl-date strong { color:var(--text); font-weight:600; }

/* Stats */
.wtl-card-stats {
    display:flex; align-items:stretch;
    border-left:1px solid var(--border); margin:14px 0;
}
.wtl-stat {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:0 18px; gap:2px; border-right:1px solid var(--border);
}
.wtl-stat:last-child { border-right:none; }
.wtl-stat-v { font-size:24px; font-weight:800; color:var(--text); line-height:1; }
.wtl-stat-l { font-size:11px; font-weight:700; letter-spacing:.9px; text-transform:uppercase; color:var(--text-sub); }

/* Footer */
.wtl-card-foot {
    display:flex; align-items:center; gap:8px;
    padding:10px 18px; border-top:1px solid var(--border);
    background:var(--surface2);
}
.wtl-action {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:var(--rsm,6px); font-size:13px; font-weight:600;
    border:1px solid var(--border); cursor:pointer; text-decoration:none;
    background:var(--surface); color:var(--text); transition:all .15s;
}
.wtl-action:hover         { border-color:var(--accent); color:var(--accent); }
.wtl-action.primary       { background:var(--accent); color:#fff; border-color:var(--accent); }
.wtl-action.primary:hover { opacity:.88; }
.wtl-action.warn          { background:#d97706; color:#fff; border-color:#d97706; }
.wtl-action.warn:hover    { opacity:.88; }
.wtl-foot-time { margin-left:auto; font-size:12px; color:var(--text-sub); }

/* Empty state */
.wtl-empty {
    text-align:center; padding:64px 32px;
    background:var(--surface); border:1px dashed var(--border); border-radius:var(--r);
}
.wtl-empty-ico { font-size:53px; margin-bottom:16px; }
.wtl-empty h3  { font-size:20px; font-weight:700; color:var(--text); margin:0 0 8px; }
.wtl-empty p   { font-size:16px; color:var(--text-sub); margin:0; }

/* Pagination */
.wtl-pagination { margin-top:4px; }

/* Responsive Design */
@media(max-width:900px) {
    .wtl-card-stats { flex-wrap:wrap; }
}

@media(max-width:768px) {
    .wtl-page-header { flex-direction:column; align-items:stretch; gap:12px; }
    .wtl-page-header h1 { font-size:24px; }
    .wtl-dropdown-btn { width:100%; justify-content:space-between; }
    .wtl-dropdown-menu { left:0; right:0; }
    .wtl-card-top { flex-direction:column; padding:0 16px; min-height:auto; }
    .wtl-card-stats { border-left:none; border-top:1px solid var(--border); margin:0; }
    .wtl-stat { padding:12px 16px; flex:1; min-width:100px; }
    .wtl-route { flex-wrap:wrap; }
    .wtl-route-dash-line { width:30px; }
    .wtl-dates { gap:8px; }
    .wtl-card-foot { padding:12px 16px; flex-wrap:wrap; gap:8px; }
    .wtl-action { flex:1; justify-content:center; font-size:12px; }
}

@media(max-width:640px) {
    .wtl-page-header h1 { font-size:22px; }
    .wtl-dropdown-btn { font-size:13px; padding:9px 14px; }
    .wtl-card { border-radius:8px; }
    .wtl-card-top { padding:0 14px; }
    .wtl-card-info { padding:12px 0; }
    .wtl-num { font-size:15px; }
    .wtl-badge { font-size:10px; padding:2px 8px; }
    .wtl-route { font-size:13px; gap:6px; }
    .wtl-route-dash-line { width:20px; }
    .wtl-route-node { max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .wtl-stat { padding:10px 12px; min-width:80px; border-right:none; border-bottom:1px solid var(--border); }
    .wtl-stat:last-child { border-bottom:none; }
    .wtl-stat-v { font-size:20px; }
    .wtl-stat-l { font-size:10px; }
    .wtl-card-foot { padding:10px 14px; gap:6px; }
    .wtl-foot-time { width:100%; text-align:center; margin-left:0; margin-top:4px; }
    .wtl-empty { padding:48px 24px; }
    .wtl-empty-ico { font-size:42px; margin-bottom:12px; }
    .wtl-empty h3 { font-size:18px; }
    .wtl-empty p { font-size:14px; }
    .wtl-wrap { gap:16px; }
}

</style>

<div class="wtl-wrap">

  {{-- ── Page Header with Status Dropdown ─────────── --}}
  <div class="wtl-page-header">
    <h1>Outbound Transfers</h1>

    <div class="wtl-status-dropdown" x-data="{ open: false }" @click.away="open = false">
      <button @click="open = !open" class="wtl-dropdown-btn" :class="{ 'open': open }">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
        </svg>
        <span>
          @if($statusFilter === 'all') All Transfers
          @elseif($statusFilter === TransferStatus::PENDING->value) Pending
          @elseif($statusFilter === TransferStatus::APPROVED->value) Approved
          @elseif($statusFilter === TransferStatus::IN_TRANSIT->value) In Transit
          @elseif($statusFilter === TransferStatus::DELIVERED->value) Delivered
          @elseif($statusFilter === TransferStatus::RECEIVED->value) Received
          @elseif($statusFilter === TransferStatus::REJECTED->value) Rejected
          @elseif($statusFilter === TransferStatus::CANCELLED->value) Cancelled
          @endif
        </span>
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div class="wtl-dropdown-menu" :class="{ 'open': open }">
        <button wire:click="$set('statusFilter','all')" @click="open = false"
                class="wtl-dropdown-item {{ $statusFilter==='all'?'active':'' }}">
          <span>All Transfers</span>
          <span class="wtl-dropdown-count">{{ $transfers->total() }}</span>
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::PENDING->value }}')" @click="open = false"
                class="wtl-dropdown-item {{ $statusFilter===TransferStatus::PENDING->value?'active':'' }}">
          <span>Pending</span>
          @if($pendingCount > 0)<span class="wtl-dropdown-count">{{ $pendingCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::APPROVED->value }}')" @click="open = false"
                class="wtl-dropdown-item {{ $statusFilter===TransferStatus::APPROVED->value?'active':'' }}">
          <span>Approved</span>
          @if($approvedCount > 0)<span class="wtl-dropdown-count">{{ $approvedCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::IN_TRANSIT->value }}')" @click="open = false"
                class="wtl-dropdown-item {{ $statusFilter===TransferStatus::IN_TRANSIT->value?'active':'' }}">
          <span>In Transit</span>
          @if($inTransitCount > 0)<span class="wtl-dropdown-count">{{ $inTransitCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::DELIVERED->value }}')" @click="open = false"
                class="wtl-dropdown-item {{ $statusFilter===TransferStatus::DELIVERED->value?'active':'' }}">
          <span>Delivered</span>
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::RECEIVED->value }}')" @click="open = false"
                class="wtl-dropdown-item {{ $statusFilter===TransferStatus::RECEIVED->value?'active':'' }}">
          <span>Received</span>
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::REJECTED->value }}')" @click="open = false"
                class="wtl-dropdown-item {{ $statusFilter===TransferStatus::REJECTED->value?'active':'' }}">
          <span>Rejected</span>
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::CANCELLED->value }}')" @click="open = false"
                class="wtl-dropdown-item {{ $statusFilter===TransferStatus::CANCELLED->value?'active':'' }}">
          <span>Cancelled</span>
        </button>
      </div>
    </div>
  </div>

  {{-- ── Transfer Cards ──────────────────────────── --}}
  <div class="wtl-list">
    @forelse($transfers as $transfer)
    @php
      $sc = match($transfer->status->value) {
        'pending'    => ['color'=>'#d97706','bg'=>'rgba(217,119,6,.1)'],
        'approved'   => ['color'=>'var(--accent)','bg'=>'rgba(99,102,241,.1)'],
        'in_transit' => ['color'=>'var(--violet)','bg'=>'rgba(139,92,246,.1)'],
        'delivered'  => ['color'=>'#0ea5e9','bg'=>'rgba(14,165,233,.1)'],
        'received'   => ['color'=>'var(--green)','bg'=>'rgba(16,185,129,.1)'],
        'rejected'   => ['color'=>'#ef4444','bg'=>'rgba(239,68,68,.1)'],
        default      => ['color'=>'var(--text-sub)','bg'=>'rgba(128,128,128,.08)'],
      };
      $itemsReq = $transfer->items->sum(function ($item) {
          $ipb = max(1, (int) ($item->product->items_per_box ?? 1));
          return (int) round($item->quantity_requested / $ipb);
      });
    @endphp
    <div class="wtl-card" style="--card-color:{{ $sc['color'] }}">
      <div class="wtl-card-stripe"></div>

      <div class="wtl-card-top">
        {{-- ── Core info ── --}}
        <div class="wtl-card-info">

          {{-- Number + status + urgency --}}
          <div class="wtl-card-meta">
            <span class="wtl-num">{{ $transfer->transfer_number }}</span>
            <span class="wtl-badge" style="--badge-bg:{{ $sc['bg'] }};--badge-color:{{ $sc['color'] }}">
              <span class="wtl-badge-dot"></span>
              {{ $transfer->status->label() }}
            </span>
            @if($transfer->status === TransferStatus::PENDING)
              <span class="wtl-urgent">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v5h2v-5h-2zm0 6v2h2v-2h-2z"/></svg>
                Action Required
              </span>
            @endif
          </div>

          {{-- Route: Warehouse → Shop --}}
          <div class="wtl-route">
            <div class="wtl-route-node">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="1"/><path d="M8 21h8M12 17v4"/></svg>
              Your Warehouse
            </div>
            <div class="wtl-route-dash">
              <div class="wtl-route-dash-line"></div>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $sc['color'] }}"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
            <div class="wtl-route-node">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              {{ $transfer->toShop->name ?? '—' }}
            </div>
          </div>

          {{-- Timeline dates --}}
          <div class="wtl-dates">
            <span class="wtl-date">
              Requested: <strong>{{ $transfer->requested_at?->format('d M Y') ?? '—' }}</strong>
            </span>
            @if($transfer->requestedBy)
            <span class="wtl-date">
              By: <strong>{{ $transfer->requestedBy->name }}</strong>
            </span>
            @endif
            @if($transfer->delivered_at)
            <span class="wtl-date">
              Dispatched: <strong>{{ $transfer->delivered_at->format('d M Y') }}</strong>
            </span>
            @endif
          </div>
        </div>

        {{-- ── Stats panel ── --}}
        <div class="wtl-card-stats">
          <div class="wtl-stat">
            <div class="wtl-stat-v">{{ $transfer->items->count() }}</div>
            <div class="wtl-stat-l">Products</div>
          </div>
          <div class="wtl-stat">
            <div class="wtl-stat-v">{{ $itemsReq }}</div>
            <div class="wtl-stat-l">Boxes Req.</div>
          </div>
        </div>
      </div>

      {{-- ── Footer actions ── --}}
      <div class="wtl-card-foot">
        @if($transfer->status === TransferStatus::PENDING)
          <a href="{{ route('warehouse.transfers.show', $transfer) }}" class="wtl-action warn">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            Review &amp; Approve
          </a>
        @elseif($transfer->status === TransferStatus::APPROVED)
          <a href="{{ route('warehouse.transfers.pack', $transfer) }}" class="wtl-action primary">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            Pack Transfer
          </a>
        @else
          <a href="{{ route('warehouse.transfers.show', $transfer) }}" class="wtl-action">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            View Details
          </a>
          @if (in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED, TransferStatus::RECEIVED]))
          <a href="{{ route('warehouse.transfers.delivery-note', $transfer) }}" target="_blank" class="wtl-action">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Delivery Note
          </a>
          @endif
        @endif

        <span class="wtl-foot-time">{{ $transfer->created_at->diffForHumans() }}</span>
      </div>
    </div>
    @empty
    <div class="wtl-empty">
      <div class="wtl-empty-ico">🏭</div>
      <h3>No transfers found</h3>
      <p>{{ $statusFilter==='all' ? 'No transfer requests have been made from shops yet.' : 'No transfers match the selected filter.' }}</p>
    </div>
    @endforelse
  </div>

  {{-- ── Pagination ──────────────────────────────── --}}
  @if($transfers->hasPages())
    <div class="wtl-pagination">{{ $transfers->links() }}</div>
  @endif

</div>
</div>
