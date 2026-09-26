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
    <div style="background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                  border-bottom:1px solid var(--border)">
        Product Identity
      </div>

      {{-- Name --}}
      <div style="margin-bottom:14px">
        <label class="pf-label">Product Name <span style="color:var(--red)">*</span></label>
        <input wire:model.live="name" type="text" placeholder="e.g. Coca Cola 500ml"
               class="pf-input">
        @error('name')
          <div class="pf-error">{{ $message }}</div>
        @enderror
      </div>

      {{-- SKU + Barcode row --}}
      <div class="pf-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
        <div>
          <label class="pf-label">SKU <span style="color:var(--red)">*</span></label>
          <input wire:model="sku" type="text" placeholder="PROD-001"
                 class="pf-input pf-mono pf-upper">
          @error('sku')
            <div class="pf-error">{{ $message }}</div>
          @enderror
        </div>
        <div>
          <label class="pf-label">Barcode</label>
          <input wire:model="barcode" type="text" placeholder="8801234567890"
                 class="pf-input pf-mono">
          @error('barcode')
            <div class="pf-error">{{ $message }}</div>
          @enderror
        </div>
      </div>

      {{-- Category --}}
      <div style="margin-bottom:14px">
        <label class="pf-label">Category <span style="color:var(--red)">*</span></label>
        <select wire:model.live="categoryId" class="pf-input pf-select">
          <option value="">Select a category...</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
        @error('categoryId')
          <div class="pf-error">{{ $message }}</div>
        @enderror
      </div>

      {{-- Description --}}
      <div>
        <label class="pf-label">Description</label>
        <textarea wire:model="description" rows="3"
                  placeholder="Optional product description..."
                  class="pf-input pf-textarea"></textarea>
      </div>
    </div>

    {{-- Card: Pricing --}}
    <div style="background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                  border-bottom:1px solid var(--border);display:flex;align-items:center;
                  justify-content:space-between">
        <span>Pricing <span style="color:var(--text-dim);font-size:10px">(RWF per box)</span></span>
        {{-- Live margin badge --}}
        @if($this->margin !== null)
          <span style="font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;
                       background:{{ $this->margin >= 20 ? 'var(--green-dim)' : ($this->margin >= 10 ? 'var(--accent-dim)' : 'var(--red-dim)') }};
                       color:{{ $this->margin >= 20 ? 'var(--green)' : ($this->margin >= 10 ? 'var(--accent)' : 'var(--red)') }}">
            {{ $this->margin }}% margin
          </span>
        @endif
      </div>

      {{-- Items per Box (first — needed to compute per-item hints) --}}
      <div style="margin-bottom:16px">
        <label class="pf-label">Items per Box <span style="color:var(--red)">*</span></label>
        <input wire:model.live="itemsPerBox" type="number" min="1"
               class="pf-input pf-mono" style="width:180px">
        @error('itemsPerBox')
          <div class="pf-error">{{ $message }}</div>
        @enderror
      </div>

      <div class="pf-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- Box Purchase Price --}}
        <div>
          <label class="pf-label">Box Purchase Price <span style="color:var(--red)">*</span></label>
          <div class="pf-price-wrap">
            <span class="pf-price-prefix">RWF</span>
            <input wire:model.live="boxPurchasePrice" type="number" min="0" step="100"
                   placeholder="0" class="pf-input pf-mono pf-price">
          </div>
          @error('boxPurchasePrice')
            <div class="pf-error">{{ $message }}</div>
          @enderror
          <div class="pf-hint">
            @if($boxPurchasePrice && $itemsPerBox > 0)
              → RWF {{ number_format((int) round((float)$boxPurchasePrice / $itemsPerBox)) }} per item
            @else
              → per-item price calculated automatically
            @endif
          </div>
        </div>

        {{-- Box Selling Price --}}
        <div>
          <label class="pf-label">Box Selling Price <span style="color:var(--red)">*</span></label>
          <div class="pf-price-wrap">
            <span class="pf-price-prefix">RWF</span>
            <input wire:model.live="boxSellingPrice" type="number" min="0" step="100"
                   placeholder="0" class="pf-input pf-mono pf-price">
          </div>
          @error('boxSellingPrice')
            <div class="pf-error">{{ $message }}</div>
          @enderror
          <div class="pf-hint">
            @if($boxSellingPrice && $itemsPerBox > 0)
              → RWF {{ number_format((int) round((float)$boxSellingPrice / $itemsPerBox)) }} per item
            @else
              → per-item price calculated automatically
            @endif
          </div>
        </div>

      </div>
    </div>

    {{-- Card: Packaging & Operational --}}
    <div style="background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:16px;padding-bottom:12px;
                  border-bottom:1px solid var(--border)">
        Packaging &amp; Operations
      </div>

      <div class="pf-grid-3" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">

        {{-- Items per Box confirmation --}}
        <div>
          <label class="pf-label">Items per Box <span style="color:var(--text-dim);font-size:10px">(confirmation)</span></label>
          <input wire:model.live="itemsPerBox" type="number" min="1"
                 class="pf-input pf-mono pf-readonly">
        </div>

        <div>
          <label class="pf-label">Low Stock Alert At</label>
          <input wire:model="lowStockThreshold" type="number" min="0"
                 class="pf-input pf-mono">
          <div class="pf-hint">items remaining</div>
        </div>

        <div>
          <label class="pf-label">Reorder Point</label>
          <input wire:model="reorderPoint" type="number" min="0"
                 class="pf-input pf-mono">
        </div>

      </div>

      <div class="pf-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">

        <div>
          <label class="pf-label">Unit of Measure</label>
          <select wire:model="unitOfMeasure" class="pf-input pf-select">
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
          <label class="pf-label">Supplier</label>
          <input wire:model="supplier" type="text" placeholder="e.g. Rwanda Imports Ltd"
                 class="pf-input">
        </div>

      </div>
    </div>

    {{-- Card: Selling in packs (loose sales by pair / dozen / pack) --}}
    <div style="background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);padding:22px 24px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:6px;padding-bottom:12px;
                  border-bottom:1px solid var(--border)">
        Selling Loose
      </div>
      <div class="pf-hint" style="margin:10px 0 14px">
        Besides full boxes, this product can be sold by the piece or in packs (pair, dozen, pack of 10…).
        Stock is still counted in pieces.
      </div>

      @if($categoryId && ! $this->categorySellsLoose)
        <div class="pf-su-note">
          This category is sold by full box only, so nothing below is offered in the POS until the owner
          ticks it in Settings → Sales.
        </div>
      @endif

      <label class="pf-su-switch">
        <input type="checkbox" wire:model.live="sellSinglePieces">
        <span class="pf-su-track"><span class="pf-su-knob"></span></span>
        <span>
          <span style="display:block;font-size:13px;font-weight:600;color:var(--text)">Sell single pieces</span>
          <span style="display:block;font-size:11px;color:var(--text-dim)">
            @if($sellSinglePieces)
              One piece at a time, at {{ number_format($this->piecePrice) }} RWF
            @else
              Off — loose sales only in the packs below
            @endif
          </span>
        </span>
      </label>

      @if(count($sellUnits))
        <div class="pf-su-head" aria-hidden="true">
          <span>Pack name</span><span>Pieces</span><span>Price (RWF)</span><span></span>
        </div>
      @endif
      @foreach($sellUnits as $i => $unit)
        @php
          $size  = max(1, (int) ($unit['size'] ?? 0));
          $each  = $size > 0 && (int) ($unit['price'] ?? 0) > 0 ? $unit['price'] / $size : 0;
          $vsOne = $this->piecePrice > 0 && $each > 0 ? round((1 - $each / $this->piecePrice) * 100) : null;
        @endphp
        <div wire:key="su-{{ $i }}">
          <div class="pf-su-row">
            <input type="text" wire:model.blur="sellUnits.{{ $i }}.name" class="pf-input" placeholder="e.g. Dozen" aria-label="Pack name">
            <input type="number" min="2" wire:model.live.debounce.400ms="sellUnits.{{ $i }}.size" class="pf-input pf-mono" aria-label="Pieces in the pack">
            <div class="pf-price-wrap">
              <span class="pf-price-prefix">RWF</span>
              <input type="number" min="1" step="50" wire:model.live.debounce.400ms="sellUnits.{{ $i }}.price" class="pf-input pf-mono pf-price" aria-label="Pack price">
            </div>
            <button type="button" class="pf-su-remove" wire:click="removeSellUnit({{ $i }})" title="Remove this pack" aria-label="Remove {{ $unit['name'] ?: 'pack' }}">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          @if($each > 0)
            <div class="pf-hint" style="margin:-4px 0 10px">
              = {{ number_format($each, $each == floor($each) ? 0 : 1) }} RWF a piece
              @if($vsOne !== null && $vsOne > 0) · {{ $vsOne }}% less than single pieces
              @elseif($vsOne !== null && $vsOne < 0) · {{ abs($vsOne) }}% more than single pieces @endif
            </div>
          @endif
          @foreach(['name', 'size', 'price'] as $f)
            @error("sellUnits.$i.$f") <div class="pf-error" style="margin:-6px 0 10px">{{ $message }}</div> @enderror
          @endforeach
        </div>
      @endforeach

      <div class="pf-su-presets">
        @foreach($this->sellUnitPresets as $key => $preset)
          @php [$pName, $pSize] = $preset; @endphp
          <button type="button" class="pf-su-chip" wire:click="addSellUnit('{{ $key }}')">+ {{ $pName }} <span>{{ $pSize }}</span></button>
        @endforeach
        @if(count($sellUnits) < 8 && $itemsPerBox > 2)
          <button type="button" class="pf-su-chip" wire:click="addSellUnit('custom')">+ Other pack</button>
        @endif
      </div>
      @error('sellUnits') <div class="pf-error">{{ $message }}</div> @enderror
      @if(! $sellSinglePieces && ! count($sellUnits))
        <div class="pf-hint" style="color:var(--amber)">Single pieces are off and there are no packs, so this product is sold by full box only.</div>
      @endif
    </div>

  </div>{{-- /left --}}

  {{-- ═══ RIGHT: Summary sidebar ═══ --}}
  <div class="pf-sidebar" style="display:flex;flex-direction:column;gap:16px;position:sticky;top:84px">

    {{-- Status card --}}
    <div style="background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);padding:20px 22px">
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
    <div style="background:var(--surface);border:none;box-shadow:var(--shadow-card);border-radius:var(--r);padding:20px 22px">
      <div style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;
                  color:var(--text-sub);margin-bottom:14px">Preview</div>

      <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:4px;min-height:22px">
        {{ $name ?: 'Product name' }}
      </div>
      <div style="font-family:var(--mono);font-size:11px;color:var(--text-dim);margin-bottom:12px">
        {{ $sku ?: 'SKU-000' }}
      </div>

      @if($boxSellingPrice)
        <div style="font-size:11px;color:var(--text-sub);margin-bottom:2px">Box of {{ $itemsPerBox }}</div>
        <div style="font-size:18px;font-weight:700;color:var(--accent);font-family:var(--mono);margin-bottom:2px">
          {{ number_format((float)$boxSellingPrice) }} RWF
        </div>
        <div style="font-size:11px;color:var(--text-dim)">per box</div>
        @if($itemsPerBox > 0)
          <div style="font-size:12px;color:var(--text-sub);margin-top:4px">
            = {{ number_format((int) round((float)$boxSellingPrice / $itemsPerBox)) }} RWF per item
          </div>
        @endif
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
        class="pf-btn-save">
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
        <span wire:loading style="display:none;font-size:13px">Saving...</span>
      </button>

      <a href="{{ route('owner.products.index') }}" class="pf-btn-cancel">Cancel</a>
    </div>

  </div>{{-- /right --}}

