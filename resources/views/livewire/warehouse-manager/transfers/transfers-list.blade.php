@php use App\Enums\TransferStatus; @endphp
<div>
<style>
/* ─── Warehouse Transfer List Styles (shared namespace wtl) ─── */
.wtl-wrap { display:flex; flex-direction:column; gap:20px; }

/* ── Page header */
.wtl-page-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.wtl-page-header-left h1 { font-size:24px; font-weight:800; letter-spacing:-.5px; color:var(--text); margin:0 0 3px; }
.wtl-page-header-left p  { font-size:13px; color:var(--text-sub); margin:0; }

/* ── Pipeline strip */
.wtl-pipeline {
    display:grid; grid-template-columns:repeat(3, 1fr); gap:0;
    background:var(--surface); border:1px solid var(--border); border-radius:var(--r);
    overflow:hidden;
}
.wtl-pipeline-step {
    padding:14px 16px; display:flex; flex-direction:column; gap:4px;
    border-right:1px solid var(--border); position:relative; cursor:pointer;
    transition:background .15s; background:transparent;
}
.wtl-pipeline-step:last-child { border-right:none; }
.wtl-pipeline-step:hover      { background:var(--surface2); }
.wtl-pipeline-step.active     { background:var(--step-bg); }
.wtl-pipeline-step.active::after {
    content:''; position:absolute; bottom:0; left:0; right:0; height:3px;
    background:var(--step-color);
}
.wtl-step-num   { font-size:22px; font-weight:800; line-height:1; }
.wtl-step-label { font-size:10px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:var(--text-sub); }
.wtl-step-sub   { font-size:11px; color:var(--text-sub); }

/* ── Filter / search bar */
.wtl-bar {
    display:flex; align-items:center; gap:6px; flex-wrap:wrap;
    background:var(--surface); border:1px solid var(--border); border-radius:var(--r);
    padding:10px 14px;
}
.wtl-bar-label { font-size:10px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:var(--text-sub); padding-right:6px; }
.wtl-chip {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600;
    border:1.5px solid transparent; cursor:pointer; transition:all .15s;
    background:transparent; color:var(--text-sub);
}
.wtl-chip:hover  { background:var(--surface2); color:var(--text); }
.wtl-chip.active { background:var(--chip-bg,var(--accent)); color:#fff; }
.wtl-chip-ct {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:18px; height:18px; padding:0 4px; border-radius:10px;
    background:rgba(255,255,255,.25); font-size:10px; font-weight:800;
}

/* ── Transfer cards */
.wtl-list { display:flex; flex-direction:column; gap:10px; }

.wtl-card {
    background:var(--surface); border:1px solid var(--border); border-radius:var(--r);
    overflow:hidden; transition:border-color .2s, box-shadow .18s; position:relative;
}
.wtl-card:hover { border-color:var(--card-color,var(--accent)); box-shadow:0 4px 20px rgba(0,0,0,.08); }
.wtl-card-stripe {
    position:absolute; top:0; left:0; bottom:0; width:4px;
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
.wtl-num       { font-size:14px; font-weight:800; color:var(--text); letter-spacing:-.2px; }
.wtl-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 9px; border-radius:20px; font-size:10px; font-weight:700;
    letter-spacing:.5px; text-transform:uppercase;
    background:var(--badge-bg); color:var(--badge-color);
}
.wtl-badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }

/* Urgency flag */
.wtl-urgent {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 9px; border-radius:20px; font-size:10px; font-weight:700;
    background:rgba(217,119,6,.12); color:#d97706;
}

/* Route */
.wtl-route { display:flex; align-items:center; gap:8px; font-size:12px; }
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
.wtl-date  { font-size:11px; color:var(--text-sub); }
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
.wtl-stat-v { font-size:20px; font-weight:800; color:var(--text); line-height:1; }
.wtl-stat-l { font-size:9px; font-weight:700; letter-spacing:.9px; text-transform:uppercase; color:var(--text-sub); }

