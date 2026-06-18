{{-- Cart panel — shared by desktop right column and mobile drawer --}}
@php $hasWarehouseItems = collect($cart)->contains(fn($i) => ($i['source'] ?? 'shop') === 'warehouse'); @endphp
<div class="upos-cart-panel">

    {{-- Header --}}
    <div class="upos-cart-header">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" style="flex-shrink:0"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="upos-cart-title">Cart</span>
        @if(count($cart) > 0)
            <span class="upos-cart-badge">{{ count($cart) }}</span>
        @endif
        @if(count($cart) > 0)
            <button class="upos-cart-clear" wire:click="clearCart" wire:confirm="Clear all items from cart?">Clear</button>
        @endif
    </div>

    {{-- Items --}}
    <div class="upos-cart-items">
        @forelse($cart as $index => $item)
        <div class="upos-cart-item">
            <div class="upos-cart-item-top">
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:3px">
                        <span class="upos-badge {{ $item['source'] }}">
                            @if($item['source'] === 'shop')
                                <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                Shop
                            @else
                                <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                Warehouse
                            @endif
                        </span>
                        <span class="upos-cart-item-mode">{{ $item['mode'] === 'box' ? 'Box' : 'Item' }}</span>
                    </div>
                    <div class="upos-cart-item-name">{{ $item['product_name'] }}</div>
                    @if(!empty($item['price_modified']))
                        <span style="font-size:10px;color:var(--amber);font-weight:600">Price modified</span>
                    @endif
                </div>
                <div class="upos-cart-item-actions">
                    <button class="upos-cart-item-btn" wire:click="openEditItem({{ $index }})" title="Edit">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="upos-cart-item-btn del" wire:click="removeCartItem({{ $index }})" title="Remove">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                </div>
            </div>
            <div class="upos-cart-item-row">
                <span class="upos-cart-item-qty">
                    {{ $item['qty'] }} {{ $item['mode'] === 'box' ? ($item['qty'] == 1 ? 'box' : 'boxes') : ($item['qty'] == 1 ? 'item' : 'items') }}
                    × {{ number_format($item['price']) }}
                </span>
                <span class="upos-cart-item-total">{{ number_format($item['line_total']) }}</span>
            </div>
        </div>
        @empty
        <div class="upos-cart-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--border)" stroke-width="1.5" style="margin:0 auto 10px;display:block"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            No items yet.<br>
            <span style="font-size:11px">Tap a product to add it.</span>
        </div>
        @endforelse
    </div>

    {{-- Footer --}}
    <div class="upos-cart-footer">
        @if(count($cart) > 0)
            <div class="upos-cart-subtotal">
                <span>{{ count($cart) }} {{ count($cart) == 1 ? 'item' : 'items' }}</span>
                <span style="font-family:var(--mono)">{{ number_format($cartTotal) }} RWF</span>
            </div>
            @if($hasWarehouseItems)
            <div style="display:flex;align-items:center;gap:6px;padding:6px 0;margin-bottom:4px">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span style="font-size:11px;color:var(--amber);font-weight:600">Contains warehouse items — fulfillment required</span>
            </div>
            @endif
        @endif
        <div class="upos-cart-total">
            <span class="upos-cart-total-label">Total</span>
            <span class="upos-cart-total-val">{{ number_format($cartTotal) }} <span style="font-size:12px;font-weight:400;color:var(--text-dim)">RWF</span></span>
        </div>
        <button class="upos-btn-checkout" wire:click="openCheckout" @if(count($cart) === 0) disabled @endif>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:6px;vertical-align:middle"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            Checkout
        </button>
        @if(count($cart) > 0)
        <div class="upos-cart-actions">
            <button class="upos-cart-action-btn" wire:click="holdSale">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:4px;vertical-align:middle"><path d="M18 11V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0"/><path d="M14 10V4a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/></svg>
                Hold
            </button>
            <button class="upos-cart-action-btn" wire:click="saveCart">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:4px;vertical-align:middle"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save
            </button>
        </div>
        @endif
    </div>

</div>
