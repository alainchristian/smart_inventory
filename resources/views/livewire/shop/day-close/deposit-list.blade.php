<div style="font-family:var(--font)">
<style>
.dl-table { width:100%;border-collapse:collapse; }
.dl-table thead tr { border-bottom:2px solid var(--border); }
.dl-table th { padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);white-space:nowrap; }
.dl-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr); }
.dl-table tbody tr:hover { background:var(--surface2); }
.dl-table td { padding:11px 16px;font-size:13px;vertical-align:middle;color:var(--text-sub); }
.dl-table tfoot td { padding:12px 16px;font-size:13px;border-top:2px solid var(--border); }
.dl-time  { font-family:var(--mono);font-size:12px;color:var(--text-dim);white-space:nowrap; }
.dl-src   { display:inline-block;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;white-space:nowrap; }
.dl-amt   { text-align:right;font-family:var(--mono);font-weight:700;color:var(--text);white-space:nowrap; }
.dl-act   { text-align:right;white-space:nowrap;width:1%; }
.dl-action { padding:4px 10px;border-radius:7px;border:1.5px solid var(--border);background:transparent;font-size:12px;font-weight:600;cursor:pointer;
             font-family:var(--font);color:var(--text-sub);transition:all var(--tr);white-space:nowrap; }
.dl-action:hover { border-color:var(--red);color:var(--red); }
.dl-action.yes { background:var(--red);border-color:var(--red);color:#fff; }
.dl-empty { padding:40px 20px;text-align:center;font-size:13px;color:var(--text-dim); }
@media (max-width:640px) {
    .dl-action { min-height:30px !important;min-width:0 !important;padding:4px 10px !important; }
    .dl-table td { padding:0 !important; }
    .dl-table tfoot td { padding:10px 14px !important; }
    .dl-table thead { display:none; }
    .dl-table tbody, .dl-table tfoot { display:block; }
    .dl-table tbody tr { display:grid;grid-template-columns:auto minmax(0,1fr) auto auto;gap:10px;padding:10px 14px;align-items:center; }
    .dl-table tfoot tr { display:flex;justify-content:space-between; }
    .dl-table td { padding:0;min-width:0; }
    .dl-table tfoot td { padding:10px 14px; }
    .dl-hide-mob { display:none !important; }
}
</style>

@if ($deposits->isEmpty())
    <div class="dl-empty">No bank deposits recorded for this session.</div>
@else
    <table class="dl-table">
        <thead>
            <tr>
                <th style="width:70px">Time</th>
                <th style="width:110px">From</th>
                <th>Reference</th>
                <th style="text-align:right">Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($deposits as $deposit)
                @php $isMomo = ($deposit->source ?? 'cash') === 'mobile_money'; @endphp
                <tr wire:key="dl-{{ $deposit->id }}" x-data="{ c:false }">
                    <td class="dl-time dl-hide-mob">{{ local_time($deposit->deposited_at)->format('H:i') }}</td>
                    <td><span class="dl-src" style="background:{{ $isMomo ? 'var(--accent-dim)' : 'var(--green-dim)' }};color:{{ $isMomo ? 'var(--accent)' : 'var(--green)' }}">{{ $isMomo ? 'MoMo' : 'Cash' }}</span></td>
                    <td style="min-width:0">
                        <div style="font-family:var(--mono);color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $deposit->bank_reference ?: '—' }}</div>
                        @if ($deposit->notes)
                            <div style="font-size:12px;color:var(--text-dim);overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $deposit->notes }}">{{ $deposit->notes }}</div>
                        @endif
                    </td>
                    <td class="dl-amt">{{ number_format($deposit->amount) }}</td>
                    <td class="dl-act">
                        @if ($session?->isEditable())
                            <button type="button" class="dl-action" x-show="!c" @click="c = true">Void</button>
                            <span x-show="c" x-cloak style="display:inline-flex;gap:4px">
                                <button type="button" class="dl-action yes" wire:click="voidDeposit({{ $deposit->id }})">Void</button>
                                <button type="button" class="dl-action" @click="c = false">Keep</button>
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="dl-hide-mob" style="font-weight:700;color:var(--text)">Total</td>
                <td class="dl-amt" style="font-size:14px">{{ number_format($deposits->sum('amount')) }} <span style="font-size:10px;font-weight:500;color:var(--text-dim)">RWF</span></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endif
</div>