</div>

<style>
/* ── Input system ─────────────────────────────────── */
.pf-label  { display:block;font-size:12px;font-weight:600;color:var(--text-sub);
             margin-bottom:5px;letter-spacing:.2px }
.pf-error  { color:var(--red);font-size:11px;margin-top:4px }
.pf-hint   { font-size:11px;color:var(--text-dim);margin-top:4px }

.pf-input  { width:100%;padding:9px 12px;border:1.5px solid var(--border);
             border-radius:var(--rsm);font-size:14px;background:var(--surface);
             color:var(--text);outline:none;box-sizing:border-box;font-family:var(--font);
             transition:border-color var(--tr),box-shadow var(--tr) }
.pf-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }

.pf-mono   { font-family:var(--mono) }
.pf-upper  { text-transform:uppercase }
.pf-select { cursor:pointer }
.pf-textarea { resize:vertical }
.pf-readonly { background:var(--surface2);color:var(--text-sub) }

.pf-price-wrap   { position:relative }
.pf-price-prefix { position:absolute;left:10px;top:50%;transform:translateY(-50%);
                   font-size:11px;color:var(--text-dim);font-weight:600;pointer-events:none }
.pf-price        { padding-left:40px !important }

/* ── Selling loose (packs) ───────────────────────── */
.pf-su-note   { font-size:12px;color:var(--text-sub);border-left:3px solid var(--amber);padding:8px 12px;margin-bottom:14px;line-height:1.5 }
.pf-su-switch { display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:16px }
.pf-su-switch input { position:absolute;opacity:0;width:0;height:0 }
.pf-su-track  { position:relative;width:40px;height:22px;flex-shrink:0;border-radius:11px;background:var(--border-hi);transition:background var(--tr) }
.pf-su-knob   { position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:transform var(--tr) }
.pf-su-switch input:checked + .pf-su-track { background:var(--green) }
.pf-su-switch input:checked + .pf-su-track .pf-su-knob { transform:translateX(18px) }
.pf-su-switch input:focus-visible + .pf-su-track { box-shadow:0 0 0 3px var(--accent-dim) }
.pf-su-head   { display:grid;grid-template-columns:minmax(0,1.3fr) 90px minmax(0,1fr) 34px;gap:8px;margin-bottom:6px;
                font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--text-dim) }
