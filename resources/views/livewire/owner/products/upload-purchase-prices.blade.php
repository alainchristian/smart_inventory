<div>
  {{-- ══ UPLOAD CARD ══ --}}
  @if(!$showPreview && !$applied)
  <div class="w-full">

    {{-- Instructions card --}}
    <div class="card mb-4 flex items-start gap-4">
        <div class="w-10 h-10 rounded-lg bg-[var(--accent-dim)] flex items-center justify-center shrink-0">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-[var(--accent)]">
            <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
          </svg>
        </div>
        <div>
          <div class="text-[14px] font-bold text-[var(--text)] mb-1">
            Upload Product Prices
          </div>
          <div class="text-[13px] text-[var(--text-sub)] leading-relaxed">
            Upload a CSV with columns: <code class="bg-[var(--surface2)] px-1.5 py-0.5 rounded text-[12px] font-[var(--mono)]">sku</code>,
            <code class="bg-[var(--surface2)] px-1.5 py-0.5 rounded text-[12px] font-[var(--mono)]">purchase_price</code> (per item), and optionally
            <code class="bg-[var(--surface2)] px-1.5 py-0.5 rounded text-[12px] font-[var(--mono)]">selling_price</code> (per item).
            The <code class="bg-[var(--surface2)] px-1.5 py-0.5 rounded text-[12px] font-[var(--mono)]">product_name_reference</code> column is optional
            and ignored — it's there to help you identify products while filling in the spreadsheet.
            Prices must be in whole RWF (e.g. <strong>33000</strong> for RWF 33,000 per item).
          </div>
        </div>
    </div>

    {{-- Download template --}}
    <div class="card mb-4 flex items-center justify-between p-5">
      <div>
        <div class="text-[13px] font-semibold text-[var(--text)]">Download current price sheet</div>
        <div class="text-[12px] text-[var(--text-sub)] mt-0.5">
          Pre-filled with all {{ \App\Models\Product::count() }} SKUs and their current purchase prices
        </div>
      </div>
      <button wire:click="downloadTemplate"
              class="px-4 py-2 bg-[var(--surface2)] border border-[var(--border)] rounded-lg text-[13px] font-semibold text-[var(--text)] flex items-center gap-1.5 hover:bg-[var(--surface3)] hover:text-[var(--text)] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
        </svg>
        Download CSV
      </button>
    </div>

    {{-- Upload area --}}
    <div class="card">
      <div class="text-[12px] font-bold tracking-wide uppercase text-[var(--text-dim)] mb-3">Upload CSV</div>

      <label class="block border-2 border-dashed border-[var(--border)] rounded-xl p-8 text-center cursor-pointer transition-colors hover:border-[var(--accent)] hover:bg-[var(--accent-dim)] group">
        <svg class="w-8 h-8 text-[var(--text-dim)] mx-auto mb-2 transition-colors group-hover:text-[var(--accent)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
        </svg>
        @if($csvFile)
          <div class="text-[14px] font-semibold text-[var(--accent)]">
            {{ $csvFile->getClientOriginalName() }}
          </div>
          <div class="text-[12px] text-[var(--text-sub)] mt-1">
            {{ number_format($csvFile->getSize() / 1024, 1) }} KB — click to change
          </div>
        @else
          <div class="text-[14px] font-semibold text-[var(--text)]">
            Choose CSV file
          </div>
          <div class="text-[12px] text-[var(--text-sub)] mt-1">
            .csv files only, max 5 MB
          </div>
        @endif
        <input type="file" wire:model="csvFile" accept=".csv,text/csv" class="hidden">
      </label>

      @if($fileError)
        <div class="mt-2 text-[12px] text-[var(--red)] font-medium">{{ $fileError }}</div>
      @endif

      @if($csvFile)
        <div class="mt-4 flex justify-end">
          <button wire:click="processFile"
                  wire:loading.attr="disabled"
                  class="px-5 py-2.5 bg-[var(--accent)] text-white rounded-lg text-[13px] font-bold hover:opacity-90 transition-opacity disabled:opacity-75 disabled:cursor-not-allowed">
            <span wire:loading wire:target="processFile">Parsing…</span>
            <span wire:loading.remove wire:target="processFile">Preview Changes →</span>
          </button>
        </div>
      @endif
    </div>
  </div>
  @endif

  {{-- ══ PREVIEW CARD ══ --}}
  @if($showPreview)
  <div>

    {{-- Summary bar --}}
    <div class="flex flex-wrap gap-3 mb-4">
      <div class="px-4 py-2.5 bg-[var(--green-dim)] rounded-xl flex items-center gap-2 border border-[var(--green-glow)]">
        <span class="text-[20px] font-bold text-[var(--green)]">{{ $this->newCount }}</span>
        <span class="text-[12px] font-semibold text-[var(--green)]">New prices</span>
      </div>
      <div class="px-4 py-2.5 bg-[var(--accent-dim)] rounded-xl flex items-center gap-2 border border-[var(--accent-glow)]">
        <span class="text-[20px] font-bold text-[var(--accent)]">{{ $this->updatedCountPreview }}</span>
        <span class="text-[12px] font-semibold text-[var(--accent)]">Updates</span>
      </div>
      <div class="px-4 py-2.5 bg-[var(--surface2)] rounded-xl flex items-center gap-2 border border-[var(--border)]">
        <span class="text-[20px] font-bold text-[var(--text-dim)]">{{ $this->unchangedCount }}</span>
        <span class="text-[12px] font-semibold text-[var(--text-sub)]">Unchanged</span>
      </div>
      @if(count($parseErrors) > 0)
      <div class="px-4 py-2.5 bg-[var(--red-dim)] rounded-xl flex items-center gap-2 border border-[var(--red-glow)]">
        <span class="text-[20px] font-bold text-[var(--red)]">{{ count($parseErrors) }}</span>
        <span class="text-[12px] font-semibold text-[var(--red)]">Errors</span>
      </div>
      @endif
    </div>

    {{-- Errors --}}
    @if(count($parseErrors) > 0)
    <div class="bg-[var(--red-dim)] border border-[var(--red)] rounded-xl p-4 mb-4 shadow-sm">
      <div class="text-[13px] font-bold text-[var(--red)] mb-2 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Rows with errors (skipped)
      </div>
      <div class="max-h-32 overflow-y-auto pr-2 space-y-1">
          @foreach($parseErrors as $err)
            <div class="text-[12px] text-[var(--red)] font-[var(--mono)] bg-white/50 px-2 py-1 rounded">
              <span class="font-bold opacity-75">Row {{ $err['row'] }}:</span> {{ $err['message'] }}
            </div>
          @endforeach
      </div>
    </div>
    @endif

    {{-- Preview table --}}
    @if(count($preview) > 0)
    <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl shadow-[0_1px_4px_rgba(26,31,54,.06)] overflow-hidden mb-5">
      <div class="overflow-x-auto max-h-[500px]">
        <table class="w-full border-collapse min-w-[700px] relative">
          <thead class="sticky top-0 z-10 bg-[var(--surface2)] shadow-sm">
            <tr>
              <th class="px-4 py-3 text-left text-[11px] font-bold tracking-wide uppercase text-[var(--text-dim)] border-b border-[var(--border)]">Product</th>
              <th class="px-4 py-3 text-left text-[11px] font-bold tracking-wide uppercase text-[var(--text-dim)] border-b border-[var(--border)]">SKU</th>
              <th class="px-4 py-3 text-right text-[11px] font-bold tracking-wide uppercase text-[var(--text-dim)] border-b border-[var(--border)]">Purchase Price</th>
              <th class="px-4 py-3 text-right text-[11px] font-bold tracking-wide uppercase text-[var(--text-dim)] border-b border-[var(--border)]">Selling Price</th>
              <th class="px-4 py-3 text-center text-[11px] font-bold tracking-wide uppercase text-[var(--text-dim)] border-b border-[var(--border)]">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--border)]">
            @foreach($preview as $row)
            @php
              $purchaseDiff = $row['new_purchase_price'] - $row['old_purchase_price'];
              $sellingDiff = $row['new_selling_price'] !== null
                ? $row['new_selling_price'] - $row['old_selling_price']
                : null;
            @endphp
            <tr class="transition-colors hover:bg-[var(--surface2)] {{ $row['status'] === 'unchanged' ? 'opacity-50 bg-[var(--surface)]' : 'bg-white' }}">
              <td class="px-4 py-3 text-[13px] font-semibold text-[var(--text)]">
                {{ $row['name'] }}
              </td>
              <td class="px-4 py-3 text-[12px] font-[var(--mono)] text-[var(--text-sub)]">
                {{ $row['sku'] }}
              </td>
              <td class="px-4 py-3 text-[13px] font-[var(--mono)] text-right">
                <div class="text-[--text-dim] mb-0.5 text-[11px]">
                  @if($row['old_purchase_price'] > 0)
                    {{ number_format($row['old_purchase_price']) }}
                  @else
                    <span class="italic">—</span>
                  @endif
                </div>
                <div class="font-bold text-[var(--text)] flex items-center justify-end gap-1.5">
                  {{ number_format($row['new_purchase_price']) }}
                  @if($purchaseDiff != 0)
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-black {{ $purchaseDiff > 0 ? 'bg-[var(--red-dim)] text-[var(--red)]' : 'bg-[var(--green-dim)] text-[var(--green)]' }}">
                      {{ $purchaseDiff > 0 ? '+' : '' }}{{ number_format($purchaseDiff) }}
                    </span>
                  @endif
                </div>
              </td>
              <td class="px-4 py-3 text-[13px] font-[var(--mono)] text-right">
                <div class="text-[--text-dim] mb-0.5 text-[11px]">
                  @if($row['old_selling_price'] > 0)
                    {{ number_format($row['old_selling_price']) }}
                  @else
                    <span class="italic">—</span>
                  @endif
                </div>
                <div class="font-bold text-[var(--text)] flex items-center justify-end gap-1.5">
                  @if($row['new_selling_price'] !== null)
                    {{ number_format($row['new_selling_price']) }}
                    @if($sellingDiff != 0)
                      <span class="text-[10px] px-1.5 py-0.5 rounded font-black {{ $sellingDiff > 0 ? 'bg-[var(--green-dim)] text-[var(--green)]' : 'bg-[var(--red-dim)] text-[var(--red)]' }}">
                        {{ $sellingDiff > 0 ? '+' : '' }}{{ number_format($sellingDiff) }}
                      </span>
                    @endif
                  @else
                    <span class="text-[var(--text-dim)] italic font-normal text-[12px] opacity-75">unchanged</span>
                  @endif
                </div>
              </td>
              <td class="px-4 py-3 text-center">
                @if($row['status'] === 'new')
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide bg-[var(--green-glow)] text-[var(--green)]">NEW</span>
                @elseif($row['status'] === 'updated')
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide bg-[var(--accent-glow)] text-[var(--accent)]">UPDATED</span>
                @else
                  <span class="text-[var(--text-dim)] text-[12px]">—</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] pt-4">
      <button wire:click="resetUpload"
              class="px-5 py-2.5 bg-[var(--surface2)] border border-[var(--border)] rounded-lg text-[13px] font-semibold text-[var(--text-sub)] hover:bg-[var(--surface3)] hover:text-[var(--text)] transition-colors">
        ← Upload different file
      </button>
      @if($this->newCount + $this->updatedCountPreview > 0)
      <button wire:click="applyPrices"
              wire:loading.attr="disabled"
              wire:confirm="Apply {{ $this->newCount + $this->updatedCountPreview }} price updates?"
              class="px-6 py-2.5 bg-[var(--accent)] text-white border-none rounded-lg text-[13px] font-bold shadow-sm hover:opacity-90 hover:shadow-md transition-all flex items-center gap-2">
        <span wire:loading wire:target="applyPrices">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Saving…
        </span>
        <span wire:loading.remove wire:target="applyPrices">
          ✓ Apply {{ $this->newCount + $this->updatedCountPreview }} Updates
        </span>
      </button>
      @endif
    </div>
  </div>
  @endif

  {{-- ══ SUCCESS STATE ══ --}}
  @if($applied)
  <div class="max-w-md mx-auto py-16 text-center">
    <div class="w-16 h-16 rounded-full bg-[var(--green-dim)] flex items-center justify-center mx-auto mb-5 shadow-[0_0_20px_var(--green-glow)]">
      <svg class="w-8 h-8 text-[var(--green)]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>
    <div class="text-[22px] font-black text-[var(--text)] mb-2 tracking-tight">
      {{ number_format($updatedCount) }} products updated
    </div>
    <div class="text-[14px] text-[var(--text-sub)] mb-8">
      Product prices have been successfully saved with your CSV. Purchase prices remain private.
    </div>
    <button wire:click="reset"
            class="px-6 py-2.5 bg-[var(--accent)] text-white rounded-lg text-[13px] font-bold shadow-sm hover:opacity-90 transition-all hover:-translate-y-0.5 hover:shadow-md">
      Upload another file
    </button>
  </div>
  @endif
</div>
