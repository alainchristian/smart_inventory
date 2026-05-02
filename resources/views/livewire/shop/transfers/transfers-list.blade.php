@php use App\Enums\TransferStatus; @endphp
<div wire:poll.10s>
<style>
/* ─── Transfer List Styles ───────────────────────────── */
.tl-wrap { display:flex; flex-direction:column; gap:20px; }

/* Mission 2C: Mobile padding override */
@media(max-width:600px) {
    .max-w-7xl { padding-left:12px !important; padding-right:12px !important; }
    .py-6 { padding-top:16px !important; padding-bottom:16px !important; }
}

/* ── New Request Button */
.tl-new-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:12px 20px; border-radius:10px; border:none; cursor:pointer;
    background:var(--accent); color:#fff; font-size:23px; font-weight:700;
    text-decoration:none; transition:all .2s; width:100%;
    box-shadow: 0 2px 8px rgba(59, 111, 212, 0.25);
}
.tl-new-btn:hover { background:#2d5dbf; transform:translateY(-1px); box-shadow: 0 4px 12px rgba(59, 111, 212, 0.3); }
.tl-new-btn svg { flex-shrink:0; }

/* ── Filter Dropdown */
.tl-filter-wrap {
    position:relative;
}
.tl-filter-btn {
    width:100%; display:flex; align-items:center; gap:10px;
    padding:12px 16px; background:var(--surface); border:1.5px solid var(--border);
    border-radius:10px; cursor:pointer; font-size:22px; font-weight:600;
    color:var(--text); transition:all .2s;
}
.tl-filter-btn:hover {
    border-color:var(--border-hi); background:var(--surface2);
}
.tl-filter-btn svg:first-child {
    color:var(--text-sub); flex-shrink:0;
}
.tl-filter-btn svg:last-child {
    margin-left:auto; color:var(--text-sub); flex-shrink:0;
}
.tl-filter-btn span {
    flex:1; text-align:left;
}

.tl-filter-menu {
    position:absolute; top:calc(100% + 6px); left:0; right:0; z-index:50;
    background:var(--surface); border:1.5px solid var(--border);
    border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,.1);
    overflow:hidden; opacity:0; visibility:hidden;
    transform:translateY(-8px); transition:all .2s;
}
.tl-filter-menu.open {
    opacity:1; visibility:visible; transform:translateY(0);
}
.tl-filter-item {
    width:100%; display:flex; align-items:center; gap:10px;
    padding:10px 16px; background:transparent; border:none;
    border-bottom:1px solid var(--border); cursor:pointer;
    font-size:20px; font-weight:600; color:var(--text);
    text-align:left; transition:background .15s;
}
.tl-filter-item:last-child {
    border-bottom:none;
}
.tl-filter-item:hover {
    background:var(--surface2);
}
.tl-filter-item.active {
    background:var(--accent-dim); color:var(--accent);
}
.tl-filter-dot {
    width:8px; height:8px; border-radius:50%; flex-shrink:0;
}
.tl-filter-count {
    margin-left:auto; font-size:17px; font-weight:700;
    padding:2px 8px; border-radius:12px;
    background:var(--surface3); color:var(--text-sub);
}

/* OLD STYLES - Removed for cleaner dropdown design */

/* ── Transfer cards */
.tl-list { display:flex; flex-direction:column; gap:10px; }

@media(max-width:600px) {
    .tl-list { gap:12px; }
}

.tl-card {
    background:var(--surface); border:1px solid var(--border); border-radius:var(--r);
    overflow:hidden; transition:border-color .2s, box-shadow .18s; position:relative;
}

@media(max-width:600px) {
    .tl-card { border-radius:10px; }
}
.tl-card:hover { border-color:var(--card-color,var(--accent)); box-shadow:0 4px 20px rgba(0,0,0,.08); }
.tl-card-stripe {
    position:absolute; top:0; left:0; bottom:0; width:4px;
    background:var(--card-color,var(--accent));
}

@media(max-width:600px) {
    .tl-card-stripe { width:3px; }
}

/* Card top row */
.tl-card-top {
    display:flex; align-items:stretch; gap:0;
    padding:0 18px 0 22px; min-height:88px;
}
.tl-card-info { flex:1; display:flex; flex-direction:column; justify-content:center; gap:5px; padding:14px 0; }

@media(max-width:600px) {
    .tl-card-top { padding:0 14px; min-height:auto; }
    .tl-card-info { padding:12px 0; gap:6px; }
}

