<style>
.dc-snap-tile     { padding:12px 14px; }
.dc-snap-label    { font-size:9px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;color:var(--text-dim);margin-bottom:4px; }
.dc-snap-num      { font-size:16px;font-weight:800;font-family:var(--mono);line-height:1.1; }
.dc-snap-sub      { font-size:9px;color:var(--text-dim);margin-top:2px; }
.dc-snap-brk      { font-size:9px; }

/* Tiles that have a right-side breakdown */
.dc-snap-tile-brk { display:flex;align-items:flex-start;justify-content:space-between;gap:6px; }
.dc-snap-brkwrap  { text-align:right;flex-shrink:0;padding-top:14px; }

@media (max-width:640px) {
    .dc-snap-tile      { padding:9px 10px; }
    .dc-snap-label     { font-size:8px; }
    .dc-snap-num       { font-size:12px; }
    .dc-snap-sub       { font-size:8px; }
    .dc-snap-brk       { font-size:8px; }

    /* Stack breakdown below the number instead of floating right */
    .dc-snap-tile-brk  { flex-direction:column; gap:3px; }
    .dc-snap-brkwrap   { text-align:left;padding-top:0;display:flex;flex-wrap:wrap;gap:4px; }

    /* Collapse the multi-column grids so tiles never overflow the viewport.
       Balance row: 2 columns (Cash+MoMo, Bank on next row if present)
       Activity row: 2 columns (Revenue+Expenses, Withdrawals below) */
    .dc-snap-grid-top  { grid-template-columns:1fr 1fr !important; }
    .dc-snap-grid-act  { grid-template-columns:1fr 1fr !important; }
}

/* ── Extra-narrow (360px and below) ── */
@media (max-width:400px) {
    /* Single column on very small phones */
    .dc-snap-grid-top  { grid-template-columns:1fr !important; }
    .dc-snap-grid-act  { grid-template-columns:1fr 1fr !important; }
    /* Shrink numbers further */
    .dc-snap-num       { font-size:11px; }
    .dc-snap-brk       { font-size:7px; }
    .dc-snap-tile      { padding:7px 8px; }
}
</style>

