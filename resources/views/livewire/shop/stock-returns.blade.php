<div class="sr-page" style="font-family:var(--font)">
<style>
.sr-page { padding:0 0 80px; }
.sr-header { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap; }
.sr-title  { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px; }
.sr-sub    { font-size:13px;color:var(--text-dim);margin:0; }
.sr-sub b  { color:var(--text-sub);font-weight:600; }
.sr-select { padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:var(--surface);color:var(--text);outline:none;font-family:var(--font); }

.sr-card      { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);margin-bottom:16px;min-width:0; }
.sr-card-head { padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap; }
.sr-card-title { font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px; }
.sr-card-sub  { font-size:12px;color:var(--text-dim);margin-top:2px; }
.sr-count     { font-size:11px;font-weight:700;padding:1px 7px;border-radius:20px;font-family:var(--mono); }
.sr-warn      { border-left:3px solid var(--amber); }

.sr-table { width:100%;border-collapse:collapse; }
.sr-table thead tr { border-bottom:2px solid var(--border); }
.sr-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap; }
.sr-table th.num, .sr-table td.num { text-align:right; }
.sr-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr); }
.sr-table tbody tr:last-child { border-bottom:none; }
.sr-table tbody tr:hover { background:var(--surface2); }
.sr-table td { padding:11px 16px;font-size:13px;vertical-align:middle;color:var(--text-sub); }
.sr-name  { font-weight:600;color:var(--text); }
.sr-meta  { font-size:12px;color:var(--text-dim);margin-top:2px; }
.sr-mono  { font-family:var(--mono);font-weight:600;color:var(--text);white-space:nowrap; }
.sr-qty   { display:inline-flex;align-items:center;gap:6px;justify-content:flex-end; }
.sr-num   { width:72px;padding:6px 8px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:var(--mono);text-align:right;background:var(--surface);color:var(--text);outline:none;-moz-appearance:textfield; }
.sr-num:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.sr-link  { background:none;border:none;padding:0;cursor:pointer;font-size:12px;font-weight:600;color:var(--accent);font-family:var(--font);white-space:nowrap; }
.sr-link:hover { text-decoration:underline; }
.sr-error { font-size:11px;color:var(--red);margin-top:3px;text-align:right; }

