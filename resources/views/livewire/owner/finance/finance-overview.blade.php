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
                        ? 'background:var(--accent);color:white;'
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
        $totalRevenue     = collect($rows)->sum('revenue');
        $totalExpenses    = collect($rows)->sum('expenses');
        $totalWithdrawals = collect($rows)->sum('withdrawals');
        $totalBanked      = collect($rows)->sum('cash_banked');
        $totalVariance    = collect($rows)->sum('total_variance');
        $sessionCount     = collect($rows)->sum('session_count');
        $closedCount      = collect($rows)->sum('closed_count');
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-xs mb-1" style="color:var(--text-dim);">Revenue</div>
            <div class="font-mono font-bold text-sm" style="color:var(--green);">{{ number_format($totalRevenue) }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-dim);">RWF</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-xs mb-1" style="color:var(--text-dim);">Expenses</div>
            <div class="font-mono font-bold text-sm" style="color:var(--red);">{{ number_format($totalExpenses) }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-dim);">RWF</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-xs mb-1" style="color:var(--text-dim);">Withdrawals</div>
            <div class="font-mono font-bold text-sm" style="color:var(--accent);">{{ number_format($totalWithdrawals) }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-dim);">RWF</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-xs mb-1" style="color:var(--text-dim);">Banked</div>
            <div class="font-mono font-bold text-sm" style="color:var(--accent);">{{ number_format($totalBanked) }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-dim);">RWF</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-xs mb-1" style="color:var(--text-dim);">Variance</div>
            <div class="font-mono font-bold text-sm"
                 style="{{ $totalVariance < 0 ? 'color:var(--red)' : ($totalVariance > 0 ? 'color:var(--amber)' : 'color:var(--text-dim)') }}">
                {{ $totalVariance >= 0 ? '+' : '' }}{{ number_format($totalVariance) }}
            </div>
            <div class="text-xs mt-0.5" style="color:var(--text-dim);">RWF</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-xs mb-1" style="color:var(--text-dim);">Sessions</div>
            <div class="font-mono font-bold text-sm" style="color:var(--text);">{{ $sessionCount }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-dim);">total</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-xs mb-1" style="color:var(--text-dim);">Closed</div>
            <div class="font-mono font-bold text-sm"
                 style="{{ $closedCount >= $sessionCount && $sessionCount > 0 ? 'color:var(--green)' : 'color:var(--amber)' }}">
                {{ $closedCount }}<span class="font-normal text-xs" style="color:var(--text-dim);">/{{ $sessionCount }}</span>
            </div>
            <div class="text-xs mt-0.5" style="color:var(--text-dim);">sessions</div>
        </div>
    </div>

    {{-- ── Chart ── --}}
    @if (!empty($chartData['labels']))
        <div class="rounded-xl p-5 mb-6" style="background:var(--surface-raised);border:1px solid var(--border);">
            <div class="text-sm font-semibold mb-4" style="color:var(--text);">Revenue vs Expenses</div>
            <div style="position:relative;height:200px;">
                <script id="finance-chart-data" type="application/json">@json($chartData)</script>
                <canvas id="finance-overview-chart"></canvas>
            </div>
        </div>

        @script
        <script>
            let chart = null;
            function getChartData() {
                const el = document.getElementById('finance-chart-data');
                return el ? JSON.parse(el.textContent) : { labels: [], revenue: [], expenses: [] };
            }
            function drawChart() {
                const canvas = document.getElementById('finance-overview-chart');
                if (!canvas) return;
                const data = getChartData();
                if (chart) {
                    chart.data.labels = data.labels;
                    chart.data.datasets[0].data = data.revenue;
                    chart.data.datasets[1].data = data.expenses;
                    chart.update();
                    return;
                }
                chart = new Chart(canvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Revenue',  data: data.revenue,  backgroundColor: 'rgba(34,197,94,0.25)',  borderColor: 'rgb(34,197,94)',  borderWidth: 1.5 },
                            { label: 'Expenses', data: data.expenses, backgroundColor: 'rgba(239,68,68,0.25)', borderColor: 'rgb(239,68,68)', borderWidth: 1.5 }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: '#888', font: { size: 11 } } } },
                        scales: {
                            x: { ticks: { color: '#888', font: { size: 10 } }, grid: { color: 'rgba(128,128,128,0.1)' } },
                            y: { ticks: { color: '#888', font: { size: 10 } }, grid: { color: 'rgba(128,128,128,0.1)' } }
                        }
                    }
                });
            }
            drawChart();
            Livewire.hook('commit', ({ succeed }) => { succeed(() => { setTimeout(drawChart, 0); }); });
        </script>
        @endscript
    @endif

    {{-- ── No data ── --}}
    @if (empty($rows))
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
            <div class="rounded-xl overflow-hidden" style="background:var(--surface-raised);border:1px solid var(--border);">
                <div class="px-4 pt-4 pb-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-semibold" style="color:var(--text);">{{ $row['shop_name'] }}</div>
                            <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                                {{ \Carbon\Carbon::parse($row['session_date'])->format('d M Y · D') }}
                            </div>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded flex-shrink-0"
                              style="{{ $row['closed_count'] >= $row['session_count'] ? 'background:var(--green-dim);color:var(--green)' : 'background:var(--amber-dim);color:var(--amber)' }}">
                            {{ $row['closed_count'] }}/{{ $row['session_count'] }} closed
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-3" style="border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
                    <div class="px-3 py-2.5 text-center" style="border-right:1px solid var(--border);">
                        <div class="text-xs mb-0.5" style="color:var(--text-dim);">Revenue</div>
                        <div class="font-mono font-semibold text-sm" style="color:var(--green);">{{ number_format($row['revenue']) }}</div>
                    </div>
                    <div class="px-3 py-2.5 text-center" style="border-right:1px solid var(--border);">
                        <div class="text-xs mb-0.5" style="color:var(--text-dim);">Expenses</div>
                        <div class="font-mono font-semibold text-sm" style="color:var(--red);">{{ number_format($row['expenses']) }}</div>
                    </div>
                    <div class="px-3 py-2.5 text-center">
                        <div class="text-xs mb-0.5" style="color:var(--text-dim);">Variance</div>
                        <div class="font-mono font-semibold text-sm"
                             style="{{ $rv < 0 ? 'color:var(--red)' : ($rv > 0 ? 'color:var(--amber)' : 'color:var(--text-dim)') }}">
                            {{ $rv >= 0 ? '+' : '' }}{{ number_format($rv) }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between px-4 py-2.5">
                    <div class="text-xs" style="color:var(--text-dim);">
                        Banked: <span class="font-mono" style="color:var(--accent);">{{ number_format($row['cash_banked']) }}</span>
                        @if($row['withdrawals'] > 0)
                            · W/D: <span class="font-mono" style="color:var(--accent);">{{ number_format($row['withdrawals']) }}</span>
                        @endif
                    </div>
                    <button wire:click="toggleRow('{{ $row['session_date'] }}', {{ $row['shop_id'] }})"
                            class="text-xs px-2.5 py-1.5 rounded-lg font-medium"
                            style="color:var(--accent);background:var(--accent-dim);">
                        {{ $isExpanded ? 'Hide ▲' : 'Details ▾' }}
                    </button>
                </div>

                @if ($isExpanded && $expandedSessions->isNotEmpty())
                    <div class="px-4 pb-4 space-y-3" style="border-top:1px solid var(--border);background:var(--surface);">
                        <div class="pt-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--text-dim);">Sessions</div>
                        @foreach ($expandedSessions as $s)
                            @php $sv = $s->cash_variance ?? 0; @endphp
                            <div class="rounded-lg p-3" style="background:var(--surface-raised);border:1px solid var(--border);">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-xs font-medium" style="color:var(--text);">
                                        {{ $s->opened_at?->format('H:i') }} – {{ $s->closed_at?->format('H:i') ?? '—' }}
                                        <span style="color:var(--text-dim);">· {{ $s->openedBy->name ?? '—' }}</span>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded"
                                          style="{{ $s->isLocked() ? 'color:var(--text-dim);border:1px solid var(--border)' : 'background:var(--green-dim);color:var(--green)' }}">
                                        {{ ucfirst($s->status) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div><span style="color:var(--text-dim);">Sales</span> <span class="font-mono" style="color:var(--green);">{{ number_format($s->total_sales ?? 0) }}</span></div>
                                    <div><span style="color:var(--text-dim);">Expenses</span> <span class="font-mono" style="color:var(--red);">{{ number_format($s->total_expenses ?? 0) }}</span></div>
                                    <div><span style="color:var(--text-dim);">Banked</span> <span class="font-mono" style="color:var(--accent);">{{ number_format($s->total_bank_deposits ?? 0) }}</span></div>
                                    <div><span style="color:var(--text-dim);">Variance</span>
                                        <span class="font-mono" style="{{ $sv < 0 ? 'color:var(--red)' : ($sv > 0 ? 'color:var(--amber)' : 'color:var(--text-dim)') }}">
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
    <div class="hidden sm:block rounded-xl overflow-hidden" style="border:1px solid var(--border);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--surface-raised);border-bottom:1px solid var(--border);">
                        <th class="text-left px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Date</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Shop</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Revenue</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Expenses</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Withdrawals</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Banked</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Variance</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Sessions</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @php
                            $rowKey     = $row['session_date'] . ':' . $row['shop_id'];
                            $isExpanded = $expandedKey === $rowKey;
                            $rv         = $row['total_variance'];
                        @endphp
                        <tr style="border-bottom:1px solid var(--border);" class="hover:bg-[var(--surface)]">
                            <td class="px-4 py-3">
                                <div class="text-xs font-mono font-medium" style="color:var(--text);">
                                    {{ \Carbon\Carbon::parse($row['session_date'])->format('d M Y') }}
                                </div>
                                <div class="text-xs" style="color:var(--text-dim);">
                                    {{ \Carbon\Carbon::parse($row['session_date'])->format('D') }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs font-medium" style="color:var(--text);">{{ $row['shop_name'] }}</td>
                            <td class="px-4 py-3 text-right font-mono text-xs font-semibold" style="color:var(--green);">{{ number_format($row['revenue']) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-xs" style="color:var(--red);">{{ number_format($row['expenses']) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-xs" style="color:var(--accent);">{{ number_format($row['withdrawals']) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-xs" style="color:var(--accent);">{{ number_format($row['cash_banked']) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-xs font-semibold"
                                style="{{ $rv < 0 ? 'color:var(--red)' : ($rv > 0 ? 'color:var(--amber)' : 'color:var(--text-dim)') }}">
                                {{ $rv >= 0 ? '+' : '' }}{{ number_format($rv) }}
                            </td>
                            <td class="px-4 py-3 text-center text-xs">
                                <span style="color:{{ $row['closed_count'] >= $row['session_count'] ? 'var(--green)' : 'var(--amber)' }};">
                                    {{ $row['closed_count'] }}
                                </span><span style="color:var(--text-dim);">/{{ $row['session_count'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="toggleRow('{{ $row['session_date'] }}', {{ $row['shop_id'] }})"
                                        class="text-xs px-2 py-1 rounded"
                                        style="color:var(--accent);border:1px solid var(--accent-dim);">
                                    {{ $isExpanded ? 'Hide ▲' : 'Details ▾' }}
                                </button>
                            </td>
                        </tr>

                        {{-- Expanded drill-down row --}}
                        @if ($isExpanded)
                            <tr style="background:var(--surface);">
                                <td colspan="9" class="px-5 py-4">
                                    @if ($expandedSessions->isEmpty())
                                        <div class="text-xs" style="color:var(--text-dim);">No sessions found.</div>
                                    @else
                                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                            @foreach ($expandedSessions as $s)
                                                @php $sv = $s->cash_variance ?? 0; @endphp
                                                <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
                                                    {{-- Session header --}}
                                                    <div class="flex items-start justify-between gap-2 mb-3">
                                                        <div>
                                                            <div class="text-xs font-semibold" style="color:var(--text);">
                                                                {{ $s->opened_at?->format('H:i') }} – {{ $s->closed_at?->format('H:i') ?? 'open' }}
                                                            </div>
                                                            <div class="text-xs mt-0.5" style="color:var(--text-dim);">
                                                                {{ $s->openedBy->name ?? '—' }}
                                                                @if($s->closedBy && $s->closedBy->id !== $s->openedBy?->id)
                                                                    → {{ $s->closedBy->name }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <span class="text-xs px-2 py-0.5 rounded flex-shrink-0"
                                                              style="{{ $s->isLocked()
                                                                  ? 'background:var(--surface);color:var(--text-dim);border:1px solid var(--border)'
                                                                  : ($s->isClosed() ? 'background:var(--amber-dim);color:var(--amber)' : 'background:var(--green-dim);color:var(--green)') }}">
                                                            {{ ucfirst($s->status) }}
                                                        </span>
                                                    </div>

                                                    {{-- Metrics --}}
                                                    <div class="space-y-1.5 text-xs mb-3">
                                                        <div class="flex justify-between">
                                                            <span style="color:var(--text-dim);">Revenue</span>
                                                            <span class="font-mono font-semibold" style="color:var(--green);">{{ number_format($s->total_sales ?? 0) }}</span>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <span style="color:var(--text-dim);">Expenses</span>
                                                            <span class="font-mono" style="color:var(--red);">{{ number_format($s->total_expenses ?? 0) }}</span>
                                                        </div>
                                                        @if($s->total_withdrawals)
                                                        <div class="flex justify-between">
                                                            <span style="color:var(--text-dim);">Withdrawals</span>
                                                            <span class="font-mono" style="color:var(--accent);">{{ number_format($s->total_withdrawals) }}</span>
                                                        </div>
                                                        @endif
                                                        <div class="flex justify-between">
                                                            <span style="color:var(--text-dim);">Bank deposits</span>
                                                            <span class="font-mono" style="color:var(--accent);">{{ number_format($s->total_bank_deposits ?? 0) }}</span>
                                                        </div>
                                                        <div class="flex justify-between pt-1.5" style="border-top:1px solid var(--border);">
                                                            <span style="color:var(--text);">Variance</span>
                                                            <span class="font-mono font-semibold"
                                                                  style="{{ $sv < 0 ? 'color:var(--red)' : ($sv > 0 ? 'color:var(--amber)' : 'color:var(--text-dim)') }}">
                                                                {{ $sv >= 0 ? '+' : '' }}{{ number_format($sv) }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    {{-- Expenses list --}}
                                                    @if($s->expenses->isNotEmpty())
                                                        <div class="text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color:var(--text-dim);">Expenses</div>
                                                        @foreach($s->expenses as $exp)
                                                            <div class="flex justify-between text-xs py-1" style="border-top:1px solid var(--border);">
                                                                <span style="color:var(--text-dim);">{{ $exp->category->name ?? '—' }}</span>
                                                                <span class="font-mono" style="color:var(--red);">{{ number_format($exp->amount) }}</span>
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    {{-- Withdrawals list --}}
                                                    @if($s->ownerWithdrawals->isNotEmpty())
                                                        <div class="text-xs font-semibold mb-1.5 mt-2 uppercase tracking-wide" style="color:var(--text-dim);">Withdrawals</div>
                                                        @foreach($s->ownerWithdrawals as $w)
                                                            <div class="flex justify-between text-xs py-1" style="border-top:1px solid var(--border);">
                                                                <span style="color:var(--text-dim);">{{ $w->reason }}</span>
                                                                <span class="font-mono" style="color:var(--accent);">{{ number_format($w->amount) }}</span>
                                                            </div>
                                                        @endforeach
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
                    <tr style="border-top:2px solid var(--border);background:var(--surface-raised);">
                        <td colspan="2" class="px-4 py-3 text-xs font-semibold" style="color:var(--text-dim);">Totals</td>
                        <td class="px-4 py-3 text-right font-mono text-sm font-bold" style="color:var(--green);">{{ number_format($totalRevenue) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-sm font-bold" style="color:var(--red);">{{ number_format($totalExpenses) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-sm font-bold" style="color:var(--accent);">{{ number_format($totalWithdrawals) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-sm font-bold" style="color:var(--accent);">{{ number_format($totalBanked) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-sm font-bold"
                            style="{{ $totalVariance < 0 ? 'color:var(--red)' : ($totalVariance > 0 ? 'color:var(--amber)' : 'color:var(--text-dim)') }}">
                            {{ $totalVariance >= 0 ? '+' : '' }}{{ number_format($totalVariance) }}
                        </td>
                        <td class="px-4 py-3 text-center text-xs font-semibold" style="color:var(--text-dim);">{{ $closedCount }}/{{ $sessionCount }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>