.pf-su-row    { display:grid;grid-template-columns:minmax(0,1.3fr) 90px minmax(0,1fr) 34px;gap:8px;align-items:center;margin-bottom:8px }
.pf-su-remove { width:34px;height:38px;border:1.5px solid var(--border);border-radius:var(--rsm);background:var(--surface);
                color:var(--text-dim);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all var(--tr) }
.pf-su-remove:hover { border-color:var(--red);color:var(--red) }
.pf-su-presets { display:flex;flex-wrap:wrap;gap:6px;margin-top:6px }
.pf-su-chip   { padding:6px 12px;border:1.5px dashed var(--border-hi);border-radius:20px;background:var(--surface);
                font-size:12px;font-weight:600;color:var(--text-sub);cursor:pointer;font-family:var(--font);transition:all var(--tr) }
.pf-su-chip span { font-family:var(--mono);color:var(--text-dim);margin-left:2px }
.pf-su-chip:hover { border-color:var(--accent);border-style:solid;color:var(--accent) }
@media (max-width: 640px) {
  .pf-su-remove, .pf-su-chip { min-height:0 !important;min-width:0 !important }
  .pf-su-chip { padding:6px 12px !important }
  .pf-su-remove { padding:0 !important;width:34px !important;height:38px !important }
  .pf-su-head { display:none }
  .pf-su-row { grid-template-columns:minmax(0,1fr) 70px 34px }
  .pf-su-row .pf-price-wrap { grid-column:1 / 3;grid-row:2 }
}

