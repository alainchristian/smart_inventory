@php use App\Enums\TransferStatus; @endphp
<div>
<style>
/* ─── Transfer List Styles ───────────────────────────── */
.tl-wrap { display:flex; flex-direction:column; gap:20px; }

/* ── Page header */
.tl-page-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.tl-page-header-left h1 { font-size:29px; font-weight:800; letter-spacing:-.5px; color:var(--text); margin:0 0 3px; }
.tl-page-header-left p  { font-size:16px; color:var(--text-sub); margin:0; }

.tl-new-btn {
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 20px; border-radius:var(--r); border:none; cursor:pointer;
    background:var(--accent); color:#fff; font-size:16px; font-weight:700;
    letter-spacing:.3px; text-decoration:none; transition:opacity .15s, transform .1s;
    white-space:nowrap;
}
.tl-new-btn:hover { opacity:.88; transform:translateY(-1px); }
.tl-new-btn svg   { flex-shrink:0; }

/* ── Pipeline strip */
.tl-pipeline {
    display:grid; grid-template-columns:repeat(4, 1fr); gap:0;
    background:var(--surface); border:1px solid var(--border); border-radius:var(--r);
    overflow:hidden;
}
.tl-pipeline-step {
    padding:14px 16px; display:flex; flex-direction:column; gap:4px;
    border-right:1px solid var(--border); position:relative; cursor:pointer;
    transition:background .15s; background:transparent;
}
.tl-pipeline-step:last-child { border-right:none; }
.tl-pipeline-step:hover      { background:var(--surface2); }
.tl-pipeline-step.active     { background:var(--step-bg); }
.tl-pipeline-step.active::after {
    content:''; position:absolute; bottom:0; left:0; right:0; height:3px;
    background:var(--step-color);
}
.tl-step-num   { font-size:26px; font-weight:800; color:var(--step-color,var(--text-sub)); line-height:1; }
.tl-step-label { font-size:12px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:var(--text-sub); }
.tl-step-sub   { font-size:13px; color:var(--text-sub); }

/* ── Filter / search bar */
.tl-bar {
    display:flex; align-items:center; gap:6px; flex-wrap:wrap;
    background:var(--surface); border:1px solid var(--border); border-radius:var(--r);
    padding:10px 14px;
}
.tl-bar-label { font-size:12px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:var(--text-sub); padding-right:6px; }
.tl-chip {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:20px; font-size:14px; font-weight:600;
    border:1.5px solid transparent; cursor:pointer; transition:all .15s;
    background:transparent; color:var(--text-sub);
}
.tl-chip:hover  { background:var(--surface2); color:var(--text); }
.tl-chip.active { background:var(--chip-bg,var(--accent)); color:#fff; }
.tl-chip-ct {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:18px; height:18px; padding:0 4px; border-radius:10px;
    background:rgba(255,255,255,.25); font-size:12px; font-weight:800;
}
.tl-search { margin-left:auto; position:relative; }
.tl-search input {
    padding:6px 10px 6px 32px; border-radius:var(--rsm,6px);
    border:1px solid var(--border); background:var(--surface2);
    color:var(--text); font-size:14px; width:200px; outline:none;
    transition:border-color .15s;
}
.tl-search input:focus { border-color:var(--accent); }
.tl-search-ico {
    position:absolute; left:9px; top:50%; transform:translateY(-50%);
    color:var(--text-sub); pointer-events:none;
}

/* ── Transfer cards */
.tl-list { display:flex; flex-direction:column; gap:10px; }

.tl-card {
    background:var(--surface); border:1px solid var(--border); border-radius:var(--r);
    overflow:hidden; transition:border-color .2s, box-shadow .18s; position:relative;
}
.tl-card:hover { border-color:var(--card-color,var(--accent)); box-shadow:0 4px 20px rgba(0,0,0,.08); }
.tl-card-stripe {
    position:absolute; top:0; left:0; bottom:0; width:4px;
    background:var(--card-color,var(--accent));
}

/* Card top row */
.tl-card-top {
    display:flex; align-items:stretch; gap:0;
    padding:0 18px 0 22px; min-height:88px;
}
.tl-card-info { flex:1; display:flex; flex-direction:column; justify-content:center; gap:5px; padding:14px 0; }

/* Number + badge row */
.tl-card-meta { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.tl-num       { font-size:17px; font-weight:800; color:var(--text); letter-spacing:-.2px; }
.tl-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 9px; border-radius:20px; font-size:12px; font-weight:700;
    letter-spacing:.5px; text-transform:uppercase;
    background:var(--badge-bg); color:var(--badge-color);
}
.tl-badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }

