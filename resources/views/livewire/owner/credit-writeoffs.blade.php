<div>
    {{-- Flash messages --}}
    @if (session()->has('success'))
        <div style="margin-bottom:20px;padding:14px 18px;border-radius:12px;background:var(--green-dim);border:1px solid var(--green);color:var(--green);font-size:14px;font-weight:600;">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div style="margin-bottom:20px;padding:14px 18px;border-radius:12px;background:var(--red-dim);border:1px solid var(--red);color:var(--red);font-size:14px;font-weight:600;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Page header --}}
    <div style="margin-bottom:24px;">
        <h1 style="font-size:22px;font-weight:700;color:var(--text);margin:0 0 4px;">Credit Write-offs</h1>
        <p style="font-size:13px;color:var(--text-dim);margin:0;">Owner-only. Permanently reduces a customer's outstanding credit balance.</p>
    </div>

    {{-- Search --}}
    <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:16px 20px;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
        <label style="display:block;font-size:11px;font-weight:600;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Search Customer</label>
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Search by name or phone..."
               style="width:100%;padding:10px 14px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:14px;">
    </div>

    {{-- Customer table --}}
    <div style="background:white;border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
        <div style="padding:12px 20px;border-bottom:1px solid var(--border);background:var(--surface2);">
            <h2 style="font-size:13px;font-weight:700;color:var(--text);margin:0;">Customers with Outstanding Credit</h2>
        </div>

        @if($this->customers->count() > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--surface2);border-bottom:1px solid var(--border);">
                            <th style="text-align:left;padding:9px 20px;font-size:10px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.7px;">Customer</th>
                            <th style="text-align:left;padding:9px 20px;font-size:10px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.7px;">Phone</th>
                            <th style="text-align:right;padding:9px 20px;font-size:10px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.7px;">Outstanding</th>
                            <th style="text-align:left;padding:9px 20px;font-size:10px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.7px;">Last Repayment</th>
                            <th style="text-align:center;padding:9px 20px;font-size:10px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.7px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->customers as $customer)
                            @php $rowBg = $writeoffCustomerId === $customer->id ? 'var(--surface2)' : 'white'; @endphp
                            <tr style="border-bottom:1px solid var(--border);background:{{ $rowBg }};"
                                onmouseover="this.style.background='var(--surface2)'"
                                onmouseout="this.style.background='{{ $rowBg }}'">
                                <td style="padding:13px 20px;font-weight:600;color:var(--text);">{{ $customer->name }}</td>
                                <td style="padding:13px 20px;font-family:var(--mono);color:var(--text-dim);font-size:13px;">{{ $customer->phone }}</td>
                                <td style="text-align:right;padding:13px 20px;font-family:var(--mono);font-weight:700;color:var(--red);font-size:15px;">
                                    {{ number_format($customer->outstanding_balance) }} RWF
                                </td>
                                <td style="padding:13px 20px;font-size:13px;color:var(--text-dim);">
                                    {{ $customer->last_repayment_at ? $customer->last_repayment_at->diffForHumans() : 'Never' }}
                                </td>
                                <td style="text-align:center;padding:13px 20px;">
                                    @if($writeoffCustomerId === $customer->id)
                                        <button wire:click="cancelWriteoff"
                                                style="padding:7px 14px;border-radius:8px;background:white;border:1px solid var(--border);color:var(--text-dim);font-size:13px;font-weight:600;cursor:pointer;">
                                            Cancel
                                        </button>
                                    @else
                                        <button wire:click="startWriteoff({{ $customer->id }})"
                                                style="padding:7px 14px;border-radius:8px;background:var(--red-dim);border:1px solid var(--red);color:var(--red);font-size:13px;font-weight:600;cursor:pointer;">
                                            Write Off
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            {{-- Inline write-off form --}}
                            @if($writeoffCustomerId === $customer->id && $this->selectedCustomer)
                                <tr>
                                    <td colspan="5" style="padding:0;background:var(--surface2);border-bottom:2px solid var(--red);">
                                        <div style="padding:20px 24px;">

                                            @if(! $confirmStep)
                                                {{-- STEP 1: Enter amount and reason --}}
                                                <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:16px;">
                                                    Write-off for {{ $this->selectedCustomer->name }}
                                                    <span style="font-weight:400;color:var(--text-dim);margin-left:8px;">Balance: {{ number_format($this->selectedCustomer->outstanding_balance) }} RWF</span>
                                                </div>

                                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="writeoff-form-grid">
                                                <style>.writeoff-form-grid{grid-template-columns:1fr 1fr;} @media(max-width:700px){.writeoff-form-grid{grid-template-columns:1fr!important;}}</style>

                                                    <div>
                                                        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-dim);margin-bottom:6px;">
                                                            Amount to write off (RWF) <span style="color:var(--red)">*</span>
                                                        </label>
                                                        <div style="display:flex;gap:8px;align-items:center;">
                                                            <input type="number" wire:model="writeoffAmount"
                                                                   min="1" max="{{ $this->selectedCustomer->outstanding_balance }}"
                                                                   style="flex:1;padding:10px 14px;border-radius:10px;border:1px solid var(--border);background:white;color:var(--text);font-size:15px;font-weight:600;font-family:var(--mono);">
                                                            <button wire:click="fillFullBalance" type="button"
                                                                    style="padding:10px 12px;border-radius:10px;background:white;border:1px solid var(--border);color:var(--text-dim);font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;">
                                                                Full balance
                                                            </button>
                                                        </div>
                                                        <div style="font-size:11px;color:var(--text-dim);margin-top:4px;">Max: {{ number_format($this->selectedCustomer->outstanding_balance) }} RWF</div>
                                                        @error('writeoffAmount') <div style="color:var(--red);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                                                    </div>

                                                    <div>
                                                        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-dim);margin-bottom:6px;">
                                                            Reason <span style="color:var(--red)">*</span>
                                                        </label>
                                                        <textarea wire:model="writeoffReason" rows="3"
                                                                  placeholder="Explain why this debt is being written off (min 10 characters)..."
                                                                  style="width:100%;padding:10px 14px;border-radius:10px;border:1px solid var(--border);background:white;color:var(--text);font-size:13px;resize:vertical;"></textarea>
                                                        @error('writeoffReason') <div style="color:var(--red);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>

                                                <div style="display:flex;gap:10px;margin-top:16px;">
                                                    <button wire:click="cancelWriteoff"
                                                            style="padding:10px 20px;border-radius:10px;background:white;border:1px solid var(--border);color:var(--text-dim);font-size:13px;font-weight:600;cursor:pointer;">
                                                        Cancel
                                                    </button>
                                                    <button wire:click="proceedToConfirm"
                                                            style="padding:10px 20px;border-radius:10px;background:var(--amber-dim);border:1px solid var(--amber);color:var(--amber);font-size:13px;font-weight:700;cursor:pointer;">
                                                        Review Write-off →
                                                    </button>
                                                </div>

                                            @else
                                                {{-- STEP 2: Confirm --}}
                                                @php
                                                    $balAfter = $this->selectedCustomer->outstanding_balance - $writeoffAmount;
                                                @endphp
                                                <div style="background:var(--red-dim);border:1px solid var(--red);border-radius:12px;padding:16px 20px;margin-bottom:20px;">
                                                    <div style="font-size:14px;font-weight:700;color:var(--red);margin-bottom:4px;">
                                                        ⚠ You are about to permanently write off {{ number_format($writeoffAmount) }} RWF from {{ $this->selectedCustomer->name }}'s balance. This cannot be undone.
                                                    </div>
                                                </div>

                                                <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:16px 20px;margin-bottom:16px;">
                                                    <div style="display:grid;gap:10px;">
                                                        <div style="display:flex;justify-content:space-between;font-size:13px;">
                                                            <span style="color:var(--text-dim);">Current balance</span>
                                                            <span style="font-weight:700;color:var(--text);font-family:var(--mono);">{{ number_format($this->selectedCustomer->outstanding_balance) }} RWF</span>
                                                        </div>
                                                        <div style="display:flex;justify-content:space-between;font-size:13px;padding:8px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
                                                            <span style="color:var(--red);">Write-off amount</span>
                                                            <span style="font-weight:700;color:var(--red);font-family:var(--mono);">−{{ number_format($writeoffAmount) }} RWF</span>
                                                        </div>
                                                        <div style="display:flex;justify-content:space-between;font-size:14px;">
                                                            <span style="font-weight:600;color:var(--text);">Balance after</span>
                                                            <span style="font-weight:800;color:{{ $balAfter === 0 ? 'var(--green)' : 'var(--text)' }};font-family:var(--mono);">{{ number_format($balAfter) }} RWF</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div style="font-size:12px;color:var(--text-dim);padding:10px 14px;background:white;border:1px solid var(--border);border-radius:10px;margin-bottom:16px;">
                                                    <span style="font-weight:600;color:var(--text-sub);">Reason:</span> {{ $writeoffReason }}
                                                </div>

                                                <div style="display:flex;gap:10px;">
                                                    <button wire:click="$set('confirmStep', false)"
                                                            style="padding:10px 20px;border-radius:10px;background:white;border:1px solid var(--border);color:var(--text-dim);font-size:13px;font-weight:600;cursor:pointer;">
                                                        ← Go Back
                                                    </button>
                                                    <button wire:click="submitWriteoff"
                                                            style="padding:10px 24px;border-radius:10px;background:var(--red);border:none;color:#fff;font-size:13px;font-weight:700;cursor:pointer;">
                                                        Confirm Write-off
                                                    </button>
                                                </div>
                                            @endif

                                            {{-- Write-off history --}}
                                            @if($this->selectedCustomer->writeoffs->count() > 0)
                                                <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
                                                    <div style="font-size:12px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:12px;">Previous Write-offs</div>
                                                    @foreach($this->selectedCustomer->writeoffs as $wo)
                                                        <div style="display:flex;align-items:start;justify-content:space-between;padding:10px 14px;background:white;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;">
                                                            <div>
                                                                <div style="font-size:14px;font-weight:700;color:var(--red);font-family:var(--mono);">{{ number_format($wo->amount) }} RWF</div>
                                                                <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">{{ $wo->written_off_at->format('d M Y') }} · by {{ $wo->writtenOffBy?->name ?? '—' }}</div>
                                                                <div style="font-size:12px;color:var(--text-dim);margin-top:4px;">{{ $wo->reason }}</div>
                                                            </div>
                                                            <div style="text-align:right;font-size:11px;color:var(--text-dim);white-space:nowrap;padding-left:12px;">
                                                                {{ number_format($wo->balance_before) }} → {{ number_format($wo->balance_after) }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($this->customers->hasPages())
                <div style="padding:14px 20px;border-top:1px solid var(--border);">
                    {{ $this->customers->links() }}
                </div>
            @endif
        @else
            <div style="text-align:center;padding:60px 20px;color:var(--text-dim);">
                <div style="font-size:15px;font-weight:600;color:var(--text-dim);margin-bottom:4px;">No customers with outstanding credit</div>
                <div style="font-size:13px;">
                    @if($search)
                        No results for "{{ $search }}"
                    @else
                        All balances are clear
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
