@php use App\Enums\TransferStatus; @endphp
<style>
/* ── Transfer Detail ────────────────────────────────────── */
.td-wrap { display:flex; flex-direction:column; gap:16px; }

/* ── Action alert ────────────────────────────────────── */
.td-alert {
    display:flex; align-items:flex-start; gap:10px;
    padding:12px 14px; border-radius:10px;
    background:var(--accent-dim); border:1px solid var(--accent);
}
.td-alert-body p { margin:0; font-size:12px; color:var(--accent); }
.td-alert-body p:first-child { font-weight:700; font-size:13px; margin-bottom:2px; }

/* ── Hero banner ─────────────────────────────────────── */
.td-hero {
    background:#fff; border:1px solid var(--border);
    border-radius:12px; overflow:hidden; position:relative;
    padding:16px 18px;
    border-left:4px solid var(--hero-color, var(--accent));
}
.td-hero-inner { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.td-hero-num   { font-size:17px; font-weight:800; color:var(--text); font-family:var(--mono); letter-spacing:-.3px; }
.td-hero-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700;
    letter-spacing:.4px; text-transform:uppercase;
    background:var(--badge-bg); color:var(--badge-color);
}
.td-hero-badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.td-hero-route { display:flex; align-items:center; gap:6px; }
.td-hero-rnode { font-size:12px; font-weight:600; color:var(--text); }
.td-hero-rnode svg { color:var(--text-dim); }
.td-hero-divider { width:1px; height:20px; background:var(--border); }
.td-hero-date { font-size:11px; color:var(--text-dim); }
.td-hero-date strong { color:var(--text); font-weight:600; display:block; font-size:12px; }
.td-disc-badge {
    display:inline-flex; align-items:center; gap:5px; margin-left:auto;
    padding:2px 8px; border-radius:8px; font-size:10px; font-weight:700;
    background:var(--red-dim); color:var(--red);
}

