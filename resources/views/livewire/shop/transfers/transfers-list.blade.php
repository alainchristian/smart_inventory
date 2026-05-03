@php use App\Enums\TransferStatus; @endphp
<div wire:poll.10s>
<style>
/* ── Filter tabs ────────────────────────────────────────── */
.tl-tabs {
    display:flex; align-items:center; gap:2px;
    background:var(--surface); border:1px solid var(--border);
    border-radius:9px; padding:3px; overflow-x:auto; scrollbar-width:none;
}
.tl-tabs::-webkit-scrollbar { display:none; }
.tl-tab {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px; border-radius:6px; border:none;
    font-size:12px; font-weight:500; color:var(--text-dim);
    background:transparent; cursor:pointer; white-space:nowrap;
    transition:all .15s; line-height:1.4; flex-shrink:0;
}
.tl-tab:hover  { color:var(--text); background:rgba(0,0,0,.04); }
.tl-tab.active { background:var(--accent); color:#fff; font-weight:600; }
.tl-tab-badge {
    font-size:10px; font-weight:700; padding:1px 6px;
    border-radius:10px; background:rgba(255,255,255,.25); color:inherit;
    line-height:1.5;
}
.tl-tab:not(.active) .tl-tab-badge {
    background:var(--surface2); color:var(--text-dim);
}

/* ── Transfer list ──────────────────────────────────────── */
.tl-list { display: flex; flex-direction: column; gap: 8px; }

/* ── Transfer card ──────────────────────────────────────── */
.tl-card {
    background: #fff; border: 1px solid var(--border);
    border-radius: 12px; overflow: hidden;
    transition: box-shadow .18s, border-color .18s; position: relative;
}
.tl-card:hover { border-color: var(--card-accent, var(--accent)); box-shadow: 0 2px 12px rgba(0,0,0,.07); }
.tl-stripe {
    position: absolute; top: 0; left: 0; bottom: 0; width: 3px;
    background: var(--card-accent, var(--accent));
}

/* ── Card body ──────────────────────────────────────────── */
.tl-body { display: flex; align-items: stretch; padding: 12px 14px 12px 18px; gap: 12px; min-height: 76px; }
.tl-info { flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 5px; min-width: 0; }

.tl-meta   { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.tl-num    { font-size: 13px; font-weight: 800; color: var(--text); font-family: var(--mono); letter-spacing: -.2px; }
.tl-badge  {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700;
    letter-spacing: .4px; text-transform: uppercase;
    background: var(--badge-bg); color: var(--badge-c);
}

.tl-route { display: flex; align-items: center; gap: 6px; }
.tl-rnode { font-size: 12px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
.tl-rline { flex: 0 0 24px; border-top: 1.5px dashed var(--border); }

.tl-dates { display: flex; gap: 10px; flex-wrap: wrap; }
.tl-date  { font-size: 11px; color: var(--text-dim); }
.tl-date strong { color: var(--text); font-weight: 600; }

/* ── Stats ──────────────────────────────────────────────── */
.tl-stats {
    display: flex; align-items: stretch;
    border-left: 1px solid var(--border); flex-shrink: 0;
}
.tl-stat {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 0 16px; gap: 1px;
    border-right: 1px solid var(--border);
}
.tl-stat:last-child { border-right: none; }
.tl-stat-v { font-size: 18px; font-weight: 800; color: var(--text); line-height: 1; }
.tl-stat-l { font-size: 9px; font-weight: 700; letter-spacing: .7px; text-transform: uppercase; color: var(--text-dim); }

/* ── Footer ─────────────────────────────────────────────── */
.tl-foot {
    display: flex; align-items: center; gap: 7px;
    padding: 8px 14px; border-top: 1px solid var(--border);
}
.tl-action {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 7px; font-size: 12px; font-weight: 600;
    border: 1px solid var(--border); cursor: pointer; text-decoration: none;
    background: #fff; color: var(--text); transition: all .15s;
}
.tl-action:hover         { border-color: var(--accent); color: var(--accent); }
.tl-action.primary       { background: var(--accent); color: #fff; border-color: var(--accent); }
.tl-action.primary:hover { opacity: .88; }
.tl-foot-time { margin-left: auto; font-size: 11px; color: var(--text-dim); }

/* ── Empty state ────────────────────────────────────────── */
.tl-empty {
    text-align: center; padding: 48px 24px;
    background: #fff; border: 1px solid var(--border); border-radius: 12px;
}
.tl-empty h3 { font-size: 15px; font-weight: 700; color: var(--text); margin: 12px 0 4px; }
.tl-empty p  { font-size: 12px; color: var(--text-dim); margin: 0 0 16px; }

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 640px) {
    .tl-body   { padding: 11px 12px 11px 16px; }
    .tl-stats  { display: none; }   /* hide stats panel on mobile — info is in the body */
}
@media (max-width: 480px) {
    .tl-route  { flex-wrap: wrap; }
    .tl-rnode  { max-width: 120px; }
    .tl-foot   { flex-wrap: wrap; gap: 6px; }
    .tl-action { flex: 1; justify-content: center; }
    .tl-foot-time { width: 100%; text-align: center; margin-left: 0; }
}
</style>

{{-- Status filter tabs --}}
<div class="tl-tabs" style="margin-bottom:14px;">
  <button wire:click="$set('statusFilter','all')"
          class="tl-tab {{ $statusFilter==='all' ? 'active' : '' }}">
    All
  </button>
  <button wire:click="$set('statusFilter','{{ TransferStatus::PENDING->value }}')"
          class="tl-tab {{ $statusFilter===TransferStatus::PENDING->value ? 'active' : '' }}">
    Pending
    @if($pendingCount > 0)<span class="tl-tab-badge">{{ $pendingCount }}</span>@endif
  </button>
  <button wire:click="$set('statusFilter','{{ TransferStatus::APPROVED->value }}')"
          class="tl-tab {{ $statusFilter===TransferStatus::APPROVED->value ? 'active' : '' }}">
    Approved
    @if($approvedCount > 0)<span class="tl-tab-badge">{{ $approvedCount }}</span>@endif
  </button>
  <button wire:click="$set('statusFilter','{{ TransferStatus::IN_TRANSIT->value }}')"
          class="tl-tab {{ $statusFilter===TransferStatus::IN_TRANSIT->value ? 'active' : '' }}">
    In Transit
    @if($inTransitCount > 0)<span class="tl-tab-badge">{{ $inTransitCount }}</span>@endif
  </button>
  <button wire:click="$set('statusFilter','{{ TransferStatus::DELIVERED->value }}')"
          class="tl-tab {{ $statusFilter===TransferStatus::DELIVERED->value ? 'active' : '' }}">
    Delivered
    @if($deliveredCount > 0)<span class="tl-tab-badge">{{ $deliveredCount }}</span>@endif
  </button>
  <button wire:click="$set('statusFilter','{{ TransferStatus::RECEIVED->value }}')"
          class="tl-tab {{ $statusFilter===TransferStatus::RECEIVED->value ? 'active' : '' }}">
    Received
  </button>
  <button wire:click="$set('statusFilter','{{ TransferStatus::CANCELLED->value }}')"
          class="tl-tab {{ $statusFilter===TransferStatus::CANCELLED->value ? 'active' : '' }}">
    Cancelled
  </button>
</div>

{{-- Transfer cards --}}
<div class="tl-list">
  @forelse($transfers as $transfer)
  @php
    $sc = match($transfer->status->value) {
      'pending'    => ['c'=>'#d97706',        'bg'=>'rgba(217,119,6,.1)'],
      'approved'   => ['c'=>'var(--accent)',  'bg'=>'rgba(99,102,241,.1)'],
      'in_transit' => ['c'=>'#8b5cf6',        'bg'=>'rgba(139,92,246,.1)'],
      'delivered'  => ['c'=>'#0ea5e9',        'bg'=>'rgba(14,165,233,.1)'],
      'received'   => ['c'=>'var(--green)',   'bg'=>'rgba(16,185,129,.1)'],
      'rejected'   => ['c'=>'var(--red)',     'bg'=>'var(--red-dim)'],
      default      => ['c'=>'#6b7280',        'bg'=>'rgba(107,114,128,.08)'],
    };
    $boxCount = $transfer->items->sum(fn ($item) => (int) $item->quantity_requested);
  @endphp
  <div class="tl-card" style="--card-accent:{{ $sc['c'] }};">
    <div class="tl-stripe"></div>

    <div class="tl-body">
      <div class="tl-info">

        {{-- Number + status --}}
        <div class="tl-meta">
          <span class="tl-num">{{ $transfer->transfer_number }}</span>
          <span class="tl-badge" style="--badge-bg:{{ $sc['bg'] }};--badge-c:{{ $sc['c'] }};">
            <span class="tl-dot"></span>
            {{ $transfer->status->label() }}
          </span>
          @if($transfer->has_discrepancy)
            <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;
                         background:var(--red-dim);color:var(--red);">Discrepancy</span>
          @endif
        </div>

        {{-- Route --}}
        <div class="tl-route">
          <span class="tl-rnode">{{ $transfer->fromWarehouse->name ?? '—' }}</span>
          <div class="tl-rline"></div>
          <svg width="10" height="10" viewBox="0 0 24 24" fill="{{ $sc['c'] }}" style="flex-shrink:0;">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
          <span class="tl-rnode">Your Shop</span>
        </div>

        {{-- Dates --}}
        <div class="tl-dates">
          <span class="tl-date">Requested: <strong>{{ $transfer->requested_at?->format('d M Y') ?? '—' }}</strong></span>
          @if($transfer->requestedBy)
            <span class="tl-date">By: <strong>{{ $transfer->requestedBy->name }}</strong></span>
          @endif
          @if($transfer->delivered_at)
            <span class="tl-date">Delivered: <strong>{{ $transfer->delivered_at->format('d M Y') }}</strong></span>
          @endif
          @if($transfer->received_at)
            <span class="tl-date">Received: <strong>{{ $transfer->received_at->format('d M Y') }}</strong></span>
          @endif
        </div>

      </div>

      {{-- Stats --}}
      <div class="tl-stats">
        <div class="tl-stat">
          <div class="tl-stat-v">{{ $transfer->items->count() }}</div>
          <div class="tl-stat-l">Products</div>
        </div>
        <div class="tl-stat">
          <div class="tl-stat-v">{{ $boxCount }}</div>
          <div class="tl-stat-l">Boxes</div>
        </div>
      </div>
    </div>

    {{-- Footer --}}
    <div class="tl-foot">
      <a href="{{ route('shop.transfers.show', $transfer) }}" class="tl-action">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        View
      </a>
      @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED]))
        <a href="{{ route('shop.transfers.receive', $transfer) }}" class="tl-action primary">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="9 11 12 14 22 4"/>
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
          </svg>
          Scan &amp; Receive
        </a>
      @endif
      <span class="tl-foot-time">{{ $transfer->created_at->diffForHumans() }}</span>
    </div>
  </div>
  @empty
  <div class="tl-empty">
    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
         style="display:block;margin:0 auto;opacity:.2;">
      <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
    </svg>
    <h3>No transfers found</h3>
    <p>{{ $statusFilter==='all' ? 'No transfer history yet.' : 'No transfers match this filter.' }}</p>
    <a href="{{ route('shop.transfers.request') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;
              background:var(--accent);color:#fff;font-size:12px;font-weight:700;text-decoration:none;">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
      </svg>
      New Request
    </a>
  </div>
  @endforelse
</div>

@if($transfers->hasPages())
  <div style="margin-top:12px;">{{ $transfers->links() }}</div>
@endif

</div>
