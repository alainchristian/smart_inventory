<div>
    @if ($sessionBlocked)
        @include('components.session-gate-blocked')
    @else

    {{-- Success Message --}}
    @if (session()->has('success'))
        <div style="margin-bottom:20px;padding:16px 20px;border-radius:12px;background:#10b98120;border:1px solid #10b981;color:#10b981;font-size:14px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div style="margin-bottom:24px">
        <h1 style="font-size:24px;font-weight:700;color:var(--text);margin:0 0 6px">Credit Repayments</h1>
        <p style="font-size:14px;color:var(--text-sub);margin:0">Record customer credit repayments and track payment history</p>
    </div>

    {{-- Search Bar --}}
    <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:24px">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">
            Search Customer
        </label>
        <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search by name or phone..."
            style="width:100%;padding:12px 16px;border-radius:10px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:15px">
    </div>

    {{-- Customers List --}}
    <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
            <h2 style="font-size:16px;font-weight:700;color:var(--text);margin:0">Customers with Outstanding Credit</h2>
        </div>

        @if($this->customers->count() > 0)
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border);background:var(--surface)">
                            <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px">Customer</th>
                            <th style="text-align:left;padding:12px 20px;font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px">Phone</th>
                            <th style="text-align:right;padding:12px 20px;font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px">Outstanding</th>
                            <th style="text-align:right;padding:12px 20px;font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px">Credit Given</th>
                            <th style="text-align:right;padding:12px 20px;font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px">Repaid</th>
                            <th style="text-align:center;padding:12px 20px;font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.8px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->customers as $customer)
                            <tr style="border-bottom:1px solid var(--border)">
                                <td style="padding:14px 20px;font-weight:600;color:var(--text)">{{ $customer->name }}</td>
                                <td style="padding:14px 20px;font-family:var(--mono);color:var(--text-sub);font-size:13px">{{ $customer->phone }}</td>
                                <td style="text-align:right;padding:14px 20px;font-family:var(--mono);font-weight:700;color:#ef4444;font-size:15px">
                                    {{ number_format($customer->outstanding_balance, 0) }}
                                </td>
                                <td style="text-align:right;padding:14px 20px;font-family:var(--mono);color:var(--text-sub);font-size:13px">
                                    {{ number_format($customer->total_credit_given, 0) }}
                                </td>
                                <td style="text-align:right;padding:14px 20px;font-family:var(--mono);color:#10b981;font-size:13px;font-weight:600">
                                    {{ number_format($customer->total_repaid, 0) }}
                                </td>
                                <td style="text-align:center;padding:14px 20px">
                                    <button wire:click="selectCustomer({{ $customer->id }})"
                                        style="padding:8px 16px;border-radius:8px;background:var(--accent);color:white;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:opacity 0.2s"
                                        onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                        Record Payment
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($this->customers->hasPages())
                <div style="padding:16px 20px;border-top:1px solid var(--border)">
                    {{ $this->customers->links() }}
                </div>
            @endif
        @else
            <div style="text-align:center;padding:60px 20px;color:var(--text-dim)">
                <svg style="width:64px;height:64px;margin:0 auto 16px;opacity:0.3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div style="font-size:16px;font-weight:600;color:var(--text-sub);margin-bottom:4px">No Customers with Outstanding Credit</div>
                <div style="font-size:13px;font-style:italic">All customers have cleared their balances</div>
            </div>
        @endif
    </div>

    {{-- Repayment Modal --}}
    @if($showRepaymentForm && $this->selectedCustomer)
        <div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px"
             wire:click="cancelRepayment">
            <div style="background:var(--surface);border-radius:12px;max-width:600px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5)"
                 wire:click.stop>

                {{-- Modal Header --}}
                <div style="padding:24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <h3 style="font-size:20px;font-weight:700;color:var(--text);margin:0 0 4px">Record Credit Repayment</h3>
                        <div style="font-size:13px;color:var(--text-sub)">{{ $this->selectedCustomer->name }} · {{ $this->selectedCustomer->phone }}</div>
                    </div>
                    <button wire:click="cancelRepayment"
                        style="width:32px;height:32px;border-radius:8px;background:var(--surface2);border:1px solid var(--border);color:var(--text-sub);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700">
                        ×
                    </button>
                </div>

                {{-- Customer Balance Summary --}}
                <div style="padding:20px 24px;background:var(--surface2);border-bottom:1px solid var(--border)">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px">
                        <div>
                            <div style="font-size:11px;color:var(--text-sub);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Outstanding Balance</div>
                            <div style="font-size:22px;font-weight:800;color:#ef4444;font-family:var(--mono)">
                                {{ number_format($this->selectedCustomer->outstanding_balance, 0) }} RWF
                            </div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-sub);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Total Credit Given</div>
                            <div style="font-size:22px;font-weight:800;color:var(--text);font-family:var(--mono)">
                                {{ number_format($this->selectedCustomer->total_credit_given, 0) }} RWF
                            </div>
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-sub);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Total Repaid</div>
                            <div style="font-size:22px;font-weight:800;color:#10b981;font-family:var(--mono)">
                                {{ number_format($this->selectedCustomer->total_repaid, 0) }} RWF
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Repayment Form --}}
                <form wire:submit.prevent="recordRepayment" style="padding:24px">
                    <div style="margin-bottom:20px">
                        <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px">
                            Repayment Amount (RWF) <span style="color:#ef4444">*</span>
                        </label>
                        <input type="number" wire:model="amount" min="1" step="1" placeholder="Enter amount..."
                            style="width:100%;padding:12px 16px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:15px;font-family:var(--mono);font-weight:600">
                        @error('amount') <span style="color:#ef4444;font-size:12px;margin-top:4px;display:block">{{ $message }}</span> @enderror
                    </div>

                    <div style="margin-bottom:20px">
                        <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px">
                            Payment Method <span style="color:#ef4444">*</span>
                        </label>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px">
                            @foreach($this->paymentMethods as $value => $label)
                                <label style="display:flex;align-items:center;padding:12px;border-radius:10px;border:2px solid {{ $paymentMethod === $value ? 'var(--accent)' : 'var(--border)' }};background:{{ $paymentMethod === $value ? 'var(--accent-glow)' : 'var(--surface2)' }};cursor:pointer;transition:all 0.2s">
                                    <input type="radio" wire:model.live="paymentMethod" value="{{ $value }}" style="margin-right:10px">
                                    <span style="font-size:14px;font-weight:600;color:{{ $paymentMethod === $value ? 'var(--accent)' : 'var(--text)' }}">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div style="margin-bottom:20px">
                        <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px">
                            Reference / Transaction ID (Optional)
                        </label>
                        <input type="text" wire:model="reference" placeholder="e.g., Transfer reference, receipt number..."
                            style="width:100%;padding:12px 16px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:14px">
                    </div>

                    <div style="margin-bottom:24px">
                        <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px">
                            Notes (Optional)
                        </label>
                        <textarea wire:model="notes" rows="3" placeholder="Any additional notes..."
                            style="width:100%;padding:12px 16px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:14px;resize:vertical"></textarea>
                    </div>

                    <div style="display:flex;gap:12px">
                        <button type="button" wire:click="cancelRepayment"
                            style="flex:1;padding:14px;border-radius:10px;background:var(--surface2);border:1px solid var(--border);color:var(--text);font-size:15px;font-weight:600;cursor:pointer;transition:all 0.2s"
                            onmouseover="this.style.background='var(--surface3)'" onmouseout="this.style.background='var(--surface2)'">
                            Cancel
                        </button>
                        <button type="submit"
                            style="flex:2;padding:14px;border-radius:10px;background:var(--accent);border:none;color:white;font-size:15px;font-weight:700;cursor:pointer;transition:opacity 0.2s"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            Record Repayment
                        </button>
                    </div>
                </form>

                {{-- Recent Repayment History --}}
                @if($this->selectedCustomer->creditRepayments->count() > 0)
                    <div style="padding:20px 24px;border-top:1px solid var(--border);background:var(--surface2)">
                        <h4 style="font-size:14px;font-weight:700;color:var(--text);margin:0 0 16px">Recent Repayment History</h4>
                        <div style="space-y:12px">
                            @foreach($this->selectedCustomer->creditRepayments as $repayment)
                                <div style="padding:12px;background:var(--surface);border:1px solid var(--border);border-radius:8px;margin-bottom:8px">
                                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:6px">
                                        <div>
                                            <div style="font-size:16px;font-weight:700;color:#10b981;font-family:var(--mono)">
                                                {{ number_format($repayment->amount, 0) }} RWF
                                            </div>
                                            <div style="font-size:11px;color:var(--text-sub);margin-top:2px">
                                                {{ $repayment->repayment_date->format('M d, Y h:i A') }}
                                            </div>
                                        </div>
                                        <span style="padding:4px 10px;border-radius:6px;background:var(--surface2);border:1px solid var(--border);font-size:11px;font-weight:600;color:var(--text-sub);text-transform:uppercase">
                                            {{ $repayment->payment_method->label() }}
                                        </span>
                                    </div>
                                    @if($repayment->reference)
                                        <div style="font-size:12px;color:var(--text-sub);margin-top:4px">
                                            Ref: {{ $repayment->reference }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @endif {{-- end session gate --}}
</div>
