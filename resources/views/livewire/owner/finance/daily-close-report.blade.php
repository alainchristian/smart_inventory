<div>
    {{-- Flash --}}
    @if (session()->has('success'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:#ccfbf1;color:#0f766e;">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:#ffe4e6;color:#e11d48;">{{ session('error') }}</div>
    @endif

    {{-- ── Day Navigation ── --}}
    <div class="mb-6 flex items-center gap-2">
        <button wire:click="previousDay" class="p-2 rounded-lg flex-shrink-0" style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <input type="date" wire:model.live="reportDate"
               class="flex-1 sm:flex-none px-3 py-2 rounded-lg text-sm font-medium"
               style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);"
               max="{{ today()->toDateString() }}">
        <button wire:click="nextDay" class="p-2 rounded-lg flex-shrink-0"
                style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);{{ \Carbon\Carbon::parse($reportDate)->isToday() ? 'opacity:.4;cursor:default;' : '' }}"
                @if(\Carbon\Carbon::parse($reportDate)->isToday()) disabled @endif>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7 7 7"/></svg>
        </button>
        @unless(\Carbon\Carbon::parse($reportDate)->isToday())
            <button wire:click="goToToday" class="px-3 py-2 rounded-lg text-xs font-medium flex-shrink-0" style="background:#ccfbf1;color:#0f766e;border:1px solid #0f766e;">Today</button>
        @endunless
        <div class="flex-1 text-right text-sm font-medium hidden sm:block" style="color:var(--text-dim);">
            {{ \Carbon\Carbon::parse($reportDate)->format('l, d M Y') }}
        </div>
    </div>

    @if ($sessions->isEmpty())
        <div class="text-center py-16 rounded-xl" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center" style="background:var(--surface-overlay);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" style="color:var(--text-faint);">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                </svg>
            </div>
            <div class="text-sm font-medium" style="color:var(--text-dim);">No sessions found</div>
            <div class="text-xs mt-1" style="color:var(--text-faint);">{{ \Carbon\Carbon::parse($reportDate)->format('d M Y') }} had no activity</div>
        </div>
    @else

    @php
        // ── Day-level aggregates ──────────────────────────────────────────────
        $dayOpening     = (int) $sessions->sum('opening_balance');
        $dayRevenue     = (int) $sessions->sum('total_sales');
        $dayRepayments  = (int) $sessions->sum('total_repayments');
        $dayRefunds     = (int) $sessions->sum('total_refunds_cash');
        $dayExpenses    = (int) $sessions->sum('total_expenses');
        $dayWithdrawals = (int) $sessions->sum('total_withdrawals');
        $dayBanked      = (int) $sessions->sum('total_bank_deposits');
        $dayVariance    = (int) $sessions->sum('cash_variance');
        $totalSessions  = $sessions->count();
        $closedSessions = $sessions->whereIn('status', ['closed','locked'])->count();
        $allClosed      = $closedSessions === $totalSessions;
        $operatingProfit = $dayRevenue - $dayRefunds - $dayExpenses;
        $netOperating    = $operatingProfit - $dayWithdrawals; // after owner drawings

        // Payment channel sales
        $pCash   = (int) $sessions->sum('total_sales_cash');
        $pMomo   = (int) $sessions->sum('total_sales_momo');
        $pCard   = (int) $sessions->sum('total_sales_card');
        $pBank   = (int) $sessions->sum('total_sales_bank_transfer');
        $pCredit = (int) $sessions->sum('total_sales_credit');

        // Visibility flags — show channel if setting enabled OR there were transactions
        $showCard = $settingAllowCard || $pCard > 0;
        $showBank = $settingAllowBankTransfer || $pBank > 0;

        // Money position — where is every coin right now?
        $cashRetained    = (int) $sessions->sum(fn($s) => $s->cash_retained ?? $s->actual_cash_counted ?? $s->expected_cash ?? 0);
        $momoAvailable   = (int) ($sessions->sum('total_sales_momo') + $sessions->sum('total_repayments_momo')
                                - $sessions->sum('total_expenses_momo') - $sessions->sum('total_withdrawals_momo')
                                - $sessions->sum('momo_deposits'));
        $creditOutstanding = (int) $overdueCustomers->sum('outstanding_balance');

        // Expenses by category
        $allExpenses   = $sessions->flatMap(fn($s) => $s->expenses ?? collect());
        $expByCategory = $allExpenses->groupBy(fn($e) => $e->category->name ?? 'Other')
                            ->map(fn($g) => $g->sum('amount'))->sortDesc();
        $maxExpCat = $expByCategory->max() ?: 1;

        // Sales analytics
        $saleCount  = $todaySales->count();
        $avgBasket  = $saleCount > 0 ? round($dayRevenue / $saleCount) : 0;
        $hourlyCounts = $todaySales->groupBy(fn($s) => (int)$s->sale_date->format('G'))->map->count();
        $maxHourCount = $hourlyCounts->max() ?: 1;
        $peakHour   = $hourlyCounts->sortDesc()->keys()->first();
        $peakHourFmt = $peakHour !== null ? str_pad($peakHour,2,'0',STR_PAD_LEFT).':00' : '—';

        $pTotal     = $dayRevenue ?: 1;
    @endphp

    {{-- ── Balance Statement ── --}}
    @php
        $totalIn  = $dayOpening + $pCash + $pMomo + $pCard + $pBank + $pCredit + $dayRepayments;
        $totalOut = $dayRefunds + $dayExpenses + $dayWithdrawals + $dayBanked
                  + $cashRetained + $momoAvailable + $pCredit;
        $balanceDiff = $totalIn - $totalOut;
        $isBalanced  = abs($balanceDiff) <= 1;

        $inRows = [
            ['Opening balance',      $dayOpening, '#475569', 'Cash float at start of day'],
            ['Sales — Cash',         $pCash,      '#0f766e', 'Cash collected at point of sale'],
            ['Sales — Mobile Money', $pMomo,      '#0891b2', 'MoMo payments received'],
        ];
        if ($showCard || $pCard > 0) {
            $inRows[] = ['Sales — Card',          $pCard, '#6366f1', 'Card payments received'];
        }
        if ($showBank || $pBank > 0) {
            $inRows[] = ['Sales — Bank transfer',  $pBank, '#7c3aed', 'Bank transfer payments'];
        }
        if ($pCredit > 0) {
            $inRows[] = ['Sales — Credit (owed)',  $pCredit, '#f59e0b', 'Goods sold on credit today — not yet collected'];
        }
        if ($dayRepayments > 0) {
            $inRows[] = ['Credit repayments in',   $dayRepayments, '#0891b2', 'Debt collected from customers today'];
        }

        $outRows = [
            ['Refunds paid out',   $dayRefunds,    '#d97706', 'Cash returned to customers'],
            ['Expenses paid',      $dayExpenses,   '#e11d48', 'Operational costs'],
            ['Owner withdrawals',  $dayWithdrawals,'#7c3aed', 'Cash + MoMo taken by owner'],
            ['Deposited to bank',  $dayBanked,     '#6366f1', 'Sent to bank during the day'],
            ['Cash on hand',       $cashRetained,  '#14b8a6', 'Physical cash remaining in shop'],
            ['MoMo on hand',       $momoAvailable, '#0284c7', 'Mobile money wallet balance'],
            ['Credit outstanding', $pCredit,       '#f59e0b', 'Sold on credit — awaiting collection'],
        ];
    @endphp

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <div style="font-size:15px;font-weight:700;color:#0f172a;">Daily Balance Statement</div>
            @if($isBalanced)
                <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;background:#ccfbf1;color:#0f766e;font-size:12px;font-weight:600;">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Balanced
                </span>
            @else
                <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;background:#ffe4e6;color:#e11d48;font-size:12px;font-weight:600;">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    Off by {{ number_format(abs($balanceDiff)) }} RWF
                </span>
            @endif
        </div>

        {{-- Two-column balance --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;" class="balance-cols">
        <style>
            .balance-cols{grid-template-columns:1fr 1fr;}
            @media(max-width:700px){.balance-cols{grid-template-columns:1fr!important;}}
        </style>

            {{-- LEFT: Money In --}}
            <div>
                <div style="font-size:10px;font-weight:700;color:#0f766e;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;padding-bottom:8px;border-bottom:2px solid #ccfbf1;">
                    Money In
                </div>
                @foreach($inRows as $__inRow)
                @php [$label, $amt, $color, $note] = $__inRow; @endphp
                <div style="display:flex;align-items:start;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f1f5f9;">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:#334155;">{{ $label }}</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:1px;">{{ $note }}</div>
                    </div>
                    <div style="font-size:15px;font-weight:700;color:{{ $color }};font-variant-numeric:tabular-nums;white-space:nowrap;padding-left:12px;">
                        {{ number_format($amt) }} <span style="font-size:10px;font-weight:400;color:#94a3b8;">RWF</span>
                    </div>
                </div>
                @endforeach
                {{-- Total In --}}
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0 0;margin-top:4px;">
                    <div style="font-size:13px;font-weight:700;color:#0f172a;">Total In</div>
                    <div style="font-size:20px;font-weight:800;color:#0f766e;font-variant-numeric:tabular-nums;">
                        {{ number_format($totalIn) }} <span style="font-size:11px;font-weight:400;color:#94a3b8;">RWF</span>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Where It Went --}}
            <div>
                <div style="font-size:10px;font-weight:700;color:#e11d48;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;padding-bottom:8px;border-bottom:2px solid #ffe4e6;">
                    Where It Went
                </div>
                @foreach($outRows as $__outRow)
                @php [$label, $amt, $color, $note] = $__outRow; @endphp
                <div style="display:flex;align-items:start;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f1f5f9;">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:#334155;">{{ $label }}</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:1px;">{{ $note }}</div>
                    </div>
                    <div style="font-size:15px;font-weight:700;color:{{ $color }};font-variant-numeric:tabular-nums;white-space:nowrap;padding-left:12px;">
                        {{ number_format($amt) }} <span style="font-size:10px;font-weight:400;color:#94a3b8;">RWF</span>
                    </div>
                </div>
                @endforeach
                {{-- Total Out --}}
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0 0;margin-top:4px;">
                    <div style="font-size:13px;font-weight:700;color:#0f172a;">Total Accounted For</div>
                    <div style="font-size:20px;font-weight:800;color:{{ $isBalanced ? '#0f766e' : '#e11d48' }};font-variant-numeric:tabular-nums;">
                        {{ number_format($totalOut) }} <span style="font-size:11px;font-weight:400;color:#94a3b8;">RWF</span>
                    </div>
                </div>
            </div>
        </div>

        @if($creditRepaidToday > 0)
        <div style="margin-top:16px;padding:10px 14px;background:#ccfbf1;border-radius:10px;font-size:12px;color:#0f766e;border:1px solid #86efac;">
            + <strong>{{ number_format($creditRepaidToday) }} RWF</strong> collected from outstanding credit today (already included in Repayments In)
        </div>
        @endif
    </div>

    {{-- ── Quick Metrics Strip ── --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;" class="metrics-strip">
        <style>.metrics-strip{grid-template-columns:repeat(3,1fr);} @media(max-width:600px){.metrics-strip{grid-template-columns:1fr!important;}}</style>

        {{-- Revenue After Expenses --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size:10px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Revenue After Expenses</div>
            <div style="font-size:22px;font-weight:700;color:#0f766e;margin-bottom:3px;">{{ number_format($operatingProfit) }} <span style="font-size:11px;font-weight:400;color:#94a3b8;">RWF</span></div>
            <div style="font-size:11px;color:#94a3b8;">Revenue minus refunds and operational expenses</div>
        </div>

        {{-- Cash Variance --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size:10px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Cash Variance</div>
            <div style="font-size:22px;font-weight:700;margin-bottom:3px;color:{{ $dayVariance > 0 ? '#0f766e' : ($dayVariance < 0 ? '#e11d48' : '#94a3b8') }};">
                {{ $dayVariance >= 0 ? '+' : '' }}{{ number_format($dayVariance) }} <span style="font-size:11px;font-weight:400;color:#94a3b8;">RWF</span>
            </div>
            <div style="font-size:11px;color:#94a3b8;">Difference between expected & counted cash</div>
        </div>

        {{-- Sessions --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size:10px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Sessions</div>
            <div style="font-size:22px;font-weight:700;color:#0f172a;margin-bottom:3px;">{{ $closedSessions }}<span style="font-size:14px;font-weight:400;color:#94a3b8;">/{{ $totalSessions }}</span></div>
            <span style="font-size:11px;padding:2px 8px;border-radius:20px;font-weight:500;background:{{ $allClosed ? '#ccfbf1' : '#fef3c7' }};color:{{ $allClosed ? '#0f766e' : '#d97706' }};">
                {{ $allClosed ? 'All closed' : 'Some still open' }}
            </span>
        </div>
    </div>

    {{-- ── Payment Mix + Expense Breakdown ── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;" class="mix-exp-grid">
        <style>.mix-exp-grid{grid-template-columns:1fr 1fr;} @media(max-width:800px){.mix-exp-grid{grid-template-columns:1fr!important;}}</style>

        {{-- Payment Mix (settings-aware) --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <span style="font-size:15px;font-weight:600;color:#0f172a;">Revenue by channel</span>
                <span style="font-size:12px;color:#475569;">{{ number_format($dayRevenue) }} RWF</span>
            </div>
            @php
                $channels = [
                    ['Cash',          $pCash,   '#0f766e', true],
                    ['Mobile Money',  $pMomo,   '#0284c7', true],
                    ['Card',          $pCard,   '#7c3aed', $showCard],
                    ['Bank Transfer', $pBank,   '#475569', $showBank],
                    ['Credit',        $pCredit, '#d97706', true],
                ];
            @endphp
            <div style="position:relative;height:160px;margin-bottom:14px;">
                <canvas id="daily-donut-chart"></canvas>
            </div>
            @foreach($channels as $__chRow)
            @php [$chName, $chAmt, $chColor, $chVisible] = $__chRow; @endphp
            @if($chVisible)
            <div style="display:flex;align-items:center;gap:10px;padding:5px 0;font-size:13px;">
                <span style="width:10px;height:10px;border-radius:3px;flex-shrink:0;background:{{ $chColor }};"></span>
                <span style="flex:0 0 80px;font-size:12px;font-weight:500;color:#475569;">{{ $chName }}</span>
                <div style="flex:1;height:5px;background:#f1f5f9;border-radius:3px;overflow:hidden;">
                    <div style="height:100%;border-radius:3px;width:{{ $pTotal > 0 ? round($chAmt/$pTotal*100) : 0 }}%;background:{{ $chColor }};"></div>
                </div>
                <span style="flex:0 0 90px;text-align:right;font-weight:600;font-size:12px;color:{{ $chAmt > 0 ? $chColor : '#94a3b8' }};">{{ number_format($chAmt) }}</span>
            </div>
            @endif
            @endforeach
        </div>

        {{-- Expense Breakdown + Sales stats --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:16px;">
                <span style="font-size:15px;font-weight:600;color:#0f172a;">Expense breakdown</span>
                <span style="font-size:12px;color:#475569;">{{ number_format($dayExpenses) }} RWF</span>
            </div>
            @if($expByCategory->isEmpty())
                <div style="text-align:center;padding:16px 0;font-size:13px;color:#94a3b8;">No expenses recorded</div>
            @else
                @foreach($expByCategory->take(5) as $catName => $catAmt)
                <div style="display:flex;align-items:center;gap:10px;padding:5px 0;font-size:13px;">
                    <span style="flex:0 0 90px;font-size:12px;color:#475569;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $catName }}</span>
                    <div style="flex:1;height:5px;background:#f1f5f9;border-radius:3px;overflow:hidden;">
                        <div style="height:100%;border-radius:3px;width:{{ round($catAmt/$maxExpCat*100) }}%;background:#e11d48;"></div>
                    </div>
                    <span style="flex:0 0 70px;text-align:right;font-size:12px;font-weight:500;color:#475569;">{{ number_format($catAmt) }}</span>
                </div>
                @endforeach
            @endif
            <div style="height:1px;background:#e2e8f0;margin:14px 0;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;text-align:center;">
                    <div style="font-size:10px;color:#475569;margin-bottom:3px;">Transactions</div>
                    <div style="font-size:15px;font-weight:700;color:#0f172a;">{{ $saleCount }}</div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;text-align:center;">
                    <div style="font-size:10px;color:#475569;margin-bottom:3px;">Avg basket</div>
                    <div style="font-size:15px;font-weight:700;color:#0f172a;">{{ $avgBasket > 0 ? number_format($avgBasket) : '—' }}</div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;text-align:center;">
                    <div style="font-size:10px;color:#475569;margin-bottom:3px;">Peak hour</div>
                    <div style="font-size:15px;font-weight:700;color:#0f766e;">{{ $peakHourFmt }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Session Cards (merged with Closing Detail) ── --}}
    <div style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;">
        Sessions &amp; closing detail — {{ \Carbon\Carbon::parse($reportDate)->format('d M Y') }}
    </div>

    {{-- Action notices --}}
    <div class="space-y-2 mb-4">
        @if(!$allClosed && $totalSessions > 0)
        <div style="padding:12px 16px;background:#fef3c7;border-left:4px solid #d97706;border-radius:4px 10px 10px 4px;font-size:12px;color:#92400e;">
            <strong>Pending:</strong> {{ $totalSessions - $closedSessions }} session{{ ($totalSessions - $closedSessions) !== 1 ? 's' : '' }} still open — remind shop managers to close the day.
        </div>
        @endif
        @if(abs($dayVariance) > 5000)
        <div style="padding:12px 16px;background:#ffe4e6;border-left:4px solid #e11d48;border-radius:4px 10px 10px 4px;font-size:12px;color:#9f1239;">
            <strong>Cash discrepancy:</strong> Total variance of {{ $dayVariance >= 0 ? '+' : '' }}{{ number_format($dayVariance) }} RWF across all sessions — review reconciliations below.
        </div>
        @endif
        @if($allClosed)
        <div style="padding:12px 16px;background:#ccfbf1;border-left:4px solid #0f766e;border-radius:4px 10px 10px 4px;font-size:12px;color:#115e59;">
            <strong>Day complete.</strong> All sessions closed. Lock them to make records immutable.
        </div>
        @endif
    </div>

    <div class="space-y-4">
    @foreach ($sessions as $session)
    @php
        $isExpanded = $expandedSessionId === $session->id;
        $sv = $session->cash_variance ?? 0;
        $statusMap = ['open' => ['#ccfbf1','#0f766e'], 'closed' => ['#fef3c7','#d97706'], 'locked' => ['#f1f5f9','#475569']];
        [$sBg,$sColor] = $statusMap[$session->status] ?? ['#f1f5f9','#475569'];

        // Per-session channel visibility
        $sesShowCard = $settingAllowCard || ($session->total_sales_card ?? 0) > 0;
        $sesShowBank = $settingAllowBankTransfer || ($session->total_sales_bank_transfer ?? 0) > 0;
    @endphp
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.04);">

        {{-- Session header --}}
        <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <button wire:click="toggleExpand({{ $session->id }})"
                            style="padding:4px;border-radius:6px;background:transparent;border:none;cursor:pointer;"
                            onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                        <svg style="width:16px;height:16px;color:#94a3b8;transition:transform 0.2s;transform:{{ $isExpanded ? 'rotate(90deg)' : 'none' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="font-weight:700;font-size:15px;color:#0f172a;">{{ $session->shop->name ?? '—' }}</span>
                            <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:500;background:{{ $sBg }};color:{{ $sColor }};">{{ ucfirst($session->status) }}</span>
                        </div>
                        <div style="font-size:12px;margin-top:3px;color:#475569;">
                            Opened {{ $session->opened_at->format('H:i') }} by {{ $session->openedBy->name ?? '—' }}
                            @if($session->closed_at) · Closed {{ $session->closed_at->format('H:i') }} @endif
                            @if($session->locked_at) · Locked {{ $session->locked_at->format('H:i') }} @endif
                        </div>
                    </div>
                </div>

                {{-- Header stats --}}
                <div style="display:flex;align-items:center;gap:20px;">
                    <div style="text-align:right;">
                        <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.3px;">Opening</div>
                        <div style="font-size:13px;font-weight:600;color:#64748b;">{{ number_format($session->opening_balance ?? 0) }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.3px;">Revenue</div>
                        <div style="font-size:14px;font-weight:700;color:#0f766e;">{{ number_format($session->total_sales ?? 0) }}</div>
                    </div>
                    @if(($session->total_repayments ?? 0) > 0)
                    <div style="text-align:right;">
                        <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.3px;">Repayments</div>
                        <div style="font-size:13px;font-weight:600;color:#0891b2;">{{ number_format($session->total_repayments) }}</div>
                    </div>
                    @endif
                    <div style="text-align:right;">
                        <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.3px;">Variance</div>
                        <div style="font-size:13px;font-weight:700;color:{{ $sv > 0 ? '#0f766e' : ($sv < 0 ? '#e11d48' : '#94a3b8') }};">
                            {{ $sv >= 0 ? '+' : '' }}{{ number_format($sv) }}
                        </div>
                    </div>
                    @if($session->status === 'closed')
                    <button wire:click="lockSession({{ $session->id }})"
                            wire:confirm="Lock this session? Records become immutable."
                            style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:500;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;cursor:pointer;">
                        Lock
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Expanded body --}}
        @if($isExpanded)
        <div style="padding:20px;background:#f8fafc;">

            {{-- 2-col: Reconciliation | Revenue breakdown --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;" class="recon-rev-grid-{{ $session->id }}">
                <style>.recon-rev-grid-{{ $session->id }}{grid-template-columns:1fr 1fr;} @media(max-width:700px){.recon-rev-grid-{{ $session->id }}{grid-template-columns:1fr!important;}}</style>

                {{-- Cash Reconciliation --}}
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">Cash Reconciliation</div>
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                        @php
                            $recon = [
                                ['Opening balance',             $session->opening_balance            ?? 0, null,      false],
                                ['+ Cash sales',                $session->total_sales_cash           ?? 0, '#0f766e', false],
                                ['+ Cash repayments received',  $session->total_repayments_cash      ?? 0, '#0891b2', ($session->total_repayments_cash ?? 0) === 0],
                                ['− Cash refunds',              $session->total_refunds_cash         ?? 0, '#d97706', ($session->total_refunds_cash ?? 0) === 0],
                                ['− Cash expenses',             $session->total_expenses_cash        ?? 0, '#e11d48', ($session->total_expenses_cash ?? 0) === 0],
                                ['− Cash withdrawals',          $session->total_withdrawals_cash     ?? 0, '#7c3aed', ($session->total_withdrawals_cash ?? 0) === 0],
                                ['− Cash deposits to bank',     $session->cash_deposits              ?? ($session->total_bank_deposits ?? 0), '#6366f1', ($session->cash_deposits ?? $session->total_bank_deposits ?? 0) === 0],
                            ];
                        @endphp
                        @foreach ($recon as $__rcRow)
                        @php [$rl, $ra, $rc, $rskip] = $__rcRow; @endphp
                        @if(!$rskip)
                        <div style="display:flex;justify-content:space-between;padding:7px 14px;border-bottom:1px solid #e2e8f0;font-size:12px;">
                            <span style="color:#475569;">{{ $rl }}</span>
                            <span style="font-weight:500;color:{{ $rc ?? '#0f172a' }};">{{ number_format($ra) }} RWF</span>
                        </div>
                        @endif
                        @endforeach

                        {{-- Expected --}}
                        <div style="display:flex;justify-content:space-between;padding:9px 14px;border-bottom:1px solid #e2e8f0;font-size:12px;font-weight:700;background:#f8fafc;">
                            <span style="color:#0f172a;">= Expected cash</span>
                            <span style="color:#0f172a;">{{ number_format($session->expected_cash ?? 0) }} RWF</span>
                        </div>

                        @if($session->actual_cash_counted !== null)
                        <div style="display:flex;justify-content:space-between;padding:7px 14px;border-bottom:1px solid #e2e8f0;font-size:12px;">
                            <span style="color:#475569;">Actual cash counted</span>
                            <span style="font-weight:500;color:#0f172a;">{{ number_format($session->actual_cash_counted) }} RWF</span>
                        </div>
                        @endif
                        @if(($session->cash_to_owner_momo ?? 0) > 0)
                        <div style="display:flex;justify-content:space-between;padding:7px 14px;border-bottom:1px solid #e2e8f0;font-size:12px;">
                            <span style="color:#475569;">Sent to owner via MoMo</span>
                            <span style="font-weight:500;color:#7c3aed;">{{ number_format($session->cash_to_owner_momo) }} RWF</span>
                        </div>
                        @endif
                        @if($session->cash_retained !== null)
                        <div style="display:flex;justify-content:space-between;padding:7px 14px;border-bottom:1px solid #e2e8f0;font-size:12px;">
                            <span style="color:#475569;">Cash retained in shop</span>
                            <span style="font-weight:600;color:#0f766e;">{{ number_format($session->cash_retained) }} RWF</span>
                        </div>
                        @endif

                        {{-- Variance --}}
                        @if($session->cash_variance !== null)
                        <div style="display:flex;justify-content:space-between;padding:10px 14px;font-size:12px;font-weight:700;background:{{ $sv < 0 ? '#ffe4e6' : ($sv > 0 ? '#f0fdf4' : '#f8fafc') }};">
                            <span style="color:#0f172a;">Variance</span>
                            <span style="color:{{ $sv > 0 ? '#0f766e' : ($sv < 0 ? '#e11d48' : '#94a3b8') }};">
                                {{ $sv >= 0 ? '+' : '' }}{{ number_format($sv) }} RWF
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Revenue by Channel + Non-cash settlements --}}
                <div class="space-y-4">
                    <div>
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">Revenue by channel</div>
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                            @php
                                $revCh = [
                                    ['Cash',          $session->total_sales_cash          ?? 0, '#0f766e', true],
                                    ['Mobile Money',  $session->total_sales_momo           ?? 0, '#0284c7', true],
                                    ['Card',          $session->total_sales_card           ?? 0, '#7c3aed', $sesShowCard],
                                    ['Bank Transfer', $session->total_sales_bank_transfer  ?? 0, '#475569', $sesShowBank],
                                    ['Credit',        $session->total_sales_credit         ?? 0, '#d97706', true],
                                ];
                                $revTotal = $session->total_sales ?? 0;
                            @endphp
                            @foreach($revCh as $__rchRow)
                            @php [$rChName, $rChAmt, $rChColor, $rChVisible] = $__rchRow; @endphp
                            @if($rChVisible)
                            <div style="display:flex;justify-content:space-between;padding:8px 14px;border-bottom:1px solid #e2e8f0;font-size:12px;">
                                <span style="color:#475569;">{{ $rChName }}</span>
                                <span style="font-weight:600;color:{{ $rChAmt > 0 ? $rChColor : '#94a3b8' }};">{{ number_format($rChAmt) }} RWF</span>
                            </div>
                            @endif
                            @endforeach
                            <div style="display:flex;justify-content:space-between;padding:9px 14px;background:#f8fafc;border-top:2px solid #e2e8f0;font-size:12px;font-weight:700;">
                                <span style="color:#0f172a;">Total</span>
                                <span style="color:#0284c7;">{{ number_format($revTotal) }} RWF</span>
                            </div>
                        </div>
                    </div>

                    {{-- Non-cash settlements --}}
                    @php
                        $settlements = array_filter([
                            ['Mobile Money',  $session->total_sales_momo ?? 0,          $session->momo_settled ?? 0,          $session->momo_settled_ref,          '#0284c7', true],
                            ['Card',          $session->total_sales_card ?? 0,          $session->card_settled ?? 0,          $session->card_settled_ref,          '#7c3aed', $sesShowCard],
                            ['Bank Transfer', $session->total_sales_bank_transfer ?? 0, $session->bank_transfer_settled ?? 0, $session->bank_transfer_settled_ref, '#475569', $sesShowBank],
                        ], fn($r) => $r[4] && $r[1] > 0);
                        $hasCredit = ($session->total_sales_credit ?? 0) > 0;
                    @endphp
                    @if(count($settlements) > 0 || $hasCredit)
                    <div>
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">Non-cash settlement</div>
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                            @foreach($settlements as $__stRow)
                            @php [$sChName, $sChSales, $sChSettled, $sChRef, $sChColor, $_] = $__stRow; @endphp
                            <div style="padding:9px 14px;display:flex;justify-content:space-between;align-items:start;gap:12px;border-bottom:1px solid #e2e8f0;">
                                <div>
                                    <div style="font-size:12px;font-weight:600;color:#0f172a;">{{ $sChName }}</div>
                                    <div style="font-size:11px;color:#475569;margin-top:1px;">Sales: {{ number_format($sChSales) }} RWF @if($sChRef)· Ref: {{ $sChRef }}@endif</div>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <div style="font-size:12px;font-weight:600;color:{{ $sChColor }};">{{ number_format($sChSettled) }} RWF</div>
                                    @if($sChSettled < $sChSales)
                                        <div style="font-size:11px;color:#e11d48;margin-top:1px;">−{{ number_format($sChSales - $sChSettled) }} unaccounted</div>
                                    @elseif($sChSettled > 0)
                                        <div style="font-size:11px;color:#0f766e;margin-top:1px;">Settled ✓</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            @if($hasCredit)
                            <div style="padding:9px 14px;display:flex;justify-content:space-between;background:#fef3c7;">
                                <div>
                                    <div style="font-size:12px;font-weight:600;color:#d97706;">Credit</div>
                                    <div style="font-size:11px;color:#475569;margin-top:1px;">Owed by customers</div>
                                </div>
                                <span style="font-size:12px;font-weight:700;color:#d97706;">{{ number_format($session->total_sales_credit) }} RWF</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Expenses + Withdrawals --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;" class="exp-wd-grid-{{ $session->id }}">
                <style>.exp-wd-grid-{{ $session->id }}{grid-template-columns:1fr 1fr;} @media(max-width:600px){.exp-wd-grid-{{ $session->id }}{grid-template-columns:1fr!important;}}</style>

                {{-- Expenses --}}
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">
                        Expenses
                        @if($session->expenses->count() > 0)
                            <span style="margin-left:4px;padding:1px 6px;border-radius:20px;background:#f8fafc;color:#475569;font-size:10px;">{{ $session->expenses->count() }}</span>
                        @endif
                    </div>
                    @if($session->expenses->isEmpty())
                        <div style="text-align:center;padding:10px;border-radius:10px;font-size:12px;color:#94a3b8;background:#f8fafc;border:1px solid #e2e8f0;">None recorded</div>
                    @else
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                            @foreach($session->expenses as $exp)
                            <div style="padding:7px 14px;display:flex;justify-content:space-between;gap:10px;border-bottom:1px solid #e2e8f0;font-size:12px;">
                                <div>
                                    <div style="font-weight:500;color:#0f172a;">{{ $exp->category->name ?? '—' }}</div>
                                    @if($exp->description)<div style="font-size:11px;color:#94a3b8;margin-top:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:150px;">{{ $exp->description }}</div>@endif
                                </div>
                                <span style="font-weight:600;flex-shrink:0;color:#e11d48;">{{ number_format($exp->amount) }}</span>
                            </div>
                            @endforeach
                            <div style="padding:7px 14px;display:flex;justify-content:space-between;background:#f8fafc;font-size:12px;font-weight:700;">
                                <span style="color:#475569;">Total</span>
                                <span style="color:#e11d48;">{{ number_format($session->expenses->sum('amount')) }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Withdrawals --}}
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">
                        Owner Withdrawals
                        @if($session->ownerWithdrawals->count() > 0)
                            <span style="margin-left:4px;padding:1px 6px;border-radius:20px;background:#f8fafc;color:#475569;font-size:10px;">{{ $session->ownerWithdrawals->count() }}</span>
                        @endif
                    </div>
                    @if($session->ownerWithdrawals->isEmpty())
                        <div style="text-align:center;padding:10px;border-radius:10px;font-size:12px;color:#94a3b8;background:#f8fafc;border:1px solid #e2e8f0;">None recorded</div>
                    @else
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                            @foreach($session->ownerWithdrawals as $wd)
                            <div style="padding:7px 14px;display:flex;justify-content:space-between;gap:10px;border-bottom:1px solid #e2e8f0;font-size:12px;">
                                <div>
                                    @if($wd->reason)<div style="font-weight:500;color:#0f172a;">{{ $wd->reason }}</div>@endif
                                    <div style="font-size:11px;color:#94a3b8;">{{ $wd->created_at->format('H:i') }}{{ $wd->recordedBy ? ' · '.$wd->recordedBy->name : '' }}</div>
                                </div>
                                <span style="font-weight:600;flex-shrink:0;color:#7c3aed;">{{ number_format($wd->amount) }}</span>
                            </div>
                            @endforeach
                            <div style="padding:7px 14px;display:flex;justify-content:space-between;background:#f8fafc;font-size:12px;font-weight:700;">
                                <span style="color:#475569;">Total</span>
                                <span style="color:#7c3aed;">{{ number_format($session->ownerWithdrawals->sum('amount')) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Bank Deposits --}}
            @if($session->bankDeposits->isNotEmpty())
            <div style="margin-bottom:16px;">
                <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">
                    Bank Deposits
                    <span style="margin-left:4px;padding:1px 6px;border-radius:20px;background:#f8fafc;color:#475569;font-size:10px;">{{ $session->bankDeposits->count() }}</span>
                </div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    @foreach($session->bankDeposits as $dep)
                    <div style="padding:7px 14px;display:flex;justify-content:space-between;gap:10px;border-bottom:1px solid #e2e8f0;font-size:12px;">
                        <div>
                            @if($dep->bank_reference)<div style="font-weight:500;color:#0f172a;">Ref: {{ $dep->bank_reference }}</div>@endif
                            @if($dep->notes)<div style="font-size:11px;color:#94a3b8;margin-top:1px;">{{ $dep->notes }}</div>@endif
                            <div style="font-size:11px;color:#94a3b8;">{{ $dep->deposited_at->format('H:i') }}{{ $dep->depositedBy ? ' · '.$dep->depositedBy->name : '' }}</div>
                        </div>
                        <span style="font-weight:600;flex-shrink:0;color:#0284c7;">{{ number_format($dep->amount) }}</span>
                    </div>
                    @endforeach
                    <div style="padding:7px 14px;display:flex;justify-content:space-between;background:#f8fafc;font-size:12px;font-weight:700;">
                        <span style="color:#475569;">Total banked</span>
                        <span style="color:#0284c7;">{{ number_format($session->bankDeposits->sum('amount')) }}</span>
                    </div>
                </div>
            </div>
            @endif

            @if($session->notes)
            <div style="padding:10px 14px;border-radius:10px;font-size:12px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;">
                <span style="font-weight:600;color:#0f172a;">Notes:</span> {{ $session->notes }}
            </div>
            @endif
        </div>
        @endif
    </div>
    @endforeach
    </div>

    {{-- ── Credit Accounts & Transaction Feed ── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:20px;" class="credit-tx-grid">
        <style>.credit-tx-grid{grid-template-columns:1fr 1fr;} @media(max-width:800px){.credit-tx-grid{grid-template-columns:1fr!important;}}</style>

        {{-- Open credit accounts --}}
        @php
            $overdueCount = $overdueCustomers->filter(fn($c) => $c->last_credit_at && $c->last_credit_at->diffInDays(now()) > 30)->count();
        @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                <span style="font-size:15px;font-weight:600;color:#0f172a;">Open credit accounts</span>
                @if($overdueCount > 0)
                    <span style="font-size:11px;padding:3px 10px;border-radius:20px;background:#ffe4e6;color:#e11d48;font-weight:500;">{{ $overdueCount }} overdue</span>
                @endif
            </div>
            @if($overdueCustomers->isEmpty())
                <div style="text-align:center;padding:20px;font-size:13px;color:#94a3b8;">No outstanding credit</div>
            @else
                <div class="space-y-2">
                    @foreach($overdueCustomers->take(8) as $cust)
                    @php
                        $isOverdue = $cust->last_credit_at && $cust->last_credit_at->diffInDays(now()) > 30;
                    @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid {{ $isOverdue ? '#e11d48' : '#d97706' }};">
                        <div>
                            <div style="font-weight:600;font-size:12px;color:#0f172a;">{{ $cust->name }}</div>
                            <div style="font-size:11px;color:{{ $isOverdue ? '#e11d48' : '#475569' }};margin-top:1px;">
                                @if($isOverdue){{ $cust->last_credit_at->diffInDays(now()) }} days overdue
                                @elseif($cust->last_credit_at){{ $cust->last_credit_at->diffForHumans() }}
                                @else Outstanding @endif
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700;font-size:13px;color:{{ $isOverdue ? '#e11d48' : '#d97706' }};">{{ number_format($cust->outstanding_balance) }}</div>
                            <div style="font-size:10px;color:#94a3b8;">RWF</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($overdueCustomers->count() > 8)
                    <div style="text-align:center;padding-top:10px;font-size:12px;color:#94a3b8;">+{{ $overdueCustomers->count() - 8 }} more</div>
                @endif
            @endif
        </div>

        {{-- Transaction feed --}}
        @php
            $expCount  = $sessions->sum(fn($s) => $s->expenses->count());
            $drawCount = $sessions->sum(fn($s) => $s->ownerWithdrawals->count());
            $txFeed    = collect();
            foreach ($todaySales as $s) {
                $txFeed->push(['time'=>$s->sale_date,'desc'=>$s->customer_name ? 'Sale — '.$s->customer_name : 'Sale',
                    'method'=>$s->is_split_payment ? 'Split' : ucfirst(str_replace('_',' ',$s->payment_method?->value ?? 'cash')),
                    'amount'=>$s->total,'type'=>'sale']);
            }
            foreach ($sessions as $sess) {
                foreach ($sess->expenses as $exp) {
                    $txFeed->push(['time'=>$exp->created_at,'desc'=>($exp->category->name ?? 'Expense').($exp->description ? ' — '.$exp->description : ''),
                        'method'=>ucfirst(str_replace('_',' ',$exp->payment_method ?? 'cash')),'amount'=>$exp->amount,'type'=>'expense']);
                }
                foreach ($sess->ownerWithdrawals as $wd) {
                    $txFeed->push(['time'=>$wd->created_at,'desc'=>'Owner draw'.($wd->reason ? ' — '.$wd->reason : ''),
                        'method'=>ucfirst($wd->method ?? 'Cash'),'amount'=>$wd->amount,'type'=>'withdrawal']);
                }
            }
            $txFeed = $txFeed->sortByDesc('time')->values()->take(30);
        @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;max-height:460px;overflow-y:auto;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="position:sticky;top:-20px;background:#fff;padding-top:0;z-index:2;margin-bottom:12px;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:15px;font-weight:600;color:#0f172a;">All transactions today</span>
                    <span style="font-size:11px;color:#94a3b8;">{{ $saleCount }}s · {{ $expCount }}e · {{ $drawCount }}w</span>
                </div>
            </div>
            @if($txFeed->isEmpty())
                <div style="text-align:center;padding:20px;font-size:13px;color:#94a3b8;">No transactions recorded</div>
            @else
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr style="border-bottom:2px solid #e2e8f0;">
                            <th style="text-align:left;padding:5px 8px;font-size:10px;font-weight:600;color:#475569;text-transform:uppercase;">Time</th>
                            <th style="text-align:left;padding:5px 8px;font-size:10px;font-weight:600;color:#475569;text-transform:uppercase;">Description</th>
                            <th style="text-align:left;padding:5px 8px;font-size:10px;font-weight:600;color:#475569;text-transform:uppercase;">Method</th>
                            <th style="text-align:right;padding:5px 8px;font-size:10px;font-weight:600;color:#475569;text-transform:uppercase;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($txFeed as $tx)
                        @php
                            $txColors = ['sale'=>['#0f766e','#ccfbf1'],'expense'=>['#e11d48','#ffe4e6'],'withdrawal'=>['#7c3aed','#ede9fe']];
                            [$txC,$txBg] = $txColors[$tx['type']] ?? ['#475569','#f1f5f9'];
                        @endphp
                        <tr style="border-bottom:1px solid #e2e8f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding:7px 8px;font-size:11px;color:#94a3b8;white-space:nowrap;">{{ $tx['time']?->format('H:i') ?? '—' }}</td>
                            <td style="padding:7px 8px;font-size:12px;font-weight:500;color:#0f172a;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $tx['desc'] }}</td>
                            <td style="padding:7px 8px;font-size:11px;color:#475569;">{{ $tx['method'] }}</td>
                            <td style="padding:7px 8px;text-align:right;font-weight:600;font-size:12px;color:{{ $txC }};">{{ $tx['type'] === 'sale' ? '+' : '−' }}{{ number_format($tx['amount']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @endif

    {{-- Chart.js donut --}}
    @if(!$sessions->isEmpty())
    @script
    <script>
    (function() {
        function drawDonut() {
            const canvas = document.getElementById('daily-donut-chart');
            if (!canvas || !window.Chart) return;
            if (canvas._chartInstance) { canvas._chartInstance.destroy(); }
            const showCard = {{ $showCard ? 'true' : 'false' }};
            const showBank = {{ $showBank ? 'true' : 'false' }};
            const rawData = [
                { label: 'Cash',          value: {{ $pCash }},   color: '#0f766e', show: true },
                { label: 'Mobile Money',  value: {{ $pMomo }},   color: '#0284c7', show: true },
                { label: 'Card',          value: {{ $pCard }},   color: '#7c3aed', show: showCard },
                { label: 'Bank Transfer', value: {{ $pBank }},   color: '#475569', show: showBank },
                { label: 'Credit',        value: {{ $pCredit }}, color: '#d97706', show: true },
            ].filter(d => d.show);
            canvas._chartInstance = new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: rawData.map(d => d.label),
                    datasets: [{ data: rawData.map(d => d.value), backgroundColor: rawData.map(d => d.color), borderWidth: 2, borderColor: '#fff', hoverOffset: 4 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: 'rgba(15,23,42,0.9)', padding: 10, cornerRadius: 8,
                            callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.raw.toLocaleString() + ' RWF' } }
                    }
                }
            });
        }
        drawDonut();
        Livewire.hook('commit', ({ succeed }) => { succeed(() => { setTimeout(drawDonut, 0); }); });
    })();
    </script>
    @endscript
    @endif
</div>