/* ── Timeline ────────────────────────────────────────── */
.td-timeline { display:flex; align-items:center; margin-top:14px; }
.td-tl-step {
    flex:1; display:flex; flex-direction:column; align-items:center; gap:3px;
    position:relative;
}
.td-tl-step::before {
    content:''; position:absolute; top:10px;
    left:calc(-50% + 10px); right:calc(50% + 10px);
    height:2px; background:var(--border);
}
.td-tl-step:first-child::before { display:none; }
.td-tl-step.done::before   { background:var(--green); }
.td-tl-step.active::before { background:var(--accent); }
.td-tl-dot {
    width:20px; height:20px; border-radius:50%; border:2px solid var(--border);
    background:#fff; display:flex; align-items:center; justify-content:center;
    position:relative; z-index:1;
}
.td-tl-step.done .td-tl-dot   { background:var(--green);  border-color:var(--green);  color:#fff; }
.td-tl-step.active .td-tl-dot { background:var(--accent); border-color:var(--accent); color:#fff; }
.td-tl-label {
    font-size:9px; font-weight:700; letter-spacing:.5px; text-transform:uppercase;
    color:var(--text-dim); text-align:center; line-height:1.3;
}
.td-tl-step.done .td-tl-label   { color:var(--green); }
.td-tl-step.active .td-tl-label { color:var(--accent); }

/* ── Body grid ───────────────────────────────────────── */
.td-body { display:grid; grid-template-columns:1fr 300px; gap:16px; align-items:start; }
@media(max-width:860px) { .td-body { grid-template-columns:1fr; } }

/* ── Cards ───────────────────────────────────────────── */
.td-card {
    background:#fff; border:1px solid var(--border);
    border-radius:12px; overflow:hidden; margin-bottom:12px;
}
.td-card:last-child { margin-bottom:0; }
.td-card-head {
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 14px; border-bottom:1px solid var(--border);
    background:var(--surface2);
}
.td-card-head-left { display:flex; align-items:center; gap:8px; }
.td-card-icon {
    width:28px; height:28px; border-radius:7px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:var(--icon-bg, rgba(99,102,241,.12));
    color:var(--icon-c, var(--accent));
}
.td-card-head h3 { font-size:13px; font-weight:700; color:var(--text); margin:0; }
.td-card-head p  { font-size:11px; color:var(--text-dim); margin:0; }
.td-card-body    { overflow-x:auto; -webkit-overflow-scrolling:touch; }

/* ── Items table ─────────────────────────────────────── */
.td-table { width:100%; border-collapse:collapse; min-width:480px; }
.td-table thead th {
    padding:8px 12px; font-size:10px; font-weight:700; letter-spacing:.7px;
    text-transform:uppercase; color:var(--text-dim);
    border-bottom:1px solid var(--border); text-align:left; white-space:nowrap;
}
.td-table tbody tr { border-bottom:1px solid var(--border); }
.td-table tbody tr:last-child { border-bottom:none; }
.td-table tbody tr:hover { background:var(--surface2); }
.td-table tbody td { padding:10px 12px; font-size:12px; color:var(--text); vertical-align:middle; }
.td-table .col-product { font-weight:600; font-size:13px; }
.td-table .col-sku     { font-size:10px; color:var(--text-dim); display:block; margin-top:1px; }
.td-table .col-num     { font-weight:700; font-size:13px; font-family:var(--mono); }
.td-table .col-disc    { color:var(--red); font-weight:700; }
.td-table .col-ok      { color:var(--green); font-weight:700; }

/* ── Box rows ────────────────────────────────────────── */
.td-box-row {
    display:flex; align-items:center; gap:10px;
    padding:9px 14px; border-bottom:1px solid var(--border);
}
.td-box-row:last-child { border-bottom:none; }
.td-box-row:hover { background:var(--surface2); }
.td-box-code {
    font-size:11px; font-weight:700; color:var(--text); font-family:var(--mono);
    background:var(--surface2); padding:2px 7px; border-radius:4px;
    border:1px solid var(--border); flex-shrink:0;
}
.td-box-status {
    font-size:10px; font-weight:700; padding:2px 7px; border-radius:4px;
    letter-spacing:.4px; text-transform:uppercase;
    background:var(--bs-bg); color:var(--bs-c); flex-shrink:0;
}
.td-box-items { font-size:11px; color:var(--text-dim); }
.td-box-scan  { font-size:11px; color:var(--text-dim); margin-left:auto; display:flex; gap:10px; flex-wrap:wrap; }
.td-box-scan strong { color:var(--text); font-weight:600; }

/* ── Meta list ───────────────────────────────────────── */
.td-meta-list { padding:12px 14px; display:flex; flex-direction:column; gap:10px; }
.td-meta-row  {
    display:flex; justify-content:space-between; align-items:flex-start;
    gap:10px; padding-bottom:10px; border-bottom:1px solid var(--border);
}
.td-meta-row:last-child { border-bottom:none; padding-bottom:0; }
.td-meta-label { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase;
                 color:var(--text-dim); flex-shrink:0; }
.td-meta-value { font-size:12px; font-weight:600; color:var(--text); text-align:right;
                 word-break:break-word; }
.td-meta-value.mono { font-family:var(--mono); font-size:11px; }

/* ── Transporter ─────────────────────────────────────── */
.td-trans-body  { padding:12px 14px; }
.td-trans-name  { font-size:14px; font-weight:700; color:var(--text); margin:0 0 2px; }
.td-trans-co    { font-size:11px; color:var(--text-dim); margin:0 0 10px; }
.td-trans-row   { display:flex; align-items:center; gap:7px; font-size:11px;
                  color:var(--text-dim); margin-bottom:5px; }
.td-trans-row:last-child { margin-bottom:0; }
.td-trans-row svg { flex-shrink:0; color:var(--accent); }
.td-trans-row strong { color:var(--text); font-weight:600; }

/* ── Action buttons ──────────────────────────────────── */
.td-actions-body { padding:12px; display:flex; flex-direction:column; gap:7px; }
.td-btn {
    display:flex; align-items:center; justify-content:center; gap:7px;
    padding:9px 14px; border-radius:8px; font-size:12px; font-weight:700;
    border:none; cursor:pointer; text-decoration:none; transition:all .15s;
    width:100%;
}
.td-btn:hover   { opacity:.88; }
.td-btn.primary { background:var(--accent); color:#fff; border:1px solid var(--accent); }
.td-btn.outline { background:#fff; color:var(--text); border:1px solid var(--border); }
.td-btn.outline:hover { border-color:var(--accent); color:var(--accent); }
.td-btn.success { background:var(--green-dim); color:var(--green); border:1px solid var(--green-dim); }
.td-btn.success:hover { background:var(--green); color:#fff; }
.td-btn svg { flex-shrink:0; }

/* ── Notes ───────────────────────────────────────────── */
.td-notes {
    padding:10px 14px; font-size:12px; color:var(--text-dim);
    line-height:1.6; font-style:italic;
    border-top:1px solid var(--border);
}

/* ── Empty state ─────────────────────────────────────── */
.td-empty { text-align:center; padding:28px; color:var(--text-dim); font-size:12px; }
.td-empty svg { display:block; margin:0 auto 8px; opacity:.25; }

/* ── Responsive ──────────────────────────────────────── */
@media(max-width:640px) {
    .td-hero        { padding:12px 14px; }
    .td-hero-divider { display:none; }
    .td-hero-route  { width:100%; }
    .td-timeline    { overflow-x:auto; padding-bottom:6px; }
    .td-meta-row    { flex-direction:column; align-items:flex-start; gap:3px; }
    .td-meta-value  { text-align:left; }
    .td-box-scan    { width:100%; margin-left:0; }
}
@media(max-width:480px) {
    .td-hero-inner  { gap:8px; }
    .td-tl-label    { font-size:8px; padding:0 2px; }
    .td-tl-dot      { width:16px; height:16px; }
}
</style>

<div class="td-wrap"
     @if(in_array($transfer->status->value, ['approved','in_transit'])) wire:poll.5s @endif>

  {{-- Action required alert --}}
  @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED]))
  <div class="td-alert">
    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;color:var(--accent);margin-top:1px;">
      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
    </svg>
    <div class="td-alert-body">
      <p>{{ $transfer->status === TransferStatus::IN_TRANSIT ? 'Transfer In Transit — Action Required' : 'Transfer Delivered — Ready to Receive' }}</p>
      <p>{{ $transfer->status === TransferStatus::IN_TRANSIT ? 'This transfer is on its way. Mark as delivered when it arrives, then scan to receive boxes.' : 'This transfer has been delivered. Scan the boxes to complete the receiving process.' }}</p>
    </div>
  </div>
  @endif

  {{-- Hero banner --}}
  @php
    $sc = match($transfer->status->value) {
      'pending'    => ['color'=>'#d97706',       'bg'=>'rgba(217,119,6,.1)'],
      'approved'   => ['color'=>'var(--accent)',  'bg'=>'rgba(99,102,241,.1)'],
      'in_transit' => ['color'=>'#8b5cf6',        'bg'=>'rgba(139,92,246,.1)'],
      'delivered'  => ['color'=>'#0ea5e9',        'bg'=>'rgba(14,165,233,.1)'],
      'received'   => ['color'=>'var(--green)',   'bg'=>'rgba(16,185,129,.1)'],
      'rejected'   => ['color'=>'var(--red)',     'bg'=>'var(--red-dim)'],
      default      => ['color'=>'var(--text-dim)','bg'=>'rgba(128,128,128,.08)'],
    };
    $steps = [
      ['label'=>'Requested', 'done'=>true,  'active'=>false],
      ['label'=>'Approved',  'done'=>in_array($transfer->status->value, ['approved','in_transit','delivered','received']), 'active'=>$transfer->status->value==='approved'],
      ['label'=>'In Transit','done'=>in_array($transfer->status->value, ['in_transit','delivered','received']), 'active'=>$transfer->status->value==='in_transit'],
      ['label'=>'Delivered', 'done'=>in_array($transfer->status->value, ['delivered','received']), 'active'=>$transfer->status->value==='delivered'],
      ['label'=>'Received',  'done'=>$transfer->status->value==='received', 'active'=>false],
    ];
  @endphp

  <div class="td-hero" style="--hero-color:{{ $sc['color'] }}">
    <div class="td-hero-inner">
      <span class="td-hero-num">{{ $transfer->transfer_number }}</span>
      <span class="td-hero-badge" style="--badge-bg:{{ $sc['bg'] }};--badge-color:{{ $sc['color'] }}">
        <span class="td-hero-badge-dot"></span>
        {{ $transfer->status->label() }}
      </span>

      <div class="td-hero-divider"></div>

      <div class="td-hero-route">
        <div class="td-hero-rnode" style="display:flex;align-items:center;gap:4px;">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          {{ $transfer->fromWarehouse->name ?? '—' }}
        </div>
        <svg width="10" height="10" viewBox="0 0 24 24" fill="{{ $sc['color'] }}"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        <div class="td-hero-rnode" style="display:flex;align-items:center;gap:4px;">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          {{ $transfer->toShop->name ?? '—' }}
        </div>
      </div>

      <div class="td-hero-divider"></div>

      <div class="td-hero-date">
        <strong>{{ $transfer->requested_at?->format('d M Y') ?? '—' }}</strong>
        Requested
      </div>

      @if($transfer->has_discrepancy ?? false)
        <span class="td-disc-badge">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v5h2v-5h-2zm0 6v2h2v-2h-2z"/></svg>
          Discrepancy
        </span>
      @endif
    </div>

    {{-- Timeline --}}
    <div class="td-timeline" style="margin-top:14px;">
      @foreach($steps as $step)
        <div class="td-tl-step {{ $step['done'] ? 'done' : '' }} {{ $step['active'] ? 'active' : '' }}">
          <div class="td-tl-dot">
            @if($step['done'])
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            @elseif($step['active'])
              <svg width="7" height="7" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg>
            @endif
          </div>
          <div class="td-tl-label">{{ $step['label'] }}</div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Body --}}
  <div class="td-body">

    {{-- Left: Items + Boxes --}}
    <div>

      {{-- Products requested --}}
      <div class="td-card">
        <div class="td-card-head">
          <div class="td-card-head-left">
            <div class="td-card-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
            </div>
            <div>
              <h3>Products Requested</h3>
              <p>{{ $transfer->items->count() }} product{{ $transfer->items->count() === 1 ? '' : 's' }} · {{ array_sum(array_column($items, 'boxes_requested')) }} boxes total</p>
            </div>
          </div>
        </div>
        <div class="td-card-body">
          @if($transfer->items->isEmpty())
            <div class="td-empty">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
              No items on this transfer.
            </div>
          @else
            <table class="td-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Requested</th>
                  <th>Shipped</th>
                  <th>Received</th>
                  <th>Discrepancy</th>
                </tr>
              </thead>
              <tbody>
                @foreach($transfer->items as $item)
                  @php
                    $ipb = max(1, (int)($item->product->items_per_box ?? 1));
                    $bxReq      = (int) $item->quantity_requested;
                    $bxShipped  = $item->quantity_shipped  !== null ? (int) round($item->quantity_shipped  / $ipb) : null;
                    $bxReceived = $item->quantity_received !== null ? (int) round($item->quantity_received / $ipb) : null;
                    $bxDisc     = (int) round(($item->discrepancy_quantity ?? 0) / $ipb);
                    $hasDisc    = $bxDisc !== 0;
                  @endphp
                  <tr>
                    <td>
                      <div class="col-product">{{ $item->product->name ?? '—' }}</div>
                      @if($item->product->sku ?? false)
                        <span class="col-sku">{{ $item->product->sku }}</span>
                      @endif
                    </td>
                    <td><span class="col-num">{{ $bxReq }}</span> <small style="color:var(--text-dim);font-size:10px;">box{{ $bxReq === 1 ? '' : 'es' }}</small></td>
                    <td>
                      @if($bxShipped !== null)
                        <span class="col-num">{{ $bxShipped }}</span> <small style="color:var(--text-dim);font-size:10px;">box{{ $bxShipped === 1 ? '' : 'es' }}</small>
                      @else
                        <span style="color:var(--text-dim);">—</span>
                      @endif
                    </td>
                    <td>
                      @if($bxReceived !== null)
                        <span class="{{ $hasDisc ? 'col-disc' : 'col-ok' }}">{{ $bxReceived }}</span> <small style="color:var(--text-dim);font-size:10px;">box{{ $bxReceived === 1 ? '' : 'es' }}</small>
                      @else
                        <span style="color:var(--text-dim);">—</span>
                      @endif
                    </td>
                    <td>
                      @if($hasDisc)
                        <span class="col-disc">{{ $bxDisc > 0 ? '+' : '' }}{{ $bxDisc }} box{{ abs($bxDisc) === 1 ? '' : 'es' }}</span>
                      @else
                        <span class="col-ok" style="font-size:11px;">—</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </div>

      {{-- Assigned boxes --}}
      @if($transfer->boxes()->count() > 0)
      <div class="td-card">
        <div class="td-card-head">
          <div class="td-card-head-left">
            <div class="td-card-icon" style="--icon-bg:rgba(139,92,246,.12);--icon-c:#8b5cf6;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
            </div>
            <div>
              <h3>Assigned Boxes</h3>
              <p>{{ $transfer->boxes()->count() }} box{{ $transfer->boxes()->count() === 1 ? '' : 'es' }} assigned</p>
            </div>
          </div>
        </div>
        <div class="td-card-body">
          @foreach($transfer->boxes as $tb)
            @php
              $box = $tb->box;
              $sv  = $box->status?->value ?? 'full';
              $bsc = match($sv) {
                'full'    => ['bg'=>'rgba(16,185,129,.1)', 'c'=>'var(--green)'],
                'partial' => ['bg'=>'rgba(217,119,6,.1)',  'c'=>'#d97706'],
                'damaged' => ['bg'=>'var(--red-dim)',      'c'=>'var(--red)'],
                default   => ['bg'=>'var(--surface2)',     'c'=>'var(--text-dim)'],
              };
            @endphp
            <div class="td-box-row">
              <span class="td-box-code">{{ $box->box_code ?? '—' }}</span>
              <span class="td-box-status" style="--bs-bg:{{ $bsc['bg'] }};--bs-c:{{ $bsc['c'] }}">
                {{ $box->status?->label() ?? ucfirst($sv) }}
              </span>
              <span class="td-box-items">{{ $box->items_remaining ?? '—' }} items</span>
              <div class="td-box-scan">
                @if($tb->scanned_out_at)
                  <span>Out: <strong>{{ $tb->scanned_out_at->format('d M, H:i') }}</strong></span>
                @endif
                @if($tb->scanned_in_at)
                  <span style="color:var(--green)">In: <strong>{{ $tb->scanned_in_at->format('d M, H:i') }}</strong></span>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
      @endif

    </div>

    {{-- Right: Meta + Transporter + Actions --}}
    <div>

      {{-- Transfer metadata --}}
      <div class="td-card">
        <div class="td-card-head">
          <div class="td-card-head-left">
            <div class="td-card-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div><h3>Details</h3></div>
          </div>
        </div>
        <div class="td-card-body">
          <div class="td-meta-list">
            <div class="td-meta-row">
              <span class="td-meta-label">Reference</span>
              <span class="td-meta-value mono">{{ $transfer->transfer_number }}</span>
            </div>
            <div class="td-meta-row">
              <span class="td-meta-label">Status</span>
              <span class="td-meta-value" style="color:{{ $sc['color'] }};">{{ $transfer->status->label() }}</span>
            </div>
            <div class="td-meta-row">
              <span class="td-meta-label">From</span>
              <span class="td-meta-value">{{ $transfer->fromWarehouse->name ?? '—' }}</span>
            </div>
            <div class="td-meta-row">
              <span class="td-meta-label">To</span>
              <span class="td-meta-value">{{ $transfer->toShop->name ?? '—' }}</span>
            </div>
            <div class="td-meta-row">
              <span class="td-meta-label">Requested by</span>
              <span class="td-meta-value">{{ $transfer->requestedBy->name ?? '—' }}</span>
            </div>
            <div class="td-meta-row">
              <span class="td-meta-label">Requested</span>
              <span class="td-meta-value">{{ $transfer->requested_at?->format('d M Y, H:i') ?? '—' }}</span>
            </div>
            @if($transfer->reviewedBy)
            <div class="td-meta-row">
              <span class="td-meta-label">Reviewed by</span>
              <span class="td-meta-value">{{ $transfer->reviewedBy->name }}</span>
            </div>
            @endif
            @if($transfer->reviewed_at)
            <div class="td-meta-row">
              <span class="td-meta-label">Reviewed</span>
              <span class="td-meta-value">{{ $transfer->reviewed_at->format('d M Y, H:i') }}</span>
            </div>
            @endif
            @if($transfer->delivered_at)
            <div class="td-meta-row">
              <span class="td-meta-label">Delivered</span>
              <span class="td-meta-value">{{ $transfer->delivered_at->format('d M Y, H:i') }}</span>
            </div>
            @endif
            @if($transfer->received_at)
            <div class="td-meta-row">
              <span class="td-meta-label">Received</span>
              <span class="td-meta-value" style="color:var(--green);">{{ $transfer->received_at->format('d M Y, H:i') }}</span>
            </div>
            @endif
            @if($transfer->receivedBy)
            <div class="td-meta-row">
              <span class="td-meta-label">Received by</span>
              <span class="td-meta-value">{{ $transfer->receivedBy->name }}</span>
            </div>
            @endif
          </div>
          @if($transfer->notes)
            <div class="td-notes">{{ $transfer->notes }}</div>
          @endif
        </div>
      </div>

      {{-- Transporter --}}
      @if($transfer->transporter)
      <div class="td-card">
        <div class="td-card-head">
          <div class="td-card-head-left">
            <div class="td-card-icon" style="--icon-bg:rgba(139,92,246,.12);--icon-c:#8b5cf6;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </div>
            <div><h3>Transporter</h3></div>
          </div>
        </div>
        <div class="td-card-body">
          <div class="td-trans-body">
            <p class="td-trans-name">{{ $transfer->transporter->name }}</p>
            @if($transfer->transporter->company_name ?? false)
              <p class="td-trans-co">{{ $transfer->transporter->company_name }}</p>
            @endif
            @if($transfer->transporter->phone ?? false)
              <div class="td-trans-row">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8a19.79 19.79 0 01-3-8.63A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.72 6.72l1.28-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                <strong>{{ $transfer->transporter->phone }}</strong>
              </div>
            @endif
            @if($transfer->transporter->vehicle_number ?? false)
              <div class="td-trans-row">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Vehicle: <strong>{{ $transfer->transporter->vehicle_number }}</strong>
              </div>
            @endif
            @if($transfer->transporter->license_number ?? false)
              <div class="td-trans-row">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                License: <strong>{{ $transfer->transporter->license_number }}</strong>
              </div>
            @endif
          </div>
        </div>
      </div>
      @endif

      {{-- Actions --}}
      @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED]))
      <div class="td-card">
        <div class="td-card-head">
          <div class="td-card-head-left">
            <div class="td-card-icon" style="--icon-bg:var(--green-dim);--icon-c:var(--green);">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div><h3>Actions</h3></div>
          </div>
        </div>
        <div class="td-card-body">
          <div class="td-actions-body">
            @if($transfer->status === TransferStatus::IN_TRANSIT)
            <button wire:click="markAsDelivered" class="td-btn primary">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
              Mark as Delivered
            </button>
            @endif
            <a href="{{ route('shop.transfers.receive', $transfer) }}" class="td-btn success">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
              Scan &amp; Receive Boxes
            </a>
          </div>
        </div>
      </div>
      @endif

    </div>
  </div>

</div>
