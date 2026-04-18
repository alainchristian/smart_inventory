<div>
    @if (session()->has('success'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--green-dim);color:var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-3 px-3 py-2 rounded-lg text-xs" style="background:var(--red-dim);color:var(--red);">{{ session('error') }}</div>
    @endif

    @if ($requests->isEmpty())
        <div class="text-center py-8 text-sm" style="color:var(--text-faint);">No pending warehouse requests.</div>
    @else
        <div class="space-y-3">
            @foreach ($requests as $request)
                <div class="rounded-xl p-4" style="background:var(--surface-raised);border:1px solid var(--border);">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-mono font-semibold" style="color:var(--accent);">{{ $request->reference_number }}</span>
                                <span class="text-xs px-2 py-0.5 rounded" style="background:var(--amber-dim);color:var(--amber);">Pending</span>
                            </div>
                            <div class="text-sm font-medium" style="color:var(--text);">{{ $request->reason }}</div>
                            <div class="text-xs mt-1" style="color:var(--text-dim);">
                                From {{ $request->warehouse->name ?? 'Warehouse' }} · Requested by {{ $request->requestedBy->name ?? '—' }} · {{ $request->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="font-mono font-bold text-sm" style="color:var(--text);">{{ number_format($request->amount) }} RWF</div>
                        </div>
                    </div>

                    @if ($rejectingId === $request->id)
                        <div class="mt-3 pt-3" style="border-top:1px solid var(--border);">
                            <label class="block text-xs font-medium mb-1" style="color:var(--text-dim);">Rejection reason</label>
                            <input type="text" wire:model="rejectionReason"
                                   class="w-full px-3 py-2 rounded-lg text-sm mb-2"
                                   style="background:var(--surface);border:1px solid var(--border);color:var(--text);"
                                   placeholder="Reason for rejection…">
                            @error('rejectionReason') <div class="text-xs mb-2" style="color:var(--red);">{{ $message }}</div> @enderror
                            <div class="flex gap-2">
                                <button wire:click="submitRejection"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold"
                                        style="background:var(--red);color:white;">Confirm Reject</button>
                                <button wire:click="cancelReject"
                                        class="px-3 py-1.5 rounded-lg text-xs"
                                        style="background:var(--surface-raised);color:var(--text-dim);border:1px solid var(--border);">Cancel</button>
                            </div>
                        </div>
                    @else
                        <div class="mt-3 pt-3 flex gap-2" style="border-top:1px solid var(--border);">
                            <button wire:click="payRequest({{ $request->id }})"
                                    wire:confirm="Pay {{ number_format($request->amount) }} RWF from today's session cash?"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold"
                                    style="background:var(--green);color:white;">
                                Pay from Cash
                            </button>
                            <button wire:click="showRejectForm({{ $request->id }})"
                                    class="px-3 py-1.5 rounded-lg text-xs"
                                    style="background:var(--red-dim);color:var(--red);">
                                Reject
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
