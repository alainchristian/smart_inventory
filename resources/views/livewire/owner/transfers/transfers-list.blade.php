@php use App\Enums\TransferStatus; @endphp
<div>
<style>
/* ─── Owner Transfer List Styles (shared namespace otl) ─── */
.otl-wrap { display:flex; flex-direction:column; gap:20px; }

/* ── Page header */
.otl-page-header { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.otl-page-header-left h1 { font-size:35px; font-weight:800; letter-spacing:-.5px; color:var(--text); margin:0; }

/* ── Status dropdown */
.otl-status-dropdown { position:relative; }
.otl-dropdown-btn {
    display:flex; align-items:center; gap:8px;
    padding:10px 16px; background:var(--surface); color:var(--text);
    border:1.5px solid var(--border-hi); border-radius:8px;
    font-size:17px; font-weight:600; cursor:pointer;
    transition:border-color .15s, box-shadow .15s;
}
.otl-dropdown-btn:hover { border-color:var(--accent); }
.otl-dropdown-btn svg { transition:transform .2s; }
.otl-dropdown-btn.open svg { transform:rotate(180deg); }
.otl-dropdown-menu {
    position:absolute; top:calc(100% + 6px); right:0; z-index:10;
    background:var(--surface); border:1px solid var(--border);
    border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.12);
    min-width:200px; padding:6px; display:none;
}
.otl-dropdown-menu.open { display:block; }
.otl-dropdown-item {
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 12px; border-radius:6px; cursor:pointer;
    font-size:17px; font-weight:600; color:var(--text);
    transition:background .15s;
}
.otl-dropdown-item:hover { background:var(--surface2); }
.otl-dropdown-item.active { background:var(--accent-dim); color:var(--accent); }
.otl-dropdown-count {
    font-size:14px; font-weight:700; padding:2px 8px;
    background:var(--surface3); border-radius:12px;
    color:var(--text-sub);
}
.otl-dropdown-item.active .otl-dropdown-count { background:var(--accent); color:#fff; }

/* ── Transfer cards */
.otl-list { display:flex; flex-direction:column; gap:10px; }

.otl-card {
    background:var(--surface);
    border:2px solid rgba(128,128,128,.35);
    border-radius:var(--r);
    overflow:hidden;
    transition:border-color .2s, box-shadow .18s;
    position:relative;
}
.otl-card:hover {
    border-color:var(--card-color,var(--accent));
    box-shadow:0 4px 20px rgba(0,0,0,.08);
}
.otl-card-stripe {
    position:absolute; top:0; left:0; bottom:0; width:4px;
    background:transparent;
    transition:background .2s;
}
.otl-card:hover .otl-card-stripe {
    background:var(--card-color,var(--accent));
}

/* Card top row */
.otl-card-top {
    display:flex; align-items:stretch; gap:0;
    padding:0 18px 0 22px; min-height:88px;
}
.otl-card-info { flex:1; display:flex; flex-direction:column; justify-content:center; gap:5px; padding:14px 0; }

/* Number + badge row */
.otl-card-meta { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.otl-num       { font-size:17px; font-weight:800; color:var(--text); letter-spacing:-.2px; }
.otl-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 9px; border-radius:20px; font-size:14px; font-weight:700;
    letter-spacing:.5px; text-transform:uppercase;
    background:var(--badge-bg); color:var(--badge-color);
}
.otl-badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }

/* Urgency flag */
.otl-urgent {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 9px; border-radius:20px; font-size:14px; font-weight:700;
    background:rgba(217,119,6,.12); color:#d97706;
}

/* Route */
.otl-route { display:flex; align-items:center; gap:8px; font-size:17px; }
.otl-route-node {
    display:inline-flex; align-items:center; gap:5px; font-weight:700; color:var(--text);
}
.otl-route-node svg { color:var(--text-sub); flex-shrink:0; }
.otl-route-dash {
    flex:0 0 auto; display:flex; align-items:center; gap:3px;
}
.otl-route-dash-line { width:40px; border-top:1.5px dashed var(--border); }

/* Dates */
.otl-dates { display:flex; gap:14px; flex-wrap:wrap; }
.otl-date  { font-size:16px; color:var(--text-sub); }
.otl-date strong { color:var(--text); font-weight:600; }

