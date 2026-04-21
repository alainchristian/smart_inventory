<div>
    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-3 mb-5">
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;">Search</label>
            <div style="position:relative;">
                <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-gray-400">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Sale #, customer name, phone, product…"
                       style="width:100%;padding:8px 10px 8px 30px;border:1.5px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);font-size:13px;outline:none;">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;">From</label>
            <input wire:model.live="dateFrom" type="date"
                   style="padding:8px 10px;border:1.5px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);font-size:13px;outline:none;">
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1.5" style="color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;">To</label>
            <input wire:model.live="dateTo" type="date"
                   style="padding:8px 10px;border:1.5px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);font-size:13px;outline:none;">
        </div>
    </div>

    {{-- Results table --}}
    <div style="border:1px solid var(--border);border-radius:14px;overflow:hidden;">

        {{-- Header --}}
        <div style="display:grid;grid-template-columns:130px 1fr 1fr 90px 90px 80px;gap:8px;padding:9px 16px;background:var(--surface-raised);border-bottom:1px solid var(--border);">
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);">Receipt #</span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);">Customer</span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);">Items</span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);text-align:right;">Total</span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);">Date</span>
            <span></span>
        </div>

        @forelse($sales as $sale)
        <div style="display:grid;grid-template-columns:130px 1fr 1fr 90px 90px 80px;gap:8px;padding:10px 16px;border-bottom:1px solid var(--border);background:var(--surface);align-items:center;"
             wire:key="sale-{{ $sale->id }}">

            {{-- Receipt # --}}
            <div>
                <div style="font-size:12px;font-weight:700;font-family:monospace;color:var(--text);">{{ $sale->sale_number }}</div>
                @if($sale->shop)
                    <div style="font-size:10px;color:var(--text-faint);">{{ $sale->shop->name }}</div>
                @endif
            </div>

            {{-- Customer --}}
            <div>
                @if($sale->customer_name)
                    <div style="font-size:12px;color:var(--text);">{{ $sale->customer_name }}</div>
                    @if($sale->customer_phone)
                        <div style="font-size:10px;font-family:monospace;color:var(--text-faint);">{{ $sale->customer_phone }}</div>
                    @endif
                @else
                    <span style="font-size:11px;color:var(--text-faint);">Walk-in</span>
                @endif
            </div>

            {{-- Items summary --}}
            <div style="font-size:11px;color:var(--text-dim);">
                @php
                    $grouped = $sale->items->groupBy(fn($i) => $i->product_id)->map(fn($g) => $g->first()->product->name ?? '?');
                    $names = $grouped->take(2)->values();
                    $extra = $grouped->count() - 2;
                @endphp
                {{ $names->implode(', ') }}{{ $extra > 0 ? ' +' . $extra . ' more' : '' }}
            </div>

            {{-- Total --}}
            <div style="font-size:12px;font-weight:700;font-family:monospace;color:var(--accent);text-align:right;">
                {{ number_format($sale->total) }}
            </div>

            {{-- Date --}}
            <div style="font-size:11px;color:var(--text-dim);">
                {{ ($sale->sale_date ?? $sale->created_at)->format('d M Y') }}<br>
                <span style="color:var(--text-faint);font-size:10px;">{{ ($sale->sale_date ?? $sale->created_at)->format('H:i') }}</span>
            </div>

            {{-- Print button --}}
            <div>
                <a href="{{ route('shop.receipt.print', $sale->id) }}" target="_blank"
                   style="display:inline-flex;align-items:center;gap:5px;padding:6px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:11px;font-weight:700;color:var(--text-dim);text-decoration:none;background:var(--surface-raised);transition:all .15s;"
                   onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
                   onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-dim)'">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Print
                </a>
            </div>

        </div>
        @empty
        <div style="padding:32px;text-align:center;color:var(--text-faint);font-size:13px;">
            No receipts found. Try adjusting the filters.
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($sales->hasPages())
        <div class="mt-4">{{ $sales->links() }}</div>
    @endif
</div>
