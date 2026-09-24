<div class="af-card" style="font-family:var(--font)" x-data="{ f: 'all' }" wire:poll.30s="refresh">
<style>
.af-card  { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);min-width:0; }
.af-head  { padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap; }
.af-title { font-size:13px;font-weight:700;color:var(--text);margin:0; }
.af-sub   { font-size:12px;color:var(--text-dim);margin-top:2px; }
.af-pills { display:flex;gap:4px;overflow-x:auto;scrollbar-width:none;flex-wrap:nowrap;min-width:0; }
.af-pills::-webkit-scrollbar { display:none; }
.af-pill  { padding:5px 11px;border-radius:6px;font-size:12px;font-weight:600;border:1px solid transparent;background:transparent;
            color:var(--text-dim);cursor:pointer;white-space:nowrap;flex-shrink:0;transition:all var(--tr);font-family:var(--font); }
.af-pill:hover  { background:var(--surface2);color:var(--text);border-color:var(--border); }
.af-pill.active { background:var(--accent);color:#fff;border-color:var(--accent); }
.af-pill-n { font-family:var(--mono);font-size:11px;opacity:.75;margin-left:3px; }

.af-table { width:100%;border-collapse:collapse; }
.af-table thead tr { border-bottom:2px solid var(--border); }
.af-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap; }
.af-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr); }
.af-table tbody tr:last-child { border-bottom:none; }
.af-table tbody tr:hover { background:var(--surface2); }
.af-table td { padding:0 16px;height:46px;font-size:13px;vertical-align:middle;color:var(--text-sub); }
.af-time  { font-family:var(--mono);font-size:12px;color:var(--text-dim);white-space:nowrap; }
.af-type  { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap; }
.af-desc  { display:block;min-width:0;color:var(--text);max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.af-wh    { font-size:10px;font-weight:700;padding:1px 6px;border-radius:5px;background:var(--accent-dim);color:var(--accent);margin-left:6px;flex-shrink:0; }
.af-amt   { text-align:right;font-family:var(--mono);font-weight:700;white-space:nowrap; }
.af-act   { text-align:right;white-space:nowrap;width:1%; }
.af-action { padding:4px 10px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;
             cursor:pointer;font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap; }
.af-action:hover { border-color:var(--red);color:var(--red); }
.af-action.yes   { background:var(--red);border-color:var(--red);color:#fff; }
.af-action.yes:hover { opacity:.9;color:#fff; }

.af-foot  { display:grid;grid-template-columns:repeat(3,1fr);border-top:2px solid var(--border); }
.af-foot > div { padding:12px 20px;border-right:1px solid var(--border); }
.af-foot > div:last-child { border-right:none; }
.af-foot-l { font-size:11px;color:var(--text-dim);margin-bottom:3px; }
.af-foot-v { font-size:14px;font-weight:800;font-family:var(--mono);white-space:nowrap; }

.af-empty       { padding:48px 20px;text-align:center; }
.af-empty-title { font-size:14px;font-weight:700;color:var(--text-sub);margin-bottom:4px; }
.af-empty-sub   { font-size:13px;color:var(--text-dim); }
.af-error { margin:12px 20px 0;padding:8px 12px;border-radius:var(--rsm);border-left:3px solid var(--red);font-size:12px;color:var(--red);background:var(--red-dim); }

@media (max-width:768px) { .af-hide-mob { display:none !important; } }
@media (max-width:640px) {
    .af-action { min-height:30px !important;min-width:0 !important;padding:4px 10px !important; }
    .af-pill { min-height:30px !important;min-width:0 !important;padding:5px 10px !important; }
    .af-table td { padding:0 !important; }
    .af-head { padding:12px 14px; }
    .af-table thead { display:none; }
    .af-table tbody { display:block; }
    .af-table tbody tr { display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:2px 10px;padding:10px 14px;align-items:center; }
    .af-table tbody td:nth-child(1) { grid-row:2;grid-column:1;font-size:11px; }
    .af-table tbody td:nth-child(3) { grid-row:1;grid-column:1; }
    .af-table tbody td:nth-child(4) { grid-row:1;grid-column:2; }
    .af-table tbody td:nth-child(5) { grid-row:1 / span 2;grid-column:3;width:auto; }
    .af-table td { padding:0;height:auto;min-width:0; }
    .af-desc { max-width:none;white-space:normal;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;line-height:1.35;word-break:break-word; }
    .af-foot > div { padding:10px 14px; }
}
</style>

@php
    $typeMeta = [
        'sale'         => ['Sale',       'var(--green-dim)',  'var(--green)'],
        'repayment'    => ['Repayment',  'var(--accent-dim)', 'var(--accent)'],
        'return'       => ['Refund',     'var(--red-dim)',    'var(--red)'],
        'expense'      => ['Expense',    'var(--red-dim)',    'var(--red)'],
        'withdrawal'   => ['Withdrawal', 'var(--amber-dim)',  'var(--amber)'],
        'bank_deposit' => ['Deposit',    'var(--violet-dim)', 'var(--violet)'],
    ];
    $filters = [
        'all'      => ['All',        null],
        'in'       => ['Money in',   ['sale', 'repayment']],
        'expense'  => ['Expenses',   ['expense']],
        'withdrawal' => ['Withdrawals', ['withdrawal']],
        'bank_deposit' => ['Deposits', ['bank_deposit']],
        'return'   => ['Refunds',    ['return']],
    ];
    $net = $totalIn - $totalOut;
@endphp

<div class="af-head">
    <div>
        <div class="af-title">Today's activity</div>
        <div class="af-sub">{{ $activities->count() }} {{ $activities->count() === 1 ? 'entry' : 'entries' }} · newest first</div>
    </div>
    <div class="af-pills">
        @foreach ($filters as $key => $__f)
            @php
                [$label, $types] = $__f;
                $n = $types === null ? $activities->count() : $activities->whereIn('type', $types)->count();
            @endphp
            @if ($key === 'all' || $n > 0)
                <button type="button" class="af-pill" :class="{ active: f === '{{ $key }}' }" @click="f = '{{ $key }}'">
                    {{ $label }}<span class="af-pill-n">{{ $n }}</span>
                </button>
            @endif
        @endforeach
    </div>
</div>

@if (session()->has('error'))
    <div class="af-error">{{ session('error') }}</div>
@endif

@if ($activities->isEmpty())
    <div class="af-empty">
        <div class="af-empty-title">No activity yet</div>
        <div class="af-empty-sub">Sales, expenses and cash movements will appear here as they're recorded.</div>
    </div>
@else
    <div>
        <table class="af-table">
            <thead>
                <tr>
                    <th style="width:70px">Time</th>
                    <th style="width:120px" class="af-hide-mob">Type</th>
                    <th>Description</th>
                    <th style="text-align:right">Amount</th>
                    @if ($isOpen)<th></th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach ($activities as $item)
                    @php
                        [$tLabel, $tBg, $tColor] = $typeMeta[$item['type']] ?? [ucfirst($item['type']), 'var(--surface2)', 'var(--text-dim)'];
                        $isIn  = in_array($item['type'], ['sale', 'repayment']);
                        $group = $isIn ? 'in' : $item['type'];
                        $voidMethod = match ($item['type']) {
                            'expense'      => 'voidExpense',
                            'bank_deposit' => 'voidDeposit',
                            default        => 'voidWithdrawal',
                        };
                    @endphp
                    <tr wire:key="af-{{ $item['type'] }}-{{ $item['id'] }}"
                        x-show="f === 'all' || f === '{{ $group }}'"
                        x-data="{ c:false }">
                        <td class="af-time">{{ local_time($item['time'])?->format('H:i') }}</td>
                        <td class="af-hide-mob"><span class="af-type" style="background:{{ $tBg }};color:{{ $tColor }}">{{ $tLabel }}</span></td>
                        <td>
                            <div style="display:flex;align-items:center;min-width:0">
                                <span class="af-desc" title="{{ $item['label'] }}" style="{{ $item['system'] ? 'font-style:italic;color:var(--text-dim)' : '' }}">{{ $item['label'] }}</span>
                                @if (($item['fulfillment_type'] ?? null) === 'warehouse_direct')<span class="af-wh" title="Sold from warehouse stock">WH</span>@endif
                            </div>
                        </td>
                        <td class="af-amt" style="color:{{ $isIn ? 'var(--green)' : 'var(--text)' }}">
                            {{ $isIn ? '+' : '−' }}{{ number_format($item['amount']) }}
                        </td>
                        @if ($isOpen)
                            <td class="af-act">
                                @if ($item['voidable'])
                                    <button type="button" class="af-action" x-show="!c" @click="c = true">Void</button>
                                    <span x-show="c" x-cloak style="display:inline-flex;gap:4px">
                                        <button type="button" class="af-action yes" wire:click="{{ $voidMethod }}({{ $item['id'] }})">Void</button>
                                        <button type="button" class="af-action" @click="c = false">Keep</button>
                                    </span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="af-foot">
        <div><div class="af-foot-l">Money in</div><div class="af-foot-v" style="color:var(--green)">+{{ number_format($totalIn) }}</div></div>
        <div><div class="af-foot-l">Money out</div><div class="af-foot-v" style="color:var(--red)">−{{ number_format($totalOut) }}</div></div>
        <div><div class="af-foot-l">Net</div><div class="af-foot-v" style="color:{{ $net >= 0 ? 'var(--text)' : 'var(--red)' }}">{{ $net >= 0 ? '+' : '−' }}{{ number_format(abs($net)) }}</div></div>
    </div>
@endif
</div>
