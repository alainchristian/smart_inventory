<div class="pr-page" style="font-family:var(--font);color:var(--text)">
<style>
.pr-page { padding-bottom:80px }

/* ── Header ── */
.pr-hdr     { display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;gap:16px;flex-wrap:wrap }
.pr-hdr-ttl { font-size:22px;font-weight:800;letter-spacing:-.4px;margin:0 0 3px }
.pr-hdr-sub { font-size:13px;color:var(--text-dim);margin:0 }
.pr-back-btn { display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;border:1px solid var(--border);background:transparent;color:var(--text-dim);text-decoration:none;transition:all var(--tr) }
.pr-back-btn:hover { color:var(--accent);border-color:var(--accent) }

/* ── Layout ── */
.pr-grid    { display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start }
.pr-sidebar { position:sticky;top:24px;display:flex;flex-direction:column;gap:16px }

/* ── Step bar ── */
.pr-steps   { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);
              padding:16px 24px;margin-bottom:20px;display:flex;align-items:center }
.pr-step    { display:flex;align-items:center;gap:9px;flex-shrink:0 }
.pr-step-n  { width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;
              font-size:11px;font-weight:800;flex-shrink:0;transition:all var(--tr) }
.pr-step-n.done    { background:var(--text);color:var(--surface) }
.pr-step-n.active  { background:var(--accent);color:#fff }
.pr-step-n.pending { background:transparent;color:var(--text-dim);border:1.5px solid var(--border) }
.pr-step-lbl  { font-size:12px;font-weight:700 }
.pr-step-hint { font-size:10px;color:var(--text-dim);margin-top:1px }
.pr-step-line { flex:1;height:1px;background:var(--border);margin:0 14px }
.pr-step-line.done { background:var(--text) }

/* ── Cards ── */
.pr-card      { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card);margin-bottom:20px }
.pr-card-head { padding:16px 20px;border-bottom:1px solid var(--border) }
.pr-card-ttl  { font-size:14px;font-weight:700;margin:0 }
.pr-card-sub  { font-size:12px;color:var(--text-dim);margin-top:2px }
.pr-card-body { padding:20px }

/* ── Form ── */
.pr-lbl      { display:block;font-size:10px;font-weight:700;color:var(--text-dim);letter-spacing:.6px;text-transform:uppercase;margin-bottom:7px }
.pr-lbl span { color:var(--red) }
.pr-field    { margin-bottom:16px }
.pr-2col     { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px }
.pr-inp      { width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;background:var(--surface);color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.pr-inp:focus { border-color:var(--accent) }
.pr-inp.ico  { padding-left:38px }
.pr-inp-wrap { position:relative }
.pr-inp-icon { position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--text-dim);pointer-events:none }

/* ── Dropdown ── */
.pr-drop     { margin-top:4px;border-radius:8px;border:1px solid var(--border);background:var(--surface);box-shadow:0 4px 16px rgba(0,0,0,.08);overflow:hidden }
.pr-drop-btn { width:100%;padding:11px 16px;text-align:left;border:none;border-bottom:1px solid var(--border);background:transparent;cursor:pointer;transition:background var(--tr);font-family:var(--font);display:flex;justify-content:space-between;align-items:center }
.pr-drop-btn:hover { background:var(--surface2) }
.pr-drop-btn:last-child { border-bottom:none }