<div @if($session?->isOpen()) wire:poll.30s @endif>

    {{-- ── Header ── --}}
    <div class="dc-card-head" style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
        <div style="display:flex;align-items:center;gap:6px;">
            @if ($session?->isOpen())
                <span style="width:6px;height:6px;border-radius:50%;background:var(--green);
                             flex-shrink:0;animation:pulse 2s infinite;display:inline-block;"></span>
            @endif
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-dim);">
                @if ($session)
                    @if ($session->session_date->isToday())
                        Today's Snapshot
                    @else
                        Snapshot — {{ $session->session_date->format('d M Y') }}
                    @endif
                @else
                    Snapshot
                @endif
            </span>
        </div>
        @if ($session && ! $session->isOpen())
            <span style="font-size:9px;padding:1px 7px;border-radius:999px;font-weight:600;
                         background:var(--surface2);color:var(--text-dim);border:1px solid var(--border);">
                {{ ucfirst($session->status) }}
            </span>
        @endif
    </div>

    {{-- ── Balances row ── --}}
    @php $topCols = $settingAllowBank ? 3 : 2; @endphp
    <div class="dc-snap-grid-top" style="display:grid;grid-template-columns:repeat({{ $topCols }},1fr);background:var(--surface);border-bottom:1px solid var(--border);">

        {{-- Cash in drawer — with formula breakdown --}}
        @php
            $cashOpening  = $snap['opening_balance']     ?? 0;
            $cashSales    = $snap['total_sales_cash']    ?? 0;
            $cashRepaid   = $snap['total_repayments_cash'] ?? 0;
            $cashExpenses = $snap['total_expenses_cash'] ?? 0;
            $cashWithdraw = $snap['total_withdrawals_cash'] ?? 0;
            $cashDeposits = $snap['cash_deposits']       ?? 0;
            $cashRefunds  = $snap['total_refunds_cash']  ?? 0;
            $expectedCash = $snap['expected_cash']       ?? 0;
        @endphp
        <div class="dc-snap-tile dc-snap-tile-brk" style="border-right:1px solid var(--border);">
            <div>
                <div class="dc-snap-label">Cash in Drawer</div>
                <div class="dc-snap-num" style="color:var(--green);">{{ number_format($expectedCash) }}</div>
                <div class="dc-snap-sub">expected</div>
            </div>
            <div class="dc-snap-brkwrap">
                <div class="dc-snap-brk" style="color:var(--text-dim);">
                    Opening <span style="font-family:var(--mono);font-weight:600;">{{ number_format($cashOpening) }}</span>
                </div>
                @if ($cashSales > 0)
                    <div class="dc-snap-brk" style="color:var(--green);">
                        +Sales <span style="font-family:var(--mono);font-weight:600;">{{ number_format($cashSales) }}</span>
                    </div>
                @endif
                @if ($cashRepaid > 0)
                    <div class="dc-snap-brk" style="color:var(--green);">
                        +Repaid <span style="font-family:var(--mono);font-weight:600;">{{ number_format($cashRepaid) }}</span>
                    </div>
                @endif
                @if ($cashExpenses > 0)
                    <div class="dc-snap-brk" style="color:var(--red);">
                        −Exp <span style="font-family:var(--mono);font-weight:600;">{{ number_format($cashExpenses) }}</span>
                    </div>
                @endif
                @if ($cashWithdraw > 0)
                    <div class="dc-snap-brk" style="color:var(--amber);">
                        −Wthd <span style="font-family:var(--mono);font-weight:600;">{{ number_format($cashWithdraw) }}</span>
                    </div>
                @endif
                @if ($cashDeposits > 0)
                    <div class="dc-snap-brk" style="color:var(--red);">
                        −Dep <span style="font-family:var(--mono);font-weight:600;">{{ number_format($cashDeposits) }}</span>
                    </div>
                @endif
                @if ($cashRefunds > 0)
                    <div class="dc-snap-brk" style="color:var(--red);">
                        −Refunds <span style="font-family:var(--mono);font-weight:600;">{{ number_format($cashRefunds) }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- MoMo balance --}}
        @php $momo = $snap['momo_available'] ?? 0; @endphp
        <div class="dc-snap-tile" @if($settingAllowBank) style="border-right:1px solid var(--border);" @endif>
            <div class="dc-snap-label">MoMo</div>
            <div class="dc-snap-num" style="color:{{ $momo >= 0 ? '#0ea5e9' : 'var(--red)' }};">{{ number_format($momo) }}</div>
            <div class="dc-snap-sub">available</div>
        </div>

        {{-- Bank balance — only if owner allows bank transfer --}}
        @if ($settingAllowBank)
            @php
                $bank     = $snap['bank_available'] ?? 0;
                $bankCash = $snap['cash_deposits']  ?? 0;
                $bankMomo = $snap['momo_deposits']  ?? 0;
            @endphp
            <div class="dc-snap-tile dc-snap-tile-brk">
                <div>
                    <div class="dc-snap-label">Bank</div>
                    <div class="dc-snap-num" style="color:{{ $bank >= 0 ? '#7c3aed' : 'var(--red)' }};">{{ number_format($bank) }}</div>
                    <div class="dc-snap-sub">available</div>
                </div>
                @if ($bankCash > 0 || $bankMomo > 0)
                    <div class="dc-snap-brkwrap">
                        @if ($bankCash > 0)
                            <div class="dc-snap-brk" style="color:var(--green);">
                                Cash <span style="font-family:var(--mono);font-weight:600;">{{ number_format($bankCash) }}</span>
                            </div>
                        @endif
                        @if ($bankMomo > 0)
                            <div class="dc-snap-brk" style="color:#0ea5e9;">
                                MoMo <span style="font-family:var(--mono);font-weight:600;">{{ number_format($bankMomo) }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

    </div>

    {{-- ── Activity row ── --}}
    <div class="dc-snap-grid-act" style="display:grid;grid-template-columns:repeat(3,1fr);background:var(--surface);">

        {{-- Revenue — with payment channel breakdown --}}
        @php
            $tx        = $snap['transaction_count']       ?? 0;
            $revCash   = $snap['total_sales_cash']        ?? 0;
            $revMomo   = $snap['total_sales_momo']        ?? 0;
            $revBank   = $snap['total_sales_bank_transfer'] ?? 0;
            $revCard   = $snap['total_sales_card']        ?? 0;
            $revCredit = $snap['total_sales_credit']      ?? 0;
            $revTotal  = $snap['total_sales']             ?? 0;
        @endphp
        <div class="dc-snap-tile dc-snap-tile-brk" style="border-right:1px solid var(--border);">
            <div>
                <div class="dc-snap-label">Revenue</div>
                <div class="dc-snap-num" style="color:var(--accent);">{{ number_format($revTotal) }}</div>
                <div class="dc-snap-sub">{{ $tx }} {{ $tx === 1 ? 'sale' : 'sales' }}</div>
            </div>
            @if ($revTotal > 0)
                <div class="dc-snap-brkwrap">
                    @if ($revCash > 0)
                        <div class="dc-snap-brk" style="color:var(--green);">
                            Cash <span style="font-family:var(--mono);font-weight:600;">{{ number_format($revCash) }}</span>
                        </div>
                    @endif
                    @if ($revMomo > 0)
                        <div class="dc-snap-brk" style="color:#0ea5e9;">
                            MoMo <span style="font-family:var(--mono);font-weight:600;">{{ number_format($revMomo) }}</span>
                        </div>
                    @endif
                    @if ($settingAllowBank && $revBank > 0)
                        <div class="dc-snap-brk" style="color:#7c3aed;">
                            Bank <span style="font-family:var(--mono);font-weight:600;">{{ number_format($revBank) }}</span>
                        </div>
                    @endif
                    @if ($settingAllowCard && $revCard > 0)
                        <div class="dc-snap-brk" style="color:var(--text-dim);">
                            Card <span style="font-family:var(--mono);font-weight:600;">{{ number_format($revCard) }}</span>
                        </div>
                    @endif
                    @if ($revCredit > 0)
                        <div class="dc-snap-brk" style="color:var(--amber);">
                            Credit <span style="font-family:var(--mono);font-weight:600;">{{ number_format($revCredit) }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Expenses — channel breakdown on right --}}
        @php
            $exp     = $snap['total_expenses']      ?? 0;
            $expCash = $snap['total_expenses_cash'] ?? 0;
            $expMomo = $snap['total_expenses_momo'] ?? 0;
            $expBank = $snap['total_expenses_bank'] ?? 0;
            $ec      = $snap['expense_count']       ?? 0;
        @endphp
        <div class="dc-snap-tile dc-snap-tile-brk" style="border-right:1px solid var(--border);">
            <div>
                <div class="dc-snap-label">Expenses</div>
                <div class="dc-snap-num" style="color:{{ $exp > 0 ? 'var(--red)' : 'var(--text-dim)' }};">{{ number_format($exp) }}</div>
                <div class="dc-snap-sub">{{ $ec }} {{ $ec === 1 ? 'entry' : 'entries' }}</div>
            </div>
            @if ($exp > 0)
                <div class="dc-snap-brkwrap">
                    @if ($expCash > 0)
                        <div class="dc-snap-brk" style="color:var(--green);">
                            Cash <span style="font-family:var(--mono);font-weight:600;">{{ number_format($expCash) }}</span>
                        </div>
                    @endif
                    @if ($expMomo > 0)
                        <div class="dc-snap-brk" style="color:#0ea5e9;">
                            MoMo <span style="font-family:var(--mono);font-weight:600;">{{ number_format($expMomo) }}</span>
                        </div>
                    @endif
                    @if ($expBank > 0)
                        <div class="dc-snap-brk" style="color:#7c3aed;">
                            Bank <span style="font-family:var(--mono);font-weight:600;">{{ number_format($expBank) }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Withdrawals — method breakdown on right --}}
        @php
            $wd     = $snap['total_withdrawals']      ?? 0;
            $wdCash = $snap['total_withdrawals_cash'] ?? 0;
            $wdMomo = $snap['total_withdrawals_momo'] ?? 0;
            $wc     = $snap['withdrawal_count']       ?? 0;
        @endphp
        <div class="dc-snap-tile dc-snap-tile-brk">
            <div>
                <div class="dc-snap-label">Withdrawals</div>
                <div class="dc-snap-num" style="color:{{ $wd > 0 ? 'var(--amber)' : 'var(--text-dim)' }};">{{ number_format($wd) }}</div>
                <div class="dc-snap-sub">{{ $wc }} {{ $wc === 1 ? 'entry' : 'entries' }}</div>
            </div>
            @if ($wd > 0)
                <div class="dc-snap-brkwrap">
                    @if ($wdCash > 0)
                        <div class="dc-snap-brk" style="color:var(--green);">
                            Cash <span style="font-family:var(--mono);font-weight:600;">{{ number_format($wdCash) }}</span>
                        </div>
                    @endif
                    @if ($wdMomo > 0)
                        <div class="dc-snap-brk" style="color:#0ea5e9;">
                            MoMo <span style="font-family:var(--mono);font-weight:600;">{{ number_format($wdMomo) }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

    </div>

</div>
