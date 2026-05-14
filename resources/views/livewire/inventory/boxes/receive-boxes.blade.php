{{--
  Stock Intake form — Owner receives boxes from supplier into a warehouse.
  Component: App\Livewire\Inventory\Boxes\ReceiveBoxes
--}}

<div>

  @if(session('success'))
    <div style="background:var(--green-dim);color:var(--green);border:1px solid var(--green);
                border-radius:var(--r);padding:12px 16px;margin-bottom:20px;font-size:13px;
                font-weight:600;display:flex;align-items:center;gap:8px">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
      {!! session('success') !!}
    </div>
  @endif

  <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:flex-start"
       class="intake-grid">

    {{-- ═══ LEFT: Form ═══ --}}
    <div style="display:flex;flex-direction:column;gap:16px">

      {{-- Product + Warehouse --}}
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px 24px">
        <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                    color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                    border-bottom:1px solid var(--border)">
          Stock Details
        </div>

        {{-- Product --}}
        <div style="margin-bottom:14px">
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Product <span style="color:var(--red)">*</span>
          </label>
          <select wire:model.live="productId"
                  style="width:100%;padding:9px 12px;border:1px solid var(--border);
                         border-radius:var(--rx);font-size:13px;background:var(--surface);
                         color:var(--text);outline:none;cursor:pointer;box-sizing:border-box"
                  onfocus="this.style.borderColor='var(--accent)'"
                  onblur="this.style.borderColor='var(--border)'">
            <option value="">Select a product...</option>
            @foreach($products as $product)
              <option value="{{ $product->id }}">
                {{ $product->name }}
                @if($product->category)· {{ $product->category->name }}@endif
                — {{ $product->items_per_box }} per box
              </option>
            @endforeach
          </select>
          @error('productId')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Warehouse --}}
        <div style="margin-bottom:14px">
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Destination Warehouse <span style="color:var(--red)">*</span>
          </label>
          <select wire:model.live="warehouseId"
                  style="width:100%;padding:9px 12px;border:1px solid var(--border);
                         border-radius:var(--rx);font-size:13px;background:var(--surface);
                         color:var(--text);outline:none;cursor:pointer;box-sizing:border-box"
                  onfocus="this.style.borderColor='var(--accent)'"
                  onblur="this.style.borderColor='var(--border)'">
            <option value="">Select a warehouse...</option>
            @foreach($warehouses as $wh)
              <option value="{{ $wh->id }}">{{ $wh->name }}</option>
            @endforeach
          </select>
          @error('warehouseId')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Number of boxes --}}
        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Number of Boxes <span style="color:var(--red)">*</span>
          </label>
          <input wire:model.live="numberOfBoxes" type="number" min="1" max="100"
                 style="width:160px;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:14px;font-family:var(--mono);
                        background:var(--surface);color:var(--text);outline:none;box-sizing:border-box"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
          @error('numberOfBoxes')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
          <div style="font-size:10px;color:var(--text-dim);margin-top:3px">Max 100 boxes per intake. Repeat for larger quantities.</div>
        </div>
      </div>

      {{-- Optional fields --}}
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px 24px">
        <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                    color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                    border-bottom:1px solid var(--border)">
          Optional Details
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
              Batch / PO Number
            </label>
            <input wire:model="batchNumber" type="text" placeholder="e.g. BATCH-2026-001"
                   style="width:100%;padding:9px 12px;border:1px solid var(--border);
                          border-radius:var(--rx);font-size:13px;background:var(--surface);
                          color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                   onfocus="this.style.borderColor='var(--accent)'"
                   onblur="this.style.borderColor='var(--border)'">
          </div>
          <div>
            <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
              Expiry Date
            </label>
            <input wire:model="expiryDate" type="date"
                   style="width:100%;padding:9px 12px;border:1px solid var(--border);
                          border-radius:var(--rx);font-size:13px;background:var(--surface);
                          color:var(--text);outline:none;box-sizing:border-box"
                   onfocus="this.style.borderColor='var(--amber)'"
                   onblur="this.style.borderColor='var(--border)'">
            @error('expiryDate')
              <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

    </div>{{-- /left --}}

    {{-- ═══ RIGHT: Summary + Action ═══ --}}
    <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:84px">

      {{-- Preview --}}
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:20px 22px">
        <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                    color:var(--text-sub);margin-bottom:14px">Intake Summary</div>

        @php
          $selectedProduct  = $productId  ? $products->firstWhere('id', $productId)  : null;
          $selectedWarehouse= $warehouseId ? $warehouses->firstWhere('id', $warehouseId) : null;
        @endphp

        @if($selectedProduct)
          <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:2px">
            {{ $selectedProduct->name }}
          </div>
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:10px;font-family:var(--mono)">
            {{ $selectedProduct->sku }} · {{ $selectedProduct->items_per_box }} items/box
          </div>
        @else
          <div style="font-size:13px;color:var(--text-dim);margin-bottom:10px">No product selected</div>
        @endif

        @if($selectedWarehouse)
          <div style="font-size:12px;color:var(--text-sub);margin-bottom:10px">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;margin-right:4px">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            → {{ $selectedWarehouse->name }}
          </div>
        @endif

        <div style="padding:12px;background:var(--surface2);border-radius:var(--rx);
                    text-align:center;margin-bottom:14px">
          <div style="font-size:28px;font-weight:800;color:var(--accent);font-family:var(--mono)">
            {{ $numberOfBoxes }}
          </div>
          <div style="font-size:11px;color:var(--text-sub)">
            box{{ $numberOfBoxes != 1 ? 'es' : '' }} to receive
          </div>
          @if($selectedProduct)
            <div style="font-size:11px;color:var(--text-dim);margin-top:4px">
              = {{ number_format($numberOfBoxes * $selectedProduct->items_per_box) }} items total
            </div>
          @endif
        </div>

        <button wire:click="createBoxes"
                wire:loading.attr="disabled"
                style="width:100%;padding:12px 20px;background:var(--accent);color:#fff;
                       border:none;border-radius:var(--rx);font-size:14px;font-weight:700;
                       cursor:pointer;font-family:var(--font);display:flex;align-items:center;
                       justify-content:center;gap:8px">
          <span wire:loading.remove>
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Confirm Intake
          </span>
          <span wire:loading style="font-size:13px">Saving...</span>
        </button>

        @if($createdBoxes)
          <button wire:click="resetForm"
                  style="width:100%;margin-top:8px;padding:10px 20px;background:var(--surface2);
                         color:var(--text-sub);border:1px solid var(--border);border-radius:var(--rx);
                         font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font)">
            Receive Another Batch
          </button>
        @endif
      </div>

      {{-- Created boxes list --}}
      @if($createdBoxes)
        <div style="background:var(--surface);border:1px solid var(--green);border-radius:var(--r);padding:16px 18px">
          <div style="font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                      color:var(--green);margin-bottom:10px">
            {{ count($createdBoxes) }} Boxes Created
          </div>
          <div style="display:flex;flex-direction:column;gap:4px;max-height:200px;overflow-y:auto">
            @foreach($createdBoxes as $box)
              <div style="font-family:var(--mono);font-size:12px;color:var(--text-sub);
                          padding:4px 8px;background:var(--surface2);border-radius:4px">
                {{ $box->box_code }}
              </div>
            @endforeach
          </div>
          <button wire:click="printLabels"
                  wire:loading.attr="disabled"
                  style="width:100%;margin-top:10px;padding:8px 14px;
                         background:var(--surface2);color:var(--text);border:1px solid var(--border);
                         border-radius:var(--rx);font-size:12px;font-weight:600;cursor:pointer;
                         font-family:var(--font);display:flex;align-items:center;justify-content:center;gap:6px">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
              <rect x="6" y="14" width="12" height="8"/>
            </svg>
            <span wire:loading.remove>Print Box Labels</span>
            <span wire:loading>Generating...</span>
          </button>
        </div>
      @endif

    </div>{{-- /right --}}

  </div>

  <style>
  @media (max-width: 860px) {
    .intake-grid { grid-template-columns: 1fr !important; }
  }
  </style>

</div>
