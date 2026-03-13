{{-- ┌─────────────────────────────────────────────────────────────────────────┐
    │  Owner · Customer Credit Report                                        │
    │  Track customer credit balances and payment history                   │
    │  Consistent with .bkpi design system (app.css)                        │
    └─────────────────────────────────────────────────────────────────────────┘ --}}
<div wire:poll.60s>
<style>
/* ── Font size increases for better readability ───────────────────── */
.cc-page-title { font-size:26px !important; }
.cc-page-subtitle { font-size:14px !important; }
.cc-section-title { font-size:16px !important; }
.cc-section-subtitle { font-size:13px !important; }
.cc-table thead th { font-size:12px !important; }
.cc-table tbody td { font-size:14px !important; }

/* ── Mobile responsive ───────────────────────────── */
@media(max-width:640px) {
    .cc-header-controls { flex-direction:column !important; align-items:stretch !important; gap:10px !important; }
    .cc-page-title { font-size:24px !important; }
    .cc-page-subtitle { font-size:13px !important; }
}

@media(max-width:640px) {
    .biz-kpi-grid { grid-template-columns:1fr 1fr !important; gap:10px !important; }
    .bkpi { padding:13px 14px !important; }
    .bkpi-value { font-size:22px !important; }
    .bkpi-label { font-size:12px !important; }
}

@media(max-width:640px) {
    .cc-table { display:block; overflow-x:auto; }
    .cc-table thead, .cc-table tbody, .cc-table tr { display:block; }
    .cc-table thead { display:none; }
    .cc-table tr { margin-bottom:16px; border:1px solid var(--border); border-radius:8px; padding:12px; background:var(--surface); }
    .cc-table td { display:block; padding:6px 0 !important; border:none !important; text-align:left !important; }
    .cc-table td::before { content:attr(data-label); display:inline-block; font-weight:600; width:140px; color:var(--text-sub); font-size:11px; text-transform:uppercase; letter-spacing:0.5px; }
}

/* Modal styles */
.cc-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:20px; }
.cc-modal.active { display:flex; }
.cc-modal-content { background:var(--surface); border-radius:12px; max-width:900px; width:100%; max-height:90vh; overflow-y:auto; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); }
@media(max-width:640px) {
    .cc-modal-content { max-width:100%; max-height:100%; border-radius:0; }
}
</style>

