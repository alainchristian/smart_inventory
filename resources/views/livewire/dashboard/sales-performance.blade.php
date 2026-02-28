<div class="card" style="animation:fadeUp .4s ease .45s both">
    {{-- Card Header with Period Tabs --}}
    <div class="card-header">
        <div>
            <div class="card-title">Sales Performance</div>
            <div class="card-subtitle">Transaction volume over time</div>
        </div>
        {{-- Period Tabs --}}
        <div class="period-tabs">
            @foreach(['today' => 'Today', 'week' => 'Week', 'month' => 'Month'] as $key => $lbl)
            <button class="period-tab {{ $chartPeriod === $key ? 'active' : '' }}"
                    wire:click="setChartPeriod('{{ $key }}')">{{ $lbl }}</button>
            @endforeach
        </div>
    </div>

    {{-- Chart Canvas --}}
    <div style="padding:0 16px 16px 16px">
        <canvas id="salesChart" style="max-height:280px"></canvas>
    </div>

    {{-- Period Summary Row --}}
    @php $summaries = $this->getPeriodSummaries(); @endphp
    <div class="sp-period-row">
        @foreach([0 => ['today','Today'], 1 => ['week','This Week'], 2 => ['month','This Month']] as $idx => [$key, $lbl])
        <div class="sp-period-col {{ $activePeriodCol === $idx ? 'active-period' : '' }}"
             wire:click="setActivePeriodCol({{ $idx }})">
            <div class="sp-period-name">{{ $lbl }}</div>
            <div class="sp-period-val {{ $summaries[$key] > 0 ? 'blue' : 'ok' }}">
                {{ $summaries[$key] > 0 ? number_format($summaries[$key]) : '0' }}
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let salesChartInstance = null;

function initSalesChart() {
    const canvas = document.getElementById('salesChart');
    if (!canvas) {
        console.log('Sales chart canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');

    // Destroy existing chart if it exists
    if (salesChartInstance) {
        salesChartInstance.destroy();
    }

    const chartData = @json($chartData);

    salesChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Transactions',
                    data: chartData.fullBoxData,
                    backgroundColor: '#3b6fd4',
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Items Sold',
                    data: chartData.itemsData,
                    backgroundColor: '#0e9e86',
                    borderRadius: 6,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2.5,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: '#6b7494',
                        font: {
                            size: 11,
                            weight: '600',
                            family: 'DM Sans'
                        },
                        padding: 12,
                        boxWidth: 12,
                        boxHeight: 12,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#1a1f36',
                    bodyColor: '#6b7494',
                    borderColor: '#e2e6f3',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    boxPadding: 6,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#a8aec8',
                        font: {
                            size: 11,
                            family: 'DM Sans'
                        }
                    },
                    border: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e2e6f3',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#a8aec8',
                        font: {
                            size: 11,
                            family: 'DM Sans'
                        },
                        padding: 8
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });
}

// Initialize chart immediately when script loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSalesChart);
} else {
    // DOM is already ready, initialize immediately
    initSalesChart();
}

// Also initialize on Livewire navigation
document.addEventListener('livewire:navigated', initSalesChart);

// Reinitialize chart when component updates
document.addEventListener('livewire:update', function () {
    setTimeout(() => initSalesChart(), 100);
});
</script>
@endpush