/* Footer */
.wtl-card-foot {
    display:flex; align-items:center; gap:8px;
    padding:10px 18px; border-top:1px solid var(--border);
    background:var(--surface2);
}
.wtl-action {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:var(--rsm,6px); font-size:11px; font-weight:600;
    border:1px solid var(--border); cursor:pointer; text-decoration:none;
    background:var(--surface); color:var(--text); transition:all .15s;
}
.wtl-action:hover         { border-color:var(--accent); color:var(--accent); }
.wtl-action.primary       { background:var(--accent); color:#fff; border-color:var(--accent); }
.wtl-action.primary:hover { opacity:.88; }
.wtl-action.warn          { background:#d97706; color:#fff; border-color:#d97706; }
.wtl-action.warn:hover    { opacity:.88; }
.wtl-foot-time { margin-left:auto; font-size:10px; color:var(--text-sub); }

/* Empty state */
.wtl-empty {
    text-align:center; padding:64px 32px;
    background:var(--surface); border:1px dashed var(--border); border-radius:var(--r);
}
.wtl-empty-ico { font-size:44px; margin-bottom:16px; }
.wtl-empty h3  { font-size:17px; font-weight:700; color:var(--text); margin:0 0 8px; }
.wtl-empty p   { font-size:13px; color:var(--text-sub); margin:0; }

/* Pagination */
.wtl-pagination { margin-top:4px; }
</style>

<div class="wtl-wrap">

  {{-- ── Page Header ─────────────────────────────── --}}
  <div class="wtl-page-header">
    <div class="wtl-page-header-left">
      <h1>Outbound Transfers</h1>
      <p>Review, pack, and dispatch stock from your warehouse to shops</p>
    </div>
  </div>

  {{-- ── Pipeline Overview ───────────────────────── --}}
  <div class="wtl-pipeline">
    <button class="wtl-pipeline-step {{ $statusFilter===TransferStatus::PENDING->value?'active':'' }}"
            style="--step-color:#d97706;--step-bg:rgba(217,119,6,.06)"
            wire:click="$set('statusFilter','{{ TransferStatus::PENDING->value }}')">
      <div class="wtl-step-num" style="color:{{ $statusFilter===TransferStatus::PENDING->value?'#d97706':'var(--text-sub)' }}">{{ $pendingCount }}</div>
      <div class="wtl-step-label">Pending</div>
      <div class="wtl-step-sub">Awaiting your review</div>
    </button>
    <button class="wtl-pipeline-step {{ $statusFilter===TransferStatus::APPROVED->value?'active':'' }}"
            style="--step-color:var(--accent);--step-bg:rgba(99,102,241,.06)"
            wire:click="$set('statusFilter','{{ TransferStatus::APPROVED->value }}')">
      <div class="wtl-step-num" style="color:{{ $statusFilter===TransferStatus::APPROVED->value?'var(--accent)':'var(--text-sub)' }}">{{ $approvedCount }}</div>
      <div class="wtl-step-label">Approved</div>
      <div class="wtl-step-sub">Ready to pack</div>
    </button>
    <button class="wtl-pipeline-step {{ $statusFilter===TransferStatus::IN_TRANSIT->value?'active':'' }}"
            style="--step-color:var(--violet);--step-bg:rgba(139,92,246,.06)"
            wire:click="$set('statusFilter','{{ TransferStatus::IN_TRANSIT->value }}')">
      <div class="wtl-step-num" style="color:{{ $statusFilter===TransferStatus::IN_TRANSIT->value?'var(--violet)':'var(--text-sub)' }}">{{ $inTransitCount }}</div>
      <div class="wtl-step-label">In Transit</div>
      <div class="wtl-step-sub">Dispatched to shop</div>
    </button>
  </div>

  {{-- ── Filter Bar ──────────────────────────────── --}}
  <div class="wtl-bar">
    <span class="wtl-bar-label">Status</span>
    <button wire:click="$set('statusFilter','all')"
            class="wtl-chip {{ $statusFilter==='all'?'active':'' }}">All</button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::PENDING->value }}')"
            class="wtl-chip {{ $statusFilter===TransferStatus::PENDING->value?'active':'' }}"
            style="--chip-bg:#d97706">
      Pending @if($pendingCount>0)<span class="wtl-chip-ct">{{ $pendingCount }}</span>@endif
    </button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::APPROVED->value }}')"
            class="wtl-chip {{ $statusFilter===TransferStatus::APPROVED->value?'active':'' }}"
            style="--chip-bg:var(--accent)">
      Approved @if($approvedCount>0)<span class="wtl-chip-ct">{{ $approvedCount }}</span>@endif
    </button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::IN_TRANSIT->value }}')"
            class="wtl-chip {{ $statusFilter===TransferStatus::IN_TRANSIT->value?'active':'' }}"
            style="--chip-bg:var(--violet)">
      In Transit @if($inTransitCount>0)<span class="wtl-chip-ct">{{ $inTransitCount }}</span>@endif
    </button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::DELIVERED->value }}')"
            class="wtl-chip {{ $statusFilter===TransferStatus::DELIVERED->value?'active':'' }}"
            style="--chip-bg:#0ea5e9">Delivered</button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::RECEIVED->value }}')"
            class="wtl-chip {{ $statusFilter===TransferStatus::RECEIVED->value?'active':'' }}"
            style="--chip-bg:var(--green)">Received</button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::REJECTED->value }}')"
            class="wtl-chip {{ $statusFilter===TransferStatus::REJECTED->value?'active':'' }}"
            style="--chip-bg:#ef4444">Rejected</button>
    <button wire:click="$set('statusFilter','{{ TransferStatus::CANCELLED->value }}')"
            class="wtl-chip {{ $statusFilter===TransferStatus::CANCELLED->value?'active':'' }}"
            style="--chip-bg:#6b7280">Cancelled</button>
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
      $itemsReq = $transfer->items->sum('quantity_requested');
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
            <div class="wtl-stat-l">Units Req.</div>
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
