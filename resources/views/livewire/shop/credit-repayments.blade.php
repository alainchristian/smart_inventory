<div style="font-family:var(--font)">
@if ($sessionBlocked)
    <x-session-gate-blocked
        :reason="$sessionBlockReason"
        :session-date="$blockedSessionDate"
        :session-id="$blockedSessionId"
    />
@else

<style>
.cr-header       { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap }
.cr-header-title { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px }
.cr-header-sub   { font-size:13px;color:var(--text-dim);margin:0 }

.cr-kpis      { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px }
.cr-kpi       { background:var(--surface);border:none;border-radius:var(--r);
                box-shadow:var(--shadow-card);padding:22px 20px;
                display:flex;flex-direction:column;gap:16px;transition:box-shadow var(--tr) }
.cr-kpi:hover { box-shadow:var(--shadow-card-hover) }
.cr-kpi-row   { display:flex;align-items:center;gap:12px }
.cr-kpi-icon  { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.cr-kpi-body  { flex:1;min-width:0 }
.cr-kpi-label { font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);line-height:1.2 }
.cr-kpi-sub   { font-size:12px;color:var(--text-dim);margin-top:2px }
.cr-kpi-val   { font-size:24px;font-weight:800;font-family:var(--mono);letter-spacing:-1px;line-height:1 }
.cr-kpi-divider { height:1px;background:var(--border) }
.cr-kpi-footer  { display:grid;grid-template-columns:repeat(3,1fr) }
.cr-kpi-stat    { display:flex;flex-direction:column;align-items:center;gap:3px;padding:4px 0 }
.cr-kpi-stat-v  { font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text-sub) }
.cr-kpi-stat-l  { font-size:10px;color:var(--text-dim);letter-spacing:.3px;text-align:center }

.cr-bar         { display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px }
.cr-search-wrap { flex:1;min-width:200px;position:relative }
.cr-search-icon { position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-dim);pointer-events:none }
.cr-search      { width:100%;padding:10px 12px 10px 34px;border:1.5px solid var(--border);border-radius:10px;
                  font-size:14px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;
                  font-family:var(--font);transition:border-color var(--tr) }
.cr-search:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }

.cr-table-wrap { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card) }
.cr-scroll     { overflow-x:auto;-webkit-overflow-scrolling:touch }
.cr-table      { width:100%;border-collapse:collapse }
.cr-table thead tr { border-bottom:2px solid var(--border) }
.cr-table thead th { padding:12px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;
                     text-transform:uppercase;color:var(--text-dim);white-space:nowrap }
.cr-table tbody tr { border-bottom:1px solid var(--border);transition:background var(--tr) }
.cr-table tbody tr:last-child { border-bottom:none }
.cr-table tbody tr:hover { background:var(--surface2) }
.cr-table td   { padding:13px 16px;font-size:13px;vertical-align:middle }
.cr-cust-main  { font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px }
.cr-cust-sub   { font-size:12px;color:var(--text-dim);margin-top:2px;font-family:var(--mono) }

.cr-badge     { display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;white-space:nowrap }
.cr-badge-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0 }

.cr-action { padding:6px 14px;border-radius:7px;border:none;background:var(--accent);color:#fff;
             font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font);transition:opacity var(--tr) }
.cr-action:hover { opacity:.88 }

.cr-empty       { padding:60px 20px;text-align:center }
.cr-empty-title { font-size:15px;font-weight:700;color:var(--text-sub);margin-bottom:6px }
.cr-empty-sub   { font-size:13px;color:var(--text-dim) }

@media(max-width:900px) {
    .cr-kpis { grid-template-columns:1fr 1fr;gap:8px }
    .cr-kpi  { padding:14px;gap:10px }
    .cr-kpi-val { font-size:20px }
}
@media(max-width:480px) { .cr-kpis { grid-template-columns:1fr } }

/* Repayment modal */
.cr-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;display:flex;
                     align-items:center;justify-content:center;padding:16px }
.cr-modal      { background:var(--surface);border-radius:var(--r);max-width:560px;width:100%;
                 max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-card-hover) }
.cr-modal-head { padding:20px 22px;border-bottom:1px solid var(--border);display:flex;
                 align-items:center;justify-content:space-between;gap:12px }