{{-- ══════════════════════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;flex-wrap:wrap">
    <div>
        <h1 class="cc-page-title" style="font-size:22px;font-weight:700;color:var(--text);letter-spacing:-0.5px;margin:0 0 4px">
            Customer Credit Report
        </h1>
        <div class="cc-page-subtitle" style="font-size:13px;color:var(--text-dim);font-family:var(--mono)">
            Track outstanding balances and payment history
            @if($locationFilter !== 'all')
                · {{ $this->selectedShopName }}
            @endif
            · auto-refreshes every 60s
        </div>
    </div>

    {{-- Shop filter --}}
    <div class="cc-header-controls" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
        <select wire:model.live="locationFilter"
            style="padding:6px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer">
            <option value="all">All Shops</option>
            @foreach($this->shops as $shop)
                <option value="shop:{{ $shop->id }}">{{ $shop->name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     SUMMARY KPI GRID
══════════════════════════════════════════════════════════════════════════ --}}
@php $summary = $this->creditSummary; @endphp
<div class="biz-kpi-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:32px">
    {{-- Total Outstanding --}}
    <div class="bkpi" style="padding:20px 24px;border-radius:12px;background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);box-shadow:0 4px 12px rgba(239,68,68,0.3)">
        <div class="bkpi-value" style="font-size:28px;font-weight:800;color:white;font-family:var(--mono);margin-bottom:6px">
            {{ number_format($summary['total_outstanding'] / 100, 0) }} RWF
        </div>
        <div class="bkpi-label" style="font-size:12px;font-weight:600;color:rgba(255,255,255,0.9);text-transform:uppercase;letter-spacing:0.8px">
            Total Outstanding
        </div>
    </div>

    {{-- Customers with Credit --}}
    <div class="bkpi" style="padding:20px 24px;border-radius:12px;background:var(--surface2);border:1px solid var(--border)">
        <div class="bkpi-value" style="font-size:28px;font-weight:800;color:var(--text);font-family:var(--mono);margin-bottom:6px">
            {{ number_format($summary['total_customers_with_credit']) }}
        </div>
        <div class="bkpi-label" style="font-size:12px;font-weight:600;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.8px">
            Customers with Credit
        </div>
    </div>

    {{-- Total Credit Given --}}
    <div class="bkpi" style="padding:20px 24px;border-radius:12px;background:var(--surface2);border:1px solid var(--border)">
        <div class="bkpi-value" style="font-size:28px;font-weight:800;color:var(--text);font-family:var(--mono);margin-bottom:6px">
            {{ number_format($summary['total_credit_given'] / 100, 0) }} RWF
        </div>
        <div class="bkpi-label" style="font-size:12px;font-weight:600;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.8px">
            Total Credit Given
        </div>
    </div>

    {{-- Total Repaid --}}
    <div class="bkpi" style="padding:20px 24px;border-radius:12px;background:var(--surface2);border:1px solid var(--border)">
        <div class="bkpi-value" style="font-size:28px;font-weight:800;color:var(--text);font-family:var(--mono);margin-bottom:6px">
            {{ number_format($summary['total_repaid'] / 100, 0) }} RWF
        </div>
        <div class="bkpi-label" style="font-size:12px;font-weight:600;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.8px">
            Total Repaid
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     FILTERS & SEARCH
══════════════════════════════════════════════════════════════════════════ --}}
<div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:24px">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;align-items:end">
        {{-- Search --}}
        <div>
            <label style="display:block;font-size:11px;font-weight:600;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">
                Search Customer
            </label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name or phone..."
                style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:14px">
        </div>

        {{-- Balance Filter --}}
        <div>
            <label style="display:block;font-size:11px;font-weight:600;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">
                Balance Status
            </label>
            <select wire:model.live="balanceFilter"
                style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:14px;cursor:pointer">
                <option value="all">All Customers</option>
                <option value="with_balance">With Outstanding Balance</option>
                <option value="no_balance">Fully Paid</option>
            </select>
        </div>

        {{-- Sort By --}}
        <div>
            <label style="display:block;font-size:11px;font-weight:600;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">
                Sort By
            </label>
            <select wire:model.live="sortBy"
                style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:14px;cursor:pointer">
                <option value="balance_desc">Highest Balance</option>
                <option value="balance_asc">Lowest Balance</option>
                <option value="name">Name (A-Z)</option>
                <option value="recent">Most Recent Credit</option>
            </select>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     CUSTOMERS LIST
══════════════════════════════════════════════════════════════════════════ --}}
<div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:24px">
    <h2 class="cc-section-title" style="font-size:18px;font-weight:700;color:var(--text);margin:0 0 16px">
        Customer Credit List
    </h2>

    <div style="overflow-x:auto">
        <table class="cc-table" style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="border-bottom:2px solid var(--border)">
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Customer</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Phone</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Shop</th>
                    <th style="text-align:right;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Outstanding</th>
                    <th style="text-align:right;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Credit Given</th>
                    <th style="text-align:right;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Repaid</th>
                    <th style="text-align:center;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Last Credit</th>
                    <th style="text-align:center;padding:12px 16px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;font-size:11px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->customers as $customer)
                    <tr style="border-bottom:1px solid var(--border)">
                        <td data-label="Customer" style="padding:12px 16px;font-weight:600;color:var(--text)">{{ $customer->name }}</td>
                        <td data-label="Phone" style="padding:12px 16px;font-family:var(--mono);color:var(--text-sub);font-size:12px">{{ $customer->phone }}</td>
                        <td data-label="Shop" style="padding:12px 16px;color:var(--text)">{{ $customer->shop?->name ?? 'N/A' }}</td>
                        <td data-label="Outstanding" style="text-align:right;padding:12px 16px;font-family:var(--mono);font-weight:700;color:{{ $customer->outstanding_balance > 0 ? '#ef4444' : 'var(--text-sub)' }}">
                            {{ number_format($customer->outstanding_balance / 100, 0) }} RWF
                        </td>
                        <td data-label="Credit Given" style="text-align:right;padding:12px 16px;font-family:var(--mono);color:var(--text)">
                            {{ number_format($customer->total_credit_given / 100, 0) }} RWF
                        </td>
                        <td data-label="Repaid" style="text-align:right;padding:12px 16px;font-family:var(--mono);color:#10b981">
                            {{ number_format($customer->total_repaid / 100, 0) }} RWF
                        </td>
                        <td data-label="Last Credit" style="text-align:center;padding:12px 16px;color:var(--text-sub);font-size:12px">
                            @if($customer->last_credit_at)
                                {{ $customer->last_credit_at->diffForHumans() }}
                            @else
                                <span style="color:var(--text-dim)">—</span>
                            @endif
                        </td>
                        <td data-label="Actions" style="text-align:center;padding:12px 16px">
                            <button wire:click="showCustomerHistory({{ $customer->id }})"
                                style="padding:6px 12px;border-radius:6px;background:var(--primary);color:white;font-size:11px;font-weight:600;border:none;cursor:pointer;text-transform:uppercase;letter-spacing:0.5px">
                                View History
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding:32px;text-align:center;color:var(--text-dim);font-style:italic">
                            No customers found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($this->customers->hasPages())
        <div style="margin-top:20px">
            {{ $this->customers->links() }}
        </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     CREDIT HISTORY MODAL