/* ── Buttons ── */
.pr-btn     { display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:700;border:none;cursor:pointer;transition:all var(--tr);font-family:var(--font) }
.pr-btn-pr  { background:var(--accent);color:#fff }
.pr-btn-pr:hover { opacity:.88 }
.pr-btn-gh  { background:transparent;color:var(--text-dim);border:1px solid var(--border) }
.pr-btn-gh:hover { color:var(--text);border-color:var(--text-dim) }
.pr-btn-gr  { background:var(--green);color:#fff;border:none }
.pr-chg-btn { padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;border:1px solid var(--border);background:transparent;color:var(--text-dim);cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.pr-chg-btn:hover { color:var(--text);border-color:var(--text-dim) }

/* ── Sale items ── */
.pr-item          { border-radius:10px;border:1px solid var(--border);background:var(--surface);padding:14px 16px;margin-bottom:8px;transition:border-color var(--tr);cursor:pointer }
.pr-item:hover    { border-color:var(--text-dim) }
.pr-item.selected { border-color:var(--accent);border-width:1.5px }
.pr-chk           { width:18px;height:18px;border-radius:4px;border:1.5px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all var(--tr) }
.pr-chk.on        { background:var(--accent);border-color:var(--accent) }

/* ── Section divider ── */
.pr-sec     { padding-top:16px;margin-top:16px;border-top:1px solid var(--border) }
.pr-sec-lbl { font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim);margin-bottom:10px }

/* ── Segmented control ── */
.pr-seg     { display:flex;border:1px solid var(--border);border-radius:8px;padding:3px;gap:2px }
.pr-seg-btn { flex:1;padding:7px 10px;border:none;border-radius:6px;background:transparent;color:var(--text-dim);font-size:12px;font-weight:600;font-family:var(--font);cursor:pointer;transition:all var(--tr) }
.pr-seg-btn.on { background:var(--accent);color:#fff;font-weight:700 }
.pr-seg-btn:hover:not(.on) { color:var(--text) }

/* ── Qty control ── */
.pr-qty     { display:flex;align-items:center;border:1px solid var(--border);border-radius:8px;overflow:hidden;width:fit-content }
.pr-qty-btn { width:34px;height:40px;border:none;background:transparent;color:var(--text-dim);font-size:16px;cursor:pointer;font-family:var(--font);transition:all var(--tr);flex-shrink:0;display:flex;align-items:center;justify-content:center }
.pr-qty-btn:hover { background:var(--surface2);color:var(--text) }
.pr-qty-in  { width:52px;height:40px;border:none;border-left:1px solid var(--border);border-right:1px solid var(--border);text-align:center;font-size:18px;font-weight:800;font-family:var(--mono);color:var(--text);background:var(--surface);outline:none;padding:0 }

/* ── Condition cards ── */
.pr-cond-row  { display:flex;gap:8px }
.pr-cond-card { flex:1;padding:12px 8px;border-radius:8px;border:1.5px solid var(--border);background:var(--surface);cursor:pointer;text-align:center;transition:all var(--tr);font-family:var(--font);display:block }
.pr-cond-card:hover { border-color:var(--text-dim) }
.pr-cond-card.good    { border-color:var(--green) }
.pr-cond-card.partial { border-color:var(--amber) }
.pr-cond-card.damaged { border-color:var(--red) }
.pr-cond-ico  { width:28px;height:28px;border-radius:50%;background:var(--border);display:flex;align-items:center;justify-content:center;margin:0 auto 7px }
.pr-cond-lbl  { font-size:11px;font-weight:700;color:var(--text-dim) }
.pr-cond-card.good    .pr-cond-lbl { color:var(--green) }
.pr-cond-card.partial .pr-cond-lbl { color:var(--amber) }
.pr-cond-card.damaged .pr-cond-lbl { color:var(--red) }
.pr-cond-sub  { font-size:10px;color:var(--text-dim);margin-top:2px }
.pr-cond-card.good    .pr-cond-sub { color:var(--green) }
.pr-cond-card.partial .pr-cond-sub { color:var(--amber) }
.pr-cond-card.damaged .pr-cond-sub { color:var(--red) }

/* ── Partial damage split ── */
.pr-split      { display:grid;grid-template-columns:1fr 1fr;border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:14px }
.pr-split-side { padding:14px;text-align:center }
.pr-split-side + .pr-split-side { border-left:1px solid var(--border) }
.pr-split-lbl  { font-size:9px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px }
.pr-split-num  { font-size:22px;font-weight:800;font-family:var(--mono);color:var(--text);line-height:1 }
.pr-split-unit { font-size:10px;color:var(--text-dim);margin-top:4px;font-weight:600 }
.pr-dmg-inp    { width:64px;height:38px;border-radius:7px;font-size:20px;font-weight:800;font-family:var(--mono);text-align:center;background:var(--surface);border:1.5px solid var(--border);color:var(--text);box-sizing:border-box;outline:none;transition:border-color var(--tr) }
.pr-dmg-inp:focus { border-color:var(--accent) }

/* ── Outcome rows ── */
.pr-outcome   { margin-bottom:12px }
.pr-out-row   { display:flex;align-items:center;gap:7px;padding:5px 0;font-size:12px;font-weight:500;color:var(--text-dim) }
.pr-out-row + .pr-out-row { border-top:1px solid var(--border);padding-top:9px;margin-top:4px }

/* ── Refund calc line ── */
.pr-calc { display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-top:1px solid var(--border);margin-bottom:12px }

/* ── Sidebar cards ── */
.pr-sc { background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-card) }
.pr-sc-head { padding:14px 18px;border-bottom:1px solid var(--border) }
.pr-sc-ttl  { font-size:13px;font-weight:700;margin:0 }
.pr-sc-body { padding:16px 18px }

/* ── Summary data rows ── */
.pr-sale-strip { display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);margin-bottom:14px }
.pr-d-row  { display:flex;align-items:center;justify-content:space-between;padding:7px 0;font-size:12px }
.pr-d-row + .pr-d-row { border-top:1px solid var(--border) }
.pr-d-lbl  { color:var(--text-dim);font-weight:500 }
.pr-d-val  { font-family:var(--mono);font-weight:700;color:var(--text) }

/* ── Refund total ── */
.pr-ref-total { padding:14px 0 4px;border-top:1px solid var(--border);margin-top:4px }
.pr-ref-lbl   { font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-dim);margin-bottom:5px }
.pr-ref-amt   { font-size:28px;font-weight:800;font-family:var(--mono);color:var(--green);letter-spacing:-1px;line-height:1.1 }
.pr-ref-warn  { display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--amber);margin-top:7px }