.sr-foot  { display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;padding:14px 20px;border-top:1px solid var(--border); }
.sr-label { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px; }
.sr-input { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font); }
.sr-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim); }
.sr-btn   { padding:9px 16px;border-radius:var(--rsm);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr);display:inline-flex;align-items:center;gap:6px;white-space:nowrap;border:1px solid var(--border);background:var(--surface);color:var(--text-sub); }
.sr-btn:hover { background:var(--surface2);color:var(--text); }
.sr-btn-primary { background:var(--accent);border-color:var(--accent);color:#fff;box-shadow:0 3px 10px rgba(59,111,212,.25); }
.sr-btn-primary:hover { background:var(--accent);color:#fff;opacity:.88; }
.sr-btn-primary:disabled { opacity:.5;cursor:not-allowed; }
.sr-btn-sm { padding:5px 11px;font-size:12px; }
.sr-btn-red { background:var(--red);border-color:var(--red);color:#fff; }
.sr-btn-red:hover { background:var(--red);color:#fff;opacity:.9; }

.sr-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap; }
.sr-dot   { width:6px;height:6px;border-radius:50%; }
.sr-empty { padding:40px 20px;text-align:center;font-size:13px;color:var(--text-dim); }

.sr-overlay { position:fixed;inset:0;z-index:400;background:rgba(26,31,54,.45);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;padding:16px; }
.sr-modal   { background:var(--surface);border-radius:var(--r);box-shadow:0 24px 60px rgba(26,31,54,.25);width:100%;max-width:460px;max-height:calc(100vh - 32px);display:flex;flex-direction:column; }
.sr-modal-head  { padding:20px 22px 0; }
.sr-modal-title { font-size:16px;font-weight:800;color:var(--text);margin:0; }
.sr-modal-sub   { font-size:13px;color:var(--text-dim);margin:4px 0 0;line-height:1.5; }
.sr-modal-body  { padding:14px 22px;overflow-y:auto; }
.sr-modal-foot  { padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px; }
.sr-row { display:flex;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px; }
.sr-row:last-child { border-bottom:none; }

@media (max-width:640px) {
    .sr-table thead { display:none; }
    .sr-table, .sr-table tbody { display:block; }
    .sr-table tbody tr { display:grid;grid-template-columns:minmax(0,1fr) auto;gap:4px 12px;padding:12px 14px;align-items:center; }
    .sr-table td { padding:0 !important; }
    .sr-hide-mob { display:none !important; }
    .sr-link, .sr-btn-sm { min-height:32px !important;min-width:0 !important;padding:5px 11px !important; }
    .sr-modal-foot { flex-direction:column-reverse; }
    .sr-modal-foot .sr-btn { width:100%;justify-content:center; }
}
</style>

@php
    $statusBadge = fn ($r) => match ($r->status) {
        'in_transit' => ['In transit', 'var(--amber-dim)', 'var(--amber)'],
        'received'   => [$r->has_discrepancy ? 'Received · issues' : 'Received', $r->has_discrepancy ? 'var(--red-dim)' : 'var(--green-dim)', $r->has_discrepancy ? 'var(--red)' : 'var(--green)'],
        default      => ['Cancelled', 'var(--surface2)', 'var(--text-dim)'],
    };
@endphp

{{-- ── Header ── --}}
<div class="sr-header">
    <div>
        <h1 class="sr-title">Return to warehouse</h1>
        <p class="sr-sub">
            @if($shop)<b>{{ $shop->name }}</b> → {{ $shop->defaultWarehouse?->name ?? 'no default warehouse' }} · Sells: {{ $shop->sellsLabel() }}@endif
        </p>
    </div>
    @if($shops->count() > 1)
        <select class="sr-select" wire:model.live="shopId" aria-label="Shop">
            @foreach($shops as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
    @endif
</div>

{{-- ── Stock to send ── --}}
@if($notSold->isNotEmpty())
<div class="sr-card sr-warn">
    <div class="sr-card-head">
        <div>
            <div class="sr-card-title">Not sold at this shop <span class="sr-count" style="background:var(--amber-dim);color:var(--amber)">{{ $notSold->count() }}</span></div>
            <div class="sr-card-sub">Outside this shop's categories — it can't be sold here. Send it back so another shop can.</div>
        </div>
    </div>
    @include('livewire.shop.partials.stock-return-rows', ['rows' => $notSold])
</div>
@endif

<div class="sr-card" x-data="{ open: {{ $notSold->isEmpty() ? 'true' : 'false' }} }">
    <div class="sr-card-head">
        <div>
            <div class="sr-card-title">{{ $notSold->isEmpty() ? 'Shop stock' : 'Other stock' }} <span class="sr-count" style="background:var(--surface2);color:var(--text-dim)">{{ $sold->count() }}</span></div>
            <div class="sr-card-sub">Stock this shop sells — send back only if you need to (overstock, wrong delivery…)</div>
        </div>
        @if($notSold->isNotEmpty())
            <button type="button" class="sr-link" @click="open = !open" x-text="open ? 'Hide' : 'Show'"></button>
        @endif
    </div>
    <div x-show="open" @if($notSold->isNotEmpty()) x-cloak @endif>
        @if($sold->isEmpty())
            <div class="sr-empty">No stock at this shop.</div>
        @else
            @include('livewire.shop.partials.stock-return-rows', ['rows' => $sold])
        @endif
    </div>
</div>

@if($notSold->isNotEmpty() || $sold->isNotEmpty())
<div class="sr-card">
    <div class="sr-foot">
        <div style="flex:1;min-width:220px">
            <label class="sr-label" for="sr-reason">Reason <span style="font-weight:500;color:var(--text-dim)">(optional)</span></label>
            <input id="sr-reason" type="text" class="sr-input" wire:model="reason" placeholder="e.g. Not sold at this shop, overstock">
        </div>
        <button type="button" class="sr-btn sr-btn-primary" wire:click="review" @disabled($selectedBoxes === 0)>
            Review · {{ $selectedBoxes }} {{ $selectedBoxes === 1 ? 'box' : 'boxes' }}
        </button>
    </div>
    @error('send') <div class="sr-error" style="padding:0 20px 12px;text-align:left">{{ $message }}</div> @enderror
</div>
@endif

{{-- ── History ── --}}
<div class="sr-card">
    <div class="sr-card-head">
        <div><div class="sr-card-title">Returns sent</div><div class="sr-card-sub">Boxes in transit are off sale until the warehouse receives them</div></div>
    </div>
    @if($returns->isEmpty())
        <div class="sr-empty">No returns yet.</div>
    @else
        <table class="sr-table">
            <thead><tr><th>Return</th><th class="sr-hide-mob">Sent</th><th class="num">Boxes</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach($returns as $r)
                    @php [$bl, $bb, $bc] = $statusBadge($r); @endphp
                    <tr wire:key="ret-{{ $r->id }}" x-data="{ c:false }">
                        <td>
                            <div class="sr-mono">{{ $r->return_number }}</div>
                            <div class="sr-meta">to {{ $r->warehouse?->name }}@if($r->reason) · {{ $r->reason }}@endif</div>
                        </td>
                        <td class="sr-hide-mob">
                            <div>{{ local_time($r->sent_at)->format('d M, H:i') }}</div>
                            <div class="sr-meta">by {{ $r->sentBy?->name }}</div>
                        </td>
                        <td class="num sr-mono">{{ $r->boxes_count }}</td>
                        <td>
                            <span class="sr-badge" style="background:{{ $bb }};color:{{ $bc }}"><span class="sr-dot" style="background:{{ $bc }}"></span>{{ $bl }}</span>
                            @if($r->received_at)<div class="sr-meta">{{ local_time($r->received_at)->format('d M, H:i') }} · {{ $r->receivedBy?->name }}</div>@endif
                        </td>
                        <td class="num">
                            @if($r->status === 'in_transit')
                                <button type="button" class="sr-btn sr-btn-sm" x-show="!c" @click="c = true">Cancel</button>
                                <span x-show="c" x-cloak style="display:inline-flex;gap:4px">
                                    <button type="button" class="sr-btn sr-btn-sm sr-btn-red" wire:click="cancelReturn({{ $r->id }})">Cancel return</button>
                                    <button type="button" class="sr-btn sr-btn-sm" @click="c = false">Keep</button>
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ── Review modal ── --}}
@if($showReview)
    @php $picked = collect($send)->filter(fn ($n) => (int) $n > 0); @endphp
    <div class="sr-overlay" x-data @keydown.escape.window="$wire.set('showReview', false)" @click.self="$wire.set('showReview', false)">
        <div class="sr-modal" role="dialog" aria-modal="true" aria-labelledby="sr-review-title">
            <div class="sr-modal-head">
                <h2 class="sr-modal-title" id="sr-review-title">Send {{ $selectedBoxes }} {{ $selectedBoxes === 1 ? 'box' : 'boxes' }} back?</h2>
                <p class="sr-modal-sub">To {{ $shop?->defaultWarehouse?->name }}. They come off sale now and move to the warehouse once it confirms receipt.</p>
            </div>
            <div class="sr-modal-body">
                @foreach($picked as $productId => $n)
                    <div class="sr-row"><span style="color:var(--text)">{{ $rowsById[$productId]->name ?? '—' }}</span><span class="sr-mono">{{ $n }} {{ (int) $n === 1 ? 'box' : 'boxes' }}</span></div>
                @endforeach
                @if($reason)<div class="sr-meta" style="margin-top:8px">Reason: {{ $reason }}</div>@endif
            </div>
            <div class="sr-modal-foot">
                <button type="button" class="sr-btn" wire:click="$set('showReview', false)">Back</button>
                <button type="button" class="sr-btn sr-btn-primary" wire:click="confirmSend" wire:loading.attr="disabled" wire:target="confirmSend">
                    <span wire:loading.remove wire:target="confirmSend">Send back</span>
                    <span wire:loading wire:target="confirmSend">Sending…</span>
                </button>
            </div>
        </div>
    </div>
@endif
</div>
