<div class="wr-page" style="font-family:var(--font)">
<style>
.wr-page { padding:0 0 80px; }
.wr-title { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px; }
.wr-sub   { font-size:13px;color:var(--text-dim);margin:0 0 20px; }
.wr-card      { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);margin-bottom:16px;min-width:0; }
.wr-card-head { padding:14px 20px;border-bottom:1px solid var(--border); }
.wr-card-title { font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px; }
.wr-card-sub  { font-size:12px;color:var(--text-dim);margin-top:2px; }
.wr-count     { font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;font-family:var(--mono);background:var(--amber-dim);color:var(--amber); }
.wr-row   { display:grid;grid-template-columns:170px minmax(0,1fr) 150px auto;gap:16px;align-items:center;padding:13px 20px;border-bottom:1px solid var(--border);transition:background var(--tr); }
.wr-row:last-child { border-bottom:none; }
.wr-row:hover { background:var(--surface2); }
.wr-mono  { font-family:var(--mono);font-weight:700;color:var(--text);white-space:nowrap; }
.wr-main  { font-size:13px;font-weight:600;color:var(--text); }
.wr-meta  { font-size:12px;color:var(--text-dim);margin-top:2px; }
.wr-ellip { overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.wr-empty { padding:40px 20px;text-align:center;font-size:13px;color:var(--text-dim); }
.wr-table { width:100%;border-collapse:collapse; }
.wr-table thead tr { border-bottom:2px solid var(--border); }
.wr-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim); }
.wr-table td { padding:11px 16px;font-size:13px;border-bottom:1px solid var(--border);color:var(--text-sub);vertical-align:middle; }
.wr-table tbody tr:last-child td { border-bottom:none; }
.wr-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap; }
.wr-btn   { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);display:inline-flex;align-items:center;gap:6px;white-space:nowrap;border:1px solid var(--border);background:var(--surface);color:var(--text-sub); }
.wr-btn:hover { background:var(--surface2);color:var(--text); }
.wr-btn-primary { background:var(--accent);border-color:var(--accent);color:#fff;box-shadow:0 3px 10px rgba(59,111,212,.25); }
.wr-btn-primary:hover { background:var(--accent);color:#fff;opacity:.88; }
.wr-btn-sm { padding:6px 14px;font-size:12px; }
.wr-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;padding:16px; }
.wr-modal   { background:var(--surface);border-radius:var(--r);box-shadow:0 24px 60px rgba(26,31,54,.25);width:100%;max-width:560px;max-height:calc(100vh - 32px);display:flex;flex-direction:column; }
.wr-modal-head  { padding:20px 22px 0; }
.wr-modal-title { font-size:16px;font-weight:800;color:var(--text);margin:0; }
.wr-modal-sub   { font-size:13px;color:var(--text-dim);margin:4px 0 0;line-height:1.5; }
.wr-modal-body  { padding:14px 0;overflow-y:auto; }
.wr-modal-foot  { padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px; }
.wr-seg   { display:inline-grid;grid-template-columns:repeat(3,auto);gap:2px;padding:2px;border:1.5px solid var(--border);border-radius:8px; }
.wr-seg button { padding:4px 9px;border:none;border-radius:6px;background:transparent;font-size:12px;font-weight:600;color:var(--text-dim);cursor:pointer;font-family:var(--font); }
.wr-seg button.on-received { background:var(--green);color:#fff; }
.wr-seg button.on-damaged  { background:var(--amber);color:#fff; }
.wr-seg button.on-missing  { background:var(--red);color:#fff; }
.wr-label { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px; }
.wr-input { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font); }
.wr-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.wr-error { font-size:12px;color:var(--red);margin-top:5px; }
@media (max-width:768px) {
    .wr-row { grid-template-columns:minmax(0,1fr) auto;gap:6px 12px;padding:12px 16px; }
    .wr-row > :nth-child(2), .wr-row > :nth-child(3) { grid-column:1 / -1; }
}
@media (max-width:640px) {
    .wr-seg button, .wr-btn-sm { min-height:30px !important;min-width:0 !important;padding:4px 9px !important; }
    .wr-table td { padding-left:12px !important;padding-right:12px !important; }
    .wr-modal-foot { flex-direction:column-reverse; }
    .wr-modal-foot .wr-btn { width:100%;justify-content:center; }
}
</style>

<h1 class="wr-title">Returns from shops</h1>
<p class="wr-sub">Stock shops are sending back. Check each box as it arrives, then confirm.</p>

<div class="wr-card">
    <div class="wr-card-head">
        <div class="wr-card-title">Incoming @if($incoming->count())<span class="wr-count">{{ $incoming->count() }}</span>@endif</div>
        <div class="wr-card-sub">In transit — off sale everywhere until received</div>
    </div>
    @forelse($incoming as $r)
        @php $products = $r->boxes->groupBy(fn ($b) => $b->box?->product?->name ?? '—')->map->count(); @endphp
        <div class="wr-row" wire:key="in-{{ $r->id }}">
            <div>
                <div class="wr-mono">{{ $r->return_number }}</div>
                <div class="wr-meta">{{ local_time($r->sent_at)->format('d M, H:i') }}</div>
            </div>
            <div style="min-width:0">
                <div class="wr-main wr-ellip">{{ $products->map(fn ($n, $name) => $name . ($n > 1 ? " ×{$n}" : ''))->implode(', ') }}</div>
                <div class="wr-meta">{{ $r->boxes->count() }} {{ $r->boxes->count() === 1 ? 'box' : 'boxes' }}@if($r->reason) · {{ $r->reason }}@endif</div>
            </div>
            <div style="min-width:0">
                <div class="wr-main wr-ellip">{{ $r->shop?->name }}</div>
                <div class="wr-meta">sent by {{ $r->sentBy?->name }}</div>
            </div>
            <div><button type="button" class="wr-btn wr-btn-primary wr-btn-sm" wire:click="openReceive({{ $r->id }})">Receive</button></div>
        </div>
    @empty
        <div class="wr-empty">Nothing on its way back.</div>
    @endforelse
</div>

<div class="wr-card">
    <div class="wr-card-head"><div class="wr-card-title">History</div></div>
    @if($history->isEmpty())
        <div class="wr-empty">No completed returns yet.</div>
    @else
        <div style="overflow-x:auto">
            <table class="wr-table" style="min-width:620px">
                <thead><tr><th>Return</th><th>From</th><th style="text-align:right">Boxes</th><th>Outcome</th></tr></thead>
                <tbody>
                    @foreach($history as $r)
                        <tr wire:key="h-{{ $r->id }}">
                            <td><div class="wr-mono">{{ $r->return_number }}</div><div class="wr-meta">{{ $r->receipt_notes ?? $r->cancel_reason ?? $r->reason }}</div></td>
                            <td>{{ $r->shop?->name }}</td>
                            <td style="text-align:right" class="wr-mono">{{ $r->boxes_count }}</td>
                            <td>
                                @if($r->status === 'received')
                                    <span class="wr-badge" style="background:{{ $r->has_discrepancy ? 'var(--red-dim)' : 'var(--green-dim)' }};color:{{ $r->has_discrepancy ? 'var(--red)' : 'var(--green)' }}">{{ $r->has_discrepancy ? 'Received · issues' : 'Received' }}</span>
                                    <div class="wr-meta">{{ local_time($r->received_at)->format('d M, H:i') }} · {{ $r->receivedBy?->name }}</div>
                                @else
                                    <span class="wr-badge" style="background:var(--surface2);color:var(--text-dim)">Cancelled by shop</span>
                                    <div class="wr-meta">{{ local_time($r->cancelled_at)?->format('d M, H:i') }} · {{ $r->cancelledBy?->name }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if($receiving)
    <div class="wr-overlay" wire:key="wr-receive-{{ $receiving->id }}" x-data @keydown.escape.window="$wire.closeReceive()" @click.self="$wire.closeReceive()">
        <div class="wr-modal" role="dialog" aria-modal="true" aria-labelledby="wr-r-title">
            <div class="wr-modal-head">
                <h2 class="wr-modal-title" id="wr-r-title">Receive {{ $receiving->return_number }}</h2>
                <p class="wr-modal-sub">From {{ $receiving->shop?->name }} · {{ $receiving->boxes->count() }} {{ $receiving->boxes->count() === 1 ? 'box' : 'boxes' }}. Mark anything that arrived damaged or didn't arrive.</p>
            </div>
            <div class="wr-modal-body">
                <table class="wr-table">
                    <tbody>
                        @foreach($receiving->boxes as $line)
                            @php $o = $outcomes[$line->box_id] ?? 'received'; @endphp
                            <tr wire:key="rb-{{ $line->id }}">
                                <td>
                                    <div class="wr-main">{{ $line->box?->product?->name }}</div>
                                    <div class="wr-meta"><span style="font-family:var(--mono)">{{ $line->box?->box_code }}</span> · {{ $line->items_sent }} items{{ $line->previous_status === 'partial' ? ' (opened)' : '' }}</div>
                                </td>
                                <td style="text-align:right">
                                    <span class="wr-seg" role="radiogroup" aria-label="Outcome for box {{ $line->box?->box_code }}">
                                        @foreach(['received' => 'Received', 'damaged' => 'Damaged', 'missing' => 'Missing'] as $key => $label)
                                            <button type="button" class="{{ $o === $key ? 'on-' . $key : '' }}" wire:click="$set('outcomes.{{ $line->box_id }}', '{{ $key }}')">{{ $label }}</button>
                                        @endforeach
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="padding:14px 22px 0">
                    <label class="wr-label" for="wr-notes">Notes @if(collect($outcomes)->contains(fn ($x) => $x !== 'received'))<span style="color:var(--red)">*</span>@else<span style="font-weight:500;color:var(--text-dim)">(optional)</span>@endif</label>
                    <input id="wr-notes" type="text" class="wr-input" wire:model="notes" placeholder="e.g. Box crushed in transport">
                    @error('notes') <div class="wr-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="wr-modal-foot">
                <button type="button" class="wr-btn" wire:click="closeReceive">Cancel</button>
                <button type="button" class="wr-btn wr-btn-primary" wire:click="confirmReceive" wire:loading.attr="disabled" wire:target="confirmReceive">
                    <span wire:loading.remove wire:target="confirmReceive">Confirm receipt</span>
                    <span wire:loading wire:target="confirmReceive">Saving…</span>
                </button>
            </div>
        </div>
    </div>
@endif
</div>
