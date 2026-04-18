<div>
    @if (session()->has('success'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--green-dim);color:var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    {{-- Request Form --}}
    <div class="rounded-xl p-5 mb-6" style="background:var(--surface-raised);border:1px solid var(--border);">
        <div class="text-sm font-semibold mb-4" style="color:var(--text);">New Expense Request</div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Target Shop</label>
                <select wire:model="targetShopId"
                        class="w-full px-3 py-2 rounded-lg text-sm"
                        style="background:var(--surface);border:1px solid var(--border);color:var(--text);">
                    <option value="0">Select shop…</option>
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
                @error('targetShopId') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Amount (RWF)</label>
                <input type="number" wire:model="amount" min="1"
                       class="w-full px-3 py-2 rounded-lg text-sm"
                       style="background:var(--surface);border:1px solid var(--border);color:var(--text);font-family:var(--font-mono);"
                       placeholder="0">
                @error('amount') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Reason</label>
                <textarea wire:model="reason" rows="2"
                          class="w-full px-3 py-2 rounded-lg text-sm"
                          style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                          placeholder="Describe what the funds are needed for…"></textarea>
                @error('reason') <div class="text-xs mt-1" style="color:var(--red);">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button wire:click="submitRequest"
                    wire:loading.attr="disabled"
                    class="px-5 py-2 rounded-lg text-sm font-semibold"
                    style="background:var(--accent);color:white;">
                <span wire:loading.remove wire:target="submitRequest">Submit Request</span>
                <span wire:loading wire:target="submitRequest" style="display:none;">Submitting…</span>
            </button>
        </div>
    </div>

    {{-- Request History --}}
    <div>
        <div class="text-sm font-semibold mb-3" style="color:var(--text);">My Request History</div>

        @if ($myRequests->isEmpty())
            <div class="text-center py-6 text-sm" style="color:var(--text-faint);">No requests yet.</div>
        @else
            <div class="space-y-2">
                @foreach ($myRequests as $req)
                    <div class="rounded-lg p-3 flex items-center justify-between gap-3"
                         style="background:var(--surface-raised);border:1px solid var(--border);">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono" style="color:var(--accent);">{{ $req->reference_number }}</span>
                                @php
                                    $statusColors = [
                                        'pending'  => ['bg' => 'var(--amber-dim)',  'text' => 'var(--amber)'],
                                        'paid'     => ['bg' => 'var(--green-dim)',  'text' => 'var(--green)'],
                                        'rejected' => ['bg' => 'var(--red-dim)',    'text' => 'var(--red)'],
                                        'approved' => ['bg' => 'var(--accent-dim)', 'text' => 'var(--accent)'],
                                    ];
                                    $sc = $statusColors[$req->status] ?? ['bg' => 'var(--surface-raised)', 'text' => 'var(--text-dim)'];
                                @endphp
                                <span class="text-xs px-2 py-0.5 rounded"
                                      style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </div>
                            <div class="text-xs mt-0.5 truncate" style="color:var(--text-dim);">
                                → {{ $req->targetShop->name ?? '—' }} · {{ $req->reason }}
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="font-mono font-semibold text-sm" style="color:var(--text);">{{ number_format($req->amount) }} RWF</div>
                            <div class="text-xs" style="color:var(--text-faint);">{{ $req->created_at->format('d M') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
