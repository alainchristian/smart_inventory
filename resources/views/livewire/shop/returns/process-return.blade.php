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
                                                : 'background:var(--surface2);color:var(--text-dim);border:1.5px solid var(--border);') }}">
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
                <div style="background:var(--surface2);border:1px solid var(--border);
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
                                    background:var(--surface2);overflow:hidden;
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
                        <span style="font-size:11px;color:var(--text-dim);">or search above</span>
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
                <div style="background:var(--surface2);border:1px solid var(--border);
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

                                // Box-level display for unselected items
                                $_ipb = max(1, (int) ($saleItem->product->items_per_box ?? 1));
                                if ($saleItem->is_full_box) {
                                    $_bsold  = max(1, (int) round($saleItem->quantity_sold / $_ipb));
                                    $_bprice = $saleItem->actual_unit_price ?? 0;
                                    $_iprice = $_ipb > 0 ? (int) round($_bprice / $_ipb) : $_bprice;
                                } else {
                                    $_iprice = $saleItem->actual_unit_price ?? 0;
                                    $_bsold  = max(1, (int) round($saleItem->quantity_sold / $_ipb));
                                    $_bprice = $_iprice * $_ipb;
                                }
                                $_isold = $saleItem->quantity_sold;
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
                                                {{ $_bsold }} {{ $_bsold === 1 ? 'box' : 'boxes' }} sold
                                                · <span style="font-family:var(--mono);font-weight:600;">{{ number_format($_bprice) }} RWF/box</span>
                                                · <span style="opacity:0.7;">{{ number_format($_iprice) }} RWF/item ({{ $_ipb }} items/box)</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Right: total sale value --}}
                                    @if (!$isSelected)
                                        <div style="text-align:right;flex-shrink:0;">
                                            <div style="font-size:12px;font-weight:700;font-family:var(--mono);color:var(--text-dim);">
                                                {{ number_format($_bprice * $_bsold) }}
                                                <span style="font-size:10px;font-weight:400;">RWF</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if ($isSelected)
                                    @php
                                        $selType      = $item['return_type']  ?? 'box';
                                        $selCond      = $item['condition']    ?? 'good';
                                        $selBoxesSold = $item['boxes_sold']   ?? $_bsold;
                                        $selItemsSold = $item['items_sold']   ?? $_isold;
                                        $selQtyRet    = $item['qty_returned'] ?? ($selType === 'box' ? $selBoxesSold : $selItemsSold);
                                        $selQtyDmg    = $item['qty_damaged']  ?? 0;
                                        $selQtyGood   = $selQtyRet - $selQtyDmg;
                                        $selBoxPrice  = $item['box_price']    ?? $_bprice;
                                        $selItemPrice = $item['item_price']   ?? $_iprice;
                                        $selIpb       = $item['items_per_box'] ?? $_ipb;
                                        $selMaxQty    = $selType === 'box' ? $selBoxesSold : $selItemsSold;
                                        $selUnitLabel = $selType === 'box' ? 'box' : 'item';
                                        $selUnitPrice = $selType === 'box' ? $selBoxPrice : $selItemPrice;
                                        $selLineRefund = $selUnitPrice * $selQtyRet;
                                        $selItemsCount = $selType === 'box' ? $selQtyRet * $selIpb : $selQtyRet;
                                        $selGoodItems  = $selType === 'box' ? $selQtyGood * $selIpb : $selQtyGood;
                                        $selDmgItems   = $selType === 'box' ? $selQtyDmg * $selIpb : $selQtyDmg;
                                    @endphp

                                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);"
                                         @click.stop>

                                        {{-- Row 1: Return type toggle --}}
                                        <div style="font-size:10px;font-weight:700;color:var(--text-dim);
                                                    text-transform:uppercase;letter-spacing:0.5px;margin-bottom:5px;">
                                            What is being returned?
                                        </div>
                                        <div style="display:flex;gap:6px;margin-bottom:14px;">
                                            <button wire:click="setReturnType({{ $selectedIndex }}, 'box')"
                                                    style="flex:1;padding:7px 10px;border-radius:8px;font-size:12px;font-weight:600;
                                                           border:1.5px solid {{ $selType === 'box' ? 'var(--accent)' : 'var(--border)' }};
                                                           background:{{ $selType === 'box' ? 'var(--accent)' : 'var(--surface)' }};
                                                           color:{{ $selType === 'box' ? 'white' : 'var(--text-dim)' }};cursor:pointer;">
                                                Full Box(es)
                                            </button>
                                            <button wire:click="setReturnType({{ $selectedIndex }}, 'item')"
                                                    style="flex:1;padding:7px 10px;border-radius:8px;font-size:12px;font-weight:600;
                                                           border:1.5px solid {{ $selType === 'item' ? 'var(--accent)' : 'var(--border)' }};
                                                           background:{{ $selType === 'item' ? 'var(--accent)' : 'var(--surface)' }};
                                                           color:{{ $selType === 'item' ? 'white' : 'var(--text-dim)' }};cursor:pointer;">
                                                Individual Items
                                            </button>
                                        </div>

                                        {{-- Row 2: Quantity --}}
                                        <div style="font-size:10px;font-weight:700;color:var(--text-dim);
                                                    text-transform:uppercase;letter-spacing:0.5px;margin-bottom:5px;">
                                            How many {{ $selType === 'box' ? 'boxes' : 'items' }}?
                                            <span style="font-weight:400;opacity:0.7;">(max {{ $selMaxQty }})</span>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                                            <input type="number"
                                                   wire:model.live="items.{{ $selectedIndex }}.qty_returned"
                                                   min="1" max="{{ $selMaxQty }}"
                                                   style="width:120px;padding:8px 10px;border-radius:8px;font-size:22px;
                                                          font-weight:800;font-family:var(--mono);text-align:center;
                                                          background:var(--surface);border:1.5px solid var(--accent);
                                                          color:var(--accent);box-sizing:border-box;"
                                                   @click.stop>
                                            <div style="font-size:12px;color:var(--text-dim);">
                                                <div style="font-family:var(--mono);font-weight:600;font-size:14px;color:var(--text);">
                                                    {{ number_format($selUnitPrice) }} RWF/{{ $selUnitLabel }}
                                                </div>
                                                @if ($selType === 'box')
                                                    <div>= {{ number_format($selItemsCount) }} items total</div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Row 3: Condition --}}
                                        <div style="font-size:10px;font-weight:700;color:var(--text-dim);
                                                    text-transform:uppercase;letter-spacing:0.5px;margin-bottom:5px;">
                                            Condition of returned {{ $selType === 'box' ? 'boxes' : 'items' }}
                                        </div>
                                        <div style="display:flex;gap:6px;margin-bottom:10px;">
                                            <button wire:click="setCondition({{ $selectedIndex }}, 'good')"
                                                    style="flex:1;padding:8px 6px;border-radius:8px;font-size:11px;font-weight:600;
                                                           border:1.5px solid {{ $selCond === 'good' ? 'var(--green)' : 'var(--border)' }};
                                                           background:{{ $selCond === 'good' ? 'var(--green-dim)' : 'var(--surface)' }};
                                                           color:{{ $selCond === 'good' ? 'var(--green)' : 'var(--text-dim)' }};cursor:pointer;
                                                           text-align:center;line-height:1.3;">
                                                ✓ Good Condition<br>
                                                <span style="font-size:9px;font-weight:400;opacity:0.8;">Back to stock</span>
                                            </button>
                                            <button wire:click="setCondition({{ $selectedIndex }}, 'partially_damaged')"
                                                    style="flex:1;padding:8px 6px;border-radius:8px;font-size:11px;font-weight:600;
                                                           border:1.5px solid {{ $selCond === 'partially_damaged' ? 'var(--amber)' : 'var(--border)' }};
                                                           background:{{ $selCond === 'partially_damaged' ? 'var(--amber-dim)' : 'var(--surface)' }};
                                                           color:{{ $selCond === 'partially_damaged' ? 'var(--amber)' : 'var(--text-dim)' }};cursor:pointer;
                                                           text-align:center;line-height:1.3;">
                                                ⚠ Partially Damaged<br>
                                                <span style="font-size:9px;font-weight:400;opacity:0.8;">Some go to damaged goods</span>
                                            </button>
                                            <button wire:click="setCondition({{ $selectedIndex }}, 'fully_damaged')"
                                                    style="flex:1;padding:8px 6px;border-radius:8px;font-size:11px;font-weight:600;
                                                           border:1.5px solid {{ $selCond === 'fully_damaged' ? 'var(--red)' : 'var(--border)' }};
                                                           background:{{ $selCond === 'fully_damaged' ? 'var(--red-dim)' : 'var(--surface)' }};
                                                           color:{{ $selCond === 'fully_damaged' ? 'var(--red)' : 'var(--text-dim)' }};cursor:pointer;
                                                           text-align:center;line-height:1.3;">
                                                ✕ Fully Damaged<br>
                                                <span style="font-size:9px;font-weight:400;opacity:0.8;">All go to damaged goods</span>
                                            </button>
                                        </div>

                                        {{-- Partial split input (only when partially damaged) --}}
                                        @if ($selCond === 'partially_damaged')
                                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;
                                                        padding:10px;border-radius:8px;background:var(--amber-dim);border:1px solid var(--amber);">
                                                <div style="text-align:center;">
                                                    <div style="font-size:10px;font-weight:700;color:var(--green);
                                                                text-transform:uppercase;letter-spacing:0.4px;margin-bottom:4px;">
                                                        Good — back to stock
                                                    </div>
                                                    <div style="font-size:24px;font-weight:800;font-family:var(--mono);color:var(--green);">
                                                        {{ $selQtyGood }}
                                                    </div>
                                                    <div style="font-size:10px;color:var(--text-dim);">{{ $selUnitLabel }}(s)</div>
                                                </div>
                                                <div style="text-align:center;">
                                                    <div style="font-size:10px;font-weight:700;color:var(--red);
                                                                text-transform:uppercase;letter-spacing:0.4px;margin-bottom:4px;">
                                                        Damaged — damaged goods
                                                    </div>
                                                    <input type="number"
                                                           wire:model.live="items.{{ $selectedIndex }}.qty_damaged"
                                                           min="1" max="{{ $selQtyRet - 1 }}"
                                                           style="width:80px;padding:6px 8px;border-radius:8px;font-size:22px;
                                                                  font-weight:800;font-family:var(--mono);text-align:center;
                                                                  background:var(--surface);border:1.5px solid var(--red);
                                                                  color:var(--red);box-sizing:border-box;"
                                                           @click.stop>
                                                    <div style="font-size:10px;color:var(--text-dim);margin-top:2px;">{{ $selUnitLabel }}(s)</div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Outcome summary strip --}}
                                        <div style="display:flex;gap:6px;margin-bottom:10px;">
                                            @if ($selQtyGood > 0)
                                                <div style="flex:1;padding:6px 8px;border-radius:6px;background:var(--green-dim);
                                                            border:1px solid var(--green);text-align:center;">
                                                    <div style="font-size:13px;font-weight:800;font-family:var(--mono);color:var(--green);">
                                                        {{ $selQtyGood }}
                                                    </div>
                                                    <div style="font-size:9px;color:var(--green);font-weight:600;">
                                                        {{ $selUnitLabel }}(s) → Stock
                                                        @if ($selType === 'box')
                                                            <span style="opacity:0.7;">({{ number_format($selGoodItems) }} items)</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($selQtyDmg > 0)
                                                <div style="flex:1;padding:6px 8px;border-radius:6px;background:var(--red-dim);
                                                            border:1px solid var(--red);text-align:center;">
                                                    <div style="font-size:13px;font-weight:800;font-family:var(--mono);color:var(--red);">
                                                        {{ $selQtyDmg }}
                                                    </div>
                                                    <div style="font-size:9px;color:var(--red);font-weight:600;">
                                                        {{ $selUnitLabel }}(s) → Damaged Goods
                                                        @if ($selType === 'box')
                                                            <span style="opacity:0.7;">({{ number_format($selDmgItems) }} items)</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Refund line --}}
                                        <div style="display:flex;align-items:center;justify-content:space-between;
                                                    padding:8px 12px;border-radius:8px;background:var(--green-dim);
                                                    border:1px solid var(--green);margin-bottom:10px;">
                                            <span style="font-size:11px;color:var(--green);font-weight:600;">
                                                Line refund: {{ $selQtyRet }} × {{ number_format($selUnitPrice) }} RWF/{{ $selUnitLabel }}
                                            </span>
                                            <span style="font-size:13px;font-weight:800;font-family:var(--mono);color:var(--green);">
                                                {{ number_format($selLineRefund) }} RWF
                                            </span>
                                        </div>

                                        {{-- Damage notes (required if damaged) --}}
                                        <textarea wire:model="items.{{ $selectedIndex }}.condition_notes"
                                                  rows="2"
                                                  placeholder="{{ $selCond === 'good' ? 'Notes (optional)...' : 'Describe the damage (required)...' }}"
                                                  style="width:100%;padding:7px 10px;border-radius:8px;font-size:12px;resize:none;
                                                         background:var(--surface);color:var(--text);box-sizing:border-box;
                                                         border:1px solid {{ in_array($selCond, ['fully_damaged','partially_damaged']) ? 'var(--amber)' : 'var(--border)' }};"
                                                  @click.stop></textarea>
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
                <div style="background:var(--surface2);border:1px solid var(--border);
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
                                <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">
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
                                <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">
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
        <div class="pr-summary" style="position:sticky;top:24px;background:var(--surface2);
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
                    $sumQty     = collect($items)->sum('qty_returned');
                    $sumDamaged = collect($items)->sum('qty_damaged');
                    $sumGood    = $sumQty - $sumDamaged;
                    $sumRefund  = 0;
                    if (!$isExchange) {
                        foreach ($items as $itm) {
                            $retType = $itm['return_type'] ?? 'box';
                            if ($retType === 'box') {
                                $sumRefund += ($itm['box_price'] ?? 0) * ($itm['qty_returned'] ?? 0);
                            } else {
                                $sumRefund += ($itm['item_price'] ?? 0) * ($itm['qty_returned'] ?? 0);
                            }
                        }
                    }
                @endphp

                @if ($sumItems > 0)
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
                        <div style="padding:8px;border-radius:8px;background:var(--surface);
                                    border:1px solid var(--border);text-align:center;">
                            <div style="font-size:18px;font-weight:800;color:var(--text);
                                        font-family:var(--mono);">{{ $sumQty }}</div>
                            <div style="font-size:10px;color:var(--text-dim);">Qty Returned</div>
                        </div>
                        <div style="padding:8px;border-radius:8px;background:var(--surface);
                                    border:1px solid var(--border);text-align:center;">
                            <div style="font-size:18px;font-weight:800;color:var(--red);
                                        font-family:var(--mono);">{{ $sumDamaged }}</div>
                            <div style="font-size:10px;color:var(--text-dim);">Qty Damaged</div>
                        </div>
                    </div>

                    <div style="font-size:11px;margin-bottom:12px;">
                        @if ($sumGood > 0)
                            <div style="color:var(--green);margin-bottom:3px;">
                                ✓ {{ $sumGood }} {{ $sumGood === 1 ? 'box' : 'boxes' }} return to stock
                            </div>
                        @endif
                        @if ($sumDamaged > 0)
                            <div style="color:var(--red);">
                                ✗ {{ $sumDamaged }} {{ $sumDamaged === 1 ? 'box' : 'boxes' }} flagged as damaged
                            </div>
                        @endif
                    </div>

                    @if (!$isExchange && $sumRefund > 0)
                        <div style="padding:10px 12px;border-radius:8px;background:var(--green-dim);
                                    border:1px solid var(--green);margin-bottom:12px;">
                            <div style="font-size:10px;font-weight:600;color:var(--text-dim);
                                        text-transform:uppercase;letter-spacing:0.5px;">Est. Refund</div>
                            <div style="font-size:20px;font-weight:800;color:var(--green);
                                        font-family:var(--mono);margin-top:2px;">
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
                                {{ $sumQty }} {{ $sumQty === 1 ? 'box' : 'boxes' }}
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