/* ── Buttons ─────────────────────────────────────── */
.pf-btn-save { padding:11px 20px;background:var(--accent);color:#fff;border:none;
               border-radius:var(--rsm);font-size:14px;font-weight:700;cursor:pointer;
               width:100%;font-family:var(--font);display:flex;align-items:center;
               justify-content:center;gap:8px;transition:opacity var(--tr);
               box-shadow:0 3px 10px rgba(59,111,212,.25) }
.pf-btn-save:hover    { opacity:.88 }
.pf-btn-save:disabled { opacity:.5;cursor:not-allowed }
.pf-btn-cancel { padding:10px 20px;background:var(--surface2);color:var(--text-sub);
                 border:1.5px solid var(--border);border-radius:var(--rsm);font-size:13px;
                 font-weight:600;text-decoration:none;text-align:center;display:block;
                 transition:all var(--tr) }
.pf-btn-cancel:hover { border-color:var(--border-hi);color:var(--text) }

/* ── Layout breakpoints ──────────────────────────── */
@media (max-width: 900px) {
  .product-form-grid { grid-template-columns: 1fr !important; }
  .pf-sidebar { position:static !important; top:auto !important; }
}
@media (max-width: 768px) {
  .pf-grid-3 { grid-template-columns: 1fr 1fr !important; }
}
@media (max-width: 540px) {
  .pf-grid-2, .pf-grid-3 { grid-template-columns: 1fr !important; }
  .pf-input { font-size:16px !important; } /* prevent iOS zoom */
}
</style>
