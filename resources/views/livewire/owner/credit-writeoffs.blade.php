<div style="font-family:var(--font)">
<style>
.cw-page         { padding:0 0 80px; }
.cw-header       { margin-bottom:24px; }
.cw-header-title { font-size:22px;font-weight:800;color:var(--text);margin:0 0 4px; }
.cw-header-sub   { font-size:13px;color:var(--text-dim);margin:0; }

/* ── Flash messages ── */
.cw-flash        { margin-bottom:20px;padding:14px 18px;border-radius:var(--r);
                   font-size:14px;font-weight:600; }
.cw-flash.success { background:var(--green-dim);border:1px solid var(--green);color:var(--green); }
.cw-flash.error   { background:var(--red-dim);border:1px solid var(--red);color:var(--red); }

/* ── Search card ── */
.cw-search-card  { background:var(--surface);border:none;border-radius:var(--r);
                   padding:16px 20px;margin-bottom:20px;box-shadow:var(--shadow-card); }
.cw-search-label { display:block;font-size:11px;font-weight:700;color:var(--text-dim);
                   text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px; }
.cw-search-input { width:100%;padding:10px 14px;border-radius:var(--rsm);
                   border:1px solid var(--border);background:var(--surface);
                   color:var(--text);font-size:14px;font-family:var(--font);outline:none;
                   transition:border-color var(--tr); }
.cw-search-input:focus { border-color:var(--accent); }

/* ── Customer table card ── */
.cw-card         { background:var(--surface);border:none;border-radius:var(--r);
                   box-shadow:var(--shadow-card);margin-bottom:20px; }
.cw-card-head    { padding:14px 20px;border-bottom:1px solid var(--border);
                   display:flex;align-items:center;justify-content:space-between; }
.cw-card-title   { font-size:13px;font-weight:700;color:var(--text);margin:0; }

.cw-table-wrap   { overflow-x:auto; }
.cw-table        { width:100%;border-collapse:collapse; }
.cw-table thead tr { border-bottom:2px solid var(--border); }
.cw-table th     { padding:9px 20px;font-size:10px;font-weight:700;color:var(--text-dim);
                   text-transform:uppercase;letter-spacing:0.7px;white-space:nowrap; }
.cw-table th:not(:first-child) { text-align:right; }
.cw-table th:last-child        { text-align:center; }
.cw-row          { border-bottom:1px solid var(--border);transition:background var(--tr); }
.cw-row:hover    { background:var(--surface2); }
.cw-row.active   { background:var(--surface2); }
.cw-td           { padding:13px 20px;vertical-align:middle; }

/* ── Inline write-off form ── */
.cw-form-row td  { padding:0;border-bottom:1px solid var(--border); }
.cw-form-inner   { padding:20px 24px;border-left:3px solid var(--red); }
.cw-form-title   { font-size:13px;font-weight:700;color:var(--text);margin-bottom:16px; }
.cw-form-grid    { display:grid;grid-template-columns:1fr 1fr;gap:20px; }
.cw-form-label   { display:block;font-size:12px;font-weight:600;color:var(--text-dim);margin-bottom:6px; }
.cw-form-input   { width:100%;padding:10px 14px;border-radius:var(--rsm);
                   border:1px solid var(--border);background:var(--surface);
                   color:var(--text);font-size:15px;font-weight:600;font-family:var(--mono);
                   outline:none;transition:border-color var(--tr); }
.cw-form-input:focus { border-color:var(--accent); }
.cw-form-hint    { font-size:11px;color:var(--text-dim);margin-top:4px; }
.cw-form-error   { font-size:12px;color:var(--red);margin-top:4px; }
.cw-form-actions { display:flex;gap:10px;margin-top:16px; }

/* ── Confirm summary ── */
.cw-warn-banner  { background:var(--red-dim);border:1px solid var(--red);border-radius:var(--rsm);
                   padding:14px 18px;margin-bottom:20px;
                   font-size:13px;font-weight:700;color:var(--red); }
.cw-summary-card { border:1px solid var(--border);border-radius:var(--rsm);
                   padding:16px 20px;margin-bottom:16px; }
.cw-summary-row  { display:flex;justify-content:space-between;font-size:13px;padding:4px 0; }
.cw-reason-box   { font-size:12px;color:var(--text-dim);padding:10px 14px;
                   border:1px solid var(--border);border-radius:var(--rsm);margin-bottom:16px; }

/* ── Write-off history ── */
.cw-history-head { font-size:11px;font-weight:700;color:var(--text-dim);
                   text-transform:uppercase;letter-spacing:0.6px;margin-bottom:12px; }
.cw-wo-item      { display:flex;align-items:start;justify-content:space-between;
                   padding:10px 14px;border:1px solid var(--border);border-radius:var(--rsm);
                   margin-bottom:8px; }