/* Route line */
.tl-route { display:flex; align-items:center; gap:8px; font-size:14px; }
.tl-route-node {
    display:inline-flex; align-items:center; gap:5px; font-weight:700; color:var(--text);
}
.tl-route-node svg { color:var(--text-sub); flex-shrink:0; }
.tl-route-dash {
    flex:0 0 auto; display:flex; align-items:center; gap:3px; color:var(--text-sub);
}
.tl-route-dash-line { width:40px; border-top:1.5px dashed var(--border); }

/* Timeline dates */
.tl-dates { display:flex; gap:14px; flex-wrap:wrap; }
.tl-date  { font-size:13px; color:var(--text-sub); }
.tl-date strong { color:var(--text); font-weight:600; }

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
.tl-stat-v { font-size:24px; font-weight:800; color:var(--text); line-height:1; }
.tl-stat-l { font-size:11px; font-weight:700; letter-spacing:.9px; text-transform:uppercase; color:var(--text-sub); }

/* Card footer actions */
.tl-card-foot {
    display:flex; align-items:center; gap:8px;
    padding:10px 18px; border-top:1px solid var(--border);
    background:var(--surface2);
}
.tl-action {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:var(--rsm,6px); font-size:13px; font-weight:600;
    border:1px solid var(--border); cursor:pointer; text-decoration:none;
    background:var(--surface); color:var(--text); transition:all .15s;
}
.tl-action:hover         { border-color:var(--accent); color:var(--accent); }
.tl-action.primary       { background:var(--accent); color:#fff; border-color:var(--accent); }
.tl-action.primary:hover { opacity:.88; }
.tl-foot-time { margin-left:auto; font-size:12px; color:var(--text-sub); }

/* Empty state */
.tl-empty {
    text-align:center; padding:64px 32px;
    background:var(--surface); border:1px dashed var(--border); border-radius:var(--r);
}
.tl-empty-ico { font-size:53px; margin-bottom:16px; }
.tl-empty h3  { font-size:20px; font-weight:700; color:var(--text); margin:0 0 8px; }
.tl-empty p   { font-size:16px; color:var(--text-sub); margin:0 0 20px; }

/* Pagination */
.tl-pagination { margin-top:4px; }

/* Mission 2A */
@media(max-width:900px) {
    .tl-pipeline { grid-template-columns: repeat(3, 1fr); }
}
@media(max-width:600px) {
    .tl-pipeline { grid-template-columns: repeat(2, 1fr); gap:0; }
    .tl-pipeline-step { padding:10px 12px; }
    .tl-step-num  { font-size:24px; } /* It was 20px in instructions, but we already scaled fonts. Let's just use what instruction said and it will scale? Actually let's use exact code */
    .tl-step-sub  { display:none; }
    
    .tl-card-top    { flex-direction:column; padding:0 14px; }
    .tl-card-stats  { border-left:none; border-top:1px solid var(--border);
                      margin:0 0 8px; flex-wrap:wrap; }
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
    .tl-page-header-left h1 { font-size:24px; }
    .tl-new-btn             { width:100%; justify-content:center; }
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

</style>

<div class="tl-wrap">

  {{-- ── Page Header ─────────────────────────────── --}}
  <div class="tl-page-header">
    <div class="tl-page-header-left">
      <h1>Transfer Requests</h1>
      <p>Track all stock movements from warehouse to your shop</p>
    </div>
    <a href="{{ route('shop.transfers.request') }}" class="tl-new-btn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Request
    </a>
  </div>

  {{-- ── Pipeline Overview ───────────────────────── --}}
  <div class="tl-pipeline">
    <button class="tl-pipeline-step {{ $statusFilter===TransferStatus::PENDING->value?'active':'' }}"
            style="--step-color:#d97706;--step-bg:rgba(217,119,6,.06)"
            wire:click="$set('statusFilter','{{ TransferStatus::PENDING->value }}')">
      <div class="tl-step-num" style="color:{{ $statusFilter===TransferStatus::PENDING->value?'#d97706':'var(--text-sub)' }}">{{ $pendingCount }}</div>
      <div class="tl-step-label">Pending</div>
      <div class="tl-step-sub">Awaiting review</div>
    </button>
    <button class="tl-pipeline-step {{ $statusFilter===TransferStatus::APPROVED->value?'active':'' }}"
            style="--step-color:var(--accent);--step-bg:rgba(99,102,241,.06)"
            wire:click="$set('statusFilter','{{ TransferStatus::APPROVED->value }}')">
      <div class="tl-step-num" style="color:{{ $statusFilter===TransferStatus::APPROVED->value?'var(--accent)':'var(--text-sub)' }}">{{ $approvedCount }}</div>
      <div class="tl-step-label">Approved</div>
      <div class="tl-step-sub">Ready to pack</div>
    </button>
    <button class="tl-pipeline-step {{ $statusFilter===TransferStatus::IN_TRANSIT->value?'active':'' }}"
            style="--step-color:var(--violet);--step-bg:rgba(139,92,246,.06)"
            wire:click="$set('statusFilter','{{ TransferStatus::IN_TRANSIT->value }}')">
      <div class="tl-step-num" style="color:{{ $statusFilter===TransferStatus::IN_TRANSIT->value?'var(--violet)':'var(--text-sub)' }}">{{ $inTransitCount }}</div>
      <div class="tl-step-label">In Transit</div>
      <div class="tl-step-sub">On the road</div>
    </button>
    <button class="tl-pipeline-step {{ $statusFilter===TransferStatus::DELIVERED->value?'active':'' }}"
            style="--step-color:#0ea5e9;--step-bg:rgba(14,165,233,.06)"
            wire:click="$set('statusFilter','{{ TransferStatus::DELIVERED->value }}')">
      <div class="tl-step-num" style="color:{{ $statusFilter===TransferStatus::DELIVERED->value?'#0ea5e9':'var(--text-sub)' }}">{{ $deliveredCount }}</div>
      <div class="tl-step-label">Delivered</div>
      <div class="tl-step-sub">Pending scan-in</div>
    </button>
  </div>

  {{-- ── Filter Bar ──────────────────────────────── --}}
  <div class="tl-bar">
    <span class="tl-bar-label">Status</span>
    <button wire:click="$set('statusFilter','all')"
            class="tl-chip {{ $statusFilter==='all'?'active':'' }}">All</button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::PENDING->value }}')"
            class="tl-chip {{ $statusFilter===TransferStatus::PENDING->value?'active':'' }}"
            style="--chip-bg:#d97706">
      Pending @if($pendingCount>0)<span class="tl-chip-ct">{{ $pendingCount }}</span>@endif
    </button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::APPROVED->value }}')"
            class="tl-chip {{ $statusFilter===TransferStatus::APPROVED->value?'active':'' }}"
            style="--chip-bg:var(--accent)">
      Approved @if($approvedCount>0)<span class="tl-chip-ct">{{ $approvedCount }}</span>@endif
    </button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::IN_TRANSIT->value }}')"
            class="tl-chip {{ $statusFilter===TransferStatus::IN_TRANSIT->value?'active':'' }}"
            style="--chip-bg:var(--violet)">
      In Transit @if($inTransitCount>0)<span class="tl-chip-ct">{{ $inTransitCount }}</span>@endif
    </button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::DELIVERED->value }}')"
            class="tl-chip {{ $statusFilter===TransferStatus::DELIVERED->value?'active':'' }}"
            style="--chip-bg:#0ea5e9">
      Delivered @if($deliveredCount>0)<span class="tl-chip-ct">{{ $deliveredCount }}</span>@endif
    </button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::RECEIVED->value }}')"
            class="tl-chip {{ $statusFilter===TransferStatus::RECEIVED->value?'active':'' }}"
            style="--chip-bg:var(--green)">Received</button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::CANCELLED->value }}')"
            class="tl-chip {{ $statusFilter===TransferStatus::CANCELLED->value?'active':'' }}"
            style="--chip-bg:#6b7280">Cancelled</button>
    @isset($search)
    <div class="tl-search">
      <svg class="tl-search-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search transfers…">
    </div>
    @endisset
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
      $itemsReq = $transfer->items->sum('quantity_requested');
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
            <div class="tl-stat-l">Units Req.</div>
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
