<div>
    {{-- ── Preset Period Buttons ── --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ([
            'today'      => 'Today',
            'yesterday'  => 'Yesterday',
            'this_week'  => 'This Week',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'last_30'    => 'Last 30 Days',
        ] as $key => $label)
            <button wire:click="setPreset('{{ $key }}')"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                    style="{{ $preset === $key
                        ? 'background:#0f766e;color:#fff;border:1px solid #0f766e;'
                        : 'background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ── Filters ── --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="flex items-center gap-2">
            <label class="text-xs font-medium flex-shrink-0" style="color:var(--text-dim);">From</label>
            <input type="date" wire:model.live="dateFrom"
                   class="px-3 py-2 rounded-lg text-sm"
                   style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs font-medium flex-shrink-0" style="color:var(--text-dim);">To</label>
            <input type="date" wire:model.live="dateTo"
                   class="px-3 py-2 rounded-lg text-sm"
                   style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);">
        </div>
        <select wire:model.live="shopFilter"
                class="px-3 py-2 rounded-lg text-sm"
                style="background:var(--surface-raised);border:1px solid var(--border);color:var(--text);">
            <option value="all">All Shops</option>
            @foreach ($shops as $shop)
                <option value="{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- ── KPI Cards ── --}}
    @php
        $rows_col            = collect($rows);
        $totalOpeningBalance = $rows_col->sum('opening_balance');
        $totalRevenue        = $rows_col->sum('revenue');
        $totalRepayments     = $rows_col->sum('repayments');
        $totalRefunds        = $rows_col->sum('refunds');
        $totalExpenses       = $rows_col->sum('expenses');
        $totalWithdrawals    = $rows_col->sum('withdrawals');
        $totalBanked         = $rows_col->sum('cash_banked');
        $totalVariance       = $rows_col->sum('total_variance');
        $sessionCount        = $rows_col->sum('session_count');
        $closedCount         = $rows_col->sum('closed_count');
        // Net Operating: revenue after refunds, expenses, and owner withdrawals
        $netOperating        = $totalRevenue - $totalRefunds - $totalExpenses - $totalWithdrawals;
        $days                = $rows_col->pluck('session_date')->unique()->count() ?: 1;
        $avgDailyVariance    = $days > 0 ? round($totalVariance / $days) : 0;
        $totalCashIn         = $totalOpeningBalance + $totalRevenue + $totalRepayments;
    @endphp

    {{-- Row 1: Starting point + inflows --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:12px;" class="kpi-row1-grid">
        <style>
            .kpi-row1-grid{grid-template-columns:repeat(4,1fr);}
            .kpi-row2-grid{grid-template-columns:repeat(3,1fr);}
            @media(max-width:1100px){.kpi-row1-grid{grid-template-columns:repeat(2,1fr)!important;}}
            @media(max-width:900px){.kpi-row2-grid{grid-template-columns:repeat(2,1fr)!important;}}
            @media(max-width:500px){.kpi-row1-grid,.kpi-row2-grid{grid-template-columns:1fr!important;}}
        </style>

        {{-- Opening Balance --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Opening Balance</div>
            <div style="font-size:24px;font-weight:700;line-height:1.2;margin-bottom:6px;letter-spacing:-0.5px;color:#475569;">
                {{ number_format($totalOpeningBalance) }}
                <span style="font-size:13px;font-weight:500;color:#94a3b8;">RWF</span>
            </div>
            <div style="font-size:12px;color:#475569;">{{ $days }} day{{ $days !== 1 ? 's' : '' }} · {{ $sessionCount }} session{{ $sessionCount !== 1 ? 's' : '' }}</div>
            <div style="height:4px;border-radius:2px;background:#f1f5f9;overflow:hidden;margin-top:12px;">
                <div style="height:100%;border-radius:2px;width:100%;background:#94a3b8;"></div>
            </div>
        </div>

        {{-- Gross Revenue --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Gross Revenue</div>
            <div style="font-size:24px;font-weight:700;line-height:1.2;margin-bottom:6px;letter-spacing:-0.5px;color:#0f766e;">
                {{ number_format($totalRevenue) }}
                <span style="font-size:13px;font-weight:500;color:#94a3b8;">RWF</span>
            </div>
            <div style="font-size:12px;color:#475569;">cash in from sales</div>
            <div style="height:4px;border-radius:2px;background:#f1f5f9;overflow:hidden;margin-top:12px;">
                <div style="height:100%;border-radius:2px;width:100%;background:#0f766e;"></div>
            </div>
        </div>

        {{-- Credit Repayments --}}
        @php $repPct = $totalRevenue > 0 ? min(100, round($totalRepayments / ($totalRevenue ?: 1) * 100)) : 0; @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Repayments Collected</div>
            <div style="font-size:24px;font-weight:700;line-height:1.2;margin-bottom:6px;letter-spacing:-0.5px;color:#0891b2;">
                {{ number_format($totalRepayments) }}
                <span style="font-size:13px;font-weight:500;color:#94a3b8;">RWF</span>
            </div>
            <div style="font-size:12px;color:#475569;">{{ $repPct }}% of revenue · debt collected</div>
            <div style="height:4px;border-radius:2px;background:#f1f5f9;overflow:hidden;margin-top:12px;">
                <div style="height:100%;border-radius:2px;width:{{ $repPct }}%;background:#0891b2;"></div>
            </div>
        </div>

        {{-- Cash Refunds --}}
        @php $refPct = $totalRevenue > 0 ? min(100, round($totalRefunds / ($totalRevenue ?: 1) * 100)) : 0; @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Cash Refunds (Returns)</div>
            <div style="font-size:24px;font-weight:700;line-height:1.2;margin-bottom:6px;letter-spacing:-0.5px;color:#d97706;">
                {{ number_format($totalRefunds) }}
                <span style="font-size:13px;font-weight:500;color:#94a3b8;">RWF</span>
            </div>
            <div style="font-size:12px;color:#475569;">{{ $refPct }}% of revenue · cash out</div>
            <div style="height:4px;border-radius:2px;background:#f1f5f9;overflow:hidden;margin-top:12px;">
                <div style="height:100%;border-radius:2px;width:{{ $refPct }}%;background:#d97706;"></div>
            </div>
        </div>
    </div>

    {{-- Row 2: Outflows + Net --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;" class="kpi-row2-grid">
        {{-- Total Expenses --}}
        @php $expPct = $totalRevenue > 0 ? min(100, round($totalExpenses / ($totalRevenue ?: 1) * 100)) : 0; @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Total Expenses</div>
            <div style="font-size:24px;font-weight:700;line-height:1.2;margin-bottom:6px;letter-spacing:-0.5px;color:#e11d48;">
                {{ number_format($totalExpenses) }}
                <span style="font-size:13px;font-weight:500;color:#94a3b8;">RWF</span>
            </div>
            <div style="font-size:12px;color:#475569;">{{ $expPct }}% of revenue</div>
            <div style="height:4px;border-radius:2px;background:#f1f5f9;overflow:hidden;margin-top:12px;">
                <div style="height:100%;border-radius:2px;width:{{ $expPct }}%;background:#e11d48;"></div>
            </div>
        </div>

        {{-- Owner Withdrawals --}}
        @php $wdlPct = $totalRevenue > 0 ? min(100, round($totalWithdrawals / ($totalRevenue ?: 1) * 100)) : 0; @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Owner Withdrawals</div>
            <div style="font-size:24px;font-weight:700;line-height:1.2;margin-bottom:6px;letter-spacing:-0.5px;color:#7c3aed;">
                {{ number_format($totalWithdrawals) }}
                <span style="font-size:13px;font-weight:500;color:#94a3b8;">RWF</span>
            </div>
            <div style="font-size:12px;color:#475569;">{{ $wdlPct }}% of revenue</div>
            <div style="height:4px;border-radius:2px;background:#f1f5f9;overflow:hidden;margin-top:12px;">
                <div style="height:100%;border-radius:2px;width:{{ $wdlPct }}%;background:#7c3aed;"></div>
            </div>
        </div>

        {{-- Net Operating --}}
        @php $netPct = $totalRevenue > 0 ? min(100, max(0, round($netOperating / ($totalRevenue ?: 1) * 100))) : 0; @endphp
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Net Operating</div>
            <div style="font-size:24px;font-weight:700;line-height:1.2;margin-bottom:6px;letter-spacing:-0.5px;color:#0284c7;">
                {{ number_format($netOperating) }}
                <span style="font-size:13px;font-weight:500;color:#94a3b8;">RWF</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:12px;color:#475569;">Margin {{ $netPct }}%</span>
                <span style="font-size:12px;padding:2px 8px;border-radius:20px;font-weight:500;background:{{ $closedCount >= $sessionCount && $sessionCount > 0 ? '#ccfbf1' : '#fef3c7' }};color:{{ $closedCount >= $sessionCount && $sessionCount > 0 ? '#0f766e' : '#d97706' }};">
                    {{ $closedCount }}/{{ $sessionCount }} closed
                </span>
            </div>
            <div style="height:4px;border-radius:2px;background:#f1f5f9;overflow:hidden;margin-top:12px;">
                <div style="height:100%;border-radius:2px;width:{{ $netPct }}%;background:#0284c7;"></div>
            </div>
        </div>
    </div>

    {{-- ── Dual Chart Row ── --}}
    @if(!empty($chartData['labels']))
    <div style="display:grid;grid-template-columns:1.6fr 1fr;gap:16px;margin-bottom:20px;" class="chart-row-grid">
        <style>.chart-row-grid{grid-template-columns:1.6fr 1fr;} @media(max-width:900px){.chart-row-grid{grid-template-columns:1fr!important;}}</style>

        {{-- Line trend chart --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                <span style="font-size:15px;font-weight:600;color:#0f172a;letter-spacing:-0.2px;">Revenue & expense trend</span>
                <span style="font-size:12px;color:#475569;">RWF</span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:12px;">
                <span style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:500;color:#475569;"><span style="width:10px;height:10px;border-radius:3px;background:#0f766e;flex-shrink:0;"></span>Revenue</span>
                <span style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:500;color:#475569;"><span style="width:10px;height:10px;border-radius:3px;background:#0891b2;flex-shrink:0;"></span>Repayments</span>
                <span style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:500;color:#475569;"><span style="width:10px;height:10px;border-radius:3px;background:#e11d48;flex-shrink:0;"></span>Expenses</span>
                <span style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:500;color:#475569;"><span style="width:10px;height:10px;border-radius:3px;background:#0284c7;flex-shrink:0;"></span>Net operating</span>
            </div>
            <div style="position:relative;height:200px;">
                <script id="finance-trend-data" type="application/json">@json($chartData)</script>
                <canvas id="finance-trend-chart"></canvas>
            </div>
        </div>

        {{-- Grouped bar chart --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                <span style="font-size:15px;font-weight:600;color:#0f172a;letter-spacing:-0.2px;">Revenue vs expenses</span>
                <span style="font-size:12px;color:#475569;">Grouped</span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:12px;">
                <span style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:500;color:#475569;"><span style="width:10px;height:10px;border-radius:3px;background:#0f766e;flex-shrink:0;"></span>Revenue</span>
                <span style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:500;color:#475569;"><span style="width:10px;height:10px;border-radius:3px;background:#ffe4e6;border:1px solid #e11d48;flex-shrink:0;"></span>Expenses</span>
            </div>
            <div style="position:relative;height:200px;">
                <canvas id="finance-bar-chart"></canvas>
            </div>
        </div>
    </div>

    @script
    <script>
    (function() {
        let trendChart = null;
        let barChart = null;

        function getChartData() {
            const el = document.getElementById('finance-trend-data');
            return el ? JSON.parse(el.textContent) : { labels: [], revenue: [], expenses: [], net: [] };
        }

        function drawCharts() {
            const data = getChartData();
            const grid = 'rgba(0,0,0,0.04)';
            const txt  = '#64748b';

            // Line trend chart
            const trendCanvas = document.getElementById('finance-trend-chart');
            if (trendCanvas) {
                if (trendChart) {
                    trendChart.data.labels = data.labels;
                    trendChart.data.datasets[0].data = data.revenue;
                    trendChart.data.datasets[1].data = data.repayments || [];
                    trendChart.data.datasets[2].data = data.expenses;
                    trendChart.data.datasets[3].data = data.net || [];
                    trendChart.update();
                } else {
                    trendChart = new Chart(trendCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                { label: 'Revenue',    data: data.revenue,          borderColor: '#0f766e', backgroundColor: 'rgba(15,118,110,0.05)', borderWidth: 2.5, pointBackgroundColor: '#0f766e', pointRadius: 3, tension: 0.4, fill: true },
                                { label: 'Repayments', data: data.repayments || [], borderColor: '#0891b2', borderWidth: 2, pointBackgroundColor: '#0891b2', pointRadius: 3, tension: 0.4, borderDash: [6,3], fill: false },
                                { label: 'Expenses',   data: data.expenses,         borderColor: '#e11d48', borderWidth: 2, pointBackgroundColor: '#e11d48', pointRadius: 3, tension: 0.4, borderDash: [4,3], fill: false },
                                { label: 'Net',        data: data.net || [],        borderColor: '#0284c7', borderWidth: 2, pointBackgroundColor: '#0284c7', pointRadius: 3, tension: 0.4, borderDash: [2,3], fill: false },
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { mode: 'index', intersect: false, backgroundColor: 'rgba(15,23,42,0.9)', titleFont: { size: 13 }, bodyFont: { size: 12 }, padding: 10, cornerRadius: 8 }
                            },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: txt, font: { size: 11 } } },
                                y: { grid: { color: grid }, border: { display: false }, ticks: { color: txt, font: { size: 11 } } }
                            }
                        }
                    });
                }
            }

            // Bar chart
            const barCanvas = document.getElementById('finance-bar-chart');
            if (barCanvas) {
                if (barChart) {
                    barChart.data.labels = data.labels;
                    barChart.data.datasets[0].data = data.revenue;
                    barChart.data.datasets[1].data = data.expenses;
                    barChart.update();
                } else {
                    barChart = new Chart(barCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [
                                { label: 'Revenue',  data: data.revenue,  backgroundColor: '#0f766e', borderRadius: 4, categoryPercentage: 0.75, barPercentage: 0.6 },
                                { label: 'Expenses', data: data.expenses, backgroundColor: '#ffe4e6', borderColor: '#e11d48', borderWidth: 1, borderRadius: 4, categoryPercentage: 0.75, barPercentage: 0.6 },
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { backgroundColor: 'rgba(15,23,42,0.9)', padding: 10, cornerRadius: 8, mode: 'index' }
                            },
                            scales: {
                                x: { grid: { display: false }, border: { display: false }, ticks: { color: txt, font: { size: 11 } } },
                                y: { grid: { color: grid }, border: { display: false }, ticks: { color: txt, font: { size: 11 } } }
                            }
                        }
                    });
                }
            }
        }

        drawCharts();
        Livewire.hook('commit', ({ succeed }) => { succeed(() => { setTimeout(drawCharts, 0); }); });
    })();
    </script>
    @endscript
    @endif

    {{-- ── No data ── --}}
    @if(empty($rows))
        <div class="text-center py-12 rounded-xl" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-sm" style="color:var(--text-dim);">No sessions found for this period.</div>
        </div>
    @else

    {{-- ── MOBILE: card layout ── --}}
    <div class="space-y-3 sm:hidden">
        @foreach ($rows as $row)
            @php
                $rowKey = $row['session_date'] . ':' . $row['shop_id'];
                $isExpanded = $expandedKey === $rowKey;
                $rv = $row['total_variance'];
            @endphp
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div class="px-4 pt-4 pb-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-semibold" style="color:var(--text);">{{ $row['shop_name'] }}</div>
                            <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                                {{ \Carbon\Carbon::parse($row['session_date'])->format('d M Y · D') }}
                            </div>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded flex-shrink-0"
                              style="{{ $row['closed_count'] >= $row['session_count'] ? 'background:#ccfbf1;color:#0f766e' : 'background:#fef3c7;color:#d97706' }}">
                            {{ $row['closed_count'] }}/{{ $row['session_count'] }} closed
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-3" style="border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                    <div class="px-3 py-2.5 text-center" style="border-right:1px solid #e2e8f0;">
                        <div class="text-xs mb-0.5" style="color:#475569;">Opening</div>
                        <div class="font-mono font-semibold text-sm" style="color:#64748b;">{{ number_format($row['opening_balance']) }}</div>
                    </div>
                    <div class="px-3 py-2.5 text-center" style="border-right:1px solid #e2e8f0;">
                        <div class="text-xs mb-0.5" style="color:#475569;">Revenue</div>
                        <div class="font-mono font-semibold text-sm" style="color:#0f766e;">{{ number_format($row['revenue']) }}</div>
                    </div>
                    <div class="px-3 py-2.5 text-center">
                        <div class="text-xs mb-0.5" style="color:#475569;">Variance</div>
                        <div class="font-mono font-semibold text-sm"
                             style="color:{{ $rv < 0 ? '#e11d48' : ($rv > 0 ? '#d97706' : '#94a3b8') }};">
                            {{ $rv >= 0 ? '+' : '' }}{{ number_format($rv) }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between px-4 py-2.5">
                    <div class="text-xs" style="color:#475569;">
                        Exp: <span class="font-mono" style="color:#e11d48;">{{ number_format($row['expenses']) }}</span>
                        · Banked: <span class="font-mono" style="color:#0284c7;">{{ number_format($row['cash_banked']) }}</span>
                        @if($row['repayments'] > 0)
                            · Rep: <span class="font-mono" style="color:#0891b2;">{{ number_format($row['repayments']) }}</span>
                        @endif
                        @if($row['withdrawals'] > 0)
                            · W/D: <span class="font-mono" style="color:#7c3aed;">{{ number_format($row['withdrawals']) }}</span>
                        @endif
                    </div>
                    <button wire:click="toggleRow('{{ $row['session_date'] }}', {{ $row['shop_id'] }})"
                            class="text-xs px-2.5 py-1.5 rounded-lg font-medium"
                            style="color:#0f766e;background:#ccfbf1;border:none;cursor:pointer;">
                        {{ $isExpanded ? 'Hide ▲' : 'Details ▾' }}
                    </button>
                </div>

                @if ($isExpanded && $expandedSessions->isNotEmpty())
                    <div class="px-4 pb-4 space-y-3" style="border-top:1px solid #e2e8f0;background:#f8fafc;">
                        <div class="pt-3 text-xs font-semibold uppercase tracking-wide" style="color:#94a3b8;">Sessions</div>
                        @foreach ($expandedSessions as $s)
                            @php $sv = $s->cash_variance ?? 0; @endphp
                            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px;">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-xs font-medium" style="color:#0f172a;">
                                        {{ $s->opened_at?->format('H:i') }} – {{ $s->closed_at?->format('H:i') ?? '—' }}
                                        <span style="color:#475569;">· {{ $s->openedBy->name ?? '—' }}</span>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded"
                                          style="{{ $s->isLocked() ? 'color:#475569;border:1px solid #e2e8f0' : 'background:#ccfbf1;color:#0f766e' }}">
                                        {{ ucfirst($s->status) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div><span style="color:#475569;">Opening</span> <span class="font-mono" style="color:#64748b;">{{ number_format($s->opening_balance ?? 0) }}</span></div>
                                    <div><span style="color:#475569;">Sales</span> <span class="font-mono" style="color:#0f766e;">{{ number_format($s->total_sales ?? 0) }}</span></div>
                                    <div><span style="color:#475569;">Repayments</span> <span class="font-mono" style="color:#0891b2;">{{ number_format($s->total_repayments ?? 0) }}</span></div>
                                    <div><span style="color:#475569;">Refunds</span> <span class="font-mono" style="color:#d97706;">{{ number_format($s->total_refunds_cash ?? 0) }}</span></div>
                                    <div><span style="color:#475569;">Expenses</span> <span class="font-mono" style="color:#e11d48;">{{ number_format($s->total_expenses ?? 0) }}</span></div>
                                    <div><span style="color:#475569;">Banked</span> <span class="font-mono" style="color:#0284c7;">{{ number_format($s->total_bank_deposits ?? 0) }}</span></div>
                                    <div><span style="color:#475569;">Variance</span>
                                        <span class="font-mono" style="color:{{ $sv < 0 ? '#e11d48' : ($sv > 0 ? '#d97706' : '#94a3b8') }};">
                                            {{ $sv >= 0 ? '+' : '' }}{{ number_format($sv) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ── DESKTOP: table layout ── --}}
    <div class="hidden sm:block rounded-xl overflow-hidden" style="border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div class="overflow-x-auto">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                        <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Date</th>
                        <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Shop</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Opening</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Revenue</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:600;color:#0891b2;text-transform:uppercase;letter-spacing:0.5px;">Repayments</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:600;color:#d97706;text-transform:uppercase;letter-spacing:0.5px;">Refunds</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Expenses</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Withdrawals</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Banked</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Variance</th>
                        <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Sessions</th>
                        <th style="padding:10px 14px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @php
                            $rowKey     = $row['session_date'] . ':' . $row['shop_id'];
                            $isExpanded = $expandedKey === $rowKey;
                            $rv         = $row['total_variance'];
                        @endphp
                        <tr style="border-bottom:1px solid #e2e8f0;background:#fff;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                            <td style="padding:10px 14px;">
                                <div style="font-size:12px;font-weight:600;color:#0f172a;">{{ \Carbon\Carbon::parse($row['session_date'])->format('d M Y') }}</div>
                                <div style="font-size:11px;color:#94a3b8;">{{ \Carbon\Carbon::parse($row['session_date'])->format('D') }}</div>
                            </td>
                            <td style="padding:10px 14px;font-size:12px;font-weight:500;color:#0f172a;">{{ $row['shop_name'] }}</td>
                            <td style="padding:10px 14px;text-align:right;font-size:12px;color:#64748b;">{{ number_format($row['opening_balance']) }}</td>
                            <td style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:#0f766e;">{{ number_format($row['revenue']) }}</td>
                            <td style="padding:10px 14px;text-align:right;font-size:12px;color:{{ $row['repayments'] > 0 ? '#0891b2' : '#94a3b8' }};">{{ number_format($row['repayments']) }}</td>
                            <td style="padding:10px 14px;text-align:right;font-size:12px;color:{{ $row['refunds'] > 0 ? '#d97706' : '#94a3b8' }};">{{ number_format($row['refunds']) }}</td>
                            <td style="padding:10px 14px;text-align:right;font-size:12px;color:#e11d48;">{{ number_format($row['expenses']) }}</td>
                            <td style="padding:10px 14px;text-align:right;font-size:12px;color:#7c3aed;">{{ number_format($row['withdrawals']) }}</td>
                            <td style="padding:10px 14px;text-align:right;font-size:12px;color:#0284c7;">{{ number_format($row['cash_banked']) }}</td>
                            <td style="padding:10px 14px;text-align:right;font-size:12px;font-weight:600;color:{{ $rv < 0 ? '#e11d48' : ($rv > 0 ? '#d97706' : '#94a3b8') }};">
                                {{ $rv >= 0 ? '+' : '' }}{{ number_format($rv) }}
                            </td>
                            <td style="padding:10px 14px;text-align:center;font-size:12px;">
                                <span style="color:{{ $row['closed_count'] >= $row['session_count'] ? '#0f766e' : '#d97706' }};">{{ $row['closed_count'] }}</span><span style="color:#94a3b8;">/{{ $row['session_count'] }}</span>
                            </td>
                            <td style="padding:10px 14px;text-align:right;">
                                <button wire:click="toggleRow('{{ $row['session_date'] }}', {{ $row['shop_id'] }})"
                                        style="font-size:12px;padding:4px 10px;border-radius:6px;color:#0f766e;border:1px solid #ccfbf1;background:#ccfbf1;cursor:pointer;">
                                    {{ $isExpanded ? 'Hide ▲' : 'Details ▾' }}
                                </button>
                            </td>
                        </tr>

                        @if ($isExpanded)
                            <tr style="background:#f8fafc;">
                                <td colspan="12" style="padding:16px 20px;">
                                    @if ($expandedSessions->isEmpty())
                                        <div style="font-size:12px;color:#94a3b8;">No sessions found.</div>
                                    @else
                                        <div style="display:grid;gap:12px;grid-template-columns:repeat(3,1fr);" class="session-drill-grid">
                                            <style>.session-drill-grid{grid-template-columns:repeat(3,1fr);} @media(max-width:900px){.session-drill-grid{grid-template-columns:1fr 1fr!important;}} @media(max-width:600px){.session-drill-grid{grid-template-columns:1fr!important;}}</style>
                                            @foreach ($expandedSessions as $s)
                                                @php $sv = $s->cash_variance ?? 0; @endphp
                                                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
                                                    <div style="display:flex;align-items:start;justify-content:space-between;gap:8px;margin-bottom:12px;">
                                                        <div>
                                                            <div style="font-size:12px;font-weight:600;color:#0f172a;">
                                                                {{ $s->opened_at?->format('H:i') }} – {{ $s->closed_at?->format('H:i') ?? 'open' }}
                                                            </div>
                                                            <div style="font-size:11px;margin-top:2px;color:#475569;">
                                                                {{ $s->openedBy->name ?? '—' }}
                                                                @if($s->closedBy && $s->closedBy->id !== $s->openedBy?->id)
                                                                    → {{ $s->closedBy->name }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <span style="font-size:11px;padding:2px 8px;border-radius:20px;flex-shrink:0;{{ $s->isLocked() ? 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;' : ($s->isClosed() ? 'background:#fef3c7;color:#d97706;' : 'background:#ccfbf1;color:#0f766e;') }}">
                                                            {{ ucfirst($s->status) }}
                                                        </span>
                                                    </div>

                                                    <div style="space-y:6px;">
                                                        @foreach([
                                                            ['Opening balance',  $s->opening_balance ?? 0,      '#64748b', false],
                                                            ['Revenue',          $s->total_sales ?? 0,          '#0f766e', false],
                                                            ['Repayments in',    $s->total_repayments ?? 0,     '#0891b2', ($s->total_repayments ?? 0) === 0],
                                                            ['Cash refunds',     $s->total_refunds_cash ?? 0,   '#d97706', ($s->total_refunds_cash ?? 0) === 0],
                                                            ['Expenses',         $s->total_expenses ?? 0,       '#e11d48', false],
                                                            ['Withdrawals',      $s->total_withdrawals ?? 0,    '#7c3aed', ($s->total_withdrawals ?? 0) === 0],
                                                            ['Bank deposits',    $s->total_bank_deposits ?? 0,  '#0284c7', ($s->total_bank_deposits ?? 0) === 0],
                                                        ] as [$lbl, $val, $clr, $skip])
                                                        @if(!$skip)
                                                        <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #e2e8f0;font-size:12px;">
                                                            <span style="color:#475569;">{{ $lbl }}</span>
                                                            <span style="font-weight:600;color:{{ $clr }};">{{ number_format($val) }}</span>
                                                        </div>
                                                        @endif
                                                        @endforeach
                                                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-top:2px solid #e2e8f0;font-size:12px;margin-top:4px;">
                                                            <span style="color:#0f172a;font-weight:600;">Variance</span>
                                                            <span style="font-weight:700;color:{{ $sv < 0 ? '#e11d48' : ($sv > 0 ? '#d97706' : '#94a3b8') }};">
                                                                {{ $sv >= 0 ? '+' : '' }}{{ number_format($sv) }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    @if($s->expenses->isNotEmpty())
                                                        <div style="margin-top:10px;">
                                                            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:6px;">Expenses</div>
                                                            @foreach($s->expenses as $exp)
                                                            <div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-top:1px solid #e2e8f0;">
                                                                <span style="color:#475569;">{{ $exp->category->name ?? '—' }}</span>
                                                                <span style="font-weight:500;color:#e11d48;">{{ number_format($exp->amount) }}</span>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    @if($s->ownerWithdrawals->isNotEmpty())
                                                        <div style="margin-top:10px;">
                                                            <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:6px;">Withdrawals</div>
                                                            @foreach($s->ownerWithdrawals as $w)
                                                            <div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-top:1px solid #e2e8f0;">
                                                                <span style="color:#475569;">{{ $w->reason }}</span>
                                                                <span style="font-weight:500;color:#d97706;">{{ number_format($w->amount) }}</span>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid #e2e8f0;background:#f8fafc;">
                        <td colspan="2" style="padding:10px 14px;font-size:12px;font-weight:600;color:#475569;">Totals</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:#64748b;">{{ number_format($totalOpeningBalance) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:#0f766e;">{{ number_format($totalRevenue) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:#0891b2;">{{ number_format($totalRepayments) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:#d97706;">{{ number_format($totalRefunds) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:#e11d48;">{{ number_format($totalExpenses) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:#7c3aed;">{{ number_format($totalWithdrawals) }}</td>
                        <td style="padding:10px 14px;text-align:right;font-size:13px;font-weight:700;color:#0284c7;">{{ number_format($totalBanked) }}</td>
                        <td style="padding:10px 14px;text-align:right;">
                            <div style="font-size:13px;font-weight:700;color:{{ $totalVariance < 0 ? '#e11d48' : ($totalVariance > 0 ? '#d97706' : '#94a3b8') }};">
                                {{ $totalVariance >= 0 ? '+' : '' }}{{ number_format($totalVariance) }}
                            </div>
                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                                avg/day {{ $avgDailyVariance >= 0 ? '+' : '' }}{{ number_format($avgDailyVariance) }}
                            </div>
                        </td>
                        <td style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:#475569;">{{ $closedCount }}/{{ $sessionCount }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>