/* Stats */
.otl-card-stats {
    display:flex; align-items:stretch;
    border-left:1px solid var(--border); margin:14px 0;
}
.otl-stat {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:0 18px; gap:2px; border-right:1px solid var(--border);
}
.otl-stat:last-child { border-right:none; }
.otl-stat-v { font-size:29px; font-weight:800; color:var(--text); line-height:1; }
.otl-stat-l { font-size:13px; font-weight:700; letter-spacing:.9px; text-transform:uppercase; color:var(--text-sub); }

/* Footer */
.otl-card-foot {
    display:flex; align-items:center; gap:8px;
    padding:10px 18px; border-top:1px solid var(--border);
    background:var(--surface2);
}
.otl-action {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:var(--rsm,6px); font-size:16px; font-weight:600;
    border:1px solid var(--border); cursor:pointer; text-decoration:none;
    background:var(--surface); color:var(--text); transition:all .15s;
}
.otl-action:hover         { border-color:var(--accent); color:var(--accent); }
.otl-action.primary       { background:var(--accent); color:#fff; border-color:var(--accent); }
.otl-action.primary:hover { opacity:.88; }
.otl-action.warn          { background:#d97706; color:#fff; border-color:#d97706; }
.otl-action.warn:hover    { opacity:.88; }
.otl-foot-time { margin-left:auto; font-size:14px; color:var(--text-sub); }

/* Empty state */
.otl-empty {
    text-align:center; padding:64px 32px;
    background:var(--surface); border:1px dashed var(--border); border-radius:var(--r);
}
.otl-empty-ico { font-size:53px; margin-bottom:16px; }
.otl-empty h3  { font-size:24px; font-weight:700; color:var(--text); margin:0 0 8px; }
.otl-empty p   { font-size:19px; color:var(--text-sub); margin:0; }

/* Pagination */
.otl-pagination { margin-top:4px; }

/* Responsive Design */
@media(max-width:900px) {
    .otl-card-stats { flex-wrap:wrap; }
}

@media(max-width:768px) {
    .otl-page-header { flex-direction:column; align-items:stretch; gap:12px; }
    .otl-page-header h1 { font-size:29px; }
    .otl-dropdown-btn { width:100%; justify-content:space-between; }
    .otl-dropdown-menu { left:0; right:0; }
    .otl-card-top { flex-direction:column; padding:0 16px; min-height:auto; }
    .otl-card-stats { border-left:none; border-top:1px solid var(--border); margin:0; }
    .otl-stat { padding:12px 16px; flex:1; min-width:100px; }
    .otl-route { flex-wrap:wrap; }
    .otl-route-dash-line { width:30px; }
    .otl-dates { gap:8px; }
    .otl-card-foot { padding:12px 16px; flex-wrap:wrap; gap:8px; }
    .otl-action { flex:1; justify-content:center; font-size:14px; }
}

@media(max-width:640px) {
    .otl-page-header h1 { font-size:26px; }
    .otl-dropdown-btn { font-size:16px; padding:9px 14px; }
    .otl-card { border-radius:8px; }
    .otl-card-top { padding:0 14px; }
    .otl-card-info { padding:12px 0; }
    .otl-num { font-size:18px; }
    .otl-badge { font-size:12px; padding:2px 8px; }
    .otl-route { font-size:16px; gap:6px; }
    .otl-route-dash-line { width:20px; }
    .otl-route-node { max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .otl-stat { padding:10px 12px; min-width:80px; border-right:none; border-bottom:1px solid var(--border); }
    .otl-stat:last-child { border-bottom:none; }
    .otl-stat-v { font-size:24px; }
    .otl-stat-l { font-size:12px; }
    .otl-card-foot { padding:10px 14px; gap:6px; }
    .otl-foot-time { width:100%; text-align:center; margin-left:0; margin-top:4px; }
    .otl-empty { padding:48px 24px; }
    .otl-empty-ico { font-size:42px; margin-bottom:12px; }
    .otl-empty h3 { font-size:22px; }
    .otl-empty p { font-size:17px; }
    .otl-wrap { gap:16px; }
}


/* Responsive base — applied to all transfer pages */
@media(max-width:600px) {
    /* Cards */
    .tl-card, .rf-card {
        border-radius:var(--rsm, 8px);
    }
    /* Tables inside cards — make them scroll horizontally */
    table {
        display:block;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        white-space:nowrap;
    }
    /* Prevent text overflow on narrow screens */
    .tl-num, .rf-prod-name, .tl-route-node {
        max-width:140px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    /* Badges wrap instead of overflow */
    .tl-card-meta, .tl-dates {
        flex-wrap:wrap;
        gap:4px;
    }
}
\n
/* 2A - Transfer List Fixes */
@media(max-width:900px) {
    .tl-pipeline { grid-template-columns: repeat(3, 1fr); }
}
@media(max-width:600px) {
    .tl-pipeline { grid-template-columns: repeat(2, 1fr); gap:0; }
    .tl-pipeline-step { padding:10px 12px; }
    .tl-step-num  { font-size:20px; }
    .tl-step-sub  { display:none; }
    .tl-card-top    { flex-direction:column; padding:0 14px; }
    .tl-card-stats  { border-left:none; border-top:1px solid var(--border); margin:0 0 8px; flex-wrap:wrap; }
    .tl-stat        { padding:8px 14px; flex:1; min-width:80px; }
    .tl-bar         { gap:4px; padding:8px 10px; }
    .tl-chip        { padding:4px 10px; font-size:11px; }
    .tl-search      { width:100%; margin-left:0; margin-top:6px; }
    .tl-search input{ width:100%; }
    .tl-route-dash-line { width:20px; }
    .tl-card-foot   { flex-wrap:wrap; gap:6px; }
    .tl-action      { flex:1; justify-content:center; }
    .tl-foot-time   { width:100%; text-align:center; margin-left:0; }
    .tl-page-header         { flex-direction:column; align-items:flex-start; }
    .tl-page-header-left h1 { font-size:20px; }
    .tl-new-btn             { width:100%; justify-content:center; }
}
\n
/* 2B - Request Form Fixes */
@media(max-width:860px) {
    .rf-layout { grid-template-columns:1fr; }
    .rf-summary { position:static; }
}
@media(max-width:600px) {
    .rf-row2 { grid-template-columns:1fr; }
    .rf-prod-row    { flex-wrap:wrap; gap:8px; }
    .rf-prod-info   { width:100%; }
    .rf-stock       { align-items:flex-start; }
    .rf-add-btn     { width:100%; justify-content:center; }
    .rf-item-top    { flex-wrap:wrap; }
    .rf-qty-ctrl    { width:100%; justify-content:space-between; }
}
\n</style>

<div class="otl-wrap">

  {{-- ── Page Header with Status Dropdown ─────────── --}}
  <div class="otl-page-header">
    <h1>All Transfers</h1>

    <div class="otl-status-dropdown" x-data="{ open: false }" @click.away="open = false">
      <button @click="open = !open" class="otl-dropdown-btn" :class="{ 'open': open }">
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
          @elseif($statusFilter === 'discrepancy') With Discrepancies
          @endif
        </span>
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div class="otl-dropdown-menu" :class="{ 'open': open }">
        <button wire:click="$set('statusFilter','all')" @click="open = false"
                class="otl-dropdown-item {{ $statusFilter==='all'?'active':'' }}">
          <span>All Transfers</span>
          <span class="otl-dropdown-count">{{ $transfers->total() }}</span>
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::PENDING->value }}')" @click="open = false"
                class="otl-dropdown-item {{ $statusFilter===TransferStatus::PENDING->value?'active':'' }}">
          <span>Pending</span>
          @if($pendingCount > 0)<span class="otl-dropdown-count">{{ $pendingCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::APPROVED->value }}')" @click="open = false"
                class="otl-dropdown-item {{ $statusFilter===TransferStatus::APPROVED->value?'active':'' }}">
          <span>Approved</span>
          @if($approvedCount > 0)<span class="otl-dropdown-count">{{ $approvedCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::IN_TRANSIT->value }}')" @click="open = false"
                class="otl-dropdown-item {{ $statusFilter===TransferStatus::IN_TRANSIT->value?'active':'' }}">
          <span>In Transit</span>
          @if($inTransitCount > 0)<span class="otl-dropdown-count">{{ $inTransitCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::DELIVERED->value }}')" @click="open = false"
                class="otl-dropdown-item {{ $statusFilter===TransferStatus::DELIVERED->value?'active':'' }}">
          <span>Delivered</span>
          @if($deliveredCount > 0)<span class="otl-dropdown-count">{{ $deliveredCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::RECEIVED->value }}')" @click="open = false"
                class="otl-dropdown-item {{ $statusFilter===TransferStatus::RECEIVED->value?'active':'' }}">
          <span>Received</span>
          @if($receivedCount > 0)<span class="otl-dropdown-count">{{ $receivedCount }}</span>@endif
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::REJECTED->value }}')" @click="open = false"
                class="otl-dropdown-item {{ $statusFilter===TransferStatus::REJECTED->value?'active':'' }}">
          <span>Rejected</span>
        </button>
        <button wire:click="$set('statusFilter','{{ TransferStatus::CANCELLED->value }}')" @click="open = false"
                class="otl-dropdown-item {{ $statusFilter===TransferStatus::CANCELLED->value?'active':'' }}">
          <span>Cancelled</span>
        </button>
        <button wire:click="$set('statusFilter','discrepancy')" @click="open = false"
                class="otl-dropdown-item {{ $statusFilter==='discrepancy'?'active':'' }}">
          <span>With Discrepancies</span>
          @if($discrepancyCount > 0)<span class="otl-dropdown-count">{{ $discrepancyCount }}</span>@endif
        </button>
      </div>
    </div>
  </div>

  {{-- ── Transfer Cards ──────────────────────────── --}}
  <div class="otl-list">
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
    <div class="otl-card" style="--card-color:{{ $sc['color'] }}">
      <div class="otl-card-stripe"></div>

      <div class="otl-card-top">
        {{-- ── Core info ── --}}
        <div class="otl-card-info">

          {{-- Number + status + urgency --}}
          <div class="otl-card-meta">
            <span class="otl-num">{{ $transfer->transfer_number }}</span>
            <span class="otl-badge" style="--badge-bg:{{ $sc['bg'] }};--badge-color:{{ $sc['color'] }}">
              <span class="otl-badge-dot"></span>
              {{ $transfer->status->label() }}
            </span>
            @if($transfer->status === TransferStatus::PENDING)
              <span class="otl-urgent">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v5h2v-5h-2zm0 6v2h2v-2h-2z"/></svg>
                Awaiting Approval
              </span>
            @endif
            @if($transfer->has_discrepancy)
              <span class="otl-urgent">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v5h2v-5h-2zm0 6v2h2v-2h-2z"/></svg>
                Discrepancy
              </span>
            @endif
          </div>

          {{-- Route: Warehouse → Shop --}}
          <div class="otl-route">
            <div class="otl-route-node">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="1"/><path d="M8 21h8M12 17v4"/></svg>
              {{ $transfer->fromWarehouse->name ?? 'Warehouse' }}
            </div>
            <div class="otl-route-dash">
              <div class="otl-route-dash-line"></div>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $sc['color'] }}"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
            <div class="otl-route-node">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              {{ $transfer->toShop->name ?? 'Shop' }}
            </div>
          </div>

          {{-- Timeline dates --}}
          <div class="otl-dates">
            <span class="otl-date">
              Requested: <strong>{{ $transfer->requested_at?->format('d M Y') ?? '—' }}</strong>
            </span>
            @if($transfer->requestedBy)
            <span class="otl-date">
              By: <strong>{{ $transfer->requestedBy->name }}</strong>
            </span>
            @endif
            @if($transfer->delivered_at)
            <span class="otl-date">
              Dispatched: <strong>{{ $transfer->delivered_at->format('d M Y') }}</strong>
            </span>
            @endif
          </div>
        </div>

        {{-- ── Stats panel ── --}}
        <div class="otl-card-stats">
          <div class="otl-stat">
            <div class="otl-stat-v">{{ $transfer->items->count() }}</div>
            <div class="otl-stat-l">Products</div>
          </div>
          <div class="otl-stat">
            <div class="otl-stat-v">{{ $itemsReq }}</div>
            <div class="otl-stat-l">Boxes Req.</div>
          </div>
        </div>
      </div>

      {{-- ── Footer actions ── --}}
      <div class="otl-card-foot">
        <a href="{{ route('owner.transfers.show', $transfer) }}" class="otl-action">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          View Details
        </a>

        <span class="otl-foot-time">{{ $transfer->created_at->diffForHumans() }}</span>
      </div>
    </div>
    @empty
    <div class="otl-empty">
      <div class="otl-empty-ico">📦</div>
      <h3>No transfers found</h3>
      <p>{{ $statusFilter==='all' ? 'No transfer requests have been made yet.' : 'No transfers match the selected filter.' }}</p>
    </div>
    @endforelse
  </div>

  {{-- ── Pagination ──────────────────────────────── --}}
  @if($transfers->hasPages())
    <div class="otl-pagination">{{ $transfers->links() }}</div>
  @endif

</div>
</div>