/* ── Refund method ── */
.pr-mth-lbl { font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);margin:14px 0 8px }
.pr-mth-seg { display:flex;border:1px solid var(--border);border-radius:8px;padding:3px;gap:2px }
.pr-mth-btn { flex:1;padding:6px 4px;font-size:11px;font-weight:600;border:none;border-radius:6px;cursor:pointer;font-family:var(--font);background:transparent;color:var(--text-dim);transition:all var(--tr) }
.pr-mth-btn.on { background:var(--accent);color:#fff;font-weight:700 }
.pr-mth-btn:hover:not(.on) { color:var(--text) }

/* ── Confirm block ── */
.pr-confirm { padding:12px 14px;border-radius:8px;border:1px solid var(--amber);margin-bottom:12px }
.pr-conf-ttl { font-size:13px;font-weight:700;color:var(--amber);margin-bottom:3px }
.pr-conf-sub { font-size:12px;color:var(--text-dim) }

/* ── Alerts ── */
.pr-al-amber { margin-top:12px;padding:10px 14px;border-radius:8px;background:var(--amber-dim);border:1px solid var(--amber);display:flex;align-items:flex-start;gap:8px }
.pr-al-red   { margin-top:12px;padding:10px 14px;border-radius:8px;background:var(--red-dim);border:1px solid var(--red);display:flex;align-items:flex-start;gap:8px }

/* ── Responsive ── */
@media(max-width:900px) { .pr-grid { grid-template-columns:1fr } .pr-sidebar { position:static } }
@media(max-width:640px) { .pr-2col { grid-template-columns:1fr } .pr-cond-row { gap:5px } }
</style>

    @if($sessionBlocked)
        <x-session-gate-blocked
            :reason="$sessionBlockReason"
            :session-date="$blockedSessionDate"
            :session-id="$blockedSessionId"
        />
    @else

    @if(session()->has('error'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:8px;background:var(--red-dim);border:1px solid var(--red);font-size:13px;font-weight:600;color:var(--red)">{{ session('error') }}</div>
    @endif
    @if(session()->has('success'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:8px;background:var(--green-dim);border:1px solid var(--green);font-size:13px;font-weight:600;color:var(--green)">{{ session('success') }}</div>
    @endif

    {{-- Header --}}
    <div class="pr-hdr">
        <div>
            <h1 class="pr-hdr-ttl">Process Return</h1>
            <p class="pr-hdr-sub">{{ $shopName }}</p>
        </div>
        <a href="{{ route('shop.returns.index') }}" class="pr-back-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7l-7 7 7 7"/></svg>
            Back to Returns
        </a>
    </div>

    <div class="pr-grid">

        {{-- ── LEFT COLUMN ── --}}
        <div>

            {{-- Step indicator --}}
            <div class="pr-steps">
                @foreach([[1,'Find Sale','Search by sale or customer'],[2,'Select Items','Choose what to return'],[3,'Return Details','Reason and notes']] as [$sn,$sl,$sh])
                    <div style="display:flex;align-items:center;{{ !$loop->last ? 'flex:1' : '' }}">
                        <div class="pr-step">
                            <div class="pr-step-n {{ $currentStep > $sn ? 'done' : ($currentStep === $sn ? 'active' : 'pending') }}">
                                @if($currentStep > $sn)
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    {{ $sn }}
                                @endif
                            </div>
                            <div>
                                <div class="pr-step-lbl" style="color:{{ $currentStep === $sn ? 'var(--text)' : 'var(--text-dim)' }}">{{ $sl }}</div>
                                <div class="pr-step-hint">{{ $sh }}</div>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div class="pr-step-line {{ $currentStep > $sn ? 'done' : '' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- ── STEP 1 — Find Sale ── --}}
            @if($currentStep === 1)
                <div class="pr-card">
                    <div class="pr-card-head">
                        <div class="pr-card-ttl">Find Sale</div>
                        <div class="pr-card-sub">Browse by period or search by sale number, customer name or phone</div>
                    </div>
                    <div class="pr-card-body">

                        {{-- Period filter pills --}}
                        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px">
                            @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month'] as $key => $label)
                                <button type="button"
                                        wire:click="loadSalesByPeriod('{{ $key }}')"
                                        style="padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;border:1.5px solid {{ $salePeriod === $key && $showQuickSales ? 'var(--accent)' : 'var(--border)' }};background:{{ $salePeriod === $key && $showQuickSales ? 'var(--accent)' : 'transparent' }};color:{{ $salePeriod === $key && $showQuickSales ? '#fff' : 'var(--text-dim)' }};cursor:pointer;font-family:var(--font);transition:all var(--tr)">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Search input --}}
                        <div style="position:relative;margin-bottom:14px">
                            <input type="text"
                                   wire:model.live.debounce.300ms="saleSearch"
                                   wire:focus="$set('showSaleSearchDropdown', true)"
                                   placeholder="Search by sale number, customer name or phone…"
                                   class="pr-inp ico">
                            <svg class="pr-inp-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>

                        {{-- Results list --}}
                        @if(count($saleSearchResults) > 0)
                            @php $isSearch = $showSaleSearchDropdown && strlen($saleSearch) >= 2; @endphp
                            <div style="font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-dim);margin-bottom:8px">
                                @if($isSearch)
                                    {{ count($saleSearchResults) }} result{{ count($saleSearchResults) !== 1 ? 's' : '' }} for "{{ $saleSearch }}"
                                @else
                                    {{ count($saleSearchResults) }} sale{{ count($saleSearchResults) !== 1 ? 's' : '' }} —
                                    {{ ['today'=>'Today','yesterday'=>'Yesterday','this_week'=>'This Week','this_month'=>'This Month'][$salePeriod] ?? '' }}
                                @endif
                            </div>
                            <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden">
                                @foreach($saleSearchResults as $result)
                                    <button type="button" wire:click="selectSale({{ $result['id'] }})"
                                            style="width:100%;padding:12px 16px;text-align:left;border:none;border-bottom:1px solid var(--border);background:transparent;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:12px;font-family:var(--font);transition:background var(--tr)"
                                            onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                        <div style="min-width:0">
                                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
                                                <span style="font-size:13px;font-weight:700;font-family:var(--mono);color:var(--text)">{{ $result['sale_number'] }}</span>
                                                @if($result['customer_name'])
                                                    <span style="font-size:11px;color:var(--text-dim)">· {{ $result['customer_name'] }}</span>
                                                @endif
                                            </div>
                                            <div style="font-size:11px;color:var(--text-dim)">
                                                {{ $result['created_at'] }}
                                                @if($result['sold_by'])
                                                    · by {{ $result['sold_by'] }}
                                                @endif
                                                · {{ $result['items_count'] }} item{{ $result['items_count'] !== 1 ? 's' : '' }}
                                            </div>
                                        </div>
                                        <div style="flex-shrink:0;text-align:right">
                                            <div style="font-size:13px;font-weight:700;font-family:var(--mono);color:var(--text)">{{ number_format($result['total']) }}</div>
                                            <div style="font-size:10px;color:var(--text-dim)">RWF</div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @elseif($showQuickSales)
                            <div style="text-align:center;padding:28px 0;color:var(--text-dim);font-size:13px;border:1px solid var(--border);border-radius:10px">
                                No sales found for this period
                            </div>
                        @endif

                        @if($saleAgeWarning)
                            <div class="pr-al-amber" style="margin-top:14px">
                                <svg style="width:14px;height:14px;color:var(--amber);flex-shrink:0;margin-top:1px" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div>
                                    <div style="font-size:12px;font-weight:700;color:var(--amber)">Sale is over 7 days old</div>
                                    <div style="font-size:11px;color:var(--text-dim);margin-top:2px">Returns older than 7 days may require owner approval.</div>
                                </div>
                            </div>
                        @endif

                        @if($existingReturnWarning)
                            <div class="pr-al-red" style="margin-top:14px">
                                <svg style="width:14px;height:14px;color:var(--red);flex-shrink:0;margin-top:1px" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div style="font-size:12px;color:var(--red)">{{ $existingReturnWarning }}</div>
                            </div>
                        @endif

                    </div>
                </div>
            @endif

            {{-- ── STEP 2 — Select Items ── --}}
            @if($currentStep === 2)
                <div class="pr-card">
                    <div class="pr-card-head">
                        <div class="pr-card-ttl">Select Items to Return</div>
                    </div>
                    <div class="pr-card-body">

                        @if($linkedSale)
                            {{-- Linked sale strip --}}
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:8px;border:1px solid var(--border);margin-bottom:16px">
                                <div>
                                    <div style="font-size:13px;font-weight:700;font-family:var(--mono);color:var(--text)">{{ $linkedSale->sale_number }}</div>
                                    <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                                        {{ $linkedSale->customer_name ?? 'Walk-in' }} · {{ $linkedSale->sale_date->format('d M Y') }} · {{ number_format($linkedSale->total) }} RWF
                                    </div>
                                </div>
                                <button type="button" wire:click="changeSale" class="pr-chg-btn">Change</button>
                            </div>

                            @foreach($linkedSale->items as $saleItem)
                                @php
                                    $selectedIndex = collect($items)->search(fn($i) => ($i['original_sale_item_id'] ?? null) == $saleItem->id);
                                    $isSelected    = $selectedIndex !== false;
                                    $item          = $isSelected ? $items[$selectedIndex] : null;

                                    $_ipb    = max(1, (int)($saleItem->product->items_per_box ?? 1));
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

                                <div class="pr-item {{ $isSelected ? 'selected' : '' }}"
                                     wire:click="toggleItem({{ $saleItem->id }})">

                                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
                                        <div style="display:flex;align-items:center;gap:10px">
                                            <div class="pr-chk {{ $isSelected ? 'on' : '' }}">
                                                @if($isSelected)
                                                    <svg width="10" height="10" fill="none" stroke="#fff" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                @endif
                                            </div>
                                            <div>
                                                <div style="font-size:13px;font-weight:700">{{ $saleItem->product->name ?? 'Unknown' }}</div>
                                                <div style="font-size:11px;color:var(--text-dim);margin-top:2px">
                                                    {{ $_bsold }} {{ $_bsold === 1 ? 'box' : 'boxes' }} sold ·
                                                    <span style="font-family:var(--mono);font-weight:600">{{ number_format($_bprice) }} RWF/box</span>
                                                    · {{ number_format($_iprice) }} RWF/item · {{ $_ipb }} items/box
                                                </div>
                                            </div>
                                        </div>
                                        @if(!$isSelected)
                                            <span style="font-family:var(--mono);font-size:12px;font-weight:700;color:var(--text-dim);flex-shrink:0;white-space:nowrap">{{ number_format($_bprice * $_bsold) }} <span style="font-size:10px;font-weight:400">RWF</span></span>
                                        @endif
                                    </div>

                                    @if($isSelected)
                                        @php
                                            $selType      = $item['return_type']   ?? 'box';
                                            $selCond      = $item['condition']     ?? 'good';
                                            $selBoxesSold = $item['boxes_sold']    ?? $_bsold;
                                            $selItemsSold = $item['items_sold']    ?? $_isold;
                                            $selQtyRet    = $item['qty_returned']  ?? ($selType === 'box' ? $selBoxesSold : $selItemsSold);
                                            $selQtyDmg    = $item['qty_damaged']   ?? 0;
                                            $selQtyGood   = $selQtyRet - $selQtyDmg;
                                            $selBoxPrice  = $item['box_price']     ?? $_bprice;
                                            $selItemPrice = $item['item_price']    ?? $_iprice;
                                            $selIpb       = $item['items_per_box'] ?? $_ipb;
                                            $selMaxQty    = $selType === 'box' ? $selBoxesSold : $selItemsSold;
                                            $selUnitLabel = $selType === 'box' ? 'box' : 'item';
                                            $selUnitPrice = $selType === 'box' ? $selBoxPrice : $selItemPrice;
                                            $selLineRefund = $selUnitPrice * $selQtyRet;
                                            $selGoodItems  = $selType === 'box' ? $selQtyGood * $selIpb : $selQtyGood;
                                            $selDmgItems   = $selType === 'box' ? $selQtyDmg  * $selIpb : $selQtyDmg;
                                        @endphp

                                        <div @click.stop>

                                            {{-- 1. What is being returned? --}}
                                            <div class="pr-sec">
                                                <div class="pr-sec-lbl">What is being returned?</div>
                                                <div class="pr-seg">
                                                    <button class="pr-seg-btn {{ $selType === 'box' ? 'on' : '' }}"
                                                            wire:click="setReturnType({{ $selectedIndex }}, 'box')">Full Box(es)</button>
                                                    <button class="pr-seg-btn {{ $selType === 'item' ? 'on' : '' }}"
                                                            wire:click="setReturnType({{ $selectedIndex }}, 'item')">Individual Items</button>
                                                </div>
                                            </div>

                                            {{-- 2. Quantity --}}
                                            <div class="pr-sec">
                                                <div class="pr-sec-lbl">
                                                    How many {{ $selType === 'box' ? 'boxes' : 'items' }}?
                                                    <span style="font-weight:500;text-transform:none;letter-spacing:0;font-size:10px">(max {{ $selMaxQty }})</span>
                                                </div>
                                                <div style="display:flex;align-items:center;gap:14px">
                                                    <div class="pr-qty">
                                                        <button type="button" class="pr-qty-btn"
                                                                wire:click="$set('items.{{ $selectedIndex }}.qty_returned', max(1, {{ $selQtyRet }} - 1))"
                                                                @click.stop>−</button>
                                                        <input type="number" class="pr-qty-in"
                                                               wire:model.live="items.{{ $selectedIndex }}.qty_returned"
                                                               min="1" max="{{ $selMaxQty }}"
                                                               @click.stop>
                                                        <button type="button" class="pr-qty-btn"
                                                                wire:click="$set('items.{{ $selectedIndex }}.qty_returned', min({{ $selMaxQty }}, {{ $selQtyRet }} + 1))"
                                                                @click.stop>+</button>
                                                    </div>
                                                    <div>
                                                        <div style="font-size:14px;font-weight:700;font-family:var(--mono)">{{ number_format($selUnitPrice) }} RWF<span style="font-size:11px;font-weight:500;color:var(--text-dim)">/{{ $selUnitLabel }}</span></div>
                                                        @if($selType === 'box')
                                                            <div style="font-size:11px;color:var(--text-dim);margin-top:2px">= {{ number_format($selQtyRet * $selIpb) }} items total</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- 3. Condition --}}
                                            <div class="pr-sec">
                                                <div class="pr-sec-lbl">Condition of returned {{ $selType === 'box' ? 'boxes' : 'items' }}</div>
                                                <div class="pr-cond-row">

                                                    <button class="pr-cond-card {{ $selCond === 'good' ? 'good' : '' }}"
                                                            wire:click="setCondition({{ $selectedIndex }}, 'good')">
                                                        <div class="pr-cond-ico">
                                                            <svg width="13" height="13" fill="none" stroke="{{ $selCond === 'good' ? 'var(--green)' : 'var(--text-dim)' }}" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                        </div>
                                                        <div class="pr-cond-lbl">Good Condition</div>
                                                        <div class="pr-cond-sub">Back to stock</div>
                                                    </button>

                                                    <button class="pr-cond-card {{ $selCond === 'partially_damaged' ? 'partial' : '' }}"
                                                            wire:click="setCondition({{ $selectedIndex }}, 'partially_damaged')">
                                                        <div class="pr-cond-ico">
                                                            <svg width="13" height="13" fill="none" stroke="{{ $selCond === 'partially_damaged' ? 'var(--amber)' : 'var(--text-dim)' }}" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                        </div>
                                                        <div class="pr-cond-lbl">Partially Damaged</div>
                                                        <div class="pr-cond-sub">Some go to damaged</div>
                                                    </button>

                                                    <button class="pr-cond-card {{ $selCond === 'fully_damaged' ? 'damaged' : '' }}"
                                                            wire:click="setCondition({{ $selectedIndex }}, 'fully_damaged')">
                                                        <div class="pr-cond-ico">
                                                            <svg width="13" height="13" fill="none" stroke="{{ $selCond === 'fully_damaged' ? 'var(--red)' : 'var(--text-dim)' }}" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </div>
                                                        <div class="pr-cond-lbl">Fully Damaged</div>
                                                        <div class="pr-cond-sub">All to damaged</div>
                                                    </button>

                                                </div>
                                            </div>

                                            {{-- 4. Partial damage split --}}
                                            @if($selCond === 'partially_damaged')
                                                <div class="pr-split" style="margin-top:12px">
                                                    <div class="pr-split-side">
                                                        <div class="pr-split-lbl">Good → Stock</div>
                                                        <div class="pr-split-num">{{ $selQtyGood }}</div>
                                                        <div class="pr-split-unit">{{ $selUnitLabel }}(s)</div>
                                                    </div>
                                                    <div class="pr-split-side">
                                                        <div class="pr-split-lbl">Damaged → Flag</div>
                                                        <input type="number" class="pr-dmg-inp"
                                                               wire:model.live="items.{{ $selectedIndex }}.qty_damaged"
                                                               min="1" max="{{ $selQtyRet - 1 }}"
                                                               @click.stop>
                                                        <div class="pr-split-unit">{{ $selUnitLabel }}(s)</div>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- 5. Outcome --}}
                                            @if($selQtyGood > 0 || $selQtyDmg > 0)
                                                <div class="pr-outcome" style="margin-top:12px">
                                                    @if($selQtyGood > 0)
                                                        <div class="pr-out-row">
                                                            <svg width="11" height="11" fill="none" stroke="var(--green)" viewBox="0 0 24 24" stroke-width="3" style="flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            <span style="color:var(--green);font-weight:600">
                                                                {{ $selQtyGood }} {{ $selUnitLabel }}{{ $selQtyGood !== 1 ? 's' : '' }} returning to stock
                                                                @if($selType === 'box')
                                                                    · {{ number_format($selGoodItems) }} items
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if($selQtyDmg > 0)
                                                        <div class="pr-out-row">
                                                            <svg width="11" height="11" fill="none" stroke="var(--red)" viewBox="0 0 24 24" stroke-width="3" style="flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            <span style="color:var(--red);font-weight:600">
                                                                {{ $selQtyDmg }} {{ $selUnitLabel }}{{ $selQtyDmg !== 1 ? 's' : '' }} flagged as damaged
                                                                @if($selType === 'box')
                                                                    · {{ number_format($selDmgItems) }} items
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- 6. Refund calc --}}
                                            <div class="pr-calc">
                                                <span style="font-size:12px;color:var(--text-dim);font-weight:500">{{ $selQtyRet }} × {{ number_format($selUnitPrice) }} RWF/{{ $selUnitLabel }}</span>
                                                <span style="font-size:16px;font-weight:800;font-family:var(--mono);color:var(--text)">{{ number_format($selLineRefund) }} <span style="font-size:11px;font-weight:400;color:var(--text-dim)">RWF</span></span>
                                            </div>

                                            {{-- 7. Notes --}}
                                            <textarea wire:model="items.{{ $selectedIndex }}.condition_notes"
                                                      rows="2"
                                                      placeholder="{{ $selCond === 'good' ? 'Notes (optional)…' : 'Describe the damage (required)…' }}"
                                                      class="pr-inp"
                                                      style="resize:vertical"
                                                      @click.stop></textarea>

                                        </div>
                                    @endif

                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            @endif

            {{-- ── STEP 3 — Return Details ── --}}
            @if($currentStep === 3)
                <div class="pr-card">
                    <div class="pr-card-head">
                        <div class="pr-card-ttl">Return Details</div>
                    </div>
                    <div class="pr-card-body">

                        <div class="pr-2col">
                            <div>
                                <label class="pr-lbl">Customer Name</label>
                                <input type="text" wire:model="customerName" placeholder="Optional…" class="pr-inp">
                            </div>
                            <div>
                                <label class="pr-lbl">Phone Number</label>
                                <input type="text" wire:model="customerPhone" placeholder="Optional…" class="pr-inp">
                            </div>
                        </div>

                        <div class="pr-field">
                            <label class="pr-lbl">Return Reason <span>*</span></label>
                            <select wire:model="reason" class="pr-inp">
                                @foreach($returnReasons as $reasonCase)
                                    <option value="{{ $reasonCase->value }}">{{ $reasonCase->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pr-field">
                            <label class="pr-lbl">Notes</label>
                            <textarea wire:model="notes" rows="3" placeholder="Any additional notes…" class="pr-inp" style="resize:vertical"></textarea>
                        </div>

                    </div>
                </div>
            @endif

            {{-- Navigation --}}
            <div style="display:flex;justify-content:space-between;gap:12px">
                @if($currentStep > 1)
                    <button wire:click="goToStep({{ $currentStep - 1 }})" class="pr-btn pr-btn-gh">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7l-7 7 7 7"/></svg>
                        Back
                    </button>
                @else
                    <div></div>
                @endif
                @if($currentStep < 3)
                    <button wire:click="goToStep({{ $currentStep + 1 }})" class="pr-btn pr-btn-pr">
                        Continue
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7l7 7-7 7"/></svg>
                    </button>
                @endif
            </div>

        </div>{{-- end left --}}

        {{-- ── RIGHT SIDEBAR ── --}}
        <div class="pr-sidebar">

            {{-- Return Type + Refund Method --}}
            <div class="pr-sc">
                <div class="pr-sc-head">
                    <div class="pr-sc-ttl">Return Type</div>
                </div>
                <div class="pr-sc-body">
                    <div class="pr-seg">
                        <button type="button" wire:click="$set('isExchange', false)"
                                class="pr-seg-btn {{ !$isExchange ? 'on' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Refund
                        </button>
                        <button type="button" wire:click="$set('isExchange', true)"
                                class="pr-seg-btn {{ $isExchange ? 'on' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            Exchange
                        </button>
                    </div>

                    @if(!$isExchange)
                        <div class="pr-mth-lbl">Refund Method</div>
                        <div class="pr-mth-seg">
                            @foreach(['cash' => 'Cash', 'card' => 'Card', 'mobile_money' => 'MoMo', 'store_credit' => 'Credit'] as $val => $lbl)
                                <button type="button"
                                        wire:click="$set('refundMethod', '{{ $val }}')"
                                        class="pr-mth-btn {{ $refundMethod === $val ? 'on' : '' }}">
                                    {{ $lbl }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Summary --}}
            <div class="pr-sc">
                <div class="pr-sc-head">
                    <div class="pr-sc-ttl">Summary</div>
                </div>
                <div class="pr-sc-body">

                    @if($linkedSale)
                        <div class="pr-sale-strip">
                            <div>
                                <div style="font-size:13px;font-weight:700;font-family:var(--mono)">{{ $linkedSale->sale_number }}</div>
                                <div style="font-size:11px;color:var(--text-dim);margin-top:2px">{{ $linkedSale->sale_date->format('d M Y') }}</div>
                            </div>
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-dim);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
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
                                $sumRefund += ($retType === 'box')
                                    ? (($itm['box_price']  ?? 0) * ($itm['qty_returned'] ?? 0))
                                    : (($itm['item_price'] ?? 0) * ($itm['qty_returned'] ?? 0));
                            }
                        }
                        // Unit labels — derive from actual return types
                        $allBox  = $sumItems > 0 && collect($items)->every(fn($i) => ($i['return_type'] ?? 'box') === 'box');
                        $allItem = $sumItems > 0 && collect($items)->every(fn($i) => ($i['return_type'] ?? 'box') === 'item');
                        $sumUnit = $allBox ? 'box' : ($allItem ? 'item' : 'unit');
                        $sumUnitPlural = $allBox ? 'boxes' : ($allItem ? 'items' : 'units');
                    @endphp

                    @if($sumItems > 0)

                        <div class="pr-d-row">
                            <span class="pr-d-lbl">Items selected</span>
                            <span class="pr-d-val">{{ $sumItems }}</span>
                        </div>
                        <div class="pr-d-row">
                            <span class="pr-d-lbl">{{ ucfirst($sumUnitPlural) }} returned</span>
                            <span class="pr-d-val">{{ $sumQty }}</span>
                        </div>
                        @if($sumDamaged > 0)
                            <div class="pr-d-row">
                                <span class="pr-d-lbl">{{ ucfirst($sumUnitPlural) }} damaged</span>
                                <span class="pr-d-val" style="color:var(--red)">{{ $sumDamaged }}</span>
                            </div>
                        @endif
                        <div class="pr-d-row">
                            <span class="pr-d-lbl">Type</span>
                            <span class="pr-d-val">{{ $isExchange ? 'Exchange' : 'Refund' }}</span>
                        </div>
                        @if(!$isExchange)
                            <div class="pr-d-row">
                                <span class="pr-d-lbl">Method</span>
                                <span class="pr-d-val">{{ ucwords(str_replace('_', ' ', $refundMethod)) }}</span>
                            </div>
                        @endif

                        @if($sumGood > 0 || $sumDamaged > 0)
                            <div style="padding:10px 0;border-top:1px solid var(--border);margin-top:4px">
                                @if($sumGood > 0)
                                    <div style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--green);padding:3px 0">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        {{ $sumGood }} {{ $sumGood === 1 ? $sumUnit : $sumUnitPlural }} return to stock
                                    </div>
                                @endif
                                @if($sumDamaged > 0)
                                    <div style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--red);padding:3px 0">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        {{ $sumDamaged }} {{ $sumDamaged === 1 ? $sumUnit : $sumUnitPlural }} flagged damaged
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(!$isExchange && $sumRefund > 0)
                            <div class="pr-ref-total">
                                <div class="pr-ref-lbl">Estimated Refund</div>
                                <div class="pr-ref-amt">{{ number_format($sumRefund) }} <span style="font-size:14px;font-weight:500;color:var(--text-dim)">RWF</span></div>
                                @if($sumRefund > 50000)
                                    <div class="pr-ref-warn">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Requires owner approval
                                    </div>
                                @endif
                            </div>
                        @endif

                    @else
                        <div style="text-align:center;padding:24px 0;color:var(--text-dim);font-size:13px">
                            Select items to see summary
                        </div>
                    @endif

                    @if($currentStep === 3)
                        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">
                            @if(!$showConfirmation)
                                <button wire:click="confirmSubmit" class="pr-btn pr-btn-pr" style="width:100%;padding:12px;font-size:14px">
                                    Review &amp; Submit
                                </button>
                            @else
                                <div class="pr-confirm">
                                    <div class="pr-conf-ttl">Confirm this return?</div>
                                    <div class="pr-conf-sub">
                                        {{ $isExchange ? 'Exchange' : 'Refund' }} · {{ $sumQty }} {{ $sumQty === 1 ? $sumUnit : $sumUnitPlural }}
                                        @if(!$isExchange && $sumRefund > 0)
                                            · {{ number_format($sumRefund) }} RWF
                                        @endif
                                    </div>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                                    <button wire:click="cancelSubmit" class="pr-btn pr-btn-gh" style="padding:10px">Cancel</button>
                                    <button wire:click="submitReturn"
                                            wire:loading.attr="disabled"
                                            wire:target="submitReturn"
                                            class="pr-btn pr-btn-gr"
                                            style="padding:10px">
                                        <span wire:loading.remove wire:target="submitReturn">Confirm</span>
                                        <span wire:loading wire:target="submitReturn" style="display:none">Processing…</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            </div>

        </div>{{-- end sidebar --}}

    </div>{{-- end grid --}}

    @endif

</div>