/* Number + badge row */
.tl-card-meta { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.tl-num       { font-size:20px; font-weight:800; color:var(--text); letter-spacing:-.2px; }
.tl-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 9px; border-radius:20px; font-size:17px; font-weight:700;
    letter-spacing:.5px; text-transform:uppercase;
    background:var(--badge-bg); color:var(--badge-color);
}
.tl-badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }

@media(max-width:600px) {
    .tl-num { font-size:22px; }
    .tl-badge { font-size:14px; padding:2px 8px; letter-spacing:.3px; }
    .tl-badge-dot { width:4px; height:4px; }
}

/* Route line */
.tl-route { display:flex; align-items:center; gap:8px; font-size:20px; }
.tl-route-node {
    display:inline-flex; align-items:center; gap:5px; font-weight:700; color:var(--text);
}
.tl-route-node svg { color:var(--text-sub); flex-shrink:0; }
.tl-route-dash {
    flex:0 0 auto; display:flex; align-items:center; gap:3px; color:var(--text-sub);
}
.tl-route-dash-line { width:40px; border-top:1.5px dashed var(--border); }

@media(max-width:600px) {
    .tl-route { font-size:19px; gap:6px; }
    .tl-route-node { gap:4px; }
    .tl-route-node svg { width:11px; height:11px; }
    .tl-route-dash svg { width:10px; height:10px; }
    .tl-route-dash-line { width:20px; }
}

/* Timeline dates */
.tl-dates { display:flex; gap:14px; flex-wrap:wrap; }
.tl-date  { font-size:19px; color:var(--text-sub); }
.tl-date strong { color:var(--text); font-weight:600; }

@media(max-width:600px) {
    .tl-dates { gap:10px; }
    .tl-date { font-size:17px; }
}

/* Card stats panel */
.tl-card-stats {
    display:flex; align-items:stretch;
    border-left:1px solid var(--border); margin:14px 0;
}
.tl-stat {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:0 18px; gap:2px; border-right:1px solid var(--border);
}
.tl-stat:last-child { border-right:none; }
.tl-stat-v { font-size:35px; font-weight:800; color:var(--text); line-height:1; }
.tl-stat-l { font-size:16px; font-weight:700; letter-spacing:.9px; text-transform:uppercase; color:var(--text-sub); }

