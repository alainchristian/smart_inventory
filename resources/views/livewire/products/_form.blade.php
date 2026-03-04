{{--
  Shared product form partial
  Variables available: $categories, $mode ('create'|'edit')
  Livewire properties accessed via wire:model
--}}

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:flex-start"
     class="product-form-grid">

  {{-- ═══ LEFT: Main fields ═══ --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Card: Identity --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                  border-bottom:1px solid var(--border)">
        Product Identity
      </div>

      {{-- Name --}}
      <div style="margin-bottom:14px">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
          Product Name <span style="color:var(--red)">*</span>
        </label>
        <input wire:model.live="name" type="text" placeholder="e.g. Coca Cola 500ml"
               style="width:100%;padding:9px 12px;border:1px solid var(--border);
                      border-radius:var(--rx);font-size:14px;background:var(--surface);
                      color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font)"
               onfocus="this.style.borderColor='var(--accent)'"
               onblur="this.style.borderColor='var(--border)'">
        @error('name')
          <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
        @enderror
      </div>

      {{-- SKU + Barcode row --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            SKU <span style="color:var(--red)">*</span>
          </label>
          <input wire:model="sku" type="text" placeholder="PROD-001"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;
                        font-family:var(--mono);text-transform:uppercase"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
          @error('sku')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
        </div>
        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Barcode
          </label>
          <input wire:model="barcode" type="text" placeholder="8801234567890"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
          @error('barcode')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
        </div>
      </div>

      {{-- Category --}}
      <div style="margin-bottom:14px">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
          Category <span style="color:var(--red)">*</span>
        </label>
        <select wire:model="categoryId"
                style="width:100%;padding:9px 12px;border:1px solid var(--border);
                       border-radius:var(--rx);font-size:13px;background:var(--surface);
                       color:var(--text);outline:none;cursor:pointer;box-sizing:border-box">
          <option value="">Select a category...</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
        @error('categoryId')
          <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
        @enderror
      </div>

      {{-- Description --}}
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
          Description
        </label>
        <textarea wire:model="description" rows="3"
                  placeholder="Optional product description..."
                  style="width:100%;padding:9px 12px;border:1px solid var(--border);
                         border-radius:var(--rx);font-size:13px;background:var(--surface);
                         color:var(--text);outline:none;resize:vertical;
                         box-sizing:border-box;font-family:var(--font)"
                  onfocus="this.style.borderColor='var(--accent)'"
                  onblur="this.style.borderColor='var(--border)'"></textarea>
      </div>
    </div>

    {{-- Card: Pricing --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                  border-bottom:1px solid var(--border);display:flex;align-items:center;
                  justify-content:space-between">
        <span>Pricing <span style="color:var(--text-dim);font-size:10px">(in RWF)</span></span>
        {{-- Live margin badge --}}
        @if($this->margin !== null)
          <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;
                       background:{{ $this->margin >= 20 ? 'var(--green-dim)' : ($this->margin >= 10 ? 'var(--accent-dim)' : 'var(--red-dim)') }};
                       color:{{ $this->margin >= 20 ? 'var(--green)' : ($this->margin >= 10 ? 'var(--accent)' : 'var(--red)') }}">
            {{ $this->margin }}% margin
          </span>
        @endif
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Purchase Price <span style="color:var(--red)">*</span>
          </label>
          <div style="position:relative">
            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                         font-size:11px;color:var(--text-dim);font-weight:600">RWF</span>
            <input wire:model.live="purchasePrice" type="number" min="0" step="100"
                   placeholder="0"
                   style="width:100%;padding:9px 12px 9px 40px;border:1px solid var(--border);
                          border-radius:var(--rx);font-size:13px;background:var(--surface);
                          color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                   onfocus="this.style.borderColor='var(--violet)'"
                   onblur="this.style.borderColor='var(--border)'">
          </div>
          @error('purchasePrice')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
          <div style="font-size:10px;color:var(--text-dim);margin-top:3px">Owner-only visible</div>
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Selling Price / Item <span style="color:var(--red)">*</span>
          </label>
          <div style="position:relative">
            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                         font-size:11px;color:var(--text-dim);font-weight:600">RWF</span>
            <input wire:model.live="sellingPrice" type="number" min="0" step="100"
                   placeholder="0"
                   style="width:100%;padding:9px 12px 9px 40px;border:1px solid var(--border);
                          border-radius:var(--rx);font-size:13px;background:var(--surface);
                          color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                   onfocus="this.style.borderColor='var(--accent)'"
                   onblur="this.style.borderColor='var(--border)'">
          </div>
          @error('sellingPrice')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
          @if($sellingPrice && $itemsPerBox)
            <p style="font-size:12px;color:var(--text-sub);margin-top:4px">
              Box price: RWF {{ number_format((int)$sellingPrice * (int)$itemsPerBox) }}
              @if($boxSellingPrice)
                · Override set: RWF {{ number_format((int)$boxSellingPrice) }}
              @endif
            </p>
          @endif
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Box Selling Price
            <span style="font-size:10px;font-weight:400;color:var(--text-dim)">(optional)</span>
          </label>
          <div style="position:relative">
            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                         font-size:11px;color:var(--text-dim);font-weight:600">RWF</span>
            <input wire:model.live="boxSellingPrice" type="number" min="0" step="100"
                   placeholder="{{ $this->boxPriceSuggestion ?: '0' }}"
                   style="width:100%;padding:9px 12px 9px 40px;border:1px solid var(--border);
                          border-radius:var(--rx);font-size:13px;background:var(--surface);
                          color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                   onfocus="this.style.borderColor='var(--accent)'"
                   onblur="this.style.borderColor='var(--border)'">
          </div>
          @if($this->boxPriceSuggestion)
            <div style="font-size:10px;color:var(--text-dim);margin-top:3px">
              Suggested: RWF {{ $this->boxPriceSuggestion }}
              ({{ $itemsPerBox }} x {{ $sellingPrice }})
            </div>
          @endif
        </div>

      </div>
    </div>

    {{-- Card: Packaging & Operational --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                  border-bottom:1px solid var(--border)">
        Packaging &amp; Operations
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Items per Box <span style="color:var(--red)">*</span>
          </label>
          <input wire:model.live="itemsPerBox" type="number" min="1"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
          @error('itemsPerBox')
            <div style="color:var(--red);font-size:11px;margin-top:4px">{{ $message }}</div>
          @enderror
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Low Stock Alert At
          </label>
          <input wire:model="lowStockThreshold" type="number" min="0"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                 onfocus="this.style.borderColor='var(--amber)'"
                 onblur="this.style.borderColor='var(--border)'">
          <div style="font-size:10px;color:var(--text-dim);margin-top:3px">items remaining</div>
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Reorder Point
          </label>
          <input wire:model="reorderPoint" type="number" min="0"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--mono)"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
        </div>

      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Unit of Measure
          </label>
          <select wire:model="unitOfMeasure"
                  style="width:100%;padding:9px 12px;border:1px solid var(--border);
                         border-radius:var(--rx);font-size:13px;background:var(--surface);
                         color:var(--text);outline:none;cursor:pointer;box-sizing:border-box">
            <option value="piece">Piece</option>
            <option value="pair">Pair</option>
            <option value="kg">Kilogram (kg)</option>
            <option value="g">Gram (g)</option>
            <option value="litre">Litre</option>
            <option value="ml">Millilitre (ml)</option>
            <option value="box">Box</option>
            <option value="carton">Carton</option>
            <option value="set">Set</option>
            <option value="roll">Roll</option>
          </select>
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text-sub);margin-bottom:5px">
            Supplier
          </label>
          <input wire:model="supplier" type="text" placeholder="e.g. Rwanda Imports Ltd"
                 style="width:100%;padding:9px 12px;border:1px solid var(--border);
                        border-radius:var(--rx);font-size:13px;background:var(--surface);
                        color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font)"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'">
        </div>

      </div>
    </div>

  </div>{{-- /left --}}

  {{-- ═══ RIGHT: Summary sidebar ═══ --}}
  <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:84px">

    {{-- Status card --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:20px 22px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:14px">Status</div>

      <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
        <div style="position:relative;width:40px;height:22px;flex-shrink:0">
          <input wire:model.live="isActive" type="checkbox" style="opacity:0;position:absolute;inset:0;cursor:pointer;z-index:1">
          <div style="position:absolute;inset:0;border-radius:11px;transition:.2s;
                      background:{{ $isActive ? 'var(--green)' : 'var(--border-hi)' }}"></div>
          <div style="position:absolute;top:3px;left:{{ $isActive ? '21px' : '3px' }};
                      width:16px;height:16px;border-radius:50%;background:#fff;
                      transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2)"></div>
        </div>
        <div>
          <div style="font-size:13px;font-weight:600;
                      color:{{ $isActive ? 'var(--green)' : 'var(--text-sub)' }}">
            {{ $isActive ? 'Active' : 'Inactive' }}
          </div>
          <div style="font-size:11px;color:var(--text-dim)">
            {{ $isActive ? 'Visible in POS and transfers' : 'Hidden from operations' }}
          </div>
        </div>
      </label>
    </div>

    {{-- Live preview card --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:20px 22px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:14px">Preview</div>

      <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:4px;min-height:22px">
        {{ $name ?: 'Product name' }}
      </div>
      <div style="font-family:var(--mono);font-size:11px;color:var(--text-dim);margin-bottom:12px">
        {{ $sku ?: 'SKU-000' }}
      </div>

      @if($sellingPrice)
        <div style="font-size:18px;font-weight:700;color:var(--accent);font-family:var(--mono);margin-bottom:4px">
          {{ number_format((float)$sellingPrice) }} RWF
        </div>
        <div style="font-size:11px;color:var(--text-dim)">per item</div>
      @endif

      @if($itemsPerBox > 0 && $sellingPrice)
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
          <div style="font-size:11px;color:var(--text-sub)">Box of {{ $itemsPerBox }}</div>
          <div style="font-size:14px;font-weight:700;color:var(--text);font-family:var(--mono)">
            @if($boxSellingPrice)
              {{ number_format((float)$boxSellingPrice) }} RWF
            @else
              ~{{ number_format((float)$sellingPrice * $itemsPerBox) }} RWF
            @endif
          </div>
        </div>
      @endif

      @if($this->margin !== null)
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);
                    display:flex;align-items:center;justify-content:space-between">
          <span style="font-size:11px;color:var(--text-sub)">Gross margin</span>
          <span style="font-size:13px;font-weight:700;
                       color:{{ $this->margin >= 20 ? 'var(--green)' : ($this->margin >= 10 ? 'var(--accent)' : 'var(--red)') }}">
            {{ $this->margin }}%
          </span>
        </div>
      @endif
    </div>

    {{-- Action buttons --}}
    <div style="display:flex;flex-direction:column;gap:8px">
      <button
        @if($mode === 'create') wire:click="save" @else wire:click="update" @endif
        wire:loading.attr="disabled"
        style="padding:11px 20px;background:var(--accent);color:#fff;border:none;
               border-radius:var(--rx);font-size:14px;font-weight:700;cursor:pointer;
               width:100%;font-family:var(--font);display:flex;align-items:center;
               justify-content:center;gap:8px">
        <span wire:loading.remove>
          @if($mode === 'create')
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Create Product
          @else
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
            </svg>
            Save Changes
          @endif
        </span>
        <span wire:loading style="font-size:13px">Saving...</span>
      </button>

      <a href="{{ route('owner.products.index') }}"
         style="padding:10px 20px;background:var(--surface2);color:var(--text-sub);
                border:1px solid var(--border);border-radius:var(--rx);font-size:13px;
                font-weight:600;text-decoration:none;text-align:center;display:block">
        Cancel
      </a>
    </div>

  </div>{{-- /right --}}

</div>

{{-- Responsive: stack on smaller screens --}}
<style>
@media (max-width: 900px) {
  .product-form-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>
