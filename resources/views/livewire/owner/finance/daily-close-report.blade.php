<div>
    {{-- Flash messages --}}
    @if (session()->has('success'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:#ccfbf1;color:#0f766e;">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:#ffe4e6;color:#e11d48;">{{ session('error') }}</div>
    @endif

    {{-- ── Day Navigation ── --}}
    <div class="mb-6 flex items-center gap-2">
        <button wire:click="previousDay"
                class="p-2 rounded-lg flex-shrink-0 transition-colors hover:opacity-80"
                style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <input type="date" wire:model.live="reportDate"
               class="flex-1 sm:flex-none px-3 py-2 rounded-lg text-sm font-medium"
               style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);"
               max="{{ today()->toDateString() }}">
        <button wire:click="nextDay"
                class="p-2 rounded-lg flex-shrink-0 transition-colors hover:opacity-80"
                style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);"
                @if(\Carbon\Carbon::parse($reportDate)->isToday()) disabled style="background:var(--surface-raised);color:var(--text-faint);border:1px solid var(--border);opacity:0.4;cursor:default;" @endif>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7 7 7"/>
            </svg>
        </button>
        @unless(\Carbon\Carbon::parse($reportDate)->isToday())
            <button wire:click="goToToday"
                    class="px-3 py-2 rounded-lg text-xs font-medium flex-shrink-0"
                    style="background:#ccfbf1;color:#0f766e;border:1px solid #0f766e;">
                Today
            </button>
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
        $dayRevenue     = $sessions->sum('total_sales')      ?? 0;
        $dayExpenses    = $sessions->sum('total_expenses')   ?? 0;
        $dayWithdrawals = $sessions->sum('total_withdrawals') ?? 0;
        $dayBanked      = $sessions->sum('total_bank_deposits') ?? 0;
        $dayVariance    = $sessions->sum('cash_variance')    ?? 0;
        $totalSessions  = $sessions->count();
        $closedSessions = $sessions->whereIn('status', ['closed','locked'])->count();
        $allClosed      = $closedSessions === $totalSessions;
        $netResult      = $dayRevenue - $dayExpenses - $dayWithdrawals;

        // Payment channel totals
        $pCash   = $sessions->sum('total_sales_cash')          ?? 0;
        $pMomo   = $sessions->sum('total_sales_momo')          ?? 0;
        $pCard   = $sessions->sum('total_sales_card')          ?? 0;
        $pBank   = $sessions->sum('total_sales_bank_transfer') ?? 0;
        $pCredit = $sessions->sum('total_sales_credit')        ?? 0;
        $pTotal  = $dayRevenue ?: 1;

        // Expense by category
        $allExpenses = $sessions->flatMap(fn($s) => $s->expenses ?? collect());
        $expByCategory = $allExpenses->groupBy(fn($e) => $e->category->name ?? 'Other')
            ->map(fn($g) => $g->sum('amount'))
            ->sortDesc();
        $maxExpCat = $expByCategory->max() ?: 1;
        $topExpCat = $expByCategory->keys()->first() ?? '—';
        $topExpAmt = $expByCategory->first() ?? 0;
    @endphp

    {{-- ── KPI Strip ── --}}
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;"
         class="sm:grid-cols-3 lg:grid-cols-6">
        @foreach ([
            ['Gross Revenue',     $dayRevenue,     '#0f766e', '#ccfbf1', 'RWF', false],
            ['Expenses',          $dayExpenses,    '#e11d48', '#ffe4e6', 'RWF', false],
            ['Owner Withdrawals', $dayWithdrawals, '#d97706', '#fef3c7', 'RWF', false],
            ['Net Operating',     $netResult,      '#0284c7', '#e0f2fe', 'RWF', false],
        ] as [$label, $value, $color, $bg, $unit, $signed])
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">{{ $label }}</div>
            <div style="font-size:22px;font-weight:700;line-height:1.2;margin-bottom:6px;letter-spacing:-0.5px;color:{{ $color }};">
                {{ number_format($value) }}
                <span style="font-size:12px;font-weight:500;color:#94a3b8;">{{ $unit }}</span>
            </div>
            <div style="height:4px;border-radius:2px;background:#f1f5f9;overflow:hidden;margin-top:10px;">
                @php $pct = $dayRevenue > 0 ? min(100, round(abs($value) / $dayRevenue * 100)) : 0; @endphp
                <div style="height:100%;border-radius:2px;width:{{ $pct }}%;background:{{ $color }};transition:width 0.5s;"></div>
            </div>
        </div>
        @endforeach

        {{-- Variance --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Cash Variance</div>
            <div style="font-size:22px;font-weight:700;line-height:1.2;margin-bottom:6px;letter-spacing:-0.5px;color:{{ $dayVariance > 0 ? '#0f766e' : ($dayVariance < 0 ? '#e11d48' : '#94a3b8') }};">
                {{ $dayVariance >= 0 ? '+' : '' }}{{ number_format($dayVariance) }}
                <span style="font-size:12px;font-weight:500;color:#94a3b8;">RWF</span>
            </div>
            <div style="font-size:12px;color:{{ abs($dayVariance) <= 5000 ? '#0f766e' : '#e11d48' }};">
                {{ abs($dayVariance) <= 5000 ? '✓ Within tolerance' : '⚠ Check discrepancy' }}
            </div>
        </div>

        {{-- Sessions --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Sessions</div>
            <div style="font-size:22px;font-weight:700;line-height:1.2;margin-bottom:6px;letter-spacing:-0.5px;color:var(--text);">
                {{ $totalSessions }}
            </div>
            @if ($allClosed)
                <span style="font-size:12px;padding:3px 10px;border-radius:20px;background:#ccfbf1;color:#0f766e;font-weight:500;">All closed</span>
            @else
                <span style="font-size:12px;padding:3px 10px;border-radius:20px;background:#fef3c7;color:#d97706;font-weight:500;">{{ $closedSessions }}/{{ $totalSessions }} closed</span>
            @endif
        </div>
    </div>

    {{-- ── 3-Column Breakdown ── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;" class="breakdown-grid">
        <style>.breakdown-grid { grid-template-columns: 1fr 1fr 1fr; } @media(max-width:900px){.breakdown-grid{grid-template-columns:1fr!important;}}</style>

        {{-- Col 1: Payment Mix --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:15px;font-weight:600;margin-bottom:16px;color:#0f172a;letter-spacing:-0.2px;">Payment mix</div>
            <div style="position:relative;height:150px;margin-bottom:12px;">
                <canvas id="daily-donut-chart"></canvas>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:14px;">
                @foreach([['Cash','#0f766e'],['MoMo','#0284c7'],['Card','#7c3aed'],['Bank','#475569'],['Credit','#d97706']] as [$lbl,$lc])
                <span style="display:flex;align-items:center;gap:5px;font-size:12px;font-weight:500;color:#475569;">
                    <span style="width:10px;height:10px;border-radius:3px;background:{{ $lc }};flex-shrink:0;"></span>{{ $lbl }}
                </span>
                @endforeach
            </div>
            <div style="height:1px;background:#e2e8f0;margin:12px 0;"></div>
            @foreach([
                ['Cash',   $pCash,   '#0f766e'],
                ['MoMo',   $pMomo,   '#0284c7'],
                ['Card',   $pCard,   '#7c3aed'],
                ['Bank',   $pBank,   '#475569'],
                ['Credit', $pCredit, '#d97706'],
            ] as [$ch, $amt, $cc])
            @if($amt > 0)
            <div style="display:flex;align-items:center;gap:10px;padding:5px 0;font-size:13px;">
                <span style="flex:0 0 50px;font-size:12px;font-weight:500;color:#475569;">{{ $ch }}</span>
                <div style="flex:1;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden;">
                    <div style="height:100%;border-radius:3px;width:{{ $pTotal > 0 ? round($amt/$pTotal*100) : 0 }}%;background:{{ $cc }};"></div>
                </div>
                <span style="flex:0 0 80px;text-align:right;font-weight:600;font-size:12px;color:{{ $cc }};">{{ number_format($amt) }}</span>
            </div>
            @endif
            @endforeach
        </div>

        {{-- Col 2: Expense Breakdown --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:16px;">
                <span style="font-size:15px;font-weight:600;color:#0f172a;letter-spacing:-0.2px;">Expense breakdown</span>
                <span style="font-size:12px;color:#475569;">{{ number_format($dayExpenses) }} RWF</span>
            </div>

            @if($expByCategory->isEmpty())
                <div style="text-align:center;padding:24px 0;font-size:13px;color:#94a3b8;">No expenses recorded</div>
            @else
                @foreach($expByCategory->take(6) as $catName => $catAmt)
                <div style="display:flex;align-items:center;gap:10px;padding:5px 0;font-size:13px;">
                    <span style="flex:0 0 80px;font-size:12px;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $catName }}</span>
                    <div style="flex:1;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden;">
                        <div style="height:100%;border-radius:3px;width:{{ round($catAmt/$maxExpCat*100) }}%;background:#e11d48;"></div>
                    </div>
                    <span style="flex:0 0 70px;text-align:right;font-size:12px;font-weight:500;color:#475569;">{{ number_format($catAmt) }}</span>
                </div>
                @endforeach
            @endif

            <div style="height:1px;background:#e2e8f0;margin:16px 0;"></div>

            {{-- Peak sales hours --}}
            @php
                $hourlyCounts = $todaySales->groupBy(fn($s) => (int)$s->sale_date->format('G'))->map->count();
                $maxHourCount = $hourlyCounts->max() ?: 1;
                $peakHour     = $hourlyCounts->sortDesc()->keys()->first();
                $saleCount    = $todaySales->count();
                $avgBasket    = $saleCount > 0 ? round($dayRevenue / $saleCount) : 0;
                $peakHourFmt  = $peakHour !== null ? str_pad($peakHour, 2, '0', STR_PAD_LEFT) . ':00' : '—';
            @endphp
            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:8px;">
                <span style="font-size:14px;font-weight:600;color:#0f172a;letter-spacing:-0.1px;">Peak sales hours</span>
                <span style="font-size:12px;color:#475569;">Today</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(12,1fr);gap:3px;align-items:end;height:56px;margin-bottom:6px;">
                @foreach(range(8, 19) as $h)
                    @php $cnt = $hourlyCounts[$h] ?? 0; $hpct = $maxHourCount > 0 ? round($cnt / $maxHourCount * 100) : 0; @endphp
                    <div style="border-radius:3px 3px 0 0;background:{{ $cnt > 0 && $h === $peakHour ? '#0f766e' : ($cnt > 0 ? '#ccfbf1' : '#f1f5f9') }};height:{{ max(8, $hpct) }}%;min-height:4px;"></div>
                @endforeach
            </div>
            <div style="display:grid;grid-template-columns:repeat(12,1fr);gap:3px;margin-bottom:12px;">
                @foreach(range(8, 19) as $h)
                    <div style="font-size:9px;color:#94a3b8;text-align:center;">{{ $h }}</div>
                @endforeach
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;text-align:center;">
                    <div style="font-size:11px;font-weight:500;color:#475569;margin-bottom:4px;">Peak hour</div>
                    <div style="font-size:16px;font-weight:600;color:#0f766e;">{{ $peakHourFmt }}</div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;text-align:center;">
                    <div style="font-size:11px;font-weight:500;color:#475569;margin-bottom:4px;">Avg basket</div>
                    <div style="font-size:16px;font-weight:600;color:#0f172a;">{{ $avgBasket > 0 ? number_format($avgBasket) : '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Col 3: Owner Insights --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:15px;font-weight:600;margin-bottom:16px;color:#0f172a;letter-spacing:-0.2px;">Owner insights</div>

            {{-- Revenue summary --}}
            <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #e2e8f0;">
                <div style="width:32px;height:32px;border-radius:8px;background:#ccfbf1;color:#0f766e;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16"><path d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:3px;">
                        {{ $dayRevenue > 0 ? 'Revenue recorded' : 'No revenue today' }}
                    </div>
                    <div style="font-size:12px;color:#475569;line-height:1.5;">
                        {{ number_format($dayRevenue) }} RWF across {{ $totalSessions }} session{{ $totalSessions !== 1 ? 's' : '' }}
                    </div>
                </div>
            </div>

            {{-- Top expense category --}}
            @if($topExpAmt > 0)
            <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #e2e8f0;">
                <div style="width:32px;height:32px;border-radius:8px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:3px;">Top expense: {{ $topExpCat }}</div>
                    <div style="font-size:12px;color:#475569;line-height:1.5;">
                        {{ number_format($topExpAmt) }} RWF
                        @if($dayExpenses > 0) · {{ round($topExpAmt/$dayExpenses*100) }}% of total expenses @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Credit outstanding --}}
            @if($pCredit > 0)
            <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #e2e8f0;">
                <div style="width:32px;height:32px;border-radius:8px;background:#ffe4e6;color:#e11d48;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:3px;">Credit sales today</div>
                    <div style="font-size:12px;color:#475569;line-height:1.5;">{{ number_format($pCredit) }} RWF owed by customers</div>
                </div>
            </div>
            @endif

            {{-- Cash health --}}
            <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;">
                <div style="width:32px;height:32px;border-radius:8px;background:{{ abs($dayVariance) <= 5000 ? '#ccfbf1' : '#ffe4e6' }};color:{{ abs($dayVariance) <= 5000 ? '#0f766e' : '#e11d48' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="16" height="16"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:3px;">
                        Cash health: {{ abs($dayVariance) <= 5000 ? 'Strong' : 'Review needed' }}
                    </div>
                    <div style="font-size:12px;color:#475569;line-height:1.5;">
                        Variance {{ $dayVariance >= 0 ? '+' : '' }}{{ number_format($dayVariance) }} RWF
                        · {{ abs($dayVariance) <= 5000 ? 'Within ±5,000 tolerance' : 'Exceeds tolerance — check counts' }}
                    </div>
                </div>
            </div>

            <div style="height:1px;background:#e2e8f0;margin:12px 0;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px;text-align:center;">
                    <div style="font-size:10px;font-weight:500;color:#475569;margin-bottom:3px;">Banked</div>
                    <div style="font-size:13px;font-weight:600;color:#0284c7;">{{ number_format($dayBanked) }}</div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px;text-align:center;">
                    <div style="font-size:10px;font-weight:500;color:#475569;margin-bottom:3px;">W/D</div>
                    <div style="font-size:13px;font-weight:600;color:#d97706;">{{ number_format($dayWithdrawals) }}</div>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px;text-align:center;">
                    <div style="font-size:10px;font-weight:500;color:#475569;margin-bottom:3px;">Expenses</div>
                    <div style="font-size:13px;font-weight:600;color:#e11d48;">{{ number_format($dayExpenses) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Channel Balances ── --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);margin-bottom:20px;">
        <div style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:16px;">Channel position — today</div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px;" class="channel-grid">
            <style>.channel-grid { grid-template-columns: repeat(4,1fr); } @media(max-width:700px){.channel-grid{grid-template-columns:1fr 1fr!important;}}</style>
            @php
                $channels = [
                    ['Cash',          $pCash,   '#0f766e', "Sales {$pCash}"],
                    ['Mobile Money',  $pMomo,   '#0284c7', "Sales {$pMomo}"],
                    ['Bank Transfer', $pBank,   '#7c3aed', "Sales {$pBank}"],
                    ['Credit Owed',   $pCredit, '#d97706', "Credit {$pCredit}"],
                ];
                $maxCh = max($pCash, $pMomo, $pBank, $pCredit, 1);
            @endphp
            @foreach($channels as [$chName, $chAmt, $chColor, $chNote])
            <div>
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:8px;">
                    <span style="font-size:14px;font-weight:600;color:#0f172a;">{{ $chName }}</span>
                    <span style="font-size:11px;color:#475569;">Today</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                    <div style="flex:1;height:8px;background:#f1f5f9;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;border-radius:4px;width:{{ round($chAmt/$maxCh*100) }}%;background:{{ $chColor }};"></div>
                    </div>
                    <span style="font-size:13px;font-weight:700;color:{{ $chColor }};flex-shrink:0;">{{ number_format(round($chAmt/1000)) }}k</span>
                </div>
                <div style="font-size:11px;color:#94a3b8;">{{ number_format($chAmt) }} RWF</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Credit Accounts & Transactions ── --}}
    <div style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;">Credit accounts &amp; transactions</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;" class="credit-tx-grid">
        <style>.credit-tx-grid{grid-template-columns:1fr 1fr;} @media(max-width:800px){.credit-tx-grid{grid-template-columns:1fr!important;}}</style>

        {{-- Open credit accounts --}}
        @php
            $overdueCount = $overdueCustomers->filter(fn($c) => $c->last_credit_at && $c->last_credit_at->diffInDays(now()) > 30)->count();
        @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <span style="font-size:15px;font-weight:600;color:#0f172a;letter-spacing:-0.2px;">Open credit accounts</span>
                @if($overdueCount > 0)
                    <span style="font-size:12px;padding:3px 10px;border-radius:20px;background:#ffe4e6;color:#e11d48;font-weight:500;">{{ $overdueCount }} overdue</span>
                @endif
            </div>
            @if($overdueCustomers->isEmpty())
                <div style="text-align:center;padding:24px 0;font-size:13px;color:#94a3b8;">No outstanding credit accounts</div>
            @else
                <div class="space-y-2">
                    @foreach($overdueCustomers->take(6) as $cust)
                    @php
                        $isOverdue = $cust->last_credit_at && $cust->last_credit_at->diffInDays(now()) > 30;
                        $borderColor = $isOverdue ? '#e11d48' : '#d97706';
                        $settled = $cust->outstanding_balance <= 0;
                    @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid {{ $settled ? '#0f766e' : $borderColor }};{{ $settled ? 'opacity:0.65;' : '' }}">
                        <div>
                            <div style="font-weight:600;font-size:13px;color:#0f172a;margin-bottom:2px;">{{ $cust->name }}</div>
                            <div style="font-size:12px;color:{{ $isOverdue ? '#e11d48' : '#475569' }};">
                                @if($isOverdue)
                                    Overdue {{ $cust->last_credit_at->diffInDays(now()) }} days
                                @elseif($cust->last_credit_at)
                                    Last credit {{ $cust->last_credit_at->diffForHumans() }}
                                @else
                                    Outstanding balance
                                @endif
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:600;font-size:14px;color:{{ $settled ? '#0f766e' : $borderColor }};margin-bottom:2px;">{{ number_format($cust->outstanding_balance) }} RWF</div>
                            <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:{{ $isOverdue ? '#ffe4e6' : ($settled ? '#ccfbf1' : '#fef3c7') }};color:{{ $isOverdue ? '#e11d48' : ($settled ? '#0f766e' : '#d97706') }};font-weight:500;">
                                {{ $isOverdue ? 'Overdue' : ($settled ? 'Settled' : 'Open') }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- All today's transactions --}}
        @php
            $txFeed = collect();
            foreach ($todaySales as $s) {
                $txFeed->push([
                    'time'   => $s->sale_date,
                    'desc'   => $s->customer_name ? 'Sale — ' . $s->customer_name : 'Sale',
                    'method' => $s->is_split_payment ? 'Split' : ucfirst(str_replace('_', ' ', $s->payment_method?->value ?? 'cash')),
                    'amount' => $s->total,
                    'type'   => 'sale',
                ]);
            }
            foreach ($sessions as $sess) {
                foreach ($sess->expenses as $exp) {
                    $txFeed->push([
                        'time'   => $exp->created_at,
                        'desc'   => ($exp->category->name ?? 'Expense') . ($exp->description ? ' — ' . $exp->description : ''),
                        'method' => ucfirst(str_replace('_', ' ', $exp->payment_method ?? 'cash')),
                        'amount' => $exp->amount,
                        'type'   => 'expense',
                    ]);
                }
                foreach ($sess->ownerWithdrawals as $wd) {
                    $txFeed->push([
                        'time'   => $wd->created_at,
                        'desc'   => 'Owner draw' . ($wd->reason ? ' — ' . $wd->reason : ''),
                        'method' => 'Cash',
                        'amount' => $wd->amount,
                        'type'   => 'withdrawal',
                    ]);
                }
            }
            $txFeed = $txFeed->sortByDesc('time')->values()->take(30);
            $saleCount  = $todaySales->count();
            $expCount   = $sessions->sum(fn($s) => $s->expenses->count());
            $drawCount  = $sessions->sum(fn($s) => $s->ownerWithdrawals->count());
        @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;max-height:440px;overflow-y:auto;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="position:sticky;top:-20px;background:#fff;padding-top:0;z-index:2;margin-bottom:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:15px;font-weight:600;color:#0f172a;letter-spacing:-0.2px;">All today's transactions</span>
                    <span style="font-size:12px;color:#475569;">{{ $saleCount }} sales · {{ $expCount }} exp · {{ $drawCount }} draw</span>
                </div>
            </div>
            @if($txFeed->isEmpty())
                <div style="text-align:center;padding:24px 0;font-size:13px;color:#94a3b8;">No transactions recorded</div>
            @else
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="border-bottom:2px solid #e2e8f0;">
                            <th style="text-align:left;padding:6px 10px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Time</th>
                            <th style="text-align:left;padding:6px 10px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Description</th>
                            <th style="text-align:left;padding:6px 10px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Method</th>
                            <th style="text-align:right;padding:6px 10px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Amount</th>
                            <th style="text-align:center;padding:6px 10px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($txFeed as $tx)
                        @php
                            $txColors = [
                                'sale'       => ['#0f766e','#ccfbf1'],
                                'expense'    => ['#e11d48','#ffe4e6'],
                                'withdrawal' => ['#d97706','#fef3c7'],
                            ];
                            [$txC,$txBg] = $txColors[$tx['type']] ?? ['#475569','#f1f5f9'];
                            $sign = $tx['type'] === 'sale' ? '+' : '−';
                        @endphp
                        <tr style="border-bottom:1px solid #e2e8f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding:9px 10px;font-size:11px;color:#475569;white-space:nowrap;">{{ $tx['time']?->format('H:i') ?? '—' }}</td>
                            <td style="padding:9px 10px;font-size:12px;font-weight:500;color:#0f172a;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $tx['desc'] }}</td>
                            <td style="padding:9px 10px;font-size:11px;color:#475569;">{{ $tx['method'] }}</td>
                            <td style="padding:9px 10px;text-align:right;font-weight:600;font-size:12px;color:{{ $txC }};">{{ $sign }}{{ number_format($tx['amount']) }}</td>
                            <td style="padding:9px 10px;text-align:center;">
                                <span style="font-size:10px;padding:2px 8px;border-radius:20px;background:{{ $txBg }};color:{{ $txC }};font-weight:600;">{{ $tx['type'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Daily Closing — Summary ── --}}
    <div style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;">Daily closing — summary</div>
    <div style="display:grid;grid-template-columns:1.3fr 1fr;gap:16px;margin-bottom:20px;" class="closing-summary-grid">
        <style>.closing-summary-grid{grid-template-columns:1.3fr 1fr;} @media(max-width:800px){.closing-summary-grid{grid-template-columns:1fr!important;}}</style>

        {{-- Summary table --}}
        @php
            $marginPct    = $dayRevenue > 0 ? round($netResult / $dayRevenue * 100, 1) : 0;
            $rollFwdCash  = $sessions->sum(fn($s) => $s->cash_retained ?? $s->actual_cash_counted ?? $s->expected_cash ?? 0);
            $rollFwdMomo  = $sessions->sum(fn($s) => $s->momo_settled ?? 0);
            $rollFwdBank  = $sessions->sum(fn($s) => $s->bank_transfer_settled ?? ($s->total_bank_deposits ?? 0));
        @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:15px;font-weight:600;color:#0f172a;letter-spacing:-0.2px;margin-bottom:16px;">Closing summary</div>

            @foreach([
                ['Gross revenue',       number_format($dayRevenue) . ' RWF',     '#0f172a', false],
                ['Less: expenses',      '−' . number_format($dayExpenses) . ' RWF', '#e11d48', false],
                ['Less: owner withdrawal','−' . number_format($dayWithdrawals) . ' RWF', '#d97706', false],
            ] as [$lbl, $val, $clr, $bold])
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px dashed #e2e8f0;font-size:13px;">
                <span style="color:#475569;">{{ $lbl }}</span>
                <span style="font-weight:500;color:{{ $clr }};">{{ $val }}</span>
            </div>
            @endforeach

            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px dashed #e2e8f0;font-size:15px;font-weight:600;border-top:2px solid #e2e8f0;margin-top:4px;">
                <span style="color:#0f172a;">Net operating result</span>
                <span style="color:#0284c7;">{{ number_format($netResult) }} RWF</span>
            </div>

            @if($pCredit > 0)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px dashed #e2e8f0;font-size:13px;">
                <span style="color:#475569;">Credit sales (not cash)</span>
                <span style="font-weight:500;color:#d97706;">{{ number_format($pCredit) }} RWF</span>
            </div>
            @endif
            @if($creditRepaidToday > 0)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px dashed #e2e8f0;font-size:13px;">
                <span style="color:#475569;">Credit collected today</span>
                <span style="font-weight:500;color:#0f766e;">+{{ number_format($creditRepaidToday) }} RWF</span>
            </div>
            @endif
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;font-size:13px;">
                <span style="color:#475569;">Profit margin</span>
                <span style="font-weight:600;color:#0f766e;">{{ $marginPct }}%</span>
            </div>
        </div>

        {{-- Info notices --}}
        <div style="display:flex;flex-direction:column;gap:12px;">
            @if($rollFwdCash > 0 || $rollFwdMomo > 0 || $rollFwdBank > 0)
            <div style="padding:14px 16px;background:#ccfbf1;border-left:4px solid #0f766e;border-radius:4px 10px 10px 4px;font-size:12px;color:#115e59;line-height:1.6;">
                <strong>Roll Forward:</strong> Today's closing balances carry forward as tomorrow's opening balances:
                Cash {{ number_format(round($rollFwdCash/1000)) }}k
                · MoMo {{ number_format(round($rollFwdMomo/1000)) }}k
                · Bank {{ number_format(round($rollFwdBank/1000)) }}k RWF.
            </div>
            @endif

            @php $actionCustomers = $overdueCustomers->filter(fn($c) => $c->last_credit_at && $c->last_credit_at->diffInDays(now()) > 30); @endphp
            @if($actionCustomers->isNotEmpty())
            @foreach($actionCustomers->take(3) as $ac)
            <div style="padding:14px 16px;background:#ffe4e6;border-left:4px solid #e11d48;border-radius:4px 10px 10px 4px;font-size:12px;color:#9f1239;line-height:1.6;">
                <strong>Action Needed:</strong> Follow up with {{ $ac->name }} ({{ number_format($ac->outstanding_balance) }} RWF overdue {{ $ac->last_credit_at->diffInDays(now()) }} days).
            </div>
            @endforeach
            @endif

            @if($allClosed)
            <div style="padding:14px 16px;background:#ccfbf1;border-left:4px solid #0f766e;border-radius:4px 10px 10px 4px;font-size:12px;color:#115e59;line-height:1.6;">
                <strong>Day complete:</strong> All {{ $totalSessions }} session{{ $totalSessions !== 1 ? 's' : '' }} closed. You can lock them to make records immutable.
            </div>
            @elseif($totalSessions > 0)
            <div style="padding:14px 16px;background:#fef3c7;border-left:4px solid #d97706;border-radius:4px 10px 10px 4px;font-size:12px;color:#92400e;line-height:1.6;">
                <strong>Pending:</strong> {{ $totalSessions - $closedSessions }} session{{ ($totalSessions - $closedSessions) !== 1 ? 's' : '' }} still open — remind shop managers to close the day.
            </div>
            @endif

            {{-- Variance notice --}}
            @if(abs($dayVariance) > 5000)
            <div style="padding:14px 16px;background:#ffe4e6;border-left:4px solid #e11d48;border-radius:4px 10px 10px 4px;font-size:12px;color:#9f1239;line-height:1.6;">
                <strong>Cash discrepancy:</strong> Total variance of {{ $dayVariance >= 0 ? '+' : '' }}{{ number_format($dayVariance) }} RWF exceeds tolerance — review session reconciliations.
            </div>
            @endif
        </div>
    </div>

    {{-- ── Session Cards ── --}}
    <div style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;">Sessions — {{ \Carbon\Carbon::parse($reportDate)->format('d M Y') }}</div>
    <div class="space-y-4">
        @foreach ($sessions as $session)
        @php
            $isExpanded = $expandedSessionId === $session->id;
            $statusColors = [
                'open'   => ['#ccfbf1','#0f766e'],
                'closed' => ['#fef3c7','#d97706'],
                'locked' => ['#f1f5f9','#475569'],
            ];
            [$sBg,$sColor] = $statusColors[$session->status] ?? ['#f1f5f9','#475569'];
            $sv = $session->cash_variance ?? 0;
        @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);">

            {{-- Card header --}}
            <div style="padding:16px 20px;background:#fff;">
                <div style="display:flex;align-items:start;justify-content:space-between;gap:12px;">
                    <div style="display:flex;align-items:start;gap:12px;flex:1;min-width:0;">
                        <button wire:click="toggleExpand({{ $session->id }})"
                                style="margin-top:2px;padding:4px;border-radius:6px;background:transparent;border:none;cursor:pointer;transition:background 0.15s;"
                                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                            <svg style="width:16px;height:16px;color:#94a3b8;transition:transform 0.2s;transform:{{ $isExpanded ? 'rotate(90deg)' : 'none' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <span style="font-weight:600;font-size:15px;color:#0f172a;">{{ $session->shop->name ?? '—' }}</span>
                                <span style="font-size:12px;padding:3px 10px;border-radius:20px;font-weight:500;background:{{ $sBg }};color:{{ $sColor }};">{{ ucfirst($session->status) }}</span>
                            </div>
                            <div style="font-size:12px;margin-top:4px;color:#475569;">
                                Opened {{ $session->opened_at->format('H:i') }} by {{ $session->openedBy->name ?? '—' }}
                                @if($session->closed_at) · Closed {{ $session->closed_at->format('H:i') }} @endif
                                @if($session->locked_at) · Locked {{ $session->locked_at->format('H:i') }} @endif
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:20px;flex-shrink:0;">
                        <div style="text-align:right;display:none;" class="sm:block">
                            <div style="font-size:11px;color:#475569;">Revenue</div>
                            <div style="font-mono;font-weight:700;font-size:15px;color:#0f766e;">{{ number_format($session->total_sales ?? 0) }}</div>
                        </div>
                        <div style="text-align:right;display:none;" class="sm:block">
                            <div style="font-size:11px;color:#475569;">Variance</div>
                            <div style="font-weight:700;font-size:15px;color:{{ $sv > 0 ? '#0f766e' : ($sv < 0 ? '#e11d48' : '#94a3b8') }};">
                                {{ $sv >= 0 ? '+' : '' }}{{ number_format($sv) }}
                            </div>
                        </div>
                        @if($session->status === 'closed')
                            <button wire:click="lockSession({{ $session->id }})"
                                    wire:confirm="Lock this session? It cannot be edited after locking."
                                    style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:500;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;cursor:pointer;transition:all 0.15s;">
                                Lock
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Expanded detail --}}
            @if ($isExpanded)
            <div style="padding:20px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                <div class="space-y-5">

                    {{-- Timeline --}}
                    <div>
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">Timeline</div>
                        <div style="display:flex;align-items:start;gap:0;">
                            <div style="display:flex;flex-direction:column;align-items:center;margin-right:12px;">
                                <div style="width:12px;height:12px;border-radius:50%;background:#0f766e;flex-shrink:0;"></div>
                                <div style="width:1px;flex:1;margin-top:4px;background:#e2e8f0;min-height:16px;"></div>
                            </div>
                            <div style="padding-bottom:12px;flex:1;">
                                <div style="font-size:12px;font-weight:600;color:#0f766e;">Opened</div>
                                <div style="font-size:12px;margin-top:2px;color:#475569;">{{ $session->opened_at->format('H:i') }} · {{ $session->openedBy->name ?? '—' }}</div>
                            </div>
                        </div>
                        @if($session->closed_at)
                        <div style="display:flex;align-items:start;gap:0;">
                            <div style="display:flex;flex-direction:column;align-items:center;margin-right:12px;">
                                <div style="width:12px;height:12px;border-radius:50%;background:#d97706;flex-shrink:0;"></div>
                                @if($session->locked_at)<div style="width:1px;flex:1;margin-top:4px;background:#e2e8f0;min-height:16px;"></div>@endif
                            </div>
                            <div style="{{ $session->locked_at ? 'padding-bottom:12px;' : '' }}flex:1;">
                                <div style="font-size:12px;font-weight:600;color:#d97706;">Closed</div>
                                <div style="font-size:12px;margin-top:2px;color:#475569;">{{ $session->closed_at->format('H:i') }} · {{ $session->closedBy->name ?? '—' }}</div>
                            </div>
                        </div>
                        @endif
                        @if($session->locked_at)
                        <div style="display:flex;align-items:start;gap:0;">
                            <div style="margin-right:12px;"><div style="width:12px;height:12px;border-radius:50%;background:#94a3b8;flex-shrink:0;"></div></div>
                            <div style="flex:1;">
                                <div style="font-size:12px;font-weight:600;color:#475569;">Locked</div>
                                <div style="font-size:12px;margin-top:2px;color:#94a3b8;">{{ $session->locked_at->format('H:i') }} · {{ $session->lockedBy->name ?? '—' }}</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Revenue by Channel --}}
                    <div>
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">Revenue by Channel</div>
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                            @php
                                $revChannels = [
                                    ['Cash',          $session->total_sales_cash             ?? 0, '#0f766e'],
                                    ['Mobile Money',  $session->total_sales_momo              ?? 0, '#0284c7'],
                                    ['Card',          $session->total_sales_card              ?? 0, '#7c3aed'],
                                    ['Bank Transfer', $session->total_sales_bank_transfer     ?? 0, '#475569'],
                                    ['Credit',        $session->total_sales_credit            ?? 0, '#d97706'],
                                ];
                                $revTotal = $session->total_sales ?? 0;
                            @endphp
                            @foreach ($revChannels as [$ch, $amt, $color])
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 14px;border-bottom:1px solid #e2e8f0;">
                                <span style="font-size:12px;color:#475569;">{{ $ch }}</span>
                                <span style="font-size:12px;font-weight:600;color:{{ $color }};">{{ number_format($amt) }} RWF</span>
                            </div>
                            @endforeach
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#f8fafc;border-top:2px solid #e2e8f0;">
                                <span style="font-size:12px;font-weight:700;color:#0f172a;">Total</span>
                                <span style="font-size:12px;font-weight:700;color:#0284c7;">{{ number_format($revTotal) }} RWF</span>
                            </div>
                        </div>
                    </div>

                    {{-- Cash Reconciliation --}}
                    <div>
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">Cash Reconciliation</div>
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                            @php
                                $recon = [
                                    ['Opening balance',             $session->opening_balance            ?? 0, false],
                                    ['+ Cash sales',                $session->total_sales_cash           ?? 0, false],
                                    ['+ Cash repayments received',  $session->total_repayments_cash      ?? 0, false],
                                    ['− Cash refunds',              $session->total_refunds_cash         ?? 0, true],
                                    ['− Cash expenses',             $session->total_expenses_cash        ?? 0, true],
                                    ['− Cash withdrawals',          $session->total_withdrawals_cash     ?? 0, true],
                                    ['− Cash deposits to bank',     $session->cash_deposits              ?? $session->total_bank_deposits ?? 0, true],
                                    ['= Expected cash',             $session->expected_cash              ?? 0, false],
                                    ['Actual cash counted',         $session->actual_cash_counted        ?? 0, false],
                                    ['Sent to owner via MoMo',      $session->cash_to_owner_momo         ?? 0, false],
                                    ['Cash retained in shop',       $session->cash_retained              ?? 0, false],
                                ];
                            @endphp
                            @foreach ($recon as [$label, $amount, $isDeduc])
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 14px;{{ str_starts_with($label,'=') ? 'background:#f8fafc;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;' : 'border-bottom:1px solid #e2e8f0;' }}">
                                <span style="font-size:12px;{{ str_starts_with($label,'=') ? 'font-weight:600;color:#0f172a;' : 'color:#475569;' }}">{{ $label }}</span>
                                <span style="font-size:12px;{{ str_starts_with($label,'=') ? 'font-weight:700;color:#0f172a;' : 'font-weight:500;' }}{{ $isDeduc ? 'color:#e11d48;' : '' }}">
                                    {{ number_format($amount) }} RWF
                                </span>
                            </div>
                            @endforeach
                            @if($session->cash_variance !== null)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:{{ $session->cash_variance < 0 ? '#ffe4e6' : ($session->cash_variance > 0 ? '#ccfbf1' : '#f8fafc') }};">
                                <span style="font-size:12px;font-weight:600;color:#0f172a;">Variance</span>
                                <span style="font-size:12px;font-weight:700;color:{{ $session->cash_variance > 0 ? '#0f766e' : ($session->cash_variance < 0 ? '#e11d48' : '#94a3b8') }};">
                                    {{ $session->cash_variance >= 0 ? '+' : '' }}{{ number_format($session->cash_variance) }} RWF
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Non-cash settlements --}}
                    @php
                        $reportSettlements = [
                            ['Mobile Money',  $session->total_sales_momo          ?? 0, $session->momo_settled          ?? 0, $session->momo_settled_ref,          '#0284c7'],
                            ['Card',          $session->total_sales_card          ?? 0, $session->card_settled          ?? 0, $session->card_settled_ref,          '#7c3aed'],
                            ['Bank Transfer', $session->total_sales_bank_transfer ?? 0, $session->bank_transfer_settled ?? 0, $session->bank_transfer_settled_ref, '#475569'],
                            ['Other',         $session->total_sales_other         ?? 0, $session->other_settled         ?? 0, $session->other_settled_ref,         '#475569'],
                        ];
                        $creditAmt     = $session->total_sales_credit ?? 0;
                        $hasAnyNonCash = collect($reportSettlements)->contains(fn($r) => $r[1] > 0) || $creditAmt > 0;
                    @endphp
                    @if($hasAnyNonCash)
                    <div>
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">Non-Cash Channel Settlement</div>
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                            @foreach ($reportSettlements as [$ch, $salesAmt, $settledAmt, $ref, $color])
                            @if($salesAmt > 0)
                            <div style="padding:10px 14px;display:flex;align-items:start;justify-content:space-between;gap:12px;border-bottom:1px solid #e2e8f0;">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:12px;font-weight:600;color:#0f172a;">{{ $ch }}</div>
                                    <div style="font-size:11px;margin-top:2px;color:#475569;">Sales: {{ number_format($salesAmt) }} RWF @if($ref) · Ref: {{ $ref }} @endif</div>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <div style="font-size:12px;font-weight:600;color:{{ $color }};">{{ number_format($settledAmt) }} RWF</div>
                                    @if($settledAmt < $salesAmt)
                                        <div style="font-size:11px;margin-top:2px;color:#e11d48;">−{{ number_format($salesAmt - $settledAmt) }} unaccounted</div>
                                    @elseif($settledAmt > 0)
                                        <div style="font-size:11px;margin-top:2px;color:#0f766e;">Settled</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            @endforeach
                            @if($creditAmt > 0)
                            <div style="padding:10px 14px;display:flex;align-items:start;justify-content:space-between;gap:12px;background:#fef3c7;">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:12px;font-weight:600;color:#d97706;">Credit</div>
                                    <div style="font-size:11px;margin-top:2px;color:#475569;">Owed by customers — tracked via credit accounts</div>
                                </div>
                                <div style="font-size:12px;font-weight:600;flex-shrink:0;color:#d97706;">{{ number_format($creditAmt) }} RWF</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Expenses + Withdrawals --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="exp-wd-grid">
                        <style>.exp-wd-grid{grid-template-columns:1fr 1fr;} @media(max-width:600px){.exp-wd-grid{grid-template-columns:1fr!important;}}</style>

                        <div>
                            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">
                                Expenses
                                @if($session->expenses->count() > 0)
                                    <span style="margin-left:4px;padding:2px 6px;border-radius:20px;background:#f8fafc;color:#475569;font-weight:normal;text-transform:none;font-size:11px;">{{ $session->expenses->count() }}</span>
                                @endif
                            </div>
                            @if($session->expenses->isEmpty())
                                <div style="text-align:center;padding:12px;border-radius:10px;font-size:12px;color:#94a3b8;background:#f8fafc;border:1px solid #e2e8f0;">No expenses</div>
                            @else
                                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                                    @foreach($session->expenses as $exp)
                                    <div style="padding:8px 14px;display:flex;align-items:start;justify-content:space-between;gap:12px;border-bottom:1px solid #e2e8f0;">
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-size:12px;font-weight:500;color:#0f172a;">{{ $exp->category->name ?? '—' }}</div>
                                            @if($exp->description)<div style="font-size:11px;margin-top:1px;color:#475569;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $exp->description }}</div>@endif
                                        </div>
                                        <div style="font-size:12px;font-weight:600;flex-shrink:0;color:#e11d48;">{{ number_format($exp->amount) }}</div>
                                    </div>
                                    @endforeach
                                    <div style="padding:8px 14px;display:flex;justify-content:space-between;background:#f8fafc;">
                                        <span style="font-size:12px;font-weight:600;color:#475569;">Total</span>
                                        <span style="font-size:12px;font-weight:700;color:#e11d48;">{{ number_format($session->expenses->sum('amount')) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">
                                Owner Withdrawals
                                @if($session->ownerWithdrawals->count() > 0)
                                    <span style="margin-left:4px;padding:2px 6px;border-radius:20px;background:#f8fafc;color:#475569;font-weight:normal;text-transform:none;font-size:11px;">{{ $session->ownerWithdrawals->count() }}</span>
                                @endif
                            </div>
                            @if($session->ownerWithdrawals->isEmpty())
                                <div style="text-align:center;padding:12px;border-radius:10px;font-size:12px;color:#94a3b8;background:#f8fafc;border:1px solid #e2e8f0;">No withdrawals</div>
                            @else
                                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                                    @foreach($session->ownerWithdrawals as $wd)
                                    <div style="padding:8px 14px;display:flex;align-items:start;justify-content:space-between;gap:12px;border-bottom:1px solid #e2e8f0;">
                                        <div style="flex:1;min-width:0;">
                                            @if($wd->reason)<div style="font-size:12px;font-weight:500;color:#0f172a;">{{ $wd->reason }}</div>@endif
                                            <div style="font-size:11px;margin-top:1px;color:#475569;">{{ $wd->created_at->format('H:i') }}@if($wd->recordedBy) · {{ $wd->recordedBy->name }}@endif</div>
                                        </div>
                                        <div style="font-size:12px;font-weight:600;flex-shrink:0;color:#d97706;">{{ number_format($wd->amount) }}</div>
                                    </div>
                                    @endforeach
                                    <div style="padding:8px 14px;display:flex;justify-content:space-between;background:#f8fafc;">
                                        <span style="font-size:12px;font-weight:600;color:#475569;">Total</span>
                                        <span style="font-size:12px;font-weight:700;color:#d97706;">{{ number_format($session->ownerWithdrawals->sum('amount')) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Bank Deposits --}}
                    @if($session->bankDeposits->isNotEmpty())
                    <div>
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:10px;">
                            Bank Deposits
                            <span style="margin-left:4px;padding:2px 6px;border-radius:20px;background:#f8fafc;color:#475569;font-weight:normal;text-transform:none;font-size:11px;">{{ $session->bankDeposits->count() }}</span>
                        </div>
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                            @foreach($session->bankDeposits as $dep)
                            <div style="padding:8px 14px;display:flex;align-items:start;justify-content:space-between;gap:12px;border-bottom:1px solid #e2e8f0;">
                                <div style="flex:1;min-width:0;">
                                    @if($dep->bank_reference)<div style="font-size:12px;font-weight:500;color:#0f172a;">Ref: {{ $dep->bank_reference }}</div>@endif
                                    @if($dep->notes)<div style="font-size:11px;margin-top:1px;color:#475569;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $dep->notes }}</div>@endif
                                    <div style="font-size:11px;margin-top:1px;color:#475569;">{{ $dep->deposited_at->format('H:i') }}@if($dep->depositedBy) · {{ $dep->depositedBy->name }}@endif</div>
                                </div>
                                <div style="font-size:12px;font-weight:600;flex-shrink:0;color:#0284c7;">{{ number_format($dep->amount) }}</div>
                            </div>
                            @endforeach
                            <div style="padding:8px 14px;display:flex;justify-content:space-between;background:#f8fafc;">
                                <span style="font-size:12px;font-weight:600;color:#475569;">Total</span>
                                <span style="font-size:12px;font-weight:700;color:#0284c7;">{{ number_format($session->bankDeposits->sum('amount')) }}</span>
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
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Chart.js donut for payment mix --}}
    @if(!$sessions->isEmpty())
    @script
    <script>
    (function() {
        function drawDailyDonut() {
            const canvas = document.getElementById('daily-donut-chart');
            if (!canvas || !window.Chart) return;
            if (canvas._chartInstance) { canvas._chartInstance.destroy(); }
            const ctx = canvas.getContext('2d');
            canvas._chartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Cash','MoMo','Card','Bank','Credit'],
                    datasets: [{
                        data: [{{ $pCash }}, {{ $pMomo }}, {{ $pCard }}, {{ $pBank }}, {{ $pCredit }}],
                        backgroundColor: ['#0f766e','#0284c7','#7c3aed','#475569','#d97706'],
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.9)',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ' ' + ctx.label + ': ' + ctx.raw.toLocaleString() + ' RWF'
                            }
                        }
                    }
                }
            });
        }
        drawDailyDonut();
        Livewire.hook('commit', ({ succeed }) => { succeed(() => { setTimeout(drawDailyDonut, 0); }); });
    })();
    </script>
    @endscript
    @endif
</div>