══════════════════════════════════════════════════════════════════════════ --}}
<div class="cc-modal {{ $showCreditHistory ? 'active' : '' }}" wire:click="closeCreditHistory">
    <div class="cc-modal-content" wire:click.stop style="padding:0">
        {{-- Modal Header --}}
        <div style="padding:24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div>
                <h3 style="font-size:20px;font-weight:700;color:var(--text);margin:0 0 4px">
                    @if($this->selectedCustomer)
                        {{ $this->selectedCustomer->name }}
                    @endif
                </h3>
                <div style="font-size:12px;color:var(--text-sub);font-family:var(--mono)">
                    Credit History
                    @if($this->selectedCustomer)
                        · {{ $this->selectedCustomer->phone }}
                    @endif
                </div>
            </div>
            <button wire:click="closeCreditHistory"
                style="width:32px;height:32px;border-radius:8px;background:var(--surface);border:1px solid var(--border);color:var(--text-sub);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700">
                ×
            </button>
        </div>

        {{-- Customer Summary --}}
        @if($this->selectedCustomer)
            <div style="padding:20px 24px;background:var(--surface);border-bottom:1px solid var(--border)">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px">
                    <div>
                        <div style="font-size:11px;color:var(--text-sub);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Outstanding Balance</div>
                        <div style="font-size:20px;font-weight:800;color:#ef4444;font-family:var(--mono)">
                            {{ number_format($this->selectedCustomer->outstanding_balance / 100, 0) }} RWF
                        </div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-sub);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Total Credit Given</div>
                        <div style="font-size:20px;font-weight:800;color:var(--text);font-family:var(--mono)">
                            {{ number_format($this->selectedCustomer->total_credit_given / 100, 0) }} RWF
                        </div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-sub);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Total Repaid</div>
                        <div style="font-size:20px;font-weight:800;color:#10b981;font-family:var(--mono)">
                            {{ number_format($this->selectedCustomer->total_repaid / 100, 0) }} RWF
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Credit Sales History --}}
        <div style="padding:24px;max-height:500px;overflow-y:auto">
            <h4 style="font-size:14px;font-weight:700;color:var(--text);margin:0 0 16px;text-transform:uppercase;letter-spacing:0.5px">
                Sales with Credit
            </h4>

            @forelse($this->customerCreditSales as $sale)
                <div style="padding:16px;background:var(--surface);border:1px solid var(--border);border-radius:8px;margin-bottom:12px">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:8px">
                        <div>
                            <div style="font-size:14px;font-weight:700;color:var(--text);font-family:var(--mono)">
                                {{ $sale->sale_number }}
                            </div>
                            <div style="font-size:11px;color:var(--text-sub);margin-top:2px">
                                {{ $sale->sale_date->format('M d, Y h:i A') }}
                                @if($sale->shop)
                                    · {{ $sale->shop->name }}
                                @endif
                            </div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:18px;font-weight:800;color:var(--text);font-family:var(--mono)">
                                {{ number_format($sale->total / 100, 0) }} RWF
                            </div>
                            <div style="font-size:11px;color:#ef4444;font-weight:600;margin-top:2px">
                                Credit: {{ number_format($sale->credit_amount / 100, 0) }} RWF
                            </div>
                        </div>
                    </div>
                    @if($sale->is_split_payment)
                        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px">
                            <span style="font-size:10px;color:var(--text-sub);font-weight:600">Payments:</span>
                            @foreach($sale->payments as $payment)
                                <span style="font-size:10px;font-weight:600;padding:3px 8px;border-radius:6px;background:var(--surface2);border:1px solid var(--border);color:var(--text-sub)">
                                    {{ $payment->payment_method->label() }}: {{ number_format($payment->amount / 100, 0) }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div style="padding:32px;text-align:center;color:var(--text-dim);font-style:italic">
                    No credit sales found for this customer
                </div>
            @endforelse
        </div>
    </div>
</div>

</div>
