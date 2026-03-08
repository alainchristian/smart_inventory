@php use App\Enums\TransferStatus; @endphp

@push('styles')
<style>
/* ── Transfer Detail Page ────────────────────────── */
.td-wrap       { display:flex; flex-direction:column; gap:20px; max-width:100%; overflow:hidden; box-sizing:border-box; }
.td-wrap *     { box-sizing:border-box; }

/* Status hero banner */
.td-hero {
    border-radius:var(--r); overflow:hidden; position:relative;
    background:var(--surface); border:1px solid var(--border);
    padding:20px 24px;
    border-left:5px solid var(--hero-color, var(--accent));
    max-width:100%;
}
.td-hero-inner { display:flex; align-items:center; gap:20px; flex-wrap:wrap; }
.td-hero-num   { font-size:22px; font-weight:800; color:var(--text); letter-spacing:-.5px; }
.td-hero-badge {
    display:inline-flex; align-items:center; gap:6px;
    padding:5px 14px; border-radius:20px; font-size:12px; font-weight:700;
    letter-spacing:.5px; text-transform:uppercase;
    background:var(--badge-bg); color:var(--badge-color);
}
.td-hero-badge-dot { width:7px; height:7px; border-radius:50%; background:currentColor; }
.td-hero-route { display:flex; align-items:center; gap:10px; font-size:14px; flex-wrap:wrap; }
.td-hero-route-node {
    display:flex;
    align-items:center;
    gap:6px;
    font-weight:700;
    color:var(--text);
    word-break:break-word;
    overflow-wrap:break-word;
}
.td-hero-route-node svg { color:var(--text-sub); flex-shrink:0; }
.td-hero-route-arrow { color:var(--text-sub); font-size:18px; }
.td-hero-divider { width:1px; height:32px; background:var(--border); margin:0 8px; }
.td-hero-date  { font-size:12px; color:var(--text-sub); }
.td-hero-date strong { color:var(--text); display:block; font-size:14px; font-weight:700; }
.td-disc-alert {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px; border-radius:8px; font-size:12px; font-weight:700;
    background:rgba(239,68,68,.1); color:#ef4444; border:1px solid rgba(239,68,68,.2);
    margin-left:auto;
}