/* Card footer actions */
.tl-card-foot {
    display:flex; align-items:center; gap:8px;
    padding:10px 18px; border-top:1px solid var(--border);
    background:var(--surface2);
}
.tl-action {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:var(--rsm,6px); font-size:19px; font-weight:600;
    border:1px solid var(--border); cursor:pointer; text-decoration:none;
    background:var(--surface); color:var(--text); transition:all .15s;
}
.tl-action:hover         { border-color:var(--accent); color:var(--accent); }
.tl-action.primary       { background:var(--accent); color:#fff; border-color:var(--accent); }
.tl-action.primary:hover { opacity:.88; }
.tl-foot-time { margin-left:auto; font-size:17px; color:var(--text-sub); }

/* Empty state */
.tl-empty {
    text-align:center; padding:64px 32px;
    background:var(--surface); border:1px dashed var(--border); border-radius:var(--r);
}
.tl-empty-ico { font-size:64px; margin-bottom:16px; }
.tl-empty h3  { font-size:29px; font-weight:700; color:var(--text); margin:0 0 8px; }
.tl-empty p   { font-size:23px; color:var(--text-sub); margin:0 0 20px; }

/* Pagination */
.tl-pagination { margin-top:4px; }

/* Responsive */
@media(max-width:768px) {
    .tl-new-btn { padding:13px 20px; font-size:22px; }
}

@media(max-width:600px) {
    .tl-wrap { gap:16px; }

    .tl-new-btn { padding:12px 18px; font-size:20px; }

    .tl-filter-btn { padding:10px 14px; font-size:20px; }
    .tl-filter-btn svg:first-child { width:16px; height:16px; }
    .tl-filter-item { padding:9px 14px; font-size:19px; }

    .tl-card-top { flex-direction:column; padding:0 14px; }
    .tl-card-stats { border-left:none; border-top:1px solid var(--border);
                      margin:0 0 8px; flex-wrap:wrap; }
    .tl-stat { padding:10px 14px; flex:1; min-width:80px; }
    .tl-stat-v { font-size:29px; }

    .tl-card-foot { flex-wrap:wrap; gap:6px; padding:10px 14px; }
    .tl-action { flex:1; justify-content:center; font-size:17px; padding:6px 10px; }
    .tl-foot-time { width:100%; text-align:center; margin-left:0; font-size:16px; }

    .tl-empty { padding:48px 24px; }
    .tl-empty-ico { font-size:58px; margin-bottom:12px; }
    .tl-empty h3 { font-size:26px; }
    .tl-empty p { font-size:20px; }
}


/* Mission 2C: Responsive base — applied to all transfer pages */
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


/* 2A - Transfer List Fixes */
@media(max-width:900px) {
    .tl-pipeline { grid-template-columns: repeat(3, 1fr); }
}
@media(max-width:600px) {
    .tl-pipeline { grid-template-columns: repeat(2, 1fr); gap:0; }
    .tl-pipeline-step { padding:10px 12px; }
    .tl-step-num  { font-size:24px; }
    .tl-step-sub  { display:none; }
    .tl-card-top    { flex-direction:column; padding:0 14px; }
    .tl-card-stats  { border-left:none; border-top:1px solid var(--border); margin:0 0 8px; flex-wrap:wrap; }
    .tl-stat        { padding:8px 14px; flex:1; min-width:80px; }
    .tl-bar         { gap:4px; padding:8px 10px; }
    .tl-chip        { padding:4px 10px; font-size:13px; }
    .tl-search      { width:100%; margin-left:0; margin-top:6px; }
    .tl-search input{ width:100%; }
    .tl-route-dash-line { width:20px; }
    .tl-card-foot   { flex-wrap:wrap; gap:6px; }
    .tl-action      { flex:1; justify-content:center; }
    .tl-foot-time   { width:100%; text-align:center; margin-left:0; }
    .tl-page-header         { flex-direction:column; align-items:flex-start; }
    .tl-page-header-left h1 { font-size:24px; }
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
\n
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
</style>

<div class="tl-wrap">

  {{-- ── New Request Button ─────────────────────── --}}
  <a href="{{ route('shop.transfers.request') }}" class="tl-new-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    New Request
  </a>

  {{-- ── Filter Dropdown ──────────────────────────── --}}
  <div class="tl-filter-wrap" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open" class="tl-filter-btn">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
      </svg>
      <span>
        @if($statusFilter === 'all')
          All Transfers
        @elseif($statusFilter === TransferStatus::PENDING->value)
          Pending ({{ $pendingCount }})
        @elseif($statusFilter === TransferStatus::APPROVED->value)
          Approved ({{ $approvedCount }})
        @elseif($statusFilter === TransferStatus::IN_TRANSIT->value)
          In Transit ({{ $inTransitCount }})
        @elseif($statusFilter === TransferStatus::DELIVERED->value)
          Delivered ({{ $deliveredCount }})
        @elseif($statusFilter === TransferStatus::RECEIVED->value)
          Received
        @elseif($statusFilter === TransferStatus::CANCELLED->value)
          Cancelled
        @endif
      </span>
      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" :class="{ 'rotate-180': open }" style="transition:transform 0.2s">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <div class="tl-filter-menu" :class="{ 'open': open }">
      <button wire:click="$set('statusFilter','all')" @click="open = false" class="tl-filter-item {{ $statusFilter==='all'?'active':'' }}">
        All Transfers
      </button>
      <button wire:click="$set('statusFilter','{{ TransferStatus::PENDING->value }}')" @click="open = false" class="tl-filter-item {{ $statusFilter===TransferStatus::PENDING->value?'active':'' }}">
        <span class="tl-filter-dot" style="background:#d97706"></span>
        Pending
        @if($pendingCount>0)<span class="tl-filter-count">{{ $pendingCount }}</span>@endif
      </button>
      <button wire:click="$set('statusFilter','{{ TransferStatus::APPROVED->value }}')" @click="open = false" class="tl-filter-item {{ $statusFilter===TransferStatus::APPROVED->value?'active':'' }}">
        <span class="tl-filter-dot" style="background:var(--accent)"></span>
        Approved
        @if($approvedCount>0)<span class="tl-filter-count">{{ $approvedCount }}</span>@endif
      </button>
      <button wire:click="$set('statusFilter','{{ TransferStatus::IN_TRANSIT->value }}')" @click="open = false" class="tl-filter-item {{ $statusFilter===TransferStatus::IN_TRANSIT->value?'active':'' }}">
        <span class="tl-filter-dot" style="background:var(--violet)"></span>
        In Transit
        @if($inTransitCount>0)<span class="tl-filter-count">{{ $inTransitCount }}</span>@endif
      </button>
      <button wire:click="$set('statusFilter','{{ TransferStatus::DELIVERED->value }}')" @click="open = false" class="tl-filter-item {{ $statusFilter===TransferStatus::DELIVERED->value?'active':'' }}">
        <span class="tl-filter-dot" style="background:#0ea5e9"></span>
        Delivered
        @if($deliveredCount>0)<span class="tl-filter-count">{{ $deliveredCount }}</span>@endif
      </button>
      <button wire:click="$set('statusFilter','{{ TransferStatus::RECEIVED->value }}')" @click="open = false" class="tl-filter-item {{ $statusFilter===TransferStatus::RECEIVED->value?'active':'' }}">
        <span class="tl-filter-dot" style="background:var(--green)"></span>
        Received
      </button>
      <button wire:click="$set('statusFilter','{{ TransferStatus::CANCELLED->value }}')" @click="open = false" class="tl-filter-item {{ $statusFilter===TransferStatus::CANCELLED->value?'active':'' }}">
        <span class="tl-filter-dot" style="background:#6b7280"></span>
        Cancelled
      </button>
    </div>
  </div>

  {{-- ── Transfer Cards ──────────────────────────── --}}
  <div class="tl-list">
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
    <div class="tl-card" style="--card-color:{{ $sc['color'] }}">
      <div class="tl-card-stripe"></div>

      <div class="tl-card-top">
        {{-- ── Core info ── --}}
        <div class="tl-card-info">

          {{-- Number + status --}}
          <div class="tl-card-meta">
            <span class="tl-num">{{ $transfer->transfer_number }}</span>
            <span class="tl-badge" style="--badge-bg:{{ $sc['bg'] }};--badge-color:{{ $sc['color'] }}">
              <span class="tl-badge-dot"></span>
              {{ $transfer->status->label() }}
            </span>
          </div>

          {{-- Route --}}
          <div class="tl-route">
            <div class="tl-route-node">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              {{ $transfer->fromWarehouse->name ?? '—' }}
            </div>
            <div class="tl-route-dash">
              <div class="tl-route-dash-line"></div>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $sc['color'] }}"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
            <div class="tl-route-node">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              Your Shop
            </div>
          </div>

          {{-- Timeline dates --}}
          <div class="tl-dates">
            <span class="tl-date">
              Requested: <strong>{{ $transfer->requested_at?->format('d M Y') ?? '—' }}</strong>
            </span>
            @if($transfer->requestedBy)
            <span class="tl-date">
              By: <strong>{{ $transfer->requestedBy->name }}</strong>
            </span>
            @endif
            @if($transfer->delivered_at)
            <span class="tl-date">
              Delivered: <strong>{{ $transfer->delivered_at->format('d M Y') }}</strong>
            </span>
            @endif
            @if($transfer->received_at)
            <span class="tl-date">
              Received: <strong>{{ $transfer->received_at->format('d M Y') }}</strong>
            </span>
            @endif
          </div>
        </div>

        {{-- ── Stats panel ── --}}
        <div class="tl-card-stats">
          <div class="tl-stat">
            <div class="tl-stat-v">{{ $transfer->items->count() }}</div>
            <div class="tl-stat-l">Products</div>
          </div>
          <div class="tl-stat">
            <div class="tl-stat-v">{{ $itemsReq }}</div>
            <div class="tl-stat-l">Boxes Req.</div>
          </div>
        </div>
      </div>

      {{-- ── Footer actions ── --}}
      <div class="tl-card-foot">
        <a href="{{ route('shop.transfers.show', $transfer) }}" class="tl-action">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          View Details
        </a>

        @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED]))
          <a href="{{ route('shop.transfers.receive', $transfer) }}" class="tl-action primary">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            Scan &amp; Receive
          </a>
        @endif

        <span class="tl-foot-time">{{ $transfer->created_at->diffForHumans() }}</span>
      </div>
    </div>
    @empty
    <div class="tl-empty">
      <div class="tl-empty-ico">📦</div>
      <h3>No transfers found</h3>
      <p>{{ $statusFilter==='all' ? 'Your shop has no transfer history yet.' : 'No transfers match the selected filter.' }}</p>
      <a href="{{ route('shop.transfers.request') }}" class="tl-new-btn" style="display:inline-flex">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Request
      </a>
    </div>
    @endforelse
  </div>

  {{-- ── Pagination ──────────────────────────────── --}}
  @if($transfers->hasPages())
    <div class="tl-pagination">{{ $transfers->links() }}</div>
  @endif

</div>
</div>
