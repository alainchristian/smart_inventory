<div style="font-family:var(--font);color:var(--text);min-height:100vh;padding:24px;">

    {{-- Session gate --}}
    @if($sessionBlocked)
        <x-session-gate-blocked
            :reason="$sessionBlockReason"
            :session-date="$blockedSessionDate"
            :session-id="$blockedSessionId"
        />
    @else

    {{-- Flash Messages --}}
    @if (session()->has('error'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;
                    background:var(--red-dim);border:1px solid var(--red);
                    font-size:13px;font-weight:600;color:var(--red);">
            {{ session('error') }}
        </div>
    @endif
    @if (session()->has('success'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;
                    background:var(--green-dim);border:1px solid var(--green);
                    font-size:13px;font-weight:600;color:var(--green);">
            {{ session('success') }}
        </div>
    @endif

    {{-- Page header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div>
            <div style="font-size:20px;font-weight:800;color:var(--text);">Process Return</div>
            <div style="font-size:13px;color:var(--text-dim);margin-top:2px;">{{ $shopName }}</div>
        </div>
        <a href="{{ route('shop.returns.index') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
                  border-radius:8px;font-size:13px;font-weight:600;
                  border:1.5px solid var(--border);background:transparent;
                  color:var(--text-dim);text-decoration:none;transition:border-color 0.15s;"
           onmouseover="this.style.borderColor='var(--accent)';"
           onmouseout="this.style.borderColor='var(--border)';">
            ← Back to Returns
        </a>
    </div>

    {{-- Two-column layout --}}
    <div class="pr-grid" style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

        {{-- ============================================================
             MAIN WIZARD COLUMN
        ============================================================ --}}
        <div>

            {{-- Step Indicator --}}
            <div style="display:flex;align-items:center;margin-bottom:28px;">
                @foreach ([
                    [1, 'Find Sale'],
                    [2, 'Select Items'],
                    [3, 'Return Details'],
                ] as [$stepNum, $stepLabel])
                    <div style="display:flex;align-items:center;{{ !$loop->last ? 'flex:1;' : '' }}">
                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                            <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;
                                        justify-content:center;font-size:11px;font-weight:700;
                                        {{ $currentStep > $stepNum
                                            ? 'background:var(--green);color:white;'
                                            : ($currentStep === $stepNum
                                                ? 'background:var(--accent);color:white;'
                                                : 'background:var(--surface-raised);color:var(--text-faint);border:1.5px solid var(--border);') }}">
                                @if ($currentStep > $stepNum)
                                    <svg style="width:13px;height:13px;" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    {{ $stepNum }}
                                @endif
                            </div>
                            <span style="font-size:12px;font-weight:600;
                                         color:{{ $currentStep === $stepNum ? 'var(--text)' : 'var(--text-dim)' }};">
                                {{ $stepLabel }}
                            </span>
                        </div>
                        @if (!$loop->last)
                            <div style="flex:1;height:1px;background:var(--border);margin:0 12px;"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- ============================================================
                 STEP 1 — Find Sale
            ============================================================ --}}
            @if ($currentStep === 1)
                <div style="background:var(--surface-raised);border:1px solid var(--border);
                            border-radius:16px;padding:20px;">
                    <div style="font-size:14px;font-weight:700;color:var(--text);">Find Sale</div>
                    <div style="font-size:12px;color:var(--text-dim);margin-top:2px;">
                        Search by sale number or customer name
                    </div>

                    <div style="position:relative;margin-top:16px;">
                        <input type="text"
                               wire:model.live.debounce.300ms="saleSearch"
                               wire:focus="$set('showSaleSearchDropdown', true)"
                               placeholder="Sale number or customer name..."
                               style="width:100%;padding:10px 16px 10px 40px;border-radius:10px;
                                      font-size:14px;background:var(--surface);border:1.5px solid var(--border);
                                      color:var(--text);box-sizing:border-box;transition:border-color 0.15s;"
                               onfocus="this.style.borderColor='var(--accent)';"
                               onblur="this.style.borderColor='var(--border)';">
                        <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                                    width:16px;height:16px;color:var(--text-dim);" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    {{-- Search results dropdown --}}
                    @if ($showSaleSearchDropdown && count($saleSearchResults) > 0)
                        <div style="margin-top:4px;border-radius:10px;border:1px solid var(--border);
                                    background:var(--surface-raised);overflow:hidden;
                                    box-shadow:0 4px 16px rgba(0,0,0,0.08);">
                            @foreach ($saleSearchResults as $result)
                                <button type="button"
                                        wire:click="selectSale({{ $result['id'] }})"
                                        style="width:100%;padding:10px 14px;text-align:left;border:none;
                                               border-bottom:1px solid var(--border);background:transparent;
                                               cursor:pointer;transition:background 0.1s;font-family:var(--font);"
                                        onmouseover="this.style.background='var(--surface)';"
                                        onmouseout="this.style.background='transparent';">
                                    <div style="font-size:13px;font-weight:700;color:var(--text);">
                                        {{ $result['sale_number'] }}
                                    </div>
                                    <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">
                                        {{ $result['customer_name'] ?? 'Walk-in' }}
                                        · {{ $result['created_at'] }}
                                        · {{ number_format($result['total']) }} RWF
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Today's sales quick button --}}
                    <div style="margin-top:12px;display:flex;align-items:center;gap:8px;">
                        <button type="button"
                                wire:click="loadTodaySales"
                                style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;
                                       border-radius:7px;font-size:12px;font-weight:600;
                                       border:1px solid var(--border);background:var(--surface);
                                       color:var(--text-dim);cursor:pointer;font-family:var(--font);">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Today's Sales
                        </button>
                        <span style="font-size:11px;color:var(--text-faint);">or search above</span>
                    </div>

                    @if ($saleAgeWarning)
                        <div style="margin-top:12px;padding:10px 14px;border-radius:8px;
                                    background:var(--amber-dim);border:1px solid var(--amber);
                                    display:flex;align-items:flex-start;gap:8px;">
                            <svg style="width:16px;height:16px;color:var(--amber);flex-shrink:0;margin-top:1px;"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <div style="font-size:12px;font-weight:700;color:var(--amber);">Sale is over 7 days old</div>
                                <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">
                                    Returns older than 7 days may require owner approval.
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($existingReturnWarning)
                        <div style="margin-top:12px;padding:10px 14px;border-radius:8px;
                                    background:var(--red-dim);border:1px solid var(--red);
                                    display:flex;align-items:flex-start;gap:8px;">
                            <svg style="width:16px;height:16px;color:var(--red);flex-shrink:0;margin-top:1px;"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div style="font-size:12px;color:var(--red);">{{ $existingReturnWarning }}</div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ============================================================
                 STEP 2 — Select Items
            ============================================================ --}}
            @if ($currentStep === 2)
                <div style="background:var(--surface-raised);border:1px solid var(--border);
                            border-radius:16px;padding:20px;">
                    <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:16px;">
                        Select Items to Return
                    </div>

                    @if ($linkedSale)
                        {{-- Linked sale header --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;
                                    padding:10px 14px;border-radius:8px;background:var(--accent-dim);
                                    border:1px solid var(--accent);margin-bottom:16px;">
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--accent);">
                                    {{ $linkedSale->sale_number }}
                                </div>
                                <div style="font-size:11px;color:var(--text-dim);margin-top:1px;">
                                    {{ $linkedSale->customer_name ?? 'Walk-in' }}
                                    · {{ $linkedSale->sale_date->format('d M Y') }}
                                    · {{ number_format($linkedSale->total) }} RWF
                                </div>
                            </div>
                            <button type="button"
                                    wire:click="changeSale"
                                    style="font-size:11px;font-weight:600;color:var(--text-dim);
                                           background:transparent;border:none;cursor:pointer;
                                           padding:4px 8px;border-radius:6px;">
                                Change
                            </button>
                        </div>

                        @foreach ($linkedSale->items as $saleItem)
                            @php
                                $selectedIndex = collect($items)->search(
                                    fn($i) => ($i['original_sale_item_id'] ?? null) == $saleItem->id
                                );
                                $isSelected = $selectedIndex !== false;
                                $item = $isSelected ? $items[$selectedIndex] : null;
                            @endphp

                            <div style="border-radius:12px;border:1.5px solid {{ $isSelected ? 'var(--accent)' : 'var(--border)' }};
                                        background:{{ $isSelected ? 'var(--accent-dim)' : 'var(--surface)' }};
                                        padding:14px 16px;margin-bottom:8px;transition:all 0.15s;cursor:pointer;"
                                 wire:click="toggleItem({{ $saleItem->id }})">

                                <div style="display:flex;align-items:center;justify-content:space-between;">
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:20px;height:20px;border-radius:5px;
                                                    border:2px solid {{ $isSelected ? 'var(--accent)' : 'var(--border)' }};
                                                    background:{{ $isSelected ? 'var(--accent)' : 'transparent' }};
                                                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            @if ($isSelected)
                                                <svg style="width:11px;height:11px;color:white;" fill="none"
                                                     stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div style="font-size:13px;font-weight:600;color:var(--text);">
                                                {{ $saleItem->product->name ?? 'Unknown' }}
                                            </div>
                                            <div style="font-size:11px;color:var(--text-dim);margin-top:1px;">
                                                Sold: {{ $saleItem->quantity_sold }} ×
                                                {{ number_format($saleItem->unit_price ?? 0) }} RWF
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($isSelected)
                                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);
                                                display:grid;grid-template-columns:1fr 1fr;gap:10px;"
                                         @click.stop>
                                        <div>
                                            <div style="font-size:11px;font-weight:600;color:var(--text-dim);
                                                        margin-bottom:5px;text-transform:uppercase;letter-spacing:0.4px;">
                                                Qty Returned
                                            </div>
                                            <input type="number"
                                                   wire:model.live="items.{{ $selectedIndex }}.quantity_returned"
                                                   min="1"
                                                   max="{{ $saleItem->quantity_sold }}"
                                                   style="width:100%;padding:8px 10px;border-radius:8px;font-size:14px;
                                                          font-weight:700;font-family:var(--font-mono);text-align:right;
                                                          background:var(--surface);border:1.5px solid var(--border);
                                                          color:var(--text);box-sizing:border-box;"
                                                   @click.stop>
                                        </div>
                                        <div>
                                            <div style="font-size:11px;font-weight:600;color:var(--red);
                                                        margin-bottom:5px;text-transform:uppercase;letter-spacing:0.4px;">
                                                Qty Damaged
                                            </div>
                                            <input type="number"
                                                   wire:model.live="items.{{ $selectedIndex }}.quantity_damaged"
                                                   min="0"
                                                   max="{{ $item['quantity_returned'] ?? $saleItem->quantity_sold }}"
                                                   style="width:100%;padding:8px 10px;border-radius:8px;font-size:14px;
                                                          font-weight:700;font-family:var(--font-mono);text-align:right;
                                                          background:var(--surface);border:1.5px solid var(--red);
                                                          color:var(--red);box-sizing:border-box;"
                                                   @click.stop>
                                        </div>
                                        <div style="grid-column:span 2;">
                                            <input type="text"
                                                   wire:model="items.{{ $selectedIndex }}.condition_notes"
                                                   placeholder="Condition notes (optional)..."
                                                   style="width:100%;padding:7px 10px;border-radius:8px;font-size:12px;
                                                          background:var(--surface);border:1px solid var(--border);
                                                          color:var(--text);box-sizing:border-box;"
                                                   @click.stop>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            @endif

            {{-- ============================================================
                 STEP 3 — Return Details
            ============================================================ --}}
            @if ($currentStep === 3)
                <div style="background:var(--surface-raised);border:1px solid var(--border);
                            border-radius:16px;padding:20px;">
                    <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:16px;">
                        Return Details
                    </div>

                    {{-- Customer (optional) --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:11px;font-weight:700;color:var(--text-dim);
                                    text-transform:uppercase;letter-spacing:0.6px;margin-bottom:10px;">
                            Customer Info (optional)
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                            <div>
                                <div style="font-size:11px;font-weight:600;color:var(--text-dim);margin-bottom:5px;">Name</div>
                                <input type="text"
                                       wire:model="customerName"
                                       placeholder="Customer name..."
                                       style="width:100%;padding:8px 12px;border-radius:8px;font-size:13px;
                                              background:var(--surface);border:1.5px solid var(--border);
                                              color:var(--text);box-sizing:border-box;transition:border-color 0.15s;"
                                       onfocus="this.style.borderColor='var(--accent)';"
                                       onblur="this.style.borderColor='var(--border)';">
                            </div>
                            <div>
                                <div style="font-size:11px;font-weight:600;color:var(--text-dim);margin-bottom:5px;">Phone</div>
                                <input type="text"
                                       wire:model="customerPhone"
                                       placeholder="Phone number..."
                                       style="width:100%;padding:8px 12px;border-radius:8px;font-size:13px;
                                              background:var(--surface);border:1.5px solid var(--border);
                                              color:var(--text);box-sizing:border-box;transition:border-color 0.15s;"
                                       onfocus="this.style.borderColor='var(--accent)';"
                                       onblur="this.style.borderColor='var(--border)';">
                            </div>
                        </div>
                    </div>

                    {{-- Return type --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:11px;font-weight:700;color:var(--text-dim);
                                    text-transform:uppercase;letter-spacing:0.6px;margin-bottom:10px;">
                            Return Type
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                            <button type="button"
                                    wire:click="$set('isExchange', false)"
                                    style="padding:16px;border-radius:12px;border:2px solid {{ !$isExchange ? 'var(--green)' : 'var(--border)' }};
                                           background:{{ !$isExchange ? 'var(--green-dim)' : 'var(--surface)' }};
                                           cursor:pointer;text-align:center;transition:all 0.15s;">
                                <div style="font-size:24px;margin-bottom:6px;">💵</div>
                                <div style="font-size:13px;font-weight:700;
                                            color:{{ !$isExchange ? 'var(--green)' : 'var(--text-dim)' }};">Refund</div>
                                <div style="font-size:11px;color:var(--text-faint);margin-top:2px;">
                                    Return money to customer
                                </div>
                            </button>

                            <button type="button"
                                    wire:click="$set('isExchange', true)"
                                    style="padding:16px;border-radius:12px;border:2px solid {{ $isExchange ? 'var(--accent)' : 'var(--border)' }};
                                           background:{{ $isExchange ? 'var(--accent-dim)' : 'var(--surface)' }};
                                           cursor:pointer;text-align:center;transition:all 0.15s;">
                                <div style="font-size:24px;margin-bottom:6px;">🔄</div>
                                <div style="font-size:13px;font-weight:700;
                                            color:{{ $isExchange ? 'var(--accent)' : 'var(--text-dim)' }};">Exchange</div>
                                <div style="font-size:11px;color:var(--text-faint);margin-top:2px;">
                                    Replace with another item
                                </div>
                            </button>
                        </div>

                        @if (!$isExchange)
                            <div style="margin-top:14px;">
                                <div style="font-size:11px;font-weight:600;color:var(--text-dim);
                                            margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                                    Refund Method
                                </div>
                                <div style="display:grid;grid-template-columns:repeat(4, 1fr);
                                            border-radius:8px;overflow:hidden;border:1px solid var(--border);">
                                    @foreach (['cash' => 'Cash', 'card' => 'Card', 'mobile_money' => 'MoMo', 'store_credit' => 'Credit'] as $val => $lbl)
                                        <button type="button"
                                                wire:click="$set('refundMethod', '{{ $val }}')"
                                                style="padding:7px 4px;font-size:11px;font-weight:600;border:none;
                                                       cursor:pointer;font-family:var(--font);
                                                       {{ $refundMethod === $val
                                                           ? 'background:var(--accent);color:#fff;'
                                                           : 'background:var(--surface);color:var(--text-dim);' }}
                                                       {{ !$loop->last ? 'border-right:1px solid var(--border);' : '' }}">
                                            {{ $lbl }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Return reason --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:11px;font-weight:600;color:var(--text-dim);
                                    margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                            Return Reason
                        </div>
                        <select wire:model="reason"
                                style="width:100%;padding:9px 12px;border-radius:8px;font-size:13px;
                                       background:var(--surface);border:1.5px solid var(--border);
                                       color:var(--text);font-family:var(--font);cursor:pointer;
                                       transition:border-color 0.15s;"
                                onfocus="this.style.borderColor='var(--accent)';"
                                onblur="this.style.borderColor='var(--border)';">
                            @foreach ($returnReasons as $reasonCase)
                                <option value="{{ $reasonCase->value }}">{{ $reasonCase->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <div style="font-size:11px;font-weight:600;color:var(--text-dim);
                                    margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">
                            Notes (optional)
                        </div>
                        <textarea wire:model="notes"
                                  rows="3"
                                  placeholder="Any additional notes..."
                                  style="width:100%;padding:9px 12px;border-radius:8px;font-size:13px;
                                         background:var(--surface);border:1.5px solid var(--border);
                                         color:var(--text);font-family:var(--font);resize:vertical;
                                         box-sizing:border-box;transition:border-color 0.15s;"
                                  onfocus="this.style.borderColor='var(--accent)';"
                                  onblur="this.style.borderColor='var(--border)';"></textarea>
                    </div>
                </div>
            @endif

            {{-- Navigation Buttons --}}
            <div style="display:flex;justify-content:space-between;margin-top:20px;">
                @if ($currentStep > 1)
                    <button wire:click="goToStep({{ $currentStep - 1 }})"
                            style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;
                                   border-radius:8px;font-size:13px;font-weight:600;
                                   border:1.5px solid var(--border);background:transparent;
                                   color:var(--text-dim);cursor:pointer;font-family:var(--font);">
                        ← Back
                    </button>
                @else
                    <div></div>
                @endif

                @if ($currentStep < 3)
                    <button wire:click="goToStep({{ $currentStep + 1 }})"
                            style="display:inline-flex;align-items:center;gap:6px;padding:9px 20px;
                                   border-radius:8px;font-size:13px;font-weight:700;
                                   background:var(--accent);color:white;border:none;
                                   cursor:pointer;font-family:var(--font);">
                        Continue →
                    </button>
                @endif
            </div>

        </div>{{-- end main column --}}

        {{-- ============================================================
             SUMMARY CARD (sticky right column)
        ============================================================ --}}
        <div class="pr-summary" style="position:sticky;top:24px;background:var(--surface-raised);
                    border:1px solid var(--border);border-radius:16px;overflow:hidden;">

            <div style="padding:14px 16px;border-bottom:1px solid var(--border);background:var(--surface);">
                <div style="font-size:11px;font-weight:700;color:var(--text-dim);
                            text-transform:uppercase;letter-spacing:0.7px;">Return Summary</div>
            </div>

            <div style="padding:16px;">

                @if ($linkedSale)
                    <div style="padding:8px 10px;border-radius:8px;background:var(--accent-dim);
                                border:1px solid var(--accent);margin-bottom:12px;">
                        <div style="font-size:12px;font-weight:700;color:var(--accent);">
                            {{ $linkedSale->sale_number }}
                        </div>
                        <div style="font-size:11px;color:var(--text-dim);margin-top:1px;">
                            {{ $linkedSale->sale_date->format('d M Y') }}
                        </div>
                    </div>
                @else
                    <div style="padding:8px 10px;border-radius:8px;background:var(--amber-dim);
                                border:1px solid var(--amber);margin-bottom:12px;font-size:12px;
                                color:var(--amber);font-weight:600;">
                        No sale selected yet
                    </div>
                @endif

                @php
                    $sumItems   = count($items);
                    $sumQty     = collect($items)->sum('quantity_returned');
                    $sumDamaged = collect($items)->sum('quantity_damaged');
                    $sumGood    = $sumQty - $sumDamaged;
                    $sumRefund  = 0;
                    if (!$isExchange) {
                        foreach ($items as $itm) {
                            $sumRefund += ($itm['unit_price'] ?? 0) * ($itm['quantity_returned'] ?? 0);
                        }
                    }
                @endphp

                @if ($sumItems > 0)
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
                        <div style="padding:8px;border-radius:8px;background:var(--surface);
                                    border:1px solid var(--border);text-align:center;">
                            <div style="font-size:18px;font-weight:800;color:var(--text);
                                        font-family:var(--font-mono);">{{ $sumQty }}</div>
                            <div style="font-size:10px;color:var(--text-dim);">Items</div>
                        </div>
                        <div style="padding:8px;border-radius:8px;background:var(--surface);
                                    border:1px solid var(--border);text-align:center;">
                            <div style="font-size:18px;font-weight:800;color:var(--red);
                                        font-family:var(--font-mono);">{{ $sumDamaged }}</div>
                            <div style="font-size:10px;color:var(--text-dim);">Damaged</div>
                        </div>
                    </div>

                    <div style="font-size:11px;margin-bottom:12px;">
                        @if ($sumGood > 0)
                            <div style="color:var(--green);margin-bottom:3px;">
                                ✓ {{ $sumGood }} item(s) return to stock
                            </div>
                        @endif
                        @if ($sumDamaged > 0)
                            <div style="color:var(--red);">
                                ✗ {{ $sumDamaged }} item(s) flagged as damaged
                            </div>
                        @endif
                    </div>

                    @if (!$isExchange && $sumRefund > 0)
                        <div style="padding:10px 12px;border-radius:8px;background:var(--green-dim);
                                    border:1px solid var(--green);margin-bottom:12px;">
                            <div style="font-size:10px;font-weight:600;color:var(--text-dim);
                                        text-transform:uppercase;letter-spacing:0.5px;">Est. Refund</div>
                            <div style="font-size:20px;font-weight:800;color:var(--green);
                                        font-family:var(--font-mono);margin-top:2px;">
                                {{ number_format($sumRefund) }} RWF
                            </div>
                            @if ($sumRefund > 50000)
                                <div style="font-size:10px;color:var(--amber);margin-top:4px;font-weight:600;">
                                    ⚠ Large refund — requires owner approval
                                </div>
                            @endif
                        </div>
                    @endif

                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;">
                        <span style="font-size:11px;padding:3px 8px;border-radius:20px;font-weight:600;
                                     {{ $isExchange
                                         ? 'background:var(--accent-dim);color:var(--accent);'
                                         : 'background:var(--green-dim);color:var(--green);' }}">
                            {{ $isExchange ? '🔄 Exchange' : '💵 Refund' }}
                        </span>
                        @if (!$isExchange)
                            <span style="font-size:11px;color:var(--text-dim);">
                                via {{ ucwords(str_replace('_', ' ', $refundMethod)) }}
                            </span>
                        @endif
                    </div>
                @endif

                @if ($currentStep === 3)
                    @if (!$showConfirmation)
                        <button wire:click="confirmSubmit"
                                style="width:100%;padding:11px;border-radius:10px;font-size:13px;
                                       font-weight:700;border:none;cursor:pointer;font-family:var(--font);
                                       background:var(--accent);color:white;transition:opacity 0.15s;">
                            Review & Submit
                        </button>
                    @else
                        <div style="padding:10px 12px;border-radius:8px;background:var(--amber-dim);
                                    border:1px solid var(--amber);margin-bottom:10px;">
                            <div style="font-size:12px;font-weight:700;color:var(--amber);margin-bottom:4px;">
                                Confirm this return?
                            </div>
                            <div style="font-size:11px;color:var(--text-dim);">
                                {{ $isExchange ? 'Exchange' : 'Refund' }} ·
                                {{ $sumQty }} item(s)
                                @if (!$isExchange && $sumRefund > 0)
                                    · {{ number_format($sumRefund) }} RWF
                                @endif
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <button wire:click="cancelSubmit"
                                    style="padding:9px;border-radius:8px;font-size:12px;font-weight:600;
                                           border:1.5px solid var(--border);background:transparent;
                                           color:var(--text-dim);cursor:pointer;font-family:var(--font);">
                                Cancel
                            </button>
                            <button wire:click="submitReturn"
                                    wire:loading.attr="disabled"
                                    style="padding:9px;border-radius:8px;font-size:12px;font-weight:700;
                                           border:none;background:var(--green);color:white;
                                           cursor:pointer;font-family:var(--font);">
                                <span wire:loading.remove wire:target="submitReturn">Confirm</span>
                                <span wire:loading wire:target="submitReturn" style="display:none;">Processing…</span>
                            </button>
                        </div>
                    @endif
                @endif

            </div>
        </div>

    </div>{{-- end grid --}}

    @endif{{-- end session gate --}}

    <style>
    @media (max-width: 900px) {
        .pr-grid { grid-template-columns: 1fr !important; }
        .pr-summary { position: static !important; }
    }
    </style>

</div>
