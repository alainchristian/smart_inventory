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
        $totalCogs           = $rows_col->sum('total_cogs');
        $netProfit           = $totalRevenue - $totalCogs - $totalExpenses;
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

    {{-- ── Table (all screen sizes — horizontal scroll on mobile) ── --}}
    <div class="rounded-xl overflow-hidden" style="border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
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
                        <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:600;color:#0f766e;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">Net Operating</th>
                        <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:600;color:#6d28d9;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">Net Profit</th>
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
                            $rowNetOp   = $row['revenue'] - $row['refunds'] - $row['expenses'] - $row['withdrawals'];
                            $rowNetPr   = $row['revenue'] - ($row['total_cogs'] ?? 0) - $row['expenses'];
                        @endphp
                        <tr style="border-bottom:1px solid #e2e8f0;background:#fff;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                            <td style="padding:10px 14px;white-space:nowrap;">
                                <div style="font-size:12px;font-weight:600;color:#0f172a;">{{ \Carbon\Carbon::parse($row['session_date'])->format('d M Y') }}</div>
                                <div style="font-size:11px;color:#94a3b8;">{{ \Carbon\Carbon::parse($row['session_date'])->format('D') }}</div>
                            </td>
                            <td style="padding:10px 14px;font-size:12px;font-weight:500;color:#0f172a;white-space:nowrap;">{{ $row['shop_name'] }}</td>
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
                            <td style="padding:10px 14px;text-align:right;font-size:12px;font-weight:700;color:{{ $rowNetOp >= 0 ? '#0284c7' : '#e11d48' }};">
                                {{ number_format($rowNetOp) }}
                            </td>
                            <td style="padding:10px 14px;text-align:right;font-size:12px;font-weight:700;color:{{ $rowNetPr >= 0 ? '#0f766e' : '#e11d48' }};">
                                {{ number_format($rowNetPr) }}
                            </td>
                            <td style="padding:10px 14px;text-align:center;font-size:12px;">
                                <span style="color:{{ $row['closed_count'] >= $row['session_count'] ? '#0f766e' : '#d97706' }};">{{ $row['closed_count'] }}</span><span style="color:#94a3b8;">/{{ $row['session_count'] }}</span>
                            </td>
                            <td style="padding:10px 14px;text-align:right;">
                                <button wire:click="toggleRow('{{ $row['session_date'] }}', {{ $row['shop_id'] }})"
                                        style="font-size:12px;padding:4px 10px;border-radius:6px;color:#0f766e;border:1px solid #ccfbf1;background:#ccfbf1;cursor:pointer;white-space:nowrap;">
                                    Details
                                </button>
                            </td>
                        </tr>

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
                        <td style="padding:10px 14px;text-align:right;">
                            <div style="font-size:13px;font-weight:700;color:{{ $netOperating >= 0 ? '#0284c7' : '#e11d48' }};">{{ number_format($netOperating) }}</div>
                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">rev − ref − exp − w/d</div>
                        </td>
                        <td style="padding:10px 14px;text-align:right;">
                            <div style="font-size:13px;font-weight:700;color:{{ $netProfit >= 0 ? '#0f766e' : '#e11d48' }};">{{ number_format($netProfit) }}</div>
                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">rev − cogs − exp</div>
                        </td>
                        <td style="padding:10px 14px;text-align:center;font-size:12px;font-weight:600;color:#475569;">{{ $closedCount }}/{{ $sessionCount }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
    @endif

    {{-- ── Session Details Modal ── --}}
    @if ($expandedKey && $expandedSessions->isNotEmpty())
        @php $sessCount = $expandedSessions->count(); @endphp

        <style>
            .fo-backdrop { position:fixed;inset:0;background:rgba(15,23,42,0.45);z-index:50;backdrop-filter:blur(2px); }
            .fo-modal-wrap { position:fixed;inset:0;z-index:51;display:flex;align-items:center;justify-content:center;padding:16px; }
            .fo-modal { background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.2);width:100%;max-width:760px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden; }
            .fo-modal-header { display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0;background:#f8fafc;flex-shrink:0; }
            .fo-modal-drag { display:none; }
            .fo-modal-body { overflow-y:auto;flex:1; }
            .fo-modal-session { padding:16px 20px; }
            .fo-kpi-strip { display:flex;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:14px;background:#f8fafc; }
            .fo-kpi-cell { flex:1;padding:10px 8px;text-align:center; }
            .fo-breakdown { display:flex;gap:16px;flex-wrap:wrap; }
            .fo-breakdown-col { flex:1;min-width:160px; }
            @media (max-width:640px) {
                .fo-modal-wrap { align-items:flex-end;padding:0; }
                .fo-modal { border-radius:20px 20px 0 0;max-height:85vh;max-width:100%; }
                .fo-modal-drag { display:flex;justify-content:center;padding:10px 0 4px;flex-shrink:0; }
                .fo-modal-header { padding:4px 16px 12px; }
                .fo-modal-session { padding:12px 16px; }
                .fo-kpi-strip { overflow-x:auto;-webkit-overflow-scrolling:touch;flex-wrap:nowrap;border-radius:8px; }
                .fo-kpi-cell { flex-shrink:0;min-width:72px; }
                .fo-breakdown { flex-direction:column;gap:12px; }
                .fo-breakdown-col { min-width:0 !important; }
            }
        </style>

        {{-- Backdrop --}}
        <div class="fo-backdrop" wire:click="closeExpanded"></div>

        {{-- Modal --}}
        <div class="fo-modal-wrap">
            <div class="fo-modal">

                {{-- Drag handle (mobile only) --}}
                <div class="fo-modal-drag">
                    <div style="width:36px;height:4px;border-radius:2px;background:#cbd5e1;"></div>
                </div>

                {{-- Header --}}
                <div class="fo-modal-header">
                    <div>
                        <div style="font-size:15px;font-weight:700;color:#0f172a;">
                            {{ \Carbon\Carbon::parse($expandedSessions->first()->session_date)->format('d M Y') }}
                            &nbsp;·&nbsp;
                            {{ $expandedSessions->first()->shop->name ?? '' }}
                        </div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">
                            {{ $sessCount }} session{{ $sessCount !== 1 ? 's' : '' }}
                        </div>
                    </div>
                    <button wire:click="closeExpanded"
                            style="width:32px;height:32px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-size:18px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;"
                            onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                        &times;
                    </button>
                </div>

                {{-- Body --}}
                <div class="fo-modal-body">
                    @foreach ($expandedSessions as $sIdx => $s)
                        @php
                            $sv      = $s->cash_variance ?? 0;
                            $sNetOp  = ($s->total_sales ?? 0) - ($s->total_refunds_cash ?? 0) - ($s->total_expenses ?? 0) - ($s->total_withdrawals ?? 0);
                            $sCogs   = \Illuminate\Support\Facades\DB::table('sale_items as si')
                                ->join('sales as s2', 'si.sale_id', '=', 's2.id')
                                ->join('products as p', 'si.product_id', '=', 'p.id')
                                ->where('s2.shop_id', $s->shop_id)
                                ->whereRaw('s2.sale_date::date = ?', [$s->session_date])
                                ->whereNull('s2.voided_at')
                                ->whereNull('s2.deleted_at')
                                ->sum(\Illuminate\Support\Facades\DB::raw('p.purchase_price * si.quantity_sold'));
                            $sNetPr  = ($s->total_sales ?? 0) - $sCogs - ($s->total_expenses ?? 0);
                            $isLast  = $sIdx === $sessCount - 1;
                        @endphp
                        <div class="fo-modal-session" style="{{ $isLast ? '' : 'border-bottom:1px solid #e2e8f0;' }}">

                            {{-- Session header --}}
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap;">
                                <span style="font-size:14px;font-weight:700;font-family:monospace;color:#0f172a;">
                                    {{ $s->opened_at?->format('H:i') }} – {{ $s->closed_at?->format('H:i') ?? 'open' }}
                                </span>
                                <span style="font-size:11px;padding:2px 9px;border-radius:20px;font-weight:600;
                                    {{ $s->isLocked() ? 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;' : ($s->isClosed() ? 'background:#fef3c7;color:#92400e;' : 'background:#d1fae5;color:#065f46;') }}">
                                    {{ ucfirst($s->status) }}
                                </span>
                                <span style="font-size:11px;color:#64748b;">
                                    <strong style="color:#334155;">{{ $s->openedBy->name ?? '—' }}</strong>
                                    @if($s->closedBy && $s->closedBy->id !== $s->openedBy?->id)
                                        <span style="color:#cbd5e1;"> → </span><strong style="color:#334155;">{{ $s->closedBy->name }}</strong>
                                    @endif
                                </span>
                            </div>

                            {{-- KPI strip --}}
                            <div class="fo-kpi-strip">
                                @php
                                    $kpis = [
                                        ['Opening',    $s->opening_balance ?? 0, '#64748b'],
                                        ['Revenue',    $s->total_sales ?? 0,      '#0f766e'],
                                        ['Expenses',   $s->total_expenses ?? 0,   '#e11d48'],
                                        ['Net Op.',    $sNetOp,                   $sNetOp >= 0 ? '#0284c7' : '#e11d48'],
                                        ['Net Profit', $sNetPr,                   $sNetPr >= 0 ? '#6d28d9' : '#e11d48'],
                                        ['Variance',   $sv,                       $sv < 0 ? '#e11d48' : ($sv > 0 ? '#d97706' : '#94a3b8')],
                                    ];
                                @endphp
                                @foreach($kpis as $kIdx => $kpi)
                                @php [$kLabel, $kVal, $kClr] = $kpi; @endphp
                                <div class="fo-kpi-cell" style="{{ $kIdx < count($kpis) - 1 ? 'border-right:1px solid #e2e8f0;' : '' }}">
                                    <div style="font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:#94a3b8;margin-bottom:4px;white-space:nowrap;">{{ $kLabel }}</div>
                                    <div style="font-size:13px;font-weight:700;font-family:monospace;color:{{ $kClr }};white-space:nowrap;">
                                        @if($kLabel === 'Variance') {{ $sv >= 0 ? '+' : '' }} @endif{{ number_format($kVal) }}
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            {{-- Breakdown columns --}}
                            <div class="fo-breakdown">

                                {{-- Cash Flow --}}
                                <div class="fo-breakdown-col">
                                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:8px;">Cash Flow</div>
                                    @php
                                        $cfRows = [
                                            ['+', 'Opening balance', $s->opening_balance ?? 0,     '#475569', false],
                                            ['+', 'Cash sales',      $s->total_sales ?? 0,         '#0f766e', false],
                                            ['+', 'Repayments',      $s->total_repayments ?? 0,    '#0891b2', ($s->total_repayments ?? 0) === 0],
                                            ['−', 'Cash refunds',    $s->total_refunds_cash ?? 0,  '#d97706', ($s->total_refunds_cash ?? 0) === 0],
                                            ['−', 'Expenses',        $s->total_expenses ?? 0,      '#e11d48', false],
                                            ['−', 'Withdrawals',     $s->total_withdrawals ?? 0,   '#7c3aed', ($s->total_withdrawals ?? 0) === 0],
                                            ['−', 'Bank deposits',   $s->total_bank_deposits ?? 0, '#0284c7', ($s->total_bank_deposits ?? 0) === 0],
                                        ];
                                    @endphp
                                    @foreach($cfRows as $cfRow)
                                    @php [$cfSign, $cfLbl, $cfVal, $cfClr, $cfSkip] = $cfRow; @endphp
                                    @if(!$cfSkip)
                                    <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 0;border-bottom:1px dashed #f1f5f9;gap:8px;">
                                        <div style="display:flex;align-items:center;gap:5px;">
                                            <span style="font-size:10px;font-weight:700;width:10px;flex-shrink:0;color:{{ $cfSign === '+' ? '#0f766e' : '#e11d48' }};">{{ $cfSign }}</span>
                                            <span style="font-size:11px;color:#475569;white-space:nowrap;">{{ $cfLbl }}</span>
                                        </div>
                                        <span style="font-size:12px;font-weight:600;font-family:monospace;white-space:nowrap;color:{{ $cfClr }};">{{ number_format($cfVal) }}</span>
                                    </div>
                                    @endif
                                    @endforeach
                                    @if($sv !== 0)
                                    <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0 2px;border-top:2px solid #e2e8f0;margin-top:3px;gap:8px;">
                                        <span style="font-size:11px;font-weight:700;color:#0f172a;">Cash variance</span>
                                        <span style="font-size:12px;font-weight:700;font-family:monospace;color:{{ $sv < 0 ? '#e11d48' : '#d97706' }};">{{ $sv >= 0 ? '+' : '' }}{{ number_format($sv) }}</span>
                                    </div>
                                    @endif
                                </div>

                                {{-- Expense Breakdown --}}
                                @if($s->expenses->whereNull('deleted_at')->isNotEmpty())
                                <div class="fo-breakdown-col">
                                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:8px;">Expense Breakdown</div>
                                    @foreach($s->expenses->whereNull('deleted_at') as $exp)
                                    <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 0;border-bottom:1px dashed #f1f5f9;gap:8px;">
                                        <div style="display:flex;align-items:center;gap:5px;min-width:0;">
                                            @if($exp->is_system_generated)
                                                <span style="font-size:9px;padding:1px 5px;border-radius:3px;background:#fef3c7;color:#92400e;flex-shrink:0;white-space:nowrap;">auto</span>
                                            @else
                                                <span style="width:5px;height:5px;border-radius:50%;background:#e11d48;flex-shrink:0;"></span>
                                            @endif
                                            <span style="font-size:11px;color:#475569;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $exp->category->name ?? '—' }}</span>
                                        </div>
                                        <span style="font-size:12px;font-weight:600;font-family:monospace;white-space:nowrap;color:#e11d48;">{{ number_format($exp->amount) }}</span>
                                    </div>
                                    @endforeach
                                    <div style="display:flex;justify-content:space-between;padding:5px 0 2px;border-top:2px solid #e2e8f0;margin-top:3px;">
                                        <span style="font-size:11px;font-weight:700;color:#0f172a;">Total</span>
                                        <span style="font-size:12px;font-weight:700;font-family:monospace;color:#e11d48;">{{ number_format($s->total_expenses ?? 0) }}</span>
                                    </div>
                                </div>
                                @endif

                                {{-- Withdrawals --}}
                                @if($s->ownerWithdrawals->whereNull('deleted_at')->isNotEmpty())
                                <div class="fo-breakdown-col">
                                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;margin-bottom:8px;">Withdrawals</div>
                                    @foreach($s->ownerWithdrawals->whereNull('deleted_at') as $w)
                                    <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 0;border-bottom:1px dashed #f1f5f9;gap:8px;">
                                        <div style="display:flex;align-items:center;gap:5px;min-width:0;">
                                            <span style="font-size:9px;padding:1px 5px;border-radius:3px;background:#ede9fe;color:#5b21b6;flex-shrink:0;white-space:nowrap;">{{ $w->isCash() ? 'cash' : 'momo' }}</span>
                                            <span style="font-size:11px;color:#475569;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $w->reason }}</span>
                                        </div>
                                        <span style="font-size:12px;font-weight:600;font-family:monospace;white-space:nowrap;color:#7c3aed;">{{ number_format($w->amount) }}</span>
                                    </div>
                                    @endforeach
                                    <div style="display:flex;justify-content:space-between;padding:5px 0 2px;border-top:2px solid #e2e8f0;margin-top:3px;">
                                        <span style="font-size:11px;font-weight:700;color:#0f172a;">Total</span>
                                        <span style="font-size:12px;font-weight:700;font-family:monospace;color:#7c3aed;">{{ number_format($s->total_withdrawals ?? 0) }}</span>
                                    </div>
                                </div>
                                @endif

                            </div>{{-- /breakdown --}}
                        </div>{{-- /session --}}
                    @endforeach
                </div>{{-- /body --}}

            </div>{{-- /modal --}}
        </div>{{-- /wrap --}}
    @endif

</div>{{-- /livewire root --}}