/* ── Buttons ── */
.cw-btn          { padding:10px 20px;border-radius:var(--rsm);font-size:13px;font-weight:600;
                   cursor:pointer;font-family:var(--font);transition:all var(--tr);border:1px solid var(--border); }
.cw-btn-ghost    { background:var(--surface);color:var(--text-dim); }
.cw-btn-ghost:hover { background:var(--surface2);color:var(--text); }
.cw-btn-amber    { background:var(--amber-dim);border-color:var(--amber);color:var(--amber);font-weight:700; }
.cw-btn-amber:hover { background:var(--amber);color:#fff; }
.cw-btn-red      { background:var(--red);border-color:var(--red);color:#fff;font-weight:700; }
.cw-btn-red:hover { background:var(--red);opacity:.9; }
.cw-btn-sm       { padding:7px 14px;font-size:12px; }
.cw-btn-red-outline { background:var(--red-dim);border-color:var(--red);color:var(--red);font-weight:600; }
.cw-btn-red-outline:hover { background:var(--red);color:#fff; }

/* ── Empty state ── */
.cw-empty        { text-align:center;padding:60px 20px;color:var(--text-dim); }
.cw-empty-title  { font-size:15px;font-weight:600;color:var(--text-sub);margin-bottom:4px; }

/* ── Responsive ── */
@media(max-width:700px) {
    .cw-form-grid { grid-template-columns:1fr; }
    .cw-table th:nth-child(2),
    .cw-table td:nth-child(2) { display:none; }
}
</style>

<div class="cw-page">

    {{-- Flash messages --}}
    @if (session()->has('success'))
        <div class="cw-flash success">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="cw-flash error">{{ session('error') }}</div>
    @endif

    {{-- Page header --}}
    <div class="cw-header">
        <h1 class="cw-header-title">Credit Write-offs</h1>
        <p class="cw-header-sub">Owner-only. Permanently reduces a customer's outstanding credit balance.</p>
    </div>

    {{-- Search --}}
    <div class="cw-search-card">
        <label class="cw-search-label">Search Customer</label>
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Search by name or phone..."
               class="cw-search-input">
    </div>

    {{-- Customer table --}}
    <div class="cw-card">
        <div class="cw-card-head">
            <h2 class="cw-card-title">Customers with Outstanding Credit</h2>
        </div>

        @if($this->customers->count() > 0)
            <div class="cw-table-wrap">
                <table class="cw-table">
                    <thead>
                        <tr>
                            <th style="text-align:left;">Customer</th>
                            <th style="text-align:left;">Phone</th>
                            <th>Outstanding</th>
                            <th>Last Repayment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->customers as $customer)
                            <tr class="cw-row {{ $writeoffCustomerId === $customer->id ? 'active' : '' }}">
                                <td class="cw-td" style="font-weight:600;color:var(--text);">{{ $customer->name }}</td>
                                <td class="cw-td" style="font-family:var(--mono);color:var(--text-dim);font-size:13px;">{{ $customer->phone }}</td>
                                <td class="cw-td" style="text-align:right;font-family:var(--mono);font-weight:700;color:var(--red);font-size:15px;">
                                    {{ number_format($customer->outstanding_balance) }} RWF
                                </td>
                                <td class="cw-td" style="text-align:right;font-size:13px;color:var(--text-dim);">
                                    {{ $customer->last_repayment_at ? $customer->last_repayment_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="cw-td" style="text-align:center;">
                                    @if($writeoffCustomerId === $customer->id)
                                        <button wire:click="cancelWriteoff" class="cw-btn cw-btn-ghost cw-btn-sm">Cancel</button>
                                    @else
                                        <button wire:click="startWriteoff({{ $customer->id }})" class="cw-btn cw-btn-red-outline cw-btn-sm">Write Off</button>
                                    @endif
                                </td>
                            </tr>

                            {{-- Inline write-off form --}}
                            @if($writeoffCustomerId === $customer->id && $this->selectedCustomer)
                                <tr class="cw-form-row">
                                    <td colspan="5">
                                        <div class="cw-form-inner">

                                            @if(! $confirmStep)
                                                {{-- Step 1: amount + reason --}}
                                                <div class="cw-form-title">
                                                    Write-off for {{ $this->selectedCustomer->name }}
                                                    <span style="font-weight:400;color:var(--text-dim);margin-left:8px;">
                                                        Balance: {{ number_format($this->selectedCustomer->outstanding_balance) }} RWF
                                                    </span>
                                                </div>

                                                <div class="cw-form-grid">
                                                    <div>
                                                        <label class="cw-form-label">
                                                            Amount to write off (RWF) <span style="color:var(--red)">*</span>
                                                        </label>
                                                        <div style="display:flex;gap:8px;align-items:center;">
                                                            <input type="number" wire:model="writeoffAmount"
                                                                   min="1" max="{{ $this->selectedCustomer->outstanding_balance }}"
                                                                   class="cw-form-input">
                                                            <button wire:click="fillFullBalance" type="button" class="cw-btn cw-btn-ghost" style="white-space:nowrap;">
                                                                Full balance
                                                            </button>
                                                        </div>
                                                        <div class="cw-form-hint">Max: {{ number_format($this->selectedCustomer->outstanding_balance) }} RWF</div>
                                                        @error('writeoffAmount') <div class="cw-form-error">{{ $message }}</div> @enderror
                                                    </div>

                                                    <div>
                                                        <label class="cw-form-label">
                                                            Reason <span style="color:var(--red)">*</span>
                                                        </label>
                                                        <textarea wire:model="writeoffReason" rows="3"
                                                                  placeholder="Explain why this debt is being written off (min 10 characters)..."
                                                                  class="cw-form-input" style="resize:vertical;font-family:var(--font);font-size:13px;font-weight:400;"></textarea>
                                                        @error('writeoffReason') <div class="cw-form-error">{{ $message }}</div> @enderror
                                                    </div>
                                                </div>

                                                <div class="cw-form-actions">
                                                    <button wire:click="cancelWriteoff" class="cw-btn cw-btn-ghost">Cancel</button>
                                                    <button wire:click="proceedToConfirm" class="cw-btn cw-btn-amber">Review Write-off →</button>
                                                </div>

                                            @else
                                                {{-- Step 2: confirm --}}
                                                @php $balAfter = $this->selectedCustomer->outstanding_balance - $writeoffAmount; @endphp

                                                <div class="cw-warn-banner">
                                                    ⚠ You are about to permanently write off {{ number_format($writeoffAmount) }} RWF from {{ $this->selectedCustomer->name }}'s balance. This cannot be undone.
                                                </div>

                                                <div class="cw-summary-card">
                                                    <div class="cw-summary-row">
                                                        <span style="color:var(--text-dim);">Current balance</span>
                                                        <span style="font-weight:700;font-family:var(--mono);">{{ number_format($this->selectedCustomer->outstanding_balance) }} RWF</span>
                                                    </div>
                                                    <div class="cw-summary-row" style="border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:8px 0;margin:4px 0;">
                                                        <span style="color:var(--red);">Write-off amount</span>
                                                        <span style="font-weight:700;color:var(--red);font-family:var(--mono);">−{{ number_format($writeoffAmount) }} RWF</span>
                                                    </div>
                                                    <div class="cw-summary-row">
                                                        <span style="font-weight:600;">Balance after</span>
                                                        <span style="font-weight:800;font-family:var(--mono);color:{{ $balAfter === 0 ? 'var(--green)' : 'var(--text)' }};">{{ number_format($balAfter) }} RWF</span>
                                                    </div>
                                                </div>

                                                <div class="cw-reason-box">
                                                    <span style="font-weight:600;color:var(--text-sub);">Reason:</span> {{ $writeoffReason }}
                                                </div>

                                                <div class="cw-form-actions">
                                                    <button wire:click="$set('confirmStep', false)" class="cw-btn cw-btn-ghost">← Go Back</button>
                                                    <button wire:click="submitWriteoff" class="cw-btn cw-btn-red">Confirm Write-off</button>
                                                </div>
                                            @endif

                                            {{-- Write-off history --}}
                                            @if($this->selectedCustomer->writeoffs->count() > 0)
                                                <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
                                                    <div class="cw-history-head">Previous Write-offs</div>
                                                    @foreach($this->selectedCustomer->writeoffs as $wo)
                                                        <div class="cw-wo-item">
                                                            <div>
                                                                <div style="font-size:14px;font-weight:700;color:var(--red);font-family:var(--mono);">{{ number_format($wo->amount) }} RWF</div>
                                                                <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">{{ $wo->written_off_at->format('d M Y') }} · by {{ $wo->writtenOffBy?->name ?? '—' }}</div>
                                                                <div style="font-size:12px;color:var(--text-dim);margin-top:4px;">{{ $wo->reason }}</div>
                                                            </div>
                                                            <div style="text-align:right;font-size:11px;font-family:var(--mono);color:var(--text-dim);white-space:nowrap;padding-left:12px;">
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
            <div class="cw-empty">
                <div class="cw-empty-title">No customers with outstanding credit</div>
                <div style="font-size:13px;">
                    @if($search) No results for "{{ $search }}" @else All balances are clear @endif
                </div>
            </div>
        @endif
    </div>

</div>
</div>