/* Timeline progress bar */
.td-timeline   { display:flex; align-items:center; gap:0; margin-top:16px; }
.td-tl-step    {
    flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;
    position:relative;
}
.td-tl-step::before {
    content:''; position:absolute; top:11px; left:calc(-50% + 12px); right:calc(50% + 12px);
    height:2px; background:var(--border);
}
.td-tl-step:first-child::before { display:none; }
.td-tl-step.done::before  { background:var(--green); }
.td-tl-step.active::before { background:var(--accent); }
.td-tl-dot {
    width:24px; height:24px; border-radius:50%; border:2px solid var(--border);
    background:var(--surface); display:flex; align-items:center; justify-content:center;
    position:relative; z-index:1; font-size:11px;
}
.td-tl-step.done .td-tl-dot   { background:var(--green); border-color:var(--green); color:#fff; }
.td-tl-step.active .td-tl-dot { background:var(--accent); border-color:var(--accent); color:#fff; }
.td-tl-label   { font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase;
                  color:var(--text-sub); text-align:center; }
.td-tl-step.done .td-tl-label   { color:var(--green); }
.td-tl-step.active .td-tl-label { color:var(--accent); }

/* Body layout */
.td-body       { display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start; max-width:100%; }
.td-body > div { min-width:0; max-width:100%; }
@media(max-width:860px) { .td-body { grid-template-columns:1fr; gap:16px; } }

/* Cards */
.td-card       {
    background:var(--surface); border:1px solid var(--border);
    border-radius:var(--r); overflow:hidden; margin-bottom:16px;
    max-width:100%; width:100%;
}
.td-card:last-child { margin-bottom:0; }
.td-card-head  {
    display:flex; align-items:center; justify-content:space-between;
    padding:13px 18px; border-bottom:1px solid var(--border);
    background:var(--surface2);
}
.td-card-head-left { display:flex; align-items:center; gap:10px; }
.td-card-icon  {
    width:30px; height:30px; border-radius:8px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:var(--icon-bg, rgba(99,102,241,.12));
    color:var(--icon-c, var(--accent));
}
.td-card-head h3 { font-size:13px; font-weight:700; color:var(--text); margin:0; }
.td-card-head p  { font-size:11px; color:var(--text-sub); margin:0; }
.td-card-body  { padding:0; overflow-x:auto; -webkit-overflow-scrolling:touch; max-width:100%; }

/* Items table */
.td-table      { width:100%; border-collapse:collapse; min-width:max-content; }
.td-table thead th {
    padding:10px 16px; font-size:10px; font-weight:700; letter-spacing:.8px;
    text-transform:uppercase; color:var(--text-sub);
    border-bottom:1px solid var(--border); text-align:left; white-space:nowrap;
}
.td-table tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
.td-table tbody tr:last-child { border-bottom:none; }
.td-table tbody tr:hover { background:var(--surface2); }
.td-table tbody td { padding:12px 16px; font-size:13px; color:var(--text); vertical-align:middle; }
.td-table .col-product { font-weight:600; }
.td-table .col-sku     { font-size:11px; color:var(--text-sub); display:block; margin-top:1px; }
.td-table .col-num     { font-weight:700; font-size:14px; }
.td-table .col-disc    { color:#ef4444; font-weight:700; }
.td-table .col-ok      { color:var(--green); font-weight:700; }

/* Box rows */
.td-box-row    { display:flex; align-items:center; gap:12px; padding:11px 16px;
                 border-bottom:1px solid var(--border); }
.td-box-row:last-child { border-bottom:none; }
.td-box-row:hover { background:var(--surface2); }
.td-box-code   { font-size:12px; font-weight:700; color:var(--text); font-family:monospace;
                 background:var(--surface2); padding:3px 8px; border-radius:4px;
                 border:1px solid var(--border); }
.td-box-status {
    font-size:10px; font-weight:700; padding:2px 8px; border-radius:4px;
    letter-spacing:.4px; text-transform:uppercase;
    background:var(--bs-bg); color:var(--bs-c);
}
.td-box-scan   { font-size:11px; color:var(--text-sub); margin-left:auto; }
.td-box-scan strong { color:var(--text); }
.td-box-items  { font-size:11px; color:var(--text-sub); }

/* Right panel meta card */
.td-meta-list  { padding:16px; display:flex; flex-direction:column; gap:12px; max-width:100%; }
.td-meta-row   { display:flex; justify-content:space-between; align-items:flex-start;
                 gap:12px; padding-bottom:12px; border-bottom:1px solid var(--border);
                 max-width:100%; }
.td-meta-row:last-child { border-bottom:none; padding-bottom:0; }
.td-meta-label { font-size:10px; font-weight:700; letter-spacing:.7px; text-transform:uppercase;
                 color:var(--text-sub); flex-shrink:0; }
.td-meta-value { font-size:13px; font-weight:600; color:var(--text); text-align:right;
                 word-wrap:break-word; overflow-wrap:break-word; min-width:0; }
.td-meta-value.mono { font-family:monospace; font-size:12px; }

/* Transporter card */
.td-transporter-body { padding:16px; max-width:100%; }
.td-trans-name { font-size:16px; font-weight:800; color:var(--text); margin:0 0 4px; word-break:break-word; }
.td-trans-company { font-size:12px; color:var(--text-sub); margin:0 0 12px; word-break:break-word; }
.td-trans-detail { display:flex; flex-direction:column; gap:7px; max-width:100%; }
.td-trans-row  { display:flex; align-items:center; gap:8px; font-size:12px; color:var(--text-sub); word-break:break-word; }
.td-trans-row svg { flex-shrink:0; color:var(--accent); }
.td-trans-row strong { color:var(--text); word-break:break-word; }

/* Actions card */
.td-actions-body { padding:14px; display:flex; flex-direction:column; gap:8px; }
.td-btn {
    display:flex; align-items:center; justify-content:center; gap:8px;
    padding:10px 16px; border-radius:var(--rsm); font-size:13px; font-weight:700;
    border:none; cursor:pointer; text-decoration:none; transition:opacity .15s, transform .1s;
    width:100%;
}
.td-btn:hover   { opacity:.88; transform:translateY(-1px); }
.td-btn:active  { transform:scale(.99); }
.td-btn.primary { background:var(--accent); color:#fff; }
.td-btn.outline { background:transparent; color:var(--text); border:1.5px solid var(--border); }
.td-btn.outline:hover { border-color:var(--accent); color:var(--accent); }
.td-btn.danger  { background:rgba(239,68,68,.08); color:#ef4444; border:1.5px solid rgba(239,68,68,.25); }
.td-btn.danger:hover { background:#ef4444; color:#fff; border-color:#ef4444; }
.td-btn.success { background:rgba(16,185,129,.1); color:var(--green); border:1.5px solid rgba(16,185,129,.25); }
.td-btn.success:hover { background:var(--green); color:#fff; border-color:var(--green); }
.td-btn svg     { flex-shrink:0; }

/* Notes block */
.td-notes {
    padding:14px 16px; font-size:13px; color:var(--text-sub);
    line-height:1.6; font-style:italic;
    border-top:1px solid var(--border);
}
.td-notes:empty { display:none; }

/* Empty state */
.td-empty { text-align:center; padding:32px; color:var(--text-sub); font-size:13px; }
.td-empty svg { display:block; margin:0 auto 10px; opacity:.3; }

/* Responsive */
@media(max-width:860px) {
    .td-body  { grid-template-columns:1fr; }
}
@media(max-width:768px) {
    .td-hero        { padding:18px; }
    .td-hero-num    { font-size:20px; }
    .td-hero-badge  { font-size:11px; }
    .td-hero-route  { font-size:13px; }
}
@media(max-width:600px) {
    /* Hero banner */
    .td-hero        { padding:14px 16px; border-radius:8px; }
    .td-hero-inner  { gap:10px; }
    .td-hero-num    { font-size:18px; }
    .td-hero-badge  { padding:4px 11px; font-size:11px; }
    .td-hero-divider { display:none; }
    .td-hero-route  { width:100%; order:3; font-size:12px; }
    .td-hero-date   { font-size:11px; }
    .td-timeline    { overflow-x:auto; padding-bottom:8px; -webkit-overflow-scrolling:touch; }

    /* Cards */
    .td-card        { border-radius:8px; margin-bottom:12px; max-width:100%; }
    .td-card-head   { padding:11px 14px; max-width:100%; }
    .td-card-head h3 { font-size:12px; }
    .td-card-head p  { font-size:10px; }
    .td-card-icon   { width:26px; height:26px; }
    .td-card-icon svg { width:13px; height:13px; }

    /* Card body - must scroll table content */
    .td-card-body { overflow-x:auto; -webkit-overflow-scrolling:touch; max-width:100%; }

    /* Table - natural width with scroll */
    .td-table       {
        display:table;
        width:auto;
        min-width:550px; /* Minimum to show all columns */
    }
    .td-table thead th {
        padding:8px 12px;
        font-size:9px;
        white-space:nowrap;
    }
    .td-table tbody td {
        padding:10px 12px;
        font-size:12px;
    }
    .td-table .col-product { font-size:12px; }
    .td-table .col-sku { font-size:10px; }
    .td-table .col-num { font-size:13px; }

    /* Metadata */
    .td-meta-list   { padding:12px 14px; gap:10px; max-width:100%; }
    .td-meta-row    { padding-bottom:10px; flex-direction:column; align-items:flex-start; gap:4px; max-width:100%; }
    .td-meta-label  { font-size:9px; }
    .td-meta-value  {
        font-size:12px;
        text-align:left;
        word-break:break-word;
        overflow-wrap:break-word;
        max-width:100%;
    }

    /* Boxes */
    .td-box-row     {
        padding:10px 14px;
        flex-wrap:wrap;
        gap:8px;
    }
    .td-box-code    { font-size:11px; }
    .td-box-status  { font-size:9px; }
    .td-box-items   { font-size:10px; }
    .td-box-scan    {
        width:100%;
        margin-left:0;
        font-size:10px;
    }

    /* Transporter */
    .td-transporter-body { padding:12px 14px; }
    .td-trans-name { font-size:14px; }
    .td-trans-company { font-size:11px; }
    .td-trans-row { font-size:11px; }

    /* Actions */
    .td-actions-body { padding:12px; }
    .td-btn { padding:11px 14px; font-size:12px; }

    /* Notes */
    .td-notes { padding:12px 14px; font-size:12px; }
}

/* Extra small phones (under 480px) */
@media(max-width:480px) {
    .td-wrap { gap:14px; }
    .td-body { gap:14px; }

    /* Hero banner - more compact and better text handling */
    .td-hero        { padding:12px; }
    .td-hero-inner  { gap:8px; flex-direction:column; align-items:flex-start; }
    .td-hero-num    { font-size:15px; }
    .td-hero-badge  { padding:4px 10px; font-size:10px; }
    .td-hero-route  {
        font-size:11px;
        gap:8px;
        width:100%;
        flex-direction:column;
        align-items:flex-start;
    }
    .td-hero-route-node {
        font-size:11px;
        word-break:break-word;
        white-space:normal;
    }
    .td-hero-route-node svg { width:11px; height:11px; }
    .td-hero-route-arrow { display:none; }
    .td-hero-date   { font-size:10px; width:100%; }
    .td-hero-date strong { font-size:11px; }
    .td-disc-alert  { padding:5px 10px; font-size:10px; width:100%; justify-content:center; margin-top:6px; }

    /* Timeline */
    .td-timeline { gap:0; margin-top:14px !important; }
    .td-tl-dot { width:18px; height:18px; }
    .td-tl-dot svg { width:10px; height:10px; }
    .td-tl-label { font-size:7px; line-height:1.3; padding:0 2px; }

    /* Cards - more compact */
    .td-card        { border-radius:6px; margin-bottom:12px; }
    .td-card-head   { padding:10px 12px; flex-wrap:wrap; gap:8px; }
    .td-card-head-left { width:100%; }
    .td-card-head h3 { font-size:11px; }
    .td-card-head p  { font-size:9px; line-height:1.4; }
    .td-card-icon   { width:24px; height:24px; border-radius:6px; }
    .td-card-icon svg { width:12px; height:12px; }

    /* Card body scrolling for tables */
    .td-card-body { padding:0; }

    /* Table - compact and scrollable */
    .td-table       { min-width:480px; width:auto; }
    .td-table thead th {
        padding:8px;
        font-size:9px;
        letter-spacing:0.3px;
    }
    .td-table tbody td {
        padding:10px 8px;
        font-size:11px;
    }
    .td-table .col-product { font-size:11px; max-width:120px; overflow:hidden; text-overflow:ellipsis; }
    .td-table .col-sku { font-size:9px; margin-top:2px; }
    .td-table .col-num { font-size:12px; }

    /* Keep product column visible when scrolling */
    .td-table thead th:first-child,
    .td-table tbody td:first-child {
        position:sticky;
        left:0;
        background:var(--surface);
        z-index:2;
    }
    .td-table tbody tr:hover td:first-child { background:var(--surface2); }

    /* Metadata - fully stacked */
    .td-meta-list   { padding:12px; gap:10px; max-width:100%; }
    .td-meta-row    { padding-bottom:10px; gap:4px; max-width:100%; }
    .td-meta-label  { font-size:9px; letter-spacing:0.5px; }
    .td-meta-value  {
        font-size:12px;
        word-break:break-word;
        overflow-wrap:break-word;
        max-width:100%;
    }
    .td-meta-value.mono { font-size:11px; word-break:break-all; }

    /* Boxes */
    .td-box-row     { padding:10px 12px; gap:7px; flex-wrap:wrap; }
    .td-box-code    { font-size:10px; padding:3px 7px; }
    .td-box-status  { font-size:9px; padding:3px 7px; }
    .td-box-items   { font-size:10px; }
    .td-box-scan    {
        font-size:10px;
        width:100%;
        margin-left:0;
        display:flex;
        gap:12px;
        flex-wrap:wrap;
    }
    .td-box-scan > div { flex:1; min-width:max-content; }

    /* Transporter */
    .td-transporter-body { padding:10px 12px; }
    .td-trans-name { font-size:13px; }
    .td-trans-company { font-size:10px; margin-bottom:10px; }
    .td-trans-row { font-size:10px; gap:6px; }
    .td-trans-row svg { width:11px; height:11px; }

    /* Actions */
    .td-actions-body { padding:10px; }
    .td-btn { padding:10px 12px; font-size:11px; }
    .td-btn svg { width:12px; height:12px; }

    /* Notes */
    .td-notes { padding:10px 12px; font-size:11px; }

    /* Empty state */
    .td-empty { padding:24px 16px; font-size:12px; }
    .td-empty svg { width:28px; height:28px; }
}
</style>
@endpush

<div class="td-wrap"
     @if(in_array($transfer->status->value, ['approved', 'in_transit']))
     wire:poll.5s
     @endif>

  {{-- Action Required Alert --}}
  @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED]))
  <div style="background:#eff6ff;border:1.5px solid #3b82f6;border-radius:10px;padding:14px 18px;display:flex;align-items:center;gap:12px;">
    <svg width="20" height="20" fill="#3b82f6" viewBox="0 0 20 20" style="flex-shrink:0;">
      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
    </svg>
    <div>
      <p style="font-size:14px;font-weight:700;color:#1e40af;margin:0 0 2px;">
        @if($transfer->status === TransferStatus::IN_TRANSIT)
          Transfer In Transit - Action Required
        @else
          Transfer Delivered - Ready to Receive
        @endif
      </p>
      <p style="font-size:13px;color:#1e40af;margin:0;">
        @if($transfer->status === TransferStatus::IN_TRANSIT)
          This transfer is on the way. Mark it as delivered when it arrives, then scan to receive the boxes.
        @else
          This transfer has been delivered. Scan the boxes to complete the receiving process.
        @endif
      </p>
    </div>
  </div>
  @endif

  {{-- ── Status hero ─────────────────────────────── --}}
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

    // Timeline step states — adapt status values to match TransferStatus enum
    $steps = [
      ['label'=>'Requested', 'done'=> true,  'active'=> false],
      ['label'=>'Approved',  'done'=> in_array($transfer->status->value, ['approved','in_transit','delivered','received']), 'active'=> $transfer->status->value === 'approved'],
      ['label'=>'In Transit','done'=> in_array($transfer->status->value, ['in_transit','delivered','received']), 'active'=> $transfer->status->value === 'in_transit'],
      ['label'=>'Delivered', 'done'=> in_array($transfer->status->value, ['delivered','received']), 'active'=> $transfer->status->value === 'delivered'],
      ['label'=>'Received',  'done'=> $transfer->status->value === 'received', 'active'=> false],
    ];
  @endphp

  <div class="td-hero" style="--hero-color:{{ $sc['color'] }}">
    <div class="td-hero-inner">

      {{-- Number + status --}}
      <span class="td-hero-num">{{ $transfer->transfer_number }}</span>
      <span class="td-hero-badge" style="--badge-bg:{{ $sc['bg'] }};--badge-color:{{ $sc['color'] }}">
        <span class="td-hero-badge-dot"></span>
        {{ $transfer->status->label() }}
      </span>

      <div class="td-hero-divider"></div>

      {{-- Route --}}
      <div class="td-hero-route">
        <div class="td-hero-route-node">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          {{ $transfer->fromWarehouse->name ?? '—' }}
        </div>
        <span class="td-hero-route-arrow">→</span>
        <div class="td-hero-route-node">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          {{ $transfer->toShop->name ?? '—' }}
        </div>
      </div>

      <div class="td-hero-divider"></div>

      {{-- Key date --}}
      <div class="td-hero-date">
        <strong>{{ $transfer->requested_at?->format('d M Y') ?? '—' }}</strong>
        Requested
      </div>

      @if($transfer->has_discrepancy ?? false)
        <span class="td-disc-alert">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v5h2v-5h-2zm0 6v2h2v-2h-2z"/></svg>
          Discrepancy Recorded
        </span>
      @endif
    </div>

    {{-- Timeline --}}
    <div class="td-timeline" style="margin-top:20px">
      @foreach($steps as $step)
        <div class="td-tl-step {{ $step['done'] ? 'done' : '' }} {{ $step['active'] ? 'active' : '' }}">
          <div class="td-tl-dot">
            @if($step['done'])
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            @elseif($step['active'])
              <svg width="8" height="8" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg>
            @endif
          </div>
          <div class="td-tl-label">{{ $step['label'] }}</div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- ── Body ────────────────────────────────────── --}}
  <div class="td-body">

    {{-- ── Left: Items + Boxes ─────────────────── --}}
    <div>

      {{-- Items requested --}}
      <div class="td-card">
        <div class="td-card-head">
          <div class="td-card-head-left">
            <div class="td-card-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
            </div>
            <div>
              <h3>Products Requested</h3>
              <p>{{ $transfer->items->count() }} product line(s) · {{ array_sum(array_column($items, 'boxes_requested')) }} boxes total</p>
            </div>
          </div>
        </div>
        <div class="td-card-body">
          @if($transfer->items->isEmpty())
            <div class="td-empty">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
              No items found on this transfer.
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
                    $hasDisc = ($item->discrepancy_quantity ?? 0) != 0;
                    $received = $item->quantity_received ?? null;
                    $shipped = $item->quantity_shipped ?? null;
                  @endphp
                  <tr>
                    <td>
                      <div class="col-product">{{ $item->product->name ?? '—' }}</div>
                      <span class="col-sku">{{ $item->product->sku ?? '' }}</span>
                    </td>
                    <td><span class="col-num">{{ $item->quantity_requested }}</span> <small style="color:var(--text-sub)">items</small></td>
                    <td>
                      @if($shipped !== null)
                        <span class="col-num">{{ $shipped }}</span>
                      @else
                        <span style="color:var(--text-sub)">—</span>
                      @endif
                    </td>
                    <td>
                      @if($received !== null)
                        <span class="{{ $hasDisc ? 'col-disc' : 'col-ok' }}">{{ $received }}</span>
                      @else
                        <span style="color:var(--text-sub)">—</span>
                      @endif
                    </td>
                    <td>
                      @if($hasDisc)
                        <span class="col-disc">{{ $item->discrepancy_quantity > 0 ? '+' : '' }}{{ $item->discrepancy_quantity }}</span>
                      @else
                        <span class="col-ok">—</span>
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
            <div class="td-card-icon" style="--icon-bg:rgba(139,92,246,.12);--icon-c:var(--violet)">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
            </div>
            <div>
              <h3>Assigned Boxes</h3>
              <p>{{ $transfer->boxes()->count() }} box(es) assigned to this transfer</p>
            </div>
          </div>
        </div>
        <div class="td-card-body">
          @foreach($transfer->boxes as $tb)
            @php
              $box = $tb->box;
              $statusValue = $box->status?->value ?? 'full';
              $bsc = match($statusValue) {
                'full'    => ['bg'=>'rgba(16,185,129,.1)','c'=>'var(--green)'],
                'partial' => ['bg'=>'rgba(217,119,6,.1)','c'=>'#d97706'],
                'damaged' => ['bg'=>'rgba(239,68,68,.1)','c'=>'#ef4444'],
                default   => ['bg'=>'rgba(128,128,128,.08)','c'=>'var(--text-sub)'],
              };
            @endphp
            <div class="td-box-row">
              <span class="td-box-code">{{ $box->box_code ?? '—' }}</span>
              <span class="td-box-status" style="--bs-bg:{{ $bsc['bg'] }};--bs-c:{{ $bsc['c'] }}">
                {{ $box->status?->label() ?? ucfirst($statusValue) }}
              </span>
              <span class="td-box-items" style="color:var(--text-sub)">
                {{ $box->items_remaining ?? '—' }} items
              </span>
              <div class="td-box-scan">
                @if($tb->scanned_out_at)
                  <div>Out: <strong>{{ $tb->scanned_out_at->format('d M, H:i') }}</strong></div>
                @endif
                @if($tb->scanned_in_at)
                  <div style="color:var(--green)">In: <strong>{{ $tb->scanned_in_at->format('d M, H:i') }}</strong></div>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
      @endif

    </div>

    {{-- ── Right: Metadata + Transporter + Actions ─ --}}
    <div>

      {{-- Transfer metadata --}}
      <div class="td-card">
        <div class="td-card-head">
          <div class="td-card-head-left">
            <div class="td-card-icon" style="--icon-bg:rgba(99,102,241,.1);--icon-c:var(--accent)">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div><h3>Transfer Details</h3></div>
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
              <span class="td-meta-value" style="color:{{ $sc['color'] }}">{{ $transfer->status->label() }}</span>
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
              <span class="td-meta-label">Requested at</span>
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
              <span class="td-meta-label">Reviewed at</span>
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
              <span class="td-meta-value" style="color:var(--green)">{{ $transfer->received_at->format('d M Y, H:i') }}</span>
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
            <div class="td-card-icon" style="--icon-bg:rgba(139,92,246,.12);--icon-c:var(--violet)">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </div>
            <div><h3>Transporter</h3></div>
          </div>
        </div>
        <div class="td-card-body">
          <div class="td-transporter-body">
            <p class="td-trans-name">{{ $transfer->transporter->name }}</p>
            @if($transfer->transporter->company_name ?? false)
              <p class="td-trans-company">{{ $transfer->transporter->company_name }}</p>
            @endif
            <div class="td-trans-detail">
              @if($transfer->transporter->phone ?? false)
                <div class="td-trans-row">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 0 0 .07 2.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.72 6.72l1.28-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                  <strong>{{ $transfer->transporter->phone }}</strong>
                </div>
              @endif
              @if($transfer->transporter->vehicle_number ?? false)
                <div class="td-trans-row">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                  <strong>{{ $transfer->transporter->vehicle_number }}</strong>
                </div>
              @endif
              @if($transfer->transporter->license_number ?? false)
                <div class="td-trans-row">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                  License: <strong>{{ $transfer->transporter->license_number }}</strong>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
      @endif

      {{-- Context-aware actions --}}
      @if(in_array($transfer->status, [TransferStatus::IN_TRANSIT, TransferStatus::DELIVERED]))
      <div class="td-card">
        <div class="td-card-head">
          <div class="td-card-head-left">
            <div class="td-card-icon" style="--icon-bg:rgba(16,185,129,.1);--icon-c:var(--green)">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div><h3>Actions</h3></div>
          </div>
        </div>
        <div class="td-card-body">
          <div class="td-actions-body">
            @if($transfer->status === TransferStatus::IN_TRANSIT)
            <button wire:click="markAsDelivered" class="td-btn primary">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
              Mark as Delivered
            </button>
            @endif
            <a href="{{ route('shop.transfers.receive', $transfer) }}" class="td-btn success">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
              Scan &amp; Receive Boxes
            </a>
          </div>
        </div>
      </div>
      @endif

    </div>
  </div>

</div>
