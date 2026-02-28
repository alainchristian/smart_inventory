<div style="min-height: 100vh; background: var(--bg);">

    {{-- Shop Selection Modal for Owners (Mandatory) --}}
    @if($showShopSelectionModal && $isOwner)
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 20px;">
            <div style="background: var(--surface); border-radius: 16px; max-width: 500px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                <div style="padding: 24px; border-bottom: 1px solid var(--border);">
                    <div style="text-align: center;">
                        <div style="width: 64px; height: 64px; background: var(--accent-dim); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                            <svg style="width: 32px; height: 32px; color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h2 style="font-size: 20px; font-weight: 700; color: var(--text); margin-bottom: 8px;">Select Shop Location</h2>
                        <p style="font-size: 14px; color: var(--text-sub);">Choose which shop you want to operate POS from</p>
                    </div>
                </div>

                <div style="padding: 24px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 12px;">Shop Location</label>

                    <div style="display: grid; gap: 12px;">
                        @foreach($availableShops as $shop)
                            <button wire:click="$set('shopId', {{ $shop['id'] }})" type="button"
                                style="padding: 16px; border: 2px solid {{ $shopId === $shop['id'] ? 'var(--accent)' : 'var(--border)' }};
                                       border-radius: 12px; background: {{ $shopId === $shop['id'] ? 'var(--accent-dim)' : 'var(--surface)' }};
                                       cursor: pointer; transition: all 0.2s; text-align: left;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <div style="font-size: 15px; font-weight: 600; color: {{ $shopId === $shop['id'] ? 'var(--accent)' : 'var(--text)' }};">
                                            {{ $shop['name'] }}
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-dim); margin-top: 4px;">
                                            {{ $shop['address'] ?? 'Shop Location' }}
                                        </div>
                                    </div>
                                    @if($shopId === $shop['id'])
                                        <svg style="width: 24px; height: 24px; color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <button wire:click="selectShopFromModal" type="button"
                            @if(!$shopId) disabled @endif
                            style="width: 100%; margin-top: 24px; padding: 14px 24px; background: {{ $shopId ? 'var(--accent)' : 'var(--border)' }};
                                   color: white; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: {{ $shopId ? 'pointer' : 'not-allowed' }};
                                   border: none; transition: all 0.2s;">
                        Continue to POS
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Block content if no shop selected for owners --}}
    @if($isOwner && !$shopId)
        <div style="height: 100vh; display: flex; align-items: center; justify-content: center;">
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--surface2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <svg style="width: 40px; height: 40px; color: var(--text-dim);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <p style="font-size: 16px; color: var(--text-sub);">Please select a shop to start</p>
            </div>
        </div>
    @else
        {{-- Main POS Content --}}
        <div style="background: var(--surface); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 50;">
            <div style="max-width: 1400px; margin: 0 auto; padding: 20px 24px;">
                {{-- Shop Selector for Owners --}}
                @if($isOwner && !empty($availableShops))
                    <div style="margin-bottom: 16px; background: var(--accent-dim); border: 1px solid var(--accent); border-radius: 12px; padding: 14px 16px;">
                        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg style="width: 18px; height: 18px; color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span style="font-size: 13px; font-weight: 600; color: var(--accent);">Owner Mode:</span>
                            </div>
                            <select wire:model.live="shopId"
                                    wire:change="changeShop"
                                    style="flex: 1; max-width: 300px; padding: 8px 12px; border: 1px solid var(--accent); border-radius: 8px; background: var(--surface);
                                           font-size: 14px; font-weight: 600; color: var(--text); cursor: pointer;">
                                @foreach($availableShops as $shop)
                                    <option value="{{ $shop['id'] }}">{{ $shop['name'] }}</option>
                                @endforeach
                            </select>
                            <span style="font-size: 12px; color: var(--accent); opacity: 0.8;">Cart will clear when switching shops</span>
                        </div>
                    </div>
                @endif

                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h1 style="font-size: 24px; font-weight: 700; color: var(--text); margin-bottom: 4px;">Point of Sale</h1>
                        <p style="font-size: 14px; color: var(--text-sub);">
                            <svg style="width: 14px; height: 14px; display: inline; vertical-align: middle; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            {{ $shopName }}
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="text-align: right;">
                            <p style="font-size: 12px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Cart Total</p>
                            <p style="font-size: 28px; font-weight: 700; color: var(--accent); font-family: var(--mono);">{{ number_format($cartTotal / 100) }}</p>
                        </div>
                        @if(!empty($cart))
                            <button wire:click="openCheckout"
                                    style="padding: 14px 24px; background: var(--accent); color: white; border-radius: 10px; font-weight: 600; font-size: 15px;
                                           border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Checkout
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div style="max-width: 1400px; margin: 0 auto; padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px;">
                {{-- LEFT: Product Search & Cart --}}
                <div style="display: flex; flex-direction: column; gap: 20px;">

                    {{-- Phone Scanner Panel --}}
                    <div class="card">
                        <div style="display: flex; align-items: center; justify-between;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; background: {{ $showScannerPanel ? 'var(--success-glow)' : 'var(--surface2)' }}; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <svg style="width: 20px; height: 20px; color: {{ $showScannerPanel ? 'var(--success)' : 'var(--text-dim)' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p style="font-size: 14px; font-weight: 600; color: var(--text);">Phone Scanner</p>
                                    <p style="font-size: 12px; color: {{ $showScannerPanel ? 'var(--success)' : 'var(--text-dim)' }};">
                                        {{ $showScannerPanel ? '● Connected & polling' : 'Use your phone as barcode scanner' }}
                                    </p>
                                </div>
                            </div>
                            @if($showScannerPanel)
                                <button wire:click="disablePhoneScanner" style="padding: 8px 16px; font-size: 12px; font-weight: 600; color: var(--red); border: 1px solid var(--red); background: transparent; border-radius: 8px; cursor: pointer;">
                                    Disconnect
                                </button>
                            @else
                                <button wire:click="enablePhoneScanner" style="padding: 8px 16px; font-size: 12px; font-weight: 600; color: var(--accent); border: 1px solid var(--accent); background: transparent; border-radius: 8px; cursor: pointer;">
                                    Enable
                                </button>
                            @endif
                        </div>

                        @if($showScannerPanel && $scannerSession)
                            <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div style="background: var(--surface2); border-radius: 12px; padding: 20px; text-align: center;">
                                    <p style="font-size: 11px; color: var(--text-dim); margin-bottom: 12px; font-weight: 600;">SCAN WITH PHONE CAMERA</p>
                                    {!! QrCode::size(140)->generate(url('/scanner') . '?code=' . $scannerSession->session_code) !!}
                                    <div style="margin-top: 12px; padding: 8px 12px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px;">
                                        <p style="font-size: 18px; font-weight: 700; letter-spacing: 0.2em; color: var(--text); font-family: var(--mono);">{{ $scannerSession->session_code }}</p>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; justify-content: center; gap: 16px;">
                                    <div style="display: flex; align-items: start; gap: 10px;">
                                        <span style="width: 24px; height: 24px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">1</span>
                                        <span style="font-size: 13px; color: var(--text-sub);">Scan QR code with phone camera</span>
                                    </div>
                                    <div style="display: flex; align-items: start; gap: 10px;">
                                        <span style="width: 24px; height: 24px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">2</span>
                                        <span style="font-size: 13px; color: var(--text-sub);">Tap notification to open scanner</span>
                                    </div>
                                    <div style="display: flex; align-items: start; gap: 10px;">
                                        <span style="width: 24px; height: 24px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">3</span>
                                        <span style="font-size: 13px; color: var(--text-sub);">Scan products - appears instantly!</span>
                                    </div>
                                    <div style="margin-top: 8px; padding: 10px; background: var(--success-glow); border: 1px solid var(--success); border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 8px; height: 8px; background: var(--success); border-radius: 50%; animation: pulse 2s infinite;"></span>
                                        <span style="font-size: 11px; color: var(--success); font-weight: 600;">Listening every 2s</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($showScannerPanel)
                        <div wire:poll.2000ms="checkForScans"></div>
                    @endif

                    {{-- Barcode Scanner --}}
                    <div class="card">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 12px;">Scan Barcode</label>
                        <div style="position: relative;">
                            <input type="text" wire:model.live="barcodeInput" placeholder="Focus here and scan..." autofocus
                                   style="width: 100%; padding: 14px 16px 14px 48px; border: 2px solid var(--border); border-radius: 10px; font-size: 15px; background: var(--surface);">
                            <div style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-dim);">
                                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Product Search --}}
                    <div class="card" x-data="{ open: @entangle('showSearchResults') }" @click.away="open = false; $wire.closeSearch()">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 12px;">Search Products</label>
                        <div style="position: relative;">
                            <div style="position: relative;">
                                <div style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-dim);">
                                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" wire:model.live.debounce.200ms="searchQuery" wire:focus="loadAvailableProducts" autocomplete="off"
                                       placeholder="Click to browse or type to filter..."
                                       style="width: 100%; padding: 14px 48px 14px 48px; border: 2px solid var(--border); border-radius: 10px; font-size: 15px; background: var(--surface);">
                                @if($searchQuery)
                                    <button type="button" wire:click="$set('searchQuery', '')" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-dim); cursor: pointer;">
                                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            {{-- Search Results Dropdown --}}
                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1"
                                 style="position: absolute; z-index: 20; width: 100%; margin-top: 8px; background: var(--surface); border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: 1px solid var(--border); overflow: hidden;">

                                <div style="padding: 12px 16px; background: var(--surface2); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; color: var(--text-dim); font-weight: 600;">
                                        {{ count($searchResults) }} RESULT{{ count($searchResults) !== 1 ? 'S' : '' }}
                                        @if($searchQuery) FOR "{{ $searchQuery }}" @endif
                                    </span>
                                    <button type="button" @click="open = false; $wire.closeSearch()" style="background: none; border: none; color: var(--text-dim); cursor: pointer;">
                                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <div style="max-height: 400px; overflow-y: auto;">
                                    @forelse($searchResults as $result)
                                        <button wire:click="selectProduct({{ $result['id'] }})" @click="open = false" type="button"
                                                style="width: 100%; padding: 14px 16px; border: none; border-bottom: 1px solid var(--border); background: var(--surface); cursor: pointer; text-align: left; transition: background 0.2s;">
                                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                                <div style="flex: 1; min-width: 0;">
                                                    <p style="font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 4px;">{{ $result['name'] }}</p>
                                                    <p style="font-size: 12px; color: var(--text-dim);">
                                                        SKU: {{ $result['sku'] }}
                                                        @if($result['category']) · {{ $result['category'] }} @endif
                                                    </p>
                                                </div>
                                                <div style="text-align: right; margin-left: 16px;">
                                                    <p style="font-size: 15px; font-weight: 700; color: var(--accent); font-family: var(--mono);">{{ $result['selling_price_display'] }}</p>
                                                    <p style="font-size: 11px; color: var(--text-dim);">Stock: {{ $result['stock']['total_items'] }}</p>
                                                </div>
                                            </div>
                                        </button>
                                    @empty
                                        <div style="padding: 48px 24px; text-align: center;">
                                            <svg style="width: 48px; height: 48px; color: var(--text-dim); opacity: 0.3; margin: 0 auto 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                            </svg>
                                            <p style="font-size: 13px; color: var(--text-sub);">No products found</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Cart Items --}}
                    @if(!empty($cart))
                        <div class="card" style="padding: 0;">
                            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                                <h3 style="font-size: 15px; font-weight: 700; color: var(--text);">Cart Items ({{ count($cart) }})</h3>
                                <button wire:click="clearCart" style="font-size: 13px; color: var(--red); font-weight: 600; background: none; border: none; cursor: pointer;">
                                    Clear Cart
                                </button>
                            </div>
                            @foreach($cart as $index => $item)
                                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
                                    <div style="display: flex; align-items: start; justify-content: space-between; gap: 16px;">
                                        <div style="flex: 1;">
                                            <h4 style="font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 6px;">{{ $item['product_name'] }}</h4>
                                            <p style="font-size: 12px; color: var(--text-dim); font-family: var(--mono);">
                                                Box: {{ $item['box_code'] }}
                                                @if($item['is_full_box'])
                                                    <span style="margin-left: 8px; padding: 2px 8px; font-size: 10px; font-weight: 700; background: var(--blue-glow); color: var(--blue); border-radius: 6px;">FULL BOX</span>
                                                @endif
                                            </p>
                                            @if($item['price_modified'] ?? false)
                                                <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                                                    <span style="padding: 3px 8px; font-size: 10px; font-weight: 700; background: var(--amber-glow); color: var(--amber); border-radius: 6px;">PRICE MODIFIED</span>
                                                    @if($item['requires_owner_approval'] ?? false)
                                                        <span style="padding: 3px 8px; font-size: 10px; font-weight: 700; background: var(--red-glow); color: var(--red); border-radius: 6px;">NEEDS APPROVAL</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div style="text-align: right;">
                                            <p style="font-size: 16px; font-weight: 700; color: var(--text); font-family: var(--mono);">{{ number_format($item['line_total'] / 100) }}</p>
                                            <p style="font-size: 12px; color: var(--text-dim);">{{ number_format($item['price'] / 100) }} × {{ $item['quantity'] }}</p>
                                        </div>
                                    </div>
                                    <div style="margin-top: 12px; display: flex; align-items: center; gap: 8px;">
                                        <button wire:click="openEditItem({{ $index }})" style="padding: 6px 12px; background: var(--accent-dim); color: var(--accent); border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </button>
                                        <button wire:click="removeCartItem({{ $index }})" style="margin-left: auto; padding: 6px 12px; background: var(--red-glow); color: var(--red); border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="card" style="padding: 64px 24px; text-align: center;">
                            <svg style="width: 64px; height: 64px; color: var(--text-dim); opacity: 0.3; margin: 0 auto 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <p style="font-size: 15px; font-weight: 600; color: var(--text-sub); margin-bottom: 4px;">Cart is empty</p>
                            <p style="font-size: 13px; color: var(--text-dim);">Scan or search for products to add</p>
                        </div>
                    @endif
                </div>

                {{-- RIGHT: Cart Summary & Quick Stats --}}
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="card" style="position: sticky; top: 120px;">
                        <h3 style="font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 16px;">Cart Summary</h3>
                        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-sub);">Items:</span>
                                <span style="font-weight: 700; color: var(--text);">{{ count($cart) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-sub);">Quantity:</span>
                                <span style="font-weight: 700; color: var(--text);">{{ array_sum(array_column($cart, 'quantity')) }}</span>
                            </div>
                            <div style="border-top: 1px solid var(--border); padding-top: 12px; margin-top: 4px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 16px; font-weight: 700; color: var(--text);">Total:</span>
                                    <span style="font-size: 24px; font-weight: 700; color: var(--accent); font-family: var(--mono);">{{ number_format($cartTotal / 100) }}</span>
                                </div>
                            </div>
                        </div>
                        @if(!empty($cart))
                            <button wire:click="openCheckout" style="width: 100%; margin-top: 20px; padding: 14px; background: var(--accent); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer;">
                                Proceed to Checkout
                            </button>
                        @endif
                    </div>

                    <div style="background: linear-gradient(135deg, var(--accent) 0%, var(--violet) 100%); border-radius: 12px; padding: 20px; color: white;">
                        <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 16px; opacity: 0.9;">Today's Sales</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="opacity: 0.8;">Transactions:</span>
                                <span style="font-weight: 700;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="opacity: 0.8;">Revenue:</span>
                                <span style="font-weight: 700;">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════ MODALS ═══════════════════ --}}

    {{-- Add/Edit Item Modal --}}
    @if($showAddModal && $stagingProduct)
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 20px; overflow-y: auto;"
             x-data="{
                 mode: @entangle('stagingMode'),
                 qty: @entangle('stagingQty'),
                 price: @entangle('stagingPrice'),
                 priceModified: @entangle('stagingPriceModified'),
                 get lineTotal() { return this.price * this.qty; },
                 get lineTotalDisplay() { return Math.floor(this.lineTotal / 100).toLocaleString(); }
             }">
            <div style="background: var(--surface); border-radius: 16px; max-width: 700px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">

                {{-- Header --}}
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); position: sticky; top: 0; background: var(--surface); z-index: 10;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--text);">
                            {{ $stagingCartIndex !== null ? 'Edit Cart Item' : 'Add to Cart' }}
                        </h3>
                        <button wire:click="closeAddModal" style="width: 32px; height: 32px; border-radius: 8px; background: var(--surface2); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-dim);">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="padding: 24px;">
                    {{-- Product Info --}}
                    <div style="background: linear-gradient(135deg, var(--accent-dim) 0%, var(--blue-glow) 100%); border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid var(--accent);">
                        <h4 style="font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 12px;">{{ $stagingProduct['name'] }}</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                            <div>
                                <p style="color: var(--text-sub); margin-bottom: 4px;">SKU: <span style="font-weight: 600; color: var(--text);">{{ $stagingProduct['sku'] }}</span></p>
                                <p style="color: var(--text-sub);">Category: <span style="font-weight: 600; color: var(--text);">{{ $stagingProduct['category'] ?? 'N/A' }}</span></p>
                            </div>
                            <div>
                                <p style="color: var(--text-sub); margin-bottom: 4px;">Item Price: <span style="font-weight: 700; color: var(--accent); font-family: var(--mono);">{{ number_format($stagingProduct['selling_price'] / 100) }}</span></p>
                                <p style="color: var(--text-sub);">Box Price ({{ $stagingProduct['items_per_box'] }} items): <span style="font-weight: 700; color: var(--accent); font-family: var(--mono);">{{ number_format($stagingProduct['box_price'] / 100) }}</span></p>
                            </div>
                        </div>
                    </div>

                    {{-- Stock Info --}}
                    @if($stagingStock)
                        <div style="background: var(--success-glow); border: 1px solid var(--success); border-radius: 10px; padding: 14px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px;">
                                <span style="font-weight: 700; color: var(--success);">📦 Available Stock:</span>
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <span style="color: var(--success); font-weight: 600;"><strong>{{ $stagingStock['total_items'] }}</strong> items</span>
                                    <span style="color: var(--success); opacity: 0.9;">{{ $stagingStock['full_boxes'] }} full • {{ $stagingStock['partial_boxes'] }} partial</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Mode Toggle --}}
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 12px;">Sale Mode</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <button wire:click="$set('stagingMode', 'box')" type="button"
                                    style="padding: 16px; border-radius: 10px; border: 2px solid {{ $stagingMode === 'box' ? 'var(--accent)' : 'var(--border)' }};
                                           background: {{ $stagingMode === 'box' ? 'var(--accent-dim)' : 'var(--surface)' }}; cursor: pointer; transition: all 0.2s;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <svg style="width: 24px; height: 24px; color: {{ $stagingMode === 'box' ? 'var(--accent)' : 'var(--text-dim)' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <span style="font-weight: 700; font-size: 14px; color: {{ $stagingMode === 'box' ? 'var(--accent)' : 'var(--text)' }};">Full Boxes</span>
                                    <span style="font-size: 11px; color: var(--text-dim);">Sell by the box</span>
                                </div>
                            </button>
                            <button wire:click="$set('stagingMode', 'item')" type="button"
                                    style="padding: 16px; border-radius: 10px; border: 2px solid {{ $stagingMode === 'item' ? 'var(--accent)' : 'var(--border)' }};
                                           background: {{ $stagingMode === 'item' ? 'var(--accent-dim)' : 'var(--surface)' }}; cursor: pointer; transition: all 0.2s;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <svg style="width: 24px; height: 24px; color: {{ $stagingMode === 'item' ? 'var(--accent)' : 'var(--text-dim)' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    <span style="font-weight: 700; font-size: 14px; color: {{ $stagingMode === 'item' ? 'var(--accent)' : 'var(--text)' }};">Individual Items</span>
                                    <span style="font-size: 11px; color: var(--text-dim);">Sell by piece</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    {{-- Quantity --}}
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 8px;">
                            Quantity
                            <span style="font-weight: 400; color: var(--text-dim);" x-show="mode === 'box'">(boxes @ {{ $stagingProduct['items_per_box'] }} items each)</span>
                            <span style="font-weight: 400; color: var(--text-dim);" x-show="mode === 'item'">(individual items)</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <button wire:click="$set('stagingQty', {{ max(1, $stagingQty - 1) }})" type="button"
                                    style="width: 48px; height: 48px; background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; font-size: 20px; font-weight: 700; color: var(--text); cursor: pointer;">
                                −
                            </button>
                            <input type="number" wire:model.live="stagingQty" min="1"
                                   style="flex: 1; text-align: center; font-size: 24px; font-weight: 700; padding: 12px; border: 2px solid var(--border); border-radius: 10px; background: var(--surface);">
                            <button wire:click="$set('stagingQty', {{ $stagingQty + 1 }})" type="button"
                                    style="width: 48px; height: 48px; background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; font-size: 20px; font-weight: 700; color: var(--text); cursor: pointer;">
                                +
                            </button>
                        </div>
                    </div>

                    {{-- Price --}}
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 8px;">
                            Unit Price (RWF)
                            <span style="font-weight: 400; color: var(--text-dim);">— price per {{ $stagingMode === 'box' ? 'box' : 'item' }}</span>
                        </label>
                        <div style="position: relative;">
                            <input type="number" wire:model.live="stagingPrice"
                                   wire:change="$set('stagingPriceModified', {{ $stagingPrice }} !== ({{ $stagingMode }} === 'box' ? {{ $stagingProduct['box_price'] }} : {{ $stagingProduct['selling_price'] }}))"
                                   min="0" step="100"
                                   style="width: 100%; font-size: 18px; font-weight: 700; padding: 14px 60px 14px 16px; border: 2px solid {{ $stagingPriceModified ? 'var(--amber)' : 'var(--border)' }};
                                          border-radius: 10px; background: {{ $stagingPriceModified ? 'var(--amber-glow)' : 'var(--surface)' }}; font-family: var(--mono);">
                            <div style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-dim); font-size: 14px; font-weight: 600;">RWF</div>
                        </div>
                        @if($stagingPriceModified)
                            <div style="margin-top: 12px;">
                                <label style="display: block; font-size: 11px; color: var(--amber); font-weight: 600; margin-bottom: 6px;">Price Modification Reason</label>
                                <input type="text" wire:model="stagingPriceReason" placeholder="e.g., Bulk discount, damage, manager approval..."
                                       style="width: 100%; font-size: 13px; padding: 10px 12px; border: 1px solid var(--amber); border-radius: 8px; background: var(--amber-glow);">
                            </div>
                        @endif
                    </div>

                    {{-- Line Total --}}
                    <div style="background: var(--accent-dim); border: 2px solid var(--accent); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <p style="font-size: 12px; color: var(--accent); font-weight: 700; margin-bottom: 4px;">LINE TOTAL</p>
                                <p style="font-size: 11px; color: var(--text-sub);">
                                    <span x-text="qty"></span> × <span x-text="Math.floor(price / 100).toLocaleString()"></span> RWF
                                </p>
                            </div>
                            <p style="font-size: 32px; font-weight: 700; color: var(--accent); font-family: var(--mono);">
                                <span x-text="lineTotalDisplay"></span>
                            </p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div style="display: flex; gap: 12px;">
                        <button wire:click="confirmAddToCart" type="button"
                                style="flex: 1; padding: 14px 24px; background: var(--accent); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ $stagingCartIndex !== null ? 'Update Cart' : 'Add to Cart' }}
                        </button>
                        <button wire:click="closeAddModal" type="button"
                                style="padding: 14px 24px; background: var(--surface2); color: var(--text); border: 1px solid var(--border); border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer;">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Checkout Modal --}}
    @if($showCheckoutModal)
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 20px; overflow-y: auto;">
            <div style="background: var(--surface); border-radius: 16px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">

                {{-- Header --}}
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); position: sticky; top: 0; background: var(--surface); z-index: 10;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--text);">Complete Sale</h3>
                        <button wire:click="closeCheckoutModal" style="width: 32px; height: 32px; border-radius: 8px; background: var(--surface2); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-dim);">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="padding: 24px;">
                    {{-- Cart Summary --}}
                    <div style="background: var(--accent-dim); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: var(--text-sub); font-size: 14px;">Items:</span>
                            <span style="font-weight: 700; color: var(--text); font-size: 14px;">{{ count($cart) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span style="color: var(--text-sub); font-size: 14px;">Quantity:</span>
                            <span style="font-weight: 700; color: var(--text); font-size: 14px;">{{ array_sum(array_column($cart, 'quantity')) }}</span>
                        </div>
                        <div style="border-top: 1px solid var(--accent); padding-top: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 16px; font-weight: 700; color: var(--text);">Total:</span>
                                <span style="font-size: 28px; font-weight: 700; color: var(--accent); font-family: var(--mono);">{{ number_format($cartTotal / 100) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 8px;">Payment Method</label>
                        <select wire:model.live="paymentMethod" style="width: 100%; padding: 12px 16px; border: 2px solid var(--border); border-radius: 10px; font-size: 14px; background: var(--surface); color: var(--text); cursor: pointer;">
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    {{-- Amount Received (Cash) --}}
                    @if($paymentMethod === 'cash')
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 8px;">Amount Received (RWF)</label>
                            <input type="number" wire:model.live="amountReceived" step="1" min="0"
                                   style="width: 100%; padding: 12px 16px; border: 2px solid var(--border); border-radius: 10px; font-size: 16px; font-weight: 600; background: var(--surface); font-family: var(--mono);">
                        </div>

                        @if($changeAmount > 0)
                            <div style="background: var(--success-glow); border: 1px solid var(--success); border-radius: 10px; padding: 14px; margin-bottom: 20px;">
                                <p style="font-size: 13px; color: var(--success); font-weight: 600;">
                                    Change to Return: <span style="font-size: 18px; font-family: var(--mono);">{{ number_format($changeAmount) }} RWF</span>
                                </p>
                            </div>
                        @endif
                    @endif

                    {{-- Customer Info --}}
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 8px;">Customer Name (Optional)</label>
                        <input type="text" wire:model="customerName" placeholder="Enter customer name"
                               style="width: 100%; padding: 12px 16px; border: 2px solid var(--border); border-radius: 10px; font-size: 14px; background: var(--surface);">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 8px;">Customer Phone (Optional)</label>
                        <input type="text" wire:model="customerPhone" placeholder="Enter customer phone"
                               style="width: 100%; padding: 12px 16px; border: 2px solid var(--border); border-radius: 10px; font-size: 14px; background: var(--surface);">
                    </div>

                    {{-- Notes --}}
                    <div style="margin-bottom: 24px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-sub); margin-bottom: 8px;">Notes (Optional)</label>
                        <textarea wire:model="notes" rows="2" placeholder="Additional notes..."
                                  style="width: 100%; padding: 12px 16px; border: 2px solid var(--border); border-radius: 10px; font-size: 14px; background: var(--surface); resize: vertical;"></textarea>
                    </div>

                    {{-- Actions --}}
                    <div style="display: flex; gap: 12px;">
                        <button wire:click="completeSale"
                                style="flex: 1; padding: 14px 24px; background: var(--success); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer;">
                            Complete Sale
                        </button>
                        <button wire:click="closeCheckoutModal"
                                style="padding: 14px 24px; background: var(--surface2); color: var(--text); border: 1px solid var(--border); border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer;">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Receipt Modal --}}
    @if($showReceiptModal && $completedSale)
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 20px; overflow-y: auto;">
            <div style="background: var(--surface); border-radius: 16px; max-width: 500px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">

                {{-- Header --}}
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); position: sticky; top: 0; background: var(--surface); z-index: 10;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--text);">Sale Receipt</h3>
                        <button wire:click="closeReceipt" style="width: 32px; height: 32px; border-radius: 8px; background: var(--surface2); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-dim);">
                            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="receipt-content" style="padding: 24px;">
                    {{-- Receipt Header --}}
                    <div style="text-align: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px dashed var(--border);">
                        <h2 style="font-size: 22px; font-weight: 700; color: var(--text); margin-bottom: 8px;">{{ $completedSale->shop->name }}</h2>
                        <p style="font-size: 13px; color: var(--text-sub);">{{ $completedSale->shop->address ?? 'Rwanda' }}</p>
                        @if($completedSale->shop->phone)
                            <p style="font-size: 13px; color: var(--text-sub);">{{ $completedSale->shop->phone }}</p>
                        @endif
                    </div>

                    {{-- Sale Info --}}
                    <div style="margin-bottom: 24px; font-size: 13px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: var(--text-sub);">Sale #:</span>
                            <span style="font-weight: 700; color: var(--text); font-family: var(--mono);">{{ $completedSale->sale_number }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: var(--text-sub);">Date:</span>
                            <span style="font-weight: 600; color: var(--text);">{{ $completedSale->sale_date->format('Y-m-d H:i') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: var(--text-sub);">Cashier:</span>
                            <span style="font-weight: 600; color: var(--text);">{{ $completedSale->soldBy->name }}</span>
                        </div>
                        @if($completedSale->customer_name)
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-sub);">Customer:</span>
                                <span style="font-weight: 600; color: var(--text);">{{ $completedSale->customer_name }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Items --}}
                    <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px dashed var(--border);">
                        <h4 style="font-weight: 700; color: var(--text); margin-bottom: 16px; font-size: 14px;">Items:</h4>
                        @foreach($completedSale->items as $item)
                            <div style="margin-bottom: 14px;">
                                <div style="display: flex; justify-content: space-between; font-weight: 700; color: var(--text); margin-bottom: 4px; font-size: 14px;">
                                    <span>{{ $item->product->name }}</span>
                                    <span style="font-family: var(--mono);">{{ number_format($item->line_total / 100) }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-dim);">
                                    <span>{{ $item->quantity_sold }} × {{ number_format($item->actual_unit_price / 100) }} RWF</span>
                                    @if($item->is_full_box)
                                        <span style="color: var(--blue); font-weight: 600;">Full Box</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totals --}}
                    <div style="margin-bottom: 24px; font-size: 14px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: var(--text-sub);">Subtotal:</span>
                            <span style="font-weight: 700; color: var(--text); font-family: var(--mono);">{{ number_format($completedSale->subtotal / 100) }}</span>
                        </div>
                        @if($completedSale->tax > 0)
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="color: var(--text-sub);">Tax:</span>
                                <span style="font-weight: 700; color: var(--text); font-family: var(--mono);">{{ number_format($completedSale->tax / 100) }}</span>
                            </div>
                        @endif
                        @if($completedSale->discount > 0)
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: var(--red);">
                                <span>Discount:</span>
                                <span style="font-weight: 700; font-family: var(--mono);">-{{ number_format($completedSale->discount / 100) }}</span>
                            </div>
                        @endif
                        <div style="border-top: 2px solid var(--border); padding-top: 12px; margin-top: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 18px; font-weight: 700; color: var(--text);">TOTAL:</span>
                                <span style="font-size: 28px; font-weight: 700; color: var(--accent); font-family: var(--mono);">{{ number_format($completedSale->total / 100) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div style="text-align: center; margin-bottom: 24px; font-size: 13px; color: var(--text-sub);">
                        <p>Payment: <span style="font-weight: 700; color: var(--text);">{{ $completedSale->payment_method->label() }}</span></p>
                    </div>

                    {{-- Footer --}}
                    <div style="text-align: center; padding-top: 20px; border-top: 1px solid var(--border); font-size: 13px; color: var(--text-sub);">
                        <p style="font-weight: 700; margin-bottom: 4px;">Thank you for your business!</p>
                        <p>Please come again</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="padding: 16px 24px; background: var(--surface2); border-top: 1px solid var(--border); display: flex; gap: 12px;">
                    <button wire:click="printReceipt"
                            style="flex: 1; padding: 12px 20px; background: var(--accent); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer;">
                        Print Receipt
                    </button>
                    <button wire:click="closeReceipt"
                            style="padding: 12px 20px; background: var(--surface); color: var(--text); border: 1px solid var(--border); border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Print Receipt Script --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('print-receipt', () => {
                window.print();
            });
        });
    </script>

    {{-- Styles --}}
    <style>
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 20px;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    [x-cloak] { display: none !important; }

    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }
        #receipt-content, #receipt-content * {
            visibility: visible;
        }
        #receipt-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
    </style>

</div>