.cr-modal-title { font-size:17px;font-weight:800;color:var(--text);margin:0 0 4px }
.cr-modal-sub   { font-size:13px;color:var(--text-dim) }
.cr-modal-close { width:32px;height:32px;border-radius:8px;border:none;background:var(--surface2);
                  color:var(--text-sub);cursor:pointer;display:flex;align-items:center;justify-content:center;
                  flex-shrink:0;transition:background var(--tr) }
.cr-modal-close:hover { background:var(--surface3) }

.cr-modal-stats { padding:18px 22px;border-bottom:1px solid var(--border);display:grid;
                  grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px }
.cr-modal-stat-l { font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;
                    letter-spacing:.5px;margin-bottom:4px }
.cr-modal-stat-v { font-size:20px;font-weight:800;font-family:var(--mono) }

.cr-field     { margin-bottom:18px;padding:0 22px }
.cr-label     { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px }
.cr-label span { color:var(--red) }
.cr-input     { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;
                font-size:14px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;
                font-family:var(--font);transition:border-color var(--tr) }
.cr-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.cr-error { font-size:11px;color:var(--red);margin-top:4px }

.cr-pm-grid  { display:grid;grid-template-columns:repeat(2,1fr);gap:10px }
.cr-pm-opt   { display:flex;align-items:center;padding:11px 12px;border-radius:10px;border:1.5px solid var(--border);
               background:var(--surface);cursor:pointer;transition:all var(--tr) }
.cr-pm-opt.active { border-color:var(--accent);background:var(--accent-dim) }
.cr-pm-opt input { margin-right:9px }
.cr-pm-opt-label { font-size:13px;font-weight:600;color:var(--text-sub) }
.cr-pm-opt.active .cr-pm-opt-label { color:var(--accent) }

.cr-modal-foot { padding:16px 22px;border-top:1px solid var(--border);display:flex;gap:10px }
.cr-cancel-btn { flex:1;padding:12px;background:transparent;border:1.5px solid var(--border);color:var(--text-sub);
                 border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.cr-cancel-btn:hover { border-color:var(--border-hi);color:var(--text) }
.cr-save-btn   { flex:2;padding:12px;background:var(--accent);color:#fff;border:none;border-radius:10px;
                 font-size:14px;font-weight:700;cursor:pointer;font-family:var(--font);
                 box-shadow:0 3px 10px rgba(59,111,212,.25);transition:opacity var(--tr) }
.cr-save-btn:hover { opacity:.88 }
.cr-save-btn:disabled { opacity:.5;cursor:not-allowed }

.cr-history       { padding:18px 22px;border-top:1px solid var(--border) }
.cr-history-title { font-size:13px;font-weight:700;color:var(--text);margin:0 0 12px }
.cr-history-row   { padding:11px 12px;background:var(--surface);box-shadow:var(--shadow-card);
                     border-radius:8px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:flex-start;gap:10px }
.cr-history-amt   { font-size:15px;font-weight:700;color:var(--green);font-family:var(--mono) }
.cr-history-date  { font-size:11px;color:var(--text-dim);margin-top:2px }
.cr-history-ref   { font-size:11px;color:var(--text-dim);margin-top:4px }
.cr-history-pill  { padding:3px 9px;border-radius:6px;background:var(--surface2);font-size:10px;font-weight:700;
                     color:var(--text-dim);text-transform:uppercase;white-space:nowrap;flex-shrink:0 }

@media(max-width:640px) {
    .cr-pm-grid { grid-template-columns:1fr }
    .cr-modal-foot { flex-direction:column }
}

@keyframes cr-spin { to { transform:rotate(360deg) } }
</style>

{{-- Page Header --}}
<div class="cr-header">
    <div>
        <h1 class="cr-header-title">Credit Repayments</h1>
        <p class="cr-header-sub">Record customer credit repayments and track payment history</p>
    </div>
</div>

{{-- KPI Cards --}}
<div class="cr-kpis">
    {{-- Total Outstanding --}}
    <div class="cr-kpi">
        <div class="cr-kpi-row">
            <div class="cr-kpi-icon" style="background:var(--red-dim);color:var(--red)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="6" width="20" height="12" rx="2"/><path stroke-linecap="round" d="M2 10h20M6 15h4"/>
                </svg>
            </div>
            <div class="cr-kpi-body">
                <div class="cr-kpi-label">Total Outstanding</div>
                <div class="cr-kpi-sub">Owed across all customers</div>
            </div>
        </div>
        <div class="cr-kpi-val" style="color:var(--red)">{{ number_format($this->stats['total_outstanding']) }}<span style="font-size:13px;font-weight:500;color:var(--text-dim);margin-left:3px">RWF</span></div>
        <div class="cr-kpi-divider"></div>
        <div class="cr-kpi-footer">
            <div class="cr-kpi-stat">
                <span class="cr-kpi-stat-v">{{ $this->stats['customer_count'] }}</span>
                <span class="cr-kpi-stat-l">Customers</span>
            </div>
            <div class="cr-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="cr-kpi-stat-v">{{ number_format($this->stats['highest_balance']) }}</span>
                <span class="cr-kpi-stat-l">Highest</span>
            </div>
            <div class="cr-kpi-stat">
                <span class="cr-kpi-stat-v">{{ number_format($this->stats['avg_balance']) }}</span>
                <span class="cr-kpi-stat-l">Avg Balance</span>
            </div>
        </div>
    </div>

    {{-- Collected Today --}}
    <div class="cr-kpi">
        <div class="cr-kpi-row">
            <div class="cr-kpi-icon" style="background:var(--green-dim);color:var(--green)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-4-4h8M4 6h16v12H4z"/>
                </svg>
            </div>
            <div class="cr-kpi-body">
                <div class="cr-kpi-label">Collected Today</div>
                <div class="cr-kpi-sub">Repayments recorded today</div>
            </div>
        </div>
        <div class="cr-kpi-val" style="color:var(--green)">{{ number_format($this->stats['collected_today']) }}<span style="font-size:13px;font-weight:500;color:var(--text-dim);margin-left:3px">RWF</span></div>
        <div class="cr-kpi-divider"></div>
        <div class="cr-kpi-footer">
            <div class="cr-kpi-stat">
                <span class="cr-kpi-stat-v">{{ $this->stats['repayments_today_count'] }}</span>
                <span class="cr-kpi-stat-l">Payments</span>
            </div>
            <div class="cr-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="cr-kpi-stat-v">{{ number_format($this->stats['avg_payment_today']) }}</span>
                <span class="cr-kpi-stat-l">Avg Payment</span>
            </div>
            <div class="cr-kpi-stat">
                <span class="cr-kpi-stat-v">{{ $this->stats['customers_paid_today'] }}</span>
                <span class="cr-kpi-stat-l">Customers</span>
            </div>
        </div>
    </div>

    {{-- Repayment Rate --}}
    <div class="cr-kpi">
        <div class="cr-kpi-row">
            <div class="cr-kpi-icon" style="background:var(--accent-dim);color:var(--accent)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7v6h-6"/>
                </svg>
            </div>
            <div class="cr-kpi-body">
                <div class="cr-kpi-label">Repayment Rate</div>
                <div class="cr-kpi-sub">All-time, credit given vs repaid</div>
            </div>
        </div>
        <div class="cr-kpi-val" style="color:var(--accent)">{{ $this->stats['repayment_rate'] }}<span style="font-size:13px;font-weight:500;color:var(--text-dim);margin-left:3px">%</span></div>
        <div class="cr-kpi-divider"></div>
        <div class="cr-kpi-footer">
            <div class="cr-kpi-stat">
                <span class="cr-kpi-stat-v">{{ number_format($this->stats['all_credit_given']) }}</span>
                <span class="cr-kpi-stat-l">Credit Given</span>
            </div>
            <div class="cr-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="cr-kpi-stat-v">{{ number_format($this->stats['all_repaid']) }}</span>
                <span class="cr-kpi-stat-l">Repaid</span>
            </div>
            <div class="cr-kpi-stat">
                <span class="cr-kpi-stat-v">{{ number_format($this->stats['total_written_off']) }}</span>
                <span class="cr-kpi-stat-l">Written Off</span>
            </div>
        </div>
    </div>

    {{-- Overdue --}}
    <div class="cr-kpi">
        <div class="cr-kpi-row">
            <div class="cr-kpi-icon" style="background:var(--amber-dim);color:var(--amber)">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 3"/>
                </svg>
            </div>
            <div class="cr-kpi-body">
                <div class="cr-kpi-label">Overdue Customers</div>
                <div class="cr-kpi-sub">No repayment in {{ $this->stats['overdue_days'] }}+ days</div>
            </div>
        </div>
        <div class="cr-kpi-val" style="color:var(--amber)">{{ $this->stats['overdue_count'] }}</div>
        <div class="cr-kpi-divider"></div>
        <div class="cr-kpi-footer">
            <div class="cr-kpi-stat">
                <span class="cr-kpi-stat-v">{{ $this->stats['customer_count'] }}</span>
                <span class="cr-kpi-stat-l">Total Owing</span>
            </div>
            <div class="cr-kpi-stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border)">
                <span class="cr-kpi-stat-v">{{ $this->stats['customer_count'] > 0 ? round(($this->stats['overdue_count'] / $this->stats['customer_count']) * 100) : 0 }}%</span>
                <span class="cr-kpi-stat-l">Of Total</span>
            </div>
            <div class="cr-kpi-stat">
                <span class="cr-kpi-stat-v">{{ $this->stats['overdue_days'] }}d</span>
                <span class="cr-kpi-stat-l">Threshold</span>
            </div>
        </div>
    </div>
</div>

{{-- Search --}}
<div class="cr-bar">
    <div class="cr-search-wrap">
        <svg class="cr-search-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.3-4.3"/>
        </svg>
        <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search by customer name or phone…" class="cr-search">
    </div>
</div>

{{-- Customers Table --}}
<div class="cr-table-wrap">
    @if($this->customers->count() > 0)
        <div class="cr-scroll">
            <table class="cr-table" style="min-width:900px;table-layout:fixed">
                <colgroup>
                    <col style="width:260px"><col style="width:150px"><col style="width:150px">
                    <col style="width:150px"><col style="width:140px"><col style="width:150px">
                </colgroup>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th style="text-align:right">Outstanding</th>
                        <th style="text-align:right">Credit Given</th>
                        <th style="text-align:right">Repaid</th>
                        <th>Last Payment</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->customers as $customer)
                        @php
                            $cutoff = now()->subDays($this->stats['overdue_days']);
                            $isOverdue = $customer->last_repayment_at
                                ? $customer->last_repayment_at->lt($cutoff)
                                : ($customer->last_credit_at && $customer->last_credit_at->lt($cutoff));
                        @endphp
                        <tr wire:key="cust-{{ $customer->id }}">
                            <td>
                                <div class="cr-cust-main">
                                    {{ $customer->name }}
                                    @if($isOverdue)
                                        <span class="cr-badge" style="background:var(--red-dim);color:var(--red)">
                                            <span class="cr-badge-dot" style="background:var(--red)"></span>Overdue
                                        </span>
                                    @endif
                                </div>
                                <div class="cr-cust-sub">{{ $customer->phone }}</div>
                            </td>
                            <td style="text-align:right;white-space:nowrap">
                                <span style="font-family:var(--mono);font-weight:700;color:var(--red);font-size:14px">{{ number_format($customer->outstanding_balance) }}</span>
                            </td>
                            <td style="text-align:right;white-space:nowrap">
                                <span style="font-family:var(--mono);color:var(--text-sub);font-size:13px">{{ number_format($customer->total_credit_given) }}</span>
                            </td>
                            <td style="text-align:right;white-space:nowrap">
                                <span style="font-family:var(--mono);color:var(--green);font-size:13px;font-weight:600">{{ number_format($customer->total_repaid) }}</span>
                            </td>
                            <td style="color:var(--text-dim);font-size:12px">
                                {{ $customer->last_repayment_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td style="text-align:center">
                                <button wire:click="selectCustomer({{ $customer->id }})" class="cr-action">
                                    Record Payment
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($this->customers->hasPages())
            <div style="padding:14px 20px;border-top:1px solid var(--border)">
                {{ $this->customers->links() }}
            </div>
        @endif
    @else
        <div class="cr-empty">
            <svg style="width:56px;height:56px;margin:0 auto 16px;color:var(--text-dim);opacity:.4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="cr-empty-title">
                @if($searchQuery) No customers match "{{ $searchQuery }}" @else No Customers with Outstanding Credit @endif
            </div>
            <div class="cr-empty-sub">
                @if($searchQuery) Try a different name or phone number @else All customers have cleared their balances @endif
            </div>
        </div>
    @endif
</div>

{{-- Repayment Modal --}}
@if($showRepaymentForm && $this->selectedCustomer)
    <div class="cr-modal-overlay" wire:click="cancelRepayment">
        <div class="cr-modal" wire:click.stop>

            <div class="cr-modal-head">
                <div>
                    <div class="cr-modal-title">Record Credit Repayment</div>
                    <div class="cr-modal-sub">{{ $this->selectedCustomer->name }} · {{ $this->selectedCustomer->phone }}</div>
                </div>
                <button wire:click="cancelRepayment" class="cr-modal-close">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="cr-modal-stats">
                <div>
                    <div class="cr-modal-stat-l">Outstanding Balance</div>
                    <div class="cr-modal-stat-v" style="color:var(--red)">{{ number_format($this->selectedCustomer->outstanding_balance) }}</div>
                </div>
                <div>
                    <div class="cr-modal-stat-l">Total Credit Given</div>
                    <div class="cr-modal-stat-v" style="color:var(--text)">{{ number_format($this->selectedCustomer->total_credit_given) }}</div>
                </div>
                <div>
                    <div class="cr-modal-stat-l">Total Repaid</div>
                    <div class="cr-modal-stat-v" style="color:var(--green)">{{ number_format($this->selectedCustomer->total_repaid) }}</div>
                </div>
            </div>

            <form wire:submit.prevent="recordRepayment">
                <div class="cr-field" style="margin-top:20px">
                    <label class="cr-label">Repayment Amount (RWF) <span>*</span></label>
                    <input type="number" wire:model="amount" min="1" step="1" placeholder="Enter amount…"
                           class="cr-input" style="font-family:var(--mono);font-weight:600">
                    @error('amount') <div class="cr-error">{{ $message }}</div> @enderror
                </div>

                <div class="cr-field">
                    <label class="cr-label">Payment Method <span>*</span></label>
                    <div class="cr-pm-grid">
                        @foreach($this->paymentMethods as $value => $label)
                            <label class="cr-pm-opt {{ $paymentMethod === $value ? 'active' : '' }}">
                                <input type="radio" wire:model.live="paymentMethod" value="{{ $value }}">
                                <span class="cr-pm-opt-label">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="cr-field">
                    <label class="cr-label">Reference / Transaction ID (Optional)</label>
                    <input type="text" wire:model="reference" placeholder="e.g., transfer reference, receipt number…" class="cr-input">
                </div>

                <div class="cr-field" style="margin-bottom:20px">
                    <label class="cr-label">Notes (Optional)</label>
                    <textarea wire:model="notes" rows="3" placeholder="Any additional notes…" class="cr-input" style="resize:vertical"></textarea>
                </div>

                <div class="cr-modal-foot">
                    <button type="button" wire:click="cancelRepayment" class="cr-cancel-btn">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="recordRepayment" class="cr-save-btn">
                        <span wire:loading.remove wire:target="recordRepayment">Record Repayment</span>
                        <span wire:loading wire:target="recordRepayment" style="display:none;align-items:center;gap:8px;justify-content:center">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:cr-spin 1s linear infinite">
                                <path d="M21 12a9 9 0 11-6.219-8.56"/>
                            </svg>
                            Recording…
                        </span>
                    </button>
                </div>
            </form>

            @if($this->selectedCustomer->creditRepayments->count() > 0)
                <div class="cr-history">
                    <div class="cr-history-title">Recent Repayment History</div>
                    @foreach($this->selectedCustomer->creditRepayments as $repayment)
                        <div class="cr-history-row">
                            <div>
                                <div class="cr-history-amt">{{ number_format($repayment->amount) }} RWF</div>
                                <div class="cr-history-date">{{ $repayment->repayment_date->format('M d, Y h:i A') }}</div>
                                @if($repayment->reference)
                                    <div class="cr-history-ref">Ref: {{ $repayment->reference }}</div>
                                @endif
                            </div>
                            <span class="cr-history-pill">{{ $repayment->payment_method->label() }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif

@endif {{-- end session gate --}}
</div>
